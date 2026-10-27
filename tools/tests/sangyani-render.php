<?php
/** Bare template fixture, deliberately outside the production theme. */
define('ABSPATH',__DIR__);define('SP_QUIZ_BANK_DIR',dirname(__DIR__,2).'/content/sangyani');
function add_action(...$a){}function add_filter(...$a){}function get_option($k,$d=[]){return $d;}
function get_stylesheet_directory(){return dirname(__DIR__,2).'/buddyboss-theme-child';}
function get_header(){echo '<!doctype html><html lang="en"><head><meta name="viewport" content="width=device-width, initial-scale=1"><style>body{margin:0;font-family:Arial}';readfile(get_stylesheet_directory().'/assets/css/quiz.css');readfile(get_stylesheet_directory().'/assets/css/quiz-emblem.css');echo '</style></head><body>';}
function get_footer(){echo '</body></html>';}
function esc_html($s){return htmlspecialchars((string)$s);}function esc_attr($s){return esc_html($s);}function esc_url($s){return esc_html($s);}
function esc_html_e($s,...$a){echo esc_html($s);}function esc_attr_e($s,...$a){echo esc_attr($s);}function is_user_logged_in(){return false;}
function home_url($s){return 'https://example.invalid'.$s;}function wp_login_url($s){return $s;}function sp_research_credit(){echo '<p class="sp-quiz__note">ShivBodh Research Group</p>';}
require get_stylesheet_directory().'/inc/quiz/quiz.php';require get_stylesheet_directory().'/template-quiz.php';
