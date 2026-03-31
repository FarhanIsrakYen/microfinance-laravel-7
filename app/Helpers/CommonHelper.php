<?php

namespace App\Helpers;

use App\Model\GNL\Branch;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Session;

class CommonHelper
{
  
    /**
     * Get Company ID from user login session
     */
    public static function getCompanyId()
    {
        $companyID = Session::get('LoginBy.user_config.company_id');

        if ($companyID == '' || $companyID == null || empty($companyID)) {
            $companyID = 1;
        }

        return $companyID;
    }

    /**
     * Get Branch ID from user login session
     */
    public static function getBranchId()
    {
        $branchID = Session::get('LoginBy.user_config.branch_id');

        if ($branchID == '' || $branchID == null || empty($branchID)) {
            $branchID = 1;
        }

        return $branchID;
    }

    /**
     * Get System Date Depending on Day End
     */
    public static function systemCurrentDate()
    {
        $branchID = self::getBranchId();

        $DayEndModel = 'App\\Model\\POS\\DayEnd';
        $BranchModel = 'App\\Model\\GNL\\Branch';

        $CurrentDate = new DateTime();

        $DayEndData = $DayEndModel::where(['branch_id' => $branchID, 'is_active' => 1])->first();

        if (empty($DayEndData)) {
            if ($branchID > 1) {
                $BranchData = $BranchModel::where(['id' => $branchID, 'is_approve' => 1])->get();

                if ($BranchData) {
                    if (!empty($BranchData->soft_start_date)) {
                        $CurrentDate = new DateTime($BranchData->soft_start_date);
                    }
                }
            }
        } else {
            $CurrentDate = new DateTime($DayEndData->branch_date);
        }

        $CurrentDate = $CurrentDate->format('d-m-Y');

        return $CurrentDate;
    }

   

    /*
    public static function systemMonthWorkingDay($startDate){
    $branchID = self::getBranchId();
    $companyID = self::getCompanyId();

    $govtHolidayModel = 'App\\Model\\HR\\GovtHoliday';
    $comapnyHolidayModel = 'App\\Model\\HR\\CompanyHoliday';
    $specialHolidayModel = 'App\\Model\\HR\\SpecialHoliday';

    $workingDays = array();

    $startDate = new DateTime($startDate);

    $firstDay = $startDate->format('d');
    $workingMonth = $startDate->format('m');
    $workingYear = $startDate->format('Y');

    // Count day of curent month
    $lastday = cal_days_in_month(CAL_GREGORIAN, $workingMonth, $workingYear);
    $endDate = $lastday . "-" . $workingMonth . "-" . $workingYear;
    $endDate = new DateTime($endDate);

    // Fixed Govt Holiday Check
    $govtHolidays = $govtHolidayModel::where('is_delete', 0)
    ->where('gh_date', 'LIKE', "%-{$workingMonth}")
    ->get();

    for ($i = $firstDay; $i <= $lastday; $i++) {
    $workdayFlag = true;

    $day = sprintf("%02d", $i);
    // $tempDateM = $day . "-" . $workingMonth . "-" . $workingYear;
    $tempDateM = $workingYear . "-" . $workingMonth . "-" . $day;
    $tempDate = new DateTime($tempDateM);

    foreach ($govtHolidays as $govtHoliday) {
    if ($govtHoliday->gh_date == $day . "-" . $workingMonth) {
    // echo "Govt: ".$i."<br>";
    $workdayFlag = false;
    }
    }

    if ($workdayFlag) {
    // This is Full day name
    $dayName = $tempDate->format('l');
    $tempDateDBF = $tempDate->format('Y-m-d');

    // Company Holiday Check
    $companyArr = (!empty($companyID)) ? ['company_id', '=', $companyID] : ['company_id', '<>', ''];
    $companyHoliday = $comapnyHolidayModel::where(['is_delete' => 0])
    ->where('ch_eff_date', '<=', $tempDateDBF)
    ->where([$companyArr])
    ->where(function ($companyHoliday) use ($dayName) {
    $companyHoliday->where('ch_day', 'LIKE', "{$dayName}")
    ->orWhere('ch_day', 'LIKE', "%,{$dayName},%")
    ->orWhere('ch_day', 'LIKE', "%,{$dayName}%")
    ->orWhere('ch_day', 'LIKE', "%{$dayName},%");
    })
    ->count();

    if ($companyHoliday > 0) {
    // echo "comp: ".$i."<br>";
    $workdayFlag = false;
    } else {

    $specialHolidayORG = $specialHolidayModel::where(['sh_app_for' => 'org', 'is_delete' => 0])
    ->where('sh_date_from', '<=', $tempDateDBF)
    ->where('sh_date_to', '>=', $tempDateDBF)
    ->count();

    if ($specialHolidayORG > 0) {
    // echo "Spcl ORG: ".$i."<br>";
    $workdayFlag = false;
    } else {
    $specialHolidayBranch = $specialHolidayModel::where(['sh_app_for' => 'branch', 'is_delete' => 0])
    ->where('branch_id', '=', $branchID)
    ->where('sh_date_from', '<=', $tempDateDBF)
    ->where('sh_date_to', '>=', $tempDateDBF)
    ->count();

    if ($specialHolidayBranch > 0) {
    // echo "Spcl Bra: ".$i."<br>";
    $workdayFlag = false;
    }
    }
    }
    }

    if ($workdayFlag) {
    array_push($workingDays, $tempDateM);
    }
    }

    return $workingDays;
    }
     */

   

    /**
     * Get system Holidays
     * @param companyID @type int
     * @param branchID @type int
     * @param somityID @type int
     * @param startDate @type string '02-02-2020' or '2020-02-02'
     * @param endDate @type string '02-02-2020' or '2020-02-02'
     * @param period @type string '2 day' or '2 month' or '2 year'
     *
     * @condition
     * startDate != null && endDate != null && period == null
     * startDate != null && endDate == null && period != null  (Auto calculate last day depend on period(+))
     * startDate == null && endDate != null && period != null (Auto calculate first day depend on period(-))
     * startDate == null && endDate == null && period != null (Get System Current date as first day & last day calculate depend on period(+))
     * Calling: Common::systemHolidays(companyID,branchID,somityID,startDate,endDate, period)
     */

