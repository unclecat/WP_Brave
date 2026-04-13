<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Customizer 模块加载器。
 *
 * @package Brave_Love
 */

/**
 * Customizer 控件资源。
 */
function brave_customize_controls_assets() {
    wp_enqueue_style('brave-customizer-controls', BRAVE_URI . '/assets/css/customizer-controls.css', array(), BRAVE_VERSION);
    wp_enqueue_script('jquery-ui-sortable');
    wp_enqueue_script('brave-customizer-controls', BRAVE_URI . '/assets/js/customizer-controls.js', array('jquery', 'customize-controls', 'jquery-ui-sortable'), BRAVE_VERSION, true);
}
add_action('customize_controls_enqueue_scripts', 'brave_customize_controls_assets');

require __DIR__ . '/customizer-sanitize.php';
require __DIR__ . '/customizer-controls.php';
require __DIR__ . '/customizer-register.php';
require __DIR__ . '/customizer-output.php';
require __DIR__ . '/customizer-anniversary.php';
