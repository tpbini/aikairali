<?php
namespace AIKairali\Portal\Modules\Jobs;

use AIKairali\Portal\Core\ModuleInterface;
use AIKairali\Portal\Core\CPT;
use AIKairali\Portal\Core\Taxonomy;
use AIKairali\Portal\Core\ACFLoader;
use AIKairali\Portal\Core\Helpers;
use AIKairali\Portal\Core\Cache;

/**
 * Class Jobs
 *
 * Implements the AI Jobs Module.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Modules/Jobs
 * @since      1.0.0
 */
class Jobs implements ModuleInterface {

	/**
	 * Initialize the module by registering hooks.
	 */
	public function init(): void {
		// Custom Admin Columns.
		add_filter( 'manage_jobs_posts_columns', [ $this, 'add_admin_columns' ] );
		add_action( 'manage_jobs_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );

		// SEO JSON-LD injection.
		add_action( 'wp_head', [ $this, 'inject_json_ld_schema' ] );

		// Hook into core REST API registration.
		add_action( 'aikairali_rest_api_init', [ $this, 'register_rest_endpoints' ], 10, 2 );
		
		// Pre get posts filtering for jobs archive page.
		add_action( 'pre_get_posts', [ $this, 'filter_jobs_archive_query' ] );

		// Flush cache when jobs are saved or deleted to keep related posts fresh.
		add_action( 'save_post_jobs', [ $this, 'clear_module_cache' ] );
		add_action( 'deleted_post', [ $this, 'clear_module_cache' ] );
	}

	/**
	 * Register Custom Post Types.
	 */
	public function register_cpts(): void {
		CPT::register(
			'jobs',
			__( 'Job', 'aikairali-portal' ),
			__( 'Jobs', 'aikairali-portal' ),
			[
				'menu_icon'    => 'dashicons-portfolio',
				'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
				'has_archive'  => 'jobs',
				'rewrite'      => [ 'slug' => 'jobs', 'with_front' => false ],
			]
		);
	}

	/**
	 * Register Taxonomies.
	 */
	public function register_taxonomies(): void {
		// Job Category.
		Taxonomy::register(
			'job-category',
			'jobs',
			__( 'Job Category', 'aikairali-portal' ),
			__( 'Job Categories', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'job-category' ] ]
		);

		// Employment Type.
		Taxonomy::register(
			'employment-type',
			'jobs',
			__( 'Employment Type', 'aikairali-portal' ),
			__( 'Employment Types', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'employment-type' ] ]
		);

