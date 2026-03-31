<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use App\Model\GNL\SysUserHistory;
use App\Model\GNL\SysUserRole;
use Auth;
use Illuminate\Http\Request;
use Redirect;
use App\Services\RoleService as Role;
use App\Services\CommonService as Common;

class SysUserRoleController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index()
    {
        $userID = Common::getUserId();
        $roleID = Common::getRoleId();

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

        return view('GNL.SysUsrRole.index', compact('user_role'));
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {

            $request->validate([
                'role_name' => 'required',
            ]);

            $data = $request->all();
            $isCreate = SysUserRole::create($data);

            if ($isCreate) {
                $notification = array(
                    'message' => 'Successfully Inserted',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/sys_role')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Insert',
                    'alert-type' => 'error',
                );
                return Redirect()->back()->with($notification);
            }
        } else {

            $userID = Common::getUserId();
            $roleID = Common::getRoleId();

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

            $parent_role = SysUserRole::where([['is_delete', 0], ['is_active', 1]])
                ->whereIn('id', $data)
                ->get();

            return view('GNL.SysUsrRole.add', compact('parent_role', 'roleID'));
        }
    }

    public function edit(Request $request, $id = null)
    {
        $user_role = SysUserRole::where('id', $id)->first();
        if ($request->isMethod('post')) {

            $request->validate([
                'role_name' => 'required',
            ]);

            $data = $request->all();
            $isUpdate = $user_role->update($data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated',
                    'alert-type' => 'success',
                );
                return redirect('gnl/sys_role')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $userID = Common::getUserId();
            $roleID = Common::getRoleId();

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

            $parent_role = SysUserRole::where([['is_delete', 0], ['is_active', 1]])
                ->where('id', '<>', $user_role->id)
                ->whereIn('id', $data)
                ->get();

            return view('GNL.SysUsrRole.edit', compact('user_role', 'parent_role', 'roleID'));
        }
    }

    public function delete($id = null)
    {
        $sumenus = SysUserRole::where('id', $id)->first();
        if ($sumenus->is_delete == 0) {

            $sumenus->is_delete = 1;
            $isSuccess = $sumenus->update();

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
        // $module = SysUserRole::where('id', $id)->delete();
        $module = SysUserRole::where('id', $id)->get()->each->delete();

        if ($module) {
            $notification = array(
                'message' => 'Successfully Destory',
                'alert-type' => 'success',
            );
            return redirect()->back()->with($notification);
        }
    }

    public function isActive($id = null)
    {
        $sumenus = SysUserRole::where('id', $id)->first();

        if ($sumenus->is_active == 1) {
            $sumenus->is_active = 0;
        } else {
            $sumenus->is_active = 1;
        }

        $sumenus->update();
        $notification = array(
            'message' => 'user activation is changed',
            'alert-type' => 'success',
        );
        return redirect()->back()->with($notification);
    }

    // permission_assign begins--------------------------------->

    public function assignPermission(Request $request, $rid = null)
    {

        if ($request->isMethod('post')) {

            $RequestData = array();

            $request->module_arr = (!empty($request->module_arr)) ? $request->module_arr : array(0);
            $request->menu_arr = (!empty($request->menu_arr)) ? $request->menu_arr : array(0);
            $request->per_arr = (!empty($request->per_arr)) ? $request->per_arr : array(0);

            $RequestData['modules'] = implode(',', $request->module_arr);
            $RequestData['menus'] = implode(',', $request->menu_arr);
            $RequestData['permissions'] = implode(',', $request->per_arr);

            $RequestData['serialize_module'] = Role::prepareModuleArray($request->module_arr);
            $RequestData['serialize_menu'] = Role::prepareMenuArray($request->menu_arr);
            $RequestData['serialize_permission'] = Role::preparePermissionArray($request->per_arr);

            $isSuccess = SysUserRole::where('id', $rid)->update($RequestData);

            if ($isSuccess) {
                $notification = array(
                    'message' => 'Successfully Updated Permissions',
                    'alert-type' => 'success',
                );

                $session_remove = array();
                $session_dir = storage_path() . "\\framework\\sessions\\";

                $sessions = SysUserHistory::where([['is_active', 1], ['is_delete', 0], ['sys_user_role_id', $rid]])->pluck('session_key')->toArray();

                if ($sessions) {
                    foreach ($sessions as $key => $session) {
                        $sessions[$key] = $session_dir . $session;

                        if (file_exists($sessions[$key])) {
                            array_push($session_remove, $sessions[$key]);
                            SysUserHistory::where('session_key', $session)->update(['is_delete' => 1]);
                        }
                    }
                }

                if(!empty($session_remove)){
                    array_map('unlink', array_filter((array) array_merge($session_remove)));
                }

                return redirect('gnl/sys_role')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update Permissions',
                    'alert-type' => 'error',
                );
            }
        }

        $role_per = SysUserRole::where('id', $rid)->first();

        $role_name = $role_per->role_name;

        $modules = explode(',', $role_per->modules);
        $menus = explode(',', $role_per->menus);
        $permissions = explode(',', $role_per->permissions);

        return view('GNL.SysUsrRole.permission_assign', compact('role_name','rid', 'modules', 'menus', 'permissions'));
    }

}
