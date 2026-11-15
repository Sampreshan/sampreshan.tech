<?php
/** Isolated public REST boundary fixtures; no WordPress or network writes. */
define('ABSPATH', __DIR__);
function add_action(...$args) {}
function __($s,...$args) { return $s; }
class WP_Error { public function __construct(public $code,public $message,public $data) {} }
class WP_REST_Response { public function __construct(public $data,public $status) {} }
class WP_REST_Request implements ArrayAccess {
 public function offsetExists($o):bool{return $o==='id';} public function offsetGet($o):mixed{return 42;}
 public function offsetSet($o,$v):void{} public function offsetUnset($o):void{}
 public function get_param($key){return ['per_page'=>20,'page'=>1][$key]??null;}
}
function get_post($id){return $GLOBALS['post'];}
function sp_petition_signature_count($id){return 2;}
function sp_petition_get_signatures($args){$GLOBALS['signature_reads']++;return [(object)['id'=>1,'user_id'=>99,'is_anonymous'=>1,'display_name'=>'Hidden name','comment'=>'Support','created_at'=>'2026-11-15'],(object)['id'=>2,'user_id'=>100,'is_anonymous'=>0,'display_name'=>'Public name','comment'=>'Support','created_at'=>'2026-11-15']];}
require dirname(__DIR__,2).'/buddyboss-theme-child/inc/petitions/rest.php';
$checks=0;$GLOBALS['signature_reads']=0;
function check($ok){if(!$ok)throw new Exception('Privacy fixture failed');$GLOBALS['checks']++;}
$r=new WP_REST_Request;
foreach(['draft','pending','private','trash','future'] as $status){
 $GLOBALS['post']=(object)['post_type'=>'petition','post_status'=>$status,'post_password'=>''];
 foreach(['sp_rest_petitions_show','sp_rest_petition_signatures'] as $handler){$x=$handler($r);check($x instanceof WP_Error && $x->data['status']===404);}
}
foreach([null,(object)['post_type'=>'post','post_status'=>'publish','post_password'=>''],(object)['post_type'=>'petition','post_status'=>'publish','post_password'=>'secret']] as $p){$GLOBALS['post']=$p;check(sp_rest_petitions_show($r) instanceof WP_Error);check(sp_rest_petition_signatures($r) instanceof WP_Error);}
check($GLOBALS['signature_reads']===0);
$GLOBALS['post']=(object)['post_type'=>'petition','post_status'=>'publish','post_password'=>''];
$x=sp_rest_petition_signatures($r);check($x->status===200);check($x->data['items'][0]['user_id']===0);check($x->data['items'][0]['name']==='Anonymous supporter');check($x->data['items'][1]['user_id']===100);check($x->data['items'][1]['name']==='Public name');
echo json_encode(['status'=>'PASS','checks'=>$checks,'productionWrites'=>0],JSON_PRETTY_PRINT).PHP_EOL;
