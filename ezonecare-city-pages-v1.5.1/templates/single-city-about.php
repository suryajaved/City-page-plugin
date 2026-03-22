<?php
/**
 * City About Page Template — Polished v2
 * URL: /ranchi/about/
 */

if (!defined('ABSPATH')) exit;

$query_handler = Ezonecare_Query_Handler::get_instance();
$city_post     = $query_handler->get_city();

if (!$city_post) { wp_redirect(home_url()); exit; }

$city_id     = $city_post->ID;
$city_name   = $city_post->post_title;
$city_slug   = $city_post->post_name;
$city_data   = Ezonecare_City_ACF::get_city_data($city_id);
$wa_link     = Ezonecare_City_ACF::get_whatsapp_link($city_id, $city_name);
$ph_link     = Ezonecare_City_ACF::get_phone_link($city_id);

$about_title = $city_data['about_title'] ?: ('About ' . ($city_data['center_name'] ?: 'E Zone Care') . ' — ' . $city_name);
// v1.5.1 FIX: Placeholder process karo about_story mein
$about_story_raw = $city_data['about_story'] ?: '';
$about_story     = ! empty( $about_story_raw )
    ? Ezonecare_Placeholder::process( $about_story_raw, $city_id, $city_data )
    : '';
$team_photo  = $city_data['team_photo']  ?: '';
$est_year    = $city_data['est_year']    ?: '';
$rating      = $city_data['rating']      ?: '';
// v1.5.1: repair_count bhi lo
$repair_count = $city_data['repair_count'] ?: '';
$years_exp   = $est_year ? (date('Y') - intval($est_year)) : '';
$has_photo   = !empty($team_photo);
$has_stats   = $est_year || $years_exp || $rating || $repair_count;

include(EZ_CITY_PLUGIN_DIR . 'templates/ez-header.php');
?>
<style>
* { box-sizing: border-box; }
.ez-ab { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; }

