<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template Name: 旅行计划
 *
 * @package Brave_Love
 */

get_header();

if (have_posts()) {
    the_post();
}

$page_id = get_the_ID();
$travel_posts = brave_get_sorted_travel_plan_posts();
$travel_count = count($travel_posts);
$next_travel = null;
$first_pending_travel = null;
$latest_completed = null;
$latest_completed_timestamp = null;
$today = wp_date('Y-m-d');
$manual_footprints = brave_split_multiline_items(get_post_meta($page_id, '_travel_manual_footprints', true));
$footprint_nodes = array();
$footprint_status_rank = array(
    'planning' => 1,
    'booked' => 2,
    'ongoing' => 3,
    'completed' => 4,
);
$resolve_footprint_tone = static function ($status) {
    if ('completed' === $status) {
        return 'visited';
    }

    if (in_array($status, array('booked', 'ongoing'), true)) {
        return 'upcoming';
    }

    return 'planning';
};

$build_footprint_key = static function ($label) {
    $key = sanitize_title($label);

    if ('' === $key) {
        $key = md5($label);
    }

    return $key;
};

foreach ($travel_posts as $travel_post) {
    $travel_meta = brave_get_travel_plan_meta($travel_post->ID);
    $travel_primary_date = brave_get_travel_plan_primary_date($travel_meta);
    $destination_label = '' !== $travel_meta['destination'] ? $travel_meta['destination'] : get_the_title($travel_post);
    $destination_key = $build_footprint_key($destination_label);

    if ('completed' === $travel_meta['status']) {
        $completed_timestamp = '' !== $travel_primary_date ? strtotime($travel_primary_date . ' 00:00:00') : (int) get_post_time('U', true, $travel_post);

        if (null === $latest_completed || null === $latest_completed_timestamp || $completed_timestamp >= $latest_completed_timestamp) {
            $latest_completed = $travel_post;
            $latest_completed_timestamp = $completed_timestamp;
        }
    } elseif (null === $first_pending_travel) {
        $first_pending_travel = $travel_post;
    }

    if (null === $next_travel && 'completed' !== $travel_meta['status'] && '' !== $travel_primary_date && $travel_primary_date >= $today) {
        $next_travel = $travel_post;
    }

    if (!isset($footprint_nodes[$destination_key])) {
        $footprint_nodes[$destination_key] = array(
            'label' => $destination_label,
            'date_label' => brave_format_travel_date($travel_primary_date),
            'status' => $travel_meta['status'],
            'status_label' => brave_get_travel_status_label($travel_meta['status']),
            'tone' => $resolve_footprint_tone($travel_meta['status']),
        );

        continue;
    }

    $existing_rank = $footprint_status_rank[$footprint_nodes[$destination_key]['status']] ?? 0;
    $current_rank = $footprint_status_rank[$travel_meta['status']] ?? 0;

    if ($current_rank >= $existing_rank) {
        $footprint_nodes[$destination_key]['status'] = $travel_meta['status'];
        $footprint_nodes[$destination_key]['status_label'] = brave_get_travel_status_label($travel_meta['status']);
        $footprint_nodes[$destination_key]['tone'] = $resolve_footprint_tone($travel_meta['status']);
    }
}

foreach ($manual_footprints as $manual_footprint) {
    $manual_footprint = trim((string) $manual_footprint);

    if ('' === $manual_footprint) {
        continue;
    }

    $destination_key = $build_footprint_key($manual_footprint);

    if (!isset($footprint_nodes[$destination_key])) {
        $footprint_nodes[$destination_key] = array(
            'label' => $manual_footprint,
            'date_label' => __('手动点亮', 'brave-love'),
            'status' => 'completed',
            'status_label' => __('已点亮', 'brave-love'),
            'tone' => 'visited',
        );

        continue;
    }

    $footprint_nodes[$destination_key]['label'] = $manual_footprint;
    $footprint_nodes[$destination_key]['date_label'] = __('手动点亮', 'brave-love');
    $footprint_nodes[$destination_key]['status'] = 'completed';
    $footprint_nodes[$destination_key]['status_label'] = __('已点亮', 'brave-love');
    $footprint_nodes[$destination_key]['tone'] = 'visited';
}

if (null === $next_travel && null !== $first_pending_travel) {
    $next_travel = $first_pending_travel;
}

if (null === $next_travel && $travel_count > 0) {
    $next_travel = $travel_posts[0];
}

