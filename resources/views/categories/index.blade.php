<x-layouts.app>
    @php
        $globalCategories = $categories->whereNull('user_id');
    @endphp

    <div class="space-y-8">
        <x-ui.app-card>
            <x-ui.section-header
                title="Categories"
                eyebrow="Money Labels"
                description="Manage custom categories while global defaults stay available automatically."
            />
        </x-ui.app-card>

        <x-ui.app-card>
            <form action="{{ route('categories.store') }}" method="POST" class="grid gap-4 md:grid-cols-2">
                @csrf
                <flux:input name="name" label="Name" value="{{ old('name') }}" required />

                <flux:select name="type" label="Type" required>
                    <option value="">Select type</option>
                    <option value="income" @selected(old('type') === 'income')>Income</option>
                    <option value="expense" @selected(old('type') === 'expense')>Expense</option>
                </flux:select>

                <flux:input name="icon" label="Icon (optional)" value="{{ old('icon') }}" />
                <flux:input name="color" label="Color (optional)" value="{{ old('color') }}" />

                <div class="md:col-span-2">
                    <x-ui.button type="submit">Add Category</x-ui.button>
                </div>
            </form>
        </x-ui.app-card>

        <div class="grid gap-5 lg:grid-cols-2">
            <x-ui.dashboard-widget title="Custom Categories" description="Personal labels you create for your own tracking style.">
                @if ($customCategories->isEmpty())
                    <div class="px-5 pb-5 pt-4 md:px-6">
                        <x-ui.empty-state
                            title="Belum ada custom category."
                            description="Anda masih bisa langsung pakai kategori global bawaan, lalu tambahkan custom category saat pola spending mulai terlihat."
                        />
                    </div>
                @else
                    <div class="space-y-2 px-5 pb-5 pt-4 md:px-6">
                        @foreach ($customCategories as $category)
                            <div class="flex items-center justify-between rounded-xl px-3 py-3 transition duration-200 hover:bg-white/[0.04]">
                                <div class="flex items-center gap-3">
                                    <div class="h-9 w-9 rounded-full border border-white/10 bg-white/[0.05]"></div>
                                    <div>
                                        <p class="font-medium text-white">{{ $category->name }}</p>
                                        <p class="text-sm text-zinc-500">{{ strtoupper($category->type->value) }}</p>
                                    </div>
                                </div>
                                <span class="rounded-full border border-cyan-300/15 bg-cyan-300/[0.08] px-3 py-1 text-xs font-medium text-cyan-200">Custom</span>
                            </div>
                        @endforeach
                    </div>
                @endif
            </x-ui.dashboard-widget>

            <x-ui.dashboard-widget title="Global Categories" description="Default labels available to every user for fast setup.">
                <div class="space-y-2 px-5 pb-5 pt-4 md:px-6">
                    @foreach ($globalCategories as $category)
                        <div class="flex items-center justify-between rounded-xl px-3 py-3 transition duration-200 hover:bg-white/[0.04]">
                            <div class="flex items-center gap-3">
                                <div class="h-9 w-9 rounded-full border border-white/10 bg-white/[0.05]"></div>
                                <div>
                                    <p class="font-medium text-white">{{ $category->name }}</p>
                                    <p class="text-sm text-zinc-500">{{ strtoupper($category->type->value) }}</p>
                                </div>
                            </div>
                            <span class="rounded-full border border-emerald-300/15 bg-emerald-300/[0.08] px-3 py-1 text-xs font-medium text-emerald-200">Global</span>
                        </div>
                    @endforeach
                </div>
            </x-ui.dashboard-widget>
        </div>
    </div>
</x-layouts.app>
