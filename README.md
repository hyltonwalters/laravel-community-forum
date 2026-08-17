![Laravel Community Forum banner](https://raw.githubusercontent.com/hyltonwalters/laravel-community-forum/deploy/render-demo/docs/social-preview.png)

# Laravel Community Forum

[![Laravel 12 CI](https://github.com/hyltonwalters/laravel-community-forum/actions/workflows/modernization-checkpoint.yml/badge.svg?branch=deploy%2Frender-demo)](https://github.com/hyltonwalters/laravel-community-forum/actions/workflows/modernization-checkpoint.yml?query=branch%3Adeploy%2Frender-demo)

A Laravel community forum with channels, discussions, replies, likes, watchers, best-answer workflows, notifications and social authentication.

Originally built on Laravel 7, this project has been deliberately modernized and security-hardened through successive framework upgrades to **Laravel 12**. The goal of the modernization work is to preserve the original forum domain while improving authorization, mutation safety, dependency health, automated testing and build reproducibility.

## Live demo

- **Live application:** https://laravel-community-forum.onrender.com
- **Source:** https://github.com/hyltonwalters/laravel-community-forum
- **Hosting:** Render
- **Database:** Neon PostgreSQL

### Demo login

Use the non-admin demo account:

```text
Email: john@doe.com
Password: password
```

The public deployment is intended as a portfolio demonstration of the forum workflow and modernization work. The hosted database is seeded with sample discussions, replies, channels and users so the application can be explored without creating an account first.

> **Note:** Render free services can sleep when idle, so the first request after a period of inactivity may take longer than normal.

## Modernization highlights

- Upgraded the application from **Laravel 7 to Laravel 12** on **PHP 8.2+**.
- Replaced obsolete framework-era dependencies with Laravel 12-compatible packages, including Socialite 5 and PHPUnit 11.
- Replaced the legacy Laravel Mix / Webpack / Vue 2 frontend scaffold with a smaller **Vite + Bootstrap 5** asset pipeline.
- Converted state-changing forum actions to CSRF-protected POST, PATCH and DELETE requests rather than GET requests.
- Added ownership checks for discussion and reply updates and authorization around best-answer selection.
- Made likes and discussion watchers idempotent and backed them with database uniqueness constraints.
- Added transactional handling for reply points, best-answer points and watcher notifications.
- Replaced the legacy Markdown package with CommonMark 2 and added regression coverage for unsafe raw HTML.
- Added GitHub Actions verification for dependency audits, Laravel tests, the production frontend build and the deployment Docker image.
- Added a containerized Render deployment backed by Neon PostgreSQL.

## Current stack

**Backend:** PHP 8.2+, Laravel 12, Eloquent ORM, Laravel Socialite, CommonMark 2  
**Frontend:** Vite, Bootstrap 5, Sass  
**Database:** PostgreSQL on Neon for the hosted demo; SQLite in-memory for automated tests  
**Deployment:** Docker, Render  
**Testing:** PHPUnit 11, Laravel feature tests  
**Quality:** Composer validation/audit, npm audit, production asset build, GitHub Actions CI

## Forum capabilities

- Channels and threaded discussions
- Replies with validation and ownership rules
- Likes with duplicate prevention
- Discussion watching/unwatching with duplicate prevention
- Best-answer selection restricted to the discussion owner
- User points for participation and accepted answers
- Watcher notifications while avoiding self-notification
- Social authentication through Laravel Socialite
- Markdown rendering with raw HTML disabled

## Automated verification

The hardening suite currently covers discussion and reply authorization, reply validation, idempotent likes/watchers, best-answer rules, points, watcher notifications and Markdown safety.

CI also verifies:

```text
composer validate
composer audit --locked
php artisan test
npm audit --audit-level=high
npm run build
docker build --pull -f Dockerfile.render -t laravel-community-forum-render .
```

## Local setup

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
npm ci
npm run build
php artisan serve
```

Configure your database and any Socialite provider credentials in `.env` before using those integrations.

## Why this repository exists

This repository is both a working forum application and a modernization exercise: taking an older Laravel application forward without rewriting the domain from scratch. The emphasis is on framework migration, security hardening, regression tests, deterministic dependencies and maintainable CI rather than simply changing version numbers.

## License

This project is licensed under the [MIT License](LICENSE).
