<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    /**
     * Serve robots.txt dynamically (not a static public/ file) so the
     * Sitemap: line always reflects the current environment's APP_URL
     * instead of a domain hardcoded at some point in the past, and so it
     * can be covered by a normal HTTP feature test — a truly static file
     * never reaches the Laravel router in a test client, since that path
     * relies on Apache's file-exists rewrite bypass, which PHPUnit's
     * test client does not simulate.
     */
    public function __invoke(): Response
    {
        $lines = [
            'User-agent: *',
            'Allow: /',
            'Allow: /build/',
            'Allow: /images/',
            '',
            'Disallow: /dashboard',
            'Disallow: /profile',
            'Disallow: /livewire/',
            '',
            'Sitemap: '.route('sitemap'),
        ];

        $body = implode("\n", $lines)."\n";

        return response($body)
            ->header('Content-Type', 'text/plain')
            ->header('Cache-Control', 'public, max-age=3600')
            ->header('ETag', '"'.md5($body).'"');
    }
}
