<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): Response
    {
        $urls = [
            [
                'loc' => route('landing'),
                'lastmod' => now()->toAtomString(),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
        ];

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml');
    }
}