    public static function systemHolidays($companyID = null, $branchID = null, $somityID = null, $startDate = null, $endDate = null, $period = null)
    {
        $companyID = (!empty($companyID)) ? $companyID : self::getBranchId();
        $branchID = (!empty($branchID)) ? $branchID : self::getBranchId();
        $somityID = (!empty($somityID)) ? $somityID : 1;

        $companyID = (!empty($companyID)) ? $companyID : 1;

        $govtHolidayModel = 'App\\Model\\HR\\GovtHoliday';
        $comapnyHolidayModel = 'App\\Model\\HR\\CompanyHoliday';
        $specialHolidayModel = 'App\\Model\\HR\\SpecialHoliday';

        $fromDate = null;
        $toDate = null;

        if ($startDate == '') {
            $startDate = null;
        }

        if ($endDate == '') {
            $endDate = null;
        }

        if ($period == '') {
            $period = null;
        }

        if (!empty($startDate) && !empty($endDate)) {
            $fromDate = new DateTime($startDate);
            $toDate = new DateTime($endDate);
        } elseif (!empty($startDate) && empty($endDate) && !empty($period)) {
            $fromDate = new DateTime($startDate);
            $tempDate = clone $fromDate;
            $toDate = $tempDate->modify('+' . $period);
        } elseif (empty($startDate) && !empty($endDate) && !empty($period)) {
            $toDate = new DateTime($endDate);
            $tempDate = clone $toDate;
            $fromDate = $tempDate->modify('-' . $period);
        } elseif (empty($startDate) && empty($endDate) && !empty($period)) {
            $fromDate = new DateTime(self::systemCurrentDate());
            $tempDate = clone $fromDate;
            $toDate = $tempDate->modify('+' . $period);
        }

        $holiDays = array();

        if (!empty($fromDate) && !empty($toDate)) {

            // Fixed Govt Holiday Query
            $govtHolidays = $govtHolidayModel::where([['is_delete', 0], ['is_active', 1]])
                ->select('id', 'gh_title', 'gh_date')
                ->get();
            $fixedGovtHoliday = (count($govtHolidays->toarray()) > 0) ? $govtHolidays->toarray() : array();

            // Company Holiday Query
            $companyArr = (!empty($companyID)) ? ['company_id', '=', $companyID] : ['company_id', '<>', ''];
            $companyHolidayQuery = $comapnyHolidayModel::where([['is_delete', 0], ['is_active', 1]])
                ->select('id', 'company_id', 'ch_title', 'ch_day', 'ch_eff_date')
                ->where([$companyArr])
                ->get();
            $companyHolidays = (count($companyHolidayQuery->toarray()) > 0) ? $companyHolidayQuery->toarray() : array();

            // Special Holiday for Organization Query
            $specialHolidayORGQuery = $specialHolidayModel::where([['is_delete', 0], ['is_active', 1], ['sh_app_for', 'org']])
                ->select('id', 'company_id', 'branch_id', 'sh_title', 'sh_app_for', 'sh_date_from', 'sh_date_to')
                ->get();

            $sHolidaysORG = (count($specialHolidayORGQuery->toarray()) > 0) ? $specialHolidayORGQuery->toarray() : array();

            // Special Holiday for Branch Query
            $specialHolidayBrQuery = $specialHolidayModel::where([['is_delete', 0], ['is_active', 1], ['sh_app_for', 'branch']])
                ->where('branch_id', '=', $branchID)
                ->select('id', 'company_id', 'branch_id', 'sh_title', 'sh_app_for', 'sh_date_from', 'sh_date_to')
                ->get();

            $sHolidaysBr = (count($specialHolidayBrQuery->toarray()) > 0) ? $specialHolidayBrQuery->toarray() : array();

            //  // Special Holiday for Somity Query
            //  $specialHolidaySQuery = $specialHolidayModel::where([['is_delete', 0], ['is_active', 1], ['sh_app_for', 'branch']])
            //  ->where('branch_id', '=', $branchID)
            //  ->select('id','company_id','branch_id','sh_title','sh_app_for','sh_date_from','sh_date_to')
            //  ->get();

            $sHolidaysBr = (count($specialHolidayBrQuery->toarray()) > 0) ? $specialHolidayBrQuery->toarray() : array();

            $tempLoopDate = clone $fromDate;
            while ($tempLoopDate <= $toDate) {
                $holiDayFlag = false;

                // Fixed Govt Holiday Check
                foreach ($fixedGovtHoliday as $RowFG) {
                    if ($RowFG['gh_date'] == $tempLoopDate->format('d-m')) {
                        $holiDayFlag = true;
                    }
                }

                // Company Holiday Check
                if ($holiDayFlag == false) {
                    foreach ($companyHolidays as $RowC) {
                        $ch_day = $RowC['ch_day'];

                        $ch_day_arr = explode(',', $RowC['ch_day']);
                        $ch_eff_date = new DateTime($RowC['ch_eff_date']);

                        // This is Full day name
                        $dayName = $tempLoopDate->format('l');

                        if (in_array($dayName, $ch_day_arr) && ($ch_eff_date <= $tempLoopDate)) {
                            $holiDayFlag = true;
                        }
                    }
                }

                // Special Holiday Org check
                if ($holiDayFlag == false) {
                    foreach ($sHolidaysORG as $RowO) {
                        $sh_date_from = new DateTime($RowO['sh_date_from']);
                        $sh_date_to = new DateTime($RowO['sh_date_to']);

                        if (($sh_date_from <= $tempLoopDate) && ($sh_date_to >= $tempLoopDate)) {
                            $holiDayFlag = true;
                        }
                    }
                }

                // Special Holiday Branch check
                if ($holiDayFlag == false) {
                    foreach ($sHolidaysBr as $RowB) {
                        $sh_date_from_b = new DateTime($RowB['sh_date_from']);
                        $sh_date_to_b = new DateTime($RowB['sh_date_to']);

                        if (($sh_date_from_b <= $tempLoopDate) && ($sh_date_to_b >= $tempLoopDate)) {
                            $holiDayFlag = true;
                        }
                    }
                }

                if ($holiDayFlag == true) {
                    array_push($holiDays, $tempLoopDate->format('Y-m-d'));
                }
                $tempLoopDate = $tempLoopDate->modify('+1 day');
            }
        }

        return $holiDays;
    }

