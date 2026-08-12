<?php
namespace AIKairali\Portal\Core;

/**
 * Class Shortcodes
 *
 * Registers shortcodes for the AIKairali Portal plugin.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.0.0
 */
class Shortcodes {

	/**
	 * Constructor.
	 *
	 * @param Loader $loader The hook loader instance.
	 */
	public function __construct( Loader $loader ) {
		$loader->add_action( 'init', $this, 'register_shortcodes' );
	}

	/**
	 * Register shortcodes.
	 */
	public function register_shortcodes(): void {
		// AI Tools, AI Courses, AI Books Grid
		add_shortcode( 'aikairali_home_grid', [ $this, 'render_home_grid' ] );
		add_shortcode( 'ai_home_grid', [ $this, 'render_home_grid' ] );

		// AI Events, AI Prompts, AI Models Grid
		add_shortcode( 'aikairali_events_prompts_models', [ $this, 'render_events_grid' ] );
		add_shortcode( 'ai_events_prompts_models_grid', [ $this, 'render_events_grid' ] );

		// AI Tutorial Videos Carousel
		add_shortcode( 'aikairali_videos_carousel', [ $this, 'render_videos_carousel' ] );
		add_shortcode( 'ai_videos_carousel', [ $this, 'render_videos_carousel' ] );

		// 3-Column AI News Layout with Advertisement Slot
		add_shortcode( 'aikairali_news_grid', [ $this, 'render_news_grid' ] );
		add_shortcode( 'ai_news_grid', [ $this, 'render_news_grid' ] );
	}

	/**
	 * Render the AI Home Grid layout for AI Tools, AI Courses, and AI Books.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_home_grid( $atts = [] ): string {
		$atts = shortcode_atts( [
			'tools_limit'   => 4,
			'courses_limit' => 4,
			'books_limit'   => 4,
		], $atts, 'aikairali_home_grid' );

		// Enqueue frontend styles
		wp_enqueue_style( 'aikairali-portal-frontend' );

		ob_start();
		TemplateLoader::get_template_part( 'home-ai-grid', '', $atts );
		return ob_get_clean();
	}

	/**
	 * Render the AI Events, AI Prompts, and AI Models layout.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_events_grid( $atts = [] ): string {
		$atts = shortcode_atts( [
			'events_limit'  => 1,
			'prompts_limit' => 4,
			'models_limit'  => 3,
		], $atts, 'aikairali_events_prompts_models' );

		// Enqueue frontend styles
		wp_enqueue_style( 'aikairali-portal-frontend' );

		ob_start();
		TemplateLoader::get_template_part( 'home-ai-events-grid', '', $atts );
		return ob_get_clean();
	}

	/**
	 * Render the AI Tutorial Videos Carousel component.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_videos_carousel( $atts = [] ): string {
		$atts = shortcode_atts( [
			'limit' => 10,
		], $atts, 'aikairali_videos_carousel' );

		// Enqueue frontend styles
		wp_enqueue_style( 'aikairali-portal-frontend' );

		ob_start();
		TemplateLoader::get_template_part( 'home-ai-videos-carousel', '', $atts );
		return ob_get_clean();
	}

	/**
	 * Render the 3-Column AI News layout with Advertisement slot.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string HTML output.
	 */
	public function render_news_grid( $atts = [] ): string {
		$atts = shortcode_atts( [
			'limit'    => 9,
			'cat_slug' => '',
			'ad_image' => '',
			'ad_link'  => '#',
			'ad_code'  => '',
		], $atts, 'aikairali_news_grid' );

		// Enqueue frontend styles
		wp_enqueue_style( 'aikairali-portal-frontend' );

		ob_start();
		TemplateLoader::get_template_part( 'home-ai-news-grid', '', $atts );
		return ob_get_clean();
	}
}