/* TOP BAR */
.ez-ab-topbar {
    background: #1a1a2e; color: #a0aec0;
    padding: 10px 24px; text-align: center;
    font-size: 0.82em; letter-spacing: 0.02em;
}
.ez-ab-topbar a { color: #63b3ed; text-decoration: none; font-weight: 600; }
.ez-ab-topbar .sep { margin: 0 10px; opacity: 0.3; }

/* HERO */
.ez-ab-hero {
    background: linear-gradient(135deg, #1a1a2e 0%, #2d3748 100%);
    padding: 64px 24px 52px; text-align: center;
    position: relative; overflow: hidden;
}
.ez-ab-hero::before {
    content: ''; position: absolute; top: -80px; right: -80px;
    width: 280px; height: 280px;
    background: rgba(49,130,206,0.07); border-radius: 50%; pointer-events: none;
}
.ez-ab-hero::after {
    content: ''; position: absolute; bottom: -50px; left: -50px;
    width: 200px; height: 200px;
    background: rgba(49,130,206,0.05); border-radius: 50%; pointer-events: none;
}
.ez-ab-hero-inner { position: relative; z-index: 1; max-width: 600px; margin: 0 auto; }
.ez-ab-hero-badge {
    display: inline-block;
    background: rgba(49,130,206,0.18); color: #90cdf4;
    font-size: 0.7em; font-weight: 700; letter-spacing: 0.12em;
    text-transform: uppercase; padding: 6px 18px; border-radius: 50px;
    border: 1px solid rgba(49,130,206,0.3); margin-bottom: 20px;
}
.ez-ab-hero h1 {
    font-size: 2.2em; font-weight: 800; color: #fff;
    margin: 0 0 12px; line-height: 1.25;
}
.ez-ab-hero h1 em { color: #63b3ed; font-style: normal; }
.ez-ab-hero-sub { color: #a0aec0; font-size: 0.95em; margin: 0; line-height: 1.6; }

/* STATS */
.ez-ab-stats { background: #fff; border-bottom: 2px solid #e2e8f0; }
.ez-ab-stats-grid {
    max-width: 860px; margin: 0 auto;
    display: grid; grid-template-columns: repeat(4,1fr);
}
@media(max-width:580px){ .ez-ab-stats-grid { grid-template-columns: repeat(2,1fr); } }
.ez-ab-stat {
    text-align: center; padding: 26px 16px;
    border-right: 1px solid #e2e8f0; transition: background 0.2s;
}
.ez-ab-stat:last-child { border-right: none; }
.ez-ab-stat:hover { background: #f7fafc; }
.ez-ab-stat-num { font-size: 2em; font-weight: 800; color: #3182ce; line-height: 1; margin-bottom: 5px; }
.ez-ab-stat-label { font-size: 0.7em; color: #a0aec0; text-transform: uppercase; letter-spacing: 0.07em; font-weight: 600; }

/* STORY */
.ez-ab-story { padding: 68px 24px; background: #fff; }
.ez-ab-story-wrap { max-width: 920px; margin: 0 auto; }
.ez-ab-story-2col {
    display: grid; grid-template-columns: 55fr 45fr;
    gap: 60px; align-items: center;
}
@media(max-width:720px){
    .ez-ab-story-2col { grid-template-columns: 1fr; gap: 36px; }
    .ez-ab-photo-col { order: -1; }
}
.ez-ab-story-1col { max-width: 740px; }
.ez-ab-eyebrow {
    font-size: 0.7em; font-weight: 700; letter-spacing: 0.12em;
    text-transform: uppercase; color: #3182ce;
    margin-bottom: 14px; display: flex; align-items: center; gap: 10px;
}
.ez-ab-eyebrow::after {
    content: ''; display: inline-block;
    height: 2px; width: 36px;
    background: #3182ce; border-radius: 2px;
}
.ez-ab-story h2 {
    font-size: 1.8em; font-weight: 800; color: #1a1a2e;
    margin: 0 0 24px; line-height: 1.3;
}
.ez-ab-story-body { font-size: 0.96em; color: #4a5568; line-height: 1.95; }
.ez-ab-story-body p { margin: 0 0 15px; }
.ez-ab-story-body p:last-child { margin: 0; }
.ez-ab-photo-col { position: relative; }
.ez-ab-photo-col::before {
    content: ''; position: absolute; top: 16px; right: -16px;
    width: 100%; height: 100%;
    background: #ebf8ff; border-radius: 18px; z-index: 0;
}
.ez-ab-photo-col img {
    position: relative; z-index: 1; width: 100%; height: 340px;
    object-fit: cover; border-radius: 16px;
    box-shadow: 0 14px 44px rgba(0,0,0,0.13); display: block;
}
@media(max-width:720px){
    .ez-ab-photo-col::before { display: none; }
    .ez-ab-photo-col img { height: 220px; }
}

/* WHY CHOOSE US */
.ez-ab-why {
    background: #f7fafc; padding: 60px 24px;
    border-top: 1px solid #e2e8f0;
}
.ez-ab-why-inner { max-width: 860px; margin: 0 auto; }
.ez-ab-why-header { text-align: center; margin-bottom: 36px; }
.ez-ab-why-header .ez-ab-eyebrow { justify-content: center; }
.ez-ab-why-header .ez-ab-eyebrow::after { display: none; }
.ez-ab-why-header h2 { font-size: 1.5em; font-weight: 800; color: #1a1a2e; margin: 0; }
.ez-ab-why-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 20px; }
@media(max-width:640px){ .ez-ab-why-grid { grid-template-columns: 1fr; } }
.ez-ab-why-card {
    background: #fff; border: 1px solid #e2e8f0; border-radius: 14px;
    padding: 30px 22px; text-align: center;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    transition: transform 0.25s, box-shadow 0.25s;
}
.ez-ab-why-card:hover { transform: translateY(-4px); box-shadow: 0 10px 28px rgba(0,0,0,0.09); }
.ez-ab-why-icon {
    width: 58px; height: 58px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    margin: 0 auto 16px; font-size: 1.5em; font-style: normal;
}
.ez-ab-why-icon.blue  { background: #ebf8ff; }
.ez-ab-why-icon.green { background: #f0fff4; }
.ez-ab-why-icon.amber { background: #fffaf0; }
.ez-ab-why-title { font-size: 0.95em; font-weight: 700; color: #1a1a2e; margin-bottom: 8px; }
.ez-ab-why-desc { font-size: 0.82em; color: #718096; line-height: 1.65; }

/* CTA */
.ez-ab-cta {
    background: linear-gradient(135deg, #1a1a2e 0%, #2d3748 100%);
    padding: 56px 24px; text-align: center;
}
.ez-ab-cta h2 { color: #fff; font-size: 1.55em; font-weight: 800; margin: 0 0 8px; }
.ez-ab-cta p { color: #a0aec0; font-size: 0.9em; margin: 0 0 28px; }
.ez-ab-cta-btns { display: flex; gap: 14px; justify-content: center; flex-wrap: wrap; }
.ez-ab-btn-wa {
    display: inline-flex; align-items: center; gap: 9px;
    background: #25D366; color: #fff !important;
    padding: 15px 30px; border-radius: 50px;
    font-weight: 700; font-size: 0.95em; text-decoration: none !important;
    box-shadow: 0 4px 16px rgba(37,211,102,0.35); transition: all 0.3s;
}
.ez-ab-btn-wa:hover { background: #128C7E; transform: translateY(-2px); }
.ez-ab-btn-visit {
    display: inline-flex; align-items: center; gap: 9px;
    background: rgba(255,255,255,0.08); border: 2px solid rgba(255,255,255,0.25);
    color: #fff !important; padding: 15px 30px; border-radius: 50px;
    font-weight: 700; font-size: 0.95em; text-decoration: none !important;
    transition: all 0.3s;
}
.ez-ab-btn-visit:hover { background: #fff; color: #1a1a2e !important; border-color: #fff; }
</style>

<div class="ez-ab">

    <!-- HERO -->
    <div class="ez-ab-hero">
        <div class="ez-ab-hero-inner">
            <div class="ez-ab-hero-badge">Our Story</div>
            <h1>About Us &mdash; <em><?php echo esc_html($city_name); ?></em></h1>
            <p class="ez-ab-hero-sub">
                <?php echo esc_html($city_data['center_name'] ?: 'E Zone Care'); ?> &mdash;
                <?php echo esc_html($city_name); ?>'s trusted laptop repair &amp; service partner.
            </p>
        </div>
    </div>

    <!-- STATS BAR -->
    <?php if ($has_stats): ?>
    <div class="ez-ab-stats">
        <div class="ez-ab-stats-grid">
            <?php if ($est_year): ?>
            <div class="ez-ab-stat">
                <div class="ez-ab-stat-num"><?php echo esc_html($est_year); ?></div>
                <div class="ez-ab-stat-label">Est. Year</div>
            </div>
            <?php endif; ?>
            <?php if ($years_exp): ?>
            <div class="ez-ab-stat">
                <div class="ez-ab-stat-num"><?php echo esc_html($years_exp); ?>+</div>
                <div class="ez-ab-stat-label">Years Experience</div>
            </div>
            <?php endif; ?>
            <?php if ($rating): ?>
            <div class="ez-ab-stat">
                <div class="ez-ab-stat-num"><?php echo esc_html($rating); ?> &#9733;</div>
                <div class="ez-ab-stat-label">Rating</div>
            </div>
            <?php endif; ?>
            <?php if ($repair_count): ?>
            <div class="ez-ab-stat">
                <div class="ez-ab-stat-num"><?php echo esc_html($repair_count); ?></div>
                <div class="ez-ab-stat-label">Repairs Done</div>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <!-- STORY -->
    <?php if ($about_story || $has_photo): ?>
    <div class="ez-ab-story">
        <div class="ez-ab-story-wrap">

            <?php if ($has_photo): ?>
            <div class="ez-ab-story-2col">
                <div>
                    <div class="ez-ab-eyebrow">Our Story</div>
                    <h2><?php echo esc_html($about_title); ?></h2>
                    <div class="ez-ab-story-body">
                        <?php
                        // Placeholder already processed hai — wp_kses se safe output
                        $paras = preg_split('/\n{2,}/', trim($about_story));
                        foreach ($paras as $para) {
                            $para = trim($para);
                            if ($para) echo '<p>' . nl2br(esc_html($para)) . '</p>';
                        }
                        ?>
                    </div>
                </div>
                <div class="ez-ab-photo-col">
                    <img src="<?php echo esc_url($team_photo); ?>"
                         alt="<?php echo esc_attr(($city_data['center_name'] ?: 'E Zone Care') . ' — ' . $city_name); ?>"
                         loading="lazy">
                </div>
            </div>

            <?php else: ?>
            <div class="ez-ab-story-1col">
                <div class="ez-ab-eyebrow">Our Story</div>
                <h2><?php echo esc_html($about_title); ?></h2>
                <div class="ez-ab-story-body">
                    <?php
                    if ($about_story) {
                        // Placeholder already processed hai
                        $paras = preg_split('/\n{2,}/', trim($about_story));
                        foreach ($paras as $para) {
                            $para = trim($para);
                            if ($para) echo '<p>' . nl2br(esc_html($para)) . '</p>';
                        }
                    } else {
                        echo '<p>' . esc_html(($city_data['center_name'] ?: 'E Zone Care') . ' ' . $city_name . ' mein ek trusted laptop repair center hai. Hum sabhi brands ke laptops repair karte hain.') . '</p>';
                    }
                    ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
    <?php endif; ?>

    <!-- WHY CHOOSE US -->
    <div class="ez-ab-why">
        <div class="ez-ab-why-inner">
            <div class="ez-ab-why-header">
                <div class="ez-ab-eyebrow">Why Choose Us</div>
                <h2>What Makes Us Different</h2>
            </div>
            <div class="ez-ab-why-grid">
                <div class="ez-ab-why-card">
                    <i class="ez-ab-why-icon blue">&#128295;</i>
                    <div class="ez-ab-why-title">Expert Technicians</div>
                    <div class="ez-ab-why-desc">Certified technicians with years of hands-on experience across all major laptop brands.</div>
                </div>
                <div class="ez-ab-why-card">
                    <i class="ez-ab-why-icon green">&#10003;</i>
                    <div class="ez-ab-why-title">Genuine Parts</div>
                    <div class="ez-ab-why-desc">We use only genuine or OEM-grade parts — no shortcuts, no compromise on quality.</div>
                </div>
                <div class="ez-ab-why-card">
                    <i class="ez-ab-why-icon amber">&#9889;</i>
                    <div class="ez-ab-why-title">Quick Turnaround</div>
                    <div class="ez-ab-why-desc">Most repairs completed same day. We respect your time and keep you updated throughout.</div>
                </div>
            </div>
        </div>
    </div>


</div>
<?php include(EZ_CITY_PLUGIN_DIR . 'templates/ez-footer.php'); ?>
