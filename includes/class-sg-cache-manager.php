<?php
/**
 * Cache Manager — Aggressive caching layer for FlexiBooking.
 *
 * Provides a unified caching API that supports multiple backends:
 *   1. WordPress Object Cache (Redis / Memcached via drop-in)
 *   2. WordPress Transients (fallback)
 *
 * When a persistent object cache is available (Redis, Memcached),
 * the manager automatically uses `wp_cache_*()` functions for fast
 * in-memory lookups. Otherwise, it falls back to transients.
 *
 * Usage:
 *   $cache = SG_Cache_Manager::get_instance();
 *   $cache->set( 'timeslots_42_2024-01-15', $data, 300 );
 *   $data  = $cache->get( 'timeslots_42_2024-01-15' );
 *   $cache->delete( 'timeslots_42_2024-01-15' );
 *
 * @since      1.2.0
 * @package    Booking_Management
 * @subpackage Booking_Management/includes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SG_Cache_Manager {

	/**
	 * Cache group name for this plugin.
	 *
	 * @var string
	 */
	const CACHE_GROUP = 'sg_booking';

	/**
	 * Default TTL in seconds (5 minutes).
	 *
	 * @var int
	 */
	const DEFAULT_TTL = 300;

	/**
	 * Singleton instance.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Whether a persistent object cache (Redis/Memcached) is available.
	 *
	 * @var bool
	 */
	private $has_persistent_cache = false;

	/**
	 * In-memory runtime cache for the current request.
	 *
	 * @var array
	 */
	private $runtime_cache = array();

	/**
	 * Get the singleton instance.
	 *
	 * @return self
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Private constructor.
	 */
	private function __construct() {
		$this->has_persistent_cache = wp_using_ext_object_cache();
	}

	/**
	 * Whether a persistent cache backend (Redis/Memcached) is available.
	 *
	 * @return bool
	 */
	public function has_persistent_cache() {
		return $this->has_persistent_cache;
	}

	/**
	 * Get a cached value.
	 *
	 * Checks in order: runtime cache → object cache → transient.
	 *
	 * @param string $key Cache key.
	 * @return mixed|false Cached value or false if not found.
	 */
	public function get( $key ) {
		// 1. Runtime cache (fastest — no I/O).
		if ( isset( $this->runtime_cache[ $key ] ) ) {
			return $this->runtime_cache[ $key ];
		}

		$value = false;

		// 2. Persistent object cache (Redis/Memcached).
		if ( $this->has_persistent_cache ) {
			$found = false;
			$value = wp_cache_get( $key, self::CACHE_GROUP, false, $found );
			if ( ! $found ) {
				$value = false;
			}
		} else {
			// 3. WordPress transients (fallback).
			$value = get_transient( 'sg_bk_' . $key );
		}

		if ( false !== $value ) {
			$this->runtime_cache[ $key ] = $value;
		}

		return $value;
	}

	/**
	 * Set a cached value.
	 *
	 * @param string $key   Cache key.
	 * @param mixed  $value Value to cache.
	 * @param int    $ttl   Time-to-live in seconds. Default 300 (5 min).
	 * @return bool True on success.
	 */
	public function set( $key, $value, $ttl = self::DEFAULT_TTL ) {
		$this->runtime_cache[ $key ] = $value;

		if ( $this->has_persistent_cache ) {
			return wp_cache_set( $key, $value, self::CACHE_GROUP, $ttl );
		}

		return set_transient( 'sg_bk_' . $key, $value, $ttl );
	}

	/**
	 * Delete a cached value.
	 *
	 * @param string $key Cache key.
	 * @return bool True on success.
	 */
	public function delete( $key ) {
		unset( $this->runtime_cache[ $key ] );

		if ( $this->has_persistent_cache ) {
			return wp_cache_delete( $key, self::CACHE_GROUP );
		}

		return delete_transient( 'sg_bk_' . $key );
	}

	/**
	 * Flush all plugin cache entries.
	 *
	 * For persistent caches, this increments the group salt.
	 * For transients, this removes known transient keys.
	 *
	 * @return void
	 */
	public function flush_group() {
		$this->runtime_cache = array();

		if ( $this->has_persistent_cache ) {
			wp_cache_flush_group( self::CACHE_GROUP );
			return;
		}

		// Transient cleanup — delete known prefixed keys.
		global $wpdb;
		// Table name from WordPress core — not user input.
		// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
		$wpdb->query(
			"DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_sg_bk_%' OR option_name LIKE '_transient_timeout_sg_bk_%'"
		);
	}

	/**
	 * Get or set a cached value using a callback.
	 *
	 * If the key is not in the cache, the callback is invoked and
	 * its return value is stored for the specified TTL.
	 *
	 * @param string   $key      Cache key.
	 * @param callable $callback Function that returns the value to cache.
	 * @param int      $ttl      Time-to-live in seconds. Default 300.
	 * @return mixed Cached or computed value.
	 */
	public function remember( $key, callable $callback, $ttl = self::DEFAULT_TTL ) {
		$value = $this->get( $key );

		if ( false !== $value ) {
			return $value;
		}

		$value = $callback();
		$this->set( $key, $value, $ttl );

		return $value;
	}

	/**
	 * Cache an API response with automatic invalidation.
	 *
	 * Suitable for REST API responses that should be cached at the
	 * application level before sending to the client.
	 *
	 * @param string $endpoint    REST endpoint path (used as cache key suffix).
	 * @param array  $params      Query parameters (hashed for uniqueness).
	 * @param callable $callback  Function that produces the response data.
	 * @param int    $ttl         TTL in seconds. Default 300.
	 * @return mixed Cached or fresh response data.
	 */
	public function cache_api_response( $endpoint, array $params, callable $callback, $ttl = self::DEFAULT_TTL ) {
		$param_hash = md5( wp_json_encode( $params ) );
		$key        = 'api_' . sanitize_key( $endpoint ) . '_' . $param_hash;

		return $this->remember( $key, $callback, $ttl );
	}

	/**
	 * Invalidate API cache for a specific endpoint.
	 *
	 * @param string $endpoint REST endpoint path.
	 */
	public function invalidate_api_cache( $endpoint ) {
		// For persistent cache, the group flush handles everything.
		// For transients, we do a targeted cleanup.
		if ( ! $this->has_persistent_cache ) {
			global $wpdb;
			$prefix = '_transient_sg_bk_api_' . sanitize_key( $endpoint );
			// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
			$wpdb->query(
				$wpdb->prepare(
					"DELETE FROM {$wpdb->options} WHERE option_name LIKE %s",
					$prefix . '%'
				)
			);
		}
	}
}
