<?php
namespace AIKairali\Portal\Core;

/**
 * Class Taxonomy
 *
 * Helper to programmatically register Custom Taxonomies with sensible defaults.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.0.0
 */
class Taxonomy {

	/**
	 * Register a Custom Taxonomy.
	 *
	 * @param string       $taxonomy   Taxonomy key (max 32 characters, no capital letters or spaces).
	 * @param string|array $post_types Post type(s) associated with this taxonomy.
	 * @param string       $singular   Singular label (e.g. 'Category').
	 * @param string       $plural     Plural label (e.g. 'Categories').
	 * @param array        $args       Overriding arguments for register_taxonomy.
	 */
	public static function register( string $taxonomy, $post_types, string $singular, string $plural, array $args = [] ): void {
		$labels = [
			'name'                       => $plural,
			'singular_name'              => $singular,
			'menu_name'                  => $plural,
			'all_items'                  => sprintf( __( 'All %s', 'aikairali-portal' ), $plural ),
			'parent_item'                => sprintf( __( 'Parent %s', 'aikairali-portal' ), $singular ),
			'parent_item_colon'          => sprintf( __( 'Parent %s:', 'aikairali-portal' ), $singular ),
			'new_item_name'              => sprintf( __( 'New %s Name', 'aikairali-portal' ), $singular ),
			'add_new_item'               => sprintf( __( 'Add New %s', 'aikairali-portal' ), $singular ),
			'edit_item'                  => sprintf( __( 'Edit %s', 'aikairali-portal' ), $singular ),
			'update_item'                => sprintf( __( 'Update %s', 'aikairali-portal' ), $singular ),
			'view_item'                  => sprintf( __( 'View %s', 'aikairali-portal' ), $singular ),
			'separate_items_with_commas' => sprintf( __( 'Separate %s with commas', 'aikairali-portal' ), strtolower( $plural ) ),
			'add_or_remove_items'        => sprintf( __( 'Add or remove %s', 'aikairali-portal' ), strtolower( $plural ) ),
			'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s', 'aikairali-portal' ), strtolower( $plural ) ),
			'popular_items'              => sprintf( __( 'Popular %s', 'aikairali-portal' ), $plural ),
			'search_items'               => sprintf( __( 'Search %s', 'aikairali-portal' ), $plural ),
			'not_found'                  => sprintf( __( 'No %s found', 'aikairali-portal' ), strtolower( $plural ) ),
			'no_terms'                   => sprintf( __( 'No %s', 'aikairali-portal' ), strtolower( $plural ) ),
			'items_list'                 => sprintf( __( '%s list', 'aikairali-portal' ), $plural ),
			'items_list_navigation'      => sprintf( __( '%s list navigation', 'aikairali-portal' ), $plural ),
		];

		$defaults = [
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => true,
			'show_in_rest'      => true, // Gutenberg support
			'rewrite'           => [ 'slug' => $taxonomy, 'with_front' => false ],
		];

		$final_args = array_replace_recursive( $defaults, $args );

		register_taxonomy( $taxonomy, $post_types, $final_args );
	}
}
