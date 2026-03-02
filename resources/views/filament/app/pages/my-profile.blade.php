<x-filament-panels::page>
    <div class="mx-auto max-w-xl rounded-2xl border border-[#D4A841]/30 bg-[#1E293B] p-6 text-white shadow-xl">
        <div class="mb-5 flex items-center gap-4">
            <div class="flex h-16 w-16 items-center justify-center rounded-full bg-[#D4A841]/20 text-3xl">
                {{ mb_substr(auth()->user()->name, 0, 1) }}
            </div>
            <div>
                <h2 class="text-xl font-bold text-[#D4A841]">{{ auth()->user()->name }}</h2>
                <p class="text-sm text-gray-400">{{ auth()->user()->role_label }}</p>
            </div>
        </div>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between border-b border-gray-700 pb-2">
                <span class="text-gray-400">??? ??????</span>
                <span class="font-mono">{{ auth()->user()->employee_id ?? '' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-700 pb-2">
                <span class="text-gray-400">?????</span>
                <span>{{ auth()->user()->branch?->name ?? '' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-700 pb-2">
                <span class="text-gray-400">?????</span>
                <span>{{ auth()->user()->department ?? '' }}</span>
            </div>
            <div class="flex justify-between border-b border-gray-700 pb-2">
                <span class="text-gray-400">?????? ??????????</span>
                <span>{{ auth()->user()->email }}</span>
            </div>
            <div class="flex justify-between pb-2">
                <span class="text-gray-400">?????? ??????</span>
                <span class="font-bold text-[#D4A841]">{{ number_format(auth()->user()->total_points) }} ????</span>
            </div>
        </div>
    </div>
</x-filament-panels::page>
