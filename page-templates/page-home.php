<?php
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
?>

<!-- 计时器区域 - 恋爱正计时 -->
<section class="timer-section">
    <p class="timer-title"><?php echo esc_html($timer_text); ?></p>
    <div class="timer-display" id="love-timer">
        <span class="timer-number" id="timer-days">0</span> 天
        <span class="timer-number" id="timer-hours">0</span> 小时
        <span class="timer-number" id="timer-minutes">0</span> 分
    </div>
    <?php if ($love_start_datetime) : ?>
    <p class="timer-date" style="font-size: 0.8rem; color: #999; margin-top: 0.5rem;">
        起始时间：<?php echo esc_html($love_start_datetime); ?>
    </p>
    <?php else : ?>
    <p class="timer-date" style="font-size: 0.8rem; color: #ff5162; margin-top: 0.5rem;">
        ⚠️ 请在外观 → 自定义 → Brave 主题设置中设置恋爱起始时间
    </p>
    <?php endif; ?>
</section>

<!-- 纪念日倒计时区域 -->
<?php if ($next_anniversary_datetime) : ?>
<section class="timer-section countdown-section" style="background: linear-gradient(135deg, #e3f2fd 0%, #f5f5f5 100%);">
    <p class="timer-title"><?php echo esc_html($countdown_text); ?></p>
    <div class="countdown-target" style="font-size: 1.1rem; color: #666; margin-bottom: 1rem;">
        🎯 <?php echo esc_html($next_anniversary_name); ?>
    </div>
    <div class="timer-display countdown-display" id="anniversary-countdown">
        <span class="timer-number" id="countdown-days">0</span> 天
        <span class="timer-number" id="countdown-hours">0</span> 小时
        <span class="timer-number" id="countdown-minutes">0</span> 分
    </div>
    <p class="timer-date" style="font-size: 0.8rem; color: #666; margin-top: 0.5rem;">
        目标时间：<?php echo esc_html($next_anniversary_datetime); ?>
    </p>
</section>
<?php endif; ?>

<!-- 纪念日列表（保留原有功能） -->
<?php
$anniversaries = brave_get_anniversaries();
if (!empty($anniversaries)) :
?>
<section class="anniversary-section">
    <h3 class="anniversary-title">💕 特别的日子</h3>
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
        <a href="<?php echo esc_url(brave_get_page_link('list')); ?>" class="entry-card">
            <div class="entry-icon"><?php echo brave_get_option('icon_list', '📜'); ?></div>
            <div class="entry-title"><?php _e('恋爱清单', 'brave-love'); ?></div>
            <div class="entry-desc"><?php _e('一起做的事', 'brave-love'); ?></div>
        </a>
        <a href="<?php echo esc_url(brave_get_page_link('memory')); ?>" class="entry-card">
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
