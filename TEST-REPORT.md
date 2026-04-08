# Brave Love 1.0.9 Test Report

Generated: 2026-04-08
Project: `brave-love`
Tester: Codex CLI
Environment: local WordPress Docker runtime + local PHP CLI

## Scope

This patch-release check focused on the new backend QWeather credential configuration flow for `v1.0.9`.

This run covered:

- PHP syntax lint for the changed weather service and weather admin files
- full PHP syntax lint for the theme
- front-end JavaScript syntax check
- local static theme checklist and security scan
- local weather REST endpoint verification
- local option persistence verification for backend-stored QWeather host/key values
- release metadata consistency for `style.css`, `functions.php`, `README.md`, `RELEASE.md`, and `CHANGELOG.md`

This run did not cover:

- authenticated manual clicking inside the WordPress admin screen
- full browser-driven regression across all templates
- production environment verification

## Environment

- `php`: available
- `node`: available
- `docker`: available
- local WordPress: `http://localhost:8080`
- local phpMyAdmin: `http://localhost:8081`
- theme runtime version target: `1.0.9`

## Review Findings And Fixes

### 1. Added backend-stored QWeather credential fallback

Risk:

- 原实现只能从 `wp-config.php` 或环境变量读取 QWeather 配置
- 对不想改服务器文件的站点，日常维护成本偏高

Fix:

- added backend option storage for `QWeather API Host` and `QWeather API Key`
- updated config resolution order to prefer server config and fall back to admin-saved values

### 2. Improved weather settings page status feedback

Risk:

- 管理员在后台无法判断当前到底是服务器配置生效，还是后台保存值生效
- 已保存 Key 的维护方式也不够直观

Fix:

- added config source labels for Host and Key
- added masked key behavior, keep-existing behavior, and clear-key support

### 3. Refreshed caches after weather setting changes

Risk:

- 修改天气凭证或城市后，旧缓存可能继续生效一段时间
- 管理员会误以为新配置没有生效

Fix:

- added weather cache flush on credential or city updates
- refreshed in-memory config cache after saving

## Executed Tests

### 1. PHP syntax lint

Commands:

```bash
php -l inc/weather-service.php
php -l inc/weather-admin.php
find . -name '*.php' -not -path './tests/wordpress/*' -print0 | xargs -0 -n1 php -l
```

Result: passed.

Observed summary:

- both changed files linted successfully
- full theme PHP lint completed without syntax errors

Status: passed.

### 2. Front-end JavaScript syntax check

Command:

```bash
node --check assets/js/brave.js
```

Result: passed.

Status: passed.

### 3. Theme checklist and security scan

Commands:

```bash
bash tests/check-theme-simple.sh
php tests/check-theme.php
php tests/security-scan.php
```

Result: passed.

Observed summary:

- required files remained present
- metadata remained valid
- security scan reported no warnings and no errors

Status: passed.

### 4. Weather REST verification

Checks executed:

- requested `http://localhost:8080/wp-json/brave-love/v1/weather`
- verified the endpoint still returned configured weather payloads after the config fallback change

Observed summary:

- provider remained `qweather`
- payload remained configured and returned the expected city list

Status: passed.

### 5. Backend option persistence verification

Checks executed:

- wrote temporary backend `QWeather API Host` and `QWeather API Key` values into local WordPress options
- verified the stored values could be read back by the theme helpers
- verified the current local environment still preferred server-level constants when both existed
- cleaned up the temporary test values afterwards

Observed summary:

- backend-stored host and key values were saved successfully
- helper functions read the stored values successfully
- source resolution correctly remained `server` in the local environment because `wp-config.php` constants are currently present

Status: passed.

## Overall Assessment

Release candidate `v1.0.9` is ready for tagging.

Residual risk is low and mainly limited to authenticated manual admin-page clicking, which was not part of this run.
