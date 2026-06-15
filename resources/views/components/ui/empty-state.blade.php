@props([
    'title',
    'description',
    'actionLabel' => null,
    'actionUrl' => null,
    'wireNavigate' => true,
])

<div {{ $attributes->class(['rounded-2xl border border-dashed border-white/10 bg-gradient-to-br from-white/[0.04] to-white/[0.015] px-6 py-10 text-center shadow-[0_20px_80px_rgba(0,0,0,0.22)]']) }}>
    <div class="mx-auto mb-5 flex h-12 w-12 items-center justify-center rounded-full border border-white/10 bg-white/[0.05] shadow-[0_0_36px_rgba(103,232,249,0.12)]">
        <div class="h-2.5 w-2.5 rounded-full bg-cyan-300/80 shadow-[0_0_24px_rgba(103,232,249,0.65)]"></div>
    </div>

    <p class="text-sm font-medium text-white">{{ $title }}</p>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-zinc-400">{{ $description }}</p>

    @if ($actionLabel && $actionUrl)
        <x-ui.button class="mt-5 inline-flex" :href="$actionUrl" :wire-navigate="$wireNavigate">
            {{ $actionLabel }}
        </x-ui.button>
    @endif
</div>
