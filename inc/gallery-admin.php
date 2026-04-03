<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 相册数据管理页面
 *
 * @package Brave_Love
 */

/**
 * 添加相册管理菜单
 */
function brave_gallery_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=moment',
        __('相册数据管理', 'brave-love'),
        __('相册数据管理', 'brave-love'),
        'manage_options',
        'brave-gallery-admin',
        'brave_gallery_admin_page'
    );
}
add_action('admin_menu', 'brave_gallery_admin_menu');

/**
 * 判断是否为旧版 memory 文章。
 *
 * @param int $post_id 文章 ID
 * @return bool
 */
function brave_is_legacy_memory_post($post_id) {
    $post = get_post($post_id);

    return $post instanceof WP_Post && 'memory' === $post->post_type;
}

/**
 * 管理页面内容
 */
function brave_gallery_admin_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('权限不足', 'brave-love'));
    }

    // 处理删除请求
    if (isset($_POST['action']) && check_admin_referer('brave_gallery_admin_nonce')) {
        $action = sanitize_key(wp_unslash($_POST['action']));
        $deleted = 0;

        if ($action === 'delete_all') {
            // 删除所有旧相册
            $memories = get_posts(array(
                'post_type' => 'memory',
                'posts_per_page' => -1,
                'post_status' => 'any',
                'fields' => 'ids',
            ));
            foreach ($memories as $memory_id) {
                wp_delete_post($memory_id, true);
                $deleted++;
            }
        } elseif ($action === 'delete_selected' && !empty($_POST['memory_ids']) && is_array($_POST['memory_ids'])) {
            // 删除选中的相册
            foreach (wp_unslash($_POST['memory_ids']) as $memory_id) {
                $memory_id = absint($memory_id);

                if ($memory_id > 0 && brave_is_legacy_memory_post($memory_id)) {
                    wp_delete_post($memory_id, true);
                    $deleted++;
                }
            }
        } elseif ($action === 'convert_all') {
            // 转换所有旧相册为点滴
            $memories = get_posts(array(
                'post_type' => 'memory',
                'posts_per_page' => -1,
                'post_status' => 'any',
            ));
            foreach ($memories as $memory) {
                brave_convert_memory_to_moment($memory);
                $deleted++;
            }
        }
        
        if ($deleted > 0) {
            echo '<div class="notice notice-success"><p>' . sprintf(__('成功处理 %d 条数据', 'brave-love'), $deleted) . '</p></div>';
        }
    }
    
    // 获取旧相册数据
    $memories = get_posts(array(
        'post_type' => 'memory',
        'posts_per_page' => -1,
        'post_status' => 'any',
    ));
    
    $total_count = count($memories);
    ?>
    <div class="wrap">
        <h1><?php _e('相册数据管理', 'brave-love'); ?></h1>
        
        <div class="notice notice-warning">
            <p><strong><?php _e('提示：', 'brave-love'); ?></strong><?php _e('以下是从旧版相册（memory）迁移过来的数据。您可以选择删除或转换为「点点滴滴」文章。', 'brave-love'); ?></p>
        </div>
        
        <div class="brave-admin-stats" style="background: #fff; padding: 20px; margin: 20px 0; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2><?php _e('数据统计', 'brave-love'); ?></h2>
            <p style="font-size: 18px;">
                <?php _e('旧相册文章数量：', 'brave-love'); ?>
                <strong style="color: #ff5162; font-size: 24px;"><?php echo esc_html((string) $total_count); ?></strong>
            </p>
            
            <?php if ($total_count > 0) : ?>
            <form method="post" style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee;">
                <?php wp_nonce_field('brave_gallery_admin_nonce'); ?>
                
                <h3><?php _e('批量操作', 'brave-love'); ?></h3>
                
                <div style="margin: 15px 0;">
                    <label style="display: block; margin: 10px 0; padding: 10px; background: #f0f0f0; border-radius: 4px; cursor: pointer;">
                        <input type="radio" name="action" value="delete_all" onclick="return confirm('<?php _e('确定要删除所有旧相册数据吗？此操作不可恢复！', 'brave-love'); ?>')">
                        <strong style="color: #d63638;"><?php _e('删除所有旧相册', 'brave-love'); ?></strong>
                        <span style="color: #666; display: block; margin-top: 5px;"><?php _e('彻底删除所有 memory 类型的文章及其数据', 'brave-love'); ?></span>
                    </label>
                    
                    <label style="display: block; margin: 10px 0; padding: 10px; background: #f0f0f0; border-radius: 4px; cursor: pointer;">
                        <input type="radio" name="action" value="convert_all" checked>
                        <strong style="color: #2271b1;"><?php _e('转换为「点点滴滴」', 'brave-love'); ?></strong>
                        <span style="color: #666; display: block; margin-top: 5px;"><?php _e('将旧相册转为点滴文章，照片会自动在新相册中显示', 'brave-love'); ?></span>
                    </label>
                </div>
                
                <?php submit_button(__('执行批量操作', 'brave-love'), 'primary', 'submit', false, array('style' => 'background: #ff5162; border-color: #ff5162;')); ?>
            </form>
            <?php else : ?>
            <div class="notice notice-success" style="margin-top: 20px;">
                <p>🎉 <?php _e('没有旧相册数据需要处理', 'brave-love'); ?></p>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($total_count > 0) : ?>
        <div class="brave-admin-list" style="background: #fff; padding: 20px; margin: 20px 0; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2><?php _e('旧相册列表', 'brave-love'); ?></h2>
            
            <form method="post">
                <?php wp_nonce_field('brave_gallery_admin_nonce'); ?>
                <input type="hidden" name="action" value="delete_selected">
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th class="column-cb" style="width: 30px;">
                                <input type="checkbox" id="select-all">
                            </th>
                            <th><?php _e('标题', 'brave-love'); ?></th>
                            <th style="width: 120px;"><?php _e('日期', 'brave-love'); ?></th>
                            <th style="width: 80px;"><?php _e('照片数', 'brave-love'); ?></th>
                            <th style="width: 100px;"><?php _e('操作', 'brave-love'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($memories as $memory) : 
                            $photo_count = brave_get_memory_photo_count_for_admin($memory->ID);
                            $memory_date = get_post_meta($memory->ID, '_memory_date', true);
                        ?>
                        <tr>
                            <td>
                                <input type="checkbox" name="memory_ids[]" value="<?php echo $memory->ID; ?>">
                            </td>
                            <td>
                                <strong><?php echo esc_html($memory->post_title); ?></strong>
                                <div class="row-actions">
                                    <a href="<?php echo esc_url(get_edit_post_link($memory->ID)); ?>"><?php _e('编辑', 'brave-love'); ?></a> |
                                    <a href="<?php echo esc_url(get_permalink($memory->ID)); ?>" target="_blank" rel="noopener noreferrer"><?php _e('查看', 'brave-love'); ?></a>
                                </div>
                            </td>
                            <td><?php echo $memory_date ? esc_html($memory_date) : '—'; ?></td>
                            <td><?php echo esc_html((string) $photo_count); ?></td>
                            <td>
                                <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin.php?page=brave-gallery-admin&action=delete_single&memory_id=' . $memory->ID), 'delete_memory_' . $memory->ID)); ?>" 
                                   class="button button-small" 
                                   onclick="return confirm('<?php _e('确定删除？', 'brave-love'); ?>')"
                                   style="color: #d63638;">
                                    <?php _e('删除', 'brave-love'); ?>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <p style="margin-top: 15px;">
                    <?php submit_button(__('删除选中项', 'brave-love'), 'secondary', 'submit', false); ?>
                </p>
            </form>
        </div>
        <?php endif; ?>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#select-all').on('click', function() {
            $('input[name="memory_ids[]"]').prop('checked', this.checked);
        });
    });
    </script>
    <?php
}

