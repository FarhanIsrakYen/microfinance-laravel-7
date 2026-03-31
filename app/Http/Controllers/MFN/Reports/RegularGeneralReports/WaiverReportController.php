<?php

namespace App\Http\Controllers\MFN\Reports\RegularGeneralReports;

use App\Http\Controllers\Controller;
use App\Services\HrService;
use App\Services\MfnService;
use App\Model\MFN\Samity;
use App\Model\GNL\Branch;
use Auth;
use DB;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DateTime;

class WaiverReportController extends Controller
{   
    public function getWaiverReportIndex(Request $req)
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


        // $fundingOrgData = DB::table('mfn_funding_orgs')
        //     ->where([['is_delete', 0]])
        //     ->select('id', 'name')
        //     ->get();

        // $loanProdCategoryData = DB::table('mfn_loan_product_category')
        //     ->where([['is_delete', 0]])
        //     ->get();

        // $loanProductData = DB::table('mfn_loan_products')
        //     ->where([['is_delete', 0]])
        //     ->get();

        // $savingsProductData = DB::table('mfn_savings_product')
        //     ->where([['is_delete', 0]])
        //     ->get();

        $data = array(
            "branchData"           => $branchs,
            'samityData'           => $samityData,
            // "fundingOrgData"       => $fundingOrgData,
            // "loanProdCategoryData" => $loanProdCategoryData,
            // "loanProductData"      => $loanProductData,
            // 'savingsProductData'    => $savingsProductData,
            'sysDate' => $sysDate,
        );

