<?php
/**
 * Computes what restoring a revision would change and renders the revisions page parts.
 *
 * @package WPCode
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class WPCode_Revisions_View.
 */
class WPCode_Revisions_View {

	/**
	 * Revision types.
	 *
	 * @var string
	 */
	const TYPE_SNIPPET       = 'snippet';
	const TYPE_HEADER_FOOTER = 'header_footer';
	const TYPE_PAGE_SCRIPTS  = 'page_scripts';

	/**
	 * The revision type.
	 *
	 * @var string
	 */
	private $type;

	/**
	 * Snippet id, post id, or 0 for Header & Footer.
	 *
	 * @var int
	 */
	private $parent_id;

	/**
	 * Revisions newest first, loaded on demand.
	 *
	 * @var array
	 */
	private $revisions;

	/**
	 * Live data for the item, loaded on demand.
	 *
	 * @var array
	 */
	private $current_data;

	/**
	 * Rows per revision id.
	 *
	 * @var array
	 */
	private $rows = array();

	/**
	 * The revision matching the live item, or false when none does. Loaded on demand.
	 *
	 * @var stdClass|false
	 */
	private $current_revision;

	/**
	 * The live side of each row, converted once per view. Loaded on demand.
	 *
	 * @var array
	 */
	private $current_display;

	/**
	 * Constructor.
	 *
	 * @param string $type      One of the TYPE_* constants.
	 * @param int    $parent_id The snippet id, post id, or 0 for Header & Footer.
	 */
	public function __construct( $type, $parent_id ) {
		$this->type      = $type;
		$this->parent_id = absint( $parent_id );
	}

	/**
	 * Build a view for the item a revision belongs to.
	 *
	 * @param int $revision_id The revision id.
	 *
	 * @return WPCode_Revisions_View|null Null when the revision does not exist.
	 */
	public static function for_revision( $revision_id ) {
		$parent_id = wpcode()->revisions->get_revision_parent_id( absint( $revision_id ) );

		if ( false === $parent_id ) {
			return null;
		}
		if ( 0 === $parent_id ) {
			return new self( self::TYPE_HEADER_FOOTER, 0 );
		}

		$type = wpcode_get_post_type() === get_post_type( $parent_id ) ? self::TYPE_SNIPPET : self::TYPE_PAGE_SCRIPTS;

		return new self( $type, $parent_id );
	}

	/**
	 * Get the revision type.
	 *
	 * @return string
	 */
	public function get_type() {
		return $this->type;
	}

	/**
	 * Get the parent id.
	 *
	 * @return int
	 */
	public function get_parent_id() {
		return $this->parent_id;
	}

	/**
	 * All revisions of the item, newest first by revision id.
	 *
	 * @return array
	 */
	public function get_revisions() {
		if ( ! isset( $this->revisions ) ) {
			$revisions = wpcode()->revisions->get_snippet_revisions( $this->parent_id );
			$revisions = is_array( $revisions ) ? array_values( $revisions ) : array();

			usort(
				$revisions,
				function ( $a, $b ) {
					return absint( $b->revision_id ) - absint( $a->revision_id );
				}
			);

			$this->revisions = $revisions;
		}

		return $this->revisions;
	}

	/**
	 * Find one revision of this item.
	 *
	 * @param int $revision_id The revision id.
	 *
	 * @return stdClass|null
	 */
	public function get_revision( $revision_id ) {
		$revision_id = absint( $revision_id );

		foreach ( $this->get_revisions() as $revision ) {
			if ( absint( $revision->revision_id ) === $revision_id ) {
				return $revision;
			}
		}

		return null;
	}

	/**
	 * The newest revision.
	 *
	 * @return stdClass|null
	 */
	public function get_newest() {
		$revisions = $this->get_revisions();

		return empty( $revisions ) ? null : $revisions[0];
	}

	/**
	 * Whether a revision is the newest one.
	 *
	 * @param stdClass $revision The revision.
	 *
	 * @return bool
	 */
	public function is_newest( $revision ) {
		$newest = $this->get_newest();

		return $newest && absint( $newest->revision_id ) === absint( $revision->revision_id );
	}

	/**
	 * The newest revision whose data matches the live item.
	 *
	 * A first Header & Footer or page scripts revision inserts the new state before the
	 * back-filled previous state, so the highest id is not always the live data.
	 *
	 * @return stdClass|null
	 */
	public function get_current_revision() {
		if ( ! isset( $this->current_revision ) ) {
			$this->current_revision = false;

			foreach ( $this->get_revisions() as $revision ) {
				if ( 0 === count( $this->get_rows( $revision, false ) ) ) {
					$this->current_revision = $revision;
					break;
				}
			}
		}

		return false === $this->current_revision ? null : $this->current_revision;
	}

	/**
	 * Whether a revision is the one matching the live item.
	 *
	 * @param stdClass $revision The revision.
	 *
	 * @return bool
	 */
	public function is_current( $revision ) {
		$current = $this->get_current_revision();

		return $current && absint( $current->revision_id ) === absint( $revision->revision_id );
	}

