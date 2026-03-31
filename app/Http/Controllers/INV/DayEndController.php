<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\INV\DayEnd;
use App\Model\INV\MonthEnd;
use App\Model\INV\PurchaseMaster;
use App\Model\INV\UsesMaster;

use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\InvService as INVS;
use App\Services\RoleService as Role;


use DateTime;
use Illuminate\Http\Request;

class DayEndController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {

            $columns = array(
                0 => 'id',
                1 => 'branch_name',
                2 => 'branch_code',
                3 => 'branch_date',
                4 => 'total_product_quantity',
                5 => 'is_active',
                6 => 'action',
            );
            // Datatable Pagination Variable
            //    dd( $request->input('start'));
            $totalData = DayEnd::where('is_delete', 0)
                ->where('is_active', 0)
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $SDate = (empty($request->input('SDate'))) ? null : $request->input('SDate');
            $EDate = (empty($request->input('EDate'))) ? null : $request->input('EDate');
            $BranchID = (empty($request->input('EDate'))) ? null : $request->input('branchID');

            // Query
            //dd($BranchID );
            $data = DayEnd::where('inv_day_end.is_delete', 0)
                ->where('inv_day_end.is_active', 0)
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->select('inv_day_end.*', 'gnl_branchs.branch_name')
                ->leftJoin('gnl_branchs', 'inv_day_end.branch_id', '=', 'gnl_branchs.id')
                ->where(function ($data) use ($search, $SDate, $EDate, $BranchID) {
                    if (!empty($search)) {
                        $data->where('branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('inv_day_end.branch_code', 'LIKE', "%{$search}%");
                    }
                    if (!empty($BranchID)) {
                        $data->where('branch_id', $BranchID);
                    }
                    if (!empty($SDate) && !empty($EDate)) {

                        $SDate = new DateTime($SDate);
                        $SDate = $SDate->format('Y-m-d');

                        $EDate = new DateTime($EDate);
                        $EDate = $EDate->format('Y-m-d');

                        $data->whereBetween('branch_date', [$SDate, $EDate]);
                    }
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('inv_day_end.branch_date', 'DESC')
                ->orderBy('inv_day_end.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            $day_end_group = $data->groupBy('branch_id');

            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($BranchID)) {
                $totalFiltered = count($data);
            }

            $DataSet = array();
            $i = 0;

            foreach ($data as $Row) {

                $ignoreArray = array();
                if (isset($day_end_group[$Row->branch_id])) {
                    if ($day_end_group[$Row->branch_id]->toarray()[0]['branch_date'] != $Row->branch_date) {
                        $ignoreArray = ['delete'];
                    }
                }

                $ApproveText = ($Row->is_active == 0) ?
                '<span class="text-danger">Close</span>' :
                '<span class="text-primary">Active</span>';
                $branch_date = new DateTime($Row->branch_date);
                $branch_date = $branch_date->format('d-m-Y');

                $TempSet = array();
                $TempSet = [
                    'id' => ++$i,
                    'branch_name' => $Row->branch['branch_name'],
                    'branch_code' => $Row->branch_code,
                    'branch_date' => $branch_date,
                    'total_product_quantity' => $Row->total_product_quantity,
                    'status' => $ApproveText,
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->dayend_no, $ignoreArray),
                ];

                $DataSet[] = $TempSet;
            }
            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $DataSet,
            );

            // dd(Role::roleWiseArray($this->GlobalRole, $Row->id, []));

            echo json_encode($json_data);
        } else {
            return view('INV.Dayend.index');
        }

    }

    public function end(Request $request)
    {
        $notification = array();

        if ($request->ajax()) {

            $current_time = new DateTime(); //current time
            $current_time = $current_time->format('Y-m-d h:i:s');

            $CompanyID = $request->company_id;
            $branchID = $request->branch_id; //branch id

            $dayendData = DayEnd::where(['branch_id' => $branchID, 'is_active' => 1])->first();

            if (!empty($dayendData)) {

                $previousdata = DayEnd::where('branch_id', '=', $branchID)
                    ->where([['is_delete', 0], ['is_active', 0]])
                    ->where('branch_date', '<', $dayendData->branch_date)->orderBy('branch_date', 'DESC')
                    ->first();

                if ($previousdata) {
                    $preDate = $previousdata->branch_date;
                } else {
                    $tempDate = new DateTime($dayendData->branch_date);
                    $preDate = $tempDate->modify("-1 day");
                    $preDate = $preDate->format('Y-m-d');
                }

                $check_Month = $this->fnCheckChangeMonth($dayendData->branch_date, $preDate);

                if ($check_Month == false) {

                    // dd( $dayendData->branch_date );
                    $check_flag = $this->functioncheckMonthend($branchID, $dayendData->branch_date);
                    //    dd( $check_flag );
                    // if month ended return true else flase

                    if ($check_flag == true) {

                        // ececute day end if month end true
                        //................. execute day end

                        //                $dateCheck = new DateTime($dayendData->branch_date);
                        $datestring = $dayendData->branch_date;

                        $totalsale = UsesMaster::where(['branch_id' => $branchID,
                            'uses_date' => $datestring,
                            'is_delete' => 0])
                            ->get();

                        $TotalPurAmount = PurchaseMaster::where(['branch_id' => $branchID,
                            'purchase_date' => $datestring,
                            'is_delete' => 0])
                            ->sum('total_amount');

                        $SalesProductQuantity = $totalsale->sum('total_quantity');

                        ///////////////////////    set calculated data for update
                        $dayendData->total_product_quantity = $SalesProductQuantity;
                        $dayendData->total_purchases = $TotalPurAmount;

                        //status change in day in table and update
                        $dayendData->is_active = 0;
                        $dayendData->day_end_date = $current_time;

                        $isUpdate = $dayendData->save();

                        if ($isUpdate) {
                            // generate next day and set start time and create new date
                            $nextDay = Common::systemNextWorkingDay($dayendData->branch_date, $branchID, $CompanyID);
                            $RequestData['dayend_no'] = INVS::generateDayendNo($dayendData->branch_id);
                            $RequestData['branch_id'] = $dayendData->branch_id;
                            $RequestData['branch_code'] = $dayendData->branch_code;
                            $RequestData['company_id'] = $CompanyID;
                            $RequestData['branch_date'] = $nextDay;
                            $RequestData['day_start_date'] = $current_time;
                            $RequestData['is_active'] = 1;

                            $isInsert = DayEnd::create($RequestData);

                            if ($isInsert) {

                                $notification = array(
                                    'message' => 'Successfully Day End Executed',
                                    'alert_type' => 'success',
                                    'system_date' => (new DateTime($nextDay))->format('d-m-Y'),
                                );

                            } else {
                                $notification = array(
                                    'message' => 'Unsuccessful to Day End Executed',
                                    'alert_type' => 'error',
                                );
                                // return redirect()->back()->with($notification);
                            }
                        } else {
                            $notification = array(
                                'message' => 'Unsuccessful to Day End Executed',
                                'alert_type' => 'error',
                            );
                            // return redirect()->back()->with($notification);
                        }

                        //.............................................................................

                    } else {
                        $notification = array(
                            'message' => 'Please Execute Month end First',
                            'alert_type' => 'error',
                        );
                        // return redirect()->back()->with($notification);
                    }

                } else {
                    //................. execute day end
                    //                $dateCheck = new DateTime($dayendData->branch_date);
                    $datestring = $dayendData->branch_date;

                    $totalsale = UsesMaster::where(['branch_id' => $branchID,
                        'uses_date' => $datestring,
                        'is_delete' => 0])
                        ->get();

                    $TotalPurAmount = PurchaseMaster::where(['branch_id' => $branchID,
                        'purchase_date' => $datestring,
                        'is_delete' => 0])
                        ->sum('total_amount');

                    $SalesProductQuantity = $totalsale->sum('total_quantity');

                    ///////////////////////    set calculated data for update
                    $dayendData->total_product_quantity = $SalesProductQuantity;
                    $dayendData->total_purchases = $TotalPurAmount;

                    //status change in day in table and update
                    $dayendData->is_active = 0;
                    $dayendData->day_end_date = $current_time;

                    $isUpdate = $dayendData->save();

                    if ($isUpdate) {
                        // generate next day and set start time and create new date
                        $nextDay = Common::systemNextWorkingDay($dayendData->branch_date, $branchID, $CompanyID);
                        $RequestData['dayend_no'] = INVS::generateDayendNo($dayendData->branch_id);
                        $RequestData['branch_id'] = $dayendData->branch_id;
                        $RequestData['branch_code'] = $dayendData->branch_code;
                        $RequestData['company_id'] = $CompanyID;
                        $RequestData['branch_date'] = $nextDay;
                        $RequestData['day_start_date'] = $current_time;
                        $RequestData['is_active'] = 1;

                        $isInsert = DayEnd::create($RequestData);

                        if ($isInsert) {

                            $notification = array(
                                'message' => 'Successfully Day End Executed',
                                'alert_type' => 'success',
                                'system_date' => (new DateTime($nextDay))->format('d-m-Y'),
                            );

                        } else {
                            $notification = array(
                                'message' => 'Unsuccessful to Day End Executed',
                                'alert_type' => 'error',
                            );
                            // return redirect()->back()->with($notification);
                        }
                    } else {
                        $notification = array(
                            'message' => 'Unsuccessful to Day End Executed',
                            'alert_type' => 'error',
                        );
                        // return redirect()->back()->with($notification);
                    }

                    //.............................................................................
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

                // dd('tet else ');

                $datestring = $branch_soft_start_date;

                $totalsale = UsesMaster::where(['branch_id' => $branchID,
                    'uses_date' => $datestring,
                    'is_delete' => 0])->get();

                $TotalPurAmount = PurchaseMaster::where(['branch_id' => $branchID,
                    'purchase_date' => $datestring,
                    'is_delete' => 0])->sum('total_amount');

                $SalesProductQuantity = $totalsale->sum('total_quantity');

                $RequestData['dayend_no'] = INVS::generateDayendNo($branchID);
                $RequestData['branch_id'] = $branchID;
                $RequestData['branch_code'] = $BranchCode;
                $RequestData['company_id'] = $CompanyID;
                $RequestData['branch_date'] = $branch_soft_start_date;

                $RequestData['day_start_date'] = $current_time;
                $RequestData['day_end_date'] = $current_time;
                $RequestData['is_active'] = 0;

                ///////////////////////    set calculated data for update
                $RequestData['total_product_quantity'] = $SalesProductQuantity;
                $RequestData['total_purchases'] = $TotalPurAmount;

                $isInsert = DayEnd::create($RequestData);

                if ($isInsert) {
                    // generate next day and set new date

                    $nextDay = Common::systemNextWorkingDay($branch_soft_start_date, $branchID, $CompanyID);
                    $newRequestData['dayend_no'] = INVS::generateDayendNo($branchID);
                    $newRequestData['branch_id'] = $branchID;
                    $newRequestData['branch_code'] = $BranchCode;
                    $newRequestData['company_id'] = $CompanyID;
                    $newRequestData['branch_date'] = $nextDay;
                    $newRequestData['day_start_date'] = $current_time;
                    $newRequestData['is_active'] = 1;

                    $isInsert = DayEnd::create($newRequestData);

                    if ($isInsert) {
                        $notification = array(
                            'message' => 'Successfully Day End Executed',
                            'alert_type' => 'success',
                            'system_date' => (new DateTime($nextDay))->format('d-m-Y'),
                        );
                    } else {
                        $notification = array(
                            'message' => 'Unsuccessful to Day End Executed',
                            'alert_type' => 'error',
                        );
                    }

                } else {

                    $notification = array(
                        'message' => 'Unsuccessful to Day End Executed',
                        'alert_type' => 'error',
                    );
                    // return redirect()->back()->with($notification);
                }
            }
        }

        echo json_encode($notification);
        exit;
    }

    public function fnCheckChangeMonth($date1, $date2)
    {
        $MonthDate1 = new DateTime($date1);
        $MonthDate1 = $MonthDate1->format('m');

        $MonthDate2 = new DateTime($date2);
        $MonthDate2 = $MonthDate2->format('m');

        if ($MonthDate1 == $MonthDate2) {
            return true;
        } else {
            return false;
        }

    }

    public function functioncheckMonthend($branchID, $date)
    {

        $date1 = new DateTime($date);

        $monthDate = $date1->modify('-1 month');
        $monthDate = $date1->format('Y-m');
        $monthEndData = MonthEnd::where('branch_id', $branchID)
            ->where('is_active', '=', 0)
            ->where('month_date', '=', $monthDate)
        // ->orderBy('id', 'DESC')
            ->first();
        // dd($monthDate,$monthEndData);

        // dd(!empty($monthEndData));

        if (!empty($monthEndData)) {
            return true;

        } else {

            $BranchData = Branch::where('id', $branchID)->first();
            //    $date = new DateTime ();
            //    $date->format('Y-m-d');

            $branchsoft = new DateTime($BranchData->inv_start_date);
            // $branchsoft->format('Y-m');
            $tempPre = new DateTime($date);
            // $tempPre->format('Y-m');
            // dd($branchsoft->format('Y-m'),$branchsoft);

            if ($tempPre->format('Y-m') == $branchsoft->format('Y-m')) {
                return true;
            } else {
                return false;
            }
        }

    }

    public function ajaxDeleteInvDayEnd(Request $request)
    {
        if ($request->ajax()) {

            $key = $request->RowID;
            $Model = 'App\\Model\\INV\\DayEnd';
            $DayEndData = $Model::where('dayend_no', $key)->first();
            $branch_id = $DayEndData->branch_id;
            $branch_date = $DayEndData->branch_date;
            $branch_month = (new DateTime($DayEndData->branch_date))->format('Y-m');

            $checkdata = $Model::where('branch_id', '=', $branch_id)
                ->where([['is_delete', 0], ['is_active', 0]])
                ->where('branch_date', '>', $DayEndData->branch_date)
                ->count();

            if ($checkdata > 0) {
                return 'child';
            } else {

                $checkMonthEnd = DB::table('inv_month_end')
                    ->where([['branch_id', '=', $branch_id],
                        ['month_date', $branch_month],
                        ['is_active', 0],
                        ['is_delete', 0]])
                    ->count();

                if ($checkMonthEnd == 0) {
                    $DayEndData->is_active = 1;
                    $isSuccess = $DayEndData->update();

                    if ($isSuccess) {

                        $deletedata = $Model::where('branch_id', '=', $branch_id)
                            ->where('branch_date', '>', $DayEndData->branch_date)
                            ->update(['is_active' => 0, 'is_delete' => 1]);

                            $notification = array(
                                'status'=> 'success',
                                'message' => 'Successfully remove data',
                                'new_date' => (new DateTime($DayEndData->branch_date))->format('d-m-Y'),
                            );
    
                            return json_encode($notification);

                    } else {
                        return 'db_error';
                    }
                } else {
                    return 'month_end';
                }

            }
        }
    }

}
