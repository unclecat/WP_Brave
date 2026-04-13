<?php
if (!defined('ABSPATH')) {
    exit;
}

get_header();

if (have_posts()) {
    the_post();
}

$travel_id = get_the_ID();
$travel_meta = brave_get_travel_plan_meta($travel_id);
$travel_days = brave_get_travel_plan_days($travel_id);
$travel_summary = brave_get_travel_plan_summary($travel_id);
$travel_date_range = brave_format_travel_date_range($travel_meta['start_date'], $travel_meta['end_date']);
$travel_duration = brave_get_travel_duration_label($travel_meta['start_date'], $travel_meta['end_date']);
$travel_status_label = brave_get_travel_status_label($travel_meta['status']);
$travel_status_tone = brave_get_travel_status_tone($travel_meta['status']);
$travel_keyword = trim((string) ($travel_meta['keyword'] ?? ''));
$travel_departure_note = trim((string) ($travel_meta['departure_note'] ?? ''));
$travel_tips_title = '' !== trim((string) ($travel_meta['tips_title'] ?? '')) ? $travel_meta['tips_title'] : __('出行 Tips', 'brave-love');
$travel_tips = brave_split_multiline_items($travel_meta['tips'] ?? '');
$travel_checklist_title = '' !== trim((string) ($travel_meta['checklist_title'] ?? '')) ? $travel_meta['checklist_title'] : __('出发 Checklist', 'brave-love');
$travel_checklist = brave_split_multiline_items($travel_meta['checklist'] ?? '');
$travel_content_raw = get_the_content();
$travel_content = '' !== trim((string) wp_strip_all_tags($travel_content_raw))
    ? apply_filters('the_content', $travel_content_raw)
    : '';
$first_day = brave_get_travel_plan_first_day_overview($travel_days);
$hero_subtitle_parts = array();

if ('' !== $travel_meta['destination']) {
    $hero_subtitle_parts[] = $travel_meta['destination'];
}

if ('' !== $travel_date_range) {
    $hero_subtitle_parts[] = $travel_date_range;
}

get_template_part(
    'template-parts/page-hero',
    null,
    array(
        'title' => get_the_title(),
        'subtitle' => implode(' · ', array_filter($hero_subtitle_parts)),
    )
);
?>

