<?php
namespace AIKairali\Portal\Modules\AiEvents;

use AIKairali\Portal\Core\ModuleInterface;
use AIKairali\Portal\Core\CPT;
use AIKairali\Portal\Core\Taxonomy;
use AIKairali\Portal\Core\ACFLoader;

/**
 * Class AiEvents
 *
 * Implements the AI Events Module.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Modules/AiEvents
 * @since      1.0.0
 */
class AiEvents implements ModuleInterface {

	/**
	 * Initialize the module by registering hooks.
	 */
	public function init(): void {
		// Custom Admin Columns.
		add_filter( 'manage_ai-events_posts_columns', [ $this, 'add_admin_columns' ] );
		add_action( 'manage_ai-events_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );

		// SEO JSON-LD injection.
		add_action( 'wp_head', [ $this, 'inject_json_ld_schema' ] );

		// Hook into core REST API registration.
		add_action( 'aikairali_rest_api_init', [ $this, 'register_rest_endpoints' ], 10, 2 );

		// Flush cache when events are saved or deleted.
		add_action( 'save_post_ai-events', [ $this, 'clear_module_cache' ] );
		add_action( 'deleted_post', [ $this, 'clear_module_cache' ] );
	}

	/**
	 * Register Custom Post Types.
	 */
	public function register_cpts(): void {
		CPT::register(
			'ai-events',
			__( 'AI Event', 'aikairali-portal' ),
			__( 'AI Events', 'aikairali-portal' ),
			[
				'menu_icon'    => 'dashicons-calendar-alt',
				'supports'     => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions' ],
				'has_archive'  => 'ai-events',
				'rewrite'      => [ 'slug' => 'ai-events', 'with_front' => false ],
			]
		);
	}

	/**
	 * Register Taxonomies.
	 */
	public function register_taxonomies(): void {
		// Event Country.
		Taxonomy::register(
			'event-country',
			'ai-events',
			__( 'Country', 'aikairali-portal' ),
			__( 'Countries', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'event-country' ] ]
		);

		// Event City.
		Taxonomy::register(
			'event-city',
			'ai-events',
			__( 'City', 'aikairali-portal' ),
			__( 'Cities', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'event-city' ] ]
		);

