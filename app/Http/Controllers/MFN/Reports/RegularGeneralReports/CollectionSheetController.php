<?php

namespace App\Http\Controllers\MFN\Reports\RegularGeneralReports;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\HrService;
use App\Services\MfnService;

class CollectionSheetController extends Controller
{
    public function index()
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        $branches = DB::table('gnl_branchs')
            ->whereIn('id', $accessAbleBranchIds)
            ->select(DB::raw("CONCAT(branch_code , ' - ' , branch_name) AS branchName, id"))
            ->get();

        $productCategories = DB::table('mfn_loan_product_category')
            ->where('is_delete', 0)
            ->select('id', 'name')
            ->get();

        $years = self::getYears();
        $months = array(
            1 => 'January',
            2 => 'February',
            3 => 'March',
            4 => 'April',
            5 => 'May',
            6 => 'June',
            7 => 'July',
            8 => 'August',
            9 => 'September',
            10 => 'October',
            11 => 'November',
            12 => 'December'
        );

        $preSelectedBranchId = '';

        if (count($accessAbleBranchIds) == 1) {
            $preSelectedBranchId = Auth::user()->branch_id;
        }

        $data = array(
            'branches'              => $branches,
            'productCategories'     => $productCategories,
            'years'                 => $years,
            'months'                => $months,
            'preSelectedBranchId'   => $preSelectedBranchId,
        );

