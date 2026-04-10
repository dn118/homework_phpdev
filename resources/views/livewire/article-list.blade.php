<div class="space-y-8">
    <section class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
        <div class="flex flex-col gap-6 lg:flex-row lg:items-start lg:justify-between">
            <div class="space-y-3">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-orange-600">Article Feed</p>
                <h2 class="text-2xl font-semibold tracking-tight text-gray-900">Saved from a public source, personalized per user.</h2>
                <p class="max-w-2xl text-sm text-gray-600">
                    Articles are preloaded from Hacker News into the local database. Favorites stay pinned to the top, and hidden
                    articles are kept separate until you restore them.
                </p>
                <p class="text-xs text-gray-500">Source: {{ $this->source }}</p>
            </div>

            <dl class="grid grid-cols-3 gap-3 sm:w-auto">
                <div class="rounded-xl bg-orange-50 px-4 py-3 text-center">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-orange-700">Visible</dt>
                    <dd class="mt-1 text-2xl font-semibold text-orange-900">{{ $this->articles->count() }}</dd>
                </div>
                <div class="rounded-xl bg-yellow-50 px-4 py-3 text-center">
                    <dt class="text-xs font-semibold uppercase tracking-wide text-yellow-700">Favorites</dt>
                    <dd class="mt-1 text-2xl font-semibold text-yellow-900">{{ $this->articles->where('is_favorited', true)->count() }}</dd>
                </div>
                <button
                    type="button"
                    onclick="document.getElementById('hidden-articles').scrollIntoView({ behavior: 'smooth', block: 'start' })"
                    class="rounded-xl bg-gray-100 px-4 py-3 text-center cursor-pointer hover:bg-gray-200 transition"
                >
                    <dt class="text-xs font-semibold uppercase tracking-wide text-gray-600">Hidden</dt>
                    <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ $this->hiddenArticles->count() }}</dd>
                </button>
            </dl>
        </div>
    </section>

    <section class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Visible Articles</h3>
                <p class="text-sm text-gray-500">Favorites appear first, ordered by the latest favorite action.</p>
            </div>
        </div>

        @if($this->articles->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-8 text-center shadow-sm">
                <p class="text-base font-medium text-gray-700">No visible articles right now.</p>
                <p class="mt-2 text-sm text-gray-500">Run `php artisan articles:sync` or restart the local server to populate articles.</p>
            </div>
        @else
            <div class="space-y-4">
                @foreach($this->articles as $article)
                    <article class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm transition hover:border-orange-200 hover:shadow-md">
                        <div class="flex flex-col gap-5 lg:flex-row lg:items-start lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    @if($article->is_favorited)
                                        <span class="rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-semibold text-yellow-800">Favorite</span>
                                    @endif
                                    @if(!$article->url)
                                        <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700">HN Discussion</span>
                                    @endif
                                </div>

                                <h4 class="mt-3 text-xl font-semibold tracking-tight text-gray-900">
                                    @if($article->url)
                                        <a href="{{ $article->url }}" target="_blank" rel="noopener noreferrer" class="hover:text-orange-600 hover:underline">
                                            {{ $article->title }}
                                        </a>
                                    @else
                                        <span>{{ $article->title }}</span>
                                    @endif
                                </h4>

                                <p class="mt-2 text-sm text-gray-500">
                                    {{ $article->source }} | Published: {{ $article->published_at?->format('Y-m-d H:i') ?? 'N/A' }}
                                </p>

                                <div
                                    wire:loading.flex
                                    wire:target="favorite('{{ $article->id }}'), hide('{{ $article->id }}')"
                                    class="mt-3 hidden items-center gap-2 text-sm text-gray-500"
                                >
                                    <span class="inline-block h-2 w-2 rounded-full bg-orange-500"></span>
                                    Updating article state...
                                </div>

                                <div class="mt-4 flex flex-wrap gap-4 text-sm">
                                    @if($article->url)
                                        <a
                                            href="{{ $article->url }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="font-medium text-orange-600 hover:text-orange-500"
                                        >
                                            Open article
                                        </a>
                                    @endif
                                    <a
                                        href="{{ $article->discussion_url }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="font-medium text-gray-700 hover:text-gray-900"
                                    >
                                        Open Hacker News discussion
                                    </a>
                                </div>
                            </div>

                            <div class="flex shrink-0 items-center gap-3 lg:pt-1">
                                <button
                                    wire:click="favorite('{{ $article->id }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="favorite('{{ $article->id }}')"
                                    class="inline-flex items-center gap-2 rounded-full border border-yellow-200 bg-yellow-50 px-4 py-2 text-sm font-medium text-yellow-800 transition hover:bg-yellow-100 disabled:opacity-60"
                                    aria-label="{{ $article->is_favorited ? 'Remove from favorites' : 'Add to favorites' }}"
                                >
                                    <span class="text-base leading-none">{{ $article->is_favorited ? '★' : '☆' }}</span>
                                    <span wire:loading.remove wire:target="favorite('{{ $article->id }}')">
                                        {{ $article->is_favorited ? 'Favorited' : 'Favorite' }}
                                    </span>
                                    <span wire:loading wire:target="favorite('{{ $article->id }}')">Updating...</span>
                                </button>

                                <button
                                    wire:click="hide('{{ $article->id }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="hide('{{ $article->id }}')"
                                    class="inline-flex items-center rounded-lg border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 transition hover:border-red-200 hover:bg-red-50 hover:text-red-600 disabled:opacity-60"
                                    aria-label="Hide article"
                                >
                                    <span wire:loading.remove wire:target="hide('{{ $article->id }}')">Hide</span>
                                    <span wire:loading wire:target="hide('{{ $article->id }}')">Hiding...</span>
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>

    <section id="hidden-articles" class="space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-lg font-semibold text-gray-900">Hidden Articles</h3>
                <p class="text-sm text-gray-500">Hidden items stay available here so they can be restored later.</p>
            </div>
        </div>

        @if($this->hiddenArticles->isEmpty())
            <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-6 text-center shadow-sm">
                <p class="text-sm text-gray-500">No hidden articles.</p>
            </div>
        @else
            <div class="space-y-3">
                @foreach($this->hiddenArticles as $article)
                    <article class="rounded-2xl border border-gray-200 bg-gray-50 p-5">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                            <div class="min-w-0 flex-1">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="rounded-full bg-gray-200 px-2.5 py-1 text-xs font-semibold text-gray-700">Hidden</span>
                                    @if($article->is_favorited)
                                        <span class="rounded-full bg-yellow-100 px-2.5 py-1 text-xs font-semibold text-yellow-800">Favorite</span>
                                    @endif
                                </div>
                                <h4 class="mt-3 text-base font-semibold text-gray-900">{{ $article->title }}</h4>
                                <p class="mt-1 text-sm text-gray-500">
                                    Hidden on {{ $article->hidden_at?->format('Y-m-d H:i') ?? 'N/A' }}
                                </p>
                            </div>

                            <div class="flex shrink-0 items-center gap-3">
                                <a
                                    href="{{ $article->discussion_url }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="text-sm font-medium text-gray-700 hover:text-gray-900"
                                >
                                    Open discussion
                                </a>
                                <button
                                    wire:click="unhide('{{ $article->id }}')"
                                    wire:loading.attr="disabled"
                                    wire:target="unhide('{{ $article->id }}')"
                                    class="inline-flex items-center rounded-lg border border-orange-200 bg-white px-4 py-2 text-sm font-medium text-orange-700 transition hover:bg-orange-50 disabled:opacity-60"
                                >
                                    <span wire:loading.remove wire:target="unhide('{{ $article->id }}')">Unhide</span>
                                    <span wire:loading wire:target="unhide('{{ $article->id }}')">Restoring...</span>
                                </button>
                            </div>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </section>
</div>
