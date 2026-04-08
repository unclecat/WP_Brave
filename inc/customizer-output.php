<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Customizer 前台输出。
 *
 * @package Brave_Love
 */

/**
 * 输出自定义 CSS
 */
function brave_output_custom_css() {
    $custom_css = get_theme_mod('brave_custom_css', '');
    if (!empty($custom_css)) {
        echo "<style type=\"text/css\" id=\"brave-custom-css\">\n" . $custom_css . "\n</style>"; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
add_action('wp_head', 'brave_output_custom_css', 100);

/**
 * 输出底部代码
 */
function brave_output_footer_code() {
    $footer_code = get_theme_mod('brave_footer_code', '');
    if (!empty($footer_code)) {
        echo $footer_code; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
add_action('wp_footer', 'brave_output_footer_code', 100);
