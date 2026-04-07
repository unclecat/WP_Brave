<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * QWeather 天气服务与 REST 输出。
 *
 * @package Brave_Love
 */

function brave_get_qweather_setting($name) {
    $env_value = getenv($name);
    if (is_string($env_value) && '' !== trim($env_value)) {
        return trim($env_value);
    }

    if (defined($name)) {
        $constant_value = constant($name);
        if (is_string($constant_value) && '' !== trim($constant_value)) {
            return trim($constant_value);
        }
    }

    return '';
}

function brave_get_qweather_config() {
    static $config = null;

    if (null !== $config) {
        return $config;
    }

    $api_host = brave_get_qweather_setting('QWEATHER_API_HOST');
    $api_key = brave_get_qweather_setting('QWEATHER_API_KEY');

    if ('' !== $api_host && !preg_match('#^https?://#i', $api_host)) {
        $api_host = 'https://' . $api_host;
    }

    $api_host = untrailingslashit($api_host);

    $config = array(
        'api_host' => $api_host,
        'api_key' => $api_key,
        'configured' => '' !== $api_host && '' !== $api_key,
    );

    return $config;
}

function brave_qweather_city_key($city) {
    return md5(($city['lat'] ?? '') . ':' . ($city['lon'] ?? ''));
}

function brave_qweather_cache_key($type, $city) {
    return 'brv_qw_' . sanitize_key($type) . '_' . brave_qweather_city_key($city);
}

function brave_qweather_backup_option_key($cache_key) {
    return 'brv_qw_backup_' . $cache_key;
}

function brave_qweather_get_location_string($city) {
    return number_format((float) $city['lon'], 2, '.', '') . ',' . number_format((float) $city['lat'], 2, '.', '');
}

function brave_qweather_get_air_location_path($city) {
    return number_format((float) $city['lat'], 2, '.', '') . '/' . number_format((float) $city['lon'], 2, '.', '');
}

function brave_qweather_request($path, $query_args = array()) {
    $config = brave_get_qweather_config();
    if (empty($config['configured'])) {
        return new WP_Error('brave_qweather_not_configured', __('QWeather 凭证未配置完整。', 'brave-love'));
    }

    $request_url = add_query_arg($query_args, $config['api_host'] . $path);
    $headers = array(
        'Accept' => 'application/json',
        'X-QW-Api-Key' => $config['api_key'],
    );

    $response = wp_remote_get($request_url, array(
        'timeout' => 12,
        'headers' => $headers,
        'user-agent' => 'Brave-Love/' . BRAVE_VERSION . '; ' . home_url('/'),
    ));

    if (is_wp_error($response)) {
        return $response;
    }

    $status_code = (int) wp_remote_retrieve_response_code($response);
    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (200 !== $status_code || !is_array($data)) {
        $detail = is_array($data) ? ($data['error']['detail'] ?? '') : '';
        $message = sprintf(__('QWeather 请求失败（HTTP %d）。', 'brave-love'), $status_code);

        if ('' !== $detail) {
            $message .= ' ' . $detail;
        }

        return new WP_Error(
            'brave_qweather_http_error',
            $message
        );
    }

    if (isset($data['code']) && '200' !== (string) $data['code']) {
        return new WP_Error(
            'brave_qweather_api_error',
            sprintf(__('QWeather 返回错误代码：%s。', 'brave-love'), $data['code'])
        );
    }

    return $data;
}

function brave_qweather_get_cached_response($type, $city, $path, $query_args, $ttl, $optional = false) {
    $cache_key = brave_qweather_cache_key($type, $city);
    $cached = get_transient($cache_key);

    if (is_array($cached) && isset($cached['data'])) {
        return array(
            'data' => $cached['data'],
            'stale' => false,
            'cached_at' => (int) ($cached['cached_at'] ?? time()),
        );
    }

    $fresh = brave_qweather_request($path, $query_args);

    if (!is_wp_error($fresh)) {
        $payload = array(
            'cached_at' => time(),
            'data' => $fresh,
        );
        set_transient($cache_key, $payload, $ttl);
        update_option(brave_qweather_backup_option_key($cache_key), $payload, false);

        return array(
            'data' => $fresh,
            'stale' => false,
            'cached_at' => $payload['cached_at'],
        );
    }

    $backup = get_option(brave_qweather_backup_option_key($cache_key), array());
    if (is_array($backup) && isset($backup['data'])) {
        return array(
            'data' => $backup['data'],
            'stale' => true,
            'cached_at' => (int) ($backup['cached_at'] ?? time()),
            'error' => $fresh->get_error_message(),
        );
    }

    if ($optional) {
        return array(
            'data' => null,
            'stale' => false,
            'cached_at' => 0,
            'error' => $fresh->get_error_message(),
        );
    }

    return $fresh;
}

