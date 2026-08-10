<x-layouts.guide
    :title="$title"
    :description="$description"
    :canonical="$canonical"
    :h1="$h1"
    :date-published="$datePublished"
    :date-modified="$dateModified"
    :breadcrumbs="$breadcrumbs"
    :related-links="$relatedLinks"
    :faq="$faq"
>
    <h2>Target Harian di Aplikasi Dihitung dari Omzet Kotor, Bukan Uang Bersih</h2>
    <p>
        Target harian yang dipajang di aplikasi — misalnya buat kejar bonus tertentu — biasanya dipatok dari
        total argo dan poin, alias omzet kotor. Padahal uang yang benar-benar dibawa pulang driver adalah omzet
        kotor itu dikurangi BBM, servis, dan parkir. Dua angka ini bisa beda cukup jauh, dan kalau target
        disusun tanpa memperhitungkan selisihnya, driver bisa merasa "kurang" padahal sebenarnya sudah sesuai
        rencana.
    </p>

    <h2>Kenapa Realita di Lapangan Sering Meleset dari Target</h2>
    <p>
        Faktor yang gak muncul di angka target: kepadatan order yang naik-turun sepanjang hari, jarak antar
        titik jemput yang menambah cost/KM tanpa menambah argo sebanding, dan waktu tunggu di resto yang makan
        jam kerja tanpa menghasilkan trip baru. Semua ini bikin realita omzet kotor per jam jarang konsisten
        dengan proyeksi target harian.
    </p>

    <h2>Cara Menyusun Target Harian yang Realistis</h2>
    <p>
        Balik logikanya: mulai dari berapa uang bersih yang ingin dibawa pulang, lalu tambahkan kembali estimasi
        biaya operasional (sekitar 20-30% dari omzet kotor untuk motor matic 110-125cc dengan pola kerja normal)
        untuk mendapatkan angka omzet kotor yang perlu dikejar di aplikasi. Target yang disusun dari uang bersih
        lebih jujur dibanding target yang cuma menyalin angka bonus dari aplikasi.
    </p>

    <h2>Pantau Selisih Target vs Realita per Shift</h2>
    <p>
        Catat dua angka tiap shift: omzet kotor yang tercapai dan net profit riil setelah dipotong biaya
        operasional (lihat rumus lengkapnya di <a href="{{ route('guides.net-profit') }}">panduan Cara Hitung
        Net Profit</a>). Kalau selisih keduanya makin melebar dari waktu ke waktu, itu tanda biaya operasional
        naik — motor perlu servis, atau rute yang diambil makin boros.
    </p>

    <h2>Pertanyaan Umum</h2>
    @foreach ($faq as $item)
        <h3>{{ $item['q'] }}</h3>
        <p>{{ $item['a'] }}</p>
    @endforeach
</x-layouts.guide>
