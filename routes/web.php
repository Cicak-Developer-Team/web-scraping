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

    // itmg
    Route::get("/itmg", "itmg")->name("itmg");
    Route::post("/itmgScrape", "itmgScrape")->name("itmgScrape");

    // myoh
    Route::get("/myoh", "myoh")->name("myoh");
    Route::post("/myohScrape", "myohScrape")->name("myohScrape");

    // dssa
    Route::get("/dssa", "dssa")->name("dssa");
    Route::post("/dssaScrape", "dssaScrape")->name("dssaScrape");

    // pgas
    Route::get("/pgas", "pgas")->name("pgas");
    Route::post("/pgasScrape", "pgasScrape")->name("pgasScrape");

    // ptba
    Route::get("/ptba", "ptba")->name("ptba");
    Route::post("/ptbaScrape", "ptbaScrape")->name("ptbaScrape");

    // raja
    Route::get("/raja", "raja")->name("raja");
    Route::post("/rajaScrape", "rajaScrape")->name("rajaScrape");

    // smmt
    Route::get("/smmt", "smmt")->name("smmt");
    Route::post("/smmtScrape", "smmtScrape")->name("smmtScrape");

    // smru
    Route::get("/smru", "smru")->name("smru");
    Route::post("/smruScrape", "smruScrape")->name("smruScrape");

    // toba
    Route::get("/toba", "toba")->name("toba");
    Route::post("/tobaScrape", "tobaScrape")->name("tobaScrape");

    // pssi
    Route::get("/pssi", "pssi")->name("pssi");
    Route::post("/pssiScrape", "pssiScrape")->name("pssiScrape");

    // dwgl
    Route::get("/dwgl", "dwgl")->name("dwgl");
    Route::post("/dwglScrape", "dwglScrape")->name("dwglScrape");

    // tcpi
    Route::get("/tcpi", "tcpi")->name("tcpi");
    Route::post("/tcpiScrape", "tcpiScrape")->name("tcpiScrape");

    // sure
    Route::get("/sure", "sure")->name("sure");
    Route::post("/sureScrape", "sureScrape")->name("sureScrape");

    // tebe
    Route::get("/tebe", "tebe")->name("tebe");
    Route::post("/tebeScrape", "tebeScrape")->name("tebeScrape");

    // func
    Route::post("/scrape", "scrape")->name("scrape");
    Route::post("/scrapeDynamic", "scrapeDynamic")->name("scrapeDynamic");
});
Route::get('phpinfo', function () {
    echo $_SERVER["SERVER_SOFTWARE"];
    phpinfo();
});
