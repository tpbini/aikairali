<?php
namespace AIKairali\Portal\Modules\AiVideos;

use AIKairali\Portal\Core\ModuleInterface;
use AIKairali\Portal\Core\CPT;
use AIKairali\Portal\Core\Taxonomy;
use AIKairali\Portal\Core\ACFLoader;

/**
 * Class AiVideos
 *
 * Implements the AI Videos Module.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Modules/AiVideos
 * @since      1.0.0
 */
class AiVideos implements ModuleInterface {

	/**
	 * Initialize the module by registering hooks.
	 */
	public function init(): void {
		// Custom Admin Columns.
		add_filter( 'manage_ai-videos_posts_columns', [ $this, 'add_admin_columns' ] );
		add_action( 'manage_ai-videos_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );

		// SEO JSON-LD injection.
		add_action( 'wp_head', [ $this, 'inject_json_ld_schema' ] );

		// Hook into core REST API registration.
		add_action( 'aikairali_rest_api_init', [ $this, 'register_rest_endpoints' ], 10, 2 );

		// Flush cache when videos are saved or deleted.
		add_action( 'save_post_ai-videos', [ $this, 'clear_module_cache' ] );
		add_action( 'deleted_post', [ $this, 'clear_module_cache' ] );
	}

	/**
	 * Register Custom Post Types.
	 */
	public function register_cpts(): void {
		CPT::register(
			'ai-videos',
			__( 'AI Video', 'aikairali-portal' ),
			__( 'AI Videos', 'aikairali-portal' ),
			[
				'menu_icon'    => 'dashicons-video-alt3',
				'supports'     => [ 'title', 'thumbnail', 'excerpt', 'revisions' ],
				'has_archive'  => 'ai-videos',
				'rewrite'      => [ 'slug' => 'ai-videos', 'with_front' => false ],
			]
		);
	}

	/**
	 * Register Taxonomies.
	 */
	public function register_taxonomies(): void {
		// Video Category (Hierarchical)
		Taxonomy::register(
			'video-category',
			'ai-videos',
			__( 'Video Category', 'aikairali-portal' ),
			__( 'Video Categories', 'aikairali-portal' ),
			[
				'hierarchical' => true,
				'rewrite'      => [ 'slug' => 'video-category' ],
			]
		);

		// Video Tag (Non-hierarchical)
		Taxonomy::register(
			'video-tag',
			'ai-videos',
			__( 'Video Tag', 'aikairali-portal' ),
			__( 'Video Tags', 'aikairali-portal' ),
			[
				'hierarchical' => false,
				'rewrite'      => [ 'slug' => 'video-tag' ],
			]
		);
	}

	/**
	 * Register ACF Field Groups programmatically.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		// Field Group: Video Details.
		ACFLoader::register_field_group( [
			'key'      => 'group_video_details',
			'title'    => __( 'Video Details', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_video_url',
					'label' => __( 'Video URL', 'aikairali-portal' ),
					'name'  => 'video_url',
					'type'  => 'oembed',
					'wrapper' => [ 'width' => '100' ],
				],
				[
					'key'   => 'field_video_youtube_id',
					'label' => __( 'YouTube ID', 'aikairali-portal' ),
					'name'  => 'youtube_id',
					'type'  => 'text',
					'instructions' => __( 'E.g., dQw4w9WgXcQ. Useful for API integrations.', 'aikairali-portal' ),
					'wrapper' => [ 'width' => '25' ],
				],
				[
					'key'   => 'field_video_duration',
					'label' => __( 'Duration', 'aikairali-portal' ),
					'name'  => 'duration',
					'type'  => 'text',
					'placeholder' => __( 'e.g., PT15M33S or 15:33', 'aikairali-portal' ),
					'wrapper' => [ 'width' => '25' ],
				],
				[
					'key'   => 'field_video_channel',
					'label' => __( 'Channel Name', 'aikairali-portal' ),
					'name'  => 'channel_name',
					'type'  => 'text',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'   => 'field_video_pub_date',
					'label' => __( 'Published Date', 'aikairali-portal' ),
					'name'  => 'published_date',
					'type'  => 'date_picker',
					'display_format' => 'F j, Y',
					'return_format'  => 'Y-m-d',
					'wrapper' => [ 'width' => '50' ],
				],
				[
					'key'     => 'field_video_featured',
					'label'   => __( 'Featured Video', 'aikairali-portal' ),
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
						'value'    => 'ai-videos',
					],
				],
			],
		] );

		// Field Group: Content Details.
		ACFLoader::register_field_group( [
			'key'      => 'group_video_content',
			'title'    => __( 'Content Details', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'         => 'field_video_key_points',
					'label'       => __( 'Key Points', 'aikairali-portal' ),
					'name'        => 'key_points',
					'type'        => 'textarea',
					'rows'        => 5,
					'placeholder' => __( 'One key point per line', 'aikairali-portal' ),
				],
				[
					'key'          => 'field_video_transcript',
					'label'        => __( 'Transcript', 'aikairali-portal' ),
					'name'         => 'transcript',
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
						'value'    => 'ai-videos',
					],
				],
			],
		] );

		// Field Group: SEO Overrides.
		ACFLoader::register_field_group( [
			'key'      => 'group_video_seo',
			'title'    => __( 'SEO Meta Overrides', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'   => 'field_video_seo_title',
					'label' => __( 'SEO Title', 'aikairali-portal' ),
					'name'  => 'seo_title',
					'type'  => 'text',
				],
				[
					'key'   => 'field_video_seo_desc',
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
						'value'    => 'ai-videos',
					],
				],
			],
		] );
	}

	/**
	 * Add custom columns to the WP Admin AI Videos list table.
	 *
	 * @param array $columns Current columns.
	 * @return array Modified columns.
	 */
	public function add_admin_columns( array $columns ): array {
		$new_columns = [];
		foreach ( $columns as $key => $value ) {
			if ( 'date' === $key ) {
				$new_columns['video_channel']  = __( 'Channel', 'aikairali-portal' );
				$new_columns['video_duration'] = __( 'Duration', 'aikairali-portal' );
				$new_columns['video_pub_date'] = __( 'Published', 'aikairali-portal' );
				$new_columns['video_featured'] = __( 'Featured', 'aikairali-portal' );
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
			case 'video_channel':
				$channel = get_field( 'channel_name', $post_id );
				echo esc_html( $channel ?: '—' );
				break;

			case 'video_duration':
				$duration = get_field( 'duration', $post_id );
				echo esc_html( $duration ?: '—' );
				break;

			case 'video_pub_date':
				$pub_date = get_field( 'published_date', $post_id );
				if ( $pub_date ) {
					echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $pub_date ) ) );
				} else {
					echo '—';
				}
				break;

			case 'video_featured':
				$featured = get_field( 'featured', $post_id );
				if ( $featured ) {
					echo '<span class="dashicons dashicons-star-filled" style="color:#f56e28;" title="' . esc_attr__( 'Featured', 'aikairali-portal' ) . '"></span>';
				} else {
					echo '—';
				}
				break;
		}
	}

	/**
	 * Inject Google-compliant JSON-LD VideoObject schema.
	 */
	public function inject_json_ld_schema(): void {
		if ( ! is_singular( 'ai-videos' ) ) {
			return;
		}

		$post_id  = get_the_ID();
		$settings = get_option( 'aikairali_portal_settings', [] );
		$enable   = $settings['seo']['enable_json_ld'] ?? '1';

		if ( '1' !== $enable ) {
			return;
		}

		$thumbnail   = get_the_post_thumbnail_url( $post_id, 'large' );
		if ( ! $thumbnail ) {
			return; // Thumbnail is required for VideoObject
		}

		$video_url   = get_field( 'video_url', $post_id );
		$description = get_field( 'seo_description', $post_id ) ?: wp_trim_words( get_post_field( 'post_excerpt', $post_id ), 30 );
		if ( empty( $description ) ) {
			$description = get_the_title( $post_id );
		}
		
		$upload_date = get_field( 'published_date', $post_id ) ?: get_the_date( 'c', $post_id );
		$duration    = get_field( 'duration', $post_id );

		$schema = [
			'@context'     => 'https://schema.org',
			'@type'        => 'VideoObject',
			'name'         => get_the_title( $post_id ),
			'description'  => esc_html( $description ),
			'thumbnailUrl' => esc_url( $thumbnail ),
			'uploadDate'   => esc_html( $upload_date ),
		];

		// Try to format duration to ISO 8601 (e.g. PT15M33S) if it's just '15:33'
		if ( $duration ) {
			if ( strpos( $duration, 'PT' ) === 0 ) {
				$schema['duration'] = esc_html( $duration );
			} elseif ( preg_match( '/^(\d+):(\d+)$/', $duration, $matches ) ) {
				$schema['duration'] = 'PT' . $matches[1] . 'M' . $matches[2] . 'S';
			} elseif ( preg_match( '/^(\d+):(\d+):(\d+)$/', $duration, $matches ) ) {
				$schema['duration'] = 'PT' . $matches[1] . 'H' . $matches[2] . 'M' . $matches[3] . 'S';
			}
		}

		if ( $video_url ) {
			$schema['embedUrl'] = esc_url( $video_url );
		}

		echo "\n<!-- AIKairali Video Structured Data -->\n";
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
			'/videos',
			[
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_videos_api' ],
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
	public function get_videos_api( \WP_REST_Request $request ): \WP_REST_Response {
		$page     = $request->get_param( 'page' ) ? absint( $request->get_param( 'page' ) ) : 1;
		$per_page = $request->get_param( 'per_page' ) ? absint( $request->get_param( 'per_page' ) ) : 12;
		$search   = $request->get_param( 's' ) ? sanitize_text_field( $request->get_param( 's' ) ) : '';
		
		$args = [
			'post_type'      => 'ai-videos',
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
				'taxonomy' => 'video-category',
				'field'    => 'slug',
				'terms'    => $category,
			];
		}
		
		$tag = $request->get_param( 'tag' ) ? sanitize_text_field( $request->get_param( 'tag' ) ) : '';
		if ( $tag ) {
			$args['tax_query'][] = [
				'taxonomy' => 'video-tag',
				'field'    => 'slug',
				'terms'    => $tag,
			];
		}

		// Optionally filter only featured
		$featured = $request->get_param( 'featured' );
		if ( null !== $featured ) {
			$args['meta_query'][] = [
				'key'   => 'featured',
				'value' => '1',
			];
		}

		$query = new \WP_Query( $args );
		$videos = [];

		if ( $query->have_posts() ) {
			while ( $query->have_posts() ) {
				$query->the_post();
				$post_id = get_the_ID();
				
				$categories = wp_get_post_terms( $post_id, 'video-category', [ 'fields' => 'names' ] );
				$tags       = wp_get_post_terms( $post_id, 'video-tag', [ 'fields' => 'names' ] );
				
				// Extract key points
				$points = [];
				if ( have_rows( 'key_points', $post_id ) ) {
					while ( have_rows( 'key_points', $post_id ) ) {
						the_row();
						$points[] = get_sub_field( 'point_text' );
					}
				}
				
				$videos[] = [
					'id'            => $post_id,
					'title'         => get_the_title(),
					'slug'          => get_post_field( 'post_name', $post_id ),
					'date'          => get_the_date( 'c' ),
					'categories'    => $categories,
					'tags'          => $tags,
					'channel'       => get_field( 'channel_name', $post_id ),
					'duration'      => get_field( 'duration', $post_id ),
					'published'     => get_field( 'published_date', $post_id ),
					'youtube_id'    => get_field( 'youtube_id', $post_id ),
					'featured'      => (bool) get_field( 'featured', $post_id ),
					'key_points'    => $points,
					'thumbnail_url' => get_the_post_thumbnail_url( $post_id, 'large' ),
					'video_url'     => get_field( 'video_url', $post_id ),
					'url'           => get_permalink(),
				];
			}
			wp_reset_postdata();
		}

		return new \WP_REST_Response( [
			'total' => $query->found_posts,
			'pages' => $query->max_num_pages,
			'data'  => $videos,
		], 200 );
	}

	/**
	 * Flush module-specific transient caches on save or delete.
	 */
	public function clear_module_cache(): void {
		global $wpdb;
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_videos_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_videos_%'" );
		
		// Flush general search cache.
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_aikairali_search_%'" );
		$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '_transient_timeout_aikairali_search_%'" );
	}
}
