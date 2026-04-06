# Brave Love 1.0.6 Test Report

Generated: 2026-04-06  
Project: `brave-love`  
Tester: Codex CLI  
Environment: local WordPress Docker runtime + local PHP CLI

## Scope

This patch-release check focused on the new ordering behavior for the `love_list` archive and the matching default ordering in the admin `love_list` table for `v1.0.6`.

This run covered:

- PHP syntax lint for the changed theme source
- local front-end ordering verification for the love list archive query
- local admin ordering verification for the default love list management query
- explicit admin `orderby` override verification
- release metadata consistency for `style.css`, `functions.php`, `README.md`, `RELEASE.md`, and `CHANGELOG.md`

This run did not cover:

- browser-driven authenticated admin UI checks
- full regression across unrelated templates or settings pages
- production data validation beyond the local test site

## Environment

- `php`: available
- `docker`: available
- local WordPress: `http://localhost:8080`
- local phpMyAdmin: `http://localhost:8081`
- theme runtime version target: `1.0.6`

## Review Findings And Fixes

### 1. Love list archive lacked a user-oriented default order

Risk:

- 前台恋爱清单没有明确的完成状态优先级时，待完成事项会被已完成事项打散
- 用户打开页面后，最想先看的“还没完成的事”不一定能排在最前面

Fix:

- added a unified love-list ordering clause in `functions.php`
- grouped pending items first and done items second
- sorted done items by `_done_date` descending with publish date as a fallback

### 2. Admin maintenance order did not match the front end

Risk:

- 后台列表和前台展示顺序不一致，会增加维护时的判断成本
- 站长在后台处理事项时，需要额外筛选才能先看到待完成项

Fix:

- applied the same default ordering rule to the admin `love_list` list table
- limited the admin hook to the default `edit.php?post_type=love_list` main query
- preserved explicit admin sorting when the user clicks another sortable column

### 3. Release metadata needed to reflect the new patch release

Risk:

- 版本号和发布说明如果不更新，会让安装包、GitHub 项目页和实际代码状态不一致

Fix:

- bumped release metadata to `1.0.6`
- updated `README.md`, `docs/USER-GUIDE.md`, `RELEASE.md`, and `CHANGELOG.md`

## Executed Tests

### 1. PHP syntax lint

Command:

```bash
php -l functions.php
```

Result: passed.

Observed summary:

- `functions.php` linted successfully
- no syntax errors were introduced by the shared sorting helper or admin hook

Status: passed.

### 2. Front-end love list ordering verification

Checks executed:

- loaded the local WordPress runtime inside Docker
- executed the archive query with the theme hook applied
- verified pending items render first
- verified done items render after pending items and are ordered by `_done_date` descending

Observed summary:

- pending items were grouped first
- done items were ordered `2025-04-01 -> 2025-03-20 -> 2025-03-01 -> 2025-02-14`
- items sharing the same done date fell back to publish date ordering

Status: passed.

### 3. Admin default ordering verification

Checks executed:

- simulated the `edit.php?post_type=love_list` admin screen in the local runtime
- executed the default main query with the new admin hook applied
- compared the resulting order with the front-end archive order

Observed summary:

- admin default order matched the front-end logic
- pending items appeared first and done items followed by done date descending

Status: passed.

### 4. Admin explicit orderby override verification

Checks executed:

- simulated `orderby=title&order=ASC` for the admin love list screen
- verified the custom default sort flag was not enabled
- confirmed the returned results followed title ordering instead of the default status/date ordering

Observed summary:

- explicit admin sorting was preserved
- the default hook did not override user-selected ordering

Status: passed.

## Conclusion

Current state is suitable for a `v1.0.6` patch release.
