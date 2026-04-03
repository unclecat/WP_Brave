<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 工具函数
 *
 * @package Brave_Love
 */

/**
 * 校验 ISO 日期字符串。
 *
 * @param string $value 日期字符串
 * @return bool
 */
function brave_is_valid_iso_date($value) {
    $value = trim((string) $value);

    if ('' === $value) {
        return false;
    }

    $date = DateTime::createFromFormat('!Y-m-d', $value);

    return $date instanceof DateTime && $date->format('Y-m-d') === $value;
}

/**
 * 清理 ISO 日期字符串，非法值返回空字符串。
 *
 * @param string $value 日期字符串
 * @return string
 */
function brave_sanitize_iso_date($value) {
    $value = trim((string) $value);

    return brave_is_valid_iso_date($value) ? $value : '';
}

/**
 * 清理经纬度。
 *
 * @param string $value     原始值
 * @param float  $min       最小值
 * @param float  $max       最大值
 * @param int    $precision 保留小数位
 * @return string
 */
function brave_sanitize_coordinate($value, $min, $max, $precision = 4) {
    $value = trim((string) $value);

    if ('' === $value || !is_numeric($value)) {
        return '';
    }

    $number = (float) $value;

    if ($number < $min || $number > $max) {
        return '';
    }

    return number_format($number, max(0, absint($precision)), '.', '');
}

/**
 * 读取布尔型主题设置。
 *
 * @param string $key     设置键名
 * @param bool   $default 默认值
 * @return bool
 */
function brave_theme_mod_enabled($key, $default = false) {
    return wp_validate_boolean(get_theme_mod($key, $default ? '1' : '0'));
}

/**
 * 允许 data URI 作为主题内联头像输出。
 *
 * @param string $url 头像 URL
 * @return string
 */
function brave_esc_avatar_url($url) {
    return esc_url($url, array('http', 'https', 'data'));
}

/**
 * 获取主题 Hero 背景样式。
 *
 * 优先使用后台配置的 Hero 图，缺失时回退到主题内渐变背景，
 * 供首页 Hero 和其他页面的通用 Hero 复用。
 *
 * @param string $background_url 指定背景图 URL，留空时回退到全局 Hero 图
 * @return string
 */
function brave_get_hero_background_style($background_url = '') {
    $hero_bg = $background_url ? $background_url : get_theme_mod('brave_hero_bg');

    if (!empty($hero_bg)) {
        return "background-image: url('" . esc_url_raw($hero_bg) . "');";
    }

    return 'background-image: radial-gradient(circle at top, rgba(255, 255, 255, 0.22), transparent 36%), linear-gradient(135deg, #ff9a9e 0%, #fad0c4 42%, #ffd1ff 100%);';
}

/**
 * 获取页面 Hero 自定义字段。
 *
 * @param int $post_id 页面 ID
 * @return array
 */
function brave_get_page_hero_meta($post_id) {
    $post_id = absint($post_id);

    if ($post_id <= 0) {
        return array(
            'title' => '',
            'subtitle' => '',
            'background' => '',
        );
    }

    return array(
        'title' => get_post_meta($post_id, '_brave_page_hero_title', true),
        'subtitle' => get_post_meta($post_id, '_brave_page_hero_subtitle', true),
        'background' => get_post_meta($post_id, '_brave_page_hero_bg', true),
    );
}

/**
 * 解析当前页面 Hero 最终配置。
 *
 * 页面模板优先读取当前页面的 Hero 自定义字段；
 * 恋爱清单归档则读取主题设置中的归档 Hero 配置；
 * 其余字段回退到模板传入的默认值。
 *
 * @param array $args 默认配置
 * @return array
 */
