@props([
    'value' => 0,
    'tone' => 'accent',
    'height' => 'h-2',
])

@php
    $percentage = min(100, max(0, (float) $value));

    $toneClasses = [
        'safe' => 'bg-emerald-300/80',
        'warning' => 'bg-amber-300/80',
        'danger' => 'bg-rose-300/80',
        'accent' => 'bg-cyan-300/80',
        'neutral' => 'bg-zinc-300/70',
    ];
@endphp

<div {{ $attributes->class(['overflow-hidden rounded-full bg-white/10', $height]) }}>
    <div
        class="{{ $height }} rounded-full transition-all duration-500 {{ $toneClasses[$tone] ?? $toneClasses['accent'] }}"
        style="width: {{ $percentage }}%"
    ></div>
</div>
