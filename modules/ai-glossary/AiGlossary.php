<?php
namespace AIKairali\Portal\Modules\AiGlossary;

use AIKairali\Portal\Core\ModuleInterface;
use AIKairali\Portal\Core\CPT;
use AIKairali\Portal\Core\Taxonomy;
use AIKairali\Portal\Core\ACFLoader;

/**
 * Class AiGlossary
 *
 * Implements the AI Glossary Module.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Modules/AiGlossary
 * @since      1.0.0
 */
class AiGlossary implements ModuleInterface {

	/**
	 * Initialize the module by registering hooks.
	 */
	public function init(): void {
		// Custom Admin Columns.
		add_filter( 'manage_ai-glossary_posts_columns', [ $this, 'add_admin_columns' ] );
		add_action( 'manage_ai-glossary_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );

		// SEO JSON-LD injection.
		add_action( 'wp_head', [ $this, 'inject_json_ld_schema' ] );

		// Hook into core REST API registration.
		add_action( 'aikairali_rest_api_init', [ $this, 'register_rest_endpoints' ], 10, 2 );

		// Flush cache when glossary terms are saved or deleted.
		add_action( 'save_post_ai-glossary', [ $this, 'clear_module_cache' ] );
		add_action( 'deleted_post', [ $this, 'clear_module_cache' ] );
	}

	/**
	 * Register Custom Post Types.
	 */
	public function register_cpts(): void {
		CPT::register(
			'ai-glossary',
			__( 'Glossary Term', 'aikairali-portal' ),
			__( 'AI Glossary', 'aikairali-portal' ),
			[
				'menu_icon'    => 'dashicons-book-alt',
				'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
				'has_archive'  => 'ai-glossary',
				'rewrite'      => [ 'slug' => 'ai-glossary', 'with_front' => false ],
			]
		);
	}

	/**
	 * Register Taxonomies.
	 */
	public function register_taxonomies(): void {
		// Glossary Category.
		Taxonomy::register(
			'glossary-category',
			'ai-glossary',
			__( 'Glossary Category', 'aikairali-portal' ),
			__( 'Glossary Categories', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'glossary-category' ] ]
		);
	}

	/**
	 * Register ACF Field Groups programmatically.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		// Field Group: Term Information.
		ACFLoader::register_field_group( [
			'key'      => 'group_glossary_term_info',
			'title'    => __( 'Term Information', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_glossary_pronunciation',
					'label' => __( 'Pronunciation', 'aikairali-portal' ),
					'name'  => 'pronunciation',
					'type'  => 'text',
					'placeholder' => __( 'e.g. /ˌɑːr.tɪˈfɪʃ.əl ɪnˈtel.ɪ.dʒəns/', 'aikairali-portal' ),
				],
				[
					'key'          => 'field_glossary_example',
					'label'        => __( 'Example Usage', 'aikairali-portal' ),
					'name'         => 'example',
					'type'         => 'wysiwyg',
					'media_upload' => 0,
					'toolbar'      => 'basic',
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-glossary',
					],
				],
			],
		] );

		// Field Group: Media & References.
		ACFLoader::register_field_group( [
			'key'      => 'group_glossary_media_references',
			'title'    => __( 'Media & References', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_glossary_video',
					'label' => __( 'Video Explanation', 'aikairali-portal' ),
					'name'  => 'video',
					'type'  => 'oembed',
				],
				[
					'key'         => 'field_glossary_references',
					'label'       => __( 'External References', 'aikairali-portal' ),
					'name'        => 'external_references',
					'type'        => 'textarea',
					'rows'        => 4,
					'placeholder' => __( 'One URL per line (e.g. https://example.com)', 'aikairali-portal' ),
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-glossary',
					],
				],
			],
		] );

		// Field Group: Relationships & FAQs.
		ACFLoader::register_field_group( [
			'key'      => 'group_glossary_relationships_faqs',
			'title'    => __( 'Relationships & FAQs', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'           => 'field_glossary_related',
					'label'         => __( 'Related Terms', 'aikairali-portal' ),
					'name'          => 'related_terms',
					'type'          => 'relationship',
					'post_type'     => [ 'ai-glossary' ],
					'filters'       => [ 'search', 'taxonomy' ],
					'return_format' => 'id',
				],
				[
					'key'         => 'field_glossary_faq',
					'label'       => __( 'FAQ', 'aikairali-portal' ),
					'name'        => 'faq',
					'type'        => 'textarea',
					'rows'        => 5,
					'placeholder' => __( 'Q: Question?\nA: Answer.', 'aikairali-portal' ),
					'instructions' => __( 'Enter Q: and A: pairs, one per line.', 'aikairali-portal' ),
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-glossary',
					],
				],
			],
		] );

		// Field Group: SEO Overrides.
		ACFLoader::register_field_group( [
			'key'      => 'group_glossary_seo',
			'title'    => __( 'SEO Meta Overrides', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_glossary_seo_title',
					'label' => __( 'SEO Title', 'aikairali-portal' ),
					'name'  => 'seo_title',
					'type'  => 'text',
				],
				[
					'key'   => 'field_glossary_seo_desc',
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
						'value'    => 'ai-glossary',
					],
				],
			],
		] );
	}

	/**
	 * Add custom columns to the WP Admin AI Glossary list table.
	 *
	 * @param array $columns Current columns.
	 * @return array Modified columns.
	 */
	public function add_admin_columns( array $columns ): array {
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			if ( 'date' === $key ) {
				$new_columns['glossary_category'] = __( 'Category', 'aikairali-portal' );
				$new_columns['glossary_media']    = __( 'Media', 'aikairali-portal' );
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
			case 'glossary_category':
				$terms = get_the_term_list( $post_id, 'glossary-category', '', ', ', '' );
				echo $terms ? wp_kses_post( $terms ) : '—';
				break;

			case 'glossary_media':
				$badges = [];
				if ( has_post_thumbnail( $post_id ) ) {
					$badges[] = '<span class="dashicons dashicons-format-image" title="' . esc_attr__( 'Illustration', 'aikairali-portal' ) . '"></span>';
				}
				if ( get_field( 'video', $post_id ) ) {
					$badges[] = '<span class="dashicons dashicons-format-video" title="' . esc_attr__( 'Video', 'aikairali-portal' ) . '"></span>';
				}
				echo ! empty( $badges ) ? implode( ' ', $badges ) : '—';
				break;
		}
	}

