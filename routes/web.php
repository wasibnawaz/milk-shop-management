<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DealerController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\SaleController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Authentication is added in Milestone 2, at which point these move inside
| a Route::middleware('auth') group.
|
*/

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::resource('sales', SaleController::class)->except('show');
Route::resource('products', ProductController::class)->except('show');
Route::resource('dealers', DealerController::class)->except('show');