function brave_resolve_page_hero_args($args = array()) {
    $resolved = wp_parse_args(
        $args,
        array(
            'context' => '',
            'post_id' => 0,
            'eyebrow' => '',
            'title' => get_the_title(),
            'subtitle' => '',
            'background' => '',
        )
    );

    if ('love_list_archive' === $resolved['context']) {
        $resolved['title'] = get_theme_mod('brave_love_list_hero_title', $resolved['title']);
        $resolved['subtitle'] = get_theme_mod('brave_love_list_hero_subtitle', $resolved['subtitle']);
        $resolved['background'] = get_theme_mod('brave_love_list_hero_bg', $resolved['background']);
    } else {
        $post_id = $resolved['post_id'] ? absint($resolved['post_id']) : get_the_ID();
        $meta = brave_get_page_hero_meta($post_id);

        if (!empty($meta['title'])) {
            $resolved['title'] = $meta['title'];
        }

        if (!empty($meta['subtitle'])) {
            $resolved['subtitle'] = $meta['subtitle'];
        }

        if (!empty($meta['background'])) {
            $resolved['background'] = $meta['background'];
        }
    }

    $resolved['background_style'] = brave_get_hero_background_style($resolved['background']);

    return $resolved;
}

/**
 * 获取头像占位字符。
 *
 * @param string $text 原始文本
 * @return string
 */
function brave_get_avatar_character($text) {
    $text = html_entity_decode(wp_strip_all_tags((string) $text), ENT_QUOTES, get_bloginfo('charset'));
    $chars = preg_split('//u', trim($text), -1, PREG_SPLIT_NO_EMPTY);

    if (empty($chars)) {
        return '?';
    }

    $char = $chars[0];

    if (preg_match('/^[a-z]$/i', $char)) {
        return strtoupper($char);
    }

    return $char;
}

/**
 * 获取头像文本标签。
 *
 * @param string $name 名称
 * @return string
 */
function brave_get_avatar_label($name) {
    $name = html_entity_decode(wp_strip_all_tags((string) $name), ENT_QUOTES, get_bloginfo('charset'));
    $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY);

    if (empty($parts)) {
        return '?';
    }

    if (count($parts) === 1) {
        return brave_get_avatar_character($parts[0]);
    }

    $label = '';

    foreach (array_slice($parts, 0, 2) as $part) {
        $label .= brave_get_avatar_character($part);
    }

    return $label ?: '?';
}

/**
 * 规范化头像颜色值。
 *
 * @param string $color 颜色值
 * @param string $default 默认颜色
 * @return string
 */
function brave_normalize_avatar_color($color, $default = 'ff5162') {
    $color = preg_replace('/[^0-9a-f]/i', '', (string) $color);

    if (strlen($color) === 3 || strlen($color) === 6) {
        return strtolower($color);
    }

    return $default;
}

/**
 * 生成本地 SVG 占位头像。
 *
 * @param string $name 显示名称
 * @param int $size 头像尺寸
 * @param string $background 背景色
 * @param string $foreground 前景色
 * @return string
 */
function brave_get_placeholder_avatar_url($name = '', $size = 100, $background = 'ff5162', $foreground = 'ffffff') {
    $label = brave_get_avatar_label($name);
    $background = brave_normalize_avatar_color($background, 'ff5162');
    $foreground = brave_normalize_avatar_color($foreground, 'ffffff');
    $size = max(40, absint($size));

    $svg = sprintf(
        '<svg xmlns="http://www.w3.org/2000/svg" width="%1$d" height="%1$d" viewBox="0 0 100 100" role="img" aria-hidden="true"><rect width="100" height="100" rx="50" fill="#%2$s"/><text x="50" y="54" fill="#%3$s" font-family="-apple-system,BlinkMacSystemFont,Segoe UI,sans-serif" font-size="40" font-weight="700" text-anchor="middle">%4$s</text></svg>',
        $size,
        $background,
        $foreground,
        htmlspecialchars($label, ENT_QUOTES, 'UTF-8')
    );

    return 'data:image/svg+xml;charset=UTF-8,' . rawurlencode($svg);
}

/**
 * 判断头像 URL 是否来自站点本地资源。
 *
 * @param string $url 头像 URL
 * @return bool
 */
function brave_is_local_avatar_url($url) {
    if (empty($url)) {
        return false;
    }

    if (strpos($url, 'data:image/') === 0) {
        return true;
    }

    $avatar_host = wp_parse_url($url, PHP_URL_HOST);

    if (empty($avatar_host)) {
        return true;
    }

    $site_host = wp_parse_url(home_url('/'), PHP_URL_HOST);

    if (empty($site_host)) {
        return false;
    }

    return strtolower($avatar_host) === strtolower($site_host);
}

