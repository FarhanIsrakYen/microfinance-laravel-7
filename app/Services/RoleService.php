<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Route;
use Session;
use URL;
use Auth;
use App\Services\CommonService as Common;

class RoleService
{

    public static function getRoleId($userID = null)
    {
        $userInfo = Auth::user();
        $roleID = $userInfo->sys_user_role_id;

        if (!empty($userID)) {
            $userInfo = DB::table('gnl_sys_users')->where('id', $userID)->first();
            $roleID = ($userInfo) ? $userInfo->sys_user_role_id : null;
        }

        return $roleID;
    }

    public static function checkDataPrmissionForRoleWise($roleid = null)
    {
        // // 1 Super User
        if(self::getRoleId() == 1){
            return true;
        }
        
        if(in_array($roleid, self::childRolesIds(self::getRoleId())) == false){
            return false;
        }
        else {
            return true;
        }
    }

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
        $companyID = Common::getCompanyId();
        $comModules = array();

        $roleID = Common::getRoleId();
        $roleModules = array();

        ///////////////
        if(Common::isSuperUser() != true){
            $companyData = DB::table('gnl_companies')
                            ->where([['is_active', 1], ['is_delete', 0],
                            ['id', $companyID]])
                            ->first();
            $comModules = ($companyData) ? explode(',', $companyData->module_arr) : array();

            
            $roleData = DB::table('gnl_sys_user_roles')
                            ->where([['is_active', 1], ['is_delete', 0],
                            ['id', $roleID]])
                            ->select(['modules'])
                            ->first();

            $roleModules = ($roleData) ? explode(',', $roleData->modules) : array();
            
        }
        ///////////////
        
        // Module Query
        $module_query = DB::table('gnl_sys_modules')
            ->select(['id as module_id', 'module_name'])
            ->where(['is_active' => 1, 'is_delete' => 0])
            ->where(function ($module_query) use ($comModules, $roleModules) {
                if(Common::isSuperUser() != true){
                    $module_query->whereIn('id', $comModules);
                    $module_query->whereIn('id', $roleModules);
                }
            })
            ->get();

