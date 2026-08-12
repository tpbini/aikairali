<?php
namespace AIKairali\Portal\Core;

/**
 * Class Deactivator
 *
 * Fired during plugin deactivation.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.0.0
 */
class Deactivator {

	/**
	 * Deactivate the plugin.
	 */
	public static function deactivate(): void {
		// Flush rewrite rules to remove custom post type rules.
		flush_rewrite_rules();
	}
}
