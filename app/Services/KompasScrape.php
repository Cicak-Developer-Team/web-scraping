<?php

namespace App\Services;

use App\Services\CrawlerService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;

class KompasScrape extends CrawlerService
{
    public function kompas()
    {
        return view("kompas");
    }

    /**
     * Scrape satu halaman dan ekstrak artikel secara paralel.
     */
    private function scrapePages(array $urls)
    {
        try {
            // Parallel HTTP Request
            $responses = Http::pool(fn($pool) => array_map(fn($url) => $pool->as($url)->get($url), $urls));
            $articles = [];
            foreach ($responses as $url => $response) {
                if (!$response->successful()) continue;

                $crawler = new Crawler($response->body());
                $classItem = ".articleItem";

                if ($crawler->filter($classItem)->count() === 0) continue;

                $crawler->filter($classItem)->each(function ($node) use (&$articles) {
                    $article = $this->extractArticleData($node);
                    if ($article) {
                        $articles[] = $article;
                    }
                });
            }

            return $articles;
        } catch (Exception $e) {
            Log::error("Error during parallel scraping", ['error' => $e->getMessage()]);
            return [];
        }
    }

    /**
     * Ekstrak data artikel dari elemen yang di-scrape.
     */
    private function extractArticleData($node)
    {
        $title = trim($node->text());
        if (!$this->filterTitle($title)) {
            return null;
        }

        $link = $node->filter('a')->attr('href');
        $gambar = $node->filter('img')->attr('src');

        // Cache content agar tidak mengambil ulang artikel yang sama
        $content = Cache::remember("content:" . md5($link), now()->addHours(1), function () use ($link) {
            return $this->fetchContent($link);
        });

        return [
            "title" => $title,
            "link" => $link,
            "gambar" => $gambar,
            "content" => $content,
        ];
    }

    /**
     * Ambil dan bersihkan konten artikel (dengan cache).
     */
    private function fetchContent($url)
    {
        try {
            $response = Http::get($url);
            if (!$response->successful()) {
                return "";
            }

            $crawler = new Crawler($response->body());
            $classContent = ".read__content";

            if ($crawler->filter($classContent)->count() === 0) {
                return "";
            }

            return $this->cleanText($crawler->filter($classContent)->text());
        } catch (Exception $e) {
            Log::error("Error fetching article content: {$url}", ['error' => $e->getMessage()]);
            return "";
        }
    }

    public function kompasScrape(Request $request)
    {
        set_time_limit(0);

        $urls = $request->url;
        $dari = Carbon::parse($request->dari);
        $sampai = Carbon::parse($request->sampai);

        $dateUrls = $this->generateUrls($urls, $dari, $sampai);
        $results = [];

        foreach ($dateUrls as $formattedUrl) {
            $page = 1;
            while (true) {
                $paginatedUrls = array_map(fn($p) => $formattedUrl . $p, range($page, $page + 4));

                // Cache hasil scraping agar tidak mengambil data yang sama
                $cachedResults = Cache::remember("scrape:" . md5($urls), now()->addMinutes(30), function () use ($paginatedUrls) {
                    return $this->scrapePages($paginatedUrls);
                });

                if (empty($cachedResults)) {
                    break;
                }

                $results = array_merge($results, $cachedResults);
                $page += 5;
            }
        }

        return $this->printAndDownload($results);
    }
}
