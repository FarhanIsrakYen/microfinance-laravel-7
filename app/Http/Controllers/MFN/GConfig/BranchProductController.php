<?php

namespace App\Http\Controllers\MFN\GConfig;

use App\Services\RoleService;
use App\Http\Controllers\Controller;
use App\Model\MFN\BranchProduct;
use App\Model\GNL\Branch;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;

class BranchProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $req) {

        if ($req->ajax()) {

            // $loanProductIDs = json_decode(DB::table('mfn_branch_products')
            //         ->first()->loanProductIds);
            

            $loanProducts = DB::table('mfn_loan_products')
            ->where([['is_delete', 0],])
            // ->whereIn('id', $loanProductIDs)
            ->select('shortName')
            ->get();

            $branchProducts = DB::table('mfn_branch_products')
                ->join('gnl_branchs', 'gnl_branchs.id', 'mfn_branch_products.branchId')
                ->select('gnl_branchs.branch_name AS branchName','gnl_branchs.branch_code AS branchCode', 'mfn_branch_products.*')
                ->get();

            // Get Loan and Savings Product Names and seperate names with comma
            foreach ($branchProducts as $key => $branchProduct) {
                $loanProdutIds = json_decode($branchProduct->loanProductIds);

                $loanProductNames = DB::table('mfn_loan_products')
                ->where('is_delete', 0)
                ->whereIn('id', $loanProdutIds)
                ->select(DB::raw("GROUP_CONCAT(shortName SEPARATOR ', ') AS loanProdctNames"))
                ->value('loanProdctNames');

                $branchProducts[$key]->loanProdctNames = $loanProductNames;

                $savingsProdutIds = json_decode($branchProduct->savingProductIds);

                $savingsProductNames = DB::table('mfn_savings_product')
                ->where('is_delete', 0)
                ->whereIn('id', $savingsProdutIds)
                ->select(DB::raw("GROUP_CONCAT(shortName SEPARATOR ', ') AS savingProdctNames"))
                ->value('savingProdctNames');

                $branchProducts[$key]->savingProdctNames = $savingsProductNames;
            }

            $totalData = DB::table('mfn_branch_products')->count('branchId');

            $sl = (int)$req->start + 1;
            foreach ($branchProducts as $key => $area) {
                $branchProducts[$key]->sl        = $sl++;
                $branchProducts[$key]->action    = RoleService::roleWiseArray($this->GlobalRole, $branchProducts[$key]->branchId);
            }

            $data = array(
                "draw" => intval($req->input('draw')),
                "recordsTotal" => $totalData,
                "recordsFiltered" => $totalData,
                'data' => $branchProducts,
            );


            return response()->json($data);

        
        }else {
            return view('MFN.BranchProduct.index');
        }
    }


    public function view($prodAssignId) {

        $branchProduct = BranchProduct::where('branchId', $prodAssignId)->first();

        $branch = DB::table('gnl_branchs')
            ->where('id', $branchProduct->branchId)
            ->select('branch_name', 'branch_code')
            ->first();

        $loanProdutIds = json_decode($branchProduct->loanProductIds);

        $loanProductNames = DB::table('mfn_loan_products')
        ->where('is_delete', 0)
                                ->whereIn('id', $loanProdutIds)
                                ->select(DB::raw("GROUP_CONCAT(shortName SEPARATOR ', ') AS loanProdctNames"))
                                ->value('loanProdctNames');

        $branchProduct->loanProdctNames = $loanProductNames;

        $savingsProdutIds = json_decode($branchProduct->savingProductIds);

        $savingsProductNames = DB::table('mfn_savings_product')
        ->where('is_delete', 0)
                                ->whereIn('id', $savingsProdutIds)
                                ->select(DB::raw("GROUP_CONCAT(shortName SEPARATOR ', ') AS savingProdctNames"))
                                ->value('savingProdctNames');

        $branchProduct->savingProdctNames = $savingsProductNames;

        $data = array(
            'branchProduct'     => $branchProduct,
            'branchName'        => $branch->branch_name,
            'branchCode'        => $branch->branch_code,
            'loanProduct'       => $branchProduct->loanProdctNames,
            'savingsProduct'    => $branchProduct->savingProdctNames,
        );

        return view('MFN.BranchProduct.view', $data);
    }


    public function add(Request $req) {

        $branchIds = BranchProduct::select('branchId')->get()->toArray();
    
        $branchList = Branch::where('is_delete',0)
                            ->whereNotIn('id',[1])
                            ->whereNotIn('id',$branchIds)
                            ->orderBy('branch_name')
                            ->select('id','branch_name')
                            ->get();

        $loanProductList = DB::table('mfn_loan_products')
                            ->where('is_delete',0)
                            ->orderBy('name')
                            ->select('id','name','shortName')
                            ->get();

        $savingsProductList = DB::table('mfn_savings_product')
                            ->where('is_delete',0)
                            ->orderBy('name')
                            ->select('id','name','shortName')
                            ->get();

        $data = array(
            'branchList'            => $branchList,
            'loanProductList'       => $loanProductList,
            'savingsProductList'    => $savingsProductList,
        );

        if ($req->isMethod('post')) {

            $passport = $this->getPassport($req, $operationType = 'store');

            if ($passport['isValid'] == false) {
                $notification = array(
                    'message' => $passport['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            // Get Loan And Savings Product Data
            $loan_product_arrs = isset($req['loanProductIds']) ? $req['loanProductIds'] : array();
            $savings_product_arrs = isset($req['savingProductIds']) ? $req['savingProductIds'] : array();

            $branchProduct = new BranchProduct();
            $branchProduct->branchId        = $req->branchId;
            $branchProduct->loanProductIds  = json_encode($loan_product_arrs);
            $branchProduct->savingProductIds  = json_encode($savings_product_arrs);
            $branchProduct->created_by  = Auth::user()->id;
            $branchProduct->created_at  = Carbon::now();
            $branchProduct->save();

            $notification = array(
                'message' => 'Successfully Inserted',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        } else {
            return view('MFN.BranchProduct.add',$data);
        }
    }

    public function edit(Request $req) {


        $branchProductList = BranchProduct::where('branchId', $req->prodAssignId)->first();
        
        $branchList = Branch::where('is_delete',0)
                            ->whereNotIn('id', [1])
                            ->orderBy('branch_name')
                            ->select('id','branch_name')
                            ->get();

        $loanProductList = DB::table('mfn_loan_products')
                            ->where('is_delete',0)
                            ->orderBy('name')
                            ->select('id','name','shortName')
                            ->get();

        $savingsProductList = DB::table('mfn_savings_product')
                            ->where('is_delete',0)
                            ->orderBy('name')
                            ->select('id','name','shortName')
                            ->get();

        $data = array(
            'branchID'              => $branchProductList->branchId,
            'branchList'            => $branchList,
            'loanProductList'       => json_decode($loanProductList),
            'savingsProductList'    => $savingsProductList,
            'loanProduct'           => json_decode($branchProductList->loanProductIds),
            'savingsProduct'        => json_decode($branchProductList->savingProductIds),
        );

        if ($req->isMethod('post')) {
            // $passport = $this->getPassport($req, $operationType = 'update');

            // if ($passport['isValid'] == false) {
            //     $notification = array(
            //         'message' => $passport['errorMsg'],
            //         'alert-type' => 'error',
            //     );
            //     return response()->json($notification);
            // }

            // Get Loan And Savings Product Data
            $loan_product_arrs = isset($req['loanProductIds']) ? $req['loanProductIds'] : array();
            $savings_product_arrs = isset($req['savingProductIds']) ? $req['savingProductIds'] : array();

            $branchProduct                    = BranchProduct::find($branchProductList->branchId);
            $branchProduct->branchId          = $branchProductList->branchId;
            $branchProduct->loanProductIds    = json_encode($loan_product_arrs);
            $branchProduct->savingProductIds  = json_encode($savings_product_arrs);
            $branchProduct->updated_by        = Auth::user()->id;
            $branchProduct->updated_at        = Carbon::now();
            $branchProduct->save();

            $notification = array(
                'message' => 'Successfully Updated',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        } else {
            return view('MFN.BranchProduct.edit',$data);
        }
    }

    public function getPassport($req, $operationType)
    {
        $errorMsg = null;

        if ($operationType == 'store') {
            $rules['branchId'] = 'required';
        }

        $validator = Validator::make($req->all(), $rules);

        if ($validator->fails()) {
            $errorMsg = implode(' || ', $validator->messages()->all());
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $passport;
    }
}
