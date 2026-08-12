<?php
// Global polyfill for mb_strtoupper if mbstring extension is disabled
if ( ! function_exists( 'mb_strtoupper' ) ) {
	function mb_strtoupper( $str, $encoding = 'UTF-8' ) {
		return strtoupper( $str );
	}
}

/**
 * Class Helpers
 *
 * General helper functions for the plugin.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.0.0
 */
class Helpers {

	/**
	 * Render breadcrumbs navigation.
	 */
	public static function breadcrumbs(): void {
		if ( is_front_page() ) {
			return;
		}

		echo '<nav class="aikairali-breadcrumbs" aria-label="breadcrumb">';
		echo '<ol class="aikairali-breadcrumb-list" style="display:flex; list-style:none; padding:0; margin:0; gap:5px;">';
		echo '<li class="breadcrumb-item"><a href="' . esc_url( home_url( '/' ) ) . '">' . esc_html__( 'Home', 'aikairali-portal' ) . '</a></li>';

		if ( is_home() ) {
			echo '<li class="breadcrumb-item active" aria-current="page">&nbsp;/&nbsp;' . esc_html__( 'Blog', 'aikairali-portal' ) . '</li>';
		} elseif ( is_archive() ) {
			$post_type = get_query_var( 'post_type' );
			if ( is_array( $post_type ) ) {
				$post_type = reset( $post_type );
			}
			$post_type_obj = get_post_type_object( $post_type );
			$label = $post_type_obj ? $post_type_obj->labels->name : __( 'Archive', 'aikairali-portal' );
			echo '<li class="breadcrumb-item active" aria-current="page">&nbsp;/&nbsp;' . esc_html( $label ) . '</li>';
		} elseif ( is_single() ) {
			$post_type = get_post_type();
			$post_type_obj = get_post_type_object( $post_type );
			if ( $post_type_obj && $post_type_obj->has_archive ) {
				$archive_url = get_post_type_archive_link( $post_type );
				echo '<li class="breadcrumb-item">&nbsp;/&nbsp;<a href="' . esc_url( $archive_url ) . '">' . esc_html( $post_type_obj->labels->name ) . '</a></li>';
			}
			echo '<li class="breadcrumb-item active" aria-current="page">&nbsp;/&nbsp;' . esc_html( get_the_title() ) . '</li>';
		} elseif ( is_page() ) {
			global $post;
			if ( isset( $post->post_parent ) && $post->post_parent ) {
				$anc = array_reverse( get_post_ancestors( $post->ID ) );
				foreach ( $anc as $ancestor ) {
					echo '<li class="breadcrumb-item">&nbsp;/&nbsp;<a href="' . esc_url( get_permalink( $ancestor ) ) . '">' . esc_html( get_the_title( $ancestor ) ) . '</a></li>';
				}
			}
			echo '<li class="breadcrumb-item active" aria-current="page">&nbsp;/&nbsp;' . esc_html( get_the_title() ) . '</li>';
		} elseif ( is_search() ) {
			echo '<li class="breadcrumb-item active" aria-current="page">&nbsp;/&nbsp;' . sprintf( esc_html__( 'Search results for "%s"', 'aikairali-portal' ), esc_html( get_search_query() ) ) . '</li>';
		}

		echo '</ol>';
		echo '</nav>';
	}

	/**
	 * Render post pagination.
	 *
	 * @param \WP_Query|null $query Custom query to paginate. If null, global $wp_query is used.
	 */
	public static function pagination( $query = null ): void {
		if ( ! $query ) {
			global $wp_query;
			$query = $wp_query;
		}

		if ( $query->max_num_pages <= 1 ) {
			return;
		}

		$big = 999999999;
		$pages = paginate_links( [
			'base'      => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'    => '?paged=%#%',
			'current'   => max( 1, get_query_var( 'paged' ) ),
			'total'     => $query->max_num_pages,
			'type'      => 'array',
			'prev_text' => '&laquo;',
			'next_text' => '&raquo;',
		] );

		if ( is_array( $pages ) ) {
			echo '<nav class="aikairali-pagination" aria-label="Page navigation">';
			echo '<ul class="pagination" style="display:flex; list-style:none; padding:0; gap:5px;">';
			foreach ( $pages as $page ) {
				$active = strpos( $page, 'current' ) !== false ? 'active' : '';
				echo '<li class="page-item ' . esc_attr( $active ) . '" style="padding:5px 10px; border:1px solid #ddd;">' . wp_kses_post( $page ) . '</li>';
			}
			echo '</ul>';
			echo '</nav>';
		}
	}

	/**
	 * Get post thumbnail URL or fallback image.
	 *
	 * @param int|null    $post_id Post ID. If null, current post is used.
	 * @param string      $size    Image size. Default is 'post-thumbnail'.
	 * @return string Image URL.
	 */
	public static function get_image_url( ?int $post_id = null, string $size = 'post-thumbnail' ): string {
		$post_id = $post_id ?: get_the_ID();
		if ( has_post_thumbnail( $post_id ) ) {
			$url = get_the_post_thumbnail_url( $post_id, $size );
			if ( $url ) {
				return $url;
			}
		}

		// Fallback image.
		$settings = get_option( 'aikairali_portal_settings', [] );
		if ( ! empty( $settings['brand']['fallback_image'] ) ) {
			return $settings['brand']['fallback_image'];
		}

		return AIKAIRALI_PORTAL_URL . 'assets/images/fallback.png';
	}

	/**
	 * Security helper to verify Capability and Nonce.
	 *
	 * @param string $nonce_action Nonce action name.
	 * @param string $nonce_field  Nonce query parameter or header.
	 * @param string $capability   Required user capability. Default is 'manage_options'.
	 * @return bool True if authorized, false otherwise.
	 */
	public static function verify_auth( string $nonce_action, string $nonce_field = '_wpnonce', string $capability = 'manage_options' ): bool {
		if ( ! current_user_can( $capability ) ) {
			return false;
		}

		$nonce = '';
		if ( isset( $_REQUEST[ $nonce_field ] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_REQUEST[ $nonce_field ] ) );
		} elseif ( isset( $_SERVER['HTTP_X_WP_NONCE'] ) ) {
			$nonce = sanitize_text_field( wp_unslash( $_SERVER['HTTP_X_WP_NONCE'] ) );
		}

		return (bool) wp_verify_nonce( $nonce, $nonce_action );
	}
}
