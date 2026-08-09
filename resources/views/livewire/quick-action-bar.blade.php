<nav
    class="fixed inset-x-0 bottom-0 z-30 bg-white border-t border-gray-200 shadow-[0_-2px_12px_rgba(0,0,0,0.12)] pb-[env(safe-area-inset-bottom)]"
    aria-label="Aksi cepat"
>
    @if (! $this->hasActiveShift)
        <button type="button"
            x-on:click="$store.sheet.toggle('shift')"
            x-bind:aria-pressed="$store.sheet.isOpen('shift').toString()"
            class="w-full min-h-[56px] flex items-center justify-center gap-2 bg-indigo-800 hover:bg-indigo-900 active:scale-95 text-white font-semibold text-base transition">
            <span aria-hidden="true">🚀</span> Mulai Shift
        </button>
    @else
        <div class="grid grid-cols-4">
            <button type="button"
                x-on:click="$store.sheet.toggle('trip')"
                x-bind:aria-pressed="$store.sheet.isOpen('trip').toString()"
                class="min-h-[56px] flex flex-col items-center justify-center gap-0.5 bg-emerald-800 hover:bg-emerald-900 active:scale-95 text-white transition">
                <span class="text-xl leading-none" aria-hidden="true">✅</span>
                <span class="text-[11px] font-semibold leading-none">Trip</span>
            </button>
            <button type="button"
                x-on:click="$store.sheet.toggle('expense')"
                x-bind:aria-pressed="$store.sheet.isOpen('expense').toString()"
                class="min-h-[56px] flex flex-col items-center justify-center gap-0.5 bg-amber-800 hover:bg-amber-900 active:scale-95 text-white transition">
                <span class="text-xl leading-none" aria-hidden="true">💸</span>
                <span class="text-[11px] font-semibold leading-none">Keluar</span>
            </button>
            <button type="button"
                x-on:click="$store.sheet.toggle('wallet')"
                x-bind:aria-pressed="$store.sheet.isOpen('wallet').toString()"
                class="min-h-[56px] flex flex-col items-center justify-center gap-0.5 bg-gray-800 hover:bg-gray-950 active:scale-95 text-white transition">
                <span class="text-xl leading-none" aria-hidden="true">🔁</span>
                <span class="text-[11px] font-semibold leading-none">Tunai</span>
            </button>
            <button type="button"
                x-on:click="$store.sheet.toggle('endshift')"
                x-bind:aria-pressed="$store.sheet.isOpen('endshift').toString()"
                class="min-h-[56px] flex flex-col items-center justify-center gap-0.5 bg-red-800 hover:bg-red-900 active:scale-95 text-white transition">
                <span class="text-xl leading-none" aria-hidden="true">🏁</span>
                <span class="text-[11px] font-semibold leading-none">Akhiri</span>
            </button>
        </div>
    @endif
</nav>
