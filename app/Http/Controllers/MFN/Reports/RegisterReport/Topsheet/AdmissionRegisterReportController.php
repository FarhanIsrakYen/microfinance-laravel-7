<?php

namespace App\Http\Controllers\MFN\Reports\RegisterReport\Topsheet;
use App\Services\MfnService;
use App\Services\AccService;

use App\Http\Controllers\Controller;
use App\Model\MFN\Samity;
use App\Model\MFN\MemberDetails;

use App\Model\GNL\Branch;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;
use DateTime;

class AdmissionRegisterReportController extends Controller
{
    
    public function index(Request $req)
    {
        $branchId = ($req->branch_id) ? $req->branch_id : Auth::user()->branch_id;

        $branchData = Branch::where([['is_delete', 0], ['is_active', 1], ['is_approve', 1]])
                      ->select('id','branch_name','branch_code')->orderBy('branch_code')->get();
        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
        $fieldOfficerData = DB::table('mfn_samity as ms')
                        ->where([['ms.is_delete', 0],['ms.branchId',$branchId]])
                        ->leftjoin('hr_employees as he', function ($fieldOfficerData) {
                            $fieldOfficerData->on('ms.fieldOfficerEmpId', '=', 'he.id')
                                ->where([['he.is_delete', 0], ['he.is_active', 1]]);
                        })
                        ->select('he.id','he.emp_name')
                        ->groupBy('he.id')
                        ->get();

        $fundingOrgData = DB::table('mfn_funding_orgs')
                        ->where([['is_delete', 0]])
                        ->select('id','name')
                        ->get();

        $loanProdCategoryData = DB::table('mfn_loan_product_category')
                        ->where([['is_delete', 0]])
                        ->get();

        $loanProductData = DB::table('mfn_loan_products')
                        ->where([['is_delete', 0]])
                        ->get();

        $data = array(
            "branchData"           => $branchData,
            "fieldOfficerData"     => $fieldOfficerData,
            "fundingOrgData"       => $fundingOrgData,
            "loanProdCategoryData" => $loanProdCategoryData,
            "loanProductData"      => $loanProductData,
            'sysDate' => $sysDate,
        );

            return view('MFN.Reports.RegisterReport.Topsheet.AdmissionRegister.index', $data);



    }


    public function getData(Request $req)
    {


        try {

           
            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
            $branchId = $req->branch; 
            $branchData = Branch::where('gnl_branchs.id',$req->branch)
            ->select('gnl_branchs.*', 'gnl_companies.*')
            ->leftJoin('gnl_companies', 'gnl_companies.id', 'gnl_branchs.company_id')->first();
            $date = new Datetime($req->date);
            $date = $date->format('Y-m-d');
            $date_to = new Datetime($req->date_to);
            $date_to = $date_to->format('Y-m-d');

            $loanProdCategory = $req->category; 

            $loanProduct = $req->product; 

            $selected_samity = $req->samity;
            $samityData = Samity::where('branchId',$branchId)->get();





            $DataQuerry = MemberDetails::where([['mfn_member_details.is_delete', 0]])
                    
                    ->join('mfn_members', function ($DataQuerry) use ($branchId) {
                        $DataQuerry->on('mfn_members.id', '=', 'mfn_member_details.memberId')
                        ->where([['mfn_members.is_delete', 0],['mfn_members.branchId',$branchId]]);
                    })
                    ->join('mfn_samity', function ($DataQuerry){
                        $DataQuerry->on('mfn_members.samityId', '=', 'mfn_samity.id');
                           //->where([['mfn_samity.is_delete', 0]]);
                    })
                    ->join('mfn_loan_products as lp', function ($DataQuerry){
                        $DataQuerry->on('mfn_members.primaryProductId', '=', 'lp.id');
                        // ->where([['lp.is_delete', 0]]);
                    })
                    ->join('mfn_loan_product_category as lc', function ($DataQuerry){
                        $DataQuerry->on('lp.productCategoryId', '=', 'lc.id');
                           // ->where([['lc.is_delete', 0]]);
                    })
                    ->join('hr_employees', function ($DataQuerry){
                        $DataQuerry->on('mfn_samity.fieldOfficerEmpId', '=', 'hr_employees.id');
                            //->where([['lc.is_delete', 0]]);
                    })
                    ->where(function ($DataQuerry) use ($selected_samity) {
                        if (!empty($selected_samity)) {
                            $DataQuerry->where('mfn_members.samityId', '=', $selected_samity);
                        }
                    })
                    ->where(function ($DataQuerry) use ($loanProdCategory) {
                        if (!empty($loanProdCategory)) {
                            $DataQuerry->where('lp.productCategoryId', '=', $loanProdCategory);
                        }
                    })
                    ->where(function ($DataQuerry) use ($loanProduct) {
                        if (!empty($loanProduct)) {
                            $DataQuerry->where('mfn_members.primaryProductId', '=', $loanProduct);
                        }
                    })




                    ->where(function ($DataQuerry) use ( $date, $date_to) {

                        if (!empty($date) && !empty($date_to)) {
        
                            $date = new DateTime($date);
                            $date = $date->format('Y-m-d');
        
                            $date_to = new DateTime($date_to);
                            $date_to = $date_to->format('Y-m-d');
        
                            $DataQuerry->whereBetween('mfn_members.admissionDate', [$date, $date_to]);
                        }
                    })
                    ->select('mfn_members.*','mfn_member_details.*','mfn_samity.name as samity','mfn_samity.samityCode as samityCode','hr_employees.emp_name','lp.shortName as component','lc.shortName as category')
                    ->orderBy('mfn_members.samityId')
                    ->orderBy('mfn_member_details.memberId')
                    // ->orderBy('lp.isPrimaryProduct','desc')
                    ->get();
     
           

          




  
                    $data = array(
                        'DataQuerry' => $DataQuerry,
                        'branchData'  => $branchData,
                        'FromDate' => $date,
                        'samity_selected' =>$selected_samity,
                        'samityData' => $samityData,
                        'sysDate' => $sysDate,
                        'toDate' => $date_to,
                    );
                    return view('MFN.Reports.RegisterReport.Topsheet.AdmissionRegister.viewreportbranch', $data);
                
                   
    
              

               

            
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
