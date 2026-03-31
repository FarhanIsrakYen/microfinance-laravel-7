<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
// use App\Model\INV\Barcode;
use App\Model\INV\PurchaseDetails;
use App\Model\INV\PurchaseMaster;
use App\Model\INV\OrderMaster;
use App\Model\INV\Supplier;

use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\RoleService as Role;
use App\Services\InvService as INVS;

use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;

class OrderablePurchaseController extends Controller
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
                'pm.bill_no',
                // 'pm.requisition_no',
                'pm.order_no',
                'pm.delivery_no',
                'pm.purchase_date',
                'sup.sup_name',
                'pm.total_quantity',
                'pm.total_payable_amount',
                'pm.paid_amount',
                'pm.due_amount',
            ];

            // Datatable Pagination Variable
            $totalData = PurchaseMaster::where([['is_delete', '=', 0],['order_no','!=','']])
                ->whereIn('inv_purchases_m.branch_id', HRS::getUserAccesableBranchIds())
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
            $BranchID = (empty($request->input('BranchID'))) ? null : $request->input('BranchID');
            $SupplierID = (empty($request->input('SupplierID'))) ? null : $request->input('SupplierID');
            $PGroupID = (empty($request->input('PGroupID'))) ? null : $request->input('PGroupID');
            $CategoryId = (empty($request->input('CategoryId'))) ? null : $request->input('CategoryId');
            $SubCatID = (empty($request->input('SubCatID'))) ? null : $request->input('SubCatID');
            $BrandID = (empty($request->input('BrandID'))) ? null : $request->input('BrandID');

            // Query
            $PurchaseData = DB::table('inv_purchases_m as pm')
                ->where([['pm.is_delete' , 0],['pm.order_no','!=','']])
                ->whereIn('pm.branch_id', HRS::getUserAccesableBranchIds())
                ->select('pm.*', 'br.branch_name', 'sup.sup_name')
                ->leftjoin('gnl_branchs as br', function ($PurchaseData) {
                    $PurchaseData->on('pm.branch_id', '=', 'br.id')
                        ->where('br.is_approve', 1);
                })
                ->leftjoin('inv_suppliers as sup', function ($PurchaseData) {
                    $PurchaseData->on('pm.supplier_id', '=', 'sup.id');
                })
                ->where(function ($PurchaseData) use ($search, $BranchID, $SDate, $EDate, $SupplierID, $PGroupID, $CategoryId, $SubCatID, $BrandID) {

                    if (!empty($search)) {
                        $PurchaseData->where('sup.sup_name', 'LIKE', "%{$search}%")
                            ->orWhere('pm.bill_no', 'LIKE', "%{$search}%")
                            ->orWhere('br.branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('pm.purchase_date', 'LIKE', "%{$search}%");
                    }

                    if (!empty($BranchID)) {
                        $PurchaseData->where('pm.branch_id', '=', $BranchID);
                    }

                    if (!empty($SDate) && !empty($EDate)) {

                        $SDate = new DateTime($SDate);
                        $SDate = $SDate->format('Y-m-d');

                        $EDate = new DateTime($EDate);
                        $EDate = $EDate->format('Y-m-d');

                        $PurchaseData->whereBetween('pm.purchase_date', [$SDate, $EDate]);
                    }

                    if (!empty($SupplierID)) {
                        $PurchaseData->where('pm.supplier_id', '=', $SupplierID);
                    }
                    if (!empty($PGroupID)) {
                        $PurchaseData->where('prod.prod_group_id', '=', $PGroupID);
                    }
                    if (!empty($CategoryId)) {
                        $PurchaseData->where('prod.prod_cat_id', '=', $CategoryId);
                    }
                    if (!empty($SubCatID)) {
                        $PurchaseData->where('prod.prod_sub_cat_id', '=', $SubCatID);
                    }
                    if (!empty($BrandID)) {
                        $PurchaseData->where('prod.prod_brand_id', '=', $BrandID);
                    }

                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('pm.purchase_date', 'DESC')
                ->orderBy('pm.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            $billNoList = $PurchaseData->pluck('bill_no');
            $detailsData = DB::table('inv_purchases_d as dt')
                ->whereIn('dt.purchase_bill_no', $billNoList->toarray())
                ->join('inv_products as pro', function ($detailsData) {
                    $detailsData->on('pro.id', '=', 'dt.product_id')
                                ->where('pro.is_delete', 0);
                })
                ->select('dt.purchase_bill_no', 'pro.product_name')
                ->get();

            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($BranchID) || !empty($SupplierID) || !empty($PGroupID)
                || !empty($CategoryId) || !empty($SubCatID) || !empty($BrandID)) {
                $totalFiltered = count($PurchaseData);
            }

            $DataSet = array();
            $i = 0;

            // $RequisitionArray = array();

            foreach ($PurchaseData as $Row) {

                $TempSet = array();
                $IgnoreArray = array();

                if (date('d-m-Y', strtotime($Row->purchase_date)) != Common::systemCurrentDate()) {
                    $IgnoreArray = ['delete', 'edit'];
                }

                // if(in_array($Row->requisition_no, $RequisitionArray)){

                //     if(!in_array('edit', $IgnoreArray)){
                //         array_push($IgnoreArray, 'edit');
                //     }
                // }
                // else{
                //     array_push($RequisitionArray, $Row->requisition_no);
                // }
                $product_names = $detailsData->where('purchase_bill_no', $Row->bill_no)
                    ->pluck('product_name')
                    ->toArray();

                if(count($product_names) > 0){
                    $TempSet = [
                        'id' => ++$i,
                        'purchase_date' => date('d-m-Y', strtotime($Row->purchase_date)),
                        'bill_no' => $Row->bill_no,
                        // 'requisition_no' => $Row->requisition_no,
                        'order_no' => $Row->order_no,
                        // 'delivery_no' => $Row->delivery_no,
                        'supplier_name' => $Row->sup_name,
                        'product_name'=> implode(', ', $product_names),
                        'total_quantity' => $Row->total_quantity,
                        'total_payable_amount' => $Row->total_payable_amount,
                        'paid_amount' => $Row->paid_amount,
                        'due_amount' => $Row->due_amount,

                        'action' => Role::roleWiseArray($this->GlobalRole, $Row->id, $IgnoreArray),
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
            return view('INV.OrderablePurchases.index');
        }
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'bill_no' => 'required',
                'purchase_date' => 'required',
                'total_payable_amount' => 'required',
                'order_no' => 'required',
                'paid_amount' => 'required',
            ]);

            $RequestData = $request->all();

            /* Master Table Insertion */

            /* Format date*/
            $purchase_date = new DateTime($RequestData['purchase_date']);
            $RequestData['purchase_date'] = $purchase_date->format('Y-m-d');

            $RequestData['total_ordered_quantity'] = $RequestData['total_quantity'];
            $RequestData['total_received_quantity'] = $RequestData['total_quantity'];

            /* Product Data */
            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            $unit_cost_price_arr = (isset($RequestData['unit_cost_price_arr']) ? $RequestData['unit_cost_price_arr'] : array());
            $total_cost_price_arr = (isset($RequestData['total_cost_price_arr']) ? $RequestData['total_cost_price_arr'] : array());

            /*DB begain transaction*/
            DB::beginTransaction();

            try {

                if (PurchaseMaster::where('bill_no', '=', $RequestData['bill_no'])->exists()) {
                    $RequestData['bill_no'] = INVS::generateBillPurchase($RequestData['branch_id']);
                }

                $isInsert = PurchaseMaster::create($RequestData);

                if ($isInsert) {

                    /* Child Table Insertion */
                    // $RequestData['purchase_id'] = $isInsert->id;
                    $RequestData['purchase_bill_no'] = $RequestData['bill_no'];
                    $RequestData['company_id'] = $RequestData['company_id'];

                    $IsInsertFlag = true;

                    // start insert 2nd table
                    foreach ($product_id_arr as $key => $product_id_sin) {
                        if (!empty($product_id_sin)) {

                            $RequestData['product_id'] = $product_id_sin;
                            $RequestData['product_quantity'] = $product_quantity_arr[$key];
                            $RequestData['ordered_quantity'] = $product_quantity_arr[$key];
                            $RequestData['received_quantity'] = $product_quantity_arr[$key];
                            $RequestData['unit_cost_price'] = $unit_cost_price_arr[$key];
                            $RequestData['total_cost_price'] = $total_cost_price_arr[$key];

                            $isInsertM = PurchaseDetails::create($RequestData);

                            // if ($isInsertM) {
                            //     /* Barcode Table Insert Start */

                            //     $QtnBarcodeArr = array();
                            //     $BCounterArr = array();

                            //     $QtnBarcode = Barcode::where(['product_id' => $product_id_sin])
                            //         ->select(['id', 'product_id', 'product_barcode', 'qtn_barcode', 'bar_counter', 'bar_counter_array'])
                            //         ->first();

                            //     if ($QtnBarcode) {

                            //         // Old
                            //         $BCounter = $QtnBarcode->bar_counter;
                            //         // $QtnBarcodeArr = unserialize($QtnBarcode->qtn_barcode);
                            //         // $BCounterArr = unserialize($QtnBarcode->bar_counter_array);

                            //         if (!empty($QtnBarcode->qtn_barcode)) {
                            //             $QtnBarcodeArr = explode(',', $QtnBarcode->qtn_barcode);
                            //         }

                            //         $BCounterArr = unserialize($QtnBarcode->bar_counter_array);
                            //         $BasicBarcode = $QtnBarcode->product_barcode;

                            //         // New
                            //         $BCounterNew = $BCounter + $product_quantity_arr[$key];

                            //         for ($i = 1; $i <= $product_quantity_arr[$key]; $i++) {

                            //             $QtnBarcodeNew = $BasicBarcode . sprintf("%05d", $BCounter + $i);
                            //             $QtnBarcodeArr[] = $QtnBarcodeNew;
                            //             // array_push($QtnBarcodeArr, $QtnBarcodeNew);
                            //         }

                            //         $RequestData['bar_counter'] = $BCounterNew;
                            //         $RequestData['qtn_barcode'] = implode(',', $QtnBarcodeArr);

                            //         $BCounterArr[$RequestData['bill_no']] = $product_quantity_arr[$key];
                            //         $RequestData['bar_counter_array'] = serialize($BCounterArr);

                            //         $isUpdate = $QtnBarcode->update($RequestData);

                            //         if (!$isUpdate) {
                            //             $IsInsertFlag = false;
                            //         }
                            //     }

                            //     /* Barcode Insert End */
                            // } else {
                            //     $IsInsertFlag = false;
                            // }
                        }
                    }
                    // end insert 2nd table
                }

                $queryData = DB::table('inv_orders_m as pom')
                    ->where('pom.order_no', $RequestData['order_no'])
                    ->select('pod.product_id', 'pod.product_quantity')
                    ->leftjoin('inv_orders_d as pod', function ($queryData) {
                        $queryData->on('pod.order_no', '=', 'pom.order_no');
                    })
                    ->addSelect(['remaining_qtn' => DB::table('inv_purchases_m as ppm')
                            ->select(DB::raw('(pod.product_quantity - IFNULL(SUM(ppd.product_quantity), 0))'))
                            ->leftjoin('inv_purchases_d as ppd', function ($queryData) {
                                $queryData->on('ppd.purchase_bill_no', '=', 'ppm.bill_no');
                            })
                            ->whereColumn([['pom.order_no', 'ppm.order_no'], ['ppd.product_id', 'pod.product_id']])
                            ->where([['ppm.is_delete', 0], ['ppm.is_active', 1]])
                            ->limit(1),
                    ])
                    ->get();

                $flug = false;
                foreach ($queryData as $row) {
                    if ($row->remaining_qtn == 0) {
                        $flug = true;
                    }
                }

                if ($flug == true) {
                    OrderMaster::where('order_no', $RequestData['order_no'])->update(['is_completed' => 1]);
                }

                // commit DB and return with success masssage
                DB::commit();
                $notification = array(
                    'message' => 'Successfully Inserted Data in Purchase',
                    'alert-type' => 'success',
                );

                return Redirect::to('inv/purchase_orderable')->with($notification);
            } catch (\Exception $e) {
                dd($e);
                DB::rollBack();
                // role back undo all DB operation
                // return $e file line and error masssage in console log ;
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Purchase',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $SupplierData = Supplier::where(['is_delete' => 0, 'is_active' => 1])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->get();
            return view('INV.OrderablePurchases.add', compact('SupplierData'));
        }
    }

    public function edit(Request $request, $id = null)
    {
        $PurchaseData = PurchaseMaster::where('id', $id)->first();
        $PurchaseDataD = PurchaseDetails::where('purchase_bill_no', $PurchaseData->bill_no)->get();
        // $inv_products = Product::where('id', $id)->get();

        if ($request->isMethod('post')) {


            $validateData = $request->validate([
                'bill_no' => 'required',
                'purchase_date' => 'required',
                // 'order_no' => 'required',
                // 'total_amount' => 'required',
            ]);

            /* ---------------------------------- Master Table update start ------------------------- */
            $RequestData = $request->all();
            // Date Format
            $purchase_date = new DateTime($RequestData['purchase_date']);
            $RequestData['purchase_date'] = $purchase_date->format('Y-m-d');

            $RequestData['total_ordered_quantity'] = $RequestData['total_quantity'];
            $RequestData['total_received_quantity'] = $RequestData['total_quantity'];

            // Product Data
            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            $unit_cost_price_arr = (isset($RequestData['unit_cost_price_arr']) ? $RequestData['unit_cost_price_arr'] : array());
            $total_cost_price_arr = (isset($RequestData['total_cost_price_arr']) ? $RequestData['total_cost_price_arr'] : array());

            DB::beginTransaction();

            try {
                // Database update start  1st table

                $isUpdate = $PurchaseData->update($RequestData);

                if ($isUpdate) {

                    /* Barcode Table Update start */
                    // $BarcodeQuery = DB::table('inv_purchases_d')
                    //     ->join('pos_p_barcodes', 'pos_p_barcodes.product_id', 'inv_purchases_d.product_id')
                    //     ->where(['inv_purchases_d.purchase_bill_no' => $PurchaseData->bill_no])
                    //     ->select('pos_p_barcodes.*')
                    //     ->get();

                    // foreach ($BarcodeQuery as $BarcodeData) {

                    //     $temp_bar_counter_array = unserialize($BarcodeData->bar_counter_array);
                    //     $temp_last_bcounter = $temp_bar_counter_array[$RequestData['bill_no']];
                    //     $temp_qtn_barcode = explode(',', $BarcodeData->qtn_barcode);

                    //     $BarRowID = $BarcodeData->id;
                    //     $NextBCounter = ($BarcodeData->bar_counter - $temp_last_bcounter);
                    //     unset($temp_bar_counter_array[$RequestData['bill_no']]);
                    //     $Next_bar_counter_array = serialize($temp_bar_counter_array);
                    //     $p = 0;
                    //     while ($p < $temp_last_bcounter) {
                    //         array_pop($temp_qtn_barcode);
                    //         $p++;
                    //     }

                    //     $Next_qtn_barcode = implode(',', $temp_qtn_barcode);

                    //     // Update Query
                    //     $BarcodeUpdateQuery = DB::table('pos_p_barcodes')
                    //         ->where('id', $BarRowID)
                    //         ->update(['qtn_barcode' => $Next_qtn_barcode,
                    //             'bar_counter' => $NextBCounter,
                    //             'bar_counter_array' => $Next_bar_counter_array]);
                    // }
                    /* Barcode Table Update End */

                    /* Delete Purchase details data for this bill no */
                    // $RequestData['purchase_bill_no'] = $isInsert->bill_no;
                    PurchaseDetails::where('purchase_bill_no', $PurchaseData->bill_no)->get()->each->delete();

                    /* Child Table Insertion Start */
                    // $RequestData['purchase_id'] = $id;
                    $RequestData['purchase_bill_no'] = $RequestData['bill_no'];
                    $RequestData['company_id'] = $RequestData['company_id'];

                    $IsInsertFlag = true;

                    foreach ($product_id_arr as $key => $product_id_sin) {

                        if (!empty($product_id_sin)) {
                            $RequestData['product_id'] = $product_id_sin;
                            // $RequestData['barcode_no'] = $sys_barcode_arr[$key];
                            // $RequestData['product_name'] = $product_name_arr[$key];
                            $RequestData['product_quantity'] = $product_quantity_arr[$key];
                            $RequestData['ordered_quantity'] = $product_quantity_arr[$key];
                            $RequestData['received_quantity'] = $product_quantity_arr[$key];
                            $RequestData['unit_cost_price'] = $unit_cost_price_arr[$key];
                            $RequestData['total_cost_price'] = $total_cost_price_arr[$key];

                            // dd($RequestData);

                            $isInsertM = PurchaseDetails::create($RequestData);

                            // if ($isInsertM) {

                            //     /* Barcode Table Insert Start */
                            //     $QtnBarcodeArr = array();
                            //     $BCounterArr = array();

                            //     $QtnBarcode = Barcode::where(['product_id' => $product_id_sin])
                            //         ->select(['id', 'product_id', 'product_barcode', 'qtn_barcode', 'bar_counter', 'bar_counter_array'])
                            //         ->first();

                            //     if ($QtnBarcode) {

                            //         // Old
                            //         $BCounter = $QtnBarcode->bar_counter;
                            //         // $QtnBarcodeArr = unserialize($QtnBarcode->qtn_barcode);
                            //         // $BCounterArr = unserialize($QtnBarcode->bar_counter_array);

                            //         if (!empty($QtnBarcode->qtn_barcode)) {
                            //             $QtnBarcodeArr = explode(',', $QtnBarcode->qtn_barcode);
                            //         }

                            //         $BCounterArr = unserialize($QtnBarcode->bar_counter_array);
                            //         $BasicBarcode = $QtnBarcode->product_barcode;

                            //         // New
                            //         $BCounterNew = $BCounter + $product_quantity_arr[$key];

                            //         for ($i = 1; $i <= $product_quantity_arr[$key]; $i++) {

                            //             $QtnBarcodeNew = $BasicBarcode . sprintf("%05d", $BCounter + $i);
                            //             $QtnBarcodeArr[] = $QtnBarcodeNew;
                            //             // array_push($QtnBarcodeArr, $QtnBarcodeNew);
                            //         }

                            //         $RequestData['bar_counter'] = $BCounterNew;
                            //         $RequestData['qtn_barcode'] = implode(',', $QtnBarcodeArr);

                            //         $BCounterArr[$RequestData['bill_no']] = $product_quantity_arr[$key];
                            //         $RequestData['bar_counter_array'] = serialize($BCounterArr);

                            //         $isUpdate = $QtnBarcode->update($RequestData);

                            //         if (!$isUpdate) {
                            //             $IsInsertFlag = false;
                            //         }
                            //     }

                            //     /* Barcode Insert End */
                            // } else {
                            //     $IsInsertFlag = false;
                            // }
                        }
                    }

                    /* ------------------- Child Data insertion End ---------------- */
                }

                DB::commit();
                //commit and  return with success massage
                $notification = array(
                    'message' => 'Successfully Update Purchase Data',
                    'alert-type' => 'success',
                );

                return Redirect::to('inv/purchase_orderable')->with($notification);
            } catch (\Exception $e) {
                DB::rollBack();
                // return $e file line and error masssage in console log ;
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Purchase',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
            }

            /* ----------------------- Master table Update end ------------------------ */
        } else {
            $SupplierData = Supplier::where('is_delete', 0)
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->orderBy('id', 'DESC')->get();
            return view('INV.OrderablePurchases.edit', compact('PurchaseData', 'PurchaseDataD', 'SupplierData'));
        }
    }

    public function view($id = null)
    {
        $PurchaseData = PurchaseMaster::where('id', $id)->first();
        $PurchaseDataD = PurchaseDetails::where('purchase_bill_no', $PurchaseData->bill_no)->get();
        // $inv_products = Product::where('id', $id)->get();

        $Supplier = Supplier::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('INV.OrderablePurchases.view', compact('PurchaseData', 'PurchaseDataD', 'Supplier'));
    }

    public function delete($id = null)
    {
        $PurchaseData = PurchaseMaster::where('id', $id)->first();

        if ($PurchaseData->is_delete == 0) {

            $PurchaseData->is_delete = 1;
            $isSuccess = $PurchaseData->update();

            if ($isSuccess) {
                $notification = array(
                    'message' => 'Successfully Deleted',
                    'alert-type' => 'success',
                );
                return redirect()->back()->with($notification);
            }
        }
    }

    public function popUpSupplierDataInsert(Request $request)
    {
        $request->validate([
            'sup_name' => 'required',
            'supplier_type' => 'required',
            'sup_comp_name' => 'required',
            'sup_email' => 'required',
            'sup_phone' => 'required',
        ]);

        $isCreate = Supplier::create($request->all());

        if ($isCreate) {

            return json_encode(array(
                "statusCode" => 200,
            ));
        }
    }

}
