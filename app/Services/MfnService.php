<?php

namespace App\Services;

use App\Jobs\SendMailJob;
use App\Services\AccService;
use App\Services\HrService;
use Carbon\Carbon;
use DateTime;
use DB;
use Illuminate\Support\Facades\Auth;

class MfnService
{
    // this variables are used to generate loan schedule
    public static $regularLoanConfig  = null;
    public static $holidays           = [];
    public static $samity             = null;
    public static $samityDayChanges   = null;
    public static $requirement        = 'installments';
    public static $loanStatusFromDate = null;
    public static $loanStatusToDate   = null;
    public static $loanReschedules    = null;
    public static $rescheduledLoanIds = [];
    public static $exceptRescheduleId = null;

    public static function resetProperties()
    {
        self::$regularLoanConfig  = null;
        self::$holidays           = [];
        self::$samity             = null;
        self::$samityDayChanges   = null;
        self::$requirement        = 'installments';
        self::$loanStatusFromDate = null;
        self::$loanStatusToDate   = null;
        self::$loanReschedules    = null;
        self::$rescheduledLoanIds = [];
        self::$exceptRescheduleId = null;
    }

    public static function systemCurrentDate($branchId)
    {
        $sysDate = DB::table('mfn_day_end')
            ->where([
                ['branchId', $branchId],
                ['isActive', 1],
            ])
            ->max('date');

        if ($sysDate == null) {
            $sysDate = DB::table('gnl_branchs')
                ->where('id', $branchId)
                ->first()
                ->mfn_start_date;
        }

        return $sysDate;
    }

