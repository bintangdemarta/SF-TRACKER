<div class="max-w-xl mx-auto space-y-4">
    @if (! $activeShift)
        {{-- START SHIFT --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Mulai Shift</h3>

            <form wire:submit="startShift" class="space-y-4">
                <div>
                    <label for="start_odometer" class="block text-sm font-medium text-gray-800 mb-1">Odometer Awal (KM)</label>
                    <input id="start_odometer" type="number" inputmode="numeric" autocomplete="off"
                        wire:model="start_odometer"
                        class="w-full min-h-[48px] rounded-lg border-gray-300 text-2xl py-4 px-4 focus:border-indigo-700 focus:ring-indigo-700"
                        placeholder="0">
                    @error('start_odometer')
                        <p class="text-red-800 text-sm font-medium mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="target_income" class="block text-sm font-medium text-gray-800 mb-1">Target Pendapatan Hari Ini (Rp)</label>
                    <input id="target_income" type="number" inputmode="numeric" autocomplete="off"
                        wire:model="target_income"
                        class="w-full min-h-[48px] rounded-lg border-gray-300 text-2xl py-4 px-4 focus:border-indigo-700 focus:ring-indigo-700"
                        placeholder="0">
                    @error('target_income')
                        <p class="text-red-800 text-sm font-medium mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="startShift"
                    class="w-full min-h-[48px] bg-indigo-800 hover:bg-indigo-900 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-lg py-4 rounded-lg shadow transition">
                    <span wire:loading.remove wire:target="startShift">🚀 Mulai Shift</span>
                    <span wire:loading wire:target="startShift">Memulai…</span>
                </button>
            </form>
        </div>
    @else
        {{-- ACTIVE SHIFT SUMMARY --}}
        <div class="bg-indigo-800 text-white overflow-hidden shadow-sm rounded-lg p-6" aria-live="polite" aria-atomic="true">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-indigo-100 text-sm">Pendapatan Kotor</p>
                    <p class="text-3xl font-bold">Rp{{ number_format($this->grossRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-indigo-100 text-sm">Target</p>
                    <p class="text-lg font-semibold">Rp{{ number_format($activeShift->target_income, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="mt-3 flex justify-between text-sm text-indigo-100">
                <span>{{ $this->tripCount }} trip</span>
                <span>Mulai {{ $activeShift->started_at->format('H:i') }} · odo {{ $activeShift->start_odometer }} km</span>
            </div>
            @if ($activeShift->target_income > 0)
                <div class="mt-3 h-2 bg-indigo-950 rounded-full overflow-hidden" role="progressbar"
                    aria-valuenow="{{ min(100, round($this->grossRevenue / $activeShift->target_income * 100)) }}"
                    aria-valuemin="0" aria-valuemax="100">
                    <div class="h-full bg-emerald-400 transition-all duration-300" style="width: {{ min(100, round($this->grossRevenue / $activeShift->target_income * 100)) }}%"></div>
                </div>
            @endif
        </div>

        {{-- TRIP QUICK-LOGGER --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Catat Trip</h3>

            <form wire:submit="logTrip" class="space-y-3">
                <div>
                    <label for="fare_amount" class="block text-sm font-medium text-gray-800 mb-1">Argo / Ongkir (Rp)</label>
                    <input id="fare_amount" type="number" inputmode="numeric" autocomplete="off"
                        wire:model="fare_amount"
                        class="w-full min-h-[48px] rounded-lg border-gray-300 text-2xl py-4 px-4 focus:border-indigo-700 focus:ring-indigo-700"
                        placeholder="0">
                    @error('fare_amount')
                        <p class="text-red-800 text-sm font-medium mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="tip_cash" class="block text-sm font-medium text-gray-800 mb-1">Tip Tunai</label>
                        <input id="tip_cash" type="number" inputmode="numeric" autocomplete="off"
                            wire:model="tip_cash"
                            class="w-full min-h-[48px] rounded-lg border-gray-300 text-xl py-3 px-3 focus:border-indigo-700 focus:ring-indigo-700"
                            placeholder="0">
                    </div>
                    <div>
                        <label for="tip_app" class="block text-sm font-medium text-gray-800 mb-1">Tip Aplikasi</label>
                        <input id="tip_app" type="number" inputmode="numeric" autocomplete="off"
                            wire:model="tip_app"
                            class="w-full min-h-[48px] rounded-lg border-gray-300 text-xl py-3 px-3 focus:border-indigo-700 focus:ring-indigo-700"
                            placeholder="0">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="points_earned" class="block text-sm font-medium text-gray-800 mb-1">Poin/Berlian</label>
                        <input id="points_earned" type="number" inputmode="numeric" autocomplete="off"
                            wire:model="points_earned"
                            class="w-full min-h-[48px] rounded-lg border-gray-300 text-xl py-3 px-3 focus:border-indigo-700 focus:ring-indigo-700"
                            placeholder="0">
                    </div>
                    <div>
                        <label for="order_id" class="block text-sm font-medium text-gray-800 mb-1">No. Order (opsional)</label>
                        <input id="order_id" type="text" autocomplete="off"
                            wire:model="order_id"
                            class="w-full min-h-[48px] rounded-lg border-gray-300 text-sm py-3 px-3 focus:border-indigo-700 focus:ring-indigo-700"
                            placeholder="#12345">
                    </div>
                </div>

                <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="logTrip"
                    class="w-full min-h-[48px] bg-emerald-800 hover:bg-emerald-900 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-lg py-4 rounded-lg shadow transition">
                    <span wire:loading.remove wire:target="logTrip">✅ Catat Trip</span>
                    <span wire:loading wire:target="logTrip">Menyimpan…</span>
                </button>
            </form>
        </div>

        {{-- RECENT TRIPS --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">Trip Terbaru</h3>
            @if ($this->trips->isEmpty())
                <p class="text-gray-500 text-sm py-2">Belum ada trip tercatat di shift ini.</p>
            @else
                <ul class="divide-y divide-gray-100">
                    @foreach ($this->trips->take(5) as $trip)
                        <li class="py-2 flex justify-between text-sm">
                            <span class="text-gray-700">{{ $trip->created_at->format('H:i') }} @if($trip->order_id) · {{ $trip->order_id }} @endif</span>
                            <span class="font-medium text-gray-900">Rp{{ number_format($trip->totalIncome(), 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        {{-- END SHIFT --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Akhiri Shift</h3>
            <form wire:submit="endShift" class="space-y-4">
                <div>
                    <label for="end_odometer" class="block text-sm font-medium text-gray-800 mb-1">Odometer Akhir (KM)</label>
                    <input id="end_odometer" type="number" inputmode="numeric" autocomplete="off"
                        wire:model="end_odometer"
                        class="w-full min-h-[48px] rounded-lg border-gray-300 text-2xl py-4 px-4 focus:border-indigo-700 focus:ring-indigo-700"
                        placeholder="0">
                    @error('end_odometer')
                        <p class="text-red-800 text-sm font-medium mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit"
                    wire:confirm="Yakin mau akhiri shift sekarang?"
                    wire:loading.attr="disabled"
                    wire:target="endShift"
                    class="w-full min-h-[48px] bg-red-800 hover:bg-red-900 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-lg py-4 rounded-lg shadow transition">
                    <span wire:loading.remove wire:target="endShift">🏁 Akhiri Shift</span>
                    <span wire:loading wire:target="endShift">Mengakhiri…</span>
                </button>
            </form>
        </div>
    @endif
</div>
