<?php
/**
 * EzoneCare Local Intro Progress Indicator v1.0
 *
 * City edit page pe progress bar dikhata hai:
 * Kitne brand service local intros fill hain
 *
 * @package EzonecareCity
 * @version 1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class Ezonecare_Progress_Indicator {

    // Sabhi brand service slugs → Display labels
    // Naya service add karne pe yahan add karo
    public static $service_map = array(
        'dell-laptop-repair-service'          => 'Dell Laptop Repair',
        'dell-laptop-keyboard-replacement'    => 'Dell Keyboard',
        'dell-laptop-battery-replacement'     => 'Dell Battery',
        'dell-laptop-screen-replacement'      => 'Dell Screen',
        'dell-laptop-motherboard-repair'      => 'Dell Motherboard',
        'acer-laptop-repair-service'          => 'Acer Laptop Repair',
        'acer-laptop-keyboard-replacement'    => 'Acer Keyboard',
        'acer-laptop-battery-replacement'     => 'Acer Battery',
        'acer-laptop-screen-replacement'      => 'Acer Screen',
        'acer-laptop-motherboard-repair'      => 'Acer Motherboard',
    );

    private static $instance = null;

    public static function get_instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'add_meta_boxes', array( $this, 'add_progress_metabox' ) );
        add_action( 'admin_head',     array( $this, 'progress_styles' ) );
    }

    public function add_progress_metabox() {
        add_meta_box(
            'ez_local_intro_progress',
            '📊 Local Intro Progress',
            array( $this, 'render_progress_metabox' ),
            'city',
            'side',
            'high'
        );
    }

    public function render_progress_metabox( $post ) {
        if ( ! function_exists( 'get_field' ) ) {
            echo '<p>ACF plugin required.</p>';
            return;
        }

        $progress = Ezonecare_Placeholder::get_progress(
            $post->ID,
            self::$service_map
        );

        $percent = $progress['percent'];
        $filled  = $progress['filled'];
        $total   = $progress['total'];
        $pending = $progress['pending'];

        // Color based on percent
        if ( $percent >= 80 ) {
            $bar_color = '#38a169'; // green
            $emoji     = '🟢';
        } elseif ( $percent >= 40 ) {
            $bar_color = '#d69e2e'; // yellow
            $emoji     = '🟡';
        } else {
            $bar_color = '#e53e3e'; // red
            $emoji     = '🔴';
        }
        ?>
        <div class="ez-progress-wrap">

            <div class="ez-progress-header">
                <?php echo $emoji; ?>
                <strong><?php echo $filled; ?>/<?php echo $total; ?> filled</strong>
                <span class="ez-percent"><?php echo $percent; ?>%</span>
            </div>

            <div class="ez-progress-bar-bg">
                <div class="ez-progress-bar-fill"
                     style="width:<?php echo $percent; ?>%; background:<?php echo $bar_color; ?>;">
                </div>
            </div>

            <?php if ( ! empty( $pending ) ) : ?>
            <div class="ez-pending-list">
                <strong>⏳ Pending:</strong><br>
                <?php foreach ( $pending as $label ) : ?>
                    <span class="ez-pending-item">• <?php echo esc_html( $label ); ?></span><br>
                <?php endforeach; ?>
            </div>
            <?php else : ?>
            <div class="ez-all-done">
                ✅ <strong>Sab fill ho gaya!</strong>
            </div>
            <?php endif; ?>

            <p class="ez-progress-tip">
                💡 <em>Local Intro tab mein fill karo for better SEO</em>
            </p>
        </div>
        <?php
    }

    public function progress_styles() {
        global $post_type;
        if ( $post_type !== 'city' ) return;
        ?>
        <style>
        .ez-progress-wrap { font-size: 13px; }
        .ez-progress-header {
            display: flex; align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .ez-percent {
            background: #edf2f7;
            padding: 2px 8px;
            border-radius: 12px;
            font-weight: 700;
            font-size: 12px;
        }
        .ez-progress-bar-bg {
            background: #e2e8f0;
            border-radius: 10px;
            height: 12px;
            overflow: hidden;
            margin-bottom: 12px;
        }
        .ez-progress-bar-fill {
            height: 100%;
            border-radius: 10px;
            transition: width 0.5s ease;
        }
        .ez-pending-list {
            background: #fff5f5;
            border: 1px solid #fed7d7;
            border-radius: 6px;
            padding: 8px 10px;
            margin-bottom: 10px;
            color: #742a2a;
            line-height: 1.8;
        }
        .ez-pending-item { font-size: 12px; }
        .ez-all-done {
            background: #f0fff4;
            border: 1px solid #9ae6b4;
            border-radius: 6px;
            padding: 8px 10px;
            margin-bottom: 10px;
            color: #22543d;
        }
        .ez-progress-tip {
            color: #718096;
            font-size: 11px;
            margin: 0;
        }
        </style>
        <?php
    }
}
