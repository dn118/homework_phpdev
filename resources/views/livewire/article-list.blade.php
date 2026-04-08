<div>
    <div class="mb-4">
        <p class="text-sm text-gray-500">Source: {{ $this->source }}</p>
    </div>

    @if($syncError)
        <div class="mb-4 bg-red-50 text-red-700 px-4 py-3 rounded-md">
            {{ $syncError }}
        </div>
    @endif

    @if($this->articles->isEmpty())
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 text-center">
            <p class="text-gray-500">No articles available.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($this->articles as $article)
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6 flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                            @if($article->url)
                                <a href="{{ $article->url }}" target="_blank" class="hover:underline">{{ $article->title }}</a>
                            @else
                                <span class="text-gray-600 dark:text-gray-400">{{ $article->title }}</span>
                            @endif
                        </h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                            {{ $article->source }} | Published: {{ $article->published_at?->format('Y-m-d H:i') ?? 'N/A' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2 ml-4">
                        <button
                            wire:click="favorite('{{ $article->id }}')"
                            class="text-2xl {{ $article->is_favorited ? 'text-yellow-500' : 'text-gray-400' }} hover:text-yellow-500"
                            aria-label="{{ $article->is_favorited ? 'Unfavorite article' : 'Favorite article' }}"
                        >
                            {{ $article->is_favorited ? '★' : '☆' }}
                        </button>
                        <button
                            wire:click="hide('{{ $article->id }}')"
                            class="text-gray-400 hover:text-red-500 text-sm"
                            aria-label="Hide article"
                        >
                            Hide
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
