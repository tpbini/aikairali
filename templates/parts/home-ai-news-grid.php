<?php
/**
 * 3-Column AI News Section Layout Template Part.
 * Left Column: Big Featured News + 3 Headline Items below.
 * Center Column: 3 Text Items + 2 Small Thumbnail Items.
 * Right Column: Custom Advertisement Banner Slot (Image or Code input).
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Templates/Parts
 * @since      1.0.0
 */

$limit       = isset( $args['limit'] ) ? intval( $args['limit'] ) : 9;
$cat_slug    = isset( $args['cat_slug'] ) ? sanitize_text_field( $args['cat_slug'] ) : '';
$ad_image    = isset( $args['ad_image'] ) ? esc_url( $args['ad_image'] ) : '';
$ad_link     = isset( $args['ad_link'] ) ? esc_url( $args['ad_link'] ) : '#';
$ad_code     = isset( $args['ad_code'] ) ? $args['ad_code'] : '';

// Retrieve option setting for fallback ad code if not passed in shortcode
if ( empty( $ad_code ) && empty( $ad_image ) ) {
	$portal_settings = get_option( 'aikairali_portal_settings', [] );
	$ad_image = $portal_settings['ad_banner_image'] ?? '/wp-content/themes/twentytwentyfive/assets/images/ad-banner.png';
	$ad_link  = $portal_settings['ad_banner_link'] ?? '#';
	$ad_code  = $portal_settings['ad_banner_code'] ?? '';
}

// Query AI News posts
$query_args = [
	'post_type'      => [ 'post', 'ai-news' ],
	'posts_per_page' => $limit,
	'post_status'    => 'publish',
];

if ( ! empty( $cat_slug ) ) {
	$query_args['category_name'] = $cat_slug;
}

$news_query = new \WP_Query( $query_args );

$posts = [];
if ( $news_query->have_posts() ) {
	while ( $news_query->have_posts() ) {
		$news_query->the_post();
		$pid       = get_the_ID();
		$img       = get_the_post_thumbnail_url( $pid, 'medium_large' );
		$cats      = get_the_category( $pid );
		$cat_name  = ( ! empty( $cats ) && ! is_wp_error( $cats ) ) ? mb_strtoupper( $cats[0]->name, 'UTF-8' ) : 'TECHNOLOGY';
		$content   = get_post_field( 'post_content', $pid );
		$word_count = str_word_count( strip_tags( $content ) );
		$read_time = max( 1, ceil( $word_count / 200 ) ) . ' min read';

		if ( ! $img ) {
			$u_img = get_field( 'upload_image', $pid );
			$img   = is_array( $u_img ) ? ( $u_img['url'] ?? '' ) : ( is_string( $u_img ) ? $u_img : '' );
		}

		$posts[] = [
			'id'        => $pid,
			'title'     => get_the_title( $pid ),
			'link'      => get_permalink( $pid ),
			'excerpt'   => get_the_excerpt( $pid ) ?: wp_trim_words( $content, 22, '...' ),
			'image'     => $img ?: 'https://images.unsplash.com/photo-1618005182384-a83a8bd57fbe?w=600&auto=format&fit=crop',
			'cat'       => $cat_name,
			'read_time' => $read_time,
		];
	}
	wp_reset_postdata();
}

