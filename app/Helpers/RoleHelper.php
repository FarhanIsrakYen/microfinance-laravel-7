<?php

namespace App\Helpers;

use Illuminate\Support\Facades\DB;
use Route;
use Session;
use URL;

class RoleHelper
{

    /**
     * prepareModuleArray for Modules Serialize
     * @Param Requested Module Array
     */
    public static function prepareModuleArray($RequestedModuleArr = [])
    {
        // Module Query
        $module_query = DB::table('gnl_sys_modules')
            ->select(['id', 'module_name as name', 'module_short_name as short_name',
                'module_icon as icon', 'route_link as module_link'])
            ->where(['is_active' => 1, 'is_delete' => 0])
            ->whereIn('id', $RequestedModuleArr)
            ->get();

        $ModuleSet = array();
        foreach ($module_query as $RowData) {
            $ModuleSet[] = (array) $RowData;
        }

        $ArraySerialize = base64_encode(serialize($ModuleSet));
        return $ArraySerialize;
    }

    /**
     * prepareMenuArray for Menus Serialize
     * @Param Requested Menu Array
     */
    public static function prepareMenuArray($RequestedMenuArr = [])
    {
        // Menus Query
        $menu_query = DB::table('gnl_sys_menus as mn')
            ->select(['mn.id', 'mn.parent_menu_id', 'mn.menu_name as name', 'mn.page_title', 'mn.menu_icon as icon', 'mn.route_link as menu_link',
                'mn.module_id', 'md.module_name', 'md.route_link as module_link'])
            ->leftjoin('gnl_sys_modules as md', 'md.id', '=', 'mn.module_id')
            ->where(['mn.is_active' => 1, 'mn.is_delete' => 0])
            ->whereIn('mn.id', $RequestedMenuArr)
            ->orderBy('mn.parent_menu_id', 'ASC')
            ->orderBy('mn.order_by', 'ASC')
            ->get();

        $menu_query_group_module_parent = $menu_query->groupBy(['module_id', 'parent_menu_id']);

        $MenuSet = array();
        foreach ($menu_query_group_module_parent as $ModuleID => $ParentMenuData) {
            /*
             * $ParentMenuData[0] is Root Menu List
             */
            $RootMenuData = $ParentMenuData[0];
            foreach ($RootMenuData as $RootMenu) {
                // $RootMenu->id . "-" .
                $MenuSet[$RootMenu->module_link][$RootMenu->menu_link] = (array) $RootMenu;
                $MenuSet[$RootMenu->module_link][$RootMenu->menu_link]['sub_menu'] = self::prepareSubMenuArray($RootMenu->id, $ParentMenuData);
            }
        }

        //dd($MenuSet);

        $ArraySerialize = base64_encode(serialize($MenuSet));
        return $ArraySerialize;
    }

    /**
     * prepareSubMenuArray for Sub Menu Serialize
     * @Param Requested Menu Array
     */
    public static function prepareSubMenuArray($ParentID = null, $ParentMenuArr = [])
    {
        $SubMenuSet = array();

        if (isset($ParentMenuArr[$ParentID])) {
            $SubMenuData = $ParentMenuArr[$ParentID];

            foreach ($SubMenuData as $SubMenu) {
                $TempArray = (array) $SubMenu;
                $TempArray['sub_menu'] = self::prepareSubMenuArray($SubMenu->id, $ParentMenuArr);

                $SubMenuSet[] = $TempArray;
            }
        }
        return $SubMenuSet;
    }

    /**
     * preparePermissionArray for Permission Serialize
     * @Param Requested Permission Array
     */
    public static function preparePermissionArray($RequestedPerArr = [])
    {
        // Permissions Query
        $permission_query = DB::table('gnl_user_permissions as p')
            ->select(['p.id', 'p.menu_id', 'p.name', 'p.set_status', 'p.route_link', 'p.order_by', 'p.page_title',
                'm.route_link as menu_link'])
            ->leftjoin('gnl_sys_menus as m', 'm.id', '=', 'p.menu_id')
            ->where(['p.is_active' => 1, 'p.is_delete' => 0])
            ->whereIn('p.id', $RequestedPerArr)
            ->orderBy('p.menu_id', 'ASC')
            ->orderBy('p.order_by', 'ASC')
            ->get();

        $PermissionSet = array();
        foreach ($permission_query as $RowData) {
            $PermissionSet[$RowData->menu_link][] = (array) $RowData;
        }

        $ArraySerialize = base64_encode(serialize($PermissionSet));
        return $ArraySerialize;
    }

