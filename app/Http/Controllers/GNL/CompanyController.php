<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use App\Model\GNL\Company;
use App\Model\GNL\Group;
use App\Services\CommonService as Common;
use Illuminate\Http\Request;
use Redirect;
use Auth;
use DB;

class CompanyController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index()
    {
        $CompanyData = Company::where(['is_delete' => 0])->orderBy('comp_code', 'ASC')->get();
        return view('GNL.Company.index', compact('CompanyData'));
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'group_id' => 'required',
                'comp_name' => 'required',
                'comp_code' => 'required',
                'fy_start_date' => 'required ',
                'comp_logo' => 'mimes:jpeg,jpg,png,JPEG,JPG,PNG | max:500',
            ]);

            $RequestData = $request->all();
            
            if(isset($RequestData['module_arr']) && !empty($RequestData['module_arr'])){
                $RequestData['module_arr'] = implode(',', $RequestData['module_arr']);
            }

            $RequestData['comp_logo'] = null;
            $isInsert = Company::create($RequestData);

            $SuccessFlag = false;

            if ($isInsert) {
                $SuccessFlag = true;

                $lastInsertQuery = Company::latest()->first();
                $tableName = $lastInsertQuery->getTable();
                $pid = $lastInsertQuery->id;

                $logo = $request->file('comp_logo');

                if ($logo != null) {

                    $FileType = $logo->getMimeType();

                    if (($FileType != "image/jpeg") 
                    && ($FileType != "image/pjpeg") 
                    && ($FileType != "image/jpg") 
                    && ($FileType != "image/png")) {
                        $logo = null;
                    } else {
                        $upload = Common::fileUpload($logo, $tableName, $pid);

                        $lastInsertQuery->comp_logo = $upload;
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
                return Redirect::to('gnl/company')->with($notification);
            }
            else{
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Company',
                    'alert-type' => 'error',
                );
                return Redirect()->back()->with($notification);
            }

        } else {

            $GroupData = Group::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('GNL.Company.add', compact('GroupData'));
        }
    }

    public function edit(Request $request, $id = null)
    {

        $CompanyData = Company::where('id', $id)->first();
        $tableName = $CompanyData->getTable();
        $pid = $id;

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'group_id' => 'required',
                'comp_name' => 'required',
                'comp_code' => 'required',
                'fy_start_date' => 'required ',
                'comp_logo' => 'mimes:jpeg,jpg,png,JPEG,JPG,PNG | max:500',
            ]);

            $Data = $request->all();

            if(isset($Data['module_arr']) && !empty($Data['module_arr'])){
                $Data['module_arr'] = implode(',', $Data['module_arr']);
            }

            $logo = $request->file('comp_logo');

            if ($logo != null) {
                $FileType = $logo->getMimeType();

                if (($FileType != "image/jpeg") 
                && ($FileType != "image/pjpeg") 
                && ($FileType != "image/jpg") 
                && ($FileType != "image/png")) {
                    $logo = null;
                } else {
                    $upload = Common::fileUpload($logo, $tableName, $pid);
                    $Data['comp_logo'] = $upload;
                }
            }

            $isUpdate = $CompanyData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Group Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/company')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Group',
                    'alert-type' => 'error',
                );
                return Redirect()->back()->with($notification);
            }
        } else {

            $CompanyData = Company::where('id', $id)->first();
            $GroupData = Group::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('GNL.Company.edit', compact('CompanyData', 'GroupData'));
        }
    }

    public function view($id = null)
    {
        $CompanyData = Company::where('id', $id)->first();
        $GroupData = Group::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('GNL.Company.view', compact('CompanyData', 'GroupData'));
    }

    public function delete($id = null)
    {
        $CompanyData = Company::where('id', $id)->first();
        $CompanyData->is_delete = 1;

        $delete = $CompanyData->save();

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
        $CompanyData = Company::where('id', $id)->first();

        if ($CompanyData->is_active == 1) {

            $CompanyData->is_active = 0;
            # code...
        } else {

            $CompanyData->is_active = 1;
        }

        $Status = $CompanyData->save();

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