/**
 * 获取仅允许本地站点资源的 WordPress 头像 URL。
 *
 * @param mixed $id_or_email 用户 ID、邮箱或评论对象
 * @param int $size 头像尺寸
 * @return string
 */
function brave_get_safe_wp_avatar_url($id_or_email, $size = 100) {
    $avatar_url = get_avatar_url($id_or_email, array('size' => absint($size)));

    if ($avatar_url && brave_is_local_avatar_url($avatar_url)) {
        return esc_url_raw($avatar_url);
    }

    return '';
}

/**
 * 获取普通用户头像，优先使用站点内头像，否则使用本地 SVG 占位头像。
 *
 * @param int $user_id 用户 ID
 * @param string $name 显示名称
 * @param int $size 头像尺寸
 * @param string $background 背景色
 * @return string
 */
function brave_get_person_avatar_url($user_id = 0, $name = '', $size = 100, $background = 'ff5162') {
    $user_id = absint($user_id);

    if ($user_id > 0) {
        $avatar_url = brave_get_safe_wp_avatar_url($user_id, $size);

        if ($avatar_url) {
            return $avatar_url;
        }

        if (empty($name)) {
            $user = get_userdata($user_id);
            if ($user) {
                $name = $user->display_name;
            }
        }
    }

    return brave_get_placeholder_avatar_url($name, $size, $background);
}

/**
 * 获取评论头像 URL。
 *
 * @param WP_Comment|int $comment 评论对象或评论 ID
 * @param int $size 头像尺寸
 * @param string $background 背景色
 * @return string
 */
function brave_get_comment_avatar_url($comment, $size = 100, $background = 'ff5162') {
    $comment = get_comment($comment);

    if (!$comment) {
        return brave_get_placeholder_avatar_url('', $size, $background);
    }

    if (!empty($comment->user_id)) {
        return brave_get_person_avatar_url($comment->user_id, $comment->comment_author, $size, $background);
    }

    return brave_get_avatar_url($comment->comment_author_email, $comment->comment_author, $size, $background);
}

/**
 * 获取祝福留言页的卡通头像池。
 *
 * @return array
 */
function brave_get_blessing_avatar_pool() {
    return array(
        'avatar-01.svg',
        'avatar-02.svg',
        'avatar-03.svg',
        'avatar-04.svg',
        'avatar-05.svg',
        'avatar-06.svg',
        'avatar-07.svg',
        'avatar-08.svg',
    );
}

/**
 * 为祝福留言评论分配稳定的本地卡通头像。
 *
 * 这里使用“稳定随机”策略：同一个昵称/邮箱组合始终映射到同一张
 * 本地卡通头像，避免每次刷新页面头像都变化。
 *
 * @param WP_Comment|int $comment 评论对象或评论 ID
 * @return string
 */
function brave_get_blessing_avatar_url($comment) {
    $comment = get_comment($comment);

    if (!$comment) {
        return esc_url_raw(BRAVE_URI . '/assets/images/blessing-avatars/avatar-01.svg');
    }

    $pool = brave_get_blessing_avatar_pool();
    $seed = strtolower(trim($comment->comment_author_email . '|' . $comment->comment_author));

    if (empty($seed) || $seed === '|') {
        $seed = 'comment-' . $comment->comment_ID;
    }

    $index = absint(crc32($seed)) % count($pool);

    return esc_url_raw(BRAVE_URI . '/assets/images/blessing-avatars/' . $pool[$index]);
}

/**
 * 获取情侣头像
 * 优先使用主题设置的头像，如果没有则使用站点内头像，再回退到本地占位头像
 *
 * @param string $gender 'boy' 或 'girl'
 * @param int $size 头像尺寸
 * @return string 头像URL
 */
