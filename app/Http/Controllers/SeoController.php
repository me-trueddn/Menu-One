<?php

namespace App\Http\Controllers;

use App\Support\SeoPolicy;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function robots(): Response
    {
        SeoPolicy::ensureDefaults();

        $lines = [
            'User-agent: *',
            'Disallow: /platform/',
            'Disallow: /admin/',
            'Disallow: /waiter/',
            'Disallow: /cashier/',
            'Disallow: /kitchen/',
            'Disallow: /reservations/',
            'Disallow: /profile',
            'Disallow: /home',
            'Disallow: /dashboard',
        ];

        if (SeoPolicy::indexPublic()) {
            $lines[] = 'Allow: /login';
            $lines[] = 'Allow: /register';
        } else {
            $lines[] = 'Disallow: /';
        }

        $lines[] = '';
        $lines[] = 'Sitemap: '.route('seo.sitemap');

        return response(implode("\n", $lines), 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
    }

    public function sitemap(): Response
    {
        SeoPolicy::ensureDefaults();

        $urls = SeoPolicy::sitemapPaths();
        $now = now()->toAtomString();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $loc) {
            $xml .= '<url>';
            $xml .= '<loc>'.e($loc).'</loc>';
            $xml .= '<lastmod>'.$now.'</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }
}
