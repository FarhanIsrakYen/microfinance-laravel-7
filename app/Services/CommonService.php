<?php

namespace App\Services;

use App\Model\GNL\Branch;
use Auth;
use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Session;

class CommonService
{
    public static function getUserId()
    {
        $userInfo = Auth::user();
        $userID = $userInfo->id;

        return $userID;
    }

    public static function getRoleId($userID = null)
    {
        $userInfo = Auth::user();
        $roleID = $userInfo->sys_user_role_id;

        if (!empty($userID)) {
            $userInfo = DB::table('gnl_sys_users')->where('id', $userID)->first();
            $roleID = ($userInfo) ? $userInfo->sys_user_role_id : null;
        }

        return $roleID;
    }

    public static function getModuleId()
    {
        $CurrentRouteURI = Route::getCurrentRoute()->uri();
        $currentRouteURIAr = explode('/', $CurrentRouteURI);
        $moduleName = $currentRouteURIAr[0];

        $module_id = DB::table('gnl_sys_modules')
            ->where([['is_delete', 0], ['is_active', 1]])->where('route_link', $moduleName)
            ->first()->id;

        if ($module_id) {
            return $module_id;
        } else {
            return false;
        }
    }

    public static function isSuperUser($userID = null)
    {
        $userInfo = Auth::user();
        $roleID = $userInfo->sys_user_role_id;

        if (!empty($userID)) {
            $userInfo = DB::table('gnl_sys_users')->where('id', $userID)->first();
            $roleID = ($userInfo) ? $userInfo->sys_user_role_id : null;
        }

        if ($roleID == 1) {
            return true;
        } else {
            return false;
        }
    }

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
     * Get Company ID from user login session
     */
    // public static function getCompanyId($userID = null)
    // {
    //     $userInfo = Auth::user();
    //     $companyID = $userInfo->company_id;

    //     if(!empty($userID)){
    //         $userInfo = DB::table('gnl_sys_users')->where('id', $userID)->first();
    //         $companyID = ($userInfo) ? $userInfo->company_id : null;
    //     }

    //     if ($companyID == '' || $companyID == null || empty($companyID)) {
    //         $companyID = 1;
    //     }

    //     return $companyID;
    // }

    /**
     * Get Branch ID from user login session
     */
    // public static function getBranchId($userID = null)
    // {
    //     $userInfo = Auth::user();
    //     $branchID = $userInfo->branch_id;

    //     if(!empty($userID)){
    //         $userInfo = DB::table('gnl_sys_users')->where('id', $userID)->first();
    //         $branchID = ($userInfo) ? $userInfo->branch_id : null;
    //     }

    //     if ($branchID == '' || $branchID == null || empty($branchID)) {
    //         $branchID = 1;
    //     }

    //     return $branchID;
    // }

    /**
     * Get Branch ID from user login session
     */
    public static function getCounterNo()
    {
        $counter_no = Session::get('LoginBy.user_config.counter_no');

        // if ($counter_no == '' || $counter_no == null || empty($counter_no)) {
        //     $counter_no = '00';
        // }

        return $counter_no;
    }

    /**
     * if findDate dewa hoy tahole branchID & selModule dite hobe na

     */
    public static function systemFiscalYear($findDate = null, $companyID = null, $branchID = null, $selModule = 'pos')
    {
        if ($companyID == null) {
            $companyID = self::getCompanyId();
        }

        if ($branchID == null) {
            $branchID = self::getBranchId();
        }

        if ($findDate == null) {
            $findDate = self::systemCurrentDate($branchID, $selModule);
        }

        $findDate = new DateTime($findDate);

        $fiscalQuery = DB::table('gnl_fiscal_year')
            ->where([['is_active', 1], ['is_delete', 0],
                ['company_id', $companyID],
                ['fy_start_date', '<=', $findDate->format('Y-m-d')],
                ['fy_end_date', '>=', $findDate->format('Y-m-d')],
            ])
            ->select('id', 'fy_name', 'fy_start_date', 'fy_end_date')
            ->first();

        $fiscalData = array();

        if ($fiscalQuery) {
            $fiscalData = [
                'id' => $fiscalQuery->id,
                'fy_name' => $fiscalQuery->fy_name,
                'fy_start_date' => $fiscalQuery->fy_start_date,
                'fy_end_date' => $fiscalQuery->fy_end_date,
            ];
        } else {
            $fiscalData = [
                'id' => 0,
                'fy_name' => "Jan-Dec",
                'fy_start_date' => $findDate->format('Y') . "-01-01",
                'fy_end_date' => $findDate->format('Y') . "-12-31",
            ];
        }
        return $fiscalData;
    }

