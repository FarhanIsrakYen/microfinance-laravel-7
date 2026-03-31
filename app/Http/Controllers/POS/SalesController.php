<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\GNL\Division;
use App\Model\POS\Collection;
use App\Model\POS\Customer;
use App\Model\POS\Product;
use App\Model\POS\SaleReturnm;
use App\Model\POS\SalesDetails;
use App\Model\POS\SalesMaster;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\PosService as POSS;
use App\Services\RoleService as Role;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;

class SalesController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);

        $this->middleware('permission', ['except' => ['invoice']]);
        parent::__construct();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            // ## Ordering Variable
            $columns = array(
                0 => 'pos_sales_m.id',
                1 => 'pos_sales_m.sales_date',
                2 => 'pos_sales_m.sales_bill_no',
                5 => 'pos_sales_m.total_quantity',
                6 => 'pos_sales_m.total_amount',
            );

            // ## Datatable Pagination Variable
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // ## Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $sDate = (empty($request->input('sDate'))) ? null : $request->input('sDate');
            $eDate = (empty($request->input('eDate'))) ? null : $request->input('eDate');
            $branchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');
            $salesType = (empty($request->input('salesType'))) ? null : $request->input('salesType');
            $customerID = (empty($request->input('customerID'))) ? null : $request->input('customerID');
            $ProductID = (empty($request->input('ProductID'))) ? null : $request->input('ProductID');
            // $SubCatID = (empty($request->input('SubCatID'))) ? null : $request->input('SubCatID');
            // $BrandID = (empty($request->input('BrandID'))) ? null : $request->input('BrandID');

            // ## Query
            $salesData = SalesMaster::where([['pos_sales_m.is_delete', 0], ['gnl_branchs.is_approve', 1], ['pos_sales_m.sales_type', 1], ['pos_sales_m.is_opening', 0]])
                ->select('pos_sales_m.*',
                    'gnl_branchs.branch_name as branch_name', 'gnl_branchs.branch_code as branch_code')
                ->whereIn('pos_sales_m.branch_id', HRS::getUserAccesableBranchIds())
                ->leftJoin('gnl_branchs', 'pos_sales_m.branch_id', '=', 'gnl_branchs.id')
                ->leftjoin('pos_sales_d as psd', function ($salesData) {
                    $salesData->on('psd.sales_bill_no', '=', 'pos_sales_m.sales_bill_no');
                })
                ->where(function ($salesData) use ($search, $sDate, $eDate, $branchID, $customerID, $ProductID, $salesType) {

                    if (!empty($search)) {
                        $salesData->where('pos_sales_m.sales_bill_no', 'LIKE', "%{$search}%")
                            ->orWhere('pos_sales_m.total_amount', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_branchs.branch_name', 'LIKE', "%{$search}%");
                    }

                    if (!empty($branchID)) {
                        $salesData->where('pos_sales_m.branch_id', '=', $branchID);
                    }

                    if (!empty($customerID)) {
                        $salesData->where('pos_sales_m.customer_id', '=', $customerID);
                    }

                    if (!empty($ProductID)) {
                        $salesData->where('psd.product_id', '=', $ProductID);
                    }
                    if (!empty($sDate) && !empty($eDate)) {

                        $sDate = new DateTime($sDate);
                        $sDate = $sDate->format('Y-m-d');

                        $eDate = new DateTime($eDate);
                        $eDate = $eDate->format('Y-m-d');

                        $salesData->whereBetween('pos_sales_m.sales_date', [$sDate, $eDate]);
                    }
                })
                ->orderBy($order, $dir)
                ->orderBy('pos_sales_m.sales_date', 'DESC');

            $tempQueryData = clone $salesData;
            $salesData = $salesData->offset($start)->limit($limit)->get();

            $totalData = SalesMaster::where([['is_delete', 0], ['sales_type', 1], ['is_opening', 0]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;

            if (!empty($search) || !empty($sDate) || !empty($eDate) || !empty($branchID) || !empty($customerID) || !empty($ProductID) || !empty($salesType)) {
                $totalFiltered = $tempQueryData->count();
            }

            $billNoList = $salesData->pluck('sales_bill_no');
            $detailsData = DB::table('pos_sales_d as dt')
                ->whereIn('dt.sales_bill_no', $billNoList->toarray())
                ->join('pos_products as pro', function ($detailsData) {
                    $detailsData->on('pro.id', '=', 'dt.product_id')
                        ->where('pro.is_delete', 0);
                })
                ->select('dt.sales_bill_no', 'pro.product_name')
                ->get();

            // ## get sales_bill_no for chack sales
            $salesRData = SaleReturnm::where([['is_active', 1], ['is_delete', 0]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->pluck('sales_bill_no')
                ->toArray();

            $dataSet = array();
            $i = $start;

            foreach ($salesData as $row) {
                $TempSet = array();
                $IgnoreArray = array();

                if (date('d-m-Y', strtotime($row->sales_date)) != Common::systemCurrentDate($row->branch_id, 'pos')) {
                    $IgnoreArray = ['delete', 'edit'];

                    if (in_array($row->sales_bill_no, $salesRData) == true) {
                        $IgnoreArray = ['delete', 'edit'];
                    }
                }

                $product_names = $detailsData->where('sales_bill_no', $row->sales_bill_no)
                    ->pluck('product_name')
                    ->toArray();

                // if(count($product_names) > 0){
                $TempSet = [
                    'id' => ++$i,
                    'sales_date' => date('d-m-Y', strtotime($row->sales_date)),
                    'sales_bill_no' => $row->sales_bill_no,
                    'branch_name' => (!empty($row->branch_name)) ? $row->branch_name . " (" . $row->branch_code . ")" : "",
                    'product_name' => implode(', ', $product_names),
                    'total_quantity' => $row->total_quantity,
                    'total_amount' => $row->total_amount,
                    'customer_name' => (!empty($row->customer['customer_name'])) ? $row->customer['customer_name'] . " (" . $row->customer['customer_no'] . ")" : "",
                    // 'paid_amount' => $row->paid_amount,
                    // 'due_amount' => $row->due_amount,
                    'action' => Role::roleWiseArray($this->GlobalRole, $row->sales_bill_no, $IgnoreArray),
                ];

                $dataSet[] = $TempSet;
                // }
                // else{
                //     --$totalData;
                //     --$totalFiltered;
                // }
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $dataSet,
            );

            echo json_encode($json_data);

        } else {
            return view('POS.Sales.index');
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

            /* Product Data */
            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            $product_cost_price_arr = (isset($RequestData['product_cost_price_arr']) ? $RequestData['product_cost_price_arr'] : array());
            $unit_sale_price_arr = (isset($RequestData['unit_sale_price_arr']) ? $RequestData['unit_sale_price_arr'] : array());
            $product_sales_price_arr = (isset($RequestData['product_sales_price_arr']) ? $RequestData['product_sales_price_arr'] : array());
            $sys_barcode_arr = (isset($RequestData['sys_barcode_arr']) ? $RequestData['sys_barcode_arr'] : array());
            $product_name_arr = (isset($RequestData['product_name_arr']) ? $RequestData['product_name_arr'] : array());
            $product_barcode_arr = (isset($RequestData['product_barcode_arr']) ? $RequestData['product_barcode_arr'] : array());
            $product_serial_arr = (isset($RequestData['product_serial_arr']) ? $RequestData['product_serial_arr'] : array());

            $RequestData['paid_amount'] = $RequestData['total_payable_amount'];
            $RequestData['due_amount'] = 0;

            /* Format date*/
            $sales_date = new DateTime($RequestData['sales_date']);
            $RequestData['sales_date'] = $sales_date->format('Y-m-d');

            // ##  Fiscal Year
            $fiscal_year = Common::systemFiscalYear($RequestData['sales_date'], $RequestData['company_id']);
            $RequestData['fiscal_year_id'] = $fiscal_year['id'];

            /* Master Table's CashPrice, Principle, installment_profit */
            /* Here total_amount = sum of all product amount in this sales */
            $RequestData['cash_price'] = $RequestData['total_amount'];
            $RequestData['principal_amount'] = $RequestData['total_amount'];
            $RequestData['installment_profit'] = 0;
            $RequestData['is_complete'] = 1;
            $RequestData['complete_date'] = $RequestData['sales_date'];

            if (count(array_filter($product_id_arr)) <= 0) {
                $notification = array(
                    'message' => 'Something went wrong! Please try again.',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

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
                    $RequestData['principal_amount'] = $RequestData['collection_amount'];
                    $RequestData['installment_profit'] = 0;
                    $RequestData['collection_no'] = POSS::generateCollectionNo(Common::getBranchId());
                    $RequestData['sales_type'] = 1;

                    // ## Collection Insert
                    $isInsertC = Collection::create($RequestData);

                    $IsInsertFlag = true;

                    $total_cost_amount = 0;

                    foreach ($product_id_arr as $key => $product_id_sin) {
                        if (!empty($product_id_sin)) {

                            $RequestData['product_id'] = $product_id_sin;
                            $RequestData['product_name'] = $product_name_arr[$key];
                            $RequestData['product_barcode'] = $product_barcode_arr[$key];
                            $RequestData['product_quantity'] = $product_quantity_arr[$key];
                            $RequestData['product_system_barcode'] = $sys_barcode_arr[$key];
                            $RequestData['product_cost_price'] = $product_cost_price_arr[$key];
                            $RequestData['product_unit_price'] = $unit_sale_price_arr[$key];
                            $RequestData['total_sales_price'] = $product_sales_price_arr[$key];

                            // $RequestData['cash_price'] = $unit_sale_price_arr[$key];
                            // $RequestData['principal_amount'] = $unit_sale_price_arr[$key];
                            // $RequestData['total_amount'] = $product_sales_price_arr[$key];
                            $RequestData['product_serial_no'] = $product_serial_arr[$key];

                            //////////////
                            $RequestData['total_cost_price'] = ($RequestData['product_quantity'] * $RequestData['product_cost_price']);

                            $total_cost_amount += $RequestData['total_cost_price'];

                            /* Child Table's CashPrice, Principle, installment_profit */
                            /*  */
                            $RequestData['cash_price'] = $product_sales_price_arr[$key];
                            $RequestData['principal_amount'] = $product_sales_price_arr[$key];
                            $RequestData['installment_profit'] = 0;

                            // ## Sales Details insert
                            $isInsertD = SalesDetails::create($RequestData);

                            if ($isInsertD) {
                                continue;
                            } else {
                                $IsInsertFlag = false;
                            }
                        }
                    }

                    //////////////////////////////////////
                    if ($total_cost_amount > 0) {
                        SalesMaster::where('sales_bill_no', $RequestData['sales_bill_no'])->update(['total_cost_amount' => $total_cost_amount]);
                    }

                }
                // ## commit DB and return with success masssage
                DB::commit();
                $notification = array(
                    'message' => 'Successfully Insert in Sales',
                    'alert-type' => 'success',
                );

                // return Redirect::to('pos/sales_cash')->with($notification);
                return Redirect::to('pos/sales_cash/invoice/' . $RequestData['sales_bill_no'])->with($notification);
            } catch (\Exception $e) {
                // dd($e);
                DB::rollBack();
                // ## role back undo all DB operation
                // ## return $e file line and error masssage in console log ;
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
            return view('POS.Sales.add', compact('DivData', 'BranchData'));
        }
    }

    public function edit(Request $request, $id = null)
    {

        $SalesData = SalesMaster::where('sales_bill_no', $id)->first();
        $SalesDataD = SalesDetails::where('sales_bill_no', $id)->get();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'sales_bill_no' => 'required',
            ]);

            /* ---------------------------------- Master Table update start ------------------------- */
            $RequestData = $request->all();

            // ## Date Format
            $sales_date = new DateTime($RequestData['sales_date']);
            $RequestData['sales_date'] = $sales_date->format('Y-m-d');

            // ## Product data
            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            $product_cost_price_arr = (isset($RequestData['product_cost_price_arr']) ? $RequestData['product_cost_price_arr'] : array());
            $unit_sale_price_arr = (isset($RequestData['unit_sale_price_arr']) ? $RequestData['unit_sale_price_arr'] : array());
            $product_sales_price_arr = (isset($RequestData['product_sales_price_arr']) ? $RequestData['product_sales_price_arr'] : array());
            $sys_barcode_arr = (isset($RequestData['sys_barcode_arr']) ? $RequestData['sys_barcode_arr'] : array());
            $product_name_arr = (isset($RequestData['product_name_arr']) ? $RequestData['product_name_arr'] : array());
            $product_barcode_arr = (isset($RequestData['product_barcode_arr']) ? $RequestData['product_barcode_arr'] : array());
            $product_serial_arr = (isset($RequestData['product_serial_arr']) ? $RequestData['product_serial_arr'] : array());

            $RequestData['paid_amount'] = $RequestData['total_payable_amount'];
            $RequestData['due_amount'] = 0;

            /* Master Table's CashPrice, Principle, installment_profit */
            /* Here total_amount = sum of all product amount in this sales */

            $RequestData['cash_price'] = $RequestData['total_amount'];
            $RequestData['principal_amount'] = $RequestData['total_amount'];
            $RequestData['installment_profit'] = 0;
            $RequestData['is_complete'] = 1;
            $RequestData['complete_date'] = $RequestData['sales_date'];

            if (count(array_filter($product_id_arr)) <= 0) {
                $notification = array(
                    'message' => 'Something went wrong! Please try again.',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

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
                    $RequestData['principal_amount'] = $RequestData['collection_amount'];
                    $RequestData['installment_profit'] = 0;

                    // $fiscal_year = DB::table('gnl_fiscal_year')
                    //     ->select('id')
                    //     ->where('company_id', $RequestData['company_id'])
                    //     ->where('fy_start_date', '<=', $RequestData['sales_date'])
                    //     ->where('fy_end_date', '>=', $RequestData['sales_date'])
                    //     ->orderBy('id', 'DESC')
                    //     ->first();

                    // if ($fiscal_year) {
                    //     $RequestData['fiscal_year_id'] = $fiscal_year->id;
                    // }

                    // // Fiscal Year
                    $fiscal_year = Common::systemFiscalYear($RequestData['sales_date'], $RequestData['company_id']);
                    $RequestData['fiscal_year_id'] = $fiscal_year['id'];

                    $RequestData['collection_no'] = POSS::generateCollectionNo(Common::getBranchId());
                    $RequestData['sales_type'] = 1;

                    // Collection Insert
                    $isInsertC = Collection::create($RequestData);

                    $IsInsertFlag = true;

                    $total_cost_amount = 0;

                    foreach ($product_id_arr as $key => $product_id_sin) {
                        if (!empty($product_id_sin)) {

                            $RequestData['product_id'] = $product_id_sin;
                            $RequestData['product_name'] = $product_name_arr[$key];
                            $RequestData['product_barcode'] = $product_barcode_arr[$key];
                            $RequestData['product_quantity'] = $product_quantity_arr[$key];
                            $RequestData['product_system_barcode'] = $sys_barcode_arr[$key];
                            $RequestData['product_cost_price'] = $product_cost_price_arr[$key];
                            $RequestData['product_unit_price'] = $unit_sale_price_arr[$key];

                            $RequestData['total_sales_price'] = $product_sales_price_arr[$key];
                            // $RequestData['cash_price'] = $unit_sale_price_arr[$key];
                            // $RequestData['principal_amount'] = $unit_sale_price_arr[$key];
                            // $RequestData['total_amount'] = $product_sales_price_arr[$key];
                            $RequestData['product_serial_no'] = $product_serial_arr[$key];

                            //////////////
                            $RequestData['total_cost_price'] = ($RequestData['product_quantity'] * $RequestData['product_cost_price']);
                            $total_cost_amount += $RequestData['total_cost_price'];

                            /* Child Table's CashPrice, Principle, installment_profit */
                            $RequestData['cash_price'] = $product_sales_price_arr[$key];
                            $RequestData['principal_amount'] = $product_sales_price_arr[$key];
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

                    //////////////////////////////////////
                    if ($total_cost_amount > 0) {
                        SalesMaster::where('sales_bill_no', $RequestData['sales_bill_no'])->update(['total_cost_amount' => $total_cost_amount]);
                    }
                }

                DB::commit();
                //commit and  return with success massage
                $notification = array(
                    'message' => 'Successfully Updated Data',
                    'alert-type' => 'success',
                );

                return Redirect::to('pos/sales_cash')->with($notification);

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
            return view('POS.Sales.edit',
                compact('SalesData', 'SalesDataD', 'BranchData', 'DivData'));
        }
    }

    public function instIndex(Request $request)
    {
        if ($request->ajax()) {
            // ## Ordering Variable
            $columns = array(
                0 => 'pos_sales_m.id',
                1 => 'pos_sales_m.sales_date',
                2 => 'pos_sales_m.sales_bill_no',
                5 => 'pos_sales_m.total_quantity',
                6 => 'pos_sales_m.total_amount',
                7 => 'pos_sales_m.paid_amount',
                8 => 'pos_sales_m.due_amount',
                9 => 'pos_sales_m.installment_amount',
            );

            // ## Datatable Pagination Variable
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // ## Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $sDate = (empty($request->input('sDate'))) ? null : $request->input('sDate');
            $eDate = (empty($request->input('eDate'))) ? null : $request->input('eDate');
            $branchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');
            $salesType = (empty($request->input('salesType'))) ? null : $request->input('salesType');
            // $PGroupID = (empty($request->input('PGroupID'))) ? null : $request->input('PGroupID');
            // $CategoryId = (empty($request->input('CategoryId'))) ? null : $request->input('CategoryId');
            // $SubCatID = (empty($request->input('SubCatID'))) ? null : $request->input('SubCatID');
            // $BrandID = (empty($request->input('BrandID'))) ? null : $request->input('BrandID');
            $customerID = (empty($request->input('customerID'))) ? null : $request->input('customerID');
            $installmentTypeID = (empty($request->input('installmentTypeID'))) ? null : $request->input('installmentTypeID');
            $installmentMonthID = (empty($request->input('installmentMonthID'))) ? null : $request->input('installmentMonthID');
            $employeeID = (empty($request->input('employeeID'))) ? null : $request->input('employeeID');

            // ## Query
            $salesData = SalesMaster::where([['pos_sales_m.is_delete', 0],
                ['gnl_branchs.is_approve', 1], ['pos_sales_m.sales_type', 2], ['pos_sales_m.is_opening', 0]])
                ->select('pos_sales_m.*', 'gnl_branchs.branch_name as branch_name', 'gnl_branchs.branch_code as branch_code')
                ->whereIn('pos_sales_m.branch_id', HRS::getUserAccesableBranchIds())
                ->leftJoin('gnl_branchs', 'pos_sales_m.branch_id', '=', 'gnl_branchs.id')
                ->where(function ($salesData) use ($search, $sDate, $eDate, $branchID,
                    $customerID, $installmentTypeID, $installmentMonthID, $employeeID) {

                    if (!empty($search)) {
                        $salesData->where('pos_sales_m.sales_bill_no', 'LIKE', "%{$search}%")
                            ->orWhere('pos_sales_m.total_amount', 'LIKE', "%{$search}%")
                            ->orWhere('pos_sales_m.paid_amount', 'LIKE', "%{$search}%")
                            ->orWhere('pos_sales_m.due_amount', 'LIKE', "%{$search}%")
                            ->orWhere('pos_sales_m.installment_amount', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_branchs.branch_name', 'LIKE', "%{$search}%");
                    }

                    if (!empty($branchID)) {
                        $salesData->where('pos_sales_m.branch_id', '=', $branchID);
                    }

                    if (!empty($sDate) && !empty($eDate)) {

                        $sDate = new DateTime($sDate);
                        $sDate = $sDate->format('Y-m-d');

                        $eDate = new DateTime($eDate);
                        $eDate = $eDate->format('Y-m-d');

                        $salesData->whereBetween('pos_sales_m.sales_date', [$sDate, $eDate]);
                    }

                    if (!empty($customerID)) {
                        $salesData->where('pos_sales_m.customer_id', 'LIKE', $customerID);
                    }

                    if (!empty($installmentTypeID)) {
                        $salesData->where('pos_sales_m.installment_type', '=', $installmentTypeID);
                    }

                    if (!empty($installmentMonthID)) {
                        $salesData->where('pos_sales_m.inst_package_id', '=', $installmentMonthID);
                    }

                    if (!empty($employeeID)) {
                        $salesData->where('pos_sales_m.employee_id', 'LIKE', $employeeID);
                    }
                })
            // ->offset($start)
            // ->limit($limit)
                ->orderBy($order, $dir)
                ->orderBy('pos_sales_m.sales_date', 'DESC')
                ->orderBy('pos_sales_m.sales_bill_no', 'DESC');
            // ->get();

            $tempQueryData = clone $salesData;
            $salesData = $salesData->offset($start)->limit($limit)->get();

            $totalData = SalesMaster::where([['pos_sales_m.is_delete', 0], ['pos_sales_m.sales_type', 2], ['pos_sales_m.is_opening', 0]])
                ->whereIn('pos_sales_m.branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;
            if (!empty($search) || !empty($sDate) || !empty($eDate) || !empty($branchID)) {
                $totalFiltered = $tempQueryData->count(); //count($salesData);
            }

            $billNoList = $salesData->pluck('sales_bill_no');
            $detailsData = DB::table('pos_sales_d as dt')
                ->whereIn('dt.sales_bill_no', $billNoList->toarray())
                ->join('pos_products as pro', function ($detailsData) {
                    $detailsData->on('pro.id', '=', 'dt.product_id')
                        ->where('pro.is_delete', 0);
                })
                ->select('dt.sales_bill_no', 'pro.product_name')
                ->get();

            $collectionCount = DB::table('pos_collections')
                ->where([['is_active', 1], ['is_delete', 0]])
                ->select(DB::raw('count(*) as collection_count, sales_bill_no'))
                ->groupBy('sales_bill_no')
                ->having('collection_count', '>', 1)
                ->pluck('sales_bill_no')
                ->toArray();

            $dataSet = array();
            $i = $start;

            foreach ($salesData as $row) {
                $TempSet = array();
                $IgnoreArray = array();

                if (date('d-m-Y', strtotime($row->sales_date)) != Common::systemCurrentDate($row->branch_id, 'pos')) {
                    $IgnoreArray = ['delete', 'edit'];

                    if (in_array($row->sales_bill_no, $collectionCount) == true) {
                        $IgnoreArray = ['delete', 'edit'];
                    }
                }

                $product_names = $detailsData->where('sales_bill_no', $row->sales_bill_no)
                    ->pluck('product_name')
                    ->toArray();

                // if(count($product_names) > 0){
                $TempSet = [
                    'id' => ++$i,
                    'sales_type' => ($row->sales_type == 1) ? 'Cash' : 'Installment',
                    'sales_date' => date('d-m-Y', strtotime($row->sales_date)),
                    'sales_bill_no' => $row->sales_bill_no,
                    'product_name' => implode(', ', $product_names),
                    'total_quantity' => $row->total_quantity,
                    'total_amount' => $row->total_amount,
                    'paid_amount' => $row->paid_amount,
                    'due_amount' => $row->due_amount,
                    'installment_amount' => $row->installment_amount,
                    'branch_name' => (!empty($row->branch_name)) ? $row->branch_name . " (" . $row->branch_code . ")" : "",
                    'customer_name' => (!empty($row->customer['customer_name'])) ? $row->customer['customer_name'] . " (" . $row->customer['customer_no'] . ")" : "",
                    'action' => Role::roleWiseArray($this->GlobalRole, $row->sales_bill_no, $IgnoreArray),
                ];

                $dataSet[] = $TempSet;
                // }
                // else{
                //     --$totalData;
                //     --$totalFiltered;
                // }
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $dataSet,
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Sales.inst_index');
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
            $product_barcode_arr = (isset($RequestData['product_barcode_arr']) ? $RequestData['product_barcode_arr'] : array());
            $product_system_barcode_arr = (isset($RequestData['product_system_barcode_arr']) ? $RequestData['product_system_barcode_arr'] : array());

            $product_sno_arr = (isset($RequestData['product_sno_arr']) ? $RequestData['product_sno_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            $product_cost_price_arr = (isset($RequestData['product_cost_price_arr']) ? $RequestData['product_cost_price_arr'] : array());

            $unit_sales_price_arr = (isset($RequestData['unit_sales_price_arr']) ? $RequestData['unit_sales_price_arr'] : array());

            $total_sales_price_arr = (isset($RequestData['total_sales_price_arr']) ? $RequestData['total_sales_price_arr'] : array());

            $sales_date = new DateTime($RequestData['sales_date']);
            $RequestData['sales_date'] = $sales_date->format('Y-m-d');
            $RequestData['payment_month'] = date('M');

            // ## Fiscal Year
            $fiscal_year = Common::systemFiscalYear($RequestData['sales_date'], $RequestData['company_id']);
            $RequestData['fiscal_year_id'] = $fiscal_year['id'];

            $scheduleList = POSS::installmentSchedule($RequestData['company_id'], $RequestData['branch_id'], null,
                $RequestData['sales_date'], $RequestData['installment_type'], $RequestData['installment_month']);

            $RequestData['installment_date'] = end($scheduleList);

            /* Master Table's CashPrice, Principle, installment_profit */
            /* Here total_amount = sum of all product amount in this sales */

            $principal_amount_m = ($RequestData['total_amount'] / (100 + $RequestData['installment_rate'])) * 100;
            $installment_profit_m = ($RequestData['total_amount'] - $principal_amount_m);

            $RequestData['cash_price'] = $principal_amount_m;
            $RequestData['principal_amount'] = $principal_amount_m;
            $RequestData['installment_profit'] = $installment_profit_m;

            if ($RequestData['total_payable_amount'] == $RequestData['paid_amount']) {
                $RequestData['is_complete'] = 1;
                $RequestData['complete_date'] = $RequestData['sales_date'];
            }

            if (count(array_filter($product_id_arr)) <= 0) {
                $notification = array(
                    'message' => 'Something went wrong! Please try again.',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

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

                $principal_amount_c = ($RequestData['collection_amount'] / (100 + $RequestData['installment_rate'])) * 100;
                $installment_profit_c = ($RequestData['collection_amount'] - $principal_amount_c);

                $RequestData['cash_price'] = $principal_amount_c;
                $RequestData['principal_amount'] = $principal_amount_c;
                $RequestData['installment_profit'] = $installment_profit_c;
                $RequestData['collection_no'] = POSS::generateCollectionNo(Common::getBranchId());

                // ## Collection Insert
                $isInsertC = Collection::create($RequestData);

                $total_cost_amount = 0;

                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {

                        $RequestData['product_id'] = $product_id_sin;
                        // $RequestData['barcode_no'] = $sys_barcode_arr[$key];
                        // $RequestData['product_name'] = $product_name_arr[$key];

                        $RequestData['product_barcode'] = $product_barcode_arr[$key];
                        $RequestData['product_system_barcode'] = $product_system_barcode_arr[$key];
                        $RequestData['product_serial_no'] = $product_sno_arr[$key];

                        $RequestData['product_quantity'] = $product_quantity_arr[$key];
                        $RequestData['product_cost_price'] = $product_cost_price_arr[$key];

                        $RequestData['product_unit_price'] = $unit_sales_price_arr[$key];
                        $RequestData['total_sales_price'] = $total_sales_price_arr[$key];
                        // $RequestData['total_amount'] = $total_sales_price_arr[$key];

                        //////////////
                        $RequestData['total_cost_price'] = ($RequestData['product_quantity'] * $RequestData['product_cost_price']);
                        $total_cost_amount += $RequestData['total_cost_price'];

                        /* Child Table's CashPrice, Principle, installment_profit */
                        /* Here total_amount = sum of product amount in this product */

                        $principal_amount_d = ($total_sales_price_arr[$key] / (100 + $RequestData['installment_rate'])) * 100;
                        $installment_profit_d = ($total_sales_price_arr[$key] - $principal_amount_d);

                        $RequestData['cash_price'] = $principal_amount_d;
                        $RequestData['principal_amount'] = $principal_amount_d;
                        $RequestData['installment_profit'] = $installment_profit_d;

                        SalesDetails::create($RequestData);
                    }
                }

                //////////////////////////////////////
                if ($total_cost_amount > 0) {
                    SalesMaster::where('sales_bill_no', $RequestData['sales_bill_no'])->update(['total_cost_amount' => $total_cost_amount]);
                }

                DB::commit();

                $notification = array(
                    'message' => 'Successfully Inserted data',
                    'alert-type' => 'success',
                );
                // return redirect('pos/sales_installment')->with($notification);
                return Redirect::to('pos/sales_installment/invoice/' . $RequestData['sales_bill_no'])->with($notification);

            } catch (\Exception $e) {

                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to insert data',
                    'alert-type' => 'error',
                    // 'console_error' => $e
                );
                return redirect()->back()->with($notification);
            }

        } else {
            $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
                ->orderBy('branch_code', 'ASC')
                ->get();

            $DivData = Division::where('is_delete', 0)->orderBy('id', 'DESC')->get();

            return view('POS.Sales.inst_add', compact('BranchData', 'DivData'));
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

            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_barcode_arr = (isset($RequestData['product_barcode_arr']) ? $RequestData['product_barcode_arr'] : array());
            $product_system_barcode_arr = (isset($RequestData['product_system_barcode_arr']) ? $RequestData['product_system_barcode_arr'] : array());

            $product_sno_arr = (isset($RequestData['product_sno_arr']) ? $RequestData['product_sno_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            $product_cost_price_arr = (isset($RequestData['product_cost_price_arr']) ? $RequestData['product_cost_price_arr'] : array());

            $unit_sales_price_arr = (isset($RequestData['unit_sales_price_arr']) ? $RequestData['unit_sales_price_arr'] : array());

            $total_sales_price_arr = (isset($RequestData['total_sales_price_arr']) ? $RequestData['total_sales_price_arr'] : array());

            $sales_date = new DateTime($RequestData['sales_date']);
            $RequestData['sales_date'] = $sales_date->format('Y-m-d');
            $RequestData['payment_month'] = date('M');

            /* Master Table's CashPrice, Principle, installment_profit */
            /* Here total_amount = sum of all product amount in this sales */

            $principal_amount_m = ($RequestData['total_amount'] / (100 + $RequestData['installment_rate'])) * 100;
            $installment_profit_m = ($RequestData['total_amount'] - $principal_amount_m);

            $RequestData['cash_price'] = $principal_amount_m;
            $RequestData['principal_amount'] = $principal_amount_m;
            $RequestData['installment_profit'] = $installment_profit_m;

            if ($RequestData['total_payable_amount'] == $RequestData['paid_amount']) {
                $RequestData['is_complete'] = 1;
                $RequestData['complete_date'] = $RequestData['sales_date'];
            }

            if (count(array_filter($product_id_arr)) <= 0) {
                $notification = array(
                    'message' => 'Something went wrong! Please try again.',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
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

                $principal_amount_c = ($RequestData['collection_amount'] / (100 + $RequestData['installment_rate'])) * 100;
                $installment_profit_c = ($RequestData['collection_amount'] - $principal_amount_c);

                $RequestData['cash_price'] = $principal_amount_c;
                $RequestData['principal_amount'] = $principal_amount_c;
                $RequestData['installment_profit'] = $installment_profit_c;

                // ## Fiscal Year
                $fiscal_year = Common::systemFiscalYear($RequestData['sales_date'], $RequestData['company_id']);
                $RequestData['fiscal_year_id'] = $fiscal_year['id'];

                // $fiscal_year = DB::table('gnl_fiscal_year')
                //     ->select('id')
                //     ->where('company_id', $RequestData['company_id'])
                //     ->where('fy_start_date', '<=', $RequestData['sales_date'])
                //     ->where('fy_end_date', '>=', $RequestData['sales_date'])
                //     ->orderBy('id', 'DESC')
                //     ->first();

                // if ($fiscal_year) {
                //     $RequestData['fiscal_year_id'] = $fiscal_year->id;
                // }

                $RequestData['collection_no'] = POSS::generateCollectionNo(Common::getBranchId());

                // ## Collection Insert
                $isInsertC = Collection::create($RequestData);

                $total_cost_amount = 0;

                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {

                        $RequestData['product_id'] = $product_id_sin;
                        // $RequestData['barcode_no'] = $sys_barcode_arr[$key];
                        // $RequestData['product_name'] = $product_name_arr[$key];

                        $RequestData['product_barcode'] = $product_barcode_arr[$key];
                        $RequestData['product_system_barcode'] = $product_system_barcode_arr[$key];
                        $RequestData['product_serial_no'] = $product_sno_arr[$key];

                        $RequestData['product_quantity'] = $product_quantity_arr[$key];
                        $RequestData['product_cost_price'] = $product_cost_price_arr[$key];

                        $RequestData['product_unit_price'] = $unit_sales_price_arr[$key];
                        $RequestData['total_sales_price'] = $total_sales_price_arr[$key];
                        // $RequestData['total_amount'] = $total_sales_price_arr[$key];

                        //////////////
                        $RequestData['total_cost_price'] = ($RequestData['product_quantity'] * $RequestData['product_cost_price']);
                        $total_cost_amount += $RequestData['total_cost_price'];

                        /* Child Table's CashPrice, Principle, installment_profit */
                        /* Here total_amount = sum of product amount in this product */

                        $principal_amount_d = ($total_sales_price_arr[$key] / (100 + $RequestData['installment_rate'])) * 100;
                        $installment_profit_d = ($total_sales_price_arr[$key] - $principal_amount_d);

                        $RequestData['cash_price'] = $principal_amount_d;
                        $RequestData['principal_amount'] = $principal_amount_d;
                        $RequestData['installment_profit'] = $installment_profit_d;

                        $isInsertD = SalesDetails::create($RequestData);
                    }
                }

                //////////////////////////////////////
                if ($total_cost_amount > 0) {
                    SalesMaster::where('sales_bill_no', $RequestData['sales_bill_no'])->update(['total_cost_amount' => $total_cost_amount]);
                }

                DB::commit();

                $notification = array(
                    'message' => 'Successfully Updated data',
                    'alert-type' => 'success',
                );
                return redirect('pos/sales_installment')->with($notification);

            } catch (\Exception $e) {

                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to Update data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            return view('POS.Sales.inst_edit', compact('SalesDataM', 'SalesDataD'));
        }
    }

    public function view($id = null)
    {
        $SalesData = SalesMaster::where('sales_bill_no', $id)->first();
        $SalesDataD = SalesDetails::where('sales_bill_no', $id)->get();
        $CollectionData = Collection::where('sales_bill_no', $id)->get();

        return view('POS.Sales.view', compact('SalesData', 'SalesDataD', 'CollectionData'));
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
        $RequestData['customer_no'] = Common::generateCustomerNo($RequestData['branch_id']);

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
        return view('POS.Sales.invoice', compact('SalesData', 'SalesDataD', 'ProductData'));
    }

}
