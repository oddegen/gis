<?php

namespace App\Http\Controllers;

use App\Models\Monument;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MonumentController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return Inertia::render('Dashboard');
    }

    public function show()
    {
        return Monument::orderBy('name')->get('name');
    }
}
