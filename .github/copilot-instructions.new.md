# Copilot / AI Agent Instructions for this repo

Purpose

- Help AI coding agents quickly make safe, small changes and find integration points in this PHP + frontend admin panel.

Big picture

- This repository is a PHP-based admin panel with a chat subsystem (Groq API integration). Primary backend lives under `src/php/` (not a purely static site). There are legacy scripts in `PHP/` and static previews in `public/` and `src/html/`.
- Key backend: `src/php/sistema.php` (API endpoints), `src/php/dashboard/` (admin UI pages like `index.php`, `menssage.php`).

Key files and locations

- Backend & API: `src/php/sistema.php` and `src/php/dashboard/*.php` — inspect these for endpoints and business logic.
- Config: `config/config.php` and `.env.example` — DB creds and `GROQ_API_KEY` are read from here.
- Frontend assets: `src/css/` (styles), `src/js/` (scripts like `dashboard.js`, `sistema.js`), `src/html/chat-cliente.html` (client chat UI).

Patterns & conventions (discoverable)

- Sidebar and markup: keep existing `<div class="sidebar">` blocks and `aside`/`.container` wrappers — CSS/JS rely on these class names.
- Icons: use Google Material Symbols via `<span class="material-symbols-sharp">ICON_NAME</span>` as in existing files.
- API usage: frontend JS talks to `sistema.php` using query params (`?api=1&endpoint=admin&action=...`). See `README.md` examples for exact routes.
- PHP: uses PDO/prepared statements and sessions. Edit `config/config.php` to change DB settings; keep SQL parameterization.

Developer workflows (how to run & debug)

- Preferred local environment: XAMPP (Apache + PHP + MySQL) or equivalent LAMP/WAMP stack. Place project in `htdocs` or configure a virtual host.
- Quick PHP built-in server (for small frontend-only checks):
  - Open PowerShell and run:

    ```powershell
    cd src
    php -S localhost:8000
    ```

  - For full PHP pages that expect the `src/php` layout, use XAMPP or run `php -S` with `-t src`.
- Database: run SQL in `README.md` or import schema; update `config/config.php` or `.env` accordingly.

Integration points and examples

- Groq AI: API key referenced in `.env.example` and used by backend calls in `src/php/sistema.php`.
- Example endpoint usage (from the app):
  - `GET src/php/sistema.php?api=1&endpoint=admin&action=get_conversations&filter=unread`
  - `POST src/php/sistema.php?api=1&endpoint=admin&action=send_admin_message` (JSON body)

What not to change without approval

- Don't introduce a build system (Webpack, npm scripts) or change project structure unless asked.
- Avoid renaming CSS classes like `.sidebar` or `aside` wrappers; these are referenced by multiple files.

How to make small safe edits (recommended steps)

1. Locate UI: `src/php/dashboard/*.php` or `src/html/*.html` and `src/css/*.css`.
2. Make minimal change, preserve class names, and test locally using XAMPP or PHP built-in server.
3. If changing backend API, update `src/php/sistema.php` and search repository for callers of the same `action` to update them.

Questions to ask before larger changes

- Should I update legacy `PHP/` files or focus only on `src/php/`?
- Do you want a local `docker-compose` or keep XAMPP as the supported dev environment?

Examples from the codebase

- Sidebar pattern (preserve structure):

```html
<div class="sidebar">
  <a href="#">
    <span class="material-symbols-sharp">home</span>
    <h3>Dashboard</h3>
  </a>
</div>
```

- API call pattern (frontend → backend):

```
GET src/php/sistema.php?api=1&endpoint=admin&action=get_messages&conversa_id=123
```

If anything above is unclear or you want additional guidance (tests, formatter, or a dev container), tell me which direction to expand and I will iterate.