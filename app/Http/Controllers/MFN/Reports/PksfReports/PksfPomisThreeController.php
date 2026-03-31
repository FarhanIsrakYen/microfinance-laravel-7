<?php

namespace App\Http\Controllers\MFN\Reports\PksfReports;

use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class PksfPomisThreeController extends Controller
{
    public function getPksfPomisThreeFilterPart(Request $req)
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

        return view('MFN.Reports.PksfReports.pomis_three_filterPart', $data);
    }

    public function getPksfPomisThreeViewPart(Request $req)
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

        $pomisThreeData = DB::table('mfn_month_end_loans as mmel')
            ->where('mmel.date', $monthEndDate)
            ->select('mmel.id', 'mfo.name as fundingOrgName', 'mlp.name as productName', 'mlpc.name as productCatName', DB::raw('SUM(mmel.standardOutstanding) as standardOutstanding, SUM(mmel.watchfulDue) as watchfulDue, SUM(mmel.watchfulOutstanding) as watchfulOutstanding, SUM(mmel.substandardDue) as substandardDue, SUM(mmel.substandardOutstanding) as substandardOutstanding, SUM(mmel.doubtfulDue) as doubtfulDue, SUM(mmel.doubtfulOutstanding) as doubtfulOutstanding, SUM(mmel.badOutstanding) as badOutstanding, SUM(mmel.outstandingWithMoreThanTwoDueInstallments) as outstandingWithMoreThanTwoDueInstallments, SUM(mmel.savingBalanceOfOverdueLoanee) as savingBalanceOfOverdueLoanee'))
            ->leftjoin('mfn_funding_orgs as mfo', 'mmel.fundingOrgId', 'mfo.id')
            ->leftjoin('mfn_loan_products as mlp', 'mmel.productId', 'mlp.id')
            ->leftjoin('mfn_loan_product_category as mlpc', 'mmel.categoryId', 'mlpc.id')
            ->where(function ($pomisThreeData) use ($fundingOrg) {

                if (!is_null($fundingOrg)) {
                    $pomisThreeData->where('mmel.fundingOrgId', $fundingOrg);
                }
            });

        if ($loanOption == 'LoanProd') {
            $pomisThreeData = $pomisThreeData->groupBy('mmel.productId', 'mmel.fundingOrgId')->get();
        } else {
            $pomisThreeData = $pomisThreeData->groupBy('mmel.categoryId', 'mmel.fundingOrgId')->get();
        }

        $pomisThreeDataArr = array();
        foreach ($pomisThreeData as $row) {

            $pomisThreeDataArr[$row->fundingOrgName]['fundingOrgName']                             = $row->fundingOrgName;
            $pomisThreeDataArr[$row->fundingOrgName]['products'][$row->productName]['productName'] = ($loanOption == 'LoanProd') ? $row->productName : $row->productCatName;

            $pomisThreeDataArr[$row->fundingOrgName]['products'][$row->productName]['standardOutstanding'] = ($isRoundUp == 'Yes') ? round($row->standardOutstanding) : round($row->standardOutstanding, 2);

            $pomisThreeDataArr[$row->fundingOrgName]['products'][$row->productName]['watchfulDue']         = ($isRoundUp == 'Yes') ? round($row->watchfulDue) : round($row->watchfulDue, 2);
            $pomisThreeDataArr[$row->fundingOrgName]['products'][$row->productName]['watchfulOutstanding'] = ($isRoundUp == 'Yes') ? round($row->watchfulOutstanding) : round($row->watchfulOutstanding, 2);

            $pomisThreeDataArr[$row->fundingOrgName]['products'][$row->productName]['substandardDue']      = ($isRoundUp == 'Yes') ? round($row->substandardDue) : round($row->substandardDue, 2);
            $pomisThreeDataArr[$row->fundingOrgName]['products'][$row->productName]['substandardOutstanding'] = ($isRoundUp == 'Yes') ? round($row->substandardOutstanding) : round($row->substandardOutstanding, 2);

            $pomisThreeDataArr[$row->fundingOrgName]['products'][$row->productName]['doubtfulDue']         = ($isRoundUp == 'Yes') ? round($row->doubtfulDue) : round($row->doubtfulDue, 2);
            $pomisThreeDataArr[$row->fundingOrgName]['products'][$row->productName]['doubtfulOutstanding'] = ($isRoundUp == 'Yes') ? round($row->doubtfulOutstanding) : round($row->doubtfulOutstanding, 2);

            $pomisThreeDataArr[$row->fundingOrgName]['products'][$row->productName]['badOutstanding'] = ($isRoundUp == 'Yes') ? round($row->badOutstanding) : round($row->badOutstanding, 2);

            $pomisThreeDataArr[$row->fundingOrgName]['products'][$row->productName]['outstandingWithMoreThanTwoDueInstallments'] = ($isRoundUp == 'Yes') ? round($row->outstandingWithMoreThanTwoDueInstallments) : round($row->outstandingWithMoreThanTwoDueInstallments, 2);

            $pomisThreeDataArr[$row->fundingOrgName]['products'][$row->productName]['savingBalanceOfOverdueLoanee'] = ($isRoundUp == 'Yes') ? round($row->savingBalanceOfOverdueLoanee) : round($row->savingBalanceOfOverdueLoanee, 2);
        }

        $data = array(
            'groupName'            => $group->group_name,
            'branchName'           => $branch->branch_name,
            'isWithServiceChanrge' => ($isWithServiceChanrge == 'Yes') ? 'With Service Charge' : 'Without Service Charge',
            'fundingOrg'           => ($fundingOrg == null) ? 'All' : $fundingOrg,
            'pomisThreeDataArr'    => $pomisThreeDataArr,
        );

        return view('MFN.Reports.PksfReports.pomis_three_viewPart', $data);
    }

}
