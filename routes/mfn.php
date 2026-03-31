<?php

// MFN config setting
Route::any('mfn/initialSetting', 'MFN\GConfig\SettingController@add');

Route::group(['middleware' => ['auth', 'CheckMfnSetting'], 'prefix' => 'mfn', 'namespace' => 'MFN'], function () {

    Route::get('/', 'DashboardController@index');
    Route::get('/branch-status', 'DashboardController@branchStatus')->name('mfnBranchStatus');

    // COMMON THINGS GOES HERE

    Route::group(['namespace' => 'Others'], function () {
        // this class is used to do miscellaneous tasks
        Route::get('/miscellaneous', 'MiscellaneousController@index');
        
        // this class is used to find out bugs
        Route::get('/findbug/{branchId?}', 'CheckInappropriateDataController@index');

        // get a single row from tables
        Route::post('/getObject', 'CommonController@getObject');

        // get samity list on selecting branch
        Route::post('/getSamities', 'CommonController@getSamities');
        // get branch date of a branch
        Route::post('/getBranchDate', 'CommonController@getBranchDate');

        // get Active member
        Route::post('/getMember', 'CommonController@getMember');

        // get member details
        Route::post('/getMemberDetails', 'CommonController@getMemberDetails');

        // get Loan Accounts
        Route::post('/getLoanAccounts', 'CommonController@getLoanAccounts');

        // get Savings Accounts
        Route::post('/getSavingsAccounts', 'CommonController@getSavingsAccounts');

        // get address
        Route::post('/getDistricts', 'CommonController@getDistricts');
        Route::post('/getUpazilas', 'CommonController@getUpazilas');
        Route::post('/getUnions', 'CommonController@getUnions');
        Route::post('/getVillages', 'CommonController@getVillages');

        //get banck ledger for mfn
        Route::any('/getBankLedgerId', 'CommonController@getBankLedgerId');
    });

    Route::group(['namespace' => 'Samity'], function () {
        //------------------------Route For Samity --------//
        Route::any('samity', 'SamityController@index');
        Route::get('/samity/view/{samityId}', 'SamityController@viewSamity');
        Route::any('/samity/add', 'SamityController@addSamity');
        Route::any('/samity/edit/{samityId?}', 'SamityController@editSamity');
        Route::post('/samity/delete', 'SamityController@deleteSamity');

        // // ## Samity Field Officer Change Route
        Route::group(['prefix' => 'samityFieldOfficerChange'], function () {
            Route::any('/', 'SamityFieldOfficerChangeController@index');
            Route::get('/view/{samityFOCId}', 'SamityFieldOfficerChangeController@view');
            Route::any('/add', 'SamityFieldOfficerChangeController@add');
            Route::any('/edit/{samityFOCId?}', 'SamityFieldOfficerChangeController@edit');
            Route::any('/delete', 'SamityFieldOfficerChangeController@delete');
            Route::post('/getSamityInfo', 'SamityFieldOfficerChangeController@getSamityInfo');
        });

        // // ## Samity Day Change Route
        Route::group(['prefix' => 'samityDayChange'], function () {
            Route::any('/', 'SamityDayChangeController@index');
            Route::get('/view/{samityDC}', 'SamityDayChangeController@view');
            Route::any('add', 'SamityDayChangeController@add');
            Route::any('edit/{samityDC?}', 'SamityDayChangeController@edit');
            Route::post('getCurrentSamityDay', 'SamityDayChangeController@getCurrentSamityDay');
            Route::post('/delete', 'SamityDayChangeController@delete');
        });

        // // ## Samity Closing Route
        Route::group(['prefix' => 'samityclosing'], function () {
            Route::any('/', 'SamityClosingController@index');
            Route::match(['get', 'post'], 'add', 'SamityClosingController@add');
            Route::post('/delete', 'SamityClosingController@delete');
        });
    });

    Route::group(['namespace' => 'Member'], function () {

        // // ## Member Route
        Route::group(['prefix' => 'member'], function () {
            Route::any('/', 'MemberController@index');
            Route::any('add', 'MemberController@add');
            Route::any('edit/{id}', 'MemberController@edit');
            Route::get('view/{memberId}', 'MemberController@view');
            Route::post('delete', 'MemberController@delete');

            Route::any('saveingsDetails', 'MemberController@showsavingsDetails')->name('saveingsDetails');
            Route::any('loanDetails', 'MemberController@showloanDetails')->name('loanDetails');
            /* get admission related information on add or update */
            Route::post('getData', 'MemberController@getData');
        });

        // // ## Member Samity Transfe Route
        Route::group(['prefix' => 'memberSamityTransfer'], function () {
            Route::any('/', 'MemberSamityTransferController@index');
            Route::any('add', 'MemberSamityTransferController@add');
            Route::get('view/{id}', 'MemberSamityTransferController@view');
            Route::any('edit/{id}', 'MemberSamityTransferController@edit');
            Route::any('getData', 'MemberSamityTransferController@getData');
            Route::post('delete', 'MemberSamityTransferController@delete'); 
        });

        // // ## Member Primary Product Transfer Route
        Route::group(['prefix' => 'memberPrimaryProductTransfer'], function () {


            Route::any('/', 'MemberPrimaryProductTransferController@index');
            Route::any('add', 'MemberPrimaryProductTransferController@add');
            Route::get('view/{id}', 'MemberPrimaryProductTransferController@view');
            Route::any('edit/{id}', 'MemberPrimaryProductTransferController@edit');
            Route::post('delete', 'MemberPrimaryProductTransferController@delete');
            Route::post('/getData', 'MemberPrimaryProductTransferController@getData');
        });

        // // ## Member Closing Route
        Route::group(['prefix' => 'memberclosing'], function () {
            Route::any('/', 'MemberClosingController@index');
            Route::any('add', 'MemberClosingController@add');
            Route::get('view/{id}', 'MemberClosingController@view');
            Route::post('delete/{id}', 'MemberClosingController@delete');
        });
    });

    Route::group(['namespace' => 'Savings', 'prefix' => 'savings'], function () {

        Route::group(['prefix' => 'account'], function () {
            Route::any('/', 'SavingsAccountController@index');
            Route::any('/add', 'SavingsAccountController@add');
            Route::any('/edit/{id}', 'SavingsAccountController@edit');
            Route::any('/view/{id}', 'SavingsAccountController@view');
            Route::post('/delete', 'SavingsAccountController@delete');
            Route::post('/getData', 'SavingsAccountController@getData');
        });

        Route::group(['prefix' => 'deposit'], function () {
            Route::any('/', 'DepositController@index');
            Route::any('/add', 'DepositController@add');
            Route::any('/edit/{id}', 'DepositController@edit');
            Route::any('/view/{id}', 'DepositController@view');
            Route::post('/delete', 'DepositController@delete');
            Route::post('/getData', 'DepositController@getData');
        });

        Route::group(['prefix' => 'withdraw'], function () {
            Route::any('/', 'WithdrawController@index');
            Route::any('/add', 'WithdrawController@add');
            Route::any('/edit/{id}', 'WithdrawController@edit');
            Route::any('/view/{id}', 'WithdrawController@view');
            Route::post('/delete', 'WithdrawController@delete');
            Route::post('/getData', 'WithdrawController@getData');
        });
        Route::group(['prefix' => 'closing'], function () {
            Route::any('/', 'ClosingController@index')->name('closingsDatatable');
            Route::any('closingaccountDetails', 'ClosingController@closingaccountDetails')->name('closingaccountDetails');
            Route::any('/add', 'ClosingController@add');
            Route::any('/view/{id}', 'ClosingController@view');
            Route::post('/delete', 'ClosingController@delete');
            Route::post('/getData', 'ClosingController@getData');
        });

        Route::group(['prefix' => 'status'], function () {
            Route::any('/', 'StatusController@index');
            Route::any('SavingsStatusDetails', 'StatusController@SavingsStatusDetails')->name('SavingsStatusDetails');
            Route::any('/view/{id}', 'StatusController@view');
        });

        Route::group(['prefix' => 'savingProvision'], function () {
            Route::any('/', 'SavingProvisionController@index');
            Route::any('/add', 'SavingProvisionController@add');
            Route::any('/delete', 'SavingProvisionController@delete');
            Route::any('/view', 'SavingProvisionController@view');
            Route::post('/getData', 'SavingProvisionController@getData');
        });
    });

    Route::group(['namespace' => 'Savings', 'prefix' => 'savings_ob'], function () {
        Route::any('/', 'SavingsOBController@index');
        Route::any('SavingsOBDetails', 'SavingsOBController@SavingsStatusDetails')->name('SavingsOBDetails');
        // Route::group(['prefix' => 'status'], function () {
        //     Route::any('/', 'StatusController@index');
        //     Route::any('SavingsStatusDetails', 'StatusController@SavingsStatusDetails')->name('SavingsStatusDetails');
        //     Route::any('/view/{id}', 'StatusController@view');
        // });
    });
    // // ## Share account Route
    Route::group(['namespace' => 'Share', 'prefix' => 'share_acc'], function () {
        Route::any('/', 'ShareAccountController@index');
        Route::any('/add', 'ShareAccountController@add');
        Route::any('/edit/{id}', 'ShareAccountController@edit');
        Route::any('/view/{id}', 'ShareAccountController@view');
        Route::post('/delete', 'ShareAccountController@delete');
        Route::post('/getData', 'ShareAccountController@getData');
    });
    // // ## Share withdraw Route
    Route::group(['namespace' => 'Share', 'prefix' => 'share_withdraw'], function () {
        Route::any('/', 'ShareWithdrawController@index');
        Route::any('/add', 'ShareWithdrawController@add');
        Route::any('/edit/{id}', 'ShareWithdrawController@edit');
        Route::any('/view/{id}', 'ShareWithdrawController@view');
        Route::post('/delete', 'ShareWithdrawController@delete');
        Route::post('/getData', 'ShareWithdrawController@getData');
    });

    Route::group(['namespace' => 'Loan'], function () {

        // // ## Regular Loan Route
        Route::group(['prefix' => 'regularloan'], function () {
            Route::any('/', 'LoanController@index');
            Route::any('/add', 'LoanController@add');
            Route::any('/view/{id}', 'LoanController@view');
            Route::any('/edit/{id}', 'LoanController@edit');
            Route::post('/delete', 'LoanController@delete');
            Route::any('getData', 'LoanController@getData');
        });

        // // ## Onetime Loan Route
        Route::group(['prefix' => 'oneTimeLoan'], function () {
            Route::any('/', 'LoanController@index');
            Route::any('/add', 'LoanController@add');
            Route::any('/view/{id}', 'LoanController@view');
            Route::any('/edit/{id}', 'LoanController@edit');
            Route::post('/delete', 'LoanController@delete');
            Route::any('getData', 'LoanController@getData');
        });

        // // ## Loan Reschedule
        Route::group(['prefix' => 'loanReschedule'], function () {
            Route::any('/', 'LoanRescheduleController@index');
            Route::any('add', 'LoanRescheduleController@add');
            Route::any('edit/{id}', 'LoanRescheduleController@edit');
            Route::post('delete', 'LoanRescheduleController@delete');
            Route::post('getData', 'LoanRescheduleController@getData');
        });

        // // ## Regular Loan Transaction Route
        Route::group(['prefix' => 'regularloanTransaction'], function () {
            Route::any('/', 'LoanTransactionController@index');
            Route::match(['get', 'post'], 'add', 'LoanTransactionController@add');
            Route::any('/edit/{id}', 'LoanTransactionController@edit');
            Route::post('/delete', 'LoanTransactionController@delete');
            Route::any('/view', 'LoanTransactionController@view');
            Route::any('getData', 'LoanTransactionController@getData');
        });

        /*
         * One time loan transaction route
         */
        Route::group(['prefix' => 'oneTimeLoanTransaction'], function () {
            Route::any('/', 'LoanTransactionController@index');
            Route::match(['get', 'post'], 'add', 'LoanTransactionController@add');
            Route::any('/edit/{id}', 'LoanTransactionController@edit');
            Route::post('/delete', 'LoanTransactionController@delete');
            Route::any('/view', 'LoanTransactionController@view');
            Route::any('getData', 'LoanTransactionController@getData');
        });

        /*
         * Loan Rebate route
         */
        Route::group(['prefix' => 'loanRebate'], function () {
            Route::any('/', 'RebateController@index');
            Route::match(['get', 'post'], 'add', 'RebateController@add');
            Route::any('/edit/{id}', 'RebateController@edit');
            Route::post('/delete', 'RebateController@delete');
            Route::any('/view', 'RebateController@view');
            Route::any('getData', 'RebateController@getData');
        });

        /*
         * Write off route
         */
        Route::group(['prefix' => 'writeOff'], function () {
            Route::any('eligibleList', 'WriteOffController@eligibleList');
            Route::any('/', 'WriteOffController@index');
            Route::match(['get', 'post'], 'add/{id}', 'WriteOffController@add');
            Route::post('/delete', 'WriteOffController@delete');
            Route::any('getData', 'WriteOffController@getData');
        });

        /*
         * Loan Waiver route
         */
        Route::group(['prefix' => 'loanWaiver'], function () {
            Route::any('/', 'WaiverController@index');
            Route::match(['get', 'post'], 'add', 'WaiverController@add');
            Route::post('/delete', 'WaiverController@delete');
            Route::any('/view', 'WaiverController@view');
            Route::any('getData', 'WaiverController@getData');
        });

        /*
         * Loan adjustment route
         */
        Route::group(['prefix' => 'loanAdjustment'], function () {
            Route::any('/', 'AdjustmentController@index');
            Route::match(['get', 'post'], 'add', 'AdjustmentController@add');
            Route::any('/edit/{id}', 'AdjustmentController@edit');
            Route::post('/delete', 'AdjustmentController@delete');
            Route::post('/approve', 'AdjustmentController@approve');
            Route::any('/view', 'AdjustmentController@view');
            Route::any('getData', 'AdjustmentController@getData');
        });

        /*
         * Write off Collection route
         */
        Route::group(['prefix' => 'writeOffCollection'], function () {
            Route::any('/list', 'WriteOffCollectionController@list');
            Route::any('/', 'WriteOffCollectionController@index');
            Route::match(['get', 'post'], 'add/{id}', 'WriteOffCollectionController@add');
            Route::any('/edit/{id}', 'WriteOffCollectionController@edit');
            Route::any('/view', 'WriteOffCollectionController@view');
            Route::post('/delete', 'WriteOffCollectionController@delete');
            Route::any('getData', 'WriteOffCollectionController@getData');
        });
    });

    Route::group(['namespace' => 'GConfig'], function () {
        // // ## Working Area Route
        Route::group(['prefix' => 'workingarea'], function () {
            Route::any('/', 'WorkingAreaController@index')->name('workingareaDatatable');
            Route::get('/view/{wareaId}', 'WorkingAreaController@view');
            Route::match(['get', 'post'], 'add', 'WorkingAreaController@add');
            Route::match(['get', 'post'], '/edit/{wareaId?}', 'WorkingAreaController@edit');
            Route::post('/delete', 'WorkingAreaController@delete');
        });

        // // ## Field Officer Route
        Route::group(['prefix' => 'fieldofficer'], function () {
            Route::get('/', 'FieldOfficerController@index');
            Route::match(['get', 'post'], 'add', 'FieldOfficerController@add');
            Route::match(['get', 'post'], '/edit/{fieldOfficerId?}', 'FieldOfficerController@edit');
        });

        // // ## Savings Products Route
        Route::group(['prefix' => 'savingsProduct'], function () {
            Route::any('/', 'SavingsProductController@index');
            Route::any('/view/{savingsProductId?}', 'SavingsProductController@view');
            Route::any('add', 'SavingsProductController@add');
            Route::any('/edit/{savingsProductId?}', 'SavingsProductController@edit');
            Route::post('/delete/{savingsProductId?}', 'SavingsProductController@delete');
        });

        // // ## Savings Product's Interest Route
        Route::group(['prefix' => 'savingsProduct/interest'], function () {
            Route::any('add/{productId?}', 'SavingsProductInterestController@add');
            Route::get('view/{inetrestId}', 'SavingsProductInterestController@view');
        });

        // // ## Funding Organizations Route
        Route::group(['prefix' => 'fundingOrganization'], function () {
            Route::any('/', 'FundingOrganizationController@index');
            Route::match(['get', 'post'], 'add', 'FundingOrganizationController@add');
            Route::match(['get', 'post'], '/edit/{id?}', 'FundingOrganizationController@edit');
            Route::post('/delete', 'FundingOrganizationController@delete');
        });

        // // ## Loan Products Route
        Route::group(['prefix' => 'loanProducts'], function () {
            Route::any('/', 'LoanProductController@index');
            Route::any('view/{loanProductId}', 'LoanProductController@view');
            Route::match(['get', 'post'], 'add', 'LoanProductController@add');
            Route::match(['get', 'post'], '/edit/{loanProductId?}', 'LoanProductController@edit');
            Route::post('/delete', 'LoanProductController@delete');
        });

        // // ## Loan Products Interest Rates
        Route::group(['prefix' => 'loanProducts/interest'], function () {
            Route::any('add/{productId?}', 'LoanProductInterestController@add');
            Route::post('getProductFrequencyWiseInstallments', 'LoanProductInterestController@getProductFrequencyWiseInstallments');
        });

        // // ## Loan Product Category Route
        Route::group(['prefix' => 'loanProductCategory'], function () {
            Route::any('/', 'LoanProductCategoryController@index')->name('loanProdCatDatatable');
            Route::any('delete/{loanProdCatId}', 'LoanProductCategoryController@delete');
            Route::get('/view/{loanProdCatId}', 'LoanProductCategoryController@view');
            Route::match(['get', 'post'], 'add', 'LoanProductCategoryController@add');
            Route::match(['get', 'post'], '/edit/{loanProdCatId?}', 'LoanProductCategoryController@edit');
        });

        // // ## Product Assign Route
        Route::group(['prefix' => 'branchProduct'], function () {
            Route::any('/', 'BranchProductController@index')->name('prodAssignDatatable');
            Route::any('delete/{prodAssignId}', 'BranchProductController@delete');
            Route::get('/view/{prodAssignId}', 'BranchProductController@view');
            Route::match(['get', 'post'], 'add', 'BranchProductController@add');
            Route::match(['get', 'post'], '/edit/{prodAssignId?}', 'BranchProductController@edit');
        });

        // // ## PKSF Funds Route
        Route::group(['prefix' => 'pksfFunds'], function () {
            Route::any('/', 'PksfFundsController@index')->name('pksfFundsDatatable');
            Route::match(['get', 'post'], 'add', 'PksfFundsController@add');
            Route::match(['get', 'post'], '/edit/{pksfFundId?}', 'PksfFundsController@edit');
            Route::post('/delete', 'PksfFundsController@delete');
        });

        // // ## Auto Voucher Config
        Route::group(['prefix' => 'autoVoucherConfig'], function () {
            Route::any('/', 'AutoVoucherConfigurationController@add');
            Route::any('/loadDiv', 'AutoVoucherConfigurationController@makeConfigDiv');
        });

        // // ## Opening Loan Information Route
        Route::group(['prefix' => 'openingLoanInfo'], function () {
            Route::any('/', 'OpeningLoanController@index')->name('openingLoanDatatable');
            Route::get('/view/{loanId}', 'OpeningLoanController@view');
            Route::match(['get', 'post'], 'add', 'OpeningLoanController@add');
            Route::match(['get', 'post'], '/edit/{loanId?}', 'OpeningLoanController@edit');
            Route::post('/delete/{loanId}', 'OpeningLoanController@delete');
        });

        // // ## Opening Savings Information Route
        Route::group(['prefix' => 'openingSavingsInfo'], function () {
            Route::any('/', 'OpeningSavingsController@index')->name('openingSavingsDatatable');
            Route::get('/view/{savingsId}', 'OpeningSavingsController@view');
            Route::match(['get', 'post'], 'add', 'OpeningSavingsController@add');
            Route::match(['get', 'post'], '/edit/{savingsId?}', 'OpeningSavingsController@edit');
            Route::post('/delete/{savingsId}', 'OpeningSavingsController@delete');
        });

        // // ## Global Cnfiguration Route
        Route::group(['prefix' => 'global_config'], function () {
            Route::any('/', 'SettingController@index');
        });

        // // ## Notice Route
        Route::group(['prefix' => 'notice'], function () {
            Route::any('/', 'NoticeController@index')->name('noticeDatatable');
            Route::match(['get', 'post'], 'add', 'NoticeController@add');
            Route::match(['get', 'post'], '/edit/{noticeId?}', 'NoticeController@edit');
            Route::post('/delete/{noticeId}', 'NoticeController@delete');
        });
    });

    Route::group(['namespace' => 'ProductInterest'], function () {

        // // ## Product Interest Rate
        Route::group(['prefix' => 'productInterest'], function () {
            Route::any('/', 'ProductInterestController@index');
            Route::get('/view/{prodInterestId}', 'ProductInterestController@view');
            Route::match(['get', 'post'], '/edit/{prodInterestId?}', 'ProductInterestController@edit');
        });
    });

    // // ## Day End  Route
    Route::group(['namespace' => 'Process'], function () {

        Route::group(['prefix' => 'day_end'], function () {
            Route::any('/', 'MfnDayEndController@index');

            // Route::post('end', 'MfnDayEndController@end');
            // Route::any('delete/{id}', 'MfnDayEndController@delete');
            Route::post('mfndayendDatatable', 'MfnDayEndController@mfndayendDatatable')->name('mfndayendDatatable');
            Route::any('mfndayendexecute', 'MfnDayEndController@end')->name('mfndayendexecute');
            Route::any('deletemfndayend', 'MfnDayEndController@delete')->name('deletemfndayend');
        });
        Route::group(['prefix' => 'month_end'], function () {
            Route::any('/', 'MfnMonthEndController@index')->name('mfnmonthendDatatable');

            Route::get('checkDayEndData', 'MfnMonthEndController@checkDayEndData');
            Route::post('execute', 'MfnMonthEndController@execute');
            Route::any('delete', 'MfnMonthEndController@delete');
            //
            //
            // Route::get('delete', 'AccMonthEndController@isDelete');
            // Route::post('end', 'MfnDayEndController@end');

            // Route::post('mfndayendDatatable', 'MfnDayEndController@mfndayendDatatable')
            // Route::any('mfndayendexecute', 'MfnDayEndController@end')->name('mfndayendexecute');
            // Route::any('deletemfndayend', 'MfnDayEndController@delete')->name('deletemfndayend');

        });

        Route::group(['prefix' => 'year_end'], function () {
            Route::any('/', 'MfnYearEndController@index');

            Route::post('execute', 'MfnYearEndController@execute');
            Route::get('checkMonthEndData', 'MfnYearEndController@checkMonthEndData');
            Route::get('delete', 'MfnYearEndController@isDelete');
        });

        Route::group(['prefix' => 'trnsc_auth'], function () {
            Route::any('/', 'TransactionAuthController@index');

            Route::post('transactionauthDatatable', 'TransactionAuthController@transactionauthDatatable')->name('transactionauthDatatable');
            Route::any('ajaxTransactionAuth', 'TransactionAuthController@ajaxTransactionAuth')->name('ajaxTransactionAuth');
        });

        Route::group(['prefix' => 'auto_pro'], function () {
            Route::any('/', 'AutoProcessController@index')->name('DatatabledetailsSamity');

            Route::any('/add/{samityId?}/{autoProcessDate?}', 'AutoProcessController@add');
            Route::any('/edit/{samityId?}/{autoProcessDate?}', 'AutoProcessController@edit');
        });

        Route::group(['prefix' => 'trnsc_unauth'], function () {
            Route::any('/', 'TransactionUnauthController@index');

            Route::post('transactionUnauthDatatable', 'TransactionUnauthController@transactionUnauthDatatable')->name('transactionUnauthDatatable');

            Route::get('/view/{id?}/{date?}', 'TransactionUnauthController@view');

            Route::any('ajaxTransactionUnauth', 'TransactionUnauthController@ajaxTransactionUnauth')->name('ajaxTransactionUnauth');
        });
    });

    // // ## Report Route C

    Route::group(['namespace' => 'Reports', 'prefix' => 'reports'], function () {

        Route::group(['namespace' => 'Register'], function () {
            Route::group(['namespace' => 'Regular'], function () {
                Route::any('dueRegister', 'DueRegisterReportController@getDueRegister')->name('dueRegisterDataTable');

                Route::any('savingsRegister', 'SavingsRegisterReportController@getSavingsRegister')->name('savingsRegisterDataTable');
                Route::any('savingsRegisterReport', 'SavingsRegisterReportController@loadReportData')->name('savingsRegisterReportTable');
            });
        });

        Route::group(['namespace' => 'RegularGeneralReports'], function () {
            Route::any('memberLedger', 'MemberLedgerReportController@getmemberLedger')->name('memberLedgerDataTable');
        });

        /*
         * waiverReport, writeOff, rebateReport reports
         */
        Route::group(['namespace' => 'RegularGeneralReports'], function () {

            // Route::any('waiverReport', 'WaiverReportController@getWaiverData');
            Route::group(['prefix' => 'waiverReport'], function () {
                Route::any('/', 'WaiverReportController@getWaiverReportIndex');
                Route::any('/loadData', 'WaiverReportController@getWaiverData');
            });
            Route::group(['prefix' => 'writeOffReport'], function () {
                Route::any('/', 'WaiverReportController@getWriteOffDataIndex');
                Route::any('/loadData', 'WaiverReportController@getWriteOffData');
            });
            // Route::any('writeOffReport', 'WaiverReportController@getWriteOffData');
            Route::group(['prefix' => 'rebateReport'], function () {
                Route::any('/', 'WaiverReportController@getRebateDataIndex');
                Route::any('/loadData', 'WaiverReportController@getRebateData');
            });
            // Route::any('rebateReport', 'WaiverReportController@getRebateData');
            Route::any('waiverGetData', 'WaiverReportController@getData');

            /*
            * Preriodic collection report component wise
            */
            Route::group(['prefix' => 'periodic_collection_component_wise'], function () {
                Route::any('/', 'PccoReportController@index');
                Route::post('/getData', 'PccoReportController@getData');
                Route::any('/loadReportData', 'PccoReportController@loadReportData');
            });

            // Route::any('PCRComponentWise', 'PCRComponentWiseReport@getPCRComponentWiseFilterPart');
            // Route::any('PCRCWReport', 'PCRComponentWiseReport@getPCRComponentWiseViewPart');
            // Route::any('pcrGetData', 'PCRComponentWiseReport@getData');

            /*
            * daily collection component wise report route
            */
            Route::any('dailyCollectionComponentWise', 'DailyCollectionComponentWiseReportController@getDailyCollectionCompWiseFilterPart');
            Route::any('dailyCollectionComponentWiseTablePart', 'DailyCollectionComponentWiseReportController@getDailyCollectionCompWiseTablePart');
            Route::any('dcGetData', 'DailyCollectionComponentWiseReportController@getData');

            Route::group(['prefix' => 'collection_sheet'], function () {
                Route::any('/', 'CollectionSheetController@index');
                Route::any('/loadRreport', 'CollectionSheetController@printReport');
                Route::any('/getData', 'CollectionSheetController@getData');

                // Route::any('/', 'MfnSamityMonthySavingsLoanController@index');
                // Route::any('mfnReportgSamity', 'MfnSamityMonthySavingsLoanController@getDataSamity')->name('mfnReportgSamity');
                // Route::any('/loadMfnCollectionReport', 'MfnSamityMonthySavingsLoanController@ShowReport');
                // Route::any('mfnReportgSamitygetYears', 'MfnSamityMonthySavingsLoanController@getDataYears')->name('mfnReportgSamitygetYears');
            });
        });


        Route::group(['namespace' => 'Others', 'prefix' => 'member_migration_balance'], function () {
            Route::any('/', 'MfnMemberMigrationBalanceController@index');
            Route::any('/print_report', 'MfnMemberMigrationBalanceController@printReport');
            Route::any('/getData', 'MfnMemberMigrationBalanceController@getData');
        });

        Route::group(['namespace' => 'RegisterReport\Regular', 'prefix' => 'loan_collection_reg'], function () {
            Route::any('/', 'MfnLoanCollectionRegisterController@index');
            Route::any('/loadData', 'MfnLoanCollectionRegisterController@getData');
        });

        Route::group(['namespace' => 'RegisterReport', 'prefix' => 'loan_disburse_reg'], function () {
            Route::any('/', 'MfnLoanDisburseRegisterController@index');
            Route::any('/loadData', 'MfnLoanDisburseRegisterController@getData');
        });

        Route::group(['namespace' => 'RegisterReport\Regular', 'prefix' => 'dual_loanee_info'], function () {
            Route::any('/', 'MfnDualLoaneeInformationController@index');
            Route::any('/loadData', 'MfnDualLoaneeInformationController@getData');
        });

        Route::group(['namespace' => 'RegisterReport\Regular', 'prefix' => 'savings_interest_info'], function () {
            Route::any('/', 'SavingsInterestReportController@index');
            Route::any('/loadData', 'SavingsInterestReportController@getData');
        });

        Route::group(['namespace' => 'RegisterReport\Regular', 'prefix' => 'savingsProvision'], function () {
            Route::any('/', 'SavingsProvisionReportController@getFilterPart');
            Route::any('/singleTableView', 'SavingsProvisionReportController@getSingleTableView');
            Route::any('/getData', 'SavingsProvisionReportController@getData');
        });

        Route::group(['namespace' => 'RegisterReport\Regular', 'prefix' => 'advance_loan_reg'], function () {
            Route::any('/', 'AdvanceRegisterReportController@index')->name('advanceRegisterDataTable');
            Route::any('/loadReportData', 'AdvanceRegisterReportController@loadReportData');
            Route::any('/getData', 'AdvanceRegisterReportController@getData');
        });
        Route::group(['namespace' => 'RegisterReport\Regular', 'prefix' => 'member_closing'], function () {
            Route::any('/', 'MemberClosingReportController@index');
            Route::any('/loadData', 'MemberClosingReportController@getData');
        });

        Route::group(['namespace' => 'RegisterReport\Regular', 'prefix' => 'savings_closing'], function () {
            Route::any('/', 'SavingsClosingReportController@index');
            Route::any('/loadData', 'SavingsClosingReportController@getData');
        });

        Route::group(['namespace' => 'RegisterReport\Regular', 'prefix' => 'savings_refund_register'], function () {
            Route::any('/', 'SavingsRefundRegisterController@index');
            Route::any('/loadData', 'SavingsRefundRegisterController@getData');
        });

        Route::group(['namespace' => 'RegisterReport\Topsheet', 'prefix' => 'admission_reg'], function () {
            Route::any('/', 'AdmissionRegisterReportController@index');
            Route::any('/loadData', 'AdmissionRegisterReportController@getData');
        });

        Route::group(['namespace' => 'Others'], function () {
            Route::any('/loanStatement', 'LoanStatementReportController@getLoanStatement')->name('loanStatementDataTble');
        });

        Route::group(['namespace' => 'PksfReports', 'prefix' => 'pksf'], function () {

            /*
             * POMIS-2 Report
             */
            Route::any('pomis2', 'PksfPomisTwoController@getPksfPomisTwoFilterPart');
            Route::any('pomis2/viewPart', 'PksfPomisTwoController@getPksfPomisTwoViewPart');

            /*
             * POMIS-2A Report
             */
            Route::any('pomis2A', 'PksfPomisTwoController@getPksfPomisTwoAFilterPart');
            Route::any('pomis2A/viewPart', 'PksfPomisTwoController@getPksfPomisTwoAViewPart');

            /*
             * POMIS-3 Report
             */
            Route::any('pomis3', 'PksfPomisThreeController@getPksfPomisThreeFilterPart');
            Route::any('pomis3/viewPart', 'PksfPomisThreeController@getPksfPomisThreeViewPart');
        });

        Route::group(['namespace' => 'PksfReports', 'prefix' => 'pksf'], function () {
            Route::group(['prefix' => 'pomis1'], function () {

                //POMIS-1 Report
                Route::any('/', 'PksfPomisOneController@index');
                Route::any('/loadData', 'PksfPomisOneController@getData');
            });
        });
    });

    // Route::any('/meSummary/{branchId}/{month}', function ($branchId, $month) {
    //     return app('App\Http\Controllers\MFN\Process\MonthEndSummary')->storeMonthEndSummary($branchId, $month);
    // });

    // Route::any('/dayChange', function () {
    //     return app('App\Http\Controllers\MFN\Samity\SamityDayChangeController')->isDayChangeEffectLoanSchedule();
    // });

    Route::any('/sendMail', function () {

        $subject = 'Laravel Test';
        $name    = 'Mr. Saiful';
        $body    = 'This is test mail from ' . env('MAIL_FROM_NAME');

        // Bellow line is commented, but should be uncomment
        // Mail::to('saiful.b1k996@gmail.com')->send(new App\Mail\SendMail($subject, $name, $body));
    });

});

Route::get('mfn/mail/verify/{memberId}/{eToken}', 'MFN\Mail\MailVarificationController@isVerified');
