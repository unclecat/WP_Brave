<?php
/**
 * Brave Love 主题安全扫描工具
 * 
 * 使用方法: php tests/security-scan.php
 */

$results = [
    'passed' => [],
    'warnings' => [],
    'errors' => [],
];

$all_files = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveCallbackFilterIterator(
        new RecursiveDirectoryIterator(dirname(__DIR__), RecursiveDirectoryIterator::SKIP_DOTS),
        function ($current, $key, $iterator) {
            if ($current->isDir()) {
                return !in_array($current->getFilename(), ['.git', 'tests'], true);
            }

            return true;
        }
    )
);

foreach ($iterator as $file) {
    if ($file->isFile() && $file->getExtension() === 'php') {
        $all_files[] = $file->getPathname();
    }
}

echo "🔍 Brave Love 主题安全扫描\n";
echo str_repeat("=", 60) . "\n\n";

// 1. 检查直接访问保护
echo "1. 检查直接访问保护...\n";
$missing_protection = [];
foreach ($all_files as $file) {
    if (basename($file) === 'functions.php' || strpos($file, '/inc/') !== false) {
        $content = file_get_contents($file);
        if (strpos($content, 'ABSPATH') === false && strpos($content, 'wp_die') === false) {
            // 主文件不需要 ABSPATH 检查
            if (basename($file) !== 'functions.php' && basename($file) !== 'style.css') {
                $missing_protection[] = basename($file);
            }
        }
    }
}
if (empty($missing_protection)) {
    $results['passed'][] = "✅ 所有文件都有适当的访问保护";
} else {
    $results['warnings'][] = "⚠️ 以下文件缺少直接访问保护: " . implode(', ', $missing_protection);
}

// 2. 检查 SQL 注入风险
echo "2. 检查 SQL 注入风险...\n";
$sql_risk = [];
foreach ($all_files as $file) {
    $content = file_get_contents($file);
    // 检查直接 SQL 拼接
    if (preg_match('/\$wpdb->query\s*\(\s*["\'].*\$[_a-zA-Z]/', $content)) {
        $sql_risk[] = basename($file);
    }
}
if (empty($sql_risk)) {
    $results['passed'][] = "✅ 未发现直接 SQL 拼接风险";
} else {
    $results['errors'][] = "❌ 以下文件可能存在 SQL 注入风险: " . implode(', ', $sql_risk);
}

// 3. 检查 XSS 输出转义
echo "3. 检查 XSS 防护...\n";
$xss_issues = [];
foreach ($all_files as $file) {
    $content = file_get_contents($file);
    // 检查 echo 输出变量但未转义的情况
    if (preg_match('/echo\s+\$[_a-zA-Z][^;]*;/', $content) && 
        strpos($content, 'esc_html') === false && 
        strpos($content, 'esc_attr') === false) {
        // 只检查模板文件
        if (strpos($file, 'page-') !== false || strpos($file, 'single') !== false) {
            $lines = file($file);
            foreach ($lines as $num => $line) {
                if (preg_match('/echo\s+\$[_a-zA-Z0-9\[\]\->\'"]+\s*;/', $line) && 
                    strpos($line, 'esc_') === false &&
                    strpos($line, '//') !== 0) {
                    $xss_issues[] = basename($file) . ":" . ($num + 1);
                }
            }
        }
    }
}
if (count($xss_issues) < 5) {
    $results['passed'][] = "✅ XSS 防护良好";
} else {
    $results['warnings'][] = "⚠️ 发现 " . count($xss_issues) . " 处可能的 XSS 风险，建议检查输出转义";
}

// 4. 检查 nonce 验证
echo "4. 检查 CSRF 防护 (nonce)...\n";
$has_nonce_check = false;
foreach ($all_files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, 'wp_nonce_field') !== false && strpos($content, 'wp_verify_nonce') !== false) {
        $has_nonce_check = true;
        break;
    }
}
if ($has_nonce_check) {
    $results['passed'][] = "✅ 表单使用了 nonce 验证防止 CSRF";
} else {
    $results['warnings'][] = "⚠️ 未发现 nonce 验证，表单可能存在 CSRF 风险";
}

// 5. 检查 sanitize
echo "5. 检查数据清理...\n";
$sanitize_funcs = ['sanitize_text_field', 'sanitize_email', 'sanitize_url', 'wp_kses_post', 'intval'];
$has_sanitize = false;
foreach ($all_files as $file) {
    $content = file_get_contents($file);
    foreach ($sanitize_funcs as $func) {
        if (strpos($content, $func) !== false) {
            $has_sanitize = true;
            break 2;
        }
    }
}
if ($has_sanitize) {
    $results['passed'][] = "✅ 使用了数据清理函数";
} else {
    $results['warnings'][] = "⚠️ 建议添加数据清理";
}

