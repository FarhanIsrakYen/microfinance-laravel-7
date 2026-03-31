<?php

namespace App\Http\Controllers\FAM;

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
        $this->middleware('auth');
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        return view('FAM.dashboard.dashboard');
        if (Auth::check()) {
            return view('FAM.dashboard.dashboard');
        }
        return Redirect::to("login")->withSuccess('Opps! You do not have access');
    }

}
