<?php
/** Isolated schema identity, metadata and publication policy fixtures. */
define('ABSPATH',__DIR__);
function add_filter(...$a){} function add_action(...$a){}
function home_url($p){return 'https://sampreshan.tech'.$p;}
function get_bloginfo($k){return 'SampreShan';}
function get_site_icon_url($n){return 'https://sampreshan.tech/logo.png';}
function sp_mission_short(){return 'Community and dharmic learning.';}
function is_post_type_archive($type){return false;}
function is_front_page(){return false;}
function is_singular($type){return $type==='dharma_update';}
function get_queried_object_id(){return 42;}
function get_post($id){return $GLOBALS['post'];}
function strip_shortcodes($t){return $t;}
function wp_strip_all_tags($t){return strip_tags($t);}
function wp_trim_words($t,$n,$more='...'){$w=explode(' ',$t);return count($w)>$n?implode(' ',array_slice($w,0,$n)).$more:$t;}
function get_the_title($p){return $p->post_title;}
require dirname(__DIR__,2).'/buddyboss-theme-child/inc/seo/schema.php';
class FixtureSettings { public $values=['existing'=>'kept']; public function all(){return $this->values;} public function set($g,$k,$v){$this->values[$g][$k]=$v;} }
function rank_math(){static $plugin; if(!$plugin)$plugin=(object)['settings'=>new FixtureSettings]; return $plugin;}
$checks=0;function check($ok){if(!$ok)throw new Exception('SEO fixture failed');$GLOBALS['checks']++;}
sp_seo_activate_directory_sitemaps();check(rank_math()->settings->values['existing']==='kept');check(rank_math()->settings->values['sitemap']['items_per_page']===1000);check(rank_math()->settings->values['sitemap']['pt_dharma_profile_sitemap']===true);check(rank_math()->settings->values['sitemap']['pt_dharma_update_sitemap']===true);
$o=sp_seo_site_org();check($o['name']==='SampreShan');check($o['@id']==='https://sampreshan.tech/#organization');check($o['parentOrganization']['@id']==='https://shivbodhtrust.org/#organization');check(!isset($o['sameAs']));
$s=sp_seo_directory_sitemap_settings(['other'=>'kept']);check($s['other']==='kept');check($s['items_per_page']===1000);check($s['pt_dharma_profile_sitemap']==='on');check($s['pt_dharma_update_sitemap']==='on');
$base=['post_type'=>'dharma_update','post_status'=>'publish','post_title'=>'A programme update','post_content'=>'English summary.','post_excerpt'=>''];check(sp_update_publication_gate($base)===$base);
$unicode=json_decode('"\u0927\u0930\u094d\u092e"');
foreach(['post_title','post_content','post_excerpt'] as $field){$d=$base;$d[$field]=$unicode;$r=sp_update_publication_gate($d);check($r['post_status']==='pending');check($r[$field]===$unicode);}
$d=$base;$d['post_type']='page';$d['post_title']=$unicode;check(sp_update_publication_gate($d)===$d);
$d=$base;$d['post_status']='draft';$d['post_title']=$unicode;check(sp_update_publication_gate($d)===$d);
$GLOBALS['post']=(object)['post_title'=>'Teaching on service','post_excerpt'=>'A practical teaching on helping others.','post_content'=>''];check(sp_seo_public_description('')==='A practical teaching on helping others.');
$GLOBALS['post']->post_excerpt='';$GLOBALS['post']->post_content='<p>A programme of study and worship.</p>';check(sp_seo_public_description('')==='A programme of study and worship.');
$GLOBALS['post']->post_content='';check(str_starts_with(sp_seo_public_description(''),'Teaching on service'));
echo json_encode(['status'=>'PASS','checks'=>$checks,'productionWrites'=>0],JSON_PRETTY_PRINT).PHP_EOL;
