<?php
/**
 * Site footer for every template that calls get_footer().
 *
 * The parent BuddyBoss footer.php prints only a one-line copyright bar, while
 * the home, About, Contact and policy templates include the full SampreShan
 * footer. This file gives every page the same full footer. The partial closes
 * the #content wrappers, prints the footer, scroll-top, Sahayak widget,
 * wp_footer() and </body></html>.
 *
 * @package SampreShan_Child
 */

do_action( THEME_HOOK_PREFIX . 'end_content' );

// The SampreShan footer replaces BuddyBoss's copyright bar; other footer
// callbacks (e.g. an Elementor Pro footer location) keep running.
remove_action( THEME_HOOK_PREFIX . 'footer', 'buddyboss_theme_footer_area' );
do_action( THEME_HOOK_PREFIX . 'after_content' );
do_action( THEME_HOOK_PREFIX . 'before_footer' );
do_action( THEME_HOOK_PREFIX . 'footer' );
do_action( THEME_HOOK_PREFIX . 'after_footer' );

get_template_part( 'template-parts/footer/site-footer' );