    public static function installmentSchedule_new($companyID = null, $branchID = null, $somityID = null,
        $salesDate = null, $instType = null, $instMonth = null) {
        $govtHolidayModel = 'App\\Model\\HR\\GovtHoliday';
        $comapnyHolidayModel = 'App\\Model\\HR\\CompanyHoliday';
        $specialHolidayModel = 'App\\Model\\HR\\SpecialHoliday';

        $companyID = (!empty($companyID)) ? $companyID : self::getBranchId();
        $branchID = (!empty($branchID)) ? $branchID : self::getBranchId();
        $somityID = (!empty($somityID)) ? $somityID : 1;
        $companyID = (!empty($companyID)) ? $companyID : 1;

        $instCount = 0;
        $scheduleDays = array();

        // ///// This is for query
        if (!empty($salesDate) && !empty($instType) && !empty($instMonth)) {

            // Fixed Govt Holiday Query
            $govtHolidays = $govtHolidayModel::where([['is_delete', 0], ['is_active', 1]])
                ->select('id', 'gh_title', 'gh_date')
                ->get();
            $fixedGovtHoliday = (count($govtHolidays->toarray()) > 0) ? $govtHolidays->toarray() : array();

            // Company Holiday Query
            $companyArr = (!empty($companyID)) ? ['company_id', '=', $companyID] : ['company_id', '<>', ''];
            $companyHolidayQuery = $comapnyHolidayModel::where([['is_delete', 0], ['is_active', 1]])
                ->select('id', 'company_id', 'ch_title', 'ch_day', 'ch_eff_date')
                ->where([$companyArr])
                ->get();
            $companyHolidays = (count($companyHolidayQuery->toarray()) > 0) ? $companyHolidayQuery->toarray() : array();

            // Special Holiday for Organization Query
            $specialHolidayORGQuery = $specialHolidayModel::where([['is_delete', 0], ['is_active', 1], ['sh_app_for', 'org']])
                ->select('id', 'company_id', 'branch_id', 'sh_title', 'sh_app_for', 'sh_date_from', 'sh_date_to')
                ->get();

            $sHolidaysORG = (count($specialHolidayORGQuery->toarray()) > 0) ? $specialHolidayORGQuery->toarray() : array();

            // Special Holiday for Branch Query
            $specialHolidayBrQuery = $specialHolidayModel::where([['is_delete', 0], ['is_active', 1], ['sh_app_for', 'branch']])
                ->where('branch_id', '=', $branchID)
                ->select('id', 'company_id', 'branch_id', 'sh_title', 'sh_app_for', 'sh_date_from', 'sh_date_to')
                ->get();

            $sHolidaysBr = (count($specialHolidayBrQuery->toarray()) > 0) ? $specialHolidayBrQuery->toarray() : array();
        }

        if (!empty($salesDate) && !empty($instType) && !empty($instMonth)) {
            $fromDate = new DateTime($salesDate);

            ///////////////////////////////////// test ////////////////////////////
            $instType = 2;
            ///////////////////////////////////// test ////////////////////////////
            // $week = $tempLoopDate_n->format("W");

            if ($instType == 1) {
                // Month Type
                $instCount = (int) $instMonth;
            } else {
                // Week Type
                $tempDate = clone $fromDate;
                $toDate = $tempDate->modify('+' . ($instMonth - 1) . ' month');

                $tempLoopDate_c = clone $fromDate;
                while ($tempLoopDate_c <= $toDate) {
                    $instCount++;
                    $tempLoopDate_c = $tempLoopDate_c->modify('+1 week');
                }
            }

            $loopCount = $instCount;
            $tempStartDate = clone $fromDate;
            $tempSD = $fromDate->format('d');

            while ($loopCount > 0) {
                // echo $loopCount."<br>";
                $loopCount--;

                $holidayFlag = true;
                $tempLoopDate = clone $tempStartDate;
                ////////// while loop
                array_push($scheduleDays, $tempLoopDate->format('Y-m-d'));

                if ($instType == 1) {
                    $tempStartDate = new DateTime($tempSD . "-" . $tempLoopDate->format('m-Y'));
                    $tempStartDate = $tempStartDate->modify('+1 month');
                } else {
                    // $tempStartDate = new DateTime($tempSD."-".$tempLoopDate->format('m-Y'));
                    $tempStartDate = $tempStartDate->modify('+1 week');
                }
            }

            dd($scheduleDays);

        }

        dd(1);

        $fromDate = null;
        $toDate = null;

        // $scheduleDays = array();
        $tempScheduleDays = array();

        if (!empty($salesDate) && !empty($instMonth)) {

            $fromDate = new DateTime($salesDate);
            $tempDate = clone $fromDate;
            $toDate = $tempDate->modify('+' . ($instMonth - 1) . ' month');
        }

        dd(1);

        if (!empty($fromDate) && !empty($toDate) && !empty($instType)) {

            ///////////////////////////////////// test ////////////////////////////
            // $instType = 2;
            ///////////////////////////////////// test ////////////////////////////
            // $week = $tempLoopDate_n->format("W");

            if ($instType == 1) {
                // Month Type
                $tempLoopDate_n = clone $fromDate;
                while ($tempLoopDate_n <= $toDate) {
                    // array_push($tempScheduleDays, $tempLoopDate_n->format('Y-m-d'));
                    array_push($tempScheduleDays, clone $tempLoopDate_n);
                    $tempLoopDate_n = $tempLoopDate_n->modify('+1 month');
                }
            } elseif ($instType == 2) {
                // Week Type
                $tempLoopDate_n = clone $fromDate;
                while ($tempLoopDate_n <= $toDate) {
                    // array_push($tempScheduleDays, $tempLoopDate_n->format('Y-m-d'));
                    array_push($tempScheduleDays, clone $tempLoopDate_n);
                    $tempLoopDate_n = $tempLoopDate_n->modify('+1 week');
                }
            }

            // dd($tempScheduleDays);

            foreach ($tempScheduleDays as $tempRow) {

                $holidayFlag = true;
                $tempLoopDate = clone $tempRow;

                while ($holidayFlag == true) {

                    $holidayFlag = false;

                    // Fixed Govt Holiday Check
                    foreach ($fixedGovtHoliday as $RowFG) {
                        if ($RowFG['gh_date'] == $tempLoopDate->format('d-m')) {
                            $holidayFlag = true;
                        }
                    }

                    // Company Holiday Check
                    if ($holidayFlag == false) {
                        foreach ($companyHolidays as $RowC) {
                            $ch_day = $RowC['ch_day'];

                            $ch_day_arr = explode(',', $RowC['ch_day']);
                            $ch_eff_date = new DateTime($RowC['ch_eff_date']);

                            // This is Full day name
                            $dayName = $tempLoopDate->format('l');

                            if (in_array($dayName, $ch_day_arr) && ($ch_eff_date <= $tempLoopDate)) {
                                $holidayFlag = true;
                            }
                        }
                    }

                    // Special Holiday Org check
                    if ($holidayFlag == false) {
                        foreach ($sHolidaysORG as $RowO) {
                            $sh_date_from = new DateTime($RowO['sh_date_from']);
                            $sh_date_to = new DateTime($RowO['sh_date_to']);

                            if (($sh_date_from <= $tempLoopDate) && ($sh_date_to >= $tempLoopDate)) {
                                $holidayFlag = true;
                            }
                        }
                    }

                    // Special Holiday Branch check
                    if ($holidayFlag == false) {
                        foreach ($sHolidaysBr as $RowB) {
                            $sh_date_from_b = new DateTime($RowB['sh_date_from']);
                            $sh_date_to_b = new DateTime($RowB['sh_date_to']);

                            if (($sh_date_from_b <= $tempLoopDate) && ($sh_date_to_b >= $tempLoopDate)) {
                                $holidayFlag = true;
                            }
                        }
                    }

                    if ($holidayFlag == false) {
                        array_push($scheduleDays, $tempLoopDate->format('Y-m-d'));
                    } else {
                        // $tempCurMonth = $tempRow->format('m');
                        $tempLoopDate = $tempLoopDate->modify('+1 day');
                    }
                }
            }

            dd($scheduleDays);

        }

        return $scheduleDays;
    }

