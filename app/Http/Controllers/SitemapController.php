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
        $lastmodOf = fn (string $view) => Carbon::createFromTimestamp(
            filemtime(resource_path("views/{$view}.blade.php"))
        )->toAtomString();

        $urls = [
            [
                'loc' => route('landing'),
                'lastmod' => $lastmodOf('landing'),
                'changefreq' => 'weekly',
                'priority' => '1.0',
            ],
            [
                'loc' => route('guides.pillar'),
                'lastmod' => $lastmodOf('guides/pillar'),
                'changefreq' => 'weekly',
                'priority' => '0.9',
            ],
            [
                'loc' => route('guides.net-profit'),
                'lastmod' => $lastmodOf('guides/net-profit'),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => route('guides.cost-per-km'),
                'lastmod' => $lastmodOf('guides/cost-per-km'),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => route('guides.poin-insentif'),
                'lastmod' => $lastmodOf('guides/poin-insentif'),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => route('guides.dual-wallet'),
                'lastmod' => $lastmodOf('guides/dual-wallet'),
                'changefreq' => 'weekly',
                'priority' => '0.8',
            ],
            [
                'loc' => route('guides.target-harian'),
                'lastmod' => $lastmodOf('guides/target-harian'),
                'changefreq' => 'weekly',
                'priority' => '0.8',
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
