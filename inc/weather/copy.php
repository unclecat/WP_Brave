<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * QWeather 标签与贴心提醒文案生成。
 *
 * @package Brave_Love
 */

function brave_qweather_add_tag(&$tags, $label, $kind) {
    if ('' === trim((string) $label)) {
        return;
    }

    foreach ($tags as $tag) {
        if (($tag['label'] ?? '') === $label) {
            return;
        }
    }

    $tags[] = array(
        'label' => $label,
        'kind' => $kind,
    );
}

function brave_qweather_get_dressing_tag_label($category) {
    $category = (string) $category;

    return '' !== $category ? '穿衣 ' . $category : '';
}

function brave_qweather_get_sport_tag_label($category) {
    $category = (string) $category;

    return '' !== $category ? '运动 ' . $category : '';
}

function brave_qweather_get_uv_tag_label($index) {
    $category = (string) ($index['category'] ?? '');

    if ('' === $category) {
        return '';
    }

    if (brave_qweather_contains($category, '很弱') || brave_qweather_contains($category, '弱')) {
        return '';
    }

    return '紫外线 ' . $category;
}

function brave_qweather_get_allergy_tag_label($index) {
    $category = (string) ($index['category'] ?? '');

    if (
        '' === $category
        || brave_qweather_contains($category, '无')
        || brave_qweather_contains($category, '不易')
        || brave_qweather_contains($category, '少')
    ) {
        return '';
    }

    return '过敏 ' . $category;
}

function brave_qweather_get_comfort_tag_label($index) {
    $category = (string) ($index['category'] ?? '');

    return '' !== $category ? '体感 ' . $category : '';
}

function brave_qweather_get_cold_tag_label($index) {
    $category = (string) ($index['category'] ?? '');

    if (
        '' === $category
        || brave_qweather_contains($category, '少发')
        || brave_qweather_contains($category, '不易')
        || brave_qweather_contains($category, '无')
    ) {
        return '';
    }

    return '感冒 ' . $category;
}

function brave_qweather_get_air_tag_label($index, $weather_payload) {
    $category = (string) ($index['category'] ?? '');

    if ('' === $category) {
        return '';
    }

    if (!empty($weather_payload['aqi']) && (int) $weather_payload['aqi'] > 100) {
        return '空气 ' . $category;
    }

    if (brave_qweather_contains($category, '较差') || brave_qweather_contains($category, '差')) {
        return '空气 ' . $category;
    }

    return '';
}

function brave_qweather_should_notice_cold($cold_category) {
    $cold_category = (string) $cold_category;

    return '' !== $cold_category
        && !brave_qweather_contains($cold_category, '少发')
        && !brave_qweather_contains($cold_category, '不易')
        && !brave_qweather_contains($cold_category, '无');
}

function brave_qweather_get_clothing_base_copy($dressing_category, $cold_category, $temp_max, $temp_min) {
    $dressing_category = (string) $dressing_category;

    if ('' !== $dressing_category) {
        if (brave_qweather_contains($dressing_category, '热')) {
            return '今天偏暖，穿轻便一点就好';
        }

        if (brave_qweather_contains($dressing_category, '冷')) {
            return brave_qweather_should_notice_cold($cold_category)
                ? '今天有点凉，记得加件外套，注意保暖呀'
                : '今天有点凉，记得加件外套';
        }

        if (brave_qweather_contains($dressing_category, '舒适')) {
            return '今天挺舒服，穿喜欢的就好';
        }

        return '今天按舒服的方式穿就好';
    }

    if (is_numeric($temp_max) || is_numeric($temp_min)) {
        return '今天温差有点变化，穿方便增减的会更舒服';
    }

    return '今天按舒服的方式穿就好';
}

