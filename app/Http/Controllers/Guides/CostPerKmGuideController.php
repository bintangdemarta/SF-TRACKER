<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class CostPerKmGuideController extends Controller
{
    public function __invoke(): View
    {
        $canonical = route('guides.cost-per-km');

        return view('guides.cost-per-km', [
            'title' => 'Cara Hitung Cost per KM Motor Driver ShopeeFood',
            'description' => 'Hitung biaya riil per kilometer motor matic driver ShopeeFood: BBM, servis, dan ban — bukan cuma tebak-tebakan dari sisa saldo yang keliatan di aplikasi.',
            'canonical' => $canonical,
            'h1' => 'Cara Menghitung Biaya per Kilometer (Cost/KM) Motor Driver ShopeeFood',
            'datePublished' => Carbon::parse('2026-08-10T00:00:00+07:00')->toAtomString(),
            'dateModified' => Carbon::createFromTimestamp(
                filemtime(resource_path('views/guides/cost-per-km.blade.php'))
            )->toAtomString(),
            'breadcrumbs' => [
                ['name' => 'Beranda', 'url' => route('landing')],
                ['name' => 'Panduan Driver ShopeeFood', 'url' => route('guides.pillar')],
                ['name' => 'Cost per KM Motor', 'url' => $canonical],
            ],
            'relatedLinks' => [
                ['title' => 'Cara Hitung Net Profit', 'url' => route('guides.net-profit')],
                ['title' => 'Sistem Poin & Insentif', 'url' => route('guides.poin-insentif')],
            ],
            'howTo' => [
                'name' => 'Cara Menghitung Cost per KM Motor Matic untuk Driver ShopeeFood',
                'steps' => [
                    ['name' => 'Hitung biaya BBM per KM', 'text' => 'Bagi harga per liter Pertalite dengan konsumsi motor matic (40-45 km/liter) untuk dapat biaya BBM per kilometer.'],
                    ['name' => 'Hitung alokasi servis per KM', 'text' => 'Sisihkan Rp35.000-Rp40.000 untuk setiap 2.000 km sebagai dana ganti oli mesin dan oli gardan, lalu bagi rata per kilometer.'],
                    ['name' => 'Hitung biaya parkir harian', 'text' => 'Kalikan rata-rata 5-8 resto per shift dengan tarif parkir Rp1.000-Rp2.000, hasilnya sekitar Rp10.000-Rp15.000 per hari.'],
                    ['name' => 'Jumlahkan semua komponen', 'text' => 'Total biaya BBM + alokasi servis + parkir per hari, lalu bagi dengan total KM tempuh shift untuk dapat cost/KM riil.'],
                ],
            ],
        ]);
    }
}
