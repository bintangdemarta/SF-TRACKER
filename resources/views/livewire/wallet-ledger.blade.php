<div class="max-w-xl mx-auto">
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">Saldo Dompet</h3>

        <div class="grid grid-cols-2 gap-3 mb-4">
            <div class="rounded-lg bg-emerald-50 border border-emerald-200 p-4">
                <p class="text-xs font-medium text-emerald-700 uppercase">Kas Tunai Fisik</p>
                <p class="text-2xl font-bold text-emerald-800 mt-1">
                    Rp{{ number_format($cashBalance, 0, ',', '.') }}
                </p>
                @if ($cashBalance < 0)
                    <p class="text-xs text-red-600 mt-1">Defisit — kas fisik minus</p>
                @endif
            </div>
            <div class="rounded-lg bg-indigo-50 border border-indigo-200 p-4">
                <p class="text-xs font-medium text-indigo-700 uppercase">Saldo Digital</p>
                <p class="text-2xl font-bold text-indigo-800 mt-1">
                    Rp{{ number_format($digitalBalance, 0, ',', '.') }}
                </p>
            </div>
        </div>

        <form wire:submit="transferToCash" class="flex gap-2">
            <input type="number" inputmode="numeric" wire:model="transfer_amount"
                class="flex-1 rounded-lg border-gray-300 py-3 px-3 focus:border-indigo-500 focus:ring-indigo-500"
                placeholder="Nominal tarik ke tunai">
            <button type="submit"
                class="bg-gray-800 hover:bg-gray-900 text-white font-semibold px-4 rounded-lg shadow transition whitespace-nowrap">
                Tarik ke Tunai
            </button>
        </form>
        @error('transfer_amount') <p class="text-red-600 text-sm mt-1">{{ $message }}</p> @enderror
    </div>
</div>
