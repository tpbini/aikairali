<?php
/**
 * Main 3-column responsive layout template part for AI Tools, AI Courses, and AI Books.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Templates/Parts
 * @since      1.0.0
 */

$tools_limit   = isset( $args['tools_limit'] ) ? intval( $args['tools_limit'] ) : 4;
$courses_limit = isset( $args['courses_limit'] ) ? intval( $args['courses_limit'] ) : 4;
$books_limit   = isset( $args['books_limit'] ) ? intval( $args['books_limit'] ) : 4;

// Query AI Tools
$tools_query = new \WP_Query( [
	'post_type'      => 'ai-tools',
	'posts_per_page' => $tools_limit,
	'post_status'    => 'publish',
] );

// Query AI Courses
$courses_query = new \WP_Query( [
	'post_type'      => 'ai-courses',
	'posts_per_page' => $courses_limit,
	'post_status'    => 'publish',
] );

// Query AI Books
$books_query = new \WP_Query( [
	'post_type'      => 'ai-books',
	'posts_per_page' => $books_limit,
	'post_status'    => 'publish',
] );


// Helper to locate template part inside plugin or theme
$locate_part = function( $file, $args = [] ) {
	$theme_file = locate_template( [ "aikairali-portal/parts/{$file}", "aikairali-portal/{$file}" ] );
	$plugin_file = __DIR__ . '/' . $file;
	$target = $theme_file ?: ( file_exists( $plugin_file ) ? $plugin_file : false );
	if ( $target ) {
		if ( ! empty( $args ) ) {
			extract( $args );
		}
		include $target;
	}
};
?>

<div class="aik-portal-home-grid">
	<div class="aik-portal-container">
		
		<!-- COLUMN 1: AI TOOLS -->
		<div class="aik-column aik-col-tools">
			<div class="aik-section-header">
				<span class="aik-pill-indicator"></span>
				<h3 class="aik-section-title"><?php esc_html_e( 'AI TOOLS', 'aikairali-portal' ); ?></h3>
			</div>

			<div class="aik-list-wrap">
				<?php
				if ( $tools_query->have_posts() ) :
					while ( $tools_query->have_posts() ) : $tools_query->the_post();
						$locate_part( 'card-ai-tools-row.php' );
					endwhile;
					wp_reset_postdata();
				else :
					echo '<p class="aik-no-posts">' . esc_html__( 'No AI Tools found.', 'aikairali-portal' ) . '</p>';
				endif;
				?>
			</div>

			<?php
			$tools_archive = get_post_type_archive_link( 'ai-tools' ) ?: home_url( '/ai-tools/' );
			?>
			<div class="aik-column-footer">
				<a href="<?php echo esc_url( $tools_archive ); ?>" class="aik-more-link">
					<?php esc_html_e( 'MORE FROM AI TOOLS', 'aikairali-portal' ); ?> <span class="aik-arrow">&raquo;&raquo;</span>
				</a>
			</div>
		</div>

		<!-- COLUMN 2: AI COURSES -->
		<div class="aik-column aik-col-courses">
			<div class="aik-section-header">
				<span class="aik-pill-indicator"></span>
				<h3 class="aik-section-title"><?php esc_html_e( 'AI COURSES', 'aikairali-portal' ); ?></h3>
			</div>

			<div class="aik-grid-2x2">
				<?php
				if ( $courses_query->have_posts() ) :
					while ( $courses_query->have_posts() ) : $courses_query->the_post();
						$locate_part( 'card-ai-courses-grid.php' );
					endwhile;
					wp_reset_postdata();
				else :
					echo '<p class="aik-no-posts">' . esc_html__( 'No AI Courses found.', 'aikairali-portal' ) . '</p>';
				endif;
				?>
			</div>

			<?php
			$courses_archive = get_post_type_archive_link( 'ai-courses' ) ?: home_url( '/ai-courses/' );
			?>
			<div class="aik-column-footer">
				<a href="<?php echo esc_url( $courses_archive ); ?>" class="aik-more-link">
					<?php esc_html_e( 'MORE FROM AI COURSES', 'aikairali-portal' ); ?> <span class="aik-arrow">&raquo;&raquo;</span>
				</a>
			</div>
		</div>

		<!-- COLUMN 3: AI BOOKS -->
		<div class="aik-column aik-col-books">
			<div class="aik-section-header">
				<span class="aik-pill-indicator"></span>
				<h3 class="aik-section-title"><?php esc_html_e( 'AI BOOKS', 'aikairali-portal' ); ?></h3>
			</div>

			<div class="aik-list-wrap">
				<?php
				if ( $books_query->have_posts() ) :
					while ( $books_query->have_posts() ) : $books_query->the_post();
						$locate_part( 'card-ai-books-row.php' );
					endwhile;
					wp_reset_postdata();
				else :
					echo '<p class="aik-no-posts">' . esc_html__( 'No AI Books found.', 'aikairali-portal' ) . '</p>';
				endif;
				?>
			</div>

			<?php
			$books_archive = get_post_type_archive_link( 'ai-books' ) ?: home_url( '/ai-books/' );
			?>
			<div class="aik-column-footer">
				<a href="<?php echo esc_url( $books_archive ); ?>" class="aik-more-link">
					<?php esc_html_e( 'MORE FROM AI BOOKS', 'aikairali-portal' ); ?> <span class="aik-arrow">&raquo;&raquo;</span>
				</a>
			</div>
		</div>

	</div>
</div>
