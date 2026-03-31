<?php

namespace App\Http\Controllers\acc;

use App\Http\Controllers\Controller;
use App\Model\Acc\AccMonthEnd;
use App\Model\Acc\Ledger;
use App\Model\Acc\OpeningBalanceMaster;
use App\Model\Acc\OpeningBalanceDetails;
use App\Model\Acc\AccYearEnd;
use App\Model\GNL\Branch;
use App\Model\GNL\FiscalYear;
use App\Model\Acc\AccDayEnd;

use DateTime;
use DB;
use Redirect;

use Illuminate\Http\Request;
use App\Services\RoleService as Role;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\AccService as ACCS;

class AccYearEndController extends Controller
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
                1 => 'branch_name',
                2 => 'date',
                3 => 'is_active',
                4 => 'action',
            );

            // dd('gdgdgdg');
            // Datatable Pagination Variable

            $totalData = AccYearEnd::where('is_delete', 0)
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
            $data = AccYearEnd::where('acc_year_end.is_delete', 0)
                ->where('acc_year_end.is_active', 0)
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->select('acc_year_end.*', 'gnl_branchs.branch_name')
                ->leftJoin('gnl_branchs', 'acc_year_end.branch_id', '=', 'gnl_branchs.id')
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

                        $data->whereBetween('date', [$SDate, $EDate]);
                    }
                })
                ->orderBy('acc_year_end.date','DESC')
                ->orderBy('acc_year_end.id','DESC')
                // ->offset($start)
                // ->limit($limit)
                ->orderBy($order, $dir);
                // ->get();

            $tempQueryData = clone $data;
            $data = $data->offset($start)->limit($limit)->get();

            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($BranchID)) {
                $totalFiltered =  $tempQueryData->count();
            }

            $DataSet = array();
            $i = $start;

            // dd($data);
            foreach ($data as $Row) {
                $ApproveText = ($Row->is_active == 0) ?
                '<span class="text-danger">Close</span>' :
                '<span class="text-primary">Active</span>';

                $month_date = new DateTime($Row->date);
                $month_date = $month_date->format('M-Y');

                $TempSet = array();
                $TempSet = [
                    'id' => ++$i,
                    'branch_name' => (!empty($Row->branch['branch_name']))  ? $Row->branch['branch_name'] . "(".$Row->branch['branch_code'].")" : "",
                    'date' => $month_date,
                    'status' => $ApproveText,
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->id, [])

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
            return view('ACC.AccYearEnd.index');
        }

    }

    public function checkMonthEndData(Request $request)
    {
        if ($request->ajax()) {

            $branchID = $request->branchId;
            $CompanyID = Common::getCompanyId();




            $dayendData = AccDayEnd::where(['branch_id' => $branchID, 'is_active' => 1])->first();



            $previousdata = AccDayEnd::where('branch_id', '=', $branchID)
            ->where('branch_date', '<', $dayendData->branch_date)->orderBy('branch_date', 'DESC')
            ->first();




            $fiscal_year = DB::table('gnl_fiscal_year')
                                            ->where('company_id',$CompanyID)
                                            ->where('fy_start_date', '<=',$previousdata->branch_date)
                                            ->where('fy_end_date', '>=', $previousdata->branch_date)
                                            ->orderBy('id', 'DESC')
                                            ->first();
                                            // $previousdata->branch_date;

            // dd( $fiscal_year);

            $monthData= AccMonthEnd::where(['branch_id' => $branchID, 'is_active' => 0])
                        ->where('month_date', '>=', $fiscal_year->fy_start_date)
                        ->where('month_date', '<=', $fiscal_year->fy_end_date)
                        ->orderBy('month_date', 'DESC')
                        ->count();


            $BranchData = Branch::where(['id' => $branchID, 'is_approve' => 1])->first();

            // testing ob /
            //    $test =  $this->fnOpeningBalance($branchID,$dayendData->branch_date);

            //    dd($test );

            //    dd('TESTING OB');

           // testing ob /

            $branch_soft_start_date = new DateTime($BranchData->acc_start_date);
            $branch_soft_start_date = $branch_soft_start_date->format('Y-m-d');

            $fiscal_year_branch = DB::table('gnl_fiscal_year')
                                            ->where('company_id',$CompanyID)
                                            ->where('fy_start_date', '<=',$branch_soft_start_date)
                                            ->where('fy_end_date', '>=', $branch_soft_start_date)
                                            ->orderBy('id', 'DESC')
                                            ->first();

            if($fiscal_year_branch->id==$fiscal_year->id){

                $count_month = $this->monthCalculation($branch_soft_start_date,$fiscal_year->fy_end_date);
                //dd($count_month);
            }else{
                $count_month = $this->monthCalculation($fiscal_year->fy_start_date,$fiscal_year->fy_end_date);
            }
            // dd('dkdkkdkdkdkddk');

            if ($count_month == $monthData ) {
                return response()->json(array("isDayEndCheck" => true));
            } else {
                return response()->json(array("isDayEndCheck" => false));
            }


        }
    }

    public function execute(Request $request)
    {
        if (isset($request->btnMonthEnd) && $request->btnMonthEnd == 'Submit')
        // if ($request->isMethod('post'))
        {

           // dd('htest');

            $CompanyID = $request->company_id;
            $branchID = $request->branch_id;

            //-----get month end data according to branch which is requested
            $dayendData = AccDayEnd::where(['branch_id' => $branchID, 'is_active' => 1])->first();



            $previousdata = AccDayEnd::where('branch_id', '=', $branchID)
            ->where('branch_date', '<', $dayendData->branch_date)->orderBy('branch_date', 'DESC')
            ->first();


            $fiscal_year = DB::table('gnl_fiscal_year')
                                            ->where('company_id',$CompanyID)
                                            ->where('fy_start_date', '<=',$previousdata->branch_date)
                                            ->where('fy_end_date', '>=', $previousdata->branch_date)
                                            ->orderBy('id', 'DESC')
                                            ->first();


            if(empty($fiscal_year))
            {

                // redirect back ==========>
                $notification = array(
                    'message' => 'Please insert fiscal year first',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);


            }else{



                // $BranchData = Branch::where(['id' => $branchID, 'is_approve' => 1])->first();

                // $branch_soft_start_date = new DateTime($BranchData->acc_start_date);
                // $branch_soft_start_date = $branch_soft_start_date->format('Y-m-d');

                // $BranchCode = sprintf("%04d", $BranchData->branch_code);


                $newRequestData = array();

                $newRequestData['date'] = $fiscal_year->fy_end_date;
                $newRequestData['fiscal_year_id'] = $fiscal_year->id;
                $newRequestData['start_date'] =$fiscal_year->fy_start_date;
                $newRequestData['end_date'] =  $fiscal_year->fy_end_date;
                $newRequestData['company_id'] = $CompanyID;
                $newRequestData['branch_id'] = $branchID;
                $newRequestData['is_active'] = 0;



                DB::beginTransaction();


                try{

                    $isInsert = AccYearEnd::create($newRequestData);

                    if ($isInsert) {

                        //
/*
                        //----- fiscal year code start


                        $dateended1 = new DateTime($fiscal_year->fy_end_date);
                        $start_new_year_date = $dateended1->modify('+1 day');

                        $dateended2 = new DateTime($fiscal_year->fy_end_date);
                        $end_new_year_date = $dateended2->modify('+1 day');
                        $end_new_year_date = $end_new_year_date->modify('+11 month');
                        $end_new_year_date = $end_new_year_date->modify('last day of this month');
                        //  dd($start_new_year_date,$end_new_year_date);

                        $fiscal_new_year = array();


                        $fiscal_new_year['fy_start_date'] = $start_new_year_date->format('Y-m-d');;
                        $fiscal_new_year['fy_end_date'] = $end_new_year_date->format('Y-m-d');;

                        $fiscal_new_year['company_id'] = $CompanyID;

                        $str = $start_new_year_date->format('Y');
                        $end = $end_new_year_date->format('Y');


                        $fiscal_new_year['fy_name'] = $str."-".$end;


                        $fiscal_new_year['is_active'] = 1;

                        FiscalYear::create($fiscal_new_year);


                        //----- fiscal year code end


                        //-----
                        $dayendData = AccDayEnd::where(['branch_id' => $branchID, 'is_active' => 1])->first();
                        if($dayendData->fiscal_year_id==null){

                            $fiscal_year = DB::table('gnl_fiscal_year')
                                            ->where('company_id',$CompanyID)
                                            ->where('fy_start_date', '<=',$dayendData->branch_date)
                                            ->where('fy_end_date', '>=', $dayendData->branch_date)
                                            ->orderBy('id', 'DESC')
                                            ->first();

                                            $dayendData->fiscal_year_id = $fiscal_year->id;
                                            $dayendData->save();


                        }


*/


                        // call function for opening balance .... &&&&&&&&&&&&&&&&&&&&&&&&&

                        //*****************************$%need to check fiscal year id  */
                        $test =  $this->fnOpeningBalance($branchID,$CompanyID,$dayendData->branch_date,$previousdata->branch_date);

                        if($test == true){

                        DB::commit();
                        // return
                        $notification = array(
                            'message' => 'Successfully Year end executed with new Opening Balance',
                            'alert-type' => 'success',
                        );

                        return Redirect::to('acc/year_end')->with($notification);

                        }else{

                            return Redirect::to('acc/year_end')->with($test);

                        }




                    }


                } catch (Exception $e) {

                    DB::rollBack();

                    $notification = array(
                        'message' => 'Unsuccessfull Year end execution ',
                        'alert-type' => 'error',
                        'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                    );
                    return redirect()->back()->with($notification);
                    //return $e;
                }


            }




        }
    }
    public function isDelete(Request $request)
    {

        dd('it will be implemented later delete fiscal year also ');
        // $branchId = Common::getBranchId();
        $yearEndId = $request->yearEndId;

        $YearEndData = AccYearEnd::where([['id', $yearEndId]])->first();

        if (!empty($monthEndData)) {

            $branchId = $monthEndData->branch_id;
            $monthDate = $monthEndData->month_date;
           // dd($monthDate);


            // $lastday = cal_days_in_month(CAL_GREGORIAN, $monthDateEx[1], $monthDateEx[0]);

            $monthEndDate = new DateTime($monthDate . '-01');
            $monthEndDate->modify('last day of this month');
            $monthEndDate = $monthEndDate->format('Y-m-d');
           // dd($monthEndData->month_date,$monthEndDate);

            $dayEndData = AccDayEnd::where('branch_id', $branchId)->where('is_active', '=', 0)
                ->where('branch_date', '>', $monthEndDate)
                ->count();

                // dd($dayEndData );$monthEndData

            if ($dayEndData == 0) {
                AccMonthEnd::where('branch_id', $branchId)->where('month_date', '>', $monthDate)->get()->each->delete();
                //
                $monthEndData = AccMonthEnd::where('branch_id', $branchId)
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

    public function fnCheckChangeYear($data1,$data2){


        // dd($data1->fiscal_year_id==$data2->fiscal_year_id);

        if($data1->fiscal_year_id==$data2->fiscal_year_id){
            return true ;
        }else{
            return false;
        }


    }

    public function monthCalculation($data1,$data2){


        $datestart= new DateTime($data1);


        $dateEnd = new DateTime($data2);

        $count =0;


        while($datestart<=$dateEnd){
            $datestart = $datestart->modify('+1 month');
            // dd($dateEnd,$datestart);
            $count +=1;
        }

        //    dd($count);

       return $count;
    }

    public function fnOpeningBalance($branchID,$CompanyID,$date_present,$date_previous){


        $BranchData = Branch::where(['id' => $branchID, 'is_approve' => 1])->first();


        $projectID =  $BranchData->project_id;
        $projectTypeID =  $BranchData->project_type_id;

        $RequestData = array();
        $RequestData['branch_id'] = $branchID;
        $RequestData['company_id'] = $CompanyID;
        $RequestData['project_id'] = $BranchData->project_id;
        $RequestData['project_type_id'] = $BranchData->project_type_id;
        $RequestData['is_year_end'] = 1;

        $RequestData['ob_no'] = ACCS::generateBillAccOB($RequestData['branch_id']);

        $RequestData['opening_date'] = new DateTime($date_present); // used date time for d/m/y format
        $RequestData['opening_date'] = $RequestData['opening_date']->format('Y-m-d');

        $fiscal_year = DB::table('gnl_fiscal_year')
            ->where('company_id', 13)
            ->where('fy_start_date', '<=', $date_previous)
            ->where('fy_end_date', '>=', $date_previous)
            ->orderBy('id', 'DESC')
            ->first();

        if ($fiscal_year) {
            $RequestData['fiscal_year_id'] = $fiscal_year->id;
        }


        $endDate =$fiscal_year->fy_end_date;
        $startDate = $fiscal_year->fy_start_date;


            // // Query For Debit Amount
            $DebitQuery = DB::table('acc_voucher as av')
                ->where([['av.is_delete', 0], ['av.is_active', 1]])
                ->whereIn('av.voucher_status', [1, 2])
                ->where(function ($DebitQuery) use ($startDate,$endDate, $branchID, $projectID, $projectTypeID) {

                    if (!empty($endDate)) {
                        // whereBetween('voucher_date', [$SDate, $EDate]);
                        $DebitQuery->whereBetween('av.voucher_date', [$startDate, $endDate]);
                    }

                    if (!empty($branchID)) {
                        $DebitQuery->where('av.branch_id', $branchID);
                    }
                    if (!empty($projectID)) {
                        $DebitQuery->where('av.project_id', $projectID);
                    }
                    if (!empty($projectTypeID)) {
                        $DebitQuery->where('av.project_type_id', $projectTypeID);
                    }
                })
                ->leftjoin('acc_voucher_details as avd', function ($DebitQuery) {
                    $DebitQuery->on('avd.voucher_id', 'av.id');
                })
            // 'av.voucher_date',
                ->select('avd.debit_acc as ledger_id',
                    DB::raw('
                        IFNULL(SUM(
                            CASE
                                WHEN av.voucher_date >= "' . $startDate . '" and av.voucher_date <= "' . $endDate . '"
                                THEN avd.amount
                            END
                        ), 0) as sum_debit_amt'
                    )
                )
                ->orderBy('avd.debit_acc', 'ASC')
                ->groupBy('avd.debit_acc')
                ->get();



            $DebitData = array();
            foreach ($DebitQuery as $rowD) {
                $DebitData[$rowD->ledger_id] = (array) $rowD;
            }


             // // Query For Credit Amount
             $CreditQuery = DB::table('acc_voucher as av')
             ->where([['av.is_delete', 0], ['av.is_active', 1]])
             ->whereIn('av.voucher_status', [1, 2])
             ->where(function ($CreditQuery) use ($startDate,$endDate, $branchID, $projectID, $projectTypeID) {
                 if (!empty($endDate)) {
                     $CreditQuery->whereBetween('av.voucher_date', [$startDate, $endDate]);
                 }

                 if (!empty($branchID)) {
                     $CreditQuery->where('av.branch_id', $branchID);
                 }
                 if (!empty($projectID)) {
                     $CreditQuery->where('av.project_id', $projectID);
                 }
                 if (!empty($projectTypeID)) {
                     $CreditQuery->where('av.project_type_id', $projectTypeID);
                 }
             })
             ->leftjoin('acc_voucher_details as avd', function ($CreditQuery) {
                 $CreditQuery->on('avd.voucher_id', 'av.id');
             })
         // 'av.voucher_date',
             ->select('avd.credit_acc as ledger_id',
                 DB::raw('
                     IFNULL(SUM(
                         CASE
                             WHEN av.voucher_date >= "' . $startDate . '" and av.voucher_date <= "' . $endDate . '"
                             THEN avd.amount
                         END
                     ), 0) as sum_credit_amt'
                 )
             )
             ->orderBy('avd.credit_acc', 'ASC')
             ->groupBy('avd.credit_acc')
             ->get();

             $CreditData = array();
            foreach ($CreditQuery as $rowC) {
                $CreditData[$rowC->ledger_id] = (array) $rowC;
            }


        $LedgerData = Ledger::select('id')->where(['is_group_head' => 0, 'is_delete' => 0 ,'is_active'=> 1 ])->get();

         //  dd($CreditData,$DebitData);

        DB::beginTransaction();
        try {
            $isInsert = OpeningBalanceMaster::create($RequestData);
            //   dd($isInsert);
            /* Child Table Insertion */
            $RequestData['ob_no'] =$RequestData['ob_no'];
            // $countertest = 0;
            foreach($LedgerData as $row){

                $flag = false;

                if (isset($DebitData[$row->id]) || isset($CreditData[$row->id]) ) {
                    $flag= true;
                }

                if($flag == true){

                    $RequestData['ledger_id'] = $row->id;
                    $RequestData['debit_amount'] = (isset($DebitData[$row->id]['sum_debit_amt'])? $DebitData[$row->id]['sum_debit_amt'] : 0);
                    $RequestData['credit_amount'] = (isset($CreditData[$row->id]['sum_credit_amt'])? $CreditData[$row->id]['sum_credit_amt'] : 0);
                    $RequestData['balance_amount'] = ($RequestData['debit_amount'] - $RequestData['credit_amount']);

                    $isInsertDetails = OpeningBalanceDetails::create($RequestData);

                    // $countertest ++;
                }

            }

            DB::commit();

            return true;

        } catch (\Exception $e) {

            dd($e);

            DB::rollBack();

            $notification = array(
                'message' => 'Unsuccessful to inserted Issue List',
                'alert-type' => 'error',
                'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
            );
            return $notification;
        }



    }



}
