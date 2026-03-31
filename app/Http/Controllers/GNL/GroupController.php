<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use App\Model\GNL\Group;
use App\Services\CommonService as Common;
use Illuminate\Http\Request;
use Redirect;

class GroupController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index()
    {
        $GroupData = Group::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('GNL.Group.index', compact('GroupData'));
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'group_name' => 'required',
                'group_phone' => 'required',
                'group_logo' => 'mimes:jpeg,jpg,png,JPEG,JPG,PNG | max:500',
            ]);

            $RequestData = $request->all();
            $RequestData['group_logo'] = null;
            $isInsert = Group::create($RequestData);
            $SuccessFlag = false;

            if ($isInsert) {
                $SuccessFlag = true;
                $lastInsertQuery = Group::latest()->first();

                $tableName = $lastInsertQuery->getTable();
                $pid = $lastInsertQuery->id;

                $logo = $request->file('group_logo');

                if ($logo != null) {

                    $FileType = $logo->getMimeType();

                    if (($FileType != "image/jpeg") 
                    && ($FileType != "image/pjpeg") 
                    && ($FileType != "image/jpg") 
                    && ($FileType != "image/png")) {
                        $logo = null;
                    } else {
                        $upload = Common::fileUpload($logo, $tableName, $pid);

                        $lastInsertQuery->group_logo = $upload;
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
                );
                return Redirect::to('gnl/group')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Group',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

        } else {
            return view('GNL.Group.add');
        }

    }

    public function edit(Request $request, $id = null)
    {

        $GroupData = Group::where(['id' => $id, 'is_delete' => 0])->first();
        $tableName = $GroupData->getTable();
        $pid = $id;

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'group_name' => 'required',
                'group_phone' => 'required',
                'group_logo' => 'mimes:jpeg,jpg,png,JPEG,JPG,PNG | max:500',
            ]);

            $Data = $request->all();

            $logo = $request->file('group_logo');

            if ($logo != null) {
                $FileType = $logo->getMimeType();

                if (($FileType != "image/jpeg") 
                && ($FileType != "image/pjpeg") 
                && ($FileType != "image/jpg") 
                && ($FileType != "image/png")) {
                    $logo = null;
                } else {
                    $upload = Common::fileUpload($logo, $tableName, $pid);
                    $Data['group_logo'] = $upload;
                }
            }

            $isUpdate = $GroupData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Group Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/group')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Group',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        }

        return view('GNL.Group.edit', compact('GroupData'));
    }

    public function view($id = null)
    {
        $GroupData = Group::where('id', $id)->first();
        return view('GNL.Group.view', compact('GroupData'));
    }

    public function delete($id = null)
    {

        $GroupData = Group::where('id', $id)->first();

        $GroupData->is_delete = 1;
        $delete = $GroupData->save();
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
        $GroupData = Group::where('id', $id)->first();
        if ($GroupData->is_active == 1) {
            $GroupData->is_active = 0;
        } else {
            $GroupData->is_active = 1;
        }
        $Status = $GroupData->save();

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
