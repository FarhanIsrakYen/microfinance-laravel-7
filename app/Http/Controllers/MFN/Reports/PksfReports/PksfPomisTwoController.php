<?php

namespace App\Http\Controllers\MFN\Reports\PksfReports;

use App\Http\Controllers\Controller;
use App\Services\MfnService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class PksfPomisTwoController extends Controller
{
    public function getPksfPomisTwoFilterPart(Request $req)
    {
        $fundingOrg = DB::table('mfn_funding_orgs as forg')
            ->where('is_delete', 0)
            ->select('id', 'name', DB::raw("CONCAT(forg.id,' - ',forg.name) as fundingOrg"))
            ->get();

        $branch = DB::table('gnl_branchs')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
                ['id', Auth::user()->branch_id],
            ])
            ->select('soft_start_date')
            ->first();

        $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

        $data = array(
            'fundingOrg'  => $fundingOrg,
            'year'        => Carbon::parse($branch->soft_start_date)->format('Y'),
            'currentYear' => Carbon::parse(Carbon::now())->format('Y'),
            'months'      => $months,
        );

        return view('MFN.Reports.PksfReports.pomis_two', $data);
    }

    public function getPksfPomisTwoViewPart(Request $req)
    {
        $year                 = (is_null($req->year)) ? null : $req->year;
        $month                = (is_null($req->month)) ? null : $req->month;
        $isWithServiceChanrge = (is_null($req->isWithServiceChanrge)) ? null : $req->isWithServiceChanrge;
        $isRoundUp            = (is_null($req->isRoundUp)) ? null : $req->isRoundUp;
        $loanOption           = (is_null($req->loanOption)) ? null : $req->loanOption;
        $fundingOrg           = (is_null($req->fundingOrg)) ? null : $req->fundingOrg;

        $monthStartDate = date('Y-m-d', strtotime($year . '-' . $month . '-01'));
        $monthEndDate   = date('Y-m-t', strtotime($monthStartDate));

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
            ->select('id', 'branch_name')
            ->first();

        $pomisTwoData = DB::table('mfn_month_end_loans as mmel')
            ->where('mmel.date', $monthEndDate)
            ->select('mmel.id', 'mfo.name as fundingOrgName', 'mlp.name as productName', 'mlpc.name as productCatName', 'mmel.gender', 'mmel.closingOutstanding', 'mmel.closingOutstandingPrincipal', 'mmel.collectionAmount', 'mmel.collectionAmountPrincipal', DB::raw('SUM(mmel.openingBorrowerNo) as openingBorrowerNo, SUM(mmel.openingOutstanding) as openingOutstanding, SUM(mmel.openingOutstandingPrincipal) as openingOutstandingPrincipal, SUM(mmel.disburseAmount) as crrentMonthAmount, SUM(mmel.borrowerNo) as currentMonthBorrowerNo, SUM(mmel.fullyPaidBorrowerNo) as fullyPaidBorrowerNo, SUM(mmel.closingBorrowerNo) as closingBorrowerNo, SUM(mmel.closingOutstanding) as closingOutstanding, SUM(mmel.closingOutstandingPrincipal) as closingOutstandingPrincipal, SUM(mmel.collectionAmount) as collectionAmount, SUM(mmel.collectionAmountPrincipal) as collectionAmountPrincipal'))
            ->leftjoin('mfn_funding_orgs as mfo', 'mmel.fundingOrgId', 'mfo.id')
            ->leftjoin('mfn_loan_products as mlp', 'mmel.productId', 'mlp.id')
            ->leftjoin('mfn_loan_product_category as mlpc', 'mmel.categoryId', 'mlpc.id')
            ->where(function ($pomisTwoData) use ($fundingOrg) {

                if (!is_null($fundingOrg)) {
                    $pomisTwoData->where('mmel.fundingOrgId', $fundingOrg);
                }
            });

        if ($loanOption == 'LoanProd') {
            $pomisTwoData = $pomisTwoData->groupBy('mmel.productId', 'mmel.fundingOrgId', 'mmel.gender')->get();
        } else {
            $pomisTwoData = $pomisTwoData->groupBy('mmel.categoryId', 'mmel.fundingOrgId', 'mmel.gender')->get();
        }

        $pomisTwoDataArr = array();

        foreach ($pomisTwoData as $row) {

            $pomisTwoDataArr[$row->fundingOrgName]['fundingOrgName'] = $row->fundingOrgName;
            $pomisTwoDataArr[$row->fundingOrgName]['products'][$row->productName]['productName'] = ($loanOption == 'LoanProd') ? $row->productName : $row->productCatName;

            $pomisTwoDataArr[$row->fundingOrgName]['products'][$row->productName]['genders'][$row->gender]['genderName'] = $row->gender;

            $pomisTwoDataArr[$row->fundingOrgName]['products'][$row->productName]['genders'][$row->gender]['openingBorrowerNo'] = $row->openingBorrowerNo;

            $pomisTwoDataArr[$row->fundingOrgName]['products'][$row->productName]['genders'][$row->gender]['loanOutstanding'] = ($isWithServiceChanrge == 'Yes') ? ($isRoundUp == 'Yes') ? round($row->openingOutstanding) : round($row->openingOutstanding, 2) : ($isRoundUp == 'Yes') ? round($row->openingOutstandingPrincipal) : round($row->openingOutstandingPrincipal, 2);

            $pomisTwoDataArr[$row->fundingOrgName]['products'][$row->productName]['genders'][$row->gender]['currentMonthBorrowerNo'] = $row->currentMonthBorrowerNo;

            $pomisTwoDataArr[$row->fundingOrgName]['products'][$row->productName]['genders'][$row->gender]['crrentMonthAmount'] = ($isRoundUp == 'Yes') ? round($row->crrentMonthAmount) : round($row->crrentMonthAmount, 2);

            $pomisTwoDataArr[$row->fundingOrgName]['products'][$row->productName]['genders'][$row->gender]['totalRecoveryAmount'] = ($isWithServiceChanrge == 'Yes') ? ($isRoundUp == 'Yes') ? round($row->collectionAmount) : round($row->collectionAmount, 2) : ($isRoundUp == 'Yes') ? round($row->collectionAmountPrincipal) : round($row->collectionAmountPrincipal, 2);

            $pomisTwoDataArr[$row->fundingOrgName]['products'][$row->productName]['genders'][$row->gender]['fullyPaidBorrowerNo'] = $row->fullyPaidBorrowerNo;

            $pomisTwoDataArr[$row->fundingOrgName]['products'][$row->productName]['genders'][$row->gender]['closingBorrowerNo'] = $row->closingBorrowerNo;

            $pomisTwoDataArr[$row->fundingOrgName]['products'][$row->productName]['genders'][$row->gender]['closingAmount'] = ($isWithServiceChanrge == 'Yes') ? ($isRoundUp == 'Yes') ? round($row->closingOutstanding) : round($row->closingOutstanding, 2) : ($isRoundUp == 'Yes') ? round($row->closingOutstandingPrincipal) : round($row->closingOutstandingPrincipal, 2);

        }

        $data = array(
            'groupName'            => $group->group_name,
            'branchName'           => $branch->branch_name,
            'isWithServiceChanrge' => ($isWithServiceChanrge == 'Yes') ? 'With Service Charge' : 'Without Service Charge',
            'fundingOrg'           => ($fundingOrg == null) ? 'All' : $fundingOrg,
            'pomisTwoDataArr'      => $pomisTwoDataArr,
        );

        return view('MFN.Reports.PksfReports.pomis_two_viewPart', $data);
    }

    public function getPksfPomisTwoAFilterPart(Request $req)
    {
        $fundingOrg = DB::table('mfn_funding_orgs as forg')
            ->where('is_delete', 0)
            ->select('id', 'name', DB::raw("CONCAT(forg.id,' - ',forg.name) as fundingOrg"))
            ->get();

        $branch = DB::table('gnl_branchs')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
                ['id', Auth::user()->branch_id],
            ])
            ->select('soft_start_date')
            ->first();

        $months = array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December');

        $data = array(
            'fundingOrg'  => $fundingOrg,
            'year'        => Carbon::parse($branch->soft_start_date)->format('Y'),
            'currentYear' => Carbon::parse(Carbon::now())->format('Y'),
            'months'      => $months,
        );

        return view('MFN.Reports.PksfReports.pomis_two_a', $data);
    }

    public function getPksfPomisTwoAViewPart(Request $req)
    {
        $year                 = (is_null($req->year)) ? null : $req->year;
        $month                = (is_null($req->month)) ? null : $req->month;
        $isWithServiceChanrge = (is_null($req->isWithServiceChanrge)) ? null : $req->isWithServiceChanrge;
        $isRoundUp            = (is_null($req->isRoundUp)) ? null : $req->isRoundUp;
        $loanOption           = (is_null($req->loanOption)) ? null : $req->loanOption;
        $fundingOrg           = (is_null($req->fundingOrg)) ? null : $req->fundingOrg;

        $monthStartDate = date('Y-m-d', strtotime($year . '-' . $month . '-01'));
        $monthEndDate   = date('Y-m-t', strtotime($monthStartDate));

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
            ->select('id', 'branch_name')
            ->first();

        $loanData = DB::table('mfn_loans as ml')
            ->where('ml.is_delete', 0)
            ->where('ml.disbursementDate', '<=', $monthEndDate)
            ->where(function ($query) use ($monthStartDate) {
                $query->where('ml.loanCompleteDate', '0000-00-00')
                    ->orWhere('ml.loanCompleteDate', '>=', $monthStartDate);
            })
            ->select('ml.id', 'mfo.name as fundingOrgName', 'mlp.name as productName', 'mlpc.name as productCatName', 'mm.gender')
            ->leftjoin('mfn_loan_products as mlp', 'ml.productId', 'mlp.id')
            ->leftjoin('mfn_loan_product_category as mlpc', 'mlp.productCategoryId', 'mlpc.id')
            ->leftjoin('mfn_funding_orgs as mfo', 'mlp.fundingOrgId', 'mfo.id')
            ->leftjoin('mfn_members as mm', 'ml.memberId', 'mm.id')
            ->where(function ($loanData) use ($fundingOrg) {

                if (!is_null($fundingOrg)) {
                    $loanData->where('mfo.id', $fundingOrg);
                }
            });

        if ($loanOption == 'LoanProd') {
            $loanData = $loanData->groupBy('mlp.id', 'mfo.id', 'mm.gender')->get();
        } else {
            $loanData = $loanData->groupBy('mlp.productCategoryId', 'mfo.id', 'mm.gender')->get();
        }

        $loanIds    = $loanData->pluck('id')->toArray();
        $loanStatus = MfnService::getLoanStatus($loanIds, $monthStartDate, $monthEndDate);

        $pomisTwoAData = array();

        foreach ($loanStatus as $row) {

            $loanInfo = $loanData->where('id', $row['loanId'])->first();

            $pomisTwoAData[$loanInfo->fundingOrgName]['fundingOrgName'] = $loanInfo->fundingOrgName;

            $pomisTwoAData[$loanInfo->fundingOrgName]['products'][$loanInfo->productName]['productName'] = ($loanOption == 'LoanProd') ? $loanInfo->productName : $loanInfo->productCatName;

            $pomisTwoAData[$loanInfo->fundingOrgName]['products'][$loanInfo->productName]['genders'][$loanInfo->gender]['genderName'] = $loanInfo->gender;

            $monthEndLoan = DB::table('mfn_month_end_loans')
                ->where([
                    ['branchId', Auth::user()->branch_id],
                    ['gender', $loanInfo->gender],
                    ['date', date('Y-m-d', strtotime('-1 day', strtotime($monthStartDate)))],
                ])
                ->first();

            $dueEndMonth = 0;
            if (!empty($monthEndLoan)) {
                $dueEndMonth = $monthEndLoan->closingDue;
            }

            $currentMonthTotalAmount = $row['onPeriodReularCollection'] + $row['onPeriodDueCollection'] + $row['onPeriodAdvanceCollection'];
            $newDue                  = $row['onPeriodPayable'] - $row['onPeriodReularCollection'];
            $totalDue                = $dueEndMonth - $row['onPeriodDueCollection'] + $newDue;

            $pomisTwoAData[$loanInfo->fundingOrgName]['products'][$loanInfo->productName]['genders'][$loanInfo->gender]['dueEndMonth'] = ($isRoundUp == 'Yes') ? round($dueEndMonth) : round($dueEndMonth, 2);

            $pomisTwoAData[$loanInfo->fundingOrgName]['products'][$loanInfo->productName]['genders'][$loanInfo->gender]['currentMonthLoanRecoverable'] = ($isRoundUp == 'Yes') ? round($row['onPeriodPayable']) : round($row['onPeriodPayable'], 2);

            $pomisTwoAData[$loanInfo->fundingOrgName]['products'][$loanInfo->productName]['genders'][$loanInfo->gender]['currentMonthRegularAmount'] = ($isRoundUp == 'Yes') ? round($row['onPeriodReularCollection']) : round($row['onPeriodReularCollection'], 2);

            $pomisTwoAData[$loanInfo->fundingOrgName]['products'][$loanInfo->productName]['genders'][$loanInfo->gender]['currentMonthDueAmount'] = ($isRoundUp == 'Yes') ? round($row['onPeriodDueCollection']) : round($row['onPeriodDueCollection'], 2);

            $pomisTwoAData[$loanInfo->fundingOrgName]['products'][$loanInfo->productName]['genders'][$loanInfo->gender]['currentMonthAdvanceAmount'] = ($isRoundUp == 'Yes') ? round($row['onPeriodAdvanceCollection']) : round($row['onPeriodAdvanceCollection'], 2);

            $pomisTwoAData[$loanInfo->fundingOrgName]['products'][$loanInfo->productName]['genders'][$loanInfo->gender]['currentMonthTotalAmount'] = ($isRoundUp == 'Yes') ? round($currentMonthTotalAmount) : round($currentMonthTotalAmount, 2);

            $pomisTwoAData[$loanInfo->fundingOrgName]['products'][$loanInfo->productName]['genders'][$loanInfo->gender]['newDue'] = ($isRoundUp == 'Yes') ? round($newDue) : round($newDue, 2);

            $pomisTwoAData[$loanInfo->fundingOrgName]['products'][$loanInfo->productName]['genders'][$loanInfo->gender]['totalDue'] = ($isRoundUp == 'Yes') ? round($totalDue) : round($totalDue, 2);

            $loanee = 0;
            if ($row['dueAmount'] != 0) {
                $loanee = DB::table('mfn_loan_collections')
                    ->where('loanId', $row['loanId'])
                    ->select('memberId')
                    ->distinct()
                    ->count();
            }

            $pomisTwoAData[$loanInfo->fundingOrgName]['products'][$loanInfo->productName]['genders'][$loanInfo->gender]['totalLonee'] = $loanee;

        }

        $data = array(
            'groupName'            => $group->group_name,
            'branchName'           => $branch->branch_name,
            'isWithServiceChanrge' => ($isWithServiceChanrge == 'Yes') ? 'With Service Charge' : 'Without Service Charge',
            'fundingOrg'           => ($fundingOrg == null) ? 'All' : $fundingOrg,
            'pomisTwoAData'        => $pomisTwoAData,
        );

        return view('MFN.Reports.PksfReports.pomis_tow_a_viewPart', $data);
    }
}
