<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use DateTime;
use DB;
use Illuminate\Http\Request;
use View;

/* Model Load Start */

// use App\Model\POS\Product;
/* Model Load End */

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
            // ->whewreIn(['id', HRS::getUserAccesableBranchIds()])
            ->count();

        $customer = DB::table('pos_customers')
            ->where([['is_delete', 0], ['is_active', 1]])
            // ->whewreIn(['branch_id', HRS::getUserAccesableBranchIds()])
            ->count();

        $product = DB::table('pos_products')
            ->where([['is_delete', 0], ['is_active', 1]])
            // ->whewreIn(['branch_id', HRS::getUserAccesableBranchIds()])
            ->count();

        $pBrand = DB::table('pos_p_brands')
            ->where([['is_delete', 0], ['is_active', 1]])
            // ->whewreIn(['branch_id', HRS::getUserAccesableBranchIds()])
            ->count();

        $branchID = Common::getBranchId();
        $sysTodayDate = (new Datetime(Common::systemCurrentDate($branchID, 'pos')));

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

        $OBQuery = DB::table('pos_ob_duesales_m')
            ->where([['is_delete', 0], ['is_active', 1],
                ['opening_date', '<=', $sysTodayDate->format('Y-m-d')]])
            ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
            ->where(function ($OBQuery) use ($branchID) {
                if (!empty($branchID) && $branchID != 1) {
                    $OBQuery->where('branch_id', $branchID);
                }
            })
            ->select(
                DB::raw('IFNULL(SUM(total_sales_amount),0) as sales_all,
                IFNULL(SUM(total_collection),0) as collection_all,
                IFNULL(SUM(total_due_amount),0) as due_all')
            )
            ->first();

        $salesQuery = DB::table('pos_sales_m')
            ->where([['is_delete', 0], ['is_active', 1], ['is_opening', 0],
                ['sales_date', '<=', $sysTodayDate->format('Y-m-d')]])
            ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
            ->where(function ($salesQuery) use ($branchID) {
                if (!empty($branchID) && $branchID != 1) {
                    $salesQuery->where('branch_id', $branchID);
                }
            })
            ->select(
                DB::raw('IFNULL(SUM(total_amount),0) as sales_all,
                        IFNULL(SUM(
                            CASE
                                WHEN sales_date LIKE "' . $sysTodayDate->format('Y-m-d') . '"
                                THEN total_amount
                            END
                        ), 0) as sales_today,
                        IFNULL(SUM(
                            CASE
                                WHEN sales_date LIKE "' . $prevDate->format('Y-m-d') . '"
                                THEN total_amount
                            END
                        ), 0) as sales_pre_day,
                        IFNULL(SUM(
                            CASE
                                WHEN sales_date LIKE "' . $sysTodayDate->format('Y-m-') . '%"
                                THEN total_amount
                            END
                        ), 0) as sales_this_month,
                        IFNULL(SUM(
                            CASE
                                WHEN sales_date LIKE "' . $prevMonth->format('Y-m-') . '%"
                                THEN total_amount
                            END
                        ), 0) as sales_pre_month,
                        IFNULL(SUM(
                            CASE
                                WHEN sales_date BETWEEN "' . $cur_yr_start->format('Y-m-d') . '" AND "' . $cur_yr_end->format('Y-m-d') . '"
                                THEN total_amount
                            END
                        ), 0) as sales_this_year,
                        IFNULL(SUM(
                            CASE
                                WHEN sales_date BETWEEN "' . $pre_yr_start->format('Y-m-d') . '" AND "' . $pre_yr_end->format('Y-m-d') . '"
                                THEN total_amount
                            END
                        ), 0) as sales_pre_year')
            )
            ->first();

        $collectionQuery = DB::table('pos_collections')
            ->where([['is_delete', 0], ['is_active', 1], ['is_opening', 0],
                ['collection_date', '<=', $sysTodayDate->format('Y-m-d')]])
            ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
            ->where(function ($collectionQuery) use ($branchID) {
                if (!empty($branchID) && $branchID != 1) {
                    $collectionQuery->where('branch_id', $branchID);
                }
            })
            ->select(
                DB::raw('IFNULL(SUM(collection_amount),0) as collection_all,
                        IFNULL(SUM(
                            CASE
                                WHEN collection_date LIKE "' . $sysTodayDate->format('Y-m-d') . '"
                                THEN collection_amount
                            END
                        ), 0) as collection_today,
                        IFNULL(SUM(
                            CASE
                                WHEN collection_date LIKE "' . $prevDate->format('Y-m-d') . '"
                                THEN collection_amount
                            END
                        ), 0) as collection_pre_day,
                        IFNULL(SUM(
                            CASE
                                WHEN collection_date LIKE "' . $sysTodayDate->format('Y-m-') . '%"
                                THEN collection_amount
                            END
                        ), 0) as collection_this_month,
                        IFNULL(SUM(
                            CASE
                                WHEN collection_date LIKE "' . $prevMonth->format('Y-m-') . '%"
                                THEN collection_amount
                            END
                        ), 0) as collection_pre_month,
                        IFNULL(SUM(
                            CASE
                                WHEN collection_date BETWEEN "' . $cur_yr_start->format('Y-m-d') . '" AND "' . $cur_yr_end->format('Y-m-d') . '"
                                THEN collection_amount
                            END
                        ), 0) as collection_this_year,
                        IFNULL(SUM(
                            CASE
                                WHEN collection_date BETWEEN "' . $pre_yr_start->format('Y-m-d') . '" AND "' . $pre_yr_end->format('Y-m-d') . '"
                                THEN collection_amount
                            END
                        ), 0) as collection_pre_year')
            )
            ->first();

        $dataSet = [
            'sales_all' => $OBQuery->sales_all + $salesQuery->sales_all,
            'sales_today' => $salesQuery->sales_today,
            'sales_pre_day' => $salesQuery->sales_pre_day,
            'sales_this_month' => $salesQuery->sales_this_month,
            'sales_pre_month' => $salesQuery->sales_pre_month,
            'sales_this_year' => $salesQuery->sales_this_year,
            'sales_pre_year' => $salesQuery->sales_pre_year,

            'collection_all' => $OBQuery->collection_all + $collectionQuery->collection_all,
            'collection_today' => $collectionQuery->collection_today,
            'collection_pre_day' => $collectionQuery->collection_pre_day,
            'collection_this_month' => $collectionQuery->collection_this_month,
            'collection_pre_month' => $collectionQuery->collection_pre_month,
            'collection_this_year' => $collectionQuery->collection_this_year,
            'collection_pre_year' => $collectionQuery->collection_pre_year,
            'cur_yr_start' => $cur_yr_start,
            'cur_yr_end' => $cur_yr_end,
            'branchCount' => $branch,
            'customerCount' => $customer,
            'productCount' => $product,
            'brandCount' => $pBrand
        ];

        return view('POS.Dashboard.dashboard', compact('dataSet'));
    }

    public function branchStatus(Request $request)
    {

        $totalData = 0;
        $totalFiltered = $totalData;
        $sl = 0;
        $branchData = array();
        $requestData = $request->all();

        // Query
        $customerData = DB::table('pos_customers')
            ->where([['is_delete', 0], ['is_active', 1]])
            ->get();

        $dayEndData = DB::table('pos_day_end')
            ->where([['is_delete', 0], ['is_active', 1]])
            ->get();

        $salesQuery = DB::table('pos_sales_m')
            ->where([['is_delete', 0], ['is_active', 1]])
            ->select('branch_id',DB::raw("SUM(total_amount) as sales_amount,
                SUM(vat_amount) as vat_amount, SUM(service_charge) as pro_fee, SUM(discount_amount) as discount_amount"))
            ->groupBy('branch_id')
            ->get();

        $collectionsQuery = DB::table('pos_collections')
            ->where([['is_delete', 0], ['is_active', 1]])
            ->select('branch_id',DB::raw("SUM(collection_amount) as coll_amount"))
            ->groupBy('branch_id')
            ->get();

        $branchData = DB::table('gnl_branchs as gb')
            ->where([['gb.is_active', 1], ['gb.is_delete', 0], ['gb.is_approve', 1]])
            ->whereIn('gb.id', HRS::getUserAccesableBranchIds())
            ->select('gb.id',
                'gb.branch_name',
                'gb.branch_code',
                'gb.branch_opening_date',
                'gb.soft_start_date'
            )
            ->orderBy('gb.branch_code', 'ASC')
            ->get();

        $total_balance = 0;
        $DataSet = array();

        foreach ($branchData as $Row) {

            $TempSet = array();

            ///////////////////////////////
            $nCustomer = $customerData->where('branch_id', $Row->id)->count();
            $branch = sprintf("%04d", $Row->branch_code) . " - " . $Row->branch_name;

            ////////////////////
            $branchDate = $dayEndData->where('branch_id', $Row->id)->first();
            if(!empty($branchDate->branch_date)){
                $br_date = new DateTime($branchDate->branch_date);
            }
            else{
                $br_date = new DateTime($Row->soft_start_date);
            }

            $to_date = new DateTime();
            $lag = $to_date->diff($br_date)->format('%R%a');

            if ($lag != 0) {
                $lagText = '<p style="color:red;">' . $lag . '</p>';
            } else {
                $lagText = abs($lag);
            }

            /////////////////
            $sales_amount = 0;
            $vat_amount = 0;
            $discount_amt = 0;
            $pro_fee = 0;
            $collection_amt = 0;

            $salesData = $salesQuery->where('branch_id', $Row->id)->first();
            if($salesData){
                $sales_amount = $salesData->sales_amount;
                // $vat_amount = $salesData->vat_amount;
                // $discount_amt = $salesData->discount_amount;
                // $pro_fee = $salesData->pro_fee;
            }

            $collectionsData = $collectionsQuery->where('branch_id', $Row->id)->first();
            if($collectionsData){
                $collection_amt = $collectionsData->coll_amount;
            }

            // $ttl_balance = $sales_amount + $vat_amount - $total_collection - $pro_fee - $discount_amt;
            $ttl_balance = $sales_amount - $collection_amt;
            $total_balance += $ttl_balance;
            $f_ttl_balance = $ttl_balance;

            $TempSet = [
                'sl' => ++$sl,
                'branch_name' => $branch,
                'branch_opening_date' => date('d-m-Y', strtotime($Row->branch_opening_date)),
                'soft_start_date' => date('d-m-Y', strtotime($Row->soft_start_date)),
                'branch_date' => $br_date->format('d-m-Y'),
                'total_customer' => $nCustomer,
                'last_m_due' => "-",
                'total_due' => "-",
                'total_balance' => "-",
                 // number_format($f_ttl_balance, 2)
                'LAG' => $lagText,
            ];
            $DataSet[] = $TempSet;
        }

        $json_data = array(
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $DataSet,
            "tcustomer" => count($customerData),
            // "total_balance" => number_format($total_balance, 2),
        );

        echo json_encode($json_data);

    }
}
