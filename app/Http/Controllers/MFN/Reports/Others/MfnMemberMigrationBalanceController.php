<?php

namespace App\Http\Controllers\MFN\Reports\Others;

use App\Services\HrService as HRS;
use App\Http\Controllers\Controller;
use App\Model\MFN\MfnDayEnd;
use App\Model\GNL\Branch;
use App\Model\MFN\Samity;
use App\Model\MFN\Member;
use App\Model\MFN\Loan;

use Carbon\Carbon;
use DB;
use App\Model\MFN\SavingsAccount;
use App\Model\MFN\LoanProductCategory;
use App\Model\MFN\LoanProduct;

use App\Model\MFN\LoanCollection;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use DateTime;
use Role;
use App\Services\HrService;
use App\Services\MfnService;
use App\Helpers\RoleHelper;

class MfnMemberMigrationBalanceController extends Controller
{
    public function index(Request $req)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();
        
        $branchces = Branch::where('is_delete', 0)
            ->where('id', '>', 1)
            ->whereIn('id', $accessAbleBranchIds)
            ->orderBy('branch_code')
            ->select('id', 'branch_name', 'branch_code')
            ->get();

        $samities = [];
        if (count($branchces) == 1) {
            $samities = DB::table('mfn_samity')
                ->where('is_delete', 0)
                ->whereIn('branchId', $accessAbleBranchIds)
                ->get();
        }
        $fundindOrgs = DB::table('mfn_funding_orgs')->where('is_delete', 0)->get();
        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
        
