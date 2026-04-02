# Brave Love Test Report

Generated: 2026-04-02
Project: `brave-love`
Tester: Codex CLI
Environment: local WordPress Docker runtime + local PHP CLI

## Scope

This run covered:

- full PHP syntax linting for theme source
- bundled theme structure and metadata checks
- bundled security scan
- local WordPress HTTP smoke tests for the main templates
- love-list filter / canonical / redirect verification
- release artifact documentation refresh

This run did not cover:

- real browser-driven visual regression
- authenticated end-to-end publishing flows in a browser
- external weather API correctness in live network conditions

## Environment

- `php`: available
- `docker`: available
- local WordPress: available at `http://localhost:8080`
- local phpMyAdmin: available at `http://localhost:8081`
- theme runtime version: `0.7.7`

## Review Findings And Fixes

### 1. Direct access hardening

Risk:

- multiple template files and `inc/` modules lacked direct-access guards
- direct requests to theme PHP files could expose partial internals or trigger avoidable execution paths

Fix:

- added `ABSPATH` guards to theme templates, template parts, and `inc/` core modules

### 2. Input normalization consistency

Risk:

- several front-end and admin handlers read `$_GET` / `$_POST` values without `wp_unslash`
- this could store slash-escaped content or create inconsistent behavior under WordPress request handling

Fix:

- normalized key request reads with `wp_unslash` + sanitization in front-end note publishing, page filters, meta box saves, weather admin, gallery admin, and anniversary admin

### 3. Runtime notice hardening

Risk:

- `brave_is_mobile()` assumed `$_SERVER['HTTP_USER_AGENT']` always exists
- CLI or proxy/headless requests could trigger PHP notices

Fix:

- added safe fallback handling for missing user agent values

## Executed Tests

### 1. PHP syntax lint

Command:

```bash
find . -name '*.php' -not -path './tests/*' -print0 | xargs -0 -n1 php -l
```

Result: passed.

Coverage:

- `functions.php`
- theme templates and template parts
- `inc/` helper and admin modules
- archive and single templates

Status: passed.

### 2. Bundled shell smoke check

Command:

```bash
bash tests/check-theme-simple.sh
```

Result: passed.

Observed summary:

- required theme files: complete
- theme name: `Brave Love`
- version: `0.7.7`
- dangerous runtime functions: none found
- text domain usage: detected and consistent

Status: passed.

### 3. Bundled PHP check

Command:

```bash
php tests/check-theme.php
```

Result: passed.

Observed summary:

- all required files present
- all scanned PHP files linted successfully
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

### 5. Local WordPress runtime smoke tests

Checks executed against `http://localhost:8080`:

- home page entry cards render and link to all major sections
- moments page renders hero, dropdown filter, year badges, and timeline cards
- memories page renders dropdown filter, gallery waterfall, and PhotoSwipe info shell
- notes page renders dropdown filter and note card structure
- blessing page renders blessing card grid and submission form structure
- love-list page renders canonical tag, status filter links, and all 11 cards on one page

Status: passed.

### 6. Love-list behavior verification

Verified via local HTTP:

- `filter_status=done` only returns completed cards and emits canonical `.../lists/?filter_status=done`
- `filter_status=pending` only returns pending cards and emits canonical `.../lists/?filter_status=pending`
- legacy pagination URL `/lists/page/2/` returns `302` to `/lists/`
- invalid query URL `/lists/?filter_status=bad&foo=1` returns `302` to `/lists/`

Status: passed.

## Outcome Summary

- Static PHP validation: passed
- Theme structure check: passed
- Security scan: passed
- Runtime page smoke tests: passed
- Love-list filtering and canonical cleanup: passed
- Release docs refresh: completed

## Remaining Risks

- no screenshot-based or browser automation regression coverage yet
- weather data still depends on external runtime API responses
- visual polish still relies on manual browser review across actual devices

## Recommended Release State

Current state is suitable for a patch release.

Recommended release version: `v0.7.7`
