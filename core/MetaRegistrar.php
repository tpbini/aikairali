<?php
namespace AIKairali\Portal\Core;

/**
 * Class MetaRegistrar
 *
 * Registers all custom post meta keys for the aikairali-portal CPTs so they
 * are exposed via the WordPress REST API (show_in_rest: true).  Without this
 * registration WordPress silently drops any `meta` keys submitted to the
 * REST endpoint.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.1.0
 */
class MetaRegistrar {

	/**
	 * Constructor – registers the rest_api_init hook.
	 *
	 * @param Loader $loader The hook loader.
	 */
	public function __construct( Loader $loader ) {
		$loader->add_action( 'rest_api_init', $this, 'register_all_meta' );
		$loader->add_action( 'save_post', $this, 'ensure_acf_reference_keys', 20, 2 );
	}

	/**
	 * Automatically attach ACF hidden reference keys (_field_name => field_key)
	 * whenever any post is saved or updated.
	 *
	 * Without this, editing posts in WP Admin displays empty input fields because ACF
	 * requires _field_name to link stored values to ACF field configurations.
	 *
	 * @param int      $post_id
	 * @param \WP_Post $post
	 */
	public function ensure_acf_reference_keys( $post_id = 0, $post = null ): void {
		if ( ! $post_id ) {
			return;
		}

		// Ignore autosaves and revisions
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) ) {
			return;
		}

		$post_obj = get_post( $post_id );
		if ( ! $post_obj || ! is_object( $post_obj ) || empty( $post_obj->post_type ) ) {
			return;
		}

		$post_type = $post_obj->post_type;
		$all_meta  = get_post_meta( $post_id );

		if ( ! is_array( $all_meta ) ) {
			return;
		}

		foreach ( $all_meta as $key => $values ) {
			// Skip internal keys starting with underscore
			if ( 0 === strpos( $key, '_' ) ) {
				continue;
			}

			$val = reset( $values );
			if ( $val === '' || $val === null ) {
				continue;
			}

			$ref_key_name = '_' . $key;
			$existing_ref = get_post_meta( $post_id, $ref_key_name, true );

			if ( ! $existing_ref ) {
				$field_key = RestAPI::get_field_key( $key, $post_type );
				if ( $field_key ) {
					update_post_meta( $post_id, $ref_key_name, $field_key );
					if ( function_exists( 'update_field' ) ) {
						update_field( $field_key, $val, $post_id );
					}
				}
			}
		}
	}

	/**
	 * Register post meta for all CPTs managed by the plugin.
	 */
	public function register_all_meta(): void {
		$this->register_shared_seo_meta();
		$this->register_ai_tools_meta();
		$this->register_ai_events_meta();
		$this->register_ai_books_meta();
		$this->register_ai_courses_meta();
		$this->register_ai_models_meta();
		$this->register_ai_prompts_meta();
		$this->register_ai_glossary_meta();
		$this->register_ai_videos_meta();
		$this->register_jobs_meta();
		$this->register_newsletters_meta();
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Register a single post meta key for one or more post types.
	 *
	 * @param string|array $post_types  CPT slug(s).
	 * @param string       $meta_key    The meta key.
	 * @param string       $type        The schema type: 'string', 'boolean', 'integer', 'number'.
	 * @param bool         $single      Whether the meta is a single value.
	 */
	private function meta( $post_types, string $meta_key, string $type = 'string', bool $single = true ): void {
		foreach ( (array) $post_types as $post_type ) {
			register_post_meta(
				$post_type,
				$meta_key,
				[
					'type'         => $type,
					'single'       => $single,
					'show_in_rest' => true,
				]
			);
		}
	}

	// -------------------------------------------------------------------------
	// Shared SEO meta (Yoast + custom ACF SEO override fields)
	// -------------------------------------------------------------------------

	private function register_shared_seo_meta(): void {
		$all_cpts = [
			'ai-tools', 'ai-events', 'ai-books', 'ai-courses',
			'ai-models', 'ai-prompts', 'ai-glossary', 'ai-videos',
			'jobs', 'newsletters',
		];

		foreach ( $all_cpts as $cpt ) {
			// Custom ACF SEO overrides
			$this->meta( $cpt, 'seo_title' );
			$this->meta( $cpt, 'seo_description' );
			$this->meta( $cpt, 'image_prompt' );

			// Yoast SEO meta keys
			$this->meta( $cpt, '_yoast_wpseo_title' );
			$this->meta( $cpt, '_yoast_wpseo_metadesc' );
			$this->meta( $cpt, '_yoast_wpseo_focuskw' );
			$this->meta( $cpt, '_yoast_wpseo_canonical' );
		}
	}

	// -------------------------------------------------------------------------
	// Per-module meta registrations
	// -------------------------------------------------------------------------

	private function register_ai_tools_meta(): void {
		$cpt = 'ai-tools';
		$this->meta( $cpt, 'official_website' );
		$this->meta( $cpt, 'pricing_details' );
		$this->meta( $cpt, 'free_plan',        'boolean' );
		$this->meta( $cpt, 'free_trial',       'boolean' );
		$this->meta( $cpt, 'api_available',    'boolean' );
		$this->meta( $cpt, 'mobile_app',       'boolean' );
		$this->meta( $cpt, 'chrome_extension', 'boolean' );
		$this->meta( $cpt, 'developer' );
		$this->meta( $cpt, 'founded_year',     'integer' );
		$this->meta( $cpt, 'rating',           'number' );
		// Repeater fields stored as serialised strings in wp_postmeta
		$this->meta( $cpt, 'features' );
		$this->meta( $cpt, 'pros' );
		$this->meta( $cpt, 'cons' );
	}

	private function register_ai_events_meta(): void {
		$cpt = 'ai-events';
		$this->meta( $cpt, 'event_date' );
		$this->meta( $cpt, 'event_end_date' );
		$this->meta( $cpt, 'event_time' );
		$this->meta( $cpt, 'timezone' );
		$this->meta( $cpt, 'event_location' );
		$this->meta( $cpt, 'venue_name' );
		$this->meta( $cpt, 'event_mode' );
		$this->meta( $cpt, 'registration_url' );
		$this->meta( $cpt, 'registration_fee' );
		$this->meta( $cpt, 'is_free',           'boolean' );
		$this->meta( $cpt, 'organizer_name' );
		$this->meta( $cpt, 'organizer_website' );
		$this->meta( $cpt, 'speakers' );
		$this->meta( $cpt, 'sponsors' );
	}

	private function register_ai_books_meta(): void {
		$cpt = 'ai-books';
		$this->meta( $cpt, 'author_name' );
		$this->meta( $cpt, 'isbn' );
		$this->meta( $cpt, 'publisher' );
		$this->meta( $cpt, 'publication_date' );
		$this->meta( $cpt, 'page_count',        'integer' );
		$this->meta( $cpt, 'language' );
		$this->meta( $cpt, 'book_rating',       'number' );
		$this->meta( $cpt, 'amazon_link' );
		$this->meta( $cpt, 'buy_link' );
		$this->meta( $cpt, 'affiliate_link' );
		$this->meta( $cpt, 'reading_level' );
		$this->meta( $cpt, 'reviews' );
		$this->meta( $cpt, 'key_takeaways' );
		$this->meta( $cpt, 'table_of_contents' );
	}

	private function register_ai_courses_meta(): void {
		$cpt = 'ai-courses';
		$this->meta( $cpt, 'course_provider' );
		$this->meta( $cpt, 'instructor_name' );
		$this->meta( $cpt, 'course_url' );
		$this->meta( $cpt, 'course_duration' );
		$this->meta( $cpt, 'difficulty_level' );
		$this->meta( $cpt, 'course_price' );
		$this->meta( $cpt, 'is_free',           'boolean' );
		$this->meta( $cpt, 'certificate',       'boolean' );
		$this->meta( $cpt, 'language' );
		$this->meta( $cpt, 'syllabus' );
		$this->meta( $cpt, 'prerequisites' );
		$this->meta( $cpt, 'skills_covered' );
	}

	private function register_ai_models_meta(): void {
		$cpt = 'ai-models';
		$this->meta( $cpt, 'provider' );
		$this->meta( $cpt, 'version' );
		$this->meta( $cpt, 'release_date' );
		$this->meta( $cpt, 'open_source',       'boolean' );
		$this->meta( $cpt, 'context_window' );
		$this->meta( $cpt, 'parameters' );
		$this->meta( $cpt, 'pricing' );
		$this->meta( $cpt, 'documentation_url' );
		$this->meta( $cpt, 'benchmarks' );
		$this->meta( $cpt, 'strengths' );
		$this->meta( $cpt, 'limitations' );
	}

	private function register_ai_prompts_meta(): void {
		$cpt = 'ai-prompts';
		$this->meta( $cpt, 'prompt_text' );
		$this->meta( $cpt, 'prompt_model' );
		$this->meta( $cpt, 'use_case' );
		$this->meta( $cpt, 'output_example' );
		$this->meta( $cpt, 'difficulty_level' );
		$this->meta( $cpt, 'prompt_variables' );
	}

	private function register_ai_glossary_meta(): void {
		$cpt = 'ai-glossary';
		$this->meta( $cpt, 'term_definition' );
		$this->meta( $cpt, 'term_origin' );
		$this->meta( $cpt, 'related_terms' );
		$this->meta( $cpt, 'examples' );
		$this->meta( $cpt, 'further_reading' );
	}

	private function register_ai_videos_meta(): void {
		$cpt = 'ai-videos';
		$this->meta( $cpt, 'video_url' );
		$this->meta( $cpt, 'video_duration' );
		$this->meta( $cpt, 'channel_name' );
		$this->meta( $cpt, 'channel_url' );
		$this->meta( $cpt, 'video_language' );
		$this->meta( $cpt, 'skill_level' );
		$this->meta( $cpt, 'video_topics' );
	}

	private function register_jobs_meta(): void {
		$cpt = 'jobs';
		$this->meta( $cpt, 'company_name' );
		$this->meta( $cpt, 'job_location' );
		$this->meta( $cpt, 'job_type' );
		$this->meta( $cpt, 'salary_range' );
		$this->meta( $cpt, 'application_url' );
		$this->meta( $cpt, 'application_deadline' );
		$this->meta( $cpt, 'remote_ok',         'boolean' );
		$this->meta( $cpt, 'experience_level' );
		$this->meta( $cpt, 'skills_required' );
	}

	private function register_newsletters_meta(): void {
		$cpt = 'newsletters';
		$this->meta( $cpt, 'newsletter_date' );
		$this->meta( $cpt, 'edition_number',    'integer' );
		$this->meta( $cpt, 'subscribe_url' );
		$this->meta( $cpt, 'highlights' );
	}
}
