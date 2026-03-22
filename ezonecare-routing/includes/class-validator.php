<?php
/**
 * Validator Class - PRODUCTION HARDENED v2.2
 * 
 * Features:
 * - HARD BLOCK for slug conflicts (auto-generates unique slugs)
 * - Persistent object cache support (Redis/Memcached)
 * - Fallback to static cache
 * - Comprehensive conflict detection
 *
 * @package EzonecareRouting
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ezonecare_Validator {

    /**
     * Single instance
     */
    private static $instance = null;

    /**
     * Cache group for object cache
     */
    private $cache_group = 'ezonecare_routing';

    /**
     * Cache expiry time (1 hour)
     */
    private $cache_expiry = 3600;

    /**
     * Get instance
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
        // HARD BLOCK enforcement hooks
        add_action('save_post_service', array($this, 'enforce_service_slug_uniqueness'), 10, 2);
        add_action('save_post_city', array($this, 'enforce_city_slug_uniqueness'), 10, 2);
        add_action('create_brand', array($this, 'enforce_brand_slug_uniqueness'), 10, 2);
        add_action('edited_brand', array($this, 'enforce_brand_slug_uniqueness'), 10, 2);
        
        // Admin notices
        add_action('admin_notices', array($this, 'display_slug_enforcement_notices'));
        
        // Cache invalidation
        add_action('save_post_service', array($this, 'clear_service_cache'), 20);
        add_action('save_post_city', array($this, 'clear_city_cache'), 20);
        add_action('edited_brand', array($this, 'clear_brand_cache'), 20, 2);
        add_action('create_brand', array($this, 'clear_brand_cache'), 20, 2);
    }

    /**
     * Validate service with OBJECT CACHE support
     */
    public function validate_service($slug) {
        if (empty($slug)) {
            return false;
        }

        $cache_key = 'service_' . $slug;

        // Try object cache (persistent across requests if Redis/Memcached available)
        $service = wp_cache_get($cache_key, $this->cache_group);
        
        if (false !== $service) {
            return $service; // Cache HIT
        }

        // Cache MISS - query database
        $args = array(
            'name'           => sanitize_title($slug),
            'post_type'      => 'service',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'no_found_rows'  => true,
            'cache_results'  => true,
        );

        $query = new WP_Query($args);
        $service = $query->have_posts() ? $query->posts[0] : false;

        // Store in object cache (1 hour)
        wp_cache_set($cache_key, $service, $this->cache_group, $this->cache_expiry);

        return $service;
    }

    /**
     * Validate brand with OBJECT CACHE support
     */
    public function validate_brand($slug) {
        if (empty($slug)) {
            return false;
        }

        $cache_key = 'brand_' . $slug;
        $brand = wp_cache_get($cache_key, $this->cache_group);
        
        if (false !== $brand) {
            return $brand;
        }

        $term = get_term_by('slug', sanitize_title($slug), 'brand');
        $brand = ($term && !is_wp_error($term)) ? $term : false;

        wp_cache_set($cache_key, $brand, $this->cache_group, $this->cache_expiry);

        return $brand;
    }

    /**
     * Validate city with OBJECT CACHE support (FUTURE)
     */
    public function validate_city($slug) {
        if (empty($slug) || !post_type_exists('city')) {
            return false;
        }

        $cache_key = 'city_' . $slug;
        $city = wp_cache_get($cache_key, $this->cache_group);
        
        if (false !== $city) {
            return $city;
        }

        $args = array(
            'name'           => sanitize_title($slug),
            'post_type'      => 'city',
            'post_status'    => 'publish',
            'posts_per_page' => 1,
            'no_found_rows'  => true,
        );

        $query = new WP_Query($args);
        $city = $query->have_posts() ? $query->posts[0] : false;

        wp_cache_set($cache_key, $city, $this->cache_group, $this->cache_expiry);

        return $city;
    }

    /**
     * Check if brand is attached to service
     */
    public function is_brand_attached_to_service($brand_id, $service_id) {
        if (empty($brand_id) || empty($service_id)) {
            return false;
        }

        $cache_key = 'brand_service_' . $brand_id . '_' . $service_id;
        $is_attached = wp_cache_get($cache_key, $this->cache_group);
        
        if (false !== $is_attached) {
            return (bool) $is_attached;
        }

        $service_brands = wp_get_post_terms($service_id, 'brand', array('fields' => 'ids'));

        if (is_wp_error($service_brands)) {
            return false;
        }

        $is_attached = in_array($brand_id, $service_brands, true);
        wp_cache_set($cache_key, $is_attached, $this->cache_group, 900);

        return $is_attached;
    }

    /**
     * HARD ENFORCEMENT: Service slug uniqueness
     */
    public function enforce_service_slug_uniqueness($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;
        if (!in_array($post->post_status, array('publish', 'draft', 'pending', 'future'))) return;

        $slug = empty($post->post_name) ? sanitize_title($post->post_title) : $post->post_name;
        $conflicts = $this->check_slug_conflicts($slug, 'service', $post_id);

        if (!empty($conflicts)) {
            $new_slug = $this->generate_unique_slug($slug, 'service', $post_id);
            
            remove_action('save_post_service', array($this, 'enforce_service_slug_uniqueness'), 10);
            wp_update_post(array('ID' => $post_id, 'post_name' => $new_slug));
            add_action('save_post_service', array($this, 'enforce_service_slug_uniqueness'), 10, 2);
            
            set_transient('ezonecare_slug_enforced_' . $post_id, array(
                'original' => $slug,
                'new' => $new_slug,
                'conflicts' => $conflicts,
            ), 60);
        }
    }

    /**
     * HARD ENFORCEMENT: City slug uniqueness (FUTURE)
     */
    public function enforce_city_slug_uniqueness($post_id, $post) {
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (wp_is_post_revision($post_id)) return;
        if (!in_array($post->post_status, array('publish', 'draft', 'pending', 'future'))) return;

        $slug = empty($post->post_name) ? sanitize_title($post->post_title) : $post->post_name;
        $conflicts = $this->check_slug_conflicts($slug, 'city', $post_id);

        if (!empty($conflicts)) {
            $new_slug = $this->generate_unique_slug($slug, 'city', $post_id);
            
            remove_action('save_post_city', array($this, 'enforce_city_slug_uniqueness'), 10);
            wp_update_post(array('ID' => $post_id, 'post_name' => $new_slug));
            add_action('save_post_city', array($this, 'enforce_city_slug_uniqueness'), 10, 2);
            
            set_transient('ezonecare_slug_enforced_' . $post_id, array(
                'original' => $slug,
                'new' => $new_slug,
                'conflicts' => $conflicts,
            ), 60);
        }
    }

    /**
     * HARD ENFORCEMENT: Brand slug uniqueness
     */
    public function enforce_brand_slug_uniqueness($term_id, $tt_id = null) {
        $term = get_term($term_id, 'brand');
        if (!$term || is_wp_error($term)) return;

        $conflicts = $this->check_slug_conflicts($term->slug, 'brand', $term_id);

        if (!empty($conflicts)) {
            $new_slug = $this->generate_unique_slug($term->slug, 'brand', $term_id);
            
            remove_action('edited_brand', array($this, 'enforce_brand_slug_uniqueness'), 10);
            remove_action('create_brand', array($this, 'enforce_brand_slug_uniqueness'), 10);
            wp_update_term($term_id, 'brand', array('slug' => $new_slug));
            add_action('edited_brand', array($this, 'enforce_brand_slug_uniqueness'), 10, 2);
            add_action('create_brand', array($this, 'enforce_brand_slug_uniqueness'), 10, 2);
            
            set_transient('ezonecare_slug_enforced_term_' . $term_id, array(
                'original' => $term->slug,
                'new' => $new_slug,
                'conflicts' => $conflicts,
            ), 60);
        }
    }

    /**
     * Generate unique slug
     */
    private function generate_unique_slug($slug, $type, $exclude_id = 0) {
        $base_slug = $slug;
        $counter = 2;
        
        while ($counter <= 100) {
            $test_slug = $base_slug . '-' . $counter;
            if (empty($this->check_slug_conflicts($test_slug, $type, $exclude_id))) {
                return $test_slug;
            }
            $counter++;
        }
        
        return $base_slug . '-' . time();
    }

    /**
     * Check slug conflicts
     */
    public function check_slug_conflicts($slug, $exclude_type = '', $exclude_id = 0) {
        if (empty($slug)) return array();

        $conflicts = array();
        $slug = sanitize_title($slug);

        if ($exclude_type !== 'service') {
            $service = $this->validate_service($slug);
            if ($service && $service->ID != $exclude_id) {
                $conflicts[] = sprintf('Service: "%s" (ID: %d)', $service->post_title, $service->ID);
            }
        }

        if ($exclude_type !== 'brand') {
            $brand = $this->validate_brand($slug);
            if ($brand && $brand->term_id != $exclude_id) {
                $conflicts[] = sprintf('Brand: "%s" (ID: %d)', $brand->name, $brand->term_id);
            }
        }

        if (post_type_exists('city') && $exclude_type !== 'city') {
            $city = $this->validate_city($slug);
            if ($city && $city->ID != $exclude_id) {
                $conflicts[] = sprintf('City: "%s" (ID: %d)', $city->post_title, $city->ID);
            }
        }

        return $conflicts;
    }

    /**
     * Display enforcement notices
     */
    public function display_slug_enforcement_notices() {
        $screen = get_current_screen();
        if (!$screen) return;

        // Post enforcement notice
        if (in_array($screen->post_type, array('service', 'city'))) {
            global $post;
            if (!$post) return;

            $enforced = get_transient('ezonecare_slug_enforced_' . $post->ID);
            if (!empty($enforced)) {
                ?>
                <div class="notice notice-warning is-dismissible">
                    <p><strong>🔒 SLUG CONFLICT PREVENTED</strong></p>
                    <p>Original slug "<code><?php echo esc_html($enforced['original']); ?></code>" conflicted with:</p>
                    <ul>
                        <?php foreach ($enforced['conflicts'] as $conflict): ?>
                            <li><?php echo esc_html($conflict); ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <p><strong>Auto-changed to:</strong> "<code><?php echo esc_html($enforced['new']); ?></code>"</p>
                </div>
                <?php
                delete_transient('ezonecare_slug_enforced_' . $post->ID);
            }
        }

        // Term enforcement notice
        if ($screen->taxonomy === 'brand') {
            $term_id = isset($_GET['tag_ID']) ? intval($_GET['tag_ID']) : 0;
            if ($term_id) {
                $enforced = get_transient('ezonecare_slug_enforced_term_' . $term_id);
                if (!empty($enforced)) {
                    ?>
                    <div class="notice notice-warning is-dismissible">
                        <p><strong>🔒 SLUG CONFLICT PREVENTED</strong></p>
                        <p>Original "<code><?php echo esc_html($enforced['original']); ?></code>" conflicted with:</p>
                        <ul>
                            <?php foreach ($enforced['conflicts'] as $conflict): ?>
                                <li><?php echo esc_html($conflict); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <p><strong>Auto-changed to:</strong> "<code><?php echo esc_html($enforced['new']); ?></code>"</p>
                    </div>
                    <?php
                    delete_transient('ezonecare_slug_enforced_term_' . $term_id);
                }
            }
        }
    }

    /**
     * Cache invalidation methods
     */
    public function clear_service_cache($post_id) {
        $post = get_post($post_id);
        if ($post) wp_cache_delete('service_' . $post->post_name, $this->cache_group);
    }

    public function clear_city_cache($post_id) {
        $post = get_post($post_id);
        if ($post) wp_cache_delete('city_' . $post->post_name, $this->cache_group);
    }

    public function clear_brand_cache($term_id, $tt_id = null) {
        $term = get_term($term_id, 'brand');
        if ($term && !is_wp_error($term)) {
            wp_cache_delete('brand_' . $term->slug, $this->cache_group);
        }
    }

    /**
     * Helper methods
     */
    public function get_service_brands($service_id) {
        if (empty($service_id)) return array();
        $brands = wp_get_post_terms($service_id, 'brand');
        return is_wp_error($brands) ? array() : $brands;
    }

    public function get_brand_services($brand_id) {
        if (empty($brand_id)) return array();
        $query = new WP_Query(array(
            'post_type' => 'service',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'tax_query' => array(array(
                'taxonomy' => 'brand',
                'field' => 'term_id',
                'terms' => $brand_id,
            )),
            'no_found_rows' => true,
        ));
        return $query->posts;
    }
}