		// Event Type.
		Taxonomy::register(
			'event-type',
			'ai-events',
			__( 'Event Type', 'aikairali-portal' ),
			__( 'Event Types', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'event-type' ] ]
		);

		// Event Organizer.
		Taxonomy::register(
			'event-organizer',
			'ai-events',
			__( 'Organizer', 'aikairali-portal' ),
			__( 'Organizers', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'event-organizer' ] ]
		);
	}

	/**
	 * Register ACF Field Groups programmatically.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		// Field Group: Event Details.
		ACFLoader::register_field_group( [
			'key'      => 'group_event_details',
			'title'    => __( 'Event Details', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_event_date',
					'label' => __( 'Event Date', 'aikairali-portal' ),
					'name'  => 'event_date',
					'type'  => 'date_picker',
					'display_format' => 'Y-m-d',
					'return_format'  => 'Y-m-d',
					'wrapper' => [ 'width' => '33' ],
				],
				[
					'key'   => 'field_event_start_time',
					'label' => __( 'Start Time', 'aikairali-portal' ),
					'name'  => 'start_time',
					'type'  => 'time_picker',
					'display_format' => 'g:i a',
					'return_format'  => 'H:i:s',
					'wrapper' => [ 'width' => '33' ],
				],
				[
					'key'   => 'field_event_end_time',
					'label' => __( 'End Time', 'aikairali-portal' ),
					'name'  => 'end_time',
					'type'  => 'time_picker',
					'display_format' => 'g:i a',
					'return_format'  => 'H:i:s',
					'wrapper' => [ 'width' => '33' ],
				],
				[
					'key'     => 'field_event_online',
					'label'   => __( 'Online Event', 'aikairali-portal' ),
					'name'    => 'online_event',
					'type'    => 'true_false',
					'ui'      => 1,
					'wrapper' => [ 'width' => '25' ],
				],
				[
					'key'   => 'field_event_venue',
					'label' => __( 'Venue', 'aikairali-portal' ),
					'name'  => 'venue',
					'type'  => 'text',
					'placeholder' => __( 'e.g. Convention Center, Virtual', 'aikairali-portal' ),
					'wrapper' => [ 'width' => '75' ],
					'conditional_logic' => [
						[
							[
								'field'    => 'field_event_online',
								'operator' => '!=',
								'value'    => '1',
							],
						],
					],
				],
				[
					'key'   => 'field_event_registration_url',
					'label' => __( 'Registration URL', 'aikairali-portal' ),
					'name'  => 'registration_url',
					'type'  => 'url',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_event_ticket_price',
					'label' => __( 'Ticket Price', 'aikairali-portal' ),
					'name'  => 'ticket_price',
					'type'  => 'text',
					'placeholder' => __( 'e.g. $99, Free', 'aikairali-portal' ),
					'wrapper' => [ 'width' => '25' ],
				],
				[
					'key'   => 'field_event_capacity',
					'label' => __( 'Capacity', 'aikairali-portal' ),
					'name'  => 'capacity',
					'type'  => 'number',
					'wrapper' => [ 'width' => '25' ],
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-events',
					],
				],
			],
		] );

		// Field Group: Event Content Details.
		ACFLoader::register_field_group( [
			'key'      => 'group_event_content',
			'title'    => __( 'Event Content Details', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_event_speakers',
					'label' => __( 'Speakers', 'aikairali-portal' ),
					'name'  => 'speakers',
					'type'  => 'textarea',
					'rows'  => 3,
					'placeholder' => __( 'List of speakers...', 'aikairali-portal' ),
				],
				[
					'key'   => 'field_event_sponsors',
					'label' => __( 'Sponsors', 'aikairali-portal' ),
					'name'  => 'sponsors',
					'type'  => 'textarea',
					'rows'  => 3,
					'placeholder' => __( 'List of sponsors...', 'aikairali-portal' ),
				],
				[
					'key'   => 'field_event_agenda',
					'label' => __( 'Event Agenda', 'aikairali-portal' ),
					'name'  => 'event_agenda',
					'type'  => 'wysiwyg',
					'media_upload' => 0,
				],
				[
					'key'   => 'field_event_gallery',
					'label' => __( 'Gallery', 'aikairali-portal' ),
					'name'  => 'gallery',
					'type'  => 'gallery',
					'return_format' => 'url',
					'preview_size'  => 'medium',
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-events',
					],
				],
			],
		] );

		// Field Group: SEO Overrides.
		ACFLoader::register_field_group( [
			'key'      => 'group_event_seo',
			'title'    => __( 'SEO Meta Overrides', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_event_seo_title',
					'label' => __( 'SEO Title', 'aikairali-portal' ),
					'name'  => 'seo_title',
					'type'  => 'text',
				],
				[
					'key'   => 'field_event_seo_desc',
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
						'value'    => 'ai-events',
					],
				],
			],
		] );
	}

	/**
	 * Add custom columns to the WP Admin AI Events list table.
	 *
	 * @param array $columns Current columns.
	 * @return array Modified columns.
	 */
	public function add_admin_columns( array $columns ): array {
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			if ( 'date' === $key ) {
				$new_columns['event_date_time'] = __( 'Date & Time', 'aikairali-portal' );
				$new_columns['event_venue']     = __( 'Venue', 'aikairali-portal' );
				$new_columns['event_organizer'] = __( 'Organizer', 'aikairali-portal' );
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
			case 'event_date_time':
				$date       = get_field( 'event_date', $post_id );
				$start_time = get_field( 'start_time', $post_id );
				
				if ( $date ) {
					$formatted_date = date_i18n( get_option( 'date_format' ), strtotime( $date ) );
					$formatted_time = $start_time ? date_i18n( get_option( 'time_format' ), strtotime( $start_time ) ) : '';
					
					echo esc_html( $formatted_date );
					if ( $formatted_time ) {
						echo '<br><span style="color:#888; font-size:12px;">' . esc_html( $formatted_time ) . '</span>';
					}
				} else {
					echo '—';
				}
				break;

			case 'event_venue':
				$is_online = get_field( 'online_event', $post_id );
				$venue     = get_field( 'venue', $post_id );
				if ( $is_online ) {
					echo '<span style="color:#2271b1; font-weight:bold;">' . esc_html__( 'Online', 'aikairali-portal' ) . '</span>';
				} else {
					echo esc_html( $venue ?: '—' );
				}
				break;

			case 'event_organizer':
				$terms = get_the_term_list( $post_id, 'event-organizer', '', ', ', '' );
				echo $terms ? wp_kses_post( $terms ) : '—';
				break;
		}
	}

	/**
	 * Inject Google-compliant JSON-LD Event schema.
	 */
	public function inject_json_ld_schema(): void {
		if ( ! is_singular( 'ai-events' ) ) {
			return;
		}

		$post_id  = get_the_ID();
		$settings = get_option( 'aikairali_portal_settings', [] );
		$enable   = $settings['seo']['enable_json_ld'] ?? '1';

		if ( '1' !== $enable ) {
			return;
		}

		$organizers     = wp_get_post_terms( $post_id, 'event-organizer', [ 'fields' => 'names' ] );
		$organizer_name = ! empty( $organizers ) ? reset( $organizers ) : get_bloginfo( 'name' );
		
		$date       = get_field( 'event_date', $post_id );
		$start_time = get_field( 'start_time', $post_id );
		$end_time   = get_field( 'end_time', $post_id );
		
		$start_datetime = $date ? $date . 'T' . ( $start_time ?: '00:00:00' ) : '';
		$end_datetime   = $date ? $date . 'T' . ( $end_time ?: '23:59:59' ) : '';

		$is_online = get_field( 'online_event', $post_id );
		$venue     = get_field( 'venue', $post_id );
		
		$description = get_field( 'seo_description', $post_id ) ?: wp_trim_words( get_post_field( 'post_content', $post_id ), 20 );
		$thumbnail   = get_the_post_thumbnail_url( $post_id, 'large' );

		$schema = [
			'@context'    => 'https://schema.org',
			'@type'       => 'Event',
			'name'        => get_the_title( $post_id ),
			'description' => esc_html( $description ),
			'organizer'   => [
				'@type' => 'Organization',
				'name'  => esc_html( $organizer_name ),
			],
		];

		if ( $start_datetime ) {
			$schema['startDate'] = $start_datetime;
		}
		if ( $end_datetime ) {
			$schema['endDate'] = $end_datetime;
		}
		if ( $thumbnail ) {
			$schema['image'] = [ esc_url( $thumbnail ) ];
		}

		if ( $is_online ) {
			$schema['eventAttendanceMode'] = 'https://schema.org/OnlineEventAttendanceMode';
			$schema['location'] = [
				'@type' => 'VirtualLocation',
				'url'   => esc_url( get_permalink( $post_id ) ),
			];
		} else {
			$schema['eventAttendanceMode'] = 'https://schema.org/OfflineEventAttendanceMode';
			if ( $venue ) {
				$schema['location'] = [
					'@type' => 'Place',
					'name'  => esc_html( $venue ),
					'address' => esc_html( $venue ), // simplified mapping
				];
			}
		}

		echo "\n<!-- AIKairali Event Structured Data -->\n";
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
			'/events',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_events_api' ],
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
	public function get_events_api( \WP_REST_Request $request ): \WP_REST_Response {
		$page     = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;
		$per_page = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 10;
		$search   = $request->get_param( 's' ) ? sanitize_text_field( $request->get_param( 's' ) ) : '';
		
		$args = [
			'post_type'      => 'ai-events',
			'post_status'    => 'publish',
			'paged'          => $page,
			'posts_per_page' => $per_page,
			'meta_key'       => 'event_date',
			'orderby'        => 'meta_value',
			'order'          => 'DESC', // Might want to sort upcoming first depending on logic
		];

		if ( $search ) {
			$args['s'] = $search;
		}

		$query = new \WP_Query( $args );
		$events = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				
				$organizers = wp_get_post_terms( $post_id, 'event-organizer', [ 'fields' => 'names' ] );
				
				$events[] = [
					'id'            => $post_id,
					'title'         => get_the_title(),
					'slug'          => get_post_field( 'post_name', $post_id ),
					'date'          => get_field( 'event_date', $post_id ),
					'start_time'    => get_field( 'start_time', $post_id ),
					'end_time'      => get_field( 'end_time', $post_id ),
					'organizer'     => ! empty( $organizers ) ? reset( $organizers ) : '',
					'is_online'     => get_field( 'online_event', $post_id ),
					'venue'         => get_field( 'venue', $post_id ),
					'ticket_price'  => get_field( 'ticket_price', $post_id ),
					'thumbnail_url' => get_the_post_thumbnail_url( $post_id, 'large' ),
					'url'           => get_permalink(),
				];
			}
			wp_reset_postdata();
		}

		return new \WP_REST_Response( [
			'total' => $query->found_posts,
			'pages' => $query->max_num_pages,
			'data'  => $events,
		], 200 );
	}

	/**
	 * Flush module-specific transient caches on save or delete.
	 */
	public function clear_module_cache(): void {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_events_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_events_%'" );
		
		// Flush general search cache.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_search_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_search_%'" );
	}
}
