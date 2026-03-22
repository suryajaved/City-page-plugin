<?php
/**
 * City Page Template
 * URL: /nagpur/ or /ranchi/
 */

if (!defined('ABSPATH')) exit;

$query_handler = Ezonecare_Query_Handler::get_instance();
$city_post     = $query_handler->get_city();

if (!$city_post) { wp_redirect(home_url()); exit; }

$city_id   = $city_post->ID;
$city_name = $city_post->post_title;
$city_data = Ezonecare_City_ACF::get_city_data($city_id);
$wa_link   = Ezonecare_City_ACF::get_whatsapp_link($city_id, $city_name);
$ph_link   = Ezonecare_City_ACF::get_phone_link($city_id);

// Get services from Custom Meta Box (active services for this city)
$active_service_ids = Ezonecare_City_Meta_Box::get_active_service_ids($city_id);

if (!empty($active_service_ids)) {
    $services = get_posts(array(
        'post_type'      => 'service',
        'post_status'    => 'publish',
        'post__in'       => $active_service_ids,
        'orderby'        => 'post__in',
        'posts_per_page' => 50,
    ));
} else {
    // Fallback: show all published services
    $services = get_posts(array(
        'post_type'      => 'service',
        'post_status'    => 'publish',
        'posts_per_page' => 10,
        'orderby'        => 'menu_order',
        'order'          => 'ASC',
    ));
}

$city_slug = $city_post->post_name;

include(EZ_CITY_PLUGIN_DIR . 'templates/ez-header.php');

?>

<!-- Schema.org LocalBusiness — wp_head ke through print hota hai (class-seo-handler.php v4.0) -->

<style>
.ez-city-hero {
    position: relative;
    min-height: 400px;
    background: #1a1a2e;
    display: flex;
    align-items: center;
    overflow: hidden;
}
.ez-city-hero-photos {
    position: absolute;
    inset: 0;
    display: flex;
}
.ez-city-hero-photos {
    /* CLS Fix — height define karo taaki page shift na ho */
    min-height: 350px;
    background: #1a1a2e; /* placeholder color jab tak image load ho */
}
.ez-city-hero-photos img {
    width: 50%;
    height: 100%;
    object-fit: cover;
    opacity: 1;
}
.ez-city-hero-overlay {
    position: absolute;
    inset: 0;
    background: linear-gradient(135deg, rgba(0,0,0,0.35) 0%, rgba(0,0,0,0.25) 100%);
}
.ez-city-hero-content {
    position: relative;
    z-index: 2;
    text-align: center;
    color: #fff;
    padding: 60px 20px;
    width: 100%;
}
.ez-city-hero-content h1 {
    font-size: 2.2em;
    font-weight: 700;
    margin-bottom: 10px;
    color: #fff;
}
.ez-city-hero-content .tagline {
    font-size: 1.1em;
    color: #a0aec0;
    margin-bottom: 25px;
}
.ez-btn-whatsapp {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #25D366;
    color: #fff !important;
    padding: 14px 28px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1em;
    text-decoration: none !important;
    margin: 5px;
    transition: all 0.3s;
}
.ez-btn-whatsapp:hover { background: #128C7E; transform: translateY(-2px); }
.ez-btn-phone {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #3182ce;
    color: #fff !important;
    padding: 14px 28px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1em;
    text-decoration: none !important;
    margin: 5px;
    transition: all 0.3s;
}
.ez-btn-phone:hover { background: #2c5282; transform: translateY(-2px); }

/* ── SERVICE CARDS — 3D Style ──────────────── */
.ez-services-section {
    background: #0f1923;
    padding: 60px 20px 70px;
}
.ez-services-section h2 {
    text-align: center;
    color: #fff;
    font-size: 1.9em;
    font-weight: 700;
    margin-bottom: 10px;
}
.ez-services-section .ez-section-sub {
    text-align: center;
    color: #718096;
    font-size: 0.95em;
    margin-bottom: 45px;
}
.ez-services-grid {
    max-width: 1150px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: 28px;
}

/* Card — Full clickable 3D box */
.ez-service-card {
    background: #fff;
    border-radius: 14px;
    overflow: hidden;
    text-decoration: none !important;
    display: flex;
    flex-direction: column;
    cursor: pointer;
    /* 3D shadow effect */
    box-shadow:
        0 1px 2px rgba(0,0,0,0.07),
        0 4px 8px rgba(0,0,0,0.10),
        0 8px 16px rgba(0,0,0,0.08),
        0 0 0 1px rgba(0,0,0,0.04);
    transition: transform 0.28s cubic-bezier(.22,.68,0,1.2),
                box-shadow 0.28s ease;
    position: relative;
}
.ez-service-card:hover {
    transform: translateY(-8px) scale(1.02);
    box-shadow:
        0 2px 4px rgba(0,0,0,0.08),
        0 8px 20px rgba(0,0,0,0.15),
        0 20px 40px rgba(0,0,0,0.12),
        0 0 0 1px rgba(66,153,225,0.3);
    text-decoration: none !important;
}

/* Card Image */
.ez-card-image {
    width: 100%;
    height: 175px;
    overflow: hidden;
    background: #e2e8f0;
    position: relative;
}
.ez-card-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.4s ease;
}
.ez-service-card:hover .ez-card-image img {
    transform: scale(1.06);
}
.ez-card-image .no-img {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #1a1a2e 0%, #2d3748 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3.5em;
}

/* Card Body */
.ez-card-body {
    padding: 18px 20px 22px;
    display: flex;
    flex-direction: column;
    flex: 1;
}
.ez-card-body h3 {
    color: #1a1a2e !important;
    font-size: 1em;
    font-weight: 700;
    margin: 0 0 10px;
    line-height: 1.4;
}
.ez-card-body p {
    color: #718096;
    font-size: 0.84em;
    line-height: 1.6;
    margin: 0 0 auto;
    flex: 1;
}
.ez-card-cta {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    margin-top: 15px;
    color: #3182ce !important;
    font-size: 0.85em;
    font-weight: 700;
    text-decoration: none !important;
    transition: gap 0.2s;
}
.ez-service-card:hover .ez-card-cta {
    gap: 9px;
}

/* Top accent line on hover */
.ez-service-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #3182ce, #63b3ed);
    transform: scaleX(0);
    transition: transform 0.3s ease;
    transform-origin: left;
}
.ez-service-card:hover::before {
    transform: scaleX(1);
}

