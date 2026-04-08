<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 天气城市管理
 *
 * @package Brave_Love
 */

/**
 * 添加天气管理菜单
 */
function brave_add_weather_menu() {
    add_options_page(
        __('天气城市', 'brave-love'),
        __('天气城市', 'brave-love'),
        'manage_options',
        'brave-weather',
        'brave_weather_page'
    );
}
add_action('admin_menu', 'brave_add_weather_menu');

/**
 * 天气管理页面
 */
function brave_weather_mask_secret($value) {
    $value = (string) $value;
    $length = strlen($value);

    if ($length <= 8) {
        return str_repeat('*', max(4, $length));
    }

    return substr($value, 0, 4) . str_repeat('*', max(4, $length - 8)) . substr($value, -4);
}

function brave_weather_get_qweather_source_label($source) {
    if ('server' === $source) {
        return __('wp-config.php / 环境变量', 'brave-love');
    }

    if ('database' === $source) {
        return __('后台设置', 'brave-love');
    }

    return __('未提供', 'brave-love');
}

function brave_weather_get_qweather_effective_source_label($config) {
    $host_source = $config['host_source'] ?? '';
    $key_source = $config['key_source'] ?? '';

    if ($host_source === $key_source) {
        return brave_weather_get_qweather_source_label($host_source);
    }

    $host_label = brave_weather_get_qweather_source_label($host_source);
    $key_label = brave_weather_get_qweather_source_label($key_source);

    if ('' === $host_source || '' === $key_source) {
        return sprintf(__('部分配置（Host：%1$s；Key：%2$s）', 'brave-love'), $host_label, $key_label);
    }

    return sprintf(__('混合来源（Host：%1$s；Key：%2$s）', 'brave-love'), $host_label, $key_label);
}

function brave_weather_get_qweather_missing_items($config) {
    $missing = array();

    if ('' === (string) ($config['api_host'] ?? '')) {
        $missing[] = 'API Host';
    }

    if ('' === (string) ($config['api_key'] ?? '')) {
        $missing[] = 'API Key';
    }

    return $missing;
}

function brave_weather_get_qweather_status_tips($config) {
    $tips = array(
        __('排查时先看“当前实际生效”，不要只看下面输入框里保存了什么。', 'brave-love'),
    );
    $host_source = $config['host_source'] ?? '';
    $key_source = $config['key_source'] ?? '';
    $stored_api_host = (string) ($config['stored_api_host'] ?? '');
    $stored_api_key = (string) ($config['stored_api_key'] ?? '');

    if ('server' === $host_source || 'server' === $key_source) {
        $tips[] = __('只要服务器环境里存在同名配置，就会优先覆盖后台设置；下方表单里保存的值会保留，但当前请求未必使用它。', 'brave-love');
    } elseif (!empty($config['configured'])) {
        $tips[] = __('当前请求直接使用后台里这组 Host / Key；保存后刷新首页天气即可验证。', 'brave-love');
    }

    if ('' !== $host_source && '' !== $key_source && $host_source !== $key_source) {
        $tips[] = __('当前 Host 和 Key 来自不同位置。建议两项统一放在同一处，后续排查最不容易误判。', 'brave-love');
    }

    if (empty($config['configured'])) {
        $missing = brave_weather_get_qweather_missing_items($config);
        if (!empty($missing)) {
            $tips[] = sprintf(__('当前还缺少：%s。补齐后天气接口才会真正恢复。', 'brave-love'), implode(' / ', $missing));
        }
    } elseif (('server' === $host_source || 'server' === $key_source) && ('' !== $stored_api_host || '' !== $stored_api_key)) {
        $tips[] = __('如果你想改用后台这组值，需要先移除 wp-config.php 或环境变量里的 QWEATHER_API_HOST / QWEATHER_API_KEY。', 'brave-love');
    }

    return $tips;
}

