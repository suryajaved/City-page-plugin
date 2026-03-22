<?php
/**
 * Plugin Name: EzoneCare Routing
 * Plugin URI:  https://ezonecare.com/ez-plugin-docs/routing
 * Description: EzoneCare ka core URL routing engine. City/Service/Brand pages ke liye custom URLs handle karta hai. Saath hi automatic XML sitemap bhi generate karta hai. DEPENDENCY: ezonecare-city-pages plugin bhi active hona chahiye.
 * Version:     1.3.0
 * Author:      EzoneCare Development
 * Author URI:  https://ezonecare.com
 * Text Domain: ezonecare-routing
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * =============================================================
 * YE PLUGIN KYA KARTA HAI — SIMPLE EXPLANATION
 * =============================================================
 *
 * PROBLEM JO YE SOLVE KARTA HAI:
 * WordPress normally custom URLs nahi bana sakta jaise:
 *   ezonecare.com/ranchi/laptop-repair/acer/
 * Ye plugin ye sab possible banata hai.
 *
 * URL STRUCTURE JO YE HANDLE KARTA HAI:
 *   /ranchi/                          → City home page
 *   /ranchi/laptop-repair-services/   → City service list
 *   /ranchi/laptop-screen-replacement/         → City specific service
 *   /ranchi/laptop-screen-replacement/acer/    → City + Service + Brand
 *   /ranchi/about/                    → City about page
 *   /ranchi/contact/                  → City contact page
 *
 * SITEMAP:
 *   /ez-cities-sitemap.xml → Saari city URLs ka XML sitemap
 *   Ye automatically sitemap_index.xml mein add hota hai (Rank Math)
 *
 * INCLUDED FILES:
 *   includes/class-rewrite-rules.php  → Custom URL rules register karta hai
 *   includes/class-query-handler.php  → URL parse karke sahi city/service dhundta hai
 *   includes/class-validator.php      → City/service slugs validate karta hai
 *   includes/class-sitemap.php        → XML sitemap generate karta hai
 *
 * DEPENDENCY:
 *   ezonecare-city-pages plugin ZAROOR active hona chahiye
 *   ACF plugin bhi chahiye (city data ke liye)
 *
 * KAB UPDATE KARE:
 *   Sirf tab jab URL structure change karna ho ya sitemap mein kuch add karna ho
 *
 * =============================================================
 * CHANGELOG
 * =============================================================
 *
 * v1.2.5 - 08 Mar 2026
 *   FIXED: Sitemap duplicate URL root cause properly fix kiya
 *          v1.2.4 mein hardcoded line hatayi thi lekin duplicate abhi bhi aa rahi thi
 *          Asli wajah: Database mein same slug ke 2 service posts hain
 *          Fix: $added[] tracker array add kiya — koi bhi URL sirf 1 baar add hogi
 *          Cache key v3 → v4 update kiya — purana cached data auto-expire hoga
 *
 * v1.2.4 - 08 Mar 2026
 *   FIXED: Sitemap mein /city/laptop-repair-services/ URL duplicate aa rahi thi
 *          Root cause: Hardcoded 'laptop-repair-services/' line alag se add
 *          ho rahi thi, aur service post loop mein wahi slug dobara add ho raha tha
 *          Fix: Hardcoded line remove ki — service loop khud sahi URL generate karta hai
 *          Affected cities: Ranchi, New Delhi, Chandrapur — teeno mein duplicate tha
 *
 * v1.2.2 - 27 Feb 2026
 *   ADDED: Plugin documentation — View Details mein puri info
 *          (Kya karta hai, files list, ACF fields, future plans, changelog)
 *
 * v1.2.1 - 27 Feb 2026
 *   FIXED: X-Robots-Tag noindex header remove kiya ez-cities-sitemap.xml se
 *          (Google sitemap fetch nahi kar pa raha tha — 50 din ki problem solve)
 *
 * v1.2.0 - 27 Feb 2026
 *   ADDED: class-sitemap.php — automatic XML sitemap generation
 *   ADDED: Rank Math sitemap_index.xml mein city sitemap ka link
 *   FIXED: String/array compatibility issue with rank_math/sitemap/index hook
 *   FIXED: Plugin folder name ezonecare-routing-v1.1.0 → ezonecare-routing
 *
 * v1.1.0 - 26 Feb 2026
 *   ADDED: fix_trailing_slash() — Opera browser trailing slash bug fix
 *   FIXED: ServerByt hosting .htaccess limitation bypass
 *
 * v1.0.0 - Initial Release
 *   ADDED: Custom URL rewrite rules (class-rewrite-rules.php)
 *   ADDED: Query handler for city/service/brand resolution
 *   ADDED: Slug validator
 *
 * =============================================================
 *
 * @package EzonecareRouting
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── VERSION & CONSTANTS ───────────────────────────────────────
define( 'EZ_ROUTING_VERSION',     '1.3.0' );
define( 'EZ_ROUTING_PLUGIN_DIR',  plugin_dir_path( __FILE__ ) );
define( 'EZ_ROUTING_PLUGIN_URL',  plugin_dir_url( __FILE__ ) );
define( 'EZ_ROUTING_PLUGIN_FILE', __FILE__ );

/**
 * Main routing plugin class — Singleton
 */
