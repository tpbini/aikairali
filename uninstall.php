<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @link       https://aikairali.org
 * @since      1.0.0
 *
 * @package    AIKairali_Portal
 */

// If uninstall not called from WordPress, exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Clean up options.
delete_option( 'aikairali_portal_settings' );
delete_option( 'aikairali_portal_db_version' );

// Clean up transients.
global $wpdb;
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_%'" );
$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_%'" );
