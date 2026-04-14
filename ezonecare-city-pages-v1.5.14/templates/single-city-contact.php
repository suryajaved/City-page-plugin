<?php
/**
 * @version 1.5.11
 * City Contact Page Template
 * URL: /ranchi/contact/
 * SEO: Local contact page — city-specific, GMB linked
 */

if (!defined('ABSPATH')) exit;

$query_handler = Ezonecare_Query_Handler::get_instance();
$city_post     = $query_handler->get_city();

if (!$city_post) { wp_redirect(home_url()); exit; }

$city_id   = $city_post->ID;
$city_name = $city_post->post_title;
$city_slug = $city_post->post_name;
$city_data = Ezonecare_City_ACF::get_city_data($city_id);
$wa_link   = Ezonecare_City_ACF::get_whatsapp_link($city_id, $city_name);
$ph_link   = Ezonecare_City_ACF::get_phone_link($city_id);

// Social links
$fb_url    = $city_data['facebook_url']  ?: '';
$insta_url = $city_data['instagram_url'] ?: '';
$gmb_url   = $city_data['gmb_url']       ?: '';
$map_url   = $city_data['map_url']       ?: '';
$est_year  = $city_data['est_year']      ?: '';
$rating    = $city_data['rating']        ?: '';
$years_exp = $est_year ? (date('Y') - intval($est_year)) : '';

include(EZ_CITY_PLUGIN_DIR . 'templates/ez-header.php');
?>

<!-- Schema.org LocalBusiness — wp_head se print hota hai (class-seo-handler.php v5.0) -->
<style>
/* ── CONTACT PAGE STYLES ───────────────────────── */
.ez-contact-page { font-family: inherit; }

/* Top bar */
.ez-cp-topbar {
    background: #1a1a2e;
    color: #fff;
    padding: 12px 20px;
    text-align: center;
    font-size: 0.85em;
}
.ez-cp-topbar a { color: #63b3ed; text-decoration: none; }

/* Page hero */
.ez-cp-hero {
    background: #f7fafc;
    padding: 50px 20px 40px;
    text-align: center;
    border-bottom: 1px solid #e2e8f0;
}
.ez-cp-hero-badge {
    display: inline-block;
    background: #ebf8ff;
    color: #3182ce;
    font-size: 0.75em;
    font-weight: 700;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    padding: 5px 14px;
    border-radius: 50px;
    margin-bottom: 16px;
}
.ez-cp-hero h1 {
    font-size: 2em;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0 0 10px;
    line-height: 1.3;
}
.ez-cp-hero p {
    color: #718096;
    font-size: 1em;
    margin: 0;
}

/* Info cards row */
.ez-cp-info-section {
    padding: 40px 20px;
    background: #fff;
}
.ez-cp-info-grid {
    max-width: 900px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
}
@media(max-width:700px) {
    .ez-cp-info-grid { grid-template-columns: 1fr; }
}
.ez-cp-info-card {
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 28px 24px;
    text-align: center;
    box-shadow: 0 2px 12px rgba(0,0,0,0.05);
    transition: transform 0.2s, box-shadow 0.2s;
}
.ez-cp-info-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,0.1);
}
.ez-cp-info-icon {
    width: 52px; height: 52px;
    border-radius: 12px;
    background: #ebf8ff;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4em;
    margin: 0 auto 14px;
}
.ez-cp-info-label {
    font-size: 0.75em;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    color: #a0aec0;
    margin-bottom: 8px;
}
.ez-cp-info-value {
    font-size: 0.95em;
    font-weight: 600;
    color: #1a1a2e;
    line-height: 1.5;
}
.ez-cp-info-value a {
    color: #3182ce;
    text-decoration: none;
}

