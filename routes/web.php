<?php

use Illuminate\Support\Facades\Route;

// SPA - все маршруты обрабатываются Vue Router
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
