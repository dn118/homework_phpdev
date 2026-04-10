<x-app-layout>
    <x-slot name="header">
        <div class="space-y-2">
            <p class="text-sm font-semibold uppercase tracking-[0.2em] text-orange-600">Workspace</p>
            <h1 class="text-3xl font-semibold tracking-tight text-gray-900">
                Articles
            </h1>
            <p class="max-w-2xl text-sm text-gray-600">
                Review the latest imported items, keep the ones that matter at the top, and restore hidden ones whenever needed.
            </p>
        </div>
    </x-slot>

    <div class="py-10">
        <div class="mx-auto max-w-6xl px-6">
            @livewire('article-list')
        </div>
    </div>
</x-app-layout>