	/**
	 * The live data of the item, in the same shape as a revision's data.
	 *
	 * @return array
	 */
	public function get_current_data() {
		if ( isset( $this->current_data ) ) {
			return $this->current_data;
		}

		if ( self::TYPE_HEADER_FOOTER === $this->type ) {
			$this->current_data = array(
				'ihaf_insert_header' => (string) get_option( 'ihaf_insert_header', '' ),
				'ihaf_insert_footer' => (string) get_option( 'ihaf_insert_footer', '' ),
				'ihaf_insert_body'   => (string) get_option( 'ihaf_insert_body', '' ),
			);
		} elseif ( self::TYPE_PAGE_SCRIPTS === $this->type ) {
			$this->current_data = array();
			foreach ( array( '_wpcode_header_scripts', '_wpcode_footer_scripts', '_wpcode_body_scripts', '_wpcode_page_snippets' ) as $key ) {
				$this->current_data[ $key ] = get_post_meta( $this->parent_id, $key, true );
			}
		} else {
			$data = wpcode()->revisions->get_all_snippet_revision_data( wpcode_get_snippet( $this->parent_id ) );
			$this->current_data = is_array( $data ) ? $data : array();
		}

		return $this->current_data;
	}

	/**
	 * Decode a revision's stored data.
	 *
	 * @param stdClass $revision The revision.
	 *
	 * @return array
	 */
	public function get_revision_data( $revision ) {
		$data = json_decode( $revision->revision_data, true );

		return is_array( $data ) ? $data : array();
	}

	/**
	 * The fields restoring this revision would change, in display order.
	 *
	 * @param stdClass $revision The revision.
	 * @param bool     $render   Whether code rows carry their diff markup. False only counts them.
	 *
	 * @return array
	 */
	public function get_rows( $revision, $render = true ) {
		$id   = absint( $revision->revision_id );
		$mode = $render ? 'full' : 'count';

		if ( isset( $this->rows[ $id ][ $mode ] ) ) {
			return $this->rows[ $id ][ $mode ];
		}

		$current = $this->get_current_data();
		$after   = $this->get_revision_data( $revision );

		if ( self::TYPE_HEADER_FOOTER === $this->type ) {
			$rows = $this->header_footer_rows( $current, $after, $render );
		} elseif ( self::TYPE_PAGE_SCRIPTS === $this->type ) {
			$rows = $this->page_scripts_rows( $current, $after, $render );
		} else {
			$rows = $this->snippet_rows( $current, $after, $render );
		}

		$this->rows[ $id ][ $mode ] = array_values( array_filter( $rows ) );

		return $this->rows[ $id ][ $mode ];
	}

	/**
	 * Rows for a snippet revision.
	 *
	 * @param array $current Live data.
	 * @param array $after   Revision data.
	 * @param bool  $render  Whether code rows carry their diff markup.
	 *
	 * @return array
	 */
	private function snippet_rows( $current, $after, $render = true ) {
		$defaults = array(
			'title'                => '',
			'code'                 => '',
			'code_type'            => '',
			'auto_insert'          => 0,
			'location'             => '',
			'location_extra'       => '',
			'insert_number'        => '',
			'custom_shortcode'     => '',
			'shortcode_attributes' => array(),
			'priority'             => 10,
			'device_type'          => 'any',
			'schedule'             => array(),
			'use_rules'            => false,
			'rules'                => array(),
			'tags'                 => array(),
			'note'                 => '',
		);
		$stored   = $after;
		$current  = wp_parse_args( $current, $defaults );
		$after    = wp_parse_args( $after, $defaults );
		$live     = $this->snippet_current_display( $current );

		$rows = array(
			$this->value_row( 'title', __( 'Title', 'wpcode-premium' ), $current['title'], $after['title'] ),
			$this->value_row( 'code_type', __( 'Code Type', 'wpcode-premium' ), $live['code_type'], self::code_type_text( $after['code_type'] ) ),
			$this->code_row( 'code', __( 'Code', 'wpcode-premium' ), $current['code'], $after['code'], $render ),
			$this->value_row( 'auto_insert', __( 'Insert Method', 'wpcode-premium' ), $live['auto_insert'], self::insert_method_text( $after['auto_insert'] ) ),
			$this->value_row( 'location', __( 'Auto-insert Location', 'wpcode-premium' ), $live['location'], self::location_text( $after ) ),
			$this->value_row( 'location_extra', __( 'Location-specific setting', 'wpcode-premium' ), $live['location_extra'], WPCode_Admin_Page_Revisions::location_extra_to_text( $after ) ),
			$this->value_row( 'insert_number', __( 'Insert Number', 'wpcode-premium' ), $live['insert_number'], self::insert_number_text( $after['insert_number'] ) ),
			$this->value_row( 'custom_shortcode', __( 'Custom Shortcode', 'wpcode-premium' ), $current['custom_shortcode'], $after['custom_shortcode'] ),
			$this->value_row( 'shortcode_attributes', __( 'Shortcode Attributes', 'wpcode-premium' ), $current['shortcode_attributes'], $after['shortcode_attributes'] ),
			$this->value_row( 'priority', __( 'Priority', 'wpcode-premium' ), $current['priority'], $after['priority'] ),
			$this->value_row( 'device_type', __( 'Device Type', 'wpcode-premium' ), $live['device_type'], self::device_type_text( $after['device_type'] ) ),
			$this->value_row( 'schedule', __( 'Schedule', 'wpcode-premium' ), $live['schedule'], self::schedule_text( $after['schedule'] ) ),
			$this->value_row( 'rules', __( 'Conditional Logic', 'wpcode-premium' ), $live['rules'], self::rules_text( $after ) ),
			$this->value_row( 'tags', __( 'Tags', 'wpcode-premium' ), $current['tags'], $after['tags'] ),
			$this->value_row( 'note', __( 'Note', 'wpcode-premium' ), $current['note'], $after['note'] ),
		);

		return $this->rows_for_stored_keys( $rows, $stored );
	}

