<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 自定义文章类型注册
 *
 * @package Brave_Love
 */

/**
 * 注册点点滴滴 CPT
 */
function brave_register_moment_post_type() {
    $labels = array(
        'name'                  => __('点点滴滴', 'brave-love'),
        'singular_name'         => __('点滴', 'brave-love'),
        'menu_name'             => __('点点滴滴', 'brave-love'),
        'add_new'               => __('添加点滴', 'brave-love'),
        'add_new_item'          => __('添加新点滴', 'brave-love'),
        'edit_item'             => __('编辑点滴', 'brave-love'),
        'new_item'              => __('新点滴', 'brave-love'),
        'view_item'             => __('查看点滴', 'brave-love'),
        'search_items'          => __('搜索点滴', 'brave-love'),
        'not_found'             => __('暂无点滴', 'brave-love'),
        'not_found_in_trash'    => __('回收站中没有点滴', 'brave-love'),
    );

    $args = array(
        'labels'                => $labels,
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 5,
        'menu_icon'             => 'dashicons-heart',
        'query_var'             => true,
        'rewrite'               => array('slug' => 'moment'),
        'capability_type'       => 'post',
        'has_archive'           => true,
        'hierarchical'          => false,
        'supports'              => array('title', 'editor', 'thumbnail', 'author', 'excerpt'),
        'show_in_rest'          => true,
    );

    register_post_type('moment', $args);
}
add_action('init', 'brave_register_moment_post_type');

/**
 * 注册恋爱清单 CPT
 */
function brave_register_list_post_type() {
    $labels = array(
        'name'                  => __('恋爱清单', 'brave-love'),
        'singular_name'         => __('清单项', 'brave-love'),
        'menu_name'             => __('恋爱清单', 'brave-love'),
        'add_new'               => __('添加事项', 'brave-love'),
        'add_new_item'          => __('添加新事项', 'brave-love'),
        'edit_item'             => __('编辑事项', 'brave-love'),
        'new_item'              => __('新事项', 'brave-love'),
        'view_item'             => __('查看事项', 'brave-love'),
        'search_items'          => __('搜索事项', 'brave-love'),
        'not_found'             => __('暂无事项', 'brave-love'),
        'not_found_in_trash'    => __('回收站中没有事项', 'brave-love'),
    );

    $args = array(
        'labels'                => $labels,
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 6,
        'menu_icon'             => 'dashicons-star-filled',
        'query_var'             => true,
        'rewrite'               => array('slug' => 'lists'),
        'capability_type'       => 'post',
        'has_archive'           => true,
        'hierarchical'          => false,
        'supports'              => array('title', 'editor', 'thumbnail'),
        'show_in_rest'          => true,
    );

    register_post_type('love_list', $args);

    // 注册分类法
    $tax_labels = array(
        'name'                  => __('清单分类', 'brave-love'),
        'singular_name'         => __('分类', 'brave-love'),
    );

    $tax_args = array(
        'labels'                => $tax_labels,
        'hierarchical'          => true,
        'public'                => true,
        'show_ui'               => true,
        'show_admin_column'     => true,
        'rewrite'               => array('slug' => 'lists-category'),
    );

    register_taxonomy('list_category', 'love_list', $tax_args);
}
add_action('init', 'brave_register_list_post_type');

/**
 * 注册随笔说说 CPT
 */
function brave_register_note_post_type() {
    $labels = array(
        'name'                  => __('随笔说说', 'brave-love'),
        'singular_name'         => __('说说', 'brave-love'),
        'menu_name'             => __('随笔说说', 'brave-love'),
        'add_new'               => __('添加说说', 'brave-love'),
        'add_new_item'          => __('添加新说说', 'brave-love'),
        'edit_item'             => __('编辑说说', 'brave-love'),
        'new_item'              => __('新说说', 'brave-love'),
        'view_item'             => __('查看说说', 'brave-love'),
        'search_items'          => __('搜索说说', 'brave-love'),
        'not_found'             => __('暂无说说', 'brave-love'),
        'not_found_in_trash'    => __('回收站中没有说说', 'brave-love'),
    );

    $args = array(
        'labels'                => $labels,
        'public'                => true,
        'publicly_queryable'    => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 7,
        'menu_icon'             => 'dashicons-format-chat',
        'query_var'             => true,
        'rewrite'               => array('slug' => 'note'),
        'capability_type'       => 'post',
        'has_archive'           => true,
        'hierarchical'          => false,
        'supports'              => array('editor', 'author', 'comments'),
        'show_in_rest'          => true,
    );

    register_post_type('note', $args);
}
add_action('init', 'brave_register_note_post_type');

/**
 * 注册关于我们故事节点 CPT
 */
