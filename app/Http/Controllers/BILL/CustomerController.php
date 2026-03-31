<?php

namespace App\Http\Controllers\BILL;

use App\Http\Controllers\Controller;
use App\Model\GNL\Division;
use App\Model\BILL\Customer;
use App\Model\BILL\SalesMaster;
use App\Services\HrService as HRS;
use App\Services\CommonService as Common;
use DateTime;
use Illuminate\Http\Request;
use Redirect;
use App\Services\RoleService as Role;

class CustomerController extends Controller
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
                0 => 'bill_customers.id',
                1 => 'bill_customers.customer_name',
                2 => 'bill_customers.customer_code',
                // 3 => 'bill_customers.customer_type',
                3 => 'bill_customers.customer_mobile',
                4 => 'bill_customers.customer_email',
                5 => 'bill_customers.branch_name',
                6 => 'bill_customers.company_name',
            );

            // Datatable Pagination Variable
            $totalData = Customer::where('bill_customers.is_delete', '=', 0)
                ->whereIn('bill_customers.branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $BranchID = (empty($request->input('BranchID'))) ? null : $request->input('BranchID');
            // $Customercode = (empty($request->input('Customercode'))) ? null : $request->input('Customercode');
            // $CustomerName = (empty($request->input('CustomerName'))) ? null : $request->input('CustomerName');

            // Query
            $CustomerData = Customer::where(['bill_customers.is_delete' => 0, 'gnl_branchs.is_approve' => 1])
                ->select('bill_customers.*',
                    'gnl_branchs.branch_name as branch_name')
                    // , 'gnl_companies.comp_name as comp_name'
                    //,'bill_sales_d.sales_bill_no','bill_sales_m.sales_bill_no','bill_sales_m.customer_id'
                ->whereIn('bill_customers.branch_id', HRS::getUserAccesableBranchIds())
                ->leftJoin('gnl_branchs', 'bill_customers.branch_id', '=', 'gnl_branchs.id')
                // ->leftJoin('gnl_companies', 'bill_customers.company_id', '=', 'gnl_companies.id')
               //->leftJoin('bill_sales_d', 'bill_customers.id', '=', 'bill_sales_d.sales_bill_no')
               // ->leftJoin('bill_sales_m', 'bill_sales_m.customer_id', '=', 'bill_sales_d.sales_bill_no')
                ->where(function ($CustomerData) use ($search) {
                    if (!empty($search)) {
                        $CustomerData->where('bill_customers.customer_name', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_branchs.branch_name', 'LIKE', "%{$search}%")
                            // ->orWhere('gnl_companies.comp_name', 'LIKE', "%{$search}%")
                            ->orWhere('bill_customers.customer_code', 'LIKE', "%{$search}%");
                    }


                    // if (!empty($Customercode)) {
                    //     $CustomerData->where('bill_customers.customer_code', '=', $Customercode);
                    // }

                    // if (!empty($CustomerName)) {
                    //     $CustomerData->where('bill_customers.customer_name', '=', $CustomerName);
                    // }

                })
                ->where(function ($CustomerData) use ($BranchID) {


                    if (!empty($BranchID)) {
                        $CustomerData->where('bill_customers.branch_id', '=', $BranchID);
                    }


                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('bill_customers.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            if (!empty($search) || !empty($BranchID)) {
                $totalFiltered = count($CustomerData);
            }

            $DataSet = array();
            $i = 0;


            foreach ($CustomerData as $Row) {
              // dd($Row);
                $TempSet = array();
                $IgnoreArray = array();
                $salesMData = SalesMaster::where([['is_active', 1], ['is_delete', 0]])
                              ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                              ->where('customer_id','=',$Row->customer_no)
                              ->count();

                if($salesMData > 0){
                    $IgnoreArray = ['delete', 'edit'];
                }

                $CustomerText = ($Row->customer_type == 1) ? 'Cash' : 'Installment';
                $TempSet = [
                    'id' => ++$i,
                    'customer_name' => $Row->customer_name,
                    'customer_code' => $Row->customer_code,
                    // 'customer_type' => $CustomerText,
                    'customer_mobile' => $Row->customer_mobile,
                    'customer_email' => $Row->customer_email,
                    'branch_name' => $Row->branch_name,
                    // 'comp_name' => $Row->comp_name,
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->customer_no, $IgnoreArray)
                ];
                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $DataSet,
            );

            // dd(json_encode($json_data));

            echo json_encode($json_data);
            exit;
        } else {
            return view('BILL.Customer.index');
        }

    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'customer_name' => 'required',
                'customer_mobile' => 'required',
                'billing_name' => 'required',
                'customer_code' => 'required',
                'branch_id' => 'required',
                'customer_image' => 'mimes:jpeg,jpg,png,JPEG,JPG,PNG | max:500',
            ]);
            // $branchID = Common::getBranchId();
            $RequestData = $request->all();
            $RequestData['customer_dob'] = new DateTime($RequestData['customer_dob']); // used date time for d/m/y format
            $RequestData['customer_dob'] = $RequestData['customer_dob']->format('Y-m-d');
            $RequestData['customer_no'] = Common:: generateCustomerNo($RequestData['branch_id']);
            $CustomerType = $request->customer_type;

            $isInsert = Customer::create($RequestData);
            // dd($isInsert);

            $SuccessFlag = false;

            if ($isInsert) {
                $SuccessFlag = true;

                $lastInsertQuery = Customer::latest()->first();
                $tableName = $lastInsertQuery->getTable();
                $pid = $lastInsertQuery->customer_no;

                $image = $request->file('customer_image');

                if ($image != null) {

                    $FileType = $image->getMimeType();

                    if (($FileType != "image/jpeg") && ($FileType != "image/pjpeg") && ($FileType != "image/jpg") && ($FileType != "image/png")) {
                        $image = null;
                    } else {
                        $upload = Common::fileUpload($image, $tableName, $pid);

                        $lastInsertQuery->customer_image = $upload;
                        $isSuccess = $lastInsertQuery->update();

                        if ($isSuccess) {
                            $SuccessFlag = true;
                        } else {
                            $SuccessFlag = false;
                        }
                    }
                }
            }

            if ($SuccessFlag) {

                $notification = array(
                    'message' => 'Successfully Inserted',
                    'alert-type' => 'success',
                    'CustomerID' => $isInsert->id,
                );

                if ($CustomerType == 1) {
                    return Redirect::to('bill/customer')->with($notification);
                }
                else {
                    return Redirect::to('bill/customer/')->with($notification);
                }
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Insert',
                    'alert-type' => 'error',
                );
                return Redirect()->back()->with($notification);
            }

        } else {
            $DivData = Division::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('BILL.Customer.add', compact('DivData'));
        }
    }

    //  Customer Edit
    public function edit(Request $request, $id = null)
    {

        $CustomerData = Customer::where('customer_no', $id)->first();
        $tableName = $CustomerData->getTable();
        $pid = $id;

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'customer_name' => 'required',
                'customer_mobile' => 'required',
                'billing_name' => 'required',
                'customer_code' => 'required',
                // 'branch_id' => 'required',
                'customer_image' => 'mimes:jpeg,jpg,png,JPEG,JPG,PNG | max:500',
            ]);

            $Data = $request->all();
            $Data['customer_dob'] = new DateTime($Data['customer_dob']);
            $Data['customer_dob'] = $Data['customer_dob']->format('Y-m-d');

            $image = $request->file('customer_image');

            if ($image != null) {
                $FileType = $image->getMimeType();

                if (($FileType != "image/jpeg") && ($FileType != "image/pjpeg") && ($FileType != "image/jpg") && ($FileType != "image/png")) {
                    $image = null;
                } else {
                    $upload = Common::fileUpload($image, $tableName, $pid);
                    $Data['customer_image'] = $upload;
                }
            }

            $isUpdate = $CustomerData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Customer',
                    'alert-type' => 'success',
                );
                return Redirect::to('bill/customer')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Customer',
                    'alert-type' => 'error',
                );
                return Redirect()->back()->with($notification);
            }
        } else {

            $DivData = Division::where('is_delete', 0)->orderBy('id', 'DESC')->get();

            return view('BILL.Customer.edit', compact('CustomerData', 'DivData'));
        }

    }

    public function view($id = null)
    {
        $DivData = Division::where('is_delete', 0)->orderBy('id', 'DESC')->get();

        $CustomerData = Customer::where('customer_no', $id)->first();

        return view('BILL.Customer.view', compact('CustomerData', 'DivData'));
    }

    //  customer Delete
    public function delete($id = null)
    {
        $CustomerData = Customer::where('customer_no', $id)->first();
        $CustomerData->is_delete = 1;
        $delete = $CustomerData->save();

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
        $CustomerData = Customer::where('customer_no', $id)->first();
        if ($CustomerData->is_active == 1) {
            $CustomerData->is_active = 0;
        } else {
            $CustomerData->is_active = 1;
        }

        $Status = $CustomerData->save();

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
