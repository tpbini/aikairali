<?php
/**
 * Search Results Page Template for AIKairali Portal.
 * Matches the Google-style news search layout with search bar, result metadata,
 * keyword highlighting, right-aligned image thumbnails, and right sidebar ads.
 *
 * @package AIKairali_Portal
 */

get_header();

$search_query  = get_search_query();
$start_time    = microtime( true );

// Query all portal content post types
$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
$args  = [
	's'              => $search_query,
	'post_type'      => [ 'post', 'ai-news', 'ai-tools', 'ai-courses', 'ai-prompts', 'ai-videos', 'ai-books', 'ai-events', 'ai-models', 'ai-glossary', 'jobs', 'tutorial' ],
	'post_status'    => 'publish',
	'posts_per_page' => 10,
	'paged'          => $paged,
];

$search_results = new WP_Query( $args );
$total_found    = $search_results->found_posts;
$elapsed_time   = number_format( microtime( true ) - $start_time, 2 );

// Helper function to highlight search keywords in title and excerpt
if ( ! function_exists( 'aikairali_highlight_search_term' ) ) {
	function aikairali_highlight_search_term( $text, $query ) {
		if ( empty( $query ) || empty( $text ) ) {
			return $text;
		}
		$words = array_filter( explode( ' ', $query ) );
		foreach ( $words as $word ) {
			if ( mb_strlen( $word ) < 2 ) {
				continue;
			}
			$pattern = '/' . preg_quote( $word, '/' ) . '/iu';
			$text    = preg_replace( $pattern, '<strong>$0</strong>', $text );
		}
		return $text;
	}
}
?>