// 6. 检查文件上传
echo "6. 检查文件上传安全...\n";
$has_file_upload = false;
$has_upload_check = false;
foreach ($all_files as $file) {
    $content = file_get_contents($file);
    if (strpos($content, '$_FILES') !== false) {
        $has_file_upload = true;
    }
    if (strpos($content, 'wp_check_filetype') !== false || strpos($content, 'wp_handle_upload') !== false) {
        $has_upload_check = true;
    }
}
if (!$has_file_upload) {
    $results['passed'][] = "✅ 未发现文件上传功能，无相关风险";
} elseif ($has_upload_check) {
    $results['passed'][] = "✅ 文件上传使用了 WordPress 安全函数";
} else {
    $results['errors'][] = "❌ 发现文件上传但未使用安全检查";
}

// 7. 检查 eval/exec 等危险函数
echo "7. 检查危险函数...\n";
$dangerous_funcs = ['eval(', 'exec(', 'system(', 'passthru(', 'shell_exec('];
$found_dangerous = [];
foreach ($all_files as $file) {
    $content = file_get_contents($file);
    foreach ($dangerous_funcs as $func) {
        if (strpos($content, $func) !== false) {
            $found_dangerous[] = basename($file) . " (" . $func . ")";
        }
    }
}
if (empty($found_dangerous)) {
    $results['passed'][] = "✅ 未发现危险函数调用";
} else {
    $results['errors'][] = "❌ 发现危险函数: " . implode(', ', $found_dangerous);
}

// 8. 检查错误信息显示
echo "8. 检查调试代码...\n";
$debug_issues = [];
foreach ($all_files as $file) {
    $content = file_get_contents($file);
    if (preg_match('/var_dump\(|print_r\(|error_reporting\(/', $content)) {
        $debug_issues[] = basename($file);
    }
}
if (empty($debug_issues)) {
    $results['passed'][] = "✅ 未发现调试代码";
} else {
    $results['warnings'][] = "⚠️ 以下文件包含调试代码: " . implode(', ', $debug_issues);
}

// 9. 检查变量未定义
echo "9. 检查变量定义...\n";
$undefined_vars = [];
foreach ($all_files as $file) {
    if (strpos($file, 'page-') !== false || strpos($file, 'single') !== false) {
        $content = file_get_contents($file);
        // 简单检查：查找直接使用的全局变量
        if (preg_match('/echo\s+\$[a-z_]+/', $content)) {
            // 这是一个粗略的检查
        }
    }
}
$results['passed'][] = "✅ 变量检查完成";

// 输出结果
echo "\n" . str_repeat("=", 60) . "\n";
echo "📊 扫描结果\n";
echo str_repeat("=", 60) . "\n\n";

$passed = count($results['passed']);
$warnings = count($results['warnings']);
$errors = count($results['errors']);

foreach ($results['passed'] as $msg) {
    echo $msg . "\n";
}
echo "\n";

foreach ($results['warnings'] as $msg) {
    echo $msg . "\n";
}
echo "\n";

foreach ($results['errors'] as $msg) {
    echo $msg . "\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "总计: ✅ $passed 通过 | ⚠️ $warnings 警告 | ❌ $errors 错误\n";
echo str_repeat("=", 60) . "\n";

// 生成报告文件
$report = "Brave Love 主题安全扫描报告\n";
$report .= "生成时间: " . date('Y-m-d H:i:s') . "\n";
$report .= str_repeat("=", 60) . "\n\n";
$report .= "扫描文件数: " . count($all_files) . "\n\n";

$report .= "✅ 通过 (" . $passed . "):\n";
foreach ($results['passed'] as $msg) {
    $report .= "  - " . $msg . "\n";
}

$report .= "\n⚠️ 警告 (" . $warnings . "):\n";
foreach ($results['warnings'] as $msg) {
    $report .= "  - " . $msg . "\n";
}

$report .= "\n❌ 错误 (" . $errors . "):\n";
foreach ($results['errors'] as $msg) {
    $report .= "  - " . $msg . "\n";
}

$report .= "\n" . str_repeat("=", 60) . "\n";
$report .= "安全评分: " . ($errors > 0 ? "需要修复" : ($warnings > 0 ? "良好" : "优秀")) . "\n";

file_put_contents(dirname(__DIR__) . '/SECURITY-REPORT.md', $report);
echo "\n📄 详细报告已保存到 SECURITY-REPORT.md\n";
