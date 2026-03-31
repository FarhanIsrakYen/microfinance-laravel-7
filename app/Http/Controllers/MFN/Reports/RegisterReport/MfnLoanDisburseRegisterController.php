<?php

namespace App\Http\Controllers\MFN\Reports\RegisterReport;


use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\MFN\Samity;
use App\Model\MFN\Loan;

use App\Model\MFN\LoanProductCategory;
use App\Model\MFN\LoanProduct;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DateTime;
use App\Services\HrService;
use App\Services\MfnService;

class MfnLoanDisburseRegisterController extends Controller
{

    public function index(Request $req)
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
        return view('MFN.Reports.RegisterReport.LoanDisburseRegister.index', $data);
    }
    public function getData(Request $req)
    {


        try {
            $RequestData = $req->all();

            $date = new Datetime($RequestData['date']);
            $date = $date->format('Y-m-d');
            $date_to = new Datetime($RequestData['date_to']);
            $date_to = $date_to->format('Y-m-d');


            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
            $samityData = Samity::where('branchId', $RequestData['branch'])->get();
            $loanproduct = LoanProduct::get();

            $selected_samity = $RequestData['samity'];

            $branchData = Branch::where('gnl_branchs.id', $RequestData['branch'])
                ->select('gnl_branchs.*', 'gnl_companies.*')
                ->leftJoin('gnl_companies', 'gnl_companies.id', 'gnl_branchs.company_id')->first();

            $product_id = $RequestData['product'];
            $category_id = $RequestData['category'];
            $LoanData = Loan::where('mfn_loans.branchId', $RequestData['branch'])
                ->where(function ($LoanData) use ($selected_samity, $date, $date_to) {

                    if (!empty($selected_samity)) {

                        $LoanData->where('mfn_loans.samityId', $selected_samity);
                    }

                    if (!empty($date) && !empty($date_to)) {

                        $date = new DateTime($date);
                        $date = $date->format('Y-m-d');

                        $date_to = new DateTime($date_to);
                        $date_to = $date_to->format('Y-m-d');

                        $LoanData->whereBetween('mfn_loans.disbursementDate', [$date, $date_to]);
                    }
                })
                ->leftJoin('mfn_loan_products', 'mfn_loan_products.id', 'mfn_loans.productId')
                ->selectRaw('mfn_loans.samityId, mfn_loans.productId, mfn_loan_products.productCategoryId,  SUM(mfn_loans.loanAmount) as amount')
                ->groupBy('mfn_loans.samityId')
                ->groupby('mfn_loans.productId')
                ->where(function ($LoanData) use ($product_id, $category_id) {

                    if (!empty($product_id)) {

                        $LoanData->where('mfn_loans.productId', $product_id);
                    }
                    if (!empty($category_id)) {

                        $LoanData->where('mfn_loan_products.productCategoryId', $category_id);
                    }
                })
                ->get();







            $data = array(
                'LoanData' => $LoanData,
                'branchData'  => $branchData,
                'samityData' => $samityData,
                'samity_selected' => $selected_samity,
                'loanproduct' => $loanproduct,
                'sysDate' => $sysDate,
                'FromDate' => $date,
                'toDate' => $date_to,
            );
            return view('MFN.Reports.RegisterReport.LoanDisburseRegister.viewreportbranch', $data);
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