class Ezonecare_Routing {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        $this->load_dependencies();
        $this->init();
    }

    private function load_dependencies() {
        require_once EZ_ROUTING_PLUGIN_DIR . 'includes/class-rewrite-rules.php';
        require_once EZ_ROUTING_PLUGIN_DIR . 'includes/class-query-handler.php';
        require_once EZ_ROUTING_PLUGIN_DIR . 'includes/class-validator.php';
        require_once EZ_ROUTING_PLUGIN_DIR . 'includes/class-sitemap.php';
    }

    private function init() {
        // Boot core components
        Ezonecare_Rewrite_Rules::get_instance();
        Ezonecare_Query_Handler::get_instance();
        Ezonecare_Validator::get_instance();
        Ezonecare_Sitemap::get_instance();

        // ── TRAILING SLASH FIX ───────────────────────────────
        // ServerByt pe .htaccess trailing slash rules kaam nahi karte
        // Isliye PHP se pehle hi URL fix kar dete hain
        add_action('init', array($this, 'fix_trailing_slash'), 0);

        // Lifecycle hooks
        register_activation_hook( __FILE__,   array( $this, 'on_activate' ) );
        register_deactivation_hook( __FILE__,  array( $this, 'on_deactivate' ) );

        // Admin notice
        add_action( 'admin_notices', array( $this, 'admin_notices' ) );

        // Full-width layout for custom routes (Astra theme)
        add_filter( 'astra_page_layout',         array( $this, 'force_full_width' ) );
        add_filter( 'astra_post_layout',          array( $this, 'force_full_width' ) );
        add_filter( 'body_class',                 array( $this, 'add_body_classes' ) );
        add_filter( 'astra_sidebar_layout_class', array( $this, 'remove_sidebar_class' ) );
    }

    // ── TRAILING SLASH FIX ───────────────────────────────────
    /**
     * ServerByt pe .htaccess trailing slash rewrite kaam nahi karta
     * Ye method PHP level pe URL parse karke custom_city query var set karta hai
     * Jisse /ranchi/ aur /ranchi dono sahi kaam karein
     */
    public function fix_trailing_slash() {
        // Only run if not already handled
        if ( get_query_var('custom_city') || get_query_var('custom_city_route') ) {
            return;
        }

        $request_uri = isset($_SERVER['REQUEST_URI'])
            ? $_SERVER['REQUEST_URI']
            : '';

        // Clean URI — remove query string
        $path = parse_url($request_uri, PHP_URL_PATH);
        if ( empty($path) ) return;

        // Remove leading/trailing slashes
        $path = trim($path, '/');
        if ( empty($path) ) return;

        // Skip WP native paths
        $skip = array('wp-admin', 'wp-content', 'wp-includes', 'wp-json',
                      'feed', 'sitemap', 'uploads', 'wp-login.php',
                      'xmlrpc.php', 'wp-cron.php', 'index.php');
        $first_segment = explode('/', $path)[0];
        if ( in_array($first_segment, $skip) ) return;

        // Skip if it's a real file or directory
        $file_path = ABSPATH . $path;
        if ( file_exists($file_path) ) return;

        // Parse segments
        $segments = explode('/', $path);
        $count    = count($segments);

        // Only handle if not already in query vars
        if ( $count === 1 && !empty($segments[0]) ) {
            // /ranchi/ or /ranchi
            $city_slug = sanitize_title($segments[0]);

            // Skip reserved WP slugs
            $reserved = array('blog', 'page', 'author', 'search', 'tag',
                              'category', 'shop', 'cart', 'checkout',
                              'privacy-policy', 'terms-and-conditions',
                              'disclaimer', 'refund-policy', 'sample-page',
                              'home', 'feed', 'sitemap');
            if ( in_array($city_slug, $reserved) ) return;

            // Check if it's a real WP page/post first
            $existing = get_page_by_path($city_slug, OBJECT, array('page', 'post'));
            if ( $existing ) return;

            // Set query var so query handler picks it up
            set_query_var('custom_city', $city_slug);
            $_GET['custom_city']     = $city_slug;
            $_REQUEST['custom_city'] = $city_slug;
        }
    }

    // ── LAYOUT HELPERS ───────────────────────────────────────

    public function force_full_width( $layout ) {
        if ( Ezonecare_Query_Handler::get_instance()->has_validated_data() ) {
            return 'no-sidebar';
        }
        return $layout;
    }

    public function add_body_classes( $classes ) {
        if ( Ezonecare_Query_Handler::get_instance()->has_validated_data() ) {
            $classes[] = 'astra-full-width-layout';
            $classes[] = 'no-sidebar';
            $classes[] = 'ezonecare-custom-page';
            $classes   = array_diff( $classes, array( 'right-sidebar', 'left-sidebar', 'two-sidebar' ) );
        }
        return array_values( $classes );
    }

    public function remove_sidebar_class( $class ) {
        if ( Ezonecare_Query_Handler::get_instance()->has_validated_data() ) {
            return 'no-sidebar';
        }
        return $class;
    }

    // ── LIFECYCLE ────────────────────────────────────────────

    public function on_activate() {
        Ezonecare_Rewrite_Rules::get_instance()->add_rewrite_rules();
        flush_rewrite_rules();
        set_transient( 'ez_routing_activated', true, 30 );
    }

    public function on_deactivate() {
        flush_rewrite_rules();
    }

    // ── ADMIN NOTICES ────────────────────────────────────────

    public function admin_notices() {

        // Activation success
        if ( get_transient( 'ez_routing_activated' ) ) {
            echo '<div class="notice notice-success is-dismissible">
                <p><strong>✅ EzoneCare Routing v' . EZ_ROUTING_VERSION . '</strong> activated!
                URL routing is now ACTIVE. Make sure <strong>EzoneCare City Pages</strong> plugin is also active.</p>
            </div>';
            delete_transient( 'ez_routing_activated' );
        }

        // CPT checks
        if ( ! post_type_exists( 'service' ) ) {
            echo '<div class="notice notice-error">
                <p><strong>EzoneCare Routing:</strong> "service" CPT not found! Please register it via CPT UI plugin.</p>
            </div>';
        }

        if ( ! post_type_exists( 'city' ) ) {
            echo '<div class="notice notice-warning">
                <p><strong>EzoneCare Routing:</strong> "city" CPT not found. City pages will not work.
                Create City CPT using CPT UI with slug: <code>city</code></p>
            </div>';
        }

        if ( ! taxonomy_exists( 'brand' ) ) {
            echo '<div class="notice notice-error">
                <p><strong>EzoneCare Routing:</strong> "brand" taxonomy not found! Please register it via CPT UI plugin.</p>
            </div>';
        }
    }
}

// Boot
Ezonecare_Routing::get_instance();