	/**
	 * Keep only the rows whose field the revision actually carries.
	 *
	 * WPCode_Snippet::save() writes a field only when the restored data holds its key, so a key an
	 * older revision row never stored leaves the live value untouched instead of resetting it.
	 *
	 * @param array $rows   The rows, null entries included.
	 * @param array $stored The revision data as stored, before the defaults are filled in.
	 *
	 * @return array
	 */
	private function rows_for_stored_keys( $rows, $stored ) {
		// Conditional logic is restored from either key; every other row is named after its key.
		$drivers = array( 'rules' => array( 'use_rules', 'rules' ) );
		$keys    = array_keys( $stored );
		$kept    = array();

		foreach ( $rows as $row ) {
			if ( empty( $row ) ) {
				continue;
			}

			$driving = isset( $drivers[ $row['key'] ] ) ? $drivers[ $row['key'] ] : array( $row['key'] );

			if ( array_intersect( $driving, $keys ) ) {
				$kept[] = $row;
			}
		}

		return $kept;
	}

	/**
	 * The converted live side of the snippet rows. It is the same for every revision, so the
	 * conversions run once per view instead of once per row.
	 *
	 * @param array $current Live data filled with the defaults.
	 *
	 * @return array
	 */
	private function snippet_current_display( $current ) {
		if ( ! isset( $this->current_display ) ) {
			$this->current_display = array(
				'code_type'      => self::code_type_text( $current['code_type'] ),
				'auto_insert'    => self::insert_method_text( $current['auto_insert'] ),
				'location'       => self::location_text( $current ),
				'location_extra' => WPCode_Admin_Page_Revisions::location_extra_to_text( $current ),
				'insert_number'  => self::insert_number_text( $current['insert_number'] ),
				'device_type'    => self::device_type_text( $current['device_type'] ),
				'schedule'       => self::schedule_text( $current['schedule'] ),
				'rules'          => self::rules_text( $current ),
			);
		}

		return $this->current_display;
	}

	/**
	 * Rows for a Header & Footer revision.
	 *
	 * The Header & Footer page stores the request slashed, so both the live option and the
	 * revision hold slashed code and both sides are unslashed for display only.
	 *
	 * @param array $current Live data.
	 * @param array $after   Revision data.
	 * @param bool  $render  Whether code rows carry their diff markup.
	 *
	 * @return array
	 */
	private function header_footer_rows( $current, $after, $render = true ) {
		$labels = array(
			'ihaf_insert_header' => __( 'Header', 'wpcode-premium' ),
			'ihaf_insert_footer' => __( 'Footer', 'wpcode-premium' ),
			'ihaf_insert_body'   => __( 'Body', 'wpcode-premium' ),
		);
		$rows   = array();

		foreach ( $labels as $key => $label ) {
			$rows[] = $this->code_row(
				$key,
				$label,
				isset( $current[ $key ] ) ? wp_unslash( $current[ $key ] ) : '',
				isset( $after[ $key ] ) ? wp_unslash( $after[ $key ] ) : '',
				$render
			);
		}

		return $rows;
	}

	/**
	 * Rows for a Page Scripts revision.
	 *
	 * The metabox stores the revision from the slashed request while the meta API unslashes the
	 * live value, so only the revision side is unslashed for display.
	 *
	 * @param array $current Live data.
	 * @param array $after   Revision data.
	 * @param bool  $render  Whether code rows carry their diff markup.
	 *
	 * @return array
	 */
	private function page_scripts_rows( $current, $after, $render = true ) {
		$areas   = array(
			'_wpcode_header_scripts' => __( 'Header Scripts', 'wpcode-premium' ),
			'_wpcode_footer_scripts' => __( 'Footer Scripts', 'wpcode-premium' ),
			'_wpcode_body_scripts'   => __( 'Body Scripts', 'wpcode-premium' ),
		);
		$devices = array(
			'any'     => __( 'Any Device Type', 'wpcode-premium' ),
			'desktop' => __( 'Desktop Only', 'wpcode-premium' ),
			'mobile'  => __( 'Mobile Only', 'wpcode-premium' ),
		);
		$rows    = array();
		$live    = $this->page_scripts_current_display( $current, array_keys( $areas ) );

		foreach ( $areas as $key => $label ) {
			$before_area = isset( $current[ $key ] ) && is_array( $current[ $key ] ) ? $current[ $key ] : array();
			$after_area  = isset( $after[ $key ] ) && is_array( $after[ $key ] ) ? $after[ $key ] : array();

			$rows[] = $this->value_row(
				$key . '_disable_global',
				/* translators: %s: the scripts area, for example Header Scripts. */
				sprintf( __( '%s: Disable Global Scripts', 'wpcode-premium' ), $label ),
				$live[ $key . '_disable_global' ],
				self::enabled_text( ! empty( $after_area['disable_global'] ) )
			);

			foreach ( $devices as $device => $device_label ) {
				$rows[] = $this->code_row(
					$key . '_' . $device,
					sprintf( /* translators: 1: scripts area label, 2: device type label. */ _x( '%1$s (%2$s)', 'page scripts revision row label', 'wpcode-premium' ), $label, $device_label ),
					isset( $before_area[ $device ] ) ? $before_area[ $device ] : '',
					isset( $after_area[ $device ] ) ? wp_unslash( $after_area[ $device ] ) : '',
					$render
				);
			}
		}

		$rows[] = $this->value_row(
			'_wpcode_page_snippets',
			__( 'Custom Code Snippets', 'wpcode-premium' ),
			$live['_wpcode_page_snippets'],
			WPCode_Admin_Page_Revisions::page_scripts_snippets_to_text( isset( $after['_wpcode_page_snippets'] ) ? $after['_wpcode_page_snippets'] : array() )
		);

		return $rows;
	}

