<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Repository;

class PageController extends Controller
{
    public function home(){

        $aData = [
            'aRepositories' => Repository::latest()->get(),
        ];

        return view('welcome', $aData);
    }
}
