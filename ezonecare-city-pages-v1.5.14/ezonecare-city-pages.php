<?php
/**
 * Plugin Name: EzoneCare City Pages
 * Plugin URI:  https://ezonecare.com/ez-plugin-docs/city-pages
 * Description: Har city ka page design, ACF data fields, aur SEO schema handle karta hai. City add karo WP Admin mein — page automatically ban jata hai. DEPENDENCY: ezonecare-routing + ACF plugin zaroori hain.
 * Version:     1.5.14
 * Author:      EzoneCare Development
 * Author URI:  https://ezonecare.com
 * Text Domain: ezonecare-city-pages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 *
 * =============================================================
 * YE PLUGIN KYA KARTA HAI — SIMPLE EXPLANATION
 * =============================================================
 *
 * PROBLEM JO YE SOLVE KARTA HAI:
 * Har city ka page manually banana mushkil tha.
 * Ye plugin ek city WP Admin mein add karo — poora page
 * automatically ban jata hai apne design ke saath.
 *
 * CITY ADD KARNE KA PROCESS:
 *   1. WP Admin → Cities → Add New
 *   2. City name likho (e.g. "Ranchi")
 *   3. Meta box mein fill karo:
 *      - Service Center Name
 *      - Area/Locality
 *      - Full Address
 *      - Pincode
 *      - State
 *      - Phone Number
 *      - WhatsApp Number
 *      - Working Hours
 *      - Google Maps URL
 *      - 2 Photos (entry + internal)
 *      - City Intro Headline
 *      - City Intro Text
 *   4. Active services assign karo (meta box se)
 *   5. Publish karo — page ready!
 *
 * PAGES JO AUTOMATICALLY BANTE HAIN:
 *   /ranchi/                        → City home (single-city.php)
 *   /ranchi/laptop-repair-services/ → Services list (single-city-services.php)
 *   /ranchi/laptop-screen-replacement/      → Service detail (single-city-service.php)
 *   /ranchi/laptop-screen-replacement/acer/ → Brand service (single-city-service-brand.php)
 *   /ranchi/about/                  → About page (single-city-about.php)
 *   /ranchi/contact/                → Contact page (single-city-contact.php)
 *
 * INCLUDED FILES:
 *   templates/single-city.php              → City home page design
 *   templates/single-city-services.php     → Services list page
 *   templates/single-city-service.php      → Individual service page
 *   templates/single-city-service-brand.php → Brand-specific service page
 *   templates/single-city-about.php        → About page
 *   templates/single-city-contact.php      → Contact page
 *   templates/ez-header.php                → City pages ka common header
 *   templates/ez-footer.php                → City pages ka common footer
 *   includes/class-city-acf.php            → ACF fields register + data fetch
 *   includes/class-seo-handler.php         → Meta title, description, Schema.org
 *   includes/class-template-loader.php     → Sahi template load karta hai
 *   admin/class-city-meta-box.php          → WP Admin mein service assign karne ka UI
 *
 * ACF FIELDS LIST:
 *   center_name, area, address, pincode, state, phone,
 *   whatsapp, hours, map_url, photo_entry, photo_internal,
 *   tagline, city_intro_headline, city_intro_text
 *
 * FUTURE PLANS (YAD RAKHNA):
 *   - TV/Mobile ke liye alag service center support
 *     (ek city mein laptop aur TV ke alag alag centers ho sakte hain)
 *   - State-wise city grouping
 *
 * DEPENDENCY:
 *   ezonecare-routing plugin ZAROOR active hona chahiye
 *   ACF (Advanced Custom Fields) plugin bhi chahiye
 *
 * =============================================================
 * CHANGELOG
 * =============================================================
 *
 * v1.5.12 - 12 Apr 2026
 *   ADDED: {city_slug} Elementor Link Support
 *          Elementor Container/Button ke URL mein {city_slug} placeholder use kar sako
 *          Example: https://ezonecare.com/{city_slug}/dell-laptop-repair
 *          Plugin automatically detect karta hai current city aur replace karta hai
 *          Kaam karta hai: Containers, Buttons, Image links — sabhi Elementor elements pe
 *          wp_footer mein JS inject hota hai — EZ_CITY_SLUG global variable set hota hai
 *          DOMContentLoaded pe sabhi matching hrefs replace ho jaate hain
 *          Sirf city pages pe run hota hai (has_validated_data check)
 *   NOTE:  class-internal-links.php ka purana PHP-based internal linking code
 *          abhi COMMENT kiya gaya hai — future use ke liye preserve hai
 *          Ab Elementor se directly {city_slug} placeholder use karo
 *
 * v1.5.0 - 19 Mar 2026
 *   REDESIGN: City Bar — Light background (#f8faff), professional look
 *          Service center name: larger, blue, prominent
 *          Separated from photo visually — no longer dark bar
 *   REDESIGN: Intro text — Clean paragraphs, NO box/border
 *          Removed: blue left border, background color
 *          Added: dark black text (#111827), normal weight (not italic)
 *          Good line-height, proper breathing room
 *          Professional directory style — JustDial/Sulekha standard
 *   REDESIGN: H1 + Intro spacing — proper visual hierarchy
 *          Clear breathing room between each section
 *
 * v1.5.2 - 23 Mar 2026
 *   FIXED: SEO Handler v5.2 — Meta description Google ko bilkul nahi ja rahi thi
 *          Root cause: Rank Math 'rank_math/frontend/description' filter
 *          city pages pe kaam nahi karta tha — blank description Google ko jaati thi
 *          Fix 1: Meta description directly wp_head mein print karo (priority 1)
 *                 Rank Math pe depend karna band kiya
 *          Fix 2: OG tags (og:title, og:description, og:url, og:image) directly print
 *          Fix 3: Robots tag directly print
 *          Fix 4: Rank Math description suppress karo city pages pe (rm_suppress)
 *                 Pehle duplicate meta description aa sakti thi
 *
 * v1.5.1 - 22 Mar 2026
 *   ADDED: "Why Choose Us" ACF field in SEO Content Tab
 *          Field: city_why_choose (textarea, 100-120 words)
 *          Rendered on city home page — between Disclaimer and Google Map
 *          Placeholder processing: {center} {city} {area} etc. supported
 *          Key phrases auto-bolded: chip-level, genuine parts, pickup and drop etc.
 *          AI Step 1 Output 5 directly paste karo is field mein
 *
 * v1.4.9 - 19 Mar 2026
 *   IMPROVED: City Bar position — ab Photos ke BAAD, H1 se PEHLE
 *          Order: Photos → City Bar → H1 → Intro Box → Content
 *          Reason: Photo pehle trust signal, City Bar info natural flow mein
 *   IMPROVED: Service center name font size bada kiya City Bar mein
 *          font-size: 0.95em → 1.25em, font-weight: 700 → 800
 *   REMOVED: Template inline CTA box ("Book Dell Laptop Repair in...")
 *          Footer ka CTA strip already yahi kaam karta hai better design mein
 *          3 CTAs back-to-back tha — clutter remove kiya
 *          Affected: single-city-service-brand.php + single-city-service.php
 *
 * v1.4.8 - 18 Mar 2026
 *   IMPROVED: Page layout — Photos ab H1+Intro se PEHLE aati hain
 *          New order: City Bar → Photos → H1 → Intro Box → Content
 *          Pehle: City Bar → H1 → Intro → Photos → Content
 *          Reason: Visual trust signal (shop photo) pehle dikhna chahiye
 *          Both templates updated: single-city-service.php + single-city-service-brand.php
 *   IMPROVED: Intro box full-width background — baaki content se aligned
 *
 * v1.4.7 - 17 Mar 2026
 *   ADDED: ACF Local Intro box — Auto Default Text System
 *          acf/load_value hook — field empty ho to service post ka
 *          raw Elementor content (with {placeholders}) auto-load hota hai
 *          Admin ko box mein default text pre-filled milta hai
 *          DB mein kuch nahi likhta jab tak admin Save na kare
 *   ADDED: 3-state Progress System
 *          Customized = Admin ne khud text save kiya → ✅ Green
 *          Default     = Auto-loaded, save nahi kiya → 🔵 Blue
 *          Overall % = (customized × 1.0 + default × 0.5) / total
 *   ADDED: "Reset to Default" button per intro field
 *          Click → DB se custom text delete → next load pe default wapas
 *   FIXED: v1.4.4 excerpt placeholder bug (single-city.php)
 *
 * v1.4.3 - 06 Mar 2026
 *   FIXED: Performance score 39 → ~60+ (Mobile PageSpeed)
 *   FIXED: LCP 7.0s → improved
 *          Entry photo pe fetchpriority="high" + loading="eager" + decoding="async"
 *          Internal photo pe loading="lazy" + decoding="async"
 *          width/height attributes added — browser pre-calculate kar sakta hai space
 *   FIXED: CLS 0.614 → near 0
 *          ez-city-hero-photos min-height define kiya
 *          ez-photos-section min-height define kiya
 *          Background color placeholder — shift nahi hoga
 *   FIXED: Google Maps JS 378 KiB → 0 on initial load
 *          Click-to-Load pattern — "Show on Map" button
 *          Maps JS sirf tab load hoti hai jab user click kare
 *          Est. saving: 200ms + 378 KiB unused JS removed
 *
 * v1.4.2 - 05 Mar 2026
 *   ADDED: Focus Keyword — Rank Math ko auto send karta hai
 *          Pattern: "Service Name City Name" (e.g. "Dell Laptop Repair Ranchi")
 *          Routes: city, city-service, city-service-brand, city-services
 *   ADDED: Internal Links section — brand service pages pe
 *          Same brand ke related services auto-detect aur card grid mein show
 *          Dell Laptop Repair → Dell Keyboard, Battery, Screen, Motherboard links
 *          Slug-based brand prefix detection (dell-, acer-, hp- etc.)
 *          City active_brands ACF field se filter hota hai
 *
 * v1.4.1 - 05 Mar 2026
 *   FIXED: {city} {center} placeholders replace nahi ho rahe the
 *          Root cause: Elementor renders AFTER apply_filters('the_content')
 *          Isliye str_replace pehle kaam karta tha, Elementor baad mein override karta tha
 *          Fix: ob_start() → apply_filters → ob_get_clean() → THEN Placeholder::process()
 *          Ab Elementor pehle HTML render karta hai, hum baad mein replace karte hain
 *
 * v1.4.0 - 05 Mar 2026
 *   ADDED: Placeholder replacement system
 *          {city} {center} {area} {landmark} {phone} {pincode} {state} {address}
 *          Single-city-service.php + single-city-service-brand.php dono mein
 *   ADDED: Local Intro system — slug-based ACF field auto-match
 *          Service slug → city_local_intro_{slug} field → custom city intro
 *          Field empty → default content with placeholders
 *   ADDED: 10 Brand Local Intro ACF fields (Dell×5 + Acer×5)
 *   ADDED: Progress Indicator meta box (city edit page sidebar)
 *          Shows % completion, filled/pending lists per city
 *   ADDED: {landmark} ACF field support (city_landmark)
 *
 * v1.2.9 - 01 Mar 2026
 *   FIXED: 3 templates mein purana get_schema() call tha — double LocalBusiness ban raha tha
 *          single-city-contact, single-city-service, single-city-service-brand
 *          Ab sirf class-seo-handler.php v5.0 LocalBusiness wp_head se print karta hai
 *
 * v1.2.8 - 01 Mar 2026 (aapka version)
 *
 * v1.3.3 - 01 Mar 2026
 *   FIX: Double meta description — add_meta_tags() se print hataya
 *        Sirf Rank Math rm_description() filter se ek tag print hoti hai
 *   FIX: Meta description too long — max 155 chars limit add ki
 *        Short address logic: full address ki jagah locality only
 *
 * v1.3.2 - 01 Mar 2026
 *   UI: City Intro Section — Option D Dark/Navy Highlight Card
 *       Gradient dark background, blue highlight headline, auto-bold key phrases
 *       3-item icon strip: Repairs count, Since year, Rating
 *       ACF fields: city_since_year, city_repair_count, city_rating (optional)
 *
 * v1.3.1 - 01 Mar 2026
 *   SEO: ez-footer.php mein 'Our Service Cities' strip add ki
 *        Bottom bar ke upar, state-wise grouped, current city skip
 *        Google ke liye strong internal linking — sabhi city pages connected
 *        Future: state click filter baad mein add hoga
 *
 * v1.3.0 - 01 Mar 2026
 *   UI: Nav brand — center_name bada headline, city name chota subtitle
 *   UI: Contact card — 3D card design, hover lift effect, gradient top border
 *   FIX: 3 templates mein get_schema() double LocalBusiness — removed
 *
 * v1.2.8 - 01 Mar 2026 (aapka version)
 *
 * v1.2.1 - 01 Mar 2026
 *   FIXED: OG Tags duplicate — Rank Math ke OG/Twitter tags city pages pe cancel
 *   FIXED: BreadcrumbList Schema duplicate — Rank Math ka breadcrumb cancel
 *   FIXED: LocalBusiness Schema URL galat tha (/?city=) — ab correct city URL
 *   FIXED: Double LocalBusiness schema — Rank Math ka LB schema city pages pe disable
 *   FIXED: Double header — Astra WP header city pages pe completely hidden
 *   FIXED: Double footer — Astra WP footer city pages pe completely hidden
 *
 * v1.2.0 - 01 Mar 2026
 *   FIXED: Double canonical tag issue — Bing Webmaster Tools warning fix
 *          Rank Math city pages pe apna canonical alag se print kar raha tha
 *          Ab sirf plugin ka canonical hoga — Rank Math ka cancel hoga
 *          Affected: /faiz-computer/, /new-delhi/ aur sabhi city pages
 *
 * v1.1.3 - 27 Feb 2026
 *   ADDED: Plugin documentation — View Details mein puri info
 *          (City add process, templates list, ACF fields, future plans, changelog)
 *
 * v1.0.0 - Feb 2026
 *   ADDED: City post type registration
 *   ADDED: All ACF fields (address, phone, photos, etc.)
 *   ADDED: All page templates (city, service, brand, about, contact)
 *   ADDED: Schema.org LocalBusiness markup
 *   ADDED: City-specific header/footer with nav
 *   ADDED: Service meta box (assign services per city)
 *   ADDED: SEO handler (meta title, description per page type)
 *
 * =============================================================
 *
 * @package EzonecareCityPages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// ── VERSION & CONSTANTS ───────────────────────────────────────
define( 'EZ_CITY_VERSION',      '1.5.14' );
define( 'EZ_CITY_PLUGIN_DIR',   plugin_dir_path( __FILE__ ) );
define( 'EZ_CITY_PLUGIN_URL',   plugin_dir_url( __FILE__ ) );
define( 'EZ_CITY_PLUGIN_FILE',  __FILE__ );

/**
 * Main city pages plugin class — Singleton
 */
