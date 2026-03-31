<?php
/**
 * 工具函数
 *
 * @package Brave_Love
 */

/**
 * 获取情侣头像
 * 优先使用主题设置的头像，如果没有则使用 WordPress 用户头像
 *
 * @param string $gender 'boy' 或 'girl'
 * @param int $size 头像尺寸
 * @return string 头像URL
 */
function brave_get_couple_avatar($gender = 'boy', $size = 100) {
    // 1. 优先使用主题设置的头像
    $theme_avatar = get_theme_mod('brave_' . $gender . '_avatar');
    if (!empty($theme_avatar)) {
        return esc_url($theme_avatar);
    }
    
    // 2. 尝试获取 WordPress 用户头像
    $user_id = get_theme_mod('brave_' . $gender . '_user_id');
    $user_id = intval($user_id);
    if ($user_id > 0) {
        $wp_avatar = get_avatar_url($user_id, array('size' => $size));
        if ($wp_avatar) {
            return esc_url($wp_avatar);
        }
    }
    
    // 3. 使用默认头像生成服务
    $name = brave_get_couple_name($gender);
    return 'https://ui-avatars.com/api/?name=' . urlencode($name) . '&size=' . $size . '&background=' . ($gender === 'boy' ? '667eea' : 'f5576c') . '&color=fff';
}

/**
 * 获取情侣昵称
 *
 * @param string $gender 'boy' 或 'girl'
 * @return string 昵称
 */
function brave_get_couple_name($gender = 'boy') {
    $name = get_theme_mod('brave_' . $gender . '_name');
    if (!empty($name)) {
        return esc_html($name);
    }
    
    // 尝试获取 WordPress 用户昵称
    $user_id = get_theme_mod('brave_' . $gender . '_user_id');
    $user_id = intval($user_id);
    if ($user_id > 0) {
        $user = get_userdata($user_id);
        if ($user) {
            return esc_html($user->display_name);
        }
    }
    
    return $gender === 'boy' ? '他' : '她';
}

/**
 * 获取头像 HTML
 * 兼容 WordPress 默认头像函数
 *
 * @param int|string $user_id 用户ID或性别(boy/girl)
 * @param int $size 头像尺寸
 * @return string 头像HTML
 */
function brave_get_avatar_html($user_id_or_gender = 'boy', $size = 100) {
    // 如果传入的是性别
    if (in_array($user_id_or_gender, array('boy', 'girl'))) {
        $avatar_url = brave_get_couple_avatar($user_id_or_gender, $size);
        $name = brave_get_couple_name($user_id_or_gender);
        return '<img src="' . $avatar_url . '" alt="' . $name . '" class="avatar avatar-' . $size . ' photo" width="' . $size . '" height="' . $size . '">';
    }
    
    // 如果是用户ID，使用默认 get_avatar
    return get_avatar($user_id_or_gender, $size);
}

/**
 * 获取心情表情
 */
function brave_get_mood_emoji($mood) {
    $moods = array(
        'happy' => '😊',
        'excited' => '🤩',
        'romantic' => '🥰',
        'peaceful' => '😌',
        'touched' => '🥺',
        'miss' => '😢',
    );
    return isset($moods[$mood]) ? $moods[$mood] : '💕';
}

/**
 * 获取心情文字
 */
function brave_get_mood_text($mood) {
    $moods = array(
        'happy' => __('开心', 'brave-love'),
        'excited' => __('兴奋', 'brave-love'),
        'romantic' => __('浪漫', 'brave-love'),
        'peaceful' => __('平静', 'brave-love'),
        'touched' => __('感动', 'brave-love'),
        'miss' => __('想念', 'brave-love'),
    );
    return isset($moods[$mood]) ? $moods[$mood] : '';
}

/**
 * 获取恋爱清单完成进度
 */
function brave_get_list_progress() {
    $total = wp_count_posts('love_list')->publish;
    
    $done_args = array(
        'post_type' => 'love_list',
        'posts_per_page' => -1,
        'meta_query' => array(
            array(
                'key' => '_is_done',
                'value' => '1',
                'compare' => '=',
            ),
        ),
        'fields' => 'ids',
    );
    $done = count(get_posts($done_args));
    
    return array(
        'total' => $total,
        'done' => $done,
        'percentage' => $total > 0 ? round(($done / $total) * 100) : 0,
    );
}

/**
 * 获取说说配图
 */
