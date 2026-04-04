<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template Name: 首页
 *
 * @package Brave_Love
 */

get_header();

// 获取设置
$timer_text = get_theme_mod('brave_timer_text', '我们风雨同舟已经一起走过');
$countdown_text = get_theme_mod('brave_countdown_text', '距离我们的特别日子还有');
$next_anniversary_name = get_theme_mod('brave_next_anniversary_name', '恋爱周年纪念日');
$next_anniversary_datetime = get_theme_mod('brave_next_anniversary_datetime', '');
$love_start_datetime = brave_get_love_start_datetime();
$anniversary_section_title = get_theme_mod('brave_anniversary_section_title', '💕 特别的日子');
?>

<!-- 计时器区域 -->
<section class="timer-band">
    <div class="timer-shell page-shell">
        <div class="timer-wrapper <?php echo $next_anniversary_datetime ? 'has-countdown' : 'is-single'; ?>">
            <!-- 恋爱正计时 -->
            <section class="timer-section timer-first">
                <p class="timer-title"><?php echo esc_html($timer_text); ?></p>
                <div class="timer-display" id="love-timer">
                    <span class="timer-number" id="timer-days">0</span> 天
                    <span class="timer-number" id="timer-hours">0</span> 时
                    <span class="timer-number" id="timer-minutes">0</span> 分
                    <span class="timer-number" id="timer-seconds">0</span> 秒
                </div>
                <?php if ($love_start_datetime) : ?>
                <p class="timer-date">起始时间：<?php echo esc_html($love_start_datetime); ?></p>
                <?php else : ?>
                <p class="timer-date timer-date-warning">⚠️ 请在外观 → 自定义 → Brave 主题设置中设置恋爱起始时间</p>
                <?php endif; ?>
            </section>

            <!-- 纪念日倒计时 -->
            <?php if ($next_anniversary_datetime) : ?>
            <section class="timer-section timer-second">
                <p class="timer-title"><?php echo esc_html($countdown_text); ?></p>
                <p class="countdown-target"><?php echo esc_html($next_anniversary_name); ?></p>
                <div class="timer-display" id="anniversary-countdown">
                    <span class="timer-number" id="countdown-days">0</span> 天
                    <span class="timer-number" id="countdown-hours">0</span> 时
                    <span class="timer-number" id="countdown-minutes">0</span> 分
                    <span class="timer-number" id="countdown-seconds">0</span> 秒
                </div>
                <p class="timer-date">目标时间：<?php echo esc_html($next_anniversary_datetime); ?></p>
            </section>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- 天气小组件 -->
<?php 
$weather_enabled = brave_is_weather_enabled();
$weather_cities = brave_get_weather_cities();
if ($weather_enabled && !empty($weather_cities)) : 
?>
<section class="weather-section">
    <div class="weather-shell page-shell">
        <div class="weather-section-header">
            <div class="weather-section-copy">
                <h3 class="weather-section-title">🌤️ 今日天气</h3>
            </div>
        </div>

        <div class="weather-scroll" id="weather-scroll">
            <?php foreach ($weather_cities as $index => $city) : ?>
            <button type="button"
                    class="weather-card is-loading"
                    data-index="<?php echo $index; ?>"
                    data-name="<?php echo esc_attr($city['name']); ?>"
                    data-lat="<?php echo esc_attr($city['lat']); ?>"
                    data-lon="<?php echo esc_attr($city['lon']); ?>"
                    data-weather="sunny"
                    aria-haspopup="dialog">
                <div class="weather-card-top">
                    <span class="weather-city-name"><?php echo esc_html($city['name']); ?></span>
                    <span class="weather-card-status">同步中</span>
                </div>

                <div class="weather-card-main">
                    <div class="weather-icon">☀️</div>
                    <div class="weather-card-main-copy">
                        <div class="weather-temp">--°</div>
                        <div class="weather-desc">加载中</div>
                    </div>
                </div>

                <div class="weather-card-footer">
                    <span class="weather-range">--° ~ --°</span>
                    <span class="weather-precip">降水 --%</span>
                </div>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- 天气详情模态框 -->