function brave_qweather_to_float($value) {
    return is_numeric($value) ? (float) $value : null;
}

function brave_qweather_to_int($value) {
    return is_numeric($value) ? (int) round((float) $value) : null;
}

function brave_qweather_format_time_value($value) {
    if (empty($value)) {
        return '';
    }

    try {
        $date = new DateTime($value);
        return $date->format(DateTime::ATOM);
    } catch (Exception $exception) {
        if (preg_match('/^\d{2}:\d{2}$/', (string) $value)) {
            return gmdate('Y-m-d') . 'T' . $value . ':00+00:00';
        }

        return '';
    }
}

function brave_qweather_format_clock($value) {
    if (empty($value)) {
        return '--:--';
    }

    try {
        $date = new DateTime($value);
        return $date->format('H:i');
    } catch (Exception $exception) {
        return preg_match('/^\d{2}:\d{2}$/', (string) $value) ? $value : '--:--';
    }
}

function brave_qweather_is_daytime($updated_at, $sunrise, $sunset, $icon_code) {
    if (is_string($icon_code) && preg_match('/^(15|35)/', $icon_code)) {
        return false;
    }

    if (empty($updated_at) || empty($sunrise) || empty($sunset)) {
        return true;
    }

    try {
        $current = new DateTime($updated_at);
        $day = $current->format('Y-m-d');
        $sunrise_time = new DateTime($day . 'T' . $sunrise . $current->format('P'));
        $sunset_time = new DateTime($day . 'T' . $sunset . $current->format('P'));

        return $current >= $sunrise_time && $current < $sunset_time;
    } catch (Exception $exception) {
        return true;
    }
}

function brave_qweather_contains($haystack, $needle) {
    if (function_exists('mb_strpos')) {
        return false !== mb_strpos((string) $haystack, (string) $needle);
    }

    return false !== strpos((string) $haystack, (string) $needle);
}

function brave_qweather_match_weather_visual($text, $is_day) {
    $text = (string) $text;

    if (brave_qweather_contains($text, '雷')) {
        return array('icon' => '⛈️', 'type' => 'stormy');
    }

    if (brave_qweather_contains($text, '雪') || brave_qweather_contains($text, '冰雹')) {
        return array('icon' => '🌨️', 'type' => 'snowy');
    }

    if (brave_qweather_contains($text, '雨')) {
        return array('icon' => brave_qweather_contains($text, '阵') ? '🌦️' : '🌧️', 'type' => 'rainy');
    }

    if (brave_qweather_contains($text, '雾') || brave_qweather_contains($text, '霾') || brave_qweather_contains($text, '沙') || brave_qweather_contains($text, '尘')) {
        return array('icon' => '🌫️', 'type' => 'cloudy');
    }

    if (brave_qweather_contains($text, '阴')) {
        return array('icon' => '☁️', 'type' => 'cloudy');
    }

    if (brave_qweather_contains($text, '云')) {
        return array('icon' => '⛅', 'type' => 'cloudy');
    }

    return array(
        'icon' => $is_day ? '☀️' : '🌙',
        'type' => 'sunny',
    );
}

function brave_qweather_get_tone_from_aqi($value) {
    if (!is_numeric($value)) {
        return 'unknown';
    }

    $value = (int) $value;

    if ($value <= 50) {
        return 'good';
    }

    if ($value <= 100) {
        return 'moderate';
    }

    if ($value <= 150) {
        return 'sensitive';
    }

    if ($value <= 200) {
        return 'unhealthy';
    }

    return 'hazardous';
}