function brave_get_note_images($post_id, $size = 'medium') {
    $images = get_post_meta($post_id, '_note_images', true);
    if (!is_array($images)) {
        return array();
    }
    
    $urls = array();
    foreach ($images as $image_id) {
        $url = wp_get_attachment_image_url($image_id, $size);
        if ($url) {
            $urls[] = array(
                'id' => $image_id,
                'url' => $url,
                'thumb' => wp_get_attachment_image_url($image_id, 'thumbnail'),
            );
        }
    }
    return $urls;
}

/**
 * 获取点滴年份列表（包括没有见面日期的文章）
 *
 * @return array 年份数组，按降序排列
 */
function brave_get_moment_years() {
    global $wpdb;
    
    // 先从 meta 获取年份
    $meta_years = $wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT YEAR(meta_value) as year
        FROM {$wpdb->postmeta}
        WHERE meta_key = %s
        AND meta_value != ''
        AND meta_value IS NOT NULL
        ORDER BY year DESC
    ", '_meet_date'));
    
    // 从发布日期获取年份（针对没有 _meet_date 的文章）
    $post_years = $wpdb->get_col("
        SELECT DISTINCT YEAR(p.post_date) as year
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id AND pm.meta_key = '_meet_date'
        WHERE p.post_type = 'moment'
        AND p.post_status = 'publish'
        AND (pm.meta_value IS NULL OR pm.meta_value = '')
        ORDER BY year DESC
    ");
    
    // 确保是数组
    $meta_years = is_array($meta_years) ? $meta_years : array();
    $post_years = is_array($post_years) ? $post_years : array();
    
    // 过滤掉 null 和空值，确保都是整数
    $meta_years = array_filter($meta_years, 'is_numeric');
    $post_years = array_filter($post_years, 'is_numeric');
    
    // 合并并去重
    $all_years = array_unique(array_map('intval', array_merge($meta_years, $post_years)));
    
    // 降序排序
    rsort($all_years, SORT_NUMERIC);
    
    return $all_years;
}

/**
 * 格式化日期差
 */
function brave_format_date_diff($date1, $date2 = null) {
    if ($date2 === null) {
        $date2 = current_time('Y-m-d');
    }
    
    $diff = abs(strtotime($date1) - strtotime($date2));
    $days = floor($diff / 86400);
    
    return $days;
}

/**
 * 判断是否是移动端
 */
function brave_is_mobile() {
    if (function_exists('wp_is_mobile')) {
        return wp_is_mobile();
    }
    
    $user_agent = $_SERVER['HTTP_USER_AGENT'];
    $mobile_agents = array('Mobile', 'Android', 'Silk/', 'Kindle', 'BlackBerry', 'Opera Mini', 'Opera Mobi');
    
    foreach ($mobile_agents as $agent) {
        if (strpos($user_agent, $agent) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * 获取主题页面链接
 */
function brave_get_page_link($type) {
    // 恋爱清单使用 CPT archive
    if ($type === 'lists') {
        return get_post_type_archive_link('love_list');
    }
    
    // 其他页面使用设置的页面或默认链接
    $page_id = get_theme_mod("brave_page_{$type}");
    if ($page_id) {
        return get_permalink($page_id);
    }
    
    return home_url("/{$type}/");
}

/**
 * 获取主题选项
 */
function brave_get_option($key, $default = '') {
    return get_theme_mod("brave_{$key}", $default);
}

/**
 * 清理 SVG
 */
function brave_kses_svg($svg) {
    $allowed_tags = array(
        'svg' => array(
            'xmlns' => true,
            'viewbox' => true,
            'width' => true,
            'height' => true,
            'fill' => true,
            'class' => true,
            'preserveaspectratio' => true,
        ),
        'path' => array(
            'd' => true,
            'fill' => true,
            'class' => true,
        ),
        'circle' => array(
            'cx' => true,
            'cy' => true,
            'r' => true,
            'fill' => true,
        ),
        'rect' => array(
            'x' => true,
            'y' => true,
            'width' => true,
            'height' => true,
            'fill' => true,
        ),
        'g' => array(
            'class' => true,
            'transform' => true,
        ),
        'use' => array(
            'href' => true,
            'x' => true,
            'y' => true,
        ),
        'defs' => array(),
    );
    
    return wp_kses($svg, $allowed_tags);
}

/**
 * 获取所有点滴照片（用于瀑布流相册）
 *
 * @param array $args 查询参数
 * @return array 照片数组
 */
function brave_get_all_moment_photos($args = array()) {
    $defaults = array(
        'posts_per_page' => 12,
        'paged' => 1,
        'year' => 0,
    );
    $args = wp_parse_args($args, $defaults);
    
    // 首先获取所有符合条件的 moment 文章
    $query_args = array(
        'post_type' => 'moment',
        'post_status' => 'publish',
        'posts_per_page' => -1,  // 获取所有
        'orderby' => 'meta_value',
        'meta_key' => '_meet_date',
        'order' => 'DESC',
        'fields' => 'ids',
    );
    
    // 按年份筛选
    if (!empty($args['year'])) {
        $query_args['meta_query'] = array(
            array(
                'key' => '_meet_date',
                'value' => array($args['year'] . '-01-01', $args['year'] . '-12-31'),
                'compare' => 'BETWEEN',
                'type' => 'DATE',
            ),
        );
    }
    
    $moment_ids = get_posts($query_args);
    $all_photos = array();
    
    // 收集所有照片
    foreach ($moment_ids as $moment_id) {
        $moment_photos = brave_extract_photos_from_moment($moment_id);
        
        if (!empty($moment_photos)) {
            // 获取 moment 元数据
            $meet_date = get_post_meta($moment_id, '_meet_date', true);
            $location = get_post_meta($moment_id, '_meet_location', true);
            $mood = get_post_meta($moment_id, '_mood', true);
            $summary = get_post_meta($moment_id, '_moment_summary', true);
            
            // 如果没有见面日期，使用发布日期
            if (empty($meet_date)) {
                $meet_date = get_the_date('Y-m-d', $moment_id);
            }
            
            // 格式化日期
            $date_obj = strtotime($meet_date);
            $date_formatted = date_i18n(__('Y年n月j日', 'brave-love'), $date_obj);
            $weekday = date_i18n(__('l', 'brave-love'), $date_obj);
            
            foreach ($moment_photos as $photo) {
                $all_photos[] = array_merge($photo, array(
                    'moment_id'     => $moment_id,
                    'moment_title'  => get_the_title($moment_id),
                    'moment_url'    => get_permalink($moment_id),
                    'date'          => $meet_date,
                    'date_formatted'=> $date_formatted,
                    'weekday'       => $weekday,
                    'location'      => $location,
                    'mood'          => $mood,
                    'mood_emoji'    => brave_get_mood_emoji($mood),
                    'mood_text'     => brave_get_mood_text($mood),
                    'summary'       => $summary ? $summary : wp_trim_words(get_post_field('post_content', $moment_id), 100),
                ));
            }
        }
    }
    
    // 计算分页
    $total_photos = count($all_photos);
    $per_page = $args['posts_per_page'];
    $paged = max(1, $args['paged']);
    
    // 手动分页
    if ($per_page > 0) {
        $offset = ($paged - 1) * $per_page;
        $paged_photos = array_slice($all_photos, $offset, $per_page);
    } else {
        $paged_photos = $all_photos;
    }
    
    $max_num_pages = ($per_page > 0) ? ceil($total_photos / $per_page) : 1;
    
    return array(
        'photos' => $paged_photos,
        'total_photos' => $total_photos,
        'max_num_pages' => $max_num_pages,
    );
}

/**
 * 从点滴文章提取所有照片
 *
 * @param int $moment_id 点滴文章ID
 * @return array 照片数组
 */
function brave_extract_photos_from_moment($moment_id) {
    $photos = array();
    
    // 1. 特色图片作为第一张
    if (has_post_thumbnail($moment_id)) {
        $thumb_id = get_post_thumbnail_id($moment_id);
        $photo_data = brave_get_photo_data($thumb_id);
        if ($photo_data) {
            $photo_data['is_cover'] = true;
            $photos[] = $photo_data;
        }
    }
    
    // 2. 从内容中提取的图片
    $post = get_post($moment_id);
    if (!$post) {
        return $photos;
    }
    
    $content = $post->post_content;
    $image_ids = array();
    
    // 方法1：古腾堡图片块和画廊块
    if (function_exists('parse_blocks')) {
        $blocks = parse_blocks($content);
        foreach ($blocks as $block) {
            // 单张图片块
            if ($block['blockName'] === 'core/image' && !empty($block['attrs']['id'])) {
                $image_ids[] = $block['attrs']['id'];
            }
            // 画廊块
            if ($block['blockName'] === 'core/gallery' && !empty($block['attrs']['ids'])) {
                $image_ids = array_merge($image_ids, $block['attrs']['ids']);
            }
        }
    }
    
    // 方法2：经典编辑器图片 (wp-image-xxx)
    preg_match_all('/wp-image-(\d+)/', $content, $matches);
    if (!empty($matches[1])) {
        $image_ids = array_merge($image_ids, array_map('intval', $matches[1]));
    }
    
    // 去重并获取图片数据
    $image_ids = array_unique($image_ids);
    $cover_id = isset($thumb_id) ? $thumb_id : 0;
    
    foreach ($image_ids as $image_id) {
        // 跳过已添加的特色图片
        if ($image_id == $cover_id) {
            continue;
        }
        
        $photo_data = brave_get_photo_data($image_id);
        if ($photo_data) {
            $photo_data['is_cover'] = false;
            $photos[] = $photo_data;
        }
    }
    
    return $photos;
}

/**
 * 获取单张照片的完整数据
 *
 * @param int $attachment_id 附件ID
 * @return array|false 照片数据
 */
function brave_get_photo_data($attachment_id) {
    // 验证附件是否存在且有效
    if (!wp_attachment_is_image($attachment_id)) {
        return false;
    }
    
    $url = wp_get_attachment_image_url($attachment_id, 'large');
    if (!$url) {
        return false;
    }
    
    $thumb = wp_get_attachment_image_url($attachment_id, 'medium');
    $meta = wp_get_attachment_metadata($attachment_id);
    $attachment_post = get_post($attachment_id);
    
    // 验证缩略图也有效
    if (!$thumb) {
        $thumb = $url;
    }
    
    // 计算宽高比
    $width = !empty($meta['width']) ? $meta['width'] : 0;
    $height = !empty($meta['height']) ? $meta['height'] : 0;
    $aspect_ratio = ($height > 0) ? round($width / $height, 2) : 1;
    
    return array(
        'id'            => $attachment_id,
        'url'           => $url,
        'thumb'         => $thumb,
        'title'         => $attachment_post ? $attachment_post->post_title : '',
        'caption'       => $attachment_post ? $attachment_post->post_excerpt : '',
        'alt'           => get_post_meta($attachment_id, '_wp_attachment_image_alt', true),
        'width'         => $width,
        'height'        => $height,
        'aspect_ratio'  => $aspect_ratio,
    );
}

/**
 * 获取相册可用年份列表
 *
 * @return array 年份数组
 */
function brave_get_gallery_years() {
    global $wpdb;
    
    // 获取有照片的 moment 年份
    $years = $wpdb->get_col("
        SELECT DISTINCT YEAR(pm.meta_value) as year
        FROM {$wpdb->posts} p
        JOIN {$wpdb->postmeta} pm ON p.ID = pm.post_id
        WHERE p.post_type = 'moment'
        AND p.post_status = 'publish'
        AND pm.meta_key = '_meet_date'
        AND pm.meta_value != ''
        AND pm.meta_value IS NOT NULL
        AND (
            p.ID IN (
                SELECT post_id FROM {$wpdb->postmeta} 
                WHERE meta_key = '_thumbnail_id'
            )
            OR p.post_content LIKE '%wp-image-%'
            OR p.post_content LIKE '%<!-- wp:image%'
            OR p.post_content LIKE '%<!-- wp:gallery%'
        )
        ORDER BY year DESC
    ");
    
    return is_array($years) ? array_map('intval', array_filter($years, 'is_numeric')) : array();
}

/**
 * 获取随笔说说的所有年份
 *
 * @return array 年份数组
 */
function brave_get_note_years() {
    global $wpdb;
    
    $years = $wpdb->get_col("
        SELECT DISTINCT YEAR(post_date) as year
        FROM {$wpdb->posts}
        WHERE post_type = 'note'
        AND post_status = 'publish'
        ORDER BY year DESC
    ");
    
    return is_array($years) ? array_map('intval', array_filter($years, 'is_numeric')) : array();
}

/**
 * 获取指定年份的月份
 *
 * @param int $year 年份
 * @return array 月份数组
 */
function brave_get_note_months($year) {
    global $wpdb;
    
    $months = $wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT MONTH(post_date) as month
        FROM {$wpdb->posts}
        WHERE post_type = 'note'
        AND post_status = 'publish'
        AND YEAR(post_date) = %d
        ORDER BY month DESC
    ", $year));
    
    return is_array($months) ? array_map('intval', array_filter($months, 'is_numeric')) : array();
}

/**
 * 获取指定年月的天数
 *
 * @param int $year 年份
 * @param int $month 月份
 * @return array 日期数组
 */
function brave_get_note_days($year, $month) {
    global $wpdb;
    
    $days = $wpdb->get_col($wpdb->prepare("
        SELECT DISTINCT DAY(post_date) as day
        FROM {$wpdb->posts}
        WHERE post_type = 'note'
        AND post_status = 'publish'
        AND YEAR(post_date) = %d
        AND MONTH(post_date) = %d
        ORDER BY day DESC
    ", $year, $month));
    
    return is_array($days) ? array_map('intval', array_filter($days, 'is_numeric')) : array();
}
