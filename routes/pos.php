<?php

// , 'permission'
Route::group(['middleware' => ['auth', 'permission'], 'prefix' => 'pos', 'namespace' => 'POS'], function () {

    // Controllers Within The "App\Http\Controllers\Admin" Namespace

    Route::any('/', 'DashboardController@index')->name('Dashboard');
    Route::any('/branch_status', 'DashboardController@branchStatus')->name('POSBranchStatus');

    // // ## Supplier  Route C
    Route::group(['prefix' => 'supplier'], function () {
        Route::any('/', 'SupplierController@index')->name('supplierDatatable');
        Route::any('add', 'SupplierController@add');
        Route::any('edit/{id}', 'SupplierController@edit');
        Route::get('view/{id}', 'SupplierController@view');
        Route::any('delete/{id}', 'SupplierController@delete');
        Route::any('publish/{id}', 'SupplierController@isActive');
        Route::any('destroy/{id}', 'SupplierController@destroy');
    });

    // // ## Customer  Route C
    Route::group(['prefix' => 'customer'], function () {
        Route::any('/', 'CustomerController@index')->name('customerDatatable');
        Route::any('add', 'CustomerController@add');
        Route::any('edit/{id}', 'CustomerController@edit');
        Route::get('view/{id}', 'CustomerController@view');
        Route::any('delete/{id}', 'CustomerController@delete');
        Route::any('publish/{id}', 'CustomerController@isActive');
        Route::any('destroy/{id}', 'CustomerController@destroy');
    });

    // // ## guarantor  Route
    Route::group(['prefix' => 'guarantor'], function () {

        Route::any('/', 'GuarantorController@index')->name('GuarantorDatatable');
        Route::any('add', 'GuarantorController@add');
        Route::any('edit/{id}', 'GuarantorController@edit');
        Route::get('view/{id}', 'GuarantorController@view');
        Route::any('delete/{id}', 'GuarantorController@delete');
        Route::any('publish/{id}', 'GuarantorController@isActive');
        Route::get('destroy/{id}', 'GuarantorController@destroy');

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

    // // ## Customer OB Route C
    Route::group(['prefix' => 'customer_ob'], function () {
        Route::any('/', 'PCustomerOBController@index');
        Route::any('add', 'PCustomerOBController@add');
        Route::any('edit/{id}', 'PCustomerOBController@edit');
        Route::get('view/{id}', 'PCustomerOBController@view');
        Route::any('delete/{id}', 'PCustomerOBController@delete');
        Route::any('destroy/{id}', 'PCustomerOBController@destroy');
        // Route::any('publish/{id}', 'PCustomerOBController@isActive');
    });

    // // ## Day End Route C
    Route::group(['prefix' => 'day_end'], function () {
        Route::any('/', 'DayEndController@index')->name('dayendDatatable');
        Route::post('execute', 'DayEndController@end');

        Route::any('auto_voucher_script', 'DayEndController@scriptAutoVoucher');

        Route::get('/ajaxDeleteDayEnd', 'DayEndController@ajaxDeleteDayEnd');
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

    // // ## Installment Packages Route
    Route::group(['prefix' => 'pinstallpackage'], function () {

        Route::any('/', 'PInstPackageController@index');
        Route::any('add', 'PInstPackageController@add');
        Route::any('edit/{id}', 'PInstPackageController@edit');
        Route::get('view/{id}', 'PInstPackageController@view');
        Route::any('delete/{id}', 'PInstPackageController@delete');
        Route::any('publish/{id}', 'PInstPackageController@isActive');
        Route::any('destroy/{id}', 'PInstPackageController@destroy');

    });

    // // ## Processing Fee Route
    Route::group(['prefix' => 'proFee'], function () {

        Route::any('/', 'ProFeeController@index');
        Route::any('add', 'ProFeeController@add');
        Route::any('edit/{id}', 'ProFeeController@edit');
        Route::any('delete/{id}', 'ProFeeController@delete');
        Route::any('publish/{id}', 'ProFeeController@isActive');
        Route::any('destroy/{id}', 'ProFeeController@destroy');

    });

    // // ## Product Route
    Route::group(['prefix' => 'product'], function () {
        Route::any('/', 'ProductController@index')->name('productDatatable');
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

    // // ## Purchase Route
    Route::group(['prefix' => 'purchase'], function () {
        Route::any('/', 'PurchaseController@index')->name('PurchaseList');
        Route::any('add', 'PurchaseController@add');
        Route::any('edit/{id}', 'PurchaseController@edit');
        Route::get('view/{id}', 'PurchaseController@view');
        Route::any('delete/{id}', 'PurchaseController@delete');
        // Route::any('publish/{id}', 'PurchaseController@isActive');
        Route::post('popUpSupplierData', 'PurchaseController@popUpSupplierDataInsert');
    });

    // // ## Purchases Return Route C
    Route::group(['prefix' => 'purchase_return'], function () {

        Route::any('/', 'PurchaseReturnController@index')->name('purReturnIndexDtable');
        Route::any('add', 'PurchaseReturnController@add');
        Route::any('edit/{id}', 'PurchaseReturnController@edit');
        Route::get('view/{id}', 'PurchaseReturnController@view');
        Route::any('delete/{id}', 'PurchaseReturnController@delete');
        Route::any('publish/{id}', 'PurchaseReturnController@isActive');

    });

    // // ## issue Route
    Route::group(['prefix' => 'issue'], function () {
        Route::any('/', 'IssueController@index')->name('issueDatatable');
        Route::any('add', 'IssueController@add');
        Route::any('edit/{id}', 'IssueController@edit');
        Route::get('view/{id}', 'IssueController@view');
        Route::any('delete/{id}', 'IssueController@delete');
        Route::any('publish/{id}', 'IssueController@isActive');
    });

    // // ## Issue Return Route C
    Route::group(['prefix' => 'issue_return'], function () {

        Route::any('/', 'IssueReturnController@index')->name('issueReturnDatatable');
        Route::any('add', 'IssueReturnController@add');
        Route::any('edit/{id}', 'IssueReturnController@edit');
        Route::get('view/{id}', 'IssueReturnController@view');
        Route::any('delete/{id}', 'IssueReturnController@delete');
        Route::any('publish/{id}', 'IssueReturnController@isActive');

    });

    // // ## Cash Sales Route C
    Route::group(['prefix' => 'sales_cash'], function () {

        Route::any('/', 'SalesController@index')->name('CashSalesList');
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

    // // ## Installment Sales Route C
    Route::group(['prefix' => 'sales_installment'], function () {

        Route::any('/', 'SalesController@instIndex')->name('InstallmentSalesList');
        Route::any('add', 'SalesController@instAdd');
        Route::any('edit/{id}', 'SalesController@instEdit');
        Route::any('view/{id}', 'SalesController@view');
        Route::any('delete/{id}', 'SalesController@delete');
        Route::any('invoice/{id}', 'SalesController@invoice');
        // Route::any('publish/{id}', 'SalesController@isActive');
    });

    // // ## Sales Return Route C
    Route::group(['prefix' => 'sales_return'], function () {

        Route::any('/', 'SaleReturnController@index')->name('CashSalesRList');
        Route::any('add', 'SaleReturnController@add');
        Route::any('edit/{id}', 'SaleReturnController@edit');
        Route::get('view/{id}', 'SaleReturnController@view');
        Route::any('delete/{id}', 'SaleReturnController@delete');
        Route::any('publish/{id}', 'SaleReturnController@isActive');
    });

    // // ## Transfer Route
    Route::group(['prefix' => 'transfer'], function () {

        Route::any('/', 'TransferController@index')->name('transferDatatable');
        Route::any('add', 'TransferController@add');
        Route::any('edit/{id}', 'TransferController@edit');
        Route::get('view/{id}', 'TransferController@view');
        Route::any('delete/{id}', 'TransferController@delete');
        Route::any('publish/{id}', 'TransferController@isActive');

    });

    // // ## Collection Route
    Route::group(['prefix' => 'collection'], function () {

        Route::any('/', 'CollectionController@index')->name('CollectionList');
        Route::any('add', 'CollectionController@add');
        Route::any('edit/{id}', 'CollectionController@edit');
        Route::get('view/{id}', 'CollectionController@view');
        Route::get('delete/{id}', 'CollectionController@delete');
        // Route::get('destroy/{id}', 'CollectionController@destroy');

        // ////// collection auto process
        Route::any('/auto_process', 'CollectionController@autoProcess');

        Route::any('ajaxColCustList', 'CollectionController@ajaxCollCustList');
        Route::any('ajaxColEmpList', 'CollectionController@ajaxCollEmpList');

    });

    // ////// collection auto process
    Route::any('/col_auto_process', 'CollectionController@autoProcess');

    // ## Requisition Route
    Route::group(['prefix' => 'requisition'], function () {
        Route::any('/', 'RequisitionController@index');
        Route::any('add', 'RequisitionController@add');
        Route::any('edit/{id}', 'RequisitionController@edit');
        Route::get('view/{id}', 'RequisitionController@view');
        Route::any('delete/{id}', 'RequisitionController@delete');
        Route::any('approve/{id}', 'RequisitionController@isApprove');
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

    // ## Product deliver Route
    Route::group(['prefix' => 'product_delivery'], function () {
        Route::any('/', 'DeliveryController@index');
        Route::any('add', 'DeliveryController@add');
        // Route::any('edit/{id}', 'DeliveryController@edit');
        Route::get('view/{id}', 'DeliveryController@view');
        // Route::any('delete/{id}', 'DeliveryController@delete');
        // Route::any('approve/{id}', 'DeliveryController@isApprove');
    });

    // // ## Report Route C
    Route::group(['prefix' => 'report'], function () {

        /* start ---------------------- Collection Reports */
        Route::any('collections', 'ReportController@getCollectionAll')->name('allcollectionDataTable');
        Route::any('collection_with_profit', 'ReportController@getCollectionWithProfit')->name('collectionProfitDatatable');
        Route::any('customer_due', 'ReportController@customerDue');
        Route::any('collection_sheet', 'ReportController@getCollectionSheet')->name('collectionsheetDatatable');
        /* end ---------------------- Collection Reports */

        /* start ---------------------- Stock Reports */
        Route::any('stock_branch', 'ReportController@stockBranch');
        Route::any('stock_ho', 'ReportController@stockHO');
        Route::any('stock_inv_branch', 'ReportController@stockInvBranch');
        Route::any('stock_inv_ho', 'ReportController@stockInvHO');
        /* end ---------------------- Stock Reports */

        /* start ---------------------- Due Reports */
        Route::any('current_due', 'ReportController@getCurrentDue');
        Route::any('over_due', 'ReportController@getOverDue');
        Route::any('current_n_over_due', 'ReportController@getCurrentnOverDue');
        /* end ---------------------- Due Reports */

        /* start ---------------------- Sales Reports */
        Route::any('salesr', 'ReportController@getsale')->name('saleRDatatable');
        Route::any('sales_details', 'ReportController@getsaleDetails');
        Route::any('tsales_summary', 'ReportController@getTSalesSummary')->name('TSalesSummaryDatatable');
        Route::any('area_sales', 'ReportController@getAreaWiseSales');
        Route::any('zone_sales', 'ReportController@getZoneSales');

        // Under COnstruction
        Route::any('region_sales', 'ReportController@getRegionSales');
        Route::any('sales_return', 'ReportController@getSalesReturn')->name('SalesReturnDataTable');
        Route::any('customer_details', 'ReportController@getCustomerDetails')->name('CustDetailsDataTable');

        /* end ---------------------- Sales Reports */

        /* start ---------------------- Purchase Reports */
        Route::any('purchase', 'ReportController@getPurchaseAll')->name('purchasereportDatatable');
        Route::any('purchase_return', 'ReportController@getPurchaseReturnAll')->name('PurReturnDataTable');
        /* end ---------------------- Purchase Reports */

        /* start ---------------------- Issue Reports */
        Route::any('issue', 'ReportController@getIssueAll')->name('issueDataTable');
        Route::any('issue_return', 'ReportController@getIssueReturnAll')->name('issueReturnDataTable');
        /* end ---------------------- Issue Reports */

        /* start ---------------------- Transfer Reports */
        Route::any('transferin', 'ReportController@getTransferIn')->name('transferinDataTable');
        Route::any('transferout', 'ReportController@getTransferOut')->name('transferoutDataTable');
        /* end ---------------------- Transfer Reports */

        Route::any('requisition', 'ReportController@getRequisition');
        Route::any('product_order', 'ReportController@getProdOrder');
        Route::any('branch_customer', 'ReportController@getbranchcustomer')->name('branchcustomerDatatable');



        // Under Construction
        Route::any('mis_report-1', 'ReportController@getMISReportFirst');
        Route::any('mis_report-2', 'ReportController@getMISReportSecond');

        Route::any('incentive', 'ReportController@getincentive')->name('incentiveDatatable');
        Route::any('collection_register', 'ReportController@getcollregister')->name('collregisterDatatable');

        Route::any('branchr', 'ReportController@getbranch')->name('branchRDatatable');
        Route::any('arear', 'ReportController@getarea')->name('areaRDatatable');
        Route::any('zoner', 'ReportController@getzone')->name('zoneRDatatable');

        Route::any('installment_receivable', 'ReportController@getInstallmentReceivable');




    });

    // // ## Necissity File  Route C
    Route::group(['prefix' => 'file_management'], function () {
        Route::any('/', 'FileController@index')->name('fileDatatable');
        Route::any('add', 'FileController@add');
        Route::any('edit/{id}', 'FileController@edit');
        Route::get('view/{id}', 'FileController@download');
        Route::any('delete/{id}', 'FileController@delete');
        Route::any('publish/{id}', 'FileController@isActive');
        Route::any('destroy/{id}', 'FileController@destroy');
    });

});
