<?php
/**
 * Template part for rendering a Job Card.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Templates/Parts
 * @since      1.0.0
 */

$post_id   = get_the_ID();
$comp_name = get_field( 'company_name', $post_id );
$comp_logo = get_field( 'company_logo', $post_id );
$work_mode = get_field( 'work_mode', $post_id );
$salary    = get_field( 'salary', $post_id );
$currency  = get_field( 'currency', $post_id );
$deadline  = get_field( 'deadline', $post_id );
$state     = get_field( 'state', $post_id );
$featured  = get_field( 'featured_job', $post_id );
$urgent    = get_field( 'urgent_hiring', $post_id );

// Taxonomies.
$countries = wp_get_post_terms( $post_id, 'job-country', [ 'fields' => 'names' ] );
$cities    = wp_get_post_terms( $post_id, 'job-city', [ 'fields' => 'names' ] );
$location  = '';
if ( ! empty( $cities ) ) {
	$location .= reset( $cities );
}
if ( $state ) {
	$location .= ( $location ? ', ' : '' ) . $state;
}
if ( ! empty( $countries ) ) {
	$location .= ( $location ? ', ' : '' ) . reset( $countries );
}

$classes = 'aikairali-job-card';
if ( $featured ) {
	$classes .= ' featured-card';
}
if ( $urgent ) {
	$classes .= ' urgent-card';
}
?>
<div class="<?php echo esc_attr( $classes ); ?>" style="border: 1px solid #e2e8f0; border-radius: 8px; padding: 20px; background: #fff; position: relative; transition: all 0.3s; margin-bottom: 20px; box-shadow: 0 1px 3px rgba(0,0,0,0.05);">
	<?php if ( $featured ) : ?>
		<div class="featured-badge" style="position: absolute; top: 15px; right: 15px; background: #ffb900; color: #fff; font-size: 11px; font-weight: bold; padding: 3px 8px; border-radius: 3px; z-index: 1;">
			<?php esc_html_e( 'FEATURED', 'aikairali-portal' ); ?>
		</div>
	<?php endif; ?>

	<div style="display: flex; align-items: flex-start; gap: 15px;">
		<?php if ( $comp_logo ) : ?>
			<div class="company-logo-wrap" style="flex-shrink: 0; width: 60px; height: 60px; border: 1px solid #edf2f7; border-radius: 6px; overflow: hidden; display: flex; align-items: center; justify-content: center; background: #fff;">
				<img src="<?php echo esc_url( $comp_logo ); ?>" alt="<?php echo esc_attr( $comp_name ); ?>" style="max-width: 100%; max-height: 100%; object-fit: contain;" />
			</div>
		<?php else : ?>
			<div class="company-logo-wrap" style="flex-shrink: 0; width: 60px; height: 60px; border: 1px solid #edf2f7; border-radius: 6px; background: #f7fafc; display: flex; align-items: center; justify-content: center; color: #a0aec0; font-weight: bold;">
				<?php echo esc_html( substr( $comp_name ?: 'J', 0, 1 ) ); ?>
			</div>
		<?php endif; ?>

		<div style="flex-grow: 1;">
			<h3 style="margin: 0 0 5px 0; font-size: 18px; line-height: 1.3;">
				<a href="<?php the_permalink(); ?>" style="color: #2d3748; text-decoration: none; font-weight: 700; transition: color 0.2s;">
					<?php the_title(); ?>
				</a>
			</h3>
			
			<div style="display: flex; flex-wrap: wrap; align-items: center; gap: 10px; margin-bottom: 10px; color: #718096; font-size: 14px;">
				<span class="company-name" style="font-weight: 600; color: #4a5568;"><?php echo esc_html( $comp_name ?: __( 'Unknown Company', 'aikairali-portal' ) ); ?></span>
				<?php if ( $location ) : ?>
					<span>•</span>
					<span class="job-location"><span class="dashicons dashicons-location" style="font-size: 16px; width: 16px; height: 16px; vertical-align: text-bottom;"></span> <?php echo esc_html( $location ); ?></span>
				<?php endif; ?>
			</div>

			<div style="display: flex; flex-wrap: wrap; gap: 8px; margin-bottom: 12px;">
				<?php if ( $work_mode ) : ?>
					<span class="badge-work-mode" style="font-size: 12px; background: #ebf8ff; color: #2b6cb0; padding: 2px 8px; border-radius: 4px; font-weight: 600;"><?php echo esc_html( $work_mode ); ?></span>
				<?php endif; ?>
				
				<?php
				$emp_types = wp_get_post_terms( $post_id, 'employment-type' );
				foreach ( $emp_types as $type ) {
					printf( '<span class="badge-emp-type" style="font-size:12px; background:#f0fff4; color:#22543d; padding:2px 8px; border-radius:4px; font-weight:600;">%s</span>', esc_html( $type->name ) );
				}
				
				if ( $urgent ) {
					echo '<span class="badge-urgent" style="font-size:12px; background:#fff5f5; color:#c53030; padding:2px 8px; border-radius:4px; font-weight:600;">' . esc_html__( 'Urgent', 'aikairali-portal' ) . '</span>';
				}
				?>
			</div>
		</div>
	</div>

	<div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #edf2f7; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px;">
		<div style="font-size: 14px; color: #4a5568;">
			<?php if ( $salary ) : ?>
				<strong style="color: #2b6cb0; font-size: 16px;"><?php echo esc_html( "{$currency} {$salary}" ); ?></strong>
			<?php else : ?>
				<span style="color: #a0aec0; font-style: italic;"><?php esc_html_e( 'Salary Negotiable', 'aikairali-portal' ); ?></span>
			<?php endif; ?>
		</div>
		
		<div style="display: flex; align-items: center; gap: 10px;">
			<?php if ( $deadline ) : ?>
				<span style="font-size: 12px; color: #718096; margin-right: 5px;">
					<?php esc_html_e( 'Apply before:', 'aikairali-portal' ); ?> <strong><?php echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $deadline ) ) ); ?></strong>
				</span>
			<?php endif; ?>
			<a href="<?php the_permalink(); ?>" style="background: #2b6cb0; color: #fff; text-decoration: none; padding: 6px 16px; border-radius: 4px; font-weight: 600; font-size: 14px; transition: background 0.2s; display: inline-block;">
				<?php esc_html_e( 'View Job', 'aikairali-portal' ); ?>
			</a>
		</div>
	</div>
</div>
