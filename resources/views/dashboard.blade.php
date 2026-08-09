<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-8 pb-28">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-4">
            @livewire('financial-dashboard')
            @livewire('wallet-ledger')
            @livewire('shift-tracker')
            @livewire('expense-logger')
        </div>
    </div>

    {{-- Shared backdrop for whichever bottom sheet is open --}}
    <div
        x-show="$store.sheet.open !== null"
        x-cloak
        x-transition:enter="transition ease-out duration-150"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 bg-black/50 z-40"
        x-on:click="$store.sheet.close()"
        aria-hidden="true"
    ></div>

    @livewire('quick-action-bar')
</x-app-layout>
