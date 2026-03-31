<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\POS\RequisitionDetails;
use App\Model\POS\RequisitionMaster;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\PosService as POSS;
use App\Services\RoleService as Role;
use DateTime;
use DB;
use Illuminate\Http\Request;

class RequisitionController extends Controller
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
                0 => 'id',
                1 => 'requisition_date',
                2 => 'requisition_no',
                5 => 'total_quantity',
            ];

            // Datatable Pagination Variable

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $SDate = (empty($request->input('sDate'))) ? null : $request->input('sDate');
            $EDate = (empty($request->input('eDate'))) ? null : $request->input('eDate');
            $branchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');

            $requisitionData = DB::table('pos_requisitions_m as rm')
                ->where(['rm.is_delete' => 0])
                ->whereIn('rm.branch_from', HRS::getUserAccesableBranchIds())
                ->select('rm.*', 'brf.branch_name as branch_from', 'brf.branch_code as branch_code_from')
            // ->leftjoin('pos_suppliers as sup', function ($requisitionData) {
            //     $requisitionData->on('rm.supplier_id', '=', 'sup.id');
            // })
                ->leftjoin('gnl_branchs as brf', function ($requisitionData) {
                    $requisitionData->on('rm.branch_from', '=', 'brf.id')
                        ->where('brf.is_approve', 1);
                })
                ->where(function ($requisitionData) use ($search, $SDate, $EDate, $branchID) {
                    if (!empty($search)) {
                        $requisitionData->where('rm.requisition_no', 'LIKE', "%{$search}%")
                        // ->orWhere('sup.sup_name', 'LIKE', "%{$search}%")
                            ->orWhere('rm.total_quantity', 'LIKE', "%{$search}%")
                            ->orWhere('brf.branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('rm.requisition_date', 'LIKE', "%{date('d-m-Y', strtotime($search))}%");
                    }

                    if (!empty($SDate) && !empty($EDate)) {

                        $SDate = new DateTime($SDate);
                        $SDate = $SDate->format('Y-m-d');

                        $EDate = new DateTime($EDate);
                        $EDate = $EDate->format('Y-m-d');

                        $requisitionData->whereBetween('rm.requisition_date', [$SDate, $EDate]);
                    }
                    if (!empty($branchID)) {
                            $requisitionData->where('rm.branch_from', '=', $branchID);
                    }

                })
            // ->offset($start)
            // ->limit($limit)
            // ->orderBy('rm.requisition_no','DESC')
            // ->orderBy('rm.id', 'DESC')
                ->orderBy($order, $dir)
                ->orderBy('rm.requisition_date', 'DESC');
            // ->get();

            $tempQueryData = clone $requisitionData;
            $requisitionData = $requisitionData->offset($start)->limit($limit)->get();

            $totalData = RequisitionMaster::where('is_delete', '=', 0)
                ->whereIn('branch_from', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;
            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($branchID)) {
                $totalFiltered = $tempQueryData->count();
            }

            $requisitionNoList = $requisitionData->pluck('requisition_no');
            $reqDetailsData = DB::table('pos_requisitions_d as prd')
                ->whereIn('prd.requisition_no', $requisitionNoList->toarray())
                ->join('pos_products as pro', function ($reqDetailsData) {
                    $reqDetailsData->on('pro.id', '=', 'prd.product_id')
                        ->where('pro.is_delete', 0);
                })
                ->select('prd.requisition_no', 'pro.product_name')
                ->get();

            $DataSet = array();
            $sl = $start + 1;

            foreach ($requisitionData as $row) {

                $TempSet = array();
                $IgnoreArray = array();

                if ($row->is_approve == 1) {
                    $statusText = '<span class="text-primary">Approved</span>';
                    $IgnoreArray = ['edit', 'approve'];
                    // 'delete',
                } else {
                    $key = array_search(7, array_column($this->GlobalRole, 'set_status'));

                    if (Common::getBranchId() == 1 && $key != false) {
                        $statusText = '<a type="button" class="btn btn-danger btn-sm" href="' . url('pos/requisition/approve/' . $row->requisition_no) . '"><i class="fad fa-info-circle"></i> Approve</a>';
                    } else {
                        $statusText = '<span class="text-danger">Pending</span>';
                    }
                    $IgnoreArray = ['approve'];
                }

                if ($row->is_complete == 1) {
                    $statusText = '<span class="text-primary">Completed</span>';
                }

                $product_names = $reqDetailsData->where('requisition_no', $row->requisition_no)
                    ->pluck('product_name')
                    ->toArray();
                // if(count($product_names) > 0){
                $TempSet = [
                    'id' => $sl++,
                    'requisition_date' => date('d-m-Y', strtotime($row->requisition_date)),
                    'requisition_no' => $row->requisition_no,
                    'branch_from' => (!empty($row->branch_from)) ? $row->branch_from . "(" . $row->branch_code_from . ")" : "",
                    'total_quantity' => $row->total_quantity,
                    'product_name' => implode(', ', $product_names),
                    'status' => $statusText,
                    'action' => Role::roleWiseArray($this->GlobalRole, $row->requisition_no, $IgnoreArray),
                ];

                $DataSet[] = $TempSet;
                // }
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $DataSet,
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Requisition.index');
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

            // dd($requestData['branch_from']);

            $requisitionData = RequisitionMaster::where('requisition_no', $requestData['requisition_no'])->first();

            if (!empty($requisitionData)) {
                $requestData['requisition_no'] = POSS::generateBillRequisiton($requestData['branch_from']);
            }

            if (count(array_filter($product_id_arr)) <= 0) {
                $notification = array(
                    'message' => 'Something went wrong! Please try again.',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

            //dd($requestData['requisition_no']);
            DB::beginTransaction();

            try {
                $isInsert = RequisitionMaster::create($requestData);
                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {

                        // $requestData['requisition_no'] = $isInsert->id;
                        $requestData['product_id'] = $product_id_sin;
                        // $requestData['product_name'] = $product_name_arr[$key];
                        // $requestData['barcode_no'] = $product_barcode_arr[$key];
                        $requestData['product_quantity'] = $product_quantity_arr[$key];

                        RequisitionDetails::create($requestData);
                    }
                }

                DB::commit();
                $notification = array(
                    'message' => 'Successfully Inserted Data',
                    'alert-type' => 'success',
                );

                return redirect('pos/requisition')->with($notification);
            } catch (\Exception $e) {
                // dd($e);
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to Insert Data',
                    'alert-type' => 'error',
                );

                return redirect()->back()->with($notification);
            }
        } else {
            return view('POS.Requisition.add');
        }
    }

    public function edit(Request $request, $id)
    {
        $requisitionM = RequisitionMaster::where('requisition_no', $id)->first();

        if ($request->isMethod('post')) {

            $requestData = $request->all();

            $product_id_arr = (isset($requestData['product_id_arr']) ? $requestData['product_id_arr'] : array());
            // $product_name_arr = (isset($requestData['product_name_arr']) ? $requestData['product_name_arr'] : array());
            // $product_barcode_arr = (isset($requestData['product_barcode_arr']) ? $requestData['product_barcode_arr'] : array());
            $product_quantity_arr = (isset($requestData['product_quantity_arr']) ? $requestData['product_quantity_arr'] : array());

            $reqDate = new DateTime($requestData['requisition_date']);
            $reqDate = $reqDate->format('Y-m-d');
            $requestData['requisition_date'] = $reqDate;

            if (count(array_filter($product_id_arr)) <= 0) {
                $notification = array(
                    'message' => 'Something went wrong! Please try again.',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

            DB::beginTransaction();

            try {
                $isInsert = $requisitionM->update($requestData);

                RequisitionDetails::where('requisition_no', $id)->get()->each->delete();

                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {

                        $requestData['requisition_no'] = $requisitionM->requisition_no;
                        $requestData['product_id'] = $product_id_sin;
                        // $requestData['product_name'] = $product_name_arr[$key];
                        // $requestData['barcode_no'] = $product_barcode_arr[$key];
                        $requestData['product_quantity'] = $product_quantity_arr[$key];

                        RequisitionDetails::create($requestData);
                    }
                }

                DB::commit();
                $notification = array(
                    'message' => 'Successfully Updated Data',
                    'alert-type' => 'success',
                );

                return redirect('pos/requisition')->with($notification);
            } catch (\Exception $e) {
                // dd($e);
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to Update Data',
                    'alert-type' => 'error',
                );

                return redirect()->back()->with($notification);
            }
        } else {
            $requisitionD = DB::table('pos_requisitions_d as prd')
                ->where('prd.requisition_no', $id)
                ->select('prd.*', 'pp.product_name', 'pp.prod_barcode')
                ->leftjoin('pos_products as pp', function ($requisitionD) {
                    $requisitionD->on('pp.id', '=', 'prd.product_id')
                        ->where([['pp.is_delete', 0], ['pp.is_active', 1]]);
                })
                ->get();

            return view('POS.Requisition.edit', compact('requisitionM', 'requisitionD'));
        }
    }

    public function view($id = null)
    {
        $requisitionM = DB::table('pos_requisitions_m as rm')
            ->where('rm.requisition_no', $id)
            ->select('rm.*', 'ps.sup_name', 'brf.branch_name as branch_from', 'brt.branch_name as branch_to')
            ->leftjoin('pos_requisitions_d as rd', function ($queryData) {
                $queryData->on('rd.requisition_no', '=', 'rm.requisition_no');
            })
            ->leftjoin('pos_products as prod', function ($queryData) {
                $queryData->on('prod.id', '=', 'rd.product_id')
                    ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
            })
            ->leftjoin('pos_suppliers as ps', function ($queryData) {
                $queryData->on('ps.id', '=', 'prod.supplier_id')
                    ->where([['ps.is_delete', 0], ['ps.is_active', 1]]);
            })
            ->leftjoin('gnl_branchs as brf', function ($requisitionData) {
                $requisitionData->on('rm.branch_from', '=', 'brf.id')
                    ->where('brf.is_approve', 1);
            })
            ->leftjoin('gnl_branchs as brt', function ($requisitionData) {
                $requisitionData->on('rm.branch_to', '=', 'brt.id')
                    ->where('brt.is_approve', 1);
            })
            ->first();

        $requisitionD = DB::table('pos_requisitions_d as prd')
            ->where([['prd.requisition_no', $id]])
            ->select('prd.*', 'prod.product_name', 'prod.sys_barcode')
            ->leftjoin('pos_requisitions_m as prm', function ($queryData) {
                $queryData->on('prd.requisition_no', '=', 'prm.requisition_no')
                    ->where([['prm.is_delete', 0], ['prm.is_active', 1]]);
            })
            ->leftjoin('pos_products as prod', function ($queryData) {
                $queryData->on('prod.id', '=', 'prd.product_id')
                    ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
            })
            ->addSelect(['order_qtn' => DB::table('pos_orders_d as pod')
                    ->select(DB::raw('SUM(pod.product_quantity)'))
                    ->whereColumn([['prm.requisition_no', 'pod.requisition_no'], ['pod.product_id', 'prd.product_id']])
                    ->limit(1),
            ])
            ->get();

        return view('POS.Requisition.view', compact('requisitionM', 'requisitionD'));
    }

    public function delete($id = null)
    {
        $requiM = RequisitionMaster::where('requisition_no', $id)->first();

        if ($requiM->is_delete == 0) {

            $requiM->is_delete = 1;
            $isSuccess = $requiM->update();

            if ($isSuccess) {

                // RequisitionDetails::where('requisition_no', $requisitionM->id)->update(['is_delete' => 1]);
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
        $requisitionM = RequisitionMaster::where('requisition_no', $id)->first();

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
