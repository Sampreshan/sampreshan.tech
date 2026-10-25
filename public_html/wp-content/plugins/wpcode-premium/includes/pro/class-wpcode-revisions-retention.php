<?php
/**
 * Retention for snippet revisions: keeps wp_wpcode_revisions from growing without bound.
 *
 * @package WPCode
 */

/**
 * Class WPCode_Revisions_Retention.
 */
class WPCode_Revisions_Retention {

	/**
	 * Option holding an integer bumped after any bulk delete, used to bust cached revision lists.
	 *
	 * @var string
	 */
	const CACHE_SALT = 'wpcode_revisions_cache_salt';

	/**
	 * Rows removed per DELETE statement while trimming.
	 *
	 * @var int
	 */
	const TRIM_ROW_BATCH = 500;

	/**
	 * Option holding the state of a user-triggered cleanup run.
	 *
	 * @var string
	 */
	const CLEANUP_OPTION = 'wpcode_revisions_cleanup';

	/**
	 * Items trimmed per cleanup step.
	 *
	 * @var int
	 */
	const CLEANUP_ITEM_BATCH = 20;

	/**
	 * How long a half-finished run stays resumable, in seconds.
	 *
	 * A run is resumed without a fresh confirmation, so it must not be resumable indefinitely: the
	 * dialog the user agreed to was about the state of the site at that moment.
	 *
	 * @var int
	 */
	const CLEANUP_MAX_AGE = 900;

	/**
	 * The lowest limit allowed. See get_keep_count().
	 *
	 * @var int
	 */
	const MIN_KEEP_COUNT = 5;

	/**
	 * The highest limit allowed.
	 *
	 * @var int
	 */
	const MAX_KEEP_COUNT = 100000;

	/**
	 * Per-request memo for the count queries.
	 *
	 * Both counts are read twice while the settings page renders, once for the row and once for the
	 * data handed to the browser. They are index-only scans, but there is no reason to run each
	 * twice. Cleared by every path that removes rows, so it can never answer from stale state.
	 *
	 * @var array
	 */
	protected $counts = array();

	/**
	 * Whether a query in the current cleanup step failed outright.
	 *
	 * $wpdb->query() returns false on error, which casts to 0 and is therefore indistinguishable
	 * from "nothing matched" at the call site. The loops must treat both as a reason to stop, but
	 * the run's terminal state must not: reporting a lock-wait timeout as a completed cleanup tells
	 * the user their revisions are within the limit when thousands are not.
	 *
	 * @var bool
	 */
	protected $step_failed = false;

	/**
	 * Most DELETE statements one trim will issue.
	 *
	 * Bounds the work a single snippet save can do. Anything still over the limit after this is
	 * removed on that item's next revision, which is preferable to a save that hangs.
	 *
	 * @var int
	 */
	const TRIM_MAX_ITERATIONS = 20;

	/**
	 * How long one trim may spend deleting, in seconds.
	 *
	 * The iteration cap bounds rows, but the cost is in bytes: revision_data holds the whole
	 * snippet, so 10,000 rows is 55ms at 700 bytes and over 1.4 seconds at 40KB. This runs inside
	 * the user's save request, so the budget has to be wall clock. Anything still over the limit
	 * comes down on the item's next revision, which the design already allows for.
	 *
	 * @var float
	 */
	const TRIM_MAX_SECONDS = 0.25;

	/**
	 * Constructor.
	 */
	public function __construct() {
		add_action( 'wpcode_after_create_revision', array( $this, 'trim_item' ) );
		add_action( 'delete_post', array( $this, 'delete_post_revisions' ) );
		// Priority 5 so the notice is queued before WPCode_Notice::display() runs at 10. Added on
		// this hook rather than admin_init because it fires on WPCode's own screens only, where the
		// notice is relevant; queued from admin_init it showed on the Dashboard, Plugins and Posts.
		add_action( 'wpcode_admin_notices', array( $this, 'maybe_show_notice' ), 5 );

		// Two hooks, not one: WPCode_Admin_Page calls remove_all_actions( 'admin_notices' ) and
		// fires wpcode_admin_notices instead, so admin_notices alone never reaches the snippet
		// editor, and wpcode_admin_notices alone never reaches a post or page edit screen.
		add_action( 'admin_notices', array( $this, 'maybe_warn_before_save' ) );
		add_action( 'wpcode_admin_notices', array( $this, 'maybe_warn_before_save' ) );

		// The block editor hides admin_notices output, so post and page screens there get the
		// same warning through the editor's own notices store.
		add_action( 'enqueue_block_editor_assets', array( $this, 'maybe_warn_in_block_editor' ) );
	}

