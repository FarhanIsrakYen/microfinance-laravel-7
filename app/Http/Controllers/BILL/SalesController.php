<?php

namespace App\Http\Controllers\BILL;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\GNL\Division;
use App\Model\BILL\Collection;
use App\Model\BILL\Customer;
use App\Model\BILL\SalesDetails;
use App\Model\BILL\SalesMaster;
use App\Model\BILL\Product;

use App\Services\CommonService as Common;
use App\Services\RoleService as Role;
use App\Services\HrService as HRS;
use App\Services\PosService as POSS;

use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;


class SalesController extends Controller
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
                0 => 'bill_sales_m.id',
                1 => 'bill_sales_m.sales_type',
                2 => 'bill_sales_m.sales_date',
                3 => 'bill_sales_m.sales_bill_no',
                4 => 'bill_sales_m.total_quantity',
                5 => 'bill_sales_m.total_amount',
                6 => 'bill_sales_m.paid_amount',
                7 => 'bill_sales_m.installment_amount',
                8 => 'action',
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
            $salesType = (empty($request->input('salesType'))) ? null : $request->input('salesType');
            // $PGroupID = (empty($request->input('PGroupID'))) ? null : $request->input('PGroupID');
            // $CategoryId = (empty($request->input('CategoryId'))) ? null : $request->input('CategoryId');
            // $SubCatID = (empty($request->input('SubCatID'))) ? null : $request->input('SubCatID');
            // $BrandID = (empty($request->input('BrandID'))) ? null : $request->input('BrandID');

            // Query
            $salesData = SalesMaster::where(['bill_sales_m.is_delete' => 0, 'gnl_branchs.is_approve' => 1, 'bill_sales_m.is_opening' => 0])
                ->select('bill_sales_m.*',
                    'gnl_branchs.branch_name as branch_name')
                ->whereIn('bill_sales_m.branch_id', HRS::getUserAccesableBranchIds())
                ->leftJoin('gnl_branchs', 'bill_sales_m.branch_id', '=', 'gnl_branchs.id')
                ->where(function ($salesData) use ($search, $sDate, $eDate, $branchID, $salesType) {

                    if (!empty($search)) {
                        $salesData->where('bill_sales_m.sales_bill_no', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_branchs.branch_name', 'LIKE', "%{$search}%");
                    }

                    if (!empty($branchID)) {
                        $salesData->where('bill_sales_m.branch_id', '=', $branchID);
                    }

                    if (!empty($sDate) && !empty($eDate)) {

                        $sDate = new DateTime($sDate);
                        $sDate = $sDate->format('Y-m-d');

                        $eDate = new DateTime($eDate);
                        $eDate = $eDate->format('Y-m-d');

                        $salesData->whereBetween('bill_sales_m.sales_date', [$sDate, $eDate]);
                    }

                    if (!empty($salesType)) {
                        $salesData->where('bill_sales_m.sales_type', '=', $salesType);
                    } else {
                        $salesData->where('bill_sales_m.sales_type', '=', 1);
                    }
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('bill_sales_m.sales_date', 'DESC')
                ->orderBy('bill_sales_m.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            $totalData = SalesMaster::where(['bill_sales_m.is_delete' => 0, 'bill_sales_m.sales_type' => 1, 'gnl_branchs.is_approve' => 1])
                ->whereIn('bill_sales_m.branch_id', HRS::getUserAccesableBranchIds())
                ->leftJoin('gnl_branchs', 'bill_sales_m.branch_id', '=', 'gnl_branchs.id')
                ->count();

            $totalFiltered = count($salesData);

            if (!empty($search) || !empty($sDate) || !empty($eDate) || !empty($branchID) || !empty($salesType)) {
                $totalFiltered = count($salesData);
            }

            $dataSet = array();
            $i = 0;

            foreach ($salesData as $row) {
                $TempSet = array();
                $IgnoreArray = array();

                if (date('d-m-Y', strtotime($row->sales_date)) != Common::systemCurrentDate($row->branch_id, 'bill')) {
                    $IgnoreArray = ['delete', 'edit'];

                    // if ($row->sales_bill_no) {
                    //     $IgnoreArray = ['delete', 'edit'];
                    // }
                }

                $TempSet = [
                    'id' => ++$i,
                    'sales_type' => ($row->sales_type == 1) ? 'Cash' : 'Installment',
                    'sales_date' => date('d-m-Y', strtotime($row->sales_date)),
                    'sales_bill_no' => $row->sales_bill_no,
                    'total_quantity' => $row->total_quantity,
                    'total_amount' => $row->total_amount,
                    'paid_amount' => $row->paid_amount,
                    'due_amount' => $row->due_amount,
                    'installment_amount' => $row->installment_amount,

                    'action' => Role::roleWiseArray($this->GlobalRole, $row->sales_bill_no),
                ];

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
            return view('BILL.Sales.index');
        }
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'sales_bill_no' => 'required',
                'sales_date' => 'required',
                'sales_type' => 'required',
                'payment_system_id' => 'required',
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();

            // dd($RequestData);

            /* Product Data */
            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());

            $unit_sale_price_arr = (isset($RequestData['unit_sale_price_arr']) ? $RequestData['unit_sale_price_arr'] : array());
            $product_sales_price_arr = (isset($RequestData['product_sales_price_arr']) ? $RequestData['product_sales_price_arr'] : array());

            $product_name_arr = (isset($RequestData['product_name_arr']) ? $RequestData['product_name_arr'] : array());

            $product_serial_arr = (isset($RequestData['product_serial_arr']) ? $RequestData['product_serial_arr'] : array());

            $RequestData['paid_amount'] = $RequestData['total_payable_amount'];
            $RequestData['due_amount'] = 0;

            /* Format date*/
            $sales_date = new DateTime($RequestData['sales_date']);
            $RequestData['sales_date'] = $sales_date->format('Y-m-d');

            $fiscal_year = DB::table('gnl_fiscal_year')
                ->select('id')
                ->where('company_id', $RequestData['company_id'])
                ->where('fy_start_date', '<=', $RequestData['sales_date'])
                ->where('fy_end_date', '>=', $RequestData['sales_date'])
                ->orderBy('id', 'DESC')
                ->first();

            if ($fiscal_year) {
                $RequestData['fiscal_year_id'] = $fiscal_year->id;
            }

            /* Master Table's CashPrice, Principle, installment_profit */
            /* Here total_amount = sum of all product amount in this sales */
            $RequestData['cash_price'] = $RequestData['total_amount'];
            $RequestData['principal_amount'] = 0;
            $RequestData['installment_profit'] = 0;
            $RequestData['is_complete'] = 1;

            /*DB begain transaction*/
            DB::beginTransaction();

            try {

                if (SalesMaster::where('sales_bill_no', '=', $RequestData['sales_bill_no'])->exists()) {
                    $RequestData['sales_bill_no'] = POSS::generateBillSales($RequestData['branch_id']);
                }

                $isInsertM = SalesMaster::create($RequestData);

                if ($isInsertM) {

                    /* Child Table + Collection table Insertion */
                    // $RequestData['sales_id'] = $isInsertM->id;
                    $RequestData['service_charge'] = 0;
                    $RequestData['collection_amount'] = ($RequestData['paid_amount'] + $RequestData['discount_amount'] - $RequestData['vat_amount'] - $RequestData['service_charge']);
                    $RequestData['collection_date'] = $RequestData['sales_date'];

                    /* Collection Table's CashPrice, Principle, installment_profit */
                    $RequestData['cash_price'] = $RequestData['collection_amount'];
                    $RequestData['principal_amount'] = 0;
                    $RequestData['installment_profit'] = 0;
                    $RequestData['collection_no'] = POSS::generateCollectionNo(Common::getBranchId());

                    // Collection Insert
                    $isInsertC = Collection::create($RequestData);

                    $IsInsertFlag = true;

                    foreach ($product_id_arr as $key => $product_id_sin) {
                        if (!empty($product_id_sin)) {

                            $RequestData['product_id'] = $product_id_sin;

                            $RequestData['product_name'] = $product_name_arr[$key];
                            $RequestData['product_quantity'] = $product_quantity_arr[$key];

                            // $RequestData['product_cost_price'] = $product_cost_price_arr[$key];

                            $RequestData['product_unit_price'] = $unit_sale_price_arr[$key];
                            $RequestData['product_sales_price'] = $product_sales_price_arr[$key];

                            // $RequestData['cash_price'] = $unit_sale_price_arr[$key];
                            // $RequestData['principal_amount'] = $unit_sale_price_arr[$key];
                            $RequestData['total_amount'] = $product_sales_price_arr[$key];
                            $RequestData['product_serial_no'] = $product_serial_arr[$key];


                            //////////////
                            // $RequestData['total_cost_price'] = ($RequestData['product_quantity'] * $RequestData['product_cost_price']);

                            /* Child Table's CashPrice, Principle, installment_profit */
                            /*  */
                            $RequestData['cash_price'] = $RequestData['total_amount'];
                            $RequestData['principal_amount'] = 0;
                            $RequestData['installment_profit'] = 0;

                            // Sales Details insert
                            $isInsertD = SalesDetails::create($RequestData);

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
                    'message' => 'Successfully Insert in Sales',
                    'alert-type' => 'success',
                );

                // return Redirect::to('bill/sales_cash')->with($notification);
                return Redirect::to('bill/sales_cash/invoice/'.$RequestData['sales_bill_no'])->with($notification);
            } catch (\Exception $e) {
                // dd($e);
                DB::rollBack();
                // role back undo all DB operation
                // return $e file line and error masssage in console log ;
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Sales',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
                ->orderBy('branch_code', 'ASC')
                ->get();
            $DivData = Division::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('BILL.Sales.add', compact('DivData', 'BranchData'));
        }
    }

    public function edit(Request $request, $id = null)
    {

        $SalesData = SalesMaster::where('sales_bill_no', $id)->first();
        $SalesDataD = SalesDetails::where('sales_bill_no', $id)->get();
        // $bill_products = Product::where('id', $id)->get();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'sales_bill_no' => 'required',
            ]);

            /* ---------------------------------- Master Table update start ------------------------- */
            $RequestData = $request->all();

            // Date Format
            $sales_date = new DateTime($RequestData['sales_date']);
            $RequestData['sales_date'] = $sales_date->format('Y-m-d');

            // Product data
            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            $unit_sale_price_arr = (isset($RequestData['unit_sale_price_arr']) ? $RequestData['unit_sale_price_arr'] : array());
            $product_sales_price_arr = (isset($RequestData['product_sales_price_arr']) ? $RequestData['product_sales_price_arr'] : array());
            $product_name_arr = (isset($RequestData['product_name_arr']) ? $RequestData['product_name_arr'] : array());
            $product_serial_arr = (isset($RequestData['product_serial_arr']) ? $RequestData['product_serial_arr'] : array());

            $RequestData['paid_amount'] = $RequestData['total_payable_amount'];
            $RequestData['due_amount'] = 0;

            /* Master Table's CashPrice, Principle, installment_profit */
            /* Here total_amount = sum of all product amount in this sales */

            $RequestData['cash_price'] = $RequestData['total_amount'];
            $RequestData['principal_amount'] = 0;
            $RequestData['installment_profit'] = 0;
            $RequestData['is_complete'] = 1;

            DB::beginTransaction();

            try {

                $isUpdateM = $SalesData->update($RequestData);

                if ($isUpdateM) {

                    /* Delete sales details data for this bill no */
                    SalesDetails::where('sales_bill_no', $id)->get()->each->delete();

                    /* Delete Collection for this bill no */
                    Collection::where([['sales_bill_no', $id], ['collection_date', $RequestData['sales_date']]])->get()->each->delete();

                    /* Child Table + Collection Table Insertion  Start */
                    // $RequestData['sales_id'] = $id;
                    $RequestData['service_charge'] = 0;
                    $RequestData['collection_amount'] = ($RequestData['paid_amount'] + $RequestData['discount_amount'] - $RequestData['vat_amount'] - $RequestData['service_charge']);
                    $RequestData['collection_date'] = $RequestData['sales_date'];

                    /* Collection Table's CashPrice, Principle, installment_profit */
                    $RequestData['cash_price'] = $RequestData['collection_amount'];
                    $RequestData['principal_amount'] = 0;
                    $RequestData['installment_profit'] = 0;

                    $fiscal_year = DB::table('gnl_fiscal_year')
                        ->select('id')
                        ->where('company_id', $RequestData['company_id'])
                        ->where('fy_start_date', '<=', $RequestData['sales_date'])
                        ->where('fy_end_date', '>=', $RequestData['sales_date'])
                        ->orderBy('id', 'DESC')
                        ->first();

                    if ($fiscal_year) {
                        $RequestData['fiscal_year_id'] = $fiscal_year->id;
                    }

                    $RequestData['collection_no'] = POSS::generateCollectionNo(Common::getBranchId());

                    // Collection Insert
                    $isInsertC = Collection::create($RequestData);

                    $IsInsertFlag = true;

                    foreach ($product_id_arr as $key => $product_id_sin) {
                        if (!empty($product_id_sin)) {

                            $RequestData['product_id'] = $product_id_sin;
                            $RequestData['product_name'] = $product_name_arr[$key];
                            $RequestData['product_quantity'] = $product_quantity_arr[$key];
                            $RequestData['product_sales_price'] = $product_sales_price_arr[$key];
                            $RequestData['product_unit_price'] = $unit_sale_price_arr[$key];
                            // $RequestData['cash_price'] = $unit_sale_price_arr[$key];
                            // $RequestData['principal_amount'] = $unit_sale_price_arr[$key];
                            $RequestData['total_amount'] = $product_sales_price_arr[$key];
                            $RequestData['product_serial_no'] = $product_serial_arr[$key];

                            //////////////
                            // $RequestData['total_cost_price'] = ($RequestData['product_quantity'] * $RequestData['product_cost_price']);

                            /* Child Table's CashPrice, Principle, installment_profit */
                            $RequestData['cash_price'] = $RequestData['total_amount'];
                            $RequestData['principal_amount'] = 0;
                            $RequestData['installment_profit'] = 0;

                            $isInsertD = SalesDetails::create($RequestData);

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

                return Redirect::to('bill/sales_cash')->with($notification);

            } catch (\Exception $e) {

                DB::rollBack();
                // return $e file line and error masssage in console log ;
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Sales',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
            }

            /* ----------------------- Master table Update end ------------------------ */
        } else {

            // $CustomerData = Customer::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            // $EmployeeData = Employee::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
                ->orderBy('branch_code', 'ASC')
                ->get();
            $DivData = Division::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('BILL.Sales.edit',
                compact('SalesData', 'SalesDataD', 'BranchData', 'DivData'));
        }
    }

    public function instIndex(Request $request)
    {
        if ($request->ajax()) {
            // Ordering Variable
            $columns = array(
                0 => 'bill_sales_m.id',
                1 => 'bill_sales_m.sales_type',
                2 => 'bill_sales_m.sales_date',
                3 => 'bill_sales_m.sales_bill_no',
                4 => 'bill_sales_m.total_quantity',
                5 => 'bill_sales_m.total_amount',
                6 => 'bill_sales_m.paid_amount',
                7 => 'bill_sales_m.installment_amount',
                8 => 'action',
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
            $salesType = (empty($request->input('salesType'))) ? null : $request->input('salesType');
            // $PGroupID = (empty($request->input('PGroupID'))) ? null : $request->input('PGroupID');
            // $CategoryId = (empty($request->input('CategoryId'))) ? null : $request->input('CategoryId');
            // $SubCatID = (empty($request->input('SubCatID'))) ? null : $request->input('SubCatID');
            // $BrandID = (empty($request->input('BrandID'))) ? null : $request->input('BrandID');

            // Query
            $salesData = SalesMaster::where(['bill_sales_m.is_delete' => 0, 'gnl_branchs.is_approve' => 1, 'bill_sales_m.is_opening' => 0])
                ->select('bill_sales_m.*', 'gnl_branchs.branch_name as branch_name')
                ->whereIn('bill_sales_m.branch_id', HRS::getUserAccesableBranchIds())
                ->leftJoin('gnl_branchs', 'bill_sales_m.branch_id', '=', 'gnl_branchs.id')
                ->where(function ($salesData) use ($search, $sDate, $eDate, $branchID, $salesType) {

                    if (!empty($search)) {
                        $salesData->where('bill_sales_m.sales_bill_no', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_branchs.branch_name', 'LIKE', "%{$search}%");
                    }

                    if (!empty($branchID)) {
                        $salesData->where('bill_sales_m.branch_id', '=', $branchID);
                    }

                    if (!empty($sDate) && !empty($eDate)) {

                        $sDate = new DateTime($sDate);
                        $sDate = $sDate->format('Y-m-d');

                        $eDate = new DateTime($eDate);
                        $eDate = $eDate->format('Y-m-d');

                        $salesData->whereBetween('bill_sales_m.sales_date', [$sDate, $eDate]);
                    }

                    if (!empty($salesType)) {
                        $salesData->where('bill_sales_m.sales_type', '=', $salesType);
                    } else {
                        $salesData->where('bill_sales_m.sales_type', '=', 2);
                    }
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('bill_sales_m.sales_date', 'DESC')
                ->orderBy('bill_sales_m.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            $totalData = SalesMaster::where(['bill_sales_m.is_delete' => 0, 'bill_sales_m.sales_type' => 2, 'gnl_branchs.is_approve' => 1])
                ->whereIn('bill_sales_m.branch_id', HRS::getUserAccesableBranchIds())
                ->leftJoin('gnl_branchs', 'bill_sales_m.branch_id', '=', 'gnl_branchs.id')
                ->count();

            $totalFiltered = count($salesData);
            // $sDate, $eDate, $branchID, $salesType
            if (!empty($search) || !empty($sDate) || !empty($eDate) || !empty($branchID) || !empty($salesType)) {
                $totalFiltered = count($salesData);
            }

            $collectionCount = DB::table('bill_collections')
                ->where([['is_active', 1], ['is_delete', 0]])
                ->select(DB::raw('count(*) as collection_count, sales_bill_no'))
                ->groupBy('sales_bill_no')
                ->having('collection_count', '>', 1)
                ->pluck('sales_bill_no')
                ->toArray();

            $dataSet = array();
            $i = 0;

            foreach ($salesData as $row) {
                $TempSet = array();
                $IgnoreArray = array();

                if (date('d-m-Y', strtotime($row->sales_date)) != Common::systemCurrentDate($row->branch_id, 'bill')) {
                    $IgnoreArray = ['delete', 'edit'];

                    // if (in_array($row->sales_bill_no, $collectionCount) == true) {
                    //     $IgnoreArray = ['delete', 'edit'];
                    // }
                }

                $TempSet = [
                    'id' => ++$i,
                    'sales_type' => ($row->sales_type == 1) ? 'Cash' : 'Installment',
                    'sales_date' => date('d-m-Y', strtotime($row->sales_date)),
                    'sales_bill_no' => $row->sales_bill_no,
                    'total_quantity' => $row->total_quantity,
                    'total_amount' => $row->total_amount,
                    'paid_amount' => $row->paid_amount,
                    'due_amount' => $row->due_amount,
                    'installment_amount' => $row->installment_amount,

                    'action' => Role::roleWiseArray($this->GlobalRole, $row->sales_bill_no),
                ];

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
            return view('BILL.Sales.inst_index');
        }
    }

    public function instAdd(Request $request)
    {
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'sales_bill_no' => 'required',
                'sales_date' => 'required',
                'sales_type' => 'required',
                'payment_system_id' => 'required',
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();

            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            // $sys_barcode_arr = (isset($RequestData['sys_barcode_arr']) ? $RequestData['sys_barcode_arr'] : array());
            $product_name_arr = (isset($RequestData['product_name_arr']) ? $RequestData['product_name_arr'] : array());
            $product_sno_arr = (isset($RequestData['product_sno_arr']) ? $RequestData['product_sno_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            // $month_arr = (isset($RequestData['month_arr']) ? $RequestData['month_arr'] : array());
            $unit_sales_price_arr = (isset($RequestData['unit_sales_price_arr']) ? $RequestData['unit_sales_price_arr'] : array());
            $sales_price_arr = (isset($RequestData['sales_price_arr']) ? $RequestData['sales_price_arr'] : array());
            // $fst_Installment_arr = (isset($RequestData['fst_Installment_arr']) ? $RequestData['fst_Installment_arr'] : array());
            // $inst_amount_arr = (isset($RequestData['inst_amount_arr']) ? $RequestData['inst_amount_arr'] : array());
            $total_cost_price_arr = (isset($RequestData['total_cost_price_arr']) ? $RequestData['total_cost_price_arr'] : array());
            // dd($product_sno_arr);
            $sales_date = new DateTime($RequestData['sales_date']);
            $RequestData['sales_date'] = $sales_date->format('Y-m-d');
            $RequestData['payment_month'] = date('M');

            $fiscal_year = DB::table('gnl_fiscal_year')
                ->select('id')
                ->where('company_id', $RequestData['company_id'])
                ->where('fy_start_date', '<=', $RequestData['sales_date'])
                ->where('fy_end_date', '>=', $RequestData['sales_date'])
                ->orderBy('id', 'DESC')
                ->first();

            if ($fiscal_year) {
                $RequestData['fiscal_year_id'] = $fiscal_year->id;
            }

            $scheduleList = POSS::installmentSchedule($RequestData['company_id'], $RequestData['branch_id'], null,
                $RequestData['sales_date'], $RequestData['installment_type'], $RequestData['installment_month']);

            $RequestData['installment_date'] = end($scheduleList);

            /* Master Table's CashPrice, Principle, installment_profit */
            /* Here total_amount = sum of all product amount in this sales */

            $RequestData['cash_price'] = $RequestData['total_amount'];
            $principal_amount_m = ($RequestData['total_amount'] / (100 + $RequestData['installment_rate'])) * 100;
            $installment_profit_m = ($RequestData['total_amount'] - $principal_amount_m);

            $RequestData['principal_amount'] = $principal_amount_m;
            $RequestData['installment_profit'] = $installment_profit_m;

            if ($RequestData['total_payable_amount'] == $RequestData['paid_amount']) {
                $RequestData['is_complete'] = 1;
            }

            //dd($RequestData);

            DB::beginTransaction();

            try {

                if (SalesMaster::where('sales_bill_no', '=', $RequestData['sales_bill_no'])->exists()) {
                    $RequestData['sales_bill_no'] = POSS::generateBillSales($RequestData['branch_id']);
                }

                $isInsertM = SalesMaster::create($RequestData);

                /* Child Table + Collection table Insertion */
                // $RequestData['sales_id'] = $isInsertM->id;

                $RequestData['collection_amount'] = ($RequestData['paid_amount'] - $RequestData['vat_amount'] - $RequestData['service_charge']);
                $RequestData['collection_date'] = $RequestData['sales_date'];

                /* Collection Table's CashPrice, Principle, installment_profit */
                $RequestData['cash_price'] = $RequestData['collection_amount'];

                $principal_amount_c = ($RequestData['collection_amount'] / (100 + $RequestData['installment_rate'])) * 100;
                $installment_profit_c = ($RequestData['collection_amount'] - $principal_amount_c);

                $RequestData['principal_amount'] = $principal_amount_c;
                $RequestData['installment_profit'] = $installment_profit_c;
                $RequestData['collection_no'] = POSS::generateCollectionNo(Common::getBranchId());

                // Collection Insert
                $isInsertC = Collection::create($RequestData);

                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {

                        $RequestData['product_id'] = $product_id_sin;
                        // $RequestData['barcode_no'] = $sys_barcode_arr[$key];
                        $RequestData['product_name'] = $product_name_arr[$key];
                        $RequestData['product_serial_no'] = $product_sno_arr[$key];
                        $RequestData['product_quantity'] = $product_quantity_arr[$key];
                        $RequestData['product_unit_price'] = $unit_sales_price_arr[$key];
                        $RequestData['product_cost_price'] = $sales_price_arr[$key];
                        $RequestData['product_sales_price'] = $sales_price_arr[$key];
                        $RequestData['total_amount'] = $total_cost_price_arr[$key];

                        //////////////
                        $RequestData['total_cost_price'] = ($RequestData['product_quantity'] * $RequestData['product_cost_price']);

                        /* Child Table's CashPrice, Principle, installment_profit */
                        /* Here total_amount = sum of product amount in this product */
                        $RequestData['cash_price'] = $RequestData['total_amount'];

                        $principal_amount_d = ($RequestData['total_amount'] / (100 + $RequestData['installment_rate'])) * 100;
                        $installment_profit_d = ($RequestData['total_amount'] - $principal_amount_d);

                        $RequestData['principal_amount'] = $principal_amount_d;
                        $RequestData['installment_profit'] = $installment_profit_d;

                        SalesDetails::create($RequestData);
                    }
                }

                DB::commit();

                $notification = array(
                    'message' => 'Successfully Inserted data',
                    'alert-type' => 'success',
                );
                // return redirect('bill/sales_installment')->with($notification);
                return Redirect::to('bill/sales_installment/invoice/'.$RequestData['sales_bill_no'])->with($notification);


            } catch (\Exception $e) {

                DB::rollBack();

                // dd($e);

                $notification = array(
                    'message' => 'Unsuccessful to insert data',
                    'alert-type' => 'error',
                    'console_error' => $e
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
                ->orderBy('branch_code', 'ASC')
                ->get();

            $DivData = Division::where('is_delete', 0)->orderBy('id', 'DESC')->get();

            return view('BILL.Sales.inst_add', compact('BranchData', 'DivData'));
        }
    }

    public function instEdit(Request $request, $id = null)
    {
        $SalesDataM = SalesMaster::where('sales_bill_no', $id)->first();
        $SalesDataD = SalesDetails::where('sales_bill_no', $id)->get();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'sales_bill_no' => 'required',
                'sales_date' => 'required',
                'sales_type' => 'required',
                'payment_system_id' => 'required',
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();
            // dd($RequestData );

            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            // $sys_barcode_arr = (isset($RequestData['sys_barcode_arr']) ? $RequestData['sys_barcode_arr'] : array());
            $product_name_arr = (isset($RequestData['product_name_arr']) ? $RequestData['product_name_arr'] : array());
            $product_sno_arr = (isset($RequestData['product_sno_arr']) ? $RequestData['product_sno_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            // $month_arr = (isset($RequestData['month_arr']) ? $RequestData['month_arr'] : array());
            $unit_sales_price_arr = (isset($RequestData['unit_sales_price_arr']) ? $RequestData['unit_sales_price_arr'] : array());
            $sales_price_arr = (isset($RequestData['sales_price_arr']) ? $RequestData['sales_price_arr'] : array());
            // $fst_Installment_arr = (isset($RequestData['fst_Installment_arr']) ? $RequestData['fst_Installment_arr'] : array());
            // $inst_amount_arr = (isset($RequestData['inst_amount_arr']) ? $RequestData['inst_amount_arr'] : array());
            $total_cost_price_arr = (isset($RequestData['total_cost_price_arr']) ? $RequestData['total_cost_price_arr'] : array());

            $sales_date = new DateTime($RequestData['sales_date']);
            $RequestData['sales_date'] = $sales_date->format('Y-m-d');
            $RequestData['payment_month'] = date('M');

            /* Master Table's CashPrice, Principle, installment_profit */
            /* Here total_amount = sum of all product amount in this sales */

            $RequestData['cash_price'] = $RequestData['total_amount'];
            $principal_amount_m = ($RequestData['total_amount'] / (100 + $RequestData['installment_rate'])) * 100;
            $installment_profit_m = ($RequestData['total_amount'] - $principal_amount_m);

            $RequestData['principal_amount'] = $principal_amount_m;
            $RequestData['installment_profit'] = $installment_profit_m;

            if ($RequestData['total_payable_amount'] == $RequestData['paid_amount']) {
                $RequestData['is_complete'] = 1;
            }

            DB::beginTransaction();

            try {

                $isUpdateM = $SalesDataM->update($RequestData);

                /* Delete sales details data for this bill no */
                SalesDetails::where('sales_bill_no', $id)->get()->each->delete();

                /* Delete Collection for this bill no */
                Collection::where([['sales_bill_no', $id], ['collection_date', $RequestData['sales_date']]])->get()->each->delete();

                /* Child Table + Collection table Insertion */
                // $RequestData['sales_id'] = $id;
                $RequestData['installment_date'] = $RequestData['sales_date'];

                $RequestData['collection_amount'] = ($RequestData['paid_amount'] - $RequestData['vat_amount'] - $RequestData['service_charge']);
                $RequestData['collection_date'] = $RequestData['sales_date'];

                /* Collection Table's CashPrice, Principle, installment_profit */
                $RequestData['cash_price'] = $RequestData['collection_amount'];

                $principal_amount_c = ($RequestData['collection_amount'] / (100 + $RequestData['installment_rate'])) * 100;
                $installment_profit_c = ($RequestData['collection_amount'] - $principal_amount_c);

                $RequestData['principal_amount'] = $principal_amount_c;
                $RequestData['installment_profit'] = $installment_profit_c;

                $fiscal_year = DB::table('gnl_fiscal_year')
                    ->select('id')
                    ->where('company_id', $RequestData['company_id'])
                    ->where('fy_start_date', '<=', $RequestData['sales_date'])
                    ->where('fy_end_date', '>=', $RequestData['sales_date'])
                    ->orderBy('id', 'DESC')
                    ->first();

                if ($fiscal_year) {
                    $RequestData['fiscal_year_id'] = $fiscal_year->id;
                }

                $RequestData['collection_no'] = POSS::generateCollectionNo(Common::getBranchId());

                // Collection Insert
                $isInsertC = Collection::create($RequestData);

                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {

                        $RequestData['product_id'] = $product_id_sin;
                        // $RequestData['barcode_no'] = $sys_barcode_arr[$key];
                        $RequestData['product_name'] = $product_name_arr[$key];
                        $RequestData['product_serial_no'] = $product_sno_arr[$key];
                        $RequestData['product_quantity'] = $product_quantity_arr[$key];
                        $RequestData['product_unit_price'] = $unit_sales_price_arr[$key];
                        $RequestData['product_cost_price'] = $sales_price_arr[$key];
                        $RequestData['product_sales_price'] = $sales_price_arr[$key];
                        $RequestData['total_amount'] = $total_cost_price_arr[$key];

                        //////////////
                        $RequestData['total_cost_price'] = ($RequestData['product_quantity'] * $RequestData['product_cost_price']);

                        /* Child Table's CashPrice, Principle, installment_profit */
                        /* Here total_amount = sum of product amount in this product */
                        $RequestData['cash_price'] = $RequestData['total_amount'];

                        $principal_amount_d = ($RequestData['total_amount'] / (100 + $RequestData['installment_rate'])) * 100;
                        $installment_profit_d = ($RequestData['total_amount'] - $principal_amount_d);

                        $RequestData['principal_amount'] = $principal_amount_d;
                        $RequestData['installment_profit'] = $installment_profit_d;

                        $isInsertD = SalesDetails::create($RequestData);
                    }
                }

                DB::commit();

                $notification = array(
                    'message' => 'Successfully Updated data',
                    'alert-type' => 'success',
                );
                return redirect('bill/sales_installment')->with($notification);

            } catch (\Exception $e) {

                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to Update data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            return view('BILL.Sales.inst_edit', compact('SalesDataM', 'SalesDataD'));
        }
    }

    public function view($id = null)
    {
        $SalesData = SalesMaster::where('sales_bill_no', $id)->first();
        $SalesDataD = SalesDetails::where('sales_bill_no', $id)->get();
        $CollectionData = Collection::where('sales_bill_no', $id)->get();

        return view('BILL.Sales.view', compact('SalesData', 'SalesDataD', 'CollectionData'));
    }

    public function delete($id = null)
    {
        DB::beginTransaction();

        try {
            $SalesData = SalesMaster::where('sales_bill_no', $id)->first();

            $SalesData->is_delete = 1;
            $isSuccess = $SalesData->update();

            // SalesDetails::where('sales_bill_no', $SalesData->sales_bill_no)->update(['is_delete' => 1]);
            Collection::where('sales_bill_no', $id)->update(['is_delete' => 1]);

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

    public function popUpCustomerDataInsert(Request $request)
    {
        $request->validate([
            'customer_name' => 'required',
            'customer_email' => 'required',
            'customer_mobile' => 'required',
        ]);

        $RequestData = $request->all();
        // $RequestData['customer_dob'] = new DateTime($RequestData['customer_dob']); // used date time for d/m/y format
        // $RequestData['customer_dob'] = $RequestData['customer_dob']->format('Y-m-d');
        // $RequestData['customer_no'] = Common:: generateCustomerNo($RequestData['branch_id']);
        // dd($request->all());
        $RequestData['customer_no'] = Common:: generateCustomerNo($RequestData['branch_id']);

        $isCreate = Customer::create($RequestData);

        if ($isCreate) {

            return json_encode(array(
                "statusCode" => 200,
            ));
        }
    }

    public function invoice($id = null)
    {
        $SalesData = SalesMaster::where('sales_bill_no', $id)->first();
        $SalesDataD = SalesDetails::where('sales_bill_no', $id)->get();

        $ProductData = Product::where([['is_active', 1], ['is_delete', 0]])
        ->whereIn('id', $SalesDataD->pluck('product_id'))
        ->get();
        return view('BILL.Sales.invoice', compact('SalesData', 'SalesDataD', 'ProductData'));
    }

}
