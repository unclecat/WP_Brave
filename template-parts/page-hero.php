<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * 通用内容页 Hero。
 *
 * @package Brave_Love
 */

$args = brave_resolve_page_hero_args($args);
?>

<section class="page-hero-section">
    <div class="page-hero-bg" style="<?php echo esc_attr($args['background_style']); ?>"></div>
    <div class="page-hero-overlay"></div>

    <div class="container page-hero-content">
        <div class="page-hero-panel">
            <?php if (!empty($args['eyebrow'])) : ?>
                <span class="page-hero-kicker"><?php echo esc_html($args['eyebrow']); ?></span>
            <?php endif; ?>

            <?php if (!empty($args['title'])) : ?>
                <h1 class="page-hero-title"><?php echo esc_html($args['title']); ?></h1>
            <?php endif; ?>

            <?php if (!empty($args['subtitle'])) : ?>
                <p class="page-hero-desc"><?php echo esc_html($args['subtitle']); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <div class="waves-area">
        <svg class="waves-svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28" preserveAspectRatio="none">
            <defs>
                <path id="gentle-wave-page" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18v44h-352z"/>
            </defs>
            <g class="parallax">
                <use xlink:href="#gentle-wave-page" x="48" y="0"/>
                <use xlink:href="#gentle-wave-page" x="48" y="3"/>
                <use xlink:href="#gentle-wave-page" x="48" y="5"/>
                <use xlink:href="#gentle-wave-page" x="48" y="7"/>
            </g>
        </svg>
    </div>
</section>