/* CTA Buttons */
.ez-cp-cta-section {
    background: #1a1a2e;
    padding: 36px 20px;
    text-align: center;
}
.ez-cp-cta-section h2 {
    color: #fff;
    font-size: 1.3em;
    font-weight: 700;
    margin: 0 0 20px;
}
.ez-cp-cta-btns {
    display: flex;
    gap: 14px;
    justify-content: center;
    flex-wrap: wrap;
}
.ez-cp-btn-wa {
    display: inline-flex; align-items: center; gap: 9px;
    background: #25D366; color: #fff !important;
    padding: 14px 30px; border-radius: 50px;
    font-weight: 700; font-size: 1em;
    text-decoration: none !important;
    transition: all 0.3s;
}
.ez-cp-btn-wa:hover { background: #128C7E; transform: translateY(-2px); }
.ez-cp-btn-call {
    display: inline-flex; align-items: center; gap: 9px;
    background: #3182ce; color: #fff !important;
    padding: 14px 30px; border-radius: 50px;
    font-weight: 700; font-size: 1em;
    text-decoration: none !important;
    transition: all 0.3s;
}
.ez-cp-btn-call:hover { background: #2c5282; transform: translateY(-2px); }

/* Social Links Row */
.ez-cp-social-section {
    background: #f7fafc;
    padding: 36px 20px;
    border-top: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
}
.ez-cp-social-inner {
    max-width: 700px;
    margin: 0 auto;
    text-align: center;
}
.ez-cp-social-inner h3 {
    font-size: 1em;
    font-weight: 700;
    color: #4a5568;
    text-transform: uppercase;
    letter-spacing: 0.06em;
    margin: 0 0 20px;
}
.ez-cp-social-grid {
    display: flex;
    gap: 16px;
    justify-content: center;
    flex-wrap: wrap;
}
.ez-cp-social-card {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    background: #fff;
    border: 1px solid #e2e8f0;
    border-radius: 14px;
    padding: 20px 28px;
    text-decoration: none !important;
    color: #1a1a2e !important;
    font-size: 0.85em;
    font-weight: 600;
    min-width: 110px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    transition: all 0.25s;
}
.ez-cp-social-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 6px 18px rgba(0,0,0,0.1);
}
.ez-cp-social-icon { font-size: 1.6em; }
.ez-cp-social-arrow {
    font-size: 0.75em;
    color: #a0aec0;
}

/* Map + Address Section */
.ez-cp-locate-section {
    padding: 50px 20px;
    background: #fff;
}
.ez-cp-locate-inner {
    max-width: 900px;
    margin: 0 auto;
}
.ez-cp-locate-inner h2 {
    font-size: 1.6em;
    font-weight: 800;
    color: #1a1a2e;
    margin: 0 0 8px;
}
.ez-cp-locate-inner > p {
    color: #718096;
    margin: 0 0 32px;
    font-size: 0.95em;
}
.ez-cp-locate-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 30px;
    align-items: start;
}
@media(max-width:700px) {
    .ez-cp-locate-grid { grid-template-columns: 1fr; }
}

/* Map */
.ez-cp-map-wrap {
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    background: #e2e8f0;
    min-height: 300px;
    display: flex; align-items: center; justify-content: center;
}
.ez-cp-map-wrap iframe {
    width: 100%;
    height: 340px;
    border: none;
    display: block;
}
.ez-cp-map-placeholder {
    text-align: center;
    padding: 40px 20px;
    color: #a0aec0;
}
.ez-cp-map-placeholder span { font-size: 2.5em; display: block; margin-bottom: 10px; }
.ez-cp-map-placeholder p { font-size: 0.85em; margin: 0; }

/* Address detail */
.ez-cp-address-detail {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.ez-cp-addr-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
}
.ez-cp-addr-icon {
    width: 40px; height: 40px;
    background: #ebf8ff;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1em;
    flex-shrink: 0;
}
.ez-cp-addr-label {
    font-size: 0.75em;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: #a0aec0;
    margin-bottom: 4px;
}
.ez-cp-addr-value {
    font-size: 0.92em;
    color: #2d3748;
    font-weight: 600;
    line-height: 1.6;
}
.ez-cp-addr-value a {
    color: #3182ce;
    text-decoration: none;
}

/* GMB Button */
.ez-cp-gmb-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #fff;
    border: 2px solid #3182ce;
    color: #3182ce !important;
    padding: 10px 20px;
    border-radius: 8px;
    font-size: 0.85em;
    font-weight: 700;
    text-decoration: none !important;
    margin-top: 6px;
    transition: all 0.2s;
}
.ez-cp-gmb-btn:hover {
    background: #3182ce;
    color: #fff !important;
}

