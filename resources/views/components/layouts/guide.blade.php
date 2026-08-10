@props([
    'title',
    'description',
    'canonical',
    'h1',
    'datePublished',
    'dateModified',
    'breadcrumbs' => [],
    'relatedLinks' => [],
    'faq' => null,
    'howTo' => null,
])
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <x-seo-meta :title="$title" :description="$description" :canonical="$canonical" />
    <x-guide-structured-data
        :title="$title"
        :description="$description"
        :canonical="$canonical"
        :date-published="$datePublished"
        :date-modified="$dateModified"
        :breadcrumbs="$breadcrumbs"
        :faq="$faq"
        :how-to="$howTo"
    />

    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-white text-slate-900">

    <header class="border-b border-slate-200">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <a href="{{ route('landing') }}" class="text-lg font-bold text-slate-900">{{ config('app.name') }}</a>
            <a href="{{ route('login') }}"
                class="min-h-[44px] inline-flex items-center text-sm font-semibold text-slate-700 hover:text-slate-900 px-3">
                Masuk
            </a>
        </div>
    </header>

    @if (! empty($breadcrumbs))
        <nav aria-label="Breadcrumb" class="max-w-3xl mx-auto px-4 sm:px-6 pt-6">
            <ol class="flex flex-wrap items-center gap-1 text-sm text-slate-500">
                @foreach ($breadcrumbs as $crumb)
                    @if (! $loop->first)
                        <li aria-hidden="true" class="text-slate-300">/</li>
                    @endif
                    <li>
                        @if ($loop->last)
                            <span class="text-slate-700 font-medium">{{ $crumb['name'] }}</span>
                        @else
                            <a href="{{ $crumb['url'] }}" class="hover:text-slate-900 hover:underline">{{ $crumb['name'] }}</a>
                        @endif
                    </li>
                @endforeach
            </ol>
        </nav>
    @endif

    <main class="max-w-3xl mx-auto px-4 sm:px-6 py-8">
        <h1 class="text-3xl sm:text-4xl font-extrabold text-slate-900 leading-tight">{{ $h1 }}</h1>

        <article class="prose prose-slate prose-headings:font-bold prose-h2:text-2xl prose-h2:mt-10 prose-h3:text-xl prose-a:text-emerald-700 max-w-none mt-8">
            {{ $slot }}
        </article>

        @if (! empty($relatedLinks))
            <aside class="mt-14 pt-8 border-t border-slate-200">
                <h2 class="text-lg font-bold text-slate-900">Baca Juga</h2>
                <ul class="mt-4 grid gap-3 sm:grid-cols-2">
                    @foreach ($relatedLinks as $link)
                        <li>
                            <a href="{{ $link['url'] }}"
                                class="block min-h-[48px] rounded-lg border border-slate-200 p-4 hover:border-emerald-400 hover:bg-emerald-50 transition">
                                <span class="font-semibold text-slate-900 text-sm">{{ $link['title'] }}</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            </aside>
        @endif

        <div class="mt-10 rounded-xl bg-slate-900 text-white p-6 text-center">
            <p class="font-bold text-lg">Mulai catat keuangan shift kamu sekarang</p>
            <a href="{{ route('register') }}"
                class="mt-4 min-h-[48px] inline-flex items-center justify-center rounded-lg bg-emerald-500 hover:bg-emerald-400 active:scale-95 transition px-8 text-base font-bold text-slate-950">
                🚀 Mulai Catat Gratis
            </a>
        </div>
    </main>

    <footer class="border-t border-slate-200 mt-12">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 py-8 text-center text-sm text-slate-500">
            &copy; {{ now()->year }} {{ config('app.name') }}. Dibuat buat mitra pengemudi ShopeeFood.
            <br>
            <a href="{{ route('guides.pillar') }}" class="text-emerald-700 hover:underline font-medium">
                Panduan Lengkap Driver ShopeeFood
            </a>
        </div>
    </footer>

</body>
</html>
