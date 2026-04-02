<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Template Name: 首页 - 关于我们
 *
 * @package Brave_Love
 */

get_header();

// 获取设置
$timer_text = get_theme_mod('brave_timer_text', '我们风雨同舟已经一起走过');
$countdown_text = get_theme_mod('brave_countdown_text', '距离我们的特别日子还有');
$next_anniversary_name = get_theme_mod('brave_next_anniversary_name', '恋爱周年纪念日');
$next_anniversary_datetime = get_theme_mod('brave_next_anniversary_datetime', '');
$love_start_datetime = get_theme_mod('brave_love_start_datetime', '');
$anniversary_section_title = get_theme_mod('brave_anniversary_section_title', '💕 特别的日子');
?>

<!-- 计时器区域 -->
<div class="timer-wrapper">
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
        <p class="timer-date" style="color: #ff5162;">⚠️ 请在外观 → 自定义 → Brave 主题设置中设置恋爱起始时间</p>
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

<!-- 天气小组件 -->
<?php 
$weather_enabled = brave_is_weather_enabled();
$weather_cities = brave_get_weather_cities();
if ($weather_enabled && !empty($weather_cities)) : 
?>
<section class="weather-section">
    <h3 class="weather-section-title">🌤️ 今日天气</h3>
    <div class="weather-scroll" id="weather-scroll">
        <?php foreach ($weather_cities as $index => $city) : ?>
        <div class="weather-card" 
             data-index="<?php echo $index; ?>"
             data-name="<?php echo esc_attr($city['name']); ?>"
             data-lat="<?php echo esc_attr($city['lat']); ?>"
             data-lon="<?php echo esc_attr($city['lon']); ?>"
             data-weather="sunny">
            <div class="weather-city-name"><?php echo esc_html($city['name']); ?></div>
            <div class="weather-icon">☀️</div>
            <div class="weather-temp">--°</div>
            <div class="weather-desc">加载中</div>
        </div>
        <?php endforeach; ?>
    </div>
</section>

<!-- 天气详情模态框 -->
<div class="weather-modal" id="weather-modal">
    <div class="weather-modal-content">
        <button class="weather-modal-close" id="weather-modal-close">&times;</button>
        <div class="weather-modal-header">
            <h3 class="weather-modal-city" id="modal-city">城市</h3>
            <span class="weather-modal-time" id="modal-time">--:-- 更新</span>
        </div>
        <div class="weather-modal-main">
            <div class="weather-modal-icon" id="modal-icon">☀️</div>
            <div class="weather-modal-temp" id="modal-temp">--°</div>
            <div class="weather-modal-desc" id="modal-desc">--</div>
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
        </div>
        <div class="weather-modal-clothing">
            <div class="clothing-title">👔 穿衣指南</div>
            <div class="clothing-text" id="modal-clothing">正在分析...</div>
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
    <h3 class="anniversary-title"><?php echo esc_html($anniversary_section_title); ?></h3>
    <div class="anniversary-scroll">
        <?php foreach ($anniversaries as $item) : ?>
            <div class="anniversary-card <?php echo $item['is_countdown'] ? 'countdown' : 'countup'; ?>">
                <div class="anniversary-name"><?php echo esc_html($item['name']); ?></div>
                <div class="anniversary-days">
                    <?php echo $item['is_countdown'] ? '还有' : '已经'; ?>
                    <?php echo $item['days']; ?>
                    <span>天</span>
                </div>
                <div class="anniversary-date"><?php echo esc_html($item['date']); ?></div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
<?php endif; ?>

<!-- 入口卡片 -->
<section class="entry-section">
    <div class="entry-grid">
        <a href="<?php echo esc_url(brave_get_page_link('moments')); ?>" class="entry-card">
            <div class="entry-icon"><?php echo brave_get_option('icon_moments', '💖'); ?></div>
            <div class="entry-title"><?php _e('点点滴滴', 'brave-love'); ?></div>
            <div class="entry-desc"><?php _e('记录生活', 'brave-love'); ?></div>
        </a>
        <a href="<?php echo esc_url(brave_get_page_link('lists')); ?>" class="entry-card">
            <div class="entry-icon"><?php echo brave_get_option('icon_list', '📜'); ?></div>
            <div class="entry-title"><?php _e('恋爱清单', 'brave-love'); ?></div>
            <div class="entry-desc"><?php _e('一起做的事', 'brave-love'); ?></div>
        </a>
        <a href="<?php echo esc_url(brave_get_page_link('memories')); ?>" class="entry-card">
            <div class="entry-icon"><?php echo brave_get_option('icon_memory', '📷'); ?></div>
            <div class="entry-title"><?php _e('甜蜜相册', 'brave-love'); ?></div>
            <div class="entry-desc"><?php _e('美好回忆', 'brave-love'); ?></div>
        </a>
        <a href="<?php echo esc_url(brave_get_page_link('notes')); ?>" class="entry-card">
            <div class="entry-icon"><?php echo brave_get_option('icon_notes', '📝'); ?></div>
            <div class="entry-title"><?php _e('随笔说说', 'brave-love'); ?></div>
            <div class="entry-desc"><?php _e('心情碎片', 'brave-love'); ?></div>
        </a>
        <a href="<?php echo esc_url(brave_get_page_link('blessing')); ?>" class="entry-card">
            <div class="entry-icon"><?php echo brave_get_option('icon_blessing', '💌'); ?></div>
            <div class="entry-title"><?php _e('祝福留言', 'brave-love'); ?></div>
            <div class="entry-desc"><?php _e('收到祝福', 'brave-love'); ?></div>
        </a>
    </div>
</section>

<?php
get_footer();
