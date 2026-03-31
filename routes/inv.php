<?php

// , 'permission'
Route::group(['middleware' => ['auth', 'permission'], 'prefix' => 'inv', 'namespace' => 'INV'], function () {

    // Controllers Within The "App\Http\Controllers\Admin" Namespace

    Route::any('/', 'DashboardController@index')->name('INVDashboard');
    Route::any('/branch_status', 'DashboardController@branchStatus')->name('INVBranchStatus');

    // // ## Supplier  Route C
    Route::group(['prefix' => 'supplier'], function () {
        Route::any('/', 'SupplierController@index')->name('INVsupplierDatatable');
        Route::any('add', 'SupplierController@add');
        Route::any('edit/{id}', 'SupplierController@edit');
        Route::get('view/{id}', 'SupplierController@view');
        Route::any('delete/{id}', 'SupplierController@delete');
        Route::any('publish/{id}', 'SupplierController@isActive');
        Route::any('destroy/{id}', 'SupplierController@destroy');
    });

    // // ## Product OB Route C
    Route::group(['prefix' => 'product_ob'], function () {

        Route::any('/', 'POpeningBalanceController@index');
        Route::any('add', 'POpeningBalanceController@add');
        Route::any('edit/{id}', 'POpeningBalanceController@edit');
        Route::get('view/{id}', 'POpeningBalanceController@view');
        Route::any('delete/{id}', 'POpeningBalanceController@delete');
        Route::any('publish/{id}', 'POpeningBalanceController@isActive');
        Route::any('destroy/{id}', 'POpeningBalanceController@destroy');
    });

    // // ## Day End Route C
    Route::group(['prefix' => 'day_end'], function () {
        Route::any('/', 'DayEndController@index')->name('INVdayendDatatable');
        Route::post('execute', 'DayEndController@end');

        Route::get('/ajaxDeleteInvDayEnd', 'DayEndController@ajaxDeleteInvDayEnd');
    });

    // // ## Month End Route C
    Route::group(['prefix' => 'month_end'], function () {
        Route::any('/', 'MonthEndController@index');
        Route::post('execute', 'MonthEndController@executeMonthEnd');
        Route::get('checkDayEndData', 'MonthEndController@checkDayEndData');
        Route::get('delete', 'MonthEndController@isDelete');
    });

    // // ## Product Settings --------------------------

    // // ## Group Route
    Route::group(['prefix' => 'group'], function () {
        Route::any('/', 'PGroupController@index');
        Route::any('add', 'PGroupController@add');
        Route::any('edit/{id}', 'PGroupController@edit');
        Route::get('view/{id}', 'PGroupController@view');
        Route::any('delete/{id}', 'PGroupController@delete');
        Route::any('publish/{id}', 'PGroupController@isActive');
        Route::any('destroy/{id}', 'PGroupController@destroy');
    });

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

    // // ## Subcategory Route
    Route::group(['prefix' => 'subcategory'], function () {

        Route::any('/', 'PSubCategoryController@index');
        Route::any('add', 'PSubCategoryController@add');
        Route::any('edit/{id}', 'PSubCategoryController@edit');
        Route::get('view/{id}', 'PSubCategoryController@view');
        Route::any('delete/{id}', 'PSubCategoryController@delete');
        Route::any('publish/{id}', 'PSubCategoryController@isActive');
        Route::any('destroy/{id}', 'PSubCategoryController@destroy');
    });

    // // ## Brand Route
    Route::group(['prefix' => 'brand'], function () {
        Route::any('/', 'PBrandController@index');
        Route::any('add', 'PBrandController@add');
        Route::any('edit/{id}', 'PBrandController@edit');
        Route::get('view/{id}', 'PBrandController@view');
        Route::any('delete/{id}', 'PBrandController@delete');
        Route::any('publish/{id}', 'PBrandController@isActive');
        Route::any('destroy/{id}', 'PBrandController@destroy');
    });

    // // ## Model Route
    Route::group(['prefix' => 'model'], function () {
        Route::any('/', 'PModelController@index');
        Route::any('add', 'PModelController@add');
        Route::any('edit/{id}', 'PModelController@edit');
        Route::get('view/{id}', 'PModelController@view');
        Route::any('delete/{id}', 'PModelController@delete');
        Route::any('publish/{id}', 'PModelController@isActive');
        Route::any('destroy/{id}', 'PModelController@destroy');
    });

    // // ## Size Route C
    Route::group(['prefix' => 'size'], function () {
        Route::any('/', 'ProductSizeController@index');
        Route::any('add', 'ProductSizeController@add');
        Route::any('edit/{id}', 'ProductSizeController@edit');
        Route::get('view/{id}', 'ProductSizeController@view');
        Route::any('delete/{id}', 'ProductSizeController@delete');
        Route::any('publish/{id}', 'ProductSizeController@isActive');
        Route::any('destroy/{id}', 'ProductSizeController@destroy');

        Route::any('loadModelSize', 'ProductSizeController@ajaxSelectModelLoad');
    });

    // // ## Color Route C
    Route::group(['prefix' => 'color'], function () {

        Route::any('/', 'ProductColorController@index');
        Route::any('add', 'ProductColorController@add');
        Route::any('edit/{id}', 'ProductColorController@edit');
        Route::get('view/{id}', 'ProductColorController@view');
        Route::any('delete/{id}', 'ProductColorController@delete');
        Route::any('publish/{id}', 'ProductColorController@isActive');
        Route::any('destroy/{id}', 'ProductColorController@destroy');

        Route::any('loadModelColor', 'ProductColorController@ajaxSelectModelLoad');


    });

    // // ##  Product UOM Route C
    Route::group(['prefix' => 'uom'], function () {

        Route::any('/', 'ProductUOMController@index');
        Route::any('add', 'ProductUOMController@add');
        Route::any('edit/{id}', 'ProductUOMController@edit');
        Route::get('view/{id}', 'ProductUOMController@view');
        Route::any('delete/{id}', 'ProductUOMController@delete');
        Route::any('publish/{id}', 'ProductUOMController@isActive');
        Route::any('destroy/{id}', 'ProductUOMController@destroy');

    });

    // // ## Product Route
    Route::group(['prefix' => 'product'], function () {
        Route::any('/', 'ProductController@index')->name('INVproductDatatable');
        Route::any('add', 'ProductController@add');
        Route::any('edit/{id}', 'ProductController@edit');
        Route::get('view/{id}', 'ProductController@view');
        Route::any('delete/{id}', 'ProductController@delete');
        Route::any('publish/{id}', 'ProductController@isActive');
        Route::any('destroy/{id}', 'ProductController@destroy');

        // Route::any('loadModelProduct', 'ProductController@ajaxSelectModelLoad');

        Route::any('loadModelForProduct', 'ProductController@ajaxLoadModelFP');
        // ->withoutMiddleware(['permission'])
        Route::any('loadSizeForProduct', 'ProductController@ajaxLoadSizeFP');
        Route::any('loadColorForProduct', 'ProductController@ajaxLoadColorFP');
    });

    // // ## Transaction

    // // ##Order able Purchase Route
    Route::group(['prefix' => 'purchase_orderable'], function () {
        Route::any('/', 'OrderablePurchaseController@index')->name('INVOrderablePurchaseList');
        Route::any('add', 'OrderablePurchaseController@add');
        Route::any('edit/{id}', 'OrderablePurchaseController@edit');
        Route::get('view/{id}', 'OrderablePurchaseController@view');
        Route::any('delete/{id}', 'OrderablePurchaseController@delete');
        // Route::any('publish/{id}', 'PurchaseController@isActive');
        Route::post('popUpSupplierData', 'OrderablePurchaseController@popUpSupplierDataInsert');
          });
        Route::group(['prefix' => 'purchase'], function () {
        // ##Purchase Route
        Route::any('/', 'PurchaseController@index')->name('INVPurchaseList');
        Route::any('add', 'PurchaseController@add');
        Route::any('edit/{id}', 'PurchaseController@edit');
        Route::get('view/{id}', 'PurchaseController@view');
        Route::any('delete/{id}', 'PurchaseController@delete');
        // Route::any('publish/{id}', 'PurchaseController@isActive');
        Route::post('popUpSupplierData', 'PurchaseController@popUpSupplierDataInsert');
    });

    // // ## Purchases Return Route C
    Route::group(['prefix' => 'purchase_return'], function () {

        Route::any('/', 'PurchaseReturnController@index')->name('INVpurReturnIndexDtable');
        Route::any('add', 'PurchaseReturnController@add');
        Route::any('edit/{id}', 'PurchaseReturnController@edit');
        Route::get('view/{id}', 'PurchaseReturnController@view');
        Route::any('delete/{id}', 'PurchaseReturnController@delete');
        Route::any('publish/{id}', 'PurchaseReturnController@isActive');

    });

    // // ## issue Route
    Route::group(['prefix' => 'issue'], function () {
        Route::any('/', 'IssueController@index')->name('INVissueDatatable');
        Route::any('add', 'IssueController@add');
        Route::any('edit/{id}', 'IssueController@edit');
        Route::get('view/{id}', 'IssueController@view');
        Route::any('delete/{id}', 'IssueController@delete');
        Route::any('publish/{id}', 'IssueController@isActive');
    });

    // // ## Issue Return Route C
    Route::group(['prefix' => 'issue_return'], function () {

        Route::any('/', 'IssueReturnController@index')->name('INVissueReturnDatatable');
        Route::any('add', 'IssueReturnController@add');
        Route::any('edit/{id}', 'IssueReturnController@edit');
        Route::get('view/{id}', 'IssueReturnController@view');
        Route::any('delete/{id}', 'IssueReturnController@delete');
        Route::any('publish/{id}', 'IssueReturnController@isActive');

    });

    // // ## Cash Sales Route C
    Route::group(['prefix' => 'use'], function () {

        Route::any('/', 'UsesController@index')->name('INVUsesList');
        Route::any('add', 'UsesController@add');
        Route::any('edit/{id}', 'UsesController@edit');
        Route::any('view/{id}', 'UsesController@view');
        Route::any('delete/{id}', 'UsesController@delete');
        Route::any('invoice/{id}', 'UsesController@invoice');
        // Route::any('publish/{id}', 'UsesController@isActive');

        Route::any('/ajEmpLoadDeptWise', 'UsesController@ajaxEmployeeLoad');
        Route::any('/ajReqLoadEmpWise', 'UsesController@ajaxRequisitionLoad');
        Route::any('/ajReqLoadDeptWise', 'UsesController@ajaxRequisitionLoadForDept');

    });

    // // ## Sales Return Route C
    Route::group(['prefix' => 'use_return'], function () {

        Route::any('/', 'UseReturnController@index')->name('invUseRList');
        Route::any('add', 'UseReturnController@add');
        Route::any('edit/{id}', 'UseReturnController@edit');
        Route::get('view/{id}', 'UseReturnController@view');
        Route::any('delete/{id}', 'UseReturnController@delete');
        Route::any('publish/{id}', 'UseReturnController@isActive');
    });

    // // ## Transfer Route
    Route::group(['prefix' => 'transfer'], function () {

        Route::any('/', 'TransferController@index')->name('INVtransferDatatable');
        Route::any('add', 'TransferController@add');
        Route::any('edit/{id}', 'TransferController@edit');
        Route::get('view/{id}', 'TransferController@view');
        Route::any('delete/{id}', 'TransferController@delete');
        Route::any('publish/{id}', 'TransferController@isActive');

    });

    // ## Requisition Route
    Route::group(['prefix' => 'requisition'], function () {
        Route::any('/', 'RequisitionController@index');
        Route::any('add', 'RequisitionController@add');
        Route::any('edit/{id}', 'RequisitionController@edit');
        Route::get('view/{id}', 'RequisitionController@view');
        Route::any('delete/{id}', 'RequisitionController@delete');
        Route::any('approve/{id}', 'RequisitionController@isApprove');
    });

    // ## Employee Requisition Route
    Route::group(['prefix' => 'requisition_emp'], function () {
        Route::any('/', 'EmployeeRequisitionController@index');
        Route::any('add', 'EmployeeRequisitionController@add');
        Route::any('edit/{id}', 'EmployeeRequisitionController@edit');
        Route::get('view/{id}', 'EmployeeRequisitionController@view');
        Route::any('delete/{id}', 'EmployeeRequisitionController@delete');
        Route::any('approve/{id}', 'EmployeeRequisitionController@isApprove');
    });

    // ## Product Order Route
    Route::group(['prefix' => 'product_order'], function () {
        Route::any('/', 'OrderController@index');
        Route::any('add', 'OrderController@add');
        Route::any('edit/{id}', 'OrderController@edit');
        Route::get('view/{id}', 'OrderController@view');
        Route::any('delete/{id}', 'OrderController@delete');
        Route::any('approve/{id}', 'OrderController@isApprove');
    });

    // // ## Report Route C
    Route::group(['prefix' => 'report'], function () {

        /* start ---------------------- Stock Reports */
        Route::any('stock_branch', 'ReportController@stockBranch');
        Route::any('stock_ho', 'ReportController@stockHO');
        Route::any('stock_inv_branch', 'ReportController@stockInvBranch');
        Route::any('stock_inv_ho', 'ReportController@stockInvHO');
        /* end ---------------------- Stock Reports */

        /* start ---------------------- Sales Reports */
        Route::any('salesr', 'ReportController@getsale')->name('INVsaleRDatatable');
        Route::any('sales_details', 'ReportController@getsaleDetails');
        Route::any('tsales_summary', 'ReportController@getTSalesSummary')->name('INVTSalesSummaryDatatable');
        Route::any('area_sales', 'ReportController@getAreaWiseSales');
        Route::any('zone_sales', 'ReportController@getZoneSales');

        // Under COnstruction
        Route::any('region_sales', 'ReportController@getRegionSales');
        Route::any('sales_return', 'ReportController@getSalesReturn')->name('INVSalesReturnDataTable');;

        /* end ---------------------- Sales Reports */

        /* start ---------------------- Purchase Reports */
        Route::any('purchase', 'ReportController@getPurchaseAll')->name('INVpurchasereportDatatable');
        Route::any('purchase_return', 'ReportController@getPurchaseReturnAll')->name('INVPurReturnDataTable');
        /* end ---------------------- Purchase Reports */

        /* start ---------------------- Issue Reports */
        Route::any('issue', 'ReportController@getIssueAll')->name('INVissueDataTableReport');
        Route::any('issue_return', 'ReportController@getIssueReturnAll')->name('INVissueReturnDataTableReport');
        /* end ---------------------- Issue Reports */

        /* start ---------------------- Transfer Reports */
        Route::any('transferin', 'ReportController@getTransferIn')->name('INVtransferinDataTable');
        Route::any('transferout', 'ReportController@getTransferOut')->name('INVtransferoutDataTable');
        /* end ---------------------- Transfer Reports */

        Route::any('requisition', 'ReportController@getRequisition');
        Route::any('requisition_emp', 'ReportController@getRequisitionEmployee');
        Route::any('product_order', 'ReportController@getProdOrder');
        Route::any('branch_customer', 'ReportController@getbranchcustomer')->name('INVbranchcustomerDatatable');

        Route::any('branchr', 'ReportController@getbranch')->name('INVbranchRDatatable');
        Route::any('arear', 'ReportController@getarea')->name('INVareaRDatatable');
        Route::any('zoner', 'ReportController@getzone')->name('INVzoneRDatatable');

        Route::any('use', 'ReportController@getUse')->name('INVuseDataTableReport');
        Route::any('use_return', 'ReportController@getUseReturn')->name('INVuseRetDataTableReport');
    });
});
