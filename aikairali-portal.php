<?php
/**
 * Plugin Name:       AIKairali Portal
 * Plugin URI:        https://aikairali.org/portal
 * Description:       Enterprise-grade modular plugin for AI directory, learning portal, models, jobs, courses, and prompts.
 * Version:           1.0.2
 * Author:            AIKairali Team
 * Author URI:        https://aikairali.org
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       aikairali-portal
 * Domain Path:       /languages
 * Requires PHP:      7.4
 * Requires at least: 6.2
 */

// If this file is called directly, abort.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Plugin Constants.
define( 'AIKAIRALI_PORTAL_VERSION', '1.0.2' );
define( 'AIKAIRALI_PORTAL_PATH', plugin_dir_path( __FILE__ ) );
define( 'AIKAIRALI_PORTAL_URL', plugin_dir_url( __FILE__ ) );
define( 'AIKAIRALI_PORTAL_BASENAME', plugin_basename( __FILE__ ) );

// Explicitly require core framework components for cross-platform Linux safety
require_once AIKAIRALI_PORTAL_PATH . 'core/ModuleInterface.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/Loader.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/Activator.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/Deactivator.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/CPT.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/Taxonomy.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/ACFLoader.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/RestAPI.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/MetaRegistrar.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/Settings.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/Assets.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/Search.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/Shortcodes.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/TemplateLoader.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/Helpers.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/Cache.php';
require_once AIKAIRALI_PORTAL_PATH . 'core/Plugin.php';

// Explicitly require all 10 module classes for cross-platform Linux safety
require_once AIKAIRALI_PORTAL_PATH . 'modules/ai-tools/AiTools.php';
require_once AIKAIRALI_PORTAL_PATH . 'modules/ai-books/AiBooks.php';
require_once AIKAIRALI_PORTAL_PATH . 'modules/ai-events/AiEvents.php';
require_once AIKAIRALI_PORTAL_PATH . 'modules/ai-courses/AiCourses.php';
require_once AIKAIRALI_PORTAL_PATH . 'modules/ai-models/AiModels.php';
require_once AIKAIRALI_PORTAL_PATH . 'modules/ai-prompts/AiPrompts.php';
require_once AIKAIRALI_PORTAL_PATH . 'modules/ai-glossary/AiGlossary.php';
require_once AIKAIRALI_PORTAL_PATH . 'modules/ai-videos/AiVideos.php';
require_once AIKAIRALI_PORTAL_PATH . 'modules/jobs/Jobs.php';
require_once AIKAIRALI_PORTAL_PATH . 'modules/newsletters/Newsletters.php';
require_once AIKAIRALI_PORTAL_PATH . 'modules/ai-tutorials/AiTutorials.php';

/**
 * Register lightweight PSR-4 Autoloader for modules.
 */
spl_autoload_register( function ( $class ) {
	// Project-specific namespace prefix.
	$prefix = 'AIKairali\\Portal\\';

	// Base directory for the namespace prefix.
	$base_dir = AIKAIRALI_PORTAL_PATH;

	// Does the class use the namespace prefix?
	$len = strlen( $prefix );
	if ( strncmp( $prefix, $class, $len ) !== 0 ) {
		return;
	}

	// Get the relative class name.
	$relative_class = substr( $class, $len );

	// Split namespace into parts.
	$parts = explode( '\\', $relative_class );
	if ( empty( $parts ) ) {
		return;
	}

	// Map top-level directory names to lowercase.
	$parts[0] = strtolower( $parts[0] );

	// If it's a module, convert PascalCase to kebab-case for the module directory name.
	if ( 'modules' === $parts[0] && isset( $parts[1] ) ) {
		$parts[1] = strtolower( preg_replace( '/(?<!^)[A-Z]/', '-$0', $parts[1] ) );
	}

	// Reconstruct the file path.
	$file = $base_dir . implode( '/', $parts ) . '.php';

	// If the file exists, require it.
	if ( file_exists( $file ) ) {
		require_once $file;
	}
} );

/**
 * The code that runs during plugin activation.
 */
