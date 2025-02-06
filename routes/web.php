<?php

use App\Http\Controllers\ScrapperController;
use Illuminate\Support\Facades\Route;
use App\Services\KompasScrape;
use App\Services\RepublikaScrape;

Route::controller(ScrapperController::class)->group(function () {
    // view
    Route::get("/", "index");
    Route::post("/print", "print")->name("print");
    Route::get("/scrape-dynamic", "scrapeDynamicIndex")->name("scrape-dynamic-index");


    // kompas
    Route::controller(KompasScrape::class)->group(function () {
        Route::get("/kompas", "kompas")->name("kompas");
        Route::post("/kompasScrape", "kompasScrape")->name("KompasScrape");
    });

    // Republika
    Route::get("/republika", "republika")->name("republika");
    Route::post("/republikaScrape", "republikaScrape")->name("republikaScrape");

    // Okezone
    Route::get("/okezone", "okezone")->name("okezone");
    Route::post("/okezoneScrape", "okezoneScrape")->name("okezoneScrape");

    // Kontan
    Route::get("/kontan", "kontan")->name("kontan");
    Route::post("/kontanScrape", "kontanScrape")->name("kontanScrape");

    // Bisnis
    Route::get("/bisnis", "bisnis")->name("bisnis");
    Route::post("/bisnisScrape", "bisnisScrape")->name("bisnisScrape");

    // pikiranrakyat
    Route::get("/pikiranrakyat", "pikiranrakyat")->name("pikiranrakyat");
    Route::post("/pikiranrakyatScrape", "pikiranrakyatScrape")->name("pikiranrakyatScrape");

    // mediaindo
    Route::get("/mediaindo", "mediaindo")->name("mediaindo");
    Route::post("/mediaindoScrape", "mediaindoScrape")->name("mediaindoScrape");

    // jawa
    Route::get("/jawa", "jawa")->name("jawa");
    Route::post("/jawaScrape", "jawaScrape")->name("jawaScrape");

    // detiksport
    Route::get("/detiksport", "detiksport")->name("detiksport");
    Route::post("/detiksportScrape", "detiksportScrape")->name("detiksportScrape");

    // sindonews
    Route::get("/sindonews", "sindonews")->name("sindonews");
    Route::post("/sindonewsScrape", "sindonewsScrape")->name("sindonewsScrape");

    // boalsport
    Route::get("/boalsport", "boalsport")->name("boalsport");
    Route::post("/boalsportScrape", "boalsportScrape")->name("boalsportScrape");

    // moneykompas
    Route::get("/moneykompas", "moneykompas")->name("moneykompas");
    Route::post("/moneykompasScrape", "moneykompasScrape")->name("moneykompasScrape");

    // rmid
    Route::get("/rmid", "rmid")->name("rmid");
    Route::post("/rmidScrape", "rmidScrape")->name("rmidScrape");

    // thejakartapost
    Route::get("/thejakartapost", "thejakartapost")->name("thejakartapost");
    Route::post("/thejakartapostScrape", "thejakartapostScrape")->name("thejakartapostScrape");

    // surabayatribunnews
    Route::get("/surabayatribunnews", "surabayatribunnews")->name("surabayatribunnews");
    Route::post("/surabayatribunnewsScrape", "surabayatribunnewsScrape")->name("surabayatribunnewsScrape");

    // sportsindonews
    Route::get("/sportsindonews", "sportsindonews")->name("sportsindonews");
    Route::post("/sportsindonewsScrape", "sportsindonewsScrape")->name("sportsindonewsScrape");

    // postkota
    Route::get("/postkota", "postkota")->name("postkota");
    Route::post("/postkotaScrape", "postkotaScrape")->name("postkotaScrape");

    // investor
    Route::get("/investor", "investor")->name("investor");
    Route::post("/investorScrape", "investorScrape")->name("investorScrape");

    // skorid
    Route::get("/skorid", "skorid")->name("skorid");
    Route::post("/skoridScrape", "skoridScrape")->name("skoridScrape");

    // gayotribunnews
    Route::get("/gayotribunnews", "gayotribunnews")->name("gayotribunnews");
    Route::post("/gayotribunnewsScrape", "gayotribunnewsScrape")->name("gayotribunnewsScrape");

    // googlesearch
    Route::get("/googlesearch", "googlesearch")->name("googlesearch");
    Route::post("/googlesearchScrape", "googlesearchScrape")->name("googlesearchScrape");

    // abmm
    Route::get("/abmm", "abmm")->name("abmm");
    Route::post("/abmmScrape", "abmmScrape")->name("abmmScrape");

    // bipi
    Route::get("/bipi", "bipi")->name("bipi");
    Route::post("/bipiScrape", "bipiScrape")->name("bipiScrape");

    // apex
    Route::get("/apex", "apex")->name("apex");
    Route::post("/apexScrape", "apexScrape")->name("apexScrape");

    // dewa
    Route::get("/dewa", "dewa")->name("dewa");
    Route::post("/dewaScrape", "dewaScrape")->name("dewaScrape");

    // doid
    Route::get("/doid", "doid")->name("doid");
    Route::post("/doidScrape", "doidScrape")->name("doidScrape");

    // elsa
    Route::get("/elsa", "elsa")->name("elsa");
    Route::post("/elsaScrape", "elsaScrape")->name("elsaScrape");

    // func
    Route::post("/scrape", "scrape")->name("scrape");
    Route::post("/scrapeDynamic", "scrapeDynamic")->name("scrapeDynamic");
});
Route::get('phpinfo', function () {
    echo $_SERVER["SERVER_SOFTWARE"];
    phpinfo();
});