		// Experience Level.
		Taxonomy::register(
			'experience-level',
			'jobs',
			__( 'Experience Level', 'aikairali-portal' ),
			__( 'Experience Levels', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'experience-level' ] ]
		);

		// Skills.
		Taxonomy::register(
			'job-skill',
			'jobs',
			__( 'Skill', 'aikairali-portal' ),
			__( 'Skills Required', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'job-skill' ] ]
		);

		// Country.
		Taxonomy::register(
			'job-country',
			'jobs',
			__( 'Country', 'aikairali-portal' ),
			__( 'Countries', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'job-country' ] ]
		);

		// City.
		Taxonomy::register(
			'job-city',
			'jobs',
			__( 'City', 'aikairali-portal' ),
			__( 'Cities', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'job-city' ] ]
		);
	}

	/**
	 * Register ACF Field Groups programmatically.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		// Field Group: Company Details.
		ACFLoader::register_field_group( [
			'key'      => 'group_jobs_company',
			'title'    => __( 'Company Details', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_jobs_company_name',
					'label' => __( 'Company Name', 'aikairali-portal' ),
					'name'  => 'company_name',
					'type'  => 'text',
					'required' => 1,
				],
				[
					'key'           => 'field_jobs_company_logo',
					'label'         => __( 'Company Logo', 'aikairali-portal' ),
					'name'          => 'company_logo',
					'type'          => 'image',
					'return_format' => 'url',
					'preview_size'  => 'thumbnail',
				],
				[
					'key'   => 'field_jobs_company_website',
					'label' => __( 'Company Website', 'aikairali-portal' ),
					'name'  => 'company_website',
					'type'  => 'url',
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'jobs',
					],
				],
			],
		] );

		// Field Group: Job Details.
		ACFLoader::register_field_group( [
			'key'      => 'group_jobs_details',
			'title'    => __( 'Job Details & Metadata', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_jobs_apply_url',
					'label' => __( 'Apply URL', 'aikairali-portal' ),
					'name'  => 'apply_url',
					'type'  => 'url',
					'required' => 1,
				],
				[
					'key'   => 'field_jobs_salary',
					'label' => __( 'Salary / Compensation', 'aikairali-portal' ),
					'name'  => 'salary',
					'type'  => 'text',
					'placeholder' => __( 'e.g. 80,000 - 100,000', 'aikairali-portal' ),
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'     => 'field_jobs_currency',
					'label'   => __( 'Currency', 'aikairali-portal' ),
					'name'    => 'currency',
					'type'    => 'select',
					'choices' => [
						'USD' => 'USD ($)',
						'EUR' => 'EUR (€)',
						'INR' => 'INR (₹)',
						'GBP' => 'GBP (£)',
					],
					'default_value' => 'USD',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_jobs_experience_required',
					'label' => __( 'Experience Required', 'aikairali-portal' ),
					'name'  => 'experience_required',
					'type'  => 'text',
					'placeholder' => __( 'e.g. 3+ years', 'aikairali-portal' ),
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'     => 'field_jobs_work_mode',
					'label'   => __( 'Work Mode', 'aikairali-portal' ),
					'name'    => 'work_mode',
					'type'    => 'select',
					'choices' => [
						'Remote' => __( 'Remote', 'aikairali-portal' ),
						'Hybrid' => __( 'Hybrid', 'aikairali-portal' ),
						'Onsite' => __( 'Onsite', 'aikairali-portal' ),
					],
					'default_value' => 'Remote',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_jobs_state',
					'label' => __( 'State / Province', 'aikairali-portal' ),
					'name'  => 'state',
					'type'  => 'text',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_jobs_education',
					'label' => __( 'Education Requirement', 'aikairali-portal' ),
					'name'  => 'education',
					'type'  => 'text',
					'placeholder' => __( 'e.g. Bachelor\'s in Computer Science', 'aikairali-portal' ),
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_jobs_deadline',
					'label' => __( 'Application Deadline', 'aikairali-portal' ),
					'name'  => 'deadline',
					'type'  => 'date_picker',
					'display_format' => 'Y-m-d',
					'return_format'  => 'Y-m-d',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'     => 'field_jobs_featured',
					'label'   => __( 'Featured Job', 'aikairali-portal' ),
					'name'    => 'featured_job',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '25' ],
				],
				[
					'key'     => 'field_jobs_urgent',
					'label'   => __( 'Urgent Hiring', 'aikairali-portal' ),
					'name'    => 'urgent_hiring',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '25' ],
				],
				[
					'key'   => 'field_jobs_benefits',
					'label' => __( 'Benefits & Perks', 'aikairali-portal' ),
					'name'  => 'benefits',
					'type'  => 'textarea',
					'rows'  => 3,
				],
				[
					'key'   => 'field_jobs_contact_email',
					'label' => __( 'Contact Email', 'aikairali-portal' ),
					'name'  => 'contact_email',
					'type'  => 'email',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_jobs_contact_phone',
					'label' => __( 'Contact Phone', 'aikairali-portal' ),
					'name'  => 'contact_phone',
					'type'  => 'text',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_jobs_instructions',
					'label' => __( 'Application Instructions', 'aikairali-portal' ),
					'name'  => 'application_instructions',
					'type'  => 'textarea',
					'rows'  => 3,
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'jobs',
					],
				],
			],
		] );

		// Field Group: SEO Overrides.
		ACFLoader::register_field_group( [
			'key'      => 'group_jobs_seo',
			'title'    => __( 'SEO Meta Overrides', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_jobs_seo_title',
					'label' => __( 'SEO Title', 'aikairali-portal' ),
					'name'  => 'seo_title',
					'type'  => 'text',
				],
				[
					'key'   => 'field_jobs_seo_desc',
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
						'value'    => 'jobs',
					],
				],
			],
		] );
	}

	/**
	 * Add custom columns to the WP Admin Jobs list table.
	 *
	 * @param array $columns Current columns.
	 * @return array Modified columns.
	 */
	public function add_admin_columns( array $columns ): array {
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			if ( 'date' === $key ) {
				$new_columns['company_details'] = __( 'Company', 'aikairali-portal' );
				$new_columns['work_mode']       = __( 'Work Mode', 'aikairali-portal' );
				$new_columns['salary_info']     = __( 'Salary', 'aikairali-portal' );
				$new_columns['badges']          = __( 'Badges', 'aikairali-portal' );
				$new_columns['job_deadline']    = __( 'Deadline', 'aikairali-portal' );
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
			case 'company_details':
				$logo = get_field( 'company_logo', $post_id );
				$name = get_field( 'company_name', $post_id );
				if ( $logo ) {
					echo '<img src="' . esc_url( $logo ) . '" style="max-height: 30px; max-width: 50px; vertical-align: middle; margin-right: 8px; border-radius: 4px;" />';
				}
				echo esc_html( $name ?: __( 'Unknown Company', 'aikairali-portal' ) );
				break;

			case 'work_mode':
				$mode = get_field( 'work_mode', $post_id );
				echo esc_html( $mode ?: '—' );
				break;

			case 'salary_info':
				$salary   = get_field( 'salary', $post_id );
				$currency = get_field( 'currency', $post_id );
				if ( $salary ) {
					echo esc_html( "{$currency} {$salary}" );
				} else {
					echo '—';
				}
				break;

			case 'badges':
				$featured = get_field( 'featured_job', $post_id );
				$urgent   = get_field( 'urgent_hiring', $post_id );
				
				if ( $featured ) {
					echo '<span class="badge" style="background:#ffb900; color:#fff; padding:2px 6px; border-radius:3px; font-weight:bold; font-size:10px; margin-right:4px;">' . esc_html__( 'Featured', 'aikairali-portal' ) . '</span>';
				}
				if ( $urgent ) {
					echo '<span class="badge" style="background:#dc3232; color:#fff; padding:2px 6px; border-radius:3px; font-weight:bold; font-size:10px;">' . esc_html__( 'Urgent', 'aikairali-portal' ) . '</span>';
				}
				if ( ! $featured && ! $urgent ) {
					echo '—';
				}
				break;

			case 'job_deadline':
				$deadline = get_field( 'deadline', $post_id );
				if ( $deadline ) {
					$deadline_time = strtotime( $deadline );
					$formatted     = date_i18n( get_option( 'date_format' ), $deadline_time );
					if ( $deadline_time < time() ) {
						echo '<span style="color:red; font-weight:bold;">' . esc_html( $formatted ) . ' (' . esc_html__( 'Expired', 'aikairali-portal' ) . ')</span>';
					} else {
						echo esc_html( $formatted );
					}
				} else {
					echo '—';
				}
				break;
		}
	}

	/**
	 * Inject Google-compliant JSON-LD JobPosting schema.
	 */
	public function inject_json_ld_schema(): void {
		if ( ! is_singular( 'jobs' ) ) {
			return;
		}

		$post_id  = get_the_ID();
		$settings = get_option( 'aikairali_portal_settings', [] );
		$enable   = $settings['seo']['enable_json_ld'] ?? '1';

		if ( '1' !== $enable ) {
			return;
		}

		// Fetch ACF fields.
		$comp_name = get_field( 'company_name', $post_id );
		$comp_logo = get_field( 'company_logo', $post_id );
		$comp_web  = get_field( 'company_website', $post_id );
		$work_mode = get_field( 'work_mode', $post_id );
		$salary    = get_field( 'salary', $post_id );
		$currency  = get_field( 'currency', $post_id );
		$deadline  = get_field( 'deadline', $post_id );
		$state     = get_field( 'state', $post_id );

		// Fetch Taxonomies.
		$emp_types    = wp_get_post_terms( $post_id, 'employment-type', [ 'fields' => 'names' ] );
		$countries    = wp_get_post_terms( $post_id, 'job-country', [ 'fields' => 'names' ] );
		$cities       = wp_get_post_terms( $post_id, 'job-city', [ 'fields' => 'names' ] );
		
		// Map employment types to Google-supported values.
		$mapped_types = [];
		foreach ( $emp_types as $type ) {
			$lower = strtolower( $type );
			if ( strpos( $lower, 'full' ) !== false ) {
				$mapped_types[] = 'FULL_TIME';
			} elseif ( strpos( $lower, 'part' ) !== false ) {
				$mapped_types[] = 'PART_TIME';
			} elseif ( strpos( $lower, 'contract' ) !== false ) {
				$mapped_types[] = 'CONTRACTOR';
			} elseif ( strpos( $lower, 'temp' ) !== false ) {
				$mapped_types[] = 'TEMPORARY';
			} elseif ( strpos( $lower, 'intern' ) !== false ) {
				$mapped_types[] = 'INTERN';
			} elseif ( strpos( $lower, 'volunteer' ) !== false ) {
				$mapped_types[] = 'VOLUNTEER';
			} else {
				$mapped_types[] = 'OTHER';
			}
		}
		if ( empty( $mapped_types ) ) {
			$mapped_types[] = 'FULL_TIME'; // Default fallback.
		}

		$schema = [
			'@context'          => 'https://schema.org',
			'@type'             => 'JobPosting',
			'title'             => get_the_title( $post_id ),
			'description'       => wp_kses_post( wpautop( get_post_field( 'post_content', $post_id ) ) ),
			'datePosted'        => get_the_date( 'c', $post_id ),
			'hiringOrganization' => [
				'@type'  => 'Organization',
				'name'   => esc_html( $comp_name ?: get_bloginfo( 'name' ) ),
				'sameAs' => esc_url( $comp_web ?: home_url( '/' ) ),
			],
			'employmentType'    => $mapped_types,
		];

		if ( $comp_logo ) {
			$schema['hiringOrganization']['logo'] = esc_url( $comp_logo );
		}

		if ( $deadline ) {
			$schema['validThrough'] = date( 'c', strtotime( $deadline ) );
		}

		// Location handling.
		$job_country = ! empty( $countries ) ? reset( $countries ) : 'US';
		$job_city    = ! empty( $cities ) ? reset( $cities ) : '';

		if ( 'Remote' === $work_mode ) {
			$schema['jobLocationType'] = 'TELECOMMUTE';
			// Remote jobs require applicantLocationRequirements in Google Schema.
			$schema['applicantLocationRequirements'] = [
				'@type' => 'Area',
				'name'  => esc_html( $job_country ),
			];
		} else {
			$schema['jobLocation'] = [
				'@type'   => 'Place',
				'address' => [
					'@type'           => 'PostalAddress',
					'addressCountry'  => esc_html( $job_country ),
				],
			];

			if ( $job_city ) {
				$schema['jobLocation']['address']['addressLocality'] = esc_html( $job_city );
			}
			if ( $state ) {
				$schema['jobLocation']['address']['addressRegion'] = esc_html( $state );
			}
		}

		// Salary structure.
		if ( $salary ) {
			// Extract numerical value if range or string.
			preg_match( '/\d+[\d,\.]*/', $salary, $matches );
			if ( ! empty( $matches ) ) {
				$salary_num = floatval( str_replace( [ ',', ' ' ], '', $matches[0] ) );
				$schema['baseSalary'] = [
					'@type'    => 'MonetaryAmount',
					'currency' => esc_html( $currency ?: 'USD' ),
					'value'    => [
						'@type' => 'QuantitativeValue',
						'value' => $salary_num,
						'unitText' => 'YEAR', // Default assumption.
					],
				];
			}
		}

		echo "\n<!-- AIKairali JobPosting Structured Data -->\n";
		echo '<script type="application/ld+json">' . json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT ) . "</script>\n";
	}

	/**
	 * Register REST API Route.
	 *
	 * @param string  $namespace REST namespace.
	 * @param RestAPI $rest      Core RestAPI instance.
	 */
	public function register_rest_endpoints( string $namespace, $rest ): void {
		register_rest_route(
			$namespace,
			'/jobs',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_jobs_api' ],
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
	public function get_jobs_api( \WP_REST_Request $request ): \WP_REST_Response {
		$page     = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;
		$per_page = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 10;
		$search   = $request->get_param( 's' ) ? sanitize_text_field( $request->get_param( 's' ) ) : '';
		
		$args = [
			'post_type'      => 'jobs',
			'post_status'    => 'publish',
			'paged'          => $page,
			'posts_per_page' => $per_page,
		];

		if ( $search ) {
			$args['s'] = $search;
		}

		// Filter by Job Category.
		$category = $request->get_param( 'category' ) ? sanitize_text_field( $request->get_param( 'category' ) ) : '';
		if ( $category ) {
			$args['tax_query'][] = [
				'taxonomy' => 'job-category',
				'field'    => 'slug',
				'terms'    => $category,
			];
		}

		// Filter by Work Mode.
		$work_mode = $request->get_param( 'work_mode' ) ? sanitize_text_field( $request->get_param( 'work_mode' ) ) : '';
		if ( $work_mode ) {
			$args['meta_query'][] = [
				'key'     => 'work_mode',
				'value'   => $work_mode,
				'compare' => '=',
			];
		}

		$query = new \WP_Query( $args );
		$jobs  = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				$jobs[]  = [
					'id'           => $post_id,
					'title'        => get_the_title(),
					'slug'         => get_post_field( 'post_name', $post_id ),
					'date'         => get_the_date( 'c' ),
					'company_name' => get_field( 'company_name', $post_id ),
					'company_logo' => get_field( 'company_logo', $post_id ),
					'work_mode'    => get_field( 'work_mode', $post_id ),
					'salary'       => get_field( 'salary', $post_id ),
					'currency'     => get_field( 'currency', $post_id ),
					'url'          => get_permalink(),
				];
			}
			wp_reset_postdata();
		}

		return new \WP_REST_Response( [
			'total' => $query->found_posts,
			'pages' => $query->max_num_pages,
			'data'  => $jobs,
		], 200 );
	}

	/**
	 * Flush module-specific transient caches on save or delete.
	 */
	public function clear_module_cache(): void {
		global $wpdb;
		// Delete any transients starting with 'aikairali_related_jobs_' or 'aikairali_search_'.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_related_jobs_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_related_jobs_%'" );
		
		// Flush general search cache as well.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_search_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_search_%'" );
	}

	/**
	 * Apply frontend search filters to the main query on the Jobs archive page.
	 *
	 * @param \WP_Query $query The main query object.
	 */
	public function filter_jobs_archive_query( \WP_Query $query ): void {
		if ( is_admin() || ! $query->is_main_query() || ! $query->is_post_type_archive( 'jobs' ) ) {
			return;
		}

		$tax_query  = [];
		$meta_query = [];

		// Job Category.
		if ( ! empty( $_GET['job_cat'] ) ) {
			$tax_query[] = [
				'taxonomy' => 'job-category',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( wp_unslash( $_GET['job_cat'] ) ),
			];
		}

		// Employment Type.
		if ( ! empty( $_GET['emp_type'] ) ) {
			$tax_query[] = [
				'taxonomy' => 'employment-type',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( wp_unslash( $_GET['emp_type'] ) ),
			];
		}

		// Experience Level.
		if ( ! empty( $_GET['exp_level'] ) ) {
			$tax_query[] = [
				'taxonomy' => 'experience-level',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( wp_unslash( $_GET['exp_level'] ) ),
			];
		}

		// Country.
		if ( ! empty( $_GET['country'] ) ) {
			$tax_query[] = [
				'taxonomy' => 'job-country',
				'field'    => 'slug',
				'terms'    => sanitize_text_field( wp_unslash( $_GET['country'] ) ),
			];
		}

		// Work Mode.
		if ( ! empty( $_GET['work_mode'] ) ) {
			$meta_query[] = [
				'key'     => 'work_mode',
				'value'   => sanitize_text_field( wp_unslash( $_GET['work_mode'] ) ),
				'compare' => '=',
			];
		}

		// Search term override if any.
		if ( ! empty( $_GET['job_search'] ) ) {
			$query->set( 's', sanitize_text_field( wp_unslash( $_GET['job_search'] ) ) );
		}

		if ( ! empty( $tax_query ) ) {
			if ( count( $tax_query ) > 1 ) {
				$tax_query['relation'] = 'AND';
			}
			$query->set( 'tax_query', $tax_query );
		}

		if ( ! empty( $meta_query ) ) {
			$query->set( 'meta_query', $meta_query );
		}
	}
}
