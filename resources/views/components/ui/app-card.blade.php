@props([
    'padding' => 'p-5 md:p-6',
    'interactive' => false,
])

<div
    {{ $attributes->class([
        'rounded-2xl border border-white/10 bg-white/[0.035] shadow-[0_20px_80px_rgba(0,0,0,0.35)] backdrop-blur',
        'transition duration-200 hover:-translate-y-0.5 hover:bg-white/[0.06] hover:shadow-[0_24px_90px_rgba(0,0,0,0.45)]' => $interactive,
        $padding,
    ]) }}
>
    {{ $slot }}
</div>
