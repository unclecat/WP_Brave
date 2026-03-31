<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="theme-color" content="#ff5162">
    <link rel="profile" href="https://gmpg.org/xfn/11">
    
    <?php wp_head(); ?>
    
    <!-- 预加载字体 -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<?php 
$hero_bg = get_theme_mod('brave_hero_bg');
$boy_avatar = brave_get_couple_avatar('boy', 200);
$boy_name = brave_get_couple_name('boy');
$girl_avatar = brave_get_couple_avatar('girl', 200);
$girl_name = brave_get_couple_name('girl');
$nav_text = get_theme_mod('brave_nav_text', '世间最动情之事，莫过于两人相依');
?>

<!-- 导航栏 -->
<nav class="navbar navbar-expand-lg navbar-brave">
    <div class="container">
        <a class="navbar-brand" href="<?php echo esc_url(home_url('/')); ?>">
            <?php bloginfo('name'); ?>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon" style="background-image: url('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 30 30%22%3E%3Cpath stroke=%22rgba%28255,255,255,0.9%29%22 stroke-width=%222%22 stroke-linecap=%22round%22 stroke-miterlimit=%2210%22 d=%22M4 7h22M4 15h22M4 23h22%22/%3E%3C/svg%3E');"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if ($nav_text) : ?>
                <li class="nav-item">
                    <span class="nav-say text-white"><?php echo esc_html($nav_text); ?></span>
                </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (is_page_template('page-templates/page-home.php') || is_front_page()) : ?>
<!-- Hero 区域 -->
<section class="hero-section">
    <div class="hero-bg" style="background-image: url('<?php echo $hero_bg ? esc_url($hero_bg) : 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=1920'; ?>');"></div>
    
    <div class="lover-container">
        <div class="lover-item">
            <div class="avatar-wrapper">
                <?php if ($boy_avatar) : ?>
                    <img src="<?php echo esc_url($boy_avatar); ?>" alt="<?php echo esc_attr($boy_name); ?>" class="lover-avatar">
                <?php else : ?>
                    <div class="lover-avatar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);"></div>
                <?php endif; ?>
                <!-- 星星旋转动画 -->
                <div class="stars-orbit">
                    <span class="star" style="--delay:0s;--size:3px;"></span>
                    <span class="star" style="--delay:0.5s;--size:2px;"></span>
                    <span class="star" style="--delay:1s;--size:4px;"></span>
                    <span class="star" style="--delay:1.5s;--size:2px;"></span>
                    <span class="star" style="--delay:2s;--size:3px;"></span>
                    <span class="star" style="--delay:2.5s;--size:2px;"></span>
                </div>
            </div>
            <span class="lover-name"><?php echo esc_html($boy_name); ?></span>
        </div>
        
        <div class="heart-container">
            <div class="heart"></div>
        </div>
        
        <div class="lover-item">
            <div class="avatar-wrapper">
                <?php if ($girl_avatar) : ?>
                    <img src="<?php echo esc_url($girl_avatar); ?>" alt="<?php echo esc_attr($girl_name); ?>" class="lover-avatar">
                <?php else : ?>
                    <div class="lover-avatar" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);"></div>
                <?php endif; ?>
                <!-- 星星旋转动画 -->
                <div class="stars-orbit">
                    <span class="star" style="--delay:0.3s;--size:2px;"></span>
                    <span class="star" style="--delay:0.8s;--size:3px;"></span>
                    <span class="star" style="--delay:1.3s;--size:2px;"></span>
                    <span class="star" style="--delay:1.8s;--size:4px;"></span>
                    <span class="star" style="--delay:2.3s;--size:2px;"></span>
                    <span class="star" style="--delay:2.8s;--size:3px;"></span>
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
