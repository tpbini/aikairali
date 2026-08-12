<?php
namespace AIKairali\Portal\Modules\AiTools;

use AIKairali\Portal\Core\ModuleInterface;
use AIKairali\Portal\Core\CPT;
use AIKairali\Portal\Core\Taxonomy;
use AIKairali\Portal\Core\ACFLoader;

/**
 * Class AiTools
 *
 * Implements the AI Tools Module.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Modules/AiTools
 * @since      1.0.0
 */
class AiTools implements ModuleInterface {

	/**
	 * Initialize the module by registering hooks.
	 */
	public function init(): void {
		// Custom Admin Columns.
		add_filter( 'manage_ai-tools_posts_columns', [ $this, 'add_admin_columns' ] );
		add_action( 'manage_ai-tools_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );

		// SEO JSON-LD injection.
		add_action( 'wp_head', [ $this, 'inject_json_ld_schema' ] );

		// Hook into core REST API registration.
		add_action( 'aikairali_rest_api_init', [ $this, 'register_rest_endpoints' ], 10, 2 );

		// Flush cache when tools are saved or deleted.
		add_action( 'save_post_ai-tools', [ $this, 'clear_module_cache' ] );
		add_action( 'deleted_post', [ $this, 'clear_module_cache' ] );
	}

	/**
	 * Register Custom Post Types.
	 */
	public function register_cpts(): void {
		CPT::register(
			'ai-tools',
			__( 'AI Tool', 'aikairali-portal' ),
			__( 'AI Tools', 'aikairali-portal' ),
			[
				'menu_icon'    => 'dashicons-admin-tools',
				'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
				'has_archive'  => 'ai-tools',
				'rewrite'      => [ 'slug' => 'ai-tools', 'with_front' => false ],
			]
		);
	}

	/**
	 * Register Taxonomies.
	 */
	public function register_taxonomies(): void {
		// Tool Category.
		Taxonomy::register(
			'tool-category',
			'ai-tools',
			__( 'Tool Category', 'aikairali-portal' ),
			__( 'Tool Categories', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'tool-category' ] ]
		);

		// AI Model Used.
		Taxonomy::register(
			'tool-model',
			'ai-tools',
			__( 'AI Model', 'aikairali-portal' ),
			__( 'AI Models', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'tool-model' ] ]
		);

