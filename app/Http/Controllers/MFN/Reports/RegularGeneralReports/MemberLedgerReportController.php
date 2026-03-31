<?php

namespace App\Http\Controllers\MFN\Reports\RegularGeneralReports;
use App\Services\MfnService;
use App\Http\Controllers\Controller;
use App\Model\MFN\Samity;
use App\Model\GNL\Branch;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;

class MemberLedgerReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function getmemberLedger(Request $req)
    {
        if ($req->ajax()) {

            $branchId = (empty($req->input('branchId'))) ? null : $req->input('branchId');

            $samityId = (empty($req->input('samityId'))) ? null : $req->input('samityId');

            $memberId = (empty($req->input('memberId'))) ? null : $req->input('memberId');

            $startDate = (empty($req->input('startDate'))) ? null : $req->input('startDate');

            $endDate = (empty($req->input('endDate'))) ? null : $req->input('endDate');

            $savingsProduct = (empty($req->input('savingsProduct'))) ? null : $req->input('savingsProduct');
            $savingsAccount = (empty($req->input('savingsAccount'))) ? null : $req->input('savingsAccount');

            $loanProduct = (empty($req->input('loanProduct'))) ? null : $req->input('loanProduct');
            $loanAccount = (empty($req->input('loanAccount'))) ? null : $req->input('loanAccount');


            $branchData = Branch::where([['is_delete', 0], ['is_active', 1], ['is_approve', 1]])
                          ->select('id','branch_name','branch_code')->orderBy('branch_code')->get();


            $loanProductData = DB::table('mfn_loan_products')
                            ->where([['is_delete', 0]])
                            ->select('id','shortName')
                            ->get();
            $savingsProductData = DB::table('mfn_savings_product')
                            ->where([['is_delete', 0]])
                            ->select('id','shortName')
                            ->get();

            if (!empty($startDate) && !empty($endDate)) {
                $startDate = Carbon::parse($startDate)->format('Y-m-d');
                $endDate = Carbon::parse($endDate)->format('Y-m-d');
            }

            $ttl_deposit_amount = 0;
            $ttl_withdraw_amount = 0;
            $ttl_balance_sav = 0;

            $ttl_disburse_amount = 0;
            $ttl_principal_amount = 0;
            $ttl_interest_amount = 0;
            $ttl_collection_amount = 0;
            $ttl_rebate_amount = 0;
            $ttl_outstanding = 0;

            $savingsDeposit = DB::table('mfn_savings_accounts as sa')
                            ->where([['sa.is_delete',0],['sa.branchId',$branchId],
                                ['sa.samityId',$samityId],['sa.memberId',$memberId]])
                            ->join('mfn_savings_deposit as sd', function ($savingsDeposit) use($startDate, $endDate) {
                                $savingsDeposit->on('sd.accountId', '=', 'sa.id')
                                    ->whereBetween('sd.date',[$startDate, $endDate])
                                    ->where([['sd.is_delete', 0]]);
                            })
                            ->join('mfn_savings_product as sp', function ($savingsDeposit){
                                $savingsDeposit->on('sp.id', '=', 'sa.savingsProductId')
                                    ->where([['sp.is_delete', 0]]);
                            })
                            ->where(function ($savingsDeposit) use ($savingsProduct) {
                                if (!empty($savingsProduct)) {
                                    $savingsDeposit->where('sa.savingsProductId', '=', $savingsProduct);
                                }
                            })
                            ->where(function ($savingsDeposit) use ($savingsAccount) {
                                if (!empty($savingsAccount)) {
                                    $savingsDeposit->where('sa.id', '=', $savingsAccount);
                                }
                            })
                            ->select('sd.date','sd.amount as depositAmount','sd.accountId','sa.accountCode',
                                DB::raw('CONCAT(sp.productCode," - ", sp.name) AS product'))
                            ->get();

            $savingsWithdraw = DB::table('mfn_savings_accounts as sa')
                            ->where([['sa.is_delete',0],['sa.branchId',$branchId],
                                ['sa.samityId',$samityId],['sa.memberId',$memberId]])
                            ->join('mfn_savings_withdraw as sw', function ($savingsWithdraw) use($startDate, $endDate) {
                                $savingsWithdraw->on('sw.accountId', '=', 'sa.id')
                                    ->whereBetween('sw.date',[$startDate, $endDate])
                                    ->where([['sw.is_delete', 0]]);
                            })
                            ->join('mfn_savings_product as sp', function ($savingsDeposit){
                                $savingsDeposit->on('sp.id', '=', 'sa.savingsProductId')
                                    ->where([['sp.is_delete', 0]]);
                            })
                            ->where(function ($savingsWithdraw) use ($savingsProduct) {
                                if (!empty($savingsProduct)) {
                                    $savingsWithdraw->where('sa.savingsProductId', '=', $savingsProduct);
                                }
                            })
                            ->where(function ($savingsWithdraw) use ($savingsAccount) {
                                if (!empty($savingsAccount)) {
                                    $savingsWithdraw->where('sa.id', '=', $savingsAccount);
                                }
                            })
                            ->select('sw.date','sw.amount as withdrawAmount','sw.accountId','sa.accountCode',
                                    DB::raw('CONCAT(sp.productCode," - ", sp.name) AS product'))
                            ->get();

            $savingsDates = array_unique(array_merge(
                                collect($savingsDeposit)->pluck('date')->toArray() , 
                                collect($savingsWithdraw)->pluck('date')->toArray()
                            ));

            sort($savingsDates);

            $savingsData = '';
            $loanData = '';

            $c = 0;
            foreach ($savingsDates as $key => $value) {
                if (!empty($savingsData)) {
                    $merged_savings = $savingsData;
                }
                $savingsData = $savingsDeposit->where('date',$value)->where('depositAmount','>','0');
                $savingsData2 = $savingsWithdraw->where('date',$value)->where('withdrawAmount','>','0');


                foreach ($savingsData as $key => $value) {
                    $savingsData[$key]->date = Carbon::parse($savingsData[$key]->date)->format('d-m-Y'); 
                    $savingsData[$key]->withdrawAmount = 0;  
                    $savingsData[$key]->depositAmount = $value->depositAmount;
                    $accFilters['accountId'] = $value->accountId;
                    $accFilters['dateTo'] = $startDate;
                    $savingsData[$key]->currentBL = Mfnservice::getSavingsBalance($accFilters) + $savingsData[$key]->depositAmount;
                }

                foreach ($savingsData2 as $key => $value) {
                    $savingsData2[$key]->date = Carbon::parse($savingsData2[$key]->date)->format('d-m-Y'); 
                    $savingsData2[$key]->depositAmount = 0;                    
                    $savingsData2[$key]->withdrawAmount = $value->withdrawAmount;
                    $accFilters['accountId'] = $value->accountId;
                    $accFilters['dateTo'] = $startDate;

                    $savingsData2[$key]->currentBL = Mfnservice::getSavingsBalance($accFilters) - $savingsData2[$key]->withdrawAmount;

                    foreach ($savingsData as $row) {
                        if ($row->date == $value->date && $row->accountId == $value->accountId) {
                            $savingsData2[$key]->currentBL = $row->currentBL - $savingsData2[$key]->withdrawAmount;
                        }
                    }
                }
                if ($c > 0) {
                    $savingsData = $merged_savings->merge($savingsData);
                }
                $savingsData = $savingsData->merge($savingsData2);
                $c++;
                
            }

            $ttl_deposit_amount  = $savingsDeposit->sum('depositAmount');
            $ttl_withdraw_amount = $savingsWithdraw->sum('withdrawAmount');
            $ttl_balance_sav     = $savingsDeposit->sum('currentBL') + $savingsWithdraw->sum('currentBL');

            $loanDisburse = DB::table('mfn_loans as ml')
                            ->where([['ml.is_delete',0],['ml.branchId',$branchId],
                                ['ml.samityId',$samityId],['ml.memberId',$memberId]])
                            ->whereBetween('ml.disbursementDate',[$startDate, $endDate])
                            ->join('mfn_loan_products as lp', function ($loanDisburse){
                                $loanDisburse->on('ml.productId', '=', 'lp.id')
                                    ->where([['lp.is_delete', 0]]);
                            })
                            ->where(function ($loanDisburse) use ($loanProduct) {
                                if (!empty($loanProduct)) {
                                    $loanDisburse->where('ml.productId', '=', $loanProduct);
                                }
                            })
                            ->where(function ($loanDisburse) use ($loanAccount) {
                                if (!empty($loanAccount)) {
                                    $loanDisburse->where('ml.id', '=', $loanAccount);
                                }
                            })
                            ->select('ml.disbursementDate as date','ml.loanCode','ml.loanType','ml.id as accountId',
                                DB::raw('IFNULL( ml.repayAmount , 0 ) as disburseAmount,
                                    CONCAT(lp.productCode," - ", lp.name) AS product'))
                            ->get();
            
            $loanCollection = DB::table('mfn_loans as ml')
                            ->where([['ml.is_delete',0],['ml.branchId',$branchId],
                                ['ml.samityId',$samityId],['ml.memberId',$memberId]])
                            ->join('mfn_loan_collections as sc', function ($loanCollection) use($startDate, $endDate) {
                                $loanCollection->on('sc.loanId', '=', 'ml.id')
                                    ->whereBetween('sc.collectionDate',[$startDate, $endDate])
                                    ->where([['sc.is_delete', 0]]);
                            })
                            ->join('mfn_loan_products as lp', function ($loanCollection){
                                $loanCollection->on('ml.productId', '=', 'lp.id')
                                    ->where([['lp.is_delete', 0]]);
                            })
                            ->where(function ($loanCollection) use ($loanProduct) {
                                if (!empty($loanProduct)) {
                                    $loanCollection->where('ml.productId', '=', $loanProduct);
                                }
                            })
                            ->where(function ($loanCollection) use ($loanAccount) {
                                if (!empty($loanAccount)) {
                                    $loanCollection->where('ml.id', '=', $loanAccount);
                                }
                            })
                            ->select('sc.collectionDate as date','ml.loanCode','ml.loanType','ml.id as accountId','sc.paymentType','sc.amount',
                                DB::raw('IFNULL( sc.principalAmount , 0 ) as principalAmount,
                                    IFNULL( sc.interestAmount , 0 ) as interestAmount,
                                    IFNULL( sc.amount , 0) as totalAmount,
                                    CONCAT(lp.productCode," - ", lp.name) AS product'))
                            ->get();

            $loanDates = array_unique(array_merge(
                                collect($loanDisburse)->pluck('date')->toArray() , 
                                collect($loanCollection)->pluck('date')->toArray()
                            ));

            sort($loanDates);

            $c = 0;
            foreach ($loanDates as $key => $value) {
                if (!empty($loanData)) {
                    $merged_loan = $loanData;
                }
                $loanData = $loanDisburse->where('date',$value);
                $loanData2 = $loanCollection->where('date',$value);

                foreach ($loanData as $key => $value) {
                    $loanData[$key]->date = Carbon::parse($loanData[$key]->date)->format('d-m-Y'); 
                    $loanData[$key]->principalAmount = 0;
                    $loanData[$key]->interestAmount = 0;  
                    $loanData[$key]->totalAmount = 0;   
                    $loanData[$key]->outstanding = 0;
                    $loanData[$key]->rebate = 0;
                }
                foreach ($loanData2 as $key => $value) { 
                    $loanData2[$key]->date = Carbon::parse($loanData2[$key]->date)->format('d-m-Y');                 
                    $loanData2[$key]->disburseAmount = 0;
                    $accFilters['accountId'] = $value->accountId;
                    $accFilters['dateTo'] = $startDate;
                    $loanData2[$key]->rebate = 0;
                    if ($loanData2[$key]->loanType == 'Regular') {
                        $loanData2[$key]->outstanding = Mfnservice::getLoanStatus($accFilters)[0]['outstanding'];
                    }
                    else if ($loanData2[$key]->loanType == 'Onetime') {
                        $loanData2[$key]->outstanding = Mfnservice::getLoanStatus($accFilters)[0]['outstandingPrincipal'];
                    }
                    if ($loanData2[$key]->paymentType == 'Rebate') {
                        $loanData2[$key]->rebate = $loanData2[$key]->amount;
                    }
                }
                if ($c > 0) {
                    $loanData = $merged_loan->merge($loanData);
                }
                $loanData = $loanData->merge($loanData2);
                $c++;
                
            }
            $ttl_disburse_amount  = $loanDisburse->sum('disburseAmount');
            $ttl_principal_amount = $loanCollection->sum('principalAmount');
            $ttl_interest_amount  = $loanCollection->sum('interestAmount');
            $ttl_collection_amount = $loanCollection->sum('totalAmount');
            $ttl_rebate_amount  = $loanCollection->sum('rebate');
            $ttl_outstanding     = $loanCollection->sum('outstanding');

            $json_data = array(
                "draw"                  => intval($req->input('draw')),
                "savingsData"           => $savingsData,
                "loanData"              => $loanData,
                "ttl_deposit_amount"    => number_format($ttl_deposit_amount, 2),
                "ttl_withdraw_amount"   => number_format($ttl_withdraw_amount, 2),
                "ttl_balance_sav"       => number_format($ttl_balance_sav, 2),
                "ttl_disburse_amount"   => number_format($ttl_disburse_amount, 2),
                "ttl_principal_amount"  => number_format($ttl_principal_amount, 2),
                "ttl_interest_amount"   => number_format($ttl_interest_amount, 2),
                "ttl_collection_amount" => number_format($ttl_collection_amount, 2),
                "ttl_rebate_amount"     => number_format($ttl_rebate_amount, 2),
                "ttl_outstanding"       => number_format($ttl_outstanding, 2)
            );
            echo json_encode($json_data);
        }

    	else {

            $branchId = ($req->branch_id) ? $req->branch_id : Auth::user()->branch_id;

            $branchData = Branch::where([['is_delete', 0], ['is_active', 1], ['is_approve', 1]])
                          ->select('id','branch_name','branch_code')->orderBy('branch_code')->get();


            $loanProductData = DB::table('mfn_loan_products')
                            ->where([['is_delete', 0]])
                            ->select('id','shortName')
                            ->get();

            $loanAccountData = DB::table('mfn_loans')
                            ->where([['is_delete', 0]])
                            ->select('id','loanCode')
                            ->get();

            $savingsProductData = DB::table('mfn_savings_product')
                            ->where([['is_delete', 0]])
                            ->select('id','shortName')
                            ->get();

            $savingsAccountData = DB::table('mfn_savings_accounts')
                            ->where([['is_delete', 0]])
                            ->select('id','accountCode')
                            ->get();

            $data = array(
                "branchData"           => $branchData,
                "loanProductData"      => $loanProductData,
                "loanAccountData"   => $loanAccountData,
                "savingsProductData"      => $savingsProductData,
                "savingsAccountData"   => $savingsAccountData
            );

            return view('MFN.Reports.RegularGeneralReports.get_member_ledger',$data);
        }
    }

}
