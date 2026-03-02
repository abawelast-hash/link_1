<x-filament-panels::page>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse($this->badges as $userBadge)
        <div class="rounded-2xl border border-[#D4A841]/30 bg-[#1E293B] p-5 text-white shadow">
            <div class="mb-2 text-4xl">{{ $userBadge->badge->icon }}</div>
            <h3 class="text-lg font-bold text-[#D4A841]">{{ $userBadge->badge->name }}</h3>
            <p class="mt-1 text-sm text-gray-400">{{ $userBadge->badge->description }}</p>
            <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                <span>+{{ $userBadge->badge->points_reward }} ????</span>
                <span>{{ $userBadge->awarded_at->format('Y-m-d') }}</span>
            </div>
        </div>
        @empty
        <div class="col-span-full rounded-xl border border-dashed border-gray-600 p-10 text-center text-gray-400">
            ?? ???? ????? ???. ???? ???????? ????? ???? ??????! 
        </div>
        @endforelse
    </div>
</x-filament-panels::page>
