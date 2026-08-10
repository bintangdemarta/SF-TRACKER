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
    <h2>Dua Jenis Uang yang Sering Tercampur</h2>
    <p>
        Sepanjang shift, driver ShopeeFood memegang dua jenis uang yang sifatnya beda: saldo digital di
        ShopeePay (dari argo, poin, dan pembayaran non-tunai) dan uang tunai fisik dari customer yang bayar
        cash — termasuk uang belanja yang dititipkan buat order COD di resto. Kalau digabung dalam satu
        "kantong mental", driver gampang salah kira sisa uang tunai di dompet sebagai keuntungan pribadi.
    </p>

    <h2>Apa Itu Dana Talangan Resto dan Kenapa Bukan Uang Pribadi</h2>
    <p>
        Untuk order dengan metode pembayaran tunai, driver kadang perlu membayar dulu ke resto dari uang
        sendiri sebelum digantikan customer, atau sebaliknya — menerima uang tunai dari customer yang sebagian
        harus disetorkan balik ke sistem. Uang yang lewat tangan ini adalah dana talangan, bukan pendapatan.
        Kalau tercampur dengan saldo pribadi, seolah-olah ada uang "lebih" padahal itu titipan yang harus balik
        ke alurnya.
    </p>

    <h2>Cara Rekonsiliasi Tunai vs Saldo ShopeePay Setiap Shift</h2>
    <p>
        Di akhir tiap shift, sebelum uang tunai fisik tercampur dengan pengeluaran pribadi lain, lakukan tiga
        langkah: (1) jumlahkan semua uang tunai fisik yang ada di tangan, (2) kurangi dengan total dana talangan
        yang harus disetorkan atau sudah otomatis dipotong sistem, (3) sisanya baru bisa dianggap bagian dari
        pendapatan tunai yang sah. Lakukan ini tiap shift, bukan ditumpuk mingguan — supaya selisih (kalau ada)
        langsung ketahuan selagi ingatan soal transaksi masih segar.
    </p>

    <h2>Kenapa Dompet Terpisah Bikin Net Profit Lebih Akurat</h2>
    <p>
        Setelah tunai dan saldo digital dipisah dan direkonsiliasi, angka yang masuk ke rumus net profit jadi
        angka yang benar-benar milik driver — bukan campuran dengan dana titipan yang sebenarnya bukan hak
        driver. Ini juga yang bikin studi kasus perhitungan di
        <a href="{{ route('guides.net-profit') }}">panduan Cara Hitung Net Profit</a> valid dipakai harian.
    </p>

    <h2>Pertanyaan Umum</h2>
    @foreach ($faq as $item)
        <h3>{{ $item['q'] }}</h3>
        <p>{{ $item['a'] }}</p>
    @endforeach
</x-layouts.guide>