	/**
	 * Get the object cache key for a revision list.
	 *
	 * The salt lets a bulk delete invalidate every cached list at once, which matters on installs
	 * with a persistent object cache because these entries are stored without an expiry.
	 *
	 * @param int $snippet_id The snippet id.
	 *
	 * @return string
	 */
	public static function get_cache_key( $snippet_id ) {
		return 'wpcode_revisions_for_snippet_' . absint( $snippet_id ) . '_' . absint( get_option( self::CACHE_SALT, 0 ) );
	}

	/**
	 * Invalidate cached revision lists.
	 *
	 * @param int|null $snippet_id Invalidate just this snippet, or null to invalidate every list.
	 *
	 * @return void
	 */
	public function flush_revision_cache( $snippet_id = null ) {
		// Every path that removes rows calls this, so clearing here is what makes the memo's
		// guarantee hold for a path added later that forgets to clear it itself.
		$this->counts = array();

		if ( is_null( $snippet_id ) ) {
			update_option( self::CACHE_SALT, absint( get_option( self::CACHE_SALT, 0 ) ) + 1, false );

			return;
		}

		wp_cache_delete( self::get_cache_key( $snippet_id ), 'wpcode' );
	}

	/**
	 * Trim one item back to the configured limit.
	 *
	 * Hooked to wpcode_after_create_revision, which is the only moment an item can exceed the
	 * limit, so it is the only moment worth checking: there is deliberately no cron sweep.
	 *
	 * $snippet_id is whatever create_revision() was given, and every value is a bucket in its own
	 * right: a wpcode post id, a regular post id for page scripts, or 0 for Header & Footer. It is
	 * NOT guarded on > 0 here, unlike the deletion handler, because 0 is Header & Footer's own
	 * bucket and is subject to the limit like anything else.
	 *
	 * @param int $snippet_id The item a revision was just created for.
	 *
	 * @return int Rows removed. Used by the cleanup runner to report progress; the hook ignores it.
	 */
	public function trim_item( $snippet_id ) {
		// A revision was just created, so any memoised count is stale regardless of whether this
		// call goes on to remove anything.
		$this->counts = array();

		if ( ! $this->is_enabled() ) {
			return 0;
		}

		global $wpdb;

		$snippet_id = absint( $snippet_id );

		// The newest row already past the limit. Nothing comes back when the item is within it,
		// which is the common case and costs one index-only read.
		$cutoff = $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT revision_id FROM {$wpdb->prefix}wpcode_revisions
				WHERE snippet_id = %d ORDER BY revision_id DESC LIMIT 1 OFFSET %d",
				$snippet_id,
				$this->get_keep_count()
			)
		);

		if ( empty( $cutoff ) ) {
			return 0;
		}

		$cutoff  = absint( $cutoff );
		$removed = 0;
		$started = microtime( true );

		// Chunked because this runs inside a snippet save. Capped by iteration count rather than by
		// rows removed: $wpdb->query() returns false on error, which casts to 0, so a rows-removed
		// cap would re-issue the same failing DELETE forever.
		for ( $i = 0; $i < self::TRIM_MAX_ITERATIONS; $i++ ) {
			$deleted = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
				$wpdb->prepare(
					"DELETE FROM {$wpdb->prefix}wpcode_revisions
					WHERE snippet_id = %d AND revision_id <= %d LIMIT %d",
					$snippet_id,
					$cutoff,
					self::TRIM_ROW_BATCH
				)
			);

			if ( false === $deleted ) {
				// Errored, not finished. Recorded so the run does not report success.
				$this->step_failed = true;
				break;
			}

			if ( 0 === (int) $deleted ) {
				// Nothing matched: another process got there first, or we are done.
				break;
			}

			$removed += (int) $deleted;

			// Checked after a chunk, never before, so one chunk always runs.
			if ( microtime( true ) - $started > self::TRIM_MAX_SECONDS ) {
				break;
			}
		}

		if ( $removed > 0 ) {
			$this->flush_revision_cache( $snippet_id );
		}

		return $removed;
	}

	/**
	 * Remove a post's revisions when that post is permanently deleted.
	 *
	 * Hooked to delete_post rather than before_delete_post: wp_delete_attachment() never fires
	 * before_delete_post, so media would keep leaking rows. delete_post fires on every permanent
	 * deletion path, and trashing fires neither, which is correct because trash is recoverable.
	 *
	 * No post-type gate. Revisions are keyed by post id for snippets, pages and page scripts alike,
	 * and post ids are globally unique, so one indexed DELETE covers every owner without a lookup
	 * query of its own. The positive-id guard is load-bearing: Header & Footer history is stored
	 * against snippet_id 0, so an id of 0 here would wipe all of it.
	 *
	 * @param int $post_id The post being deleted.
	 *
	 * @return void
	 */
	public function delete_post_revisions( $post_id ) {
		$post_id = absint( $post_id );

		if ( empty( $post_id ) ) {
			return;
		}

		// delete_post fires for WordPress's own revisions and housekeeping types too, none of which
		// can own a row here: deleting one post with ten revisions fires it eleven times, so
		// emptying a large trash meant thousands of pointless index-locking DELETEs on the path of
		// users who never enabled the feature. get_post_type() is free at this point, because
		// delete_post runs before the row and its caches are gone.
		$skip = array( 'revision', 'nav_menu_item', 'oembed_cache', 'customize_changeset' );

		if ( in_array( get_post_type( $post_id ), $skip, true ) ) {
			return;
		}

		global $wpdb;

		$wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"DELETE FROM {$wpdb->prefix}wpcode_revisions WHERE snippet_id = %d",
				$post_id
			)
		);

		$this->counts = array();
		$this->flush_revision_cache( $post_id );
	}

	/**
	 * Tell the user that revision limits exist, once, until they enable or dismiss.
	 *
	 * Deliberately announces availability rather than a pending deletion: removal is opt-in, so
	 * nothing is removed until the user turns it on. The notice disappears for good once dismissed
	 * (DISMISS_GLOBAL) or once the toggle is enabled, so enabling counts as having read it.
	 *
	 * Shown on WPCode screens only, and only when there is at least one revision: on a site with
	 * no revisions the setting has nothing to act on.
	 *
	 * @return void
	 */
	public function maybe_show_notice() {
		if ( ! isset( wpcode()->notice ) || ! current_user_can( 'wpcode_manage_settings' ) ) {
			return;
		}

		// Already enabled: there is nothing to announce.
		if ( $this->is_enabled() ) {
			return;
		}

		// Dismissed already. Checked here, not only in WPCode_Notice::display(), so a dismissed
		// notice does not cost a query on every WPCode screen.
		$dismissed = (array) get_option( 'wpcode_admin_notices', array() );

		if ( ! empty( $dismissed['wpcode_revisions_retention']['dismissed'] ) ) {
			return;
		}

		if ( ! $this->has_revisions() ) {
			return;
		}

		WPCode_Notice::add(
			sprintf(
				/* translators: %1$s and %2$s are the opening and closing tags of a link to the settings tab. */
				esc_html__( 'WPCode can now limit how many revisions each snippet, page and your Header & Footer code keeps. This is off by default and nothing is removed until you turn it on in %1$sSettings > General%2$s.', 'wpcode-premium' ),
				'<a href="' . esc_url( admin_url( 'admin.php?page=wpcode-settings&view=general' ) ) . '">',
				'</a>'
			),
			'info',
			array(
				'dismiss' => WPCode_Notice::DISMISS_GLOBAL,
				'slug'    => 'wpcode_revisions_retention',
			)
		);
	}

	/**
	 * Does the revisions table hold at least one row?
	 *
	 * @return bool
	 */
	protected function has_revisions() {
		global $wpdb;

		return (bool) $wpdb->get_var( "SELECT 1 FROM {$wpdb->prefix}wpcode_revisions LIMIT 1" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
	}

	/**
	 * How many revisions one item would lose if it were trimmed right now.
	 *
	 * @param int $snippet_id The item. 0 is Header & Footer's own bucket, and is valid here.
	 * @param int $pending    Rows about to be inserted that count toward the limit. A save
	 *                        inserts its revision before the trim runs, so the warning passes 1.
	 *
	 * @return int Rows over the limit, or 0 when the feature is off or the item is within it.
	 */
	public function count_item_excess( $snippet_id, $pending = 0 ) {
		if ( ! $this->is_enabled() ) {
			return 0;
		}

		global $wpdb;

		$rows = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$wpdb->prefix}wpcode_revisions WHERE snippet_id = %d",
				absint( $snippet_id )
			)
		);

		return max( 0, $rows + absint( $pending ) - $this->get_keep_count() );
	}

	/**
	 * Warn on an edit screen when saving is about to delete revisions.
	 *
	 * Without this the limit is invisible until it has already taken something: an item over the
	 * limit is trimmed by the next save that creates a revision, with nothing on screen to say so.
	 * States the number of revisions that will go rather than restating the rule, because that
	 * number is the one someone would stop for.
	 *
	 * @return void
	 */
	public function maybe_warn_before_save() {
		// The block editor hides this output. maybe_warn_in_block_editor() covers those screens.
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;

		if ( $screen && method_exists( $screen, 'is_block_editor' ) && $screen->is_block_editor() ) {
			return;
		}

		$warning = $this->get_save_warning();

		if ( empty( $warning ) ) {
			return;
		}

		printf(
			'<div class="notice notice-warning"><p><strong>%1$s</strong> %2$s</p></div>',
			esc_html( $warning['title'] ),
			esc_html( $warning['detail'] )
		);
	}

	/**
	 * Show the save warning in the block editor.
	 *
	 * The block editor does not show admin_notices output, so the warning is added to the editor's
	 * own notices store, where it renders above the content like any other editor notice.
	 *
	 * @return void
	 */
	public function maybe_warn_in_block_editor() {
		$warning = $this->get_save_warning();

		if ( empty( $warning ) ) {
			return;
		}

		wp_add_inline_script(
			'wp-notices',
			sprintf(
				'( function ( wp ) { if ( ! wp || ! wp.data || ! wp.data.dispatch( "core/notices" ) ) { return; } wp.data.dispatch( "core/notices" ).createWarningNotice( %s, { id: "wpcode-revisions-limit", isDismissible: true } ); } )( window.wp );',
				wp_json_encode( $warning['title'] . ' ' . $warning['detail'] )
			)
		);
	}

	/**
	 * The save warning for the edit screen being shown, if saving a change would delete revisions.
	 *
	 * A save deletes revisions only when it creates one, which needs a tracked field to change, so
	 * the wording is conditional on changes being saved. On a post or page the only tracked fields
	 * are its page scripts.
	 *
	 * @return array|null {
	 *     @type string $title  The short warning.
	 *     @type string $detail The numbers behind it.
	 * }
	 */
	protected function get_save_warning() {
		if ( ! $this->is_enabled() ) {
			return null;
		}

		$item_id = $this->get_edited_item_id();

		if ( is_null( $item_id ) ) {
			return null;
		}

		if ( $item_id > 0 && ! current_user_can( 'edit_post', $item_id ) ) {
			return null;
		}

		if ( 0 === $item_id && ! current_user_can( 'wpcode_edit_html_snippets' ) ) {
			return null;
		}

		// The save inserts its own revision before the trim runs, so the row about to be written
		// counts toward the limit: an item sitting exactly at the limit still loses its oldest.
		$excess = $this->count_item_excess( $item_id, 1 );

		if ( $excess < 1 ) {
			return null;
		}

		$keep = $this->get_keep_count();
		// Exact while $excess is unclamped, which the early return above guarantees.
		$rows = $excess + $keep - 1;

		$is_post = $item_id > 0 && 'wpcode' !== get_post_type( $item_id );

		if ( $is_post ) {
			$title = sprintf(
				/* translators: %s: number of revisions that will be deleted. */
				_n( 'Saving changes to the page scripts will delete %s revision.', 'Saving changes to the page scripts will delete %s revisions.', $excess, 'wpcode-premium' ),
				number_format_i18n( $excess )
			);
			/* translators: %1$s: revisions the page scripts have, %2$s: the configured limit, %3$s: revisions that will be deleted. */
			$detail = __( 'The page scripts have %1$s revisions and your limit is %2$s. Saving a change to them adds one more revision, so the %3$s oldest are removed. A save that does not change the page scripts removes nothing. This cannot be undone.', 'wpcode-premium' );
		} else {
			$title = sprintf(
				/* translators: %s: number of revisions that will be deleted. */
				_n( 'Saving changes will delete %s revision.', 'Saving changes will delete %s revisions.', $excess, 'wpcode-premium' ),
				number_format_i18n( $excess )
			);
			/* translators: %1$s: revisions this item has, %2$s: the configured limit, %3$s: revisions that will be deleted. */
			$detail = __( 'This item has %1$s revisions and your limit is %2$s. Saving a change adds one more revision, so the %3$s oldest are removed. A save without changes removes nothing. This cannot be undone.', 'wpcode-premium' );
		}

		return array(
			'title'  => $title,
			'detail' => sprintf(
				$detail,
				number_format_i18n( $rows ),
				number_format_i18n( $keep ),
				number_format_i18n( $excess )
			),
		);
	}

	/**
	 * Which item's edit screen are we on, if any.
	 *
	 * Snippets live on WPCode's own admin page rather than post.php, so both shapes are resolved
	 * here. Returns null when this is not an edit screen at all, which is distinct from 0: 0 is
	 * Header & Footer's real bucket id.
	 *
	 * @return int|null
	 */
	protected function get_edited_item_id() {
		// phpcs:disable WordPress.Security.NonceVerification.Recommended -- reading which screen we are on.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		// Header & Footer is its own bucket at snippet_id 0 and its screen carries no id, so it
		// has to be resolved from the page itself. It is also the one owner with no wp_posts row
		// and no way back, which makes it the one that most needs the warning.
		if ( 'wpcode-headers-footers' === $page ) {
			return 0;
		}

		if ( 'wpcode-snippet-manager' === $page && isset( $_GET['snippet_id'] ) ) {
			return $this->positive_id_or_null( sanitize_text_field( wp_unslash( $_GET['snippet_id'] ) ) );
		}

		if ( isset( $_GET['post'] ) && isset( $_GET['action'] ) && 'edit' === sanitize_key( wp_unslash( $_GET['action'] ) ) ) {
			return $this->positive_id_or_null( sanitize_text_field( wp_unslash( $_GET['post'] ) ) );
		}
		// phpcs:enable WordPress.Security.NonceVerification.Recommended

		return null;
	}

	/**
	 * Read a request value as a real item id, or nothing at all.
	 *
	 * Deliberately not absint(): absint( -5 ) is 5, so a negative id resolved to a real item's
	 * history, and an unparseable id resolved to 0, which is Header & Footer's own bucket. Either
	 * produced a confident warning about the wrong item on a screen that deletes data. On a snippet
	 * screen 0 means "new snippet", which owns nothing.
	 *
	 * @param string $value The sanitized request value.
	 *
	 * @return int|null
	 */
	protected function positive_id_or_null( $value ) {
		if ( ! ctype_digit( trim( (string) $value ) ) ) {
			return null;
		}

		$id = (int) $value;

		return $id > 0 ? $id : null;
	}

	/**
	 * Read the state of the current or last cleanup run.
	 *
	 * @return array Empty when no run has been started.
	 */
	public function get_cleanup_state() {
		$state = get_option( self::CLEANUP_OPTION, array() );

		return is_array( $state ) ? $state : array();
	}

	/**
	 * Forget the cleanup run state.
	 *
	 * @return void
	 */
	public function clear_cleanup_state() {
		delete_option( self::CLEANUP_OPTION );
	}

	/**
	 * The stored run, but only if it is still safe to continue without asking again.
	 *
	 * Resuming skips the confirmation dialog, which is the consent gate for an irreversible delete,
	 * so a run may only be picked up on exactly the terms it was agreed on. A run is abandoned when
	 * the feature has since been switched off, when the limit no longer matches the one the dialog
	 * named, or when it is old enough that the site it described has probably moved on. Abandoning
	 * clears the state, so the row falls back to offering a fresh, freshly-confirmed run.
	 *
	 * @return array Empty when there is nothing to resume.
	 */
	public function resumable_cleanup_state() {
		$state = $this->get_cleanup_state();

		if ( empty( $state ) || ! isset( $state['status'] ) || 'running' !== $state['status'] ) {
			return array();
		}

		$stale = ! $this->is_enabled()
			|| ! isset( $state['keep'] ) || $this->get_keep_count() !== absint( $state['keep'] )
			|| ! isset( $state['updated'] ) || ( time() - absint( $state['updated'] ) ) > self::CLEANUP_MAX_AGE;

		if ( $stale ) {
			$this->clear_cleanup_state();

			return array();
		}

		return $state;
	}

	/**
	 * Do one bounded batch of the user-triggered cleanup, and report the run state.
	 *
	 * This is the retroactive half of the feature: the limit alone only trims an item when it next
	 * gets a revision, so without this an item nobody edits again stays over the limit forever.
	 *
	 * Deliberately resumable rather than scheduled. The state lives in an option, so a caller that
	 * stops (a closed tab, a navigation) leaves a valid half-finished run that the next call
	 * continues, which is what a cron job was wanted for, without depending on cron firing at all.
	 *
	 * Orphans go first: their parent post is gone, so no limit applies to them, and clearing them
	 * up front keeps each step's budget for rows the user's own limit selects.
	 *
	 * @return array The run state.
	 */
	public function cleanup_step() {
		if ( ! $this->is_enabled() ) {
			return array( 'status' => 'idle' );
		}

		$this->step_failed = false;

		$state = $this->resumable_cleanup_state();

		if ( empty( $state ) ) {
			$over    = $this->count_over_limit();
			$orphans = $this->count_orphans();
			$state   = array(
				'status'  => 'running',
				// The limit this run is working to. A later step will not continue under a
				// different one, because the dialog named this number.
				'keep'    => $this->get_keep_count(),
				'total'   => $over['revisions'] + $orphans,
				'removed' => 0,
				'items'   => $over['items'],
				'orphans' => $orphans,
				'started' => time(),
				'updated' => time(),
			);

			if ( 0 === $state['total'] ) {
				$state['status'] = 'done';
				update_option( self::CLEANUP_OPTION, $state, false );

				return $state;
			}
		}

		$removed = $this->cleanup_orphan_batch();

		if ( 0 === $removed ) {
			$removed = $this->cleanup_over_limit_batch();
		}

		$state['removed'] = absint( isset( $state['removed'] ) ? $state['removed'] : 0 ) + $removed;
		$state['updated'] = time();

		if ( $this->step_failed ) {
			// A query errored. Stop, and say so: the caller must not render this as a clean site.
			$state['status'] = 'error';
		} elseif ( 0 === $removed ) {
			// No rows went. That is only completion if nothing is actually left, so check rather
			// than assume: a lock-wait timeout or a row another process holds also removes nothing.
			$over = $this->count_over_limit();

			$state['status'] = ( 0 === $over['items'] && 0 === $this->count_orphans() ) ? 'done' : 'error';
		}

		update_option( self::CLEANUP_OPTION, $state, false );

		return $state;
	}

	/**
	 * Remove one chunk of orphaned rows.
	 *
	 * Ids are selected and then deleted by primary key because a multi-table DELETE cannot take a
	 * LIMIT in MySQL.
	 *
	 * @return int Rows removed.
	 */
	protected function cleanup_orphan_batch() {
		global $wpdb;

		$ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT r.revision_id FROM {$wpdb->prefix}wpcode_revisions r
				LEFT JOIN {$wpdb->posts} p ON r.snippet_id = p.ID
				WHERE r.snippet_id > 0 AND p.ID IS NULL
				LIMIT %d",
				self::TRIM_ROW_BATCH
			)
		);

		if ( empty( $ids ) ) {
			return 0;
		}

		$ids          = array_map( 'absint', $ids );
		$placeholders = implode( ',', array_fill( 0, count( $ids ), '%d' ) );

		$deleted = $wpdb->query( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.NotPrepared
			$wpdb->prepare(
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared, WordPress.DB.PreparedSQLPlaceholders.UnfinishedPrepare -- $placeholders is a generated run of %d; every value is bound below.
				"DELETE FROM {$wpdb->prefix}wpcode_revisions WHERE revision_id IN ( $placeholders )",
				$ids
			)
		);

		if ( false === $deleted ) {
			$this->step_failed = true;

			return 0;
		}

		$removed = (int) $deleted;

		if ( $removed > 0 ) {
			$this->counts = array();
			$this->flush_revision_cache();
		}

		return $removed;
	}

	/**
	 * Trim one batch of over-limit items, reusing the live trim so there is one implementation.
	 *
	 * @return int Rows removed.
	 */
	protected function cleanup_over_limit_batch() {
		global $wpdb;

		$keep = $this->get_keep_count();

		$ids = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT r.snippet_id FROM {$wpdb->prefix}wpcode_revisions r
				LEFT JOIN {$wpdb->posts} p ON r.snippet_id = p.ID
				WHERE r.snippet_id = 0 OR p.ID IS NOT NULL
				GROUP BY r.snippet_id
				HAVING COUNT(*) > %d
				LIMIT %d",
				$keep,
				self::CLEANUP_ITEM_BATCH
			)
		);

		$removed = 0;

		foreach ( (array) $ids as $snippet_id ) {
			$removed += $this->trim_item( absint( $snippet_id ) );
		}

		return $removed;
	}

	/**
	 * Count what the limit would remove from revisions that already exist.
	 *
	 * Sums the excess per bucket rather than counting buckets: one snippet 500 over the limit and
	 * 500 snippets one over it are very different numbers to put in front of someone.
	 *
	 * Every live bucket counts, Header & Footer's snippet_id 0 included, because the limit applies
	 * to it like anything else. Buckets whose parent post is gone are excluded outright, not merely
	 * their excess: count_orphans() reports all of their rows, and the cleanup removes them whole.
	 * Counting them here as well would double-count the overlap, inflating the figure in the
	 * confirmation dialog and letting a run's progress exceed 100%.
	 *
	 * @return array {
	 *     @type int $items     Buckets above the limit.
	 *     @type int $revisions Rows that would be removed from them.
	 * }
	 */
	public function count_over_limit() {
		$keep = $this->get_keep_count();

		if ( isset( $this->counts[ 'over_' . $keep ] ) ) {
			return $this->counts[ 'over_' . $keep ];
		}

		global $wpdb;

		$row = $wpdb->get_row( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->prepare(
				"SELECT COUNT(*) AS items, COALESCE( SUM( excess ), 0 ) AS revisions FROM (
					SELECT COUNT(*) - %d AS excess
					FROM {$wpdb->prefix}wpcode_revisions r
					LEFT JOIN {$wpdb->posts} p ON r.snippet_id = p.ID
					WHERE r.snippet_id = 0 OR p.ID IS NOT NULL
					GROUP BY r.snippet_id
					HAVING COUNT(*) > %d
				) x",
				$keep,
				$keep
			),
			ARRAY_A
		);

		$this->counts[ 'over_' . $keep ] = array(
			'items'     => isset( $row['items'] ) ? absint( $row['items'] ) : 0,
			'revisions' => isset( $row['revisions'] ) ? absint( $row['revisions'] ) : 0,
		);

		return $this->counts[ 'over_' . $keep ];
	}

	/**
	 * Count rows whose snippet or page no longer exists.
	 *
	 * The snippet_id > 0 guard is load-bearing: Header & Footer rows are stored against 0 and have
	 * no wp_posts row, so without it every one of them reads as orphaned.
	 *
	 * @return int
	 */
	public function count_orphans() {
		if ( isset( $this->counts['orphans'] ) ) {
			return $this->counts['orphans'];
		}

		global $wpdb;

		$this->counts['orphans'] = (int) $wpdb->get_var( // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			"SELECT COUNT(*) FROM {$wpdb->prefix}wpcode_revisions r
			LEFT JOIN {$wpdb->posts} p ON r.snippet_id = p.ID
			WHERE r.snippet_id > 0 AND p.ID IS NULL"
		);

		return $this->counts['orphans'];
	}

	/**
	 * Is revision removal enabled?
	 *
	 * @return bool
	 */
	public function is_enabled() {
		return (bool) wpcode()->settings->get_option( 'revisions_removal_enabled', false );
	}

	/**
	 * How many revisions each item keeps.
	 *
	 * Bounded identically here and in the save handler, so a stored value can never fall outside
	 * what this returns. The floor is MIN_KEEP_COUNT: a Header & Footer or page-scripts save inserts
	 * the live state and then the back-filled previous state, and the trim keeps the newest rows,
	 * so a limit below 2 deletes the live state. 5 leaves room for both.
	 *
	 * Cast rather than absint(): absint( -5 ) is 5, so a negative stored value would silently
	 * become a real limit instead of falling to the floor.
	 *
	 * @return int
	 */
	public function get_keep_count() {
		return max( self::MIN_KEEP_COUNT, min( self::MAX_KEEP_COUNT, (int) wpcode()->settings->get_option( 'revisions_keep_count', 30 ) ) );
	}
}
