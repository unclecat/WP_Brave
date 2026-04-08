<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 点滴、清单与内容辅助函数。
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
 * 统计恋爱清单条目数量。
 *
 * 使用 found_posts 拿计数，避免把整批文章 ID 全部拉进内存。
 *
 * @param int       $term_id   可选的分类 ID，用于分类页统计。
 * @param bool|null $done_only 传 true 时只统计已完成条目。
 *
 * @return int
 */
function brave_count_love_list_items($term_id = 0, $done_only = null) {
    $term_id = absint($term_id);

    $args = array(
        'post_type' => 'love_list',
        'post_status' => 'publish',
        'posts_per_page' => 1,
        'paged' => 1,
        'fields' => 'ids',
        'ignore_sticky_posts' => true,
        'no_found_rows' => false,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    );

    if ($term_id > 0) {
        $args['tax_query'] = array(
            array(
                'taxonomy' => 'list_category',
                'field' => 'term_id',
                'terms' => $term_id,
            ),
        );
    }

    if (true === $done_only) {
        $args['meta_query'] = array(
            array(
                'key' => '_is_done',
                'value' => '1',
                'compare' => '=',
            ),
        );
    }

    $query = new WP_Query($args);

    return (int) $query->found_posts;
}

/**
 * 获取恋爱清单完成进度。
 *
 * @param int $term_id 可选的分类 ID，用于分类页统计。
 */
function brave_get_list_progress($term_id = 0) {
    $total = brave_count_love_list_items($term_id);
    $done = brave_count_love_list_items($term_id, true);

    return array(
        'total' => $total,
        'done' => $done,
        'percentage' => $total > 0 ? round(($done / $total) * 100) : 0,
    );
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
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = %s
        AND p.post_type = 'moment'
        AND p.post_status = 'publish'
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
 * 获取点滴的有效日期。
 *
 * 优先使用 _meet_date，缺失时回退到文章发布日期，
 * 保证展示、筛选、排序使用同一套日期口径。
 *
 * @param int $moment_id 点滴文章 ID
 * @return string 日期字符串
 */
function brave_get_moment_effective_date($moment_id) {
    $meet_date = get_post_meta($moment_id, '_meet_date', true);
    if (!empty($meet_date)) {
        return $meet_date;
    }

    return get_the_date('Y-m-d', $moment_id);
}

/**
 * 获取点滴有效日期对应的 SQL 表达式。
 *
 * @param string $post_alias 文章表别名
 * @param string $meta_alias 元数据表别名
 * @return string SQL 表达式
 */
function brave_get_moment_effective_date_sql($post_alias = 'p', $meta_alias = 'pm') {
    return "COALESCE(NULLIF({$meta_alias}.meta_value, ''), DATE({$post_alias}.post_date))";
}

/**
 * 按有效日期获取点滴文章 ID 列表。
 *
 * @param int $year 年份，0 表示全部
 * @return array 点滴文章 ID 数组
 */
function brave_get_moment_ids_by_effective_date($year = 0) {
    global $wpdb;

    $year = absint($year);
    $effective_date_sql = brave_get_moment_effective_date_sql('p', 'pm');

    $query = "
        SELECT p.ID
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm
            ON p.ID = pm.post_id
            AND pm.meta_key = %s
        WHERE p.post_type = 'moment'
        AND p.post_status = 'publish'
    ";

    if ($year > 0) {
        $query .= $wpdb->prepare(" AND YEAR({$effective_date_sql}) = %d", $year);
    }

    $query .= " ORDER BY {$effective_date_sql} DESC, p.ID DESC";

    $ids = $wpdb->get_col($wpdb->prepare($query, '_meet_date'));

    return is_array($ids) ? array_map('intval', $ids) : array();
}

/**
 * 获取指定年份的点滴文章 ID 列表。
 *
 * 使用 _meet_date 作为主日期；缺失时回退到文章发布日期，
 * 保证年份筛选与页面展示逻辑一致。
 *
 * @param int $year 年份
 * @return array 点滴文章 ID 数组
 */
function brave_get_moment_ids_by_year($year) {
    return brave_get_moment_ids_by_effective_date($year);
}

/**
 * 获取点滴摘要。
 *
 * 优先读取 WordPress 原生摘要，迁移期内回退到旧的自定义摘要字段。
 *
 * @param int|WP_Post $moment 点滴文章 ID 或对象
 * @return string
 */
function brave_get_moment_summary($moment) {
    $moment = get_post($moment);

    if (!$moment instanceof WP_Post || 'moment' !== $moment->post_type) {
        return '';
    }

    $summary = trim((string) $moment->post_excerpt);

    if ('' !== $summary) {
        return $summary;
    }

    return trim((string) get_post_meta($moment->ID, '_moment_summary', true));
}
