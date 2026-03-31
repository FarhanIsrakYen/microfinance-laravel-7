<?php

namespace App\Http\Controllers\MFN\Reports\RegularGeneralReports;

use App\Http\Controllers\Controller;
use App\Services\HrService;
use App\Services\MfnService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use DateTime;

class PCRComponentWiseReport extends Controller
{
    public function getPCRComponentWiseFilterPart(Request $req)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        if (Auth::user()->branch_id == 1) {
            $branchs = DB::table('gnl_branchs as b')
                ->where([['is_delete', 0], ['is_approve', 1], ['is_active', 1], ['id', '>', 1]])
                ->whereIn('id', HrService::getUserAccesableBranchIds())
                ->select('b.id', 'b.branch_name', 'b.branch_code', DB::raw("CONCAT(b.branch_code,' - ',b.branch_name) as branch"))
                ->get();
        }

        $group = DB::table('gnl_groups')
            ->where([['is_delete', 0], ['is_active', 1]])
            ->select('id', 'group_name')
            ->first();

        $branch = DB::table('gnl_branchs')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
                ['id', Auth::user()->branch_id],
            ])
            ->select('id', 'branch_name')
            ->first();

        $fundingOrg = DB::table('mfn_funding_orgs as forg')
            ->where('is_delete', 0)
            ->select('id', 'name', DB::raw("CONCAT(forg.id,' - ',forg.name) as fundingOrg"))
            ->get();

        $prodCat = DB::table('mfn_loan_product_category as mlpc')
            ->where('is_delete', 0)
            ->select('id', 'name', DB::raw("CONCAT(mlpc.id,' - ',mlpc.name) as prodCat"))
            ->get();

        $products = DB::table('mfn_loan_products as mlp')
                ->where('is_delete', 0)
                ->select('id', 'name', DB::raw("CONCAT(mlp.id,' - ',mlp.name) as product"))
                ->get();

        $data = array(
            'sysDate'    => Carbon::parse($sysDate)->format('d-m-Y'),
            'branchs'    => (Auth::user()->branch_id == 1) ? $branchs : Auth::user()->branch_id,
            'fundingOrg' => $fundingOrg,
            'prodCat' => $prodCat,
            'products' => $products,
            'groupName'  => $group->group_name,
            'branchName' => $branch->branch_name,

        );

        return view('MFN.Reports.RegularGeneralReports.pcr_component_wise', $data);
    }

    public function getPCRComponentWiseViewPart(Request $req)
    {
        // Searching variable
        $startDate = (is_null($req->fromDate)) ? null : $req->fromDate;
        $endDate   = (is_null($req->toDate)) ? null : $req->toDate;
        $branchId  = (is_null($req->branchId)) ? null : $req->branchId;
        $fundingOrg  = (is_null($req->fundingOrg)) ? null : $req->fundingOrg;
        $productCategories  = (is_null($req->productCategories)) ? null : $req->productCategories;
        $product  = (is_null($req->product)) ? null : $req->product;


        $savingsProduct = DB::table('mfn_savings_product')
            ->where('is_delete', 0)
            ->select('id', 'shortName')
            ->get();

        $countSP = $savingsProduct->count();
        $th      = '';
        foreach ($savingsProduct as $key => $row) {
            $th .= '<th rowspan="2">' . $row->shortName . '</th> ';
        }

        $PCRComWiseData = DB::table('mfn_samity as ms')
            ->where([['ms.is_delete', 0], ['ms.branchId', $branchId]])
            ->select('ms.id as samityId', 'he.emp_code', 'he.emp_name', 'ms.samityCode', 'ms.name as samityName', 'mlp.shortName as component', 'msp.shortName as savingProdName', 'msd.savingsProductId', 'mlc.amount as lrTotal', DB::raw('SUM(msd.amount) as savingAmount, SUM(IF(msd.transactionTypeId = 3, msd.amount, 0)) as savingInterestAmount, SUM(msw.amount) as savingRefund, SUM(mld.additionalFee) as additionalFee, SUM(ml.loanAmount) as disbursementAmount, SUM(IF(mlc.paymentType = "Rebate", mlc.amount, 0)) as rebateAmount, IFNULL(SUM(mlc.principalAmount), "-") as lrPrincipal, IFNULL(SUM(mlc.interestAmount), "-") as lrServiceCharge'))
            ->leftjoin('hr_employees as he', 'ms.fieldOfficerEmpId', 'he.id')
            ->leftjoin('mfn_savings_deposit as msd', 'ms.id', 'msd.samityId')
            ->leftjoin('mfn_savings_withdraw as msw', 'ms.id', 'msw.samityId')
            ->leftjoin('mfn_loans as ml', 'ms.id', 'ml.samityId')
            ->leftjoin('mfn_loan_collections as mlc', 'ms.id', 'mlc.samityId')
            ->leftjoin('mfn_loan_details as mld', 'ml.id', 'mld.loanId')
            ->leftjoin('mfn_loan_products as mlp', 'msd.primaryProductId', 'mlp.id')
            ->leftjoin('mfn_savings_product as msp', 'msd.savingsProductId', 'msp.id')
            ->where(function ($PCRComWiseData) use ($startDate, $endDate, $branchId, $fundingOrg, $productCategories, $product) {

                if (!is_null($startDate) && !is_null($endDate)) {

                    $startDate = new DateTime($startDate);
                    $startDate = $startDate->format('Y-m-d');

                    $endDate = new DateTime($endDate);
                    $endDate = $endDate->format('Y-m-d');

                    $PCRComWiseData->whereBetween('ms.created_at', [$startDate, $endDate]);
                }

                if (!is_null($branchId)) {
                    $PCRComWiseData->where('ms.branchId', $branchId);
                }

                if (!is_null($fundingOrg)) {
                    $PCRComWiseData->where('mlp.fundingOrgId', $fundingOrg);
                }

                if (!is_null($productCategories)) {
                    $PCRComWiseData->where('mlp.productCategoryId', $productCategories);
                }

                if (!is_null($product)) {
                    $PCRComWiseData->where('mlp.id', $product);
                }
            })
            ->groupBy('msd.samityId', 'msd.branchId', 'msd.savingsProductId', 'msw.samityId', 'msw.branchId', 'msw.savingsProductId', 'mlc.samityId', 'mlc.branchId')
            ->orderBy('he.id', 'ASC')
            ->get();

        $pcrData = array();

        foreach ($PCRComWiseData as $key => $row) {

            $samitysLoanIds = DB::table('mfn_loans')
                ->where([['is_delete', 0], ['samityId', $row->samityId]])
                ->pluck('id')
                ->toArray();

            $loanCollnData = mfnService::getLoanStatus($samitysLoanIds, $startDate, $endDate);

            $regularRecoAmt = 0;
            $regularAmt     = 0;
            $dueAmt         = 0;
            $advanceAmt     = 0;

            foreach ($loanCollnData as $value) {
                $regularRecoAmt += $value['onPeriodPayable'];
                $regularAmt += $value['onPeriodReularCollection'];
                $dueAmt += $value['dueAmount'];
                $advanceAmt += $value['advanceAmount'];
            }

            $pcrData[$row->emp_code . '-' . $row->emp_name]['emp_code']                                                        = $row->emp_code;
            $pcrData[$row->emp_code . '-' . $row->emp_name]['emp_name']                                                        = $row->emp_name;
            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['samityCode'] = $row->samityCode;
            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['samityName'] = $row->samityName;
            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['component']  = $row->component;

            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['savingProduct'][$row->savingsProductId . '-' . $row->savingProdName]        = $row->savingAmount;
            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['savingInterestAmount'][$row->savingsProductId . '-' . $row->savingProdName] = $row->savingInterestAmount;
            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['savingRefund'][$row->savingsProductId . '-' . $row->savingProdName]         = $row->savingRefund;

            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['additionalFee']      = $row->additionalFee;
            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['disbursementAmount'] = $row->disbursementAmount;
            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['regularRecoAmt']     = $regularRecoAmt;

            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['regular'] = $regularAmt;
            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['due']     = $dueAmt;
            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['advance'] = $advanceAmt;
            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['rebate']  = $row->rebateAmount;

            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['lrPrincipal']     = $row->lrPrincipal;
            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['lrServiceCharge'] = $row->lrServiceCharge;
            $pcrData[$row->emp_code . '-' . $row->emp_name]['samity'][$row->samityCode . '-' . $row->samityName]['lrTotal']         = $row->lrTotal;
        }

        $data = array(
            'savingProduct' => $countSP,
            'th'            => $th,
            'pcrData'       => $pcrData,
        );

        return view('MFN.Reports.RegularGeneralReports.pcrcw_table_view', $data);
    }

    public function getData(Request $req)
    {
        if ($req->context == 'product') {

            $products = DB::table('mfn_loan_products as mlp')
                ->where([['is_delete', 0], ['productCategoryId', $req->prodCatId]])
                ->select('id', 'name', DB::raw("CONCAT(mlp.id,' - ',mlp.name) as product"))
                ->get();
            
            $data = array(
                'products' => $products,
            );
        }

        return response()->json($data);
    }

    public function loadReportData(Request $req)
    {
        
        // $req->options -> Load Product=1, Load Product category=2
        try {
            $RequestData = $req->all();
            
  
            $branchId = (empty($RequestData['branchId'])) ? null : $RequestData['branchId'];
            $fundingOrg = (empty($RequestData['fundingOrg'])) ? null : $RequestData['fundingOrg'];
            $productCategory = (empty($RequestData['productCategories'])) ? null : $RequestData['productCategories'];
            $product = (empty($RequestData['product'])) ? null : $RequestData['product'];
            $fromDate = (empty($RequestData['fromDate'])) ? null : $RequestData['fromDate'];
            $toDate = (empty($RequestData['toDate'])) ? null : $RequestData['toDate'];
            $options = (empty($RequestData['options'])) ? null : $RequestData['options'];
            
            //queery
            //SELECT s.name,s.samityCode , emp.employee_no, emp.emp_name FROM `mfn_samity` as s, hr_employees as emp WHERE s.`fieldOfficerEmpId` = s.id GROUP BY s.fieldOfficerEmpId

            $samityData = (empty($RequestData['branchId'])) ? null : DB::table('mfn_samity as ms')
                                                                    ->where([['ms.is_delete', 0], ['ms.branchId', $branchId]])
                                                                    ->leftjoin('hr_employees as he', 'ms.fieldOfficerEmpId', 'he.id')
                                                                    ->leftjoin('mfn_branch_products as mbp', 'mbp.branchId', 'ms.branchId')
                                                                    ->groupBy('fieldOfficerEmpId')
                                                                    ->select('he.id', 'he.emp_name', 'ms.name', 'ms.samityCode','mbp.savingProductIds','mbp.loanProductIds')
                                                                    ->orderBy('he.emp_name', 'ASC')
                                                                    ->get();

            if($samityData){
                $employeeIds= $samityData->pluck('emp_id')->toArray();
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
