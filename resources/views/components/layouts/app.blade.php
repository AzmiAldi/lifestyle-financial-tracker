<x-layouts.app.sidebar>
    <flux:main class="relative min-h-screen bg-[#050507] text-zinc-100">
        <div class="pointer-events-none fixed inset-0 -z-10">
            <div class="absolute right-[-8rem] top-[-8rem] h-[30rem] w-[30rem] rounded-full bg-cyan-400/10 blur-3xl"></div>
            <div class="absolute bottom-[-10rem] left-[-10rem] h-[28rem] w-[28rem] rounded-full bg-emerald-400/[0.08] blur-3xl"></div>
            <div class="absolute left-1/3 top-1/4 h-72 w-72 rounded-full bg-blue-500/[0.06] blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(255,255,255,0.075),transparent_32%),linear-gradient(to_bottom,rgba(255,255,255,0.025),transparent_30%)]"></div>
        </div>

        <div class="mx-auto w-full max-w-7xl px-6 py-8 md:px-8">
            {{ $slot }}
        </div>
    </flux:main>
</x-layouts.app.sidebar>