	/**
	 * The converted live side of the page script rows, built once per view. Listing the live
	 * snippets loads each of them, so this must not run per revision.
	 *
	 * @param array $current Live data.
	 * @param array $areas   The script area meta keys.
	 *
	 * @return array
	 */
	private function page_scripts_current_display( $current, $areas ) {
		if ( ! isset( $this->current_display ) ) {
			$display = array();

			foreach ( $areas as $key ) {
				$area = isset( $current[ $key ] ) && is_array( $current[ $key ] ) ? $current[ $key ] : array();

				$display[ $key . '_disable_global' ] = self::enabled_text( ! empty( $area['disable_global'] ) );
			}

			$display['_wpcode_page_snippets'] = WPCode_Admin_Page_Revisions::page_scripts_snippets_to_text( isset( $current['_wpcode_page_snippets'] ) ? $current['_wpcode_page_snippets'] : array() );

			$this->current_display = $display;
		}

		return $this->current_display;
	}

	/**
	 * A "current => after" row, or null when both display the same.
	 *
	 * @param string $key   Row key.
	 * @param string $label Row label.
	 * @param mixed  $from  Live value.
	 * @param mixed  $to    Revision value.
	 *
	 * @return array|null
	 */
	private function value_row( $key, $label, $from, $to ) {
		$from = self::display_value( $from );
		$to   = self::display_value( $to );

		if ( $from === $to ) {
			return null;
		}

		return array(
			'key'   => $key,
			'label' => $label,
			'kind'  => 'value',
			'from'  => $from,
			'to'    => $to,
			'muted' => in_array( $to, self::muted_values(), true ),
		);
	}

	/**
	 * A code diff row, or null when the code is unchanged.
	 *
	 * @param string $key    Row key.
	 * @param string $label  Row label.
	 * @param string $from   Live code.
	 * @param string $to     Revision code.
	 * @param bool   $render Whether the row carries its diff markup. False only reports the change.
	 *
	 * @return array|null
	 */
	private function code_row( $key, $label, $from, $to, $render = true ) {
		$from = is_scalar( $from ) ? (string) $from : '';
		$to   = is_scalar( $to ) ? (string) $to : '';

		if ( ! $render ) {
			if ( ! WPCode_Revisions_Code_Diff::differs( $from, $to ) ) {
				return null;
			}

			return array(
				'key'   => $key,
				'label' => $label,
				'kind'  => 'code',
				'html'  => '',
			);
		}

		$html = WPCode_Revisions_Code_Diff::render( $from, $to );

		if ( '' === $html ) {
			return null;
		}

		return array(
			'key'   => $key,
			'label' => $label,
			'kind'  => 'code',
			'html'  => $html,
		);
	}

	/**
	 * Turn a stored value into the string shown to the user.
	 *
	 * @param mixed $value The value.
	 *
	 * @return string
	 */
	private static function display_value( $value ) {
		if ( is_array( $value ) ) {
			$parts = array();
			foreach ( $value as $part ) {
				if ( is_scalar( $part ) && '' !== trim( (string) $part ) ) {
					$parts[] = trim( (string) $part );
				}
			}
			$value = implode( ', ', $parts );
		} elseif ( is_bool( $value ) ) {
			$value = $value ? '1' : '';
		}

		$value = trim( (string) $value );

		return '' === $value ? __( 'Empty', 'wpcode-premium' ) : $value;
	}

	/**
	 * Display values that read as "nothing", shown in the muted colour.
	 *
	 * @return array
	 */
	private static function muted_values() {
		return array(
			__( 'None', 'wpcode-premium' ),
			__( 'Off', 'wpcode-premium' ),
			__( 'Empty', 'wpcode-premium' ),
			__( 'Not scheduled', 'wpcode-premium' ),
		);
	}

	/**
	 * Insert method label.
	 *
	 * @param mixed $auto_insert The stored auto_insert flag.
	 *
	 * @return string
	 */
	private static function insert_method_text( $auto_insert ) {
		return 1 === absint( $auto_insert ) ? __( 'Auto Insert', 'wpcode-premium' ) : __( 'Shortcode', 'wpcode-premium' );
	}

	/**
	 * Code type label, or the stored key when the viewer's role cannot see that type.
	 *
	 * @param string $code_type The stored code type key.
	 *
	 * @return string
	 */
	private static function code_type_text( $code_type ) {
		$label = WPCode_Admin_Page_Revisions::code_type_to_label( $code_type );

		return '' === $label ? (string) $code_type : $label;
	}

	/**
	 * Insert number, normalised the way WPCode_Snippet::get_auto_insert_number() reads it.
	 *
	 * @param mixed $value The stored insert number.
	 *
	 * @return string
	 */
	private static function insert_number_text( $value ) {
		return (string) max( 1, absint( $value ) );
	}

	/**
	 * Auto-insert location label, or None when auto insert is off.
	 *
	 * @param array $data Snippet data with auto_insert and location keys.
	 *
	 * @return string
	 */
	private static function location_text( $data ) {
		if ( 1 !== absint( $data['auto_insert'] ) || empty( $data['location'] ) ) {
			return __( 'None', 'wpcode-premium' );
		}

		$label = wpcode_find_location_label( $data['location'] );

		return '' === $label ? __( 'None', 'wpcode-premium' ) : $label;
	}

