<?php
/**
 * City + Service Page Template v3.16
 * URL: /ranchi/laptop-repair-services/
 * Brand cards = Service post ke ACF "Related Brand Posts"
 *               filtered by City's "Active Brand Services"
 */

if (!defined('ABSPATH')) exit;

$query_handler = Ezonecare_Query_Handler::get_instance();
$city_post     = $query_handler->get_city();
$service_post  = $query_handler->get_service();

if (!$city_post || !$service_post) { wp_redirect(home_url()); exit; }

$city_id      = $city_post->ID;
$city_name    = $city_post->post_title;
$city_slug    = $city_post->post_name;
$city_data    = Ezonecare_City_ACF::get_city_data($city_id);
$wa_link      = Ezonecare_City_ACF::get_whatsapp_link($city_id, $city_name, $service_post->post_title);
$ph_link      = Ezonecare_City_ACF::get_phone_link($city_id);
$service_name = $service_post->post_title;
$center_name  = $city_data['center_name'] ?: 'E Zone Care ' . $city_name;

// Photo ALT texts
$alt_entry    = $center_name . ' - ' . $service_name . ' Service Center Entry - ' . $city_name;
$alt_internal = $center_name . ' - ' . $service_name . ' Repair Area - ' . $city_name;
$has_entry    = !empty($city_data['photo_entry']);
$has_internal = !empty($city_data['photo_internal']);

// ── BRAND CARDS LOGIC (Custom Meta Box) ──────────────────
// Get brand IDs assigned to THIS service in THIS city
$active_brand_ids = Ezonecare_City_Meta_Box::get_active_brand_ids($city_id, $service_post->ID);

$show_brands = array();
if (!empty($active_brand_ids)) {
    // Load brand posts by IDs
    $show_brands = get_posts(array(
        'post_type'      => 'service',
        'post_status'    => 'publish',
        'post__in'       => $active_brand_ids,
        'orderby'        => 'post__in',
        'posts_per_page' => 50,
    ));
} else {
    // Fallback: get brands from ACF service_brand_posts if meta box not configured
    $acf_brands = get_field('service_brand_posts', $service_post->ID) ?: array();
    $show_brands = $acf_brands;
}
// ─────────────────────────────────────────────────────────

include(EZ_CITY_PLUGIN_DIR . 'templates/ez-header.php');
?>

<!-- Schema.org LocalBusiness — wp_head se print hota hai (class-seo-handler.php v5.0) -->
<style>

/* ── v1.5.0 CITY INFO BAR — Light, professional ─── */
.ez-city-bar {
    background: #f0f4f8;
    border-bottom: 2px solid #dbeafe;
    padding: 14px 28px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}