    /* For Role Permission Assign Page Start */

    public static function moduleArray()
    {
        // Module Query
        $module_query = DB::table('gnl_sys_modules')
            ->select(['id as module_id', 'module_name'])
            ->where(['is_active' => 1, 'is_delete' => 0])
            ->get();

        $ModuleSet = array();
        foreach ($module_query as $RowData) {
            $ModuleSet[] = (array) $RowData;
        }
        return $ModuleSet;
    }

    public static function menuArray()
    {
        // Menus Query
        $menu_query = DB::table('gnl_sys_menus')
            ->select(['id as menu_id', 'parent_menu_id', 'menu_name', 'module_id', 'route_link'])
            ->where(['is_active' => 1, 'is_delete' => 0])
            ->orderBy('parent_menu_id', 'ASC')
            ->orderBy('order_by', 'ASC')
            ->get();

        $menu_query_group_module_parent = $menu_query->groupBy(['module_id', 'parent_menu_id']);

        $MenuSet = array();
        foreach ($menu_query_group_module_parent as $ModuleID => $ParentMenuData) {
            /*
             * $ParentMenuData[0] is Root Menu List
             */
            $RootMenuData = $ParentMenuData[0];
            foreach ($RootMenuData as $RootMenu) {
                $MenuSet[$ModuleID][$RootMenu->menu_id . "-" . $RootMenu->route_link] = (array) $RootMenu;
                $MenuSet[$ModuleID][$RootMenu->menu_id . "-" . $RootMenu->route_link]['sub_menu'] = self::subMenuArray($RootMenu->menu_id, $ParentMenuData);
            }
        }
        return $MenuSet;
    }

    public static function subMenuArray($ParentID = null, $ParentMenuArr = [])
    {
        $SubMenuSet = array();

        if (isset($ParentMenuArr[$ParentID])) {
            $SubMenuData = $ParentMenuArr[$ParentID];

            foreach ($SubMenuData as $SubMenu) {
                $TempArray = (array) $SubMenu;
                $TempArray['sub_menu'] = self::subMenuArray($SubMenu->menu_id, $ParentMenuArr);

                $SubMenuSet[] = $TempArray;
            }
        }
        return $SubMenuSet;
    }

    public static function permissionArray()
    {
        // Permissions Query
        $permission_query = DB::table('gnl_user_permissions')
            ->select(['id as per_id', 'menu_id', 'name as per_name', 'set_status', 'route_link'])
            ->where(['is_active' => 1, 'is_delete' => 0])
            ->orderBy('menu_id', 'ASC')
            ->orderBy('order_by', 'ASC')
            ->get();

        $permission_query_group_menu = $permission_query->groupBy(['menu_id']);

        $PermissionSet = array();
        foreach ($permission_query_group_menu as $MenuID => $PermissionData) {
            foreach ($PermissionData as $RowData) {
                $PermissionSet[$MenuID][] = (array) $RowData;
            }
        }
        return $PermissionSet;
    }

