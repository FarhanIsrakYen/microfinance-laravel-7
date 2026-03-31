<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\POS\DayEnd;
use App\Model\POS\MonthEnd;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\PosService as POSS;
use App\Services\RoleService as Role;
use DateTime;
use DB;
use Illuminate\Http\Request;

class MonthEndController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Ordering Variable
            $columns = [
                0 => 'id',
                2 => 'month_date',
                // 3 => 'total_working_day',
                // 'total_current_month_customer',
                // 'total_product_quantity',
                // 'total_current_month_sales_amount',
                // 'total_current_month_collection',
                // 'total_current_month_due',
            ];

            // Datatable Pagination Variable

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $startDate = (empty($request->input('SDate'))) ? null : $request->input('SDate');
            $endDate = (empty($request->input('EDate'))) ? null : $request->input('EDate');
            $branchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');

            // Query
            $monthEndData = DB::table('pos_month_end as mEnd')
                ->whereIn('mEnd.branch_id', HRS::getUserAccesableBranchIds())
                ->where([['mEnd.is_delete', 0], ['mEnd.is_active', 0]])
                ->select('mEnd.*', 'br.branch_name', 'br.branch_code')
                ->leftjoin('gnl_branchs as br', function ($monthEndData) {
                    $monthEndData->on('mEnd.branch_id', '=', 'br.id')
                        ->where('br.is_approve', 1);
                })
                ->where(function ($monthEndData) use ($search, $startDate, $endDate, $branchID) {
                    if (!empty($search)) {
                        $monthEndData->where('mEnd.branch_id', 'LIKE', "%{$search}%")
                            ->orWhere('mEnd.month_date', 'LIKE', "%{$search}%")
                            ->orWhere('mEnd.total_working_day', 'LIKE', "%{$search}%")
                            ->orWhere('mEnd.total_current_month_customer', 'LIKE', "%{$search}%")
                            ->orWhere('mEnd.total_product_quantity', 'LIKE', "%{$search}%")
                            ->orWhere('mEnd.total_current_month_sales_amount', 'LIKE', "%{$search}%")
                            ->orWhere('mEnd.total_current_month_collection', 'LIKE', "%{$search}%")
                            ->orWhere('mEnd.total_current_month_due', 'LIKE', "%{$search}%")
                            ->orWhere('mEnd.is_active', 'LIKE', "%{$search}%")
                            ->orWhere('br.branch_code', 'LIKE', "%{$search}%")
                            ->orWhere('br.branch_name', 'LIKE', "%{$search}%");
                    }

                    if (!empty($branchID)) {
                        $monthEndData->where('mEnd.branch_id', $branchID);
                    }

                    if (!empty($startDate) && !empty($endDate)) {

                        $startDate = new DateTime($startDate);
                        $startDate = $startDate->format('Y-m-d');

                        $endDate = new DateTime($endDate);
                        $endDate = $endDate->format('Y-m-d');

                        $monthEndData->whereBetween('mEnd.month_date', [$startDate, $endDate]);
                    }
                })
                ->orderBy('mEnd.month_date', 'DESC')
                ->orderBy('mEnd.id', 'DESC')
                ->orderBy($order, $dir);

            $tempQueryData = clone $monthEndData;
            $monthEndData = $monthEndData->offset($start)->limit($limit)->get();

            $totalData = DB::table('pos_month_end')
                ->where([['is_delete', 0], ['is_active', 0]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;
            if (!empty($search) || !empty($startDate) || !empty($endDate) || !empty($branchID)) {
                $totalFiltered = $tempQueryData->count();
            }

            $month_end_group = DB::table('pos_month_end')
                ->where([['is_delete', 0], ['is_active', 0]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->select('branch_id', 'month_date')
                ->orderBy('month_date', 'DESC')
                ->get();

            $month_end_group = $month_end_group->groupBy('branch_id');

            // $month_end_group = $monthEndData->groupBy('branch_id');

            $DataSet = array();
            $i = $start;

            foreach ($monthEndData as $Row) {

                $ignoreArray = array();
                if (isset($month_end_group[$Row->branch_id])) {

                    if ($month_end_group[$Row->branch_id]->max('month_date') != $Row->month_date) {
                        $ignoreArray = ['delete'];
                    }
                }

                // if (isset($month_end_group[$Row->branch_id])) {

                //     if ($month_end_group[$Row->branch_id]->toarray()[0]->month_date != $Row->month_date) {
                //         $ignoreArray = ['delete'];
                //     }
                // }

                // if ($Row->is_active == 0) {
                //     $TempSet['is_active'] = '<span class="text-danger">Close</span>';
                // } else {
                //     $TempSet['is_active'] = '<span class="text-primary">Active</span>';
                // }

                $TempSet = array();
                $TempSet = [
                    'id' => ++$i,
                    'branch_id' => (!empty($Row->branch_name)) ? $Row->branch_name . "(" . $Row->branch_code . ")" : "",
                    'month_date' => (new DateTime($Row->month_date))->format('M-Y'),
                    'total_working_day' => $Row->total_working_day,
                    'total_current_month_customer' => $Row->total_current_month_customer,
                    'total_product_quantity' => $Row->total_product_quantity,
                    'total_current_month_sales_amount' => $Row->total_current_month_sales_amount,
                    'total_current_month_collection' => $Row->total_current_month_collection,
                    'total_current_month_due' => $Row->total_current_month_due,
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->monthend_no, $ignoreArray),
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
        } else {
            return view('POS.MonthEnd.index');
        }

    }

    public function checkDayEndData(Request $request)
    {
        if ($request->ajax()) {

            $branchID = $request->branchID;
            $CompanyID = Common::getCompanyId();

            //-----get month end data according to branch which is requested
            $monthEndData = DB::table('pos_month_end')
            ->where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])->first();

            if (!empty($monthEndData)) {
                $MonthSrt = (new DateTime($monthEndData->month_start_date))->format('Y-m-d');
            } else {
                $BranchData = DB::table('gnl_branchs')->where([['id', $branchID], ['is_approve', 1], ['is_active', 1], ['is_delete', 0]])->first();

                if ($BranchData) {
                    $MonthSrt = (new DateTime($BranchData->soft_start_date))->format('Y-m-d');
                } else {
                    return response()->json(array("isValidBranch" => false));exit;
                }
            }

            $date = new DateTime($MonthSrt);
            $date->modify('last day of this month');
            $MonthEnd = $date->format('Y-m-d');

            $workingDays = Common::systemMonthWorkingDay($CompanyID, $branchID, null, $MonthSrt);

            //get total day end data in current month
            $dayEndData = DB::table('pos_day_end')
                ->where([['branch_id', $branchID], ['is_active', 0], ['is_delete', 0]])
                ->whereBetween('branch_date', [$MonthSrt . " 00:00:00", $MonthEnd . " 23:59:59"])
                ->pluck('branch_date')
                ->toArray();

            if (count($dayEndData) > 0) {

                //diffBtwnWDNDE = difference between working day & day end
                $diffBtwnWDNDE = array_diff($workingDays, $dayEndData);

                if (empty($diffBtwnWDNDE)) {
                    return response()->json(array("isDayEndCheck" => true));
                } else {
                    return response()->json(array("isDayEndCheck" => false));
                }
            } else {
                return response()->json(array("isDayEndCheck" => false));
            }

        }
    }

    public function executeMonthEnd(Request $request)
    {

        $notification = array();

        if ($request->ajax()) {

            $CompanyID = $request->company_id;
            $branchID = $request->branchID;

            //-----get month end data according to branch which is requested
            $monthEndData = MonthEnd::where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])->first();

            // MonthEnd::create($newMonthData);

            if (!empty($monthEndData)) {

                $MonthSrt = (new DateTime($monthEndData->month_start_date))->format('Y-m-d');

                $date = new DateTime($MonthSrt);
                $date->modify('last day of this month');
                $MonthEnd = $date->format('Y-m-d');

                $workingDays = count(Common::systemMonthWorkingDay($CompanyID, $branchID, null, $MonthSrt));

                // // ## Customer Count
                $customerQuery = DB::table('pos_customers')
                    ->where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])
                    ->whereDate('created_at', '<=', $MonthEnd . " 23:59:59");

                $totalCustomer = $customerQuery->count();
                $curTtlCustomer = $customerQuery->whereBetween('created_at', [$MonthSrt . " 00:00:00", $MonthEnd . " 23:59:59"])->count();

                $tCMNProduct = 0;
                $ttlProduct = 0;
                $tCMProdQtn = 0;

                // // ## Sales Amount
                $salesQuery = DB::table('pos_sales_m')
                    ->where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])
                    ->whereDate('sales_date', '<=', $MonthEnd);

                $tSalesAmt = $salesQuery->sum('total_amount');
                $tCMSalesAmt = $salesQuery->whereBetween('sales_date', [$MonthSrt, $MonthEnd])->sum('total_amount');

                // // ## Collection Amount
                $collectionQuery = DB::table('pos_collections')
                    ->where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])
                    ->whereDate('collection_date', '<=', $MonthEnd);

                $ttlCllection = $collectionQuery->sum('collection_amount');
                $tCMCllection = $collectionQuery->whereBetween('collection_date', [$MonthSrt, $MonthEnd])->sum('collection_amount');

                // // ## Collection Amount
                $purchaseQuery = DB::table('pos_purchases_m')
                    ->where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])
                    ->whereDate('purchase_date', '<=', $MonthEnd);

                $totalPurchase = $purchaseQuery->sum('total_amount');
                $tCMPurchase = $purchaseQuery->whereBetween('purchase_date', [$MonthSrt, $MonthEnd])->sum('total_amount');

                $ttlCMDue = 0;
                $totalDue = 0;

                $monthEndData->total_working_day = $workingDays;
                $monthEndData->total_current_month_customer = $curTtlCustomer;
                $monthEndData->total_customer = $totalCustomer;
                $monthEndData->total_current_month_product_quantity = $tCMNProduct;
                $monthEndData->total_product_quantity = $ttlProduct;
                $monthEndData->total_current_month_sales_amount = $tCMSalesAmt;
                $monthEndData->total_sales_amount = $tSalesAmt;
                $monthEndData->total_current_month_collection = $tCMCllection;
                $monthEndData->total_collection = $ttlCllection;
                $monthEndData->total_current_month_purchases = $tCMPurchase;
                $monthEndData->total_purchases = $totalPurchase;
                $monthEndData->total_current_month_due = $ttlCMDue;
                $monthEndData->total_due = $totalDue;

                $monthEndData->is_active = 0;
                $monthEndData->month_end_date = $MonthEnd;

                $isUpdate = $monthEndData->save();

                if ($isUpdate) {

                    $nextMonthDate = new DateTime($MonthSrt);
                    $nextMonthDate->modify('first day of next month');

                    $haveNextData = MonthEnd::where(['branch_id' => $branchID, 'month_date' => $nextMonthDate->format('Y-m')])->first();

                    if ($haveNextData) {
                        $haveNextData->is_active = 1;
                        $haveNextData->is_delete = 0;
                        $haveNextData->month_start_date = $nextMonthDate->format('Y-m-d');

                        $isInsert = $haveNextData->save();
                    } else {
                        $newMonthData = array();
                        $newMonthData['monthend_no'] = POSS::generateMonthendNo($branchID);
                        $newMonthData['branch_id'] = $branchID;
                        // $newMonthData['branch_code'] = $monthEndData->branch_code;
                        $newMonthData['company_id'] = $CompanyID;

                        $newMonthData['month_date'] = $nextMonthDate->format('Y-m');
                        $newMonthData['month_start_date'] = $nextMonthDate->format('Y-m-d');

                        // $monthDate = $nextMonthDate->format('Y-m');
                        // $date = new DateTime($MonthSrt);
                        // $date->modify('first day of next month');
                        // $nextMonthSD = $date->format('Y-m-d');
                        // $newMonthData['month_start_date'] = $nextMonthSD;

                        $newMonthData['is_active'] = 1;
                        $isInsert = MonthEnd::create($newMonthData);
                    }

                    if ($isInsert) {
                        $notification = array(
                            'message' => 'Successfull to Month End Executed',
                            'alert_type' => 'success',
                        );
                    } else {
                        $notification = array(
                            'message' => 'Unsuccessful to Month End Executed. Please try again !!!',
                            'alert_type' => 'error',
                        );
                    }
                } else {
                    $notification = array(
                        'message' => 'Unsuccessful to Month End Executed',
                        'alert_type' => 'error',
                    );
                }

                // return response($notification);exit;
            } else {

                /// else go branch table and set branch date and execute end
                $BranchData = DB::table('gnl_branchs')->where([['id', $branchID], ['is_approve', 1], ['is_active', 1], ['is_delete', 0]])->first();

                if ($BranchData) {
                    $branch_soft_start_date = new DateTime($BranchData->soft_start_date);
                    $branch_soft_start_date = $branch_soft_start_date->format('Y-m-d');

                    $MonthSrt = $branch_soft_start_date;

                    $date = new DateTime($MonthSrt);
                    $date->modify('last day of this month');
                    $MonthEnd = $date->format('Y-m-d');

                    $haveNextData = MonthEnd::where(['branch_id' => $branchID, 'month_date' => $date->format('Y-m')])->first();

                    if ($haveNextData) {
                        $haveNextData->is_active = 1;
                        $haveNextData->is_delete = 0;

                        $isInsert = $haveNextData->save();
                    } else {
                        $workingDays = count(Common::systemMonthWorkingDay($CompanyID, $branchID, null, $MonthSrt));

                        // // ## Customer Count
                        $customerQuery = DB::table('pos_customers')
                            ->where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])
                            ->whereDate('created_at', '<=', $MonthEnd . " 23:59:59");

                        $totalCustomer = $customerQuery->count();
                        $curTtlCustomer = $customerQuery->whereBetween('created_at', [$MonthSrt . " 00:00:00", $MonthEnd . " 23:59:59"])->count();

                        $tCMNProduct = 0;
                        $ttlProduct = 0;
                        $tCMProdQtn = 0;

                        // // ## Sales Amount
                        $salesQuery = DB::table('pos_sales_m')
                            ->where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])
                            ->whereDate('sales_date', '<=', $MonthEnd);

                        $tSalesAmt = $salesQuery->sum('total_amount');
                        $tCMSalesAmt = $salesQuery->whereBetween('sales_date', [$MonthSrt, $MonthEnd])->sum('total_amount');

                        // // ## Collection Amount
                        $collectionQuery = DB::table('pos_collections')
                            ->where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])
                            ->whereDate('collection_date', '<=', $MonthEnd);

                        $ttlCllection = $collectionQuery->sum('collection_amount');
                        $tCMCllection = $collectionQuery->whereBetween('collection_date', [$MonthSrt, $MonthEnd])->sum('collection_amount');

                        // // ## Collection Amount
                        $purchaseQuery = DB::table('pos_purchases_m')
                            ->where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])
                            ->whereDate('purchase_date', '<=', $MonthEnd);

                        $totalPurchase = $purchaseQuery->sum('total_amount');
                        $tCMPurchase = $purchaseQuery->whereBetween('purchase_date', [$MonthSrt, $MonthEnd])->sum('total_amount');

                        $ttlCMDue = 0;
                        $totalDue = 0;

                        $monthData = array();
                        $monthData['monthend_no'] = POSS::generateMonthendNo($branchID);
                        $monthData['branch_id'] = $branchID;
                        // $monthData['branch_code'] = $BranchCode;
                        $monthData['company_id'] = $CompanyID;
                        $monthData['total_working_day'] = $workingDays;

                        $date = new DateTime($branch_soft_start_date);
                        $monthDate = $date->format('Y-m');
                        $monthData['month_date'] = $monthDate;

                        $date = new DateTime($branch_soft_start_date);
                        $date->modify('last day of this month');
                        $monthLastDate = $date->format('Y-m-d');

                        $monthData['month_start_date'] = $branch_soft_start_date;
                        $monthData['month_end_date'] = $monthLastDate;
                        $monthData['is_active'] = 0;

                        $monthData['total_current_month_customer'] = $curTtlCustomer;
                        $monthData['total_customer'] = $totalCustomer;
                        $monthData['total_current_month_product_quantity'] = $tCMNProduct;
                        $monthData['total_product_quantity'] = $ttlProduct;
                        $monthData['total_current_month_sales_amount'] = $tCMSalesAmt;
                        $monthData['total_sales_amount'] = $tSalesAmt;
                        $monthData['total_current_month_collection'] = $tCMCllection;
                        $monthData['total_collection'] = $ttlCllection;
                        $monthData['total_current_month_purchases'] = $tCMPurchase;
                        $monthData['total_purchases'] = $totalPurchase;
                        $monthData['total_current_month_due'] = $ttlCMDue;
                        $monthData['total_due'] = $totalDue;

                        $isInsert = MonthEnd::create($monthData);
                    }

                    if ($isInsert) {

                        $nextMonthDate = new DateTime($MonthSrt);
                        $nextMonthDate->modify('first day of next month');

                        $haveNextData = MonthEnd::where(['branch_id' => $branchID, 'month_date' => $nextMonthDate->format('Y-m')])->first();

                        if ($haveNextData) {
                            $haveNextData->is_active = 1;
                            $haveNextData->is_delete = 0;
                            $haveNextData->month_start_date = $nextMonthDate->format('Y-m-d');

                            $isInsert = $haveNextData->save();
                        } else {
                            $newMonthData = array();

                            $date = new DateTime($branch_soft_start_date);
                            $date->modify('first day of next month');
                            $nextMonthSD = $date->format('Y-m-d');
                            $newMonthData['monthend_no'] = POSS::generateMonthendNo($branchID);
                            $newMonthData['branch_id'] = $branchID;
                            // $newMonthData['branch_code'] = $BranchCode;
                            $newMonthData['company_id'] = $CompanyID;

                            $date = new DateTime($branch_soft_start_date);
                            $date->modify('first day of next month');
                            $monthDate = $date->format('Y-m');

                            $newMonthData['month_date'] = $monthDate;

                            $date = new DateTime($branch_soft_start_date);
                            $date->modify('first day of next month');
                            $nextMonthSD = $date->format('Y-m-d');

                            $newMonthData['month_start_date'] = $nextMonthSD;
                            $newMonthData['is_active'] = 1;

                            $isInsert = MonthEnd::create($newMonthData);
                        }

                        if ($isInsert) {
                            $notification = array(
                                'message' => 'Successfull to Month End Executed',
                                'alert_type' => 'success',
                            );
                        }
                        // return redirect()->back()->with($notification);
                    } else {

                        $notification = array(
                            'message' => 'Unsuccessful to Month End Executed',
                            'alert_type' => 'error',
                        );
                        // return redirect()->back()->with($notification);
                    }
                } else {
                    $notification = array(
                        'message' => 'There is no valid branch. Please try again !!!',
                        'alert_type' => 'error',
                    );
                }
            }
        }

        // return response($notification);exit;

        echo json_encode($notification);
        exit;
    }

    public function isDelete(Request $request)
    {

        $monthEndId = $request->monthEndId;

        $monthEndData = MonthEnd::where([['monthend_no', $monthEndId]])->first();

        if (!empty($monthEndData)) {

            $branchId = $monthEndData->branch_id;
            $monthDate = $monthEndData->month_date;

            // $lastday = cal_days_in_month(CAL_GREGORIAN, $monthDateEx[1], $monthDateEx[0]);

            $monthEndDate = new DateTime($monthDate . '-01');
            $monthEndDate->modify('last day of this month');
            $monthEndDate = $monthEndDate->format('Y-m-d');
            // dd($monthEndData->month_date,$monthEndDate);

            $dayEndData = DayEnd::where('branch_id', $branchId)
                ->where([['is_delete', 0], ['is_active', 0]])
                ->where('branch_date', '>', $monthEndDate)
                ->count();

            // dd($dayEndData );$monthEndData

            if ($dayEndData == 0) {

                $monthEnds = MonthEnd::where('branch_id', $branchId)->where('month_date', '>', $monthDate)->get();
                // dd($monthEnds);
                foreach ($monthEnds as $monthEnd) {
                    $monthEnd->is_delete = 1;
                    $monthEnd->update();
                }

                // MonthEnd::where('branch_id', $branchId)->where('month_date', '>', $monthDate)->get()->each->delete();
                //
                $monthEndData = MonthEnd::where('branch_id', $branchId)
                    ->where('month_date', $monthDate)
                    ->orderBy('monthend_no', 'DESC')
                    ->first();

                // dd( $monthEndData);
                if (!empty($monthEndData)) {

                    $monthEndData->is_active = 1;
                    $monthEndData->update();
                    return response()->json(array("isDelete" => true));
                } else {
                    return response()->json(array("isDelete" => false));
                }
            } else {
                return response()->json(array('isDelete' => false));
            }
        } else {
            return response()->json(array("isDelete" => false));
        }
    }
}
