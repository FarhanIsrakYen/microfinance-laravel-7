<?php

namespace App\Http\Controllers\MFN\GConfig;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
// use Response;
// use Redirect;
use App\Model\MFN\SavingsProduct;
use App\Model\MFN\SavingsProductInterestRate as SPIR;

class SavingsProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $req)
    {
        if (!$req->ajax()) {
            return view('MFN.SavingsProduct.index');
        }

        $columns = ['sp.name', 'sp.shortName', 'sp.productCode', 'spt.name', 'sp.effectiveDate'];

        $limit = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order = $columns[$orderColumnIndex];
        $dir = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $products = DB::table('mfn_savings_product AS sp')
            ->leftJoin('mfn_savings_product_type AS spt', 'spt.id', 'sp.productTypeId')
            ->where('sp.is_delete', 0)
            ->orderBy($order, $dir)
            ->select(DB::raw("sp.name, sp.shortName, sp.productCode, spt.name AS productType, sp.effectiveDate, sp.id"));

        if ($search != null) {
            $products->where(function ($query) use ($search) {
                $query->where('sp.name', 'LIKE', "%{$search}%")
                    ->orWhere('sp.productCode', 'LIKE', "%{$search}%")
                    ->orWhere('spt.name', 'LIKE', "%{$search}%");
            });
        }

        $totalData = (clone $products)->count();
        $products = $products->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;
        foreach ($products as $key => $product) {
            $products[$key]->effectiveDate = Carbon::parse($product->effectiveDate)->format('d-m-Y');
            $products[$key]->sl            = $sl++;
            $products[$key]->action        = RoleService::roleWiseArray($this->GlobalRole, $products[$key]->id);
        }

        $data = array(
            // "draw"              => intval($req->input('draw')),
            "recordsTotal"      => $totalData,
            "recordsFiltered"   => $totalData,
            'data'              => $products,
        );

        return response()->json($data);
    }

    public function view($savingsProductId)
    {
        $product = DB::table('mfn_savings_product')->where('id', $savingsProductId)->first();
        $interests = DB::table('mfn_savings_product_interest_rates')
            ->where([
                ['is_delete', 0],
                ['productId', $product->id],
            ])
            ->orderBy('durationMonth', 'desc')
            ->get();

        $productType = DB::table('mfn_savings_product_type')->where('id', $product->productTypeId)->value('name');
        $collectionFrequency = $product->productTypeId == 1 ? DB::table('mfn_savings_collection_frequency')->where('id', $product->collectionFrequencyId)->value('name') : 'N/A';

        $interestCalculationMethod = DB::table('mfn_savings_interest_cal_methods')->where('id', $product->interestCalculationMethodId)->value('name');
        $interestAvgMethodPeriod = DB::table('mfn_savings_interest_avg_methos_periods')->where('id', $product->interestAvgMethodPeriodId)->value('name');

        $data = array(
            'product' => $product,
            'interests' => $interests,
            'productType' => $productType,
            'collectionFrequency' => $collectionFrequency,
            'interestCalculationMethod' => $interestCalculationMethod,
            'interestAvgMethodPeriod' => $interestAvgMethodPeriod,
        );

        return view('MFN.SavingsProduct.view', $data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $savingsCollectionFrequencies = DB::table('mfn_savings_collection_frequency')
            ->where([
                ['status', 1],
                ['is_delete', 0]
            ])
            ->get();

        $interestAvgMethosPeriods = DB::table('mfn_savings_interest_avg_methos_periods')->get();

        $interestCalMethods = DB::table('mfn_savings_interest_cal_methods')->get();

        $productTypes = DB::table('mfn_savings_product_type')->where('status', 1)->get();

        $data = array(
            'savingsCollectionFrequencies' => $savingsCollectionFrequencies,
            'interestAvgMethosPeriods' => $interestAvgMethosPeriods,
            'interestCalMethods' => $interestCalMethods,
            'productTypes' => $productTypes,
        );

        return view('MFN.SavingsProduct.add', $data);
    }

    public function store(Request $req)
    {
        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();

        $passport = $this->getPassport($req, $operationType = 'store');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {
            $product = new SavingsProduct;
            $product->name = $req->name;
            $product->shortName = $req->shortName;
            $product->productCode = $req->productCode;
            $product->productTypeId = $req->productType;
            $product->effectiveDate = Carbon::parse($req->startDate);
            $product->minimumSavingsBalance = $req->productType == 2 ? $req->minSavingsBalance : 0;
            $product->collectionFrequencyId = $req->productType == 1 ? $req->savingsCollectionFrequency : 0;
            $product->generateInterestProbation = $req->generateInterestProbation;
            $product->interestCalculationMethodId = $req->productType == 1 ? $req->interestCalculationMethod : 0;
            $product->isMultipleSavingsAllowed = $req->isMultipleSavings;
            $product->isNomineeRequired = $req->isNomineeRequired;
            $product->isClosingChargeApplicable = $req->isClosingChargeApplicable;
            $product->closingCharge = $req->isClosingChargeApplicable == 'Yes' ? $req->closingCharge : 0;
            $product->isPartialWithdrawAllowed = $req->isPartialWithdrawAllowed;
            $product->isPartialInterestWithdrawAllowed = $req->isPartialInterestWithdrawAllowed;
            $product->isDueMemberGettingInterest = $req->isDueMemberGettingInterest;
            $product->onClosingInterestEditable = $req->onClosingInterestEditable;
            $product->isMandatoryOnMemberAdmission = $req->isMandatoryOnMemberAdmission;
            $product->status = $req->status;
            $product->created_by = Auth::user()->id;
            $product->created_at = Carbon::now();
            $product->save();

            // INSET INTEREST RATES

            if ($product->productTypeId == 1) { // if it is a regulat=r product
                $interest = new SPIR;
                $interest->productId        = $product->id;
                $interest->interestRate     = $req->interestRateRegular;
                $interest->effectiveDate    = $product->effectiveDate;
                $interest->created_by       = Auth::user()->id;
                $interest->created_at       = Carbon::now();
                $interest->save();
            } elseif ($product->productTypeId == 2) { // if it is a one time product

                $maturePeriods = json_decode($req->maturePeriods);

                foreach ($maturePeriods as $maturePeriod) {
                    $interest = new SPIR;
                    $interest->productId        = $product->id;
                    $interest->interestRate     = $maturePeriod->interestRate;
                    $interest->effectiveDate    = $product->effectiveDate;
                    $interest->durationMonth    = $maturePeriod->month;
                    $interest->created_by       = Auth::user()->id;
                    $interest->created_at       = Carbon::now();
                    $interest->save();

                    foreach ($maturePeriod->partials as $partial) {

                        $partialInterest = new SPIR;
                        $partialInterest->productId         = $product->id;
                        $partialInterest->interestRate      = $partial->interestRate;
                        $partialInterest->effectiveDate     = $product->effectiveDate;
                        $partialInterest->durationMonth     = $partial->month;
                        $partialInterest->parentId          = $interest->id;
                        $partialInterest->created_by        = Auth::user()->id;
                        $partialInterest->created_at        = Carbon::now();
                        $partialInterest->save();
                    }
                }
            }

            DB::commit();

            $notification = array(
                'message'       => 'Successfully Inserted',
                'alert-type'    => 'success',
            );

            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong',
                'consoleMsg'    => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage()
            );
            return response()->json($notification);
        }
    }


    public function edit(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->update($req);
        }

        $savingsCollectionFrequencies = DB::table('mfn_savings_collection_frequency')
            ->where([
                ['status', 1],
                ['is_delete', 0]
            ])
            ->get();

        $interestAvgMethosPeriods = DB::table('mfn_savings_interest_avg_methos_periods')->get();

        $interestCalMethods = DB::table('mfn_savings_interest_cal_methods')->get();

        $product = DB::table('mfn_savings_product')->where('id', $req->savingsProductId)->first();

        $productType = DB::table('mfn_savings_product_type')->where('id', $product->productTypeId)->value('name');
        $collectionFrequency = DB::table('mfn_savings_collection_frequency')->where('id', $product->collectionFrequencyId)->value('name');

        $data = array(
            'savingsCollectionFrequencies' => $savingsCollectionFrequencies,
            'interestAvgMethosPeriods' => $interestAvgMethosPeriods,
            'interestCalMethods' => $interestCalMethods,
            'productType' => $productType,
            'collectionFrequency' => $collectionFrequency,
            'product' => $product,
        );

        return view('MFN.SavingsProduct.edit', $data);
    }

    public function update(Request $req)
    {
        $product = SavingsProduct::find(decrypt($req->productId));

        $passport = $this->getPassport($req, $operationType = 'update', $product);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $product->name = $req->name;
        $product->shortName = $req->shortName;
        $product->productCode = $req->productCode;
        $product->minimumSavingsBalance = $product->productTypeId == 2 ? $req->minSavingsBalance : 0;
        $product->generateInterestProbation = $req->generateInterestProbation;
        $product->interestCalculationMethodId = $product->productTypeId == 1 ? $req->interestCalculationMethod : 0;
        $product->isMultipleSavingsAllowed = $req->isMultipleSavings;
        $product->isNomineeRequired = $req->isNomineeRequired;
        $product->isClosingChargeApplicable = $req->isClosingChargeApplicable;
        $product->closingCharge = $req->isClosingChargeApplicable == 'Yes' ? $req->closingCharge : 0;
        $product->isPartialWithdrawAllowed = $req->isPartialWithdrawAllowed;
        $product->isPartialInterestWithdrawAllowed = $req->isPartialInterestWithdrawAllowed;
        $product->isDueMemberGettingInterest = $req->isDueMemberGettingInterest;
        $product->onClosingInterestEditable = $req->onClosingInterestEditable;
        $product->isMandatoryOnMemberAdmission = $req->isMandatoryOnMemberAdmission;
        $product->status = $req->status;
        $product->updated_by = Auth::user()->id;
        $product->updated_at = Carbon::now();
        $product->save();

        $notification = array(
            'message'       => 'Successfully Updated',
            'alert-type'    => 'success',
        );

        return response()->json($notification);
    }

    public function delete(Request $req)
    {
        $product = SavingsProduct::find($req->productId);

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

    public function getPassport($req, $operationType, $product = null)
    {
        $errorMsg = null;

        $req->name = trim($req->name);
        $req->shortName = trim($req->shortName);
        $req->productCode = trim($req->productCode);

        if ($operationType != 'delete') {

            $rules = array(
                'name'                              => 'required',
                'shortName'                         => 'required',
                'productCode'                       => 'required',
                'isMultipleSavings'                 => 'required',
                'isNomineeRequired'                 => 'required',
                'isClosingChargeApplicable'         => 'required',
                'isPartialWithdrawAllowed'          => 'required',
                'isPartialInterestWithdrawAllowed'  => 'required',
                'isDueMemberGettingInterest'        => 'required',
                'generateInterestProbation'         => 'required',
                'isMandatoryOnMemberAdmission'      => 'required',
            );

            if ($operationType == 'store') {
                $rules = array_merge($rules, array(
                    'startDate'                         => 'required|date',
                    'productType'                       => 'required',
                ));
            }

            if ($req->isClosingChargeApplicable == 'Yes') {
                $rules['closingCharge'] = 'required';
            }

            if ($req->productType == 1 && $operationType == 'store') {
                $rules = array_merge($rules, array(
                    'savingsCollectionFrequency'    => 'required',
                    'interestRateRegular'           => 'required|numeric',
                    'interestCalculationMethod'     => 'required',
                ));

                if ($req->interestCalculationMethod == 2) {
                    $rules['interestAvgMethodPeriod'] = 'required';
                }
            } elseif ($req->productType == 2) {

                $rules['minSavingsBalance'] = 'required|numeric';

                if ($operationType == 'store') {
                    // here we will validate the FDR interest data, e.g. maturePeriods data.
                    $maturePeriods = json_decode($req->maturePeriods);

                    if (count($maturePeriods) == 0) {
                        $errorMsg = 'Please give period and interest details.';
                    }

                    $matureMonths = [];

                    foreach ($maturePeriods as $maturePeriod) {
                        array_push($matureMonths, $maturePeriod->month);
                        if ($maturePeriod->month == '' || $maturePeriod->interestRate == '') {
                            $errorMsg = 'Interest Details fields should not be Empty.';
                            break;
                        }
                        $partialMonths = [];
                        foreach ($maturePeriod->partials as $partial) {

                            array_push($partialMonths, $partial->month);

                            if ($partial->month == '' || $partial->interestRate == '') {
                                $errorMsg = 'Interest Details fields should not be Empty.';
                                break;
                            }

                            if ($partial->month >= $maturePeriod->month) {
                                $errorMsg = 'Partial Period should be less than the Parent Period.';
                                break;
                            }
                        }

                        if (count($partialMonths) != count(array_unique($partialMonths))) {
                            $errorMsg = 'Partial Months should be unique under period '.$maturePeriod->month;
                        }
                    }

                    if (count($matureMonths) != count(array_unique($matureMonths))) {
                        $errorMsg = 'Mature Months should be unique';
                    }
                }
            }

            $validator = Validator::make($req->all(), $rules);

            $attributes = array(
                'name'                          => 'Name',
                'shortName'                     => 'Short Name',
                'productCode'                   => 'Product Code',
                'startDate'                     => 'Start Date',
                'minSavingsBalance'             => 'Minimum Savings Balance',
                'productType'                   => 'Product Type',
                'savingsCollectionFrequency'    => 'Savings Collection Frequency',
                'interestRateRegular'           => 'Interest Rate',
                'generateInterestProbation'     => 'Generate Interest Probation',
                'interestCalculationMethod'     => 'Interest Calculation Method',
                'interestAvgMethodPeriod'       => 'Interest Avg. Method Period',

                'isMultipleSavings'                 => 'Is Multiple Savings',
                'isNomineeRequired'                 => 'Is Nominee Required',
                'isClosingChargeApplicable'         => 'Is Closing Charge Applicable',
                'isPartialWithdrawAllowed'          => 'Is Partial Withdraw Allowed',
                'isPartialInterestWithdrawAllowed'  => 'Is Partial Interest Withdraw Allowed',
                'isDueMemberGettingInterest'        => 'Is Due Member Getting Interest',
                'isMandatoryOnMemberAdmission'      => 'Is Mandatory On Member Admission',
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }

            // check the uniqueness of the product name, short name and code
            $isNameExists = DB::table('mfn_savings_product')
                ->where([
                    ['is_delete', 0],
                    ['name', $req->name],
                ]);
            $operationType == 'update' ? $isNameExists->where('id', '!=', $product->id) : false;
            $isNameExists = $isNameExists->exists();
            $isNameExists ? $errorMsg = 'Name already exists' : false;

            $isShortNameExists = DB::table('mfn_savings_product')
                ->where([
                    ['is_delete', 0],
                    ['shortName', $req->shortName],
                ]);
            $operationType == 'update' ? $isShortNameExists->where('id', '!=', $product->id) : false;
            $isShortNameExists = $isShortNameExists->exists();
            $isShortNameExists ? $errorMsg = 'Short Name already exists' : false;

            $isProductCodeExists = DB::table('mfn_savings_product')
                ->where([
                    ['is_delete', 0],
                    ['productCode', $req->productCode],
                ]);
            $operationType == 'update' ? $isProductCodeExists->where('id', '!=', $product->id) : false;
            $isProductCodeExists = $isProductCodeExists->exists();
            $isProductCodeExists ? $errorMsg = 'Product Code already exists' : false;
        }

        if ($operationType == 'delete') {
            $isAnyAccountExists = DB::table('mfn_savings_accounts')
                ->where([
                    ['is_delete', 0],
                    ['savingsProductId', $product->id],
                ])
                ->exists();

            if ($isAnyAccountExists) {
                $errorMsg = 'Account exists, you can not delete.';
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
