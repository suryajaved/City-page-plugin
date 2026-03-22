<?php
/**
 * Query Handler v3.27
 * Complete rewrite — proper URL resolution order
 *
 * @package EzonecareRouting
 */

if (!defined('ABSPATH')) exit;

class Ezonecare_Query_Handler {

    private static $instance = null;
    private $validated_data  = null;
    private $resolved_route  = '';
    private $cache_group     = 'ezonecare_routing';
    private $cache_expiry    = 3600;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_filter('request',            array($this, 'handle_request'), 10);
        add_filter('redirect_canonical', array($this, 'disable_canonical_redirect'), 10, 2);
        add_action('template_redirect',  array($this, 'load_custom_template'), 1);
        add_action('save_post_city',     array($this, 'clear_city_cache'));
        add_action('save_post_service',  array($this, 'clear_service_cache'));
    }

    /**
     * Stop city pages doing canonical redirect to /city/ranchi/
     * Also stop service CPT pages redirecting to homepage
     */
    public function disable_canonical_redirect($redirect_url, $requested_url) {
        // Our custom city routes
        if (get_query_var('custom_city_route') || get_query_var('custom_city_id')) {
            return false;
        }
        // Service CPT native pages
        if (get_query_var('post_type') === 'service' && get_query_var('name')) {
            return false;
        }
        return $redirect_url;
    }

    /**
     * Main request handler — called via 'request' filter
     * WordPress passes $query_vars after rewrite rules matched
     */
    public function handle_request($query_vars) {

        // ── Let WordPress-native prefixed URLs pass through ────────
        $request_uri = isset($_SERVER['REQUEST_URI'])
            ? trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/')
            : '';

        // Pass: /service/, /city/, /wp-admin/, /wp-content/, /feed/, /sitemap/
        if (preg_match('#^(service|city|wp-admin|wp-content|wp-includes|wp-json|feed|sitemap|uploads|themes|plugins)#', $request_uri)) {
            return $query_vars;
        }

        // Pass: WordPress REST API
        if (preg_match('#^wp-json#', $request_uri)) {
            return $query_vars;
        }

        // Only proceed if our custom rewrite vars are set
        if (empty($query_vars['custom_city'])) {
            return $query_vars;
        }

        $city_slug    = sanitize_title($query_vars['custom_city']);
        $service_slug = !empty($query_vars['custom_service']) ? sanitize_title($query_vars['custom_service']) : '';
        $brand_slug   = !empty($query_vars['custom_brand'])   ? sanitize_title($query_vars['custom_brand'])   : '';

        // ── STEP 1: ALWAYS check if this is a native WP URL first ──
        // blog post, page, service, or any CPT — let WP handle it
        $native = $this->find_native_post($city_slug, $service_slug, $brand_slug);
        if ($native) {
            // City type — routing system handle karega STEP 3 mein
            // STEP 1 se return mat karo — neeche jaane do
            if ($native['type'] === 'city') {
                // Fall through to STEP 3
            } else {
                // Non-city native post — WP handle karega
                unset($query_vars['custom_city']);
                unset($query_vars['custom_service']);
                unset($query_vars['custom_brand']);

                if ($native['type'] === 'post') {
                    $query_vars['p']    = $native['id'];
                    $query_vars['name'] = $city_slug;

                } elseif ($native['type'] === 'page') {
                    $query_vars['page_id']  = $native['id'];
                    $query_vars['pagename'] = $city_slug;

                } elseif ($native['type'] === 'service') {
                    $query_vars['name']      = $city_slug;
                    $query_vars['post_type'] = 'service';
                    unset($query_vars['p']);
                }

                return $query_vars;
            }
        }

        // ── STEP 2: Reserved slugs — pass through ─────────────────
        $reserved = array('wp-admin', 'wp-content', 'wp-includes', 'feed',
                          'sitemap', 'blog', 'shop', 'cart', 'checkout',
                          'page', 'author', 'search', 'tag', 'category');
        if (in_array($city_slug, $reserved)) {
            unset($query_vars['custom_city']);
            return $query_vars;
        }

        // ── STEP 3: Single segment OR contact page ────────────────
        if (empty($service_slug)) {
            $city = $this->find_city($city_slug);
            if ($city) {

                // ── CONTACT PAGE: /ranchi/contact/ ───────────────
                if (!empty($query_vars['custom_page']) && $query_vars['custom_page'] === 'contact') {
                    $this->resolved_route = 'city-contact';
                    $this->validated_data = array(
                        'route' => 'city-contact',
                        'city'  => $city,
                    );
                    $query_vars['custom_city_route'] = 1;
                    unset($query_vars['custom_city']);
                    return $query_vars;
                }

                // ── ABOUT PAGE: /ranchi/about/ ────────────────────
                if (!empty($query_vars['custom_page']) && $query_vars['custom_page'] === 'about') {
                    $this->resolved_route = 'city-about';
                    $this->validated_data = array(
                        'route' => 'city-about',
                        'city'  => $city,
                    );
                    $query_vars['custom_city_route'] = 1;
                    unset($query_vars['custom_city']);
                    return $query_vars;
                }

                // ── SERVICES PAGE: /ranchi/services/ ──────────────
                if (!empty($query_vars['custom_page']) && $query_vars['custom_page'] === 'services') {
                    $this->resolved_route = 'city-services';
                    $this->validated_data = array(
                        'route' => 'city-services',
                        'city'  => $city,
                    );
                    $query_vars['custom_city_route'] = 1;
                    unset($query_vars['custom_city']);
                    return $query_vars;
                }

                // ── CITY LANDING: /ranchi/ ────────────────────────
                $this->resolved_route = 'city';
                $this->validated_data = array('route' => 'city', 'city' => $city);
                $query_vars['custom_city_route'] = 1;
                $query_vars['custom_city_id']    = $city->ID;
                unset($query_vars['custom_city']);
                unset($query_vars['name']);
                unset($query_vars['post_type']);
                return $query_vars;
            }
            // Not a city, not a native post — pass through (WP will 404)
            unset($query_vars['custom_city']);
            return $query_vars;
        }

        // ── STEP 4: Two segments — city/service ────────────────────
        $city = $this->find_city($city_slug);
        if ($city) {

            // ── SERVICE/BRAND: /ranchi/laptop-repair-services/ ────
            $service = $this->find_service($service_slug);
            if (!$service) {
                $query_vars['error'] = '404';
                return $query_vars;
            }

            if (!empty($brand_slug)) {
                $brand = $this->find_service($brand_slug); // brand is also a service post
                if ($brand) {
                    $this->resolved_route = 'city-service-brand';
                    $this->validated_data = array(
                        'route'   => 'city-service-brand',
                        'city'    => $city,
                        'service' => $service,
                        'brand'   => $brand,
                    );
                } else {
                    $query_vars['error'] = '404';
                    return $query_vars;
                }
            } else {
                $this->resolved_route = 'city-service';
                $this->validated_data = array(
                    'route'   => 'city-service',
                    'city'    => $city,
                    'service' => $service,
                );
            }
            $query_vars['custom_city_route'] = 1;
            return $query_vars;
        }

        // ── STEP 5: service/brand (no city) ────────────────────────
        $service = $this->find_service($city_slug);
        if ($service && !empty($service_slug)) {
            $brand = $this->find_brand($service_slug);
            if ($brand) {
                $this->resolved_route = 'service-brand';
                $this->validated_data = array(
                    'route'   => 'service-brand',
                    'service' => $service,
                    'brand'   => $brand,
                );
                $query_vars['custom_city_route'] = 1;
                return $query_vars;
            }
        }

        // Fallback — nothing matched, clean up and let WP handle
        unset($query_vars['custom_city']);
        unset($query_vars['custom_service']);
        unset($query_vars['custom_brand']);
        return $query_vars;
    }

    /**
     * Load correct template at template_redirect
     */
    public function load_custom_template() {
        if (empty($this->resolved_route) || empty($this->validated_data)) return;

        $map = array(
            'city'               => 'single-city.php',
            'city-service'       => 'single-city-service.php',
            'city-service-brand' => 'single-city-service-brand.php',
            'service-brand'      => 'single-service-brand.php',
        );

        if (!isset($map[$this->resolved_route])) return;

        // Template loading is handled by ezonecare-city-pages plugin
        // (class-template-loader.php via template_redirect hook)
        // Plugin 1 sirf routing decide karta hai — template load nahi karta
        // Agar city-pages plugin active nahi hai to 404 aayega — ye expected behaviour hai
        do_action('ezonecare_route_resolved', $this->resolved_route, $this->validated_data);
    }

    // ── NATIVE POST CHECK ────────────────────────────────────────
    /**
     * Check if URL belongs to a native WordPress post/page/CPT
     * Only check single-segment URLs — multi-segment are city/service combos
     */
    private function find_native_post($city_slug, $service_slug, $brand_slug) {
        // Only check single-segment URLs
        if (!empty($service_slug) || !empty($brand_slug)) {
            return false;
        }

        global $wpdb;
        $row = $wpdb->get_row($wpdb->prepare(
            "SELECT ID, post_type FROM {$wpdb->posts}
             WHERE post_name = %s
             AND post_status = 'publish'
             AND post_type IN ('post', 'page', 'attachment', 'service', 'city')
             LIMIT 1",
            $city_slug
        ));
        if (!$row) return false;
        return array(
            'id'   => intval($row->ID),
            'type' => $row->post_type,
        );
    }

    // ── PRIVATE DB LOOKUPS ───────────────────────────────────────

    private function find_city($slug) {
        if (empty($slug)) return false;
        $cached = wp_cache_get('ez_city_' . $slug, $this->cache_group);
        if ($cached !== false) return $cached;

        global $wpdb;
        $id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
             WHERE post_name = %s AND post_type = 'city' AND post_status = 'publish' LIMIT 1",
            $slug
        ));
        $result = $id ? get_post($id) : false;
        wp_cache_set('ez_city_' . $slug, $result, $this->cache_group, $this->cache_expiry);
        return $result;
    }

    private function find_service($slug) {
        if (empty($slug)) return false;
        $cached = wp_cache_get('ez_service_' . $slug, $this->cache_group);
        if ($cached !== false) return $cached;

        global $wpdb;
        $id = $wpdb->get_var($wpdb->prepare(
            "SELECT ID FROM {$wpdb->posts}
             WHERE post_name = %s AND post_type = 'service' AND post_status = 'publish' LIMIT 1",
            $slug
        ));
        $result = $id ? get_post($id) : false;
        wp_cache_set('ez_service_' . $slug, $result, $this->cache_group, $this->cache_expiry);
        return $result;
    }

    private function find_brand($slug) {
        if (empty($slug)) return false;
        $cached = wp_cache_get('ez_brand_' . $slug, $this->cache_group);
        if ($cached !== false) return $cached;

        $term   = get_term_by('slug', $slug, 'brand');
        $result = ($term && !is_wp_error($term)) ? $term : false;
        wp_cache_set('ez_brand_' . $slug, $result, $this->cache_group, $this->cache_expiry);
        return $result;
    }

    // ── PUBLIC GETTERS ───────────────────────────────────────────

    public function get_resolved_route()  { return $this->resolved_route; }
    public function get_validated_data()  { return $this->validated_data; }
    public function has_validated_data()  { return !empty($this->validated_data); }
    public function get_city()    { return isset($this->validated_data['city'])    ? $this->validated_data['city']    : null; }
    public function get_service() { return isset($this->validated_data['service']) ? $this->validated_data['service'] : null; }
    public function get_brand()   { return isset($this->validated_data['brand'])   ? $this->validated_data['brand']   : null; }

    public function clear_city_cache($post_id) {
        $p = get_post($post_id);
        if ($p) wp_cache_delete('ez_city_' . $p->post_name, $this->cache_group);
    }
    public function clear_service_cache($post_id) {
        $p = get_post($post_id);
        if ($p) wp_cache_delete('ez_service_' . $p->post_name, $this->cache_group);
    }
}
