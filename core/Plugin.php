<?php
namespace AIKairali\Portal\Core;

/**
 * Class Plugin
 *
 * The main plugin orchestrator class.
 *
 * @package    AIKairali_Portal
 * @subpackage AIKairali_Portal/Core
 * @since      1.0.0
 */
class Plugin {

	/**
	 * The single instance of the class.
	 *
	 * @var Plugin|null
	 */
	private static ?Plugin $instance = null;

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @var Loader
	 */
	protected Loader $loader;

	/**
	 * Array of active modules.
	 *
	 * @var array<string, ModuleInterface>
	 */
	protected array $modules = [];

	/**
	 * Retrieve the single instance of the class.
	 *
	 * @return Plugin
	 */
	public static function instance(): Plugin {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		$this->loader = new Loader();
		$this->init_core_services();
		$this->discover_modules();
	}

	/**
	 * Initialize core service hooks.
	 */
	private function init_core_services(): void {
		// Initialize the settings page.
		new Settings( $this->loader );
		
		// Initialize asset loading.
		new Assets( $this->loader );
		
		// Initialize global AJAX search.
		new Search( $this->loader );
		
		// Initialize template overrides.
		new TemplateLoader( $this->loader );

		// Initialize REST API.
		new RestAPI( $this->loader );

		// Register all CPT post meta keys for REST API access.
		new MetaRegistrar( $this->loader );

		// Initialize ACF Loader.
		new ACFLoader( $this->loader );

		// Initialize Shortcodes.
		new Shortcodes( $this->loader );
	}

	/**
	 * Discover and load modules in the modules directory.
	 */
	private function discover_modules(): void {
		$module_classes = [
			'ai-tools'    => \AIKairali\Portal\Modules\AiTools\AiTools::class,
			'ai-books'    => \AIKairali\Portal\Modules\AiBooks\AiBooks::class,
			'ai-events'   => \AIKairali\Portal\Modules\AiEvents\AiEvents::class,
			'ai-courses'  => \AIKairali\Portal\Modules\AiCourses\AiCourses::class,
			'ai-models'   => \AIKairali\Portal\Modules\AiModels\AiModels::class,
			'ai-prompts'   => \AIKairali\Portal\Modules\AiPrompts\AiPrompts::class,
			'ai-glossary'  => \AIKairali\Portal\Modules\AiGlossary\AiGlossary::class,
			'ai-videos'    => \AIKairali\Portal\Modules\AiVideos\AiVideos::class,
			'jobs'         => \AIKairali\Portal\Modules\Jobs\Jobs::class,
			'ai-tutorials' => \AIKairali\Portal\Modules\AiTutorials\AiTutorials::class,
			'newsletters'  => \AIKairali\Portal\Modules\Newsletters\Newsletters::class,
		];

		foreach ( $module_classes as $slug => $class_name ) {
			if ( class_exists( $class_name ) ) {
				$instance = new $class_name();
				if ( $instance instanceof ModuleInterface ) {
					$this->modules[ $slug ] = $instance;
				}
			}
		}
	}

	/**
	 * Run the loader to execute all of the hooks.
	 */
	public function run(): void {
		// Register module-specific activation hooks/initialization.
		$this->loader->add_action( 'init', $this, 'register_module_cpts_and_taxonomies', 5 );
		$this->loader->add_action( 'init', $this, 'init_modules', 10 );
		$this->loader->add_action( 'acf/init', $this, 'register_module_acf_fields', 10 );

		// Run the loader.
		$this->loader->run();
	}

	/**
	 * Get the loader.
	 *
	 * @return Loader
	 */
	public function get_loader(): Loader {
		return $this->loader;
	}

	/**
	 * Get all active modules.
	 *
	 * @return array<string, ModuleInterface>
	 */
	public function get_modules(): array {
		return $this->modules;
	}

	/**
	 * Callback to register all module-specific Custom Post Types and Taxonomies.
	 */
	public function register_module_cpts_and_taxonomies(): void {
		foreach ( $this->modules as $module ) {
			$module->register_taxonomies();
			$module->register_cpts();
		}
	}

	/**
	 * Callback to initialize all modules.
	 */
	public function init_modules(): void {
		foreach ( $this->modules as $module ) {
			$module->init();
		}
	}

	/**
	 * Callback to register ACF fields for all modules.
	 */
	public function register_module_acf_fields(): void {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}
		foreach ( $this->modules as $module ) {
			$module->register_fields();
		}
	}
}
