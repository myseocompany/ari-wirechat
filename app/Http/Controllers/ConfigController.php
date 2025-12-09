<?php

namespace App\Http\Controllers;

use App\Models\Config;

class ConfigController extends Controller
{
    public function index()
    {
        $configs = Config::orderBy('key')->paginate(20);

        return view('configs.index', compact('configs'));
    }
}
