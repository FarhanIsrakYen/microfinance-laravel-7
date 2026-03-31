<?php

namespace App\Http\Controllers\MFN\GConfig;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\MFN\OpeningLoan;
use App\Model\MFN\OpeningSavings;
use App\Model\MFN\LoanProduct;
use App\Model\MFN\SavingsProduct;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;
use App\Services\MfnService;

class OpeningSavingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }
    
    public function index(Request $req) {

        if ($req->ajax()) {
            $columns = ['mfn_opening_info_savings.loanProductId', 'mfn_opening_info_savings.savingsProductId', 
                        'mfn_opening_info_savings.gender', 'mfn_loan_products.name'];

            $limit = $req->length;
            $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
            $order = $columns[$orderColumnIndex];
            $dir = $req->input('order.0.dir');
            // Searching variable
            $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');
            $branchId = (empty($req->input('branchId'))) ? null : $req->input('branchId');

            $savingsInfos = DB::table('mfn_opening_info_savings as os')
                ->where([['os.is_delete', 0],['os.branchId',$branchId]])
                ->join('mfn_loan_products as lp', 'lp.id', 'os.loanProductId')
                ->join('mfn_savings_product as sp', 'sp.id', 'os.savingsProductId')
                ->select('os.id','os.gender','lp.name as loanProduct','sp.name as savingsProduct')
                // ->orderBy($order, $dir)
                ->limit($limit);

            if ($search != null) {
                $savingsInfos->where(function ($query) use ($search) {
                    $query->where('ol.gender', 'LIKE', "%{$search}%")
                          ->orWhere('lp.name', 'LIKE', "%{$search}%");
                });
            }
            $savingsInfos = $savingsInfos->get();

            $totalData = DB::table('mfn_opening_info_savings')->where('is_delete', 0)->count('id');

            $sl = (int)$req->start + 1;
            foreach ($savingsInfos as $key => $savingsInfo) {
                $savingsInfos[$key]->sl = $sl++;
            }

            $data = array(
                "draw" => intval($req->input('draw')),
                "recordsTotal" => $totalData,
                "recordsFiltered" => $totalData,
                'data' => $savingsInfos,
            );

            return response()->json($data);
        }
        $branchList = DB::table('gnl_branchs')
            ->where([['is_delete',0],['is_active',1],['is_approve',1]])
            ->orderBy('branch_code')
            ->select('id','branch_name', 'branch_code')
            ->get();
        return view('MFN.OpeningSavings.index',compact('branchList'));
    }

    public function view($loanId)
    {

        $openingSavings = DB::table('mfn_opening_info_savings as os')
            ->where([['os.id',$loanId],['os.is_delete',0]])
            ->join('gnl_branchs as gb', 'gb.id', 'os.branchId')
            ->join('mfn_loan_products as lp', 'lp.id', 'os.loanProductId')
            ->join('mfn_savings_product as sp', 'sp.id', 'os.savingsProductId')
            ->select('os.*','lp.name as loan_product','sp.name as savings_product','gb.branch_name')
            ->first();

        return view('MFN.OpeningSavings.view', compact('openingSavings'));
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

            $openingSavings = new OpeningSavings();

            $openingSavings->branchId             = $req->branchId ? $req->branchId : Auth::user()->branch_id;
            $openingSavings->loanCategoryId       = $loanProduct->productCategoryId;
            $openingSavings->fundingOrgId         = $loanProduct->fundingOrgId;
            $openingSavings->loanProductId        = $req->loanProductId;
            $openingSavings->savingsProductId     = $req->savingsProductId;
            $openingSavings->gender               = $req->gender;
            $openingSavings->cumulativeDeposit    = $req->cumulativeDeposit;
            $openingSavings->cumulativeInterest   = $req->cumulativeInterest;
            $openingSavings->cumulativeWithdraw   = $req->cumulativeWithdraw;
            $openingSavings->closingBalance       = $req->closingBalance;
            $openingSavings->created_by           = Auth::user()->id;
            $openingSavings->created_at           = Carbon::now();
    
            $openingSavings->save();

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

        $loanProductList = DB::table('mfn_loan_products')
                    ->where('is_delete',0)
                    ->orderBy('productCode')
                    ->select('id','shortName','productCode')
                    ->get();

        $savProductList = DB::table('mfn_savings_product')
                    ->where('is_delete',0)
                    ->orderBy('productCode')
                    ->select('id','shortName','productCode')
                    ->get();
                    
        return view('MFN.OpeningSavings.add',compact('branchList','loanProductList','savProductList'));
    }


    public function edit(Request $req) {

        $branchList = Branch::where([['is_delete',0],['is_active',1],['is_approve',1]])
                    ->orderBy('branch_name')
                    ->select('id','branch_name')
                    ->get();

        $loanProductList = LoanProduct::where('is_delete',0)->orderBy('name')->select('id','productCode','shortName')->get();

        $savProductList = SavingsProduct::where('is_delete',0)->orderBy('name')->select('id','productCode','shortName')->get();

        $loanProduct = LoanProduct::where([['id',$req->loanProductId], ['is_delete',0]])
                            ->select('productCategoryId','fundingOrgId')
                            ->first();

        $openingSavingsData = OpeningSavings::where('id', $req->savingsId)->first();

        if ($req->isMethod('post')) {

            $passport = $this->getPassport($req, $operationType = 'update');
            if ($passport['isValid'] == false) {
                $notification = array(
                    'message' => $passport['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }
            $openingSavings = OpeningSavings::find($openingSavingsData->id);

            $openingSavings->branchId             = $req->branchId ? $req->branchId : Auth::user()->branch_id;
            $openingSavings->loanCategoryId       = $loanProduct->productCategoryId;
            $openingSavings->fundingOrgId         = $loanProduct->fundingOrgId;
            $openingSavings->loanProductId        = $req->loanProductId;
            $openingSavings->savingsProductId     = $req->savingsProductId;
            $openingSavings->gender               = $req->gender;
            $openingSavings->cumulativeDeposit    = $req->cumulativeDeposit;
            $openingSavings->cumulativeInterest   = $req->cumulativeInterest;
            $openingSavings->cumulativeWithdraw   = $req->cumulativeWithdraw;
            $openingSavings->closingBalance       = $req->closingBalance;
            $openingSavings->updated_by           = Auth::user()->id;
            $openingSavings->updated_at           = Carbon::now();
    
            $openingSavings->save();

            $notification = array(
                'message' => 'Successfully Updated',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        } 
        return view('MFN.OpeningSavings.edit',compact('branchList','loanProductList','savProductList','openingSavingsData'));
    }

    public function delete(Request $req) {

        $openingSavingsData = OpeningSavings::find($req->savingsId);

        $openingSavingsData->is_delete = 1;

        $delete = $openingSavingsData->save();

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
        $req->savingsProductId = trim($req->savingsProductId);
        $req->branch_id = $req->branch_id ? trim($req->branch_id) : Auth::user()->branch_id;

        if ($operationType == 'store') {
            $existingSavings = OpeningSavings::where([['branchId',$req->branch_id], 
                                ['loanProductId',$req->loanProductId], 
                                ['savingsProductId',$req->savingsProductId],
                                ['is_delete',0]])
                              ->get();

            if (count($existingSavings) > 0) {
                $errorMsg = 'Duplicate Entry Prohibited.';
            }
        }

        if ($operationType == 'update') {
            $existingSavings = OpeningSavings::where([['branchId',$req->branch_id], 
                                ['loanProductId',$req->loanProductId], 
                                ['savingsProductId',$req->savingsProductId],
                                ['is_delete',0]])
                              ->where('id', '!=', $req->savingsId)
                              ->get();

            if (count($existingSavings) > 0) {
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
