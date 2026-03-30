<?php
/**
 * PHPUnit bootstrap file.
 *
 * Mocks WordPress and WooCommerce functions so unit tests can run
 * without a full WordPress installation.
 */

// Define WordPress constants.
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', dirname( __DIR__ ) . '/' );
}

if ( ! defined( 'WPINC' ) ) {
	define( 'WPINC', 'wp-includes' );
}

// ── In-memory options store ──────────────────────────────────────────────────

global $wp_test_options;
$wp_test_options = array();

if ( ! function_exists( 'add_option' ) ) {
	function add_option( $option, $value = '' ) {
		global $wp_test_options;
		if ( ! isset( $wp_test_options[ $option ] ) ) {
			$wp_test_options[ $option ] = $value;
		}
		return true;
	}
}

if ( ! function_exists( 'get_option' ) ) {
	function get_option( $option, $default = false ) {
		global $wp_test_options;
		return isset( $wp_test_options[ $option ] ) ? $wp_test_options[ $option ] : $default;
	}
}

if ( ! function_exists( 'update_option' ) ) {
	function update_option( $option, $value ) {
		global $wp_test_options;
		$wp_test_options[ $option ] = $value;
		return true;
	}
}

if ( ! function_exists( 'delete_option' ) ) {
	function delete_option( $option ) {
		global $wp_test_options;
		unset( $wp_test_options[ $option ] );
		return true;
	}
}

// ── Hooks stubs ──────────────────────────────────────────────────────────────

global $wp_test_actions, $wp_test_filters;
$wp_test_actions = array();
$wp_test_filters = array();

if ( ! function_exists( 'add_action' ) ) {
	function add_action( $tag, $callback = '', $priority = 10, $accepted_args = 1 ) {
		global $wp_test_actions;
		$wp_test_actions[ $tag ][] = $callback;
		return true;
	}
}

if ( ! function_exists( 'add_filter' ) ) {
	function add_filter( $tag, $callback = '', $priority = 10, $accepted_args = 1 ) {
		global $wp_test_filters;
		$wp_test_filters[ $tag ][] = $callback;
		return true;
	}
}

if ( ! function_exists( 'apply_filters' ) ) {
	function apply_filters( $tag, $value ) {
		return $value;
	}
}

if ( ! function_exists( 'do_action' ) ) {
	function do_action( $tag ) {
		// No-op stub.
	}
}

// ── Sanitization / formatting stubs ──────────────────────────────────────────

if ( ! function_exists( 'sanitize_text_field' ) ) {
	function sanitize_text_field( $str ) {
		return trim( strip_tags( $str ) );
	}
}

if ( ! function_exists( 'absint' ) ) {
	function absint( $maybeint ) {
		return abs( (int) $maybeint );
	}
}

if ( ! function_exists( 'wp_json_encode' ) ) {
	function wp_json_encode( $data, $options = 0, $depth = 512 ) {
		return json_encode( $data, $options, $depth );
	}
}

if ( ! function_exists( 'wp_kses_post' ) ) {
	function wp_kses_post( $data ) {
		return $data;
	}
}