$next_travel_meta = $next_travel ? brave_get_travel_plan_meta($next_travel->ID) : array(
    'start_date' => '',
    'end_date' => '',
    'destination' => '',
    'status' => 'planning',
);
$next_travel_primary_date = brave_get_travel_plan_primary_date($next_travel_meta);
$next_travel_days = $next_travel ? brave_get_travel_plan_days($next_travel->ID) : array();
$next_travel_first_day = brave_get_travel_plan_first_day_overview($next_travel_days);
$next_travel_first_city = $next_travel_first_day['city'];
$next_travel_first_weather = $next_travel_first_day['weather'];
$latest_completed_meta = $latest_completed ? brave_get_travel_plan_meta($latest_completed->ID) : array(
    'start_date' => '',
    'end_date' => '',
    'destination' => '',
);
$latest_completed_primary_date = brave_get_travel_plan_primary_date($latest_completed_meta);
$latest_completed_label = $latest_completed ? ('' !== $latest_completed_meta['destination'] ? $latest_completed_meta['destination'] : get_the_title($latest_completed)) : '';
$visited_destination_count = 0;

foreach ($footprint_nodes as $footprint_node) {
    if ('visited' === $footprint_node['tone']) {
        $visited_destination_count++;
    }
}

$planned_destination_count = count($footprint_nodes) - $visited_destination_count;

get_template_part(
    'template-parts/page-hero',
    null,
    array(
        'post_id' => $page_id,
        'title' => '🧳 旅行计划',
        'subtitle' => '山河再远，岁月再长，往后余生依然同行。',
    )
);
?>

