<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\InvoiceAttachmentsController;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\InvoiceAchiveController;
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

Route::resource('InvoiceAttachments', InvoiceAttachmentsController::class);


Route::get('/section/{id}', [InvoicesController::class, 'getproducts'])->name('getproducts');

Route::get('/InvoicesDetails/{id}', [InvoicesDetailsController::class, 'edit']);

Route::get('download/{Invoice_number}/{file_name}' , [InvoicesDetailsController::class, 'get_file']);

Route::get('View_file/{Invoice_number}/{file_name}' , [InvoicesDetailsController::class, 'open_file']);

Route::post('delete_file' , [InvoicesDetailsController::class, 'destroy'])->name('delete_file');

Route::get('/edit_invoice/{id}' , [InvoicesController::class , 'edit']);

Route::get('/Status_show/{id}' , [InvoicesController::class , 'show'])->name('Status_show');

Route::post('/Status_Update/{id}' , [InvoicesController::class , 'Status_Update'])->name('Status_Update');

Route::resource('Archive', InvoiceAchiveController::class);


Route::resource('products', ProductsController::class);

Route::get('Invoices_Paid' , [InvoicesController::class , 'Invoice_Paid']);

Route::get('Invoices_UnPaid' , [InvoicesController::class , 'Invoice_UnPaid']);

Route::get('Invoices_Partial' , [InvoicesController::class , 'Invoice_Partial']);

Route::get('Print_invoice/{id}' , [InvoicesController::class , 'Print_invoice']);


Route::get('/{page}', [AdminController::class, 'index'])->name('index');


