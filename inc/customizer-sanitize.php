<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Customizer sanitize 与基础工具。
 *
 * @package Brave_Love
 */

/**
 * PV 数字 sanitize（允许空值或数字）
 */
function brave_sanitize_pv_number($value) {
    if ($value === '' || $value === null) {
        return '';
    }
    return intval($value);
}

/**
 * 页脚导航链接 sanitize，支持相对路径和完整 URL。
 *
 * @param string $value 原始值
 * @return string
 */
function brave_sanitize_footer_nav_url($value) {
    $value = trim((string) $value);

    if ('' === $value) {
        return '';
    }

    if (0 === strpos($value, '/')) {
        return esc_url_raw(home_url($value));
    }

    if (!preg_match('#^[a-z][a-z0-9+\-.]*://#i', $value)) {
        $value = home_url('/' . ltrim($value, '/'));
    }

    return esc_url_raw($value, array('http', 'https'));
}

/**
 * Customizer 复选框 sanitize。
 *
 * @param mixed $value 原始值
 * @return bool
 */
function brave_sanitize_checkbox($value) {
    return wp_validate_boolean($value);
}

/**
 * 自定义 CSS sanitize。
 *
 * 仅移除包裹 style 标签，保留正常 CSS 语法，避免把选择器内容误伤。
 *
 * @param string $value 原始值
 * @return string
 */
function brave_sanitize_custom_css($value) {
    $value = (string) $value;
    $value = preg_replace('#</?style[^>]*>#i', '', $value);
    $value = str_ireplace('</style', '<\\/style', $value);

    return trim($value);
}

/**
 * 底部代码 sanitize。
 *
 * 拥有 unfiltered_html 的管理员允许保存原始统计代码，
 * 其他用户仍回退到文章级白名单。
 *
 * @param string $value 原始值
 * @return string
 */
function brave_sanitize_footer_code($value) {
    if (current_user_can('unfiltered_html')) {
        return (string) $value;
    }

    return wp_kses_post($value);
}
