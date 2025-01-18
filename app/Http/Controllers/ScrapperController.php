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

    public function republika()
    {
        return view("republika");
    }

    public function okezone()
    {
        return view("okezone");
    }

    public function kontan()
    {
        return view("kontan");
    }

    public function bisnis()
    {
        return view("bisnis");
    }
}
