<?php
namespace AIKairali\Portal\Modules\AiTutorials;

use AIKairali\Portal\Core\ModuleInterface;
use AIKairali\Portal\Core\CPT;
use AIKairali\Portal\Core\Taxonomy;
use AIKairali\Portal\Core\ACFLoader;

/**
 * Class AiTutorials
 *
 * Implements the AI Tutorials Module.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Modules/AiTutorials
 * @since      1.0.0
 */
class AiTutorials implements ModuleInterface {

	/**
	 * Initialize the module by registering hooks.
	 */
	public function init(): void {
		add_filter( 'manage_ai-tutorials_posts_columns', [ $this, 'add_admin_columns' ] );
		add_action( 'manage_ai-tutorials_posts_custom_column', [ $this, 'render_admin_columns' ], 10, 2 );
	}

	/**
	 * Register Custom Post Type for AI Tutorials.
	 */
	public function register_cpts(): void {
		CPT::register(
			'ai-tutorials',
			__( 'AI Tutorial', 'aikairali-portal' ),
			__( 'AI Tutorials', 'aikairali-portal' ),
			[
				'menu_icon'   => 'dashicons-welcome-learn-more',
				'supports'    => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'author' ],
				'has_archive' => 'ai-tutorials',
				'rewrite'     => [ 'slug' => 'ai-tutorials', 'with_front' => false ],
			]
		);
	}

	/**
	 * Register Taxonomies.
	 */
	public function register_taxonomies(): void {
		Taxonomy::register(
			'tutorial-category',
			'ai-tutorials',
			__( 'Tutorial Category', 'aikairali-portal' ),
			__( 'Tutorial Categories', 'aikairali-portal' ),
			[ 'rewrite' => [ 'slug' => 'tutorial-category' ] ]
		);
	}

	/**
	 * Register ACF Fields.
	 */
	public function register_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		ACFLoader::register_field_group( [
			'key'      => 'group_tutorial_info',
			'title'    => __( 'Tutorial Information', 'aikairali-portal' ),
			'fields'   => [
				[
					'key'         => 'field_tutorial_reading_time',
					'label'       => __( 'Reading Time', 'aikairali-portal' ),
					'name'        => 'reading_time',
					'type'        => 'text',
					'placeholder' => __( 'e.g. 5 min read', 'aikairali-portal' ),
					'wrapper'     => [ 'width' => '50' ],
				],
				[
					'key'         => 'field_tutorial_difficulty',
					'label'       => __( 'Difficulty Level', 'aikairali-portal' ),
					'name'        => 'difficulty',
					'type'        => 'select',
					'choices'     => [
						'Beginner'     => __( 'Beginner', 'aikairali-portal' ),
						'Intermediate' => __( 'Intermediate', 'aikairali-portal' ),
						'Advanced'     => __( 'Advanced', 'aikairali-portal' ),
					],
					'wrapper'     => [ 'width' => '50' ],
				],
			],
			'location' => [
				[
					[
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'ai-tutorials',
					],
				],
			],
		] );
	}

	public function add_admin_columns( array $columns ): array {
		$columns['author'] = __( 'Author', 'aikairali-portal' );
		return $columns;
	}

	public function render_admin_columns( string $column, int $post_id ): void {
		if ( 'author' === $column ) {
			echo esc_html( get_the_author_meta( 'display_name', get_post_field( 'post_author', $post_id ) ) );
		}
	}
}