	/**
	 * Inject Google-compliant JSON-LD DefinedTerm schema.
	 */
	public function inject_json_ld_schema(): void {
		if ( ! is_singular( 'ai-glossary' ) ) {
			return;
		}

		$post_id  = get_the_ID();
		$settings = get_option( 'aikairali_portal_settings', [] );
		$enable   = $settings['seo']['enable_json_ld'] ?? '1';

		if ( '1' !== $enable ) {
			return;
		}

		$definition = get_field( 'seo_description', $post_id ) ?: wp_trim_words( get_post_field( 'post_content', $post_id ), 30 );
		$name       = get_the_title( $post_id );
		
		$schema = [
			'@context'    => 'https://schema.org',
			'@type'       => 'DefinedTerm',
			'name'        => esc_html( $name ),
			'description' => esc_html( $definition ),
			'inDefinedTermSet' => esc_url( get_post_type_archive_link( 'ai-glossary' ) ),
		];

		// Include FAQPage schema if FAQs exist
		$faqs = [];
		if ( have_rows( 'faq', $post_id ) ) {
			while ( have_rows( 'faq', $post_id ) ) {
				the_row();
				$q = get_sub_field( 'question' );
				$a = get_sub_field( 'answer' );
				if ( $q && $a ) {
					$faqs[] = [
						'@type' => 'Question',
						'name'  => esc_html( $q ),
						'acceptedAnswer' => [
							'@type' => 'Answer',
							'text'  => esc_html( $a ),
						],
					];
				}
			}
		}

		echo "\n<!-- AIKairali Glossary Structured Data -->\n";
		echo '<script type="application/ld+json">' . json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";

		if ( ! empty( $faqs ) ) {
			$faq_schema = [
				'@context'   => 'https://schema.org',
				'@type'      => 'FAQPage',
				'mainEntity' => $faqs,
			];
			echo "\n<!-- AIKairali FAQ Structured Data -->\n";
			echo '<script type="application/ld+json">' . json_encode( $faq_schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";
		}
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
			'/glossary',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_glossary_api' ],
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
	public function get_glossary_api( \WP_REST_Request $request ): \WP_REST_Response {
		$page     = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;
		$per_page = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 50; // Usually glossaries are fetched in larger batches
		$search   = $request->get_param( 's' ) ? sanitize_text_field( $request->get_param( 's' ) ) : '';
		
		$args = [
			'post_type'      => 'ai-glossary',
			'post_status'    => 'publish',
			'paged'          => $page,
			'posts_per_page' => $per_page,
			'orderby'        => 'title',
			'order'          => 'ASC', // Alphabetical ordering is standard for glossaries
		];

		if ( $search ) {
			$args['s'] = $search;
		}

		$category = $request->get_param( 'category' ) ? sanitize_text_field( $request->get_param( 'category' ) ) : '';
		if ( $category ) {
			$args['tax_query'][] = [
				'taxonomy' => 'glossary-category',
				'field'    => 'slug',
				'terms'    => $category,
			];
		}

		$query = new \WP_Query( $args );
		$terms = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				
				$categories = wp_get_post_terms( $post_id, 'glossary-category', [ 'fields' => 'names' ] );
				
				$terms[] = [
					'id'            => $post_id,
					'title'         => get_the_title(),
					'slug'          => get_post_field( 'post_name', $post_id ),
					'categories'    => $categories,
					'pronunciation' => get_field( 'pronunciation', $post_id ),
					'thumbnail_url' => get_the_post_thumbnail_url( $post_id, 'thumbnail' ),
					'url'           => get_permalink(),
				];
			}
			wp_reset_postdata();
		}

		return new \WP_REST_Response( [
			'total' => $query->found_posts,
			'pages' => $query->max_num_pages,
			'data'  => $terms,
		], 200 );
	}

	/**
	 * Flush module-specific transient caches on save or delete.
	 */
	public function clear_module_cache(): void {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_glossary_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_glossary_%'" );
		
		// Flush general search cache.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_search_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_search_%'" );
	}
}
