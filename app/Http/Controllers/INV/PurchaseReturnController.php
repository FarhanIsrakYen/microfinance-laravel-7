<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use App\Model\INV\PurchaseReturnDetails;
use App\Model\INV\PurchaseReturnMaster;
use App\Model\INV\Supplier;

use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;

use App\Services\RoleService as Role;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\InvService as INVS;

class PurchaseReturnController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $columns = array(
                0 => 'id',
                1 => 'bill_no',
                2 => 'return_date',
                3 => 'sup_name',
                4 => 'total_quantity',
                // 5 => 'total_amount',
                5 => 'branch_name',
            );

            $totalData = PurchaseReturnMaster::where('is_delete', 0)
                ->whereIn('inv_purchases_r_m.branch_id', HRS::getUserAccesableBranchIds())
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

            // Query
            $PurReturnData = PurchaseReturnMaster::where('inv_purchases_r_m.is_delete', '=', 0)
                ->whereIn('inv_purchases_r_m.branch_id', HRS::getUserAccesableBranchIds())
                ->select('inv_purchases_r_m.*', 'inv_suppliers.sup_name', 'gnl_branchs.branch_name')
                ->leftJoin('inv_suppliers', 'inv_purchases_r_m.supplier_id', '=', 'inv_suppliers.id')
                ->leftJoin('gnl_branchs', 'inv_purchases_r_m.branch_id', '=', 'gnl_branchs.id')
                ->where(function ($PurReturnData) use ($search, $SDate, $EDate, $BranchID, $SupplierID) {
                    if (!empty($search)) {
                        $PurReturnData->orWhere('inv_purchases_r_m.bill_no', 'LIKE', "%{$search}%")
                            ->orWhere('inv_purchases_r_m.return_date', 'LIKE', "%{$search}%")
                            ->orWhere('inv_purchases_r_m.total_quantity', 'LIKE', "%{$search}%")
                            // ->orWhere('inv_purchases_r_m.total_amount', 'LIKE', "%{$search}%")
                            ->orWhere('inv_suppliers.sup_name', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_branchs.branch_name', 'LIKE', "%{$search}%");
                    }
                    if (!empty($BranchID)) {
                        $PurReturnData->where('inv_purchases_r_m.branch_id', '=', $BranchID);
                    }

                    if (!empty($SDate) && !empty($EDate)) {

                        $SDate = new DateTime($SDate);
                        $SDate = $SDate->format('Y-m-d');

                        $EDate = new DateTime($EDate);
                        $EDate = $EDate->format('Y-m-d');

                        $PurReturnData->whereBetween('inv_purchases_r_m.return_date', [$SDate, $EDate]);
                    }

                    if (!empty($SupplierID)) {
                        $PurReturnData->where('inv_purchases_r_m.supplier_id', '=', $SupplierID);
                    }
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('inv_purchases_r_m.return_date','DESC')
                ->orderBy('inv_purchases_r_m.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            $billNoList = $PurReturnData->pluck('bill_no');
            $detailsData = DB::table('inv_purchases_r_d as dt')
                ->whereIn('dt.pr_bill_no', $billNoList->toarray())
                ->join('inv_products as pro', function ($detailsData) {
                    $detailsData->on('pro.id', '=', 'dt.product_id')
                                ->where('pro.is_delete', 0);
                })
                ->select('dt.pr_bill_no', 'pro.product_name')
                ->get();

            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($BranchID) || !empty($SupplierID)) {
                $totalFiltered = count($PurReturnData);
            }

            $data = array();
            if (!empty($PurReturnData)) {
                $i = 0;
                foreach ($PurReturnData as $Data) {
                    // $delete = url('/preturn/delete', $Data->id);
                    // $edit = url('/preturn/edit/', $Data->id);
                    // $view = url('/preturn/view', $Data->id);

                    $product_names = $detailsData->where('pr_bill_no', $Data->bill_no)
                    ->pluck('product_name')
                    ->toArray();

                    $IgnoreArray = array();

                    if(date('d-m-Y', strtotime($Data->return_date)) != Common::systemCurrentDate($Data->branch_id,'inv')){
                        $IgnoreArray = ['delete', 'edit'];
                    }

                    if(count($product_names) > 0){
                        $nestedData['id'] = ++$i;

                        $nestedData['bill_no'] = $Data->bill_no;

                        $returnDate = new DateTime($Data->return_date);
                        $nestedData['return_date'] = $returnDate->format('d-m-Y');

                        $nestedData['sup_name'] = $Data->sup_name;
                        $nestedData['product_name'] = implode(', ', $product_names);
                        $nestedData['total_quantity'] = $Data->total_quantity;
                        // $nestedData['total_amount'] = $Data->total_amount;
                        $nestedData['branch_name'] = $Data->branch_name;

                        // $nestedData['action'] = "&emsp;<a href='{$edit}'><i class='icon wb-edit mr-2 blue-grey-600'></i></a>
                        //                         &emsp;<a href='{$view}'><i class='icon wb-eye mr-2 blue-grey-600'></i></a>
                        //                         &emsp;<a href='{$delete}' class='btnDelete'><i class='icon wb-trash mr-2 blue-grey-600'></i></a>";

                        $nestedData['action'] = Role::roleWiseArray($this->GlobalRole, $Data->id, $IgnoreArray);
                        $data[] = $nestedData;
                    }
                }
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data,
            );

            echo json_encode($json_data);

        } else {
            $SupplierData = Supplier::where(['is_delete' => 0, 'is_active' => 1])->get();
            return view('INV.PurchasesReturn.index', compact('SupplierData'));
        }
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'company_id' => 'required',
                'supplier_id' => 'required',
                'bill_no' => 'required',
                'return_date' => 'required',
                // 'branch_id' => 'required',
                // 'total_amount' => 'required',
            ]);

            $RequestData = $request->all();
            // dd( $RequestData);

            $return_date = new DateTime($RequestData['return_date']);
            $RequestData['return_date'] = $return_date->format('Y-m-d');

            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            // $sys_barcode_arr = (isset($RequestData['sys_barcode_arr']) ? $RequestData['sys_barcode_arr'] : array());
            // $product_bar_arr = (isset($RequestData['product_bar_arr']) ? $RequestData['product_bar_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            // $unit_cost_price_arr = (isset($RequestData['unit_cost_price_arr']) ? $RequestData['unit_cost_price_arr'] : array());
            // $total_cost_price_arr = (isset($RequestData['total_cost_price_arr']) ? $RequestData['total_cost_price_arr'] : array());

            DB::beginTransaction();

            try {

                if (PurchaseReturnMaster::where('bill_no', '=', $RequestData['bill_no'])->exists()) 
                {
                   $RequestData['bill_no'] = INVS::generateBillPurchaseReturn($RequestData['branch_id']);
                }

                $isInsert = PurchaseReturnMaster::create($RequestData);

                /* Child Table Insertion */
                // $RequestData['pr_id'] = $isInsert->id;
                $RequestData['pr_bill_no'] = $RequestData['bill_no'];
                // $RequestData['company_id'] = $RequestData['company_id'];
                // $RequestData['branch_id'] = $RequestData['branch_id'];

                // $return_date = new DateTime($RequestData['return_date']);
                // $RequestData['return_date'] = $return_date->format('Y-m-d');

                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {

                        $RequestData['product_id'] = $product_id_sin;
                        $RequestData['product_quantity'] = $product_quantity_arr[$key];
                        // $RequestData['unit_cost_price'] = $unit_cost_price_arr[$key];
                        // $RequestData['total_cost_price'] = $total_cost_price_arr[$key];
                        // $RequestData['product_barcode'] = $product_bar_arr[$key];
                        // $RequestData['product_system_barcode'] = $sys_barcode_arr[$key];

                        PurchaseReturnDetails::create($RequestData);
                    }
                }

                DB::commit();

                $notification = array(
                    'message' => 'Successfully Inserted Data',
                    'alert-type' => 'success',
                );
                return redirect('inv/purchase_return')->with($notification);
            } 
            catch (\Exception $e) {

                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to insert data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            return view('INV.PurchasesReturn.add');
        }
    }

    public function edit(Request $request, $id = null)
    {

        $PReturnData = PurchaseReturnMaster::where('id', $id)->first();
        $PReturnDataD = PurchaseReturnDetails::where('pr_bill_no', $PReturnData->bill_no)->get();

        // $inv_products = Product::where('id', $id)->get();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'company_id' => 'required',
                'supplier_id' => 'required',
                'bill_no' => 'required',
                'return_date' => 'required',
                // 'branch_id' => 'required',
                // 'total_amount' => 'required',
            ]);

            $RequestData = $request->all();
            // dd( $RequestData);

            $return_date = new DateTime($RequestData['return_date']);
            $RequestData['return_date'] = $return_date->format('Y-m-d');

            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_code_arr = (isset($RequestData['product_code_arr']) ? $RequestData['product_code_arr'] : array());
            $product_bar_arr = (isset($RequestData['product_bar_arr']) ? $RequestData['product_bar_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            // $unit_cost_price_arr = (isset($RequestData['unit_cost_price_arr']) ? $RequestData['unit_cost_price_arr'] : array());
            // $total_cost_price_arr = (isset($RequestData['total_cost_price_arr']) ? $RequestData['total_cost_price_arr'] : array());

            DB::beginTransaction();

            try {
                $isUpdate = $PReturnData->update($RequestData);

                /* Delete Purchase Return details data for this bill no */
                PurchaseReturnDetails::where('pr_bill_no', $PReturnData->bill_no)->get()->each->delete();

                /* Child Table Insertion */
                // $RequestData['pr_id'] = $id;
                $RequestData['pr_bill_no'] = $RequestData['bill_no'];
                // $RequestData['company_id'] = $RequestData['company_id'];
                // $RequestData['branch_id'] = $RequestData['branch_id'];

                // $return_date = new DateTime($RequestData['return_date']);
                // $RequestData['return_date'] = $return_date->format('Y-m-d');

                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {
                        $RequestData['product_id'] = $product_id_sin;
                        $RequestData['product_quantity'] = $product_quantity_arr[$key];
                        // $RequestData['unit_cost_price'] = $unit_cost_price_arr[$key];
                        // $RequestData['total_cost_price'] = $total_cost_price_arr[$key];
                        // $RequestData['product_barcode'] = $product_bar_arr[$key];
                        // $RequestData['product_system_barcode'] = $sys_barcode_arr[$key];

                        PurchaseReturnDetails::create($RequestData);
                    }
                }

                DB::commit();

                $notification = array(
                    'message' => 'Successfully Updated Data',
                    'alert-type' => 'success',
                );
                return redirect('inv/purchase_return')->with($notification);
            } catch (\Exception $e) {

                DB::rollBack();

                $notification = array(
                    'message' => 'Upsuccessful to Updated Data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $Supplier = Supplier::where('is_delete', 0)
                        ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                        ->orderBy('id', 'DESC')
                        ->get();
            return view('INV.PurchasesReturn.edit', compact('PReturnData', 'PReturnDataD', 'Supplier'));
        }
    }

    public function view($id = null)
    {

        $PReturnData = PurchaseReturnMaster::where('id', $id)->first();
        $PReturnDataD = PurchaseReturnDetails::where('pr_bill_no', $PReturnData->bill_no)->get();
        // $inv_products = Product::where('id', $id)->get();

        return view('INV.PurchasesReturn.view', compact('PReturnData', 'PReturnDataD'));
    }

    public function delete($id = null)
    {

        $PReturnData = PurchaseReturnMaster::where('id', $id)->first();

        $PReturnData->is_delete = 1;

        $delete = $PReturnData->save();

        if ($delete) {
            
            $notification = array(
                'message' => 'Successfully Deleted',
                'alert-type' => 'success',
            );
            return redirect()->back()->with($notification);
        } else {
            $notification = array(
                'message' => 'Unsuccessful to Delete',
                'alert-type' => 'error',
            );
            return redirect()->back()->with($notification);
        }
    }

    public function isActive($id = null)
    {
        $PReturnData = PurchaseReturnMaster::where('id', $id)->first();

        if ($PReturnData->is_active == 1) {

            $PReturnData->is_active = 0;
            # code...
        } else {

            $PReturnData->is_active = 1;
        }

        $Status = $PReturnData->save();

        if ($Status) {
            $notification = array(
                'message' => 'Successfully Updated',
                'alert-type' => 'success',
            );
            return redirect()->back()->with($notification);
        } else {
            $notification = array(
                'message' => 'Unsuccessful to Update',
                'alert-type' => 'error',
            );
            return redirect()->back()->with($notification);
        }
    }

}
