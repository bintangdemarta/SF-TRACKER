<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class PillarGuideController extends Controller
{
    public function __invoke(): View
    {
        $canonical = route('guides.pillar');

        return view('guides.pillar', [
            'title' => 'Panduan Lengkap Driver ShopeeFood: Hitung Profit Bersih',
            'description' => 'Panduan komprehensif mengelola keuangan mitra driver ShopeeFood: cara hitung laba bersih, efisiensi bensin, sistem poin berlian, dan manajemen dompet tunai.',
            'canonical' => $canonical,
            'h1' => 'Panduan Lengkap Driver ShopeeFood: Penghasilan, Biaya Operasional, dan Cara Hitung Profit Bersih',
            'datePublished' => Carbon::parse('2026-08-10T00:00:00+07:00')->toAtomString(),
            'dateModified' => Carbon::createFromTimestamp(
                filemtime(resource_path('views/guides/pillar.blade.php'))
            )->toAtomString(),
            'breadcrumbs' => [
                ['name' => 'Beranda', 'url' => route('landing')],
                ['name' => 'Panduan Driver ShopeeFood', 'url' => $canonical],
            ],
            'relatedLinks' => [
                ['title' => 'Cara Hitung Net Profit', 'url' => route('guides.net-profit')],
                ['title' => 'Cost per KM Motor', 'url' => route('guides.cost-per-km')],
                ['title' => 'Sistem Poin & Insentif', 'url' => route('guides.poin-insentif')],
                ['title' => 'Kelola Uang Tunai vs Saldo', 'url' => route('guides.dual-wallet')],
                ['title' => 'Target Harian vs Realita', 'url' => route('guides.target-harian')],
            ],
            'faq' => [
                [
                    'q' => 'Kenapa saldo di aplikasi ShopeeFood beda sama uang bersih yang saya bawa pulang?',
                    'a' => 'Saldo aplikasi adalah akumulasi argo, poin, dan tips sebelum dipotong biaya operasional (BBM, servis, parkir) dan sebelum dipisahkan dari dana talangan resto yang harus dikembalikan. Uang bersih baru ketahuan setelah semua komponen itu dikurangi.',
                ],
                [
                    'q' => 'Berapa persen idealnya biaya operasional dari total omzet harian?',
                    'a' => 'Untuk motor matic 110-125cc dengan pola kerja normal, biaya operasional (BBM + servis + parkir) biasanya berada di kisaran 20-30% dari omzet kotor harian.',
                ],
            ],
        ]);
    }
}
