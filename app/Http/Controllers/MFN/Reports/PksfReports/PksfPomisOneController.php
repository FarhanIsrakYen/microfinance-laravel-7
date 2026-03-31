<?php

namespace App\Http\Controllers\MFN\Reports\PksfReports;
use App\Services\HrService;
use App\Model\GNL\Branch;
use App\Model\MFN\LoanProductCategory;
use App\Model\MFN\MonthEndSavings;

use App\Http\Controllers\Controller;
use Auth;
use Carbon\Carbon;
use DB;
use Datetime;
use Illuminate\Http\Request;
use App\Services\MfnService;

class PksfPomisOneController extends Controller
{
    public function index(){

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
            return view('MFN.Reports.Pksf.PomisOne.index', $data);
        
    }

    public function getData(Request $req)
    {


        try {

            $RequestData = $req->all();

            $branch = $RequestData['branch'];
            $year = $RequestData['year'];
            $month = $RequestData['month'];
            $branchData = Branch::where('gnl_branchs.id', $RequestData['branch'])
                    ->select('gnl_branchs.*', 'gnl_companies.*')
                    ->leftJoin('gnl_companies', 'gnl_companies.id', 'gnl_branchs.company_id')->first();
            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
            // $samityData = Samity::where('branchId',$RequestData['branch'])->get();
            $queryData = MonthEndSavings::
            
                        where(function ($queryData) use ( $branch) {
                            if (!empty($branch)) {
                                $queryData->where('branchId', '=', $branch);
                            }
                        })->where(function ($queryData) use ($year,$month) {
                            if (!empty($year) && !empty($month)) {

                                $date = new DateTime ($year.'-'.$month);
                                $date->modify('last day of this month');
                                $date->format('Y-m-d');
                                $queryData->where('date', '=', $date);
                            }
                        })
                        ->leftJoin('mfn_loan_products', 'mfn_loan_products.id', 'mfn_month_end_savings.loanProductId')
                        ->leftJoin('mfn_funding_orgs', 'mfn_funding_orgs.id', 'mfn_loan_products.fundingOrgId')
                        ->leftJoin('mfn_savings_product', 'mfn_savings_product.id', 'mfn_month_end_savings.savingsProductId')
                        ->select('mfn_month_end_savings.*', 'mfn_loan_products.name as Lname','mfn_savings_product.name as SPname','mfn_loan_products.fundingOrgId','mfn_funding_orgs.name as PksfName')
                        ->get();

             $pksfIDs = array_unique($queryData->pluck('fundingOrgId')->toArray());  
             $pksfIDs = collect($pksfIDs);
             




            $data = array(
               'branchData' => $branchData,
                'pksfIDs' => $pksfIDs,
                'sysDate' => $sysDate,
                'queryData' => $queryData,
            );
            
            return view('MFN.Reports.Pksf.PomisOne.viewreports', $data);
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
