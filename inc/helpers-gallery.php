<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 相册与图片索引辅助函数。
 *
 * @package Brave_Love
 */

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
        $summary = brave_get_moment_summary($moment_id);

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
