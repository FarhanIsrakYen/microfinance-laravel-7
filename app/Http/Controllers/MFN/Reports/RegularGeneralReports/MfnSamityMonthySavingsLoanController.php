<?php

namespace App\Http\Controllers\MFN\Reports\RegularGeneralReports;
use App\Services\HrService as HRS;
use App\Http\Controllers\Controller;
use App\Model\MFN\MfnDayEnd;
use App\Model\GNL\Branch;
use App\Model\MFN\Samity;
use App\Model\MFN\Member;
use App\Model\MFN\Loan;

use Carbon\Carbon;
use DB;
use App\Model\MFN\SavingsAccount;
use App\Model\MFN\LoanProductCategory;
use App\Model\MFN\LoanProduct;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use DateTime;
use Role;
use App\Services\HrService;
use App\Services\MfnService;
use App\Helpers\RoleHelper;

class MfnSamityMonthySavingsLoanController extends Controller
{

    public function index(Request $req)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();
        $branchList = Branch::where('is_delete', 0)
                ->where('id', '>', 1)
                ->whereIn('id', $accessAbleBranchIds)
                ->orderBy('branch_name')
                ->select('id', 'branch_name','branch_code')
                ->get();

        $LoanProductCat = LoanProductCategory::get();

                $data = array(
                    "branchList"       => $branchList,
                    "LoanProductCat"   => $LoanProductCat ,
                );
        return view('MFN.Reports.RegularGeneralReports.SamityMonthlySLCollection.index' , $data);
    }
    
    
    public function ShowReport(Request $req)
    {

        
        try {
            $RequestData = $req->all();

            $product_cat = (empty($RequestData['product_cat'])) ? null : $RequestData['product_cat'];
            $product = (empty($RequestData['product'])) ? null : $RequestData['product'];
            $report_option = (empty($RequestData['report_option'])) ? null : $RequestData['report_option'];
            $member_code_show = (empty($RequestData['member_code_show'])) ? null : $RequestData['member_code_show'];
            $branchID  = (empty($RequestData['branch'])) ? null : $RequestData['branch'];


            $productarray = null;
            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

            $branchData = (empty($RequestData['branch'])) ? null : Branch::where('gnl_branchs.is_delete', '=', 0)
                                                                    ->where('gnl_branchs.id', $RequestData['branch'])
                                                                    ->select('gnl_branchs.*','gnl_companies.*')  
                                                                    ->leftJoin('gnl_companies', 'gnl_companies.id', 'gnl_branchs.company_id')
                                                                    ->first();

            $samityData = Samity::where('id',$RequestData['samity'])->first();

            if(empty($product)){
                if(!empty($product_cat)){
                    $prod = LoanProduct::where('productCategoryId',$product_cat)->get();
                    $productarray= $prod->pluck('id')->toArray();
                }
            }

            $weekdays = $this->getWeeksOfThisMonths($RequestData['year'],$RequestData['month'],$RequestData['day']);

            $dayDate = new DateTime ($RequestData['year'].'-'.$RequestData['month']);
            $previousDayDate  = $dayDate->modify( '-1 day' );
            $previousDayDate = $previousDayDate->format('Y-m-d');
        

            
            $memberDetails = Member::where('mfn_members.samityId', $RequestData['samity'])
                            ->select('mfn_members.*','mfn_loan_products.name as Product_Name','mfn_member_details.spouseName as spouseName')  
                            ->leftJoin('mfn_loan_products', 'mfn_loan_products.id', 'mfn_members.primaryProductId')
                            ->leftJoin('mfn_member_details', 'mfn_member_details.memberId', 'mfn_members.id')
                            ->where(function ($memberDetails) use ($productarray,$product) {
                                
                                if (!empty($productarray)) {
                                    $memberDetails->whereIn('mfn_members.primaryProductId', $productarray);
                                }
                                if (!empty($product)) {
                                    $memberDetails->where('mfn_members.primaryProductId', $product);
                                }
                            
                            })
                                ->get();
            foreach ($memberDetails as $key => $value) {
    
                $memberDetails[$key]['SavingsInfo'] = SavingsAccount::where('mfn_savings_accounts.is_delete',0)->where('mfn_savings_accounts.memberId',$value->id)
                                                    ->where('mfn_savings_product.productTypeId',1)
                                                    ->select('mfn_savings_accounts.*','mfn_savings_product.collectionFrequencyId As freequency')
                                                    ->leftJoin('mfn_savings_product', 'mfn_savings_product.id', 'mfn_savings_accounts.savingsProductId') 
                                                    ->get();
                                                    

                $loans = Loan::where('is_delete',0)->where('memberId',$value->id)->where('loanCompleteDate','0000-00-00')->get();

                $installmentDetils = MfnService::generateLoanSchedule($loans->pluck('id')->toArray());
                $installmentDetils = collect($installmentDetils);
                $installmentDetils = $installmentDetils->whereIn('installmentDate', $weekdays);
                MfnService::resetProperties();

                $LonStatusAll = MfnService::getLoanStatus($loans->pluck('id')->toArray(),$previousDayDate);
                $LonStatusAll = collect($LonStatusAll);
                foreach ($loans as $loanKey => $loan) {
                    if(!empty($installmentDetils->where('loanId', $loan->id))){
                        $loans[$loanKey]['calInstalment'] = $installmentDetils->where('loanId', $loan->id);
                        
                    }else{
                        $loans[$loanKey]['calInstalment'] = '-';
                    
                    }
                    
                    $loans[$loanKey]['loanStatus'] = $LonStatusAll->where('loanId', $loan->id);
            
                }

                

                $memberDetails[$key]['LoanInfo'] = $loans;
                $memberDetails[$key]['max']= (($memberDetails[$key]['SavingsInfo']->count() >= $memberDetails[$key]['LoanInfo']->count())? $memberDetails[$key]['SavingsInfo']->count() : $memberDetails[$key]['LoanInfo']->count() );

                if($memberDetails[$key]['LoanInfo']->count() == 0 &&  $memberDetails[$key]['SavingsInfo']->count() == 0 ){
                    unset($memberDetails[$key]);
                }


            }


            if(!empty($product_cat)){
                $product_cat = LoanProductCategory::where('id',$product_cat)->first();
            }
            if(!empty($product)){
                $product = LoanProduct::where('id',$product)->first();
            }

            $data = array(
                'memberDetails' => $memberDetails,

                'weekdays'=> collect($weekdays),
                'previousDayDate' => $previousDayDate,
                'member_code_show' => $member_code_show,
                'branchData'  => $branchData,
                'samityData' => $samityData,
                'sysDate' => $sysDate,
                'month'  => $RequestData['month'],
                'product_cat' => $product_cat,
                'product' => $product,

            );

            if($RequestData['report_option']== 1){
                return view('MFN.Reports.RegularGeneralReports.SamityMonthlySLCollection.viewreports', $data);
            }else{
                return view('MFN.Reports.RegularGeneralReports.SamityMonthlySLCollection.viewreportsdouble', $data);
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

    public function getWeeksOfThisMonths($year = null , $month = null, $dayname = null)
    {
        $ArrayDate = array();

        $date = new DateTime ($year.'-'.$month);
        $clone =  new DateTime ($year.'-'.$month);
        $nextMOnth = $date;
        $nextMOnth = $nextMOnth->modify('first day of next month');

        while($clone < $nextMOnth ){
            if($clone->format('l')==$dayname){
                // print_r($clone->format('l'));
                array_push($ArrayDate,$clone->format('Y-m-d'));   
            }

            $clone->modify( '+1 day' );
            
        }

        return $ArrayDate ;
        
    }


    public function getDataSamity(Request $req)
    {

        $branchID = $req->BranchID;
        $day = $req->Day;

        $samitys = Samity::where('is_delete', '=', 0)
                        ->where('branchId', $branchID)
                        ->where('samityDay', $day)
                        ->get();
        $html = '<option value="">Select Samity</option>';

        foreach ($samitys as $Row) {
            
            $html .= '<option value="' . $Row->id . '">'. $Row->name . '</option>';
        }        

        echo json_encode($html);
        
    }
    public function getDataYears(Request $req)
    {

        $branchID = $req->BranchID;
        $branchData = Branch::where('is_delete', 0)
                    ->where('id', $branchID)
                    ->first();
        $arrayDates = array();


       $date = new DateTime ($branchData->soft_start_date);
       array_push($arrayDates , $date->format('Y') );
      

       for ($i= 0 ; $i < 5 ; $i++){
           
        $date->modify( '+1 year' );
        array_push($arrayDates ,$date->format('Y') );
       }


      $arrayDates = collect($arrayDates);
    
        $html = '<option value="">Select Year</option>';
       
        // $html .= ' <option value="2016">2016</option>';
        // $html .= ' <option value="2017">2017</option>';
        // $html .= ' <option value="2018">2018</option>';
        // $html .= ' <option value="2019">2019</option>';
        foreach ($arrayDates as $Row) {
            $html .= '<option value="' . $Row . '">'. $Row . '</option>';
        }

        

        echo json_encode($html);
        
    }
   

}