    /**
     * Get Branch Software Opening date
     */
    public static function getBranchSoftwareStartDate($selBranch = null, $selModule = null)
    {
        if ($selBranch == '' || $selBranch == null || empty($selBranch)) {
            $branchID = self::getBranchId();
        } else {
            $branchID = $selBranch;
        }

        $CurrentRouteURI = Route::getCurrentRoute()->uri();
        $currentRouteURIAr = explode('/', $CurrentRouteURI);
        // echo $CurrentRouteURI."<br>";

        if ($selModule == '' || $selModule == null || empty($selModule)) {
            $moduleName = $currentRouteURIAr[0];
        } else {
            $moduleName = $selModule;
        }

        if ($moduleName == 'gnl') {
            $fieldName = false;
        } elseif ($moduleName == 'pos') {
            $fieldName = "soft_start_date";
        } elseif ($moduleName == 'acc') {
            $fieldName = "acc_start_date";
        } elseif ($moduleName == 'hr') {
            $fieldName = "hr_start_date";
        } elseif ($moduleName == 'mfn') {
            $fieldName = "mfn_start_date";
        } elseif ($moduleName == 'inv') {
            $fieldName = "inv_start_date";
        } elseif ($moduleName == 'bill') {
            $fieldName = "bill_start_date";
        } elseif ($moduleName == 'fam') {
            $fieldName = "fam_start_date";
        } elseif ($moduleName == 'proc') {
            $fieldName = "proc_start_date";
        } else {
            $fieldName = false;
        }

        if ($fieldName) {
            $BranchData = Branch::where('id', $branchID)->first();
            return $BranchData->$fieldName;
        } else {
            return null;
        }
    }

    /**
     * Get System Date Depending on Day End
     */
    public static function systemCurrentDate($selBranch = null, $selModule = null)
    {
        if ($selBranch == '' || $selBranch == null || empty($selBranch)) {
            $branchID = self::getBranchId();
        } else {
            $branchID = $selBranch;
        }

        $CurrentRouteURI = Route::getCurrentRoute()->uri();
        $currentRouteURIAr = explode('/', $CurrentRouteURI);
        // echo $CurrentRouteURI."<br>";

        if ($selModule == '' || $selModule == null || empty($selModule)) {
            $moduleName = $currentRouteURIAr[0];
        } else {
            $moduleName = $selModule;
        }

        $CurrentDate = new DateTime();
        $QueryFlag = true;

        // Table Name & Field

        if ($moduleName == 'gnl') {
            $QueryFlag = false;
            $tableName = false;
            $fieldName = false;
        } elseif ($moduleName == 'pos') {
            $tableName = "pos_day_end";
            $fieldName = "soft_start_date";
        } elseif ($moduleName == 'acc') {
            $tableName = "acc_day_end";
            $fieldName = "acc_start_date";
        } elseif ($moduleName == 'hr') {
            // $tableName = "hr_day_end";
            $tableName = false;
            $fieldName = "hr_start_date";
        } elseif ($moduleName == 'mfn') {
            $tableName = "mfn_day_end";
            $fieldName = "mfn_start_date";
        } elseif ($moduleName == 'inv') {
            $tableName = "inv_day_end";
            $fieldName = "inv_start_date";
        } elseif ($moduleName == 'bill') {
            $tableName = false;
            $fieldName = "bill_start_date";
        } elseif ($moduleName == 'fam') {
            $tableName = false;
            $fieldName = "fam_start_date";
        } elseif ($moduleName == 'proc') {
            $tableName = false;
            $fieldName = "proc_start_date";
        } else {
            $tableName = false;
            $fieldName = false;
            $QueryFlag = false;
        }

        if ($QueryFlag == true) {

            $day_end_empty = true;

            if ($tableName) {

                if ($moduleName == 'mfn') {
                    $DayEndData = DB::table($tableName)
                        ->where(['branchId' => $branchID, 'isActive' => 1])
                        ->first();
                } else {
                    $DayEndData = DB::table($tableName)
                        ->where(['branch_id' => $branchID, 'is_active' => 1])
                        ->first();
                }

                if ($DayEndData) {
                    $day_end_empty = false;
                }
            }

            if ($day_end_empty) {
                $BranchData = DB::table('gnl_branchs')
                    ->where(['id' => $branchID, 'is_approve' => 1])
                    ->first();

                if ($BranchData) {
                    if (!empty($BranchData->$fieldName)) {
                        $CurrentDate = new DateTime($BranchData->$fieldName);
                    }
                }
            } else {
                if ($moduleName == 'mfn') {
                    $CurrentDate = new DateTime($DayEndData->date);
                } else {
                    $CurrentDate = new DateTime($DayEndData->branch_date);
                }
            }
        }

        $CurrentDate = $CurrentDate->format('d-m-Y');

        return $CurrentDate;
    }

