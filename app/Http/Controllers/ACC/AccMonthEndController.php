<?php

namespace App\Http\Controllers\acc;

use App\Http\Controllers\Controller;
use App\Model\Acc\AccDayEnd;
use App\Model\Acc\AccMonthEnd;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\RoleService as Role;
use DateTime;
use DB;
use Illuminate\Http\Request;

class AccMonthEndController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }
    //
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $columns = array(
                0 => 'id',
                2 => 'month_date',
            );
            // ## Datatable Pagination Variable
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // ## Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $SDate = (empty($request->input('SDate'))) ? null : $request->input('SDate');
            $EDate = (empty($request->input('EDate'))) ? null : $request->input('EDate');
            $BranchID = (empty($request->input('EDate'))) ? null : $request->input('branchID');

            //  ## Query
            $data = DB::table('acc_month_end as mEnd')
                ->where([['mEnd.is_delete', 0], ['mEnd.is_active', 0]])
                ->whereIn('mEnd.branch_id', HRS::getUserAccesableBranchIds())
                ->select('mEnd.*', 'br.branch_name', 'br.branch_code')
                ->leftJoin('gnl_branchs as br', 'mEnd.branch_id', '=', 'br.id')
                ->where(function ($data) use ($search, $SDate, $EDate, $BranchID) {
                    if (!empty($search)) {
                        $data->where('branch_name', 'LIKE', "%{$search}%");
                    }
                    if (!empty($BranchID)) {
                        $data->where('branch_id', $BranchID);
                    }
                    if (!empty($SDate) && !empty($EDate)) {

                        $SDate = new DateTime($SDate);
                        $SDate = $SDate->format('Y-m-d');

                        $EDate = new DateTime($EDate);
                        $EDate = $EDate->format('Y-m-d');

                        $data->whereBetween('month_date', [$SDate, $EDate]);
                    }
                })
                ->orderBy('mEnd.month_date', 'DESC')
                ->orderBy('mEnd.id', 'DESC')
                ->orderBy($order, $dir);

            $tempQueryData = clone $data;
            $data = $data->offset($start)->limit($limit)->get();

            $totalData = DB::table('acc_month_end')
                ->where([['is_delete', 0], ['is_active', 0]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;

            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($BranchID)) {
                $totalFiltered = $tempQueryData->count();
            }

            // $month_end_group = $data->groupBy('branch_id');

            $month_end_group = DB::table('acc_month_end')
                ->where([['is_delete', 0], ['is_active', 0]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->select('branch_id', 'month_date')
                ->orderBy('month_date', 'DESC')
                ->get();

            $month_end_group = $month_end_group->groupBy('branch_id');

            $DataSet = array();
            $i = $start;

            foreach ($data as $Row) {

                $ignoreArray = array();

                if (isset($month_end_group[$Row->branch_id])) {

                    if ($month_end_group[$Row->branch_id]->max('month_date') != $Row->month_date) {
                        $ignoreArray = ['delete'];
                    }
                }

                // if (isset($month_end_group[$Row->branch_id])) {

                //     if ($month_end_group[$Row->branch_id]->toarray()[0]['month_date'] != $Row->month_date) {
                //         $ignoreArray = ['delete'];
                //     }
                // }

                // $ApproveText = ($Row->is_active == 0) ?
                // '<span class="text-danger">Close</span>' :
                // '<span class="text-primary">Active</span>';

                $TempSet = array();
                $TempSet = [
                    'id' => ++$i,
                    'branch_name' => (!empty($Row->branch_name)) ? $Row->branch_name . "(" . $Row->branch_code . ")" : "",
                    'month_date' => (new DateTime($Row->month_date))->format('M-Y'),
                    // 'status' => $ApproveText,
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->id, $ignoreArray),

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
            return view('ACC.AccMonthEnd.index');
        }

    }

    public function checkDayEndData(Request $request)
    {
        if ($request->ajax()) {

            $branchID = $request->branchId;
            $CompanyID = Common::getCompanyId();

            //-----get month end data according to branch which is requested
            $monthEndData = DB::table('acc_month_end')
                ->where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])
                ->first();

            if (!empty($monthEndData)) {
                $MonthSrt = (new DateTime($monthEndData->start_date))->format('Y-m-d');
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
            $dayEndData = DB::table('acc_day_end')
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

    public function execute(Request $request)
    {
        $notification = array();

        if ($request->ajax()) {
            $CompanyID = $request->company_id;
            $branchID = $request->branch_id;

            //-----get month end data according to branch which is requested
            $monthEndData = AccMonthEnd::where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])->first();

            if (!empty($monthEndData)) {

                $MonthSrt = (new DateTime($monthEndData->start_date))->format('Y-m-d');

                $date = new DateTime($MonthSrt);
                $date->modify('last day of this month');
                $MonthEnd = $date->format('Y-m-d');

                $workingDays = count(Common::systemMonthWorkingDay($CompanyID, $branchID, null, $MonthSrt));

                /**
                 * %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
                 * %%%%%%%%%%%%%%%%%%%%%%%%%%%%%%
                 * total due and ber kora baki
                 */

                $monthEndData->total_day = $workingDays;

                $monthEndData->is_active = 0;
                $monthEndData->end_date = $MonthEnd;
                // dd($monthEndData);

                $isUpdate = $monthEndData->save();

                if ($isUpdate) {

                    $nextMonthDate = new DateTime($MonthSrt);
                    $nextMonthDate->modify('first day of next month');

                    $haveNextData = AccMonthEnd::where(['branch_id' => $branchID, 'month_date' => $nextMonthDate->format('Y-m-d')])->first();

                    if ($haveNextData) {
                        $haveNextData->is_active = 1;
                        $haveNextData->is_delete = 0;
                        $haveNextData->start_date = $nextMonthDate->format('Y-m-d');

                        $isInsert = $haveNextData->save();
                    } else {
                        $newMonthData = array();

                        $newMonthData['branch_id'] = $branchID;
                        $newMonthData['company_id'] = $CompanyID;

                        $newMonthData['month_date'] = $nextMonthDate->format('Y-m-d');
                        $newMonthData['start_date'] = $nextMonthDate->format('Y-m-d');

                        $newMonthData['is_active'] = 1;
                        $isInsert = AccMonthEnd::create($newMonthData);
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
            } else {
                /// else go branch table and set branch date and execute end
                $BranchData = DB::table('gnl_branchs')->where([['id', $branchID], ['is_approve', 1], ['is_active', 1], ['is_delete', 0]])->first();

                if ($BranchData) {

                    $branch_soft_start_date = new DateTime($BranchData->acc_start_date);
                    $branch_soft_start_date = $branch_soft_start_date->format('Y-m-d');

                    $BranchCode = sprintf("%04d", $BranchData->branch_code);

                    $MonthSrt = $branch_soft_start_date;

                    $date = new DateTime($MonthSrt);
                    $date->modify('last day of this month');
                    $MonthEnd = $date->format('Y-m-d');

                    $haveNextData = AccMonthEnd::where(['branch_id' => $branchID, 'month_date' => $date->format('Y-m-d')])->first();

                    if ($haveNextData) {
                        $haveNextData->is_active = 1;
                        $haveNextData->is_delete = 0;

                        $isInsert = $haveNextData->save();
                    } else {

                        $workingDays = count(Common::systemMonthWorkingDay($CompanyID, $branchID, null, $MonthSrt));

                        $monthData = array();

                        $monthData['branch_id'] = $branchID;
                        $monthData['company_id'] = $CompanyID;
                        $monthData['total_day'] = $workingDays;

                        $date = new DateTime($branch_soft_start_date);
                        $monthDate = $date->format('Y-m-d');
                        $monthData['month_date'] = $monthDate;

                        $date = new DateTime($branch_soft_start_date);
                        $date->modify('last day of this month');
                        $monthLastDate = $date->format('Y-m-d');

                        $monthData['start_date'] = $branch_soft_start_date;
                        $monthData['end_date'] = $monthLastDate;
                        $monthData['is_active'] = 0;

                        $isInsert = AccMonthEnd::create($monthData);
                    }

                    if ($isInsert) {

                        $nextMonthDate = new DateTime($MonthSrt);
                        $nextMonthDate->modify('first day of next month');

                        $haveNextData = AccMonthEnd::where(['branch_id' => $branchID, 'month_date' => $nextMonthDate->format('Y-m-d')])->first();

                        if ($haveNextData) {
                            $haveNextData->is_active = 1;
                            $haveNextData->is_delete = 0;
                            $haveNextData->start_date = $nextMonthDate->format('Y-m-d');

                            $isInsert = $haveNextData->save();
                        } else {

                            $newMonthData = array();

                            $newMonthData['branch_id'] = $branchID;
                            $newMonthData['company_id'] = $CompanyID;

                            $date = new DateTime($branch_soft_start_date);
                            $date->modify('first day of next month');
                            $monthDate = $date->format('Y-m-d');

                            $newMonthData['month_date'] = $monthDate;
                            $newMonthData['start_date'] = $monthDate;
                            $newMonthData['is_active'] = 1;

                            $isInsert = AccMonthEnd::create($newMonthData);
                        }

                        if ($isInsert) {
                            $notification = array(
                                'message' => 'Successfull to Month End Executed',
                                'alert_type' => 'success',
                            );
                        }
                    } else {
                        $notification = array(
                            'message' => 'Unsuccessful to Month End Executed',
                            'alert_type' => 'error',
                        );
                    }
                } else {
                    $notification = array(
                        'message' => 'There is no valid branch. Please try again !!!',
                        'alert_type' => 'error',
                    );
                }

            }
        }

        echo json_encode($notification);
        exit;
    }

    public function isDelete(Request $request)
    {

        $monthEndId = $request->monthEndId;

        $monthEndData = AccMonthEnd::where([['id', $monthEndId]])->first();

        if (!empty($monthEndData)) {

            $branchId = $monthEndData->branch_id;
            $monthDate = $monthEndData->month_date;

            // $lastday = cal_days_in_month(CAL_GREGORIAN, $monthDateEx[1], $monthDateEx[0]);

            $monthEndDate = new DateTime($monthDate . '-01');
            $monthEndDate->modify('last day of this month');
            $monthEndDate = $monthEndDate->format('Y-m-d');
            // dd($monthEndData->month_date,$monthEndDate);

            $dayEndData = AccDayEnd::where('branch_id', $branchId)
                ->where([['is_delete', '=', 0], ['is_active', '=', 0]])
                ->where('branch_date', '>', $monthEndDate)
                ->count();

            // dd($dayEndData );$monthEndData

            if ($dayEndData == 0) {

                $monthEnds = AccMonthEnd::where('branch_id', $branchId)->where('month_date', '>', $monthDate)->get();
                foreach ($monthEnds as $monthEnd) {
                    $monthEnd->is_delete = 1;
                    $monthEnd->update();
                }

                // AccMonthEnd::where('branch_id', $branchId)->where('month_date', '>', $monthDate)->get()->each->delete();
                //
                $monthEndData = AccMonthEnd::where('branch_id', $branchId)
                    ->where('month_date', $monthDate)
                    ->orderBy('id', 'DESC')
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
