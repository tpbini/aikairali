<?php
namespace AIKairali\Portal\Core;

/**
 * Interface ModuleInterface
 *
 * Defines the contract that all feature modules must implement.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.0.0
 */
interface ModuleInterface {
	/**
	 * Initialize the module (register actions, filters, REST API endpoints, etc.).
	 */
	public function init(): void;

	/**
	 * Register Custom Post Types for this module.
	 */
	public function register_cpts(): void;

	/**
	 * Register Custom Taxonomies for this module.
	 */
	public function register_taxonomies(): void;

	/**
	 * Register ACF Field Groups for this module.
	 */
	public function register_fields(): void;
}
