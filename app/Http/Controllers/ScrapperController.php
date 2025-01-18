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

    public function pikiranrakyat()
    {
        return view("pikiranrakyat");
    }

    public function mediaindo()
    {
        return view("mediaindo");
    }

    public function jawa()
    {
        return view("jawa");
    }

    public function detiksport()
    {
        return view("detiksport");
    }

    public function sindonews()
    {
        return view("sindonews");
    }
}
