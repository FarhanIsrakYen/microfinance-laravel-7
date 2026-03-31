<?php


namespace App\Http\Controllers\MFN\Process;
use App\Http\Controllers\Controller;
use App\Model\Acc\AccMonthEnd;
use App\Model\Acc\Ledger;
use App\Model\Acc\OpeningBalanceMaster;
use App\Model\Acc\OpeningBalanceDetails;
use App\Model\Acc\AccYearEnd;
use App\Model\GNL\Branch;
use App\Model\GNL\FiscalYear;
use App\Model\Acc\AccDayEnd;
use App\Model\MFN\MfnMonthEnd;
use App\Model\MFN\MfnYearEnd;
use App\Model\MFN\MfnDayEnd;

use DateTime;
use DB;
use Redirect;
use Illuminate\Support\Facades\Auth;

use Illuminate\Http\Request;
use App\Services\RoleService as Role;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\AccService as ACCS;

class MfnYearEndController extends Controller
{
    
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

            // Datatable Pagination Variable
    
            $totalData = MfnYearEnd::where('is_delete', 0)
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
            $data = MfnYearEnd::where('mfn_year_end.is_delete', 0)
                ->where('mfn_year_end.is_active', 0)
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->select('mfn_year_end.*', 'gnl_branchs.branch_name')
                ->leftJoin('gnl_branchs', 'mfn_year_end.branch_id', '=', 'gnl_branchs.id')
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
                ->orderBy('mfn_year_end.date','DESC')
                ->orderBy('mfn_year_end.id','DESC')
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

    
            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($BranchID)) {
                $totalFiltered = count($data);
            }
    
            $DataSet = array();
            $i = 0;
    
            foreach ($data as $Row) {
                $ApproveText = ($Row->is_active == 0) ?
                '<span class="text-danger">Close</span>' :
                '<span class="text-primary">Active</span>';
             
                $month_date = new DateTime($Row->date);
                $month_date = $month_date->format('M-Y');
    
                $TempSet = array();
                $TempSet = [
                    'id' => ++$i,
                    'branch_name' => $Row->branch_name,
                    'date' => $month_date,
                    'status' => $ApproveText,
                    'action'      => $Row->id,
                    
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

            $BranchID = Auth::user()->branch_id;

            $dateData = Common::ViewTableOrder('mfn_month_end', [['branchId', $BranchID]], ['id', 'date'], ['date', 'ASC']);

            if (!empty($dateData[0]->date)) {
                $date = new DateTime($dateData[0]->date);
                $date->modify('first day of next month');
            }else{
                $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

                $date = new DateTime($sysDate);
            }

            $date->modify('first day of this month');
            $StartDate = $date->format('d-m-Y');


            $date->modify('last day of this month');
            $EndDate = (isset($EndDate) && !empty($EndDate)) ? $EndDate : $date->format('d-m-Y');

            $data = array(
                    'StartDate' => $StartDate,
                    'EndDate' => $EndDate,
                    'BranchID' => $BranchID,
                );
            return view('MFN.MfnYearEnd.index',$data);
        }
       
    }

    public function checkMonthEndData(Request $request)
    {
        if ($request->ajax()) {

            $branchID = $request->branchId;
            $CompanyID = Common::getCompanyId();



           
            $dayendData = MfnDayEnd::where(['branchId' => $branchID, 'isActive' => 1])->first();

           

            $previousdata = MfnDayEnd::where('branchId', '=', $branchID)
            ->where('date', '<', $dayendData->date)->orderBy('date', 'DESC')
            ->first();


           

            $fiscal_year = DB::table('gnl_fiscal_year')
                                            ->where('company_id',$CompanyID)
                                            ->where('fy_start_date', '<=',$previousdata->date)
                                            ->where('fy_end_date', '>=', $previousdata->date)
                                            ->orderBy('id', 'DESC')
                                            ->first();
                                            // $previousdata->branch_date;


            $monthData= MfnMonthEnd::where(['branchId' => $branchID])
                        ->where('date', '>=', $fiscal_year->fy_start_date)
                        ->where('date', '<=', $fiscal_year->fy_end_date)
                        ->orderBy('date', 'DESC')
                        ->count();


            $BranchData = Branch::where(['id' => $branchID, 'is_approve' => 1])->first();

            // testing ob /
            //    $test =  $this->fnOpeningBalance($branchID,$dayendData->branch_date);



           // testing ob /

            $branch_soft_start_date = new DateTime($BranchData->soft_start_date);
            $branch_soft_start_date = $branch_soft_start_date->format('Y-m-d');

            $fiscal_year_branch = DB::table('gnl_fiscal_year')
                                            ->where('company_id',$CompanyID)
                                            ->where('fy_start_date', '<=',$branch_soft_start_date)
                                            ->where('fy_end_date', '>=', $branch_soft_start_date)
                                            ->orderBy('id', 'DESC')
                                            ->first();

            if($fiscal_year_branch->id==$fiscal_year->id){
                
                $count_month = $this->monthCalculation($branch_soft_start_date,$fiscal_year->fy_end_date);
            }else{
                $count_month = $this->monthCalculation($fiscal_year->fy_start_date,$fiscal_year->fy_end_date);  
            }

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


            $CompanyID = $request->company_id;
            $branchID = $request->branch_id;

            //-----get month end data according to branch which is requested
            $dayendData = MfnDayEnd::where(['branchId' => $branchID, 'isActive' => 1])->first();

           

            $previousdata = MfnDayEnd::where('branchId', '=', $branchID)
            ->where('date', '<', $dayendData->date)->orderBy('date', 'DESC')
            ->first();


            $fiscal_year = DB::table('gnl_fiscal_year')
                                            ->where('company_id',$CompanyID)
                                            ->where('fy_start_date', '<=',$previousdata->date)
                                            ->where('fy_end_date', '>=', $previousdata->date)
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

                    $isInsert = MfnYearEnd::create($newRequestData);

                    if ($isInsert) {

                      
                        DB::commit();
                        // return
                        $notification = array(
                            'message' => 'Successfully Year end executed .',
                            'alert-type' => 'success',
                        );

                        return Redirect::to('mfn/year_end')->with($notification);


                        

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

        $yearEndId = $request->yearEndId;

        $YearEndData = MfnYearEnd::where([['id', $yearEndId]])->first();
       
        if (!empty($YearEndData)) {

            $branchId = $YearEndData->branch_id;
            $monthDate = $YearEndData->date;

            $monthEndDate = new DateTime($monthDate);
            $monthEndDate->modify('last day of this month');
            $monthEndDate = $monthEndDate->format('Y-m-d');

            $dayEndData = MfnDayEnd::where('branchId', $branchId)->where('isActive', '=', 0)
                ->where('date', '>', $monthEndDate)
                ->count();

            if ($dayEndData == 0) {
               $monthEndData = MfnMonthEnd::where('branchId', $branchId)->where('date', '>', $monthDate)->count();
                
               
                if ($monthEndData==0) {
                    $YearEndData->is_delete = 1;
                    $YearEndData->update();
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
            $count +=1;
        }


       return $count;
    }

  

}
