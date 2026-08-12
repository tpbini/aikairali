<?php
/**
 * Template part for rendering a Portrait AI Video Card (Carousel Item).
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Templates/Parts
 * @since      1.0.0
 */

$post_id   = get_the_ID();
$permalink = get_permalink( $post_id );
$title     = get_the_title( $post_id );

// Get Category Name (video-category or fallback to post category)
$categories = wp_get_post_terms( $post_id, 'video-category', [ 'fields' => 'names' ] );
if ( is_wp_error( $categories ) || empty( $categories ) ) {
	$categories = wp_get_post_terms( $post_id, 'category', [ 'fields' => 'names' ] );
}
$cat_name = ! empty( $categories ) && ! is_wp_error( $categories ) ? reset( $categories ) : __( 'AI TUTORIAL', 'aikairali-portal' );

// Robust Image Resolver (Prioritize YouTube ID -> Featured Thumbnail -> ACF -> Fallback)
$image_url = '';
$yt_id = function_exists( 'get_field' ) ? get_field( 'youtube_id', $post_id ) : '';
if ( ! empty( $yt_id ) ) {
	$image_url = "https://i.ytimg.com/vi/{$yt_id}/hqdefault.jpg";
}

if ( empty( $image_url ) && has_post_thumbnail( $post_id ) ) {
	$image_url = get_the_post_thumbnail_url( $post_id, 'medium_large' );
}

if ( empty( $image_url ) && function_exists( 'get_field' ) ) {
	$v_img = get_field( 'video_thumbnail', $post_id ) ?: ( get_field( 'thumbnail', $post_id ) ?: get_field( 'image', $post_id ) );
	if ( is_array( $v_img ) ) {
		$image_url = isset( $v_img['url'] ) ? $v_img['url'] : '';
	} elseif ( is_numeric( $v_img ) ) {
		$image_url = wp_get_attachment_image_url( $v_img, 'medium_large' );
	} elseif ( is_string( $v_img ) && ! empty( $v_img ) ) {
		$image_url = $v_img;
	}
}

if ( empty( $image_url ) ) {
	$image_url = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=600&auto=format&fit=crop';
}
?>

<div class="aik-video-portrait-card">
	<a href="<?php echo esc_url( $permalink ); ?>" class="aik-vp-link" title="<?php echo esc_attr( $title ); ?>">
		<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="aik-vp-img" loading="lazy" />
		<div class="aik-vp-play-badge">
			<svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor"><path d="M8 5v14l11-7z"/></svg>
		</div>
		<div class="aik-vp-gradient">
			<h4 class="aik-vp-title"><?php echo esc_html( $title ); ?></h4>
			<div class="aik-vp-cat"><?php echo esc_html( mb_strtoupper( $cat_name, 'UTF-8' ) ); ?></div>
		</div>
	</a>
</div>
