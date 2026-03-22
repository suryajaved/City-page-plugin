<?php
/**
 * City + Brand Service Page Template
 * URL: /nagpur/dell-laptop-repair/
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
$center_name  = $city_data['center_name'] ?: 'E Zone Care ' . $city_name;

// Photo variables
$has_entry    = !empty($city_data['photo_entry']);
$has_internal = !empty($city_data['photo_internal']);
$alt_entry    = $center_name . ' - ' . $service_post->post_title . ' Service Center Entry - ' . $city_name;
$alt_internal = $center_name . ' - ' . $service_post->post_title . ' Repair Area - ' . $city_name;

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
.ez-city-bar-info {
    display: flex;
    align-items: center;
    gap: 20px;
    flex-wrap: wrap;
}
.ez-city-bar-name {
    font-weight: 800;
    color: #1d4ed8;
    font-size: 1.2em;
    letter-spacing: -0.01em;
}
.ez-city-bar-detail {
    font-size: 0.84em;
    color: #475569;
    font-weight: 500;
}
.ez-city-bar-btns { display: flex; gap: 10px; }

/* ── BUTTONS ─────────────────────────────────────── */
.ez-btn-wa-sm {
    background: #16a34a;
    color: #fff !important;
    padding: 8px 18px;
    border-radius: 6px;
    font-size: 0.84em;
    font-weight: 700;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: background 0.2s;
}
.ez-btn-wa-sm:hover { background: #15803d; }
.ez-btn-ph-sm {
    background: #2563eb;
    color: #fff !important;
    padding: 8px 18px;
    border-radius: 6px;
    font-size: 0.84em;
    font-weight: 700;
    text-decoration: none !important;
    display: inline-flex;
    align-items: center;
    gap: 5px;
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
/* v1.5.0: No box, no border — clean directory style */
.ez-intro-box {
    background: transparent;
    border: none;
    padding: 0;
    margin: 0 0 8px;
}
.ez-intro-box p {
    font-size: 1em;
    color: #111827;        /* Dark black — readable */
    line-height: 1.9;
    margin: 0 0 18px;
    font-weight: 400;
    font-style: normal;    /* No italic */
}
.ez-intro-box p:last-child { margin-bottom: 0; }

/* ── PHOTOS ──────────────────────────────────────── */
.ez-photos-section {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    width: 100%;
    max-height: 420px;
    overflow: hidden;
}
.ez-photos-section.single-photo { grid-template-columns: 1fr; }
.ez-photo-wrap { position: relative; overflow: hidden; }
.ez-photo-wrap img {
    width: 100%;
    height: 380px;
    object-fit: cover;
    display: block;
}
.ez-photo-label {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    background: rgba(0,0,0,0.55);
    color: #fff;
    font-size: 0.78em;
    padding: 6px 12px;
    font-weight: 500;
}
.ez-no-photo-banner {
    background: #1a1a2e;
    color: #fff;
    text-align: center;
    padding: 60px 20px;
}

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
    margin: 30px 28px;
    font-size: 0.82em;
    color: #78350f;
    line-height: 1.6;
    max-width: 100%;
    padding-left: 5%;
    padding-right: 5%;
}
.ez-disclaimer strong {
    display: block;
    margin-bottom: 4px;
    color: #92400e;
}

/* ── STICKY WA ───────────────────────────────────── */
.ez-sticky-cta { position: fixed; bottom: 22px; right: 22px; z-index: 9999; }
.ez-sticky-cta a {
    background: #25D366;
    color: #fff !important;
    width: 56px; height: 56px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6em;
    box-shadow: 0 4px 18px rgba(37,211,102,0.45);
    text-decoration: none !important;
    transition: all 0.3s;
}
.ez-sticky-cta a:hover { transform: scale(1.12); }

@media(max-width:600px) {
    .ez-photos-section { grid-template-columns: 1fr; max-height: none; }
    .ez-photo-wrap img { height: 220px; }
    .ez-city-bar { flex-direction: column; align-items: flex-start; }
    .ez-title-intro-wrap { padding: 28px 20px 8px; }
    .ez-service-content { padding: 16px 20px 24px; }
}
</style>

<?php
// Content pehle prepare karo
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
<div class="ez-photos-section <?php echo (!$has_entry || !$has_internal) ? 'single-photo' : ''; ?>"
     style="min-height:380px; background:#1a1a2e;">
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
        <div class="ez-photo-label">🔧 Repair Area — <?php echo esc_html($city_name); ?></div>
    </div>
    <?php endif; ?>
</div>
<?php else: ?>
<div class="ez-no-photo-banner">
    <h2><?php echo esc_html($service_post->post_title); ?> in <?php echo esc_html($city_name); ?></h2>
    <p><?php echo esc_html($center_name); ?></p>
</div>
<?php endif; ?>

<!-- v1.4.9: CITY BAR — Photo ke BAAD, H1 se PEHLE -->
<div class="ez-city-bar">
    <div class="ez-city-bar-info">
        <span class="ez-city-bar-name">📍 <?php echo esc_html($city_data['center_name'] ?: $city_name); ?></span>
        <?php if (!empty($city_data['address'])): ?>
            <span class="ez-city-bar-detail"><?php echo esc_html(wp_trim_words($city_data['address'], 6)); ?><?php echo !empty($city_data['pincode']) ? ' — ' . esc_html($city_data['pincode']) : ''; ?></span>
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
        <?php echo esc_html( $service_post->post_title ); ?> in <?php echo esc_html( $city_name ); ?>
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
    wp_reset_postdata();
    ?>

    <?php
    // Internal Links — Related brand services
    if ( class_exists( 'Ezonecare_Internal_Links' ) ) {
        Ezonecare_Internal_Links::render( $city_id, $service_post, $city_name );
    }
    ?>

    <!-- DISCLAIMER -->
    <div class="ez-disclaimer">
        <strong>⚠️ Disclaimer</strong>
        <?php echo esc_html($center_name ?? ($city_data['center_name'] ?: 'E Zone Care')); ?> is an independent laptop repair service center and is not affiliated with, authorized by, or endorsed by any laptop manufacturer including Dell, HP, Asus, Lenovo, Apple, Samsung, MSI, Toshiba, or any other brand. All brand names, logos, and trademarks are the property of their respective owners. We provide third-party repair services only.
    </div>

    <!-- v1.4.9: Inline CTA box HATAYA — footer CTA strip kaafi hai -->

</div>

<div class="ez-sticky-cta">
    <a href="<?php echo esc_url($wa_link); ?>" target="_blank">📱</a>
</div>

<?php include(EZ_CITY_PLUGIN_DIR . 'templates/ez-footer.php'); ?>
