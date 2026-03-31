<?php

namespace App\Http\Controllers\MFN\Reports\RegularGeneralReports;

use App\Http\Controllers\Controller;
use App\Services\HrService;
use App\Services\MfnService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class DailyCollectionComponentWiseReportController extends Controller
{
    public function getDailyCollectionCompWiseFilterPart(Request $req)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $branchs = DB::table('gnl_branchs as b')
            ->where([['is_delete', 0], ['is_approve', 1], ['is_active', 1], ['id', '>', 1]])
        // ->whereIn('id', HrService::getUserAccesableBranchIds())
            ->select('b.id', DB::raw("CONCAT(b.branch_code,' - ',b.branch_name) as branch"))
            ->get();

        $prodCat = DB::table('mfn_loan_product_category as mlpc')
            ->where('is_delete', 0)
            ->select('id', DB::raw("CONCAT(mlpc.id,' - ',mlpc.name) as prodCat"))
            ->get();

        $products = DB::table('mfn_loan_products as mlp')
            ->where('is_delete', 0)
            ->select('id', DB::raw("CONCAT(mlp.id,' - ',mlp.name) as product"))
            ->get();

        $data = array(
            'sysDate'  => Carbon::parse($sysDate)->format('d-m-Y'),
            'branchs'  => $branchs,
            'prodCat'  => $prodCat,
            'products' => $products,
        );

        return view('MFN.Reports.RegularGeneralReports.DailyCollectionComponentWise.daily_collection_comp_wise_filterPart', $data);
    }

    public function getDailyCollectionCompWiseTablePart(Request $req)
    {
        $branchId          = (is_null($req->branchId)) ? null : $req->branchId;
        $productCategories = (is_null($req->productCategories)) ? null : $req->productCategories;
        $product           = (is_null($req->product)) ? null : $req->product;
        $date              = (is_null($req->date)) ? null : $req->date;
        $savingRecoerable  = (is_null($req->savingRecoerable)) ? null : $req->savingRecoerable;
        $date              = Carbon::parse($date)->format('Y-m-d');

        $group = DB::table('gnl_groups')
            ->where([['is_delete', 0], ['is_active', 1]])
            ->select('id', 'group_name')
            ->first();

        $branch = DB::table('gnl_branchs')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
                ['id', Auth::user()->branch_id],
            ])
            ->select('id', 'branch_name', 'branch_code', 'branch_addr')
            ->first();

        $savingsProduct = DB::table('mfn_savings_product')
            ->where('is_delete', 0)
            ->select('id', 'shortName')
            ->get();

        $countSP = $savingsProduct->count();
        $thhtml  = '';
        foreach ($savingsProduct as $key => $row) {
            $thhtml .= '<th rowspan="2">' . $row->shortName . '</th> ';
        }

        $dailyCollectionData = DB::table('mfn_samity as ms')
            ->where([['ms.is_delete', 0], ['ms.branchId', $branchId]])
            ->select('ms.id', 'ms.samityCode', 'ms.name as samityName', 'he.emp_code', 'he.emp_name', 'msp.id as savingsProductId', 'msp.shortName as savingProdName', 'mlp.shortName as component')
            ->leftjoin('hr_employees as he', 'ms.fieldOfficerEmpId', 'he.id')
            ->leftjoin('mfn_savings_deposit as msd', 'ms.id', 'msd.samityId')
            ->leftjoin('mfn_savings_product as msp', 'msd.savingsProductId', 'msp.id')
            ->leftjoin('mfn_loan_products as mlp', 'msd.primaryProductId', 'mlp.id')
            ->leftjoin('mfn_loan_product_category as mlpc', 'mlp.productCategoryId', 'mlpc.id')
            ->addSelect(['totalMembers' => DB::table('mfn_members as mm')
                    ->select(DB::raw('COUNT(mm.id)'))
                    ->whereColumn('ms.id', 'mm.samityId')
                    ->where([['mm.is_delete', 0], ['mm.admissionDate', $date]])
                    ->limit(1),
                'totalLoanee'               => DB::table('mfn_loans as ml')
                    ->select(DB::raw('COUNT(ml.id)'))
                    ->whereColumn('ms.id', 'ml.samityId')
                    ->where([['ml.is_delete', 0], ['ml.disbursementDate', $date]])
                    ->limit(1),
                'savingDeposit'             => DB::table('mfn_savings_deposit as msdepo')
                    ->select(DB::raw('IFNULL(SUM(msdepo.amount), 0)'))
                    ->whereColumn([['ms.id', 'msdepo.samityId'], ['msp.id', 'msdepo.savingsProductId']])
                    ->where([['msdepo.is_delete', 0], ['msdepo.date', $date]])
                    ->limit(1),
                'savingRefund'              => DB::table('mfn_savings_withdraw as msw')
                    ->select(DB::raw('IFNULL(SUM(msw.amount), 0)'))
                    ->whereColumn([['ms.id', 'msw.samityId'], ['msp.id', 'msw.savingsProductId']])
                    ->where([['msw.is_delete', 0], ['msw.date', $date]])
                    ->limit(1),
                'ldNoOfPersion'             => DB::table('mfn_loans as ml')
                    ->select(DB::raw('COUNT(DISTINCT(ml.memberId))'))
                    ->whereColumn('ms.id', 'ml.samityId')
                    ->where([['ml.is_delete', 0], ['ml.disbursementDate', $date]])
                    ->limit(1),
                'ldAmount'                  => DB::table('mfn_loans as ml')
                    ->select(DB::raw('IFNULL(SUM(ml.loanAmount), 0)'))
                    ->whereColumn('ms.id', 'ml.samityId')
                    ->where([['ml.is_delete', 0], ['ml.disbursementDate', $date]])
                    ->limit(1),
                'fplNoOfPerson'             => DB::table('mfn_loans as ml')
                    ->select(DB::raw('COUNT(DISTINCT(ml.memberId))'))
                    ->whereColumn('ms.id', 'ml.samityId')
                    ->where([['ml.is_delete', 0], ['ml.loanCompleteDate', $date]])
                    ->limit(1),
                'fplAmount'                 => DB::table('mfn_loans as ml')
                    ->select(DB::raw('IFNULL(SUM(ml.loanAmount), 0)'))
                    ->whereColumn('ms.id', 'ml.samityId')
                    ->where([['ml.is_delete', 0], ['ml.loanCompleteDate', $date]])
                    ->limit(1),
            ])
            ->where(function ($query) use ($productCategories, $product) {

                if (!is_null($productCategories)) {
                    $query->where('mlpc.id', $productCategories);
                }

                if (!is_null($product)) {
                    $query->where('mlp.id', $product);
                }
            })
            ->groupBy('ms.id', 'msp.id')
            ->get();

        $samityIdArr            = array();
        $dailyCollectionDataArr = array();

        $time = 0;
        foreach ($dailyCollectionData as $row) {

            $loanIds = DB::table('mfn_loans')
                ->where([
                    ['is_delete', 0],
                    ['samityId', $row->id],
                    ['branchId', $branchId]])
                ->pluck('id')
                ->toArray();

            $loanStatus = MfnService::getLoanStatus($loanIds, $date, $date);

            $loanRecoverable   = array_sum(array_column($loanStatus, 'onPeriodPayable'));
            $regularCollection = array_sum(array_column($loanStatus, 'onPeriodReularCollection'));
            $dueCollection     = array_sum(array_column($loanStatus, 'onPeriodDueCollection'));
            $advanceCollection = array_sum(array_column($loanStatus, 'onPeriodAdvanceCollection'));

            $todaysDue             = array_sum(array_column($loanStatus, 'onPeriodDueAmount'));
            $todaysTotalCollection = array_sum(array_column($loanStatus, 'onPeriodAdvanceCollection'));

            $insurancePremium = DB::table('mfn_loans')
                ->where([
                    ['is_delete', 0],
                    ['samityId', $row->id],
                    ['branchId', $branchId],
                    ['disbursementDate', $date],
                ])
                ->sum('insuranceAmount');

            $loanCollectionInfo = DB::table('mfn_loan_collections')
                ->where([
                    ['is_delete', 0],
                    ['samityId', $row->id],
                    ['branchId', $branchId],
                    ['collectionDate', $date],
                ])
                ->selectRaw('SUM(principalAmount) as loanReceive, SUM(interestAmount) as serviceCharge, SUM(amount) as amount, SUM(IF(paymentType = "Rebate", amount, 0)) as rebateAmount')
                ->groupBy('samityId')
                ->first();

            $todaysTotalRefund = DB::table('mfn_savings_withdraw')
                ->where([
                    ['is_delete', 0],
                    ['samityId', $row->id],
                    ['branchId', $branchId],
                    ['date', $date],
                ])
                ->sum('amount');

            MfnService::$requirement        = 'installments';
            MfnService::$loanStatusFromDate = null;
            $loanSchedule                   = MfnService::generateLoanSchedule($loanIds);

            $filterDate         = date('Y-m-d', strtotime($date));
            $todayScheduleLoans = array_filter($loanSchedule, function ($schedule) use ($filterDate) {

                if ($schedule['installmentDate'] == $filterDate) {
                    return true;
                }
            });

            $todayScheduleLoanIds            = array_column($todayScheduleLoans, 'loanId');
            $todayScheduleLoanCollectionInfo = DB::table('mfn_loan_collections')
                ->where('is_delete', 0)
                ->where('collectionDate', $date)
                ->whereIn('loanId', $todayScheduleLoanIds)
                ->select('loanId', 'amount')
                ->get();

            $totalNoOfPayer = 0;
            if (count($todayScheduleLoanCollectionInfo) > 0) {
                foreach ($todayScheduleLoanCollectionInfo as $todayloanCollectionInfo) {

                    $loanId            = $todayloanCollectionInfo->loanId;
                    $installmentAmount = array_column(array_filter($todayScheduleLoans, function ($todaySchedule) use ($loanId) {

                        if ($todaySchedule['loanId'] == $loanId) {
                            return true;
                        }
                    }), 'installmentAmount')[0];

                    if ($installmentAmount == $todayloanCollectionInfo->amount) {
                        $totalNoOfPayer++;
                    }
                }
            }

            $totalNoInstDue = count($loanIds) - $totalNoOfPayer;

            $dailyCollectionDataArr[$row->emp_code]['emp_code']                               = $row->emp_code;
            $dailyCollectionDataArr[$row->emp_code]['emp_name']                               = $row->emp_name;
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['samityCode'] = $row->samityCode;
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['samityName'] = $row->samityName;
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['component']  = $row->component;

            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['totalMembers']   = $row->totalMembers;
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['totalLoanee']    = $row->totalLoanee;
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['totalNoOfPayer'] = $totalNoOfPayer;
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['totalNoInstDue'] = $totalNoInstDue;

            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['savingDeposit'][$row->savingsProductId . '-' . $row->savingProdName] = $row->savingDeposit;
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['savingRefund'][$row->savingsProductId . '-' . $row->savingProdName]  = $row->savingRefund;

            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['ldNoOfPersion'] = $row->ldNoOfPersion;
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['ldAmount']      = $row->ldAmount;

            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['fplNoOfPerson'] = $row->fplNoOfPerson;
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['fplAmount']     = $row->fplAmount;

            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['loanRecoverable'] = $loanRecoverable;

            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['regularCollection'] = $regularCollection;
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['dueCollection']     = $dueCollection;
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['advanceCollection'] = $advanceCollection;

            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['rebateAmount']  = (is_null($loanCollectionInfo) ? 0 : round($loanCollectionInfo->rebateAmount, 2));
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['loanReceive']   = (is_null($loanCollectionInfo) ? 0 : round($loanCollectionInfo->loanReceive, 2));
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['serviceCharge'] = (is_null($loanCollectionInfo) ? 0 : round($loanCollectionInfo->serviceCharge, 2));
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['total']         = (is_null($loanCollectionInfo) ? 0 : round($loanCollectionInfo->amount, 2));

            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['todaysDue']                = $todaysDue;
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['insurancePremium']         = $insurancePremium;
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['todaysTotalCollection']    = $todaysTotalCollection;
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['todaysTotalRefund']        = $todaysTotalRefund;
            $dailyCollectionDataArr[$row->emp_code]['samity'][$row->samityCode]['todaysTotalNetCollection'] = "00";
        }

        $data = array(
            'groupName'              => $group->group_name,
            'branch'                 => $branch->branch_code . ' - ' . $branch->branch_name,
            'branchAddress'          => $branch->branch_addr,
            'date'                   => Carbon::parse($date)->format('d-m-Y'),
            'savingProduct'          => $countSP,
            'thhtml'                 => $thhtml,
            'dailyCollectionDataArr' => $dailyCollectionDataArr,
        );

        return view('MFN.Reports.RegularGeneralReports.DailyCollectionComponentWise.daily_collection_comp_wise_tablePart', $data);
    }

    public function getData(Request $req)
    {
        if ($req->context == 'product') {

            $products = DB::table('mfn_loan_products as mlp')
                ->where([['is_delete', 0], ['productCategoryId', $req->prodCatId]])
                ->select('id', 'name', DB::raw("CONCAT(mlp.id,' - ',mlp.name) as product"))
                ->get();

            $productOptionHtml = '<option value="">Select</option>';

            foreach ($products as $row) {
                $productOptionHtml .= '<option value="' . $row->id . '">' . $row->product . '</option>';
            }

            $data = array(
                'products' => $productOptionHtml,
            );
        }

        return response()->json($data);
    }
}
