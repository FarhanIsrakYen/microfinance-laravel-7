<?php

namespace App\Http\Controllers\MFN\Process;

use App\Model\MFN\SavingsAccount;
use App\Services\AccService;
use DB;
use Illuminate\Http\Request;
use App\Services\MfnService;

class AutoVoucher
{
    private $components = null;

    public function mfnCreateAutoVoucher($branchId = null, $sysDate = null)
    {
        // $branchId = 10 ;
        // $sysDate='2020-07-01';

        $configLedger = DB::table('mfn_auto_voucher_config')->get();
        $date = date('Y-m-d', strtotime($sysDate));

        /* array index 0 = debit 1 credit 2 jurnal */
        $debit_arr[0] = array();
        $credit_arr[0] = array();
        $amount_arr[0] = array();
        $narration_arr[0] = array();
        $debit_arr[1] = array();
        $credit_arr[1] = array();
        $amount_arr[1] = array();
        $narration_arr[1] = array();
        $debit_arr[2] = array();
        $credit_arr[2] = array();
        $amount_arr[2] = array();
        $narration_arr[2] = array();
        /* array index 0 = debit 1 credit 2 jurnal */

        $components = DB::table('mfn_auto_voucher_components')
            ->where([
                ['is_delete', 0],
                ['status', 1],
            ])
            ->get();


        $loansData = DB::table('mfn_loans')
            ->join('mfn_loan_details', 'mfn_loan_details.loanId', 'mfn_loans.id')
            ->where([
                ['mfn_loans.is_delete', 0],
                ['mfn_loans.disbursementDate', $date],
                ['mfn_loans.branchId', $branchId],
            ])
            ->groupBy('productId')
            ->groupBy('ledgerId')
            ->select(DB::raw("productId, ledgerId, SUM(loanAmount) AS loanAmount, SUM(insuranceAmount) AS insuranceAmount, SUM(loanFormFee) AS loanFormFee, SUM(additionalFee) AS additionalFee"))
            ->get();


        $collections = DB::table('mfn_loan_collections AS lc')
            ->join('mfn_loans', 'mfn_loans.id', 'lc.loanId')
            ->where([
                ['lc.is_delete', 0],
                ['lc.collectionDate', $date],
                ['lc.branchId', $branchId],
            ])
            ->groupBy('mfn_loans.productId')
            ->groupBy('lc.ledgerId')
            ->groupBy('lc.paymentType')
            ->select(DB::raw("productId, ledgerId, paymentType, SUM(amount) AS amount, SUM(principalAmount) AS principalAmount, SUM(interestAmount) AS interestAmount"))
            ->get();

        $deposits = DB::table('mfn_savings_deposit')
            ->where([
                ['is_delete', 0],
                ['date', $date],
                ['branchId', $branchId],
            ])
            ->groupBy('primaryProductId')
            ->groupBy('savingsProductId')
            ->groupBy('ledgerId')
            ->groupBy('transactionTypeId')
            ->select(DB::raw("primaryProductId, savingsProductId, ledgerId, transactionTypeId, SUM(amount) AS amount"))
            ->get();

        $withdraws = DB::table('mfn_savings_withdraw')
            ->where([
                ['is_delete', 0],
                ['date', $date],
                ['branchId', $branchId],
            ])
            ->groupBy('primaryProductId')
            ->groupBy('savingsProductId')
            ->groupBy('ledgerId')
            ->groupBy('transactionTypeId')
            ->select(DB::raw("primaryProductId, savingsProductId, ledgerId, transactionTypeId, SUM(amount) AS amount"))
            ->get();
        // product transfer
        $productTransfers = DB::table('mfn_member_primary_product_transfers')
            ->where([
                ['is_delete', 0],
                ['transferDate', $date],
                ['branchId', $branchId],
            ])
            ->get();

        $provisions = DB::table('mfn_savings_provision_details AS spd')
            ->join('mfn_savings_provision as sp', 'sp.id', 'spd.provisionId')
            ->join('mfn_savings_accounts as sa', 'sa.id', 'spd.accountId')
            ->where([
                ['sp.is_delete', 0],
                ['sp.provisionDate', $date],
                ['spd.branchId', $branchId],
            ])
            // ->groupBy('productId')
            ->groupBy('spd.accountId')
            // ->groupBy('paymentType')
            ->select(DB::raw("SUM(provisionAmount) AS amount, spd.accountId , sa.memberId , sa.savingsProductId"))
            ->get();

        $memberPrimaryIds = MfnService::getMemberPrimaryProductId($provisions->pluck('memberId')->toArray(), $date);

        foreach ($provisions as $key => $provision) {
            $provisions[$key]->primaryProductId = $memberPrimaryIds[$provision->memberId];
        }


        foreach ($components as $key1 => $value) {
            # code...

            if ($value->id == 1) {

                // Loan Disbursements
                $loans = $loansData;

                $ledgerIds = $loans->unique('ledgerId')->pluck('ledgerId')->toArray();

                foreach ($ledgerIds as $ledgerId) {
                    $loanProductIds = $loans->where('ledgerId', $ledgerId)->unique('productId')->pluck('productId')->toArray();
                    foreach ($loanProductIds as $loanProductId) {
                        $amount = $loans->where('ledgerId', $ledgerId)->where('productId', $loanProductId)->sum('loanAmount');
                        $autoConfigLedger = $configLedger->where('componentId', $value->id)->where('loanProductId', $loanProductId);
                        if ($autoConfigLedger != null && $amount > 0) {
                            $Credit = (!empty($autoConfigLedger->where('headFor', 'Credit')->first())) ? $autoConfigLedger->where('headFor', 'Credit')->first()->principalLedgerId : null;
                            $Debit = (!empty($autoConfigLedger->where('headFor', 'Debit')->first())) ? $autoConfigLedger->where('headFor', 'Debit')->first()->principalLedgerId : null;


                            //array index 0 = debit \\ 1 = credit \\2  = jurnal
                            if ($value->voucherType == "Debit" && $Debit != null) {

                                array_push($credit_arr[0], $ledgerId);
                                array_push($debit_arr[0], $Debit);
                                array_push($amount_arr[0], $amount);
                                array_push($narration_arr[0], '');
                            } else if ($value->voucherType == "Credit" && $Credit != null) {
                                array_push($credit_arr[1], $Credit);
                                array_push($debit_arr[1], $ledgerId);
                                array_push($amount_arr[1], $amount);
                                array_push($narration_arr[1], '');
                            } else if ($value->voucherType == "Journal" && $Debit != null && $Credit != null) {
                                array_push($credit_arr[2], $Credit);
                                array_push($debit_arr[2], $Debit);
                                array_push($amount_arr[2], $amount);
                                array_push($narration_arr[2], '');
                            }
                        }
                    }
                }
            } else if ($value->id == 2) {

                $loanCollections = $collections->filter(function ($obj) {
                    return ($obj->paymentType == 'Cash' || $obj->paymentType == 'Bank');
                });


                $ledgerIds = $loanCollections->unique('ledgerId')->pluck('ledgerId');
                foreach ($ledgerIds as $ledgerId) {
                    $loanProductIds = $loanCollections->where('ledgerId', $ledgerId)->unique('productId')->pluck('productId');
                    foreach ($loanProductIds as $loanProductId) {
                        $principalAmount = $loanCollections->where('ledgerId', $ledgerId)->where('productId', $loanProductId)->sum('principalAmount');
                        $interestAmount = $loanCollections->where('ledgerId', $ledgerId)->where('productId', $loanProductId)->sum('interestAmount');

                        $autoConfigLedger = $configLedger->where('componentId', $value->id)->where('loanProductId', $loanProductId);

                        if ($autoConfigLedger != null && $principalAmount > 0) {

                            $Credit = (!empty($autoConfigLedger->where('headFor', 'Credit')->first())) ? $autoConfigLedger->where('headFor', 'Credit')->first()->principalLedgerId : null;
                            $Debit = (!empty($autoConfigLedger->where('headFor', 'Debit')->first())) ? $autoConfigLedger->where('headFor', 'Debit')->first()->principalLedgerId : null;

                            ///array index 0 = debit \\ 1 = credit \\2  = jurnal
                            if ($value->voucherType == "Debit" && $Debit != null) {

                                array_push($credit_arr[0], $ledgerId);
                                array_push($debit_arr[0], $Debit);
                                array_push($amount_arr[0], $principalAmount);
                                array_push($narration_arr[0], '');
                            } else if ($value->voucherType == "Credit" && $Credit != null) {
                                array_push($credit_arr[1], $Credit);
                                array_push($debit_arr[1], $ledgerId);
                                array_push($amount_arr[1], $principalAmount);
                                array_push($narration_arr[1], '');
                            } else if ($value->voucherType == "Journal" && $Debit != null && $Credit != null) {
                                array_push($credit_arr[2], $Credit);
                                array_push($debit_arr[2], $Debit);
                                array_push($amount_arr[2], $principalAmount);
                                array_push($narration_arr[2], '');
                            }
                        }
                        if ($autoConfigLedger != null && $interestAmount > 0) {

                            $Credit = (!empty($autoConfigLedger->where('headFor', 'Credit')->first())) ? $autoConfigLedger->where('headFor', 'Credit')->first()->interestLedgerId : null;
                            $Debit = (!empty($autoConfigLedger->where('headFor', 'Debit')->first())) ? $autoConfigLedger->where('headFor', 'Debit')->first()->interestLedgerId : null;

                            ///array index 0 = debit \\ 1 = credit \\2  = jurnal
                            if ($value->voucherType == "Debit" && $Debit != null) {

                                array_push($credit_arr[0], $ledgerId);
                                array_push($debit_arr[0], $Debit);
                                array_push($amount_arr[0], $interestAmount);
                                array_push($narration_arr[0], '');
                            } else if ($value->voucherType == "Credit" && $Credit != null) {

                                array_push($credit_arr[1], $Credit);
                                array_push($debit_arr[1], $ledgerId);
                                array_push($amount_arr[1], $interestAmount);
                                array_push($narration_arr[1], '');
                            } else if ($value->voucherType == "Journal" && $Debit != null && $Credit != null) {
                                array_push($credit_arr[2], $Credit);
                                array_push($debit_arr[2], $Debit);
                                array_push($amount_arr[2], $interestAmount);
                                array_push($narration_arr[2], '');
                            }
                        }
                    }
                }
            } else if ($value->id == 3) {

                $TransactionData = $collections->filter(function ($obj) {
                    return $obj->paymentType == 'Rebate';
                });

                $loanProductIds = $TransactionData->unique('productId')->pluck('productId')->toArray();
                foreach ($loanProductIds as $loanProductId) {
                    $principalAmount = $TransactionData->where('productId', $loanProductId)->sum('principalAmount');
                    $interestAmount = $TransactionData->where('productId', $loanProductId)->sum('interestAmount');

                    $autoConfigLedger = $configLedger->where('componentId', $value->id)->where('loanProductId', $loanProductId);

                    if ($autoConfigLedger != null && $principalAmount > 0) {

                        $Credit = (!empty($autoConfigLedger->where('headFor', 'Credit')->first())) ? $autoConfigLedger->where('headFor', 'Credit')->first()->principalLedgerId : null;
                        $Debit = (!empty($autoConfigLedger->where('headFor', 'Debit')->first())) ? $autoConfigLedger->where('headFor', 'Debit')->first()->principalLedgerId : null;

                        //array index 0 = debit \\ 1 = credit \\2  = jurnal
                        if ($value->voucherType == "Debit" && $Debit != null) {

                            array_push($credit_arr[0], $ledgerId);
                            array_push($debit_arr[0], $Debit);
                            array_push($amount_arr[0], $principalAmount);
                            array_push($narration_arr[0], '');
                        } else if ($value->voucherType == "Credit" && $Credit != null) {
                            array_push($credit_arr[1], $Credit);
                            array_push($debit_arr[1], $ledgerId);
                            array_push($amount_arr[1], $principalAmount);
                            array_push($narration_arr[1], '');
                        } else if ($value->voucherType == "Journal" && $Debit != null && $Credit != null) {
                            array_push($credit_arr[2], $Credit);
                            array_push($debit_arr[2], $Debit);
                            array_push($amount_arr[2], $principalAmount);
                            array_push($narration_arr[2], '');
                        }
                    }

                    if ($autoConfigLedger != null && $interestAmount > 0) {

                        $Credit = (!empty($autoConfigLedger->where('headFor', 'Credit')->first())) ? $autoConfigLedger->where('headFor', 'Credit')->first()->interestLedgerId : null;
                        $Debit = (!empty($autoConfigLedger->where('headFor', 'Debit')->first())) ? $autoConfigLedger->where('headFor', 'Debit')->first()->interestLedgerId : null;

                        //array index 0 = debit \\ 1 = credit \\2  = jurnal
                        if ($value->voucherType == "Debit" && $Debit != null) {
                            array_push($credit_arr[0], $ledgerId);
                            array_push($debit_arr[0], $Debit);
                            array_push($amount_arr[0], $interestAmount);
                            array_push($narration_arr[0], '');
                        } else if ($value->voucherType == "Credit" && $Credit != null) {
                            array_push($credit_arr[1], $Credit);
                            array_push($debit_arr[1], $ledgerId);
                            array_push($amount_arr[1], $interestAmount);
                            array_push($narration_arr[1], '');
                        } else if ($value->voucherType == "Journal" && $Debit != null && $Credit != null) {
                            array_push($credit_arr[2], $Credit);
                            array_push($debit_arr[2], $Debit);
                            array_push($amount_arr[2], $interestAmount);
                            array_push($narration_arr[2], '');
                        }
                    }
                }
            } else if ($value->id == 4) {

                $TransactionData = $collections->filter(function ($obj) {
                    return $obj->paymentType == 'WriteOff';
                });

                $loanProductIds = $TransactionData->unique('productId')->pluck('productId')->toArray();
                foreach ($loanProductIds as $loanProductId) {
                    $principalAmount = $TransactionData->where('productId', $loanProductId)->sum('principalAmount');
                    $interestAmount = $TransactionData->where('productId', $loanProductId)->sum('interestAmount');

                    $autoConfigLedger = $configLedger->where('componentId', $value->id)->where('loanProductId', $loanProductId);

                    if ($autoConfigLedger != null && $principalAmount > 0) {

                        $Credit = (!empty($autoConfigLedger->where('headFor', 'Credit')->first())) ? $autoConfigLedger->where('headFor', 'Credit')->first()->principalLedgerId : null;
                        $Debit = (!empty($autoConfigLedger->where('headFor', 'Debit')->first())) ? $autoConfigLedger->where('headFor', 'Debit')->first()->principalLedgerId : null;

                        //array index 0 = debit \\ 1 = credit \\2  = jurnal
                        if ($value->voucherType == "Debit" && $Debit != null) {

                            array_push($credit_arr[0], $ledgerId);
                            array_push($debit_arr[0], $Debit);
                            array_push($amount_arr[0], $principalAmount);
                            array_push($narration_arr[0], '');
                        } else if ($value->voucherType == "Credit" && $Credit != null) {
                            array_push($credit_arr[1], $Credit);
                            array_push($debit_arr[1], $ledgerId);
                            array_push($amount_arr[1], $principalAmount);
                            array_push($narration_arr[1], '');
                        } else if ($value->voucherType == "Journal" && $Debit != null && $Credit != null) {
                            array_push($credit_arr[2], $Credit);
                            array_push($debit_arr[2], $Debit);
                            array_push($amount_arr[2], $principalAmount);
                            array_push($narration_arr[2], '');
                        }
                    }

                    if ($autoConfigLedger != null && $interestAmount > 0) {

                        $Credit = (!empty($autoConfigLedger->where('headFor', 'Credit')->first())) ? $autoConfigLedger->where('headFor', 'Credit')->first()->interestLedgerId : null;
                        $Debit = (!empty($autoConfigLedger->where('headFor', 'Debit')->first())) ? $autoConfigLedger->where('headFor', 'Debit')->first()->interestLedgerId : null;

                        //array index 0 = debit \\ 1 = credit \\2  = jurnal
                        if ($value->voucherType == "Debit" && $Debit != null) {
                            array_push($credit_arr[0], $ledgerId);
                            array_push($debit_arr[0], $Debit);
                            array_push($amount_arr[0], $interestAmount);
                            array_push($narration_arr[0], '');
                        } else if ($value->voucherType == "Credit" && $Credit != null) {
                            array_push($credit_arr[1], $Credit);
                            array_push($debit_arr[1], $ledgerId);
                            array_push($amount_arr[1], $interestAmount);
                            array_push($narration_arr[1], '');
                        } else if ($value->voucherType == "Journal" && $Debit != null && $Credit != null) {
                            array_push($credit_arr[2], $Credit);
                            array_push($debit_arr[2], $Debit);
                            array_push($amount_arr[2], $interestAmount);
                            array_push($narration_arr[2], '');
                        }
                    }
                }
            } else if ($value->id == 5) {

                $TransactionData = $collections->filter(function ($obj) {
                    return $obj->paymentType == 'Waiver';
                });

                $loanProductIds = $TransactionData->unique('productId')->pluck('productId')->toArray();
                foreach ($loanProductIds as $loanProductId) {
                    $principalAmount = $TransactionData->where('productId', $loanProductId)->sum('principalAmount');
                    $interestAmount = $TransactionData->where('productId', $loanProductId)->sum('interestAmount');

                    $autoConfigLedger = $configLedger->where('componentId', $value->id)->where('loanProductId', $loanProductId);

                    if ($autoConfigLedger != null && $principalAmount > 0) {

                        $Credit = (!empty($autoConfigLedger->where('headFor', 'Credit')->first())) ? $autoConfigLedger->where('headFor', 'Credit')->first()->principalLedgerId : null;
                        $Debit = (!empty($autoConfigLedger->where('headFor', 'Debit')->first())) ? $autoConfigLedger->where('headFor', 'Debit')->first()->principalLedgerId : null;

                        //array index 0 = debit \\ 1 = credit \\2  = jurnal
                        if ($value->voucherType == "Debit" && $Debit != null) {

                            array_push($credit_arr[0], $ledgerId);
                            array_push($debit_arr[0], $Debit);
                            array_push($amount_arr[0], $principalAmount);
                            array_push($narration_arr[0], '');
                        } else if ($value->voucherType == "Credit" && $Credit != null) {
                            array_push($credit_arr[1], $Credit);
                            array_push($debit_arr[1], $ledgerId);
                            array_push($amount_arr[1], $principalAmount);
                            array_push($narration_arr[1], '');
                        } else if ($value->voucherType == "Journal" && $Debit != null && $Credit != null) {
                            array_push($credit_arr[2], $Credit);
                            array_push($debit_arr[2], $Debit);
                            array_push($amount_arr[2], $principalAmount);
                            array_push($narration_arr[2], '');
                        }
                    }

                    if ($autoConfigLedger != null && $interestAmount > 0) {

                        $Credit = (!empty($autoConfigLedger->where('headFor', 'Credit')->first())) ? $autoConfigLedger->where('headFor', 'Credit')->first()->interestLedgerId : null;
                        $Debit = (!empty($autoConfigLedger->where('headFor', 'Debit')->first())) ? $autoConfigLedger->where('headFor', 'Debit')->first()->interestLedgerId : null;

                        //array index 0 = debit \\ 1 = credit \\2  = jurnal
                        if ($value->voucherType == "Debit" && $Debit != null) {
                            array_push($credit_arr[0], $ledgerId);
                            array_push($debit_arr[0], $Debit);
                            array_push($amount_arr[0], $interestAmount);
                            array_push($narration_arr[0], '');
                        } else if ($value->voucherType == "Credit" && $Credit != null) {
                            array_push($credit_arr[1], $Credit);
                            array_push($debit_arr[1], $ledgerId);
                            array_push($amount_arr[1], $interestAmount);
                            array_push($narration_arr[1], '');
                        } else if ($value->voucherType == "Journal" && $Debit != null && $Credit != null) {
                            array_push($credit_arr[2], $Credit);
                            array_push($debit_arr[2], $Debit);
                            array_push($amount_arr[2], $interestAmount);
                            array_push($narration_arr[2], '');
                        }
                    }
                }
            } else if ($value->id == 6) {
                //Savings Diposite -

                $TransactionData = $deposits->filter(function ($obj) {
                    return $obj->transactionTypeId <= 2;
                });

                $ledgerIds = $TransactionData->unique('ledgerId')->pluck('ledgerId')->toArray();

                foreach ($ledgerIds as $ledgerId) {
                    // Primary Product Ids
                    $primaryProductIds = $TransactionData->where('ledgerId', $ledgerId)->unique('primaryProductId')->pluck('primaryProductId')->toArray();

                    foreach ($primaryProductIds as $primaryProductId) {

                        $savingsProductIds = $TransactionData->where('ledgerId', $ledgerId)->where('primaryProductId', $primaryProductId)->unique('savingsProductId')->pluck('savingsProductId')->toArray();

                        foreach ($savingsProductIds as $savingsProductId) {
                            $amount = $TransactionData->where('ledgerId', $ledgerId)->where('primaryProductId', $primaryProductId)->where('savingsProductId', $savingsProductId)->sum('amount');

                            // echo '<pre>';
                            // print_r($amount);
                            // echo '</pre>';
                            $autoConfigLedger = $configLedger->where('componentId', $value->id)->where('loanProductId', $primaryProductId)->where('savingsProductId', $savingsProductId);
                            // echo '<pre>';
                            //     print_r($autoConfigLedger);
                            //     echo '</pre>';
                            if ($autoConfigLedger != null && $amount > 0) {

                                $Credit = (!empty($autoConfigLedger->where('headFor', 'Credit')->first())) ? $autoConfigLedger->where('headFor', 'Credit')->first()->principalLedgerId : null;
                                $Debit = (!empty($autoConfigLedger->where('headFor', 'Debit')->first())) ? $autoConfigLedger->where('headFor', 'Debit')->first()->principalLedgerId : null;
                                // echo '<pre>';
                                // print_r($Credit);
                                // echo '</pre>';

                                //array index 0 = debit \\ 1 = credit \\2  = jurnal
                                if ($value->voucherType == "Debit" && $Debit != null) {

                                    array_push($credit_arr[0], $ledgerId);
                                    array_push($debit_arr[0], $Debit);
                                    array_push($amount_arr[0], $amount);
                                    array_push($narration_arr[0], '');
                                } else if ($value->voucherType == "Credit" && $Credit != null) {
                                    array_push($credit_arr[1], $Credit);
                                    array_push($debit_arr[1], $ledgerId);
                                    array_push($amount_arr[1], $amount);
                                    array_push($narration_arr[1], '');
                                } else if ($value->voucherType == "Journal" && $Debit != null && $Credit != null) {
                                    array_push($credit_arr[2], $Credit);
                                    array_push($debit_arr[2], $Debit);
                                    array_push($amount_arr[2], $amount);
                                    array_push($narration_arr[2], '');
                                }
                            }
                        }
                    }
                }
            } else if ($value->id == 7) {
                //Savings withdraw -

                $TransactionData = $withdraws->filter(function ($obj) {
                    return $obj->transactionTypeId <= 2 || $obj->transactionTypeId == 6 || $obj->transactionTypeId == 7;
                });
                $ledgerIds = $TransactionData->unique('ledgerId')->pluck('ledgerId')->toArray();

                foreach ($ledgerIds as $ledgerId) {
                    // Primary Product Ids
                    $primaryProductIds = $TransactionData->where('ledgerId', $ledgerId)->unique('primaryProductId')->pluck('primaryProductId')->toArray();

                    foreach ($primaryProductIds as $primaryProductId) {

                        $savingsProductIds = $TransactionData->where('ledgerId', $ledgerId)->where('primaryProductId', $primaryProductId)->unique('savingsProductId')->pluck('savingsProductId')->toArray();

                        foreach ($savingsProductIds as $savingsProductId) {
                            $amount = $TransactionData->where('ledgerId', $ledgerId)->where('primaryProductId', $primaryProductId)->where('savingsProductId', $savingsProductId)->sum('amount');

                            $autoConfigLedger = $configLedger->where('componentId', $value->id)->where('loanProductId', $primaryProductId)->where('savingsProductId', $savingsProductId);

                            if ($autoConfigLedger != null && $amount > 0) {

                                $Credit = (!empty($autoConfigLedger->where('headFor', 'Credit')->first())) ? $autoConfigLedger->where('headFor', 'Credit')->first()->principalLedgerId : null;
                                $Debit = (!empty($autoConfigLedger->where('headFor', 'Debit')->first())) ? $autoConfigLedger->where('headFor', 'Debit')->first()->principalLedgerId : null;

                                //array index 0 = debit \\ 1 = credit \\2  = jurnal
                                if ($value->voucherType == "Debit" && $Debit != null) {

                                    array_push($credit_arr[0], $ledgerId);
                                    array_push($debit_arr[0], $Debit);
                                    array_push($amount_arr[0], $amount);
                                    array_push($narration_arr[0], '');
                                } else if ($value->voucherType == "Credit" && $Credit != null) {
                                    array_push($credit_arr[1], $Credit);
                                    array_push($debit_arr[1], $ledgerId);
                                    array_push($amount_arr[1], $amount);
                                    array_push($narration_arr[1], '');
                                } else if ($value->voucherType == "Journal" && $Debit != null && $Credit != null) {
                                    array_push($credit_arr[2], $Credit);
                                    array_push($debit_arr[2], $Debit);
                                    array_push($amount_arr[2], $amount);
                                    array_push($narration_arr[2], '');
                                }
                            }
                        }
                    }
                }
            } else if ($value->id == 8) {
                //Savings interest -

                $TransactionData = $deposits->filter(function ($obj) {
                    return $obj->transactionTypeId == 3;
                });


                $ledgerIds = $TransactionData->unique('ledgerId')->pluck('ledgerId')->toArray();

                foreach ($ledgerIds as $ledgerId) {
                    // Primary Product Ids
                    $primaryProductIds = $TransactionData->where('ledgerId', $ledgerId)->unique('primaryProductId')->pluck('primaryProductId')->toArray();

                    foreach ($primaryProductIds as $primaryProductId) {

                        $savingsProductIds = $TransactionData->where('ledgerId', $ledgerId)->where('primaryProductId', $primaryProductId)->unique('savingsProductId')->pluck('savingsProductId')->toArray();

                        foreach ($savingsProductIds as $savingsProductId) {
                            $amount = $TransactionData->where('ledgerId', $ledgerId)->where('primaryProductId', $primaryProductId)->where('savingsProductId', $savingsProductId)->sum('amount');

                            $autoConfigLedger = $configLedger->where('componentId', $value->id)->where('loanProductId', $primaryProductId)->where('savingsProductId', $savingsProductId);

                            if ($autoConfigLedger != null && $amount > 0) {

                                $Credit = (!empty($autoConfigLedger->where('headFor', 'Credit')->first())) ? $autoConfigLedger->where('headFor', 'Credit')->first()->principalLedgerId : null;
                                $Debit = (!empty($autoConfigLedger->where('headFor', 'Debit')->first())) ? $autoConfigLedger->where('headFor', 'Debit')->first()->principalLedgerId : null;

                                //array index 0 = debit \\ 1 = credit \\2  = jurnal
                                if ($value->voucherType == "Debit" && $Debit != null) {

                                    array_push($credit_arr[0], $ledgerId);
                                    array_push($debit_arr[0], $Debit);
                                    array_push($amount_arr[0], $amount);
                                    array_push($narration_arr[0], '');
                                } else if ($value->voucherType == "Credit" && $Credit != null) {
                                    array_push($credit_arr[1], $Credit);
                                    array_push($debit_arr[1], $ledgerId);
                                    array_push($amount_arr[1], $amount);
                                    array_push($narration_arr[1], '');
                                } else if ($value->voucherType == "Journal" && $Debit != null && $Credit != null) {
                                    array_push($credit_arr[2], $Credit);
                                    array_push($debit_arr[2], $Debit);
                                    array_push($amount_arr[2], $amount);
                                    array_push($narration_arr[2], '');
                                }
                            }
                        }
                    }
                }
            } else if ($value->id == 9) {
                //Savings interest provision -


                $TransactionData = $provisions;

                $primaryProductIds = $TransactionData->unique('primaryProductId')->pluck('primaryProductId')->toArray();
                foreach ($primaryProductIds as $primaryProductId) {

                    $savingsProductIds = $TransactionData->where('primaryProductId', $primaryProductId)->unique('savingsProductId')->pluck('savingsProductId')->toArray();

                    foreach ($savingsProductIds as $savingsProductId) {
                        $amount = $TransactionData->where('primaryProductId', $primaryProductId)->where('savingsProductId', $savingsProductId)->sum('amount');


                        $configDebit = $configLedger->where('componentId', $value->id)->where('headFor', 'Debit')->where('loanProductId', $primaryProductId)->where('savingsProductId',  $savingsProductId)->first();
                        $configCredit = $configLedger->where('componentId', $value->id)->where('headFor', 'Credit')->where('loanProductId', $primaryProductId)->where('savingsProductId',  $savingsProductId)->first();

                        if ($configDebit != null && $configCredit != null && $amount > 0) {

                            $headforDebit = $configDebit->principalLedgerId;
                            $headforCredit = $configCredit->principalLedgerId;

                            if ($value->voucherType == "Debit" && $headforDebit != null && $headforCredit != null) {

                                array_push($credit_arr[0], $headforCredit);
                                array_push($debit_arr[0], $headforDebit);
                                array_push($amount_arr[0], $amount);
                                array_push($narration_arr[0], '');
                            } else if ($value->voucherType == "Credit" && $headforDebit != null && $headforCredit != null) {
                                array_push($credit_arr[1], $headforCredit);
                                array_push($debit_arr[1], $headforDebit);
                                array_push($amount_arr[1], $amount);
                                array_push($narration_arr[1], '');
                            } else if ($value->voucherType == "Journal" && $headforDebit != null && $headforCredit != null) {
                                array_push($credit_arr[2], $headforCredit);
                                array_push($debit_arr[2], $headforDebit);
                                array_push($amount_arr[2], $amount);
                                array_push($narration_arr[2], '');
                            }
                        }
                    }
                }
            } else if ($value->id == 10) {
                //product Transfer

                $infos = array();

                foreach ($productTransfers as $productTransfer) {
                    $savingsDetails = json_decode($productTransfer->transferData);
                    foreach ($savingsDetails as $savingsDetail) {

                        $savingsProductID = SavingsAccount::where('id', $savingsDetail->id)->first()->savingsProductId;

                        $temp = array(
                            'from' => $productTransfer->oldProductId,
                            'to' => $productTransfer->newProductId,
                            'savingsProductId' => $savingsProductID,
                            'amount' => $savingsDetail->amt,
                        );

                        $mathedFlag = 0;
                        foreach ($infos as $key => $info) {
                            if ($info['from'] == $temp['from'] && $info['to'] == $temp['to'] && $info['savingsProductId'] == $temp['savingsProductId']) {
                                $infos[$key]['amount'] = $infos[$key]['amount'] + $temp['amount'];
                                $mathedFlag = 1;
                            }
                        }
                        if ($mathedFlag == 0) {
                            array_push($infos, $temp);
                        }
                    }
                }

                foreach ($infos as $info) {

                    $configDebit = $configLedger->where('componentId', $value->id)->where('headFor', 'Debit')->where('loanProductId', $info['from'])->where('savingsProductId', $info['savingsProductId'])->first();
                    $configCredit = $configLedger->where('componentId', $value->id)->where('headFor', 'Credit')->where('loanProductId', $info['to'])->where('savingsProductId', $info['savingsProductId'])->first();

                    if ($configDebit != null && $configCredit != null && $info['amount'] > 0) {

                        $headforDebit = $configDebit->principalLedgerId;
                        $headforCredit = $configCredit->principalLedgerId;

                        if ($value->voucherType == "Debit" && $headforDebit != null && $headforCredit != null) {

                            array_push($credit_arr[0], $headforCredit);
                            array_push($debit_arr[0], $headforDebit);
                            array_push($amount_arr[0], $info['amount']);
                            array_push($narration_arr[0], '');
                        } else if ($value->voucherType == "Credit" && $headforDebit != null && $headforCredit != null) {
                            array_push($credit_arr[1], $headforCredit);
                            array_push($debit_arr[1], $headforDebit);
                            array_push($amount_arr[1], $info['amount']);
                            array_push($narration_arr[1], '');
                        } else if ($value->voucherType == "Journal" && $headforDebit != null && $headforCredit != null) {
                            array_push($credit_arr[2], $headforCredit);
                            array_push($debit_arr[2], $headforDebit);
                            array_push($amount_arr[2], $info['amount']);
                            array_push($narration_arr[2], '');
                        }
                    }
                }
            } elseif ($value->id == 11) {

                // Loan insuranceAmount
                $loans = $loansData;

                $ledgerIds = $loans->unique('ledgerId')->pluck('ledgerId')->toArray();

                foreach ($ledgerIds as $ledgerId) {
                    $loanProductIds = $loans->where('ledgerId', $ledgerId)->unique('productId')->pluck('productId')->toArray();
                    foreach ($loanProductIds as $loanProductId) {
                        $amount = $loans->where('ledgerId', $ledgerId)->where('productId', $loanProductId)->sum('insuranceAmount');
                        $autoConfigLedger = $configLedger->where('componentId', $value->id)->where('loanProductId', $loanProductId);
                        if ($autoConfigLedger != null && $amount > 0) {
                            $Credit = (!empty($autoConfigLedger->where('headFor', 'Credit')->first())) ? $autoConfigLedger->where('headFor', 'Credit')->first()->principalLedgerId : null;
                            $Debit = (!empty($autoConfigLedger->where('headFor', 'Debit')->first())) ? $autoConfigLedger->where('headFor', 'Debit')->first()->principalLedgerId : null;


                            //array index 0 = debit \\ 1 = credit \\2  = jurnal
                            if ($value->voucherType == "Debit" && $Debit != null) {

                                array_push($credit_arr[0], $ledgerId);
                                array_push($debit_arr[0], $Debit);
                                array_push($amount_arr[0], $amount);
                                array_push($narration_arr[0], '');
                            } else if ($value->voucherType == "Credit" && $Credit != null) {
                                array_push($credit_arr[1], $Credit);
                                array_push($debit_arr[1], $ledgerId);
                                array_push($amount_arr[1], $amount);
                                array_push($narration_arr[1], '');
                            } else if ($value->voucherType == "Journal" && $Debit != null && $Credit != null) {
                                array_push($credit_arr[2], $Credit);
                                array_push($debit_arr[2], $Debit);
                                array_push($amount_arr[2], $amount);
                                array_push($narration_arr[2], '');
                            }
                        }
                    }
                }
            } elseif ($value->id == 12) {

                // loanFormFee
                $loans = $loansData;

                $ledgerIds = $loans->unique('ledgerId')->pluck('ledgerId')->toArray();

                foreach ($ledgerIds as $ledgerId) {
                    $loanProductIds = $loans->where('ledgerId', $ledgerId)->unique('productId')->pluck('productId')->toArray();
                    foreach ($loanProductIds as $loanProductId) {
                        $amount = $loans->where('ledgerId', $ledgerId)->where('productId', $loanProductId)->sum('loanFormFee');
                        $autoConfigLedger = $configLedger->where('componentId', $value->id)->where('loanProductId', $loanProductId);
                        if ($autoConfigLedger != null && $amount > 0) {
                            $Credit = (!empty($autoConfigLedger->where('headFor', 'Credit')->first())) ? $autoConfigLedger->where('headFor', 'Credit')->first()->principalLedgerId : null;
                            $Debit = (!empty($autoConfigLedger->where('headFor', 'Debit')->first())) ? $autoConfigLedger->where('headFor', 'Debit')->first()->principalLedgerId : null;


                            //array index 0 = debit \\ 1 = credit \\2  = jurnal
                            if ($value->voucherType == "Debit" && $Debit != null) {

                                array_push($credit_arr[0], $ledgerId);
                                array_push($debit_arr[0], $Debit);
                                array_push($amount_arr[0], $amount);
                                array_push($narration_arr[0], '');
                            } else if ($value->voucherType == "Credit" && $Credit != null) {
                                array_push($credit_arr[1], $Credit);
                                array_push($debit_arr[1], $ledgerId);
                                array_push($amount_arr[1], $amount);
                                array_push($narration_arr[1], '');
                            } else if ($value->voucherType == "Journal" && $Debit != null && $Credit != null) {
                                array_push($credit_arr[2], $Credit);
                                array_push($debit_arr[2], $Debit);
                                array_push($amount_arr[2], $amount);
                                array_push($narration_arr[2], '');
                            }
                        }
                    }
                }
            } elseif ($value->id == 13) {

                // Loan Additional Fee
                $loans = $loansData;

                $ledgerIds = $loans->unique('ledgerId')->pluck('ledgerId')->toArray();

                foreach ($ledgerIds as $ledgerId) {
                    $loanProductIds = $loans->where('ledgerId', $ledgerId)->unique('productId')->pluck('productId')->toArray();
                    foreach ($loanProductIds as $loanProductId) {
                        $amount = $loans->where('ledgerId', $ledgerId)->where('productId', $loanProductId)->sum('additionalFee');
                        $autoConfigLedger = $configLedger->where('componentId', $value->id)->where('loanProductId', $loanProductId);
                        if ($autoConfigLedger != null && $amount > 0) {
                            $Credit = (!empty($autoConfigLedger->where('headFor', 'Credit')->first())) ? $autoConfigLedger->where('headFor', 'Credit')->first()->principalLedgerId : null;
                            $Debit = (!empty($autoConfigLedger->where('headFor', 'Debit')->first())) ? $autoConfigLedger->where('headFor', 'Debit')->first()->principalLedgerId : null;


                            //array index 0 = debit \\ 1 = credit \\2  = jurnal
                            if ($value->voucherType == "Debit" && $Debit != null) {

                                array_push($credit_arr[0], $ledgerId);
                                array_push($debit_arr[0], $Debit);
                                array_push($amount_arr[0], $amount);
                                array_push($narration_arr[0], '');
                            } else if ($value->voucherType == "Credit" && $Credit != null) {
                                array_push($credit_arr[1], $Credit);
                                array_push($debit_arr[1], $ledgerId);
                                array_push($amount_arr[1], $amount);
                                array_push($narration_arr[1], '');
                            } else if ($value->voucherType == "Journal" && $Debit != null && $Credit != null) {
                                array_push($credit_arr[2], $Credit);
                                array_push($debit_arr[2], $Debit);
                                array_push($amount_arr[2], $amount);
                                array_push($narration_arr[2], '');
                            }
                        }
                    }
                }
            }
        }

        $BranchData = DB::table('gnl_branchs')
            ->where([
                ['is_delete', 0],
                ['id', $branchId],
            ])
            ->first();

        $req_debit = new Request;
        $req_debit->merge([
            'branch_id' => $branchId,
            'module_id' => 5, // 5 is for micro finance
            'company_id' => $BranchData->company_id,
            'voucher_type_id' => 1, // debit 1 credit 2 journal 3
            'voucher_status' => 1,
            'project_id' => $BranchData->project_id,
            'project_type_id' => $BranchData->project_type_id,
            'v_generate_type' => 1, // 1 auto voucher
            'voucher_date' => $date,
            'global_narration' => '',
            'debit_arr' => $debit_arr[0],
            'credit_arr' => $credit_arr[0],
            'amount_arr' => $amount_arr[0],
            'narration_arr' => $narration_arr[0],

        ]);

        $req_credit = new Request;
        $req_credit->merge([
            'branch_id' => $branchId,
            'module_id' => 5, // 5 is for micro finance
            'company_id' => $BranchData->company_id,
            'voucher_type_id' => 2, // debit 1 credit 2 journal 3
            'voucher_status' => 1,
            'project_id' => $BranchData->project_id,
            'project_type_id' => $BranchData->project_type_id,
            'v_generate_type' => 1, // 1 auto voucher
            'voucher_date' => $date,
            'global_narration' => '',
            'debit_arr' => $debit_arr[1],
            'credit_arr' => $credit_arr[1],
            'amount_arr' => $amount_arr[1],
            'narration_arr' => $narration_arr[1],

        ]);

        $req_Jurnal = new Request;
        $req_Jurnal->merge([
            'branch_id' => $branchId,
            'module_id' => 5, // 5 is for micro finance
            'company_id' => $BranchData->company_id,
            'voucher_type_id' => 3, // debit 1 credit 2 journal 3
            'voucher_status' => 1,
            'project_id' => $BranchData->project_id,
            'project_type_id' => $BranchData->project_type_id,
            'v_generate_type' => 1, // 1 auto voucher
            'voucher_date' => $date,
            'global_narration' => '',
            'debit_arr' => $debit_arr[2],
            'credit_arr' => $credit_arr[2],
            'amount_arr' => $amount_arr[2],
            'narration_arr' => $narration_arr[2],

        ]);

        $insertFlag = true;
        $errorMsg = null;

        try {

            $insert = AccService::insertVouchermfn($req_debit);

            if ($insert->getData()->{'alert-type'} == 'error') {
                $insertFlag = false;
                $errorMsg = $insert->getData();
            }

            $insert = AccService::insertVouchermfn($req_credit);

            if ($insert->getData()->{'alert-type'} == 'error') {
                $insertFlag = false;
                $errorMsg = $insert->getData();
            }

            $insert = AccService::insertVouchermfn($req_Jurnal);

            if ($insert->getData()->{'alert-type'} == 'error') {
                $insertFlag = false;
                $errorMsg = $insert->getData();
            }

            if ($insertFlag) {
                return response()->json($insert->getData());
            } else {
                return response()->json($errorMsg);
            }
        } catch (\Throwable $e) {
            //throw $th;
            $notification = array(
                'message' => 'Unsuccessful to inserted Voucher',
                'alert-type' => 'error',
                'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
            );

            return response()->json($notification);
        }
    }
}
