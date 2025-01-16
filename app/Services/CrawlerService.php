<?php

namespace App\Services;

use App\Exports\UsersExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
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
                            $link = $node->filter('a')->attr('href') ?? '#';
                            $articleContent = '';

                            // Scrape artikel di dalam link jika URL valid
                            if ($link !== '#' && filter_var($link, FILTER_VALIDATE_URL)) {
                                try {
                                    $articleResponse = Http::get($link);

                                    if ($articleResponse->successful()) {
                                        $articleBody = $articleResponse->body();
                                        $articleCrawler = new Crawler($articleBody);

                                        // Ambil konten utama dari artikel, sesuaikan class atau selector jika perlu
                                        $articleContent = $articleCrawler->filter('.read__content')->text(null);
                                    }
                                } catch (\Exception $e) {
                                    $articleContent = 'Failed to scrape article: ' . $e->getMessage();
                                }
                            }

                            return [
                                'title' => $node->text(), // Mengambil teks dari elemen utama
                                'link' => $link, // Ambil 'href' dari elemen <a> dalam node
                                'image' => $node->filter('img')->attr('src') ?? null,
                                'sub' => $node->filter('.wSpec-subtitle')->text(null),
                                'artikel' => $articleContent
                            ];
                        });

                        $results[] = [
                            'data' => collect($content)->whereNotNull()->values()->toArray()
                        ];
                    } else {
                        // Jika request tidak berhasil, tambahkan pesan error untuk URL ini
                        $results[] = [
                            'error' => "Failed to retrieve content from $paginatedUrl"
                        ];
                    }
                } catch (\Exception $e) {
                    // Menangkap kesalahan jika terjadi
                    $results[] = [
                        'error' => "Error processing $paginatedUrl: " . $e->getMessage()
                    ];
                }
            }
        }

        return $results;
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
                            'image' => $node->filter('.lozad')->attr('src') ?? null,
                            'sub' => $node->filter('.wSpec-subtitle')->text() ?? null,
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

    public function print(Request $request)
    {
        $data = $this->scrape($request);
        return Excel::download(new UsersExport(collect($data)->flatten(2)), time() . '.xlsx');
    }

    public function printAndDownload($data)
    {
        return Excel::download(new UsersExport($data), time() . '.xlsx');
    }

    // web scrape
    public function KompasScrape(Request $request)
    {
        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = $request->url;
        $loop = $request->loop; // Ambil jumlah halaman dari request
        $results = [];

        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = $urls . $page;
            $response = Http::get($paginatedUrl);
            if ($response->successful()) {
                $body = $response->body();
                $crawler = new Crawler($body);

                // items clas
                $crawler->filter(".articleItem")->each(function ($node) use (&$results) {
                    $link = $node->filter('a')->attr('href');
                    $responseLinkNode = Http::get($link);
                    $crawlerSec = new Crawler($responseLinkNode->body());
                    // content class
                    $text = $crawlerSec->filter(".read__content")->text();

                    // Hapus pola umum JavaScript (fleksibel)
                    $text = preg_replace([
                        '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/is',
                        '/\b(var|let|const)\s+\w+\s*=.*?;/is',                     // Hapus deklarasi variabel
                        '/\bfunction\s+\w*\s*\([^)]*\)\s*{[^}]*}/is',              // Hapus deklarasi fungsi
                        '/\bnew\s+\w+\([^)]*\);?/is',                              // Hapus instansiasi objek
                        '/\bif\s*\([^)]*\)\s*{[^}]*}/is',                          // Hapus blok kondisi if
                        '/\bwhile\s*\([^)]*\)\s*{[^}]*}/is',                       // Hapus blok while
                        '/\bfor\s*\([^)]*\)\s*{[^}]*}/is',                         // Hapus blok for
                        '/\bxhr\.[a-z]+\([^)]*\);/is',                             // Hapus metode xhr
                        '/\bconsole\.[a-z]+\([^)]*\);/is',                         // Hapus log konsol
                        '/\breturn\b[^;]+;/is',                                    // Hapus pernyataan return
                        '/\bdocument\.[a-z]+\([^)]*\)\s*[^;]*;/is',                // Hapus manipulasi DOM
                        '/[a-zA-Z_$][\w$]*\.addEventListener\([^)]*\)\s*{[^}]*}/is', // Hapus event listener
                        '/}\s*else\s*{\s*}/is',                                    // Hapus blok else kosong
                        '/{\s*}/is'                                                // Hapus blok kosong secara umum
                    ], '', $text);
                    $text = str_replace(" } });", "", $text);
                    // Hapus semua tag HTML
                    $text = strip_tags($text);
                    // Hapus spasi ekstra
                    $text = trim(preg_replace('/\s+/', ' ', $text));
                    $results[] = [
                        "title" => $node->text(),
                        "link" => $link,
                        "gambar" => $node->filter('img')->attr('src'),
                        "content" => $text
                    ];
                });
            }
        }
        return $this->printAndDownload($results);
    }

    public function republikaScrape(Request $request)
    {
        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = $request->url;
        $loop = $request->loop; // Ambil jumlah halaman dari request
        $results = [];

        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = str_replace("[page]", $page, $urls);
            $response = Http::get($paginatedUrl);
            if ($response->successful()) {
                $body = $response->body();
                $crawler = new Crawler($body);

                // items clas
                $crawler->filter(".list-group-item")->each(function ($node) use (&$results) {
                    $link = $node->filter('a')->attr('href');
                    $responseLinkNode = Http::get($link);
                    $crawlerSec = new Crawler($responseLinkNode->body());
                    // content class
                    $text = $crawlerSec->filter(".article-content")->text();

                    $text = str_replace(" } });", "", $text);
                    // Hapus semua tag HTML
                    $text = strip_tags($text);
                    // Hapus spasi ekstra
                    $text = trim(preg_replace('/\s+/', ' ', $text));
                    $results[] = [
                        "title" => $node->text(),
                        "link" => $link,
                        "gambar" => $node->filter('img')->attr('src'),
                        "content" => $text
                    ];
                });
            }
        }
        return $this->printAndDownload($results);
    }
}
