<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\GNL\SysUser;
use App\Model\HR\Employee;
use App\Services\HrService as HRS;
use App\Services\CommonService as Common;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Redirect;
use App\Services\RoleService as Role;

class EmployeeController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    // List of Employee
    public function index(Request $request)
    {
        if ($request->ajax()) {
            // Ordering Variable
            $columns = array(
                0 => 'hr_employees.id',
                1 => 'hr_employees.emp_name',
                2 => 'hr_employees.emp_code',
                3 => 'hr_employees.emp_phone',
                4 => 'hr_employees.emp_email',
                5 => 'hr_designations.name',
                6 => 'gnl_branchs.branch_name',
            );

            // Datatable Pagination Variable
            $totalData = Employee::where('hr_employees.is_delete', '=', 0)
                ->whereIn('hr_employees.branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $BranchID = (empty($request->input('BranchID'))) ? null : $request->input('BranchID');
            $EmpName = (empty($request->input('EmpName'))) ? null : $request->input('EmpName');
            $Empcode = (empty($request->input('Empcode'))) ? null : $request->input('Empcode');
            // Query

            $EmployeeData = Employee::where(['hr_employees.is_delete' => 0, 'gnl_branchs.is_approve' => 1])
                ->select('hr_employees.*',
                    'gnl_branchs.branch_name as branch_name', 'hr_designations.name as emp_designation')
                ->whereIn('hr_employees.branch_id', HRS::getUserAccesableBranchIds())
                ->leftJoin('gnl_branchs', 'hr_employees.branch_id', '=', 'gnl_branchs.id')
                ->leftJoin('hr_designations', 'hr_employees.designation_id', '=', 'hr_designations.id')
                ->where(function ($EmployeeData) use ($search, $BranchID, $EmpName, $Empcode) {

                    if (!empty($search)) {
                        $EmployeeData->where('hr_employees.emp_name', 'LIKE', "%{$search}%")
                            ->orWhere('hr_employees.emp_code', 'LIKE', "%{$search}%")
                            ->orWhere('hr_designations.name', 'LIKE', "%{$search}%");
                    }

                    if (!empty($BranchID)) {
                        $EmployeeData->where('hr_employees.branch_id', '=', $BranchID);
                    }

                    if (!empty($EmpName)) {
                        $EmployeeData->where('hr_employees.emp_name', '=', $EmpName);
                    }

                    if (!empty($Empcode)) {
                        $EmployeeData->where('hr_employees.emp_code', '=', $Empcode);
                    }

                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('hr_employees.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            if (!empty($search) || !empty($BranchID)) {
                $totalFiltered = count($EmployeeData);
            }

            $DataSet = array();
            $i = 0;

            foreach ($EmployeeData as $Row) {
                $TempSet = array();

                $TempSet = [
                    'id' => ++$i,
                    'emp_name' => $Row->emp_name,
                    'emp_code' => $Row->emp_code,
                    'emp_phone' => $Row->emp_phone,
                    'emp_email' => $Row->emp_email,
                    'emp_designation' => $Row->emp_designation,
                    'branch_name' => $Row->branch_name,
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->employee_no, [])
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
            $EmployeeData = Employee::where('is_delete', 0)
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->orderBy('emp_code', 'ASC')->get();
            return view('POS.Employee.index', compact('EmployeeData'));
        }
    }

    // Add and Store Employee
    public function add(Request $request)
    {
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'emp_name' => 'required',
                'username' => 'required',
                'emp_phone' => 'required',

            ]);

            $RequestData = $request->all();

            if (!empty($RequestData['emp_dob'])) {
                $RequestData['emp_dob'] = new DateTime($RequestData['emp_dob']);
                $RequestData['emp_dob'] = $RequestData['emp_dob']->format('Y-m-d');
            }

            $RequestData['employee_no'] = Common::generateEmployeeNo($RequestData['branch_id']);
            $isInsert = Employee::create($RequestData);

            $successFlag = true;

            if($isInsert){
                $userData = array(
                    'sys_user_role_id' => 3,
                    'full_name' => $RequestData['emp_name'],
                    'username' => $RequestData['username'],
                    'password' => Hash::make($RequestData['password']),
                    'email' => $RequestData['emp_email'],
                    'contact_no' => $RequestData['emp_phone'],
                    'branch_id' => $RequestData['branch_id'],
                    'company_id' => $RequestData['company_id'],
                    'employee_id' => $RequestData['employee_no'],
                );

                $isInsertUser = SysUser::create($userData);

                if($isInsertUser){
                    $lastInsertQuery = SysUser::latest()->first();
                    $pid = $lastInsertQuery->id;

                    $isSuccess =  Employee::where('employee_no', $RequestData['employee_no'])
                        ->update(['user_id' => $pid]);

                    if($isSuccess){
                        $successFlag = true;
                    }
                    else{
                        $successFlag = false;
                        $message = "Unsuccessful update in Employee.";
                    }
                }
                else{
                    $successFlag = false;
                    $message = "Unsuccessful to insert data in User.";
                }
            }
            else{
                $successFlag = false;
                $message = "Unsuccessful to insert data in Employee.";
            }

            if($successFlag){
                $notification = array(
                    'message' => 'Successfully Inserted New Employee Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/employee')->with($notification);
            }
            else{
                $notification = array(
                    'message' => $message,
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

        } else {
            return view('POS.Employee.add');
        }
    }

    // Edit Employee
    public function edit(Request $request, $id = null)
    {
        $EmployeeData = Employee::where('employee_no', $id)->first();

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'emp_name' => 'required',
                'emp_phone' => 'required',
            ]);

            $Data = $request->all();

            if (!empty($Data['emp_dob'])) {
                $Data['emp_dob'] = new DateTime($Data['emp_dob']);
                $Data['emp_dob'] = $Data['emp_dob']->format('Y-m-d');
            }

            $isUpdate = $EmployeeData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Employee Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/employee')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Employee',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            return view('POS.Employee.edit', compact('EmployeeData'));
        }
    }

    //View Employee
    public function view($id = null)
    {
        $EmployeeData = Employee::where('employee_no', $id)->first();
        return view('POS.Employee.view', compact('EmployeeData'));
    }

    // Soft Delete Employee
    public function delete($id = null)
    {
        $EmployeeData = Employee::where('employee_no', $id)->first();
        $EmployeeData->is_delete = 1;

        $delete = $EmployeeData->save();

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

    // Parmanent Delete Employee
    public function destroy($id = null)
    {
        $EmployeeData = Employee::where('employee_no', $id)->first();
        $delete = $EmployeeData->delete();

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

    // Publish/Unpublish Employee
    public function isActive($id = null)
    {
        $EmployeeData = Employee::where('employee_no', $id)->first();

        if ($EmployeeData->is_active == 1) {
            $EmployeeData->is_active = 0;
        } else {
            $EmployeeData->is_active = 1;
        }

        $Status = $EmployeeData->save();
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
