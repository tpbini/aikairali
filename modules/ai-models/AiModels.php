<?php
namespace AIKairali\Portal\Modules\AiModels;

use AIKairali\Portal\Core\ModuleInterface;
use AIKairali\Portal\Core\CPT;
use AIKairali\Portal\Core\Taxonomy;
use AIKairali\Portal\Core\ACFLoader;

/**
 * Class AiModels
 *
 * Implements the AI Models Module.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Modules/AiModels
 * @since      1.0.0
 */
class AiModels implements ModuleInterface {

	/**
	 * Initialize the module by registering hooks.
	 */
	public function init(): void {
		// Custom Admin Columns.
		add_filter( 'manage_ai-models_posts_columns', [ $this, 'add_admin_columns' ] );
		add_action( 'manage_ai-models_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );

		// SEO JSON-LD injection.
		add_action( 'wp_head', [ $this, 'inject_json_ld_schema' ] );

		// Hook into core REST API registration.
		add_action( 'aikairali_rest_api_init', [ $this, 'register_rest_endpoints' ], 10, 2 );

		// Flush cache when models are saved or deleted.
		add_action( 'save_post_ai-models', [ $this, 'clear_module_cache' ] );
		add_action( 'deleted_post', [ $this, 'clear_module_cache' ] );
	}

	/**
	 * Register Custom Post Types.
	 */
	public function register_cpts(): void {
		CPT::register(
			'ai-models',
			__( 'AI Model', 'aikairali-portal' ),
			__( 'AI Models', 'aikairali-portal' ),
			[
				'menu_icon'    => 'dashicons-chart-line', // Appropriate for benchmarks/models
				'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
				'has_archive'  => 'ai-models',
				'rewrite'      => [ 'slug' => 'ai-models', 'with_front' => false ],
			]
		);
	}

	/**
	 * Register Taxonomies.
	 */
	public function register_taxonomies(): void {
		// Provider.
		Taxonomy::register(
			'model-provider',
			'ai-models',
			__( 'Provider', 'aikairali-portal' ),
			__( 'Providers', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'model-provider' ] ]
		);

		// Input Type.
		Taxonomy::register(
			'model-input-type',
			'ai-models',
			__( 'Input Type', 'aikairali-portal' ),
			__( 'Input Types', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'model-input-type' ] ]
		);