<div class="weather-modal" id="weather-modal" aria-hidden="true">
    <div class="weather-modal-content" role="dialog" aria-modal="true" aria-labelledby="modal-city" data-weather="sunny">
        <div class="weather-modal-header">
            <div class="weather-modal-header-copy">
                <span class="weather-modal-kicker">实时天气</span>
                <h3 class="weather-modal-city" id="modal-city">城市</h3>
                <span class="weather-modal-time" id="modal-time">-- 更新</span>
            </div>
            <button type="button" class="weather-modal-close" id="weather-modal-close" aria-label="关闭天气详情">&times;</button>
        </div>

        <div class="weather-modal-main">
            <div class="weather-modal-main-primary">
                <div class="weather-modal-icon" id="modal-icon">☀️</div>
                <div class="weather-modal-main-copy">
                    <div class="weather-modal-temp" id="modal-temp">--°</div>
                    <div class="weather-modal-desc" id="modal-desc">--</div>
                    <div class="weather-modal-range" id="modal-range">今日 --° ~ --°</div>
                    <div class="weather-modal-sunline">
                        <span class="weather-modal-sunitem">
                            <span class="weather-modal-sunicon" aria-hidden="true">🌅</span>
                            <span id="modal-sunrise">--:--</span>
                        </span>
                        <span class="weather-modal-sunitem">
                            <span class="weather-modal-sunicon" aria-hidden="true">🌇</span>
                            <span id="modal-sunset">--:--</span>
                        </span>
                    </div>
                </div>
            </div>

            <div class="weather-modal-aside">
                <div class="weather-modal-aside-summary">
                    <div class="weather-modal-aside-row">
                        <span class="weather-modal-aside-label">空气质量</span>
                        <span class="weather-modal-aside-value" id="modal-aqi" data-tone="unknown">
                            <span class="weather-modal-aside-dot" aria-hidden="true"></span>
                            <span class="weather-modal-aside-text">暂无 · AQI --</span>
                        </span>
                    </div>
                    <div class="weather-modal-aside-row">
                        <span class="weather-modal-aside-label">紫外线</span>
                        <span class="weather-modal-aside-value" id="modal-uv" data-tone="unknown">
                            <span class="weather-modal-aside-dot" aria-hidden="true"></span>
                            <span class="weather-modal-aside-text">暂无 · UV --</span>
                        </span>
                    </div>
                </div>

                <div class="weather-modal-note">
                    <div class="weather-modal-note-label">👔 今日穿搭</div>
                    <div class="weather-modal-tag-list" id="modal-clothing">
                        <span class="weather-modal-tag is-muted">分析中</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="weather-modal-details">
            <div class="weather-modal-item">
                <span class="weather-modal-label">体感温度</span>
                <span class="weather-modal-value" id="modal-feels">--°</span>
            </div>
            <div class="weather-modal-item">
                <span class="weather-modal-label">湿度</span>
                <span class="weather-modal-value" id="modal-humidity">--%</span>
            </div>
            <div class="weather-modal-item">
                <span class="weather-modal-label">风速</span>
                <span class="weather-modal-value" id="modal-wind">-- km/h</span>
            </div>
            <div class="weather-modal-item">
                <span class="weather-modal-label">降水概率</span>
                <span class="weather-modal-value" id="modal-precip">--%</span>
            </div>
        </div>

        <div class="weather-modal-trend">
            <div class="weather-modal-section-title">接下来 6 小时</div>
            <div class="weather-hourly-list" id="modal-hourly">
                <div class="weather-hourly-empty">正在整理趋势...</div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- 纪念日列表 -->
<?php
$anniversaries = brave_get_anniversaries();
if (!empty($anniversaries)) :
?>
<section class="anniversary-section">
    <div class="anniversary-shell page-shell">
        <div class="anniversary-section-header">
            <div class="anniversary-section-copy">
                <h3 class="anniversary-title"><?php echo esc_html($anniversary_section_title); ?></h3>
            </div>
        </div>

        <div class="anniversary-scroll">
            <?php foreach ($anniversaries as $item) : ?>
                <div class="anniversary-card <?php echo $item['is_countdown'] ? 'countdown' : 'countup'; ?>">
                    <div class="anniversary-card-top">
                        <div class="anniversary-name"><?php echo esc_html($item['name']); ?></div>
                        <?php if ($item['is_countdown']) : ?>
                            <span class="anniversary-card-status">倒计时</span>
                        <?php endif; ?>
                    </div>

                    <div class="anniversary-card-main">
                        <div class="anniversary-prefix"><?php echo $item['is_countdown'] ? '还有' : '已经'; ?></div>
                        <div class="anniversary-days">
                            <strong><?php echo esc_html($item['days']); ?></strong>
                            <span>天</span>
                        </div>
                    </div>

                    <div class="anniversary-date"><?php echo esc_html($item['date']); ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- 入口卡片 -->
