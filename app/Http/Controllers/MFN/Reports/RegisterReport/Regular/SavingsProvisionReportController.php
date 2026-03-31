<?php

namespace App\Http\Controllers\MFN\Reports\RegisterReport\Regular;

use App\Http\Controllers\Controller;
use App\Services\HrService;
use App\Services\MfnService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class SavingsProvisionReportController extends Controller
{
    public function getFilterPart(Request $req)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $branchs = DB::table('gnl_branchs as b')
            ->where([['is_delete', 0], ['is_approve', 1], ['is_active', 1], ['id', '>', 1]])
        // ->whereIn('id', HrService::getUserAccesableBranchIds())
            ->select('b.id', DB::raw("CONCAT(b.branch_code,' - ',b.branch_name) as branch"))
            ->get();

        $samitys = DB::table('mfn_samity')
            ->where('is_delete', 0)
            ->select('id', DB::raw('CONCAT(samityCode, " - ", name) as samity'))
            ->get();

        $data = array(
            'sysDate' => Carbon::parse($sysDate)->format('d-m-Y'),
            'branchs' => $branchs,
            'samitys' => $samitys,
        );

        return view('MFN.Reports.RegisterReport.Regular.SavingsProvision.filter_part', $data);
    }

    public function getSingleTableView(Request $req)
    {
        static $savingsProvision;
        static $savingsProvisionProduct;
        static $view;

        $branchId = (is_null($req->branchId)) ? null : $req->branchId;
        $samityId = (is_null($req->samityId)) ? null : $req->samityId;
        $dateFrom = (is_null($req->dateFrom)) ? null : $req->dateFrom;
        $dateTo   = (is_null($req->dateTo)) ? null : $req->dateTo;
        $dateFrom = Carbon::parse($dateFrom)->format('Y-m-d');
        $dateTo   = Carbon::parse($dateTo)->format('Y-m-d');

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

        if (is_null($samityId)) {

            $savingProvisionData = DB::table('mfn_savings_provision_details as mspd')
                ->where('mspd.branchId', $branchId)
                ->whereBetween('msp.provisionDate', [$dateFrom, $dateTo])
                ->leftjoin('mfn_savings_provision as msp', 'mspd.provisionId', 'msp.id')
                ->leftjoin('mfn_savings_accounts as msa', 'mspd.accountId', 'msa.id')
                ->leftjoin('mfn_members as mm', 'msa.memberId', 'mm.id')
                ->leftjoin('mfn_loan_products as mlp', 'mm.primaryProductId', 'mlp.id')     
                ->leftjoin('mfn_samity as ms', 'mspd.samityId', 'ms.id');

            $tempData = clone $savingProvisionData;

            $savingsProvision = $tempData
                ->select('ms.samityCode as samityId', 'ms.name as samityName', 'mlp.shortName as primaryProduct', DB::raw('ROUND(SUM(mspd.provisionAmount), 2) as provisionAmount'))
                ->groupBy('mspd.samityId', 'ms.id')
                ->get();

            $savingsProvisionProduct = $savingProvisionData
                ->select('mlp.shortName as primaryProduct', DB::raw('ROUND(SUM(mspd.provisionAmount), 2) as provisionAmount'))
                ->groupBy('mm.primaryProductId', 'mlp.id')
                ->get();

            $view = 'MFN.Reports.RegisterReport.Regular.SavingsProvision.all_table_view';

        } else {

            $savingProvisionData = DB::table('mfn_savings_provision_details as mspd')
                ->where([['mspd.branchId', $branchId], ['mspd.samityId', $samityId]])
                ->whereBetween('msp.provisionDate', [$dateFrom, $dateTo])
                ->leftjoin('mfn_savings_provision as msp', 'mspd.provisionId', 'msp.id')
                ->leftjoin('mfn_savings_accounts as msa', 'mspd.accountId', 'msa.id')
                ->leftjoin('mfn_members as mm', 'msa.memberId', 'mm.id')
                ->leftjoin('mfn_loan_products as mlp', 'mm.primaryProductId', 'mlp.id');

            $tempData = clone $savingProvisionData;

            $savingsProvision = $tempData
                ->select('mm.memberCode as memberId', 'mm.name as memberName', 'mlp.shortName as primaryProduct', 'msa.accountCode as savingsId', 'msa.openingDate as savingOpeningDate', 'mspd.dateFrom', 'mspd.dateTo', DB::raw('ROUND(SUM(mspd.provisionAmount), 2) as provisionAmount, DATEDIFF(mspd.dateTo, mspd.dateFrom) as duration'))
                ->groupBy('mspd.samityId', 'mspd.accountId')
                ->get();

            $savingsProvisionProduct = $savingProvisionData
                ->select('mlp.shortName as primaryProduct', DB::raw('ROUND(SUM(mspd.provisionAmount), 2) as provisionAmount'))
                ->groupBy('mm.primaryProductId', 'mlp.id')
                ->get();

            $view = 'MFN.Reports.RegisterReport.Regular.SavingsProvision.single_table_view';

        }


        $data = array(
            'groupName'               => $group->group_name,
            'branch'                  => $branch->branch_code . ' - ' . $branch->branch_name,
            'branchAddress'           => $branch->branch_addr,
            'dateFrom'                => Carbon::parse($dateFrom)->format('d-m-Y'),
            'dateTo'                  => Carbon::parse($dateTo)->format('d-m-Y'),
            'savingsProvision'        => $savingsProvision,
            'savingsProvisionProduct' => $savingsProvisionProduct,
        );

        return view($view, $data);
    }

    public function getData(Request $req)
    {
        if ($req->context == 'samity') {

            $samitys = DB::table('mfn_samity')
                ->where('branchId', $req->branchId)
                ->select('id', DB::raw('CONCAT(samityCode, " - ", name) as samity'))
                ->get();

            $samityHtml = '<option value="">All Samity</option>';

            foreach ($samitys as $row) {
                $samityHtml .= '<option value="' . $row->id . '">' . $row->samity . '</option>';
            }

            $data = array(
                'samityHtml' => $samityHtml,
            );
        }

        return response()->json($data);
    }
}