class Ezonecare_City_Pages {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {

        // ── DEPENDENCY CHECK ─────────────────────────────────
        // Plugin 1 (ezonecare-routing) active hona chahiye
        add_action( 'admin_notices',   array( $this, 'check_dependency' ) );
        add_action( 'plugins_loaded',  array( $this, 'boot' ), 20 );

        // Lifecycle
        register_activation_hook( __FILE__,  array( $this, 'on_activate' ) );
        register_deactivation_hook( __FILE__, array( $this, 'on_deactivate' ) );
    }

    /**
     * Boot — sirf tab jab routing plugin active ho
     */
    public function boot() {

        // Routing plugin ka core class exist karta hai?
        if ( ! class_exists( 'Ezonecare_Query_Handler' ) ) {
            // Routing plugin active nahi — kuch load mat karo
            return;
        }

        $this->load_dependencies();
        $this->init_components();
        $this->init_elementor();
    }

    private function load_dependencies() {
        require_once EZ_CITY_PLUGIN_DIR . 'includes/class-city-acf.php';
        require_once EZ_CITY_PLUGIN_DIR . 'includes/class-placeholder.php';
        require_once EZ_CITY_PLUGIN_DIR . 'includes/class-internal-links.php';
        require_once EZ_CITY_PLUGIN_DIR . 'admin/class-progress-indicator.php';
        require_once EZ_CITY_PLUGIN_DIR . 'includes/class-seo-handler.php';
        require_once EZ_CITY_PLUGIN_DIR . 'includes/class-template-loader.php';
        require_once EZ_CITY_PLUGIN_DIR . 'admin/class-city-meta-box.php';
    }