// Fallback Mock Posts if DB returns 0 items
if ( empty( $posts ) ) {
	$posts = [
		[
			'title'     => 'ക്ലൗഡ് ചരിത്രം തിരുത്താൻ അലിബാബ; ക്വെൻ (Qwen) എഐ മോഡലുകൾക്കായി റെവന്യൂ ഷെയറിംഗ് തന്ത്രം പരീക്ഷിക്കുന്നു',
			'excerpt'   => 'അലിബാബ തങ്ങളുടെ ക്വെൻ (Qwen) എഐ മോഡലുകളിൽ നിന്ന് വരുമാനം കണ്ടെത്താൻ റെവന്യൂ ഷെയറിംഗ് തന്ത്രം പരീക്ഷിക്കുന്നു. പൂർണ്ണ വിവരങ്ങൾ വായിക്കൂ.',
			'image'     => 'https://images.unsplash.com/photo-1506744038136-46273834b3fb?w=800&auto=format&fit=crop',
			'cat'       => 'TECHNOLOGY',
			'read_time' => '4 min read',
			'link'      => home_url( '/ai-news/' ),
		],
		[
			'title'     => 'ഗണിതശാസ്ത്രത്തിൽ പുതിയ ചരിത്രം കുറിച്ച് ഓപ്പൺഎഐ ‘ആസ്ട്ര’ (Astra); പതിറ്റാണ്ടുകളായി പൂട്ടിപ്പാലിച്ച സമസ്യകൾക്ക് എഐ പരിഹാരം',
			'excerpt'   => 'സങ്കീർണ്ണമായ ഗണിത സമസ്യകൾ മിനിറ്റുകൾക്കുള്ളിൽ നിർദ്ധാരണം ചെയ്ത് ഓപ്പൺഎഐയുടെ പുതിയ സംവിധാനം.',
			'image'     => 'https://images.unsplash.com/photo-1635070041078-e363dbe005cb?w=600&auto=format&fit=crop',
			'cat'       => 'TECHNOLOGY',
			'read_time' => '2 min read',
			'link'      => home_url( '/ai-news/' ),
		],
		[
			'title'     => 'പ്രകൃതിയിലില്ലാത്ത പുതിയ വൈറസുകൾ നിർമ്മിച്ച് നിർമ്മിതബുദ്ധി; ശാസ്ത്രലോകത്ത് വിപ്ലവകരമായ മുന്നേറ്റവും ആശങ്കയും',
			'excerpt'   => 'ബയോടെക്നോളജി രംഗത്ത് പുതിയ തരംഗമാകാൻ സാധിക്കുന്ന പുതിയ തന്മാത്രാ ഘടനകൾ എഐ രൂപകൽപ്പന ചെയ്തു.',
			'image'     => 'https://images.unsplash.com/photo-1532187863486-abf9dbad1b69?w=600&auto=format&fit=crop',
			'cat'       => 'TECHNOLOGY',
			'read_time' => '2 min read',
			'link'      => home_url( '/ai-news/' ),
		],
		[
			'title'     => 'എഐ അറിയാത്ത ഒരാളുടെ നാളത്തെ ജീവിതം എങ്ങനെയാകും?',
			'excerpt'   => 'ഡിജിറ്റൽ യുഗത്തിൽ നിർമ്മിത ബുദ്ധി സൃഷ്ടിക്കുന്ന തൊഴിൽ മാറ്റങ്ങളും സാമൂഹിക മാറ്റങ്ങളും.',
			'image'     => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=600&auto=format&fit=crop',
			'cat'       => 'AI',
			'read_time' => '3 min read',
			'link'      => home_url( '/ai-news/' ),
		],
		[
			'title'     => 'ക്ലൗഡ് എഐ അതിരുകടന്നു; സൈബർ പരീക്ഷണത്തിനിടെ കമ്പനികളെ ഹാക്ക് ചെയ്ത് ആൻത്രോപിക്',
			'excerpt'   => 'എഐ സിസ്റ്റം സ്വയം കോഡിംഗ് സുരക്ഷ പരിശോധിക്കുമ്പോൾ ഉണ്ടായ ശ്രദ്ധേയമായ സംഭവവികാസങ്ങൾ.',
			'image'     => 'https://images.unsplash.com/photo-1526374965328-7f61d4dc18c5?w=600&auto=format&fit=crop',
			'cat'       => 'TECHNOLOGY',
			'read_time' => '5 min read',
			'link'      => home_url( '/ai-news/' ),
		],
		[
			'title'     => 'OpenAI GPT-5.6 മോഡലുകളുടെ നിരക്കുകൾ വെട്ടിക്കുറച്ചു; API ചെലവിൽ വൻ ഇളവ്',
			'excerpt'   => 'ഡെവലപ്പർമാർക്ക് കുറഞ്ഞ നിരക്കിൽ എഐ കോൾ സേവനങ്ങൾ ലഭ്യമാക്കാനായി പുതിയ പ്രൈസിംഗ് വിപ്ലവം.',
			'image'     => 'https://images.unsplash.com/photo-1677442136019-21780efad99a?w=600&auto=format&fit=crop',
			'cat'       => 'AI TOOLS',
			'read_time' => '2 min read',
			'link'      => home_url( '/ai-news/' ),
		],
		[
			'title'     => 'ഗൂഗിൾ എർത്തിൽ വ്യാജ ഉപഗ്രഹ ചിത്രങ്ങൾ നിർമ്മിക്കാം; വിവാദത്തിന് പിന്നാലെ പുതിയ എഐ ഫീച്ചർ അടിയന്തരമായി പിൻവലിച്ച് ഗൂഗിൾ',
			'excerpt'   => 'ഉപഗ്രഹ ചിത്രങ്ങൾ കൃത്രിമമായി സൃഷ്ടിക്കാൻ സാധിക്കുന്ന മോഡലിൽ സുരക്ഷാ ഭീഷണി.',
			'image'     => 'https://images.unsplash.com/photo-1526778548025-fa2f459cd5c1?w=600&auto=format&fit=crop',
			'cat'       => 'TECHNOLOGY',
			'read_time' => '4 min read',
			'link'      => home_url( '/ai-news/' ),
		],
		[
			'title'     => '2026-ൽ AI സൈബർ സുരക്ഷയെ മാറ്റിമറിക്കുന്നത് എങ്ങനെ?',
			'excerpt'   => 'ഓട്ടോമേറ്റഡ് ത്രെറ്റ് ഡിറ്റക്ഷനും തത്സമയ സുരക്ഷാ പ്രതിരോധ സംവിധാനങ്ങളും.',
			'image'     => 'https://images.unsplash.com/photo-1550751827-4bd374c3f58b?w=600&auto=format&fit=crop',
			'cat'       => 'TECHNOLOGY NEWS | CYBERSECURITY',
			'read_time' => '3 min read',
			'link'      => home_url( '/ai-news/' ),
		],
		[
			'title'     => 'വികസിത എഐയെ സർക്കാരുകൾ മന്ദഗതിയിലാക്കണോ?',
			'excerpt'   => 'നിർമ്മിത ബുദ്ധി നിയന്ത്രണ നിയമങ്ങളും ആഗോള നയരൂപീകരണ ചർച്ചകളും.',
			'image'     => 'https://images.unsplash.com/photo-1573164713988-8665fc963095?w=600&auto=format&fit=crop',
			'cat'       => 'TECHNOLOGY POLICY',
			'read_time' => '3 min read',
			'link'      => home_url( '/ai-news/' ),
		],
	];
}

