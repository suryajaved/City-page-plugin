<?php
/**
 * EzoneCare Custom Header v3
 * Desktop: Left city badge + Center nav + Right Call/WA buttons
 * Mobile: Top city badge + Sticky bottom navigation bar
 */
if (!defined('ABSPATH')) exit;

$_ez_query   = Ezonecare_Query_Handler::get_instance();
$_ez_city    = $_ez_query->get_city();
$_ez_slug    = $_ez_city ? $_ez_city->post_name : '';
$_ez_name    = $_ez_city ? $_ez_city->post_title : '';

if ($_ez_slug) {
    $_ez_data    = Ezonecare_City_ACF::get_city_data($_ez_city->ID);
    $_ez_wa      = Ezonecare_City_ACF::get_whatsapp_link($_ez_city->ID, $_ez_name);
    $_ez_ph      = Ezonecare_City_ACF::get_phone_link($_ez_city->ID);
    $_ez_phone   = $_ez_data['phone'] ?: '';

    $_ez_home    = home_url('/' . $_ez_slug . '/');
    $_ez_sv      = home_url('/' . $_ez_slug . '/services/');
    $_ez_about   = home_url('/' . $_ez_slug . '/about/');
    $_ez_contact = home_url('/' . $_ez_slug . '/contact/');
}

// Active page detection
$_ez_uri   = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
$_ez_parts = explode('/', $_ez_uri);
$_ez_page  = isset($_ez_parts[1]) ? $_ez_parts[1] : '';

function ez_nav_active($page, $current) {
    return $page === $current ? 'ez-nav-active' : '';
}
$_home_active    = ($_ez_page === '' || (isset($_ez_parts[1]) && $_ez_parts[1] === '')) ? 'ez-nav-active' : '';
// More precise: if only 1 segment (city slug) — it's home
$_home_active    = (count(array_filter($_ez_parts)) === 1) ? 'ez-nav-active' : '';
$_sv_active      = ez_nav_active('services', $_ez_page);
$_about_active   = ez_nav_active('about', $_ez_page);
$_contact_active = ez_nav_active('contact', $_ez_page);
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
<style>
/* ── FULL WIDTH OVERRIDE ──────────────────── */
.ast-sidebar-wrap,#secondary,.widget-area,
.ast-right-sidebar,.ast-left-sidebar,
.ast-sidebar-layout,.sidebar{display:none !important}
#primary,.content-area,#main,.ast-article-post,
.entry-content,.site-content{
    width:100% !important;max-width:100% !important;
    float:none !important;padding:0 !important;margin:0 !important
}
.ast-container,.container,.ast-grid-row{
    max-width:100% !important;padding:0 !important
}
.ezonecare-custom-page{padding:0 !important;margin:0 !important}

/* ── DESKTOP CITY NAV ─────────────────────── */
.ez-city-nav{
    background:#fff;
    border-bottom:1px solid #e2e8f0;
    position:sticky;
    top:0;
    z-index:999;
    box-shadow:0 2px 8px rgba(0,0,0,.06);
}
.ez-city-nav-inner{
    max-width:1100px;
    margin:0 auto;
    padding:0 24px;
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:16px;
    height:52px;
}

/* Left — City Badge */
.ez-nav-brand{
    display:flex;
    align-items:center;
    gap:7px;
    text-decoration:none;
    flex-shrink:0;
}
.ez-nav-brand-icon{
    width:30px;height:30px;
    background:#eff6ff;
    border-radius:8px;
    display:flex;align-items:center;justify-content:center;
    font-size:.9em;
}
.ez-nav-brand-text{
    font-size:1.05em;
    font-weight:800;
    color:#1e293b;
    line-height:1.15;
    letter-spacing:-0.01em;
}
.ez-nav-brand-sub{
    font-size:.68em;
    color:#64748b;
    font-weight:500;
    letter-spacing:.03em;
    text-transform:uppercase;
}

