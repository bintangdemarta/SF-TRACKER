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
    <h2>Jebakan "Tutup Poin": Mengapa Saldo Aplikasi Bukan Uang Bersih?</h2>
    <p>
        Banyak driver ngejar "tutup poin" — target tertentu buat cairin insentif — tanpa sadar biaya operasional
        buat ngejar target itu kadang lebih besar dari insentif yang didapat. Saldo yang muter tinggi di aplikasi
        cuma nunjukin omzet kotor, bukan berapa yang beneran nyisa di kantong setelah BBM, servis, dan parkir
        dipotong.
    </p>

    <h2>Rumus Baku Net Take-Home Pay untuk Mitra Driver</h2>
    <p>Net profit = Pendapatan Kotor − Beban Langsung − Beban Tersembunyi.</p>

    <h3>Pendapatan Kotor (Total Argo Bersih + Poin Berlian + Tips Customer)</h3>
    <p>
        Jumlahkan semua argo dari trip yang selesai, poin/berlian yang sudah cair jadi saldo (bukan yang masih
        progress), dan tips langsung dari customer. Ini angka kotor sebelum dikurangi apapun.
    </p>

    <h3>Beban Langsung (BBM Harian + Parkir + Konsumsi Saat Shift)</h3>
    <p>
        BBM dihitung dari konsumsi motor matic (40-45 km/liter Pertalite) dikali total KM tempuh shift. Parkir
        rata-rata 5-8 resto per shift x Rp1.000-Rp2.000, sekitar Rp10.000-Rp15.000 per hari. Konsumsi pribadi
        selama shift (makan, minum) juga masuk sini kalau mau hitungan yang jujur.
    </p>

    <h3>Beban Tersembunyi (Amortisasi Ganti Oli, Ban, dan Pajak Motor)</h3>
    <p>
        Ini biaya yang gak keluar tiap hari tapi tetap harus dialokasikan harian: ganti oli mesin dan gardan
        (~Rp60.000-Rp75.000 per 2.000 km, setara ~Rp35.000-Rp40.000/km kalau dirata-rata), ban, dan pajak
        tahunan motor. Kalau gak disisihkan tiap hari, biaya ini nagih sekaligus dalam jumlah besar pas motor
        rewel.
    </p>

    <h2>Studi Kasus: Simulasi Shift 10 Jam di Lapangan (Hitungan Nyata)</h2>
    <p>
        Contoh pola umum: shift 10 jam menempuh sekitar 80-100 km. Biaya BBM dan alokasi servis untuk jarak
        segitu ada di kisaran 20-30% dari omzet kotor hari itu — sisanya, setelah dikurangi parkir dan konsumsi,
        adalah net profit riil. Angka pastinya beda-beda tergantung rute, kepadatan order, dan kondisi motor
        masing-masing, jadi bagian terpenting bukan hafal satu angka, tapi konsisten mencatat tiap shift.
    </p>

    <h2>Cara Memisahkan Uang Modal Belanja Resto dari Uang Pribadi</h2>
    <p>
        Uang tunai dari customer untuk order COD sering kepakai dulu buat belanjain pesanan di resto — ini
        talangan, bukan pendapatan. Sisihkan segera setelah trip selesai, jangan tunggu akhir shift, supaya gak
        ketuker sama uang pribadi yang udah campur di dompet fisik. Detail alurnya ada di
        <a href="{{ route('guides.dual-wallet') }}">panduan Kelola Uang Tunai vs Saldo</a>.
    </p>

    <h2>Pertanyaan Umum</h2>
    @foreach ($faq as $item)
        <h3>{{ $item['q'] }}</h3>
        <p>{{ $item['a'] }}</p>
    @endforeach
</x-layouts.guide>
