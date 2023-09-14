<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\InvoicesDetailsController;
use App\Http\Controllers\ProductsController;
use App\Http\Controllers\SectionsController;

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
    return view('auth.login');
});


// Route::get('/{page}' , 'AdminController@index');




Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::resource('invoices', InvoicesController::class);

Route::resource('sections', SectionsController::class);

Route::get('/section/{id}', [InvoicesController::class, 'getproducts'])->name('getproducts');

Route::get('/InvoicesDetails/{id}', [InvoicesDetailsController::class, 'edit']);

Route::get('download/{Invoice_number}/{file_name}' , [InvoicesDetailsController::class, 'get_file']);

Route::get('View_file/{Invoice_number}/{file_name}' , [InvoicesDetailsController::class, 'open_file']);

Route::post('delete_file' , [InvoicesDetailsController::class, 'destroy'])->name('delete_file');

Route::resource('products', ProductsController::class);

Route::get('/{page}', [AdminController::class, 'index'])->name('index');
