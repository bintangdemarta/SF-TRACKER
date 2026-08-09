@props(['description' => ''])
@php
    $schema = [
        '@context' => 'https://schema.org',
        '@type' => ['WebApplication', 'SoftwareApplication'],
        'name' => config('app.name'),
        'alternateName' => 'ShopeeFood Driver Finance Tracker',
        'url' => route('landing'),
        'description' => $description,
        'applicationCategory' => 'FinanceApplication',
        'operatingSystem' => 'All',
        'offers' => [
            '@type' => 'Offer',
            'price' => '0',
            'priceCurrency' => 'IDR',
        ],
        'inLanguage' => 'id-ID',
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
