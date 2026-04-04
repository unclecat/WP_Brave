# Brave Love 1.0.3 Test Report

Generated: 2026-04-04  
Project: `brave-love`  
Tester: Codex CLI  
Environment: local WordPress Docker runtime + local PHP CLI

## Scope

This patch-release check focused on the moment summary migration to native WordPress excerpts, the safe cleanup of duplicate summary entry points, and the related release metadata updates.

This run covered:

- full PHP syntax linting for changed theme source
- bundled theme structure and metadata checks
- bundled security scan
- local WordPress runtime verification for the moments page and homepage timer
- local migration verification for `_moment_summary` -> `post_excerpt` backfill and legacy cleanup
- release metadata verification for `style.css`, `functions.php`, `README.md`, `RELEASE.md`, and `CHANGELOG.md`

This run did not cover:

- browser-driven click-through regression for every authenticated admin flow
- archive packaging on a second clean machine
- full production-data migration rehearsal beyond the local test dataset

## Environment

- `php`: available
- `python3`: available
- `docker`: available
- local WordPress: `http://localhost:8080`
- local phpMyAdmin: `http://localhost:8081`
- theme runtime version: `1.0.3`

## Review Findings And Fixes

### 1. Moment summaries had duplicated data sources

Risk:

- 点点滴滴同时保留了原生摘要能力和自定义 `_moment_summary` 字段，后续维护容易混淆来源
- 列表页、详情页、相册摘要如果继续直接读旧字段，会阻碍后续数据收口

Fix:

- added a shared `brave_get_moment_summary()` helper that prefers native excerpts and falls back to the legacy meta during migration
- updated moments list, single moment, and gallery summary building to use the shared helper
- removed the duplicate custom summary input from the moment meta box so future editing stays in the editor excerpt panel

### 2. Existing sites needed a safe migration path

Risk:

- 直接切到原生摘要会让已有 `_moment_summary` 数据在前台消失
- 粗暴删除旧字段可能误伤已人工编辑过的原生摘要

Fix:

- added an admin migration tool under `点点滴滴 -> 摘要迁移`
- backfill only copies old summaries into `post_excerpt` when the native excerpt is currently empty
- cleanup only deletes legacy `_moment_summary` rows when they exactly match the native excerpt

### 3. Minor safe-cleanup regressions were still possible

Risk:

- 首页计时器如果直接读取新配置键，旧站点可能误提示未设置
- 未使用短代码和天气后台外链会继续增加维护噪音与后台外链暴露面

Fix:

- unified the homepage timer data source around `brave_get_love_start_datetime()`
- removed the unused shortcode module
- replaced the weather admin external-doc link with internal plain-text instructions

## Executed Tests

### 1. PHP syntax lint

Command:

```bash
php -l functions.php
php -l inc/helpers.php
php -l inc/meta-boxes.php
php -l inc/moment-excerpt-migration.php
php -l inc/weather-admin.php
php -l page-templates/page-home.php
php -l page-templates/page-moments.php
php -l single-moment.php
```

Result: passed.

Observed summary:

- all changed PHP files linted successfully
- no syntax errors were introduced by the summary migration or safe cleanup changes

Status: passed.

### 2. Bundled shell structure check

Command:

```bash
bash tests/check-theme-simple.sh
```

Result: passed.

Observed summary:

- required theme files: complete
- theme name: `Brave Love`
- version metadata: readable
- dangerous runtime functions: none found

Status: passed.

### 3. Bundled PHP project check

Command:

```bash
php tests/check-theme.php
```

Result: passed.

Observed summary:

- all required files present
- scanned PHP files linted successfully
- style header fields present and readable
- no dangerous runtime function warnings

Status: passed.

### 4. Security scan

Command:

```bash
php tests/security-scan.php
```

Result: passed.

Observed summary:

- direct access protection: passed
- SQL injection scan: passed
- XSS scan: passed
- nonce presence: passed
- dangerous function scan: passed
- warnings: `0`
- errors: `0`

Status: passed.

### 5. Local moment summary migration verification

Checks executed:

- queried migration stats before backfill
- executed `brave_run_moment_excerpt_backfill()` in the local WordPress runtime
- re-checked stats after backfill
- executed `brave_cleanup_legacy_moment_summaries()` in the local WordPress runtime
- re-checked final stats and database row counts

Observed summary:

- before backfill: `23` moments had legacy summaries and `0` had native excerpts
- backfill migrated `23` summaries into `post_excerpt`
- cleanup removed `23` legacy `_moment_summary` rows
- final state: `0` legacy summaries remained and `23` native excerpts were present

Status: passed.

### 6. Local front-end verification

Checks executed:

- fetched `http://localhost:8080/?page_id=5` and confirmed moments cards still render summary blocks after migration
- fetched `http://localhost:8080/` and confirmed the homepage timer still shows a valid start time
- confirmed the homepage weather city cards continue to render after the safe-cleanup changes

Observed summary:

- moments timeline excerpts continued to render normally after migration
- homepage timer displayed `2023-05-20 12:00` in the local dataset
- homepage weather city list remained intact

Status: passed.

## Outcome Summary

- Static PHP validation: passed
- Theme structure check: passed
- Security scan: passed
- Moment summary migration and cleanup flow: passed
- Front-end rendering after migration: passed
- Release metadata consistency for `1.0.3`: passed

Current state is suitable for a `v1.0.3` patch release.