function brave_qweather_get_warning_tone($severity_color) {
    $severity_color = strtolower((string) $severity_color);

    if (in_array($severity_color, array('green', 'blue', 'minor'), true)) {
        return 'moderate';
    }

    if (in_array($severity_color, array('yellow', 'moderate'), true)) {
        return 'sensitive';
    }

    if (in_array($severity_color, array('orange', 'severe'), true)) {
        return 'unhealthy';
    }

    if (in_array($severity_color, array('red', 'black', 'extreme'), true)) {
        return 'hazardous';
    }

    return 'unknown';
}

function brave_qweather_get_uv_tone($value) {
    if (!is_numeric($value)) {
        return 'unknown';
    }

    $value = (float) $value;

    if ($value < 3) {
        return 'good';
    }

    if ($value < 6) {
        return 'moderate';
    }

    if ($value < 8) {
        return 'sensitive';
    }

    if ($value < 11) {
        return 'unhealthy';
    }

    return 'hazardous';
}

function brave_qweather_find_primary_aqi_index($indexes) {
    if (!is_array($indexes) || empty($indexes)) {
        return array();
    }

    foreach ($indexes as $index) {
        if (!empty($index['aqi'])) {
            return $index;
        }
    }

    return $indexes[0];
}

function brave_qweather_find_primary_pollutant_data($pollutants, $primary_code) {
    if (!is_array($pollutants) || '' === $primary_code) {
        return array();
    }

    foreach ($pollutants as $pollutant) {
        if (($pollutant['code'] ?? '') === $primary_code) {
            return $pollutant;
        }
    }

    return array();
}

function brave_qweather_pick_main_alert($warnings) {
    if (!is_array($warnings) || empty($warnings)) {
        return array();
    }

    $severity_weight = array(
        'minor' => 1,
        'moderate' => 2,
        'severe' => 3,
        'extreme' => 4,
    );
    $color_weight = array(
        'green' => 1,
        'blue' => 2,
        'yellow' => 3,
        'orange' => 4,
        'red' => 5,
        'black' => 6,
    );

    usort($warnings, function ($left, $right) use ($severity_weight, $color_weight) {
        $left_weight = $severity_weight[strtolower((string) ($left['severity'] ?? ''))] ?? 0;
        $right_weight = $severity_weight[strtolower((string) ($right['severity'] ?? ''))] ?? 0;

        if ($left_weight !== $right_weight) {
            return $right_weight <=> $left_weight;
        }

        $left_color = $color_weight[strtolower((string) ($left['severityColor'] ?? ''))] ?? 0;
        $right_color = $color_weight[strtolower((string) ($right['severityColor'] ?? ''))] ?? 0;

        if ($left_color !== $right_color) {
            return $right_color <=> $left_color;
        }

        return strcmp((string) ($right['pubTime'] ?? ''), (string) ($left['pubTime'] ?? ''));
    });

    return $warnings[0];
}

function brave_qweather_group_indices($daily_indices) {
    $grouped = array();

    if (!is_array($daily_indices)) {
        return $grouped;
    }

    foreach ($daily_indices as $item) {
        $type = (string) ($item['type'] ?? '');
        if ('' === $type) {
            continue;
        }

        $grouped[$type] = array(
            'name' => $item['name'] ?? '',
            'level' => $item['level'] ?? '',
            'category' => $item['category'] ?? '',
            'text' => $item['text'] ?? '',
        );
    }

    return $grouped;
}

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

