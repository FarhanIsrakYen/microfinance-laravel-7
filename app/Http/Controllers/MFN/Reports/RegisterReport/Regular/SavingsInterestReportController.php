<?php

namespace App\Http\Controllers\MFN\Reports\RegisterReport\Regular;
use App\Services\MfnService;
use App\Services\AccService;

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
use DateTime;
use App\Model\MFN\Member;

class SavingsInterestReportController extends Controller
{
    
    public function index(Request $req)
    {
        $branchId = ($req->branch_id) ? $req->branch_id : Auth::user()->branch_id;
        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
        $branchData = Branch::where([['is_delete', 0], ['is_active', 1], ['is_approve', 1]])
                      ->select('id','branch_name','branch_code')->orderBy('branch_code')->get();

       

      

        $ProductData = DB::table('mfn_savings_product')
                        ->where([['is_delete', 0]])
                        ->select('id','shortName')
                        ->get();

        $data = array(
            "branchData"           => $branchData,
           
            "sysDate" => $sysDate,
            "ProductData"      => $ProductData,
        );

         return view('MFN.Reports.RegisterReport.Regular.SavingsInterest.index', $data);
 

    }


    public function getData(Request $req)
    {


        try {

            $RequestData = $req->all();
            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
            $branchId = $req->branch_id; 
            $branchData = Branch::where('gnl_branchs.id',$req->branch_id)
            ->select('gnl_branchs.*', 'gnl_companies.*')
            ->leftJoin('gnl_companies', 'gnl_companies.id', 'gnl_branchs.company_id')->first();
            

            
            $date = new Datetime($req->date);
            $date = $date->format('Y-m-d');
            $date_to = new Datetime($req->date_to);
            $date_to = $date_to->format('Y-m-d'); 
            $selected_samity = $req->samity;
            $product_id = $req->product_id;
            $samityData = Samity::where('branchId',$branchId)->get();
            $MemberData = Member::where('branchId',$branchId)->get();

            $savings = DB::table('mfn_savings_accounts')->where('mfn_savings_accounts.closingDate', '0000-00-00')
            ->where([['mfn_savings_accounts.is_delete', 0],['mfn_savings_accounts.branchId',$branchId]])
            ->where(function($savings) use ($selected_samity) {
                if (!empty($selected_samity)) {
                    $savings->where('mfn_savings_accounts.samityId', $selected_samity);
                }
            })
            ->where(function($savings) use ($product_id) {
                if (!empty($product_id)) {
                    $savings->where('mfn_savings_accounts.savingsProductId', $product_id);
                }
            })
            
            ->leftjoin('mfn_savings_product', function ($savings) {
                $savings->on('mfn_savings_product.id', '=', 'mfn_savings_accounts.savingsProductId')
                    ->where([['mfn_savings_product.is_delete', 0]]);
            })
            ->leftjoin('mfn_savings_deposit', function ($savings) {
                $savings->on('mfn_savings_deposit.accountId', '=', 'mfn_savings_accounts.id')
                    ->where([['mfn_savings_deposit.is_delete', 0],['mfn_savings_deposit.transactionTypeId', 2]]);
            })
            ->where(function ($savings) use ( $date, $date_to) {

                if (!empty($date) && !empty($date_to)) {

                    $date = new DateTime($date);
                    $date = $date->format('Y-m-d');

                    $date_to = new DateTime($date_to);
                    $date_to = $date_to->format('Y-m-d');

                    $savings->whereBetween('mfn_savings_deposit.date', [$date, $date_to]);
                }
            })
            ->groupBy('mfn_savings_deposit.samityId')
            ->selectRaw('mfn_savings_accounts.* , mfn_savings_product.name as product ,mfn_savings_deposit.transactionTypeId , SUM(mfn_savings_deposit.amount) as amount')
                // DB::raw('
                //     IFNULL(SUM(sd.amount),0) as deposit, IFNULL(SUM(sw.amount),0) as withdraw,
                //     IFNULL(SUM(sd.amount),0) - IFNULL(SUM(sw.amount),0) as closingBL,
                //     IFNULL(SUM(sd2.amount),0) - IFNULL(SUM(sw2.amount),0) as openingBL
                //     '))
            ->get();
     
                
                
                

              

          
                if(!empty( $selected_samity ) && !empty($branchId)){
                    $data = array(
                        'savings' => $savings,
                        'MemberData' =>$MemberData,
                        'branchData'  => $branchData,
                        'FromDate' => $date,
                        'samity_selected' =>$selected_samity,
                        'samityData' => $samityData,
                        'sysDate' => $sysDate,
                        
                        'toDate' => $date_to,
                    );
                    return view('MFN.Reports.RegisterReport.Regular.SavingsInterest.viewreportsamity', $data);
                }else{
                    $data = array(
                        'savings' => $savings,
                        
                        'branchData'  => $branchData,
                        'FromDate' => $date,
                        'samity_selected' =>$selected_samity,
                        'samityData' => $samityData,
                        'sysDate' => $sysDate,
                        'toDate' => $date_to,
                    );
                    return view('MFN.Reports.RegisterReport.Regular.SavingsInterest.viewreportbranch', $data);
                }
                   
    
              

               

            
        } catch (\Throwable $e) {
            //throw $th;
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong',
                'consoleMsg'    => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage()
            );



            return redirect()->back()->with($notification);
        }
    }


}
