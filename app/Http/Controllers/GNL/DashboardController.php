<?php

namespace App\Http\Controllers\GNL;

/* Base Controller */

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Redirect;

/* Model Load Start */

// use App\Model\POS\Product;
/* Model Load End */

class DashboardController extends Controller
{
    public function __construct()
    {
        // $this->middleware(['auth', 'permission']);
        $this->middleware(['auth']);
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        return view('GNL.Dashboard.dashboard');
        if (Auth::check()) {

            // dd(HTML::makeMenus());

            // $Menus = HTML::makeMenus();

            // dd($Menus);

            return view('GNL.Dashboard.dashboard');
        }
        return Redirect::to("login")->withSuccess('Opps! You do not have access');
    }

}
