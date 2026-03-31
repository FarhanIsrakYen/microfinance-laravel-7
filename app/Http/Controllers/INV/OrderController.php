<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use App\Model\INV\OrderDetails;
use App\Model\INV\OrderMaster;

use DateTime;
use DB;
use Illuminate\Http\Request;

use App\Services\RoleService as Role;
use App\Services\HrService as HRS;
use App\Services\CommonService as Common;
use App\Services\InvService as INVS;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $columns = [
                'id',
                'order_no',
                'order_date',
                'delivery_date',
                // 'order_from',
                'sup_name',
                'total_quantity',
                'status',
                'action',
            ];

            // Datatable Pagination Variable
            $totalData = OrderMaster::where([['is_completed', 0], ['is_delete', 0], ['is_active', 1]])
                ->whereIn('order_from', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
            $sl = $start + 1;

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');

            $orderData = DB::table('inv_orders_m as pom')
                ->where([['pom.is_completed', 0], ['pom.is_delete', 0], ['pom.is_active', 1]])
                ->whereIn('order_from', HRS::getUserAccesableBranchIds())
                ->select('pom.*', 'sup.sup_name')
                ->leftjoin('inv_suppliers as sup', function ($orderData) {
                    $orderData->on('sup.id', '=', 'pom.order_to')
                        ->where([['sup.is_delete', 0], ['sup.is_active', 1]]);
                })
                ->where(function ($orderData) use ($search) {
                    if (!empty($search)) {
                        $orderData->where('pom.order_no', 'LIKE', "%{$search}%")
                            ->orWhere('pom.order_date', 'LIKE', "%{date('d-m-Y', strtotime($search))}%")
                            ->orWhere('pom.delivery_date', 'LIKE', "%{date('d-m-Y', strtotime($search))}%")
                            ->orWhere('pom.total_quantity', 'LIKE', "%{$search}%")
                            ->orWhere('sup.sup_name', 'LIKE', "%{$search}%");
                    }
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('pom.order_no', 'DESC')
                ->orderBy('pom.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            $orderNoList = $orderData->pluck('order_no');

            $orderDetailsData = DB::table('inv_orders_d as pod')
                ->whereIn('pod.order_no', $orderNoList->toarray())
                ->join('inv_products as pro', function ($orderDetailsData) {
                    $orderDetailsData->on('pro.id', '=', 'pod.product_id')
                                     ->where('pro.is_delete', 0);
                })
                ->select('pod.order_no', 'pro.product_name')
                ->get();

            if (!empty($search)) {
                $totalFiltered = count($orderData);
            }

            $DataSet = array();

            foreach ($orderData as $row) {

                $TempSet = array();
                $IgnoreArray = array();

                if ($row->is_approve == 1) {
                    $statusText = '<span class="text-primary">Approved</span>';
                    $IgnoreArray = ['edit', 'delete', 'approve'];
                }
                else{
                    $key = array_search(7, array_column($this->GlobalRole, 'set_status'));

                    if (Common::getBranchId() == 1 && $key != false){
                        $statusText = '<a type="button" class="btn btn-danger btn-sm" href="' . url('inv/product_order/approve/' . $row->id) . '"><i class="fad fa-info-circle"></i> Approve</a>';
                    }
                    else{
                        $statusText = '<span class="text-danger">Pending</span>';
                    }

                    $IgnoreArray = ['approve'];
                }
                
                $product_names = $orderDetailsData->where('order_no', $row->order_no)
                    ->pluck('product_name')
                    ->toArray();
                if(count($product_names) > 0){
                    $TempSet = [
                        'id' => $sl++,
                        'order_no' => $row->order_no,
                        'order_date' => date('d-m-Y', strtotime($row->order_date)),
                        'delivery_date' => date('d-m-Y', strtotime($row->delivery_date)),
                        'order_to' => $row->sup_name,
                        'total_quantity' => $row->total_quantity,
                        'product_name'=> implode(', ', $product_names),
                        'status' => $statusText,
                        'action' => Role::roleWiseArray($this->GlobalRole, $row->id, $IgnoreArray),
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
        } else {
            return view('INV.ProductOrder.index');
        }
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {

            $requestData = $request->all();

            $product_id_arr = (isset($requestData['product_id_arr']) ? $requestData['product_id_arr'] : array());
            $product_quantity_arr = (isset($requestData['product_quantity_arr']) ? $requestData['product_quantity_arr'] : array());

            $orderDate = new DateTime($requestData['order_date']);
            $requestData['order_date'] = $orderDate->format('Y-m-d');

            $deliveryDate = new DateTime($requestData['delivery_date']);
            $requestData['delivery_date'] = $deliveryDate->format('Y-m-d');
            // dd($requestData);
            $orderData = OrderMaster::where('order_no', $requestData['order_no'])->first();

            if (!empty($orderData)) {
                $requestData['order_no'] = INVS::generateBillOrder(Common::getBranchId());
            }

            DB::beginTransaction();

            try {
                $isInsert = OrderMaster::create($requestData);

                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {

                        // $requestData['order_no'] = $isInsert->order_no;
                        $requestData['product_id'] = $product_id_sin;
                        $requestData['product_quantity'] = $product_quantity_arr[$key];

                        OrderDetails::create($requestData);
                    }
                }

                DB::commit();
                $notification = array(
                    'message' => 'Successfully Inserted Data',
                    'alert-type' => 'success',
                );

                return redirect('inv/product_order')->with($notification);
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
            return view('INV.ProductOrder.add');
        }
    }

    public function edit(Request $request, $id)
    {
        $orderM = OrderMaster::where('id', $id)->first();

        if ($request->isMethod('post')) {

            $requestData = $request->all();

            $product_id_arr = (isset($requestData['product_id_arr']) ? $requestData['product_id_arr'] : array());
            $product_quantity_arr = (isset($requestData['product_quantity_arr']) ? $requestData['product_quantity_arr'] : array());

            $orderDate = new DateTime($requestData['order_date']);
            $requestData['order_date'] = $orderDate->format('Y-m-d');

            $deliveryDate = new DateTime($requestData['delivery_date']);
            $requestData['delivery_date'] = $deliveryDate->format('Y-m-d');

            DB::beginTransaction();

            try {
                $isInsert = $orderM->update($requestData);

                OrderDetails::where('order_no', $orderM->order_no)->get()->each->delete();

                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {

                        $requestData['order_no'] = $orderM->order_no;
                        $requestData['product_id'] = $product_id_sin;
                        $requestData['product_quantity'] = $product_quantity_arr[$key];

                        OrderDetails::create($requestData);
                    }
                }

                DB::commit();
                $notification = array(
                    'message' => 'Successfully Updated Data',
                    'alert-type' => 'success',
                );

                return redirect('inv/product_order')->with($notification);
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
            $orderD = DB::table('inv_orders_m as pom')
                ->where('pom.id', $id)
                ->select('pod.*', 'pp.product_name')
                ->leftjoin('inv_orders_d as pod', function ($orderD) {
                    $orderD->on('pod.order_no', '=', 'pom.order_no');
                })
                ->leftjoin('inv_products as pp', function ($orderD) {
                    $orderD->on('pp.id', '=', 'pod.product_id')
                        ->where([['pp.is_delete', 0], ['pp.is_active', 1]]);
                })
                ->get();

            return view('INV.ProductOrder.edit', compact('orderM', 'orderD'));
        }
    }

    public function view($id = null)
    {
        $orderData = DB::table('inv_orders_m as pom')
            ->where('pom.id', $id)
            ->select('pom.*', 'sup.sup_name', 'sup.sup_addr', 'sup.sup_email', 'sup.sup_attentionA', 'sup.sup_attentionB', 'sup.sup_attentionC')
            ->leftjoin('inv_suppliers as sup', function ($orderData) {
                $orderData->on('sup.id', '=', 'pom.order_to')
                    ->where([['sup.is_delete', 0], ['sup.is_active', 1]]);
            })
            ->first();

        $orderD = DB::table('inv_orders_d as pod')
            ->where('pod.order_no', $orderData->order_no)
            ->select('pp.product_name', 'ppm.model_name', 'pod.product_quantity', 'br.branch_addr', 'br.contact_person', 'br.branch_phone', 'pod.requisition_no')
            ->leftjoin('inv_orders_m as pom', function ($orderM) {
                $orderM->on('pom.order_no', '=', 'pod.order_no')
                    ->where([['pom.is_delete', 0], ['pom.is_active', 1]]);
            })
            ->leftjoin('inv_suppliers as ps', function ($orderM) {
                $orderM->on('ps.id', '=', 'pom.order_to')
                    ->where([['ps.is_delete', 0], ['ps.is_active', 1]]);
            })
            ->leftjoin('inv_products as pp', function ($orderM) {
                $orderM->on('pp.id', '=', 'pod.product_id')
                    ->where([['pp.is_delete', 0], ['pp.is_active', 1]]);
            })
            ->leftjoin('inv_p_models as ppm', function ($orderM) {
                $orderM->on('ppm.id', '=', 'pp.prod_model_id')
                    ->where([['ppm.is_delete', 0], ['ppm.is_active', 1]]);
            })
            ->leftjoin('gnl_branchs as br', function ($orderM) {
                $orderM->on('br.id', '=', 'pom.order_from')
                    ->where([['br.is_delete', 0], ['br.is_active', 1]]);
            })
            ->get();

        return view('INV.ProductOrder.view', compact('orderData', 'orderD'));
    }

    public function delete($id = null)
    {
        $orderM = OrderMaster::where('id', $id)->first();

        if ($orderM->is_delete == 0) {

            $orderM->is_delete = 1;
            $isSuccess = $orderM->update();

            if ($isSuccess) {

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
        $orderM = OrderMaster::where('id', $id)->first();

        if ($orderM->is_approve == 0) {

            $orderM->is_approve = 1;
            $isSuccess = $orderM->update();

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
