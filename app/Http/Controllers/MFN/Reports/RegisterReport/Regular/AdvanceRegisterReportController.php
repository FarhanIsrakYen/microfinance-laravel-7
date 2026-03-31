<?php

namespace App\Http\Controllers\MFN\Reports\RegisterReport\Regular;
use App\Services\MfnService;
use App\Services\HrService;

use App\Http\Controllers\Controller;
use App\Model\MFN\Samity;
use App\Model\GNL\Branch;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AdvanceRegisterReportController extends Controller
{
    
    public function index(Request $req)
    {
        $branchId = ($req->branch_id) ? $req->branch_id : Auth::user()->branch_id;

        $branchData = DB::table('gnl_branchs as b')
                        ->where([['is_delete', 0], ['is_approve', 1], ['is_active', 1], ['id', '>', 1]])
                        ->whereIn('id', HrService::getUserAccesableBranchIds())
                        ->select('b.id', 'b.branch_name', 'b.branch_code')
                        ->get();

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
                        ->select('id','shortName')
                        ->get();

        $loanProductData = DB::table('mfn_loan_products')
                        ->where([['is_delete', 0]])
                        ->select('id','shortName')
                        ->get();

        $data = array(
            "branchData"           => $branchData,
            "fieldOfficerData"     => $fieldOfficerData,
            "fundingOrgData"       => $fundingOrgData,
            "loanProdCategoryData" => $loanProdCategoryData,
            "loanProductData"      => $loanProductData,
        );

   return view('MFN.Reports.RegisterReport.Regular.AdvanceRegister.index', $data);


        // // test purpose dont delete code /////

        //         $req = new Request;

        //         $req->branch_id = 5;
        //         $req->company_id = 1;
        //         $req->voucher_type_id = 3;
        //         $req->voucher_code = 'JV0001000100001';
        //         $req->project_id = 1;
        //         $req->project_type_id = 1;
        //         $req->voucher_date = MfnService::systemCurrentDate(Auth::user()->branch_id);
        //         $req->global_narration = 'global nar';
        //         $req->global_narration = 1;

        //         $req->debit_arr = array("33", "334", "233");
        //         $req->credit_arr = array("31", "314", "211");

        //         $req->amount_arr = array("222", "222", "21");

        //         $req->narration_arr = array("200n", "250n", "300n");
        //         $dara = AccService::insertVoucher($req);


    }

    public function loadReportData(Request $req)
    {
        // $selected_samity = $req->samity;

        $branchId = (empty($req->input('branchId'))) ? null : $req->input('branchId');
        
        $endDate = date('Y-m-d', strtotime($req->input('endDate')));

        $fieldOfficer = (empty($req->input('fieldOfficer'))) ? null : $req->input('fieldOfficer');

        $fundingOrg = (empty($req->input('fundingOrg'))) ? null : $req->input('fundingOrg');

        $loanProdCategory = (empty($req->input('loanProdCategory'))) ? null : $req->input('loanProdCategory');

        $loanProduct = (empty($req->input('loanProduct'))) ? null : $req->input('loanProduct');

        $serviceCharge = (empty($req->input('serviceCharge'))) ? null : $req->input('serviceCharge');

        $prodCatWise = (empty($req->input('prodCatWise'))) ? null : $req->input('prodCatWise');


        $loans = DB::table('mfn_samity as ms')
            ->where([['ms.is_delete', 0], ['ms.branchId', $branchId]])
            ->join('mfn_members as mm', function ($loans) {
                $loans->on('mm.samityId', '=', 'ms.id')
                    ->where([['mm.is_delete', 0]]);
            })
            ->join('mfn_loans as ml', function ($loans) {
                $loans->on('ml.memberId', '=', 'mm.id')
                    ->where([['ml.is_delete', 0]]);
            })
            ->where(function ($loans) use ($fieldOfficer) {
                if (!empty($fieldOfficer)) {
                    $loans->where('ms.fieldOfficerEmpId', '=', $fieldOfficer);
                }
            })
            ->join('mfn_loan_products as lp', function ($loans) {
                $loans->on('ml.productId', '=', 'lp.id')
                    ->where([['lp.is_delete', 0]]);
            })
            ->join('mfn_loan_product_category as lc', function ($loans) {
                $loans->on('lp.productCategoryId', '=', 'lc.id')
                    ->where([['lc.is_delete', 0]]);
            })
            ->where(function ($loans) use ($fundingOrg) {
                if (!empty($fundingOrg)) {
                    $loans->where('lp.fundingOrgId', '=', $fundingOrg);
                }
            })
            ->where(function ($loans) use ($loanProdCategory) {
                if (!empty($loanProdCategory)) {
                    $loans->where('lp.productCategoryId', '=', $loanProdCategory);
                }
            })
            ->where(function ($loans) use ($loanProduct) {
                if (!empty($loanProduct)) {
                    $loans->where('ml.productId', '=', $loanProduct);
                }
            })
            ->orderBy('ml.memberId')
            ->orderBy('lp.isPrimaryProduct', 'desc');

        if ($serviceCharge == 1) {
            $loans->select('ml.id', 'ml.memberId', 'ml.loanCode', 'ml.disbursementDate', 'ms.name as samity', 'mm.memberCode', 'mm.name as member', 'lp.shortName as component', 'lc.shortName as category', DB::Raw('IFNULL( ml.repayAmount , 0 ) as disburseAmount'));
        } else {
            $loans->select('ml.id', 'ml.memberId', 'ml.loanCode', 'ml.disbursementDate', 'ms.name as samity', 'mm.memberCode', 'mm.name as member', 'lp.shortName as component', 'lc.shortName as category', DB::Raw('IFNULL( ml.loanAmount , 0 ) as disburseAmount'));
        }

        $loans = $loans->get();

        $ttl_disburse_amount = 0;
        $ttl_loan_amount = 0;
        $ttl_advance_amount = 0;
        $ttl_sav_balance = 0;
        $ttl_member_claim = 0;
        $ttl_org_claim = 0;

        $ttl_disburse = 0;
        $ttl_loan = 0;
        $ttl_advance = 0;

        if (count($loans) > 0) {
            
            $loanStatuses = Mfnservice::getLoanStatus($loans->pluck('id')->toArray(), $endDate);
            // $loanStatuses = Mfnservice::getLoanStatus([16623], $endDate);
            $loanStatuses = collect($loanStatuses)->where('advanceAmount', '>', 0);
        
            $loans = $loans->whereIn('id', $loanStatuses->pluck('loanId')->toArray());


            foreach ($loans as $key => $loan) {
                $loans[$key]->disbursementDate = date('d-m-Y', strtotime($loan->disbursementDate));
                if ($serviceCharge == 1) {
                    $loans[$key]->advanceAmount = $loanStatuses->where('loanId', $loan->id)->first()['advanceAmount'];
                    $loans[$key]->loanAmount = $loanStatuses->where('loanId', $loan->id)->first()['outstanding'];
                } else {
                    $loans[$key]->advanceAmount = $loanStatuses->where('loanId', $loan->id)->first()['advanceAmountPrincipal'];
                    $loans[$key]->loanAmount = $loanStatuses->where('loanId', $loan->id)->first()['outstandingPrincipal'];
                }
            }
            $ttl_disburse_amount = $loans->sum('disburseAmount');
            $ttl_loan_amount     = $loans->sum('loanAmount');
            $ttl_advance_amount      = $loans->sum('advanceAmount');

            $sL = 1;
            if ($prodCatWise == 1) {
                $productData = array();
                foreach ($loans->groupBy('component') as $key => $row) {
                    $tempSet = array();

                    $tempSet = [
                        'sL'      => $sL++,
                        'product' => $key,
                        'disburse' => number_format($row->sum('disburseAmount'), 2),
                        'loan'    => number_format($row->sum('loanAmount'), 2),
                        'advance'  => number_format($row->sum('advanceAmount'), 2),
                    ];
                    $ttl_disburse += $row->sum('disburseAmount');
                    $ttl_loan += $row->sum('loanAmount');
                    $ttl_advance += $row->sum('advanceAmount');

                    $productData[] = $tempSet;
                }
            } else {
                $productData = array();
                foreach ($loans->groupBy('category') as $key => $row) {
                    $tempSet = array();

                    $tempSet = [
                        'sL'      => $sL++,
                        'product' => $key,
                        'disburse' => number_format($row->sum('disburseAmount'), 2),
                        'loan'    => number_format($row->sum('loanAmount'), 2),
                        'advance'     => number_format($row->sum('advanceAmount'), 2),
                    ];
                    $ttl_disburse += $row->sum('disburseAmount');
                    $ttl_loan += $row->sum('loanAmount');
                    $ttl_advance += $row->sum('advanceAmount');

                    $productData[] = $tempSet;
                }
            }
            
            $collection = collect($loans)->groupBy('samity');
            $json_data = array(
                "draw"                => intval($req->input('draw')),
                "data"                => $collection,
                'ttl_disburse_amount' => number_format($ttl_disburse_amount, 2),
                'ttl_loan_amount'     => number_format($ttl_loan_amount, 2),
                'ttl_advance_amount'      => number_format($ttl_advance_amount, 2),
                'ttl_sav_balance'     => number_format($ttl_sav_balance, 2),
                'ttl_member_claim'    => number_format($ttl_member_claim, 2),
                'ttl_org_claim'       => number_format($ttl_org_claim, 2),
                "prodCatData"         => $productData,
                'ttl_disburse'        => number_format($ttl_disburse, 2),
                'ttl_loan'            => number_format($ttl_loan, 2),
                'ttl_advance'         => number_format($ttl_advance, 2),
                
            );
            echo json_encode($json_data);
        } else {
            $json_data = array(
                "draw"                => intval($req->input('draw')),
                "data"                => '',
                'ttl_disburse_amount' => number_format($ttl_disburse_amount, 2),
                'ttl_loan_amount'     => number_format($ttl_loan_amount, 2),
                'ttl_advance_amount'      => number_format($ttl_advance_amount, 2),
                'ttl_sav_balance'     => number_format($ttl_sav_balance, 2),
                'ttl_member_claim'    => number_format($ttl_member_claim, 2),
                'ttl_org_claim'       => number_format($ttl_org_claim, 2),
                "prodCatData"         => '',
                'ttl_disburse'        => number_format($ttl_disburse, 2),
                'ttl_loan'            => number_format($ttl_loan, 2),
                'ttl_advance'             => number_format($ttl_advance, 2),
            );
            echo json_encode($json_data);
        }
    }

    public function loadReportData2(Request $req)
    {
        try {
            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
            $branchId = $req->branchId; 
            $branchData = Branch::where('gnl_branchs.id',$req->branchId)
            ->select('gnl_branchs.*', 'gnl_companies.*')
            ->leftJoin('gnl_companies', 'gnl_companies.id', 'gnl_branchs.company_id')->first();
            $endDate =$req->end_date; 

            $fundingOrg = $req->funding_org; 

            $loanProdCategory = $req->loan_prod_cat; 

            $loanProduct = $req->loan_product; 

            $serviceCharge = $req->service_charge; 

            $prodCatWise = $req->prod_cat_wise; 
            $selected_samity = $req->samity;
            $samityData = Samity::where('branchId',$branchId)->get();

            $loans = DB::table('mfn_samity as ms')
                    ->where([['ms.is_delete', 0],['ms.branchId',$branchId]])
                    ->join('mfn_members as mm', function ($loans){
                        $loans->on('mm.samityId', '=', 'ms.id')
                            ->where([['mm.is_delete', 0]]);
                    })
                    ->join('mfn_loans as ml', function ($loans){
                        $loans->on('ml.memberId', '=', 'mm.id')
                            ->where([['ml.is_delete', 0]]);
                    })
                    ->join('mfn_loan_products as lp', function ($loans){
                        $loans->on('ml.productId', '=', 'lp.id')
                            ->where([['lp.is_delete', 0]]);
                    })
                    ->join('mfn_loan_product_category as lc', function ($loans){
                        $loans->on('lp.productCategoryId', '=', 'lc.id')
                            ->where([['lc.is_delete', 0]]);
                    })
                    ->where(function ($loans) use ($fundingOrg) {
                        if (!empty($fundingOrg)) {
                            $loans->where('lp.fundingOrgId', '=', $fundingOrg);
                        }
                    })
                    ->where(function ($loans) use ($loanProdCategory) {
                        if (!empty($loanProdCategory)) {
                            $loans->where('lp.productCategoryId', '=', $loanProdCategory);
                        }
                    })
                    ->where(function ($loans) use ($loanProduct) {
                        if (!empty($loanProduct)) {
                            $loans->where('ml.productId', '=', $loanProduct);
                        }
                    })
                    ->where(function ($loans) use ($endDate) {
                        if (!empty($endDate)) {
                            $date =Carbon::parse($endDate)->format('Y-m-d');
                            
                            // $endDate = $endDate->format('Y-m-d');
                            $loans->where('ml.disbursementDate', '<=', $date);
                          
                        }
                    })
                    ->select('ml.id','ml.memberId','ml.loanCode','mm.samityId','ml.disbursementDate','ms.name as samity','mm.memberCode','mm.name as member','lp.shortName as component','lc.shortName as category', DB::Raw('IFNULL( ml.repayAmount , 0 ) as disburseAmount'))
                    ->orderBy('mm.samityId')
                    ->orderBy('ml.memberId')
                    ->orderBy('lp.isPrimaryProduct','desc')
                    ->get();
     
           

                $loanStatuses = Mfnservice::getLoanStatus($loans->pluck('id')->toArray(), $endDate);
                $loanStatuses = collect($loanStatuses);
                $loans = $loans->whereIn('id', $loanStatuses->pluck('loanId')->toArray());
                
                

                foreach ($loans as $key => $loan) {
                    if ($serviceCharge == 1) {
                        $loans[$key]->adAmount = $loanStatuses->where('loanId', $loan->id)->first()['advanceAmount'];
                        $loans[$key]->loanAmount = $loanStatuses->where('loanId', $loan->id)->first()['outstanding'];
                    }
                    else {
                        $loans[$key]->adAmount = $loanStatuses->where('loanId', $loan->id)->first()['advanceAmountPrincipal'];
                        $loans[$key]->loanAmount = $loanStatuses->where('loanId', $loan->id)->first()['outstandingPrincipal'];
                    }

                   
                }

          
                if(!empty( $selected_samity ) && !empty($branchId)){
                    $data = array(
                        'loans' => $loans,
                        'loanStatuses' =>$loanStatuses,
                        'branchData'  => $branchData,
                        'FromDate' => $endDate,
                        'samity_selected' =>$selected_samity,
                        'samityData' => $samityData,
                        'sysDate' => $sysDate,
                        'prodCatWise' => $prodCatWise,
                        'toDate' => $endDate,
                    );
                    return view('MFN.Reports.RegisterReport.Regular.AdvanceRegister.viewreportsamity', $data);
                }else{
                    $data = array(
                        'loans' => $loans,
                        'loanStatuses' =>$loanStatuses,
                        'branchData'  => $branchData,
                        'FromDate' => $endDate,
                        'samity_selected' =>$selected_samity,
                        'samityData' => $samityData,
                        'sysDate' => $sysDate,
                        'prodCatWise' => $prodCatWise,
                        'toDate' => $endDate,
                    );
                    return view('MFN.Reports.RegisterReport.Regular.AdvanceRegister.viewreportbranch', $data);
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



    public function getData(Request $req)
    {
        if($req->operation == "GetSamity"){
            $samities = DB::table('mfn_samity')
                    ->where([['is_delete',0],['branchId', $req->branchId]])
                    ->get();

            $data = array(
                'samities' => $samities,
            );
            return response()->json($data);
        }
        elseif($req->operation == "GetProduct"){
            $loanProducts= MfnService::getBranchAssignedLoanProductIds($req->branchId);
            $products = DB::table('mfn_loan_products')
                        ->whereIn('id', $loanProducts)    
                        ->where('is_delete', 0);

            if($req->fundingOrgId){
                $products = $products->where('fundingOrgId', $req->fundingOrgId) ;
            }
            if($req->prodCatId){
                $products = $products->where('productCategoryId', $req->prodCatId) ;
            }

            $data = array(
                'product' => $products->get(),
            );
            
            return response()->json($data);
        }
    }


}