    /**
     * Get Next Working Date in System
     */
    public static function systemNextWorkingDay($currentDate, $selBranch = null, $selCompany = null)
    {
        if ($selBranch == '' || $selBranch == null || empty($selBranch)) {
            $branchID = self::getBranchId();
        } else {
            $branchID = $selBranch;
        }

        if ($selCompany == '' || $selCompany == null || empty($selCompany)) {
            $companyID = self::getCompanyId();
        } else {
            $companyID = $selCompany;
        }

        // $branchID = self::getBranchId();
        // $companyID = self::getCompanyId();

        $GovtHolidayModel = 'App\\Model\\GNL\\GovtHoliday';
        $ComapnyHolidayModel = 'App\\Model\\GNL\\CompanyHoliday';
        $SpecialHolidayModel = 'App\\Model\\GNL\\SpecialHoliday';

        $TempCurrentDate = new DateTime($currentDate);
        $TempNextDate = $TempCurrentDate->modify('+1 day');

        $HolidayFlag = true;

        while ($HolidayFlag == true) {
            $HolidayFlag = false;

            $TempNext = $TempNextDate->format('d-m');
            // This is for Half Day Name
            // $DayName = strtolower($TempNextDate->format('D'));

            // This is Full day name
            $DayName = $TempNextDate->format('l');

            $TempNextD = $TempNextDate->format('Y-m-d');

            $GovtHoliday = $GovtHolidayModel::where(['gh_date' => $TempNext, 'is_delete' => 0])->count();

            if ($GovtHoliday > 0) {
                $HolidayFlag = true;
            } else {
                $CompanyArr = (!empty($companyID)) ? ['company_id', '=', $companyID] : ['company_id', '<>', ''];

                $CompanyHoliday = $ComapnyHolidayModel::where(['is_delete' => 0])
                    ->where('ch_eff_date', '<=', $TempNextD)
                    ->where([$CompanyArr])
                    ->where(function ($CompanyHoliday) use ($DayName) {
                        $CompanyHoliday->where('ch_day', 'LIKE', "{$DayName}")
                            ->orWhere('ch_day', 'LIKE', "%,{$DayName},%")
                            ->orWhere('ch_day', 'LIKE', "%,{$DayName}%")
                            ->orWhere('ch_day', 'LIKE', "%{$DayName},%");
                    })
                    ->count();

                if ($CompanyHoliday > 0) {
                    $HolidayFlag = true;
                } else {
                    $SpecialHolidayORG = $SpecialHolidayModel::where(['sh_app_for' => 'org', 'is_delete' => 0])
                        ->where('sh_date_from', '<=', $TempNextD)
                        ->where('sh_date_to', '>=', $TempNextD)
                        ->count();

                    if ($SpecialHolidayORG > 0) {
                        $HolidayFlag = true;
                    } else {
                        $SpecialHolidayBranch = $SpecialHolidayModel::where(['sh_app_for' => 'branch', 'is_delete' => 0])
                            ->where('branch_id', '=', $branchID)
                            ->where('sh_date_from', '<=', $TempNextD)
                            ->where('sh_date_to', '>=', $TempNextD)
                            ->count();

                        if ($SpecialHolidayBranch > 0) {
                            $HolidayFlag = true;
                        }
                    }
                }
            }

            if ($HolidayFlag == true) {
                $TempNextDate = $TempNextDate->modify('+1 day');
            }
        }

        $currentDate = $TempNextDate->format('Y-m-d');

        return $currentDate;
    }

