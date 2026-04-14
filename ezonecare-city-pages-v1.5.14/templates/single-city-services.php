<?php
/**
 * @version 1.5.11
 * City Services Page Template
 * URL: /ranchi/services/
 * SEO: All services offered in this city — links to individual service pages
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
$center    = $city_data['center_name'] ?: 'E Zone Care';

// Get active services + their brands
$service_ids = Ezonecare_City_Meta_Box::get_active_service_ids($city_id);
$services    = array();
foreach ($service_ids as $sid) {
    $post = get_post($sid);
    if (!$post) continue;
    $brand_ids = Ezonecare_City_Meta_Box::get_active_brand_ids($city_id, $sid);
    $brands    = array();
    foreach ($brand_ids as $bid) {
        $bp = get_post($bid);
        if ($bp) $brands[] = $bp;
    }
    $services[] = array('post' => $post, 'brands' => $brands);
}

// Service icons mapping — fallback emoji per service type
function ez_service_icon($name) {
    $name = strtolower($name);
    if (strpos($name, 'battery')  !== false) return '🔋';
    if (strpos($name, 'screen')   !== false) return '🖥️';
    if (strpos($name, 'keyboard') !== false) return '⌨️';
    if (strpos($name, 'motherboard') !== false) return '🔌';
    if (strpos($name, 'hinges')   !== false) return '🔩';
    if (strpos($name, 'charging') !== false) return '⚡';
    if (strpos($name, 'virus')    !== false) return '🛡️';
    if (strpos($name, 'data')     !== false) return '💾';
    if (strpos($name, 'ram')      !== false) return '💿';
    if (strpos($name, 'ssd')      !== false) return '💿';
    return '🔧';
}

include(EZ_CITY_PLUGIN_DIR . 'templates/ez-header.php');
?>
<style>
.ez-services{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;color:#2d3748}
.ez-services *,.ez-services *::before,.ez-services *::after{box-sizing:border-box}
.ez-services a{text-decoration:none}

/* INFO BAR */
.ez-sv-bar{background:#1a1a2e;padding:10px 24px;display:flex;justify-content:center;align-items:center;gap:24px;flex-wrap:wrap;font-size:.82em;color:#94a3b8}
.ez-sv-bar a{color:#60a5fa;font-weight:600}
.ez-sv-bar span{display:flex;align-items:center;gap:6px}

/* HERO */
.ez-sv-hero{background:linear-gradient(135deg,#f8faff 0%,#eef2ff 100%);padding:60px 24px 50px;text-align:center;border-bottom:1px solid #e2e8f0;position:relative;overflow:hidden}
.ez-sv-hero::before{content:'';position:absolute;top:-60px;right:-60px;width:240px;height:240px;background:rgba(99,102,241,.06);border-radius:50%}
.ez-sv-hero::after{content:'';position:absolute;bottom:-40px;left:-40px;width:160px;height:160px;background:rgba(59,130,246,.05);border-radius:50%}
.ez-sv-hero-content{position:relative;z-index:1}
.ez-sv-badge{display:inline-flex;align-items:center;gap:6px;background:#eff6ff;color:#3b82f6;font-size:.72em;font-weight:700;letter-spacing:.1em;text-transform:uppercase;padding:6px 16px;border-radius:50px;border:1px solid #bfdbfe;margin-bottom:20px}
.ez-sv-hero h1{font-size:clamp(1.8em,4vw,2.5em);font-weight:800;color:#1e293b;margin:0 0 14px;line-height:1.25;letter-spacing:-.02em}
.ez-sv-hero-sub{font-size:1em;color:#64748b;margin:0 auto;max-width:520px;line-height:1.6}

/* SERVICES GRID */
.ez-sv-section{padding:60px 24px;background:#fff}
.ez-sv-wrap{max-width:980px;margin:0 auto}
.ez-sv-section-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:36px;flex-wrap:wrap;gap:12px}
.ez-sv-section-top h2{font-size:1.4em;font-weight:800;color:#1e293b;margin:0;letter-spacing:-.01em}
.ez-sv-count{background:#eff6ff;color:#3b82f6;font-size:.8em;font-weight:700;padding:4px 12px;border-radius:50px}
.ez-sv-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:20px}

/* SERVICE CARD */
.ez-sv-card{background:#fff;border:1px solid #e2e8f0;border-radius:16px;overflow:hidden;transition:transform .2s,box-shadow .2s;display:flex;flex-direction:column}
.ez-sv-card:hover{transform:translateY(-4px);box-shadow:0 12px 32px rgba(0,0,0,.08);border-color:#bfdbfe}
.ez-sv-card-top{padding:24px 24px 16px;flex:1}
.ez-sv-card-icon{width:52px;height:52px;background:#eff6ff;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4em;margin-bottom:16px}
.ez-sv-card-name{font-size:1.02em;font-weight:700;color:#1e293b;margin:0 0 10px;line-height:1.3}
.ez-sv-brands-label{font-size:.7em;font-weight:700;text-transform:uppercase;letter-spacing:.07em;color:#94a3b8;margin-bottom:8px}
.ez-sv-brands-wrap{display:flex;flex-wrap:wrap;gap:5px}
.ez-sv-brand-chip{background:#f1f5f9;color:#475569;font-size:.75em;font-weight:600;padding:3px 10px;border-radius:50px}
.ez-sv-card-footer{padding:14px 24px;border-top:1px solid #f1f5f9;background:#fafbfc}
.ez-sv-card-link{display:flex;align-items:center;justify-content:space-between;color:#3b82f6 !important;font-size:.88em;font-weight:700}
.ez-sv-card-link:hover{color:#2563eb !important}
.ez-sv-card-arrow{font-size:1.1em;transition:transform .2s}
.ez-sv-card:hover .ez-sv-card-arrow{transform:translateX(4px)}

/* EMPTY STATE */
.ez-sv-empty{text-align:center;padding:60px 24px;color:#94a3b8}
.ez-sv-empty-icon{font-size:3em;margin-bottom:16px}
.ez-sv-empty p{font-size:.97em;margin:0}

/* ALL SERVICES NOTE */
.ez-sv-note{background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:18px 22px;margin-top:32px;display:flex;align-items:flex-start;gap:12px}
.ez-sv-note-icon{font-size:1.2em;flex-shrink:0;margin-top:2px}
.ez-sv-note-text{font-size:.88em;color:#166534;line-height:1.6}
.ez-sv-note-text strong{font-weight:700}

/* CTA */
.ez-sv-cta{background:linear-gradient(135deg,#1e293b 0%,#1a1a2e 100%);padding:56px 24px;text-align:center}
.ez-sv-cta h2{font-size:1.5em;font-weight:800;color:#fff;margin:0 0 10px;letter-spacing:-.02em}
.ez-sv-cta p{color:#94a3b8;font-size:.97em;margin:0 0 28px}
.ez-sv-cta-btns{display:flex;gap:14px;justify-content:center;flex-wrap:wrap}
.ez-sv-btn-wa{display:inline-flex;align-items:center;gap:8px;background:#25D366;color:#fff !important;padding:13px 28px;border-radius:50px;font-weight:700;font-size:.97em;transition:all .25s;box-shadow:0 4px 14px rgba(37,211,102,.3)}
.ez-sv-btn-wa:hover{background:#128C7E;transform:translateY(-2px)}
.ez-sv-btn-call{display:inline-flex;align-items:center;gap:8px;background:rgba(255,255,255,.08);border:2px solid rgba(255,255,255,.2);color:#fff !important;padding:13px 28px;border-radius:50px;font-weight:700;font-size:.97em;transition:all .25s}
.ez-sv-btn-call:hover{background:rgba(255,255,255,.15);border-color:rgba(255,255,255,.4)}
</style>

<div class="ez-services">

    <!-- HERO -->
    <div class="ez-sv-hero">
        <div class="ez-sv-hero-content">
            <div class="ez-sv-badge">🔧 Our Services</div>
            <h1>Laptop Repair Services<br>in <?php echo esc_html($city_name); ?></h1>
            <p class="ez-sv-hero-sub">
                <?php echo esc_html($center); ?> offers <?php echo count($services); ?>+ professional laptop repair services in <?php echo esc_html($city_name); ?> — all major brands covered.
            </p>
        </div>
    </div>

    <!-- SERVICES GRID -->
    <div class="ez-sv-section">
        <div class="ez-sv-wrap">

            <div class="ez-sv-section-top">
                <h2>All Services in <?php echo esc_html($city_name); ?></h2>
                <?php if (!empty($services)): ?>
                <span class="ez-sv-count"><?php echo count($services); ?> Services Available</span>
                <?php endif; ?>
            </div>

            <?php if (!empty($services)): ?>
            <div class="ez-sv-grid">
                <?php foreach ($services as $item):
                    $spost = $item['post'];
                    $brands = $item['brands'];
                    $service_url = home_url('/' . $city_slug . '/' . $spost->post_name . '/');
                    $icon = ez_service_icon($spost->post_title);
                ?>
                <div class="ez-sv-card">
                    <div class="ez-sv-card-top">
                        <div class="ez-sv-card-icon"><?php echo $icon; ?></div>
                        <div class="ez-sv-card-name"><?php echo esc_html($spost->post_title); ?></div>
                        <?php if (!empty($brands)): ?>
                        <div class="ez-sv-brands-label">Brands Covered</div>
                        <div class="ez-sv-brands-wrap">
                            <?php foreach ($brands as $brand): ?>
                            <span class="ez-sv-brand-chip"><?php echo esc_html($brand->post_title); ?></span>
                            <?php endforeach; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="ez-sv-card-footer">
                        <a href="<?php echo esc_url($service_url); ?>" class="ez-sv-card-link">
                            View Service Details
                            <span class="ez-sv-card-arrow">→</span>
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- NOTE -->
            <div class="ez-sv-note">
                <span class="ez-sv-note-icon">ℹ️</span>
                <div class="ez-sv-note-text">
                    <strong>Don't see your issue?</strong> WhatsApp us — hum sabhi laptop problems diagnose karte hain. Free diagnosis available at our <?php echo esc_html($city_name); ?> center.
                </div>
            </div>

            <?php else: ?>
            <div class="ez-sv-empty">
                <div class="ez-sv-empty-icon">🔧</div>
                <p>Services coming soon. Please WhatsApp us for current service availability.</p>
            </div>
            <?php endif; ?>

        </div>
    </div>


</div>

<!-- CTA SECTION -->
<div class="ez-sv-cta">
    <h2>Need Help? Talk to Us Directly</h2>
    <p><?php echo esc_html($center); ?> — <?php echo esc_html($city_name); ?> | <?php echo esc_html($city_data['hours'] ?: 'Mon–Sat 10 AM – 8 PM'); ?></p>
    <div class="ez-sv-cta-btns">
        <?php if ($wa_link && $wa_link !== '#'): ?>
        <a href="<?php echo esc_url($wa_link); ?>" class="ez-sv-btn-wa" target="_blank" rel="noopener">
            💬 WhatsApp Us
        </a>
        <?php endif; ?>
        <?php if ($ph_link): ?>
        <a href="<?php echo esc_url($ph_link); ?>" class="ez-sv-btn-call">
            📞 Call Now
        </a>
        <?php endif; ?>
    </div>
</div>

<?php include(EZ_CITY_PLUGIN_DIR . 'templates/ez-footer.php'); ?>
