<?php
namespace AIKairali\Portal\Core;

/**
 * Class TemplateLoader
 *
 * Intercepts and overrides theme single/archive templates, prioritizing child/parent theme overrides.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.0.0
 */
class TemplateLoader {

	/**
	 * Constructor.
	 *
	 * @param Loader $loader The hook loader.
	 */
	public function __construct( Loader $loader ) {
		$loader->add_filter( 'single_template', $this, 'locate_single_template' );
		$loader->add_filter( 'archive_template', $this, 'locate_archive_template' );
		$loader->add_filter( 'category_template', $this, 'locate_category_template' );
		$loader->add_filter( 'taxonomy_template', $this, 'locate_taxonomy_template' );
		$loader->add_filter( 'page_template', $this, 'locate_page_template' );
	}

	/**
	 * Locate single templates for custom post types.
	 *
	 * @param string $template Standard located template path.
	 * @return string Modified template path.
	 */
	public function locate_single_template( string $template ): string {
		$post_type = get_post_type();
		if ( ! $post_type ) {
			return $template;
		}

		$possible_files = [
			"single-{$post_type}.php",
		];

		if ( 'tutorial' === $post_type || 'ai-tutorials' === $post_type ) {
			$possible_files[] = 'single-ai-tutorials.php';
			$possible_files[] = 'single-tutorial.php';
			$possible_files[] = 'single-post.php';
		}

		foreach ( $possible_files as $file ) {
			$located = self::locate_template( 'single', $file );
			if ( $located ) {
				return $located;
			}
		}

		return $template;
	}

	/**
	 * Locate archive templates for custom post types.
	 *
	 * @param string $template Standard located template path.
	 * @return string Modified template path.
	 */
	public function locate_archive_template( string $template ): string {
		$post_type = get_query_var( 'post_type' );
		if ( is_array( $post_type ) ) {
			$post_type = reset( $post_type );
		}

		if ( ! $post_type ) {
			return $template;
		}

		$map = [
			'ai-news'      => 'archive-ai-news.php',
			'news'         => 'archive-ai-news.php',
			'ai-prompts'   => 'archive-ai-prompts.php',
			'prompts'      => 'archive-ai-prompts.php',
			'ai-courses'   => 'archive-ai-courses.php',
			'courses'      => 'archive-ai-courses.php',
			'ai-videos'    => 'archive-ai-videos.php',
			'videos'       => 'archive-ai-videos.php',
			'ai-tutorials' => 'archive-ai-tutorials.php',
			'tutorial'     => 'archive-ai-tutorials.php',
			'tutorials'    => 'archive-ai-tutorials.php',
			'ai-tools'     => 'archive-ai-tools.php',
			'tools'        => 'archive-ai-tools.php',
			'ai-books'     => 'archive-ai-books.php',
			'books'        => 'archive-ai-books.php',
			'ai-events'    => 'archive-ai-events.php',
			'events'       => 'archive-ai-events.php',
			'ai-models'    => 'archive-ai-models.php',
			'models'       => 'archive-ai-models.php',
			'ai-glossary'  => 'archive-ai-glossary.php',
			'glossary'     => 'archive-ai-glossary.php',
			'jobs'         => 'archive-jobs.php',
			'ai-jobs'      => 'archive-jobs.php',
		];

		if ( isset( $map[ $post_type ] ) ) {
			$located = self::locate_template( 'archive', $map[ $post_type ] );
			if ( $located ) {
				return $located;
			}
		}

		$file = "archive-{$post_type}.php";
		$located = self::locate_template( 'archive', $file );

		return $located ?: $template;
	}

	/**
	 * Locate category templates.
	 *
	 * @param string $template Standard template path.
	 * @return string Modified template path.
	 */
	public function locate_category_template( string $template ): string {
		$news_cats = [
			'generative-ai',
			'india-ai',
			'research-innovation',
			'robotics',
			'automation',
			'cybersecurity',
			'education',
			'healthcare',
			'opinions-analysis',
			'ai-news',
			'news',
		];
		if ( is_category( $news_cats ) ) {
			$located = self::locate_template( 'archive', 'archive-ai-news.php' );
			if ( $located ) {
				return $located;
			}
		}
		return $template;
	}

