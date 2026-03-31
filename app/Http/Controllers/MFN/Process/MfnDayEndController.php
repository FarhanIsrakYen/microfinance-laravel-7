<?php

namespace App\Http\Controllers\MFN\Process;

use App\Services\HrService as HRS;
use App\Http\Controllers\Controller;
use App\Model\MFN\MfnDayEnd;
use App\Model\GNL\Branch;
use App\Model\MFN\Samity;
use App\Model\MFN\AutoProcess;

use Carbon\Carbon;
use DB;
use App\Model\MFN\MfnMonthEnd;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use DateTime;
use Role;
// use App\Services\HrService;
use App\Services\MfnService;
use App\Helpers\RoleHelper;
use App\Services\RoleService;
use App\Http\Controllers\MFN\Process\AutoVoucher;

class MfnDayEndController extends Controller
{

    public function index(Request $request)
    {
        if (!$request->ajax()) {
            return view('MFN.MfnDayEnd.index');
        }


        $columns = array(
            0 => 'id',
            1 => 'branch_name',
            2 => 'branch_name',
            3 => 'branch_date',
            4 => 'is_active',
            5 => 'action',
        );
        // Datatable Pagination Variable

        $totalData = MfnDayEnd::where('isActive', 0)
            ->whereIn('branchId', HRS::getUserAccesableBranchIds())
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
        $BranchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');

        // Query
        $data = MfnDayEnd::
            // where('mfn_day_end.is_delete', 0)
            where('mfn_day_end.isActive', 0)
            ->whereIn('branchId', HRS::getUserAccesableBranchIds())
            ->select('mfn_day_end.*', 'gnl_branchs.branch_name', 'gnl_branchs.branch_code')
            ->leftJoin('gnl_branchs', 'mfn_day_end.branchId', '=', 'gnl_branchs.id')
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
            ->offset($start)
            ->limit($limit)
            // ->orderBy($order, $dir)
            ->orderBy('date', 'DESC')
            // ->orderBy($order, $dir)
            ->get();

        $maxDayEnds = DB::table('mfn_day_end')
            ->where('isActive', 0)
            ->select(DB::raw("branchId, MAX(date) AS date"))
            ->groupBy('branchId')
            ->get();

        $day_end_group = $data->groupBy('branchId');

        if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($BranchID)) {
            $totalFiltered = count($data);
        }

        $DataSet = array();
        $i = 0;

        foreach ($data as $Row) {
            $ApproveText = ($Row->is_active == 0) ? '<span class="text-danger">Close</span>' : '<span class="text-primary">Active</span>';
            $branch_date = new DateTime($Row->date);
            $branch_date = $branch_date->format('d-m-Y');

            $action_text = 0;

            // if (isset($day_end_group[$Row->branchId]) && $day_end_group[$Row->branchId]->toarray()[0]['date'] == $Row->date) {
            //     $action_text = $Row->id;
            // }
            $maxDayEnd = $maxDayEnds->where('branchId', $Row->branchId)->first();

            if ($Row->date == $maxDayEnd->date) {
                $action_text = $Row->id;
            }

            $TempSet = array();
            $TempSet = [
                'slNo' => ++$i,
                'branch_name' => $Row->branch_name,
                'branch_code' => $Row->branch_code,
                'branch_date' => $branch_date,
                'status' => $ApproveText,
                // 'action' =>  $action_text,
                'action' =>  RoleService::roleWiseArray($this->GlobalRole, $Row->id)
            ];

            $DataSet[] = $TempSet;
        }
        $json_data = array(
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $DataSet,
        );

