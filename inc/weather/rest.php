<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * QWeather REST 路由注册。
 *
 * @package Brave_Love
 */

function brave_get_home_weather_payload() {
    if (!function_exists('brave_is_weather_enabled') || !brave_is_weather_enabled()) {
        return brave_qweather_build_home_weather_payload();
    }

    $config = brave_get_qweather_config();
    if (empty($config['configured'])) {
        return brave_qweather_build_home_weather_payload();
    }

    $cached = brave_qweather_get_home_snapshot_record();
    if (!empty($cached['data'])) {
        return $cached['data'];
    }

    $payload = brave_qweather_build_home_weather_payload();
    if (!brave_qweather_home_payload_has_errors($payload)) {
        brave_qweather_store_home_snapshot($payload);
        return $payload;
    }

    $backup = brave_qweather_get_home_snapshot_record(true);
    if (!empty($backup['data'])) {
        return brave_qweather_mark_home_snapshot_stale(
            $backup['data'],
            __('天气接口短时波动，已回退到稍早缓存。', 'brave-love')
        );
    }

    return $payload;
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
