<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 首页天气快照缓存。
 *
 * @package Brave_Love
 */

function brave_qweather_home_snapshot_cache_key() {
    return 'brv_qw_home_snapshot';
}

function brave_qweather_home_snapshot_backup_option_key() {
    return brave_qweather_backup_option_key(brave_qweather_home_snapshot_cache_key());
}

function brave_qweather_home_snapshot_ttl() {
    return (int) apply_filters('brave_qweather_home_snapshot_ttl', 5 * MINUTE_IN_SECONDS);
}

function brave_qweather_home_snapshot_backup_max_age() {
    return max(0, (int) apply_filters('brave_qweather_home_snapshot_backup_max_age', brave_qweather_backup_max_age()));
}

function brave_qweather_get_home_snapshot_record($use_backup = false) {
    $record = $use_backup
        ? get_option(brave_qweather_home_snapshot_backup_option_key(), array())
        : get_transient(brave_qweather_home_snapshot_cache_key());

    if (!is_array($record) || !isset($record['data']) || !is_array($record['data'])) {
        return array();
    }

    if ($use_backup && !brave_qweather_is_backup_record_usable($record, brave_qweather_home_snapshot_backup_max_age())) {
        return array();
    }

    return $record;
}

function brave_qweather_store_home_snapshot($payload, $persist_backup = true) {
    if (!is_array($payload)) {
        return array();
    }

    $record = array(
        'cached_at' => time(),
        'data' => $payload,
    );

    set_transient(brave_qweather_home_snapshot_cache_key(), $record, brave_qweather_home_snapshot_ttl());

    if ($persist_backup) {
        update_option(brave_qweather_home_snapshot_backup_option_key(), $record, false);
    }

    return $record;
}

function brave_qweather_home_payload_has_errors($payload) {
    $cities = $payload['cities'] ?? array();

    if (!is_array($cities)) {
        return false;
    }

    foreach ($cities as $city) {
        if (!empty($city['status']) && 'error' === $city['status']) {
            return true;
        }
    }

    return false;
}

function brave_qweather_home_payload_has_successful_cities($payload) {
    $cities = $payload['cities'] ?? array();

    if (!is_array($cities)) {
        return false;
    }

    foreach ($cities as $city) {
        if (is_array($city) && 'error' !== ($city['status'] ?? '')) {
            return true;
        }
    }

    return false;
}

function brave_qweather_fill_home_payload_city_errors($payload, $backup_payload, $message = '') {
    if (!is_array($payload) || empty($payload['cities']) || !is_array($payload['cities'])) {
        return $payload;
    }

    $backup_cities = $backup_payload['cities'] ?? array();
    if (!is_array($backup_cities) || empty($backup_cities)) {
        return $payload;
    }

    $backup_by_index = array();
    $backup_by_name = array();

    foreach ($backup_cities as $backup_city) {
        if (!is_array($backup_city) || 'error' === ($backup_city['status'] ?? '')) {
            continue;
        }

        $backup_index = isset($backup_city['index']) ? (string) $backup_city['index'] : '';
        if ('' !== $backup_index) {
            $backup_by_index[$backup_index] = $backup_city;
        }

        $backup_name = sanitize_title((string) ($backup_city['name'] ?? ''));
        if ('' !== $backup_name) {
            $backup_by_name[$backup_name] = $backup_city;
        }
    }

    $used_backup = false;

    foreach ($payload['cities'] as $index => $city) {
        if (!is_array($city) || 'error' !== ($city['status'] ?? '')) {
            continue;
        }

        $lookup_index = isset($city['index']) ? (string) $city['index'] : '';
        $lookup_name = sanitize_title((string) ($city['name'] ?? ''));
        $backup_city = array();

        if ('' !== $lookup_index && isset($backup_by_index[$lookup_index])) {
            $backup_city = $backup_by_index[$lookup_index];
        } elseif ('' !== $lookup_name && isset($backup_by_name[$lookup_name])) {
            $backup_city = $backup_by_name[$lookup_name];
        }

        if (empty($backup_city)) {
            continue;
        }

        $backup_city['index'] = $city['index'] ?? ($backup_city['index'] ?? $index);

        if (!empty($city['name'])) {
            $backup_city['name'] = $city['name'];
        }

        $backup_city['stale'] = true;

        if ('' !== $message) {
            $backup_city['message'] = $message;
        }

        $payload['cities'][$index] = $backup_city;
        $used_backup = true;
    }

    if ($used_backup) {
        $payload['snapshotStale'] = true;

        if ('' !== $message) {
            $payload['snapshotMessage'] = $message;
        }
    }

    return $payload;
}

function brave_qweather_mark_home_snapshot_stale($payload, $message = '') {
    if (!is_array($payload)) {
        return array();
    }

    $payload['snapshotStale'] = true;

    if ('' !== $message) {
        $payload['snapshotMessage'] = $message;
    }

    if (!empty($payload['cities']) && is_array($payload['cities'])) {
        foreach ($payload['cities'] as $index => $city) {
            if (!is_array($city) || ('error' === ($city['status'] ?? ''))) {
                continue;
            }

            $payload['cities'][$index]['stale'] = true;
        }
    }

    return $payload;
}

function brave_qweather_build_home_weather_payload($include_details = false) {
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
        $payloads[] = brave_qweather_normalize_city_weather($city, $index, $include_details);
    }

    return array(
        'enabled' => true,
        'configured' => true,
        'provider' => 'qweather',
        'generatedAt' => gmdate(DateTime::ATOM),
        'cities' => $payloads,
    );
}
