# Brave Love 1.1.2 Test Report

Generated: 2026-04-09
Project: `brave-love`
Tester: Codex CLI
Environment: local WordPress runtime + local PHP CLI

## Scope

This release-candidate check focused on the `v1.1.2` maintenance release.

This run covered:

- full PHP syntax lint for all theme PHP files
- JavaScript syntax checks for `assets/js/brave.js`, `assets/js/admin.js`, and `assets/js/memory.js`
- static theme structure check and security scan
- review of the weather snapshot diff for `inc/weather-service.php`, `inc/weather/rest.php`, and `inc/weather/snapshot.php`
- local front-end smoke test for homepage, about page, and moments page
- homepage weather REST cache verification and browser screenshot regression for desktop / mobile
- release metadata consistency for `style.css`, `functions.php`, `README.md`, `CHANGELOG.md`, `RELEASE.md`, and `TEST-REPORT.md`

This run did not cover:

- authenticated manual clicking inside the WordPress admin screen
- authenticated manual clicking inside the WordPress admin screen
- production environment verification

## Environment

- `php`: available
- `node`: available
- local WordPress runtime: available
- theme runtime version target: `1.1.2`

## Review Summary

This round did not find a P0 / P1 release blocker.

Checked重点：

- 首页天气 REST 已增加整包快照缓存，重复请求会优先命中最近 5 分钟的成功结果
- 当本轮天气重新拉取失败但服务器已有旧快照时，会回退到上一份成功结果，而不是直接让首页天气整块报错
- 新增的 `inc/weather/snapshot.php` 已接入天气服务入口，require 链路和 PHP lint 正常
- 首页在桌面端和手机端的浏览器截图里，天气卡片都能完成异步加载并正常显示

## Executed Tests

### 1. PHP syntax lint

Command:

```bash
find . -name '*.php' -print0 | xargs -0 -n1 php -l
```

Result: pass. No syntax errors detected in theme PHP files.

### 2. JavaScript syntax checks

Commands:

```bash
node --check assets/js/brave.js
node --check assets/js/admin.js
node --check assets/js/memory.js
```

Result: pass. No syntax errors reported.

### 3. Static theme checklist

Commands:

```bash
php tests/check-theme.php
bash tests/check-theme-simple.sh
```

Result: pass. Required files exist, metadata is present, and theme structure is valid.

### 4. Security scan

Command:

```bash
php tests/security-scan.php
```

Result: pass. Report summary shows `9` checks passed, `0` warnings, `0` errors.

### 5. Diff sanity check

Command:

```bash
git diff --check
```

Result: pass. No whitespace or patch formatting issues detected.

### 6. Local page smoke test

Commands:

```bash
curl http://127.0.0.1:8080/
curl http://127.0.0.1:8080/about-us/
curl http://127.0.0.1:8080/%E7%82%B9%E7%82%B9%E6%BB%B4%E6%BB%B4/
```

Result: pass. Local homepage, about page, and moments page all returned expected HTML and loaded `theme-core.css` + `brave.css`.

### 7. Weather REST snapshot verification

Commands:

```bash
curl http://127.0.0.1:8080/wp-json/brave-love/v1/weather
curl http://127.0.0.1:8080/wp-json/brave-love/v1/weather
```

Result: pass. Repeated requests returned the same `generatedAt` timestamp within the snapshot window, confirming the homepage weather payload is served from the new 5-minute server-side snapshot cache.

### 8. Browser screenshot regression

Commands:

```bash
Google Chrome --headless --virtual-time-budget=6000 http://127.0.0.1:8080/
Google Chrome --headless --virtual-time-budget=6000 --window-size=430,932 http://127.0.0.1:8080/
```

Result: pass. Desktop and mobile homepage screenshots rendered correctly, and the weather cards completed asynchronous loading in both layouts.

## Conclusion

Release candidate `v1.1.2` is ready for tagging.
