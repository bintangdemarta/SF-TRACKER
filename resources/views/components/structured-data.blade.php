@props(['description' => ''])
@php
    $base = route('landing');
    $orgId = $base.'#organization';
    $webAppId = $base.'#webapp';
    $websiteId = $base.'#website';

    $schema = [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'Organization',
                '@id' => $orgId,
                'name' => config('app.name'),
                'url' => $base,
                'logo' => [
                    '@type' => 'ImageObject',
                    '@id' => $base.'#logo',
                    'url' => asset('images/og-cover.png'),
                ],
            ],
            [
                '@type' => ['WebApplication', 'SoftwareApplication'],
                '@id' => $webAppId,
                'name' => config('app.name'),
                'alternateName' => 'ShopeeFood Driver Finance Tracker',
                'url' => $base,
                'description' => $description,
                'applicationCategory' => 'FinanceApplication',
                'operatingSystem' => 'All',
                'offers' => [
                    '@type' => 'Offer',
                    'price' => '0',
                    'priceCurrency' => 'IDR',
                ],
                'publisher' => ['@id' => $orgId],
                'inLanguage' => 'id-ID',
            ],
            [
                '@type' => 'WebSite',
                '@id' => $websiteId,
                'name' => config('app.name'),
                'url' => $base,
                'publisher' => ['@id' => $orgId],
                'inLanguage' => 'id-ID',
            ],
        ],
    ];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
