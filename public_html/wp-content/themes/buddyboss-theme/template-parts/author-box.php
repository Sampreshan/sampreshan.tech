<?php 
$author_box = buddyboss_theme_get_option( 'blog_author_box' );

if ( is_single() && !is_related_posts() && is_singular('post') ) : ?>
	<div class="post-author-info">
        <?php if ( !empty( $author_box ) ) :
		$description = get_the_author_meta( 'description' );

		$platform_author_link = buddyboss_theme_get_option( 'blog_platform_author_link' );
		$author_name = get_the_author_meta( 'user_nicename' );
		if ( function_exists( 'bp_core_get_user_domain' ) && $platform_author_link ) {
			$user_link = bp_core_get_user_domain( get_the_author_meta( 'ID' ) );
		} else {
			$user_link = get_author_posts_url( get_the_author_meta( 'ID' ) );
		}
		$class = empty( $description ) ? 'align-items-center' : '';

		// The hover card describes a BuddyPress member, so it is offered only when the
		// byline actually links to a member profile. With blog_platform_author_link off
		// the link is a WP author archive, and a member card would not match it — hence
		// the same two conditions that choose $user_link above gate the attribute here.
		$bb_use_profile_link = function_exists( 'bp_core_get_user_domain' ) && $platform_author_link;
		$bb_hp_attr          = ( $bb_use_profile_link && function_exists( 'bb_get_hover_card_profile_attr' ) ) ? bb_get_hover_card_profile_attr( get_the_author_meta( 'ID' ) ) : '';
		?>
		<div class="post-author-details <?php echo $class; ?>">
			<a href="<?php echo $user_link; ?>"<?php echo $bb_hp_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped via esc_attr() in bb_get_hover_card_profile_attr(). ?>>
				<?php echo get_avatar( get_the_author_meta( 'ID' ), 80, '', $author_name ); ?>
			</a>
			<div class="author-desc-wrap">
				<a class="post-author" href="<?php echo $user_link; ?>"<?php echo $bb_hp_attr; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Escaped via esc_attr() in bb_get_hover_card_profile_attr(). ?>><?php the_author(); ?></a>
				<?php if( !empty($description) ) { ?>
					<div class="author-desc"><?php the_author_meta( 'description' ); ?></div>
				<?php } ?>
			</div>
		</div>
        <?php endif; ?>
	</div><!--.post-author-info-->
<?php endif; ?>
