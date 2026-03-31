<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\POS\Collection;
use App\Model\POS\DayEnd;
use App\Model\POS\PInstallmentPackage;
use App\Model\POS\POBDueSaleDetails;
use App\Model\POS\POBDueSaleMaster;
use App\Model\POS\Product;
use App\Model\POS\SalesMaster;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\PosService as POSS;
use App\Services\RoleService as Role;
use Auth;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;

class PCustomerOBController extends Controller
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
                1 => 'opening_date',
                2 => 'total_customer',
                3 => 'total_sales_amount',
                4 => 'total_collection',
                5 => 'total_due_amount',
            );

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $BranchID = (empty($request->input('BranchID'))) ? null : $request->input('BranchID');

            // Query
            $CDSaleData = POBDueSaleMaster::where('pos_ob_duesales_m.is_delete', '=', 0)
                ->whereIn('pos_ob_duesales_m.branch_id', HRS::getUserAccesableBranchIds())
                ->select('pos_ob_duesales_m.*', 'gnl_branchs.branch_name', 'gnl_branchs.branch_code')
                ->leftJoin('gnl_branchs', 'pos_ob_duesales_m.branch_id', '=', 'gnl_branchs.id')
                ->where(function ($CDSaleData) use ($search, $BranchID) {
                    if (!empty($search)) {
                        $CDSaleData->where('gnl_branchs.branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('pos_ob_duesales_m.total_customer', 'LIKE', "%{$search}%")
                            ->orWhere('pos_ob_duesales_m.total_sales_amount', 'LIKE', "%{$search}%")
                            ->orWhere('pos_ob_duesales_m.opening_date', 'LIKE', "%{$search}%")
                            ->orWhere('pos_ob_duesales_m.total_collection', 'LIKE', "%{$search}%")
                            ->orWhere('pos_ob_duesales_m.total_due_amount', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_branchs.branch_name', 'LIKE', "%{$search}%");
                    }

                    if (!empty($BranchID)) {
                        $CDSaleData->where('pos_ob_duesales_m.branch_id', '=', $BranchID);
                    }

                })
            // ->offset($start)
            // ->limit($limit)
                ->orderBy($order, $dir)
                ->orderBy('pos_ob_duesales_m.opening_date', 'DESC')
                ->orderBy('pos_ob_duesales_m.id', 'DESC');
            // ->get();

            $tempQueryData = clone $CDSaleData;
            $CDSaleData = $CDSaleData->offset($start)->limit($limit)->get();

            $totalData = POBDueSaleMaster::where([['is_delete', 0], ['is_active', 1]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;

            if (!empty($search) || !empty($BranchID)) {
                $totalFiltered = $tempQueryData->count();
            }

            config()->set('database.connections.mysql.strict', false);
            DB::reconnect();

            // Check Day End table
            $checkForEnD = DayEnd::where([['is_active', 0], ['is_delete', 0]])
                ->select('branch_id')
                ->orderBy('id', 'DESC')
                ->groupBy('branch_id')
                ->pluck('branch_id')
                ->toArray();

            $data = array();
            $sl = $start + 1;

            if (!empty($CDSaleData)) {

                foreach ($CDSaleData as $Data) {

                    $IgnoreArray = array();
                    if (in_array($Data->branch_id, $checkForEnD)) {
                        $IgnoreArray = ['delete', 'edit'];
                    }

                    $nestedData['id'] = $sl++;

                    $CollectionDate = new DateTime($Data->opening_date); //format changed for searching by date
                    $nestedData['opening_date'] = $CollectionDate->format('d-m-Y');

                    $nestedData['total_customer'] = $Data->total_customer;
                    $nestedData['total_sales_amount'] = $Data->total_sales_amount;
                    $nestedData['total_collection'] = $Data->total_collection;
                    $nestedData['total_due_amount'] = $Data->total_due_amount;
                    $nestedData['branch_name'] = (!empty($Data->branch_name)) ? $Data->branch_name . "(" . $Data->branch_code . ")" : "";
                    $nestedData['action'] = Role::roleWiseArray($this->GlobalRole, $Data->ob_no, $IgnoreArray);

                    $data[] = $nestedData;
                }
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data,
                'current_branch_id' => Common::getBranchId(),
                'access_branch' => HRS::getUserAccesableBranchIds(),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.PCustomerOB.index');
        }
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            // $validateData = $request->validate([
            //     'sales_bill_no' => 'required',
            // ]);
            $RequestData = $request->all();

            // dd($RequestData);

            $branchList = Branch::where([['is_delete', 0], ['id', $RequestData['branch_id']]])
                ->select('branch_code')
                ->first();

            $branchCode = (!empty($branchList)) ? $branchList->branch_code : null;

            $opening_date = new DateTime($RequestData['opening_date']);
            $RequestData['opening_date'] = $opening_date->format('Y-m-d');

            $RequestData['ob_no'] = POSS::generateBillPOBCustomer($RequestData['branch_id']);

            $customer_id_arr = (isset($RequestData['customer_id_arr']) ? $RequestData['customer_id_arr'] : array());
            $customer_name_arr = (isset($RequestData['customer_name_arr']) ? $RequestData['customer_name_arr'] : array());
            $customer_no_arr = (isset($RequestData['customer_no_arr']) ? $RequestData['customer_no_arr'] : array());
            $sale_amt_arr = (isset($RequestData['sale_amt_arr']) ? $RequestData['sale_amt_arr'] : array());
            $collection_amt_arr = (isset($RequestData['collection_amt_arr']) ? $RequestData['collection_amt_arr'] : array());
            $due_amt_arr = (isset($RequestData['due_amt_arr']) ? $RequestData['due_amt_arr'] : array());
            $inst_month_arr = (isset($RequestData['inst_month_arr']) ? $RequestData['inst_month_arr'] : array());
            $inst_amt_arr = (isset($RequestData['inst_amt_arr']) ? $RequestData['inst_amt_arr'] : array());
            $inst_type_arr = (isset($RequestData['inst_type_arr']) ? $RequestData['inst_type_arr'] : array());
            $sale_date_arr = (isset($RequestData['sale_date_arr']) ? $RequestData['sale_date_arr'] : array());
            $last_clln_date_arr = (isset($RequestData['last_clln_date_arr']) ? $RequestData['last_clln_date_arr'] : array());
            $product_arr = (isset($RequestData['product_arr']) ? $RequestData['product_arr'] : array());
            $sale_bill_no_arr = (isset($RequestData['sale_bill_no_arr']) ? $RequestData['sale_bill_no_arr'] : array());

            if (count(array_filter($customer_id_arr)) <= 0) {
                $notification = array(
                    'message' => 'Something went wrong! Please try again.',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

            DB::beginTransaction();

            try {
                $isInsertM = POBDueSaleMaster::create($RequestData);

                // $RequestData['ob_no'] = $RequestData['ob_no'];

                foreach ($customer_id_arr as $key => $customer_id_sin) {

                    if (!empty($customer_id_sin)) {

                        $RequestData['customer_id'] = $customer_id_sin;
                        // $RequestData['customer_name'] = $customer_name_arr[$key];
                        // $RequestData['customer_no'] = $customer_no_arr[$key];
                        $RequestData['sales_amount'] = $sale_amt_arr[$key];
                        $RequestData['collection_amount'] = $collection_amt_arr[$key];
                        $RequestData['due_amount'] = $due_amt_arr[$key];
                        $RequestData['installment_month'] = $inst_month_arr[$key];
                        $RequestData['installment_amount'] = $inst_amt_arr[$key];
                        $RequestData['installment_type'] = $inst_type_arr[$key];

                        if (strstr($sale_bill_no_arr[$key], 'SL')) {
                            $RequestData['sales_bill_no'] = $sale_bill_no_arr[$key];
                        } else {
                            $RequestData['sales_bill_no'] = 'SL' . sprintf('%04d', $branchCode) . $sale_bill_no_arr[$key];
                        }

                        $sales_date = new DateTime($sale_date_arr[$key]);
                        $RequestData['sales_date'] = $sales_date->format('Y-m-d');

                        $last_clln_date = new DateTime($last_clln_date_arr[$key]);
                        $RequestData['last_collection_date'] = $last_clln_date->format('Y-m-d');

                        if (!empty($product_arr[$customer_id_sin])) {
                            $RequestData['sales_products'] = implode(',', $product_arr[$customer_id_sin]);
                        }

                        // dd($RequestData);

                        POBDueSaleDetails::create($RequestData);

                        // $RequestData['sales_bill_no'] = $RequestData['sales_bill_no'];
                        // $RequestData['collection_amount'] = $RequestData['collection_amount'];
                        $RequestData['collection_date'] = $RequestData['last_collection_date'];

                        $instRate = PInstallmentPackage::where('id', $RequestData['installment_month'])
                            ->first();

                        /* Collection Table's CashPrice, Principle, installment_profit */
                        $RequestData['cash_price'] = $RequestData['collection_amount'];
                        /* $RequestData['installment_rate'] */
                        $principal_amount_c = ($RequestData['collection_amount'] / (100 + $instRate->prod_inst_profit)) * 100;
                        $installment_profit_c = ($RequestData['collection_amount'] - $principal_amount_c);

                        $RequestData['principal_amount'] = $principal_amount_c;
                        $RequestData['installment_profit'] = $installment_profit_c;
                        $RequestData['employee_id'] = Auth::id();
                        $RequestData['payment_system_id'] = 1;
                        $RequestData['collection_no'] = POSS::generateCollectionNo(Common::getBranchId());
                        $RequestData['is_opening'] = 1;

                        Collection::create($RequestData);

                        $salesData = array();

                        $salesData['is_opening'] = 1;
                        $salesData['sales_type'] = 2;
                        $salesData['customer_id'] = $RequestData['customer_id'];
                        $salesData['company_id'] = $RequestData['company_id'];
                        $salesData['employee_id'] = $RequestData['employee_id'];
                        $salesData['total_quantity'] = count($product_arr[$customer_id_sin]);
                        $salesData['sales_bill_no'] = $RequestData['sales_bill_no'];
                        $salesData['total_amount'] = $RequestData['sales_amount'];
                        $salesData['paid_amount'] = $RequestData['collection_amount'];
                        $salesData['due_amount'] = $RequestData['due_amount'];
                        $salesData['installment_rate'] = $instRate->prod_inst_profit;
                        $salesData['cash_price'] = $RequestData['sales_amount'];
                        $salesData['payment_system_id'] = 1;
                        $salesData['principal_amount'] = ($salesData['total_amount'] /
                            (100 + $salesData['installment_rate'])) * 100;

                        $salesData['installment_profit'] = ($salesData['total_amount'] -
                            $salesData['principal_amount']);

                        $salesData['installment_type'] = $RequestData['installment_type'];
                        $salesData['inst_package_id'] = $RequestData['installment_month'];
                        $salesData['installment_month'] = $instRate->prod_inst_month;
                        $salesData['sales_date'] = $RequestData['sales_date'];
                        $salesData['installment_amount'] = $RequestData['installment_amount'];

                        $fiscal_year = DB::table('gnl_fiscal_year')
                            ->select('id')
                            ->where('company_id', $RequestData['company_id'])
                            ->where('fy_start_date', '<=', $RequestData['sales_date'])
                            ->where('fy_end_date', '>=', $RequestData['sales_date'])
                            ->orderBy('id', 'DESC')
                            ->first();

                        if ($fiscal_year) {
                            $salesData['fiscal_year_id'] = $fiscal_year->id;
                        }

                        SalesMaster::create($salesData);
                    }
                }

                DB::commit();

                $notification = array(
                    'message' => 'Successfully data inserted',
                    'alert-type' => 'success',
                );
                return Redirect('pos/customer_ob')->with($notification);

            } catch (\Exception $e) {

                DB::rollBack();

                $notification = array(
                    'message' => 'Unsuccessfull to insert data',
                    'alert-type' => 'error',
                );

                return redirect()->back()->with($notification);
            }

        } else {
            $ProductData = Product::where('is_delete', 0)->get();
            return view('POS.PCustomerOB.add', compact('ProductData'));
        }
    }

    public function edit(Request $request, $id = null)
    {
        $POBDueSaleDataM = POBDueSaleMaster::where(['ob_no' => $id, 'is_delete' => 0])->first();

        if ($request->isMethod('post')) {
            $RequestData = $request->all();

            $branchList = Branch::where([['is_delete', 0], ['id', $RequestData['branch_id']]])
                ->select('branch_code')
                ->first();

            $branchCode = (!empty($branchList)) ? $branchList->branch_code : null;

            $opening_date = new DateTime($RequestData['opening_date']);
            $RequestData['opening_date'] = $opening_date->format('Y-m-d');

            $RequestData['ob_no'] = $id;

            $customer_id_arr = (isset($RequestData['customer_id_arr']) ? $RequestData['customer_id_arr'] : array());
            $customer_name_arr = (isset($RequestData['customer_name_arr']) ? $RequestData['customer_name_arr'] : array());
            $customer_no_arr = (isset($RequestData['customer_no_arr']) ? $RequestData['customer_no_arr'] : array());
            $sale_amt_arr = (isset($RequestData['sale_amt_arr']) ? $RequestData['sale_amt_arr'] : array());
            $collection_amt_arr = (isset($RequestData['collection_amt_arr']) ? $RequestData['collection_amt_arr'] : array());
            $due_amt_arr = (isset($RequestData['due_amt_arr']) ? $RequestData['due_amt_arr'] : array());
            $inst_month_arr = (isset($RequestData['inst_month_arr']) ? $RequestData['inst_month_arr'] : array());
            $inst_amt_arr = (isset($RequestData['inst_amt_arr']) ? $RequestData['inst_amt_arr'] : array());
            $inst_type_arr = (isset($RequestData['inst_type_arr']) ? $RequestData['inst_type_arr'] : array());
            $sale_date_arr = (isset($RequestData['sale_date_arr']) ? $RequestData['sale_date_arr'] : array());
            $last_clln_date_arr = (isset($RequestData['last_clln_date_arr']) ? $RequestData['last_clln_date_arr'] : array());
            $product_arr = (isset($RequestData['product_arr']) ? $RequestData['product_arr'] : array());
            $sale_bill_no_arr = (isset($RequestData['sale_bill_no_arr']) ? $RequestData['sale_bill_no_arr'] : array());

            if (count(array_filter($customer_id_arr)) <= 0) {
                $notification = array(
                    'message' => 'Something went wrong! Please try again.',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

            DB::beginTransaction();

            try {
                $isUpdateM = $POBDueSaleDataM->update($RequestData);

                POBDueSaleDetails::where('ob_no', $id)->get()->each->delete();

                $IsInsertFlag = true;
                // $RequestData['ob_no'] =$RequestData['ob_no'];

                foreach ($customer_id_arr as $key => $customer_id_sin) {
                    if (!empty($customer_id_sin)) {
                        $RequestData['customer_id'] = $customer_id_sin;
                        // $RequestData['customer_name'] = $customer_name_arr[$key];
                        // $RequestData['customer_no'] = $customer_no_arr[$key];
                        $RequestData['sales_amount'] = $sale_amt_arr[$key];
                        $RequestData['collection_amount'] = $collection_amt_arr[$key];
                        $RequestData['due_amount'] = $due_amt_arr[$key];
                        $RequestData['installment_month'] = $inst_month_arr[$key];
                        $RequestData['installment_amount'] = $inst_amt_arr[$key];
                        $RequestData['installment_type'] = $inst_type_arr[$key];

                        if (strstr($sale_bill_no_arr[$key], 'SL')) {
                            $RequestData['sales_bill_no'] = $sale_bill_no_arr[$key];
                        } else {
                            $RequestData['sales_bill_no'] = 'SL' . sprintf('%04d', $branchCode) . $sale_bill_no_arr[$key];
                        }

                        $sales_date = new DateTime($sale_date_arr[$key]);
                        $RequestData['sales_date'] = $sales_date->format('Y-m-d');

                        $last_clln_date = new DateTime($last_clln_date_arr[$key]);
                        $RequestData['last_collection_date'] = $last_clln_date->format('Y-m-d');

                        // dd($customer_id_sin, $product_arr[$customer_id_sin]);

                        if (!empty($product_arr[$customer_id_sin])) {
                            $RequestData['sales_products'] = implode(',', $product_arr[$customer_id_sin]);
                        }

                        $isInsertD = POBDueSaleDetails::create($RequestData);
                    }
                }

                DB::commit();
                $notification = array(
                    'message' => 'Successfully updated',
                    'alert-type' => 'success',
                );
                return redirect('pos/customer_ob')->with($notification);
            } catch (\Exception $e) {
                DB::rollBack();

                $notification = array(
                    'message' => 'Unsuccessful to update data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $POBDueSaleDataD = DB::table('pos_ob_duesales_d as pobd')
                ->where('pobd.ob_no', $id)
                ->select('pobd.*', 'pc.customer_name', 'pc.customer_no')
                ->leftjoin('pos_customers as pc', function ($POBDueSaleDataD) {
                    $POBDueSaleDataD->on('pc.customer_no', 'pobd.customer_id');
                })
                ->get();

            $ProductData = Product::where([['is_delete', 0], ['is_active', 1]])->get();

            $cust_in_sales = SalesMaster::select('customer_id')->distinct()->get()->pluck('customer_id')->all();

            return view('POS.PCustomerOB.edit',
                compact('POBDueSaleDataM', 'POBDueSaleDataD', 'ProductData', 'cust_in_sales'));
        }
    }

    public function view($id = null)
    {
        $POBDueSaleDataM = POBDueSaleMaster::where(['ob_no' => $id, 'is_delete' => 0])->first();
        $POBDueSaleDataD = DB::table('pos_ob_duesales_d as pobd')
            ->where('pobd.ob_no', $id)
            ->select('pobd.*', 'pc.customer_name', 'pc.customer_no')
            ->leftjoin('pos_customers as pc', function ($POBDueSaleDataD) {
                $POBDueSaleDataD->on('pc.customer_no', 'pobd.customer_id');
            })
            ->get();

        $ProductData = Product::where([['is_delete', 0], ['is_active', 1]])->get();

        return view('POS.PCustomerOB.view',
            compact('POBDueSaleDataM', 'POBDueSaleDataD', 'ProductData'));
    }

    public function delete($id = null)
    {
        $POBDueSaleDataM = POBDueSaleMaster::where('ob_no', $id)->first();

        if ($POBDueSaleDataM->is_delete == 0) {

            $POBDueSaleDataM->is_delete = 1;
            $isSuccess = $POBDueSaleDataM->update();

            if ($isSuccess) {
                // POBDueSaleDetails::where('master_id', $id)->update(['is_delete' => 1]);
                $notification = array(
                    'message' => 'Successfully Deleted',
                    'alert-type' => 'success',
                );
                return redirect()->back()->with($notification);
            }
        }
    }

}
