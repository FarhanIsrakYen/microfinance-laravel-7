<?php

namespace App\Services;

use App\Services\HrService as HRS;
use App\Services\CommonService as Common;
use App\Services\MfnService as MFN;
use Route;
use Session;

class HtmlService
{

    public static function getModuleId($id = null)
    {
        return $smoduleID = Session::get('ModuleID');
    }

    public static function forLedgerSelectFeild($value = null)
    {

        $html = '';
        // $CompanyID = Common::getCompanyId();
        // $CompanyID = 0 ;

        $Model = 'App\\Model\\Acc\\Ledger';
        $QuerryData = $Model::where([['is_delete', 0], ['is_group_head', 0]])->orderBy('id', 'ASC')->get();

        $html .= '<div class="input-group" style="width:100%;">';
        $html .= '<select class="form-control  clsSelect2" name="code_arr[]" >';

        $html .= '<option value="">Select Code</option>';

        foreach ($QuerryData as $Row) {
            $selectTxt = '';
            if ($value != null) {
                if ($Row->code == $value) {
                    $selectTxt = "selected";
                }
            }
            $html .= '<option value="' . $Row->code . '" ' . $selectTxt . ' leger_id="' . $Row->id . '">' . $Row->name . " - " . $Row->code . '</option>';
        }

        $html .= '</select>';
        $html .= '</div>';

        return $html;
    }

    public static function forCompanyFeild($value = null, $disableText = '', $SelectBox = false)
    {

        $html = '';
        $CompanyID = Common::getCompanyId();
        // $CompanyID = 0 ;

        if ($CompanyID == 0 && $SelectBox == true) {

            $CompanyModel = 'App\\Model\\GNL\\Company';
            $CompanyData = $CompanyModel::where('is_delete', 0)->orderBy('comp_code', 'ASC')->get();

            $html .= '<div class="form-row align-items-center">';
            $html .= '<label class="col-lg-3 input-title">Company</label>';
            $html .= '<div class="col-lg-5 form-group">';
            $html .= '<div class="input-group">';
            $html .= '<select class="form-control selCompanyCls clsSelect2" name="company_id"  id="company_id" ' . $disableText . '>';

            $html .= '<option value="">Select Company</option>';

            foreach ($CompanyData as $Row) {
                $selectTxt = '';
                if ($value != null) {
                    if ($Row->id == $value) {
                        $selectTxt = "selected";
                    }
                }
                $html .= '<option value="' . $Row->id . '" ' . $selectTxt . ' >' . sprintf("%04d", $Row->comp_code) . " - " . $Row->comp_name . '</option>';
            }

            $html .= '</select>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        } else {
            if (!empty($value)) {
                $CompanyID = $value;
            }
            $html .= '<input type="hidden" name="company_id" id="company_id" value="' . $CompanyID . '">';
        }

        return $html;
    }

    public static function forBranchFeild($SelectBox = false, $FeildName = 'branch_id', $FeildID = 'branch_id',
        $SelectedValue = null, $DisableFeild = '', $Title = 'Branch', $IgnoreHO = false) {
        /**
         * Branch ID 1 = Head Office
         * When Head Office Login Feild is select form otherwise input hidden feild
         */

         
        $html = '';
        $BranchID = Common::getBranchId();

        if ($BranchID == 1 && $SelectBox == true) {

            $BranchModel = 'App\\Model\\GNL\\Branch';

            if ($IgnoreHO) {
                $BranchData = $BranchModel::where('is_delete', 0)
                    ->where('id', '<>', 1)
                    ->whereIn('id', HRS::getUserAccesableBranchIds())
                    ->orderBy('branch_code', 'ASC')->get();
            } else {
                $BranchData = $BranchModel::where(['is_delete' => 0])
                    ->whereIn('id', HRS::getUserAccesableBranchIds())
                    ->orderBy('branch_code', 'ASC')->get();
            }


            // dd($BranchData);
            $html .= '<div class="form-row align-items-center">';
            $html .= '<label class="col-lg-3 input-title RequiredStar">' . $Title . '</label>';
            $html .= '<div class="col-lg-5 form-group">';
            $html .= '<div class="input-group">';

            $html .= '<select class="form-control clsSelect2" required name="' . $FeildName . '"  id="' . $FeildID . '" ' . $DisableFeild . '>';

            if ($IgnoreHO) {
                $html .= '<option value="">Select One</option>';
            }

            foreach ($BranchData as $Row) {
                $selectTxt = '';
                if ($SelectedValue != null) {
                    if ($Row->id == $SelectedValue) {
                        $selectTxt = "selected";
                    }
                }
                $html .= '<option value="' . $Row->id . '" ' . $selectTxt . ' >' . sprintf("%04d", $Row->branch_code) . " - " . $Row->branch_name . '</option>';
            }

            $html .= '</select>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '</div>';
        } else {
            if (!empty($SelectedValue)) {
                $BranchID = $SelectedValue;
            }
            $html .= '<input type="hidden" name="' . $FeildName . '" id="' . $FeildID . '" value="' . $BranchID . '">';
        }

        return $html;
    }