    private function init_components() {
        Ezonecare_City_ACF::get_instance();
        Ezonecare_SEO_Handler::get_instance();

        // v1.5.2: LiteSpeed Cache — city pages ko cache se bahar rakho
        // Meta description dynamic hai — cached HTML mein nahi hogi
        add_action( 'wp_head', array( $this, 'set_no_cache_headers' ), 0 );
        Ezonecare_Template_Loader::get_instance();
        Ezonecare_City_Meta_Box::get_instance();
        Ezonecare_Progress_Indicator::get_instance();

        // ACF: Brand Service Links field on Service CPT
        add_action( 'acf/init', array( $this, 'register_service_brand_field' ) );

        // v1.5.8: Universal load_value hook (fully dynamic — koi hardcoded slug nahi)
        add_action( 'acf/init', array( 'Ezonecare_City_ACF', 'register_load_value_hooks' ) );

        // v1.5.1: Why Choose Us default fallback hook
        add_action( 'acf/init', array( 'Ezonecare_City_ACF', 'register_why_choose_hook' ) );

        // v1.5.8: Dynamic Local Intro fields — city ke assigned brands se auto-generate
        add_action( 'acf/init', array( 'Ezonecare_City_ACF', 'register_dynamic_local_intro_fields' ) );

        // v1.4.7: AJAX reset intro
        add_action( 'wp_ajax_ez_reset_intro', array( 'Ezonecare_City_ACF', 'ajax_reset_intro' ) );

        // v1.5.14: AJAX update intro (sirf ek field save)
        add_action( 'wp_ajax_ez_update_intro', array( 'Ezonecare_City_ACF', 'ajax_update_intro' ) );

        // v1.5.12: {city_slug} placeholder — Elementor links ke liye JS inject karo
        add_action( 'wp_footer', array( $this, 'inject_city_context' ), 5 );
    }

