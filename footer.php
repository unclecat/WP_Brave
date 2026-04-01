</main><!-- #main -->

<!-- 页脚 -->
<footer class="footer">
    <div class="footer-text">
        &copy; <?php echo date('Y'); ?> <a href="<?php echo esc_url(home_url('/')); ?>"><?php bloginfo('name'); ?></a>
    </div>
    <div class="footer-text">
        <?php _e('用 ❤️ 记录我们的故事', 'brave-love'); ?>
    </div>
    <?php if (get_theme_mod('brave_pv_enabled', true)) : ?>
    <div class="footer-pv-stats">
        <?php $pv = brave_get_pv_stats(); ?>
        <span class="pv-item"><?php echo esc_html(brave_get_pv_display_text('today')); ?> <?php echo number_format($pv['today_count']); ?> <?php echo esc_html(brave_get_pv_display_text('unit')); ?></span>
        <span class="pv-separator">·</span>
        <span class="pv-item"><?php echo esc_html(brave_get_pv_display_text('total')); ?> <?php echo number_format($pv['total_count']); ?> <?php echo esc_html(brave_get_pv_display_text('unit')); ?></span>
    </div>
    <?php endif; ?>
</footer>

<!-- 返回顶部 -->
<button class="back-to-top" id="backToTop" aria-label="<?php _e('返回顶部', 'brave-love'); ?>">
    ↑
</button>

<?php wp_footer(); ?>
</body>
</html>
