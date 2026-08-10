<?php

namespace App\Http\Controllers\Guides;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;

class DualWalletGuideController extends Controller
{
    public function __invoke(): View
    {
        $canonical = route('guides.dual-wallet');

        return view('guides.dual-wallet', [
            'title' => 'Cara Kelola Uang Tunai vs Saldo ShopeePay Driver',
            'description' => 'Kenapa kas tunai customer dan saldo digital ShopeePay wajib dipisah: dana talangan resto, rekonsiliasi harian, dan cara driver ShopeeFood menghindari uang bercampur.',
            'canonical' => $canonical,
            'h1' => 'Manajemen Kas: Mengapa Dompet Tunai dan Saldo ShopeePay Harus Dipisah?',
            'datePublished' => Carbon::parse('2026-08-10T00:00:00+07:00')->toAtomString(),
            'dateModified' => Carbon::createFromTimestamp(
                filemtime(resource_path('views/guides/dual-wallet.blade.php'))
            )->toAtomString(),
            'breadcrumbs' => [
                ['name' => 'Beranda', 'url' => route('landing')],
                ['name' => 'Panduan Driver ShopeeFood', 'url' => route('guides.pillar')],
                ['name' => 'Kelola Uang Tunai vs Saldo', 'url' => $canonical],
            ],
            'relatedLinks' => [
                ['title' => 'Sistem Poin & Insentif', 'url' => route('guides.poin-insentif')],
                ['title' => 'Target Harian vs Realita', 'url' => route('guides.target-harian')],
            ],
            'faq' => [
                [
                    'q' => 'Kenapa uang talangan resto tidak boleh digabung dengan saldo pribadi?',
                    'a' => 'Uang tunai dari customer untuk order cash-on-delivery sebagian adalah modal yang harus dikembalikan ke sistem lewat setoran atau potongan saldo — kalau tercampur dengan uang pribadi, driver bisa merasa "kaya" di tengah shift padahal itu bukan uang bersihnya.',
                ],
                [
                    'q' => 'Kapan waktu terbaik rekonsiliasi tunai vs saldo?',
                    'a' => 'Di akhir tiap shift, sebelum uang tunai fisik tercampur dengan pengeluaran pribadi lain — supaya selisih (kalau ada) langsung ketahuan sementara ingatan soal transaksi shift itu masih segar.',
                ],
            ],
        ]);
    }
}
