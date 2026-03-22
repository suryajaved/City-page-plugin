<?php
/**
 * City ACF Fields v3.5
 * Complete fields including photos, pincode, area, tagline
 * WhatsApp split into country code + number
 *
 * @package EzonecareRouting
 */

if (!defined('ABSPATH')) {
    exit;
}

class Ezonecare_City_ACF {

    private static $instance = null;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action('acf/init', array($this, 'register_city_fields'));
        // v1.4.7: Reset button render karo intro fields ke baad
        add_action('acf/render_field', array($this, 'render_reset_button'));
        // v1.4.7: Admin page pe nonce aur script load karo
        add_action('admin_enqueue_scripts', array($this, 'enqueue_intro_scripts'));
    }

    public function register_city_fields() {
        if (!function_exists('acf_add_local_field_group')) {
            return;
        }

        acf_add_local_field_group(array(
            'key'    => 'group_ezonecare_city',
            'title'  => 'City Service Center Details',
            'fields' => array(

                // ── SECTION: Basic Info ──────────────────────────
                array(
                    'key'     => 'field_city_tab_basic',
                    'label'   => 'Basic Information',
                    'type'    => 'tab',
                    'placement' => 'top',
                ),

                array(
                    'key'          => 'field_city_center_name',
                    'label'        => 'Service Center Name',
                    'name'         => 'city_center_name',
                    'type'         => 'text',
                    'instructions' => 'E.g. Multimedia Computer, Ezone Care, Faiz Computer',
                    'required'     => 1,
                    'placeholder'  => 'Enter service center name',
                ),

                array(
                    'key'          => 'field_city_tagline',
                    'label'        => 'Service Center Tagline',
                    'name'         => 'city_tagline',
                    'type'         => 'text',
                    'instructions' => 'Short tagline. E.g. Nagpur\'s Most Trusted Laptop Repair Center',
                    'required'     => 0,
                    'placeholder'  => 'Most Trusted Laptop Repair Center',
                ),

                array(
                    'key'          => 'field_city_area',
                    'label'        => 'Area / Locality',
                    'name'         => 'city_area',
                    'type'         => 'text',
                    'instructions' => 'Area name. E.g. Sitabuldi, Ranchi Road, MG Road',
                    'required'     => 0,
                    'placeholder'  => 'Sitabuldi',
                ),

                array(
                    'key'          => 'field_city_address',
                    'label'        => 'Full Address',
                    'name'         => 'city_address',
                    'type'         => 'textarea',
                    'instructions' => 'Complete address with street and area',
                    'required'     => 1,
                    'rows'         => 3,
                    'placeholder'  => 'Shop No. 5, ABC Market, Sitabuldi',
                ),

                array(
                    'key'          => 'field_city_pincode',
                    'label'        => 'Pincode',
                    'name'         => 'city_pincode',
                    'type'         => 'text',
                    'instructions' => '6 digit pincode. E.g. 440001',
                    'required'     => 1,
                    'placeholder'  => '440001',
                ),

                array(
                    'key'          => 'field_city_state',
                    'label'        => 'State',
                    'name'         => 'city_state',
                    'type'         => 'text',
                    'instructions' => 'E.g. Maharashtra, Jharkhand',
                    'required'     => 1,
                    'placeholder'  => 'Maharashtra',
                ),

                array(
                    'key'          => 'field_city_hours',
                    'label'        => 'Working Hours',
                    'name'         => 'city_hours',
                    'type'         => 'text',
                    'instructions' => 'E.g. Mon-Sat: 10 AM - 7 PM',
                    'required'     => 0,
                    'placeholder'  => 'Mon-Sat: 10 AM - 7 PM',
                ),

                // ── SECTION: Contact ─────────────────────────────
                array(
                    'key'       => 'field_city_tab_contact',
                    'label'     => 'Contact Details',
                    'type'      => 'tab',
                    'placement' => 'top',
                ),

                array(
                    'key'          => 'field_city_phone',
                    'label'        => 'Phone Number',
                    'name'         => 'city_phone',
                    'type'         => 'text',
                    'instructions' => 'With country code. E.g. +91-9876543210',
                    'required'     => 1,
                    'placeholder'  => '+91-9876543210',
                ),

                // WhatsApp Country Code
                array(
                    'key'          => 'field_city_whatsapp_country',
                    'label'        => 'WhatsApp Country Code',
                    'name'         => 'city_whatsapp_country',
                    'type'         => 'text',
                    'instructions' => 'Country code only (numbers). E.g. 91 for India, 1 for USA',
                    'required'     => 1,
                    'placeholder'  => '91',
                    'default_value'=> '91',
                ),

                // WhatsApp Number (without country code)
                array(
                    'key'          => 'field_city_whatsapp_number',
                    'label'        => 'WhatsApp Number',
                    'name'         => 'city_whatsapp_number',
                    'type'         => 'text',
                    'instructions' => 'Number without country code. E.g. 9876543210',
                    'required'     => 1,
                    'placeholder'  => '9876543210',
                ),

                // ── SECTION: Photos ──────────────────────────────
                array(
                    'key'       => 'field_city_tab_photos',
                    'label'     => 'Photos',
                    'type'      => 'tab',
                    'placement' => 'top',
                ),

                array(
                    'key'           => 'field_city_photo_entry',
                    'label'         => 'Entry Gate / Shop Front Photo',
                    'name'          => 'city_photo_entry',
                    'type'          => 'image',
                    'instructions'  => 'Photo of shop entry or front view. Recommended: 1200x600px',
                    'required'      => 0,
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                ),

                array(
                    'key'           => 'field_city_photo_internal',
                    'label'         => 'Internal Service Area Photo',
                    'name'          => 'city_photo_internal',
                    'type'          => 'image',
                    'instructions'  => 'Photo of internal repair area. Recommended: 1200x600px',
                    'required'      => 0,
                    'return_format' => 'array',
                    'preview_size'  => 'medium',
                ),

                array(
                    'key'          => 'field_city_landmark',
                    'label'        => 'Landmark',
                    'name'         => 'city_landmark',
                    'type'         => 'text',
                    'instructions' => 'Nearest landmark. E.g. Near Roshpa Tower, Near SBI Main Branch',
                    'required'     => 0,
                    'placeholder'  => 'Near Roshpa Tower',
                ),

                // ── SECTION: Active Services ─────────────────────
                array(
                    'key'       => 'field_city_tab_services',
                    'label'     => 'Active Services',
                    'type'      => 'tab',
                    'placement' => 'top',
                ),

                array(
                    'key'          => 'field_city_active_services',
                    'label'        => 'Active Services for This City',
                    'name'         => 'city_active_services',
                    'type'         => 'relationship',
                    'instructions' => 'Select which services are available at this service center. Only selected services will show on city page.',
                    'required'     => 0,
                    'post_type'    => array('service'),
                    'filters'      => array('search'),
                    'min'          => '',
                    'max'          => '',
                    'return_format'=> 'object',
                    'ui'           => 1,
                ),

                // Brand Services (shown after pillar service selected)
                array(
                    'key'          => 'field_city_active_brands',
                    'label'        => 'Active Brand Services',
                    'name'         => 'city_active_brands',
                    'type'         => 'relationship',
                    'instructions' => 'Select brand-specific services for this city (e.g. Dell Laptop Repair, HP Battery Replacement). These will show inside each service page.',
                    'required'     => 0,
                    'post_type'    => array('service'),
                    'filters'      => array('search'),
                    'return_format'=> 'object',
                    'ui'           => 1,
                ),

                // ── SECTION: Map ─────────────────────────────────
                // ── SEO CONTENT TAB ─────────────────────────
                array(
                    'key'       => 'field_city_tab_seo',
                    'label'     => 'SEO Content',
                    'type'      => 'tab',
                    'placement' => 'top',
                ),

                array(
                    'key'     => 'field_city_seo_info',
                    'label'   => '📌 City SEO Content',
                    'name'    => '',
                    'type'    => 'message',
                    'message' => '<strong>Google ke liye zaroori hai!</strong> City page pe unique content hona chahiye jo Google ko bataye ki yeh page genuinely usi city ke liye hai. Achha intro = better local ranking.',
                ),

                array(
                    'key'          => 'field_city_intro_headline',
                    'label'        => '🏆 Expert Headline',
                    'name'         => 'city_intro_headline',
                    'type'         => 'text',
                    'instructions' => 'H2 heading. E.g. "Ranchi ka Sabse Trusted Laptop Repair Center — 10 Saal Ka Bharosa"',
                    'placeholder'  => '[City] ka Sabse Trusted Laptop Repair Center',
                    'required'     => 0,
                ),

                array(
                    'key'          => 'field_city_intro_text',
                    'label'        => '📝 City Introduction',
                    'name'         => 'city_intro_text',
                    'type'         => 'textarea',
                    'instructions' => '100-200 words. City name, center experience, services, local trust factors. Google ko city-specific signal deta hai.',
                    'rows'         => 6,
                    'placeholder'  => 'E.g. Ranchi, Jharkhand mein laptop repair ke liye E Zone Care pichhle 10 saalon se aapka sabse trusted partner hai...',
                    'required'     => 0,
                ),

                // v1.5.1 — Why Choose Us
                array(
                    'key'          => 'field_city_why_choose',
                    'label'        => '⭐ Why Choose Us',
                    'name'         => 'city_why_choose',
                    'type'         => 'textarea',
                    'instructions' => '100-120 words. Is center ko doosron se kya alag karta hai — USP, warranty, turnaround, pickup/drop, trust factors. AI Step 1 Output 5 yahan paste karo.',
                    'rows'         => 5,
                    'placeholder'  => 'What truly sets {center} apart in {city} is the rare combination of...',
                    'required'     => 0,
                ),

                // ── MAP TAB ──────────────────────────────────
               array(
                    'key'       => 'field_city_tab_map',
                    'label'     => 'Google Map',
                    'type'      => 'tab',
                    'placement' => 'top',
                ),

                array(
                    'key'          => 'field_city_map_url',
                    'label'        => 'Google Map Embed URL',
                    'name'         => 'city_map_url',
                    'type'         => 'url',
                    'instructions' => 'Google Maps → Share → Embed a map → Copy src URL only',
                    'required'     => 0,
                    'placeholder'  => 'https://www.google.com/maps/embed?pb=...',
                ),

                // ── SOCIAL LINKS TAB ─────────────────────────
                array(
                    'key'       => 'field_city_tab_social',
                    'label'     => 'Social Links',
                    'type'      => 'tab',
                    'placement' => 'top',
                ),

                array(
                    'key'          => 'field_city_facebook_url',
                    'label'        => 'Facebook Page URL',
                    'name'         => 'city_facebook_url',
                    'type'         => 'url',
                    'instructions' => 'Partner ki Facebook page ka link. E.g. https://facebook.com/ezonecare.ranchi',
                    'required'     => 0,
                    'placeholder'  => 'https://facebook.com/yourpage',
                ),

                array(
                    'key'          => 'field_city_instagram_url',
                    'label'        => 'Instagram Profile URL',
                    'name'         => 'city_instagram_url',
                    'type'         => 'url',
                    'instructions' => 'Partner ki Instagram profile ka link.',
                    'required'     => 0,
                    'placeholder'  => 'https://instagram.com/yourprofile',
                ),

                array(
                    'key'          => 'field_city_gmb_url',
                    'label'        => 'Google My Business (GMB) URL',
                    'name'         => 'city_gmb_url',
                    'type'         => 'url',
                    'instructions' => 'Google Maps pe business listing ka link. GMB Dashboard → View on Maps → Copy URL',
                    'required'     => 0,
                    'placeholder'  => 'https://maps.google.com/?cid=...',
                ),

                array(
                    'key'          => 'field_city_est_year',
                    'label'        => 'Established Year',
                    'name'         => 'city_est_year',
                    'type'         => 'number',
                    'instructions' => 'Service center kab se hai? E.g. 2015',
                    'required'     => 0,
                    'placeholder'  => '2015',
                    'min'          => 1990,
                    'max'          => 2030,
                ),

                array(
                    'key'          => 'field_city_rating',
                    'label'        => 'Google / JustDial Rating',
                    'name'         => 'city_rating',
                    'type'         => 'text',
                    'instructions' => 'Partner ki rating. E.g. 4.5',
                    'required'     => 0,
                    'placeholder'  => '4.5',
                ),

                // v1.5.1 — Total Repairs Done stat card ke liye
                array(
                    'key'          => 'field_city_repair_count',
                    'label'        => 'Total Repairs Done',
                    'name'         => 'city_repair_count',
                    'type'         => 'text',
                    'instructions' => 'Stat card mein dikhega. E.g. 1,00,000+ ya 500+',
                    'required'     => 0,
                    'placeholder'  => '500+',
                ),

                // ── ABOUT TAB ─────────────────────────────────────
                array(
                    'key'       => 'field_city_tab_about',
                    'label'     => 'About Page',
                    'type'      => 'tab',
                    'placement' => 'top',
                ),

                array(
                    'key'          => 'field_city_about_title',
                    'label'        => 'About Section Heading',
                    'name'         => 'city_about_title',
                    'type'         => 'text',
                    'instructions' => 'About page ka main heading. E.g. "10 Saal Ka Bharosa — E Zone Care Ranchi"',
                    'required'     => 0,
                    'placeholder'  => '10 Saal Ka Bharosa — E Zone Care Ranchi',
                ),

                array(
                    'key'          => 'field_city_about_story',
                    'label'        => 'Our Story / Partner History',
                    'name'         => 'city_about_story',
                    'type'         => 'textarea',
                    'instructions' => 'Partner ki history, experience, specialization. 150-300 words best hai SEO ke liye. Paragraphs ke beech blank line do.',
                    'required'     => 0,
                    'rows'         => 8,
                    'placeholder'  => 'E Zone Care ki shuruaat 2015 mein Ranchi se hui thi...',
                ),

                array(
                    'key'          => 'field_city_team_photo',
                    'label'        => 'Team / Owner Photo (Optional)',
                    'name'         => 'city_team_photo',
                    'type'         => 'image',
                    'instructions' => 'Team ya owner ki photo. Agar upload nahi ki to sirf text dikhayi dega. Recommended: 600x400px',
                    'required'     => 0,
                    'return_format'=> 'array',
                    'preview_size' => 'medium',
                ),

                // ── LOCAL INTRO TAB ───────────────────────────────
                array(
                    'key'       => 'field_city_tab_local_intro',
                    'label'     => '📝 Local Intro',
                    'type'      => 'tab',
                    'placement' => 'top',
                ),

                array(
                    'key'     => 'field_city_local_intro_info',
                    'label'   => '📌 Local Intro — SEO Boost',
                    'name'    => '',
                    'type'    => 'message',
                    'message' => '<strong>Ye fields optional hain lekin SEO ke liye powerful hain!</strong><br><br>
Har service ke liye ek city-specific intro paragraph likho (80-120 words).<br>
Placeholders use kar sakte ho: <code>{city}</code> <code>{center}</code> <code>{area}</code> <code>{landmark}</code><br><br>
<strong>Field EMPTY</strong> = Default content show hoga (with auto placeholders)<br>
<strong>Field FILLED</strong> = Aapka custom intro content ke upar show hoga ✅<br><br>
<em>Progress bar neeche dikh raha hai kitne fill hain.</em>',
                ),

                // ── Dell Services ─────────────────────────────────
                array(
                    'key'          => 'field_city_local_intro_dell-laptop-repair-service',
                    'label'        => '🔧 Dell Laptop Repair — Local Intro',
                    'name'         => 'city_local_intro_dell-laptop-repair-service',
                    'type'         => 'textarea',
                    'instructions' => '80-120 words. {city} {center} {area} {landmark} use kar sakte ho.',
                    'rows'         => 4,
                    'placeholder'  => 'E.g. {center} {city} mein Dell laptop repair ke liye...',
                    'required'     => 0,
                ),

                array(
                    'key'          => 'field_city_local_intro_dell-laptop-keyboard-replacement',
                    'label'        => '⌨️ Dell Keyboard Replacement — Local Intro',
                    'name'         => 'city_local_intro_dell-laptop-keyboard-replacement',
                    'type'         => 'textarea',
                    'instructions' => '80-120 words. {city} {center} {area} {landmark} use kar sakte ho.',
                    'rows'         => 4,
                    'placeholder'  => 'E.g. {city} mein Dell keyboard replacement ke liye...',
                    'required'     => 0,
                ),

                array(
                    'key'          => 'field_city_local_intro_dell-laptop-battery-replacement',
                    'label'        => '🔋 Dell Battery Replacement — Local Intro',
                    'name'         => 'city_local_intro_dell-laptop-battery-replacement',
                    'type'         => 'textarea',
                    'instructions' => '80-120 words. {city} {center} {area} {landmark} use kar sakte ho.',
                    'rows'         => 4,
                    'placeholder'  => 'E.g. {city} mein Dell battery replacement ke liye...',
                    'required'     => 0,
                ),

                array(
                    'key'          => 'field_city_local_intro_dell-laptop-screen-replacement',
                    'label'        => '🖥️ Dell Screen Replacement — Local Intro',
                    'name'         => 'city_local_intro_dell-laptop-screen-replacement',
                    'type'         => 'textarea',
                    'instructions' => '80-120 words. {city} {center} {area} {landmark} use kar sakte ho.',
                    'rows'         => 4,
                    'placeholder'  => 'E.g. {city} mein Dell screen replacement ke liye...',
                    'required'     => 0,
                ),

                array(
                    'key'          => 'field_city_local_intro_dell-laptop-motherboard-repair',
                    'label'        => '🔌 Dell Motherboard Repair — Local Intro',
                    'name'         => 'city_local_intro_dell-laptop-motherboard-repair',
                    'type'         => 'textarea',
                    'instructions' => '80-120 words. {city} {center} {area} {landmark} use kar sakte ho.',
                    'rows'         => 4,
                    'placeholder'  => 'E.g. {city} mein Dell motherboard repair ke liye...',
                    'required'     => 0,
                ),

                // ── Acer Services ─────────────────────────────────
                array(
                    'key'          => 'field_city_local_intro_acer-laptop-repair-service',
                    'label'        => '🔧 Acer Laptop Repair — Local Intro',
                    'name'         => 'city_local_intro_acer-laptop-repair-service',
                    'type'         => 'textarea',
                    'instructions' => '80-120 words. {city} {center} {area} {landmark} use kar sakte ho.',
                    'rows'         => 4,
                    'placeholder'  => 'E.g. {center} {city} mein Acer laptop repair ke liye...',
                    'required'     => 0,
                ),

                array(
                    'key'          => 'field_city_local_intro_acer-laptop-keyboard-replacement',
                    'label'        => '⌨️ Acer Keyboard Replacement — Local Intro',
                    'name'         => 'city_local_intro_acer-laptop-keyboard-replacement',
                    'type'         => 'textarea',
                    'instructions' => '80-120 words. {city} {center} {area} {landmark} use kar sakte ho.',
                    'rows'         => 4,
                    'placeholder'  => 'E.g. {city} mein Acer keyboard replacement ke liye...',
                    'required'     => 0,
                ),

                array(
                    'key'          => 'field_city_local_intro_acer-laptop-battery-replacement',
                    'label'        => '🔋 Acer Battery Replacement — Local Intro',
                    'name'         => 'city_local_intro_acer-laptop-battery-replacement',
                    'type'         => 'textarea',
                    'instructions' => '80-120 words. {city} {center} {area} {landmark} use kar sakte ho.',
                    'rows'         => 4,
                    'placeholder'  => 'E.g. {city} mein Acer battery replacement ke liye...',
                    'required'     => 0,
                ),

                array(
                    'key'          => 'field_city_local_intro_acer-laptop-screen-replacement',
                    'label'        => '🖥️ Acer Screen Replacement — Local Intro',
                    'name'         => 'city_local_intro_acer-laptop-screen-replacement',
                    'type'         => 'textarea',
                    'instructions' => '80-120 words. {city} {center} {area} {landmark} use kar sakte ho.',
                    'rows'         => 4,
                    'placeholder'  => 'E.g. {city} mein Acer screen replacement ke liye...',
                    'required'     => 0,
                ),

                array(
                    'key'          => 'field_city_local_intro_acer-laptop-motherboard-repair',
                    'label'        => '🔌 Acer Motherboard Repair — Local Intro',
                    'name'         => 'city_local_intro_acer-laptop-motherboard-repair',
                    'type'         => 'textarea',
                    'instructions' => '80-120 words. {city} {center} {area} {landmark} use kar sakte ho.',
                    'rows'         => 4,
                    'placeholder'  => 'E.g. {city} mein Acer motherboard repair ke liye...',
                    'required'     => 0,
                ),

            ), // end fields array

            'location' => array(
                array(
                    array(
                        'param'    => 'post_type',
                        'operator' => '==',
                        'value'    => 'city',
                    ),
                ),
            ),
            'menu_order'            => 0,
            'position'              => 'normal',
            'style'                 => 'default',
            'label_placement'       => 'top',
            'instruction_placement' => 'label',
            'active'                => true,
        ));
    }

    // ═══════════════════════════════════════════════════════════
    // v1.4.7 — RESET BUTTON + ADMIN SCRIPTS
    // ═══════════════════════════════════════════════════════════

    /**
     * Intro field ke baad Reset button + state badge render karo
     */
    public function render_reset_button( $field ) {
        // Sirf city_local_intro_ wale fields pe
        if ( strpos( $field['name'], 'city_local_intro_' ) !== 0 ) return;

        global $post;
        if ( ! $post || $post->post_type !== 'city' ) return;

        $service_slug = str_replace( 'city_local_intro_', '', $field['name'] );
        $city_id      = $post->ID;
        $state        = self::get_intro_state( $city_id, $service_slug );

        // State badge
        if ( $state === 'customized' ) {
            $badge = '<span class="ez-intro-badge ez-badge-custom">✅ Customized</span>';
        } else {
            $badge = '<span class="ez-intro-badge ez-badge-default">🔵 Default (service post se auto-loaded)</span>';
        }

        echo '<div class="ez-intro-actions">';
        echo $badge;
        echo '<button type="button"
                class="button ez-reset-intro-btn"
                data-city="' . esc_attr( $city_id ) . '"
                data-slug="' . esc_attr( $service_slug ) . '"
                data-field="' . esc_attr( $field['key'] ) . '"
                title="Is field ka custom text delete karo — next load pe service post ka default text wapas aayega">';
        echo '↺ Reset to Default';
        echo '</button>';
        echo '</div>';
    }

    /**
     * Admin scripts + styles + nonce enqueue karo
     */
    public function enqueue_intro_scripts( $hook ) {
        global $post;
        if ( ! in_array( $hook, array( 'post.php', 'post-new.php' ) ) ) return;
        if ( ! $post || $post->post_type !== 'city' ) return;

        // Inline CSS
        wp_add_inline_style( 'wp-admin', '
            .ez-intro-actions {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-top: 6px;
                padding: 6px 0;
                border-top: 1px solid #e2e8f0;
            }
            .ez-intro-badge {
                font-size: 12px;
                font-weight: 600;
                padding: 3px 10px;
                border-radius: 20px;
                display: inline-block;
            }
            .ez-badge-custom {
                background: #c6f6d5;
                color: #22543d;
                border: 1px solid #9ae6b4;
            }
            .ez-badge-default {
                background: #bee3f8;
                color: #2a4365;
                border: 1px solid #90cdf4;
            }
            .ez-reset-intro-btn {
                font-size: 12px !important;
                padding: 3px 10px !important;
                height: auto !important;
                line-height: 1.5 !important;
                color: #c53030 !important;
                border-color: #fc8181 !important;
            }
            .ez-reset-intro-btn:hover {
                background: #fff5f5 !important;
                border-color: #e53e3e !important;
            }
            .ez-reset-intro-btn.loading {
                opacity: 0.6;
                pointer-events: none;
            }
        ' );

        // Inline JS — AJAX reset handler
        $nonce = wp_create_nonce( 'ez_reset_intro_nonce' );
        wp_add_inline_script( 'jquery', '
        jQuery(document).ready(function($) {

            // Reset button click
            $(document).on("click", ".ez-reset-intro-btn", function() {
                var $btn      = $(this);
                var city_id   = $btn.data("city");
                var slug      = $btn.data("slug");
                var field_key = $btn.data("field");

                if ( ! confirm("Is service ka custom intro delete hoga aur default text wapas aayega. Sure hain?") ) {
                    return;
                }

                $btn.addClass("loading").text("Resetting...");

                $.post(ajaxurl, {
                    action:       "ez_reset_intro",
                    nonce:        "' . $nonce . '",
                    city_id:      city_id,
                    service_slug: slug
                }, function(response) {
                    if ( response.success ) {
                        // ACF textarea mein default text set karo
                        var $textarea = $("textarea[data-key=\'" + field_key + "\']," +
                                        "#acf-" + field_key);
                        if ( ! $textarea.length ) {
                            // Fallback: name attribute se find karo
                            $textarea = $("textarea[name*=\'" + slug + "\']");
                        }
                        if ( $textarea.length ) {
                            $textarea.val( response.data.default_text );
                        }

                        // Badge update karo
                        $btn.closest(".ez-intro-actions")
                            .find(".ez-intro-badge")
                            .removeClass("ez-badge-custom")
                            .addClass("ez-badge-default")
                            .text("🔵 Default (service post se auto-loaded)");

                        $btn.removeClass("loading").text("↺ Reset to Default");
                    } else {
                        alert("Reset failed: " + (response.data.message || "Unknown error"));
                        $btn.removeClass("loading").text("↺ Reset to Default");
                    }
                }).fail(function() {
                    alert("Network error. Please try again.");
                    $btn.removeClass("loading").text("↺ Reset to Default");
                });
            });

        });
        ' );
    }

    /**
     * Get all city data
     */
    public static function get_city_data($city_id) {
        if (!function_exists('get_field')) return array();

        $country_code = get_field('city_whatsapp_country', $city_id) ?: '91';
        $wa_number    = get_field('city_whatsapp_number', $city_id);
        $full_wa      = preg_replace('/[^0-9]/', '', $country_code . $wa_number);

        $photo_entry    = get_field('city_photo_entry', $city_id);
        $photo_internal = get_field('city_photo_internal', $city_id);

        return array(
            'center_name'      => get_field('city_center_name', $city_id),
            'tagline'          => get_field('city_tagline', $city_id),
            'area'             => get_field('city_area', $city_id),
            'landmark'         => get_field('city_landmark', $city_id),
            'address'          => get_field('city_address', $city_id),
            'pincode'          => get_field('city_pincode', $city_id),
            'state'            => get_field('city_state', $city_id),
            'phone'            => get_field('city_phone', $city_id),
            'whatsapp_country' => $country_code,
            'whatsapp_number'  => $wa_number,
            'whatsapp_full'    => $full_wa,
            'hours'            => get_field('city_hours', $city_id),
            'photo_entry'      => $photo_entry ? $photo_entry['url'] : '',
            'photo_internal'   => $photo_internal ? $photo_internal['url'] : '',
            'map_url'          => html_entity_decode(get_field('city_map_url', $city_id) ?: ''),
            'facebook_url'     => get_field('city_facebook_url', $city_id) ?: '',
            'instagram_url'    => get_field('city_instagram_url', $city_id) ?: '',
            'gmb_url'          => get_field('city_gmb_url', $city_id) ?: '',
            'est_year'         => get_field('city_est_year', $city_id) ?: '',
            'rating'           => get_field('city_rating', $city_id) ?: '',
            'repair_count'     => get_field('city_repair_count', $city_id) ?: '',
            'about_title'      => get_field('city_about_title', $city_id) ?: '',
            'about_story'      => get_field('city_about_story', $city_id) ?: '',
            'why_choose'       => get_field('city_why_choose', $city_id) ?: '',
            'team_photo'       => ($tp = get_field('city_team_photo', $city_id)) ? $tp['url'] : '',
            'active_services'  => get_field('city_active_services', $city_id) ?: array(),
            'active_brands'    => get_field('city_active_brands', $city_id) ?: array(),
        );
    }

    /**
     * Get WhatsApp link with optional message
     * Auto uses city name in message
     */
    public static function get_whatsapp_link($city_id, $city_name = '', $service_name = '') {
        if (!function_exists('get_field')) return '#';

        $country = preg_replace('/[^0-9]/', '', get_field('city_whatsapp_country', $city_id) ?: '91');
        $number  = preg_replace('/[^0-9]/', '', get_field('city_whatsapp_number', $city_id));

        if (empty($number)) return '#';

        $full_number = $country . $number;

        // Auto message
        if (!empty($service_name) && !empty($city_name)) {
            $message = "Hi, I need {$service_name} in {$city_name}";
        } elseif (!empty($city_name)) {
            $message = "Hi, I need laptop repair service in {$city_name}";
        } else {
            $message = "Hi, I need laptop repair service";
        }

        return 'https://wa.me/' . $full_number . '?text=' . urlencode($message);
    }

    /**
     * Get phone link
     */
    public static function get_phone_link($city_id) {
        if (!function_exists('get_field')) return '#';
        $phone = get_field('city_phone', $city_id);
        if (empty($phone)) return '#';
        return 'tel:' . preg_replace('/[^0-9+]/', '', $phone);
    }

    /**
     * Get Schema.org LocalBusiness JSON-LD for SEO
     */
    public static function get_schema($city_id, $city_name) {
        $data = self::get_city_data($city_id);
        if (empty($data['center_name'])) return '';

        $schema = array(
            '@context'        => 'https://schema.org',
            '@type'           => 'LocalBusiness',
            'name'            => $data['center_name'],
            'description'     => $data['tagline'] ?: 'Professional Laptop Repair Service in ' . $city_name,
            'telephone'       => $data['phone'],
            'address'         => array(
                '@type'           => 'PostalAddress',
                'streetAddress'   => $data['address'],
                'addressLocality' => $city_name,
                'addressRegion'   => $data['state'],
                'postalCode'      => $data['pincode'],
                'addressCountry'  => 'IN',
            ),
            'openingHours'    => $data['hours'],
            'url'             => home_url('/' . get_post_field('post_name', $city_id) . '/'), // v1.2.1 FIX: get_permalink galat /?city= URL deta tha
        );

        if (!empty($data['photo_entry'])) {
            $schema['image'] = $data['photo_entry'];
        }

        return '<script type="application/ld+json">' . json_encode($schema, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . '</script>';
    }

    // ═══════════════════════════════════════════════════════════
    // v1.4.7 — AUTO DEFAULT TEXT SYSTEM
    // ═══════════════════════════════════════════════════════════

    /**
     * Service post ka raw content (with placeholders) extract karo
     * Yeh text ACF box mein default ke roop mein dikhega
     * Page pe bhi yahi Elementor content show hota hai (placeholder replace hokar)
     *
     * @param string $service_slug  e.g. 'dell-laptop-repair-service'
     * @return string  Raw text with {placeholders} intact
     */
    public static function get_service_raw_intro( $service_slug ) {
        $service_post = get_page_by_path( $service_slug, OBJECT, 'service' );
        if ( ! $service_post ) return '';

        $raw      = get_post_field( 'post_content', $service_post->ID );
        $rendered = apply_filters( 'the_content', $raw );

        // Tags clean karo, paragraph breaks preserve karo
        $text = preg_replace( '/<br\s*\/?>/i', "\n", $rendered );
        $text = preg_replace( '/<\/p>\s*<p[^>]*>/i', "\n\n", $text );
        $text = strip_tags( $text );
        $text = html_entity_decode( $text, ENT_QUOTES, 'UTF-8' );
        $text = preg_replace( '/\n{3,}/', "\n\n", trim( $text ) );

        // Sirf intro paragraphs nikalo — heading se pehle wale
        $heading_markers = array(
            'Common ', 'Why Choose', 'Our Services', 'Service Includes',
            'Repair Process', 'FAQ', 'Pricing', 'Cost', 'How We',
            'Hardware', 'Series We', 'Diagnostic', 'Reliable ',
        );

        $paragraphs  = explode( "\n\n", $text );
        $intro_paras = array();

        foreach ( $paragraphs as $para ) {
            $para = trim( $para );
            if ( empty( $para ) || str_word_count( $para ) < 15 ) continue;

            $is_heading = false;
            foreach ( $heading_markers as $marker ) {
                if ( stripos( $para, $marker ) === 0 ) {
                    $is_heading = true;
                    break;
                }
            }
            if ( $is_heading ) break;

            $intro_paras[] = $para;
            if ( count( $intro_paras ) >= 3 ) break;
        }

        return implode( "\n\n", $intro_paras );
    }

    /**
     * Register acf/load_value hooks for all 10 intro fields
     * Yeh hook sirf display ke liye hai — DB mein kuch nahi likhta
     * Field empty → service post ka raw content load karo (with {placeholders})
     * Field filled → admin ka saved custom text as-is dikhao
     */
    public static function register_load_value_hooks() {
        $slugs = array(
            'dell-laptop-repair-service',
            'dell-laptop-keyboard-replacement',
            'dell-laptop-battery-replacement',
            'dell-laptop-screen-replacement',
            'dell-laptop-motherboard-repair',
            'acer-laptop-repair-service',
            'acer-laptop-keyboard-replacement',
            'acer-laptop-battery-replacement',
            'acer-laptop-screen-replacement',
            'acer-laptop-motherboard-repair',
        );

        foreach ( $slugs as $slug ) {
            // Closure mein $slug capture karo
            add_filter(
                'acf/load_value/name=city_local_intro_' . $slug,
                function( $value, $post_id, $field ) use ( $slug ) {
                    // Field mein already kuch hai? As-is return karo
                    if ( ! empty( $value ) && trim( $value ) !== '' ) {
                        return $value;
                    }
                    // Empty hai — service post ka raw content load karo
                    return self::get_service_raw_intro( $slug );
                },
                10, 3
            );
        }
    }

    /**
     * v1.5.1 — Why Choose Us ACF field default fallback
     * Field empty → default text show karo admin mein (DB mein kuch nahi likhta)
     * Field filled → admin ka saved AI text as-is dikhao
     */
    public static function register_why_choose_hook() {
        add_filter(
            'acf/load_value/name=city_why_choose',
            function( $value, $post_id, $field ) {
                // Already filled hai? As-is return karo
                if ( ! empty( $value ) && trim( $value ) !== '' ) {
                    return $value;
                }
                // Empty hai — default text show karo (with {placeholders})
                // Yeh sirf admin display ke liye hai — DB mein save nahi hota
                return '{center} has been serving {city} for many years, building a strong reputation for honest and dependable electronics repair. '
                     . 'Our workshop at {area}, near {landmark}, specialises in laptop repair, LCD and LED television servicing. '
                     . 'Every repair is backed by a service warranty, and we use quality spare parts from trusted suppliers. '
                     . 'With a quick turnaround of mostly one working day, a city-wide pickup and drop facility, and a team of trained technicians, {center} is the most trusted choice in {city}. '
                     . 'Call or WhatsApp us on {phone} — we are open Monday to Saturday and happy to help.';
            },
            10, 3
        );
    }

    /**
     * Check karo ki Why Choose Us field DB mein actually saved hai ya default hai
     * DB mein saved = ACF post meta exist karta hai
     *
     * @param int $city_id
     * @return string  'customized' | 'default'
     */
    public static function get_why_choose_state( $city_id ) {
        $raw = get_post_meta( $city_id, 'city_why_choose', true );
        return ( ! empty( $raw ) && trim( $raw ) !== '' ) ? 'customized' : 'default';
    }

    /**
     * Check karo ki field DB mein actually saved hai ya sirf default load hua hai
     * DB mein saved = ACF post meta exist karta hai
     *
     * @param int    $city_id
     * @param string $service_slug
     * @return string  'customized' | 'default'
     */
    public static function get_intro_state( $city_id, $service_slug ) {
        $meta_key = 'city_local_intro_' . $service_slug;
        $raw      = get_post_meta( $city_id, $meta_key, true );
        if ( ! empty( $raw ) && trim( $raw ) !== '' ) {
            return 'customized';
        }
        return 'default';
    }

    /**
     * AJAX: Reset intro to default
     * Admin "Reset" click kare → DB se delete → next load pe default wapas
     */
    public static function ajax_reset_intro() {
        // Security check
        if ( ! check_ajax_referer( 'ez_reset_intro_nonce', 'nonce', false ) ) {
            wp_send_json_error( array( 'message' => 'Security check failed' ) );
        }

        if ( ! current_user_can( 'edit_posts' ) ) {
            wp_send_json_error( array( 'message' => 'Permission denied' ) );
        }

        $city_id = intval( $_POST['city_id'] ?? 0 );
        $slug    = sanitize_key( $_POST['service_slug'] ?? '' );

        if ( ! $city_id || ! $slug ) {
            wp_send_json_error( array( 'message' => 'Invalid data' ) );
        }

        // ACF field ka meta key delete karo
        $meta_key = 'city_local_intro_' . $slug;
        delete_post_meta( $city_id, $meta_key );

        // Default text return karo (box mein wapas load hoga)
        $default_text = self::get_service_raw_intro( $slug );

        wp_send_json_success( array(
            'message'      => 'Reset successful',
            'default_text' => $default_text,
        ) );
    }
}
