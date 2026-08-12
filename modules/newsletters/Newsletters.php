<?php
namespace AIKairali\Portal\Modules\Newsletters;

use AIKairali\Portal\Core\ModuleInterface;
use AIKairali\Portal\Core\CPT;
use AIKairali\Portal\Core\Taxonomy;
use AIKairali\Portal\Core\ACFLoader;

/**
 * Class Newsletters
 *
 * Implements the Newsletters Module.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Modules/Newsletters
 * @since      1.0.0
 */
class Newsletters implements ModuleInterface {

	/**
	 * Initialize the module by registering hooks.
	 */
	public function init(): void {
		// Custom Admin Columns.
		add_filter( 'manage_newsletters_posts_columns', [ $this, 'add_admin_columns' ] );
		add_action( 'manage_newsletters_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );

		// SEO JSON-LD injection.
		add_action( 'wp_head', [ $this, 'inject_json_ld_schema' ] );

		// Hook into core REST API registration.
		add_action( 'aikairali_rest_api_init', [ $this, 'register_rest_endpoints' ], 10, 2 );

		// Flush cache when newsletters are saved or deleted.
		add_action( 'save_post_newsletters', [ $this, 'clear_module_cache' ] );
		add_action( 'deleted_post', [ $this, 'clear_module_cache' ] );
	}

	/**
	 * Register Custom Post Types.
	 */
	public function register_cpts(): void {
		CPT::register(
			'newsletters',
			__( 'Newsletter', 'aikairali-portal' ),
			__( 'Newsletters', 'aikairali-portal' ),
			[
				'menu_icon'    => 'dashicons-email-alt',
				'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
				'has_archive'  => 'newsletters',
				'rewrite'      => [ 'slug' => 'newsletters', 'with_front' => false ],
			]
		);
	}

	/**
	 * Register Taxonomies.
	 */
	public function register_taxonomies(): void {
		// Newsletter Category.
		Taxonomy::register(
			'newsletter-category',
			'newsletters',
			__( 'Newsletter Category', 'aikairali-portal' ),
			__( 'Newsletter Categories', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'newsletter-category' ] ]
		);
	}