        $data = array(
            "branchces"     => $branchces,
            "samities"      => $samities,
            "sysDate"       => $sysDate,
            "fundindOrgs"   => $fundindOrgs,
        );
        return view('MFN.Reports.Others.MemberMigrationBalance.index', $data);
    }

    public function printReport(Request $req)
    {
        // this reposrt will print only active members, lonas and savings

        $date = date('Y-m-d', strtotime($req->date));
        $withServiceCharge = $req->service_charge;
        $genders = array(
            0 => 'Male',
            1 => 'Female',
        );

        if (Auth::user()->branch_id != 1) {
            $req->branch = Auth::user()->branch_id;
        }

        // if search by samity
        if ($req->samity != '') {
            $samity = DB::table('mfn_samity')
                ->where('id', $req->samity)
                ->first();

            $branch = DB::table('gnl_branchs AS branch')
                ->join('gnl_companies AS com', 'com.id', 'branch.company_id')
                ->where('branch.id', $samity->branchId)
                ->select('branch.*', 'com.comp_name', 'com.comp_addr')
                ->first();

            // get active members
            $members = DB::table('mfn_members AS mem')
                ->join('mfn_member_details AS memDet', 'mem.id', 'memDet.memberId')
                ->where([
                    ['mem.is_delete', 0],
                    ['mem.samityId', $req->samity],
                    ['mem.admissionDate', '<=', $date],
                ])
                ->where(function ($query) use ($date) {
                    $query->where('mem.closingDate', '0000-00-00')
                        ->orWhere('mem.closingDate', '>', $date);
                })
                ->select('mem.id', 'mem.name', 'mem.memberCode', 'mem.primaryProductId','mem.admissionDate', 'memDet.spouseName')
                ->get();

            // modify samity id according to the member samity transfer
            # this code will be done after samity transfer

            // modify primary product id according to the member primary product transfer
            $memberPrimaryProducts = MfnService::getMemberPrimaryProductId($members->pluck('id')->toArray(), $date);

            foreach ($memberPrimaryProducts as $memberId => $primaryProductId) {
                if ($members->where('id', $memberId)->first() != null) {
                    $members->where('id', $memberId)->first()->primaryProductId = $primaryProductId;
                }
            }

            $loans = DB::table('mfn_loans AS loan')
                ->join('mfn_members AS member', 'member.id', 'loan.memberId')
                ->where([
                    ['loan.is_delete', 0],
                    ['loan.samityId', $req->samity],
                    ['loan.disbursementDate', '<=', $date],
                ])
                ->whereIn('loan.memberId', $members->pluck('id'))
                ->where(function ($query) use ($date) {
                    $query->where('loanCompleteDate', '0000-00-00')
                        ->orWhere('loanCompleteDate', '>', $date);
                })
                ->orderBy('loan.loanCycle')
                ->select('loan.id', 'loan.loanCode', 'loan.memberId', 'loan.samityId', 'loan.productId', 'loan.primaryProductId', 'loan.loanAmount', 'loan.disbursementDate', 'loan.loanCycle', 'loan.numberOfInstallment', 'member.gender')
                ->get(); 
                
            $loanSchedules = MfnService::generateLoanSchedule($loans->pluck('id')->toArray());
            $loanSchedules = collect($loanSchedules);

            $loanStatuses = MfnService::getLoanStatus($loans->pluck('id')->toArray(), $date);
            $loanStatuses = collect($loanStatuses);

            foreach ($loans as $key => $loan) {
                $loans[$key]->firstRepayDate = $loanSchedules->where('loanId', $loan->id)->min('installmentDate');

                $loans[$key]->totalRecovery = $loanStatuses->where('loanId', $loan->id)->sum('paidAmount');
                $loans[$key]->principalRecovery = $loanStatuses->where('loanId', $loan->id)->sum('paidAmountPrincipal');
                $loans[$key]->serviceChargeRecovery = $loanStatuses->where('loanId', $loan->id)->sum('paidAmountInterest');
                $loans[$key]->totalOutStanding = $loanStatuses->where('loanId', $loan->id)->sum('outstanding');
                $loans[$key]->outStandingPrinciple = $loanStatuses->where('loanId', $loan->id)->sum('outstandingPrincipal');
                $loans[$key]->rebateAmount = $loanStatuses->where('loanId', $loan->id)->sum('rebateAmount');
                $loans[$key]->dueAmount = $loanStatuses->where('loanId', $loan->id)->sum('dueAmount');
                $loans[$key]->dueAmountPrincipal = $loanStatuses->where('loanId', $loan->id)->sum('dueAmountPrincipal');
            }

            $loanProducts = DB::table('mfn_loan_products')->get();

            // get savings informations
            $savProducts = DB::table('mfn_savings_product')
                ->where('is_delete', 0)
                ->get();

            $deposits = DB::table('mfn_savings_deposit')
                ->where([
                    ['is_delete', 0],
                    ['date', '<=', $date],
                ])
                ->whereIn('memberId', $members->pluck('id'))
                ->select(DB::raw("memberId, savingsProductId, SUM(amount) AS amount"))
                ->groupBy('memberId')
                ->groupBy('savingsProductId')
                ->get();

            $withdraws = DB::table('mfn_savings_withdraw')
                ->where([
                    ['is_delete', 0],
                    ['date', '<=', $date],
                ])
                ->whereIn('memberId', $members->pluck('id'))
                ->select(DB::raw("memberId, savingsProductId, SUM(amount) AS amount"))
                ->groupBy('memberId')
                ->groupBy('savingsProductId')
                ->get();

            $data = array(
                'date'              => $date,
                'loanProducts'      => $loanProducts,
                'branch'            => $branch,
                'samity'            => $samity,
                'members'           => $members,
                'loans'             => $loans,
                'genders'           => $genders,
                'withServiceCharge' => $withServiceCharge,
                'savProducts'       => $savProducts,
                'deposits'          => $deposits,
                'withdraws'         => $withdraws,
            );

            return view('MFN.Reports.Others.MemberMigrationBalance.samity', $data);
        }


        // if search by only branch
        elseif ($req->branch != null) {

            $branch = DB::table('gnl_branchs AS branch')
                ->join('gnl_companies AS com', 'com.id', 'branch.company_id')
                ->where('branch.id', $req->branch)
                ->select('branch.*', 'com.comp_name', 'com.comp_addr')
                ->first();

            // get active members
            $members = DB::table('mfn_members')
                ->where([
                    ['is_delete', 0],
                    ['branchId', $req->branch],
                    ['admissionDate', '<=', $date],
                ])
                ->where(function ($query) use ($date) {
                    $query->where('closingDate', '0000-00-00')
                        ->orWhere('closingDate', '>', $date);
                })
                ->select('id', 'samityId', 'primaryProductId', 'gender')
                ->get();

            // modify samity id according to the member samity transfer
            # this code will be done after samity transfer

            // modify primary product id according to the member primary product transfer
            $memberPrimaryProducts = MfnService::getMemberPrimaryProductId($members->pluck('id')->toArray(), $date);

            foreach ($memberPrimaryProducts as $memberId => $primaryProductId) {
                if ($members->where('id', $memberId)->first() != null) {
                    $members->where('id', $memberId)->first()->primaryProductId = $primaryProductId;
                }
            }

            $samities = DB::table('mfn_samity')
                ->whereIn('id', $members->pluck('samityId')->toArray())
                ->select('id', 'name', 'samityCode')
                ->get();

            $loans = DB::table('mfn_loans AS loan')
                ->join('mfn_members AS member', 'member.id', 'loan.memberId')
                ->where([
                    ['loan.is_delete', 0],
                    ['loan.branchId', $req->branch],
                    ['loan.disbursementDate', '<=', $date],
                ])
                ->whereIn('loan.memberId', $members->pluck('id'))
                ->where(function ($query) use ($date) {
                    $query->where('loanCompleteDate', '0000-00-00')
                        ->orWhere('loanCompleteDate', null)
                        ->orWhere('loanCompleteDate', '>', $date);
                })
                ->select('loan.id', 'loan.samityId', 'loan.productId', 'loan.primaryProductId', 'loan.loanAmount', 'member.gender')
                ->get();

            $loanStatuses = MfnService::getLoanStatus($loans->pluck('id')->toArray(), $date);
            $loanStatuses = collect($loanStatuses);

            foreach ($loans as $key => $loan) {
                $loans[$key]->totalRecovery = $loanStatuses->where('loanId', $loan->id)->sum('paidAmount');
                $loans[$key]->principalRecovery = $loanStatuses->where('loanId', $loan->id)->sum('paidAmountPrincipal');
                $loans[$key]->serviceChargeRecovery = $loanStatuses->where('loanId', $loan->id)->sum('paidAmountInterest');
                $loans[$key]->totalOutStanding = $loanStatuses->where('loanId', $loan->id)->sum('outstanding');
                $loans[$key]->outStandingPrinciple = $loanStatuses->where('loanId', $loan->id)->sum('outstandingPrincipal');
                $loans[$key]->rebateAmount = $loanStatuses->where('loanId', $loan->id)->sum('rebateAmount');
                $loans[$key]->dueAmount = $loanStatuses->where('loanId', $loan->id)->sum('dueAmount');
                $loans[$key]->dueAmountPrincipal = $loanStatuses->where('loanId', $loan->id)->sum('dueAmountPrincipal');
            }

            // considered loan product Ids
            $consideredLoanProductIds = array_merge($members->pluck('primaryProductId')->toArray(), $loans->pluck('productId')->toArray());

            $loanProducts = DB::table('mfn_loan_products')->whereIn('id', $consideredLoanProductIds)->get();
            $loanProductCategories = DB::table('mfn_loan_product_category')->whereIn('id', $loanProducts->pluck('productCategoryId'))->get();

            // get savings informations
            $savProducts = DB::table('mfn_savings_product')
                ->where('is_delete', 0)
                ->get();

            $deposits = DB::table('mfn_savings_deposit')
                ->where([
                    ['is_delete', 0],
                    ['branchId', $req->branch],
                    ['date', '<=', $date],
                ])
                ->whereIn('memberId', $members->pluck('id'))
                ->select(DB::raw("memberId, savingsProductId, SUM(amount) AS amount"))
                ->groupBy('memberId')
                ->groupBy('savingsProductId')
                ->get();

            $withdraws = DB::table('mfn_savings_withdraw')
                ->where([
                    ['is_delete', 0],
                    ['branchId', $req->branch],
                    ['date', '<=', $date],
                ])
                ->whereIn('memberId', $members->pluck('id'))
                ->select(DB::raw("memberId, savingsProductId, SUM(amount) AS amount"))
                ->groupBy('memberId')
                ->groupBy('savingsProductId')
                ->get();

            $data = array(
                'branch'                => $branch,
                'date'                  => $date,
                'loanProducts'          => $loanProducts,
                'loanProductCategories' => $loanProductCategories,
                'samities'              => $samities,
                'members'               => $members,
                'loans'                 => $loans,
                'genders'               => $genders,
                'withServiceCharge'     => $withServiceCharge,
                'savProducts'           => $savProducts,
                'deposits'              => $deposits,
                'withdraws'             => $withdraws,
            );

            return view('MFN.Reports.Others.MemberMigrationBalance.branch', $data);
        }
    }

    public function getData(Request $req)
    {
        try {
            $data = [];

            if ($req->context == 'branch') {
                $samities = DB::table('mfn_samity')
                    ->where([
                        ['is_delete', 0],
                        ['branchId', $req->branchId],
                    ])
                    ->select(DB::raw("CONCAT(samityCode, ' - ', name) AS name, id"))
                    ->get();

                $data = array(
                    'samities' => $samities
                );
            }
            return response()->json($data);
        } catch (\Throwable $e) {
            //throw $th;
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong',
                'consoleMsg'    => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage()
            );

            return response()->json($notification);
        }
    }
}
