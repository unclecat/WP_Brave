<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 旅行计划辅助函数。
 *
 * @package Brave_Love
 */

function brave_get_travel_status_options() {
    return array(
        'planning' => __('筹备中', 'brave-love'),
        'booked' => __('已订好', 'brave-love'),
        'ongoing' => __('旅途中', 'brave-love'),
        'completed' => __('已完成', 'brave-love'),
    );
}

function brave_get_travel_status_label($status) {
    $options = brave_get_travel_status_options();

    return $options[$status] ?? $options['planning'];
}

function brave_get_travel_status_tone($status) {
    $allowed = array('planning', 'booked', 'ongoing', 'completed');

    return in_array($status, $allowed, true) ? $status : 'planning';
}

function brave_get_travel_plan_meta($post_id) {
    $post_id = absint($post_id);
    $status = sanitize_key((string) get_post_meta($post_id, '_travel_status', true));

    return array(
        'destination' => trim((string) get_post_meta($post_id, '_travel_destination', true)),
        'start_date' => brave_sanitize_iso_date(get_post_meta($post_id, '_travel_start_date', true)),
        'end_date' => brave_sanitize_iso_date(get_post_meta($post_id, '_travel_end_date', true)),
        'status' => array_key_exists($status, brave_get_travel_status_options()) ? $status : 'planning',
        'keyword' => trim((string) get_post_meta($post_id, '_travel_keyword', true)),
        'departure_note' => trim((string) get_post_meta($post_id, '_travel_departure_note', true)),
        'tips_title' => trim((string) get_post_meta($post_id, '_travel_tips_title', true)),
        'tips' => trim((string) get_post_meta($post_id, '_travel_tips', true)),
        'checklist_title' => trim((string) get_post_meta($post_id, '_travel_checklist_title', true)),
        'checklist' => trim((string) get_post_meta($post_id, '_travel_checklist', true)),
    );
}

function brave_get_travel_plan_primary_date($travel_meta) {
    if (!is_array($travel_meta)) {
        return '';
    }

    $start_date = brave_sanitize_iso_date($travel_meta['start_date'] ?? '');
    if ('' !== $start_date) {
        return $start_date;
    }

    return brave_sanitize_iso_date($travel_meta['end_date'] ?? '');
}

function brave_get_travel_plan_days($post_id) {
    $stored_days = get_post_meta(absint($post_id), '_travel_days', true);

    if (!is_array($stored_days)) {
        return array();
    }

    $days = array();

    foreach ($stored_days as $stored_day) {
        if (!is_array($stored_day)) {
            continue;
        }

        $day = brave_get_travel_day_empty_template();
        $day['title'] = sanitize_text_field($stored_day['title'] ?? '');
        $day['date'] = brave_sanitize_iso_date($stored_day['date'] ?? '');
        $day['city'] = sanitize_text_field($stored_day['city'] ?? '');
        $day['weather'] = sanitize_text_field($stored_day['weather'] ?? '');
        $day['outfit'] = sanitize_textarea_field($stored_day['outfit'] ?? '');
        $day['hotel'] = sanitize_textarea_field($stored_day['hotel'] ?? '');
        $day['transport'] = sanitize_textarea_field($stored_day['transport'] ?? '');
        $day['spots'] = sanitize_textarea_field($stored_day['spots'] ?? '');
        $day['restaurants'] = sanitize_textarea_field($stored_day['restaurants'] ?? '');
        $day['notes'] = sanitize_textarea_field($stored_day['notes'] ?? '');

        $days[] = $day;
    }

    return array_values($days);
}

function brave_get_travel_day_empty_template() {
    return array(
        'title' => '',
        'date' => '',
        'city' => '',
        'weather' => '',
        'outfit' => '',
        'hotel' => '',
        'transport' => '',
        'spots' => '',
        'restaurants' => '',
        'notes' => '',
    );
}

function brave_get_travel_plan_summary($post) {
    $post = get_post($post);

    if (!$post instanceof WP_Post) {
        return '';
    }

    $excerpt = trim((string) $post->post_excerpt);
    if ('' !== $excerpt) {
        return $excerpt;
    }

    $content = trim((string) wp_strip_all_tags($post->post_content));

    if ('' === $content) {
        return '';
    }

    return wp_trim_words($content, 36);
}

function brave_get_travel_plan_first_day_overview($travel_days) {
    $travel_days = is_array($travel_days) ? array_values($travel_days) : array();
    $first_day = !empty($travel_days[0]) && is_array($travel_days[0]) ? $travel_days[0] : array();
    $title = trim((string) ($first_day['title'] ?? ''));
    $city = trim((string) ($first_day['city'] ?? ''));
    $weather = trim((string) ($first_day['weather'] ?? ''));
    $date_raw = trim((string) ($first_day['date'] ?? ''));
    $spot = brave_get_travel_first_line($first_day['spots'] ?? '');
    $restaurant = brave_get_travel_first_line($first_day['restaurants'] ?? '');
    $highlight_label = '' !== $spot ? __('重点安排', 'brave-love') : __('想吃的一顿', 'brave-love');
    $highlight_value = '' !== $spot ? $spot : $restaurant;
    $highlight_note = '';

    if ('' !== $spot && '' !== $restaurant) {
        $highlight_note = sprintf(
            /* translators: %s: first restaurant */
            __('顺路也想去：%s', 'brave-love'),
            $restaurant
        );
    }

    return array(
        'title' => $title,
        'city' => $city,
        'weather' => $weather,
        'date_raw' => $date_raw,
        'date' => '' !== $date_raw ? brave_format_travel_date($date_raw) : '',
        'hotel' => brave_get_travel_first_line($first_day['hotel'] ?? ''),
        'transport' => brave_get_travel_first_line($first_day['transport'] ?? ''),
        'spot' => $spot,
        'restaurant' => $restaurant,
        'highlight_label' => $highlight_label,
        'highlight_value' => $highlight_value,
        'highlight_note' => $highlight_note,
    );
}

