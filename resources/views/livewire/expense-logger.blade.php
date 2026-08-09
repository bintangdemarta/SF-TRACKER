<div class="max-w-xl mx-auto">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Catat Pengeluaran</h3>

        @if ($warningMessage)
            <div class="mb-4 rounded-lg bg-amber-50 border border-amber-300 p-3 text-amber-800 text-sm flex gap-2">
                <span>⚠️</span>
                <span>{{ $warningMessage }}</span>
            </div>
        @endif

        <form wire:submit="logExpense" class="space-y-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                <select wire:model.live="category_id"
                    class="w-full rounded-lg border-gray-300 py-3 px-3 text-lg focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">— Pilih kategori —</option>
                    @foreach ($categories->groupBy('type') as $type => $group)
                        <optgroup label="{{ match ($type) {
                            'bbm' => 'BBM',
                            'mikro' => 'Biaya Mikro',
                            'pemeliharaan' => 'Pemeliharaan',
                            'sinking_fund' => 'Sinking Fund',
                            default => ucfirst($type),
                        } }}">
                            @foreach ($group as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
                @error('category_id') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nominal (Rp)</label>
                <input type="number" inputmode="numeric" wire:model="amount"
                    class="w-full rounded-lg border-gray-300 text-2xl py-4 px-4 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="0">
                @error('amount') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            @if ($this->showOdometerField)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Odometer Saat Isi (KM)</label>
                    <input type="number" inputmode="numeric" wire:model="odometer"
                        class="w-full rounded-lg border-gray-300 text-xl py-3 px-3 focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="opsional">
                    @error('odometer') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Dibayar Pakai</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center justify-center gap-2 rounded-lg border-2 py-3 cursor-pointer transition {{ $payment_source === 'cash' ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-600' }}">
                        <input type="radio" wire:model.live="payment_source" value="cash" class="sr-only">
                        💵 Tunai
                    </label>
                    <label class="flex items-center justify-center gap-2 rounded-lg border-2 py-3 cursor-pointer transition {{ $payment_source === 'digital_balance' ? 'border-indigo-600 bg-indigo-50 text-indigo-700' : 'border-gray-200 text-gray-600' }}">
                        <input type="radio" wire:model.live="payment_source" value="digital_balance" class="sr-only">
                        📱 Saldo Digital
                    </label>
                </div>
                @error('payment_source') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm text-gray-700">
                <input type="checkbox" wire:model="is_reimbursable" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                Bisa direimburse (mis. uang talangan resto)
            </label>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catatan (opsional)</label>
                <input type="text" wire:model="notes"
                    class="w-full rounded-lg border-gray-300 text-sm py-3 px-3 focus:border-indigo-500 focus:ring-indigo-500"
                    placeholder="mis. isi bensin di SPBU Sudirman">
                @error('notes') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <button type="submit"
                class="w-full bg-amber-600 hover:bg-amber-700 text-white font-semibold text-lg py-4 rounded-lg shadow transition">
                💸 Catat Pengeluaran
            </button>
        </form>
    </div>

    @if ($this->recentExpenses->isNotEmpty())
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mt-4">
            <h3 class="text-sm font-semibold text-gray-500 uppercase mb-3">Pengeluaran Terbaru</h3>
            <ul class="divide-y divide-gray-100">
                @foreach ($this->recentExpenses as $expense)
                    <li class="py-2 flex justify-between items-center text-sm">
                        <div>
                            <p class="text-gray-800 font-medium">{{ $expense->category->name }}</p>
                            <p class="text-gray-500 text-xs">
                                {{ $expense->created_at->format('d M H:i') }} ·
                                {{ $expense->payment_source === 'cash' ? 'Tunai' : 'Digital' }}
                                @if ($expense->is_reimbursable)
                                    @if ($expense->reimbursed_at)
                                        · <span class="text-emerald-600">sudah direimburse</span>
                                    @else
                                        · <span class="text-amber-600">menunggu reimburse</span>
                                    @endif
                                @endif
                            </p>
                        </div>
                        <span class="font-medium text-gray-800">Rp{{ number_format($expense->amount, 0, ',', '.') }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
