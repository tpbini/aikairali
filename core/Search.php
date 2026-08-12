<?php
namespace AIKairali\Portal\Core;

/**
 * Class Search
 *
 * Implements the Global AJAX Search functionality.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.0.0
 */
class Search {

	/**
	 * Constructor.
	 *
	 * @param Loader $loader The hook loader.
	 */
	public function __construct( Loader $loader ) {
		$loader->add_action( 'wp_ajax_aikairali_search', $this, 'ajax_search' );
		$loader->add_action( 'wp_ajax_nopriv_aikairali_search', $this, 'ajax_search' );
	}

	/**
	 * AJAX Search handler.
	 */
	public function ajax_search(): void {
		check_ajax_referer( 'aikairali_portal_search_nonce', 'nonce' );

		$query_str = isset( $_GET['q'] ) ? sanitize_text_field( wp_unslash( $_GET['q'] ) ) : '';
		if ( empty( $query_str ) ) {
			wp_send_json_success( [] );
		}

		$modules = Plugin::instance()->get_modules();
		$post_types = array_keys( $modules );

		if ( empty( $post_types ) ) {
			wp_send_json_success( [] );
		}

		// Cache key for search results.
		$cache_key = 'search_' . md5( $query_str . implode( '_', $post_types ) );
		$results = Cache::get( $cache_key );

		if ( false === $results ) {
			// Temporarily hook filters to extend search query to Custom Fields and Taxonomies.
			add_filter( 'posts_join', [ $this, 'search_join' ], 10, 2 );
			add_filter( 'posts_where', [ $this, 'search_where' ], 10, 2 );
			add_filter( 'posts_distinct', [ $this, 'search_distinct' ] );

			$search_query = new \WP_Query( [
				'post_type'      => $post_types,
				'post_status'    => 'publish',
				's'              => $query_str,
				'posts_per_page' => 10,
				'fields'         => 'ids',
			] );

			// Remove filters immediately after running the query.
			remove_filter( 'posts_join', [ $this, 'search_join' ] );
			remove_filter( 'posts_where', [ $this, 'search_where' ] );
			remove_filter( 'posts_distinct', [ $this, 'search_distinct' ] );

			$results = [];
			if ( $search_query->have_posts() ) {
				foreach ( $search_query->posts as $post_id ) {
					$results[] = [
						'id'        => $post_id,
						'title'     => get_the_title( $post_id ),
						'url'       => get_permalink( $post_id ),
						'excerpt'   => wp_strip_all_tags( get_the_excerpt( $post_id ) ),
						'post_type' => get_post_type_labels( get_post_type_object( get_post_type( $post_id ) ) )->singular_name,
						'image'     => Helpers::get_image_url( $post_id, 'thumbnail' ),
					];
				}
			}

			// Cache search results for 10 minutes.
			Cache::set( $cache_key, $results, 600 );
		}

		wp_send_json_success( $results );
	}

	/**
	 * Join postmeta and term tables to the query.
	 *
	 * @param string    $join  SQL join string.
	 * @param \WP_Query $query The WP_Query object.
	 * @return string Modified SQL join string.
	 */
	public function search_join( string $join, \WP_Query $query ): string {
		global $wpdb;

		if ( ! empty( $query->query_vars['s'] ) ) {
			$join .= " LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id ";
			$join .= " LEFT JOIN {$wpdb->term_relationships} ON {$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id ";
			$join .= " LEFT JOIN {$wpdb->term_taxonomy} ON {$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id ";
			$join .= " LEFT JOIN {$wpdb->terms} ON {$wpdb->term_taxonomy}.term_id = {$wpdb->terms}.term_id ";
		}

		return $join;
	}

	/**
	 * Modify the search WHERE clause to search custom fields and taxonomies.
	 *
	 * @param string    $where SQL where clause.
	 * @param \WP_Query $query The WP_Query object.
	 * @return string Modified SQL where clause.
	 */
	public function search_where( string $where, \WP_Query $query ): string {
		global $wpdb;

		if ( ! empty( $query->query_vars['s'] ) ) {
			$search = $query->query_vars['s'];
			$search_escaped = esc_sql( $wpdb->esc_like( $search ) );

			// Modify query to look inside terms name or meta values as well.
			$where = preg_replace(
				"/\({$wpdb->posts}\.post_title\s+LIKE\s+'%{$search_escaped}%'\)/",
				"({$wpdb->posts}.post_title LIKE '%{$search_escaped}%') 
				OR ({$wpdb->postmeta}.meta_value LIKE '%{$search_escaped}%') 
				OR ({$wpdb->terms}.name LIKE '%{$search_escaped}%')",
				$where
			);
		}

		return $where;
	}

	/**
	 * Ensure distinct results.
	 *
	 * @return string 'DISTINCT'.
	 */
	public function search_distinct(): string {
		return 'DISTINCT';
	}
}