    /**
     * Get Next Working Date in System
     */
    public static function systemPreWorkingDay($currentDate, $selBranch = null, $selCompany = null)
    {
        if ($selBranch == '' || $selBranch == null || empty($selBranch)) {
            $branchID = self::getBranchId();
        } else {
            $branchID = $selBranch;
        }

        if ($selCompany == '' || $selCompany == null || empty($selCompany)) {
            $companyID = self::getCompanyId();
        } else {
            $companyID = $selCompany;
        }

        // $branchID = self::getBranchId();
        // $companyID = self::getCompanyId();

        $GovtHolidayModel = 'App\\Model\\GNL\\GovtHoliday';
        $ComapnyHolidayModel = 'App\\Model\\GNL\\CompanyHoliday';
        $SpecialHolidayModel = 'App\\Model\\GNL\\SpecialHoliday';

        $TempCurrentDate = new DateTime($currentDate);
        $TempNextDate = $TempCurrentDate->modify('-1 day');

        $HolidayFlag = true;

        while ($HolidayFlag == true) {
            $HolidayFlag = false;

            $TempNext = $TempNextDate->format('d-m');
            // This is for Half Day Name
            // $DayName = strtolower($TempNextDate->format('D'));

            // This is Full day name
            $DayName = $TempNextDate->format('l');

            $TempNextD = $TempNextDate->format('Y-m-d');

            $GovtHoliday = $GovtHolidayModel::where(['gh_date' => $TempNext, 'is_delete' => 0])->count();

            if ($GovtHoliday > 0) {
                $HolidayFlag = true;
            } else {
                $CompanyArr = (!empty($companyID)) ? ['company_id', '=', $companyID] : ['company_id', '<>', ''];

                $CompanyHoliday = $ComapnyHolidayModel::where(['is_delete' => 0])
                    ->where('ch_eff_date', '<=', $TempNextD)
                    ->where([$CompanyArr])
                    ->where(function ($CompanyHoliday) use ($DayName) {
                        $CompanyHoliday->where('ch_day', 'LIKE', "{$DayName}")
                            ->orWhere('ch_day', 'LIKE', "%,{$DayName},%")
                            ->orWhere('ch_day', 'LIKE', "%,{$DayName}%")
                            ->orWhere('ch_day', 'LIKE', "%{$DayName},%");
                    })
                    ->count();

                if ($CompanyHoliday > 0) {
                    $HolidayFlag = true;
                } else {
                    $SpecialHolidayORG = $SpecialHolidayModel::where(['sh_app_for' => 'org', 'is_delete' => 0])
                        ->where('sh_date_from', '<=', $TempNextD)
                        ->where('sh_date_to', '>=', $TempNextD)
                        ->count();

                    if ($SpecialHolidayORG > 0) {
                        $HolidayFlag = true;
                    } else {
                        $SpecialHolidayBranch = $SpecialHolidayModel::where(['sh_app_for' => 'branch', 'is_delete' => 0])
                            ->where('branch_id', '=', $branchID)
                            ->where('sh_date_from', '<=', $TempNextD)
                            ->where('sh_date_to', '>=', $TempNextD)
                            ->count();

                        if ($SpecialHolidayBranch > 0) {
                            $HolidayFlag = true;
                        }
                    }
                }
            }

            if ($HolidayFlag == true) {
                $TempNextDate = $TempNextDate->modify('-1 day');
            }
        }

        $currentDate = $TempNextDate->format('Y-m-d');

        return $currentDate;
    }

