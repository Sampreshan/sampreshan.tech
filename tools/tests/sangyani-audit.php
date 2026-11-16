<?php
/** Offline bank report. Exit 1 for exact duplicates; wording candidates need human review. */
define( 'ABSPATH', __DIR__ );
define( 'SP_QUIZ_BANK_DIR', dirname( __DIR__, 2 ) . '/content/sangyani' );
function add_action( ...$args ) {} function add_filter( ...$args ) {}
function get_option( $key, $default = array() ) { return $default; }
function get_stylesheet_directory() { return dirname( __DIR__, 2 ) . '/buddyboss-theme-child'; }
require get_stylesheet_directory() . '/inc/quiz/quiz.php';
$report = sp_quiz_audit_cards( sp_quiz_bank_cards( sp_quiz_bank() ) );
echo json_encode( $report, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . PHP_EOL;
exit( empty( $report['duplicateQuestions'] ) ? 0 : 1 );
