<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use App\Model\GNL\SysUsrMenus;
use App\Model\GNL\UserPermission;
use DB;
use Illuminate\Http\Request;
use Redirect;
use Route;

class SysUserMenusController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        // $this->middleware('permission');
        parent::__construct();
    }

    public function index()
    {
        $this->middleware('permission');

        $action = Route::currentRouteAction();
        // dd($action);
        $sysMenus = SysUsrMenus::where(['is_delete' => 0])
            ->orderBy('order_by', 'ASC')
//                ->orderBy('parent_menu_id', 'ASC')
            ->get();

        return view('GNL.SysUsrMenus.index', compact('sysMenus'));
    }

    public function add(Request $request)
    {
        $this->middleware('permission');

        if ($request->isMethod('post')) {

            $request->validate([
                'menu_name' => 'required',
//                'controller' => 'required',
                //                'action' => 'required',
            ]);

            $data = $request->all();
//            $data['menu_link'] = $data['controller'] . "/" . $data['action'];
            $isCreate = SysUsrMenus::create($data);

            if ($isCreate) {

                // $lastIns = $isCreate->id;

                $lastInsertQuery = SysUsrMenus::latest()->first();
                $lastIns = $lastInsertQuery->id;

                $this->autoAddPermission($lastIns, $isCreate->route_link);
                $notification = array(
                    'message' => 'Successfully Inserted',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/sys_menu')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Insert',
                    'alert-type' => 'error',
                );
                return Redirect()->back()->with($notification);
            }
        } else {
            $module = DB::table('gnl_sys_modules')
                ->where(['is_delete' => 0, 'is_active' => 1])
                ->get();
            $parent_menu = DB::table('gnl_sys_menus')
                ->where(['is_delete' => 0, 'is_active' => 1])
                ->get();
            return view('GNL.SysUsrMenus.add', compact('module', 'parent_menu'));
        }
    }

    public function edit(Request $request, $id = null)
    {
        $this->middleware('permission');

        $sumenus = SysUsrMenus::where('id', $id)->first();

        if ($request->isMethod('post')) {

            $request->validate([
                'menu_name' => 'required',
//                'controller' => 'required',
                //                'action' => 'required',
            ]);

            $data = $request->all();
//            $data['menu_link'] = $data['controller'] . "/" . $data['action'];
            $isUpdate = $sumenus->update($data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated',
                    'alert-type' => 'success',
                );
                return redirect('gnl/sys_menu')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $module = DB::table('gnl_sys_modules')
                ->where(['is_delete' => 0, 'is_active' => 1])
                ->get();
            $parent_menu = DB::table('gnl_sys_menus')
                ->where(['is_delete' => 0, 'is_active' => 1])
                ->get();
            return view('GNL.SysUsrMenus.edit', compact('module', 'sumenus', 'parent_menu'));
        }
    }

    public function delete($id = null)
    {
        $this->middleware('permission');

        $sumenus = SysUsrMenus::where('id', $id)->first();
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
        $this->middleware('permission');

        // $module = SysUsrMenus::where('id', $id)->delete();
        $module = SysUsrMenus::where('id', $id)->get()->each->delete();

        if ($module) {
            $notification = array(
                'message' => 'Successfully Destory',
                'alert-type' => 'success',
            );
            return redirect()->back()->with($notification);
        }
    }

    // public function view($id = null) {
    //     $sumenus = SysUsrMenus::findOrFail($id = null);
    //     return view('GNL.SysUser.view', compact('sumenus'));
    // }

    public function isActive($id = null)
    {
        $this->middleware('permission');

        $sumenus = SysUsrMenus::where('id', $id)->first();

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

    public function autoAddPermission($mid = null, $menu_link = null)
    {

        /**
         * SetStatus 1 = New
         * 2 = Edit
         * 3 = View
         * 4 = Publish(is_active)
         * 5 = Unpublish(is_active)
         * 6 = Delete
         * 7 = Approve
         * 8 = All Data
         * 9 = Change Password
         * 10 = Permission
         * 11 = Print
         * 12 = print pdf
         * 13 = Force Delete
         * 14 = Permission Folder
         */

        $DataAll = [
            '0' => [
                'name' => 'New Entry',
                'route_link' => $menu_link . '/add',
                'method_name' => 'add',
                'page_title' => 'Entry',
                'menu_id' => $mid,
                'order_by' => 1,
                'set_status' => 1,
            ],
            '1' => [
                'name' => 'Edit',
                'route_link' => $menu_link . '/edit',
                'method_name' => 'edit',
                'page_title' => 'Update',
                'menu_id' => $mid,
                'order_by' => 2,
                'set_status' => 2,
            ],
            '2' => [
                'name' => 'View',
                'route_link' => $menu_link . '/view',
                'method_name' => 'view',
                'page_title' => 'View',
                'menu_id' => $mid,
                'order_by' => 3,
                'set_status' => 3,
            ],
            '3' => [
                'name' => 'Delete',
                'route_link' => $menu_link . '/delete',
                'method_name' => 'delete',
                'menu_id' => $mid,
                'order_by' => 6,
                'set_status' => 6,
            ],
            '4' => [
                'name' => 'All Data',
                'route_link' => $menu_link,
                'method_name' => 'all_data',
                'menu_id' => $mid,
                'order_by' => 8,
                'set_status' => 8,
            ],
            '5' => [
                'name' => 'Force Delete',
                'route_link' => $menu_link . '/destroy',
                'method_name' => 'destroy',
                'menu_id' => $mid,
                'order_by' => 13,
                'set_status' => 13,
                'is_active' => 0,
            ],
            '6' => [
                'name' => 'Publish',
                'route_link' => $menu_link . '/isActive',
                'method_name' => 'isActive',
                'menu_id' => $mid,
                'order_by' => 14,
                'set_status' => 4,
                'is_active' => 0,
            ],
            '7' => [
                'name' => 'Unpublish',
                'route_link' => $menu_link . '/isActive',
                'method_name' => 'isActive',
                'menu_id' => $mid,
                'order_by' => 15,
                'set_status' => 5,
                'is_active' => 0,
            ],
        ];

        foreach ($DataAll as $Data) {
            $isCreate = UserPermission::create($Data);
        }
    }

    //permission method begins------------------------------>
    public function indexPermission($mid = null)
    {
        $upermission = UserPermission::where(['is_delete' => 0, 'menu_id' => $mid])->get();
        return view('GNL.SysUsrMenus.index_permission', compact('upermission', 'mid'));
    }

    public function addPermission(Request $request, $mid = null)
    {
        if ($request->isMethod('post')) {

            $request->validate([
                'set_status' => 'required',
                'name' => 'required',
            ]);

            $data = $request->all();
            $data['menu_id'] = $mid;

            $isCreate = UserPermission::create($data);
            if ($isCreate) {
                $notification = array(
                    'message' => 'Successfully Inserted',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/sys_permission/' . $mid)->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Insert',
                    'alert-type' => 'error',
                );
                return Redirect()->back()->with($notification);
            }
        } else {
            // $module = DB::table('gnl_sys_modules')->get();
            // $parent_menu = DB::table('gnl_sys_menus')->get();
            return view('GNL.SysUsrMenus.add_permission', compact('mid'));
        }
    }

    public function editPermission(Request $request, $mid = null, $id = null)
    {
        $upermission = UserPermission::where(['id' => $id, 'menu_id' => $mid])->first();

        if ($request->isMethod('post')) {

            $request->validate([
                'set_status' => 'required',
                'name' => 'required',
//                'method_name' => 'required',
            ]);

            $data = $request->all();
            $isUpdate = $upermission->update($data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated',
                    'alert-type' => 'success',
                );
                return redirect('gnl/sys_permission/' . $mid)->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            // $module = DB::table('gnl_sys_modules')->get();
            // $parent_menu = DB::table('gnl_sys_menus')->get();
            return view('GNL.SysUsrMenus.edit_permission', compact('upermission', 'mid'));
        }
    }

    public function deletePermission($mid = null, $id = null)
    {
        $permission = UserPermission::where(['menu_id' => $mid, 'id' => $id])->first();
        if ($permission->is_delete == 0) {

            $permission->is_delete = 1;
            $isSuccess = $permission->update();

            if ($isSuccess) {
                $notification = array(
                    'message' => 'Successfully Deleted',
                    'alert-type' => 'success',
                );
                return redirect()->back()->with($notification);
            }
        }
    }

    public function destroyPermission($mid = null, $id = null)
    {
        // $permission = UserPermission::where(['id' => $id, 'menu_id' => $mid])->delete();
        $permission = UserPermission::where(['id' => $id, 'menu_id' => $mid])->get()->each->delete();

        if ($permission) {
            $notification = array(
                'message' => 'Successfully Destory',
                'alert-type' => 'success',
            );
            return redirect()->back()->with($notification);
        }
    }

    public function isActivePermission($mid = null, $id = null)
    {
        $permission = UserPermission::where(['id' => $id, 'menu_id' => $mid])->first();

        if ($permission->is_active == 1) {
            $permission->is_active = 0;
        } else {
            $permission->is_active = 1;
        }

        $permission->update();
        $notification = array(
            'message' => 'user activation is changed',
            'alert-type' => 'success',
        );
        return redirect()->back()->with($notification);
    }

}
