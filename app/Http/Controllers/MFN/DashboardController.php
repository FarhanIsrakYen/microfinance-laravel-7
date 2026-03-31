<?php

namespace App\Http\Controllers\MFN;

/* Base Controller */

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\HrService as HRS;
use App\Services\MfnService;
use Redirect;
use DateTime;
use DB;

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
//        return view('mf.dashboard.dashboard');
        if (Auth::check()) {
            return view('MFN.Dashboard.dashboard');
        }
        return Redirect::to("login")->withSuccess('Opps! You do not have access');
    }

    public function branchStatus(Request $request)
    {
        $sl = 0;
        $branchData = array();

        $branchData = DB::table('gnl_branchs as gb')
            ->where([['gb.is_active', 1], ['gb.is_delete', 0], ['gb.is_approve', 1],['gb.id','!=',1]])
            ->whereIn('gb.id', HRS::getUserAccesableBranchIds())
            ->select('gb.id',
                'gb.branch_name',
                'gb.branch_code',
                'gb.branch_opening_date',
                'gb.acc_start_date',
                'gb.soft_start_date'
            )
            ->orderBy('gb.branch_code','ASC')
            ->get();
        
        $dayEndData = collect(DB::select( DB::raw("select f.`branchId`, f.`numberOfMember`, f.`numberOfBorrower`, f.`loanDueAmount`, f.`totalLoanDueAmount`, f.`loanOutstandingAmount`
        from (
           select `branchId`, max(date) as maxdate
           from mfn_day_end WHERE isActive =0 group by `branchId`
        ) as x inner join mfn_day_end as f on f.`branchId` = x.`branchId` and f.date = x.maxdate ORDER BY branchId;")));

        $DataSet = array();
        foreach ($branchData as $branch) {
            $dayEndDataForThisBranch = $dayEndData->where('branchId', $branch->id)->first();
            if($dayEndDataForThisBranch){
                
                $branch_name = $branch->branch_code . "-" . $branch->branch_name;
                $TempSet = array();

                $br_date = new DateTime(MfnService::systemCurrentDate($branch->id));
                $to_date = new DateTime();

                $lag = $to_date->diff($br_date)->format('%R%a');
                if ($lag < 0) {
                    $lagText = '<p style="color:red;">' . $lag . '</p>';
                }
                else if($lag >= 1){
                    $lagText = '<p style="color:#0F9FEC;">' . $lag . '</p>';
                }
                else {
                    $lagText = '<p style="color:greenyellow;">' . abs($lag) . '</p>';
                }
                
                $TempSet = [
                    'sl' => ++$sl,
                    'branch_name' => $branch_name,
                    'soft_start_date' => date('d-m-Y',strtotime($branch->soft_start_date)),
                    'branch_date' => $br_date->format('d-m-Y'),
                    'total_members' => $dayEndDataForThisBranch->numberOfMember,
                    'total_borrowers' => $dayEndDataForThisBranch->numberOfBorrower,
                    'total_due' => $dayEndDataForThisBranch->totalLoanDueAmount,
                    'due_today' => $dayEndDataForThisBranch->loanDueAmount,
                    'total_outstanding' => $dayEndDataForThisBranch->loanOutstandingAmount,
                    'LAG' => $lagText,
                ];
                $DataSet[] = $TempSet;
            }
        }

        $context = array(
            "data" => $DataSet,
        );

        return view('MFN.Dashboard.branchstatus', $context);
    }

}
