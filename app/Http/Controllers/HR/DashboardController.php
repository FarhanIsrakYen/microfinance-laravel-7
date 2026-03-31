<?php

namespace App\Http\Controllers\HR;

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
//        return view('HR.Dashboard.dashboard');
        if (Auth::check()) {
            return view('HR.Dashboard.dashboard');
        }
        return Redirect::to("login")->withSuccess('Opps! You do not have access');
    }

}
