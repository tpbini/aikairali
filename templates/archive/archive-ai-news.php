<?php
/**
 * Archive / List Template for AI News.
 * Supports Multi-Category Overview layout (grouped by category matching attached reference image)
 * as well as Single-Category Stream layout.
 *
 * @package AIKairali_Portal
 */

get_header();

// Determine if we are on a single category archive or main AI News overview
$current_cat        = get_queried_object();
$is_single_category = ( is_category() || is_tax() ) && ! empty( $current_cat ) && isset( $current_cat->term_id );
?>

<div class="aikairali-container" style="max-width: 1200px; margin: 40px auto; padding: 0 20px; box-sizing: border-box; font-family: 'Anek Malayalam', system-ui, -apple-system, sans-serif;">

	<?php if ( $is_single_category ) : ?>
		<?php aikairali_render_single_category_news_view( $current_cat ); ?>
	<?php else : ?>
		<?php aikairali_render_multi_category_news_view(); ?>
	<?php endif; ?>

</div>

<?php
/**
 * Render Main AI News Multi-Category Sectioned Layout
 */
function aikairali_render_multi_category_news_view() {
	$categories_config = [
		[
			'name' => 'Generative AI',
			'slug' => 'generative-ai',
			'desc' => 'LLMs, Multimodal AI, Image & Code Generators',
		],
		[
			'name' => 'India AI',
			'slug' => 'india-ai',
			'desc' => 'Indian AI Ecosystem, Policy, Startups & Local Innovation',
		],
		[
			'name' => 'Research & Innovation',
			'slug' => 'research-innovation',
			'desc' => 'Scientific breakthroughs, algorithms, and future tech',
		],
		[
			'name' => 'Robotics',
			'slug' => 'robotics',
			'desc' => 'Humanoid robots, autonomous systems, and industrial robotics',
		],
		[
			'name' => 'Automation',
			'slug' => 'automation',
			'desc' => 'Workplace automation, RPA, AI workflows, and software tools',
		],
		[
			'name' => 'Cybersecurity',
			'slug' => 'cybersecurity',
			'desc' => 'AI threat detection, deepfake defense, and data protection',
		],
		[
			'name' => 'Education',
			'slug' => 'education',
			'desc' => 'AI in schools, universities, tutoring, and ed-tech',
		],
		[
			'name' => 'Healthcare',
			'slug' => 'healthcare',
			'desc' => 'AI diagnostics, drug discovery, and medical technology',
		],
		[
			'name' => 'Opinions & Analysis',
			'slug' => 'opinions-analysis',
			'desc' => 'Expert perspectives, AI ethics, economy, and future impact',
		],
	];
	?>

	<!-- Page Header Banner -->
	<header class="archive-header" style="margin-bottom: 40px; border-bottom: 2px solid #f1f5f9; padding-bottom: 24px;">
		<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
			<span style="width: 4px; height: 28px; background: #2563eb; border-radius: 2px;"></span>
			<h1 style="font-size: 26px; font-weight: 600; color: #0f172a; margin: 0; line-height: 1.2;">
				<?php esc_html_e( 'AI News Directory', 'aikairali-portal' ); ?>
			</h1>
		</div>
		<p style="color: #64748b; font-size: 16px; margin: 0;">
			<?php esc_html_e( 'Latest artificial intelligence news, breakthroughs, and analysis organized by category.', 'aikairali-portal' ); ?>
		</p>
	</header>

	<!-- Iterate over each Category Section -->
	<div class="ai-news-categories-wrapper">
		<?php foreach ( $categories_config as $cat_info ) : ?>
			<?php aikairali_render_category_section_block( $cat_info ); ?>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Render a single Category Row Section (Left Feature + Center 2x2 Grid + Right Ad Box)
 */
function aikairali_render_category_section_block( $cat_info ) {
	$cat_name = $cat_info['name'];
	$cat_slug = $cat_info['slug'];
	
	// Retrieve WP Term if available
	$term     = get_term_by( 'slug', $cat_slug, 'category' );
	$cat_link = $term ? get_category_link( $term->term_id ) : esc_url( home_url( '/category/' . $cat_slug . '/' ) );

	// Query posts in this category
	$cat_posts_query = new WP_Query( [
		'post_type'      => [ 'post', 'ai-news' ],
		'category_name'  => $cat_slug,
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
				'image' => $img ?: 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=600&auto=format&fit=crop',
				'cat'   => mb_strtoupper( $cat_name, 'UTF-8' ),
			];
		}
		wp_reset_postdata();
	}

	// Fallback mock items tailored per category if database has 0 items
	if ( empty( $posts ) ) {
		$posts = aikairali_get_fallback_posts_for_category( $cat_slug, $cat_name );
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

		<!-- 3-Column Category Row Layout matching user reference image -->
		<div class="ai-cat-grid-layout" style="display: flex; gap: 28px; flex-wrap: wrap; align-items: flex-start;">

			<!-- 1. LEFT FEATURE COLUMN (~36% width) -->
			<div class="ai-cat-feature-col" style="flex: 1.2; min-width: 290px; box-sizing: border-box;">
				<?php $col1_hero = $posts[0]; $col1_sub = array_slice( $posts, 1, 3 ); ?>
				<article style="margin-bottom: 20px;">
					<div style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 6px;">
						<?php echo esc_html( mb_strtoupper( $cat_name, 'UTF-8' ) ); ?>
					</div>
					<h3 style="font-size: 1.35rem; font-weight: 800; line-height: 1.3; color: #0f172a; margin: 0 0 12px 0;">
						<a href="<?php echo esc_url( $col1_hero['link'] ); ?>" style="color: #0f172a; text-decoration: none;" class="ai-news-title-link"><?php echo esc_html( $col1_hero['title'] ); ?></a>
					</h3>
					<a href="<?php echo esc_url( $col1_hero['link'] ); ?>" style="display: block; width: 100%; aspect-ratio: 16/10; overflow: hidden; border-radius: 10px; background: #f1f5f9; margin-bottom: 12px;">
						<img src="<?php echo esc_url( $col1_hero['image'] ); ?>" alt="<?php echo esc_attr( $col1_hero['title'] ); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease;" class="ai-zoom-img" />
					</a>
					<p style="font-size: 0.925rem; color: #475569; line-height: 1.5; margin: 0 0 8px 0;">
						<?php echo esc_html( $col1_hero['excerpt'] ?? wp_trim_words( $col1_hero['title'], 18, '...' ) ); ?>
					</p>
					<div style="font-size: 11px; color: #94a3b8; font-weight: 500;">4 min read</div>
				</article>

				<!-- Sub-items below Left Hero -->
				<div style="border-top: 1px solid #e2e8f0; padding-top: 16px;">
					<?php foreach ( $col1_sub as $sub_item ) : ?>
						<article style="padding-bottom: 14px; margin-bottom: 14px; border-bottom: 1px solid #f1f5f9;">
							<div style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px;">
								<?php echo esc_html( mb_strtoupper( $cat_name, 'UTF-8' ) ); ?>
							</div>
							<h4 style="font-size: 0.95rem; font-weight: 700; line-height: 1.35; margin: 0 0 4px 0;">
								<a href="<?php echo esc_url( $sub_item['link'] ); ?>" style="color: #0f172a; text-decoration: none;" class="ai-news-title-link"><?php echo esc_html( $sub_item['title'] ); ?></a>
							</h4>
							<div style="font-size: 11px; color: #94a3b8; font-weight: 500;">2 min read</div>
						</article>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- 2. CENTER LIST & THUMBNAILS COLUMN (~38% width) -->
			<div class="ai-cat-center-col" style="flex: 1.3; min-width: 300px; box-sizing: border-box;">
				<?php $col2_text = array_slice( $posts, 0, 3 ); $col2_thumb = array_slice( $posts, 3, 2 ); ?>
				<!-- Text-Only List Items -->
				<div>
					<?php foreach ( $col2_text as $t_item ) : ?>
						<article style="padding-bottom: 16px; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0;">
							<div style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px;">
								<?php echo esc_html( mb_strtoupper( $cat_name, 'UTF-8' ) ); ?>
							</div>
							<h4 style="font-size: 1.05rem; font-weight: 700; line-height: 1.35; margin: 0 0 6px 0;">
								<a href="<?php echo esc_url( $t_item['link'] ); ?>" style="color: #0f172a; text-decoration: none;" class="ai-news-title-link"><?php echo esc_html( $t_item['title'] ); ?></a>
							</h4>
							<div style="font-size: 11px; color: #94a3b8; font-weight: 500;">3 min read</div>
						</article>
					<?php endforeach; ?>
				</div>

				<!-- Small Right-Thumbnail List Items -->
				<div>
					<?php foreach ( $col2_thumb as $th_item ) : ?>
						<article style="display: flex; gap: 14px; padding-bottom: 16px; margin-bottom: 16px; border-bottom: 1px solid #e2e8f0; align-items: flex-start;">
							<div style="flex: 1; min-width: 0;">
								<div style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px;">
									<?php echo esc_html( mb_strtoupper( $cat_name, 'UTF-8' ) ); ?>
								</div>
								<h4 style="font-size: 0.95rem; font-weight: 700; line-height: 1.35; margin: 0 0 6px 0;">
									<a href="<?php echo esc_url( $th_item['link'] ); ?>" style="color: #0f172a; text-decoration: none;" class="ai-news-title-link"><?php echo esc_html( $th_item['title'] ); ?></a>
								</h4>
								<div style="font-size: 11px; color: #94a3b8; font-weight: 500;">3 min read</div>
							</div>
							<a href="<?php echo esc_url( $th_item['link'] ); ?>" style="display: block; width: 88px; height: 64px; min-width: 88px; border-radius: 8px; overflow: hidden; background: #f1f5f9; flex-shrink: 0;">
								<img src="<?php echo esc_url( $th_item['image'] ); ?>" alt="<?php echo esc_attr( $th_item['title'] ); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease;" class="ai-zoom-img" />
							</a>
						</article>
					<?php endforeach; ?>
				</div>
			</div>

			<!-- 3. RIGHT SIDEBAR ADVERTISEMENT COLUMN (~26% width) -->
			<div class="ai-cat-ad-col" style="width: 270px; min-width: 250px; box-sizing: border-box;">
				<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 14px; text-align: center; box-sizing: border-box; position: sticky; top: 90px;">
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
function aikairali_render_single_category_news_view( $term ) {
	$cat_name = $term->name;
	$cat_slug = $term->slug;
	?>
	<!-- Category Archive Header Banner -->
	<header class="archive-header" style="margin-bottom: 35px; border-bottom: 2px solid #f1f5f9; padding-bottom: 20px;">
		<div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
			<span style="width: 4px; height: 26px; background: #2563eb; border-radius: 2px;"></span>
			<h1 style="font-size: 26px; font-weight: 600; color: #0f172a; margin: 0; line-height: 1.2;">
				<?php printf( esc_html__( 'AI News: %s', 'aikairali-portal' ), esc_html( $cat_name ) ); ?>
			</h1>
		</div>
		<p style="color: #64748b; font-size: 16px; margin: 0;">
			<?php printf( esc_html__( 'Browse all artificial intelligence news and articles in %s.', 'aikairali-portal' ), esc_html( $cat_name ) ); ?>
		</p>
	</header>

	<div class="ai-news-archive-layout" style="display: flex; gap: 40px; align-items: flex-start; flex-wrap: wrap;">
		<main class="ai-news-main-content" style="flex: 1; min-width: 320px;">
			<?php
			$paged      = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
			$cat_query  = new WP_Query( [
				'post_type'      => [ 'post', 'ai-news' ],
				'category_name'  => $cat_slug,
				'post_status'    => 'publish',
				'posts_per_page' => 10,
				'paged'          => $paged,
			] );

			$fallback_items = aikairali_get_fallback_posts_for_category( $cat_slug, $cat_name );
			?>

			<div class="ai-news-list-stream">
				<?php
				if ( $cat_query->have_posts() ) :
					while ( $cat_query->have_posts() ) : $cat_query->the_post();
						aikairali_render_news_list_item( get_the_ID() );
					endwhile;
					wp_reset_postdata();
				else :
					foreach ( $fallback_items as $fitem ) :
						aikairali_render_news_default_item( $fitem );
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
					<h3 style="font-size: 13px; font-weight: 800; color: #0f172a; margin: 0; text-transform: uppercase; letter-spacing: 0.06em;">RECENT NEWS</h3>
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
 * Return customized Malayalam news fallback items per category
 */
function aikairali_get_fallback_posts_for_category( $slug, $cat_name ) {
	$map = [
		'generative-ai' => [
			[
				'title' => 'ഓപ്പൺഎഐ ജിപിടി-5 സിഇഒ സാം ആൾട്ട്മാൻ പ്രഖ്യാപനം; നിർമ്മൽ ബുദ്ധിരംഗത്ത് വൻ വിപ്ലവം',
				'excerpt' => 'സാം ആൾട്ട്മാൻ നയിക്കുന്ന ഓപ്പൺഎഐ അടുത്ത തലമുറയിലെ വൻകിട ഭാഷാ മോഡലായ ജിപിടി-5 ഔദ്യോഗികമായി അവതരിപ്പിച്ചു...',
				'image' => 'https://images.unsplash.com/photo-1677442136019-21780efad99a?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'ഗൂഗിൾ ജെമിനി 1.5 പ്രോ പുതിയ അപ്‌ഡേറ്റ് മലയാളം ഭാഷാ പിന്തുണ വർദ്ധിപ്പിച്ചു',
				'excerpt' => 'ഗൂഗിളിന്റെ അത്യാധുനിക എഐ സിസ്റ്റമായ ജെമിനി 1.5 പ്രോ ഇന്ത്യയിലെ പ്രാദേശിക ഭാഷകളിൽ പ്രോസസ്സിംഗ് മെച്ചപ്പെടുത്തി...',
				'image' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'ക്ലോഡ് 3.5 സോണറ്റ് ആർട്ടിഫിഷ്യൽ ഇന്റലിജൻസ് രംഗത്ത് പുതിയ ചരിത്രം സൃഷ്ടിക്കുന്നു',
				'excerpt' => 'ആന്ത്രോപിക് വികസിപ്പിച്ച ക്ലോഡ് 3.5 സോണറ്റ് കോഡിംഗിലും ലോജിക്കൽ റീസണിംഗിലും ഉയർന്ന സ്കോർ നേടി...',
				'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'മിഡ്‌ജേർണി v6.1 പുതിയ റിയലിസ്റ്റിക് ഇമേജ് ജനറേഷൻ ടൂൾ പുറത്തിറക്കി',
				'excerpt' => 'ഫോട്ടോഗ്രാഫിക് ക്വാളിറ്റിയുള്ള ഡിജിറ്റൽ ചിത്രങ്ങൾ നിർമ്മിക്കാൻ മിഡ്‌ജേർണിയുടെ പുതിയ അപ്‌ഡേറ്റ് സജ്ജമായി...',
				'image' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'മെറ്റാ Llama 3.1 ഓപ്പൺ സോഴ്സ് എഐ മോഡൽ സൗജന്യമായി ലഭ്യമാക്കി',
				'excerpt' => 'ലോകമെമ്പാടുമുള്ള ഡെവലപ്പർമാർക്കായി മെറ്റാ തങ്ങളുടെ ശക്തമായ Llama 3.1 മോഡലുകൾ തുറന്നുകൊടുത്തു...',
				'image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=600&auto=format&fit=crop',
			],
		],
		'india-ai' => [
			[
				'title' => 'ഇന്ത്യ എഐ മിഷൻ: ഡിജിറ്റൽ ഇന്ത്യ പദ്ധതിക്ക് 10,000 കോടി രൂപയുടെ സൂപ്പർ കമ്പ്യൂട്ടർ ഇൻഫ്രാസ്ട്രക്ചർ',
				'excerpt' => 'ഭാരതത്തിൽ സ്വന്തമായി എഐ വികസനം ശക്തമാക്കുന്നതിന് കേന്ദ്ര സർക്കാർ 10,000 കോടി രൂപയുടെ എഐ മിഷൻ പദ്ധതി ആരംഭിച്ചു...',
				'image' => 'https://images.unsplash.com/photo-1532375810709-75b1da00537c?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'ഐഐടി മദ്രാസ് വികസിപ്പിച്ച പുതിയ മലയാളം എഐ ഭാഷാ മോഡൽ പുറത്തിറക്കി',
				'excerpt' => 'മലയാളം ഭാഷാ സംസ്കരണത്തിന് ഐഐടി മദ്രാസിലെ ഗവേഷകർ വികസിപ്പിച്ച തദ്ദേശീയ എഐ ടൂൾ ജനപ്രിയമാകുന്നു...',
				'image' => 'https://images.unsplash.com/photo-1524178232363-1fb2b075b655?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'ബെംഗളൂരുവിൽ പുതിയ എഐ ഗവേഷണ കേന്ദ്രം തുറന്ന് മൈക്രോസോഫ്റ്റ്',
				'excerpt' => 'ഇന്ത്യയിലെ തദ്ദേശീയ സാങ്കേതിക വികസനത്തിനായി മൈക്രോസോഫ്റ്റ് പുതിയ എഐ ഗവേഷണ ശാല ആരംഭിച്ചു...',
				'image' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'ഇന്ത്യൻ ഭാഷകൾക്കായി ഭാഷിണി എഐ വോയിസ് ട്രാൻസ്ലേഷൻ സർവീസ് സജ്ജമാക്കി',
				'excerpt' => 'ഭാരതത്തിലെ 22 ഭാഷകളിൽ തത്സമയ ശബ്ദ പരിഭാഷ സാധ്യമാക്കുന്ന ഭാരത സർക്കാറിന്റെ ഭാഷിണി പദ്ധതി...',
				'image' => 'https://images.unsplash.com/photo-1517245386807-bb43f82c33c4?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'കേരളത്തിൽ എഐ അധിഷ്ഠിത സ്റ്റാർട്ടപ്പുകൾക്ക് വൻ സഹായ പദ്ധതി പ്രഖ്യാപിച്ച് സ്റ്റാർട്ടപ്പ് മിഷൻ',
				'excerpt' => 'കേരള സ്റ്റാർട്ടപ്പ് മിഷൻ പുതുസംരംഭകർക്കായി പ്രത്യേക എഐ ഇൻക്യുബേഷൻ സഹായം നൽകുന്നു...',
				'image' => 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=600&auto=format&fit=crop',
			],
		],
		'research-innovation' => [
			[
				'title' => 'ക്വാണ്ടം കമ്പ്യൂട്ടിംഗും എഐയും സംയോജിപ്പിച്ച് പുതിയ സൂപ്പർഅൽഗോരിതം വികസിപ്പിച്ച് ഗവേഷകർ',
				'excerpt' => 'സങ്കീർണ്ണമായ ഭൗതികശാസ്ത്ര കണക്കുകൂട്ടലുകൾ നിമിഷങ്ങൾക്കുള്ളിൽ സാധ്യമാക്കുന്ന പുതിയ മുന്നേറ്റം...',
				'image' => 'https://images.unsplash.com/photo-1507413245164-6160d8298b31?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'മസ്തിഷ്ക തരംഗങ്ങളെ നേരിട്ട് വാചകങ്ങളാക്കി മാറ്റുന്ന പുതിയ ന്യൂറൽ എഐ ബ്രേക്ക്ത്രൂ',
				'excerpt' => 'സംസാരശേഷി നഷ്ടപ്പെട്ട രോഗികൾക്ക് വലിയ പ്രതീക്ഷ നൽകുന്ന ന്യൂറോടെക്നോളജി മുന്നേറ്റം...',
				'image' => 'https://images.unsplash.com/photo-1559757175-5700dde675bc?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'സൗരോർജ്ജ പാനലുകളുടെ കാര്യക്ഷമത 40% വർദ്ധിപ്പിക്കാൻ പുതിയ എഐ മോഡൽ',
				'excerpt' => 'ഹരിത ഊർജ്ജ വികസനത്തിൽ വഴിത്തിരിവാകുന്ന എഐ ഇൻസെന്റീവ് സോളാർ മോഡലിംഗ്...',
				'image' => 'https://images.unsplash.com/photo-1509391365360-2e959784a276?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'ബഹിരാകാശ ഗവേഷണത്തിന് നാസയുടെ പുതിയ സ്വയംഭരണ എഐ റോവർ സജ്ജമായി',
				'excerpt' => 'ചൊവ്വാ ദൗത്യത്തിനായി ഗ്രഹോപരിതലങ്ങൾ തനിയെ വിശകലനം ചെയ്യാൻ കഴിവുള്ള പുതിയ നാസ റോവർ...',
				'image' => 'https://images.unsplash.com/photo-1614728894747-a83421e2b9c9?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'കാലാവസ്ഥാ വ്യതിയാനം കൃത്യമായി പ്രവചിക്കുന്നതിന് ദീർഘവീക്ഷണ എഐ മോഡൽ',
				'excerpt' => 'പ്രകൃതിദുരന്തങ്ങൾ നേരത്തെ പ്രവചിക്കുന്നതിന് എഐ അധിഷ്ഠിത കാലാവസ്ഥാ മാപ്പിംഗ്...',
				'image' => 'https://images.unsplash.com/photo-1590055531615-f16d36ffe8ec?w=600&auto=format&fit=crop',
			],
		],
		'robotics' => [
			[
				'title' => 'ടെസ്‌ല ഹ്യൂമനോയിഡ് റോബോട്ട് ഒപ്റ്റിമസ് പുതിയ വീട്ടുകാര്യങ്ങൾ ചെയ്യാൻ സജ്ജമെന്ന് ഇലോൺ മസ്ക്',
				'excerpt' => 'ടെസ്‌ലയുടെ മനുഷ്യതുല്യമായ റോബോട്ട് ഒപ്റ്റിമസ് വീട്ടുജോലികൾ ചെയ്യാനും ലഗേജുകൾ സൂക്ഷിക്കാനും തുടങ്ങി...',
				'image' => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'ബോസ്റ്റൺ ഡൈനാമിക്സ് പുതിയ ഇലക്ട്രിക് അറ്റ്‌ലസ് റോബോട്ട് നിർമ്മാണം പൂർത്തിയാക്കി',
				'excerpt' => 'സ്വാഭാവിക ചലനശേഷിയും അത്യാധുനിക എഐ പ്രോസസ്സറുമുള്ള പുതിയ തലമുറ അറ്റ്‌ലസ്...',
				'image' => 'https://images.unsplash.com/photo-1546776310-eef45dd6d63c?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'ശസ്ത്രക്രിയകൾ സുഗമമാക്കാൻ ആശുപത്രികളിൽ എഐ റോബോട്ടിക് അസിസ്റ്റന്റ്',
				'excerpt' => 'സങ്കീർണ്ണമായ സർജറികളിൽ ഡോക്ടർമാർക്ക് തുണയാകുന്ന മില്ലിമീറ്റർ അക്യുറസി റോബോട്ടുകൾ...',
				'image' => 'https://images.unsplash.com/photo-1584036561566-baf8f5f1b144?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'ദുരന്തനിവാരണ പ്രവർത്തനങ്ങൾക്ക് സ്വയംഭരണ റോബോട്ടിക് നായ്ക്കൾ',
				'excerpt' => 'അപകട മേഖലകളിൽ രക്ഷാപ്രവർത്തനം നടത്താൻ അത്യാധുനിക റോബോട്ടിക് ഡോഗ് ഡ്രോണുകൾ...',
				'image' => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=600&auto=format&fit=crop',
			],
			[
				'title' => 'വ്യാവസായിക നിർമ്മാണ രംഗത്ത് പാകപ്പെടുത്തിയ പുതിയ സഹകരണ റോബോട്ടുകൾ',
				'excerpt' => 'നിർമ്മാണ ശാലകളിൽ മനുഷ്യ തൊഴിലാളികൾക്കൊപ്പം സുരക്ഷിതമായി പ്രവർത്തിക്കുന്ന റോബോട്ടുകൾ...',
				'image' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=600&auto=format&fit=crop',
			],
		],
	];

	$default_items = [
		[
			'title' => sprintf( '%s മേഖലയിൽ പുതിയ എഐ മുന്നേറ്റങ്ങളും അപ്‌ഡേറ്റുകളും', $cat_name ),
			'excerpt' => 'ആർട്ടിഫിഷ്യൽ ഇന്റലിജൻസ് ലോകത്തെ അത്യാധുനിക സാങ്കേതിക വിദ്യകളെക്കുറിച്ചുള്ള സമഗ്ര റിപ്പോർട്ട്...',
			'image' => 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=600&auto=format&fit=crop',
		],
		[
			'title' => sprintf( '%s സാങ്കേതികവിദ്യ എങ്ങനെ ഭാവി മാറ്റിയെഴുതും?', $cat_name ),
			'excerpt' => 'സാങ്കേതിക രംഗത്ത് വൻ കുതിച്ചുചാട്ടമുണ്ടാക്കുന്ന എഐ ടൂളുകളുടെ വിശകലനം...',
			'image' => 'https://images.unsplash.com/photo-1516321318423-f06f85e504b3?w=600&auto=format&fit=crop',
		],
		[
			'title' => sprintf( '%s രംഗത്ത് പുതിയ തദ്ദേശീയ എഐ ടൂളുകൾ പ്രഖ്യാപിച്ചു', $cat_name ),
			'excerpt' => 'ഉപഭോക്താക്കൾക്കും സ്ഥാപനങ്ങൾക്കും ഒരുപോലെ പ്രയോജനപ്പെടുന്ന പുതിയ സേവനങ്ങൾ...',
			'image' => 'https://images.unsplash.com/photo-1555066931-4365d14bab8c?w=600&auto=format&fit=crop',
		],
		[
			'title' => sprintf( '%s പ്രോസസ്സിംഗിനായി അത്യാധുനിക അൽഗോരിതങ്ങൾ', $cat_name ),
			'excerpt' => 'ആഗോള തലത്തിൽ ശ്രദ്ധിക്കപ്പെടുന്ന പുതിയ ഗവേഷണ പേപ്പറുകളും കണ്ടെത്തലുകളും...',
			'image' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=600&auto=format&fit=crop',
		],
		[
			'title' => sprintf( '%s ബിസിനസ്സ് സാധ്യതകളും വളർച്ചാ റിപ്പോർട്ടും', $cat_name ),
			'excerpt' => 'വരും വർഷങ്ങളിൽ ഈ രംഗത്തുണ്ടാകുന്ന നിക്ഷേപങ്ങളും വിപണി വളർച്ചയും...',
			'image' => 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=600&auto=format&fit=crop',
		],
	];

	$results = isset( $map[ $slug ] ) ? $map[ $slug ] : $default_items;

	foreach ( $results as &$it ) {
		$it['cat'] = mb_strtoupper( $cat_name, 'UTF-8' );
		if ( empty( $it['link'] ) || '#' === $it['link'] ) {
			$p = get_page_by_title( $it['title'], OBJECT, 'post' );
			if ( $p ) {
				$it['link'] = get_permalink( $p->ID );
			} else {
				$any = get_posts( [ 'numberposts' => 1, 'category_name' => $slug ] );
				if ( empty( $any ) ) {
					$any = get_posts( [ 'numberposts' => 1 ] );
				}
				if ( ! empty( $any ) ) {
					$it['link'] = get_permalink( $any[0]->ID );
				} else {
					$it['link'] = home_url( '/' );
				}
			}
		}
	}

	return $results;
}

/**
 * Helper function to render a single DB news item in stream list view
 */
function aikairali_render_news_list_item( $post_id ) {
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
		$image = 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=600&auto=format&fit=crop';
	}

	$categories = get_the_category( $post_id );
	$cat_name   = ( $categories && ! is_wp_error( $categories ) ) ? mb_strtoupper( $categories[0]->name, 'UTF-8' ) : 'AI NEWS';

	aikairali_render_news_default_item( [
		'title'   => $title,
		'excerpt' => $excerpt,
		'cat'     => $cat_name,
		'date'    => $date,
		'image'   => $image,
		'link'    => $link,
	] );
}

/**
 * Helper function to render a news item array structure matching stream design
 */
function aikairali_render_news_default_item( $item ) {
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
.ai-more-link:hover {
	color: #0f172a !important;
	text-decoration: underline !important;
}
.ai-news-title-link:hover {
	color: #2563eb !important;
}
.ai-cat-feature-card:hover .ai-zoom-img,
.ai-cat-small-card:hover .ai-zoom-img,
.ai-news-card-item:hover .ai-news-thumb-link img {
	transform: scale(1.05);
}
.ai-bookmark-btn:hover {
	color: #2563eb !important;
}
@media (max-width: 992px) {
	.ai-cat-grid-layout {
		flex-direction: column;
	}
	.ai-cat-ad-col {
		width: 100% !important;
	}
}
@media (max-width: 600px) {
	.ai-cat-center-col div {
		grid-template-columns: 1fr !important;
	}
	.ai-news-card-item {
		flex-direction: column;
	}
	.ai-news-thumb-link {
		width: 100% !important;
		min-width: 100% !important;
	}
}
</style>

<?php
get_footer();
