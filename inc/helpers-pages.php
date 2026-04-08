<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 页面链接、导航与归档辅助函数。
 *
 * @package Brave_Love
 */

/**
 * 根据页面模板查找页面 ID。
 *
 * @param string $template 页面模板路径
 * @return int
 */
function brave_get_page_id_by_template($template) {
    static $cache = array();

    $template = (string) $template;

    if (isset($cache[$template])) {
        return $cache[$template];
    }

    $posts = get_posts(
        array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'orderby' => 'menu_order title',
            'order' => 'ASC',
            'meta_key' => '_wp_page_template',
            'meta_value' => $template,
            'no_found_rows' => true,
        )
    );

    $cache[$template] = !empty($posts) ? (int) $posts[0] : 0;

    return $cache[$template];
}

/**
 * 获取主题页面链接
 */
function brave_get_page_link($type) {
    // 恋爱清单使用 CPT archive
    if ($type === 'lists') {
        return get_post_type_archive_link('love_list');
    }

    // 其他页面使用设置的页面或默认链接
    $page_id = get_theme_mod("brave_page_{$type}");
    if ($page_id) {
        return get_permalink($page_id);
    }

    $template_map = array(
        'moments' => 'page-templates/page-moments.php',
        'memories' => 'page-templates/page-memories.php',
        'notes' => 'page-templates/page-notes.php',
        'blessing' => 'page-templates/page-blessing.php',
        'about' => 'page-templates/page-about.php',
    );

    if (isset($template_map[$type])) {
        $page_id = brave_get_page_id_by_template($template_map[$type]);

        if ($page_id) {
            return get_permalink($page_id);
        }
    }

    $fallback_path_map = array(
        'about' => 'about-us',
    );

    $path = isset($fallback_path_map[$type]) ? $fallback_path_map[$type] : $type;

    return home_url("/{$path}/");
}

/**
 * 获取页脚导航默认配置。
 *
 * @return array
 */
function brave_get_footer_nav_defaults() {
    return array(
        'home' => array(
            'label' => __('首页', 'brave-love'),
            'url' => home_url('/'),
        ),
        'about' => array(
            'label' => __('关于我们', 'brave-love'),
            'url' => brave_get_page_link('about'),
        ),
        'moments' => array(
            'label' => __('点点滴滴', 'brave-love'),
            'url' => brave_get_page_link('moments'),
        ),
        'lists' => array(
            'label' => __('恋爱清单', 'brave-love'),
            'url' => brave_get_page_link('lists'),
        ),
        'memories' => array(
            'label' => __('甜蜜相册', 'brave-love'),
            'url' => brave_get_page_link('memories'),
        ),
        'notes' => array(
            'label' => __('随笔说说', 'brave-love'),
            'url' => brave_get_page_link('notes'),
        ),
        'blessing' => array(
            'label' => __('祝福留言', 'brave-love'),
            'url' => brave_get_page_link('blessing'),
        ),
    );
}

/**
 * 获取页脚导航项。
 *
 * @return array
 */
function brave_get_footer_nav_items() {
    $defaults = brave_get_footer_nav_defaults();
    $items = array();

    foreach ($defaults as $key => $default) {
        $custom_label = trim((string) get_theme_mod("brave_footer_nav_{$key}_label", ''));
        $custom_url = trim((string) get_theme_mod("brave_footer_nav_{$key}_url", ''));
        $label = '' !== $custom_label ? $custom_label : $default['label'];
        $url = '' !== $custom_url ? $custom_url : $default['url'];

        if ('' === $label || '' === $url) {
            continue;
        }

        $items[] = array(
            'key' => $key,
            'label' => $label,
            'url' => $url,
        );
    }

    return $items;
}

/**
 * 获取恋爱起始日期时间
 *
 * 兼容旧版本仅保存日期的配置项。
 *
 * @return string
 */
function brave_get_love_start_datetime() {
    $start_datetime = get_theme_mod('brave_love_start_datetime', '');
    if (!empty($start_datetime)) {
        return $start_datetime;
    }

    return get_theme_mod('brave_love_start_date', '2020-01-01 00:00');
}

/**
 * 获取主题选项
 */
function brave_get_option($key, $default = '') {
    return get_theme_mod("brave_{$key}", $default);
}


/**
 * 获取随笔说说的所有年份
 *
 * @return array 年份数组
 */
function brave_get_note_years() {
    global $wpdb;
    
    $years = $wpdb->get_col("
        SELECT DISTINCT YEAR(post_date) as year
        FROM {$wpdb->posts}
        WHERE post_type = 'note'
        AND post_status = 'publish'
        ORDER BY year DESC
    ");
    
    return is_array($years) ? array_map('intval', array_filter($years, 'is_numeric')) : array();
}

/**
 * 获取指定年份的月份
 *
 * @param int $year 年份
 * @return array 月份数组
 */
function brave_get_note_months($year) {
    global $wpdb;
    
    $months = $wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT MONTH(post_date) as month
        FROM {$wpdb->posts}
        WHERE post_type = 'note'
        AND post_status = 'publish'
        AND YEAR(post_date) = %d
        ORDER BY month DESC
    ", $year));
    
    return is_array($months) ? array_map('intval', array_filter($months, 'is_numeric')) : array();
}

/**
 * 获取指定年月的天数
 *
 * @param int $year 年份
 * @param int $month 月份
 * @return array 日期数组
 */
function brave_get_note_days($year, $month) {
    global $wpdb;
    
    $days = $wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT DAY(post_date) as day
        FROM {$wpdb->posts}
        WHERE post_type = 'note'
        AND post_status = 'publish'
        AND YEAR(post_date) = %d
        AND MONTH(post_date) = %d
        ORDER BY day DESC
    ", $year, $month));
    
    return is_array($days) ? array_map('intval', array_filter($days, 'is_numeric')) : array();
}