<?php
$home_entries = array(
    array(
        'page' => 'about',
        'template' => 'page-templates/page-about.php',
        'icon' => brave_get_option('icon_about', '💞'),
        'title' => __('关于我们', 'brave-love'),
        'tag' => __('故事', 'brave-love'),
        'desc_fallback' => __('从初见走到相守，从陌生走到熟悉，这一路，皆是我们。', 'brave-love'),
        'tone' => 'rose',
    ),
    array(
        'page' => 'moments',
        'template' => 'page-templates/page-moments.php',
        'icon' => brave_get_option('icon_moments', '💖'),
        'title' => __('点点滴滴', 'brave-love'),
        'tag' => __('瞬间', 'brave-love'),
        'desc_fallback' => __('岁月为笔，你我为墨，写下一段独属于我们的故事。', 'brave-love'),
        'tone' => 'peach',
    ),
    array(
        'page' => 'lists',
        'icon' => brave_get_option('icon_list', '📜'),
        'title' => __('恋爱清单', 'brave-love'),
        'tag' => __('计划', 'brave-love'),
        'desc_fallback' => get_theme_mod('brave_love_list_hero_subtitle', __('不必惊天动地，只需岁岁相依，这便是我们最好的经历。', 'brave-love')),
        'tone' => 'amber',
    ),
    array(
        'page' => 'memories',
        'template' => 'page-templates/page-memories.php',
        'icon' => brave_get_option('icon_memory', '📷'),
        'title' => __('甜蜜相册', 'brave-love'),
        'tag' => __('照片', 'brave-love'),
        'desc_fallback' => __('人间烟火，山河远阔，无一不是你我同行的见证。', 'brave-love'),
        'tone' => 'sky',
    ),
    array(
        'page' => 'notes',
        'template' => 'page-templates/page-notes.php',
        'icon' => brave_get_option('icon_notes', '📝'),
        'title' => __('随笔说说', 'brave-love'),
        'tag' => __('心情', 'brave-love'),
        'desc_fallback' => __('朝暮与年岁并往，我们一同走过寻常与漫长。', 'brave-love'),
        'tone' => 'mint',
    ),
    array(
        'page' => 'blessing',
        'template' => 'page-templates/page-blessing.php',
        'icon' => brave_get_option('icon_blessing', '💌'),
        'title' => __('祝福留言', 'brave-love'),
        'tag' => __('来信', 'brave-love'),
        'desc_fallback' => __('我们的故事，不长不短，刚好是一生。', 'brave-love'),
        'tone' => 'coral',
    ),
);

foreach ($home_entries as &$entry) {
    $entry['desc'] = $entry['desc_fallback'];

    if (!empty($entry['template'])) {
        $entry_page_id = brave_get_page_id_by_template($entry['template']);

        if ($entry_page_id) {
            $entry_hero = brave_resolve_page_hero_args(
                array(
                    'post_id' => $entry_page_id,
                    'title' => $entry['title'],
                    'subtitle' => $entry['desc_fallback'],
                )
            );

            if (!empty($entry_hero['subtitle'])) {
                $entry['desc'] = wp_strip_all_tags($entry_hero['subtitle']);
            }
        }
    }
}
unset($entry);
?>
<section class="entry-section entry-home-section">
    <div class="entry-shell page-shell">
        <div class="entry-section-header">
            <div class="entry-section-copy">
                <h3 class="entry-section-title">💌 继续逛逛</h3>
            </div>
        </div>

        <div class="entry-grid">
            <?php foreach ($home_entries as $entry) : ?>
                <a href="<?php echo esc_url(brave_get_page_link($entry['page'])); ?>" class="entry-card" data-tone="<?php echo esc_attr($entry['tone']); ?>">
                    <div class="entry-card-top">
                        <div class="entry-icon"><?php echo $entry['icon']; ?></div>
                        <span class="entry-card-tag"><?php echo esc_html($entry['tag']); ?></span>
                    </div>

                    <div class="entry-card-body">
                        <div class="entry-title"><?php echo esc_html($entry['title']); ?></div>
                        <p class="entry-desc"><?php echo esc_html($entry['desc']); ?></p>
                    </div>

                    <div class="entry-card-footer">
                        <span class="entry-card-link"><?php esc_html_e('进入页面', 'brave-love'); ?></span>
                        <span class="entry-card-arrow" aria-hidden="true">→</span>
                    </div>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<?php
get_footer();
