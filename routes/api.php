<?php

use App\Http\Controllers\MonumentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/monuments', [MonumentController::class, 'show'])->name('monuments');
