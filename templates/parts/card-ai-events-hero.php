<?php
/**
 * Template part for rendering an AI Event Hero Card (Column 1).
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Templates/Parts
 * @since      1.0.0
 */

$post_id   = get_the_ID();
$permalink = get_permalink( $post_id );
$title     = get_the_title( $post_id );

// Get Category Name (event-type or event-country or fallback to post category)
$categories = wp_get_post_terms( $post_id, 'event-type', [ 'fields' => 'names' ] );
if ( is_wp_error( $categories ) || empty( $categories ) ) {
	$categories = wp_get_post_terms( $post_id, 'category', [ 'fields' => 'names' ] );
}
$cat_name = ! empty( $categories ) && ! is_wp_error( $categories ) ? reset( $categories ) : __( 'AI EVENT', 'aikairali-portal' );

// Robust Image Resolver
$image_url = '';
if ( has_post_thumbnail( $post_id ) ) {
	$image_url = get_the_post_thumbnail_url( $post_id, 'large' );
}

if ( empty( $image_url ) && function_exists( 'get_field' ) ) {
	$banner = get_field( 'event_banner', $post_id );
	if ( empty( $banner ) ) {
		$banner = get_field( 'image', $post_id );
	}
	if ( empty( $banner ) ) {
		$banner = get_field( 'upload_image', $post_id );
	}

	if ( is_array( $banner ) ) {
		$image_url = isset( $banner['sizes']['large'] ) ? $banner['sizes']['large'] : ( isset( $banner['url'] ) ? $banner['url'] : '' );
	} elseif ( is_numeric( $banner ) ) {
		$image_url = wp_get_attachment_image_url( $banner, 'large' );
	} elseif ( is_string( $banner ) && ! empty( $banner ) ) {
		$image_url = $banner;
	}
}

if ( empty( $image_url ) ) {
	$image_url = 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800&auto=format&fit=crop';
}
?>

<div class="aik-hero-card">
	<a href="<?php echo esc_url( $permalink ); ?>" class="aik-hero-thumb-link" title="<?php echo esc_attr( $title ); ?>">
		<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="aik-hero-thumb" loading="lazy" />
	</a>
	<div class="aik-hero-content">
		<h3 class="aik-hero-title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
		</h3>
		<div class="aik-hero-cat">
			<?php echo esc_html( mb_strtoupper( $cat_name, 'UTF-8' ) ); ?>
		</div>
	</div>
</div>
