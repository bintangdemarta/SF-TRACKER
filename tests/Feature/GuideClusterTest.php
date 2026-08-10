<?php

namespace Tests\Feature;

use Tests\TestCase;

class GuideClusterTest extends TestCase
{
    public static function clusterRoutes(): array
    {
        return [
            'pillar' => ['guides.pillar'],
            'net-profit' => ['guides.net-profit'],
            'cost-per-km' => ['guides.cost-per-km'],
            'poin-insentif' => ['guides.poin-insentif'],
            'dual-wallet' => ['guides.dual-wallet'],
            'target-harian' => ['guides.target-harian'],
        ];
    }

    /**
     * @dataProvider clusterRoutes
     */
    public function test_cluster_page_is_publicly_accessible(string $routeName): void
    {
        $this->get(route($routeName))->assertOk();
    }

    /**
     * @dataProvider clusterRoutes
     */
    public function test_cluster_page_has_canonical_matching_its_own_url(string $routeName): void
    {
        $response = $this->get(route($routeName));

        $response->assertOk();
        $response->assertSee('rel="canonical" href="'.route($routeName).'"', false);
    }

    /**
     * @dataProvider clusterRoutes
     */
    public function test_cluster_page_has_open_graph_tags(string $routeName): void
    {
        $response = $this->get(route($routeName));

        $response->assertOk();
        foreach (['og:title', 'og:description', 'og:url', 'og:image'] as $tag) {
            $response->assertSee('property="'.$tag.'"', false);
        }
    }

    /**
     * @dataProvider clusterRoutes
     */
    public function test_cluster_page_has_json_ld_article_in_graph(string $routeName): void
    {
        $response = $this->get(route($routeName));
        $html = $response->getContent();

        preg_match('#<script type="application/ld\+json">(.*?)</script>#s', $html, $matches);
        $this->assertNotEmpty($matches, 'JSON-LD script tag not found on '.$routeName);

        $schema = json_decode($matches[1], true);
        $this->assertNotNull($schema);
        $this->assertArrayHasKey('@graph', $schema);

        $types = collect($schema['@graph'])->map(fn ($node) => is_array($node['@type']) ? implode('+', $node['@type']) : $node['@type']);
        $this->assertTrue($types->contains('Article'), 'Article node missing from '.$routeName);
    }

    public function test_pillar_page_links_to_all_five_spokes(): void
    {
        $response = $this->get(route('guides.pillar'));
        $response->assertOk();

        foreach (['net-profit', 'cost-per-km', 'poin-insentif', 'dual-wallet', 'target-harian'] as $spoke) {
            $response->assertSee(route('guides.'.$spoke), false);
        }
    }

    /**
     * @dataProvider clusterRoutes
     */
    public function test_cluster_page_is_registered_in_sitemap(string $routeName): void
    {
        $response = $this->get('/sitemap.xml');
        $xml = simplexml_load_string($response->getContent());

        // simplexml_load_string()'s Iterator keys every <url> sibling as
        // "url" — collect() on it collapses same-keyed entries down to
        // the last one. xpath() returns a plain 0-indexed array instead.
        // local-name() is required because <urlset> declares a default
        // xmlns — an unprefixed //url query matches nothing in that
        // namespace under XPath 1.0's rules.
        $locs = collect($xml->xpath("//*[local-name()='url']"))->map(fn ($url) => (string) $url->loc);
        $this->assertTrue($locs->contains(route($routeName)), $routeName.' missing from sitemap.xml');
    }
}