        return response()->json($json_data);        
    }

    public function mfnDayEndDatatable(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'branch_name',
            2 => 'branch_name',
            3 => 'branch_date',
            4 => 'is_active',
            5 => 'action',
        );
        // Datatable Pagination Variable

        $totalData = MfnDayEnd::where('isActive', 0)
            ->whereIn('branchId', HRS::getUserAccesableBranchIds())
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
        $BranchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');

        // Query
        $data = MfnDayEnd::
            // where('mfn_day_end.is_delete', 0)
            where('mfn_day_end.isActive', 0)
            ->whereIn('branchId', HRS::getUserAccesableBranchIds())
            ->select('mfn_day_end.*', 'gnl_branchs.branch_name', 'gnl_branchs.branch_code')
            ->leftJoin('gnl_branchs', 'mfn_day_end.branchId', '=', 'gnl_branchs.id')
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
            ->offset($start)
            ->limit($limit)
            // ->orderBy($order, $dir)
            ->orderBy('date', 'DESC')
            // ->orderBy($order, $dir)
            ->get();

        $maxDayEnds = DB::table('mfn_day_end')
            ->where('isActive', 0)
            ->select(DB::raw("branchId, MAX(date) AS date"))
            ->groupBy('branchId')
            ->get();

        $day_end_group = $data->groupBy('branchId');

        if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($BranchID)) {
            $totalFiltered = count($data);
        }

        $DataSet = array();
        $i = 0;

        foreach ($data as $Row) {
            $ApproveText = ($Row->is_active == 0) ? '<span class="text-danger">Close</span>' : '<span class="text-primary">Active</span>';
            $branch_date = new DateTime($Row->date);
            $branch_date = $branch_date->format('d-m-Y');

            $action_text = 0;

            // if (isset($day_end_group[$Row->branchId]) && $day_end_group[$Row->branchId]->toarray()[0]['date'] == $Row->date) {
            //     $action_text = $Row->id;
            // }
            $maxDayEnd = $maxDayEnds->where('branchId', $Row->branchId)->first();

            if ($Row->date == $maxDayEnd->date) {
                $action_text = $Row->id;
            }

            $TempSet = array();
            $TempSet = [
                'slNo' => ++$i,
                'branch_name' => $Row->branch_name,
                'branch_code' => $Row->branch_code,
                'branch_date' => $branch_date,
                'status' => $ApproveText,
                // 'action' =>  $action_text,
                'action' =>  RoleService::roleWiseArray($this->GlobalRole, $Row->id)
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

    public function end(Request $request)
    {
        DB::beginTransaction();

        try {
            if ($request->ajax()) {

                $current_time = new DateTime(); //current time
                $current_time = $current_time->format('Y-m-d h:i:s');

                $CompanyID = $request->company_id;

                $branchID = $request->BranchId; //branch id

                $branchDate = MfnService::systemCurrentDate($branchID);

                // if any unauthorized transaction exits today
                // then could not be day end
                $hasAnyUnauthorizedTransactions = $this->hasAnyUnauthorizedTransactions($branchID, $branchDate);
                
                if ($hasAnyUnauthorizedTransactions) {
                    $notification = array(
                        'alert-type'    => 'error',
                        'message'       => 'Unauthorized Transaction Exists.',
                    );
                    return response()->json($notification);
                }

                $autoVoucher = new AutoVoucher();
                $autoVoucher->mfnCreateAutoVoucher($branchID, $branchDate);


                //  $sysDate = MfnService::systemCurrentDate($branchID);
                //  $date = new DateTime($sysDate);
                //  $date = $date->format('Y-m-d');



                // $AutoProcess = AutoProcess::whereIn('samityId', $samityIds)->where('date', $date)->where('isCompleted', 1)->count();
                //            $BranchNameD = Branch::where(['id' => $branchID, 'is_approve' => 1])->first();

                // search in dayend table
                /*
            when is_active 1, it represent branch current date,
            is_active 0 represent end of day for branch
             */


       
                
                $dayendData = MfnDayEnd::where(['branchId' => $branchID, 'isActive' => 1])->first();
                if (!empty($dayendData)) {
                    $day = new DateTime($dayendData->date);
                    $day = $day->format('l');


                    // $AutosamityIds = $AutoProcess->pluck('samityId')->toArray();
                    $AutosamityIds = app('App\Http\Controllers\MFN\Process\AutoProcessController')->getSamityIdsForAutoProcess($branchID, $dayendData->date);


                    $Samity = Samity::whereIn('id', $AutosamityIds)->get();


                    $samityIds = $Samity->pluck('id')->toArray();

                    $AutoProcess = AutoProcess::whereIn('samityId', $samityIds)->where('date', $dayendData->date)->where('isCompleted', 1)->get();

                    $isOpening = MfnService::isOpening($branchID);
                    // $flag == 

                    // check if auto process is completed all at that day ***********
                    

                    if ($Samity->count() == $AutoProcess->count() || $isOpening) {
                        $previousdata = MfnDayEnd::where('branchId', '=', $branchID)
                            ->where('date', '<', $dayendData->date)->orderBy('date', 'DESC')
                            ->first();

                        if (!empty($previousdata)) {
                            $check_Month = $this->fnCheckChangeMonth($dayendData->date, $previousdata->date);
                            // $check_Year = $this->fnCheckChangeYear($dayendData, $previousdata);
                        } else {
                            $check_Month = true;
                            // $check_Year = true;
                        }



                        if ($check_Month == false) {
                            // check fn month end
                            $check_flag = $this->functioncheckMonthend($branchID, $dayendData->date);

                            // if month ended return true else flase
                            if ($check_flag == true) {

                                // true month ended already
                                //............ execute day end

                                //////// calculations  of day end 

                                // $dayendData->loanDisbursementAmount = DB::table('mfn_loans')->where('branchId', $branchID)->where('disbursementDate', $dayendData->date)->sum('loanAmount');
                                // $dayendData->loanCollectionAmount =  DB::table('mfn_loan_collections')->where('branchId', $branchID)->where('collectionDate', $dayendData->date)->sum('amount');
                                // $dayendData->loanDueAmount = 0; // to be calcutale in future 
                                // $dayendData->savingsDepositAmount =  DB::table('mfn_savings_deposit')->where('branchId', $branchID)->where('date', $dayendData->date)->sum('amount');
                                // $dayendData->savingsWithdrawAmount = DB::table('mfn_savings_withdraw')->where('branchId', $branchID)->where('date', $dayendData->date)->sum('amount');
                                $this->calculateDayEndSummery($branchID, $branchDate, $dayendData);

                                ///////
                                $dayendData->isActive = 0;
                                $isUpdate = $dayendData->save();

                                if ($isUpdate) {
                                    // generate next day and set start time and create new date
                                    $nextDay = HRS::systemNextWorkingDay($dayendData->date, $branchID, $CompanyID);

                                    $RequestData['branchId'] = $dayendData->branchId;

                                    $RequestData['date'] = $nextDay;

                                    $RequestData['isActive'] = 1;

                                    $isInsert = MfnDayEnd::create($RequestData);

                                    if ($isInsert) {

                                        echo true;
                                    } else {
                                        echo false;
                                    }
                                } else {
                                    echo false;
                                }


                                //.............................. execute day end

                            } else {
                                echo json_encode('month');
                            }
                        } else {

                            //............ execute day end


                            //////// calculations  of day end 

                            // $dayendData->loanDisbursementAmount = DB::table('mfn_loans')->where('branchId', $branchID)->where('disbursementDate', $dayendData->date)->sum('loanAmount');
                            // $dayendData->loanCollectionAmount =  DB::table('mfn_loan_collections')->where('branchId', $branchID)->where('collectionDate', $dayendData->date)->sum('amount');
                            // $dayendData->loanDueAmount = 0; // to be calcutale in future 
                            // $dayendData->savingsDepositAmount =  DB::table('mfn_savings_deposit')->where('branchId', $branchID)->where('date', $dayendData->date)->sum('amount');
                            // $dayendData->savingsWithdrawAmount = DB::table('mfn_savings_withdraw')->where('branchId', $branchID)->where('date', $dayendData->date)->sum('amount');

                            $this->calculateDayEndSummery($branchID, $branchDate, $dayendData);
                            ///////
                            $dayendData->isActive = 0;
                            $isUpdate = $dayendData->save();

                            if ($isUpdate) {
                                // generate next day and set start time and create new date
                                $nextDay = HRS::systemNextWorkingDay($dayendData->date, $branchID, $CompanyID);

                                $RequestData['branchId'] = $dayendData->branchId;

                                $RequestData['date'] = $nextDay;

                                $RequestData['isActive'] = 1;

                                $isInsert = MfnDayEnd::create($RequestData);

                                if ($isInsert) {

                                    echo true;
                                } else {
                                    echo false;
                                }
                            } else {
                                echo false;
                            }
                            //.............................. execute day end

                        }
                    } else {
                        //auto not completed 
                        //    $Samity = Samity::where('is_delete', '=', 0)->whereNotIn('id',$AutosamityIds)
                        //     ->where('branchId', $branchID)->where('samityDay', $day)
                        //     ->get();
                        $Samity = $Samity->whereIn('id', $AutosamityIds)->whereNotIn('id', $AutoProcess->pluck('samityId'));
                        $samityNames = $Samity->pluck('name')->toArray();
                        $string = implode(',', $samityNames);
                        $array = array('autoprocessFalse', $string);

                        echo json_encode($array);
                    }
                } else {

                    /// else go branch table and set branch date and execute end
                    $BranchData = Branch::where(['id' => $branchID, 'is_approve' => 1])->first();

                    $branch_mis_soft_start_date = new DateTime($BranchData->mfn_start_date);
                    $branch_mis_soft_start_date = $branch_mis_soft_start_date->format('Y-m-d');

                    $datestring = $branch_mis_soft_start_date;

                    $RequestData['branchId'] = $branchID;
                    // $RequestData['company_id'] = $CompanyID;
                    $RequestData['date'] = $branch_mis_soft_start_date;

                    $day = new DateTime($branch_mis_soft_start_date);
                    $day = $day->format('l');

                    $Samity = Samity::where('is_delete', '=', 0)
                        ->where('branchId', $branchID)->where('samityDay', $day)
                        ->get();
                    $samityIds = $Samity->pluck('id')->toArray();
                    $AutoProcess = AutoProcess::whereIn('samityId', $samityIds)->where('date', $branch_mis_soft_start_date)->where('isCompleted', 1)->get();
                    $AutosamityIds = $AutoProcess->pluck('samityId')->toArray();
                    $isOpening = MfnService::isOpening($branchID);
                    if ($Samity->count() == $AutoProcess->count() || $isOpening) {

                        // $RequestData['start_date'] = $current_time;
                        // $RequestData['end_date'] = $current_time;

                        //////// calculations  of day end 
                        // $RequestData['loanDisbursementAmount'] = DB::table('mfn_loans')->where('branchId', $branchID)->where('disbursementDate', $RequestData['date'])->sum('loanAmount');
                        // $RequestData['loanCollectionAmount'] =  DB::table('mfn_loan_collections')->where('branchId', $branchID)->where('collectionDate', $RequestData['date'])->sum('amount');
                        // $RequestData['loanDueAmount'] = 0; // to be calcutale in future 
                        // $RequestData['savingsDepositAmount'] =  DB::table('mfn_savings_deposit')->where('branchId', $branchID)->where('date', $RequestData['date'])->sum('amount');
                        // $RequestData['savingsWithdrawAmount'] = DB::table('mfn_savings_withdraw')->where('branchId', $branchID)->where('date', $RequestData['date'])->sum('amount');
                        

                        ///////
                        $RequestData['isActive'] = 0;
                        $isInsert = MfnDayEnd::create($RequestData);

                        $this->calculateDayEndSummery($branchID, $branchDate);

                        if ($isInsert) {
                            // generate next day and set new date
                            // HRS::systemNextWorkingDay($dayendData->date , $branchID , $CompanyID);
                            $nextDay = HRS::systemNextWorkingDay($branch_mis_soft_start_date, $branchID, $CompanyID);

                            $newRequestData['branchId'] = $branchID;
                            // $newRequestData['company_id'] = $CompanyID;
                            $newRequestData['date'] = $nextDay;
                            // $newRequestData['start_date'] = $current_time;
                            $newRequestData['isActive'] = 1;

                            $isInsert = MfnDayEnd::create($newRequestData);

                            echo true;
                        } else {

                            echo false;
                        }
                    } else {
                        //auto not completed 
                        $Samity = Samity::where('is_delete', '=', 0)->whereNotIn('id', $AutosamityIds)
                            ->where('branchId', $branchID)->where('samityDay', $day)
                            ->get();
                        $samityNames = $Samity->pluck('name')->toArray();
                        $string = implode(',', $samityNames);
                        $array = array('autoprocessFalse', $string);

                        echo json_encode($array);
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong',
                'consoleMsg'    => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage()
            );
            return response()->json($notification);
        }
    }

    public function calculateDayEndSummery($branchId, $date, $dayEndObj=null){
        $numberOfMembers = DB::table('mfn_members')->where([['is_delete',0],['branchId', $branchId]])
                           ->where(function ($query) use ($date) {
                                $query->where('closingDate', '=', '0000-00-00')
                                    ->orWhere('closingDate', '>', $date);
                            })->count();
        
        $activeLoans = DB::table('mfn_loans')->where([['is_delete', 0],['branchId', $branchId]])
                            ->where(function ($query) use ($date) {
                                $query->where('loanCompleteDate', '=', '0000-00-00')
                                    ->orWhere('loanCompleteDate', '>', $date);
                            })->get();

        $numberOfBorrower = $activeLoans->groupBy('memberId')->count();
        $loanInfo = collect(MfnService::getLoanStatus($activeLoans->pluck('id')->toArray(), $date, $date));
        $totalLoanDueAmount = $loanInfo->sum('dueAmount');
        $loanDueAmount = $loanInfo->sum('onPeriodDueAmount');
        $loanOutstnadingAmount = $loanInfo->sum('outstanding');

        $loanDisbursementAmount = DB::table('mfn_loans')->where('branchId', $branchId)->where('disbursementDate', $date)->sum('loanAmount');
        $loanCollectionAmount =  DB::table('mfn_loan_collections')->where('branchId', $branchId)->where('collectionDate', $date)->sum('amount');
        $savingsDepositAmount =  DB::table('mfn_savings_deposit')->where('branchId', $branchId)->where('transactionTypeId','<=',7)->where('date', $date)->sum('amount');
        $savingsWithdrawAmount = DB::table('mfn_savings_withdraw')->where('branchId', $branchId)->where('transactionTypeId','<=',7)->where('date', $date)->sum('amount');

        $needToSave =0;
        if($dayEndObj == null){
            //need to get the dayEnd first and modify it
            $needToSave = 1;
            $dayEndObj = MfnDayEnd::where(['branchId' => $branchId, 'date' => $date])->first();
        }
        
        $dayEndObj->numberOfMember = $numberOfMembers ;
        $dayEndObj->numberOfBorrower = $numberOfBorrower ;
        $dayEndObj->totalLoanDueAmount = $totalLoanDueAmount;
        $dayEndObj->loanDueAmount = $loanDueAmount;
        $dayEndObj->loanOutstandingAmount = $loanOutstnadingAmount ;
        $dayEndObj->loanDisbursementAmount = $loanDisbursementAmount ;
        $dayEndObj->loanCollectionAmount = $loanCollectionAmount ;
        $dayEndObj->savingsDepositAmount = $savingsDepositAmount ;
        $dayEndObj->savingsWithdrawAmount = $savingsWithdrawAmount ;

        
        if($needToSave == 1){
            $dayEndObj->save();
        }
        
    }

    public function delete(Request $request)
    {

        if (Auth::user()->branch_id == 1) {
            if ($request->ajax()) {

                $id = $request->RowID;
                $fetch_data = MfnDayEnd::where('id', $id)->first();


                $branchId = $fetch_data->branchId;
                $BranchDate = $fetch_data->date;

                // MfnDayEnd::where('id', $id)->delete();
                $checkData = MfnDayEnd::where('branchId', $branchId)
                    ->where('date', '>', $BranchDate)
                    ->count();

                $checkMonthEnd = MfnMonthEnd::where('branchId', $branchId)
                    ->where('date', '>=', $BranchDate)
                    ->count();

                if ($checkData > 1) {
                    echo false;
                } else if ($checkMonthEnd >= 1) {
                    echo 'month';
                } else {
                    MfnDayEnd::where('branchId', $branchId)
                        ->where('isActive', 1)
                        ->delete();
                    $fetch_data->isActive = 1;
                    $isSuccess = $fetch_data->update();
                    if ($isSuccess) {
                        echo true;
                    } else {
                        echo false;
                    }
                }
            }
        } else {
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'You don\'t have access to do this opertion!',
            );
            return response()->json($notification);
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
    public function functioncheckMonthend($branchID, $date)
    {

        $date = new DateTime($date);

        $monthDate = $date->modify('-1 month');
        $monthDate->modify('last day of this month');
        $monthDate = $date->format('Y-m-d');

        $monthEndData = DB::table('mfn_month_end')
            ->where('branchId', $branchID)
            // ->where('is_active', '=', 0)
            ->where('date', '>=', $monthDate)
            // ->orderBy('id', 'DESC')
            ->first();


        if (!empty($monthEndData)) {

            return true;
        } else {
            return false;
        }
    }

    public function hasAnyUnauthorizedTransactions($branchId, $date)
    {
        $hasAnyUnauthorizedTransactions = false;

        $tableNames = array(
            'mfn_savings_deposit',
            'mfn_savings_withdraw',
            'mfn_loans',
            'mfn_loan_collections',
        );

        $dateFieldNames = array(
            'date',
            'date',
            'disbursementDate',
            'collectionDate',
        );

        foreach ($tableNames as $key => $tableName) {
            $unauthTraExists = DB::table($tableName)
                ->where([
                    ['is_delete', 0],
                    ['branchId', $branchId],
                    ['isAuthorized', 0],
                    [$dateFieldNames[$key], $date],
                ])
                ->exists();

            if ($unauthTraExists) {
                $hasAnyUnauthorizedTransactions = true;
                break;
            }
        }

        return $hasAnyUnauthorizedTransactions;
    }
}