@media(max-width: 600px) {
    .ez-services-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    .ez-card-image { height: 130px; }
}
@media(max-width: 380px) {
    .ez-services-grid { grid-template-columns: 1fr; }
}


/* ── CONTACT SECTION ─────────────────────── */
.ez-contact-section {
    background: linear-gradient(135deg, #f0f4ff 0%, #fafbff 100%);
    padding: 40px 20px;
    border-bottom: 1px solid #e2e8f0;
}
.ez-contact-card {
    max-width: 860px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}
@media(max-width:600px){
    .ez-contact-card {
        grid-template-columns: 1fr;
        gap: 12px;
    }
}
.ez-contact-item {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    background: #fff;
    border-radius: 16px;
    padding: 20px 22px;
    box-shadow:
        0 1px 0 rgba(255,255,255,0.9) inset,
        0 4px 6px -1px rgba(0,0,0,0.07),
        0 10px 25px -5px rgba(0,0,0,0.08),
        0 0 0 1px rgba(0,0,0,0.04);
    transition: transform 0.22s cubic-bezier(.22,.68,0,1.2), box-shadow 0.22s ease;
    position: relative;
    overflow: hidden;
}
.ez-contact-item::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #3b82f6, #6366f1);
    border-radius: 16px 16px 0 0;
    opacity: 0;
    transition: opacity 0.2s;
}
.ez-contact-item:hover {
    transform: translateY(-4px) scale(1.01);
    box-shadow:
        0 1px 0 rgba(255,255,255,0.9) inset,
        0 8px 16px -4px rgba(0,0,0,0.10),
        0 20px 40px -8px rgba(59,130,246,0.12),
        0 0 0 1px rgba(59,130,246,0.15);
}
.ez-contact-item:hover::before { opacity: 1; }
.ez-contact-icon {
    width: 44px; height: 44px;
    background: linear-gradient(135deg, #eff6ff 0%, #e0e7ff 100%);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3em; flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(59,130,246,0.15);
}
.ez-contact-label {
    font-size: 0.7em;
    color: #94a3b8;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-weight: 600;
}
.ez-contact-value {
    font-weight: 700;
    color: #1e293b;
    font-size: 0.95em;
    line-height: 1.5;
}
.ez-contact-value a {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 700;
}

/* ── CITY SEO INTRO — Option D: Dark/Navy Highlight Card ── */
.ez-city-intro-section {
    background: linear-gradient(160deg, #0f172a 0%, #1e293b 100%);
    padding: 52px 20px;
    position: relative;
    overflow: hidden;
}
.ez-city-intro-section::before {
    content: '';
    position: absolute;
    top: -80px; right: -80px;
    width: 320px; height: 320px;
    background: radial-gradient(circle, rgba(59,130,246,0.12) 0%, transparent 70%);
    pointer-events: none;
}
.ez-city-intro-section::after {
    content: '';
    position: absolute;
    bottom: -60px; left: -60px;
    width: 240px; height: 240px;
    background: radial-gradient(circle, rgba(99,102,241,0.10) 0%, transparent 70%);
    pointer-events: none;
}
.ez-city-intro-block {
    max-width: 900px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}

/* Top — label + headline */
.ez-city-intro-label {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(59,130,246,0.15);
    border: 1px solid rgba(59,130,246,0.3);
    border-radius: 50px;
    padding: 5px 14px;
    font-size: 0.72em;
    font-weight: 700;
    color: #60a5fa;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin-bottom: 18px;
}
.ez-city-intro-h2 {
    font-size: 1.7em;
    font-weight: 800;
    color: #f1f5f9;
    margin: 0 0 8px;
    line-height: 1.3;
    letter-spacing: -0.02em;
}
.ez-city-intro-h2 .ez-intro-highlight {
    background: linear-gradient(90deg, #3b82f6, #818cf8);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

/* Middle — paragraph */
.ez-city-intro-para {
    font-size: 0.93em;
    color: #94a3b8;
    line-height: 1.9;
    margin: 0 0 32px;
    max-width: 720px;
    /* Bold key phrases */
}
.ez-city-intro-para strong {
    color: #cbd5e1;
    font-weight: 600;
}

/* Bottom — icon strip (3 highlights) */
.ez-city-intro-strips {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}
.ez-city-intro-strip-item {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 12px;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: background 0.2s, border-color 0.2s;
}
.ez-city-intro-strip-item:hover {
    background: rgba(59,130,246,0.08);
    border-color: rgba(59,130,246,0.25);
}
.ez-city-strip-icon {
    width: 38px; height: 38px;
    background: linear-gradient(135deg, #1e3a5f, #1e3058);
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1em;
    flex-shrink: 0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.3);
}
.ez-city-strip-text strong {
    display: block;
    font-size: 0.82em;
    font-weight: 700;
    color: #e2e8f0;
    margin-bottom: 2px;
}
.ez-city-strip-text span {
    font-size: 0.73em;
    color: #64748b;
}
@media (max-width: 700px) {
    .ez-city-intro-h2 { font-size: 1.3em; }
    .ez-city-intro-strips { grid-template-columns: 1fr; gap: 10px; }
    .ez-city-intro-section { padding: 36px 16px; }
}

.ez-disclaimer {
    background: #fffbeb;
    border-left: 4px solid #f6ad55;
    border-radius: 0 8px 8px 0;
    padding: 16px 20px;
    margin: 30px 25px;
    font-size: 0.83em;
    color: #744210;
    line-height: 1.6;
}
.ez-disclaimer strong {
    display: block;
    margin-bottom: 4px;
    color: #92400e;
}

/* Map Section */
.ez-map-section { padding: 0; }
.ez-map-section iframe {
    width: 100%;
    height: 350px;
    border: 0;
    display: block;
}
</style>

<!-- HERO SECTION -->
<div class="ez-city-hero">
    <?php if (!empty($city_data['photo_entry']) || !empty($city_data['photo_internal'])): ?>
    <div class="ez-city-hero-photos">
        <?php if (!empty($city_data['photo_entry'])): ?>
            <img src="<?php echo esc_url($city_data['photo_entry']); ?>"
                 alt="<?php echo esc_attr($city_data['center_name']); ?> Entry"
                 width="800" height="450"
                 fetchpriority="high"
                 loading="eager"
                 decoding="async">
        <?php endif; ?>
        <?php if (!empty($city_data['photo_internal'])): ?>
            <img src="<?php echo esc_url($city_data['photo_internal']); ?>"
                 alt="<?php echo esc_attr($city_data['center_name']); ?> Service Area"
                 width="800" height="450"
                 loading="lazy"
                 decoding="async">
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="ez-city-hero-overlay"></div>
    <div class="ez-city-hero-content">
        <h1>Laptop Repair Services in <?php echo esc_html($city_name); ?></h1>
        <?php if (!empty($city_data['tagline'])): ?>
            <p class="tagline"><?php echo esc_html($city_data['tagline']); ?></p>
        <?php else: ?>
            <p class="tagline"><?php echo esc_html($city_data['center_name']); ?> — Trusted Laptop Repair Center</p>
        <?php endif; ?>
        <div>
            <a href="<?php echo esc_url($wa_link); ?>" class="ez-btn-whatsapp" target="_blank">
                📱 WhatsApp Us
            </a>
            <a href="<?php echo esc_url($ph_link); ?>" class="ez-btn-phone">
                📞 Call Now
            </a>
        </div>
    </div>
</div>

<!-- CONTACT DETAILS -->
<div class="ez-contact-section">
    <div class="ez-contact-card">
        <?php if (!empty($city_data['center_name'])): ?>
        <div class="ez-contact-item">
            <div class="ez-contact-icon">🏪</div>
            <div>
                <div class="ez-contact-label">Service Center</div>
                <div class="ez-contact-value"><?php echo esc_html($city_data['center_name']); ?></div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($city_data['address'])): ?>
        <div class="ez-contact-item">
            <div class="ez-contact-icon">📍</div>
            <div>
                <div class="ez-contact-label">Address</div>
                <div class="ez-contact-value">
                    <?php echo nl2br(esc_html($city_data['address'])); ?>
                    <?php if (!empty($city_data['pincode'])): ?>
                        - <?php echo esc_html($city_data['pincode']); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($city_data['phone'])): ?>
        <div class="ez-contact-item">
            <div class="ez-contact-icon">📞</div>
            <div>
                <div class="ez-contact-label">Phone</div>
                <div class="ez-contact-value">
                    <a href="<?php echo esc_url($ph_link); ?>"><?php echo esc_html($city_data['phone']); ?></a>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($city_data['hours'])): ?>
        <div class="ez-contact-item">
            <div class="ez-contact-icon">⏰</div>
            <div>
                <div class="ez-contact-label">Working Hours</div>
                <div class="ez-contact-value"><?php echo esc_html($city_data['hours']); ?></div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- CITY SEO INTRO — Option D: Dark/Navy Highlight Card -->
<?php
$intro_headline  = get_field('city_intro_headline', $city_id);
$intro_text_raw  = get_field('city_intro_text', $city_id);

// v1.5.1 FIX: Placeholder process karo intro_text mein
$intro_text = !empty($intro_text_raw)
    ? Ezonecare_Placeholder::process( $intro_text_raw, $city_id, $city_data )
    : '';

// v1.5.1 FIX: Stats — sahi ACF fields se lo, koi fake default nahi
$since_year   = ! empty( $city_data['est_year'] )     ? $city_data['est_year']     : '';
$repair_count = ! empty( $city_data['repair_count'] )  ? $city_data['repair_count'] : '';
$rating       = ! empty( $city_data['rating'] )        ? $city_data['rating']       : '';

if (!empty($intro_headline) || !empty($intro_text)):
?>
<div class="ez-city-intro-section">
    <div class="ez-city-intro-block">

        <!-- Label -->
        <div class="ez-city-intro-label">
            🏆 About Our <?php echo esc_html($city_name); ?> Center
        </div>

        <!-- Headline — comma se pehle highlight -->
        <?php if (!empty($intro_headline)): ?>
        <h2 class="ez-city-intro-h2">
            <?php
            $lines = explode(',', $intro_headline, 2);
            if (count($lines) === 2) {
                echo '<span class="ez-intro-highlight">' . esc_html(trim($lines[0])) . ',</span> ' . esc_html(trim($lines[1]));
            } else {
                echo esc_html($intro_headline);
            }
            ?>
        </h2>
        <?php endif; ?>

        <!-- Paragraph — key phrases auto-bold -->
        <?php if (!empty($intro_text)): ?>
        <div class="ez-city-intro-para">
            <?php
            $text = esc_html($intro_text);
            $bold_phrases = array(
                'chip-level', 'Chip-Level', 'chip level',
                'certified', 'Certified',
                'genuine parts', 'Genuine Parts',
                'fast turnaround', 'Fast Turnaround',
                'affordable', 'Affordable',
                'pickup and drop', 'Pickup and drop', 'Pickup & Drop',
                'chip level repairs', 'Chip Level Repairs',
            );
            foreach ($bold_phrases as $phrase) {
                $text = str_replace($phrase, '<strong>' . $phrase . '</strong>', $text);
            }
            echo nl2br(wp_kses($text, array('strong' => array(), 'br' => array())));
            ?>
        </div>
        <?php endif; ?>

        <!-- Icon Strip — 3 highlights — sirf tab show karo jab data ho -->
        <?php
        $show_repair = !empty($repair_count);
        $show_since  = !empty($since_year);
        $show_rating = !empty($rating);
        if ($show_repair || $show_since || $show_rating):
        ?>
        <div class="ez-city-intro-strips">
            <?php if ($show_repair): ?>
            <div class="ez-city-intro-strip-item">
                <div class="ez-city-strip-icon">🛠️</div>
                <div class="ez-city-strip-text">
                    <strong><?php echo esc_html($repair_count); ?> Repairs</strong>
                    <span>Successfully completed</span>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($show_since): ?>
            <div class="ez-city-intro-strip-item">
                <div class="ez-city-strip-icon">📅</div>
                <div class="ez-city-strip-text">
                    <strong>Since <?php echo esc_html($since_year); ?></strong>
                    <span>Serving <?php echo esc_html($city_name); ?></span>
                </div>
            </div>
            <?php endif; ?>
            <?php if ($show_rating): ?>
            <div class="ez-city-intro-strip-item">
                <div class="ez-city-strip-icon">⭐</div>
                <div class="ez-city-strip-text">
                    <strong><?php echo esc_html($rating); ?> Rating</strong>
                    <span>Customer satisfaction</span>
                </div>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>
</div>
<?php endif; ?>

<!-- SERVICES GRID -->
<div class="ez-services-section">
    <h2>Our Laptop Repair Services in <?php echo esc_html($city_name); ?></h2>
    <p class="ez-section-sub">
        Click any service to book at <?php echo esc_html($city_data['center_name'] ?: $city_name); ?>
    </p>
    <div class="ez-services-grid">
        <?php foreach ($services as $service):
            $service_url = home_url('/' . $city_slug . '/' . $service->post_name . '/');
            $thumb       = get_the_post_thumbnail_url($service->ID, 'large');
            $excerpt     = wp_trim_words(
                $service->post_excerpt ?: wp_strip_all_tags($service->post_content),
                18
            );
            // v1.4.7 FIX: excerpt mein bhi {city} {center} etc. replace karo
            $excerpt     = Ezonecare_Placeholder::process( $excerpt, $city_id, $city_data );
            $alt_text    = $service->post_title . ' in ' . $city_name . ' - ' . ($city_data['center_name'] ?: 'E Zone Care');
        ?>
        <a href="<?php echo esc_url($service_url); ?>"
           class="ez-service-card"
           title="<?php echo esc_attr($service->post_title . ' in ' . $city_name); ?>">

            <!-- Image -->
            <div class="ez-card-image">
                <?php if ($thumb): ?>
                    <img src="<?php echo esc_url($thumb); ?>"
                         alt="<?php echo esc_attr($alt_text); ?>"
                         loading="lazy">
                <?php else: ?>
                    <div class="no-img">🔧</div>
                <?php endif; ?>
            </div>

            <!-- Body -->
            <div class="ez-card-body">
                <h3><?php echo esc_html($service->post_title); ?></h3>
                <?php if ($excerpt): ?>
                    <p><?php echo esc_html($excerpt); ?></p>
                <?php endif; ?>
                <span class="ez-card-cta">Know More →</span>
            </div>

        </a>
        <?php endforeach; ?>
    </div>
</div>


    <!-- DISCLAIMER -->
    <div class="ez-disclaimer">
        <strong>⚠️ Disclaimer</strong>
        <?php echo esc_html($city_data['center_name'] ?: 'E Zone Care'); ?> is an independent laptop repair service center and is not affiliated with, authorized by, or endorsed by any laptop manufacturer including Dell, HP, Asus, Lenovo, Apple, Samsung, MSI, Toshiba, or any other brand. All brand names, logos, and trademarks are the property of their respective owners. We provide third-party repair services only.
    </div>

<!-- WHY CHOOSE US — v1.5.1 -->
<?php
$why_choose_raw = get_field( 'city_why_choose', $city_id );

// Default fallback — placeholders ke saath, automatic city-specific ban jaata hai
$why_choose_default = '{center} has been serving {city} since ' . ( ! empty( $city_data['est_year'] ) ? $city_data['est_year'] : 'many years' ) . ', building a strong reputation for honest and dependable electronics repair. '
    . 'Our workshop at {area}, near {landmark}, specialises in laptop chip-level repair, LCD and LED television servicing, and TV panel bonding — a combination that very few service centres in {state} can offer under one roof. '
    . 'Every repair is backed by a service warranty, and we use quality spare parts sourced from trusted suppliers. '
    . 'With a quick turnaround of mostly one working day, a city-wide pickup and drop facility, and a team of trained technicians who have handled over thousands of devices, {center} remains {city} most trusted choice for all your repair needs. '
    . 'Call or WhatsApp us on {phone} — we are open Monday to Saturday and happy to help.';

// ACF filled = use AI content, empty = use default fallback
$why_choose_content = ! empty( $why_choose_raw ) ? $why_choose_raw : $why_choose_default;
$why_choose = Ezonecare_Placeholder::process( $why_choose_content, $city_id, $city_data );
$is_default = empty( $why_choose_raw ); // track karo — admin badge ke liye
if ( true ) : // Always show — default ya custom dono mein
?>
<div class="ez-why-choose-section">
    <div class="ez-why-choose-inner">
        <div class="ez-why-choose-label">✅ Why Choose Us</div>
        <h2 class="ez-why-choose-h2">Why <?php echo esc_html( $city_name ); ?> Customers Trust <?php echo esc_html( $city_data['center_name'] ?: $city_name ); ?></h2>
        <div class="ez-why-choose-text">
            <?php
            $wc_text = esc_html( $why_choose );
            $bold_phrases = array(
                'chip-level', 'Chip-Level', 'chip level',
                'one-month', 'One-Month', '1 month',
                'genuine parts', 'Genuine Parts',
                'pickup and drop', 'Pickup and drop', 'Pickup & Drop',
                'panel bonding', 'Panel Bonding',
                'turnaround', 'Turnaround',
                'one working day', 'One Working Day',
            );
            foreach ( $bold_phrases as $phrase ) {
                $wc_text = str_replace( $phrase, '<strong>' . $phrase . '</strong>', $wc_text );
            }
            echo nl2br( wp_kses( $wc_text, array( 'strong' => array(), 'br' => array() ) ) );
            ?>
        </div>
    </div>
</div>
<style>
.ez-why-choose-section {
    background: #f8faff;
    border-top: 1px solid #dbeafe;
    border-bottom: 1px solid #dbeafe;
    padding: 48px 28px;
}
.ez-why-choose-inner {
    max-width: 860px;
    margin: 0 auto;
}
.ez-why-choose-label {
    display: inline-block;
    background: #1d4ed8;
    color: #fff;
    font-size: 0.78em;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    padding: 4px 14px;
    border-radius: 20px;
    margin-bottom: 16px;
}
.ez-why-choose-h2 {
    font-size: 1.45em;
    font-weight: 800;
    color: #111827;
    margin: 0 0 18px;
    line-height: 1.3;
}
.ez-why-choose-text {
    font-size: 1em;
    color: #1e3a5f;
    line-height: 1.9;
    font-weight: 400;
}
.ez-why-choose-text strong {
    color: #1d4ed8;
    font-weight: 700;
}
@media(max-width:600px) {
    .ez-why-choose-section { padding: 36px 20px; }
    .ez-why-choose-h2 { font-size: 1.25em; }
}
</style>
<?php endif; ?>

<!-- GOOGLE MAP -->
<?php if (!empty($city_data['map_url'])): ?>
<div class="ez-map-section">
    <?php $raw_map_url = $city_data['map_url']; ?>

    <!-- MAP LAZY LOAD — Click to Load pattern
         Google Maps JS (378 KiB) sirf tab load hoti hai
         jab user "Show Map" button click kare
         Performance saving: ~200ms + 378 KiB JS -->
    <div class="ez-map-wrapper" id="ez-map-wrapper">

        <!-- Placeholder — map load hone se pehle -->
        <div class="ez-map-placeholder" id="ez-map-placeholder">
            <div class="ez-map-ph-content">
                <span class="ez-map-pin">📍</span>
                <p class="ez-map-center-name"><?php echo esc_html($city_data['center_name'] ?: $city_name); ?></p>
                <p class="ez-map-address"><?php echo esc_html(wp_trim_words($city_data['address'] ?? '', 8)); ?></p>
                <button class="ez-map-load-btn"
                        onclick="ezLoadMap('<?php echo esc_js($raw_map_url); ?>')"
                        aria-label="Load Google Map">
                    🗺️ Show on Map
                </button>
            </div>
        </div>

        <!-- Actual iframe — JS se inject hoga -->
        <div id="ez-map-frame" style="display:none; width:100%; height:350px;"></div>

    </div>

    <style>
    .ez-map-wrapper { width: 100%; }
    .ez-map-placeholder {
        width: 100%;
        height: 350px;
        background: linear-gradient(135deg, #1a1a2e, #16213e);
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0;
    }
    .ez-map-ph-content {
        text-align: center;
        color: #fff;
        padding: 20px;
    }
    .ez-map-pin { font-size: 2.5em; display: block; margin-bottom: 8px; }
    .ez-map-center-name {
        font-size: 1.1em;
        font-weight: 700;
        color: #63b3ed;
        margin: 0 0 6px;
    }
    .ez-map-address {
        font-size: 0.85em;
        color: #a0aec0;
        margin: 0 0 16px;
    }
    .ez-map-load-btn {
        background: #3182ce;
        color: #fff;
        border: none;
        padding: 10px 24px;
        border-radius: 25px;
        font-size: 0.9em;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.2s;
    }
    .ez-map-load-btn:hover { background: #2c5282; }
    #ez-map-frame iframe {
        width: 100%;
        height: 350px;
        border: 0;
    }
    </style>

    <script>
    function ezLoadMap(mapUrl) {
        var placeholder = document.getElementById('ez-map-placeholder');
        var frameDiv    = document.getElementById('ez-map-frame');
        if (!placeholder || !frameDiv) return;

        // Iframe inject karo
        frameDiv.innerHTML = '<iframe src="' + mapUrl + '" width="100%" height="350" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade" title="Location Map"></iframe>';

        // Placeholder hide, map show
        placeholder.style.display = 'none';
        frameDiv.style.display    = 'block';
    }
    </script>
</div>
<?php endif; ?>

<?php include(EZ_CITY_PLUGIN_DIR . 'templates/ez-footer.php'); ?>
