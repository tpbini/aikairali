<?php
namespace AIKairali\Portal\Core;

/**
 * Class Activator
 *
 * Fired during plugin activation.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.0.0
 */
class Activator {

	/**
	 * Activate the plugin.
	 */
	public static function activate(): void {
		// Set default options.
		if ( false === get_option( 'aikairali_portal_settings' ) ) {
			$defaults = [
				'general'     => [
					'brand_name' => 'AIKairali',
				],
				'performance' => [
					'enable_cache' => '1',
					'cache_expiry' => '3600',
				],
				'seo'         => [
					'enable_json_ld' => '1',
				],
			];
			update_option( 'aikairali_portal_settings', $defaults );
		}

		// Register CPTs and Taxonomies manually during activation so they exist before flushing rewrite rules.
		$plugin = Plugin::instance();
		$plugin->register_module_cpts_and_taxonomies();

		// Flush rewrite rules.
		flush_rewrite_rules();

		// Check if ACF is installed and active.
		if ( ! class_exists( 'ACF' ) ) {
			set_transient( 'aikairali_portal_acf_missing_notice', true, 300 );
		}
	}
}
