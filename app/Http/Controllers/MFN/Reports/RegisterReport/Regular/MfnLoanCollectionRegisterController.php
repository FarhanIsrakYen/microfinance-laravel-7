<?php

namespace App\Http\Controllers\MFN\Reports\RegisterReport\Regular;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\MFN\Samity;

use App\Model\MFN\LoanProductCategory;
use App\Model\MFN\LoanProduct;

use App\Model\MFN\LoanCollection;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use DateTime;
use App\Services\HrService;
use App\Services\MfnService;
use DB;

class MfnLoanCollectionRegisterController extends Controller
{

    public function index()
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();
        $branchList = Branch::where('is_delete', 0)
            ->where('id', '>', 1)
            ->whereIn('id', $accessAbleBranchIds)
            ->orderBy('branch_code')
            ->select(DB::raw("id, CONCAT(branch_code, ' - ', branch_name) AS branch_name"))
            ->get();

        if (count($branchList) > 1) {
            $samities = [];
        } else {
            $samities = DB::table('mfn_samity')
                ->where('is_delete', 0)
                ->whereIn('branchId', $accessAbleBranchIds)
                ->select(DB::raw("id, CONCAT(samityCode,' - ', name) AS name"))
                ->get();
        }
        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
        $LoanProduct = LoanProduct::get();
        $LoanProductCatagory = LoanProductCategory::get();
        
        $data = array(
            "branchList"            => $branchList,
            "samities"              => $samities,
            "sysDate"               => $sysDate,
            "LoanProduct"           => $LoanProduct,
            "LoanProductCatagory"   => $LoanProductCatagory,
        );
        return view('MFN.Reports.RegisterReport.LoanCollectionRegister.index', $data);
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
            $LoanCollectionData = LoanCollection::where('mfn_loan_collections.branchId', $RequestData['branch'])
                ->where(function ($LoanCollectionData) use ($selected_samity, $date, $date_to) {

                    if (!empty($selected_samity)) {

                        $LoanCollectionData->where('mfn_loan_collections.samityId', $selected_samity);
                    }

                    if (!empty($date) && !empty($date_to)) {

                        $date = new DateTime($date);
                        $date = $date->format('Y-m-d');

                        $date_to = new DateTime($date_to);
                        $date_to = $date_to->format('Y-m-d');

                        $LoanCollectionData->whereBetween('mfn_loan_collections.collectionDate', [$date, $date_to]);
                    }
                })
                // ->select('mfn_loan_collections.*', 'mfn_loans.productId')
                ->leftJoin('mfn_loans', 'mfn_loans.id', 'mfn_loan_collections.loanId')
                ->leftJoin('mfn_loan_products', 'mfn_loan_products.id', 'mfn_loans.productId')
                ->selectRaw('mfn_loan_collections.samityId, mfn_loans.productId, mfn_loan_products.productCategoryId,  SUM(mfn_loan_collections.amount) as amount , SUM(mfn_loan_collections.principalAmount) as principalAmount, SUM(mfn_loan_collections.interestAmount) as interestAmount')
                ->groupBy('mfn_loan_collections.samityId')
                ->groupby('mfn_loans.productId')
                ->where(function ($LoanCollectionData) use ($product_id, $category_id) {

                    if (!empty($product_id)) {

                        $LoanCollectionData->where('mfn_loans.productId', $product_id);
                    }
                    if (!empty($category_id)) {

                        $LoanCollectionData->where('mfn_loan_products.productCategoryId', $category_id);
                    }
                })
                ->get();


            $data = array(
                'LoanCollectionData' => $LoanCollectionData,
                'branchData'  => $branchData,
                'samityData' => $samityData,
                'samity_selected' => $selected_samity,
                'loanproduct' => $loanproduct,
                'sysDate' => $sysDate,
                'FromDate' => $date,
                'toDate' => $date_to,
            );
            return view('MFN.Reports.RegisterReport.LoanCollectionRegister.viewreportbranch', $data);


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