    public static function subMenuPermissionLoad($ParentModuleID = null, $ParentMenuID = null, $SubMenuMenuArr = [], $PermissionArray = [], $SelectedMenus = [], $SelectedPermissions = [])
    {

        $html = '<hr>';
        $html .= '<ul>';

        if (count($SubMenuMenuArr) > 0) {
            $html .= '<label class="submenus" id="menu_arr_' . $ParentModuleID . '_' . $ParentMenuID . '_sub_lvl">';
            foreach ($SubMenuMenuArr as $SubMenuData) {
                $html .= '<li class="list-unstyled menus">';
                $html .= '<div class="checkbox-custom checkbox-primary menuscheck">';

                $SubMCheckedText = (in_array($SubMenuData['menu_id'], $SelectedMenus)) ? "checked" : "";

                $Text = "'module_arr_" . $ParentModuleID . "_check'";

                $html .= '<input type="checkbox" class="menusCheckbox" name="menu_arr[]"
                                id="menu_arr_' . $ParentModuleID . '_' . $SubMenuData['menu_id'] . '"
                                value="' . $SubMenuData['menu_id'] . '" ' . $SubMCheckedText . '
                                onclick="fnPermissionLoad(this.id, ' . $Text . ');">';

                $html .= '<label for="menu_arr_' . $ParentModuleID . '_' . $SubMenuData['menu_id'] . '">';
                $html .= '<b>' . $SubMenuData['menu_name'] . '</b>';
                $html .= '</label>';
                $html .= '</div>';

                //  Sub Menu & Permission View calling
                $html .= self::subMenuPermissionLoad($ParentModuleID, $SubMenuData['menu_id'], $SubMenuData['sub_menu'], $PermissionArray, $SelectedMenus, $SelectedPermissions);

                $html .= '</li>';
            }
            $html .= '</label>';

        } else {
            /* permission  */
            $Menu_Permissions = (isset($PermissionArray[$ParentMenuID])) ? $PermissionArray[$ParentMenuID] : array();

            $html .= '<label class="permissions" id="menu_arr_' . $ParentModuleID . '_' . $ParentMenuID . '_per_lvl">';
            foreach ($Menu_Permissions as $PerData) {

                $html .= '<li class="list-inline-item mr-4">';
                $html .= '<div class="checkbox-custom checkbox-primary" id="permissionsID">';
                $PerCheckedText = (in_array($PerData["per_id"], $SelectedPermissions)) ? "checked" : "";

                $html .= '<input type="checkbox" class="PerCheckClass"
                            id="per_arr_' . $ParentModuleID . '_' . $ParentMenuID . '_' . $PerData["per_id"] . '" name="per_arr[]"
                            value="' . $PerData['per_id'] . '" ' . $PerCheckedText . ' >';

                $html .= '<label for="per_arr_' . $ParentModuleID . '_' . $ParentMenuID . '_' . $PerData["per_id"] . '">';
                $html .= $PerData['per_name'];
                $html .= '</label>';
                $html .= '</div>';
                $html .= '</li>';

            }
            $html .= '</label>';

        }

        $html .= "</ul>";

        return $html;
    }

    /* For Role Permission Assign Page End */

