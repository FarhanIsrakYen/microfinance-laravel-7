<?php

// , 'permission'
Route::group(['middleware' => ['auth', 'permission'], 'prefix' => 'bill', 'namespace' => 'BILL'], function () {

    // Controllers Within The "App\Http\Controllers\Admin" Namespace

    Route::any('/', 'DashboardController@index')->name('Dashboard');
    // Route::any('/branch_status', 'DashboardController@branchStatus')->name('POSBranchStatus');

    // // ## Supplier  Route C
    Route::group(['prefix' => 'supplier'], function () {
        Route::any('/', 'SupplierController@index')->name('supplierBillDatatable');
        Route::any('add', 'SupplierController@add');
        Route::any('edit/{id}', 'SupplierController@edit');
        Route::get('view/{id}', 'SupplierController@view');
        Route::any('delete/{id}', 'SupplierController@delete');
        Route::any('publish/{id}', 'SupplierController@isActive');
        Route::any('destroy/{id}', 'SupplierController@destroy');
    });

    // // ## Customer  Route C
    Route::group(['prefix' => 'customer'], function () {
        Route::any('/', 'CustomerController@index')->name('customerBillDatatable');
        Route::any('add', 'CustomerController@add');
        Route::any('edit/{id}', 'CustomerController@edit');
        Route::get('view/{id}', 'CustomerController@view');
        Route::any('delete/{id}', 'CustomerController@delete');
        Route::any('publish/{id}', 'CustomerController@isActive');
        Route::any('destroy/{id}', 'CustomerController@destroy');
    });


    // // ## Product Route
    Route::group(['prefix' => 'product'], function () {
        Route::any('/', 'ProductController@index')->name('productBillDatatable');
        Route::any('add', 'ProductController@add');
        Route::any('edit/{id}', 'ProductController@edit');
        Route::get('view/{id}', 'ProductController@view');
        Route::any('delete/{id}', 'ProductController@delete');
        Route::any('publish/{id}', 'ProductController@isActive');
        Route::any('destroy/{id}', 'ProductController@destroy');

        // Route::any('loadModelProduct', 'ProductController@ajaxSelectModelLoad');

        // Route::any('loadModelForProduct', 'ProductController@ajaxLoadModelFP');
        // ->withoutMiddleware(['permission'])
        // Route::any('loadSizeForProduct', 'ProductController@ajaxLoadSizeFP');
        // Route::any('loadColorForProduct', 'ProductController@ajaxLoadColorFP');
    });

    // // ## Transaction

    // // ## Cash Sales Route C
    Route::group(['prefix' => 'sales_cash'], function () {

        Route::any('/', 'SalesController@index')->name('BillCashSalesList');
        Route::any('add', 'SalesController@add');
        Route::any('edit/{id}', 'SalesController@edit');
        Route::any('view/{id}', 'SalesController@view');
        Route::any('delete/{id}', 'SalesController@delete');
        Route::any('invoice/{id}', 'SalesController@invoice');
        // Route::any('publish/{id}', 'SalesController@isActive');

    });

    // Route::any('sales/view/{id}', 'SalesController@view');
    // Route::any('sales/delete/{id}', 'SalesController@delete');
    Route::post('/popUpCustomerData', 'SalesController@popUpCustomerDataInsert');

    // ## Installment Sales Route C
    Route::group(['prefix' => 'sales_installment'], function () {

        Route::any('/', 'SalesController@instIndex')->name('BillInstallmentSalesList');
        Route::any('add', 'SalesController@instAdd');
        Route::any('edit/{id}', 'SalesController@instEdit');
        Route::any('view/{id}', 'SalesController@view');
        Route::any('delete/{id}', 'SalesController@delete');
        Route::any('invoice/{id}', 'SalesController@invoice');
        Route::any('publish/{id}', 'SalesController@isActive');
    });


    // // ## Collection Route
    Route::group(['prefix' => 'collection'], function () {

        Route::any('/', 'CollectionController@index')->name('BillCollectionList');
        Route::any('add', 'CollectionController@add');
        Route::any('edit/{id}', 'CollectionController@edit');
        Route::get('view/{id}', 'CollectionController@view');
        Route::get('delete/{id}', 'CollectionController@delete');
        // Route::get('destroy/{id}', 'CollectionController@destroy');

        ////// collection auto process
        Route::any('/auto_process', 'CollectionController@autoProcess');
    });

    // Bill auto process
    Route::any('/bill_auto_process', 'BillController@autoProcess');


    // // ## Product Settings --------------------------


    // // ## category Route
    Route::group(['prefix' => 'category'], function () {

        Route::any('/', 'PCategoryController@index');
        Route::any('add', 'PCategoryController@add');
        Route::any('edit/{id}', 'PCategoryController@edit');
        Route::get('view/{id}', 'PCategoryController@view');
        Route::any('delete/{id}', 'PCategoryController@delete');
        Route::any('publish/{id}', 'PCategoryController@isActive');
        Route::any('destroy/{id}', 'PCategoryController@destroy');

    });

    // ## Sales Return Route C
    Route::group(['prefix' => 'package'], function () {

        Route::any('/', 'PackageController@index');
        Route::any('add', 'PackageController@add');
        Route::any('edit/{id}', 'PackageController@edit');
        Route::get('view/{id}', 'PackageController@view');
        Route::any('delete/{id}', 'PackageController@delete');
        Route::any('publish/{id}', 'PackageController@isActive');
    });

    // // ## Agreement Route
    Route::group(['prefix' => 'agreement'], function () {
        Route::any('/', 'AgreementController@index')->name('AgreementList');
        Route::any('add', 'AgreementController@add');
        Route::any('edit/{id}', 'AgreementController@edit');
        Route::get('view/{id}', 'AgreementController@view');
        Route::any('delete/{id}', 'AgreementController@delete');

        Route::any('loadProductForAgreement', 'AgreementController@ajaxLoadProductForAgreement');
        Route::any('loadPackageForAgreement', 'AgreementController@ajaxLoadPackageForAgreement');
    });

    // // ##Software Agreement Route
    Route::group(['prefix' => 'agreement_us'], function () {
        Route::any('/', 'SoftwareAgreementController@index')->name('SoftAgreementList');
        Route::any('add', 'SoftwareAgreementController@add');
        Route::any('edit/{id}', 'SoftwareAgreementController@edit');
        Route::get('view/{id}', 'SoftwareAgreementController@view');
        Route::any('delete/{id}', 'SoftwareAgreementController@delete');
    });

    // // ## Cash Sales Route C
    Route::group(['prefix' => 'cash_bill'], function () {

        Route::any('/', 'BillController@index')->name('cashBillList');
        Route::any('add', 'BillController@add');
        Route::any('edit/{id}', 'BillController@edit');
        Route::any('view/{id}', 'BillController@view');
        Route::any('delete/{id}', 'BillController@delete');
        Route::any('invoice/{id}', 'BillController@invoice');
        // Route::any('publish/{id}', 'SalesController@isActive');

    });


});