/* Center — Nav links */
.ez-nav-links{
    display:flex;
    align-items:center;
    gap:2px;
    flex:1;
    justify-content:center;
}
.ez-nav-links a{
    display:inline-flex;
    align-items:center;
    gap:5px;
    padding:6px 14px;
    border-radius:8px;
    font-size:.84em;
    font-weight:600;
    color:#475569;
    text-decoration:none;
    transition:all .18s;
    white-space:nowrap;
}
.ez-nav-links a:hover{background:#f1f5f9;color:#1e293b}
.ez-nav-links a.ez-nav-active{background:#eff6ff;color:#3b82f6}
.ez-nav-links a .ez-nl-icon{font-size:.95em}

/* Right — CTA Buttons */
.ez-nav-cta{
    display:flex;
    align-items:center;
    gap:8px;
    flex-shrink:0;
}
.ez-nav-btn-call{
    display:inline-flex;align-items:center;gap:6px;
    background:#1e293b;color:#fff !important;
    padding:7px 16px;border-radius:8px;
    font-size:.82em;font-weight:700;
    text-decoration:none !important;
    transition:background .2s;
    white-space:nowrap;
}
.ez-nav-btn-call:hover{background:#0f172a}
.ez-nav-btn-wa{
    display:inline-flex;align-items:center;gap:6px;
    background:#25D366;color:#fff !important;
    padding:7px 16px;border-radius:8px;
    font-size:.82em;font-weight:700;
    text-decoration:none !important;
    transition:background .2s;
    white-space:nowrap;
}
.ez-nav-btn-wa:hover{background:#128C7E}

/* ── MOBILE — hide desktop nav links + cta ─ */
@media(max-width:768px){
    .ez-nav-links{display:none}
    .ez-nav-cta{display:none}
    /* Make brand full width on mobile */
    .ez-city-nav-inner{justify-content:center}
    /* Add bottom padding to page for sticky nav */
    body{padding-bottom:64px !important}
}

/* ── MOBILE STICKY BOTTOM NAV ─────────────── */
.ez-mobile-bottom-nav{
    display:none;
    position:fixed;
    bottom:0;left:0;right:0;
    z-index:9999;
    background:#fff;
    border-top:1px solid #e2e8f0;
    box-shadow:0 -4px 16px rgba(0,0,0,.08);
    height:60px;
}
@media(max-width:768px){
    .ez-mobile-bottom-nav{display:flex}
}
.ez-mbn-inner{
    display:flex;
    width:100%;
    height:100%;
}
.ez-mbn-item{
    flex:1;
    display:flex;
    flex-direction:column;
    align-items:center;
    justify-content:center;
    gap:3px;
    text-decoration:none !important;
    color:#64748b !important;
    font-size:.6em;
    font-weight:600;
    transition:all .15s;
    border-top:2px solid transparent;
    padding:6px 4px 4px;
}
.ez-mbn-item:hover{color:#1e293b !important;background:#f8fafc}
.ez-mbn-item.ez-nav-active{
    color:#3b82f6 !important;
    border-top-color:#3b82f6;
    background:#f8fbff;
}
.ez-mbn-icon{font-size:1.5em;line-height:1}
.ez-mbn-label{font-size:1em;letter-spacing:.01em}

/* WhatsApp bottom item — green */
.ez-mbn-item.ez-mbn-wa{
    background:#f0fdf4;
    color:#16a34a !important;
}
.ez-mbn-item.ez-mbn-wa:hover{background:#dcfce7}
</style>
</head>
<body <?php body_class('ezonecare-custom-page'); ?>>
<?php wp_body_open(); ?>

<?php
if (function_exists('astra_header')) {
    astra_header();
} else {
    get_template_part('template-parts/header/header', 'primary');
}
?>

<?php if ($_ez_slug): ?>

<!-- ── DESKTOP CITY NAV ───────────────────── -->
<nav class="ez-city-nav" aria-label="<?php echo esc_attr($_ez_name); ?> Navigation">
    <div class="ez-city-nav-inner">

        <!-- Left: City Badge -->
        <a href="<?php echo esc_url($_ez_home); ?>" class="ez-nav-brand">
            <div class="ez-nav-brand-icon">📍</div>
            <div>
                <div class="ez-nav-brand-text"><?php echo esc_html( !empty($_ez_data['center_name']) ? $_ez_data['center_name'] : $_ez_name ); ?></div>
                <div class="ez-nav-brand-sub"><?php echo esc_html($_ez_name); ?> &middot; Service Center</div>
            </div>
        </a>

        <!-- Center: Nav Links -->
        <div class="ez-nav-links">
            <a href="<?php echo esc_url($_ez_home); ?>" class="<?php echo $_home_active; ?>">
                <span class="ez-nl-icon">🏠</span> Home
            </a>
            <a href="<?php echo esc_url($_ez_sv); ?>" class="<?php echo $_sv_active; ?>">
                <span class="ez-nl-icon">🔧</span> Services
            </a>
            <a href="<?php echo esc_url($_ez_about); ?>" class="<?php echo $_about_active; ?>">
                <span class="ez-nl-icon">ℹ️</span> About Us
            </a>
            <a href="<?php echo esc_url($_ez_contact); ?>" class="<?php echo $_contact_active; ?>">
                <span class="ez-nl-icon">📍</span> Contact
            </a>
        </div>

        <!-- Right: CTA Buttons -->
        <div class="ez-nav-cta">
            <?php if (!empty($_ez_phone)): ?>
            <a href="<?php echo esc_url($_ez_ph); ?>" class="ez-nav-btn-call">
                📞 <?php echo esc_html($_ez_phone); ?>
            </a>
            <?php endif; ?>
            <?php if (!empty($_ez_wa) && $_ez_wa !== '#'): ?>
            <a href="<?php echo esc_url($_ez_wa); ?>" class="ez-nav-btn-wa" target="_blank" rel="noopener">
                💬 WhatsApp
            </a>
            <?php endif; ?>
        </div>

    </div>
</nav>


<!-- ── MOBILE BOTTOM NAV ──────────────────── -->
<nav class="ez-mobile-bottom-nav" aria-label="Mobile Navigation">
    <div class="ez-mbn-inner">
        <a href="<?php echo esc_url($_ez_home); ?>" class="ez-mbn-item <?php echo $_home_active; ?>">
            <span class="ez-mbn-icon">🏠</span>
            <span class="ez-mbn-label">Home</span>
        </a>
        <a href="<?php echo esc_url($_ez_sv); ?>" class="ez-mbn-item <?php echo $_sv_active; ?>">
            <span class="ez-mbn-icon">🔧</span>
            <span class="ez-mbn-label">Services</span>
        </a>
        <a href="<?php echo esc_url($_ez_about); ?>" class="ez-mbn-item <?php echo $_about_active; ?>">
            <span class="ez-mbn-icon">ℹ️</span>
            <span class="ez-mbn-label">About Us</span>
        </a>
        <a href="<?php echo esc_url($_ez_contact); ?>" class="ez-mbn-item <?php echo $_contact_active; ?>">
            <span class="ez-mbn-icon">📍</span>
            <span class="ez-mbn-label">Contact</span>
        </a>
        <?php if (!empty($_ez_wa) && $_ez_wa !== '#'): ?>
        <a href="<?php echo esc_url($_ez_wa); ?>" class="ez-mbn-item ez-mbn-wa" target="_blank" rel="noopener">
            <span class="ez-mbn-icon">💬</span>
            <span class="ez-mbn-label">WhatsApp</span>
        </a>
        <?php endif; ?>
    </div>
</nav>

<?php endif; ?>
