<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="bg-gray-50 font-sans text-gray-900 antialiased">
        <div class="min-h-screen">
            <header class="border-b border-gray-200 bg-white">
                <div class="mx-auto flex max-w-6xl items-center justify-between px-6 py-4">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-orange-600">Article Favorites</p>
                        <p class="text-sm text-gray-600">Laravel + Livewire take-home project</p>
                    </div>

                    <nav class="flex items-center gap-3">
                        @auth
                            <a
                                href="{{ route('dashboard') }}"
                                class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-700"
                            >
                                Open Dashboard
                            </a>
                        @else
                            <a
                                href="{{ route('login') }}"
                                class="rounded-md border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-gray-400 hover:text-gray-900"
                            >
                                Log In
                            </a>
                            <a
                                href="{{ route('register') }}"
                                class="rounded-md bg-gray-900 px-4 py-2 text-sm font-medium text-white transition hover:bg-gray-700"
                            >
                                Create Account
                            </a>
                        @endauth
                    </nav>
                </div>
            </header>

            <main class="mx-auto max-w-6xl px-6 py-16">
                <section class="grid gap-10 lg:grid-cols-[1.3fr,0.9fr] lg:items-start">
                    <div class="space-y-6">
                        <div class="space-y-4">
                            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-orange-600">Public API + Local Persistence</p>
                            <h1 class="max-w-3xl text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                                Browse public articles, hide noise, and pin favorites to the top.
                            </h1>
                            <p class="max-w-2xl text-lg text-gray-600">
                                This application imports stories from Hacker News, stores them locally, and keeps hide and
                                favorite state per authenticated user.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-3">
                            @auth
                                <a
                                    href="{{ route('dashboard') }}"
                                    class="rounded-md bg-orange-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-orange-500"
                                >
                                    Go to Articles
                                </a>
                            @else
                                <a
                                    href="{{ route('register') }}"
                                    class="rounded-md bg-orange-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-orange-500"
                                >
                                    Start with Registration
                                </a>
                                <a
                                    href="{{ route('login') }}"
                                    class="rounded-md border border-gray-300 px-5 py-3 text-sm font-semibold text-gray-700 transition hover:border-gray-400 hover:text-gray-900"
                                >
                                    Sign In
                                </a>
                            @endauth
                        </div>

                        <dl class="grid gap-4 sm:grid-cols-3">
                            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                <dt class="text-sm font-semibold text-gray-900">Authenticated workflow</dt>
                                <dd class="mt-2 text-sm text-gray-600">Registration, login, logout, and per-user article state.</dd>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                <dt class="text-sm font-semibold text-gray-900">Deterministic ordering</dt>
                                <dd class="mt-2 text-sm text-gray-600">Favorites stay pinned first and sort by latest favorite action.</dd>
                            </div>
                            <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm">
                                <dt class="text-sm font-semibold text-gray-900">Local-first interactions</dt>
                                <dd class="mt-2 text-sm text-gray-600">Hide and favorite actions update persisted data without repeated API fetches.</dd>
                            </div>
                        </dl>
                    </div>

                    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                        <h2 class="text-lg font-semibold text-gray-900">How it works</h2>

                        <ol class="mt-4 space-y-4 text-sm text-gray-600">
                            <li class="flex gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-orange-100 text-xs font-semibold text-orange-700">1</span>
                                <span>Stories are synced from the public Hacker News API into the local `articles` table.</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-orange-100 text-xs font-semibold text-orange-700">2</span>
                                <span>Authenticated users manage hidden and favorite state through `user_article_preferences`.</span>
                            </li>
                            <li class="flex gap-3">
                                <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-orange-100 text-xs font-semibold text-orange-700">3</span>
                                <span>The dashboard renders from local persisted data, with favorites sorted ahead of the rest.</span>
                            </li>
                        </ol>

                        <div class="mt-6 rounded-xl bg-gray-50 p-4">
                            <h3 class="text-sm font-semibold text-gray-900">Local setup</h3>
                            <pre class="mt-3 overflow-x-auto text-xs leading-6 text-gray-700"><code>composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
npm install
npm run build
php artisan serve</code></pre>
                        </div>
                    </section>
                </section>
            </main>
        </div>
    </body>
</html>
