<?php

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

// Route::get('/', function () {
//     return view('welcome');
// });

Auth::routes();

Route::get('/', 'AuthController@index')->name('loginT');

Route::get('/page_not_found', 'AuthController@pageNotFound')->name('page_not_found');
Route::get('/access_denied', 'AuthController@accessDenied')->name('access_denied');
Route::get('/under_construction', 'AuthController@underConstruction')->name('under_construction');

// Not use login route
Route::get('/login', 'AuthController@index')->name('login');

Route::post('post-login', 'AuthController@postLogin');
// Route::get('registration', 'AuthController@registration');
// Route::post('post-registration', 'AuthController@postRegistration');
// Route::get('dashboard', 'AuthController@dashboard')->name('Dashboard');
Route::get('logout', 'AuthController@logout');

Route::get('modules', 'AuthController@moduleDashboard')->name('Modules');
Route::get('/modules/ajaxModuleID', 'AuthController@ajaxSetModuleID')->name('ajaxModuleID');


// ****************** Ajax Route
include 'ajax.php';

//************************ */ General Configuration Routing Group

include 'gnl.php';

// ***************************POS Routing Group
include 'pos.php';

// *************HR & Payroll Routing Group
include 'hr.php';

// Accounting Routes
include 'acc.php';

// Microfinance Routes
include 'mfn.php';

// Billing System Routes
include 'bill.php';


// Inventory Routes
include 'inv.php';


// Fixed Asset Management Routing Group
Route::namespace('FAM')->group(function () {
    // Controllers Within The "App\Http\Controllers\Admin" Namespace

    Route::get('/fam', 'DashboardController@index');
});

// Inventory Routing Group
Route::namespace('INV')->group(function () {
    // Controllers Within The "App\Http\Controllers\Admin" Namespace

    Route::get('/inv', 'DashboardController@index');
});

// Procument Routing Group
Route::namespace('PROC')->group(function () {
    // Controllers Within The "App\Http\Controllers\Admin" Namespace

    Route::get('/proc', 'DashboardController@index');
});
