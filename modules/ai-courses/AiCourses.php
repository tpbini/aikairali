<?php
namespace AIKairali\Portal\Modules\AiCourses;

use AIKairali\Portal\Core\ModuleInterface;
use AIKairali\Portal\Core\CPT;
use AIKairali\Portal\Core\Taxonomy;
use AIKairali\Portal\Core\ACFLoader;

/**
 * Class AiCourses
 *
 * Implements the AI Courses Module.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Modules/AiCourses
 * @since      1.0.0
 */
class AiCourses implements ModuleInterface {

	/**
	 * Initialize the module by registering hooks.
	 */
	public function init(): void {
		// Custom Admin Columns.
		add_filter( 'manage_ai-courses_posts_columns', [ $this, 'add_admin_columns' ] );
		add_action( 'manage_ai-courses_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );

		// SEO JSON-LD injection.
		add_action( 'wp_head', [ $this, 'inject_json_ld_schema' ] );

		// Hook into core REST API registration.
		add_action( 'aikairali_rest_api_init', [ $this, 'register_rest_endpoints' ], 10, 2 );

		// Flush cache when courses are saved or deleted.
		add_action( 'save_post_ai-courses', [ $this, 'clear_module_cache' ] );
		add_action( 'deleted_post', [ $this, 'clear_module_cache' ] );
	}

	/**
	 * Register Custom Post Types.
	 */
	public function register_cpts(): void {
		CPT::register(
			'ai-courses',
			__( 'AI Course', 'aikairali-portal' ),
			__( 'AI Courses', 'aikairali-portal' ),
			[
				'menu_icon'    => 'dashicons-welcome-learn-more',
				'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
				'has_archive'  => 'ai-courses',
				'rewrite'      => [ 'slug' => 'ai-courses', 'with_front' => false ],
			]
		);
	}

	/**
	 * Register Taxonomies.
	 */
	public function register_taxonomies(): void {
		// Course Category.
		Taxonomy::register(
			'course-category',
			'ai-courses',
			__( 'Course Category', 'aikairali-portal' ),
			__( 'Course Categories', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'course-category' ] ]
		);

		// Difficulty.
		Taxonomy::register(
			'course-difficulty',
			'ai-courses',
			__( 'Difficulty', 'aikairali-portal' ),
			__( 'Difficulties', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'course-difficulty' ] ]
		);

