<?php

namespace App\Http\Controllers\MFN\Reports\Register\Regular;
use App\Services\MfnService;
use App\Services\HrService;
use App\Http\Controllers\Controller;
use App\Model\MFN\Samity;
use App\Model\GNL\Branch;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;
use Datetime;

class SavingsRegisterReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function loadReportData(Request $req){
        $branchId = (empty($req->input('branchId'))) ? null : $req->input('branchId');
        $samityId = (empty($req->input('samityId'))) ? null : $req->input('samityId');
        $startDate = (empty($req->input('startDate'))) ? null : $req->input('startDate');
        $endDate = (empty($req->input('endDate'))) ? null : $req->input('endDate');
        if (!empty($startDate) && !empty($endDate)) {
            $startDate = (new Datetime($startDate))->format('Y-m-d');
            $endDate = (new Datetime($endDate))->format('Y-m-d');
        }
        
        // getBranchAssignedSavProductIds
        if($samityId){ //siungle samity. show savigns account wise
            $savingsAccounts = DB::table('mfn_savings_accounts as msa')
                          ->where([['msa.is_delete', 0],['msa.branchId',$branchId],['msa.samityId',$samityId]])
                          ->orderBy('memberId')
                          ->select('msa.*')->get();

            $savingProducts = DB::table('mfn_savings_product')->whereIn('id', array_unique($savingsAccounts->pluck('savingsProductId')->toArray()))->get();
            
            $openingDeposits = DB::table('mfn_savings_deposit')
                            ->where([['branchId',$branchId],['samityId',$samityId]])
                            ->where('date','<',$startDate)
                            ->groupBy('accountId')
                            ->select(DB::raw("accountId, SUM(amount) AS amount"))->get();

            $openingWithdraws = DB::table('mfn_savings_withdraw')
                                ->where([['branchId',$branchId],['samityId',$samityId]])
                                ->where('date','<',$startDate)
                                ->groupBy('accountId')
                                ->select(DB::raw("accountId, SUM(amount) AS amount"))->get();

            
            $deposit = DB::table('mfn_savings_deposit')
                        ->where([['branchId',$branchId],['samityId',$samityId]])
                        ->where([['date','>=',$startDate],['date','<=',$endDate]])
                        ->groupBy('accountId')
                        ->select(DB::raw("accountId, SUM(amount) AS amount"))->get();
            
            $withdraw = DB::table('mfn_savings_withdraw')
                        ->where([['branchId',$branchId],['samityId',$samityId]])
                        ->where([['date','>=',$startDate],['date','<=',$endDate]])
                        ->groupBy('accountId')
                        ->select(DB::raw("accountId, SUM(amount) AS amount"))->get();

            $context = array(
                'accounts'=> $savingsAccounts,
                'savingProducts' => $savingProducts,
                'openingDeposits' => $openingDeposits,
                'openingWithdraws' => $openingWithdraws,
                'deposit' => $deposit,
                'withdraw' => $withdraw,
            );
    
            return view('MFN.Reports.SavingsRegister.table_view_single_samity', $context);
        }
        else{ // all samity. show with smaity wise

            $samityList = DB::table('mfn_samity')->where([['is_delete', 0], ['branchId', $branchId]])->get();

            $openingDeposits = DB::table('mfn_savings_deposit')->where('date','<',$startDate)
                            ->where('branchId',$branchId)
                            ->groupBy('samityId')
                            ->groupBy('savingsProductId')
                            ->select(DB::raw("samityId, savingsProductId, SUM(amount) AS amount"))->get();

            $openingWithdraws = DB::table('mfn_savings_withdraw')->where('date','<',$startDate)
                                ->where('branchId',$branchId)
                                ->groupBy('samityId')
                                ->groupBy('savingsProductId')
                                ->select(DB::raw("samityId, savingsProductId, SUM(amount) AS amount"))->get();

            
            $deposit = DB::table('mfn_savings_deposit')
                        ->where('branchId',$branchId)
                        ->where([['date','>=',$startDate],['date','<=',$endDate]])
                        ->groupBy('samityId')
                        ->groupBy('savingsProductId')
                        ->select(DB::raw("samityId,savingsProductId,  SUM(amount) AS amount"))->get();
            
            $withdraw = DB::table('mfn_savings_withdraw')
                        ->where('branchId',$branchId)
                        ->where([['date','>=',$startDate],['date','<=',$endDate]])
                        ->groupBy('samityId')
                        ->groupBy('savingsProductId')
                        ->select(DB::raw("samityId, savingsProductId, SUM(amount) AS amount"))->get();

            $savingsProductIds= [];
            $savingsProductIds = array_merge($savingsProductIds, $openingDeposits->pluck('savingsProductId')->toArray());
            $savingsProductIds = array_merge($savingsProductIds, $openingWithdraws->pluck('savingsProductId')->toArray());
            $savingsProductIds = array_merge($savingsProductIds, $deposit->pluck('savingsProductId')->toArray());
            $savingsProductIds = array_merge($savingsProductIds, $withdraw->pluck('savingsProductId')->toArray());
            $savingsProductIds = array_unique($savingsProductIds);

            $savingProducts = DB::table('mfn_savings_product')->whereIn('id', $savingsProductIds)->get();

            $context = array(
                'samityList'=> $samityList,
                'savingProducts' => $savingProducts,
                'openingDeposits' => $openingDeposits,
                'openingWithdraws' => $openingWithdraws,
                'deposit' => $deposit,
                'withdraw' => $withdraw,
            );
    
            return view('MFN.Reports.SavingsRegister.table_view_multi_samity', $context);
        }
        
        
    }

    public function getSavingsRegister(Request $req)
    {
        $branchId = ($req->branch_id) ? $req->branch_id : Auth::user()->branch_id;
        
        // if (Auth::user()->branch_id == 1) {
        //     $branchs = DB::table('gnl_branchs as b')
        //         ->where([['is_delete', 0], ['is_approve', 1], ['is_active', 1], ['id', '>', 1]])
        //         ->whereIn('id', HrService::getUserAccesableBranchIds())
        //         ->select('b.id', 'b.branch_name', 'b.branch_code', DB::raw("CONCAT(b.branch_code,' - ',b.branch_name) as branch"))
        //         ->get();
        // }

        $branchData = Branch::where([['is_delete', 0], ['is_active', 1], ['is_approve', 1]])
                        ->where('id', '>', 1)
                        ->select('id','branch_name','branch_code')->orderBy('branch_code')->get();

        $samityData = DB::table('mfn_samity')
                        ->where([['is_delete', 0],['branchId',$branchId]])
                        ->select('id','name')
                        ->get();

        $data = array(
            "branchData"  => $branchData,
            "samityData"  => $samityData,
        );

        return view('MFN.Reports.SavingsRegister.get_savings_register',$data);
    }
}
