<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AlbumController;
use App\Http\Controllers\BandcampController;

// Main application route
Route::get('/', [AlbumController::class, 'index'])->name('home');

// Album routes
Route::resource('albums', AlbumController::class);
Route::put('albums/{album}/rating', [AlbumController::class, 'updateRating'])->name('albums.rating');
Route::get('api/albums/stats', [AlbumController::class, 'stats'])->name('albums.stats');

// Bandcamp integration routes
Route::prefix('api/bandcamp')->group(function () {
    Route::post('refresh', [BandcampController::class, 'refresh'])->name('bandcamp.refresh');
    Route::post('fan-id', [BandcampController::class, 'getFanId'])->name('bandcamp.fan-id');
    Route::post('tracks', [BandcampController::class, 'parseTracks'])->name('bandcamp.tracks');
    Route::get('status', [BandcampController::class, 'refreshStatus'])->name('bandcamp.status');
    Route::get('status/{taskId}', [BandcampController::class, 'refreshStatus'])->name('bandcamp.status.task');
});