	/**
	 * Device type label.
	 *
	 * @param string $device_type any, desktop or mobile.
	 *
	 * @return string
	 */
	private static function device_type_text( $device_type ) {
		$labels = array(
			'any'     => __( 'Any device type', 'wpcode-premium' ),
			'desktop' => __( 'Desktop only', 'wpcode-premium' ),
			'mobile'  => __( 'Mobile only', 'wpcode-premium' ),
		);
		$device_type = empty( $device_type ) ? 'any' : $device_type;

		return isset( $labels[ $device_type ] ) ? $labels[ $device_type ] : (string) $device_type;
	}

	/**
	 * Schedule label.
	 *
	 * @param mixed $schedule The stored schedule array.
	 *
	 * @return string
	 */
	private static function schedule_text( $schedule ) {
		$start = is_array( $schedule ) && ! empty( $schedule['start'] ) ? $schedule['start'] : '';
		$end   = is_array( $schedule ) && ! empty( $schedule['end'] ) ? $schedule['end'] : '';

		if ( '' === $start && '' === $end ) {
			return __( 'Not scheduled', 'wpcode-premium' );
		}

		return WPCode_Admin_Page_Revisions::schedule_to_text( $schedule );
	}

	/**
	 * Conditional logic label: Off, or the rules as text.
	 *
	 * @param array $data Snippet data with use_rules and rules keys.
	 *
	 * @return string
	 */
	private static function rules_text( $data ) {
		if ( empty( $data['use_rules'] ) ) {
			return __( 'Off', 'wpcode-premium' );
		}

		$text = trim( WPCode_Admin_Page_Revisions::conditional_logic_to_text( is_array( $data['rules'] ) ? $data['rules'] : array() ) );

		return '' === $text ? __( 'On', 'wpcode-premium' ) : $text;
	}

	/**
	 * Enabled or Disabled.
	 *
	 * @param bool $enabled The flag.
	 *
	 * @return string
	 */
	private static function enabled_text( $enabled ) {
		return $enabled ? __( 'Enabled', 'wpcode-premium' ) : __( 'Disabled', 'wpcode-premium' );
	}

	/**
	 * Offset-summed timestamp of the GMT created column, the form date_i18n() expects.
	 *
	 * @param string $created The created column (GMT).
	 *
	 * @return int
	 */
	public static function timestamp( $created ) {
		return (int) strtotime( get_date_from_gmt( $created ) );
	}

	/**
	 * Sidebar date: "Just now", "Today, 10:42", "Sep 6, 16:05" or "Sep 6, 2025, 16:05".
	 *
	 * @param string $created The created column (GMT).
	 *
	 * @return string
	 */
	public static function format_list_date( $created ) {
		$age = time() - (int) strtotime( $created . ' UTC' );
		if ( $age >= 0 && $age < MINUTE_IN_SECONDS ) {
			return __( 'Just now', 'wpcode-premium' );
		}

		$timestamp = self::timestamp( $created );
		$time      = self::format_time( $created );

		if ( date_i18n( 'Y-m-d', $timestamp ) === date_i18n( 'Y-m-d' ) ) {
			/* translators: %s: the time of day. */
			return sprintf( __( 'Today, %s', 'wpcode-premium' ), $time );
		}

		$same_year = date_i18n( 'Y', $timestamp ) === date_i18n( 'Y' );
		$format    = $same_year ? _x( 'M j', 'revision list date in the current year', 'wpcode-premium' ) : _x( 'M j, Y', 'revision list date in another year', 'wpcode-premium' );

		/* translators: 1: the date, 2: the time of day. */
		return sprintf( _x( '%1$s, %2$s', 'revision list date and time', 'wpcode-premium' ), date_i18n( $format, $timestamp ), $time );
	}

	/**
	 * "Sep 3, 2026".
	 *
	 * @param string $created The created column (GMT).
	 *
	 * @return string
	 */
	public static function format_date( $created ) {
		return date_i18n( _x( 'M j, Y', 'revision date', 'wpcode-premium' ), self::timestamp( $created ) );
	}

	/**
	 * The time of day in the site's time format.
	 *
	 * @param string $created The created column (GMT).
	 *
	 * @return string
	 */
	public static function format_time( $created ) {
		return date_i18n( get_option( 'time_format' ), self::timestamp( $created ) );
	}

	/**
	 * "Sep 3, 2026 at 09:18".
	 *
	 * @param string $created The created column (GMT).
	 *
	 * @return string
	 */
	public static function format_date_time( $created ) {
		/* translators: 1: the date, 2: the time of day. */
		return sprintf( _x( '%1$s at %2$s', 'revision date and time', 'wpcode-premium' ), self::format_date( $created ), self::format_time( $created ) );
	}

