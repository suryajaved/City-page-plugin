<?php
/**
 * SEO Handler — v5.2
 *
 * FIXED IN v5.2:
 * - Meta description DIRECTLY print karo — Rank Math pe depend mat karo
 * - Focus keyword directly wp_head mein inject karo
 * - OG tags (og:title, og:description) properly set karo
 * - Rank Math filters sirf duplicate prevent karne ke liye rakhe hain
 *
 * @package EzonecareRouting
 * @version 1.5.11
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Ezonecare_SEO_Handler {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {

        // Title
        add_filter( 'wp_title',             array( $this, 'modify_page_title' ),     999, 2 );
        add_filter( 'document_title_parts', array( $this, 'modify_document_title' ), 999 );

        // v5.2: wp_head mein directly print karo — priority 1 (Rank Math se pehle)
        add_action( 'wp_head', array( $this, 'remove_wp_canonical' ),       0 );
        add_action( 'wp_head', array( $this, 'add_canonical_tag' ),         1 );
        add_action( 'wp_head', array( $this, 'add_meta_description' ),      1 ); // v5.2: DIRECT print
        add_action( 'wp_head', array( $this, 'add_og_tags' ),               1 ); // v5.2: OG tags
        add_action( 'wp_head', array( $this, 'add_robots_tag' ),            1 ); // v5.2: robots
        add_action( 'wp_head', array( $this, 'add_schema_output' ),         3 );

        // Rank Math filters — duplicate prevent karne ke liye
        // v5.2: Description aur title HAMARA version use karo
        add_filter( 'rank_math/frontend/canonical',   array( $this, 'rm_canonical' ),   999 );
        add_filter( 'rank_math/frontend/title',       array( $this, 'rm_title' ),       999 );
        add_filter( 'rank_math/frontend/description', array( $this, 'rm_suppress' ),    999 ); // v5.2: suppress RM description
        add_filter( 'rank_math/json_ld',              array( $this, 'rm_jsonld' ),      999, 2 );
        add_filter( 'rank_math/frontend/focus_keyword', array( $this, 'rm_focus_keyword' ), 999 );
    }

    // ── Rank Math Filters ─────────────────────────────────────────────────

    public function rm_canonical( $canonical ) {
        if ( ! $this->is_custom_route() ) return $canonical;
        return ''; // Hamara plugin print karega
    }

    public function rm_title( $title ) {
        if ( ! $this->is_custom_route() ) return $title;
        $t = $this->get_dynamic_title();
        return $t ? $t : $title;
    }

    /**
     * v5.2: Rank Math ki description suppress karo city pages pe
     * Hamari description wp_head priority 1 pe already print ho chuki hai
     * Rank Math ki description print hone se DUPLICATE banti thi
     */
    public function rm_suppress( $desc ) {
        if ( ! $this->is_custom_route() ) return $desc;
        return ''; // City pages pe Rank Math description suppress
    }

    public function rm_focus_keyword( $keyword ) {
        if ( ! $this->is_custom_route() ) return $keyword;
        $fk = $this->get_focus_keyword();
        return $fk ? $fk : $keyword;
    }

    public function rm_jsonld( $data, $jsonld ) {
        if ( ! $this->is_custom_route() ) return $data;
        return array(); // Hamara LocalBusiness + Breadcrumb schema better hai
    }

    // ── WP Canonical ──────────────────────────────────────────────────────

    public function remove_wp_canonical() {
        if ( ! $this->is_custom_route() ) return;
        remove_action( 'wp_head', 'rel_canonical' );
    }

    // ── Title ─────────────────────────────────────────────────────────────

    public function modify_page_title( $title, $sep = '' ) {
        if ( ! $this->is_custom_route() ) return $title;
        $t = $this->get_dynamic_title();
        return $t ? $t : $title;
    }

    public function modify_document_title( $parts ) {
        if ( ! $this->is_custom_route() ) return $parts;
        $t = $this->get_dynamic_title();
        if ( $t ) $parts['title'] = $t;
        return $parts;
    }

    private function get_dynamic_title() {
        $qh    = Ezonecare_Query_Handler::get_instance();
        $route = $qh->get_resolved_route();
        $city  = $qh->get_city();
        $svc   = $qh->get_service();
        $brand = $qh->get_brand();
        $cn    = $city  ? $city->post_title : '';
        $sn    = $svc   ? $svc->post_title  : '';
        $bn    = $brand ? $brand->name      : '';

        switch ( $route ) {
            case 'city':               return $cn ? 'Laptop Repair in ' . $cn . ' — E Zone Care' : '';
            case 'city-service':       return ( $cn && $sn ) ? $sn . ' in ' . $cn . ' | E Zone Care' : '';
            case 'city-service-brand': return ( $cn && $sn && $bn ) ? $bn . ' ' . $sn . ' in ' . $cn . ' | E Zone Care' : '';
            case 'service-brand':      return ( $sn && $bn ) ? $bn . ' ' . $sn . ' | E Zone Care' : '';
            case 'city-about':         return $cn ? 'About Us — E Zone Care ' . $cn : '';
            case 'city-contact':       return $cn ? 'Contact Us — E Zone Care ' . $cn : '';
            case 'city-services':      return $cn ? 'Laptop Repair Services in ' . $cn . ' | E Zone Care' : '';
        }
        return '';
    }

    // ── Meta Description — v5.2 DIRECT PRINT ─────────────────────────────

    /**
     * v5.2: Meta description DIRECTLY wp_head mein print karo
     * Priority 1 — Rank Math se pehle
     * Rank Math ki description suppress ho jaati hai rm_suppress() se
     */
    public function add_meta_description() {
        if ( ! $this->is_custom_route() ) return;

        $desc = $this->get_meta_description();
        if ( empty( $desc ) ) return;

        echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
    }

    /**
     * v5.2: OG tags directly print karo
     * og:title, og:description, og:url, og:image
     */
    public function add_og_tags() {
        if ( ! $this->is_custom_route() ) return;

        $title    = $this->get_dynamic_title();
        $desc     = $this->get_meta_description();
        $url      = $this->get_canonical_url();
        $og_image = $this->get_og_image();

        if ( $title ) {
            echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
            echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
        }
        if ( $desc ) {
            echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
            echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
        }
        if ( $url ) {
            echo '<meta property="og:url" content="' . esc_url( $url ) . '">' . "\n";
        }
        if ( $og_image ) {
            echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";
            echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n";
        }
        echo '<meta property="og:type" content="website">' . "\n";
    }

    /**
     * v5.2: Robots tag directly print
     */
    public function add_robots_tag() {
        if ( ! $this->is_custom_route() ) return;
        echo '<meta name="robots" content="index, follow">' . "\n";
    }

    private function get_meta_description() {
        $qh    = Ezonecare_Query_Handler::get_instance();
        $route = $qh->get_resolved_route();
        $city  = $qh->get_city();
        $svc   = $qh->get_service();
        $brand = $qh->get_brand();
        $cn    = $city  ? $city->post_title : '';
        $sn    = $svc   ? $svc->post_title  : '';
        $bn    = $brand ? $brand->name      : '';

        $city_data = ( $city && class_exists( 'Ezonecare_City_ACF' ) )
            ? Ezonecare_City_ACF::get_city_data( $city->ID ) : array();

        $center  = ! empty( $city_data['center_name'] ) ? $city_data['center_name'] : 'E Zone Care';
        $address = ! empty( $city_data['address'] )     ? $city_data['address']     : $cn;
        $phone   = ! empty( $city_data['phone'] )       ? $city_data['phone']       : '';
        $hours   = ! empty( $city_data['hours'] )       ? $city_data['hours']       : 'Mon-Sat 10AM-8PM';

        // Short address — 2 parts
        $short_address = $address;
        if ( strpos( $address, ',' ) !== false ) {
            $parts         = explode( ',', $address );
            $short_address = trim( $parts[ count($parts) - 2 ] ?? $parts[0] ) . ', ' . trim( end( $parts ) );
        }

        switch ( $route ) {
            case 'city':
                return $center . ' — trusted laptop repair center in ' . $cn
                     . '. Located at ' . $short_address
                     . '. Call ' . $phone
                     . '. Open ' . $hours . '.';

            case 'city-service':
                return $sn . ' in ' . $cn . ' by ' . $center
                     . '. Expert laptop technicians at ' . $short_address
                     . '. Call ' . $phone . ' for fast repair.';

            case 'city-service-brand':
                return $bn . ' ' . $sn . ' in ' . $cn . ' — ' . $center
                     . '. Authorised service for ' . $bn . ' laptops at ' . $short_address
                     . '. Call ' . $phone . '.';

            case 'city-services':
                return 'Laptop repair services in ' . $cn . ' by ' . $center
                     . '. Battery, keyboard, screen, motherboard repair. Call ' . $phone . '.';

            case 'city-about':
                return 'About ' . $center . ' — trusted laptop repair center in ' . $cn
                     . '. Located at ' . $short_address . '. ' . $hours . '.';

            case 'city-contact':
                return 'Contact ' . $center . ' in ' . $cn
                     . '. Call ' . $phone
                     . '. Address: ' . $short_address . '.';

            case 'service-brand':
                return $bn . ' ' . $sn . ' service by E Zone Care. '
                     . 'Expert ' . $bn . ' laptop repair across India. Book your repair today.';
        }

        return '';
    }

    private function get_focus_keyword() {
        $qh    = Ezonecare_Query_Handler::get_instance();
        $route = $qh->get_resolved_route();
        $city  = $qh->get_city();
        $svc   = $qh->get_service();
        $cn    = $city ? $city->post_title : '';
        $sn    = $svc  ? $svc->post_title  : '';

        switch ( $route ) {
            case 'city':               return $cn ? 'Laptop Repair ' . $cn : '';
            case 'city-service':       return ( $cn && $sn ) ? $sn . ' ' . $cn : '';
            case 'city-service-brand': return ( $cn && $sn ) ? $sn . ' ' . $cn : '';
            case 'city-services':      return $cn ? 'Laptop Repair Services ' . $cn : '';
            case 'city-about':         return $cn ? 'Laptop Repair Center ' . $cn : '';
            case 'city-contact':       return $cn ? 'Laptop Repair Contact ' . $cn : '';
        }
        return '';
    }

    private function get_og_image() {
        $qh   = Ezonecare_Query_Handler::get_instance();
        $city = $qh->get_city();
        if ( $city && class_exists( 'Ezonecare_City_ACF' ) ) {
            $data = Ezonecare_City_ACF::get_city_data( $city->ID );
            if ( ! empty( $data['photo_entry'] ) ) return $data['photo_entry'];
        }
        $logo_id = get_theme_mod( 'custom_logo' );
        if ( $logo_id ) {
            $logo = wp_get_attachment_image_src( $logo_id, 'full' );
            if ( $logo ) return $logo[0];
        }
        return '';
    }

    // ── Canonical ─────────────────────────────────────────────────────────

    public function add_canonical_tag() {
        if ( ! $this->is_custom_route() ) return;
        $url = $this->get_canonical_url();
        if ( $url ) {
            echo '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
        }
    }

    private function get_canonical_url() {
        $qh    = Ezonecare_Query_Handler::get_instance();
        $route = $qh->get_resolved_route();
        $city  = $qh->get_city();
        $svc   = $qh->get_service();
        $brand = $qh->get_brand();

        switch ( $route ) {
            case 'city':               return $city ? home_url( '/' . $city->post_name . '/' ) : '';
            case 'city-contact':       return $city ? home_url( '/' . $city->post_name . '/contact/' ) : '';
            case 'city-about':         return $city ? home_url( '/' . $city->post_name . '/about/' ) : '';
            case 'city-services':      return $city ? home_url( '/' . $city->post_name . '/services/' ) : '';
            case 'service-brand':      return ( $svc && $brand ) ? home_url( '/' . $svc->post_name . '/' . $brand->slug . '/' ) : '';
            case 'city-service':       return ( $city && $svc ) ? home_url( '/' . $city->post_name . '/' . $svc->post_name . '/' ) : '';
            case 'city-service-brand': return ( $city && $svc && $brand ) ? home_url( '/' . $city->post_name . '/' . $svc->post_name . '/' . $brand->slug . '/' ) : '';
        }
        return '';
    }

    // ── Schema ────────────────────────────────────────────────────────────

    public function add_schema_output() {
        if ( ! $this->is_custom_route() ) return;
        $this->output_breadcrumb_schema();
        $this->output_localbusiness_schema();
    }

    private function output_breadcrumb_schema() {
        $qh    = Ezonecare_Query_Handler::get_instance();
        $route = $qh->get_resolved_route();
        $city  = $qh->get_city();
        $svc   = $qh->get_service();
        $brand = $qh->get_brand();

        $items = array();
        $pos   = 1;
        $items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'name' => get_bloginfo( 'name' ), 'item' => home_url( '/' ) );

        if ( $city ) {
            $items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'name' => $city->post_title, 'item' => home_url( '/' . $city->post_name . '/' ) );
        }

        switch ( $route ) {
            case 'city-service':
            case 'city-service-brand':
                if ( $svc && $city ) $items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'name' => $svc->post_title, 'item' => home_url( '/' . $city->post_name . '/' . $svc->post_name . '/' ) );
                break;
            case 'service-brand':
                if ( $svc ) $items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'name' => $svc->post_title, 'item' => home_url( '/' . $svc->post_name . '/' ) );
                break;
            case 'city-about':
                if ( $city ) $items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'name' => 'About Us', 'item' => home_url( '/' . $city->post_name . '/about/' ) );
                break;
            case 'city-contact':
                if ( $city ) $items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'name' => 'Contact', 'item' => home_url( '/' . $city->post_name . '/contact/' ) );
                break;
            case 'city-services':
                if ( $city ) $items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'name' => 'Services', 'item' => home_url( '/' . $city->post_name . '/services/' ) );
                break;
        }

        if ( $brand && $svc && $city && $route === 'city-service-brand' ) {
            $items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'name' => $brand->name . ' ' . $svc->post_title, 'item' => home_url( '/' . $city->post_name . '/' . $svc->post_name . '/' . $brand->slug . '/' ) );
        } elseif ( $brand && $svc && $route === 'service-brand' ) {
            $items[] = array( '@type' => 'ListItem', 'position' => $pos++, 'name' => $brand->name . ' ' . $svc->post_title, 'item' => home_url( '/' . $svc->post_name . '/' . $brand->slug . '/' ) );
        }

        if ( count( $items ) < 2 ) return;

        $schema = array( '@context' => 'https://schema.org', '@type' => 'BreadcrumbList', 'itemListElement' => $items );
        echo '<script type="application/ld+json">' . "\n" . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . "\n</script>\n";
    }

    private function output_localbusiness_schema() {
        $qh    = Ezonecare_Query_Handler::get_instance();
        $route = $qh->get_resolved_route();
        $city  = $qh->get_city();

        $city_routes = array( 'city', 'city-about', 'city-contact', 'city-services', 'city-service', 'city-service-brand' );
        if ( ! in_array( $route, $city_routes ) || ! $city ) return;
        if ( ! class_exists( 'Ezonecare_City_ACF' ) ) return;

        $data    = Ezonecare_City_ACF::get_city_data( $city->ID );
        $center  = ! empty( $data['center_name'] ) ? $data['center_name'] : get_bloginfo( 'name' );
        $address = ! empty( $data['address'] )     ? $data['address']     : '';
        $pincode = ! empty( $data['pincode'] )     ? $data['pincode']     : '';
        $state   = ! empty( $data['state'] )       ? $data['state']       : '';
        $phone   = ! empty( $data['phone'] )       ? $data['phone']       : '';
        $hours   = ! empty( $data['hours'] )       ? $data['hours']       : '';

        if ( empty( $address ) && empty( $phone ) ) return;

        $schema = array(
            '@context' => 'https://schema.org',
            '@type'    => 'LocalBusiness',
            'name'     => $center,
            'url'      => home_url( '/' . $city->post_name . '/' ),
        );

        $postal = array( '@type' => 'PostalAddress', 'addressLocality' => $city->post_title, 'addressCountry' => 'IN' );
        if ( $address ) $postal['streetAddress'] = $address;
        if ( $state )   $postal['addressRegion'] = $state;
        if ( $pincode ) $postal['postalCode']    = $pincode;
        $schema['address'] = $postal;

        if ( $phone )               $schema['telephone']    = preg_replace( '/[^0-9+]/', '', $phone );
        if ( $hours )               $schema['openingHours'] = $hours;
        $img = $this->get_og_image();
        if ( $img )                 $schema['image']        = $img;
        if ( ! empty( $data['gmb_url'] ) ) $schema['hasMap'] = $data['gmb_url'];

        echo '<script type="application/ld+json">' . "\n" . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) . "\n</script>\n";
    }

    // ── Robots ────────────────────────────────────────────────────────────

    public function modify_robots_meta( $robots ) {
        if ( ! $this->is_custom_route() ) return $robots;
        unset( $robots['noindex'] );
        $robots['index']  = true;
        $robots['follow'] = true;
        return $robots;
    }

    // ── Helper ────────────────────────────────────────────────────────────

    private function is_custom_route() {
        return Ezonecare_Query_Handler::get_instance()->has_validated_data();
    }
}
