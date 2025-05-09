<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MasterRisiko;

class MasterRisikoController extends Controller
{
    public function index()
    {
        $masterRisiko = MasterRisiko::get();
        return view('master.master-risiko.index', compact('masterRisiko'));
    }

    public function view($masterRisiko)
    {
        return view('master.master-risiko.edit', compact('masterRisiko'));
    }
}
