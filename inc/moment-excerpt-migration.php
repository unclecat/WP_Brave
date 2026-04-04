<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 点滴摘要迁移工具。
 *
 * @package Brave_Love
 */

/**
 * 注册点滴摘要迁移页面。
 */
function brave_add_moment_excerpt_migration_menu() {
    add_submenu_page(
        'edit.php?post_type=moment',
        __('点滴摘要迁移', 'brave-love'),
        __('摘要迁移', 'brave-love'),
        'manage_options',
        'brave-moment-excerpt-migration',
        'brave_render_moment_excerpt_migration_page'
    );
}
add_action('admin_menu', 'brave_add_moment_excerpt_migration_menu');

/**
 * 获取需要迁移统计的点滴文章 ID。
 *
 * @return int[]
 */
function brave_get_moment_excerpt_migration_ids() {
    return get_posts(array(
        'post_type' => 'moment',
        'post_status' => array('publish', 'future', 'draft', 'pending', 'private'),
        'posts_per_page' => -1,
        'fields' => 'ids',
        'orderby' => 'date',
        'order' => 'DESC',
        'no_found_rows' => true,
        'update_post_meta_cache' => false,
        'update_post_term_cache' => false,
    ));
}

/**
 * 统一摘要内容，供迁移比对使用。
 *
 * @param string $summary 摘要内容
 * @return string
 */
function brave_normalize_moment_summary_for_compare($summary) {
    $summary = trim((string) $summary);
    $summary = preg_replace("/\r\n?|\n/", "\n", $summary);

    return trim((string) $summary);
}

/**
 * 获取点滴摘要迁移统计信息。
 *
 * @param int $conflict_limit 最多返回多少条冲突记录
 * @return array
 */
function brave_get_moment_excerpt_migration_stats($conflict_limit = 8) {
    $stats = array(
        'total' => 0,
        'legacy_count' => 0,
        'excerpt_count' => 0,
        'pending_count' => 0,
        'cleanup_ready_count' => 0,
        'conflict_count' => 0,
        'conflicts' => array(),
    );

    foreach (brave_get_moment_excerpt_migration_ids() as $moment_id) {
        $stats['total']++;

        $legacy_summary = trim((string) get_post_meta($moment_id, '_moment_summary', true));
        $excerpt = trim((string) get_post_field('post_excerpt', $moment_id));

        if ('' !== $legacy_summary) {
            $stats['legacy_count']++;
        }

        if ('' !== $excerpt) {
            $stats['excerpt_count']++;
        }

        if ('' === $legacy_summary) {
            continue;
        }

        if ('' === $excerpt) {
            $stats['pending_count']++;
            continue;
        }

        if (brave_normalize_moment_summary_for_compare($legacy_summary) === brave_normalize_moment_summary_for_compare($excerpt)) {
            $stats['cleanup_ready_count']++;
            continue;
        }

        $stats['conflict_count']++;

        if (count($stats['conflicts']) < $conflict_limit) {
            $stats['conflicts'][] = array(
                'id' => $moment_id,
                'title' => get_the_title($moment_id),
                'edit_link' => get_edit_post_link($moment_id, ''),
            );
        }
    }

    return $stats;
}

/**
 * 将旧摘要迁移到原生摘要。
 *
 * 仅填充当前 excerpt 为空的文章，避免覆盖已手动维护的新摘要。
 *
 * @return array
 */
function brave_run_moment_excerpt_backfill() {
    $result = array(
        'migrated' => 0,
        'skipped_existing' => 0,
        'skipped_empty' => 0,
    );

    foreach (brave_get_moment_excerpt_migration_ids() as $moment_id) {
        $legacy_summary = trim((string) get_post_meta($moment_id, '_moment_summary', true));
        $excerpt = trim((string) get_post_field('post_excerpt', $moment_id));

        if ('' === $legacy_summary) {
            $result['skipped_empty']++;
            continue;
        }

        if ('' !== $excerpt) {
            $result['skipped_existing']++;
            continue;
        }

        wp_update_post(array(
            'ID' => $moment_id,
            'post_excerpt' => wp_slash($legacy_summary),
        ));
        $result['migrated']++;
    }

    return $result;
}

/**
 * 清理已安全迁移的旧摘要字段。
 *
 * 仅删除与原生摘要完全一致的旧字段，冲突内容保留待人工确认。
 *
 * @return array
 */
function brave_cleanup_legacy_moment_summaries() {
    $result = array(
        'deleted' => 0,
        'kept_pending' => 0,
        'kept_conflicts' => 0,
    );

    foreach (brave_get_moment_excerpt_migration_ids() as $moment_id) {
        $legacy_summary = trim((string) get_post_meta($moment_id, '_moment_summary', true));

        if ('' === $legacy_summary) {
            continue;
        }

        $excerpt = trim((string) get_post_field('post_excerpt', $moment_id));

        if ('' === $excerpt) {
            $result['kept_pending']++;
            continue;
        }

        if (brave_normalize_moment_summary_for_compare($legacy_summary) !== brave_normalize_moment_summary_for_compare($excerpt)) {
            $result['kept_conflicts']++;
            continue;
        }

        delete_post_meta($moment_id, '_moment_summary');
        $result['deleted']++;
    }

    return $result;
}

