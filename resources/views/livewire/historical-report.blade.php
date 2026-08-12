<div class="max-w-4xl mx-auto space-y-4">
    {{-- Filter --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">🗂️ Filter Periode</h3>

        <div class="flex flex-wrap items-end gap-3">
            <div>
                <label for="period" class="block text-xs text-gray-600 mb-1">Periode</label>
                <select id="period" wire:model.live="period"
                    class="rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="daily">Harian</option>
                    <option value="weekly">Mingguan</option>
                    <option value="monthly">Bulanan</option>
                    <option value="custom">Custom</option>
                </select>
            </div>

            @if ($period === 'custom')
                <div>
                    <label for="customFrom" class="block text-xs text-gray-600 mb-1">Dari</label>
                    <input id="customFrom" type="date" wire:model.live="customFrom"
                        class="rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
                <div>
                    <label for="customTo" class="block text-xs text-gray-600 mb-1">Sampai</label>
                    <input id="customTo" type="date" wire:model.live="customTo"
                        class="rounded-md border-gray-300 shadow-sm text-sm focus:border-indigo-500 focus:ring-indigo-500" />
                </div>
            @endif

            @if ($this->range)
                <div class="flex gap-2 ms-auto">
                    <a href="{{ $this->exportUrl('csv') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        ⬇️ CSV
                    </a>
                    <a href="{{ $this->exportUrl('xlsx') }}"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        ⬇️ Excel
                    </a>
                </div>
            @endif
        </div>

        @if ($rangeError)
            <p class="mt-3 text-sm text-red-700" role="alert">{{ $rangeError }}</p>
        @endif
    </div>

    {{-- Summary --}}
    @if ($this->summary)
        @php $s = $this->summary; @endphp
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6" aria-live="polite" aria-atomic="true">
            <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">📊 Ringkasan Periode</h3>

            @if ($s['shift_count'] === 0)
                <p class="text-gray-500 text-sm py-2">Belum ada shift pada periode ini.</p>
            @else
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p class="text-xs text-gray-600">Gross Revenue</p>
                        <p class="text-xl font-bold font-mono text-indigo-800">Rp{{ number_format($s['gross_revenue'], 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-600">Net Profit</p>
                        <p class="text-xl font-bold font-mono {{ $s['net_profit'] >= 0 ? 'text-emerald-800' : 'text-red-800' }}">
                            Rp{{ number_format($s['net_profit'], 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div class="mt-4 grid grid-cols-3 gap-3">
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Rata-rata Cost/KM</p>
                        <p class="text-base font-semibold font-mono text-gray-900">
                            {{ $s['cost_per_km'] !== null ? 'Rp'.number_format($s['cost_per_km'], 0, ',', '.') : '—' }}
                        </p>
                        <x-metric-badge :level="$s['cost_per_km_level']" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 mb-1">Efisiensi BBM</p>
                        <p class="text-base font-semibold font-mono text-gray-900">
                            {{ $s['fuel_efficiency_km_l'] !== null ? number_format($s['fuel_efficiency_km_l'], 1).' km/L' : '—' }}
                        </p>
                        <x-metric-badge :level="$s['fuel_efficiency_level']" />
                    </div>
                    <div>
                        <p class="text-xs text-gray-600 mb-1">% Target</p>
                        <p class="text-base font-semibold font-mono text-gray-900">
                            {{ $s['target_achievement_pct'] !== null ? number_format($s['target_achievement_pct'], 0).'%' : '—' }}
                        </p>
                        <x-metric-badge :level="$s['target_achievement_level']" />
                    </div>
                </div>

                <div class="mt-3 pt-3 border-t border-gray-200 flex justify-between text-xs text-gray-600 font-mono">
                    <span>{{ $s['shift_count'] }} shift · {{ number_format($s['distance_km'], 1) }} km total</span>
                    <span>Pengeluaran Rp{{ number_format($s['operational_cost'], 0, ',', '.') }}</span>
                </div>
            @endif
        </div>
    @endif

    {{-- Shift history table --}}
    @if ($this->shifts && $this->shifts->count() > 0)
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal</th>
                            <th scope="col" class="px-4 py-2 text-left text-xs font-semibold text-gray-600 uppercase">Jam</th>
                            <th scope="col" class="px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Jarak</th>
                            <th scope="col" class="px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Net Profit</th>
                            <th scope="col" class="px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">Cost/KM</th>
                            <th scope="col" class="px-4 py-2 text-right text-xs font-semibold text-gray-600 uppercase">KM/L</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($this->shifts as $row)
                            <tr>
                                <td class="px-4 py-2 font-mono text-gray-900">{{ $row['tanggal'] }}</td>
                                <td class="px-4 py-2 font-mono text-gray-600">{{ $row['jam_mulai'] }}–{{ $row['jam_selesai'] }}</td>
                                <td class="px-4 py-2 font-mono text-right text-gray-900">{{ number_format($row['jarak_km'], 1) }} km</td>
                                <td class="px-4 py-2 font-mono text-right {{ $row['net_profit'] >= 0 ? 'text-emerald-800' : 'text-red-800' }}">
                                    Rp{{ number_format($row['net_profit'], 0, ',', '.') }}
                                </td>
                                <td class="px-4 py-2 font-mono text-right text-gray-900">
                                    {{ $row['cost_per_km'] !== null ? 'Rp'.number_format($row['cost_per_km'], 0, ',', '.') : '—' }}
                                </td>
                                <td class="px-4 py-2 font-mono text-right text-gray-900">
                                    {{ $row['fuel_efficiency_km_l'] !== null ? number_format($row['fuel_efficiency_km_l'], 1) : '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-4 py-3 border-t border-gray-200">
                {{ $this->shifts->links() }}
            </div>
        </div>
    @endif
</div>
