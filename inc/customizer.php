<?php
/**
 * Customizer 设置
 *
 * @package Brave_Love
 */

/**
 * 自定义控制类 - 纪念日管理说明
 */
if (class_exists('WP_Customize_Control')) {
    class Brave_Anniversary_Note_Control extends WP_Customize_Control {
        public function render_content() {
            ?>
            <div class="brave-anniversary-note">
                <p><?php _e('纪念日管理请前往：后台 → 设置 → 纪念日管理', 'brave-love'); ?></p>
                <a href="<?php echo admin_url('options-general.php?page=brave-anniversary'); ?>" class="button">
                    <?php _e('管理纪念日', 'brave-love'); ?>
                </a>
            </div>
            <?php
        }
    }
}

/**
 * Customizer 注册
 */
function brave_customize_register($wp_customize) {
    // 添加主题设置面板
    $wp_customize->add_panel('brave_settings', array(
        'title' => __('Brave 主题设置', 'brave-love'),
        'priority' => 30,
    ));

    // ==================== 基本信息 ====================
    $wp_customize->add_section('brave_basic', array(
        'title' => __('基本信息', 'brave-love'),
        'panel' => 'brave_settings',
    ));

    // 恋爱起始日期时间（精确到分钟）
    $wp_customize->add_setting('brave_love_start_datetime', array(
        'default' => date('Y-m-d') . ' 00:00',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_love_start_datetime', array(
        'label' => __('恋爱起始日期时间', 'brave-love'),
        'description' => __('格式：YYYY-MM-DD HH:MM，例如：2020-05-20 20:00（精确到分钟）', 'brave-love'),
        'section' => 'brave_basic',
        'type' => 'text',
    ));

    // 恋爱计时器文字
    $wp_customize->add_setting('brave_timer_text', array(
        'default' => '我们风雨同舟已经一起走过',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_timer_text', array(
        'label' => __('恋爱计时器文字', 'brave-love'),
        'description' => __('显示在计时器上方的文字', 'brave-love'),
        'section' => 'brave_basic',
        'type' => 'text',
    ));

    // 下一个纪念日日期时间（精确到分钟）
    $wp_customize->add_setting('brave_next_anniversary_datetime', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_next_anniversary_datetime', array(
        'label' => __('下一个纪念日日期时间', 'brave-love'),
        'description' => __('格式：YYYY-MM-DD HH:MM，例如：2024-12-25 20:00（精确到分钟）', 'brave-love'),
        'section' => 'brave_basic',
        'type' => 'text',
    ));

    // 下一个纪念日名称
    $wp_customize->add_setting('brave_next_anniversary_name', array(
        'default' => '恋爱周年纪念日',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_next_anniversary_name', array(
        'label' => __('下一个纪念日名称', 'brave-love'),
        'description' => __('例如：恋爱一周年、100天纪念日等', 'brave-love'),
        'section' => 'brave_basic',
        'type' => 'text',
    ));

    // 下一个纪念日倒计时文字
    $wp_customize->add_setting('brave_countdown_text', array(
        'default' => '距离我们的特别日子还有',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_countdown_text', array(
        'label' => __('倒计时文字', 'brave-love'),
        'description' => __('显示在倒计时上方的文字', 'brave-love'),
        'section' => 'brave_basic',
        'type' => 'text',
    ));

    // 导航栏文字
    $wp_customize->add_setting('brave_nav_text', array(
        'default' => '世间最动情之事，莫过于两人相依',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_nav_text', array(
        'label' => __('导航栏右侧文字', 'brave-love'),
        'section' => 'brave_basic',
        'type' => 'text',
    ));
    
    // ==================== 纪念日设置 ====================
    $wp_customize->add_section('brave_anniversary', array(
        'title' => __('纪念日', 'brave-love'),
        'panel' => 'brave_settings',
    ));
    
    // 纪念日管理说明
    $wp_customize->add_setting('brave_anniversary_note', array(
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control(new Brave_Anniversary_Note_Control($wp_customize, 'brave_anniversary_note', array(
        'section' => 'brave_anniversary',
    )));

    // ==================== Hero 区域 ====================
    $wp_customize->add_section('brave_hero', array(
        'title' => __('Hero 区域', 'brave-love'),
        'panel' => 'brave_settings',
    ));

    // 背景图片
    $wp_customize->add_setting('brave_hero_bg', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'brave_hero_bg', array(
        'label' => __('Hero 背景图片', 'brave-love'),
        'description' => __('建议尺寸：1920×1080，浪漫风格的照片', 'brave-love'),
        'section' => 'brave_hero',
    )));

    // 男生头像
    $wp_customize->add_setting('brave_boy_avatar', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'brave_boy_avatar', array(
        'label' => __('男生头像', 'brave-love'),
        'section' => 'brave_hero',
    )));

    // 男生昵称
    $wp_customize->add_setting('brave_boy_name', array(
        'default' => '他',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_boy_name', array(
        'label' => __('男生昵称', 'brave-love'),
        'section' => 'brave_hero',
        'type' => 'text',
    ));

    // 女生头像
    $wp_customize->add_setting('brave_girl_avatar', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'brave_girl_avatar', array(
        'label' => __('女生头像', 'brave-love'),
        'section' => 'brave_hero',
    )));

    // 女生昵称
    $wp_customize->add_setting('brave_girl_name', array(
        'default' => '她',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_girl_name', array(
        'label' => __('女生昵称', 'brave-love'),
        'section' => 'brave_hero',
        'type' => 'text',
    ));

    // ==================== 入口图标 ====================
    $wp_customize->add_section('brave_icons', array(
        'title' => __('入口图标', 'brave-love'),
        'panel' => 'brave_settings',
    ));

    // 点点滴滴图标
    $wp_customize->add_setting('brave_icon_moments', array(
        'default' => '💖',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_icon_moments', array(
        'label' => __('点点滴滴图标', 'brave-love'),
        'section' => 'brave_icons',
        'type' => 'text',
    ));

    // 恋爱清单图标
    $wp_customize->add_setting('brave_icon_list', array(
        'default' => '📜',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_icon_list', array(
        'label' => __('恋爱清单图标', 'brave-love'),
        'section' => 'brave_icons',
        'type' => 'text',
    ));

    // 甜蜜相册图标
    $wp_customize->add_setting('brave_icon_memory', array(
        'default' => '📷',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_icon_memory', array(
        'label' => __('甜蜜相册图标', 'brave-love'),
        'section' => 'brave_icons',
        'type' => 'text',
    ));

    // 随笔说说图标
    $wp_customize->add_setting('brave_icon_notes', array(
        'default' => '📝',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_icon_notes', array(
        'label' => __('随笔说说图标', 'brave-love'),
        'section' => 'brave_icons',
        'type' => 'text',
    ));

    // 祝福留言图标
    $wp_customize->add_setting('brave_icon_blessing', array(
        'default' => '💌',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_icon_blessing', array(
        'label' => __('祝福留言图标', 'brave-love'),
        'section' => 'brave_icons',
        'type' => 'text',
    ));

    // ==================== 页面链接 ====================
    $wp_customize->add_section('brave_pages', array(
        'title' => __('页面链接', 'brave-love'),
        'panel' => 'brave_settings',
    ));

    // 点点滴滴页面
    $wp_customize->add_setting('brave_page_moments', array(
        'default' => '',
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_page_moments', array(
        'label' => __('点点滴滴页面', 'brave-love'),
        'section' => 'brave_pages',
        'type' => 'dropdown-pages',
    ));

    // 恋爱清单页面
    $wp_customize->add_setting('brave_page_list', array(
        'default' => '',
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_page_list', array(
        'label' => __('恋爱清单页面', 'brave-love'),
        'section' => 'brave_pages',
        'type' => 'dropdown-pages',
    ));

    // 甜蜜相册页面
    $wp_customize->add_setting('brave_page_memory', array(
        'default' => '',
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_page_memory', array(
        'label' => __('甜蜜相册页面', 'brave-love'),
        'section' => 'brave_pages',
        'type' => 'dropdown-pages',
    ));

    // 随笔说说页面
    $wp_customize->add_setting('brave_page_notes', array(
        'default' => '',
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_page_notes', array(
        'label' => __('随笔说说页面', 'brave-love'),
        'section' => 'brave_pages',
        'type' => 'dropdown-pages',
    ));

    // 祝福留言页面
    $wp_customize->add_setting('brave_page_blessing', array(
        'default' => '',
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_page_blessing', array(
        'label' => __('祝福留言页面', 'brave-love'),
        'section' => 'brave_pages',
        'type' => 'dropdown-pages',
    ));

    // ==================== 自定义代码 ====================
    $wp_customize->add_section('brave_custom_code', array(
        'title' => __('自定义代码', 'brave-love'),
        'panel' => 'brave_settings',
    ));

    // 自定义 CSS
    $wp_customize->add_setting('brave_custom_css', array(
        'default' => '',
        'sanitize_callback' => 'wp_strip_all_tags',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_custom_css', array(
        'label' => __('自定义 CSS', 'brave-love'),
        'description' => __('直接书写 CSS 代码，无需包含 style 标签', 'brave-love'),
        'section' => 'brave_custom_code',
        'type' => 'textarea',
        'input_attrs' => array(
            'rows' => 10,
        ),
    ));

    // 底部代码
    $wp_customize->add_setting('brave_footer_code', array(
        'default' => '',
        'sanitize_callback' => 'wp_kses_post',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_footer_code', array(
        'label' => __('底部代码', 'brave-love'),
        'description' => __('统计代码等，会插入在 </body> 之前', 'brave-love'),
        'section' => 'brave_custom_code',
        'type' => 'textarea',
        'input_attrs' => array(
            'rows' => 5,
        ),
    ));
}
add_action('customize_register', 'brave_customize_register');

