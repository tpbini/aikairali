<?php
namespace AIKairali\Portal\Core;

/**
 * Class Settings
 *
 * Manages admin menus, settings tabs, import/export, and system info screens.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.0.0
 */
class Settings {

	/**
	 * Constructor.
	 *
	 * @param Loader $loader The hook loader.
	 */
	public function __construct( Loader $loader ) {
		$loader->add_action( 'admin_menu', $this, 'register_admin_menus' );
		$loader->add_action( 'admin_init', $this, 'register_settings' );
		$loader->add_action( 'admin_init', $this, 'process_import_export' );
	}

	/**
	 * Register the admin menu structure.
	 */
	public function register_admin_menus(): void {
		// Parent Menu Page.
		add_menu_page(
			__( 'AIKairali Portal', 'aikairali-portal' ),
			__( 'AIKairali', 'aikairali-portal' ),
			'manage_options',
			'aikairali-portal',
			[ $this, 'render_dashboard_page' ],
			'dashicons-admin-site',
			30
		);

		// Dashboard Submenu (same slug as parent to rename first submenu item).
		add_submenu_page(
			'aikairali-portal',
			__( 'AIKairali Dashboard', 'aikairali-portal' ),
			__( 'Dashboard', 'aikairali-portal' ),
			'manage_options',
			'aikairali-portal',
			[ $this, 'render_dashboard_page' ]
		);

		// Import / Export Page.
		add_submenu_page(
			'aikairali-portal',
			__( 'Import / Export Data', 'aikairali-portal' ),
			__( 'Import / Export', 'aikairali-portal' ),
			'manage_options',
			'aikairali-portal-import-export',
			[ $this, 'render_import_export_page' ]
		);

		// Settings Page.
		add_submenu_page(
			'aikairali-portal',
			__( 'AIKairali Settings', 'aikairali-portal' ),
			__( 'Settings', 'aikairali-portal' ),
			'manage_options',
			'aikairali-portal-settings',
			[ $this, 'render_settings_page' ]
		);

		// System Info Page.
		add_submenu_page(
			'aikairali-portal',
			__( 'System Information', 'aikairali-portal' ),
			__( 'System Info', 'aikairali-portal' ),
			'manage_options',
			'aikairali-portal-system-info',
			[ $this, 'render_system_info_page' ]
		);
	}

	/**
	 * Register settings via WordPress Settings API.
	 */
	public function register_settings(): void {
		register_setting(
			'aikairali_portal_settings_group',
			'aikairali_portal_settings',
			[ $this, 'sanitize_settings' ]
		);
	}

	/**
	 * Sanitize setting values.
	 *
	 * @param array $input Input array.
	 * @return array Sanitized array.
	 */
	public function sanitize_settings( array $input ): array {
		$sanitized = [];

		if ( isset( $input['general'] ) ) {
			$sanitized['general']['brand_name'] = sanitize_text_field( $input['general']['brand_name'] );
		}

		if ( isset( $input['brand'] ) ) {
			$sanitized['brand']['fallback_image'] = esc_url_raw( $input['brand']['fallback_image'] );
		}

		if ( isset( $input['seo'] ) ) {
			$sanitized['seo']['enable_json_ld'] = ! empty( $input['seo']['enable_json_ld'] ) ? '1' : '0';
		}

		if ( isset( $input['performance'] ) ) {
			$sanitized['performance']['enable_cache'] = ! empty( $input['performance']['enable_cache'] ) ? '1' : '0';
			$sanitized['performance']['cache_expiry'] = absint( $input['performance']['cache_expiry'] );
		}

		return $sanitized;
	}

