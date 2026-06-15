@props([
    'label',
    'value',
    'description' => null,
    'tone' => 'neutral',
])

@php
    $toneClasses = [
        'neutral' => 'text-white',
        'positive' => 'text-emerald-300',
        'negative' => 'text-rose-300',
        'accent' => 'text-cyan-100',
    ];
@endphp

<x-ui.app-card :interactive="true" padding="p-5" {{ $attributes }}>
    <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">{{ $label }}</p>
    <p class="mt-3 text-3xl font-semibold tracking-tight md:text-4xl {{ $toneClasses[$tone] ?? $toneClasses['neutral'] }}">{{ $value }}</p>

    @if ($description)
        <p class="mt-3 text-sm leading-6 text-zinc-400">{{ $description }}</p>
    @endif
</x-ui.app-card>
