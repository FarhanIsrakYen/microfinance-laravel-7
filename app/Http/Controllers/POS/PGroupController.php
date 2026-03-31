<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\POS\PGroup;
use Illuminate\Http\Request;
use Redirect;

class PGroupController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
        $PGroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('POS.Group.index', compact('PGroupData'));
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'group_name' => 'required',
            ]);

            $RequestData = $request->all();

            $isInsert = PGroup::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted Product Group Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/group')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert Product Group data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            return view('POS.Group.add');
        }

    }

    public function edit(Request $request, $id = null)
    {

        $PGroupData = PGroup::where('id', $id)->first();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'group_name' => 'required',
            ]);

            $Data = $request->all();

            $isUpdate = $PGroupData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Product Group Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/group')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update Product Group data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            return view('POS.Group.edit', compact('PGroupData'));
        }
    }

    public function view($id = null)
    {
        $PGroupData = PGroup::where('id', $id)->first();
        return view('POS.Group.view', compact('PGroupData'));
    }

    public function delete($id = null)
    {
        $PGroupData = PGroup::where('id', $id)->first();
        $PGroupData->is_delete = 1;

        $delete = $PGroupData->save();

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
        $PGroupData = PGroup::where('id', $id)->first();
        if ($PGroupData->is_active == 1) {
            $PGroupData->is_active = 0;
        } else {
            $PGroupData->is_active = 1;
        }

        $Status = $PGroupData->save();

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
