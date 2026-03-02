<x-filament-panels::page>
    <div class="space-y-4">
        @forelse($this->reports as $report)
        <div class="rounded-2xl border border-[#D4A841]/20 bg-[#1E293B] p-5 text-white shadow">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="text-lg font-bold text-[#D4A841]">{{ $report->period_label }}</h3>
                <span class="rounded-full bg-[#D4A841]/20 px-3 py-1 text-sm text-[#D4A841]">{{ number_format($report->net_salary, 2) }} ????</span>
            </div>
            <div class="grid grid-cols-2 gap-3 text-sm md:grid-cols-4">
                <div class="rounded-lg bg-[#0F172A] p-3 text-center">
                    <div class="text-xl font-bold text-emerald-400">{{ $report->present_days }}</div>
                    <div class="text-gray-400">???? ????</div>
                </div>
                <div class="rounded-lg bg-[#0F172A] p-3 text-center">
                    <div class="text-xl font-bold text-yellow-400">{{ $report->late_days }}</div>
                    <div class="text-gray-400">???? ?????</div>
                </div>
                <div class="rounded-lg bg-[#0F172A] p-3 text-center">
                    <div class="text-xl font-bold text-red-400">{{ $report->total_deduction }} ????</div>
                    <div class="text-gray-400">??????</div>
                </div>
                <div class="rounded-lg bg-[#0F172A] p-3 text-center">
                    <div class="text-xl font-bold text-[#D4A841]">{{ $report->total_points }}</div>
                    <div class="text-gray-400">????</div>
                </div>
            </div>
        </div>
        @empty
        <div class="rounded-xl border border-dashed border-gray-600 p-10 text-center text-gray-400">
            ?? ???? ?????? ????? ???.
        </div>
        @endforelse
    </div>
</x-filament-panels::page>
