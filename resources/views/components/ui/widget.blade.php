@props([
    'title' => null,
    'description' => null,
    'eyebrow' => null,
])

<section {{ $attributes->class(['rounded-2xl border border-white/10 bg-white/[0.035] shadow-[0_20px_80px_rgba(0,0,0,0.35)] backdrop-blur transition duration-200']) }}>
    @if ($title || $description || $eyebrow)
        <div class="px-5 pb-2 pt-5 md:px-6">
            <x-ui.section-header :title="$title" :description="$description" :eyebrow="$eyebrow" />
        </div>
    @endif

    {{ $slot }}
</section>
