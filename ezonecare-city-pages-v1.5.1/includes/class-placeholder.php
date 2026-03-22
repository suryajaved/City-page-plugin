<?php
/**
 * EzoneCare Placeholder Replacement Engine v1.0
 *
 * Service post content mein {city} {center} {area} etc
 * replace karta hai city-specific data se.
 *
 * Supported placeholders:
 *   {city}      → Ranchi / Chandrapur
 *   {center}    → E Zone Care / Faiz Computer
 *   {area}      → Main Road / Kasturba Road
 *   {landmark}  → Roshpa Tower / SBI Branch
 *   {phone}     → 9310896575
 *   {pincode}   → 834001
 *   {state}     → Jharkhand / Maharashtra
 *   {address}   → Full address
 *
 * @package EzonecareCity
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Ezonecare_Placeholder {

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Content mein placeholders replace karo
     */
    public static function process( $content, $city_id, $city_data ) {
        if ( empty( $content ) ) return $content;

        $search  = array_keys( self::get_replacements( $city_id, $city_data ) );
        $replace = array_values( self::get_replacements( $city_id, $city_data ) );

        return str_replace( $search, $replace, $content );
    }

    /**
     * Placeholder → Value mapping
     */
    public static function get_replacements( $city_id, $city_data ) {
        $city_name   = get_the_title( $city_id );
        $center_name = ! empty( $city_data['center_name'] ) ? $city_data['center_name'] : 'E Zone Care ' . $city_name;
        $area        = ! empty( $city_data['area'] )        ? $city_data['area']        : $city_name;
        $landmark    = get_field( 'city_landmark', $city_id ) ?: $area;

        return array(
            '{city}'     => $city_name,
            '{center}'   => $center_name,
            '{area}'     => $area,
            '{landmark}' => $landmark,
            '{phone}'    => ! empty( $city_data['phone'] )   ? $city_data['phone']   : '',
            '{pincode}'  => ! empty( $city_data['pincode'] ) ? $city_data['pincode'] : '',
            '{state}'    => ! empty( $city_data['state'] )   ? $city_data['state']   : '',
            '{address}'  => ! empty( $city_data['address'] ) ? $city_data['address'] : '',
        );
    }

    /**
     * Service slug se ACF field name banao
     * dell-laptop-keyboard-replacement → city_local_intro_dell_laptop_keyboard_replacement
     */
    public static function get_intro_field_name( $service_slug ) {
        return 'city_local_intro_' . $service_slug;
    }

    /**
     * City ke liye local intro get karo (null if not saved in DB)
     *
     * v1.4.7 FIX: get_field() ki jagah get_post_meta() use karo
     * Reason: get_field() ab acf/load_value hook se default text return karta hai
     * Isliye ACF box empty hone pe bhi get_field() null nahi deta — double intro bug
     * get_post_meta() = direct DB check — hook bypass — sirf actually saved value
     *
     * ACF box mein = default text dikhega (acf/load_value se) ✅
     * Page pe      = sirf DB saved text (null agar save nahi kiya) ✅
     */
    public static function get_local_intro( $city_id, $service_slug ) {
        $meta_key = 'city_local_intro_' . $service_slug;
        $value    = get_post_meta( $city_id, $meta_key, true );
        return ( ! empty( $value ) && trim( $value ) !== '' ) ? trim( $value ) : null;
    }

    /**
     * v1.4.7: Elementor rendered content se intro paragraphs strip karo
     *
     * Jab ACF intro filled ho to Elementor content ke pehle ke
     * intro-style paragraphs hata do — duplicate avoid karo
     *
     * Logic:
     * - HTML ko parse karo
     * - Pehle H2 heading tak ke <p> blocks check karo
     * - Jo paragraphs "In ", "Dell manufactures", "Acer manufactures",
     *   "HP manufactures", "Customers from" se shuru hote hain unhe hata do
     * - H2 heading milne ke baad sab as-is rakho
     *
     * @param string $rendered_html  Elementor rendered + placeholder replaced HTML
     * @return string  HTML without intro paragraphs
     */
    public static function strip_intro_from_content( $rendered_html ) {
        if ( empty( $rendered_html ) ) return $rendered_html;

        // Intro paragraph identify karne ke patterns
        $intro_patterns = array(
            '/^In\s+/i',
            '/^Dell\s+manufactures/i',
            '/^Acer\s+manufactures/i',
            '/^HP\s+manufactures/i',
            '/^Lenovo\s+manufactures/i',
            '/^Asus\s+manufactures/i',
            '/^Customers\s+from/i',
            '/^Apple\s+manufactures/i',
            '/^Samsung\s+manufactures/i',
        );

        // DOMDocument se parse karo
        $doc = new DOMDocument();
        // Suppress warnings for HTML5 tags
        libxml_use_internal_errors( true );
        $doc->loadHTML(
            '<?xml encoding="UTF-8">' . $rendered_html,
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();

        $xpath    = new DOMXPath( $doc );
        $h2_found = false;
        $to_remove = array();

        // Body ke direct children ya nested paragraphs check karo
        $paragraphs = $xpath->query( '//p' );

        foreach ( $paragraphs as $p ) {
            // H2 pehle aa gaya? Baaki paragraphs chodo
            $prev = $p->previousSibling;
            // Check if any H2 exists before this paragraph in document order
            $h2_before = $xpath->query( '//h2[following::p[. = $p]]' );

            // Simple approach: text content check karo
            $text = trim( $p->textContent );
            if ( empty( $text ) ) continue;

            // Intro pattern match karo
            $is_intro = false;
            foreach ( $intro_patterns as $pattern ) {
                if ( preg_match( $pattern, $text ) ) {
                    $is_intro = true;
                    break;
                }
            }

            if ( $is_intro ) {
                $to_remove[] = $p;
            }
        }

        // Remove identified paragraphs
        foreach ( $to_remove as $node ) {
            if ( $node->parentNode ) {
                $node->parentNode->removeChild( $node );
            }
        }

        // HTML wapas banao
        $result = $doc->saveHTML();

        // XML declaration clean karo
        $result = preg_replace( '/<\?xml[^>]+>\s*/i', '', $result );
        $result = preg_replace( '/^<!DOCTYPE[^>]+>\s*/i', '', $result );
        $result = preg_replace( '/^<html[^>]*>\s*<body[^>]*>/i', '', $result );
        $result = preg_replace( '/<\/body>\s*<\/html>\s*$/i', '', $result );

        return trim( $result ) ?: $rendered_html;
    }

    /**
     * Progress calculator
     * Returns: [ 'percent'=>60, 'filled'=>[...], 'empty'=>[...], 'total'=>10 ]
     */
    public static function get_progress( $city_id, $service_slugs ) {
        if ( empty( $service_slugs ) ) {
            return array( 'percent' => 0, 'filled' => array(), 'empty' => array(), 'total' => 0 );
        }

        $filled = array();
        $empty  = array();

        foreach ( $service_slugs as $slug ) {
            if ( self::get_local_intro( $city_id, $slug ) !== null ) {
                $filled[] = $slug;
            } else {
                $empty[] = $slug;
            }
        }

        $total = count( $service_slugs );

        return array(
            'percent' => $total > 0 ? round( ( count( $filled ) / $total ) * 100 ) : 0,
            'filled'  => $filled,
            'empty'   => $empty,
            'total'   => $total,
        );
    }
}