function brave_sort_travel_plan_posts($posts) {
    if (!is_array($posts)) {
        return array();
    }

    $posts = array_values(array_filter($posts, static function ($post) {
        return $post instanceof WP_Post;
    }));

    if (count($posts) < 2) {
        return $posts;
    }

    $meta_cache = array();
    $timestamp_cache = array();

    usort($posts, static function ($left, $right) use (&$meta_cache, &$timestamp_cache) {
        $left_id = $left->ID;
        $right_id = $right->ID;

        if (!isset($meta_cache[$left_id])) {
            $meta_cache[$left_id] = brave_get_travel_plan_meta($left_id);
        }

        if (!isset($meta_cache[$right_id])) {
            $meta_cache[$right_id] = brave_get_travel_plan_meta($right_id);
        }

        $left_date = brave_get_travel_plan_primary_date($meta_cache[$left_id]);
        $right_date = brave_get_travel_plan_primary_date($meta_cache[$right_id]);

        if ('' === $left_date && '' !== $right_date) {
            return 1;
        }

        if ('' !== $left_date && '' === $right_date) {
            return -1;
        }

        if ($left_date !== $right_date) {
            return strcmp($left_date, $right_date);
        }

        if (!isset($timestamp_cache[$left_id])) {
            $timestamp_cache[$left_id] = (int) get_post_time('U', true, $left);
        }

        if (!isset($timestamp_cache[$right_id])) {
            $timestamp_cache[$right_id] = (int) get_post_time('U', true, $right);
        }

        if ($timestamp_cache[$left_id] !== $timestamp_cache[$right_id]) {
            return $timestamp_cache[$right_id] <=> $timestamp_cache[$left_id];
        }

        return $right_id <=> $left_id;
    });

    return $posts;
}

function brave_get_sorted_travel_plan_posts($args = array()) {
    $query_args = wp_parse_args(
        $args,
        array(
            'post_type' => 'travel_plan',
            'post_status' => 'publish',
            'numberposts' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
            'ignore_sticky_posts' => true,
        )
    );

    return brave_sort_travel_plan_posts(get_posts($query_args));
}

function brave_get_travel_duration_days($start_date, $end_date) {
    $start_date = brave_sanitize_iso_date($start_date);
    $end_date = brave_sanitize_iso_date($end_date);

    if ('' === $start_date && '' === $end_date) {
        return 0;
    }

    if ('' === $start_date) {
        $start_date = $end_date;
    }

    if ('' === $end_date) {
        $end_date = $start_date;
    }

    try {
        $start = new DateTimeImmutable($start_date);
        $end = new DateTimeImmutable($end_date);
    } catch (Exception $exception) {
        return 0;
    }

    if ($end < $start) {
        $end = $start;
    }

    return (int) $start->diff($end)->days + 1;
}

function brave_get_travel_duration_label($start_date, $end_date) {
    $days = brave_get_travel_duration_days($start_date, $end_date);

    if ($days <= 0) {
        return __('待定', 'brave-love');
    }

    return sprintf(
        /* translators: %d: number of days */
        _n('%d 天', '%d 天', $days, 'brave-love'),
        $days
    );
}

function brave_format_travel_date($date_value, $format = 'Y年n月j日') {
    $date_value = brave_sanitize_iso_date($date_value);

    if ('' === $date_value) {
        return __('待定', 'brave-love');
    }

    $timestamp = strtotime($date_value);

    return $timestamp ? wp_date($format, $timestamp) : $date_value;
}

function brave_format_travel_date_range($start_date, $end_date) {
    $start_date = brave_sanitize_iso_date($start_date);
    $end_date = brave_sanitize_iso_date($end_date);

    if ('' === $start_date && '' === $end_date) {
        return __('日期待定', 'brave-love');
    }

    if ('' === $start_date) {
        $start_date = $end_date;
    }

    if ('' === $end_date) {
        $end_date = $start_date;
    }

    if ($start_date === $end_date) {
        return brave_format_travel_date($start_date);
    }

    try {
        $start = new DateTimeImmutable($start_date);
        $end = new DateTimeImmutable($end_date);
    } catch (Exception $exception) {
        return $start_date . ' - ' . $end_date;
    }

    if ($end < $start) {
        $end = $start;
    }

    return wp_date('Y年n月j日', $start->getTimestamp()) . ' - ' . wp_date('Y年n月j日', $end->getTimestamp());
}

function brave_get_travel_first_line($value) {
    $items = brave_split_multiline_items($value);

    if (!empty($items[0])) {
        return $items[0];
    }

    $value = trim((string) $value);

    if ('' === $value) {
        return '';
    }

    $lines = preg_split('/\r\n|\r|\n/', $value);

    return trim((string) ($lines[0] ?? ''));
}

function brave_split_multiline_items($value) {
    $value = trim((string) $value);

    if ('' === $value) {
        return array();
    }

    $lines = preg_split('/\r\n|\r|\n/', $value);

    return array_values(array_filter(array_map('trim', $lines), static function ($line) {
        return '' !== $line;
    }));
}
