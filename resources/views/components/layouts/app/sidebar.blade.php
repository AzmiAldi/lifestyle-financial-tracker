<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-[#050507] text-zinc-100 antialiased">
        @php
            $itemBaseClass = 'rounded-xl border border-transparent px-3 py-2.5 text-zinc-400 transition duration-200 hover:bg-white/[0.06] hover:text-white';
            $itemActiveClass = '!border-white/10 !bg-white/10 !text-white shadow-inner shadow-white/[0.04]';
        @endphp

        <flux:sidebar sticky stashable class="border-r border-white/10 bg-[#08080a]/90 text-zinc-100 backdrop-blur-xl">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="mr-3 rounded-2xl border border-white/10 bg-white/[0.04] px-3 py-3 shadow-[0_20px_70px_rgba(0,0,0,0.24)] transition duration-200 hover:bg-white/[0.06]" wire:navigate>
                <div class="flex items-center gap-2">
                    <x-app-logo class="size-8" href="#"></x-app-logo>
                </div>
                <p class="mt-2 text-[11px] font-medium text-zinc-500">Behavioral Finance Companion</p>
            </a>

            <flux:navlist variant="outline" class="mt-6">
                <flux:navlist.group heading="Workspace" class="grid gap-1.5">
                    <flux:navlist.item icon="home" class="{{ $itemBaseClass }} {{ request()->routeIs('dashboard') ? $itemActiveClass : '' }}" :href="route('dashboard')" :current="request()->routeIs('dashboard')" wire:navigate>Dashboard</flux:navlist.item>
                    <flux:navlist.item icon="banknotes" class="{{ $itemBaseClass }} {{ request()->routeIs('transactions.*') ? $itemActiveClass : '' }}" :href="route('transactions.index')" :current="request()->routeIs('transactions.*')" wire:navigate>Transactions</flux:navlist.item>
                    <flux:navlist.item icon="chart-bar" class="{{ $itemBaseClass }} {{ request()->routeIs('budgets.*') ? $itemActiveClass : '' }}" :href="route('budgets.index')" :current="request()->routeIs('budgets.*')" wire:navigate>Budgets</flux:navlist.item>
                    <flux:navlist.item icon="sparkles" class="{{ $itemBaseClass }} {{ request()->routeIs('savings-goals.*') ? $itemActiveClass : '' }}" :href="route('savings-goals.index')" :current="request()->routeIs('savings-goals.*')" wire:navigate>Savings Goals</flux:navlist.item>
                    <flux:navlist.item icon="face-smile" class="{{ $itemBaseClass }} {{ request()->routeIs('mood-tracker.*') ? $itemActiveClass : '' }}" :href="route('mood-tracker.index')" :current="request()->routeIs('mood-tracker.*')" wire:navigate>Mood Tracker</flux:navlist.item>
                    <flux:navlist.item icon="presentation-chart-line" class="{{ $itemBaseClass }} {{ request()->routeIs('analytics.*') ? $itemActiveClass : '' }}" :href="route('analytics.index')" :current="request()->routeIs('analytics.*')" wire:navigate>Analytics</flux:navlist.item>
                    <flux:navlist.item icon="trophy" class="{{ $itemBaseClass }} {{ request()->routeIs('achievements.*') ? $itemActiveClass : '' }}" :href="route('achievements.index')" :current="request()->routeIs('achievements.*')" wire:navigate>Achievements</flux:navlist.item>
                    <flux:navlist.item icon="tag" class="{{ $itemBaseClass }} {{ request()->routeIs('categories.*') ? $itemActiveClass : '' }}" :href="route('categories.index')" :current="request()->routeIs('categories.*')" wire:navigate>Categories</flux:navlist.item>
                </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <!-- Desktop User Menu -->
            <flux:dropdown position="bottom" align="start" class="rounded-2xl border border-white/10 bg-white/[0.04] p-2 shadow-[0_20px_70px_rgba(0,0,0,0.24)]">
                <flux:profile
                    :name="auth()->user()->name"
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevrons-up-down"
                />

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-white/10 text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs text-zinc-400">{{ auth()->user()->email }}</span>
                                    <span class="mt-0.5 text-[11px] text-zinc-500">Personal Workspace</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Settings</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="border-b border-white/10 bg-[#08080a]/90 backdrop-blur-xl lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <flux:profile
                    :initials="auth()->user()->initials()"
                    icon-trailing="chevron-down"
                />

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    <span class="flex h-full w-full items-center justify-center rounded-lg bg-white/10 text-white">
                                        {{ auth()->user()->initials() }}
                                    </span>
                                </span>

                                <div class="grid flex-1 text-left text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs text-zinc-400">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item href="/settings/profile" icon="cog" wire:navigate>Settings</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
