@props([
    'variant' => 'primary',
    'href' => null,
    'type' => 'button',
    'wireNavigate' => true,
])

@php
    $classes = [
        'primary' => 'rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-zinc-950 shadow-[0_12px_34px_rgba(255,255,255,0.08)] transition duration-200 hover:bg-zinc-200',
        'secondary' => 'rounded-xl border border-white/10 bg-white/[0.04] px-4 py-2.5 text-sm font-semibold text-white transition duration-200 hover:bg-white/[0.08]',
        'danger' => 'rounded-xl bg-rose-500/90 px-4 py-2.5 text-sm font-semibold text-white transition duration-200 hover:bg-rose-400',
    ][$variant] ?? 'rounded-xl bg-white px-4 py-2.5 text-sm font-semibold text-zinc-950 transition duration-200 hover:bg-zinc-200';
@endphp

@if ($href)
    @if ($wireNavigate)
        <a href="{{ $href }}" wire:navigate {{ $attributes->class([$classes]) }}>
            {{ $slot }}
        </a>
    @else
        <a href="{{ $href }}" {{ $attributes->class([$classes]) }}>
            {{ $slot }}
        </a>
    @endif
@else
    <button type="{{ $type }}" {{ $attributes->class([$classes]) }}>
        {{ $slot }}
    </button>
@endif
