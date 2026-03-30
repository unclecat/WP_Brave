<?php
/**
 * 自定义字段（Meta Boxes）
 *
 * @package Brave_Love
 */

/**
 * 添加 Meta Boxes
 */
function brave_add_meta_boxes() {
    // 点点滴滴字段
    add_meta_box(
        'moment_details',
        __('见面详情', 'brave-love'),
        'brave_moment_meta_box',
        'moment',
        'normal',
        'high'
    );

    // 恋爱清单字段
    add_meta_box(
        'list_details',
        __('事项详情', 'brave-love'),
        'brave_list_meta_box',
        'love_list',
        'normal',
        'high'
    );

    // 随笔说说字段
    add_meta_box(
        'note_details',
        __('说说详情', 'brave-love'),
        'brave_note_meta_box',
        'note',
        'normal',
        'high'
    );

    // 甜蜜相册字段
    add_meta_box(
        'memory_details',
        __('相册信息', 'brave-love'),
        'brave_memory_meta_box',
        'memory',
        'normal',
        'high'
    );

    // 相册上传说明
    add_meta_box(
        'memory_upload_guide',
        __('📷 如何上传照片', 'brave-love'),
        'brave_memory_upload_guide_meta_box',
        'memory',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'brave_add_meta_boxes');

/**
 * 点点滴滴 Meta Box
 */
function brave_moment_meta_box($post) {
    wp_nonce_field('brave_moment_meta', 'brave_moment_nonce');
    
    $meet_date = get_post_meta($post->ID, '_meet_date', true);
    $meet_location = get_post_meta($post->ID, '_meet_location', true);
    $mood = get_post_meta($post->ID, '_mood', true);
    $related_memory = get_post_meta($post->ID, '_related_memory', true);
    $moment_summary = get_post_meta($post->ID, '_moment_summary', true);
    ?>
    <p>
        <label for="meet_date"><strong><?php _e('见面日期', 'brave-love'); ?></strong></label><br>
        <input type="date" id="meet_date" name="meet_date" value="<?php echo esc_attr($meet_date); ?>" class="widefat">
    </p>
    <p>
        <label for="meet_location"><strong><?php _e('见面地点', 'brave-love'); ?></strong></label><br>
        <input type="text" id="meet_location" name="meet_location" value="<?php echo esc_attr($meet_location); ?>" class="widefat" placeholder="<?php _e('例如：电影院、餐厅、公园...', 'brave-love'); ?>">
    </p>
    <p>
        <label for="moment_summary"><strong><?php _e('摘要', 'brave-love'); ?></strong></label><br>
        <textarea id="moment_summary" name="moment_summary" class="widefat" rows="4" placeholder="<?php _e('填写在列表页显示的摘要内容，支持 HTML', 'brave-love'); ?>"><?php echo esc_textarea($moment_summary); ?></textarea>
        <span class="description"><?php _e('如不填写，将自动截取文章内容', 'brave-love'); ?></span>
    </p>
    <p>
        <label for="mood"><strong><?php _e('心情', 'brave-love'); ?></strong></label><br>
        <select id="mood" name="mood" class="widefat">
            <option value=""><?php _e('选择心情', 'brave-love'); ?></option>
            <option value="happy" <?php selected($mood, 'happy'); ?>><?php _e('😊 开心', 'brave-love'); ?></option>
            <option value="excited" <?php selected($mood, 'excited'); ?>><?php _e('🤩 兴奋', 'brave-love'); ?></option>
            <option value="romantic" <?php selected($mood, 'romantic'); ?>><?php _e('🥰 浪漫', 'brave-love'); ?></option>
            <option value="peaceful" <?php selected($mood, 'peaceful'); ?>><?php _e('😌 平静', 'brave-love'); ?></option>
            <option value="touched" <?php selected($mood, 'touched'); ?>><?php _e('🥺 感动', 'brave-love'); ?></option>
            <option value="miss" <?php selected($mood, 'miss'); ?>><?php _e('😢 想念', 'brave-love'); ?></option>
        </select>
    </p>
    <p>
        <label for="related_memory"><strong><?php _e('关联相册', 'brave-love'); ?></strong></label><br>
        <?php
        $memories = get_posts(array(
            'post_type' => 'memory',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));
        ?>
        <select id="related_memory" name="related_memory" class="widefat">
            <option value=""><?php _e('选择关联相册（可选）', 'brave-love'); ?></option>
            <?php foreach ($memories as $memory) : ?>
                <option value="<?php echo $memory->ID; ?>" <?php selected($related_memory, $memory->ID); ?>>
                    <?php echo esc_html($memory->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <?php
}

/**
 * 恋爱清单 Meta Box
 */
function brave_list_meta_box($post) {
    wp_nonce_field('brave_list_meta', 'brave_list_nonce');
    
    $is_done = get_post_meta($post->ID, '_is_done', true);
    $done_date = get_post_meta($post->ID, '_done_date', true);
    ?>
    <p>
        <label for="is_done">
            <input type="checkbox" id="is_done" name="is_done" value="1" <?php checked($is_done, 1); ?>>
            <strong><?php _e('已完成', 'brave-love'); ?></strong>
        </label>
    </p>
    <p>
        <label for="done_date"><strong><?php _e('完成日期', 'brave-love'); ?></strong></label><br>
        <input type="date" id="done_date" name="done_date" value="<?php echo esc_attr($done_date); ?>" class="widefat">
    </p>
    <?php
}

/**
 * 随笔说说 Meta Box
 */
function brave_note_meta_box($post) {
    wp_nonce_field('brave_note_meta', 'brave_note_nonce');
    
    $note_mood = get_post_meta($post->ID, '_note_mood', true);
    ?>
    <p>
        <label for="note_mood"><strong><?php _e('心情表情', 'brave-love'); ?></strong></label><br>
        <input type="text" id="note_mood" name="note_mood" value="<?php echo esc_attr($note_mood); ?>" class="widefat" placeholder="<?php _e('例如：😊 🤩 🥰', 'brave-love'); ?>">
    </p>
    <p class="description"><?php _e('在标题下方显示表情符号', 'brave-love'); ?></p>
    <?php
}

/**
 * 甜蜜相册 Meta Box
 */
function brave_memory_meta_box($post) {
    wp_nonce_field('brave_memory_meta', 'brave_memory_nonce');
    
    $memory_date = get_post_meta($post->ID, '_memory_date', true);
    $memory_location = get_post_meta($post->ID, '_memory_location', true);
    $related_moment = get_post_meta($post->ID, '_related_moment', true);
    ?>
    <p>
        <label for="memory_date"><strong><?php _e('拍摄/发生日期', 'brave-love'); ?></strong></label><br>
        <input type="date" id="memory_date" name="memory_date" value="<?php echo esc_attr($memory_date); ?>" class="widefat">
    </p>
    <p>
        <label for="memory_location"><strong><?php _e('地点', 'brave-love'); ?></strong></label><br>
        <input type="text" id="memory_location" name="memory_location" value="<?php echo esc_attr($memory_location); ?>" class="widefat" placeholder="<?php _e('例如：杭州西湖、日本东京...', 'brave-love'); ?>">
    </p>
    <p>
        <label for="related_moment"><strong><?php _e('关联点滴', 'brave-love'); ?></strong></label><br>
        <?php
        $moments = get_posts(array(
            'post_type' => 'moment',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC',
        ));
        ?>
        <select id="related_moment" name="related_moment" class="widefat">
            <option value=""><?php _e('选择关联点滴（可选）', 'brave-love'); ?></option>
            <?php foreach ($moments as $moment) : ?>
                <option value="<?php echo $moment->ID; ?>" <?php selected($related_moment, $moment->ID); ?>>
                    <?php echo esc_html($moment->post_title); ?>
                </option>
            <?php endforeach; ?>
        </select>
    </p>
    <?php
}

/**
 * 相册上传说明 Meta Box
 */
function brave_memory_upload_guide_meta_box($post) {
    ?>
    <div style="padding: 10px;">
        <h4 style="margin-top: 0;">🎯 简单三步创建相册</h4>
        
        <p><strong>1. 设置封面</strong><br>
        点击右侧「特色图片」→ 上传照片作为相册封面</p>
        
        <p><strong>2. 上传照片</strong><br>
        在下方编辑器中：<br>
        • 点击「+」添加「图片」块<br>
        • 或添加「画廊」块批量上传<br>
        • 直接拖拽照片到编辑器</p>
        
        <p><strong>3. 写点描述</strong><br>
        在编辑器中写下这段回忆的故事</p>
        
        <hr style="margin: 15px 0;">
        
        <p style="color: #666; font-size: 12px;">
        💡 提示：所有插入到文章中的图片都会自动显示在相册页面
        </p>
        
        <?php
        // 显示已上传的图片数量
        $content = $post->post_content;
        preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
        $image_count = count($matches[1]);
        
        if ($image_count > 0) :
        ?>
        <div style="background: #e8f5e9; padding: 10px; border-radius: 4px; margin-top: 10px;">
            ✅ 已检测到 <?php echo $image_count; ?> 张照片
        </div>
        <?php else : ?>
        <div style="background: #fff3e0; padding: 10px; border-radius: 4px; margin-top: 10px;">
            ⚠️ 尚未添加照片
        </div>
        <?php endif; ?>
    </div>
    <?php
}

/**
 * 保存 Meta Box 数据
 */
function brave_save_meta_boxes($post_id) {
    // 点点滴滴
    if (isset($_POST['brave_moment_nonce']) && wp_verify_nonce($_POST['brave_moment_nonce'], 'brave_moment_meta')) {
        if (isset($_POST['meet_date'])) {
            update_post_meta($post_id, '_meet_date', sanitize_text_field($_POST['meet_date']));
        }
        if (isset($_POST['meet_location'])) {
            update_post_meta($post_id, '_meet_location', sanitize_text_field($_POST['meet_location']));
        }
        if (isset($_POST['mood'])) {
            update_post_meta($post_id, '_mood', sanitize_text_field($_POST['mood']));
        }
        if (isset($_POST['related_memory'])) {
            update_post_meta($post_id, '_related_memory', intval($_POST['related_memory']));
        }
        if (isset($_POST['moment_summary'])) {
            update_post_meta($post_id, '_moment_summary', wp_kses_post($_POST['moment_summary']));
        }
    }

    // 恋爱清单
    if (isset($_POST['brave_list_nonce']) && wp_verify_nonce($_POST['brave_list_nonce'], 'brave_list_meta')) {
        $is_done = isset($_POST['is_done']) ? 1 : 0;
        update_post_meta($post_id, '_is_done', $is_done);
        
        if (isset($_POST['done_date'])) {
            update_post_meta($post_id, '_done_date', sanitize_text_field($_POST['done_date']));
        }
    }

    // 随笔说说
    if (isset($_POST['brave_note_nonce']) && wp_verify_nonce($_POST['brave_note_nonce'], 'brave_note_meta')) {
        if (isset($_POST['note_mood'])) {
            update_post_meta($post_id, '_note_mood', sanitize_text_field($_POST['note_mood']));
        }
    }

    // 相册
    if (isset($_POST['brave_memory_nonce']) && wp_verify_nonce($_POST['brave_memory_nonce'], 'brave_memory_meta')) {
        if (isset($_POST['memory_date'])) {
            update_post_meta($post_id, '_memory_date', sanitize_text_field($_POST['memory_date']));
        }
        if (isset($_POST['memory_location'])) {
            update_post_meta($post_id, '_memory_location', sanitize_text_field($_POST['memory_location']));
        }
        if (isset($_POST['related_moment'])) {
            update_post_meta($post_id, '_related_moment', intval($_POST['related_moment']));
        }
    }
}
add_action('save_post', 'brave_save_meta_boxes');

/**
 * 从文章内容中提取图片
 */
function brave_extract_images_from_content($post_id) {
    $post = get_post($post_id);
    if (!$post) return array();
    
    $content = $post->post_content;
    $images = array();
    
    // 方法1：提取 Gutenberg 图片块的 data-id
    if (function_exists('parse_blocks')) {
        $blocks = parse_blocks($content);
        foreach ($blocks as $block) {
            // 图片块
            if ($block['blockName'] === 'core/image' && !empty($block['attrs']['id'])) {
                $images[] = $block['attrs']['id'];
            }
            // 画廊块
            if ($block['blockName'] === 'core/gallery' && !empty($block['attrs']['ids'])) {
                $images = array_merge($images, $block['attrs']['ids']);
            }
        }
    }
    
    // 方法2：提取经典编辑器的图片
    preg_match_all('/wp-image-(\d+)/', $content, $matches);
    if (!empty($matches[1])) {
        $images = array_merge($images, array_map('intval', $matches[1]));
    }
    
    // 去重
    $images = array_unique($images);
    
    return $images;
}

/**
 * 获取相册图片（特色图片 + 内容中的图片）
 */
function brave_get_memory_photos($post_id, $size = 'large') {
    $photos = array();
    
    // 1. 特色图片作为第一张
    if (has_post_thumbnail($post_id)) {
        $thumb_id = get_post_thumbnail_id($post_id);
        $url = wp_get_attachment_image_url($thumb_id, $size);
        if ($url) {
            // 获取图片尺寸
            $meta = wp_get_attachment_metadata($thumb_id);
            $photos[] = array(
                'id' => $thumb_id,
                'url' => $url,
                'thumb' => wp_get_attachment_image_url($thumb_id, 'thumbnail'),
                'title' => get_the_title($thumb_id),
                'is_cover' => true,
                'width' => !empty($meta['width']) ? $meta['width'] : 0,
                'height' => !empty($meta['height']) ? $meta['height'] : 0,
            );
        }
    }
    
    // 2. 从内容中提取的图片
    $content_images = brave_extract_images_from_content($post_id);
    foreach ($content_images as $image_id) {
        // 跳过已经添加的特色图片
        if (isset($thumb_id) && $image_id == $thumb_id) continue;
        
        $url = wp_get_attachment_image_url($image_id, $size);
        if ($url) {
            // 获取图片尺寸
            $meta = wp_get_attachment_metadata($image_id);
            $photos[] = array(
                'id' => $image_id,
                'url' => $url,
                'thumb' => wp_get_attachment_image_url($image_id, 'thumbnail'),
                'title' => get_the_title($image_id),
                'is_cover' => false,
                'width' => !empty($meta['width']) ? $meta['width'] : 0,
                'height' => !empty($meta['height']) ? $meta['height'] : 0,
            );
        }
    }
    
    return $photos;
}

/**
 * 获取相册照片数量
 */
function brave_get_memory_photo_count($post_id) {
    return count(brave_get_memory_photos($post_id, 'thumbnail'));
}
