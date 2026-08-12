<?php
namespace AIKairali\Portal\Modules\AiPrompts;

use AIKairali\Portal\Core\ModuleInterface;
use AIKairali\Portal\Core\CPT;
use AIKairali\Portal\Core\Taxonomy;
use AIKairali\Portal\Core\ACFLoader;

/**
 * Class AiPrompts
 *
 * Implements the AI Prompts Module.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Modules/AiPrompts
 * @since      1.0.0
 */
class AiPrompts implements ModuleInterface {

	/**
	 * Initialize the module by registering hooks.
	 */
	public function init(): void {
		// Custom Admin Columns.
		add_filter( 'manage_ai-prompts_posts_columns', [ $this, 'add_admin_columns' ] );
		add_action( 'manage_ai-prompts_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );

		// SEO JSON-LD injection.
		add_action( 'wp_head', [ $this, 'inject_json_ld_schema' ] );

		// Hook into core REST API registration.
		add_action( 'aikairali_rest_api_init', [ $this, 'register_rest_endpoints' ], 10, 2 );

		// Flush cache when prompts are saved or deleted.
		add_action( 'save_post_ai-prompts', [ $this, 'clear_module_cache' ] );
		add_action( 'deleted_post', [ $this, 'clear_module_cache' ] );
	}

	/**
	 * Register Custom Post Types.
	 */
	public function register_cpts(): void {
		CPT::register(
			'ai-prompts',
			__( 'AI Prompt', 'aikairali-portal' ),
			__( 'AI Prompts', 'aikairali-portal' ),
			[
				'menu_icon'    => 'dashicons-testimonial', // Message/Quote icon fits prompts well
				'supports'     => [ 'title', 'author', 'excerpt', 'revisions' ],
				'has_archive'  => 'ai-prompts',
				'rewrite'      => [ 'slug' => 'ai-prompts', 'with_front' => false ],
			]
		);
	}

	/**
	 * Register Taxonomies.
	 */
	public function register_taxonomies(): void {
		// Prompt Category.
		Taxonomy::register(
			'prompt-category',
			'ai-prompts',
			__( 'Prompt Category', 'aikairali-portal' ),
			__( 'Prompt Categories', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'prompt-category' ] ]
		);

		// AI Platform.
		Taxonomy::register(
			'prompt-platform',
			'ai-prompts',
			__( 'AI Platform', 'aikairali-portal' ),
			__( 'AI Platforms', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'prompt-platform' ] ]
		);