    public static function forBranchFeildSearch($option = null, $FeildName = 'branch_id',
    $FeildID = 'branch_id', $Title = 'Branch', $SelectedValue = null, $IgnoreHO = false)
    {
        /**
         * Branch ID 1 = Head Office
         * When Head Office Login Feild is select form otherwise input hidden feild
         */
        $html = '';
        $BranchID = Common::getBranchId();

        if ($BranchID == 1) {

            $BranchModel = 'App\\Model\\GNL\\Branch';

            if ($IgnoreHO) {
                $BranchData = $BranchModel::where('is_delete', 0)
                    ->where('id', '<>', 1)
                    ->whereIn('id', HRS::getUserAccesableBranchIds())
                    ->orderBy('branch_code', 'ASC')->get();
            } else {
                $BranchData = $BranchModel::where(['is_delete' => 0])
                    ->whereIn('id', HRS::getUserAccesableBranchIds())
                    ->orderBy('branch_code', 'ASC')->get();
            }
            // $BranchData = $BranchModel::where(['is_delete' => 0])
            //     ->whereIn('id', HRS::getUserAccesableBranchIds())
            //     ->orderBy('branch_code', 'ASC')->get();

            $html .= '<div class="col-lg-2">';
            $html .= '<label class="input-title">' . $Title . '</label>';

            $html .= '<select class="form-control clsSelect2" name="' . $FeildName . '" id="' . $FeildID . '">';
            if ($option == 'all') {
                $html .= '<option value="">All</option>';
            }
            if ($option == 'one') {
                $html .= '<option value="">Select One</option>';
            }

            foreach ($BranchData as $Row) {
                $selectTxt = '';
                if ($SelectedValue != null) {
                    if ($Row->id == $SelectedValue) {
                        $selectTxt = "selected";
                    }
                }
                $html .= '<option value="' . $Row->id . '" ' . $selectTxt . '>' . sprintf("%04d", $Row->branch_code) . " - " . $Row->branch_name . '</option>';
            }

            $html .= '</select>';
            $html .= '</div>';
        } else {
            $html .= '<input type="hidden" name="branch_id" id="branch_id" value="' . $BranchID . '">';
        }

        return $html;
    }
    
    public static function forBranchFeildSearch_new($option = null, $FeildName = 'branch_id',
    $FeildID = 'branch_id', $Title = 'Branch', $SelectedValue = null)
    {
        /**
         * Branch ID 1 = Head Office
         * When Head Office Login Feild is select form otherwise input hidden feild
         */
        $html = '';
        $BranchID = Common::getBranchId();

        if ($BranchID == 1) {

            $BranchModel = 'App\\Model\\GNL\\Branch';
            $BranchData = $BranchModel::where([['is_delete', 0], ['id', '!=', 1]])
                ->whereIn('id', HRS::getUserAccesableBranchIds())
                ->orderBy('branch_code', 'ASC')
                ->get();

            $html .= '<div class="col-lg-2">';
            $html .= '<label class="input-title">'.$Title.'</label>';

            $html .= '<select class="form-control clsSelect2" name="'.$FeildName.'" id="'.$FeildID.'">';
            if ($option == 'all') {
                $html .= '<option value="">All</option>';
            }
            if ($option == 'one') {
                $html .= '<option value="">Select One</option>';
            }

            foreach ($BranchData as $Row) {
                $selectTxt = '';
                if ($SelectedValue != null) {
                    if ($Row->id == $SelectedValue) {
                        $selectTxt = "selected";
                    }
                }
                $html .= '<option value="' . $Row->id . '" ' . $selectTxt . '>' . sprintf("%04d", $Row->branch_code) . " - " . $Row->branch_name . '</option>';
            }

            $html .= '</select>';
            $html .= '</div>';
        } else {
            $html .= '<input type="hidden" name="'.$FeildName.'" id="'.$FeildID.'" value="' . $BranchID . '">';
        }

        return $html;
    }

    public static function forBranchSelect()
    {

        $BranchID = Common::getBranchId();
        $html = '';

        $html .= '<input type="hidden" name="branch_id" id="branch_id" value="' . $BranchID . '">';

        return $html;
    }

    public static function forCompanySelect()
    {

        $CompanyID = Common::getCompanyId();
        $html = '';

        $html .= '<input type="hidden" name="company_id" id="company_id" value="' . $CompanyID . '">';

        return $html;
    }