/**
 * 处理单个删除请求
 */
function brave_handle_single_delete() {
    $page = isset($_GET['page']) ? sanitize_key(wp_unslash($_GET['page'])) : '';

    if ($page !== 'brave-gallery-admin') {
        return;
    }

    $action = isset($_GET['action']) ? sanitize_key(wp_unslash($_GET['action'])) : '';

    if ($action === 'delete_single' && isset($_GET['memory_id'])) {
        $memory_id = absint(wp_unslash($_GET['memory_id']));
        $nonce = isset($_GET['_wpnonce']) ? sanitize_text_field(wp_unslash($_GET['_wpnonce'])) : '';

        if (!wp_verify_nonce($nonce, 'delete_memory_' . $memory_id)) {
            wp_die(__('安全验证失败', 'brave-love'));
        }
        
        if (!current_user_can('manage_options')) {
            wp_die(__('权限不足', 'brave-love'));
        }

        if (!brave_is_legacy_memory_post($memory_id)) {
            wp_die(__('仅支持删除旧版相册数据', 'brave-love'));
        }
        
        wp_delete_post($memory_id, true);
        
        wp_safe_redirect(admin_url('edit.php?post_type=moment&page=brave-gallery-admin&deleted=1'));
        exit;
    }
}
add_action('admin_init', 'brave_handle_single_delete');

/**
 * 转换旧相册为点滴
 */
function brave_convert_memory_to_moment($memory) {
    // 更新文章类型
    wp_update_post(array(
        'ID' => $memory->ID,
        'post_type' => 'moment',
    ));
    
    // 复制元数据
    $memory_date = get_post_meta($memory->ID, '_memory_date', true);
    $memory_location = get_post_meta($memory->ID, '_memory_location', true);
    
    if ($memory_date) {
        update_post_meta($memory->ID, '_meet_date', $memory_date);
        delete_post_meta($memory->ID, '_memory_date');
    }
    if ($memory_location) {
        update_post_meta($memory->ID, '_meet_location', $memory_location);
        delete_post_meta($memory->ID, '_memory_location');
    }
}

/**
 * 获取旧相册照片数量（用于管理页面）
 */
function brave_get_memory_photo_count_for_admin($post_id) {
    // 兼容旧函数
    $photos = get_post_meta($post_id, '_memory_photos', true);
    if (is_array($photos)) {
        return count($photos);
    }
    
    // 从内容提取
    $content = get_post_field('post_content', $post_id);
    preg_match_all('/<img[^>]+src=["\']([^"\']+)["\'][^>]*>/i', $content, $matches);
    $count = count($matches[1]);
    
    if (has_post_thumbnail($post_id)) {
        $count++;
    }
    
    return $count;
}