        return view('MFN.Reports.RegularGeneralReports.CollectionSheet.index', $data);
    }

    public function printReport(Request $req)
    {
        if ($req->samity == '') return '';

        $date = $req->year . '-' . str_pad($req->month, 2, '0', STR_PAD_LEFT)  . '-' . '01';
        $monthEndDate = date('Y-m-t', strtotime($date));

        $samity = DB::table('mfn_samity')
            ->where('id', $req->samity)
            ->first();

        $weekDates = self::getWeekDays($samity, $date);

        // opening date is the date before first week date
        $openingDate = date('Y-m-d', strtotime('-1 day', strtotime(min($weekDates))));

        $branch = DB::table('gnl_branchs')
            ->join('gnl_companies', 'gnl_companies.id', 'gnl_branchs.company_id')
            ->where('gnl_branchs.id', $samity->branchId)
            ->select('comp_name', 'comp_addr', 'gnl_branchs.*')
            ->first();

        $members = DB::table('mfn_members AS member')
            ->join('mfn_member_details AS md', 'md.memberId', 'member.id')
            ->join('mfn_loan_products AS lp', 'lp.id', 'member.primaryProductId')
            ->where([
                ['member.is_delete', 0],
                ['member.samityId', $req->samity],
                ['member.admissionDate', '<=', $monthEndDate],
            ])
            ->where(function ($query) use ($date) {
                $query->where('closingDate', '0000-00-00')
                    ->orWhere('closingDate', '>=', $date);
            });

        if ($req->product_cat != '') {
            $catProductIds = DB::table('mfn_loan_products')->where('is_delete', 0)->where('productCategoryId', $req->product_cat)->pluck('id')->toArray();
            $members->whereIn('member.primaryProductId', $catProductIds);
        }
        if ($req->product != '') {
            $members->where('member.primaryProductId', $req->product);
        }

        $members = $members->select('member.id', 'member.name', 'memberCode', 'spouseName', 'lp.name AS primaryProduct')
            ->get();

        $loans = DB::table('mfn_loans AS loan')
        ->join('mfn_loan_products AS lp', 'lp.id', 'loan.productId')
            ->where([
                ['loan.is_delete', 0],
                ['loan.disbursementDate', '<=', $monthEndDate],
            ])
            ->whereIn('loan.memberId', $members->pluck('id')->toArray())
            ->where(function ($query) use ($date) {
                $query->where('loan.loanCompleteDate', '0000-00-00')
                    ->orWhere('loan.loanCompleteDate', '>=', $date);
            })
            ->select('loan.*', 'lp.name AS loanProductName')
            ->get();

        MfnService::resetProperties();
        $loanSchedules = MfnService::generateLoanSchedule($loans->pluck('id')->toArray(), max($weekDates));
        $loanSchedules = collect($loanSchedules);

        $weekDayLoanSchedules = clone $loanSchedules->whereIn('installmentDate', $weekDates);

        // filter the loans havings loan schedule on week days only
        $loans = $loans->whereIn('id', $weekDayLoanSchedules->unique('loanId')->pluck('loanId')->toArray());

        MfnService::resetProperties();
        $loanStatuses = MfnService::getLoanStatus($loans->pluck('id')->toArray(), $openingDate);
        $loanStatuses = collect($loanStatuses);

        foreach ($loans as $key => $loan) {
            $loans[$key]->outstanding = $loanStatuses->where('loanId', $loan->id)->sum('outstanding');
            $loans[$key]->dueAmount = $loanStatuses->where('loanId', $loan->id)->sum('dueAmount');

            // repayWeek is the number of total completed before repay date of this month
            $openingSchedules = $loanSchedules->where('loanId', $loan->id)->where('installmentDate', '<', min($weekDates));
            $repayWeek = 0;
            $paidAmount = $loanStatuses->where('loanId', $loan->id)->sum('paidAmount');
            foreach ($openingSchedules as $openingSchedule) {

                if ($openingSchedule['installmentAmount'] >= $paidAmount) {
                    $repayWeek++;
                }
                $paidAmount -= $openingSchedule['installmentAmount'];
            }
            $loans[$key]->repayWeek = $repayWeek;
        }

        $savAccs = DB::table('mfn_savings_accounts AS sa')
            ->join('mfn_savings_product AS savP', 'savP.id', 'sa.savingsProductId')
            ->where([
                ['sa.is_delete', 0],
                ['savP.productTypeId', 1], // Regular Product
                ['sa.openingDate', '<=', $monthEndDate],
            ])
            ->whereIn('memberId', $members->pluck('id')->toArray())
            ->where(function ($query) use ($date) {
                $query->where('sa..closingDate', '0000-00-00')
                    ->orWhere('sa..closingDate', '>=', $date);
            })
            ->select('sa.*', 'savP.name AS savProductName', 'savP.collectionFrequencyId')
            ->get();

        // get samity day for monthly savings account
        foreach ($savAccs->where('collectionFrequencyId', 3) as $key => $savAcc) {
            $savAccs[$key]->depositSamityDay = self::getMonthlySavingsSamityDate($weekDates, $savAcc->openingDate);
        }

        $savOpeningBalanceParameters = array(
            'accountIds'    => $savAccs->pluck('id')->toArray(),
            'dateTo'        => $openingDate,
            'individual'    => true,
        );
        $savOpeningBalances = MfnService::getSavingsBalance($savOpeningBalanceParameters);
        $savOpeningBalances = collect($savOpeningBalances);

        // filter the mmebers havings loan or savings accounts
        $contableMemberIds = array_unique(array_merge($loans->pluck('memberId')->toArray(), $savAccs->pluck('memberId')->toArray()));
        $members = $members->whereIn('id', $contableMemberIds);

        $fieldOfficer = DB::table('hr_employees')
            ->where('id', $samity->fieldOfficerEmpId)
            ->select(DB::raw('CONCAT(employee_no, " - ", emp_name) AS fieldOfficer'))
            ->value('fieldOfficer');

        $month = date('F, Y', strtotime($date));
        $showMemberCode = $req->memberCodeVisibility == 'show' ? 1 : 0;
        $selectedLroductCategory = 'All';
        if ($req->product_cat != '') {
            $selectedLroductCategory = DB::table('mfn_loan_product_category')
                ->where('id', $req->product_cat)
                ->value('name');
        }
        $selectedLroduct = 'All';
        if ($req->product != '') {
            $selectedLroduct = DB::table('mfn_loan_products')
                ->where('id', $req->product)
                ->select(DB::raw("CONCAT(productCode, ' - ', name) AS name"))
                ->value('name');
        }


        $data = array(
            'members'                   => $members,
            'loans'                     => $loans,
            'savAccs'                   => $savAccs,
            'savOpeningBalances'        => $savOpeningBalances,
            'branch'                    => $branch,
            'weekDates'                 => $weekDates,
            'samity'                    => $samity,
            'fieldOfficer'              => $fieldOfficer,
            'month'                     => $month,
            'showMemberCode'            => $showMemberCode,
            'selectedLroduct'           => $selectedLroduct,
            'selectedLroductCategory'   => $selectedLroductCategory,
            'weekDayLoanSchedules'      => $weekDayLoanSchedules,
        );

        if ($req->report_option == 'singlePart') {
            return view('MFN.Reports.RegularGeneralReports.CollectionSheet.single_part', $data);
        } elseif ($req->report_option == 'twoPart') {
            return view('MFN.Reports.RegularGeneralReports.CollectionSheet.two_part', $data);
        }
    }

    public static function getYears()
    {
        $headOfficeOpeningDate = DB::table('gnl_branchs')->where('id', 1)->value('branch_opening_date');

        $startYear = (int)date('Y', strtotime($headOfficeOpeningDate));

        $endYear = (int)date('Y') + 1;

        $years = [];

        while ($endYear >= $startYear) {
            array_push($years, $endYear);
            $endYear--;
        }

        return $years;
    }

    /**
     * This method returns week days a samity of a particular month
     *
     * @param [object] $samity
     * @param [date] $date
     * @return array
     */
    public static function getWeekDays($samity, $date)
    {
        $monthStartDate = date('Y-m-01', strtotime($date));
        $monthEndDate = date('Y-m-t', strtotime($date));

        // some times it may happen that this function will return 3 weeks depending on some circumstances
        // to avoid this we will add 7 more days to $monthEndDate
        $endDate = date('Y-m-d', strtotime('+7 day', strtotime($monthEndDate)));

        $samityDates = array();

        while ($monthStartDate <= $endDate) {
            $samityDate = MfnService::getSamityDateOfWeek($samity, $monthStartDate);
            array_push($samityDates, $samityDate);
            $monthStartDate = date('Y-m-d', strtotime('+7 day', strtotime($monthStartDate)));
        }

        $monthStartDate = date('Y-m-01', strtotime($date));

        $samityDates = array_filter($samityDates, function ($value) use ($monthStartDate, $monthEndDate) {
            return ($value >= $monthStartDate && $value <= $monthEndDate);
        });
        $samityDates =  array_values($samityDates);

        // holidays
        $holidays = HrService::systemHolidays($companyId = null, $branchId = $samity->branchId, $samityId = $samity->id, $monthStartDate, $monthEndDate);

        $samityDates = array_diff($samityDates, $holidays);

        return $samityDates;
    }

    public static function getMonthlySavingsSamityDate($dates, $accountOpeningDate)
    {
        $accountOpeninDay = date('d', strtotime($accountOpeningDate));
        $closestDay = 99;
        $closestDate = null;

        foreach ($dates as $date) {
            $dateDay = date('d', strtotime($date));
            if (abs($accountOpeninDay - $dateDay) < $closestDay) {
                $closestDay = abs($accountOpeninDay - $dateDay);
                $closestDate = $date;
            }
        }

        return $closestDate;
    }

    public function getData(Request $req)
    {
        if ($req->context == 'samity') {
            $date = $req->year . '-' . str_pad($req->month, 2, '0', STR_PAD_LEFT)  . '-' . '01';
            $monthEndDate = date('Y-m-t', strtotime($date));

            // we have to take those samities which are on the $req->day on 1st date of the month
            $samityDayChanges = DB::table('mfn_samity_day_changes')
                ->where([
                    ['is_delete', 0],
                    ['branchId', $req->branchId],
                    ['effectiveDate', '>=', $date],
                ])
                ->orderBy('effectiveDate')
                ->get();

            $samityDayChanges = $samityDayChanges->unique('samityId');
            $samityIdsFromSamityDayChnages = $samityDayChanges->pluck('samityId')->toArray();
            $searchDay = $req->day;

            $samityies = DB::table('mfn_samity')
                ->where([
                    ['is_delete', 0],
                    ['branchId', $req->branchId],
                    ['openingDate', '<=', $monthEndDate],
                ])
                ->where(function ($query) use ($searchDay, $samityIdsFromSamityDayChnages) {
                    $query->where('samityDay', $searchDay)
                        ->orWhereIn('id', $samityIdsFromSamityDayChnages);
                })
                ->where(function ($query) use ($date) {
                    $query->where('closingDate', '0000-00-00')
                        ->orWhere('closingDate', '>', $date);
                })
                ->select(DB::raw("id, CONCAT(samityCode, ' - ', name) AS samityName, samityDay"))
                ->get();

            // update the samity day according to the samity day chnage history
            foreach ($samityDayChanges as $key => $samityDayChange) {
                $samityies->where('id', $samityDayChange->samityId)->first()->samityDay = $samityDayChange->oldSamityDay;
            }

            // again filter the samities to the day
            $samityies = $samityies->where('samityDay');

            return response()->json($samityies);
        } elseif ($req->context == 'product_cat') {
            $products = DB::table('mfn_loan_products')
                ->where([
                    ['is_delete', 0],
                    ['productCategoryId', $req->productCategoryId],
                ])
                ->select(DB::raw("id, CONCAT(productCode, ' - ', name) AS name"))
                ->get();

            return response()->json($products);
        }
    }
}
