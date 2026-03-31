<?php

namespace App\Http\Controllers\ACC;

/* Base Controller */
use App\Http\Controllers\Controller;
use App\Services\AccService as ACC;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use DateTime;
use DB;
use Illuminate\Http\Request;

/* Model Load Start */

// use App\Model\POS\Product;
/* Model Load End */

class DashboardController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $this->middleware('permission');
        return view('ACC.Dashboard.dashboard');
    }

    public function branchStatus(Request $request)
    {
        $totalData = 0;
        $totalFiltered = $totalData;
        $sl = 0;
        $branchData = array();

        $companyID = Common::getCompanyId();
        $branchID = Common::getBranchId();

        // // // System Today Date with DateTime Object
        $sysTodayDate = new DateTime(Common::systemCurrentDate($branchID, 'acc'));

        // // // // ---------------- Previous Month Calculation start
        $prevMonth = clone $sysTodayDate;
        $prevMonth->modify('-1 month');
        // // // // ------------ last day of month (Y-m-t)

        // dd($sysTodayDate, $prevMonth, $prevMonth->format('Y-m-t'));
        // // // Cash & Bank all branch
        $curMonthData = ACC::blCalculationCB(true, $sysTodayDate->format('Y-m-')."01", $sysTodayDate->format('Y-m-d'));
        
        $preMonthData = ACC::blCalculationCB(true, $prevMonth->format('Y-m-')."01", $prevMonth->format('Y-m-t'));

        // dd($curMonthData, $preMonthData);
        // Query
        $branchData = DB::table('gnl_branchs as gb')
            ->where([['gb.is_active', 1], ['gb.is_delete', 0], ['gb.is_approve', 1]])
            ->whereIn('gb.id', HRS::getUserAccesableBranchIds())
            ->where(function ($branchData) use ($companyID, $branchID) {
                // if (!empty($companyID)) {
                //     $branchData->where('gb.company_id', $companyID);
                // }

                if (!empty($branchID) && $branchID != 1) {
                    $branchData->where('gb.id', $branchID);
                }
            })
            ->leftjoin('acc_day_end as ade', function ($branchData) {
                $branchData->on('gb.id', 'ade.branch_id')
                    ->where('ade.is_active', 1)
                    ->where('ade.is_delete', 0);
            })
            ->select('gb.id',
                'gb.branch_name',
                'gb.branch_code',
                'gb.branch_opening_date',
                'gb.acc_start_date',
                'ade.branch_date'
            )
            
            ->orderBy('gb.branch_code','ASC')
            ->get();

        // dd($branchData);
        $DataSet = array();
        
        $ttl_pre_cash = 0;
        $ttl_pre_bank = 0;
        $ttl_cur_cash = 0;
        $ttl_cur_bank = 0;
        $ttl_balance = 0;

        foreach ($branchData as $row) {

            $branch_name = $row->branch_code . "-" . $row->branch_name;

            $prev_month_cash = 0;
            $prev_month_bank = 0;
            $cur_month_cash = 0;
            $cur_month_bank = 0;
            $cur_month_total = 0;

            if (isset($curMonthData[$branch_name])) {
                $cur_month_cash = $curMonthData[$branch_name]['Cash'];
                $cur_month_bank = $curMonthData[$branch_name]['Bank'];
                $cur_month_total = $curMonthData[$branch_name]['Total_Balance'];
            }

            if (isset($preMonthData[$branch_name])) {
                $prev_month_cash = $preMonthData[$branch_name]['Cash'];
                $prev_month_bank = $preMonthData[$branch_name]['Bank'];
            }

            $ttl_pre_cash += $prev_month_cash;
            $ttl_pre_bank += $prev_month_bank;
            $ttl_cur_cash += $cur_month_cash;
            $ttl_cur_bank += $cur_month_bank;
            $ttl_balance += $cur_month_total;


            $prev_month_cash = ($prev_month_cash != 0) ? number_format($prev_month_cash, 2) : '-';
            $prev_month_bank = ($prev_month_bank != 0) ? number_format($prev_month_bank, 2) : '-';

            $cur_month_cash = ($cur_month_cash != 0) ? number_format($cur_month_cash, 2) : '-';
            $cur_month_bank = ($cur_month_bank != 0) ? number_format($cur_month_bank, 2) : '-';
            $cur_month_total = ($cur_month_total != 0) ? number_format($cur_month_total, 2) : '-';

            $TempSet = array();

            //  $lag = (int)((strtotime($br_date) - strtotime($sysDate))/(60*60*24));

            if(!empty($row->branch_date)){
                $br_date = new DateTime($row->branch_date);
            }
            else{
                $br_date = new DateTime($row->acc_start_date);
            }

            
            $to_date = new DateTime();
            $lag = $to_date->diff($br_date)->format('%R%a');

            // dd($lag);

            if ($lag != 0) {
                $lagText = '<p style="color:red;">' . $lag . '</p>';
            } else {
                $lagText = abs($lag);
            }

            
            // dd($ttl_balance);
            $TempSet = [
                'sl' => ++$sl,
                'branch_name' => $branch_name,
                'soft_start_date' => (new DateTime($row->acc_start_date))->format('d-m-Y'),
                'branch_date' => $br_date->format('d-m-Y'),
                'pre_month_cash' => $prev_month_cash,
                'pre_month_bank' => $prev_month_bank,
                'cur_month_cash' => $cur_month_cash,
                'cur_month_bank' => $cur_month_bank,
                'total' => $cur_month_total,
                'progress' => '',
                'LAG' => $lagText,
            ];
            $DataSet[] = $TempSet;
        }

        $ttl_pre_cash = number_format($ttl_pre_cash, 2);
        $ttl_pre_bank = number_format($ttl_pre_bank, 2);
        $ttl_cur_cash = number_format($ttl_cur_cash, 2);
        $ttl_cur_bank = number_format($ttl_cur_bank, 2);
        $ttl_balance = number_format($ttl_balance, 2);

        // dd($TempSet);
        $json_data = array(
            "data" => $DataSet,
            'ttl_pre_cash'=> $ttl_pre_cash,
            'ttl_pre_bank'=> $ttl_pre_bank,
            'ttl_cur_cash'=> $ttl_cur_cash,
            'ttl_cur_bank'=> $ttl_cur_bank,
            'ttl_balance'=>  $ttl_balance,
        );

        echo json_encode($json_data);
        exit;
    }

    public function organizationStatus(Request $request)
    {
        $companyID = Common::getCompanyId();
        $branchID = Common::getBranchId();
        $selBranchID = ($branchID == 1) ? -1 : $branchID;

        $branchQuery = DB::table('gnl_branchs')
            ->where([['id', $branchID]])
            ->first();

        $projectID = ($branchQuery) ? $branchQuery->project_id : null;
        $projectTypeID = ($branchQuery) ? $branchQuery->project_type_id : null;

        // // // System Today Date with DateTime Object
        $sysTodayDate = new DateTime(Common::systemCurrentDate($branchID, 'acc'));

        // // // // ---------------- Previous Month Calculation start
        $prevMonth = clone $sysTodayDate;
        $prevMonth->modify('-1 month');
        // // // // ------------ last day of month (Y-m-t)
        $preMWorkDay = Common::systemMonthWorkingDay(null, $branchID, null, $prevMonth->format('Y-m-') . "01", $prevMonth->format('Y-m-t'));
        if (count($preMWorkDay) == 0) {
            while (count($preMWorkDay) == 0) {
                $prevMonth->modify('-1 month');
                $preMWorkDay = Common::systemMonthWorkingDay(null, $branchID, null, $prevMonth->format('Y-m-') . "01", $prevMonth->format('Y-m-t'));
            }
        }
        // // // // ------ Previous Month Calculation End

        // /// // ----- Current Year calculation
        $fiscalYearInfo = Common::systemFiscalYear($sysTodayDate->format('Y-m-d'));
        $cur_yr_start = new DateTime($fiscalYearInfo['fy_start_date']);
        $cur_yr_end = new DateTime($fiscalYearInfo['fy_end_date']);
        if ($sysTodayDate < $cur_yr_end) {
            $cur_yr_end = $sysTodayDate;
        }
        // // // // ------ End Current Year Calculation

        ///////////////////////////////////// Cash & Bank Amount ///////////////////////////////////
        $cashBankData = ACC::blCalculationCB(false, null, $sysTodayDate->format('Y-m-d'), $selBranchID, $companyID, $projectID, $projectTypeID);

        $current_cash_amount = number_format($cashBankData['Cash'], 2);
        $current_bank_amount = number_format($cashBankData['Bank'], 2);
        $total_balance = number_format($cashBankData['Total_Balance'], 2);
        //////////////////////////////////// End Cash & Bank ///////////////////////////////////////

        //////////////////////////////////// Surplus/Defeat = Income - Expenses Amount Calculation //////////////////////////////
        // // // IE = Income Expense
        $currentMonthIEData = ACC::blCalculationIE(false, $sysTodayDate->format('Y-m-') . "01", $sysTodayDate->format('Y-m-d'), $selBranchID, $companyID, $projectID, $projectTypeID);
        $lastMonthIEData = ACC::blCalculationIE(false, $prevMonth->format('Y-m-') . "01", $prevMonth->format('Y-m-t'), $selBranchID, $companyID, $projectID, $projectTypeID);
        $curYearIEData = ACC::blCalculationIE(false, $cur_yr_start->format('Y-m-d'), $cur_yr_end->format('Y-m-d'), $selBranchID, $companyID, $projectID, $projectTypeID);
        $cumulativeIEData = ACC::blCalculationIE(false, null, $sysTodayDate->format('Y-m-d'), $selBranchID, $companyID, $projectID, $projectTypeID);

        $current_month_surplus = number_format($currentMonthIEData['surplus_amount'], 2);
        $current_year_surplus = number_format($curYearIEData['surplus_amount'], 2);
        $last_month_surplus = number_format($lastMonthIEData['surplus_amount'], 2);
        $cumulative_surplus = number_format($cumulativeIEData['surplus_amount'], 2);

        //////////////////////////////////// End Surplus ///////////////////////////////////////

        return compact('current_month_surplus', 'current_year_surplus', 'last_month_surplus', 'cumulative_surplus',
            'current_cash_amount', 'current_bank_amount', 'total_balance');

    }

    public function graphSurplusBranch(Request $request)
    {
        $surplusPercent = array();

        $companyID = Common::getCompanyId();
        $branchID = Common::getBranchId();
        $selBranchID = ($branchID == 1) ? -1 : $branchID;

        $branchQuery = DB::table('gnl_branchs')
            ->where([['id', $branchID]])
            ->first();

        $projectID = ($branchQuery) ? $branchQuery->project_id : null;
        $projectTypeID = ($branchQuery) ? $branchQuery->project_type_id : null;

        $sysTodayDate = new DateTime(Common::systemCurrentDate($branchID, 'acc'));
        // /// // ----- Current Year calculation
        $fiscalYearInfo = Common::systemFiscalYear($sysTodayDate->format('Y-m-d'));

        $cur_yr_start = new DateTime($fiscalYearInfo['fy_start_date']);
        $cur_yr_end = new DateTime($fiscalYearInfo['fy_end_date']);

        if ($sysTodayDate < $cur_yr_end) {
            $cur_yr_end = $sysTodayDate;
        }
        // // // // ------ End Current Year Calculation

        // // // Surplus of current year all branch
        $curYearIEData = ACC::blCalculationIE(true, $cur_yr_start->format('Y-m-d'), $cur_yr_end->format('Y-m-d'),
            null, $companyID, $projectID, $projectTypeID);

        // // // Ignore All branch Total data
        // unset($curYearIEData[0]);

        // // Total Surplus
        $ttl_profit_or_loss = 0;
        if (isset($curYearIEData[0])) {
            $ttl_profit_or_loss = $curYearIEData[0]['surplus_amount'];
        }

        if ($ttl_profit_or_loss > 0) {
            $ttl_surplus = $ttl_profit_or_loss;
        }

        $branchData = DB::table('gnl_branchs as gb')
            ->where([['gb.is_active', 1], ['gb.is_delete', 0], ['gb.is_approve', 1]])
            ->whereIn('gb.id', HRS::getUserAccesableBranchIds())
            ->where(function ($branchData) use ($companyID, $projectID, $projectTypeID) {
                // if (!empty($companyID)) {
                //     $branchData->where('gb.company_id', $companyID);
                // }

                if (!empty($projectID)) {
                    $branchData->where('gb.project_id', $projectID);
                }

                if (!empty($projectTypeID)) {
                    $branchData->where('gb.project_type_id', $projectTypeID);
                }
            })
            ->get();

        // Get Branch Wise Surplus (In Percentage)
        foreach ($branchData as $row) {

            $branch_name = $row->branch_code . "-" . $row->branch_name;

            $profit_or_loss_branch = 0;
            if (isset($curYearIEData[$branch_name])) {
                $profit_or_loss_branch = $curYearIEData[$branch_name]['surplus_amount'];
            }

            if ($profit_or_loss_branch >= 0) {
                // $row->branch_name
                $surplusPercent[$branch_name] = 0;
                if ($profit_or_loss_branch > 0 && $ttl_profit_or_loss > 0) {
                    $surplusPercent[$branch_name] = $profit_or_loss_branch / $ttl_profit_or_loss * 100;
                }
            }
        }

        // Associative Array sort in Descending Order
        arsort($surplusPercent);

        // Get Surplus For Top 10 Branch
        $surplusPercent = array_slice($surplusPercent, 0, 10, true);

        // $surplusPercent = array();
        return compact('surplusPercent');
    }

    public function graphSurplusAll(Request $request)
    {
        $surplus = array();

        $companyID = Common::getCompanyId();
        $branchID = Common::getBranchId();
        $selBranchID = ($branchID == 1) ? -1 : $branchID;

        $branchQuery = DB::table('gnl_branchs')
            ->where([['id', $branchID]])
            ->first();

        $projectID = ($branchQuery) ? $branchQuery->project_id : null;
        $projectTypeID = ($branchQuery) ? $branchQuery->project_type_id : null;

        $sysTodayDate = new DateTime(Common::systemCurrentDate($branchID, 'acc'));
        // /// // ----- Current Year calculation
        $fiscalYearInfo = Common::systemFiscalYear($sysTodayDate->format('Y-m-d'));

        $cur_yr_start = new DateTime($fiscalYearInfo['fy_start_date']);
        $cur_yr_end = new DateTime($fiscalYearInfo['fy_end_date']);

        if ($sysTodayDate < $cur_yr_end) {
            $cur_yr_end = $sysTodayDate;
        }
        // // // // ------ End Current Year Calculation

        // // // Surplus of current year all branch
        $curYearIEData = ACC::blCalculationIE(true, $cur_yr_start->format('Y-m-d'), $cur_yr_end->format('Y-m-d'),
            null, $companyID, $projectID, $projectTypeID);

        // // // Ignore All branch Total data
        // unset($curYearIEData[0]);

        $branchData = DB::table('gnl_branchs as gb')
            ->where([['gb.is_active', 1], ['gb.is_delete', 0], ['gb.is_approve', 1]])
            ->whereIn('gb.id', HRS::getUserAccesableBranchIds())
            ->where(function ($branchData) use ($companyID, $projectID, $projectTypeID) {
                // if (!empty($companyID)) {
                //     $branchData->where('gb.company_id', $companyID);
                // }

                if (!empty($projectID)) {
                    $branchData->where('gb.project_id', $projectID);
                }

                if (!empty($projectTypeID)) {
                    $branchData->where('gb.project_type_id', $projectTypeID);
                }
            })
            ->get();

        foreach ($branchData as $row) {
            $branch_name = $row->branch_code . "-" . $row->branch_name;

            $surplus[$branch_name] = 0;
            if (isset($curYearIEData[$branch_name])) {
                $surplus[$branch_name] = $curYearIEData[$branch_name]['surplus_amount'];
            }
        }

        // // // sort associative arrays in descending order, according to the value
        arsort($surplus);

        return compact('surplus');
    }

    public function graphCashBankBranch(Request $request)
    {
        $brCashBank = array();

        $companyID = Common::getCompanyId();
        $branchID = Common::getBranchId();
        $selBranchID = ($branchID == 1) ? -1 : $branchID;

        $branchQuery = DB::table('gnl_branchs')
            ->where([['id', $branchID]])
            ->first();

        $projectID = ($branchQuery) ? $branchQuery->project_id : null;
        $projectTypeID = ($branchQuery) ? $branchQuery->project_type_id : null;

        $sysTodayDate = new DateTime(Common::systemCurrentDate($branchID, 'acc'));

        // // // Cash & Bank all branch
        $cashBankData = ACC::blCalculationCB(true, null, $sysTodayDate->format('Y-m-d'), null, $companyID, $projectID, $projectTypeID);

        // dd($cashBankData);
        // // // Ignore All branch Total data
        // unset($curYearIEData[0]);

        $branchData = DB::table('gnl_branchs as gb')
            ->where([['gb.is_active', 1], ['gb.is_delete', 0], ['gb.is_approve', 1]])
            ->whereIn('gb.id', HRS::getUserAccesableBranchIds())
            ->where(function ($branchData) use ($companyID, $projectID, $projectTypeID) {
                // if (!empty($companyID)) {
                //     $branchData->where('gb.company_id', $companyID);
                // }

                if (!empty($projectID)) {
                    $branchData->where('gb.project_id', $projectID);
                }

                if (!empty($projectTypeID)) {
                    $branchData->where('gb.project_type_id', $projectTypeID);
                }
            })
            ->get();

        foreach ($branchData as $row) {

            $branch_name = $row->branch_code . "-" . $row->branch_name;

            $brCashBank[$branch_name]['total'] = 0;
            $brCashBank[$branch_name]['cash'] = 0;
            $brCashBank[$branch_name]['bank'] = 0;

            if (isset($cashBankData[$branch_name])) {
                $brCashBank[$branch_name]['total'] = $cashBankData[$branch_name]['Total_Balance'];
                $brCashBank[$branch_name]['cash'] = $cashBankData[$branch_name]['Cash'];
                $brCashBank[$branch_name]['bank'] = $cashBankData[$branch_name]['Bank'];
            }
        }
        // // // sort associative arrays in descending order, according to the value
        arsort($brCashBank);

        return compact('brCashBank');
    }

    public function graphIncomeExpenseBranch(Request $request)
    {
        $brIncExp = array();

        $companyID = Common::getCompanyId();
        $branchID = Common::getBranchId();
        $selBranchID = ($branchID == 1) ? -1 : $branchID;

        $branchQuery = DB::table('gnl_branchs')
            ->where([['id', $branchID]])
            ->first();

        $projectID = ($branchQuery) ? $branchQuery->project_id : null;
        $projectTypeID = ($branchQuery) ? $branchQuery->project_type_id : null;

        $sysTodayDate = new DateTime(Common::systemCurrentDate($branchID, 'acc'));
        // /// // ----- Current Year calculation
        $fiscalYearInfo = Common::systemFiscalYear($sysTodayDate->format('Y-m-d'));

        $cur_yr_start = new DateTime($fiscalYearInfo['fy_start_date']);
        $cur_yr_end = new DateTime($fiscalYearInfo['fy_end_date']);

        if ($sysTodayDate < $cur_yr_end) {
            $cur_yr_end = $sysTodayDate;
        }
        // // // // ------ End Current Year Calculation

        // // // Surplus of current year all branch
        $curYearIEData = ACC::blCalculationIE(true, $cur_yr_start->format('Y-m-d'), $cur_yr_end->format('Y-m-d'),
            null, $companyID, $projectID, $projectTypeID);

        // // // Ignore All branch Total data
        // unset($curYearIEData[0]);

        $branchData = DB::table('gnl_branchs as gb')
            ->where([['gb.is_active', 1], ['gb.is_delete', 0], ['gb.is_approve', 1]])
            ->whereIn('gb.id', HRS::getUserAccesableBranchIds())
            ->where(function ($branchData) use ($companyID, $projectID, $projectTypeID) {
                // if (!empty($companyID)) {
                //     $branchData->where('gb.company_id', $companyID);
                // }

                if (!empty($projectID)) {
                    $branchData->where('gb.project_id', $projectID);
                }

                if (!empty($projectTypeID)) {
                    $branchData->where('gb.project_type_id', $projectTypeID);
                }
            })
            ->get();

        foreach ($branchData as $row) {

            $branch_name = $row->branch_code . "-" . $row->branch_name;

            $brIncExp[$branch_name]['surplus'] = 0;
            $brIncExp[$branch_name]['income'] = 0;
            $brIncExp[$branch_name]['expense'] = 0;

            if (isset($curYearIEData[$branch_name])) {
                $brIncExp[$branch_name]['surplus'] = $curYearIEData[$branch_name]['surplus_amount'];
                $brIncExp[$branch_name]['income'] = $curYearIEData[$branch_name]['income_amount'];
                $brIncExp[$branch_name]['expense'] = $curYearIEData[$branch_name]['expense_amount'];
            }
        }

        // // // sort associative arrays in descending order, according to the value
        arsort($brIncExp);

        // dd($brIncExp);

        return compact('brIncExp');
    }

}
