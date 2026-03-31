<?php

use App\Http\Controllers\MFN\Loan\LoanTransactionController;
use App\Http\Controllers\MFN\Savings\DepositController;
use Illuminate\Http\Request;

Route::group(['prefix' => 'v1'], function () {

    Route::post('/login', 'ApiAuthController@login');

    Route::group(['middleware' => 'auth:api'], function () {

        Route::get('logout', 'ApiAuthController@logout');

        Route::group(['prefix' => 'mfn'], function () {

            Route::post('insertDepositLocalDataIntoLiveDB', function (Request $req) {

                $depositDataFromLocal = $req->data;
                $insertedIds          = array();

                foreach ($depositDataFromLocal as $key => $row) {

                    if (isset($req->context) && $req->context === 1) {
                        $request = new Request;
                        $request->merge(array(
                            'id'                => encrypt($row['liveId']),
                            'amount'            => $row['amount'],
                            'transactionTypeId' => $row['transactionTypeId'],
                            'ledgerId'          => $row['ledgerId'],
                            'chequeNo'          => $row['chequeNo'],
                            'updated_at'        => $row['updated_at'],
                            'updated_by'        => $row['updated_by'],
                            'is_delete'         => $row['is_delete'],
                        ));

                        $depositController = new DepositController;
                        $response          = $depositController->update($request)->getData();

                    } else {
                        if ($row['liveId'] == 0) {

                            $request = new Request;
                            $request->merge(array(
                                'accountId'         => $row['accountId'],
                                'memberId'          => $row['memberId'],
                                'samityId'          => $row['samityId'],
                                'branchId'          => $row['branchId'],
                                'primaryProductId'  => $row['primaryProductId'],
                                'savingsProductId'  => $row['savingsProductId'],
                                'amount'            => $row['amount'],
                                'date'              => $row['date'],
                                'transactionTypeId' => $row['transactionTypeId'],
                                'ledgerId'          => $row['ledgerId'],
                                'chequeNo'          => $row['chequeNo'],
                                'isAuthorized'      => $row['isAuthorized'],
                                'isFromAutoProcess' => $row['isFromAutoProcess'],
                                'created_at'        => $row['created_at'],
                                'updated_at'        => $row['updated_at'],
                                'created_by'        => $row['created_by'],
                                'updated_by'        => $row['updated_by'],
                                'is_delete'         => $row['is_delete'],
                            ));

                            $depositController = new DepositController;
                            $response          = $depositController->store($request)->getData();

                        } else {

                            //for update
                            if ($row['is_delete'] == 0) {

                                //for check if data is deleted from live database
                                if (DB::table('mfn_savings_deposit')->where('id', $row['liveId'])->where('is_delete', 1)->exists()) {
                                    $response = (object) array(
                                        'message'    => 'Deleted from live Database!!',
                                        'alert-type' => 'deleted',
                                        'deletedId'  => $row['liveId'],
                                    );

                                } else {
                                    $request = new Request;
                                    $request->merge(array(
                                        'id'                => encrypt($row['liveId']),
                                        'amount'            => $row['amount'],
                                        'transactionTypeId' => $row['transactionTypeId'],
                                        'ledgerId'          => $row['ledgerId'],
                                        'chequeNo'          => $row['chequeNo'],
                                        'updated_at'        => $row['updated_at'],
                                        'updated_by'        => $row['updated_by'],
                                    ));

                                    $depositController = new DepositController;
                                    $response          = $depositController->update($request)->getData();
                                }
                            }
                            //for delete
                            else {

                                $request = new Request;
                                $request->merge(array(
                                    'id' => encrypt($row['liveId']),
                                ));

                                $depositController = new DepositController;
                                $response          = $depositController->delete($request)->getData();
                            }
                        }
                    }

                    $insertedIds[$row['uniqueId']] = [
                        'alertType'      => $response->{'alert-type'},
                        'message'        => $response->message,
                        'liveInsertedId' => ($response->{'alert-type'} === 'success') ? (($row['liveId'] == 0) ? $response->depositId : $row['liveId']) : null,
                        'deletedData'    => ($response->{'alert-type'} === 'deleted') ? $row : null,
                    ];
                }

                return response()->json($insertedIds);
            });

            Route::post('insertLoanCollectionLocalDataIntoLiveDB', function (Request $req) {

                $loanCollectionDataFromLocal = $req->data;
                $insertedIds                 = array();

                foreach ($loanCollectionDataFromLocal as $key => $row) {

                    if (isset($req->context) && $req->context === 1) {
                        $request = new Request;
                        $request->merge(array(
                            'transactionType' => 'Regular',
                            'id'              => encrypt($row['liveId']),
                            'amount'          => $row['amount'],
                            'loanId'          => $row['loanId'],
                            'ledgerId'        => $row['ledgerId'],
                            'chequeNo'        => $row['chequeNo'],
                            'updated_at'      => $row['updated_at'],
                            'updated_by'      => $row['updated_by'],
                            'is_delete'       => $row['is_delete'],
                        ));

                        $loanTransactionController = new LoanTransactionController($request);
                        $response                  = $loanTransactionController->update($request)->getData();

                    } else {

                        if ($row['liveId'] == 0) {

                            $request = new Request;
                            $request->merge([
                                'transactionType'   => 'Regular',
                                'loanId'            => $row['loanId'],
                                'memberId'          => $row['memberId'],
                                'samityId'          => $row['samityId'],
                                'branchId'          => $row['branchId'],
                                'collectionDate'    => $row['collectionDate'],
                                'amount'            => $row['amount'],
                                'principalAmount'   => $row['principalAmount'],
                                'interestAmount'    => $row['interestAmount'],
                                'paymentType'       => $row['paymentType'],
                                'ledgerId'          => $row['ledgerId'],
                                'chequeNo'          => $row['chequeNo'],
                                'created_at'        => $row['created_at'],
                                'updated_at'        => $row['updated_at'],
                                'created_by'        => $row['created_by'],
                                'updated_by'        => $row['updated_by'],
                                'isAuthorized'      => $row['isAuthorized'],
                                'isFromAutoProcess' => $row['isFromAutoProcess'],
                                'is_delete'         => $row['is_delete'],
                            ]);

                            $loanTransactionController = new LoanTransactionController($request);
                            $response                  = $loanTransactionController->store($request)->getData();

                        } else {

                            if ($row['is_delete'] == 0) {

                                //for check if data is deleted from live database
                                if (DB::table('mfn_loan_collections')->where('id', $row['liveId'])->where('is_delete', 1)->exists()) {
                                    $response = (object) array(
                                        'message'    => 'Deleted from live Database!!',
                                        'alert-type' => 'deleted',
                                        'deletedId'  => $row['liveId'],
                                    );
                                }
                                //for update
                                else {
                                    $request = new Request;
                                    $request->merge([
                                        'transactionType' => 'Regular',
                                        'id'              => encrypt($row['liveId']),
                                        'loanId'          => $row['loanId'],
                                        'amount'          => $row['amount'],
                                        'paymentType'     => $row['paymentType'],
                                        'ledgerId'        => $row['ledgerId'],
                                        'chequeNo'        => $row['chequeNo'],
                                        'updated_at'      => $row['updated_at'],
                                        'updated_by'      => $row['updated_by'],
                                    ]);

                                    $loanTransactionController = new LoanTransactionController($request);
                                    $response                  = $loanTransactionController->update($request)->getData();
                                }
                            }
                            //for delete
                            else {
                                $request = new Request;
                                $request->merge([
                                    'transactionType' => 'Regular',
                                    'id'              => encrypt($row['liveId']),
                                ]);

                                $loanTransactionController = new LoanTransactionController($request);
                                $response                  = $loanTransactionController->delete($request)->getData();
                            }
                        }
                    }

                    $insertedIds[$row['uniqueId']] = [
                        'alertType'      => $response->{'alert-type'},
                        'message'        => $response->message,
                        'liveInsertedId' => ($response->{'alert-type'} === 'success') ? (($row['liveId'] == 0) ? $response->createId : $row['liveId']) : null,
                        'deletedData'    => ($response->{'alert-type'} === 'deleted') ? $row : null,
                    ];
                }

                return response()->json($insertedIds);
            });
        });

        // Route::get('getMfnBranchDatas', function (Request $req) {

        //     $samities = DB::table('mfn_samity')
        //         ->where('branchId', $req->branchId)
        //         ->get();

        //     $members = DB::table('mfn_members')
        //         ->where('branchId', $req->branchId)
        //         ->get();

        //     $accounts = DB::table('mfn_savings_accounts')
        //         ->where('branchId', $req->branchId)
        //         ->get();

        //     // $deposits = DB::table('mfn_savings_deposit')
        //     //     ->where('branchId', $req->branchId)
        //     //     ->get();

        //     // $withdraw = DB::table('mfn_savings_withdraw')
        //     //     ->where('branchId', $req->branchId)
        //     //     ->get();

        //     $memberBalance = MfnService::getSavingsAccountsBalance($req->branchId);
        //     $ledgerData    = AccService::getLedgerAccount($req->branchId, null, null, 5);

        //     $ledgerDataArr = array();
        //     foreach ($ledgerData as $key => $row) {
        //         $ledgerDataArr[$key]['id']    = $row->id;
        //         $ledgerDataArr[$key]['label'] = $row->name;
        //     }

        //     $loans = DB::table('mfn_loans')
        //         ->where('branchId', $req->branchId)
        //         ->get();

        //     $loanStatus = MfnService::getLoanStatus($loans->pluck('id')->all());

        //     $data = array(
        //         'samities'      => $samities,
        //         'members'       => $members,
        //         'accounts'      => $accounts,
        //         // 'deposits' => $deposits,
        //         // 'withdraw' => $withdraw,
        //         'memberBalance' => $memberBalance,
        //         'ledgerData'    => $ledgerDataArr,
        //         'loans'         => $loans,
        //         'loanStatus'    => $loanStatus,
        //     );

        //     return response()->json($data);
        // });

        // Route::group(['namespace' => 'MFN'], function () {

        //     Route::group(['namespace' => 'Savings'], function () {

        //         Route::post('depositAdd', 'DepositController@add');

        //         Route::delete('depositDelete', function (Request $req) {

        //             $requestArr['id'] = encrypt($req->id);

        //             $request = new Request;
        //             $request->merge($requestArr);

        //             $depositController = new DepositController;
        //             $response          = $depositController->delete($request)->getData();

        //             return response()->json($response);
        //         });

        //         Route::get('depositEdit', function (Request $req) {

        //             $depositData = DB::table('mfn_savings_deposit as msd')
        //                 ->where('msd.id', $req->id)
        //                 ->select('msd.id', 'msd.date', 'msd.amount', 'msd.transactionTypeId', 'msa.accountCode', DB::raw("CONCAT(mm.name, ' - ', mm.memberCode) as member"))
        //                 ->leftjoin('mfn_members as mm', 'msd.memberId', 'mm.id')
        //                 ->leftjoin('mfn_savings_accounts as msa', 'msd.samityId', 'msa.id')
        //                 ->first();

        //             return response()->json($depositData);
        //         });

        //         Route::get('getMemberSavingAccouts', function (Request $req) {

        //             $depositController = new DepositController;
        //             $savingAccounts    = $depositController->getData($req);
        //             $savingAccounts    = $savingAccounts->original['savAccounts'];

        //             $savingAccountsArr = array();

        //             foreach ($savingAccounts as $key => $value) {

        //                 $data = array();

        //                 $data['id']    = $key;
        //                 $data['label'] = $value;

        //                 array_push($savingAccountsArr, $data);
        //             }

        //             return response()->json($savingAccountsArr);
        //         });

        //         Route::get('getAccountDetails', 'DepositController@getData');

        //     });

        //     Route::get('/getMembers', function (Request $req) {

        //         $filter = array();

        //         if (isset($req->branchId)) {
        //             $filter['branchId'] = $req->branchId;
        //         }

        //         if (isset($req->samityId)) {
        //             $filter['samityId'] = $req->samityId;

        //         }

        //         if (isset($req->dateTo)) {
        //             $filter['dateTo'] = $req->dateTo;
        //         }

        //         $members = MfnService::getSelectizeMembers($filter);

        //         $membersArr = array();
        //         foreach ($members as $key => $row) {

        //             $membersArr[$key]['id']    = $row->id;
        //             $membersArr[$key]['label'] = $row->member;
        //         }

        //         return response()->json($membersArr);
        //     });

        //     Route::get('getSysDate', function (Request $req) {

        //         $sysDate = MfnService::systemCurrentDate($req->branchId);

        //         return response()->json(date('d-m-Y', strtotime($sysDate)));
        //     });

        //     Route::group(['namespace' => 'GConfig'], function () {

        //         Route::post('workingAreaAdd', 'WorkingAreaController@add');
        //     });

        //     Route::group(['namespace' => 'Others'], function () {

        //         Route::get('/getBranchsAndDivisions', function () {

        //             $branchs   = CommonController::getBranchs();
        //             $divisions = CommonController::getDivisions();

        //             return json_encode([
        //                 'branchs'   => $branchs,
        //                 'divisions' => $divisions,
        //             ]);
        //         });

        //         Route::get('getBranchs', 'CommonController@getBranchs');
        //         Route::get('getSamities', function (Request $req) {

        //             $commonController = new CommonController;
        //             $samities         = json_decode(json_encode($commonController->getSamities($req)))->original;

        //             $samitiesArr = array();

        //             foreach ($samities as $key => $row) {
        //                 $samitiesArr[$key]['id']    = $row->id;
        //                 $samitiesArr[$key]['label'] = $row->name;
        //             }

        //             return response()->json($samitiesArr);
        //         });

        //         Route::get('getDivisions', 'CommonController@getDivisions');
        //         Route::get('getDistricts', function (Request $req) {

        //             $divisions = CommonController::getDistricts($req);

        //             $divisionsArr = array();
        //             foreach ($divisions->original as $key => $row) {

        //                 $data          = array();
        //                 $data['id']    = $key;
        //                 $data['label'] = $row;

        //                 array_push($divisionsArr, $data);
        //             }

        //             return response()->json($divisionsArr);
        //         });

        //         Route::get('/getUpazilas', function (Request $req) {

        //             $upazilas = CommonController::getUpazilas($req);

        //             $upazilasArr = array();
        //             foreach ($upazilas->original as $key => $row) {

        //                 $data          = array();
        //                 $data['id']    = $key;
        //                 $data['label'] = $row;

        //                 array_push($upazilasArr, $data);
        //             }

        //             return response()->json($upazilasArr);
        //         });

        //         Route::get('/getUnions', function (Request $req) {

        //             $unions = CommonController::getUnions($req);

        //             $unionsArr = array();
        //             foreach ($unions->original as $key => $row) {

        //                 $data          = array();
        //                 $data['id']    = $key;
        //                 $data['label'] = $row;

        //                 array_push($unionsArr, $data);
        //             }

        //             return response()->json($unionsArr);
        //         });

        //         Route::get('/getVillages', function (Request $req) {

        //             $villages = CommonController::getVillages($req);

        //             $villagesArr = array();
        //             foreach ($villages->original as $key => $row) {

        //                 $data          = array();
        //                 $data['id']    = $key;
        //                 $data['label'] = $row;

        //                 array_push($villagesArr, $data);
        //             }

        //             return response()->json($villagesArr);
        //         });

        //         Route::get('getAccountStatement', 'CommonController@getAccountStatement');
        //     });
        // });

    });
});
