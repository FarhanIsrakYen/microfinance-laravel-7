<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DeliveryController extends Controller
{
    public function index(Request $request)
    {
    	if ($request->ajax()) 
    	{
    		dd(1);
    	}
    	else
    	{
    		return view('POS.ProductDelivery.index');
    	}
    }

    public function add(Request $request)
    {
    	if($request->isMethod('POST'))
    	{
    		dd(2);
    	}
    	else
    	{
    		return view('POS.ProductDelivery.add');
    	}
    }
}
