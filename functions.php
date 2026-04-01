<?php
/**
 * Brave Love Theme Functions
 *
 * @package Brave_Love
 */

// 定义常量
define('BRAVE_VERSION', '0.6.27');
define('BRAVE_DIR', get_template_directory());
define('BRAVE_URI', get_template_directory_uri());

/**
 * 主题初始化
 */
function brave_setup() {
    // 加载语言包
    load_theme_textdomain('brave-love', BRAVE_DIR . '/languages');

    // 添加主题支持
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('html5', array(
        'search-form',
        'comment-form',
        'comment-list',
        'gallery',
        'caption',
        'script',
        'style',
    ));
    add_theme_support('responsive-embeds');
    add_theme_support('customize-selective-refresh-widgets');

    // 设置特色图片尺寸
    set_post_thumbnail_size(400, 400, true);
    add_image_size('timeline-thumb', 300, 300, true);

    // 注册导航菜单
    register_nav_menus(array(
        'primary' => __('主导航', 'brave-love'),
    ));
}
add_action('after_setup_theme', 'brave_setup');

/**
 * 加载样式和脚本
 */
function brave_scripts() {
    // Bootstrap 5 CSS
    wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css', array(), '5.3.2');
    
    // 主题样式
    wp_enqueue_style('brave-style', get_stylesheet_uri(), array(), BRAVE_VERSION);
    
    // 额外样式
    wp_enqueue_style('brave-extra', BRAVE_URI . '/assets/css/brave.css', array(), BRAVE_VERSION);
    
    // PhotoSwipe CSS
    wp_enqueue_style('photoswipe', 'https://cdn.jsdelivr.net/npm/photoswipe@5.4.2/dist/photoswipe.css', array(), '5.4.2');
    
    // Bootstrap JS
    wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js', array(), '5.3.2', true);
    
    // PhotoSwipe JS
    wp_enqueue_script('photoswipe', 'https://cdn.jsdelivr.net/npm/photoswipe@5.4.2/dist/photoswipe.umd.min.js', array(), '5.4.2', true);
    wp_enqueue_script('photoswipe-lightbox', 'https://cdn.jsdelivr.net/npm/photoswipe@5.4.2/dist/photoswipe-lightbox.umd.min.js', array('photoswipe'), '5.4.2', true);
    
    // 主题脚本
    wp_enqueue_script('brave-script', BRAVE_URI . '/assets/js/brave.js', array('jquery'), BRAVE_VERSION, true);
    
    // 传递数据到 JS
    $theme_options = array(
        'love_start_datetime' => get_theme_mod('brave_love_start_datetime', '2020-01-01 00:00'),
        'next_anniversary_datetime' => get_theme_mod('brave_next_anniversary_datetime', ''),
        'next_anniversary_name' => get_theme_mod('brave_next_anniversary_name', ''),
        'ajax_url' => admin_url('admin-ajax.php'),
        'home_url' => home_url(),
    );
    wp_localize_script('brave-script', 'braveData', $theme_options);
    
    // 评论脚本
    if (is_singular() && comments_open() && get_option('thread_comments')) {
        wp_enqueue_script('comment-reply');
    }
    
    // 恋爱清单存档页面样式
    if (is_post_type_archive('love_list')) {
        wp_enqueue_style('brave-love-list', BRAVE_URI . '/assets/css/love-list.css', array(), BRAVE_VERSION);
    }
    
    // 随笔说说页面样式
    if (is_page_template('page-templates/page-notes.php')) {
        wp_enqueue_style('brave-notes', BRAVE_URI . '/assets/css/notes.css', array(), BRAVE_VERSION);
    }
    
    // 甜蜜相册页面样式和脚本
    if (is_page_template('page-templates/page-memories.php')) {
        wp_enqueue_style('brave-memory', BRAVE_URI . '/assets/css/memory.css', array(), BRAVE_VERSION);
        wp_enqueue_script('brave-memory', BRAVE_URI . '/assets/js/memory.js', array('photoswipe', 'photoswipe-lightbox'), BRAVE_VERSION, true);
    }
}
add_action('wp_enqueue_scripts', 'brave_scripts');

