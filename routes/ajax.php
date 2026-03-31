<?php
Route::get('/ajaxSelectBoxObj', 'AjaxController@ajaxSelectBoxLoadObj');

Route::get('/ajaxSelectBox', 'AjaxController@ajaxSelectBoxLoad');
Route::get('/ajaxSelectBoxfortargetbranch', 'AjaxController@ajaxSelectBoxLoadForTargetBranch');
Route::get('/ajaxSelectBoxCode', 'AjaxController@ajaxSelectBoxCodeLoad');
Route::get('/ajaxSelectBoxforLedger', 'AjaxController@ajaxSelectBoxLoadforLedger');
Route::get('/ajaxSetInput', 'AjaxController@ajaxSetInput');

Route::get('/ajaxDeleteCheck', 'AjaxController@ajaxdDeleteCheck');

Route::get('/ajaxBranchOpendate', 'AjaxController@ajaxBranchOpendate');

//Route::get('/checkDuplicate', 'AjaxController@checkDuplicate');
Route::get('/ajaxCheckDuplicate', 'AjaxController@ajaxCheckDuplicate')->name('ajaxCheckDuplicate');
Route::get('/ajaxCheckDuplicateForHR', 'AjaxController@ajaxCheckDuplicateForHR')->name('ajaxCheckDuplicateForHR');

//supplier name load when select supplier in purchase load
Route::get('/ajaxSupplierNameLoad', 'AjaxController@ajaxSupplierNameLoad');

//Customer Mobile No load when select Customer in Sales load
Route::get('/ajaxCustomerMobileLoad', 'AjaxController@ajaxCustomerMobileLoad');

//Customer NID load when select Customer in Sales load
Route::get('/ajaxCustomerNIDLoad', 'AjaxController@ajaxCustomerNIDLoad');

// generate customer no
Route::get('/ajaxGCustomerNo', 'AjaxController@ajaxGenerateCustomerNo');
Route::get('/ajaxSupplierInfo', 'AjaxController@ajaxSupplierInfo');

Route::get('/ajaxBarcode', 'AjaxController@ajaxBarcodeGenerate');
Route::get('/ajaxBarcodeImage', 'AjaxController@ajaxBarcodeImage');

//Sales ID load when select Sales Bill in Collection
Route::get('/ajaxSalesIDLoad', 'AjaxController@ajaxSalesIDLoad');

/* * ************************************ Generate Bill No */
Route::get('/ajaxGBillPurchase', 'AjaxController@ajaxGenerateBillPurchase');
Route::get('/ajaxGBillPR', 'AjaxController@ajaxGenerateBillPurchaseReturn');

Route::get('/ajaxGBillIssue', 'AjaxController@ajaxGenerateBillIssue')->name('ajaxGBillIssue');
Route::get('/ajaxGBillIR', 'AjaxController@ajaxGenerateBillIssueReturn')->name('ajaxGBillIR');

Route::get('/ajaxGBillTransfer', 'AjaxController@ajaxGenerateBillTransfer');
Route::get('/ajaxBranchDate', 'AjaxController@ajaxBranchDate');


Route::get('/ajaxVoucherBillGen', 'AjaxController@ajaxGenerateBillVoucher');


// Route::get('/ajaxGBillSales', 'AjaxController@ajaxGenerateBillSales');
Route::get('/ajaxGBillSR', 'AjaxController@ajaxGenerateBillSalesReturn');
//genterate bill no for installement sales
Route::get('/ajaxGBillSales', 'AjaxController@ajaxGBillSales');
/* * ******************************** Generate Bill No End */

// ***************************************************Check Stock**********************
Route::get('/ajaxCheckStock', 'AjaxController@ajaxStockQuantity');
Route::get('/ajaxCheckOBStockBranch', 'AjaxController@ajaxCheckOBStockBranch');

/* * ************************************ Load Product Start */
Route::get('/ajaxProductLPurchase', 'AjaxController@ajaxProductLoadPurchase');
Route::get('/ajaxProductLPR', 'AjaxController@ajaxProductLoadPurchaseReturn');

