<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * QWeather 请求与缓存读取。
 *
 * @package Brave_Love
 */

function brave_qweather_city_key($city) {
    return md5(($city['lat'] ?? '') . ':' . ($city['lon'] ?? ''));
}

function brave_qweather_cache_key($type, $city) {
    return 'brv_qw_' . sanitize_key($type) . '_' . brave_qweather_city_key($city);
}

function brave_qweather_backup_option_key($cache_key) {
    return 'brv_qw_backup_' . $cache_key;
}

function brave_qweather_backup_max_age() {
    return max(0, (int) apply_filters('brave_qweather_backup_max_age', 6 * HOUR_IN_SECONDS));
}

function brave_qweather_is_backup_record_usable($record, $max_age = null) {
    if (!is_array($record) || !array_key_exists('data', $record)) {
        return false;
    }

    $cached_at = (int) ($record['cached_at'] ?? 0);
    if ($cached_at <= 0) {
        return false;
    }

    $max_age = null === $max_age ? brave_qweather_backup_max_age() : max(0, (int) $max_age);

    if ($max_age <= 0) {
        return true;
    }

    return (time() - $cached_at) <= $max_age;
}

function brave_qweather_get_location_string($city) {
    return number_format((float) $city['lon'], 4, '.', '') . ',' . number_format((float) $city['lat'], 4, '.', '');
}

function brave_qweather_get_air_location_path($city) {
    return number_format((float) $city['lat'], 4, '.', '') . '/' . number_format((float) $city['lon'], 4, '.', '');
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
    if (brave_qweather_is_backup_record_usable($backup)) {
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