		// Provider.
		Taxonomy::register(
			'course-provider',
			'ai-courses',
			__( 'Provider', 'aikairali-portal' ),
			__( 'Providers', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'course-provider' ] ]
		);

		// Language.
		Taxonomy::register(
			'course-language',
			'ai-courses',
			__( 'Language', 'aikairali-portal' ),
			__( 'Languages', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'course-language' ] ]
		);
	}

	/**
	 * Register ACF Field Groups programmatically.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		// Field Group: Course Information.
		ACFLoader::register_field_group( [
			'key'      => 'group_course_info',
			'title'    => __( 'Course Information', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_course_instructor',
					'label' => __( 'Instructor', 'aikairali-portal' ),
					'name'  => 'instructor',
					'type'  => 'text',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_course_official_website',
					'label' => __( 'Official Website', 'aikairali-portal' ),
					'name'  => 'official_website',
					'type'  => 'url',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_course_duration',
					'label' => __( 'Duration', 'aikairali-portal' ),
					'name'  => 'duration',
					'type'  => 'text',
					'placeholder' => __( 'e.g. 10 hours, 4 weeks', 'aikairali-portal' ),
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_course_lessons',
					'label' => __( 'Lessons', 'aikairali-portal' ),
					'name'  => 'lessons',
					'type'  => 'number',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'     => 'field_course_certificate',
					'label'   => __( 'Certificate Available', 'aikairali-portal' ),
					'name'    => 'certificate_available',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'     => 'field_course_lifetime_access',
					'label'   => __( 'Lifetime Access', 'aikairali-portal' ),
					'name'    => 'lifetime_access',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_course_price',
					'label' => __( 'Price', 'aikairali-portal' ),
					'name'  => 'price',
					'type'  => 'text',
					'placeholder' => __( 'e.g. $49, Free', 'aikairali-portal' ),
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_course_discount_price',
					'label' => __( 'Discount Price', 'aikairali-portal' ),
					'name'  => 'discount_price',
					'type'  => 'text',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_course_rating',
					'label' => __( 'Rating', 'aikairali-portal' ),
					'name'  => 'rating',
					'type'  => 'number',
					'min'   => 0,
					'max'   => 5,
					'step'  => 0.1,
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_course_students',
					'label' => __( 'Students Enrolled', 'aikairali-portal' ),
					'name'  => 'students_enrolled',
					'type'  => 'number',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_course_url',
					'label' => __( 'Course URL', 'aikairali-portal' ),
					'name'  => 'course_url',
					'type'  => 'url',
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-courses',
					],
				],
			],
		] );

		// Field Group: Course Content Details.
		ACFLoader::register_field_group( [
			'key'      => 'group_course_content_details',
			'title'    => __( 'Course Content Details', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_course_requirements',
					'label' => __( 'Requirements', 'aikairali-portal' ),
					'name'  => 'requirements',
					'type'  => 'wysiwyg',
					'media_upload' => 0,
				],
				[
					'key'   => 'field_course_what_you_learn',
					'label' => __( 'What You\'ll Learn', 'aikairali-portal' ),
					'name'  => 'what_you_learn',
					'type'  => 'wysiwyg',
					'media_upload' => 0,
				],
				[
					'key'   => 'field_course_curriculum',
					'label' => __( 'Course Curriculum', 'aikairali-portal' ),
					'name'  => 'course_curriculum',
					'type'  => 'wysiwyg',
					'media_upload' => 0,
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-courses',
					],
				],
			],
		] );

		// Field Group: SEO Overrides.
		ACFLoader::register_field_group( [
			'key'      => 'group_course_seo',
			'title'    => __( 'SEO Meta Overrides', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_course_seo_title',
					'label' => __( 'SEO Title', 'aikairali-portal' ),
					'name'  => 'seo_title',
					'type'  => 'text',
				],
				[
					'key'   => 'field_course_seo_desc',
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
						'value'    => 'ai-courses',
					],
				],
			],
		] );
	}

	/**
	 * Add custom columns to the WP Admin AI Courses list table.
	 *
	 * @param array $columns Current columns.
	 * @return array Modified columns.
	 */
	public function add_admin_columns( array $columns ): array {
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			if ( 'date' === $key ) {
				$new_columns['course_provider'] = __( 'Provider', 'aikairali-portal' );
				$new_columns['course_diff']     = __( 'Difficulty', 'aikairali-portal' );
				$new_columns['course_price']    = __( 'Price', 'aikairali-portal' );
				$new_columns['course_cert']     = __( 'Certificate', 'aikairali-portal' );
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
			case 'course_provider':
				$terms = get_the_term_list( $post_id, 'course-provider', '', ', ', '' );
				echo $terms ? wp_kses_post( $terms ) : '—';
				break;

			case 'course_diff':
				$terms = get_the_term_list( $post_id, 'course-difficulty', '', ', ', '' );
				echo $terms ? wp_kses_post( $terms ) : '—';
				break;

			case 'course_price':
				$price    = get_field( 'price', $post_id );
				$discount = get_field( 'discount_price', $post_id );
				if ( $price ) {
					if ( $discount ) {
						echo '<del>' . esc_html( $price ) . '</del> <strong>' . esc_html( $discount ) . '</strong>';
					} else {
						echo esc_html( $price );
					}
				} else {
					echo '—';
				}
				break;

			case 'course_cert':
				$cert = get_field( 'certificate_available', $post_id );
				if ( $cert ) {
					echo '<span style="color:green; font-weight:bold;">' . esc_html__( 'Yes', 'aikairali-portal' ) . '</span>';
				} else {
					echo '—';
				}
				break;
		}
	}

	/**
	 * Inject Google-compliant JSON-LD Course schema.
	 */
	public function inject_json_ld_schema(): void {
		if ( ! is_singular( 'ai-courses' ) ) {
			return;
		}

		$post_id  = get_the_ID();
		$settings = get_option( 'aikairali_portal_settings', [] );
		$enable   = $settings['seo']['enable_json_ld'] ?? '1';

		if ( '1' !== $enable ) {
			return;
		}

		$providers = wp_get_post_terms( $post_id, 'course-provider', [ 'fields' => 'names' ] );
		$provider_name = ! empty( $providers ) ? reset( $providers ) : get_bloginfo( 'name' );
		
		$instructor = get_field( 'instructor', $post_id );
		$description = get_field( 'seo_description', $post_id ) ?: wp_trim_words( get_post_field( 'post_content', $post_id ), 20 );

		$schema = [
			'@context'    => 'https://schema.org',
			'@type'       => 'Course',
			'name'        => get_the_title( $post_id ),
			'description' => esc_html( $description ),
			'provider'    => [
				'@type' => 'Organization',
				'name'  => esc_html( $provider_name ),
			],
		];

		if ( $instructor ) {
			$schema['creator'] = [
				'@type' => 'Person',
				'name'  => esc_html( $instructor ),
			];
		}

		echo "\n<!-- AIKairali Course Structured Data -->\n";
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
			'/courses',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_courses_api' ],
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
	public function get_courses_api( \WP_REST_Request $request ): \WP_REST_Response {
		$page     = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;
		$per_page = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 10;
		$search   = $request->get_param( 's' ) ? sanitize_text_field( $request->get_param( 's' ) ) : '';
		
		$args = [
			'post_type'      => 'ai-courses',
			'post_status'    => 'publish',
			'paged'          => $page,
			'posts_per_page' => $per_page,
		];

		if ( $search ) {
			$args['s'] = $search;
		}

		$query = new \WP_Query( $args );
		$courses = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				
				$providers = wp_get_post_terms( $post_id, 'course-provider', [ 'fields' => 'names' ] );
				
				$courses[] = [
					'id'            => $post_id,
					'title'         => get_the_title(),
					'slug'          => get_post_field( 'post_name', $post_id ),
					'date'          => get_the_date( 'c' ),
					'provider'      => ! empty( $providers ) ? reset( $providers ) : '',
					'instructor'    => get_field( 'instructor', $post_id ),
					'duration'      => get_field( 'duration', $post_id ),
					'price'         => get_field( 'price', $post_id ),
					'discount'      => get_field( 'discount_price', $post_id ),
					'rating'        => get_field( 'rating', $post_id ),
					'students'      => get_field( 'students_enrolled', $post_id ),
					'thumbnail_url' => get_the_post_thumbnail_url( $post_id, 'large' ),
					'url'           => get_permalink(),
				];
			}
			wp_reset_postdata();
		}

		return new \WP_REST_Response( [
			'total' => $query->found_posts,
			'pages' => $query->max_num_pages,
			'data'  => $courses,
		], 200 );
	}

	/**
	 * Flush module-specific transient caches on save or delete.
	 */
	public function clear_module_cache(): void {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_courses_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_courses_%'" );
		
		// Flush general search cache.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_search_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_search_%'" );
	}
}