function brave_qweather_build_clothing_data($indices, $weather_payload) {
    $tags = array();
    $copy_parts = array();

    $dressing = $indices['3'] ?? array();
    $sport = $indices['1'] ?? array();
    $allergy = $indices['7'] ?? array();
    $temp_max = $weather_payload['tempMax'] ?? null;
    $temp_min = $weather_payload['tempMin'] ?? null;

    if (!empty($dressing['category'])) {
        brave_qweather_add_tag($tags, '穿衣 ' . $dressing['category'], 'official');

        if (false !== strpos((string) $dressing['category'], '热')) {
            $copy_parts[] = '今天整体偏暖，轻薄一点会更自在。';
        } elseif (false !== strpos((string) $dressing['category'], '冷')) {
            $copy_parts[] = '今天有些凉，出门记得多加一层。';
        } elseif (false !== strpos((string) $dressing['category'], '舒适')) {
            $copy_parts[] = '今天体感比较舒服，按平时最喜欢的搭配穿就很好。';
        } else {
            $copy_parts[] = '今天穿搭以舒服、方便活动为主就好。';
        }
    } elseif (is_numeric($temp_max) || is_numeric($temp_min)) {
        $copy_parts[] = '今天温差有变化，出门前给自己留一件方便增减的外套会更稳妥。';
    } else {
        $copy_parts[] = '今天穿搭以舒服、方便活动为主就好。';
    }

    if (!empty($sport['category'])) {
        brave_qweather_add_tag($tags, '运动 ' . $sport['category'], 'official');
    }

    if (!empty($allergy['category']) && !brave_qweather_contains($allergy['category'], '无')) {
        brave_qweather_add_tag($tags, '过敏 ' . $allergy['category'], 'official');
    }

    if (!empty($weather_payload['minutely']['summary'])) {
        brave_qweather_add_tag($tags, '短时降雨提醒', 'extra');
        $copy_parts[] = $weather_payload['minutely']['summary'] . '，临出门前看一眼天空会更安心。';
    }

    if (!empty($weather_payload['warning']['badge'])) {
        brave_qweather_add_tag($tags, '注意预警', 'extra');
        $copy_parts[] = '还有 ' . $weather_payload['warning']['badge'] . '，今天更适合留一点出行余量。';
    }

    if (!empty($weather_payload['aqi']) && (int) $weather_payload['aqi'] > 100) {
        brave_qweather_add_tag($tags, '空气一般', 'extra');
        $copy_parts[] = '空气不算太通透，久待户外的话口罩最好随身带着。';
    }

    return array(
        'tags' => $tags,
        'copy' => implode(' ', $copy_parts),
    );
}

function brave_qweather_build_health_advice($aqi_index, $warning) {
    $general = $aqi_index['health']['advice']['generalPopulation'] ?? '';
    $sensitive = $aqi_index['health']['advice']['sensitivePopulation'] ?? '';

    if ('' === $general) {
        $general = '空气情况暂时平稳，正常安排今天的出行就好。';
    }

    if ('' === $sensitive) {
        $sensitive = '如果你今天对空气或天气更敏感，记得给自己留一点缓冲时间。';
    }

    if (!empty($warning['headline'])) {
        $general = '当前有天气预警，' . $general;
        $sensitive = '当前有天气预警，' . $sensitive;
    }

    return array(
        'general' => $general,
        'sensitive' => $sensitive,
    );
}