	/**
	 * Locate custom taxonomy templates for all modules.
	 *
	 * @param string $template Standard template path.
	 * @return string Modified template path.
	 */
	public function locate_taxonomy_template( string $template ): string {
		$taxonomy = get_query_var( 'taxonomy' );
		if ( ! $taxonomy ) {
			return $template;
		}

		$tax_map = [
			'prompt-category'   => 'archive-ai-prompts.php',
			'course-category'   => 'archive-ai-courses.php',
			'video-category'    => 'archive-ai-videos.php',
			'tutorial-category' => 'archive-ai-tutorials.php',
			'tool-category'     => 'archive-ai-tools.php',
			'tool-model'        => 'archive-ai-tools.php',
			'tool-pricing'      => 'archive-ai-tools.php',
			'book-category'     => 'archive-ai-books.php',
			'event-category'    => 'archive-ai-events.php',
			'model-category'    => 'archive-ai-models.php',
			'glossary-category' => 'archive-ai-glossary.php',
			'job-category'      => 'archive-jobs.php',
		];

		if ( isset( $tax_map[ $taxonomy ] ) ) {
			$located = self::locate_template( 'archive', $tax_map[ $taxonomy ] );
			if ( $located ) {
				return $located;
			}
		}

		return $template;
	}

	/**
	 * Locate page templates for module landing pages.
	 *
	 * @param string $template Standard template path.
	 * @return string Modified template path.
	 */
	public function locate_page_template( string $template ): string {
		$page_map = [
			'ai-news'         => 'archive-ai-news.php',
			'news'            => 'archive-ai-news.php',
			'prompts'         => 'archive-ai-prompts.php',
			'ai-prompts'      => 'archive-ai-prompts.php',
			'courses'         => 'archive-ai-courses.php',
			'ai-courses'      => 'archive-ai-courses.php',
			'videos'          => 'archive-ai-videos.php',
			'ai-videos'       => 'archive-ai-videos.php',
			'tutorial-videos' => 'archive-ai-videos.php',
			'tutorials'       => 'archive-ai-tutorials.php',
			'ai-tutorials'    => 'archive-ai-tutorials.php',
			'ai-tools'        => 'archive-ai-tools.php',
			'tools'           => 'archive-ai-tools.php',
			'ai-books'        => 'archive-ai-books.php',
			'books'           => 'archive-ai-books.php',
			'ai-events'       => 'archive-ai-events.php',
			'events'          => 'archive-ai-events.php',
			'ai-models'       => 'archive-ai-models.php',
			'models'          => 'archive-ai-models.php',
			'ai-glossary'     => 'archive-ai-glossary.php',
			'glossary'        => 'archive-ai-glossary.php',
			'jobs'            => 'archive-jobs.php',
			'ai-jobs'         => 'archive-jobs.php',
		];

		foreach ( $page_map as $slug => $archive_file ) {
			if ( is_page( $slug ) ) {
				$located = self::locate_template( 'archive', $archive_file );
				if ( $located ) {
					return $located;
				}
			}
		}

		return $template;
	}

	/**
	 * Search for template in theme first, then fall back to plugin.
	 *
	 * @param string $subfolder Directory inside templates (single, archive, parts).
	 * @param string $file      Template file name.
	 * @return string|false Path to the located template, or false if not found.
	 */
	public static function locate_template( string $subfolder, string $file ) {
		// Theme overrides: /theme/aikairali-portal/{subfolder}/{file} or /theme/aikairali-portal/{file}.
		$theme_files = [
			"aikairali-portal/{$subfolder}/{$file}",
			"aikairali-portal/{$file}",
		];

		$located_in_theme = locate_template( $theme_files );
		if ( $located_in_theme ) {
			return $located_in_theme;
		}

		// Fallback to plugin templates directory.
		$plugin_path = AIKAIRALI_PORTAL_PATH . "templates/{$subfolder}/{$file}";
		if ( file_exists( $plugin_path ) ) {
			return $plugin_path;
		}

		return false;
	}

	/**
	 * Load a template part (like a card or sidebar component).
	 *
	 * @param string $slug The template slug (e.g. 'card').
	 * @param string $name The template part name (e.g. 'tools').
	 * @param array  $args Variables to pass to the template context.
	 */
	public static function get_template_part( string $slug, string $name = '', array $args = [] ): void {
		$file = empty( $name ) ? "{$slug}.php" : "{$slug}-{$name}.php";

		// Create variables locally in template scope.
		if ( ! empty( $args ) ) {
			extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}

		$located = self::locate_template( 'parts', $file );

		if ( $located ) {
			include $located;
		}
	}
}