function brave_qweather_build_clothing_data($indices, $weather_payload) {
    $tags = array();
    $copy_extras = array();

    $dressing = $indices['3'] ?? array();
    $sport = $indices['1'] ?? array();
    $uv = $indices['5'] ?? array();
    $allergy = $indices['7'] ?? array();
    $comfort = $indices['8'] ?? array();
    $cold = $indices['9'] ?? array();
    $air_index = $indices['10'] ?? array();
    $temp_max = $weather_payload['tempMax'] ?? null;
    $temp_min = $weather_payload['tempMin'] ?? null;

    $dressing_category = (string) ($dressing['category'] ?? '');
    $comfort_category = (string) ($comfort['category'] ?? '');
    $cold_category = (string) ($cold['category'] ?? '');
    $uv_level = (int) ($uv['level'] ?? 0);
    $has_cold_notice = brave_qweather_should_notice_cold($cold_category);
    $copy_base = brave_qweather_get_clothing_base_copy($dressing_category, $cold_category, $temp_max, $temp_min);

    if ('' !== $dressing_category) {
        brave_qweather_add_tag($tags, brave_qweather_get_dressing_tag_label($dressing_category), 'official');
    }

    if ('' !== $comfort_category && $comfort_category !== $dressing_category) {
        brave_qweather_add_tag($tags, brave_qweather_get_comfort_tag_label($comfort), 'official');
    }

    if (!empty($sport['category'])) {
        brave_qweather_add_tag($tags, brave_qweather_get_sport_tag_label($sport['category']), 'official');
    }

    brave_qweather_add_tag($tags, brave_qweather_get_uv_tag_label($uv), 'official');
    brave_qweather_add_tag($tags, brave_qweather_get_allergy_tag_label($allergy), 'official');
    brave_qweather_add_tag($tags, brave_qweather_get_cold_tag_label($cold), 'official');
    brave_qweather_add_tag($tags, brave_qweather_get_air_tag_label($air_index, $weather_payload), 'extra');

    if (!empty($weather_payload['warning']['badge'])) {
        array_unshift($tags, array(
            'label' => '预警生效中',
            'kind' => 'extra',
        ));
        $copy_extras[] = array(
            'priority' => 100,
            'text' => '还有' . $weather_payload['warning']['badge'] . '，路上慢一点会更安心呀',
        );
    }

    if (!empty($weather_payload['aqi']) && (int) $weather_payload['aqi'] > 100) {
        $copy_extras[] = array(
            'priority' => 90,
            'text' => '空气不算太通透，久待户外记得戴好口罩呀',
        );
    }

    if ($uv_level >= 3) {
        $copy_extras[] = array(
            'priority' => $uv_level >= 4 ? 70 : 60,
            'text' => '出门记得防晒呀',
        );
    }

    if (!empty($allergy['category']) && !empty(brave_qweather_get_allergy_tag_label($allergy))) {
        $copy_extras[] = array(
            'priority' => brave_qweather_contains((string) $allergy['category'], '极易') ? 82 : 72,
            'text' => '容易过敏，记得洗手洗脸呀',
        );
    }

    if ($has_cold_notice && !brave_qweather_contains($dressing_category, '冷')) {
        $copy_extras[] = array(
            'priority' => 88,
            'text' => '早晚有温差，记得保暖呀',
        );
    }

    usort($copy_extras, function ($left, $right) {
        return ((int) ($right['priority'] ?? 0)) <=> ((int) ($left['priority'] ?? 0));
    });

    $extra_texts = array();
    foreach ($copy_extras as $item) {
        $text = trim((string) ($item['text'] ?? ''));
        if ('' === $text || in_array($text, $extra_texts, true)) {
            continue;
        }

        $extra_texts[] = $text;

        if (count($extra_texts) >= 2) {
            break;
        }
    }

    $copy = '宝宝💗，' . $copy_base;
    if (!empty($extra_texts)) {
        $copy .= '，' . implode('，', $extra_texts);
    }
    $copy .= '。';

    return array(
        'tags' => $tags,
        'copy' => $copy,
    );
}
