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
    <p class="description" style="color: #666; font-size: 12px; margin-top: 15px; padding: 10px; background: #f0f0f0; border-radius: 4px;">
        💡 <strong>提示：</strong>在编辑器中上传的照片会自动显示在<a href="<?php echo esc_url(home_url('/memories/')); ?>" target="_blank">甜蜜相册</a>页面
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
    $note_miss_level = get_post_meta($post->ID, '_note_miss_level', true);
    if (empty($note_miss_level)) {
        $note_miss_level = 3; // 默认3星
    }
    ?>
    <p>
        <label for="note_mood"><strong><?php _e('心情表情', 'brave-love'); ?></strong></label><br>
        <input type="text" id="note_mood" name="note_mood" value="<?php echo esc_attr($note_mood); ?>" class="widefat" placeholder="<?php _e('例如：😊 🤩 🥰', 'brave-love'); ?>">
    </p>
    <p class="description"><?php _e('在标题下方显示表情符号', 'brave-love'); ?></p>
    
    <p style="margin-top: 20px;">
        <label for="note_miss_level"><strong><?php _e('思念度', 'brave-love'); ?></strong></label><br>
        <select id="note_miss_level" name="note_miss_level" class="widefat">
            <option value="1" <?php selected($note_miss_level, 1); ?>>⭐ (1星 - 轻微思念)</option>
            <option value="2" <?php selected($note_miss_level, 2); ?>>⭐⭐ (2星 - 有点想你)</option>
            <option value="3" <?php selected($note_miss_level, 3); ?>>⭐⭐⭐ (3星 - 很想你)</option>
            <option value="4" <?php selected($note_miss_level, 4); ?>>⭐⭐⭐⭐ (4星 - 非常想你)</option>
            <option value="5" <?php selected($note_miss_level, 5); ?>>⭐⭐⭐⭐⭐ (5星 - 思念成疾)</option>
        </select>
    </p>
    <p class="description"><?php _e('选择对TA的思念程度', 'brave-love'); ?></p>
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
        if (isset($_POST['note_miss_level'])) {
            update_post_meta($post_id, '_note_miss_level', intval($_POST['note_miss_level']));
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