	/**
	 * The sidebar card with one row per revision.
	 *
	 * @param int $selected_id The revision shown in the detail panel.
	 * @param int $restored_id The revision just restored, or 0.
	 *
	 * @return string
	 */
	public function render_sidebar( $selected_id, $restored_id = 0 ) {
		$selected_id = absint( $selected_id );
		$restored_id = absint( $restored_id );

		$html = '<div class="wpcode-revisions-sidebar"><div class="wpcode-revisions-sidebar-title">' . esc_html__( 'Revisions', 'wpcode-premium' ) . '</div><div class="wpcode-revisions-sidebar-list">';

		foreach ( $this->get_revisions() as $revision ) {
			$id         = absint( $revision->revision_id );
			$is_current = $this->is_current( $revision );
			$classes    = 'wpcode-revisions-row';

			if ( $is_current ) {
				$classes .= ' wpcode-revisions-row-current';
			}
			if ( $id === $selected_id ) {
				$classes .= ' wpcode-revisions-row-selected';
			}

			$count = count( $this->get_rows( $revision, false ) );

			if ( $is_current ) {
				$badge = __( 'Current', 'wpcode-premium' );
			} elseif ( $id === $restored_id ) {
				$badge = __( 'Restored', 'wpcode-premium' );
			} elseif ( 0 === $count ) {
				// Identical to the live item but not the row marked Current.
				$badge = __( 'Same as current', 'wpcode-premium' );
			} else {
				/* translators: %d: number of fields restoring the revision would change. */
				$badge = $count ? sprintf( _n( '%d change', '%d changes', $count, 'wpcode-premium' ), $count ) : '';
			}

			$author      = get_the_author_meta( 'display_name', $revision->author_id );
			$badge_class = 'wpcode-revisions-row-badge';

			// The only badge long enough to take width from the date, so it wraps instead.
			if ( ! $is_current && $id !== $restored_id && 0 === $count ) {
				$badge_class .= ' wpcode-revisions-row-badge-wrap';
			}

			$html .= sprintf(
				'<a href="%1$s" class="%2$s" data-revision="%3$d"%4$s><span class="wpcode-revisions-row-main">%5$s<span class="wpcode-revisions-row-text"><span class="wpcode-revisions-row-meta">%8$s</span><span class="wpcode-revisions-row-author" title="%6$s">%7$s</span></span></span>%9$s</a>',
				esc_url( $this->get_view_url( $id ) ),
				esc_attr( $classes ),
				$id,
				$id === $selected_id ? ' aria-current="true"' : '',
				get_avatar( $revision->author_id, 26 ),
				esc_attr( $author ),
				esc_html( $author ),
				esc_html( self::format_list_date( $revision->created ) ),
				'' !== $badge ? '<span class="' . esc_attr( $badge_class ) . '">' . esc_html( $badge ) . '</span>' : ''
			);
		}

		return $html . '</div></div>';
	}

	/**
	 * The inner markup of the detail panel for one revision.
	 *
	 * @param stdClass $revision      The revision.
	 * @param int      $restored_id   The revision just restored, or 0.
	 * @param bool     $restore_error Whether the last restore attempt was refused.
	 *
	 * @return string
	 */
	public function render_detail( $revision, $restored_id = 0, $restore_error = false ) {
		$id   = absint( $revision->revision_id );
		$rows = $this->get_rows( $revision );

		if ( $this->is_current( $revision ) ) {
			$heading = __( 'Current version', 'wpcode-premium' );
		} else {
			/* translators: %s: the revision's date and time. */
			$heading = sprintf( __( 'Revision from %s', 'wpcode-premium' ), self::format_date_time( $revision->created ) );
		}

		$html = '<div class="wpcode-revisions-detail-header"><h2 class="wpcode-revisions-detail-title">' . esc_html( $heading ) . '</h2>';

		if ( ! empty( $rows ) ) {
			$html .= $this->restore_button( $revision, $rows );
		}

		$html .= '</div>';

		// Only a snippet restore can be refused, so the other types never show this notice.
		if ( $restore_error && self::TYPE_SNIPPET === $this->type ) {
			$html .= '<div class="wpcode-revisions-detail-notice wpcode-revisions-detail-notice-error">' . wp_kses( get_wpcode_icon( 'remove', 20, 20 ), wpcode_get_icon_allowed_tags() ) . '<span>';
			$html .= esc_html__( 'The revision could not be restored. Check that you are allowed to edit this snippet and try again.', 'wpcode-premium' );
			$html .= '</span></div>';
		}

		// A restore selects the new current revision, so the notice shows there. The restored
		// revision can be gone already, removed by the revision limit in the restore request.
		if ( absint( $restored_id ) && $this->is_current( $revision ) ) {
			$restored = $this->get_revision( $restored_id );
			$html    .= '<div class="wpcode-revisions-detail-notice">' . wp_kses( get_wpcode_icon( 'check', 20, 20, '0 0 18 14' ), wpcode_get_icon_allowed_tags() ) . '<span>';
			if ( $restored ) {
				/* translators: %s: the revision's date and time. */
				$html .= esc_html( sprintf( __( 'Revision from %s restored and saved as the new current revision.', 'wpcode-premium' ), self::format_date_time( $restored->created ) ) );
			} else {
				$html .= esc_html__( 'Revision restored and saved as the new current revision.', 'wpcode-premium' );
			}
			$html .= '</span></div>';
		}

		if ( empty( $rows ) ) {
			return $html . '<div class="wpcode-revisions-detail-current">' . esc_html( $this->current_message( $this->is_current( $revision ) ) ) . '</div>';
		}

		return $html . $this->render_rows( $rows );
	}

