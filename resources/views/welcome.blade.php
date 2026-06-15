<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="relative min-h-screen overflow-x-hidden bg-[#050507] text-zinc-100 antialiased">
        <div class="pointer-events-none fixed inset-0">
            <div class="absolute left-1/2 top-[-18rem] h-[38rem] w-[38rem] -translate-x-1/2 rounded-full bg-cyan-300/[0.13] blur-3xl"></div>
            <div class="absolute bottom-[-16rem] left-[-12rem] h-[32rem] w-[32rem] rounded-full bg-blue-500/[0.10] blur-3xl"></div>
            <div class="absolute right-[-14rem] top-24 h-[30rem] w-[30rem] rounded-full bg-emerald-300/[0.08] blur-3xl"></div>
            <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,rgba(255,255,255,0.08),transparent_34%),linear-gradient(to_bottom,rgba(255,255,255,0.035),transparent_42%)]"></div>
            <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.025)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.025)_1px,transparent_1px)] bg-[size:72px_72px] [mask-image:radial-gradient(circle_at_center,black,transparent_72%)]"></div>
        </div>

        <main class="relative mx-auto flex min-h-screen w-full max-w-7xl flex-col px-6 py-8 md:px-8">
            <header class="flex items-center justify-between rounded-2xl border border-white/10 bg-white/[0.035] px-4 py-3 shadow-[0_20px_80px_rgba(0,0,0,0.28)] backdrop-blur">
                <div>
                    <p class="text-sm font-semibold tracking-tight text-white">Lifestyle Financial Tracker</p>
                    <p class="text-xs text-zinc-500">Behavioral finance companion</p>
                </div>

                <div class="flex items-center gap-2">
                    @auth
                        <x-ui.button :href="route('dashboard')" class="px-4 py-2">Dashboard</x-ui.button>
                    @else
                        <x-ui.button variant="secondary" :href="route('login')" class="px-4 py-2">Login</x-ui.button>
                        <x-ui.button :href="route('register')" class="px-4 py-2">Register</x-ui.button>
                    @endauth
                </div>
            </header>

            <section class="grid flex-1 items-center gap-12 py-16 lg:grid-cols-[1.02fr_0.98fr] lg:py-10">
                <div>
                    <div class="mb-7 inline-flex w-fit items-center gap-3 rounded-full border border-white/10 bg-white/[0.04] px-4 py-2 shadow-[0_20px_80px_rgba(0,0,0,0.22)] backdrop-blur">
                        <span class="h-2 w-2 rounded-full bg-cyan-300 shadow-[0_0_22px_rgba(103,232,249,0.9)]"></span>
                        <p class="text-xs font-medium uppercase tracking-[0.24em] text-cyan-300/80">finance SaaS</p>
                    </div>

                    <h1 class="max-w-5xl text-5xl font-semibold leading-[0.98] tracking-[-0.055em] text-white sm:text-6xl lg:text-7xl">
                        Track your money, understand your lifestyle.
                    </h1>

                    <p class="mt-7 max-w-2xl text-lg leading-8 text-zinc-400 md:text-xl">
                        Build better financial habits with a calmer and more personal tracking experience.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center gap-4">
                        @auth
                            <x-ui.button :href="route('dashboard')" class="px-6 py-3">Open Dashboard</x-ui.button>
                        @else
                            <x-ui.button :href="route('login')" class="px-6 py-3">Login</x-ui.button>
                            <x-ui.button variant="secondary" :href="route('register')" class="px-6 py-3">Register</x-ui.button>
                        @endauth
                    </div>

                    <div class="mt-10 grid max-w-xl gap-3 sm:grid-cols-3">
                        <div class="rounded-2xl border border-white/10 bg-white/[0.035] p-4">
                            <p class="text-xs text-zinc-500">Budget aware</p>
                            <p class="mt-2 font-semibold text-cyan-100">Soft limits</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/[0.035] p-4">
                            <p class="text-xs text-zinc-500">Habit building</p>
                            <p class="mt-2 font-semibold text-emerald-300">Calm progress</p>
                        </div>
                        <div class="rounded-2xl border border-white/10 bg-white/[0.035] p-4">
                            <p class="text-xs text-zinc-500">Daily clarity</p>
                            <p class="mt-2 font-semibold text-white">Less noise</p>
                        </div>
                    </div>
                </div>

                <div class="relative">
                    <div class="absolute -inset-8 rounded-full bg-cyan-300/[0.10] blur-3xl"></div>
                    <div class="absolute -left-4 top-12 z-10 hidden rounded-2xl border border-white/10 bg-[#08080a]/90 px-4 py-3 shadow-[0_24px_80px_rgba(0,0,0,0.42)] backdrop-blur sm:block">
                        <p class="text-xs text-zinc-500">Income</p>
                        <p class="mt-1 text-lg font-semibold text-emerald-300">+ Rp 8.000.000</p>
                    </div>
                    <div class="absolute -right-4 bottom-16 z-10 hidden rounded-2xl border border-white/10 bg-[#08080a]/90 px-4 py-3 shadow-[0_24px_80px_rgba(0,0,0,0.42)] backdrop-blur sm:block">
                        <p class="text-xs text-zinc-500">Budget room</p>
                        <p class="mt-1 text-lg font-semibold text-cyan-100">Rp 3.200.000</p>
                    </div>

                    <div class="relative rounded-[2rem] border border-white/10 bg-white/[0.035] p-4 shadow-[0_34px_130px_rgba(0,0,0,0.52)] backdrop-blur">
                        <div class="rounded-[1.5rem] border border-white/10 bg-[#08080a]/80 p-5">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-xs uppercase tracking-[0.22em] text-cyan-300/70">Balance Overview</p>
                                    <p class="mt-3 text-4xl font-semibold tracking-[-0.04em] text-white">Rp 12.450.000</p>
                                    <p class="mt-2 text-sm text-zinc-500">June rhythm feels steady.</p>
                                </div>
                                <div class="rounded-full border border-emerald-300/15 bg-emerald-300/[0.08] px-3 py-1 text-xs text-emerald-200">Healthy pace</div>
                            </div>

                            <div class="mt-8 grid gap-3">
                                <div class="rounded-2xl border border-white/10 bg-white/[0.035] p-4">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-zinc-400">Budget Status</span>
                                        <span class="text-cyan-200">62%</span>
                                    </div>
                                    <x-ui.progress-bar class="mt-3" value="62" tone="accent" height="h-2.5" />
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/[0.035] p-4">
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-zinc-400">Savings Goal</span>
                                        <span class="text-emerald-200">Rp 2.500.000 remaining</span>
                                    </div>
                                    <x-ui.progress-bar class="mt-3" value="48" tone="safe" height="h-2.5" />
                                </div>
                                <div class="rounded-2xl border border-white/10 bg-white/[0.035] p-4">
                                    <p class="text-sm text-zinc-400">Recent Activity</p>
                                    <div class="mt-4 space-y-3">
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-zinc-300">Groceries</span>
                                            <span class="text-rose-300">- Rp 250.000</span>
                                        </div>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-zinc-300">Salary</span>
                                            <span class="text-emerald-300">+ Rp 8.000.000</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </main>
    </body>
</html>
