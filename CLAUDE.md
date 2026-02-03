# Quran Visuals

## Project Overview
Cinematic audio-reactive visualization player for Quran recitations, with a community feature request board and public roadmap. Built with Laravel 11, Blade templates, and inline CSS (no Tailwind/build step).

## Tech Stack
- **Backend:** Laravel 11, PHP 8.2+
- **Frontend:** Blade templates, inline CSS, vanilla JavaScript
- **Fonts:** Cinzel (serif headings), Inter (sans-serif body)
- **Theme:** Dark with CSS custom properties (--bg-1 through --bg-3, --accent, --accent-2, --text, --muted)
- **Database:** MySQL (production), SQLite (local)
- **Mail:** Gmail SMTP for admin notifications

## Deployment

### Server
Hosted on Laravel Forge at `quran-visuals.on-forge.com` on shared server `144.126.242.199`.

```bash
# SSH alias
ssh diecasthub

# Site path (zero-downtime deployment)
/home/forge/quran-visuals.on-forge.com/current

# Run artisan commands on server
ssh diecasthub "cd ~/quran-visuals.on-forge.com/current && php artisan migrate --force"
```

Always run database/artisan commands yourself via SSH. Do NOT tell the user to run them manually.

### Database
- **Name:** `quran_visuals`
- **User:** `forge`
- **Deploy command must use:** `php artisan migrate --force` (NOT `migrate:fresh`)

### GitHub
Repository: `azam17/quran-visuals` on branch `master`.

## Architecture

### No Build Step
The app uses inline `<style>` and `<script>` tags in Blade views. There is no Tailwind, no Vite CSS processing for the frontend. All styling is hand-written CSS with CSS custom properties for theming.

### Key Directories
```
app/
  Http/Controllers/
    Auth/               # Hand-rolled login/register (no Breeze/Jetstream)
    AdminFeedbackController.php
    FeedbackController.php
    PlayerController.php
    RoadmapController.php
  Http/Middleware/
    EnsureUserIsAdmin.php   # Checks email === config('quran.admin_email')
  Mail/
    NewFeedbackSubmitted.php
  Models/
    FeedbackItem.php    # Status constants: under_review, planned, in_progress, done
    FeedbackVote.php
    User.php            # isAdmin() method
  Services/
    QuranUrlInspector.php
config/
  quran.php             # admin_email, keywords, presets, yt-dlp config
resources/views/
  layouts/feedback.blade.php  # Shared layout for feedback/roadmap/auth pages
  player.blade.php            # Main visualizer (standalone, no layout)
  auth/                       # login, register
  feedback/                   # index, create, show
  roadmap/                    # index
  emails/                     # new-feedback notification
```

### Auth System
Hand-rolled authentication (no Laravel Breeze) to avoid Tailwind conflicts with the inline-CSS dark theme. Session-based with remember-me support.

### Admin System
Admin is determined by email match against `config('quran.admin_email')` (env: `ADMIN_EMAIL`). The `admin` middleware alias is registered in `bootstrap/app.php`. Admin can change feedback status, post responses, and delete items.

### Feedback & Voting
- Users submit feature requests (title + description)
- Toggle-vote system (one vote per user per item)
- Voted IDs preloaded via single query to avoid N+1
- Sort: new (created_at), top (votes_count), trending (votes in last 30 days)
- Search: LIKE on title + description

### Email Notifications
New feedback submissions trigger an email to the admin via `NewFeedbackSubmitted` mailable. Uses Gmail SMTP.

## Config: config/quran.php
- `admin_email` — admin user email (from ADMIN_EMAIL env)
- `keywords` / `blocked_keywords` — Quran content validation
- `presets` — visual effect presets with CSS vars and layer definitions
- `yt_dlp_binary` — path to yt-dlp

## Styling Conventions
- All pages use the same dark theme CSS variables
- Accent color: gold (#c28b3b)
- Cards: `border-radius: 14px`, `border: 1px solid rgba(255,255,255,0.08)`, `background: rgba(8,9,12,0.5)`
- Buttons: `.btn` base class, `.btn-primary` for gold gradient, `.btn-danger` for red
- Status badges: colored by status (muted=under_review, blue=planned, gold=in_progress, green=done)

## Common Gotchas
1. **No Tailwind** — do not add Tailwind classes. Use inline styles or `<style>` blocks.
2. **Database name** — `quran_visuals` (underscore), not `quran-visuals` (MySQL doesn't allow unquoted hyphens).
3. **Zero-downtime deploy** — site lives in `current/` symlink, not the root site directory.
4. **`.env` is gitignored** — server env must be edited via SSH or Forge UI.
