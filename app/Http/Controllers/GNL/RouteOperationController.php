<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class RouteOperationController extends Controller
{
    /**
     * This function shows all the routes.
     *
     * @return void
     */

    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index()
    {
        return view('GNL.routes.index');
    }

    public function ajaxRoutesIndex(Request $req)
    {

        $columns = ['route', 'moduleId', 'componentId', 'operationId', 'ignoreRoute'];

        $limit = $req->length;
        $order = $columns[$req->input('order.0.column') + 1];
        $dir = $req->input('order.0.dir');

        $routes = DB::table('gnl_routes')
            ->orderBy($order, $dir)
            ->limit($limit)
            ->get();

        $totalData = DB::table('gnl_routes')->count('id');

        $sl = (int) $req->start + 1;
        foreach ($routes as $key => $route) {
            $routes[$key]->sl = $sl++;
        }

        $data = array(
            "draw" => intval($req->input('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalData,
            'data' => $routes,
        );

        return response()->json($data);
    }

    public function refreshRoutes()
    {
        $routes = \Route::getRoutes();

        // routs which we would ignore
        $ignoredRoutes = ['/', 'post-login', '_ignition/health-check', '_ignition/execute-solution', '_ignition/share-report', '_ignition/scripts', '_ignition/styles', 'api/user', 'login', 'login', 'logout', 'register', 'register', 'password/reset', 'password/email', 'password/reset', 'password/reset', 'password/confirm', 'password/confirm'];

        $routesArray = array();
        foreach ($routes as $route) {
            if (strpos($route->uri, '/{') == true) {
                $route->uri = substr($route->uri, -0, strpos($route->uri, "/{"));
            }
            array_push($routesArray, $route->uri);
        }
        // dd($routesArray);
        $routesArray = array_diff($routesArray, $ignoredRoutes);
        // dd($routesArray, $ignoredRoutes);

        $dbRoutes = DB::table('gnl_routes')->get();

        $newRoutes = array_unique(array_diff($routesArray, $dbRoutes->pluck('name')->toArray()));
        // dd($newRoutes);
        $invalidRoutes = array_diff($dbRoutes->pluck('name')->toArray(), $routesArray);

        // insert new routes to the database
        $newRoutesData = array();
        foreach ($newRoutes as $key => $newRoute) {
            $newRoutesData[$key]['route'] = $newRoute;
        }
        DB::table('gnl_routes')->insert($newRoutesData);

        // delete $invalidRoutes
        DB::table('gnl_routes')
            ->whereIn('route', $invalidRoutes)
            ->delete();

        dd($newRoutes);
    }
}