    public static function installmentSchedule($companyID = null, $branchID = null, $somityID = null,
        $salesDate = null, $instType = null, $instMonth = null) {
        $govtHolidayModel = 'App\\Model\\HR\\GovtHoliday';
        $comapnyHolidayModel = 'App\\Model\\HR\\CompanyHoliday';
        $specialHolidayModel = 'App\\Model\\HR\\SpecialHoliday';

        $companyID = (!empty($companyID)) ? $companyID : self::getBranchId();
        $branchID = (!empty($branchID)) ? $branchID : self::getBranchId();
        $somityID = (!empty($somityID)) ? $somityID : 1;
        $companyID = (!empty($companyID)) ? $companyID : 1;

        $fromDate = null;
        $toDate = null;
        $instCount = 0;

        $scheduleDays = array();
        $tempScheduleDays = array();

        if (!empty($salesDate) && !empty($instMonth)) {

            $fromDate = new DateTime($salesDate);
            $tempDate = clone $fromDate;
            $toDate = $tempDate->modify('+' . ($instMonth - 1) . ' month');
        }

        // ///// This is for query
        if (!empty($fromDate) && !empty($toDate)) {

            // Fixed Govt Holiday Query
            $govtHolidays = $govtHolidayModel::where([['is_delete', 0], ['is_active', 1]])
                ->select('id', 'gh_title', 'gh_date')
                ->get();
            $fixedGovtHoliday = (count($govtHolidays->toarray()) > 0) ? $govtHolidays->toarray() : array();

            // Company Holiday Query
            $companyArr = (!empty($companyID)) ? ['company_id', '=', $companyID] : ['company_id', '<>', ''];
            $companyHolidayQuery = $comapnyHolidayModel::where([['is_delete', 0], ['is_active', 1]])
                ->select('id', 'company_id', 'ch_title', 'ch_day', 'ch_eff_date')
                ->where([$companyArr])
                ->get();
            $companyHolidays = (count($companyHolidayQuery->toarray()) > 0) ? $companyHolidayQuery->toarray() : array();

            // Special Holiday for Organization Query
            $specialHolidayORGQuery = $specialHolidayModel::where([['is_delete', 0], ['is_active', 1], ['sh_app_for', 'org']])
                ->select('id', 'company_id', 'branch_id', 'sh_title', 'sh_app_for', 'sh_date_from', 'sh_date_to')
                ->get();

            $sHolidaysORG = (count($specialHolidayORGQuery->toarray()) > 0) ? $specialHolidayORGQuery->toarray() : array();

            // Special Holiday for Branch Query
            $specialHolidayBrQuery = $specialHolidayModel::where([['is_delete', 0], ['is_active', 1], ['sh_app_for', 'branch']])
                ->where('branch_id', '=', $branchID)
                ->select('id', 'company_id', 'branch_id', 'sh_title', 'sh_app_for', 'sh_date_from', 'sh_date_to')
                ->get();

            $sHolidaysBr = (count($specialHolidayBrQuery->toarray()) > 0) ? $specialHolidayBrQuery->toarray() : array();
        }

        if (!empty($fromDate) && !empty($toDate) && !empty($instType)) {

            ///////////////////////////////////// test ////////////////////////////
            // $instType = 2;
            ///////////////////////////////////// test ////////////////////////////
            // $week = $tempLoopDate_n->format("W");

            if ($instType == 1) {
                // Month Type
                $tempLoopDate_n = clone $fromDate;
                while ($tempLoopDate_n <= $toDate) {
                    // array_push($tempScheduleDays, $tempLoopDate_n->format('Y-m-d'));
                    array_push($tempScheduleDays, clone $tempLoopDate_n);
                    $tempLoopDate_n = $tempLoopDate_n->modify('+1 month');
                }
            } elseif ($instType == 2) {
                // Week Type
                $tempLoopDate_n = clone $fromDate;
                while ($tempLoopDate_n <= $toDate) {
                    // array_push($tempScheduleDays, $tempLoopDate_n->format('Y-m-d'));
                    array_push($tempScheduleDays, clone $tempLoopDate_n);
                    $tempLoopDate_n = $tempLoopDate_n->modify('+1 week');
                }
            }

            // dd($tempScheduleDays);

            foreach ($tempScheduleDays as $tempRow) {

                $holidayFlag = true;
                $tempLoopDate = clone $tempRow;

                while ($holidayFlag == true) {

                    $holidayFlag = false;

                    // Fixed Govt Holiday Check
                    foreach ($fixedGovtHoliday as $RowFG) {
                        if ($RowFG['gh_date'] == $tempLoopDate->format('d-m')) {
                            $holidayFlag = true;
                        }
                    }

                    // Company Holiday Check
                    if ($holidayFlag == false) {
                        foreach ($companyHolidays as $RowC) {
                            $ch_day = $RowC['ch_day'];

                            $ch_day_arr = explode(',', $RowC['ch_day']);
                            $ch_eff_date = new DateTime($RowC['ch_eff_date']);

                            // This is Full day name
                            $dayName = $tempLoopDate->format('l');

                            if (in_array($dayName, $ch_day_arr) && ($ch_eff_date <= $tempLoopDate)) {
                                $holidayFlag = true;
                            }
                        }
                    }

                    // Special Holiday Org check
                    if ($holidayFlag == false) {
                        foreach ($sHolidaysORG as $RowO) {
                            $sh_date_from = new DateTime($RowO['sh_date_from']);
                            $sh_date_to = new DateTime($RowO['sh_date_to']);

                            if (($sh_date_from <= $tempLoopDate) && ($sh_date_to >= $tempLoopDate)) {
                                $holidayFlag = true;
                            }
                        }
                    }

                    // Special Holiday Branch check
                    if ($holidayFlag == false) {
                        foreach ($sHolidaysBr as $RowB) {
                            $sh_date_from_b = new DateTime($RowB['sh_date_from']);
                            $sh_date_to_b = new DateTime($RowB['sh_date_to']);

                            if (($sh_date_from_b <= $tempLoopDate) && ($sh_date_to_b >= $tempLoopDate)) {
                                $holidayFlag = true;
                            }
                        }
                    }

                    if ($holidayFlag == false) {
                        array_push($scheduleDays, $tempLoopDate->format('Y-m-d'));
                        // array_push($scheduleDays, clone $tempLoopDate);

                    } else {
                        // $tempCurMonth = $tempRow->format('m');
                        $tempLoopDate = $tempLoopDate->modify('+1 day');
                    }
                }
            }

            // dd($scheduleDays);

        }

        ///////////////////////////////////////////////////////////////
        // Incomplete function, check remain if full week holiday skip this week but schedule date count must be equal installment month or week
        // When month and week end and go to next week that case date modify minus day

        return $scheduleDays;
    }

    /**
     * Stock Count for Product
     */

