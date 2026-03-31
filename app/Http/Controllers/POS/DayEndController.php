<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\Acc\AutoVoucherConfig;
use App\Model\Acc\Voucher;
use App\Model\Acc\VoucherDetails;
use App\Model\GNL\Branch;
use App\Model\POS\Collection;
use App\Model\POS\DayEnd;
use App\Model\POS\MonthEnd;
use App\Services\AccService as ACCS;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\PosService as POSS;
use App\Services\RoleService as Role;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

class DayEndController extends Controller
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
                2 => 'branch_date',
                4 => 'total_customer',
                5 => 'total_product_quantity',
                6 => 'total_sales_amount',
                7 => 'total_collection',
            );
            // ## Datatable Pagination Variable

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // ## Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $startDate = (empty($request->input('SDate'))) ? null : $request->input('SDate');
            $endDate = (empty($request->input('EDate'))) ? null : $request->input('EDate');
            $branchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');

            // ## Query
            $dayEndQuery = DayEnd::from('pos_day_end as pde')
                ->where([['pde.is_delete', 0], ['pde.is_active', 0]])
                ->whereIn('pde.branch_id', HRS::getUserAccesableBranchIds())
                ->select('pde.*', 'gb.branch_name', 'gb.branch_code')
                ->leftJoin('gnl_branchs as gb', 'pde.branch_id', '=', 'gb.id')
                ->where(function ($dayEndQuery) use ($search, $startDate, $endDate, $branchID) {
                    if (!empty($search)) {
                        $dayEndQuery->where('gb.branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('gb.branch_code', 'LIKE', "%{$search}%")
                            ->orWhere('pde.day_end_date', 'LIKE', "%{$search}%");
                    }
                    if (!empty($branchID)) {
                        $dayEndQuery->where('pde.branch_id', $branchID);
                    }
                    if (!empty($startDate) && !empty($endDate)) {

                        $startDate = new DateTime($startDate);
                        $startDate = $startDate->format('Y-m-d');

                        $endDate = new DateTime($endDate);
                        $endDate = $endDate->format('Y-m-d');

                        $dayEndQuery->whereBetween('pde.branch_date', [$startDate, $endDate]);
                    }
                })
                ->orderBy($order, $dir)
                ->orderBy('pde.branch_date', 'DESC');

            $tempQueryData = clone $dayEndQuery;
            $dayEndQuery = $dayEndQuery->offset($start)->limit($limit)->get();

            $totalData = DB::table('pos_day_end')
                ->where([['is_delete', 0], ['is_active', 0]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;

            if (!empty($search) || !empty($startDate) || !empty($endDate) || !empty($branchID)) {
                $totalFiltered = $tempQueryData->count();
            }

            $day_end_group = DayEnd::where([['is_delete', 0], ['is_active', 0]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->select('branch_id', 'branch_date')
                ->orderBy('branch_date', 'DESC')
                ->get();

            $day_end_group = $day_end_group->groupBy('branch_id');

            $DataSet = array();
            $i = $start;

            foreach ($dayEndQuery as $Row) {

                $ignoreArray = array();
                if (isset($day_end_group[$Row->branch_id])) {

                    if ($day_end_group[$Row->branch_id]->max('branch_date') != $Row->branch_date) {
                        $ignoreArray = ['delete'];
                    }
                }

                // $ApproveText = ($Row->is_active == 0) ? '<span class="text-danger">Close</span>' : '<span class="text-primary">Active</span>';

                $branch_date = new DateTime($Row->branch_date);
                $branch_date = $branch_date->format('d-m-Y');

                $TempSet = array();
                $TempSet = [
                    'id' => ++$i,
                    'branch_name' => (!empty($Row->branch['branch_name'])) ? $Row->branch['branch_name'] . "(" . $Row->branch_code . ")" : "",
                    'branch_date' => $branch_date,
                    'total_customer' => $Row->total_customer,
                    'total_product_quantity' => $Row->total_product_quantity,
                    'total_sales_amount' => $Row->total_sales_amount,
                    'total_collection' => $Row->total_collection,
                    'total_due' => $Row->total_due,
                    'total_purchases' => $Row->total_purchases,
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
            return view('POS.Dayend.index');
        }
    }

    public function end(Request $request)
    {
        $notification = array();

        if ($request->ajax()) {

            $autoVoucherConfig = AutoVoucherConfig::where([['is_delete', 0], ['is_active', 1]])->count();
            if ($autoVoucherConfig < 1) {
                $notification = array(
                    'message' => 'Please Configure Auto Voucher !!!',
                    'alert_type' => 'error',
                );

                return response($notification);
            }

            $current_time = (new DateTime())->format('Y-m-d h:i:s'); //current time
            $prepare_by = Auth::id();

            $companyID = $request->company_id;
            $branchID = $request->branch_id; //branch id
            $currentDayData = DayEnd::where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])->first();

            if (!empty($currentDayData)) {

                $currentBranchDate = $currentDayData->branch_date;

                $previousDayData = DayEnd::where('branch_id', '=', $branchID)
                    ->where([['is_delete', 0], ['is_active', 0]])
                    ->where('branch_date', '<', $currentBranchDate)
                    ->orderBy('branch_date', 'DESC')
                    ->first();

                if ($previousDayData) {
                    $preDate = $previousDayData->branch_date;
                } else {
                    $tempDate = new DateTime($currentBranchDate);
                    $preDate = $tempDate->modify("-1 day");
                    $preDate = $preDate->format('Y-m-d');
                }

                $check_Month = $this->fnCheckChangeMonth($currentBranchDate, $preDate);

                if ($check_Month) {

                    $check_flag = $this->functioncheckMonthend($branchID, $currentBranchDate);

                    if ($check_flag == false) {
                        $notification = array(
                            'message' => 'Please Execute Month end First',
                            'alert_type' => 'error',
                        );

                        return response($notification);exit;
                    }

                    // // ## Execute Day end
                    return $this->dayendExecution($branchID, $currentBranchDate, $companyID, $current_time, $current_time, $prepare_by);
                } else {
                    // ## Execute Day End
                    return $this->dayendExecution($branchID, $currentBranchDate, $companyID, $current_time, $current_time, $prepare_by);
                }
            } else {
                /// ## first day end from software start date
                return $this->dayendExecution($branchID, null, $companyID, $current_time, $current_time, $prepare_by);
            }
        }
    }

    public function dayendExecution($branchID, $datestring, $companyID, $day_start_date_time, $day_end_date_time, $prepare_by)
    {
        DB::beginTransaction();
        try {

            $isSuccessFlag = false;
            if (empty($datestring)) {

                $branchData = Branch::where([['id', $branchID], ['is_approve', 1], ['is_active', 1], ['is_delete', 0]])->first();

                if ($branchData) {
                    $branch_soft_start_date = new DateTime($branchData->soft_start_date);
                    $branch_soft_start_date = $branch_soft_start_date->format('Y-m-d');
                    // $BranchCode = sprintf("%04d", $branchData->branch_code);

                    $datestring = $branch_soft_start_date;
                    $haveDayData = DayEnd::where(['branch_id' => $branchID, 'branch_date' => $datestring])->first();

                    if ($haveDayData) {
                        $haveDayData->is_active = 1;
                        $haveDayData->is_delete = 0;
                        $haveDayData->day_start_date = $day_start_date_time;

                        $isInsert = $haveDayData->save();
                    } else {
                        $RequestData['dayend_no'] = POSS::generateDayendNo($branchID);
                        $RequestData['branch_id'] = $branchID;
                        $RequestData['company_id'] = $companyID;
                        $RequestData['branch_date'] = $datestring;

                        $RequestData['day_start_date'] = $day_start_date_time;
                        $RequestData['day_end_date'] = $day_end_date_time;
                        $RequestData['is_active'] = 1;

                        $isInsert = DayEnd::create($RequestData);
                    }

                    if ($isInsert) {
                        $isSuccessFlag = true;
                    } else {
                        $isSuccessFlag = false;
                    }
                } else {

                    $notification = array(
                        'message' => 'There is no valid branch. Please try again !!!',
                        'alert_type' => 'error',
                    );

                    return response($notification);exit;
                }
            }

            $currentDayData = DayEnd::where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])
                ->whereDate('branch_date', 'LIKE', $datestring)
                ->first();

            ///////////////////////  calculate
            $calculationData = $this->day_end_calculation($branchID, $datestring, $companyID);

            $totalCustomer = (isset($calculationData['totalCustomer'])) ? $calculationData['totalCustomer'] : 0;
            $SalesProductQuantity = (isset($calculationData['SalesProductQuantity'])) ? $calculationData['SalesProductQuantity'] : 0;
            $SalesTotalAmount = (isset($calculationData['SalesTotalAmount'])) ? $calculationData['SalesTotalAmount'] : 0;
            $TotalCollection = (isset($calculationData['TotalCollection'])) ? $calculationData['TotalCollection'] : 0;
            $TotalPurAmount = (isset($calculationData['TotalPurAmount'])) ? $calculationData['TotalPurAmount'] : 0;
            $TotalDue = (isset($calculationData['TotalDue'])) ? $calculationData['TotalDue'] : 0;

            ///////////////////////    set calculated data for update
            $currentDayData->total_customer = $totalCustomer;
            $currentDayData->total_product_quantity = $SalesProductQuantity;
            $currentDayData->total_sales_amount = $SalesTotalAmount;
            $currentDayData->total_collection = $TotalCollection;
            $currentDayData->total_purchases = $TotalPurAmount;
            $currentDayData->total_due = $TotalDue;
            $currentDayData->is_active = 0;
            $currentDayData->day_end_date = $day_end_date_time;


            // ## Current Date take update kore is_active = 0 kora hocche
            $isUpdate = $currentDayData->save();

            if ($isUpdate) {
                // generate next day and set new date

                $nextDay = Common::systemNextWorkingDay($datestring, $branchID, $companyID);

                // ## Check kora hocche Next Date a kono deleted data ache kina, thakle update korbe noyto insert
                // 'is_delete' => 1,
                $haveNextDayData = DayEnd::where(['branch_id' => $branchID, 'branch_date' => $nextDay])->first();

                if ($haveNextDayData) {
                    $haveNextDayData->is_active = 1;
                    $haveNextDayData->is_delete = 0;
                    $haveNextDayData->day_start_date = $day_start_date_time;

                    $isInsert = $haveNextDayData->save();
                } else {
                    $newRequestData['dayend_no'] = POSS::generateDayendNo($branchID);
                    $newRequestData['branch_id'] = $branchID;
                    // $newRequestData['branch_code'] = $BranchCode;
                    $newRequestData['company_id'] = $companyID;
                    $newRequestData['branch_date'] = $nextDay;
                    $newRequestData['day_start_date'] = $day_start_date_time;
                    $newRequestData['is_active'] = 1;

                    $isInsert = DayEnd::create($newRequestData);
                }

                if ($isInsert) {

                    /// call for auto voucher create as configured if success return true
                    $auto_v = $this->auto_voucher_insert($branchID, $datestring, $prepare_by);

                    if ($auto_v == true) {
                        $isSuccessFlag = true;
                    } else {
                        $isSuccessFlag = false;
                    }
                } else {
                    $isSuccessFlag = false;
                }
            } else {
                $isSuccessFlag = false;
            }

            DB::commit();

            if ($isSuccessFlag) {
                $notification = array(
                    'message' => 'Successfully Day End Executed & Auto voucher created',
                    'alert_type' => 'success',
                    'system_date' => (new DateTime($nextDay))->format('d-m-Y'),
                );

                return response($notification);exit;
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Day End Executed',
                    'alert_type' => 'error',
                );
                return response($notification);exit;
            }

        } catch (Exception $e) {
            DB::rollBack();
            $notification = array(
                'message' => 'Unsuccessful Execution.',
                'alert-type' => 'error',
                'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
            );

            // return $notification;

            return response($notification);exit;
        }
    }

    public function day_end_calculation($branchID, $datestring, $companyID)
    {
        /////////////
        $totalCustomer = DB::table('pos_customers')
            ->where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0]])
            ->where('created_at', 'LIKE', "{$datestring}%")
            ->count();

        /////////////////////
        $salesData = DB::table('pos_sales_m')
            ->where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0], ['sales_date', $datestring]])
            ->get();

        $SalesProductQuantity = ($salesData) ? $salesData->sum('total_quantity') : 0;
        $SalesTotalAmount = ($salesData) ? $salesData->sum('total_amount') : 0;

        ///////////////////////
        $TotalCollection = DB::table('pos_collections')
            ->where([['branch_id', $branchID], ['is_active', 1], ['is_delete', 0], ['collection_date', $datestring]])
            ->sum('collection_amount');

        ///////////////////////
        $TotalPurAmount = DB::table('pos_purchases_m')
            ->where(['branch_id' => $branchID, 'purchase_date' => $datestring, 'is_delete' => 0])
            ->sum('total_amount');

        //////// --------- /////////////////
        $dueData = POSS::due_calculation($companyID, [$branchID], $datestring, 'current_and_over_due', 'end_execution');
        $TotalDue = (($dueData) && isset($dueData['ttl_total_due'])) ? $dueData['ttl_total_due'] : 0;

        $result_set = [
            'totalCustomer' => $totalCustomer,
            'SalesProductQuantity' => $SalesProductQuantity,
            'SalesTotalAmount' => $SalesTotalAmount,
            'TotalCollection' => $TotalCollection,
            'TotalPurAmount' => $TotalPurAmount,
            'TotalDue' => $TotalDue,
        ];

        return $result_set;
    }

    public function auto_voucher_insert($branchID, $date, $prepare_by)
    {
        //////////////////////////////////////////////////date and branch parameter a pass a aybo '

        $companyID = Common::getCompanyId();
        $branchData = DB::table('gnl_branchs')->where([['id', $branchID], ['is_approve', 1], ['is_active', 1], ['is_delete', 0]])->first();

        //geting module id
        $CurrentRouteURI = Route::getCurrentRoute()->uri();
        $currentRouteURIAr = explode('/', $CurrentRouteURI);
        $moduleName = $currentRouteURIAr[0];

        $module_id = DB::table('gnl_sys_modules')
            ->where([['is_delete', 0], ['is_active', 1]])->where('route_link', $moduleName)
            ->first()->id;
        ## end geting and setting module id

        // $RequestData = new Request();
        $RequestData = array();
        $RequestData = [
            'voucher_date' => $date,
            'branch_id' => $branchID,
            'prep_by' => (empty($prepare_by)) ? Auth::id() : $prepare_by, // debit 1 credit 2 journal 3
            'project_id' => $branchData->project_id,
            'project_type_id' => $branchData->project_type_id,
            'v_generate_type' => 1,
            'voucher_status' => 1,
            'voucher_type_id' => '',
            'company_id' => $companyID,
            'module_id' => $module_id,
            'global_narration' => '',
            'ft_id' => 0,
            'ft_from' => 0,
            'ft_to' => 0,
            'ft_target_acc' => '',
            'created_by' => (empty($prepare_by)) ? Auth::id() : $prepare_by,
        ];

        ## delete previous auto voucher if exist
        $sameDateExixtesAutoVouchers = Voucher::where('voucher_date', $RequestData['voucher_date'])
            ->where('branch_id', $RequestData['branch_id'])
            ->where('v_generate_type', $RequestData['v_generate_type'])
            ->get();

        if (!empty($sameDateExixtesAutoVouchers)) {
            VoucherDetails::whereIn('voucher_id', $sameDateExixtesAutoVouchers->unique('id')->pluck('id')->toArray())->delete();
            Voucher::whereIn('id', $sameDateExixtesAutoVouchers->unique('id')->pluck('id')->toArray())->delete();
        }

        // config()->set('database.connections.mysql.strict', false);
        // DB::reconnect();

        $AutoVoucherConfig = AutoVoucherConfig::select('id', 'config_id')
            ->where('is_delete', 0)
            ->groupBy('config_id')->distinct('config_id')->get();

        DB::beginTransaction();
        try {

            $globalFlag = true;

            foreach ($AutoVoucherConfig as $index => $Row) {

                $loopVar = AutoVoucherConfig::where('config_id', $Row->config_id)->where('is_delete', 0)->get();

                /**
                 * amount_type = 1 = Credit Amount
                 * amount_type = 0 = Debit Amount
                 */

                $amountD = AutoVoucherConfig::where('config_id', $Row->config_id)->where('amount_type', 0)->where('is_delete', 0)->get();
                $amountC = AutoVoucherConfig::where('config_id', $Row->config_id)->where('amount_type', 1)->where('is_delete', 0)->get();
                $FixedLedger = '';

                if ($amountD->count() > 0) {

                    $loopVar = $amountC;
                    $FixedLedger = $amountD[0]->ledger_id; /// ledger debit fixed

                } else if ($amountC->count() > 0) {

                    $loopVar = $amountD;
                    $FixedLedger = $amountC[0]->ledger_id; /// ledger credit fixed

                } else {
                    return false;
                }

                $flagAmount = false;

                // checking if any amount gater then zero in loopvar then create a voucher
                foreach ($loopVar as $indexLoop => $value) {

                    if($this->calculate_amount($branchID, $date, $value) > 0){
                        $flagAmount = true;
                    }
                    // $flagAmount = $this->calculate_amount($branchID, $date, $value);
                }

                //creating voucher and voucher details
                if ($flagAmount) {

                    $RequestData['voucher_type_id'] = $value->voucher_type;
                    $RequestData['global_narration'] = $value->local_narration;
                    //print_r($value->voucher_type);

                    if ($RequestData['voucher_type_id'] == 5) { ## fund transfer  Voucher

                        $RequestDataNew = array();
                        $RequestDataNew = $RequestData;

                        $debit_arr = array();
                        $credit_arr = array();
                        $amount_arr = array();
                        $narration_arr = array();

                        $transferdata = DB::table('pos_transfers_m')
                            ->where(['branch_from' => $branchID, 'is_delete' => 0, 'is_active' => 1])
                            ->where('transfer_date', $date)
                            ->get();

                        $ftid = Voucher::select('ft_id')->max('ft_id');
                        $ftid += 1;

                        foreach ($loopVar as $data) {

                            if ($this->calculate_amount($branchID, $date, $data) > 0) {
                                $amount = $this->calculate_amount($branchID, $date, $data);
                                array_push($amount_arr, $amount);

                                if ($data->amount_type == 0) {
                                    array_push($credit_arr, $FixedLedger);
                                    array_push($debit_arr, $data->ledger_id);
                                } else {
                                    array_push($credit_arr, $data->ledger_id);
                                    array_push($debit_arr, $FixedLedger);
                                }

                                array_push($narration_arr, 'auto voucher deatils');

                                // // narretion fixeed for auto voucher
                            }
                        }

                        $RequestDataNew['ft_id'] = $ftid;
                        $RequestDataNew['ft_from'] = $branchID;

                        /////////////////////// From Branch Insert ///////////////////////

                        $RequestDataFrom = array();
                        $RequestDataFrom = $RequestDataNew;

                        $RequestDataFrom += [
                            'debit_arr' => $debit_arr,
                            'credit_arr' => $credit_arr,
                            'amount_arr' => $amount_arr,
                            'narration_arr' => $narration_arr,
                        ];

                        $RequestDataFrom = new Request($RequestDataFrom);
                        $Status = ACCS::insertVoucher($RequestDataFrom);

                        if ($Status['alert-type'] == "error") {
                            $globalFlag = false;
                        }
                        /////////////////////End From Branch Insert/////////////////////////////

                        ///////////////// Branch To Insert /////////////////////

                        $transferdata = DB::table('pos_transfers_m')
                            ->where(['branch_from' => $branchID, 'is_delete' => 0, 'is_active' => 1])
                            ->where('transfer_date', $date)
                            ->select(DB::raw('sum(total_amount) as total_amount, branch_to'))
                            ->groupBy('branch_to')
                            ->get();

                        foreach ($transferdata as $To_Row) {

                            $RequestDataTo = array();
                            $RequestDataTo = $RequestDataNew;
                            $amount_arr = array();

                            $RequestDataTo['branch_id'] = $To_Row->branch_to;
                            $RequestDataTo['ft_to'] = $To_Row->branch_to;

                            array_push($amount_arr, $To_Row->total_amount);

                            $RequestDataTo += [
                                'debit_arr' => $credit_arr,
                                'credit_arr' => $debit_arr,
                                'amount_arr' => $amount_arr,
                                'narration_arr' => $narration_arr,
                            ];

                            $RequestDataTo = new Request($RequestDataTo);

                            $Status = ACCS::insertVoucher($RequestDataTo);

                            if ($Status['alert-type'] == "error") {
                                $globalFlag = false;
                            }
                        }

                        //////////////////////// End Branch To Insert //////////////////////////////

                        ///////////////////////// HO Branch Insert ////////////////////////
                        $hofundtransfer = $transferdata->where('branch_to', '<>', 1)->sum('total_amount');

                        if ($hofundtransfer > 0) {

                            $RequestDataHO = array();
                            $RequestDataHO = $RequestDataNew;
                            $amount_arr = array();

                            $RequestDataHO['branch_id'] = 1;

                            array_push($amount_arr, $hofundtransfer);

                            $RequestDataHO += [
                                'debit_arr' => $debit_arr,
                                'credit_arr' => $debit_arr,
                                'amount_arr' => $amount_arr,
                                'narration_arr' => $narration_arr,
                            ];

                            $RequestDataHO = new Request($RequestDataHO);

                            $Status = ACCS::insertVoucher($RequestDataHO);

                            if ($Status['alert-type'] == "error") {
                                $globalFlag = false;
                            }
                        }
                        /////////////////////// End //////////////////////////////

                    } else {

                        $debit_arr = array();
                        $credit_arr = array();
                        $amount_arr = array();
                        $narration_arr = array();

                        $RequestDataAll = array();
                        $RequestDataAll = $RequestData;

                        foreach ($loopVar as $data) {

                            if ($this->calculate_amount($branchID, $date, $data) > 0) {
                                $amount = $this->calculate_amount($branchID, $date, $data);
                                array_push($amount_arr, $amount);

                                if ($data->amount_type == 0) {
                                    array_push($credit_arr, $FixedLedger);
                                    array_push($debit_arr, $data->ledger_id);
                                } else {
                                    array_push($credit_arr, $data->ledger_id);
                                    array_push($debit_arr, $FixedLedger);
                                }

                                array_push($narration_arr, 'auto voucher deatils');

                                // // narretion fixeed for auto voucher
                            }
                        }

                        $RequestDataAll += [
                            'debit_arr' => $debit_arr,
                            'credit_arr' => $credit_arr,
                            'amount_arr' => $amount_arr,
                            'narration_arr' => $narration_arr,
                        ];

                        $RequestDataAll = new Request($RequestDataAll);
                        $Status = ACCS::insertVoucher($RequestDataAll);

                        if ($Status['alert-type'] == "error") {
                            $globalFlag = false;
                        }
                    }
                }
            }

            if ($globalFlag == true) {
                DB::commit();
                return true;
            } else {
                DB::rollBack();
                return false;
            }

        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function calculate_amount($branchID, $date, $data)
    {

        /**
         * Service charge in sales table
         * profit installment profit in collection table
         * Saletype = 1  Cash sale  (pos_sales_m)
         * Saletype = 2  installment sale (pos_sales_m)
         * Saletype = 3  servicecharege (pos_sales_m) others (pos_collections)
         * Saletype = 4 Transfer (pos_transfers_m)
         * Saletype = 5 Purchase(pos_purchases_m)
         * Saletype = 6  Purchase retuen (pos_purchases_r_m)
         * Saletype = 7 Inventory Adjustment (0 undefine)
         * Saletype = 8  Sales Return (pos_sales_return_m)
         * Saletype = 9 Issue (pos_issues_m)
         * Saletype = 10 Issue Return (pos_issues_r_m)
         *
         * (dont delete this comment)
         *
         *
         *
         */

        /*  (dont delete this comment)
        (dont delete this comment)
        (dont delete this comment)&&&&&&&&&&&&&&&&&&&&&&&&&&&&
        $tableD = 'pos_sales_d';

        $MasterData = DB::table($tableM)
        ->select('sales_bill_no')
        ->where(['branch_id' => $branchID, 'is_delete' => 0])
        ->where(['sales_type' => 2, 'sales_date' => $date])
        ->get();

        $MasterDataP = $MasterData->pluck('sales_bill_no');

        $Amount = DB::table($tableD)
        ->whereIn('sales_bill_no', $MasterDataP->toArray())

        ->get();

        (dont delete this comment)
         */

        $saletype = $data->sales_type;
        $table_field = $data->table_field_name;
        // dd($data->supplier_id);

        $AmountData = '0';

        if ($saletype == 1) { ## Cash Sales
        $tableM = 'pos_sales_m';

            $Amount = DB::table($tableM)
                ->where(['branch_id' => $branchID, 'is_delete' => 0])
                ->where(['sales_type' => 1, 'sales_date' => $date])
                ->get();

            $AmountData = $Amount->sum($table_field);
        } else if ($saletype == 2) { ## Installment Sales

            $tableM = 'pos_sales_m';

            $Amount = DB::table($tableM)
                ->where(['branch_id' => $branchID, 'is_delete' => 0])
                ->where(['sales_type' => 2, 'sales_date' => $date])
                ->get();

            $AmountData = $Amount->sum($table_field);
        } else if ($saletype == 3) {

            if ($table_field == 'service_charge') {
                $table = 'pos_sales_m';
                $Amount = DB::table($table)
                    ->where(['branch_id' => $branchID, 'is_delete' => 0])
                    ->where('sales_date', $date)
                    ->get();
                $AmountData = $Amount->sum($table_field);
            } else {
                $table = 'pos_collections';
                $Amount = DB::table($table)
                    ->where([['sales_type', 2], ['is_opening', 0], ['branch_id', $branchID], ['is_delete', 0], ['is_active', 1]])
                    ->where('collection_date', $date)
                    ->get();

                $AmountData = $Amount->sum($table_field);
            }
        } else if ($saletype == 4) {
            $tableM = 'pos_transfers_m';
            $Amount = DB::table($tableM)
                ->where(['branch_from' => $branchID, 'is_delete' => 0])
                ->where('transfer_date', $date)
                ->get();

            $AmountData = $Amount->sum($table_field);
        } else if ($saletype == 5) {
            $tableM = 'pos_purchases_m';

            $Amount = DB::table($tableM)
                ->where(['branch_id' => $branchID, 'is_delete' => 0])
                ->where('purchase_date', $date)->where('supplier_id', $data->supplier_id)
                ->get();

            $AmountData = $Amount->sum($table_field);
        } else if ($saletype == 6) {
            $tableM = 'pos_purchases_r_m';

            $Amount = DB::table($tableM)
                ->where(['branch_id' => $branchID, 'is_delete' => 0])
                ->where('return_date', $date)->where('supplier_id', $data->supplier_id)
                ->get();
            $AmountData = $Amount->sum($table_field);
        } else if ($saletype == 7) {
            $AmountData = '0';
        } else if ($saletype == 8) {
            $tableM = 'pos_sales_return_m';

            $Amount = DB::table($tableM)
                ->where(['branch_id' => $branchID, 'is_delete' => 0])
                ->where('return_date', $date)
                ->get();
            $AmountData = $Amount->sum($table_field);
        } else if ($saletype == 9) {
            $tableM = 'pos_issues_m';

            $Amount = DB::table($tableM)
                ->where(['branch_from' => $branchID, 'is_delete' => 0])
                ->where('issue_date', $date)
                ->get();

            $AmountData = $Amount->sum($table_field);
        } else if ($saletype == 10) {
            $table = 'pos_issues_r_m';

            $Amount = DB::table($table)
                ->where(['branch_from' => $branchID, 'is_delete' => 0])
                ->where('return_date', $date)
                ->get();

            $AmountData = $Amount->sum($table_field);
        } else {
            $AmountData = '0';
        }

        return $AmountData;
    }

    public function fnCheckChangeMonth($date1, $date2)
    {

        $MonthDate1 = new DateTime($date1);
        $MonthDate1 = $MonthDate1->format('m');

        $MonthDate2 = new DateTime($date2);
        $MonthDate2 = $MonthDate2->format('m');

        if ($MonthDate1 == $MonthDate2) {
            return false;
        } else {
            return true;
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
            ->orderBy('id', 'DESC')
            ->first();

        if (!empty($monthEndData)) {
            return true;
        } else {
            $BranchData = Branch::where('id', $branchID)->first();

            $branchsoft = new DateTime($BranchData->soft_start_date);
            $tempPre = new DateTime($date);

            if ($tempPre->format('Y-m') == $branchsoft->format('Y-m')) {
                return true;
            } else {
                return false;
            }
        }
    }

    public function ajaxDeleteDayEnd(Request $request)
    {
        if ($request->ajax()) {

            $key = $request->RowID;
            $Model = 'App\\Model\\POS\\DayEnd';
            $DayEndData = $Model::where('dayend_no', $key)->first();
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

                $checkMonthEnd = DB::table('pos_month_end')
                    ->where([
                        ['branch_id', '=', $branch_id],
                        ['month_date', $branch_month],
                        ['is_active', 0],
                        ['is_delete', 0],
                    ])
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

    public function scriptAutoVoucher(Request $request)
    {
        $notification = array();

        if ($request->ajax()) {

            $autoVoucherConfig = AutoVoucherConfig::where([['is_delete', 0], ['is_active', 1]])->count();
            if ($autoVoucherConfig < 1) {
                $notification = array(
                    'message' => 'Please Configure Auto Voucher !!!',
                    'alert_type' => 'error',
                );

                return response($notification);
            }

            $startDate = (empty($request->input('start_date'))) ? null : (new DateTime($request->input('start_date')))->format('Y-m-d');
            $endDate = (empty($request->input('end_date'))) ? null : (new DateTime($request->input('end_date')))->format('Y-m-d');
            $branchID = (empty($request->input('branch_id'))) ? null : $request->input('branch_id');

            $dayEndQuery = DB::table('pos_day_end as pde')
                ->where([['pde.is_delete', 0], ['pde.is_active', 0]])
            // ->select('pde.branch_id', 'pde.branch_date', 'pde.created_by')
                ->where(function ($dayEndQuery) use ($startDate, $endDate, $branchID) {
                    if (!empty($branchID)) {
                        $dayEndQuery->where('pde.branch_id', $branchID);
                    }
                    if (!empty($startDate) && !empty($endDate)) {

                        $startDate = new DateTime($startDate);
                        $startDate = $startDate->format('Y-m-d');

                        $endDate = new DateTime($endDate);
                        $endDate = $endDate->format('Y-m-d');

                        $dayEndQuery->whereBetween('pde.branch_date', [$startDate, $endDate]);
                    }
                })
                ->orderBy('pde.branch_date', 'ASC')
                ->get();

            if (count($dayEndQuery->toarray()) < 1) {
                $notification = array(
                    'message' => 'There is no Day End. Please try again !!!',
                    'alert_type' => 'warning',
                );
                return response($notification);
            }

            if ($dayEndQuery) {

                $branch_wise_group = array_unique($dayEndQuery->pluck('branch_id')->toarray());

                $branchQuery = DB::table('gnl_branchs')
                    ->whereIn('id', $branch_wise_group)
                    ->select('id', DB::raw('CONCAT(branch_name, "(", branch_code,") (",id,")") as branch_info'))
                    ->get();
                $branchQuery = $branchQuery->groupBy('id')->toarray();

                $data_set = array();
                $i = 0;
                foreach ($dayEndQuery as $Row) {
                    $branch_information = "";
                    if (isset($branchQuery[$Row->branch_id])) {
                        $branch_information = $branchQuery[$Row->branch_id][0]->branch_info;
                    }

                    $calculationData = $this->day_end_calculation($Row->branch_id, $Row->branch_date, $Row->company_id);

                    $totalCustomer = (isset($calculationData['totalCustomer'])) ? $calculationData['totalCustomer'] : 0;
                    $SalesProductQuantity = (isset($calculationData['SalesProductQuantity'])) ? $calculationData['SalesProductQuantity'] : 0;
                    $SalesTotalAmount = (isset($calculationData['SalesTotalAmount'])) ? $calculationData['SalesTotalAmount'] : 0;
                    $TotalCollection = (isset($calculationData['TotalCollection'])) ? $calculationData['TotalCollection'] : 0;
                    $TotalPurAmount = (isset($calculationData['TotalPurAmount'])) ? $calculationData['TotalPurAmount'] : 0;
                    $TotalDue = (isset($calculationData['TotalDue'])) ? $calculationData['TotalDue'] : 0;

                    $updateQuery = DB::table('pos_day_end')
                        ->where('id', $Row->id)
                        ->update(['total_customer' => $totalCustomer,
                            'total_product_quantity' => $SalesProductQuantity,
                            'total_sales_amount' => $SalesTotalAmount,
                            'total_collection' => $TotalCollection,
                            'total_purchases' => $TotalPurAmount,
                            'total_due' => $TotalDue]);

                    if ($updateQuery) {
                        $flag_update = true;
                    } else {
                        $flag_update = false;
                    }

                    $flag_auto_voucher = $this->auto_voucher_insert($Row->branch_id, $Row->branch_date, $Row->created_by);

                    $status = '';
                    if ($flag_update) {
                        $status .= '<p style="color:green;">Data Update => Success</p>';
                    } else {
                        $status .= '<p style="color:red;">Data Update => Error</p>';
                    }

                    if ($flag_auto_voucher) {
                        $status .= '<p style="color:green;">Voucher Update => Success</p>';
                    } else {
                        $status .= '<p style="color:red;">Voucher Update => Error</p>';
                    }

                    $temp_arr = array();
                    $temp_arr = [
                        ++$i,
                        $branch_information,
                        $Row->branch_date,
                        $status,
                    ];

                    $data_set[] = $temp_arr;
                }

                $notification = array(
                    'message' => 'Auto Voucher Creeate !',
                    'alert_type' => 'success',
                    'data' => $data_set,
                );

                return response($notification);
            }
        } else {
            return view('POS.Dayend.auto_voucher_script');
        }
    }
}
