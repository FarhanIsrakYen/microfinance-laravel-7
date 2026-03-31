<?php

Route::group(['middleware' => ['auth', 'permission'], 'prefix' => 'acc', 'namespace' => 'ACC'], function () {

    Route::get('/', 'DashboardController@index');
    Route::any('/branch_status', 'DashboardController@branchStatus')->name('BranchStatus');

    Route::any('/org_status', 'DashboardController@organizationStatus')->name('orgStatus');

    Route::any('/org_graph_surplus', 'DashboardController@graphSurplusBranch')->name('org_graph_surplus');
    Route::any('/org_graph_surplus_all', 'DashboardController@graphSurplusAll')->name('org_graph_surplus_all');

    Route::any('/org_graph_cb', 'DashboardController@graphCashBankBranch')->name('org_graph_cb');
    // Route::any('/org_graph_cb_all', 'DashboardController@graphCashBankAll')->name('org_graph_cb_all');

    Route::any('/org_graph_ie', 'DashboardController@graphIncomeExpenseBranch')->name('org_graph_ie');
    // Route::any('/org_graph_ie_all', 'DashboardController@graphIncomeExpenseAll')->name('org_graph_ie_all');



    // // ## AccountType Route
    Route::group(['prefix' => 'acc_type'], function () {

        Route::any('/', 'AccountTypeController@index')->name('accountTypeDatatable');
        Route::any('add', 'AccountTypeController@add');
        Route::any('edit/{id}', 'AccountTypeController@edit');
        Route::get('view/{id}', 'AccountTypeController@view');
        Route::any('delete/{id}', 'AccountTypeController@delete');
        Route::any('publish/{id}', 'AccountTypeController@isActive');
        //Route::get('approve/{id}', 'AccountTypeController@isApprove');

    });

    // // ## VoucherType Route
    Route::group(['prefix' => 'voucher_type'], function () {

        Route::any('/', 'VoucherTypeController@index');
        Route::any('add', 'VoucherTypeController@add');
        Route::any('edit/{id}', 'VoucherTypeController@edit');
        Route::get('view/{id}', 'VoucherTypeController@view');
        Route::any('delete/{id}', 'VoucherTypeController@delete');

        // Route::any('delete', 'VoucherTypeController@delete')->name('vtype/del');

        Route::any('publish/{id}', 'VoucherTypeController@isActive');
        //Route::get('approve/{id}', 'VoucherTypeController@isApprove');

    });
    // // ## Day End Acc Route
    Route::group(['prefix' => 'day_end'], function () {

        Route::any('/', 'AccDayEndController@index')->name('accdayendDatatable');;
        Route::post('end', 'AccDayEndController@end');
        Route::any('delete/{id}', 'AccDayEndController@delete');
        // Route::post('accdayendDatatable', 'AccDayEndController@accdayendDatatable')->name('accdayendDatatable');

        Route::get('/ajaxdDeleteAccDayEnd', 'AccDayEndController@ajaxDeleteAccDayEnd');

    });

    // // ## month End Acc Route
    Route::group(['prefix' => 'month_end'], function () {

        Route::any('/', 'AccMonthEndController@index')->name('accmonthendDatatable');
        // Route::post('end', 'AccMonthEndController@end');
        // Route::any('delete/{id}', 'AccMonthEndController@delete');
        // Route::post('accdayendDatatable', 'AccDayEndController@accdayendDatatable')->name('accdayendDatatable');
        // Route::any('/', 'AccMonthEndController@index');
        Route::post('execute', 'AccMonthEndController@execute');
        Route::get('checkDayEndData', 'AccMonthEndController@checkDayEndData');
        Route::get('delete', 'AccMonthEndController@isDelete');

    });

    // // ## Yearr End Acc Route
    Route::group(['prefix' => 'year_end'], function () {

        Route::any('/', 'AccYearEndController@index')->name('accyearendDatatable');
        // Route::post('end', 'AccYearEndController@end');
        // Route::any('delete/{id}', 'AccYearEndController@delete');
        // Route::post('accdayendDatatable', 'AccDayEndController@accdayendDatatable')->name('accdayendDatatable');
        // Route::any('/', 'AccYearEndController@index');
        Route::post('execute', 'AccYearEndController@execute');
        Route::get('checkMonthEndData', 'AccYearEndController@checkMonthEndData');
        Route::get('delete', 'AccYearEndController@isDelete');

    });

    // // ## MIS Configaration Route
    Route::group(['prefix' => 'mis_config'], function () {

        Route::any('/', 'MisConfigController@index');
        Route::any('add', 'MisConfigController@add');
        Route::any('edit/{id}', 'MisConfigController@edit');
        Route::get('view/{id}', 'MisConfigController@view');
        Route::any('delete/{id}', 'MisConfigController@delete');
        Route::any('publish/{id}', 'MisConfigController@isActive');

    });
    // // ## Ledger  Route
    Route::group(['prefix' => 'ledger'], function () {

        Route::any('/', 'LedgerController@index');
        Route::any('add', 'LedgerController@add');
        Route::any('add/{id}', 'LedgerController@addin');
        Route::post('add/{id}', 'LedgerController@add');
        Route::any('edit/{id}', 'LedgerController@edit');
        Route::get('view/{id}', 'LedgerController@view');
        Route::any('delete/{id}', 'LedgerController@delete');
        Route::any('publish/{id}', 'LedgerController@isActive');
        Route::get('aajaxLedgerTabledd', 'LedgerController@ajaxLedgerTable')->name('ajaxLedgerTable');

    });
// Opening Balance Route
    Route::group(['prefix' => 'acc_ob'], function () {

        Route::any('/', 'OpeningBalanceController@index')->name('ACCOpeningBalance');
        Route::any('add', 'OpeningBalanceController@add');
        Route::any('add/{id}', 'OpeningBalanceController@addin');
        Route::post('add/{id}', 'OpeningBalanceController@add');
        Route::any('edit/{id}', 'OpeningBalanceController@edit');
        Route::get('view/{id}', 'OpeningBalanceController@view');
        Route::any('delete/{id}', 'OpeningBalanceController@delete');
        Route::any('publish/{id}', 'OpeningBalanceController@isActive');

        Route::get('ajaxBLoadForACOB', 'OpeningBalanceController@ajaxBranchLoad')->name('ajaxBLoadForACOB');
        Route::get('ajaxLedgerACOB', 'OpeningBalanceController@ajaxLedgerLoad')->name('ajaxLedgerACOB');
    });

    // // ## AutoVoucher Route  working on hold
    Route::group(['prefix' => 'auto_v_config'], function () {

        Route::any('/', 'AutoVoucherConfigController@index');
        Route::any('add', 'AutoVoucherConfigController@add');
        Route::any('edit/{id}', 'AutoVoucherConfigController@edit');
        Route::get('view/{id}', 'AutoVoucherConfigController@view');
        Route::any('delete/{id}', 'AutoVoucherConfigController@delete');
        Route::any('publish/{id}', 'AutoVoucherConfigController@isActive');

    });

    // // ## AutoVoucher
    Route::group(['prefix' => 'auto_vouchers'], function () {

        Route::any('/', 'AutoVoucherController@index')->name('autoVoucherDatatable');
        // Route::any('/', 'AutoVoucherController@index');
        // Route::any('add', 'AutoVoucherController@add');
        // Route::any('edit/{id}', 'AutoVoucherController@edit');
        Route::get('view/{id}', 'AutoVoucherController@view');
        // Route::any('delete/{id}', 'AutoVoucherController@delete');
        // Route::any('publish/{id}', 'AutoVoucherController@isActive');

    });

    // // ## AccountType Route
    Route::group(['prefix' => 'vouchers'], function () {
        Route::any('/', 'VoucherController@index')->name('voucherDatatable');
        Route::any('add', 'VoucherController@add');
        Route::any('edit/{id}', 'VoucherController@edit');
        Route::get('view/{id}', 'VoucherController@view');
        Route::any('delete/{id}', 'VoucherController@delete');
        Route::any('publish/{id}', 'VoucherController@isActive');
        //Route::get('approve/{id}', 'VoucherController@isApprove');

    });

    // // ## Auth Voucher Route
    Route::group(['prefix' => 'auth_vouchers'], function () {
        Route::any('/', 'AuthVoucherController@index')->name('authvoucherDatatable');
        Route::any('auth/{id}', 'AuthVoucherController@isAuth');

    });

    // // ## Unauth voucher Route
    Route::group(['prefix' => 'unauth_vouchers'], function () {
        Route::any('/', 'UnauthVoucherController@index')->name('unauthvoucherDatatable');

        Route::any('auth/{id}', 'UnauthVoucherController@isAuth');

    });

    // // ## Auto Auth Voucher Route
    Route::group(['prefix' => 'auto_auth_vouchers'], function () {
        Route::any('/', 'AutoAuthVoucherController@index')->name('autoauthvoucherDatatable');
        Route::any('auth/{id}', 'AutoAuthVoucherController@isAuth');

    });

    // // ## Auto Unauth voucher Route
    Route::group(['prefix' => 'auto_unauth_vouchers'], function () {
        Route::any('/', 'AutoUnauthVoucherController@index')->name('autounauthvoucherDatatable');

        Route::any('auth/{id}', 'AutoUnauthVoucherController@isAuth');

    });

    // // ## Report Route C
    Route::group(['prefix' => 'report'], function () {

        // // ## Report Ledger Route
        Route::any('ledger_report', 'ReportController@getledger')->name('ledgerReportDatatable');

        // // ## Report Trial Balance Route
        Route::any('trail_bal_report', 'ReportController@getTrialBalance')->name('TBalanceReportDatatable');

        // // ## Report Branch Wise Ledger Route
        Route::any('branch_ledger_report', 'ReportController@getBranchWiseLedger')->name('BranchLedgerReportDatatable');

        // // ## Report Consolidated Income Statement Route
        Route::any('com_income_stat', 'ReportController@getIncomeStatement')->name('IncomeStatementDatatable');

        // // ## Report Cash Book Route
        Route::any('cash_book', 'ReportController@getCashBook')->name('CashBookReportDatatable');

        // // ## Report Cash Book Route
        Route::any('bank_book', 'ReportController@getBankBook')->name('BankBookReportDatatable');

        // // ## Report Balance Sheet Route
        Route::any('stat_fin_position', 'ReportController@getBalanceSheet')->name('BalanceSheetReportDatatable');

        // // ## Report Receipt Payment Route
        Route::any('receipt_stat', 'ReportController@getReceiptPayment')->name('ReceiptPaymentReportDatatable');

    });
});