    public static function getFieldOfficers($branchId)
    {
        $fieldOfficersDesignationIds = json_decode(DB::table('mfn_config')->where('title', 'fieldOfficerHrDesignationIds')->first()->content);

        $filedOfficers = DB::table('hr_employees')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
                ['branch_id', $branchId],
            ])
            ->whereIn('designation_Id', $fieldOfficersDesignationIds)
            ->select(DB::raw("CONCAT(emp_code, ' - ', emp_name) AS name, id"))
            ->get();

        return $filedOfficers;
    }

    public static function getSamities($branchIdOrIds)
    {
        if (is_numeric($branchIdOrIds)) {
            $sysDate = self::systemCurrentDate($branchIdOrIds);

            $samities = DB::table('mfn_samity')
                ->where([
                    ['is_delete', 0],
                    ['branchId', $branchIdOrIds],
                    ['openingDate', '<=', $sysDate],
                    ['closingDate', null],
                ])
                ->orderBy('samityCode')
                ->select(DB::raw("CONCAT(samityCode, ' - ', name) AS name, id"))
                ->get();
        } else {
            $samities = DB::table('mfn_samity')
                ->where([
                    ['is_delete', 0],
                    ['closingDate', null],
                ])
                ->orderBy('samityCode')
                ->whereIn('branchId', $branchIdOrIds)
                ->select(DB::raw("CONCAT(samityCode, ' - ', name) AS name, id"))
                ->get();
        }

        return $samities;
    }

    public static function getWorkingWeekDays()
    {
        $weekDays = array(
            'Saturday'  => 'Saturday',
            'Sunday'    => 'Sunday',
            'Monday'    => 'Monday',
            'Tuesday'   => 'Tuesday',
            'Wednesday' => 'Wednesday',
            'Thursday'  => 'Thursday',
            'Friday'    => 'Friday',
        );
        // get weekly holidays
        $weekEnds = DB::table('hr_holidays_comp')
            ->where([
                ['ch_title', 'Weekend'],
                ['is_delete', 0],
                ['is_active', 1],
            ])
            ->value('ch_day');

        $weekEnds = explode(',', $weekEnds);
        $weekDays = array_diff($weekDays, $weekEnds);

        return $weekDays;
    }

    public static function getSavingsRegularProductInterestRate($productId, $date)
    {
        $interestRate = DB::table('mfn_savings_product_interest_rates')
            ->where([
                ['is_delete', 0],
                ['productId', $productId],
                ['effectiveDate', '<=', $date],
            ])
            ->where(function ($query) use ($date) {
                $query->where('validTill', '>=', $date)
                    ->orWhere('validTill', '0000-00-00');
            })
            ->value('interestRate');

        return $interestRate;
    }

    /**
     * get the interest rates of savings one time product
     * depending on the durations
     *
     * @return  array              [index = month, value = interest rate]
     */
    public static function getSavingsOnetimeProductInterestRates($productId, $date)
    {
        $interestRates = DB::table('mfn_savings_product_interest_rates')
            ->where([
                ['is_delete', 0],
                ['productId', $productId],
                ['parentId', 0],
                ['effectiveDate', '<=', $date],
            ])
            ->where(function ($query) use ($date) {
                $query->where('validTill', '>=', $date)
                    ->orWhere('validTill', '0000-00-00');
            })
            ->orderBy('durationMonth')
            ->pluck('interestRate', 'durationMonth')
            ->toArray();

        return $interestRates;
    }

    public static function getSavingsAccounts($filters = [])
    {
        $savAccs = DB::table('mfn_savings_accounts')->where('is_delete', 0);

        if (isset($filters['branchIds'])) {
            $savAccs->whereIn('branchId', $filters['branchIds']);
        }
        if (isset($filters['branchId'])) {
            $savAccs->where('branchId', $filters['branchId']);
        }
        if (isset($filters['samityId'])) {
            $savAccs->where('samityId', $filters['samityId']);
        }
        if (isset($filters['memberId'])) {
            $savAccs->where('memberId', $filters['memberId']);
        }
        if (isset($filters['openingDateFrom'])) {
            $savAccs->where('openingDate', '>=', $filters['openingDateFrom']);
        }
        if (isset($filters['openingDateTo'])) {
            $savAccs->where('openingDate', '<=', $filters['openingDateTo']);
        }
        if (isset($filters['onlyActiveAccounts'])) {
            if ($filters['onlyActiveAccounts'] == 'yes') {
                $savAccs->where('closingDate', '0000-00-00');
            }
        }
        if (isset($filters['accountType'])) {
            if ($filters['accountType'] == 'regular') {
                $productIds = DB::table('mfn_savings_product')->where('productTypeId', 1)->pluck('id')->all();
            } elseif ($filters['accountType'] == 'onetime') {
                $productIds = DB::table('mfn_savings_product')->where('productTypeId', 2)->pluck('id')->all();
            }
            $savAccs->whereIn('savingsProductId', $productIds);
        }

        $savAccs = $savAccs->get();

        return $savAccs;
    }

    public static function getSavingsBalance($filers = [])
    {
        $deposit  = DB::table('mfn_savings_deposit')->where('is_delete', 0)->whereNotIn('transactionTypeId', [8,9]);
        $withdraw = DB::table('mfn_savings_withdraw')->where('is_delete', 0)->whereNotIn('transactionTypeId', [8,9]);

        if (isset($filers['branchId'])) {
            $deposit->where('branchId', $filers['branchId']);
            $withdraw->where('branchId', $filers['branchId']);
        }
        if (isset($filers['samityId'])) {
            $deposit->where('samityId', $filers['samityId']);
            $withdraw->where('samityId', $filers['samityId']);
        }
        if (isset($filers['memberId'])) {
            $deposit->where('memberId', $filers['memberId']);
            $withdraw->where('memberId', $filers['memberId']);
        }
        if (isset($filers['memberIds'])) {
            $deposit->whereIn('memberId', $filers['memberIds']);
            $withdraw->whereIn('memberId', $filers['memberIds']);
        }
        if (isset($filers['accountId'])) {
            $deposit->where('accountId', $filers['accountId']);
            $withdraw->where('accountId', $filers['accountId']);
        }
        if (isset($filers['accountIds'])) {
            $deposit->whereIn('accountId', $filers['accountIds']);
            $withdraw->whereIn('accountId', $filers['accountIds']);
        }
        if (isset($filers['dateTo'])) {
            $deposit->where('date', '<=', $filers['dateTo']);
            $withdraw->where('date', '<=', $filers['dateTo']);
        }

        if (isset($filers['individual'])) {
            if ($filers['individual'] === true) {
                $deposits = $deposit->groupBy('accountId')->select(DB::raw('accountId, SUM(amount) AS amount'))
                    ->get();
                $withdraws = $withdraw->groupBy('accountId')->select(DB::raw('accountId, SUM(amount) AS amount'))
                    ->get();

                $accountBalances = array();

                foreach ($deposits as $deposit) {
                    $accountBalance['accountId'] = $deposit->accountId;
                    $accountBalance['balance']   = $deposits->where('accountId', $deposit->accountId)->sum('amount') - $withdraws->where('accountId', $deposit->accountId)->sum('amount');
                    array_push($accountBalances, $accountBalance);
                }

                return $accountBalances;
            }
        }

        $balance = $deposit->sum('amount') - $withdraw->sum('amount');

        if (isset($filers['neglectAmount'])) {
            $balance -= $filers['neglectAmount'];
        }

        return $balance;
    }

    public static function getSavingsWithdraw($filers = [])
    {
        $withdraw = DB::table('mfn_savings_withdraw')->where('is_delete', 0)->whereIn('transactionTypeId', [1, 2, 4, 6, 7]);

        if (isset($filers['branchId'])) {
            $withdraw->where('branchId', $filers['branchId']);
        }
        if (isset($filers['samityId'])) {
            $withdraw->where('samityId', $filers['samityId']);
        }
        if (isset($filers['memberId'])) {
            $withdraw->where('memberId', $filers['memberId']);
        }
        if (isset($filers['accountId'])) {
            $withdraw->where('accountId', $filers['accountId']);
        }
        if (isset($filers['dateTo'])) {
            $withdraw->where('date', '<=', $filers['dateTo']);
        }

        $balance = $withdraw->sum('amount');

        if (isset($filers['neglectAmount'])) {
            $balance -= $filers['neglectAmount'];
        }
        return $balance;
    }
    public static function getSavingsDeposit($filers = [])
    {
        $deposit = DB::table('mfn_savings_deposit')->where('is_delete', 0)->whereIn('transactionTypeId', [1, 2, 4, 6, 7]);
        // $withdraw = DB::table('mfn_savings_withdraw')->where('is_delete', 0);

        if (isset($filers['branchId'])) {
            $deposit->where('branchId', $filers['branchId']);
        }
        if (isset($filers['samityId'])) {
            $deposit->where('samityId', $filers['samityId']);
        }
        if (isset($filers['memberId'])) {
            $deposit->where('memberId', $filers['memberId']);
        }
        if (isset($filers['accountId'])) {
            $deposit->where('accountId', $filers['accountId']);
        }
        if (isset($filers['dateTo'])) {
            $deposit->where('date', '<=', $filers['dateTo']);
        }

        $balance = $deposit->sum('amount');

        if (isset($filers['neglectAmount'])) {
            $balance -= $filers['neglectAmount'];
        }
        return $balance;
    }
    public static function getSavingsInterest($filers = [])
    {
        $deposit = DB::table('mfn_savings_deposit')->where('is_delete', 0)->whereIn('transactionTypeId', [3, 5]);
        // $withdraw = DB::table('mfn_savings_withdraw')->where('is_delete', 0);

        if (isset($filers['branchId'])) {
            $deposit->where('branchId', $filers['branchId']);
        }
        if (isset($filers['samityId'])) {
            $deposit->where('samityId', $filers['samityId']);
        }
        if (isset($filers['memberId'])) {
            $deposit->where('memberId', $filers['memberId']);
        }
        if (isset($filers['accountId'])) {
            $deposit->where('accountId', $filers['accountId']);
        }
        if (isset($filers['dateTo'])) {
            $deposit->where('date', '<=', $filers['dateTo']);
        }

        $balance = $deposit->sum('amount');

        if (isset($filers['neglectAmount'])) {
            $balance -= $filers['neglectAmount'];
        }
        return $balance;
    }
    public static function getLoanCollection($filers = [])
    {
        $collection = DB::table('mfn_loan_collections')->where('is_delete', 0);
        // $withdraw = DB::table('mfn_savings_withdraw')->where('is_delete', 0);

        if (isset($filers['branchId'])) {
            $collection->where('branchId', $filers['branchId']);
            // $withdraw->where('branchId', $filers['branchId']);
        }
        if (isset($filers['samityId'])) {
            $collection->where('samityId', $filers['samityId']);
            // $withdraw->where('samityId', $filers['samityId']);
        }
        if (isset($filers['memberId'])) {
            $collection->where('memberId', $filers['memberId']);
            // $withdraw->where('memberId', $filers['memberId']);
        }
        if (isset($filers['loanId'])) {
            $collection->where('loanId', $filers['loanId']);
            // $withdraw->where('loanId', $filers['loanId']);
        }
        if (isset($filers['dateTo'])) {
            $collection->where('collectionDate', '<=', $filers['dateTo']);
            // $withdraw->where('date', '<=', $filers['dateTo']);
        }
        // print_r($collection."---");

        $balance = $collection->sum('amount');

        return $balance;
    }

    public static function isSamityDay($samityId, $date)
    {
        if ($date != null) {
            $date = date('Y-m-d', strtotime($date));
        }

        $isSamityDay     = false;
        $samityDayChange = DB::table('mfn_samity_day_changes')
            ->where([
                ['is_delete', 0],
                ['samityId', $samityId],
                ['effectiveDate', '>=', $date],
            ])
            ->orderBy('effectiveDate')
            ->limit(1)
            ->first();

        if ($samityDayChange != null) {
            $samityDay = $samityDayChange->newSamityDay;
        } else {
            $samityDay = DB::table('mfn_samity')->where('id', $samityId)->first()->samityDay;
        }

        if ($samityDay == date('l', strtotime($date))) {
            $isSamityDay = true;
        }

        return $isSamityDay;
    }

    /**
     *  determine is it from opening or not
     *  if the system date is equal to software start date
     *  and branch opening date is less than this date, than it is from opening
     *
     * @param   [int]  $branchId
     *
     * @return  [boolean]
     */
    public static function isOpening($branchId)
    {
        $sysDate = self::systemCurrentDate($branchId);
        $branch  = DB::table('gnl_branchs')->where('id', $branchId)->first();
        if ($branch->branch_opening_date < $branch->mfn_start_date && $sysDate <= $branch->mfn_start_date) {
            $isOpening = true;
        } else {
            $isOpening = false;
        }

        return $isOpening;
    }

    public function getActiveMembers($branchId, $samityId = null)
    {
        // $activeMembers = DB::table('mfn_members')
    }

    public static function getSelectizeMembers($filters = [])
    {
        $members = DB::table('mfn_members AS m')
            ->leftJoin('gnl_branchs as b', 'b.id', 'm.branchId')
            ->leftJoin('mfn_samity as s', 's.id', 'm.samityId')
            ->where([
                ['m.is_delete', 0],
                ['m.closingDate', '0000-00-00'],
            ])
            ->orderBy('m.memberCode')
            ->select(DB::raw("m.id, m.name, m.memberCode, CONCAT(m.name,' - ',m.memberCode) as member, CONCAT(b.branch_name,' - ',b.branch_code) as branch, CONCAT(s.name,' - ',s.samityCode) as samity, s.workingAreaId"));

        if (isset($filters['branchId'])) {
            $members->where('m.branchId', $filters['branchId']);
        }

        if (isset($filters['samityId'])) {
            $members->where('m.samityId', $filters['samityId']);
        }

        if (isset($filters['dateTo'])) {
            $members->where('m.admissionDate', '<=', $filters['dateTo']);
        }

        $members = $members->get();

        foreach ($members as $key => $member) {
            $worKingArea = DB::table('mfn_working_areas')
                ->where('id', $member->workingAreaId)
                ->value('name');

            $members[$key]->workingArea = $worKingArea;
        }

        return $members;
    }

    public static function getFirstRepayDate($samityId, $loanProductId, $disbursementDate, $repaymentFrequencyId = null, $periodMonth = null)
    {
        $samity            = DB::table('mfn_samity')->where('id', $samityId)->first();
        $product           = DB::table('mfn_loan_products')->where('id', $loanProductId)->first();
        $gracePeriodInDays = 0;

        if ($product->productTypeId == 1) { // if it is regular

            $repaymentInfo = json_decode($product->repaymentInfo);
            $repaymentInfo = collect($repaymentInfo);

            $repayment = $repaymentInfo->where('repaymentFrequencyId', $repaymentFrequencyId)->first();

            if ($repayment == null) {
                return null;
            }
            $gracePeriodInDays = $repayment->gracePeriod;

            $targetDate = Carbon::parse($disbursementDate)->addDays($gracePeriodInDays);
        }
        if ($product->productTypeId == 2) { // if it is one time loan

            if ($periodMonth == null) {
                return null;
            }

            $targetDate = Carbon::parse($disbursementDate)->addMonthsNoOverflow($periodMonth);
        }

        $firstRepayDate = self::getSamityDateOfWeek($samityId, $targetDate->format('Y-m-d'));

        while (Carbon::parse($firstRepayDate)->lt($targetDate)) {
            $firstRepayDate = self::getSamityDateOfWeek($samityId, Carbon::parse($firstRepayDate)->addDays(7)->format('Y-m-d'));
        }

        return $firstRepayDate;
    }

    /**
     * This function returns samity date of the particular week serached by date
     * Here week starts at Saturday and ends with Friday
     *
     * @param   [date]  $date
     * @return  [date]         [return Samity Date of a week]
     */
    public static function getSamityDateOfWeek($samity, $date)
    {
        // week start date is SATURDAY
        if (date('D', strtotime($date)) == 'Sat') {
            $startOfWeek = strtotime($date);
        } else {
            $startOfWeek = strtotime("last Saturday", strtotime($date));
        }

        if (is_numeric($samity)) {
            $samity = DB::table('mfn_samity')->where('id', $samity)->first();
        }

        if (self::$samityDayChanges !== null) {
            $samityDayChange = self::$samityDayChanges
                ->where('effectiveDate', '>', $date)
                ->sortBy('effectiveDate')
                ->first();
        } else {
            $samityDayChange = DB::table('mfn_samity_day_changes')
                ->where('samityId', $samity->id)
                ->where('effectiveDate', '>', $date)
                ->orderBy('effectiveDate')
                ->limit(1)
                ->select('oldSamityDay')
                ->first();
        }

        if ($samityDayChange != null) {
            $samityDay = $samityDayChange->oldSamityDay;
        } else {
            $samityDay = $samity->samityDay;
        }

        if (date('l', strtotime($date)) == $samityDay) {
            $samityDate = date('Y-m-d', strtotime($date));
        } else {
            $samityDate = date('Y-m-d', strtotime('next ' . $samityDay, $startOfWeek));
        }

        return $samityDate;
    }

    public static function getSamityFieldOfficerEmpId($samityIdOrIds, $date)
    {
        $date = date('Y-m-d', strtotime($date));

        if (is_array($samityIdOrIds)) {
            $samityIds = $samityIdOrIds;
        } else {
            $samityIds = [$samityIdOrIds];
        }

        $samities = DB::table('mfn_samity')
            ->whereIn('id', $samityIds)
            ->select('id', 'fieldOfficerEmpId')
            ->get();

        $fieldOfficerChanges = DB::table('mfn_samity_field_officer_change')
            ->where([
                ['is_delete', 0],
                ['effectiveDate', '<=', $date],
            ])
            ->whereIn('samityId', $samityIds)
            ->select('samityId', 'newFieldOfficerEmpId', 'effectiveDate')
            ->get();

        foreach ($samities as $key => $samity) {
            if (count($fieldOfficerChanges->where('samityId', $samity->id)) > 0) {
                $maxDate = $fieldOfficerChanges->where('samityId', $samity->id)->max('effectiveDate');
                $samities[$key]->fieldOfficerEmpId = $fieldOfficerChanges->where('samityId', $samity->id)->where('effectiveDate', $maxDate)->first()->newFieldOfficerEmpId;
            }
        }

        if (is_array($samityIdOrIds)) {
            return $samities->pluck('fieldOfficerEmpId', 'id')->toArray();
        }

        return $samities->first()->fieldOfficerEmpId;
    }

    /**
     * You can pass loan id as int or loanids as array
     * if you pass a single date, you will receive loan status on that date
     * if you pass two dates, then you will get loan status on second date and other statuses from first date to second date
     *
     * @param [int/array] $loanIdOrIds
     * @param [date] ...$dates
     * @return array
     */
    public static function getLoanStatus($loanIdOrIds, ...$dates)
    {
        self::$requirement = 'status';

        if (isset($dates[1])) {
            self::$loanStatusToDate   = date('Y-m-d', strtotime($dates[1]));
            self::$loanStatusFromDate = date('Y-m-d', strtotime($dates[0]));
        } elseif (isset($dates[0])) {
            self::$loanStatusToDate = date('Y-m-d', strtotime($dates[0]));
        }

        if (self::$loanStatusFromDate > self::$loanStatusToDate) {
            // print_r('return');
            return 'From date should be appear first';
        }

        if (is_numeric($loanIdOrIds)) {
            $loanIdOrIds = [$loanIdOrIds];
        }

        $loans = DB::table('mfn_loans')
            ->whereIn('id', $loanIdOrIds)
            ->select('id', 'loanAmount', 'repayAmount', 'samityId', 'productId')
            ->get();

        $loans = $loans->keyBy('id');

        $loanCollections = DB::table('mfn_loan_collections')
            ->whereIn('loanId', $loanIdOrIds)
            ->where('is_delete', 0)
            ->groupby('loanId')
            ->groupby('paymentType')
            ->select(DB::raw("loanId, paymentType, SUM(amount) AS amount, SUM(principalAmount) AS principalAmount, SUM(interestAmount) AS interestAmount"));

        if (self::$loanStatusToDate != null) {
            $loanCollections->where('collectionDate', '<=', self::$loanStatusToDate);
        }

        if (self::$loanStatusFromDate != null) {
            $loanCollectionOnPeriod = clone $loanCollections;
            $loanCollectionOnPeriod->where('collectionDate', '>=', self::$loanStatusFromDate);
            $loanCollectionOnPeriod = $loanCollectionOnPeriod->get();
        }

        $loanCollections = $loanCollections->get();

        $loanStatuses = self::generateLoanSchedule($loanIdOrIds, ...$dates);

        // if self::$loanStatusFromDate != null then you are trying to get loan status
        // between two dates. Here we will get payable amount till $loanStatusToDate date
        // and payable amount between two dates, also we will get paid amount till $loanStatusToDate
        // date and between two dates


        foreach ($loanStatuses as $key => $loanStatus) {
            $loanCollection = $loanCollections->where('loanId', $loanStatus['loanId']);
            $paidAmount          = $loanCollection->sum('amount');
            $paidAmountPrincipal = $loanCollection->sum('principalAmount');
            $paidAmountInterest = $loanCollection->sum('interestAmount');

            $dueAmount              = $loanStatus['payableAmount'] - $paidAmount;
            $dueAmountPrincipal     = round($loanStatus['payableAmountPrincipal'] - $paidAmountPrincipal, 5);
            $advanceAmount          = $dueAmount == 0 ? 0 : -$dueAmount;
            $advanceAmountPrincipal = $dueAmountPrincipal == 0 ? 0 : -$dueAmountPrincipal;

            $dueAmount              = $dueAmount < 0 ? 0 : $dueAmount;
            $dueAmountPrincipal     = $dueAmountPrincipal <= 0 ? 0 : $dueAmountPrincipal;
            $advanceAmount          = $advanceAmount < 0 ? 0 : $advanceAmount;
            $advanceAmountPrincipal = $advanceAmountPrincipal < 0 ? 0 : $advanceAmountPrincipal;

            // Assign values to main object
            $loanStatuses[$key]['samityId']                 = $loans[$loanStatus['loanId']]->samityId;
            $loanStatuses[$key]['productId']                = $loans[$loanStatus['loanId']]->productId;

            $loanStatuses[$key]['paidAmount']               = $paidAmount;
            $loanStatuses[$key]['paidAmountPrincipal']      = $paidAmountPrincipal;
            $loanStatuses[$key]['paidAmountInterest']       = $paidAmountInterest;
            $loanStatuses[$key]['rebateAmount']             = $loanCollection->where('paymentType', 'Rebate')->sum('amount');
            $loanStatuses[$key]['dueAmount']                = $dueAmount;
            $loanStatuses[$key]['dueAmountPrincipal']       = $dueAmountPrincipal;
            $loanStatuses[$key]['advanceAmount']            = $advanceAmount;
            $loanStatuses[$key]['advanceAmountPrincipal']   = $advanceAmountPrincipal;

            // $loanStatuses[$key]['outstanding']            = $loans->where('id', $loanStatus['loanId'])->sum('repayAmount') - $paidAmount;
            // $loanStatuses[$key]['outstandingPrincipal']   = $loans->where('id', $loanStatus['loanId'])->sum('loanAmount') - $paidAmountPrincipal;

            $loanStatuses[$key]['outstanding']              = $loans[$loanStatus['loanId']]->repayAmount - $paidAmount;
            $loanStatuses[$key]['outstandingPrincipal']     = $loans[$loanStatus['loanId']]->loanAmount - $paidAmountPrincipal;

            // now calculate on period data i.e. between two dates
            if (self::$loanStatusFromDate != null) {
                // to know about on period status, we need to know status before start date
                $beginningPayable          = $loanStatus['payableAmount'] - $loanStatus['periodPayableAmount'];
                $beginningPayablePrincipal = $loanStatus['payableAmountPrincipal'] - $loanStatus['periodPayableAmountPrincipal'];

                $onPeriodPaidAmount           = $loanCollectionOnPeriod->where('loanId', $loanStatus['loanId'])->sum('amount');
                
                $onPeriodPaidAmountPrincipal  = $loanCollectionOnPeriod->where('loanId', $loanStatus['loanId'])->sum('principalAmount');
                $beginningPaidAmount          = $paidAmount - $onPeriodPaidAmount;
                $beginningPaidAmountPrincipal = $paidAmountPrincipal - $onPeriodPaidAmountPrincipal;

                $beginigAdvanceAmount          = $beginningPaidAmount - $beginningPayable;
                $beginigAdvanceAmountPrincipal = $beginningPaidAmountPrincipal - $beginningPayablePrincipal;

                $beginigDueAmount          = -$beginigAdvanceAmount;
                $beginigDueAmountPrincipal = -$beginigAdvanceAmountPrincipal;

                $beginigAdvanceAmount          = $beginigAdvanceAmount < 0 ? 0 : $beginigAdvanceAmount;
                $beginigAdvanceAmountPrincipal = $beginigAdvanceAmountPrincipal < 0 ? 0 : $beginigAdvanceAmountPrincipal;

                $beginigDueAmount          = $beginigDueAmount < 0 ? 0 : $beginigDueAmount;
                $beginigDueAmountPrincipal = $beginigDueAmountPrincipal < 0 ? 0 : $beginigDueAmountPrincipal;

                // if advanced paid before period than it will be deducted
                $onPeriodPayable          = $loanStatus['periodPayableAmount'] - $beginigAdvanceAmount;
                $onPeriodPayable          = $onPeriodPayable < 0 ? 0 : $onPeriodPayable;
                $onPeriodPayablePrincipal = $loanStatus['periodPayableAmountPrincipal'] - $beginigAdvanceAmountPrincipal;
                $onPeriodPayablePrincipal = $onPeriodPayablePrincipal < 0 ? 0 : $onPeriodPayablePrincipal;

                $onPeriodDueAmount              = $onPeriodPayable - $onPeriodPaidAmount;
                $onPeriodAdvanceAmount          = -$onPeriodDueAmount;
                $onPeriodDueAmountPrincipal     = $onPeriodPayablePrincipal - $onPeriodPaidAmountPrincipal;
                $onPeriodAdvanceAmountPrincipal = -$onPeriodDueAmountPrincipal;

                $onPeriodDueAmount              = $onPeriodDueAmount < 0 ? 0 : $onPeriodDueAmount;
                $onPeriodAdvanceAmount          = $onPeriodAdvanceAmount < 0 ? 0 : $onPeriodAdvanceAmount;
                $onPeriodDueAmountPrincipal     = $onPeriodDueAmountPrincipal < 0 ? 0 : $onPeriodDueAmountPrincipal;
                $onPeriodAdvanceAmountPrincipal = $onPeriodAdvanceAmountPrincipal < 0 ? 0 : $onPeriodAdvanceAmountPrincipal;

                // Assign values to main object
                $loanStatuses[$key]['onPeriodPayable']                = $onPeriodPayable;
                $loanStatuses[$key]['onPeriodPayablePrincipal']       = $onPeriodPayablePrincipal;
                $loanStatuses[$key]['onPeriodDueAmount']              = $onPeriodDueAmount;
                $loanStatuses[$key]['onPeriodDueAmountPrincipal']     = $onPeriodDueAmountPrincipal;
                $loanStatuses[$key]['onPeriodAdvanceAmount']          = $onPeriodAdvanceAmount;
                $loanStatuses[$key]['onPeriodAdvanceAmountPrincipal'] = $onPeriodAdvanceAmountPrincipal;
                $loanStatuses[$key]['onPeriodCollection'] = $onPeriodPaidAmount;
                $loanStatuses[$key]['onPeriodCollectionPrincipal'] = $onPeriodPaidAmountPrincipal;
                $loanStatuses[$key]['onPeriodCollectionInterest'] = $onPeriodPaidAmount - $onPeriodPaidAmountPrincipal;

                // classify regular, due, advance collection
                $remainingCollectionAmount                   = $onPeriodPaidAmount;
                $loanStatuses[$key]['onPeriodReularCollection'] = min($onPeriodPayable, $remainingCollectionAmount);

                $remainingCollectionAmount -= $loanStatuses[$key]['onPeriodReularCollection'];

                $loanStatuses[$key]['onPeriodDueCollection'] = min($remainingCollectionAmount, $beginigDueAmount);

                $remainingCollectionAmount -= $loanStatuses[$key]['onPeriodDueCollection'];

                $loanStatuses[$key]['onPeriodAdvanceCollection'] = $remainingCollectionAmount;

                // Principal
                $remainingCollectionAmount                   = $onPeriodPaidAmountPrincipal;
                $loanStatuses[$key]['onPeriodReularCollectionPrincipal'] = min($onPeriodPayablePrincipal, $remainingCollectionAmount);

                $remainingCollectionAmount -= $loanStatuses[$key]['onPeriodReularCollectionPrincipal'];

                $loanStatuses[$key]['onPeriodDueCollectionPrincipal'] = min($remainingCollectionAmount, $beginigDueAmountPrincipal);

                $remainingCollectionAmount -= $loanStatuses[$key]['onPeriodDueCollectionPrincipal'];

                $loanStatuses[$key]['onPeriodAdvanceCollectionPrincipal'] = $remainingCollectionAmount;

                $loanStatuses[$key]['onPeriodRebateAmount']             = $loanCollectionOnPeriod->where('paymentType', 'Rebate')->sum('amount');

                ////////////////
                // previous logic of classifying regular, due, advance collection
                // $remainingCollectionAmount                   = $onPeriodPaidAmount;
                // $loanStatuses[$key]['onPeriodDueCollection'] = min($onPeriodPaidAmount, $beginigDueAmount);

                // $remainingCollectionAmount -= $loanStatuses[$key]['onPeriodDueCollection'];

                // $loanStatuses[$key]['onPeriodReularCollection'] = min($onPeriodPayable, $remainingCollectionAmount);

                // $remainingCollectionAmount -= $loanStatuses[$key]['onPeriodReularCollection'];

                // $loanStatuses[$key]['onPeriodAdvanceCollection'] = $remainingCollectionAmount;

                // // Principal
                // $remainingCollectionAmountPrincipal                   = $onPeriodPaidAmountPrincipal;
                // $loanStatuses[$key]['onPeriodDueCollectionPrincipal'] = min($onPeriodPaidAmountPrincipal, $beginigDueAmount);

                // $remainingCollectionAmountPrincipal -= $loanStatuses[$key]['onPeriodDueCollectionPrincipal'];

                // $loanStatuses[$key]['onPeriodReularCollectionPrincipal'] = min($onPeriodPayablePrincipal, $remainingCollectionAmountPrincipal);

                // $remainingCollectionAmountPrincipal -= $loanStatuses[$key]['onPeriodReularCollectionPrincipal'];

                // $loanStatuses[$key]['onPeriodAdvanceCollectionPrincipal'] = $remainingCollectionAmountPrincipal;
                ////////////////
            }
        }

        return $loanStatuses;
    }



    public static function generateLoanSchedule($loanIdOrIds, ...$dates)
    {
        self::$regularLoanConfig = json_decode(DB::table('mfn_config')->where('title', 'regularLoan')->value('content'));
        rsort(self::$regularLoanConfig->preferedAmounts);

        if (isset($dates[1])) {
            self::$loanStatusToDate   = date('Y-m-d', strtotime($dates[1]));
            self::$loanStatusFromDate = date('Y-m-d', strtotime($dates[0]));
        } elseif (isset($dates[0])) {
            self::$loanStatusToDate = date('Y-m-d', strtotime($dates[0]));
        }

        if (self::$loanStatusFromDate > self::$loanStatusToDate) {
            return 'From date should be appear first';
        }

        if (is_numeric($loanIdOrIds)) {
            $loanIdOrIds = [$loanIdOrIds];
        }

        $loans = DB::table('mfn_loans')
            ->whereIn('id', $loanIdOrIds)
            ->orderBy('branchId')
            ->orderBy('samityId')
            ->get();

        // get reschedules
        // here self::$loanReschedules and self::$rescheduledLoanIds it defind into a condition
        // because generateLoanSchedule() functionn may call from dummy reschedule data
        // when it is call from dummy values then merge it with original
        $loanReschedules = DB::table('mfn_loan_reschedules')
            ->where('is_delete', 0)
            ->whereIn('loanId', $loanIdOrIds);
        if (self::$exceptRescheduleId !== null) {
            $loanReschedules->where('id', '!=', self::$exceptRescheduleId);
        }
        $loanReschedules = $loanReschedules->get();

        if (self::$loanReschedules !== null) {
            self::$loanReschedules = self::$loanReschedules->merge($loanReschedules);
        } else {
            self::$loanReschedules = $loanReschedules;
        }

        self::$rescheduledLoanIds = self::$loanReschedules->pluck('loanId')->toArray();

        $currentSamityId = 0;
        $schedules       = [];
        $loanStatuses    = [];

        // get the rage of dates between which we will get the holidays
        $holidayFrom = date('Y-m-d', strtotime($loans->min('disbursementDate')));
        $holidayTo   = date('Y-m-d', strtotime("+10 years", strtotime($loans->max('disbursementDate')))); // here we assume that no loan period is longer than 10 years

        $samityIdsHavingSamityHoliday = DB::table('hr_holidays_special')
            ->where([
                ['is_delete', 0],
                ['sh_date_from', '>=', $holidayFrom],
                ['sh_date_to', '<=', $holidayTo],
                ['samity_id', '>', 0],
            ])
            ->groupBy('samity_id')
            ->pluck('samity_id')
            ->toArray();

        $samityIdsHavingSamityHoliday = [];

        foreach ($loans as $key => $loan) {
            if ($currentSamityId != $loan->samityId) {
                $currentSamityId        = $loan->samityId;
                self::$samity           = DB::table('mfn_samity')->where('id', $loan->samityId)->first();
                self::$samityDayChanges = DB::table('mfn_samity_day_changes')
                    ->where([
                        ['is_delete', 0],
                        ['samityId', $loan->samityId],
                    ])
                    ->get();
            }
            if (count(self::$holidays) == 0 || in_array($loan->samityId, $samityIdsHavingSamityHoliday)) {
                self::$holidays         = HrService::systemHolidays($companyId = null, $branchId = $loan->branchId, $samityId = $loan->samityId, $holidayFrom, $holidayTo);                
            }

            $installments = self::generateInstallmentDetails($loan->loanAmount, $loan->numberOfInstallment, $loan->interestRateIndex, $loan->loanType);

            $insallmentdates = self::generateInstallmentDates($loan);

            $installmentNo = 1;
            $payableAmount = $payableAmountPrincipal = $periodPayableAmount = $periodPayableAmountPrincipal = 0;

            foreach ($insallmentdates as $insallmentdate) {

                $schedule['loanId']          = $loan->id;
                $schedule['installmentNo']   = $installmentNo;
                $schedule['installmentDate'] = $insallmentdate;
                $schedule['weekDay']         = date('l', strtotime($insallmentdate));

                if ($installmentNo == $loan->numberOfInstallment) {
                    // if it the last installment
                    $schedule['installmentAmount']          = $installments['lastInstallmentAmount'];
                    $schedule['actualInastallmentAmount']   = 0;
                    $schedule['extraInstallmentAmount']     = 0;
                    $schedule['installmentAmountPrincipal'] = $installments['lastInstallmentPrincipal'];
                    $schedule['installmentAmountInterest']  = $installments['lastInstallmentInterest'];
                } else {
                    $schedule['installmentAmount']          = $installments['installmentAmount'];
                    $schedule['actualInastallmentAmount']   = $installments['actualInastallmentAmount'];
                    $schedule['extraInstallmentAmount']     = $installments['extraInstallmentAmount'];
                    $schedule['installmentAmountPrincipal'] = $installments['installmentAmountPrincipal'];
                    $schedule['installmentAmountInterest']  = $installments['installmentAmountInterest'];
                }

                $installmentNo++;

                if (self::$requirement == 'status') {
                    $payableAmount += $schedule['installmentAmount'];
                    $payableAmountPrincipal += $schedule['installmentAmountPrincipal'];

                    if (self::$loanStatusFromDate != null && $insallmentdate >= self::$loanStatusFromDate) {
                        $periodPayableAmount += $schedule['installmentAmount'];
                        $periodPayableAmountPrincipal += $schedule['installmentAmountPrincipal'];
                    }
                } else {                    

                    if ($insallmentdate >= self::$loanStatusFromDate) {
                        array_push($schedules, $schedule);
                    }
                }
            }
            if (self::$requirement == 'status') {
                $loanStatus['loanId']                 = $loan->id;
                $loanStatus['payableAmount']          = $payableAmount;
                $loanStatus['payableAmountPrincipal'] = $payableAmountPrincipal;
                if (self::$loanStatusFromDate != null) {
                    $loanStatus['periodPayableAmount']          = $periodPayableAmount;
                    $loanStatus['periodPayableAmountPrincipal'] = $periodPayableAmountPrincipal;
                }
                array_push($loanStatuses, $loanStatus);
            }
        } /* loan loop end */

        if (self::$requirement == 'status') {
            return $loanStatuses;
        }

        return $schedules;
    }

    public static function generateInstallmentDetails($loanAmount, $numberOfInstallment, $interestRateIndex, $loanType)
    {
        if ($loanType == 'Onetime') {
            $data = array(
                'installmentAmount'          => $loanAmount,
                'actualInastallmentAmount'   => $loanAmount,
                'extraInstallmentAmount'     => 0,
                'installmentAmountPrincipal' => $loanAmount,
                'installmentAmountInterest'  => 0,
                'lastInstallmentAmount'      => $loanAmount,
                'lastInstallmentPrincipal'   => $loanAmount,
                'lastInstallmentInterest'    => 0,
                'adoptedPolicy'              => 'Onetime',
            );

            return $data;
        }

        $repayAmount = round($loanAmount * $interestRateIndex);

        $interestAmount        = $repayAmount - $loanAmount;
        $installmentAmount     = null;
        $installmentAmountFlag = false;
        $adoptedPolicy         = null;

        $actualInastallmentAmount = round($repayAmount / $numberOfInstallment, 5);
        // it is the last two digit with the fractional part
        $actualInstallmentLastDigits = (float)substr(number_format($actualInastallmentAmount, 5, '.', ''), -8);

        if (self::$regularLoanConfig == null) {
            self::$regularLoanConfig = json_decode(DB::table('mfn_config')->where('title', 'regularLoan')->value('content'));
            rsort(self::$regularLoanConfig->preferedAmounts);
        }

        // in thi sloop we will find $installmentAmount, $extraInstallmentAmount and $lastInstallmentAmount
        foreach (self::$regularLoanConfig->installmentAmountGeneratePolicies as $key => $policy) {

            if ($policy == '2.5Percent') {
                $installmentAmount      = $loanAmount * 0.025;
                $extraInstallmentAmount = $installmentAmount - $actualInastallmentAmount;
                $lastInstallmentAmount  = $repayAmount - $installmentAmount * ($numberOfInstallment - 1);

                if ($installmentAmount != round($installmentAmount) || $lastInstallmentAmount <= 0 || $extraInstallmentAmount < 0) {
                    continue;
                }

                $installmentAmountFlag = true;
                $adoptedPolicy         = $policy;
            } elseif ($policy == 'higestPreferedAmount') {
                if ($actualInastallmentAmount == round($actualInastallmentAmount) && in_array($actualInstallmentLastDigits, self::$regularLoanConfig->preferedAmounts)) {
                    $installmentAmount      = $actualInastallmentAmount;
                    $extraInstallmentAmount = $installmentAmount - $actualInastallmentAmount;
                    $lastInstallmentAmount  = $repayAmount - $installmentAmount * ($numberOfInstallment - 1);
                    if ($lastInstallmentAmount <= 0) {
                        continue;
                    }
                    $installmentAmountFlag = true;
                    $adoptedPolicy         = $policy;
                } else {
                    foreach (self::$regularLoanConfig->preferedAmounts as $key => $preferedAmount) {
                        $extraInstallmentAmount = $preferedAmount - $actualInstallmentLastDigits;
                        $installmentAmount      = $actualInastallmentAmount + $extraInstallmentAmount;
                        $lastInstallmentAmount  = $repayAmount - $installmentAmount * ($numberOfInstallment - 1);
                        if ($extraInstallmentAmount < 0 || $lastInstallmentAmount <= 0) {
                            continue;
                        }
                        $installmentAmountFlag = true;
                        $adoptedPolicy         = $policy;
                        break;
                    }
                }
            } elseif ($policy == 'nearestPreferedAmount') {
                $preferedAmounts = self::$regularLoanConfig->preferedAmounts;
                $preferedAmounts = array_filter($preferedAmounts, function ($value) use ($actualInstallmentLastDigits) {
                    return $value >= $actualInstallmentLastDigits;
                });
                sort($preferedAmounts);
                foreach ($preferedAmounts as $key => $preferedAmount) {
                    $extraInstallmentAmount = $preferedAmount - $actualInstallmentLastDigits;
                    $installmentAmount      = $actualInastallmentAmount + $extraInstallmentAmount;
                    $lastInstallmentAmount  = $repayAmount - $installmentAmount * ($numberOfInstallment - 1);
                    if ($extraInstallmentAmount < 0 || $lastInstallmentAmount <= 0) {
                        continue;
                    }
                    $installmentAmountFlag = true;
                    $adoptedPolicy         = $policy;
                    break;
                }
            } elseif ($policy == 'roundToDecade') {
                $extraInstallmentAmount = (ceil($actualInstallmentLastDigits / 10) * 10) - $actualInstallmentLastDigits;
                $installmentAmount      = $actualInastallmentAmount + $extraInstallmentAmount;
                $lastInstallmentAmount  = $repayAmount - $installmentAmount * ($numberOfInstallment - 1);
                if ($lastInstallmentAmount <= 0) {
                    continue;
                }
                $installmentAmountFlag = true;
                $adoptedPolicy         = $policy;
            } elseif ($policy == 'roundToOne') {
                $extraInstallmentAmount = ceil($actualInstallmentLastDigits) - $actualInstallmentLastDigits;
                $installmentAmount      = $actualInastallmentAmount + $extraInstallmentAmount;
                $lastInstallmentAmount  = $repayAmount - $installmentAmount * ($numberOfInstallment - 1);
                if ($lastInstallmentAmount <= 0) {
                    continue;
                }
                $installmentAmountFlag = true;
                $adoptedPolicy         = $policy;
            }

            if ($installmentAmountFlag == true) {
                break;
            }
        }

        if ($installmentAmountFlag == false) {
            return null;
        }

        $installmentAmountPrincipal = round($installmentAmount / $interestRateIndex, 5);
        $installmentAmountInterest  = $installmentAmount - $installmentAmountPrincipal;
        $lastInstallmentPrincipal   = round($loanAmount - ($installmentAmountPrincipal * ($numberOfInstallment - 1)), 5);
        $lastInstallmentInterest    = round($interestAmount - ($installmentAmountInterest * ($numberOfInstallment - 1)), 5);

        $data = array(
            'installmentAmount'          => $installmentAmount,
            'actualInastallmentAmount'   => $actualInastallmentAmount,
            'extraInstallmentAmount'     => $extraInstallmentAmount,
            'installmentAmountPrincipal' => $installmentAmountPrincipal,
            'installmentAmountInterest'  => $installmentAmountInterest,
            'lastInstallmentAmount'      => $lastInstallmentAmount,
            'lastInstallmentPrincipal'   => $lastInstallmentPrincipal,
            'lastInstallmentInterest'    => $lastInstallmentInterest,
            'adoptedPolicy'              => $adoptedPolicy,
        );

        return $data;
    }

    public static function generateInstallmentDates($loan)
    {
        $dates = [];
        // if it is one time loan
        if ($loan->loanType === 'Onetime') {
            $dates[0] = $loan->firstRepayDate;
            while (in_array($dates[0], self::$holidays)) {
                $dates[0] = date('Y-m-d', strtotime("+7 day", strtotime($dates[0])));
            }
        } elseif ($loan->loanType === 'Regular') {
            // if it is Daily Loan
            if ($loan->repaymentFrequencyId === 1) {
                $dates = self::generateDailyInstallmentDates($loan);
            }
            // if it is Weekly Loan
            elseif ($loan->repaymentFrequencyId === 2) {
                $dates = self::generateWeeklyInstallmentDates($loan);
            }
            // if it is Monthly Loan
            elseif ($loan->repaymentFrequencyId === 4) {
                $dates = self::generateMonthlyInstallmentDates($loan);
            }
        }

        return $dates;
    }

    public static function generateDailyInstallmentDates($loan)
    {
        $installmentDate = $loan->firstRepayDate;
        $dates           = [];
        for ($i = 0; $i < $loan->numberOfInstallment && (self::$loanStatusToDate == null || $installmentDate <= self::$loanStatusToDate); $i++) {
            // reschedule installment
            if (in_array($loan->id, self::$rescheduledLoanIds)) {
                $numberOfTerm = self::$loanReschedules->where('loanId', $loan->id)->where('installmentNo', $i + 1)->sum('numberOfTerm');
                if ($numberOfTerm > 0) {
                    $installmentDate = date('Y-m-d', strtotime("+" . $numberOfTerm . " day", strtotime($installmentDate)));
                }
            }

            while (in_array($installmentDate, self::$holidays)) {
                $installmentDate = date('Y-m-d', strtotime("+1 day", strtotime($installmentDate)));
            }
            if (self::$loanStatusToDate != null && $installmentDate > self::$loanStatusToDate) {
                continue;
            }
            $dates[$i]       = $installmentDate;
            $installmentDate = date('Y-m-d', strtotime("+1 day", strtotime($installmentDate)));
        }

        return $dates;
    }

    public static function generateWeeklyInstallmentDates($loan)
    {
        // it should be ensured that first repay date is on samiy day
        $installmentDate = $loan->firstRepayDate;
        $dates           = [];
        $willContinue = true;
        for ($i = 0; $i < $loan->numberOfInstallment && $willContinue; $i++) {

            // reschedule installment
            if (in_array($loan->id, self::$rescheduledLoanIds)) {
                $numberOfTerm = self::$loanReschedules->where('loanId', $loan->id)->where('installmentNo', $i + 1)->sum('numberOfTerm');
                if ($numberOfTerm > 0) {
                    $installmentDate = date('Y-m-d', strtotime("+" . (7 * $numberOfTerm) . " day", strtotime($installmentDate)));
                }
            }

            $installmentDate = self::getSamityDateOfWeek(self::$samity, $installmentDate);

            while (in_array($installmentDate, self::$holidays) || $installmentDate < $loan->firstRepayDate) {
                $installmentDate = date('Y-m-d', strtotime("+7 day", strtotime($installmentDate)));
                $installmentDate = self::getSamityDateOfWeek(self::$samity, $installmentDate);
            }
            if (self::$loanStatusToDate != null && $installmentDate > self::$loanStatusToDate) {
                $willContinue = false;
                continue;
            }
            $dates[$i]       = $installmentDate;
            $installmentDate = date('Y-m-d', strtotime("+7 day", strtotime($installmentDate)));
        }

        return $dates;
    }

    public static function generateMonthlyInstallmentDates($loan)
    {
        $installmentDate = $loan->firstRepayDate;
        $monthStartDate  = date("Y-m-01", strtotime($installmentDate));
        $monthEndDate    = date("Y-m-t", strtotime($installmentDate));

        $dates = [];
        $willContinue = true;
        for ($i = 0; $i < $loan->numberOfInstallment && $willContinue; $i++) {
            // reschedule installment
            if (in_array($loan->id, self::$rescheduledLoanIds)) {
                $numberOfTerm = self::$loanReschedules->where('loanId', $loan->id)->where('installmentNo', $i + 1)->sum('numberOfTerm');
                if ($numberOfTerm > 0) {
                    $monthStartDate = date('Y-m-d', strtotime("+" . ($numberOfTerm) . " months", strtotime($monthStartDate)));
                    $monthEndDate   = date("Y-m-t", strtotime($monthStartDate));

                    $installmentDate = date('Y-m-' . date('d', strtotime($loan->firstRepayDate)), strtotime($monthStartDate));
                }
            }

            $installmentDate = self::getSamityDateOfWeek(self::$samity, $installmentDate);            

            while ($installmentDate < $monthStartDate) {
                $installmentDate = date('Y-m-d', strtotime("+7 day", strtotime($installmentDate)));
            }
            while ($installmentDate > $monthEndDate) {
                $installmentDate = date('Y-m-d', strtotime("-7 day", strtotime($installmentDate)));
            }

            $initialDate = $installmentDate;

            while (in_array($installmentDate, self::$holidays) || $installmentDate < $loan->firstRepayDate || $installmentDate < $monthStartDate) {
                $installmentDate = date('Y-m-d', strtotime("+7 day", strtotime($installmentDate)));
                $installmentDate = self::getSamityDateOfWeek(self::$samity, $installmentDate);
            }

            $progessiveInstallmentDate = $installmentDate;

            if ($installmentDate > $monthEndDate) {
                $installmentDate = date('Y-m-d', strtotime("-7 day", strtotime($initialDate)));
                while (in_array($installmentDate, self::$holidays) || $installmentDate > $loan->firstRepayDate || $installmentDate > $monthStartDate) {
                    $installmentDate = date('Y-m-d', strtotime("-7 day", strtotime($installmentDate)));
                    $installmentDate = self::getSamityDateOfWeek(self::$samity, $installmentDate);
                }
            }

            if ($installmentDate < $monthStartDate || $installmentDate < $loan->firstRepayDate) {

                if (self::$regularLoanConfig->monthlyLoanMonthOverflow == 'no') {
                    // set the installment date to the next any working day
                    $installmentDate = $initialDate;
                    while (in_array($installmentDate, self::$holidays)) {
                        $installmentDate = date('Y-m-d', strtotime("+1 day", strtotime($installmentDate)));
                    }
                    if ($installmentDate > $monthEndDate) {
                        $installmentDate = $initialDate;
                        while (in_array($installmentDate, self::$holidays)) {
                            $installmentDate = date('Y-m-d', strtotime("-1 day", strtotime($installmentDate)));
                        }
                    }
                    if ($installmentDate < $monthStartDate) {
                        // it means that whole month is holiday,
                        // so $progessiveInstallmentDate will be the $installmentDate
                        $installmentDate = $progessiveInstallmentDate;
                    }
                } elseif (self::$regularLoanConfig->monthlyLoanMOnthOverflow == 'yes') {
                    $installmentDate = $progessiveInstallmentDate;
                }
            }


            if (self::$loanStatusToDate != null && $installmentDate > self::$loanStatusToDate) {
                $willContinue = false;
                continue;
            }

            $dates[$i] = $installmentDate;

            $monthStartDate = date('Y-m-d', strtotime("+1 day", strtotime(date("Y-m-t", strtotime($installmentDate)))));
            $monthEndDate   = date("Y-m-t", strtotime($monthStartDate));

            $installmentDate = date('Y-m-' . date('d', strtotime($loan->firstRepayDate)), strtotime($monthStartDate));
        }

        return $dates;
    }

    public static function getLoanReschedulableDate($loanId, $installmentNo, $numberOfTerm, $exceptRescheduleId = null)
    {
        self::$exceptRescheduleId = $exceptRescheduleId;

        $loanId                    = (int)$loanId;
        $reschedule                = new \stdClass();
        $reschedule->loanId        = $loanId;
        $reschedule->installmentNo = $installmentNo;
        $reschedule->numberOfTerm  = $numberOfTerm;

        self::$loanReschedules = collect([
            0 => $reschedule,
        ]);

        $schedules = self::generateLoanSchedule($loanId);

        return $schedules[$installmentNo - 1]['installmentDate'];
    }

    public static function getInterestRateForRegularLoan($productId, $repaymentFrequencyId, $numberOfInstallment, $date)
    {
        $date         = date('Y-m-d', strtotime($date));
        $interestRate = DB::table('mfn_loan_product_interest_rates')
            ->where([
                ['is_delete', 0],
                ['productId', $productId],
                ['repaymentFrequencyId', $repaymentFrequencyId],
                ['numberOfInstallment', $numberOfInstallment],
                ['effectiveDate', '<=', $date],
            ])
            ->where(function ($query) use ($date) {
                $query->where('validTill', '0000-00-00')
                    ->orWhere('validTill', '>=', $date);
            })
            ->first();

        return $interestRate;
    }

    public static function getInterestRateForOnetimeLoan($productId, $date)
    {
        $date         = date('Y-m-d', strtotime($date));
        $interestRate = DB::table('mfn_loan_product_interest_rates')
            ->where([
                ['is_delete', 0],
                ['productId', $productId],
                ['effectiveDate', '<=', $date],
            ])
            ->where(function ($query) use ($date) {
                $query->where('validTill', '0000-00-00')
                    ->orWhere('validTill', '>=', $date);
            })
            ->first();

        return $interestRate;
    }

    /**
     * Get Branch MIS Software Opening date
     */
    public static function getBranchMisSoftwareStartDate($branchID = null)
    {

        $branchModel = 'App\\Model\\GNL\\Branch';
        if ($branchID == null) {
            $branchID = Session::get('LoginBy.user_config.branch_id');
        }

        $BranchData = $branchModel::where('id', $branchID)->first();
        //    $date = new DateTime ();
        //    $date->format('Y-m-d');

        return $BranchData->mfn_start_date;
    }

    public static function getLoanAccounts($filters = [])
    {
        $loans = DB::table('mfn_loans')->where('is_delete', 0);

        if (isset($filters['memberId'])) {
            $loans->where('memberId', $filters['memberId']);
        }
        if (isset($filters['isAuthorized'])) {
            $loans->where('isAuthorized', $filters['isAuthorized']);
        }
        if (isset($filters['status'])) {
            if ($filters['status'] == 'Living') {
                $loans->where('loanStatusId', 4); // 4 = Living
            }
        }
        if (isset($filters['onlyActiveLoan'])) {
            if ($filters['onlyActiveLoan'] == 'yes') {
                if (isset($filters['date'])) {
                    $date = $filters['date'];
                    $loans->where(function ($query) use ($date) {
                        $query->where('loanCompleteDate', '0000-00-00')
                            ->orWhere('loanCompleteDate', '>=', $date);
                    });
                } else {
                    $loans->where('loanCompleteDate', '0000-00-00');
                }
            }
        }
        if (isset($filters['date'])) {
            $loans->where('disbursementDate', '<=', $filters['date']);
        }

        return $loans->get();
    }

    public static function getCloseWeekDate($date1 = null, $date2 = null, $freequency = null)
    {

        $weekdate    = new DateTime($date1);
        $openingDate = new DateTime($date2);

        if ($freequency == 3) {
            $weekNum           = round($weekdate->format('d') / 7);
            $openingDayWeekNum = round($openingDate->format('d') / 7);

            if ($openingDayWeekNum >= 5) {
                $openingDayWeekNum = 4;
            }

            if ($weekNum == $openingDayWeekNum) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }
    }

    public static function getCashLedgerId($branchId = null, $projectID = null, $projectTypeID = null, $groupHead = null, $level = null)
    {
        if (is_null($branchId)) {
            $branchId = Auth::user()->branch_id;
        }

        $accTypeID = 4; // acctype id 4 for cash

        $ledgerData = AccService::getLedgerAccount($branchId, $projectID, $projectTypeID, $accTypeID, $groupHead, $level);

        if ($ledgerData->count() > 0) {
            $ledgerId = collect($ledgerData)->first()->id;

            return $ledgerId;
        } else {
            return null;
        }
    }

    public static function sendMail($table = null, $memberId = null, $time = null, $amount = 0, $isUpdate = false)
    {
        if (DB::table('mfn_config')->where('title', 'mail')->first()->content == 'yes') {

            $memberMail = DB::table('mfn_mail_verification')->where('memberId', $memberId);

            if ($memberMail->exists()) {

                if ($memberMail->first()->isVerified == 'yes') {

                    $dataAttachInMessage = array(
                        'amount' => $amount,
                        'date'   => date('d-m-Y', strtotime($time)),
                    );

                    $name  = DB::table('mfn_members')->where('id', $memberId)->first()->name;
                    $email = DB::table('mfn_member_details')->where('memberId', $memberId)->first()->email;

                    if ($isUpdate == false) {
                        $subject = __('mail.' . $table . '.insert.subject');
                        $body    = __('mail.' . $table . '.insert.body', $dataAttachInMessage);
                    } else {

                        $subject = __('mail.' . $table . '.update.subject');
                        $body    = __('mail.' . $table . '.update.body', $dataAttachInMessage);
                    }

                    SendMailJob::dispatch($email, $subject, $name, $body)->delay(now()->addMinutes(2));
                }
            }
        }
    }

    public static function getSavingsAccountsBalance($branchId)
    {
        $accounts = DB::table('mfn_savings_accounts as msa')
            ->where([
                ['msa.is_delete', 0],
                ['msa.branchId', $branchId],
            ])
            ->leftjoin('mfn_members as mm', 'msa.memberId', 'mm.id')
            ->select('msa.id as accountId', 'msa.branchId', 'msa.samityId', 'msa.memberId', 'mm.name as memberName')
            ->get();

        // $accountIds = $accounts->pluck('accountId')->all();

        $savingAccountInfo = array();

        foreach ($accounts as $key => $account) {

            $deposit = DB::table('mfn_savings_deposit')
                ->where([
                    ['is_delete', 0],
                    ['accountId', $account->accountId],
                    ['branchId', $account->branchId],
                    ['samityId', $account->samityId],
                    ['memberId', $account->memberId],
                ])
                ->sum('amount');

            $withdraw = DB::table('mfn_savings_withdraw')
                ->where([
                    ['is_delete', 0],
                    ['accountId', $account->accountId],
                    ['branchId', $account->branchId],
                    ['samityId', $account->samityId],
                    ['memberId', $account->memberId],
                ])
                ->sum('amount');

            $savingAccountInfo[$key]['accountId']  = $account->accountId;
            $savingAccountInfo[$key]['branchId']   = $account->branchId;
            $savingAccountInfo[$key]['samityId']   = $account->samityId;
            $savingAccountInfo[$key]['memberId']   = $account->memberId;
            $savingAccountInfo[$key]['memberName'] = $account->memberName;
            $savingAccountInfo[$key]['balance']    = (is_null($deposit) ? 0 : floatval($deposit)) - (is_null($withdraw) ? 0 : floatval($withdraw));
        }

        return $savingAccountInfo;
    }

    /**
     * It returns the primary product of a member
     * if date is null then it returns the current primary product
     * if date is given, then it returns primary product id of the member on particular date
     *
     * @param [int or array if int] $memberIdOrIds
     * @param [null or date] $date
     * @return [if $memberIdOrIds is int then it will return int, if $memberIdOrIds is an array then it will retuen an array where index is the member id and vale is the primaryProductId, ['memberId' => 'primaryProductId'] ]
     */
    public static function getMemberPrimaryProductId($memberIdOrIds, $date = null)
    {
        $memberIds = $memberIdOrIds;
        if (!is_array($memberIdOrIds)) {
            $memberIds = [$memberIdOrIds];
        }

        $memberProductIds = DB::table('mfn_members')->whereIn('id', $memberIds)->pluck('primaryProductId', 'id')->toArray();

        if ($date == null) {
            if (is_int($memberIdOrIds)) {
                return $memberProductIds[$memberIdOrIds];
            }
            return $memberProductIds;
        }

        $productTransfers = DB::table('mfn_member_primary_product_transfers')
            ->where([
                ['is_delete', 0],
                ['transferDate', '>', $date],
            ])
            ->whereIn('memberId', $memberIds)
            // ->orderBy('transferDate')
            ->select('memberId', 'oldProductId', 'transferDate')
            ->get();

        foreach ($memberProductIds as $memberId => $productId) {
            if ($productTransfers->where('memberId', $memberId)->first() != null) {
                $closestTransferDate = $productTransfers->where('memberId', $memberId)->min('transferDate');

                $memberProductIds[$memberId] = $productTransfers->where('memberId', $memberId)->where('transferDate', $closestTransferDate)->first()->oldProductId;
            }
        }

        if (is_int($memberIdOrIds)) {
            return $memberProductIds[$memberIdOrIds];
        }
        return $memberProductIds;
    }

    public static function getBranchAssignedLoanProductIds($branchIdOrIds)
    {
        if (!is_array($branchIdOrIds)) {
            $branchIds = [$branchIdOrIds];
        } else {
            $branchIds = $branchIdOrIds;
        }

        $branchProducts = DB::table('mfn_branch_products')
            ->whereIn('branchId', $branchIds)
            ->pluck('loanProductIds')
            ->toArray();


        $productIds = [];

        foreach ($branchProducts as $key => $branchProduct) {
            $productIds = array_merge($productIds, array_map('intval', json_decode($branchProduct)));
        }


        return $productIds;
    }

    public static function getBranchAssignedSavProductIds($branchIdOrIds)
    {
        if (!is_array($branchIdOrIds)) {
            $branchIds = [$branchIdOrIds];
        } else {
            $branchIds = $branchIdOrIds;
        }

        $branchProducts = DB::table('mfn_branch_products')
            ->whereIn('branchId', $branchIds)
            ->pluck('savingProductIds')
            ->toArray();

        $productIds = [];

        foreach ($branchProducts as $key => $branchProduct) {
            $productIds = array_merge($productIds, array_map('intval', json_decode($branchProduct)));
        }

        $productIds = array_unique($productIds);

        return $productIds;
    }



    /**
     * [setOpeningBalanceForOneTimeSavings description]
     *
     * @param   int  $branchId
     *
     * @return  [void]
     */
    public static function setOpeningBalanceForOneTimeSavings($branchId, $accountId = null)
    {
        $branch = DB::table('gnl_branchs')->where('id', $branchId)->first();

        if ($branch == null) {
            return null;
        }

        $oneTimeProductIds = DB::table('mfn_savings_product')
            ->where('productTypeId', 2)
            ->pluck('id')
            ->toArray();

        $savAccs = DB::table('mfn_savings_accounts')
            ->where([
                ['is_delete', 0],
                ['branchId', $branchId],
                ['openingDate', '<=', $branch->mfn_start_date],
                ['isOpening', 1]
            ])
            ->whereIn('savingsProductId', $oneTimeProductIds);

        if ($accountId != null) {
            $savAccs->where('id', $accountId);
        }

        $savAccs = $savAccs->get();

        // store or update opening and deposit for one time savings
        foreach ($savAccs as $key => $savAcc) {
            $memberPrimaryProductId = self::getMemberPrimaryProductId($savAcc->memberId, $savAcc->openingDate);

            // insert opening balance information
            DB::table('mfn_savings_opening_balance')
                ->updateOrInsert(
                    ['accountId'         => $savAcc->id],
                    [
                        'memberId'          => $savAcc->memberId,
                        'samityId'          => $savAcc->samityId,
                        'branchId'          => $savAcc->branchId,
                        'depositAmount'     => $savAcc->autoProcessAmount,
                        'interestAmount'    => 0,
                        'withdrawAmount'    => 0,
                        'openingBalance'    => $savAcc->autoProcessAmount,
                        'created_at'        => date('Y-m-d H:m:s'),
                        'created_by'        => 0,
                    ]
                );

            DB::table('mfn_savings_deposit')
                ->updateOrInsert(
                    ['accountId' => $savAcc->id, 'is_delete' => 0, 'transactionTypeId' => 4], // 4 is for opening balance
                    [
                        'memberId'          => $savAcc->memberId,
                        'samityId'          => $savAcc->samityId,
                        'branchId'          => $savAcc->branchId,
                        'primaryProductId'  => $memberPrimaryProductId,
                        'savingsProductId'  => $savAcc->savingsProductId,
                        'amount'            => $savAcc->autoProcessAmount,
                        'date'              => $savAcc->openingDate,
                        'ledgerId'          => 0,
                        'isAuthorized'      => 1,
                        'created_at'        => date('Y-m-d H:m:s'),
                        'created_by'        => 0,
                    ]
                );
        }
    }

    public static function getFieldOfficersByDate($samityIdOrIds, $startDate, $endDate)
    {
        if (is_array($samityIdOrIds)) {
            $samityIds = $samityIdOrIds;
        }
        else{
            $samityIds = [$samityIdOrIds];
        }
        $samities = DB::table('mfn_samity')
            ->whereIn('id', $samityIds)
            ->select('id', 'fieldOfficerEmpId')
            ->get();

        $fieldOfficerChanges = DB::table('mfn_samity_field_officer_change')
            ->where([
                ['is_delete', 0],
                ['effectiveDate', '>=', $startDate],
                ['effectiveDate', '<=', $endDate],
            ])
            ->orderBy('samityId')
            ->orderBy('effectiveDate')
            ->whereIn('samityId', $samityIds)
            ->get();

        $samities = $samities->whereNotIn('id', $fieldOfficerChanges->pluck('samityId')->toArray());

        $data = array();

        foreach ($samities as $key => $samity) {
            $info['fieldOfficerId'] = $samity->fieldOfficerEmpId;
            $info['samityId'] = $samity->id;
            $info['dateFrom'] = $startDate;
            $info['dateTo'] = $endDate;

            array_push($data, $info);
        }

        foreach ($fieldOfficerChanges as $key => $fieldOfficerChange) {
            // for old field officer
            $info['fieldOfficerId'] = $fieldOfficerChange->oldFieldOfficerEmpId;
            $info['samityId'] = $fieldOfficerChange->samityId;
            $wouldPush = true;

            $dateFrom = $startDate;
            if (isset($fieldOfficerChanges[$key - 1])) {
                if ($fieldOfficerChanges[$key - 1]->samityId == $fieldOfficerChange->samityId) {
                    $dateFrom = $fieldOfficerChanges[$key - 1]->effectiveDate;
                    $wouldPush = false;
                }
            }
            $info['dateFrom'] = $dateFrom;

            $dateTo = date('Y-m-d', strtotime('-1 days', strtotime($fieldOfficerChange->effectiveDate)));
            $info['dateTo'] = $dateTo;

            if ($wouldPush) {
                array_push($data, $info);
            }            

            // for new field officer
            $info['fieldOfficerId'] = $fieldOfficerChange->newFieldOfficerEmpId;
            $info['samityId'] = $fieldOfficerChange->samityId;

            $dateFrom = $fieldOfficerChange->effectiveDate;
            $info['dateFrom'] = $dateFrom;

            $dateTo = $endDate;
            if (isset($fieldOfficerChanges[$key + 1])) {
                if ($fieldOfficerChanges[$key + 1]->samityId == $fieldOfficerChange->samityId) {
                    $dateTo = date('Y-m-d', strtotime('-1 days', strtotime($fieldOfficerChanges[$key + 1]->effectiveDate)));
                }
            }
            $info['dateTo'] = $dateTo;

            array_push($data, $info);
        }

        return $data;
    }
}
