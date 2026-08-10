<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

    <x-seo-meta :title="$title" :description="$description" :canonical="$canonical" />
    <x-structured-data :description="$description" />

    @vite(['resources/css/app.css'])
</head>
<body class="font-sans antialiased bg-white text-slate-900">

    <header class="border-b border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-4 flex items-center justify-between">
            <span class="text-lg font-bold text-slate-900">{{ config('app.name') }}</span>
            <a href="{{ route('login') }}"
                class="min-h-[44px] inline-flex items-center text-sm font-semibold text-slate-700 hover:text-slate-900 px-3">
                Masuk
            </a>
        </div>
    </header>

    <main>
        {{-- HERO --}}
        <section class="bg-slate-900 text-white">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 py-16 sm:py-24 text-center">
                <h1 class="text-3xl sm:text-5xl font-extrabold leading-tight tracking-tight">
                    Hitung Bersih, Jangan Tertipu Saldo
                    <span class="block text-2xl sm:text-3xl font-semibold text-slate-300 mt-2">Aplikasi Keuangan Driver ShopeeFood</span>
                </h1>
                <p class="mt-5 text-lg sm:text-xl text-slate-300 max-w-2xl mx-auto">
                    Saldo muter tinggi bukan berarti untung. {{ config('app.name') }} pisahin kas tunai & saldo digital
                    secara otomatis, dan kasih tau profit bersih riil kamu — bukan cuma angka yang keliatan ramai.
                </p>
                <p class="mt-3 text-sm sm:text-base text-slate-400 max-w-2xl mx-auto">
                    Poin/Berlian insentif, dana talangan resto, sampai rekonsiliasi tunai vs saldo ShopeePay —
                    semua kepisah rapi, gak numpuk jadi satu angka yang menyesatkan.
                </p>
                <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                    <a href="{{ route('register') }}"
                        class="min-h-[48px] inline-flex items-center justify-center rounded-lg bg-emerald-500 hover:bg-emerald-400 active:scale-95 transition px-8 text-base font-bold text-slate-950 shadow-lg">
                        🚀 Mulai Catat Gratis
                    </a>
                    <a href="{{ route('login') }}"
                        class="min-h-[48px] inline-flex items-center justify-center rounded-lg border-2 border-slate-600 hover:border-slate-400 active:scale-95 transition px-8 text-base font-semibold text-white">
                        Sudah punya akun? Masuk
                    </a>
                </div>
            </div>
        </section>

        {{-- FEATURES --}}
        <section class="bg-white">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 py-16 sm:py-20">
                <h2 class="text-2xl sm:text-3xl font-bold text-center text-slate-900">
                    Bukan Cuma Pencatat — Ini Kalkulator Profit ShopeeFood
                </h2>
                <div class="mt-12 grid gap-8 sm:grid-cols-3">
                    <div class="rounded-xl border border-slate-200 p-6">
                        <div class="h-12 w-12 rounded-lg bg-emerald-50 border border-emerald-300 flex items-center justify-center text-2xl" aria-hidden="true">💰</div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Dual-Wallet Ledger</h3>
                        <p class="mt-2 text-sm text-slate-700 leading-relaxed">
                            Kas tunai fisik dan saldo digital dipisah otomatis tiap kali kamu catat trip atau
                            pengeluaran — gak akan ketuker lagi.
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-6">
                        <div class="h-12 w-12 rounded-lg bg-amber-50 border border-amber-300 flex items-center justify-center text-2xl" aria-hidden="true">⛽</div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Cost/KM &amp; Efisiensi BBM</h3>
                        <p class="mt-2 text-sm text-slate-700 leading-relaxed">
                            Tau persis biaya riil per kilometer (BBM + servis + parkir) dan efisiensi bensin motor
                            kamu dalam KM/Liter — bukan tebak-tebakan.
                        </p>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-6">
                        <div class="h-12 w-12 rounded-lg bg-indigo-50 border border-indigo-300 flex items-center justify-center text-2xl" aria-hidden="true">📊</div>
                        <h3 class="mt-4 text-lg font-bold text-slate-900">Real-Time Net Profit</h3>
                        <p class="mt-2 text-sm text-slate-700 leading-relaxed">
                            Net profit keliatan langsung tiap kali shift jalan — sudah dipotong semua biaya
                            operasional, bukan cuma saldo yang muter.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        {{-- SECONDARY CTA --}}
        <section class="bg-slate-50 border-t border-slate-200">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 py-14 text-center">
                <h2 class="text-2xl font-bold text-slate-900">Gratis, Langsung Pakai</h2>
                <p class="mt-3 text-slate-700">Gak ada biaya langganan. Daftar, mulai shift, catat trip pertama kamu.</p>
                <a href="{{ route('register') }}"
                    class="mt-6 min-h-[48px] inline-flex items-center justify-center rounded-lg bg-emerald-700 hover:bg-emerald-800 active:scale-95 transition px-8 text-base font-bold text-white shadow">
                    Mulai Catat Gratis
                </a>
            </div>
        </section>
    </main>

    <footer class="border-t border-slate-200">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 py-8 text-center text-sm text-slate-500">
            &copy; {{ now()->year }} {{ config('app.name') }}. Dibuat buat mitra pengemudi ShopeeFood.
            <br>
            <a href="{{ route('guides.pillar') }}" class="text-emerald-700 hover:underline font-medium">
                Panduan Lengkap Driver ShopeeFood
            </a>
        </div>
    </footer>

</body>
</html>