/**
 * 加载后台样式和脚本
 */
function brave_admin_scripts($hook) {
    $theme_uri = BRAVE_URI;
    $version = BRAVE_VERSION;
    
    // 在所有后台页面加载样式
    wp_enqueue_style('brave-admin', $theme_uri . '/assets/css/admin.css', array(), $version);
}
add_action('admin_enqueue_scripts', 'brave_admin_scripts');

/**
 * 注册自定义文章类型
 */
require BRAVE_DIR . '/inc/post-types.php';

/**
 * 自定义字段
 */
require BRAVE_DIR . '/inc/meta-boxes.php';

/**
 * Customizer 设置
 */
require BRAVE_DIR . '/inc/customizer.php';

/**
 * 天气管理
 */
require BRAVE_DIR . '/inc/weather-admin.php';

/**
 * 相册数据管理
 */
require BRAVE_DIR . '/inc/gallery-admin.php';

/**
 * 短代码
 */
require BRAVE_DIR . '/inc/shortcodes.php';

/**
 * 工具函数
 */
require BRAVE_DIR . '/inc/helpers.php';

/**
 * 禁用 WordPress 默认功能
 */
function brave_disable_defaults() {
    // 禁用 Emoji
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    
    // 移除 wp-embed
    wp_deregister_script('wp-embed');
}
add_action('init', 'brave_disable_defaults');

/**
 * 自定义上传图片质量
 */
function brave_jpeg_quality($quality) {
    return 85;
}
add_filter('jpeg_quality', 'brave_jpeg_quality');

/**
 * 移除导航菜单的 li 多余 class/id
 */
function brave_nav_menu_css_class($classes) {
    $keep = array('current-menu-item', 'menu-item-has-children', 'dropdown');
    return array_intersect($classes, $keep);
}
add_filter('nav_menu_css_class', 'brave_nav_menu_css_class', 10, 1);

/**
 * 评论表单字段排序
 */
function brave_comment_fields($fields) {
    $commenter = wp_get_current_commenter();
    $req = get_option('require_name_email');
    $aria_req = ($req ? " aria-required='true'" : '');
    
    $fields['author'] = '<div class="form-group">' .
        '<input type="text" name="author" id="author" class="form-control" placeholder="' . __('姓名或昵称 *', 'brave-love') . '" value="' . esc_attr($commenter['comment_author']) . '"' . $aria_req . ' />' .
        '</div>';
    
    $fields['email'] = '<div class="form-group">' .
        '<input type="email" name="email" id="email" class="form-control" placeholder="' . __('邮箱 *', 'brave-love') . '" value="' . esc_attr($commenter['comment_author_email']) . '"' . $aria_req . ' />' .
        '</div>';
    
    $fields['url'] = '<div class="form-group">' .
        '<input type="url" name="url" id="url" class="form-control" placeholder="' . __('网站（可选）', 'brave-love') . '" value="' . esc_attr($commenter['comment_author_url']) . '" />' .
        '</div>';
    
    return $fields;
}
add_filter('comment_form_default_fields', 'brave_comment_fields');

/**
 * 评论表单主体
 */
function brave_comment_form_defaults($defaults) {
    $defaults['comment_field'] = '<div class="form-group">' .
        '<textarea name="comment" id="comment" class="form-control" placeholder="' . __('写下对我们的祝福...', 'brave-love') . '" rows="4" required></textarea>' .
        '</div>';
    
    $defaults['submit_button'] = '<button type="submit" class="btn-submit">' . __('发送祝福', 'brave-love') . '</button>';
    $defaults['title_reply'] = '';
    $defaults['title_reply_to'] = '';
    $defaults['cancel_reply_link'] = __('取消回复', 'brave-love');
    $defaults['label_submit'] = __('发送祝福', 'brave-love');
    $defaults['class_form'] = 'comment-form';
    $defaults['class_submit'] = 'btn-submit';
    
    return $defaults;
}
add_filter('comment_form_defaults', 'brave_comment_form_defaults');

