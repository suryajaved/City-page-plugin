<?php
/**
 * EzoneCare Internal Links Generator v1.0
 *
 * Brand service page pe related services automatically show karta hai
 * E.g. Dell Laptop Repair page pe:
 *   -> Dell Keyboard Replacement
 *   -> Dell Battery Replacement
 *   -> Dell Screen Replacement
 *   -> Dell Motherboard Repair
 *
 * @package EzonecareCity
 * @version 1.5.11
 *
 * =============================================================
 * v1.5.12 NOTE — TEMPORARILY DISABLED
 * =============================================================
 * Ab hum Elementor se directly {city_slug} placeholder use
 * kar rahe hain Elementor Containers ke links mein.
 * Example: https://ezonecare.com/{city_slug}/dell-laptop-repair
 *
 * Plugin ka inject_city_context() function (ezonecare-city-pages.php)
 * automatically JS inject karta hai jo {city_slug} ko real city
 * slug se replace kar deta hai.
 *
 * Yeh purana code future reference ke liye preserve hai.
 * Wapas enable karna ho toh: if(false) hatao.
 * =============================================================
 */

if ( ! defined( 'ABSPATH' ) ) exit;

// v1.5.12: Class disabled — if(false) wrap mein preserve kiya hai
// Wapas enable karna ho toh: if(false){ ... } hatao
if ( false ) {

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
     * dell-laptop-repair-service -> dell
     * acer-laptop-keyboard-replacement -> acer
     * hp-laptop-battery-replacement -> hp
     */
    public static function get_brand_prefix( $slug ) {
        $parts = explode( '-', $slug );
        return ! empty( $parts[0] ) ? strtolower( $parts[0] ) : '';
    }

    /**
     * Related brand services find karo
     */
    public static function get_related_services( $city_id, $current_svc ) {
        $current_slug  = $current_svc->post_name;
        $brand_prefix  = self::get_brand_prefix( $current_slug );

        if ( empty( $brand_prefix ) ) return array();

        $city_data     = Ezonecare_City_ACF::get_city_data( $city_id );
        $active_brands = $city_data['active_brands'] ?? array();

        if ( empty( $active_brands ) ) {
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

            if ( $svc_slug === $current_slug ) continue;
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
     */
    public static function get_brand_display_name( $prefix ) {
        $map = array(
            'dell'    => 'Dell',
            'acer'    => 'Acer',
            'hp'      => 'HP',
            'lenovo'  => 'Lenovo',
            'asus'    => 'Asus',
            'apple'   => 'Apple',
            'msi'     => 'MSI',
            'samsung' => 'Samsung',
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
                More <?php echo esc_html( $brand_name ); ?> Repair Services in <?php echo esc_html( $city_name ); ?>
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
        <?php
    }
}

} // end if(false) — class preserved for future use