    /**
     * Elementor assets — city+service+brand sabhi pages pe load karo
     */
    private function init_elementor() {
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_elementor_assets' ), 99 );
    }

    public function enqueue_elementor_assets() {
        $handler = Ezonecare_Query_Handler::get_instance();
        if ( ! $handler->has_validated_data() ) return;

        if ( class_exists( '\Elementor\Plugin' ) ) {
            \Elementor\Plugin::$instance->frontend->enqueue_styles();
            \Elementor\Plugin::$instance->frontend->enqueue_scripts();
        }
    }

    /**
     * ACF: Brand Service Links — Service CPT pe
     */
    public function register_service_brand_field() {
        if ( ! function_exists( 'acf_add_local_field_group' ) ) return;

        acf_add_local_field_group( array(
            'key'    => 'group_service_brand_links',
            'title'  => 'Brand Service Links',
            'fields' => array(
                array(
                    'key'           => 'field_service_brand_posts',
                    'label'         => 'Related Brand Posts',
                    'name'          => 'service_brand_posts',
                    'type'          => 'relationship',
                    'instructions'  => 'Select brand-specific service posts for this pillar service. E.g. "Laptop Repair Services" → Dell Laptop Repair, HP Laptop Repair, Acer Laptop Repair etc.',
                    'required'      => 0,
                    'post_type'     => array( 'service' ),
                    'filters'       => array( 'search' ),
                    'return_format' => 'object',
                    'ui'            => 1,
                ),
            ),
            'location' => array(
                array(
                    array(
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'service',
                    ),
                ),
            ),
            'menu_order'      => 5,
            'position'        => 'normal',
            'style'           => 'default',
            'label_placement' => 'top',
            'active'          => true,
        ) );
    }

