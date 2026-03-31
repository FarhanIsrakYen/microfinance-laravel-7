<?php

namespace App\Http\Controllers\BILL;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\GNL\Division;
use App\Model\POS\Collection;
use App\Model\POS\Customer;
use App\Model\POS\SaleReturnm;
use App\Model\BILL\BillDetails;
use App\Model\BILL\BillMaster;
use App\Model\BILL\AgreementMaster;
use App\Model\BILL\Product;
use App\Model\BILL\Package;

use App\Services\CommonService as Common;
use App\Services\RoleService as Role;
use App\Services\HrService as HRS;
use App\Services\BillService as BILLS;

use Auth;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;


class BillController extends Controller
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
            // Ordering Variable
            $columns = array(
                0 => 'bill_cash_m.id',
                1 => 'bill_cash_m.bill_date',
                2 => 'bill_cash_m.bill_no',
                3 => 'bill_customers.customer_name',
                4 => 'bill_products.product_name',
                5 => 'bill_cash_m.total_quantity',
                6 => 'bill_cash_m.total_amount'
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
            $customerNo = (empty($request->input('customerNo'))) ? null : $request->input('customerNo');
            $productID = (empty($request->input('productID'))) ? null : $request->input('productID');

            // Query
            $billData = BillMaster::where([['bill_cash_m.is_delete', 0], ['bill_cash_m.is_opening', 0], 
                            ['gnl_branchs.is_delete', 0], ['gnl_branchs.is_active', 1], ['gnl_branchs.is_approve', 1]])
                        ->select('bill_cash_m.*',
                            'gnl_branchs.branch_name as branch_name', 'gnl_branchs.branch_code as branch_code')
                        ->whereIn('bill_cash_m.branch_id', HRS::getUserAccesableBranchIds())
                        ->leftJoin('gnl_branchs', 'bill_cash_m.branch_id', '=', 'gnl_branchs.id')
                        ->where(function ($billData) use ($search, $sDate, $eDate, $branchID, $customerNo) {
                            if (!empty($search)) {
                                $billData->where('bill_cash_m.bill_no', 'LIKE', "%{$search}%")
                                    ->orWhere('bill_cash_m.total_quantity', 'LIKE', "%{$search}%")
                                    ->orWhere('bill_cash_m.total_amount', 'LIKE', "%{$search}%");
                            }

                            if (!empty($customerNo)) {
                                $billData->where('bill_cash_m.customer_id', 'LIKE', $customerNo);
                            }

                            if (!empty($sDate) && !empty($eDate)) {

                                $sDate = new DateTime($sDate);
                                $sDate = $sDate->format('Y-m-d');

                                $eDate = new DateTime($eDate);
                                $eDate = $eDate->format('Y-m-d');

                                $billData->whereBetween('bill_cash_m.bill_date', [$sDate, $eDate]);
                            }
                        })
                        ->offset($start)
                        ->limit($limit)
                        ->orderBy($order, $dir)
                        // ->orderBy('bill_cash_m.bill_date', 'DESC')
                        ->get();

            $billNoList = $billData->pluck('bill_no');
            $detailsData = DB::table('bill_cash_d as dt')
                ->whereIn('dt.bill_no', $billNoList->toarray())
                ->join('bill_products as pro', function ($detailsData) {
                    $detailsData->on('pro.id', '=', 'dt.product_id')
                                ->where('pro.is_delete', 0);
                })
                ->select('dt.bill_no', 'pro.product_name')
                ->get();


            $totalData = BillMaster::where([['is_delete', 0], ['is_opening', 0]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;

            if (!empty($search) || !empty($sDate) || !empty($eDate)) {
                $totalFiltered = count($billData);
            }

            $dataSet = array();
            $i = 0;

            foreach ($billData as $row) {
                $TempSet = array();
                $IgnoreArray = array();

                if (date('d-m-Y', strtotime($row->bill_date)) != Common::systemCurrentDate()) {
                    $IgnoreArray = ['delete', 'edit'];
                }

                $product_names = $detailsData->where('bill_no', $row->bill_no)
                    ->pluck('product_name')
                    ->toArray();

                if(count($product_names) > 0){
                    $TempSet = [
                        'id'             => ++$i,
                        'bill_date'      => date('d-m-Y', strtotime($row->bill_date)),
                        'bill_no'        => $row->bill_no,
                        'product_name'   => implode(', ', $product_names),
                        'total_quantity' => $row->total_quantity,
                        'total_amount'   => $row->total_amount,
                        'customer_name'  => (!empty($row->customer['customer_name'])) ? $row->customer['customer_name']." (".$row->customer['customer_no'].")" : "",
                        'action'         => Role::roleWiseArray($this->GlobalRole, $row->bill_no, $IgnoreArray),
                    ];

                    $dataSet[] = $TempSet;
                }
                else{
                    --$totalData;
                    --$totalFiltered;
                }
            }

            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $dataSet,
            );

            echo json_encode($json_data);

        } else {
            return view('BILL.Bills.index');
        }
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'customer_id' => 'required'
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();

            /* Product Data */
            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            $unit_sale_price_arr = (isset($RequestData['unit_sale_price_arr']) ? $RequestData['unit_sale_price_arr'] : array());
            $product_sales_price_arr = (isset($RequestData['product_sales_price_arr']) ? $RequestData['product_sales_price_arr'] : array());
            $product_type_arr = (isset($RequestData['product_type_arr']) ? $RequestData['product_type_arr'] : array());
        

            /* Format date*/
            $bill_date = new DateTime($RequestData['bill_date']);
            $RequestData['bill_date'] = $bill_date->format('Y-m-d');

            // // Fiscal Year
            $fiscal_year = Common::systemFiscalYear($RequestData['bill_date'], $RequestData['company_id']);
            $RequestData['fiscal_year_id'] = $fiscal_year['id'];


            /*DB begain transaction*/
            DB::beginTransaction();

            try {

                if (BillMaster::where('bill_no', '=', $RequestData['bill_no'])->exists()) {
                    $RequestData['bill_no'] = BILLS::generateBillCash($RequestData['branch_id']);
                }

                $isInsertM = BillMaster::create($RequestData);

                if ($isInsertM) {
                    $IsInsertFlag = true;

                    foreach ($product_id_arr as $key => $product_id_sin) {
                        if (!empty($product_id_sin)) {

                            $RequestData['product_id'] = $product_id_sin;
                            $RequestData['product_quantity'] = $product_quantity_arr[$key];
                            $RequestData['product_unit_price'] = $unit_sale_price_arr[$key];
                            $RequestData['product_sales_price'] = $product_sales_price_arr[$key];
                            $RequestData['product_type'] = $product_type_arr[$key];

                            // Bill Details insert
                            $isInsertD = BillDetails::create($RequestData);
                            

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
                    'message' => 'Successfully Inserted in Bills',
                    'alert-type' => 'success',
                );
                return Redirect::to('bill/cash_bill')->with($notification);
            } catch (\Exception $e) {
                DB::rollBack();
                // role back undo all DB operation
                // return $e file line and error masssage in console log ;
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Bills',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            return view('BILL.Bills.add');
        }
    }

    public function edit(Request $request, $id = null)
    {

        $billData = BillMaster::where('bill_no', $id)->first();
        $billDataD = BillDetails::where('bill_no', $id)->get();


        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'customer_id' => 'required',
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();

            /* Product Data */
            $product_id_arr          = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_quantity_arr    = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            $unit_sale_price_arr     = (isset($RequestData['unit_sale_price_arr']) ? $RequestData['unit_sale_price_arr'] : array());
            $product_sales_price_arr = (isset($RequestData['product_sales_price_arr']) ? $RequestData['product_sales_price_arr'] : array());
            $product_type_arr        = (isset($RequestData['product_type_arr']) ? $RequestData['product_type_arr'] : array());
        
            /* Format date*/
            $bill_date = new DateTime($RequestData['bill_date']);
            $RequestData['bill_date'] = $bill_date->format('Y-m-d');

            // // Fiscal Year
            $fiscal_year = Common::systemFiscalYear($RequestData['bill_date'], $RequestData['company_id']);
            $RequestData['fiscal_year_id'] = $fiscal_year['id'];

            DB::beginTransaction();

            try {

                $isUpdateM = $billData->update($RequestData);

                if ($isUpdateM) {

                    /* Delete Bill details data for this bill no */
                    BillDetails::where('bill_no', $id)->get()->each->delete();

                    // // Fiscal Year
                    $fiscal_year = Common::systemFiscalYear($RequestData['bill_date'], $RequestData['company_id']);
                    $RequestData['fiscal_year_id'] = $fiscal_year['id'];

                    $IsInsertFlag = true;

                    foreach ($product_id_arr as $key => $product_id_sin) {
                        if (!empty($product_id_sin)) {

                            $RequestData['product_id']          = $product_id_sin;
                            $RequestData['product_quantity']    = $product_quantity_arr[$key];
                            $RequestData['product_unit_price']  = $unit_sale_price_arr[$key];
                            $RequestData['product_sales_price'] = $product_sales_price_arr[$key];
                            $RequestData['product_type']        = $product_type_arr[$key];

                            // Bill Details insert
                            $isInsertD = BillDetails::create($RequestData);
                            

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

                return Redirect::to('bill/cash_bill')->with($notification);

            } catch (\Exception $e) {
                dd($e);
                DB::rollBack();
                // return $e file line and error masssage in console log ;
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Bills',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
            }

            /* ----------------------- Master table Update end ------------------------ */
        } else {
            return view('BILL.Bills.edit',
                compact('billData', 'billDataD'));
        }
    }

    public function view($id = null)
    {
        $billData = BillMaster::where('bill_no', $id)->first();
        $billDataD = BillDetails::where('bill_no', $id)->get();

        return view('BILL.Bills.view', compact('billData', 'billDataD'));
    }

    public function delete($id = null)
    {
        DB::beginTransaction();

        try {
            $billData = BillMaster::where('bill_no', $id)->first();

            $billData->is_delete = 1;
            $isSuccess = $billData->update();

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

    public function autoProcess(Request $request)
    {
        if ($request->isMethod('post')) {

            $RequestData = $request->all();

            $bill_date = (new DateTime(Common::systemCurrentDate()))->format('Y-m-d');
            $RequestData['bill_date'] = $bill_date;


            $RequestData['bill_month'] = $RequestData['month_year'];

            $RequestData['employee_id'] = Auth::id();

            // // Fiscal Year
            $fiscal_year = Common::systemFiscalYear($RequestData['bill_date'], $RequestData['company_id']);
            $RequestData['fiscal_year_id'] = $fiscal_year['id'];

            $agreement_id_arr = (isset($RequestData['agreement_id_arr'])) ? $RequestData['agreement_id_arr'] : array();
            $agreement_no_arr = (isset($RequestData['agreement_no_arr'])) ? $RequestData['agreement_no_arr'] : array();
            $customer_id_arr = (isset($RequestData['customer_id_arr'])) ? $RequestData['customer_id_arr'] : array();
            $product_id_arr = (isset($RequestData['product_id_arr'])) ? $RequestData['product_id_arr'] : array();
            $product_type_arr = (isset($RequestData['product_type_arr'])) ? $RequestData['product_type_arr'] : array();
            $total_service_fee_arr = (isset($RequestData['total_service_fee_arr'])) ? $RequestData['total_service_fee_arr'] : array();
            
            /*DB begain transaction*/
            DB::beginTransaction();

            $id_array = [];

            try {
                foreach ($agreement_no_arr as $key => $value) {
                    $RequestData['agreement_no'] = $agreement_no_arr[$key];
                    $RequestData['bill_no'] = BILLS::generateBillCash($RequestData['branch_id']);
                    $RequestData['customer_id'] = $customer_id_arr[$key];
                    $RequestData['product_id'] = $product_id_arr[$key];
                    $RequestData['product_type'] = $product_type_arr[$key];
                    $RequestData['service_charge'] = $total_service_fee_arr[$key];

                    $isInsertM = BillMaster::create($RequestData);

                    array_push($id_array, $isInsertM->bill_no);

                    if ($isInsertM) {
                        $IsInsertFlag = true;
                        $isInsertD = BillDetails::create($RequestData);
                    }
                    if ($isInsertD) {
                        continue;
                    } else {
                        $IsInsertFlag = false;
                    }
                }

                $concated_id = base64_encode(implode("&", $id_array));
                
                //commit DB and return with success masssage
                DB::commit();
                $notification = array(
                    'message' => 'Successfully Inserted in Bills',
                    'alert-type' => 'success',
                );
                return Redirect::to('bill/cash_bill/invoice/'.$concated_id)->with($notification);
            } catch (\Exception $e) {
                DB::rollBack();
                // role back undo all DB operation
                // return $e file line and error masssage in console log ;
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Bills',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        }
        else {
            return view('BILL.Bills.auto_process');
        }
    }

    public function invoice($id = null)
    {
        $id_array = explode("&",$id);
        $id_array = explode("&",base64_decode($id_array[0]));
        $billData = BillMaster::whereIn('bill_no', $id_array)->get();

        $billDataD = BillDetails::whereIn('bill_no', $id_array)->get();

        $productData = Product::where([['is_active', 1], ['is_delete', 0]])
                    ->whereIn('id', $billDataD->pluck('product_id'))
                    ->get();

        $packageData = Package::where([['is_active', 1], ['is_delete', 0]])
                    ->whereIn('id', $billDataD->pluck('product_id'))
                    ->get();

        return view('BILL.Bills.invoice', compact('billData', 'billDataD', 'productData','packageData'));
    }


}