.ez-city-bar-info { display: flex; align-items: center; gap: 20px; flex-wrap: wrap; }
.ez-city-bar-name { font-weight: 800; color: #1d4ed8; font-size: 1.2em; letter-spacing: -0.01em; }
.ez-city-bar-detail { font-size: 0.84em; color: #475569; font-weight: 500; }
.ez-city-bar-btns { display: flex; gap: 10px; }

/* ── BUTTONS ─────────────────────────────────────── */
.ez-btn-wa-sm {
    background: #16a34a; color: #fff !important;
    padding: 8px 18px; border-radius: 6px;
    font-size: 0.84em; font-weight: 700;
    text-decoration: none !important;
    display: inline-flex; align-items: center; gap: 5px;
    transition: background 0.2s;
}
.ez-btn-wa-sm:hover { background: #15803d; }
.ez-btn-ph-sm {
    background: #2563eb; color: #fff !important;
    padding: 8px 18px; border-radius: 6px;
    font-size: 0.84em; font-weight: 700;
    text-decoration: none !important;
    display: inline-flex; align-items: center; gap: 5px;
    transition: background 0.2s;
}
.ez-btn-ph-sm:hover { background: #1d4ed8; }

/* ── v1.5.0 TITLE + INTRO — Clean, no box/border ── */
.ez-title-intro-wrap {
    width: 100%;
    margin: 0 auto;
    padding: 36px 5% 8px;
}
.ez-page-h1 {
    text-align: center;
    font-size: 1.9em;
    color: #111827;
    font-weight: 800;
    margin: 0 0 28px;
    line-height: 1.25;
    letter-spacing: -0.02em;
    max-width: 100%;
}
.ez-intro-box {
    background: transparent;
    border: none;
    padding: 0;
    margin: 0 0 8px;
}
.ez-intro-box p {
    font-size: 1em;
    color: #111827;
    line-height: 1.9;
    margin: 0 0 18px;
    font-weight: 400;
    font-style: normal;
}
.ez-intro-box p:last-child { margin-bottom: 0; }

/* ── PHOTOS ──────────────────────────────────────── */
.ez-photos-section {
    min-height: 380px;
    background: #1a1a2e;
    display: grid;
    grid-template-columns: 1fr 1fr;
    max-height: 380px;
    overflow: hidden;
}
.ez-photos-section.single-photo { grid-template-columns: 1fr; }
.ez-photo-wrap { position: relative; overflow: hidden; }
.ez-photo-wrap img {
    width: 100%; height: 380px; object-fit: cover;
    display: block; transition: transform 0.4s ease;
}
.ez-photo-wrap:hover img { transform: scale(1.03); }
.ez-photo-label {
    position: absolute; bottom: 0; left: 0; right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.75));
    color: #fff; padding: 20px 15px 12px;
    font-size: 0.82em; font-weight: 600;
}
.ez-no-photo-banner {
    background: linear-gradient(135deg,#1a1a2e,#16213e);
    padding: 45px 25px; text-align: center; color: #fff;
}

/* ── BRAND CARDS ─────────────────────────────────── */
.ez-brands-section {
    background: #f0f4f8;
    padding: 40px 25px;
    border-bottom: 2px solid #e2e8f0;
}
.ez-brands-section h2 {
    text-align: center; color: #111827;
    font-size: 1.4em; font-weight: 700; margin-bottom: 6px;
}
.ez-brands-sub {
    text-align: center; color: #64748b;
    font-size: 0.88em; margin-bottom: 30px;
}
.ez-brands-grid {
    max-width: 1100px; margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(155px, 1fr));
    gap: 20px;
}
.ez-brand-card {
    background: #fff; border-radius: 12px;
    overflow: hidden; text-decoration: none !important;
    display: flex; flex-direction: column;
    box-shadow: 0 2px 4px rgba(0,0,0,0.06), 0 4px 12px rgba(0,0,0,0.08);
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.ez-brand-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(0,0,0,0.14);
}
.ez-brand-img {
    width: 100%; aspect-ratio: 1/1;
    background: #f7fafc;
    display: flex; align-items: center; justify-content: center;
    overflow: hidden;
}
.ez-brand-img img {
    width: 100%; height: 100%;
    object-fit: contain; padding: 8px; background: #fff;
}
.ez-brand-img .no-img { font-size: 2.5em; color: #a0aec0; }
.ez-brand-body { padding: 12px 14px 16px; text-align: center; }
.ez-brand-body h3 {
    color: #111827 !important; font-size: 0.88em;
    font-weight: 700; margin: 0 0 8px; line-height: 1.4;
}
.ez-brand-cta { color: #2563eb !important; font-size: 0.8em; font-weight: 700; }

/* ── SERVICE CONTENT ─────────────────────────────── */
.ez-service-content {
    width: 100%;
    margin: 0 auto;
    padding: 20px 5% 30px;
}

/* ── DISCLAIMER ──────────────────────────────────── */
.ez-disclaimer {
    background: #fffbeb;
    border-left: 4px solid #f59e0b;
    border-radius: 0 6px 6px 0;
    padding: 14px 20px;
    margin: 30px auto;
    font-size: 0.82em;
    color: #78350f;
    line-height: 1.6;
    max-width: 100%;
    padding-left: 5%;
    padding-right: 5%;
}
.ez-disclaimer strong { display: block; margin-bottom: 4px; color: #92400e; }

.ez-bottom-section { max-width: 960px; margin: 0 auto; padding: 10px 28px 20px; }

/* ── STICKY WA ───────────────────────────────────── */
.ez-sticky-cta { position: fixed; bottom: 22px; right: 22px; z-index: 9999; }
.ez-sticky-cta a {
    background: #25D366; color: #fff !important;
    width: 56px; height: 56px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6em;
    box-shadow: 0 4px 18px rgba(37,211,102,0.45);
    text-decoration: none !important; transition: all 0.3s;
}
.ez-sticky-cta a:hover { transform: scale(1.12); }

@media(max-width:600px) {
    .ez-photos-section { grid-template-columns: 1fr; max-height: none; }
    .ez-photo-wrap img { height: 220px; }
    .ez-brands-grid { grid-template-columns: repeat(2,1fr); gap: 12px; }
    .ez-city-bar { flex-direction: column; align-items: flex-start; }
    .ez-title-intro-wrap { padding: 28px 20px 8px; }
    .ez-service-content { padding: 16px 20px 24px; }
}
</style>

<!-- BRAND CARDS -->
<?php if (!empty($show_brands)): ?>
<div class="ez-brands-section">
    <h2>🏷️ <?php echo esc_html($service_name); ?> by Brand — <?php echo esc_html($city_name); ?></h2>
    <p class="ez-brands-sub">Select your laptop brand for specialized service</p>
    <div class="ez-brands-grid">
        <?php foreach ($show_brands as $brand_svc):
            $brand_url = home_url('/' . $city_slug . '/' . $brand_svc->post_name . '/');
            $brand_img = get_the_post_thumbnail_url($brand_svc->ID, 'medium');
            $brand_alt = $brand_svc->post_title . ' in ' . $city_name . ' - ' . $center_name;
        ?>
        <a href="<?php echo esc_url($brand_url); ?>"
           class="ez-brand-card"
           title="<?php echo esc_attr($brand_svc->post_title . ' in ' . $city_name); ?>">
            <div class="ez-brand-img">
                <?php if ($brand_img): ?>
                <img src="<?php echo esc_url($brand_img); ?>"
                     alt="<?php echo esc_attr($brand_alt); ?>"
                     loading="lazy">
                <?php else: ?>
                <div class="no-img">🔧</div>
                <?php endif; ?>
            </div>
            <div class="ez-brand-body">
                <h3><?php echo esc_html($brand_svc->post_title); ?></h3>
                <span class="ez-brand-cta">View Service →</span>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<?php
// Content prepare karo pehle
global $post;
$post = $service_post;
setup_postdata($post);

$service_slug = $service_post->post_name;
$local_intro  = Ezonecare_Placeholder::get_local_intro( $city_id, $service_slug );

$raw_content = get_post_field( 'post_content', $service_post->ID );
ob_start();
echo apply_filters( 'the_content', $raw_content );
$rendered = ob_get_clean();
$rendered = Ezonecare_Placeholder::process( $rendered, $city_id, $city_data );
?>

<!-- v1.4.9: PHOTOS PEHLE — Trust signal -->
<?php if ($has_entry || $has_internal): ?>
<div class="ez-photos-section <?php echo (!$has_entry || !$has_internal) ? 'single-photo' : ''; ?>">
    <?php if ($has_entry): ?>
    <div class="ez-photo-wrap">
        <img src="<?php echo esc_url($city_data['photo_entry']); ?>"
             alt="<?php echo esc_attr($alt_entry); ?>"
             width="800" height="380"
             fetchpriority="high"
             loading="eager"
             decoding="async">
        <div class="ez-photo-label">🏪 <?php echo esc_html($center_name); ?> — Entry</div>
    </div>
    <?php endif; ?>
    <?php if ($has_internal): ?>
    <div class="ez-photo-wrap">
        <img src="<?php echo esc_url($city_data['photo_internal']); ?>"
             alt="<?php echo esc_attr($alt_internal); ?>"
             width="800" height="380"
             loading="lazy"
             decoding="async">
        <div class="ez-photo-label">🔧 <?php echo esc_html($service_name); ?> Repair Area</div>
    </div>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="ez-no-photo-banner">
    <h2><?php echo esc_html($service_name); ?> in <?php echo esc_html($city_name); ?></h2>
    <p><?php echo esc_html($center_name); ?></p>
</div>
<?php endif; ?>

<!-- v1.4.9: CITY BAR — Photo ke BAAD, H1 se PEHLE -->
<div class="ez-city-bar">
    <div class="ez-city-bar-info">
        <span class="ez-city-bar-name">📍 <?php echo esc_html($center_name); ?></span>
        <?php if (!empty($city_data['address'])): ?>
        <span class="ez-city-bar-detail">
            <?php echo esc_html(wp_trim_words($city_data['address'], 6)); ?>
            <?php echo !empty($city_data['pincode']) ? ' — ' . esc_html($city_data['pincode']) : ''; ?>
        </span>
        <?php endif; ?>
        <?php if (!empty($city_data['hours'])): ?>
        <span class="ez-city-bar-detail">⏰ <?php echo esc_html($city_data['hours']); ?></span>
        <?php endif; ?>
    </div>
    <div class="ez-city-bar-btns">
        <a href="<?php echo esc_url($wa_link); ?>" class="ez-btn-wa-sm" target="_blank">📱 WhatsApp</a>
        <a href="<?php echo esc_url($ph_link); ?>" class="ez-btn-ph-sm">📞 Call</a>
    </div>
</div>

<!-- H1 + INTRO -->
<div class="ez-title-intro-wrap">
    <h1 class="ez-page-h1">
        <?php echo esc_html($service_name); ?> in <?php echo esc_html($city_name); ?>
    </h1>

    <?php if ( $local_intro ) : ?>
    <div class="ez-intro-box">
        <?php
        $local_intro_processed = Ezonecare_Placeholder::process( $local_intro, $city_id, $city_data );
        echo wp_kses_post( wpautop( $local_intro_processed ) );
        ?>
    </div>
    <?php endif; ?>
</div>

<!-- SERVICE CONTENT -->
<div class="ez-service-content">
    <?php
    if ( $local_intro ) {
        echo Ezonecare_Placeholder::strip_intro_from_content( $rendered );
    } else {
        echo $rendered;
    }
    ?>
    <?php wp_reset_postdata(); ?>

</div><!-- .ez-service-content -->

<?php
// Internal Links
if ( class_exists( 'Ezonecare_Internal_Links' ) && ! empty( $show_brands ) ) {
    // Brand cards already hain — skip
} elseif ( class_exists( 'Ezonecare_Internal_Links' ) ) {
    Ezonecare_Internal_Links::render( $city_id, $service_post, $city_name );
}
?>

<!-- DISCLAIMER — v1.4.9: Inline CTA box HATAYA -->
<div class="ez-bottom-section">
    <div class="ez-disclaimer">
        <strong>⚠️ Disclaimer</strong>
        <?php echo esc_html($center_name); ?> is an independent laptop repair service center and is not affiliated with, authorized by, or endorsed by any laptop manufacturer including Dell, HP, Asus, Lenovo, Apple, Samsung, MSI, Toshiba, or any other brand. All brand names, logos, and trademarks are property of their respective owners. We provide third-party repair services only.
    </div>
</div><!-- .ez-bottom-section -->

<!-- STICKY WA -->
<div class="ez-sticky-cta">
    <a href="<?php echo esc_url($wa_link); ?>" target="_blank"
       title="WhatsApp <?php echo esc_attr($center_name); ?>">📱</a>
</div>

<?php include(EZ_CITY_PLUGIN_DIR . 'templates/ez-footer.php'); ?>
