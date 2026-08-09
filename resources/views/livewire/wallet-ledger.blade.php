<div class="max-w-xl mx-auto space-y-4">
    {{-- SALDO (read-only, tetap di alur scroll) --}}
    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Saldo Dompet</h3>

        <div class="grid grid-cols-2 gap-3" aria-live="polite" aria-atomic="true">
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
    </div>

    {{-- TARIK KE TUNAI — bottom sheet --}}
    <div x-show="$store.sheet.isOpen('wallet')" x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="translate-y-full opacity-0"
        x-transition:enter-end="translate-y-0 opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-y-0 opacity-100"
        x-transition:leave-end="translate-y-full opacity-0"
        x-on:wallet-updated.window="$store.sheet.close()"
        x-on:keydown.escape.window="$store.sheet.close()"
        class="fixed inset-x-0 bottom-0 z-50 bg-white rounded-t-2xl shadow-2xl max-h-[85vh] overflow-y-auto pb-[calc(1rem+env(safe-area-inset-bottom))]"
        role="dialog" aria-modal="true" aria-labelledby="wallet-sheet-title">
        <div class="sticky top-0 bg-white flex items-center justify-between px-6 pt-4 pb-2 border-b border-gray-100">
            <h3 id="wallet-sheet-title" class="text-lg font-semibold text-gray-900">Tarik Saldo ke Tunai</h3>
            <button type="button" x-on:click="$store.sheet.close()" aria-label="Tutup"
                class="min-h-[44px] min-w-[44px] flex items-center justify-center text-gray-500 hover:text-gray-800 active:scale-90 transition">✕</button>
        </div>

        <form wire:submit="transferToCash" class="px-6 py-4 space-y-4">
            <div>
                <label for="transfer_amount" class="block text-sm font-medium text-gray-800 mb-1">Nominal (Rp)</label>
                <input id="transfer_amount" type="number" inputmode="numeric" autocomplete="off"
                    wire:model="transfer_amount"
                    class="w-full min-h-[48px] rounded-lg border-gray-300 text-2xl py-4 px-4 focus:border-indigo-700 focus:ring-indigo-700"
                    placeholder="0">
                @error('transfer_amount')
                    <p class="text-red-800 text-sm font-medium mt-1" role="alert">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit"
                wire:loading.attr="disabled"
                wire:target="transferToCash"
                class="w-full min-h-[48px] bg-gray-800 hover:bg-gray-950 active:scale-95 disabled:opacity-50 disabled:cursor-not-allowed text-white font-semibold text-lg py-4 rounded-lg shadow transition">
                <span wire:loading.remove wire:target="transferToCash">🔁 Tarik ke Tunai</span>
                <span wire:loading wire:target="transferToCash">Memproses…</span>
            </button>
        </form>
    </div>
</div>
