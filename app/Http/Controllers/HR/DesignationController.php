<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Services\CommonService as Common;
use Illuminate\Http\Request;
use Redirect;
use App\Model\HR\EmployeeDesignation;

class DesignationController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index()
    {
        $DesignationData = EmployeeDesignation::where([['is_active', 1], ['is_delete', 0]])
            ->orderBy('id', 'ASC')
            ->get();

        return view('HR.Designation.index', compact('DesignationData'));
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'name' => 'required',
            ]);

            $RequestData = $request->all();
           
            $isInsert = EmployeeDesignation::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted data !',
                    'alert-type' => 'success',
                );
                return Redirect::to('hr/designation')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data !',
                    'alert-type' => 'error',
                );
                return Redirect()->back()->with($notification);
            }
        } else {
            return view('HR.Designation.add');
        }
    }

    public function edit(Request $request, $id = null)
    {
        $DesignationData = EmployeeDesignation::where('id', $id)->first();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'name' => 'required',
            ]);

            $Data = $request->all();
           
            $isUpdate = $DesignationData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Data !',
                    'alert-type' => 'success',
                );
                return Redirect::to('hr/designation')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update Data',
                    'alert-type' => 'error',
                );
                return Redirect()->back()->with($notification);
            }
        } else {
            return view('HR.Designation.edit', compact('DesignationData'));
        }
    }

    public function view($id = null)
    {
        $DesignationData = EmployeeDesignation::where('id', $id)->first();
        return view('HR.Designation.view', compact('DesignationData'));
    }

    public function delete($id = null)
    {
        $DesignationData = EmployeeDesignation::where('id', $id)->first();
        $DesignationData->is_delete = 1;
        $delete = $DesignationData->save();

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
        $DesignationData = EmployeeDesignation::where('id', $id)->first();

        if ($DesignationData->is_active == 1) {
            $DesignationData->is_active = 0;
        } else {
            $DesignationData->is_active = 1;
        }

        $Status = $DesignationData->save();

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