    /**
     * Get system Month Working Days
     * @param companyID @type int
     * @param branchID @type int
     * @param somityID @type int
     * @param startDate @type string '02-02-2020' or '2020-02-02'
     * @param endDate @type string '02-02-2020' or '2020-02-02'
     * @param period @type string '2 day' or '2 month' or '2 year'
     *
     * @Condition
     * startDate != null && endDate != null && period == null
     * startDate != null && endDate == null && period == null (Auto Calculate last day of month)
     * startDate != null && endDate == null && period != null  (Auto calculate last day depend on period(+))
     * startDate == null && endDate != null && period == null  (Auto calculate first day of month is 01)
     * startDate == null && endDate != null && period != null (Auto calculate first day depend on period(-))
     * startDate == null && endDate == null && period != null (Get System Current date as first day & last day calculate depend on period(+))
     * startDate == null && endDate == null && period == null (Get System Current date as first day & last day calculate depend on month)
     *
     * Calling: Common::systemMonthWorkingDay(companyID,branchID,somityID,startDate,endDate, period)
     */

    public static function systemMonthWorkingDay($companyID = null, $branchID = null, $somityID = null, $startDate = null, $endDate = null, $period = null)
    {
        $companyID = (!empty($companyID)) ? $companyID : self::getBranchId();
        $branchID = (!empty($branchID)) ? $branchID : self::getBranchId();
        $somityID = (!empty($somityID)) ? $somityID : 1;

        $companyID = (!empty($companyID)) ? $companyID : 1;

        $govtHolidayModel = 'App\\Model\\GNL\\GovtHoliday';
        $comapnyHolidayModel = 'App\\Model\\GNL\\CompanyHoliday';
        $specialHolidayModel = 'App\\Model\\GNL\\SpecialHoliday';

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
        } elseif (!empty($startDate) && empty($endDate) && empty($period)) {
            $fromDate = new DateTime($startDate);
            // Count day of curent month
            $lastday = cal_days_in_month(CAL_GREGORIAN, $fromDate->format('m'), $fromDate->format('Y'));
            $toDate = new DateTime($lastday . "-" . $fromDate->format('m') . "-" . $fromDate->format('Y'));
        } elseif (!empty($startDate) && empty($endDate) && !empty($period)) {
            $fromDate = new DateTime($startDate);
            $tempDate = clone $fromDate;
            $toDate = $tempDate->modify('+' . $period);
        } elseif (empty($startDate) && !empty($endDate) && empty($period)) {
            $fromDate = new DateTime("01-" . $fromDate->format('m') . "-" . $fromDate->format('Y'));
            $toDate = new DateTime($endDate);
        } elseif (empty($startDate) && !empty($endDate) && !empty($period)) {
            $toDate = new DateTime($endDate);
            $tempDate = clone $toDate;
            $fromDate = $tempDate->modify('-' . $period);
        } elseif (empty($startDate) && empty($endDate) && !empty($period)) {
            $fromDate = new DateTime(self::systemCurrentDate());
            $tempDate = clone $fromDate;
            $toDate = $tempDate->modify('+' . $period);
        } elseif (empty($startDate) && empty($endDate) && empty($period)) {
            $fromDate = new DateTime(self::systemCurrentDate());
            // Count day of curent month
            $lastday = cal_days_in_month(CAL_GREGORIAN, $fromDate->format('m'), $fromDate->format('Y'));
            $toDate = new DateTime($lastday . "-" . $fromDate->format('m') . "-" . $fromDate->format('Y'));
        }

