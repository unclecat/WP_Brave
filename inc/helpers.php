<?php
/**
 * 工具函数
 *
 * @package Brave_Love
 */

/**
 * 获取心情表情
 */
function brave_get_mood_emoji($mood) {
    $moods = array(
        'happy' => '😊',
        'excited' => '🤩',
        'romantic' => '🥰',
        'peaceful' => '😌',
        'touched' => '🥺',
        'miss' => '😢',
    );
    return isset($moods[$mood]) ? $moods[$mood] : '💕';
}

/**
 * 获取心情文字
 */
function brave_get_mood_text($mood) {
    $moods = array(
        'happy' => __('开心', 'brave-love'),
        'excited' => __('兴奋', 'brave-love'),
        'romantic' => __('浪漫', 'brave-love'),
        'peaceful' => __('平静', 'brave-love'),
        'touched' => __('感动', 'brave-love'),
        'miss' => __('想念', 'brave-love'),
    );
    return isset($moods[$mood]) ? $moods[$mood] : '';
}

/**
 * 获取恋爱清单完成进度
 */
function brave_get_list_progress() {
    $total = wp_count_posts('love_list')->publish;
    
    $done_args = array(
        'post_type' => 'love_list',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_is_done',
                'value' => '1',
                'compare' => '=',
            ),
        ),
        'fields' => 'ids',
    );
    $done = count(get_posts($done_args));
    
    return array(
        'total' => $total,
        'done' => $done,
        'percentage' => $total > 0 ? round(($done / $total) * 100) : 0,
    );
}

/**
 * 获取说说配图
 */
function brave_get_note_images($post_id, $size = 'medium') {
    $images = get_post_meta($post_id, '_note_images', true);
    if (!is_array($images)) {
        return array();
    }
    
    $urls = array();
    foreach ($images as $image_id) {
        $url = wp_get_attachment_image_url($image_id, $size);
        if ($url) {
            $urls[] = array(
                'id' => $image_id,
                'url' => $url,
                'thumb' => wp_get_attachment_image_url($image_id, 'thumbnail'),
            );
        }
    }
    return $urls;
}

/**
 * 获取相册年份列表（用于筛选）
 */
function brave_get_memory_years() {
    global $wpdb;
    
    $years = $wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT YEAR(meta_value) as year
        FROM {$wpdb->postmeta}
        WHERE meta_key = %s
        AND meta_value != ''
        ORDER BY year DESC
    ", '_memory_date'));
    
    return $years;
}

/**
 * 获取点滴年份列表（包括没有见面日期的文章）
 *
 * @return array 年份数组，按降序排列
 */
function brave_get_moment_years() {
    global $wpdb;
    
    // 先从 meta 获取年份
    $meta_years = $wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT YEAR(meta_value) as year
        FROM {$wpdb->postmeta}
        WHERE meta_key = %s
        AND meta_value != ''
        AND meta_value IS NOT NULL
        ORDER BY year DESC
    ", '_meet_date'));
    
    // 从发布日期获取年份（针对没有 _meet_date 的文章）
    $post_years = $wpdb->get_col("
        SELECT DISTINCT YEAR(p.post_date) as year
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_meet_date'
        WHERE p.post_type = 'moment'
        AND p.post_status = 'publish'
        AND (pm.meta_value IS NULL OR pm.meta_value = '')
        ORDER BY year DESC
    ");
    
    // 确保是数组
    $meta_years = is_array($meta_years) ? $meta_years : array();
    $post_years = is_array($post_years) ? $post_years : array();
    
    // 过滤掉 null 和空值，确保都是整数
    $meta_years = array_filter($meta_years, 'is_numeric');
    $post_years = array_filter($post_years, 'is_numeric');
    
    // 合并并去重
    $all_years = array_unique(array_map('intval', array_merge($meta_years, $post_years)));
    
    // 降序排序
    rsort($all_years, SORT_NUMERIC);
    
    return $all_years;
}

/**
 * 格式化日期差
 */
function brave_format_date_diff($date1, $date2 = null) {
    if ($date2 === null) {
        $date2 = current_time('Y-m-d');
    }
    
    $diff = abs(strtotime($date1) - strtotime($date2));
    $days = floor($diff / 86400);
    
    return $days;
}

/**
 * 判断是否是移动端
 */
function brave_is_mobile() {
    if (function_exists('wp_is_mobile')) {
        return wp_is_mobile();
    }
    
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = array('Mobile', 'Android', 'Silk/', 'Kindle', 'BlackBerry', 'Opera Mini', 'Opera Mobi');
    
    foreach ($mobile_agents as $agent) {
        if (strpos($user_agent, $agent) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * 获取主题页面链接
 */
function brave_get_page_link($type) {
    $page_id = get_theme_mod("brave_page_{$type}");
    if ($page_id) {
        return get_permalink($page_id);
    }
    return home_url("/{$type}/");
}

/**
 * 获取主题选项
 */
function brave_get_option($key, $default = '') {
    return get_theme_mod("brave_{$key}", $default);
}

/**
 * 清理 SVG
 */
function brave_kses_svg($svg) {
    $allowed_tags = array(
        'svg' => array(
            'xmlns' => true,
            'viewbox' => true,
            'width' => true,
            'height' => true,
            'fill' => true,
            'class' => true,
            'preserveaspectratio' => true,
        ),
        'path' => array(
            'd' => true,
            'fill' => true,
            'class' => true,
        ),
        'circle' => array(
            'cx' => true,
            'cy' => true,
            'r' => true,
            'fill' => true,
        ),
        'rect' => array(
            'x' => true,
            'y' => true,
            'width' => true,
            'height' => true,
            'fill' => true,
        ),
        'g' => array(
            'class' => true,
            'transform' => true,
        ),
        'use' => array(
            'href' => true,
            'x' => true,
            'y' => true,
        ),
        'defs' => array(),
    );
    
    return wp_kses($svg, $allowed_tags);
}