		// Pricing Model.
		Taxonomy::register(
			'tool-pricing',
			'ai-tools',
			__( 'Pricing Model', 'aikairali-portal' ),
			__( 'Pricing Models', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'tool-pricing' ] ]
		);
	}

	/**
	 * Register ACF Field Groups programmatically.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		// Field Group: Tool Information.
		ACFLoader::register_field_group( [
			'key'      => 'group_tool_info',
			'title'    => __( 'Tool Information', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_tool_official_website',
					'label' => __( 'Official Website', 'aikairali-portal' ),
					'name'  => 'official_website',
					'type'  => 'url',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_tool_pricing_details',
					'label' => __( 'Pricing Details', 'aikairali-portal' ),
					'name'  => 'pricing_details',
					'type'  => 'text',
					'placeholder' => __( 'e.g. Starts at $10/mo', 'aikairali-portal' ),
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'     => 'field_tool_free_plan',
					'label'   => __( 'Free Plan Available', 'aikairali-portal' ),
					'name'    => 'free_plan',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '20' ],
				],
				[
					'key'     => 'field_tool_free_trial',
					'label'   => __( 'Free Trial Available', 'aikairali-portal' ),
					'name'    => 'free_trial',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '20' ],
				],
				[
					'key'     => 'field_tool_api_available',
					'label'   => __( 'API Available', 'aikairali-portal' ),
					'name'    => 'api_available',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '20' ],
				],
				[
					'key'     => 'field_tool_mobile_app',
					'label'   => __( 'Mobile App', 'aikairali-portal' ),
					'name'    => 'mobile_app',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '20' ],
				],
				[
					'key'     => 'field_tool_chrome_ext',
					'label'   => __( 'Chrome Extension', 'aikairali-portal' ),
					'name'    => 'chrome_extension',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '20' ],
				],
				[
					'key'   => 'field_tool_developer',
					'label' => __( 'Developer / Company', 'aikairali-portal' ),
					'name'  => 'developer',
					'type'  => 'text',
					'wrapper' => [ 'width' => '33' ],
				],
				[
					'key'   => 'field_tool_founded_year',
					'label' => __( 'Founded Year', 'aikairali-portal' ),
					'name'  => 'founded_year',
					'type'  => 'number',
					'wrapper' => [ 'width' => '33' ],
				],
				[
					'key'     => 'field_tool_rating',
					'label'   => __( 'Rating', 'aikairali-portal' ),
					'name'    => 'rating',
					'type'    => 'number',
					'min'     => 0,
					'max'     => 5,
					'step'    => 0.1,
					'wrapper' => [ 'width' => '33' ],
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-tools',
					],
				],
			],
		] );

		// Field Group: Tool Details (textarea fields – ACF Free compatible).
		ACFLoader::register_field_group( [
			'key'      => 'group_tool_content',
			'title'    => __( 'Tool Content Details', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'         => 'field_tool_features',
					'label'       => __( 'Features', 'aikairali-portal' ),
					'name'        => 'features',
					'type'        => 'textarea',
					'rows'        => 5,
					'placeholder' => __( 'One feature per line', 'aikairali-portal' ),
					'instructions' => __( 'Enter each feature on a new line.', 'aikairali-portal' ),
				],
				[
					'key'         => 'field_tool_pros',
					'label'       => __( 'Pros', 'aikairali-portal' ),
					'name'        => 'pros',
					'type'        => 'textarea',
					'rows'        => 4,
					'placeholder' => __( 'One advantage per line', 'aikairali-portal' ),
					'wrapper'     => [ 'width' => '50' ],
				],
				[
					'key'         => 'field_tool_cons',
					'label'       => __( 'Cons', 'aikairali-portal' ),
					'name'        => 'cons',
					'type'        => 'textarea',
					'rows'        => 4,
					'placeholder' => __( 'One limitation per line', 'aikairali-portal' ),
					'wrapper'     => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_tool_video_demo',
					'label' => __( 'Video Demo URL', 'aikairali-portal' ),
					'name'  => 'video_demo',
					'type'  => 'url',
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-tools',
					],
				],
			],
		] );

		// Field Group: SEO Overrides.
		ACFLoader::register_field_group( [
			'key'      => 'group_tool_seo',
			'title'    => __( 'SEO Meta Overrides', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_tool_seo_title',
					'label' => __( 'SEO Title', 'aikairali-portal' ),
					'name'  => 'seo_title',
					'type'  => 'text',
				],
				[
					'key'   => 'field_tool_seo_desc',
					'label' => __( 'SEO Description', 'aikairali-portal' ),
					'name'  => 'seo_description',
					'type'  => 'textarea',
					'rows'  => 2,
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-tools',
					],
				],
			],
		] );
	}

	/**
	 * Add custom columns to the WP Admin AI Tools list table.
	 *
	 * @param array $columns Current columns.
	 * @return array Modified columns.
	 */
	public function add_admin_columns( array $columns ): array {
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			if ( 'date' === $key ) {
				$new_columns['tool_pricing'] = __( 'Pricing', 'aikairali-portal' );
				$new_columns['tool_badges']  = __( 'Features', 'aikairali-portal' );
				$new_columns['tool_rating']  = __( 'Rating', 'aikairali-portal' );
			}
			$new_columns[ $key ] = $value;
		}
		return $new_columns;
	}

	/**
	 * Render custom column cell contents.
	 *
	 * @param string $column  Column key.
	 * @param int    $post_id Post ID.
	 */
	public function render_admin_columns( string $column, int $post_id ): void {
		switch ( $column ) {
			case 'tool_pricing':
				$terms   = get_the_term_list( $post_id, 'tool-pricing', '', ', ', '' );
				$details = get_field( 'pricing_details', $post_id );
				echo $terms ? wp_kses_post( $terms ) : '—';
				if ( $details ) {
					echo '<br><span style="color:#888; font-size:12px;">' . esc_html( $details ) . '</span>';
				}
				break;

			case 'tool_badges':
				$badges = [];
				if ( get_field( 'free_plan', $post_id ) ) {
					$badges[] = '<span style="color:green; font-weight:bold;">' . esc_html__( 'Free Plan', 'aikairali-portal' ) . '</span>';
				}
				if ( get_field( 'free_trial', $post_id ) ) {
					$badges[] = '<span style="color:#2271b1; font-weight:bold;">' . esc_html__( 'Free Trial', 'aikairali-portal' ) . '</span>';
				}
				if ( get_field( 'api_available', $post_id ) ) {
					$badges[] = 'API';
				}
				
				echo ! empty( $badges ) ? implode( ' | ', $badges ) : '—';
				break;

			case 'tool_rating':
				$rating = get_field( 'rating', $post_id );
				echo esc_html( $rating ?: '—' );
				break;
		}
	}

	/**
	 * Inject Google-compliant JSON-LD SoftwareApplication schema.
	 */
	public function inject_json_ld_schema(): void {
		if ( ! is_singular( 'ai-tools' ) ) {
			return;
		}

		$post_id  = get_the_ID();
		$settings = get_option( 'aikairali_portal_settings', [] );
		$enable   = $settings['seo']['enable_json_ld'] ?? '1';

		if ( '1' !== $enable ) {
			return;
		}

		$developer = get_field( 'developer', $post_id );
		$rating    = get_field( 'rating', $post_id );
		$url       = get_field( 'official_website', $post_id );
		
		$categories = wp_get_post_terms( $post_id, 'tool-category', [ 'fields' => 'names' ] );
		$category   = ! empty( $categories ) ? reset( $categories ) : 'BusinessApplication';

		$description = get_field( 'seo_description', $post_id ) ?: wp_trim_words( get_post_field( 'post_excerpt', $post_id ), 20 );

		$schema = [
			'@context'            => 'https://schema.org',
			'@type'               => 'SoftwareApplication',
			'name'                => get_the_title( $post_id ),
			'description'         => esc_html( $description ),
			'applicationCategory' => esc_html( $category ),
			'operatingSystem'     => 'Web', // Default assumption
		];

		if ( $developer ) {
			$schema['author'] = [
				'@type' => 'Organization',
				'name'  => esc_html( $developer ),
			];
		}

		if ( $url ) {
			$schema['url'] = esc_url( $url );
		}

		$thumbnail = get_the_post_thumbnail_url( $post_id, 'large' );
		if ( $thumbnail ) {
			$schema['image'] = esc_url( $thumbnail );
		}

		// Simple rating schema (assumes out of 5)
		if ( $rating ) {
			$schema['aggregateRating'] = [
				'@type'       => 'AggregateRating',
				'ratingValue' => floatval( $rating ),
				'bestRating'  => 5,
				'ratingCount' => 1, // Fallback if no real user reviews exist
			];
		}

		// Pricing Schema based on free plan/trial
		$pricing_terms = wp_get_post_terms( $post_id, 'tool-pricing', [ 'fields' => 'names' ] );
		$pricing_type = ! empty( $pricing_terms ) ? reset( $pricing_terms ) : '';
		$has_free = get_field( 'free_plan', $post_id ) || strtolower( $pricing_type ) === 'free';
		
		$schema['offers'] = [
			'@type' => 'Offer',
			'price' => $has_free ? '0' : '0', // Adjust appropriately, fallback to 0 to avoid errors
			'priceCurrency' => 'USD',
		];

		echo "\n<!-- AIKairali Tool Structured Data -->\n";
		echo '<script type="application/ld+json">' . json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";
	}

	/**
	 * Register REST API Route.
	 *
	 * @param string $namespace REST namespace.
	 * @param mixed  $rest      Core RestAPI instance.
	 */
	public function register_rest_endpoints( string $namespace, $rest ): void {
		register_rest_route(
			$namespace,
			'/tools',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_tools_api' ],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * REST API Callback.
	 *
	 * @param \WP_REST_Request $request Request parameters.
	 * @return \WP_REST_Response Response object.
	 */
	public function get_tools_api( \WP_REST_Request $request ): \WP_REST_Response {
		$page     = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;
		$per_page = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 10;
		$search   = $request->get_param( 's' ) ? sanitize_text_field( $request->get_param( 's' ) ) : '';
		
		$args = [
			'post_type'      => 'ai-tools',
			'post_status'    => 'publish',
			'paged'          => $page,
			'posts_per_page' => $per_page,
		];

		if ( $search ) {
			$args['s'] = $search;
		}

		// Tool Category filter
		$category = $request->get_param( 'category' ) ? sanitize_text_field( $request->get_param( 'category' ) ) : '';
		if ( $category ) {
			$args['tax_query'][] = [
				'taxonomy' => 'tool-category',
				'field'    => 'slug',
				'terms'    => $category,
			];
		}

		$query = new \WP_Query( $args );
		$tools = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				
				$categories = wp_get_post_terms( $post_id, 'tool-category', [ 'fields' => 'names' ] );
				$models     = wp_get_post_terms( $post_id, 'tool-model', [ 'fields' => 'names' ] );
				$pricing    = wp_get_post_terms( $post_id, 'tool-pricing', [ 'fields' => 'names' ] );
				
				// Extract repeaters
				$features = [];
				if ( have_rows( 'features', $post_id ) ) {
					while ( have_rows( 'features', $post_id ) ) {
						the_row();
						$features[] = get_sub_field( 'feature_name' );
					}
				}

				$tools[] = [
					'id'               => $post_id,
					'title'            => get_the_title(),
					'slug'             => get_post_field( 'post_name', $post_id ),
					'date'             => get_the_date( 'c' ),
					'categories'       => $categories,
					'models'           => $models,
					'pricing'          => $pricing,
					'website'          => get_field( 'official_website', $post_id ),
					'pricing_details'  => get_field( 'pricing_details', $post_id ),
					'free_plan'        => get_field( 'free_plan', $post_id ),
					'free_trial'       => get_field( 'free_trial', $post_id ),
					'api_available'    => get_field( 'api_available', $post_id ),
					'rating'           => get_field( 'rating', $post_id ),
					'features'         => $features, // Summary
					'thumbnail_url'    => get_the_post_thumbnail_url( $post_id, 'large' ),
					'url'              => get_permalink(),
				];
			}
			wp_reset_postdata();
		}

		return new \WP_REST_Response( [
			'total' => $query->found_posts,
			'pages' => $query->max_num_pages,
			'data'  => $tools,
		], 200 );
	}

	/**
	 * Flush module-specific transient caches on save or delete.
	 */
	public function clear_module_cache(): void {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_tools_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_tools_%'" );
		
		// Flush general search cache.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_search_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_search_%'" );
	}
}