/* Stats bar */
.ez-cp-stats-section {
    background: #f0f7ff;
    padding: 28px 20px;
    border-top: 1px solid #bee3f8;
}
.ez-cp-stats-grid {
    max-width: 700px;
    margin: 0 auto;
    display: flex;
    gap: 0;
    justify-content: center;
    flex-wrap: wrap;
}
.ez-cp-stat {
    text-align: center;
    padding: 0 30px;
    border-right: 1px solid #bee3f8;
}
.ez-cp-stat:last-child { border-right: none; }
.ez-cp-stat-num {
    font-size: 1.6em;
    font-weight: 800;
    color: #3182ce;
    line-height: 1;
}
.ez-cp-stat-label {
    font-size: 0.75em;
    color: #718096;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    margin-top: 4px;
}
@media(max-width:500px) {
    .ez-cp-stat { padding: 10px 20px; border-right: none; border-bottom: 1px solid #bee3f8; width: 50%; }
    .ez-cp-stat:last-child { border-bottom: none; }
}
</style>

<div class="ez-contact-page">

    <!-- HERO -->
    <div class="ez-cp-hero">
        <div class="ez-cp-hero-badge">GET IN TOUCH</div>
        <h1>Contact Us in <?php echo esc_html($city_name); ?></h1>
        <p>Visit our center or reach us on WhatsApp for quick laptop repair assistance.</p>
    </div>

    <!-- STATS BAR (only if data available) -->
    <?php if ($years_exp || $rating): ?>
    <div class="ez-cp-stats-section">
        <div class="ez-cp-stats-grid">
            <?php if ($est_year): ?>
            <div class="ez-cp-stat">
                <div class="ez-cp-stat-num"><?php echo esc_html($est_year); ?></div>
                <div class="ez-cp-stat-label">Est. Year</div>
            </div>
            <?php endif; ?>
            <?php if ($years_exp): ?>
            <div class="ez-cp-stat">
                <div class="ez-cp-stat-num"><?php echo esc_html($years_exp); ?>+</div>
                <div class="ez-cp-stat-label">Years Exp.</div>
            </div>
            <?php endif; ?>
            <?php if ($rating): ?>
            <div class="ez-cp-stat">
                <div class="ez-cp-stat-num"><?php echo esc_html($rating); ?> ⭐</div>
                <div class="ez-cp-stat-label">Rating</div>
            </div>
            <?php endif; ?>
            <div class="ez-cp-stat">
                <div class="ez-cp-stat-num">500+</div>
                <div class="ez-cp-stat-label">Customers</div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- INFO CARDS -->
    <div class="ez-cp-info-section">
        <div class="ez-cp-info-grid">

            <div class="ez-cp-info-card">
                <div class="ez-cp-info-icon">📍</div>
                <div class="ez-cp-info-label">Our Address</div>
                <div class="ez-cp-info-value">
                    <?php echo nl2br(esc_html($city_data['address'])); ?>
                    <?php if ($city_data['pincode']): ?>
                    <br><?php echo esc_html($city_data['pincode']); ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="ez-cp-info-card">
                <div class="ez-cp-info-icon">📞</div>
                <div class="ez-cp-info-label">Phone Number</div>
                <div class="ez-cp-info-value">
                    <a href="<?php echo esc_url($ph_link); ?>">
                        <?php echo esc_html($city_data['phone']); ?>
                    </a>
                </div>
            </div>

            <div class="ez-cp-info-card">
                <div class="ez-cp-info-icon">🕐</div>
                <div class="ez-cp-info-label">Working Hours</div>
                <div class="ez-cp-info-value">
                    <?php echo esc_html($city_data['hours'] ?: 'Mon-Sat 10 AM – 8 PM'); ?>
                </div>
            </div>

        </div>
    </div>

    <!-- SOCIAL LINKS ROW -->
    <?php if ($fb_url || $insta_url || $gmb_url): ?>
    <div class="ez-cp-social-section">
        <div class="ez-cp-social-inner">
            <h3>Find Us Online</h3>
            <div class="ez-cp-social-grid">

                <?php if ($fb_url): ?>
                <a href="<?php echo esc_url($fb_url); ?>" class="ez-cp-social-card" target="_blank" rel="noopener">
                    <span class="ez-cp-social-icon">📘</span>
                    <span>Facebook</span>
                    <span class="ez-cp-social-arrow">→</span>
                </a>
                <?php endif; ?>

                <?php if ($insta_url): ?>
                <a href="<?php echo esc_url($insta_url); ?>" class="ez-cp-social-card" target="_blank" rel="noopener">
                    <span class="ez-cp-social-icon">📸</span>
                    <span>Instagram</span>
                    <span class="ez-cp-social-arrow">→</span>
                </a>
                <?php endif; ?>

                <?php if ($gmb_url): ?>
                <a href="<?php echo esc_url($gmb_url); ?>" class="ez-cp-social-card" target="_blank" rel="noopener">
                    <span class="ez-cp-social-icon">🗺️</span>
                    <span>Google Map</span>
                    <span class="ez-cp-social-arrow">→</span>
                </a>
                <?php endif; ?>

                <!-- WhatsApp always show -->
                <a href="<?php echo esc_url($wa_link); ?>" class="ez-cp-social-card" target="_blank" rel="noopener">
                    <span class="ez-cp-social-icon">💬</span>
                    <span>WhatsApp</span>
                    <span class="ez-cp-social-arrow">→</span>
                </a>

            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- LOCATE US — MAP + ADDRESS -->
    <div class="ez-cp-locate-section">
        <div class="ez-cp-locate-inner">
            <h2>Visit Our Center</h2>
            <p>We are located in <?php echo esc_html($city_name); ?>. Stop by for a quick diagnosis — no appointment needed.</p>

            <div class="ez-cp-locate-grid">

                <!-- MAP -->
                <div class="ez-cp-map-wrap">
                    <?php if (!empty($map_url)): ?>
                        <iframe
                            src="<?php echo esc_url($map_url); ?>"
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade"
                            title="<?php echo esc_attr($city_data['center_name'] . ' location on Google Maps'); ?>">
                        </iframe>
                    <?php else: ?>
                        <div class="ez-cp-map-placeholder">
                            <span>🗺️</span>
                            <p>Google Map coming soon.<br>Use address below to navigate.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- ADDRESS DETAIL -->
                <div class="ez-cp-address-detail">

                    <div class="ez-cp-addr-item">
                        <div class="ez-cp-addr-icon">📍</div>
                        <div>
                            <div class="ez-cp-addr-label">Address</div>
                            <div class="ez-cp-addr-value">
                                <?php echo nl2br(esc_html($city_data['address'])); ?>
                                <?php if ($city_data['pincode'] && $city_data['state']): ?>
                                <br><?php echo esc_html($city_data['state']); ?> – <?php echo esc_html($city_data['pincode']); ?>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="ez-cp-addr-item">
                        <div class="ez-cp-addr-icon">🕐</div>
                        <div>
                            <div class="ez-cp-addr-label">Operating Hours</div>
                            <div class="ez-cp-addr-value">
                                <?php echo esc_html($city_data['hours'] ?: 'Mon-Sat 10 AM – 8 PM'); ?>
                            </div>
                        </div>
                    </div>

                    <div class="ez-cp-addr-item">
                        <div class="ez-cp-addr-icon">📞</div>
                        <div>
                            <div class="ez-cp-addr-label">Contact</div>
                            <div class="ez-cp-addr-value">
                                <a href="<?php echo esc_url($ph_link); ?>">
                                    <?php echo esc_html($city_data['phone']); ?>
                                </a>
                            </div>
                        </div>
                    </div>

                    <?php if ($gmb_url): ?>
                    <div class="ez-cp-addr-item">
                        <div class="ez-cp-addr-icon">🗺️</div>
                        <div>
                            <div class="ez-cp-addr-label">Google Maps</div>
                            <div class="ez-cp-addr-value">
                                <a href="<?php echo esc_url($gmb_url); ?>" class="ez-cp-gmb-btn" target="_blank" rel="noopener">
                                    🗺️ View on Google Maps
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                </div>
            </div>
        </div>
    </div>

</div>

<?php include(EZ_CITY_PLUGIN_DIR . 'templates/ez-footer.php'); ?>
