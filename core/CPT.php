<?php
namespace AIKairali\Portal\Core;

/**
 * Class CPT
 *
 * Helper to programmatically register Custom Post Types with sensible defaults.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.0.0
 */
class CPT {

	/**
	 * Register a Custom Post Type.
	 *
	 * @param string $post_type The post type key (max 20 characters, no capital letters or spaces).
	 * @param string $singular  Singular label (e.g. 'Job').
	 * @param string $plural    Plural label (e.g. 'Jobs').
	 * @param array  $args      Overriding arguments for register_post_type.
	 */
	public static function register( string $post_type, string $singular, string $plural, array $args = [] ): void {
		$labels = [
			'name'                  => $plural,
			'singular_name'         => $singular,
			'menu_name'             => $plural,
			'name_admin_bar'        => $singular,
			'archives'              => sprintf( __( '%s Archives', 'aikairali-portal' ), $singular ),
			'attributes'            => sprintf( __( '%s Attributes', 'aikairali-portal' ), $singular ),
			'parent_item_colon'     => sprintf( __( 'Parent %s:', 'aikairali-portal' ), $singular ),
			'all_items'             => sprintf( __( 'All %s', 'aikairali-portal' ), $plural ),
			'add_new_item'          => sprintf( __( 'Add New %s', 'aikairali-portal' ), $singular ),
			'add_new'               => __( 'Add New', 'aikairali-portal' ),
			'new_item'              => sprintf( __( 'New %s', 'aikairali-portal' ), $singular ),
			'edit_item'             => sprintf( __( 'Edit %s', 'aikairali-portal' ), $singular ),
			'update_item'           => sprintf( __( 'Update %s', 'aikairali-portal' ), $singular ),
			'view_item'             => sprintf( __( 'View %s', 'aikairali-portal' ), $singular ),
			'view_items'            => sprintf( __( 'View %s', 'aikairali-portal' ), $plural ),
			'search_items'          => sprintf( __( 'Search %s', 'aikairali-portal' ), $plural ),
			'not_found'             => sprintf( __( 'No %s found', 'aikairali-portal' ), strtolower( $plural ) ),
			'not_found_in_trash'    => sprintf( __( 'No %s found in Trash', 'aikairali-portal' ), strtolower( $plural ) ),
			'featured_image'        => __( 'Featured Image', 'aikairali-portal' ),
			'set_featured_image'    => __( 'Set featured image', 'aikairali-portal' ),
			'remove_featured_image' => __( 'Remove featured image', 'aikairali-portal' ),
			'use_featured_image'    => __( 'Use as featured image', 'aikairali-portal' ),
			'insert_into_item'      => sprintf( __( 'Insert into %s', 'aikairali-portal' ), strtolower( $singular ) ),
			'uploaded_to_this_item' => sprintf( __( 'Uploaded to this %s', 'aikairali-portal' ), strtolower( $singular ) ),
			'items_list'            => sprintf( __( '%s list', 'aikairali-portal' ), $plural ),
			'items_list_navigation' => sprintf( __( '%s list navigation', 'aikairali-portal' ), $plural ),
			'filter_items_list'     => sprintf( __( 'Filter %s list', 'aikairali-portal' ), strtolower( $plural ) ),
		];

		$defaults = [
			'label'               => $plural,
			'labels'              => $labels,
			'supports'            => [ 'title', 'editor', 'thumbnail', 'excerpt', 'revisions', 'comments' ],
			'hierarchical'        => false,
			'public'              => true,
			'show_ui'             => true,
			'show_in_menu'        => 'aikairali-portal', // Nested under AIKairali main menu
			'menu_position'       => 5,
			'show_in_admin_bar'   => true,
			'show_in_nav_menus'   => true,
			'can_export'          => true,
			'has_archive'         => true,
			'exclude_from_search' => false,
			'publicly_queryable'  => true,
			'show_in_rest'        => true, // Gutenberg & REST API support
			'capability_type'     => 'post',
			'rewrite'             => [ 'slug' => $post_type, 'with_front' => false ],
		];

		$final_args = array_replace_recursive( $defaults, $args );

		register_post_type( $post_type, $final_args );
	}
}
