<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;

class UsersExport implements FromView
{
    public function __construct(
        protected $data
    ) {}
    public function view(): View
    {
        return view('printHasil', [
            'data' => $this->data
        ]);
    }
}
