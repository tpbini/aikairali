<?php
namespace AIKairali\Portal\Core;

/**
 * Class Cache
 *
 * A helper class that wraps around WordPress Transients API for queries and data caching.
 * Respects performance and cache settings.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.0.0
 */
class Cache {

	/**
	 * Retrieve cached data.
	 *
	 * @param string $key Cache key.
	 * @return mixed False if not cached, otherwise cached data.
	 */
	public static function get( string $key ) {
		if ( ! self::is_cache_enabled() ) {
			return false;
		}

		return get_transient( 'aikairali_' . $key );
	}

	/**
	 * Cache data.
	 *
	 * @param string $key        Cache key.
	 * @param mixed  $value      Data to cache.
	 * @param int    $expiration Expiration time in seconds. Defaults to settings or 1 hour.
	 * @return bool True if cached, false otherwise.
	 */
	public static function set( string $key, $value, int $expiration = 0 ): bool {
		if ( ! self::is_cache_enabled() ) {
			return false;
		}

		if ( 0 === $expiration ) {
			$settings   = get_option( 'aikairali_portal_settings', [] );
			$expiration = isset( $settings['performance']['cache_expiry'] ) ? absint( $settings['performance']['cache_expiry'] ) : HOUR_IN_SECONDS;
		}

		return set_transient( 'aikairali_' . $key, $value, $expiration );
	}

	/**
	 * Delete cached data.
	 *
	 * @param string $key Cache key.
	 * @return bool True on success, false on failure.
	 */
	public static function delete( string $key ): bool {
		return delete_transient( 'aikairali_' . $key );
	}

	/**
	 * Flush all plugin transients.
	 */
	public static function flush_all(): void {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_%'" );
	}

	/**
	 * Check if caching is enabled in settings.
	 *
	 * @return bool True if enabled, false otherwise.
	 */
	public static function is_cache_enabled(): bool {
		$settings = get_option( 'aikairali_portal_settings', [] );
		$enabled  = $settings['performance']['enable_cache'] ?? '1';
		return '1' === $enabled;
	}
}
