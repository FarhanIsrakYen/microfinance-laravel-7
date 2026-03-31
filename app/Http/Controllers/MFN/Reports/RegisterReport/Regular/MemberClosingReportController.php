<?php

namespace App\Http\Controllers\MFN\Reports\RegisterReport\Regular;
use App\Services\MfnService;
use App\Services\AccService;

use App\Http\Controllers\Controller;
use App\Model\MFN\Samity;
use App\Model\MFN\Member;
use App\Model\MFN\memberClosing;
use App\Model\GNL\Branch;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;
use DateTime;

class memberClosingReportController extends Controller
{
    
    public function index(Request $req)
    {
        
        $branchData = Branch::where([['is_delete', 0], ['is_active', 1], ['is_approve', 1]])
                      ->select('id','branch_name','branch_code')->orderBy('branch_code')->get();
        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
        

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
            "fundingOrgData"       => $fundingOrgData,
            "loanProdCategoryData" => $loanProdCategoryData,
            "loanProductData"      => $loanProductData,
            'sysDate' => $sysDate,
        );

            return view('MFN.Reports.RegisterReport.Regular.MemberClosing.index', $data);

    }


    public function getData(Request $req)
    {
        try {

           
            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

            //branch
            $branchId = $req->branch; 
            $branchData = Branch::where('gnl_branchs.id',$req->branch)
            ->select('gnl_branchs.*', 'gnl_companies.*')
            ->leftJoin('gnl_companies', 'gnl_companies.id', 'gnl_branchs.company_id')->first();

            //date
            $date = new Datetime($req->date);
            $date = $date->format('Y-m-d');
            $date_to = new Datetime($req->date_to);
            $date_to = $date_to->format('Y-m-d');

            //category
            $selected_category = $req->category; 

            //product
            $selected_product = $req->productId; 


            //samity
            $selected_samity = $req->samity;
            $samityData = Samity::where('branchId',$branchId)->get();

            $memberClosing = DB::table('mfn_member_closings')->where([['mfn_member_closings.branchId', $branchId]])
                    ->where('mfn_member_closings.closingDate', '!=', '0000-00-00')
                    ->join('mfn_members', function ($memberClosing){
                        $memberClosing->on('mfn_members.id', '=', 'mfn_member_closings.memberId')
                        ->where([['mfn_members.is_delete', 0]]);
                    })
                    ->join('mfn_member_details', function ($memberClosing){
                        $memberClosing->on('mfn_member_details.memberId', '=', 'mfn_member_closings.memberId');
                    })
                    ->join('mfn_samity', function ($memberClosing){
                        $memberClosing->on('mfn_member_closings.samityId', '=', 'mfn_samity.id');
                    })
                    ->join('mfn_savings_product', function ($memberClosing){
                        $memberClosing->on('mfn_savings_product.id', '=', 'mfn_members.primaryProductId');
                    })
                    ->join('mfn_loan_products', function ($memberClosing){
                        $memberClosing->on('mfn_loan_products.id', '=', 'mfn_members.primaryProductId');
                    })
                    
                    ->where(function ($memberClosing) use ($selected_samity, $selected_category, $selected_product, $date, $date_to) {

                        //samity
                        if (!empty($selected_samity)) {
                            $memberClosing->where('mfn_member_closings.samityId', '=', $selected_samity);
                        }

                        //category
                        if(!empty($selected_category)){
                            $memberClosing->where('mfn_loan_products.productCategoryId','=',$selected_category);
                        }

                        //product
                        if(!empty($selected_product)){
                            $memberClosing->where('mfn_members.primaryProductId','=',$selected_product);
                        }

                        //date && date_to
                        if (!empty($date) && !empty($date_to)) {
        
                            $date = new DateTime($date);
                            $date = $date->format('Y-m-d');
        
                            $date_to = new DateTime($date_to);
                            $date_to = $date_to->format('Y-m-d');
        
                            $memberClosing->whereBetween('mfn_member_closings.closingDate', [$date, $date_to]);
                        }
                    })
                    ->select('mfn_samity.name as samity',
                            'mfn_samity.fieldOfficerEmpId as fieldOfficerEmpId',
                            'mfn_samity.samityCode as samityCode',
                            'mfn_members.memberCode as memberCode',
                            'mfn_members.name as memberName',
                            'mfn_member_details.fatherName as mFatherName',
                            'mfn_member_details.spouseName as mSpouseName',
                            'mfn_members.admissionDate as mAdmissionDate',
                            'mfn_members.closingDate as mClosingDate',
                            'mfn_members.primaryProductId as productId',
                            'mfn_member_closings.*',
                            'mfn_loan_products.name as productName'
                            )
                    ->orderBy('mfn_member_closings.samityId')
                    ->get();
            //field officers
            $fieldOfficersData=  MfnService::getFieldOfficersByDate($samityData->pluck('id')->toArray(),$date, $date_to);
            $fieldOfficersData = collect($fieldOfficersData);
            $employeeNameIdentify = DB::table('hr_employees')
                                ->whereIn('id', $fieldOfficersData->pluck('fieldOfficerId')->toArray())
                                ->get();
            foreach ($memberClosing as $key => $memClosing) {
                $fieldOfficerId = $fieldOfficersData->where('samityId', $memClosing->samityId)->where('dateFrom', '<=', $memClosing->mClosingDate)->where('dateTo', '>=', $memClosing->mClosingDate)->first()['fieldOfficerId'];

                $memberClosing[$key]->fieldOfficer = $employeeNameIdentify->firstWhere('id', $fieldOfficerId)->employee_no;
            }
           
               
                    $data = array(
                        'memberClosing' => $memberClosing,
                        'branchData'  => $branchData,
                        'FromDate' => $date,
                        'samity_selected' => $selected_samity,
                        'selected_product' => $selected_product,
                        'samityData' => $samityData,
                        'fieldOfficersData' => $fieldOfficersData,
                        'sysDate' => $sysDate,
                        'toDate' => $date_to,
                    );
                    return view('MFN.Reports.RegisterReport.Regular.MemberClosing.viewreportbranch', $data);

            
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
