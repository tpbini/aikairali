<?php
/**
 * Template part for rendering an AI Tool list row item.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Templates/Parts
 * @since      1.0.0
 */

$post_id   = get_the_ID();
$permalink = get_permalink( $post_id );
$title     = get_the_title( $post_id );

// Get Category Name (tool-category or fallback to post category)
$categories = wp_get_post_terms( $post_id, 'tool-category', [ 'fields' => 'names' ] );
if ( is_wp_error( $categories ) || empty( $categories ) ) {
	$categories = wp_get_post_terms( $post_id, 'category', [ 'fields' => 'names' ] );
}
$cat_name = ! empty( $categories ) && ! is_wp_error( $categories ) ? reset( $categories ) : __( 'AI TOOL', 'aikairali-portal' );

// Get Image URL
if ( has_post_thumbnail( $post_id ) ) {
	$image_url = get_the_post_thumbnail_url( $post_id, 'medium' );
} else {
	$logo = get_field( 'tool_logo', $post_id );
	if ( is_array( $logo ) && isset( $logo['url'] ) ) {
		$image_url = $logo['url'];
	} elseif ( is_string( $logo ) && ! empty( $logo ) ) {
		$image_url = $logo;
	} else {
		$image_url = class_exists( '\\AIKairali\\Portal\\Core\\Helpers' ) 
			? \AIKairali\Portal\Core\Helpers::get_image_url( $post_id )
			: plugin_dir_url( __FILE__ ) . '../../assets/images/fallback.png';
	}
}
?>

<div class="aik-row-item">
	<a href="<?php echo esc_url( $permalink ); ?>" class="aik-row-thumb-link" title="<?php echo esc_attr( $title ); ?>">
		<img src="<?php echo esc_url( $image_url ); ?>" alt="<?php echo esc_attr( $title ); ?>" class="aik-row-thumb" loading="lazy" />
	</a>
	<div class="aik-row-content">
		<h4 class="aik-row-title">
			<a href="<?php echo esc_url( $permalink ); ?>"><?php echo esc_html( $title ); ?></a>
		</h4>
		<div class="aik-row-cat">
			<?php echo esc_html( mb_strtoupper( $cat_name, 'UTF-8' ) ); ?>
		</div>
	</div>
</div>