    public static function roleWisePermission($CurrentMenuPers = [], $RowID = null, $ignoreAction = [], $IsActive = null, $IsApproved = null)
    {
        $data_link = '';

        foreach ($CurrentMenuPers as $RowData) {

            $SetStatus = $RowData['set_status'];
            $ActionLink = $RowData['route_link'];
            $ActionName = $RowData['name'];

            /**
             * SetStatus 1 = Add
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
             * 15 = Day End / Month End
             */

            if ($SetStatus == 2 && !in_array('edit', $ignoreAction)) { // Edit
                // url('gnl/sumenus/active/'.$menus->id)
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnEdit">';
                $data_link .= '<i class="icon wb-edit mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';

            }

            if ($SetStatus == 3 && !in_array('view', $ignoreAction)) { // View
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnView">';
                $data_link .= '<i class="icon wb-eye mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($SetStatus == 6 && !in_array('delete', $ignoreAction)) { // delete
                $data_link .= '<a href="javascript:void(0)" onclick="fnDelete(' . $RowID . ');" title="' . $ActionName . '" class="btnDelete">';
                $data_link .= '<i class="icon wb-trash mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($IsActive == 1) {
                if ($SetStatus == 5 && !in_array('isActive', $ignoreAction)) { // Unpublish
                    $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnUnpublish">';
                    $data_link .= '<i class="icon fa-check-square-o mr-2 blue-grey-600"></i>';
                    $data_link .= '</a>';
                }
            } else {
                if ($SetStatus == 4 && !in_array('isActive', $ignoreAction)) { // Publish
                    $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnPublish">';
                    $data_link .= '<i class="icon fa-square-o mr-2 blue-grey-600"></i>';
                    $data_link .= '</a>';
                }
            }

            if ($IsApproved == 0) {
                if ($SetStatus == 7) { // Approve
                    $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnApprove">';
                    $data_link .= '<i class="icon fa fa-check-square mr-2 blue-grey-600" style="font-size: 18px;"></i>';
                    $data_link .= '</a>';
                }
            }

            if ($SetStatus == 9) { // Change Password
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnChangePassword">';
                $data_link .= '<i class="icon fa fa-exchange mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($SetStatus == 10) { // Permission
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnPermission">';
                $data_link .= '<i class="icon fa fa-qrcode mr-2 blue-grey-600"></i>';
                // <i class="icon wb-grid-4 mr-2 blue-grey-600"></i>
                $data_link .= '</a>';
            }

            if ($SetStatus == 11) { // print
                $data_link .= '<a href="javascript:void(0)" onClick="window.print()" title="' . $ActionName . '" class="btnPrint">';
                $data_link .= '<i class="icon fa fa-print mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($SetStatus == 12) { // print pdf
                $data_link .= '<a href="javascript:void(0)" onClick="window.print()" title="' . $ActionName . '" class="btnPrintPDF">';
                $data_link .= '<i class="icon fa fa-file-pdf-o mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($SetStatus == 13 && !in_array('destroy', $ignoreAction)) { // Force Delete
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnForceDelete">';
                $data_link .= '<i class="icon wb-scissor mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($SetStatus == 14) { // Permission Folder
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnPermissionFolder">';
                $data_link .= '<i class="icon icon wb-folder mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

        }

        // dd($data_link);
        return $data_link;
    }

    public static function roleWiseArray($CurrentMenuPers = [], $RowID = null, $ignoreAction = [], $IsActive = null, $IsApproved = null) {
        $data_link = array();

        $SetStatus_arr = array();
        $actionName_arr = array();
        $actionLink_arr = array();

        foreach ($CurrentMenuPers as $RowData) {

            $SetStatus = $RowData['set_status'];
            $actionLink = URL::to($RowData['route_link'] . '/' . $RowID);
            $actionName = $RowData['name'];

            $actionFlag = false;

            /**
             * SetStatus 1 = Add
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
             * 15 = Day End / Month End
             */

            if ($SetStatus == 2 && !in_array('edit', $ignoreAction)) { // Edit
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $actionLink);
            }

            if ($SetStatus == 3 && !in_array('view', $ignoreAction)) { // View
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $actionLink);
            }

            if ($SetStatus == 6 && !in_array('delete', $ignoreAction)) { // delete
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $RowID);
            }

            if ($IsActive == 1) {
                if ($SetStatus == 5 && !in_array('isActive', $ignoreAction)) { // Unpublish
                    array_push($SetStatus_arr, $SetStatus);
                    array_push($actionName_arr, $actionName);
                    array_push($actionLink_arr, $actionLink);
                }
            } else {
                if ($SetStatus == 4 && !in_array('isActive', $ignoreAction)) { // Publish
                    array_push($SetStatus_arr, $SetStatus);
                    array_push($actionName_arr, $actionName);
                    array_push($actionLink_arr, $actionLink);
                }
            }

            if ($IsApproved == 0) {
                if ($SetStatus == 7) { // Approve
                    array_push($SetStatus_arr, $SetStatus);
                    array_push($actionName_arr, $actionName);
                    array_push($actionLink_arr, $actionLink);
                }
            }