    public static function stockQuantity($branchID, $ProductID, $returnArray = false, $startDate = null, $endDate = null)
    {
        /**
         * Algorithm Stock Count For H/O
         * Stock = OpeningBalance + Purchase - PurchaseReturn - Issue + IssueReturn +- Adjustment
         */
        /**
         * Algorithm Stock Count For Branch
         * Stock = OpeningBalance + Issue - IssueReturn + TransferIn - TransferOut - Sales + SalesReturn +- Adjustment
         */

        $fromDate = null;
        $toDate = null;

        if ($startDate == '') {
            $startDate = null;
        }

        if ($endDate == '') {
            $endDate = null;
        }

        if (!empty($startDate)) {
            $fromDate = new DateTime($startDate);
        }

        if (!empty($endDate)) {
            $toDate = new DateTime($endDate);
        } else {
            $toDate = new DateTime(self::systemCurrentDate());
        }

        if ($branchID >= 1 && !empty($ProductID)) {

            $Stock = 0;
            $PreOB = 0;
            $OpeningBalance = 0;
            $Purchase = 0;
            $PurchaseReturn = 0;
            $Issue = 0;
            $IssueReturn = 0;
            $TransferIn = 0;
            $TransferOut = 0;
            $Sales = 0;
            $SalesReturn = 0;
            $Adjustment = 0;


            /* Branch ID 1 for Head Office Branch */
            if ($branchID == 1) {

                $stockCount = DB::table('pos_products as p')
                ->select('p.id', 
                    DB::raw('IFNULL(SUM(obs.product_quantity), 0) as OpeningBalance, 
                        IFNULL(SUM(pd.product_quantity), 0) as Purchase,
                        IFNULL(SUM(prd.product_quantity), 0) as PurchaseReturn,
                        IFNULL(SUM(isd.product_quantity), 0) as Issue,
                        IFNULL(SUM(isrd.product_quantity), 0) as IssueReturn'))

                ->where([['p.is_active', 1], ['p.is_delete', 0], ['p.id', $ProductID]])

                ->leftjoin('pos_ob_stock_d as obs', function($stockCount) use ($fromDate, $toDate, $branchID){

                    $stockCount->on('p.id', '=', 'obs.product_id')
                        ->where([['obs.is_active', 1], ['obs.is_delete', 0], ['obs.branch_id', $branchID]])
                        ->where(function($stockCount) use ($fromDate, $toDate){

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('obs.opening_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('obs.opening_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('obs.opening_date', '<=', $toDate);
                            }
                        });
                })

                ->leftjoin('pos_purchases_d as pd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'pd.product_id')
                        ->where([['pd.is_active', 1], ['pd.is_delete', 0], ['pd.branch_id', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('pd.purchase_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('pd.purchase_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('pd.purchase_date', '<=', $toDate);
                            }
                        });
                })

                ->leftjoin('pos_purchases_r_d as prd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'prd.product_id')
                        ->where([['prd.is_active', 1], ['prd.is_delete', 0], ['prd.branch_id', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('prd.return_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('prd.return_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('prd.return_date', '<=', $toDate);
                            }
                        });
                })

                ->leftjoin('pos_issues_d as isd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'isd.product_id')
                        ->where([['isd.is_active', 1], ['isd.is_delete', 0], ['isd.branch_from', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('isd.issue_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('isd.issue_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('isd.issue_date', '<=', $toDate);
                            }
                        });
                })

                ->leftjoin('pos_issues_r_d as isrd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'isrd.product_id')
                        ->where([['isrd.is_active', 1], ['isrd.is_delete', 0], ['isrd.branch_to', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('isrd.return_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('isrd.return_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('isrd.return_date', '<=', $toDate);
                            }
                        });
                })
                ->groupBy('p.id')
                ->first();


                if ($stockCount) {
                    $OpeningBalance = $stockCount->OpeningBalance;
                    $Purchase = $stockCount->Purchase;
                    $PurchaseReturn = $stockCount->PurchaseReturn;
                    $Issue = $stockCount->Issue;
                    $IssueReturn = $stockCount->IssueReturn;
                }

                if (!empty($fromDate) && !empty($toDate)) {
                    
                    $tempDate = clone $fromDate;
                    $NewDate = $tempDate->modify('-1 day');
                    $PreOB = self::stockQuantity($branchID, $ProductID, false, null, $NewDate->format('Y-m-d'));
                }

                $Stock = ($PreOB + $OpeningBalance + $Purchase - $PurchaseReturn - $Issue + $IssueReturn + $Adjustment);

            }
            else{

                $stockCount = DB::table('pos_products as p')
                ->select('p.id', 
                    DB::raw('IFNULL(SUM(obs.product_quantity), 0) as OpeningBalance, 
                        IFNULL(SUM(pd.product_quantity), 0) as Purchase,
                        IFNULL(SUM(prd.product_quantity), 0) as PurchaseReturn,
                        IFNULL(SUM(isd.product_quantity), 0) as Issue,
                        IFNULL(SUM(isrd.product_quantity), 0) as IssueReturn,
                        IFNULL(SUM(tin.product_quantity), 0) as TransferIn,
                        IFNULL(SUM(tout.product_quantity), 0) as TransferOut,
                        IFNULL(SUM(sd.product_quantity), 0) as Sales,
                        IFNULL(SUM(srd.product_quantity), 0) as SalesReturn'))

                ->where([['p.is_active', 1], ['p.is_delete', 0], ['p.id', $ProductID]])

                ->leftjoin('pos_ob_stock_d as obs', function($stockCount) use ($fromDate, $toDate, $branchID){

                    $stockCount->on('p.id', '=', 'obs.product_id')
                        ->where([['obs.is_active', 1], ['obs.is_delete', 0], ['obs.branch_id', $branchID]])
                        ->where(function($stockCount) use ($fromDate, $toDate){

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('obs.opening_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('obs.opening_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('obs.opening_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_purchases_d as pd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'pd.product_id')
                        ->where([['pd.is_active', 1], ['pd.is_delete', 0], ['pd.branch_id', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('pd.purchase_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('pd.purchase_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('pd.purchase_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_purchases_r_d as prd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'prd.product_id')
                        ->where([['prd.is_active', 1], ['prd.is_delete', 0], ['prd.branch_id', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('prd.return_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('prd.return_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('prd.return_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_issues_d as isd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'isd.product_id')
                        ->where([['isd.is_active', 1], ['isd.is_delete', 0], ['isd.branch_to', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('isd.issue_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('isd.issue_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('isd.issue_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_issues_r_d as isrd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'isrd.product_id')
                        ->where([['isrd.is_active', 1], ['isrd.is_delete', 0], ['isrd.branch_from', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('isrd.return_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('isrd.return_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('isrd.return_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_transfers_d as tin', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'tin.product_id')
                        ->where([['tin.is_active', 1], ['tin.is_delete', 0], ['tin.branch_to', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('tin.transfer_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('tin.transfer_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('tin.transfer_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_transfers_d as tout', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'tout.product_id')
                        ->where([['tout.is_active', 1], ['tout.is_delete', 0], ['tout.branch_from', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('tout.transfer_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('tout.transfer_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('tout.transfer_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_sales_d as sd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'sd.product_id')
                        ->where([['sd.is_active', 1], ['sd.is_delete', 0], ['sd.branch_id', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('sd.sales_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('sd.sales_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('sd.sales_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_sales_return_d as srd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'srd.product_id')
                        ->where([['srd.is_active', 1], ['srd.is_delete', 0], ['srd.branch_id', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('srd.return_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('srd.return_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('srd.return_date', '<=', $toDate);
                            }
                        });
                })
                ->groupBy('p.id')
                ->first();



                if ($stockCount) {
                    $OpeningBalance = $stockCount->OpeningBalance;
                    $Purchase = $stockCount->Purchase;
                    $PurchaseReturn = $stockCount->PurchaseReturn;
                    $Issue = $stockCount->Issue;
                    $IssueReturn = $stockCount->IssueReturn;
                    $TransferIn = $stockCount->TransferIn;
                    $TransferOut =$stockCount->TransferOut;
                    $Sales = $stockCount->Sales;
                    $SalesReturn =$stockCount->SalesReturn;

                }

                if (!empty($fromDate) && !empty($toDate)) {
                    
                    $tempDate = clone $fromDate;
                    $NewDate = $tempDate->modify('-1 day');
                    $PreOB = self::stockQuantity($branchID, $ProductID, false, null, $NewDate->format('Y-m-d'));
                }

                $Stock = ($PreOB + $OpeningBalance + $Purchase - $PurchaseReturn + $Issue - $IssueReturn + $TransferIn - $TransferOut - $Sales + $SalesReturn + $Adjustment);

                
            }

            if ($returnArray) {
                $stockDetails = array();

                $stockDetails = [
                    'Stock' => $Stock,
                    'PreOB' => $PreOB,
                    'OpeningBalance' => $OpeningBalance + $PreOB,
                    'Purchase' => $Purchase,
                    'PurchaseReturn' => $PurchaseReturn,
                    'Issue' => $Issue,
                    'IssueReturn' => $IssueReturn,
                    'TransferIn' => $TransferIn,
                    'TransferOut' => $TransferOut,
                    'Sales' => $Sales,
                    'SalesReturn' => $SalesReturn,
                    'Adjustment' => $Adjustment,
                ];

                return $stockDetails;

            } else {
                return $Stock;
            }
        } 
        else {
            return "Error";
        }
    }

    public static function stockQuantityMultiple($branchID, $ProductID = [], $returnArray = false, $startDate = null, $endDate = null)
    {
        /**
         * Algorithm Stock Count For H/O
         * Stock = OpeningBalance + Purchase - PurchaseReturn - Issue + IssueReturn +- Adjustment
         */
        /**
         * Algorithm Stock Count For Branch
         * Stock = OpeningBalance + Issue - IssueReturn + TransferIn - TransferOut - Sales + SalesReturn +- Adjustment
         */

        $fromDate = null;
        $toDate = null;

        if ($startDate == '') {
            $startDate = null;
        }

        if ($endDate == '') {
            $endDate = null;
        }

        if (!empty($startDate)) {
            $fromDate = new DateTime($startDate);
        }

        if (!empty($endDate)) {
            $toDate = new DateTime($endDate);
        } else {
            $toDate = new DateTime(self::systemCurrentDate());
        }

        if ($branchID >= 1 && !empty($ProductID)) {

            $Stock = 0;
            $PreOB = 0;
            $OpeningBalance = 0;
            $Purchase = 0;
            $PurchaseReturn = 0;
            $Issue = 0;
            $IssueReturn = 0;
            $TransferIn = 0;
            $TransferOut = 0;
            $Sales = 0;
            $SalesReturn = 0;
            $Adjustment = 0;
            $stockArr = array();

            $AllStockArray = array();

            // dd($ProductID);

            /* Branch ID 1 for Head Office Branch */
            if ($branchID == 1) {

                $stockCount = DB::table('pos_products as p')
                ->select('p.id', 
                    DB::raw('IFNULL(SUM(obs.product_quantity), 0) as OpeningBalance, 
                        IFNULL(SUM(pd.product_quantity), 0) as Purchase,
                        IFNULL(SUM(prd.product_quantity), 0) as PurchaseReturn,
                        IFNULL(SUM(isd.product_quantity), 0) as Issue,
                        IFNULL(SUM(isrd.product_quantity), 0) as IssueReturn'))

                ->where([['p.is_active', 1], ['p.is_delete', 0]])
                ->whereIn('p.id', $ProductID)

                ->leftjoin('pos_ob_stock_d as obs', function($stockCount) use ($fromDate, $toDate, $branchID){

                    $stockCount->on('p.id', '=', 'obs.product_id')
                        ->where([['obs.is_active', 1], ['obs.is_delete', 0], ['obs.branch_id', $branchID]])
                        ->where(function($stockCount) use ($fromDate, $toDate){

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('obs.opening_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('obs.opening_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('obs.opening_date', '<=', $toDate);
                            }
                        });
                })

                ->leftjoin('pos_purchases_d as pd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'pd.product_id')
                        ->where([['pd.is_active', 1], ['pd.is_delete', 0], ['pd.branch_id', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('pd.purchase_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('pd.purchase_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('pd.purchase_date', '<=', $toDate);
                            }
                        });
                })

                ->leftjoin('pos_purchases_r_d as prd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'prd.product_id')
                        ->where([['prd.is_active', 1], ['prd.is_delete', 0], ['prd.branch_id', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('prd.return_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('prd.return_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('prd.return_date', '<=', $toDate);
                            }
                        });
                })

                ->leftjoin('pos_issues_d as isd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'isd.product_id')
                        ->where([['isd.is_active', 1], ['isd.is_delete', 0], ['isd.branch_from', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('isd.issue_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('isd.issue_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('isd.issue_date', '<=', $toDate);
                            }
                        });
                })

                ->leftjoin('pos_issues_r_d as isrd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'isrd.product_id')
                        ->where([['isrd.is_active', 1], ['isrd.is_delete', 0], ['isrd.branch_to', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('isrd.return_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('isrd.return_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('isrd.return_date', '<=', $toDate);
                            }
                        });
                })
                ->groupBy('p.id')
                ->get();

                foreach ($stockCount as $row) {

                    $OpeningBalance = $row->OpeningBalance;
                    $Purchase = $row->Purchase;
                    $PurchaseReturn = $row->PurchaseReturn;
                    $Issue = $row->Issue;
                    $IssueReturn = $row->IssueReturn;

                    $Stock = ($OpeningBalance + $Purchase - $PurchaseReturn - $Issue + $IssueReturn + $Adjustment);

                    $stockArr[$row->id] = [
                        'Stock' => $Stock,
                        'PreOB' => $PreOB,
                        'OpeningBalance' => $OpeningBalance,
                        'Purchase' => $Purchase,
                        'PurchaseReturn' => $PurchaseReturn,
                        'Issue' => $Issue,
                        'IssueReturn' => $IssueReturn,
                        'TransferIn' => $TransferIn,
                        'TransferOut' => $TransferOut,
                        'Sales' => $Sales,
                        'SalesReturn' => $SalesReturn,
                        'Adjustment' => $Adjustment,
                    ];
                }

                // dd($stockArr);

                $PreOBArr = array();

                $AllStockArray = $stockArr;

                if (!empty($fromDate) && !empty($toDate)) {
                    
                    $tempDate = clone $fromDate;
                    $NewDate = $tempDate->modify('-1 day');

                    $PreOBArr = self::stockQuantityMultiple($branchID, $ProductID, false, null, $NewDate->format('Y-m-d'));


                    foreach (array_keys($stockArr + $PreOBArr) as $key) {

                        $AllStockArray[$key] = [
                            'Stock' => $stockArr[$key]['Stock'] + $PreOBArr[$key]['Stock'],
                            'PreOB' => $stockArr[$key]['PreOB'] + $PreOBArr[$key]['PreOB'],
                            'OpeningBalance' => $stockArr[$key]['OpeningBalance'] + $PreOBArr[$key]['OpeningBalance'],
                            'Purchase' => $stockArr[$key]['Purchase'] + $PreOBArr[$key]['Purchase'],
                            'PurchaseReturn' => $stockArr[$key]['PurchaseReturn'] + $PreOBArr[$key]['PurchaseReturn'],
                            'Issue' => $stockArr[$key]['Issue'] + $PreOBArr[$key]['Issue'],
                            'IssueReturn' => $stockArr[$key]['IssueReturn'] + $PreOBArr[$key]['IssueReturn'],
                            'TransferIn' => $stockArr[$key]['TransferIn'] + $PreOBArr[$key]['TransferIn'],
                            'TransferOut' => $stockArr[$key]['TransferOut'] + $PreOBArr[$key]['TransferOut'],
                            'Sales' => $stockArr[$key]['Sales'] + $PreOBArr[$key]['Sales'],
                            'SalesReturn' => $stockArr[$key]['TransferIn'] + $PreOBArr[$key]['TransferIn'],
                            'Adjustment' => $stockArr[$key]['Adjustment'] + $PreOBArr[$key]['Adjustment'],
                        ];
                    }
                }

            }
            else{

                $stockCount = DB::table('pos_products as p')
                ->select('p.id', 
                    DB::raw('IFNULL(SUM(obs.product_quantity), 0) as OpeningBalance, 
                        IFNULL(SUM(pd.product_quantity), 0) as Purchase,
                        IFNULL(SUM(prd.product_quantity), 0) as PurchaseReturn,
                        IFNULL(SUM(isd.product_quantity), 0) as Issue,
                        IFNULL(SUM(isrd.product_quantity), 0) as IssueReturn,
                        IFNULL(SUM(tin.product_quantity), 0) as TransferIn,
                        IFNULL(SUM(tout.product_quantity), 0) as TransferOut,
                        IFNULL(SUM(sd.product_quantity), 0) as Sales,
                        IFNULL(SUM(srd.product_quantity), 0) as SalesReturn'))

                ->where([['p.is_active', 1], ['p.is_delete', 0]])
                ->whereIn('p.id', $ProductID)

                ->leftjoin('pos_ob_stock_d as obs', function($stockCount) use ($fromDate, $toDate, $branchID){

                    $stockCount->on('p.id', '=', 'obs.product_id')
                        ->where([['obs.is_active', 1], ['obs.is_delete', 0], ['obs.branch_id', $branchID]])
                        ->where(function($stockCount) use ($fromDate, $toDate){

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('obs.opening_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('obs.opening_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('obs.opening_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_purchases_d as pd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'pd.product_id')
                        ->where([['pd.is_active', 1], ['pd.is_delete', 0], ['pd.branch_id', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('pd.purchase_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('pd.purchase_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('pd.purchase_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_purchases_r_d as prd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'prd.product_id')
                        ->where([['prd.is_active', 1], ['prd.is_delete', 0], ['prd.branch_id', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('prd.return_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('prd.return_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('prd.return_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_issues_d as isd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'isd.product_id')
                        ->where([['isd.is_active', 1], ['isd.is_delete', 0], ['isd.branch_to', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('isd.issue_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('isd.issue_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('isd.issue_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_issues_r_d as isrd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'isrd.product_id')
                        ->where([['isrd.is_active', 1], ['isrd.is_delete', 0], ['isrd.branch_from', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('isrd.return_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('isrd.return_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('isrd.return_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_transfers_d as tin', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'tin.product_id')
                        ->where([['tin.is_active', 1], ['tin.is_delete', 0], ['tin.branch_to', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('tin.transfer_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('tin.transfer_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('tin.transfer_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_transfers_d as tout', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'tout.product_id')
                        ->where([['tout.is_active', 1], ['tout.is_delete', 0], ['tout.branch_from', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('tout.transfer_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('tout.transfer_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('tout.transfer_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_sales_d as sd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'sd.product_id')
                        ->where([['sd.is_active', 1], ['sd.is_delete', 0], ['sd.branch_id', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('sd.sales_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('sd.sales_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('sd.sales_date', '<=', $toDate);
                            }
                        });
                })
                ->leftjoin('pos_sales_return_d as srd', function($stockCount) use ($fromDate, $toDate, $branchID){
                    $stockCount->on('p.id', '=', 'srd.product_id')
                        ->where([['srd.is_active', 1], ['srd.is_delete', 0], ['srd.branch_id', $branchID]])
                        ->where(function ($stockCount) use ($fromDate, $toDate) {

                            if (!empty($fromDate) && !empty($toDate)) {
                                $stockCount->whereBetween('srd.return_date', [$fromDate, $toDate]);
                            }

                            if (!empty($fromDate) && empty($toDate)) {
                                $stockCount->where('srd.return_date', '>=', $fromDate);
                            }

                            if (empty($fromDate) && !empty($toDate)) {
                                $stockCount->where('srd.return_date', '<=', $toDate);
                            }
                        });
                })
                ->groupBy('p.id')
                ->get();


                foreach ($stockCount as $row) {

                    $OpeningBalance = $row->OpeningBalance;
                    $Purchase = $row->Purchase;
                    $PurchaseReturn = $row->PurchaseReturn;
                    $Issue = $row->Issue;
                    $IssueReturn = $row->IssueReturn;
                    $TransferIn = $row->TransferIn;
                    $TransferOut =$row->TransferOut;
                    $Sales = $row->Sales;
                    $SalesReturn =$row->SalesReturn;


                    $Stock = ($OpeningBalance + $Purchase - $PurchaseReturn + $Issue - $IssueReturn + $TransferIn - $TransferOut - $Sales + $SalesReturn + $Adjustment);

                    $stockArr[$row->id] = [
                        'Stock' => $Stock,
                        'PreOB' => $PreOB,
                        'OpeningBalance' => $OpeningBalance,
                        'Purchase' => $Purchase,
                        'PurchaseReturn' => $PurchaseReturn,
                        'Issue' => $Issue,
                        'IssueReturn' => $IssueReturn,
                        'TransferIn' => $TransferIn,
                        'TransferOut' => $TransferOut,
                        'Sales' => $Sales,
                        'SalesReturn' => $SalesReturn,
                        'Adjustment' => $Adjustment,
                    ];
                }

                $PreOBArr = array();
                $AllStockArray = $stockArr;

                if (!empty($fromDate) && !empty($toDate)) {
                    
                    $tempDate = clone $fromDate;
                    $NewDate = $tempDate->modify('-1 day');

                    $PreOBArr = self::stockQuantityMultiple($branchID, $ProductID, false, null, $NewDate->format('Y-m-d'));


                    foreach (array_keys($stockArr + $PreOBArr) as $key) {

                        $AllStockArray[$key] = [
                            'Stock' => $stockArr[$key]['Stock'] + $PreOBArr[$key]['Stock'],
                            'PreOB' => $stockArr[$key]['PreOB'] + $PreOBArr[$key]['PreOB'],
                            'OpeningBalance' => $stockArr[$key]['OpeningBalance'] + $PreOBArr[$key]['OpeningBalance'],
                            'Purchase' => $stockArr[$key]['Purchase'] + $PreOBArr[$key]['Purchase'],
                            'PurchaseReturn' => $stockArr[$key]['PurchaseReturn'] + $PreOBArr[$key]['PurchaseReturn'],
                            'Issue' => $stockArr[$key]['Issue'] + $PreOBArr[$key]['Issue'],
                            'IssueReturn' => $stockArr[$key]['IssueReturn'] + $PreOBArr[$key]['IssueReturn'],
                            'TransferIn' => $stockArr[$key]['TransferIn'] + $PreOBArr[$key]['TransferIn'],
                            'TransferOut' => $stockArr[$key]['TransferOut'] + $PreOBArr[$key]['TransferOut'],
                            'Sales' => $stockArr[$key]['Sales'] + $PreOBArr[$key]['Sales'],
                            'SalesReturn' => $stockArr[$key]['TransferIn'] + $PreOBArr[$key]['TransferIn'],
                            'Adjustment' => $stockArr[$key]['Adjustment'] + $PreOBArr[$key]['Adjustment'],
                        ];
                    }
                }
                
            }

            return $AllStockArray;

        } 
        else {
            return "Error";
        }
    }

    public static function getCompayID()
    {
        return self::getCompanyId();
    }

    public static function fileUpload($img_path, $image, $image_type)
    {
        $image_name = hexdec(uniqid());
        $ext_img = strtolower($image->getClientOriginalExtension());
        $image_full_name = $image_type . $image_name . '.' . $ext_img;
        $image_url = $img_path . $image_full_name;

        // $isUpload = $image->move($img_path, $image_full_name);

        $isUpload = Storage::putFileAs('public/' . $img_path, $image, $image_full_name);

        if ($isUpload) {
            return 'storage/' . $image_url;
        } else {
            $notification = array(
                'message' => 'Unsuccessful to Insert',
                'alert-type' => 'error',
            );
            return Redirect()->back()->with($notification);
        }
    }

    public static function getWeekdayName()
    {
        $days = array(
            'Saturday' => 'Saturday',
            'Sunday' => 'Sunday',
            'Monday' => 'Monday',
            'Tuesday' => 'Tuesday',
            'Wednesday' => 'Wednesday',
            'Thursday' => 'Thursday',
            'Friday' => 'Friday',
        );

        return $days;
    }

    /* -------------------------------------------------------------------- generate bill start */

    public static function generateBillPurchase($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $PurchaseMasterT = 'App\\Model\\POS\\PurchaseMaster';

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        $PreBillNo = "PUR" . $BranchCode;

        $record = $PurchaseMasterT::select(['id', 'bill_no'])
            ->where('branch_id', $branchID)
            ->where('bill_no', 'LIKE', "{$PreBillNo}%")
            ->orderBy('bill_no', 'DESC')
            ->first();

        if ($record) {
            $OldBillNoA = explode($PreBillNo, $record->bill_no);
            $BillNo = $PreBillNo . sprintf("%07d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%07d", 1);
        }
        return $BillNo;
    }

    public static function generateBillPurchaseReturn($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $PurchaseReturnMasterT = 'App\\Model\\POS\\PurchaseReturnMaster';

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        $PreBillNo = "PR" . $BranchCode;

        $record = $PurchaseReturnMasterT::where('branch_id', $branchID)
            ->select(['id', 'bill_no'])
            ->where('bill_no', 'LIKE', "{$PreBillNo}%")
            ->orderBy('bill_no', 'DESC')
            ->first();

        if ($record) {
            $OldBillNoA = explode($PreBillNo, $record->bill_no);
            $BillNo = $PreBillNo . sprintf("%07d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%07d", 1);
        }

        return $BillNo;
    }

    public static function generateBillIssue($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $IssuemT = 'App\\Model\\POS\\Issuem';

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        $PreBillNo = "IS" . $BranchCode;

        $record = $IssuemT::where('branch_from', $branchID)
            ->select(['id', 'bill_no'])
            ->where('bill_no', 'LIKE', "{$PreBillNo}%")
            ->orderBy('bill_no', 'DESC')
            ->first();

        if ($record) {
            $OldBillNoA = explode($PreBillNo, $record->bill_no);
            $BillNo = $PreBillNo . sprintf("%07d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%07d", 1);
        }

        return $BillNo;
    }

    public static function generateBillIssueReturn($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $IssueReturnmT = 'App\\Model\\POS\\IssueReturnm';

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        $PreBillNo = "IR" . $BranchCode;

        $record = $IssueReturnmT::where('branch_from', $branchID)
            ->select(['id', 'bill_no'])
            ->where('bill_no', 'LIKE', "{$PreBillNo}%")
            ->orderBy('bill_no', 'DESC')
            ->first();

        if ($record) {
            $OldBillNoA = explode($PreBillNo, $record->bill_no);
            $BillNo = $PreBillNo . sprintf("%07d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%07d", 1);
        }

        return $BillNo;
    }

    public static function generateBillTransfer($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $ModelT = "App\\Model\\POS\\TransferMaster";

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        $PreBillNo = "TRA" . $BranchCode;

        $record = $ModelT::where('branch_from', $branchID)
            ->select(['id', 'bill_no'])
            ->where('bill_no', 'LIKE', "{$PreBillNo}%")
            ->orderBy('bill_no', 'DESC')
            ->first();

        if ($record) {
            $OldBillNoA = explode($PreBillNo, $record->bill_no);
            $BillNo = $PreBillNo . sprintf("%07d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%07d", 1);
        }

        return $BillNo;
    }

    public static function generateBillSales($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $ModelT = "App\\Model\\POS\\SalesMaster";

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        $ldate = date('Ym');

        $PreBillNo = "SL" . $BranchCode . $ldate;

        $record = $ModelT::select(['id', 'sales_bill_no'])
            ->where('branch_id', $branchID)
            ->where('sales_bill_no', 'LIKE', "{$PreBillNo}%")
            ->orderBy('sales_bill_no', 'DESC')
            ->first();

        if ($record) {
            $OldBillNoA = explode($PreBillNo, $record->sales_bill_no);
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
        }

        return $BillNo;
    }

    /* --------------------------------------------------------------------- generate bill End */

    /* Query Builder */

    public static function ViewTableOrder($table = null, $option = [], $select = [], $order = [])
    { //for all view
        $data = DB::table($table)
            ->select($select)
            ->where($option)
            ->orderBy($order[0], $order[1])
            ->get();

        return $data;
    }

    public static function ViewTableOrderIn($table = null, $option = [], $optionIn = [], $select = [], $order = [])
    { //for all view
        $data = DB::table($table)
            ->select($select)
            ->where($option)
            ->whereIn($optionIn[0], $optionIn[1])
            ->orderBy($order[0], $order[1])
            ->get();

        return $data;
    }

    public static function ViewTableJoinAll($table = null, $table2 = null, $option = [], $select = [], $joinOption = [])
    { //for all view
        $data = DB::table($table)
            ->select($select)
            ->leftjoin($table2, [$joinOption[0] => $joinOption[1]])
        // ['gnl_sys_menus.is_delete' => 0])
            ->where($option)
            ->get();

        return $data;
    }

    public static function ViewTableFirst($table = null, $option = [], $select = [])
    { //for all view
        $data = DB::table($table)
            ->select($select)
            ->where($option)
            ->first();
        return $data;
    }

    public static function ViewTableFirstIn($table = null, $option = [], $optionIn = [], $select = [])
    { //for all view
        $data = DB::table($table)
            ->select($select)
            ->where($option)
            ->whereIn($optionIn)
            ->first();
        return $data;
    }

    public static function ViewTableLast($table = null, $option = [], $select = [])
    { //for all view
        $data = DB::table($table)
            ->select($select)
            ->where($option)
            ->first();
        return $data;
    }

    public static function ViewTableLastIn($table = null, $option = [], $optionIn = [], $select = [])
    { //for all view
        $data = DB::table($table)
            ->select($select)
            ->where($option)
            ->whereIn($optionIn)
            ->first();
        return $data;
    }
   
  

    /*

    

public function ViewTableLast($table = NULL, $option = [], $select = []) { //for all view
$this->$table = TableRegistry::get($table);
$data = $this->$table->find()
->select($select)
->where($option)
->last();
return $data;
}

public function viewMax($table = NULL, $option = [], $counter = NULL) {  // search max count
$this->$table = TableRegistry::get($table);
$data = $this->$table->find()->where($option)->max($counter);
return ($data->$counter) + 1;
}

public function maxData($table = NULL, $option = [], $id = NULL) {  // search max data
$this->$table = TableRegistry::get($table);
$data = $this->$table->find()->where($option)->max($id);
return $data;
}

public function ViewTableLastLimit($table = NULL, $option = [], $select = []) { //for all view
$this->$table = TableRegistry::get($table);
$data = $this->$table->find()
->select($select)
->where($option)
->order(['OrderBy' => 'ASC'])
->last();
return $data;
}

public function ViewTableAll($table = NULL, $option = [], $select = []) { //for all view
$this->$table = TableRegistry::get($table);
$data = $this->$table->find()
->select($select)
->where($option)
->all();
return $data;
}

public function ViewTableOrder($table = NULL, $option = [], $select = [], $order = []) { //for all view
$this->$table = TableRegistry::get($table);
$data = $this->$table->find()
->select($select)
->where($option)
->order($order);
return $data;
}

public function ViewTableJoinAll($table = NULL, $table2 = NULL, $option = [], $select = [], $joinOption = []) { //for all view
$this->$table = TableRegistry::get($table);
$data = $this->$table->find()
->select($select)
->leftJoin($table2, $joinOption[1] . ' = ' . $joinOption[0])
->where($option)
->all();
return $data;
}

public function ViewTableJoinLast($table = NULL, $table2 = NULL, $option = [], $select = [], $joinOption = []) { //for all view
$this->$table = TableRegistry::get($table);
$data = $this->$table->find()
->select($select)
->leftJoin($table2, $joinOption[1] . ' = ' . $joinOption[0])
->where($option)
->last();
return $data;
}

 */
}
