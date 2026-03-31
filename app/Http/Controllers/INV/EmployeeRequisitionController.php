<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Model\INV\EmployeeRequisitionMaster;
use App\Model\INV\EmployeeRequisitionDetails;
use DateTime;

use App\Services\HrService as HRS;
use App\Services\RoleService as Role;
use App\Services\CommonService as Common;
use App\Services\InvService as INVS;

class EmployeeRequisitionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $columns = [
                'id',
                'requisition_date',
                'requisition_no',
                'branch_id',
                'emp_from',
                'total_quantity',
                'supplier_name',
                'details',
                'status',
                'action'
            ];

            // Datatable Pagination Variable
            $totalData = EmployeeRequisitionMaster::where('is_delete', '=', 0)
                        // ->whereIn('emp_from', HRS::getUserAccesableBranchIds())
                        ->count();

            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
            $sl = $start + 1;

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');

            $requisitionData = DB::table('inv_requisitions_emp_m as rm')
                ->where(['rm.is_delete' => 0])
                // ->whereIn('rm.emp_from', HRS::getUserAccesableBranchIds())
                ->select('rm.*', 'brf.emp_name as emp_from', 'brf.emp_code')
                // ->leftjoin('inv_suppliers as sup', function ($requisitionData) {
                //     $requisitionData->on('rm.supplier_id', '=', 'sup.id');
                // })
                ->leftjoin('hr_employees as brf', function ($requisitionData) {
                    $requisitionData->on('rm.emp_from', '=', 'brf.employee_no')
                        ->where('brf.is_delete', 0);
                })
                ->where(function ($requisitionData) use ($search) {
                    if (!empty($search)) {
                        $requisitionData->where('rm.requisition_no', 'LIKE', "%{$search}%")
                            // ->orWhere('sup.sup_name', 'LIKE', "%{$search}%")
                            ->orWhere('brf.branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('rm.requisition_date', 'LIKE', "%{date('d-m-Y', strtotime($search))}%");
                    }
                })
                ->offset($start)
                ->limit($limit)
                // ->orderBy('rm.requisition_no','DESC')
                ->orderBy('rm.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            $requisitionNoList = $requisitionData->pluck('requisition_no');

            $reqDetailsData = DB::table('inv_requisitions_emp_d as prd')
                ->whereIn('prd.requisition_no', $requisitionNoList->toarray())
                ->join('inv_products as pro', function ($reqDetailsData) {
                    $reqDetailsData->on('pro.id', '=', 'prd.product_id')
                                   ->where('pro.is_delete', 0);
                })
                ->select('prd.requisition_no', 'pro.product_name')
                ->get();


            if (!empty($search)) {
                $totalFiltered = count($requisitionData);
            }

            $DataSet = array();

            foreach ($requisitionData as $row) {

                $TempSet = array();
                $IgnoreArray = array();

                if ($row->is_approve == 1) {
                    $statusText = '<span class="text-primary">Approved</span>';
                    $IgnoreArray = ['edit', 'delete', 'approve'];
                }
                else{
                    $key = array_search(7, array_column($this->GlobalRole, 'set_status'));

                    if (Common::getBranchId() == 1 && $key != false){
                        $statusText = '<a type="button" class="btn btn-danger btn-sm" href="'. url('inv/requisition_emp/approve/'.$row->requisition_no).'"><i class="fad fa-info-circle"></i> Approve</a>';
                    }
                    else{
                        $statusText = '<span class="text-danger">Pending</span>';
                    }
                    $IgnoreArray = ['approve'];
                }

                $product_names = $reqDetailsData->where('requisition_no', $row->requisition_no)
                    ->pluck('product_name')
                    ->toArray();
                if(count($product_names) > 0){
                    $TempSet = [
                        'id' => $sl++,
                        'requisition_date' => date('d-m-Y', strtotime($row->requisition_date)),
                        'requisition_no' => $row->requisition_no,
                        // 'branch_id' => $row->branch_id,
                        'emp_from' => (!empty($row->emp_from)) ? $row->emp_from."(".$row->emp_code.")" : "",
                        'product_name'=> implode(', ', $product_names),
                        'total_quantity' => $row->total_quantity,
                        // 'supplier_name' => $row->sup_name,
                        'status' => $statusText,
                        'action' => Role::roleWiseArray($this->GlobalRole, $row->requisition_no, $IgnoreArray),
                    ];

                    $DataSet[] = $TempSet;
                }
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $DataSet,
            );

            echo json_encode($json_data);
        }
        else{
            return view('INV.EmployeeRequisition.index');
        }
    }

    public function add(Request $request)
    {
    	if ($request->isMethod('post')) {

            $requestData = $request->all();

            $product_id_arr = (isset($requestData['product_id_arr']) ? $requestData['product_id_arr'] : array());
            // $product_name_arr = (isset($requestData['product_name_arr']) ? $requestData['product_name_arr'] : array());
            // $product_barcode_arr = (isset($requestData['product_barcode_arr']) ? $requestData['product_barcode_arr'] : array());
            $product_quantity_arr = (isset($requestData['product_quantity_arr']) ? $requestData['product_quantity_arr'] : array());
            //$product_quantity_arr = (isset($requestData['product_quantity_arr']) ? $requestData['product_quantity_arr'] : array());


            $reqDate = new DateTime($requestData['requisition_date']);
            $reqDate = $reqDate->format('Y-m-d');
            $requestData['requisition_date'] = $reqDate;
          //  $requestData['requisition_no'] = $requestData['requisition_no'];
          // dd($requestData);

            // dd($requestData['emp_from']);

            $requisitionData = EmployeeRequisitionMaster::where('requisition_no', $requestData['requisition_no'])->first();

            if(!empty($requisitionData)){
                $requestData['requisition_no'] = INVS::generateBillRequisitonEmp($requestData['branch_id']);
            }

            DB::beginTransaction();

            try{
                $isInsert = EmployeeRequisitionMaster::create($requestData);
                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {

                        // $requestData['requisition_no'] = $isInsert->id;
                        $requestData['product_id'] = $product_id_sin;
                        // $requestData['product_name'] = $product_name_arr[$key];
                        // $requestData['barcode_no'] = $product_barcode_arr[$key];
                        $requestData['product_quantity'] = $product_quantity_arr[$key];

                        EmployeeRequisitionDetails::create($requestData);
                    }
                }

                DB::commit();
                $notification = array(
                    'message' => 'Successfully Inserted Data',
                    'alert-type' => 'success',
                );

                return redirect('inv/requisition_emp')->with($notification);
            }
            catch(\Exception $e){
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to Insert Data',
                    'alert-type' => 'error',
                );

                return redirect()->back()->with($notification);
            }
    	}
    	else{
            $employeeData = DB::table('hr_employees')->where([['is_delete',0],['is_active',1]])
                            ->select('id','emp_name', 'employee_no', 'emp_code')->get();
            $departmentData = DB::table('hr_departments')->where([['is_delete',0],['is_active',1]])
                            ->select('id','dept_name', 'short_name')->get();

            $roomData = DB::table('hr_rooms')->where([['is_delete',0],['is_active',1]])
                            ->select('id','room_name', 'room_code')->get();
    		return view('INV.EmployeeRequisition.add',compact('employeeData','departmentData','roomData'));
    	}
    }

    public function edit(Request $request, $id)
    {
        $requisitionM = EmployeeRequisitionMaster::where('requisition_no', $id)->first();

        if ($request->isMethod('post')) {

            $requestData = $request->all();

            $product_id_arr = (isset($requestData['product_id_arr']) ? $requestData['product_id_arr'] : array());
            // $product_name_arr = (isset($requestData['product_name_arr']) ? $requestData['product_name_arr'] : array());
            // $product_barcode_arr = (isset($requestData['product_barcode_arr']) ? $requestData['product_barcode_arr'] : array());
            $product_quantity_arr = (isset($requestData['product_quantity_arr']) ? $requestData['product_quantity_arr'] : array());

            $reqDate = new DateTime($requestData['requisition_date']);
            $reqDate = $reqDate->format('Y-m-d');
            $requestData['requisition_date'] = $reqDate;

            DB::beginTransaction();

            try{
                $isInsert = $requisitionM->update($requestData);

                EmployeeRequisitionDetails::where('requisition_no', $id)->get()->each->delete();

                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {

                        $requestData['requisition_no'] = $requisitionM->requisition_no;
                        $requestData['product_id'] = $product_id_sin;
                        // $requestData['product_name'] = $product_name_arr[$key];
                        // $requestData['barcode_no'] = $product_barcode_arr[$key];
                        $requestData['product_quantity'] = $product_quantity_arr[$key];

                        EmployeeRequisitionDetails::create($requestData);
                    }
                }

                DB::commit();
                $notification = array(
                    'message' => 'Successfully Updated Data',
                    'alert-type' => 'success',
                );

                return redirect('inv/requisition_emp')->with($notification);
            }
            catch(\Exception $e){
                // dd($e);
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to Update Data',
                    'alert-type' => 'error',
                );

                return redirect()->back()->with($notification);
            }
        }
        else{
            $requisitionD = DB::table('inv_requisitions_emp_d as prd')
                        ->where('prd.requisition_no', $id)
                        ->select('prd.*', 'pp.product_name')
                        ->leftjoin('inv_products as pp', function($requisitionD){
                            $requisitionD->on('pp.id', '=', 'prd.product_id')
                                ->where([['pp.is_delete', 0], ['pp.is_active', 1]]);
                        })
                        ->get();
            $employeeData = DB::table('hr_employees')->where([['is_delete',0],['is_active',1]])
                            ->select('id','emp_name', 'employee_no', 'emp_code')->get();
            $departmentData = DB::table('hr_departments')->where([['is_delete',0],['is_active',1]])
                            ->select('id','dept_name', 'short_name')->get();

            $roomData = DB::table('hr_rooms')->where([['is_delete',0],['is_active',1]])
                            ->select('id','room_name', 'room_code')->get();

            return view('INV.EmployeeRequisition.edit', compact('requisitionM', 'requisitionD','employeeData','departmentData','roomData'));
        }
    }

    public function view($id = null)
    {
        $requisitionM = DB::table('inv_requisitions_emp_m as rm')
                        ->where('rm.requisition_no', $id)
                        ->select('rm.*', 'ps.sup_name', 'brf.emp_name as emp_from', 'brt.branch_name as branch_id', 'hd.dept_name', 'hrm.room_name')
                        ->leftjoin('inv_requisitions_emp_d as rd', function($queryData){
                            $queryData->on('rd.requisition_no', '=', 'rm.requisition_no');
                        })
                        ->leftjoin('inv_products as prod', function($queryData){
                            $queryData->on('prod.id', '=', 'rd.product_id')
                                ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                        })
                        ->leftjoin('inv_suppliers as ps', function($queryData){
                            $queryData->on('ps.id', '=', 'prod.supplier_id')
                                ->where([['ps.is_delete', 0], ['ps.is_active', 1]]);
                        })
                        ->leftjoin('hr_employees as brf', function ($requisitionData) {
                            $requisitionData->on('rm.emp_from', '=', 'brf.employee_no')
                                ->where('brf.is_delete', 0);
                        })
                        ->leftjoin('gnl_branchs as brt', function ($requisitionData) {
                            $requisitionData->on('rm.branch_id', '=', 'brt.id')
                                ->where('brt.is_approve', 1);
                        })
                        ->leftjoin('hr_departments as hd', function ($requisitionData) {
                            $requisitionData->on('rm.dept_id', '=', 'hd.id')
                                ->where([['hd.is_delete', 0], ['hd.is_active', 1]]);
                        })
                        ->leftjoin('hr_rooms as hrm', function ($requisitionData) {
                            $requisitionData->on('rm.dept_id', '=', 'hrm.id')
                                ->where([['hrm.is_delete', 0], ['hrm.is_active', 1]]);
                        })
                        ->first();


        $requisitionD = DB::table('inv_requisitions_emp_d as prd')
                ->where([['prd.requisition_no', $id]])
                ->select('prd.*', 'prod.product_name', 'prod.product_code')
                ->leftjoin('inv_requisitions_emp_m as prm', function($queryData){
                    $queryData->on('prd.requisition_no', '=', 'prm.requisition_no')
                        ->where([['prm.is_delete', 0], ['prm.is_active', 1]]);
                })
                ->leftjoin('inv_products as prod', function($queryData){
                    $queryData->on('prod.id', '=', 'prd.product_id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->addSelect(['order_qtn' => DB::table('inv_orders_d as pod')
                        ->select(DB::raw('SUM(pod.product_quantity)'))
                        ->whereColumn([['prm.requisition_no', 'pod.requisition_no'], ['pod.product_id', 'prd.product_id']])
                        ->limit(1)
                ])
                ->get();

        return view('INV.EmployeeRequisition.view', compact('requisitionM', 'requisitionD'));
    }

    public function delete($id = null)
    {
        $requiM = EmployeeRequisitionMaster::where('requisition_no', $id)->first();

        if ($requiM->is_delete == 0) {

            $requiM->is_delete = 1;
            $isSuccess = $requiM->update();

            if ($isSuccess) {

                // EmployeeRequisitionDetails::where('requisition_no', $requisitionM->id)->update(['is_delete' => 1]);
                $notification = array(

                    'message' => 'Successfully Deleted',
                    'alert-type' => 'success',
                );
                return redirect()->back()->with($notification);
            }
        }
    }

    public function isApprove($id = null)
    {
        $requisitionM = EmployeeRequisitionMaster::where('requisition_no', $id)->first();

        if ($requisitionM->is_approve == 0) {

            $requisitionM->is_approve = 1;
            $isSuccess = $requisitionM->update();

            if ($isSuccess) {

                $notification = array(

                    'message' => 'Successfully Aproved',
                    'alert-type' => 'success',
                );
                return redirect()->back()->with($notification);
            }
        }
    }

}