<section class="content-section travel-plans-section">
    <div class="page-shell page-shell-narrow travel-page-shell">
        <div class="travel-intro-grid">
            <article class="travel-panel travel-footprint-card">
                <header class="travel-summary-head travel-intro-head travel-footprint-head">
                    <div class="travel-summary-heading">
                        <span class="travel-section-label"><?php esc_html_e('我们的足迹', 'brave-love'); ?></span>
                    </div>

                    <?php if (!empty($footprint_nodes)) : ?>
                        <div class="travel-footprint-summary">
                            <span class="travel-footprint-pill" data-tone="visited"><?php echo esc_html(sprintf(__('已点亮 %d 站', 'brave-love'), $visited_destination_count)); ?></span>
                            <span class="travel-footprint-pill" data-tone="planning"><?php echo esc_html(sprintf(__('计划中 %d 站', 'brave-love'), $planned_destination_count)); ?></span>
                        </div>
                    <?php endif; ?>
                </header>

                <?php if (!empty($footprint_nodes)) : ?>
                    <div class="travel-footprint-map" data-count="<?php echo esc_attr(count($footprint_nodes)); ?>">
                        <div class="travel-footprint-grid">
                            <?php foreach (array_values($footprint_nodes) as $index => $footprint_node) : ?>
                                <article class="travel-footprint-node" data-tone="<?php echo esc_attr($footprint_node['tone']); ?>" style="--travel-node-order: <?php echo esc_attr($index + 1); ?>;">
                                    <span class="travel-footprint-pin" aria-hidden="true"></span>
                                    <strong class="travel-footprint-name"><?php echo esc_html($footprint_node['label']); ?></strong>
                                    <span class="travel-footprint-meta"><?php echo esc_html($footprint_node['date_label'] . ' · ' . $footprint_node['status_label']); ?></span>
                                </article>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else : ?>
                    <div class="travel-footprint-empty">
                        <div class="travel-empty-icon">🗺️</div>
                        <p class="travel-empty-text"><?php esc_html_e('第一站还没有点亮，等你写下第一篇旅行计划，这张地图就会慢慢亮起来。', 'brave-love'); ?></p>
                    </div>
                <?php endif; ?>
            </article>

            <?php if ($travel_count > 0) : ?>
                <aside class="travel-panel travel-overview-card travel-overview-card-detail travel-list-overview-card">
                    <div class="travel-overview-head">
                        <span class="travel-section-label"><?php esc_html_e('旅程总览', 'brave-love'); ?></span>
                    </div>

                    <div class="travel-overview-list">
                        <div class="travel-overview-item travel-overview-item-feature">
                            <span class="travel-overview-label"><?php esc_html_e('下一趟', 'brave-love'); ?></span>
                            <div class="travel-overview-stack">
                                <strong class="travel-overview-value"><?php echo $next_travel ? esc_html(get_the_title($next_travel)) : esc_html__('待补充', 'brave-love'); ?></strong>
                                <span class="travel-overview-note"><?php echo esc_html(brave_format_travel_date($next_travel_primary_date)); ?></span>
                                <span class="travel-overview-value-secondary"><?php echo esc_html(implode(' · ', array_filter(array($next_travel_meta['destination'], !empty($next_travel_days) ? sprintf(__('已安排 %d 天', 'brave-love'), count($next_travel_days)) : '')))); ?></span>
                            </div>
                        </div>

                        <div class="travel-overview-item">
                            <span class="travel-overview-label"><?php esc_html_e('已点亮', 'brave-love'); ?></span>
                            <div class="travel-overview-stack">
                                <strong class="travel-overview-value"><?php echo esc_html($visited_destination_count); ?></strong>
                            </div>
                        </div>

                        <div class="travel-overview-item">
                            <span class="travel-overview-label"><?php esc_html_e('计划数量', 'brave-love'); ?></span>
                            <div class="travel-overview-stack">
                                <strong class="travel-overview-value"><?php echo esc_html($travel_count); ?></strong>
                            </div>
                        </div>

                        <?php if ('' !== $latest_completed_label) : ?>
                            <div class="travel-overview-item">
                                <span class="travel-overview-label"><?php esc_html_e('最近去过', 'brave-love'); ?></span>
                                <div class="travel-overview-stack">
                                    <strong class="travel-overview-value"><?php echo esc_html($latest_completed_label); ?></strong>
                                    <span class="travel-overview-value-secondary"><?php echo esc_html(brave_format_travel_date($latest_completed_primary_date)); ?></span>
                                </div>
                            </div>
                        <?php elseif ('' !== $next_travel_first_city || '' !== $next_travel_first_weather) : ?>
                            <div class="travel-overview-item">
                                <span class="travel-overview-label"><?php esc_html_e('第一站', 'brave-love'); ?></span>
                                <div class="travel-overview-stack">
                                    <strong class="travel-overview-value"><?php echo esc_html($next_travel_first_city ?: __('待补充', 'brave-love')); ?></strong>
                                    <?php if ('' !== $next_travel_first_weather) : ?>
                                        <span class="travel-overview-value-secondary"><?php echo esc_html($next_travel_first_weather); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </aside>
            <?php endif; ?>
        </div>

        <?php if ($travel_count > 0) : ?>
            <div class="travel-plan-timeline">
                <?php foreach ($travel_posts as $travel_post) : ?>
                    <?php
                    $travel_id = $travel_post->ID;
                    $travel_meta = brave_get_travel_plan_meta($travel_id);
                    $travel_days = brave_get_travel_plan_days($travel_id);
                    $travel_link = get_permalink($travel_id);
                    $travel_title = get_the_title($travel_id);
                    $travel_status_label = brave_get_travel_status_label($travel_meta['status']);
                    $travel_status_tone = brave_get_travel_status_tone($travel_meta['status']);
                    $travel_date_range = brave_format_travel_date_range($travel_meta['start_date'], $travel_meta['end_date']);
                    $travel_duration = brave_get_travel_duration_label($travel_meta['start_date'], $travel_meta['end_date']);
                    $travel_day_count = count($travel_days);
                    $first_day = brave_get_travel_plan_first_day_overview($travel_days);
                    ?>
                    <article class="travel-plan-node">
                        <div class="travel-plan-marker" aria-hidden="true">
                            <span class="travel-plan-dot"></span>
                        </div>

                        <div class="travel-plan-card">
                            <div class="travel-plan-layout">
                                <div class="travel-plan-main">
                                    <header class="travel-plan-card-header">
                                        <div class="travel-plan-card-meta">
                                            <span class="travel-plan-date"><?php echo esc_html($travel_date_range); ?></span>
                                            <span class="travel-plan-status" data-tone="<?php echo esc_attr($travel_status_tone); ?>"><?php echo esc_html($travel_status_label); ?></span>
                                        </div>

                                        <h2 class="travel-plan-title">
                                            <a href="<?php echo esc_url($travel_link); ?>"><?php echo esc_html($travel_title); ?></a>
                                        </h2>

                                        <div class="travel-plan-facts">
                                            <?php if ('' !== $travel_meta['destination']) : ?>
                                                <span class="travel-plan-fact">📍 <?php echo esc_html($travel_meta['destination']); ?></span>
                                            <?php endif; ?>

                                            <span class="travel-plan-fact">🗓️ <?php echo esc_html($travel_duration); ?></span>

                                            <?php if ('' !== $first_day['city']) : ?>
                                                <span class="travel-plan-fact">🚏 <?php echo esc_html($first_day['city']); ?></span>
                                            <?php elseif ('' !== $first_day['title']) : ?>
                                                <span class="travel-plan-fact">🚏 <?php echo esc_html($first_day['title']); ?></span>
                                            <?php endif; ?>
                                        </div>

                                    </header>

                                    <?php if (has_post_thumbnail($travel_id)) : ?>
                                        <figure class="travel-plan-media">
                                            <a href="<?php echo esc_url($travel_link); ?>">
                                                <?php echo get_the_post_thumbnail($travel_id, 'large', array('class' => 'travel-plan-image')); ?>
                                            </a>
                                        </figure>
                                    <?php endif; ?>
                                </div>

                                <aside class="travel-plan-aside">
                                    <div class="travel-plan-aside-head">
                                        <span class="travel-plan-aside-label"><?php esc_html_e('行前速览', 'brave-love'); ?></span>
                                    </div>

                                    <div class="travel-plan-aside-list">
                                        <div class="travel-plan-aside-item">
                                            <span class="travel-overview-label"><?php esc_html_e('目的地', 'brave-love'); ?></span>
                                            <div class="travel-overview-stack">
                                                <strong class="travel-overview-value"><?php echo '' !== $travel_meta['destination'] ? esc_html($travel_meta['destination']) : esc_html__('待补充', 'brave-love'); ?></strong>
                                                <span class="travel-overview-note"><?php echo esc_html($travel_date_range); ?></span>
                                                <span class="travel-overview-value-secondary"><?php echo esc_html($travel_status_label . ' · ' . $travel_duration); ?></span>
                                            </div>
                                        </div>

                                        <?php if ('' !== $first_day['city'] || '' !== $first_day['title'] || '' !== $first_day['weather'] || '' !== $first_day['date']) : ?>
                                            <div class="travel-plan-aside-item">
                                                <span class="travel-overview-label"><?php esc_html_e('第一站', 'brave-love'); ?></span>
                                                <div class="travel-overview-stack">
                                                    <strong class="travel-overview-value"><?php echo esc_html($first_day['city'] ?: ($first_day['title'] ?: __('待补充', 'brave-love'))); ?></strong>
                                                    <?php if ('' !== $first_day['date']) : ?>
                                                        <span class="travel-overview-note"><?php echo esc_html($first_day['date']); ?></span>
                                                    <?php endif; ?>
                                                    <?php if ('' !== $first_day['weather']) : ?>
                                                        <span class="travel-overview-value-secondary"><?php echo esc_html($first_day['weather']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ('' !== $first_day['hotel'] || '' !== $first_day['transport']) : ?>
                                            <div class="travel-plan-aside-item">
                                                <span class="travel-overview-label"><?php esc_html_e('落脚安排', 'brave-love'); ?></span>
                                                <div class="travel-overview-stack">
                                                    <strong class="travel-overview-value"><?php echo esc_html($first_day['hotel'] ?: __('待补充', 'brave-love')); ?></strong>
                                                    <?php if ('' !== $first_day['transport']) : ?>
                                                        <span class="travel-overview-value-secondary"><?php echo esc_html($first_day['transport']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <?php if ('' !== $first_day['highlight_value']) : ?>
                                            <div class="travel-plan-aside-item">
                                                <span class="travel-overview-label"><?php echo esc_html($first_day['highlight_label']); ?></span>
                                                <div class="travel-overview-stack">
                                                    <strong class="travel-overview-value"><?php echo esc_html($first_day['highlight_value']); ?></strong>
                                                    <?php if ('' !== $first_day['highlight_note']) : ?>
                                                        <span class="travel-overview-value-secondary"><?php echo esc_html($first_day['highlight_note']); ?></span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="travel-plan-aside-footer">
                                        <?php if ($travel_day_count > 0) : ?>
                                            <span class="travel-plan-day-count">
                                                <?php
                                                printf(
                                                    /* translators: %d: day count */
                                                    esc_html__('已安排 %d 天', 'brave-love'),
                                                    $travel_day_count
                                                );
                                                ?>
                                            </span>
                                        <?php endif; ?>

                                        <a class="travel-plan-link" href="<?php echo esc_url($travel_link); ?>"><?php esc_html_e('查看计划', 'brave-love'); ?></a>
                                    </div>
                                </aside>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="travel-panel travel-empty-state">
                <div class="travel-empty-icon">🌤️</div>
                <h2 class="travel-empty-title"><?php esc_html_e('第一趟旅行还在等你落笔', 'brave-love'); ?></h2>
                <p class="travel-empty-text"><?php esc_html_e('先在后台添加一篇“旅行计划”，这里就会按出发日期排成一条清晰的旅程时间线。', 'brave-love'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
get_footer();
?>
