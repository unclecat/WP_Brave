<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 纪念日后台管理。
 *
 * @package Brave_Love
 */

// ==================== 纪念日管理 ====================

/**
 * 添加纪念日管理页面
 */
function brave_add_anniversary_menu() {
    add_options_page(
        __('纪念日管理', 'brave-love'),
        __('纪念日', 'brave-love'),
        'manage_options',
        'brave-anniversary',
        'brave_anniversary_page'
    );
}
add_action('admin_menu', 'brave_add_anniversary_menu');

/**
 * 纪念日管理页面
 */
function brave_anniversary_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('权限不足', 'brave-love'));
    }

    // 保存数据
    if (isset($_POST['brave_save_anniversaries']) && check_admin_referer('brave_anniversary_nonce')) {
        $anniversaries = array();
        if (isset($_POST['anniversary_name']) && is_array($_POST['anniversary_name'])) {
            $anniversary_names = wp_unslash($_POST['anniversary_name']);
            $anniversary_dates = isset($_POST['anniversary_date']) && is_array($_POST['anniversary_date'])
                ? wp_unslash($_POST['anniversary_date'])
                : array();

            foreach ($anniversary_names as $key => $name) {
                $date = brave_sanitize_iso_date($anniversary_dates[$key] ?? '');
                $name = sanitize_text_field($name);
                if (!empty($name) && !empty($date)) {
                    $anniversaries[] = array(
                        'name' => $name,
                        'date' => $date,
                    );
                }
            }
        }
        update_option('brave_anniversaries', $anniversaries);
        echo '<div class="notice notice-success"><p>' . __('已保存', 'brave-love') . '</p></div>';
    }

    $anniversaries = get_option('brave_anniversaries', array());
    ?>
    <div class="wrap">
        <h1><?php _e('纪念日管理', 'brave-love'); ?></h1>
        <p class="description"><?php _e('添加你们的特别日子，如生日、恋爱周年、重要纪念日等。', 'brave-love'); ?></p>
        
        <form method="post" action="" id="anniversary-form">
            <?php wp_nonce_field('brave_anniversary_nonce'); ?>
            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th style="width: 40%;"><?php _e('纪念日名称', 'brave-love'); ?></th>
                        <th style="width: 40%;"><?php _e('日期', 'brave-love'); ?></th>
                        <th style="width: 20%;"><?php _e('操作', 'brave-love'); ?></th>
                    </tr>
                </thead>
                <tbody id="anniversary-list">
                    <?php foreach ($anniversaries as $index => $item) : ?>
                        <tr class="anniversary-item">
                            <td>
                                <input type="text" name="anniversary_name[]" value="<?php echo esc_attr($item['name']); ?>" class="regular-text" placeholder="<?php _e('例如：恋爱一周年', 'brave-love'); ?>">
                            </td>
                            <td>
                                <input type="date" name="anniversary_date[]" value="<?php echo esc_attr($item['date']); ?>" class="regular-text">
                            </td>
                            <td>
                                <button type="button" class="button remove-anniversary"><?php _e('删除', 'brave-love'); ?></button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($anniversaries)) : ?>
                        <tr class="anniversary-item empty-row" style="display: none;"></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            
            <p class="submit">
                <button type="button" class="button" id="add-anniversary"><?php _e('添加纪念日', 'brave-love'); ?></button>
                <input type="submit" name="brave_save_anniversaries" class="button button-primary" value="<?php _e('保存', 'brave-love'); ?>">
            </p>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#add-anniversary').on('click', function() {
            var row = '<tr class="anniversary-item">' +
                '<td><input type="text" name="anniversary_name[]" class="regular-text" placeholder="<?php _e('例如：恋爱一周年', 'brave-love'); ?>"></td>' +
                '<td><input type="date" name="anniversary_date[]" class="regular-text"></td>' +
                '<td><button type="button" class="button remove-anniversary"><?php _e('删除', 'brave-love'); ?></button></td>' +
                '</tr>';
            $('#anniversary-list').append(row);
        });
        
        $(document).on('click', '.remove-anniversary', function() {
            $(this).closest('.anniversary-item').remove();
        });
    });
    </script>
    <?php
}
