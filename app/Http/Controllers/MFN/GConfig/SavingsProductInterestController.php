<?php

namespace App\Http\Controllers\MFN\GConfig;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
// use Response;
// use Redirect;
use App\Model\MFN\SavingsProduct;
use App\Model\MFN\SavingsProductInterestRate as SPIR;

class SavingsProductInterestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function view($interestId)
    {
        $interest = DB::table('mfn_savings_product_interest_rates')
            ->where('id', $interestId)
            ->first();

        $partials = DB::table('mfn_savings_product_interest_rates')
            ->where([
                ['is_delete', 0],
                ['parentId', $interest->id],
            ])
            ->orderBy('durationMonth', 'desc')
            ->get();

        $data = array(
            'interest' => $interest,
            'partials' => $partials,
        );

        return view('MFN.SavingsProductInterest.view', $data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $product = DB::table('mfn_savings_product')->where('id', $req->productId)->first();

        $activeInterestRates = DB::table('mfn_savings_product_interest_rates')
            ->where([
                ['is_delete', 0],
                ['status', 1],
                ['productId', $product->id],
            ])
            ->get();

        $data = array(
            'product' => $product,
            'activeInterestRates' => $activeInterestRates,
        );

        return view('MFN.SavingsProductInterest.add', $data);
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

        $product = DB::table('mfn_savings_product')->where('id', decrypt($req->productId))->first();

        DB::beginTransaction();

        try {
            if ($product->productTypeId == 1) { // if it is regular produt
                $interest = new SPIR;
                $interest->productId        = $product->id;
                $interest->interestRate     = $req->interestRate;
                $interest->effectiveDate    = Carbon::parse($req->effectiveDate);
                $interest->created_by       = Auth::user()->id;
                $interest->created_at       = Carbon::now();
                $interest->save();

                // set previoos interest rates status to zero
                DB::table('mfn_savings_product_interest_rates')
                    ->where([
                        ['productId', $product->id],
                        ['status', 1],
                        ['id', '!=', $interest->id]
                    ])
                    ->update([
                        'status' => 0,
                        'validTill' => Carbon::parse($req->effectiveDate)->subDay()
                    ]);
            } elseif ($product->productTypeId == 2) { // if it is one time product

                $activeInterestRateIds = [];

                $maturePeriods = json_decode($req->maturePeriods);

                foreach ($maturePeriods as $maturePeriod) {

                    if ($maturePeriod->isModified == 1) {
                        $interest = new SPIR;
                        $interest->productId        = $product->id;
                        $interest->interestRate     = $maturePeriod->interestRate;
                        $interest->effectiveDate    = Carbon::parse($maturePeriod->effectiveDate);
                        $interest->durationMonth    = $maturePeriod->month;
                        $interest->created_by       = Auth::user()->id;
                        $interest->created_at       = Carbon::now();
                        $interest->save();

                        // set the previuos mature period and its partial's effective date.
                        $previousInterestId = SPIR::where([
                            ['productId', $product->id],
                            ['durationMonth', $maturePeriod->month],
                            ['is_delete', 0],
                            ['status', 1],
                            ['id', '!=', $interest->id],
                        ])
                            ->value('id');

                        if ($previousInterestId > 0) {
                            DB::table('mfn_savings_product_interest_rates')
                                ->where('id', $previousInterestId)
                                ->orWhere(function ($query) use ($previousInterestId) {
                                    $query->where([
                                        ['parentId', $previousInterestId],
                                        ['validTill', '0000-00-00'],
                                    ]);
                                })
                                ->update([
                                    'status' => 0,
                                    'validTill' => Carbon::parse($interest->effectiveDate)->subDay()
                                ]);
                        }
                    } else {
                        $interest = SPIR::where([
                            ['productId', $product->id],
                            ['durationMonth', $maturePeriod->month],
                            ['is_delete', 0],
                            ['status', 1],
                        ])
                            ->first();
                    }
                    array_push($activeInterestRateIds, $interest->id);

                    foreach ($maturePeriod->partials as $partial) {

                        if ($partial->isModified == 1 || $maturePeriod->isModified == 1) {
                            if ($partial->isModified == 1) {
                                $effectiveDate = Carbon::parse($partial->effectiveDate);
                            } else {
                                $effectiveDate = Carbon::parse($maturePeriod->effectiveDate);
                            }
                            $partialInterest = new SPIR;
                            $partialInterest->productId         = $product->id;
                            $partialInterest->interestRate      = $partial->interestRate;
                            $partialInterest->effectiveDate     = $effectiveDate;
                            $partialInterest->durationMonth     = $partial->month;
                            $partialInterest->parentId          = $interest->id;
                            $partialInterest->created_by        = Auth::user()->id;
                            $partialInterest->created_at        = Carbon::now();
                            $partialInterest->save();
                        } else {
                            $partialInterest = SPIR::where([
                                ['productId', $product->id],
                                ['durationMonth', $partial->month],
                                ['is_delete', 0],
                                ['status', 1],
                            ])
                                ->first();
                        }

                        array_push($activeInterestRateIds, $partialInterest->id);
                    }
                }

                $removedMaturePeriods = json_decode($req->removedMaturePeriods);

                foreach ($removedMaturePeriods as $removedMaturePeriod) {
                    $removedMaturePeriodId = $removedMaturePeriod->id;
                    DB::table('mfn_savings_product_interest_rates')
                        ->where('id', $removedMaturePeriod->id)
                        ->orWhere(function ($query) use ($removedMaturePeriodId) {
                            $query->where([
                                ['parentId', $removedMaturePeriodId],
                                ['validTill', '0000-00-00'],
                            ]);
                        })
                        ->update([
                            'status' => 0,
                            'validTill' => Carbon::parse($removedMaturePeriod->effectiveDate)
                        ]);
                }

                $removedPartialPeriods = json_decode($req->removedPartialPeriods);

                foreach ($removedPartialPeriods as $removedPartialPeriod) {
                    $removedPartialPeriodId = $removedPartialPeriod->id;
                    DB::table('mfn_savings_product_interest_rates')
                        ->where('id', $removedPartialPeriod->id)
                        ->update([
                            'status' => 0,
                            'validTill' => Carbon::parse($removedPartialPeriod->effectiveDate)
                        ]);
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

    public function getPassport($req, $operationType)
    {
        $errorMsg = null;


        $product = DB::table('mfn_savings_product')->where('id', decrypt($req->productId))->first();

        if ($operationType != 'delete') {
            if ($product->productTypeId == 1) {
                $rules = array(
                    'interestRate'  => 'required|numeric',
                    'effectiveDate' => 'required|date',
                );

                $validator = Validator::make($req->all(), $rules);

                $attributes = array(
                    'interestRate' => 'Interest Rate',
                    'effectiveDate' => 'Effective Date',
                );
                $validator->setAttributeNames($attributes);

                if ($validator->fails()) {
                    $errorMsg = implode(' || ', $validator->messages()->all());
                }

                // check any savings account is created after/on the effective or not
                $anySavings = DB::table('mfn_savings_accounts')
                    ->where([
                        ['is_delete', 0],
                        ['savingsProductId', $product->id],
                        ['openingDate', '>=', Carbon::parse($req->effectiveDate)->format('Y-m-d')],
                    ])
                    ->exists();

                if ($anySavings) {
                    $errorMsg = 'Savings exists on/after ' . Carbon::parse($req->effectiveDate)->format('d-m-Y');
                }

                // check effective date is after to the previous once or not
                $previousEffectiveDate = DB::table('mfn_savings_product_interest_rates')
                    ->where([
                        ['is_delete', 0],
                        ['status', 1],
                        ['productId', $product->id],
                    ])
                    ->max('effectiveDate');

                if ($previousEffectiveDate > Carbon::parse($req->effectiveDate)->format('Y-m-d')) {
                    $errorMsg = "Effective Date should be after " . Carbon::parse($previousEffectiveDate)->format('d-m-Y');
                }
            } elseif ($product->productTypeId == 2) { // if the product is One Time

                $minEffectiveDate = '';

                $removedMaturePeriods = json_decode($req->removedMaturePeriods);
                foreach ($removedMaturePeriods as $removedMaturePeriod) {
                    if ($removedMaturePeriod->effectiveDate == '' || (bool)strtotime($removedMaturePeriod->effectiveDate) == false) {
                        $errorMsg = 'Please give all disabled effective dates';
                        break;
                    }
                    $removedMaturePeriod->effectiveDate = Carbon::parse($removedMaturePeriod->effectiveDate)->format('Y-m-d');
                    // if the removed mature period's validTill date is less than startting date
                    // then give an error messages
                    $interestRateObj = SPIR::find($removedMaturePeriod->id);
                    if ($removedMaturePeriod->effectiveDate <= $interestRateObj->effectiveDate) {
                        $errorMsg = 'Effective date of disabling of period ' . $interestRateObj->durationMonth . ' shoud be after ' . Carbon::parse($interestRateObj->effectiveDate)->format('d-m-Y');
                    }
                    if ($minEffectiveDate == '' || $minEffectiveDate > $removedMaturePeriod->effectiveDate) {
                        $minEffectiveDate = $removedMaturePeriod->effectiveDate;
                    }
                }

                $removedPartialPeriods = json_decode($req->removedPartialPeriods);
                foreach ($removedPartialPeriods as $removedPartialPeriod) {
                    if ($removedPartialPeriod->effectiveDate == '' || (bool)strtotime($removedPartialPeriod->effectiveDate) == false) {
                        $errorMsg = 'Please give all disabled partial effective dates';
                        break;
                    }
                    $removedPartialPeriod->effectiveDate = Carbon::parse($removedPartialPeriod->effectiveDate)->format('Y-m-d');
                    // if the removed partials period's validTill date is less than startting date
                    // then give an error messages
                    $interestRateObj = SPIR::find($removedPartialPeriod->id);
                    if ($removedPartialPeriod->effectiveDate <= $interestRateObj->effectiveDate) {
                        $errorMsg = 'Effective date of disabling of partial period ' . $interestRateObj->durationMonth . ' shoud be after ' . Carbon::parse($interestRateObj->effectiveDate)->format('d-m-Y');
                    }
                    if ($minEffectiveDate == '' || $minEffectiveDate > $removedPartialPeriod->effectiveDate) {
                        $minEffectiveDate = $removedPartialPeriod->effectiveDate;
                    }
                }

                // here we will validate the FDR interest data, e.g. maturePeriods data.
                $maturePeriods = json_decode($req->maturePeriods);

                if (count($maturePeriods) == 0) {
                    $errorMsg = 'Please give period and interest details.';
                }

                $matureMonths = [];

                foreach ($maturePeriods as $maturePeriod) {
                    array_push($matureMonths, $maturePeriod->month);
                    if ($maturePeriod->isModified == 1) {
                        if ($maturePeriod->month == '' || $maturePeriod->interestRate == '') {
                            $errorMsg = 'Interest Details fields should not be Empty.';
                            break;
                        }
                        if ($maturePeriod->month < 0 || $maturePeriod->interestRate < 0) {
                            $errorMsg = 'Interest Details fields should not be Negetive.';
                            break;
                        }
                        if ($maturePeriod->effectiveDate == '' || (bool)strtotime($maturePeriod->effectiveDate) == false) {
                            $errorMsg = 'Please give all effective dates properly.';
                            break;
                        }

                        // check effective date is after to the previous once or not
                        $previousEffectiveDate = DB::table('mfn_savings_product_interest_rates')
                            ->where([
                                ['is_delete', 0],
                                ['parentId', 0],
                                ['status', 1],
                                ['productId', $product->id],
                                ['durationMonth', $maturePeriod->month],
                            ])
                            ->max('effectiveDate');

                        $maturePeriod->effectiveDate = Carbon::parse($maturePeriod->effectiveDate)->format('Y-m-d');

                        if ($minEffectiveDate == '' || $minEffectiveDate > $maturePeriod->effectiveDate) {
                            $minEffectiveDate = $maturePeriod->effectiveDate;
                        }

                        if ($previousEffectiveDate > $maturePeriod->effectiveDate) {
                            $errorMsg = "Effective Date for Mature period $maturePeriod->month months should be after " . Carbon::parse($previousEffectiveDate)->format('d-m-Y');
                            break;
                        }
                    }
                    $partialMonths = [];
                    foreach ($maturePeriod->partials as $partial) {

                        array_push($partialMonths, $partial->month);

                        if ($partial->isModified == 1) {
                            if ($partial->month == '' || $partial->interestRate == '') {
                                $errorMsg = 'Interest Details fields should not be Empty.';
                                break;
                            }
                            if ($partial->month < 0 || $partial->interestRate < 0) {
                                $errorMsg = 'Interest Details fields should not be Negetive.';
                                break;
                            }
                            if ($partial->effectiveDate == '' || (bool)strtotime($partial->effectiveDate) == false) {
                                $errorMsg = 'Please give all effective dates properly.';
                                break;
                            }

                            // check effective date is after to the previous once or not
                            // it should be under same parent
                            $parentId = DB::table('mfn_savings_product_interest_rates')
                                ->where([
                                    ['is_delete', 0],
                                    ['parentId', 0],
                                    ['status', 1],
                                    ['productId', $product->id],
                                    ['durationMonth', $maturePeriod->month],
                                ])
                                ->value('id');

                            if ($parentId > 0) {
                                $previousEffectiveDate = DB::table('mfn_savings_product_interest_rates')
                                    ->where([
                                        ['is_delete', 0],
                                        ['parentId', $parentId],
                                        ['status', 1],
                                        ['productId', $product->id],
                                        ['durationMonth', $partial->month],
                                    ])
                                    ->max('effectiveDate');

                                $partial->effectiveDate = Carbon::parse($partial->effectiveDate)->format('Y-m-d');

                                if ($minEffectiveDate == '' || $minEffectiveDate > $partial->effectiveDate) {
                                    $minEffectiveDate = $partial->effectiveDate;
                                }

                                if ($previousEffectiveDate > $partial->effectiveDate) {
                                    $errorMsg = "Effective Date for Partial period $partial->month months should be after " . Carbon::parse($previousEffectiveDate)->format('d-m-Y');
                                    break;
                                }
                            }
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

                // check any savungs account exists after min effective date
                $anySavings = DB::table('mfn_savings_accounts')
                    ->where([
                        ['is_delete', 0],
                        ['savingsProductId', $product->id],
                        ['openingDate', '>=', $minEffectiveDate],
                    ])
                    ->exists();

                if ($anySavings && $minEffectiveDate != '') {
                    $errorMsg = 'Savings exists on/after ' . Carbon::parse($minEffectiveDate)->format('d-m-Y');
                }
            }
        }

        $isValid = $errorMsg == null ?true : false;

        $passport = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $passport;
    }
}
