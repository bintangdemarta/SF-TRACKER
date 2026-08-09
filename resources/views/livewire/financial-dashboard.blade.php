<div class="max-w-xl mx-auto space-y-4">
    @if ($this->activeShiftMetrics)
        @php $m = $this->activeShiftMetrics; @endphp
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">📊 Shift Berjalan</h3>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs text-gray-500">Net Profit</p>
                    <p class="text-xl font-bold {{ $m['net_profit'] >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                        Rp{{ number_format($m['net_profit'], 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Pengeluaran</p>
                    <p class="text-xl font-bold text-gray-800">Rp{{ number_format($m['operational_cost'], 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Cost / KM</p>
                    <p class="text-lg font-semibold text-gray-800">
                        {{ $m['cost_per_km'] !== null ? 'Rp'.number_format($m['cost_per_km'], 0, ',', '.') : '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Rp / Jam</p>
                    <p class="text-lg font-semibold text-gray-800">
                        {{ $m['hourly_rate'] !== null ? 'Rp'.number_format($m['hourly_rate'], 0, ',', '.') : '—' }}
                    </p>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between text-xs text-gray-500">
                <span>{{ number_format($m['distance_km'], 1) }} km ditempuh</span>
                <span>{{ number_format($m['hours_worked'], 1) }} jam jalan</span>
            </div>
        </div>
    @endif

    @php $t = $this->todayMetrics; @endphp
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">📈 Ringkasan Hari Ini</h3>

        @if ($t['shift_count'] === 0)
            <p class="text-gray-500 text-sm">Belum ada shift hari ini.</p>
        @else
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs text-gray-500">Gross Revenue</p>
                    <p class="text-xl font-bold text-indigo-700">Rp{{ number_format($t['gross_revenue'], 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Net Profit</p>
                    <p class="text-xl font-bold {{ $t['net_profit'] >= 0 ? 'text-emerald-700' : 'text-red-600' }}">
                        Rp{{ number_format($t['net_profit'], 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Cost / KM</p>
                    <p class="text-lg font-semibold text-gray-800">
                        {{ $t['cost_per_km'] !== null ? 'Rp'.number_format($t['cost_per_km'], 0, ',', '.') : '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Rp / Jam</p>
                    <p class="text-lg font-semibold text-gray-800">
                        {{ $t['hourly_rate'] !== null ? 'Rp'.number_format($t['hourly_rate'], 0, ',', '.') : '—' }}
                    </p>
                </div>
            </div>
            <div class="mt-3 pt-3 border-t border-gray-100 flex justify-between text-xs text-gray-500">
                <span>{{ $t['shift_count'] }} shift · {{ number_format($t['distance_km'], 1) }} km</span>
                <span>Pengeluaran Rp{{ number_format($t['operational_cost'], 0, ',', '.') }}</span>
            </div>
        @endif
    </div>
</div>
