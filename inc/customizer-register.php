<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Customizer 注册逻辑。
 *
 * @package Brave_Love
 */

/**
 * Customizer 注册
 */
function brave_customize_register($wp_customize) {
    // 添加主题设置面板
    $wp_customize->add_panel('brave_settings', array(
        'title' => __('Brave 主题设置', 'brave-love'),
        'priority' => 30,
    ));

    // ==================== 首页与情侣 ====================
    $wp_customize->add_section('brave_basic', array(
        'title' => __('首页与情侣', 'brave-love'),
        'description' => __('管理首页计时、顶部导航副标题、默认页面 Hero 背景，以及情侣头像昵称。', 'brave-love'),
        'panel' => 'brave_settings',
    ));

    // 恋爱起始日期时间（精确到分钟）
    $wp_customize->add_setting('brave_love_start_datetime', array(
        'default' => date('Y-m-d') . ' 00:00',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_love_start_datetime', array(
        'label' => __('恋爱起始时间', 'brave-love'),
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
        'label' => __('首页计时器文案', 'brave-love'),
        'description' => __('显示在首页恋爱计时器上方的文字。', 'brave-love'),
        'section' => 'brave_basic',
        'type' => 'text',
    ));

    // ==================== 纪念日与倒计时 ====================
    $wp_customize->add_section('brave_anniversary', array(
        'title' => __('纪念日与倒计时', 'brave-love'),
        'description' => __('首页“特别的日子”列表与顶部倒计时是两套独立内容：列表请前往“设置 > 纪念日管理”，倒计时请在这里单独填写。', 'brave-love'),
        'panel' => 'brave_settings',
    ));

    // 下一个纪念日日期时间（精确到分钟）
    $wp_customize->add_setting('brave_next_anniversary_datetime', array(
        'default' => '',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_next_anniversary_datetime', array(
        'label' => __('下一个倒计时时间', 'brave-love'),
        'description' => __('格式：YYYY-MM-DD HH:MM，例如：2024-12-25 20:00（精确到分钟）', 'brave-love'),
        'section' => 'brave_anniversary',
        'type' => 'text',
    ));

    // 下一个纪念日名称
    $wp_customize->add_setting('brave_next_anniversary_name', array(
        'default' => '恋爱周年纪念日',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_next_anniversary_name', array(
        'label' => __('下一个倒计时名称', 'brave-love'),
        'description' => __('例如：恋爱一周年、100天纪念日等', 'brave-love'),
        'section' => 'brave_anniversary',
        'type' => 'text',
    ));

    // 下一个纪念日倒计时文字
    $wp_customize->add_setting('brave_countdown_text', array(
        'default' => '距离我们的特别日子还有',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_countdown_text', array(
        'label' => __('首页倒计时文案', 'brave-love'),
        'description' => __('显示在首页倒计时上方的文字。', 'brave-love'),
        'section' => 'brave_anniversary',
        'type' => 'text',
    ));

    // 导航栏文字
    $wp_customize->add_setting('brave_nav_text', array(
        'default' => '世间最动情之事，莫过于两人相依',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_nav_text', array(
        'label' => __('顶部导航副标题', 'brave-love'),
        'section' => 'brave_basic',
        'type' => 'text',
    ));

    // 纪念日列表标题
    $wp_customize->add_setting('brave_anniversary_section_title', array(
        'default' => '💕 特别的日子',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_anniversary_section_title', array(
        'label' => __('首页特别日子标题', 'brave-love'),
        'description' => __('显示在首页“特别的日子”列表上方的标题。', 'brave-love'),
        'section' => 'brave_anniversary',
        'type' => 'text',
    ));
    
    // 纪念日管理说明
    $wp_customize->add_setting('brave_anniversary_note', array(
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control(new Brave_Anniversary_Note_Control($wp_customize, 'brave_anniversary_note', array(
        'section' => 'brave_anniversary',
    )));

    // ==================== 恋爱清单归档 ====================
    $wp_customize->add_section('brave_hero', array(
        'title' => __('恋爱清单归档', 'brave-love'),
        'description' => __('恋爱清单是文章归档页，不是普通页面，所以它的 Hero 在这里单独维护；其他内容页面请到对应页面编辑器右侧的“页面 Hero 设置”中配置。', 'brave-love'),
        'panel' => 'brave_settings',
    ));

    // 默认页面 Hero 背景
    $wp_customize->add_setting('brave_hero_bg', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'brave_hero_bg', array(
        'label' => __('默认页面 Hero 背景', 'brave-love'),
        'description' => __('建议尺寸：1920×1080。未在页面编辑器里单独设置 Hero 背景的内容页，会回退到这里。', 'brave-love'),
        'section' => 'brave_basic',
    )));

    // 男生头像
    $wp_customize->add_setting('brave_boy_avatar', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'brave_boy_avatar', array(
        'label' => __('男生头像', 'brave-love'),
        'section' => 'brave_basic',
    )));

    // 男生昵称
    $wp_customize->add_setting('brave_boy_name', array(
        'default' => '他',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_boy_name', array(
        'label' => __('男生昵称', 'brave-love'),
        'section' => 'brave_basic',
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
        'section' => 'brave_basic',
    )));

    // 女生昵称
    $wp_customize->add_setting('brave_girl_name', array(
        'default' => '她',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_girl_name', array(
        'label' => __('女生昵称', 'brave-love'),
        'section' => 'brave_basic',
        'type' => 'text',
    ));

    // 关联 WordPress 用户（可选）
    // 男生关联用户
    $wp_customize->add_setting('brave_boy_user_id', array(
        'default' => '',
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_boy_user_id', array(
        'label' => __('男生关联 WordPress 用户ID', 'brave-love'),
        'description' => __('填写用户 ID。若已上传自定义头像，则头像仍优先使用上传版本；这里主要用于作者头像与昵称映射。', 'brave-love'),
        'section' => 'brave_basic',
        'type' => 'text',
    ));

    // 女生关联用户
    $wp_customize->add_setting('brave_girl_user_id', array(
        'default' => '',
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_girl_user_id', array(
        'label' => __('女生关联 WordPress 用户ID', 'brave-love'),
        'description' => __('填写用户 ID。若已上传自定义头像，则头像仍优先使用上传版本；这里主要用于作者头像与昵称映射。', 'brave-love'),
        'section' => 'brave_basic',
        'type' => 'text',
    ));

    // 恋爱清单归档 Hero 标题
    $wp_customize->add_setting('brave_love_list_hero_title', array(
        'default' => '💕 恋爱清单',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_love_list_hero_title', array(
        'label' => __('Hero 标题', 'brave-love'),
        'description' => __('这里只影响恋爱清单归档页。', 'brave-love'),
        'section' => 'brave_hero',
        'type' => 'text',
    ));

    // 恋爱清单归档 Hero 副标题
    $wp_customize->add_setting('brave_love_list_hero_subtitle', array(
        'default' => '不必惊天动地，只需岁岁相依，这便是我们最好的经历。',
        'sanitize_callback' => 'sanitize_textarea_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_love_list_hero_subtitle', array(
        'label' => __('Hero 副标题', 'brave-love'),
        'section' => 'brave_hero',
        'type' => 'textarea',
    ));

    // 恋爱清单归档 Hero 背景图
    $wp_customize->add_setting('brave_love_list_hero_bg', array(
        'default' => '',
        'sanitize_callback' => 'esc_url_raw',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control(new WP_Customize_Image_Control($wp_customize, 'brave_love_list_hero_bg', array(
        'label' => __('Hero 背景图', 'brave-love'),
        'description' => __('留空则回退到“首页与情侣”里的默认页面 Hero 背景。', 'brave-love'),
        'section' => 'brave_hero',
    )));

    // ==================== 首页入口卡片 ====================
    $wp_customize->add_section('brave_icons', array(
        'title' => __('首页入口卡片', 'brave-love'),
        'description' => __('这里只影响首页“继续逛逛”入口卡片的图标，不影响页脚导航。', 'brave-love'),
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

    // 关于我们图标
    $wp_customize->add_setting('brave_icon_about', array(
        'default' => '💞',
        'sanitize_callback' => 'sanitize_text_field',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_icon_about', array(
        'label' => __('关于我们图标', 'brave-love'),
        'section' => 'brave_icons',
        'type' => 'text',
    ));

    // ==================== 页面绑定 ====================
    $wp_customize->add_section('brave_pages', array(
        'title' => __('页面绑定', 'brave-love'),
        'description' => __('为主题模块绑定实际使用的页面。首页入口、页脚默认链接和主题内部跳转都会优先使用这里。', 'brave-love'),
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

    // 甜蜜相册页面
    $wp_customize->add_setting('brave_page_memories', array(
        'default' => '',
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_page_memories', array(
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

    // 关于我们页面
    $wp_customize->add_setting('brave_page_about', array(
        'default' => '',
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_page_about', array(
        'label' => __('关于我们页面', 'brave-love'),
        'section' => 'brave_pages',
        'type' => 'dropdown-pages',
    ));

    // 旅行计划页面
    $wp_customize->add_setting('brave_page_travels', array(
        'default' => '',
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_page_travels', array(
        'label' => __('旅行计划页面', 'brave-love'),
        'section' => 'brave_pages',
        'type' => 'dropdown-pages',
    ));

    // ==================== 页脚导航 ====================
    $wp_customize->add_section('brave_footer_nav', array(
        'title' => __('页脚导航', 'brave-love'),
        'description' => __('这里只覆盖页脚导航本身：可单独调整顺序、名称和链接。链接留空时，会自动继承“页面绑定”里的目标页面。', 'brave-love'),
        'panel' => 'brave_settings',
    ));

    $footer_nav_defaults = brave_get_footer_nav_defaults();
    $footer_nav_fields = array();

    foreach (brave_get_footer_nav_order() as $key) {
        if (!isset($footer_nav_defaults[$key]['label'])) {
            continue;
        }

        $custom_label = trim((string) get_theme_mod("brave_footer_nav_{$key}_label", ''));
        $footer_nav_fields[$key] = '' !== $custom_label ? $custom_label : $footer_nav_defaults[$key]['label'];
    }

    $wp_customize->add_setting('brave_footer_nav_order', array(
        'default' => implode(',', brave_get_footer_nav_default_order()),
        'sanitize_callback' => 'brave_sanitize_footer_nav_order',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control(new Brave_Sortable_Control($wp_customize, 'brave_footer_nav_order', array(
        'label' => __('页脚导航顺序', 'brave-love'),
        'description' => __('拖拽排序，保存后页脚会按这里的顺序展示。', 'brave-love'),
        'section' => 'brave_footer_nav',
        'choices' => $footer_nav_fields,
        'priority' => 5,
    )));

    $priority = 10;

    foreach ($footer_nav_fields as $key => $label) {
        $wp_customize->add_setting("brave_footer_nav_{$key}_label", array(
            'default' => $label,
            'sanitize_callback' => 'sanitize_text_field',
            'transport' => 'refresh',
        ));
        $wp_customize->add_control("brave_footer_nav_{$key}_label", array(
            'label' => sprintf(__('%s名称', 'brave-love'), $label),
            'section' => 'brave_footer_nav',
            'type' => 'text',
            'priority' => $priority,
        ));
        $priority += 5;

        $wp_customize->add_setting("brave_footer_nav_{$key}_url", array(
            'default' => '',
            'sanitize_callback' => 'brave_sanitize_footer_nav_url',
            'transport' => 'refresh',
        ));
        $wp_customize->add_control("brave_footer_nav_{$key}_url", array(
            'label' => sprintf(__('%s链接', 'brave-love'), $label),
            'description' => __('支持填写完整链接或站内相对路径；留空则自动使用当前页面链接。', 'brave-love'),
            'section' => 'brave_footer_nav',
            'type' => 'text',
            'priority' => $priority,
        ));
        $priority += 5;
    }

    // ==================== 内容展示 ====================
    $wp_customize->add_section('brave_pagination', array(
        'title' => __('内容展示', 'brave-love'),
        'description' => __('控制点点滴滴与甜蜜相册的列表展示规则。', 'brave-love'),
        'panel' => 'brave_settings',
    ));

    // 点滴每页文章数
    $wp_customize->add_setting('brave_moments_per_page', array(
        'default' => 7,
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_moments_per_page', array(
        'label' => __('点点滴滴每页数量', 'brave-love'),
        'description' => __('设置点点滴滴页面每页显示的文章数量。', 'brave-love'),
        'section' => 'brave_pagination',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 1,
            'max' => 50,
            'step' => 1,
        ),
    ));

    // 相册每页照片数
    $wp_customize->add_setting('brave_gallery_per_page', array(
        'default' => 12,
        'sanitize_callback' => 'absint',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_gallery_per_page', array(
        'label' => __('甜蜜相册每页数量', 'brave-love'),
        'description' => __('设置甜蜜相册页面每页显示的照片数量。', 'brave-love'),
        'section' => 'brave_pagination',
        'type' => 'number',
        'input_attrs' => array(
            'min' => 4,
            'max' => 48,
            'step' => 4,
        ),
    ));

    // 显示照片信息
    $wp_customize->add_setting('brave_gallery_show_info', array(
        'default' => true,
        'sanitize_callback' => 'wp_validate_boolean',
        'transport' => 'refresh',
    ));
    $wp_customize->add_control('brave_gallery_show_info', array(
        'label' => __('显示照片附加信息', 'brave-love'),
        'description' => __('在照片悬停时显示日期、地点和心情。', 'brave-love'),
        'section' => 'brave_pagination',
        'type' => 'checkbox',
    ));

    // ==================== 高级 ====================
    $wp_customize->add_section('brave_custom_code', array(
        'title' => __('高级', 'brave-love'),
        'description' => __('放置自定义 CSS 与页脚代码。建议只在明确知道用途时再修改。', 'brave-love'),
        'panel' => 'brave_settings',
    ));

    // 自定义 CSS
    $wp_customize->add_setting('brave_custom_css', array(
        'default' => '',
        'sanitize_callback' => 'brave_sanitize_custom_css',
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
        'sanitize_callback' => 'brave_sanitize_footer_code',
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

    // ==================== 页脚访客统计 ====================
    $wp_customize->add_section('brave_pv_stats', array(
        'title' => __('页脚访客统计', 'brave-love'),
        'description' => __('控制页脚访客信息的显示文案；本组下方的手动计数仍会直接覆盖当前统计值。', 'brave-love'),
        'panel' => 'brave_settings',
    ));

    // 启用 PV 统计
    $wp_customize->add_setting('brave_pv_enabled', array(
        'default' => true,
        'sanitize_callback' => 'brave_sanitize_checkbox',
    ));
    $wp_customize->add_control('brave_pv_enabled', array(
        'label' => __('启用访问统计', 'brave-love'),
        'section' => 'brave_pv_stats',
        'type' => 'checkbox',
    ));

    // 今日文字前缀
    $wp_customize->add_setting('brave_pv_today_prefix', array(
        'default' => __('你是今日第', 'brave-love'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('brave_pv_today_prefix', array(
        'label' => __('今日前缀', 'brave-love'),
        'section' => 'brave_pv_stats',
        'type' => 'text',
    ));

    // 今日文字后缀
    $wp_customize->add_setting('brave_pv_today_suffix', array(
        'default' => __('位访客', 'brave-love'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('brave_pv_today_suffix', array(
        'label' => __('今日后缀', 'brave-love'),
        'section' => 'brave_pv_stats',
        'type' => 'text',
    ));

    // 累计文字前缀
    $wp_customize->add_setting('brave_pv_total_prefix', array(
        'default' => __('累计第', 'brave-love'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('brave_pv_total_prefix', array(
        'label' => __('累计前缀', 'brave-love'),
        'section' => 'brave_pv_stats',
        'type' => 'text',
    ));

    // 累计文字后缀
    $wp_customize->add_setting('brave_pv_total_suffix', array(
        'default' => __('位访客', 'brave-love'),
        'sanitize_callback' => 'sanitize_text_field',
    ));
    $wp_customize->add_control('brave_pv_total_suffix', array(
        'label' => __('累计后缀', 'brave-love'),
        'section' => 'brave_pv_stats',
        'type' => 'text',
    ));

    // 手动修改今日计数
    $wp_customize->add_setting('brave_pv_today_manual', array(
        'default' => '',
        'sanitize_callback' => 'brave_sanitize_pv_number',
    ));
    $wp_customize->add_control('brave_pv_today_manual', array(
        'label' => __('手动设置今日计数', 'brave-love'),
        'description' => __('输入数字后保存即可覆盖当前值，输入0清空今日计数', 'brave-love'),
        'section' => 'brave_pv_stats',
        'type' => 'number',
    ));

    // 手动修改累计计数
    $wp_customize->add_setting('brave_pv_total_manual', array(
        'default' => '',
        'sanitize_callback' => 'brave_sanitize_pv_number',
    ));
    $wp_customize->add_control('brave_pv_total_manual', array(
        'label' => __('手动设置累计计数', 'brave-love'),
        'description' => __('输入数字后保存即可覆盖当前值', 'brave-love'),
        'section' => 'brave_pv_stats',
        'type' => 'number',
    ));

    // 当前数值显示（只读）
    $wp_customize->add_setting('brave_pv_current_stats', array(
        'default' => '',
    ));
    $wp_customize->add_control(new Brave_PV_Stats_Control($wp_customize, 'brave_pv_current_stats', array(
        'section' => 'brave_pv_stats',
        'label' => __('当前数值', 'brave-love'),
    )));

}
add_action('customize_register', 'brave_customize_register');
