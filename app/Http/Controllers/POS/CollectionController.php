<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\POS\Collection;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\PosService as POSS;
use App\Services\RoleService as Role;
use Auth;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;

class CollectionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    // Collection List
    public function index(Request $request)
    {

        if ($request->ajax()) {
            // Ordering Variable
            $columns = array(
                0 => 'col.id',
                1 => 'col.sales_bill_no',
                2 => 'cus.customer_name',
                3 => 'col.collection_date',
                4 => 'col.collection_amount',
                5 => 'col.payment_system_id',
                6 => 'emp.emp_name',
            );
            // Datatable Pagination Variable

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $SDate = (empty($request->input('SDate'))) ? null : $request->input('SDate');
            $EDate = (empty($request->input('EDate'))) ? null : $request->input('EDate');
            $BranchID = (empty($request->input('BranchID'))) ? null : $request->input('BranchID');
            $EmployeeID = (empty($request->input('EmployeeID'))) ? null : $request->input('EmployeeID');

            // Query
            $collectionData = Collection::from('pos_collections as col')
                ->where(['col.is_delete' => 0])
                ->whereIn('col.branch_id', HRS::getUserAccesableBranchIds())
                ->select('col.*', 'cus.customer_name', 'cus.customer_no', 'emp.emp_name', 'emp.emp_code', 'br.branch_name', 'br.branch_code')
                ->leftjoin('gnl_branchs as br', function ($collectionData) {
                    $collectionData->on('col.branch_id', '=', 'br.id')
                        ->where([['br.is_approve', 1], ['br.is_delete', 0], ['br.is_active', 1]]);
                })
                ->leftjoin('pos_customers as cus', function ($collectionData) {
                    $collectionData->on('col.customer_id', '=', 'cus.customer_no')
                        ->where([['cus.is_delete', 0], ['cus.is_active', 1]]);

                })
                ->leftjoin('hr_employees as emp', function ($collectionData) {
                    $collectionData->on('col.employee_id', '=', 'emp.employee_no')
                        ->where([['emp.is_delete', 0], ['emp.is_active', 1]]);

                })
                ->where(function ($collectionData) use ($search) {
                    if (!empty($search)) {
                        $collectionData->where('col.sales_bill_no', 'LIKE', "%{$search}%")
                            ->orWhere('col.collection_amount', 'LIKE', "%{$search}%")
                            ->orWhere('cus.customer_name', 'LIKE', "%{$search}%")
                            ->orWhere('br.branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('emp.emp_name', 'LIKE', "%{$search}%");
                    }
                })
                ->where(function ($collectionData) use ($BranchID) {
                    if (!empty($BranchID)) {
                        $collectionData->where('br.id', '=', $BranchID);
                    }
                })
                ->where(function ($collectionData) use ($SDate, $EDate) {
                    if (!empty($SDate) && !empty($EDate)) {

                        $SDate = new DateTime($SDate);
                        $SDate = $SDate->format('Y-m-d');

                        $EDate = new DateTime($EDate);
                        $EDate = $EDate->format('Y-m-d');

                        $collectionData->whereBetween('col.collection_date', [$SDate, $EDate]);
                    }
                })
                ->where(function ($collectionData) use ($EmployeeID) {
                    if (!empty($EmployeeID)) {
                        $collectionData->where('col.employee_id', '=', $EmployeeID);
                    }

                })

            // ->offset($start)
            // ->limit($limit)
                ->orderBy($order, $dir)
                ->orderBy('col.collection_date', 'DESC')
                ->orderBy('col.id', 'DESC');
            // ->get();

            $tempQueryData = clone $collectionData;
            $collectionData = $collectionData->offset($start)->limit($limit)->get();

            $totalData = Collection::where('is_delete', '=', 0)
                ->whereIn('pos_collections.branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;

            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($BranchID) || !empty($EmployeeID)) {
                $totalFiltered = $tempQueryData->count();
            }

            $DataSet = array();
            $i = $start;

            foreach ($collectionData as $Row) {
                $TempSet = array();

                $IgnoreArray = array();

                if (date('d-m-Y', strtotime($Row->collection_date)) != Common::systemCurrentDate($Row->branch_id, 'pos')) {
                    $IgnoreArray = ['delete', 'edit'];
                }

                $TempSet = [
                    'id' => ++$i,
                    'sales_bill_no' => $Row->sales_bill_no,
                    'branch_name' => (!empty($Row->branch_name)) ? $Row->branch_name . " (" . $Row->branch_code . ")" : "",
                    'customer_name' => (!empty($Row->customer_name)) ? $Row->customer_name . " (" . $Row->customer_no . ")" : "",
                    'collection_date' => date('d-m-Y', strtotime($Row->collection_date)),
                    'collection_amount' => $Row->collection_amount,
                    'payment_system' => ($Row->payment_system_id == 1) ? 'Cash' : 'Others',
                    'collection_by' => (!empty($Row->emp_name)) ? $Row->emp_name . " (" . $Row->emp_code . ")" : "",
                    'branch_name' => (!empty($Row->branch_name)) ? $Row->branch_name . " (" . $Row->branch_code . ")" : "",
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->collection_no, $IgnoreArray),
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

        } else {

            return view('POS.Collection.index');
        }
    }

    // Add And store collection Data
    public function add(Request $request)
    {
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'collection_date' => 'required',
                'sales_bill_no' => 'required',
                'collection_amount' => 'required',
            ]);

            $RequestData = $request->all();

            $collection_date = new DateTime($RequestData['collection_date']);
            $RequestData['collection_date'] = $collection_date->format('Y-m-d');

            // // Fiscal Year
            $fiscal_year = Common::systemFiscalYear($RequestData['collection_date'], $RequestData['company_id']);
            $RequestData['fiscal_year_id'] = $fiscal_year['id'];

            // Cash Price, Principle Amount, Installment profit Calculation
            $RequestData['cash_price'] = $RequestData['collection_amount'];
            $principal_amount = ($RequestData['collection_amount'] / (100 + $RequestData['installment_rate'])) * 100;
            $installment_profit = ($RequestData['collection_amount'] - $principal_amount);

            $RequestData['principal_amount'] = $principal_amount;
            $RequestData['installment_profit'] = $installment_profit;

            if (Collection::where('collection_no', '=', $RequestData['collection_no'])->exists()) {
                $RequestData['collection_no'] = POSS::generateCollectionNo(Common::getBranchId());
            }

            $isInsert = Collection::create($RequestData);

            if ($isInsert) {

                // ## Instalment Sales Complete Query
                $salesAmount = DB::table('pos_sales_m')
                    ->where([
                        ['sales_bill_no', $RequestData['sales_bill_no']],
                        ['is_active', 1],
                        ['is_delete', 0],
                        ['sales_type', 2],
                        ['is_complete', 0],
                        ['branch_id', $RequestData['branch_id']],
                    ])
                    ->sum('total_amount');

                $collectionAmount = DB::table('pos_collections')
                    ->where([
                        ['sales_bill_no', $RequestData['sales_bill_no']],
                        ['is_active', 1],
                        ['is_delete', 0],
                        ['sales_type', 2],
                        ['is_opening', 0],
                        ['branch_id', $RequestData['branch_id']],
                    ])
                    ->sum('collection_amount');

                if ($salesAmount == $collectionAmount) {
                    $updateSales = DB::table('pos_sales_m')
                        ->where([
                            ['sales_bill_no', $RequestData['sales_bill_no']],
                            ['is_active', 1],
                            ['is_delete', 0],
                            ['sales_type', 2],
                            ['is_complete', 0],
                            ['branch_id', $RequestData['branch_id']],
                        ])
                        ->update(['is_complete' => 1, 'complete_date' => $RequestData['collection_date']]);
                }

                ## End Sales Complete

                $notification = array(
                    'message' => 'Successfully Inserted in Collection',
                    'alert-type' => 'success',
                );

                return Redirect::to('pos/collection')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Collection',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            return view('POS.Collection.add');
        }
    }

    // Edit Collection Data
    public function edit(Request $request, $id = null)
    {
        $collectionData = Collection::where('collection_no', $id)->first();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'collection_date' => 'required',
                'sales_bill_no' => 'required',
                'collection_amount' => 'required',
            ]);

            $RequestData = $request->all();

            $collection_date = new DateTime($RequestData['collection_date']);
            $RequestData['collection_date'] = $collection_date->format('Y-m-d');

            // // Fiscal Year
            $fiscal_year = Common::systemFiscalYear($RequestData['collection_date'], $RequestData['company_id']);
            $RequestData['fiscal_year_id'] = $fiscal_year['id'];

            // Cash Price, Principle Amount, Installment profit Calculation
            $RequestData['cash_price'] = $RequestData['collection_amount'];
            $principal_amount = ($RequestData['collection_amount'] / (100 + $RequestData['installment_rate'])) * 100;
            $installment_profit = ($RequestData['collection_amount'] - $principal_amount);

            $RequestData['principal_amount'] = $principal_amount;
            $RequestData['installment_profit'] = $installment_profit;

            $isUpdate = $collectionData->update($RequestData);

            if ($isUpdate) {
                // ## Instalment Sales Complete Query
                $salesAmount = DB::table('pos_sales_m')
                    ->where([
                        ['sales_bill_no', $RequestData['sales_bill_no']],
                        ['is_active', 1],
                        ['is_delete', 0],
                        ['sales_type', 2],
                        ['is_complete', 0],
                        ['branch_id', $RequestData['branch_id']],
                    ])
                    ->sum('total_amount');

                $collectionAmount = DB::table('pos_collections')
                    ->where([
                        ['sales_bill_no', $RequestData['sales_bill_no']],
                        ['is_active', 1],
                        ['is_delete', 0],
                        ['sales_type', 2],
                        ['is_opening', 0],
                        ['branch_id', $RequestData['branch_id']],
                    ])
                    ->sum('collection_amount');

                if ($salesAmount == $collectionAmount) {
                    $updateSales = DB::table('pos_sales_m')
                        ->where([
                            ['sales_bill_no', $RequestData['sales_bill_no']],
                            ['is_active', 1],
                            ['is_delete', 0],
                            ['sales_type', 2],
                            ['is_complete', 0],
                            ['branch_id', $RequestData['branch_id']],
                        ])
                        ->update(['is_complete' => 1, 'complete_date' => $RequestData['collection_date']]);
                }

                ## End Sales Complete

                $notification = array(
                    'message' => 'Successfully Updated Collection Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/collection')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update Collection data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        }
        // $PgroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();

        return view('POS.Collection.edit', compact('collectionData'));
    }

    // View Collection Data
    public function view($id = null)
    {
        $collectionData = Collection::where('collection_no', $id)->first();
        return view('POS.Collection.view', compact('collectionData'));
    }

    // Delete Collection Data
    public function delete($id = null)
    {
        $collectionData = Collection::where('collection_no', $id)->first();
        $collectionData->is_delete = 1;
        $delete = $collectionData->save();

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

    // public function destroy($id = null)
    // {
    //     $collectionData = Collection::where(['id' => $id])->get()->each->delete();

    //     if ($collectionData) {
    //         $notification = array(
    //             'message' => 'Successfully Deleted',
    //             'alert-type' => 'success',
    //         );
    //     } else {
    //         $notification = array(
    //             'message' => 'Unsuccessful to Delete',
    //             'alert-type' => 'error',
    //         );
    //     }

    //     return redirect()->back()->with($notification);
    // }

    public function autoProcess(Request $request)
    {
        if ($request->isMethod('post')) {

            $RequestData = $request->all();

            $collection_date = new DateTime($RequestData['collection_date']);
            $RequestData['collection_date'] = $collection_date->format('Y-m-d');

            // // Fiscal Year
            $fiscal_year = Common::systemFiscalYear($RequestData['collection_date'], $RequestData['company_id']);
            $RequestData['fiscal_year_id'] = $fiscal_year['id'];

            $RequestData['payment_system_id'] = 1;
            $RequestData['employee_id'] = Auth::id();
            $RequestData['collection_no'] = POSS::generateCollectionNo(Common::getBranchId());

            $sales_id_arr = (isset($RequestData['sales_id_arr'])) ? $RequestData['sales_id_arr'] : array();
            $sales_bill_no_arr = (isset($RequestData['sales_bill_no_arr'])) ? $RequestData['sales_bill_no_arr'] : array();
            $customer_id_arr = (isset($RequestData['customer_id_arr'])) ? $RequestData['customer_id_arr'] : array();
            $installment_rate_arr = (isset($RequestData['installment_rate_arr'])) ? $RequestData['installment_rate_arr'] : array();
            $paid_amount_arr = (isset($RequestData['paid_amount_arr'])) ? $RequestData['paid_amount_arr'] : array();

            $IsInsertFlag = true;
            foreach ($sales_id_arr as $key => $sales_id) {

                if (!empty($sales_id)) {

                    $RequestData['sales_id'] = $sales_id;
                    $RequestData['sales_bill_no'] = $sales_bill_no_arr[$key];
                    $RequestData['collection_amount'] = $paid_amount_arr[$key];
                    $RequestData['customer_id'] = $customer_id_arr[$key];

                    // Cash Price, Principle Amount, Installment profit Calculation
                    $RequestData['cash_price'] = $RequestData['collection_amount'];
                    $principal_amount = ($RequestData['collection_amount'] / (100 + $installment_rate_arr[$key])) * 100;
                    $installment_profit = ($RequestData['collection_amount'] - $principal_amount);

                    $RequestData['principal_amount'] = $principal_amount;
                    $RequestData['installment_profit'] = $installment_profit;

                    $isInsert = Collection::create($RequestData);
                    if (!$isInsert) {
                        $IsInsertFlag = false;
                    } else {
                        // ## Instalment Sales Complete Query
                        $salesAmount = DB::table('pos_sales_m')
                            ->where([
                                ['sales_bill_no', $RequestData['sales_bill_no']],
                                ['is_active', 1],
                                ['is_delete', 0],
                                ['sales_type', 2],
                                ['is_complete', 0],
                                ['branch_id', $RequestData['branch_id']],
                            ])
                            ->sum('total_amount');

                        $collectionAmount = DB::table('pos_collections')
                            ->where([
                                ['sales_bill_no', $RequestData['sales_bill_no']],
                                ['is_active', 1],
                                ['is_delete', 0],
                                ['sales_type', 2],
                                ['is_opening', 0],
                                ['branch_id', $RequestData['branch_id']],
                            ])
                            ->sum('collection_amount');

                        if ($salesAmount == $collectionAmount) {
                            $updateSales = DB::table('pos_sales_m')
                                ->where([
                                    ['sales_bill_no', $RequestData['sales_bill_no']],
                                    ['is_active', 1],
                                    ['is_delete', 0],
                                    ['sales_type', 2],
                                    ['is_complete', 0],
                                    ['branch_id', $RequestData['branch_id']],
                                ])
                                ->update(['is_complete' => 1, 'complete_date' => $RequestData['collection_date']]);
                        }
                        ## End Sales Complete
                    }
                }
            }

            if ($IsInsertFlag) {

                $notification = array(
                    'message' => 'Successfully Inserted in Collection',
                    'alert-type' => 'success',
                );

            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Collection',
                    'alert-type' => 'error',
                );

            }

            return redirect()->back()->with($notification);
            // return Redirect::to('pos/collection')->with($notification);
        } else {

            // $customerData = DB::table('pos_sales_m as sM')
            //                 ->select('sM.sales_bill_no', 'sM.installment_amount', 'sM.total_amount', 'cust.customer_name', 'cust.customer_no')
            //                 ->leftjoin('pos_customers as cust', 'sM.customer_id', 'cust.customer_no')
            //                 ->where([['sM.is_active', 1], ['sM.is_delete', 0], ['cust.is_active', 1], ['cust.is_delete', 0]])
            //                 ->get();

            $instSalesData = DB::table('pos_sales_m as psm')
                ->select('psm.*', 'cus.customer_name', 'cus.customer_no')
                ->leftjoin('pos_customers as cus', function ($instSalesData) {
                    $instSalesData->on('cus.customer_no', '=', 'psm.customer_id');
                })
                ->where([
                    ['psm.is_active', 1],
                    ['psm.is_delete', 0],
                    ['psm.sales_type', 2],
                    ['psm.is_complete', 0],
                    ['psm.branch_id', Common::getBranchId()],
                ])
                ->get();

            return view('POS.Collection.auto_process', compact('instSalesData'));
        }
    }

    public function ajaxCollCustList(Request $request)
    {
        if ($request->ajax()) {

            $branchId = $request->branchId;
            $SelectedVal = $request->SelectedVal;

            // Query
            $QueryData = DB::table('pos_customers')
                ->where([['is_delete', 0], ['is_active', 1], ['branch_id', $branchId]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->select('id', 'customer_no', 'customer_name', 'customer_no')
                ->orderBy('customer_name', 'ASC')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $SelectText = '';

                if ($SelectedVal != null) {
                    if ($SelectedVal == $Row->customer_no) {
                        $SelectText = 'selected="selected"';
                    }
                }
                $output .= '<option value="' . $Row->customer_no . '" ' . $SelectText . '>' . $Row->customer_name . ' (' . $Row->customer_no . ')' . '</option>';
            }

            echo $output;
        }
    }

    public function ajaxCollEmpList(Request $request)
    {
        if ($request->ajax()) {

            $branchId = $request->branchId;
            $SelectedVal = $request->SelectedVal;

            // Query
            $QueryData = DB::table('hr_employees')
                ->where([['is_delete', 0], ['is_active', 1], ['branch_id', $branchId]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->select('id', 'employee_no', 'emp_name', 'emp_code')
                ->orderBy('emp_name', 'ASC')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $SelectText = '';

                if ($SelectedVal != null) {
                    if ($SelectedVal == $Row->employee_no) {
                        $SelectText = 'selected="selected"';
                    }
                }
                $output .= '<option value="' . $Row->employee_no . '" ' . $SelectText . '>' . $Row->emp_name . ' (' . $Row->emp_code . ')' . '</option>';
            }

            echo $output;
        }
    }

}
