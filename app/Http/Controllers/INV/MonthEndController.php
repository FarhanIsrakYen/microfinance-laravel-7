<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\INV\DayEnd;
use App\Model\INV\MonthEnd;
use App\Model\INV\PurchaseMaster;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\InvService as INVS;
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
                'id',
                'branch_id',
                'month_date',
                'total_working_day',
                'total_product_quantity',
                'is_active',
                'action',
            ];

            // Datatable Pagination Variable
            $totalData = MonthEnd::whereIn('inv_month_end.branch_id', HRS::getUserAccesableBranchIds())->count();
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
            $sl = $start + 1;

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $SDate = (empty($request->input('SDate'))) ? null : $request->input('SDate');
            $EDate = (empty($request->input('EDate'))) ? null : $request->input('EDate');
            $BranchID = (empty($request->input('BranchID'))) ? null : $request->input('BranchID');

            // Query
            $monthEndData = DB::table('inv_month_end as mEnd')
                ->whereIn('mEnd.branch_id', HRS::getUserAccesableBranchIds())
                ->where([['mEnd.is_delete', 0], ['mEnd.is_active', 0]])
                ->select('mEnd.*', 'br.branch_name')
                ->leftjoin('gnl_branchs as br', function ($monthEndData) {
                    $monthEndData->on('mEnd.branch_id', '=', 'br.id')
                        ->where('br.is_approve', 1);
                })
                ->where(function ($monthEndData) use ($search, $SDate, $EDate, $BranchID) {
                    if (!empty($search)) {
                        $monthEndData->where('mEnd.branch_id', 'LIKE', "%{$search}%")
                            ->orWhere('mEnd.month_date', 'LIKE', "%{$search}%")
                            ->orWhere('mEnd.total_working_day', 'LIKE', "%{$search}%")
                            ->orWhere('mEnd.total_product_quantity', 'LIKE', "%{$search}%")
                            ->orWhere('br.branch_name', 'LIKE', "%{$search}%");
                    }

                    if (!empty($BranchID)) {
                        $monthEndData->where('mEnd.branch_id', '=', $BranchID);
                    }

                    if (!empty($SDate) && !empty($EDate)) {

                        $SDate = new DateTime($SDate);
                        $SDate = $SDate->format('Y-m-d');

                        $EDate = new DateTime($EDate);
                        $EDate = $EDate->format('Y-m-d');

                        $monthEndData->whereBetween('mEnd.month_date', [$SDate, $EDate]);
                    }
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('mEnd.month_date', 'DESC')
                ->orderBy('mEnd.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($BranchID)) {
                $totalFiltered = count($monthEndData);
            }

            $month_end_group = $monthEndData->groupBy('branch_id');

            $DataSet = array();
            foreach ($monthEndData as $Row) {

                $ignoreArray = array();
                if (isset($month_end_group[$Row->branch_id])) {

                    if ($month_end_group[$Row->branch_id]->toarray()[0]->month_date != $Row->month_date) {
                        $ignoreArray = ['delete'];
                    }
                }

                $TempSet = array();

                $TempSet['id'] = $sl++;
                $TempSet['branch_id'] = $Row->branch_name;

                $date = new DateTime($Row->month_date);
                $date = $date->format('M-Y');

                $TempSet['month_date'] = $date;
                $TempSet['total_working_day'] = $Row->total_working_day;
                $TempSet['total_product_quantity'] = $Row->total_product_quantity;

                if ($Row->is_active == 0) {
                    $TempSet['is_active'] = '<span class="text-danger">Close</span>';
                } else {
                    $TempSet['is_active'] = '<span class="text-primary">Active</span>';
                }

                $TempSet['action'] = Role::roleWiseArray($this->GlobalRole, $Row->monthend_no, $ignoreArray);

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
            return view('INV.MonthEnd.index');
        }

    }

    public function checkDayEndData(Request $request)
    {
        if ($request->ajax()) {

            $branchId = $request->BranchID;
            // dd($branchId);
            $CompanyID = Common::getCompanyId();

            //-----get month end data according to branch which is requested
            $monthEndData = MonthEnd::where(['branch_id' => $branchId, 'is_active' => 1])->first();

            if (!empty($monthEndData)) {

                //   dd('hh');

                $MonthSrt = new DateTime($monthEndData->month_start_date);
                $MonthSrt = $MonthSrt->format('Y-m-d');

                $date = new DateTime($MonthSrt);
                $date->modify('last day of this month');
                $MonthEnd = $date->format('Y-m-d');

                // dd($MonthEnd);

                $workingDays = Common::systemMonthWorkingDay($CompanyID, $branchId, null, $MonthSrt);

                //get total day end data in current month
                $dayEndData = DayEnd::where([['branch_id', $branchId], ['is_active', 0]])
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
            } else {
                // if ($branchId == 0) {
                //     $branch_soft_start_date = new DateTime();
                //     $branch_soft_start_date = $branch_soft_start_date->format('Y-m-d');

                //     $BranchCode = sprintf("%04d", 0);
                // } else {
                /// else go branch table and set branch date and execute end
                $BranchData = Branch::where(['id' => $branchId, 'is_approve' => 1])->first();

                $branch_soft_start_date = new DateTime($BranchData->inv_start_date);
                $branch_soft_start_date = $branch_soft_start_date->format('Y-m-d');

                $BranchCode = sprintf("%04d", $BranchData->branch_code);
                // }
                // dd('cc');

                $MonthSrt = $branch_soft_start_date;

                $date = new DateTime($MonthSrt);
                $date->modify('last day of this month');
                $MonthEnd = $date->format('Y-m-d');

                $workingDays = Common::systemMonthWorkingDay($CompanyID, $branchId, null, $MonthSrt);
                // dd( $workingDays);

                //get total day end data in current month
                $dayEndData = DayEnd::where([['branch_id', $branchId], ['is_active', 0]])
                    ->whereBetween('branch_date', [$MonthSrt . " 00:00:00", $MonthEnd . " 23:59:59"])
                    ->pluck('branch_date')
                    ->toArray();

                // dd($dayEndData );

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
    }

    public function executeMonthEnd(Request $request)
    {
        // if (isset($request->btnMonthEnd) && $request->btnMonthEnd == 'Submit')
        // if ($request->isMethod('post'))

        $notification = array();

        if ($request->ajax()) {
            $CompanyID = $request->company_id;
            $branchID = $request->branch_id;

            //-----get month end data according to branch which is requested
            $monthEndData = MonthEnd::where(['branch_id' => $branchID, 'is_active' => 1])->first();

            if (!empty($monthEndData)) {

                $MonthSrt = new DateTime($monthEndData->month_start_date);
                $MonthSrt = $MonthSrt->format('Y-m-d');

                $date = new DateTime($MonthSrt);
                $date->modify('last day of this month');
                $MonthEnd = $date->format('Y-m-d');

                $workingDays = count(Common::systemMonthWorkingDay($CompanyID, $branchID, null, $MonthSrt));

                //tCMNProduct = total current month number of product
                // $tCMNProduct = UsesDetails::where([
                //     ['branch_id', $branchID],
                //     ['is_delete', 0],
                // ])
                //     ->whereBetween('created_at', [$MonthSrt . " 00:00:00", $MonthEnd . " 23:59:59"])
                //     ->distinct('product_id')
                //     ->count();
                $tCMNProduct = 0;
                //tCMNProduct = total_product_quantity
                // $ttlProduct = UsesDetails::where([
                //     ['branch_id', $branchID],
                //     ['is_delete', 0],
                // ])
                //     ->distinct('product_id')
                //     ->count();
                $ttlProduct = 0;
                //tCMProdQtn = total current month product quantity
                // $tCMProdQtn = UsesDetails::where([
                //                             ['branch_id', $branchID],
                //                             ['is_delete', 0]
                //                         ])
                //                 ->groupBy('product_id')
                //                 ->count();
                $tCMProdQtn = 0;

                //tCMPurchase = total_current_month_purchases
                $tCMPurchase = PurchaseMaster::where([
                    ['branch_id', $branchID],
                    ['is_delete', 0],
                ])
                    ->whereBetween('created_at', [$MonthSrt . " 00:00:00", $MonthEnd . " 23:59:59"])
                    ->sum('total_amount');

                //totalPurchase = total_purchases
                $totalPurchase = PurchaseMaster::where([
                    ['branch_id', $branchID],
                    ['is_delete', 0],
                ])
                    ->sum('total_amount');

                $monthEndData->total_working_day = $workingDays;
                $monthEndData->total_current_month_product_quantity = $tCMNProduct;
                $monthEndData->total_product_quantity = $ttlProduct;
                $monthEndData->total_current_month_purchases = $tCMPurchase;
                $monthEndData->total_purchases = $totalPurchase;

                $monthEndData->is_active = 0;
                $monthEndData->month_end_date = $MonthEnd;

                $isUpdate = $monthEndData->save();

                if ($isUpdate) {

                    $newMonthData = array();
                    $newMonthData['monthend_no'] = INVS::generateMonthendNo($branchID);
                    $newMonthData['branch_id'] = $branchID;
                    $newMonthData['branch_code'] = $monthEndData->branch_code;
                    $newMonthData['company_id'] = $CompanyID;

                    $date = new DateTime($MonthSrt);
                    $date->modify('first day of next month');
                    $monthDate = $date->format('Y-m');

                    $newMonthData['month_date'] = $monthDate;

                    $date = new DateTime($MonthSrt);
                    $date->modify('first day of next month');
                    $nextMonthSD = $date->format('Y-m-d');

                    $newMonthData['month_start_date'] = $nextMonthSD;
                    $newMonthData['is_active'] = 1;

                    $isInsert = MonthEnd::create($newMonthData);

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

                // if ($branchID == 0) {
                //     $branch_soft_start_date = new DateTime();
                //     $branch_soft_start_date = $branch_soft_start_date->format('Y-m-d');

                //     $BranchCode = sprintf("%04d", 0);
                // } else {
                /// else go branch table and set branch date and execute end
                $BranchData = Branch::where(['id' => $branchID, 'is_approve' => 1])->first();

                $branch_soft_start_date = new DateTime($BranchData->inv_start_date);
                $branch_soft_start_date = $branch_soft_start_date->format('Y-m-d');

                $BranchCode = sprintf("%04d", $BranchData->branch_code);
                // }

                $MonthSrt = $branch_soft_start_date;

                $date = new DateTime($MonthSrt);
                $date->modify('last day of this month');
                $MonthEnd = $date->format('Y-m-d');

                $workingDays = count(Common::systemMonthWorkingDay($CompanyID, $branchID, null, $MonthSrt));

                //tCMNProduct = total current month number of product
                // $tCMNProduct = UsesDetails::whereIn('sales_bill_no', $MasterDataP->toArray())
                //     // ->select('id')
                //     ->distinct('product_id')
                //     ->get();
                $tCMNProduct = 0;

                // dd($tCMNProduct);
                //tCMNProduct = total_product_quantity
                $ttlProduct = 0; //UsesDetails::distinct('product_id')
                // ->count();
                // $ttlProduct = UsesDetails::where([
                //     ['branch_id', $branchID],
                //     ['is_delete', 0],
                // ])
                //     ->distinct('product_id')
                //     ->count();

                //tCMProdQtn = total current month product quantity
                //$tCMProdQtn = DB::table("inv_use_d")
                // ->select(DB::raw("SUM(cnt) as total"))
                // ->groupBy("product_id")
                // ->count();

                //tCMPurchase = total_current_month_purchases
                $tCMPurchase = PurchaseMaster::where([
                    ['branch_id', $branchID],
                    ['is_delete', 0],
                ])
                    ->whereBetween('created_at', [$MonthSrt . " 00:00:00", $MonthEnd . " 23:59:59"])
                    ->sum('total_amount');

                //totalPurchase = total_purchases
                $totalPurchase = PurchaseMaster::where([
                    ['branch_id', $branchID],
                    ['is_delete', 0],
                ])
                    ->sum('total_amount');

                $monthData = array();
                $monthData['monthend_no'] = INVS::generateMonthendNo($branchID);
                $monthData['branch_id'] = $branchID;
                $monthData['branch_code'] = $BranchCode;
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

                $monthData['total_current_month_product_quantity'] = $tCMNProduct;
                $monthData['total_product_quantity'] = $ttlProduct;
                $monthData['total_current_month_purchases'] = $tCMPurchase;
                $monthData['total_purchases'] = $totalPurchase;

                $isInsert = MonthEnd::create($monthData);

                if ($isInsert) {

                    $newMonthData = array();

                    $date = new DateTime($branch_soft_start_date);
                    $date->modify('first day of next month');
                    $nextMonthSD = $date->format('Y-m-d');
                    $newMonthData['monthend_no'] = INVS::generateMonthendNo($branchID);
                    $newMonthData['branch_id'] = $branchID;
                    $newMonthData['branch_code'] = $BranchCode;
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
            }
        }

        echo json_encode($notification);
        exit;
    }

    public function isDelete(Request $request)
    {
        // $branchId = Common::getBranchId();
        $monthEndId = $request->monthEndId;

        $monthEndData = MonthEnd::where([['monthend_no', $monthEndId]])->first();

        if (!empty($monthEndData)) {

            $branchId = $monthEndData->branch_id;
            $monthDate = $monthEndData->month_date;

            // $lastday = cal_days_in_month(CAL_GREGORIAN, $monthDateEx[1], $monthDateEx[0]);

            $monthEndDate = new DateTime($monthDate . '-01');
            $monthEndDate->modify('last day of this month');
            $monthEndDate = $monthEndDate->format('Y-m-d');

            $dayEndData = DayEnd::where('branch_id', $branchId)
                ->where([['is_delete', 0], ['is_active', 0]])
                ->where('branch_date', '>', $monthEndDate)
                ->count();

            if ($dayEndData == 0) {

                // MonthEnd::where('branch_id', $branchId)->where('month_date', '>', $monthDate)->get()->each->delete();

                $monthEnds = MonthEnd::where('branch_id', $branchId)->where('month_date', '>', $monthDate)->get();
                foreach ($monthEnds as $monthEnd) {
                    $monthEnd->is_delete = 1;
                    $monthEnd->update();
                }
                //
                $monthEndData = MonthEnd::where('branch_id', $branchId)
                    ->where('month_date',$monthDate)
                    ->orderBy('monthend_no', 'DESC')
                    ->first();

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
