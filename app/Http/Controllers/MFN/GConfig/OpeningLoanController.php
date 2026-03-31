<?php

namespace App\Http\Controllers\MFN\GConfig;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\MFN\OpeningLoan;
use App\Model\MFN\LoanProduct;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;
use App\Services\MfnService;

class OpeningLoanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }
    
    public function index(Request $req) {

        if ($req->ajax()) {
            $columns = ['mfn_opening_info_loan.loanProductId', 'mfn_opening_info_savings.gender', 'mfn_loan_products.name'];

            $limit = $req->length;
            $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
            $order = $columns[$orderColumnIndex];
            $dir = $req->input('order.0.dir');
            // Searching variable
            $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');
            $branchId = (empty($req->input('branchId'))) ? null : $req->input('branchId');

            $loanInfos = DB::table('mfn_opening_info_loan as ol')
                ->where([['ol.is_delete', 0],['ol.branchId',$branchId]])
                ->join('mfn_loan_products as lp', 'lp.id', 'ol.loanProductId')
                ->select('ol.id','ol.gender','lp.name as productName')
                // ->orderBy($order, $dir)
                ->limit($limit);

            if ($search != null) {
                $loanInfos->where(function ($query) use ($search) {
                    $query->where('ol.gender', 'LIKE', "%{$search}%")
                          ->orWhere('lp.name', 'LIKE', "%{$search}%");
                });
            }
            $loanInfos = $loanInfos->get();

            $totalData = DB::table('mfn_opening_info_loan')->where('is_delete', 0)->count('id');

            $sl = (int)$req->start + 1;
            foreach ($loanInfos as $key => $loanInfo) {
                $loanInfos[$key]->sl = $sl++;
            }

            $data = array(
                "draw" => intval($req->input('draw')),
                "recordsTotal" => $totalData,
                "recordsFiltered" => $totalData,
                'data' => $loanInfos,
            );

            return response()->json($data);
        }
        $branchList = DB::table('gnl_branchs')
            ->where([['is_delete',0],['is_active',1],['is_approve',1]])
            ->orderBy('branch_code')
            ->select('id','branch_name', 'branch_code')
            ->get();
        return view('MFN.OpeningLoan.index',compact('branchList'));
    }

    public function view($loanId)
    {

        $openingLoan = DB::table('mfn_opening_info_loan as ol')
            ->where([['ol.id',$loanId],['ol.is_delete',0]])
            ->join('gnl_branchs as gb', 'gb.id', 'ol.branchId')
            ->join('mfn_loan_products as lp', 'lp.id', 'ol.loanProductId')
            ->select('ol.*','lp.name as product_name','gb.branch_name')
            ->first();

        return view('MFN.OpeningLoan.view', compact('openingLoan'));
    }

    public function add(Request $req) {

        if ($req->isMethod('post')) {

            $passport = $this->getPassport($req, $operationType = 'store');
            if ($passport['isValid'] == false) {
                $notification = array(
                    'message' => $passport['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $loanProduct = LoanProduct::where([['id',$req->loanProductId], ['is_delete',0]])
                            ->select('productCategoryId','fundingOrgId')
                            ->first();

            $openingLoan = new OpeningLoan();

            $openingLoan->branchId                        = $req->branchId ? $req->branchId : Auth::user()->branch_id;
            $openingLoan->loanCategoryId                  = $loanProduct->productCategoryId;
            $openingLoan->fundingOrgId                    = $loanProduct->fundingOrgId;
            $openingLoan->loanProductId                   = $req->loanProductId;
            $openingLoan->gender                          = $req->gender;
            $openingLoan->cumulativeDisbursement          = $req->cumulativeDisbursement;
            $openingLoan->cumulativeRepay                 = $req->cumulativeRepay;
            $openingLoan->cumulativeCollection            = $req->cumulativeCollection;
            $openingLoan->cumulativeCollectionPrincipal   = $req->cumulativeCollectionPrincipal;
            $openingLoan->cumulativeWriteOff              = $req->cumulativeWriteOff;
            $openingLoan->cumulativeWriteOffPrincipal     = $req->cumulativeWriteOffPrincipal;
            $openingLoan->cumulativeWriteOffNumber        = $req->cumulativeWriteOffNumber;
            $openingLoan->cumulativeWaiver                = $req->cumulativeWaiver;
            $openingLoan->cumulativeWaiverPrincipal       = $req->cumulativeWaiverPrincipal;
            $openingLoan->cumulativeRebate                = $req->cumulativeRebate;
            $openingLoan->cumulativeFullyPaidBorrowerNo   = $req->cumulativeFullyPaidBorrowerNo;
            $openingLoan->cumulativeBorrowerNo            = $req->cumulativeBorrowerNo;
            $openingLoan->cumulativeLoanNo                = $req->cumulativeLoanNo;
            $openingLoan->created_by                      = Auth::user()->id;
            $openingLoan->created_at                      = Carbon::now();
    
            $openingLoan->save();

            $notification = array(
                'message' => 'Successfully Inserted',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        }

        $branchList = DB::table('gnl_branchs')
            ->where([['is_delete',0],['is_active',1],['is_approve',1]])
            ->orderBy('branch_code')
            ->select('id','branch_name', 'branch_code')
            ->get();

        $productList = DB::table('mfn_loan_products')
                    ->where('is_delete',0)
                    ->orderBy('productCode')
                    ->select('id','shortName','productCode')
                    ->get();
                    
        return view('MFN.OpeningLoan.add',compact('branchList','productList'));
    }


    public function edit(Request $req) {

        $branchList = Branch::where([['is_delete',0],['is_active',1],['is_approve',1]])
                    ->orderBy('branch_name')
                    ->select('id','branch_name')
                    ->get();

        $productList = LoanProduct::where('is_delete',0)->orderBy('name')->select('id','productCode','shortName')->get();

        $loanProduct = LoanProduct::where([['id',$req->loanProductId], ['is_delete',0]])
                            ->select('productCategoryId','fundingOrgId')
                            ->first();

        $openingLoanData = OpeningLoan::where('id', $req->loanId)->first();

        if ($req->isMethod('post')) {

            $passport = $this->getPassport($req, $operationType = 'update');
            if ($passport['isValid'] == false) {
                $notification = array(
                    'message' => $passport['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $openingLoan = OpeningLoan::find($openingLoanData->id);

            $openingLoan->branchId                        = $req->branchId ? $req->branchId : Auth::user()->branch_id;
            $openingLoan->loanCategoryId                  = $loanProduct->productCategoryId;
            $openingLoan->fundingOrgId                    = $loanProduct->fundingOrgId;
            $openingLoan->loanProductId                   = $req->loanProductId;
            $openingLoan->gender                          = $req->gender;
            $openingLoan->cumulativeDisbursement          = $req->cumulativeDisbursement;
            $openingLoan->cumulativeRepay                 = $req->cumulativeRepay;
            $openingLoan->cumulativeCollection            = $req->cumulativeCollection;
            $openingLoan->cumulativeCollectionPrincipal   = $req->cumulativeCollectionPrincipal;
            $openingLoan->cumulativeWriteOff              = $req->cumulativeWriteOff;
            $openingLoan->cumulativeWriteOffPrincipal     = $req->cumulativeWriteOffPrincipal;
            $openingLoan->cumulativeWriteOffNumber        = $req->cumulativeWriteOffNumber;
            $openingLoan->cumulativeWaiver                = $req->cumulativeWaiver;
            $openingLoan->cumulativeWaiverPrincipal       = $req->cumulativeWaiverPrincipal;
            $openingLoan->cumulativeRebate                = $req->cumulativeRebate;
            $openingLoan->cumulativeFullyPaidBorrowerNo   = $req->cumulativeFullyPaidBorrowerNo;
            $openingLoan->cumulativeBorrowerNo            = $req->cumulativeBorrowerNo;
            $openingLoan->cumulativeLoanNo                = $req->cumulativeLoanNo;
            $openingLoan->updated_by                      = Auth::user()->id;
            $openingLoan->updated_at                      = Carbon::now();
    
            $openingLoan->save();

            $notification = array(
                'message' => 'Successfully Updated',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        } 
        return view('MFN.OpeningLoan.edit',compact('branchList','productList','openingLoanData'));
    }

    public function delete(Request $req) {

        $openingLoanData = OpeningLoan::find($req->loanId);

        $openingLoanData->is_delete = 1;

        $delete = $openingLoanData->save();

        if ($delete) {
            $notification = array(
                'message' => 'Successfully Deleted',
                'alert-type' => 'success',
            );
            return response()->json($notification);
        } else {
            $notification = array(
                'message' => 'Unsuccessful to Delete',
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }
    }

    public function getPassport($req, $operationType)
    {
        $errorMsg = null;

        $req->loanProductId = trim($req->loanProductId);
        $req->branch_id = $req->branch_id ? trim($req->branch_id) : Auth::user()->branch_id;

        if ($operationType == 'store') {
            $existingLoan = OpeningLoan::where([['branchId',$req->branch_id], 
                                ['loanProductId',$req->loanProductId],
                                ['is_delete',0]])
                              ->get();

            if (count($existingLoan) > 0) {
                $errorMsg = 'Duplicate Entry Prohibited.';
            }
        }

        if ($operationType == 'update') {
            $existingLoan = OpeningLoan::where([['branchId',$req->branch_id], 
                                ['loanProductId',$req->loanProductId], 
                                ['is_delete',0]])
                              ->where('id', '!=', $req->loanId)
                              ->get();

            if (count($existingLoan) > 0) {
                $errorMsg = 'Duplicate Entry Prohibited.';
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $passport;
    }

}
