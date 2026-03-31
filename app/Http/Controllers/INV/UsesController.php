<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\GNL\Division;


use App\Model\INV\UseReturnMaster;
use App\Model\INV\UsesDetails;
use App\Model\INV\UsesMaster;
use App\Model\INV\Product;

use App\Services\CommonService as Common;
use App\Services\RoleService as Role;
use App\Services\HrService as HRS;
use App\Services\InvService as INVS;

use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;


class UsesController extends Controller
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
            $columns = array(
                0 => 'inv_use_m.id',
                1 => 'inv_use_m.uses_date',
                2 => 'inv_use_m.uses_bill_no',
                3 => 'inv_use_m.total_quantity',
            );

            // Datatable Pagination Variable
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $sDate = (empty($request->input('sDate'))) ? null : $request->input('sDate');
            $eDate = (empty($request->input('eDate'))) ? null : $request->input('eDate');
            $branchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');
            // $PGroupID = (empty($request->input('PGroupID'))) ? null : $request->input('PGroupID');
            // $CategoryId = (empty($request->input('CategoryId'))) ? null : $request->input('CategoryId');
            // $SubCatID = (empty($request->input('SubCatID'))) ? null : $request->input('SubCatID');
            // $BrandID = (empty($request->input('BrandID'))) ? null : $request->input('BrandID');

            // Query
            $usesData = UsesMaster::where([['inv_use_m.is_delete', 0], ['inv_use_m.is_active', 1], ['inv_use_m.is_opening', 0]])
                ->select('inv_use_m.*')
                ->whereIn('inv_use_m.branch_id', HRS::getUserAccesableBranchIds())
                ->where(function ($usesData) use ($search, $sDate, $eDate, $branchID) {

                    if (!empty($search)) {
                        $usesData->where('inv_use_m.uses_bill_no', 'LIKE', "%{$search}%");
                    }

                    if (!empty($branchID)) {
                        $usesData->where('inv_use_m.branch_id', '=', $branchID);
                    }

                    if (!empty($sDate) && !empty($eDate)) {

                        $sDate = new DateTime($sDate);
                        $sDate = $sDate->format('Y-m-d');

                        $eDate = new DateTime($eDate);
                        $eDate = $eDate->format('Y-m-d');

                        $usesData->whereBetween('inv_use_m.uses_date', [$sDate, $eDate]);
                    }
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->orderBy('inv_use_m.uses_date', 'DESC')
                ->get();

            $billNoList = $usesData->pluck('uses_bill_no');
            $detailsData = DB::table('inv_use_d as dt')
                ->whereIn('dt.uses_bill_no', $billNoList->toarray())
                ->join('inv_products as pro', function ($detailsData) {
                    $detailsData->on('pro.id', '=', 'dt.product_id')
                                ->where('pro.is_delete', 0);
                })
                ->select('dt.uses_bill_no', 'pro.product_name')
                ->get();

            $totalData = UsesMaster::where(['inv_use_m.is_delete' => 0])
                ->whereIn('inv_use_m.branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = count($usesData);

            if (!empty($search) || !empty($sDate) || !empty($eDate) || !empty($branchID)) {
                $totalFiltered = count($usesData);
            }

            //get uses_bill_no for chack sales
            $usesRData = UseReturnMaster::where([['is_active', 1], ['is_delete', 0]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->pluck('uses_bill_no')
                ->toArray();

            $dataSet = array();
            $i = 0;

            foreach ($usesData as $row) {
                $TempSet = array();
                $IgnoreArray = array();

                if ((new dateTime($row->uses_date))->format('d-m-Y') != Common::systemCurrentDate($row->branch_id,'inv')) {
                    $IgnoreArray = ['delete', 'edit'];

                    if (in_array($row->uses_bill_no, $usesRData) == true) {
                        $IgnoreArray = ['delete', 'edit'];
                    }
                }

                $product_names = $detailsData->where('uses_bill_no', $row->uses_bill_no)
                    ->pluck('product_name')
                    ->toArray();

                if(count($product_names) > 0){
                    $TempSet = [
                        'id' => ++$i,
                        'uses_date' => (new dateTime($row->uses_date))->format('d-m-Y'),
                        'uses_bill_no' => $row->uses_bill_no,
                        'emp_name' => (!empty($row->employee['emp_name'])) ? $row->employee['emp_name']." (".$row->employee['emp_code'].")" : "",
                        'dept_name' => $row->department['dept_name'],
                        'product_name' => implode(', ', $product_names),
                        'branch_name' => (!empty($row->branch['branch_name'])) ? $row->branch['branch_name']." (".$row->branch['branch_code'].")" : "",
                        'total_quantity' => $row->total_quantity,
    
                        'action' => Role::roleWiseArray($this->GlobalRole, $row->uses_bill_no, $IgnoreArray),
                    ];
                }
                

                $dataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $dataSet,
            );

            echo json_encode($json_data);

        } else {
            return view('INV.Uses.index');
        }
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'uses_bill_no' => 'required',
                'uses_date' => 'required',
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();

            // dd($RequestData);

            /* Product Data */
            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            $product_name_arr = (isset($RequestData['product_name_arr']) ? $RequestData['product_name_arr'] : array());
            $product_serial_arr = (isset($RequestData['product_serial_arr']) ? $RequestData['product_serial_arr'] : array());

            /* Format date*/
            $uses_date = new DateTime($RequestData['uses_date']);
            $RequestData['uses_date'] = $uses_date->format('Y-m-d');

            // // Fiscal Year
            $fiscal_year = Common::systemFiscalYear($RequestData['uses_date'], $RequestData['company_id'], $RequestData['branch_id']);
            $RequestData['fiscal_year_id'] = $fiscal_year['id'];

            /*DB begain transaction*/
            DB::beginTransaction();

            try {

                if (UsesMaster::where('uses_bill_no', '=', $RequestData['uses_bill_no'])->exists()) {
                    $RequestData['uses_bill_no'] = INVS::generateBillSales($RequestData['branch_id']);
                }

                $isInsertM = UsesMaster::create($RequestData);

                if ($isInsertM) {
                    $IsInsertFlag = true;

                    foreach ($product_id_arr as $key => $product_id_sin) {
                        if (!empty($product_id_sin)) {

                            $RequestData['product_id'] = $product_id_sin;
                            $RequestData['product_quantity'] = $product_quantity_arr[$key];
                            $RequestData['product_serial_no'] = $product_serial_arr[$key];

                            // Sales Details insert
                            $isInsertD = UsesDetails::create($RequestData);

                            if ($isInsertD) {
                                continue;
                            } else {
                                $IsInsertFlag = false;
                            }
                        }
                    }

                }
                // commit DB and return with success masssage
                DB::commit();
                $notification = array(
                    'message' => 'Successfully Data Insert !!',
                    'alert-type' => 'success',
                );

                return Redirect::to('inv/use')->with($notification);
                // return Redirect::to('inv/use/invoice/'.$RequestData['uses_bill_no'])->with($notification);
            } catch (\Exception $e) {
                // dd($e);
                DB::rollBack();
                // role back undo all DB operation
                // return $e file line and error masssage in console log ;
                $notification = array(
                    'message' => 'Unsuccessful to insert data !!',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            return view('INV.Uses.add');
        }
    }

    public function edit(Request $request, $id = null)
    {

        $UseData = UsesMaster::where('uses_bill_no', $id)->first();
        $UseDataD = UsesDetails::where('uses_bill_no', $id)->get();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'uses_bill_no' => 'required',
            ]);

            /* ---------------------------------- Master Table update start ------------------------- */
            $RequestData = $request->all();

            // Date Format
            $uses_date = new DateTime($RequestData['uses_date']);
            $RequestData['uses_date'] = $uses_date->format('Y-m-d');

            // Product data
            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            $product_name_arr = (isset($RequestData['product_name_arr']) ? $RequestData['product_name_arr'] : array());
            $product_serial_arr = (isset($RequestData['product_serial_arr']) ? $RequestData['product_serial_arr'] : array());

            // // Fiscal Year
            $fiscal_year = Common::systemFiscalYear($RequestData['uses_date'], $UseData->company_id, $UseData->branch_id);
            $RequestData['fiscal_year_id'] = $fiscal_year['id'];

            DB::beginTransaction();

            try {

                $isUpdateM = $UseData->update($RequestData);

                if ($isUpdateM) {

                    /* Delete sales details data for this bill no */
                    UsesDetails::where('uses_bill_no', $id)->get()->each->delete();

                    $IsInsertFlag = true;

                    foreach ($product_id_arr as $key => $product_id_sin) {
                        if (!empty($product_id_sin)) {

                            $RequestData['product_id'] = $product_id_sin;
                            $RequestData['product_quantity'] = $product_quantity_arr[$key];
                            $RequestData['product_serial_no'] = $product_serial_arr[$key];

                            $isInsertD = UsesDetails::create($RequestData);

                            if ($isInsertD) {
                                continue;
                            } else {
                                $IsInsertFlag = false;
                            }
                        }
                    }

                    /* ------------------- Child Data insertion End ---------------- */
                }

                DB::commit();
                //commit and  return with success massage
                $notification = array(
                    'message' => 'Successfully Updated Data',
                    'alert-type' => 'success',
                );

                return Redirect::to('inv/use')->with($notification);

            } catch (\Exception $e) {

                DB::rollBack();
                // return $e file line and error masssage in console log ;
                $notification = array(
                    'message' => 'Unsuccessful to Update data.',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
            }

            /* ----------------------- Master table Update end ------------------------ */
        } else {
            return view('INV.Uses.edit', compact('UseData', 'UseDataD'));
        }
    }

    public function view($id = null)
    {
        $UseData = UsesMaster::where('uses_bill_no', $id)->first();
        $UseDataD = UsesDetails::where('uses_bill_no', $id)->get();

        return view('INV.Uses.view', compact('UseData', 'UseDataD'));
    }

    public function delete($id = null)
    {
        DB::beginTransaction();

        try {
            $UseData = UsesMaster::where('uses_bill_no', $id)->first();

            $UseData->is_delete = 1;
            $isSuccess = $UseData->update();

            DB::commit();
            $notification = array(
                'message' => 'Successfully Deleted',
                'alert-type' => 'success',
            );
            return redirect()->back()->with($notification);
        } catch (\Exception $e) {
            DB::rollBack();
            $notification = array(
                'message' => 'Unsuccessful to Delete',
                'alert-type' => 'error',
            );
            return redirect()->back()->with($notification);
        }
    }


    public function ajaxEmployeeLoad(Request $request){

        if ($request->ajax()) {

            $DepartmentID = $request->DepartmentID;
            $CompanyID = $request->CompanyID;
            $BranchID = $request->BranchID;

            $SelValue = (isset($request->SelValue)) ? $request->SelValue : null;

            $QueryData = DB::table('hr_employees')
                        ->where([['is_delete', 0], 
                            ['is_active', 1], 
                            ['department_id', $DepartmentID]
                        ])
                        ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                        ->select('employee_no', 'emp_name', 'emp_code')
                        ->orderBy('emp_name', 'ASC')
                        ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $SelectText = '';

                if ($SelValue != null) {
                    if ($SelValue == $Row->employee_no) {
                        $SelectText = 'selected="selected"';
                    }
                }
                $output .= '<option value="' . $Row->employee_no . '" ' . $SelectText . '>' . $Row->emp_name.' ('.$Row->emp_code.')'. '</option>';
            }

            echo $output;
        }
    }

    public function ajaxRequisitionLoad(Request $request){
        if ($request->ajax()) {

            $EmpNo = $request->EmpNo;
            $CompanyID = $request->CompanyID;
            $BranchID = $request->BranchID;

            $SelValue = (isset($request->SelValue)) ? $request->SelValue : null;

            $QueryData = DB::table('inv_requisitions_emp_m')
                        ->where([['is_delete', 0], ['is_active', 1], ['is_approve', 1],
                            ['emp_from', $EmpNo]])
                        ->select('requisition_no')
                        ->orderBy('requisition_no', 'ASC')
                        ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $SelectText = '';

                if ($SelValue != null) {
                    if ($SelValue == $Row->requisition_no) {
                        $SelectText = 'selected="selected"';
                    }
                }
                $output .= '<option value="' . $Row->requisition_no . '" ' . $SelectText . '>' . $Row->requisition_no . '</option>';
            }

            echo $output;
        }
    }

    public function ajaxRequisitionLoadForDept(Request $request){
        if ($request->ajax()) {

            $DepartmentID = $request->DepartmentID;
            $CompanyID = $request->CompanyID;
            $BranchID = $request->BranchID;

            $SelValue = (isset($request->SelValue)) ? $request->SelValue : null;

            $QueryData = DB::table('inv_requisitions_emp_m')
                        ->where([['is_delete', 0], ['is_active', 1], ['is_approve', 1],
                            ['dept_id', $DepartmentID]])
                        ->select('requisition_no')
                        ->orderBy('requisition_no', 'ASC')
                        ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $SelectText = '';

                if ($SelValue != null) {
                    if ($SelValue == $Row->requisition_no) {
                        $SelectText = 'selected="selected"';
                    }
                }
                $output .= '<option value="' . $Row->requisition_no . '" ' . $SelectText . '>' . $Row->requisition_no . '</option>';
            }

            echo $output;
        }
    }
}
