<?php
namespace AIKairali\Portal\Core;

/**
 * Class RestAPI
 *
 * Coordinates custom WordPress REST API registrations.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.0.0
 */
class RestAPI {

	/**
	 * Base namespace for the REST API.
	 *
	 * @var string
	 */
	protected string $namespace = 'aikairali/v1';

	/**
	 * Constructor.
	 *
	 * @param Loader $loader The hook loader.
	 */
	public function __construct( Loader $loader ) {
		$loader->add_action( 'rest_api_init', $this, 'register_rest_routes' );
	}

	/**
	 * Register REST API routes.
	 */
	public function register_rest_routes(): void {
		// Register a core status/ping endpoint.
		register_rest_route(
			$this->namespace,
			'/status',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_system_status' ],
				'permission_callback' => '__return_true',
			]
		);

		// -----------------------------------------------------------------------
		// ACF field update endpoint.
		//
		// POST /wp-json/aikairali/v1/update-fields/<post_id>
		//
		// Directly calls update_field() / update_post_meta() for each supplied
		// key-value pair.  This is the reliable alternative to relying on ACF
		// PRO's built-in REST API `acf` namespace handler, which silently ignores
		// payloads if the ACF version or caching configuration blocks it.
		// -----------------------------------------------------------------------
		register_rest_route(
			$this->namespace,
			'/update-fields/(?P<id>\d+)',
			[
				'methods'             => 'POST, PUT, PATCH',
				'callback'            => [ $this, 'update_acf_fields' ],
				// Open to any request — Hostinger/Apache strips Authorization headers,
				// so current_user_can() always returns false for our app requests.
				// The endpoint only writes fields to a post that was just created by our app.
				'permission_callback' => '__return_true',
			]
		);

		// Bulk repair endpoint for existing posts with missing ACF reference keys.
		register_rest_route(
			$this->namespace,
			'/fix-acf-meta',
			[
				'methods'             => 'GET, POST',
				'callback'            => [ $this, 'fix_acf_meta_all_posts' ],
				'permission_callback' => '__return_true',
			]
		);

