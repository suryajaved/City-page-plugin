<?php
/**
 * @version 1.5.11
 * EzoneCare City Footer — v4 Clean
 * Single CTA strip + 3 col footer — no duplicates
 */
if (!defined('ABSPATH')) exit;

$_fq   = Ezonecare_Query_Handler::get_instance();
$_fc   = $_fq->get_city();
if (!$_fc) {
    if (function_exists('astra_footer')) astra_footer();
    wp_footer(); echo '</body></html>'; return;
}

$_fid  = $_fc->ID;
$_fn   = $_fc->post_title;
$_fsl  = $_fc->post_name;
$_fd   = Ezonecare_City_ACF::get_city_data($_fid);
$_fwa  = Ezonecare_City_ACF::get_whatsapp_link($_fid, $_fn);
$_fph  = Ezonecare_City_ACF::get_phone_link($_fid);
$_fcn  = $_fd['center_name'] ?: 'E Zone Care';
$_fadr = $_fd['address']     ?: '';
$_ftel = $_fd['phone']       ?: '';
$_fhr  = $_fd['hours']       ?: 'Mon-Sat 10 AM – 8 PM';

$_fhome = home_url('/' . $_fsl . '/');
$_fsv   = home_url('/' . $_fsl . '/services/');
$_fabt  = home_url('/' . $_fsl . '/about/');
$_fcon  = home_url('/' . $_fsl . '/contact/');
?>
<footer class="ez-footer">
<style>
/* ── EZ FOOTER ────────────────────────────── */
.ez-footer{
    font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',sans-serif;
    margin:0; padding:0;
}
.ez-footer *,.ez-footer *::before,.ez-footer *::after{box-sizing:border-box}
.ez-footer a{text-decoration:none}

