<?php
/**
 * Single Template for AI Models.
 * Matching the exact Portal Detail Layout.
 *
 * @package AIKairali_Portal
 */

get_header();

// ACF Fields
$version           = get_field( 'version' );
$release_date      = get_field( 'release_date' );
$pricing           = get_field( 'pricing' );
$open_source       = get_field( 'open_source' );
$context_window    = get_field( 'context_window' );
$parameters        = get_field( 'parameters' );
$documentation_url = get_field( 'documentation_url' );
$benchmarks        = get_field( 'benchmarks' );
?>

<div class="aikairali-detail-container" style="max-width: 1200px; margin: 0 auto; padding: 40px 20px; font-family: system-ui, -apple-system, sans-serif;">
	<?php while ( have_posts() ) : the_post(); ?>
		
		<!-- 1. Top Headline Header -->
		<header style="margin-bottom: 30px; max-width: 900px;">
			<h1 style="font-size: 2.2rem; font-weight: 800; line-height: 1.3; color: #0f172a; margin-bottom: 12px;">
				<?php the_title(); ?> <?php echo $version ? 'v' . esc_html($version) : ''; ?>
			</h1>
			<div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.875rem; color: #64748b; border-bottom: 1px solid #e2e8f0; padding-bottom: 16px;">
				<div>
					<strong><?php echo esc_html( get_the_author() ); ?></strong>
				</div>
				<div>
					<span>Last Updated: <?php echo esc_html( get_the_modified_date( 'd F Y, h:i A' ) ); ?></span>
				</div>
			</div>
		</header>

		<!-- 2. Main 2-Column Grid -->
		<div style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 48px;">
			
			<!-- Left Column: Main Content -->
			<main>
				<!-- Hero Featured Image -->
				<?php if ( has_post_thumbnail() ) : ?>
					<div style="margin-bottom: 30px; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 10px rgba(0,0,0,0.05);">
						<?php the_post_thumbnail( 'large', [ 'style' => 'width: 100%; height: auto; display: block;' ] ); ?>
					</div>
				<?php endif; ?>

				<!-- Excerpt / Intro -->
				<?php if ( has_excerpt() ) : ?>
					<div style="font-size: 1.125rem; line-height: 1.8; color: #334155; margin-bottom: 30px;">
						<?php the_excerpt(); ?>
					</div>
				<?php endif; ?>

				<!-- Structured Specification Box -->
				<div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; margin: 32px 0;">
					<div style="font-size: 0.75rem; font-weight: 700; color: #94a3b8; letter-spacing: 1px; text-transform: uppercase; margin-bottom: 4px;">
						AI MODEL SPECIFICATIONS
					</div>
					<h2 style="font-size: 1.75rem; font-weight: 800; color: #0f172a; margin: 0 0 20px 0;">
						<?php echo esc_html( $pricing ?: 'API Pricing & Specs' ); ?>
					</h2>

					<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px 24px; font-size: 0.925rem;">
						<?php if ( $version ) : ?>
						<div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
							<span style="color: #64748b;">Version:</span>
							<strong><?php echo esc_html( $version ); ?></strong>
						</div>
						<?php endif; ?>

						<div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
							<span style="color: #64748b;">Open Source:</span>
							<strong style="color: <?php echo $open_source ? '#16a34a' : '#dc2626'; ?>;">
								<?php echo $open_source ? 'Yes' : 'No (Proprietary)'; ?>
							</strong>
						</div>

						<?php if ( $context_window ) : ?>
						<div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
							<span style="color: #64748b;">Context Window:</span>
							<strong><?php echo esc_html( $context_window ); ?></strong>
						</div>
						<?php endif; ?>

						<?php if ( $parameters ) : ?>
						<div style="display: flex; justify-content: space-between; border-bottom: 1px solid #e2e8f0; padding-bottom: 8px;">
							<span style="color: #64748b;">Parameters:</span>
							<strong><?php echo esc_html( $parameters ); ?></strong>
						</div>
						<?php endif; ?>
					</div>

					<?php if ( $documentation_url ) : ?>
						<div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #e2e8f0;">
							<a href="<?php echo esc_url( $documentation_url ); ?>" target="_blank" rel="noopener" style="display: inline-block; background: #0284c7; color: #ffffff; font-weight: 700; padding: 10px 22px; border-radius: 8px; text-decoration: none;">
								Documentation & API Docs →
							</a>
						</div>
					<?php endif; ?>
				</div>

				<!-- Article Body Content -->
				<div class="entry-content" style="font-size: 1.05rem; line-height: 1.8; color: #334155; margin: 30px 0;">
					<?php the_content(); ?>
				</div>

				<!-- Bottom Section: LATEST FROM THIS SECTION -->
				<section style="margin-top: 48px; padding-top: 32px; border-top: 1px solid #e2e8f0;">
					<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
						<h3 style="font-size: 1rem; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; color: #0f172a; margin: 0;">
							LATEST FROM THIS SECTION
						</h3>
						<a href="<?php echo esc_url( get_post_type_archive_link( get_post_type() ) ); ?>" style="font-size: 0.85rem; font-weight: 700; color: #2563eb; text-decoration: none;">
							View All →
						</a>
					</div>
					<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px;">
						<?php
						$related = new WP_Query([
							'post_type'      => get_post_type(),
							'posts_per_page' => 3,
							'post__not_in'   => [ get_the_ID() ]
						]);
						while ( $related->have_posts() ) : $related->the_post();
						?>
							<article style="border: 1px solid #e2e8f0; border-radius: 12px; overflow: hidden; background: #fff;">
								<?php if ( has_post_thumbnail() ) : ?>
									<div style="height: 140px; overflow: hidden;">
										<?php the_post_thumbnail( 'medium', [ 'style' => 'width:100%; height:100%; object-fit:cover;' ] ); ?>
									</div>
								<?php endif; ?>
								<div style="padding: 14px;">
									<span style="font-size: 10px; font-weight: 800; color: #2563eb; text-transform: uppercase;">
										AI MODEL
									</span>
									<h4 style="font-size: 0.9rem; font-weight: 700; margin: 6px 0 0 0; color: #0f172a; line-height: 1.4;">
										<a href="<?php the_permalink(); ?>" style="color: inherit; text-decoration: none;"><?php the_title(); ?></a>
									</h4>
								</div>
							</article>
						<?php
						endwhile;
						wp_reset_postdata();
						?>
					</div>
				</section>
			</main>

			<!-- Right Column: TRENDING NOW Sidebar -->
			<aside>
				<h3 style="font-size: 1.1rem; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; color: #0f172a; border-bottom: 2px solid #0f172a; padding-bottom: 8px; margin: 0 0 20px 0;">
					TRENDING NOW
				</h3>
				<div style="display: flex; flex-direction: column; gap: 18px;">
					<?php
					$trending = new WP_Query([
						'post_type'      => 'any',
						'posts_per_page' => 5,
						'orderby'        => 'comment_count'
					]);
					while ( $trending->have_posts() ) : $trending->the_post();
					?>
						<article style="display: flex; justify-content: space-between; gap: 12px; border-bottom: 1px solid #f1f5f9; padding-bottom: 14px;">
							<div style="flex: 1;">
								<span style="font-size: 10px; font-weight: 800; color: #64748b; text-transform: uppercase; display: block; margin-bottom: 4px;">
									NEWS | KERALA
								</span>
								<h4 style="font-size: 0.875rem; font-weight: 700; color: #0f172a; margin: 0; line-height: 1.4;">
									<a href="<?php the_permalink(); ?>" style="color: inherit; text-decoration: none;"><?php the_title(); ?></a>
								</h4>
							</div>
							<?php if ( has_post_thumbnail() ) : ?>
								<div style="width: 72px; height: 72px; shrink: 0; border-radius: 6px; overflow: hidden;">
									<?php the_post_thumbnail( 'thumbnail', [ 'style' => 'width:100%; height:100%; object-fit:cover;' ] ); ?>
								</div>
							<?php endif; ?>
						</article>
					<?php
					endwhile;
					wp_reset_postdata();
					?>
				</div>
			</aside>
		</div>

	<?php endwhile; ?>
</div>

<?php get_footer(); ?>
