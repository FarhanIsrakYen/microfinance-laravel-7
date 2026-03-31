<?php

namespace App\Services;

use App\Model\GNL\Branch;
use App\Model\POS\Product;
use App\Services\CommonService as Common;
use DateTime;
use Illuminate\Support\Facades\DB;

class PosService
{

/** start Schedule function  */
    public static function installmentSchedule($companyID = null, $branchID = null, $somityID = null,
        $salesDate = null, $instType = null, $instMonth = null) {

        $govtHolidayModel = 'App\\Model\\GNL\\GovtHoliday';
        $comapnyHolidayModel = 'App\\Model\\GNL\\CompanyHoliday';
        $specialHolidayModel = 'App\\Model\\GNL\\SpecialHoliday';

        $companyID = (!empty($companyID)) ? $companyID : Common::getCompanyId();
        $branchID = (!empty($branchID)) ? $branchID : Common::getBranchId();
        $somityID = (!empty($somityID)) ? $somityID : 1;
        $companyID = (!empty($companyID)) ? $companyID : 1;

        $fromDate = null;
        $actualToDate = null;
        $toDate = null;
        $instCount = 0;
        $instMonth = (int) $instMonth;
        $instWeek = 0;

        $scheduleDays = array();
        $tempScheduleDays = array();

        if (!empty($salesDate) && !empty($instMonth)) {

            $fromDate = new DateTime($salesDate);
            $tempDate = clone $fromDate;
            $actualTempDate = clone $fromDate;

            /*
             * Extra 2 month add kora hocche karon jodi
            kono date, week, month holiday te pore
            tahole add or remove kora jay
             */
            $actualToDate = $actualTempDate->modify('+' . ($instMonth) . ' month');
            $toDate = $tempDate->modify('+' . (($instMonth - 1) + 2) . ' month');

            ## Week Count from Two Dates
            /*
             * 1 Week = 60*60*24*7 = 604800
             */
            $dateDiff = strtotime($actualToDate->format('d-m-Y'), 0) - strtotime($fromDate->format('d-m-Y'), 0);
            $instWeek = (int) floor($dateDiff / 604800);

            // dd($fromDate, $actualToDate, $instWeek);

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
            // $week = $installmentDate->format("W");

            $firstInstallmentDay = $fromDate->format('d');
            $firstInstallmentMonth = $fromDate->format('m');
            $firstInstallmentYear = $fromDate->format('Y');

            if ($instType == 1) {
                // Month Type
                $installmentDate = clone $fromDate;
                array_push($tempScheduleDays, clone $installmentDate);

                while ($installmentDate <= $toDate) {

                    if ($firstInstallmentDay == '31' || $firstInstallmentDay == 31) {
                        $installmentDate = $installmentDate->modify('last day of next month');
                    } else if ($firstInstallmentDay == '30' || $firstInstallmentDay == 30
                        || $firstInstallmentDay == '29' || $firstInstallmentDay == 29) {

                        $tempNextMonth = clone $installmentDate;
                        $tempNextMonth = $tempNextMonth->modify('last day of next month');

                        if ($tempNextMonth->format('m') == '2' || $tempNextMonth->format('m') == '02'
                            || $tempNextMonth->format('m') == 2 || $tempNextMonth->format('m') == 02) {

                            $installmentDate = $installmentDate->modify('last day of next month');
                        } else {
                            $installmentDate = $installmentDate->modify('+1 month');
                        }
                    } else {
                        $installmentDate = $installmentDate->modify('+1 month');
                    }

                    array_push($tempScheduleDays, clone $installmentDate);
                }

            } elseif ($instType == 2) {
                // Week Type
                $installmentDate = clone $fromDate;
                while ($installmentDate <= $toDate) {
                    // array_push($tempScheduleDays, $installmentDate->format('Y-m-d'));
                    array_push($tempScheduleDays, clone $installmentDate);
                    $installmentDate = $installmentDate->modify('+1 week');
                }
            }

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

        if ($instType == 1) {
            if (count($scheduleDays) > $instMonth) {
                $countDiff = count($scheduleDays) - $instMonth;
                for ($r = 0; $r < $countDiff; $r++) {
                    array_pop($scheduleDays);
                }
            }
        } else if ($instType == 2) {
            if (count($scheduleDays) > $instWeek) {
                $countDiff = count($scheduleDays) - $instWeek;
                for ($r = 0; $r < $countDiff; $r++) {
                    array_pop($scheduleDays);
                }
            }
        }

        // dd($scheduleDays);

        return $scheduleDays;
    }

    public static function installmentSchedule_multiple($companyID = null, $branchArr = [], $branchDateTypeMonthArr = [], $somityArr = [])
    {
        // ## integer, ## string, ## array
        // if (gettype($branchDateTypeMonthArr) == "string") {
        //     $branchDateTypeMonthArr = unserialize($branchDateTypeMonthArr);
        // }

        // if (gettype($branchArr) == "string") {
        //     $branchArr = unserialize($branchArr);
        // }

        $allScheduleData = array();

        // // ## Concat data by @ (branch@salesdate@installmentType@installmentMonth)
        $scheduleFlag = (count($branchDateTypeMonthArr) > 0) ? true : false;

        // ## if sales date, type, month empty then return initial with empty array
        if ($scheduleFlag == false) {
            // return serialize($allScheduleData);
            return $allScheduleData;
        }

        $weekDayArr = [
            1 => 'Saturday',
            2 => 'Sunday',
            3 => 'Monday',
            4 => 'Tuesday',
            5 => 'Wednesday',
            6 => 'Thursday',
            7 => 'Friday',
        ];

        $companyID = (!empty($companyID)) ? $companyID : Common::getCompanyId();
        $branchArr = (count($branchArr) > 0) ? $branchArr : [Common::getBranchId()];
        $somityArr = (count($somityArr) > 0) ? $somityArr : [1];

        // ## ----------------------------------- Holiday Query Start
        if ($scheduleFlag) {
            /**
             * Collection theke Array Faster,
             * @current due report - 24-09-2020 porjonto sales ache 1714 ta,
             * @collection diye korle page load hote time ney 15.72s file size 9.2 kB
             * but query er data array te convert kore check korle seta time ney 3.21s only file size 9.2 kB
             * @single data pass korle file size 9.2kB load hote time ney 4.80s but multiple function seikhane 3.21s a load hoy
             * @test korte hole ai same function er ekta copy rakha ache old a seta test kore dekha jabe
             *
             */

            // // ## Fixed Govt Holiday Query
            $fixedGovtHoliday = DB::table('hr_holidays_govt')
                ->where([['is_delete', 0], ['is_active', 1]])
                ->select('id', 'gh_title', 'gh_date')
                ->get();
            // $fixedGovtHoliday = (count($fixedGovtHoliday->toarray()) > 0) ? $fixedGovtHoliday->toarray() : array();

            // // ## Company Holiday Query
            $companyHolidays = DB::table('hr_holidays_comp')
                ->where([['is_delete', 0], ['is_active', 1]])
                ->select('id', 'company_id', 'ch_title', 'ch_day', 'ch_eff_date')
                ->where(function ($companyHolidays) use ($companyID) {
                    if (!empty($companyID)) {
                        $companyHolidays->where('company_id', $companyID);
                    }
                })
                ->get();
            // $companyHolidays = (count($companyHolidays->toarray()) > 0) ? $companyHolidays->toarray() : array();

            // // ## Special Holiday for Organization Query
            $sHolidaysORG = DB::table('hr_holidays_special')
                ->where([['is_delete', 0], ['is_active', 1], ['sh_app_for', 'org']])
                ->select('id', 'company_id', 'branch_id', 'sh_title', 'sh_app_for', 'sh_date_from', 'sh_date_to')
                ->get();

            // $sHolidaysORG = (count($sHolidaysORG->toarray()) > 0) ? $sHolidaysORG->toarray() : array();

            // // ## Special Holiday for Branch Query
            $sHolidaysBr = DB::table('hr_holidays_special')
                ->where([['is_delete', 0], ['is_active', 1], ['sh_app_for', 'branch']])
                ->where(function ($sHolidaysBr) use ($branchArr) {
                    if (!empty($branchArr)) {
                        $sHolidaysBr->whereIn('branch_id', $branchArr);
                    }
                })
                ->select('id', 'company_id', 'branch_id', 'sh_title', 'sh_app_for', 'sh_date_from', 'sh_date_to')
                ->get();

            // $sHolidaysBr = (count($sHolidaysBr->toarray()) > 0) ? $sHolidaysBr->toarray() : array();

        }
        // ## ----------------------------------- End Holiday Query

        // // ## Schedule Make Start
        foreach ($branchDateTypeMonthArr as $passingValue) {

            // ## explode concat value
            $passingArr = explode('@', $passingValue);
            $branchID = (isset($passingArr[0]) && !empty($passingArr[0])) ? $passingArr[0] : null;
            $salesDate = (isset($passingArr[1]) && !empty($passingArr[1])) ? $passingArr[1] : null;
            $instType = (isset($passingArr[2]) && !empty($passingArr[2])) ? $passingArr[2] : null;
            $instMonth = (isset($passingArr[3]) && !empty($passingArr[3])) ? $passingArr[3] : null;
            // ## end explode

            // dd($passingValue);

            // // ## Start Process Make Schedule
            $fromDate = null;
            $actualToDate = null;
            $toDate = null;
            $instCount = 0;
            $instMonth = (int) $instMonth;
            $instWeek = 0;

            $scheduleDays = array();
            $tempScheduleDays = array();

            if (!empty($salesDate) && !empty($instMonth)) {

                $fromDate = new DateTime($salesDate);
                $tempDate = clone $fromDate;
                $actualTempDate = clone $fromDate;

                /*
                 * Extra 2 month add kora hocche karon jodi
                kono date, week, month holiday te pore
                tahole add or remove kora jay
                 */
                $actualToDate = $actualTempDate->modify('+' . ($instMonth) . ' month');
                $toDate = $tempDate->modify('+' . (($instMonth - 1) + 2) . ' month');

                ## Week Count from Two Dates
                /*
                 * 1 Week = 60*60*24*7 = 604800
                 */
                $dateDiff = strtotime($actualToDate->format('d-m-Y'), 0) - strtotime($fromDate->format('d-m-Y'), 0);
                $instWeek = (int) floor($dateDiff / 604800);

                // dd($fromDate, $actualToDate, $instWeek);
            }

            if (!empty($fromDate) && !empty($toDate) && !empty($instType)) {

                ///////////////////////////////////// test ////////////////////////////
                // $instType = 2;
                ///////////////////////////////////// test ////////////////////////////
                // $week = $installmentDate->format("W");

                $firstInstallmentDay = $fromDate->format('d');
                $firstInstallmentMonth = $fromDate->format('m');
                $firstInstallmentYear = $fromDate->format('Y');

                if ($instType == 1) {
                    // Month Type
                    $installmentDate = clone $fromDate;
                    array_push($tempScheduleDays, clone $installmentDate);

                    while ($installmentDate <= $toDate) {

                        if ($firstInstallmentDay == '31' || $firstInstallmentDay == 31) {
                            $installmentDate = $installmentDate->modify('last day of next month');
                        } else if ($firstInstallmentDay == '30' || $firstInstallmentDay == 30
                            || $firstInstallmentDay == '29' || $firstInstallmentDay == 29) {

                            $tempNextMonth = clone $installmentDate;
                            $tempNextMonth = $tempNextMonth->modify('last day of next month');

                            if ($tempNextMonth->format('m') == '2' || $tempNextMonth->format('m') == '02'
                                || $tempNextMonth->format('m') == 2 || $tempNextMonth->format('m') == 02) {

                                $installmentDate = $installmentDate->modify('last day of next month');
                            } else {
                                $installmentDate = $installmentDate->modify('+1 month');
                            }
                        } else {
                            $installmentDate = $installmentDate->modify('+1 month');
                        }

                        array_push($tempScheduleDays, clone $installmentDate);
                    }

                } elseif ($instType == 2) {
                    // Week Type
                    $installmentDate = clone $fromDate;
                    while ($installmentDate <= $toDate) {
                        // array_push($tempScheduleDays, $installmentDate->format('Y-m-d'));
                        array_push($tempScheduleDays, clone $installmentDate);
                        $installmentDate = $installmentDate->modify('+1 week');
                    }
                }

                foreach ($tempScheduleDays as $tempRow) {

                    $holidayFlag = true;
                    $tempLoopDate = clone $tempRow;

                    while ($holidayFlag == true) {

                        $holidayFlag = false;

                        // Fixed Govt Holiday Check
                        foreach ($fixedGovtHoliday as $RowFG) {

                            if (($RowFG->gh_date == $tempLoopDate->format('d-m'))
                                && (empty($RowFG->efft_start_date) || ($RowFG->efft_start_date <= $tempLoopDate->format('Y-m-d')))
                                && (empty($RowFG->efft_end_date) || ($RowFG->efft_end_date >= $tempLoopDate->format('Y-m-d')))) {

                                $holidayFlag = true;
                            }
                        }

                        // Company Holiday Check
                        if ($holidayFlag == false) {
                            foreach ($companyHolidays as $RowC) {

                                $ch_day = $RowC->ch_day;
                                $ch_day_arr = explode(',', $RowC->ch_day);
                                $ch_eff_date = (!empty($RowC->ch_eff_date)) ? new DateTime($RowC->ch_eff_date) : null;

                                // ## This is Full day name
                                $dayName = $tempLoopDate->format('l');
                                if (in_array($dayName, $ch_day_arr) && (empty($ch_eff_date) || ($ch_eff_date <= $tempLoopDate))) {
                                    $holidayFlag = true;
                                }
                            }
                        }

                        // Special Holiday Org check
                        if ($holidayFlag == false) {
                            foreach ($sHolidaysORG as $RowO) {

                                $sh_date_from = new DateTime($RowO->sh_date_from);
                                $sh_date_to = new DateTime($RowO->sh_date_to);

                                if (($sh_date_from <= $tempLoopDate) && ($sh_date_to >= $tempLoopDate)) {
                                    $holidayFlag = true;
                                }
                            }
                        }

                        // Special Holiday Branch check
                        if ($holidayFlag == false) {
                            foreach ($sHolidaysBr as $RowB) {

                                $sh_date_from_b = new DateTime($RowB->sh_date_from);
                                $sh_date_to_b = new DateTime($RowB->sh_date_to);

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
            }

            ///////////////////////////////////////////////////////////////
            // Incomplete function, check remain if full week holiday skip this week but schedule date count must be equal installment month or week
            // When month and week end and go to next week that case date modify minus day

            if ($instType == 1) {
                if (count($scheduleDays) > $instMonth) {
                    $countDiff = count($scheduleDays) - $instMonth;
                    for ($r = 0; $r < $countDiff; $r++) {
                        array_pop($scheduleDays);
                    }
                }
            } else if ($instType == 2) {
                if (count($scheduleDays) > $instWeek) {
                    $countDiff = count($scheduleDays) - $instWeek;
                    for ($r = 0; $r < $countDiff; $r++) {
                        array_pop($scheduleDays);
                    }
                }
            }

            // // ## ## Data merge with (branch@salesdate@installmentType@installmentMonth) key
            $allScheduleData[$passingValue] = $scheduleDays;

            // dd($allScheduleData);
        }
        // // ## Schedule Make End

        // return serialize($allScheduleData);
        return $allScheduleData;
    }

    // ## please dont delete this function
    public static function old_installmentSchedule_multiple($companyID = null, $branchArr = [], $branchDateTypeMonthArr = [], $somityArr = [])
    {
        // ## integer, ## string, ## array
        if (gettype($branchDateTypeMonthArr) == "string") {
            $branchDateTypeMonthArr = unserialize($branchDateTypeMonthArr);
        }

        if (gettype($branchArr) == "string") {
            $branchArr = unserialize($branchArr);
        }

        $allScheduleData = array();

        // // ## Concat data by @ (branch@salesdate@installmentType@installmentMonth)
        $scheduleFlag = (count($branchDateTypeMonthArr) > 0) ? true : false;

        // ## if sales date, type, month empty then return initial with empty array
        if ($scheduleFlag == false) {
            return serialize($allScheduleData);
        }

        $weekDayArr = [
            1 => 'Saturday',
            2 => 'Sunday',
            3 => 'Monday',
            4 => 'Tuesday',
            5 => 'Wednesday',
            6 => 'Thursday',
            7 => 'Friday',
        ];

        $companyID = (!empty($companyID)) ? $companyID : Common::getCompanyId();
        $branchArr = (count($branchArr) > 0) ? $branchArr : [Common::getBranchId()];
        $somityArr = (count($somityArr) > 0) ? $somityArr : [1];

        // ## ----------------------------------- Holiday Query Start
        if ($scheduleFlag) {
            // ## Fixed Govt Holiday Query
            $fixedGovtHoliday = DB::table('hr_holidays_govt')
                ->where([['is_delete', 0], ['is_active', 1]])
                ->select('id', 'gh_title', 'gh_date', 'efft_start_date', 'efft_end_date');

            // ## Company Holiday Query
            $companyHolidays = DB::table('hr_holidays_comp')
                ->where([['is_delete', 0], ['is_active', 1]])
                ->where(function ($companyHolidays) use ($companyID) {
                    if (!empty($companyID)) {
                        $companyHolidays->where('company_id', $companyID);
                    }
                })
                ->select('id', 'company_id', 'ch_title', 'ch_day', 'ch_eff_date');

            // ## Special Holiday Query
            $specialHolidays = DB::table('hr_holidays_special')
                ->where([['is_delete', 0], ['is_active', 1]])
                ->select('id', 'company_id', 'branch_id', 'sh_title', 'sh_app_for', 'sh_date_from', 'sh_date_to');

        }
        // ## ----------------------------------- End Holiday Query

        // // ## Schedule Make Start
        foreach ($branchDateTypeMonthArr as $passingValue) {

            // ## explode concat value
            $passingArr = explode('@', $passingValue);
            $branchID = (isset($passingArr[0]) && !empty($passingArr[0])) ? $passingArr[0] : null;
            $salesDate = (isset($passingArr[1]) && !empty($passingArr[1])) ? $passingArr[1] : null;
            $instType = (isset($passingArr[2]) && !empty($passingArr[2])) ? $passingArr[2] : null;
            $instMonth = (isset($passingArr[3]) && !empty($passingArr[3])) ? $passingArr[3] : null;
            // ## end explode

            // // ## Start Process Make Schedule
            $fromDate = null;
            $actualToDate = null;
            $toDate = null;
            $instCount = 0;
            $instMonth = (int) $instMonth;
            $instWeek = 0;

            $scheduleDays = array();
            $tempScheduleDays = array();

            if (!empty($salesDate) && !empty($instMonth)) {

                $fromDate = new DateTime($salesDate);
                $tempDate = clone $fromDate;
                $actualTempDate = clone $fromDate;

                /*
                 * Extra 2 month add kora hocche karon jodi
                kono date, week, month holiday te pore
                tahole add or remove kora jay
                 */
                $actualToDate = $actualTempDate->modify('+' . ($instMonth) . ' month');
                $toDate = $tempDate->modify('+' . (($instMonth - 1) + 2) . ' month');

                ## Week Count from Two Dates
                /*
                 * 1 Week = 60*60*24*7 = 604800
                 */
                $dateDiff = strtotime($actualToDate->format('d-m-Y'), 0) - strtotime($fromDate->format('d-m-Y'), 0);
                $instWeek = (int) floor($dateDiff / 604800);

                // dd($fromDate, $actualToDate, $instWeek);
            }

            if (!empty($fromDate) && !empty($toDate) && !empty($instType)) {

                ///////////////////////////////////// test ////////////////////////////
                // $instType = 2;
                ///////////////////////////////////// test ////////////////////////////
                // $week = $installmentDate->format("W");

                $firstInstallmentDay = $fromDate->format('d');
                $firstInstallmentMonth = $fromDate->format('m');
                $firstInstallmentYear = $fromDate->format('Y');

                if ($instType == 1) {
                    // Month Type
                    $installmentDate = clone $fromDate;
                    array_push($tempScheduleDays, clone $installmentDate);

                    while ($installmentDate <= $toDate) {

                        if ($firstInstallmentDay == '31' || $firstInstallmentDay == 31) {
                            $installmentDate = $installmentDate->modify('last day of next month');
                        } else if ($firstInstallmentDay == '30' || $firstInstallmentDay == 30
                            || $firstInstallmentDay == '29' || $firstInstallmentDay == 29) {

                            $tempNextMonth = clone $installmentDate;
                            $tempNextMonth = $tempNextMonth->modify('last day of next month');

                            if ($tempNextMonth->format('m') == '2' || $tempNextMonth->format('m') == '02'
                                || $tempNextMonth->format('m') == 2 || $tempNextMonth->format('m') == 02) {

                                $installmentDate = $installmentDate->modify('last day of next month');
                            } else {
                                $installmentDate = $installmentDate->modify('+1 month');
                            }
                        } else {
                            $installmentDate = $installmentDate->modify('+1 month');
                        }

                        array_push($tempScheduleDays, clone $installmentDate);
                    }

                } elseif ($instType == 2) {
                    // Week Type
                    $installmentDate = clone $fromDate;
                    while ($installmentDate <= $toDate) {
                        // array_push($tempScheduleDays, $installmentDate->format('Y-m-d'));
                        array_push($tempScheduleDays, clone $installmentDate);
                        $installmentDate = $installmentDate->modify('+1 week');
                    }
                }

                foreach ($tempScheduleDays as $tempRow) {

                    $holidayFlag = true;
                    $tempLoopDate = clone $tempRow;

                    while ($holidayFlag == true) {

                        $holidayFlag = false;

                        // ## ---------------- Fixed Govt Holiday Check Start
                        $countFixedHolyday = clone $fixedGovtHoliday;

                        $countFixedHolyday = $countFixedHolyday->where('gh_date', $tempLoopDate->format('d-m'))
                            ->where(function ($countFixedHolyday) use ($tempLoopDate) {
                                if (!empty($tempLoopDate)) {
                                    $countFixedHolyday->whereNull('efft_start_date');
                                    $countFixedHolyday->orWhere('efft_start_date', '<=', $tempLoopDate->format('Y-m-d'));
                                }
                            })
                            ->where(function ($countFixedHolyday) use ($tempLoopDate) {
                                if (!empty($tempLoopDate)) {
                                    $countFixedHolyday->whereNull('efft_end_date');
                                    $countFixedHolyday->orWhere('efft_end_date', '>=', $tempLoopDate->format('Y-m-d'));
                                }
                            })
                            ->count();

                        if ($countFixedHolyday > 0) {
                            $holidayFlag = true;
                        }
                        // ## ---------------- End Fixed Govt Holiday Check

                        // ## ---------------- Company Holiday Check Start
                        if ($holidayFlag == false) {
                            // ## This is Full day name
                            $dayName = $tempLoopDate->format('l');

                            $countComHolyday = clone $companyHolidays;

                            $countComHolyday = $countComHolyday->where(function ($countComHolyday) use ($dayName) {
                                if (!empty($dayName)) {
                                    $countComHolyday->where('ch_day', 'LIKE', "%,{$dayName},%")
                                        ->orWhere('ch_day', 'LIKE', "{$dayName},%")
                                        ->orWhere('ch_day', 'LIKE', "%,{$dayName}")
                                        ->orWhere('ch_day', 'LIKE', "{$dayName}");
                                }
                            })
                                ->where(function ($countComHolyday) use ($tempLoopDate) {
                                    if (!empty($tempLoopDate)) {
                                        $countComHolyday->whereNull('ch_eff_date');
                                        $countComHolyday->orWhere('ch_eff_date', '<=', $tempLoopDate->format('Y-m-d'));
                                    }
                                })
                                ->count();

                            if ($countComHolyday > 0) {
                                $holidayFlag = true;
                            }
                        }
                        // ## ---------------- End Company Holiday Check

                        // ## ---------------- Special Holiday Org check Start
                        if ($holidayFlag == false) {

                            $countOrgHolyday = clone $specialHolidays;
                            $countOrgHolyday = $countOrgHolyday->where('sh_app_for', 'org')
                                ->where(function ($countOrgHolyday) use ($tempLoopDate) {
                                    if (!empty($tempLoopDate)) {
                                        $countOrgHolyday->where('sh_date_from', '<=', $tempLoopDate->format('Y-m-d'));
                                    }
                                })
                                ->where(function ($countOrgHolyday) use ($tempLoopDate) {
                                    if (!empty($tempLoopDate)) {
                                        $countOrgHolyday->where('sh_date_to', '>=', $tempLoopDate->format('Y-m-d'));
                                    }
                                })
                                ->count();

                            if ($countOrgHolyday > 0) {
                                $holidayFlag = true;
                            }
                        }
                        // ## ---------------- End Special Holiday Org check

                        // ## ---------------- Special Holiday Branch check Start
                        if ($holidayFlag == false) {

                            $countBranchHolyday = clone $specialHolidays;

                            $countBranchHolyday = $countBranchHolyday->where('sh_app_for', 'branch')
                                ->where(function ($countBranchHolyday) use ($branchID) {
                                    if (!empty($branchID)) {
                                        $countBranchHolyday->where('branch_id', $branchID);
                                    }
                                })
                                ->where(function ($countBranchHolyday) use ($tempLoopDate) {
                                    if (!empty($tempLoopDate)) {
                                        $countBranchHolyday->where('sh_date_from', '<=', $tempLoopDate->format('Y-m-d'));
                                    }
                                })
                                ->where(function ($countBranchHolyday) use ($tempLoopDate) {
                                    if (!empty($tempLoopDate)) {
                                        $countBranchHolyday->where('sh_date_to', '>=', $tempLoopDate->format('Y-m-d'));
                                    }
                                })
                                ->count();

                            if ($countBranchHolyday > 0) {
                                $holidayFlag = true;
                            }
                        }
                        // ## ---------------- End Special Branch Org check

                        if ($holidayFlag == false) {
                            array_push($scheduleDays, $tempLoopDate->format('Y-m-d'));
                            // array_push($scheduleDays, clone $tempLoopDate);

                        } else {
                            // $tempCurMonth = $tempRow->format('m');
                            $tempLoopDate = $tempLoopDate->modify('+1 day');
                        }
                    }
                }
            }

            ///////////////////////////////////////////////////////////////
            // Incomplete function, check remain if full week holiday skip this week but schedule date count must be equal installment month or week
            // When month and week end and go to next week that case date modify minus day

            if ($instType == 1) {
                if (count($scheduleDays) > $instMonth) {
                    $countDiff = count($scheduleDays) - $instMonth;
                    for ($r = 0; $r < $countDiff; $r++) {
                        array_pop($scheduleDays);
                    }
                }
            } else if ($instType == 2) {
                if (count($scheduleDays) > $instWeek) {
                    $countDiff = count($scheduleDays) - $instWeek;
                    for ($r = 0; $r < $countDiff; $r++) {
                        array_pop($scheduleDays);
                    }
                }
            }

            // // ## ## Data merge with (branch@salesdate@installmentType@installmentMonth) key
            $allScheduleData[$passingValue] = $scheduleDays;

            // dd($allScheduleData);
        }
        // // ## Schedule Make End

        return serialize($allScheduleData);
    }
/** End Schedule function */

/** Start Due Calculation function */
    public static function due_calculation($companyID = null, $selBranchArr = [], $endDate = null, $dueFor = 'current_and_over_due', $useFor = 'end_execution', $viewMethod = 'single')
    {
        /**
         * @dueFor = 'current_due' or 'over_due' or 'current_and_over_due'
         * @useFor = 'report' or 'end_execution'
         * @viewMethod is used for branch wise report or total due show
         * @viewMethod = 'single' or 'branch_wise'
         */

        $dueForCheckArr = ['current_due', 'over_due', 'current_and_over_due'];
        $useForCheckArr = ['report', 'end_execution'];

        if (count($selBranchArr) < 1 || empty($endDate) || in_array($dueFor, $dueForCheckArr) == false || in_array($useFor, $useForCheckArr) == false) {
            return false;
        }
        $companyID = (empty($companyID)) ? Common::getCompanyId() : $companyID;

        ///////// start work
        $dueQuery = DB::table('pos_sales_m as sm')
            ->where([['sm.is_delete', 0], ['sm.is_active', 1], ['sm.sales_type', 2]]) // ['sm.is_opening', 0]
            ->whereIn('sm.branch_id', $selBranchArr)
            ->select('sm.branch_id', 'sm.sales_bill_no', 'sm.customer_id',
                'sm.sales_date', 'sm.total_amount as sales_amount', 'sm.installment_amount',
                'sm.installment_type', 'sm.installment_month', 'sm.installment_date as last_installment_date',
                DB::raw('
                    (CASE
                        WHEN sm.installment_type = 1 THEN sm.installment_month
                        ELSE (FLOOR(DATEDIFF(DATE(DATE_FORMAT(DATE_ADD(sm.sales_date, INTERVAL +sm.installment_month MONTH), "%Y%m%d")), DATE(DATE_FORMAT(sm.sales_date, "%Y%m%d")))/7))
                    END) as ttl_installment,
                    IFNULL((sm.paid_amount - sm.vat_amount - sm.service_charge),0) as first_instalment,
                    (CASE
                        WHEN sm.installment_date >= "' . $endDate . '" THEN CONCAT(sm.branch_id, "@", sm.sales_date, "@", sm.installment_type, "@", sm.installment_month)
                        ELSE "NULL@NULL@NULL@NULL"
                    END) as branch_date_type_month
                    ')
            )
            ->where(function ($dueQuery) use ($endDate) {
                if (!empty($endDate)) {
                    $dueQuery->where('sm.sales_date', '<', $endDate);
                }
            })
            ->where(function ($dueQuery) use ($endDate, $dueFor) { // ## installment_date is instalment last date
                if (!empty($endDate) && $dueFor == 'current_due') {
                    $dueQuery->where('sm.installment_date', '>=', $endDate);
                }
                if (!empty($endDate) && $dueFor == 'over_due') {
                    $dueQuery->where('sm.installment_date', '<', $endDate);
                }
            })
            ->where(function ($dueQuery) use ($endDate) {
                if (!empty($endDate)) {
                    ## Complete Sales ignore
                    $dueQuery->whereNull('sm.complete_date');
                    $dueQuery->orWhere('sm.complete_date', '>=', $endDate);
                }
            })
        // ->where(function ($dueQuery) use ($branchID) {
        //     if (!empty($branchID)) {
        //         $dueQuery->where('sm.branch_id', $branchID);
        //     }
        // })
            ->groupBy('sm.sales_bill_no')
            ->orderBy('sm.sales_date', 'ASC')
            ->get();

        // dd($dueQuery);

        // // ## this is for ignore join or sub query
        if ($useFor == 'report') {
            $customerArr = (!empty($dueQuery)) ? array_values($dueQuery->pluck('customer_id')->unique()->all()) : array();
            $customerData = array();
            if (count($customerArr) > 0) {
                $customerData = DB::table('pos_customers')
                    ->where([['is_delete', 0], ['is_active', 1]])
                    ->whereIn('customer_no', $customerArr)
                    ->selectRaw('CONCAT(customer_name, " (", customer_no, ")") AS customer_name, customer_no')
                    ->pluck('customer_name', 'customer_no')
                    ->toArray();
                // // This query is return array[key as customer_no] = value as a customer_name
            }
        }

        $salesBillArr = (!empty($dueQuery)) ? array_values($dueQuery->pluck('sales_bill_no')->unique()->all()) : array();
        $collectionData = array();
        if (count($salesBillArr) > 0) {
            $collectionData = DB::table('pos_collections')
                ->where([['is_delete', 0], ['is_active', 1]])
                ->whereIn('sales_bill_no', $salesBillArr)
                ->where(function ($collectionData) use ($endDate) {
                    if (!empty($endDate)) {
                        $collectionData->where('collection_date', '<=', $endDate);
                    }
                })
                ->groupBy('sales_bill_no')
                ->selectRaw('IFNULL(SUM(collection_amount), 0) AS total_collection_amount, sales_bill_no')
                ->pluck('total_collection_amount', 'sales_bill_no')
                ->toArray();
        }

        $allScheduleData = array();

        if ($dueFor == 'current_due' || $dueFor == 'current_and_over_due') {

            $branchArr = (!empty($dueQuery)) ? array_values($dueQuery->pluck('branch_id')->unique()->all()) : array();
            // // ## Concat data by @ (branch@salesdate@installmentType@installmentMonth)
            $branchDateTypeMonthArr = (!empty($dueQuery)) ? array_values($dueQuery->pluck('branch_date_type_month')->unique()->all()) : array();

            $allScheduleData = self::installmentSchedule_multiple($companyID, $branchArr, $branchDateTypeMonthArr);
            $allScheduleData = $allScheduleData;
        }

        $sl = 0;
        $ttl_sales_amount = 0;
        $ttl_payable_amount = 0;
        $ttl_paid_amount = 0;
        $ttl_current_due = 0;
        $ttl_over_due = 0;
        $ttl_total_balance = 0;
        $ttl_total_due = 0;

        $DataSet = array();

        foreach ($dueQuery as $row) {

            $sales_amount = $row->sales_amount;
            $total_installment = $row->ttl_installment;
            // ## first_instalment
            $first_instalment_amount = $row->first_instalment;
            $regular_installment_amount = $row->installment_amount;
            /** total installment theke 2 (-) karon last & first installment bad diye calculation kora hocche */
            $last_instalment_amount = ($sales_amount - ($first_instalment_amount + ($regular_installment_amount * ($total_installment - 2))));

            /// ## Get Collection Amount
            $collection_amount = (isset($collectionData[$row->sales_bill_no])) ? $collectionData[$row->sales_bill_no] : 0;

            $outstanding_balance_amount = ($sales_amount - $collection_amount);
            $over_due_amount = ($sales_amount - $collection_amount);

            $current_due = 0;
            $payable_amount_till = 0;

            /// // ## calculation For Current Due
            if ($dueFor == 'current_due' || $dueFor == 'current_and_over_due') {
                if ($row->last_installment_date >= $endDate) {
                    $over_due_amount = 0;
                    $payable_amount_till = 0;

                    // // ## Pasing Installment due to End Date
                    $varBranchDateTypeMonth = $row->branch_date_type_month;
                    $scheduleDate = array();

                    if (isset($allScheduleData[$varBranchDateTypeMonth])) {
                        $scheduleDate = $allScheduleData[$varBranchDateTypeMonth];
                    }

                    // // ## find passing installment date
                    $passInstallment = 0;
                    foreach ($scheduleDate as $value) {
                        if ($value <= $endDate) {
                            $passInstallment++;
                        } else {
                            break;
                        }
                    }

                    // // ## Ignore 1st instalment count
                    $passInstallment = $passInstallment - 1;

                    /** Payable amount a  */
                    $payable_amount_till = $first_instalment_amount + ($regular_installment_amount * $passInstallment);

                    if ($endDate == $row->last_installment_date) {
                        // // ## Ignore Last instalment count
                        $passInstallment = $passInstallment - 1;
                        $payable_amount_till = $first_instalment_amount + ($regular_installment_amount * $passInstallment) + $last_instalment_amount;
                    }

                    $current_due = $collection_amount - $payable_amount_till;

                    if ($current_due >= 0) {
                        $current_due = 0;
                    } else {
                        $current_due = abs($current_due);
                    }
                }
            }

            if ($current_due > 0 || $over_due_amount > 0) {
                $TempSet = array();

                $customer_info = "";
                if ($useFor == 'report') {
                    $customer_info = (isset($customerData[$row->customer_id])) ? $customerData[$row->customer_id] : "";
                }

                $TempSet = [
                    'sl' => ++$sl,
                    'customer_name' => $customer_info,
                    'sales_bill_no' => $row->sales_bill_no,
                    'sales_date' => (new DateTime($row->sales_date))->format('d-m-Y'),
                    'sales_amount' => number_format($sales_amount, 2),

                    'installment' => $total_installment,
                    'first_installment' => number_format($first_instalment_amount, 2),
                    'installment_amount' => number_format($regular_installment_amount, 2),
                    'last_installment' => number_format($last_instalment_amount, 2),
                    'last_installment_date' => (new DateTime($row->last_installment_date))->format('d-m-Y'),

                    'payable_amount' => ($payable_amount_till > 0) ? number_format($payable_amount_till, 2) : '-',
                    'paid_amount' => number_format($collection_amount, 2),
                    'current_due' => ($current_due > 0) ? number_format($current_due, 2) : '-',
                    'over_due' => ($over_due_amount > 0) ? number_format($over_due_amount, 2) : '-',
                    'total_balance' => number_format($outstanding_balance_amount, 2),
                ];

                $DataSet[] = $TempSet;

                $ttl_sales_amount += $sales_amount;
                $ttl_payable_amount += $payable_amount_till;
                $ttl_paid_amount += $collection_amount;
                $ttl_current_due += $current_due;
                $ttl_over_due += $over_due_amount;
                $ttl_total_balance += $outstanding_balance_amount;
            }
        }

        $ttl_total_due = $ttl_current_due + $ttl_over_due;

        if ($useFor == 'end_execution') {
            $result_set = [
                'ttl_current_due' => $ttl_current_due,
                'ttl_over_due' => $ttl_over_due,
                'ttl_total_due' => $ttl_total_due,
            ];
        } elseif ($useFor == 'report') {
            $result_set = [
                'ttl_sales_amount' => $ttl_sales_amount,
                'ttl_payable_amount' => $ttl_payable_amount,
                'ttl_paid_amount' => $ttl_paid_amount,
                'ttl_current_due' => $ttl_current_due,
                'ttl_over_due' => $ttl_over_due,
                'ttl_total_balance' => $ttl_total_balance,
                'ttl_total_due' => $ttl_total_due,
                'report_data' => serialize($DataSet),
            ];
        }

        return $result_set;
    }

/** End Due Calculation function */

/** start Stock function  */
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

        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();

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
            // $fromDate = $fromDate->format('Y-m-d');
        }

        if (!empty($endDate)) {
            $toDate = new DateTime($endDate);
            // $toDate = $toDate->format('Y-m-d');
        } else {
            $toDate = new DateTime(Common::systemCurrentDate());
            // $toDate = new DateTime();

            // $toDate = $toDate->format('Y-m-d');
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

            /* Model Load */
            $POpeningBalance = 'App\\Model\\POS\\POBStockDetails';
            $PurchaseDetails = 'App\\Model\\POS\\PurchaseDetails';
            $PurchaseReturnDetails = 'App\\Model\\POS\\PurchaseReturnDetails';
            $IssueDetails = 'App\\Model\\POS\\Issued';
            $IssueReturnDetails = 'App\\Model\\POS\\IssueReturnd';
            $TransferDetails = 'App\\Model\\POS\\TransferDetails';
            $SalesDetails = 'App\\Model\\POS\\SalesDetails';
            $SaleReturnd = 'App\\Model\\POS\\SaleReturnd';

            // Opening Balance Count
            $OpeningBalance = DB::table('pos_ob_stock_m as obm')
                ->where([['obm.is_delete', 0], ['obm.is_active', 1], ['obm.branch_id', $branchID]])
                ->where(function ($OpeningBalance) use ($fromDate, $toDate) {
                    if (!empty($fromDate) && !empty($toDate)) {
                        $OpeningBalance->whereBetween('obm.opening_date', [$fromDate, $toDate]);
                    }

                    if (!empty($fromDate) && empty($toDate)) {
                        $OpeningBalance->where('obm.opening_date', '>=', $fromDate);
                    }

                    if (empty($fromDate) && !empty($toDate)) {
                        $OpeningBalance->where('obm.opening_date', '<=', $toDate);
                    }
                })
                ->leftjoin('pos_ob_stock_d as obd', function ($OpeningBalance) use ($ProductID) {
                    $OpeningBalance->on('obd.ob_no', 'obm.ob_no')
                        ->where('obd.product_id', $ProductID);
                })
                ->sum('obd.product_quantity');

            // Purchase Balance Count
            $Purchase = DB::table('pos_purchases_m as pm')
                ->where([['pm.is_delete', 0], ['pm.is_active', 1], ['pm.branch_id', $branchID]])
                ->where(function ($Purchase) use ($fromDate, $toDate) {

                    if (!empty($fromDate) && !empty($toDate)) {
                        $Purchase->whereBetween('pm.purchase_date', [$fromDate, $toDate]);
                    }

                    if (!empty($fromDate) && empty($toDate)) {
                        $Purchase->where('pm.purchase_date', '>=', $fromDate);
                    }

                    if (empty($fromDate) && !empty($toDate)) {
                        $Purchase->where('pm.purchase_date', '<=', $toDate);
                    }
                })
                ->leftjoin('pos_purchases_d as pd', function ($Purchase) use ($ProductID) {
                    $Purchase->on('pd.purchase_bill_no', 'pm.bill_no')
                        ->where('pd.product_id', $ProductID);
                })
                ->sum('pd.product_quantity');

            // Purchase Return Count

            $PurchaseReturn = DB::table('pos_purchases_r_m as prm')
                ->where([['prm.is_delete', 0], ['prm.is_active', 1], ['prm.branch_id', $branchID]])
                ->where(function ($PurchaseReturn) use ($fromDate, $toDate) {
                    if (!empty($fromDate) && !empty($toDate)) {
                        $PurchaseReturn->whereBetween('prm.return_date', [$fromDate, $toDate]);
                    }

                    if (!empty($fromDate) && empty($toDate)) {
                        $PurchaseReturn->where('prm.return_date', '>=', $fromDate);
                    }

                    if (empty($fromDate) && !empty($toDate)) {
                        $PurchaseReturn->where('prm.return_date', '<=', $toDate);
                    }
                })
                ->leftjoin('pos_purchases_r_d as prd', function ($PurchaseReturn) use ($ProductID) {
                    $PurchaseReturn->on('prd.pr_bill_no', 'prm.bill_no')
                        ->where('prd.product_id', $ProductID);
                })
                ->sum('prd.product_quantity');

            /* Branch ID 1 for Head Office Branch */
            if ($branchID == 1) {

                // Issue Balance Count
                $Issue = DB::table('pos_issues_m as im')
                    ->where([['im.is_delete', 0], ['im.is_active', 1], ['im.branch_from', $branchID]])
                    ->where(function ($Issue) use ($fromDate, $toDate) {

                        if (!empty($fromDate) && !empty($toDate)) {
                            $Issue->whereBetween('im.issue_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $Issue->where('im.issue_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $Issue->where('im.issue_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_issues_d as isd', function ($Issue) use ($ProductID) {
                        $Issue->on('isd.issue_bill_no', 'im.bill_no')
                            ->where('isd.product_id', $ProductID);
                    })
                    ->sum('isd.product_quantity');

                // dd($Issue);

                // Issue Return Count
                $IssueReturn = DB::table('pos_issues_r_m as irm')
                    ->where([['irm.is_delete', 0], ['irm.is_active', 1], ['irm.branch_to', $branchID]])
                    ->where(function ($IssueReturn) use ($fromDate, $toDate) {

                        if (!empty($fromDate) && !empty($toDate)) {
                            $IssueReturn->whereBetween('irm.return_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $IssueReturn->where('irm.return_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $IssueReturn->where('irm.return_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_issues_r_d as ird', function ($IssueReturn) use ($ProductID) {
                        $IssueReturn->on('ird.ir_bill_no', 'irm.bill_no')
                            ->where('ird.product_id', $ProductID);
                    })
                    ->sum('ird.product_quantity');

                //////////////////////////////////////////////////////
                if (!empty($fromDate) && !empty($toDate)) {
                    $tempDate = clone $fromDate;
                    $NewDate = $tempDate->modify('-1 day');
                    $PreOB = self::stockQuantity($branchID, $ProductID, false, null, $NewDate->format('Y-m-d'));
                }

                // $OpeningBalance = $OpeningBalance + $PreOB;

                $Stock = ($PreOB + $OpeningBalance + $Purchase - $PurchaseReturn - $Issue + $IssueReturn + $Adjustment);
            } else {
                // dd($fromDate, $toDate);
                // Issue Balance Count
                $Issue = DB::table('pos_issues_m as im')
                    ->where([['im.is_delete', 0], ['im.is_active', 1], ['im.branch_to', $branchID]])

                    ->where(function ($Issue) use ($fromDate, $toDate) {

                        if (!empty($fromDate) && !empty($toDate)) {
                            $Issue->whereBetween('im.issue_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $Issue->where('im.issue_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $Issue->where('im.issue_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_issues_d as isd', function ($Issue) use ($ProductID) {
                        $Issue->on('isd.issue_bill_no', 'im.bill_no')
                            ->where('isd.product_id', $ProductID);
                    })
                    ->sum('isd.product_quantity');

                // dd($Issue);

                // Issue Return Count
                $IssueReturn = DB::table('pos_issues_r_m as irm')
                    ->where([['irm.is_delete', 0], ['irm.is_active', 1], ['irm.branch_from', $branchID]])
                    ->where(function ($IssueReturn) use ($fromDate, $toDate) {

                        if (!empty($fromDate) && !empty($toDate)) {
                            $IssueReturn->whereBetween('irm.return_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $IssueReturn->where('irm.return_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $IssueReturn->where('irm.return_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_issues_r_d as ird', function ($IssueReturn) use ($ProductID) {
                        $IssueReturn->on('ird.ir_bill_no', 'irm.bill_no')
                            ->where('ird.product_id', $ProductID);
                    })
                    ->sum('ird.product_quantity');

                // TransferIn Balance Count
                $TransferIn = DB::table('pos_transfers_m as ptm')
                    ->where([['ptm.is_delete', 0], ['ptm.is_active', 1], ['ptm.branch_to', $branchID]])
                    ->where(function ($TransferIn) use ($fromDate, $toDate) {

                        if (!empty($fromDate) && !empty($toDate)) {
                            $TransferIn->whereBetween('ptm.transfer_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $TransferIn->where('ptm.transfer_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $TransferIn->where('ptm.transfer_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_transfers_d as ptd', function ($TransferIn) use ($ProductID) {
                        $TransferIn->on('ptd.transfer_bill_no', 'ptm.bill_no')
                            ->where('ptd.product_id', $ProductID);
                    })
                    ->sum('ptd.product_quantity');

                // TransferOut Return Count
                $TransferOut = DB::table('pos_transfers_m as ptm')
                    ->where([['ptm.is_delete', 0], ['ptm.is_active', 1], ['ptm.branch_from', $branchID]])
                    ->where(function ($TransferOut) use ($fromDate, $toDate) {

                        if (!empty($fromDate) && !empty($toDate)) {
                            $TransferOut->whereBetween('ptm.transfer_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $TransferOut->where('ptm.transfer_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $TransferOut->where('ptm.transfer_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_transfers_d as ptd', function ($TransferOut) use ($ProductID) {
                        $TransferOut->on('ptd.transfer_bill_no', 'ptm.bill_no')
                            ->where('ptd.product_id', $ProductID);
                    })
                    ->sum('ptd.product_quantity');

                // Sales Balance Count
                $Sales = DB::table('pos_sales_m as psm')
                    ->where([['psm.is_delete', 0], ['psm.is_active', 1], ['psm.branch_id', $branchID]])
                    ->where(function ($Sales) use ($fromDate, $toDate) {

                        if (!empty($fromDate) && !empty($toDate)) {
                            $Sales->whereBetween('psm.sales_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $Sales->where('psm.sales_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $Sales->where('psm.sales_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_sales_d as psd', function ($Sales) use ($ProductID) {
                        $Sales->on('psd.sales_bill_no', 'psm.sales_bill_no')
                            ->where('psd.product_id', $ProductID);
                    })
                    ->sum('psd.product_quantity');

                // SaleReturnd Return Count
                $SalesReturn = DB::table('pos_sales_return_m as psrm')
                    ->where([['psrm.is_delete', 0], ['psrm.is_active', 1], ['psrm.branch_id', $branchID]])
                    ->where(function ($SalesReturn) use ($fromDate, $toDate) {

                        if (!empty($fromDate) && !empty($toDate)) {
                            $SalesReturn->whereBetween('psrm.return_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $SalesReturn->where('psrm.return_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $SalesReturn->where('psrm.return_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_sales_return_d as psrd', function ($SalesReturn) use ($ProductID) {
                        $SalesReturn->on('psrd.return_bill_no', 'psrm.return_bill_no')
                            ->where('psrd.product_id', $ProductID);
                    })
                    ->sum('psrd.product_quantity');

                //////////////////////////////////////////////////////
                if (!empty($fromDate) && !empty($toDate)) {
                    $tempDate = clone $fromDate;
                    $NewDate = $tempDate->modify('-1 day');
                    $PreOB = self::stockQuantity($branchID, $ProductID, false, null, $NewDate->format('Y-m-d'));
                }

                // $OpeningBalance = $OpeningBalance + $PreOB;

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

        } else {
            return "Error";
        }
    }

    public static function stockQuantity_Multiple($branchID, $ProductID = [], $returnArray = false, $startDate = null, $endDate = null)
    {
        /**
         * Algorithm Stock Count For H/O
         * Stock = OpeningBalance + Purchase - PurchaseReturn - Issue + IssueReturn +- Adjustment
         */
        /**
         * Algorithm Stock Count For Branch
         * Stock = OpeningBalance + Issue - IssueReturn + TransferIn - TransferOut - Sales + SalesReturn +- Adjustment
         */

        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();

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
            $toDate = new DateTime(Common::systemCurrentDate());
        }

        // $branchID >= 1 &&

        if (!empty($ProductID)) {

            $StockC = 0;
            $PreOBC = 0;
            $OpeningBalanceC = 0;
            $PurchaseC = 0;
            $PurchaseReturnC = 0;
            $IssueC = 0;
            $IssueReturnC = 0;
            $TransferInC = 0;
            $TransferOutC = 0;
            $SalesC = 0;
            $SalesReturnC = 0;
            $AdjustmentC = 0;

            $StockA = array();
            $PreOBA = array();
            $OpeningBalanceA = array();
            $PurchaseA = array();
            $PurchaseReturnA = array();
            $IssueA = array();
            $IssueReturnA = array();
            $TransferInA = array();
            $TransferOutA = array();
            $SalesA = array();
            $SalesReturnA = array();
            $AdjustmentA = array();

            $stockArr = array();
            $AllStockArray = array();

            /* Model Load */
            // $POpeningBalance = 'App\\Model\\POS\\POBStockDetails';
            // $PurchaseDetails = 'App\\Model\\POS\\PurchaseDetails';
            // $PurchaseReturnDetails = 'App\\Model\\POS\\PurchaseReturnDetails';
            // $IssueDetails = 'App\\Model\\POS\\Issued';
            // $IssueReturnDetails = 'App\\Model\\POS\\IssueReturnd';
            // $TransferDetails = 'App\\Model\\POS\\TransferDetails';
            // $SalesDetails = 'App\\Model\\POS\\SalesDetails';
            // $SaleReturnd = 'App\\Model\\POS\\SaleReturnd';

            /* Branch ID 1 for Head Office Branch */
            if ($branchID == 1) {

                // Opening Balance Count
                $OpeningBalance = DB::table('pos_ob_stock_m as obm')
                    ->where([['obm.is_delete', 0], ['obm.is_active', 1], ['obm.branch_id', $branchID]])
                    ->where(function ($OpeningBalance) use ($fromDate, $toDate) {

                        if (!empty($fromDate) && !empty($toDate)) {
                            $OpeningBalance->whereBetween('obm.opening_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $OpeningBalance->where('obm.opening_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $OpeningBalance->where('obm.opening_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_ob_stock_d as obd', function ($OpeningBalance) use ($ProductID) {
                        $OpeningBalance->on('obd.ob_no', 'obm.ob_no')
                            ->whereIn('obd.product_id', $ProductID);
                    })
                    ->selectRaw('obd.product_id, SUM(obd.product_quantity) as OpeningBalance')
                    ->groupBy('obd.product_id')
                    ->get();

                foreach ($OpeningBalance as $Row) {
                    $OpeningBalanceA[$Row->product_id] = $Row->OpeningBalance;
                }

                // Purchase Balance Count
                $Purchase = DB::table('pos_purchases_m as pm')
                    ->where([['pm.is_delete', 0], ['pm.is_active', 1], ['pm.branch_id', $branchID]])
                    ->where(function ($Purchase) use ($fromDate, $toDate) {

                        if (!empty($fromDate) && !empty($toDate)) {
                            $Purchase->whereBetween('pm.purchase_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $Purchase->where('pm.purchase_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $Purchase->where('pm.purchase_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_purchases_d as pd', function ($Purchase) use ($ProductID) {
                        $Purchase->on('pd.purchase_bill_no', 'pm.bill_no')
                            ->whereIn('pd.product_id', $ProductID);
                    })
                    ->selectRaw('pd.product_id, IFNULL(SUM(pd.product_quantity), 0) as Purchase')
                    ->groupBy('pd.product_id')
                    ->get();
                foreach ($Purchase as $Row) {
                    $PurchaseA[$Row->product_id] = $Row->Purchase;
                }

                // Purchase Return Count
                $PurchaseReturn = DB::table('pos_purchases_r_m as prm')
                    ->where([['prm.is_delete', 0], ['prm.is_active', 1], ['prm.branch_id', $branchID]])
                    ->where(function ($PurchaseReturn) use ($fromDate, $toDate) {
                        if (!empty($fromDate) && !empty($toDate)) {
                            $PurchaseReturn->whereBetween('prm.return_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $PurchaseReturn->where('prm.return_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $PurchaseReturn->where('prm.return_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_purchases_r_d as prd', function ($PurchaseReturn) use ($ProductID) {
                        $PurchaseReturn->on('prd.pr_bill_no', 'prm.bill_no')
                            ->whereIn('prd.product_id', $ProductID);
                    })
                    ->selectRaw('prd.product_id, SUM(prd.product_quantity) as PurchaseReturn')
                    ->groupBy('prd.product_id')
                    ->get();

                foreach ($PurchaseReturn as $Row) {
                    $PurchaseReturnA[$Row->product_id] = $Row->PurchaseReturn;
                }

                // Issue Balance Count
                $Issue = DB::table('pos_issues_m as im')
                    ->where([['im.is_delete', 0], ['im.is_active', 1], ['im.branch_from', $branchID]])
                    ->where(function ($Issue) use ($fromDate, $toDate) {

                        if (!empty($fromDate) && !empty($toDate)) {
                            $Issue->whereBetween('im.issue_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $Issue->where('im.issue_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $Issue->where('im.issue_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_issues_d as isd', function ($Issue) use ($ProductID) {
                        $Issue->on('isd.issue_bill_no', 'im.bill_no')
                            ->whereIn('isd.product_id', $ProductID);
                    })
                    ->selectRaw('isd.product_id, SUM(isd.product_quantity) as Issue')
                    ->groupBy('isd.product_id')
                    ->get();

                foreach ($Issue as $Row) {
                    $IssueA[$Row->product_id] = $Row->Issue;
                }

                // Issue Return Count
                $IssueReturn = DB::table('pos_issues_r_m as irm')
                    ->where([['irm.is_delete', 0], ['irm.is_active', 1], ['irm.branch_to', $branchID]])
                    ->where(function ($IssueReturn) use ($fromDate, $toDate) {

                        if (!empty($fromDate) && !empty($toDate)) {
                            $IssueReturn->whereBetween('irm.return_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $IssueReturn->where('irm.return_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $IssueReturn->where('irm.return_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_issues_r_d as ird', function ($IssueReturn) use ($ProductID) {
                        $IssueReturn->on('ird.ir_bill_no', 'irm.bill_no')
                            ->whereIn('ird.product_id', $ProductID);
                    })
                    ->selectRaw('ird.product_id, SUM(ird.product_quantity) as IssueReturn')
                    ->groupBy('ird.product_id')
                    ->get();
                // dd($IssueReturn);

                foreach ($IssueReturn as $Row) {
                    $IssueReturnA[$Row->product_id] = $Row->IssueReturn;
                }

                $productData = Product::where([['is_delete', 0], ['is_active', 1]])->whereIn('id', $ProductID)->get();

                foreach ($productData as $row) {

                    $OpeningBalanceC = ((isset($OpeningBalanceA[$row->id]) ? $OpeningBalanceA[$row->id] : 0));
                    $PurchaseC = ((isset($PurchaseA[$row->id]) ? $PurchaseA[$row->id] : 0));
                    $PurchaseReturnC = ((isset($PurchaseReturnA[$row->id]) ? $PurchaseReturnA[$row->id] : 0));
                    $IssueC = ((isset($IssueA[$row->id]) ? $IssueA[$row->id] : 0));
                    $IssueReturnC = ((isset($IssueReturnA[$row->id]) ? $IssueReturnA[$row->id] : 0));

                    $StockC = ($OpeningBalanceC + $PurchaseC - $PurchaseReturnC - $IssueC + $IssueReturnC + $AdjustmentC);

                    $stockArr[$row->id] = [
                        'Stock' => $StockC,
                        'PreOB' => $PreOBC,
                        'OpeningBalance' => $OpeningBalanceC,
                        'Purchase' => $PurchaseC,
                        'PurchaseReturn' => $PurchaseReturnC,
                        'Issue' => $IssueC,
                        'IssueReturn' => $IssueReturnC,
                        'Adjustment' => $AdjustmentC,
                    ];
                }

                $PreOBArr = array();
                $AllStockArray = $stockArr;

                // dd(  $AllStockArray );

                if (!empty($fromDate) && !empty($toDate)) {

                    $tempDate = clone $fromDate;
                    $NewDate = $tempDate->modify('-1 day');
                    // dd( $NewDate);

                    $PreOBArr = self::stockQuantity_Multiple($branchID, $ProductID, false, null, $NewDate->format('Y-m-d'));

                    foreach (array_keys($stockArr + $PreOBArr) as $key) {

                        // $AllStockArray[$key] = [
                        //     'Stock' => $stockArr[$key]['Stock'] + $PreOBArr[$key]['Stock'],
                        //     'PreOB' => $stockArr[$key]['PreOB'] + $PreOBArr[$key]['PreOB'],
                        //     'OpeningBalance' => $stockArr[$key]['OpeningBalance'] + $PreOBArr[$key]['OpeningBalance'],
                        //     'Purchase' => $stockArr[$key]['Purchase'] + $PreOBArr[$key]['Purchase'],
                        //     'PurchaseReturn' => $stockArr[$key]['PurchaseReturn'] + $PreOBArr[$key]['PurchaseReturn'],
                        //     'Issue' => $stockArr[$key]['Issue'] + $PreOBArr[$key]['Issue'],
                        //     'IssueReturn' => $stockArr[$key]['IssueReturn'] + $PreOBArr[$key]['IssueReturn'],
                        //     'Adjustment' => $stockArr[$key]['Adjustment'] + $PreOBArr[$key]['Adjustment'],
                        // ];

                        $AllStockArray[$key] = [
                            'Stock' => $stockArr[$key]['Stock'] + $PreOBArr[$key]['Stock'],
                            'PreOB' => $stockArr[$key]['PreOB'],
                            'OpeningBalance' => $stockArr[$key]['OpeningBalance'] + $PreOBArr[$key]['Stock'],
                            'Purchase' => $stockArr[$key]['Purchase'],
                            'PurchaseReturn' => $stockArr[$key]['PurchaseReturn'],
                            'Issue' => $stockArr[$key]['Issue'],
                            'IssueReturn' => $stockArr[$key]['IssueReturn'],
                            'Adjustment' => $stockArr[$key]['Adjustment'],
                        ];
                    }
                }
            } else {

                // ## Opening Balance Count
                $OpeningBalance = DB::table('pos_ob_stock_m as obm')
                    ->where([['obm.is_delete', 0], ['obm.is_active', 1], ['obm.branch_id', '<>', 1]])
                    ->where(function ($OpeningBalance) use ($branchID, $fromDate, $toDate) {

                        if (!empty($branchID)) {
                            $OpeningBalance->where('obm.branch_id', $branchID);
                        }

                        if (!empty($fromDate) && !empty($toDate)) {
                            $OpeningBalance->whereBetween('obm.opening_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $OpeningBalance->where('obm.opening_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $OpeningBalance->where('obm.opening_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_ob_stock_d as obd', function ($OpeningBalance) use ($ProductID) {
                        $OpeningBalance->on('obd.ob_no', 'obm.ob_no')
                            ->whereIn('obd.product_id', $ProductID);
                    })
                    ->selectRaw('obd.product_id, SUM(obd.product_quantity) as OpeningBalance')
                    ->groupBy('obd.product_id')
                    ->get();

                foreach ($OpeningBalance as $Row) {
                    $OpeningBalanceA[$Row->product_id] = $Row->OpeningBalance;
                }

                // ## Issue Balance Count
                $Issue = DB::table('pos_issues_m as im')
                    ->where([['im.is_delete', 0], ['im.is_active', 1], ['im.branch_to', '<>', 1]])
                    ->where(function ($Issue) use ($branchID, $fromDate, $toDate) {

                        if (!empty($branchID)) {
                            $Issue->where('im.branch_to', $branchID);
                        }

                        if (!empty($fromDate) && !empty($toDate)) {
                            $Issue->whereBetween('im.issue_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $Issue->where('im.issue_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $Issue->where('im.issue_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_issues_d as isd', function ($Issue) use ($ProductID) {
                        $Issue->on('isd.issue_bill_no', 'im.bill_no')
                            ->whereIn('isd.product_id', $ProductID);
                    })
                    ->selectRaw('isd.product_id, SUM(isd.product_quantity) as Issue')
                    ->groupBy('isd.product_id')
                    ->get();

                foreach ($Issue as $Row) {
                    $IssueA[$Row->product_id] = $Row->Issue;
                }

                // ## Issue Return Count
                $IssueReturn = DB::table('pos_issues_r_m as irm')
                    ->where([['irm.is_delete', 0], ['irm.is_active', 1], ['irm.branch_from', '<>', 1]])
                    ->where(function ($IssueReturn) use ($branchID, $fromDate, $toDate) {

                        if (!empty($branchID)) {
                            $IssueReturn->where('irm.branch_from', $branchID);
                        }

                        if (!empty($fromDate) && !empty($toDate)) {
                            $IssueReturn->whereBetween('irm.return_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $IssueReturn->where('irm.return_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $IssueReturn->where('irm.return_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_issues_r_d as ird', function ($IssueReturn) use ($ProductID) {
                        $IssueReturn->on('ird.ir_bill_no', 'irm.bill_no')
                            ->whereIn('ird.product_id', $ProductID);
                    })
                    ->selectRaw('ird.product_id, SUM(ird.product_quantity) as IssueReturn')
                    ->groupBy('ird.product_id')
                    ->get();

                foreach ($IssueReturn as $Row) {
                    $IssueReturnA[$Row->product_id] = $Row->IssueReturn;
                }

                // ## TransferIn Balance Count
                $TransferIn = DB::table('pos_transfers_m as ptm')
                    ->where([['ptm.is_delete', 0], ['ptm.is_active', 1], ['ptm.branch_to', '<>', 1]])
                    ->where(function ($TransferIn) use ($branchID, $fromDate, $toDate) {

                        if (!empty($branchID)) {
                            $TransferIn->where('ptm.branch_to', $branchID);
                        }

                        if (!empty($fromDate) && !empty($toDate)) {
                            $TransferIn->whereBetween('ptm.transfer_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $TransferIn->where('ptm.transfer_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $TransferIn->where('ptm.transfer_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_transfers_d as ptd', function ($TransferIn) use ($ProductID) {
                        $TransferIn->on('ptd.transfer_bill_no', 'ptm.bill_no')
                            ->whereIn('ptd.product_id', $ProductID);
                    })
                    ->selectRaw('ptd.product_id, SUM(ptd.product_quantity) as TransferIn')
                    ->groupBy('ptd.product_id')
                    ->get();

                foreach ($TransferIn as $Row) {
                    $TransferInA[$Row->product_id] = $Row->TransferIn;
                }

                // ## TransferOut Return Count
                $TransferOut = DB::table('pos_transfers_m as ptm')
                    ->where([['ptm.is_delete', 0], ['ptm.is_active', 1], ['ptm.branch_from', '<>', 1]])
                    ->where(function ($TransferOut) use ($branchID, $fromDate, $toDate) {

                        if (!empty($branchID)) {
                            $TransferOut->where('ptm.branch_from', $branchID);
                        }

                        if (!empty($fromDate) && !empty($toDate)) {
                            $TransferOut->whereBetween('ptm.transfer_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $TransferOut->where('ptm.transfer_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $TransferOut->where('ptm.transfer_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_transfers_d as ptd', function ($TransferOut) use ($ProductID) {
                        $TransferOut->on('ptd.transfer_bill_no', 'ptm.bill_no')
                            ->whereIn('ptd.product_id', $ProductID);
                    })
                    ->selectRaw('ptd.product_id, SUM(ptd.product_quantity) as TransferOut')
                    ->groupBy('ptd.product_id')
                    ->get();

                foreach ($TransferOut as $Row) {
                    $TransferOutA[$Row->product_id] = $Row->TransferOut;
                }

                // ## Sales Balance Count
                $Sales = DB::table('pos_sales_m as psm')
                    ->where([['psm.is_delete', 0], ['psm.is_active', 1], ['psm.branch_id', '<>', 1]])
                    ->where(function ($Sales) use ($branchID, $fromDate, $toDate) {

                        if (!empty($branchID)) {
                            $Sales->where('psm.branch_id', $branchID);
                        }

                        if (!empty($fromDate) && !empty($toDate)) {
                            $Sales->whereBetween('psm.sales_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $Sales->where('psm.sales_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $Sales->where('psm.sales_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_sales_d as psd', function ($Sales) use ($ProductID) {
                        $Sales->on('psd.sales_bill_no', 'psm.sales_bill_no')
                            ->whereIn('psd.product_id', $ProductID);
                    })
                    ->selectRaw('psd.product_id, SUM(psd.product_quantity) as Sales')
                    ->groupBy('psd.product_id')
                    ->get();

                foreach ($Sales as $Row) {
                    $SalesA[$Row->product_id] = $Row->Sales;
                }

                // ## SaleReturnd Return Count
                $SalesReturn = DB::table('pos_sales_return_m as psrm')
                    ->where([['psrm.is_delete', 0], ['psrm.is_active', 1], ['psrm.branch_id', '<>', 1]])
                    ->where(function ($SalesReturn) use ($branchID, $fromDate, $toDate) {
                        if (!empty($branchID)) {
                            $SalesReturn->where('psrm.branch_id', $branchID);
                        }

                        if (!empty($fromDate) && !empty($toDate)) {
                            $SalesReturn->whereBetween('psrm.return_date', [$fromDate, $toDate]);
                        }

                        if (!empty($fromDate) && empty($toDate)) {
                            $SalesReturn->where('psrm.return_date', '>=', $fromDate);
                        }

                        if (empty($fromDate) && !empty($toDate)) {
                            $SalesReturn->where('psrm.return_date', '<=', $toDate);
                        }
                    })
                    ->leftjoin('pos_sales_return_d as psrd', function ($SalesReturn) use ($ProductID) {
                        $SalesReturn->on('psrd.return_bill_no', 'psrm.return_bill_no')
                            ->whereIn('psrd.product_id', $ProductID);
                    })
                    ->selectRaw('psrd.product_id, IFNULL(SUM(psrd.product_quantity), 0) as SalesReturn')
                    ->groupBy('psrd.product_id')
                    ->get();

                foreach ($SalesReturn as $Row) {
                    $SalesReturnA[$Row->product_id] = $Row->SalesReturn;
                }

                $productData = Product::where([['is_delete', 0], ['is_active', 1]])->whereIn('id', $ProductID)->get();

                foreach ($productData as $row) {

                    $OpeningBalanceC = ((isset($OpeningBalanceA[$row->id]) ? $OpeningBalanceA[$row->id] : 0));

                    $IssueC = ((isset($IssueA[$row->id]) ? $IssueA[$row->id] : 0));
                    $IssueReturnC = ((isset($IssueReturnA[$row->id]) ? $IssueReturnA[$row->id] : 0));

                    $TransferInC = ((isset($TransferInA[$row->id]) ? $TransferInA[$row->id] : 0));
                    $TransferOutC = ((isset($TransferOutA[$row->id]) ? $TransferOutA[$row->id] : 0));

                    $SalesC = ((isset($SalesA[$row->id]) ? $SalesA[$row->id] : 0));
                    $SalesReturnC = ((isset($SalesReturnA[$row->id]) ? $SalesReturnA[$row->id] : 0));

                    $StockC = ($OpeningBalanceC + $PurchaseC - $PurchaseReturnC + $IssueC - $IssueReturnC + $TransferInC - $TransferOutC - $SalesC + $SalesReturnC + $AdjustmentC);

                    $stockArr[$row->id] = [
                        'Stock' => $StockC,
                        'PreOB' => $PreOBC,
                        'OpeningBalance' => $OpeningBalanceC,
                        'Purchase' => $PurchaseC,
                        'PurchaseReturn' => $PurchaseReturnC,
                        'Issue' => $IssueC,
                        'IssueReturn' => $IssueReturnC,
                        'TransferIn' => $TransferInC,
                        'TransferOut' => $TransferOutC,
                        'Sales' => $SalesC,
                        'SalesReturn' => $SalesReturnC,
                        'Adjustment' => $AdjustmentC,
                    ];
                }

                

                $PreOBArr = array();
                $AllStockArray = $stockArr;

                if (!empty($fromDate) && !empty($toDate)) {

                    $tempDate = clone $fromDate;
                    $NewDate = $tempDate->modify('-1 day');

                    $PreOBArr = self::stockQuantity_Multiple($branchID, $ProductID, false, null, $NewDate->format('Y-m-d'));

                    // dd($stockArr, $PreOBArr);


                    foreach (array_keys($stockArr + $PreOBArr) as $key) {

                        // $AllStockArray[$key] = [
                        //     'Stock' => $stockArr[$key]['Stock'] + $PreOBArr[$key]['Stock'],
                        //     'PreOB' => $stockArr[$key]['PreOB'] + $PreOBArr[$key]['PreOB'],
                        //     'OpeningBalance' => $stockArr[$key]['OpeningBalance'] + $PreOBArr[$key]['OpeningBalance'],
                        //     'Purchase' => $stockArr[$key]['Purchase'] + $PreOBArr[$key]['Purchase'],
                        //     'PurchaseReturn' => $stockArr[$key]['PurchaseReturn'] + $PreOBArr[$key]['PurchaseReturn'],
                        //     'Issue' => $stockArr[$key]['Issue'] + $PreOBArr[$key]['Issue'],
                        //     'IssueReturn' => $stockArr[$key]['IssueReturn'] + $PreOBArr[$key]['IssueReturn'],
                        //     'TransferIn' => $stockArr[$key]['TransferIn'] + $PreOBArr[$key]['TransferIn'],
                        //     'TransferOut' => $stockArr[$key]['TransferOut'] + $PreOBArr[$key]['TransferOut'],
                        //     'Sales' => $stockArr[$key]['Sales'] + $PreOBArr[$key]['Sales'],
                        //     'SalesReturn' => $stockArr[$key]['SalesReturn'] + $PreOBArr[$key]['SalesReturn'],
                        //     'Adjustment' => $stockArr[$key]['Adjustment'] + $PreOBArr[$key]['Adjustment'],
                        // ];


                        $AllStockArray[$key] = [
                            'Stock' => $stockArr[$key]['Stock'] + $PreOBArr[$key]['Stock'],
                            'PreOB' => $stockArr[$key]['PreOB'],
                            'OpeningBalance' => $stockArr[$key]['OpeningBalance'] + $PreOBArr[$key]['Stock'],
                            'Purchase' => $stockArr[$key]['Purchase'],
                            'PurchaseReturn' => $stockArr[$key]['PurchaseReturn'],
                            'Issue' => $stockArr[$key]['Issue'],
                            'IssueReturn' => $stockArr[$key]['IssueReturn'],
                            'TransferIn' => $stockArr[$key]['TransferIn'],
                            'TransferOut' => $stockArr[$key]['TransferOut'],
                            'Sales' => $stockArr[$key]['Sales'],
                            'SalesReturn' => $stockArr[$key]['SalesReturn'],
                            'Adjustment' => $stockArr[$key]['Adjustment'],
                        ];
                    }
                }
            }

            return $AllStockArray;

        } else {
            return "Error";
        }
    }
/** End Stock function */

/** start Bill generator function  */
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
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
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
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
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
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
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
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
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
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
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

        // $ldate = date('Ym');
        $Counter = Common::getCounterNo();

        $PreBillNo = "SL" . $BranchCode . $Counter;
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

    public static function generateBillSalesReturn($BranchId = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $SalesReturn = 'App\\Model\\POS\\SaleReturnm';

        $BranchCode = $BranchT::where(['is_delete' => 0, 'is_approve' => 1, 'id' => $BranchId])
            ->select('branch_code')
            ->first();

        // $ldate = date('Ym');
        $Counter = Common::getCounterNo();

        $PreBillNo = "SR" . $BranchCode->branch_code . $Counter;

        $record = $SalesReturn::where('branch_id', $BranchId)
            ->select(['id', 'return_bill_no'])
            ->where('return_bill_no', 'LIKE', "{$PreBillNo}%")
            ->orderBy('return_bill_no', 'DESC')
            ->first();

        if ($record) {

            $OldBillNoA = explode($PreBillNo, $record->return_bill_no);
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
        }

        return $BillNo;
    }

    public static function generateBillPOBCustomer($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $ModelT = "App\\Model\\POS\\POBDueSaleMaster";

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        // $ldate = date('Ym');

        $PreBillNo = "POBC" . $BranchCode;
        $record = $ModelT::select(['id', 'ob_no'])
            ->where('branch_id', $branchID)
            ->where('ob_no', 'LIKE', "{$PreBillNo}%")
            ->orderBy('ob_no', 'DESC')
            ->first();

        if ($record) {
            $OldBillNoA = explode($PreBillNo, $record->ob_no);
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
        }

        return $BillNo;
    }

    public static function generateBillPOBS($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $ModelT = "App\\Model\\POS\\POBStockMaster";

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        // $ldate = date('Ym');

        $PreBillNo = "POBS" . $BranchCode;
        $record = $ModelT::select(['id', 'ob_no'])
            ->where('branch_id', $branchID)
            ->where('ob_no', 'LIKE', "{$PreBillNo}%")
            ->orderBy('ob_no', 'DESC')
            ->first();

        if ($record) {
            $OldBillNoA = explode($PreBillNo, $record->ob_no);
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
        }

        return $BillNo;
    }

    public static function generateBillRequisiton($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $RequisitionM = 'App\\Model\\POS\\RequisitionMaster';

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        $PreReqNo = "REQ" . $BranchCode;

        $record = $RequisitionM::select('id', 'requisition_no')
            ->where('branch_from', $branchID)
            ->where('requisition_no', 'LIKE', "{$PreReqNo}%")
            ->orderBy('requisition_no', 'DESC')
            ->first();

        if ($record) {
            $OldReqNoA = explode($PreReqNo, $record->requisition_no);
            $ReqNo = $PreReqNo . sprintf("%05d", ($OldReqNoA[1] + 1));
        } else {
            $ReqNo = $PreReqNo . sprintf("%05d", 1);
        }

        return $ReqNo;
    }

    public static function generateBillOrder($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $OrderM = 'App\\Model\\POS\\OrderMaster';

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        $PreOrderNo = "OR" . $BranchCode;

        $record = $OrderM::select('id', 'order_no')
            ->where('order_from', $branchID)
            ->where('order_no', 'LIKE', "{$PreOrderNo}%")
            ->orderBy('order_no', 'DESC')
            ->first();

        if ($record) {
            $OldOrderNo = explode($PreOrderNo, $record->order_no);
            $OrderNo = $PreOrderNo . sprintf("%05d", ($OldOrderNo[1] + 1));
        } else {
            $OrderNo = $PreOrderNo . sprintf("%05d", 1);
        }

        return $OrderNo;
    }

    public static function generateBillDelivery($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $Delivery = 'App\\Model\\POS\\Delivery';

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        $PreDeliveeryNo = "DL" . $BranchCode;

        $record = $Delivery::select('id', 'delivery_no')
            ->where('branch_id', $branchID)
            ->where('delivery_no', 'LIKE', "{$PreDeliveeryNo}%")
            ->orderBy('delivery_no', 'DESC')
            ->first();

        if ($record) {
            $OldDeliveryNo = explode($PreDeliveeryNo, $record->delivery_no);
            $deliveryNo = $PreDeliveeryNo . sprintf("%05d", ($OldDeliveryNo[1] + 1));
        } else {
            $deliveryNo = $PreDeliveeryNo . sprintf("%05d", 1);
        }

        return $deliveryNo;
    }

    public static function generateCollectionNo($branchID = null)
    {
        $Counter = 00;
        $BranchT = 'App\\Model\\GNL\\Branch';
        $CollectionT = 'App\\Model\\POS\\Collection';

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        $PreColNo = "COL" . $BranchCode . $Counter;

        $record = $CollectionT::select(['id', 'collection_no'])
            ->where('branch_id', $branchID)
            ->where('collection_no', 'LIKE', "{$PreColNo}%")
            ->orderBy('collection_no', 'DESC')
            ->first();

        if ($record) {
            $OldCOlNoA = explode($PreColNo, $record->collection_no);
            $CollNo = $PreColNo . sprintf("%05d", ($OldCOlNoA[1] + 1));
        } else {
            $CollNo = $PreColNo . sprintf("%05d", 1);
        }

        return $CollNo;
    }

    public static function generateDayendNo($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $ModelT = "App\\Model\\POS\\DayEnd";

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        // $ldate = date('Ym');

        $PreBillNo = "DE" . $BranchCode;
        $record = $ModelT::select(['id', 'dayend_no'])
            ->where('branch_id', $branchID)
            ->where('dayend_no', 'LIKE', "{$PreBillNo}%")
            ->orderBy('dayend_no', 'DESC')
            ->first();

        if ($record) {
            $OldBillNoA = explode($PreBillNo, $record->dayend_no);
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
        }

        return $BillNo;
    }

    public static function generateMonthendNo($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $ModelT = "App\\Model\\POS\\MonthEnd";

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        // $ldate = date('Ym');

        $PreBillNo = "ME" . $BranchCode;
        $record = $ModelT::select(['id', 'monthend_no'])
            ->where('branch_id', $branchID)
            ->where('monthend_no', 'LIKE', "{$PreBillNo}%")
            ->orderBy('monthend_no', 'DESC')
            ->first();

        if ($record) {
            $OldBillNoA = explode($PreBillNo, $record->monthend_no);
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
        }

        return $BillNo;
    }
/** end Bill generator function  */
}
