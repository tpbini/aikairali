<?php
/**
 * Archive / List Template for AI Prompts.
 * Supports Multi-Category Overview layout (grouped by category matching AI News)
 * as well as Single-Category Stream layout.
 *
 * @package AIKairali_Portal
 */

get_header();

$current_cat        = get_queried_object();
$is_single_category = ( is_category() || is_tax() ) && ! empty( $current_cat ) && isset( $current_cat->term_id );
if ( isset( $_GET['cat'] ) || isset( $_GET['category'] ) ) {
	$is_single_category = true;
}
?>

<div class="aikairali-container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px; box-sizing: border-box; font-family: 'Anek Malayalam', system-ui, -apple-system, sans-serif;">

	<?php if ( $is_single_category ) : ?>
		<?php aikairali_render_single_category_prompts_view( $current_cat ); ?>
	<?php else : ?>
		<?php aikairali_render_multi_category_prompts_view(); ?>
	<?php endif; ?>

</div>

<?php
/**
 * Render Main AI Prompts Multi-Category Sectioned Layout
 */
function aikairali_render_multi_category_prompts_view() {
	$categories_config = [
		[
			'name' => 'ChatGPT Prompts',
			'slug' => 'chatgpt-prompts',
			'desc' => 'System prompts, persona roles, and productivity templates for ChatGPT',
		],
		[
			'name' => 'Midjourney Prompts',
			'slug' => 'midjourney-prompts',
			'desc' => 'Photorealistic, anime, 3D render, and UI design prompts',
		],
		[
			'name' => 'Coding Prompts',
			'slug' => 'coding-prompts',
			'desc' => 'Code refactoring, debugging, architecture, and unit testing prompts',
		],
		[
			'name' => 'Business & Marketing',
			'slug' => 'business-marketing',
			'desc' => 'SEO, email marketing, pitch decks, and strategy prompts',
		],
	];
	?>

	<!-- Page Header Banner -->
	<header class="archive-header" style="margin-bottom: 40px; border-bottom: 2px solid #f1f5f9; padding-bottom: 24px;">
		<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
			<span style="width: 4px; height: 28px; background: #2563eb; border-radius: 2px;"></span>
			<h1 style="font-size: 26px; font-weight: 600; color: #0f172a; margin: 0; line-height: 1.2;">
				<?php esc_html_e( 'AI Prompts Library', 'aikairali-portal' ); ?>
			</h1>
		</div>
		<p style="color: #64748b; font-size: 16px; margin: 0;">
			<?php esc_html_e( 'Curated system prompts, ChatGPT templates, and generative AI prompt engineering guides.', 'aikairali-portal' ); ?>
		</p>
	</header>

	<!-- Iterate over each Category Section -->
	<div class="ai-prompts-categories-wrapper">
		<?php foreach ( $categories_config as $cat_info ) : ?>
			<?php aikairali_render_prompts_category_section_block( $cat_info ); ?>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Render a single Category Row Section (Left Feature + Center 2x2 Grid + Right Ad Box)
 */
function aikairali_render_prompts_category_section_block( $cat_info ) {
	$cat_name = $cat_info['name'];
	$cat_slug = $cat_info['slug'];
	
	$term     = get_term_by( 'slug', $cat_slug, 'prompt-category' );
	$cat_link = $term ? get_term_link( $term ) : add_query_arg( 'cat', $cat_slug, get_post_type_archive_link( 'ai-prompts' ) );

	$cat_posts_query = new WP_Query( [
		'post_type'      => [ 'ai-prompts', 'prompts' ],
		'tax_query'      => [
			[
				'taxonomy' => 'prompt-category',
				'field'    => 'slug',
				'terms'    => $cat_slug,
			],
		],
		'posts_per_page' => 5,
		'post_status'    => 'publish',
	] );

	$posts = [];
	if ( $cat_posts_query->have_posts() ) {
		while ( $cat_posts_query->have_posts() ) {
			$cat_posts_query->the_post();
			$pid = get_the_ID();
			$img = get_the_post_thumbnail_url( $pid, 'medium_large' );
			if ( ! $img ) {
				$u_img = get_field( 'upload_image', $pid );
				$img   = is_array( $u_img ) ? ( $u_img['url'] ?? '' ) : ( is_string( $u_img ) ? $u_img : '' );
			}
			$posts[] = [
				'title' => get_the_title( $pid ),
				'link'  => get_permalink( $pid ),
				'image' => $img ?: 'https://images.unsplash.com/photo-1677442136019-21780efad99a?w=600&auto=format&fit=crop',
				'cat'   => mb_strtoupper( $cat_name, 'UTF-8' ),
			];
		}
		wp_reset_postdata();
	}

	if ( empty( $posts ) ) {
		$posts = aikairali_get_fallback_prompts_for_category( $cat_slug, $cat_name );
	}

	$feature_post = $posts[0];
	$grid_posts   = array_slice( $posts, 1, 4 );
	?>

	<section class="ai-cat-section" style="margin-bottom: 50px; padding-bottom: 45px; border-bottom: 2px solid #e2e8f0;">
		
		<!-- Category Header Row -->
		<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 12px; border-bottom: 2px solid #0f172a;">
			<div style="display: flex; align-items: center; gap: 8px;">
				<span style="font-weight: 900; color: #dc2626; font-size: 18px; line-height: 1;">!</span>
				<h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">
					<a href="<?php echo esc_url( $cat_link ); ?>" style="color: #0f172a; text-decoration: none;"><?php echo esc_html( $cat_name ); ?></a>
				</h2>
			</div>
			<a href="<?php echo esc_url( $cat_link ); ?>" style="font-size: 12px; font-weight: 800; color: #2563eb; text-decoration: none; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 4px;" class="ai-more-link">
				<?php printf( esc_html__( 'MORE FROM %s &raquo;&raquo;', 'aikairali-portal' ), esc_html( mb_strtoupper( $cat_name, 'UTF-8' ) ) ); ?>
			</a>
		</div>

		<!-- 3-Column Category Row Layout -->
		<div class="ai-cat-grid-layout" style="display: flex; gap: 24px; flex-wrap: wrap;">

			<!-- 1. LEFT FEATURE CARD -->
			<div class="ai-cat-feature-col" style="flex: 1.1; min-width: 280px;">
				<article class="ai-cat-feature-card" style="background: #ffffff; border-radius: 12px; overflow: hidden; display: flex; flex-direction: column; height: 100%;">
					<a href="<?php echo esc_url( $feature_post['link'] ); ?>" style="display: block; width: 100%; aspect-ratio: 16/10; overflow: hidden; border-radius: 12px; background: #f1f5f9; margin-bottom: 14px;">
						<img src="<?php echo esc_url( $feature_post['image'] ); ?>" alt="<?php echo esc_attr( $feature_post['title'] ); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease;" class="ai-zoom-img" />
					</a>
					<div style="display: flex; flex-direction: column; flex-grow: 1;">
						<h3 style="font-size: 1.25rem; font-weight: 700; margin: 0 0 12px 0; line-height: 1.35;">
							<a href="<?php echo esc_url( $feature_post['link'] ); ?>" style="color: #0f172a; text-decoration: none; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;" class="ai-news-title-link"><?php echo esc_html( $feature_post['title'] ); ?></a>
						</h3>
						<div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; font-size: 11px;">
							<a href="<?php echo esc_url( $cat_link ); ?>" style="font-weight: 800; color: #2563eb; text-transform: uppercase; text-decoration: none; letter-spacing: 0.04em;">
								<?php echo esc_html( mb_strtoupper( $cat_name, 'UTF-8' ) ); ?>
							</a>
							<button type="button" aria-label="Bookmark" style="background: none; border: none; padding: 0; cursor: pointer; color: #cbd5e1;">
								<svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
							</button>
						</div>
					</div>
				</article>
			</div>

			<!-- 2. CENTER 2x2 SMALL GRID -->
			<div class="ai-cat-center-col" style="flex: 1.4; min-width: 300px;">
				<div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px;">
					<?php foreach ( $grid_posts as $g_item ) : ?>
						<article class="ai-cat-small-card" style="display: flex; flex-direction: column;">
							<a href="<?php echo esc_url( $g_item['link'] ); ?>" style="display: block; width: 100%; aspect-ratio: 16/10; overflow: hidden; border-radius: 10px; background: #f1f5f9; margin-bottom: 10px;">
								<img src="<?php echo esc_url( $g_item['image'] ); ?>" alt="<?php echo esc_attr( $g_item['title'] ); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease;" class="ai-zoom-img" />
							</a>
							<h4 style="font-size: 0.875rem; font-weight: 700; margin: 0 0 8px 0; line-height: 1.35; min-height: 38px;">
								<a href="<?php echo esc_url( $g_item['link'] ); ?>" style="color: #0f172a; text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" class="ai-news-title-link"><?php echo esc_html( $g_item['title'] ); ?></a>
							</h4>
							<div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; font-size: 11px;">
								<a href="<?php echo esc_url( $cat_link ); ?>" style="font-weight: 800; color: #2563eb; text-transform: uppercase; text-decoration: none; letter-spacing: 0.04em;">
									<?php echo esc_html( mb_strtoupper( $cat_name, 'UTF-8' ) ); ?>
								</a>
								<button type="button" aria-label="Bookmark" style="background: none; border: none; padding: 0; cursor: pointer; color: #cbd5e1;">
									<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
								</button>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- 3. RIGHT SIDEBAR AD BOX -->
			<div class="ai-cat-ad-col" style="width: 260px; min-width: 240px; box-sizing: border-box;">
				<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px; text-align: center; height: 100%; box-sizing: border-box; display: flex; flex-direction: column; justify-content: center;">
					<div style="font-size: 9px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 10px;">
						ADVERTISEMENT
					</div>
					<a href="#" target="_blank" rel="noopener" style="display: block; text-decoration: none; margin-bottom: 12px;">
						<img src="/wp-content/themes/twentytwentyfive/assets/images/ad-banner.png" alt="Advertisement Banner" style="width: 100%; height: auto; border-radius: 8px; display: block; box-shadow: 0 4px 10px rgba(0,0,0,0.05);" />
					</a>
					<div>
						<a href="#" style="display: inline-block; background: #ffffff; color: #dc2626; border: 1px solid #fca5a5; padding: 5px 14px; border-radius: 20px; font-size: 10px; font-weight: 800; text-decoration: none; text-transform: uppercase; letter-spacing: 0.05em;">
							GO AD-FREE
						</a>
					</div>
				</div>
			</div>

		</div>
	</section>

	<?php
}

/**
 * Render Single Category View (2-Column Stream Layout when clicking category title / link)
 */
function aikairali_render_single_category_prompts_view( $term ) {
	$cat_name = is_object( $term ) && isset( $term->name ) ? $term->name : ( isset( $_GET['cat'] ) ? sanitize_text_field( $_GET['cat'] ) : 'AI Prompts' );
	$cat_slug = is_object( $term ) && isset( $term->slug ) ? $term->slug : ( isset( $_GET['cat'] ) ? sanitize_text_field( $_GET['cat'] ) : '' );
	?>
	<!-- Category Archive Header Banner -->
	<header class="archive-header" style="margin-bottom: 35px; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px;">
		<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
			<span style="width: 4px; height: 26px; background: #2563eb; border-radius: 2px;"></span>
			<h1 style="font-size: 26px; font-weight: 600; color: #0f172a; margin: 0; line-height: 1.2;">
				<?php printf( esc_html__( 'AI Prompts: %s', 'aikairali-portal' ), esc_html( $cat_name ) ); ?>
			</h1>
		</div>
		<p style="color: #64748b; font-size: 16px; margin: 0;">
			<?php printf( esc_html__( 'Browse all artificial intelligence system prompts and templates in %s.', 'aikairali-portal' ), esc_html( $cat_name ) ); ?>
		</p>
	</header>

	<div class="ai-news-archive-layout" style="display: flex; gap: 40px; align-items: flex-start; flex-wrap: wrap;">
		<main class="ai-news-main-content" style="flex: 1; min-width: 320px;">
			<?php
			$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
			$args  = [
				'post_type'      => [ 'ai-prompts', 'prompts' ],
				'post_status'    => 'publish',
				'posts_per_page' => 10,
				'paged'          => $paged,
			];
			if ( ! empty( $cat_slug ) ) {
				$args['tax_query'] = [
					[
						'taxonomy' => 'prompt-category',
						'field'    => 'slug',
						'terms'    => $cat_slug,
					],
				];
			}
			$cat_query = new WP_Query( $args );

			$fallback_items = aikairali_get_fallback_prompts_for_category( $cat_slug, $cat_name );
			?>

			<div class="ai-news-list-stream">
				<?php
				if ( $cat_query->have_posts() ) :
					while ( $cat_query->have_posts() ) : $cat_query->the_post();
						aikairali_render_prompts_list_item( get_the_ID() );
					endwhile;
					wp_reset_postdata();
				else :
					foreach ( $fallback_items as $fitem ) :
						aikairali_render_prompts_default_item( $fitem );
					endforeach;
				endif;
				?>
			</div>

			<?php if ( $cat_query->have_posts() ) : ?>
				<div class="archive-pagination" style="margin-top: 40px; margin-bottom: 30px; text-align: center;">
					<?php
					echo paginate_links( [
						'total'     => $cat_query->max_num_pages,
						'prev_text' => '&laquo; Previous',
						'next_text' => 'Next &raquo;',
					] );
					?>
				</div>
			<?php endif; ?>
		</main>

		<!-- Right Sidebar -->
		<aside class="ai-news-sidebar" style="width: 340px; min-width: 280px; box-sizing: border-box;">
			<div class="ai-news-ad-widget" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 16px; padding: 18px; margin-bottom: 35px; text-align: center;">
				<div style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 12px;">ADVERTISEMENT</div>
				<a href="#" target="_blank" rel="noopener" style="display: block; text-decoration: none; margin-bottom: 14px;">
					<img src="/wp-content/themes/twentytwentyfive/assets/images/ad-banner.png" alt="Advertisement Banner" style="width: 100%; border-radius: 10px; display: block;" />
				</a>
				<div>
					<a href="#" style="display: inline-block; background: #ffffff; color: #dc2626; border: 1px solid #fca5a5; padding: 6px 18px; border-radius: 20px; font-size: 11px; font-weight: 800; text-decoration: none;">GO AD-FREE</a>
				</div>
			</div>

			<div class="ai-news-recent-widget" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; padding: 22px;">
				<div style="display: flex; align-items: center; gap: 8px; margin-bottom: 20px; padding-bottom: 12px; border-bottom: 2px solid #0f172a;">
					<span style="font-weight: 900; color: #dc2626; font-size: 16px;">!</span>
					<h3 style="font-size: 13px; font-weight: 800; color: #0f172a; margin: 0; text-transform: uppercase; letter-spacing: 0.06em;">RECENT PROMPTS</h3>
				</div>
				<div class="ai-recent-posts-list">
					<?php foreach ( array_slice( $fallback_items, 0, 4 ) as $ritem ) : ?>
						<article class="ai-recent-item" style="display: flex; gap: 14px; padding-bottom: 14px; margin-bottom: 14px; border-bottom: 1px solid #f1f5f9; align-items: center;">
							<a href="<?php echo esc_url( $ritem['link'] ); ?>" style="display: block; width: 72px; height: 72px; min-width: 72px; border-radius: 8px; overflow: hidden; background: #f1f5f9; flex-shrink: 0;">
								<img src="<?php echo esc_url( $ritem['image'] ); ?>" alt="<?php echo esc_attr( $ritem['title'] ); ?>" style="width: 100%; height: 100%; object-fit: cover;" />
							</a>
							<div style="flex: 1; min-width: 0;">
								<h4 style="font-size: 13px; font-weight: 700; margin: 0 0 6px 0; line-height: 1.35;">
									<a href="<?php echo esc_url( $ritem['link'] ); ?>" style="color: #0f172a; text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?php echo esc_html( $ritem['title'] ); ?></a>
								</h4>
								<span style="font-weight: 800; color: #2563eb; text-transform: uppercase; font-size: 11px;">
									<?php echo esc_html( mb_strtoupper( $cat_name, 'UTF-8' ) ); ?>
								</span>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>
		</aside>
	</div>
	<?php
}

/**
 * Return customized fallback prompt items per category
 */
function aikairali_get_fallback_prompts_for_category( $slug, $cat_name ) {
	$map = [
		'chatgpt-prompts' => [
			[
				'title' => 'സീനിയർ പൈത്തൺ ആർക്കിടെക്റ്റ് ചാറ്റ്ജിപിടി സിസ്റ്റം പ്രോംപ്റ്റ് മാസ്റ്റർ പ്ലാൻ',
				'excerpt' => 'നിങ്ങളുടെ ഏത് കോഡ് പ്രോജക്റ്റും സെക്കന്റുകൾക്കുള്ളിൽ വിശകലനം ചെയ്ത് വൃത്തിയുള്ള ഡ്രൈ സൊല്യൂഷൻ നൽകുന്ന വിദഗ്ദ്ധ സിസ്റ്റം പ്രോംപ്റ്റ്...',
				'image' => 'https://images.unsplash.com/photo-1677442136019-21780efad99a?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'എസ്‌ഇഒ കണ്ടന്റ് പോസ്റ്റ് ജനറേറ്റർ ചാറ്റ്ജിപിടി ടെംപ്ലേറ്റ് 2026',
				'excerpt' => 'ഗൂഗിൾ റാങ്കിംഗ് ഉയർന്ന ട്രാഫിക് ലേഖനങ്ങൾ എളുപ്പത്തിൽ തയ്യാറാക്കാനുള്ള പ്രോംപ്റ്റ് എൻജിനീയറിംഗ് ഗൈഡ്...',
				'image' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'പ്രൊഫഷണൽ കോർപ്പറേറ്റ് ഇമെയിൽ റൈറ്റിംഗ് ചാറ്റ്ജിപിടി പ്രോംപ്റ്റ്',
				'excerpt' => 'ക്ലയന്റുകൾക്ക് അയക്കേണ്ട നീണ്ട സന്ദേശങ്ങൾ ചുരുക്കി കൃത്യവും ബഹുമാനപൂർവ്വവുമായ ഔദ്യോഗിക മെയിലുകൾ തയ്യാറാക്കുക...',
				'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'ബിസിനസ്സ് ഐഡിയ വിശകലനത്തിനും റിസ്ക് അസസ്മെന്റിനുമുള്ള എഐ പ്രോംപ്റ്റ്',
				'excerpt' => 'പുതിയ സ്റ്റാർട്ടപ്പ് സംരംഭങ്ങളുടെ സാധ്യതകളും വെല്ലുവിളികളും വിശകലനം ചെയ്യുന്നതിനുള്ള സമഗ്ര ചോദ്യങ്ങൾ...',
				'image' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'യൂട്യൂബ് വീഡിയോ സ്ക്രിപ്റ്റ് ഡോക്യുമെന്ററി ടൈപ്പ് വികസിപ്പിക്കുന്ന പ്രോംപ്റ്റ്',
				'excerpt' => 'ജനപ്രിയ കഥാകഥന ശൈലിയിൽ ആകർഷകമായ യൂട്യൂബ് വീഡിയോ തിരക്കഥകൾ നിർമ്മിക്കാം...',
				'image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=600&auto=format&fit=crop',
			],
		],
		'midjourney-prompts' => [
			[
				'title' => 'ഫോട്ടോഗ്രാഫിക് അൾട്രാ റിയലിസ്റ്റിക് പോർട്രെയിറ്റ് മിഡ്‌ജേർണി v6.1 പ്രോംപ്റ്റുകൾ',
				'excerpt' => 'ക്യാമറ ലെൻസ് മോഡലുകളും ലൈറ്റിംഗ് പാരാമീറ്ററുകളും ഉപയോഗിച്ച് ലൈഫ്-ലൈക്ക് ഡിജിറ്റൽ ചിത്രങ്ങൾ നിർമ്മിക്കുക...',
				'image' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'ആനിമേ & സൈബർപങ്ക് ആർട്ട് സ്റ്റൈൽ മിഡ്‌ജേർണി പ്രോംപ്റ്റ് കോഡ്സ്',
				'excerpt' => 'നിങ്ങളുടെ സങ്കൽപ്പങ്ങളിലെ ലോകങ്ങളെ അത്ഭുതകരമായ സൈബർപങ്ക് ശൈലിയിൽ ചിത്രീകരിക്കുന്ന പാരാമീറ്ററുകൾ...',
				'image' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'മൊബൈൽ ആപ്പ് യുഐ/യുഎക്സ് ഡാഷ്‌ബോർഡ് 3D റെൻഡർ മിഡ്‌ജേർണി പ്രോംപ്റ്റ്',
				'excerpt' => 'ഡിസൈനർമാർക്ക് പ്രചോദനമേകുന്ന ആധുനിക യൂസർ ഇന്റർഫേസ് മോക്കപ്പുകൾ നിർമ്മിക്കാനുള്ള ഗൈഡ്...',
				'image' => 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'സിനിമാറ്റിക് ഡ്രോൺ ലാൻഡ്‌സ്‌കേപ്പ് ഫോട്ടോഗ്രാഫി ഇമേജ് പ്രോംപ്റ്റ്',
				'excerpt' => 'പ്രകൃതിദൃശ്യങ്ങളും നഗരക്കാഴ്ചകളും ഡ്രോൺ ക്യാമറ ആംഗിളിൽ പകർത്തുന്ന മിഡ്‌ജേർണി കോഡുകൾ...',
				'image' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'പ്രൊഡക്ട് പാക്കേജിംഗ് & 3D ബ്രാൻഡിംഗ് മിഡ്‌ജേർണി ഇമേജ് ജനറേഷൻ',
				'excerpt' => 'ബ്രാൻഡ് ലോഗോയും പ്രൊഡക്ട് മോക്കപ്പുകളും ഏറ്റവും പ്രൊഫഷണൽ ശൈലിയിൽ നിർമ്മിക്കുക...',
				'image' => 'https://images.unsplash.com/photo-1586717791821-3f44a563fa4c?w=600&auto=format&fit=crop',
			],
		],
	];

	$default_items = [
		[
			'title' => sprintf( '%s വിഭാഗത്തിലെ ഏറ്റവും ആധുനിക എഐ പ്രോംപ്റ്റുകൾ', $cat_name ),
			'excerpt' => 'ഉൽപ്പാദനക്ഷമത വർദ്ധിപ്പിക്കാനും സമയം ലാഭിക്കാനും സഹായിക്കുന്ന അത്യാധുനിക സിസ്റ്റം പ്രോംപ്റ്റുകൾ...',
			'image' => 'https://images.unsplash.com/photo-1677442136019-21780efad99a?w=600&auto=format&fit=crop',
		],
		[
			'title' => sprintf( '%s പ്രോംപ്റ്റ് എൻജിനീയറിംഗ് ഗൈഡും ഉപയോഗക്രമവും', $cat_name ),
			'excerpt' => 'കൃത്യമായ ഉത്തരങ്ങളും മികച്ച ഫലങ്ങളും നേടുന്നതിനുള്ള സാങ്കേതിക ചോദ്യ ശൈലികൾ...',
			'image' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=600&auto=format&fit=crop',
		],
		[
			'title' => sprintf( '%s ടാസ്കുകൾ വേഗത്തിലാക്കാൻ സഹായിക്കുന്ന പ്രോംപ്റ്റുകൾ', $cat_name ),
			'excerpt' => 'നിങ്ങളുടെ പ്രതിദിന പ്രോജക്റ്റുകളിൽ സൗജന്യമായി ഉപയോഗിക്കാവുന്ന പ്രോംപ്റ്റ് ടെംപ്ലേറ്റുകൾ...',
			'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop',
		],
		[
			'title' => sprintf( '%s ജനറേറ്റീവ് എഐ പ്രോംപ്റ്റ് മാസ്റ്റർ കോഴ്സ്', $cat_name ),
			'excerpt' => 'പ്രോംപ്റ്റുകൾ എങ്ങനെ സ്വന്തമായി എഴുതി തയ്യാറാക്കാം എന്ന് വ്യക്തമാക്കുന്ന പഠന റിപ്പോർട്ട്...',
			'image' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=600&auto=format&fit=crop',
		],
		[
			'title' => sprintf( '%s മികച്ച ഫലങ്ങൾ തരുന്ന നിർദ്ദേശങ്ങളുടെ സമാഹാരം', $cat_name ),
			'excerpt' => 'വലിയ പ്രോജക്റ്റുകൾക്ക് സഹായകരമാകുന്ന ആധുനിക പ്രോംപ്റ്റ് കളക്ഷൻ...',
			'image' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=600&auto=format&fit=crop',
		],
	];

	$results = isset( $map[ $slug ] ) ? $map[ $slug ] : $default_items;

	foreach ( $results as &$it ) {
		$it['cat'] = mb_strtoupper( $cat_name, 'UTF-8' );
		if ( empty( $it['link'] ) || '#' === $it['link'] ) {
			$it['link'] = home_url( '/prompts/' );
		}
	}

	return $results;
}

/**
 * Helper function to render a single DB prompt item in stream list view
 */
function aikairali_render_prompts_list_item( $post_id ) {
	$title   = get_the_title( $post_id );
	$link    = get_permalink( $post_id );
	$excerpt = get_the_excerpt( $post_id );
	if ( empty( $excerpt ) ) {
		$excerpt = wp_trim_words( get_post_field( 'post_content', $post_id ), 20, '...' );
	}
	$date = get_the_date( 'M j, Y', $post_id );

	$image = get_the_post_thumbnail_url( $post_id, 'medium_large' );
	if ( ! $image ) {
		$upload_img = get_field( 'upload_image', $post_id );
		$image = is_array( $upload_img ) ? ( $upload_img['url'] ?? '' ) : ( is_string( $upload_img ) ? $upload_img : '' );
	}
	if ( ! $image ) {
		$image = 'https://images.unsplash.com/photo-1677442136019-21780efad99a?w=600&auto=format&fit=crop';
	}

	$terms    = get_the_terms( $post_id, 'prompt-category' );
	$cat_name = ( $terms && ! is_wp_error( $terms ) ) ? mb_strtoupper( $terms[0]->name, 'UTF-8' ) : 'AI PROMPT';

	aikairali_render_prompts_default_item( [
		'title'   => $title,
		'excerpt' => $excerpt,
		'cat'     => $cat_name,
		'date'    => $date,
		'image'   => $image,
		'link'    => $link,
	] );
}

/**
 * Helper function to render a prompt item array structure matching stream design
 */
function aikairali_render_prompts_default_item( $item ) {
	?>
	<article class="ai-news-card-item" style="display: flex; gap: 24px; padding-bottom: 24px; margin-bottom: 24px; border-bottom: 1px solid #e2e8f0; align-items: flex-start;">
		<a href="<?php echo esc_url( $item['link'] ); ?>" class="ai-news-thumb-link" style="display: block; width: 240px; min-width: 240px; aspect-ratio: 16/10; border-radius: 14px; overflow: hidden; background: #f1f5f9; flex-shrink: 0; box-shadow: 0 2px 8px rgba(0,0,0,0.04);">
			<img src="<?php echo esc_url( $item['image'] ); ?>" alt="<?php echo esc_attr( $item['title'] ); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease;" />
		</a>
		<div style="flex: 1; min-width: 0; display: flex; flex-direction: column; min-height: 140px;">
			<h3 style="font-size: 1.2rem; font-weight: 600; margin: 0 0 10px 0; line-height: 1.35;">
				<a href="<?php echo esc_url( $item['link'] ); ?>" style="color: #0f172a; text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;" class="ai-news-title-link"><?php echo esc_html( $item['title'] ); ?></a>
			</h3>
			<p style="font-size: 0.925rem; color: #475569; margin: 0 0 14px 0; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
				<?php echo esc_html( $item['excerpt'] ); ?>
			</p>
			<div style="margin-top: auto; display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #64748b;">
				<div style="display: flex; align-items: center; gap: 10px;">
					<span style="font-weight: 800; color: #2563eb; text-transform: uppercase; letter-spacing: 0.05em;">
						<?php echo esc_html( $item['cat'] ); ?>
					</span>
				</div>
				<button type="button" aria-label="Bookmark" style="background: none; border: none; padding: 4px; cursor: pointer; color: #94a3b8; display: inline-flex;" class="ai-bookmark-btn">
					<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21l-7-5-7 5V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2z"></path></svg>
				</button>
			</div>
		</div>
	</article>
	<?php
}
?>

<style>
.ai-more-link:hover { color: #0f172a !important; text-decoration: underline !important; }
.ai-news-title-link:hover { color: #2563eb !important; }
.ai-cat-feature-card:hover .ai-zoom-img,
.ai-cat-small-card:hover .ai-zoom-img,
.ai-news-card-item:hover .ai-news-thumb-link img { transform: scale(1.05); }
.ai-bookmark-btn:hover { color: #2563eb !important; }
@media (max-width: 992px) {
	.ai-cat-grid-layout { flex-direction: column; }
	.ai-cat-ad-col { width: 100% !important; }
}
@media (max-width: 600px) {
	.ai-cat-center-col div { grid-template-columns: 1fr !important; }
	.ai-news-card-item { flex-direction: column; }
	.ai-news-thumb-link { width: 100% !important; min-width: 100% !important; }
}
</style>

<?php
get_footer();
