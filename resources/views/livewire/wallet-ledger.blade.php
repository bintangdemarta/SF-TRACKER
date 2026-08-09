<div class="max-w-xl mx-auto">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Saldo Dompet</h3>

        <div class="grid grid-cols-2 gap-3 mb-4" aria-live="polite" aria-atomic="true">
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4">
                <p class="text-xs font-medium text-emerald-900 uppercase tracking-wide">Kas Tunai Fisik</p>
                <p class="text-2xl font-bold text-emerald-800 mt-1">
                    Rp{{ number_format($cashBalance, 0, ',', '.') }}
                </p>
                @if ($cashBalance < 0)
                    <p class="text-xs font-semibold text-red-800 mt-1">⚠️ Defisit — kas fisik minus</p>
                @endif
            </div>
            <div class="rounded-lg bg-indigo-50 border border-indigo-200 p-4">
                <p class="text-xs font-medium text-indigo-900 uppercase tracking-wide">Saldo Digital</p>
                <p class="text-2xl font-bold text-indigo-800 mt-1">
                    Rp{{ number_format($digitalBalance, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <form wire:submit="transferToCash" class="flex gap-2">
            <label for="transfer_amount" class="sr-only">Nominal tarik ke tunai</label>
            <input id="transfer_amount" type="number" inputmode="numeric" autocomplete="off"
                wire:model="transfer_amount"
                class="flex-1 min-h-[48px] rounded-lg border-gray-300 py-3 px-3 text-base focus:border-indigo-600 focus:ring-indigo-600"
                placeholder="Nominal tarik ke tunai">
            <button type="submit"
                wire:loading.attr="disabled"
                wire:target="transferToCash"
                class="min-h-[48px] min-w-[48px] bg-gray-800 hover:bg-gray-950 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold px-4 rounded-lg shadow transition whitespace-nowrap">
                <span wire:loading.remove wire:target="transferToCash">Tarik ke Tunai</span>
                <span wire:loading wire:target="transferToCash">Memproses…</span>
            </button>
        </form>
        @error('transfer_amount')
            <p class="text-red-800 text-sm font-medium mt-1" role="alert">{{ $message }}</p>
        @enderror
    </div>
</div>
