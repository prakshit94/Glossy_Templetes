<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class ScaffoldController extends Controller
{
    /**
     * Display a dynamically scaffolded module view.
     *
     * @param string $uri The module URI key
     * @param string $title The module title
     * @param string $icon The module icon
     */
    public function show(string $uri, string $title, string $icon)
    {
        return view("{$uri}.index", [
            'moduleKey' => $uri,
            'moduleTitle' => $title,
            'moduleIcon' => $icon,
        ]);
    }
}
