<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use App\Model\GNL\BranchDB;
use Illuminate\Http\Request;
use Redirect;

class BranchDBController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index()
    {
        $Branchtable = BranchDB::where(['is_active' => 1])->orderBy('id', 'ASC')->get();
        return view('GNL.BranchDB.index', compact('Branchtable'));
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([

                'table_name' => 'required',

            ]);

            $RequestData = $request->all();

            $isInsert = BranchDB::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted New Branch DB table',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/br_db')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Branch DB table',
                    'alert-type' => 'error',
                );
                return Redirect()->back()->with($notification);
            }

        } else {

            return view('GNL.BranchDB.add');
        }
    }

    public function edit(Request $request, $id = null)
    {
        // $Branchtable = BranchDB::where(['is_active' => 1])->orderBy('id', 'ASC')->get();
        $Branchtable = BranchDB::where('id', $id)->first();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([

                'table_name' => 'required',
            ]);

            $Data = $request->all();

            $isUpdate = $Branchtable->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Branch Db Table Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/br_db')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update Branch Db Table Data',
                    'alert-type' => 'error',
                );
                return Redirect()->back()->with($notification);
            }
        } else {

            // $BranchDBData = BranchDB::where('id', $id)->first();
            // $GroupData = Group::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('GNL.BranchDB.edit', compact('Branchtable'));
        }
    }

    public function view($id = null)
    {
        $Branchtable = BranchDB::where('id', $id)->first();

        return view('GNL.BranchDB.view', compact('Branchtable'));
    }

    public function delete($id = null)
    {
        $HOtable = BranchDB::where('id', $id)->get()->each->delete();

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
        $BranchDBData = BranchDB::where('id', $id)->first();

        if ($BranchDBData->is_active == 1) {

            $BranchDBData->is_active = 0;
            # code...
        } else {

            $BranchDBData->is_active = 1;
        }

        $Status = $BranchDBData->save();

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
    // public function delete($id = null)
    // {
    //     $BranchDBData = BranchDB::where('id', $id)->first();

    //     $BranchDBData->is_delete = 1;

    //     $delete = $BranchDBData->save();

    //     if ($delete) {
    //         $notification = array(
    //             'message' => 'Successfully Deleted',
    //             'alert-type' => 'success',
    //         );
    //         return redirect()->back()->with($notification);
    //     } else {
    //         $notification = array(
    //             'message' => 'Unsuccessful to Delete',
    //             'alert-type' => 'error',
    //         );
    //         return redirect()->back()->with($notification);
    //     }
    // }

}
