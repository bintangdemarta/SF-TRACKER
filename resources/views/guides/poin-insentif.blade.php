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
    <h2>Apa Itu Poin/Berlian dan Kenapa Bisa Menipu Hitungan Profit</h2>
    <p>
        Poin atau berlian adalah insentif tambahan di luar argo dasar, biasanya cair setelah driver mencapai
        jumlah trip atau target tertentu dalam periode waktu. Masalahnya, poin ini sering muncul di layar
        sebagai "estimasi" jauh sebelum benar-benar cair jadi saldo — kalau dicatat sebagai pendapatan sejak
        awal, hitungan profit hari itu bisa lebih besar dari kenyataan.
    </p>

    {{-- Tier insentif dan nilai konversi poin berbeda per kota dan berubah sewaktu-waktu
         mengikuti kebijakan ShopeeFood setempat — sengaja tidak dicantumkan angka pasti
         di sini karena bisa berubah tanpa pemberitahuan dan berbeda antar wilayah. --}}
    <h2>Tier Insentif Berbeda per Kota — Jangan Patok Angka Kota Lain</h2>
    <p>
        Karena kebijakan tier dan nilai poin diatur per wilayah dan bisa berubah kapan saja, jangan jadikan
        angka yang beredar di grup driver kota lain sebagai patokan. Cara paling aman: catat nilai poin/berlian
        yang benar-benar cair ke akunmu sendiri, per hari, dan bandingkan trennya dari waktu ke waktu.
    </p>

    <h2>Kapan Poin/Berlian Boleh Dihitung Sebagai Pendapatan?</h2>
    <p>
        Hitung poin/berlian sebagai pendapatan hanya setelah statusnya cair jadi saldo yang bisa ditarik —
        bukan saat progress bar-nya masih berjalan. Ini mencegah kamu merasa "sudah untung" padahal target
        belum tercapai dan insentifnya belum tentu cair hari itu.
    </p>

    <h2>Cara Mencatat Poin Terpisah dari Argo</h2>
    <p>
        Pisahkan argo dan poin/berlian jadi dua baris catatan yang berbeda, bukan satu angka gabungan. Dengan
        begitu kamu bisa lihat: kalau suatu hari poin/berlian nol tapi argo tetap stabil, apakah hari itu masih
        tetap untung dari pekerjaan inti (mengantar order), atau selama ini profitmu terlalu bergantung pada
        insentif yang sifatnya gak pasti.
    </p>

    <h2>Pertanyaan Umum</h2>
    @foreach ($faq as $item)
        <h3>{{ $item['q'] }}</h3>
        <p>{{ $item['a'] }}</p>
    @endforeach
</x-layouts.guide>
