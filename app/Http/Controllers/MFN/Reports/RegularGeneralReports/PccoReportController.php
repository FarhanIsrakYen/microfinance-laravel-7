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
use App\Model\GNL\Branch;

class PccoReportController extends Controller
{
    //getPCRComponentWiseFilterPart
    public function index(Request $req)
    {
        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
        $branchs=[];
        if (Auth::user()->branch_id == 1) {
            $branchs = DB::table('gnl_branchs as b')
                ->where([['is_delete', 0], ['is_approve', 1], ['is_active', 1], ['id', '>', 1]])
                ->whereIn('id', HrService::getUserAccesableBranchIds())
                ->select('b.id', 'b.branch_name', 'b.branch_code', DB::raw("CONCAT(b.branch_code,' - ',b.branch_name) as branch"))
                ->get();
        }
        else{
            $branchs=[Auth::user()->branch_id];
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
            // 'branchs'    => (Auth::user()->branch_id == 1) ? $branchs : Auth::user()->branch_id,
            'branchs'    => $branchs,
            'fundingOrg' => $fundingOrg,
            'prodCat' => $prodCat,
            'products' => $products,
            'groupName'  => $group->group_name,
            'branchName' => $branch->branch_name,

        );

        return view('MFN.Reports.RegularGeneralReports.PeriodicCollectionComponentWise.periodic_collection_component_wise', $data);
    }

    public function getData(Request $req)
    {
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
            'data' => $products->get(),
        );
        