        $ModuleSet = array();
        foreach ($module_query as $RowData) {
            $ModuleSet[] = (array) $RowData;
        }
        return $ModuleSet;
    }

    public static function menuArray()
    {
        $companyID = Common::getCompanyId();
        $comModules = array();

        $roleID = Common::getRoleId();
        $roleMenus = array();

        ///////////////
        if(Common::isSuperUser() != true){
            $companyData = DB::table('gnl_companies')
                            ->where([['is_active', 1], ['is_delete', 0],
                            ['id', $companyID]])
                            ->first();
            $comModules = ($companyData) ? explode(',', $companyData->module_arr) : array();

            $roleData = DB::table('gnl_sys_user_roles')
                            ->where([['is_active', 1], ['is_delete', 0],
                            ['id', $roleID]])
                            ->select(['menus'])
                            ->first();

            $roleMenus = ($roleData) ? explode(',', $roleData->menus) : array();
        }
        ///////////////

        // Menus Query
        $menu_query = DB::table('gnl_sys_menus')
            ->select(['id as menu_id', 'parent_menu_id', 'menu_name', 'module_id', 'route_link'])
            ->where(['is_active' => 1, 'is_delete' => 0])
            ->where(function ($menu_query) use ($comModules, $roleMenus) {
                if(Common::isSuperUser() != true){
                    $menu_query->whereIn('module_id', $comModules);
                    $menu_query->whereIn('id', $roleMenus);
                }
            })
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
        $roleID = Common::getRoleId();
        $rolePermis = array();

        ///////////////
        if(Common::isSuperUser() != true){
            $roleData = DB::table('gnl_sys_user_roles')
                            ->where([['is_active', 1], ['is_delete', 0],
                            ['id', $roleID]])
                            ->select(['permissions'])
                            ->first();

            $rolePermis = ($roleData) ? explode(',', $roleData->permissions) : array();
        }
        ///////////////

        // Permissions Query
        $permission_query = DB::table('gnl_user_permissions')
            ->select(['id as per_id', 'menu_id', 'name as per_name', 'set_status', 'route_link'])
            ->where(['is_active' => 1, 'is_delete' => 0])
            ->where(function ($permission_query) use ($rolePermis) {
                if(Common::isSuperUser() != true){
                    $permission_query->whereIn('id', $rolePermis);
                }
            })
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

            if ($SetStatus == 6 && !in_array('delete', $ignoreAction)) { // delete
                $temp = "fnDelete('" . $RowID . "');";

                $data_link .= '<a href="javascript:void(0)" onclick="'.$temp.'" title="' . $ActionName . '" class="btnDelete">';
                $data_link .= '<i class="icon wb-trash mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            
            if ($IsApproved == 0) {
                if ($SetStatus == 7 && !in_array('approve', $ignoreAction)) { // Approve
                    $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnApprove">';
                    $data_link .= '<i class="icon fa fa-check-square mr-2 blue-grey-600" style="font-size: 18px;"></i>';
                    $data_link .= '</a>';
                }
            }

            if ($SetStatus == 9 && !in_array('cngPasword', $ignoreAction)) { // Change Password
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnChangePassword">';
                $data_link .= '<i class="icon fa fa-exchange mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($SetStatus == 10 && !in_array('permission', $ignoreAction)) { // Permission
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnPermission">';
                $data_link .= '<i class="icon wb-grid-4 mr-2 blue-grey-600"></i>';
                // <i class="icon wb-grid-4 mr-2 blue-grey-600"></i>
                $data_link .= '</a>';
            }

            if ($SetStatus == 11 && !in_array('print', $ignoreAction)) { // print
                $data_link .= '<a href="javascript:void(0)" onClick="window.print()" title="' . $ActionName . '" class="btnPrint">';
                $data_link .= '<i class="icon fa fa-print mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($SetStatus == 12 && !in_array('print_pdf', $ignoreAction)) { // print pdf
                $data_link .= '<a href="javascript:void(0)" onClick="window.print()" title="' . $ActionName . '" class="btnPrintPDF">';
                $data_link .= '<i class="icon fa fa-file-pdf-o mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($SetStatus == 13 && !in_array('destroy', $ignoreAction)) { // Force Delete
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnForceDelete">';
                $data_link .= '<i class="icon wb-scissor mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

            if ($SetStatus == 14 && !in_array('per_folder', $ignoreAction)) { // Permission Folder
                $data_link .= '<a href="' . URL::to($ActionLink . '/' . $RowID) . '" title="' . $ActionName . '" class="btnPermissionFolder">';
                $data_link .= '<i class="icon icon wb-folder mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
            }

        }

        // dd($data_link);
        return $data_link;
    }

    public static function roleWiseArray($CurrentMenuPers = [], $RowID = null, $ignoreAction = [], $IsActive = null, $IsApproved = null)
    {
        $data_link = array();
        // dd($CurrentMenuPers);
        $SetStatus_arr = array();
        $actionName_arr = array();
        $actionLink_arr = array();

        $message = (isset($ignoreAction['message']) && !empty($ignoreAction['message'])) ? $ignoreAction['message'] : "You are not allowed to execute this action.";

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

            if ($SetStatus == 2) { // Edit
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);

                if(in_array('edit', $ignoreAction)){
                    // dd(in_array('edit', $ignoreAction));
                    array_push($actionLink_arr, '(message)='.$message);
                }else{
                    array_push($actionLink_arr, $actionLink);
                }
                
            }

            if ($SetStatus == 3) { // View
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);

                if(in_array('view', $ignoreAction)){

                    // dd(in_array('view', $ignoreAction));
                    array_push($actionLink_arr, '(message)='.$message);
                }else{
                    array_push($actionLink_arr, $actionLink);
                }
            }

            if ($IsActive == 1) {
                if ($SetStatus == 5 ) { // Unpublish
                    array_push($SetStatus_arr, $SetStatus);
                    array_push($actionName_arr, $actionName);
                     if(in_array('isActive', $ignoreAction)){
                        array_push($actionLink_arr, '(message)='.$message);
                    }else{
                        array_push($actionLink_arr, $actionLink);
                    }
                }
            } else {
                if ($SetStatus == 4 ) { // Publish
                    array_push($SetStatus_arr, $SetStatus);
                    array_push($actionName_arr, $actionName);
                    if(in_array('isActive', $ignoreAction)){
                        array_push($actionLink_arr, '(message)='.$message);
                    }else{
                        array_push($actionLink_arr, $actionLink);
                    }
                }
            }

            if ($SetStatus == 6 ) { // delete
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                if(in_array('delete', $ignoreAction)){
                    array_push($actionLink_arr, '(message)='.$message);
                }else{
                    array_push($actionLink_arr, $RowID);
                }
               
            }

            
            if ($IsApproved == 0) {
                if ($SetStatus == 7) { // Approve
                    array_push($SetStatus_arr, $SetStatus);
                    array_push($actionName_arr, $actionName);
                    if(in_array('approve', $ignoreAction)){
                        array_push($actionLink_arr, '(message)='.$message);
                    }else{
                        array_push($actionLink_arr, $actionLink);
                    }
                    
                }
            }

            if ($SetStatus == 9) { // Change Password
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                    if(in_array('cngPasword', $ignoreAction)){
                        array_push($actionLink_arr, '(message)='.$message);
                    }else{
                        array_push($actionLink_arr, $actionLink);
                    }
                
            }

            if ($SetStatus == 10 ) { // Permission
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                    if(in_array('permission', $ignoreAction)){
                        array_push($actionLink_arr, '(message)='.$message);
                    }else{
                        array_push($actionLink_arr, $actionLink);
                    }
                
            }

            if ($SetStatus == 11) { // print
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);

                    if(in_array('print', $ignoreAction)){
                        array_push($actionLink_arr, '(message)='.$message);
                    }else{
                        array_push($actionLink_arr, $RowID);
                    }
               
            }

            if ($SetStatus == 12) { // print pdf
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                    if(in_array('print_pdf', $ignoreAction)){
                        array_push($actionLink_arr, '(message)='.$message);
                    }else{
                        array_push($actionLink_arr, $RowID);
                    }
               
            }

            if ($SetStatus == 13) { // Force Delete
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);

                    if(in_array('destroy', $ignoreAction)){
                        array_push($actionLink_arr, '(message)='.$message);
                    }else{
                        array_push($actionLink_arr, $actionLink);
                    }
                
            }

            if ($SetStatus == 14) { // Permission Folder
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                    if(in_array('per_folder', $ignoreAction)){
                        array_push($actionLink_arr, '(message)='.$message);
                    }else{
                        array_push($actionLink_arr, $actionLink);
                    }   
                
            }
        }

        // dd($CurrentMenuPers);
        $data_link = [
            'set_status' => implode(',', $SetStatus_arr),
            'action_name' => implode(',', $actionName_arr),
            'action_link' => implode(',', $actionLink_arr),
        ];
        // dd($data_link);x

        return $data_link;
    }

    public static function bac_roleWiseArray($CurrentMenuPers = [], $RowID = null, $ignoreAction = [], $IsActive = null, $IsApproved = null)
    {
        $data_link = array();
        // dd($CurrentMenuPers);
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

                // dd(1);
            }

            if ($SetStatus == 3 && !in_array('view', $ignoreAction)) { // View
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $actionLink);
                // dd(1);
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

            if ($SetStatus == 6 && !in_array('delete', $ignoreAction)) { // delete
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $RowID);
            }

            
            if ($IsApproved == 0) {
                if ($SetStatus == 7 && !in_array('approve', $ignoreAction)) { // Approve
                    array_push($SetStatus_arr, $SetStatus);
                    array_push($actionName_arr, $actionName);
                    array_push($actionLink_arr, $actionLink);
                }
            }

            if ($SetStatus == 9 && !in_array('cngPasword', $ignoreAction)) { // Change Password
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $actionLink);
            }

            if ($SetStatus == 10 && !in_array('permission', $ignoreAction)) { // Permission
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $actionLink);
            }

            if ($SetStatus == 11 && !in_array('print', $ignoreAction)) { // print
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $RowID);
            }

            if ($SetStatus == 12 && !in_array('print_pdf', $ignoreAction)) { // print pdf
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $RowID);
            }

            if ($SetStatus == 13 && !in_array('destroy', $ignoreAction)) { // Force Delete
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $actionLink);
            }

            if ($SetStatus == 14 && !in_array('per_folder', $ignoreAction)) { // Permission Folder
                array_push($SetStatus_arr, $SetStatus);
                array_push($actionName_arr, $actionName);
                array_push($actionLink_arr, $actionLink);
            }
        }

        // dd($CurrentMenuPers);
        $data_link = [
            'set_status' => implode(',', $SetStatus_arr),
            'action_name' => implode(',', $actionName_arr),
            'action_link' => implode(',', $actionLink_arr),
        ];
        // dd($data_link);

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

            if ($SetStatus == 6) { // delete

                $temp = "fnDelete('" . $RowID . "');";

                $data_link .= '<a href="javascript:void(0)" onclick="'.$temp.'" title="' . $ActionName . '" class="btnDelete">';
                $data_link .= '<i class="icon wb-trash mr-2 blue-grey-600"></i>';
                $data_link .= '</a>';
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
                $data_link .= '<i class="icon wb-grid-4 mr-2 blue-grey-600"></i>';
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

    public static function childRolesIds($parent = null)
    {

        $data = DB::table('gnl_sys_user_roles')
            ->where([['is_delete', 0], ['is_active', 1], ['parent_id', $parent]])
            ->orderBy('order_by', 'ASC')
            ->get();

        $ids = [];
        foreach ($data as $roleData) {
            $ids[$roleData->id] = $roleData->id;
            $child = Self::childRolesIds($roleData->id);
            $ids = array_merge($ids, $child);
        }

        return $ids;
    }

    public static function childRoles($parent = null)
    {
        $data = DB::table('gnl_sys_user_roles')
            ->where([['is_delete', 0], ['is_active', 1], ['parent_id', $parent]])
            ->orderBy('order_by', 'ASC')
            ->get();

        $role_data = array();
        foreach ($data as $row) {
            $role_data[] = array(
                'id' => $row->id,
                'name' => $row->role_name,
                'is_active' => $row->is_active,
                'child_role' => Self::childRoles($row->id),
            );
        }

        return $role_data;
    }

    public static function roleGetData($parent = "0", $roleData = null, $permission = null)
    {
        $html = '';

        foreach ($roleData as $roleSig) {
            if (!empty($roleSig['child_role'])) {
                $mylink = 'trigger right-caret';
            } else {
                $mylink = null;
            }
            $html .= '<li>';
            $html .= '<a title="' . $roleSig['name'] . '" class="' . $mylink . ' my-link">' . $roleSig['name'] . '</a>';
            $html .= Self::childRoleData($roleSig['child_role'], $roleSig['id'], $permission);

            $html .= '<table class="w-full table-hover">';
            $html .= '<tr>';
            $html .= '<td width="60%">' . $roleSig['name'] . '</td>';
            $html .= '<td width="40%" style="text-align:right;">';
            $html .= Self::roleWisePermission($permission, $roleSig['id'], [], $roleSig['is_active']);
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '</table>';

            $html .= '</li>';
        }

        return $html;
    }

    public static function childRoleData($child_role_data = null, $parentID = null, $permission = null)
    {
        if (empty($child_role_data)) {
            return;
        }

        $html = "";
        $html .= '<ul class="dropdown-role sub-menu">';

        foreach ($child_role_data as $cRoleSig) {
            if (!empty($cRoleSig['child_role'])) {
                $mylink = 'trigger right-caret';
            } else {
                $mylink = null;
            }
            $html .= '<li>';
            $html .= '<a title="' . $cRoleSig['name'] . '" class="' . $mylink . ' my-link">' . $cRoleSig['name'] . '</a>';
            $subchild = Self::childRoleData($cRoleSig['child_role'], $parentID, $permission);

            if ($subchild != '') {
                $html .= $subchild;
            }
            $html .= '<table class="w-full table-hover">';
            $html .= '<tr>';
            $html .= '<td width="60%">' . $cRoleSig['name'] . '</td>';

            $html .= '<td width="40%" style="text-align:right; padding-right:5%;">';
            $html .= Self::roleWisePermission($permission, $cRoleSig['id'], [], $cRoleSig['is_active']);
            $html .= '</td>';
            $html .= '</tr>';
            $html .= '</table>';
            $html .= "</li>";
        }

        $html .= '</ul>';

        return $html;
    }

    public static function roleGetDataUser($parent = "0", $roleData = null, $permission = null)
    {
        $userID = Auth::id();
        $html = '';
        foreach ($roleData as $roleSig) {
            if (!empty($roleSig['child_role'])) {
                $mylink = 'trigger right-caret';
            } else {
                $mylink = null;
            }
            $html .= '<li class="user_role">';
            $html .= '<a title="' . $roleSig['name'] . '" class="' . $mylink . ' my-link">' . $roleSig['name'] . '</a>';

            $Users = DB::table('gnl_sys_users as gsu')
                ->where([['gsu.is_delete', 0], 
                ['gsu.is_active', 1],
                ['gsu.id', $userID],
                ['gsu.sys_user_role_id', $roleSig['id']]])
                ->leftJoin('gnl_companies as gc', 'gsu.company_id', 'gc.id')
                ->leftJoin('gnl_branchs as gb', 'gsu.branch_id', 'gb.id')
                ->select('gsu.*', 'gc.comp_name', 'gb.branch_name')
                ->orderBy('gsu.id', 'DESC')
                ->orderBy('gsu.branch_id', 'ASC')
                ->orderBy('gsu.company_id', 'ASC')
                ->get();

            // dd($Users);

            $html .= Self::childRoleDataUser($roleSig['child_role'], $roleSig['id'], $permission);

            // dd($html);

            // $html .= '<table class="table table-hover" >';
            if (count($Users->toarray()) > 0) {

                if(count($Users->toarray()) > 25){
                    $datatable = 'clsDataTable';
                }
                else{
                    $datatable = '';
                }

                $html .= '<table class="table table-hover '.$datatable.'" >';

                $html .= '<thead>';
                $html .= '<tr>';
                $html .= '<th width="5%">SL</th>';
                $html .= '<th width="5%">Images</th>';
                $html .= '<th width="20%">Full Name</th>';
                $html .= '<th width="10%">Username</th>';
                $html .= '<th width="10%">Contact No</th>';
                // $html .= '<th width="15%">Role Name</th>';
                $html .= '<th width="10%">Company</th>';
                $html .= '<th width="10%">Branch</th>';
                $html .= '<th width="15%">Actions</th>';
                $html .= '</tr>';
                $html .= '</thead>';

                $html .= '<tbody>';

                $i = 0;
                foreach ($Users as $row) {
                    $i++;
                    $html .= '<tr>';
                    $html .= '<td class="text-center">' . $i . '</td>';
                    // // // Image td
                    $html .= '<td class="text-center">';
                    $dummy_url = asset("assets/images/dummy.png");
                    if (!empty($row->user_image)) {
                        if (file_exists($row->user_image)) {
                            $html .= '<img src="' . asset($row->user_image) . '" style="height: 32px; width: 32px;">';
                        } else {

                            $html .= '<img src="' . $dummy_url . '" style="height: 32px; width: 32px;">';
                        }
                    } else {
                        $html .= '<img src="' . $dummy_url . '" style="height: 32px; width: 32px;">';
                    }
                    $html .= '</td>';

                    $html .= '<td>' . $row->full_name . '</td>';
                    $html .= '<td>' . $row->username . '</td>';
                    $html .= '<td class="text-center">' . $row->contact_no . '</td>';
                    // $html .= '<td>' . $row->sys_user_role_id . '</td>';
                    $html .= '<td>' . $row->comp_name . '</td>';
                    $html .= '<td>' . $row->branch_name . '</td>';

                    $html .= '<td  class="text-center">';
                    $html .= Self::roleWisePermission($permission, $row->id, [], $row->is_active);
                    $html .= '</td>';
                    $html .= '</tr>';
                }
                $html .= '</tbody>';
            $html .= '</table>';


            } else {
                $html .= '<div style="border:1px solid #f0b533; text-align:center;">';
                $html .= '<b>User not Found</b>';
                $html .= '</div>';
                // $html .= '<tr>';
                // $html .= '<td class="text-center"><b>User not Found</b></td>';
                // $html .= '</tr>';
            }
            // $html .= '</table>';

            $html .= '</li>';
        }

        return $html;
    }

    public static function childRoleDataUser($child_role_data = null, $parentID = null, $permission = null)
    {
        if ($child_role_data == null) {
            return;
        }

        $html = "";
        $html .= '<ul class="dropdown-role sub-menu">';

        foreach ($child_role_data as $cRoleSig) {
            if (!empty($cRoleSig['child_role'])) {
                $mylink = 'trigger right-caret';
            } else {
                $mylink = null;
            }

            $html .= '<li>';

            $html .= '<a title="' . $cRoleSig['name'] . '" class="' . $mylink . ' my-link">' . $cRoleSig['name'] . '</a>';

            $Users = DB::table('gnl_sys_users as gsu')
                ->where([['gsu.is_delete', 0], ['gsu.sys_user_role_id', $cRoleSig['id']]])
                ->leftJoin('gnl_companies as gc', 'gsu.company_id', 'gc.id')
                ->leftJoin('gnl_branchs as gb', 'gsu.branch_id', 'gb.id')
                ->select('gsu.*', 'gc.comp_name', 'gb.branch_name')
                ->orderBy('gsu.id', 'DESC')
                ->orderBy('gsu.branch_id', 'ASC')
                ->orderBy('gsu.company_id', 'ASC')
                ->get();

            $subchild = Self::childRoleDataUser($cRoleSig['child_role'], $parentID, $permission);

            if ($subchild != '') {
                $html .= $subchild;
            }

            // $html .= '<table class="table table-hover" data-plugin="dataTable">';
            if (count($Users->toarray()) > 0) {
                if(count($Users->toarray()) > 25){
                    $datatable = 'clsDataTable';
                }
                else{
                    $datatable = '';
                }

                $html .= '<table class="table table-hover '.$datatable.'" >';

                $html .= '<thead>';
                $html .= '<tr>';
                $html .= '<th width="5%">SL</th>';
                $html .= '<th width="5%">Images</th>';
                $html .= '<th width="20%">Full Name</th>';
                $html .= '<th width="10%">Username</th>';
                $html .= '<th width="10%">Contact No</th>';
                // $html .= '<th width="15%">Role Name</th>';
                $html .= '<th width="10%">Company</th>';
                $html .= '<th width="10%">Branch</th>';
                $html .= '<th width="15%">Actions</th>';
                $html .= '</tr>';
                $html .= '</thead>';

                $html .= '<tbody>';

                $i = 0;
                foreach ($Users as $row) {
                    $i++;
                    $html .= '<tr>';
                    $html .= '<td class="text-center">' . $i . '</td>';
                    // // // Image td
                    $html .= '<td class="text-center">';
                    $dummy_url = asset("assets/images/dummy.png");
                    if (!empty($row->user_image)) {
                        if (file_exists($row->user_image)) {
                            $html .= '<img src="' . asset($row->user_image) . '" style="height: 32px; width: 32px;">';
                        } else {

                            $html .= '<img src="' . $dummy_url . '" style="height: 32px; width: 32px;">';
                        }
                    } else {
                        $html .= '<img src="' . $dummy_url . '" style="height: 32px; width: 32px;">';
                    }
                    $html .= '</td>';

                    $html .= '<td>' . $row->full_name . '</td>';
                    $html .= '<td>' . $row->username . '</td>';
                    $html .= '<td class="text-center">' . $row->contact_no . '</td>';
                    // $html .= '<td>' . $row->sys_user_role_id . '</td>';
                    $html .= '<td>'.$row->comp_name.'</td>';
                    $html .= '<td>'.$row->branch_name.'</td>';

                    $html .= '<td  class="text-center">';
                    $html .= Self::roleWisePermission($permission, $row->id, [], $row->is_active);
                    $html .= '</td>';
                    $html .= '</tr>';
                }
                $html .= '</tbody>';
             $html .= '</table>';


            } 
            else {

                $html .= '<div style="border:1px solid #f0b533; text-align:center;">';
                $html .= '<b>User not Found</b>';
                $html .= '</div>';

                // $html .= '<tr>';
                // $html .= '<td class="text-center"><b>User not Found</b></td>';
                // $html .= '</tr>';
            }
            // $html .= '</table>';

            $html .= "</li>";
        }
        $html .= '</ul>';

        return $html;
    }
}
