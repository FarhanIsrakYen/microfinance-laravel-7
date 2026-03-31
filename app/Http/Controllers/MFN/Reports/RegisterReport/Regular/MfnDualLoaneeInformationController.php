<?php

namespace App\Http\Controllers\MFN\Reports\RegisterReport\Regular;
use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\MFN\Samity;

use App\Model\MFN\LoanProductCategory;
use App\Model\MFN\LoanProduct;

use App\Model\MFN\Loan;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DateTime;
use App\Services\HrService;
use App\Services\MfnService;

class MfnDualLoaneeInformationController extends Controller
{

    public function index()
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();
        $branchList = Branch::where('is_delete', 0)
            ->where('id', '>', 1)
            ->whereIn('id', $accessAbleBranchIds)
            ->orderBy('branch_name')
            ->select('id', 'branch_name')
            ->get();
        // $FundindOrg = DB::table('mfn_funding_orgs')->where('is_delete', 0)->get();
        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
        $LoanProduct = LoanProduct::get();
        $LoanProductCatagory = LoanProductCategory::get();
        $data = array(
            "branchList"       => $branchList,
            "sysDate"           => $sysDate,
            "LoanProduct" => $LoanProduct,
            "LoanProductCatagory" => $LoanProductCatagory,
            // "FundindOrg"        => $FundindOrg,
        );
        return view('MFN.Reports.RegisterReport.Regular.DualLoneeInfo.index', $data);
    }
    public function getData(Request $req)
    {


        try {
            $RequestData = $req->all();



                $date = new Datetime($RequestData['date']);
                $date = $date->format('Y-m-d');
                // $date_to = new Datetime($RequestData['date_to']);
                // $date_to = $date_to->format('Y-m-d');
                
    
                $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
                $samityData = Samity::where('branchId',$RequestData['branch'])->get();
                $loanproduct = LoanProduct::get();
    
                $selected_samity = $RequestData['samity'];
    
                $branchData = Branch::where('gnl_branchs.id', $RequestData['branch'])
                    ->select('gnl_branchs.*', 'gnl_companies.*')
                    ->leftJoin('gnl_companies', 'gnl_companies.id', 'gnl_branchs.company_id')->first();
                    
                $product_id = $RequestData['product'];
                $category_id = $RequestData['category'];
                $LoanData = Loan::where('mfn_loans.branchId', $RequestData['branch'])
                    ->where(function ($LoanData) use ($selected_samity, $date) {
    
                        if (!empty($selected_samity)) {
    
                            $LoanData->where('mfn_loans.samityId', $selected_samity);
                        }
    
                        if (!empty($date)) {
    
                            $date = new DateTime($date);
                            $date = $date->format('Y-m-d');
    
                            $LoanData->where('mfn_loans.disbursementDate', $date);
                        }
                    })
                    ->leftJoin('mfn_loan_products', 'mfn_loan_products.id', 'mfn_loans.productId')
                    ->selectRaw('mfn_loans.samityId, mfn_loans.productId, mfn_loans.memberId, mfn_loans.loanCode, mfn_loans.loanAmount, mfn_loans.disbursementDate, mfn_loan_products.productCategoryId')
                    ->groupBy('mfn_loans.memberId')
        
                    ->where(function ($LoanData) use ($product_id, $category_id) {
    
                        if (!empty($product_id)) {
    
                            $LoanData->where('mfn_loans.productId', $product_id);
                        }
                        if (!empty($category_id)) {
    
                            $LoanData->where('mfn_loan_products.productCategoryId', $category_id);
                        }
                    })
                    ->havingRaw('count(mfn_loans.memberId) > ?', [1])
                    ->get();
    
    
                   $memberIds = $LoanData->pluck('memberId')->toArray();

                   $LoanData = Loan::where('mfn_loans.branchId', $RequestData['branch'])
                   ->whereIn('memberId',$memberIds)
                   ->where(function ($LoanData) use ($selected_samity, $date) {
   
                       if (!empty($selected_samity)) {
   
                           $LoanData->where('mfn_loans.samityId', $selected_samity);
                       }
   
                       if (!empty($date)) {
   
                           $date = new DateTime($date);
                           $date = $date->format('Y-m-d');
   
                           $LoanData->where('mfn_loans.disbursementDate', $date);
                       }
                   })
                   ->leftJoin('mfn_loan_products', 'mfn_loan_products.id', 'mfn_loans.productId')
                   ->leftJoin('mfn_members', 'mfn_members.id', 'mfn_loans.memberId')
                   ->selectRaw('mfn_loans.id ,mfn_loans.samityId, mfn_loans.productId, mfn_members.name as m_name, mfn_members.memberCode as m_code, mfn_loans.memberId, mfn_loans.loanCode, mfn_loans.loanAmount, mfn_loans.disbursementDate, mfn_loan_products.productCategoryId')
                   ->where(function ($LoanData) use ($product_id, $category_id) {
   
                       if (!empty($product_id)) {
   
                           $LoanData->where('mfn_loans.productId', $product_id);
                       }
                       if (!empty($category_id)) {
   
                           $LoanData->where('mfn_loan_products.productCategoryId', $category_id);
                       }
                   })
                   ->orderBy('samityId')
                   ->orderBy('memberId')
                   ->get();

    
    
                $data = array(
                    'LoanData' => $LoanData,
                    'branchData'  => $branchData,
                    'samityData' => $samityData,
                    'samity_selected' =>$selected_samity,
                    'loanproduct' => $loanproduct,
                    'sysDate' => $sysDate,
                    'FromDate' => $date,
                );
                return view('MFN.Reports.RegisterReport.Regular.DualLoneeInfo.viewreportbranch', $data);

            

            
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
