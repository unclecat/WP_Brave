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
function brave_weather_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('权限不足', 'brave-love'));
    }

    // 保存数据
    if (isset($_POST['brave_save_weather']) && check_admin_referer('brave_weather_nonce')) {
        $enabled = isset($_POST['brave_weather_enabled']) ? true : false;
        update_option('brave_weather_enabled', $enabled);

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

                    if (count($cities) >= 4) {
                        break;
                    }
                }
            }
        }
        update_option('brave_weather_cities', $cities);
        echo '<div class="notice notice-success"><p>' . __('已保存', 'brave-love') . '</p></div>';
    }

    $enabled = get_option('brave_weather_enabled', false);
    $cities = get_option('brave_weather_cities', array(
        array('name' => '北京', 'lat' => '39.9042', 'lon' => '116.4074'),
        array('name' => '上海', 'lat' => '31.2304', 'lon' => '121.4737'),
    ));
    ?>
    <div class="wrap">
        <h1><?php _e('天气城市管理', 'brave-love'); ?></h1>
        <p class="description">
            <?php _e('添加 2-4 个关心的城市，首页将显示这些地区的天气概况。点击天气卡片可查看详细穿衣指南。', 'brave-love'); ?>
        </p>
        
        <form method="post" action="" id="weather-form">
            <?php wp_nonce_field('brave_weather_nonce'); ?>
            
            <table class="form-table">
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
                <?php _e('经纬度可从 ', 'brave-love'); ?>
                <a href="https://open-meteo.com/en/docs" target="_blank" rel="noopener noreferrer">Open-Meteo</a>
                <?php _e(' 查询，或搜索 "城市名 latitude longitude"', 'brave-love'); ?>
            </p>
            
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 30%;"><?php _e('城市名称', 'brave-love'); ?></th>
                        <th style="width: 25%;"><?php _e('纬度', 'brave-love'); ?></th>
                        <th style="width: 25%;"><?php _e('经度', 'brave-love'); ?></th>
                        <th style="width: 20%;"><?php _e('操作', 'brave-love'); ?></th>
                    </tr>
                </thead>
                <tbody id="city-list">
                    <?php foreach ($cities as $index => $city) : ?>
                        <tr class="city-item">
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
        $('#add-city').on('click', function() {
            var row = '<tr class="city-item">' +
                '<td><input type="text" name="city_name[]" class="regular-text" placeholder="<?php _e('例如：北京', 'brave-love'); ?>"></td>' +
                '<td><input type="text" name="city_lat[]" class="regular-text" placeholder="39.9042"></td>' +
                '<td><input type="text" name="city_lon[]" class="regular-text" placeholder="116.4074"></td>' +
                '<td><button type="button" class="button remove-city"><?php _e('删除', 'brave-love'); ?></button></td>' +
                '</tr>';
            $('#city-list').append(row);
        });
        
        $(document).on('click', '.remove-city', function() {
            if ($('.city-item:visible').length > 1) {
                $(this).closest('.city-item').remove();
            } else {
                alert('<?php _e('至少保留一个城市', 'brave-love'); ?>');
            }
        });
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

        if (count($sanitized_cities) >= 4) {
            break;
        }
    }

    return $sanitized_cities;
}

/**
 * 检查天气是否启用
 */
function brave_is_weather_enabled() {
    return get_option('brave_weather_enabled', false);
}
