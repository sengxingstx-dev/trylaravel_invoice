<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CurrencyController;
use App\Http\Controllers\QuotationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::group([

    'middleware' => 'api',
    'prefix' => 'auth'

], function ($router) {

    Route::post('login', [AuthController::class, 'login']);
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('refresh', [AuthController::class, 'refresh']);
    Route::post('me', [AuthController::class, 'me']);
});


// api method = get, post, put, delete
Route::group([

    'middleware' => 'auth.jwt',
    'role:superadmin|admin',
    'prefix' => 'admin'

], function ($router) {
    
    Route::get('list-currencies', [CurrencyController::class, 'listCurrencies']);
    Route::post('add-currency', [CurrencyController::class, 'addCurrency'])->name('add.currency');
    Route::put('edit-currency/{id}', [CurrencyController::class, 'editCurrency'])->name('edit.currency');
    Route::delete('delete-currency/{id}', [CurrencyController::class, 'deleteCurrency'])->name('delete.currency');
    
    Route::get('list-companies', [CompanyController::class, 'listCompanies']);
    Route::post('add-company', [CompanyController::class, 'addCompany'])->name('add.company');
    Route::post('edit-company/{id}', [CompanyController::class, 'editCompany'])->name('edit.company');
    Route::delete('delete-company/{id}', [CompanyController::class, 'deleteCompany'])->name('delete.company');
    
    Route::get('list-quotations', [QuotationController::class, 'listQuotations']);
    Route::get('list-quotation-detail/{id}', [QuotationController::class, 'listQuotationDetail']);
    Route::post('add-quotation', [QuotationController::class, 'addQuotation'])->name('add.quotation');
    Route::put('edit-quotation/{id}', [QuotationController::class, 'editQuotation'])->name('edit.quotation');
    Route::put('edit-quotation-detail/{id}', [QuotationController::class, 'editQuotationDetail'])->name('edit.quotation.detail');
    Route::post('add-quotation-detail/{id}', [QuotationController::class, 'addQuotationDetail'])->name('add.quotation.detail');
    Route::delete('delete-quotation/{id}', [QuotationController::class, 'deleteQuotation'])->name('delete.quotation');
    Route::delete('delete-quotation-detail/{id}', [QuotationController::class, 'deleteQuotationDetail'])->name('delete.quotation.detail');

});

