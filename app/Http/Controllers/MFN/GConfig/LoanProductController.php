<?php

namespace App\Http\Controllers\MFN\GConfig;

use App\Services\MfnService;
use App\Services\RoleService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;
use App\Model\MFN\LoanProduct;
use App\Model\MFN\LoanProductHistory;

class LoanProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $req)
    {
        if (!$req->ajax()) {
            return view('MFN.LoanProducts.index');
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
        $products = $products->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;
        foreach ($products as $key => $product) {
            $products[$key]->startDate = Carbon::parse($product->startDate)->format('d-m-Y');
            $products[$key]->sl = $sl++;
            $products[$key]->action        = RoleService::roleWiseArray($this->GlobalRole, $products[$key]->id);
        }

        $data = array(
            "draw"              => intval($req->input('draw')),
            "recordsTotal"      => $totalData,
            "recordsFiltered"   => $totalData,
            'data'              => $products,
        );


        return response()->json($data);
    }

    public function view($loanProductId)
    {
        $product = LoanProduct::where('id', $loanProductId)->first();

        $productType = DB::table('mfn_loan_product_types')
            ->where('id', $product->productTypeId)
            ->select('name')
            ->first();

        $productCat = DB::table('mfn_loan_product_category')
            ->where('id', $product->productCategoryId)
            ->select('name')
            ->first();

        $fundingOrg = DB::table('mfn_funding_orgs')
            ->where('id', $product->fundingOrgId)
            ->select('name')
            ->first();

        if($product->fundingOrgId == 1){
            $pksfFund = DB::table('mfn_pksf_funds')
            ->where('id', $product->pksfFundId)
            ->value('name');
        }
        else{
            $pksfFund = 'N/A';
        }

        $insCalcMethod = DB::table('mfn_insurance_calculation_methods')
            ->where('id', $product->insuranceCalculationMethodId)
            ->select('name')
            ->first();

        $repaymentFrequencies = DB::table('mfn_loan_repayment_frequency')
            ->where([
                ['is_delete', 0],
                ['status', 1],
            ])
            ->select('id', 'name')
            ->get();

        $productInterestRates = DB::table('mfn_loan_product_interest_rates')
            ->where([
                ['is_delete', 0],
                ['productId', $product->id],
            ])
            ->orderBy('effectiveDate','ASC')
            ->get();

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
            'product'                   => $product,
            'productType'               => $productType,
            'productCat'                => $productCat,
            'fundingOrg'                => $fundingOrg,
            'pksfFund'                  => $pksfFund,
            'insCalcMethod'             => $insCalcMethod,
            'isPrimaryProduct'          => $product->isPrimaryProduct == 1 ? 'Yes' : 'No',
            'isInsuranceApplicable'     => $product->isInsuranceApplicable == 1 ? 'Yes' : 'No',
            'isMultipleLoanAllowed'     => $product->isMultipleLoanAllowed == 1 ? 'Yes' : 'No',
            'startDate'                 => Carbon::parse($product->startDate)->format('d-m-Y'),
            'repaymentFrequencies'      => $repaymentFrequencies,
            'otRepayMonths'             => $otRepayMonths,
            'regularLoanRepaymentInfo'  => $regularLoanRepaymentInfo,
            'productInterestRates'      => $productInterestRates,
        );

        return view('MFN.LoanProducts.view', $data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $loanCategories = DB::table('mfn_loan_product_category')
            ->where('is_delete', 0)
            ->select('id', 'name')
            ->get();

        $funOrgs = DB::table('mfn_funding_orgs')
            ->where('is_delete', 0)
            ->select('id', 'name')
            ->get();

        $pksfFunds = DB::table('mfn_pksf_funds')->orderBy('name')->get();

        $repaymentFrequencies = DB::table('mfn_loan_repayment_frequency')
            ->where([
                ['is_delete', 0],
                ['status', 1],
            ])
            ->select('id', 'name')
            ->get();

        $productTypes = DB::table('mfn_loan_product_types')->where('is_delete', 0)->get();

        $insuranceCalMethods = DB::table('mfn_insurance_calculation_methods')
            ->where([
                ['status', 1],
                ['softDel', 0],
            ])
            ->get();

        $data = array(
            'loanCategories'        => $loanCategories,
            'funOrgs'               => $funOrgs,
            'pksfFunds'             => $pksfFunds,
            'repaymentFrequencies'  => $repaymentFrequencies,
            'productTypes'          => $productTypes,
            'insuranceCalMethods'   => $insuranceCalMethods,
        );

        return view('MFN.LoanProducts.add', $data);
    }

    public function store(Request $req)
    {
        $passport = $this->getPassport($req, $operationType = 'store');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $insuranceCalculationMethodId = 0;
        $insurancePercentage = 0;
        $fixedInsuranceAmount = 0;

        if ($req->isInsuranceApplicable == 1) {
            $insuranceCalculationMethodId = $req->insuranceCalculationMethodId;

            if ($req->insuranceCalculationMethodId == 1) {
                $insurancePercentage = $req->insurancePercentage;
            }elseif($req->insuranceCalculationMethodId == 2){
                $fixedInsuranceAmount  = $req->fixedinsurance;
            }
        }

        $product = new LoanProduct;
        $product->name                          = $req->name;
        $product->shortName                     = $req->shortName;
        $product->productCode                   = $req->code;
        $product->productTypeId                 = $req->isPrimaryProduct == 1 ? 1 : $req->productTypeId;
        $product->productCategoryId             = $req->productCategoryId;
        $product->fundingOrgId                  = $req->fundingOrganizationId;
        $product->pksfFundId                    = $req->fundingOrganizationId == 1 ? $req->pksfFundId : 0;
        $product->startDate                     = Carbon::parse($req->startDate);
        $product->isPrimaryProduct              = $req->isPrimaryProduct;
        $product->minLoanAmount                 = $req->minLoanAmount;
        $product->avgLoanAmount                 = $req->avgLoanAmount;
        $product->maxLoanAmount                 = $req->maxLoanAmount;
        $product->yearsEligibleWriteOff         = $req->yearsEligibleWriteOff;
        $product->isInsuranceApplicable         = $req->isInsuranceApplicable;
        $product->insuranceCalculationMethodId  = $insuranceCalculationMethodId;
        $product->insurancePercentage           = $insurancePercentage;
        $product->fixedInsuranceAmount          = $fixedInsuranceAmount;
        $product->mandatorySavingsPercantage    = 0;
        $product->isMultipleLoanAllowed         = $req->isPrimaryProduct == 1 ? 0 : $req->isMultipleLoanAllowed;
        $product->repaymentInfo                 = $this->makeRepaymrntInfo($req);
        $product->formFee                       = $req->formFee;
        $product->additionalFee                 = $req->additionalFee;
        $product->additionalFreeForFirstTime    = $req->additionalFreeForFirstTime;
        $product->created_at                    = Carbon::now();
        $product->created_by                    = Auth::user()->id;
        $product->save();

        $notification = array(
            'message'       => 'Successfully Inserted',
            'alert-type'    => 'success',
        );

        return response()->json($notification);
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
            ->where('lp.id', $req->loanProductId)
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

        return view('MFN.LoanProducts.edit', $data);
    }

    public function update(Request $req)
    {
        $product = LoanProduct::find(decrypt($req->productId));

        $req->isPrimaryProduct = $product->isPrimaryProduct;
        $req->productTypeId = $product->productTypeId;

        $passport = $this->getPassport($req, $operationType = 'update', $product);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $insuranceCalculationMethodId = 0;
        $insurancePercentage = 0;
        $fixedInsuranceAmount = 0;

        if ($req->isInsuranceApplicable == 1) {
            $insuranceCalculationMethodId = $req->insuranceCalculationMethodId;

            if ($req->insuranceCalculationMethodId == 1) {
                $insurancePercentage = $req->insurancePercentage;
            }elseif($req->insuranceCalculationMethodId == 2){
                $fixedInsuranceAmount  = $req->fixedinsurance;
            }
        }
        
        $this->storeLoanProductHistory($product);
        
        $product->name                          = $req->name;
        $product->shortName                     = $req->shortName;
        $product->productCode                   = $req->code;
        $product->minLoanAmount                 = $req->minLoanAmount;
        $product->avgLoanAmount                 = $req->avgLoanAmount;
        $product->maxLoanAmount                 = $req->maxLoanAmount;
        $product->yearsEligibleWriteOff         = $req->yearsEligibleWriteOff;
        $product->isInsuranceApplicable         = $req->isInsuranceApplicable;
        $product->insuranceCalculationMethodId  = $insuranceCalculationMethodId;
        $product->insurancePercentage           = $insurancePercentage;
        $product->fixedInsuranceAmount          = $fixedInsuranceAmount;
        $product->mandatorySavingsPercantage    = 0;
        $product->repaymentInfo                 = $this->makeRepaymrntInfo($req);
        $product->formFee                       = $req->formFee;
        $product->additionalFee                 = $req->additionalFee;
        $product->additionalFreeForFirstTime    = $req->additionalFreeForFirstTime;
        $product->updated_at                    = Carbon::now();
        $product->updated_by                    = Auth::user()->id;
        $product->save();

        $notification = array(
            'message'       => 'Successfully Updated',
            'alert-type'    => 'success',
        );

        return response()->json($notification);
    }

    public function delete(Request $req)
    {
        $product = LoanProduct::find($req->productId);

        $passport = $this->getPassport($req, $operationType = 'delete', $product);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $product->is_delete = 1;
        $product->save();

        $notification = array(
            'message'       => 'Successfully Deleted',
            'alert-type'    => 'success',
        );

        return response()->json($notification);
    }

    public function makeRepaymrntInfo($req)
    {
        // if it is regular loan product
        if ($req->isPrimaryProduct == 1 || ($req->isPrimaryProduct == 0 && $req->productTypeId == 1)) {
            $repaymentInfos = array();
            foreach ($req->repaymentFrequencys as $key => $repaymentFrequency) {
                $repaymentInfo = (object)[];
                $repaymentInfo->repaymentFrequencyId = $repaymentFrequency;
                $repaymentInfo->gracePeriod = $req->gracePeriods[$key];
                $installments = explode(',', $req->installments[$key]);
                $installments = array_filter($installments);
                $repaymentInfo->eligibleNumberOfInstallments = implode(',', $installments);

                array_push($repaymentInfos, $repaymentInfo);
            }
            return json_encode($repaymentInfos);
        } else {
            $repaymentInfo = (object)[];
            $repaymentInfo->eligibleMonths = $req->otElgdMonths;
            return json_encode($repaymentInfo);
        }
    }

    public function getPassport($req, $operationType, $obj = null)
    {
        $errorMsg = null;

        // trim the name and codes
        $req->name = trim($req->name);
        $req->shortName = trim($req->shortName);
        $req->code = trim($req->code);

        if ($operationType != 'delete') {

            $rules = array(
                'name'                          => 'required',
                'shortName'                     => 'required',
                'code'                          => 'required',
                'minLoanAmount'                 => 'required|numeric|min:1',
                'avgLoanAmount'                 => 'required|numeric|min:1',
                'maxLoanAmount'                 => 'required|numeric|min:1',
                'yearsEligibleWriteOff'         => 'required|numeric',
                'isInsuranceApplicable'         => 'required',
                'formFee'                       => 'required|numeric',
                'additionalFee'                 => 'required|numeric',
                'additionalFreeForFirstTime'    => 'required|numeric',
            );

            if ($operationType == 'store') {
                $rules = array_merge($rules, array(
                    'productCategoryId'             => 'required',
                    'fundingOrganizationId'         => 'required',
                    'startDate'                     => 'required|date',
                ));

                if ($req->fundingOrganizationId == 1) {
                    $rules['pksfFundId'] = 'required';
                }

                if ($req->isPrimaryProduct == 0) {
                    $rules['isMultipleLoanAllowed'] = 'required';
                    $rules['productTypeId'] = 'required';
                }
            }

            if ($req->isPrimaryProduct == 0 && $req->productTypeId == 2) {
                $rules['otElgdMonths'] = 'required';
            }

            if ($req->isInsuranceApplicable == 1) {
                $rules['insuranceCalculationMethodId'] = 'required';

                if ($req->insuranceCalculationMethodId == 1) {
                    $rules['insurancePercentage'] = 'required|numeric|max:100';
                } else {
                    $rules['fixedinsurance'] = 'required|numeric';
                }
            }

            $validator = Validator::make($req->all(), $rules);

            $attributes = array(
                'name'                          => 'Name',
                'shortName'                     => 'Short Name',
                'code'                          => 'Code',
                'productCategoryId'             => 'Product Category',
                'fundingOrganizationId'         => 'Funding Organization',
                'startDate'                     => 'Start Date',
                'pksfFundId'                    => 'PKSF Fund',
                'minLoanAmount'                 => 'Minimum Loan Amount',
                'avgLoanAmount'                 => 'Avg. Loan Amount',
                'maxLoanAmount'                 => 'Maximum Loan Amount',
                'yearsEligibleWriteOff'         => 'years Eligible Write Off',
                'isInsuranceApplicable'         => 'Is Insurance Applicable',
                'formFee'                       => 'Form Fee',
                'additionalFee'                 => 'Additional Fee',
                'additionalFreeForFirstTime'    => 'Additional Free For First Time',
                'isMultipleLoanAllowed'         => 'Is Multiple Loan Allowed',
                'productTypeId'                 => 'Product Type',
                'otElgdMonths'                  => 'Periods In Month',
                'insuranceCalculationMethodId'  => 'Insurance Calculation Method',
                'insurancePercentage'           => 'Insurance Percentage',
                'fixedinsurance'                => 'Fixed Insurance Amount',
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }

            // validate repayment frequency aray if it is regular loan
            if ($req->isPrimaryProduct == 1 || ($req->isPrimaryProduct == 0 && $req->productTypeId == 1)) {

                if (!is_array($req->repaymentFrequencys) || !is_array($req->gracePeriods) || !is_array($req->installments)) {
                    $errorMsg = 'Please give repayment information';
                } else {
                    // reindex repayment info
                    $req->repaymentFrequencys = array_values(array_filter($req->repaymentFrequencys));
                    $req->gracePeriods = array_values(array_filter($req->gracePeriods));
                    $req->installments = array_values(array_filter($req->installments, function ($value) {
                        return $value != null;
                    }));

                    if ((count($req->repaymentFrequencys) != count($req->gracePeriods) || count($req->repaymentFrequencys) != count($req->installments))) {
                        $errorMsg = 'Please fill repayment information properly';
                    }
                }
            }

            // check Max,Avg,Min Loan amount
            if ($req->maxLoanAmount < $req->avgLoanAmount || $req->maxLoanAmount < $req->minLoanAmount) {
                $errorMsg = 'Maximum Loan amount could not be less than Average/Minimum Loan Amount.';
            }
            if ($req->avgLoanAmount < $req->minLoanAmount) {
                $errorMsg = 'Average Loan amount could not be less than Minimum Loan Amount.';
            }
        }



        // check the uniqueness of name, shoer name, and product code
        if ($operationType == 'store' || $operationType == 'update') {
            $nameExists = DB::table('mfn_loan_products')
                ->where([
                    ['is_delete', 0],
                    ['name', $req->name]
                ]);
            $operationType == 'update' ? $nameExists->where('id', '!=', $obj->id) : false;
            $nameExists = $nameExists->exists();

            if ($nameExists) {
                $errorMsg = "Name already exists";
            }

            $shortNameExists = DB::table('mfn_loan_products')
                ->where([
                    ['is_delete', 0],
                    ['shortName', $req->shortName]
                ]);
            $operationType == 'update' ? $shortNameExists->where('id', '!=', $obj->id) : false;
            $shortNameExists = $shortNameExists->exists();

            if ($shortNameExists) {
                $errorMsg = "Short Name already exists";
            }

            $codeExists = DB::table('mfn_loan_products')
                ->where([
                    ['is_delete', 0],
                    ['productCode', $req->code]
                ]);
            $operationType == 'update' ? $codeExists->where('id', '!=', $obj->id) : false;
            $codeExists = $codeExists->exists();


            if ($codeExists) {
                $errorMsg = "Product Code already exists";
            }
        }

        if ($operationType == 'delete') {
            // check this product loan is exits or not
            // check this product member is exits or not
            // check this product exits in product transfer or not
            // check this product exits in branch or not
            // if so, then it could not be deleted

            $isAssingedToBranch = DB::table('mfn_branch_products')
                ->where('loanProductIds', 'LIKE', '%' . $obj->id . '%')
                ->exists();

            if ($isAssingedToBranch) {
                $errorMsg = 'Product assigned to branch.';
            }

            $existsInMember = DB::table('mfn_members')
                ->where([
                    ['is_delete', 0],
                    ['primaryProductId', $obj->id],
                ])
                ->exists();

            if ($existsInMember) {
                $errorMsg = 'Member exists belongs to this product';
            }

            $productId = $obj->id;

            $exitsIntoProductTransfer = DB::table('mfn_member_primary_product_transfers')
                ->where('is_delete', 0)
                ->where(function ($query) use ($productId) {
                    $query->where('oldProductId', $productId)
                        ->orWhere('newProductId', $productId);
                })
                ->exists();

            if ($exitsIntoProductTransfer) {
                $errorMsg = 'Product exists into transfer history';
            }

            $existsInLoan = DB::table('mfn_loans')
                ->where('is_delete', 0)
                ->where(function ($query) use ($productId) {
                    $query->where('productId', $productId)
                        ->orWhere('primaryProductId', $productId);
                })
                ->exists();

            if ($existsInLoan) {
                $errorMsg = 'Loan exists belongs to this product';
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $passport;
    }

    public function storeLoanProductHistory($product)
    {
        $productInfo =  clone $product;
        unset($productInfo->id);
        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $findHistory = DB::table('mfn_loan_products_history')->where([['productId',$productInfo->id],['effectiveTo', $sysDate]])->first();
        
        if(!$findHistory){
            $history =  new LoanProductHistory;
            $history->productId = $product->id;
            $history->effectiveTo = $sysDate;
            $history->content = json_encode($product);
            $history->save();
        }
        
    }
}
