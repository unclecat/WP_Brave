<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Customizer 自定义控件。
 *
 * @package Brave_Love
 */

/**
 * 自定义控制类 - 纪念日管理说明
 */
if (class_exists('WP_Customize_Control')) {
    class Brave_Anniversary_Note_Control extends WP_Customize_Control {
        public function render_content() {
            ?>
            <div class="brave-anniversary-note">
                <p><?php _e('纪念日管理请前往：后台 → 设置 → 纪念日管理', 'brave-love'); ?></p>
                <a href="<?php echo esc_url(admin_url('options-general.php?page=brave-anniversary')); ?>" class="button">
                    <?php _e('管理纪念日', 'brave-love'); ?>
                </a>
            </div>
            <?php
        }
    }
}

/**
 * 自定义控制类 - PV 统计当前数值显示
 */
if (class_exists('WP_Customize_Control')) {
    class Brave_PV_Stats_Control extends WP_Customize_Control {
        public function render_content() {
            // 确保函数存在
            if (!function_exists('brave_get_pv_stats')) {
                echo '<p style="color: red;">PV 统计功能未加载</p>';
                return;
            }
            $stats = brave_get_pv_stats();
            ?>
            <div class="brave-pv-current">
                <p style="margin-bottom: 5px;"><strong><?php _e('当前数值', 'brave-love'); ?></strong></p>
                <p style="margin: 0;"><?php _e('今日：', 'brave-love'); ?> <code><?php echo number_format($stats['today_count']); ?></code></p>
                <p style="margin: 0;"><?php _e('累计：', 'brave-love'); ?> <code><?php echo number_format($stats['total_count']); ?></code></p>
                <p style="margin-top: 10px; color: #666; font-size: 12px;"><?php _e('提示：修改上方数字并保存即可覆盖', 'brave-love'); ?></p>
            </div>
            <?php
        }
    }
}
