<?php
/**
 * AI Tutorial Videos Horizontal Carousel Template Part.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Templates/Parts
 * @since      1.0.0
 */

$limit = isset( $args['limit'] ) ? intval( $args['limit'] ) : 10;

// Query AI Videos (up to 10)
$videos_query = new \WP_Query( [
	'post_type'      => 'ai-videos',
	'posts_per_page' => $limit,
	'post_status'    => 'publish',
] );


$unique_id = uniqid( 'aik-videos-swiper-' );

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

<!-- Swiper CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<style>
	@import url('https://fonts.googleapis.com/css2?family=Anek+Malayalam:wght@400;500;600;700;800&family=Archivo:wght@500;600;700;800&display=swap');

	.aik-videos-carousel-wrap {
		width: 100%;
		max-width: 1240px;
		margin: 0 auto;
		padding: 30px 16px 40px;
		box-sizing: border-box;
		font-family: "Anek Malayalam", sans-serif;
		background: #ffffff;
	}
	.aik-videos-carousel-wrap * { box-sizing: border-box; }

	.aik-videos-header {
		display: flex;
		justify-content: space-between;
		align-items: center;
		margin-bottom: 20px;
		border-bottom: 2px solid #f1f5f9;
		padding-bottom: 12px;
	}

	.aik-videos-header-left {
		display: flex;
		align-items: center;
		gap: 10px;
	}

	.aik-videos-pill {
		width: 4px;
		height: 18px;
		background: linear-gradient(180deg, #2563eb 0%, #dc2626 100%);
		border-radius: 2px;
		display: inline-block;
	}

	.aik-videos-title {
		font-family: 'Archivo', sans-serif;
		font-size: 17px;
		font-weight: 800;
		letter-spacing: 0.05em;
		text-transform: uppercase;
		color: #2563eb;
		margin: 0;
	}

	.aik-videos-more-link {
		font-family: 'Archivo', sans-serif;
		font-size: 13px;
		font-weight: 800;
		text-transform: uppercase;
		color: #2563eb;
		text-decoration: none;
		letter-spacing: 0.05em;
		display: inline-flex;
		align-items: center;
		gap: 4px;
		transition: color 0.2s ease;
	}
	.aik-videos-more-link:hover { color: #dc2626; }
	.aik-videos-more-link .aik-arrow { color: #dc2626; font-weight: 900; }

	/* Swiper Container */
	.aik-videos-swiper {
		width: 100%;
		padding: 5px 0 35px 0 !important;
		overflow: hidden !important;
	}

	/* Full Bleed Portrait Video Card */
	.aik-video-portrait-card {
		position: relative !important;
		width: 100% !important;
		aspect-ratio: 9 / 15 !important;
		border-radius: 12px !important;
		overflow: hidden !important;
		background: #0f172a !important;
		box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
		transition: transform 0.3s ease, box-shadow 0.3s ease !important;
		box-sizing: border-box !important;
	}
	.aik-video-portrait-card:hover {
		transform: translateY(-5px) !important;
		box-shadow: 0 10px 25px rgba(0,0,0,0.2) !important;
	}

	.aik-vp-link {
		display: block !important;
		width: 100% !important;
		height: 100% !important;
		position: relative !important;
		text-decoration: none !important;
		color: #ffffff !important;
	}

	/* Force full-bleed cover image filling the whole card height & width */
	.aik-vp-img {
		position: absolute !important;
		inset: 0 !important;
		width: 100% !important;
		height: 100% !important;
		object-fit: cover !important;
		display: block !important;
		z-index: 1 !important;
		transition: transform 0.4s ease !important;
	}
	.aik-video-portrait-card:hover .aik-vp-img {
		transform: scale(1.06) !important;
	}

	.aik-vp-play-badge {
		position: absolute !important;
		top: 10px !important;
		right: 10px !important;
		width: 28px !important;
		height: 28px !important;
		background: rgba(15, 23, 42, 0.65) !important;
		backdrop-filter: blur(4px) !important;
		border-radius: 6px !important;
		display: flex !important;
		align-items: center !important;
		justify-content: center !important;
		color: #ffffff !important;
		z-index: 3 !important;
		transition: background 0.25s ease !important;
	}
	.aik-video-portrait-card:hover .aik-vp-play-badge {
		background: #dc2626 !important;
	}

	.aik-vp-gradient {
		position: absolute !important;
		inset: 0 !important;
		background: linear-gradient(180deg, rgba(0,0,0,0) 30%, rgba(0,0,0,0.88) 100%) !important;
		display: flex !important;
		flex-direction: column !important;
		justify-content: flex-end !important;
		padding: 14px !important;
		box-sizing: border-box !important;
		z-index: 2 !important;
	}

	.aik-vp-title {
		font-family: "Anek Malayalam", sans-serif !important;
		font-size: 13.5px !important;
		font-weight: 700 !important;
		line-height: 1.35 !important;
		color: #ffffff !important;
		margin: 0 0 6px 0 !important;
		display: -webkit-box !important;
		-webkit-line-clamp: 3 !important;
		-webkit-box-orient: vertical !important;
		overflow: hidden !important;
		text-shadow: 0 1px 3px rgba(0,0,0,0.8) !important;
	}

	.aik-vp-cat {
		font-family: 'Archivo', sans-serif !important;
		font-size: 10px !important;
		font-weight: 700 !important;
		letter-spacing: 0.06em !important;
		text-transform: uppercase !important;
		color: #cbd5e1 !important;
		opacity: 0.9 !important;
	}

	/* Pagination Dots matching reference design */
	.aik-videos-swiper .swiper-pagination {
		bottom: 0 !important;
		display: flex !important;
		justify-content: center !important;
		align-items: center !important;
		gap: 6px !important;
	}
	.aik-videos-swiper .swiper-pagination-bullet {
		width: 8px !important;
		height: 8px !important;
		background: #64748b !important;
		opacity: 0.5 !important;
		border-radius: 50% !important;
		margin: 0 !important;
		transition: all 0.3s ease !important;
	}
	.aik-videos-swiper .swiper-pagination-bullet-active {
		background: #00a8ff !important;
		opacity: 1 !important;
		transform: scale(1.2) !important;
	}

	/* Fallback Grid when Swiper is uninitialized */
	.aik-videos-swiper:not(.swiper-initialized) .swiper-wrapper {
		display: grid !important;
		grid-template-columns: repeat(6, 1fr) !important;
		gap: 16px !important;
	}
	@media (max-width: 1024px) {
		.aik-videos-swiper:not(.swiper-initialized) .swiper-wrapper {
			grid-template-columns: repeat(3, 1fr) !important;
		}
	}
	@media (max-width: 576px) {
		.aik-videos-swiper:not(.swiper-initialized) .swiper-wrapper {
			grid-template-columns: repeat(2, 1fr) !important;
		}
	}
</style>

<div class="aik-videos-carousel-wrap">
	<!-- Header -->
	<div class="aik-videos-header">
		<div class="aik-videos-header-left">
			<span class="aik-videos-pill"></span>
			<h3 class="aik-videos-title"><?php esc_html_e( 'AI TUTORIAL VIDEOS', 'aikairali-portal' ); ?></h3>
		</div>
		<?php $archive_link = get_post_type_archive_link( 'ai-videos' ) ?: home_url( '/ai-videos/' ); ?>
		<a href="<?php echo esc_url( $archive_link ); ?>" class="aik-videos-more-link">
			<?php esc_html_e( 'MORE VIDEOS', 'aikairali-portal' ); ?> <span class="aik-arrow">&raquo;&raquo;</span>
		</a>
	</div>

	<!-- Swiper Slider -->
	<div class="swiper aik-videos-swiper <?php echo esc_attr( $unique_id ); ?>">
		<div class="swiper-wrapper">
			<?php
			if ( $videos_query->have_posts() ) :
				while ( $videos_query->have_posts() ) : $videos_query->the_post();
					echo '<div class="swiper-slide">';
					$locate_part( 'card-ai-video-portrait.php' );
					echo '</div>';
				endwhile;
				wp_reset_postdata();
			else :
				echo '<p style="color:#94a3b8; padding:20px 0;">' . esc_html__( 'No videos found.', 'aikairali-portal' ) . '</p>';
			endif;
			?>
		</div>
		<div class="swiper-pagination"></div>
	</div>
</div>

<!-- Swiper JS -->
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
(function() {
	function initVideosSwiper() {
		var container = document.querySelector('.<?php echo esc_js( $unique_id ); ?>');
		if (!container || typeof Swiper === 'undefined') return;

		if (container.swiper) {
			try { container.swiper.destroy(true, true); } catch(e) {}
		}

		new Swiper(container, {
			slidesPerView: 2,
			spaceBetween: 12,
			grabCursor: true,
			loop: true,
			pagination: {
				el: '.<?php echo esc_js( $unique_id ); ?> .swiper-pagination',
				clickable: true,
			},
			breakpoints: {
				480: { slidesPerView: 3, spaceBetween: 14 },
				768: { slidesPerView: 4, spaceBetween: 16 },
				992: { slidesPerView: 5, spaceBetween: 16 },
				1200: { slidesPerView: 6, spaceBetween: 18 }
			}
		});
	}

	if (document.readyState === 'complete' || document.readyState === 'interactive') {
		initVideosSwiper();
	} else {
		document.addEventListener('DOMContentLoaded', initVideosSwiper);
	}
	setTimeout(initVideosSwiper, 300);
	setTimeout(initVideosSwiper, 800);
	if (window.elementorFrontend && window.elementorFrontend.hooks) {
		window.elementorFrontend.hooks.addAction('frontend/element_ready/widget', initVideosSwiper);
	}
})();
</script>
