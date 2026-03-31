<?php

namespace App\Http\Controllers\MFN\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ProductInterestController extends Controller
{

    public function add()
    {
        return view('MFN.ProductInterest.add');
    }
}
