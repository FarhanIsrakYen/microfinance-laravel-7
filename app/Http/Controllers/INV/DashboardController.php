<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use DateTime;
use DB;
use Illuminate\Http\Request;
use View;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();

        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();
    }

    public function index(Request $request)
    {
        $this->middleware('permission');

        /////////////////////////////////////////////////////////////////////////

        $branch = DB::table('gnl_branchs')
            ->where([['is_delete', 0], ['is_active', 1], ['is_approve', 1]])
            ->count();

        $product = DB::table('inv_products')
            ->where([['is_delete', 0], ['is_active', 1]])
            // ->whewreIn(['branch_id', HRS::getUserAccesableBranchIds()])
            ->count();

        $pBrand = DB::table('inv_p_brands')
            ->where([['is_delete', 0], ['is_active', 1]])
            // ->whewreIn(['branch_id', HRS::getUserAccesableBranchIds()])
            ->count();

        $branchID = Common::getBranchId();
        $sysTodayDate = (new Datetime(Common::systemCurrentDate($branchID, 'inv')));

        // // Previous Working Day
        $prevDate = (new Datetime(Common::systemPreWorkingDay($sysTodayDate->format('Y-m-d'))));

        // // // // ---------------- Previous Month Calculation start
        $prevMonth = clone $sysTodayDate;
        $prevMonth->modify('-1 month');
        // // // // ------------ last day of month (Y-m-t)
        $preMWorkDay = Common::systemMonthWorkingDay(null, $branchID, null, $prevMonth->format('Y-m-') . "01", $prevMonth->format('Y-m-t'));
        if (count($preMWorkDay) == 0) {
            while (count($preMWorkDay) == 0) {
                $prevMonth->modify('-1 month');
                $preMWorkDay = Common::systemMonthWorkingDay(null, $branchID, null, $prevMonth->format('Y-m-') . "01", $prevMonth->format('Y-m-t'));
            }
        }
        // // // // ------ Previous Month Calculation End

        // /// // ----- Current & Previous Year calculation
        $fiscalYearInfo = Common::systemFiscalYear($sysTodayDate->format('Y-m-d'));

        $cur_yr_start = new DateTime($fiscalYearInfo['fy_start_date']);
        $cur_yr_end = new DateTime($fiscalYearInfo['fy_end_date']);

        if ($sysTodayDate < $cur_yr_end) {
            $cur_yr_end = $sysTodayDate;
        }
        /////////////////////////

        $pre_yr_start = clone $cur_yr_start;
        $pre_yr_start->modify('-1 year');

        $pre_yr_end = clone $cur_yr_end;
        $pre_yr_end->modify('-1 year');

        // /// // ----- End Current & Previous Year calculatio

            $dataSet = [
            'cur_yr_start' => $cur_yr_start,
            'cur_yr_end' => $cur_yr_end,
            'branchCount' => $branch,
            'productCount' => $product,
            'brandCount' => $pBrand
        ];

        return view('INV.Dashboard.dashboard', compact('dataSet'));
    }

    public function branchStatus(Request $request)
    {

        $totalData = 0;
        $totalFiltered = $totalData;
        $sl = 0;
        $branchData = array();
        $requestData = $request->all();

        // Query
        $branchData = DB::table('gnl_branchs as gb')
            ->where([['gb.is_active', 1], ['gb.is_delete', 0], ['gb.is_approve', 1]])
            ->whereIn('gb.id', HRS::getUserAccesableBranchIds())
            ->select('gb.id',
                'gb.branch_name',
                'gb.branch_code',
                'gb.branch_opening_date',
                'gb.soft_start_date',
                'pde.branch_date',
            )
            ->leftjoin('inv_day_end as pde', function ($branchData) {
                $branchData->on('gb.id', 'pde.branch_id')
                    ->where('pde.is_active', 1)
                    ->where('pde.is_delete', 0);
            })
            ->orderBy('gb.branch_code', 'ASC')
            ->groupBy('gb.id')
            ->get();

        $total_row = count($branchData);
        $tcustomer = $branchData->sum('nCustomer');
        
        $total_balance = 0;
        $DataSet = array();
        // dd($total_sales);
        foreach ($branchData as $Row) {
            // dd($Row);
            $TempSet = array();

            //  $lag = (int)((strtotime($br_date) - strtotime($sysDate))/(60*60*24));

            $branch = sprintf("%04d", $Row->branch_code) . " - " . $Row->branch_name;

            if(!empty($Row->branch_date)){
                $br_date = new DateTime($Row->branch_date);
            }
            else{
                $br_date = new DateTime($Row->soft_start_date);
            }

            $to_date = new DateTime();
            $lag = $to_date->diff($br_date)->format('%R%a');

            if ($lag != 0) {
                $lagText = '<p style="color:red;">' . $lag . '</p>';
            } else {
                $lagText = $lag;
            }

            $f_ttl_balance = 0;
            $TempSet = [
                'sl' => ++$sl,
                'branch_name' => $branch,
                'branch_opening_date' => date('d-m-Y', strtotime($Row->branch_opening_date)),
                'soft_start_date' => date('d-m-Y', strtotime($Row->soft_start_date)),
                'branch_date' => $br_date->format('d-m-Y'),
                'LAG' => $lagText,
            ];
            $DataSet[] = $TempSet;
        }

        $json_data = array(
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $DataSet,
        );

        echo json_encode($json_data);

    }
}
