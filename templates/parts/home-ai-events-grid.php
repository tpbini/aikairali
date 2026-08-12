<?php
/**
 * Main 3-column responsive layout template part for AI Events, AI Prompts, and AI Models.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Templates/Parts
 * @since      1.0.0
 */

$events_limit  = isset( $args['events_limit'] ) ? intval( $args['events_limit'] ) : 1;
$prompts_limit = isset( $args['prompts_limit'] ) ? intval( $args['prompts_limit'] ) : 4;
$models_limit  = isset( $args['models_limit'] ) ? intval( $args['models_limit'] ) : 3;

// Query AI Events
$events_query = new \WP_Query( [
	'post_type'      => 'ai-events',
	'posts_per_page' => $events_limit,
	'post_status'    => 'publish',
] );

// Query AI Prompts
$prompts_query = new \WP_Query( [
	'post_type'      => 'ai-prompts',
	'posts_per_page' => $prompts_limit,
	'post_status'    => 'publish',
] );

// Query AI Models
$models_query = new \WP_Query( [
	'post_type'      => 'ai-models',
	'posts_per_page' => $models_limit,
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

<style>
	@import url('https://fonts.googleapis.com/css2?family=Anek+Malayalam:wght@400;500;600;700;800&family=Archivo:wght@500;600;700;800&display=swap');

	.aik-portal-home-grid {
		width: 100%;
		max-width: 1240px;
		margin: 0 auto;
		padding: 24px 16px 40px;
		box-sizing: border-box;
		font-family: "Anek Malayalam", sans-serif;
		color: #1a1a1a;
		background: #ffffff;
	}
	.aik-portal-home-grid * { box-sizing: border-box; }
	.aik-portal-container {
		display: grid !important;
		grid-template-columns: 1fr 1.25fr 1fr !important;
		gap: 32px !important;
		align-items: start !important;
	}
	.aik-section-header {
		display: flex;
		align-items: center;
		gap: 8px;
		padding-bottom: 12px;
		margin-bottom: 12px;
		border-bottom: 2px solid #f1f5f9;
	}
	.aik-pill-indicator {
		width: 4px;
		height: 16px;
		background: linear-gradient(180deg, #2563eb 0%, #dc2626 100%);
		border-radius: 2px;
		display: inline-block;
		flex-shrink: 0;
	}
	.aik-section-title {
		font-family: 'Archivo', sans-serif;
		font-size: 15px;
		font-weight: 600;
		letter-spacing: 0.06em;
		text-transform: uppercase;
		color: #0f172a;
		margin: 0;
		line-height: 1;
	}
	.aik-column {
		display: flex;
		flex-direction: column;
		height: 100%;
		min-width: 0 !important;
	}

	/* Hero Card */
	.aik-hero-card {
		display: flex;
		flex-direction: column;
		width: 100%;
	}
	.aik-hero-thumb-link {
		display: block;
		width: 100%;
		aspect-ratio: 4 / 3;
		border-radius: 10px;
		overflow: hidden;
		margin-bottom: 14px;
		background: #f1f5f9;
	}
	.aik-hero-thumb {
		width: 100%;
		height: 100%;
		object-fit: cover;
		display: block;
		transition: transform 0.35s ease;
	}
	.aik-hero-card:hover .aik-hero-thumb { transform: scale(1.04); }
	.aik-hero-title {
		font-family: "Anek Malayalam", sans-serif;
		font-size: 18px;
		font-weight: 800;
		line-height: 1.38;
		margin: 0 0 8px 0;
	}
	.aik-hero-title a {
		color: #0f172a;
		text-decoration: none;
		transition: color 0.2s ease;
	}
	.aik-hero-title a:hover { color: #dc2626; }
	.aik-hero-cat {
		font-family: 'Archivo', sans-serif;
		font-size: 11px;
		font-weight: 700;
		text-transform: uppercase;
		color: #2563eb;
		letter-spacing: 0.05em;
	}

	/* 2x2 Prompts Grid - Explicit 2 Columns */
	.aik-topimg-grid-2x2 {
		display: grid !important;
		grid-template-columns: repeat(2, 1fr) !important;
		gap: 20px 16px !important;
		width: 100% !important;
		box-sizing: border-box !important;
	}
	.aik-topimg-card {
		display: flex !important;
		flex-direction: column !important;
		width: 100% !important;
		min-width: 0 !important;
		box-sizing: border-box !important;
	}
	.aik-topimg-thumb-link {
		display: block !important;
		width: 100% !important;
		aspect-ratio: 16 / 10 !important;
		border-radius: 8px !important;
		overflow: hidden !important;
		margin-bottom: 10px !important;
		background: #f1f5f9 !important;
	}
	.aik-topimg-thumb {
		width: 100% !important;
		height: 100% !important;
		object-fit: cover !important;
		display: block !important;
		transition: transform 0.3s ease !important;
	}
	.aik-topimg-card:hover .aik-topimg-thumb { transform: scale(1.05) !important; }
	.aik-topimg-title {
		font-family: "Anek Malayalam", sans-serif !important;
		font-size: 14px !important;
		font-weight: 700 !important;
		line-height: 1.38 !important;
		margin: 0 0 6px 0 !important;
		word-break: break-word !important;
	}
	.aik-topimg-title a {
		color: #0f172a !important;
		text-decoration: none !important;
		display: -webkit-box !important;
		-webkit-line-clamp: 3 !important;
		-webkit-box-orient: vertical !important;
		overflow: hidden !important;
		transition: color 0.2s ease !important;
	}
	.aik-topimg-title a:hover { color: #dc2626 !important; }
	.aik-topimg-cat {
		font-family: 'Archivo', sans-serif !important;
		font-size: 11px !important;
		font-weight: 700 !important;
		text-transform: uppercase !important;
		color: #2563eb !important;
		letter-spacing: 0.05em !important;
	}

	/* Row List Layout */
	.aik-list-wrap {
		display: flex;
		flex-direction: column;
		gap: 0;
		flex-grow: 1;
	}
	.aik-row-item {
		display: flex;
		gap: 14px;
		padding: 14px 0;
		border-bottom: 1px solid #e2e8f0;
		align-items: flex-start;
	}
	.aik-row-item:last-child { border-bottom: none; }
	.aik-row-thumb-link {
		display: block;
		flex-shrink: 0;
		width: 76px;
		height: 76px;
		border-radius: 8px;
		overflow: hidden;
		background: #f1f5f9;
	}
	.aik-row-thumb {
		width: 100%;
		height: 100%;
		object-fit: cover;
		display: block;
		border-radius: 8px;
		transition: transform 0.3s ease;
	}
	.aik-row-item:hover .aik-row-thumb { transform: scale(1.05); }
	.aik-row-content {
		display: flex;
		flex-direction: column;
		justify-content: space-between;
		flex-grow: 1;
		min-height: 76px;
		min-width: 0;
	}
	.aik-row-title {
		font-family: "Anek Malayalam", sans-serif;
		font-size: 15px;
		font-weight: 700;
		line-height: 1.38;
		margin: 0 0 8px 0;
		word-break: break-word;
	}
	.aik-row-title a {
		color: #0f172a;
		text-decoration: none;
		transition: color 0.2s ease;
		display: -webkit-box;
		-webkit-line-clamp: 3;
		-webkit-box-orient: vertical;
		overflow: hidden;
	}
	.aik-row-title a:hover { color: #dc2626; }
	.aik-row-cat {
		font-family: 'Archivo', sans-serif;
		font-size: 11px;
		font-weight: 700;
		text-transform: uppercase;
		color: #2563eb;
		letter-spacing: 0.05em;
		margin-top: auto;
	}

	.aik-column-footer {
		margin-top: 18px;
		padding-top: 8px;
		text-align: right;
	}
	.aik-more-link {
		font-family: 'Archivo', sans-serif;
		font-size: 12px;
		font-weight: 800;
		text-transform: uppercase;
		color: #0f172a;
		text-decoration: none;
		letter-spacing: 0.06em;
		transition: color 0.2s ease;
		display: inline-flex;
		align-items: center;
		gap: 4px;
	}
	.aik-more-link:hover { color: #dc2626; }
	.aik-more-link .aik-arrow { color: #dc2626; font-weight: 900; }
	.aik-no-posts { color: #94a3b8; font-size: 13px; padding: 12px 0; font-style: italic; }

	@media (max-width: 1024px) {
		.aik-portal-container {
			grid-template-columns: 1fr !important;
			gap: 36px !important;
		}
		.aik-column {
			padding-bottom: 16px;
			border-bottom: 1px solid #e2e8f0;
		}
		.aik-column:last-child { border-bottom: none; }
	}
	@media (max-width: 480px) {
		.aik-topimg-grid-2x2 { gap: 10px !important; }
		.aik-topimg-title { font-size: 12px !important; }
		.aik-hero-title { font-size: 16px; }
		.aik-row-thumb-link { width: 64px; height: 64px; }
		.aik-row-content { min-height: 64px; }
	}
</style>

<div class="aik-portal-home-grid aik-epm-grid">
	<div class="aik-portal-container">
		
		<!-- COLUMN 1: AI EVENTS (Hero Layout) -->
		<div class="aik-column aik-col-events">
			<div class="aik-section-header">
				<span class="aik-pill-indicator"></span>
				<h3 class="aik-section-title"><?php esc_html_e( 'AI EVENTS', 'aikairali-portal' ); ?></h3>
			</div>

			<div class="aik-hero-wrap">
				<?php
				if ( $events_query->have_posts() ) :
					while ( $events_query->have_posts() ) : $events_query->the_post();
						$locate_part( 'card-ai-events-hero.php' );
					endwhile;
					wp_reset_postdata();
				else :
					echo '<p class="aik-no-posts">' . esc_html__( 'No AI Events found.', 'aikairali-portal' ) . '</p>';
				endif;
				?>
			</div>

			<?php $events_archive = get_post_type_archive_link( 'ai-events' ) ?: home_url( '/ai-events/' ); ?>
			<div class="aik-column-footer">
				<a href="<?php echo esc_url( $events_archive ); ?>" class="aik-more-link">
					<?php esc_html_e( 'MORE FROM AI EVENTS', 'aikairali-portal' ); ?> <span class="aik-arrow">&raquo;&raquo;</span>
				</a>
			</div>
		</div>

		<!-- COLUMN 2: AI PROMPTS (Top Image 2x2 Grid Layout) -->
		<div class="aik-column aik-col-prompts">
			<div class="aik-section-header">
				<span class="aik-pill-indicator"></span>
				<h3 class="aik-section-title"><?php esc_html_e( 'AI PROMPTS', 'aikairali-portal' ); ?></h3>
			</div>

			<div class="aik-topimg-grid-2x2">
				<?php
				if ( $prompts_query->have_posts() ) :
					while ( $prompts_query->have_posts() ) : $prompts_query->the_post();
						$locate_part( 'card-ai-prompts-grid.php' );
					endwhile;
					wp_reset_postdata();
				else :
					echo '<p class="aik-no-posts">' . esc_html__( 'No AI Prompts found.', 'aikairali-portal' ) . '</p>';
				endif;
				?>
			</div>

			<?php $prompts_archive = get_post_type_archive_link( 'ai-prompts' ) ?: home_url( '/ai-prompts/' ); ?>
			<div class="aik-column-footer">
				<a href="<?php echo esc_url( $prompts_archive ); ?>" class="aik-more-link">
					<?php esc_html_e( 'MORE FROM AI PROMPTS', 'aikairali-portal' ); ?> <span class="aik-arrow">&raquo;&raquo;</span>
				</a>
			</div>
		</div>

		<!-- COLUMN 3: AI MODELS (Row List Layout) -->
		<div class="aik-column aik-col-models">
			<div class="aik-section-header">
				<span class="aik-pill-indicator"></span>
				<h3 class="aik-section-title"><?php esc_html_e( 'AI MODELS', 'aikairali-portal' ); ?></h3>
			</div>

			<div class="aik-list-wrap">
				<?php
				if ( $models_query->have_posts() ) :
					while ( $models_query->have_posts() ) : $models_query->the_post();
						$locate_part( 'card-ai-models-row.php' );
					endwhile;
					wp_reset_postdata();
				else :
					echo '<p class="aik-no-posts">' . esc_html__( 'No AI Models found.', 'aikairali-portal' ) . '</p>';
				endif;
				?>
			</div>

			<?php $models_archive = get_post_type_archive_link( 'ai-models' ) ?: home_url( '/ai-models/' ); ?>
			<div class="aik-column-footer">
				<a href="<?php echo esc_url( $models_archive ); ?>" class="aik-more-link">
					<?php esc_html_e( 'MORE FROM AI MODELS', 'aikairali-portal' ); ?> <span class="aik-arrow">&raquo;&raquo;</span>
				</a>
			</div>
		</div>

	</div>
</div>