/**
 * 输出自定义 CSS
 */
function brave_output_custom_css() {
    $custom_css = get_theme_mod('brave_custom_css', '');
    if (!empty($custom_css)) {
        echo '<style type="text/css">' . wp_strip_all_tags($custom_css) . '</style>';
    }
}
add_action('wp_head', 'brave_output_custom_css', 100);

/**
 * 输出底部代码
 */
function brave_output_footer_code() {
    $footer_code = get_theme_mod('brave_footer_code', '');
    if (!empty($footer_code)) {
        echo wp_kses_post($footer_code);
    }
}
add_action('wp_footer', 'brave_output_footer_code', 100);

// ==================== 纪念日管理 ====================

/**
 * 添加纪念日管理页面
 */
function brave_add_anniversary_menu() {
    add_options_page(
        __('纪念日管理', 'brave-love'),
        __('纪念日', 'brave-love'),
        'manage_options',
        'brave-anniversary',
        'brave_anniversary_page'
    );
}
add_action('admin_menu', 'brave_add_anniversary_menu');

/**
 * 纪念日管理页面
 */
function brave_anniversary_page() {
    // 保存数据
    if (isset($_POST['brave_save_anniversaries']) && check_admin_referer('brave_anniversary_nonce')) {
        $anniversaries = array();
        if (isset($_POST['anniversary_name']) && is_array($_POST['anniversary_name'])) {
            foreach ($_POST['anniversary_name'] as $key => $name) {
                $date = sanitize_text_field($_POST['anniversary_date'][$key] ?? '');
                $name = sanitize_text_field($name);
                if (!empty($name) && !empty($date)) {
                    $anniversaries[] = array(
                        'name' => $name,
                        'date' => $date,
                    );
                }
            }
        }
        update_option('brave_anniversaries', $anniversaries);
        echo '<div class="notice notice-success"><p>' . __('已保存', 'brave-love') . '</p></div>';
    }

    $anniversaries = get_option('brave_anniversaries', array());
    ?>
    <div class="wrap">
        <h1><?php _e('纪念日管理', 'brave-love'); ?></h1>
        <p class="description"><?php _e('添加你们的特别日子，如生日、恋爱周年、重要纪念日等。', 'brave-love'); ?></p>
        
        <form method="post" action="" id="anniversary-form">
            <?php wp_nonce_field('brave_anniversary_nonce'); ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 40%;"><?php _e('纪念日名称', 'brave-love'); ?></th>
                        <th style="width: 40%;"><?php _e('日期', 'brave-love'); ?></th>
                        <th style="width: 20%;"><?php _e('操作', 'brave-love'); ?></th>
                    </tr>
                </thead>
                <tbody id="anniversary-list">
                    <?php foreach ($anniversaries as $index => $item) : ?>
                        <tr class="anniversary-item">
                            <td>
                                <input type="text" name="anniversary_name[]" value="<?php echo esc_attr($item['name']); ?>" class="regular-text" placeholder="<?php _e('例如：恋爱一周年', 'brave-love'); ?>">
                            </td>
                            <td>
                                <input type="date" name="anniversary_date[]" value="<?php echo esc_attr($item['date']); ?>" class="regular-text">
                            </td>
                            <td>
                                <button type="button" class="button remove-anniversary"><?php _e('删除', 'brave-love'); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($anniversaries)) : ?>
                        <tr class="anniversary-item empty-row" style="display: none;"></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <p class="submit">
                <button type="button" class="button" id="add-anniversary"><?php _e('添加纪念日', 'brave-love'); ?></button>
                <input type="submit" name="brave_save_anniversaries" class="button button-primary" value="<?php _e('保存', 'brave-love'); ?>">
            </p>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#add-anniversary').on('click', function() {
            var row = '<tr class="anniversary-item">' +
                '<td><input type="text" name="anniversary_name[]" class="regular-text" placeholder="<?php _e('例如：恋爱一周年', 'brave-love'); ?>"></td>' +
                '<td><input type="date" name="anniversary_date[]" class="regular-text"></td>' +
                '<td><button type="button" class="button remove-anniversary"><?php _e('删除', 'brave-love'); ?></button></td>' +
                '</tr>';
            $('#anniversary-list').append(row);
        });
        
        $(document).on('click', '.remove-anniversary', function() {
            $(this).closest('.anniversary-item').remove();
        });
    });
    </script>
    <?php
}
