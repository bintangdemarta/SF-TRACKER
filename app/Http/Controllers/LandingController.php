<?php

namespace App\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class LandingController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        $title = config('app.name').' - Catat Keuangan & Performa Driver ShopeeFood';
        $description = 'Catatan keuangan driver ShopeeFood real-time: kalkulator profit ShopeeFood, cost per KM, dan efisiensi bensin motor. Gratis, langsung pakai.';

        return view('landing', [
            'title' => $title,
            'description' => $description,
            'canonical' => route('landing'),
        ]);
    }
}
