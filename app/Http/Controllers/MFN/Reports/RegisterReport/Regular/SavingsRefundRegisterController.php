<?php

namespace App\Http\Controllers\MFN\Reports\RegisterReport\Regular;

use App\Services\MfnService;
use App\Services\AccService;
use App\Services\HrService;

use App\Http\Controllers\Controller;
use App\Model\MFN\Samity;
use App\Model\MFN\Member;
use App\Model\MFN\LoanProductCategory;
use App\Model\MFN\LoanProduct;
use App\Model\MFN\SavingsProduct;
use App\Model\MFN\savingsRefundRegister;
use App\Model\GNL\Branch;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;
use DateTime;

class savingsRefundRegisterController extends Controller
{

    public function index(Request $req)
    {

        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();
        if (count($accessAbleBranchIds) > 1) {
            $branchs = DB::table('gnl_branchs as b')
                ->where([['is_delete', 0], ['is_approve', 1], ['is_active', 1], ['id', '>', 1]])
                ->whereIn('id', $accessAbleBranchIds)
                ->select('b.id', 'b.branch_name', 'b.branch_code', DB::raw("CONCAT(b.branch_code,' - ',b.branch_name) as branch"))
                ->get();
            $samityData = [];
        } else {
            $branchs = [];
            $samityData = DB::table('mfn_samity')
                ->where('is_delete', 0)
                ->whereIn('branchId', $accessAbleBranchIds)
                ->select(DB::raw("id, CONCAT(samityCode,' - ', name) AS name"))
                ->get();
        }
        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);


        $fundingOrgData = DB::table('mfn_funding_orgs')
            ->where([['is_delete', 0]])
            ->select('id', 'name')
            ->get();

        $loanProdCategoryData = DB::table('mfn_loan_product_category')
            ->where([['is_delete', 0]])
            ->get();

        $loanProductData = DB::table('mfn_loan_products')
            ->where([['is_delete', 0]])
            ->get();

        $savingsProductData = DB::table('mfn_savings_product')
            ->where([['is_delete', 0]])
            ->get();

        $data = array(
            "branchData"           => $branchs,
            'samityData'           => $samityData,
            "fundingOrgData"       => $fundingOrgData,
            "loanProdCategoryData" => $loanProdCategoryData,
            "loanProductData"      => $loanProductData,
            'savingsProductData'    => $savingsProductData,
            'sysDate' => $sysDate,
        );

