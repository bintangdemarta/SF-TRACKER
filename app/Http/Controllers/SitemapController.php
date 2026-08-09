<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * lastmod is derived from the landing view's filesystem mtime, not
     * request time — a per-request now() would signal "this page changes
     * every second" to Googlebot, which is both false and erodes trust in
     * the lastmod signal for genuine change detection. Deriving it from
     * the source file also keeps the response byte-stable between
     * requests until the page is actually edited, which is what makes
     * the ETag below meaningful (a hash of ever-changing content can
     * never produce a cache hit).
     */
    public function __invoke(): Response
    {
        $landingLastmod = Carbon::createFromTimestamp(
            filemtime(resource_path('views/landing.blade.php'))
        )->toAtomString();

        $urls = [
            [
                'loc' => route('landing'),
                'lastmod' => $landingLastmod,
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
        ];

        $xml = view('sitemap', ['urls' => $urls])->render();
        $etag = md5($xml);

        return response($xml)
            ->header('Content-Type', 'application/xml')
            ->header('Cache-Control', 'public, max-age=3600')
            ->header('ETag', '"'.$etag.'"');
    }
}
