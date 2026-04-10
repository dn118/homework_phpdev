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

            <main class="mx-auto max-w-3xl px-6 py-16">
                <div class="space-y-6">
                    <div class="space-y-4 text-center">
                        <p class="text-sm font-semibold uppercase tracking-[0.2em] text-orange-600">Public API + Local Persistence</p>
                        <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-5xl">
                            Browse public articles, hide noise, and pin favorites to the top.
                        </h1>
                        <p class="text-lg text-gray-600">
                            This application imports stories from Hacker News, stores them locally, and keeps hide and
                            favorite state per authenticated user.
                        </p>
                    </div>

                    <div class="flex flex-wrap justify-center gap-3">
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
            </main>
        </div>
    </body>
</html>
