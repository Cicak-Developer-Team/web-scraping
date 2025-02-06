<?php

namespace App\Http\Controllers;

use App\Services\CrawlerService;

class ScrapperController extends CrawlerService
{

    public function index()
    {
        return view("index");
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

    public function boalsport()
    {
        return view("boalsport");
    }

    public function moneykompas()
    {
        return view("moneykompas");
    }

    public function rmid()
    {
        return view("rmid");
    }

    public function thejakartapost()
    {
        return view("thejakartapost");
    }

    public function surabayatribunnews()
    {
        return view("surabayatribunnews");
    }

    public function sportsindonews()
    {
        return view("sportsindonews");
    }

    public function postkota()
    {
        return view("postkota");
    }

    public function investor()
    {
        return view("investor");
    }

    public function skorid()
    {
        return view("skorid");
    }

    public function gayotribunnews()
    {
        return view("gayotribunnews");
    }

    public function googlesearch()
    {
        return view("googlesearch");
    }

    public function abmm()
    {
        return view("abmm");
    }

    public function bipi()
    {
        return view("bipi");
    }

    public function apex()
    {
        return view("apex");
    }

    public function dewa()
    {
        return view("dewa");
    }

    public function doid()
    {
        return view("doid");
    }

    public function elsa()
    {
        return view("elsa");
    }
}
