<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use App\Services\CommonService as Common;
use Illuminate\Http\Request;
use Redirect;
use App\Model\HR\EmpDepartment;

class DepartmentController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index()
    {
        $DepartmentData = EmpDepartment::where([['is_active', 1], ['is_delete', 0]])
            ->orderBy('id', 'ASC')
            ->get();

        return view('GNL.Department.index', compact('DepartmentData'));
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'dept_name' => 'required',
            ]);

            $RequestData = $request->all();
           
            $isInsert = EmpDepartment::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted data !',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/department')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data !',
                    'alert-type' => 'error',
                );
                return Redirect()->back()->with($notification);
            }
        } else {
            return view('GNL.Department.add');
        }
    }

    public function edit(Request $request, $id = null)
    {
        $DepartmentData = EmpDepartment::where('id', $id)->first();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'dept_name' => 'required',
            ]);

            $Data = $request->all();
           
            $isUpdate = $DepartmentData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Data !',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/department')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update Data',
                    'alert-type' => 'error',
                );
                return Redirect()->back()->with($notification);
            }
        } else {
            return view('GNL.Department.edit', compact('DepartmentData'));
        }
    }

    public function view($id = null)
    {
        $DepartmentData = EmpDepartment::where('id', $id)->first();
        return view('GNL.Department.view', compact('DepartmentData'));
    }

    public function delete($id = null)
    {
        $DepartmentData = EmpDepartment::where('id', $id)->first();
        $DepartmentData->is_delete = 1;
        $delete = $DepartmentData->save();

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

    public function isactive($id = null)
    {
        $DepartmentData = EmpDepartment::where('id', $id)->first();

        if ($DepartmentData->is_active == 1) {
            $DepartmentData->is_active = 0;
        } else {
            $DepartmentData->is_active = 1;
        }

        $Status = $DepartmentData->save();

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
