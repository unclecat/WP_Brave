<?php
/**
 * Template Name: Hero测试页面
 *
 * 用于诊断Hero背景图显示问题
 */

get_header();

// 获取Hero背景图
$hero_bg = get_theme_mod('brave_hero_bg');
$hero_bg_url = !empty($hero_bg) ? $hero_bg : 'https://images.unsplash.com/photo-1518199266791-5375a83190b7?w=1920';
?>

<style>
/* 测试样式 - 强制显示 */
.test-hero-section {
    position: relative;
    height: 300px;
    background-image: url('<?php echo esc_url($hero_bg_url); ?>');
    background-size: cover;
    background-position: center;
    border: 5px solid red !important; /* 红色边框用于定位 */
}

.test-hero-bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 255, 0, 0.3); /* 绿色半透明 */
    z-index: -1;
}

.test-info {
    position: absolute;
    top: 10px;
    left: 10px;
    background: rgba(255, 255, 255, 0.95);
    padding: 15px;
    border-radius: 8px;
    font-family: monospace;
    font-size: 12px;
    z-index: 100;
    max-width: 400px;
}

/* 检测颜色模式 */
@media (prefers-color-scheme: light) {
    .color-mode::after { content: "LIGHT"; color: blue; }
}
@media (prefers-color-scheme: dark) {
    .color-mode::after { content: "DARK"; color: red; }
}

/* 测试4: 使用isolation: isolate */
.test-hero-section-isolated {
    position: relative;
    height: 300px;
    isolation: isolate;
    margin-top: 20px;
    border: 3px solid purple;
}

.test-hero-section-isolated .bg {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-image: url('<?php echo esc_url($hero_bg_url); ?>');
    background-size: cover;
    z-index: 0;
}

.test-hero-section-isolated .overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.3);
    z-index: 1;
}
</style>

<!-- 测试区域1: 最简单的方式 -->
<section class="test-hero-section">
    <div class="test-info">
        <strong>测试1: 直接背景图</strong><br>
        背景图URL: <?php echo esc_html(substr($hero_bg_url, 0, 50)); ?>...<br>
        颜色模式: <span class="color-mode"></span><br>
        如果看到红色边框 = section可见<br>
        如果看到绿色 = 遮罩层可见<br>
        如果看到图片 = 背景图正常
    </div>
</section>

<!-- 测试区域2: 使用独立div背景 -->
<section class="test-hero-section" style="margin-top: 20px; background-image: none;">
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background-image: url('<?php echo esc_url($hero_bg_url); ?>'); background-size: cover; z-index: -2;"></div>
    <div style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.3); z-index: -1;"></div>
    <div class="test-info">
        <strong>测试2: 独立div + z-index</strong><br>
        背景图div: z-index: -2<br>
        遮罩层div: z-index: -1
    </div>
</section>

<!-- 测试区域3: 实际使用的page-hero-section - 诊断版 -->
<section class="page-hero-section" style="margin-top: 20px; border: 3px solid orange;">
    <!-- 背景图div添加红色边框和备用背景色用于诊断 -->
    <div class="page-hero-bg" style="background-image: url('<?php echo esc_url($hero_bg_url); ?>'); border: 5px solid red; background-color: lime;"></div>
    <!-- 遮罩层添加蓝色边框用于诊断 -->
    <div class="page-hero-overlay" style="border: 3px solid blue;"></div>
    <div class="test-info" style="background: rgba(255,255,255,0.95); z-index: 10;">
        <strong>测试3: 诊断版</strong><br>
        背景图div有<span style="color:red">红色边框</span>和<span style="color:lime">绿色备用背景</span><br>
        遮罩层有<span style="color:blue">蓝色边框</span><br>
        如果看到红色/绿色/蓝色 = div存在<br>
        如果看到背景图 = 背景图正常加载
    </div>
    <!-- 暂时移除波浪，排除干扰 -->
</section>

<!-- 测试区域3b: 有波浪的版本 -->
<section class="page-hero-section" style="margin-top: 20px; border: 3px solid pink;">
    <div class="page-hero-bg" style="background-image: url('<?php echo esc_url($hero_bg_url); ?>');"></div>
    <div class="page-hero-overlay"></div>
    <div class="test-info">
        <strong>测试3b: 有波浪</strong><br>
        测试波浪是否影响背景图显示
    </div>
    <div class="waves-area">
        <svg class="waves-svg" xmlns="http://www.w3.org/2000/svg" viewBox="0 24 150 28" preserveAspectRatio="none">
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

<!-- 测试区域4: 使用isolation: isolate -->
<section class="test-hero-section-isolated">
    <div class="bg"></div>
    <div class="overlay"></div>
    <div class="test-info" style="z-index: 2;">
        <strong>测试4: isolation: isolate</strong><br>
        section设置isolation: isolate<br>
        背景图div: z-index: 0<br>
        遮罩层div: z-index: 1
    </div>
</section>

<!-- 调试信息区域 -->
<section style="padding: 20px; background: #f0f0f0; margin-top: 20px;">
    <h2>调试信息</h2>
    <pre>
主题版本: <?php echo BRAVE_VERSION; ?>

Hero背景图设置:
- get_theme_mod('brave_hero_bg'): <?php echo empty($hero_bg) ? '未设置(空)' : '已设置'; ?>
- 实际使用的URL: <?php echo esc_html($hero_bg_url); ?>

当前时间: <?php echo date('Y-m-d H:i:s'); ?>
    </pre>
    
    <h3>请检查以下几点：</h3>
    <ol>
        <li>上面3个测试区域，哪些能看到背景图片？</li>
        <li>浏览器开发者工具Console是否有报错？</li>
        <li>Network标签中背景图片是否成功加载（HTTP 200）？</li>
        <li>检查.page-hero-section的computed styles中的background-image</li>
    </ol>
</section>

<?php get_footer(); ?>
