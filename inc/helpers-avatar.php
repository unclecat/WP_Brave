<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 头像相关辅助函数。
 *
 * @package Brave_Love
 */

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
