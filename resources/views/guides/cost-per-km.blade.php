<x-layouts.guide
    :title="$title"
    :description="$description"
    :canonical="$canonical"
    :h1="$h1"
    :date-published="$datePublished"
    :date-modified="$dateModified"
    :breadcrumbs="$breadcrumbs"
    :related-links="$relatedLinks"
    :how-to="$howTo"
>
    <p>
        Cost per KM adalah angka yang paling sering dilewatkan driver, padahal ini komponen terbesar kedua
        setelah waktu kerja itu sendiri. Tanpa angka ini, kamu gak akan pernah tau apakah rute yang jauh tapi
        argonya gede itu beneran menguntungkan, atau malah rugi diam-diam.
    </p>

    <h2>Langkah 1: Hitung Biaya BBM per KM</h2>
    <p>
        Motor matic 110-125cc rata-rata menempuh 40-45 km per liter Pertalite. Bagi harga per liter Pertalite
        terkini dengan angka ini untuk dapat biaya BBM murni per kilometer.
    </p>

    <h2>Langkah 2: Hitung Alokasi Servis per KM</h2>
    <p>
        Siklus ganti oli mesin dan oli gardan setiap 2.000 km menghabiskan sekitar Rp60.000-Rp75.000, atau
        setara Rp35.000-Rp40.000 per kilometer kalau dirata-rata. Ini biaya yang gak terasa harian tapi pasti
        nagih — masukkan ke hitungan cost/KM sejak awal, bukan pas motor udah mulai rewel.
    </p>
    {{-- Biaya ban dan pajak tahunan bervariasi tergantung merek motor dan wilayah;
         sengaja tidak dipatok angka pasti di sini supaya driver mengisi dari data servis motornya sendiri. --}}

    <h2>Langkah 3: Hitung Biaya Parkir Harian</h2>
    <p>
        Rata-rata 5-8 resto disinggahi per shift, dengan tarif parkir Rp1.000-Rp2.000 per titik — totalnya
        sekitar Rp10.000-Rp15.000 per hari. Kecil per transaksi, tapi konsisten tiap hari kerja.
    </p>

    <h2>Langkah 4: Jumlahkan Semua Komponen</h2>
    <p>
        Total biaya BBM + alokasi servis + parkir dalam satu hari, dibagi dengan total kilometer yang ditempuh
        di shift itu. Hasilnya adalah cost/KM riil — angka yang seharusnya kamu bandingkan dengan argo per
        kilometer sebelum memutuskan sebuah order jauh itu worth it atau tidak.
    </p>
    <p>
        Setelah cost/KM ketemu, langkah berikutnya adalah masukkan ke rumus net profit lengkap di
        <a href="{{ route('guides.net-profit') }}">panduan Cara Hitung Net Profit</a>.
    </p>
</x-layouts.guide>
