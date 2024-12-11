<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;

class CrawlerService
{

    public function scrape(Request $request)
    {
        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = $request->url;
        $containerClass = $request->container_class;
        $loop = $request->loop; // Ambil jumlah halaman dari request
        $results = [];

        // Memisahkan beberapa URL jika ada, dan membersihkan spasi
        $urls = array_map('trim', explode(",", $urls));

        foreach ($urls as $uri) {
            for ($page = 1; $page <= $loop; $page++) {
                // Menambahkan parameter halaman ke URL
                $paginatedUrl = $uri . $page;

                try {
                    $response = Http::get($paginatedUrl);

                    // Cek apakah request berhasil
                    if ($response->successful()) {
                        $body = $response->body();
                        $crawler = new Crawler($body);

                        // Mengambil teks dari elemen yang sesuai dengan class container
                        $content = $crawler->filter($containerClass)->each(function ($node) {
                            return [
                                'title' => $node->text(), // Mengambil teks dari elemen utama
                                'link' => $node->filter('a')->attr('href') ?? '#', // Ambil 'href' dari elemen <a> dalam node
                                'image' => $node->filter('img')->attr('src') ?? null // Ambil 'src' dari elemen <img> dalam node, jika ada
                            ];
                        });

                        $results[] = [
                            'page' => $page,
                            'data' => collect($content)->whereNotNull()->values()
                        ];
                    } else {
                        // Jika request tidak berhasil, tambahkan pesan error untuk URL ini
                        $results[] = [
                            "page" => $page,
                            "error" => "Failed to retrieve content from $paginatedUrl"
                        ];
                    }
                } catch (\Exception $e) {
                    // Menangkap kesalahan jika terjadi
                    $results[] = [
                        "page" => $page,
                        "error" => "Error processing $paginatedUrl: " . $e->getMessage()
                    ];
                }
            }
        }

        return response()->json($results); // Mengembalikan hasil sebagai JSON
    }

    public function scrapeDynamic(Request $request)
    {
        // Mendapatkan input untuk `list_item`, `page`, dan `container_class`
        $listItem = $request->list_item;
        $loop = $request->loop;
        $containerClass = $request->container_class;
        $results = [];

        for ($page = 1; $page <= $loop; $page++) {
            $dynamicUrl = "https://www.republika.co.id/ajax/latest_news/{$listItem}/{$page}/false/load_more/";

            try {
                $response = Http::get($dynamicUrl);

                if ($response->successful()) {
                    $body = $response->body();
                    $crawler = new Crawler($body);

                    $content = $crawler->filter($containerClass)->each(function ($node) {
                        return [
                            'title' => $node->text(),
                            'link' => $node->filter('a')->attr('href') ?? '#',
                            'image' => $node->filter('img')->attr('src') ?? null
                        ];
                    });

                    $results[] = [
                        'page' => $page,
                        'data' => collect($content)->whereNotNull()->values()
                    ];
                } else {
                    $results[] = [
                        'page' => $page,
                        'error' => "Failed to retrieve content from $dynamicUrl"
                    ];
                }
            } catch (\Exception $e) {
                $results[] = [
                    'page' => $page,
                    'error' => "Error processing $dynamicUrl: " . $e->getMessage()
                ];
            }
        }

        return response()->json($results);
    }
}