// Separate array slices for the 3-column layout
$col1_hero       = $posts[0];
$col1_sub_items  = array_slice( $posts, 1, 3 );
$col2_text_items = array_slice( $posts, 4, 3 );
$col2_thumb_items= array_slice( $posts, 7, 2 );
?>

<div class="aik-portal-3col-news-layout" style="max-width: 1200px; margin: 40px auto; padding: 0 20px; box-sizing: border-box; font-family: 'Anek Malayalam', system-ui, -apple-system, sans-serif;">

	<!-- Section Header -->
	<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 12px; border-bottom: 2px solid #0f172a;">
		<div style="display: flex; align-items: center; gap: 8px;">
			<span style="font-weight: 900; color: #dc2626; font-size: 18px; line-height: 1;">!</span>
			<h2 style="font-size: 18px; font-weight: 800; color: #0f172a; margin: 0; text-transform: uppercase; letter-spacing: 0.05em;">
				<a href="<?php echo esc_url( home_url( '/ai-news/' ) ); ?>" style="color: #0f172a; text-decoration: none;"><?php esc_html_e( 'AI NEWS', 'aikairali-portal' ); ?></a>
			</h2>
		</div>
		<a href="<?php echo esc_url( home_url( '/ai-news/' ) ); ?>" style="font-size: 12px; font-weight: 800; color: #2563eb; text-decoration: none; text-transform: uppercase; letter-spacing: 0.05em; display: inline-flex; align-items: center; gap: 4px;" class="ai-more-link">
			<?php esc_html_e( 'MORE FROM AI NEWS &raquo;&raquo;', 'aikairali-portal' ); ?>
		</a>
	</div>

	<!-- 3-Column Grid Container -->
	<div style="display: flex; gap: 32px; flex-wrap: wrap; align-items: flex-start;" class="aik-3col-grid-wrapper">

		<!-- COLUMN 1: LEFT HERO & TOP HEADLINES (~36% Width) -->
		<div style="flex: 1.2; min-width: 300px; box-sizing: border-box;" class="aik-col-left">
			<article style="margin-bottom: 20px;">
				<!-- Category Tag -->
				<div style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 8px;">
					<?php echo esc_html( $col1_hero['cat'] ); ?>
				</div>
				<!-- Hero Title -->
				<h3 style="font-size: 1.35rem; font-weight: 800; line-height: 1.3; color: #0f172a; margin: 0 0 14px 0;">
					<a href="<?php echo esc_url( $col1_hero['link'] ); ?>" style="color: #0f172a; text-decoration: none;" class="ai-news-title-link">
						<?php echo esc_html( $col1_hero['title'] ); ?>
					</a>
				</h3>
				<!-- Hero Image -->
				<a href="<?php echo esc_url( $col1_hero['link'] ); ?>" style="display: block; width: 100%; aspect-ratio: 16/10; overflow: hidden; border-radius: 10px; background: #f1f5f9; margin-bottom: 14px;">
					<img src="<?php echo esc_url( $col1_hero['image'] ); ?>" alt="<?php echo esc_attr( $col1_hero['title'] ); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease;" class="ai-zoom-img" />
				</a>
				<!-- Excerpt -->
				<p style="font-size: 0.925rem; color: #475569; line-height: 1.55; margin: 0 0 10px 0;">
					<?php echo esc_html( $col1_hero['excerpt'] ); ?>
				</p>
				<!-- Read Time -->
				<div style="font-size: 11px; color: #94a3b8; font-weight: 500;">
					<?php echo esc_html( $col1_hero['read_time'] ); ?>
				</div>
			</article>

			<!-- Sub Headline Items below Left Hero -->
			<div style="border-top: 1px solid #e2e8f0; padding-top: 16px;">
				<?php foreach ( $col1_sub_items as $sub_item ) : ?>
					<article style="padding-bottom: 16px; margin-bottom: 16px; border-bottom: 1px solid #f1f5f9;">
						<div style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px;">
							<?php echo esc_html( $sub_item['cat'] ); ?>
						</div>
						<h4 style="font-size: 0.95rem; font-weight: 700; line-height: 1.35; margin: 0 0 6px 0;">
							<a href="<?php echo esc_url( $sub_item['link'] ); ?>" style="color: #0f172a; text-decoration: none;" class="ai-news-title-link">
								<?php echo esc_html( $sub_item['title'] ); ?>
							</a>
						</h4>
						<div style="font-size: 11px; color: #94a3b8; font-weight: 500;">
							<?php echo esc_html( $sub_item['read_time'] ); ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>
		</div>

		<!-- COLUMN 2: CENTER STREAM & THUMBNAIL ITEMS (~38% Width) -->
		<div style="flex: 1.3; min-width: 310px; box-sizing: border-box;" class="aik-col-center">
			
			<!-- Top Text-Only Items -->
			<div>
				<?php foreach ( $col2_text_items as $text_item ) : ?>
					<article style="padding-bottom: 18px; margin-bottom: 18px; border-bottom: 1px solid #e2e8f0;">
						<div style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px;">
							<?php echo esc_html( $text_item['cat'] ); ?>
						</div>
						<h4 style="font-size: 1.05rem; font-weight: 700; line-height: 1.35; margin: 0 0 6px 0;">
							<a href="<?php echo esc_url( $text_item['link'] ); ?>" style="color: #0f172a; text-decoration: none;" class="ai-news-title-link">
								<?php echo esc_html( $text_item['title'] ); ?>
							</a>
						</h4>
						<div style="font-size: 11px; color: #94a3b8; font-weight: 500;">
							<?php echo esc_html( $text_item['read_time'] ); ?>
						</div>
					</article>
				<?php endforeach; ?>
			</div>

			<!-- Bottom Items with Right Thumbnail -->
			<div>
				<?php foreach ( $col2_thumb_items as $thumb_item ) : ?>
					<article style="display: flex; gap: 16px; padding-bottom: 18px; margin-bottom: 18px; border-bottom: 1px solid #e2e8f0; align-items: flex-start;">
						<div style="flex: 1; min-width: 0;">
							<div style="font-size: 11px; font-weight: 800; color: #475569; text-transform: uppercase; letter-spacing: 0.06em; margin-bottom: 4px;">
								<?php echo esc_html( $thumb_item['cat'] ); ?>
							</div>
							<h4 style="font-size: 0.95rem; font-weight: 700; line-height: 1.35; margin: 0 0 6px 0;">
								<a href="<?php echo esc_url( $thumb_item['link'] ); ?>" style="color: #0f172a; text-decoration: none;" class="ai-news-title-link">
									<?php echo esc_html( $thumb_item['title'] ); ?>
								</a>
							</h4>
							<div style="font-size: 11px; color: #94a3b8; font-weight: 500;">
								<?php echo esc_html( $thumb_item['read_time'] ); ?>
							</div>
						</div>
						<a href="<?php echo esc_url( $thumb_item['link'] ); ?>" style="display: block; width: 90px; height: 68px; min-width: 90px; border-radius: 8px; overflow: hidden; background: #f1f5f9; flex-shrink: 0;">
							<img src="<?php echo esc_url( $thumb_item['image'] ); ?>" alt="<?php echo esc_attr( $thumb_item['title'] ); ?>" style="width: 100%; height: 100%; object-fit: cover; transition: transform 0.35s ease;" class="ai-zoom-img" />
						</a>
					</article>
				<?php endforeach; ?>
			</div>

		</div>

		<!-- COLUMN 3: RIGHT ADVERTISEMENT SLOT (~26% Width) -->
		<div style="width: 280px; min-width: 260px; box-sizing: border-box;" class="aik-col-right-ad">
			<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 14px; padding: 16px; text-align: center; box-sizing: border-box; position: sticky; top: 90px;">
				<div style="font-size: 10px; font-weight: 800; color: #94a3b8; text-transform: uppercase; letter-spacing: 0.1em; margin-bottom: 12px;">
					ADVERTISEMENT
				</div>

				<?php if ( ! empty( $ad_code ) ) : ?>
					<!-- Custom Ad Code HTML / AdSense Script -->
					<div class="aik-custom-ad-code-wrap">
						<?php echo $ad_code; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
				<?php else : ?>
					<!-- Image Advertisement Banner -->
					<a href="<?php echo esc_url( $ad_link ); ?>" target="_blank" rel="noopener" style="display: block; text-decoration: none; margin-bottom: 14px;">
						<img src="<?php echo esc_url( $ad_image ); ?>" alt="Advertisement Banner" style="width: 100%; height: auto; border-radius: 8px; display: block; box-shadow: 0 4px 12px rgba(0,0,0,0.06);" />
					</a>
				<?php endif; ?>

				<div>
					<a href="#" style="display: inline-block; background: #ffffff; color: #dc2626; border: 1px solid #fca5a5; padding: 6px 16px; border-radius: 20px; font-size: 10px; font-weight: 800; text-decoration: none; text-transform: uppercase; letter-spacing: 0.05em;">
						GO AD-FREE
					</a>
				</div>
			</div>
		</div>

	</div>
</div>

<style>
.ai-more-link:hover { color: #0f172a !important; text-decoration: underline !important; }
.ai-news-title-link:hover { color: #2563eb !important; }
.ai-zoom-img:hover { transform: scale(1.05); }

@media (max-width: 992px) {
	.aik-3col-grid-wrapper { flex-direction: column; }
	.aik-col-right-ad { width: 100% !important; }
	.aik-col-right-ad > div { position: static !important; }
}
</style>
