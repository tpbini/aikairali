<?php
/**
 * Single Template for AI Tutorials.
 * Matching Image 1 from User Request.
 *
 * @package AIKairali_Portal
 */

get_header();

$author_email = get_the_author_meta( 'user_email' );
$author_name  = get_the_author_meta( 'display_name' );
$reading_time = get_field( 'reading_time' ) ?: '4 min read';
?>

<div class="aikairali-detail-container" style="max-width: 1000px; margin: 0 auto; padding: 40px 20px; font-family: system-ui, -apple-system, sans-serif;">
	<?php while ( have_posts() ) : the_post(); ?>
		
		<!-- 1. Back To Home Button -->
		<div style="margin-bottom: 24px;">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" style="display: inline-flex; items-center: center; gap: 6px; font-size: 0.8rem; font-weight: 800; color: #475569; text-decoration: none; letter-spacing: 0.05em; text-transform: uppercase;">
				← BACK TO HOME
			</a>
		</div>

		<!-- 2. Author Email / Red Category Tag -->
		<div style="font-size: 0.85rem; font-weight: 800; color: #b91c1c; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 8px;">
			<?php echo esc_html( strtoupper( $author_email ?: $author_name ) ); ?>
		</div>

		<!-- 3. Main Malayalam Headline -->
		<h1 style="font-size: 2.3rem; font-weight: 800; line-height: 1.3; color: #0f172a; margin: 0 0 20px 0;">
			<?php the_title(); ?>
		</h1>

		<!-- 4. Date, Reading Time & Share Button Bar -->
		<div style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; padding: 14px 0; margin-bottom: 30px; font-size: 0.9rem; color: #64748b;">
			<div style="display: flex; align-items: center; gap: 16px;">
				<span><?php echo esc_html( get_the_date( 'M d, Y' ) ); ?></span>
				<span>|</span>
				<span><?php echo esc_html( $reading_time ); ?></span>
			</div>
			<div>
				<button onclick="navigator.clipboard.writeText(window.location.href); alert('Tutorial link copied to clipboard!');" style="background: #ffffff; border: 1px solid #cbd5e1; border-radius: 20px; padding: 6px 16px; font-size: 0.85rem; font-weight: 600; color: #334155; cursor: pointer; display: inline-flex; align-items: center; gap: 6px;">
					<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
					Share Tutorial
				</button>
			</div>
		</div>

		<!-- 5. Hero Featured Image -->
		<?php if ( has_post_thumbnail() ) : ?>
			<div style="margin-bottom: 35px; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
				<?php the_post_thumbnail( 'large', [ 'style' => 'width: 100%; height: auto; display: block; border-radius: 16px;' ] ); ?>
			</div>
		<?php endif; ?>

		<!-- 6. Excerpt / Intro -->
		<?php if ( has_excerpt() ) : ?>
			<div style="font-size: 1.15rem; line-height: 1.8; color: #334155; margin-bottom: 30px; font-weight: 500;">
				<?php the_excerpt(); ?>
			</div>
		<?php endif; ?>

		<!-- 7. Main Article Content Body -->
		<div class="entry-content" style="font-size: 1.08rem; line-height: 1.85; color: #334155; margin-bottom: 40px;">
			<?php the_content(); ?>
		</div>

		<!-- 8. Bottom Related Tutorials Grid -->
		<section style="margin-top: 50px; padding-top: 35px; border-top: 1px solid #e2e8f0;">
			<h3 style="font-size: 1.1rem; font-weight: 900; letter-spacing: 1px; text-transform: uppercase; color: #0f172a; margin-bottom: 20px;">
				MORE AI TUTORIALS
			</h3>
			<div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px;">
				<?php
				$related = new WP_Query([
					'post_type'      => [ 'ai-tutorials', 'tutorial', 'post' ],
					'posts_per_page' => 3,
					'post__not_in'   => [ get_the_ID() ]
				]);
				while ( $related->have_posts() ) : $related->the_post();
				?>
					<article style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; overflow: hidden; display: flex; flex-direction: column;">
						<?php if ( has_post_thumbnail() ) : ?>
							<div style="height: 150px; overflow: hidden;">
								<?php the_post_thumbnail( 'medium', [ 'style' => 'width:100%; height:100%; object-fit:cover;' ] ); ?>
							</div>
						<?php endif; ?>
						<div style="padding: 18px; display: flex; flex-direction: column; flex-grow: 1;">
							<span style="font-size: 11px; font-weight: 800; color: #2563eb; text-transform: uppercase; margin-bottom: 6px;">
								AI TUTORIAL
							</span>
							<h4 style="font-size: 0.95rem; font-weight: 700; margin: 0; color: #0f172a; line-height: 1.4;">
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

	<?php endwhile; ?>
</div>

<?php get_footer(); ?>
