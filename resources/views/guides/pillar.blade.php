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
    <p>
        Kalau kamu ngerasa udah kerja keras seharian tapi pas ditotal uangnya "gak kerasa", kamu gak sendirian.
        Masalahnya bukan di jumlah trip — masalahnya di cara ngitung. Panduan ini bongkar semua komponen keuangan
        driver ShopeeFood satu per satu, dari argo sampai dana talangan resto, biar kamu tau persis ke mana
        uangmu pergi dan berapa yang benar-benar jadi milikmu.
    </p>

    <h2>Realita Finansial Driver ShopeeFood: Antara Omzet dan Uang Bersih</h2>
    <p>
        Angka yang muncul di layar aplikasi — total pendapatan hari ini — itu omzet kotor, bukan uang bersih.
        Di dalamnya masih campur: argo, poin berlian yang belum tentu cair hari itu juga, tips customer, dan yang
        paling sering kelupaan — uang tunai customer buat order COD yang sebagian harus balik lagi ke sistem
        sebagai dana talangan resto. Uang bersih baru ketahuan setelah semua komponen itu dipisah dan biaya
        operasional (BBM, servis, parkir) dikurangi.
    </p>

    <h2>4 Komponen Utama Keuangan Mitra Driver</h2>
    <p>Empat hal ini yang wajib dipisah, bukan digabung jadi satu angka besar:</p>
    <ul>
        <li><strong>Argo</strong> — tarif dasar per order, ini pendapatan inti yang paling stabil dan bisa diprediksi.</li>
        <li><strong>Poin/Berlian</strong> — insentif tambahan dari pencapaian tertentu, nilainya fluktuatif per kota dan periode.</li>
        <li><strong>Beban Operasional</strong> — BBM, parkir, dan konsumsi selama shift, ini yang paling sering gak dicatat padahal rutin keluar tiap hari.</li>
        <li><strong>Dana Servis</strong> — alokasi rutin buat ganti oli, ban, dan perawatan motor, sifatnya "utang ke motor sendiri" yang kalau gak disisihkan bakal nagih sekaligus dalam jumlah besar.</li>
    </ul>

    <h2>Memahami Biaya per Kilometer (Cost/KM) Sepeda Motor</h2>
    <p>
        Motor matic 110-125cc dengan konsumsi rata-rata 40-45 km/liter Pertalite, ditambah alokasi servis
        Rp35.000-Rp40.000 per km dari siklus ganti oli mesin dan gardan tiap 2.000 km, adalah dua komponen
        terbesar biaya operasional harian. Rincian lengkap dan cara hitungnya ada di
        <a href="{{ route('guides.cost-per-km') }}">panduan Cost per KM Motor</a>.
    </p>

    <h2>Manajemen Kas: Mengapa Dompet Tunai dan Saldo ShopeePay Harus Terpisah?</h2>
    <p>
        Transaksi tunai dari customer wajib disisihkan buat talangan resto dan operasional — jangan digabung
        dengan sisa saldo digital yang udah siap ditarik (withdraw). Kalau tercampur, kamu bisa merasa "kaya"
        di tengah shift padahal sebagian dari uang itu bukan milikmu. Detail lengkapnya ada di
        <a href="{{ route('guides.dual-wallet') }}">panduan Kelola Uang Tunai vs Saldo</a>.
    </p>

    <h2>Indeks Panduan Spesifik</h2>
    <ul>
        <li><a href="{{ route('guides.net-profit') }}">Cara Hitung Net Profit</a> — rumus baku penghasilan bersih plus studi kasus shift 10 jam.</li>
        <li><a href="{{ route('guides.cost-per-km') }}">Cost per KM Motor</a> — cara hitung biaya riil BBM, servis, dan parkir per kilometer.</li>
        <li><a href="{{ route('guides.poin-insentif') }}">Sistem Poin & Insentif</a> — cara kerja poin berlian dan dampaknya ke profit.</li>
        <li><a href="{{ route('guides.dual-wallet') }}">Kelola Uang Tunai vs Saldo</a> — kenapa kas tunai dan saldo ShopeePay harus dipisah.</li>
        <li><a href="{{ route('guides.target-harian') }}">Target Harian vs Realita</a> — cara menyusun target yang cocok sama uang bersih yang kamu mau.</li>
    </ul>

    <h2>FAQ Seputar Keuangan dan Pendapatan Driver ShopeeFood</h2>
    @foreach ($faq as $item)
        <h3>{{ $item['q'] }}</h3>
        <p>{{ $item['a'] }}</p>
    @endforeach
</x-layouts.guide>
