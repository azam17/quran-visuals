# Agent Instructions for Quran Visuals

## Before Making Changes
1. Read `CLAUDE.md` for project context and conventions.
2. This project uses **inline CSS** — never introduce Tailwind or CSS frameworks.
3. Check `config/quran.php` for configuration values before hardcoding anything.

## Code Style
- **PHP:** Follow existing Laravel conventions. No docblocks unless logic is non-obvious.
- **Views:** Blade templates with inline `<style>` blocks. Match the dark theme variables.
- **JavaScript:** Vanilla JS in `<script>` tags. No npm frontend frameworks.
- **CSS vars:** `--bg-1`, `--bg-2`, `--bg-3`, `--accent`, `--accent-2`, `--text`, `--muted`.

## When Adding New Pages
- Extend `layouts/feedback.blade.php` for feedback/roadmap/auth pages.
- `player.blade.php` is standalone (no layout) — do not refactor it into the layout system.
- Use `@section('extra-css')` for page-specific styles.
- Match existing card/button/form styling from `layouts/feedback.blade.php`.

## When Modifying the Database
- Create a new migration, never edit existing ones.
- Use `string` columns for status/type fields, never `enum`.
- Run migrations on the server after pushing:
  ```bash
  ssh diecasthub "cd ~/quran-visuals.on-forge.com/current && php artisan migrate --force"
  ```

## When Deploying
- Push to `master` branch on `azam17/quran-visuals`.
- Forge auto-deploys or trigger manually.
- Server env is separate — update via SSH if needed:
  ```bash
  ssh diecasthub "nano /home/forge/quran-visuals.on-forge.com/.env"
  ```

## Status Values for FeedbackItem
Use model constants, not raw strings:
```php
FeedbackItem::STATUS_UNDER_REVIEW  // 'under_review'
FeedbackItem::STATUS_PLANNED       // 'planned'
FeedbackItem::STATUS_IN_PROGRESS   // 'in_progress'
FeedbackItem::STATUS_DONE          // 'done'
```

## Admin Check
Admin is email-based via `config('quran.admin_email')`. Use `$user->isAdmin()` or the `admin` middleware alias. Do not add role columns or permission packages.
