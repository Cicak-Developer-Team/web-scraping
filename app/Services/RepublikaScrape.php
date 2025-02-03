<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\DomCrawler\Crawler;
use Exception;

class RepublikaScrape extends CrawlerService
{
    private string $classItem = ".list-group-item";
    private string $classContent = ".article-content";

    public function republika()
    {
        return view("republika");
    }

    /**
     * Scrape satu halaman dan ekstrak artikel
     */
    private function scrapePage($url)
    {
        // Gunakan cache untuk menghindari request berulang
        return Cache::remember("scrape:" . md5($url), now()->addMinutes(30), function () use ($url) {
            try {
                $response = Http::timeout(10)->get($url);
                if (!$response->successful()) return [];

                $body = $response->body();
                $crawler = new Crawler($body);

                if ($crawler->filter($this->classItem)->count() === 0) return [];

                $articles = [];
                $crawler->filter($this->classItem)->each(function ($node) use (&$articles) {
                    $article = $this->extractArticleData($node);
                    if ($article) $articles[] = $article;
                });

                return $articles;
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$url}", ['error' => $e->getMessage()]);
                return [];
            }
        });
    }

    /**
     * Ekstrak data artikel dari elemen yang di-scrape
     */
    private function extractArticleData($node)
    {
        $title = $node->text();

        // if (!$this->filterTitle($title)) return null;

        $link = $node->filter('a')->attr('href');
        $gambar = $node->filter('img')->attr('src');

        // Ambil konten artikel secara paralel
        $content = $this->fetchContent($link);

        return [
            "title" => $title,
            "link" => $link,
            "gambar" => $gambar,
            "content" => $content,
        ];
    }

    /**
     * Ambil dan bersihkan konten artikel (menggunakan cache)
     */
    private function fetchContent($url)
    {
        return Cache::remember("content:" . md5($url), now()->addMinutes(30), function () use ($url) {
            try {
                $response = Http::timeout(10)->get($url);
                if (!$response->successful()) return "";

                $crawler = new Crawler($response->body());
                if ($crawler->filter($this->classContent)->count() === 0) return "";

                return $this->cleanText($crawler->filter($this->classContent)->text());
            } catch (Exception $e) {
                Log::error("Error fetching article content: {$url}", ['error' => $e->getMessage()]);
                return "";
            }
        });
    }

    public function republikaScrape(Request $request)
    {
        set_time_limit(0);

        $urls = $request->url;
        $loop = $request->loop;
        $results = [];

        $requests = [];

        // Buat request paralel untuk semua halaman
        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = str_replace("[page]", $page, $urls);
            $requests[] = Http::async()->timeout(30)->get($paginatedUrl);
        }

        // Kirim request paralel
        $responses = Http::pool(fn() => $requests);
        // Proses hasilnya
        foreach ($requests as $url => $request) {
            $response = $responses[$url] ?? null;
            dd($request);
            if ($response && $response->successful()) {
                $results = array_merge($results, $this->scrapePage($url));
            }
        }

        return $this->printAndDownload($results);
    }
}