<div class="aikairali-container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px; box-sizing: border-box; font-family: 'Anek Malayalam', system-ui, -apple-system, sans-serif;">

	<!-- Top Breadcrumb -->
	<nav class="search-breadcrumb" style="font-size: 11px; font-weight: 800; color: #64748b; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="color: #64748b; text-decoration: none;">HOME</a> &nbsp;&rsaquo;&nbsp; <span style="color: #0f172a;">SEARCH</span>
	</nav>

	<!-- Page Title -->
	<h1 style="font-size: 32px; font-weight: 700; color: #0f172a; margin: 0 0 24px 0; line-height: 1.2;">
		<?php esc_html_e( 'Search', 'aikairali-portal' ); ?>
	</h1>

	<!-- Main Layout (Search Stream + Right Sidebar) -->
	<div style="display: flex; gap: 45px; align-items: flex-start; flex-wrap: wrap;">

		<!-- LEFT COLUMN: Search Bar & Results Stream -->
		<main style="flex: 1; min-width: 320px; box-sizing: border-box;">

			<!-- Search Input Box -->
			<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" style="margin-bottom: 18px;">
				<div style="position: relative; display: flex; align-items: center; background: #ffffff; border: 1px solid #cbd5e1; border-radius: 10px; padding: 12px 18px; box-shadow: 0 2px 8px rgba(0,0,0,0.03); transition: border-color 0.2s ease;">
					<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="#64748b" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 12px; flex-shrink: 0;"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
					<input type="search" name="s" value="<?php echo esc_attr( $search_query ); ?>" placeholder="Search for news, tools, courses, prompts..." style="width: 100%; border: none; outline: none; font-size: 16px; color: #0f172a; background: transparent; font-family: inherit;" id="aikairali-search-input" />
					<?php if ( ! empty( $search_query ) ) : ?>
						<button type="button" aria-label="Clear Search" style="background: none; border: none; font-size: 20px; color: #94a3b8; cursor: pointer; padding: 0 4px; line-height: 1;" onclick="document.getElementById('aikairali-search-input').value=''; this.form.submit();">&times;</button>
					<?php endif; ?>
				</div>
			</form>

			<!-- Results Meta Row (Count, Execution Time & Sort Dropdown) -->
			<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 28px; font-size: 13px; color: #64748b;">
				<div>
					<?php if ( ! empty( $search_query ) ) : ?>
						<?php printf( esc_html__( 'About %s results (%s seconds)', 'aikairali-portal' ), esc_html( number_format( $total_found ) ), esc_html( $elapsed_time ) ); ?>
					<?php else : ?>
						<?php esc_html_e( 'Enter a keyword above to search across AIKairali portal.', 'aikairali-portal' ); ?>
					<?php endif; ?>
				</div>
				<div style="display: flex; align-items: center; gap: 4px; cursor: pointer; color: #475569; font-weight: 500;">
					Sort by <span style="font-size: 9px; margin-left: 2px;">&#9660;</span>
				</div>
			</div>

			<!-- Search Results List Stream -->
			<div class="search-results-list">
				<?php if ( $search_results->have_posts() && ! empty( $search_query ) ) : ?>
					<?php
					while ( $search_results->have_posts() ) : $search_results->the_post();
						$pid       = get_the_ID();
						$link      = get_permalink( $pid );
						$raw_title = get_the_title( $pid );
						$title     = aikairali_highlight_search_term( esc_html( $raw_title ), $search_query );

						$raw_excerpt = get_the_excerpt( $pid );
						if ( empty( $raw_excerpt ) ) {
							$raw_excerpt = wp_trim_words( get_post_field( 'post_content', $pid ), 25, '...' );
						}
						$excerpt = aikairali_highlight_search_term( esc_html( $raw_excerpt ), $search_query );

						$pt_obj    = get_post_type_object( get_post_type( $pid ) );
						$pt_label  = $pt_obj ? $pt_obj->labels->singular_name : 'News';
						$cats      = get_the_category( $pid );
						$cat_name  = ( ! empty( $cats ) && ! is_wp_error( $cats ) ) ? $cats[0]->name : '';
						
						$source_path = 'AIKairali &rsaquo; ' . esc_html( $pt_label );
						if ( ! empty( $cat_name ) ) {
							$source_path .= ' &rsaquo; ' . esc_html( $cat_name );
						}

						$date = get_the_date( 'M j, Y', $pid );

						// Thumbnail Image
						$img = get_the_post_thumbnail_url( $pid, 'thumbnail' );
						if ( ! $img ) {
							$u_img = get_field( 'upload_image', $pid );
							$img   = is_array( $u_img ) ? ( $u_img['url'] ?? '' ) : ( is_string( $u_img ) ? $u_img : '' );
						}
						if ( ! $img ) {
							$img = 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=200&auto=format&fit=crop';
						}
						?>

						<article style="display: flex; gap: 20px; padding-bottom: 22px; margin-bottom: 22px; border-bottom: 1px solid #f1f5f9; align-items: flex-start;">
							<div style="flex: 1; min-width: 0;">
								<!-- Item Title -->
								<h3 style="font-size: 1.125rem; font-weight: 700; margin: 0 0 4px 0; line-height: 1.35;">
									<a href="<?php echo esc_url( $link ); ?>" style="color: #0f172a; text-decoration: none;" class="search-item-title"><?php echo $title; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></a>
								</h3>
								<!-- Breadcrumb Source -->
								<div style="font-size: 13px; color: #64748b; margin-bottom: 6px;">
									<?php echo $source_path; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</div>
								<!-- Excerpt Snippet -->
								<p style="font-size: 0.9rem; color: #475569; line-height: 1.55; margin: 0 0 6px 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
									<?php echo $excerpt; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</p>
								<!-- Date -->
								<div style="font-size: 11px; color: #94a3b8;">
									<?php echo esc_html( $date ); ?>
								</div>
							</div>

							<!-- Right Image Thumbnail -->
							<a href="<?php echo esc_url( $link ); ?>" style="display: block; width: 90px; height: 90px; min-width: 90px; border-radius: 12px; overflow: hidden; background: #f1f5f9; flex-shrink: 0; box-shadow: 0 2px 6px rgba(0,0,0,0.04);">
								<img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $raw_title ); ?>" style="width: 100%; height: 100%; object-fit: cover;" />
							</a>
						</article>

					<?php endwhile; ?>
					<?php wp_reset_postdata(); ?>

				<?php elseif ( ! empty( $search_query ) ) : ?>
					<div style="padding: 40px 20px; text-align: center; background: #f8fafc; border-radius: 12px; border: 1px dashed #cbd5e1;">
						<h3 style="font-size: 1.1rem; color: #334155; margin-bottom: 8px;"><?php printf( esc_html__( 'No results found for "%s"', 'aikairali-portal' ), esc_html( $search_query ) ); ?></h3>
						<p style="color: #64748b; font-size: 0.9rem; margin: 0;"><?php esc_html_e( 'Try checking for spelling errors or searching for different AI keywords.', 'aikairali-portal' ); ?></p>
					</div>
				<?php endif; ?>
			</div>

			<!-- Pagination -->
			<?php if ( $search_results->have_posts() && $search_results->max_num_pages > 1 ) : ?>
				<div class="archive-pagination" style="margin-top: 35px; margin-bottom: 30px; text-align: center;">
					<?php
					echo paginate_links( [
						'total'     => $search_results->max_num_pages,
						'prev_text' => '&laquo; Previous',
						'next_text' => 'Next &raquo;',
					] );
					?>
				</div>
			<?php endif; ?>

		</main>

		<!-- RIGHT SIDEBAR: Advertisement Column -->
		<aside style="width: 320px; min-width: 280px; box-sizing: border-box;">
			
			<!-- Primary Banner Ad Box -->
			<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px; margin-bottom: 30px; text-align: center; box-shadow: 0 2px 8px rgba(0,0,0,0.02);">
				<a href="#" target="_blank" rel="noopener" style="display: block; text-decoration: none; margin-bottom: 12px;">
					<img src="/wp-content/themes/twentytwentyfive/assets/images/ad-banner.png" alt="Advertisement Banner" style="width: 100%; border-radius: 10px; display: block; box-shadow: 0 4px 12px rgba(0,0,0,0.06);" />
				</a>
				<div style="font-size: 11px; color: #94a3b8;">
					To advertise here, <a href="#" style="color: #2563eb; text-decoration: underline;">Contact Us</a>
				</div>
			</div>

			<!-- Secondary Banner Ad Box -->
			<div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px; text-align: center;">
				<a href="#" target="_blank" rel="noopener" style="display: block; text-decoration: none;">
					<img src="https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=400&auto=format&fit=crop" alt="Acronis Cyber Security" style="width: 100%; height: 180px; object-fit: cover; border-radius: 10px; display: block; margin-bottom: 12px;" />
				</a>
				<h4 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 6px 0;">Acronis Cyber Protection</h4>
				<p style="font-size: 12px; color: #64748b; margin: 0 0 10px 0;">Healthcare has evolved. Your IT should too. Discover Cyber Protection.</p>
				<a href="#" style="display: inline-block; background: #2563eb; color: #ffffff; padding: 6px 16px; border-radius: 6px; font-size: 11px; font-weight: 700; text-decoration: none;">Discover Cyber</a>
			</div>

		</aside>

	</div>

</div>

<style>
.search-item-title:hover { color: #2563eb !important; }
.search-results-list strong { color: #0f172a; background: #fef08a; padding: 0 2px; border-radius: 2px; }
@media (max-width: 768px) {
	aside { width: 100% !important; margin-top: 30px; }
}
</style>

<?php
get_footer();
