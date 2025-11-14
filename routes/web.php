<?php

use Illuminate\Support\Facades\Route;

// CSRF cookie для SPA
Route::get('/sanctum/csrf-cookie', function () {
    return response()->json(['message' => 'CSRF cookie set']);
});

// SPA - все маршруты обрабатываются Vue Router
Route::get('/{any}', function () {
    return view('app');
})->where('any', '.*');
