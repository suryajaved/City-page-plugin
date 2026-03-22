<?php
/**
 * EzoneCare Internal Links Generator v1.0
 *
 * Brand service page pe related services automatically show karta hai
 * E.g. Dell Laptop Repair page pe:
 *   → Dell Keyboard Replacement
 *   → Dell Battery Replacement
 *   → Dell Screen Replacement
 *   → Dell Motherboard Repair
 *
 * Logic:
 * - Current service post ka slug dekho
 * - Same "brand prefix" wale dusre service posts find karo
 * - City ke active services se filter karo
 * - Clean card grid render karo
 *
 * @package EzonecareCity
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Ezonecare_Internal_Links {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Brand prefix extract karo slug se
     * dell-laptop-repair-service → dell
     * acer-laptop-keyboard-replacement → acer
     * hp-laptop-battery-replacement → hp
     */
    public static function get_brand_prefix( $slug ) {
        $parts = explode( '-', $slug );
        return ! empty( $parts[0] ) ? strtolower( $parts[0] ) : '';
    }

    /**
     * Related brand services find karo
     *
     * @param int    $city_id       City post ID
     * @param object $current_svc   Current service post
     * @return array Related service posts with URLs
     */
    public static function get_related_services( $city_id, $current_svc ) {
        $current_slug  = $current_svc->post_name;
        $brand_prefix  = self::get_brand_prefix( $current_slug );

        if ( empty( $brand_prefix ) ) return array();

        // City ke active brand services get karo
        $city_data     = Ezonecare_City_ACF::get_city_data( $city_id );
        $active_brands = $city_data['active_brands'] ?? array();

        if ( empty( $active_brands ) ) {
            // Fallback: sab published service posts
            $active_brands = get_posts( array(
                'post_type'      => 'service',
                'post_status'    => 'publish',
                'posts_per_page' => 50,
                'no_found_rows'  => true,
            ) );
        }

        $city_slug = get_post_field( 'post_name', $city_id );
        $related   = array();

        foreach ( $active_brands as $svc ) {
            $svc_obj = is_object( $svc ) ? $svc : get_post( $svc );
            if ( ! $svc_obj ) continue;

            $svc_slug = $svc_obj->post_name;

            // Skip current page
            if ( $svc_slug === $current_slug ) continue;

            // Same brand prefix wale hi lo
            if ( self::get_brand_prefix( $svc_slug ) !== $brand_prefix ) continue;

            $related[] = array(
                'title'     => $svc_obj->post_title,
                'url'       => home_url( '/' . $city_slug . '/' . $svc_slug . '/' ),
                'slug'      => $svc_slug,
                'icon'      => self::get_service_icon( $svc_slug ),
                'thumbnail' => get_the_post_thumbnail_url( $svc_obj->ID, 'medium' ),
            );
        }

        return $related;
    }

    /**
     * Service slug se icon decide karo
     */
    private static function get_service_icon( $slug ) {
        if ( strpos( $slug, 'keyboard' ) !== false )    return '⌨️';
        if ( strpos( $slug, 'battery' ) !== false )     return '🔋';
        if ( strpos( $slug, 'screen' ) !== false )      return '🖥️';
        if ( strpos( $slug, 'motherboard' ) !== false ) return '🔌';
        if ( strpos( $slug, 'repair' ) !== false )      return '🔧';
        if ( strpos( $slug, 'storage' ) !== false )     return '💾';
        if ( strpos( $slug, 'ram' ) !== false )         return '💡';
        return '🛠️';
    }

    /**
     * Brand display name — slug se
     * dell → Dell | acer → Acer | hp → HP
     */
    public static function get_brand_display_name( $prefix ) {
        $map = array(
            'dell'   => 'Dell',
            'acer'   => 'Acer',
            'hp'     => 'HP',
            'lenovo' => 'Lenovo',
            'asus'   => 'Asus',
            'apple'  => 'Apple',
            'msi'    => 'MSI',
            'samsung'=> 'Samsung',
        );
        return $map[ strtolower( $prefix ) ] ?? ucfirst( $prefix );
    }

    /**
     * HTML render karo — related services section
     */
    public static function render( $city_id, $current_svc, $city_name ) {
        $related = self::get_related_services( $city_id, $current_svc );
        if ( empty( $related ) ) return;

        $brand_prefix = self::get_brand_prefix( $current_svc->post_name );
        $brand_name   = self::get_brand_display_name( $brand_prefix );
        ?>
        <div class="ez-related-services">
            <h2 class="ez-related-title">
                🔗 More <?php echo esc_html( $brand_name ); ?> Repair Services in <?php echo esc_html( $city_name ); ?>
            </h2>
            <p class="ez-related-sub">
                Select a specific service for detailed information and booking
            </p>
            <div class="ez-related-grid">
                <?php foreach ( $related as $item ) : ?>
                <a href="<?php echo esc_url( $item['url'] ); ?>"
                   class="ez-related-card"
                   title="<?php echo esc_attr( $item['title'] . ' in ' . $city_name ); ?>">
                    <div class="ez-related-icon">
                        <?php if ( $item['thumbnail'] ) : ?>
                            <img src="<?php echo esc_url( $item['thumbnail'] ); ?>"
                                 alt="<?php echo esc_attr( $item['title'] ); ?>"
                                 loading="lazy">
                        <?php else : ?>
                            <span><?php echo $item['icon']; ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="ez-related-body">
                        <h3><?php echo esc_html( $item['title'] ); ?></h3>
                        <span class="ez-related-city">📍 <?php echo esc_html( $city_name ); ?></span>
                        <span class="ez-related-cta">View Service →</span>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <style>
        .ez-related-services {
            max-width: 960px;
            margin: 10px auto 30px;
            padding: 30px 25px;
            background: #f7fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
        }
        .ez-related-title {
            text-align: center;
            font-size: 1.3em;
            color: #1a1a2e;
            font-weight: 700;
            margin: 0 0 6px;
        }
        .ez-related-sub {
            text-align: center;
            color: #718096;
            font-size: 0.85em;
            margin: 0 0 24px;
        }
        .ez-related-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
            gap: 16px;
        }
        .ez-related-card {
            background: #fff;
            border-radius: 10px;
            overflow: hidden;
            text-decoration: none !important;
            display: flex;
            flex-direction: column;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            transition: transform 0.25s ease, box-shadow 0.25s ease;
            border: 1px solid #e2e8f0;
        }
        .ez-related-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }
        .ez-related-icon {
            width: 100%;
            aspect-ratio: 4/3;
            background: #ebf8ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.2em;
            overflow: hidden;
        }
        .ez-related-icon img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .ez-related-body {
            padding: 10px 12px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }
        .ez-related-body h3 {
            font-size: 0.82em;
            font-weight: 700;
            color: #1a1a2e !important;
            margin: 0;
            line-height: 1.4;
        }
        .ez-related-city {
            font-size: 0.75em;
            color: #718096;
        }
        .ez-related-cta {
            font-size: 0.78em;
            font-weight: 700;
            color: #3182ce;
            margin-top: 2px;
        }
        @media (max-width: 600px) {
            .ez-related-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
        }
        </style>
        <?php
    }
}