    // ── DEPENDENCY CHECK ─────────────────────────────────────

    /**
     * Admin warning agar routing plugin active nahi hai
     */
    public function check_dependency() {

        // Routing plugin active hai?
        if ( class_exists( 'Ezonecare_Query_Handler' ) ) return;

        // Warning show karo
        echo '<div class="notice notice-error">
            <p>
                <strong>⚠️ EzoneCare City Pages — Dependency Missing!</strong><br>
                Ye plugin kaam nahi karega jab tak <strong>EzoneCare Routing</strong> plugin active nahi hai.<br>
                Please <strong>EzoneCare Routing</strong> plugin ko activate karo pehle.
            </p>
        </div>';
    }

    // ── LIFECYCLE ────────────────────────────────────────────

    /**
     * v1.5.2: City pages ke liye no-cache headers set karo
     * LiteSpeed Cache aur Cloudflare ko batao — yeh pages cache mat karo
     * Meta description dynamic hai — har request pe fresh chahiye
     */
    public function set_no_cache_headers() {
        if ( ! class_exists( 'Ezonecare_Query_Handler' ) ) return;
        if ( ! Ezonecare_Query_Handler::get_instance()->has_validated_data() ) return;

        // Browser cache headers
        if ( ! headers_sent() ) {
            header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
            header( 'Pragma: no-cache' );
        }

        // LiteSpeed Cache — do not cache this page
        do_action( 'litespeed_control_set_nocache', 'EzoneCare city page — dynamic meta' );

        // LiteSpeed ESI disable bhi karo
        define( 'LSCWP_ESI_EXCLUDED', true );
    }

