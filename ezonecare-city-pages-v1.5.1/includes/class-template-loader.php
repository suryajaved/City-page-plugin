<?php
/**
 * Template Loader
 * 
 * Loads appropriate templates for custom routes
 * Template hierarchy:
 * - single-service-brand.php (for service + brand)
 * - single-city-service-brand.php (future)
 * - single-city-service.php (future)
 * - single-city.php (future)
 *
 * @package EzonecareRouting
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ezonecare_Template_Loader {

    /**
     * Single instance
     *
     * @var Ezonecare_Template_Loader
     */
    private static $instance = null;

    /**
     * Template directory
     *
     * @var string
     */
    private $template_dir;

    /**
     * Get instance
     *
     * @return Ezonecare_Template_Loader
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->template_dir = EZ_CITY_PLUGIN_DIR . 'templates/';
        
        add_filter('template_include', array($this, 'load_custom_template'), 999);
        add_filter('body_class',       array($this, 'add_body_classes'));

        // v1.2.2 FIX: City pages pe sirf footer hide karo — header enable rakho
        add_action('wp', array($this, 'hide_wp_header_footer_on_city_pages'));
    }

    /**
     * City pages pe WordPress/Astra header aur footer hooks remove karo
     *
     * wp_head() se Astra apna header print karta hai via wp_body_open / astra hooks
     * wp_footer() se WP aur Astra footer print hota hai
     * Ye dono hamare custom header/footer ke saath duplicate hote hain
     *
     * v1.2.1 FIX: Double header + Double footer problem solve
     */
    public function hide_wp_header_footer_on_city_pages() {
        $query_handler = Ezonecare_Query_Handler::get_instance();
        if (!$query_handler->has_validated_data()) return;

        // Astra footer hooks remove karo (header enable hai, sirf footer hide)
        add_filter('astra_footer_enabled',        '__return_false', 999);
        add_filter('astra_above_footer_enabled',  '__return_false', 999);
        add_filter('astra_below_footer_enabled',  '__return_false', 999);

        // CSS fallback — sirf footer hide karo
        add_action('wp_head', array($this, 'inject_hide_header_footer_css'), 999);
    }

    /**
     * CSS fallback: Astra header + footer completely hide karo
     */
    public function inject_hide_header_footer_css() {
        echo '<style id="ez-hide-wp-chrome">' . "\n";
        echo '#colophon.site-footer, .site-below-footer-wrap, .ast-footer-copyright { display: none !important; }' . "\n";
        echo '</style>' . "\n";
    }

    /**
     * Load custom template based on route
     * 
     * @param string $template Default template path
     * @return string Modified template path
     */
    public function load_custom_template($template) {
        $query_handler = Ezonecare_Query_Handler::get_instance();
        $route = $query_handler->get_resolved_route();

        if (empty($route)) {
            return $template;
        }

        // Only load custom template if validation passed
        if (!$query_handler->has_validated_data()) {
            return $template;
        }

        $custom_template = '';

        switch ($route) {
            case 'service-brand':
                $custom_template = $this->locate_template('single-service-brand.php');
                break;

            case 'city':
                $custom_template = $this->locate_template('single-city.php');
                break;

            case 'city-contact':
                $custom_template = $this->locate_template('single-city-contact.php');
                break;

            case 'city-about':
                $custom_template = $this->locate_template('single-city-about.php');
                break;

            case 'city-services':
                $custom_template = $this->locate_template('single-city-services.php');
                break;

            case 'city-service':
                $custom_template = $this->locate_template('single-city-service.php');
                break;

            case 'city-service-brand':
                $custom_template = $this->locate_template('single-city-service-brand.php');
                break;
        }

        if (!empty($custom_template)) {
            return $custom_template;
        }

        return $template;
    }

    /**
     * Locate template file
     * 
     * Checks:
     * 1. Theme directory (child theme and parent theme)
     * 2. Plugin templates directory
     *
     * @param string $template_name Template filename
     * @return string Template path or empty string
     */
    private function locate_template($template_name) {
        // Check in theme directory first
        $theme_template = locate_template(array(
            'ezonecare/' . $template_name,
            $template_name,
        ));

        if (!empty($theme_template)) {
            return $theme_template;
        }

        // Check in plugin directory
        $plugin_template = $this->template_dir . $template_name;
        
        if (file_exists($plugin_template)) {
            return $plugin_template;
        }

        return '';
    }

    /**
     * Add custom body classes for routes
     * 
     * @param array $classes Existing body classes
     * @return array Modified body classes
     */
    public function add_body_classes($classes) {
        $query_handler = Ezonecare_Query_Handler::get_instance();
        $route = $query_handler->get_resolved_route();

        if (empty($route)) {
            return $classes;
        }

        if (!$query_handler->has_validated_data()) {
            return $classes;
        }

        // Add route-specific class
        $classes[] = 'ezonecare-route';
        $classes[] = 'ezonecare-route-' . sanitize_html_class($route);

        // Add service class
        $service = $query_handler->get_service();
        if ($service) {
            $classes[] = 'service-' . $service->post_name;
        }

        // Add brand class
        $brand = $query_handler->get_brand();
        if ($brand) {
            $classes[] = 'brand-' . $brand->slug;
        }

        // Add city class (future)
        $city = $query_handler->get_city();
        if ($city) {
            $classes[] = 'city-' . $city->post_name;
        }

        return $classes;
    }

    /**
     * Get template data for current route
     * 
     * Returns all relevant data for template rendering
     *
     * @return array Template data
     */
    public static function get_template_data() {
        $query_handler = Ezonecare_Query_Handler::get_instance();
        $route = $query_handler->get_resolved_route();

        $data = array(
            'route'   => $route,
            'service' => $query_handler->get_service(),
            'brand'   => $query_handler->get_brand(),
            'city'    => $query_handler->get_city(),
        );

        return $data;
    }

    /**
     * Helper function to get dynamic page title
     * 
     * @return string Page title
     */
    public static function get_dynamic_title() {
        $data = self::get_template_data();
        $title = '';

        switch ($data['route']) {
            case 'service-brand':
                if ($data['brand'] && $data['service']) {
                    // Format: "Brand Name Service Title"
                    $title = $data['brand']->name . ' ' . $data['service']->post_title;
                }
                break;

            case 'city-service':
                if ($data['city'] && $data['service']) {
                    $title = $data['service']->post_title . ' in ' . $data['city']->post_title;
                }
                break;

            case 'city-service-brand':
                if ($data['city'] && $data['brand'] && $data['service']) {
                    $title = $data['brand']->name . ' ' . $data['service']->post_title . ' in ' . $data['city']->post_title;
                }
                break;

            case 'city':
                if ($data['city']) {
                    $title = $data['city']->post_title;
                }
                break;
        }

        return $title;
    }

    /**
     * Helper function to get current URL
     * 
     * @return string Current URL
     */
    public static function get_current_url() {
        global $wp;
        return home_url($wp->request);
    }
}
