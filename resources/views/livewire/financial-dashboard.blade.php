<div class="max-w-xl mx-auto space-y-4">
    @if ($this->activeShiftMetrics)
        @php $m = $this->activeShiftMetrics; @endphp
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6" aria-live="polite" aria-atomic="true">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">📊 Shift Berjalan</h3>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs text-gray-600">Net Profit</p>
                    <p class="text-xl font-bold font-mono {{ $m['net_profit'] >= 0 ? 'text-emerald-800' : 'text-red-800' }}">
                        Rp{{ number_format($m['net_profit'], 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-600">Pengeluaran</p>
                    <p class="text-xl font-bold font-mono text-gray-900">Rp{{ number_format($m['operational_cost'], 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="mt-4 grid grid-cols-3 gap-3">
                <div>
                    <p class="text-xs text-gray-600 mb-1">Cost/KM</p>
                    <p class="text-base font-semibold font-mono text-gray-900">
                        {{ $m['cost_per_km'] !== null ? 'Rp'.number_format($m['cost_per_km'], 0, ',', '.') : '—' }}
                    </p>
                    <x-metric-badge :level="$m['cost_per_km_level']" />
                </div>
                <div>
                    <p class="text-xs text-gray-600 mb-1">KM/Liter</p>
                    <p class="text-base font-semibold font-mono text-gray-900">
                        {{ $m['fuel_efficiency_km_l'] !== null ? number_format($m['fuel_efficiency_km_l'], 1).' km/L' : '—' }}
                    </p>
                    <x-metric-badge :level="$m['fuel_efficiency_level']" />
                </div>
                <div>
                    <p class="text-xs text-gray-600 mb-1">% Target</p>
                    <p class="text-base font-semibold font-mono text-gray-900">
                        {{ $m['target_achievement_pct'] !== null ? number_format($m['target_achievement_pct'], 0).'%' : '—' }}
                    </p>
                    <x-metric-badge :level="$m['target_achievement_level']" />
                </div>
            </div>

            <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between text-xs text-gray-600 font-mono">
                <span>{{ number_format($m['distance_km'], 1) }} km ditempuh</span>
                <span>{{ number_format($m['hours_worked'], 1) }} jam jalan</span>
            </div>
        </div>
    @endif

    @foreach ([['📈 Ringkasan Hari Ini', $this->todayMetrics], ['🗓️ Ringkasan Minggu Ini', $this->weekMetrics]] as [$title, $t])
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6" aria-live="polite" aria-atomic="true">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">{{ $title }}</h3>

            @if ($t['shift_count'] === 0)
                <p class="text-gray-500 text-sm py-2">Belum ada shift pada periode ini.</p>
            @else
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs text-gray-600">Gross Revenue</p>
                        <p class="text-xl font-bold font-mono text-indigo-800">Rp{{ number_format($t['gross_revenue'], 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600">Net Profit</p>
                        <p class="text-xl font-bold font-mono {{ $t['net_profit'] >= 0 ? 'text-emerald-800' : 'text-red-800' }}">
                            Rp{{ number_format($t['net_profit'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-3">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Cost/KM</p>
                        <p class="text-base font-semibold font-mono text-gray-900">
                            {{ $t['cost_per_km'] !== null ? 'Rp'.number_format($t['cost_per_km'], 0, ',', '.') : '—' }}
                        </p>
                        <x-metric-badge :level="$t['cost_per_km_level']" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 mb-1">KM/Liter</p>
                        <p class="text-base font-semibold font-mono text-gray-900">
                            {{ $t['fuel_efficiency_km_l'] !== null ? number_format($t['fuel_efficiency_km_l'], 1).' km/L' : '—' }}
                        </p>
                        <x-metric-badge :level="$t['fuel_efficiency_level']" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 mb-1">% Target</p>
                        <p class="text-base font-semibold font-mono text-gray-900">
                            {{ $t['target_achievement_pct'] !== null ? number_format($t['target_achievement_pct'], 0).'%' : '—' }}
                        </p>
                        <x-metric-badge :level="$t['target_achievement_level']" />
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between text-xs text-gray-600 font-mono">
                    <span>{{ $t['shift_count'] }} shift · {{ number_format($t['distance_km'], 1) }} km</span>
                    <span>Pengeluaran Rp{{ number_format($t['operational_cost'], 0, ',', '.') }}</span>
                </div>
            @endif
        </div>
    @endforeach
</div>
