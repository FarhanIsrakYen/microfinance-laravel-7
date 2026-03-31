<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Session;
use Auth;
use Illuminate\Support\Facades\Route;
use Redirect;
use Illuminate\Database\Schema\Builder;

class AppServiceProvider extends ServiceProvider
{

    protected $CurrentMenuPers;
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if(env('DEBUGBAR_INSTALLED') == true){
            if(env('DEBUGBAR_ENABLED') == false){
                \Debugbar::disable();
            }
        }               

        // Using view composer to set following variables globally
        view()->composer('*',function($view) {
            // $view->with('user', Auth::user());
            if (Route::getCurrentRoute() == '') {
                return Redirect()->back();
            }


            $CurrentRouteURI = Route::getCurrentRoute()->uri();
            // echo $CurrentRouteURI.'check';
            // $CurrentMenuRoute = "/".$CurrentRouteURI;
            $CurrentMenuRoute = $CurrentRouteURI;
            $RolePermissionAll = ( !empty(Session::get('LoginBy.user_role.role_permission')) ) ? Session::get('LoginBy.user_role.role_permission') : array();
            $CurrentMenuPers = (isset($RolePermissionAll[$CurrentMenuRoute])) ? $RolePermissionAll[$CurrentMenuRoute] : array();

            $view->with('GlobalRole', $CurrentMenuPers);
            $view->with('current_route_name', $CurrentRouteURI);

            // $view->with('social', Social::all());
        });

        Builder::defaultStringLength(191);
    }

    // public function getGRole(){
    //     return $CurrentMenuPers;
    // }
}
