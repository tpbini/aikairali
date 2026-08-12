<?php
namespace AIKairali\Portal\Core;

/**
 * Class Assets
 *
 * Manages plugin CSS & JS assets.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.0.0
 */
class Assets {

	/**
	 * Constructor.
	 *
	 * @param Loader $loader The hook loader.
	 */
	public function __construct( Loader $loader ) {
		$loader->add_action( 'wp_enqueue_scripts', $this, 'enqueue_frontend_assets' );
		$loader->add_action( 'admin_enqueue_scripts', $this, 'enqueue_admin_assets' );
	}

	/**
	 * Enqueue frontend CSS and JS.
	 */
	public function enqueue_frontend_assets(): void {
		// Register frontend stylesheet.
		wp_register_style(
			'aikairali-portal-frontend',
			AIKAIRALI_PORTAL_URL . 'assets/css/frontend.css',
			[],
			AIKAIRALI_PORTAL_VERSION,
			'all'
		);

		// Register frontend script.
		wp_register_script(
			'aikairali-portal-frontend',
			AIKAIRALI_PORTAL_URL . 'assets/js/frontend.js',
			[ 'jquery' ],
			AIKAIRALI_PORTAL_VERSION,
			true
		);

		// Enqueue frontend assets.
		wp_enqueue_style( 'aikairali-portal-frontend' );
		wp_enqueue_script( 'aikairali-portal-frontend' );

		// Localize script for AJAX search.
		wp_localize_script(
			'aikairali-portal-frontend',
			'aikairaliPortal',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'aikairali_portal_search_nonce' ),
			]
		);
	}

	/**
	 * Enqueue admin CSS and JS.
	 *
	 * @param string $hook_suffix Current admin page hook.
	 */
	public function enqueue_admin_assets( string $hook_suffix ): void {
		$current_screen = get_current_screen();
		$is_plugin_screen = strpos( $hook_suffix, 'aikairali' ) !== false;
		$is_module_post_type = false;

		if ( $current_screen && $current_screen->post_type ) {
			// Check if current screen is one of our custom post types.
			$plugin_modules = Plugin::instance()->get_modules();
			$is_module_post_type = array_key_exists( $current_screen->post_type, $plugin_modules );
		}

		// Only enqueue on AIKairali pages or AIKairali CPT custom screens.
		if ( ! $is_plugin_screen && ! $is_module_post_type ) {
			return;
		}

		wp_enqueue_style(
			'aikairali-portal-admin',
			AIKAIRALI_PORTAL_URL . 'assets/css/admin.css',
			[],
			AIKAIRALI_PORTAL_VERSION,
			'all'
		);

		wp_enqueue_script(
			'aikairali-portal-admin',
			AIKAIRALI_PORTAL_URL . 'assets/js/admin.js',
			[ 'jquery' ],
			AIKAIRALI_PORTAL_VERSION,
			true
		);

		wp_localize_script(
			'aikairali-portal-admin',
			'aikairaliPortalAdmin',
			[
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'nonce'    => wp_create_nonce( 'aikairali_portal_admin_nonce' ),
			]
		);
	}
}
