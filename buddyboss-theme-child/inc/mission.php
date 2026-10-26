<?php
/**
 * Why SampreShan gathers Acharya and Peeth information in one place.
 *
 * Single source for the mission wording used on the home page, the
 * directory, every dharma profile and in structured data. Keep claims
 * verifiable: "one public place", never "the only platform in the world".
 *
 * @package SampreShan_Child
 */

if ( ! defined( 'ABSPATH' ) ) { exit; }

/** One-line mission for meta descriptions and schema. */
function sp_mission_short() {
	return __( 'One free, public place for accurate information on the Shankaracharya Peeths, their Acharyas and the sampradayas of Bharat, with a live calendar of their programmes.', 'sampreshan-child' );
}

/** Full mission paragraphs. */
function sp_mission_paragraphs() {
	return array(
		__( 'The Acharyas of the Peeths are public figures, and the institutions they lead serve society: education, welfare, culture and spiritual guidance for everyone.', 'sampreshan-child' ),
		__( 'Bringing their information together and keeping it open to all is, for us, a social responsibility. Instead of visiting ten websites and ten social profiles, people can come to one place and find correct, carefully checked information about the right Acharyas.', 'sampreshan-child' ),
		__( 'Every profile, timeline and calendar entry here is built from the institutions\' own official channels first, checked by the ShivBodh Research Group, and kept free to read.', 'sampreshan-child' ),
	);
}

/**
 * Print the mission block.
 *
 * @param string $variant 'full' (home, directory) or 'compact' (profiles).
 */
function sp_mission_block( $variant = 'full' ) {
	$paras = sp_mission_paragraphs();
	if ( 'compact' === $variant ) {
		$paras = array( $paras[0], $paras[1] );
	}
	?>
	<section class="sp-mission sp-mission--<?php echo esc_attr( $variant ); ?>" aria-labelledby="sp-mission-<?php echo esc_attr( $variant ); ?>-h">
		<p class="sp-mission__eyebrow"><?php esc_html_e( 'Why this platform exists', 'sampreshan-child' ); ?></p>
		<h2 class="sp-mission__title" id="sp-mission-<?php echo esc_attr( $variant ); ?>-h"><?php esc_html_e( 'Right information about the Acharyas, in one place', 'sampreshan-child' ); ?></h2>
		<?php foreach ( $paras as $p ) : ?>
			<p class="sp-mission__text"><?php echo esc_html( $p ); ?></p>
		<?php endforeach; ?>
	</section>
	<?php
}

add_action(
	'wp_enqueue_scripts',
	static function () {
		if ( is_front_page() || is_singular( 'dharma_profile' ) || is_post_type_archive( 'dharma_profile' ) || is_tax( 'dharma_peeth' ) ) {
			$file = '/assets/css/mission.css';
			wp_enqueue_style( 'sp-mission', get_stylesheet_directory_uri() . $file, array(), (string) filemtime( get_stylesheet_directory() . $file ) );
		}
	},
	40
);
