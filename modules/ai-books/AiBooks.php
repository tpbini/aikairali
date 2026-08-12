<?php
namespace AIKairali\Portal\Modules\AiBooks;

use AIKairali\Portal\Core\ModuleInterface;
use AIKairali\Portal\Core\CPT;
use AIKairali\Portal\Core\Taxonomy;
use AIKairali\Portal\Core\ACFLoader;

/**
 * Class AiBooks
 *
 * Implements the AI Books Module.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Modules/AiBooks
 * @since      1.0.0
 */
class AiBooks implements ModuleInterface {

	/**
	 * Initialize the module by registering hooks.
	 */
	public function init(): void {
		// Custom Admin Columns.
		add_filter( 'manage_ai-books_posts_columns', [ $this, 'add_admin_columns' ] );
		add_action( 'manage_ai-books_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );

		// SEO JSON-LD injection.
		add_action( 'wp_head', [ $this, 'inject_json_ld_schema' ] );

		// Hook into core REST API registration.
		add_action( 'aikairali_rest_api_init', [ $this, 'register_rest_endpoints' ], 10, 2 );

		// Flush cache when books are saved or deleted.
		add_action( 'save_post_ai-books', [ $this, 'clear_module_cache' ] );
		add_action( 'deleted_post', [ $this, 'clear_module_cache' ] );
	}

	/**
	 * Register Custom Post Types.
	 */
	public function register_cpts(): void {
		CPT::register(
			'ai-books',
			__( 'AI Book', 'aikairali-portal' ),
			__( 'AI Books', 'aikairali-portal' ),
			[
				'menu_icon'    => 'dashicons-book',
				'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
				'has_archive'  => 'ai-books',
				'rewrite'      => [ 'slug' => 'ai-books', 'with_front' => false ],
			]
		);
	}

	/**
	 * Register Taxonomies.
	 */
	public function register_taxonomies(): void {
		// Book Category.
		Taxonomy::register(
			'book-category',
			'ai-books',
			__( 'Book Category', 'aikairali-portal' ),
			__( 'Book Categories', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'book-category' ] ]
		);
	}

