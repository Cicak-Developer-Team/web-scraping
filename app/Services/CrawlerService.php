<?php

namespace App\Services;

use App\Exports\UsersExport;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\DomCrawler\Crawler;

class CrawlerService
{
    protected $filter = [
        "Keberlanjutan Lingkungan",
        "Etika Lingkungan",
        "Rendah Karbon",
        "Pengurangan Emisi",
        "Emisi Gas Rumah Kaca",
        "Emisi Karbon",
        "Emisi CO2",
        "Emisi Gas Rumah Kaca",
        // "Hijau",
        "Polusi",
        "Perlindungan Lingkungan",
        "Nomor"
    ];

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

    // helper
    public function print(Request $request)
    {
        $data = $this->scrape($request);
        return Excel::download(new UsersExport(collect($data)->flatten(2)), time() . '.xlsx');
    }

    public function printAndDownload($data)
    {
        return Excel::download(new UsersExport($data), time() . '.xlsx');
    }
    private function processPageContent(string $body, array &$results, string $classItem, string $classContent): void
    {
        $crawler = new Crawler($body);

        $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent) {
            $kataTeks = preg_split('/\s+/', $node->text());
            $kataDitemukan = array_intersect($this->filter ?? [], $kataTeks);

            if (!empty($kataDitemukan)) {
                $this->processNode($node, $results, $classContent);
            }
        });
    }

    private function processNode(Crawler $node, array &$results, string $classContent): void
    {
        try {
            $link = $node->filter('a')->attr('href');
            $image = $node->filter('img')->attr('src');

            $response = Http::get($link);
            if ($response->successful()) {
                $crawlerSec = new Crawler($response->body());
                $text = $crawlerSec->filter($classContent)->text();

                $results[] = [
                    'title' => trim($node->text()),
                    'link' => $link,
                    'gambar' => $image,
                    'content' => $this->cleanText($text)
                ];
            }
        } catch (Exception $e) {
            Log::error('Error processing node', ['error' => $e->getMessage()]);
        }
    }

    private function cleanText(string $text): string
    {
        $text = str_replace(' } });', '', $text);
        $text = htmlspecialchars_decode(strip_tags($text));
        return trim(preg_replace('/\s+/', ' ', $text));
    }

    protected function filterTitle($title)
    {
        foreach ($this->filter as $keyword) {
            if (stripos($title, $keyword) !== false) {
                return true; // Jika title mengandung salah satu kata kunci, ambil data
            }
        }
        return false; // Jika tidak ada kata kunci yang ditemukan, lewati data
    }

    // web scrape
    public function KompasScrape(Request $request)
    {
        set_time_limit(0);

        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = $request->url;
        $loop = $request->loop; // Ambil jumlah halaman dari request
        $results = [];

        $classItem = ".articleItem";      // Class untuk item artikel
        $classContent = ".read__content"; // Class untuk konten artikel

        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = $urls . $page;

            try {
                $response = Http::get($paginatedUrl);

                if ($response->successful()) {
                    $body = $response->body();
                    $crawler = new Crawler($body);

                    // Iterasi setiap item artikel
                    $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent) {
                        $title = trim($node->text());

                        // Terapkan filter judul
                        if ($this->filterTitle($title)) {
                            // Ambil link dan gambar
                            $link = $node->filter('a')->attr('href');
                            $gambar = $node->filter('img')->attr('src');

                            $responseLinkNode = Http::get($link);

                            if ($responseLinkNode->successful()) {
                                $crawlerSec = new Crawler($responseLinkNode->body());
                                $text = "";

                                // Ambil konten artikel jika ada
                                if ($crawlerSec->filter($classContent)->count() > 0) {
                                    $text = $crawlerSec->filter($classContent)->text();
                                }

                                // Bersihkan konten dari tag HTML dan pola JavaScript
                                $text = preg_replace([
                                    '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/is',
                                    '/\b(var|let|const)\s+\w+\s*=.*?;/is',
                                    '/\bfunction\s+\w*\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bnew\s+\w+\([^)]*\);?/is',
                                    '/\bif\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bwhile\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bfor\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bxhr\.[a-z]+\([^)]*\);/is',
                                    '/\bconsole\.[a-z]+\([^)]*\);/is',
                                    '/\breturn\b[^;]+;/is',
                                    '/\bdocument\.[a-z]+\([^)]*\)\s*[^;]*;/is',
                                    '/[a-zA-Z_$][\w$]*\.addEventListener\([^)]*\)\s*{[^}]*}/is',
                                    '/}\s*else\s*{\s*}/is',
                                    '/{\s*}/is'
                                ], '', $text);
                                $text = str_replace(" } });", "", $text);
                                $text = strip_tags($text);
                                $text = trim(preg_replace('/\s+/', ' ', $text));

                                // Simpan data ke hasil
                                $results[] = [
                                    "title" => $title,
                                    "link" => $link,
                                    "gambar" => $gambar,
                                    "content" => $text,
                                ];
                            }
                        }
                    });
                }
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$paginatedUrl}", ['error' => $e->getMessage()]);
            }
        }

        return $this->printAndDownload($results);
    }

    public function republikaScrape(Request $request)
    {
        set_time_limit(0);

        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = $request->url;
        $loop = $request->loop;
        $results = [];

        $classItem = ".list-group-item";
        $classContent = ".article-content";

        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = str_replace("[page]", $page, $urls);

            try {
                $response = Http::get($paginatedUrl);

                if ($response->successful()) {
                    $body = $response->body();
                    $crawler = new Crawler($body);

                    // Iterasi setiap item berdasarkan class item
                    $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent) {
                        $title = trim($node->text());

                        // Filter judul berdasarkan kata kunci
                        if ($this->filterTitle($title)) {
                            $link = $node->filter('a')->attr('href');
                            $gambar = $node->filter('img')->attr('src');

                            $responseLinkNode = Http::get($link);
                            if ($responseLinkNode->successful()) {
                                $crawlerSec = new Crawler($responseLinkNode->body());
                                $text = "";

                                if ($crawlerSec->filter($classContent)->count() > 0) {
                                    $text = $crawlerSec->filter($classContent)->text();
                                }

                                // Membersihkan konten dari tag HTML dan spasi ekstra
                                $text = str_replace(" } });", "", $text);
                                $text = strip_tags($text);
                                $text = trim(preg_replace('/\s+/', ' ', $text));

                                $item = [
                                    "title" => $title,
                                    "link" => $link,
                                    "gambar" => $gambar,
                                    "content" => $text,
                                ];

                                $results[] = $item;
                            }
                        }
                    });
                }
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$paginatedUrl}", ['error' => $e->getMessage()]);
            }
        }

        return $this->printAndDownload($results);
    }

    public function okezoneScrape(Request $request)
    {
        set_time_limit(0);

        // Mendapatkan input URL, jumlah loop, dan hasil array
        $urls = $request->url;
        $loop = $request->loop;
        $results = [];

        // Inisialisasi nilai number1 dan number2
        $number1 = time(); // Nilai awal number1
        $number2 = 33; // Nilai awal number2

        for ($page = 1; $page <= $loop; $page++) {
            // Format URL berdasarkan number1 dan number2
            $paginatedUrl = $urls . "/0/" . $number1 . "/" . $number2 . "/12";

            try {
                $response = Http::get($paginatedUrl);
                if ($response->successful()) {
                    $body = $response->body();
                    $crawler = new Crawler($body);
                    $crawler->filter(".list-contentx li")->each(function ($node) use (&$results) {
                        $title = trim($node->text());

                        if ($this->filterTitle($title)) {
                            $link = $node->filter('a')->attr('href');
                            $gambar = $node->filter('img')->attr('src');

                            $responseLinkNode = Http::get($link);
                            if ($responseLinkNode->successful()) {
                                $crawlerSec = new Crawler($responseLinkNode->body());
                                $text = $crawlerSec->filter(".c-detail")->count() > 0
                                    ? $crawlerSec->filter(".c-detail")->text()
                                    : "";

                                // Bersihkan teks dari elemen yang tidak diperlukan
                                $text = preg_replace([
                                    '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/is',
                                    '/\b(var|let|const)\s+\w+\s*=.*?;/is',
                                    '/\bfunction\s+\w*\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bnew\s+\w+\([^)]*\);?/is',
                                    '/\bif\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bwhile\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bfor\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bxhr\.[a-z]+\([^)]*\);/is',
                                    '/\bconsole\.[a-z]+\([^)]*\);/is',
                                    '/\breturn\b[^;]+;/is',
                                    '/\bdocument\.[a-z]+\([^)]*\)\s*[^;]*;/is',
                                    '/[a-zA-Z_$][\w$]*\.addEventListener\([^)]*\)\s*{[^}]*}/is',
                                    '/}\s*else\s*{\s*}/is',
                                    '/{\s*}/is'
                                ], '', $text);

                                $text = str_replace(" } });", "", $text);
                                $text = strip_tags($text);
                                $text = trim(preg_replace('/\s+/', ' ', $text));

                                $results[] = [
                                    "title" => $title,
                                    "link" => $link,
                                    "gambar" => $gambar,
                                    "content" => $text
                                ];
                            }
                        }
                    });
                }
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$paginatedUrl}", ['error' => $e->getMessage()]);
            }

            // Perbarui number1 dan number2 untuk iterasi berikutnya
            $number1 -= 3000;
            $number2 += 12;
        }

        return $this->printAndDownload($results);
    }

    // Sesuai tahun
    public function kontanScrape(Request $request)
    {
        set_time_limit(0);

        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = $request->url;
        $loop = $request->loop;
        $results = [];

        $starPage = 0;
        $classItem = '.list-berita ul li';

        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = $urls . $starPage;

            try {
                $response = Http::get($paginatedUrl);

                if ($response->successful()) {
                    $body = $response->body();
                    $crawler = new Crawler($body);

                    $crawler->filter($classItem)->each(function ($node) use (&$results) {
                        $title = trim($node->text());

                        if ($this->filterTitle($title)) {
                            $link = $node->filter('a')->attr('href');
                            $gambar = $node->filter('img')->attr('src');

                            $responseLinkNode = Http::get($link);
                            if ($responseLinkNode->successful()) {
                                $crawlerSec = new Crawler($responseLinkNode->body());
                                $text = "";

                                if ($crawlerSec->filter('.tmpt-desk-kon')->count() > 0) {
                                    $text = $crawlerSec->filter('.tmpt-desk-kon')->text();
                                } else if ($crawlerSec->filter('#release-content')->count() > 0) {
                                    $text = $crawlerSec->filter('#release-content')->text();
                                } else if ($crawlerSec->filter('.ctn')->count() > 0) {
                                    $text = $crawlerSec->filter('.ctn')->text();
                                }

                                $text = str_replace(' } });', '', $text);
                                $text = strip_tags($text);
                                $text = trim(preg_replace('/\s+/', ' ', $text));

                                $results[] = [
                                    "title" => $title,
                                    "link" => $link,
                                    "gambar" => $gambar,
                                    "content" => $text
                                ];
                            }
                        }
                    });
                }
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$paginatedUrl}", ['error' => $e->getMessage()]);
            }

            $starPage += 20;
        }

        return $this->printAndDownload($results);
    }

    public function bisnisScrape(Request $request)
    {
        set_time_limit(0);
        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = $request->url;
        $loop = $request->loop;
        $results = [];

        $classItem = '.artItem';
        $classContent = '.detailsContent';

        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = $urls . $page;

            try {
                $response = Http::get($paginatedUrl);

                if ($response->successful()) {
                    $body = $response->body();
                    $crawler = new Crawler($body);

                    $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent) {
                        $title = trim($node->text());

                        if ($this->filterTitle($title)) {
                            $link = $node->filter('a')->attr('href');
                            $gambar = $node->filter('img')->attr('src');

                            $responseLinkNode = Http::get($link);
                            if ($responseLinkNode->successful()) {
                                $crawlerSec = new Crawler($responseLinkNode->body());
                                $text = "";

                                if ($crawlerSec->filter($classContent)->count() > 0) {
                                    $text = $crawlerSec->filter($classContent)->text();
                                }

                                $text = str_replace(' } });', '', $text);
                                $text = strip_tags($text);
                                $text = trim(preg_replace('/\s+/', ' ', $text));

                                $results[] = [
                                    "title" => $title,
                                    "link" => $link,
                                    "gambar" => $gambar,
                                    "content" => $text
                                ];
                            }
                        }
                    });
                }
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$paginatedUrl}", ['error' => $e->getMessage()]);
            }
        }

        if (empty($results)) {
            dd(response()->json(['message' => 'Tidak ada konten yang mengandung kata dari filter'], 404));
        }

        return $this->printAndDownload($results);
    }

    public function pikiranrakyatScrape(Request $request)
    {
        set_time_limit(0);

        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = $request->url;
        $loop = $request->loop; // Ambil jumlah halaman dari request
        $results = [];

        $classItem = ".latest__item";      // Class untuk item artikel
        $classContent = ".read__content"; // Class untuk konten artikel

        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = $urls . $page;
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                    'Referer' => 'https://www.google.com/',
                ])->get($paginatedUrl);
                dump($response->successful());
                dd($response->body());
                $body = $response->body();
                $crawler = new Crawler($body);
                // Iterasi setiap item artikel
                $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent) {
                    $title = trim($node->text());

                    // Terapkan filter judul
                    if ($this->filterTitle($title)) {
                        // Ambil link dan gambar
                        $link = $node->filter('a')->attr('href');
                        $gambar = $node->filter('img')->attr('src');

                        $responseLinkNode = Http::get($link);

                        if ($responseLinkNode->successful()) {
                            $crawlerSec = new Crawler($responseLinkNode->body());
                            $text = "";

                            // Ambil konten artikel jika ada
                            if ($crawlerSec->filter($classContent)->count() > 0) {
                                $text = $crawlerSec->filter($classContent)->text();
                            }

                            $text = strip_tags($text);
                            $text = trim(preg_replace('/\s+/', ' ', $text));

                            // Simpan data ke hasil
                            $results[] = [
                                "title" => $title,
                                "link" => $link,
                                "gambar" => $gambar,
                                "content" => $text,
                            ];
                        }
                    }
                });
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$paginatedUrl}", ['error' => $e->getMessage()]);
            }
        }

        return $this->printAndDownload($results);
    }

    public function mediaindoScrape(Request $request)
    {
        set_time_limit(0);

        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = $request->url;
        $loop = $request->loop; // Ambil jumlah halaman dari request
        $results = [];

        $classItem = ".list-3 li";      // Class untuk item artikel
        $classContent = ".article"; // Class untuk konten artikel

        $startPage = 0;
        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = $urls . $page . $startPage;
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                    'Referer' => 'https://www.google.com/',
                ])->get($paginatedUrl);
                $body = $response->body();
                $crawler = new Crawler($body);
                // Iterasi setiap item artikel
                $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent) {
                    $title = trim($node->text());

                    // Terapkan filter judul
                    if ($this->filterTitle($title)) {
                        // Ambil link dan gambar
                        $link = $node->filter('a')->attr('href');
                        $gambar = $node->filter('img')->attr('src');

                        $responseLinkNode = Http::get($link);

                        if ($responseLinkNode->successful()) {
                            $crawlerSec = new Crawler($responseLinkNode->body());
                            $text = "";

                            // Ambil konten artikel jika ada
                            if ($crawlerSec->filter($classContent)->count() > 0) {
                                $text = $crawlerSec->filter($classContent)->text();
                            }

                            $text = strip_tags($text);
                            $text = trim(preg_replace('/\s+/', ' ', $text));

                            // Simpan data ke hasil
                            $results[] = [
                                "title" => $title,
                                "link" => $link,
                                "gambar" => $gambar,
                                "content" => $text,
                            ];
                        }
                    }
                });

                $startPage += 20;
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$paginatedUrl}", ['error' => $e->getMessage()]);
            }
        }

        return $this->printAndDownload($results);
    }

    public function jawaScrape(Request $request)
    {
        set_time_limit(0);

        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = $request->url;
        $loop = $request->loop; // Ambil jumlah halaman dari request
        $results = [];

        $classItem = ".latest__item";      // Class untuk item artikel
        $classContent = "#article";        // Class untuk konten artikel
        $classPagination = ".paging__wrap .paging__link"; // Selector untuk pagination link

        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = $urls . $page;
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                    'Referer' => 'https://www.google.com/',
                ])->get($paginatedUrl);
                $body = $response->body();
                $crawler = new Crawler($body);
                dd($response->body());
                dd($crawler->filter($classItem)->count());
                // Iterasi setiap item artikel  
                $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent, $classPagination) {
                    $title = trim($node->text());

                    // Terapkan filter judul
                    if ($this->filterTitle($title)) {
                        // Ambil link dan gambar
                        $link = $node->filter('a')->attr('href');
                        $gambar = $node->filter('img')->attr('src');

                        $responseLinkNode = Http::get($link);

                        if ($responseLinkNode->successful()) {
                            $crawlerSec = new Crawler($responseLinkNode->body());
                            $text = "";

                            // Ambil konten artikel dari semua halaman
                            do {
                                if ($crawlerSec->filter($classContent)->count() > 0) {
                                    $text .= $crawlerSec->filter($classContent)->text();
                                }

                                // Cari tautan ke halaman berikutnya
                                $nextPageLink = null;
                                $crawlerSec->filter($classPagination)->each(function ($paginationNode) use (&$nextPageLink) {
                                    if (stripos($paginationNode->text(), 'Selanjutnya') !== false) {
                                        $nextPageLink = $paginationNode->attr('href');
                                    }
                                });

                                if ($nextPageLink) {
                                    $responseLinkNode = Http::get($nextPageLink);
                                    $crawlerSec = new Crawler($responseLinkNode->body());
                                }
                            } while ($nextPageLink);

                            // Bersihkan teks
                            $text = strip_tags($text);
                            $text = trim(preg_replace('/\s+/', ' ', $text));

                            // Simpan data ke hasil
                            $results[] = [
                                "title" => $title,
                                "link" => $link,
                                "gambar" => $gambar,
                                "content" => $text,
                            ];
                        }
                    }
                });
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$paginatedUrl}", ['error' => $e->getMessage()]);
            }
        }

        return $this->printAndDownload($results);
    }

    public function detiksportScrape(Request $request)
    {
        set_time_limit(0);

        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = $request->url;
        $loop = $request->loop; // Ambil jumlah halaman dari request
        $results = [];

        $classItem = ".list-content__item";      // Class untuk item artikel
        $classContent = ".detail__body-text"; // Class untuk konten artikel

        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = $urls . $page;
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                    'Referer' => 'https://www.google.com/',
                ])->get($paginatedUrl);
                $body = $response->body();
                $crawler = new Crawler($body);
                // Iterasi setiap item artikel
                $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent) {
                    $title = trim($node->text());

                    // Terapkan filter judul
                    if ($this->filterTitle($title)) {
                        // Ambil link dan gambar
                        $link = $node->filter('a')->attr('href');
                        $gambar = $node->filter('img')->attr('src');

                        $responseLinkNode = Http::get($link);

                        if ($responseLinkNode->successful()) {
                            $crawlerSec = new Crawler($responseLinkNode->body());
                            $text = "";

                            // Ambil konten artikel jika ada
                            if ($crawlerSec->filter($classContent)->count() > 0) {
                                $text = $crawlerSec->filter($classContent)->text();
                            }

                            $text = strip_tags($text);
                            $text = trim(preg_replace('/\s+/', ' ', $text));

                            // Simpan data ke hasil
                            $results[] = [
                                "title" => $title,
                                "link" => $link,
                                "gambar" => $gambar,
                                "content" => $text,
                            ];
                        }
                    }
                });
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$paginatedUrl}", ['error' => $e->getMessage()]);
            }
        }

        return $this->printAndDownload($results);
    }

    public function sindonewsScrape(Request $request)
    {
        set_time_limit(0);

        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = rtrim($request->url, '/'); // Menghapus trailing slash jika ada
        $loop = $request->loop; // Ambil jumlah halaman dari request
        $results = [];

        $classItem = ".article-grid";      // Class untuk item artikel
        $classContent = ".detail-desc"; // Class untuk konten artikel

        $offset = 0;
        $articlesPerPage = 36; // Berdasarkan analisis URL

        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = $urls . ($offset > 0 ? "/$offset" : "");
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                    'Referer' => 'https://www.google.com/',
                ])->get($paginatedUrl);

                $body = $response->body();
                $crawler = new Crawler($body);
                // Iterasi setiap item artikel
                $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent) {
                    $title = trim($node->text());
                    // Terapkan filter judul
                    if ($this->filterTitle($title)) {
                        // Ambil link dan gambar
                        $link = $node->filter('a')->attr('href');
                        $gambar = $node->filter('img')->attr('src');

                        $responseLinkNode = Http::get($link);

                        if ($responseLinkNode->successful()) {
                            $crawlerSec = new Crawler($responseLinkNode->body());
                            $text = "";

                            // Ambil konten artikel jika ada
                            if ($crawlerSec->filter($classContent)->count() > 0) {
                                $text = $crawlerSec->filter($classContent)->text();
                            }

                            $text = strip_tags($text);
                            $text = trim(preg_replace('/\s+/', ' ', $text));

                            // Simpan data ke hasil
                            $results[] = [
                                "title" => $title,
                                "link" => $link,
                                "gambar" => $gambar,
                                "content" => $text,
                            ];
                        }
                    }
                });

                $offset += $articlesPerPage;
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$paginatedUrl}", ['error' => $e->getMessage()]);
            }
        }

        return $this->printAndDownload($results);
    }

    // Sesuai tahun
    public function boalsportScrape(Request $request)
    {
        set_time_limit(0);

        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = rtrim($request->url, '/'); // Menghapus trailing slash jika ada
        $loop = $request->loop; // Ambil jumlah halaman dari request
        $results = [];

        $classItem = ".news-list__item";      // Class untuk item artikel
        $classContent = ".read__right"; // Class untuk konten artikel

        $offset = 0;
        $articlesPerPage = 36; // Berdasarkan analisis URL

        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = $urls . ($offset > 0 ? "/$offset" : "");
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                    'Referer' => 'https://www.google.com/',
                ])->get($paginatedUrl);

                $body = $response->body();
                $crawler = new Crawler($body);
                // Iterasi setiap item artikel
                $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent) {
                    $title = trim($node->text());
                    // Terapkan filter judul
                    if ($this->filterTitle($title)) {
                        // Ambil link dan gambar
                        $link = $node->filter('a')->attr('href');
                        $gambar = $node->filter('img')->attr('src');

                        $responseLinkNode = Http::get($link);

                        if ($responseLinkNode->successful()) {
                            $crawlerSec = new Crawler($responseLinkNode->body());
                            $text = "";

                            // Ambil konten artikel jika ada
                            if ($crawlerSec->filter($classContent)->count() > 0) {
                                $text = $crawlerSec->filter($classContent)->text();
                            }

                            $text = strip_tags($text);
                            $text = trim(preg_replace('/\s+/', ' ', $text));

                            // Simpan data ke hasil
                            $results[] = [
                                "title" => $title,
                                "link" => $link,
                                "gambar" => $gambar,
                                "content" => $text,
                            ];
                        }
                    }
                });

                $offset += $articlesPerPage;
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$paginatedUrl}", ['error' => $e->getMessage()]);
            }
        }

        return $this->printAndDownload($results);
    }

    public function moneykompasScrape(Request $request)
    {
        set_time_limit(0);

        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = $request->url;
        $loop = $request->loop; // Ambil jumlah halaman dari request
        $results = [];

        $classItem = ".articleItem";      // Class untuk item artikel
        $classContent = ".read__content"; // Class untuk konten artikel

        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = $urls . $page;

            try {
                $response = Http::get($paginatedUrl);

                if ($response->successful()) {
                    $body = $response->body();
                    $crawler = new Crawler($body);

                    // Iterasi setiap item artikel
                    $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent) {
                        $title = trim($node->text());

                        // Terapkan filter judul
                        if ($this->filterTitle($title)) {
                            // Ambil link dan gambar
                            $link = $node->filter('a')->attr('href');
                            $gambar = $node->filter('img')->attr('src');

                            $responseLinkNode = Http::get($link);

                            if ($responseLinkNode->successful()) {
                                $crawlerSec = new Crawler($responseLinkNode->body());
                                $text = "";

                                // Ambil konten artikel jika ada
                                if ($crawlerSec->filter($classContent)->count() > 0) {
                                    $text = $crawlerSec->filter($classContent)->text();
                                }

                                // Bersihkan konten dari tag HTML dan pola JavaScript
                                $text = preg_replace([
                                    '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/is',
                                    '/\b(var|let|const)\s+\w+\s*=.*?;/is',
                                    '/\bfunction\s+\w*\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bnew\s+\w+\([^)]*\);?/is',
                                    '/\bif\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bwhile\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bfor\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bxhr\.[a-z]+\([^)]*\);/is',
                                    '/\bconsole\.[a-z]+\([^)]*\);/is',
                                    '/\breturn\b[^;]+;/is',
                                    '/\bdocument\.[a-z]+\([^)]*\)\s*[^;]*;/is',
                                    '/[a-zA-Z_$][\w$]*\.addEventListener\([^)]*\)\s*{[^}]*}/is',
                                    '/}\s*else\s*{\s*}/is',
                                    '/{\s*}/is'
                                ], '', $text);
                                $text = str_replace(" } });", "", $text);
                                $text = strip_tags($text);
                                $text = trim(preg_replace('/\s+/', ' ', $text));

                                // Simpan data ke hasil
                                $results[] = [
                                    "title" => $title,
                                    "link" => $link,
                                    "gambar" => $gambar,
                                    "content" => $text,
                                ];
                            }
                        }
                    });
                }
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$paginatedUrl}", ['error' => $e->getMessage()]);
            }
        }

        return $this->printAndDownload($results);
    }

    public function rmidScrape(Request $request)
    {
        set_time_limit(0);

        // Mendapatkan input tanggal "dari" dan "sampai"
        $url = $request->url;
        $dari = Carbon::parse($request->dari);
        $sampai = Carbon::parse($request->sampai);
        $results = [];

        $classItem = ".post";  // Class untuk item artikel
        $classContent = ".isi-berita"; // Class untuk konten artikel

        // Loop dari tanggal "dari" hingga "sampai"
        while ($dari->lte($sampai)) {
            $formattedDate = $dari->format('d-m-Y');
            $paginatedUrl = "$url{$formattedDate}";
            try {
                $response = Http::get($paginatedUrl);
                if ($response->successful()) {
                    $body = $response->body();
                    $crawler = new Crawler($body);

                    // Iterasi setiap item artikel
                    $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent) {
                        $title = trim($node->text());

                        // Terapkan filter judul
                        if ($this->filterTitle($title)) {
                            // Ambil link dan gambar
                            $link = $node->filter('a')->attr('href');
                            $gambar = $node->filter('img')->attr('src');

                            $responseLinkNode = Http::get($link);

                            if ($responseLinkNode->successful()) {
                                $crawlerSec = new Crawler($responseLinkNode->body());
                                $text = "";

                                // Ambil konten artikel jika ada
                                if ($crawlerSec->filter($classContent)->count() > 0) {
                                    $text = $crawlerSec->filter($classContent)->text();
                                }

                                // Bersihkan konten dari tag HTML dan pola JavaScript
                                $text = preg_replace([
                                    '/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/is',
                                    '/\b(var|let|const)\s+\w+\s*=.*?;/is',
                                    '/\bfunction\s+\w*\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bnew\s+\w+\([^)]*\);?/is',
                                    '/\bif\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bwhile\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bfor\s*\([^)]*\)\s*{[^}]*}/is',
                                    '/\bxhr\.[a-z]+\([^)]*\);/is',
                                    '/\bconsole\.[a-z]+\([^)]*\);/is',
                                    '/\breturn\b[^;]+;/is',
                                    '/\bdocument\.[a-z]+\([^)]*\)\s*[^;]*;/is',
                                    '/[a-zA-Z_$][\w$]*\.addEventListener\([^)]*\)\s*{[^}]*}/is',
                                    '/}\s*else\s*{\s*}/is',
                                    '/{\s*}/is'
                                ], '', $text);
                                $text = str_replace(" } });", "", $text);
                                $text = strip_tags($text);
                                $text = trim(preg_replace('/\s+/', ' ', $text));

                                // Simpan data ke hasil
                                $results[] = [
                                    "title" => $title,
                                    "link" => $link,
                                    "gambar" => $gambar,
                                    "content" => $text,
                                ];
                            }
                        }
                    });
                }
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$paginatedUrl}", ['error' => $e->getMessage()]);
            }

            // Tambah satu hari untuk iterasi berikutnya
            $dari->addDay();
        }

        return $this->printAndDownload($results);
    }

    public function thejakartapostScrape(Request $request)
    {
        set_time_limit(0);
        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = $request->url;
        $loop = $request->loop;
        $results = [];

        $classItem = '.listNews';
        $classContent = '.tjp-single__content';

        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = $urls . $page;

            try {
                $response = Http::get($paginatedUrl);

                if ($response->successful()) {
                    $body = $response->body();
                    $crawler = new Crawler($body);
                    $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent) {
                        $title = trim($node->filter(".titleNews")->text());
                        if ($this->filterTitle($title)) {
                            $link = "https://www.thejakartapost.com" . $node->filter('a')->attr('href');
                            $gambar = $node->filter('img')->attr('src');
                            $responseLinkNode = Http::get($link);
                            if ($responseLinkNode->successful()) {
                                $crawlerSec = new Crawler($responseLinkNode->body());
                                $text = "";

                                if ($crawlerSec->filter($classContent)->count() > 0) {
                                    $text = $crawlerSec->filter($classContent)->text();
                                }

                                $text = str_replace(' } });', '', $text);
                                $text = strip_tags($text);
                                $text = trim(preg_replace('/\s+/', ' ', $text));

                                $results[] = [
                                    "title" => $title,
                                    "link" => $link,
                                    "gambar" => $gambar,
                                    "content" => $text
                                ];
                            }
                        }
                    });
                }
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$paginatedUrl}", ['error' => $e->getMessage()]);
            }
        }

        return $this->printAndDownload($results);
    }

    // Sesuai tahun
    public function surabayatribunnewsScrape(Request $request)
    {
        set_time_limit(0);

        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = $request->url; // Menghapus trailing slash jika ada
        $loop = $request->loop; // Ambil jumlah halaman dari request
        $results = [];

        $classItem = ".ptb15";      // Class untuk item artikel
        $classContent = ".txt-article"; // Class untuk konten artikel

        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = $urls . $page;
            try {
                $response = Http::withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                    'Referer' => 'https://www.google.com/',
                ])->get($paginatedUrl);

                $body = $response->body();
                $crawler = new Crawler($body);
                // Iterasi setiap item artikel
                $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent, $paginatedUrl) {
                    $title = trim($node->filter("h3")->text());
                    // Terapkan filter judul
                    if ($this->filterTitle($title)) {
                        // Ambil link dan gambar
                        $link = $node->filter('a')->attr('href');
                        $gambar = $node->filter('img')->attr('src');

                        $responseLinkNode = Http::get($link);

                        if ($responseLinkNode->successful()) {
                            $crawlerSec = new Crawler($responseLinkNode->body());
                            $text = "";

                            // Ambil konten artikel jika ada
                            if ($crawlerSec->filter($classContent)->count() > 0) {
                                $text = $crawlerSec->filter($classContent)->text();
                            }

                            $text = strip_tags($text);
                            $text = trim(preg_replace('/\s+/', ' ', $text));

                            // Simpan data ke hasil
                            $results[] = [
                                "title" => $title,
                                "link" => $link,
                                "gambar" => $gambar,
                                "content" => $text,
                            ];
                        }
                    }
                });
            } catch (Exception $e) {
                Log::error("Error fetching URL: {$paginatedUrl}", ['error' => $e->getMessage()]);
            }
        }

        return $this->printAndDownload($results);
    }

    public function sportsindonewsScrape(Request $request)
    {
        set_time_limit(0);

        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = rtrim($request->url, '/'); // Menghapus trailing slash jika ada
        $loop = $request->loop; // Ambil jumlah halaman dari request
        $results = [];

        $classItem = ".terkini .mb24";      // Class untuk item artikel
        $classContent = ".detail-desc"; // Class untuk konten artikel

        $offset = 0;
        $articlesPerPage = 20; // Berdasarkan analisis URL

        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = $urls . ($offset > 0 ? "/$articlesPerPage" : "");
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Referer' => 'https://www.google.com/',
            ])->get($paginatedUrl);

            $body = $response->body();
            $crawler = new Crawler($body);
            // Iterasi setiap item artikel
            $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent) {
                $title = $node->filter(".desc-kanal")->text();
                // Terapkan filter judul
                if ($this->filterTitle($title)) {
                    // Ambil link dan gambar
                    $link = $node->filter('a')->attr('href');
                    $gambar = $node->filter('img')->attr('src');

                    $responseLinkNode = Http::get($link);

                    if ($responseLinkNode->successful()) {
                        $crawlerSec = new Crawler($responseLinkNode->body());
                        $text = "";

                        // Ambil konten artikel jika ada
                        if ($crawlerSec->filter($classContent)->count() > 0) {
                            $text = $crawlerSec->filter($classContent)->text();
                        }

                        $text = strip_tags($text);
                        $text = trim(preg_replace('/\s+/', ' ', $text));

                        // Simpan data ke hasil
                        $results[] = [
                            "title" => $title,
                            "link" => $link,
                            "gambar" => $gambar,
                            "content" => $text,
                        ];
                    }
                }
            });

            $offset += $articlesPerPage;
        }

        return $this->printAndDownload($results);
    }

    public function postkotaScrape(Request $request)
    {
        set_time_limit(0);

        // Mendapatkan input URL, class container, dan jumlah loop (jumlah halaman)
        $urls = $request->url;
        $loop = $request->loop;
        $results = [];

        $classItem = ".recent-item";       // Class untuk item artikel
        $classContent = ".content__article"; // Class untuk konten artikel
        $paginationSelector = "nav.pagination a"; // Selector untuk pagination dalam artikel


        for ($page = 1; $page <= $loop; $page++) {
            $paginatedUrl = $urls . $page;
            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Referer' => 'https://www.google.com/',
            ])->get($paginatedUrl);

            $body = $response->body();
            $crawler = new Crawler($body);
            // Iterasi setiap item artikel
            $crawler->filter($classItem)->each(function ($node) use (&$results, $classContent, $paginationSelector) {
                $title = $node->filter(".desc-kanal")->text();

                // Terapkan filter judul
                if ($this->filterTitle($title)) {
                    $link = $node->filter('a')->attr('href');
                    $gambar = $node->filter('img')->attr('src');

                    $responseLinkNode = Http::get($link);

                    if ($responseLinkNode->successful()) {
                        $crawlerSec = new Crawler($responseLinkNode->body());
                        $text = "";

                        // Ambil konten dari halaman utama artikel
                        if ($crawlerSec->filter($classContent)->count() > 0) {
                            $text .= $crawlerSec->filter($classContent)->text();
                        }

                        // Cek navigasi untuk halaman tambahan dalam artikel
                        $crawlerSec->filter($paginationSelector)->each(function ($paginationNode) use (&$text, $classContent) {
                            $nextPageUrl = $paginationNode->attr('href');
                            $responseNextPage = Http::get($nextPageUrl);

                            if ($responseNextPage->successful()) {
                                $nextPageCrawler = new Crawler($responseNextPage->body());

                                // Tambahkan konten dari halaman tambahan
                                if ($nextPageCrawler->filter($classContent)->count() > 0) {
                                    $text .= "\n" . $nextPageCrawler->filter($classContent)->text();
                                }
                            }
                        });

                        $text = strip_tags($text);
                        $text = trim(preg_replace('/\s+/', ' ', $text));

                        // Simpan data ke hasil
                        $results[] = [
                            "title" => $title,
                            "link" => $link,
                            "gambar" => $gambar,
                            "content" => $text,
                        ];
                    }
                }
            });
        }

        return $this->printAndDownload($results);
    }
}
