<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use App\Model\GNL\SysUser;
use App\Model\GNL\SysUserRole;
use App\Model\HR\Employee;
use Auth;
use App\Services\CommonService as Common;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Redirect;
use App\Services\RoleService as Role;

class SysUserController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index()
    {
        $userInfo = Auth::user();
        $userID = $userInfo->id;
        $roleID = $userInfo->sys_user_role_id;

        $UserParent = SysUserRole::where([['is_delete', 0], ['is_active', 1]])
            ->where('id', $roleID)
            ->select('parent_id')
            ->first();

        $ParentId = $UserParent->parent_id;

        /////// Same Parent Data Role Wise
        $user_role = SysUserRole::where([['is_delete', 0], ['is_active', 1]])
            ->where(function ($user_role) use ($roleID, $ParentId) {
                $user_role->where('parent_id', $ParentId);

                if (!empty($ParentId)) {
                    $user_role->where('id', $roleID);
                }
            })
            ->orderBy('id', 'ASC')
            ->get();

        return view('GNL.SysUser.index', compact('user_role'));
    }

    public function old_index()
    {
        $UserInfo = Auth::user();

        $UserId = $UserInfo->id;
        $RoleID = $UserInfo->sys_user_role_id;

        // dd($RoleID);

        $user = DB::table('gnl_sys_users')
            ->leftJoin('gnl_sys_user_roles', 'gnl_sys_users.sys_user_role_id', 'gnl_sys_user_roles.id')
            ->leftJoin('gnl_companies', 'gnl_sys_users.company_id', 'gnl_companies.id')
            ->leftJoin('gnl_branchs', 'gnl_sys_users.branch_id', 'gnl_branchs.id')
            ->where(['gnl_sys_users.is_delete' => 0, 'gnl_sys_user_roles.is_delete' => 0])
            ->select('gnl_sys_users.*', 'gnl_sys_user_roles.role_name', 'gnl_companies.comp_name', 'gnl_branchs.branch_name')
            ->where(function ($user) use ($RoleID, $UserId) {
                if ($RoleID == 19) { // Developer
                    $user->where('gnl_sys_users.sys_user_role_id', '<>', 1);
                }

                if ($RoleID == 21) { // HO
                    $user->whereNotIn('gnl_sys_users.sys_user_role_id', [1, 19]);
                }

                if ($RoleID == 3 || $RoleID == 22) { // Employee // Branch
                    $user->where('gnl_sys_users.id', $UserId);
                }
            })
            ->orderBy('id', 'DESC')
            ->get();

        ////////////////////////////////////////////////////////////
        $userInfo = Auth::user();
        $userID = $userInfo->id;
        $roleID = $userInfo->sys_user_role_id;

        $UserParent = SysUserRole::where([['is_delete', 0], ['is_active', 1]])
            ->where('id', $roleID)
            ->select('parent_id')
            ->first();
        $ParentId = $UserParent->parent_id;

        $UserRoles = SysUserRole::where([['is_delete', 0], ['is_active', 1]])
            ->where(function ($UserRoles) use ($roleID, $ParentId) {
                $UserRoles->where('parent_id', $ParentId);

                if (!empty($ParentId)) {
                    $UserRoles->where('id', $roleID);
                }
            })
            ->orderBy('id', 'ASC')
            ->get();

        return view('GNL.SysUser.index', compact('user', 'UserRoles'));
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {

            $request->validate([
                'sys_user_role_id' => 'required',
                'full_name' => 'required',
                'username' => 'required',
                'password' => 'required',
                'user_image' => 'mimes:jpeg,jpg,png,JPEG,JPG,PNG | max:500',
                'signature_image' => 'mimes:jpeg,jpg,png,JPEG,JPG,PNG | max:500',
            ]);

            $data = $request->all();
            $data['password'] = Hash::make($data['password']);
            // $data['user_image'] = null;
            // $data['signature_image'] = null;
            $isCreate = SysUser::create($data);

            $SuccessFlag = false;

            if ($isCreate) {

                $SuccessFlag = true;
                $lastInsertQuery = SysUser::latest()->first();

                $tableName = $lastInsertQuery->getTable();
                $pid = $lastInsertQuery->id;

                $image = $request->file('user_image');
                $signature = $request->file('signature_image');

                if ($image != null) {

                    $FileType = $image->getMimeType();

                    if (($FileType != "image/jpeg") 
                    && ($FileType != "image/pjpeg") 
                    && ($FileType != "image/jpg") 
                    && ($FileType != "image/png")) {
                        $image = null;
                    } else {
                        $upload = Common::fileUpload($image, $tableName, $pid);
                        $lastInsertQuery->user_image = $upload;
                    }
                }

                if ($signature != null) {

                    $FileType = $signature->getMimeType();

                    if (($FileType != "image/jpeg") 
                    && ($FileType != "image/pjpeg") 
                    && ($FileType != "image/jpg") 
                    && ($FileType != "image/png")) {
                        $signature = null;
                    } else {
                        $upload = Common::fileUpload($signature, $tableName, $pid);
                        $lastInsertQuery->signature_image = $upload;
                    }
                }

                $isSuccess = $lastInsertQuery->update();
                if ($isSuccess) {
                    $SuccessFlag = true;
                } else {
                    $SuccessFlag = false;
                }
            }

            if ($SuccessFlag) {
                $notification = array(
                    'message' => 'Successfully Inserted',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/sys_user')->with($notification);
            }
            else{
                $notification = array(
                    'message' => 'Unsuccessful to Insert',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
            
        } else {
            $userInfo = Auth::user();
            $userID = $userInfo->id;
            $roleID = $userInfo->sys_user_role_id;

            $UserParent = SysUserRole::where([['is_delete', 0], ['is_active', 1]])
                ->where('id', $roleID)
                ->select('parent_id')
                ->first();
            $ParentId = $UserParent->parent_id;

            $UserRoles = SysUserRole::where([['is_delete', 0], ['is_active', 1]])
                ->where(function ($UserRoles) use ($roleID, $ParentId) {
                    $UserRoles->where('parent_id', $ParentId);

                    if (!empty($ParentId)) {
                        $UserRoles->where('id', $roleID);
                    }
                })
                ->orderBy('id', 'ASC')
                ->get();

            $ids = [];
            $data = array();
            foreach ($UserRoles as $UserRole) {
                $ids[$UserRole->id] = $UserRole->id;
                $data = array_merge($ids, Role::childRolesIds($UserRole->id));
            }

            $user_roles = SysUserRole::where([['is_delete', 0], ['is_active', 1]])
                ->whereIn('id', $data)
                ->get();

            $EmployeeData = Employee::where('is_delete', 0)->orderBy('emp_code', 'ASC')->get();

            return view('GNL.SysUser.add', compact('user_roles', 'EmployeeData', 'roleID'));
        }
    }

    public function edit(Request $request, $id = null)
    {
        $suser = SysUser::where('id', $id)->first();
        $tableName = $suser->getTable();
        $pid = $id;

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'sys_user_role_id' => 'required',
                'full_name' => 'required',
                'user_image' => 'mimes:jpeg,jpg,png,JPEG,JPG,PNG | max:500',
                'signature_image' => 'mimes:jpeg,jpg,png,JPEG,JPG,PNG | max:100',
            ]);

            $data = $request->all();
            $image = $request->file('user_image');
            $signature = $request->file('signature_image');

            if ($image != null) {
                $FileType = $image->getMimeType();

                if (($FileType != "image/jpeg") 
                && ($FileType != "image/pjpeg") 
                && ($FileType != "image/jpg") 
                && ($FileType != "image/png")) {
                    $image = null;
                } else {
                    $upload = Common::fileUpload($image, $tableName, $pid);
                    $data['user_image'] = $upload;
                }
            }

            if ($signature != null) {
                $FileType = $signature->getMimeType();

                if (($FileType != "image/jpeg") 
                && ($FileType != "image/pjpeg") 
                && ($FileType != "image/jpg") 
                && ($FileType != "image/png")) {
                    $signature = null;
                } else {
                    $upload = Common::fileUpload($signature, $tableName, $pid);
                    $data['signature_image'] = $upload;
                }
            }

            $isUpdate = $suser->update($data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated',
                    'alert-type' => 'success',
                );
                return redirect('gnl/sys_user')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            $userInfo = Auth::user();
            $userID = $userInfo->id;
            $roleID = $userInfo->sys_user_role_id;

            $UserParent = SysUserRole::where([['is_delete', 0], ['is_active', 1]])
                ->where('id', $roleID)
                ->select('parent_id')
                ->first();
            $ParentId = $UserParent->parent_id;

            $UserRoles = SysUserRole::where([['is_delete', 0], ['is_active', 1]])
                ->where(function ($UserRoles) use ($roleID, $ParentId) {
                    $UserRoles->where('parent_id', $ParentId);

                    if (!empty($ParentId)) {
                        $UserRoles->where('id', $roleID);
                    }
                })
                ->orderBy('id', 'ASC')
                ->get();

            $ids = [];
            $data = array();
            foreach ($UserRoles as $UserRole) {
                $ids[$UserRole->id] = $UserRole->id;
                $data = array_merge($ids, Role::childRolesIds($UserRole->id));
            }

            $user_roles = SysUserRole::where([['is_delete', 0], ['is_active', 1]])
                ->whereIn('id', $data)
                ->get();

            $EmployeeData = Employee::where('is_delete', 0)->orderBy('emp_code', 'ASC')->get();

            return view('GNL.SysUser.edit', compact('suser', 'user_roles', 'EmployeeData', 'roleID'));
        }
    }

    public function delete($id = null)
    {
        $suser = SysUser::where('id', $id)->first();
        if ($suser->is_delete == 0) {

            $suser->is_delete = 1;
            $isSuccess = $suser->update();

            if ($isSuccess) {
                $notification = array(
                    'message' => 'Successfully Deleted',
                    'alert-type' => 'success',
                );
                return redirect()->back()->with($notification);
            }
        }
    }

    public function destroy($id = null)
    {
        // $suser = SysUser::where('id', $id)->delete();
        $suser = SysUser::where('id', $id)->get()->each->delete();

        if ($suser) {
            $notification = array(
                'message' => 'Successfully Destory',
                'alert-type' => 'success',
            );
            return redirect()->back()->with($notification);
        }
    }

    public function view($id = null)
    {
        $suser = SysUser::findOrFail($id = null);

        $userRole = DB::table('gnl_sys_user_roles')->get();

        $EmployeeData = Employee::where('is_delete', 0)->orderBy('emp_code', 'ASC')->get();
        return view('GNL.SysUser.view', compact('suser', 'EmployeeData', 'userRole'));
    }

    public function isActive($id = null)
    {
        $suser = SysUser::where('id', $id)->first();

        if ($suser->is_active == 1) {
            $suser->is_active = 0;
        } else {
            $suser->is_active = 1;
        }

        $suser->update();
        $notification = array(
            'message' => 'user activation is changed',
            'alert-type' => 'success',
        );
        return redirect()->back()->with($notification);
    }

    public function changePassword(Request $request, $id = null)
    {
        $change = SysUser::where('id', $id)->first();

        if ($request->isMethod('post')) {
            $request->validate([
                'password' => 'required | same:conf_password',
                'conf_password' => 'required',
            ]);
            $data = Hash::make($request->password);
            $isUpdate = $change->update(['password' => $data]);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated',
                    'alert-type' => 'success',
                );
                return redirect('gnl/sys_user')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        }

        return view('GNL.SysUser.change_pass', compact('id'));
    }

}
