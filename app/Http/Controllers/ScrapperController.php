<?php

namespace App\Http\Controllers;

use App\Services\CrawlerService;

class ScrapperController extends CrawlerService
{

    public function index()
    {
        return view("index");
    }


    public function kompas()
    {
        return view('kompas');
    }

    public function scrapeDynamicIndex()
    {
        return view("scrapeweb2");
    }
}
