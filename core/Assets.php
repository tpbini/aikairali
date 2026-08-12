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
		$loader->add_action( 'wp_footer', $this, 'render_mobile_drawer' );
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

	/**
	 * Render mobile off-canvas drawer markup in wp_footer.
	 */
	public function render_mobile_drawer(): void {
		if ( is_admin() ) {
			return;
		}
		?>
		<!-- Mobile Navigation Off-Canvas Drawer (Plugin Rendered) -->
		<div class="aik-mobile-drawer-overlay" id="aikDrawerOverlay"></div>
		<div class="aik-mobile-drawer" id="aikMobileDrawer">
			<div class="aik-drawer-header">
				<div class="aik-drawer-brand">
					<a href="/" title="AiKairali Home">
						<img src="/wp-content/themes/twentytwentyfive/assets/images/logo.jpg" alt="AiKairali" class="aik-drawer-logo">
					</a>
				</div>
				<button type="button" class="aik-drawer-close" id="aikDrawerClose" aria-label="Close Menu">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
				</button>
			</div>
			<div class="aik-drawer-divider"></div>
			<div class="aik-drawer-body">
				<!-- Mobile Drawer Search Bar -->
				<form role="search" method="get" class="aik-drawer-search-form" action="/">
					<div class="aik-drawer-search-wrap">
						<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="aik-drawer-search-icon"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
						<input type="search" class="aik-drawer-search-input" placeholder="Search..." name="s" autocomplete="off" />
					</div>
				</form>

				<ul class="aik-drawer-menu">
					<li><a href="/">Home</a></li>
					<li><a href="/ai-news/">AI News</a></li>
					<li><a href="/tutorials/">Tutorials</a></li>
					<li><a href="/ai-tools/">AI tools</a></li>
					<li><a href="/prompts/">Prompts</a></li>
					<li><a href="/courses/">Courses</a></li>
					<li><a href="/videos/">Videos</a></li>
				</ul>
			</div>
		</div>
		<?php
	}
}
