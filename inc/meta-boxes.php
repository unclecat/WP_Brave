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
        __('相册详情', 'brave-love'),
        'brave_memory_meta_box',
        'memory',
        'normal',
        'high'
    );

    // 相册照片管理
    add_meta_box(
        'memory_photos',
        __('照片管理', 'brave-love'),
        'brave_memory_photos_meta_box',
        'memory',
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
    $related_memory = get_post_meta($post->ID, '_related_memory', true);
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
    $list_category = wp_get_post_terms($post->ID, 'list_category', array('fields' => 'ids'));
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
    $note_images = get_post_meta($post->ID, '_note_images', true);
    if (!is_array($note_images)) {
        $note_images = array();
    }
    ?>
    <p>
        <label for="note_mood"><strong><?php _e('心情表情', 'brave-love'); ?></strong></label><br>
        <input type="text" id="note_mood" name="note_mood" value="<?php echo esc_attr($note_mood); ?>" class="widefat" placeholder="<?php _e('例如：😊 🤩 🥰', 'brave-love'); ?>">
    </p>
    <p>
        <label><strong><?php _e('配图（可选，最多9张）', 'brave-love'); ?></strong></label><br>
    </p>
    <div class="brave-gallery-container">
        <div class="brave-gallery-preview" id="note-images-preview">
            <?php foreach ($note_images as $image_id) : 
                $image_url = wp_get_attachment_image_url($image_id, 'thumbnail');
                if ($image_url) : ?>
                <div class="brave-gallery-item" data-id="<?php echo $image_id; ?>">
                    <img src="<?php echo esc_url($image_url); ?>" alt="">
                    <span class="brave-remove-image">×</span>
                </div>
            <?php endif; endforeach; ?>
        </div>
        <button type="button" class="button brave-add-images" data-target="note_images"><?php _e('添加图片', 'brave-love'); ?></button>
        <input type="hidden" name="note_images" id="note_images_input" value="<?php echo esc_attr(implode(',', $note_images)); ?>">
    </div>
    <style>
        .brave-gallery-preview {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 10px;
        }
        .brave-gallery-item {
            position: relative;
            aspect-ratio: 1;
        }
        .brave-gallery-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 4px;
        }
        .brave-remove-image {
            position: absolute;
            top: -8px;
            right: -8px;
            width: 20px;
            height: 20px;
            background: #ff5162;
            color: white;
            border-radius: 50%;
            text-align: center;
            line-height: 20px;
            cursor: pointer;
            font-size: 14px;
        }
    </style>
    <?php
}

/**
 * 相册详情 Meta Box
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
 * 相册照片管理 Meta Box
 */
function brave_memory_photos_meta_box($post) {
    wp_nonce_field('brave_memory_photos_meta', 'brave_memory_photos_nonce');
    
    $photos = get_post_meta($post->ID, '_memory_photos', true);
    if (!is_array($photos)) {
        $photos = array();
    }
    ?>
    <div class="brave-gallery-container">
        <p class="description"><?php _e('拖拽可排序，点击 × 删除照片', 'brave-love'); ?></p>
        <div class="brave-gallery-preview sortable" id="memory-photos-preview">
            <?php foreach ($photos as $photo_id) : 
                $photo_url = wp_get_attachment_image_url($photo_id, 'thumbnail');
                if ($photo_url) : ?>
                <div class="brave-gallery-item" data-id="<?php echo $photo_id; ?>">
                    <img src="<?php echo esc_url($photo_url); ?>" alt="">
                    <span class="brave-remove-image">×</span>
                </div>
            <?php endif; endforeach; ?>
        </div>
        <button type="button" class="button brave-add-images" data-target="memory_photos"><?php _e('添加照片', 'brave-love'); ?></button>
        <input type="hidden" name="memory_photos" id="memory_photos_input" value="<?php echo esc_attr(implode(',', $photos)); ?>">
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
        if (isset($_POST['note_images'])) {
            $images = array_filter(array_map('intval', explode(',', $_POST['note_images'])));
            update_post_meta($post_id, '_note_images', $images);
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

    if (isset($_POST['brave_memory_photos_nonce']) && wp_verify_nonce($_POST['brave_memory_photos_nonce'], 'brave_memory_photos_meta')) {
        if (isset($_POST['memory_photos'])) {
            $photos = array_filter(array_map('intval', explode(',', $_POST['memory_photos'])));
            update_post_meta($post_id, '_memory_photos', $photos);
        }
    }
}
add_action('save_post', 'brave_save_meta_boxes');

/**
 * 加载媒体上传脚本和样式
 */
function brave_admin_scripts($hook) {
    global $post;
    
    $theme_uri = get_template_directory_uri();
    $version = defined('BRAVE_VERSION') ? BRAVE_VERSION : '0.1';
    
    // 在所有后台页面加载样式
    wp_enqueue_style('brave-admin', $theme_uri . '/assets/css/admin.css', array(), $version);
    
    // 在编辑页面加载脚本
    if ($hook === 'post.php' || $hook === 'post-new.php') {
        if (isset($post) && in_array($post->post_type, array('note', 'memory'))) {
            wp_enqueue_media();
            wp_enqueue_script('jquery-ui-sortable');
            wp_enqueue_script('brave-admin', $theme_uri . '/assets/js/admin.js', array('jquery', 'jquery-ui-sortable'), $version, true);
        }
    }
}
add_action('admin_enqueue_scripts', 'brave_admin_scripts');
