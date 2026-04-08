<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * QWeather 天气服务入口。
 *
 * 保持原有加载路径不变，把实现拆到更聚焦的模块里，
 * 方便后续维护配置、请求、文案和 REST 输出。
 *
 * @package Brave_Love
 */

require_once __DIR__ . '/weather/config.php';
require_once __DIR__ . '/weather/client.php';
require_once __DIR__ . '/weather/support.php';
require_once __DIR__ . '/weather/copy.php';
require_once __DIR__ . '/weather/payload.php';
require_once __DIR__ . '/weather/rest.php';
