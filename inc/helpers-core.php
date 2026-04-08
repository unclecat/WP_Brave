<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 基础工具与页面 Hero 辅助函数。
 *
 * @package Brave_Love
 */

/**
 * 校验 ISO 日期字符串。
 *
 * @param string $value 日期字符串
 * @return bool
 */
function brave_is_valid_iso_date($value) {
    $value = trim((string) $value);

    if ('' === $value) {
        return false;
    }

    $date = DateTime::createFromFormat('!Y-m-d', $value);

    return $date instanceof DateTime && $date->format('Y-m-d') === $value;
}

/**
 * 清理 ISO 日期字符串，非法值返回空字符串。
 *
 * @param string $value 日期字符串
 * @return string
 */
function brave_sanitize_iso_date($value) {
    $value = trim((string) $value);

    return brave_is_valid_iso_date($value) ? $value : '';
}

/**
 * 清理经纬度。
 *
 * @param string $value     原始值
 * @param float  $min       最小值
 * @param float  $max       最大值
 * @param int    $precision 保留小数位
 * @return string
 */
function brave_sanitize_coordinate($value, $min, $max, $precision = 4) {
    $value = trim((string) $value);

    if ('' === $value || !is_numeric($value)) {
        return '';
    }

    $number = (float) $value;

    if ($number < $min || $number > $max) {
        return '';
    }

    return number_format($number, max(0, absint($precision)), '.', '');
}

/**
 * 读取布尔型主题设置。
 *
 * @param string $key     设置键名
 * @param bool   $default 默认值
 * @return bool
 */
function brave_theme_mod_enabled($key, $default = false) {
    return wp_validate_boolean(get_theme_mod($key, $default ? '1' : '0'));
}

/**
 * 允许 data URI 作为主题内联头像输出。
 *
 * @param string $url 头像 URL
 * @return string
 */
function brave_esc_avatar_url($url) {
    return esc_url($url, array('http', 'https', 'data'));
}

/**
 * 获取主题 Hero 背景样式。
 *
 * 优先使用后台配置的 Hero 图，缺失时回退到主题内渐变背景，
 * 供首页 Hero 和其他页面的通用 Hero 复用。
 *
 * @param string $background_url 指定背景图 URL，留空时回退到全局 Hero 图
 * @return string
 */
function brave_get_hero_background_style($background_url = '') {
    $hero_bg = $background_url ? $background_url : get_theme_mod('brave_hero_bg');

    if (!empty($hero_bg)) {
        return "background-image: url('" . esc_url_raw($hero_bg) . "');";
    }

    return 'background-image: radial-gradient(circle at top, rgba(255, 255, 255, 0.22), transparent 36%), linear-gradient(135deg, #ff9a9e 0%, #fad0c4 42%, #ffd1ff 100%);';
}

/**
 * 获取页面 Hero 自定义字段。
 *
 * @param int $post_id 页面 ID
 * @return array
 */
function brave_get_page_hero_meta($post_id) {
    $post_id = absint($post_id);

    if ($post_id <= 0) {
        return array(
            'title' => '',
            'subtitle' => '',
            'background' => '',
        );
    }

    return array(
        'title' => get_post_meta($post_id, '_brave_page_hero_title', true),
        'subtitle' => get_post_meta($post_id, '_brave_page_hero_subtitle', true),
        'background' => get_post_meta($post_id, '_brave_page_hero_bg', true),
    );
}

/**
 * 解析当前页面 Hero 最终配置。
 *
 * 页面模板优先读取当前页面的 Hero 自定义字段；
 * 恋爱清单归档则读取主题设置中的归档 Hero 配置；
 * 其余字段回退到模板传入的默认值。
 *
 * @param array $args 默认配置
 * @return array
 */
function brave_resolve_page_hero_args($args = array()) {
    $resolved = wp_parse_args(
        $args,
        array(
            'context' => '',
            'post_id' => 0,
            'eyebrow' => '',
            'title' => get_the_title(),
            'subtitle' => '',
            'background' => '',
        )
    );

    if ('love_list_archive' === $resolved['context']) {
        $resolved['title'] = get_theme_mod('brave_love_list_hero_title', $resolved['title']);
        $resolved['subtitle'] = get_theme_mod('brave_love_list_hero_subtitle', $resolved['subtitle']);
        $resolved['background'] = get_theme_mod('brave_love_list_hero_bg', $resolved['background']);
    } else {
        $post_id = $resolved['post_id'] ? absint($resolved['post_id']) : get_the_ID();
        $meta = brave_get_page_hero_meta($post_id);

        if (!empty($meta['title'])) {
            $resolved['title'] = $meta['title'];
        }

        if (!empty($meta['subtitle'])) {
            $resolved['subtitle'] = $meta['subtitle'];
        }

        if (!empty($meta['background'])) {
            $resolved['background'] = $meta['background'];
        }
    }

    $resolved['background_style'] = brave_get_hero_background_style($resolved['background']);

    return $resolved;
}
