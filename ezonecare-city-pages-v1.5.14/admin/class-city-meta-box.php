<?php
/**
 * @version 1.5.11
 * Custom Meta Box — City Service & Brand Assignment
 * Shows on City CPT edit page
 * 
 * UI Flow:
 * 1. Left box: Available pillar services → Click to add to right box
 * 2. Right box: Active services → Click service to see brand sub-box
 * 3. Brand sub-box: Select which brands active for that service in this city
 */

if (!defined('ABSPATH')) exit;

class Ezonecare_City_Meta_Box {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('add_meta_boxes',  array($this, 'register_meta_box'));
        add_action('save_post_city',  array($this, 'save_meta_box'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    /**
     * Register meta box on City CPT
     */
    public function register_meta_box() {
        add_meta_box(
            'ezonecare_city_services',
            '🛠️ Service & Brand Assignment',
            array($this, 'render_meta_box'),
            'city',
            'normal',
            'high'
        );
    }

    /**
     * Enqueue admin CSS/JS
     */
    public function enqueue_scripts($hook) {
        global $post;
        if (!in_array($hook, array('post.php', 'post-new.php'))) return;
        if (!$post || $post->post_type !== 'city') return;

        wp_enqueue_style(
            'ezonecare-city-meta',
            EZ_CITY_PLUGIN_URL . 'admin/city-meta-box.css',
            array(),
            EZ_CITY_VERSION
        );
        wp_enqueue_script(
            'ezonecare-city-meta',
            EZ_CITY_PLUGIN_URL . 'admin/city-meta-box.js',
            array('jquery'),
            EZ_CITY_VERSION,
            true
        );
    }

    /**
     * Get all pillar services (published only, no parent brand services)
     * Pillar = service post that has "service_brand_posts" field defined
     * Simple rule: service post where post_name does NOT contain brand keywords
     */
    private function get_pillar_services() {
        // Get ALL published service posts
        // Admin manually selects which are pillar vs brand
        return get_posts(array(
            'post_type'      => 'service',
            'post_status'    => 'publish',
            'posts_per_page' => 100,
            'orderby'        => 'title',
            'order'          => 'ASC',
        ));
    }

    /**
     * Get brand service posts linked to a specific pillar service
     */
    private function get_brands_for_service($service_id) {
        $brands = get_field('service_brand_posts', $service_id);
        if (empty($brands) || !is_array($brands)) return array();
        return $brands;
    }

    /**
     * Render the meta box HTML
     */
    public function render_meta_box($post) {
        wp_nonce_field('ezonecare_city_services_nonce', 'ezonecare_city_services_nonce');

        // Get saved data
        $saved = get_post_meta($post->ID, '_ezonecare_city_service_brands', true);
        $saved = $saved ? json_decode($saved, true) : array();
        // $saved = [ service_id => [brand_id1, brand_id2], ... ]

        $active_service_ids = array_keys($saved);

        // Get all pillar services
        $all_services = $this->get_pillar_services();

        // Prepare data for JS
        $services_data = array();
        foreach ($all_services as $svc) {
            $brands = $this->get_brands_for_service($svc->ID);
            $brands_arr = array();
            foreach ($brands as $b) {
                $brands_arr[] = array(
                    'id'    => $b->ID,
                    'title' => $b->post_title,
                    'slug'  => $b->post_name,
                );
            }
            $services_data[] = array(
                'id'     => $svc->ID,
                'title'  => $svc->post_title,
                'slug'   => $svc->post_name,
                'brands' => $brands_arr,
            );
        }

        // Hidden input to store JSON data
        $saved_json = json_encode($saved);
        ?>

        <!-- Hidden field to save data -->
        <input type="hidden" 
               id="ez_service_brands_data" 
               name="ez_service_brands_data" 
               value="<?php echo esc_attr($saved_json); ?>">

        <!-- Pass data to JS -->
        <script>
            var EZ_SERVICES = <?php echo json_encode($services_data); ?>;
            var EZ_SAVED    = <?php echo $saved_json ?: '{}'; ?>;
        </script>

        <div class="ez-meta-wrap">

            <!-- TOP: Service Assignment -->
            <div class="ez-services-row">

                <!-- LEFT: Available Services -->
                <div class="ez-box">
                    <div class="ez-box-head">📋 Available Services</div>
                    <div class="ez-box-sub">Click to add →</div>
                    <div class="ez-list" id="ez-available-services">
                        <?php foreach ($all_services as $svc): 
                            $is_active = in_array($svc->ID, $active_service_ids);
                        ?>
                        <div class="ez-item <?php echo $is_active ? 'ez-item-used' : ''; ?>"
                             data-id="<?php echo $svc->ID; ?>"
                             data-title="<?php echo esc_attr($svc->post_title); ?>">
                            <?php echo esc_html($svc->post_title); ?>
                            <?php if ($is_active): ?><span class="ez-tag">Added</span><?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="ez-arrow">→</div>

                <!-- RIGHT: Active Services for this City -->
                <div class="ez-box">
                    <div class="ez-box-head">✅ Active Services — This City</div>
                    <div class="ez-box-sub">Click service to manage brands ↓</div>
                    <div class="ez-list" id="ez-active-services">
                        <?php foreach ($all_services as $svc):
                            if (!in_array($svc->ID, $active_service_ids)) continue;
                        ?>
                        <div class="ez-item ez-item-active"
                             data-id="<?php echo $svc->ID; ?>"
                             data-title="<?php echo esc_attr($svc->post_title); ?>">
                            <span class="ez-item-title"><?php echo esc_html($svc->post_title); ?></span>
                            <span class="ez-remove" title="Remove">✕</span>
                        </div>
                        <?php endforeach; ?>
                        <div class="ez-empty" id="ez-services-empty" 
                             style="<?php echo !empty($active_service_ids) ? 'display:none' : ''; ?>">
                            No services added yet
                        </div>
                    </div>
                </div>

            </div>

            <!-- BOTTOM: Brand Assignment (shows when service is clicked) -->
            <div class="ez-brands-panel" id="ez-brands-panel" style="display:none;">
                <div class="ez-brands-head">
                    🏷️ Brand Services for: <strong id="ez-selected-service-name"></strong>
                </div>
                <div class="ez-services-row">

                    <!-- LEFT: Available Brands -->
                    <div class="ez-box">
                        <div class="ez-box-head">Available Brand Services</div>
                        <div class="ez-box-sub">Click to add →</div>
                        <div class="ez-list" id="ez-available-brands">
                            <div class="ez-empty">Select a service above to see brands</div>
                        </div>
                    </div>

                    <div class="ez-arrow">→</div>

                    <!-- RIGHT: Active Brands for selected service -->
                    <div class="ez-box">
                        <div class="ez-box-head">✅ Active Brands — This Service</div>
                        <div class="ez-box-sub">Click ✕ to remove</div>
                        <div class="ez-list" id="ez-active-brands">
                            <div class="ez-empty" id="ez-brands-empty">No brands selected</div>
                        </div>
                    </div>

                </div>
            </div>

        </div><!-- .ez-meta-wrap -->

        <?php
    }

    /**
     * Save meta box data
     */
    public function save_meta_box($post_id, $post) {
        // Verify nonce
        if (!isset($_POST['ezonecare_city_services_nonce'])) return;
        if (!wp_verify_nonce($_POST['ezonecare_city_services_nonce'], 'ezonecare_city_services_nonce')) return;

        // Check permissions
        if (!current_user_can('edit_post', $post_id)) return;

        // Save JSON data
        if (isset($_POST['ez_service_brands_data'])) {
            $data = stripslashes($_POST['ez_service_brands_data']);
            $decoded = json_decode($data, true);
            if (is_array($decoded)) {
                update_post_meta($post_id, '_ezonecare_city_service_brands', wp_slash($data));
            }
        }
    }

    /**
     * Get saved service-brand data for a city
     * Returns: [ service_id => [brand_id1, brand_id2], ... ]
     */
    public static function get_city_service_brands($city_id) {
        $data = get_post_meta($city_id, '_ezonecare_city_service_brands', true);
        if (empty($data)) return array();
        $decoded = json_decode($data, true);
        return is_array($decoded) ? $decoded : array();
    }

    /**
     * Get active service IDs for a city
     */
    public static function get_active_service_ids($city_id) {
        $data = self::get_city_service_brands($city_id);
        return array_map('intval', array_keys($data));
    }

    /**
     * Get active brand IDs for a specific service in a city
     */
    public static function get_active_brand_ids($city_id, $service_id) {
        $data = self::get_city_service_brands($city_id);
        $key  = strval($service_id);
        return isset($data[$key]) ? array_map('intval', $data[$key]) : array();
    }
}
