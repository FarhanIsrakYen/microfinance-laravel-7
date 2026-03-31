<?php

namespace App\Http\Controllers\MFN\Process;

use App\Model\MFN\MonthEndSummarySavings;
use App\Services\MfnService;
use DB;

class MonthEndSummary
{
    private static $branchId       = null;
    private static $monthStartDate = null;
    private static $monthEndDate   = null;

    /**
     * This function stores
     *
     * @param   [type]  $branchId   [id of the branch]
     * @param   [type]  $yearMonth  [format shoulld be 'Y-m' (year-month)]
     *
     * @return  [boolean]              [return true or false]
     */
    public static function store($branchId, $yearMonth)
    {
        $isYearMonthValid = date('Y-m', strtotime($yearMonth . '-01')) == $yearMonth ? true : false;

        if (!is_numeric($branchId) || !$isYearMonthValid) {
            return false;
        }

        self::$branchId       = $branchId;
        self::$monthStartDate = date('Y-m', strtotime($yearMonth . '-01'));
        self::$monthEndDate   = date('Y-m-t', strtotime(self::$monthStartDate));

        self::storeSavingsSummary();
    }

    public static function storeSavingsSummary()
    {
        $deposits = DB::table('mfn_savings_deposit AS deposit')
            ->join('mfn_members AS member', 'member.id', 'deposit.memberId')
            ->where([
                ['deposit.is_delete', 0],
                ['deposit.date', '>=', self::$monthStartDate],
                ['deposit.date', '>=', self::$monthEndDate],
                ['deposit.branchId', '>=', self::$branchId],
            ])
            ->groupBy('deposit.primaryProductId')
            ->groupBy('deposit.savingsProductId')
            ->groupBy('member.gender')
            ->groupBy('deposit.transactionTypeId')
            ->select(DB::raw("deposit.primaryProductId, deposit.savingsProductId, member.gender, deposit.transactionTypeId, SUM(amount) AS amount"))
            ->get();

        $withdraws = DB::table('mfn_savings_withdraw AS withdraw')
            ->join('mfn_members AS member', 'member.id', 'withdraw.memberId')
            ->where([
                ['withdraw.is_delete', 0],
                ['withdraw.date', '>=', self::$monthStartDate],
                ['withdraw.date', '>=', self::$monthEndDate],
                ['withdraw.branchId', '>=', self::$branchId],
            ])
            ->groupBy('withdraw.primaryProductId')
            ->groupBy('withdraw.savingsProductId')
            ->groupBy('member.gender')
            ->groupBy('withdraw.transactionTypeId')
            ->select(DB::raw("withdraw.primaryProductId, withdraw.savingsProductId, member.gender, withdraw.transactionTypeId, SUM(amount) AS amount"))
            ->get();

        $openingSummary = DB::table('mfn_month_end_savings')
            ->where([
                ['branchId', self::$branchId],
                ['date', date('Y-m-d', strtotime('-1 day', strtotime(self::$monthStartDate)))],
            ])
            ->get();

        $primaryProductIds = array_unique(array_merge($openingSummary->pluck('loanProductId')->toArray(), $deposits->pluck('primaryProductId')->toArray(), $withdraws->pluck('primaryProductId')->toArray()));

        $savingsProductIds = array_unique(array_merge($openingSummary->pluck('savingsProductId')->toArray(), $deposits->pluck('savingsProductId')->toArray(), $withdraws->pluck('savingsProductId')->toArray()));

        $genders = ['Male', 'Female'];

        DB::beginTransaction();

        try {
            foreach ($primaryProductIds as $primaryProductId) {
                foreach ($savingsProductIds as $savingsProductId) {
                    foreach ($genders as $gender) {

                        $openingBalance = $openingSummary
                            ->where('loanProductId', $primaryProductId)
                            ->where('savingsProductId', $savingsProductId)
                            ->where('gender', $gender)
                            ->sum('closingBalance');

                        // real deposit
                        $deposit = $deposits
                            ->where('primaryProductId', $primaryProductId)
                            ->where('savingsProductId', $savingsProductId)
                            ->where('gender', $gender)
                            ->where('transactionTypeId', '<=', 5)
                            ->sum('amount');

                        // deposit by product transfer
                        $deposit_pt = $deposits
                            ->where('primaryProductId', $primaryProductId)
                            ->where('savingsProductId', $savingsProductId)
                            ->where('gender', $gender)
                            ->where('transactionTypeId', 8)
                            ->sum('amount');

                        // real withdraw
                        $withdraw = $withdraws
                            ->where('primaryProductId', $primaryProductId)
                            ->where('savingsProductId', $savingsProductId)
                            ->where('gender', $gender)
                            ->where('transactionTypeId', '<=', 5)
                            ->sum('amount');

                        // withdraw by product transfer
                        $withdraw_pt = $withdraws
                            ->where('primaryProductId', $primaryProductId)
                            ->where('savingsProductId', $savingsProductId)
                            ->where('gender', $gender)
                            ->where('transactionTypeId', 8)
                            ->sum('amount');

                        // withdraw by loan adjustment
                        $withdraw_adjustment = $withdraws
                            ->where('primaryProductId', $primaryProductId)
                            ->where('savingsProductId', $savingsProductId)
                            ->where('gender', $gender)
                            ->where('transactionTypeId', 10)
                            ->sum('amount');

                        $closingBalance = $openingBalance + $deposit + $deposit_pt - $withdraw - $withdraw_adjustment;


                        $a = MonthEndSummarySavings::updateOrCreate(
                            [
                                'date'             => self::$monthEndDate,
                                'branchId'         => self::$branchId,
                                'loanProductId'    => $primaryProductId,
                                'savingsProductId' => $savingsProductId,
                                'gender'           => $gender,
                            ],
                            [
                                'openingBalance'      => $openingBalance,
                                'deposit'             => $deposit,
                                'deposit_pt'          => $deposit_pt,
                                'withdraw'            => $withdraw,
                                'withdraw_pt'         => $withdraw_pt,
                                'withdraw_adjustment' => $withdraw_adjustment,
                                'closingBalance'      => $closingBalance,
                            ]
                        );
                    }
                }
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }

    public static function storeMonthEndSummary($branchId, $yearMonth)
    {
        $isYearMonthValid = date('Y-m-d', strtotime($yearMonth)) == $yearMonth ? true : false;

        if (!is_numeric($branchId) || !$isYearMonthValid) {
            return false;
        }

        self::$branchId       = $branchId;
        self::$monthStartDate = date('Y-m', strtotime($yearMonth)) . '-01';
        self::$monthEndDate   = date('Y-m-t', strtotime(self::$monthStartDate));

        $openingSummary = DB::table('mfn_month_end_loans')
            ->where([
                ['branchId', self::$branchId],
                ['date', date('Y-m-d', strtotime('-1 day', strtotime(self::$monthStartDate)))],
            ])
            ->get();

        $loanSummary = DB::table('mfn_loans as ml')
            ->where([
                ['ml.is_delete', 0],
                ['ml.branchId', self::$branchId],
            ])
            ->whereBetween('mlc.collectionDate', [self::$monthStartDate, self::$monthEndDate])
            ->leftjoin('mfn_loan_collections as mlc', 'ml.id', 'mlc.loanId')
            ->leftjoin('mfn_loan_products as mlp', 'ml.productId', 'mlp.id')
            ->leftjoin('mfn_members as m', 'm.id', 'mlc.memberId')
            ->get();

        $products = array_unique(array_merge($loanSummary->pluck('productId')->toArray(), $openingSummary->pluck('productId')->toArray()));

        $genders = ['Male', 'Female'];

        DB::beginTransaction();

        try {
            foreach ($products as $product) {
                foreach ($genders as $gender) {

                    if (is_null($openingSummary)) {

                        $openingBorrowerNo           = 0;
                        $openingOutstanding          = 0;
                        $openingOutstandingPrincipal = 0;
                        $openingDue                  = 0;
                        $openingDuePrincipal         = 0;

                    } else {
                        $openingBorrowerNo = $openingSummary
                            ->where('productId', $product)
                            ->where('gender', $gender)
                            ->sum('closingBorrowerNo');

                        $openingOutstanding = $openingSummary
                            ->where('productId', $product)
                            ->where('gender', $gender)
                            ->sum('closingOutstanding');

                        $openingOutstandingPrincipal = $openingSummary
                            ->where('productId', $product)
                            ->where('gender', $gender)
                            ->sum('closingOutstandingPrincipal');

                        $openingDue = $openingSummary
                            ->where('productId', $product)
                            ->where('gender', $gender)
                            ->sum('closingDue');

                        $openingDuePrincipal = $openingSummary
                            ->where('productId', $product)
                            ->where('gender', $gender)
                            ->sum('closingDuePrincipal');
                    }

                    $disburseAmount = $loanSummary
                        ->where('productId', $product)
                        ->where('gender', $gender)
                        ->sum('loanAmount');

                    $collectionAmount = $loanSummary
                        ->where('productId', $product)
                        ->where('gender', $gender)
                        ->sum('amount');

                    $collectionAmountPrincipal = $loanSummary
                        ->where('productId', $product)
                        ->where('gender', $gender)
                        ->sum('principalAmount');

                    $writeOffAmount = $loanSummary
                        ->where('productId', $product)
                        ->where('gender', $gender)
                        ->where('paymentType', 'WriteOff')
                        ->SUM('amount');

                    $writeOffAmountPrincipal = $loanSummary
                        ->where('productId', $product)
                        ->where('gender', $gender)
                        ->where('paymentType', 'WriteOff')
                        ->SUM('principalAmount');

                    $waiverAmount = $loanSummary
                        ->where('productId', $product)
                        ->where('gender', $gender)
                        ->where('paymentType', 'Waiver')
                        ->SUM('amount');

                    $waiverAmountPrincipal = $loanSummary
                        ->where('productId', $product)
                        ->where('gender', $gender)
                        ->where('paymentType', 'Waiver')
                        ->SUM('principalAmount');

                    $rebateAmount = $loanSummary
                        ->where('productId', $product)
                        ->where('gender', $gender)
                        ->where('paymentType', 'Rebate')
                        ->SUM('amount');

                    //get borrowers member Id, according to product & gender
                    $borrowers = array_unique($loanSummary
                            ->where('productId', $product)
                            ->where('gender', $gender)
                            ->whereBetween('disbursementDate', [self::$monthStartDate, self::$monthEndDate])
                            ->pluck('memberId')
                            ->toArray());

                    //calculate total borrower number, according to product & gender
                    $borrowerNo = 0;
                    foreach ($borrowers as $row) {

                        $isBorrower = DB::table('mfn_loans')
                            ->where([
                                ['branchId', self::$branchId],
                                ['memberId', $row],
                                ['disbursementDate', '<=', date('Y-m-d', strtotime('-1 day', strtotime(self::$monthStartDate)))],
                            ])
                            ->where(function ($query) {
                                $query->where('loanCompleteDate', '0000-00-00')
                                    ->orWhere('loanCompleteDate', '>', self::$monthEndDate);
                            })
                            ->get();

                        if (!empty($isBorrower)) {
                            $borrowerNo++;
                        }
                    }

                    //get fullyBorrowers memberId, according to product & gender
                    $fullyBorrowers = array_unique($loanSummary
                            ->where('productId', $product)
                            ->where('gender', $gender)
                            ->whereBetween('loanCompleteDate', [self::$monthStartDate, self::$monthEndDate])
                            ->pluck('memberId')
                            ->toArray());

                    //get fullyPaidBorrowers number, according to product & gender
                    $fullyPaidBorrowerNo = 0;
                    foreach ($fullyBorrowers as $row) {

                        $isFullyBorrower = DB::table('mfn_loans')
                            ->where([
                                ['branchId', self::$branchId],
                                ['memberId', $row],
                            ])
                            ->where(function ($query) {
                                $query->where('loanCompleteDate', '!=', '0000-00-00')
                                    ->orWhere('loanCompleteDate', '>', self::$monthEndDate);
                            })
                            ->get();

                        if (!empty($isFullyBorrower)) {
                            $fullyPaidBorrowerNo++;
                        }
                    }

                    $closingBorrowerNo = $openingBorrowerNo + $borrowerNo;

                    //get unique loanIds
                    $loanIds = array_unique($loanSummary
                            ->where('productId', $product)
                            ->where('gender', $gender)
                            ->pluck('loanId')
                            ->toArray());

                    //get loan status for multiple loanIds
                    $loanStatus = MfnService::getLoanStatus($loanIds, self::$monthStartDate, self::$monthEndDate);
                    
                    //declatre varriable for which needs to sum from loanStatus
                    $closingOutstanding          = 0;
                    $closingOutstandingPrincipal = 0;
                    $closingDue                  = 0;
                    $closingDuePrincipal         = 0;
                    $newDue                      = 0;
                    $newDuePrincipal             = 0;
                    $totalDueLoanee              = 0;
                    $dueCollection               = 0;
                    $dueCollectionPrincipal      = 0;
                    $regularCollection           = 0;
                    $regularCollectionPrincipal  = 0;
                    $advanceCollection           = 0;
                    $advanceCollectionPrincipal  = 0;
                    $standardOutstanding         = 0;

                    //run loan status loop for sum declared varriable
                    foreach ($loanStatus as $row) {
                        
                        //sum declared varriable
                        $closingOutstanding += $row['outstanding'];
                        $closingOutstandingPrincipal += $row['outstandingPrincipal'];
                        $closingDue += $row['dueAmount'];
                        $closingDuePrincipal += $row['dueAmountPrincipal'];
                        $newDue += $row['onPeriodDueAmount'];
                        $newDuePrincipal += $row['onPeriodDueAmountPrincipal'];
                        $dueCollection += $row['onPeriodDueCollection'];
                        $dueCollectionPrincipal += $row['onPeriodDueCollectionPrincipal'];
                        $regularCollection += $row['onPeriodReularCollection'];
                        $regularCollectionPrincipal += $row['onPeriodReularCollectionPrincipal'];
                        $advanceCollection += $row['onPeriodAdvanceCollection'];
                        $advanceCollectionPrincipal += $row['onPeriodAdvanceCollectionPrincipal'];
                        
                        //if loan have due than get the all distinct member for totalDueLoanee
                        if ($row['dueAmount'] != 0) {
                            $totalDueLoanee = DB::table('mfn_loan_collections')
                                ->where('loanId', $row['loanId'])
                                ->select('memberId')
                                ->distinct()
                                ->count();

                        }

                        //if loan not have due then calculate standardOutstanding
                        if ($row['dueAmount'] == 0) {
                            $standardOutstanding += $row['outstanding'];
                        }
                    }

                    //get system date
                    $sysDate = MfnService::systemCurrentDate($branchId);

                    $watchfulDue                               = 0;
                    $watchfulOutstanding                       = 0;
                    $substandardDue                            = 0;
                    $substandardOutstanding                    = 0;
                    $doubtfulDue                               = 0;
                    $doubtfulOutstanding                       = 0;
                    $badOutstanding                            = 0;
                    $outstandingWithMoreThanTwoDueInstallments = 0;
                    $savingBalanceOfOverdueLoanee              = 0;

                    foreach ($loanIds as $loanId) {

                        //get loan info for individual loanId
                        $loanInfo = DB::table('mfn_loans as ml')
                            ->where('ml.is_delete', 0)
                            ->where('ml.id', $loanId)
                            ->select('ml.firstRepayDate', 'ml.lastInastallmentDate', 'ml.loanDurationInMonth', 'ml.installmentAmount', 'ml.memberId', 'ml.samityId', DB::raw('SUM(mlc.amount) as totalCollectionAmount, MAX(mlc.collectionDate) as lastCollectionDate'))
                            ->leftjoin('mfn_loan_collections as mlc', 'ml.id', 'mlc.loanId')
                            ->groupBy('ml.id', 'mlc.loanId')
                            ->first();

                        //get loan installment schedule
                        MfnService::$requirement = 'installments';
                        $loanSchedule = MfnService::generateLoanSchedule($loanId, $loanInfo->firstRepayDate, $loanInfo->lastInastallmentDate);

                        //calculate actual amount & actual installment
                        $scheduleNo  = 0;
                        $totalAmount = 0;
                        if (is_array($loanSchedule)) {
                            foreach ($loanSchedule as $schedule) {

                                $sysdate = '2020-07-19'; //___________________________________________
                                if ($sysdate < $schedule['installmentDate']) {
                                    break;
                                }

                                $scheduleNo++;
                                $totalAmount += $schedule['installmentAmount'];
                            }
                        }

                        //if actual amount greater than collection amount then it's called overdue
                        if (floatval($loanInfo->totalCollectionAmount) < $totalAmount) {

                            //how to much day they can't pay installments
                            $dateDifference = date_diff(date_create($loanInfo->lastCollectionDate), date_create($sysdate))->days;

                            //convert loan duration into year
                            $loanDurationYear     = $loanInfo->loanDurationInMonth / 12;
                            $individualLoanStatus = MfnService::getLoanStatus($loanId);

                            //sum of outstanding who aren't pay more than two installments
                            $amountDifference  = $totalAmount - $loanInfo->totalCollectionAmount;
                            $dueInsetallmentNo = $amountDifference / $loanInfo->installmentAmount;
                            if ($dueInsetallmentNo > 2) {
                                $outstandingWithMoreThanTwoDueInstallments += $individualLoanStatus[0]['outstanding'];
                            }

                            //sum of due & outstanding who aren't pay between 1 - 30 days
                            if ($dateDifference <= ($loanDurationYear * 30)) {

                                $watchfulDue += floatval($individualLoanStatus[0]['dueAmount']);
                                $watchfulOutstanding += floatval($individualLoanStatus[0]['outstanding']);

                            }
                            //sum of due & outstanding who aren't pay between 31 - 180 days
                            elseif ($dateDifference >= ($loanDurationYear * 31) && $dateDifference <= ($loanDurationYear * 180)) {

                                $substandardDue += floatval($individualLoanStatus[0]['dueAmount']);
                                $substandardOutstanding += floatval($individualLoanStatus[0]['outstanding']);

                            }
                            //sum of due & outstanding who aren't pay between 181 - 365 days
                            elseif ($dateDifference >= ($loanDurationYear * 181) && $dateDifference <= ($loanDurationYear * 365)) {

                                $doubtfulDue += floatval($individualLoanStatus[0]['dueAmount']);
                                $doubtfulOutstanding += floatval($individualLoanStatus[0]['outstanding']);

                            }
                            //sum of due & outstanding who aren't pay more than 365+ days
                            else {

                                $badOutstanding += floatval($individualLoanStatus[0]['outstanding']);
                            }

                            //saving Balance for overdue lonee
                            $filter['branchId'] = self::$branchId;
                            $filter['memberId'] = $loanInfo->memberId;
                            $filter['samityId'] = $loanInfo->samityId;

                            $savingBalance = MfnService::getSavingsBalance($filter);
                            $savingBalanceOfOverdueLoanee += $savingBalance;
                        }
                    }

                    //get product fundingOrgId & productCategoryId
                    $loanProduct = DB::table('mfn_loan_products')
                        ->where('id', $product)
                        ->select('fundingOrgId', 'productCategoryId')
                        ->first();

                    //insert month summary into mfn_month_end_loans table
                    DB::table('mfn_month_end_loans')->updateOrInsert([
                        'date'                        => self::$monthEndDate,
                        'branchId'                    => self::$branchId,
                        'fundingOrgId'                => $loanProduct->fundingOrgId,
                        'categoryId'                  => $loanProduct->productCategoryId,
                        'productId'                   => $product,
                        'gender'                      => $gender,
                        'openingBorrowerNo'           => $openingBorrowerNo,
                        'openingOutstanding'          => $openingOutstanding,
                        'openingOutstandingPrincipal' => $openingOutstandingPrincipal,
                        'disburseAmount'              => $disburseAmount,
                        'collectionAmount'            => $collectionAmount,
                        'collectionAmountPrincipal'   => $collectionAmountPrincipal,
                        'writeOffAmount'              => $writeOffAmount,
                        'writeOffAmountPrincipal'     => $writeOffAmountPrincipal,
                        'waiverAmount'                => $waiverAmount,
                        'waiverAmountPrincipal'       => $waiverAmountPrincipal,
                        'rebateAmount'                => $rebateAmount,
                        'borrowerNo'                  => $borrowerNo,
                        'fullyPaidBorrowerNo'         => $fullyPaidBorrowerNo,
                        'closingBorrowerNo'           => $closingBorrowerNo,
                        'closingOutstanding'          => $closingOutstanding,
                        'closingOutstandingPrincipal' => $closingOutstandingPrincipal,
                        'openingDue'                  => $openingDue,
                        'newDue'                      => $newDue,
                        'closingDue'                  => $closingDue,
                        'openingDuePrincipal'         => $openingDuePrincipal,
                        'newDuePrincipal'             => $newDuePrincipal,
                        'closingDuePrincipal'         => $closingDuePrincipal,
                        'totalDueLoanee'              => $totalDueLoanee,
                        'dueCollection'               => $dueCollection,
                        'dueCollectionPrincipal'      => $dueCollectionPrincipal,
                        'regularCollection'           => $regularCollection,
                        'regularCollectionPrincipal'  => $regularCollectionPrincipal,
                        'advanceCollection'           => $advanceCollection,
                        'advanceCollectionPrincipal'  => $advanceCollectionPrincipal,
                        'standardOutstanding'         => $standardOutstanding,
                        'watchfulDue'                 => $watchfulDue,
                        'watchfulOutstanding'         => $watchfulOutstanding,
                        'substandardDue'              => $substandardDue,
                        'substandardOutstanding'      => $substandardOutstanding,
                        'doubtfulDue'                 => $doubtfulDue,
                        'doubtfulOutstanding'         => $doubtfulOutstanding,
                        'badOutstanding'              => $badOutstanding,
                    ]);
                }
            }

            DB::commit();
            return redirect('/mfn');

        } catch (\Exception $e) {
            DB::rollback();
        }
    }
}
