# Laravel Hacker News Articles

A Laravel + Livewire application for viewing, hiding, and favoriting articles from Hacker News.

## Stack

- Laravel 13.x with Livewire
- SQLite for local development
- Pest for testing
- Laravel Pint for code formatting
- Larastan for static analysis

## Requirements

- PHP 8.3+
- Composer
- SQLite (included with PHP)

## Quick Start

```bash
make setup
make run
```

Visit http://localhost:8000 and log in. Articles will sync automatically on first visit.

## Article Sync Behavior

The app uses a sync-on-demand approach:

1. When an authenticated user visits the articles page
2. If the local `articles` table is empty, articles are automatically fetched from Hacker News
3. Subsequent visits render from local database (fast, no network dependency)
4. Manual sync: `php artisan articles:sync`

## Configuration

Edit `.env` or use environment variables:

```env
HACKERNEWS_BASE_URL=https://hacker-news.firebaseio.com/v0/
ARTICLES_SYNC_COUNT=30
ARTICLES_TIMEOUT=10
```

## Commands

```bash
# Development
make run                      # Start dev server
php artisan articles:sync     # Manually sync articles

# Testing
make test                     # Run all tests
php artisan test              # Alternative: run tests

# Code Quality
make cs_fix                   # Format code with Pint
make stan                     # Run static analysis
make audit                    # Run security audit
make code_check               # Run all checks (cs_fix + stan + audit)

# Database
make fresh                   # Reset database and run migrations
```

`make setup` runs:

```bash
composer install
cp .env.example .env
touch database/database.sqlite
php artisan key:generate
php artisan migrate
npm install
npm run build
```

## Project Structure

- `app/Livewire/ArticleList.php` - Main article list component
- `app/Providers/HackerNewsArticleProvider.php` - Hacker News API integration
- `app/Console/Commands/SyncArticles.php` - Artisan sync command
- `config/articles.php` - Article provider configuration
- `database/migrations/` - Articles and user preferences tables

## Assumptions & Tradeoffs

- SQLite is used for simplicity (no MySQL required)
- Articles sync on first page load when the local cache is empty
- Hidden and favorite states are independent per user
- Manual `articles:sync` command available for refresh

## Article Source

Hacker News Firebase API: https://hacker-news.firebaseio.com/v0/