function brave_qweather_normalize_city_weather($city, $index) {
    $location = brave_qweather_get_location_string($city);
    $lang = 'zh-hans';

    $now = brave_qweather_get_cached_response('now', $city, '/v7/weather/now', array(
        'location' => $location,
        'lang' => $lang,
    ), 20 * MINUTE_IN_SECONDS);
    $hourly = brave_qweather_get_cached_response('hourly24', $city, '/v7/weather/24h', array(
        'location' => $location,
        'lang' => $lang,
    ), 45 * MINUTE_IN_SECONDS);
    $daily = brave_qweather_get_cached_response('daily3', $city, '/v7/weather/3d', array(
        'location' => $location,
        'lang' => $lang,
    ), 6 * HOUR_IN_SECONDS);

    foreach (array($now, $hourly, $daily) as $required_result) {
        if (is_wp_error($required_result)) {
            return array(
                'index' => $index,
                'name' => $city['name'],
                'status' => 'error',
                'message' => $required_result->get_error_message(),
            );
        }
    }

    $indices = brave_qweather_get_cached_response('indices', $city, '/v7/indices/1d', array(
        'location' => $location,
        'lang' => $lang,
        'type' => '1,3,5,6,7,9,10',
    ), 8 * HOUR_IN_SECONDS, true);
    $air = brave_qweather_get_cached_response('air', $city, '/airquality/v1/current/' . brave_qweather_get_air_location_path($city), array(
        'lang' => $lang,
    ), 45 * MINUTE_IN_SECONDS, true);
    $warning = brave_qweather_get_cached_response('warning', $city, '/v7/warning/now', array(
        'location' => $location,
        'lang' => $lang,
    ), 10 * MINUTE_IN_SECONDS, true);
    $minutely = brave_qweather_get_cached_response('minutely', $city, '/v7/minutely/5m', array(
        'location' => $location,
        'lang' => $lang,
    ), 10 * MINUTE_IN_SECONDS, true);

    $now_data = $now['data']['now'] ?? array();
    $hourly_items = $hourly['data']['hourly'] ?? array();
    $daily_today = $daily['data']['daily'][0] ?? array();
    $sunrise = $daily_today['sunrise'] ?? '';
    $sunset = $daily_today['sunset'] ?? '';
    $updated_at = $now_data['obsTime'] ?? '';
    $icon_code = (string) ($now_data['icon'] ?? '');
    $is_day = brave_qweather_is_daytime($updated_at, $sunrise, $sunset, $icon_code);
    $visual = brave_qweather_match_weather_visual($now_data['text'] ?? '', $is_day);

    $hourly_trend = array();
    $precipitation_max = 0;
    if (is_array($hourly_items)) {
        foreach (array_slice($hourly_items, 0, 6) as $hourly_item) {
            $hour_precip = brave_qweather_to_int($hourly_item['pop'] ?? null);
            $precipitation_max = max($precipitation_max, (int) $hour_precip);
            $hour_visual = brave_qweather_match_weather_visual($hourly_item['text'] ?? '', true);
            $hourly_trend[] = array(
                'time' => brave_qweather_format_clock($hourly_item['fxTime'] ?? ''),
                'icon' => $hour_visual['icon'],
                'desc' => $hourly_item['text'] ?? '',
                'temp' => brave_qweather_to_int($hourly_item['temp'] ?? null),
                'precip' => $hour_precip,
            );
        }
    }

    $indices_grouped = brave_qweather_group_indices($indices['data']['daily'] ?? array());
    $uv_index = brave_qweather_to_float($daily_today['uvIndex'] ?? ($indices_grouped['5']['level'] ?? null));
    $uv_label = $indices_grouped['5']['category'] ?? '暂无';
    $uv_text = $indices_grouped['5']['text'] ?? '';

    $warning_items = $warning['data']['warning'] ?? array();
    $main_warning = brave_qweather_pick_main_alert(is_array($warning_items) ? $warning_items : array());
    $warning_badge = '';

    if (!empty($main_warning)) {
        $badge_parts = array();
        if (!empty($main_warning['severityColor'])) {
            $color_map = array(
                'blue' => '蓝色',
                'yellow' => '黄色',
                'orange' => '橙色',
                'red' => '红色',
                'black' => '黑色',
                'green' => '绿色',
            );
            $badge_parts[] = $color_map[strtolower((string) $main_warning['severityColor'])] ?? $main_warning['severityColor'];
        }
        if (!empty($main_warning['typeName'])) {
            $badge_parts[] = $main_warning['typeName'];
        }
        $warning_badge = implode('', $badge_parts);
    }

    $aqi_index = brave_qweather_find_primary_aqi_index($air['data']['indexes'] ?? array());
    $aqi_value = brave_qweather_to_int($aqi_index['aqi'] ?? null);
    $primary_pollutant = brave_qweather_find_primary_pollutant_data(
        $air['data']['pollutants'] ?? array(),
        $aqi_index['primaryPollutant']['code'] ?? ''
    );
    $health_advice = brave_qweather_build_health_advice($aqi_index, $main_warning);

    $minutely_data = array(
        'summary' => '',
        'hasRain' => false,
    );
    if (!empty($minutely['data']['summary'])) {
        $minutely_data['summary'] = $minutely['data']['summary'];
    }
    if (!empty($minutely['data']['minutely']) && is_array($minutely['data']['minutely'])) {
        foreach ($minutely['data']['minutely'] as $minute_item) {
            if (brave_qweather_to_float($minute_item['precip'] ?? null) > 0) {
                $minutely_data['hasRain'] = true;
                break;
            }
        }
    }

    $payload = array(
        'index' => $index,
        'name' => $city['name'],
        'status' => 'ok',
        'stale' => !empty($now['stale']) || !empty($hourly['stale']) || !empty($daily['stale']) || !empty($indices['stale']) || !empty($air['stale']) || !empty($warning['stale']) || !empty($minutely['stale']),
        'temp' => brave_qweather_to_int($now_data['temp'] ?? null),
        'feels' => brave_qweather_to_int($now_data['feelsLike'] ?? null),
        'humidity' => brave_qweather_to_int($now_data['humidity'] ?? null),
        'wind' => brave_qweather_to_int($now_data['windSpeed'] ?? null),
        'windDir' => $now_data['windDir'] ?? '',
        'windScale' => $now_data['windScale'] ?? '',
        'code' => $icon_code,
        'icon' => $visual['icon'],
        'desc' => $now_data['text'] ?? '天气暂不可用',
        'weatherType' => $visual['type'],
        'isDay' => $is_day,
        'updatedAt' => brave_qweather_format_time_value($updated_at),
        'tempMax' => brave_qweather_to_int($daily_today['tempMax'] ?? null),
        'tempMin' => brave_qweather_to_int($daily_today['tempMin'] ?? null),
        'precipitationMax' => $precipitation_max,
        'sunrise' => brave_qweather_format_time_value($sunrise),
        'sunset' => brave_qweather_format_time_value($sunset),
        'hourlyTrend' => $hourly_trend,
        'aqi' => $aqi_value,
        'aqiDisplay' => null !== $aqi_value ? (string) $aqi_value : '--',
        'aqiTone' => brave_qweather_get_tone_from_aqi($aqi_value),
        'aqiLabel' => $aqi_index['category'] ?? '暂无',
        'primaryPollutant' => $aqi_index['primaryPollutant']['name'] ?? ($primary_pollutant['name'] ?? '暂无'),
        'primaryPollutantValue' => $primary_pollutant['concentration']['value'] ?? '',
        'primaryPollutantUnit' => $primary_pollutant['concentration']['unit'] ?? '',
        'healthAdvice' => $health_advice,
        'uvValue' => $uv_index,
        'uvMax' => is_numeric($uv_index) ? (string) (intval($uv_index) == $uv_index ? intval($uv_index) : round($uv_index, 1)) : '--',
        'uvLabel' => $uv_label,
        'uvTone' => brave_qweather_get_uv_tone($uv_index),
        'uvText' => $uv_text,
        'warning' => !empty($main_warning) ? array(
            'badge' => $warning_badge,
            'headline' => $main_warning['headline'] ?? ($main_warning['text'] ?? ''),
            'severityColor' => strtolower((string) ($main_warning['severityColor'] ?? '')),
            'tone' => brave_qweather_get_warning_tone($main_warning['severityColor'] ?? ($main_warning['severity'] ?? '')),
            'typeName' => $main_warning['typeName'] ?? '',
            'pubTime' => brave_qweather_format_time_value($main_warning['pubTime'] ?? ''),
            'effectiveTime' => brave_qweather_format_time_value($main_warning['effectiveTime'] ?? ''),
            'expireTime' => brave_qweather_format_time_value($main_warning['expireTime'] ?? ($main_warning['expiredTime'] ?? '')),
            'text' => $main_warning['text'] ?? '',
        ) : null,
        'minutely' => $minutely_data,
        'indices' => $indices_grouped,
    );

    $payload['clothing'] = brave_qweather_build_clothing_data($indices_grouped, $payload);

    return $payload;
}