	/**
	 * The Restore This Revision button with the data the modal needs.
	 *
	 * @param stdClass $revision The revision.
	 * @param array    $rows     Its rows.
	 *
	 * @return string
	 */
	private function restore_button( $revision, $rows ) {
		$id         = absint( $revision->revision_id );
		$editor_url = $this->get_editor_url( $id );

		return sprintf(
			'<button type="button" class="wpcode-button wpcode-revisions-restore" data-restore-url="%1$s"%2$s data-title="%3$s" data-changes="%4$s">%5$s</button>',
			esc_url( $this->get_restore_url( $id ) ),
			'' !== $editor_url ? ' data-editor-url="' . esc_url( $editor_url ) . '"' : '',
			/* translators: %s: the revision's date. */
			esc_attr( sprintf( __( 'Restore Revision from %s?', 'wpcode-premium' ), self::format_date( $revision->created ) ) ),
			esc_attr( self::join_labels( wp_list_pluck( $rows, 'label' ) ) ),
			esc_html__( 'Restore This Revision', 'wpcode-premium' )
		);
	}

	/**
	 * "Title, Code and Note".
	 *
	 * @param array $labels Row labels.
	 *
	 * @return string
	 */
	private static function join_labels( $labels ) {
		$labels = array_values( $labels );
		$count  = count( $labels );

		if ( $count < 2 ) {
			return $count ? $labels[0] : '';
		}

		$last = array_pop( $labels );

		/* translators: 1: comma separated list of field names, 2: the last field name. */
		return sprintf( _x( '%1$s and %2$s', 'list of changed fields', 'wpcode-premium' ), implode( ', ', $labels ), $last );
	}

	/**
	 * The message shown when a revision matches the live item.
	 *
	 * Only the row marked Current is called the current version. Another row with the same data
	 * is called the same as the current version, which agrees with its heading and badge.
	 *
	 * @param bool $is_current Whether the revision is the row marked Current.
	 *
	 * @return string
	 */
	private function current_message( $is_current = true ) {
		if ( ! $is_current ) {
			if ( self::TYPE_HEADER_FOOTER === $this->type ) {
				return __( 'This revision is the same as the current version of your Header & Footer code. Restoring it would change nothing.', 'wpcode-premium' );
			}
			if ( self::TYPE_PAGE_SCRIPTS === $this->type ) {
				return __( 'This revision is the same as the current version of the page scripts. Restoring it would change nothing.', 'wpcode-premium' );
			}

			return __( 'This revision is the same as the current version of the snippet. Restoring it would change nothing.', 'wpcode-premium' );
		}

		if ( self::TYPE_HEADER_FOOTER === $this->type ) {
			return __( 'This is the current version of your Header & Footer code. Select another revision to see what restoring it would change.', 'wpcode-premium' );
		}
		if ( self::TYPE_PAGE_SCRIPTS === $this->type ) {
			return __( 'This is the current version of the page scripts. Select another revision to see what restoring it would change.', 'wpcode-premium' );
		}

		return __( 'This is the current version of the snippet. Select another revision to see what restoring it would change.', 'wpcode-premium' );
	}

	/**
	 * The field table.
	 *
	 * @param array $rows The rows.
	 *
	 * @return string
	 */
	private function render_rows( $rows ) {
		$html = '<div class="wpcode-revisions-fields"><div class="wpcode-revisions-fields-head">' . esc_html__( 'Field', 'wpcode-premium' ) . '</div><div class="wpcode-revisions-fields-head">' . esc_html__( 'Current → After restore', 'wpcode-premium' ) . '</div>';

		foreach ( $rows as $row ) {
			$html .= '<div class="wpcode-revisions-field-label">' . esc_html( $row['label'] ) . '</div>';

			if ( 'code' === $row['kind'] ) {
				// Built by WPCode_Revisions_Code_Diff from escaped pieces.
				$html .= '<div class="wpcode-revisions-field-cell wpcode-revisions-field-cell-code">' . $row['html'] . '</div>';
				continue;
			}

			$html .= sprintf(
				'<div class="wpcode-revisions-field-cell"><div class="wpcode-revisions-field-value"><span class="wpcode-revisions-field-from">%1$s</span><span class="wpcode-revisions-field-arrow" aria-hidden="true">→</span><span class="wpcode-revisions-field-to%3$s">%2$s</span></div></div>',
				esc_html( $row['from'] ),
				esc_html( $row['to'] ),
				$row['muted'] ? ' wpcode-revisions-field-muted' : ''
			);
		}

		return $html . '</div>';
	}

