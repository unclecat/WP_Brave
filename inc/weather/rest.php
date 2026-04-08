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
