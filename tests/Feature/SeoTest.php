<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeoTest extends TestCase
{
    use RefreshDatabase;

    // --- Landing page ---

    public function test_landing_page_is_publicly_accessible(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_landing_page_redirects_authenticated_users_to_dashboard(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertRedirect(route('dashboard'));
    }

    public function test_landing_page_has_primary_title_tag(): void
    {
        // Blade escapes "&" to "&amp;" on output — assert the actual
        // rendered HTML, not the raw source string.
        $this->get('/')
            ->assertOk()
            ->assertSee('<title>SF-Tracker - Catat Keuangan &amp; Performa Driver ShopeeFood</title>', false);
    }

    public function test_landing_page_has_meta_description_with_target_keywords(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('name="description"', false);
        $response->assertSee('kalkulator profit ShopeeFood', false);
        $response->assertSee('efisiensi bensin motor', false);
        $response->assertSee('Catatan keuangan driver', false);
    }

    public function test_landing_page_meta_description_lands_in_150_to_155_char_snippet_window(): void
    {
        // Google truncates the SERP snippet around 155-160 chars on
        // desktop — landing meaningfully under 150 wastes available
        // snippet real estate instead of surfacing more keyword context.
        $response = $this->get('/');
        $html = $response->getContent();

        preg_match('/<meta name="description" content="([^"]*)"/', $html, $matches);

        $this->assertNotEmpty($matches, 'meta description tag not found');
        $length = strlen(html_entity_decode($matches[1]));
        $this->assertGreaterThanOrEqual(150, $length);
        $this->assertLessThanOrEqual(155, $length);
    }

    public function test_landing_page_title_lands_in_50_to_60_char_pixel_safe_window(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        preg_match('#<title>(.*?)</title>#s', $html, $matches);

        $this->assertNotEmpty($matches, 'title tag not found');
        $length = strlen(html_entity_decode($matches[1]));
        $this->assertGreaterThanOrEqual(50, $length);
        $this->assertLessThanOrEqual(60, $length);
    }

    public function test_landing_page_has_canonical_and_robots_meta(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('rel="canonical"', false);
        $response->assertSee('href="http://sf-tracker.test"', false);
        $response->assertSee('name="robots" content="index, follow"', false);
    }

    public function test_landing_page_has_open_graph_and_twitter_card_tags(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        foreach (['og:type', 'og:title', 'og:description', 'og:url', 'og:image', 'og:site_name'] as $tag) {
            $response->assertSee('property="'.$tag.'"', false);
        }
        foreach (['twitter:card', 'twitter:title', 'twitter:description', 'twitter:image'] as $tag) {
            $response->assertSee('name="'.$tag.'"', false);
        }
    }

    public function test_landing_page_has_valid_json_ld_knowledge_graph(): void
    {
        $response = $this->get('/');
        $html = $response->getContent();

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $this->assertNotEmpty($matches, 'JSON-LD script tag not found');

        $schema = json_decode($matches[1], true);
        $this->assertNotNull($schema, 'JSON-LD payload did not decode as valid JSON');
        $this->assertArrayHasKey('@graph', $schema, 'schema must be a @graph, not a flat isolated node');
        $this->assertCount(3, $schema['@graph']);

        $nodesByType = collect($schema['@graph'])->keyBy(function ($node) {
            return is_array($node['@type']) ? implode('+', $node['@type']) : $node['@type'];
        });

        $org = $nodesByType->get('Organization');
        $webApp = $nodesByType->get('WebApplication+SoftwareApplication');
        $website = $nodesByType->get('WebSite');

        $this->assertNotNull($org, 'Organization node missing from @graph');
        $this->assertNotNull($webApp, 'WebApplication+SoftwareApplication node missing from @graph');
        $this->assertNotNull($website, 'WebSite node missing from @graph');

        // Entities must be @id-linked, not duplicated inline — that's
        // what lets Google's graph parser merge them into one persistent
        // entity instead of a disposable page-scoped annotation.
        $this->assertSame($org['@id'], $webApp['publisher']['@id']);
        $this->assertSame($org['@id'], $website['publisher']['@id']);

        $this->assertSame('FinanceApplication', $webApp['applicationCategory']);
        $this->assertSame('All', $webApp['operatingSystem']);
        $this->assertSame('0', $webApp['offers']['price']);
        $this->assertSame('IDR', $webApp['offers']['priceCurrency']);
    }

    public function test_landing_page_has_zero_livewire_hydration_markers(): void
    {
        // "Zero client-side hydration lock" — the landing page must not
        // pull in Livewire's runtime at all.
        $response = $this->get('/');
        $html = $response->getContent();

        $this->assertStringNotContainsString('wire:snapshot', $html);
        $this->assertStringNotContainsString('livewire.js', $html);
    }

    // --- Sitemap ---

    public function test_sitemap_returns_200_with_xml_content_type(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
    }

    public function test_sitemap_has_valid_urlset_root_and_landing_url(): void
    {
        $response = $this->get('/sitemap.xml');
        $xml = simplexml_load_string($response->getContent());

        $this->assertNotFalse($xml, 'sitemap.xml did not parse as valid XML');
        $this->assertSame('urlset', $xml->getName());
        $this->assertCount(1, $xml->url);
        $this->assertSame(route('landing'), (string) $xml->url[0]->loc);
        $this->assertSame('weekly', (string) $xml->url[0]->changefreq);
        $this->assertSame('1.0', (string) $xml->url[0]->priority);
        $this->assertNotEmpty((string) $xml->url[0]->lastmod);
    }

    public function test_sitemap_lastmod_is_w3c_datetime_and_stable_across_requests(): void
    {
        // lastmod must reflect actual content modification time, not
        // request time — otherwise it's indistinguishable from noise and
        // Googlebot has no real freshness signal to act on.
        $first = simplexml_load_string($this->get('/sitemap.xml')->getContent());
        $second = simplexml_load_string($this->get('/sitemap.xml')->getContent());

        $lastmod = (string) $first->url[0]->lastmod;
        $this->assertMatchesRegularExpression(
            '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/',
            $lastmod
        );
        $this->assertSame($lastmod, (string) $second->url[0]->lastmod);
    }

    public function test_sitemap_sends_cache_control_and_etag(): void
    {
        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Cache-Control');
        $this->assertStringContainsString('max-age=3600', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
        $this->assertNotEmpty($response->headers->get('ETag'));
    }

    // --- Robots ---

    public function test_robots_txt_is_accessible(): void
    {
        $this->get('/robots.txt')->assertOk();
    }

    public function test_robots_txt_allows_public_landing_page(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertSee('Allow: /', false);
    }

    public function test_robots_txt_blocks_authenticated_app_paths(): void
    {
        $response = $this->get('/robots.txt');
        $body = $response->getContent();

        $this->assertStringContainsString('Disallow: /dashboard', $body);
        $this->assertStringContainsString('Disallow: /profile', $body);
        $this->assertStringContainsString('Disallow: /livewire/', $body);
    }

    public function test_robots_txt_declares_sitemap_with_current_app_url(): void
    {
        $response = $this->get('/robots.txt');
        $body = $response->getContent();

        $this->assertStringContainsString('Sitemap: '.route('sitemap'), $body);
    }

    public function test_robots_txt_sends_cache_control_and_etag(): void
    {
        $response = $this->get('/robots.txt');

        $response->assertOk();
        $response->assertHeader('Cache-Control');
        $this->assertStringContainsString('max-age=3600', $response->headers->get('Cache-Control'));
        $this->assertStringContainsString('public', $response->headers->get('Cache-Control'));
        $this->assertNotEmpty($response->headers->get('ETag'));
    }
}
