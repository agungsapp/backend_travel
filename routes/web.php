<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    // return view('welcome');
    return redirect('/admin/login');
});


Route::get('generate-storage', function () {
    Artisan::call('storage:link');
    return 'Storage linked successfully!';
});
Route::get('migrate-fresh', function () {
    Artisan::call('migrate:fresh --seed');
    return 'Migrate fresh dan seeder berhasil dijalankan!';
});
