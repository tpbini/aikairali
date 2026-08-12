<?php
/**
 * Template part for rendering an AI Prompt Top-Image Card (Column 2 2x2 Grid).
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Templates/Parts
 * @since      1.0.0
 */

$post_id   = get_the_ID();
$permalink = get_permalink( $post_id );
$title     = get_the_title( $post_id );

// Get Category Name (prompt-category or fallback to post category)
$categories = wp_get_post_terms( $post_id, 'prompt-category', [ 'fields' => 'names' ] );
if ( is_wp_error( $categories ) || empty( $categories ) ) {
	$categories = wp_get_post_terms( $post_id, 'category', [ 'fields' => 'names' ] );
}
$cat_name = ! empty( $categories ) && ! is_wp_error( $categories ) ? reset( $categories ) : __( 'AI PROMPTS', 'aikairali-portal' );

// Robust Image Resolver
$image_url = '';
if ( has_post_thumbnail( $post_id ) ) {
	$image_url = get_the_post_thumbnail_url( $post_id, 'medium_large' );
}

if ( empty( $image_url ) && function_exists( 'get_field' ) ) {
	$prompt_img = get_field( 'prompt_image', $post_id );
	if ( empty( $prompt_img ) ) {
		$prompt_img = get_field( 'image', $post_id );
	}
	if ( empty( $prompt_img ) ) {
		$prompt_img = get_field( 'upload_image', $post_id );
	}

	if ( is_array( $prompt_img ) ) {
		$image_url = isset( $prompt_img['sizes']['medium_large'] ) ? $prompt_img['sizes']['medium_large'] : ( isset( $prompt_img['url'] ) ? $prompt_img['url'] : '' );
	} elseif ( is_numeric( $prompt_img ) ) {
		$image_url = wp_get_attachment_image_url( $prompt_img, 'medium_large' );
	} elseif ( is_string( $prompt_img ) && ! empty( $prompt_img ) ) {
		$image_url = $prompt_img;
	}
}

if ( empty( $image_url ) ) {
	$image_url = 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=600&auto=format&fit=crop';
}
?>

<div class="aik-topimg-card">
	<a href="<?php echo esc_url( $permalink ); ?>" class="aik-topimg-thumb-link" title="<?php echo esc_attr( $title ); ?>">
		<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="aik-topimg-thumb" loading="lazy" />
	</a>
	<div class="aik-topimg-content">
		<h4 class="aik-topimg-title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
		</h4>
		<div class="aik-topimg-cat">
			<?php echo esc_html( mb_strtoupper( $cat_name, 'UTF-8' ) ); ?>
		</div>
	</div>
</div>
