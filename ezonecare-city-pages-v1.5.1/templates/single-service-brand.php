<?php
/**
 * Template: Single Service Brand
 * 
 * Template for displaying service-brand combination pages
 * URL: /service-slug/brand-slug/
 * Example: /laptop-keyboard-replacement/dell/
 * 
 * This template can be overridden by copying it to:
 * your-theme/ezonecare/single-service-brand.php
 *
 * @package EzonecareRouting
 * @version 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Get template data
$data = Ezonecare_Template_Loader::get_template_data();
$service = $data['service'];
$brand = $data['brand'];

// Fallback if data not available
if (!$service || !$brand) {
    get_template_part('404');
    return;
}

include(EZ_CITY_PLUGIN_DIR . 'templates/ez-header.php');

?>

<div id="primary" class="content-area ezonecare-service-brand">
    <main id="main" class="site-main">

        <article id="post-<?php echo esc_attr($service->ID); ?>" <?php post_class('service-brand-page'); ?>>
            
            <header class="entry-header">
                <?php
                /**
                 * Dynamic H1 Title
                 * Format: "Brand Name Service Title"
                 * Example: "Dell Laptop Keyboard Replacement"
                 */
                ?>
                <h1 class="entry-title">
                    <?php echo esc_html($brand->name); ?> 
                    <?php echo esc_html($service->post_title); ?>
                </h1>

                <?php
                // Breadcrumbs (if theme supports)
                if (function_exists('rank_math_the_breadcrumbs')) {
                    rank_math_the_breadcrumbs();
                } elseif (function_exists('yoast_breadcrumb')) {
                    yoast_breadcrumb('<p id="breadcrumbs">', '</p>');
                }
                ?>
            </header>

            <div class="entry-content">
                
                <?php
                /**
                 * Brand-specific intro section
                 * Shows dynamic brand information
                 */
                ?>
                <div class="brand-service-intro">
                    <p class="brand-highlight">
                        <?php 
                        printf(
                            __('Professional %s services for %s products', 'ezonecare-routing'),
                            esc_html($service->post_title),
                            '<strong>' . esc_html($brand->name) . '</strong>'
                        );
                        ?>
                    </p>
                </div>

                <?php
                /**
                 * Base service content
                 * Shows the main service post content
                 */
                ?>
                <div class="service-base-content">
                    <?php echo apply_filters('the_content', $service->post_content); ?>
                </div>

                <?php
                /**
                 * Brand-specific content section
                 * 
                 * OPTION 1: Use ACF fields (if ACF plugin is installed)
                 * Uncomment this if you want to use ACF for brand-specific content
                 */
                /*
                if (function_exists('get_field')) {
                    $brand_content = get_field('brand_specific_content_' . $brand->slug, $service->ID);
                    if ($brand_content) {
                        echo '<div class="brand-specific-content">';
                        echo wp_kses_post($brand_content);
                        echo '</div>';
                    }
                }
                */

                /**
                 * OPTION 2: Conditional logic based on brand
                 * Use this for simple brand-specific modifications
                 */
                ?>
                <div class="brand-specific-section">
                    <?php
                    // Example: Brand-specific benefits or features
                    switch ($brand->slug) {
                        case 'dell':
                            ?>
                            <div class="brand-features">
                                <h2>Why Choose Dell <?php echo esc_html($service->post_title); ?>?</h2>
                                <ul>
                                    <li>Genuine Dell parts and accessories</li>
                                    <li>Dell-certified technicians</li>
                                    <li>Warranty-safe service procedures</li>
                                </ul>
                            </div>
                            <?php
                            break;

                        case 'hp':
                            ?>
                            <div class="brand-features">
                                <h2>Why Choose HP <?php echo esc_html($service->post_title); ?>?</h2>
                                <ul>
                                    <li>Authorized HP service provider</li>
                                    <li>Original HP components</li>
                                    <li>HP warranty compliant</li>
                                </ul>
                            </div>
                            <?php
                            break;

                        case 'lenovo':
                            ?>
                            <div class="brand-features">
                                <h2>Why Choose Lenovo <?php echo esc_html($service->post_title); ?>?</h2>
                                <ul>
                                    <li>Lenovo-approved service methods</li>
                                    <li>Genuine Lenovo replacement parts</li>
                                    <li>ThinkPad and IdeaPad specialists</li>
                                </ul>
                            </div>
                            <?php
                            break;

                        default:
                            // Generic brand-specific section
                            ?>
                            <div class="brand-features">
                                <h2><?php echo esc_html($brand->name); ?> Service Excellence</h2>
                                <p>
                                    Our <?php echo esc_html($brand->name); ?> specialists provide 
                                    expert <?php echo esc_html(strtolower($service->post_title)); ?> 
                                    using genuine parts and manufacturer-approved methods.
                                </p>
                            </div>
                            <?php
                            break;
                    }
                    ?>
                </div>

                <?php
                /**
                 * Related services for this brand
                 * Shows other services available for this brand
                 */
                $validator = Ezonecare_Validator::get_instance();
                $brand_services = $validator->get_brand_services($brand->term_id);
                
                if (!empty($brand_services) && count($brand_services) > 1) {
                    ?>
                    <div class="related-brand-services">
                        <h2>Other <?php echo esc_html($brand->name); ?> Services</h2>
                        <ul class="service-list">
                            <?php
                            foreach ($brand_services as $related_service) {
                                // Skip current service
                                if ($related_service->ID === $service->ID) {
                                    continue;
                                }
                                
                                $related_url = home_url('/' . $related_service->post_name . '/' . $brand->slug . '/');
                                ?>
                                <li>
                                    <a href="<?php echo esc_url($related_url); ?>">
                                        <?php echo esc_html($brand->name . ' ' . $related_service->post_title); ?>
                                    </a>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <?php
                }
                ?>

                <?php
                /**
                 * Other brands for this service
                 * Shows all other brands that offer this service
                 */
                $service_brands = $validator->get_service_brands($service->ID);
                
                if (!empty($service_brands) && count($service_brands) > 1) {
                    ?>
                    <div class="other-brands">
                        <h2>We Also Service Other Brands</h2>
                        <ul class="brand-list">
                            <?php
                            foreach ($service_brands as $other_brand) {
                                // Skip current brand
                                if ($other_brand->term_id === $brand->term_id) {
                                    continue;
                                }
                                
                                $other_brand_url = home_url('/' . $service->post_name . '/' . $other_brand->slug . '/');
                                ?>
                                <li>
                                    <a href="<?php echo esc_url($other_brand_url); ?>">
                                        <?php echo esc_html($other_brand->name . ' ' . $service->post_title); ?>
                                    </a>
                                </li>
                                <?php
                            }
                            ?>
                        </ul>
                    </div>
                    <?php
                }
                ?>

                <?php
                /**
                 * Call-to-action section
                 */
                ?>
                <div class="cta-section">
                    <h2>Get Your <?php echo esc_html($brand->name); ?> Device Fixed Today</h2>
                    <p>Professional <?php echo esc_html($service->post_title); ?> service for all <?php echo esc_html($brand->name); ?> models.</p>
                    
                    <?php
                    // You can add buttons, forms, or contact info here
                    // Example with Elementor shortcode support:
                    // echo do_shortcode('[elementor-template id="123"]');
                    ?>
                </div>

            </div><!-- .entry-content -->

            <footer class="entry-footer">
                <?php
                // Optional: Add service categories, tags, etc.
                if (has_term('', 'service-category', $service->ID)) {
                    ?>
                    <div class="service-categories">
                        <?php
                        $categories = get_the_terms($service->ID, 'service-category');
                        if ($categories && !is_wp_error($categories)) {
                            echo '<span class="cat-links">';
                            echo __('Service Categories: ', 'ezonecare-routing');
                            $cat_links = array();
                            foreach ($categories as $category) {
                                $cat_links[] = '<a href="' . esc_url(get_term_link($category)) . '">' . esc_html($category->name) . '</a>';
                            }
                            echo implode(', ', $cat_links);
                            echo '</span>';
                        }
                        ?>
                    </div>
                    <?php
                }
                ?>
            </footer>

        </article>

    </main>
</div>

<?php
get_sidebar(); // Optional
include(EZ_CITY_PLUGIN_DIR . 'templates/ez-footer.php');
