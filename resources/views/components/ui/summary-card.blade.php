@props([
    'label',
    'value',
    'description' => null,
    'tone' => 'neutral',
])

@php
    $toneClasses = [
        'neutral' => 'border-white/10 bg-white/[0.035] text-white',
        'positive' => 'border-emerald-300/15 bg-emerald-300/[0.08] text-emerald-300',
        'negative' => 'border-rose-300/15 bg-rose-300/[0.08] text-rose-300',
        'accent' => 'border-cyan-300/15 bg-cyan-300/[0.08] text-cyan-100',
    ];
@endphp

<div {{ $attributes->class(['rounded-2xl border px-5 py-5 shadow-[0_20px_80px_rgba(0,0,0,0.28)] transition duration-200 hover:-translate-y-0.5 hover:bg-white/[0.06]', $toneClasses[$tone] ?? $toneClasses['neutral']]) }}>
    <p class="text-xs font-medium uppercase tracking-[0.2em] text-cyan-300/70">{{ $label }}</p>
    <p class="mt-2 text-2xl font-semibold tracking-tight md:text-3xl">{{ $value }}</p>

    @if ($description)
        <p class="mt-2 text-sm leading-6 text-zinc-400">{{ $description }}</p>
    @endif
</div>
