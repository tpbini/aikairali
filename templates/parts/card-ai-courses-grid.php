<?php
/**
 * Template part for rendering an AI Course 2x2 grid card item.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Templates/Parts
 * @since      1.0.0
 */

$post_id   = get_the_ID();
$permalink = get_permalink( $post_id );
$title     = get_the_title( $post_id );

// Get Category Name (course-category or fallback to post category)
$categories = wp_get_post_terms( $post_id, 'course-category', [ 'fields' => 'names' ] );
if ( is_wp_error( $categories ) || empty( $categories ) ) {
	$categories = wp_get_post_terms( $post_id, 'category', [ 'fields' => 'names' ] );
}
$cat_name = ! empty( $categories ) && ! is_wp_error( $categories ) ? reset( $categories ) : __( 'AI COURSE', 'aikairali-portal' );

// Check if course has video content or lesson video
$has_video = get_field( 'has_video', $post_id ) || get_field( 'video_url', $post_id );

// Get Image URL
if ( has_post_thumbnail( $post_id ) ) {
	$image_url = get_the_post_thumbnail_url( $post_id, 'medium_large' );
} else {
	$banner = get_field( 'course_thumbnail', $post_id );
	if ( is_array( $banner ) && isset( $banner['url'] ) ) {
		$image_url = $banner['url'];
	} elseif ( is_string( $banner ) && ! empty( $banner ) ) {
		$image_url = $banner;
	} else {
		$image_url = class_exists( '\\AIKairali\\Portal\\Core\\Helpers' ) 
			? \AIKairali\Portal\Core\Helpers::get_image_url( $post_id )
			: plugin_dir_url( __FILE__ ) . '../../assets/images/fallback.png';
	}
}
?>

<div class="aik-card-item">
	<a href="<?php echo esc_url( $permalink ); ?>" class="aik-card-link-mask" aria-label="<?php echo esc_attr( $title ); ?>"></a>
	<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="aik-card-bg" loading="lazy" />
	
	<div class="aik-card-overlay"></div>
	
	<div class="aik-card-content">
		<h4 class="aik-card-title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
		</h4>
		<div class="aik-card-meta">
			<span class="aik-card-cat"><?php echo esc_html( mb_strtoupper( $cat_name, 'UTF-8' ) ); ?></span>
			<?php if ( $has_video ) : ?>
				<span class="aik-card-video-icon" title="<?php esc_attr_e( 'Video Course', 'aikairali-portal' ); ?>">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="currentColor"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
				</span>
			<?php endif; ?>
		</div>
	</div>
</div>