Route::get('/ajaxProductLIssue', 'AjaxController@ajaxProductLoadIssue');
Route::get('/ajaxProductLIR', 'AjaxController@ajaxProductLoadIssueReturn');

Route::get('/ajaxProductLTransfer', 'AjaxController@ajaxProductLoadTransfer');

Route::get('/ajaxProductLSales', 'AjaxController@ajaxProductLoadSales');
Route::get('/ajaxProductLSR', 'AjaxController@ajaxProductLoadSalesReturn');

/// Day end Delete route
// Route::get('/ajaxDeleteDayEnd', 'AjaxController@ajaxDeleteDayEnd');
/// Acc Day end Delete route
// Route::get('/ajaxdDeleteAccDayEnd', 'AjaxController@ajaxDeleteAccDayEnd');

// Inventory Day end
// Route::get('/ajaxDeleteInvDayEnd', 'AjaxController@ajaxDeleteInvDayEnd');

Route::get('/ajaxdDeleteLedger', 'AjaxController@ajaxDeleteLedger');


/* * ************************************ Area select box by Zone id */
Route::get('/ajaxGetAreabyZone', 'AjaxController@ajaxGetAreabyZone');

/* * ************************************ Load Product End */
/* * **                                         Ajax for sale bill details  */

Route::get('/ajaxSalebillDetails', 'AjaxController@ajaxSalebillDetails');
Route::get('/ajaxUsebillDetails', 'AjaxController@ajaxUsebillDetails');


//get openning date into the obduesales
Route::get('/ajaxGetBOpDate', 'AjaxController@ajaxBranchOpendate');

//get customer sales details from collection table
Route::get('/ajaxCustSalesDetails', 'AjaxController@ajaxCustSalesDetails');
Route::get('/ajaxBillCollection', 'AjaxController@ajaxBillCollection');

/*
*get processing fee from db into installment sales
*/
Route::get('/ajaxGetProFee', 'AjaxController@getProcessingFee');

Route::get('/ajaxMenuList', 'AjaxController@ajaxMenuList');

/*
*get all customer for a particular branch
*/
Route::get('/ajaxGetBranchCustomer', 'AjaxController@getBranchCustomer');

/*
*insert sales bill no, collection amount, and last collection amount into the 'collection' table
*/
Route::post('/ajaxCustInsert', 'AjaxController@custInsertIntoClln');


//Route::get('/genBillNo', 'AjaxController@genBillNo');
//Route::get('/ajaxPurReturnBillNo', 'AjaxController@purReturnBillNo');

//************************ */ General Configuration Routing Group

//************************ */ Accounting auto voucher add page item /// MIS item
Route::get('/ajaxAutoVoucheritem', 'AjaxController@ajaxAutoVoucheritem');

/*get Employee from the All Collection Report
*/
Route::get('/ajaxGetEmployeeName', 'AjaxController@getEmployeeName');
/*get Sales bill no from the All Collection Report
*/
Route::get('/ajaxGetSalesBillNo', 'AjaxController@getSalesBillNo');
/*
*get area from the zone (current and over due report)
*/
Route::get('/ajaxGetArea', 'AjaxController@getArea');
/*
*get branch from the area (current and over due report)
*/
Route::get('/ajaxGetBranch', 'AjaxController@getBranch');
/*
*get product according to requisition_no
*/
Route::get('/ajaxOrderPurchaseProdLoad', 'AjaxController@getOrderPurchaseProdLoad');

Route::get('/ajaxReqIssueProdLoad', 'AjaxController@getReqProductInIssue');
Route::get('/ajaxReqLoadIssue', 'AjaxController@getReqLoadIssue');
/*
*get product according to order_no
*/
Route::get('/ajaxGetRequisitionNo', 'AjaxController@getReqInPurchase');
Route::get('/ajaxLoadSupplierProdByReq', 'AjaxController@getLoadSupplierProdByReq');