	/**
	 * Register ACF Field Groups programmatically.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		// Field Group: Issue Details.
		ACFLoader::register_field_group( [
			'key'      => 'group_newsletter_details',
			'title'    => __( 'Issue Details', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_newsletter_issue_num',
					'label' => __( 'Issue Number', 'aikairali-portal' ),
					'name'  => 'issue_number',
					'type'  => 'number',
					'wrapper' => [ 'width' => '33' ],
				],
				[
					'key'   => 'field_newsletter_pub_date',
					'label' => __( 'Publish Date', 'aikairali-portal' ),
					'name'  => 'publish_date',
					'type'  => 'date_picker',
					'display_format' => 'F j, Y',
					'return_format'  => 'Y-m-d',
					'wrapper' => [ 'width' => '33' ],
				],
				[
					'key'   => 'field_newsletter_email_subject',
					'label' => __( 'Email Subject', 'aikairali-portal' ),
					'name'  => 'email_subject',
					'type'  => 'text',
					'wrapper' => [ 'width' => '33' ],
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'newsletters',
					],
				],
			],
		] );

		// Field Group: Media & Links.
		ACFLoader::register_field_group( [
			'key'      => 'group_newsletter_media_links',
			'title'    => __( 'Media & Links', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'           => 'field_newsletter_pdf',
					'label'         => __( 'PDF Download', 'aikairali-portal' ),
					'name'          => 'pdf_download',
					'type'          => 'file',
					'return_format' => 'url',
					'mime_types'    => 'pdf',
					'wrapper'       => [ 'width' => '50' ],
				],
				[
					'key'           => 'field_newsletter_cta',
					'label'         => __( 'CTA Button', 'aikairali-portal' ),
					'name'          => 'cta_button',
					'type'          => 'link',
					'return_format' => 'array',
					'wrapper'       => [ 'width' => '50' ],
				],
				[
					'key'           => 'field_newsletter_related',
					'label'         => __( 'Related Articles', 'aikairali-portal' ),
					'name'          => 'related_articles',
					'type'          => 'relationship',
					'post_type'     => [ 'post', 'ai-news', 'ai-tools', 'ai-books' ], // Common types you might want to link
					'filters'       => [ 'search', 'post_type', 'taxonomy' ],
					'return_format' => 'id',
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'newsletters',
					],
				],
			],
		] );

		// Field Group: SEO Overrides.
		ACFLoader::register_field_group( [
			'key'      => 'group_newsletter_seo',
			'title'    => __( 'SEO Meta Overrides', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_newsletter_seo_title',
					'label' => __( 'SEO Title', 'aikairali-portal' ),
					'name'  => 'seo_title',
					'type'  => 'text',
				],
				[
					'key'   => 'field_newsletter_seo_desc',
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
						'value'    => 'newsletters',
					],
				],
			],
		] );
	}

	/**
	 * Add custom columns to the WP Admin Newsletters list table.
	 *
	 * @param array $columns Current columns.
	 * @return array Modified columns.
	 */
	public function add_admin_columns( array $columns ): array {
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			if ( 'date' === $key ) {
				$new_columns['newsletter_issue'] = __( 'Issue', 'aikairali-portal' );
				$new_columns['newsletter_pub']   = __( 'Publish Date', 'aikairali-portal' );
				$new_columns['newsletter_subj']  = __( 'Subject', 'aikairali-portal' );
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
			case 'newsletter_issue':
				$issue = get_field( 'issue_number', $post_id );
				echo esc_html( $issue ? '#' . $issue : '—' );
				break;

			case 'newsletter_pub':
				$pub_date = get_field( 'publish_date', $post_id );
				if ( $pub_date ) {
					echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $pub_date ) ) );
				} else {
					echo '—';
				}
				break;

			case 'newsletter_subj':
				$subject = get_field( 'email_subject', $post_id );
				echo esc_html( $subject ?: '—' );
				break;
		}
	}

	/**
	 * Inject Google-compliant JSON-LD NewsArticle schema.
	 */
	public function inject_json_ld_schema(): void {
		if ( ! is_singular( 'newsletters' ) ) {
			return;
		}

		$post_id  = get_the_ID();
		$settings = get_option( 'aikairali_portal_settings', [] );
		$enable   = $settings['seo']['enable_json_ld'] ?? '1';

		if ( '1' !== $enable ) {
			return;
		}

		$description = get_field( 'seo_description', $post_id ) ?: wp_trim_words( get_post_field( 'post_excerpt', $post_id ), 30 );
		$thumbnail   = get_the_post_thumbnail_url( $post_id, 'large' );
		
		$pub_date = get_field( 'publish_date', $post_id ) ?: get_the_date( 'c', $post_id );

		$schema = [
			'@context'      => 'https://schema.org',
			'@type'         => 'NewsArticle',
			'headline'      => get_the_title( $post_id ),
			'description'   => esc_html( $description ),
			'datePublished' => esc_html( $pub_date ),
			'dateModified'  => get_the_modified_date( 'c', $post_id ),
			'author'        => [
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
				'url'   => home_url(),
			],
			'publisher'     => [
				'@type' => 'Organization',
				'name'  => get_bloginfo( 'name' ),
			],
		];

		if ( $thumbnail ) {
			$schema['image'] = [
				esc_url( $thumbnail ),
			];
		}

		echo "\n<!-- AIKairali Newsletter Structured Data -->\n";
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
			'/newsletters',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_newsletters_api' ],
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
	public function get_newsletters_api( \WP_REST_Request $request ): \WP_REST_Response {
		$page     = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;
		$per_page = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 10;
		$search   = $request->get_param( 's' ) ? sanitize_text_field( $request->get_param( 's' ) ) : '';
		
		$args = [
			'post_type'      => 'newsletters',
			'post_status'    => 'publish',
			'paged'          => $page,
			'posts_per_page' => $per_page,
		];

		if ( $search ) {
			$args['s'] = $search;
		}

		$category = $request->get_param( 'category' ) ? sanitize_text_field( $request->get_param( 'category' ) ) : '';
		if ( $category ) {
			$args['tax_query'][] = [
				'taxonomy' => 'newsletter-category',
				'field'    => 'slug',
				'terms'    => $category,
			];
		}

		$query = new \WP_Query( $args );
		$newsletters = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				
				$categories = wp_get_post_terms( $post_id, 'newsletter-category', [ 'fields' => 'names' ] );
				
				$cta = get_field( 'cta_button', $post_id );
				
				$newsletters[] = [
					'id'            => $post_id,
					'title'         => get_the_title(),
					'slug'          => get_post_field( 'post_name', $post_id ),
					'date'          => get_the_date( 'c' ),
					'categories'    => $categories,
					'issue_number'  => get_field( 'issue_number', $post_id ),
					'publish_date'  => get_field( 'publish_date', $post_id ),
					'email_subject' => get_field( 'email_subject', $post_id ),
					'pdf_url'       => get_field( 'pdf_download', $post_id ),
					'cta_button'    => $cta ? [ 'title' => $cta['title'], 'url' => $cta['url'], 'target' => $cta['target'] ] : null,
					'thumbnail_url' => get_the_post_thumbnail_url( $post_id, 'large' ),
					'summary'       => get_the_excerpt(),
					'url'           => get_permalink(),
				];
			}
			wp_reset_postdata();
		}

		return new \WP_REST_Response( [
			'total' => $query->found_posts,
			'pages' => $query->max_num_pages,
			'data'  => $newsletters,
		], 200 );
	}

	/**
	 * Flush module-specific transient caches on save or delete.
	 */
	public function clear_module_cache(): void {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_newsletters_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_newsletters_%'" );
		
		// Flush general search cache.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_search_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_search_%'" );
	}
}