    /**
     * v1.5.12: {city_slug} Elementor Link Support
     * =============================================
     * Elementor ke Container/Button URL mein aap {city_slug} likh sakte ho
     * Example: https://ezonecare.com/{city_slug}/dell-laptop-repair
     *
     * Yeh function current city ka slug detect karke page ke footer mein
     * ek chhota JS snippet inject karta hai. Woh JS snippet:
     *   1. EZ_CITY_SLUG global variable set karta hai (debugging ke liye bhi useful)
     *   2. DOMContentLoaded pe page ke sabhi <a> tags scan karta hai
     *   3. Jo bhi href mein {city_slug} ho usse replace kar deta hai
     *
     * ELEMENTOR MEIN KAISE USE KAREIN:
     *   Container => Advanced => Link => URL field mein likho:
     *   https://ezonecare.com/{city_slug}/dell-laptop-repair
     *   Plugin automatically sahi city URL bana dega.
     *
     * SUPPORTED ELEMENTS:
     *   - Elementor Container (a-tag mode)
     *   - Elementor Button widget
     *   - Elementor Image widget (link)
     *   - Koi bhi <a> tag jisme {city_slug} ho
     *
     * NOTE: Sirf city pages pe run hota hai — regular WP pages pe nahi.
     */
    public function inject_city_context() {

        // Sirf city pages pe chalao
        if ( ! class_exists( 'Ezonecare_Query_Handler' ) ) return;
        $handler = Ezonecare_Query_Handler::get_instance();
        if ( ! $handler->has_validated_data() ) return;

        $city_post = $handler->get_city();
        if ( ! $city_post ) return;

        $city_slug = $city_post->post_name;
        $city_name = $city_post->post_title;
        $base_url  = home_url( '/' );

        ?>
        <style>
        /*
         * EzoneCare v1.5.13 — ez-cover-link system
         *
         * ELEMENTOR FREE MEIN KAISE USE KAREIN:
         * ----------------------------------------
         * 1. Container ka HTML Tag = div (link nahi)
         * 2. Container ke andar ek "HTML" widget add karo
         * 3. HTML widget mein sirf yeh likho:
         *
         *    <a class="ez-cover-link"
         *       href="https://EZCITYLINK:dell-laptop-repair-service">
         *    </a>
         *
         *    (sirf service slug badlo — city automatically aayegi)
         *
         * Plugin JS "EZCITYLINK:slug" ko real city URL mein convert karta hai.
         * CSS is invisible <a> ko poore container pe stretch kar deta hai.
         * ----------------------------------------
         */

        /* Parent container — relative positioning zaroori hai */
        .elementor-element:has(> .elementor-widget-wrap > .elementor-element .ez-cover-link),
        .elementor-element:has(.ez-cover-link) {
            position: relative !important;
        }

        /* Invisible full-cover link */
        .ez-cover-link {
            position: absolute !important;
            inset: 0 !important;          /* top/right/bottom/left = 0 */
            z-index: 10 !important;
            display: block !important;
            text-decoration: none !important;
            background: transparent !important;
            width: 100% !important;
            height: 100% !important;
        }

        /* Hover pe parent card ka subtle effect */
        .elementor-element:has(.ez-cover-link):hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.10);
            transform: translateY(-2px);
            transition: all 0.25s ease;
        }
        </style>

        <script>
        /* EzoneCare v1.5.13 — EZCITYLINK Resolver
         *
         * HTML widget mein likho:
         * <a class="ez-cover-link" href="https://EZCITYLINK:dell-laptop-repair-service"></a>
         *
         * Plugin real URL banayega: /ranchi/dell-laptop-repair-service/
         */
        (function() {
            var CITY = '<?php echo esc_js( $city_slug ); ?>';
            var BASE = '<?php echo esc_js( $base_url ); ?>';

            function ezResolveLinks() {
                // EZCITYLINK: prefix wale sabhi links resolve karo
                var links = document.querySelectorAll( 'a[href*="EZCITYLINK:"]' );
                for ( var i = 0; i < links.length; i++ ) {
                    var raw  = links[i].getAttribute( 'href' );
                    // "https://EZCITYLINK:dell-laptop-repair-service" se slug nikalo
                    var slug = raw.replace( /^.*EZCITYLINK:/i, '' ).replace( /\/+$/, '' ).trim();
                    if ( slug ) {
                        links[i].href = BASE + CITY + '/' + slug + '/';
                    }
                }
            }

            if ( document.readyState === 'loading' ) {
                document.addEventListener( 'DOMContentLoaded', ezResolveLinks );
            } else {
                ezResolveLinks();
            }
        })();
        </script>
        <?php
    }

        public function on_activate() {
        set_transient( 'ez_city_pages_activated', true, 30 );
    }

    public function on_deactivate() {
        // Templates unload ho jaayenge — routing intact rahegi
    }
}

// Boot
Ezonecare_City_Pages::get_instance();