/*
*Ajax authorizing VOucher ajax
*/
Route::any('/ajaxVoucherAuth', 'AjaxController@ajaxVoucherAuth');
Route::any('/ajaxVoucherUnAuth', 'AjaxController@ajaxVoucherUnAuth');

Route::get('/ajaxGReqNo', 'AjaxController@ajaxGetRequisitionNo');

Route::get('/ajaxCollectionNo', 'AjaxController@ajaxCollectionNo');


///////////////////////////////////---------- MFN ---------------////////////////////////////// ajax route
//Route::get('/checkDuplicate', 'AjaxController@checkDuplicate');

///Customer name & NID load when select supplier in purchase load
Route::get('/ajaxSelectBoxObj', 'MfnAjaxController@ajaxSelectBoxLoadObj');
Route::get('/ajaxCustDataLoad', 'MfnAjaxController@ajaxCustDataLoad');
Route::get('/ajaxSelectBoxForMember', 'MfnAjaxController@ajaxSelectBoxLoadForMember');
Route::get('/ajaxSelectBoxForMemberDetails', 'MfnAjaxController@ajaxSelectBoxLoadForMemberDetails');
Route::get('/ajaxGetSysDate', 'MfnAjaxController@ajaxSystemCurrentDate');
Route::get('/ajaxSelectBoxForFieldOfficer', 'MfnAjaxController@ajaxSelectBoxLoadForFieldOfficer');
///////////////////////////////////---------- MFN ---------------////////////////////////////// ajax route end 
// ------------------------------Inventory------------------------//
Route::get('/ajaxSupplierNameLoadInv', 'AjaxController@ajaxSupplierNameLoadInv');
Route::get('/ajaxProductLPurchaseInv', 'AjaxController@ajaxProductLoadPurchaseInv');
Route::get('/ajaxOrderPurchaseProdLoadInv', 'AjaxController@getOrderPurchaseProdLoadInv');
Route::get('/ajaxCheckStockInv', 'AjaxController@ajaxStockQuantityInv');
Route::get('/ajaxGBillPRInv', 'AjaxController@ajaxGenerateBillPurchaseReturnInv');
Route::get('/ajaxGReqNoInv', 'AjaxController@ajaxGetRequisitionNoInv');
Route::get('/ajaxGBillIssueInv', 'AjaxController@ajaxGenerateBillIssueInv')->name('ajaxGBillIssueInv');
Route::get('/ajaxReqIssueProdLoadInv', 'AjaxController@getReqProductInIssueInv');
Route::get('/ajaxReqLoadIssueInv', 'AjaxController@getReqLoadIssueInv');
Route::get('/ajaxGBillIRInv', 'AjaxController@ajaxGenerateBillIssueReturnInv')->name('ajaxGBillIRInv');
Route::get('/ajaxGBillTransferInv', 'AjaxController@ajaxGenerateBillTransferInv');
Route::get('/ajaxProductLTransferInv', 'AjaxController@ajaxProductLoadTransferInv');

Route::get('/ajaxGBillUses', 'AjaxController@ajaxGBillUses');
Route::get('/ajaxGBillUR', 'AjaxController@ajaxGenerateBillUsessReturn');
Route::get('/ajaxProductLBills', 'AjaxController@ajaxProductLoadBills');
Route::get('/ajaxCustBillDetails', 'AjaxController@ajaxCustBillDetails');
Route::get('/ajaxBillCollectionForBills', 'AjaxController@ajaxBillCollectionForBills');
Route::get('/ajaxBillCollectionNo', 'AjaxController@ajaxBillCollectionNo');
Route::get('/ajaxAutoProcessBill', 'AjaxController@ajaxAutoProcessBill');
Route::get('/ajaxGBillForBill', 'AjaxController@ajaxGBillForBill');


Route::get('/ajaxEmpDesignationLoad', 'AjaxController@ajaxEmpDesignationLoad');
