<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Model\GNL\SysUser;
use App\Model\HR\Employee;
use App\Model\HR\EmployeeTransfer;
use App\Services\CommonService as Common;
use App\Services\HrService;
use App\Services\RoleService as Role;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Validator;

class EmployeeTransferController extends Controller
{

    public function __construct()
    {
        // $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $req)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        if (!$req->ajax()) {
            $branchces = DB::table('gnl_branchs')
                ->where([
                    ['is_delete', 0],
                    ['id', '>', 1],
                ])
                ->whereIn('id', $accessAbleBranchIds)
                ->orderBy('branch_code')
                ->select(DB::raw("id, CONCAT(branch_code, ' - ', branch_name) AS name"))
                ->get();

            $data = array(
                'branchces' => $branchces,
            );

            return view('HR.EmployeeTransfer.index', $data);
        }

        $columns = [
            'emp_code',
            'employeeName',
            'branchFrom',
            'branchTo',
            'transferDate',
            'status',
            'action',
        ];

        $limit            = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $employeeTransfers = DB::table('hr_employee_transfer as het')
            ->where('het.is_delete', 0)
            ->select('het.id', 'het.employee_no as employeeNo', 'he.emp_code', 'he.emp_name as employeeName', 'gbfrom.branch_name as branchFrom', 'gbto.branch_name as branchTo', 'het.transfer_date', 'het.is_approved')
            ->leftjoin('hr_employees as he', 'het.employee_no', 'he.employee_no')
            ->leftjoin('gnl_branchs as gbfrom', 'het.branch_from', 'gbfrom.id')
            ->leftjoin('gnl_branchs as gbto', 'het.branch_to', 'gbto.id')
            ->where(function ($query) use ($search) {
                $query->where('het.employee_no', 'LIKE', "%{$search}%")
                    ->orWhere('gbfrom.branch_name', 'LIKE', "%{$search}%")
                    ->orWhere('he.emp_name', 'LIKE', "%{$search}%")
                    ->orWhere('gbto.branch_name', 'LIKE', "%{$search}%");
            })
            ->orderBy('het.id', 'DESC')
            ->orderBy($order, $dir)
            ->limit($limit)
            ->offset($req->start)
            ->get();

        $totalData = $employeeTransfers->count();
        $sl        = (int)$req->start + 1;

        foreach ($employeeTransfers as $key => $row) {

            $status = '<span class="text-primary">Approved</span>';
            if ($row->is_approved == 0) {
                $status = '<a type="button" class="btn btn-danger btn-sm" href="javascript:void(0)" onClick="fnApprove(' . $row->id . ')"><i class="fad fa-info-circle"></i> Approve</a>';
            }

            $employeeTransfers[$key]->sl           = $sl++;
            $employeeTransfers[$key]->transferDate = Carbon::parse($row->transfer_date)->format('d-m-Y');
            $employeeTransfers[$key]->status       = $status;
            // $employeeTransfers[$key]->action       = encrypt($row->id);
            $employeeTransfers[$key]->action = Role::roleWiseArray($this->GlobalRole, $row->id);
        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $employeeTransfers,
        );

        return response()->json($data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $sysDate   = Common::systemCurrentDate(Common::getBranchId());
        $companyId = Common::getCompanyId();

        $employees = DB::table('hr_employees')
            ->where('is_delete', 0)
            ->select('employee_no', DB::raw('CONCAT(emp_code, " - ", emp_name) AS employee'))
            ->get();

        $data = array(
            'employees' => $employees,
            'sysDate'   => $sysDate,
            'companyId' => $companyId,
        );

        return view('HR.EmployeeTransfer.add', $data);
    }

    public function store(Request $req)
    {
        $passport = $this->getValidationPass($req, $operationType = 'store');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $req['transfer_date'] = Carbon::parse($req->transfer_date)->format('Y-m-d');
        $req['created_at']    = now();
        $req['created_by']    = Auth::user()->id;

        $isCreate = EmployeeTransfer::create($req->all());



        if ($isCreate) {
            $notification = array(
                'message'    => 'Successfully Inserted',
                'alert-type' => 'success',
            );
        } else {
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
            );
        }

