<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class VerifikatorController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */


    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(auth()->user()->can('verifikator_risiko')){
            return view('verifikator');
        }
        return abort(403);
    }
}
