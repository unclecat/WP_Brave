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
    $warning = brave_qweather_get_cached_response('warning', $city, '/v7/warning/now', array(
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

    if (is_array($hourly_items)) {
        foreach ($hourly_items as $hourly_index => $hourly_item) {
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
    }

    if (!$matched_reference_day) {
        $precipitation_max = $trend_precipitation_max;
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
    $air_daily_forecast = brave_qweather_pick_air_daily_forecast($air_daily['data']['days'] ?? array(), $updated_at);

    $payload = array(
        'index' => $index,
        'name' => $city['name'],
        'status' => 'ok',
        'stale' => !empty($now['stale']) || !empty($hourly['stale']) || !empty($daily['stale']) || !empty($indices['stale']) || !empty($air['stale']) || !empty($warning['stale']),
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
        'airDailyForecast' => $air_daily_forecast,
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
        'indices' => $indices_grouped,
    );

    $payload['clothing'] = brave_qweather_build_clothing_data($indices_grouped, $payload);

    return $payload;
}