function brave_register_story_post_type() {
    $labels = array(
        'name'                  => __('关于我们', 'brave-love'),
        'singular_name'         => __('故事节点', 'brave-love'),
        'menu_name'             => __('关于我们', 'brave-love'),
        'add_new'               => __('添加节点', 'brave-love'),
        'add_new_item'          => __('添加故事节点', 'brave-love'),
        'edit_item'             => __('编辑故事节点', 'brave-love'),
        'new_item'              => __('新故事节点', 'brave-love'),
        'view_item'             => __('查看故事节点', 'brave-love'),
        'search_items'          => __('搜索故事节点', 'brave-love'),
        'not_found'             => __('暂无故事节点', 'brave-love'),
        'not_found_in_trash'    => __('回收站中没有故事节点', 'brave-love'),
    );

    $args = array(
        'labels'                => $labels,
        'public'                => true,
        'publicly_queryable'    => false,
        'exclude_from_search'   => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_position'         => 8,
        'menu_icon'             => 'dashicons-book-alt',
        'query_var'             => false,
        'rewrite'               => false,
        'capability_type'       => 'post',
        'has_archive'           => false,
        'hierarchical'          => false,
        'supports'              => array('title', 'editor', 'thumbnail', 'excerpt'),
        'show_in_rest'          => true,
    );

    register_post_type('story_milestone', $args);
}
add_action('init', 'brave_register_story_post_type');

/**
 * 修改 CPT 列表显示列
 */
function brave_moment_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['meet_date'] = __('见面日期', 'brave-love');
            $new_columns['location'] = __('地点', 'brave-love');
        }
    }
    return $new_columns;
}
add_filter('manage_moment_posts_columns', 'brave_moment_columns');

function brave_moment_custom_column($column, $post_id) {
    switch ($column) {
        case 'meet_date':
            $date = get_post_meta($post_id, '_meet_date', true);
            echo $date ? esc_html($date) : '—';
            break;
        case 'location':
            $location = get_post_meta($post_id, '_meet_location', true);
            echo $location ? esc_html($location) : '—';
            break;
    }
}
add_action('manage_moment_posts_custom_column', 'brave_moment_custom_column', 10, 2);

function brave_love_list_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['status'] = __('状态', 'brave-love');
            $new_columns['done_date'] = __('完成日期', 'brave-love');
        }
    }
    return $new_columns;
}
add_filter('manage_love_list_posts_columns', 'brave_love_list_columns');

function brave_love_list_custom_column($column, $post_id) {
    switch ($column) {
        case 'status':
            $is_done = get_post_meta($post_id, '_is_done', true);
            echo $is_done ? 
                '<span style="color: #4caf50; font-weight: bold;">✓ 已完成</span>' : 
                '<span style="color: #999;">○ 待完成</span>';
            break;
        case 'done_date':
            $date = get_post_meta($post_id, '_done_date', true);
            echo $date ? esc_html($date) : '—';
            break;
    }
}
add_action('manage_love_list_posts_custom_column', 'brave_love_list_custom_column', 10, 2);

function brave_story_milestone_columns($columns) {
    $new_columns = array();
    foreach ($columns as $key => $value) {
        $new_columns[$key] = $value;
        if ($key === 'title') {
            $new_columns['story_date'] = __('节点日期', 'brave-love');
            $new_columns['story_phase'] = __('阶段', 'brave-love');
            $new_columns['related_moment'] = __('关联点滴', 'brave-love');
        }
    }
    return $new_columns;
}
add_filter('manage_story_milestone_posts_columns', 'brave_story_milestone_columns');

function brave_story_milestone_custom_column($column, $post_id) {
    switch ($column) {
        case 'story_date':
            $date = get_post_meta($post_id, '_story_date', true);
            echo $date ? esc_html($date) : '—';
            break;
        case 'story_phase':
            $phase = get_post_meta($post_id, '_story_phase', true);
            echo $phase ? esc_html($phase) : '—';
            break;
        case 'related_moment':
            $related_moment_id = (int) get_post_meta($post_id, '_related_moment_id', true);
            if ($related_moment_id > 0) {
                echo esc_html(get_the_title($related_moment_id));
            } else {
                echo '—';
            }
            break;
    }
}
add_action('manage_story_milestone_posts_custom_column', 'brave_story_milestone_custom_column', 10, 2);

/**
 * 使相册支持按日期排序
 */
function brave_add_date_sorting($vars) {
    if (is_admin() && isset($vars['orderby'])) {
        if ('meet_date' === $vars['orderby']) {
            $vars['meta_key'] = '_meet_date';
            $vars['orderby'] = 'meta_value';
        }
    }
    return $vars;
}
add_filter('request', 'brave_add_date_sorting');
