<?php

namespace App\Http\Controllers\MFN\GConfig;

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
use App\Model\MFN\LoanProductInterestRate as LPIR;

class LoanProductInterestController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $product = DB::table('mfn_loan_products')->where('id', $req->productId)->first();

        // get the min effective date
        $minEffectiveDate = Carbon::parse($product->startDate)->format('Y-m-d');

        // this is for regular product
        $eligibleRepaymentFrequencies = null;
        if ($product->productTypeId == 1) {
            $regularLoanRepaymentInfo = json_decode($product->repaymentInfo);
            $regularLoanRepaymentInfo = collect($regularLoanRepaymentInfo);
            $eligibleRepaymentFrequencies = DB::table('mfn_loan_repayment_frequency')
                ->where([
                    ['is_delete', 0],
                    ['status', 1]
                ])
                ->whereIn('id', $regularLoanRepaymentInfo->pluck('repaymentFrequencyId')->toArray())
                ->get();
        }

        $interestCalculationMethods = DB::table('mfn_loan_interest_calculation_methods')->get();

        $data = array(
            'product' => $product,
            'minEffectiveDate' => $minEffectiveDate,
            'eligibleRepaymentFrequencies' => $eligibleRepaymentFrequencies,
            'interestCalculationMethods' => $interestCalculationMethods,
        );

        return view('MFN.LoanProductsInterest.add', $data);
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

        $product = DB::table('mfn_loan_products')->where('id', decrypt($req->productId))->first();

        DB::beginTransaction();

        try {

            $lpir = new LPIR;
            $lpir->productId = $product->id;
            $lpir->repaymentFrequencyId = $product->productTypeId == 1 ? $req->reapymentFrequencyId : 0;
            $lpir->numberOfInstallment = $product->productTypeId == 1 ? $req->installmentNumber : 0;
            $lpir->effectiveDate = Carbon::parse($req->effectiveDate)->format('Y-m-d');
            $lpir->interestRatePerYear = $req->interestRate;
            $lpir->interestRateIndexPerYear = $product->productTypeId == 1 ? $req->interestRateIndexPerYear : 0;
            $lpir->interestRateIndex = $product->productTypeId == 1 ? $req->interestRateIndex : 0;
            $lpir->interestCalculationMethodId = $req->interestCalculationMethodId;
            $lpir->created_at = Carbon::now();
            $lpir->created_by = Auth::user()->id;
            $lpir->save();

            // set previos rate status to zero
            if ($product->productTypeId == 1) {
                DB::table('mfn_loan_product_interest_rates')
                    ->where([
                        ['productId', $product->id],
                        ['repaymentFrequencyId', $req->reapymentFrequencyId],
                        ['numberOfInstallment', $req->installmentNumber],
                        ['id', '!=', $lpir->id],
                    ])
                    ->update(['status' => 0, 'validTill' => Carbon::parse($req->effectiveDate)->subDay()->format('Y-m-d')]);
            } elseif ($product->productTypeId == 2) {
                DB::table('mfn_loan_product_interest_rates')
                    ->where([
                        ['productId', $product->id],
                        ['id', '!=', $lpir->id],
                    ])
                    ->update(['status' => 0, 'validTill' => Carbon::parse($req->effectiveDate)->subDay()->format('Y-m-d')]);
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

    public function getProductFrequencyWiseInstallments(Request $req)
    {
        $product = DB::table('mfn_loan_products')->where('id', $req->productId)->first();
        $regularLoanRepaymentInfo = json_decode($product->repaymentInfo);
        $regularLoanRepaymentInfo = collect($regularLoanRepaymentInfo);
        $regularLoanRepaymentInfo = $regularLoanRepaymentInfo->where('repaymentFrequencyId', $req->reapymentFrequencyId)->first();

        $eligibleNumberOfInstallments = explode(',', $regularLoanRepaymentInfo->eligibleNumberOfInstallments);
        sort($eligibleNumberOfInstallments);

        return response()->json($eligibleNumberOfInstallments);
    }

    public function getPassport($req, $operationType)
    {
        $errorMsg = null;

        $product = DB::table('mfn_loan_products')->where('id', decrypt($req->productId))->first();
        $interestRateEffectiveDate = Carbon::parse($req->effectiveDate)->format('Y-m-d');

        if ($operationType != 'delete') {
            $rules = array(
                'interestRate'  => 'required',
                'interestCalculationMethodId' => 'required',
                'effectiveDate' => 'required|date',
            );

            if ($product->productTypeId == 1) {
                $rules = array_merge($rules, array(
                    'reapymentFrequencyId' => 'required',
                    'installmentNumber' => 'required',
                    'interestRateIndexPerYear' => 'required|numeric',
                    'interestRateIndex' => 'required|numeric',
                ));
            }
            $validator = Validator::make($req->all(), $rules);

            $attributes = array(
                'interestRate' => 'Interest Rate',
                'effectiveDate' => 'Effective Date',
                'reapymentFrequencyId' => 'Reapyment Frequency',
                'installmentNumber' => 'Installment Number',
                'interestRateIndexPerYear' => 'Interest Rate Index Per Year',
                'interestRateIndex' => 'Interest Rate Index',
                'interestCalculationMethodId' => 'Interest Calculation Method',
            );

            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        // Effective date should be after/on Product start date
        if ($interestRateEffectiveDate < $product->startDate) {
            $errorMsg = 'Effective date should be after/on Product start date.';
        }

        // Effective date should be after the last Interest Rate Effective date if exists.
        $lastIneterestRateDate = DB::table('mfn_loan_product_interest_rates')
            ->where([
                ['productId', $product->id],
                ['status', 1],
                ['is_delete', 0],
            ]);

        if ($product->productTypeId == 1) {
            $lastIneterestRateDate->where([
                ['repaymentFrequencyId', $req->reapymentFrequencyId],
                ['numberOfInstallment', $req->installmentNumber],
            ]);
        }

        $lastIneterestRateDate = $lastIneterestRateDate->max('effectiveDate');

        if ($lastIneterestRateDate != null) {
            if ($interestRateEffectiveDate < $lastIneterestRateDate) {
                $errorMsg = 'Effective date should be after last Product Interest rate effective date.';
            }
        }

        // if any loan disbursed of this particular signeture then effective date should be after this disbursement date
        $loanExists = DB::table('mfn_loans')
            ->where([
                ['is_delete', 0],
                ['productId', $product->id],
                ['disbursementDate', '>=', $interestRateEffectiveDate],
            ]);

        if ($product->productTypeId == 1) {
            $loanExists->where([
                ['repaymentFrequencyId', $req->reapymentFrequencyId],
                ['numberOfInstallment', $req->installmentNumber]
            ]);
        }

        $loanExists = $loanExists->exists();

        if ($loanExists) {
            $errorMsg = 'Loan exists of this products afeter/on effective date';
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $passport;
    }
}
