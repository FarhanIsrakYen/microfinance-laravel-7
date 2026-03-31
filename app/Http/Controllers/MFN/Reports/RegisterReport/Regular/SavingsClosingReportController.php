<?php

namespace App\Http\Controllers\MFN\Reports\RegisterReport\Regular;
use App\Services\MfnService;
use App\Services\HrService;

use App\Http\Controllers\Controller;
use App\Model\MFN\Samity;
use App\Model\MFN\SavingsAccount;
use App\Model\MFN\SavingsClosing;
use App\Model\MFN\Member;

use App\Model\GNL\Branch;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DateTime;

class SavingsClosingReportController extends Controller
{
    
    public function index(Request $req)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();
        if (count($accessAbleBranchIds) > 1) {
            $branchs = DB::table('gnl_branchs as b')
                ->where([['is_delete', 0], ['is_approve', 1], ['is_active', 1], ['id', '>', 1]])
                ->whereIn('id', $accessAbleBranchIds)
                ->select('b.id', 'b.branch_name', 'b.branch_code', DB::raw("CONCAT(b.branch_code,' - ',b.branch_name) as branch"))
                ->get();
                $samityData = [];
        }
        else{
            $branchs =[];
            $samityData = DB::table('mfn_samity')
                ->where('is_delete', 0)
                ->whereIn('branchId',$accessAbleBranchIds)
                ->select(DB::raw("id, CONCAT(samityCode,' - ', name) AS name"))
                ->get();
        }

        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
       

        $ProductTypeData = DB::table('mfn_savings_product_type')
                        // ->where([['is_delete', 0]])
                        ->get();

        $ProductData = DB::table('mfn_savings_product')
                        ->where([['is_delete', 0]])
                        ->get();

        $data = array(
            "branchData"           => $branchs,
            // "fieldOfficerData"     => $fieldOfficerData,
            // "fundingOrgData"       => $fundingOrgData,
            "samityData"          =>$samityData,
            "ProductTypeData" => $ProductTypeData,
            "ProductData"      => $ProductData,
            'sysDate' => $sysDate,
        );

            return view('MFN.Reports.RegisterReport.Regular.SavingsClosing.index', $data);
    }


    public function getData(Request $req)
    {
        try {

            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
            //branch
            $branchId = $req->branch; 
            $branchData = Branch::where('gnl_branchs.id',$branchId)
                    ->select('gnl_branchs.*', 'gnl_companies.*')
                    ->leftJoin('gnl_companies', 'gnl_companies.id', 'gnl_branchs.company_id')->first();

            //date
            $date = new Datetime($req->date);
            $date = $date->format('Y-m-d');
            $date_to = new Datetime($req->date_to);
            $date_to = $date_to->format('Y-m-d');

            //product
            $selected_product = $req->productId; 
            $ProductData = $req->Product; 
            //samity
            $selected_samity = $req->samity;
            $samityData = Samity::where('branchId',$branchId)->get();
            $memberData = Member::where('branchId',$branchId)->get();



            $SavingsClosing = SavingsClosing::where([['mfn_savings_closings.branchId', $branchId]])
                    ->where('mfn_savings_closings.closingDate', '!=', '0000-00-00')
                    ->join('mfn_members', function ($SavingsClosing){
                        $SavingsClosing->on('mfn_members.id', '=', 'mfn_savings_closings.memberId')
                        ->where([['mfn_members.is_delete', 0]]);
                    })
                    ->join('mfn_samity', function ($SavingsClosing){
                        $SavingsClosing->on('mfn_savings_closings.samityId', '=', 'mfn_samity.id');
                           //->where([['mfn_samity.is_delete', 0]]);
                    })
                    ->join('mfn_savings_accounts', function($SavingsClosing){
                        $SavingsClosing->on('mfn_savings_closings.accountId', '=', 'mfn_savings_accounts.id');
                    })
                    ->where(function ($SavingsClosing) use ($selected_samity, $ProductData, $selected_product, $date, $date_to) {

                        //samity
                        if (!empty($selected_samity)) {
                            $SavingsClosing->where('mfn_savings_closings.samityId', '=', $selected_samity);
                        }
                        
                        if(!empty($selected_product)){
                            $SavingsClosing->where('mfn_savings_accounts.savingsProductId','=',$selected_product);
                        }
                        //date && date_to
                        if (!empty($date) && !empty($date_to)) {
        
                            $date = new DateTime($date);
                            $date = $date->format('Y-m-d');
        
                            $date_to = new DateTime($date_to);
                            $date_to = $date_to->format('Y-m-d');
        
                            $SavingsClosing->whereBetween('mfn_savings_closings.closingDate', [$date, $date_to]);
                        }
                    })
                    ->select('mfn_savings_accounts.accountCode','mfn_members.name as Mname','mfn_samity.name as samity','mfn_savings_closings.*','mfn_samity.samityCode as samityCode')
                    ->orderBy('mfn_savings_closings.samityId')
                    ->get();

                    $data = array(
                        'SavingsClosing' => $SavingsClosing,
                        'branchData'  => $branchData,
                        'FromDate' => $date,
                        'samity_selected' => $selected_samity,
                        'selected_product' => $selected_product,
                        'samityData' => $samityData,
                        'memberData' => $memberData,
                        'sysDate' => $sysDate,
                        'toDate' => $date_to,
                    );
                    if (empty($selected_samity)) {
                        return view('MFN.Reports.RegisterReport.Regular.SavingsClosing.viewreportbranch', $data);
                    } else {
                        return view('MFN.Reports.RegisterReport.Regular.SavingsClosing.viewreportbranchwithsamity', $data);
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
