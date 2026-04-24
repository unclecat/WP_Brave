<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * QWeather 数据归一化与首页天气负载构建。
 *
 * @package Brave_Love
 */

function brave_qweather_find_primary_aqi_index($indexes) {
    if (!is_array($indexes) || empty($indexes)) {
        return array();
    }

    foreach ($indexes as $index) {
        if ('qaqi' === strtolower((string) ($index['code'] ?? '')) && !empty($index['aqi'])) {
            return $index;
        }
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

function brave_qweather_get_air_forecast_day_label($forecast_start_time, $updated_at) {
    if (empty($forecast_start_time)) {
        return '';
    }

    try {
        $forecast_date = new DateTime($forecast_start_time);
    } catch (Exception $exception) {
        return '';
    }

    try {
        $reference_date = !empty($updated_at) ? new DateTime($updated_at) : new DateTime('now', $forecast_date->getTimezone());
    } catch (Exception $exception) {
        $reference_date = new DateTime('now', $forecast_date->getTimezone());
    }

    $forecast_day = $forecast_date->format('Y-m-d');
    $reference_day = $reference_date->format('Y-m-d');

    if ($forecast_day === $reference_day) {
        return '今日';
    }

    $day_diff = (int) $reference_date->setTime(0, 0)->diff($forecast_date->setTime(0, 0))->format('%r%a');

    if (1 === $day_diff) {
        return '明日';
    }

    if (2 === $day_diff) {
        return '后天';
    }

    return $forecast_date->format('m/d');
}

function brave_qweather_pick_air_daily_forecast($days, $updated_at) {
    if (!is_array($days) || empty($days)) {
        return array();
    }

    $reference_day = '';
    if (!empty($updated_at)) {
        try {
            $reference_day = (new DateTime($updated_at))->format('Y-m-d');
        } catch (Exception $exception) {
            $reference_day = '';
        }
    }

    $fallback = array();

    foreach ($days as $day) {
        if (!is_array($day)) {
            continue;
        }

        $forecast_start = $day['forecastStartTime'] ?? '';
        $label = brave_qweather_get_air_forecast_day_label($forecast_start, $updated_at);
        $index = brave_qweather_find_primary_aqi_index($day['indexes'] ?? array());
        $aqi = brave_qweather_to_int($index['aqi'] ?? null);

        $candidate = array(
            'dayLabel' => $label,
            'aqi' => $aqi,
            'aqiDisplay' => null !== $aqi ? (string) $aqi : '--',
            'category' => $index['category'] ?? '',
            'tone' => brave_qweather_get_tone_from_aqi($aqi),
        );

        if (empty($fallback)) {
            $fallback = $candidate;
        }

        if ('' === $reference_day || empty($forecast_start)) {
            continue;
        }

        try {
            $forecast_day = (new DateTime($forecast_start))->format('Y-m-d');
        } catch (Exception $exception) {
            continue;
        }

        if ($forecast_day > $reference_day) {
            return $candidate;
        }
    }

    return $fallback;
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

function brave_qweather_get_response_data($response) {
    if (!is_array($response)) {
        return array();
    }

    return isset($response['data']) && is_array($response['data']) ? $response['data'] : array();
}

function brave_qweather_response_is_stale($response) {
    return is_array($response) && !empty($response['stale']);
}

function brave_qweather_get_required_city_weather_responses($city, $location, $lang) {
    return array(
        'now' => brave_qweather_get_cached_response('now', $city, '/v7/weather/now', array(
            'location' => $location,
            'lang' => $lang,
        ), 20 * MINUTE_IN_SECONDS),
        'hourly' => brave_qweather_get_cached_response('hourly24', $city, '/v7/weather/24h', array(
            'location' => $location,
            'lang' => $lang,
        ), 45 * MINUTE_IN_SECONDS),
        'daily' => brave_qweather_get_cached_response('daily3', $city, '/v7/weather/3d', array(
            'location' => $location,
            'lang' => $lang,
        ), 6 * HOUR_IN_SECONDS),
    );
}

function brave_qweather_get_required_city_weather_error($responses) {
    foreach ($responses as $response) {
        if (is_wp_error($response)) {
            return $response;
        }
    }

    return null;
}

function brave_qweather_get_city_warning_response($city, $location, $lang) {
    return brave_qweather_get_cached_response('warning', $city, '/v7/warning/now', array(
        'location' => $location,
        'lang' => $lang,
    ), 10 * MINUTE_IN_SECONDS, true);
}

function brave_qweather_build_warning_payload($warning_response) {
    $warning_data = brave_qweather_get_response_data($warning_response);
    $warning_items = is_array($warning_data['warning'] ?? null) ? $warning_data['warning'] : array();
    $main_warning = brave_qweather_pick_main_alert($warning_items);

    if (empty($main_warning)) {
        return null;
    }

    $warning_badge = '';
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

    if (!empty($badge_parts)) {
        $warning_badge = implode('', $badge_parts);
    }

    return array(
        'badge' => $warning_badge,
        'headline' => $main_warning['headline'] ?? ($main_warning['text'] ?? ''),
        'severityColor' => strtolower((string) ($main_warning['severityColor'] ?? '')),
        'tone' => brave_qweather_get_warning_tone($main_warning['severityColor'] ?? ($main_warning['severity'] ?? '')),
        'typeName' => $main_warning['typeName'] ?? '',
        'pubTime' => brave_qweather_format_time_value($main_warning['pubTime'] ?? ''),
        'effectiveTime' => brave_qweather_format_time_value($main_warning['effectiveTime'] ?? ''),
        'expireTime' => brave_qweather_format_time_value($main_warning['expireTime'] ?? ($main_warning['expiredTime'] ?? '')),
        'text' => $main_warning['text'] ?? '',
    );
}

function brave_qweather_build_base_city_weather_payload($city, $index, $required_responses, $warning_response) {
    $now_data = brave_qweather_get_response_data($required_responses['now']);
    $hourly_data = brave_qweather_get_response_data($required_responses['hourly']);
    $daily_data = brave_qweather_get_response_data($required_responses['daily']);

    $now_item = is_array($now_data['now'] ?? null) ? $now_data['now'] : array();
    $hourly_items = is_array($hourly_data['hourly'] ?? null) ? $hourly_data['hourly'] : array();
    $daily_items = is_array($daily_data['daily'] ?? null) ? $daily_data['daily'] : array();
    $daily_today = is_array($daily_items[0] ?? null) ? $daily_items[0] : array();

    $sunrise = $daily_today['sunrise'] ?? '';
    $sunset = $daily_today['sunset'] ?? '';
    $updated_at = $now_item['obsTime'] ?? '';
    $icon_code = (string) ($now_item['icon'] ?? '');
    $is_day = brave_qweather_is_daytime($updated_at, $sunrise, $sunset, $icon_code);
    $visual = brave_qweather_match_weather_visual($now_item['text'] ?? '', $is_day);

    $hourly_trend = array();
    $precipitation_max = 0;
    $trend_precipitation_max = 0;
    $reference_day = '';
    $matched_reference_day = false;

    if (!empty($updated_at)) {
        try {
            $reference_day = (new DateTime($updated_at))->format('Y-m-d');
        } catch (Exception $exception) {
            $reference_day = '';
        }
    }

    foreach ($hourly_items as $hourly_index => $hourly_item) {
        if (!is_array($hourly_item)) {
            continue;
        }

        $hour_precip = brave_qweather_to_int($hourly_item['pop'] ?? null);
        $hour_precip_value = (int) $hour_precip;

        if ($hourly_index < 6) {
            $trend_precipitation_max = max($trend_precipitation_max, $hour_precip_value);
            $hour_visual = brave_qweather_match_weather_visual($hourly_item['text'] ?? '', true);
            $hourly_trend[] = array(
                'time' => brave_qweather_format_clock($hourly_item['fxTime'] ?? ''),
                'icon' => $hour_visual['icon'],
                'desc' => $hourly_item['text'] ?? '',
                'temp' => brave_qweather_to_int($hourly_item['temp'] ?? null),
                'precip' => $hour_precip,
            );
        }

        if ('' === $reference_day || empty($hourly_item['fxTime'])) {
            continue;
        }

        try {
            $hour_day = (new DateTime($hourly_item['fxTime']))->format('Y-m-d');
        } catch (Exception $exception) {
            $hour_day = '';
        }

        if ($hour_day === $reference_day) {
            $matched_reference_day = true;
            $precipitation_max = max($precipitation_max, $hour_precip_value);
        }
    }

    if (!$matched_reference_day) {
        $precipitation_max = $trend_precipitation_max;
    }

    return array(
        'index' => $index,
        'name' => $city['name'],
        'status' => 'ok',
        'detailLoaded' => false,
        'stale' => brave_qweather_response_is_stale($required_responses['now'])
            || brave_qweather_response_is_stale($required_responses['hourly'])
            || brave_qweather_response_is_stale($required_responses['daily'])
            || brave_qweather_response_is_stale($warning_response),
        'temp' => brave_qweather_to_int($now_item['temp'] ?? null),
        'feels' => brave_qweather_to_int($now_item['feelsLike'] ?? null),
        'humidity' => brave_qweather_to_int($now_item['humidity'] ?? null),
        'wind' => brave_qweather_to_int($now_item['windSpeed'] ?? null),
        'windDir' => $now_item['windDir'] ?? '',
        'windScale' => $now_item['windScale'] ?? '',
        'code' => $icon_code,
        'icon' => $visual['icon'],
        'desc' => $now_item['text'] ?? '天气暂不可用',
        'weatherType' => $visual['type'],
        'isDay' => $is_day,
        'updatedAt' => brave_qweather_format_time_value($updated_at),
        'tempMax' => brave_qweather_to_int($daily_today['tempMax'] ?? null),
        'tempMin' => brave_qweather_to_int($daily_today['tempMin'] ?? null),
        'precipitationMax' => $precipitation_max,
        'sunrise' => brave_qweather_format_time_value($sunrise),
        'sunset' => brave_qweather_format_time_value($sunset),
        'hourlyTrend' => $hourly_trend,
        'warning' => brave_qweather_build_warning_payload($warning_response),
    );
}

function brave_qweather_add_city_weather_details($payload, $city, $location, $lang) {
    $indices = brave_qweather_get_cached_response('indices_v2', $city, '/v7/indices/1d', array(
        'location' => $location,
        'lang' => $lang,
        'type' => '1,3,5,7,8,9,10',
    ), 8 * HOUR_IN_SECONDS, true);
    $air = brave_qweather_get_cached_response('air', $city, '/airquality/v1/current/' . brave_qweather_get_air_location_path($city), array(
        'lang' => $lang,
    ), 45 * MINUTE_IN_SECONDS, true);
    $air_daily = brave_qweather_get_cached_response('air_daily', $city, '/airquality/v1/daily/' . brave_qweather_get_air_location_path($city), array(
        'lang' => $lang,
        'localTime' => 'true',
    ), 6 * HOUR_IN_SECONDS, true);

    $indices_data = brave_qweather_get_response_data($indices);
    $air_data = brave_qweather_get_response_data($air);
    $air_daily_data = brave_qweather_get_response_data($air_daily);

    $indices_daily = is_array($indices_data['daily'] ?? null) ? $indices_data['daily'] : array();
    $air_indexes = is_array($air_data['indexes'] ?? null) ? $air_data['indexes'] : array();
    $air_pollutants = is_array($air_data['pollutants'] ?? null) ? $air_data['pollutants'] : array();
    $air_days = is_array($air_daily_data['days'] ?? null) ? $air_daily_data['days'] : array();

    $detail_available = !empty($indices_daily) || !empty($air_indexes) || !empty($air_pollutants) || !empty($air_days);

    if (!$detail_available) {
        $detail_errors = array_values(array_filter(array(
            $indices['error'] ?? '',
            $air['error'] ?? '',
            $air_daily['error'] ?? '',
        )));

        if (!empty($detail_errors)) {
            $payload['detailMessage'] = $detail_errors[0];
        }

        return $payload;
    }

    $indices_grouped = brave_qweather_group_indices($indices_daily);
    $uv_index = brave_qweather_to_float($indices_grouped['5']['level'] ?? null);
    $uv_label = $indices_grouped['5']['category'] ?? '暂无';
    $uv_text = $indices_grouped['5']['text'] ?? '';

    $aqi_index = brave_qweather_find_primary_aqi_index($air_indexes);
    $aqi_value = brave_qweather_to_int($aqi_index['aqi'] ?? null);
    $primary_pollutant = brave_qweather_find_primary_pollutant_data(
        $air_pollutants,
        $aqi_index['primaryPollutant']['code'] ?? ''
    );
    $air_daily_forecast = brave_qweather_pick_air_daily_forecast($air_days, $payload['updatedAt'] ?? '');

    $payload['stale'] = !empty($payload['stale'])
        || brave_qweather_response_is_stale($indices)
        || brave_qweather_response_is_stale($air)
        || brave_qweather_response_is_stale($air_daily);

    $payload['aqi'] = $aqi_value;
    $payload['aqiDisplay'] = null !== $aqi_value ? (string) $aqi_value : '--';
    $payload['aqiTone'] = brave_qweather_get_tone_from_aqi($aqi_value);
    $payload['aqiLabel'] = $aqi_index['category'] ?? '暂无';
    $payload['primaryPollutant'] = $aqi_index['primaryPollutant']['name'] ?? ($primary_pollutant['name'] ?? '暂无');
    $payload['primaryPollutantValue'] = $primary_pollutant['concentration']['value'] ?? '';
    $payload['primaryPollutantUnit'] = $primary_pollutant['concentration']['unit'] ?? '';
    $payload['airDailyForecast'] = $air_daily_forecast;
    $payload['uvValue'] = $uv_index;
    $payload['uvMax'] = is_numeric($uv_index) ? (string) (intval($uv_index) == $uv_index ? intval($uv_index) : round($uv_index, 1)) : '--';
    $payload['uvLabel'] = $uv_label;
    $payload['uvTone'] = brave_qweather_get_uv_tone($uv_index);
    $payload['uvText'] = $uv_text;
    $payload['indices'] = $indices_grouped;
    $payload['clothing'] = brave_qweather_build_clothing_data($indices_grouped, $payload);
    $payload['detailLoaded'] = true;

    return $payload;
}

function brave_qweather_normalize_city_weather($city, $index, $include_details = true) {
    $location = brave_qweather_get_location_string($city);
    $lang = 'zh-hans';
    $required_responses = brave_qweather_get_required_city_weather_responses($city, $location, $lang);
    $required_error = brave_qweather_get_required_city_weather_error($required_responses);

    if (is_wp_error($required_error)) {
        return array(
            'index' => $index,
            'name' => $city['name'],
            'status' => 'error',
            'detailLoaded' => false,
            'message' => $required_error->get_error_message(),
        );
    }

    $warning_response = brave_qweather_get_city_warning_response($city, $location, $lang);
    $payload = brave_qweather_build_base_city_weather_payload($city, $index, $required_responses, $warning_response);

    if (!$include_details) {
        return $payload;
    }

    return brave_qweather_add_city_weather_details($payload, $city, $location, $lang);
}
