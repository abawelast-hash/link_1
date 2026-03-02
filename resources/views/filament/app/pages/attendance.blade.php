<x-filament-panels::page>
    <div x-data="attendanceApp()" x-init="initGPS()" class="space-y-6">

        {{-- ????? ?????? --}}
        <div class="rounded-2xl border border-[#D4A841]/30 bg-[#1E293B] p-6 text-white shadow-xl">
            <h2 class="mb-4 text-lg font-bold text-[#D4A841]"> ????? ??????</h2>
            <div x-show="locating" class="text-yellow-400"> ???? ????? ?????...</div>
            <div x-show="!locating && error" class="text-red-400" x-text="error"></div>
            <div x-show="!locating && !error && lat" class="space-y-1 text-sm text-gray-300">
                <p>?? ?????: <span class="font-mono text-[#D4A841]" x-text="lat"></span></p>
                <p>?? ?????: <span class="font-mono text-[#D4A841]" x-text="lng"></span></p>
                <p>?????: <span x-text="accuracy + ' ???'"></span></p>
            </div>
        </div>

        {{-- ????? ??????? --}}
        @if(! $this->checkedIn)
        <button
            x-on:click="sendCheckIn"
            x-bind:disabled="locating || !lat"
            class="w-full rounded-xl bg-[#D4A841] px-6 py-4 text-xl font-bold text-[#0F172A] shadow-lg transition hover:bg-[#F5E4A3] disabled:opacity-50"
        >
             ????? ??????
        </button>
        @else
        <div class="rounded-xl border border-emerald-500/30 bg-emerald-900/20 p-4 text-center text-emerald-400 font-bold">
             ?? ????? ????? ?????
        </div>
        <button
            x-on:click="sendCheckOut"
            x-bind:disabled="locating || !lat"
            class="w-full rounded-xl border border-[#D4A841] px-6 py-3 text-lg font-semibold text-[#D4A841] shadow transition hover:bg-[#D4A841]/10 disabled:opacity-50"
        >
             ????? ????????
        </button>
        @endif
    </div>

    <script>
    function attendanceApp() {
        return {
            lat: null, lng: null, accuracy: null,
            locating: true, error: null,
            initGPS() {
                if (!navigator.geolocation) {
                    this.error = '?????? ?? ???? GPS.';
                    this.locating = false;
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    pos => {
                        this.lat = pos.coords.latitude.toFixed(7);
                        this.lng = pos.coords.longitude.toFixed(7);
                        this.accuracy = Math.round(pos.coords.accuracy);
                        this.locating = false;
                        @this.set('latitude', parseFloat(this.lat));
                        @this.set('longitude', parseFloat(this.lng));
                    },
                    err => {
                        this.error = '???? ????? ??????: ' + err.message;
                        this.locating = false;
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            },
            sendCheckIn()  { @this.call('checkIn'); },
            sendCheckOut() { @this.call('checkOut'); },
        }
    }
    </script>
</x-filament-panels::page>
