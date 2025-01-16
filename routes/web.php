<?php

use App\Http\Controllers\ScrapperController;
use Illuminate\Support\Facades\Route;

Route::controller(ScrapperController::class)->group(function () {
    // view
    Route::get("/", "index");
    Route::post("/print", "print")->name("print");
    Route::get("/scrape-dynamic", "scrapeDynamicIndex")->name("scrape-dynamic-index");

    // kompas
    Route::get("/kompas", "kompas")->name("kompas");
    Route::post("/kompasScrape", "KompasScrape")->name("KompasScrape");

    // Republika
    Route::get("/republika", "republika")->name("republika");
    Route::post("/republikaScrape", "republikaScrape")->name("republikaScrape");

    // Okezone
    Route::get("/okezone", "okezone")->name("okezone");
    Route::post("/okezoneScrape", "okezoneScrape")->name("okezoneScrape");

    // Kontan
    Route::get("/kontan", "kontan")->name("kontan");
    Route::post("/kontanScrape", "kontanScrape")->name("kontanScrape");

    // func
    Route::post("/scrape", "scrape")->name("scrape");
    Route::post("/scrapeDynamic", "scrapeDynamic")->name("scrapeDynamic");
});
