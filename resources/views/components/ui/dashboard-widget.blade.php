@props([
    'title' => null,
    'description' => null,
    'eyebrow' => null,
])

<x-ui.app-card padding="p-0" {{ $attributes }}>
    @if ($title || $description || $eyebrow)
        <div class="px-5 pb-2 pt-5 md:px-6">
            <x-ui.section-header :title="$title" :description="$description" :eyebrow="$eyebrow" />
        </div>
    @endif

    {{ $slot }}
</x-ui.app-card>
