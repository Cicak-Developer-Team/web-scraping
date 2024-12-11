<?php

use App\Http\Controllers\ScrapperController;
use Illuminate\Support\Facades\Route;

Route::controller(ScrapperController::class)->group(function () {
    // view
    Route::get("/", "index");
    Route::get("/scrape", "scrapeIndex")->name("scrape-index");
    Route::get("/scrape-dynamic", "scrapeDynamicIndex")->name("scrape-dynamic-index");

    // func
    Route::post("/scrape", "scrape")->name("scrape");
    Route::post("/scrapeDynamic", "scrapeDynamic")->name("scrapeDynamic");
});
