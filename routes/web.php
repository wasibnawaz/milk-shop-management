<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MilkController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::view('/milk/add', '/milk/add');
Route::post('/milk/add', [MilkController::class, 'addMilk'])->name('milk.add');
Route::get('/', [MilkController::class, 'viewEarnings'])->name('milk.index');
Route::get('/milk/{id}/edit', [MilkController::class, 'editMilk'])->name('milk.edit');
Route::put('/milk/{id}', [MilkController::class, 'updateMilk'])->name('milk.update');
Route::delete('/milk/{id}', [MilkController::class, 'deleteMilk'])->name('milk.delete');