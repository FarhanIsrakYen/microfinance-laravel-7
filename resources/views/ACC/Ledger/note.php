
public static function makeMenus()
    {


        $MenuData = array();

        if (!empty(Session::get('LoginBy.user_role.role_menu.' . $ModuleLink))) {
            $MenuData = Session::get('LoginBy.user_role.role_menu.' . $ModuleLink);
        } elseif (!empty(Session::get('LoginBy.user_role.role_menu./' . $ModuleLink))) {
            $MenuData = Session::get('LoginBy.user_role.role_menu.' . $ModuleLink);
        }

        $mhtml = '<ul class="site-menu" data-plugin="menu">';

        // dd($MenuData);

        foreach ($MenuData as $RootMenu) {
            $ActiveClass = '';

            $SubMenu = false;
            if (count($RootMenu['sub_menu']) > 0) {
                $SubMenu = true;
            }

            if ($SubMenu) {
                $mhtml .= '<li class="site-menu-item has-sub CustomClass ">';
                $mhtml .= '<a href="javascript:void(0)" data-dropdown-toggle="false">';
            } else {
                $mhtml .= '<li class="site-menu-item CustomClass ' . $ActiveClass . '" menu_name="' . $RootMenu['name']. $TitleText . '" page_title="' . $RootMenu['page_title']. $TitleText . '">';
                // menu_link
                $mhtml .= '<a class="animsition-link" href="' . url($RootMenu['menu_link']) . '">';
            }

            $mhtml .= '<i class="site-menu-icon wb-hammer" aria-hidden="true"></i>';
            $mhtml .= '<span class="site-menu-title">' . $RootMenu['name'] . '</span>';

            if ($SubMenu) {
                $mhtml .= '<span class="site-menu-arrow "></span>';
                $mhtml .= '</a>';

                $mhtml .= self::makeSubMenus($RootMenu['sub_menu']);
            } else {
                $mhtml .= '</a>';
            }

            $mhtml .= '</li>';
        }

        $mhtml .= '</ul>';

        return $mhtml;
    }


public static function makeSubMenus($SubMenuData = [])
    {

        $shtml = '<ul class="site-menu-sub">';

        foreach ($SubMenuData as $RowSubMenu) {

            $ActiveClass = '';

            $SubChild = false;
            if (count($RowSubMenu['sub_menu']) > 0) {
                $SubChild = true;
            }

            if ($SubChild) {
                $shtml .= '<li class="site-menu-item has-sub">';
                $shtml .= '<a href="javascript:void(0)">';
            } else {
                $shtml .= '<li class="site-menu-item ' . $ActiveClass . '" menu_name="' . $RowSubMenu['name'].$TitleText . '" page_title="' . $RowSubMenu['page_title'].$TitleText . '">';
                $shtml .= '<a class="animsition-link" href="' . url($RowSubMenu['menu_link']) . '">';
            }

            $shtml .= '<span class="site-menu-title">' . $RowSubMenu['name'] . '</span>';

            if ($SubChild) {
                $shtml .= '<span class="site-menu-arrow "></span>';
                $shtml .= '</a>';
                $shtml .= self::makeSubMenus($RowSubMenu['sub_menu']);
            } else {
                $shtml .= '</a>';
            }

            $shtml .= '</li>';
        }

        $shtml .= '</ul>';

        return $shtml;
    }








    //////////////////
    public static function LedgerHTML()
    {
        // Menus Query
        $Data_query = DB::table('acc_account_ledger')
            ->select(['id', 'parent_id', 'name', 'order_by'])
            ->where(['is_active' => 1, 'is_delete' => 0])
            ->orderBy('parent_id', 'ASC')
            ->orderBy('order_by', 'ASC')
            ->get();
       
        $Data_query_group = $Data_query->groupBy('parent_id');
        $DataSet = array();

        $html = '<ul>';
        
        foreach ($Data_query_group[0] as $RootData) {

            // .$RootData->id.'-'

            $html .= '<li id="'.$RootData->id.'-li" onclick="fnspand(this.id);">';

            $html .= '<span>'.$RootData->name.'</span>';

            $html .= self::SubLedgerHTML($RootData->id, $Data_query_group);

            $html .= "</li>";
        }

        $html .= "</ul>";
        
        return $html;
    }

    public static function SubLedgerHTML($ParentID = null, $ParentArr = [])
    {

        $subHtml = "";
      

        if (isset($ParentArr[$ParentID])) {
            $SubArrData = $ParentArr[$ParentID];

            $subHtml .= '<ul id="'.$ParentID.'-ul" style="display:none;">';

            foreach ($SubArrData as $Subdata) {

                $subHtml .= '<li id="'.$Subdata->id.'-li" onclick="fnspand(this.id);">';

                $subHtml .= '<span>'.$Subdata->name.'</span>';

                $subHtml .= self::SubLedgerHTML($Subdata->id, $ParentArr);

                $subHtml .= "</li>";
            }

            $subHtml .= '</ul>';

        }

        return $subHtml;
    }