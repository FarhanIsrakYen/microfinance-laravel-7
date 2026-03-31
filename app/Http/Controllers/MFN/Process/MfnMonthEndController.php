<?php

namespace App\Http\Controllers\MFN\Process;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\MFN\MfnDayEnd;
use App\Model\MFN\MfnMonthEnd;
use App\Services\HrService as HRS;
use App\Services\CommonService as Common;
use App\Services\RoleService;
use DateTime;
use Illuminate\Http\Request;
use Response;
// use App\Services\HrService;
use App\Http\Controllers\MFN\Process\MonthEndSummary;

class MfnMonthEndController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $columns = array(
                0 => 'id',
                1 => 'branch_name',
                2 => 'date',
                3 => 'status',
                4 => 'action',
            );
            // Datatable Pagination Variable

            $totalData = MfnMonthEnd::whereIn('branchId', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;
            $limit         = $request->input('length');
            $start         = $request->input('start');
            $order         = $columns[$request->input('order.0.column')];
            $dir           = $request->input('order.0.dir');

            // Searching variable
            $search   = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $SDate    = (empty($request->input('SDate'))) ? null : $request->input('SDate');
            $EDate    = (empty($request->input('EDate'))) ? null : $request->input('EDate');
            $BranchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');

            // Query
            $data = MfnMonthEnd::whereIn('branchId', HRS::getUserAccesableBranchIds())
                ->select('mfn_month_end.*', 'gnl_branchs.branch_name')
                ->leftJoin('gnl_branchs', 'mfn_month_end.branchId', '=', 'gnl_branchs.id')
                ->where(function ($data) use ($search, $SDate, $EDate, $BranchID) {
                    if (!empty($search)) {
                        $data->where('branch_name', 'LIKE', "%{$search}%");
                    }
                    if (!empty($BranchID)) {
                        $data->where('branchId', $BranchID);
                    }
                    if (!empty($SDate) && !empty($EDate)) {

                        $SDate = new DateTime($SDate);
                        $SDate = $SDate->format('Y-m-d');

                        $EDate = new DateTime($EDate);
                        $EDate = $EDate->format('Y-m-d');

                        $data->whereBetween('date', [$SDate, $EDate]);
                    }
                })
                ->orderBy('mfn_month_end.date', 'DESC')
                ->orderBy('mfn_month_end.id', 'DESC')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($BranchID)) {
                $totalFiltered = count($data);
            }

            $DataSet     = array();
            $i           = 0;
            $ApproveText = '<span class="text-danger">Close</span>';
            foreach ($data as $Row) {

                $month_date = new DateTime($Row->date);
                $month_date = $month_date->format('M-Y');
                

                $TempSet = array();
                $TempSet = [
                    'id'          => ++$i,
                    'branch_name' => $Row->branch_name,
                    'month_date'  => $month_date,
                    'status'      => $ApproveText,
                    // 'action'      => $Row->id,
                    'action'      => RoleService::roleWiseArray($this->GlobalRole, $Row->id),

                ];

                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $DataSet,
            );

            echo json_encode($json_data);

        } else {
            return view('MFN.MfnMonthEnd.index');
        }

    }

    public function checkDayEndData(Request $request)
    {
        if ($request->ajax()) {

            $branchId  = $request->branchId;
            $CompanyID = Common::getCompanyId();

            //-----get month end data according to branch which is requested
            $monthEndData = MfnMonthEnd::where(['branchId' => $branchId])->orderBy('date', 'DESC')->first();

            if (!empty($monthEndData)) {


                $demoDate = new DateTime($monthEndData->date);
                $demoDate = $demoDate->modify('+1 day');

                $MonthSrt = $demoDate->format('Y-m-d');

                $date = new DateTime($MonthSrt);
                $date->modify('last day of this month');
                $MonthEnd = $date->format('Y-m-d');


                $workingDays = HRS::systemMonthWorkingDay($CompanyID, $branchId, null, $MonthSrt);

                //get total day end data in current month

                $dayEndData = MfnDayEnd::where([['branchId', $branchId], ['isActive', 0]])
                    ->whereBetween('date', [$MonthSrt . " 00:00:00", $MonthEnd . " 23:59:59"])
                    ->pluck('date')
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

                $BranchData = Branch::where(['id' => $branchId, 'is_approve' => 1])->first();

                $mis_soft_start_date = new DateTime($BranchData->mfn_start_date);
                $mis_soft_start_date = $mis_soft_start_date->format('Y-m-d');

                $BranchCode = sprintf("%04d", $BranchData->branch_code);

                $MonthSrt = $mis_soft_start_date;

                $date = new DateTime($MonthSrt);
                $date->modify('last day of this month');
                $MonthEnd = $date->format('Y-m-d');
                $workingDays = HRS::systemMonthWorkingDay($CompanyID, $branchId, null, $MonthSrt);

                //get total day end data in current month
                $dayEndData = MfnDayEnd::where([['branchId', $branchId], ['isActive', 0]])
                    ->whereBetween('date', [$MonthSrt . " 00:00:00", $MonthEnd . " 23:59:59"])
                    ->pluck('date')
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
    }
    public function execute(Request $request)
    {
        if (isset($request->btnMonthEnd) && $request->btnMonthEnd == 'Submit')
        // if ($request->isMethod('post'))
        {

            $CompanyID = $request->company_id;
            $branchID  = $request->branch_id;

            //-----get month end data according to branch which is requested
            $monthEndData = MfnMonthEnd::where(['branchId' => $branchID])->orderBy('date', 'ASC')->first();

            if (!empty($monthEndData)) {

                $demoDate = new DateTime($monthEndData->date);
                $demoDate = $demoDate->modify('+1 day');

                $MonthSrt = $demoDate->format('Y-m-d');


                // $MonthSrt = new DateTime($monthEndData->start_date);
                // $MonthSrt = $MonthSrt->format('Y-m-d');

                $date = new DateTime($MonthSrt);
                $date->modify('last day of this month');
                $MonthEnd = $date->format('Y-m-d');

                $monthData = array();

                $monthData['branchId'] = $branchID;

                $monthData['date'] = $MonthEnd;

                // MonthEndSummary::storeMonthEndSummary($branchID, $MonthEnd->format('Y-m'));

                $isInsert = MfnMonthEnd::create($monthData);

                if ($isInsert) {

                    $notification = array(
                        'message'    => 'Successfull to Month End Executed',
                        'alert-type' => 'success',
                    );

                    return redirect()->back()->with($notification);
                } else {

                    $notification = array(
                        'message'    => 'Unsuccessful to Month End Executed',
                        'alert-type' => 'error',
                    );
                    return redirect()->back()->with($notification);
                }

            } else {

                $BranchData = Branch::where(['id' => $branchID, 'is_approve' => 1])->first();

                $mis_soft_start_date = new DateTime($BranchData->mfn_start_date);
                $mis_soft_start_date = $mis_soft_start_date->format('Y-m-d');

                $BranchCode = sprintf("%04d", $BranchData->branch_code);

                $MonthSrt = $mis_soft_start_date;

                $date = new DateTime($MonthSrt);
                $date->modify('last day of this month');
                $MonthEnd = $date->format('Y-m-d');

                // $workingDays = count(Common::systemMonthWorkingDay($CompanyID, $branchID, null, $MonthSrt));

                $monthData = array();

                $monthData['branchId'] = $branchID;

                $monthData['date'] = $MonthEnd;



                $isInsert = MfnMonthEnd::create($monthData);

                if ($isInsert) {

                    $notification = array(
                        'message'    => 'Successfull to Month End Executed',
                        'alert-type' => 'success',
                    );

                    return redirect()->back()->with($notification);
                } else {

                    $notification = array(
                        'message'    => 'Unsuccessful to Month End Executed',
                        'alert-type' => 'error',
                    );
                    return redirect()->back()->with($notification);
                }
            }
        }
    }

    public function delete(Request $request)
    {

        $monthEndId = $request->monthEndId;

        $monthEndData = MfnMonthEnd::where([['id', $monthEndId]])->first();

        if (!empty($monthEndData)) {

            $branchId  = $monthEndData->branchId;
            $monthDate = $monthEndData->date;

            // $lastday = cal_days_in_month(CAL_GREGORIAN, $monthDateEx[1], $monthDateEx[0]);

            $monthEndDate = new DateTime($monthDate);
            $monthEndDate->modify('last day of this month');
            $monthEndDate = $monthEndDate->format('Y-m-d');

            $dayEndData = MfnDayEnd::where('branchId', $branchId)->where('isActive', '=', 0)
                ->where('date', '>', $monthEndDate)
                ->count();


            if ($dayEndData == 0) {

                MfnMonthEnd::where('branchId', $branchId)->where('date', '=', $monthDate)->delete();
                //

                return response()->json(array("isDelete" => true));

            } else {
                return response()->json(array('isDelete' => false));
            }
        } else {
            // MonthEnd::where('id', $monthEndId)->delete();

            return response()->json(array("isDelete" => false));
        }
    }

}
