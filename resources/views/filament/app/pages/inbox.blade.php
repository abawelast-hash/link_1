<x-filament-panels::page>
    <div class="space-y-3">
        @forelse($this->messages as $message)
        <div class="rounded-xl border @if(!$message->is_read) border-[#D4A841]/50 bg-[#1E293B] @else border-gray-700 bg-[#1E293B]/50 @endif p-4 text-white shadow">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-semibold @if(!$message->is_read) text-[#D4A841] @else text-gray-300 @endif">
                        @if(!$message->is_read)  @endif {{ $message->subject ?? '????? ???? ?????' }}
                    </p>
                    <p class="mt-1 text-sm text-gray-400">??: {{ $message->sender->name }}</p>
                    <p class="mt-2 text-sm text-gray-300">{{ Str::limit($message->body, 120) }}</p>
                </div>
                <span class="shrink-0 text-xs text-gray-500">{{ $message->created_at->diffForHumans() }}</span>
            </div>
        </div>
        @empty
        <div class="rounded-xl border border-dashed border-gray-600 p-10 text-center text-gray-400">
            ????? ?????? ????.
        </div>
        @endforelse
    </div>
</x-filament-panels::page>
