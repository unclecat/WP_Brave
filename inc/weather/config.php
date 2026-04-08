<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * QWeather 配置与缓存管理。
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

function brave_get_qweather_option_name($name) {
    $map = array(
        'QWEATHER_API_HOST' => 'brave_qweather_api_host',
        'QWEATHER_API_KEY' => 'brave_qweather_api_key',
    );

    return $map[$name] ?? '';
}

function brave_get_qweather_option_setting($name) {
    $option_name = brave_get_qweather_option_name($name);
    if ('' === $option_name) {
        return '';
    }

    $value = get_option($option_name, '');

    return is_string($value) ? trim($value) : '';
}

function brave_normalize_qweather_api_host($api_host) {
    $api_host = trim((string) $api_host);

    if ('' === $api_host) {
        return '';
    }

    if (!preg_match('#^https?://#i', $api_host)) {
        $api_host = 'https://' . $api_host;
    }

    return untrailingslashit($api_host);
}

function brave_qweather_flush_cached_responses() {
    global $wpdb;

    $transient_prefix = $wpdb->esc_like('_transient_brv_qw_') . '%';
    $timeout_prefix = $wpdb->esc_like('_transient_timeout_brv_qw_') . '%';
    $backup_prefix = $wpdb->esc_like('brv_qw_backup_brv_qw_') . '%';

    $wpdb->query(
        $wpdb->prepare(
            "DELETE FROM {$wpdb->options}
            WHERE option_name LIKE %s
            OR option_name LIKE %s
            OR option_name LIKE %s",
            $transient_prefix,
            $timeout_prefix,
            $backup_prefix
        )
    );
}

function brave_get_qweather_config($refresh = false) {
    static $config = null;

    if (!$refresh && null !== $config) {
        return $config;
    }

    $server_api_host = brave_normalize_qweather_api_host(brave_get_qweather_setting('QWEATHER_API_HOST'));
    $server_api_key = brave_get_qweather_setting('QWEATHER_API_KEY');
    $option_api_host = brave_normalize_qweather_api_host(brave_get_qweather_option_setting('QWEATHER_API_HOST'));
    $option_api_key = brave_get_qweather_option_setting('QWEATHER_API_KEY');

    $api_host = '' !== $server_api_host ? $server_api_host : $option_api_host;
    $api_key = '' !== $server_api_key ? $server_api_key : $option_api_key;

    $config = array(
        'api_host' => $api_host,
        'api_key' => $api_key,
        'configured' => '' !== $api_host && '' !== $api_key,
        'host_source' => '' !== $server_api_host ? 'server' : ('' !== $option_api_host ? 'database' : ''),
        'key_source' => '' !== $server_api_key ? 'server' : ('' !== $option_api_key ? 'database' : ''),
        'stored_api_host' => $option_api_host,
        'stored_api_key' => $option_api_key,
    );

    return $config;
}