		// Difficulty.
		Taxonomy::register(
			'prompt-difficulty',
			'ai-prompts',
			__( 'Difficulty', 'aikairali-portal' ),
			__( 'Difficulties', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'prompt-difficulty' ] ]
		);
	}

	/**
	 * Register ACF Field Groups programmatically.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		// Field Group: Prompt Details.
		ACFLoader::register_field_group( [
			'key'      => 'group_prompt_details',
			'title'    => __( 'Prompt Details', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_prompt_text',
					'label' => __( 'Prompt Text', 'aikairali-portal' ),
					'name'  => 'prompt_text',
					'type'  => 'textarea',
					'rows'  => 5,
					'required' => 1,
					'instructions' => __( 'The actual prompt text for users to copy.', 'aikairali-portal' ),
				],
				[
					'key'   => 'field_prompt_variables',
					'label' => __( 'Variables', 'aikairali-portal' ),
					'name'  => 'prompt_variables',
					'type'  => 'textarea',
					'rows'  => 3,
					'instructions' => __( 'Explain the placeholders used in the prompt (e.g., [Topic], [Tone]).', 'aikairali-portal' ),
				],
				[
					'key'   => 'field_prompt_example_input',
					'label' => __( 'Example Input', 'aikairali-portal' ),
					'name'  => 'example_input',
					'type'  => 'textarea',
					'rows'  => 3,
				],
				[
					'key'   => 'field_prompt_example_output',
					'label' => __( 'Example Output', 'aikairali-portal' ),
					'name'  => 'example_output',
					'type'  => 'textarea',
					'rows'  => 4,
				],
				[
					'key'   => 'field_prompt_tips',
					'label' => __( 'Prompt Tips / Guide', 'aikairali-portal' ),
					'name'  => 'prompt_tips',
					'type'  => 'wysiwyg',
					'media_upload' => 0,
				],
				[
					'key'     => 'field_prompt_rating',
					'label'   => __( 'Rating', 'aikairali-portal' ),
					'name'    => 'rating',
					'type'    => 'number',
					'min'     => 0,
					'max'     => 5,
					'step'    => 0.1,
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'     => 'field_prompt_featured',
					'label'   => __( 'Featured Prompt', 'aikairali-portal' ),
					'name'    => 'featured',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '50' ],
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-prompts',
					],
				],
			],
		] );

		// Field Group: SEO Overrides.
		ACFLoader::register_field_group( [
			'key'      => 'group_prompt_seo',
			'title'    => __( 'SEO Meta Overrides', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_prompt_seo_title',
					'label' => __( 'SEO Title', 'aikairali-portal' ),
					'name'  => 'seo_title',
					'type'  => 'text',
				],
				[
					'key'   => 'field_prompt_seo_desc',
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
						'value'    => 'ai-prompts',
					],
				],
			],
		] );
	}

	/**
	 * Add custom columns to the WP Admin AI Prompts list table.
	 *
	 * @param array $columns Current columns.
	 * @return array Modified columns.
	 */
	public function add_admin_columns( array $columns ): array {
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			if ( 'date' === $key ) {
				$new_columns['prompt_platform']   = __( 'AI Platform', 'aikairali-portal' );
				$new_columns['prompt_difficulty'] = __( 'Difficulty', 'aikairali-portal' );
				$new_columns['prompt_rating']     = __( 'Rating', 'aikairali-portal' );
				$new_columns['prompt_featured']   = __( 'Featured', 'aikairali-portal' );
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
			case 'prompt_platform':
				$terms = get_the_term_list( $post_id, 'prompt-platform', '', ', ', '' );
				echo $terms ? wp_kses_post( $terms ) : '—';
				break;

			case 'prompt_difficulty':
				$terms = get_the_term_list( $post_id, 'prompt-difficulty', '', ', ', '' );
				echo $terms ? wp_kses_post( $terms ) : '—';
				break;

			case 'prompt_rating':
				$rating = get_field( 'rating', $post_id );
				echo esc_html( $rating ?: '—' );
				break;

			case 'prompt_featured':
				$featured = get_field( 'featured', $post_id );
				if ( $featured ) {
					echo '<span style="color:#ffb900; font-weight:bold;">★ ' . esc_html__( 'Featured', 'aikairali-portal' ) . '</span>';
				} else {
					echo '—';
				}
				break;
		}
	}

	/**
	 * Inject Google-compliant JSON-LD Schema.
	 * Prompts don't have a direct 1:1 schema, using SoftwareSourceCode as it represents executable text.
	 */
	public function inject_json_ld_schema(): void {
		if ( ! is_singular( 'ai-prompts' ) ) {
			return;
		}

		$post_id  = get_the_ID();
		$settings = get_option( 'aikairali_portal_settings', [] );
		$enable   = $settings['seo']['enable_json_ld'] ?? '1';

		if ( '1' !== $enable ) {
			return;
		}

		$description = get_field( 'seo_description', $post_id ) ?: wp_trim_words( get_post_field( 'post_excerpt', $post_id ), 20 );
		$prompt_text = get_field( 'prompt_text', $post_id );
		
		$platforms = wp_get_post_terms( $post_id, 'prompt-platform', [ 'fields' => 'names' ] );
		$platform  = ! empty( $platforms ) ? reset( $platforms ) : 'AI';

		$schema = [
			'@context'    => 'https://schema.org',
			'@type'       => 'SoftwareSourceCode', // Closest match for a prompt snippet
			'name'        => get_the_title( $post_id ),
			'description' => esc_html( $description ),
			'programmingLanguage' => [
				'@type' => 'ComputerLanguage',
				'name'  => 'Natural Language Prompt (' . esc_html( $platform ) . ')',
			],
			'text'        => esc_html( $prompt_text ),
			'author'      => [
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) ),
			],
			'datePublished' => get_the_date( 'c', $post_id ),
			'dateModified'  => get_the_modified_date( 'c', $post_id ),
		];

		echo "\n<!-- AIKairali Prompt Structured Data -->\n";
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
			'/prompts',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_prompts_api' ],
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
	public function get_prompts_api( \WP_REST_Request $request ): \WP_REST_Response {
		$page     = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;
		$per_page = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 10;
		$search   = $request->get_param( 's' ) ? sanitize_text_field( $request->get_param( 's' ) ) : '';
		
		$args = [
			'post_type'      => 'ai-prompts',
			'post_status'    => 'publish',
			'paged'          => $page,
			'posts_per_page' => $per_page,
		];

		if ( $search ) {
			$args['s'] = $search;
		}

		$query = new \WP_Query( $args );
		$prompts = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				
				$platforms  = wp_get_post_terms( $post_id, 'prompt-platform', [ 'fields' => 'names' ] );
				$categories = wp_get_post_terms( $post_id, 'prompt-category', [ 'fields' => 'names' ] );
				$difficulties = wp_get_post_terms( $post_id, 'prompt-difficulty', [ 'fields' => 'names' ] );
				
				$prompts[] = [
					'id'             => $post_id,
					'title'          => get_the_title(),
					'slug'           => get_post_field( 'post_name', $post_id ),
					'date'           => get_the_date( 'c' ),
					'author'         => get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) ),
					'platform'       => ! empty( $platforms ) ? reset( $platforms ) : '',
					'category'       => ! empty( $categories ) ? reset( $categories ) : '',
					'difficulty'     => ! empty( $difficulties ) ? reset( $difficulties ) : '',
					'prompt_text'    => get_field( 'prompt_text', $post_id ),
					'variables'      => get_field( 'prompt_variables', $post_id ),
					'example_input'  => get_field( 'example_input', $post_id ),
					'example_output' => get_field( 'example_output', $post_id ),
					'rating'         => get_field( 'rating', $post_id ),
					'featured'       => get_field( 'featured', $post_id ),
					'url'            => get_permalink(),
				];
			}
			wp_reset_postdata();
		}

		return new \WP_REST_Response( [
			'total' => $query->found_posts,
			'pages' => $query->max_num_pages,
			'data'  => $prompts,
		], 200 );
	}

	/**
	 * Flush module-specific transient caches on save or delete.
	 */
	public function clear_module_cache(): void {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_prompts_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_prompts_%'" );
		
		// Flush general search cache.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_search_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_search_%'" );
	}
}
