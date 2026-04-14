<?php
/**
 * @version 1.5.11
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

/* ── SERVICE CARDS — Light background ─────── */
/* v1.5.7: Dark (#0f1923) → Light (#f8fafc) */
.ez-services-section {
    background: #f8fafc;
    padding: 60px 20px 70px;
    border-top: 1px solid #e2e8f0;
}
.ez-services-section h2 {
    text-align: center;
    color: #111827;
    font-size: 1.9em;
    font-weight: 700;
    margin-bottom: 10px;
}
.ez-services-section .ez-section-sub {
    text-align: center;
    color: #64748b;
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


/* ── CONTACT INFO BAR — compact strip, hero ke neeche ── */
/* v1.5.7: Alag bade cards hataye — ab full-width inline bar */
.ez-contact-bar {
    background: #ffffff;
    border-bottom: 1px solid #e8edf5;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
.ez-contact-bar-inner {
    max-width: 1100px;
    margin: 0 auto;
    display: flex;
    align-items: stretch;
    flex-wrap: wrap;
}
.ez-cb-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 18px 24px;
    border-right: 1px solid #f1f5f9;
    flex: 1;
    min-width: 200px;
    transition: background 0.2s;
}
.ez-cb-item:hover { background: #f8faff; }
.ez-cb-item:last-child { border-right: none; }
.ez-cb-icon {
    font-size: 1.2em;
    flex-shrink: 0;
    width: 38px;
    height: 38px;
    background: #eff6ff;
    border-radius: 9px;
    display: flex;
    align-items: center;
    justify-content: center;
}
.ez-cb-label {
    display: block;
    font-size: 0.66em;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    font-weight: 600;
    margin-bottom: 2px;
}
.ez-cb-value {
    display: block;
    font-size: 0.86em;
    font-weight: 700;
    color: #1e293b;
    line-height: 1.4;
}
.ez-cb-value a {
    color: #3b82f6;
    text-decoration: none;
    font-weight: 700;
}
@media(max-width:768px) {
    .ez-contact-bar-inner { flex-direction: column; }
    .ez-cb-item { border-right: none; border-bottom: 1px solid #f1f5f9; padding: 12px 18px; }
    .ez-cb-item:last-child { border-bottom: none; }
}

/* ── BRANDS WE SERVICE SECTION ───────────── */
/* v1.5.6: city_brand_logos gallery field se dynamic */
.ez-brands-section {
    background: #ffffff;
    padding: 56px 20px;
    border-bottom: 1px solid #f1f5f9;
}
.ez-brands-inner {
    max-width: 960px;
    margin: 0 auto;
    text-align: center;
}
.ez-brands-section h2 {
    font-size: 1.6em;
    font-weight: 800;
    color: #111827;
    margin: 0 0 10px;
}
.ez-brands-section .ez-brands-sub {
    color: #64748b;
    font-size: 0.92em;
    margin: 0 0 40px;
}
.ez-brands-grid {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;
    gap: 20px;
}
.ez-brand-card {
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    width: 130px;
    height: 80px;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 14px;
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.2s;
}
.ez-brand-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 4px 16px rgba(59,130,246,0.12);
    transform: translateY(-3px);
}
.ez-brand-card img {
    max-width: 100%;
    max-height: 50px;
    object-fit: contain;
    filter: grayscale(30%);
    transition: filter 0.2s;
}
.ez-brand-card:hover img {
    filter: grayscale(0%);
}
@media(max-width:600px) {
    .ez-brand-card { width: 100px; height: 64px; }
}

/* ── PICKUP & DROP SECTION ───────────────── */
/* v1.5.6: city_pickup_available toggle se show/hide */
.ez-pickup-section {
    background: linear-gradient(135deg, #1e3a5f 0%, #1a2e4a 100%);
    padding: 60px 20px;
}
.ez-pickup-inner {
    max-width: 960px;
    margin: 0 auto;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 48px;
    align-items: center;
}
@media(max-width:700px) {
    .ez-pickup-inner {
        grid-template-columns: 1fr;
        gap: 32px;
        text-align: center;
    }
    .ez-pickup-svg { order: -1; }
}
.ez-pickup-content h2 {
    font-size: 2em;
    font-weight: 800;
    color: #ffffff;
    margin: 0 0 16px;
    line-height: 1.2;
}
.ez-pickup-content h2 span {
    color: #60a5fa;
}
.ez-pickup-content p {
    color: #94a3b8;
    font-size: 0.97em;
    line-height: 1.8;
    margin: 0 0 28px;
}
.ez-pickup-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: #25D366;
    color: #fff !important;
    padding: 14px 30px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 1em;
    text-decoration: none !important;
    transition: all 0.3s;
    box-shadow: 0 4px 16px rgba(37,211,102,0.3);
}
.ez-pickup-btn:hover {
    background: #128C7E;
    transform: translateY(-2px);
    box-shadow: 0 6px 20px rgba(37,211,102,0.4);
}
.ez-pickup-svg {
    display: flex;
    align-items: center;
    justify-content: center;
}

/* ── CITY SEO INTRO — Light Clean Style ── */
/* v1.5.10: Width fix + professional padding */
.ez-city-intro-section {
    background: #ffffff;
    padding: 56px 40px;
    border-top: 1px solid #e8edf5;
    border-bottom: 1px solid #e8edf5;
}
.ez-city-intro-section::before { display: none; }
.ez-city-intro-section::after  { display: none; }
@media(max-width:768px) {
    .ez-city-intro-section { padding: 40px 20px; }
}
.ez-city-intro-block {
    max-width: 1050px;
    margin: 0 auto;
    position: relative;
    z-index: 1;
}
.ez-city-intro-label {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    border-radius: 50px;
    padding: 5px 14px;
    font-size: 0.72em;
    font-weight: 700;
    color: #2563eb;
    letter-spacing: 0.06em;
    text-transform: uppercase;
    margin-bottom: 18px;
}
.ez-city-intro-h2 {
    font-size: 1.7em;
    font-weight: 800;
    color: #111827;
    margin: 0 0 8px;
    line-height: 1.3;
    letter-spacing: -0.02em;
}
.ez-city-intro-h2 .ez-intro-highlight {
    background: linear-gradient(90deg, #2563eb, #4f46e5);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
.ez-city-intro-para {
    font-size: 0.95em;
    color: #374151;
    line-height: 1.9;
    margin: 0 0 32px;
    max-width: 100%;
}
.ez-city-intro-para strong { color: #1d4ed8; font-weight: 600; }
.ez-city-intro-para a { color: #2563eb; text-decoration: underline; }
.ez-city-intro-strips {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 14px;
}
.ez-city-intro-strip-item {
    background: #f8faff;
    border: 1px solid #e0eaff;
    border-radius: 12px;
    padding: 16px 18px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: background 0.2s, border-color 0.2s;
}
.ez-city-intro-strip-item:hover {
    background: #eff6ff;
    border-color: #bfdbfe;
}
.ez-city-strip-icon {
    width: 38px; height: 38px;
    background: #eff6ff;
    border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.1em;
    flex-shrink: 0;
}
.ez-city-strip-text strong {
    display: block;
    font-size: 0.82em;
    font-weight: 700;
    color: #111827;
    margin-bottom: 2px;
}
.ez-city-strip-text span {
    font-size: 0.73em;
    color: #6b7280;
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

<!-- CONTACT INFO BAR — Hero ke neeche compact strip -->
<div class="ez-contact-bar">
    <div class="ez-contact-bar-inner">
        <?php if (!empty($city_data['center_name'])): ?>
        <div class="ez-cb-item">
            <span class="ez-cb-icon">🏪</span>
            <div>
                <span class="ez-cb-label">Service Center</span>
                <span class="ez-cb-value"><?php echo esc_html($city_data['center_name']); ?></span>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($city_data['address'])): ?>
        <div class="ez-cb-item">
            <span class="ez-cb-icon">📍</span>
            <div>
                <span class="ez-cb-label">Address</span>
                <span class="ez-cb-value"><?php echo esc_html(wp_trim_words($city_data['address'], 8)); ?><?php if (!empty($city_data['pincode'])): ?> — <?php echo esc_html($city_data['pincode']); ?><?php endif; ?></span>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($city_data['phone'])): ?>
        <div class="ez-cb-item">
            <span class="ez-cb-icon">📞</span>
            <div>
                <span class="ez-cb-label">Phone</span>
                <span class="ez-cb-value"><a href="<?php echo esc_url($ph_link); ?>"><?php echo esc_html($city_data['phone']); ?></a></span>
            </div>
        </div>
        <?php endif; ?>
        <?php if (!empty($city_data['hours'])): ?>
        <div class="ez-cb-item">
            <span class="ez-cb-icon">⏰</span>
            <div>
                <span class="ez-cb-label">Working Hours</span>
                <span class="ez-cb-value"><?php echo esc_html($city_data['hours']); ?></span>
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
        <div class="ez-city-intro-label" style="display:block; text-align:center; margin-bottom:18px;">
            <?php echo esc_html($city_data['center_name'] ?: $city_name); ?> &nbsp;|&nbsp; <?php echo esc_html($city_name); ?>
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
            // v1.5.3 FIX: esc_html() hataya — HTML tags (a, strong) allow karne ke liye
            // Pehle: esc_html() se <a> tags escape ho jaate the — link nahi banta tha
            // Ab: wp_kses() directly use karo — only allowed tags render honge
            $text = $intro_text; // placeholder already processed hai upar se
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
            // v1.5.3: <a> tag allow kiya — internal linking ke liye
            // {city_slug} placeholder already replace ho chuka hai upar Ezonecare_Placeholder::process() se
            echo nl2br(wp_kses($text, array(
                'strong' => array(),
                'br'     => array(),
                'a'      => array(
                    'href'  => array(),
                    'title' => array(),
                ),
            )));
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

        <!-- Learn More — About page link -->
        <div style="margin-top:28px; text-align:center;">
            <a href="<?php echo esc_url(home_url('/' . $city_slug . '/about/')); ?>"
               style="display:inline-flex; align-items:center; gap:8px;
                      background:#2563eb; color:#fff; text-decoration:none;
                      padding:12px 28px; border-radius:8px;
                      font-size:0.92em; font-weight:700;
                      transition:background 0.2s, transform 0.2s;"
               onmouseover="this.style.background='#1d4ed8'; this.style.transform='translateY(-2px)'"
               onmouseout="this.style.background='#2563eb'; this.style.transform='translateY(0)'">
                Learn More About Us →
            </a>
        </div>

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

<!-- BRANDS WE SERVICE — v1.5.10 -->
<?php
// v1.5.10: gallery field hataya — 6 alag image fields use karo
// Bilkul Entry Gate photo jaisa — "Add Image" button se select karo
$brand_logo_fields = array(
    get_field('city_brand_logo_1', $city_id),
    get_field('city_brand_logo_2', $city_id),
    get_field('city_brand_logo_3', $city_id),
    get_field('city_brand_logo_4', $city_id),
    get_field('city_brand_logo_5', $city_id),
    get_field('city_brand_logo_6', $city_id),
);
// Sirf filled fields lo
$brand_logos_final = array_filter($brand_logo_fields, function($logo) {
    return !empty($logo) && !empty($logo['url']);
});

if (!empty($brand_logos_final)):
?>
<div class="ez-brands-section">
    <div class="ez-brands-inner">
        <h2>Brands We Service</h2>
        <p class="ez-brands-sub">
            We work with all major brands and offer comprehensive repair services
        </p>
        <div class="ez-brands-grid">
            <?php foreach ($brand_logos_final as $logo):
                $logo_url = $logo['url'];
                $logo_alt = $logo['alt'] ?: $logo['title'] ?: 'Brand Logo';
            ?>
            <div class="ez-brand-card">
                <img src="<?php echo esc_url($logo_url); ?>"
                     alt="<?php echo esc_attr($logo_alt); ?>"
                     loading="lazy"
                     width="100"
                     height="50">
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- PICKUP & DROP SECTION — v1.5.6 -->
<?php
// city_pickup_available toggle se show/hide
// Admin ne Basic Info tab mein "Pickup & Drop Available" ON kiya hoga
$pickup_available = get_field('city_pickup_available', $city_id);
if ($pickup_available):
?>
<div class="ez-pickup-section">
    <div class="ez-pickup-inner">

        <!-- Left: SVG Illustration -->
        <div class="ez-pickup-svg">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 240" width="320" height="240" aria-hidden="true">
                <!-- Road -->
                <rect x="0" y="180" width="320" height="60" fill="#1e3a5f" rx="0"/>
                <rect x="0" y="178" width="320" height="4" fill="#2d5a8e"/>
                <!-- Road dashes -->
                <rect x="30" y="207" width="40" height="5" fill="#4a7fb5" rx="2"/>
                <rect x="100" y="207" width="40" height="5" fill="#4a7fb5" rx="2"/>
                <rect x="170" y="207" width="40" height="5" fill="#4a7fb5" rx="2"/>
                <rect x="240" y="207" width="40" height="5" fill="#4a7fb5" rx="2"/>
                <!-- Scooter body -->
                <ellipse cx="160" cy="175" rx="55" ry="18" fill="#1d4ed8"/>
                <rect x="118" y="155" width="70" height="22" rx="11" fill="#2563eb"/>
                <!-- Scooter wheel back -->
                <circle cx="120" cy="182" r="16" fill="#0f172a" stroke="#3b82f6" stroke-width="3"/>
                <circle cx="120" cy="182" r="7" fill="#1e3a5f"/>
                <!-- Scooter wheel front -->
                <circle cx="198" cy="182" r="16" fill="#0f172a" stroke="#3b82f6" stroke-width="3"/>
                <circle cx="198" cy="182" r="7" fill="#1e3a5f"/>
                <!-- Handlebar -->
                <line x1="195" y1="160" x2="210" y2="148" stroke="#60a5fa" stroke-width="3" stroke-linecap="round"/>
                <rect x="205" y="144" width="16" height="5" rx="2" fill="#60a5fa"/>
                <!-- Rider body -->
                <circle cx="165" cy="138" r="14" fill="#fbbf24"/>
                <rect x="152" y="150" width="26" height="28" rx="8" fill="#3b82f6"/>
                <!-- Laptop box on back -->
                <rect x="108" y="142" width="36" height="28" rx="5" fill="#1d4ed8" stroke="#60a5fa" stroke-width="2"/>
                <rect x="112" y="146" width="28" height="20" rx="3" fill="#1e3a5f"/>
                <line x1="126" y1="146" x2="126" y2="166" stroke="#60a5fa" stroke-width="1.5"/>
                <line x1="112" y1="156" x2="140" y2="156" stroke="#60a5fa" stroke-width="1.5"/>
                <!-- Speed lines -->
                <line x1="60" y1="162" x2="90" y2="162" stroke="#60a5fa" stroke-width="2" stroke-linecap="round" opacity="0.5"/>
                <line x1="50" y1="170" x2="88" y2="170" stroke="#60a5fa" stroke-width="1.5" stroke-linecap="round" opacity="0.4"/>
                <line x1="65" y1="178" x2="90" y2="178" stroke="#60a5fa" stroke-width="1" stroke-linecap="round" opacity="0.3"/>
                <!-- Stars/sparkles -->
                <text x="240" y="120" font-size="18" fill="#fbbf24" opacity="0.8">✦</text>
                <text x="270" y="140" font-size="12" fill="#60a5fa" opacity="0.6">✦</text>
                <text x="255" y="100" font-size="10" fill="#fbbf24" opacity="0.5">✦</text>
            </svg>
        </div>

        <!-- Right: Content -->
        <div class="ez-pickup-content">
            <h2>
                Free <span>Pickup</span> &amp;<br>
                <span>Drop</span> Service
            </h2>
            <p>
                We offer a convenient and hassle-free pickup service for laptop repairs
                in <?php echo esc_html($city_name); ?> within local area.
                Get your laptop repaired without leaving your home or office!
            </p>
            <a href="<?php echo esc_url($wa_link); ?>" class="ez-pickup-btn" target="_blank">
                📱 WhatsApp for Pickup
            </a>
        </div>

    </div>
</div>
<?php endif; ?>

<!-- WHY CHOOSE US — v1.5.11 NEW DESIGN — 2-column with robot image -->
<?php
$robot_img = EZ_CITY_PLUGIN_URL . 'assets/images/why-choose-robot.webp';
$center_nm = esc_html( $city_data['center_name'] ?: $city_name );
?>
<div class="ez-wcu-section">
    <div class="ez-wcu-wrap">

        <!-- LEFT: Content -->
        <div class="ez-wcu-content">

            <p class="ez-wcu-eyebrow">Why Choose Us?</p>
            <h2 class="ez-wcu-heading">WE DELIVER TRUSTED QUALITY</h2>
            <p class="ez-wcu-intro">
                At <?php echo $center_nm; ?>, your trusted laptop repair center in
                <?php echo esc_html( $city_name ); ?>, we focus on providing reliable
                and high-standard repair services tailored to your device needs.
                Our goal is to ensure consistent performance, transparency, and
                complete customer satisfaction. Here's what makes us different:
            </p>

            <ul class="ez-wcu-list">
                <li class="ez-wcu-item">
                    <span class="ez-wcu-icon">💰</span>
                    <div class="ez-wcu-item-body">
                        <strong>Cost-Effective Pricing</strong>
                        <span>Get quality repairs at reasonable rates with full transparency — no surprise charges, no hidden fees.</span>
                    </div>
                </li>
                <li class="ez-wcu-item">
                    <span class="ez-wcu-icon">⚡</span>
                    <div class="ez-wcu-item-body">
                        <strong>Quick Turnaround Time</strong>
                        <span>We value your time. Our technicians at <?php echo $center_nm; ?> ensure fast diagnosis and efficient repairs without compromising quality.</span>
                    </div>
                </li>
                <li class="ez-wcu-item">
                    <span class="ez-wcu-icon">🔧</span>
                    <div class="ez-wcu-item-body">
                        <strong>Original Spare Parts</strong>
                        <span>We use only verified and compatible components to maintain your device's long-term performance and reliability.</span>
                    </div>
                </li>
                <li class="ez-wcu-item">
                    <span class="ez-wcu-icon">👨‍🔧</span>
                    <div class="ez-wcu-item-body">
                        <strong>Skilled Professionals</strong>
                        <span>Our experienced technicians bring years of hands-on expertise to handle all types of laptop issues with precision.</span>
                    </div>
                </li>
                <li class="ez-wcu-item">
                    <span class="ez-wcu-icon">🧩</span>
                    <div class="ez-wcu-item-body">
                        <strong>All-in-One Repair Solutions</strong>
                        <span>From hardware faults to software issues, <?php echo $center_nm; ?> in <?php echo esc_html( $city_name ); ?> provides complete solutions under one roof for your convenience.</span>
                    </div>
                </li>
            </ul>

        </div><!-- .ez-wcu-content -->

        <!-- RIGHT: Robot image -->
        <div class="ez-wcu-image">
            <img src="<?php echo esc_url( $robot_img ); ?>"
                 alt="<?php echo $center_nm; ?> — Why Choose Us"
                 width="420" height="420"
                 loading="lazy" />
        </div>

    </div><!-- .ez-wcu-wrap -->
</div><!-- .ez-wcu-section -->

<style>
/* ── WHY CHOOSE US — v1.5.11 ────────────────────────────── */
.ez-wcu-section {
    background: #fff;
    padding: 60px 24px;
    border-top: 1px solid #e5e7eb;
    border-bottom: 1px solid #e5e7eb;
}
.ez-wcu-wrap {
    max-width: 1100px;
    margin: 0 auto;
    display: flex;
    align-items: center;
    gap: 48px;
}
.ez-wcu-content {
    flex: 1 1 55%;
    min-width: 0;
}
.ez-wcu-image {
    flex: 0 0 380px;
    text-align: center;
}
.ez-wcu-image img {
    max-width: 100%;
    height: auto;
    filter: drop-shadow(0 8px 24px rgba(0,0,0,0.10));
}
.ez-wcu-eyebrow {
    font-size: 0.85em;
    font-weight: 600;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.10em;
    margin: 0 0 8px;
}
.ez-wcu-heading {
    font-size: 2em;
    font-weight: 900;
    color: #111827;
    margin: 0 0 16px;
    line-height: 1.15;
    letter-spacing: -0.01em;
}
.ez-wcu-intro {
    font-size: 0.97em;
    color: #4b5563;
    line-height: 1.75;
    margin: 0 0 28px;
}
.ez-wcu-list {
    list-style: none;
    margin: 0;
    padding: 0;
    display: flex;
    flex-direction: column;
    gap: 18px;
}
.ez-wcu-item {
    display: flex;
    align-items: flex-start;
    gap: 16px;
    background: #f8faff;
    border: 1px solid #e0e7ff;
    border-radius: 12px;
    padding: 14px 18px;
    transition: box-shadow 0.2s;
}
.ez-wcu-item:hover {
    box-shadow: 0 4px 16px rgba(29,78,216,0.08);
}
.ez-wcu-icon {
    font-size: 1.5em;
    flex-shrink: 0;
    margin-top: 2px;
    width: 36px;
    text-align: center;
}
.ez-wcu-item-body {
    display: flex;
    flex-direction: column;
    gap: 3px;
}
.ez-wcu-item-body strong {
    font-size: 1em;
    font-weight: 700;
    color: #1d4ed8;
    display: block;
}
.ez-wcu-item-body span {
    font-size: 0.9em;
    color: #374151;
    line-height: 1.6;
}
@media (max-width: 820px) {
    .ez-wcu-wrap {
        flex-direction: column;
        gap: 32px;
    }
    .ez-wcu-image {
        flex: 0 0 auto;
        order: -1;
    }
    .ez-wcu-image img {
        max-width: 260px;
    }
    .ez-wcu-heading {
        font-size: 1.5em;
    }
}
@media (max-width: 480px) {
    .ez-wcu-section { padding: 40px 16px; }
    .ez-wcu-item { padding: 12px 14px; }
    .ez-wcu-heading { font-size: 1.3em; }
}
</style>

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
