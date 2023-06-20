<?php

use App\Http\Controllers\categoryController;
use App\Http\Controllers\contactController;
use App\Http\Controllers\placeController;
use App\Http\Controllers\reviewController;
use App\Http\Controllers\searchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Route::get('/search', [searchController::class, 'autoComplete'])->name('auto-complete');
Route::post('/search', [searchController::class, 'show'])->name('search');

Route::get('/{category:slug}', [categoryController::class, 'show'])->name('category.show');

Route::resource('/report', contactController::class, ['only' => ['create', 'store']]);

Route::get('/', [placeController::class, 'index'])->name('home');

Route::get('/{place}/{slug}', [placeController::class, 'show'])->name('place.show');

Route::post('review', [reviewController::class, 'store'])->name('review.store');


Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified'
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});
