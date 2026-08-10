<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class PoinInsentifGuideController extends Controller
{
    public function __invoke(): View
    {
        $canonical = route('guides.poin-insentif');

        return view('guides.poin-insentif', [
            'title' => 'Sistem Poin & Insentif Driver ShopeeFood, Dampak ke Profit',
            'description' => 'Cara kerja poin berlian dan insentif driver ShopeeFood, serta cara mencatatnya supaya tidak tercampur dengan argo dan menyesatkan hitungan profit bersih harian.',
            'canonical' => $canonical,
            'h1' => 'Sistem Poin dan Insentif Driver ShopeeFood: Cara Kerja dan Dampaknya ke Profit',
            'datePublished' => Carbon::parse('2026-08-10T00:00:00+07:00')->toAtomString(),
            'dateModified' => Carbon::createFromTimestamp(
                filemtime(resource_path('views/guides/poin-insentif.blade.php'))
            )->toAtomString(),
            'breadcrumbs' => [
                ['name' => 'Beranda', 'url' => route('landing')],
                ['name' => 'Panduan Driver ShopeeFood', 'url' => route('guides.pillar')],
                ['name' => 'Sistem Poin & Insentif', 'url' => $canonical],
            ],
            'relatedLinks' => [
                ['title' => 'Cost per KM Motor', 'url' => route('guides.cost-per-km')],
                ['title' => 'Kelola Uang Tunai vs Saldo', 'url' => route('guides.dual-wallet')],
            ],
            'faq' => [
                [
                    'q' => 'Apakah nilai poin/berlian sama di setiap kota?',
                    'a' => 'Tidak. Tier insentif dan nilai konversi poin berbeda per kota dan berubah sewaktu-waktu mengikuti kebijakan ShopeeFood setempat — jangan jadikan angka dari kota lain sebagai patokan.',
                ],
                [
                    'q' => 'Poin/berlian dihitung sebagai pendapatan kapan?',
                    'a' => 'Saat poin sudah cair jadi saldo (bukan saat progress bar poin masih berjalan), supaya tidak menghitung uang yang belum benar-benar ada di tangan.',
                ],
            ],
        ]);
    }
}