/**
 * 评论列表回调
 */
function brave_comment_callback($comment, $args, $depth) {
    $GLOBALS['comment'] = $comment;
    ?>
    <div id="comment-<?php comment_ID(); ?>" <?php comment_class('comment-item'); ?>>
        <?php echo get_avatar($comment, 40, '', '', array('class' => 'comment-avatar')); ?>
        <div class="comment-body">
            <div class="comment-header">
                <span class="comment-author"><?php comment_author(); ?></span>
                <span class="comment-date"><?php echo human_time_diff(get_comment_time('U'), current_time('timestamp')) . __('前', 'brave-love'); ?></span>
            </div>
            <div class="comment-text"><?php comment_text(); ?></div>
        </div>
    </div>
    <?php
}

/**
 * 获取恋爱天数
 */
function brave_get_love_days() {
    $start_date = get_theme_mod('brave_love_start_date', '2020-01-01');
    $start = strtotime($start_date);
    $now = current_time('timestamp');
    $days = floor(($now - $start) / 86400);
    return max(0, $days);
}

/**
 * 获取纪念日数据
 */
function brave_get_anniversaries() {
    $anniversaries = get_option('brave_anniversaries', array());
    $result = array();
    
    if (!empty($anniversaries) && is_array($anniversaries)) {
        foreach ($anniversaries as $item) {
            if (empty($item['name']) || empty($item['date'])) continue;
            
            $date = strtotime($item['date']);
            $now = strtotime(current_time('Y-m-d'));
            $diff = $date - $now;
            $days = abs(floor($diff / 86400));
            $is_countdown = $diff > 0;
            
            $result[] = array(
                'name' => $item['name'],
                'date' => $item['date'],
                'days' => $days,
                'is_countdown' => $is_countdown,
            );
        }
    }
    
    // 按倒计时天数排序（即将到的在前）
    usort($result, function($a, $b) {
        if ($a['is_countdown'] && !$b['is_countdown']) return -1;
        if (!$a['is_countdown'] && $b['is_countdown']) return 1;
        return $a['days'] - $b['days'];
    });
    
    return $result;
}

/**
 * 获取头像 URL（支持 QQ 邮箱）
 */
function brave_get_avatar_url($email) {
    if (strpos($email, '@qq.com') !== false) {
        $qq = str_replace('@qq.com', '', $email);
        if (is_numeric($qq)) {
            return 'https://q1.qlogo.cn/g?b=qq&nk=' . $qq . '&s=100';
        }
    }
    return get_avatar_url($email, array('size' => 100));
}

/**
 * 页面模板跳转
 */
function brave_page_template_redirect() {
    if (is_post_type_archive('moment')) {
        $page = get_page_by_path('moments');
        if ($page) {
            wp_redirect(get_permalink($page));
            exit;
        }
    }
}
add_action('template_redirect', 'brave_page_template_redirect');

/**
 * 添加 body class
 */
function brave_body_classes($classes) {
    if (is_page_template('page-templates/page-home.php')) {
        $classes[] = 'page-home';
    }
    return $classes;
}
add_filter('body_class', 'brave_body_classes');

/**
 * 处理前台发布说说
 */
