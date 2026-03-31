<?php

namespace App\Http\Controllers\MFN\Reports\Others;
use App\Services\HrService as HRS;
use App\Http\Controllers\Controller;
use App\Model\MFN\MfnDayEnd;
use App\Model\GNL\Branch;
use App\Model\MFN\Samity;
use App\Model\MFN\Member;
use App\Model\MFN\Loan;

use Carbon\Carbon;
use DB;
use App\Model\MFN\LoanAccount;
use App\Model\MFN\LoanProductCategory;
use App\Model\MFN\LoanProduct;

use App\Model\MFN\LoanCollection;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use DateTime;
use Role;
use App\Services\HrService;
use App\Services\MfnService;
use App\Helpers\RoleHelper;

class LoanStatementReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function getLoanStatement(Request $req)
    {
        if ($req->ajax()) {
            $branchId      = (empty($req->input('branchId'))) ? null : $req->input('branchId');
            $samityId      = (empty($req->input('samityId'))) ? null : $req->input('samityId');
            $monthYear     = (empty($req->input('monthYear'))) ? null : $req->input('monthYear');
            $fundingOrg    = (empty($req->input('fundingOrg'))) ? null : $req->input('fundingOrg');

            $firstWeek     = (empty($req->input('firstWeek'))) ? null : (new DateTime(
                              $req->input('firstWeek')))->format('Y-m-d');
            $endfirstWeek  = (empty($req->input('endfirstWeek'))) ? null : (new DateTime(
                                $req->input('endfirstWeek')))->format('Y-m-d');
            $secondWeek    = (empty($req->input('secondWeek'))) ? null : (new DateTime(
                                $req->input('secondWeek')))->format('Y-m-d');
            $endsecondWeek = (empty($req->input('endsecondWeek'))) ? null : (new DateTime(
                                $req->input('endsecondWeek')))->format('Y-m-d');
            $thirdWeek     = (empty($req->input('thirdWeek'))) ? null : (new DateTime(
                                $req->input('thirdWeek')))->format('Y-m-d');
            $endthirdWeek  = (empty($req->input('endthirdWeek'))) ? null : (new DateTime(
                                $req->input('endthirdWeek')))->format('Y-m-d');
            $forthWeek     = (empty($req->input('forthWeek'))) ? null : (new DateTime(
                                $req->input('forthWeek')))->format('Y-m-d');
            $endforthWeek  = (empty($req->input('endforthWeek'))) ? null : (new DateTime(
                                $req->input('endforthWeek')))->format('Y-m-d');
            $fifthWeek     = (empty($req->input('fifthWeek'))) ? null : (new DateTime(
                                $req->input('fifthWeek')))->format('Y-m-d');
            $endFifthWeek  = (empty($req->input('endFifthWeek'))) ? null : (new DateTime(
                                $req->input('endFifthWeek')))->format('Y-m-d');

            $lastWeek      = (empty($req->input('lastWeek'))) ? null : (new DateTime(
                                $req->input('lastWeek')))->format('Y-m-d');
            $endLastWeek   = (empty($req->input('endLastWeek'))) ? null : (new DateTime(
                                $req->input('endLastWeek')))->format('Y-m-d');
            
            if (!empty($samityId)) {

                $loans = DB::table('mfn_samity as ms')
                    ->where([['ms.is_delete', 0],['ms.branchId',$branchId],['ms.id', $samityId]])
                    ->join('mfn_loans as ml', function ($loans) use ($samityId) {
                        $loans->on('ml.samityId', '=', 'ms.id')
                            ->where('ml.is_delete', 0);                                     
                    })
                    ->join('mfn_members as mm', function ($loans) {
                        $loans->on('mm.samityId', '=', 'ms.id')
                            ->where([['mm.is_delete', 0]]);
                    })
                    ->join('mfn_loan_products as lp', function ($loans) {
                        $loans->on('lp.id', '=', 'ml.productId')
                            ->where([['lp.is_delete', 0]]);
                    })
                    ->where(function ($loans) use ($fundingOrg) {
                        if (!empty($fundingOrg)) {
                            $loans->where('lp.fundingOrgId', '=', $fundingOrg);
                        }
                    })
                    ->select('ml.id','mm.gender','ml.loanCompleteDate', 'lp.name as product',
                        'ml.disbursementDate','ml.loanCompleteDate',
                        DB::Raw('IFNULL( ml.repayAmount , 0 ) as ttlDisburseAmount,
                            IFNULL( ml.loanAmount , 0 ) as loanAmount, 
                            IFNULL( ml.repayAmount , 0 ) as repayAmount'))
                    ->get();

                

                // if ( count($loans) > 0) {
                    
                    $products = collect($loans)->groupBy('product');

                    // Initialize Variable of Total Data for each Row 
                    $ttlDisburseFW = 0; $ttlDisburseLastFW= 0; $ttlfullyPaidFW= 0; $ttlOverDueLoanFW= 0;
                                        $ttlcurrentLoanFW= 0;$ttlweeklyCollFW = 0;
                    $ttlDisburseSW= 0; $ttlDisburseLastSW= 0; $ttlfullyPaidSW= 0; $ttlOverDueLoanSW= 0;
                                    $ttlcurrentLoanSW= 0;$ttlweeklyCollSW = 0;
                    $ttlDisburseTW= 0; $ttlDisburseLastTW= 0; $ttlfullyPaidTW= 0; $ttlOverDueLoanTW= 0;
                                    $ttlcurrentLoanTW= 0;$ttlweeklyCollTW = 0;
                    $ttlDisburseFRW= 0; $ttlDisburseLastFRW= 0; $ttlfullyPaidFRW= 0; $ttlOverDueLoanFRW= 0;
                                    $ttlcurrentLoanFRW= 0;$ttlweeklyCollFRW = 0;
                    $ttlDisburseFVW= 0; $ttlDisburseLastFVW= 0; $ttlfullyPaidFVW= 0; $ttlOverDueLoanFVW= 0;
                                    $ttlcurrentLoanFVW= 0;$ttlweeklyCollFVW = 0;


                    foreach ($products as $k => $product) {

                    // ---------------------------------- First Week Data -----------------------------//

                        // Total Disbursement Upto Last Week
                        $products[$k][0]->ttlDisburseFemaleFW = collect($product->where('gender', 'Female')
                                            ->where('disbursementDate','<',$firstWeek))->sum('ttlDisburseAmount');
                        $products[$k][0]->ttlDisburseMaleFW   = collect($product->where('gender', 'Male')
                                            ->where('disbursementDate','<',$firstWeek))->sum('ttlDisburseAmount');
                        $ttlDisburseFW += $products[$k][0]->ttlDisburseFemaleFW + $products[$k][0]->ttlDisburseMaleFW;

                        // Disbursement at last week
                        $products[$k][0]->disburseLastFemaleFW = collect($product->where('gender', 'Female')
                                            ->whereBetween('disbursementDate',[$lastWeek,$endLastWeek]))
                                            ->sum('ttlDisburseAmount');
                        $products[$k][0]->disburseLastMaleFW   = collect($product->where('gender', 'Male')
                                            ->whereBetween('disbursementDate',[$lastWeek,$endLastWeek]))
                                            ->sum('ttlDisburseAmount');
                        $ttlDisburseLastFW += $products[$k][0]->disburseLastFemaleFW + $products[$k][0]->disburseLastMaleFW;

                        // Total Fully Paid Upto Last Week
                        $products[$k][0]->fullyPaidFemaleFW = collect($product->where('gender', 'Female')
                                            ->where('loanCompleteDate','!=', '0000-00-00')
                                            ->where('loanCompleteDate', '<', $firstWeek))->sum('repayAmount');
                        $products[$k][0]->fullyPaidMaleFW   = collect($product->where('gender', 'Male')
                                            ->where('loanCompleteDate','!=', '0000-00-00')
                                            ->where('loanCompleteDate', '<', $firstWeek))->sum('repayAmount');
                        $ttlfullyPaidFW += $products[$k][0]->fullyPaidFemaleFW + $products[$k][0]->fullyPaidMaleFW;

                        // Total Over Due
                        $dueColFemaleIds = ($product->where('gender', 'Female')->pluck('id')->toArray());
                        $dueColMaleIds = ($product->where('gender', 'Male')->pluck('id')->toArray());

                        $overDueLoanFemaleFW = Mfnservice::getLoanStatus($dueColFemaleIds, $lastWeek);
                        $products[$k][0]->overDueLoanFemaleFW = collect($overDueLoanFemaleFW)->sum('dueAmount');
                        $overDueLoanMaleFW = Mfnservice::getLoanStatus($dueColMaleIds, $lastWeek);
                        $products[$k][0]->overDueLoanMaleFW = collect($overDueLoanMaleFW)->sum('dueAmount');
                        $ttlOverDueLoanFW += $products[$k][0]->overDueLoanFemaleFW + $products[$k][0]->overDueLoanMaleFW;

                        // Regular Current Loan
                        $currentLoanFW1Female = ($product->where('gender', 'Female')->where('loanCompleteDate', '0000-00-00')->pluck('id')->toArray());
                        $currentLoanFW2Female = ($product->where('gender', 'Female')->where('loanCompleteDate', '>' , $firstWeek)->pluck('id')->toArray());
                        $currentLoanIdsFWFemale = array_merge($currentLoanFW1Female,$currentLoanFW2Female);
                        $currentLoanFemaleFW = Mfnservice::getLoanStatus($currentLoanIdsFWFemale);
                        $products[$k][0]->currentLoanFemaleFW = collect($currentLoanFemaleFW)->sum('loanAmount');

                        $currentLoanFW1Male = ($product->where('gender', 'Male')->where('loanCompleteDate', '0000-00-00')->pluck('id')->toArray());
                        $currentLoanFW2Male = ($product->where('gender', 'Male')->where('loanCompleteDate', '>' , $firstWeek)->pluck('id')->toArray());
                        $currentLoanIdsFWMale = array_merge($currentLoanFW1Male,$currentLoanFW2Male);
                        $currentLoanMaleFW = Mfnservice::getLoanStatus($currentLoanIdsFWMale);
                        $products[$k][0]->currentLoanMaleFW = collect($currentLoanMaleFW)->sum('loanAmount');

                        $ttlcurrentLoanFW += $products[$k][0]->currentLoanFemaleFW + $products[$k][0]->currentLoanMaleFW;

                        // New Weekly Collectable
                        $weeklyCollFemaleFW = Mfnservice::getLoanStatus($dueColFemaleIds, $firstWeek, $endfirstWeek);
                        $products[$k][0]->weeklyCollFemaleFW = collect($weeklyCollFemaleFW)->sum('onPeriodPayable');
                        $weeklyCollMaleFW = Mfnservice::getLoanStatus($dueColMaleIds, $firstWeek, $endfirstWeek);
                        $products[$k][0]->weeklyCollMaleFW = collect($weeklyCollMaleFW)->sum('onPeriodPayable');
                        $ttlweeklyCollFW += $products[$k][0]->weeklyCollFemaleFW + $products[$k][0]->weeklyCollMaleFW;

                    // ---------------------------------- Second Week Data -----------------------------//

                        // Total Disbursement Upto Last Week
                        $products[$k][0]->ttlDisburseFemaleSW = collect($product->where('gender', 'Female')
                                            ->where('disbursementDate','<',$secondWeek))->sum('ttlDisburseAmount');
                        $products[$k][0]->ttlDisburseMaleSW   = collect($product->where('gender', 'Male')
                                            ->where('disbursementDate','<',$secondWeek))->sum('ttlDisburseAmount');
                        $ttlDisburseSW += $products[$k][0]->ttlDisburseFemaleSW + $products[$k][0]->ttlDisburseMaleSW;

                        // Disbursement at last week
                        $products[$k][0]->disburseLastFemaleSW = collect($product->where('gender', 'Female')
                                            ->whereBetween('disbursementDate',[$firstWeek,$endfirstWeek]))
                                            ->sum('ttlDisburseAmount');
                        $products[$k][0]->disburseLastMaleSW   = collect($product->where('gender', 'Male')
                                            ->whereBetween('disbursementDate',[$firstWeek,$endfirstWeek]))
                                            ->sum('ttlDisburseAmount');
                        $ttlDisburseLastSW += $products[$k][0]->disburseLastFemaleSW + $products[$k][0]->disburseLastMaleSW;


                        // Total Fully Paid Upto Last Week
                        $products[$k][0]->fullyPaidFemaleSW = collect($product->where('gender', 'Female')
                                            ->where('loanCompleteDate','!=', '0000-00-00')
                                            ->where('loanCompleteDate', '<', $secondWeek))->sum('repayAmount');
                        $products[$k][0]->fullyPaidMaleSW   = collect($product->where('gender', 'Male')
                                            ->where('loanCompleteDate','!=', '0000-00-00')
                                            ->where('loanCompleteDate', '<', $secondWeek))->sum('repayAmount');
                        $ttlfullyPaidSW += $products[$k][0]->fullyPaidFemaleSW + $products[$k][0]->fullyPaidMaleSW;

                        // Total Over Due Loan
                        $overDueLoanFemaleSW = Mfnservice::getLoanStatus($dueColFemaleIds, $endfirstWeek);
                        $products[$k][0]->overDueLoanFemaleSW = collect($overDueLoanFemaleSW)->sum('dueAmount');
                        $overDueLoanMaleSW = Mfnservice::getLoanStatus($dueColMaleIds, $endfirstWeek);
                        $products[$k][0]->overDueLoanMaleSW = collect($overDueLoanMaleSW)->sum('dueAmount');
                        $ttlOverDueLoanSW += $products[$k][0]->overDueLoanFemaleSW + $products[$k][0]->overDueLoanMaleSW;

                        // Regular Current Loan
                        $currentLoanSW1Female = ($product->where('gender', 'Female')->where('loanCompleteDate', '0000-00-00')->pluck('id')->toArray());
                        $currentLoanSW2Female = ($product->where('gender', 'Female')->where('loanCompleteDate', '>' , $secondWeek)->pluck('id')->toArray());
                        $currentLoanIdsSWFemale = array_merge($currentLoanSW1Female,$currentLoanSW2Female);
                        $currentLoanFemaleSW = Mfnservice::getLoanStatus($currentLoanIdsSWFemale);
                        $products[$k][0]->currentLoanFemaleSW = collect($currentLoanFemaleSW)->sum('loanAmount');

                        $currentLoanSW1Male = ($product->where('gender', 'Male')->where('loanCompleteDate', '0000-00-00')->pluck('id')->toArray());
                        $currentLoanSW2Male = ($product->where('gender', 'Male')->where('loanCompleteDate', '>' , $secondWeek)->pluck('id')->toArray());
                        $currentLoanIdsSWMale = array_merge($currentLoanSW1Male,$currentLoanSW2Male);
                        $currentLoanMaleSW = Mfnservice::getLoanStatus($currentLoanIdsSWMale);
                        $products[$k][0]->currentLoanMaleSW = collect($currentLoanMaleSW)->sum('loanAmount');

                        $ttlcurrentLoanSW += $products[$k][0]->currentLoanFemaleSW + $products[$k][0]->currentLoanMaleSW;

                        // New Weekly Collectable
                        $weeklyCollFemaleSW = Mfnservice::getLoanStatus($dueColFemaleIds, $secondWeek, $endsecondWeek);
                        $products[$k][0]->weeklyCollFemaleSW = collect($weeklyCollFemaleSW)->sum('onPeriodPayable');
                        $weeklyCollMaleSW = Mfnservice::getLoanStatus($dueColMaleIds, $secondWeek, $endsecondWeek);
                        $products[$k][0]->weeklyCollMaleSW = collect($weeklyCollMaleSW)->sum('onPeriodPayable');
                        $ttlweeklyCollSW += $products[$k][0]->weeklyCollFemaleSW + $products[$k][0]->weeklyCollMaleSW;

                    // ---------------------------------- Third Week Data -----------------------------//

                        // Total Disbursement Upto Last Week
                        $products[$k][0]->ttlDisburseFemaleTW = collect($product->where('gender', 'Female')
                                            ->where('disbursementDate','<',$thirdWeek))->sum('ttlDisburseAmount');
                        $products[$k][0]->ttlDisburseMaleTW   = collect($product->where('gender', 'Male')
                                            ->where('disbursementDate','<',$thirdWeek))->sum('ttlDisburseAmount');
                        $ttlDisburseTW += $products[$k][0]->ttlDisburseFemaleTW + $products[$k][0]->ttlDisburseMaleTW;

                        // Disbursement at last week
                        $products[$k][0]->disburseLastFemaleTW = collect($product->where('gender', 'Female')
                                            ->whereBetween('disbursementDate',[$secondWeek,$endsecondWeek]))
                                            ->sum('ttlDisburseAmount');
                        $products[$k][0]->disburseLastMaleTW   = collect($product->where('gender', 'Male')
                                            ->whereBetween('disbursementDate',[$secondWeek,$endsecondWeek]))
                                            ->sum('ttlDisburseAmount');
                        $ttlDisburseLastTW += $products[$k][0]->disburseLastFemaleTW + $products[$k][0]->disburseLastMaleTW;

                        // Total Fully Paid Upto Last Week
                        $products[$k][0]->fullyPaidFemaleTW = collect($product->where('gender', 'Female')
                                            ->where('loanCompleteDate','!=', '0000-00-00')
                                            ->where('loanCompleteDate', '<', $thirdWeek))->sum('repayAmount');
                        $products[$k][0]->fullyPaidMaleTW   = collect($product->where('gender', 'Male')
                                            ->where('loanCompleteDate','!=', '0000-00-00')
                                            ->where('loanCompleteDate', '<', $thirdWeek))->sum('repayAmount');
                        $ttlfullyPaidTW += $products[$k][0]->fullyPaidFemaleTW + $products[$k][0]->fullyPaidMaleTW;


                        // Total Over Due Loan
                        $overDueLoanFemaleTW = Mfnservice::getLoanStatus($dueColFemaleIds, $endsecondWeek);
                        $products[$k][0]->overDueLoanFemaleTW = collect($overDueLoanFemaleTW)->sum('dueAmount');
                        $overDueLoanMaleTW = Mfnservice::getLoanStatus($dueColMaleIds, $endsecondWeek);
                        $products[$k][0]->overDueLoanMaleTW = collect($overDueLoanMaleTW)->sum('dueAmount');
                        $ttlOverDueLoanTW += $products[$k][0]->overDueLoanFemaleTW + $products[$k][0]->overDueLoanMaleTW;

                        // Regular Current Loan
                        $currentLoanTW1Female = ($product->where('gender', 'Female')->where('loanCompleteDate', '0000-00-00')->pluck('id')->toArray());
                        $currentLoanTW2Female = ($product->where('gender', 'Female')->where('loanCompleteDate', '>' , $thirdWeek)->pluck('id')->toArray());
                        $currentLoanIdsTWFemale = array_merge($currentLoanTW1Female,$currentLoanTW2Female);
                        $currentLoanFemaleTW = Mfnservice::getLoanStatus($currentLoanIdsTWFemale);
                        $products[$k][0]->currentLoanFemaleTW = collect($currentLoanFemaleTW)->sum('loanAmount');

                        $currentLoanTW1Male = ($product->where('gender', 'Male')->where('loanCompleteDate', '0000-00-00')->pluck('id')->toArray());
                        $currentLoanTW2Male = ($product->where('gender', 'Male')->where('loanCompleteDate', '>' , $thirdWeek)->pluck('id')->toArray());
                        $currentLoanIdsTWMale = array_merge($currentLoanTW1Male,$currentLoanTW2Male);
                        $currentLoanMaleTW = Mfnservice::getLoanStatus($currentLoanIdsTWMale);
                        $products[$k][0]->currentLoanMaleTW = collect($currentLoanMaleTW)->sum('loanAmount');

                        $ttlcurrentLoanTW += $products[$k][0]->currentLoanFemaleTW + $products[$k][0]->currentLoanMaleTW;

                        // New Weekly Collectable
                        $weeklyCollFemaleTW = Mfnservice::getLoanStatus($dueColFemaleIds, $thirdWeek, $endthirdWeek);
                        $products[$k][0]->weeklyCollFemaleTW = collect($weeklyCollFemaleTW)->sum('onPeriodPayable');
                        $weeklyCollMaleTW = Mfnservice::getLoanStatus($dueColMaleIds, $thirdWeek, $endthirdWeek);
                        $products[$k][0]->weeklyCollMaleTW = collect($weeklyCollMaleTW)->sum('onPeriodPayable');
                        $ttlweeklyCollTW += $products[$k][0]->weeklyCollFemaleTW + $products[$k][0]->weeklyCollMaleTW;

                    // ---------------------------------- Forth Week Data -----------------------------//

                        // Total Disbursement Upto Last Week
                        $products[$k][0]->ttlDisburseFemaleFRW = collect($product->where('gender', 'Female')
                                            ->where('disbursementDate','<',$forthWeek))->sum('ttlDisburseAmount');
                        $products[$k][0]->ttlDisburseMaleFRW   = collect($product->where('gender', 'Male')
                                            ->where('disbursementDate','<',$forthWeek))->sum('ttlDisburseAmount');
                        $ttlDisburseFRW += $products[$k][0]->ttlDisburseFemaleFRW + $products[$k][0]->ttlDisburseMaleFRW;


                        // Disbursement at last week
                        $products[$k][0]->disburseLastFemaleFRW = collect($product->where('gender', 'Female')
                                            ->whereBetween('disbursementDate',[$thirdWeek,$endthirdWeek]))
                                            ->sum('ttlDisburseAmount');
                        $products[$k][0]->disburseLastMaleFRW   = collect($product->where('gender', 'Male')
                                            ->whereBetween('disbursementDate',[$thirdWeek,$endthirdWeek]))
                                            ->sum('ttlDisburseAmount');
                        $ttlDisburseLastFRW += $products[$k][0]->disburseLastFemaleFRW+$products[$k][0]->disburseLastMaleFRW;


                        // Total Fully Paid Upto Last Week
                        $products[$k][0]->fullyPaidFemaleFRW = collect($product->where('gender', 'Female')
                                            ->where('loanCompleteDate','!=', '0000-00-00')
                                            ->where('loanCompleteDate', '<', $forthWeek))->sum('repayAmount');
                        $products[$k][0]->fullyPaidMaleFRW   = collect($product->where('gender', 'Male')
                                            ->where('loanCompleteDate','!=', '0000-00-00')
                                            ->where('loanCompleteDate', '<', $forthWeek))->sum('repayAmount');
                        $ttlfullyPaidFRW += $products[$k][0]->fullyPaidFemaleFRW + $products[$k][0]->fullyPaidMaleFRW;

                        // Total Over Due Loan
                        $overDueLoanFemaleFRW = Mfnservice::getLoanStatus($dueColFemaleIds, $endthirdWeek);
                        $products[$k][0]->overDueLoanFemaleFRW = collect($overDueLoanFemaleFRW)->sum('dueAmount');
                        $overDueLoanMaleFRW = Mfnservice::getLoanStatus($dueColMaleIds, $endthirdWeek);
                        $products[$k][0]->overDueLoanMaleFRW = collect($overDueLoanMaleFRW)->sum('dueAmount');
                        $ttlOverDueLoanFRW += $products[$k][0]->overDueLoanFemaleFRW + $products[$k][0]->overDueLoanMaleFRW;

                        // Regular Current Loan
                        $currentLoanFRW1Female = ($product->where('gender', 'Female')->where('loanCompleteDate','0000-00-00')->pluck('id')->toArray());
                        $currentLoanFRW2Female = ($product->where('gender', 'Female')->where('loanCompleteDate','>',$forthWeek)->pluck('id')->toArray());
                        $currentLoanIdsFRWFemale = array_merge($currentLoanFRW1Female,$currentLoanFRW2Female);
                        $currentLoanFemaleFRW = Mfnservice::getLoanStatus($currentLoanIdsFRWFemale);
                        $products[$k][0]->currentLoanFemaleFRW = collect($currentLoanFemaleFRW)->sum('loanAmount');

                        $currentLoanFRW1Male = ($product->where('gender', 'Male')->where('loanCompleteDate', '0000-00-00')->pluck('id')->toArray());
                        $currentLoanFRW2Male = ($product->where('gender', 'Male')->where('loanCompleteDate', '>' , $forthWeek)->pluck('id')->toArray());
                        $currentLoanIdsFRWMale = array_merge($currentLoanFRW1Male,$currentLoanFRW2Male);
                        $currentLoanMaleFRW = Mfnservice::getLoanStatus($currentLoanIdsFRWMale);
                        $products[$k][0]->currentLoanMaleFRW = collect($currentLoanMaleFRW)->sum('loanAmount');

                        $ttlcurrentLoanFRW += $products[$k][0]->currentLoanFemaleFRW + $products[$k][0]->currentLoanMaleFRW;

                        // New Weekly Collectable
                        $weeklyCollFemaleFRW = Mfnservice::getLoanStatus($dueColFemaleIds, $forthWeek, $endforthWeek);
                        $products[$k][0]->weeklyCollFemaleFRW = collect($weeklyCollFemaleFRW)->sum('onPeriodPayable');
                        $weeklyCollMaleFRW = Mfnservice::getLoanStatus($dueColMaleIds, $forthWeek, $endforthWeek);
                        $products[$k][0]->weeklyCollMaleFRW = collect($weeklyCollMaleFRW)->sum('onPeriodPayable');
                        $ttlweeklyCollFRW += $products[$k][0]->weeklyCollFemaleFRW + $products[$k][0]->weeklyCollMaleFRW;


                    // ---------------------------------- Fifth Week Data -----------------------------//

                        // Total Disbursement Upto Last Week
                        $products[$k][0]->ttlDisburseFemaleFVW = collect($product->where('gender', 'Female')
                                            ->where('disbursementDate','<',$fifthWeek))->sum('ttlDisburseAmount');
                        $products[$k][0]->ttlDisburseMaleFVW   = collect($product->where('gender', 'Male')
                                            ->where('disbursementDate','<',$fifthWeek))->sum('ttlDisburseAmount');
                        $ttlDisburseFVW += $products[$k][0]->ttlDisburseFemaleFVW + $products[$k][0]->ttlDisburseMaleFVW;

                        // Disbursement at last week
                        $products[$k][0]->disburseLastFemaleFVW = collect($product->where('gender', 'Female')
                                            ->whereBetween('disbursementDate',[$forthWeek,$endforthWeek]))
                                            ->sum('ttlDisburseAmount');
                        $products[$k][0]->disburseLastMaleFVW   = collect($product->where('gender', 'Male')
                                            ->whereBetween('disbursementDate',[$forthWeek,$endforthWeek]))
                                            ->sum('ttlDisburseAmount');
                        $ttlDisburseLastFVW += $products[$k][0]->disburseLastFemaleFVW+$products[$k][0]->disburseLastMaleFVW;

                        // Total Fully Paid Upto Last Week
                        $products[$k][0]->fullyPaidFemaleFVW = collect($product->where('gender', 'Female')
                                            ->where('loanCompleteDate','!=', '0000-00-00')
                                            ->where('loanCompleteDate', '<', $fifthWeek))->sum('repayAmount');
                        $products[$k][0]->fullyPaidMaleFVW   = collect($product->where('gender', 'Male')
                                            ->where('loanCompleteDate','!=', '0000-00-00')
                                            ->where('loanCompleteDate', '<', $fifthWeek))->sum('repayAmount');
                        $ttlfullyPaidFVW += $products[$k][0]->fullyPaidFemaleFVW + $products[$k][0]->fullyPaidMaleFVW;

                        // Total Over Due Loan
                        $overDueLoanFemaleFVW = Mfnservice::getLoanStatus($dueColFemaleIds, $endforthWeek);
                        $products[$k][0]->overDueLoanFemaleFVW = collect($overDueLoanFemaleFVW)->sum('dueAmount');
                        $overDueLoanMaleFVW = Mfnservice::getLoanStatus($dueColMaleIds, $endforthWeek);
                        $products[$k][0]->overDueLoanMaleFVW = collect($overDueLoanMaleFVW)->sum('dueAmount');
                        $ttlOverDueLoanFVW += $products[$k][0]->overDueLoanFemaleFVW + $products[$k][0]->overDueLoanMaleFVW;

                        // Regular Current Loan
                        $currentLoanFVW1Female = ($product->where('gender', 'Female')->where('loanCompleteDate', '0000-00-00')->pluck('id')->toArray());
                        $currentLoanFVW2Female = ($product->where('gender', 'Female')->where('loanCompleteDate', '>' , $fifthWeek)->pluck('id')->toArray());
                        $currentLoanIdsFVWFemale = array_merge($currentLoanFVW1Female,$currentLoanFVW2Female);
                        $currentLoanFemaleFVW = Mfnservice::getLoanStatus($currentLoanIdsFVWFemale);
                        $products[$k][0]->currentLoanFemaleFVW = collect($currentLoanFemaleFVW)->sum('loanAmount');

                        $currentLoanFVW1Male = ($product->where('gender', 'Male')->where('loanCompleteDate', '0000-00-00')->pluck('id')->toArray());
                        $currentLoanFVW2Male = ($product->where('gender', 'Male')->where('loanCompleteDate', '>' , $fifthWeek)->pluck('id')->toArray());
                        $currentLoanIdsFVWMale = array_merge($currentLoanFVW1Male,$currentLoanFVW2Male);
                        $currentLoanMaleFVW = Mfnservice::getLoanStatus($currentLoanIdsFVWMale);
                        $products[$k][0]->currentLoanMaleFVW = collect($currentLoanMaleFVW)->sum('loanAmount');

                        $ttlcurrentLoanFVW += $products[$k][0]->currentLoanFemaleFVW + $products[$k][0]->currentLoanMaleFVW;

                        // New Weekly Collectable
                        $weeklyCollFemaleFVW = Mfnservice::getLoanStatus($dueColFemaleIds, $fifthWeek, $endFifthWeek);
                        $products[$k][0]->weeklyCollFemaleFVW = collect($weeklyCollFemaleFVW)->sum('onPeriodPayable');
                        $weeklyCollMaleFVW = Mfnservice::getLoanStatus($dueColMaleIds, $fifthWeek, $endFifthWeek);
                        $products[$k][0]->weeklyCollMaleFVW = collect($weeklyCollMaleFVW)->sum('onPeriodPayable');
                        $ttlweeklyCollFVW += $products[$k][0]->weeklyCollFemaleFVW + $products[$k][0]->weeklyCollMaleFVW;


                    // ------------- Number Format ---------- //
                        $products[$k][0]->ttlDisburseFemaleFW = number_format($products[$k][0]->ttlDisburseFemaleFW,2);
                        $products[$k][0]->ttlDisburseMaleFW = number_format($products[$k][0]->ttlDisburseMaleFW,2);
                        $products[$k][0]->disburseLastFemaleFW = number_format($products[$k][0]->disburseLastFemaleFW,2);
                        $products[$k][0]->disburseLastMaleFW = number_format($products[$k][0]->disburseLastMaleFW,2);
                        $products[$k][0]->fullyPaidFemaleFW = number_format($products[$k][0]->fullyPaidFemaleFW,2);
                        $products[$k][0]->fullyPaidMaleFW = number_format($products[$k][0]->fullyPaidMaleFW,2);
                        $products[$k][0]->overDueLoanFemaleFW = number_format($products[$k][0]->overDueLoanFemaleFW,2);
                        $products[$k][0]->overDueLoanMaleFW = number_format($products[$k][0]->overDueLoanMaleFW,2);
                        $products[$k][0]->currentLoanFemaleFW = number_format($products[$k][0]->currentLoanFemaleFW,2);
                        $products[$k][0]->currentLoanMaleFW = number_format($products[$k][0]->currentLoanMaleFW,2);
                        $products[$k][0]->weeklyCollFemaleFW = number_format($products[$k][0]->weeklyCollFemaleFW,2);
                        $products[$k][0]->weeklyCollMaleFW = number_format($products[$k][0]->weeklyCollMaleFW,2);


                        $products[$k][0]->ttlDisburseFemaleSW = number_format($products[$k][0]->ttlDisburseFemaleSW,2);
                        $products[$k][0]->ttlDisburseMaleSW = number_format($products[$k][0]->ttlDisburseMaleSW,2);
                        $products[$k][0]->disburseLastFemaleSW = number_format($products[$k][0]->disburseLastFemaleSW,2);
                        $products[$k][0]->disburseLastMaleSW = number_format($products[$k][0]->disburseLastMaleSW,2);
                        $products[$k][0]->fullyPaidFemaleSW = number_format($products[$k][0]->fullyPaidFemaleSW,2);
                        $products[$k][0]->fullyPaidMaleSW = number_format($products[$k][0]->fullyPaidMaleSW,2);
                        $products[$k][0]->overDueLoanFemaleSW = number_format($products[$k][0]->overDueLoanFemaleSW,2);
                        $products[$k][0]->overDueLoanMaleSW = number_format($products[$k][0]->overDueLoanMaleSW,2);
                        $products[$k][0]->currentLoanFemaleSW = number_format($products[$k][0]->currentLoanFemaleSW,2);
                        $products[$k][0]->currentLoanMaleSW = number_format($products[$k][0]->currentLoanMaleSW,2);
                        $products[$k][0]->weeklyCollFemaleSW = number_format($products[$k][0]->weeklyCollFemaleSW,2);
                        $products[$k][0]->weeklyCollMaleSW = number_format($products[$k][0]->weeklyCollMaleSW,2);


                        $products[$k][0]->ttlDisburseFemaleTW = number_format($products[$k][0]->ttlDisburseFemaleTW,2);
                        $products[$k][0]->ttlDisburseMaleTW = number_format($products[$k][0]->ttlDisburseMaleTW,2);
                        $products[$k][0]->disburseLastFemaleTW = number_format($products[$k][0]->disburseLastFemaleTW,2);
                        $products[$k][0]->disburseLastMaleTW = number_format($products[$k][0]->disburseLastMaleTW,2);
                        $products[$k][0]->fullyPaidFemaleTW = number_format($products[$k][0]->fullyPaidFemaleTW,2);
                        $products[$k][0]->fullyPaidMaleTW = number_format($products[$k][0]->fullyPaidMaleTW,2);
                        $products[$k][0]->overDueLoanFemaleTW = number_format($products[$k][0]->overDueLoanFemaleTW,2);
                        $products[$k][0]->overDueLoanMaleTW = number_format($products[$k][0]->overDueLoanMaleTW,2);
                        $products[$k][0]->currentLoanFemaleTW = number_format($products[$k][0]->currentLoanFemaleTW,2);
                        $products[$k][0]->currentLoanMaleTW = number_format($products[$k][0]->currentLoanMaleTW,2);
                        $products[$k][0]->weeklyCollFemaleTW = number_format($products[$k][0]->weeklyCollFemaleTW,2);
                        $products[$k][0]->weeklyCollMaleTW = number_format($products[$k][0]->weeklyCollMaleTW,2);


                        $products[$k][0]->ttlDisburseFemaleFRW = number_format($products[$k][0]->ttlDisburseFemaleFRW,2);
                        $products[$k][0]->ttlDisburseMaleFRW = number_format($products[$k][0]->ttlDisburseMaleFRW,2);
                        $products[$k][0]->disburseLastFemaleFRW = number_format($products[$k][0]->disburseLastFemaleFRW,2);
                        $products[$k][0]->disburseLastMaleFRW = number_format($products[$k][0]->disburseLastMaleFRW,2);
                        $products[$k][0]->fullyPaidFemaleFRW = number_format($products[$k][0]->fullyPaidFemaleFRW,2);
                        $products[$k][0]->fullyPaidMaleFRW = number_format($products[$k][0]->fullyPaidMaleFRW,2);
                        $products[$k][0]->overDueLoanFemaleFRW = number_format($products[$k][0]->overDueLoanFemaleFRW,2);
                        $products[$k][0]->overDueLoanMaleFRW = number_format($products[$k][0]->overDueLoanMaleFRW,2);
                        $products[$k][0]->currentLoanFemaleFRW = number_format($products[$k][0]->currentLoanFemaleFRW,2);
                        $products[$k][0]->currentLoanMaleFRW = number_format($products[$k][0]->currentLoanMaleFRW,2);
                        $products[$k][0]->weeklyCollFemaleFRW = number_format($products[$k][0]->weeklyCollFemaleFRW,2);
                        $products[$k][0]->weeklyCollMaleFRW = number_format($products[$k][0]->weeklyCollMaleFRW,2);


                        $products[$k][0]->ttlDisburseFemaleFVW = number_format($products[$k][0]->ttlDisburseFemaleFVW,2);
                        $products[$k][0]->ttlDisburseMaleFVW = number_format($products[$k][0]->ttlDisburseMaleFVW,2);
                        $products[$k][0]->disburseLastFemaleFVW = number_format($products[$k][0]->disburseLastFemaleFVW,2);
                        $products[$k][0]->disburseLastMaleFVW = number_format($products[$k][0]->disburseLastMaleFVW,2);
                        $products[$k][0]->fullyPaidFemaleFVW = number_format($products[$k][0]->fullyPaidFemaleFVW,2);
                        $products[$k][0]->fullyPaidMaleFVW = number_format($products[$k][0]->fullyPaidMaleFVW,2);
                        $products[$k][0]->overDueLoanFemaleFVW = number_format($products[$k][0]->overDueLoanFemaleFVW,2);
                        $products[$k][0]->overDueLoanMaleFVW = number_format($products[$k][0]->overDueLoanMaleFVW,2);
                        $products[$k][0]->currentLoanFemaleFVW = number_format($products[$k][0]->currentLoanFemaleFVW,2);
                        $products[$k][0]->currentLoanMaleFVW = number_format($products[$k][0]->currentLoanMaleFVW,2);
                        $products[$k][0]->weeklyCollFemaleFVW = number_format($products[$k][0]->weeklyCollFemaleFVW,2);
                        $products[$k][0]->weeklyCollMaleFVW = number_format($products[$k][0]->weeklyCollMaleFVW,2);

                    }
                // }

                $json_data = array(
                    "draw" => intval($req->input('draw')),
                    "data" => $products,

                    "ttlDisburseFW" => number_format($ttlDisburseFW, 2),
                    "ttlDisburseLastFW" => number_format($ttlDisburseLastFW, 2),
                    "ttlfullyPaidFW" => number_format($ttlfullyPaidFW, 2),
                    "ttlOverDueLoanFW" => number_format($ttlOverDueLoanFW, 2),
                    "ttlcurrentLoanFW" => number_format($ttlcurrentLoanFW, 2),
                    "ttlweeklyCollFW" => number_format($ttlDisburseLastFW, 2),

                    "ttlDisburseSW" => number_format($ttlDisburseSW, 2),
                    "ttlDisburseLastSW" => number_format($ttlDisburseLastSW, 2),
                    "ttlfullyPaidSW" => number_format($ttlfullyPaidSW, 2),
                    "ttlOverDueLoanSW" => number_format($ttlOverDueLoanSW, 2),
                    "ttlcurrentLoanSW" => number_format($ttlcurrentLoanSW, 2),
                    "ttlweeklyCollSW" => number_format($ttlDisburseLastSW, 2),

                    "ttlDisburseTW" => number_format($ttlDisburseTW, 2),
                    "ttlDisburseLastTW" => number_format($ttlDisburseLastTW, 2),
                    "ttlfullyPaidTW" => number_format($ttlfullyPaidTW, 2),
                    "ttlOverDueLoanTW" => number_format($ttlOverDueLoanTW, 2),
                    "ttlcurrentLoanTW" => number_format($ttlcurrentLoanTW, 2),
                    "ttlweeklyCollTW" => number_format($ttlDisburseLastTW, 2),

                    "ttlDisburseFRW" => number_format($ttlDisburseFRW, 2),
                    "ttlDisburseLastFRW" => number_format($ttlDisburseLastFRW, 2),
                    "ttlfullyPaidFRW" => number_format($ttlfullyPaidFRW, 2),
                    "ttlOverDueLoanFRW" => number_format($ttlOverDueLoanFRW, 2),
                    "ttlcurrentLoanFRW" => number_format($ttlcurrentLoanFRW, 2),
                    "ttlweeklyCollFRW" => number_format($ttlDisburseLastFRW, 2),

                    "ttlDisburseFVW" => number_format($ttlDisburseFVW, 2),
                    "ttlDisburseLastFVW" => number_format($ttlDisburseLastFVW, 2),
                    "ttlfullyPaidFVW" => number_format($ttlfullyPaidFVW, 2),
                    "ttlOverDueLoanFVW" => number_format($ttlOverDueLoanFVW, 2),
                    "ttlcurrentLoanFVW" => number_format($ttlcurrentLoanFVW, 2),
                    "ttlweeklyCollFVW" => number_format($ttlDisburseLastFVW, 2),
                );
                
                echo json_encode($json_data);
            }

        }
        else {
            $branchId = ($req->branch_id) ? $req->branch_id : Auth::user()->branch_id;

            $branchData = Branch::where([['is_delete', 0], ['is_active', 1], ['is_approve', 1]])
                          ->select('id','branch_name','branch_code')->orderBy('branch_code')->get();

            $samityData = DB::table('mfn_samity')
                            ->where([['is_delete', 0],['branchId',$branchId]])
                            ->select('id','name')
                            ->get();
            $fundingOrgData = DB::table('mfn_funding_orgs')
                            ->where([['is_delete', 0]])
                            ->select('id','name')
                            ->get();

            $data = array(
                "branchData"      => $branchData,
                "samityData"      => $samityData,
                "fundingOrgData"  => $fundingOrgData
            );
            return view('MFN.Reports.Others.get_loan_statement',$data);
        }
    }
   

}