        return view('MFN.Reports.RegularGeneralReports.waiver_report', $data);
    }
    public function getWaiverData(Request $req)
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

            //samity
            $selected_samity = $req->samity;
            $samityData = Samity::where('branchId', $branchId)->get();

            $waivers = DB::table('mfn_loans as ml')
                    ->where([['ml.is_delete', 0],['ml.branchId',$branchId]])
                    ->join('mfn_loan_waivers', function ($waivers){
                        $waivers->on('mfn_loan_waivers.loanId', '=', 'ml.id')
                            ->where([['mfn_loan_waivers.is_delete', 0]]);
                    })
                    ->join('mfn_loan_products', function ($waivers){
                        $waivers->on('mfn_loan_products.id', '=', 'ml.primaryProductId')
                            ->where([['mfn_loan_products.is_delete', 0]]);
                    })
                    ->join('mfn_members', function ($waivers){
                        $waivers->on('mfn_members.id', '=', 'ml.memberId')
                            ->where([['mfn_members.is_delete', 0]]);
                    })
                    ->join('mfn_samity', function ($waivers){
                        $waivers->on('mfn_samity.id', '=', 'ml.samityId')
                            ->where([['mfn_samity.is_delete', 0]]);
                    })
                    ->where(function ($waivers) use ($selected_samity, $date, $date_to) {
                        if (!empty($selected_samity)) {
                            $waivers->where('ml.samityId', '=', $selected_samity);
                        }
                        if (!empty($date) && !empty($date_to)) {
        
                            $date = new DateTime($date);
                            $date = $date->format('Y-m-d');
        
                            $date_to = new DateTime($date_to);
                            $date_to = $date_to->format('Y-m-d');
        
                            $waivers->whereBetween('mfn_loan_waivers.waiverDate', [$date, $date_to]);
                        }
                    })
                    ->select('mfn_members.memberCode', 
                    'mfn_members.name as memberName',
                    'mfn_samity.samityCode',
                    'mfn_samity.name as samityName',
                    'ml.loanCode',
                    'mfn_loan_products.name as loanProduct',
                    'mfn_loan_waivers.waiverDate',
                    'ml.loanAmount', 
                    'mfn_loan_waivers.amount as waiverAmount')
                    ->orderBy('mfn_members.id')
                    ->get();
            $data = array(
                'waivers' => $waivers,
                'branchData'  => $branchData,
                'FromDate' => $date,
                'samity_selected' => $selected_samity,
                'samityData' => $samityData,
                'sysDate' => $sysDate,
                'toDate' => $date_to,
            );

            return view('MFN.Reports.RegularGeneralReports.waiver_report_table_view', $data);

        } catch (\Throwable $e) {
            //throw $th;
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong',
                'consoleMsg'    => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage()
            );



            return redirect()->back()->with($notification);
        }
        // $accessAbleBranchIds = HrService::getUserAccesableBranchIds();
        // if (!$req->ajax()) {

        //     $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        //     if (Auth::user()->branch_id == 1) {
        //         $branchs = DB::table('gnl_branchs as b')
        //             ->where([['is_delete', 0], ['is_approve', 1], ['is_active', 1], ['id', '>', 1]])
        //             ->whereIn('id', HrService::getUserAccesableBranchIds())
        //             ->select('b.id', 'b.branch_name', 'b.branch_code', DB::raw("CONCAT(b.branch_code,' - ',b.branch_name) as branch"))
        //             ->get();
        //     }

        //     $group = DB::table('gnl_groups')
        //         ->where([['is_delete', 0], ['is_active', 1]])
        //         ->select('id', 'group_name')
        //         ->first();

        //     $branch = DB::table('gnl_branchs')
        //         ->where([
        //             ['is_delete', 0], 
        //             ['is_active', 1],
        //             ['id', Auth::user()->branch_id]
        //         ])
        //         ->select('id', 'branch_name')
        //         ->first();

        //     $samitys = DB::table('mfn_samity as ms')
        //         ->where([['is_delete', 0], ['branchId', Auth::user()->branch_id]])
        //         ->select('ms.id', 'ms.name', 'ms.samityCode', DB::raw("CONCAT(ms.samityCode, ' - ', ms.name) as samity"))
        //         ->get();

        //     $index_data = array(
        //         'sysDate' => Carbon::parse($sysDate)->format('d-m-Y'),
        //         'branchs' => (Auth::user()->branch_id == 1) ? $branchs : Auth::user()->branch_id,
        //         'samitys' => $samitys,
        //         'groupName' => $group->group_name,
        //         'branchName' => $branch->branch_name,
        //     );

        //     return view('MFN.Reports.RegularGeneralReports.waiver_report', $index_data);
        // }

        // $sl = 1;
        // // Searching variable
        // $startDate  = (empty($req->input('startDate'))) ? null : $req->input('startDate');
        // $endDate    = (empty($req->input('endDate'))) ? null : $req->input('endDate');
        // $branchId   = (empty($req->input('branchId'))) ? null : $req->input('branchId');
        // $samityId = (empty($req->input('samityId'))) ? null : $req->input('samityId');

        // $waiverData = DB::table('mfn_loan_waivers as mlw')
        //     ->where('mlw.is_delete', 0)
        //     ->select('mm.name as memberName', 'mm.memberCode', 'ml.loanCode', 'mlp.name as loanProd', 'ms.samityCode', 'ms.name as samityName', 'mlw.waiverDate', 'ml.loanAmount', 'mlw.amount as waiverAmount')
        //     ->leftJoin('mfn_loans as ml', 'mlw.loanId', 'ml.id')
        //     ->leftJoin('mfn_members as mm', 'ml.memberId', 'mm.id')
        //     ->leftJoin('mfn_samity as ms', 'mlw.samityId', 'ms.id')
        //     ->leftJoin('mfn_loan_products as mlp', 'ml.productId', 'mlp.id')
        //     ->whereIn('mlw.branchId', $accessAbleBranchIds)
        //     ->where(function ($waiverData) use ($startDate, $endDate, $branchId, $samityId) {

        //         if (!empty($startDate) && !empty($endDate)) {

        //             $startDate = new DateTime($startDate);
        //             $startDate = $startDate->format('Y-m-d');

        //             $endDate = new DateTime($endDate);
        //             $endDate = $endDate->format('Y-m-d');

        //             $waiverData->whereBetween('mlw.created_at', [$startDate, $endDate]);
        //         }

        //         if (!empty($branchId)) {
        //             $waiverData->where('mlw.branchId', $branchId);
        //         }

        //         if (!empty($samityId)) {
        //             $waiverData->where('mlw.samityId', $samityId);
        //         }
        //     })
        //     ->orderBy('mlw.id', 'ASC')
        //     ->get();

        // $totalData = $waiverData->count();
        // $ttlLoanAmount = $waiverData->sum('loanAmount');
        // $ttlWaiverAmount = $waiverData->sum('waiverAmount');

        // foreach ($waiverData as $key => $row) {
        //     $waiverData[$key]->sl             = $sl++;
        //     $waiverData[$key]->waiverDate     = Carbon::parse($row->waiverDate)->format('d-m-Y');
        //     $waiverData[$key]->loanAmount     = round($row->loanAmount, 2);
        //     $waiverData[$key]->waiverAmount   = round($row->waiverAmount, 2);
        // }

        // $data = array(
        //     "draw"            => intval($req->input('draw')),
        //     "recordsTotal"    => $totalData,
        //     "recordsFiltered" => $totalData,
        //     "totalRow"        => $totalData,
        //     "ttlLoanAmount"   => $ttlLoanAmount,
        //     "ttlWaiverAmount" => $ttlWaiverAmount,
        //     'data'            => $waiverData,
        // );
        // return response()->json($data);
    }


    public function getWriteOffDataIndex(Request $req)
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


        $data = array(
            "branchData"           => $branchs,
            'samityData'           => $samityData,
            'sysDate' => $sysDate,
        );

        return view('MFN.Reports.RegularGeneralReports.writeOff_report', $data);
    }
    public function getWriteOffData(Request $req)
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

            //samity
            $selected_samity = $req->samity;
            $samityData = Samity::where('branchId', $branchId)->get();

            $writeoffs = DB::table('mfn_loans as ml')
                    ->where([['ml.is_delete', 0],['ml.branchId',$branchId]])
                    ->join('mfn_loan_writeoffs', function ($writeoffs){
                        $writeoffs->on('mfn_loan_writeoffs.loanId', '=', 'ml.id')
                            ->where([['mfn_loan_writeoffs.is_delete', 0]]);
                    })
                    ->join('mfn_loan_products', function ($writeoffs){
                        $writeoffs->on('mfn_loan_products.id', '=', 'ml.primaryProductId')
                            ->where([['mfn_loan_products.is_delete', 0]]);
                    })
                    ->join('mfn_members', function ($writeoffs){
                        $writeoffs->on('mfn_members.id', '=', 'ml.memberId')
                            ->where([['mfn_members.is_delete', 0]]);
                    })
                    ->join('mfn_samity', function ($writeoffs){
                        $writeoffs->on('mfn_samity.id', '=', 'ml.samityId')
                            ->where([['mfn_samity.is_delete', 0]]);
                    })
                    ->where(function ($writeoffs) use ($selected_samity, $date, $date_to) {
                        if (!empty($selected_samity)) {
                            $writeoffs->where('ml.samityId', '=', $selected_samity);
                        }
                        if (!empty($date) && !empty($date_to)) {
        
                            $date = new DateTime($date);
                            $date = $date->format('Y-m-d');
        
                            $date_to = new DateTime($date_to);
                            $date_to = $date_to->format('Y-m-d');
        
                            $writeoffs->whereBetween('mfn_loan_writeoffs.writeOffDate', [$date, $date_to]);
                        }
                    })
                    ->select('mfn_members.memberCode', 
                    'mfn_members.name as memberName',
                    'mfn_samity.samityCode',
                    'mfn_samity.name as samityName',
                    'ml.loanCode',
                    'mfn_loan_products.name as loanProduct',
                    'mfn_loan_writeoffs.writeOffDate',
                    'ml.loanAmount', 
                    'mfn_loan_writeoffs.amount as writeOffAmount')
                    ->orderBy('mfn_members.id')
                    ->get();
            $data = array(
                'writeoffs' => $writeoffs,
                'branchData'  => $branchData,
                'FromDate' => $date,
                'samity_selected' => $selected_samity,
                'samityData' => $samityData,
                'sysDate' => $sysDate,
                'toDate' => $date_to,
            );
            return view('MFN.Reports.RegularGeneralReports.writeOff_report_table_view', $data);


        } catch (\Throwable $e) {
            //throw $th;
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong',
                'consoleMsg'    => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage()
            );



            return redirect()->back()->with($notification);
        }
        // $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        // if (!$req->ajax()) {

        //     $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        //     if (Auth::user()->branch_id == 1) {
        //         $branchs = DB::table('gnl_branchs as b')
        //             ->where([['is_delete', 0], ['is_approve', 1], ['is_active', 1], ['id', '>', 1]])
        //             ->whereIn('id', HrService::getUserAccesableBranchIds())
        //             ->select('b.id', 'b.branch_name', 'b.branch_code', DB::raw("CONCAT(b.branch_code,' - ',b.branch_name) as branch"))
        //             ->get();
        //     }

        //     $group = DB::table('gnl_groups')
        //         ->where([['is_delete', 0], ['is_active', 1]])
        //         ->select('id', 'group_name')
        //         ->first();

        //     $branch = DB::table('gnl_branchs')
        //         ->where([
        //             ['is_delete', 0], 
        //             ['is_active', 1],
        //             ['id', Auth::user()->branch_id]
        //         ])
        //         ->select('id', 'branch_name')
        //         ->first();

        //     $samitys = DB::table('mfn_samity as ms')
        //         ->where([['is_delete', 0], ['branchId', Auth::user()->branch_id]])
        //         ->select('ms.id', 'ms.name', 'ms.samityCode', DB::raw("CONCAT(ms.samityCode, ' - ', ms.name) as samity"))
        //         ->get();

        //     $data = array(
        //         'sysDate' => Carbon::parse($sysDate)->format('d-m-Y'),
        //         'branchs' => (Auth::user()->branch_id == 1) ? $branchs : Auth::user()->branch_id,
        //         'samitys' => $samitys,
        //         'groupName' => $group->group_name,
        //         'branchName' => $branch->branch_name,
        //     );

        //     return view('MFN.Reports.RegularGeneralReports.writeOff_report', $data);
        // }

        // $sl = 1;
        // // Searching variable
        // $startDate  = (empty($req->input('startDate'))) ? null : $req->input('startDate');
        // $endDate    = (empty($req->input('endDate'))) ? null : $req->input('endDate');
        // $branchId   = (empty($req->input('branchId'))) ? null : $req->input('branchId');
        // $samityId = (empty($req->input('samityId'))) ? null : $req->input('samityId');

        // $writeOffData = DB::table('mfn_loan_writeoffs as mlw')
        //     ->where('mlw.is_delete', 0)
        //     ->select('mm.name as memberName', 'mm.memberCode', 'ml.loanCode', 'mlp.name as loanProd', 'ms.samityCode', 'ms.name as samityName', 'mlw.writeOffDate', 'ml.loanAmount', 'mlw.amount as writeOffAmount')
        //     ->leftJoin('mfn_loans as ml', 'mlw.loanId', 'ml.id')
        //     ->leftJoin('mfn_members as mm', 'ml.memberId', 'mm.id')
        //     ->leftJoin('mfn_samity as ms', 'mlw.samityId', 'ms.id')
        //     ->leftJoin('mfn_loan_products as mlp', 'ml.productId', 'mlp.id')
        //     ->whereIn('mlw.branchId', $accessAbleBranchIds)
        //     ->where(function ($writeOffData) use ($startDate, $endDate, $branchId, $samityId) {

        //         if (!empty($startDate) && !empty($endDate)) {

        //             $startDate = new DateTime($startDate);
        //             $startDate = $startDate->format('Y-m-d');

        //             $endDate = new DateTime($endDate);
        //             $endDate = $endDate->format('Y-m-d');

        //             $writeOffData->whereBetween('mlw.created_at', [$startDate, $endDate]);
        //         }

        //         if (!empty($branchId)) {
        //             $writeOffData->where('mlw.branchId', $branchId);
        //         }

        //         if (!empty($samityId)) {
        //             $writeOffData->where('mlw.samityId', $samityId);
        //         }
        //     })
        //     ->orderBy('mlw.id', 'ASC')
        //     ->get();

        // $totalData = $writeOffData->count();
        // $ttlLoanAmount = $writeOffData->sum('loanAmount');
        // $ttlWriteOffAmount = $writeOffData->sum('writeOffAmount');


        // foreach ($writeOffData as $key => $row) {
        //     $writeOffData[$key]->sl               = $sl++;
        //     $writeOffData[$key]->writeOffDate     = Carbon::parse($row->writeOffDate)->format('d-m-Y');
        //     $writeOffData[$key]->loanAmount       = round($row->loanAmount, 2);
        //     $writeOffData[$key]->writeOffAmount   = round($row->writeOffAmount, 2);
        // }

        // $data = array(
        //     "draw"            => intval($req->input('draw')),
        //     "recordsTotal"    => $totalData,
        //     "recordsFiltered" => $totalData,
        //     "totalRow"        => $totalData,
        //     "ttlLoanAmount"   => $ttlLoanAmount,
        //     "ttlWriteOffAmount" => $ttlWriteOffAmount,
        //     'data'            => $writeOffData,
        // );

        // return response()->json($data);
    }

    public function getRebateDataIndex(Request $req)
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


        $data = array(
            "branchData"           => $branchs,
            'samityData'           => $samityData,
            'sysDate' => $sysDate,
        );

        return view('MFN.Reports.RegularGeneralReports.rebate_report', $data);
    }
    public function getRebateData(Request $req)
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

            //samity
            $selected_samity = $req->samity;
            $samityData = Samity::where('branchId', $branchId)->get();

            $rebates = DB::table('mfn_loans as ml')
                    ->where([['ml.is_delete', 0],['ml.branchId',$branchId]])
                    ->join('mfn_loan_rebates', function ($rebates){
                        $rebates->on('mfn_loan_rebates.loanId', '=', 'ml.id')
                            ->where([['mfn_loan_rebates.is_delete', 0]]);
                    })
                    ->join('mfn_loan_products', function ($rebates){
                        $rebates->on('mfn_loan_products.id', '=', 'ml.primaryProductId')
                            ->where([['mfn_loan_products.is_delete', 0]]);
                    })
                    ->join('mfn_members', function ($rebates){
                        $rebates->on('mfn_members.id', '=', 'ml.memberId')
                            ->where([['mfn_members.is_delete', 0]]);
                    })
                    ->join('mfn_samity', function ($rebates){
                        $rebates->on('mfn_samity.id', '=', 'ml.samityId')
                            ->where([['mfn_samity.is_delete', 0]]);
                    })
                    ->where(function ($rebates) use ($selected_samity, $date, $date_to) {
                        if (!empty($selected_samity)) {
                            $rebates->where('ml.samityId', '=', $selected_samity);
                        }
                        if (!empty($date) && !empty($date_to)) {
        
                            $date = new DateTime($date);
                            $date = $date->format('Y-m-d');
        
                            $date_to = new DateTime($date_to);
                            $date_to = $date_to->format('Y-m-d');
        
                            $rebates->whereBetween('mfn_loan_rebates.rebateDate', [$date, $date_to]);
                        }
                    })
                    ->select('mfn_members.memberCode', 
                    'mfn_members.name as memberName',
                    'mfn_samity.samityCode',
                    'mfn_samity.name as samityName',
                    'ml.loanCode',
                    'mfn_loan_products.name as loanProduct',
                    'mfn_loan_rebates.rebateDate',
                    'ml.loanAmount', 
                    'mfn_loan_rebates.rebateAmount')
                    ->orderBy('mfn_members.id')
                    ->get();
            $data = array(
                'rebates' => $rebates,
                'branchData'  => $branchData,
                'FromDate' => $date,
                'samity_selected' => $selected_samity,
                'samityData' => $samityData,
                'sysDate' => $sysDate,
                'toDate' => $date_to,
            );
            return view('MFN.Reports.RegularGeneralReports.rebate_report_table_view', $data);


        } catch (\Throwable $e) {
            //throw $th;
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong',
                'consoleMsg'    => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage()
            );



            return redirect()->back()->with($notification);
        }
        // $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        // if (!$req->ajax()) {

        //     $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        //     if (Auth::user()->branch_id == 1) {
        //         $branchs = DB::table('gnl_branchs as b')
        //             ->where([['is_delete', 0], ['is_approve', 1], ['is_active', 1], ['id', '>', 1]])
        //             ->whereIn('id', HrService::getUserAccesableBranchIds())
        //             ->select('b.id', 'b.branch_name', 'b.branch_code', DB::raw("CONCAT(b.branch_code,' - ',b.branch_name) as branch"))
        //             ->get();
        //     }

        //     $group = DB::table('gnl_groups')
        //         ->where([['is_delete', 0], ['is_active', 1]])
        //         ->select('id', 'group_name')
        //         ->first();

        //     $branch = DB::table('gnl_branchs')
        //         ->where([
        //             ['is_delete', 0], 
        //             ['is_active', 1],
        //             ['id', Auth::user()->branch_id]
        //         ])
        //         ->select('id', 'branch_name')
        //         ->first();

        //     $samitys = DB::table('mfn_samity as ms')
        //         ->where([['is_delete', 0], ['branchId', Auth::user()->branch_id]])
        //         ->select('ms.id', 'ms.name', 'ms.samityCode', DB::raw("CONCAT(ms.samityCode, ' - ', ms.name) as samity"))
        //         ->get();

        //     $data = array(
        //         'sysDate' => Carbon::parse($sysDate)->format('d-m-Y'),
        //         'branchs' => (Auth::user()->branch_id == 1) ? $branchs : Auth::user()->branch_id,
        //         'samitys' => $samitys,
        //         'groupName' => $group->group_name,
        //         'branchName' => $branch->branch_name,
        //     );

        //     return view('MFN.Reports.RegularGeneralReports.rebate_report', $data);
        // }

        // $sl = 1;
        // // Searching variable
        // $startDate  = (empty($req->input('startDate'))) ? null : $req->input('startDate');
        // $endDate    = (empty($req->input('endDate'))) ? null : $req->input('endDate');
        // $branchId   = (empty($req->input('branchId'))) ? null : $req->input('branchId');
        // $samityId = (empty($req->input('samityId'))) ? null : $req->input('samityId');

        // $rebateData = DB::table('mfn_loan_rebates as mlr')
        //     ->where('mlr.is_delete', 0)
        //     ->select('mm.name as memberName', 'mm.memberCode', 'ml.loanCode', 'mlp.name as loanProd', 'ms.samityCode', 'ms.name as samityName', 'mlr.rebateDate', 'ml.loanAmount', 'mlr.rebateAmount')
        //     ->leftJoin('mfn_loans as ml', 'mlr.loanId', 'ml.id')
        //     ->leftJoin('mfn_members as mm', 'ml.memberId', 'mm.id')
        //     ->leftJoin('mfn_samity as ms', 'mlr.samityId', 'ms.id')
        //     ->leftJoin('mfn_loan_products as mlp', 'ml.productId', 'mlp.id')
        //     ->whereIn('mlr.branchId', $accessAbleBranchIds)
        //     ->where(function ($rebateData) use ($startDate, $endDate, $branchId, $samityId) {

        //         if (!empty($startDate) && !empty($endDate)) {

        //             $startDate = new DateTime($startDate);
        //             $startDate = $startDate->format('Y-m-d');

        //             $endDate = new DateTime($endDate);
        //             $endDate = $endDate->format('Y-m-d');

        //             $rebateData->whereBetween('mlr.created_at', [$startDate, $endDate]);
        //         }

        //         if (!empty($branchId)) {
        //             $rebateData->where('mlr.branchId', $branchId);
        //         }

        //         if (!empty($samityId)) {
        //             $rebateData->where('mlr.samityId', $samityId);
        //         }
        //     })
        //     ->orderBy('mlr.id', 'ASC')
        //     ->get();

        // $totalData = $rebateData->count();
        // $ttlLoanAmount = $rebateData->sum('loanAmount');
        // $ttlRebateAmount = $rebateData->sum('rebateAmount');


        // foreach ($rebateData as $key => $row) {
        //     $rebateData[$key]->sl               = $sl++;
        //     $rebateData[$key]->rebateDate     = Carbon::parse($row->rebateDate)->format('d-m-Y');
        //     $rebateData[$key]->loanAmount       = round($row->loanAmount, 2);
        //     $rebateData[$key]->rebateAmount   = round($row->rebateAmount, 2);
        // }

        // $data = array(
        //     "draw"            => intval($req->input('draw')),
        //     "recordsTotal"    => $totalData,
        //     "recordsFiltered" => $totalData,
        //     "totalRow"        => $totalData,
        //     "ttlLoanAmount"   => $ttlLoanAmount,
        //     "ttlRebateAmount" => $ttlRebateAmount,
        //     'data'            => $rebateData,
        // );

        // return response()->json($data);
    }

    public function getData(Request $req)
    {
        if ($req->context == 'samity') {

            $samitys = DB::table('mfn_samity as ms')
                ->where([['is_delete', 0], ['branchId', $req->branchId]])
                ->select('ms.id', 'ms.name', 'ms.samityCode', DB::raw("CONCAT(ms.samityCode, ' - ', ms.name) as samity"))
                ->get();

            $data = array(
                'samitys' => $samitys,
            );
        }

        return response()->json($data);
    }
}