function brave_get_home_weather_payload() {
    if (!function_exists('brave_is_weather_enabled') || !brave_is_weather_enabled()) {
        return array(
            'enabled' => false,
            'provider' => 'qweather',
            'cities' => array(),
        );
    }

    $config = brave_get_qweather_config();
    if (empty($config['configured'])) {
        return array(
            'enabled' => true,
            'provider' => 'qweather',
            'configured' => false,
            'cities' => array(),
            'message' => __('QWeather 尚未完成配置。', 'brave-love'),
        );
    }

    $cities = function_exists('brave_get_weather_cities') ? brave_get_weather_cities() : array();
    $payloads = array();

    foreach ($cities as $index => $city) {
        $payloads[] = brave_qweather_normalize_city_weather($city, $index);
    }

    return array(
        'enabled' => true,
        'configured' => true,
        'provider' => 'qweather',
        'generatedAt' => gmdate(DateTime::ATOM),
        'cities' => $payloads,
    );
}

function brave_register_weather_rest_routes() {
    register_rest_route('brave-love/v1', '/weather', array(
        'methods' => WP_REST_Server::READABLE,
        'permission_callback' => '__return_true',
        'callback' => function () {
            return rest_ensure_response(brave_get_home_weather_payload());
        },
    ));
}
add_action('rest_api_init', 'brave_register_weather_rest_routes');
