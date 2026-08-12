<?php
/**
 * Single Template for Newsletters.
 *
 * @package AIKairali_Portal
 */

get_header();

// ACF Fields
$issue_number     = get_field( 'issue_number' );
$publish_date     = get_field( 'publish_date' );
$email_subject    = get_field( 'email_subject' );
$pdf_download     = get_field( 'pdf_download' );
$cta_button       = get_field( 'cta_button' );
$related_articles = get_field( 'related_articles' );
?>

<div class="aikairali-container" style="max-width: 800px; margin: 0 auto; padding: 20px;">
	<?php while ( have_posts() ) : the_post(); ?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'newsletter-single' ); ?> style="background: #fff; border: 1px solid #eaeaea; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.05);">
			
			<header class="newsletter-header" style="background: #fdfdfd; border-bottom: 1px solid #eaeaea; padding: 3rem 2rem; text-align: center;">
				
				<div class="newsletter-meta-top" style="margin-bottom: 1rem; color: #0073aa; font-weight: bold; text-transform: uppercase; letter-spacing: 1px;">
					<?php 
					if ( $issue_number ) {
						echo esc_html__( 'Issue #', 'aikairali-portal' ) . esc_html( $issue_number );
					} else {
						esc_html_e( 'Newsletter', 'aikairali-portal' );
					}
					?>
				</div>

				<h1 class="entry-title" style="margin-bottom: 1rem; font-size: 2.5rem; font-family: serif; color: #111;">
					<?php the_title(); ?>
				</h1>
				
				<?php if ( $email_subject ) : ?>
					<div class="newsletter-subject" style="color: #666; font-size: 1.1rem; font-style: italic; margin-bottom: 1.5rem;">
						"<?php echo esc_html( $email_subject ); ?>"
					</div>
				<?php endif; ?>

				<div class="newsletter-meta-bottom" style="color: #888; font-size: 0.9em;">
					<?php 
					if ( $publish_date ) {
						echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $publish_date ) ) );
					} else {
						echo esc_html( get_the_date() );
					}
					?>
					
					<?php 
					$categories = get_the_terms( get_the_ID(), 'newsletter-category' );
					if ( $categories && ! is_wp_error( $categories ) ) {
						echo ' | ' . esc_html( wp_list_pluck( $categories, 'name' )[0] );
					}
					?>
				</div>
			</header>

			<?php if ( has_post_thumbnail() ) : ?>
				<div class="newsletter-banner">
					<?php the_post_thumbnail( 'full', [ 'style' => 'width: 100%; height: auto; display: block;' ] ); ?>
				</div>
			<?php endif; ?>

			<div class="newsletter-content" style="padding: 2rem; font-size: 1.15rem; line-height: 1.8; color: #333;">
				<?php the_content(); ?>
			</div>

			<?php if ( $cta_button ) : ?>
				<div class="newsletter-cta" style="text-align: center; padding: 2rem; background: #f9f9f9; border-top: 1px solid #eaeaea;">
					<a href="<?php echo esc_url( $cta_button['url'] ); ?>" target="<?php echo esc_attr( $cta_button['target'] ?: '_self' ); ?>" class="button button-primary" style="display: inline-block; padding: 15px 30px; background: #d93025; color: #fff; text-decoration: none; border-radius: 4px; font-weight: bold; font-size: 1.2rem;">
						<?php echo esc_html( $cta_button['title'] ); ?>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( $pdf_download ) : ?>
				<div class="newsletter-pdf" style="text-align: center; padding: 1rem 2rem 2rem; background: #f9f9f9;">
					<a href="<?php echo esc_url( wp_get_attachment_url( $pdf_download ) ); ?>" download class="button" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: #fff; color: #333; text-decoration: none; border: 1px solid #ccc; border-radius: 4px; font-weight: bold;">
						<span class="dashicons dashicons-download"></span> <?php esc_html_e( 'Download as PDF', 'aikairali-portal' ); ?>
					</a>
				</div>
			<?php endif; ?>

			<?php if ( $related_articles ) : ?>
				<footer class="newsletter-related" style="padding: 2rem; border-top: 1px solid #eaeaea; background: #fff;">
					<h3 style="margin-top: 0; font-size: 1.2rem; color: #111;"><?php esc_html_e( 'Mentioned in this issue', 'aikairali-portal' ); ?></h3>
					<ul style="list-style: none; padding: 0; margin: 0;">
						<?php foreach ( $related_articles as $article_post ) : ?>
							<li style="margin-bottom: 10px;">
								<a href="<?php echo esc_url( get_permalink( $article_post->ID ) ); ?>" style="text-decoration: none; color: #0073aa; font-weight: 500;">
									&rarr; <?php echo esc_html( get_the_title( $article_post->ID ) ); ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</footer>
			<?php endif; ?>

		</article>
	<?php endwhile; ?>
</div>

<?php
get_footer();