		// Output Type.
		Taxonomy::register(
			'model-output-type',
			'ai-models',
			__( 'Output Type', 'aikairali-portal' ),
			__( 'Output Types', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'model-output-type' ] ]
		);
	}

	/**
	 * Register ACF Field Groups programmatically.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		// Field Group: Model Details.
		ACFLoader::register_field_group( [
			'key'      => 'group_model_details',
			'title'    => __( 'Model Details', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_model_version',
					'label' => __( 'Version', 'aikairali-portal' ),
					'name'  => 'version',
					'type'  => 'text',
					'wrapper' => [ 'width' => '33' ],
				],
				[
					'key'   => 'field_model_release_date',
					'label' => __( 'Release Date', 'aikairali-portal' ),
					'name'  => 'release_date',
					'type'  => 'date_picker',
					'display_format' => 'Y-m-d',
					'return_format'  => 'Y-m-d',
					'wrapper' => [ 'width' => '33' ],
				],
				[
					'key'   => 'field_model_pricing',
					'label' => __( 'Pricing', 'aikairali-portal' ),
					'name'  => 'pricing',
					'type'  => 'text',
					'placeholder' => __( 'e.g. Free, $0.01/1k tokens', 'aikairali-portal' ),
					'wrapper' => [ 'width' => '33' ],
				],
				[
					'key'     => 'field_model_open_source',
					'label'   => __( 'Open Source', 'aikairali-portal' ),
					'name'    => 'open_source',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'     => 'field_model_api_available',
					'label'   => __( 'API Available', 'aikairali-portal' ),
					'name'    => 'api_available',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_model_context_window',
					'label' => __( 'Context Window', 'aikairali-portal' ),
					'name'  => 'context_window',
					'type'  => 'text',
					'placeholder' => __( 'e.g. 128k tokens', 'aikairali-portal' ),
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_model_parameters',
					'label' => __( 'Parameters', 'aikairali-portal' ),
					'name'  => 'parameters',
					'type'  => 'text',
					'placeholder' => __( 'e.g. 70B', 'aikairali-portal' ),
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_model_docs_url',
					'label' => __( 'Documentation URL', 'aikairali-portal' ),
					'name'  => 'documentation_url',
					'type'  => 'url',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_model_playground_url',
					'label' => __( 'Playground URL', 'aikairali-portal' ),
					'name'  => 'playground_url',
					'type'  => 'url',
					'wrapper' => [ 'width' => '50' ],
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-models',
					],
				],
			],
		] );

		// Field Group: Model Content & Evaluation.
		ACFLoader::register_field_group( [
			'key'      => 'group_model_evaluation',
			'title'    => __( 'Model Content & Evaluation', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'          => 'field_model_benchmarks',
					'label'        => __( 'Benchmarks', 'aikairali-portal' ),
					'name'         => 'benchmarks',
					'type'         => 'wysiwyg',
					'instructions' => __( 'Use tables to format benchmark scores.', 'aikairali-portal' ),
					'media_upload' => 1,
				],
				[
					'key'   => 'field_model_example_usage',
					'label' => __( 'Example Usage', 'aikairali-portal' ),
					'name'  => 'example_usage',
					'type'  => 'textarea',
					'rows'  => 4,
				],
				[
					'key'         => 'field_model_strengths',
					'label'       => __( 'Strengths', 'aikairali-portal' ),
					'name'        => 'strengths',
					'type'        => 'textarea',
					'rows'        => 4,
					'placeholder' => __( 'One strength per line', 'aikairali-portal' ),
					'wrapper'     => [ 'width' => '50' ],
				],
				[
					'key'         => 'field_model_limitations',
					'label'       => __( 'Limitations', 'aikairali-portal' ),
					'name'        => 'limitations',
					'type'        => 'textarea',
					'rows'        => 4,
					'placeholder' => __( 'One limitation per line', 'aikairali-portal' ),
					'wrapper'     => [ 'width' => '50' ],
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-models',
					],
				],
			],
		] );

		// Field Group: SEO Overrides.
		ACFLoader::register_field_group( [
			'key'      => 'group_model_seo',
			'title'    => __( 'SEO Meta Overrides', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_model_seo_title',
					'label' => __( 'SEO Title', 'aikairali-portal' ),
					'name'  => 'seo_title',
					'type'  => 'text',
				],
				[
					'key'   => 'field_model_seo_desc',
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
						'value'    => 'ai-models',
					],
				],
			],
		] );
	}

	/**
	 * Add custom columns to the WP Admin AI Models list table.
	 *
	 * @param array $columns Current columns.
	 * @return array Modified columns.
	 */
	public function add_admin_columns( array $columns ): array {
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			if ( 'date' === $key ) {
				$new_columns['model_provider'] = __( 'Provider', 'aikairali-portal' );
				$new_columns['model_version']  = __( 'Version', 'aikairali-portal' );
				$new_columns['model_badges']   = __( 'Badges', 'aikairali-portal' );
				$new_columns['model_context']  = __( 'Context', 'aikairali-portal' );
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
			case 'model_provider':
				$terms = get_the_term_list( $post_id, 'model-provider', '', ', ', '' );
				echo $terms ? wp_kses_post( $terms ) : '—';
				break;

			case 'model_version':
				$version = get_field( 'version', $post_id );
				echo esc_html( $version ?: '—' );
				break;

			case 'model_badges':
				$badges = [];
				if ( get_field( 'open_source', $post_id ) ) {
					$badges[] = '<span style="color:green; font-weight:bold;">' . esc_html__( 'Open Source', 'aikairali-portal' ) . '</span>';
				}
				if ( get_field( 'api_available', $post_id ) ) {
					$badges[] = 'API';
				}
				echo ! empty( $badges ) ? implode( ' | ', $badges ) : '—';
				break;

			case 'model_context':
				$context = get_field( 'context_window', $post_id );
				echo esc_html( $context ?: '—' );
				break;
		}
	}

	/**
	 * Inject Google-compliant JSON-LD SoftwareApplication schema.
	 */
	public function inject_json_ld_schema(): void {
		if ( ! is_singular( 'ai-models' ) ) {
			return;
		}

		$post_id  = get_the_ID();
		$settings = get_option( 'aikairali_portal_settings', [] );
		$enable   = $settings['seo']['enable_json_ld'] ?? '1';

		if ( '1' !== $enable ) {
			return;
		}

		$providers = wp_get_post_terms( $post_id, 'model-provider', [ 'fields' => 'names' ] );
		$provider  = ! empty( $providers ) ? reset( $providers ) : '';
		
		$version     = get_field( 'version', $post_id );
		$description = get_field( 'seo_description', $post_id ) ?: wp_trim_words( get_post_field( 'post_excerpt', $post_id ), 20 );

		$schema = [
			'@context'            => 'https://schema.org',
			'@type'               => 'SoftwareApplication',
			'name'                => get_the_title( $post_id ),
			'description'         => esc_html( $description ),
			'applicationCategory' => 'Artificial Intelligence Model',
			'operatingSystem'     => 'Web/API',
		];

		if ( $provider ) {
			$schema['author'] = [
				'@type' => 'Organization',
				'name'  => esc_html( $provider ),
			];
		}
		
		if ( $version ) {
			$schema['softwareVersion'] = esc_html( $version );
		}

		echo "\n<!-- AIKairali Model Structured Data -->\n";
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
			'/models',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_models_api' ],
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
	public function get_models_api( \WP_REST_Request $request ): \WP_REST_Response {
		$page     = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;
		$per_page = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 10;
		$search   = $request->get_param( 's' ) ? sanitize_text_field( $request->get_param( 's' ) ) : '';
		
		$args = [
			'post_type'      => 'ai-models',
			'post_status'    => 'publish',
			'paged'          => $page,
			'posts_per_page' => $per_page,
		];

		if ( $search ) {
			$args['s'] = $search;
		}

		// Filters
		$provider = $request->get_param( 'provider' ) ? sanitize_text_field( $request->get_param( 'provider' ) ) : '';
		if ( $provider ) {
			$args['tax_query'][] = [
				'taxonomy' => 'model-provider',
				'field'    => 'slug',
				'terms'    => $provider,
			];
		}

		$query = new \WP_Query( $args );
		$models = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				
				$providers = wp_get_post_terms( $post_id, 'model-provider', [ 'fields' => 'names' ] );
				$inputs    = wp_get_post_terms( $post_id, 'model-input-type', [ 'fields' => 'names' ] );
				$outputs   = wp_get_post_terms( $post_id, 'model-output-type', [ 'fields' => 'names' ] );
				
				$models[] = [
					'id'             => $post_id,
					'title'          => get_the_title(),
					'slug'           => get_post_field( 'post_name', $post_id ),
					'date'           => get_the_date( 'c' ),
					'providers'      => $providers,
					'input_types'    => $inputs,
					'output_types'   => $outputs,
					'version'        => get_field( 'version', $post_id ),
					'release_date'   => get_field( 'release_date', $post_id ),
					'open_source'    => get_field( 'open_source', $post_id ),
					'api_available'  => get_field( 'api_available', $post_id ),
					'context_window' => get_field( 'context_window', $post_id ),
					'parameters'     => get_field( 'parameters', $post_id ),
					'pricing'        => get_field( 'pricing', $post_id ),
					'url'            => get_permalink(),
				];
			}
			wp_reset_postdata();
		}

		return new \WP_REST_Response( [
			'total' => $query->found_posts,
			'pages' => $query->max_num_pages,
			'data'  => $models,
		], 200 );
	}

	/**
	 * Flush module-specific transient caches on save or delete.
	 */
	public function clear_module_cache(): void {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_models_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_models_%'" );
		
		// Flush general search cache.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_search_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_search_%'" );
	}
}
