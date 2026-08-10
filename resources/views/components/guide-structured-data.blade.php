@props([
    'title',
    'description',
    'canonical',
    'datePublished',
    'dateModified',
    'breadcrumbs' => [],
    'faq' => null,
    'howTo' => null,
])
@php
    $landingUrl = route('landing');
    $orgId = $landingUrl.'#organization';

    $graph = [
        [
            '@type' => 'Organization',
            '@id' => $orgId,
            'name' => config('app.name'),
            'url' => $landingUrl,
            'logo' => [
                '@type' => 'ImageObject',
                '@id' => $landingUrl.'#logo',
                'url' => asset('images/og-cover.png'),
            ],
        ],
        [
            '@type' => 'Article',
            '@id' => $canonical.'#article',
            'headline' => $title,
            'description' => $description,
            'url' => $canonical,
            'inLanguage' => 'id-ID',
            'datePublished' => $datePublished,
            'dateModified' => $dateModified,
            'author' => ['@id' => $orgId],
            'publisher' => ['@id' => $orgId],
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $canonical],
        ],
    ];

    if (! empty($breadcrumbs)) {
        $graph[] = [
            '@type' => 'BreadcrumbList',
            '@id' => $canonical.'#breadcrumb',
            'itemListElement' => collect($breadcrumbs)->values()->map(fn ($crumb, $i) => [
                '@type' => 'ListItem',
                'position' => $i + 1,
                'name' => $crumb['name'],
                'item' => $crumb['url'],
            ])->all(),
        ];
    }

    if (! empty($faq)) {
        $graph[] = [
            '@type' => 'FAQPage',
            '@id' => $canonical.'#faq',
            'mainEntity' => collect($faq)->map(fn ($item) => [
                '@type' => 'Question',
                'name' => $item['q'],
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => $item['a'],
                ],
            ])->all(),
        ];
    }

    if (! empty($howTo)) {
        $graph[] = [
            '@type' => 'HowTo',
            '@id' => $canonical.'#howto',
            'name' => $howTo['name'],
            'step' => collect($howTo['steps'])->map(fn ($step, $i) => [
                '@type' => 'HowToStep',
                'position' => $i + 1,
                'name' => $step['name'],
                'text' => $step['text'],
            ])->all(),
        ];
    }

    $schema = ['@context' => 'https://schema.org', '@graph' => $graph];
@endphp
<script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
