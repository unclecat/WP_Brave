<?php
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#fff7f8" id="brave-theme-color">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    <script>
        (function() {
            var theme = 'light';

            try {
                var storedTheme = window.localStorage.getItem('brave-theme');
                if (storedTheme === 'dark' || storedTheme === 'light') {
                    theme = storedTheme;
                }
            } catch (error) {
                theme = 'light';
            }

            document.documentElement.setAttribute('data-theme', theme);
            document.documentElement.style.colorScheme = theme;

            var themeColor = document.getElementById('brave-theme-color');
            if (themeColor) {
                themeColor.setAttribute('content', theme === 'dark' ? '#18161d' : '#fff7f8');
            }
        })();
    </script>
    
    <?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php 
$boy_avatar = brave_get_couple_avatar('boy', 200);
$boy_name = brave_get_couple_name('boy');
$girl_avatar = brave_get_couple_avatar('girl', 200);
$girl_name = brave_get_couple_name('girl');
$nav_text = get_theme_mod('brave_nav_text', '世间最动情之事，莫过于两人相依');
$hero_bg_style = brave_get_hero_background_style();
$is_home_nav = is_page_template('page-templates/page-home.php') || is_front_page();
$has_primary_menu = has_nav_menu('primary');
?>

<!-- 导航栏 -->
<nav class="navbar navbar-expand-lg navbar-brave <?php echo $is_home_nav ? 'is-home-nav' : 'is-inner-nav'; ?> <?php echo $has_primary_menu ? 'has-primary-menu' : 'no-primary-menu'; ?>">
    <div class="container">
        <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
            <span class="navbar-brand-title"><?php echo esc_html(get_bloginfo('name')); ?></span>
            <?php if ($nav_text) : ?>
                <span class="navbar-brand-subtitle"><?php echo esc_html($nav_text); ?></span>
            <?php endif; ?>
        </a>
        <?php if ($has_primary_menu) : ?>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 30 30%22%3E%3Cpath stroke=%22rgba%28255,255,255,0.9%29%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-miterlimit=%2210%22 d=%22M4 7h22M4 15h22M4 23h22%22/%3E%3C/svg%3E');"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php
            wp_nav_menu(
                array(
                    'theme_location' => 'primary',
                    'container' => false,
                    'menu_class' => 'navbar-nav ms-auto align-items-lg-center',
                    'fallback_cb' => false,
                    'depth' => 2,
                )
            );
            ?>
        </div>
        <?php endif; ?>
    </div>
</nav>

<button
    class="theme-toggle"
    type="button"
    aria-label="切换到深色模式"
    aria-pressed="false"
    title="切换到深色模式"
    data-theme-toggle
>
    <span class="theme-toggle-icon" aria-hidden="true" data-theme-toggle-icon>🌙</span>
</button>

<?php if ($is_home_nav) : ?>
<!-- Hero 区域 -->
<section class="hero-section">
    <div class="hero-bg" style="<?php echo esc_attr($hero_bg_style); ?>"></div>
    
    <div class="lover-container">
        <div class="lover-item">
            <div class="avatar-wrapper">
                <?php if ($boy_avatar) : ?>
                    <img src="<?php echo brave_esc_avatar_url($boy_avatar); ?>" alt="<?php echo esc_attr($boy_name); ?>" class="lover-avatar">
                <?php else : ?>
                    <div class="lover-avatar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                <?php endif; ?>
                <div class="stars-orbit" aria-hidden="true">
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                </div>
            </div>
            <span class="lover-name"><?php echo esc_html($boy_name); ?></span>
        </div>

        <div class="lover-connector" aria-hidden="true">
            <svg class="lover-connector-svg" viewBox="0 0 260 60" preserveAspectRatio="none">
                <defs>
                    <linearGradient id="lover-connector-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                        <stop offset="0%" stop-color="#fff7fb" stop-opacity="0"></stop>
                        <stop offset="24%" stop-color="#ffe2ec" stop-opacity="0.54"></stop>
                        <stop offset="50%" stop-color="#fffafc" stop-opacity="0.96"></stop>
                        <stop offset="76%" stop-color="#ffe2ec" stop-opacity="0.54"></stop>
                        <stop offset="100%" stop-color="#fff7fb" stop-opacity="0"></stop>
                    </linearGradient>
                    <filter id="lover-connector-dot-glow" x="-50%" y="-50%" width="200%" height="200%">
                        <feGaussianBlur stdDeviation="2.4" result="blur"></feGaussianBlur>
                        <feMerge>
                            <feMergeNode in="blur"></feMergeNode>
                            <feMergeNode in="SourceGraphic"></feMergeNode>
                        </feMerge>
                    </filter>
                </defs>
                <path id="lover-connector-path" class="lover-connector-base" d="M6 32H64L78 32L88 24L98 38L108 28L116 32L124 26L132 34L140 16L152 44L164 18L172 32L180 24L190 38L200 28L208 32L216 26L226 32H254"></path>
                <path class="lover-connector-pulse" d="M6 32H64L78 32L88 24L98 38L108 28L116 32L124 26L132 34L140 16L152 44L164 18L172 32L180 24L190 38L200 28L208 32L216 26L226 32H254"></path>
                <circle class="lover-connector-dot" cx="0" cy="0" r="3.2" filter="url(#lover-connector-dot-glow)">
                    <animateMotion dur="3.2s" repeatCount="indefinite" rotate="auto" calcMode="spline" keyTimes="0;1" keySplines="0.38 0 0.62 1">
                        <mpath href="#lover-connector-path"></mpath>
                    </animateMotion>
                </circle>
            </svg>
        </div>
        
        <div class="heart-container">
            <div class="heart"></div>
        </div>
        
        <div class="lover-item">
            <div class="avatar-wrapper">
                <?php if ($girl_avatar) : ?>
                    <img src="<?php echo brave_esc_avatar_url($girl_avatar); ?>" alt="<?php echo esc_attr($girl_name); ?>" class="lover-avatar">
                <?php else : ?>
                    <div class="lover-avatar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                <?php endif; ?>
                <div class="stars-orbit" aria-hidden="true">
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                    <span class="orbit-star"></span>
                </div>
            </div>
            <span class="lover-name"><?php echo esc_html($girl_name); ?></span>
        </div>
    </div>
    
    <!-- 波浪 -->
    <div class="waves-area">
        <svg class="waves-svg" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 24 150 28" preserveAspectRatio="none">
            <defs>
                <path id="gentle-wave" d="M-160 44c30 0 58-18 88-18s 58 18 88 18 58-18 88-18 58 18 88 18v44h-352z"/>
            </defs>
            <g class="parallax">
                <use xlink:href="#gentle-wave" x="48" y="0"/>
                <use xlink:href="#gentle-wave" x="48" y="3"/>
                <use xlink:href="#gentle-wave" x="48" y="5"/>
                <use xlink:href="#gentle-wave" x="48" y="7"/>
            </g>
        </svg>
    </div>
</section>
<?php endif; ?>

<!-- 主内容区开始 -->
<main id="main" class="site-main">
