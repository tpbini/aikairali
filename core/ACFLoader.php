<?php
namespace AIKairali\Portal\Core;

/**
 * Class ACFLoader
 *
 * Manages programmatically registering ACF field groups and handling missing ACF notice.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.0.0
 */
class ACFLoader {

	/**
	 * Constructor.
	 *
	 * @param Loader $loader The hook loader.
	 */
	public function __construct( Loader $loader ) {
		$loader->add_action( 'admin_notices', $this, 'display_acf_missing_notice' );
	}

	/**
	 * Register an ACF field group.
	 *
	 * Automatically enables `show_in_rest` so ACF PRO accepts field data posted
	 * via the WordPress REST API `acf` namespace.  Without this flag ACF silently
	 * ignores the `acf` key in REST requests and meta boxes stay empty.
	 *
	 * @param array $field_group The field group configuration array.
	 * @return bool True if successfully registered, false otherwise.
	 */
	public static function register_field_group( array $field_group ): bool {
		if ( function_exists( 'acf_add_local_field_group' ) ) {
			// Ensure REST API support is enabled on every field group.
			$field_group = array_merge( [ 'show_in_rest' => 1 ], $field_group );
			acf_add_local_field_group( $field_group );
			return true;
		}
		return false;
	}

	/**
	 * Display admin notice if ACF is not active.
	 */
	public function display_acf_missing_notice(): void {
		// Show if ACF is missing or if the activation transient is active.
		if ( ! class_exists( 'ACF' ) || get_transient( 'aikairali_portal_acf_missing_notice' ) ) {
			?>
			<div class="notice notice-warning is-dismissible">
				<p>
					<?php
					echo wp_kses_post(
						sprintf(
							/* translators: %s: ACF plugin name link */
							__( '<strong>AIKairali Portal</strong> requires the %s plugin to register custom fields. Please install and activate it to enable all features.', 'aikairali-portal' ),
							'<a href="' . esc_url( admin_url( 'plugin-install.php?tab=search&s=Advanced+Custom+Fields' ) ) . '">Advanced Custom Fields</a>'
						)
					);
					?>
				</p>
			</div>
			<?php
			// Delete the activation notice transient so it only fires once if dismissed.
			delete_transient( 'aikairali_portal_acf_missing_notice' );
		}
	}
}
