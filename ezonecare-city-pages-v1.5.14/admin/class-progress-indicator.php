<?php
/**
 * EzoneCare Content Progress Indicator v1.0
 *
 * City edit page pe content completion % dikhata hai
 * Taaki admin ko pata chale kaunse service ka local intro
 * fill karna baaki hai.
 *
 * @package EzonecareCity
 * @version 1.5.11
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Ezonecare_Progress_Indicator {

    private static $instance = null;

    // Sab brand service slugs hardcoded (abhi 10 posts)
    // Naya post aane pe yahan add karo
    private static $brand_service_slugs = array(
        'dell-laptop-repair-service',
        'dell-laptop-keyboard-replacement',
        'dell-laptop-battery-replacement',
        'dell-laptop-screen-replacement',
        'dell-laptop-motherboard-repair',
        'acer-laptop-repair-service',
        'acer-laptop-keyboard-replacement',
        'acer-laptop-battery-replacement',
        'acer-laptop-screen-replacement',
        'acer-laptop-motherboard-repair',
    );

    // Display labels for each slug
    private static $slug_labels = array(
        'dell-laptop-repair-service'         => '🔧 Dell Laptop Repair',
        'dell-laptop-keyboard-replacement'   => '⌨️ Dell Keyboard',
        'dell-laptop-battery-replacement'    => '🔋 Dell Battery',
        'dell-laptop-screen-replacement'     => '🖥️ Dell Screen',
        'dell-laptop-motherboard-repair'     => '🔌 Dell Motherboard',
        'acer-laptop-repair-service'         => '🔧 Acer Laptop Repair',
        'acer-laptop-keyboard-replacement'   => '⌨️ Acer Keyboard',
        'acer-laptop-battery-replacement'    => '🔋 Acer Battery',
        'acer-laptop-screen-replacement'     => '🖥️ Acer Screen',
        'acer-laptop-motherboard-repair'     => '🔌 Acer Motherboard',
    );

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_progress_meta_box' ) );
        add_action( 'admin_head',     array( $this, 'add_styles' ) );
    }

    /**
     * Meta box register karo — city post type pe
     */
    public function add_progress_meta_box() {
        add_meta_box(
            'ez_content_progress',
            '📊 Content Completion Progress',
            array( $this, 'render_progress_box' ),
            'city',
            'side',
            'high'
        );
    }

    /**
     * Progress box render karo — v1.4.7: 3-state system
     * customized = admin ne save kiya → ✅ Green (100% weight)
     * default    = auto-loaded, save nahi → 🔵 Blue (50% weight)
     */
    public function render_progress_box( $post ) {
        if ( ! class_exists( 'Ezonecare_City_ACF' ) ) {
            echo '<p>ACF class load nahi hua.</p>';
            return;
        }

        $slugs      = self::$brand_service_slugs;
        $customized = array();
        $defaults   = array();

        foreach ( $slugs as $slug ) {
            $state = Ezonecare_City_ACF::get_intro_state( $post->ID, $slug );
            if ( $state === 'customized' ) {
                $customized[] = $slug;
            } else {
                $defaults[] = $slug;
            }
        }

        $total   = count( $slugs );
        // customized = full weight, default = half weight
        $score   = ( count( $customized ) * 1.0 ) + ( count( $defaults ) * 0.5 );
        $percent = $total > 0 ? round( ( $score / $total ) * 100 ) : 0;

        // Color
        if ( $percent >= 80 ) {
            $bar_color = '#38a169';
            $status    = '🌟 Excellent';
        } elseif ( $percent >= 50 ) {
            $bar_color = '#d69e2e';
            $status    = '⚡ Good Progress';
        } elseif ( $percent >= 20 ) {
            $bar_color = '#e53e3e';
            $status    = '🚀 Just Started';
        } else {
            $bar_color = '#a0aec0';
            $status    = '📝 Not Started';
        }
        ?>
        <div class="ez-progress-wrap">

            <div class="ez-progress-header">
                <span class="ez-progress-percent"><?php echo $percent; ?>%</span>
                <span class="ez-progress-status"><?php echo $status; ?></span>
            </div>

            <div class="ez-progress-bar-bg">
                <div class="ez-progress-bar-fill"
                     style="width:<?php echo $percent; ?>%; background:<?php echo $bar_color; ?>;"></div>
            </div>

            <p class="ez-progress-count">
                ✅ <?php echo count($customized); ?> customized &nbsp;|&nbsp;
                🔵 <?php echo count($defaults); ?> default &nbsp;|&nbsp;
                Total: <?php echo $total; ?>
            </p>

            <!-- Customized list -->
            <?php if ( ! empty( $customized ) ) : ?>
            <div class="ez-progress-section">
                <strong class="ez-done-label">✅ Customized (<?php echo count($customized); ?>)</strong>
                <ul class="ez-progress-list ez-done-list">
                    <?php foreach ( $customized as $slug ) : ?>
                    <li><?php echo esc_html( self::$slug_labels[$slug] ?? $slug ); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <!-- Default list -->
            <?php if ( ! empty( $defaults ) ) : ?>
            <div class="ez-progress-section">
                <strong class="ez-default-label">🔵 Default / Pending Customize (<?php echo count($defaults); ?>)</strong>
                <ul class="ez-progress-list ez-default-list">
                    <?php foreach ( $defaults as $slug ) : ?>
                    <li><?php echo esc_html( self::$slug_labels[$slug] ?? $slug ); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <p class="ez-progress-tip">
                💡 <em>Local Intro tab → box mein edit karo → Save karo → Customized ho jayega</em>
            </p>

        </div>
        <?php
    }

    /**
     * CSS styles
     */
    public function add_styles() {
        global $pagenow, $post_type;
        if ( $pagenow !== 'post.php' && $pagenow !== 'post-new.php' ) return;
        if ( $post_type !== 'city' ) return;
        ?>
        <style>
        .ez-progress-wrap { font-size: 13px; }

        .ez-progress-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .ez-progress-percent {
            font-size: 2em;
            font-weight: 700;
            color: #1a1a2e;
            line-height: 1;
        }
        .ez-progress-status {
            font-size: 0.85em;
            color: #718096;
            font-weight: 600;
        }

        .ez-progress-bar-bg {
            background: #e2e8f0;
            border-radius: 6px;
            height: 10px;
            overflow: hidden;
            margin-bottom: 6px;
        }
        .ez-progress-bar-fill {
            height: 100%;
            border-radius: 6px;
            transition: width 0.4s ease;
        }

        .ez-progress-count {
            color: #718096;
            font-size: 0.82em;
            margin: 4px 0 12px;
        }

        .ez-progress-section { margin-bottom: 10px; }

        .ez-done-label    { color: #38a169; font-size: 0.85em; }
        .ez-pending-label { color: #e53e3e; font-size: 0.85em; }
        .ez-default-label { color: #2b6cb0; font-size: 0.85em; }

        .ez-progress-list {
            margin: 4px 0 0 0;
            padding: 0;
            list-style: none;
        }
        .ez-progress-list li {
            padding: 3px 0;
            font-size: 0.82em;
            border-bottom: 1px solid #f0f0f0;
            color: #4a5568;
        }
        .ez-done-list    li { color: #2d6a4f; }
        .ez-default-list li { color: #2a4365; }
        .ez-pending-list li { color: #c53030; }

        .ez-progress-tip {
            margin-top: 10px;
            padding: 8px 10px;
            background: #ebf8ff;
            border-radius: 6px;
            color: #2b6cb0;
            font-size: 0.8em;
        }
        </style>
        <?php
    }
}
