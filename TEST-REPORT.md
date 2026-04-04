# Brave Love 1.0.5 Test Report

Generated: 2026-04-04  
Project: `brave-love`  
Tester: Codex CLI  
Environment: local WordPress Docker runtime + local PHP CLI

## Scope

This patch-release check focused on the new drag-and-drop sorting flow for homepage weather cities, the related admin assets, and the release metadata updates for `v1.0.5`.

This run covered:

- PHP syntax lint for the changed theme source
- admin weather page output verification for the drag handle and sortable initialization
- local homepage ordering verification against saved weather city order
- release metadata consistency for `style.css`, `functions.php`, `README.md`, `RELEASE.md`, and `CHANGELOG.md`

This run did not cover:

- browser-driven drag interaction in a real admin session
- full authenticated regression across other admin pages
- production data verification beyond the local test site

## Environment

- `php`: available
- `python3`: available
- `docker`: available
- local WordPress: `http://localhost:8080`
- local phpMyAdmin: `http://localhost:8081`
- theme runtime version target: `1.0.5`

## Review Findings And Fixes

### 1. Weather cities could not be reordered directly in admin

Risk:

- 首页天气展示顺序只能跟随保存数组，后台没有直接的可视化调整方式
- 用户想改顺序时只能依赖额外按钮或重新录入，维护体验较差

Fix:

- added drag handles to the weather city rows
- enabled `jQuery UI sortable` on the weather settings page
- kept the saved option order as the single source of truth for front-end rendering

### 2. Admin feedback needed to make sorting obvious

Risk:

- 仅增加排序能力而没有视觉引导，会让后台用户不知道哪里可以拖拽

Fix:

- added a clearer weather admin description explaining that table order controls homepage order
- styled the drag handle and placeholder state in `assets/css/admin.css`
- kept visible order numbers so the final order remains easy to confirm before saving

### 3. Documentation needed to match the new workflow

Risk:

- README 和用户手册如果仍然只写“添加城市”，会与实际后台操作不一致

Fix:

- updated `README.md` weather-management instructions
- updated `docs/USER-GUIDE.md` to mention drag sorting for weather cities
- bumped release metadata to `1.0.5`

## Executed Tests

### 1. PHP syntax lint

Command:

```bash
php -l functions.php
php -l inc/weather-admin.php
```

Result: passed.

Observed summary:

- changed PHP files linted successfully
- no syntax errors were introduced by the sortable admin changes

Status: passed.

### 2. Weather admin output verification

Checks executed:

- rendered the weather admin page inside the local WordPress runtime
- verified the output contains `city-drag-handle`
- verified the output contains `sortable({`
- verified the output contains `dashicons-move`

Observed summary:

- drag handle markup was present
- sortable initialization script was present
- drag icon output was present

Status: passed.

### 3. Local homepage order verification

Checks executed:

- temporarily changed local weather city option order to `深圳 -> 北京 -> 杭州`
- fetched `http://localhost:8080/` and checked the first three weather card city names
- restored the original local weather settings after verification

Observed summary:

- homepage rendered weather cities in the same order as the saved option array
- temporary local verification order matched `深圳 -> 北京 -> 杭州`
- original local option values were restored after the check

Status: passed.

### 4. Release metadata consistency

Checks executed:

- verified version metadata changed to `1.0.5` in `style.css` and `functions.php`
- verified release documentation references were aligned around `1.0.5`

Observed summary:

- theme runtime version and release files matched
- README, release notes, changelog, and test report were aligned

Status: passed.

## Outcome Summary

- Static PHP validation: passed
- Weather admin output verification: passed
- Homepage weather order verification: passed
- Release metadata consistency for `1.0.5`: passed

Current state is suitable for a `v1.0.5` patch release.
