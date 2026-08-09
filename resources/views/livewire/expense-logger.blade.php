<div class="max-w-xl mx-auto">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Catat Pengeluaran</h3>

        @if ($warningMessage)
            <div class="mb-4 rounded-lg bg-amber-50 border border-amber-400 p-3 text-amber-900 text-sm flex gap-2" role="alert" aria-live="assertive">
                <span aria-hidden="true">⚠️</span>
                <span class="font-medium">{{ $warningMessage }}</span>
            </div>
        @endif

        <form wire:submit="logExpense" class="space-y-3">
            <div>
                <label for="category_id" class="block text-sm font-medium text-gray-800 mb-1">Kategori</label>
                <select id="category_id" wire:model.live="category_id" autocomplete="off"
                    class="w-full min-h-[48px] rounded-lg border-gray-300 py-3 px-3 text-lg focus:border-indigo-700 focus:ring-indigo-700">
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
                @error('category_id')
                    <p class="text-red-800 text-sm font-medium mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="amount" class="block text-sm font-medium text-gray-800 mb-1">Nominal (Rp)</label>
                <input id="amount" type="number" inputmode="numeric" autocomplete="off" wire:model="amount"
                    class="w-full min-h-[48px] rounded-lg border-gray-300 text-2xl py-4 px-4 focus:border-indigo-700 focus:ring-indigo-700"
                    placeholder="0">
                @error('amount')
                    <p class="text-red-800 text-sm font-medium mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            @if ($this->showOdometerField)
                <div>
                    <label for="odometer" class="block text-sm font-medium text-gray-800 mb-1">Odometer Saat Isi (KM)</label>
                    <input id="odometer" type="number" inputmode="numeric" autocomplete="off" wire:model="odometer"
                        class="w-full min-h-[48px] rounded-lg border-gray-300 text-xl py-3 px-3 focus:border-indigo-700 focus:ring-indigo-700"
                        placeholder="opsional">
                    @error('odometer')
                        <p class="text-red-800 text-sm font-medium mt-1" role="alert">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div role="radiogroup" aria-labelledby="payment_source_label">
                <p id="payment_source_label" class="block text-sm font-medium text-gray-800 mb-2">Dibayar Pakai</p>
                <div class="grid grid-cols-2 gap-3">
                    <label class="flex items-center justify-center gap-2 min-h-[48px] rounded-lg border-2 cursor-pointer transition {{ $payment_source === 'cash' ? 'border-indigo-700 bg-indigo-50 text-indigo-900' : 'border-gray-300 text-gray-700' }}">
                        <input type="radio" wire:model.live="payment_source" value="cash" class="sr-only">
                        <span aria-hidden="true">💵</span> Tunai
                    </label>
                    <label class="flex items-center justify-center gap-2 min-h-[48px] rounded-lg border-2 cursor-pointer transition {{ $payment_source === 'digital_balance' ? 'border-indigo-700 bg-indigo-50 text-indigo-900' : 'border-gray-300 text-gray-700' }}">
                        <input type="radio" wire:model.live="payment_source" value="digital_balance" class="sr-only">
                        <span aria-hidden="true">📱</span> Saldo Digital
                    </label>
                </div>
                @error('payment_source')
                    <p class="text-red-800 text-sm font-medium mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 min-h-[44px] text-sm text-gray-800">
                <input type="checkbox" wire:model="is_reimbursable"
                    class="h-5 w-5 rounded border-gray-400 text-indigo-700 focus:ring-indigo-700">
                Bisa direimburse (mis. uang talangan resto)
            </label>

            <div>
                <label for="notes" class="block text-sm font-medium text-gray-800 mb-1">Catatan (opsional)</label>
                <input id="notes" type="text" autocomplete="off" wire:model="notes"
                    class="w-full min-h-[48px] rounded-lg border-gray-300 text-sm py-3 px-3 focus:border-indigo-700 focus:ring-indigo-700"
                    placeholder="mis. isi bensin di SPBU Sudirman">
                @error('notes')
                    <p class="text-red-800 text-sm font-medium mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                wire:loading.attr="disabled"
                wire:target="logExpense"
                class="w-full min-h-[48px] bg-amber-800 hover:bg-amber-900 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-lg py-4 rounded-lg shadow transition">
                <span wire:loading.remove wire:target="logExpense">💸 Catat Pengeluaran</span>
                <span wire:loading wire:target="logExpense">Menyimpan…</span>
            </button>
        </form>
    </div>

    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mt-4">
        <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-3">Pengeluaran Terbaru</h3>
        @if ($this->recentExpenses->isEmpty())
            <p class="text-gray-500 text-sm py-2">Belum ada pengeluaran tercatat.</p>
        @else
            <ul class="divide-y divide-gray-100">
                @foreach ($this->recentExpenses as $expense)
                    <li class="py-2 flex justify-between items-center text-sm">
                        <div>
                            <p class="text-gray-900 font-medium">{{ $expense->category->name }}</p>
                            <p class="text-gray-600 text-xs">
                                {{ $expense->created_at->format('d M H:i') }} ·
                                {{ $expense->payment_source === 'cash' ? 'Tunai' : 'Digital' }}
                                @if ($expense->is_reimbursable)
                                    @if ($expense->reimbursed_at)
                                        · <span class="text-emerald-800 font-medium">sudah direimburse</span>
                                    @else
                                        · <span class="text-amber-800 font-medium">menunggu reimburse</span>
                                    @endif
                                @endif
                            </p>
                        </div>
                        <span class="font-medium text-gray-900">Rp{{ number_format($expense->amount, 0, ',', '.') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
