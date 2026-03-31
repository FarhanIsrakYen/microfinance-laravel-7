<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use App\Model\GNL\HOIG;
use Illuminate\Http\Request;
use Redirect;

class HOIGController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index()
    {
        $HOtable = HOIG::where(['is_active' => 1])->orderBy('id', 'ASC')->get();
        return view('GNL.HOIG.index', compact('HOtable'));
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'table_name' => 'required',
            ]);

            $RequestData = $request->all();
            $isInsert = HOIG::create($RequestData);
            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted New Head Office DB table',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/ho_db_ignore')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Head Office DB table',
                    'alert-type' => 'error',
                );
                return Redirect()->back()->with($notification);
            }
        } else {
            return view('GNL.HOIG.add');
        }
    }

    public function edit(Request $request, $id = null)
    {
        $HOtable = HOIG::where('id', $id)->first();
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'table_name' => 'required',
            ]);

            $Data = $request->all();
            $isUpdate = $HOtable->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Head Office Db Table Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/ho_db_ignore')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update Head Office Db Table Data',
                    'alert-type' => 'error',
                );
                return Redirect()->back()->with($notification);
            }
        } else {
            return view('GNL.HOIG.edit', compact('HOtable'));
        }
    }

    public function delete($id = null)
    {
        $HOtable = HOIG::where('id', $id)->get()->each->delete();

        if ($HOtable) {
            $notification = array(
                'message' => 'Successfully Destory',
                'alert-type' => 'success',
            );
            return redirect()->back()->with($notification);
        }
    }

    public function isApprove($id = null)
    {
        $HOIGData = HOIG::where('id', $id)->first();

        if ($HOIGData->is_active == 1) {
            $HOIGData->is_active = 0;
        } else {
            $HOIGData->is_active = 1;
        }

        $Status = $HOIGData->save();

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
