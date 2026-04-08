<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * QWeather 基础格式化与状态辅助函数。
 *
 * @package Brave_Love
 */

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