	/**
	 * Process Import / Export requests.
	 */
	public function process_import_export(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle Import
		if ( isset( $_POST['aikairali_import_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['aikairali_import_nonce'] ) ), 'aikairali_import' ) ) {
			if ( empty( $_FILES['import_file']['tmp_name'] ) || empty( $_POST['import_module'] ) ) {
				add_settings_error( 'aikairali_import', 'import_error', __( 'Please select a file and a module.', 'aikairali-portal' ), 'error' );
				return;
			}

			$module = sanitize_key( $_POST['import_module'] );
			$file = $_FILES['import_file']['tmp_name'];
			
			$content = file_get_contents( $file );
			$data = json_decode( $content, true );

			if ( ! is_array( $data ) ) {
				add_settings_error( 'aikairali_import', 'import_error', __( 'Invalid JSON format. Please upload an array of objects.', 'aikairali-portal' ), 'error' );
				return;
			}

			// Include file & image upload helpers if needed
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';

			$count = 0;
			foreach ( $data as $item ) {
				$post_title   = $item['title'] ?? ( $item['name'] ?? 'Imported Post' );
				$post_content = $item['content'] ?? ( $item['description'] ?? ( $item['summary'] ?? '' ) );
				$post_excerpt = $item['excerpt'] ?? ( $item['summary'] ?? '' );
				$post_name    = $item['slug'] ?? '';
				
				// Parse date and ensure it is not set in the future to prevent WP auto-scheduling to 'future' status
				$raw_date  = ! empty( $item['published_date'] ) ? strtotime( $item['published_date'] ) : current_time( 'timestamp' );
				$curr_time = current_time( 'timestamp' );
				if ( $raw_date > $curr_time ) {
					$raw_date = $curr_time - 60; // 1 minute in the past
				}
				$post_date     = date( 'Y-m-d H:i:s', $raw_date );
				$post_date_gmt = get_gmt_from_date( $post_date );

				$post_data = [
					'post_title'    => sanitize_text_field( $post_title ),
					'post_content'  => wp_kses_post( $post_content ),
					'post_excerpt'  => sanitize_textarea_field( $post_excerpt ),
					'post_type'     => $module,
					'post_status'   => 'publish',
					'post_date'     => $post_date,
					'post_date_gmt' => $post_date_gmt,
				];

				if ( ! empty( $post_name ) ) {
					$post_data['post_name'] = sanitize_title( $post_name );
				}

				$post_id = wp_insert_post( $post_data );

				if ( $post_id && ! is_wp_error( $post_id ) ) {
					// Force post_status to 'publish' to override any automatic WP scheduling
					wp_update_post( [
						'ID'          => $post_id,
						'post_status' => 'publish',
					] );

					// 1. Assign Categories
					if ( ! empty( $item['categories'] ) || ! empty( $item['category'] ) ) {
						$cats = $item['categories'] ?? $item['category'];
						$cat_list = is_array( $cats ) ? $cats : array_map( 'trim', explode( ',', $cats ) );
						wp_set_object_terms( $post_id, $cat_list, 'category' );
					}

					// 2. Assign Tags
					if ( ! empty( $item['tags'] ) || ! empty( $item['tag'] ) ) {
						$tags = $item['tags'] ?? $item['tag'];
						$tag_list = is_array( $tags ) ? $tags : array_map( 'trim', explode( ',', $tags ) );
						wp_set_object_terms( $post_id, $tag_list, 'post_tag' );
					}

					// 3. Featured Image Attachment
					$img_url = $item['featured_image'] ?? ( $item['image'] ?? ( $item['thumbnail'] ?? '' ) );
					if ( ! empty( $img_url ) && filter_var( $img_url, FILTER_VALIDATE_URL ) ) {
						$attach_id = media_sideload_image( $img_url, $post_id, $post_title, 'id' );
						if ( ! is_wp_error( $attach_id ) && is_numeric( $attach_id ) ) {
							set_post_thumbnail( $post_id, $attach_id );
						}
					}

					// 4. SEO Metadata Mapping (Yoast SEO & Rank Math)
					if ( ! empty( $item['seo_title'] ) ) {
						update_post_meta( $post_id, '_yoast_wpseo_title', sanitize_text_field( $item['seo_title'] ) );
						update_post_meta( $post_id, 'rank_math_title', sanitize_text_field( $item['seo_title'] ) );
					}
					if ( ! empty( $item['seo_description'] ) ) {
						update_post_meta( $post_id, '_yoast_wpseo_metadesc', sanitize_textarea_field( $item['seo_description'] ) );
						update_post_meta( $post_id, 'rank_math_description', sanitize_textarea_field( $item['seo_description'] ) );
					}
					if ( ! empty( $item['seo_focus_keyword'] ) ) {
						update_post_meta( $post_id, '_yoast_wpseo_focuskw', sanitize_text_field( $item['seo_focus_keyword'] ) );
						update_post_meta( $post_id, 'rank_math_focus_keyword', sanitize_text_field( $item['seo_focus_keyword'] ) );
					}

					// 5. Custom / ACF Fields Loop
					foreach ( $item as $key => $value ) {
						if ( in_array( $key, [ 'title', 'name', 'content', 'summary', 'description', 'excerpt', 'slug', 'published_date', 'categories', 'category', 'tags', 'tag', 'featured_image', 'image', 'thumbnail', 'seo_title', 'seo_description', 'seo_focus_keyword' ], true ) ) {
							continue;
						}
						if ( function_exists( 'update_field' ) ) {
							update_field( sanitize_key( $key ), $value, $post_id );
						} else {
							update_post_meta( $post_id, sanitize_key( $key ), $value );
						}
					}
					$count++;
				}
			}

			add_settings_error( 'aikairali_import', 'import_success', sprintf( __( 'Successfully imported %d items into %s.', 'aikairali-portal' ), $count, $module ), 'success' );
		}
	}

	/**
	 * Render Dashboard Page.
	 */
	public function render_dashboard_page(): void {
		?>
		<div class="wrap aikairali-settings-wrap">
			<h1><?php esc_html_e( 'AIKairali Portal Dashboard', 'aikairali-portal' ); ?></h1>
			<p><?php esc_html_e( 'Welcome to the AIKairali Portal administration panel. Manage all your AI directories, learning resources, and modules from the menu.', 'aikairali-portal' ); ?></p>
			
			<div class="card" style="max-width: 600px; margin-top: 20px;">
				<h2><?php esc_html_e( 'Active Modules Summary', 'aikairali-portal' ); ?></h2>
				<ul>
					<?php
					$modules = Plugin::instance()->get_modules();
					if ( empty( $modules ) ) {
						echo '<li>' . esc_html__( 'No modules loaded yet. Add module classes under modules/ folder.', 'aikairali-portal' ) . '</li>';
					} else {
						foreach ( $modules as $slug => $instance ) {
							printf( '<li><strong>%s</strong> (slug: %s)</li>', esc_html( ucfirst( $slug ) ), esc_html( $slug ) );
						}
					}
					?>
				</ul>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Import / Export Page.
	 */
	public function render_import_export_page(): void {
		?>
		<div class="wrap aikairali-settings-wrap">
			<h1><?php esc_html_e( 'Import & Export Data', 'aikairali-portal' ); ?></h1>
			<p><?php esc_html_e( 'Import or export portal data in JSON or CSV format.', 'aikairali-portal' ); ?></p>
			
			<?php settings_errors( 'aikairali_import' ); ?>

			<div class="metabox-holder" style="display: flex; gap: 20px; margin-top: 20px;">
				<div class="postbox" style="flex: 1; padding: 20px;">
					<h2><?php esc_html_e( 'Import Data', 'aikairali-portal' ); ?></h2>
					<form method="post" enctype="multipart/form-data">
						<?php wp_nonce_field( 'aikairali_import', 'aikairali_import_nonce' ); ?>
						<p><label for="import_file"><?php esc_html_e( 'Select File (JSON or CSV):', 'aikairali-portal' ); ?></label></p>
						<p><input type="file" id="import_file" name="import_file" /></p>
						<p>
							<select name="import_module">
								<option value=""><?php esc_html_e( 'Select Target Module', 'aikairali-portal' ); ?></option>
								<option value="post" style="font-weight: bold; color: #2563eb;"><?php esc_html_e( '📌 Standard Blog Posts (post)', 'aikairali-portal' ); ?></option>
								<?php
								foreach ( Plugin::instance()->get_modules() as $slug => $instance ) {
									printf( '<option value="%s">%s</option>', esc_attr( $slug ), esc_html( ucfirst( $slug ) ) );
								}
								?>
							</select>
						</p>
						<p><input type="submit" class="button button-primary" value="<?php esc_attr_e( 'Import Now', 'aikairali-portal' ); ?>" /></p>
					</form>
				</div>
				<div class="postbox" style="flex: 1; padding: 20px;">
					<h2><?php esc_html_e( 'Export Data', 'aikairali-portal' ); ?></h2>
					<form method="post">
						<?php wp_nonce_field( 'aikairali_export', 'aikairali_export_nonce' ); ?>
						<p>
							<select name="export_module">
								<option value="all"><?php esc_html_e( 'Export All Data', 'aikairali-portal' ); ?></option>
								<option value="post"><?php esc_html_e( 'Standard Blog Posts Only', 'aikairali-portal' ); ?></option>
								<?php
								foreach ( Plugin::instance()->get_modules() as $slug => $instance ) {
									printf( '<option value="%s">%s Only</option>', esc_attr( $slug ), esc_html( ucfirst( $slug ) ) );
								}
								?>
							</select>
						</p>

						<p>
							<select name="export_format">
								<option value="json"><?php esc_html_e( 'JSON Format', 'aikairali-portal' ); ?></option>
								<option value="csv"><?php esc_html_e( 'CSV Format', 'aikairali-portal' ); ?></option>
							</select>
						</p>
						<p><input type="submit" class="button button-secondary" value="<?php esc_attr_e( 'Export Data', 'aikairali-portal' ); ?>" disabled /></p>
					</form>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Settings Page.
	 */
	public function render_settings_page(): void {
		$settings = get_option( 'aikairali_portal_settings', [] );
		$active_tab = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'general';

		$tabs = [
			'general'     => __( 'General', 'aikairali-portal' ),
			'brand'       => __( 'Brand', 'aikairali-portal' ),
			'seo'         => __( 'SEO', 'aikairali-portal' ),
			'performance' => __( 'Performance', 'aikairali-portal' ),
		];
		?>
		<div class="wrap aikairali-settings-wrap">
			<h1><?php esc_html_e( 'AIKairali Portal Settings', 'aikairali-portal' ); ?></h1>
			
			<h2 class="nav-tab-wrapper aikairali-nav-tab-wrapper">
				<?php foreach ( $tabs as $tab => $label ) : ?>
					<a href="<?php echo esc_url( add_query_arg( 'tab', $tab ) ); ?>" class="nav-tab <?php echo $active_tab === $tab ? 'nav-tab-active' : ''; ?>">
						<?php echo esc_html( $label ); ?>
					</a>
				<?php endforeach; ?>
			</h2>

			<form method="post" action="options.php">
				<?php
				settings_fields( 'aikairali_portal_settings_group' );

				if ( 'general' === $active_tab ) {
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="general_brand_name"><?php esc_html_e( 'Portal Brand Name', 'aikairali-portal' ); ?></label></th>
							<td>
								<input type="text" id="general_brand_name" name="aikairali_portal_settings[general][brand_name]" value="<?php echo esc_attr( $settings['general']['brand_name'] ?? 'AIKairali' ); ?>" class="regular-text" />
							</td>
						</tr>
					</table>
					<?php
				} elseif ( 'brand' === $active_tab ) {
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><label for="brand_fallback_image"><?php esc_html_e( 'Fallback Feature Image URL', 'aikairali-portal' ); ?></label></th>
							<td>
								<input type="text" id="brand_fallback_image" name="aikairali_portal_settings[brand][fallback_image]" value="<?php echo esc_url( $settings['brand']['fallback_image'] ?? '' ); ?>" class="large-text" />
								<p class="description"><?php esc_html_e( 'Used when an item does not have a featured image.', 'aikairali-portal' ); ?></p>
							</td>
						</tr>
					</table>
					<?php
				} elseif ( 'seo' === $active_tab ) {
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Structured Data', 'aikairali-portal' ); ?></th>
							<td>
								<label for="seo_json_ld">
									<input type="checkbox" id="seo_json_ld" name="aikairali_portal_settings[seo][enable_json_ld]" value="1" <?php checked( $settings['seo']['enable_json_ld'] ?? '1', '1' ); ?> />
									<?php esc_html_e( 'Enable Schema JSON-LD metadata injection on single pages.', 'aikairali-portal' ); ?>
								</label>
							</td>
						</tr>
					</table>
					<?php
				} elseif ( 'performance' === $active_tab ) {
					?>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row"><?php esc_html_e( 'Enable Query Caching', 'aikairali-portal' ); ?></th>
							<td>
								<label for="perf_enable_cache">
									<input type="checkbox" id="perf_enable_cache" name="aikairali_portal_settings[performance][enable_cache]" value="1" <?php checked( $settings['performance']['enable_cache'] ?? '1', '1' ); ?> />
									<?php esc_html_e( 'Cache related post queries and API endpoints using Transients.', 'aikairali-portal' ); ?>
								</label>
							</td>
						</tr>
						<tr>
							<th scope="row"><label for="perf_cache_expiry"><?php esc_html_e( 'Cache Lifetime (Seconds)', 'aikairali-portal' ); ?></label></th>
							<td>
								<input type="number" id="perf_cache_expiry" name="aikairali_portal_settings[performance][cache_expiry]" value="<?php echo esc_attr( $settings['performance']['cache_expiry'] ?? '3600' ); ?>" class="small-text" />
								<p class="description"><?php esc_html_e( 'Default is 3600 seconds (1 hour).', 'aikairali-portal' ); ?></p>
							</td>
						</tr>
					</table>
					<?php
				}

				submit_button();
				?>
			</form>
		</div>
		<?php
	}

	/**
	 * Render System Info Page.
	 */
	public function render_system_info_page(): void {
		global $wpdb;
		?>
		<div class="wrap aikairali-settings-wrap">
			<h1><?php esc_html_e( 'System Information', 'aikairali-portal' ); ?></h1>
			<p><?php esc_html_e( 'Useful system diagnostics for development and support.', 'aikairali-portal' ); ?></p>
			
			<table class="widefat striped" style="max-width: 800px; margin-top: 20px;">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Environment Variable', 'aikairali-portal' ); ?></th>
						<th><?php esc_html_e( 'Value', 'aikairali-portal' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><strong><?php esc_html_e( 'PHP Version', 'aikairali-portal' ); ?></strong></td>
						<td><?php echo esc_html( PHP_VERSION ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'WordPress Version', 'aikairali-portal' ); ?></strong></td>
						<td><?php echo esc_html( get_bloginfo( 'version' ) ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'ACF Version', 'aikairali-portal' ); ?></strong></td>
						<td>
							<?php
							if ( class_exists( 'ACF' ) ) {
								$acf_ver = defined( 'ACF_VERSION' ) ? ACF_VERSION : 'Active';
								echo esc_html( $acf_ver );
							} else {
								echo '<span style="color: red;">' . esc_html__( 'Inactive / Missing', 'aikairali-portal' ) . '</span>';
							}
							?>
						</td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Database Table Prefix', 'aikairali-portal' ); ?></strong></td>
						<td><?php echo esc_html( $wpdb->prefix ); ?></td>
					</tr>
					<tr>
						<td><strong><?php esc_html_e( 'Memory Limit', 'aikairali-portal' ); ?></strong></td>
						<td><?php echo esc_html( WP_MEMORY_LIMIT ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<?php
	}
}