        return response()->json($notification);
    }

    public function edit(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->update($req);
        }

        $sysDate   = Common::systemCurrentDate(Common::getBranchId());
        $companyId = Common::getCompanyId();

        $employees = DB::table('hr_employees')
            ->where('is_delete', 0)
            ->select('employee_no', DB::raw('CONCAT(emp_code, " - ", emp_name) AS employee'))
            ->get();

        $employeeTransfers = EmployeeTransfer::find($req->id);

        $data = array(
            'employees'     => $employees,
            'sysDate'       => $sysDate,
            'companyId'     => $companyId,
            'employee_no'   => $employeeTransfers->employee_no,
            'branch_from'   => $employeeTransfers->branch_from,
            'branch_to'     => $employeeTransfers->branch_to,
            'transfer_date' => $employeeTransfers->transfer_date,
        );

        return view('HR.EmployeeTransfer.edit', $data);
    }

    public function update(Request $req)
    {
        $employeeTransfers = EmployeeTransfer::find($req->id);
        $passport          = $this->getValidationPass($req, $operationType = 'update', $employeeTransfers);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $req['transfer_date'] = Carbon::parse($req->transfer_date)->format('Y-m-d');
        $req['updated_at']    = now();
        $req['updated_by']    = Auth::user()->id;
        $isUpdated            = $employeeTransfers->update($req->all());

        if ($isUpdated) {
            $notification = array(
                'message'    => 'Successfully Updated.',
                'alert-type' => 'success',
            );
        } else {
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
            );
        }

        return response()->json($notification);
    }

    public function delete(Request $req)
    {
        $employeeTransfers = EmployeeTransfer::find($req->id);
        $passport          = $this->getValidationPass($req, $operationType = 'delete', $employeeTransfers);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return redirect()->back()->with($notification);
        }

        $isDeleted = $employeeTransfers->update(['is_delete' => 1]);

        if ($isDeleted) {
            $notification = array(
                'message'    => 'Successfully Deleted.',
                'alert-type' => 'success',
            );
        } else {
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
                // 'consoleMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );
        }

        return redirect()->back()->with($notification);
    }

    public function view($id)
    {
        $employeeTransfers = DB::table('hr_employee_transfer as het')
            ->where('het.id', $id)
            ->select('het.id', 'het.employee_no', 'he.emp_code', 'he.emp_name as employeeName', 'gbfrom.branch_name as branchFrom', 'gbto.branch_name as branchTo', 'het.transfer_date', 'het.is_approved', 'gsuCr.full_name as created_by')
            ->leftjoin('hr_employees as he', 'het.employee_no', 'he.employee_no')
            ->leftjoin('gnl_branchs as gbfrom', 'het.branch_from', 'gbfrom.id')
            ->leftjoin('gnl_branchs as gbto', 'het.branch_to', 'gbto.id')
            ->leftjoin('gnl_sys_users as gsuCr', 'het.created_by', 'gsuCr.id')
            ->first();

        $data = array(
            'empCode'   => $employeeTransfers->emp_code,
            'employeeName' => $employeeTransfers->employeeName,
            'branchFrom'   => $employeeTransfers->branchFrom,
            'branchTo'     => $employeeTransfers->branchTo,
            'transferDate' => Carbon::parse($employeeTransfers->transfer_date)->format('d-m-Y'),
            'isApproved'   => $employeeTransfers->is_approved == 1 ? 'Approved' : 'Pending',
            'approvedBy'   => $employeeTransfers->is_approved != 1 ? "Not Approved" : '',
            'createdBy'    => $employeeTransfers->created_by,
        );

        return view('HR.EmployeeTransfer.view', $data);
    }

    public function approve(Request $req)
    {
        DB::beginTransaction();
        try {
            $employeeTransfers = EmployeeTransfer::find($req->id);
            $employeeTransfers->update([
                'is_approved' => 1,
                'approved_by' => Auth::user()->id,
            ]);

            $employee = Employee::where('employee_no', $employeeTransfers->employee_no)->first();
            $employee->update(['branch_id' => $employeeTransfers->branch_to]);

            $systemUser = SysUser::where('employee_id', $employeeTransfers->employee_no)->first();
            $systemUser->update(['branch_id' => $employeeTransfers->branch_to]);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
                'consoleMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );
        }

        DB::commit();
        $notification = array(
            'message'    => 'Successfully Approved.',
            'alert-type' => 'success',
        );

        return response()->json($notification);
    }

    public function getData(Request $req)
    {
        if ($req->context == 'branchFrom') {

            $employeeBranch = DB::table('hr_employees')
                ->where('employee_no', $req->employeeId)
                ->value('branch_id');

            $data = array(
                'employeeBranch' => $employeeBranch,
            );
        }

        return response()->json($data);
    }

    public function getValidationPass($req, $operationType, $transfer = null)
    {
        $errorMsg = null;

        if ($operationType == 'store') {

            $validator = Validator::make($req->all(), array(
                'employee_no'   => 'required',
                'branch_from'   => 'required',
                'branch_to'     => 'required',
                'transfer_date' => 'required',
            ));

            $attributes = array(
                'employee_no'   => 'Employee No',
                'branch_from'   => 'Branch From',
                'branch_to'     => 'Branch To',
                'transfer_date' => 'Transfer Date',
            );

            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        if ($operationType != 'delete') {
            if ($errorMsg == null) {
                if ($req->branch_from == $req->branch_to) {
                    $errorMsg = "Branch From & Branch To can't be same";
                }
            }

            if ($errorMsg == null) {

                $employee = DB::table('hr_employees')
                    ->where('employee_no', $req->employee_no)
                    ->select('id', 'branch_id')
                    ->first();

                $employeeBranch = DB::table('gnl_branchs')
                    ->where('id', $employee->branch_id)
                    ->selectRaw('CONCAT(branch_code, " - ", branch_name) AS branch')
                    ->value('branch');

                if ($req->branch_from != $employee->branch_id) {
                    $errorMsg = "Employee branch from must be " . $employeeBranch;
                }

                // if microfinance module is active then check this employee is assigned to any samity or not
                // if assigned then it could not be continued
                $isMfnModuleActive = DB::table('gnl_sys_modules')->where('id', 5)->first()->is_active;
                if ($isMfnModuleActive) {
                    $isAssingedToSamity = DB::table('mfn_samity')
                        ->where([
                            ['is_delete', 0],
                            ['fieldOfficerEmpId', $employee->id],
                        ])
                        ->exists();

                    if ($isAssingedToSamity) {
                        $errorMsg = 'This employee is assigned to samity as Credit Officer.';
                    }
                }
            }
        }

        if ($operationType == 'delete') {
            if ($transfer->is_approved == 1) {
                $errorMsg = "Already approved. So, you can't delete this.";
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid'  => $isValid,
            'errorMsg' => $errorMsg,
        );

        return $passport;
    }
}
