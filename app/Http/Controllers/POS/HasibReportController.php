<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\POS\SalesDetails;
use App\Model\POS\SalesMaster;
use DateTime;
use DB;
use Illuminate\Http\Request;

class HasibReportController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

}