        return response()->json($data);
    }

    public function loadReportData(Request $req)
    {

        // $req->options -> Load Product=1, Load Product category=2
        try {
            //storing data in variables
            $branchId =  $req->branchId;
            $fundingOrg =  $req->fundingOrg;
            $productCategory = $req->productCategories;
            $product =  $req->product;
            $fromDate = date('Y-m-d', strtotime($req->fromDate));
            $toDate = date('Y-m-d', strtotime($req->toDate));
            $options = $req->options;

            //need to add time range

            //getting deposits
            $deposits = DB::table('mfn_savings_deposit as msp')
                ->where([['msp.is_delete', 0], ['msp.amount', '!=', 0], ['msp.transactionTypeId', '<=', 7], ['msp.branchId', $branchId]])
                ->leftjoin('mfn_loan_products as mlp','mlp.id','msp.primaryProductId')
                ->groupby('msp.samityId');
            
            
            if($product != null){
                $deposits=  $deposits->where('msp.primaryProductId', $product);
            }
            
            $deposits = $deposits->groupby('msp.primaryProductId')
                    ->groupby('msp.savingsProductId')
                    ->groupby('msp.date')
                    ->select(DB::raw("msp.date, msp.samityId, msp.primaryProductId, mlp.productCategoryId as productCategoryId, msp.savingsProductId, SUM(msp.amount) AS amount"))
                    ->get();
            

            $savingsProducts= DB::table('mfn_savings_product')
                              ->whereIn('id',array_unique($deposits->pluck('savingsProductId')->toArray()))
                              ->orderBy('id')
                              ->get();

            //getting withdraws
            $withdraws = DB::table('mfn_savings_withdraw as msw')
                ->where([['msw.is_delete', 0], ['msw.amount', '!=', 0], ['msw.transactionTypeId', '<=', 7], ['msw.branchId', $branchId]])
                ->leftjoin('mfn_loan_products as mlp','mlp.id','msw.primaryProductId')
                ->groupby('msw.samityId');

            
            if($product != null){
                $withdraws = $withdraws->where('primaryProductId', $product);
            }
            $withdraws = $withdraws->groupby('primaryProductId')
                ->groupby('savingsProductId')
                ->groupby('msw.date')
                ->select(DB::raw("msw.date as date, samityId, primaryProductId, mlp.productCategoryId as productCategoryId, savingsProductId, SUM(amount) AS amount"))
                ->get();
            
            
            //getting loans
            $loans = DB::table('mfn_loans as ml')->where([['ml.is_delete', 0], ['ml.branchId', $branchId]]);
            
            if($product != null){
                $loans = $loans->where('productId', $product);
            }

            $loans = $loans->leftjoin('mfn_loan_products as mlp', 'mlp.id', 'ml.productId')
                           ->select('ml.insuranceAmount as insuranceAmount','ml.disbursementDate as disbursementDate','ml.id','ml.loanAmount','mlp.name as name', 'mlp.productCategoryId as productCategoryId', 'ml.samityId as samityId', 'ml.primaryProductId as primaryProductId','ml.productId as productId')
                           ->where('disbursementDate','<=', $toDate)
                           ->where(function($query) use ($fromDate){
                               $query->where('loanCompleteDate','0000-00-00')
                                     ->orWhere('loanCompleteDate','>=',$fromDate);
                           })
                           ->get();
            
            //getting loan collection
            $loanCollection = DB::table('mfn_loan_collections as mlc')
                ->where([['mlc.is_delete', 0], ['mlc.amount', '!=', 0], ['mlc.branchId', $branchId]])
                ->leftjoin('mfn_loans as ml','ml.id','mlc.loanId');
            
            if($product != null){
                $loanCollection = $loanCollection->where('ml.productId', $product);
            }
            
            $loanCollection = $loanCollection->select('mlc.*')->get();
                
            //loan details
            $loanDetails= DB::table('mfn_loan_details')->whereIn('loanId',$loans->pluck('id')->toArray())->get();

            // getting id list of samityID from all above transaction
            $samityIds = array();
            $samityIds = array_merge($samityIds, $deposits->pluck('samityId')->toArray());
            $samityIds = array_merge($samityIds, $withdraws->pluck('samityId')->toArray());
            $samityIds = array_merge($samityIds, $loans->pluck('samityId')->toArray());
            $samityIds = array_merge($samityIds, $loanCollection->pluck('samityId')->toArray());
            $samityIds = array_unique($samityIds);

            //getting the samities where any kind of transaction took place
            $samities = DB::table('mfn_samity as ms')
                ->whereIn('ms.id', $samityIds)
                ->leftjoin('hr_employees as he', 'ms.fieldOfficerEmpId', 'he.id')
                ->groupby('ms.id')
                ->select('ms.*', 'emp_code as fieldOfficerCode', 'emp_name as fieldOfficerName')
                ->get();
                
            //unique field officers
            $filedOfficers=  MfnService::getFieldOfficersByDate($samities->pluck('id')->toArray(),$fromDate, $toDate);

            // getting id list of primaryProductID from all above transaction
            $primaryProductIds = array();
            $primaryProductIds = array_merge($primaryProductIds, $deposits->pluck('primaryProductId')->toArray());
            $primaryProductIds = array_merge($primaryProductIds, $withdraws->pluck('primaryProductId')->toArray());
            $primaryProductIds = array_merge($primaryProductIds, $loans->pluck('productId')->toArray());
            $primaryProductIds = array_merge($primaryProductIds, $loanCollection->pluck('productId')->toArray());
            $primaryProductIds = array_unique($primaryProductIds);

            //getting the loan product associated with above transactions
            //load loan product from mfn service
            $loanProductIds = MfnService::getBranchAssignedLoanProductIds($branchId);
            $loanProducts = DB::table('mfn_loan_products')
                ->where('is_delete', 0)
                ->whereIn('id', $loanProductIds)
                ->orWhereIn('id', $primaryProductIds)
                ->get();
            
           
            $uniqueFieldOfficers = array_map( function($x) { return $x['fieldOfficerId']; },$filedOfficers );
            $uniqueFieldOfficers = array_unique($uniqueFieldOfficers);

            $interests = $deposits->where('transactionTypeId',3);
            $deposits = $deposits->where('transactionTypeId','!=', 3);
            $context = array(
                'samities' => $samities,
                'filedOfficers' => $filedOfficers,
                'loans' => $loans,
                'deposits' => $deposits,
                'interests' => $interests,
                'savingsProducts' => $savingsProducts,
                'withdraws' => $withdraws,
                'loanProducts' => $loanProducts,
                'loanCollection' => $loanCollection,
                'loanDetails' => $loanDetails,
                'uniqueFieldOfficers' => $uniqueFieldOfficers,
                'from' => $fromDate,
                'to' => $toDate
            );
            
            
            if(count($samities) == 0){
                echo 'No Data Found';
            }
            if($req->options ==1)
                return view('MFN.Reports.RegularGeneralReports.PeriodicCollectionComponentWise.pcco_table_view_product', $context);
            elseif($req->options ==2){
                $prodCat = DB::table('mfn_loan_product_category')
                ->where('is_delete', 0)
                ->get();

                $context['loanProductCategories'] = $prodCat;
                return view('MFN.Reports.RegularGeneralReports.PeriodicCollectionComponentWise.pcco_table_view_category', $context);
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
