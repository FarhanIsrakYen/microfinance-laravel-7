<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use App\Services\HrService as HRS;
use App\Model\POS\OrderMaster;
use App\Model\POS\OrderDetails;
use App\Model\POS\RequisitionDetails;
use App\Services\CommonService as Common;
use DateTime;
use App\Services\RoleService as Role;

class OrderController extends Controller
{
	public function index(Request $request)
    {
    	if ($request->ajax())
    	{
    		$columns = [
                'id',
                'order_no',
                'order_date',
                'delivery_date',
                // 'order_from',
                'sup_name',
                'total_quantity',
                'action'
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
    			->leftjoin('inv_suppliers as sup', function($orderData){
    				$orderData->on('sup.id', '=', 'pom.order_to')
    					->where([['sup.is_delete', 0], ['sup.is_active', 1]]);
    			})
    			->where(function($orderData) use ($search){
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
                ->orderBy('pom.order_no','DESC')
                ->orderBy('pom.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();


            if (!empty($search)) {
                $totalFiltered = count($orderData);
            }

            $DataSet = array();

            foreach ($orderData as $row) {

                $TempSet = array();
                $IgnoreArray = array();

                $IgnoreArray = ['edit', 'delete'];

                $TempSet = [
                    'id' => $sl++,
                    'order_no' => $row->order_no,
                    'order_date' => date('d-m-Y', strtotime($row->order_date)),
                    'delivery_date' => date('d-m-Y', strtotime($row->delivery_date)),
                    // 'order_from' =>
                    'sup_name' => $row->sup_name,
                    'total_quantity' => $row->total_quantity,
                    'action' => Role::roleWiseArray($this->GlobalRole, $row->id, $IgnoreArray),
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
    	else 
    	{
    		return view('POS.ProductOrder.index');
    	}
    }    

    public function add(Request $request)
    {
    	if ($request->isMethod('post')) 
    	{
    		$requestData = $request->all();

            $order_check_box_arr = (isset($requestData['order_check_box_arr']) ? $requestData['order_check_box_arr'] : array());
            $requisition_id_arr = (isset($requestData['requisition_id_arr']) ? $requestData['requisition_id_arr'] : array());
            $order_to_arr = (isset($requestData['order_to_arr']) ? $requestData['order_to_arr'] : array());
            $requisition_no_arr = (isset($requestData['requisition_no_arr']) ? $requestData['requisition_no_arr'] : array());
            $requisition_date_arr = (isset($requestData['requisition_date_arr']) ? $requestData['requisition_date_arr'] : array());
            $requisition_branch_from_arr = (isset($requestData['requisition_branch_from_arr']) ? $requestData['requisition_branch_from_arr'] : array());
            $product_quantity_arr = (isset($requestData['product_quantity_arr']) ? $requestData['product_quantity_arr'] : array());
            $product_id_arr = (isset($requestData['product_id_arr']) ? $requestData['product_id_arr'] : array());
            // $total_quantity_arr = (isset($requestData['total_quantity_arr']) ? $requestData['total_quantity_arr'] : array());

            $order_date = new DateTime($request->order_date);
            $requestData['order_date'] = $order_date->format('Y-m-d');

            $delivery_date = new DateTime($request->delivery_date);
            $requestData['delivery_date'] = $delivery_date->format('Y-m-d');

            $ttl_qtn = 0;
            $flag = 1;
            $isInsert = null;
            foreach ($order_check_box_arr as $value) 
            {
            	if (!empty($value)) 
            	{
            		$key = array_search($value, $requisition_id_arr);

            		if ($flag == 1) {
            			$requestData['order_to'] = $order_to_arr[$key];
	            		$requestData['total_quantity'] = 0;
            		}

	            	$ttl_qtn += $product_quantity_arr[$key];

	            	DB::beginTransaction();

		            try 
		            {
		                if ($flag == 1) {
		                	$isInsert = OrderMaster::create($requestData);
		                	$flag++;
		                }

		                $requestData['order_id'] = $isInsert->id;
		                $requestData['requisition_no'] = $requisition_no_arr[$key];

		                $reqDate = new DateTime($requisition_date_arr[$key]);
		                $requestData['requisition_date'] = $reqDate->format('Y-m-d');

                        // $requestData['requisition_date'] = date('Y-m-d', strtotime($requisition_date_arr[$key]));

		                $requestData['requisition_branch_from'] = $requisition_branch_from_arr[$key];
		                $requestData['product_quantity'] = $product_quantity_arr[$key];
                        $requestData['product_id'] = $product_id_arr[$key];

		                OrderDetails::create($requestData);


                        $prod_id = $requestData['product_id'];
		                $reqUpdate = DB::table('inv_requisitions_m as prm')
                            ->where('requisition_no', $requestData['requisition_no'])
                            ->select('prm.id', 'product_quantity')
                            ->leftjoin('inv_requisitions_d as prd', function($reqUpdate) use($prod_id){
                                $reqUpdate->on('prd.requisition_id', '=', 'prm.id')
                                    ->where('product_id', $prod_id);
                            })
                            ->addSelect(['order_qtn' => DB::table('inv_orders_d as pod')
                                    ->select(DB::raw('SUM(pod.product_quantity)'))
                                    ->whereColumn([['prm.requisition_no', 'pod.requisition_no'], ['pod.product_id', 'prd.product_id']])
                                    ->limit(1)
                            ])
                            ->first();

                        if ($requestData['product_quantity'] >= $reqUpdate->product_quantity) {
                            RequisitionDetails::where([['requisition_id', $reqUpdate->id], ['product_id', $prod_id]])->update(['is_ordered' => 1]);
                        }
                        elseif ($reqUpdate->order_qtn >= $reqUpdate->product_quantity) {
                            RequisitionDetails::where([['requisition_id', $reqUpdate->id], ['product_id', $prod_id]])->update(['is_ordered' => 1]);
                        }

		                DB::commit();
		            }
		            catch(\Exception $e){
		                // dd($e);
		                DB::rollBack();
		                $notification = array(
		                    'message' => 'Unsuccessful to Insert Data',
		                    'alert-type' => 'error',
		                );

		                return redirect()->back()->with($notification);
		            }
            	}
            }

            $updateMaster = OrderMaster::where('id', $isInsert->id)->update(['total_quantity' => $ttl_qtn]);
            if ($updateMaster) {

            	$notification = array(
	                'message' => 'Successfully Inserted Data',
	                'alert-type' => 'success',
	            );

	    		return redirect('pos/product_order')->with($notification);
            }
            else{
		        DB::rollBack();
            	$notification = array(
                    'message' => 'Unsuccessful to Insert Data',
                    'alert-type' => 'error',
                );

                return redirect()->back()->with($notification);
            }
    	}
    	else 
    	{
    		return view('POS.ProductOrder.add');
    	}
    }

    public function view($id = null)
    {
        $orderData = DB::table('inv_orders_m as pom')
                ->where('pom.id', $id)
                ->select('pom.*', 'sup.sup_name', 'sup.sup_addr', 'sup.sup_email')
                ->leftjoin('inv_suppliers as sup', function($orderData){
                    $orderData->on('sup.id', '=', 'pom.order_to')
                        ->where([['sup.is_delete', 0], ['sup.is_active', 1]]);
                })
                ->first();

        $requistionData = DB::table('inv_orders_d as pod')
            ->where('pod.order_id', $id)
            ->select('pp.product_name', 'ppm.model_name', 'pod.product_quantity', 'pp.cost_price', 'br.branch_addr', 'br.contact_person', 'br.branch_phone', 'pod.requisition_no', DB::raw('(pod.product_quantity * pp.cost_price) as total_price'))
            ->leftjoin('inv_orders_m as pom', function($requistionData){
                $requistionData->on('pom.id', '=', 'pod.order_id')
                    ->where([['pom.is_delete', 0], ['pom.is_active', 1]]);
            })
            ->leftjoin('inv_products as pp', function($requistionData){
                $requistionData->on('pp.id', '=', 'pod.product_id')
                    ->where([['pp.is_delete', 0], ['pp.is_active', 1]]);
            })
            ->leftjoin('inv_p_models as ppm', function($requistionData){
                $requistionData->on('ppm.id', '=', 'pp.prod_model_id')
                    ->where([['ppm.is_delete', 0], ['ppm.is_active', 1]]);
            })
            ->leftjoin('gnl_branchs as br', function($requistionData){
                $requistionData->on('br.id', '=', 'pom.order_from')
                    ->where([['br.is_delete', 0], ['br.is_active', 1]]);
            })
            ->get();


    	return view('POS.ProductOrder.view', compact('orderData', 'requistionData'));
    }
}
