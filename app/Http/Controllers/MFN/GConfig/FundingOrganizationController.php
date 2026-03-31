<?php

namespace App\Http\Controllers\MFN\GConfig;

use App\Services\RoleService;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;
use App\Model\MFN\FundingOrg;
use App\Rules\Unique;


class FundingOrganizationController extends Controller
{

    public function index(Request $req)
    {
        if (!$req->ajax()) {
            return view('MFN.GConfig.fundingOrganization.index');
        }

        $columns = ['name'];

        $limit = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order = $columns[$orderColumnIndex];
        $dir = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $funOrgs = DB::table('mfn_funding_orgs')
            ->where('is_delete', 0)
            ->orderBy($order, $dir);

        if ($search != null) {
            $funOrgs->where('name', 'LIKE', "%{$search}%");
        }

        $totalData = (clone $funOrgs)->count();
        $funOrgs = $funOrgs->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;
        foreach ($funOrgs as $key => $funOrg) {
            $funOrgs[$key]->sl      = $sl++;
            $funOrgs[$key]->id      = encrypt($funOrg->id);
            $funOrgs[$key]->action  = RoleService::roleWiseArray($this->GlobalRole, $funOrgs[$key]->id);
        }

        $data = array(
            "draw"              => intval($req->input('draw')),
            "recordsTotal"      => $totalData,
            "recordsFiltered"   => $totalData,
            'data'              => $funOrgs,
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

        if ($product->fundingOrgId == 1) {
            $pksfFund = DB::table('mfn_pksf_funds')
                ->where('id', $product->pksfFundId)
                ->value('name');
        } else {
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
            ->orderBy('effectiveDate', 'ASC')
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

        return view('MFN.GConfig.fundingOrganization.view', $data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        return view('MFN.GConfig.fundingOrganization.add');
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

        $fundingOrg = new FundingOrg;
        $fundingOrg->name = $req->name;
        $fundingOrg->created_at = Carbon::now();
        $fundingOrg->created_by = Auth::user()->id;
        $fundingOrg->save();

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

        $funOrg = FundingOrg::find(decrypt($req->id));

        $data = array(
            'funOrg' => $funOrg
        );

        return view('MFN.GConfig.fundingOrganization.edit', $data);
    }

    public function update(Request $req)
    {
        $fundingOrg = FundingOrg::find(decrypt($req->id));

        $passport = $this->getPassport($req, $operationType = 'update', $fundingOrg);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $fundingOrg->name = $req->name;
        $fundingOrg->updated_by = Auth::user()->id;
        $fundingOrg->save();

        $notification = array(
            'message'       => 'Successfully Updated',
            'alert-type'    => 'success',
        );

        return response()->json($notification);
    }

    public function delete(Request $req)
    {
        $fundingOrg = FundingOrg::find(decrypt($req->id));

        $passport = $this->getPassport($req, $operationType = 'delete', $fundingOrg);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $fundingOrg->is_delete = 1;
        $fundingOrg->save();

        $notification = array(
            'message'       => 'Successfully Deleted',
            'alert-type'    => 'success',
        );

        return response()->json($notification);
    }

    public function getPassport($req, $operationType, $funOrg = null)
    {
        $errorMsg = null;

        // trim the name and codes
        $req->name = trim($req->name);

        if ($operationType != 'delete') {

            if ($operationType == 'store') {
                $rules = array(
                    'name' => ['required', new Unique('mfn_funding_orgs')],
                );
            }

            if ($operationType == 'update') {
                $rules = array(
                    'name' => ['required', new Unique('mfn_funding_orgs', $funOrg->id)],
                );
            }


            $validator = Validator::make($req->all(), $rules);

            $attributes = array(
                'name'                          => 'Name',
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }


        if ($operationType == 'delete') {
            // check this funding org has any product or not
            // if so, then it could not be deleted

            $isProductExists = DB::table('mfn_loan_products')
                ->where('is_delete', 0)
                ->where('fundingOrgId', $funOrg->id)
                ->exists();

            if ($isProductExists) {
                $errorMsg = 'Product Exists.';
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
