<?php
/**
 * EzoneCare Sitemap Generator
 *
 * ARCHITECTURE (v1.3.0):
 *
 *   sitemap_index.xml (Rank Math)
 *       └── /ez-cities-sitemap.xml        ← INDEX — city sitemaps ki list
 *               ├── /ez-city-ranchi-sitemap.xml
 *               ├── /ez-city-new-delhi-sitemap.xml
 *               └── /ez-city-{slug}-sitemap.xml  ← AUTO — nayi city add karo, auto aayega
 *
 * AUTO BEHAVIOUR:
 *   - Nayi city WP Admin mein publish karo
 *   - /ez-cities-sitemap.xml mein uski entry auto aayegi
 *   - /ez-city-{slug}-sitemap.xml URL auto kaam karega
 *   - Cache clear bhi auto hota hai save_post_city hook se
 *
 * @package EzonecareRouting
 * @version 1.3.0
 *
 * CHANGELOG:
 * v1.3.0 - 08 Mar 2026
 *   CHANGED: Architecture — single flat sitemap -> per-city sitemaps
 *   ADDED:   /ez-cities-sitemap.xml ab INDEX file hai (sitemapindex format)
 *            Har city ka apna /ez-city-{slug}-sitemap.xml hoga
 *   ADDED:   get_city_urls($city) — ek city ke URLs generate karta hai
 *   ADDED:   Per-city transient cache: ez_city_sitemap_{slug}_v1
 *   CHANGED: inject_into_index() — ab ek entry nahi, har city ka alag entry
 *   CHANGED: clear_cache() — ab per-city transients bhi delete karta hai
 *   ADDED:   Backward compat — get_all_city_urls() preserve kiya
 *   BENEFIT: Visual debugging easy — city-wise verify kar sakte ho
 *
 * v1.2.5 - 08 Mar 2026
 *   FIXED: Duplicate URL root cause — $added[] tracker
 *
 * v1.2.4 - 08 Mar 2026
 *   FIXED: Hardcoded laptop-repair-services/ line remove ki
 *
 * v1.2.3 - 04 Mar 2026
 *   FIXED: Sitemap lastmod date -> proper ISO 8601 timestamp
 *   ADDED: City sitemap generator + Rank Math index injection
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Ezonecare_Sitemap {

    private static $instance = null;

    const CITY_CACHE_PREFIX  = 'ez_city_sitemap_';
    const CITY_CACHE_VERSION = 'v1';

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Priority 1 — WP routing se pehle intercept
        add_action( 'init', array( $this, 'handle_sitemap_request' ), 1 );

        // /ez-cities-sitemap.xml rewrite rule
        add_action( 'wp_loaded', array( $this, 'add_sitemap_rewrite' ) );

        // Rank Math sitemap_index.xml mein inject
        add_filter( 'rank_math/sitemap/index', array( $this, 'inject_into_index' ) );

        // Cache auto-clear hooks
        add_action( 'save_post_city',    array( $this, 'clear_cache' ) );
        add_action( 'save_post_service', array( $this, 'clear_cache' ) );
        add_action( 'edited_brand',      array( $this, 'clear_cache' ) );
        add_action( 'create_brand',      array( $this, 'clear_cache' ) );
    }

    // =========================================================
    // REQUEST HANDLER
    // =========================================================

    /**
     * 2 URL patterns handle karo:
     *   /ez-cities-sitemap.xml           -> city sitemaps ka index
     *   /ez-city-{slug}-sitemap.xml      -> ek city ki URLs
     */
    public function handle_sitemap_request() {
        if ( ! isset( $_SERVER['REQUEST_URI'] ) ) return;

        $path = parse_url( $_SERVER['REQUEST_URI'], PHP_URL_PATH );
        $path = rtrim( $path, '/' );

        // Pattern 1: Index
        if ( $path === '/ez-cities-sitemap.xml' ) {
            $this->serve_index_sitemap();
            exit;
        }

        // Pattern 2: Per-city — /ez-city-ranchi-sitemap.xml
        if ( preg_match( '#^/ez-city-([a-z0-9\-]+)-sitemap\.xml$#', $path, $matches ) ) {
            $this->serve_city_sitemap( sanitize_title( $matches[1] ) );
            exit;
        }
    }

    // =========================================================
    // INDEX SITEMAP — /ez-cities-sitemap.xml
    // =========================================================

    /**
     * sitemapindex format — har city ka alag sitemap entry
     * Nayi city publish karo -> automatically yahan aayegi
     */
    private function serve_index_sitemap() {
        $cities = $this->get_published_cities();

        status_header( 200 );
        header( 'Content-Type: application/xml; charset=UTF-8' );

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ( $cities as $city ) {
            $loc     = home_url( '/ez-city-' . $city->post_name . '-sitemap.xml' );
            $lastmod = get_post_modified_time( 'Y-m-d\TH:i:s+00:00', true, $city->ID );
            if ( ! $lastmod ) $lastmod = date( 'Y-m-d\TH:i:s+00:00' );

            echo "\t<sitemap>\n";
            echo "\t\t<loc>"     . esc_url(  $loc     ) . "</loc>\n";
            echo "\t\t<lastmod>" . esc_html( $lastmod ) . "</lastmod>\n";
            echo "\t</sitemap>\n";
        }

        echo '</sitemapindex>';
    }

    // =========================================================
    // PER-CITY SITEMAP — /ez-city-{slug}-sitemap.xml
    // =========================================================

    /**
     * Ek city ki sitemap serve karo
     * City exist nahi karti -> 404
     */
    private function serve_city_sitemap( $city_slug ) {
        $city = $this->find_city_by_slug( $city_slug );

        if ( ! $city ) {
            status_header( 404 );
            header( 'Content-Type: application/xml; charset=UTF-8' );
            echo '<?xml version="1.0" encoding="UTF-8"?>';
            echo '<!-- EzoneCare: City not found [' . esc_html( $city_slug ) . '] -->';
            return;
        }

        $urls = $this->get_city_urls( $city );

        status_header( 200 );
        header( 'Content-Type: application/xml; charset=UTF-8' );

        echo '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        foreach ( $urls as $url ) {
            echo "\t<url>\n";
            echo "\t\t<loc>"        . esc_url(  $url['loc']        ) . "</loc>\n";
            echo "\t\t<lastmod>"    . esc_html( $url['lastmod']    ) . "</lastmod>\n";
            echo "\t\t<changefreq>" . esc_html( $url['changefreq'] ) . "</changefreq>\n";
            echo "\t\t<priority>"   . esc_html( $url['priority']   ) . "</priority>\n";
            echo "\t</url>\n";
        }

        echo '</urlset>';
    }

    // =========================================================
    // URL GENERATORS
    // =========================================================

    /**
     * Ek city ke saare URLs generate karo — transient cached
     *
     * @param  WP_Post $city
     * @return array
     */
    public function get_city_urls( $city ) {
        $cache_key = self::CITY_CACHE_PREFIX . $city->post_name . '_' . self::CITY_CACHE_VERSION;
        $cached    = get_transient( $cache_key );
        if ( false !== $cached ) return $cached;

        $services = $this->get_published_services();
        $cb       = trailingslashit( home_url() ) . $city->post_name . '/';
        $today    = date( 'Y-m-d\TH:i:s+00:00' );
        $urls     = array();
        $added    = array();

        $add = function( $loc, $changefreq, $priority ) use ( &$urls, &$added, $today ) {
            if ( isset( $added[ $loc ] ) ) return;
            $added[ $loc ] = true;
            $urls[] = array(
                'loc'        => $loc,
                'lastmod'    => $today,
                'changefreq' => $changefreq,
                'priority'   => $priority,
            );
        };

        $add( $cb,              'weekly',  '0.9' );
        $add( $cb . 'about/',   'monthly', '0.5' );
        $add( $cb . 'contact/', 'monthly', '0.5' );

        foreach ( $services as $service ) {
            $s = $service->post_name;
            $add( $cb . $s . '/', 'weekly', '0.8' );

            $brands = wp_get_post_terms( $service->ID, 'brand' );
            if ( ! empty( $brands ) && ! is_wp_error( $brands ) ) {
                foreach ( $brands as $brand ) {
                    $add( $cb . $s . '/' . $brand->slug . '/', 'weekly', '0.7' );
                }
            }
        }

        set_transient( $cache_key, $urls, HOUR_IN_SECONDS );
        return $urls;
    }

    /**
     * Backward compatible — v1.2.x mein yeh function tha
     * Purana code agar is function pe depend karta ho to kaam karega
     */
    public function get_all_city_urls() {
        $all = array();
        foreach ( $this->get_published_cities() as $city ) {
            $all = array_merge( $all, $this->get_city_urls( $city ) );
        }
        return $all;
    }

    // =========================================================
    // RANK MATH INTEGRATION
    // =========================================================

    /**
     * Rank Math sitemap_index.xml mein inject karo
     * Har city ka alag <sitemap> entry — auto
     */
    public function inject_into_index( $output ) {
        $cities = $this->get_published_cities();
        if ( empty( $cities ) ) return $output;

        $entry = '';
        foreach ( $cities as $city ) {
            $loc     = home_url( '/ez-city-' . $city->post_name . '-sitemap.xml' );
            $lastmod = get_post_modified_time( 'Y-m-d\TH:i:s+00:00', true, $city->ID );
            if ( ! $lastmod ) $lastmod = date( 'Y-m-d\TH:i:s+00:00' );

            $entry .= "\t<sitemap>\n";
            $entry .= "\t\t<loc>"     . esc_url(  $loc     ) . "</loc>\n";
            $entry .= "\t\t<lastmod>" . esc_html( $lastmod ) . "</lastmod>\n";
            $entry .= "\t</sitemap>\n";
        }

        return is_string( $output ) ? $output . $entry : $output;
    }

    // =========================================================
    // REWRITE
    // =========================================================

    public function add_sitemap_rewrite() {
        // Index file ke liye rule
        add_rewrite_rule( '^ez-cities-sitemap\.xml$', 'index.php?ez_city_sitemap=1', 'top' );
        // Per-city rule class-rewrite-rules.php mein hai:
        // ^ez-city-([^/]+)-sitemap\.xml$ -> index.php?ez_city_slug=$matches[1]
    }

    // =========================================================
    // CACHE
    // =========================================================

    /**
     * Saare sitemap caches clear karo
     * Auto-triggered: city/service/brand save hone pe
     */
    public function clear_cache() {
        // Purane v1.2.x keys
        delete_transient( 'ezonecare_sitemap_urls_v3' );
        delete_transient( 'ezonecare_sitemap_urls_v4' );

        // Har city ka per-city transient
        $cities = $this->get_published_cities();
        foreach ( $cities as $city ) {
            delete_transient( self::CITY_CACHE_PREFIX . $city->post_name . '_' . self::CITY_CACHE_VERSION );
        }

        // Cities static cache bhi reset karo
        $this->cities_cache  = null;
        $this->services_cache = null;
    }

    // =========================================================
    // DB HELPERS — static cached
    // =========================================================

    private $cities_cache   = null;
    private $services_cache = null;

    private function get_published_cities() {
        if ( null !== $this->cities_cache ) return $this->cities_cache;
        $this->cities_cache = get_posts( array(
            'post_type'      => 'city',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'orderby'        => 'title',
            'order'          => 'ASC',
            'no_found_rows'  => true,
        ) );
        return $this->cities_cache;
    }

    private function get_published_services() {
        if ( null !== $this->services_cache ) return $this->services_cache;
        $this->services_cache = get_posts( array(
            'post_type'      => 'service',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
            'no_found_rows'  => true,
        ) );
        return $this->services_cache;
    }

    private function find_city_by_slug( $slug ) {
        global $wpdb;
        $id = $wpdb->get_var( $wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
             WHERE post_name = %s AND post_type = 'city' AND post_status = 'publish'
             LIMIT 1",
            $slug
        ) );
        return $id ? get_post( $id ) : false;
    }
}