// ── Escaping stubs ───────────────────────────────────────────────────────────

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_html__' ) ) {
	function esc_html__( $text, $domain = 'default' ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_attr__' ) ) {
	function esc_attr__( $text, $domain = 'default' ) {
		return htmlspecialchars( $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( '__' ) ) {
	function __( $text, $domain = 'default' ) {
		return $text;
	}
}

if ( ! function_exists( '_e' ) ) {
	function _e( $text, $domain = 'default' ) {
		echo $text;
	}
}

// ── Plugin path stubs ────────────────────────────────────────────────────────

if ( ! function_exists( 'plugin_dir_url' ) ) {
	function plugin_dir_url( $file ) {
		return 'http://example.com/wp-content/plugins/' . basename( dirname( $file ) ) . '/';
	}
}

if ( ! function_exists( 'plugin_dir_path' ) ) {
	function plugin_dir_path( $file ) {
		return trailingslashit( dirname( $file ) );
	}
}

if ( ! function_exists( 'plugin_basename' ) ) {
	function plugin_basename( $file ) {
		return basename( dirname( $file ) ) . '/' . basename( $file );
	}
}

if ( ! function_exists( 'trailingslashit' ) ) {
	function trailingslashit( $string ) {
		return rtrim( $string, '/\\' ) . '/';
	}
}

// ── Misc WordPress stubs ────────────────────────────────────────────────────

if ( ! function_exists( 'is_plugin_active' ) ) {
	function is_plugin_active( $plugin ) {
		return false;
	}
}

if ( ! function_exists( 'wp_doing_ajax' ) ) {
	function wp_doing_ajax() {
		return false;
	}
}

if ( ! function_exists( 'wp_doing_cron' ) ) {
	function wp_doing_cron() {
		return false;
	}
}

if ( ! function_exists( 'is_admin' ) ) {
	function is_admin() {
		return true;
	}
}

if ( ! function_exists( 'check_ajax_referer' ) ) {
	function check_ajax_referer( $action = -1, $query_arg = false, $die = true ) {
		return true;
	}
}

if ( ! function_exists( 'dbDelta' ) ) {
	function dbDelta( $queries = '', $execute = true ) {
		return array();
	}
}

if ( ! function_exists( 'wp_parse_args' ) ) {
	function wp_parse_args( $args, $defaults = array() ) {
		if ( is_string( $args ) ) {
			parse_str( $args, $parsed );
		} else {
			$parsed = (array) $args;
		}
		return array_merge( $defaults, $parsed );
	}
}

if ( ! function_exists( 'wp_unslash' ) ) {
	function wp_unslash( $value ) {
		return stripslashes_deep( $value );
	}
}

if ( ! function_exists( 'stripslashes_deep' ) ) {
	function stripslashes_deep( $value ) {
		return is_array( $value ) ? array_map( 'stripslashes_deep', $value ) : stripslashes( $value );
	}
}

if ( ! function_exists( 'current_time' ) ) {
	function current_time( $type, $gmt = 0 ) {
		if ( 'mysql' === $type ) {
			return gmdate( 'Y-m-d H:i:s' );
		}
		return time();
	}
}

// ── Minimal $wpdb mock ──────────────────────────────────────────────────────

if ( ! class_exists( 'wpdb' ) ) {
	class wpdb {
		public $prefix  = 'wp_';
		public $charset = 'utf8mb4';

		public function get_charset_collate() {
			return 'DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci';
		}

		public function prepare( $query ) {
			$args = func_get_args();
			array_shift( $args );
			if ( ! empty( $args ) ) {
				return vsprintf( $query, $args );
			}
			return $query;
		}

		public function insert( $table, $data, $format = null ) {
			return 1;
		}

		public function update( $table, $data, $where, $format = null, $where_format = null ) {
			return 1;
		}

		public function get_row( $query = null, $output = OBJECT, $y = 0 ) {
			return null;
		}

		public function get_results( $query = null, $output = OBJECT ) {
			return array();
		}

		public function get_var( $query = null, $x = 0, $y = 0 ) {
			return null;
		}

		public function query( $query ) {
			return true;
		}

		public function get_col( $query = null, $x = 0 ) {
			return array();
		}
	}
}

if ( ! defined( 'OBJECT' ) ) {
	define( 'OBJECT', 'OBJECT' );
}
if ( ! defined( 'ARRAY_A' ) ) {
	define( 'ARRAY_A', 'ARRAY_A' );
}

global $wpdb;
$wpdb = new wpdb();

// ── Minimal WP_List_Table mock ──────────────────────────────────────────────

if ( ! class_exists( 'WP_List_Table' ) ) {
	class WP_List_Table {
		public $screen;
		protected $items = array();

		public function __construct( $args = array() ) {
			$this->screen = (object) array( 'id' => 'test_screen' );
		}

		public function get_columns() {
			return array();
		}

		public function display() {}
	}
}

// ── BM_DBhandler stub ────────────────────────────────────────────────────────

if ( ! class_exists( 'BM_DBhandler' ) ) {
	class BM_DBhandler {
		public function get_global_option_value( $option, $default = '' ) {
			return get_option( $option, $default );
		}

		public function update_global_option_value( $option, $value ) {
			return update_option( $option, $value );
		}

		public function insert_row( $identifier, $data, $format = array() ) {
			return 1;
		}

		public function update_row( $identifier, $unique_field, $unique_field_value, $data, $format = array(), $where_format = array() ) {
			return 1;
		}

		public function get_row( $identifier, $unique_field_value, $unique_field = null, $output_type = OBJECT ) {
			return null;
		}

		public function get_all_result( $identifier, $column = '*', $where = '', $result_type = OBJECT, $offset = 0, $limit = 0, $sort_by = '', $descending = true, $additional = '', $output = '', $distinct = false ) {
			return array();
		}

		public function bm_count( $identifier, $where = '', $data_specifiers = '' ) {
			return 0;
		}

		public function get_table_columns( $identifier, $exclude_columns = array() ) {
			return array();
		}

		public function filter_existing_data_by_columns( $data, $columns, $exclude_columns = array(), $column_ordered = false, $indexed = false ) {
			return $data;
		}

		public function get_activator() {
			return new Booking_Management_Activator();
		}
	}
}

// ── BM_Request stub ─────────────────────────────────────────────────────────

if ( ! class_exists( 'BM_Request' ) ) {
	class BM_Request {
		public function bm_fetch_external_service_price_module_by_service_id_and_date( $service_id = 0, $date = '' ) {
			return 0;
		}

		public function bm_fetch_external_service_price_module_age_ranges( $module_id, $service_id ) {
			return array();
		}

		public function bm_fetch_price_module_data_for_order( $booking_key ) {
			return array();
		}

		public function bm_convert_date_format( $date, $from, $to ) {
			$dt = DateTime::createFromFormat( $from, $date );
			return $dt ? $dt->format( $to ) : $date;
		}

		public function bm_fetch_order_status_name( $status ) {
			return $status;
		}
	}
}