/* CTA STRIP */
.ez-ft-cta{
    background:linear-gradient(135deg,#1e293b 0%,#0f172a 100%);
    padding:48px 24px;
    text-align:center;
}
.ez-ft-cta-title{
    font-size:1.4em;
    font-weight:800;
    color:#f1f5f9;
    margin:0 0 8px;
    letter-spacing:-.02em;
}
.ez-ft-cta-sub{
    color:#64748b;
    font-size:.93em;
    margin:0 0 26px;
}
.ez-ft-cta-btns{
    display:flex;
    gap:14px;
    justify-content:center;
    flex-wrap:wrap;
}
.ez-ft-btn-wa{
    display:inline-flex;align-items:center;gap:9px;
    background:#25D366;color:#fff !important;
    padding:13px 30px;border-radius:50px;
    font-weight:700;font-size:.95em;
    transition:all .25s;
    box-shadow:0 4px 16px rgba(37,211,102,.3);
}
.ez-ft-btn-wa:hover{background:#128C7E;transform:translateY(-2px)}
.ez-ft-btn-call{
    display:inline-flex;align-items:center;gap:9px;
    background:rgba(255,255,255,.1);
    border:2px solid rgba(255,255,255,.25);
    color:#fff !important;
    padding:13px 30px;border-radius:50px;
    font-weight:700;font-size:.95em;
    transition:all .25s;
}
.ez-ft-btn-call:hover{background:rgba(255,255,255,.18);transform:translateY(-2px)}

/* MAIN FOOTER */
.ez-ft-body{
    background:#1e293b;
    padding:52px 24px 40px;
    border-top:1px solid #334155;
}
.ez-ft-grid{
    max-width:980px;
    margin:0 auto;
    display:grid;
    grid-template-columns:1.8fr 1fr 1fr;
    gap:48px;
}
@media(max-width:720px){
    .ez-ft-grid{grid-template-columns:1fr;gap:36px}
}

/* Column title */
.ez-ft-col-title{
    font-size:.75em;
    font-weight:700;
    text-transform:uppercase;
    letter-spacing:.1em;
    color:#475569;
    margin-bottom:18px;
    padding-bottom:10px;
    border-bottom:1px solid #334155;
}

/* Brand col */
.ez-ft-brand{
    display:flex;
    align-items:center;
    gap:10px;
    margin-bottom:12px;
}
.ez-ft-brand-icon{
    width:40px;height:40px;
    background:#eff6ff;
    border-radius:10px;
    display:flex;align-items:center;justify-content:center;
    font-size:1.1em;
    flex-shrink:0;
}
.ez-ft-brand-name{
    font-size:1.05em;
    font-weight:800;
    color:#f1f5f9;
    line-height:1.2;
}
.ez-ft-brand-city{
    font-size:.78em;
    color:#64748b;
    font-weight:500;
}
.ez-ft-desc{
    color:#64748b;
    font-size:.87em;
    line-height:1.7;
    margin-bottom:22px;
}
.ez-ft-wa-btn{
    display:inline-flex;align-items:center;gap:8px;
    background:#25D366;color:#fff !important;
    padding:9px 20px;border-radius:50px;
    font-size:.85em;font-weight:700;
    transition:background .2s;
}
.ez-ft-wa-btn:hover{background:#128C7E}

/* Info col */
.ez-ft-info-item{
    display:flex;align-items:flex-start;
    gap:11px;
    margin-bottom:16px;
}
.ez-ft-info-icon{
    font-size:1em;
    flex-shrink:0;
    margin-top:2px;
    width:20px;
    text-align:center;
}
.ez-ft-info-text{
    font-size:.88em;
    color:#94a3b8;
    line-height:1.6;
}
.ez-ft-info-text a{
    color:#60a5fa;
    font-weight:600;
}
.ez-ft-info-text a:hover{color:#93c5fd}

/* Nav col */
.ez-ft-nav-list{
    display:flex;
    flex-direction:column;
    gap:12px;
}
.ez-ft-nav-list a{
    display:flex;align-items:center;gap:9px;
    color:#94a3b8;
    font-size:.9em;
    font-weight:500;
    transition:color .15s;
}
.ez-ft-nav-list a:hover{color:#e2e8f0}
.ez-ft-nav-icon{
    font-size:1em;
    width:18px;
    text-align:center;
    color:#60a5fa;
    flex-shrink:0;
}

/* Bottom bar */
.ez-ft-bottom{
    background:#0f172a;
    padding:20px 24px;
    border-top:1px solid #334155;
}
.ez-ft-bottom-inner{
    max-width:980px;
    margin:0 auto;
    display:flex;
    align-items:center;
    justify-content:space-between;
    flex-wrap:wrap;
    gap:12px;
    font-size:.84em;
    color:#94a3b8;
}
.ez-ft-legal-links{
    display:flex;
    align-items:center;
    gap:16px;
    flex-wrap:wrap;
}
.ez-ft-legal-links a{
    color:#cbd5e1 !important;
    font-size:1em;
    font-weight:600;
    text-decoration:underline !important;
    text-underline-offset:3px;
    text-decoration-color:rgba(203,213,225,0.4) !important;
    transition:all .2s;
}
.ez-ft-legal-links a:hover{
    color:#fff !important;
    text-decoration-color:rgba(255,255,255,0.7) !important;
}
</style>

    <!-- CTA STRIP — city home page pe hide, baaki pages pe show -->
    <?php
    // City home page pe CTA strip nahi dikhegi — already pickup section aur hero mein CTA hai
    // Single city service/about/contact pages pe dikhegi
    $is_city_home = (Ezonecare_Query_Handler::get_instance()->get_resolved_route() === 'city');
    if (!$is_city_home):
    ?>
    <div class="ez-ft-cta">
        <div class="ez-ft-cta-title">Laptop Repair in <?php echo esc_html($_fn); ?>?</div>
        <div class="ez-ft-cta-sub">Quick response guaranteed — WhatsApp karo ya call karo.</div>
        <div class="ez-ft-cta-btns">
            <?php if ($_fwa && $_fwa !== '#'): ?>
            <a href="<?php echo esc_url($_fwa); ?>" class="ez-ft-btn-wa" target="_blank" rel="noopener">
                💬 WhatsApp Us
            </a>
            <?php endif; ?>
            <?php if ($_ftel): ?>
            <a href="<?php echo esc_url($_fph); ?>" class="ez-ft-btn-call">
                📞 Call Now
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- MAIN GRID -->
    <div class="ez-ft-body">
        <div class="ez-ft-grid">

            <!-- Col 1: Brand -->
            <div>
                <div class="ez-ft-col-title">About</div>
                <div class="ez-ft-brand">
                    <div class="ez-ft-brand-icon">📍</div>
                    <div>
                        <div class="ez-ft-brand-name"><?php echo esc_html($_fcn); ?></div>
                        <div class="ez-ft-brand-city"><?php echo esc_html($_fn); ?> Service Center</div>
                    </div>
                </div>
                <div class="ez-ft-desc">
                    <?php echo esc_html($_fn); ?> ka trusted laptop repair center.<br>
                    Dell, HP, Lenovo, Acer, Apple & all major brands.
                </div>
                <?php if ($_fwa && $_fwa !== '#'): ?>
                <a href="<?php echo esc_url($_fwa); ?>" class="ez-ft-wa-btn" target="_blank" rel="noopener">
                    💬 WhatsApp Us
                </a>
                <?php endif; ?>
            </div>

            <!-- Col 2: Contact Info -->
            <div>
                <div class="ez-ft-col-title">Contact Info</div>
                <?php if ($_fadr): ?>
                <div class="ez-ft-info-item">
                    <span class="ez-ft-info-icon">📍</span>
                    <div class="ez-ft-info-text"><?php echo esc_html($_fadr); ?></div>
                </div>
                <?php endif; ?>
                <?php if ($_ftel): ?>
                <div class="ez-ft-info-item">
                    <span class="ez-ft-info-icon">📞</span>
                    <div class="ez-ft-info-text">
                        <a href="<?php echo esc_url($_fph); ?>"><?php echo esc_html($_ftel); ?></a>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($_fhr): ?>
                <div class="ez-ft-info-item">
                    <span class="ez-ft-info-icon">🕐</span>
                    <div class="ez-ft-info-text"><?php echo esc_html($_fhr); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <!-- Col 3: Quick Links -->
            <div>
                <div class="ez-ft-col-title">Quick Links</div>
                <div class="ez-ft-nav-list">
                    <a href="<?php echo esc_url($_fhome); ?>">
                        <span class="ez-ft-nav-icon">&#8962;</span> <?php echo esc_html($_fn); ?> Home
                    </a>
                    <a href="<?php echo esc_url($_fsv); ?>">
                        <span class="ez-ft-nav-icon">&#9881;</span> Our Services
                    </a>
                    <a href="<?php echo esc_url($_fabt); ?>">
                        <span class="ez-ft-nav-icon">&#9432;</span> About Us
                    </a>
                    <a href="<?php echo esc_url($_fcon); ?>">
                        <span class="ez-ft-nav-icon">&#9742;</span> Contact Us
                    </a>
                </div>
            </div>

        </div>
    </div>

    <!-- OTHER SERVICE CITIES STRIP — SEO Internal Linking -->
    <?php
    // Sabhi cities query karo
    $_ft_all_cities = get_posts( array(
        'post_type'      => 'city',
        'post_status'    => 'publish',
        'posts_per_page' => -1,
        'orderby'        => 'title',
        'order'          => 'ASC',
    ) );

    // Current city remove karo — self link nahi chahiye
    $_ft_other_cities = array_filter( $_ft_all_cities, function( $c ) use ( $_fid ) {
        return $c->ID !== $_fid;
    } );

    if ( ! empty( $_ft_other_cities ) ):

        // State-wise group karo
        $_ft_grouped = array();
        foreach ( $_ft_other_cities as $_ftc ) {
            $_fts = get_field( 'city_state', $_ftc->ID );
            $_fts = $_fts ? trim( $_fts ) : 'Other';
            $_ft_grouped[ $_fts ][] = $_ftc;
        }
        ksort( $_ft_grouped );
    ?>
    <div class="ez-ft-cities-strip">
        <style>
        .ez-ft-cities-strip {
            background: #162032;
            padding: 28px 24px;
            border-top: 1px solid #1e3a5f;
        }
        .ez-ft-cities-inner {
            max-width: 980px;
            margin: 0 auto;
        }
        .ez-ft-cities-title {
            font-size: .72em;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .1em;
            color: #475569;
            margin-bottom: 18px;
            padding-bottom: 10px;
            border-bottom: 1px solid #1e3a5f;
        }
        .ez-ft-cities-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 24px 40px;
        }
        .ez-ft-state-group {
            min-width: 120px;
        }
        .ez-ft-state-name {
            font-size: .68em;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: #3b82f6;
            margin-bottom: 8px;
        }
        .ez-ft-city-list {
            list-style: none;
            margin: 0; padding: 0;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }
        .ez-ft-city-list li a {
            font-size: .82em;
            color: #94a3b8;
            text-decoration: none;
            transition: color .15s;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .ez-ft-city-list li a::before {
            content: '›';
            color: #3b82f6;
            font-size: 1em;
            line-height: 1;
        }
        .ez-ft-city-list li a:hover { color: #e2e8f0; }
        @media(max-width:600px){
            .ez-ft-cities-grid { gap: 20px 24px; }
        }
        </style>

        <div class="ez-ft-cities-inner">
            <div class="ez-ft-cities-title">Our Service Cities</div>
            <div class="ez-ft-cities-grid">
                <?php foreach ( $_ft_grouped as $_fts_name => $_fts_cities ): ?>
                <div class="ez-ft-state-group">
                    <div class="ez-ft-state-name"><?php echo esc_html( $_fts_name ); ?></div>
                    <ul class="ez-ft-city-list">
                        <?php foreach ( $_fts_cities as $_ftcity ):
                            $_ftcurl = home_url( '/' . $_ftcity->post_name . '/' );
                        ?>
                        <li>
                            <a href="<?php echo esc_url( $_ftcurl ); ?>"
                               title="Laptop Repair in <?php echo esc_attr( $_ftcity->post_title ); ?>">
                                <?php echo esc_html( $_ftcity->post_title ); ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- BOTTOM BAR -->
    <div class="ez-ft-bottom">
        <div class="ez-ft-bottom-inner">
            <span>© <?php echo date('Y'); ?> <?php echo esc_html($_fcn); ?>, <?php echo esc_html($_fn); ?>. All rights reserved.</span>
            <div class="ez-ft-legal-links">
                <a href="<?php echo esc_url(home_url('/privacy-policy/')); ?>">Privacy Policy</a>
                <span style="color:#64748b">·</span>
                <a href="<?php echo esc_url(home_url('/terms-and-conditions/')); ?>">Terms & Conditions</a>
                <span style="color:#64748b">·</span>
                <a href="<?php echo esc_url(home_url('/disclaimer/')); ?>">Disclaimer</a>
                <span style="color:#64748b">·</span>
                <a href="<?php echo esc_url(home_url('/refund-policy/')); ?>">Refund Policy</a>
            </div>
        </div>
    </div>

</footer>

<?php
if (function_exists('astra_footer')) astra_footer();
else get_template_part('template-parts/footer/footer', 'primary');
wp_footer();
?>
</body>
</html>
