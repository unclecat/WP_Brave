# Brave Love 1.0.8 Test Report

Generated: 2026-04-08
Project: `brave-love`  
Tester: Codex CLI  
Environment: local WordPress Docker runtime + local PHP CLI + local headless browser

## Scope

This patch-release check focused on the homepage weather stack for `v1.0.8`, especially the recent modal UI polish, stale-state cleanup, and release-readiness validation.

This run covered:

- PHP syntax lint for the changed theme source and the full PHP file set
- JavaScript syntax check for the changed front-end bundle
- local security scan and theme checklist scripts
- local weather REST endpoint verification
- desktop and mobile browser-based smoke check for the homepage weather modal
- release metadata consistency for `style.css`, `functions.php`, `README.md`, `RELEASE.md`, and `CHANGELOG.md`

This run did not cover:

- authenticated WordPress admin interaction flows
- full visual regression for all six front-end templates
- dark-mode-specific manual review
- production data validation beyond the local test site

## Environment

- `php`: available
- `node`: available
- `docker`: available
- local WordPress: `http://localhost:8080`
- local phpMyAdmin: `http://localhost:8081`
- theme runtime version target: `1.0.8`

## Review Findings And Fixes

### 1. Removed the unused minutely weather chain

Risk:

- 首页天气弹窗已经不再展示分钟降雨，但后端仍在继续请求 `minutely`
- 这会额外增加每个城市的一次 API 请求，并可能让旧链路继续影响 `stale` 状态

Fix:

- removed the `minutely` request in `inc/weather-service.php`
- removed the returned `minutely` payload
- removed the stale-state dependency on the retired minute-rain response

### 2. Restored 4-decimal coordinate precision for QWeather requests

Risk:

- 后台保存的是 4 位经纬度，但请求前被裁成 2 位小数
- 这会降低天气、空气质量和降雨数据的定位精度

Fix:

- updated weather and air endpoint coordinate formatting to 4 decimal places
- kept existing saved city data compatible with no migration required

### 3. Cleaned up dead CSS after the health block removal

Risk:

- 首页天气弹窗的“健康提醒”模块已经移除，但样式文件中仍保留了整套相关选择器
- 这些残留样式会增加维护噪音，也容易误导后续迭代

Fix:

- removed `.weather-modal-health*` related CSS
- removed matching mobile and dark-theme residual selectors

## Executed Tests

### 1. Full PHP syntax lint

Command:

```bash
find . -name '*.php' -not -path './tests/wordpress/*' -print0 | xargs -0 -n1 php -l
```

Result: passed.

Observed summary:

- all theme PHP files linted successfully
- no syntax errors were introduced by the weather service cleanup or modal template changes

Status: passed.

### 2. Front-end JavaScript syntax check

Command:

```bash
node --check assets/js/brave.js
```

Result: passed.

Observed summary:

- `assets/js/brave.js` parsed successfully
- no syntax regressions were introduced by the modal rendering updates

Status: passed.

### 3. Theme checklist script

Command:

```bash
bash tests/check-theme-simple.sh
php tests/check-theme.php
```

Result: passed.

Observed summary:

- required theme files were present
- theme metadata remained valid
- local static checks completed without errors

Status: passed.

### 4. Security scan

Command:

```bash
php tests/security-scan.php
```

Result: passed.

Observed summary:

- direct access protection, nonce usage, sanitization, and dangerous-function checks all passed
- no warnings or errors were reported by the local security scan

Status: passed.

### 5. Weather REST verification

Checks executed:

- requested `http://localhost:8080/wp-json/brave-love/v1/weather`
- verified the first city payload after the cleanup
- confirmed `minutely` was no longer present

Observed summary:

- the endpoint returned valid weather data
- `stale` was `false` for the sampled city
- `airDailyForecast` and `precipitationMax` were present
- `minutely` was no longer included in the payload

Status: passed.

### 6. Browser-based homepage weather modal smoke check

Checks executed:

- opened the local homepage in a headless browser
- opened the first weather card modal in desktop viewport
- opened the first weather card modal in mobile viewport
- captured screenshots and checked the modal for hidden overflow

Observed summary:

- desktop modal layout rendered normally
- mobile modal layout rendered normally
- reminder copy, tags, and weather metrics wrapped correctly
- no hidden overflow was detected in the checked modal elements

Status: passed.

## Overall Assessment

Release candidate `v1.0.8` is ready for tagging.

Residual risk is low and mainly limited to areas not covered in this run, such as dark-mode visual review and broader regression across unrelated pages.