function brave_handle_frontend_note_publish() {
    // 检查是否有提交
    if (!isset($_POST['publish_note']) || !isset($_POST['publish_note_nonce'])) {
        return;
    }
    
    // 验证 nonce
    if (!wp_verify_nonce($_POST['publish_note_nonce'], 'publish_note_action')) {
        wp_die('安全验证失败');
    }
    
    // 检查用户是否登录
    if (!is_user_logged_in()) {
        wp_die('请先登录');
    }
    
    // 获取内容
    $content = isset($_POST['note_content']) ? sanitize_textarea_field($_POST['note_content']) : '';
    
    if (empty($content)) {
        wp_die('请输入说说内容');
    }
    
    // 获取心情和思念度
    $mood = isset($_POST['note_mood']) ? sanitize_text_field($_POST['note_mood']) : '😊';
    $miss_level = isset($_POST['note_miss_level']) ? intval($_POST['note_miss_level']) : 3;
    
    // 创建文章
    $post_data = array(
        'post_title'   => wp_trim_words($content, 20),
        'post_content' => $content,
        'post_status'  => 'publish',
        'post_type'    => 'note',
        'post_author'  => get_current_user_id(),
    );
    
    $post_id = wp_insert_post($post_data);
    
    if (is_wp_error($post_id)) {
        wp_die('发布失败，请重试');
    }
    
    // 保存元数据
    update_post_meta($post_id, '_note_mood', $mood);
    update_post_meta($post_id, '_note_miss_level', $miss_level);
    
    // 重定向回当前页面
    wp_redirect($_SERVER['HTTP_REFERER']);
    exit;
}
add_action('template_redirect', 'brave_handle_frontend_note_publish');

/**
 * 祝福留言必须审核
 * 所有新评论都需要管理员审核
 */
add_filter('pre_comment_approved', 'brave_comment_moderation', 99, 2);
function brave_comment_moderation($approved, $commentdata) {
    // 所有评论都需要审核
    return 0; // 0 = 待审核
}

/**
 * PV 访问统计
 */
function brave_update_pv_stats() {
    // 检查是否启用
    if (!get_theme_mod('brave_pv_enabled', '1')) {
        return;
    }
    
    $today = date('Y-m-d');
    
    // 获取统计数据
    $stats = get_option('brave_pv_stats', array(
        'today_date' => $today,
        'today_count' => 0,
        'total_count' => 0,
    ));
    
    // 检查手动覆盖（使用单独的option标记已应用）
    $manual_today = get_theme_mod('brave_pv_today_manual');
    $manual_total = get_theme_mod('brave_pv_total_manual');
    
    if ($manual_today !== '' && $manual_today !== false && $manual_today !== null) {
        $stats['today_count'] = intval($manual_today);
        // 应用后清空
        remove_theme_mod('brave_pv_today_manual');
    }
    
    if ($manual_total !== '' && $manual_total !== false && $manual_total !== null) {
        $stats['total_count'] = intval($manual_total);
        // 应用后清空
        remove_theme_mod('brave_pv_total_manual');
    }
    
    // 检查是否跨天，重置当日计数
    if ($stats['today_date'] !== $today) {
        $stats['today_date'] = $today;
        $stats['today_count'] = 0;
    }
    
    // 增加计数
    $stats['today_count']++;
    $stats['total_count']++;
    
    // 保存
    update_option('brave_pv_stats', $stats);
}
add_action('wp', 'brave_update_pv_stats');

/**
 * 获取 PV 统计数据
 */
function brave_get_pv_stats() {
    $stats = get_option('brave_pv_stats', array(
        'today_date' => date('Y-m-d'),
        'today_count' => 0,
        'total_count' => 0,
    ));
    
    // 如果日期不对，今日计数为0
    if ($stats['today_date'] !== date('Y-m-d')) {
        $stats['today_count'] = 0;
    }
    
    return $stats;
}

/**
 * 获取 PV 显示文字
 */
function brave_get_pv_display_text($type = 'today_prefix') {
    $defaults = array(
        'today_prefix' => __('你是今日第', 'brave-love'),
        'today_suffix' => __('位访客', 'brave-love'),
        'total_prefix' => __('累计第', 'brave-love'),
        'total_suffix' => __('位访客', 'brave-love'),
    );
    
    return get_theme_mod("brave_pv_{$type}", $defaults[$type] ?? '');
}