	/**
	 * The restore confirmation modal, rendered once per page and filled by JavaScript.
	 *
	 * @return string
	 */
	public function render_modal() {
		if ( self::TYPE_HEADER_FOOTER === $this->type ) {
			/* translators: %s: the list of changed fields, shown in bold. */
			$text = __( 'This changes %s in your live Header & Footer code.', 'wpcode-premium' );
		} elseif ( self::TYPE_PAGE_SCRIPTS === $this->type ) {
			/* translators: %s: the list of changed fields, shown in bold. */
			$text = __( 'This changes %s in this page\'s live scripts.', 'wpcode-premium' );
		} else {
			/* translators: %s: the list of changed fields, shown in bold. */
			$text = __( 'This changes %s on the live snippet.', 'wpcode-premium' );
		}

		// With the revision limit on, the current version is removed once enough newer revisions
		// exist, so it cannot be restored at any time.
		$retention = isset( wpcode()->revisions_retention ) ? wpcode()->revisions_retention : null;

		if ( $retention && $retention->is_enabled() ) {
			$text .= ' ' . sprintf(
				/* translators: %s: the number of revisions kept for each item. */
				__( 'Your current version stays in the revisions list, so you can restore it later, until your limit of %s revisions removes it.', 'wpcode-premium' ),
				number_format_i18n( $retention->get_keep_count() )
			);
		} else {
			$text .= ' ' . __( 'Your current version stays in the revisions list, so you can restore it later.', 'wpcode-premium' );
		}

		$options = '';
		if ( self::TYPE_SNIPPET === $this->type ) {
			$options = '<div class="wpcode-revisions-restore-options">'
				. $this->modal_option( 'now', true, __( 'Restore now', 'wpcode-premium' ), __( 'Replace the current snippet immediately. It stays active if it is active now.', 'wpcode-premium' ) )
				. $this->modal_option( 'editor', false, __( 'Load into the editor first', 'wpcode-premium' ), __( 'Open this revision in the editor without saving. Nothing changes until you click Update.', 'wpcode-premium' ) )
				. '</div>';
		}

		return sprintf(
			'<div id="wpcode-revisions-restore-modal" class="wpcode-revisions-modal" role="dialog" aria-modal="true" aria-labelledby="wpcode-revisions-modal-title"><div class="wpcode-revisions-modal-box"><div class="wpcode-revisions-modal-header"><span id="wpcode-revisions-modal-title" class="wpcode-revisions-modal-title"></span><button type="button" class="wpcode-button-just-icon wpcode-revisions-modal-close" aria-label="%1$s">%2$s</button></div><div class="wpcode-revisions-modal-body"><p class="wpcode-revisions-modal-text">%3$s</p>%4$s</div><div class="wpcode-revisions-modal-footer"><button type="button" class="wpcode-button wpcode-button-secondary wpcode-revisions-modal-cancel">%5$s</button><button type="button" class="wpcode-button wpcode-revisions-modal-confirm" data-label-restore="%6$s" data-label-editor="%7$s">%6$s</button></div></div></div>',
			esc_attr__( 'Close', 'wpcode-premium' ),
			wp_kses( get_wpcode_icon( 'close', 15, 14 ), wpcode_get_icon_allowed_tags() ),
			// The placeholder is replaced by the bold element the script fills; the sentence itself is escaped.
			str_replace( '%s', '<b class="wpcode-revisions-modal-changes"></b>', esc_html( $text ) ),
			$options,
			esc_html__( 'Cancel', 'wpcode-premium' ),
			esc_attr__( 'Restore Revision', 'wpcode-premium' ),
			esc_attr__( 'Open in Editor', 'wpcode-premium' )
		);
	}

	/**
	 * One radio card in the modal.
	 *
	 * @param string $value   Radio value: now or editor.
	 * @param bool   $checked Whether it starts checked.
	 * @param string $title   Card title.
	 * @param string $text    Card description.
	 *
	 * @return string
	 */
	private function modal_option( $value, $checked, $title, $text ) {
		return sprintf(
			'<label class="wpcode-revisions-restore-option%1$s"><input type="radio" name="wpcode-restore-mode" value="%2$s"%3$s><span><span class="wpcode-revisions-restore-option-title">%4$s</span><span class="wpcode-revisions-restore-option-text">%5$s</span></span></label>',
			$checked ? ' wpcode-revisions-restore-option-selected' : '',
			esc_attr( $value ),
			$checked ? ' checked' : '',
			esc_html( $title ),
			esc_html( $text )
		);
	}

	/**
	 * Whether the current user may save the code type a revision carries.
	 *
	 * The editor's save path enforces this, so an overlay it would refuse is never offered. A
	 * revision without a code type falls back to the generic capability, as the restore does.
	 *
	 * @param int $revision_id The revision id.
	 *
	 * @return bool
	 */
	private function can_edit_code_type( $revision_id ) {
		$revision = $this->get_revision( $revision_id );
		$data     = $revision ? $this->get_revision_data( $revision ) : array();
		$required = WPCode_Access::capability_for_code_type( isset( $data['code_type'] ) ? $data['code_type'] : '' );

		return empty( $required ) ? current_user_can( 'wpcode_edit_snippets' ) : current_user_can( $required );
	}

	/**
	 * URL of the page with a revision selected.
	 *
	 * @param int $revision_id The revision id.
	 *
	 * @return string
	 */
	public function get_view_url( $revision_id ) {
		return add_query_arg(
			array(
				'page'     => 'wpcode-revisions',
				'revision' => absint( $revision_id ),
			),
			admin_url( 'admin.php' )
		);
	}

	/**
	 * Nonce'd URL that restores a revision through the page's existing handler.
	 *
	 * @param int $revision_id The revision id.
	 *
	 * @return string
	 */
	public function get_restore_url( $revision_id ) {
		$revision_id = absint( $revision_id );

		return add_query_arg(
			array(
				'restore'  => $revision_id,
				'revision' => $revision_id,
				'_wpnonce' => wp_create_nonce( 'wpcode-restore-revision' ),
			),
			admin_url( 'admin.php?page=wpcode-revisions' )
		);
	}

	/**
	 * Nonce'd URL that opens the snippet editor with the revision loaded but not saved.
	 *
	 * @param int $revision_id The revision id.
	 *
	 * @return string Empty for types without an editor.
	 */
	public function get_editor_url( $revision_id ) {
		if ( self::TYPE_SNIPPET !== $this->type ) {
			return '';
		}

		$revision_id = absint( $revision_id );

		if ( ! $this->can_edit_code_type( $revision_id ) ) {
			return '';
		}

		return add_query_arg(
			array(
				'page'          => 'wpcode-snippet-manager',
				'snippet_id'    => $this->parent_id,
				'load_revision' => $revision_id,
				'_wpnonce'      => wp_create_nonce( 'wpcode-load-revision-' . $revision_id ),
			),
			admin_url( 'admin.php' )
		);
	}
}