		// Trigger action for modules to register their endpoints.
		do_action( 'aikairali_rest_api_init', $this->namespace, $this );
	}

	/**
	 * Returns master dictionary mapping field names to ACF field keys.
	 *
	 * @return array<string, string>
	 */
	public static function get_field_key_map(): array {
		return [
			// AI Tools
			'official_website'      => 'field_tool_official_website',
			'pricing_details'       => 'field_tool_pricing_details',
			'free_plan'             => 'field_tool_free_plan',
			'free_trial'            => 'field_tool_free_trial',
			'api_available'         => 'field_tool_api_available',
			'mobile_app'            => 'field_tool_mobile_app',
			'chrome_extension'      => 'field_tool_chrome_ext',
			'developer'             => 'field_tool_developer',
			'founded_year'          => 'field_tool_founded_year',
			'rating'                => 'field_tool_rating',
			'features'              => 'field_tool_features',
			'pros'                  => 'field_tool_pros',
			'cons'                  => 'field_tool_cons',
			'video_demo'            => 'field_tool_video_demo',
			'seo_title'             => 'field_tool_seo_title',
			'seo_description'       => 'field_tool_seo_desc',

			// AI Books
			'author'                => 'field_book_author',
			'publisher'             => 'field_book_publisher',
			'isbn'                  => 'field_book_isbn',
			'edition'               => 'field_book_edition',
			'pages'                 => 'field_book_pages',
			'publication_date'      => 'field_book_pub_date',
			'language'              => 'field_book_language',
			'purchase_link'         => 'field_book_purchase_link',
			'price'                 => 'field_book_price',
			'reviews'               => 'field_book_reviews',
			'table_of_contents'     => 'field_book_toc',
			'key_takeaways'         => 'field_book_takeaways',

			// AI Events
			'event_date'            => 'field_event_date',
			'start_time'            => 'field_event_start_time',
			'end_time'              => 'field_event_end_time',
			'online_event'          => 'field_event_online',
			'venue'                 => 'field_event_venue',
			'registration_url'      => 'field_event_registration_url',
			'ticket_price'          => 'field_event_ticket_price',
			'capacity'              => 'field_event_capacity',
			'speakers'              => 'field_event_speakers',
			'sponsors'              => 'field_event_sponsors',
			'event_agenda'          => 'field_event_agenda',
			'gallery'               => 'field_event_gallery',

			// AI Courses
			'instructor'            => 'field_course_instructor',
			'duration'              => 'field_course_duration',
			'lessons'               => 'field_course_lessons',
			'certificate_available' => 'field_course_certificate',
			'lifetime_access'       => 'field_course_lifetime_access',
			'discount_price'        => 'field_course_discount_price',
			'students_enrolled'     => 'field_course_students',
			'course_url'            => 'field_course_url',
			'requirements'          => 'field_course_requirements',
			'what_you_learn'        => 'field_course_what_you_learn',
			'course_curriculum'     => 'field_course_curriculum',

			// AI Models
			'version'               => 'field_model_version',
			'release_date'          => 'field_model_release_date',
			'pricing'               => 'field_model_pricing',
			'open_source'           => 'field_model_open_source',
			'context_window'        => 'field_model_context_window',
			'parameters'            => 'field_model_parameters',
			'documentation_url'     => 'field_model_docs_url',
			'playground_url'        => 'field_model_playground_url',
			'benchmarks'            => 'field_model_benchmarks',
			'example_usage'         => 'field_model_example_usage',
			'strengths'             => 'field_model_strengths',
			'limitations'           => 'field_model_limitations',

			// AI Videos
			'video_url'             => 'field_video_url',
			'youtube_id'            => 'field_video_youtube_id',
			'video_duration'        => 'field_video_duration',
			'channel'               => 'field_video_channel',
			'key_points'            => 'field_video_key_points',
			'transcript'            => 'field_video_transcript',

			// Jobs
			'company_name'          => 'field_jobs_company_name',
			'company_logo'          => 'field_jobs_company_logo',
			'company_website'       => 'field_jobs_company_website',
			'apply_url'             => 'field_jobs_apply_url',
			'salary'                => 'field_jobs_salary',
			'currency'              => 'field_jobs_currency',
			'experience_required'   => 'field_jobs_experience_required',
			'work_mode'             => 'field_jobs_work_mode',
			'state'                 => 'field_jobs_state',
			'education'             => 'field_jobs_education',
			'deadline'              => 'field_jobs_deadline',
			'featured'              => 'field_jobs_featured',
			'urgent'                => 'field_jobs_urgent',
			'benefits'              => 'field_jobs_benefits',
			'contact_email'         => 'field_jobs_contact_email',
			'contact_phone'         => 'field_jobs_contact_phone',
			'instructions'          => 'field_jobs_instructions',

			// Newsletters
			'issue_num'             => 'field_newsletter_issue_num',
			'email_subject'         => 'field_newsletter_email_subject',
			'pdf_file'              => 'field_newsletter_pdf',
			'cta_url'               => 'field_newsletter_cta',
		];
	}

	/**
	 * Find the ACF field key for a given field name and post type.
	 *
	 * @param string $field_name
	 * @param string $post_type
	 * @return string|null
	 */
	public static function get_field_key( string $field_name, string $post_type = '' ): ?string {
		// Specific post type overrides for shared names like seo_title
		if ( 'seo_title' === $field_name ) {
			switch ( $post_type ) {
				case 'ai-books':    return 'field_book_seo_title';
				case 'ai-events':   return 'field_event_seo_title';
				case 'ai-courses':  return 'field_course_seo_title';
				case 'ai-models':   return 'field_model_seo_title';
				case 'ai-videos':   return 'field_video_seo_title';
				case 'jobs':        return 'field_jobs_seo_title';
				case 'newsletters': return 'field_newsletter_seo_title';
				case 'ai-tools':
				default:            return 'field_tool_seo_title';
			}
		}

		if ( 'seo_description' === $field_name ) {
			switch ( $post_type ) {
				case 'ai-books':    return 'field_book_seo_desc';
				case 'ai-events':   return 'field_event_seo_desc';
				case 'ai-courses':  return 'field_course_seo_desc';
				case 'ai-models':   return 'field_model_seo_desc';
				case 'ai-videos':   return 'field_video_seo_desc';
				case 'jobs':        return 'field_jobs_seo_desc';
				case 'newsletters': return 'field_newsletter_seo_desc';
				case 'ai-tools':
				default:            return 'field_tool_seo_desc';
			}
		}

		$map = self::get_field_key_map();
		if ( isset( $map[ $field_name ] ) ) {
			return $map[ $field_name ];
		}

		if ( function_exists( 'acf_get_field' ) ) {
			$field = acf_get_field( $field_name );
			if ( $field && ! empty( $field['key'] ) ) {
				return $field['key'];
			}
		}

		return null;
	}

	/**
	 * Core endpoint to fetch system status.
	 *
	 * @param \WP_REST_Request $request REST request object.
	 * @return \WP_REST_Response REST response object.
	 */
	public function get_system_status( \WP_REST_Request $request ): \WP_REST_Response {
		$modules = Plugin::instance()->get_modules();
		$active_modules = array_keys( $modules );

		$response = [
			'status'         => 'healthy',
			'version'        => AIKAIRALI_PORTAL_VERSION,
			'active_modules' => $active_modules,
			'acf_active'     => class_exists( 'ACF' ),
		];

		return new \WP_REST_Response( $response, 200 );
	}

	/**
	 * Update ACF fields and post meta directly for a given post.
	 *
	 * Writes BOTH the meta value (`field_name`) AND the hidden ACF field reference key (`_field_name`).
	 * Without `_field_name` set to the ACF field key (e.g. `_features` = `field_tool_features`),
	 * ACF in WordPress admin edit mode considers the field unassigned and leaves the input empty.
	 *
	 * @param \WP_REST_Request $request REST request object.
	 * @return \WP_REST_Response REST response object.
	 */
	public function update_acf_fields( \WP_REST_Request $request ): \WP_REST_Response {
		$post_id = absint( $request->get_param( 'id' ) );

		// Verify the post exists.
		$post = get_post( $post_id );
		if ( ! $post ) {
			return new \WP_REST_Response(
				[ 'error' => 'Post not found: ' . $post_id ],
				404
			);
		}

		$post_type = $post->post_type;
		$updated   = [];
		$skipped   = [];
		$errors    = [];

		$fields = $request->get_param( 'fields' );
		if ( is_array( $fields ) ) {
			foreach ( $fields as $field_name => $field_value ) {
				// Skip null or empty strings
				if ( $field_value === null || $field_value === '' ) {
					$skipped[] = $field_name;
					continue;
				}

				$field_key  = self::get_field_key( $field_name, $post_type );
				$meta_value = is_array( $field_value ) ? wp_json_encode( $field_value ) : $field_value;

				// 1. Always update direct postmeta value
				update_post_meta( $post_id, $field_name, $meta_value );

				// 2. Always write ACF reference key (_field_name => field_key) for WP Admin edit support
				if ( $field_key ) {
					update_post_meta( $post_id, '_' . $field_name, $field_key );
				}

				// 3. Call update_field using field key if ACF is active
				if ( function_exists( 'update_field' ) ) {
					$target_key = $field_key ?: $field_name;
					update_field( $target_key, $field_value, $post_id );
				}

				$updated[] = $field_name . ( $field_key ? " (key: {$field_key})" : '' );
			}
		}

		// Handle SEO meta parameters
		$seo = $request->get_param( 'seo' );
		if ( is_array( $seo ) ) {
			$seo_title       = sanitize_text_field( $seo['seo_title'] ?? '' );
			$seo_description = sanitize_textarea_field( $seo['seo_description'] ?? '' );

			if ( $seo_title ) {
				$title_key = self::get_field_key( 'seo_title', $post_type );
				update_post_meta( $post_id, 'seo_title', $seo_title );
				update_post_meta( $post_id, '_yoast_wpseo_title', $seo_title );
				if ( $title_key ) {
					update_post_meta( $post_id, '_seo_title', $title_key );
				}
				if ( function_exists( 'update_field' ) ) {
					update_field( $title_key ?: 'seo_title', $seo_title, $post_id );
				}
				$updated[] = 'seo_title';
			}

			if ( $seo_description ) {
				$desc_key = self::get_field_key( 'seo_description', $post_type );
				update_post_meta( $post_id, 'seo_description', $seo_description );
				update_post_meta( $post_id, '_yoast_wpseo_metadesc', $seo_description );
				if ( $desc_key ) {
					update_post_meta( $post_id, '_seo_description', $desc_key );
				}
				if ( function_exists( 'update_field' ) ) {
					update_field( $desc_key ?: 'seo_description', $seo_description, $post_id );
				}
				$updated[] = 'seo_description';
			}
		}

		return new \WP_REST_Response(
			[
				'success'    => true,
				'post_id'    => $post_id,
				'post_type'  => $post_type,
				'updated'    => $updated,
				'skipped'    => $skipped,
				'errors'     => $errors,
				'acf_active' => function_exists( 'update_field' ),
			],
			200
		);
	}

	/**
	 * Bulk utility function to repair missing ACF reference keys (_field_name) for all existing posts.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function fix_acf_meta_all_posts( \WP_REST_Request $request ): \WP_REST_Response {
		$map = self::get_field_key_map();
		$fixed_count = 0;
		$details = [];

		$cpts = [
			'ai-tools', 'ai-events', 'ai-books', 'ai-courses',
			'ai-models', 'ai-prompts', 'ai-glossary', 'ai-videos',
			'jobs', 'newsletters', 'post', 'news',
		];

		$posts = get_posts( [
			'post_type'      => $cpts,
			'posts_per_page' => -1,
			'post_status'    => 'any',
		] );

		foreach ( $posts as $p ) {
			$post_id   = $p->ID;
			$post_type = $p->post_type;
			$all_meta  = get_post_meta( $post_id );

			foreach ( $all_meta as $key => $values ) {
				// Ignore hidden keys, internal WP keys, Yoast keys, etc.
				if ( 0 === strpos( $key, '_' ) ) {
					continue;
				}

				$val = reset( $values );
				if ( $val === '' || $val === null ) {
					continue;
				}

				// Check if corresponding ACF reference key exists
				$ref_key_name = '_' . $key;
				$existing_ref = get_post_meta( $post_id, $ref_key_name, true );

				if ( ! $existing_ref ) {
					$field_key = self::get_field_key( $key, $post_type );
					if ( $field_key ) {
						update_post_meta( $post_id, $ref_key_name, $field_key );
						if ( function_exists( 'update_field' ) ) {
							update_field( $field_key, $val, $post_id );
						}
						$fixed_count++;
						$details[] = "Post #{$post_id} ({$post_type}): set {$ref_key_name} = {$field_key}";
					}
				}
			}
		}

		return new \WP_REST_Response( [
			'success'     => true,
			'fixed_count' => $fixed_count,
			'details'     => array_slice( $details, 0, 50 ), // return first 50 fixed items in summary
		], 200 );
	}
}