        $workingDays = array();

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

            $tempLoopDate = clone $fromDate;
            while ($tempLoopDate <= $toDate) {
                $workdayFlag = true;

                // Fixed Govt Holiday Check
                foreach ($fixedGovtHoliday as $RowFG) {
                    if ($RowFG['gh_date'] == $tempLoopDate->format('d-m')) {
                        $workdayFlag = false;
                    }
                }

                // Company Holiday Check
                if ($workdayFlag == true) {
                    foreach ($companyHolidays as $RowC) {
                        $ch_day = $RowC['ch_day'];

                        $ch_day_arr = explode(',', $RowC['ch_day']);
                        $ch_eff_date = new DateTime($RowC['ch_eff_date']);

                        // This is Full day name
                        $dayName = $tempLoopDate->format('l');

                        if (in_array($dayName, $ch_day_arr) && ($ch_eff_date <= $tempLoopDate)) {
                            $workdayFlag = false;
                        }
                    }
                }

                // Special Holiday Org check
                if ($workdayFlag == true) {
                    foreach ($sHolidaysORG as $RowO) {
                        $sh_date_from = new DateTime($RowO['sh_date_from']);
                        $sh_date_to = new DateTime($RowO['sh_date_to']);

                        if (($sh_date_from <= $tempLoopDate) && ($sh_date_to >= $tempLoopDate)) {
                            $workdayFlag = false;
                        }
                    }
                }

                // Special Holiday Branch check
                if ($workdayFlag == true) {
                    foreach ($sHolidaysBr as $RowB) {
                        $sh_date_from_b = new DateTime($RowB['sh_date_from']);
                        $sh_date_to_b = new DateTime($RowB['sh_date_to']);

                        if (($sh_date_from_b <= $tempLoopDate) && ($sh_date_to_b >= $tempLoopDate)) {
                            $workdayFlag = false;
                        }
                    }
                }

                if ($workdayFlag == true) {
                    array_push($workingDays, $tempLoopDate->format('Y-m-d'));

                }
                $tempLoopDate = $tempLoopDate->modify('+1 day');
            }
        }
        return $workingDays;
    }

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

        $govtHolidayModel = 'App\\Model\\GNL\\GovtHoliday';
        $comapnyHolidayModel = 'App\\Model\\GNL\\CompanyHoliday';
        $specialHolidayModel = 'App\\Model\\GNL\\SpecialHoliday';

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

    public static function old_fileUpload($img_path, $image, $image_type)
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

    public static function fileUpload($image, $tableName, $uploadId, $fileName = null, $barcode = null, $barcode_name = null)
    {
        $img_path = "uploads/" . $tableName . "/" . $uploadId;
        if ($barcode) {
            $image_name = $barcode_name;
            $ext_img = 'png';
            $img_path .= "/barcode";
        } else {
            if (!empty($fileName)) {
                $image_name = $fileName;
            } else {
                $image_name = hexdec(uniqid());
            }
            $ext_img = strtolower($image->getClientOriginalExtension());
        }
        $image_full_name = $image_name . '.' . $ext_img;

        if (!is_dir($img_path)) {
            mkdir($img_path, 0777, true);
        }

        $image_url = $img_path . "/" . $image_full_name;

        if ($barcode) {
            $isUpload = file_put_contents($image_url, $image);
            // $isUpload = true;
        } else {
            // $isUpload = Storage::putFileAs($image_url, $image, $image_full_name);

            /* Demo, do not touch this code
            $file = $request->file('image');

            //Display File Name
            echo 'File Name: '.$file->getClientOriginalName();
            echo '<br>';

            //Display File Extension
            echo 'File Extension: '.$file->getClientOriginalExtension();
            echo '<br>';

            //Display File Real Path
            echo 'File Real Path: '.$file->getRealPath();
            echo '<br>';

            //Display File Size
            echo 'File Size: '.$file->getSize();
            echo '<br>';

            //Display File Mime Type
            echo 'File Mime Type: '.$file->getMimeType();

            //Move Uploaded File
            $destinationPath = 'uploads';
            $file->move($destinationPath,$file->getClientOriginalName());
             */

            // $FileType = $image->getMimeType();

            // if (($FileType != "image/jpeg")
            //             && ($FileType != "image/pjpeg")
            //             && ($FileType != "image/jpg")
            //             && ($FileType != "image/png")
            //             && ($FileType != "image/gif")
            //             && ($FileType != "text/plain")
            //             && ($FileType != "application/pdf")
            //             && ($FileType != "application/vnd.openxmlformats-officedocument.wordprocessingml.document")
            //             && ($FileType != "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"))
            // {
            //     $isUpload = false;
            // } else {
            // $isUpload = $image->move($img_path, $image_full_name);
            // }

            // $isUpload = true;

            $isUpload = $image->move($img_path, $image_full_name);

        }

        if ($isUpload) {
            return $image_url;
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
    public static function ViewTableOrderNotIn($table = null, $option = [], $optionIn = [], $select = [], $order = [])
    { //for all view
        $data = DB::table($table)
            ->select($select)
            ->where($option)
            ->whereNotIn($optionIn[0], $optionIn[1])
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
            ->whereIn($optionIn[0], $optionIn[1])
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
            ->whereIn($optionIn[0], $optionIn[1])
            ->first();
        return $data;
    }

    /* -------------------------------------------------------------------- generate bill start */

    public static function generateCustomerNo($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $ModelT = "App\\Model\\POS\\Customer";

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        // $ldate = date('Ym');

        $PreBillNo = "CUS" . $BranchCode;
        $record = $ModelT::select(['id', 'customer_no'])
            ->where('branch_id', $branchID)
            ->where('customer_no', 'LIKE', "{$PreBillNo}%")
            ->orderBy('customer_no', 'DESC')
            ->first();

        if ($record) {
            $OldBillNoA = explode($PreBillNo, $record->customer_no);
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
        }

        return $BillNo;
    }

    public static function generateGuarantorNo($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $ModelT = "App\\Model\\POS\\Guarantor";

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        // $ldate = date('Ym');

        $PreBillNo = "GUR" . $BranchCode;
        $record = $ModelT::select(['id', 'guarantor_no'])
        // ->where('branch_id', $branchID)
            ->where('guarantor_no', 'LIKE', "{$PreBillNo}%")
            ->orderBy('guarantor_no', 'DESC')
            ->first();

        if ($record) {
            $OldBillNoA = explode($PreBillNo, $record->guarantor_no);
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
        }

        return $BillNo;
    }
    public static function generateEmployeeNo($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $ModelT = "App\\Model\\HR\\Employee";

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        // $ldate = date('Ym');

        $PreBillNo = "EMP" . $BranchCode;
        $record = $ModelT::select(['id', 'employee_no'])
            ->where('branch_id', $branchID)
            ->where('employee_no', 'LIKE', "{$PreBillNo}%")
            ->orderBy('employee_no', 'DESC')
            ->first();

        if ($record) {
            $OldBillNoA = explode($PreBillNo, $record->employee_no);
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
        }

        return $BillNo;
    }

    /* --------------------------------------------------------------------- generate bill End */

    public static function getSignatureSettings($branchId = null)
    {
        if (is_null($branchId)) {
            $branchId = Auth::user()->branch_id;
        }

        $CurrentRouteURI = Route::getCurrentRoute()->uri();
        $currentRouteURIAr = explode('/', $CurrentRouteURI);
        $moduleName = $currentRouteURIAr[0];

        $module_id = DB::table('gnl_sys_modules')
            ->where([['is_delete', 0], ['is_active', 1]])->where('route_link', $moduleName)
            ->first()->id;

        // dd($module_id);

        $Model = 'App\\Model\\GNL\\SignatureSettings';
        if ($branchId == 1) {

            $QuerryData = $Model::where('is_delete', 0)->where('status', 1)->where('module_id', $module_id)->where('applicableFor', 'HeadOffice')->orderBy('positionOrder')->get();

        } else {
            $QuerryData = $Model::where('is_delete', 0)->where('status', 1)->where('module_id', $module_id)->where('applicableFor', 'Branch')->orderBy('positionOrder')->get();

        }
        // dd($QuerryData);

        return $QuerryData;
    }

    public static function getSignatureEmployee($branchId = null, $designation = null, $employeeID = null)
    {
        if (is_null($branchId)) {
            $branchId = Auth::user()->branch_id;
        }

        if (!empty($designation)) {
            $Model = 'App\\Model\\HR\\Employee';
            // dd($designation);
            $QuerryData = $Model::where('branch_id', $branchId)->where('designation_id', $designation)->first();

            // dd($QuerryData);

            if (!empty($QuerryData)) {
                return $QuerryData->emp_name;
            } else {
                return '';
            }
        }

        if (!empty($employeeID)) {
            $Model = 'App\\Model\\HR\\Employee';
            $QuerryData = $Model:://where('hr_employees.branch_id',$branchId)->
                where('hr_employees.id', $employeeID)
                ->leftJoin('hr_designations', 'hr_designations.id', 'hr_employees.designation_id')->select('hr_designations.name')
                ->first();
            // dd($QuerryData);
            // $QuerryData = $Model::where('hr_employees.branch_id',$branchId)->where('hr_employees.id', $employeeID)
            // ->leftJoin('hr_designations', 'hr_designations.id', 'hr_employees.designation_id')->select('hr_designations.name')->first();

            //  dd($QuerryData->name);

            if (!empty($QuerryData)) {
                return $QuerryData->name;
            } else {
                return '';
            }
        }

        // dd($QuerryData);

    }

    public static function numberToWord($Number = null)
    {

        $my_number = $Number;
        if (($Number < 0) || ($Number > 999999999)) {
            throw new Exception("Number is out of range");
        }
        $Kt = floor($Number / 10000000); /* Koti */
        $Number -= $Kt * 10000000;
        $Gn = floor($Number / 100000); /* lakh  */
        $Number -= $Gn * 100000;
        $kn = floor($Number / 1000); /* Thousands (kilo) */
        $Number -= $kn * 1000;
        $Hn = floor($Number / 100); /* Hundreds (hecto) */
        $Number -= $Hn * 100;
        $Dn = floor($Number / 10); /* Tens (deca) */
        $n = $Number % 10; /* Ones */
        $res = "";
        if ($Kt) {
            $res .= self::numberToWord($Kt) . " Crore "; /* Koti */
        }
        if ($Gn) {
            $res .= self::numberToWord($Gn) . " Lac"; /* Lakh */
        }
        if ($kn) {
            $res .= (empty($res) ? "" : " ") .
            self::numberToWord($kn) . " Thousand";
        }
        if ($Hn) {
            $res .= (empty($res) ? "" : " ") .
            self::numberToWord($Hn) . " Hundred";
        }
        $ones = array("", "One", "Two", "Three", "Four", "Five", "Six",
            "Seven", "Eight", "Nine", "Ten", "Eleven", "Twelve", "Thirteen",
            "Fourteen", "Fifteen", "Sixteen", "Seventeen", "Eightteen",
            "Nineteen");
        $tens = array("", "", "Twenty", "Thirty", "Fourty", "Fifty", "Sixty",
            "Seventy", "Eigthy", "Ninety");
        if ($Dn || $n) {
            if (!empty($res)) {
                $res .= " and ";
            }
            if ($Dn < 2) {
                $res .= $ones[$Dn * 10 + $n];
            } else {
                $res .= $tens[$Dn];
                if ($n) {
                    $res .= "-" . $ones[$n];
                }
            }
        }
        if (empty($res)) {
            $res = "zero";
        }
        return $res . " Only";
    }

}
