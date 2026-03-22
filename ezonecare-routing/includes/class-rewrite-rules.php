<?php
/**
 * Rewrite Rules v3.6
 * NOTE: /service/ and /city/ URLs handled by WordPress natively
 *
 * @package EzonecareRouting
 */

if (!defined('ABSPATH')) exit;

class Ezonecare_Rewrite_Rules {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('init',         array($this, 'add_rewrite_rules'), 1);
        add_filter('query_vars',   array($this, 'add_query_vars'));
    }

    public function add_rewrite_rules() {
        // Per-city sitemap: /ez-city-ranchi-sitemap.xml
        // IMPORTANT: 'top' priority — city routing se pehle match ho
        add_rewrite_rule(
            '^ez-city-([^/]+)-sitemap\.xml$',
            'index.php?ez_city_slug=$matches[1]',
            'top'
        );

        // Common WP slugs to exclude from our routing
        $exclude = 'service|city|wp-|feed|sitemap|blog|tag|category|author|search|page|sample-page|shop|cart|checkout|account|privacy-policy|terms';

        // 3 segments: /ranchi/laptop-repair-services/dell/
        add_rewrite_rule(
            '^(?!' . $exclude . ')([^/]+)/([^/]+)/([^/]+)/?$',
            'index.php?custom_city=$matches[1]&custom_service=$matches[2]&custom_brand=$matches[3]',
            'top'
        );

        // 2 segments — contact: /ranchi/contact/
        add_rewrite_rule(
            '^(?!' . $exclude . ')([^/]+)/contact/?$',
            'index.php?custom_city=$matches[1]&custom_page=contact',
            'top'
        );

        // 2 segments — about: /ranchi/about/
        add_rewrite_rule(
            '^(?!' . $exclude . ')([^/]+)/about/?$',
            'index.php?custom_city=$matches[1]&custom_page=about',
            'top'
        );

        // 2 segments — services: /ranchi/services/
        add_rewrite_rule(
            '^(?!' . $exclude . ')([^/]+)/services/?$',
            'index.php?custom_city=$matches[1]&custom_page=services',
            'top'
        );

        // 2 segments: /ranchi/laptop-repair-services/
        add_rewrite_rule(
            '^(?!' . $exclude . ')([^/]+)/([^/]+)/?$',
            'index.php?custom_city=$matches[1]&custom_service=$matches[2]',
            'top'
        );

        // 1 segment: /ranchi/
        add_rewrite_rule(
            '^(?!' . $exclude . ')([^/]+)/?$',
            'index.php?custom_city=$matches[1]',
            'top'
        );
    }

    public function add_query_vars($vars) {
        $vars[] = 'custom_city';
        $vars[] = 'custom_service';
        $vars[] = 'custom_brand';
        $vars[] = 'custom_city_route';
        $vars[] = 'custom_city_id';
        $vars[] = 'custom_page';
        $vars[] = 'ez_city_slug';      // Per-city sitemap: /ez-city-{slug}-sitemap.xml
        return $vars;
    }

    public static function flush_rules($hard = false) {
        self::get_instance()->add_rewrite_rules();
        flush_rewrite_rules($hard);
    }
}
