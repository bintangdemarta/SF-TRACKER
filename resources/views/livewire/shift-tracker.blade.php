<div class="max-w-xl mx-auto space-y-4">
    @if (! $activeShift)
        {{-- START SHIFT --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Mulai Shift</h3>

            <form wire:submit="startShift" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Odometer Awal (KM)</label>
                    <input type="number" inputmode="numeric" wire:model="start_odometer"
                        class="w-full rounded-lg border-gray-300 text-2xl py-4 px-4 focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="0">
                    @error('start_odometer') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Target Pendapatan Hari Ini (Rp)</label>
                    <input type="number" inputmode="numeric" wire:model="target_income"
                        class="w-full rounded-lg border-gray-300 text-2xl py-4 px-4 focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="0">
                    @error('target_income') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <button type="submit"
                    class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold text-lg py-4 rounded-lg shadow transition">
                    🚀 Mulai Shift
                </button>
            </form>
        </div>
    @else
        {{-- ACTIVE SHIFT SUMMARY --}}
        <div class="bg-indigo-600 text-white overflow-hidden shadow-sm rounded-lg p-6">
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-indigo-200 text-sm">Pendapatan Kotor</p>
                    <p class="text-3xl font-bold">Rp{{ number_format($this->grossRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="text-right">
                    <p class="text-indigo-200 text-sm">Target</p>
                    <p class="text-lg font-semibold">Rp{{ number_format($activeShift->target_income, 0, ',', '.') }}</p>
                </div>
            </div>
            <div class="mt-3 flex justify-between text-sm text-indigo-100">
                <span>{{ $this->tripCount }} trip</span>
                <span>Mulai {{ $activeShift->started_at->format('H:i') }} · odo {{ $activeShift->start_odometer }} km</span>
            </div>
            @if ($activeShift->target_income > 0)
                <div class="mt-3 h-2 bg-indigo-800 rounded-full overflow-hidden">
                    <div class="h-full bg-emerald-400" style="width: {{ min(100, round($this->grossRevenue / $activeShift->target_income * 100)) }}%"></div>
                </div>
            @endif
        </div>

        {{-- TRIP QUICK-LOGGER --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Catat Trip</h3>

            <form wire:submit="logTrip" class="space-y-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Argo / Ongkir (Rp)</label>
                    <input type="number" inputmode="numeric" wire:model="fare_amount"
                        class="w-full rounded-lg border-gray-300 text-2xl py-4 px-4 focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="0">
                    @error('fare_amount') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tip Tunai</label>
                        <input type="number" inputmode="numeric" wire:model="tip_cash"
                            class="w-full rounded-lg border-gray-300 text-xl py-3 px-3 focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tip Aplikasi</label>
                        <input type="number" inputmode="numeric" wire:model="tip_app"
                            class="w-full rounded-lg border-gray-300 text-xl py-3 px-3 focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="0">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Poin/Berlian</label>
                        <input type="number" inputmode="numeric" wire:model="points_earned"
                            class="w-full rounded-lg border-gray-300 text-xl py-3 px-3 focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="0">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">No. Order (opsional)</label>
                        <input type="text" wire:model="order_id"
                            class="w-full rounded-lg border-gray-300 text-sm py-3 px-3 focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="#12345">
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-lg py-4 rounded-lg shadow transition">
                    ✅ Catat Trip
                </button>
            </form>
        </div>

        {{-- RECENT TRIPS --}}
        @if ($this->trips->isNotEmpty())
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Trip Terbaru</h3>
                <ul class="divide-y divide-gray-100">
                    @foreach ($this->trips->take(5) as $trip)
                        <li class="py-2 flex justify-between text-sm">
                            <span class="text-gray-600">{{ $trip->created_at->format('H:i') }} @if($trip->order_id) · {{ $trip->order_id }} @endif</span>
                            <span class="font-medium text-gray-800">Rp{{ number_format($trip->totalIncome(), 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- END SHIFT --}}
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-lg font-semibold text-gray-800 mb-4">Akhiri Shift</h3>
            <form wire:submit="endShift" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Odometer Akhir (KM)</label>
                    <input type="number" inputmode="numeric" wire:model="end_odometer"
                        class="w-full rounded-lg border-gray-300 text-2xl py-4 px-4 focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="0">
                    @error('end_odometer') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <button type="submit"
                    wire:confirm="Yakin mau akhiri shift sekarang?"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold text-lg py-4 rounded-lg shadow transition">
                    🏁 Akhiri Shift
                </button>
            </form>
        </div>
    @endif
</div>
