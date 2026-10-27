<?php
/** Deterministic protocol tests; no network, WordPress database or issued public certificates. */
define( 'ABSPATH', __DIR__ );
define( 'SP_QUIZ_BANK_DIR', dirname( __DIR__, 2 ) . '/content/sangyani' );
function add_action(...$a) {} function add_filter(...$a) {}
function get_stylesheet_directory() { return dirname( __DIR__, 2 ) . '/buddyboss-theme-child'; }
$GLOBALS['user'] = 0;
function get_current_user_id() { return $GLOBALS['user']; }
function wp_json_encode($v) { return json_encode($v); }
function current_time(...$a) { return gmdate('Y-m-d H:i:s'); }
function get_option($key, $default = false) { return $default; }
function update_option(...$a) { return true; }
function get_transient(...$a) { return 0; } function set_transient(...$a) { return true; }
function wp_salt() { return 'isolated-test-salt'; }
function wp_verify_nonce(...$a) { return true; }
function sanitize_text_field($s) { return trim(strip_tags($s)); }
function sanitize_textarea_field($s) { return trim(strip_tags($s)); }
function mysql2date($f,$d) { return date($f,strtotime($d)); } function get_date_from_gmt($d) { return $d; }
function home_url($p) { return 'https://example.invalid'.$p; }
function add_query_arg($k,$v,$u) { return $u.'?'.$k.'='.$v; }
class WP_Error { public function __construct(public $code,public $message,public $data=[]){} }
function is_wp_error($x) { return $x instanceof WP_Error; }
class WP_REST_Response { public function __construct(public $data) {} function get_data() { return $this->data; } }
function rest_ensure_response($x) { return $x instanceof WP_REST_Response ? $x : new WP_REST_Response($x); }
class WP_REST_Request { function __construct(public $data) {} function get_param($k) { return $this->data[$k]??null; } function get_header($k) { return 'nonce'; } }
class MemoryDB {
    public $prefix='test_', $record, $log=[], $last_error='', $insert_id=1;
    function prepare($sql,...$args) { return [$sql,$args]; }
    function get_row($q) { if (isset($q[1][0]) && $q[1][0] !== $this->record->token) return null; return clone $this->record; }
    function get_var($q) { if (str_contains($q[0],'GET_LOCK')||str_contains($q[0],'RELEASE_LOCK')) return '1'; return null; }
    function query($q) { return 1; }
    function update($table,$data,$where) { if (str_ends_with($table,'runs')) foreach($data as $k=>$v) $this->record->$k=$v; return 1; }
    function insert($table,$data) { $this->log[]=$data; return 1; }
    function delete(...$a) { return 1; }
}
require get_stylesheet_directory().'/inc/quiz/quiz.php';
$wpdb = new MemoryDB(); $checks=0;
function check($condition,$message) { global $checks; if (!$condition) throw new Exception('FAIL: '.$message); $checks++; }
function seed($mode='challenge',$level=1) {
    global $wpdb;
    $state=sp_quiz_level_state($level); $state['mode']=$mode;
    $wpdb->record=(object)['id'=>1,'token'=>str_repeat('a',40),'user_id'=>0,'player_name'=>'Isolated Regression','level'=>$level,'levels_cleared'=>0,'score'=>0,'correct'=>0,'wrong'=>0,'status'=>'active','state'=>json_encode($state),'cert_id'=>null,'finished_at'=>null];
    $run=sp_quiz_get_run($wpdb->record->token); sp_quiz_issue_question($run); sp_quiz_save_run($run); return $run;
}
function request($fn,$data) { $r=$fn(new WP_REST_Request($data)); return $r instanceof WP_REST_Response ? $r->get_data() : $r; }
function answerCurrent($age=0) {
    global $wpdb;
    $r=sp_quiz_get_run($wpdb->record->token); $r->state['current']['issued']-= $age; sp_quiz_save_run($r);
    return request('sp_quiz_rest_answer',['token'=>$r->token,'questionId'=>$r->state['current']['id'],'choice'=>array_search(1,$r->state['current']['order'],true)]);
}
$bank=sp_quiz_bank(); check(count($bank)===10,'ten topics');
foreach($bank as $level=>$data) { check(count($data['items'])===50,'50 cards each'); foreach($data['items'] as $i=>$item) { check(count(array_unique(array_slice($item,1)))===4,'distinct options'); check(strlen(sp_quiz_explanation($level,$i))>30,'note coverage'); } }
$r=seed(); $qid=$r->state['current']['id']; $issued=$r->state['current']['issued'];
$res=request('sp_quiz_rest_start',['token'=>$r->token]); check($res['question']['id']===$qid,'resume keeps ID'); check(sp_quiz_get_run($r->token)->state['current']['issued']===$issued,'resume keeps deadline');
$res=answerCurrent(3600); check(!$res['correct']&&$res['expired']&&$res['run']['score']===-5,'server enforces expired deadline');
$again=request('sp_quiz_rest_answer',['token'=>$r->token,'questionId'=>$qid,'choice'=>0]); check($again===$res,'identical response on retry');
$loaded=request('sp_quiz_rest_start',['token'=>$r->token]); check($loaded['feedback']['questionId']===$qid,'lost response recovered');
$next=request('sp_quiz_rest_action',['token'=>$r->token,'action'=>'next','questionId'=>$qid]); check($next['question']['id']!==$qid,'next has new ID');
$stale=request('sp_quiz_rest_answer',['token'=>$r->token,'questionId'=>$qid,'choice'=>0]); check(is_wp_error($stale),'stale prior card rejected');
$next2=request('sp_quiz_rest_action',['token'=>$r->token,'action'=>'next','questionId'=>$qid]); check($next2['question']['id']===$next['question']['id'],'next retry does not skip');
$r=seed('learn'); $res=answerCurrent(3600); check($res['correct']&&!$res['expired'],'learning has no deadline');
$r=seed(); $res=request('sp_quiz_rest_answer',['token'=>$r->token,'questionId'=>$r->state['current']['id'],'choice'=>'0']); check(is_wp_error($res),'invalid choice type rejected');
$wpdb->record->user_id=17; check(sp_quiz_get_run($r->token)===null,'foreign owner inaccessible'); $GLOBALS['user']=17; check(sp_quiz_get_run($r->token)!==null,'owner can access'); $GLOBALS['user']=0;
$r=seed(); $GLOBALS['user']=23; $res=request('sp_quiz_rest_action',['token'=>$r->token,'action'=>'claim']); check($wpdb->record->user_id===23,'guest progress claimed'); $GLOBALS['user']=0;
$r=seed('learn');
for($n=0;$n<500;$n++) {
    $res=answerCurrent(); check($res['correct'],'correct accepted');
    if ($n<499) { $next=request('sp_quiz_rest_action',['token'=>$r->token,'action'=>'next','questionId'=>$res['questionId']]); check(!empty($next['question']['id']),'next card available'); }
}
check($res['run']['status']==='completed'&&$res['run']['levelsCleared']===10&&$res['run']['score']===5000,'500-card course completes');
check($res['run']['cert']['kind']==='Course completion','honest certificate kind');
$r=seed('practice'); $r->state['queue']=[$r->state['current']['i']]; $r->state['practiceTotal']=1; sp_quiz_save_run($r); $res=answerCurrent(); check($res['run']['status']==='practised'&&$res['run']['cert']===null,'revision does not issue certificate');
echo json_encode(['checks'=>$checks,'status'=>'PASS','liveRequests'=>0,'publicCertificatesIssued'=>0],JSON_PRETTY_PRINT).PHP_EOL;
