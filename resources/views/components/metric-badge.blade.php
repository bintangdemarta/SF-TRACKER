@php
    $styles = match ($level) {
        'good' => ['bg' => 'bg-emerald-50', 'text' => 'text-emerald-900', 'border' => 'border-emerald-400', 'dot' => 'bg-emerald-600', 'label' => 'Irit'],
        'ok' => ['bg' => 'bg-amber-50', 'text' => 'text-amber-900', 'border' => 'border-amber-400', 'dot' => 'bg-amber-600', 'label' => 'Sedang'],
        'bad' => ['bg' => 'bg-red-50', 'text' => 'text-red-900', 'border' => 'border-red-400', 'dot' => 'bg-red-700', 'label' => 'Boros'],
        default => ['bg' => 'bg-gray-100', 'text' => 'text-gray-700', 'border' => 'border-gray-300', 'dot' => 'bg-gray-400', 'label' => 'Belum ada data'],
    };
@endphp

<span class="inline-flex items-center gap-1 rounded-full border {{ $styles['border'] }} {{ $styles['bg'] }} {{ $styles['text'] }} px-2 py-0.5 text-[11px] font-semibold uppercase tracking-wide">
    <span class="h-1.5 w-1.5 rounded-full {{ $styles['dot'] }}" aria-hidden="true"></span>
    {{ $label ?? $styles['label'] }}
</span>
