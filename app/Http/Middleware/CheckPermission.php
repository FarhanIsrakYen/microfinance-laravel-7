<?php

namespace App\Http\Middleware;

use Closure;
use Route;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($request->ajax()){
            return $next($request);
        }
        
        $current_route_name = Route::getCurrentRoute()->uri();
        $check_route_name = $current_route_name;
        $explode_route_name = explode('/', $current_route_name);

        $ModulePermissions = (!empty(session()->get('LoginBy.user_role.role_module'))) ? session()->get('LoginBy.user_role.role_module') : array();
        // $MenuPermissions = (!empty(session()->get('LoginBy.user_role.role_menu'))) ? session()->get('LoginBy.user_role.role_menu') : array();
        $ActionPermissions = (!empty(session()->get('LoginBy.user_role.role_permission'))) ? session()->get('LoginBy.user_role.role_permission') : array();

        $MenuPermissions = array_keys($ActionPermissions);

        $requested_module = null;
        $requested_menu = null;
        $requested_action = null;

        if (isset($explode_route_name[0])) {
            $requested_module = $explode_route_name[0];
        }

        if (isset($explode_route_name[1])) {
            $requested_menu = $explode_route_name[0] . '/' . $explode_route_name[1];
        }

        if (isset($explode_route_name[2])) {
            $requested_action = $explode_route_name[0] . '/' . $explode_route_name[1] . '/' . $explode_route_name[2];
        }

        $flag = true;

        // // // Check Module
        if (!empty($requested_module)) {
            $isModule = array_search($requested_module, array_column($ModulePermissions, 'module_link'));
            if ($isModule === false) {
                $flag = false;
            }
        }

        // // // Check Menu
        if (!empty($requested_menu)) {
            $isMenu = array_search($requested_menu, $MenuPermissions);
            if ($isMenu === false) {
                // // For Report
                if (!empty($requested_action)){
                    $requested_menu = $requested_action;
                }

                $isMenu = array_search($requested_menu, $MenuPermissions);
                if ($isMenu === false){
                    $flag = false;
                }
            }
        }

        // dd($ActionPermissions[$requested_menu]);

        // // // Check Action
        if (!empty($requested_action)) {
            if (isset($ActionPermissions[$requested_menu])) {
                $isAction = array_search($requested_action, array_column($ActionPermissions[$requested_menu], 'route_link'));

                if ($isAction === false) {

                    // if(!isset($ActionPermissions[$requested_action])){
                    //     $flag = false;
                    // }

                    $flag = false;
                }
            } else {
                $flag = false;
            }
        }

        if ($flag === false) {
            // return redirect('/access_denied');
        }

        return $next($request);
    }
}