    public static function makeMenus()
    {

        $route = Route::current();
        $ActiveRouteURl = $route->uri();

        $curUriArr = explode('/', $ActiveRouteURl);

        $TitleText = '';

        if (count($curUriArr) >= 3) {
            $ActiveLink = $curUriArr[0] . "/" . $curUriArr[1];

            if ($curUriArr[2] == 'add') {
                $TitleText = ' Entry';
            } elseif ($curUriArr[2] == 'edit') {
                $TitleText = ' Update';
            } elseif ($curUriArr[2] == 'view') {
                $TitleText = ' Details';
            }
        } else {
            $ActiveLink = $ActiveRouteURl;
        }

        // dd($ActiveRouteURl);

        if (!empty(Session::get('ModuleID'))) {
            $ModuleLink = Session::get('ModuleID');
        } else {
            $route_array = explode('/', $ActiveRouteURl);
            $ModuleLink = $route_array[0];
            Session::put('ModuleID', $ModuleLink);
        }

        $route_array = explode('/', $ActiveRouteURl);
        $ModuleLink = $route_array[0];

        $MenuData = array();

        if (!empty(Session::get('LoginBy.user_role.role_menu.' . $ModuleLink))) {
            $MenuData = Session::get('LoginBy.user_role.role_menu.' . $ModuleLink);
        } elseif (!empty(Session::get('LoginBy.user_role.role_menu./' . $ModuleLink))) {
            $MenuData = Session::get('LoginBy.user_role.role_menu.' . $ModuleLink);
        }

        $mhtml = '<ul class="site-menu" data-plugin="menu">';

        // // ## Menu Hide for condition purpose
        $branchId = Common::getBranchId();
        $checkOpening = MFN::isOpening($branchId);

        // dd($MenuData);

        foreach ($MenuData as $RootMenu) {

            if($RootMenu['menu_link'] == "mfn/savings_ob"){
                if($checkOpening == false){
                    continue;
                }
            }

            $ActiveClass = '';

            if ($ActiveRouteURl == $RootMenu['menu_link'] || $ActiveLink == $RootMenu['menu_link']) {
                $ActiveClass = 'active pageTitle';
            }

            $SubMenu = false;
            if (count($RootMenu['sub_menu']) > 0) {
                $SubMenu = true;
            }

            if ($SubMenu) {
                $mhtml .= '<li class="site-menu-item has-sub CustomClass ">';
                $mhtml .= '<a href="javascript:void(0)" data-dropdown-toggle="false">';
            } else {
                $mhtml .= '<li class="site-menu-item CustomClass ' . $ActiveClass . '" menu_name="' . $RootMenu['name'] . $TitleText . '" page_title="' . $RootMenu['page_title'] . $TitleText . '">';
                // menu_link
                $mhtml .= '<a class="animsition-link" href="' . url($RootMenu['menu_link']) . '">';
            }

            $mhtml .= '<i class="site-menu-icon ' . $RootMenu['icon'] . ' aria-hidden="true" "></i>';
            $mhtml .= '<span class="site-menu-title">' . $RootMenu['name'] . '</span>';

            if ($SubMenu) {
                $mhtml .= '<span class="site-menu-arrow "></span>';
                $mhtml .= '</a>';

                $mhtml .= self::makeSubMenus($RootMenu['sub_menu'], $checkOpening);
            } else {
                $mhtml .= '</a>';
            }

            $mhtml .= '</li>';
        }

        $mhtml .= '</ul>';

        return $mhtml;
    }

    public static function makeSubMenus($SubMenuData = [], $checkOpening)
    {

        $route = Route::current();
        // $ActiveRouteURl = "/" . $route->uri();
        $ActiveRouteURl = $route->uri();

        $curUriArr = explode('/', $ActiveRouteURl);

        $TitleText = '';

        if (count($curUriArr) >= 3) {
            $ActiveLink = $curUriArr[0] . "/" . $curUriArr[1];

            if ($curUriArr[2] == 'add') {
                $TitleText = ' Entry';
            } elseif ($curUriArr[2] == 'edit') {
                $TitleText = ' Update';
            } elseif ($curUriArr[2] == 'view') {
                $TitleText = ' Details';
            }
        } else {
            $ActiveLink = $ActiveRouteURl;
        }

        $shtml = '<ul class="site-menu-sub">';

        foreach ($SubMenuData as $RowSubMenu) {

            if($RowSubMenu['menu_link'] == "mfn/savings_ob"){
                if($checkOpening == false){
                    continue;
                }
            }

            $ActiveClass = '';

            if ($ActiveRouteURl == $RowSubMenu['menu_link'] || $ActiveLink == $RowSubMenu['menu_link']) {
                // $ActiveClass = 'active';
                $ActiveClass = 'active pageTitle';
            }

            $SubChild = false;
            if (count($RowSubMenu['sub_menu']) > 0) {
                $SubChild = true;
            }

            if ($SubChild) {
                $shtml .= '<li class="site-menu-item has-sub">';
                $shtml .= '<a href="javascript:void(0)">';
            } else {
                $shtml .= '<li class="site-menu-item ' . $ActiveClass . '" menu_name="' . $RowSubMenu['name'] . $TitleText . '" page_title="' . $RowSubMenu['page_title'] . $TitleText . '">';
                $shtml .= '<a class="animsition-link" href="' . url($RowSubMenu['menu_link']) . '">';
            }

            $shtml .= '<span class="site-menu-title">' . $RowSubMenu['name'] . '</span>';

            if ($SubChild) {
                $shtml .= '<span class="site-menu-arrow "></span>';
                $shtml .= '</a>';
                $shtml .= self::makeSubMenus($RowSubMenu['sub_menu'], $checkOpening);
            } else {
                $shtml .= '</a>';
            }

            $shtml .= '</li>';
        }

        $shtml .= '</ul>';

        return $shtml;
    }

}