function activate_aikairali_portal() {
	try {
		\AIKairali\Portal\Core\Activator::activate();
	} catch ( \Throwable $e ) {
		wp_die( 'AIKairali Portal Activation Error: ' . esc_html( $e->getMessage() ) . ' in ' . esc_html( $e->getFile() ) . ':' . esc_html( $e->getLine() ) );
	}
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_aikairali_portal() {
	try {
		\AIKairali\Portal\Core\Deactivator::deactivate();
	} catch ( \Throwable $e ) {
		// Silent cleanup
	}
}

register_activation_hook( __FILE__, 'activate_aikairali_portal' );
register_deactivation_hook( __FILE__, 'deactivate_aikairali_portal' );

/**
 * Begins execution of the plugin.
 */
function run_aikairali_portal() {
	try {
		\AIKairali\Portal\Core\Plugin::instance()->run();
	} catch ( \Throwable $e ) {
		add_action( 'admin_notices', function() use ( $e ) {
			echo '<div class="notice notice-error"><p><strong>AIKairali Portal Error:</strong> ' . esc_html( $e->getMessage() ) . ' in ' . esc_html( $e->getFile() ) . ':' . esc_html( $e->getLine() ) . '</p></div>';
		} );
	}
}

run_aikairali_portal();

/**
 * Auto-register the 9 News categories.
 */
add_action( 'init', 'aikairali_register_news_categories', 15 );
function aikairali_register_news_categories() {
	$categories = [
		'Generative AI'         => 'generative-ai',
		'India AI'              => 'india-ai',
		'Research & Innovation' => 'research-innovation',
		'Robotics'              => 'robotics',
		'Automation'            => 'automation',
		'Cybersecurity'         => 'cybersecurity',
		'Education'             => 'education',
		'Healthcare'            => 'healthcare',
		'Opinions & Analysis'   => 'opinions-analysis',
	];

	foreach ( $categories as $name => $slug ) {
		if ( ! term_exists( $slug, 'category' ) ) {
			wp_insert_term( $name, 'category', [ 'slug' => $slug ] );
		}
	}
}

/**
 * Auto-seed news posts across all 9 categories if DB has fewer than 15 posts.
 */
add_action( 'init', 'aikairali_seed_news_posts', 20 );
function aikairali_seed_news_posts() {
	$count = wp_count_posts( 'post' );
	if ( isset( $count->publish ) && (int) $count->publish >= 15 ) {
		return;
	}

	$seed_data = [
		'generative-ai' => [
			'title'   => 'ഓപ്പൺഎഐ ജിപിടി-5 സിഇഒ സാം ആൾട്ട്മാൻ പ്രഖ്യാപനം; നിർമ്മൽ ബുദ്ധിരംഗത്ത് വൻ വിപ്ലവം',
			'content' => 'സാം ആൾട്ട്മാൻ നയിക്കുന്ന ഓപ്പൺഎഐ അടുത്ത തലമുറയിലെ വൻകിട ഭാഷാ മോഡലായ ജിപിടി-5 ഔദ്യോഗികമായി അവതരിപ്പിച്ചു. പുതിയ മോഡൽ കോഡിംഗ്, തത്സമയ യുക്തിചിന്ത, പ്രശ്നപരിഹാര മികവ് എന്നിവയിൽ വൻ നേട്ടങ്ങളാണ് കൈവരിച്ചിരിക്കുന്നത്.',
			'excerpt' => 'സാം ആൾട്ട്മാൻ നയിക്കുന്ന ഓപ്പൺഎഐ അടുത്ത തലമുറയിലെ വൻകിട ഭാഷാ മോഡലായ ജിപിടി-5 ഔദ്യോഗികമായി അവതരിപ്പിച്ചു...',
			'image'   => 'https://images.unsplash.com/photo-1677442136019-21780efad99a?w=800&auto=format&fit=crop',
		],
		'india-ai' => [
			'title'   => 'ഇന്ത്യ എഐ മിഷൻ: ഡിജിറ്റൽ ഇന്ത്യ പദ്ധതിക്ക് 10,000 കോടി രൂപയുടെ സൂപ്പർ കമ്പ്യൂട്ടർ ഇൻഫ്രാസ്ട്രക്ചർ',
			'content' => 'ഭാരതത്തിൽ സ്വന്തമായി എഐ വികസനം ശക്തമാക്കുന്നതിന് കേന്ദ്ര സർക്കാർ 10,000 കോടി രൂപയുടെ എഐ മിഷൻ പദ്ധതി ആരംഭിച്ചു. രാജ്യത്തെ പ്രമുഖ വിദ്യാഭ്യാസ സ്ഥാപനങ്ങൾക്കും ആർട്ടിഫിഷ്യൽ ഇന്റലിജൻസ് സ്റ്റാർട്ടപ്പുകൾക്കും പ്രയോജനം ലഭിക്കും.',
			'excerpt' => 'ഭാരതത്തിൽ സ്വന്തമായി എഐ വികസനം ശക്തമാക്കുന്നതിന് കേന്ദ്ര സർക്കാർ 10,000 കോടി രൂപയുടെ എഐ മിഷൻ പദ്ധതി ആരംഭിച്ചു...',
			'image'   => 'https://images.unsplash.com/photo-1532375810709-75b1da00537c?w=800&auto=format&fit=crop',
		],
		'research-innovation' => [
			'title'   => 'ക്വാണ്ടം കമ്പ്യൂട്ടിംഗും എഐയും സംയോജിപ്പിച്ച് പുതിയ സൂപ്പർഅൽഗോരിതം വികസിപ്പിച്ച് ഗവേഷകർ',
			'content' => 'സങ്കീർണ്ണമായ ഭൗതികശാസ്ത്ര കണക്കുകൂട്ടലുകൾ നിമിഷങ്ങൾക്കുള്ളിൽ സാധ്യമാക്കുന്ന പുതിയ ആർട്ടിഫിഷ്യൽ ഇന്റലിജൻസ് ക്വാണ്ടം മുന്നേറ്റം അന്താരാഷ്ട്ര ശാസ്ത്ര സമൂഹത്തിൽ വൻ ശ്രദ്ധ നേടുന്നു.',
			'excerpt' => 'സങ്കീർണ്ണമായ ഭൗതികശാസ്ത്ര കണക്കുകൂട്ടലുകൾ നിമിഷങ്ങൾക്കുള്ളിൽ സാധ്യമാക്കുന്ന പുതിയ മുന്നേറ്റം...',
			'image'   => 'https://images.unsplash.com/photo-1507413245164-6160d8298b31?w=800&auto=format&fit=crop',
		],
		'robotics' => [
			'title'   => 'ടെസ്‌ല ഹ്യൂമനോയിഡ് റോബോട്ട് ഒപ്റ്റിമസ് പുതിയ വീട്ടുകാര്യങ്ങൾ ചെയ്യാൻ സജ്ജമെന്ന് ഇലോൺ മസ്ക്',
			'content' => 'ടെസ്‌ലയുടെ മനുഷ്യതുല്യമായ ഹ്യൂമനോയിഡ് റോബോട്ട് ഒപ്റ്റിമസ് വീട്ടുജോലികൾ ചെയ്യാനും പാചക സഹായം നൽകാനും ആരംഭിച്ചതായി ഇലോൺ മസ്ക് പ്രഖ്യാപിച്ചു.',
			'excerpt' => 'ടെസ്‌ലയുടെ മനുഷ്യതുല്യമായ റോബോട്ട് ഒപ്റ്റിമസ് വീട്ടുജോലികൾ ചെയ്യാനും ലഗേജുകൾ സൂക്ഷിക്കാനും തുടങ്ങി...',
			'image'   => 'https://images.unsplash.com/photo-1485827404703-89b55fcc595e?w=800&auto=format&fit=crop',
		],
		'automation' => [
			'title'   => 'ബിസിനസ്സ് പ്രക്രിയകൾ 80% വേഗത്തിലാക്കാൻ നോ-കോഡ് എഐ ഓട്ടോമേഷൻ ടൂളുകൾ',
			'content' => 'ആഗോള ബിസിനസ്സ് മേഖലകളിൽ എഐ ഓട്ടോമേഷൻ സംവിധാനങ്ങൾ ഉപയോഗിച്ച് ഉൽപ്പാദനക്ഷമതയും പ്രതിദിന തൊഴിൽക്ഷമതയും ഇരട്ടിയാക്കി വർദ്ധിപ്പിക്കുന്ന പരീക്ഷണങ്ങൾ വിജയകരം.',
			'excerpt' => 'ആഗോള ബിസിനസ്സ് മേഖലകളിൽ എഐ ഓട്ടോമേഷൻ ടൂളുകൾ ഉപയോഗിച്ച് പ്രക്രിയകൾ വേഗത്തിലാക്കുന്നു...',
			'image'   => 'https://images.unsplash.com/photo-1518770660439-4636190af475?w=800&auto=format&fit=crop',
		],
		'cybersecurity' => [
			'title'   => 'സൈബർ ആക്രമണങ്ങൾ തത്സമയം തടയാൻ തദ്ദേശീയ എഐ ഡിഫൻസ് സിസ്റ്റം വികസിപ്പിച്ചു',
			'content' => 'ബാങ്കിംഗ്, ഡിജിറ്റൽ ഇടപാടുകൾ എന്നിവയിൽ ഉണ്ടാകുന്ന അതിസങ്കീർണ്ണമായ ഡീപ്‌ഫേക്ക് തട്ടിപ്പുകളും ബാക്ക്ഡോർ ഭീഷണികളും തത്സമയം ചെറുക്കുന്നതിന് അത്യാധുനിക എഐ സൈബർ സെക്യൂരിറ്റി ഡിഫൻസ്.',
			'excerpt' => 'ഡിജിറ്റൽ സാമ്പത്തിക തട്ടിപ്പുകളും സൈബർ ആക്രമണങ്ങളും തടയാൻ പുതിയ എഐ ഡിഫൻസ്...',
			'image'   => 'https://images.unsplash.com/photo-1563986768609-322da13575f3?w=800&auto=format&fit=crop',
		],
		'education' => [
			'title'   => 'കേരളത്തിലെ സ്കൂളുകളിൽ എഐ പഠനമുറികളും റോബോട്ടിക്സ് ലാബുകളും ആരംഭിക്കുന്നു',
			'content' => 'സംസ്ഥാനത്തെ പൊതുവിദ്യാലയങ്ങളിൽ വിദ്യാർത്ഥികൾക്കായി എഐ കോഡിംഗ്, റോബോട്ടിക് സാങ്കേതിക പരിശീലനം എന്നിവ ഉറപ്പാക്കുന്ന ഹൈടെക് ലാബുകൾ സജ്ജമാക്കുന്നു.',
			'excerpt' => 'കേരളത്തിലെ സ്കൂളുകളിൽ വിദ്യാർത്ഥികൾക്കായി എഐ പാഠ്യപദ്ധതിയും ലാബുകളും സജ്ജമാക്കുന്നു...',
			'image'   => 'https://images.unsplash.com/photo-1509062522246-3755977927d7?w=800&auto=format&fit=crop',
		],
		'healthcare' => [
			'title'   => 'അർബുദം ആദ്യഘട്ടത്തിൽ തന്നെ കണ്ടെത്താൻ എഐ ഇമേജിംഗ് സാങ്കേതികവിദ്യ വിജയകരം',
			'content' => 'മെഡിക്കൽ രംഗത്ത് രോഗനിർണ്ണയം മുൻകൂട്ടി സാധ്യമാക്കുന്ന എഐ സ്കാനിംഗ് സിസ്റ്റം വികസിപ്പിച്ചു. ആദ്യഘട്ടത്തിൽ തന്നെ കോശവ്യതിയാനങ്ങൾ തിരിച്ചറിയാൻ കഴിയുന്നു.',
			'excerpt' => 'മെഡിക്കൽ രംഗത്ത് അർബുദം മുൻകൂട്ടി കണ്ടെത്താൻ പുതിയ എഐ ഇമേജിംഗ്...',
			'image'   => 'https://images.unsplash.com/photo-1576091160399-112ba8d25d1d?w=800&auto=format&fit=crop',
		],
		'opinions-analysis' => [
			'title'   => 'എഐയും തൊഴിൽ മേഖലയും: ഭാവിയിൽ മനുഷ്യന്റെ തൊഴിലവസരങ്ങൾ എങ്ങനെ മാറും? വിശകലനം',
			'content' => 'ആർട്ടിഫിഷ്യൽ ഇന്റലിജൻസ് സാങ്കേതികവിദ്യ ലോകമെമ്പാടും പടരുമ്പോൾ വരാനിരിക്കുന്ന തൊഴിൽ വിപ്ലവത്തെക്കുറിച്ചും പുതിയ കഴിവുകൾ സ്വായത്തമാക്കേണ്ട ആവശ്യകതയെക്കുറിച്ചുമുള്ള പ്രത്യേക വിശകലനം.',
			'excerpt' => 'എഐ കാലഘട്ടത്തിൽ തൊഴിൽ മേഖലയിലെ മാറ്റങ്ങളും അവസരങ്ങളും സംബന്ധിച്ച് സമഗ്ര പഠനം...',
			'image'   => 'https://images.unsplash.com/photo-1451187580459-43490279c0fa?w=800&auto=format&fit=crop',
		],
	];

	foreach ( $seed_data as $cat_slug => $data ) {
		$term   = get_term_by( 'slug', $cat_slug, 'category' );
		$cat_id = $term ? $term->term_id : 0;

		$existing = get_page_by_title( $data['title'], OBJECT, 'post' );
		if ( ! $existing ) {
			$post_id = wp_insert_post( [
				'post_title'   => $data['title'],
				'post_content' => $data['content'],
				'post_excerpt' => $data['excerpt'],
				'post_status'  => 'publish',
				'post_type'    => 'post',
				'post_category'=> $cat_id ? [ $cat_id ] : [],
			] );

			if ( $post_id && ! is_wp_error( $post_id ) && ! empty( $data['image'] ) ) {
				update_post_meta( $post_id, 'upload_image', $data['image'] );
			}
		}
	}
}

/**
 * Auto-create Privacy Policy, Terms of Use, Disclaimer, Contact Us pages if missing.
 */
add_action( 'init', 'aikairali_seed_legal_pages', 25 );
function aikairali_seed_legal_pages() {
	$pages = [
		'Privacy Policy' => 'privacy-policy',
		'Terms of Use'   => 'terms-of-use',
		'Disclaimer'     => 'disclaimer',
		'Contact Us'     => 'contact-us',
	];

	foreach ( $pages as $title => $slug ) {
		$existing = get_page_by_path( $slug );
		if ( ! $existing ) {
			wp_insert_post( [
				'post_title'  => $title,
				'post_name'   => $slug,
				'post_status' => 'publish',
				'post_type'   => 'page',
			] );
		}
	}
}



