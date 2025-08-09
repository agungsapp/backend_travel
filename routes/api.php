<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\WisataController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/', function () {
    return file_get_contents(public_path('fe/index.html'));
});


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/wisata/{id}/favorit', [WisataController::class, 'addWisataToFavorites']);
    Route::get('/wisata/{id}', [WisataController::class, 'getWisataById']);
});

// Route untuk Kategori dan Wisata
Route::get('/kategori', [WisataController::class, 'getKategori']);
Route::get('/wisata', [WisataController::class, 'getWisata']);
Route::get('/top-wisata', [WisataController::class, 'getTopWisata']);
Route::get('/kategori/{id}/wisata', [WisataController::class, 'getWisataByKategori']);
