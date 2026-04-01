<?php
/**
 * 主题代码规范检查脚本
 * 运行: php check-theme.php
 */

$theme_dir = dirname(__DIR__);
$errors = [];
$warnings = [];

echo "🔍 Brave Love 主题代码检查\n";
echo str_repeat("=", 50) . "\n\n";

// 检查必需文件
$required_files = [
    'style.css',
    'index.php',
    'functions.php',
    'header.php',
    'footer.php',
    'single.php',
    'archive.php',
    'search.php',
    '404.php',
    'inc/post-types.php',
    'inc/meta-boxes.php',
    'inc/customizer.php',
    'page-templates/page-home.php',
];

echo "📁 检查必需文件...\n";
foreach ($required_files as $file) {
    if (!file_exists("$theme_dir/$file")) {
        $errors[] = "缺少文件: $file";
    }
}
if (empty($errors)) {
    echo "✅ 所有必需文件都存在\n";
}
echo "\n";

// 检查 style.css 头信息
echo "🎨 检查 style.css...\n";
$style_css = file_get_contents("$theme_dir/style.css");
$required_headers = [
    'Theme Name:',
    'Version:',
    'Description:',
    'Author:',
    'License:',
];
foreach ($required_headers as $header) {
    if (strpos($style_css, $header) === false) {
        $errors[] = "style.css 缺少: $header";
    }
}
if (preg_match('/Version:\s*(.+)/i', $style_css, $matches)) {
    echo "   版本: " . trim($matches[1]) . "\n";
}
if (preg_match('/Theme Name:\s*(.+)/i', $style_css, $matches)) {
    echo "   主题名: " . trim($matches[1]) . "\n";
}
echo "\n";

// 检查 PHP 语法
echo "🔍 检查 PHP 语法...\n";
$php_files = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator($theme_dir, RecursiveDirectoryIterator::SKIP_DOTS),
        function ($current, $key, $iterator) {
            if ($current->isDir()) {
                return !in_array($current->getFilename(), ['.git', 'tests'], true);
            }

            return true;
        }
    )
);
$php_count = 0;
foreach ($php_files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $php_count++;
        $output = [];
        $return_var = 0;
        exec('php -l ' . escapeshellarg($file->getPathname()) . ' 2>&1', $output, $return_var);
        if ($return_var !== 0) {
            $errors[] = "语法错误: " . $file->getRelativePathname();
            echo "❌ " . $file->getRelativePathname() . "\n";
        }
    }
}
echo "✅ 检查了 $php_count 个 PHP 文件\n\n";

// 检查安全问题
echo "🔒 安全检查...\n";
$dangerous_functions = [
    'eval(',
    'exec(',
    'system(',
    'shell_exec(',
    'passthru(',
    'base64_decode(',
];

foreach ($php_files as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $content = file_get_contents($file->getPathname());
        foreach ($dangerous_functions as $func) {
            if (strpos($content, $func) !== false) {
                $warnings[] = $file->getRelativePathname() . " 使用了 $func";
            }
        }
    }
}
echo "✅ 安全扫描完成\n\n";

// 检查文本域
echo "🌍 检查国际化...\n";
if (strpos($style_css, 'Text Domain:') === false) {
    $warnings[] = "style.css 缺少 Text Domain";
}
echo "✅ 国际化检查完成\n\n";

// 统计代码行数
echo "📊 代码统计...\n";
$total_lines = 0;
$file_types = ['php' => 0, 'css' => 0, 'js' => 0];
foreach ($php_files as $file) {
    if ($file->isFile()) {
        $ext = $file->getExtension();
        if (isset($file_types[$ext])) {
            $lines = count(file($file->getPathname()));
            $total_lines += $lines;
            $file_types[$ext] += $lines;
        }
    }
}
echo "   PHP 行数: " . number_format($file_types['php']) . "\n";
echo "   CSS 行数: " . number_format($file_types['css']) . "\n";
echo "   JS 行数:  " . number_format($file_types['js']) . "\n";
echo "   总行数:   " . number_format($total_lines) . "\n";
echo "\n";

// 输出结果
echo str_repeat("=", 50) . "\n";
echo "📋 检查结果\n";
echo str_repeat("=", 50) . "\n";

if (!empty($errors)) {
    echo "\n❌ 错误 (" . count($errors) . "):\n";
    foreach ($errors as $error) {
        echo "   - $error\n";
    }
}

if (!empty($warnings)) {
    echo "\n⚠️  警告 (" . count($warnings) . "):\n";
    foreach ($warnings as $warning) {
        echo "   - $warning\n";
    }
}

if (empty($errors) && empty($warnings)) {
    echo "\n✅ 所有检查通过！主题代码规范良好。\n";
    exit(0);
} elseif (empty($errors)) {
    echo "\n✅ 没有错误，但有警告需要关注。\n";
    exit(0);
} else {
    echo "\n❌ 存在错误，请修复后再发布。\n";
    exit(1);
}