function brave_get_couple_avatar($gender = 'boy', $size = 100) {
    $theme_avatar = get_theme_mod('brave_' . $gender . '_avatar');
    if (!empty($theme_avatar)) {
        return esc_url_raw($theme_avatar);
    }

    $user_id = get_theme_mod('brave_' . $gender . '_user_id');
    $user_id = absint($user_id);
    if ($user_id > 0) {
        $wp_avatar = brave_get_safe_wp_avatar_url($user_id, $size);
        if ($wp_avatar) {
            return $wp_avatar;
        }
    }

    return brave_get_placeholder_avatar_url(
        brave_get_couple_name($gender),
        $size,
        $gender === 'boy' ? '667eea' : 'f5576c'
    );
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
        return sanitize_text_field($name);
    }
    
    // 尝试获取 WordPress 用户昵称
    $user_id = get_theme_mod('brave_' . $gender . '_user_id');
    $user_id = absint($user_id);
    if ($user_id > 0) {
        $user = get_userdata($user_id);
        if ($user) {
            return sanitize_text_field($user->display_name);
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
    if (in_array($user_id_or_gender, array('boy', 'girl'))) {
        $avatar_url = brave_get_couple_avatar($user_id_or_gender, $size);
        $name = brave_get_couple_name($user_id_or_gender);
        return '<img src="' . brave_esc_avatar_url($avatar_url) . '" alt="' . esc_attr($name) . '" class="avatar avatar-' . absint($size) . ' photo" width="' . absint($size) . '" height="' . absint($size) . '">';
    }

    $avatar_url = brave_get_person_avatar_url($user_id_or_gender, '', $size);

    return '<img src="' . brave_esc_avatar_url($avatar_url) . '" alt="" class="avatar avatar-' . absint($size) . ' photo" width="' . absint($size) . '" height="' . absint($size) . '">';
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
 * 获取恋爱清单完成进度。
 *
 * @param int $term_id 可选的分类 ID，用于分类页统计。
 */
function brave_get_list_progress($term_id = 0) {
    $term_id = absint($term_id);

    $base_args = array(
        'post_type' => 'love_list',
        'post_status' => 'publish',
        'posts_per_page' => -1,
        'fields' => 'ids',
        'no_found_rows' => true,
    );

    if ($term_id > 0) {
        $base_args['tax_query'] = array(
            array(
                'taxonomy' => 'list_category',
                'field' => 'term_id',
                'terms' => $term_id,
            ),
        );
    }

    $total = count(get_posts($base_args));

    $done_args = $base_args;
    $done_args['meta_query'] = array(
        array(
            'key' => '_is_done',
            'value' => '1',
            'compare' => '=',
        ),
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
        FROM {$wpdb->postmeta} pm
        INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
        WHERE pm.meta_key = %s
        AND p.post_type = 'moment'
        AND p.post_status = 'publish'
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
 * 获取点滴的有效日期。
 *
 * 优先使用 _meet_date，缺失时回退到文章发布日期，
 * 保证展示、筛选、排序使用同一套日期口径。
 *
 * @param int $moment_id 点滴文章 ID
 * @return string 日期字符串
 */
function brave_get_moment_effective_date($moment_id) {
    $meet_date = get_post_meta($moment_id, '_meet_date', true);
    if (!empty($meet_date)) {
        return $meet_date;
    }

    return get_the_date('Y-m-d', $moment_id);
}

/**
 * 获取点滴有效日期对应的 SQL 表达式。
 *
 * @param string $post_alias 文章表别名
 * @param string $meta_alias 元数据表别名
 * @return string SQL 表达式
 */
function brave_get_moment_effective_date_sql($post_alias = 'p', $meta_alias = 'pm') {
    return "COALESCE(NULLIF({$meta_alias}.meta_value, ''), DATE({$post_alias}.post_date))";
}

/**
 * 按有效日期获取点滴文章 ID 列表。
 *
 * @param int $year 年份，0 表示全部
 * @return array 点滴文章 ID 数组
 */
function brave_get_moment_ids_by_effective_date($year = 0) {
    global $wpdb;

    $year = absint($year);
    $effective_date_sql = brave_get_moment_effective_date_sql('p', 'pm');

    $query = "
        SELECT p.ID
        FROM {$wpdb->posts} p
        LEFT JOIN {$wpdb->postmeta} pm
            ON p.ID = pm.post_id
            AND pm.meta_key = %s
        WHERE p.post_type = 'moment'
        AND p.post_status = 'publish'
    ";

    if ($year > 0) {
        $query .= $wpdb->prepare(" AND YEAR({$effective_date_sql}) = %d", $year);
    }

    $query .= " ORDER BY {$effective_date_sql} DESC, p.ID DESC";

    $ids = $wpdb->get_col($wpdb->prepare($query, '_meet_date'));

    return is_array($ids) ? array_map('intval', $ids) : array();
}

/**
 * 获取指定年份的点滴文章 ID 列表。
 *
 * 使用 _meet_date 作为主日期；缺失时回退到文章发布日期，
 * 保证年份筛选与页面展示逻辑一致。
 *
 * @param int $year 年份
 * @return array 点滴文章 ID 数组
 */
function brave_get_moment_ids_by_year($year) {
    return brave_get_moment_ids_by_effective_date($year);
}

/**
 * 获取附件缓存版本号。
 *
 * @return int 缓存版本
 */
function brave_get_gallery_attachment_cache_version() {
    return max(1, absint(get_option('brave_gallery_attachment_cache_version', 1)));
}

/**
 * 递增附件缓存版本号。
 */
function brave_bump_gallery_attachment_cache_version() {
    update_option(
        'brave_gallery_attachment_cache_version',
        brave_get_gallery_attachment_cache_version() + 1,
        false
    );
}

/**
 * 生成单篇点滴照片缓存签名。
 *
 * @param int     $moment_id 点滴文章 ID
 * @param WP_Post $post      点滴文章对象
 * @return string 缓存签名
 */
function brave_get_moment_photo_cache_signature($moment_id, $post = null) {
    $post = $post ?: get_post($moment_id);
    if (!$post) {
        return '';
    }

    $thumbnail_id = get_post_thumbnail_id($moment_id);

    return md5(implode('|', array(
        $post->post_modified_gmt,
        $thumbnail_id,
        md5($post->post_content),
        brave_get_gallery_attachment_cache_version(),
    )));
}

/**
 * 构建相册照片索引。
 *
 * 将所有点滴里的照片展开成扁平数组，供相册页分页和年份筛选复用。
 *
 * @return array 照片索引
 */
function brave_build_gallery_photo_index() {
    $moment_ids = brave_get_moment_ids_by_effective_date();
    $all_photos = array();

    foreach ($moment_ids as $moment_id) {
        $moment_photos = brave_extract_photos_from_moment($moment_id);
        if (empty($moment_photos)) {
            continue;
        }

        $meet_date = brave_get_moment_effective_date($moment_id);
        $effective_year = absint(substr($meet_date, 0, 4));
        $location = get_post_meta($moment_id, '_meet_location', true);
        $mood = get_post_meta($moment_id, '_mood', true);
        $summary = get_post_meta($moment_id, '_moment_summary', true);

        $date_obj = strtotime($meet_date);
        $date_formatted = date_i18n(__('Y年n月j日', 'brave-love'), $date_obj);
        $weekday = date_i18n(__('l', 'brave-love'), $date_obj);

        foreach ($moment_photos as $photo) {
            $all_photos[] = array_merge($photo, array(
                'moment_id'      => $moment_id,
                'moment_title'   => get_the_title($moment_id),
                'moment_url'     => get_permalink($moment_id),
                'date'           => $meet_date,
                'year'           => $effective_year,
                'date_formatted' => $date_formatted,
                'weekday'        => $weekday,
                'location'       => $location,
                'mood'           => $mood,
                'mood_emoji'     => brave_get_mood_emoji($mood),
                'mood_text'      => brave_get_mood_text($mood),
                'summary'        => $summary ? $summary : wp_trim_words(get_post_field('post_content', $moment_id), 100),
            ));
        }
    }

    return $all_photos;
}

/**
 * 获取相册照片索引缓存。
 *
 * @return array 照片索引
 */
function brave_get_gallery_photo_index() {
    $cache_key = 'brave_gallery_photo_index_v1';
    $cached_photos = get_transient($cache_key);

    if (is_array($cached_photos)) {
        return $cached_photos;
    }

    $all_photos = brave_build_gallery_photo_index();
    set_transient($cache_key, $all_photos, WEEK_IN_SECONDS);

    return $all_photos;
}

/**
 * 清理相册照片索引缓存。
 */
function brave_flush_gallery_photo_index() {
    delete_transient('brave_gallery_photo_index_v1');
}

/**
 * 点滴保存时清理相册缓存。
 *
 * @param int $post_id 文章 ID
 */
function brave_flush_gallery_cache_on_moment_save($post_id) {
    if (wp_is_post_revision($post_id) || (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE)) {
        return;
    }

    delete_post_meta($post_id, '_brave_photo_cache_signature');
    delete_post_meta($post_id, '_brave_photo_cache_photos');
    brave_flush_gallery_photo_index();
}
add_action('save_post_moment', 'brave_flush_gallery_cache_on_moment_save');

/**
 * 点滴状态变更时清理相册缓存。
 *
 * @param string  $new_status 新状态
 * @param string  $old_status 旧状态
 * @param WP_Post $post       文章对象
 */
function brave_flush_gallery_cache_on_moment_status_change($new_status, $old_status, $post) {
    if (!$post || 'moment' !== $post->post_type || $new_status === $old_status) {
        return;
    }

    brave_flush_gallery_photo_index();
}
add_action('transition_post_status', 'brave_flush_gallery_cache_on_moment_status_change', 10, 3);

/**
 * 删除点滴时清理相册缓存。
 *
 * @param int $post_id 文章 ID
 */
function brave_flush_gallery_cache_on_moment_delete($post_id) {
    $post = get_post($post_id);
    if (!$post || 'moment' !== $post->post_type) {
        return;
    }

    brave_flush_gallery_photo_index();
}
add_action('before_delete_post', 'brave_flush_gallery_cache_on_moment_delete');

/**
 * 附件变更时清理相册缓存。
 *
 * @param int $attachment_id 附件 ID
 */
function brave_flush_gallery_cache_on_attachment_change($attachment_id) {
    brave_bump_gallery_attachment_cache_version();
    brave_flush_gallery_photo_index();
}
add_action('add_attachment', 'brave_flush_gallery_cache_on_attachment_change');
add_action('edit_attachment', 'brave_flush_gallery_cache_on_attachment_change');
add_action('delete_attachment', 'brave_flush_gallery_cache_on_attachment_change');

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

    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? wp_unslash($_SERVER['HTTP_USER_AGENT']) : '';
    $mobile_agents = array('Mobile', 'Android', 'Silk/', 'Kindle', 'BlackBerry', 'Opera Mini', 'Opera Mobi');

    foreach ($mobile_agents as $agent) {
        if (strpos($user_agent, $agent) !== false) {
            return true;
        }
    }
    
    return false;
}

/**
 * 根据页面模板查找页面 ID。
 *
 * @param string $template 页面模板路径
 * @return int
 */
function brave_get_page_id_by_template($template) {
    static $cache = array();

    $template = (string) $template;

    if (isset($cache[$template])) {
        return $cache[$template];
    }

    $posts = get_posts(
        array(
            'post_type' => 'page',
            'post_status' => 'publish',
            'posts_per_page' => 1,
            'fields' => 'ids',
            'orderby' => 'menu_order title',
            'order' => 'ASC',
            'meta_key' => '_wp_page_template',
            'meta_value' => $template,
            'no_found_rows' => true,
        )
    );

    $cache[$template] = !empty($posts) ? (int) $posts[0] : 0;

    return $cache[$template];
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

    $template_map = array(
        'moments' => 'page-templates/page-moments.php',
        'memories' => 'page-templates/page-memories.php',
        'notes' => 'page-templates/page-notes.php',
        'blessing' => 'page-templates/page-blessing.php',
        'about' => 'page-templates/page-about.php',
    );

    if (isset($template_map[$type])) {
        $page_id = brave_get_page_id_by_template($template_map[$type]);

        if ($page_id) {
            return get_permalink($page_id);
        }
    }

    $fallback_path_map = array(
        'about' => 'about-us',
    );

    $path = isset($fallback_path_map[$type]) ? $fallback_path_map[$type] : $type;

    return home_url("/{$path}/");
}

/**
 * 获取页脚导航默认配置。
 *
 * @return array
 */
function brave_get_footer_nav_defaults() {
    return array(
        'home' => array(
            'label' => __('首页', 'brave-love'),
            'url' => home_url('/'),
        ),
        'about' => array(
            'label' => __('关于我们', 'brave-love'),
            'url' => brave_get_page_link('about'),
        ),
        'moments' => array(
            'label' => __('点点滴滴', 'brave-love'),
            'url' => brave_get_page_link('moments'),
        ),
        'lists' => array(
            'label' => __('恋爱清单', 'brave-love'),
            'url' => brave_get_page_link('lists'),
        ),
        'memories' => array(
            'label' => __('甜蜜相册', 'brave-love'),
            'url' => brave_get_page_link('memories'),
        ),
        'notes' => array(
            'label' => __('随笔说说', 'brave-love'),
            'url' => brave_get_page_link('notes'),
        ),
        'blessing' => array(
            'label' => __('祝福留言', 'brave-love'),
            'url' => brave_get_page_link('blessing'),
        ),
    );
}

/**
 * 获取页脚导航项。
 *
 * @return array
 */
function brave_get_footer_nav_items() {
    $defaults = brave_get_footer_nav_defaults();
    $items = array();

    foreach ($defaults as $key => $default) {
        $custom_label = trim((string) get_theme_mod("brave_footer_nav_{$key}_label", ''));
        $custom_url = trim((string) get_theme_mod("brave_footer_nav_{$key}_url", ''));
        $label = '' !== $custom_label ? $custom_label : $default['label'];
        $url = '' !== $custom_url ? $custom_url : $default['url'];

        if ('' === $label || '' === $url) {
            continue;
        }

        $items[] = array(
            'key' => $key,
            'label' => $label,
            'url' => $url,
        );
    }

    return $items;
}

/**
 * 获取恋爱起始日期时间
 *
 * 兼容旧版本仅保存日期的配置项。
 *
 * @return string
 */
function brave_get_love_start_datetime() {
    $start_datetime = get_theme_mod('brave_love_start_datetime', '');
    if (!empty($start_datetime)) {
        return $start_datetime;
    }

    return get_theme_mod('brave_love_start_date', '2020-01-01 00:00');
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
        'filter_year' => 0,
    );
    $args = wp_parse_args($args, $defaults);
    $selected_year = !empty($args['filter_year']) ? intval($args['filter_year']) : intval($args['year']);

    $all_photos = brave_get_gallery_photo_index();

    if ($selected_year > 0) {
        $all_photos = array_values(array_filter($all_photos, function($photo) use ($selected_year) {
            return isset($photo['year']) && intval($photo['year']) === $selected_year;
        }));
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
    $post = get_post($moment_id);
    if (!$post) {
        return array();
    }

    $cache_signature = brave_get_moment_photo_cache_signature($moment_id, $post);
    $cached_signature = get_post_meta($moment_id, '_brave_photo_cache_signature', true);
    $cached_photos = get_post_meta($moment_id, '_brave_photo_cache_photos', true);

    if ($cache_signature && $cached_signature === $cache_signature && is_array($cached_photos)) {
        return $cached_photos;
    }

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
    $content = $post->post_content;
    $image_ids = array();
    
    // 方法1：古腾堡图片块和画廊块
    if (function_exists('parse_blocks')) {
        $blocks = parse_blocks($content);
        foreach ($blocks as $block) {
            $block_name = $block['blockName'] ?? '';
            $block_attrs = isset($block['attrs']) && is_array($block['attrs']) ? $block['attrs'] : array();

            // 单张图片块
            if ('core/image' === $block_name && !empty($block_attrs['id'])) {
                $image_ids[] = $block_attrs['id'];
            }
            // 画廊块
            if ('core/gallery' === $block_name && !empty($block_attrs['ids']) && is_array($block_attrs['ids'])) {
                $image_ids = array_merge($image_ids, $block_attrs['ids']);
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

    update_post_meta($moment_id, '_brave_photo_cache_signature', $cache_signature);
    update_post_meta($moment_id, '_brave_photo_cache_photos', $photos);
    
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
    $years = array();

    foreach (brave_get_gallery_photo_index() as $photo) {
        $year = isset($photo['year']) ? absint($photo['year']) : 0;
        if ($year > 0) {
            $years[$year] = $year;
        }
    }

    $years = array_values($years);
    rsort($years, SORT_NUMERIC);

    return $years;
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
