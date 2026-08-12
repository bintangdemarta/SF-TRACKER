<?php

use App\Http\Controllers\Guides\CostPerKmGuideController;
use App\Http\Controllers\Guides\DualWalletGuideController;
use App\Http\Controllers\Guides\NetProfitGuideController;
use App\Http\Controllers\Guides\PillarGuideController;
use App\Http\Controllers\Guides\PoinInsentifGuideController;
use App\Http\Controllers\Guides\TargetHarianGuideController;
use App\Http\Controllers\HistoricalReportExportController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\RobotsController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');
Route::get('/robots.txt', RobotsController::class)->name('robots');

Route::prefix('panduan-driver-shopeefood')->name('guides.')->group(function () {
    Route::get('/', PillarGuideController::class)->name('pillar');
    Route::get('/cara-hitung-net-profit', NetProfitGuideController::class)->name('net-profit');
    Route::get('/cost-per-km-motor', CostPerKmGuideController::class)->name('cost-per-km');
    Route::get('/sistem-poin-dan-insentif', PoinInsentifGuideController::class)->name('poin-insentif');
    Route::get('/kelola-uang-tunai-vs-saldo', DualWalletGuideController::class)->name('dual-wallet');
    Route::get('/target-harian-vs-realita', TargetHarianGuideController::class)->name('target-harian');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('reports', 'reports')
    ->middleware(['auth', 'verified'])
    ->name('reports');

Route::get('reports/export', HistoricalReportExportController::class)
    ->middleware(['auth', 'verified'])
    ->name('reports.export');

require __DIR__.'/auth.php';
