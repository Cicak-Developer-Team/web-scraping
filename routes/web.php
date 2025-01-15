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

    // func
    Route::post("/scrape", "scrape")->name("scrape");
    Route::post("/scrapeDynamic", "scrapeDynamic")->name("scrapeDynamic");
});
