<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\INV\TransferDetails;
use App\Model\INV\TransferMaster;
use App\Services\HrService as HRS;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;
use App\Services\RoleService as Role;
use App\Services\CommonService as Common;

class TransferController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    //------ Show list of Transfer Master Table-----//
    public function index(Request $request)
    {
        if ($request->ajax()) {

            $columns = array(
                0 => 'inv_transfers_m.id',
                1 => 'inv_transfers_m.bill_no',
                2 => 'inv_transfers_m.transfer_date',
                3 => 'inv_transfers_m.branch_from',
                4 => 'inv_transfers_m.branch_to',
                5 => 'inv_transfers_m.total_quantity',
                6 => 'inv_transfers_m.total_amount',
                7 => 'action',
            );

            // Datatable Pagination Variable
            $totalData = TransferMaster::where('is_delete', '=', 0)
                ->whereIn('inv_transfers_m.branch_from', HRS::getUserAccesableBranchIds())
                ->count();
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable

            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $SDate = (empty($request->input('sDate'))) ? null : $request->input('sDate');
            $EDate = (empty($request->input('eDate'))) ? null : $request->input('eDate');
            $branchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');
            $Type = (empty($request->input('Type'))) ? null : $request->input('Type');
            $PGroupID = (empty($request->input('PGroupID'))) ? null : $request->input('PGroupID');
            $CategoryId = (empty($request->input('CategoryId'))) ? null : $request->input('CategoryId');
            $SubCatID = (empty($request->input('SubCatID'))) ? null : $request->input('SubCatID');
            $BrandID = (empty($request->input('BrandID'))) ? null : $request->input('BrandID');

            //dd($EDate);

            // Query
            $QuerryData = TransferMaster::where('inv_transfers_m.is_delete', '=', 0)
                ->whereIn('inv_transfers_m.branch_from', HRS::getUserAccesableBranchIds())
                ->select('inv_transfers_m.*', 'B1.branch_name as branch_name_from','B1.branch_code as branch_code_from', 'B2.branch_name as branch_name_to', 'B2.branch_code as branch_code_to')
                ->leftJoin('gnl_branchs as B1', 'B1.id', '=', 'inv_transfers_m.branch_from')
                ->leftJoin('gnl_branchs as B2', 'B2.id', '=', 'inv_transfers_m.branch_to')
                ->leftJoin('inv_transfers_d as ptd', 'ptd.transfer_bill_no', '=', 'inv_transfers_m.bill_no')
                ->leftJoin('inv_products as prod', 'prod.id', '=', 'ptd.product_id')
                ->where(function ($QuerryData) use ($search, $SDate, $EDate, $branchID, $Type,$PGroupID,
              $CategoryId,$SubCatID,$BrandID) {

                    if (!empty($search)) {
                        $QuerryData->where('B1.branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('B2.branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('inv_transfers_m.bill_no', 'LIKE', "%{$search}%")
                            ->orWhere('inv_transfers_m.transfer_date', 'LIKE', "%{$search}%");
                    }
                    if (!empty($SDate) && !empty($EDate)) {

                        $SDate = new DateTime($SDate);
                        $SDate = $SDate->format('Y-m-d');

                        $EDate = new DateTime($EDate);
                        $EDate = $EDate->format('Y-m-d');

                        $QuerryData->whereBetween('inv_transfers_m.transfer_date', [$SDate, $EDate]);
                    }
                    if (!empty($Type) && !empty($branchID)) {
                        if ($Type == 1) {
                            $QuerryData->where('inv_transfers_m.branch_from', '=', $branchID);
                        }
                        if ($Type == 2) {
                            $QuerryData->where('inv_transfers_m.branch_to', '=', $branchID);
                        }

                    } else if (!empty($branchID)) {
                        $QuerryData->where('inv_transfers_m.branch_from', '=', $branchID)
                            ->orWhere('inv_transfers_m.branch_to', '=', $branchID);

                    }


                    if (!empty($PGroupID)) {
                        $QuerryData->where('prod.prod_group_id', '=', $PGroupID);
                    }
                    if (!empty($CategoryId)) {
                        $QuerryData->where('prod.prod_cat_id', '=', $CategoryId);
                    }
                    if (!empty($SubCatID)) {
                        $QuerryData->where('prod.prod_sub_cat_id', '=', $SubCatID);
                    }
                    if (!empty($BrandID)) {
                        $QuerryData->where('prod.prod_brand_id', '=', $BrandID);
                    }
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('inv_transfers_m.transfer_date','DESC')
                ->orderBy('inv_transfers_m.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            $billNoList = $QuerryData->pluck('bill_no');
            $detailsData = DB::table('inv_transfers_d as dt')
                ->whereIn('dt.transfer_bill_no', $billNoList->toarray())
                ->join('inv_products as pro', function ($detailsData) {
                    $detailsData->on('pro.id', '=', 'dt.product_id')
                                ->where('pro.is_delete', 0);
                })
                ->select('dt.transfer_bill_no', 'pro.product_name')
                ->get();

            // $search, $SDate, $EDate,$branchID, $Type
            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($branchID) || !empty($Type)
            || !empty($PGroupID) || !empty($CategoryId) || !empty($SubCatID) || !empty($BrandID )) {
                $totalFiltered = count($QuerryData);
            }

            $DataSet = array();
            $i = 0;
            foreach ($QuerryData as $Row) {
                $date = new DateTime($Row->transfer_date);
                $date = $date->format('d-m-Y');
                $TempSet = array();

                $IgnoreArray = array();

                if($date != Common::systemCurrentDate($Row->branch_from,'inv')){
                    $IgnoreArray = ['delete', 'edit'];
                }

                $product_names = $detailsData->where('transfer_bill_no', $Row->bill_no)
                    ->pluck('product_name')
                    ->toArray();
                if(count($product_names) > 0){
                    $TempSet = [
                        'id' => ++$i,
                        'bill_no' => $Row->bill_no,
                        'transfer_date' => $date,
                        'branch_from' =>  (!empty($Row->branch_name_from)) ? $Row->branch_name_from."(".$Row->branch_code_from.")" : "",
                        'branch_to' =>(!empty($Row->branch_name_to)) ? $Row->branch_name_to."(".$Row->branch_code_to.")" : "",
                        'total_quantity' => $Row->total_quantity,
                        'product_name'=> implode(', ', $product_names),

                        'action' => Role::roleWiseArray($this->GlobalRole, $Row->bill_no, $IgnoreArray),
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
            return view('INV.Transfers.index');
        }
    }

    //Add and store data in Transfer Table (both Master and Details)
    public function add(Request $request)
    {

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'company_id' => 'required',
                'bill_no' => 'required',
                'transfer_date' => 'required',
                'branch_from' => 'required',
                'branch_to' => 'required',
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();

            /* Format date*/
            $transfer_date = new DateTime($RequestData['transfer_date']);
            $RequestData['transfer_date'] = $transfer_date->format('Y-m-d');
            /* */

            /* Product Data */
            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $sys_barcode_arr = (isset($RequestData['sys_barcode_arr']) ? $RequestData['sys_barcode_arr'] : array());
            $product_name_arr = (isset($RequestData['product_name_arr']) ? $RequestData['product_name_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            // $unit_cost_price_arr = (isset($RequestData['unit_cost_price_arr']) ? $RequestData['unit_cost_price_arr'] : array());
            // $total_cost_price_arr = (isset($RequestData['total_cost_price_arr']) ? $RequestData['total_cost_price_arr'] : array());

            /*DB begain transaction*/
            DB::beginTransaction();
            try {
                // start insert 1st table
                $isInsert = TransferMaster::create($RequestData);
                if ($isInsert) {

                    /* Child Table Insertion */
                    // $RequestData['transfer_id'] = $isInsert->id;
                    $RequestData['transfer_bill_no'] = $RequestData['bill_no'];
                    // $RequestData['company_id'] = $RequestData['company_id'];
                    // $RequestData['branch_from'] = $RequestData['branch_from'];
                    // $RequestData['branch_to'] = $RequestData['branch_to'];

                    // $transfer_date = new DateTime($RequestData['transfer_date']);
                    // $RequestData['transfer_date'] = $transfer_date->format('Y-m-d');

                    $IsInsertFlag = true;
                    // start insert 2nd table
                    foreach ($product_id_arr as $key => $product_id_sin) {

                        if (!empty($product_id_sin)) {
                            // dd($RequestData);
                            $RequestData['product_id'] = $product_id_sin;
                            // $RequestData['barcode_no'] = $sys_barcode_arr[$key];
                            $RequestData['product_quantity'] = $product_quantity_arr[$key];
                            // $RequestData['unit_cost_price'] = $unit_cost_price_arr[$key];
                            // $RequestData['total_cost_price'] = $total_cost_price_arr[$key];
                            // $RequestData['product_name'] = $product_name_arr[$key];

                            $isInsertM = TransferDetails::create($RequestData);

                            if ($isInsertM) {
                                continue;
                            } else {
                                $IsInsertFlag = false;
                            }
                        }

                    }
                    // end insert 2nd table

                }
                // commit DB and return with success masssage
                DB::commit();

                $notification = array(
                    'message' => 'Successfully Inserted Transfer List',
                    'alert-type' => 'success',
                );

                return Redirect::to('inv/transfer')->with($notification);
            } catch (\Exception $e) {
                DB::rollBack();
                // role back undo all DB operation
                // return $e file line and error masssage in console log ;

                dd($e);
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Transfer List',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
            }

        } else {
            $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
                ->orderBy('branch_code', 'ASC')
                ->get();
            return view('INV.Transfers.add', compact('BranchData'));
        }

    }

    public function edit(Request $request, $id = null)
    {

        $TransferData = TransferMaster::where('bill_no', $id)->first();
        $TransferDataD = TransferDetails::where('transfer_bill_no', $id)->get();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'bill_no' => 'required',
                'branch_from' => 'required',
                'branch_to' => 'required',
            ]);

            /* ---------------------------------- Master Table update start ------------------------- */
            $RequestData = $request->all();

            $transfer_date = new DateTime($RequestData['transfer_date']);
            $RequestData['transfer_date'] = $transfer_date->format('Y-m-d');

            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            // $unit_cost_price_arr = (isset($RequestData['unit_cost_price_arr']) ? $RequestData['unit_cost_price_arr'] : array());
            // $total_cost_price_arr = (isset($RequestData['total_cost_price_arr']) ? $RequestData['total_cost_price_arr'] : array());

            DB::beginTransaction();
            try {
                // Database update start  1st table
                $isUpdate = $TransferData->update($RequestData);
                if ($isUpdate) {

                    /* Delete Transfer details data for this bill no */
                    TransferDetails::where('transfer_bill_no', $id)->get()->each->delete();

                    /* Child Table Insertion Start */
                    // $RequestData['transfer_id'] = $id;
                    $RequestData['transfer_bill_no'] = $RequestData['bill_no'];
                    // $RequestData['company_id'] = $RequestData['company_id'];
                    // $RequestData['branch_from'] = $RequestData['branch_from'];
                    // $RequestData['branch_to'] = $RequestData['branch_to'];

                    $IsInsertFlag = true;
                    // Database insert star 2ND table
                    foreach ($product_id_arr as $key => $product_id_sin) {

                        if (!empty($product_id_sin)) {

                            // dd($RequestData);

                            $RequestData['product_id'] = $product_id_sin;
                            // $RequestData['barcode_no'] = $sys_barcode_arr[$key];
                            // $RequestData['product_name'] = $product_name_arr[$key];
                            $RequestData['product_quantity'] = $product_quantity_arr[$key];
                            // $RequestData['unit_cost_price'] = $unit_cost_price_arr[$key];
                            // $RequestData['total_cost_price'] = $total_cost_price_arr[$key];

                            $isInsertM = TransferDetails::create($RequestData);

                            if ($isInsertM) {
                                continue;
                            } else {
                                $IsInsertFlag = false;
                            }
                        }
                    }
                    // Database insert star 2ND table
                    /* ------------------- Child Data insertion End ---------------- */
                }

                DB::commit();
                //commit and  return with success massage
                $notification = array(
                    'message' => 'Successfully Updated Transfer Data',
                    'alert-type' => 'success',
                );

                return Redirect::to('inv/transfer')->with($notification);
            } catch (\Exception $e) {
                DB::rollBack();
                // return $e file line and error masssage in console log ;
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Transfer List',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
            }

            /* ----------------------- Master table Update end ------------------------ */
        } else {
            $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
                ->orderBy('branch_code', 'ASC')
                ->get();
            return view('INV.Transfers.edit', compact('TransferData', 'TransferDataD', 'BranchData'));
        }
    }

    public function view($id = null)
    {
        $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
            ->orderBy('branch_code', 'ASC')
            ->get();


        $TransferData = TransferMaster::where('bill_no', $id)->first();
        $TransferDataD = TransferDetails::where('transfer_bill_no', $id)->get();
        // $inv_products = Product::where('id', $id)->get();

        return view('INV.Transfers.view', compact('TransferData', 'TransferDataD', 'BranchData'));
    }

    public function delete($id = null)
    {
        $TransferData = TransferMaster::where('bill_no', $id)->first();

        $TransferData->is_delete = 1;
        DB::beginTransaction();
        try {
            // Your Code here
            $delete = $TransferData->save();
            // if ($delete) {
            //     // TransferDetails::where('transfer_id', $id)->update(['is_delete' => 1]);
            // }
            DB::commit();
            // return
            $notification = array(
                'message' => 'Successfully Deleted',
                'alert-type' => 'success',
            );
            return redirect()->back()->with($notification);
        } catch (Exception $e) {
            DB::rollBack();
            $notification = array(
                'message' => 'Unsuccessful to Delete',
                'alert-type' => 'error',
                'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
            );
            return redirect()->back()->with($notification);
        }

    }

}
