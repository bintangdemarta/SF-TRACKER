<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class NetProfitGuideController extends Controller
{
    public function __invoke(): View
    {
        $canonical = route('guides.net-profit');

        return view('guides.net-profit', [
            'title' => 'Cara Hitung Net Profit Driver ShopeeFood Secara Akurat',
            'description' => 'Rumus baku hitung penghasilan bersih driver ShopeeFood: pendapatan kotor dikurangi beban langsung dan beban tersembunyi, lengkap studi kasus shift 10 jam.',
            'canonical' => $canonical,
            'h1' => 'Cara Menghitung Penghasilan Bersih (Net Profit) Driver ShopeeFood Secara Akurat',
            'datePublished' => Carbon::parse('2026-08-10T00:00:00+07:00')->toAtomString(),
            'dateModified' => Carbon::createFromTimestamp(
                filemtime(resource_path('views/guides/net-profit.blade.php'))
            )->toAtomString(),
            'breadcrumbs' => [
                ['name' => 'Beranda', 'url' => route('landing')],
                ['name' => 'Panduan Driver ShopeeFood', 'url' => route('guides.pillar')],
                ['name' => 'Cara Hitung Net Profit', 'url' => $canonical],
            ],
            'relatedLinks' => [
                ['title' => 'Target Harian vs Realita', 'url' => route('guides.target-harian')],
                ['title' => 'Cost per KM Motor', 'url' => route('guides.cost-per-km')],
            ],
            'faq' => [
                [
                    'q' => 'Berapa persen idealnya biaya operasional dari total omzet harian?',
                    'a' => 'Idealnya 20-30% dari omzet kotor harian untuk motor matic 110-125cc dengan konsumsi BBM normal. Kalau sudah tembus di atas 30%, biasanya ada kebocoran: rute boros, servis telat, atau ban gundul yang bikin konsumsi bensin naik.',
                ],
                [
                    'q' => 'Apakah uang tip dari customer masuk ke hitungan profit bersih?',
                    'a' => 'Ya. Tip adalah pendapatan kotor murni tanpa potongan platform, jadi masuk penuh ke sisi pendapatan sebelum dikurangi beban operasional — sama seperti argo dan poin berlian.',
                ],
            ],
        ]);
    }
}
