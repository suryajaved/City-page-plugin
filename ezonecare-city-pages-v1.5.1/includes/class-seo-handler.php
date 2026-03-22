<?php
/**
 * SEO Handler — v5.0 (Clean & Stable)
 *
 * SIMPLE APPROACH:
 * - Rank Math: OG tags handle kare (og:title, og:description, twitter)
 * - Hamara plugin: canonical, meta description, robots, schema handle kare
 * - Rank Math JSON-LD city pages pe empty karo (hamara LocalBusiness + Breadcrumb better hai)
 *
 * @package EzonecareRouting
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

        // Hamara output
        add_action( 'wp_head', array( $this, 'remove_wp_canonical' ),  0 );
        add_action( 'wp_head', array( $this, 'add_canonical_tag' ),    1 );
        add_action( 'wp_head', array( $this, 'add_meta_tags' ),        2 );
        add_action( 'wp_head', array( $this, 'add_schema_output' ),    3 );

        // Rank Math — sirf JSON-LD schema city pages pe empty karo
        // OG tags Rank Math handle karega (duplicate nahi hoga)
        add_filter( 'rank_math/frontend/canonical',   array( $this, 'rm_canonical' ),   999 );
        add_filter( 'rank_math/frontend/title',       array( $this, 'rm_title' ),       999 );
        add_filter( 'rank_math/frontend/description', array( $this, 'rm_description' ), 999 );
        add_filter( 'rank_math/json_ld',              array( $this, 'rm_jsonld' ),      999, 2 );

        // Robots
        add_filter( 'wp_robots', array( $this, 'modify_robots_meta' ) );

        // Focus Keyword — Rank Math ko pass karo
        add_filter( 'rank_math/frontend/focus_keyword', array( $this, 'rm_focus_keyword' ), 999 );
    }

    // ── Rank Math Filters ────────────────────────────────────────────────

    public function rm_canonical( $canonical ) {
        if ( ! $this->is_custom_route() ) return $canonical;
        return ''; // Hamara plugin print karega
    }

    public function rm_title( $title ) {
        if ( ! $this->is_custom_route() ) return $title;
        $t = $this->get_dynamic_title();
        return $t ? $t : $title;
    }

    public function rm_description( $desc ) {
        if ( ! $this->is_custom_route() ) return $desc;
        $d = $this->get_meta_description();
        return $d ? $d : $desc;
    }

    public function rm_focus_keyword( $keyword ) {
        if ( ! $this->is_custom_route() ) return $keyword;
        $fk = $this->get_focus_keyword();
        return $fk ? $fk : $keyword;
    }

    public function rm_jsonld( $data, $jsonld ) {
        if ( ! $this->is_custom_route() ) return $data;
        // City pages pe Rank Math ka schema empty — hamara LocalBusiness + Breadcrumb better hai
        return array();
    }

    // ── WP Canonical Remove ──────────────────────────────────────────────

    public function remove_wp_canonical() {
        if ( ! $this->is_custom_route() ) return;
        remove_action( 'wp_head', 'rel_canonical' );
    }

    // ── Title ────────────────────────────────────────────────────────────

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

    // ── Meta Tags ────────────────────────────────────────────────────────

    public function add_meta_tags() {
        if ( ! $this->is_custom_route() ) return;

        // NOTE: Meta description yahan PRINT NAHI hoti —
        // Rank Math rm_description() filter se hamari description pass hoti hai
        // Wahi ek tag print karta hai. Yahan print karne se DOUBLE meta description banti thi!

        // Robots
        echo '<meta name="robots" content="follow, index">' . "\n";

        // OG image — city ki photo (Rank Math ke paas ye nahi hoti)
        $og_image = $this->get_og_image();
        if ( $og_image ) {
            echo '<meta property="og:image" content="' . esc_url( $og_image ) . '">' . "\n";
            echo '<meta name="twitter:image" content="' . esc_url( $og_image ) . '">' . "\n";
        }
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

        // Short address — sirf locality tak (comma se pehle)
        $short_address = $address;
        if ( strpos( $address, ',' ) !== false ) {
            $parts = explode( ',', $address );
            // Last 2 parts use karo — locality + area
            $short_address = trim( $parts[ count($parts) - 2 ] ?? $parts[0] ) . ', ' . trim( end( $parts ) );
        }

        switch ( $route ) {
            case 'city':
                $desc = $center . ' — trusted laptop repair center in ' . $cn . '. Located at ' . $short_address . '. Call ' . $phone . '. Open ' . $hours . '.';
                break;
            case 'city-service':
                $desc = ( $cn && $sn ) ? 'Professional ' . $sn . ' in ' . $cn . ' at ' . $center . '. Fast turnaround, genuine parts, certified technicians. Call ' . ( $phone ?: 'us' ) . '.' : '';
                break;
            case 'city-service-brand':
                $desc = ( $cn && $sn && $bn ) ? 'Expert ' . $bn . ' ' . strtolower( $sn ) . ' in ' . $cn . ' at ' . $center . '. Certified technicians, genuine parts. Call ' . ( $phone ?: 'us' ) . '.' : '';
                break;
            case 'service-brand':
                $excerpt = ( $svc && ! empty( $svc->post_excerpt ) ) ? $svc->post_excerpt : ( $svc ? wp_trim_words( $svc->post_content, 15, '.' ) : '' );
                $desc = ( $sn && $bn ) ? 'Professional ' . $bn . ' ' . strtolower( $sn ) . ' by certified technicians. ' . $excerpt : '';
                break;
            case 'city-about':
                $desc = $cn ? 'About ' . $center . ' in ' . $cn . ' — experienced laptop repair technicians. Located at ' . $short_address . '. Call ' . ( $phone ?: 'us' ) . '.' : '';
                break;
            case 'city-contact':
                $desc = $cn ? 'Contact ' . $center . ' in ' . $cn . '. Address: ' . $short_address . '. Phone: ' . ( $phone ?: 'on site' ) . '. Hours: ' . $hours . '.' : '';
                break;
            case 'city-services':
                $desc = $cn ? $center . ' in ' . $cn . ' offers laptop repair — battery, screen, keyboard, motherboard & more. Call ' . ( $phone ?: 'us' ) . ' today.' : '';
                break;
            default:
                $desc = '';
        }

        // Max 155 characters — Google 160 tak show karta hai, safe limit 155
        if ( ! empty( $desc ) && mb_strlen( $desc ) > 155 ) {
            $desc = mb_substr( $desc, 0, 152 ) . '...';
        }

        return $desc;
    }

    private function get_focus_keyword() {
        $qh    = Ezonecare_Query_Handler::get_instance();
        $route = $qh->get_resolved_route();
        $city  = $qh->get_city();
        $svc   = $qh->get_service();
        $cn    = $city ? $city->post_title : '';
        $sn    = $svc  ? $svc->post_title  : '';

        // Focus keyword pattern: "Service Name City Name"
        // E.g. "Dell Laptop Repair Ranchi"
        //      "Dell Screen Replacement Ranchi"
        //      "Laptop Repair Ranchi"
        switch ( $route ) {
            case 'city':
                return $cn ? 'Laptop Repair ' . $cn : '';
            case 'city-service':
                return ( $cn && $sn ) ? $sn . ' ' . $cn : '';
            case 'city-service-brand':
                return ( $cn && $sn ) ? $sn . ' ' . $cn : '';
            case 'city-services':
                return $cn ? 'Laptop Repair Services ' . $cn : '';
            case 'city-about':
                return $cn ? 'Laptop Repair Center ' . $cn : '';
            case 'city-contact':
                return $cn ? 'Laptop Repair Contact ' . $cn : '';
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

    // ── Canonical ────────────────────────────────────────────────────────

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

    // ── Schema ───────────────────────────────────────────────────────────

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
        $pincode = ! empty( $data['pincode'] )      ? $data['pincode']     : '';
        $state   = ! empty( $data['state'] )        ? $data['state']       : '';
        $phone   = ! empty( $data['phone'] )        ? $data['phone']       : '';
        $hours   = ! empty( $data['hours'] )        ? $data['hours']       : '';

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

    // ── Robots ───────────────────────────────────────────────────────────

    public function modify_robots_meta( $robots ) {
        if ( ! $this->is_custom_route() ) return $robots;
        unset( $robots['noindex'] );
        $robots['index']  = true;
        $robots['follow'] = true;
        return $robots;
    }

    // ── Helper ───────────────────────────────────────────────────────────

    private function is_custom_route() {
        return Ezonecare_Query_Handler::get_instance()->has_validated_data();
    }
}
