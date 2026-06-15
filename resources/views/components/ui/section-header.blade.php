@props([
    'title',
    'description' => null,
    'eyebrow' => null,
])

<div {{ $attributes->class(['flex flex-col gap-1.5']) }}>
    @if ($eyebrow)
        <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">{{ $eyebrow }}</p>
    @endif

    <h2 class="text-2xl font-semibold tracking-tight text-white">{{ $title }}</h2>

    @if ($description)
        <p class="max-w-2xl text-sm leading-6 text-zinc-400">{{ $description }}</p>
    @endif
</div>