        return view('MFN.Reports.RegisterReport.Regular.SavingsRefundRegister.index', $data);
    }

    public function getData(Request $req)
    {
        try {

            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

            //branch
            if(Auth::user()->branch_id == 1){
                $branchId = $req->branch;
                $branchData = Branch::where('gnl_branchs.id', $req->branch)
                ->select('gnl_branchs.*', 'gnl_companies.*')
                ->leftJoin('gnl_companies', 'gnl_companies.id', 'gnl_branchs.company_id')->first();
            }
            else {
                $branchId = Auth::user()->branch_id;
                $branchData = Branch::where('gnl_branchs.id', $branchId)
                ->select('gnl_branchs.*', 'gnl_companies.*')
                ->leftJoin('gnl_companies', 'gnl_companies.id', 'gnl_branchs.company_id')->first();
            }

            //date
            $date = new Datetime($req->date);
            $date = $date->format('Y-m-d');
            $date_to = new Datetime($req->date_to);
            $date_to = $date_to->format('Y-m-d');

            //date difference
            $toDate = Carbon::createMidnightDate($date_to);
            $fromDate = Carbon::createMidnightDate($date);

            $diffDays = $fromDate->diffInDays($toDate);
            //category
            $selected_category = $req->category;

            //product
            $selected_primary_product = $req->productId;
            
            //savings_product
            $selected_savings_product = $req->savingsId;

            //samity
            $selected_samity = $req->samity;
            $samityData = Samity::where('branchId', $branchId)->get();
            
            $loanProdCategoryData = LoanProductCategory::where('id', $selected_category)->get();
            $loanProductData = LoanProduct::where('id',$selected_primary_product)->get();
            $savingsProductData = SavingsProduct::where('id',$selected_savings_product)->get();
            // $time_start = microtime(true);

            ///////////////
            $withdraws = DB::table('mfn_savings_withdraw')
                ->where('is_delete', 0)
                ->where('amount', '!=', 0);

            if ($branchId != '') {
                $withdraws->where('branchId', $branchId);
            }
            if ($selected_samity != '') {
                $withdraws->where('samityId', $selected_samity);
            }
            if ($selected_primary_product != '') {
                $withdraws->where('primaryProductId', $selected_primary_product);
            }
            if ($selected_savings_product != '') {
                $withdraws->where('savingsProductId', $selected_savings_product);
            }
            if ($date != '') {
                $withdraws->where('date', '>=', date('Y-m-d', strtotime($date)));
            }
            if ($date_to != '') {
                $withdraws->where('date', '<=', date('Y-m-d', strtotime($date_to)));
            }
            $withdraws = $withdraws->get();


            // $time_start = microtime(true);
            
            $savAccounts = DB::table('mfn_savings_accounts AS savAcc')
                ->join('mfn_members', 'mfn_members.id', 'savAcc.memberId')
                ->join('mfn_samity', 'mfn_samity.id', 'savAcc.samityId')
                ->whereIn('savAcc.id', $withdraws->pluck('accountId'))
                ->select('savAcc.id', 'savAcc.accountCode', 'mfn_members.memberCode', 'mfn_members.name AS memberName','mfn_samity.samityCode','mfn_samity.name')
                ->get();


            foreach ($withdraws as $key => $withdraw) {
                $sacAcc = $savAccounts->where('id', $withdraw->accountId)->first();
                $withdraws[$key]->accountCode = $sacAcc->accountCode;
                $withdraws[$key]->memberName = $sacAcc->memberName;
                $withdraws[$key]->memberCode = $sacAcc->memberCode;
                $withdraws[$key]->samity = $sacAcc->samityCode;
                $withdraws[$key]->samityName = $sacAcc->name;

                $depositAmount = DB::table('mfn_savings_deposit')
                ->where([
                    ['is_delete', 0],
                    ['accountId', $withdraw->accountId],
                    ['date', '<=', $withdraw->date],
                ])
                ->sum('amount');

                $withdrawAmount = DB::table('mfn_savings_withdraw')
                ->where([
                    ['is_delete', 0],
                    ['accountId', $withdraw->accountId],
                    ['date', '<=', $withdraw->date],
                ])
                ->sum('amount');

                $balance = $depositAmount - $withdrawAmount;

                $withdraws[$key]->savBalBeforeRefund = $balance + $withdraw->amount;
                $withdraws[$key]->totalRefund = $withdrawAmount;
                $withdraws[$key]->currentSavBalance = $balance;
            }
            ///////////////

            // $savingsRefundRegister = DB::table('mfn_savings_withdraw')
            //     ->where([['mfn_savings_withdraw.branchId', $branchId], ['mfn_savings_withdraw.is_delete', 0], ['mfn_savings_withdraw.is_delete', 0]])
            //     ->join('mfn_members', function ($savingsRefundRegister) {
            //         $savingsRefundRegister->on('mfn_members.id', '=', 'mfn_savings_withdraw.memberId')
            //             ->where([['mfn_members.is_delete', 0]]);
            //     })
            //     ->join('mfn_samity', function ($savingsRefundRegister) {
            //         $savingsRefundRegister->on('mfn_savings_withdraw.samityId', '=', 'mfn_samity.id')
            //             ->where([['mfn_samity.is_delete', 0]]);
            //     })
            //     ->join('mfn_savings_accounts', function ($savingsRefundRegister) {
            //         $savingsRefundRegister->on('mfn_savings_accounts.memberId', '=', 'mfn_savings_withdraw.memberId')
            //             ->where([['mfn_savings_accounts.is_delete', 0]]);
            //     })
            //     ->where(function ($savingsRefundRegister) use ($selected_samity, $selected_category, $selected_primary_product, $selected_savings_product, $date, $date_to) {

            //         //samity
            //         if (!empty($selected_samity)) {
            //             $savingsRefundRegister->where('mfn_savings_withdraw.samityId', '=', $selected_samity);
            //         }

            //         //primary_product
            //         if (!empty($selected_primary_product)) {
            //             $savingsRefundRegister->where('mfn_savings_withdraw.primaryProductId', '=', $selected_primary_product);
            //         }

            //         //savings_product
            //         if (!empty($selected_savings_product)) {
            //             $savingsRefundRegister->where('mfn_savings_withdraw.savingsProductId', '=', $selected_savings_product);
            //         }

            //         //date && date_to
            //         if (!empty($date) && !empty($date_to)) {

            //             $date = new DateTime($date);
            //             $date = $date->format('Y-m-d');

            //             $date_to = new DateTime($date_to);
            //             $date_to = $date_to->format('Y-m-d');
            //         }
            //     })
            //     ->select(
            //         'mfn_savings_withdraw.date as date',
            //         'mfn_savings_withdraw.accountId as accountId',
            //         'mfn_savings_withdraw.transactionTypeId as fullRefundCheck',
            //         'mfn_savings_withdraw.amount as amount',
            //         'mfn_members.memberCode as memberCode',
            //         'mfn_members.name as memberName',
            //         'mfn_samity.samityCode as samityCode',
            //         'mfn_samity.name as samityName',
            //         'mfn_savings_accounts.accountCode as savingsCode',

            //     )
            //     ->orderBy('mfn_savings_withdraw.samityId')
            //     ->get();



            //////////////////
            // foreach ($savingsRefundRegister as $key => $withdraw) {
            //     $depositAmount = DB::table('mfn_savings_deposit')
            //     ->where([
            //         ['is_delete', 0],
            //         ['accountId', $withdraw->accountId],
            //         ['date', '<=', $withdraw->date],
            //     ])
            //     ->sum('amount');

            //     $withdrawAmount = DB::table('mfn_savings_withdraw')
            //     ->where([
            //         ['is_delete', 0],
            //         ['accountId', $withdraw->accountId],
            //         ['date', '<=', $withdraw->date],
            //     ])
            //     ->sum('amount');

            //     $balance = $depositAmount - $withdrawAmount;

            //     $savingsRefundRegister[$key]->savBalBeforeRefund = $balance + $withdraw->amount;
            //     $savingsRefundRegister[$key]->totalRefund = $withdrawAmount;
            //     $savingsRefundRegister[$key]->currentSavBalance = $balance;
            // }
            //////////////////


            // $savingsBalanceBeforeRefund = 0;

                
            $data = array(
                // 'savingsBalanceBeforeRefund' => $savingsBalanceBeforeRefund,
                'savingsRefundRegister' => $withdraws,
                'branchData'  => $branchData,
                'FromDate' => $date,
                'samity_selected' => $selected_samity,
                'selected_category' => $selected_category,
                'selected_primary_product' => $selected_primary_product,
                'selected_savings_product' => $selected_savings_product,
                'samityData' => $samityData,
                'loanProdCategoryData' => $loanProdCategoryData,
                'loanProductData' => $loanProductData,
                'savingsProductData' => $savingsProductData,
                'sysDate' => $sysDate,
                'toDate' => $date_to,
            );
            return view('MFN.Reports.RegisterReport.Regular.SavingsRefundRegister.viewreportsavingsrefundregister', $data);
        } catch (\Throwable $e) {
            //throw $th;
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong',
                'consoleMsg'    => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage()
            );



            return redirect()->back()->with($notification);
        }
    }
}
