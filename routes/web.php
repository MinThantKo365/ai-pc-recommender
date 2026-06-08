<?php

use App\Http\Controllers\RecommendationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [RecommendationController::class, 'index'])->name('home');
Route::get('/recommend', [RecommendationController::class, 'show'])->name('recommend.show');
Route::post('/recommend', [RecommendationController::class, 'store'])->name('recommend');