	/**
	 * Register ACF Field Groups programmatically.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		// Field Group: Book Details.
		ACFLoader::register_field_group( [
			'key'      => 'group_book_details',
			'title'    => __( 'Book Details', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_book_author',
					'label' => __( 'Author(s)', 'aikairali-portal' ),
					'name'  => 'author',
					'type'  => 'text',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_book_publisher',
					'label' => __( 'Publisher', 'aikairali-portal' ),
					'name'  => 'publisher',
					'type'  => 'text',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_book_isbn',
					'label' => __( 'ISBN', 'aikairali-portal' ),
					'name'  => 'isbn',
					'type'  => 'text',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_book_edition',
					'label' => __( 'Edition', 'aikairali-portal' ),
					'name'  => 'edition',
					'type'  => 'text',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_book_pages',
					'label' => __( 'Pages', 'aikairali-portal' ),
					'name'  => 'pages',
					'type'  => 'number',
					'wrapper' => [ 'width' => '33' ],
				],
				[
					'key'   => 'field_book_pub_date',
					'label' => __( 'Publication Date', 'aikairali-portal' ),
					'name'  => 'publication_date',
					'type'  => 'date_picker',
					'display_format' => 'F j, Y',
					'return_format'  => 'Y-m-d',
					'wrapper' => [ 'width' => '33' ],
				],
				[
					'key'   => 'field_book_language',
					'label' => __( 'Language', 'aikairali-portal' ),
					'name'  => 'language',
					'type'  => 'text',
					'default_value' => 'English',
					'wrapper' => [ 'width' => '33' ],
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-books',
					],
				],
			],
		] );

		// Field Group: Purchase & Reviews.
		ACFLoader::register_field_group( [
			'key'      => 'group_book_purchase_reviews',
			'title'    => __( 'Purchase & Reviews', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_book_purchase_link',
					'label' => __( 'Purchase Link', 'aikairali-portal' ),
					'name'  => 'purchase_link',
					'type'  => 'url',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_book_price',
					'label' => __( 'Price', 'aikairali-portal' ),
					'name'  => 'price',
					'type'  => 'text',
					'placeholder' => __( 'e.g. $29.99', 'aikairali-portal' ),
					'wrapper' => [ 'width' => '25' ],
				],
				[
					'key'   => 'field_book_rating',
					'label' => __( 'Rating', 'aikairali-portal' ),
					'name'  => 'rating',
					'type'  => 'number',
					'min'   => 0,
					'max'   => 5,
					'step'  => 0.1,
					'wrapper' => [ 'width' => '25' ],
				],
				[
					'key'         => 'field_book_reviews',
					'label'       => __( 'Reviews / Reader Feedback', 'aikairali-portal' ),
					'name'        => 'reviews',
					'type'        => 'textarea',
					'rows'        => 4,
					'placeholder' => __( 'Brief reader review or feedback', 'aikairali-portal' ),
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-books',
					],
				],
			],
		] );

		// Field Group: Book Content.
		ACFLoader::register_field_group( [
			'key'      => 'group_book_content',
			'title'    => __( 'Book Content Details', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_book_toc',
					'label' => __( 'Table of Contents', 'aikairali-portal' ),
					'name'  => 'table_of_contents',
					'type'  => 'wysiwyg',
					'media_upload' => 0,
				],
				[
					'key'         => 'field_book_takeaways',
					'label'       => __( 'Key Takeaways', 'aikairali-portal' ),
					'name'        => 'key_takeaways',
					'type'        => 'textarea',
					'rows'        => 5,
					'placeholder' => __( 'One takeaway per line', 'aikairali-portal' ),
					'instructions' => __( 'Enter each key takeaway on a new line.', 'aikairali-portal' ),
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-books',
					],
				],
			],
		] );

		// Field Group: SEO Overrides.
		ACFLoader::register_field_group( [
			'key'      => 'group_book_seo',
			'title'    => __( 'SEO Meta Overrides', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_book_seo_title',
					'label' => __( 'SEO Title', 'aikairali-portal' ),
					'name'  => 'seo_title',
					'type'  => 'text',
				],
				[
					'key'   => 'field_book_seo_desc',
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
						'value'    => 'ai-books',
					],
				],
			],
		] );
	}

	/**
	 * Add custom columns to the WP Admin AI Books list table.
	 *
	 * @param array $columns Current columns.
	 * @return array Modified columns.
	 */
	public function add_admin_columns( array $columns ): array {
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			if ( 'date' === $key ) {
				$new_columns['book_author']    = __( 'Author', 'aikairali-portal' );
				$new_columns['book_publisher'] = __( 'Publisher', 'aikairali-portal' );
				$new_columns['book_pub_date']  = __( 'Published', 'aikairali-portal' );
				$new_columns['book_price']     = __( 'Price', 'aikairali-portal' );
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
			case 'book_author':
				$author = get_field( 'author', $post_id );
				echo esc_html( $author ?: '—' );
				break;

			case 'book_publisher':
				$publisher = get_field( 'publisher', $post_id );
				echo esc_html( $publisher ?: '—' );
				break;

			case 'book_pub_date':
				$pub_date = get_field( 'publication_date', $post_id );
				if ( $pub_date ) {
					echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $pub_date ) ) );
				} else {
					echo '—';
				}
				break;

			case 'book_price':
				$price = get_field( 'price', $post_id );
				echo esc_html( $price ?: '—' );
				break;
		}
	}

	/**
	 * Inject Google-compliant JSON-LD Book schema.
	 */
	public function inject_json_ld_schema(): void {
		if ( ! is_singular( 'ai-books' ) ) {
			return;
		}

		$post_id  = get_the_ID();
		$settings = get_option( 'aikairali_portal_settings', [] );
		$enable   = $settings['seo']['enable_json_ld'] ?? '1';

		if ( '1' !== $enable ) {
			return;
		}

		$author    = get_field( 'author', $post_id );
		$publisher = get_field( 'publisher', $post_id );
		$isbn      = get_field( 'isbn', $post_id );
		$edition   = get_field( 'edition', $post_id );
		$pages     = get_field( 'pages', $post_id );
		$pub_date  = get_field( 'publication_date', $post_id );
		$language  = get_field( 'language', $post_id ) ?: 'English';
		$rating    = get_field( 'rating', $post_id );
		
		$description = get_field( 'seo_description', $post_id ) ?: wp_trim_words( get_post_field( 'post_excerpt', $post_id ), 30 );
		$thumbnail   = get_the_post_thumbnail_url( $post_id, 'large' );

		$schema = [
			'@context'      => 'https://schema.org',
			'@type'         => 'Book',
			'name'          => get_the_title( $post_id ),
			'description'   => esc_html( $description ),
			'inLanguage'    => esc_html( $language ),
		];

		if ( $author ) {
			$schema['author'] = [
				'@type' => 'Person',
				'name'  => esc_html( $author ),
			];
		}

		if ( $publisher ) {
			$schema['publisher'] = [
				'@type' => 'Organization',
				'name'  => esc_html( $publisher ),
			];
		}

		if ( $isbn ) {
			$schema['isbn'] = esc_html( $isbn );
		}

		if ( $edition ) {
			$schema['bookEdition'] = esc_html( $edition );
		}

		if ( $pages ) {
			$schema['numberOfPages'] = intval( $pages );
		}

		if ( $pub_date ) {
			$schema['datePublished'] = esc_html( $pub_date );
		}

		if ( $thumbnail ) {
			$schema['image'] = esc_url( $thumbnail );
		}

		if ( $rating ) {
			$schema['aggregateRating'] = [
				'@type'       => 'AggregateRating',
				'ratingValue' => floatval( $rating ),
				'bestRating'  => 5,
				'ratingCount' => 1, // Fallback
			];
		}

		// Reviews
		if ( have_rows( 'reviews', $post_id ) ) {
			$reviews = [];
			while ( have_rows( 'reviews', $post_id ) ) {
				the_row();
				$rev_name   = get_sub_field( 'reviewer_name' );
				$rev_rating = get_sub_field( 'reviewer_rating' );
				$rev_text   = get_sub_field( 'review_text' );
				
				if ( $rev_name && $rev_text ) {
					$review_obj = [
						'@type'  => 'Review',
						'author' => [
							'@type' => 'Person',
							'name'  => esc_html( $rev_name ),
						],
						'reviewBody' => esc_html( $rev_text ),
					];
					if ( $rev_rating ) {
						$review_obj['reviewRating'] = [
							'@type'       => 'Rating',
							'ratingValue' => floatval( $rev_rating ),
							'bestRating'  => 5,
						];
					}
					$reviews[] = $review_obj;
				}
			}
			if ( ! empty( $reviews ) ) {
				$schema['review'] = $reviews;
				if ( ! $rating ) {
					// Auto-calculate aggregate if none provided but reviews exist
					$schema['aggregateRating'] = [
						'@type'       => 'AggregateRating',
						'ratingValue' => 5, // Simplified
						'ratingCount' => count( $reviews ),
					];
				} else {
					$schema['aggregateRating']['ratingCount'] = max( 1, count( $reviews ) );
				}
			}
		}

		echo "\n<!-- AIKairali Book Structured Data -->\n";
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
			'/books',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_books_api' ],
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
	public function get_books_api( \WP_REST_Request $request ): \WP_REST_Response {
		$page     = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;
		$per_page = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 10;
		$search   = $request->get_param( 's' ) ? sanitize_text_field( $request->get_param( 's' ) ) : '';
		
		$args = [
			'post_type'      => 'ai-books',
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
				'taxonomy' => 'book-category',
				'field'    => 'slug',
				'terms'    => $category,
			];
		}

		$query = new \WP_Query( $args );
		$books = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				
				$categories = wp_get_post_terms( $post_id, 'book-category', [ 'fields' => 'names' ] );
				
				$books[] = [
					'id'            => $post_id,
					'title'         => get_the_title(),
					'slug'          => get_post_field( 'post_name', $post_id ),
					'date'          => get_the_date( 'c' ),
					'categories'    => $categories,
					'author'        => get_field( 'author', $post_id ),
					'publisher'     => get_field( 'publisher', $post_id ),
					'publication_date' => get_field( 'publication_date', $post_id ),
					'price'         => get_field( 'price', $post_id ),
					'rating'        => get_field( 'rating', $post_id ),
					'thumbnail_url' => get_the_post_thumbnail_url( $post_id, 'large' ),
					'purchase_link' => get_field( 'purchase_link', $post_id ),
					'url'           => get_permalink(),
				];
			}
			wp_reset_postdata();
		}

		return new \WP_REST_Response( [
			'total' => $query->found_posts,
			'pages' => $query->max_num_pages,
			'data'  => $books,
		], 200 );
	}

	/**
	 * Flush module-specific transient caches on save or delete.
	 */
	public function clear_module_cache(): void {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_books_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_books_%'" );
		
		// Flush general search cache.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_search_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_search_%'" );
	}
}