<section class="content-section travel-single-section">
    <div class="page-shell page-shell-narrow travel-single-shell">
        <div class="travel-single-meta-strip">
            <div class="travel-single-meta-card">
                <span class="travel-single-meta-label"><?php esc_html_e('状态', 'brave-love'); ?></span>
                <strong class="travel-single-meta-value">
                    <span class="travel-plan-status" data-tone="<?php echo esc_attr($travel_status_tone); ?>"><?php echo esc_html($travel_status_label); ?></span>
                </strong>
            </div>

            <div class="travel-single-meta-card travel-single-meta-card-duration">
                <span class="travel-single-meta-label"><?php esc_html_e('时长', 'brave-love'); ?></span>
                <strong class="travel-single-meta-value"><?php echo esc_html($travel_duration); ?></strong>
            </div>

            <div class="travel-single-meta-card travel-single-meta-card-keyword">
                <span class="travel-single-meta-label"><?php esc_html_e('主题', 'brave-love'); ?></span>
                <strong class="travel-single-meta-value"><?php echo esc_html('' !== $travel_keyword ? $travel_keyword : __('待补充', 'brave-love')); ?></strong>
            </div>

            <div class="travel-single-meta-card travel-single-meta-card-date">
                <span class="travel-single-meta-label"><?php esc_html_e('日期', 'brave-love'); ?></span>
                <strong class="travel-single-meta-value"><?php echo esc_html($travel_date_range); ?></strong>
            </div>
        </div>

        <div class="travel-single-top">
            <article class="travel-panel travel-summary-card">
                <header class="travel-summary-head">
                    <div class="travel-summary-heading">
                        <span class="travel-section-label"><?php esc_html_e('旅程说明', 'brave-love'); ?></span>
                    </div>
                </header>

                <div class="travel-summary-copy travel-summary-copy-wide">
                    <?php if ('' !== $travel_summary) : ?>
                        <p class="travel-summary-text"><?php echo esc_html($travel_summary); ?></p>
                    <?php endif; ?>

                    <?php if ($travel_content) : ?>
                        <div class="travel-summary-content">
                            <?php echo $travel_content; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
                        </div>
                    <?php elseif ('' === $travel_summary) : ?>
                        <p class="travel-summary-empty"><?php esc_html_e('这一页还没有补充详细说明，不过每天的安排已经可以继续往下拆了。', 'brave-love'); ?></p>
                    <?php endif; ?>
                </div>

                <?php if ('' !== $travel_departure_note) : ?>
                    <section class="travel-prep-card travel-prep-card-note">
                        <span class="travel-prep-label"><?php esc_html_e('出发前提醒', 'brave-love'); ?></span>
                        <div class="travel-prep-note-copy"><?php echo nl2br(esc_html($travel_departure_note)); ?></div>
                    </section>
                <?php endif; ?>

                <?php if (!empty($travel_tips) || !empty($travel_checklist)) : ?>
                    <div class="travel-prep-grid">
                        <?php if (!empty($travel_tips)) : ?>
                            <section class="travel-prep-card" data-prep="tips">
                                <span class="travel-prep-label"><?php echo esc_html($travel_tips_title); ?></span>
                                <ul class="travel-prep-list">
                                    <?php foreach ($travel_tips as $travel_tip) : ?>
                                        <li><?php echo esc_html($travel_tip); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </section>
                        <?php endif; ?>

                        <?php if (!empty($travel_checklist)) : ?>
                            <section class="travel-prep-card" data-prep="checklist">
                                <span class="travel-prep-label"><?php echo esc_html($travel_checklist_title); ?></span>
                                <ul class="travel-prep-checklist">
                                    <?php foreach ($travel_checklist as $checklist_item) : ?>
                                        <li><?php echo esc_html($checklist_item); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </section>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </article>

            <aside class="travel-panel travel-overview-card travel-overview-card-detail">
                <div class="travel-overview-head">
                    <span class="travel-section-label"><?php esc_html_e('行前速览', 'brave-love'); ?></span>
                </div>

                <div class="travel-overview-list">
                    <div class="travel-overview-item travel-overview-item-feature">
                        <span class="travel-overview-label"><?php esc_html_e('目的地', 'brave-love'); ?></span>
                        <div class="travel-overview-stack">
                            <strong class="travel-overview-value"><?php echo '' !== $travel_meta['destination'] ? esc_html($travel_meta['destination']) : esc_html__('待补充', 'brave-love'); ?></strong>
                        </div>
                    </div>

                    <?php if ('' !== $first_day['city'] || '' !== $first_day['title'] || '' !== $first_day['date'] || '' !== $first_day['weather']) : ?>
                        <div class="travel-overview-item">
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
                        <div class="travel-overview-item">
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
                        <div class="travel-overview-item">
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
            </aside>
        </div>

        <?php if (!empty($travel_days)) : ?>
            <div class="travel-day-section-head travel-day-section-head-simple">
                <span class="travel-section-label"><?php esc_html_e('每日安排', 'brave-love'); ?></span>
            </div>

            <div class="travel-day-list">
                <?php foreach ($travel_days as $index => $day) : ?>
                    <?php
                    $spots = brave_split_multiline_items($day['spots'] ?? '');
                    $restaurants = brave_split_multiline_items($day['restaurants'] ?? '');
                    $day_date = brave_format_travel_date($day['date'] ?? '');
                    $day_title = trim((string) ($day['title'] ?? ''));
                    $day_city = trim((string) ($day['city'] ?? ''));
                    $day_date_raw = trim((string) ($day['date'] ?? ''));
                    $day_weather = trim((string) ($day['weather'] ?? ''));
                    $day_outfit = trim((string) ($day['outfit'] ?? ''));
                    $has_day_brief = '' !== $day_date_raw || '' !== $day_city || '' !== $day_weather || '' !== $day_outfit;
                    ?>
                    <article class="travel-day-card">
                        <header class="travel-day-head">
                            <div class="travel-day-head-main">
                                <span class="travel-day-badge"><?php echo esc_html(sprintf(__('Day %d', 'brave-love'), $index + 1)); ?></span>
                                <?php if ('' !== $day_title) : ?>
                                    <h2 class="travel-day-title"><?php echo esc_html($day_title); ?></h2>
                                <?php else : ?>
                                    <h2 class="travel-day-title"><?php esc_html_e('当天安排', 'brave-love'); ?></h2>
                                <?php endif; ?>
                            </div>

                            <div class="travel-day-head-meta">
                                <?php if ('' !== $day_date_raw) : ?>
                                    <span class="travel-day-meta-item">📅 <?php echo esc_html($day_date); ?></span>
                                <?php endif; ?>
                                <?php if ('' !== $day_city) : ?>
                                    <span class="travel-day-meta-item">📍 <?php echo esc_html($day_city); ?></span>
                                <?php endif; ?>
                            </div>
                        </header>

                        <div class="travel-day-layout <?php echo $has_day_brief ? 'has-brief' : 'no-brief'; ?>">
                            <?php if ($has_day_brief) : ?>
                                <aside class="travel-day-brief">
                                    <div class="travel-day-brief-card">
                                        <span class="travel-day-brief-label"><?php esc_html_e('行程摘要', 'brave-love'); ?></span>
                                        <div class="travel-day-brief-facts">
                                            <?php if ('' !== $day_date_raw) : ?>
                                                <div class="travel-day-brief-row">
                                                    <span class="travel-day-brief-caption"><?php esc_html_e('日期', 'brave-love'); ?></span>
                                                    <strong class="travel-day-brief-value"><?php echo esc_html($day_date); ?></strong>
                                                </div>
                                            <?php endif; ?>

                                            <?php if ('' !== $day_city) : ?>
                                                <div class="travel-day-brief-row">
                                                    <span class="travel-day-brief-caption"><?php esc_html_e('城市', 'brave-love'); ?></span>
                                                    <strong class="travel-day-brief-value"><?php echo esc_html($day_city); ?></strong>
                                                </div>
                                            <?php endif; ?>

                                        </div>
                                    </div>

                                    <?php if ('' !== $day_weather || '' !== $day_outfit) : ?>
                                        <div class="travel-day-highlights travel-day-highlights-brief">
                                            <?php if ('' !== $day_weather) : ?>
                                                <div class="travel-highlight-card" data-kind="weather">
                                                    <span class="travel-highlight-label"><?php esc_html_e('天气预估', 'brave-love'); ?></span>
                                                    <strong class="travel-highlight-value"><?php echo esc_html($day_weather); ?></strong>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ('' !== $day_outfit) : ?>
                                                <div class="travel-highlight-card" data-kind="outfit">
                                                    <span class="travel-highlight-label"><?php esc_html_e('穿衣建议', 'brave-love'); ?></span>
                                                    <strong class="travel-highlight-value"><?php echo esc_html($day_outfit); ?></strong>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </aside>
                            <?php endif; ?>

                            <div class="travel-day-journal">
                                <div class="travel-day-grid">
                                    <?php if (!empty($day['hotel'])) : ?>
                                        <section class="travel-day-panel" data-panel="hotel">
                                            <span class="travel-day-panel-label">🏨 <?php esc_html_e('入住酒店', 'brave-love'); ?></span>
                                            <div class="travel-day-panel-copy"><?php echo nl2br(esc_html($day['hotel'])); ?></div>
                                        </section>
                                    <?php endif; ?>

                                    <?php if (!empty($day['transport'])) : ?>
                                        <section class="travel-day-panel" data-panel="transport">
                                            <span class="travel-day-panel-label">🚇 <?php esc_html_e('交通安排', 'brave-love'); ?></span>
                                            <div class="travel-day-panel-copy"><?php echo nl2br(esc_html($day['transport'])); ?></div>
                                        </section>
                                    <?php endif; ?>

                                    <?php if (!empty($spots)) : ?>
                                        <section class="travel-day-panel" data-panel="spots">
                                            <span class="travel-day-panel-label">📸 <?php esc_html_e('景点安排', 'brave-love'); ?></span>
                                            <ul class="travel-day-list-items">
                                                <?php foreach ($spots as $spot) : ?>
                                                    <li><?php echo esc_html($spot); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </section>
                                    <?php endif; ?>

                                    <?php if (!empty($restaurants)) : ?>
                                        <section class="travel-day-panel" data-panel="restaurants">
                                            <span class="travel-day-panel-label">🍽️ <?php esc_html_e('餐厅安排', 'brave-love'); ?></span>
                                            <ul class="travel-day-list-items">
                                                <?php foreach ($restaurants as $restaurant) : ?>
                                                    <li><?php echo esc_html($restaurant); ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        </section>
                                    <?php endif; ?>

                                    <?php if (!empty($day['notes'])) : ?>
                                        <section class="travel-day-panel travel-day-panel-wide" data-panel="notes">
                                            <span class="travel-day-panel-label">💡 <?php esc_html_e('页边备忘', 'brave-love'); ?></span>
                                            <div class="travel-day-panel-copy"><?php echo nl2br(esc_html($day['notes'])); ?></div>
                                        </section>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php else : ?>
            <div class="travel-panel travel-empty-state">
                <div class="travel-empty-icon">📝</div>
                <h2 class="travel-empty-title"><?php esc_html_e('这趟旅程还没拆到每天', 'brave-love'); ?></h2>
                <p class="travel-empty-text"><?php esc_html_e('可以在后台的“旅行计划详情”里继续补充 Day 1、Day 2 的天气、酒店、交通和餐厅安排。', 'brave-love'); ?></p>
            </div>
        <?php endif; ?>
    </div>
</section>

<?php
get_footer();
