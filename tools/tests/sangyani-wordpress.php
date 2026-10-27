<?php
/** Run through WP-CLI --skip-themes eval-file. Isolated, temporary InnoDB tables only. */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) { exit(1); }
$root = dirname( __DIR__, 2 );
define( 'SP_QUIZ_BANK_DIR', $root . '/content/sangyani' );
require $root . '/buddyboss-theme-child/inc/quiz/quiz.php';
global $wpdb;
$original = $wpdb->prefix; $test_prefix = $original . 'spqtest_' . bin2hex(random_bytes(4)) . '_';
$created = array(); $GLOBALS['checks'] = 0;
function spq_assert( $ok, $label ) { global $checks; if ( ! $ok ) { throw new RuntimeException( $label ); } $checks++; }
function spq_call( $function, $data ) {
    static $sequence=0;
    $_SERVER['REMOTE_ADDR'] = 'isolated-' . (int) floor(++$sequence / 50);
    $req = new WP_REST_Request('POST'); $req->set_body_params($data); $req->set_header('X-WP-Nonce',wp_create_nonce('wp_rest'));
    $response = $function($req); return is_wp_error($response) ? $response : $response->get_data();
}
try {
    foreach ( array('sp_quiz_runs','sp_quiz_log') as $suffix ) {
        $table = $test_prefix . $suffix; $created[] = $table;
        spq_assert(false !== $wpdb->query("CREATE TABLE `$table` LIKE `{$original}{$suffix}`"),'Create isolated table');
    }
    $stats = $test_prefix . 'sp_quiz_stats'; $created[]=$stats;
    $wpdb->query("CREATE TABLE `$stats` (card varchar(12) PRIMARY KEY, attempts bigint unsigned NOT NULL DEFAULT 0,wrong bigint unsigned NOT NULL DEFAULT 0) ENGINE=InnoDB");
    $reports=$test_prefix.'sp_quiz_reports';$created[]=$reports;
    $wpdb->query("CREATE TABLE `$reports` (id bigint unsigned AUTO_INCREMENT PRIMARY KEY,card varchar(12),message text,reported_at datetime,status varchar(12) NOT NULL DEFAULT 'open') ENGINE=InnoDB");
    $wpdb->prefix=$test_prefix;
    $start=spq_call('sp_quiz_rest_start',array('name'=>'Isolated WordPress QA','mode'=>'challenge','fresh'=>true));
    spq_assert(!is_wp_error($start),'Start'); $token=$start['run']['token'];$id=$start['question']['id'];
    $run=sp_quiz_get_run($token);$run->state['current']['issued']=time()-60;sp_quiz_save_run($run);
    $choice=array_search(1,$run->state['current']['order'],true);
    $answer=spq_call('sp_quiz_rest_answer',array('token'=>$token,'questionId'=>$id,'choice'=>$choice));
    spq_assert($answer['expired']&&!$answer['correct']&&$answer['run']['score']===-5,'Expired answer rejected');
    $again=spq_call('sp_quiz_rest_answer',array('token'=>$token,'questionId'=>$id,'choice'=>$choice));
    spq_assert($answer===$again,'Exact idempotent response');
    spq_assert((int)$wpdb->get_var("SELECT attempts FROM `$stats` WHERE card='".esc_sql($start['question']['key'])."'")===1,'Idempotent analytics');
    $resume=spq_call('sp_quiz_rest_start',array('token'=>$token));spq_assert($resume['feedback']['questionId']===$id,'Recover receipt');
    $next=spq_call('sp_quiz_rest_action',array('token'=>$token,'action'=>'next','questionId'=>$id));
    $stale=spq_call('sp_quiz_rest_answer',array('token'=>$token,'questionId'=>$id,'choice'=>$choice));spq_assert(is_wp_error($stale),'Reject stale card');
    $reported=spq_call('sp_quiz_rest_action',array('token'=>$token,'action'=>'report','key'=>$start['question']['key'],'message'=>'Isolated test report; never shown in live admin.'));spq_assert(!is_wp_error($reported),'Report accepted');
    spq_assert((int)$wpdb->get_var("SELECT COUNT(*) FROM `$reports`")===1,'Report stored');
    $owner=(int)$wpdb->get_var("SELECT ID FROM {$wpdb->users} ORDER BY ID LIMIT 1");
    if ($owner) {
        wp_set_current_user($owner);
        $claimed=spq_call('sp_quiz_rest_action',array('token'=>$token,'action'=>'claim'));
        spq_assert(!is_wp_error($claimed)&&(int)sp_quiz_get_run($token)->user_id===$owner,'Claim guest to account');
        $auto=spq_call('sp_quiz_rest_start',array());spq_assert(!is_wp_error($auto)&&$auto['run']['token']===$token,'Member resume without browser token');
        wp_set_current_user(0); spq_assert(sp_quiz_get_run($token)===null,'Protect owned run');
    }
    $start=spq_call('sp_quiz_rest_start',array('name'=>str_repeat('A',60),'mode'=>'learn','fresh'=>true));$token=$start['run']['token'];
    for($n=0;$n<500;$n++) {
        $run=sp_quiz_get_run($token);$current=$run->state['current'];
        $a=spq_call('sp_quiz_rest_answer',array('token'=>$token,'questionId'=>$current['id'],'choice'=>array_search(1,$current['order'],true)));
        spq_assert(!is_wp_error($a)&&$a['correct'],'Answer '.$n);
        if($n<499) { $next=spq_call('sp_quiz_rest_action',array('token'=>$token,'action'=>'next','questionId'=>$current['id'])); spq_assert(!is_wp_error($next)&&!empty($next['question']['id']),'Advance '.$n); }
    }
    spq_assert($a['run']['status']==='completed'&&$a['run']['score']===5000,'Complete 500 cards');
    spq_assert((int)$wpdb->get_var('SELECT COUNT(*) FROM '.sp_quiz_log_table().' WHERE run_id='.(int)$run->id)===10,'Ten level logs');
    $cert_req=new WP_REST_Request('GET');$cert_req->set_param('id',$a['run']['cert']['id']);$cert=sp_quiz_rest_certificate($cert_req);
    spq_assert(!is_wp_error($cert)&&$cert->get_data()['kind']==='Course completion','Certificate verification');
    $p=spq_call('sp_quiz_rest_action',array('token'=>$token,'action'=>'practice'));spq_assert(!is_wp_error($p)&&$p['run']['mode']==='practice'&&$p['question']['remaining']===null,'Create untimed revision');
    spq_assert(count(sp_quiz_get_run($p['run']['token'])->state['queue'])===10,'Ten revision cards');
    $wpdb->update(sp_quiz_runs_table(),array('status'=>'revoked'),array('id'=>$run->id));spq_assert(is_wp_error(sp_quiz_rest_certificate($cert_req)),'Revocation verified');
    echo wp_json_encode(array('status'=>'PASS','checks'=>$GLOBALS['checks'],'database'=>'InnoDB / WordPress REST callbacks','scope'=>'temporary isolated tables','publicCertificatesIssued'=>0),JSON_PRETTY_PRINT).PHP_EOL;
} finally {
    $wpdb->prefix=$original;
    foreach(array_reverse($created) as $table) { $wpdb->query("DROP TABLE IF EXISTS `$table`"); }
    // Salted test transients contain no bearer token or personal data and expire normally.
}
