<?php

namespace App\Services;

use DateTime;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Session;

use App\Model\GNL\Branch;
use App\Model\BILL\Product;

use App\Services\CommonService as Common;

class BillService
{

    public static function generateAgreementNo($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        // $PurchaseMasterT = 'App\\Model\\BILL\\PurchaseMaster';

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        $preAgreementNo = "AGR" . $BranchCode;

        $record = DB::table('bill_agreement_m')
            ->select(['id', 'agreement_no'])
            ->where('branch_id', $branchID)
            ->where('agreement_no', 'LIKE', "{$preAgreementNo}%")
            ->orderBy('agreement_no', 'DESC')
            ->first();

        if ($record) {
            $oldAgreementNoA = explode($preAgreementNo, $record->agreement_no);
            $agreementNo = $preAgreementNo . sprintf("%05d", ($oldAgreementNoA[1] + 1));
        } else {
            $agreementNo = $preAgreementNo . sprintf("%05d", 1);
        }
        return $agreementNo;
    }

    public static function generateSoftwareAgreementNo($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        // $PurchaseMasterT = 'App\\Model\\BILL\\PurchaseMaster';

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        $preAgreementNo = "AGR" . $BranchCode;

        $record = DB::table('bill_software_agreement_m')
            ->select(['id', 'agreement_no'])
            ->where('branch_id', $branchID)
            ->where('agreement_no', 'LIKE', "{$preAgreementNo}%")
            ->orderBy('agreement_no', 'DESC')
            ->first();

        if ($record) {
            $oldAgreementNoA = explode($preAgreementNo, $record->agreement_no);
            $agreementNo = $preAgreementNo . sprintf("%05d", ($oldAgreementNoA[1] + 1));
        } else {
            $agreementNo = $preAgreementNo . sprintf("%05d", 1);
        }
        return $agreementNo;
    }

    public static function generateBillCash($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $ModelT = "App\\Model\\BILL\\BillMaster";

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        $PreBillNo = "BL-" . $BranchCode;
        $record = $ModelT::select(['id', 'bill_no'])
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

    public static function generateBillCollectionNo($branchID = null)
    {
        $Counter = 00;
        $BranchT = 'App\\Model\\GNL\\Branch';
        $CollectionT = 'App\\Model\\BILL\\Collection';

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_active', 1],['is_approve', 1], ['id', $branchID]])
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

    public static function agreementSchedule($companyID = null, $branchID = null, $somityID = null,
        $salesDate = null, $instType = null, $instMonth = null) {
        $govtHolidayModel = 'App\\Model\\GNL\\GovtHoliday';
        $comapnyHolidayModel = 'App\\Model\\GNL\\CompanyHoliday';
        $specialHolidayModel = 'App\\Model\\GNL\\SpecialHoliday';

        $companyID = (!empty($companyID)) ? $companyID : Common::getBranchId();
        $branchID = (!empty($branchID)) ? $branchID : Common::getBranchId();
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
}
