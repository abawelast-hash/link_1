<x-filament-panels::page>
    <div class="space-y-4">
        @forelse($this->circulars as $circular)
        <div class="rounded-2xl border border-[#D4A841]/20 bg-[#1E293B] p-5 text-white shadow"
             wire:key="circular-{{ $circular->id }}">
            <div class="mb-2 flex items-center gap-3">
                @if($circular->priority === 'urgent')
                    <span class="rounded-full bg-red-900/40 px-2 py-0.5 text-xs text-red-400"> ????</span>
                @elseif($circular->priority === 'high')
                    <span class="rounded-full bg-yellow-900/40 px-2 py-0.5 text-xs text-yellow-400"> ?????</span>
                @endif
                <h3 class="font-bold text-[#D4A841]">{{ $circular->title }}</h3>
            </div>
            <div class="prose prose-invert prose-sm max-w-none text-gray-300">{!! $circular->body !!}</div>
            <div class="mt-3 flex items-center justify-between text-xs text-gray-500">
                <span>{{ $circular->author->name }}  {{ $circular->published_at?->format('Y-m-d H:i') }}</span>
                <button class="text-[#D4A841] hover:underline" wire:click="markRead({{ $circular->id }})">
                    ?? ??????? 
                </button>
            </div>
        </div>
        @empty
        <div class="rounded-xl border border-dashed border-gray-600 p-10 text-center text-gray-400">
            ?? ???? ?????? ?????.
        </div>
        @endforelse
    </div>
</x-filament-panels::page>
