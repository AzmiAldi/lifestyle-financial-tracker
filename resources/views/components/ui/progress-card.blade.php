@props([
    'title',
    'meta' => null,
    'value',
    'tone' => 'accent',
    'caption' => null,
])

<x-ui.app-card :interactive="true" padding="p-4" {{ $attributes }}>
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="font-medium text-white">{{ $title }}</p>
            @if ($meta)
                <p class="mt-1 text-sm text-zinc-400">{{ $meta }}</p>
            @endif
        </div>

        <p class="text-sm font-semibold text-cyan-300">{{ $value }}%</p>
    </div>

    <x-ui.progress-bar class="mt-3" :value="$value" :tone="$tone" />

    @if ($caption)
        <p class="mt-2 text-xs text-zinc-500">{{ $caption }}</p>
    @endif
</x-ui.app-card>