function brave_weather_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('权限不足', 'brave-love'));
    }

    $notice_message = '';

    // 保存数据
    if (isset($_POST['brave_save_weather']) && check_admin_referer('brave_weather_nonce')) {
        $enabled = isset($_POST['brave_weather_enabled']) ? true : false;
        update_option('brave_weather_enabled', $enabled);

        $stored_host_before = function_exists('brave_get_qweather_option_setting') ? brave_get_qweather_option_setting('QWEATHER_API_HOST') : '';
        $stored_key_before = function_exists('brave_get_qweather_option_setting') ? brave_get_qweather_option_setting('QWEATHER_API_KEY') : '';
        $cache_should_refresh = false;

        $api_host_input = isset($_POST['brave_qweather_api_host']) ? wp_unslash($_POST['brave_qweather_api_host']) : '';
        $api_host = function_exists('brave_normalize_qweather_api_host')
            ? brave_normalize_qweather_api_host($api_host_input)
            : trim((string) $api_host_input);

        if ('' === $api_host) {
            if ('' !== $stored_host_before) {
                delete_option('brave_qweather_api_host');
                $cache_should_refresh = true;
            }
        } else {
            update_option('brave_qweather_api_host', $api_host, false);
            if ($stored_host_before !== $api_host) {
                $cache_should_refresh = true;
            }
        }

        $clear_api_key = !empty($_POST['brave_qweather_api_key_clear']);
        $api_key_input = isset($_POST['brave_qweather_api_key']) ? trim((string) wp_unslash($_POST['brave_qweather_api_key'])) : '';

        if ($clear_api_key) {
            if ('' !== $stored_key_before) {
                delete_option('brave_qweather_api_key');
                $cache_should_refresh = true;
            }
        } elseif ('' !== $api_key_input) {
            $api_key = sanitize_text_field($api_key_input);
            update_option('brave_qweather_api_key', $api_key, false);
            if ($stored_key_before !== $api_key) {
                $cache_should_refresh = true;
            }
        }

        $cities = array();
        if (isset($_POST['city_name']) && is_array($_POST['city_name'])) {
            $city_names = wp_unslash($_POST['city_name']);
            $city_lats = isset($_POST['city_lat']) && is_array($_POST['city_lat']) ? wp_unslash($_POST['city_lat']) : array();
            $city_lons = isset($_POST['city_lon']) && is_array($_POST['city_lon']) ? wp_unslash($_POST['city_lon']) : array();

            foreach ($city_names as $key => $name) {
                $name = sanitize_text_field($name);
                $lat = brave_sanitize_coordinate($city_lats[$key] ?? '', -90, 90, 4);
                $lon = brave_sanitize_coordinate($city_lons[$key] ?? '', -180, 180, 4);
                if (!empty($name) && !empty($lat) && !empty($lon)) {
                    $cities[] = array(
                        'name' => $name,
                        'lat' => $lat,
                        'lon' => $lon,
                    );

                }
            }
        }
        $stored_cities_before = get_option('brave_weather_cities', array());
        update_option('brave_weather_cities', $cities);

        if ($stored_cities_before !== $cities) {
            $cache_should_refresh = true;
        }

        if ($cache_should_refresh && function_exists('brave_qweather_flush_cached_responses')) {
            brave_qweather_flush_cached_responses();
        }

        if (function_exists('brave_get_qweather_config')) {
            brave_get_qweather_config(true);
        }

        $notice_message = __('已保存。天气凭证或城市变更后，会自动刷新天气缓存；是否真正生效，请以上方“当前实际生效”为准。', 'brave-love');
    }

    $enabled = get_option('brave_weather_enabled', false);
    $cities = get_option('brave_weather_cities', array(
        array('name' => '北京', 'lat' => '39.9042', 'lon' => '116.4074'),
        array('name' => '上海', 'lat' => '31.2304', 'lon' => '121.4737'),
    ));
    $qweather_config = function_exists('brave_get_qweather_config') ? brave_get_qweather_config() : array('configured' => false);
    $stored_api_host = $qweather_config['stored_api_host'] ?? '';
    $stored_api_key = $qweather_config['stored_api_key'] ?? '';
    $is_qweather_configured = !empty($qweather_config['configured']);
    $effective_source_label = brave_weather_get_qweather_effective_source_label($qweather_config);
    $missing_items = brave_weather_get_qweather_missing_items($qweather_config);
    $status_tips = brave_weather_get_qweather_status_tips($qweather_config);
    ?>
    <div class="wrap">
        <h1><?php _e('天气城市管理', 'brave-love'); ?></h1>
        <p class="description">
            <?php _e('添加任意数量关心的城市，首页将显示这些地区的天气概况。点击天气卡片可查看详细穿衣指南。', 'brave-love'); ?>
        </p>

        <?php if ('' !== $notice_message) : ?>
            <div class="notice notice-success"><p><?php echo esc_html($notice_message); ?></p></div>
        <?php endif; ?>

        <div class="card" style="max-width: 780px; margin-top: 1rem; margin-bottom: 1.5rem;">
            <h2 style="margin-top: 0;"><?php _e('QWeather API 配置状态', 'brave-love'); ?></h2>
            <p>
                <strong><?php _e('状态：', 'brave-love'); ?></strong>
                <span style="color: <?php echo $is_qweather_configured ? '#2e7d32' : '#b3261e'; ?>; font-weight: 700;">
                    <?php echo esc_html($is_qweather_configured ? __('已配置', 'brave-love') : __('未配置完整', 'brave-love')); ?>
                </span>
            </p>
            <p><strong><?php _e('认证方式：', 'brave-love'); ?></strong><code>API Key</code></p>
            <p><strong><?php _e('当前实际生效：', 'brave-love'); ?></strong><?php echo esc_html($effective_source_label); ?></p>
            <?php if (!$is_qweather_configured && !empty($missing_items)) : ?>
                <p><strong><?php _e('仍缺少：', 'brave-love'); ?></strong><code><?php echo esc_html(implode(' / ', $missing_items)); ?></code></p>
            <?php endif; ?>
            <p>
                <strong><?php _e('Host 当前使用：', 'brave-love'); ?></strong>
                <code><?php echo esc_html(($qweather_config['api_host'] ?? '') ? $qweather_config['api_host'] : __('未提供', 'brave-love')); ?></code>
                <span class="description"><?php printf(esc_html__('（来源：%s）', 'brave-love'), brave_weather_get_qweather_source_label($qweather_config['host_source'] ?? '')); ?></span>
            </p>
            <p>
                <strong><?php _e('Key 当前使用：', 'brave-love'); ?></strong>
                <code><?php echo esc_html(($qweather_config['api_key'] ?? '') ? brave_weather_mask_secret($qweather_config['api_key']) : __('未提供', 'brave-love')); ?></code>
                <span class="description"><?php printf(esc_html__('（来源：%s）', 'brave-love'), brave_weather_get_qweather_source_label($qweather_config['key_source'] ?? '')); ?></span>
            </p>
            <p><strong><?php _e('后台已保存 Host：', 'brave-love'); ?></strong><code><?php echo esc_html($stored_api_host ? $stored_api_host : __('未保存', 'brave-love')); ?></code></p>
            <p><strong><?php _e('后台已保存 Key：', 'brave-love'); ?></strong><code><?php echo esc_html($stored_api_key ? brave_weather_mask_secret($stored_api_key) : __('未保存', 'brave-love')); ?></code></p>
            <div style="margin-top: 1rem; padding: 12px 14px; background: #f6f8fa; border-left: 4px solid #2271b1; border-radius: 6px;">
                <p style="margin: 0 0 8px;"><strong><?php _e('排查提示', 'brave-love'); ?></strong></p>
                <ul style="margin: 0; padding-left: 1.2rem;">
                    <?php foreach ($status_tips as $tip) : ?>
                        <li style="margin-bottom: 6px;"><?php echo esc_html($tip); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <form method="post" action="" id="weather-form">
            <?php wp_nonce_field('brave_weather_nonce'); ?>
            
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e('QWeather API Host', 'brave-love'); ?></th>
                    <td>
                        <input
                            type="text"
                            name="brave_qweather_api_host"
                            value="<?php echo esc_attr($stored_api_host); ?>"
                            class="regular-text code"
                            placeholder="nb7aarhnan.re.qweatherapi.com"
                        >
                        <p class="description">
                            <?php _e('这是 QWeather 给你的接口域名，例如 `nb7aarhnan.re.qweatherapi.com`。可直接填写域名，系统会自动补上 `https://`。注意：这里显示的是后台已保存值，不代表当前一定生效；是否真正生效以上方“当前实际生效”为准。', 'brave-love'); ?>
                        </p>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('QWeather API Key', 'brave-love'); ?></th>
                    <td>
                        <input
                            type="password"
                            name="brave_qweather_api_key"
                            value=""
                            class="regular-text code"
                            autocomplete="new-password"
                            placeholder="<?php echo esc_attr($stored_api_key ? brave_weather_mask_secret($stored_api_key) : __('留空表示暂不设置', 'brave-love')); ?>"
                        >
                        <p class="description">
                            <?php
                            echo esc_html(
                                $stored_api_key
                                    ? __('后台已保存 API Key。这里留空表示保持不变；如果输入新值，会覆盖后台当前保存的 Key。注意：是否立即生效，以上方“当前实际生效”为准。', 'brave-love')
                                    : __('如果当前没有服务器级配置，可以直接把 QWeather 控制台里的 API Key 粘贴到这里。是否真正被请求使用，以上方状态卡片为准。', 'brave-love')
                            );
                            ?>
                        </p>
                        <?php if ($stored_api_key) : ?>
                            <label style="display: inline-flex; align-items: center; gap: 6px; margin-top: 6px;">
                                <input type="checkbox" name="brave_qweather_api_key_clear" value="1">
                                <?php _e('清空后台已保存的 API Key', 'brave-love'); ?>
                            </label>
                        <?php endif; ?>
                    </td>
                </tr>
                <tr>
                    <th scope="row"><?php _e('启用天气', 'brave-love'); ?></th>
                    <td>
                        <label>
                            <input type="checkbox" name="brave_weather_enabled" <?php checked($enabled); ?>>
                            <?php _e('在首页显示天气小组件', 'brave-love'); ?>
                        </label>
                    </td>
                </tr>
            </table>
            
            <h2><?php _e('城市列表', 'brave-love'); ?></h2>
            <p class="description">
                <?php _e('请填写城市名称以及对应经纬度，也可以直接搜索“城市名 latitude longitude”获取坐标。拖拽左侧手柄即可调整首页天气卡片顺序，调整后记得点击“保存设置”。', 'brave-love'); ?>
            </p>
            
            <table class="wp-list-table widefat fixed striped weather-city-table">
                <thead>
                    <tr>
                        <th style="width: 12%;"><?php _e('拖拽', 'brave-love'); ?></th>
                        <th style="width: 30%;"><?php _e('城市名称', 'brave-love'); ?></th>
                        <th style="width: 20%;"><?php _e('纬度', 'brave-love'); ?></th>
                        <th style="width: 20%;"><?php _e('经度', 'brave-love'); ?></th>
                        <th style="width: 18%;"><?php _e('操作', 'brave-love'); ?></th>
                    </tr>
                </thead>
                <tbody id="city-list">
                    <?php foreach ($cities as $index => $city) : ?>
                        <tr class="city-item">
                            <td class="city-drag-cell">
                                <span class="city-drag-handle" title="<?php esc_attr_e('拖拽排序', 'brave-love'); ?>" aria-hidden="true">
                                    <span class="dashicons dashicons-move"></span>
                                </span>
                                <strong class="city-order-number"><?php echo esc_html((string) ($index + 1)); ?></strong>
                            </td>
                            <td>
                                <input type="text" name="city_name[]" value="<?php echo esc_attr($city['name']); ?>" class="regular-text" placeholder="<?php _e('例如：北京', 'brave-love'); ?>">
                            </td>
                            <td>
                                <input type="text" name="city_lat[]" value="<?php echo esc_attr($city['lat']); ?>" class="regular-text" placeholder="39.9042">
                            </td>
                            <td>
                                <input type="text" name="city_lon[]" value="<?php echo esc_attr($city['lon']); ?>" class="regular-text" placeholder="116.4074">
                            </td>
                            <td>
                                <button type="button" class="button remove-city"><?php _e('删除', 'brave-love'); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($cities)) : ?>
                        <tr class="city-item empty-row" style="display: none;"></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <p class="submit">
                <button type="button" class="button" id="add-city"><?php _e('添加城市', 'brave-love'); ?></button>
                <input type="submit" name="brave_save_weather" class="button button-primary" value="<?php _e('保存设置', 'brave-love'); ?>">
            </p>
        </form>
        
        <div class="card" style="max-width: 600px; margin-top: 2rem;">
            <h3><?php _e('常用城市经纬度参考', 'brave-love'); ?></h3>
            <table class="widefat" style="margin-top: 1rem;">
                <thead>
                    <tr>
                        <th><?php _e('城市', 'brave-love'); ?></th>
                        <th><?php _e('纬度', 'brave-love'); ?></th>
                        <th><?php _e('经度', 'brave-love'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>北京</td><td>39.9042</td><td>116.4074</td></tr>
                    <tr><td>上海</td><td>31.2304</td><td>121.4737</td></tr>
                    <tr><td>广州</td><td>23.1291</td><td>113.2644</td></tr>
                    <tr><td>深圳</td><td>22.5431</td><td>114.0579</td></tr>
                    <tr><td>成都</td><td>30.5728</td><td>104.0668</td></tr>
                    <tr><td>杭州</td><td>30.2741</td><td>120.1551</td></tr>
                    <tr><td>武汉</td><td>30.5928</td><td>114.3055</td></tr>
                    <tr><td>西安</td><td>34.3416</td><td>108.9398</td></tr>
                    <tr><td>重庆</td><td>29.5630</td><td>106.5516</td></tr>
                    <tr><td>南京</td><td>32.0603</td><td>118.7969</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        function refreshSortable() {
            var $list = $('#city-list');

            if ($.fn.sortable && $list.data('ui-sortable')) {
                $list.sortable('refresh');
            }
        }

        function updateCityOrderState() {
            var $rows = $('#city-list .city-item').not('.empty-row');

            $rows.each(function(index) {
                $(this).find('.city-order-number').text(index + 1);
            });
        }

        function fixHelperWidths(event, ui) {
            ui.children().each(function() {
                $(this).width($(this).width());
            });

            return ui;
        }

        function initSortable() {
            if (!$.fn.sortable) {
                return;
            }

            $('#city-list').sortable({
                items: '> .city-item:not(.empty-row)',
                axis: 'y',
                handle: '.city-drag-handle',
                helper: fixHelperWidths,
                placeholder: 'city-sort-placeholder',
                forcePlaceholderSize: true,
                tolerance: 'pointer',
                start: function(event, ui) {
                    ui.item.addClass('is-sorting');
                },
                stop: function(event, ui) {
                    ui.item.removeClass('is-sorting');
                    updateCityOrderState();
                },
            });
        }

        $('#add-city').on('click', function() {
            $('#city-list .empty-row').remove();
            var row = '<tr class="city-item">' +
                '<td class="city-drag-cell">' +
                    '<span class="city-drag-handle" title="<?php echo esc_attr__('拖拽排序', 'brave-love'); ?>" aria-hidden="true">' +
                        '<span class="dashicons dashicons-move"></span>' +
                    '</span>' +
                    '<strong class="city-order-number"></strong>' +
                '</td>' +
                '<td><input type="text" name="city_name[]" class="regular-text" placeholder="<?php _e('例如：北京', 'brave-love'); ?>"></td>' +
                '<td><input type="text" name="city_lat[]" class="regular-text" placeholder="39.9042"></td>' +
                '<td><input type="text" name="city_lon[]" class="regular-text" placeholder="116.4074"></td>' +
                '<td><button type="button" class="button remove-city"><?php _e('删除', 'brave-love'); ?></button></td>' +
                '</tr>';
            $('#city-list').append(row);
            updateCityOrderState();
            refreshSortable();
        });

        $(document).on('click', '.remove-city', function() {
            if ($('.city-item:visible').length > 1) {
                $(this).closest('.city-item').remove();
                updateCityOrderState();
                refreshSortable();
            } else {
                alert('<?php _e('至少保留一个城市', 'brave-love'); ?>');
            }
        });

        initSortable();
        updateCityOrderState();
    });
    </script>
    <?php
}

/**
 * 获取天气城市列表
 */
function brave_get_weather_cities() {
    $cities = get_option('brave_weather_cities', array());
    $sanitized_cities = array();

    if (!is_array($cities)) {
        return $sanitized_cities;
    }

    foreach ($cities as $city) {
        if (!is_array($city)) {
            continue;
        }

        $name = sanitize_text_field($city['name'] ?? '');
        $lat = brave_sanitize_coordinate($city['lat'] ?? '', -90, 90, 4);
        $lon = brave_sanitize_coordinate($city['lon'] ?? '', -180, 180, 4);

        if ('' === $name || '' === $lat || '' === $lon) {
            continue;
        }

        $sanitized_cities[] = array(
            'name' => $name,
            'lat' => $lat,
            'lon' => $lon,
        );

    }

    return $sanitized_cities;
}

/**
 * 检查天气是否启用
 */
function brave_is_weather_enabled() {
    return get_option('brave_weather_enabled', false);
}
