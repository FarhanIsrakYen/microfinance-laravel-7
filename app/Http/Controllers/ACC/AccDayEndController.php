<?php

namespace App\Http\Controllers\acc;

use App\Http\Controllers\Controller;
use App\Model\Acc\AccDayEnd;
use App\Model\Acc\AccYearEnd;
use App\Model\GNL\Branch;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\RoleService as Role;
use DateTime;
use DB;
use Illuminate\Http\Request;

class AccDayEndController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();

        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $columns = array(
                0 => 'id',
                1 => 'branch_name',
                2 => 'branch_date',
            );
            // Datatable Pagination Variable

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $SDate = (empty($request->input('SDate'))) ? null : $request->input('SDate');
            $EDate = (empty($request->input('EDate'))) ? null : $request->input('EDate');
            $BranchID = (empty($request->input('EDate'))) ? null : $request->input('branchID');

            // ## Query

            $data = AccDayEnd::from('acc_day_end as ade')
                ->where([['ade.is_delete', 0], ['ade.is_active', 0]])
                ->whereIn('ade.branch_id', HRS::getUserAccesableBranchIds())
                ->select('ade.*', 'br.branch_name')
                ->leftJoin('gnl_branchs as br', 'ade.branch_id', '=', 'br.id')
                ->where(function ($data) use ($search, $SDate, $EDate, $BranchID) {
                    if (!empty($search)) {
                        $data->where('br.branch_name', 'LIKE', "%{$search}%");
                    }
                    if (!empty($BranchID)) {
                        $data->where('ade.branch_id', $BranchID);
                    }
                    if (!empty($SDate) && !empty($EDate)) {

                        $SDate = new DateTime($SDate);
                        $SDate = $SDate->format('Y-m-d');

                        $EDate = new DateTime($EDate);
                        $EDate = $EDate->format('Y-m-d');

                        $data->whereBetween('ade.branch_date', [$SDate, $EDate]);
                    }
                })
                ->orderBy($order, $dir)
                ->orderBy('ade.branch_date', 'DESC');

            $tempQueryData = clone $data;
            $data = $data->offset($start)->limit($limit)->get();

            $totalData = DB::table('acc_day_end')
                ->where([['is_delete', 0], ['is_active', 0]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;

            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($BranchID)) {
                $totalFiltered = $tempQueryData->count();
            }

            $day_end_group = AccDayEnd::where([['is_delete', 0], ['is_active', 0]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->select('branch_id', 'branch_date')
                ->orderBy('branch_date', 'DESC')
                ->get();

            $day_end_group = $day_end_group->groupBy('branch_id');

            $DataSet = array();
            $i = $start;

            foreach ($data as $Row) {

                $ignoreArray = array();

                if (isset($day_end_group[$Row->branch_id])) {
                    // if ($day_end_group[$Row->branch_id]->toarray()[0]['branch_date'] != $Row->branch_date) {
                    //     $ignoreArray = ['delete'];
                    // }

                    if ($day_end_group[$Row->branch_id]->max('branch_date') != $Row->branch_date) {
                        $ignoreArray = ['delete'];
                    }
                }

                // $ApproveText = ($Row->is_active == 0) ?
                // '<span class="text-danger">Close</span>' :
                // '<span class="text-primary">Active</span>';

                $branch_date = new DateTime($Row->branch_date);
                $branch_date = $branch_date->format('d-m-Y');

                $TempSet = array();
                $TempSet = [
                    'id' => ++$i,
                    'branch_name' => (!empty($Row->branch['branch_name'])) ? $Row->branch['branch_name'] . "(" . $Row->branch['branch_code'] . ")" : "",
                    'branch_date' => $branch_date,
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
            return view('ACC.AccDayEnd.index');
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

            //            $BranchNameD = Branch::where(['id' => $branchID, 'is_approve' => 1])->first();

            // search in dayend table
            /*
            when is_active 1, it represent branch current date,
            is_active 0 represent end of day for branch
             */

            $dayendData = AccDayEnd::where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])->first();

            if (!empty($dayendData)) {

                $previousdata = AccDayEnd::where('branch_id', '=', $branchID)
                    ->where([['is_delete', 0], ['is_active', 0]])
                    ->where('branch_date', '<', $dayendData->branch_date)
                    ->orderBy('branch_date', 'DESC')
                    ->first();

                if (!empty($previousdata)) {
                    $check_Month = $this->fnCheckChangeMonth($dayendData->branch_date, $previousdata->branch_date);
                    $check_Year = $this->fnCheckChangeYear($dayendData, $previousdata);
                } else {
                    $check_Month = true;
                    $check_Year = true;
                }

                // if same month return true same year true

                if ($check_Month == true && $check_Year == true) {
                    //............ execute day end
                    $datestring = $dayendData->branch_date;
                    //status change in day in table and update
                    $dayendData->is_active = 0;
                    $dayendData->end_date = $current_time;
                    //dd( $dayendData );
                    $isUpdate = $dayendData->save();

                    if ($isUpdate) {
                        // generate next day and set start time and create new date
                        $nextDay = Common::systemNextWorkingDay($dayendData->branch_date, $branchID, $CompanyID);

                        $haveNextDayData = AccDayEnd::where(['branch_id' => $branchID, 'branch_date' => $nextDay])->first();

                        if ($haveNextDayData) {
                            $haveNextDayData->is_active = 1;
                            $haveNextDayData->is_delete = 0;
                            $haveNextDayData->start_date = $current_time;

                            $isInsert = $haveNextDayData->save();
                        } else {

                            $fiscal_year = DB::table('gnl_fiscal_year')
                                ->select('id')
                                ->where([['is_active', 1], ['is_delete', 0]])
                                ->where('company_id', $CompanyID)
                                ->where('fy_start_date', '<=', $nextDay)
                                ->where('fy_end_date', '>=', $nextDay)
                                ->orderBy('id', 'DESC')
                                ->first();

                            if ($fiscal_year) {
                                $RequestData['fiscal_year_id'] = $fiscal_year->id;
                            }

                            $RequestData['branch_id'] = $dayendData->branch_id;
                            $RequestData['company_id'] = $CompanyID;
                            $RequestData['branch_date'] = $nextDay;
                            $RequestData['start_date'] = $current_time;
                            $RequestData['is_active'] = 1;

                            $isInsert = AccDayEnd::create($RequestData);
                        }

                        if ($isInsert) {

                            $notification = array(
                                'message' => 'Successfully Day End Executed',
                                'alert_type' => 'success',
                                'system_date' => (new DateTime($nextDay))->format('d-m-Y'),
                            );
                            // return redirect()->back()->with($notification);
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

                    //.............................. execute day end

                } else if ($check_Month == false && $check_Year == true) {
                    // check fn month end
                    $check_flag = $this->functioncheckMonthend($branchID, $dayendData->branch_date);

                    //dd($check_flag,'uyry');
                    // if month ended return true else flase
                    if ($check_flag == true) {

                        // true month ended already
                        //............ execute day end

                        $datestring = $dayendData->branch_date;

                        //status change in day in table and update
                        $dayendData->is_active = 0;
                        $dayendData->end_date = $current_time;

                        //dd( $dayendData );

                        $isUpdate = $dayendData->save();

                        if ($isUpdate) {
                            // generate next day and set start time and create new date
                            $nextDay = Common::systemNextWorkingDay($dayendData->branch_date, $branchID, $CompanyID);

                            $haveNextDayData = AccDayEnd::where(['branch_id' => $branchID, 'branch_date' => $nextDay])->first();

                            if ($haveNextDayData) {
                                $haveNextDayData->is_active = 1;
                                $haveNextDayData->is_delete = 0;
                                $haveNextDayData->start_date = $current_time;

                                $isInsert = $haveNextDayData->save();
                            } else {

                                $fiscal_year = DB::table('gnl_fiscal_year')
                                    ->select('id')
                                    ->where([['is_active', 1], ['is_delete', 0]])
                                    ->where('company_id', $CompanyID)
                                    ->where('fy_start_date', '<=', $nextDay)
                                    ->where('fy_end_date', '>=', $nextDay)
                                    ->orderBy('id', 'DESC')
                                    ->first();

                                if ($fiscal_year) {
                                    $RequestData['fiscal_year_id'] = $fiscal_year->id;
                                }

                                $RequestData['branch_id'] = $dayendData->branch_id;
                                $RequestData['company_id'] = $CompanyID;
                                $RequestData['branch_date'] = $nextDay;
                                $RequestData['start_date'] = $current_time;
                                $RequestData['is_active'] = 1;

                                $isInsert = AccDayEnd::create($RequestData);
                            }

                            if ($isInsert) {

                                $notification = array(
                                    'message' => 'Successfully Day End Executed',
                                    'alert_type' => 'success',
                                    'system_date' => (new DateTime($nextDay))->format('d-m-Y'),
                                );
                                // return redirect()->back()->with($notification);
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

                        //.............................. execute day end

                    } else {
                        // 0 0 false month end  case :: return with noti
                        $notification = array(
                            'message' => 'Please Execute  Month end First',
                            'alert_type' => 'error',
                        );
                        // return redirect()->back()->with($notification);

                    }

                } else if ($check_Month == true && $check_Year == false) {

                    // check fn yr end

                    $check_flag = $this->functioncheckYearend($branchID, $CompanyID, $previousdata->branch_date);
                    // if month ended return true else flase
                    if ($check_flag == true) {
                        // true year ended already

                        //............ execute day end

                        $datestring = $dayendData->branch_date;

                        //status change in day in table and update
                        $dayendData->is_active = 0;
                        $dayendData->end_date = $current_time;

                        //dd( $dayendData );

                        $isUpdate = $dayendData->save();

                        if ($isUpdate) {
                            // generate next day and set start time and create new date
                            $nextDay = Common::systemNextWorkingDay($dayendData->branch_date, $branchID, $CompanyID);

                            $haveNextDayData = AccDayEnd::where(['branch_id' => $branchID, 'branch_date' => $nextDay])->first();

                            if ($haveNextDayData) {
                                $haveNextDayData->is_active = 1;
                                $haveNextDayData->is_delete = 0;
                                $haveNextDayData->start_date = $current_time;

                                $isInsert = $haveNextDayData->save();
                            } else {

                                $fiscal_year = DB::table('gnl_fiscal_year')
                                    ->select('id')
                                    ->where([['is_active', 1], ['is_delete', 0]])
                                    ->where('company_id', $CompanyID)
                                    ->where('fy_start_date', '<=', $nextDay)
                                    ->where('fy_end_date', '>=', $nextDay)
                                    ->orderBy('id', 'DESC')
                                    ->first();

                                if ($fiscal_year) {
                                    $RequestData['fiscal_year_id'] = $fiscal_year->id;
                                }

                                $RequestData['branch_id'] = $dayendData->branch_id;
                                $RequestData['company_id'] = $CompanyID;
                                $RequestData['branch_date'] = $nextDay;
                                $RequestData['start_date'] = $current_time;
                                $RequestData['is_active'] = 1;

                                $isInsert = AccDayEnd::create($RequestData);
                            }

                            if ($isInsert) {

                                $notification = array(
                                    'message' => 'Successfully Day End Executed',
                                    'alert_type' => 'success',
                                    'system_date' => (new DateTime($nextDay))->format('d-m-Y'),
                                );
                                // return redirect()->back()->with($notification);
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

                        //.............................. execute day end
                    } else {
                        // 0 0 false year end  case :: return with noti
                        $notification = array(
                            'message' => 'Please Execute  Year end First',
                            'alert_type' => 'error',
                        );
                        // return redirect()->back()->with($notification);

                    }

                } else {

                    $check_flag1 = $this->functioncheckMonthend($branchID, $dayendData->branch_date);
                    $check_flag2 = $this->functioncheckYearend($branchID, $CompanyID, $previousdata->branch_date);
                    // dd($check_flag2);
                    if ($check_flag1 == true && $check_flag2 == true) {
                        //............ execute day end

                        $datestring = $dayendData->branch_date;

                        //status change in day in table and update
                        $dayendData->is_active = 0;
                        $dayendData->end_date = $current_time;

                        // dd( $dayendData );

                        $isUpdate = $dayendData->save();

                        if ($isUpdate) {
                            // generate next day and set start time and create new date
                            $nextDay = Common::systemNextWorkingDay($dayendData->branch_date, $branchID, $CompanyID);

                            $haveNextDayData = AccDayEnd::where(['branch_id' => $branchID, 'branch_date' => $nextDay])->first();

                            if ($haveNextDayData) {
                                $haveNextDayData->is_active = 1;
                                $haveNextDayData->is_delete = 0;
                                $haveNextDayData->start_date = $current_time;

                                $isInsert = $haveNextDayData->save();
                            } else {

                                $fiscal_year = DB::table('gnl_fiscal_year')
                                    ->select('id')
                                    ->where([['is_active', 1], ['is_delete', 0]])
                                    ->where('company_id', $CompanyID)
                                    ->where('fy_start_date', '<=', $nextDay)
                                    ->where('fy_end_date', '>=', $nextDay)
                                    ->orderBy('id', 'DESC')
                                    ->first();

                                if ($fiscal_year) {
                                    $RequestData['fiscal_year_id'] = $fiscal_year->id;
                                }

                                $RequestData['branch_id'] = $dayendData->branch_id;
                                $RequestData['company_id'] = $CompanyID;
                                $RequestData['branch_date'] = $nextDay;
                                $RequestData['start_date'] = $current_time;
                                $RequestData['is_active'] = 1;

                                $isInsert = AccDayEnd::create($RequestData);
                            }

                            if ($isInsert) {

                                $notification = array(
                                    'message' => 'Successfully Day End Executed',
                                    'alert_type' => 'success',
                                    'system_date' => (new DateTime($nextDay))->format('d-m-Y'),
                                );
                                // return redirect()->back()->with($notification);
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

                        //.............................. execute day end

                    }

                    // 0 0 false both case :: return with noti
                    $notification = array(
                        'message' => 'Please Execute Month end OR Year end First',
                        'alert_type' => 'error',
                    );
                    // return redirect()->back()->with($notification);

                }

            } else {

                /// else go branch table and set branch date and execute end
                $BranchData = Branch::where([['id', $branchID], ['is_approve', 1], ['is_active', 1], ['is_delete', 0]])->first();

                $branch_soft_start_date = new DateTime($BranchData->acc_start_date);
                $branch_soft_start_date = $branch_soft_start_date->format('Y-m-d');

                $datestring = $branch_soft_start_date;

                $haveDayData = AccDayEnd::where(['branch_id' => $branchID, 'branch_date' => $datestring])->first();

                if ($haveDayData) {
                    $haveDayData->is_active = 1;
                    $haveDayData->is_delete = 0;
                    $haveDayData->start_date = $current_time;

                    $isInsert = $haveDayData->save();
                } else {

                    $fiscal_year = DB::table('gnl_fiscal_year')
                        ->select('id')
                        ->where([['is_active', 1], ['is_delete', 0]])
                        ->where('company_id', $CompanyID)
                        ->where('fy_start_date', '<=', $branch_soft_start_date)
                        ->where('fy_end_date', '>=', $branch_soft_start_date)
                        ->orderBy('id', 'DESC')
                        ->first();

                    if ($fiscal_year) {
                        $RequestData['fiscal_year_id'] = $fiscal_year->id;
                    }

                    $RequestData['branch_id'] = $branchID;
                    $RequestData['company_id'] = $CompanyID;
                    $RequestData['branch_date'] = $branch_soft_start_date;

                    $RequestData['start_date'] = $current_time;
                    $RequestData['end_date'] = $current_time;
                    $RequestData['is_active'] = 0;

                    $isInsert = AccDayEnd::create($RequestData);
                }

                if ($isInsert) {
                    // generate next day and set new date

                    $nextDay = Common::systemNextWorkingDay($branch_soft_start_date, $branchID, $CompanyID);

                    $haveNextDayData = AccDayEnd::where(['branch_id' => $branchID, 'branch_date' => $nextDay])->first();

                    if ($haveNextDayData) {
                        $haveNextDayData->is_active = 1;
                        $haveNextDayData->is_delete = 0;
                        $haveNextDayData->start_date = $current_time;

                        $isInsert = $haveNextDayData->save();
                    } else {

                        $fiscal_year = DB::table('gnl_fiscal_year')
                            ->select('id')
                            ->where([['is_active', 1], ['is_delete', 0]])
                            ->where('company_id', $CompanyID)
                            ->where('fy_start_date', '<=', $nextDay)
                            ->where('fy_end_date', '>=', $nextDay)
                            ->orderBy('id', 'DESC')
                            ->first();

                        if ($fiscal_year) {
                            $newRequestData['fiscal_year_id'] = $fiscal_year->id;
                        }

                        $newRequestData['branch_id'] = $branchID;
                        $newRequestData['company_id'] = $CompanyID;
                        $newRequestData['branch_date'] = $nextDay;
                        $newRequestData['start_date'] = $current_time;
                        $newRequestData['is_active'] = 1;

                        $isInsert = AccDayEnd::create($newRequestData);
                    }

                    if ($isInsert) {

                        $notification = array(
                            'message' => 'Successfully Day End Executed',
                            'alert_type' => 'success',
                            'system_date' => (new DateTime($nextDay))->format('d-m-Y'),
                        );
                        // return redirect()->back()->with($notification);
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
            }
        }

        echo json_encode($notification);
        exit;
    }

    public function functioncheckMonthend($branchID, $date)
    {

        $date = new DateTime($date);

        $monthDate = $date->modify('-1 month');
        $monthDate->modify('first day of this month');
        $monthDate = $date->format('Y-m-d');

        $monthEndData = DB::table('acc_month_end')
            ->where('branch_id', $branchID)
            ->where('is_active', '=', 0)
            ->where('is_delete', '=', 0)
            ->where('month_date', '>=', $monthDate)
        // ->orderBy('id', 'DESC')
            ->first();

        if (!empty($monthEndData)) {
            return true;
        } else {
            return false;
        }

    }

    public function functioncheckYearend($branchID, $CompanyID, $date)
    {

        // to be implimented
        $fiscal_year = DB::table('gnl_fiscal_year')
            ->where([['is_active', 1], ['is_delete', 0]])
            ->where('company_id', $CompanyID)
            ->where('fy_start_date', '<=', $date)
            ->where('fy_end_date', '>=', $date)
            ->orderBy('id', 'DESC')
            ->first();


        $previousdata = AccYearEnd::where('branch_id', '=', $branchID)
            ->where('fiscal_year_id', '=', $fiscal_year->id)
            ->where('is_active', '=', 0)
            ->where('is_delete', '=', 0)
        // ->orderBy('branch_date', 'DESC')
            ->first();
        // dd($previousdata);
        if (!empty($previousdata)) {
            return true;
        } else {
            return false;
        }

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

    public function fnCheckChangeYear($data1, $data2)
    {
        if ($data1->fiscal_year_id == $data2->fiscal_year_id) {
            return true;
        } else {
            return false;
        }

    }

    public function ajaxDeleteAccDayEnd(Request $request)
    {
        if ($request->ajax()) {
            $key = $request->RowID;
            // /dd($key);
            $Model = 'App\\Model\\Acc\\AccDayEnd';
            $DayEndData = $Model::where('id', $key)->first();
            $branch_id = $DayEndData->branch_id;
            $branch_date = $DayEndData->branch_date;
            $branch_month = (new DateTime($DayEndData->branch_date))->format('Y-m');

            $checkdata = $Model::where('branch_id', '=', $branch_id)
                ->where([['is_active', 0], ['is_delete', 0]])
                ->where('branch_date', '>', $DayEndData->branch_date)
                ->count();

            if ($checkdata > 0) {
                return 'child';
            } else {

                $checkMonthEnd = DB::table('acc_month_end')
                    ->where([
                        ['branch_id', '=', $branch_id],
                        ['month_date', 'LIKE', "{$branch_month}-%"],
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
                            'status' => 'success',
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

//     public function accdayendDatatable(Request $request)
    //     {
    //         $columns = array(
    //             0 => 'id',
    //             1 => 'branch_name',
    //             2 => 'branch_date',
    //             3 => 'is_active',
    //             4 => 'action',
    //         );
    //         // Datatable Pagination Variable

//         $totalData = AccDayEnd::where('is_delete', 0)
    //             ->where('is_active', 0)
    //             ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
    //             ->count();

//         $totalFiltered = $totalData;
    //         $limit = $request->input('length');
    //         $start = $request->input('start');
    //         $order = $columns[$request->input('order.0.column')];
    //         $dir = $request->input('order.0.dir');

//         // Searching variable
    //         $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
    //         $SDate = (empty($request->input('SDate'))) ? null : $request->input('SDate');
    //         $EDate = (empty($request->input('EDate'))) ? null : $request->input('EDate');
    //         $BranchID = (empty($request->input('EDate'))) ? null : $request->input('branchID');

//         // Query
    //         //dd($BranchID );
    //         $data = AccDayEnd::where('acc_day_end.is_delete', 0)
    //             ->where('acc_day_end.is_active', 0)
    //             ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
    //             ->select('acc_day_end.*', 'gnl_branchs.branch_name')
    //             ->leftJoin('gnl_branchs', 'acc_day_end.branch_id', '=', 'gnl_branchs.id')
    //             ->where(function ($data) use ($search, $SDate, $EDate, $BranchID) {
    //                 if (!empty($search)) {
    //                     $data->where('branch_name', 'LIKE', "%{$search}%");
    //                 }
    //                 if (!empty($BranchID)) {
    //                     $data->where('branch_id', $BranchID);
    //                 }
    //                 if (!empty($SDate) && !empty($EDate)) {

//                     $SDate = new DateTime($SDate);
    //                     $SDate = $SDate->format('Y-m-d');

//                     $EDate = new DateTime($EDate);
    //                     $EDate = $EDate->format('Y-m-d');

//                     $data->whereBetween('branch_date', [$SDate, $EDate]);
    //                 }
    //             })
    //             ->offset($start)
    //             ->limit($limit)
    //             ->orderBy($order, $dir)
    //             ->get();

//         if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($BranchID)) {
    //             $totalFiltered = count($data);
    //         }

//         $DataSet = array();
    //         $i = 0;

//         foreach ($data as $Row) {
    //             $ApproveText = ($Row->is_active == 0) ?
    //             '<span class="text-danger">Close</span>' :
    //             '<span class="text-primary">Active</span>';
    //             $branch_date = new DateTime($Row->branch_date);
    //             $branch_date = $branch_date->format('d-m-Y');

//             $TempSet = array();
    //             $TempSet = [
    //                 'id' => ++$i,
    //                 'branch_name' => $Row->branch['branch_name'],
    //                 'branch_date' => $branch_date,
    //                 'status' => $ApproveText,
    //                 'action' => Role::roleWiseArray($this->GlobalRole, $Row->id, [])

//             ];
    //             dd($this->GlobalRole);
    // //

//             $DataSet[] = $TempSet;
    //         }

//         $json_data = array(
    //             "draw" => intval($request->input('draw')),
    //             "recordsTotal" => intval($totalData),
    //             "recordsFiltered" => intval($totalFiltered),
    //             "data" => $DataSet,
    //         );

//         echo json_encode($json_data);
    //     }

}