            if ($SetStatus == 9) { // Change Password
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $actionLink);
            }

            if ($SetStatus == 10) { // Permission
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $actionLink);
            }

            if ($SetStatus == 11) { // print
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $RowID);
            }

            if ($SetStatus == 12) { // print pdf
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $RowID);
            }

            if ($SetStatus == 13 && !in_array('destroy', $ignoreAction)) { // Force Delete
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $actionLink);
            }

            if ($SetStatus == 14) { // Permission Folder
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $actionLink);
            }
        }

        $data_link = [
            'set_status' => implode(',', $SetStatus_arr),
            'action_name' => implode(',', $actionName_arr),
            'action_link' => implode(',', $actionLink_arr),
        ];

        return $data_link;
    }

    // echo $this->Link->action($this->GlobalRole,$UserMenus->id,$UserMenus->is_active,$UserMenus->name);
    public static function ajaxRoleWisePermission($RowID = null, $RouteLink = null,
        $IsApproved = null, $IsActive = null) {
        $route = Route::current();
        // $name = Route::currentRouteName();
        // $action = Route::currentRouteAction();
        $CurrentRouteURI = $route->uri();
        $CurrentMenuRoute = (!empty($RouteLink)) ? $RouteLink : $CurrentRouteURI;

        $RolePermissionAll = (!empty(Session::get('LoginBy.user_role.role_permission'))) ? Session::get('LoginBy.user_role.role_permission') : array();
        $CurrentMenuPers = (isset($RolePermissionAll[$CurrentMenuRoute])) ? $RolePermissionAll[$CurrentMenuRoute] : array();

        $data_link = '';

        // dd($CurrentMenuPers);

        foreach ($CurrentMenuPers as $RowData) {

            $SetStatus = $RowData['set_status'];
            $ActionLink = $RowData['route_link'];
            $ActionName = $RowData['name'];

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

            if ($SetStatus == 2) { // Edit
                // url('gnl/sumenus/active/'.$menus->id)
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnEdit">';
                $data_link .= '<i class="icon wb-edit mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';

            }

            if ($SetStatus == 3) { // View
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnView">';
                $data_link .= '<i class="icon wb-eye mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($SetStatus == 6) { // delete
                $data_link .= '<a href="javascript:void(0)" onclick="fnDelete(' . $RowID . ');" title="' . $ActionName . '" class="btnDelete">';
                $data_link .= '<i class="icon wb-trash mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($IsActive == 1) {
                if ($SetStatus == 5) { // Unpublish
                    $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnUnpublish">';
                    $data_link .= '<i class="icon fa-check-square-o mr-2 blue-grey-600"></i>';
                    $data_link .= '</a>';
                }
            } else {
                if ($SetStatus == 4) { // Publish
                    $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnPublish">';
                    $data_link .= '<i class="icon fa-square-o mr-2 blue-grey-600"></i>';
                    $data_link .= '</a>';
                }
            }

            if ($IsApproved == 0) {
                if ($SetStatus == 7) { // Approve
                    $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnApprove">';
                    $data_link .= '<i class="icon fa fa-check-square mr-2 blue-grey-600" style="font-size: 18px;"></i>';
                    $data_link .= '</a>';
                }
            }

            if ($SetStatus == 9) { // Change Password
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnChangePassword">';
                $data_link .= '<i class="icon fa fa-exchange mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($SetStatus == 10) { // Permission
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnPermission">';
                $data_link .= '<i class="icon fa fa-qrcode mr-2 blue-grey-600"></i>';
                // <i class="icon wb-grid-4 mr-2 blue-grey-600"></i>
                $data_link .= '</a>';
            }

            if ($SetStatus == 11) { // print
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnPrint">';
                $data_link .= '<i class="icon fa fa-print mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($SetStatus == 12) { // print pdf
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnPrintPDF">';
                $data_link .= '<i class="icon fa fa-file-pdf-o mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($SetStatus == 13) { // Force Delete
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnForceDelete">';
                $data_link .= '<i class="icon wb-scissor mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($SetStatus == 14) { // Permission Folder
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnPermissionFolder">';
                $data_link .= '<i class="icon icon wb-folder mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

        }

        // dd($data_link);
        return $data_link;
    }

    /* Role Helper */

//    public function roleMenusIds($parent = null) {
    //        $this->UserRoles = TableRegistry::get('UserRoles');
    //        $data = $this->UserRoles->find('all')->where(['parent_id' => $parent])->order(['orderby' => 'ASC']);
    //        $ids = [];
    //        foreach ($data as $menus) {
    //            $ids[$menus['id']] = $menus['id'];
    //            $child = $this->roleMenusIds($menus['id']);
    //            $ids = array_merge($ids, $child);
    //
    //        }
    //       // debug($ids);
    //        return $ids;
    //    }
    //
    //    /* ---------------------------------- role menu  start------------------------------------------- */
    //
    //    public function roleMenus($parent = null, $global = NULL) {
    //        $this->UserRoles = TableRegistry::get('UserRoles');
    //        $data = $this->UserRoles->find('all')->where(['parent_id' => $parent])->order(['orderby' => 'ASC']);
    //        $menu_data = array();
    //        foreach ($data as $menus) :
    //            $menu_data[] = array(
    //                'id' => $menus['id'],
    //                'name' => $menus['name'],
    //                'is_active' => $menus['is_active'],
    //                'sub_menu' => $this->roleMenus($menus['id']),
    //            );
    //
    //        endforeach;
    //        return $menu_data;
    //    }
    //
    //    public function roleSubMenu($data = NULL, $id = NULL, $permission = NULL) {
    //        if ($data == NULL)
    //            return;
    //        $html = "";
    //        $html.='<ul class="dropdown-menu2 sub-menu">';
    //        foreach ($data as $value) {
    //            if (!empty($value['sub_menu'])) {
    //                $mylink = 'trigger right-caret';
    //            } else {
    //                $mylink = NULL;
    //            }
    //            $html .= '<li>';
    //            $html .= $this->Html->link(
    //                    $value['name'], ['action' => 'index', $value['id']], ['escapeTitle' => false, 'title' => $value['name'], 'class' => $mylink . ' my-link']
    //            );
    //
    //            $subchild = $this->roleSubMenu($value['sub_menu'], $id, $permission);
    //            if ($subchild != '') {
    //                $html .= $subchild;
    //            }
    //            $html .= '<table><tr><td width="85%">' . $value['name'] . '</td><td width="15%">';
    //            $html .= $this->Link->action($permission, $value['id'], $value['is_active'], $value['name']);
    //            $html .= '</td></tr></table>';
    //            $html .= "</li>";
    //        }
    //        $html.='</ul>';
    //        return $html;
    //    }
    //
    //    public function roleGetDataMenu($parent = "0", $globalMenu = NULL, $permission = NULL) {
    //        $globalMenu = $globalMenu;
    //        if (!empty($globalMenu)) {
    //            $html = '';
    //            foreach ($globalMenu as $value) {
    //                if (!empty($value['sub_menu'])) {
    //                    $mylink = 'trigger right-caret';
    //                } else {
    //                    $mylink = NULL;
    //                }
    //                $html .= '<li>';
    //                $html .= $this->Html->link(
    //                        $value['name'], ['action' => 'index', $value['id']], ['escapeTitle' => false, 'title' => $value['name'], 'class' => $mylink . ' my-link']
    //                );
    //                $html .= $this->roleSubMenu($value['sub_menu'], $value['id'], $permission);
    //                $html .= '<table><tr><td width="85%">' . $value['name'] . '</td><td width="15%">';
    //                $html .= $this->Link->action($permission, $value['id'], $value['is_active'], $value['name']);
    //                $html .= '</td></tr></table>';
    //                $html .='</li>';
    //            }
    //
    //            $globalMenu = $html;
    //        }
    //
    //
    //        return $globalMenu;
    //    }
    //
    //    /* ---------------------------------- role menu  start------------------------------------------- */
    //
    //    public function roleSubMenuUser($data = NULL, $id = NULL, $permission = NULL) {
    //        $this->SysUsers = TableRegistry::get('SysUsers');
    //        if ($data == NULL)
    //            return;
    //        $html = "";
    //        $html.='<ul class="dropdown-menu2 sub-menu">';
    //        foreach ($data as $value) {
    //            if (!empty($value['sub_menu'])) {
    //                $mylink = 'trigger right-caret';
    //            } else {
    //                $mylink = NULL;
    //            }
    //            $html .= '<li>';
    //            $html .= $this->Html->link(
    //                    $value['name'], ['action' => 'index', $value['id']], ['escapeTitle' => false, 'title' => $value['name'], 'class' => $mylink . ' my-link']
    //            );
    //
    //            $Users = $this->SysUsers->find()->where(['sys_user_role_id' => $value['id']])->all();
    //           // $Users = $this->Link->ViewAll('SysUsers', [,'is_active' => 0]);
    //            $subchild = $this->roleSubMenuUser($value['sub_menu'], $id, $permission);
    //            if ($subchild != '') {
    //                $html .= $subchild;
    //            }
    //            $html .= '<table width="100%"><tr>';
    //            $html .= '<th width="10%">Images</th>';
    //            $html .= '<th width="35%">Username</th>';
    //            $html .= '<th width="35%">Email</th>';
    //            $html .= '<th width="20%">Actions</th></tr>';
    //            if (!empty($Users)) {
    //                foreach ($Users as $User) {
    //                    $html .= '<tr>';
    //                    $html .= '<td>' . $this->Html->image("/api/Common/getImage/sys_users/32X32/" . $User->id . '/' . $User->thumb_url, ["alt" => "user img ", "width" => "32px"]) . '</td>';
    //                    $html .= '<td>' . $User->username . '</td>';
    //                    $html .= '<td>' . $User->email . '</td>';
    //                    $html .= '<td>';
    //                    $html .= $this->Link->action($permission, $User->id, $User->is_active, $User->username);
    //                    $html .= '</td></tr>';
    //                }
    //            }
    //            $html .='</table>';
    //
    //
    //
    //            $html .= "</li>";
    //        }
    //        $html.='</ul>';
    //        return $html;
    //    }
    //
    //    public function roleGetDataMenuUser($parent = "0", $globalMenu = NULL, $permission = NULL) {
    //        $this->SysUsers = TableRegistry::get('SysUsers');
    //        $globalMenu = $globalMenu;
    //        if (!empty($globalMenu)) {
    //            $html = '';
    //            foreach ($globalMenu as $value) {
    //                if (!empty($value['sub_menu'])) {
    //                    $mylink = 'trigger right-caret';
    //                } else {
    //                    $mylink = NULL;
    //                }
    //                $html .= '<li class="user_role">';
    //                $html .= $this->Html->link(
    //                        $value['name'], ['action' => 'index', $value['id']], ['escapeTitle' => false, 'title' => $value['name'], 'class' => $mylink . ' my-link']
    //                );
    //
    //                $Users = $this->SysUsers->find()->where(['sys_user_role_id' => $value['id']])->all();
    ////                debug($globalMenu); exit;
    //                $html .= $this->roleSubMenuUser($value['sub_menu'], $value['id'], $permission);
    //                $html .= '<table class="table table-hover"><thead>';
    //                $html .= '<th width="10%">Images</th>';
    //                $html .= '<th width="30%">Username</th>';
    //                $html .= '<th width="30%">Email</th>';
    //                $html .= '<th width="20%">Actions</th></thead>';
    //                if (!empty($Users)) {
    //                    foreach ($Users as $User) {
    //                        $html .= '<tbody>';
    //                        $html .= '<tr>';
    //                        $html .= '<td>' . $this->Html->image("/api/Common/getImage/sys_users/32X32/" . $User->id . '/' . $User->thumb_url, ["alt" => "user img ", "width" => "32px"]) . '</td>';
    //                        $html .= '<td>' . $User->username . '</td>';
    //                        $html .= '<td>' . $User->email . '</td>';
    //                        $html .= '<td  class="action_default">';
    //                        $html .= $this->Link->action($permission, $User->id, $User->is_active, $User->username);
    //                        $html .= '</td></tr>';
    //                        $html .= '</tbody>';
    //                    }
    //                }
    //                $html .='</table>';
    //                $html .='</li>';
    //            }
    //
    //            $globalMenu = $html;
    //        }
    //
    //
    //        return $globalMenu;
    //    }

    /* Root view */

//    public function initialize() {
    //        // parent::initialize();
    //        $this->loadHelper('Link');
    //        $this->loadHelper('Roles');
    //        $this->loadHelper('CakephpJqueryFileUpload.JqueryFileUpload');
    //
    //        $sesstion = $this->request->session()->read('Auth.User.username');
    //        $this->g_action = '';
    //        $this->g_controller = '';
    //        if (!empty($sesstion)) :
    //            $action = $this->request->params['action'];
    //            $controller = $this->request->params['controller'];
    //            $role = $this->request->session()->read('loginby.config.role');
    //            $data = array(1 => ['setstatus' => 5000]);
    //
    //            if($action == 'add' || $action == 'edit' || $action == 'isactive' || $action == 'view'){
    //               $action = "index";
    //            }
    //
    //            $link = $controller . '_' . $action;
    ////            $link = $controller;
    //            if (!empty($role[$controller . '/' . $action])) {
    //                $data = $role[$controller . '/' . $action];
    //            }
    //            $this->GlobalRole = $data;
    //            $this->linkId = $link;
    //            $this->g_action = $action;
    //            $this->g_controller = $controller;
    //        endif;
    //
    //        if (!empty($sesstion)) :
    //            $this->GlobalRoleMenu = $this->Link->getdatamenu();
    //        endif;
    //    }
}