/**
 * 渲染点滴摘要迁移页面。
 */
function brave_render_moment_excerpt_migration_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('权限不足', 'brave-love'));
    }

    $notice = null;

    if (isset($_POST['brave_moment_excerpt_action']) && check_admin_referer('brave_moment_excerpt_migration_nonce')) {
        $action = sanitize_key(wp_unslash($_POST['brave_moment_excerpt_action']));

        if ('migrate' === $action) {
            $result = brave_run_moment_excerpt_backfill();
            $notice = array(
                'type' => 'success',
                'message' => sprintf(
                    __('已迁移 %1$d 篇点滴摘要到 WordPress 原生摘要；跳过 %2$d 篇已有原生摘要的文章，%3$d 篇没有旧摘要。', 'brave-love'),
                    $result['migrated'],
                    $result['skipped_existing'],
                    $result['skipped_empty']
                ),
            );
        } elseif ('cleanup' === $action) {
            $result = brave_cleanup_legacy_moment_summaries();
            $notice = array(
                'type' => 'success',
                'message' => sprintf(
                    __('已清理 %1$d 条旧摘要字段；保留 %2$d 条尚未迁移的数据，以及 %3$d 条与原生摘要不一致的待确认数据。', 'brave-love'),
                    $result['deleted'],
                    $result['kept_pending'],
                    $result['kept_conflicts']
                ),
            );
        }
    }

    $stats = brave_get_moment_excerpt_migration_stats();
    ?>
    <div class="wrap">
        <h1><?php _e('点滴摘要迁移', 'brave-love'); ?></h1>
        <p class="description">
            <?php _e('这一页用于把旧版点滴自定义摘要字段 `_moment_summary` 迁移到 WordPress 原生摘要 `post_excerpt`。当前主题前台已优先读取原生摘要，并在迁移期内保留旧字段兜底。', 'brave-love'); ?>
        </p>

        <?php if ($notice) : ?>
            <div class="notice notice-<?php echo esc_attr($notice['type']); ?> is-dismissible"><p><?php echo esc_html($notice['message']); ?></p></div>
        <?php endif; ?>

        <div style="background: #fff; padding: 20px; margin: 20px 0; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
            <h2><?php _e('迁移统计', 'brave-love'); ?></h2>
            <table class="widefat striped" style="max-width: 860px;">
                <tbody>
                    <tr>
                        <td style="width: 240px;"><strong><?php _e('点滴总数', 'brave-love'); ?></strong></td>
                        <td><?php echo esc_html(number_format_i18n($stats['total'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('旧自定义摘要数量', 'brave-love'); ?></strong></td>
                        <td><?php echo esc_html(number_format_i18n($stats['legacy_count'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('原生摘要数量', 'brave-love'); ?></strong></td>
                        <td><?php echo esc_html(number_format_i18n($stats['excerpt_count'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('待迁移数量', 'brave-love'); ?></strong></td>
                        <td><?php echo esc_html(number_format_i18n($stats['pending_count'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('可安全清理旧字段数量', 'brave-love'); ?></strong></td>
                        <td><?php echo esc_html(number_format_i18n($stats['cleanup_ready_count'])); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('摘要冲突数量', 'brave-love'); ?></strong></td>
                        <td><?php echo esc_html(number_format_i18n($stats['conflict_count'])); ?></td>
                    </tr>
                </tbody>
            </table>

            <form method="post" action="" style="margin-top: 20px; display: flex; gap: 12px; flex-wrap: wrap;">
                <?php wp_nonce_field('brave_moment_excerpt_migration_nonce'); ?>
                <button type="submit" name="brave_moment_excerpt_action" value="migrate" class="button button-primary" <?php disabled($stats['pending_count'] <= 0); ?>>
                    <?php _e('迁移到原生摘要', 'brave-love'); ?>
                </button>
                <button type="submit" name="brave_moment_excerpt_action" value="cleanup" class="button button-secondary" <?php disabled($stats['cleanup_ready_count'] <= 0); ?>>
                    <?php _e('清理已迁移旧字段', 'brave-love'); ?>
                </button>
            </form>

            <p class="description" style="margin-top: 12px;">
                <?php _e('迁移动作只会填充当前原生摘要为空的文章，不会覆盖已经手动维护过的原生摘要。清理动作只会删除与原生摘要完全一致的旧字段。', 'brave-love'); ?>
            </p>
        </div>

        <?php if (!empty($stats['conflicts'])) : ?>
            <div style="background: #fff; padding: 20px; margin: 20px 0; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <h2><?php _e('待人工确认的摘要冲突', 'brave-love'); ?></h2>
                <p class="description">
                    <?php _e('这些文章同时存在旧摘要和原生摘要，且内容不一致。迁移工具不会覆盖它们，请人工确认后再决定是否清理旧字段。', 'brave-love'); ?>
                </p>
                <ul style="margin: 0; padding-left: 18px;">
                    <?php foreach ($stats['conflicts'] as $item) : ?>
                        <li style="margin-bottom: 8px;">
                            <a href="<?php echo esc_url($item['edit_link']); ?>"><?php echo esc_html($item['title']); ?></a>
                            <span style="color: #666;">#<?php echo esc_html((string) $item['id']); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
    </div>
    <?php
}
