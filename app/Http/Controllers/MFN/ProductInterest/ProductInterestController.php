<?php

namespace App\Http\Controllers\MFN\ProductInterest;

use App\Services\MfnService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;
use App\Model\MFN\LoanProduct;

class ProductInterestController extends Controller
{
	public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $req) {

        if (!$req->ajax()) {
            return view('MFN.ProductInterest.index');
        }

        $columns = ['lp.name', 'lp.shortName', 'lp.productCode', 'lpt.name', 'lpc.name', 'forg.name', 'lp.startDate'];

        $limit = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order = $columns[$orderColumnIndex];
        $dir = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $products = DB::table('mfn_loan_products AS lp')
            ->leftJoin('mfn_loan_product_types AS lpt', 'lpt.id', 'lp.productTypeId')
            ->leftJoin('mfn_loan_product_category AS lpc', 'lpc.id', 'lp.productCategoryId')
            ->leftJoin('mfn_funding_orgs AS forg', 'forg.id', 'lp.fundingOrgId')
            ->where([
                ['lp.is_delete', 0],
                ['lpt.is_delete', 0],
                ['lpc.is_delete', 0],
                ['forg.is_delete', 0],
            ])
            ->orderBy($order, $dir)
            ->select(DB::raw("lp.name, lp.shortName, lp.productCode, lpt.name AS productType, lpc.name AS productCategory, forg.name AS fundingOrg, lp.startDate, lp.id"));

        if ($search != null) {
            $products->where(function ($query) use ($search) {
                $query->where('lp.name', 'LIKE', "%{$search}%")
                    ->orWhere('lp.productCode', 'LIKE', "%{$search}%")
                    ->orWhere('lpt.name', 'LIKE', "%{$search}%")
                    ->orWhere('lpc.name', 'LIKE', "%{$search}%")
                    ->orWhere('forg.name', 'LIKE', "%{$search}%");
            });
        }

        $totalData = (clone $products)->count();
        $products = $products->limit($limit)->get();

        $sl = (int)$req->start + 1;
        foreach ($products as $key => $product) {
            $products[$key]->startDate = Carbon::parse($product->startDate)->format('d-m-Y');
            $products[$key]->sl = $sl++;
        }

        $data = array(
            "draw"              => intval($req->input('draw')),
            "recordsTotal"      => $totalData,
            "recordsFiltered"   => $totalData,
            'data'              => $products,
        );


        return response()->json($data);
    }

    public function edit(Request $req)
    {
    	if ($req->isMethod('post')) {
            return $this->update($req);
        }

        $product = DB::table('mfn_loan_products AS lp')
            ->leftJoin('mfn_loan_product_types AS lpt', 'lpt.id', 'lp.productTypeId')
            ->leftJoin('mfn_loan_product_category AS lpc', 'lpc.id', 'lp.productCategoryId')
            ->leftJoin('mfn_funding_orgs AS forg', 'forg.id', 'lp.fundingOrgId')
            ->where('lp.id', $req->prodInterestId)
            ->select(DB::raw("lp.*, lp.shortName, lp.productCode, lpt.name AS productType, lpc.name AS productCategory, forg.name AS fundingOrg"))
            ->first();

        if ($product->fundingOrgId == 1) {
            $pksfFund = DB::table('mfn_pksf_funds')->where('id', $product->pksfFundId)->value('name');
        } else {
            $pksfFund = '';
        }

        $product->startDate = Carbon::parse($product->startDate)->format('d-m-Y');

        $insuranceCalMethods = DB::table('mfn_insurance_calculation_methods')
            ->where([
                ['status', 1],
                ['softDel', 0],
            ])
            ->get();

        $repaymentFrequencies = DB::table('mfn_loan_repayment_frequency')
            ->where([
                ['is_delete', 0],
                ['status', 1],
            ])
            ->select('id', 'name')
            ->get();
    

        // if it is regular type product get the repayment info
        // else get the repayments months for one time 
        $otRepayMonths = '';
        $regularLoanRepaymentInfo = '';
        if ($product->productTypeId == 1) {
            $regularLoanRepaymentInfo = json_decode($product->repaymentInfo);
            $regularLoanRepaymentInfo = collect($regularLoanRepaymentInfo);
        } elseif ($product->productTypeId == 2) {
            $repaymentInfo = json_decode($product->repaymentInfo);
            $otRepayMonths = $repaymentInfo->eligibleMonths;
        }

        $data = array(
            'product' => $product,
            'pksfFund' => $pksfFund,
            'insuranceCalMethods' => $insuranceCalMethods,
            'repaymentFrequencies' => $repaymentFrequencies,
            'otRepayMonths' => $otRepayMonths,
            'regularLoanRepaymentInfo' => $regularLoanRepaymentInfo,
        );

        return view('MFN.ProductInterest.edit', $data);
    }

}
