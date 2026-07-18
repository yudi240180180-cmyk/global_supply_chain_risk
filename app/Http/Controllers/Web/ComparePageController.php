<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Country;

class ComparePageController extends Controller
{
    public function index()
    {
        $countries = Country::orderBy('name')
            ->select('id', 'name', 'code', 'flag_url', 'region')
            ->get();

        return view('compare.index', compact('countries'));
    }
}
