<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class TargetHarianGuideController extends Controller
{
    public function __invoke(): View
    {
        $canonical = route('guides.target-harian');

        return view('guides.target-harian', [
            'title' => 'Target Harian vs Realita Penghasilan Driver ShopeeFood',
            'description' => 'Kenapa target harian yang dipajang aplikasi sering meleset dari uang bersih yang benar-benar dibawa pulang driver ShopeeFood, dan cara menyusun target yang realistis.',
            'canonical' => $canonical,
            'h1' => 'Target Harian vs Realita: Kenapa Angka di Aplikasi Sering Menipu Driver ShopeeFood',
            'datePublished' => Carbon::parse('2026-08-10T00:00:00+07:00')->toAtomString(),
            'dateModified' => Carbon::createFromTimestamp(
                filemtime(resource_path('views/guides/target-harian.blade.php'))
            )->toAtomString(),
            'breadcrumbs' => [
                ['name' => 'Beranda', 'url' => route('landing')],
                ['name' => 'Panduan Driver ShopeeFood', 'url' => route('guides.pillar')],
                ['name' => 'Target Harian vs Realita', 'url' => $canonical],
            ],
            'relatedLinks' => [
                ['title' => 'Kelola Uang Tunai vs Saldo', 'url' => route('guides.dual-wallet')],
                ['title' => 'Cara Hitung Net Profit', 'url' => route('guides.net-profit')],
            ],
            'faq' => [
                [
                    'q' => 'Kenapa target harian di aplikasi terasa tidak realistis?',
                    'a' => 'Target harian biasanya dipatok dari omzet kotor (argo + poin), bukan dari uang bersih setelah dipotong BBM, servis, dan parkir — jadi angka yang dikejar dan angka yang benar-benar dibawa pulang memang dua hal yang berbeda.',
                ],
                [
                    'q' => 'Bagaimana cara menyusun target harian yang lebih realistis?',
                    'a' => 'Tentukan target dari uang bersih yang diinginkan, lalu tambahkan kembali estimasi biaya operasional (sekitar 20-30% dari omzet) untuk mendapatkan angka omzet kotor yang perlu dikejar di aplikasi.',
                ],
            ],
        ]);
    }
}
