<?php

namespace App\Http\Controllers\ACC;

use App\Http\Controllers\Controller;
use App\Services\AccService as ACC;
use App\Services\CommonService as Common;
use DateTime;
use DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();

        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();

        /* This public array use in Trail Balance Report */
        $this->PublicLedger = array();
        $this->accountSet = array();
    }

    // Profit/Loss from income statement
    public function funcIncomeStatememnt($startDate, $endDate, $ledgerChilds = [], $branchID = null,
        $projectID = null, $projectTypeID = null) {
        $debit = 0;
        $credit = 0;
        $income = 0;
        $expense = 0;

        foreach ($ledgerChilds as $row) {

            if ($row->acc_type_id == 12 || $row->acc_type_id == 13) {

                $incomeStatement = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($incomeStatement) use ($startDate, $endDate) {
                        if (!empty($startDate) && !empty($endDate)) {
                            $incomeStatement->whereBetween('av.voucher_date', [$startDate, $endDate]);
                        }
                    })
                    ->where(function ($incomeStatement) use ($branchID) {
                        if (!empty($branchID)) {
                            $incomeStatement->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($incomeStatement) use ($projectID) {
                        if (!empty($projectID)) {
                            $incomeStatement->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($incomeStatement) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $incomeStatement->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($incomeStatement) {
                        $incomeStatement->on('avd.voucher_id', 'av.id');
                    })
                    ->select(
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDate . '" and av.voucher_date <= "' . $endDate . '"
                                    and avd.debit_acc = "' . $row->id . '" and "' . $row->acc_type_id = 12 . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_debit_income,
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDate . '" and av.voucher_date <= "' . $endDate . '"
                                    and avd.credit_acc = "' . $row->id . '" and "' . $row->acc_type_id = 12 . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_credit_income,
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDate . '" and av.voucher_date <= "' . $endDate . '"
                                    and avd.debit_acc = "' . $row->id . '" and "' . $row->acc_type_id = 13 . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_debit_expense,
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDate . '" and av.voucher_date <= "' . $endDate . '"
                                    and avd.credit_acc = "' . $row->id . '" and "' . $row->acc_type_id = 13 . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_credit_expense'
                        )
                    )
                    ->orderBy('av.voucher_date', 'ASC')
                    ->get();

                if ($row->acc_type_id == 12) {
                    $debit_income = $incomeStatement->sum('sum_debit_income');
                    $credit_income = $incomeStatement->sum('sum_credit_income');
                    $income += $credit_income - $debit_income;
                } else if ($row->acc_type_id == 13) {
                    $debit_expense = $incomeStatement->sum('sum_debit_expense');
                    $credit_expense = $incomeStatement->sum('sum_credit_expense');
                    $expense += $debit_expense - $credit_expense;
                }
            }

        }
        $income_statement = $income - $expense;
        return $income_statement;
    }

    public function openingBalanceDateRange($startDate, $endDate, $ledgerIds = [], $branchID = null,
        $projectID = null, $projectTypeID = null) {
        $OBQuery = DB::table('acc_ob_m as aom')
            ->where([['aom.is_delete', 0], ['aom.is_active', 1], ['aom.is_year_end', 0]])
            ->where(function ($OBQuery) use ($branchID) {
                if (!empty($branchID)) {
                    if ($branchID >= 0) {
                        $OBQuery->where('aom.branch_id', $branchID); // Individual Branch
                    } else if ($branchID == -2) {
                        $OBQuery->where('aom.branch_id', '!=', 1); // Branch without head office
                    }
                }
            })
            ->where(function ($OBQuery) use ($projectID) {
                if (!empty($projectID)) {
                    $OBQuery->where('aom.project_id', $projectID);
                }
            })
            ->where(function ($OBQuery) use ($projectTypeID) {
                if (!empty($projectTypeID)) {
                    $OBQuery->where('aom.project_type_id', $projectTypeID);
                }
            })
            ->leftjoin('acc_ob_d as aod', function ($OBQuery) use ($ledgerIds) {
                $OBQuery->on('aod.ob_no', 'aom.ob_no')
                    ->where(function ($OBQuery) use ($ledgerIds) {
                        $OBQuery->whereIn('aod.ledger_id', $ledgerIds);
                    });
            })
            ->select(DB::raw('IFNULL(SUM(aod.debit_amount),0) - IFNULL(SUM(aod.credit_amount),0) as balance'))
            ->orderBy('aom.id', 'ASC')
            ->groupBy('aod.ledger_id')
            ->get();

        $debitDateRange = DB::table('acc_voucher as av')
            ->where([['av.is_delete', 0], ['av.is_active', 1]])
            ->whereIn('av.voucher_status', [1, 2])
        // ->whereBetween('av.voucher_date',[$startDate, $endDate])
            ->where('av.voucher_date', '>=', $startDate)
            ->where('av.voucher_date', '<', $endDate)
            ->where(function ($debitDateRange) use ($branchID) {
                if (!empty($branchID)) {
                    if ($branchID >= 0) {
                        $debitDateRange->where('av.branch_id', $branchID); // Individual Branch
                    } else if ($branchID == -2) {
                        $debitDateRange->where('av.branch_id', '!=', 1); // Branch without head office
                    }
                }
            })
            ->where(function ($debitDateRange) use ($projectID) {
                if (!empty($projectID)) {
                    $debitDateRange->where('av.project_id', $projectID);
                }
            })
            ->where(function ($debitDateRange) use ($projectTypeID) {
                if (!empty($projectTypeID)) {
                    $debitDateRange->where('av.project_type_id', $projectTypeID);
                }
            })
            ->join('acc_voucher_details as avd', function ($debitDateRange) use ($ledgerIds) {
                $debitDateRange->on('avd.voucher_id', 'av.id')
                    ->where(function ($debitDateRange) use ($ledgerIds) {
                        $debitDateRange->whereIn('avd.debit_acc', $ledgerIds);
                    });
            })
            ->groupBy('avd.debit_acc')
            ->select(DB::raw('IFNULL(SUM(avd.amount),0) as debit_amount'))
            ->get();

        $creditDateRange = DB::table('acc_voucher as av')
            ->where([['av.is_delete', 0], ['av.is_active', 1]])
            ->whereIn('av.voucher_status', [1, 2])
        // ->whereBetween('av.voucher_date',[$startDate, $endDate])
            ->where('av.voucher_date', '>=', $startDate)
            ->where('av.voucher_date', '<', $endDate)
            ->where(function ($creditDateRange) use ($branchID) {
                if (!empty($branchID)) {
                    if ($branchID >= 0) {
                        $creditDateRange->where('av.branch_id', $branchID); // Individual Branch
                    } else if ($branchID == -2) {
                        $creditDateRange->where('av.branch_id', '!=', 1); // Branch without head office
                    }
                }
            })
            ->where(function ($creditDateRange) use ($projectID) {
                if (!empty($projectID)) {
                    $creditDateRange->where('av.project_id', $projectID);
                }
            })
            ->where(function ($creditDateRange) use ($projectTypeID) {
                if (!empty($projectTypeID)) {
                    $creditDateRange->where('av.project_type_id', $projectTypeID);
                }
            })
            ->join('acc_voucher_details as avd', function ($creditDateRange) use ($ledgerIds) {
                $creditDateRange->on('avd.voucher_id', 'av.id')
                    ->where(function ($creditDateRange) use ($ledgerIds) {
                        $creditDateRange->whereIn('avd.credit_acc', $ledgerIds);
                    });
            })
            ->groupBy('avd.credit_acc')
            ->select(DB::raw('IFNULL(SUM(avd.amount),0) as credit_amount'))
            ->get();

        $ob_date_range = ($debitDateRange->sum('debit_amount') - $creditDateRange->sum('credit_amount')) +
        $OBQuery->sum('balance');
        return $ob_date_range;

    }

    public function receiptDateRange($startDate, $endDate, $ledgerIds = [], $branchID = null,
        $projectID = null, $projectTypeID = null) {
        $receipt_date_range = DB::table('acc_voucher as av')
            ->where([['av.is_delete', 0], ['av.is_active', 1]])
            ->whereIn('av.voucher_status', [1, 2])
            ->whereBetween('av.voucher_date', [$startDate, $endDate])
            ->where(function ($receipt_date_range) use ($branchID) {
                if (!empty($branchID)) {
                    if ($branchID >= 0) {
                        $receipt_date_range->where('av.branch_id', $branchID); // Individual Branch
                    } else if ($branchID == -2) {
                        $receipt_date_range->where('av.branch_id', '!=', 1); // Branch without head office
                    }
                }
            })
            ->where(function ($receipt_date_range) use ($projectID) {
                if (!empty($projectID)) {
                    $receipt_date_range->where('av.project_id', $projectID);
                }
            })
            ->where(function ($receipt_date_range) use ($projectTypeID) {
                if (!empty($projectTypeID)) {
                    $receipt_date_range->where('av.project_type_id', $projectTypeID);
                }
            })
            ->join('acc_voucher_details as avd', function ($receipt_date_range) use ($ledgerIds) {
                $receipt_date_range->on('avd.voucher_id', 'av.id')
                    ->where(function ($receipt_date_range) use ($ledgerIds) {
                        $receipt_date_range->whereIn('avd.debit_acc', $ledgerIds);
                    });
            })
            ->distinct('avd.debit_acc')
            ->select(DB::raw('IFNULL(SUM(avd.amount),0) as debit_amount'))
            ->get();

        return $receipt_date_range->sum('debit_amount');

    }

    public function paymentDateRange($startDate, $endDate, $ledgerIds = [], $branchID = null,
        $projectID = null, $projectTypeID = null) {

        $paymentDateRange = DB::table('acc_voucher as av')
            ->where([['av.is_delete', 0], ['av.is_active', 1]])
            ->whereIn('av.voucher_status', [1, 2])
            ->whereBetween('av.voucher_date', [$startDate, $endDate])
            ->where(function ($paymentDateRange) use ($branchID) {
                if (!empty($branchID)) {
                    if ($branchID >= 0) {
                        $paymentDateRange->where('av.branch_id', $branchID); // Individual Branch
                    } else if ($branchID == -2) {
                        $paymentDateRange->where('av.branch_id', '!=', 1); // Branch without head office
                    }
                }
            })
            ->where(function ($paymentDateRange) use ($projectID) {
                if (!empty($projectID)) {
                    $paymentDateRange->where('av.project_id', $projectID);
                }
            })
            ->where(function ($paymentDateRange) use ($projectTypeID) {
                if (!empty($projectTypeID)) {
                    $paymentDateRange->where('av.project_type_id', $projectTypeID);
                }
            })
            ->join('acc_voucher_details as avd', function ($paymentDateRange) use ($ledgerIds) {
                $paymentDateRange->on('avd.voucher_id', 'av.id')
                    ->where(function ($paymentDateRange) use ($ledgerIds) {
                        $paymentDateRange->whereIn('avd.credit_acc', $ledgerIds);
                    });
            })
            ->distinct('avd.credit_acc')
            ->select(DB::raw('IFNULL(SUM(avd.amount),0) as credit_amount'))
            ->get();
        return $paymentDateRange->sum('credit_amount');

    }

    public function findParent($pid)
    {
        $pid = $pid;
        if ($pid != 0) {
            $temp = DB::table('acc_account_ledger as acl')
                ->where([['acl.is_delete', 0], ['acl.is_active', 1]])
                ->where('acl.id', $pid)
                ->select('acl.parent_id')
                ->first();

            $this->accountSet[] = $temp->parent_id;
            $this->findParent($temp->parent_id);
        } else {
            return $this->accountSet;
        }
    }

    public function getledger(Request $request)
    {

        if ($request->ajax()) {

            // Initialize  variable
            $ob_ttl_debit_amt = 0;
            $ob_ttl_credit_amt = 0;
            $ob_ttl_balance = 0;

            $sub_ttl_debit_amt = 0;
            $sub_ttl_credit_amt = 0;
            $sub_ttl_balance = 0;
            $sub_ttl_dr_or_cr = 'Dr';

            $ttl_debit_amt = 0;
            $ttl_credit_amt = 0;
            $ttl_balance = 0;
            $ttl_dr_or_cr = 'Dr';

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');

            $companyID = (empty($request->input('companyID'))) ? Common::getCompanyId() : $request->input('companyID');

            $projectID = (empty($request->input('projectID'))) ? null : $request->input('projectID');
            $projectTypeID = (empty($request->input('projectTypeID'))) ? null : $request->input('projectTypeID');
            $branchID = (empty($request->input('branchID'))) ? 1 : $request->input('branchID');
            $ledgerID = (empty($request->input('ledgerID'))) ? null : $request->input('ledgerID');
            $voucherTypeID = (empty($request->input('voucherTypeID'))) ? null : $request->input('voucherTypeID');

            $startDate = new DateTime($startDate);
            $startDate = $startDate->format('Y-m-d');

            $endDate = new DateTime($endDate);
            $endDate = $endDate->format('Y-m-d');

            ////////// ------Start------ Data Fetch & calculation For Opening Balance ////////////

            // // // Data Fetch from OB Tables for date range
            $obDateRange = DB::table('acc_ob_m as obm')
                ->where([['obm.is_delete', 0], ['obm.is_active', 1], ['obm.is_year_end', 0]])
                ->where(function ($obDateRange) use ($companyID) {
                    if (!empty($companyID)) {
                        $obDateRange->where('obm.company_id', $companyID);
                    }
                })
                ->where(function ($obDateRange) use ($branchID) {
                    if (!empty($branchID)) {

                        if ($branchID >= 0) {
                            $obDateRange->where('obm.branch_id', $branchID); // Individual Branch
                        } else if ($branchID == -2) {
                            $obDateRange->where('obm.branch_id', '!=', 1); // Branch without head office
                        }
                    }
                })
                ->where(function ($obDateRange) use ($projectID) {
                    if (!empty($projectID)) {
                        $obDateRange->where('obm.project_id', $projectID);
                    }
                })
                ->where(function ($obDateRange) use ($projectTypeID) {
                    if (!empty($projectTypeID)) {
                        $obDateRange->where('obm.project_type_id', $projectTypeID);
                    }
                })
                ->join('acc_ob_d as obd', function ($obDateRange) use ($ledgerID) {
                    $obDateRange->on('obd.ob_no', 'obm.ob_no')
                        ->where('obd.ledger_id', $ledgerID);
                })
                ->select(DB::raw('IFNULL(SUM(obd.debit_amount),0) as debit_amount,
                                IFNULL(SUM(obd.credit_amount),0) as credit_amount'))
                ->orderBy('obm.id', 'ASC')
                ->first();

            if ($obDateRange) {
                $ob_ttl_debit_amt = $obDateRange->debit_amount;
                $ob_ttl_credit_amt = $obDateRange->credit_amount;
            }
            // // // End OB Tables for date range

            // // // Data Fetch from Voucher Tables for date range & before start date
            $voucherBegData = DB::table('acc_voucher as av')
                ->where([['av.is_delete', 0], ['av.is_active', 1]])
                ->whereIn('av.voucher_status', [1, 2])
                ->where(function ($voucherBegData) use ($companyID, $startDate) {
                    // if (!empty($companyID)) {
                    //     $voucherBegData->where('av.company_id', $companyID);
                    // }

                    if (!empty($startDate)) {
                        $voucherBegData->where('av.voucher_date', '<', $startDate);
                    }
                })
                ->where(function ($voucherBegData) use ($branchID) {
                    if (!empty($branchID)) {
                        if ($branchID > 0) {
                            $voucherBegData->where('av.branch_id', $branchID); // Individual Branch
                        } else if ($branchID == -2) {
                            $voucherBegData->where('av.branch_id', '!=', 1); // Branch without head office
                        }
                    }
                })
                ->where(function ($voucherBegData) use ($projectID) {
                    if (!empty($projectID)) {
                        $voucherBegData->where('av.project_id', $projectID);
                    }
                })
                ->where(function ($voucherBegData) use ($projectTypeID) {
                    if (!empty($projectTypeID)) {
                        $voucherBegData->where('av.project_type_id', $projectTypeID);
                    }
                })
                ->where(function ($voucherBegData) use ($voucherTypeID) {
                    if (!empty($voucherTypeID)) {
                        $voucherBegData->where('av.voucher_type_id', $voucherTypeID);
                    }
                })
                ->join('acc_voucher_details as avd', function ($voucherBegData) use ($ledgerID) {
                    $voucherBegData->on('avd.voucher_id', 'av.id')
                        ->where(function ($voucherBegData) use ($ledgerID) {
                            $voucherBegData->where('avd.debit_acc', $ledgerID)
                                ->orWhere('avd.credit_acc', $ledgerID);
                        });
                })
                ->select(
                    DB::raw(
                        'IFNULL(CASE WHEN avd.debit_acc = ' . $ledgerID . ' THEN avd.amount END, 0) as debit_amount,
                        IFNULL(CASE WHEN avd.credit_acc = ' . $ledgerID . ' THEN avd.amount END, 0) as credit_amount'
                    )
                )
                ->orderBy('av.voucher_date', 'ASC')
                ->get();

            // if (isset($voucherBegData))
            if (count($voucherBegData->toarray()) > 0) {
                $ob_ttl_debit_amt += $voucherBegData->sum('debit_amount');
                $ob_ttl_credit_amt += $voucherBegData->sum('credit_amount');
            }
            // // // End Voucher Tables for date range & before start date

            // // // Calculation for Opening Balance

            $ob_ttl_balance = ($ob_ttl_debit_amt - $ob_ttl_credit_amt);
            $positive_ob_ttl_balance = $ob_ttl_balance;
            $positive_ob_ttl_balance = abs($positive_ob_ttl_balance);

            if ($ob_ttl_balance < 0) {
                $ob_ttl_credit_amt = $positive_ob_ttl_balance;
                $ob_ttl_debit_amt = 0;
            } else {
                $ob_ttl_credit_amt = 0;
                $ob_ttl_debit_amt = $positive_ob_ttl_balance;
            }
            ////////// ----End --- Data Fetch & calculation For Opening Balance ////////////

            ////////// ---Start-- Data Fetch & calculation For During Date range from vouchers table-------////////////

            $ledgerReport = DB::table('acc_voucher as av')
                ->where([['av.is_delete', 0], ['av.is_active', 1]])
                ->whereIn('av.voucher_status', [1, 2])
                ->where(function ($ledgerReport) use ($companyID, $startDate, $endDate) {
                    // if (!empty($companyID)) {
                    //     $ledgerReport->where('av.company_id', $companyID);
                    // }

                    if (!empty($startDate) && !empty($endDate)) {
                        $ledgerReport->whereBetween('av.voucher_date', [$startDate, $endDate]);
                    }
                })
                ->where(function ($ledgerReport) use ($branchID) {
                    if (!empty($branchID)) {
                        if ($branchID >= 0) {
                            $ledgerReport->where('av.branch_id', $branchID); // Individual Branch
                        } else if ($branchID == -2) {
                            $ledgerReport->where('av.branch_id', '!=', 1); // Branch without head office
                        }
                    }
                })
                ->where(function ($ledgerReport) use ($projectID) {
                    if (!empty($projectID)) {
                        $ledgerReport->where('av.project_id', $projectID);
                    }
                })
                ->where(function ($ledgerReport) use ($projectTypeID) {
                    if (!empty($projectTypeID)) {
                        $ledgerReport->where('av.project_type_id', $projectTypeID);
                    }
                })
                ->where(function ($ledgerReport) use ($voucherTypeID) {
                    if (!empty($voucherTypeID)) {
                        $ledgerReport->where('av.voucher_type_id', $voucherTypeID);
                    }
                })
                ->join('acc_voucher_details as avd', function ($ledgerReport) use ($ledgerID) {
                    $ledgerReport->on('avd.voucher_id', 'av.id')
                        ->where(function ($ledgerReport) use ($ledgerID) {
                            $ledgerReport->where('avd.debit_acc', $ledgerID)
                                ->orWhere('avd.credit_acc', $ledgerID);
                        });
                })
                ->join('acc_account_ledger as acl', function ($ledgerReport) use ($ledgerID) {
                    $ledgerReport->on(DB::raw('CASE
                                        WHEN avd.debit_acc = ' . $ledgerID . ' THEN avd.credit_acc
                                        WHEN avd.credit_acc = ' . $ledgerID . ' THEN avd.debit_acc
                                        END'), 'acl.id')
                        ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                })
                ->select('acl.name', 'avd.local_narration', 'av.voucher_date', 'av.voucher_code',
                    DB::raw(
                        'IFNULL(CASE WHEN avd.debit_acc = ' . $ledgerID . ' THEN avd.amount END, 0) as debit_amount,
                        IFNULL(CASE WHEN avd.credit_acc = ' . $ledgerID . ' THEN avd.amount END, 0) as credit_amount'
                    )
                )
                ->orderBy('av.voucher_date', 'ASC')
                ->get();

            if (count($ledgerReport->toarray()) > 0) {
                $sub_ttl_debit_amt = $ledgerReport->sum('debit_amount');
                $sub_ttl_credit_amt = $ledgerReport->sum('credit_amount');
            }

            $ttl_debit_amt = $sub_ttl_debit_amt + $ob_ttl_debit_amt;
            $ttl_credit_amt = $sub_ttl_credit_amt + $ob_ttl_credit_amt;

            ////////// ---End-- Data Fetch & calculation For During Date range from vouchers table-------////////////

            $tb = $ob_ttl_balance;
            $positive_tb = $tb;

            $DataSet = array();
            $sl = 1;
            foreach ($ledgerReport as $key => $row) {
                $tempSet = array();

                $tb = $tb + ($row->debit_amount - $row->credit_amount);
                $positive_tb = $tb;

                $tempSet = [
                    'sl' => $sl++,
                    'voucher_date' => $row->voucher_date,
                    'voucher_code' => $row->voucher_code,
                    'account_head' => $row->name,
                    'local_narration' => $row->local_narration,
                    'debit_amount' => $row->debit_amount,
                    'credit_amount' => $row->credit_amount,
                    'balance' => number_format(abs($positive_tb), 2),
                    'debit_or_credit' => ($tb >= 0) ? 'Dr' : 'Cr',
                ];

                $DataSet[] = $tempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,

                // Opening Balance Calculation
                'ob_ttl_debit_amt' => number_format($ob_ttl_debit_amt, 2),
                'ob_ttl_credit_amt' => number_format($ob_ttl_credit_amt, 2),
                'ob_ttl_balance' => number_format(abs($positive_ob_ttl_balance), 2),
                'ob_dr_or_cr' => ($ob_ttl_balance >= 0) ? 'Dr' : 'Cr',

                // Sub Total Amount Calculation (only amount during date range)
                'sub_ttl_debit_amt' => number_format($sub_ttl_debit_amt, 2),
                'sub_ttl_credit_amt' => number_format($sub_ttl_credit_amt, 2),
                'sub_ttl_balance' => number_format(abs($positive_tb), 2),
                'sub_ttl_dr_or_cr' => ($tb >= 0) ? 'Dr' : 'Cr',

                // Total Balance Calculation
                'ttl_debit_amt' => number_format($ttl_debit_amt, 2),
                'ttl_credit_amt' => number_format($ttl_credit_amt, 2),
                'ttl_balance' => number_format(abs($positive_tb), 2),
                'ttl_dr_or_cr' => ($tb >= 0) ? 'Dr' : 'Cr',
            );

            echo json_encode($json_data);
            exit;

        }
        return view('ACC.Report.ledger');
    }

    public function prepareSubLedger($ParentID = null, $ParentMenuArr = [], $MasterParent)
    {
        $SubMenuSet = array();
        $SubMenuData = $ParentMenuArr[$ParentID];
        foreach ($SubMenuData as $SubMenu) {
            $TempArray = array();

            if ($SubMenu->is_group_head == 0) {
                $TempArray = $SubMenu->id;
                $this->PublicLedger[$MasterParent][] = $SubMenu->id;
            }

            if (isset($ParentMenuArr[$SubMenu->id])) {
                self::prepareSubLedger($SubMenu->id, $ParentMenuArr, $MasterParent);
            }

            $SubMenuSet[] = $TempArray;
        }
        return $SubMenuSet;
    }

    public function getTrialBalance(Request $request)
    {
        if ($request->ajax()) {

            // Searching variable
            $fiscalYearID = (empty($request->input('fiscalYear'))) ? null : $request->input('fiscalYear');
            $yearly = (empty($request->input('yearly'))) ? null : $request->input('yearly');
            $projectID = (empty($request->input('projectID'))) ? null : $request->input('projectID');
            $projectTypeID = (empty($request->input('projectTypeID'))) ? null : $request->input('projectTypeID');
            $branchID = (empty($request->input('branchID'))) ? 1 : $request->input('branchID');

            $depth_level = (empty($request->input('depth_level'))) ? null : $request->input('depth_level');
            $round_up = (empty($request->input('round_up'))) ? null : $request->input('round_up');
            $zero_balance = (empty($request->input('zero_balance'))) ? null : $request->input('zero_balance');

            $startDateFY = (empty($request->input('startDateFY'))) ? null : $request->input('startDateFY');
            $endDateY = (empty($request->input('endDateY'))) ? null : $request->input('endDateY');

            $startDateY = (empty($request->input('startDateY'))) ? null : $request->input('startDateY');
            $endDateFY = (empty($request->input('endDateFY'))) ? null : $request->input('endDateFY');

            if (isset($startDateY)) {

                $startDate = new DateTime($startDateY);
                $startDate = $startDate->format('Y-m-d');
            }

            if (isset($endDateY)) {
                $endDate = new DateTime($endDateY);
                $endDate = $endDate->format('Y-m-d');
            }

            if (isset($fiscalYearID)) {
                $startDate = new DateTime($startDateFY);
                $startDate = $startDate->format('Y-m-d');

                $endDate = new DateTime($endDateFY);
                $endDate = $endDate->format('Y-m-d');
            }

            // // Query For Ledger Head
            $ledgerHeads = DB::table('acc_account_ledger as acl')
                ->where([['acl.is_delete', 0], ['acl.is_active', 1]])
                ->where(function ($ledgerHeads) use ($branchID) {
                    if (!empty($branchID)) {
                        $ledgerHeads->where('acl.branch_arr', 'LIKE', "%,{$branchID},%")
                            ->orWhere('acl.branch_arr', 'LIKE', "{$branchID},%")
                            ->orWhere('acl.branch_arr', 'LIKE', "%,{$branchID}")
                            ->orWhere('acl.branch_arr', 'LIKE', "{$branchID}");
                    }
                })
                ->where(function ($ledgerHeads) use ($projectID) {
                    if (!empty($projectID)) {
                        $ledgerHeads->where('acl.project_arr', 'LIKE', "%,{$projectID},%")
                            ->orWhere('acl.project_arr', 'LIKE', "{$projectID},%")
                            ->orWhere('acl.project_arr', 'LIKE', "%,{$projectID}")
                            ->orWhere('acl.project_arr', 'LIKE', "{$projectID}");
                    }
                })
                ->select('acl.id', 'acl.name', 'acl.code', 'acl.is_group_head', 'acl.parent_id', 'acl.level')
                ->orderBy('acl.sys_code', 'ASC')
                ->orderBy('acl.order_by', 'ASC')
                ->get();

            ///////////////////////////////////////////////////////////////////////////////////
            // // Ledger Data Split For Transectional Head
            $ledgerChilds = $ledgerHeads->groupBy('is_group_head');
            $ledgerChilds = $ledgerChilds->toarray();
            $ledgerChilds = (isset($ledgerChilds[0])) ? $ledgerChilds[0] : array();

            // // Ledger Data Group BY parent Ledger Wise
            $ledgerHeadsGR = $ledgerHeads->groupBy('parent_id');
            $ledgerHeadsGR = $ledgerHeadsGR->toarray();

            // // // Array Data make for parent wise transection head load set in Public Array
            foreach ($ledgerHeadsGR as $key => $ParentLedgerData) {
                foreach ($ParentLedgerData as $RootLedger) {

                    if ($RootLedger->is_group_head == 0) {
                        // Public Variable
                        $this->PublicLedger[$RootLedger->parent_id][] = $RootLedger->id;
                    }

                    if (isset($ledgerHeadsGR[$RootLedger->id])) {
                        self::prepareSubLedger($RootLedger->id, $ledgerHeadsGR, $RootLedger->parent_id);
                    }
                }
            }
            // // // End Make Data

            /////////////////////////////////////////////////////////////////////////////////////////

            // // Query For Debit Amount
            $DebitQuery = DB::table('acc_voucher as av')
                ->where([['av.is_delete', 0], ['av.is_active', 1]])
                ->whereIn('av.voucher_status', [1, 2])
                ->where(function ($DebitQuery) use ($endDate) {
                    if (!empty($endDate)) {
                        $DebitQuery->where('av.voucher_date', '<=', $endDate);
                    }
                })
                ->where(function ($DebitQuery) use ($branchID) {
                    if (!empty($branchID)) {
                        $DebitQuery->where('av.branch_id', $branchID);
                    }
                })
                ->where(function ($DebitQuery) use ($projectID) {
                    if (!empty($projectID)) {
                        $DebitQuery->where('av.project_id', $projectID);
                    }
                })
                ->where(function ($DebitQuery) use ($projectTypeID) {
                    if (!empty($projectTypeID)) {
                        $DebitQuery->where('av.project_type_id', $projectTypeID);
                    }
                })
                ->leftjoin('acc_voucher_details as avd', function ($DebitQuery) {
                    $DebitQuery->on('avd.voucher_id', 'av.id');
                })
                ->select('avd.debit_acc as ledger_id',
                    DB::raw('IFNULL(SUM(
                            CASE
                                WHEN av.voucher_date < "' . $startDate . '"
                                THEN avd.amount
                            END
                        ), 0) as sum_debit_beg,
                        IFNULL(SUM(
                            CASE
                                WHEN av.voucher_date >= "' . $startDate . '" and av.voucher_date <= "' . $endDate . '"
                                THEN avd.amount
                            END
                        ), 0) as sum_debit_dur'
                    )
                )
                ->orderBy('avd.debit_acc', 'ASC')
                ->groupBy('avd.debit_acc')
                ->get();

            $DebitData = array();
            foreach ($DebitQuery as $rowD) {
                $DebitData[$rowD->ledger_id] = (array) $rowD;
            }

            // // Query For Credit Amount
            $CreditQuery = DB::table('acc_voucher as av')
                ->where([['av.is_delete', 0], ['av.is_active', 1]])
                ->whereIn('av.voucher_status', [1, 2])
                ->where(function ($CreditQuery) use ($endDate) {
                    if (!empty($endDate)) {
                        $CreditQuery->where('av.voucher_date', '<=', $endDate);
                    }
                })
                ->where(function ($CreditQuery) use ($branchID) {
                    if (!empty($branchID)) {
                        $CreditQuery->where('av.branch_id', $branchID);
                    }
                })
                ->where(function ($CreditQuery) use ($projectID) {
                    if (!empty($projectID)) {
                        $CreditQuery->where('av.project_id', $projectID);
                    }
                })
                ->where(function ($CreditQuery) use ($projectTypeID) {
                    if (!empty($projectTypeID)) {
                        $CreditQuery->where('av.project_type_id', $projectTypeID);
                    }
                })
                ->leftjoin('acc_voucher_details as avd', function ($CreditQuery) {
                    $CreditQuery->on('avd.voucher_id', 'av.id');
                })
            // 'av.voucher_date',
                ->select('avd.credit_acc as ledger_id',
                    DB::raw('IFNULL(SUM(
                            CASE
                                WHEN av.voucher_date < "' . $startDate . '"
                                THEN avd.amount
                            END
                        ), 0) as sum_credit_beg,
                        IFNULL(SUM(
                            CASE
                                WHEN av.voucher_date >= "' . $startDate . '" and av.voucher_date <= "' . $endDate . '"
                                THEN avd.amount
                            END
                        ), 0) as sum_credit_dur'
                    )
                )
                ->orderBy('avd.credit_acc', 'ASC')
                ->groupBy('avd.credit_acc')
                ->get();

            $CreditData = array();
            foreach ($CreditQuery as $rowC) {
                $CreditData[$rowC->ledger_id] = (array) $rowC;
            }

            // // Query For Opening Balance
            /**
             * Opening BL search hobe 2 vabe, ek with fiscal year & without Fiscal Year,
             * Without Fiscal Year & date range diye search hole, ob table er branch opening bl ta ante hobe
             * with fiscal year hobe last fiscal year opening bl niye aste hobe only, branch opening balance ante hobe na
             * ekhon only Data range diye opening balance ana holo
             */

            $OBQuery = DB::table('acc_ob_m as aom')
                ->where([['aom.is_delete', 0], ['aom.is_active', 1], ['aom.is_year_end', 0]])
                ->where(function ($OBQuery) use ($branchID) {
                    if (!empty($branchID)) {
                        $OBQuery->where('aom.branch_id', $branchID);
                    }
                })
                ->where(function ($OBQuery) use ($projectID) {
                    if (!empty($projectID)) {
                        $OBQuery->where('aom.project_id', $projectID);
                    }
                })
                ->where(function ($OBQuery) use ($projectTypeID) {
                    if (!empty($projectTypeID)) {
                        $OBQuery->where('aom.project_type_id', $projectTypeID);
                    }
                })
                ->leftjoin('acc_ob_d as aod', function ($OBQuery) {
                    $OBQuery->on('aod.ob_no', 'aom.ob_no');
                })
            // 'aom.opening_date',
                ->select('aod.ledger_id', 'aod.debit_amount', 'aod.credit_amount')
                ->orderBy('aom.id', 'ASC')
                ->groupBy('aod.ledger_id')
                ->get();

            $OBData = array();
            foreach ($OBQuery as $rowO) {
                $OBData[$rowO->ledger_id] = (array) $rowO;
            }

            $ttl_debit_beg = 0;
            $ttl_debit_dur = 0;
            $ttl_debit_clo = 0;

            $ttl_credit_beg = 0;
            $ttl_credit_dur = 0;
            $ttl_credit_clo = 0;

            $ttl_closing_balance = 0;

            $DataSet = array();
            $DataSetLedger = array();
            $TempDataSet = array();

            // // This calculation for transection ledger only
            foreach ($ledgerChilds as $row) {

                $debit_beg = 0;
                $debit_dur = 0;
                $debit_clo = 0;

                $credit_beg = 0;
                $credit_dur = 0;
                $credit_clo = 0;

                if (isset($DebitData[$row->id])) {
                    $debit_beg = $DebitData[$row->id]['sum_debit_beg'];
                    $debit_dur = $DebitData[$row->id]['sum_debit_dur'];
                }

                if (isset($CreditData[$row->id])) {
                    $credit_beg = $CreditData[$row->id]['sum_credit_beg'];
                    $credit_dur = $CreditData[$row->id]['sum_credit_dur'];
                }

                if (isset($OBData[$row->id])) {
                    $debit_beg = $debit_beg + $OBData[$row->id]['debit_amount'];
                    $credit_beg = $credit_beg + $OBData[$row->id]['credit_amount'];
                }

                $OpeningBl = $debit_beg - $credit_beg;

                if ($OpeningBl < 0) {
                    $debit_beg = 0;
                    $credit_beg = abs($OpeningBl);
                } else {
                    $debit_beg = $OpeningBl;
                    $credit_beg = 0;
                }

                // // Calculate Closing Balance
                $ClosingBL = ($debit_beg + $debit_dur) - ($credit_beg + $credit_dur);
                if ($ClosingBL < 0) {
                    $credit_clo = abs($ClosingBL);
                } else {
                    $debit_clo = $ClosingBL;
                }
                // // // //  ------------------------------------- Round Up -------------------- -----
                if ($round_up == 1) {
                    $debit_beg = round($debit_beg);
                    $debit_dur = round($debit_dur);
                    $debit_clo = round($debit_clo);

                    $credit_beg = round($credit_beg);
                    $credit_dur = round($credit_dur);
                    $credit_clo = round($credit_clo);
                }

                // // Total Calculation
                $ttl_debit_beg += $debit_beg;
                $ttl_debit_dur += $debit_dur;
                $ttl_debit_clo += $debit_clo;

                $ttl_credit_beg += $credit_beg;
                $ttl_credit_dur += $credit_dur;
                $ttl_credit_clo += $credit_clo;

                $DataSetLedger[$row->id] = [
                    'debit_beg' => $debit_beg,
                    'debit_dur' => $debit_dur,
                    'debit_clo' => $debit_clo,
                    'credit_beg' => $credit_beg,
                    'credit_dur' => $credit_dur,
                    'credit_clo' => $credit_clo,
                ];
            }
            // // End Calculation for Transection Ledger

            // // // Calculation & Make visible Data for all ledger head
            foreach ($ledgerHeads as $row) {
                $tempSet = array();

                $debit_beg = 0;
                $debit_dur = 0;
                $debit_clo = 0;

                $credit_beg = 0;
                $credit_dur = 0;
                $credit_clo = 0;

                if ($row->is_group_head == 0) {

                    if (isset($DataSetLedger[$row->id])) {
                        $debit_beg = $DataSetLedger[$row->id]['debit_beg'];
                        $debit_dur = $DataSetLedger[$row->id]['debit_dur'];
                        $debit_clo = $DataSetLedger[$row->id]['debit_clo'];

                        $credit_beg = $DataSetLedger[$row->id]['credit_beg'];
                        $credit_dur = $DataSetLedger[$row->id]['credit_dur'];
                        $credit_clo = $DataSetLedger[$row->id]['credit_clo'];
                    }
                } else {
                    if (isset($this->PublicLedger[$row->id])) {
                        $ChildTransLegers = $this->PublicLedger[$row->id];

                        foreach ($ChildTransLegers as $CL_ID) {

                            $debit_beg += $DataSetLedger[$CL_ID]['debit_beg'];
                            $debit_dur += $DataSetLedger[$CL_ID]['debit_dur'];

                            $credit_beg += $DataSetLedger[$CL_ID]['credit_beg'];
                            $credit_dur += $DataSetLedger[$CL_ID]['credit_dur'];
                        }

                        // // Calculate Closing Balance
                        $ClosingBL = ($debit_beg + $debit_dur) - ($credit_beg + $credit_dur);
                        if ($ClosingBL < 0) {
                            $credit_clo = abs($ClosingBL);
                        } else {
                            $debit_clo = $ClosingBL;
                        }
                    }
                }

                /// //// // --------------------------------------- Level Check -------------------------------
                if (!empty($depth_level)) {
                    if ($row->level != $depth_level) {
                        continue;
                    }
                }

                // // // //  ------------------------------------- Zero Balance -------------------- -----
                if ($zero_balance == 2) {
                    if (($debit_beg == 0) && ($debit_dur == 0) && ($debit_clo == 0) && ($credit_beg == 0) && ($credit_dur == 0) && ($credit_clo == 0)) {
                        continue;
                    }
                }

                // // Data set for view
                $particular_name = $row->name . " [" . $row->code . "]";

                $debit_beg_txt = ($debit_beg > 0) ? number_format($debit_beg, 2) : '-';
                $debit_dur_txt = ($debit_dur > 0) ? number_format($debit_dur, 2) : '-';
                $debit_clo_txt = ($debit_clo > 0) ? number_format($debit_clo, 2) : '-';

                $credit_beg_txt = ($credit_beg > 0) ? number_format($credit_beg, 2) : '-';
                $credit_dur_txt = ($credit_dur > 0) ? number_format($credit_dur, 2) : '-';
                $credit_clo_txt = ($credit_clo > 0) ? number_format($credit_clo, 2) : '-';

                if ($row->is_group_head != 0) {

                    $particular_name = "<strong>" . $row->name . " [" . $row->code . "]</strong>";

                    $debit_beg_txt = "<strong>" . $debit_beg_txt . "</strong>";
                    $debit_dur_txt = "<strong>" . $debit_dur_txt . "</strong>";
                    $debit_clo_txt = "<strong>" . $debit_clo_txt . "</strong>";

                    $credit_beg_txt = "<strong>" . $credit_beg_txt . "</strong>";
                    $credit_dur_txt = "<strong>" . $credit_dur_txt . "</strong>";
                    $credit_clo_txt = "<strong>" . $credit_clo_txt . "</strong>";
                }

                $tempSet = [
                    'particular_name' => $particular_name,

                    'debit_beg_txt' => $debit_beg_txt,
                    'debit_dur_txt' => $debit_dur_txt,
                    'debit_clo_txt' => $debit_clo_txt,

                    'credit_beg_txt' => $credit_beg_txt,
                    'credit_dur_txt' => $credit_dur_txt,
                    'credit_clo_txt' => $credit_clo_txt,
                ];

                $DataSet[] = $tempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                'ttl_debit_beg' => number_format($ttl_debit_beg, 2),
                'ttl_debit_dur' => number_format($ttl_debit_dur, 2),
                'ttl_debit_clo' => number_format($ttl_debit_clo, 2),

                'ttl_credit_beg' => number_format($ttl_credit_beg, 2),
                'ttl_credit_dur' => number_format($ttl_credit_dur, 2),
                'ttl_credit_clo' => number_format($ttl_credit_clo, 2),
            );

            echo json_encode($json_data);
            exit;

        } else {
            return view('ACC.Report.trial_balance');
        }
    }

    public function getBranchWiseLedger(Request $request)
    {
        if ($request->ajax()) {

            // Initialize  variable
            $ob_ttl_debit_amt = 0;
            $ob_ttl_credit_amt = 0;
            $ob_ttl_balance = 0;

            $sub_ttl_debit_amt = 0;
            $sub_ttl_credit_amt = 0;
            $sub_ttl_balance = 0;
            $sub_ttl_dr_or_cr = 'Dr';

            $ttl_debit_amt = 0;
            $ttl_credit_amt = 0;
            $ttl_balance = 0;
            $ttl_dr_or_cr = 'Dr';

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $projectID = (empty($request->input('projectID'))) ? null : $request->input('projectID');
            $projectTypeID = (empty($request->input('projectTypeID'))) ? null : $request->input('projectTypeID');
            $branchID = (empty($request->input('branchID'))) ? 1 : $request->input('branchID');
            $ledgerID = (empty($request->input('ledgerID'))) ? null : $request->input('ledgerID');
            $voucherTypeID = (empty($request->input('voucherTypeID'))) ? null : $request->input('voucherTypeID');

            $startDate = new DateTime($startDate);
            $startDate = $startDate->format('Y-m-d');

            $endDate = new DateTime($endDate);
            $endDate = $endDate->format('Y-m-d');

            // Get All Branch Data Including Head Office

            $branchLedgerSql = "SELECT br.id, CONCAT( LPAD(br.branch_code,4,'0'),' - ',br.branch_name) as branch_info,
                    (
                     (IFNULL(aod.debit_amount, 0) + IFNULL(SUM(CASE WHEN (av.voucher_date < '" . $startDate . "' AND avd.debit_acc = '" . $ledgerID . "' ) THEN avd.amount END), 0)) -
                     (IFNULL(aod.credit_amount, 0) + IFNULL(SUM(CASE WHEN (av.voucher_date < '" . $startDate . "' AND avd.credit_acc = '" . $ledgerID . "') THEN avd.amount END), 0))
                    ) as opening_balance,


                    IFNULL(SUM(CASE WHEN (av.voucher_date >= '" . $startDate . "' AND av.voucher_date <= '" . $endDate . "' AND avd.debit_acc = '" . $ledgerID . "'
                        AND ( '" . $voucherTypeID . "' = '' OR av.voucher_type_id = '" . $voucherTypeID . "')) THEN avd.amount END), 0) as dur_debit_amount,
                    IFNULL(SUM(CASE WHEN (av.voucher_date >= '" . $startDate . "' AND av.voucher_date <= '" . $endDate . "' AND avd.credit_acc = '" . $ledgerID . "' AND
                         ('" . $voucherTypeID . "' = '' OR av.voucher_type_id = '" . $voucherTypeID . "')) THEN avd.amount END), 0) as dur_credit_amount,

                    (
                      ( IFNULL(aod.debit_amount, 0) +
                        IFNULL(SUM(CASE WHEN (av.voucher_date < '" . $startDate . "' AND avd.debit_acc = '" . $ledgerID . "' AND ( '" . $voucherTypeID . "' = '' OR av.voucher_type_id = '" . $voucherTypeID . "') ) THEN avd.amount END), 0) +
                        IFNULL(SUM(CASE WHEN (av.voucher_date >= '" . $startDate . "' AND av.voucher_date <= '" . $endDate . "' AND avd.debit_acc = '" . $ledgerID . "'
                            AND ( '" . $voucherTypeID . "' = '' OR av.voucher_type_id = '" . $voucherTypeID . "')) THEN avd.amount END), 0) ) -
                      ( IFNULL(aod.credit_amount, 0) +
                        IFNULL(SUM(CASE WHEN (av.voucher_date < '" . $startDate . "' AND avd.credit_acc = '" . $ledgerID . "' AND ( '" . $voucherTypeID . "' = '' OR av.voucher_type_id = '" . $voucherTypeID . "') ) THEN avd.amount END), 0) +
                        IFNULL(SUM(CASE WHEN (av.voucher_date >= '" . $startDate . "' AND av.voucher_date <= '" . $endDate . "' AND avd.credit_acc = '" . $ledgerID . "'
                            AND ( '" . $voucherTypeID . "' = '' OR av.voucher_type_id = '" . $voucherTypeID . "')) THEN avd.amount END), 0) )
                    ) as closing_balance,


                    av.voucher_date
                    FROM `gnl_branchs` as br
                    LEFT JOIN acc_ob_m as aom ON (aom.branch_id = br.id
                                                  AND aom.project_id = br.project_id
                                                  AND aom.project_type_id = br.project_type_id
                                                  AND aom.is_active = 1
                                                  AND aom.is_delete = 0
                                                  AND aom.is_year_end = 0)
                    LEFT JOIN acc_ob_d as aod ON (aod.ob_no = aom.id AND aod.ledger_id = '" . $ledgerID . "')
                    LEFT JOIN acc_voucher as av ON (av.branch_id = br.id
                                                    AND av.project_id = br.project_id
                                                    AND av.project_type_id = br.project_type_id
                                                    AND av.is_active = 1
                                                    AND av.is_delete = 0
                                                    AND av.voucher_status IN (1,2)
                                                    AND av.voucher_date <= '" . $endDate . "')
                    LEFT JOIN acc_voucher_details as avd ON (avd.voucher_id = av.voucher_code AND (avd.debit_acc = '"
                . $ledgerID . "' OR avd.credit_acc = '" . $ledgerID . "'))
                    WHERE br.is_active = 1
                    AND br.is_delete = 0
                    AND br.is_approve = 1
                    AND br.project_id = '" . $projectID . "'
                    AND br.project_type_id = '" . $projectTypeID . "'
                    GROUP BY br.id
                    ORDER BY br.branch_code ASC";

            $branchLedgerReport = DB::select($branchLedgerSql);

            $collectionLedger = collect($branchLedgerReport);

            $ttl_opening_balance = $collectionLedger->sum('opening_balance');
            $ttl_ob_dr_cr = ($ttl_opening_balance >= 0) ? 'Dr' : 'Cr';
            $ttl_debit_amt = $collectionLedger->sum('dur_debit_amount');
            $ttl_credit_amt = $collectionLedger->sum('dur_credit_amount');
            $ttl_closing_balance = $collectionLedger->sum('closing_balance');
            $ttl_cb_dr_cr = ($ttl_closing_balance >= 0) ? 'Dr' : 'Cr';

            $sl = 1;

            $DataSet = array();

            foreach ($branchLedgerReport as $row) {
                $tempSet = array();

                $tempSet = [
                    'sl' => $sl++,
                    'branch' => $row->branch_info,
                    'opening_balance' => number_format(abs($row->opening_balance), 2),
                    'ob_dr_cr' => ($row->opening_balance >= 0) ? 'Dr' : 'Cr',
                    'debit_amount' => number_format(abs($row->dur_debit_amount), 2),
                    'credit_amount' => number_format(abs($row->dur_credit_amount), 2),
                    'closing_balance' => number_format(abs($row->closing_balance), 2),
                    'cb_dr_cr' => ($row->closing_balance >= 0) ? 'Dr' : 'Cr',
                ];

                $DataSet[] = $tempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                'ttl_opening_balance' => number_format($ttl_opening_balance, 2),
                'ttl_ob_dr_cr' => $ttl_ob_dr_cr,
                'ttl_debit_amt' => number_format(abs($ttl_debit_amt), 2),
                'ttl_credit_amt' => number_format(abs($ttl_credit_amt), 2),
                'ttl_closing_balance' => number_format(abs($ttl_closing_balance), 2),
                'ttl_cb_dr_cr' => $ttl_cb_dr_cr,
            );

            echo json_encode($json_data);
            exit;

        }
        return view('ACC.Report.branch_wise_ledger');
    }

    public function getIncomeStatement(Request $request)
    {
        if ($request->ajax()) {

            // Searching variable
            // $brOpeningDate = (empty($request->input('brOPDate'))) ? null : $request->input('brOPDate');
            $brOpeningDate = Common::getBranchSoftwareStartDate();
            $searchBy = (empty($request->input('selected'))) ? null : $request->input('selected');
            $fiscalYear = (empty($request->input('fiscalYear'))) ? null : $request->input('fiscalYear');
            $projectID = (empty($request->input('projectID'))) ? null : $request->input('projectID');
            $projectTypeID = (empty($request->input('projectTypeID'))) ? null : $request->input('projectTypeID');
            $branchID = (empty($request->input('branchID'))) ? 1 : $request->input('branchID');

            $startDateFY = (empty($request->input('startDateFY'))) ? null : $request->input('startDateFY');
            $endDateFY = (empty($request->input('endDateFY'))) ? null : $request->input('endDateFY');

            $startDateCY = (empty($request->input('startDateCY'))) ? null : $request->input('startDateCY');
            $endDateCY = (empty($request->input('endDateCY'))) ? null : $request->input('endDateCY');

            $startDateDR = (empty($request->input('startDateDR'))) ? null : $request->input('startDateDR');
            $endDateDR = (empty($request->input('endDateDR'))) ? null : $request->input('endDateDR');

            $depth_level = (empty($request->input('depth_level'))) ? null : $request->input('depth_level');
            $round_up = (empty($request->input('round_up'))) ? null : $request->input('round_up');
            $zero_balance = (empty($request->input('zero_balance'))) ? null : $request->input('zero_balance');

            // // Query For Ledger Head [Only account type Income and Expense]
            $ledgerHeads = DB::table('acc_account_ledger as acl')
                ->where(function ($ledgerHeads) use ($branchID) {
                    if (!empty($branchID)) {
                        $ledgerHeads->where('acl.branch_arr', 'LIKE', "%,{$branchID},%")
                            ->orWhere('acl.branch_arr', 'LIKE', "{$branchID},%")
                            ->orWhere('acl.branch_arr', 'LIKE', "%,{$branchID}")
                            ->orWhere('acl.branch_arr', 'LIKE', "{$branchID}");
                    }
                })
                ->where(function ($ledgerHeads) use ($projectID) {
                    if (!empty($projectID)) {
                        $ledgerHeads->where('acl.project_arr', 'LIKE', "%,{$projectID},%")
                            ->orWhere('acl.project_arr', 'LIKE', "{$projectID},%")
                            ->orWhere('acl.project_arr', 'LIKE', "%,{$projectID}")
                            ->orWhere('acl.project_arr', 'LIKE', "{$projectID}");
                    }
                })
                ->where([['acl.is_delete', 0], ['acl.is_active', 1]])
                ->whereIn('acl.acc_type_id', [12, 13])
                ->select('acl.id', 'acl.name', 'acl.code', 'acl.is_group_head', 'acl.parent_id', 'acl.acc_type_id', 'acl.level')
                ->orderBy('acl.sys_code', 'ASC')
                ->orderBy('acl.order_by', 'ASC')
                ->get();

            ///////////////////////////////////////////////////////////////////////////////////
            // // Ledger Data Split For Transectional Head
            $ledgerChilds = $ledgerHeads->groupBy('is_group_head');
            $ledgerChilds = $ledgerChilds->toarray();

            $ledgerChilds = (isset($ledgerChilds[0])) ? $ledgerChilds[0] : array();

            // // Ledger Data Group BY parent Ledger Wise
            $ledgerHeadsInGR = $ledgerHeads->groupBy('parent_id');
            $ledgerHeadsInGR = $ledgerHeadsInGR->toarray();

            // // // Array Data make for parent wise transection head load set in Public Array
            foreach ($ledgerHeadsInGR as $key => $ParentLedgerData) {
                foreach ($ParentLedgerData as $RootLedger) {

                    if ($RootLedger->is_group_head == 0) {
                        // Public Variable
                        $this->PublicLedger[$RootLedger->parent_id][] = $RootLedger->id;
                    }

                    if (isset($ledgerHeadsInGR[$RootLedger->id])) {
                        self::prepareSubLedger($RootLedger->id, $ledgerHeadsInGR, $RootLedger->parent_id);
                    }
                }
            }
            // // // End Make Data

            // // // Search By Fiscal Year
            if ($searchBy == 1) {
                $startDateFY = (new DateTime($startDateFY))->format('Y-m-d');
                $endDateFY = (new DateTime($endDateFY))->format('Y-m-d');
                $previousYear = strstr($fiscalYear, '-', true);
                $prevYearEndDate = $previousYear . "-" . "12-31";

                // // Query For Debit Amount
                $fiscalDebitThisYear = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($fiscalDebitThisYear) use ($startDateFY, $endDateFY) {
                        if (!empty($startDateFY) && !empty($endDateFY)) {
                            $fiscalDebitThisYear->whereBetween('av.voucher_date', [$startDateFY, $endDateFY]);
                        }
                    })
                    ->where(function ($fiscalDebitThisYear) use ($branchID) {
                        if (!empty($branchID)) {
                            $fiscalDebitThisYear->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($fiscalDebitThisYear) use ($projectID) {
                        if (!empty($projectID)) {
                            $fiscalDebitThisYear->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($fiscalDebitThisYear) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $fiscalDebitThisYear->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($fiscalDebitThisYear) {
                        $fiscalDebitThisYear->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDateFY . '" and av.voucher_date <= "' . $endDateFY . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_debit_dur'
                        )
                    )
                    ->orderBy('avd.debit_acc', 'ASC')
                    ->groupBy('avd.debit_acc')
                    ->get();

                $DebitData = array();
                foreach ($fiscalDebitThisYear as $rowD) {
                    $DebitData[$rowD->ledger_id] = (array) $rowD;
                }

                // // Query For Credit Amount
                $fiscalCreditThisYear = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($fiscalCreditThisYear) use ($startDateFY, $endDateFY) {
                        if (!empty($startDateFY) && !empty($endDateFY)) {
                            $fiscalCreditThisYear->whereBetween('av.voucher_date', [$startDateFY, $endDateFY]);
                        }
                    })
                    ->where(function ($fiscalCreditThisYear) use ($branchID) {
                        if (!empty($branchID)) {
                            $fiscalCreditThisYear->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($fiscalCreditThisYear) use ($projectID) {
                        if (!empty($projectID)) {
                            $fiscalCreditThisYear->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($fiscalCreditThisYear) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $fiscalCreditThisYear->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($fiscalCreditThisYear) {
                        $fiscalCreditThisYear->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDateFY . '" and av.voucher_date <= "' . $endDateFY . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_credit_dur'
                        )
                    )
                    ->orderBy('avd.credit_acc', 'ASC')
                    ->groupBy('avd.credit_acc')
                    ->get();

                $CreditData = array();
                foreach ($fiscalCreditThisYear as $rowC) {
                    $CreditData[$rowC->ledger_id] = (array) $rowC;
                }

                $cumulativeDebitQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($cumulativeDebitQuery) use ($brOpeningDate, $endDateFY) {
                        if (!empty($brOpeningDate) && !empty($endDateFY)) {
                            $cumulativeDebitQuery->whereBetween('av.voucher_date', [$brOpeningDate, $endDateFY]);
                        }
                    })
                    ->where(function ($cumulativeDebitQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            $cumulativeDebitQuery->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($cumulativeDebitQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $cumulativeDebitQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($cumulativeDebitQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $cumulativeDebitQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($cumulativeDebitQuery) {
                        $cumulativeDebitQuery->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $brOpeningDate . '" and av.voucher_date <= "' . $endDateFY . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_debit_clo'
                        )
                    )
                    ->orderBy('avd.debit_acc', 'ASC')
                    ->groupBy('avd.debit_acc')
                    ->get();

                $closingDebitData = array();
                foreach ($cumulativeDebitQuery as $rowO) {
                    $closingDebitData[$rowO->ledger_id] = (array) $rowO;
                }

                $cumulativeCreditQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($cumulativeCreditQuery) use ($brOpeningDate, $endDateFY) {
                        if (!empty($brOpeningDate) && !empty($endDateFY)) {
                            $cumulativeCreditQuery->whereBetween('av.voucher_date', [$brOpeningDate, $endDateFY]);
                        }
                    })
                    ->where(function ($cumulativeCreditQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            $cumulativeCreditQuery->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($cumulativeCreditQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $cumulativeCreditQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($cumulativeCreditQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $cumulativeCreditQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($cumulativeCreditQuery) {
                        $cumulativeCreditQuery->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $brOpeningDate . '" and av.voucher_date <= "' . $endDateFY . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_credit_clo'
                        )
                    )
                    ->orderBy('avd.credit_acc', 'ASC')
                    ->groupBy('avd.credit_acc')
                    ->get();
                $closingCreditData = array();
                foreach ($cumulativeCreditQuery as $rowO) {
                    $closingCreditData[$rowO->ledger_id] = (array) $rowO;
                }

            }

            // // // Search By Current Year
            if ($searchBy == 2) {
                $startDateCY = (new DateTime($startDateCY))->format('Y-m-d');
                $endDateCY = (new DateTime($endDateCY))->format('Y-m-d');
                $endDateToday = (new DateTime())->format('Y-m-d');

                // // Query For Debit Amount
                $currentYearDebit = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($currentYearDebit) use ($startDateCY, $endDateCY) {
                        if (!empty($startDateCY) && !empty($endDateCY)) {
                            $currentYearDebit->whereBetween('av.voucher_date', [$startDateCY, $endDateCY]);
                        }
                    })
                    ->where(function ($currentYearDebit) use ($branchID) {
                        if (!empty($branchID)) {
                            $currentYearDebit->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($currentYearDebit) use ($projectID) {
                        if (!empty($projectID)) {
                            $currentYearDebit->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($currentYearDebit) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $currentYearDebit->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($currentYearDebit) {
                        $currentYearDebit->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDateCY . '" and av.voucher_date <= "' . $endDateCY . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_debit_month,
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDateCY . '" and av.voucher_date <= "' . $endDateToday . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_debit_dur'
                        )
                    )
                    ->orderBy('avd.debit_acc', 'ASC')
                    ->groupBy('avd.debit_acc')
                    ->get();

                $DebitData = array();
                foreach ($currentYearDebit as $rowD) {
                    $DebitData[$rowD->ledger_id] = (array) $rowD;
                }

                // // Query For Credit Amount
                $currentYearCredit = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($currentYearDebit) use ($startDateCY, $endDateCY) {
                        if (!empty($startDateCY) && !empty($endDateCY)) {
                            $currentYearDebit->whereBetween('av.voucher_date', [$startDateCY, $endDateCY]);
                        }
                    })
                    ->where(function ($currentYearDebit) use ($branchID) {
                        if (!empty($branchID)) {
                            $currentYearDebit->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($currentYearDebit) use ($projectID) {
                        if (!empty($projectID)) {
                            $currentYearDebit->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($currentYearDebit) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $currentYearDebit->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($currentYearDebit) {
                        $currentYearDebit->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDateCY . '" and av.voucher_date <= "' . $endDateCY . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_credit_month,
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDateCY . '" and av.voucher_date <= "' . $endDateToday . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_credit_dur'
                        )
                    )
                    ->orderBy('avd.credit_acc', 'ASC')
                    ->groupBy('avd.credit_acc')
                    ->get();

                $CreditData = array();
                foreach ($currentYearCredit as $rowC) {
                    $CreditData[$rowC->ledger_id] = (array) $rowC;
                }

                $cumulativeDebitQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($cumulativeDebitQuery) use ($brOpeningDate, $endDateToday) {
                        if (!empty($brOpeningDate) && !empty($endDateToday)) {
                            $cumulativeDebitQuery->whereBetween('av.voucher_date', [$brOpeningDate, $endDateToday]);
                        }
                    })
                    ->where(function ($cumulativeDebitQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            $cumulativeDebitQuery->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($cumulativeDebitQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $cumulativeDebitQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($cumulativeDebitQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $cumulativeDebitQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($cumulativeDebitQuery) {
                        $cumulativeDebitQuery->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $brOpeningDate . '" and av.voucher_date <= "' . $endDateToday . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_debit_clo'
                        )
                    )

                    ->orderBy('avd.debit_acc', 'ASC')
                    ->groupBy('avd.debit_acc')
                    ->get();

                $closingDebitData = array();
                foreach ($cumulativeDebitQuery as $rowO) {
                    $closingDebitData[$rowO->ledger_id] = (array) $rowO;
                }

                $cumulativeCreditQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($cumulativeCreditQuery) use ($brOpeningDate, $endDateToday) {
                        if (!empty($brOpeningDate) && !empty($endDateToday)) {
                            $cumulativeCreditQuery->whereBetween('av.voucher_date', [$brOpeningDate, $endDateToday]);
                        }
                    })
                    ->where(function ($cumulativeCreditQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            $cumulativeCreditQuery->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($cumulativeCreditQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $cumulativeCreditQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($cumulativeCreditQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $cumulativeCreditQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($cumulativeCreditQuery) {
                        $cumulativeCreditQuery->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $brOpeningDate . '" and av.voucher_date <= "' . $endDateToday . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_credit_clo'
                        )
                    )
                    ->orderBy('avd.credit_acc', 'ASC')
                    ->groupBy('avd.credit_acc')
                    ->get();
                $closingCreditData = array();
                foreach ($cumulativeCreditQuery as $rowO) {
                    $closingCreditData[$rowO->ledger_id] = (array) $rowO;
                }

            }

            // // // Search By Date Range
            if ($searchBy == 3) {
                $startDateDR = (new DateTime($startDateDR))->format('Y-m-d');
                $endDateDR = (new DateTime($endDateDR))->format('Y-m-d');
                $currentYear = (int) (strstr($endDateDR, '-', true));
                $previousYear = strval($currentYear - 1);
                $prevYearEndDate = $previousYear . "-" . "12-31";

                // // Query For Debit Amount
                $dateRangeDebit = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($dateRangeDebit) use ($startDateDR, $endDateDR) {
                        if (!empty($startDateDR) && !empty($endDateDR)) {
                            $dateRangeDebit->whereBetween('av.voucher_date', [$startDateDR, $endDateDR]);
                        }
                    })
                    ->where(function ($dateRangeDebit) use ($branchID) {
                        if (!empty($branchID)) {
                            $dateRangeDebit->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($dateRangeDebit) use ($projectID) {
                        if (!empty($projectID)) {
                            $dateRangeDebit->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($dateRangeDebit) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $dateRangeDebit->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($dateRangeDebit) {
                        $dateRangeDebit->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDateDR . '" and av.voucher_date <= "' . $endDateDR . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_debit_month'
                        )
                    )
                    ->orderBy('avd.debit_acc', 'ASC')
                    ->groupBy('avd.debit_acc')
                    ->get();

                $DebitData = array();
                foreach ($dateRangeDebit as $rowD) {
                    $DebitData[$rowD->ledger_id] = (array) $rowD;
                }

                // // Query For Credit Amount
                $dateRangeCredit = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($dateRangeCredit) use ($startDateDR, $endDateDR) {
                        if (!empty($startDateDR) && !empty($endDateDR)) {
                            $dateRangeCredit->whereBetween('av.voucher_date', [$startDateDR, $endDateDR]);
                        }
                    })
                    ->where(function ($dateRangeCredit) use ($branchID) {
                        if (!empty($branchID)) {
                            $dateRangeCredit->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($dateRangeCredit) use ($projectID) {
                        if (!empty($projectID)) {
                            $dateRangeCredit->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($dateRangeCredit) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $dateRangeCredit->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($dateRangeCredit) {
                        $dateRangeCredit->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDateDR . '" and av.voucher_date <= "' . $endDateDR . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_credit_month'
                        )
                    )
                    ->orderBy('avd.credit_acc', 'ASC')
                    ->groupBy('avd.credit_acc')
                    ->get();

                $CreditData = array();
                foreach ($dateRangeCredit as $rowC) {
                    $CreditData[$rowC->ledger_id] = (array) $rowC;
                }

                $cumulativeDebitQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($cumulativeDebitQuery) use ($brOpeningDate, $endDateDR) {
                        if (!empty($brOpeningDate) && !empty($endDateDR)) {
                            $cumulativeDebitQuery->whereBetween('av.voucher_date', [$brOpeningDate, $endDateDR]);
                        }
                    })
                    ->where(function ($cumulativeDebitQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            $cumulativeDebitQuery->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($cumulativeDebitQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $cumulativeDebitQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($cumulativeDebitQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $cumulativeDebitQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($cumulativeDebitQuery) {
                        $cumulativeDebitQuery->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $brOpeningDate . '" and av.voucher_date <= "' . $endDateDR . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_debit_clo'
                        )
                    )

                    ->orderBy('avd.debit_acc', 'ASC')
                    ->groupBy('avd.debit_acc')
                    ->get();

                $closingDebitData = array();
                foreach ($cumulativeDebitQuery as $rowO) {
                    $closingDebitData[$rowO->ledger_id] = (array) $rowO;
                }

                $cumulativeCreditQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($cumulativeCreditQuery) use ($brOpeningDate, $endDateDR) {
                        if (!empty($brOpeningDate) && !empty($endDateDR)) {
                            $cumulativeCreditQuery->whereBetween('av.voucher_date', [$brOpeningDate, $endDateDR]);
                        }
                    })
                    ->where(function ($cumulativeCreditQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            $cumulativeCreditQuery->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($cumulativeCreditQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $cumulativeCreditQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($cumulativeCreditQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $cumulativeCreditQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($cumulativeCreditQuery) {
                        $cumulativeCreditQuery->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $brOpeningDate . '" and av.voucher_date <= "' . $endDateDR . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_credit_clo'
                        )
                    )
                    ->orderBy('avd.credit_acc', 'ASC')
                    ->groupBy('avd.credit_acc')
                    ->get();
                $closingCreditData = array();
                foreach ($cumulativeCreditQuery as $rowO) {
                    $closingCreditData[$rowO->ledger_id] = (array) $rowO;
                }

            }

            $ttl_debit_dur = 0;
            $ttl_debit_clo = 0;

            $ttl_credit_dur = 0;
            $ttl_credit_clo = 0;

            $ttl_balance_dur = 0;
            $ttl_balance_clo = 0;

            $ttl_debit_month = 0;
            $ttl_credit_month = 0;
            $ttl_balance_month = 0;

            $DataSet = array();
            $DataSetLedger = array();
            $TempDataSet = array();

            // // This calculation for transection ledger only
            foreach ($ledgerChilds as $row) {

                $debit_dur = 0;
                $debit_clo = 0;

                $credit_dur = 0;
                $credit_clo = 0;

                $current_bl = 0;

                $debit_month = 0;
                $credit_month = 0;
                $month_bl = 0;

                if (isset($DebitData[$row->id])) {
                    if ($searchBy != 3) {
                        $debit_dur = $DebitData[$row->id]['sum_debit_dur'];
                    }
                    if ($searchBy != 1) {
                        $debit_month = $DebitData[$row->id]['sum_debit_month'];
                    }
                }

                if (isset($CreditData[$row->id])) {
                    if ($searchBy != 3) {
                        $credit_dur = $CreditData[$row->id]['sum_credit_dur'];
                    }
                    if ($searchBy != 1) {
                        $credit_month = $CreditData[$row->id]['sum_credit_month'];
                    }
                }

                if (isset($closingDebitData[$row->id])) {
                    $debit_clo = $closingDebitData[$row->id]['sum_debit_clo'];
                }

                if (isset($closingCreditData[$row->id])) {
                    $credit_clo = $closingCreditData[$row->id]['sum_credit_clo'];
                }

                //// Condition Implement for Round Up
                if ($round_up == 1) {
                    $debit_month = round($debit_month);
                    $debit_dur = round($debit_dur);
                    $debit_clo = round($debit_clo);

                    $credit_month = round($credit_month);
                    $credit_dur = round($credit_dur);
                    $credit_clo = round($credit_clo);
                }

                // For Account Type Expese, Balance = Debit - Credit
                if ($row->acc_type_id == 13) {
                    $current_bl = $debit_dur - $credit_dur;
                    $closing_bl = $debit_clo - $credit_clo;
                    $month_bl = $debit_month - $credit_month;
                }

                // For Account Type Income, Balance = Credit - Debit
                else {

                    $current_bl = $credit_dur - $debit_dur;
                    $closing_bl = $credit_clo - $debit_clo;
                    $month_bl = $credit_month - $debit_month;
                }

                // // Total Calculation
                $ttl_debit_dur += $debit_dur;
                $ttl_debit_clo += $debit_clo;

                // $ttl_credit_beg += $credit_beg;
                $ttl_credit_dur += $credit_dur;
                $ttl_credit_clo += $credit_clo;

                $DataSetLedger[$row->id] = [
                    'debit_month' => $debit_month,
                    'credit_month' => $credit_month,
                    'debit_dur' => $debit_dur,
                    'credit_dur' => $credit_dur,
                    'month_bl' => $month_bl,
                    'current_bl' => $current_bl,
                    'debit_clo' => $debit_clo,
                    'credit_clo' => $credit_clo,
                    'closing_bl' => $closing_bl,
                    'account_type' => $row->acc_type_id,
                ];
            }
            // // End Calculation for Transection Ledger

            // Calculation for Total Income Row Added After income type account head
            $collection_income = collect($DataSetLedger)->where('account_type', 12);
            $total_income_dur = $collection_income->sum('current_bl');
            $total_income_month = $collection_income->sum('month_bl');
            $total_income_clo = $collection_income->sum('closing_bl');

            // Calculation for Total Expense Row Added After Expense type account head
            $collection_expense = collect($DataSetLedger)->where('account_type', 13);
            $total_expense_dur = $collection_expense->sum('current_bl');
            $total_expense_month = $collection_expense->sum('month_bl');
            $total_expense_clo = $collection_expense->sum('closing_bl');

            // // Total (Profit / Loss) calculatation
            // Total = Total Income - Total Expense
            $ttl_balance_month = $total_income_month - $total_expense_month;
            $ttl_balance_dur = $total_income_dur - $total_expense_dur;
            $ttl_balance_clo = $total_income_clo - $total_expense_clo;

            // // Set View For Total Income , Total Expense and Total
            $total_income_dur = $total_income_dur != 0 ? number_format($total_income_dur, 2) : '-';
            $total_income_month = $total_income_month != 0 ? number_format($total_income_month, 2) : '-';
            $total_income_clo = $total_income_clo != 0 ? number_format($total_income_clo, 2) : '-';

            $total_expense_dur = $total_expense_dur != 0 ? number_format($total_expense_dur, 2) : '-';
            $total_expense_month = $total_expense_month != 0 ? number_format($total_expense_month, 2) : '-';
            $total_expense_clo = $total_expense_clo != 0 ? number_format($total_expense_clo, 2) : '-';

            $ttl_balance_month = $ttl_balance_month != 0 ? number_format($ttl_balance_month, 2) : '-';
            $ttl_balance_dur = $ttl_balance_dur != 0 ? number_format($ttl_balance_dur, 2) : '-';
            $ttl_balance_clo = $ttl_balance_clo != 0 ? number_format($ttl_balance_clo, 2) : '-';

            $total_income_title = "<strong class='text-uppercase'>" . 'Total Income' . "</strong>";
            $total_income_dur = "<strong>" . $total_income_dur . "</strong>";
            $total_income_month = "<strong>" . $total_income_month . "</strong>";
            $total_income_clo = "<strong>" . $total_income_clo . "</strong>";

            $total_expense_title = "<strong class='text-uppercase'>" . 'Total Expense' . "</strong>";
            $total_expense_dur = "<strong>" . $total_expense_dur . "</strong>";
            $total_expense_month = "<strong>" . $total_expense_month . "</strong>";
            $total_expense_clo = "<strong>" . $total_expense_clo . "</strong>";

            $total_title = "<strong class='text-uppercase'>" . 'Profit/Loss' . "</strong>";
            $ttl_balance_month = "<strong>" . $ttl_balance_month . "</strong>";
            $ttl_balance_dur = "<strong>" . $ttl_balance_dur . "</strong>";
            $ttl_balance_clo = "<strong>" . $ttl_balance_clo . "</strong>";

            // // // Calculation & Make visible Data for all ledger head
            $flag = 0;
            foreach ($ledgerHeads as $row) {
                $tempSet = array();

                $debit_month = 0;
                $debit_dur = 0;
                $debit_clo = 0;

                $credit_month = 0;
                $credit_dur = 0;
                $credit_clo = 0;

                $month_bl = 0;
                $current_bl = 0;
                $closing_bl = 0;

                if ($row->is_group_head == 0) {

                    if (isset($DataSetLedger[$row->id])) {

                        $debit_month = $DataSetLedger[$row->id]['debit_month'];
                        $debit_dur = $DataSetLedger[$row->id]['debit_dur'];
                        $debit_clo = $DataSetLedger[$row->id]['debit_clo'];

                        $credit_month = $DataSetLedger[$row->id]['credit_month'];
                        $credit_dur = $DataSetLedger[$row->id]['credit_dur'];
                        $credit_clo = $DataSetLedger[$row->id]['credit_clo'];

                        // $current_bl = $DataSetLedger[$row->id]['current_bl'];
                        // $closing_bl = $DataSetLedger[$row->id]['closing_bl'];
                        // $month_bl = $DataSetLedger[$row->id]['month_bl'];

                        if ($row->acc_type_id == 13) {
                            $month_bl = $DataSetLedger[$row->id]['debit_month'] - $DataSetLedger[$row->id]['credit_month'];
                            $current_bl = $DataSetLedger[$row->id]['debit_dur'] - $DataSetLedger[$row->id]['credit_dur'];
                            $closing_bl = $DataSetLedger[$row->id]['debit_clo'] - $DataSetLedger[$row->id]['credit_clo'];
                        } else {
                            $month_bl = $DataSetLedger[$row->id]['credit_month'] - $DataSetLedger[$row->id]['debit_month'];
                            $current_bl = $DataSetLedger[$row->id]['credit_dur'] - $DataSetLedger[$row->id]['debit_dur'];
                            $closing_bl = $DataSetLedger[$row->id]['credit_clo'] - $DataSetLedger[$row->id]['debit_clo'];
                        }
                    }
                } else if ($row->is_group_head == 1) {
                    if (isset($this->PublicLedger[$row->id])) {
                        $ChildTransLegers = $this->PublicLedger[$row->id];

                        foreach ($ChildTransLegers as $CL_ID) {

                            $debit_month += $DataSetLedger[$CL_ID]['debit_month'];
                            $debit_dur += $DataSetLedger[$CL_ID]['debit_dur'];
                            $debit_clo += $DataSetLedger[$CL_ID]['debit_clo'];

                            $credit_month += $DataSetLedger[$CL_ID]['credit_month'];
                            $credit_dur += $DataSetLedger[$CL_ID]['credit_dur'];
                            $credit_clo += $DataSetLedger[$CL_ID]['credit_clo'];

                            $current_bl += $DataSetLedger[$CL_ID]['current_bl'];
                            $closing_bl += $DataSetLedger[$CL_ID]['closing_bl'];

                            $month_bl += $DataSetLedger[$CL_ID]['month_bl'];
                        }

                    }
                }

                /// //// // --------------------------------------- Level Check -------------------------------
                if (($depth_level != '')) {
                    if ($row->level != $depth_level) {
                        continue;
                    }
                }

                // // ////------------------- Condition Implement for Zero Balance ---------------------------
                if ($zero_balance == 2 && $searchBy == 1) {
                    if (($debit_dur == 0) && ($debit_clo == 0) && ($credit_dur == 0) && ($credit_clo == 0)) {
                        continue;
                    }
                }
                if ($zero_balance == 2 && $searchBy == 2) {
                    if (($debit_month == 0) && ($debit_clo == 0) && ($credit_month == 0) && ($credit_clo == 0)) {
                        continue;
                    }
                }
                if ($zero_balance == 2 && $searchBy == 3) {
                    if (($debit_month == 0) && ($debit_dur == 0) && ($debit_clo == 0) && ($credit_month == 0) && ($credit_dur == 0) && ($credit_clo == 0)) {
                        continue;
                    }
                }

                // // Data set for view
                $particular_name = $row->name . " [" . $row->code . "]";

                $balance_dur_txt = ($current_bl != 0) ? number_format($current_bl, 2) : '-';
                $closing_balance_txt = ($closing_bl != 0) ? number_format($closing_bl, 2) : '-';
                $balance_month_txt = ($month_bl != 0) ? number_format($month_bl, 2) : '-';

                if ($row->is_group_head != 0) {
                    $balance_dur_txt = "<strong>" . $balance_dur_txt . "</strong>";
                    $closing_balance_txt = "<strong>" . $closing_balance_txt . "</strong>";
                    $balance_month_txt = "<strong>" . $balance_month_txt . "</strong>";

                    $particular_name = "<strong>" . $row->name . " [" . $row->code . "]</strong>";
                }

                // For adding Total Income Row
                // Just before Expense type ledger Head, Add a row for total income
                // which sums up Total balance for income type ledger
                if ($row->acc_type_id == 13 && $flag == 0) {
                    $flag = 1;
                }
                if ($flag == 1) {
                    $tempSet = [
                        'particular_name' => $total_income_title,
                        'notes' => '',
                        'balance_dur_txt' => $total_income_dur,
                        'balance_month_txt' => $total_income_month,
                        'closing_balance_txt' => $total_income_clo,
                    ];
                    $flag = 2;
                    $DataSet[] = $tempSet;
                }
                // End

                // This is Regular tempset i.e. add every income and expense type ledger head
                $tempSet = [
                    'particular_name' => $particular_name,
                    'notes' => '',
                    'balance_month_txt' => $balance_month_txt,
                    'balance_dur_txt' => $balance_dur_txt,
                    'closing_balance_txt' => $closing_balance_txt,
                ];
                $DataSet[] = $tempSet;
                // end
            }

            // For adding Total Expense Row
            // Just After Expense type ledger Head, Add a row for total Expense
            // which sums up Total balance for Expense type ledger
            $tempSet = [
                'particular_name' => $total_expense_title,
                'notes' => '',
                'balance_month_txt' => $total_expense_month,
                'balance_dur_txt' => $total_expense_dur,
                'closing_balance_txt' => $total_expense_clo,

            ];
            $DataSet[] = $tempSet;
            //End

            // For adding Total Row
            // This Row sums up total balance for both income and expense type ledger
            $tempSet = [
                'particular_name' => $total_title,
                'notes' => '',
                'balance_month_txt' => $ttl_balance_month,
                'balance_dur_txt' => $ttl_balance_dur,
                'closing_balance_txt' => $ttl_balance_clo,

            ];
            $DataSet[] = $tempSet;
            // End

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,

            );

            echo json_encode($json_data);
            exit;

        } else {
            return view('ACC.Report.income_statement');
        }
    }

    public function getCashBook(Request $request)
    {
        if ($request->ajax()) {

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');

            $companyID = (empty($request->input('companyID'))) ? Common::getCompanyId() : $request->input('companyID');

            $projectID = (empty($request->input('projectID'))) ? null : $request->input('projectID');
            $projectTypeID = (empty($request->input('projectTypeID'))) ? null : $request->input('projectTypeID');
            $branchID = (empty($request->input('branchID'))) ? 1 : $request->input('branchID');
            $ledgerID = (empty($request->input('ledgerID'))) ? -1 : $request->input('ledgerID');
            $voucherTypeID = (empty($request->input('voucherTypeID'))) ? null : $request->input('voucherTypeID');

            $startDate = new DateTime($startDate);
            $startDate = $startDate->format('Y-m-d');

            $endDate = new DateTime($endDate);
            $endDate = $endDate->format('Y-m-d');

            $ledgerArray = array();

            if ($ledgerID == -1) {
                $ledgerCashIds = DB::table('acc_account_ledger as acl')
                    ->where([['acl.is_delete', 0], ['acl.is_active', 1]])
                    ->where('acc_type_id', 4)
                    ->pluck('id');

                $ledgerArray = $ledgerCashIds->toarray();
            } else {
                $ledgerArray = [$ledgerID];
            }

            $ReportData = ACC::cash_bankBookReport(false, $ledgerArray, $startDate, $endDate, $branchID, $voucherTypeID,
                 $companyID, $projectID, $projectTypeID);


            ///
            $ob_ttl_balance = $ReportData['ob_ttl_balance'];
            $positive_ob_ttl_balance = $ReportData['ob_ttl_balance'];

            $tb = $ReportData['ttl_balance'];
            $positive_tb = $tb;

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $ReportData['DataSet'],

                // Opening Balance Calculation
                'ob_ttl_debit_amt' => number_format($ReportData['ob_ttl_debit_amt'], 2),
                'ob_ttl_credit_amt' => number_format($ReportData['ob_ttl_credit_amt'], 2),
                'ob_ttl_balance' => number_format(abs($positive_ob_ttl_balance), 2),
                'ob_dr_or_cr' => ($ob_ttl_balance >= 0) ? 'Dr' : 'Cr',

                // Sub Total Amount Calculation (only amount during date range)
                'sub_ttl_debit_amt' => number_format($ReportData['sub_ttl_debit_amt'], 2),
                'sub_ttl_credit_amt' => number_format($ReportData['sub_ttl_credit_amt'], 2),
                'sub_ttl_balance' => number_format(abs($positive_tb), 2),
                'sub_ttl_dr_or_cr' => ($tb >= 0) ? 'Dr' : 'Cr',

                // Opening Balance Calculation
                'ttl_debit_amt' => number_format($ReportData['ttl_debit_amt'], 2),
                'ttl_credit_amt' => number_format($ReportData['ttl_credit_amt'], 2),
                'ttl_balance' => number_format(abs($positive_tb), 2),
                'ttl_dr_or_cr' => ($tb >= 0) ? 'Dr' : 'Cr',
            );

            echo json_encode($json_data);
            exit;
        }
        return view('ACC.Report.cash_book');
    }

    public function getBankBook(Request $request)
    {

        if ($request->ajax()) {

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');

            $companyID = (empty($request->input('companyID'))) ? Common::getCompanyId() : $request->input('companyID');

            $projectID = (empty($request->input('projectID'))) ? null : $request->input('projectID');
            $projectTypeID = (empty($request->input('projectTypeID'))) ? null : $request->input('projectTypeID');
            $branchID = (empty($request->input('branchID'))) ? 1 : $request->input('branchID');
            $ledgerID = (empty($request->input('ledgerID'))) ? -1 : $request->input('ledgerID');
            $voucherTypeID = (empty($request->input('voucherTypeID'))) ? null : $request->input('voucherTypeID');

            $startDate = new DateTime($startDate);
            $startDate = $startDate->format('Y-m-d');

            $endDate = new DateTime($endDate);
            $endDate = $endDate->format('Y-m-d');

            $ledgerArray = array();

            if ($ledgerID == -1) {
                $ledgerCashIds = DB::table('acc_account_ledger as acl')
                    ->where([['acl.is_delete', 0], ['acl.is_active', 1]])
                    ->where('acc_type_id', 5)
                    ->pluck('id');

                $ledgerArray = $ledgerCashIds->toarray();
            } else {
                $ledgerArray = [$ledgerID];
            }

            $ReportData = ACC::cash_bankBookReport(true, $ledgerArray, $startDate, $endDate, $branchID, $voucherTypeID,
                 $companyID, $projectID, $projectTypeID);


            // Initialize  variable
            // $ob_ttl_balance = 0;

            ///
            $ob_ttl_balance = $ReportData['ob_ttl_balance'];
            $positive_ob_ttl_balance = $ReportData['ob_ttl_balance'];

            $tb = $ReportData['ttl_balance'];
            $positive_tb = $tb;

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $ReportData['DataSet'],

                // Opening Balance Calculation
                'ob_ttl_debit_amt' => number_format($ReportData['ob_ttl_debit_amt'], 2),
                'ob_ttl_credit_amt' => number_format($ReportData['ob_ttl_credit_amt'], 2),
                'ob_ttl_balance' => number_format(abs($positive_ob_ttl_balance), 2),
                'ob_dr_or_cr' => ($ob_ttl_balance >= 0) ? 'Dr' : 'Cr',

                // Sub Total Amount Calculation (only amount during date range)
                'sub_ttl_debit_amt' => number_format($ReportData['sub_ttl_debit_amt'], 2),
                'sub_ttl_credit_amt' => number_format($ReportData['sub_ttl_credit_amt'], 2),
                'sub_ttl_balance' => number_format(abs($positive_tb), 2),
                'sub_ttl_dr_or_cr' => ($tb >= 0) ? 'Dr' : 'Cr',

                // Opening Balance Calculation
                'ttl_debit_amt' => number_format($ReportData['ttl_debit_amt'], 2),
                'ttl_credit_amt' => number_format($ReportData['ttl_credit_amt'], 2),
                'ttl_balance' => number_format(abs($positive_tb), 2),
                'ttl_dr_or_cr' => ($tb >= 0) ? 'Dr' : 'Cr',
            );

            echo json_encode($json_data);
            exit;
        }
        return view('ACC.Report.bank_book');
    }

    public function getBalanceSheet(Request $request)
    {
        if ($request->ajax()) {

            // Searching variable
            // $brOpeningDate = (empty($request->input('brOPDate'))) ? null : $request->input('brOPDate');
            $brOpeningDate = Common::getBranchSoftwareStartDate();
            $searchBy = (empty($request->input('selected'))) ? null : $request->input('selected');
            $prevYearFiscal = (empty($request->input('prevFiscalYear'))) ? null : $request->input('prevFiscalYear');
            $prevYearFiscalCY = (empty($request->input('prevFiscalYearCY'))) ? null : $request->input('prevFiscalYearCY');
            $projectID = (empty($request->input('projectID'))) ? null : $request->input('projectID');
            $projectTypeID = (empty($request->input('projectTypeID'))) ? null : $request->input('projectTypeID');
            $branchID = (empty($request->input('branchID'))) ? 1 : $request->input('branchID');

            $startDateFY = (empty($request->input('startDateFY'))) ? null : $request->input('startDateFY');
            $endDateFY = (empty($request->input('endDateFY'))) ? null : $request->input('endDateFY');

            $startDateCY = (empty($request->input('startDateCY'))) ? null : $request->input('startDateCY');
            $endDateCY = (empty($request->input('endDateCY'))) ? null : $request->input('endDateCY');

            $startDateDR = (empty($request->input('startDateDR'))) ? null : $request->input('startDateDR');
            $endDateDR = (empty($request->input('endDateDR'))) ? null : $request->input('endDateDR');

            $depth_level = (empty($request->input('depth_level'))) ? null : $request->input('depth_level');
            $round_up = (empty($request->input('round_up'))) ? null : $request->input('round_up');
            $zero_balance = (empty($request->input('zero_balance'))) ? null : $request->input('zero_balance');

            $companyID = (empty($request->input('companyID'))) ? Common::getCompanyId() : $request->input('companyID');
            $prevYearRetrained = (empty($request->input('prevYearRetrained'))) ? null : $request->input('prevYearRetrained');
            $prevYearRetrainedCY = (empty($request->input('prevYearRetrainedCY'))) ? null : $request->input('prevYearRetrainedCY');

            // // Query For Ledger Head
            $ledgerHeads = DB::table('acc_account_ledger as acl')
                ->where(function ($ledgerHeads) use ($branchID) {
                    if (!empty($branchID)) {
                        $ledgerHeads->where('acl.branch_arr', 'LIKE', "%,{$branchID},%")
                            ->orWhere('acl.branch_arr', 'LIKE', "{$branchID},%")
                            ->orWhere('acl.branch_arr', 'LIKE', "%,{$branchID}")
                            ->orWhere('acl.branch_arr', 'LIKE', "{$branchID}");
                    }
                })
                ->where(function ($ledgerHeads) use ($projectID) {
                    if (!empty($projectID)) {
                        $ledgerHeads->where('acl.project_arr', 'LIKE', "%,{$projectID},%")
                            ->orWhere('acl.project_arr', 'LIKE', "{$projectID},%")
                            ->orWhere('acl.project_arr', 'LIKE', "%,{$projectID}")
                            ->orWhere('acl.project_arr', 'LIKE', "{$projectID}");
                    }
                })
                ->where([['acl.is_delete', 0], ['acl.is_active', 1]])
                ->select('acl.id', 'acl.name', 'acl.code', 'acl.is_group_head', 'acl.parent_id', 'acl.acc_type_id', 'acl.level')
                ->orderBy('acl.sys_code', 'ASC')
                ->orderBy('acl.order_by', 'ASC')
                ->get();

            ///////////////////////////////////////////////////////////////////////////////////
            // // Ledger Data Split For Transectional Head
            $ledgerChilds = $ledgerHeads->groupBy('is_group_head');
            $ledgerChilds = $ledgerChilds->toarray();
            $ledgerChilds = (isset($ledgerChilds[0])) ? $ledgerChilds[0] : array();

            // // Ledger Data Group BY parent Ledger Wise
            $ledgerHeadsInGR = $ledgerHeads->groupBy('parent_id');
            $ledgerHeadsInGR = $ledgerHeadsInGR->toarray();

            // // // Array Data make for parent wise transection head load set in Public Array
            foreach ($ledgerHeadsInGR as $key => $ParentLedgerData) {
                foreach ($ParentLedgerData as $RootLedger) {

                    if ($RootLedger->is_group_head == 0) {
                        // Public Variable
                        $this->PublicLedger[$RootLedger->parent_id][] = $RootLedger->id;
                    }

                    if (isset($ledgerHeadsInGR[$RootLedger->id])) {
                        self::prepareSubLedger($RootLedger->id, $ledgerHeadsInGR, $RootLedger->parent_id);
                    }
                }
            }
            // // // End Make Data

            // // // Search By Fiscal Year
            if ($searchBy == 1) {

                // Get Previous Year for Reatained Earning [ie if current year 2020, prv year for ret earning is 2018]
                $prevFiscalYear = DB::table('gnl_fiscal_year')
                    ->select('id', 'fy_start_date', 'fy_end_date')
                    ->where([['is_delete', 0], ['is_active', 1], ['company_id', $companyID], ['fy_name', $prevYearFiscal]])
                    ->first();

                // -------------------- Query For Previous Year ------------------//
                $prevFiscalYearRet = DB::table('gnl_fiscal_year')
                    ->select('id', 'fy_start_date', 'fy_end_date')
                    ->where([['is_delete', 0], ['is_active', 1], ['company_id', $companyID], ['fy_name', $prevYearRetrained]])
                    ->first();

                if ($prevFiscalYear) {

                    // Get Previous Fiscal Year Start And End Date
                    $prevStartDateFY = $prevFiscalYear->fy_start_date;
                    $prevEndDateFY = $prevFiscalYear->fy_end_date;

                    // // Query For Debit Amount
                    $fiscalDebitPrevYear = DB::table('acc_voucher as av')
                        ->where([['av.is_delete', 0], ['av.is_active', 1]])
                        ->whereIn('av.voucher_status', [1, 2])
                        ->where(function ($fiscalDebitPrevYear) use ($prevStartDateFY, $prevEndDateFY) {
                            if (!empty($prevStartDateFY) && !empty($prevEndDateFY)) {
                                $fiscalDebitPrevYear->whereBetween('av.voucher_date', [$prevStartDateFY, $prevEndDateFY]);
                            }
                        })
                        ->where(function ($fiscalDebitPrevYear) use ($branchID) {
                            if (!empty($branchID)) {
                                $fiscalDebitPrevYear->where('av.branch_id', $branchID);
                            }
                        })
                        ->where(function ($fiscalDebitPrevYear) use ($projectID) {
                            if (!empty($projectID)) {
                                $fiscalDebitPrevYear->where('av.project_id', $projectID);
                            }
                        })
                        ->where(function ($fiscalDebitPrevYear) use ($projectTypeID) {
                            if (!empty($projectTypeID)) {
                                $fiscalDebitPrevYear->where('av.project_type_id', $projectTypeID);
                            }
                        })
                        ->leftjoin('acc_voucher_details as avd', function ($fiscalDebitPrevYear) {
                            $fiscalDebitPrevYear->on('avd.voucher_id', 'av.id');
                        })
                        ->select('avd.debit_acc as ledger_id',
                            DB::raw('
                                IFNULL(SUM(
                                    CASE
                                        WHEN av.voucher_date >= "' . $prevStartDateFY . '" and av.voucher_date <= "'
                                . $prevEndDateFY . '"
                                        THEN avd.amount
                                    END
                                ), 0) as sum_debit_prev'
                            )
                        )
                        ->groupBy('avd.debit_acc')
                        ->orderBy('av.voucher_date', 'ASC')
                        ->get();

                    $DebitDataPrevYear = array();
                    foreach ($fiscalDebitPrevYear as $rowD) {
                        $DebitDataPrevYear[$rowD->ledger_id] = (array) $rowD;
                    }

                    // // Query For Credit Amount
                    $fiscalCreditPrevYear = DB::table('acc_voucher as av')
                        ->where([['av.is_delete', 0], ['av.is_active', 1]])
                        ->whereIn('av.voucher_status', [1, 2])
                        ->where(function ($fiscalCreditPrevYear) use ($prevStartDateFY, $prevEndDateFY) {
                            if (!empty($prevStartDateFY) && !empty($prevEndDateFY)) {
                                $fiscalCreditPrevYear->whereBetween('av.voucher_date', [$prevStartDateFY, $prevEndDateFY]);
                            }
                        })
                        ->where(function ($fiscalCreditPrevYear) use ($branchID) {
                            if (!empty($branchID)) {
                                $fiscalCreditPrevYear->where('av.branch_id', $branchID);
                            }
                        })
                        ->where(function ($fiscalCreditPrevYear) use ($projectID) {
                            if (!empty($projectID)) {
                                $fiscalCreditPrevYear->where('av.project_id', $projectID);
                            }
                        })
                        ->where(function ($fiscalCreditPrevYear) use ($projectTypeID) {
                            if (!empty($projectTypeID)) {
                                $fiscalCreditPrevYear->where('av.project_type_id', $projectTypeID);
                            }
                        })
                        ->leftjoin('acc_voucher_details as avd', function ($fiscalCreditPrevYear) {
                            $fiscalCreditPrevYear->on('avd.voucher_id', 'av.id');
                        })
                        ->select('avd.credit_acc as ledger_id',
                            DB::raw('
                                IFNULL(SUM(
                                    CASE
                                        WHEN av.voucher_date >= "' . $prevStartDateFY . '" and av.voucher_date <= "'
                                . $prevEndDateFY . '"
                                        THEN avd.amount
                                    END
                                ), 0) as sum_credit_prev'
                            )
                        )
                        ->groupBy('avd.credit_acc')
                        ->orderBy('av.voucher_date', 'ASC')
                        ->get();

                    $CreditDataPrevYear = array();
                    foreach ($fiscalCreditPrevYear as $rowC) {
                        $CreditDataPrevYear[$rowC->ledger_id] = (array) $rowC;
                    }
                }
                // ----------------------------END -------------------------------//

                //--------------------------Query For This Year ------------------//
                $startDateFY = (new DateTime($startDateFY))->format('Y-m-d');
                $endDateFY = (new DateTime($endDateFY))->format('Y-m-d');

                // // Query For Debit Amount
                $fiscalDebitThisYear = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($fiscalDebitThisYear) use ($startDateFY, $endDateFY) {
                        if (!empty($startDateFY) && !empty($endDateFY)) {
                            $fiscalDebitThisYear->whereBetween('av.voucher_date', [$startDateFY, $endDateFY]);
                        }
                    })
                    ->where(function ($fiscalDebitThisYear) use ($branchID) {
                        if (!empty($branchID)) {
                            $fiscalDebitThisYear->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($fiscalDebitThisYear) use ($projectID) {
                        if (!empty($projectID)) {
                            $fiscalDebitThisYear->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($fiscalDebitThisYear) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $fiscalDebitThisYear->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($fiscalDebitThisYear) {
                        $fiscalDebitThisYear->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDateFY . '" and av.voucher_date <= "' . $endDateFY . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_debit_dur'
                        )
                    )
                    ->groupBy('avd.debit_acc')
                    ->orderBy('av.voucher_date', 'ASC')
                    ->get();

                $DebitData = array();
                foreach ($fiscalDebitThisYear as $rowD) {
                    $DebitDataThisYear[$rowD->ledger_id] = (array) $rowD;
                }

                // // Query For Credit Amount
                $fiscalCreditThisYear = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($fiscalCreditThisYear) use ($startDateFY, $endDateFY) {
                        if (!empty($startDateFY) && !empty($endDateFY)) {
                            $fiscalCreditThisYear->whereBetween('av.voucher_date', [$startDateFY, $endDateFY]);
                        }
                    })
                    ->where(function ($fiscalCreditThisYear) use ($branchID) {
                        if (!empty($branchID)) {
                            $fiscalCreditThisYear->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($fiscalCreditThisYear) use ($projectID) {
                        if (!empty($projectID)) {
                            $fiscalCreditThisYear->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($fiscalCreditThisYear) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $fiscalCreditThisYear->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($fiscalCreditThisYear) {
                        $fiscalCreditThisYear->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDateFY . '" and av.voucher_date <= "' . $endDateFY . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_credit_dur'
                        )
                    )
                    ->groupBy('avd.credit_acc')
                    ->orderBy('av.voucher_date', 'ASC')
                    ->get();

                $CreditData = array();
                foreach ($fiscalCreditThisYear as $rowC) {
                    $CreditDataThisYear[$rowC->ledger_id] = (array) $rowC;
                }

            }

            // // // Search By Current Year
            if ($searchBy == 2) {

                // Get Previous Year for Reatained Earning [ie if current year 2020, prv year for ret earning is 2018]
                $prevFiscalYearRetCY = DB::table('gnl_fiscal_year')
                    ->select('id', 'fy_start_date', 'fy_end_date')
                    ->where([['is_delete', 0], ['is_active', 1], ['company_id', $companyID], ['fy_name', $prevYearRetrainedCY]])
                    ->first();

                // -------------------- Query For Previous Year ------------------//
                $prevFiscalYearCY = DB::table('gnl_fiscal_year')
                    ->select('id', 'fy_start_date', 'fy_end_date')
                    ->where([['is_delete', 0], ['is_active', 1], ['company_id', $companyID], ['fy_name', $prevYearFiscalCY]])
                    ->first();

                if ($prevFiscalYearCY) {
                    $prevStartDateCY = $prevFiscalYearCY->fy_start_date;
                    $prevEndDateCY = $prevFiscalYearCY->fy_end_date;

                    // // Query For Debit Amount
                    $fiscalDebitPrevYear = DB::table('acc_voucher as av')
                        ->where([['av.is_delete', 0], ['av.is_active', 1]])
                        ->whereIn('av.voucher_status', [1, 2])
                        ->where(function ($fiscalDebitPrevYear) use ($prevStartDateCY, $prevEndDateCY) {
                            if (!empty($prevStartDateCY) && !empty($prevEndDateCY)) {
                                $fiscalDebitPrevYear->whereBetween('av.voucher_date', [$prevStartDateCY, $prevEndDateCY]);
                            }
                        })
                        ->where(function ($fiscalDebitPrevYear) use ($branchID) {
                            if (!empty($branchID)) {
                                $fiscalDebitPrevYear->where('av.branch_id', $branchID);
                            }
                        })
                        ->where(function ($fiscalDebitPrevYear) use ($projectID) {
                            if (!empty($projectID)) {
                                $fiscalDebitPrevYear->where('av.project_id', $projectID);
                            }
                        })
                        ->where(function ($fiscalDebitPrevYear) use ($projectTypeID) {
                            if (!empty($projectTypeID)) {
                                $fiscalDebitPrevYear->where('av.project_type_id', $projectTypeID);
                            }
                        })
                        ->leftjoin('acc_voucher_details as avd', function ($fiscalDebitPrevYear) {
                            $fiscalDebitPrevYear->on('avd.voucher_id', 'av.id');
                        })
                        ->select('avd.debit_acc as ledger_id',
                            DB::raw('
                                IFNULL(SUM(
                                    CASE
                                        WHEN av.voucher_date >= "' . $prevStartDateCY . '" and av.voucher_date <= "'
                                . $prevEndDateCY . '"
                                        THEN avd.amount
                                    END
                                ), 0) as sum_debit_prev'
                            )
                        )
                        ->orderBy('avd.debit_acc', 'ASC')
                        ->groupBy('avd.debit_acc')
                        ->get();
                    $DebitDataPrevYear = array();
                    foreach ($fiscalDebitPrevYear as $rowD) {
                        $DebitDataPrevYear[$rowD->ledger_id] = (array) $rowD;
                    }

                    // // Query For Credit Amount
                    $fiscalCreditPrevYear = DB::table('acc_voucher as av')
                        ->where([['av.is_delete', 0], ['av.is_active', 1]])
                        ->whereIn('av.voucher_status', [1, 2])
                        ->where(function ($fiscalCreditPrevYear) use ($prevStartDateCY, $prevEndDateCY) {
                            if (!empty($prevStartDateCY) && !empty($prevEndDateCY)) {
                                $fiscalCreditPrevYear->whereBetween('av.voucher_date', [$prevStartDateCY, $prevEndDateCY]);
                            }
                        })
                        ->where(function ($fiscalCreditPrevYear) use ($branchID) {
                            if (!empty($branchID)) {
                                $fiscalCreditPrevYear->where('av.branch_id', $branchID);
                            }
                        })
                        ->where(function ($fiscalCreditPrevYear) use ($projectID) {
                            if (!empty($projectID)) {
                                $fiscalCreditPrevYear->where('av.project_id', $projectID);
                            }
                        })
                        ->where(function ($fiscalCreditPrevYear) use ($projectTypeID) {
                            if (!empty($projectTypeID)) {
                                $fiscalCreditPrevYear->where('av.project_type_id', $projectTypeID);
                            }
                        })
                        ->leftjoin('acc_voucher_details as avd', function ($fiscalCreditPrevYear) {
                            $fiscalCreditPrevYear->on('avd.voucher_id', 'av.id');
                        })
                        ->select('avd.credit_acc as ledger_id',
                            DB::raw('
                                IFNULL(SUM(
                                    CASE
                                        WHEN av.voucher_date >= "' . $prevStartDateCY . '" and av.voucher_date <= "'
                                . $prevEndDateCY . '"
                                        THEN avd.amount
                                    END
                                ), 0) as sum_credit_prev'
                            )
                        )
                        ->orderBy('avd.credit_acc', 'ASC')
                        ->groupBy('avd.credit_acc')
                    // ->toSql();
                        ->get();

                    $CreditDataPrevYear = array();
                    foreach ($fiscalCreditPrevYear as $rowC) {
                        $CreditDataPrevYear[$rowC->ledger_id] = (array) $rowC;
                    }
                }
                // ----------------------------END -------------------------------//

                //--------------------------Query For This Year ------------------//

                $startDateCY = (new DateTime($startDateCY))->format('Y-m-d');
                $endDateCY = (new DateTime($endDateCY))->format('Y-m-d');

                // // Query For Debit Amount
                $fiscalDebitThisYear = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($fiscalDebitThisYear) use ($startDateCY, $endDateCY) {
                        if (!empty($startDateCY) && !empty($endDateCY)) {
                            $fiscalDebitThisYear->whereBetween('av.voucher_date', [$startDateCY, $endDateCY]);
                        }
                    })
                    ->where(function ($fiscalDebitThisYear) use ($branchID) {
                        if (!empty($branchID)) {
                            $fiscalDebitThisYear->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($fiscalDebitThisYear) use ($projectID) {
                        if (!empty($projectID)) {
                            $fiscalDebitThisYear->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($fiscalDebitThisYear) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $fiscalDebitThisYear->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($fiscalDebitThisYear) {
                        $fiscalDebitThisYear->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDateCY . '" and av.voucher_date <= "' . $endDateCY . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_debit_dur'
                        )
                    )
                    ->orderBy('avd.debit_acc', 'ASC')
                    ->groupBy('avd.debit_acc')
                    ->get();

                $DebitDataThisYear = array();
                foreach ($fiscalDebitThisYear as $rowD) {
                    $DebitDataThisYear[$rowD->ledger_id] = (array) $rowD;
                }

                // // Query For Credit Amount
                $fiscalCreditThisYear = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($fiscalCreditThisYear) use ($startDateCY, $endDateCY) {
                        if (!empty($startDateCY) && !empty($endDateCY)) {
                            $fiscalCreditThisYear->whereBetween('av.voucher_date', [$startDateCY, $endDateCY]);
                        }
                    })
                    ->where(function ($fiscalCreditThisYear) use ($branchID) {
                        if (!empty($branchID)) {
                            $fiscalCreditThisYear->where('av.branch_id', $branchID);
                        }
                    })
                    ->where(function ($fiscalCreditThisYear) use ($projectID) {
                        if (!empty($projectID)) {
                            $fiscalCreditThisYear->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($fiscalCreditThisYear) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $fiscalCreditThisYear->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->leftjoin('acc_voucher_details as avd', function ($fiscalCreditThisYear) {
                        $fiscalCreditThisYear->on('avd.voucher_id', 'av.id');
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw('
                            IFNULL(SUM(
                                CASE
                                    WHEN av.voucher_date >= "' . $startDateCY . '" and av.voucher_date <= "' . $endDateCY . '"
                                    THEN avd.amount
                                END
                            ), 0) as sum_credit_dur'
                        )
                    )
                    ->orderBy('avd.credit_acc', 'ASC')
                    ->groupBy('avd.credit_acc')
                    ->get();

                $CreditDataThisYear = array();
                foreach ($fiscalCreditThisYear as $rowC) {
                    $CreditDataThisYear[$rowC->ledger_id] = (array) $rowC;
                }
            }
            // $ttl_debit_prev = 0;
            // $ttl_debit_dur = 0;

            // $ttl_credit_prev = 0;
            // $ttl_credit_dur = 0;

            // $ttl_balance_prev = 0;
            // $ttl_balance_dur = 0;

            $ttl_balance_prev_is = 0;
            $ttl_balance_dur_is = 0;

            $DataSet = array();
            $DataSetLedger = array();
            $TempDataSet = array();

            // // This calculation for transection ledger only
            foreach ($ledgerChilds as $row) {

                $debit_prev = 0;
                $debit_dur = 0;

                $credit_prev = 0;
                $credit_dur = 0;

                $previous_bl = 0;
                $current_bl = 0;

                if (isset($DebitDataPrevYear[$row->id])) {
                    $debit_prev = $DebitDataPrevYear[$row->id]['sum_debit_prev'];
                }
                if (isset($DebitDataThisYear[$row->id])) {
                    $debit_dur = $DebitDataThisYear[$row->id]['sum_debit_dur'];
                }

                if (isset($CreditDataPrevYear[$row->id])) {
                    $credit_prev = $CreditDataPrevYear[$row->id]['sum_credit_prev'];
                }
                if (isset($CreditDataThisYear[$row->id])) {
                    $credit_dur = $CreditDataThisYear[$row->id]['sum_credit_dur'];
                }

                if ($row->acc_type_id == 10 && $searchBy == 1) {

                    if ($prevFiscalYearRet) {
                        // Get Previous Fiscal Year Start And End Date
                        $prevStartDateFYRet = $prevFiscalYearRet->fy_start_date;
                        $prevEndDateFYRet = $prevFiscalYearRet->fy_end_date;

                        $previous_bl = ACC::funcIncomeStatememnt($brOpeningDate, $prevEndDateFYRet,
                            $ledgerChilds, $branchID, $projectID, $projectTypeID);
                        $current_bl = ACC::funcIncomeStatememnt($brOpeningDate, $prevEndDateFY,
                            $ledgerChilds, $branchID, $projectID, $projectTypeID);

                    }

                }

                if ($row->acc_type_id == 10 && $searchBy == 2) {

                    if ($prevFiscalYearRetCY) {
                        // Get Previous Fiscal Year Start And End Date
                        $prevStartDateCYRet = $prevFiscalYearRetCY->fy_start_date;
                        $prevEndDateCYRet = $prevFiscalYearRetCY->fy_end_date;

                        $previous_bl = ACC::funcIncomeStatememnt($brOpeningDate, $prevEndDateCYRet,
                            $ledgerChilds, $branchID, $projectID, $projectTypeID);
                        $current_bl = ACC::funcIncomeStatememnt($brOpeningDate, $prevEndDateCY,
                            $ledgerChilds, $branchID, $projectID, $projectTypeID);

                    }

                }

                //// Condition Implement for Round Up
                // if ($round_up == 1) {
                //     $debit_prev = round($debit_prev);
                //     $debit_dur = round($debit_dur);

                //     $credit_prev = round($credit_prev);
                //     $credit_dur = round($credit_dur);
                // }

                // For Income type transaction
                else if ($row->acc_type_id == 12 || $row->acc_type_id == 6 || $row->acc_type_id == 7 || $row->acc_type_id == 8 || $row->acc_type_id == 9) {
                    $previous_bl = $credit_prev - $debit_prev;
                    $current_bl = $credit_dur - $debit_dur;
                } else if ($row->acc_type_id != 10) {
                    $previous_bl = $debit_prev - $credit_prev;
                    $current_bl = $debit_dur - $credit_dur;
                }

                //// Condition Implement for Round Up
                if ($round_up == 1) {
                    $previous_bl = round($previous_bl);
                    $current_bl = round($current_bl);
                }

                // // Total Calculation
                // $ttl_debit_prev += $debit_prev;
                // $ttl_debit_dur += $debit_dur;

                // $ttl_credit_prev += $credit_prev;
                // $ttl_credit_dur += $credit_dur;

                // $ttl_balance_prev += $previous_bl;
                // $ttl_balance_dur += $current_bl;

                $DataSetLedger[$row->id] = [
                    // 'debit_prev' => $debit_prev,
                    // 'debit_dur' => $debit_dur,
                    // 'credit_prev' => $credit_prev,
                    // 'credit_dur' => $credit_dur,
                    'previous_bl' => $previous_bl,
                    'current_bl' => $current_bl,
                    'account_type' => $row->acc_type_id,
                ];
            }

            // // End Calculation for Transection Ledger
            $ttl_balance_prev_is = 0;

            // Profit/loss from Income statement
            if ($searchBy == 1) {
                if (isset($prevEndDateFY)) {
                    $ttl_balance_prev_is = ACC::funcIncomeStatememnt($prevStartDateFY, $prevEndDateFY,
                        $ledgerChilds, $branchID, $projectID, $projectTypeID);
                }
                $ttl_balance_dur_is = ACC::funcIncomeStatememnt($startDateFY, $endDateFY,
                    $ledgerChilds, $branchID, $projectID, $projectTypeID);
            }

            if ($searchBy == 2) {
                if (isset($prevEndDateCY)) {
                    $ttl_balance_prev_is = ACC::funcIncomeStatememnt($prevStartDateCY, $prevEndDateCY,
                        $ledgerChilds, $branchID, $projectID, $projectTypeID);
                }
                $ttl_balance_dur_is = ACC::funcIncomeStatememnt($startDateCY, $endDateCY,
                    $ledgerChilds, $branchID, $projectID, $projectTypeID);
            }

            // Total Asset
            $collection_asset = collect($DataSetLedger)->whereIn('account_type', [1, 2, 3, 4, 5]);
            $total_asset_prev = $collection_asset->sum('previous_bl');
            $total_asset_dur = $collection_asset->sum('current_bl');

            // Total Equity/ Total Fund = Equity + Profit/loss from Income statement
            $collection_equity = collect($DataSetLedger)->whereIn('account_type', [9, 10, 11]);
            $total_equity_prev = $collection_equity->sum('previous_bl') + $ttl_balance_prev_is;
            $total_equity_dur = $collection_equity->sum('current_bl') + $ttl_balance_dur_is;

            // Total Liabilities & Equity = Liabilities + Total Fund
            $collection_liabilities = collect($DataSetLedger)->whereIn('account_type', [6, 7, 8]);
            $total_liabilities_prev = $collection_liabilities->sum('previous_bl');
            $total_liabilities_dur = $collection_liabilities->sum('current_bl');

            $total_liab_equity_prev = $total_equity_prev + $total_liabilities_prev;
            $total_liab_equity_dur = $total_equity_dur + $total_liabilities_dur;

            // // Set View For Total asset , Total equity and Total Liability
            $ttl_balance_prev_is = $ttl_balance_prev_is != 0 ? number_format($ttl_balance_prev_is, 2) : '-';
            $ttl_balance_dur_is = $ttl_balance_dur_is != 0 ? number_format($ttl_balance_dur_is, 2) : '-';

            $total_asset_prev = $total_asset_prev != 0 ? number_format($total_asset_prev, 2) : '-';
            $total_asset_dur = $total_asset_dur != 0 ? number_format($total_asset_dur, 2) : '-';

            $total_equity_prev = $total_equity_prev != 0 ? number_format($total_equity_prev, 2) : '-';
            $total_equity_dur = $total_equity_dur != 0 ? number_format($total_equity_dur, 2) : '-';

            $total_liabilities_prev = $total_liabilities_prev != 0 ? number_format($total_liabilities_prev, 2) : '-';
            $total_liabilities_dur = $total_liabilities_dur != 0 ? number_format($total_liabilities_dur, 2) : '-';

            $total_liab_equity_prev = $total_liab_equity_prev != 0 ? number_format($total_liab_equity_prev, 2) : '-';
            $total_liab_equity_dur = $total_liab_equity_dur != 0 ? number_format($total_liab_equity_dur, 2) : '-';

            $total_asset = "<strong>" . 'Total asset' . "</strong>";
            $total_asset_prev = "<strong>" . $total_asset_prev . "</strong>";
            $total_asset_dur = "<strong>" . $total_asset_dur . "</strong>";

            $total_equity = "<strong class='text-uppercase'>" . 'Total Equity/Capital Fund' . "</strong>";
            $total_equity_prev = "<strong>" . $total_equity_prev . "</strong>";
            $total_equity_dur = "<strong>" . $total_equity_dur . "</strong>";

            $total_liabilities = "<strong>" . 'Total Liabilities' . "</strong>";
            $total_liabilities_prev = "<strong>" . $total_liabilities_prev . "</strong>";
            $total_liabilities_dur = "<strong>" . $total_liabilities_dur . "</strong>";

            $ttl_liabilities_equity = "<strong class='text-uppercase'>" . 'Total Liabilities & Equity' . "</strong>";
            $total_liab_equity_prev = "<strong>" . $total_liab_equity_prev . "</strong>";
            $total_liab_equity_dur = "<strong>" . $total_liab_equity_dur . "</strong>";

            // // // Calculation & Make visible Data for all ledger head
            $flag = 0;
            foreach ($ledgerHeads as $row) {
                if ($row->acc_type_id == 1 || $row->acc_type_id == 2 || $row->acc_type_id == 3 || $row->acc_type_id == 4 ||
                    $row->acc_type_id == 5 || $row->acc_type_id == 6 || $row->acc_type_id == 7 || $row->acc_type_id == 8 ||
                    $row->acc_type_id == 9 || $row->acc_type_id == 10 || $row->acc_type_id == 11) {
                    $tempSet = array();

                    $debit_prev = 0;
                    $debit_dur = 0;

                    $credit_prev = 0;
                    $credit_dur = 0;

                    $previous_bl = 0;
                    $current_bl = 0;

                    if ($row->is_group_head == 0) {

                        if (isset($DataSetLedger[$row->id])) {

                            $previous_bl = $DataSetLedger[$row->id]['previous_bl'];
                            $current_bl = $DataSetLedger[$row->id]['current_bl'];
                        }
                    } else if ($row->is_group_head == 1) {
                        if (isset($this->PublicLedger[$row->id])) {
                            $ChildTransLegers = $this->PublicLedger[$row->id];

                            foreach ($ChildTransLegers as $CL_ID) {

                                $previous_bl += $DataSetLedger[$CL_ID]['previous_bl'];
                                $current_bl += $DataSetLedger[$CL_ID]['current_bl'];
                            }

                        }
                    }

                    /// //// // --------------------------------------- Level Check -------------------------------
                    if (($depth_level != '')) {
                        if ($row->level != $depth_level) {
                            continue;
                        }
                    }

                    // // ////------------------- Condition Implement for Zero Balance ---------------------------
                    if ($zero_balance == 2) {
                        if (($previous_bl == 0) && ($current_bl == 0)) {
                            continue;
                        }
                    }

                    // // Data set for view
                    $particular_name = $row->name . " [" . $row->code . "]";

                    $previous_balance_txt = ($previous_bl != 0) ? number_format($previous_bl, 2) : '-';
                    $balance_dur_txt = ($current_bl != 0) ? number_format($current_bl, 2) : '-';

                    if ($row->is_group_head != 0) {
                        $previous_balance_txt = "<strong>" . $previous_balance_txt . "</strong>";
                        $balance_dur_txt = "<strong>" . $balance_dur_txt . "</strong>";
                        // $ttl_balance_prev = "<strong>" . $ttl_balance_prev . "</strong>";
                        // $ttl_balance_dur = "<strong>" . $ttl_balance_dur . "</strong>";

                        $particular_name = "<strong>" . $row->name . " [" . $row->code . "]</strong>";
                    }
                    // End

                    // For adding Total Asset Row
                    // Just before Liabilities type ledger Head, Add a row for total Asset
                    // which sums up Total balance for Asset type ledger
                    if ($row->acc_type_id == 6 && $flag == 0) {
                        $flag = 1;
                    }
                    if ($flag == 1) {
                        $tempSet = [
                            'particular_name' => $total_asset,
                            'previous_balance_txt' => $total_asset_prev,
                            'balance_dur_txt' => $total_asset_dur,
                        ];
                        $flag = 2;
                        $DataSet[] = $tempSet;
                    }
                    // End

                    // Just before Equity type ledger Head, Add a row for total Liabilities
                    if ($row->acc_type_id == 9 && $flag == 2) {
                        $flag = 3;
                    }
                    if ($flag == 3) {
                        $tempSet = [
                            'particular_name' => $total_liabilities,
                            'previous_balance_txt' => $total_liabilities_prev,
                            'balance_dur_txt' => $total_liabilities_dur,
                        ];
                        $flag = 4;
                        $DataSet[] = $tempSet;
                    }
                    // End

                    // This is Regular tempset i.e. add every ledger head except income and expense
                    $tempSet = [
                        'particular_name' => $particular_name,
                        'previous_balance_txt' => $previous_balance_txt,
                        'balance_dur_txt' => $balance_dur_txt,
                    ];
                    $DataSet[] = $tempSet;
                    // end
                }

            }

            // For adding Total Row
            // Calculation of Income statement
            $tempSet = [
                'particular_name' => 'Profit/Loss From Income Statement',
                'previous_balance_txt' => $ttl_balance_prev_is,
                'balance_dur_txt' => $ttl_balance_dur_is,

            ];
            $DataSet[] = $tempSet;
            // // End

            // Calculation of Total Equity
            $tempSet = [
                'particular_name' => $total_equity,
                'previous_balance_txt' => $total_equity_prev,
                'balance_dur_txt' => $total_equity_dur,

            ];
            $DataSet[] = $tempSet;
            // // End

            // Calculation of Total Liabilities and Equity
            $tempSet = [
                'particular_name' => $ttl_liabilities_equity,
                'previous_balance_txt' => $total_liab_equity_prev,
                'balance_dur_txt' => $total_liab_equity_dur,

            ];
            $DataSet[] = $tempSet;
            // // End

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,

            );

            echo json_encode($json_data);
            exit;

        } else {
            return view('ACC.Report.balance_sheet');
        }
    }

    public function getReceiptPayment(Request $request)
    {
        if ($request->ajax()) {

            // Searching variable
            // $brOpeningDate = (empty($request->input('brOPDate'))) ? null : $request->input('brOPDate');
            $brOpeningDate = Common::getBranchSoftwareStartDate();
            $searchBy = (empty($request->input('selected'))) ? null : $request->input('selected');
            $prevFiscalYear = (empty($request->input('prevFiscalYear'))) ? null : $request->input('prevFiscalYear');
            $projectID = (empty($request->input('projectID'))) ? null : $request->input('projectID');
            $projectTypeID = (empty($request->input('projectTypeID'))) ? null : $request->input('projectTypeID');
            $branchID = (empty($request->input('branchID'))) ? 1 : $request->input('branchID');

            $startDateFY = (empty($request->input('startDateFY'))) ? null : $request->input('startDateFY');
            $endDateFY = (empty($request->input('endDateFY'))) ? null : $request->input('endDateFY');

            $startDateCM = (empty($request->input('startDateCM'))) ? null : $request->input('startDateCM');
            $startDateCY = (empty($request->input('startDateCY'))) ? null : $request->input('startDateCY');
            $endDateCY = (empty($request->input('endDateCY'))) ? null : $request->input('endDateCY');
            // $endDateCMOB = (empty($request->input('endDateCMOB'))) ? null : $request->input('endDateCMOB');
            // $endDateCYOB = (empty($request->input('endDateCYOB'))) ? null : $request->input('endDateCYOB');

            $startDateDM = (empty($request->input('startDateDM'))) ? null : $request->input('startDateDM');
            $startDateDR = (empty($request->input('startDateDR'))) ? null : $request->input('startDateDR');
            $endDateDR = (empty($request->input('endDateDR'))) ? null : $request->input('endDateDR');
            // $endDateDMOB = (empty($request->input('endDateDMOB'))) ? null : $request->input('endDateDMOB');
            // $endDateDROB = (empty($request->input('endDateDROB'))) ? null : $request->input('endDateDROB');

            $depth_level = (empty($request->input('depth_level'))) ? null : $request->input('depth_level');
            $round_up = (empty($request->input('round_up'))) ? null : $request->input('round_up');
            $zero_balance = (empty($request->input('zero_balance'))) ? null : $request->input('zero_balance');

            $voucher_type = (empty($request->input('voucherType'))) ? null : $request->input('voucherType');
            $companyID = (empty($request->input('companyID'))) ? Common::getCompanyId() : $request->input('companyID');

            $prevFiscYear = DB::table('gnl_fiscal_year')
                ->select('id', 'fy_start_date', 'fy_end_date')
                ->where([['is_delete', 0], ['is_active', 1], ['company_id', $companyID], ['fy_name', $prevFiscalYear]])
                ->first();


            // // Initialize Variable
            $cash_ob_month = 0;
            $cash_ob_year = 0;
            $bank_ob_month = 0;
            $bank_ob_year = 0;
            $cash_receipt_month = 0;
            $cash_receipt_year = 0;
            $cash_receipt_cum = 0;
            $bank_receipt_month = 0;
            $bank_receipt_year = 0;
            $bank_receipt_cum = 0;
            $cash_payment_month = 0;
            $cash_payment_year = 0;
            $cash_payment_cum = 0;
            $bank_payment_month = 0;
            $bank_payment_year = 0;
            $bank_payment_cum = 0;

            // For Voucher Type Cash
            if ($voucher_type == 1) {

                // Get Ids of Cash and Bank Type Ledger
                $ledgerCashBankIds = DB::table('acc_account_ledger as acl')
                    ->where([['acl.is_delete', 0], ['acl.is_Active', 1]])
                    ->whereIn('acl.acc_type_id', [4, 5])
                    ->pluck('acl.id');

                $ledgerCashBankIds = $ledgerCashBankIds->toarray();

                $ledgerCashIds = DB::table('acc_account_ledger as acl')
                    ->where([['acl.is_delete', 0], ['acl.is_Active', 1]])
                    ->where('acl.acc_type_id', 4)
                    ->pluck('acl.id');

                $ledgerCashIds = $ledgerCashIds->toarray();

                $ledgerBankIds = DB::table('acc_account_ledger as acl')
                    ->where([['acl.is_delete', 0], ['acl.is_Active', 1]])
                    ->where('acl.acc_type_id', 5)
                    ->pluck('acl.id');

                $ledgerBankIds = $ledgerBankIds->toarray();

                // Get only debit Transaction Ids
                $debitHead = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($debitHead) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $debitHead->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $debitHead->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($debitHead) use ($projectID) {
                        if (!empty($projectID)) {
                            $debitHead->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($debitHead) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $debitHead->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($debitHead) use ($ledgerCashBankIds) {
                        $debitHead->on('avd.voucher_id', 'av.id')
                            ->where(function ($debitHead) use ($ledgerCashBankIds) {
                                $debitHead->whereIn('avd.debit_acc', $ledgerCashBankIds);
                                // ->whereNotIn('avd.credit_acc', $ledgerCashBankIds);
                            });
                    })
                // ->groupBy('avd.debit_acc')
                    ->distinct('avd.credit_acc')
                    ->pluck('avd.credit_acc');

                // Get only Credit Transaction Ids
                $creditHead = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($creditHead) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $creditHead->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $creditHead->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($creditHead) use ($projectID) {
                        if (!empty($projectID)) {
                            $creditHead->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($creditHead) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $creditHead->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($creditHead) use ($ledgerCashBankIds) {
                        $creditHead->on('avd.voucher_id', 'av.id')
                            ->where(function ($creditHead) use ($ledgerCashBankIds) {
                                $creditHead->whereIn('avd.credit_acc', $ledgerCashBankIds);
                                // ->whereNotIn('avd.debit_acc', $ledgerCashBankIds);
                            });
                    })
                // ->groupBy('avd.credit_acc')
                    ->distinct('avd.debit_acc')
                    ->pluck('avd.debit_acc');

                // dd($creditHead);

                $accountHeads = array();
                $accountHeads = array_merge($debitHead->toarray(), $creditHead->toarray());
                // dd($accountHeads);

                // // Find Parents of each Transactional Head
                foreach ($accountHeads as $key => $value) {
                    $this->findParent($value);
                }

                $accountHeads = array_merge($accountHeads, $this->accountSet);

                // // Ledgger Heads of those which had a transaction with cash and bank account type
                $ledgerHeads = DB::table('acc_account_ledger as acl')
                    ->where([['acl.is_delete', 0], ['acl.is_active', 1]])
                    ->whereIn('acl.id', $accountHeads)
                    ->where(function ($ledgerHeads) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID > 0) {
                                $ledgerHeads->where('acl.branch_arr', 'LIKE', "%,{$branchID},%")
                                    ->orWhere('acl.branch_arr', 'LIKE', "{$branchID},%")
                                    ->orWhere('acl.branch_arr', 'LIKE', "%,{$branchID}")
                                    ->orWhere('acl.branch_arr', 'LIKE', "{$branchID}"); // Individual Branch
                            }
                            if ($branchID == -2) {
                                $ledgerHeads->where('acl.branch_arr', 'NOT LIKE', "%,{$branchID},%")
                                    ->orWhere('acl.branch_arr', 'NOT LIKE', "{$branchID},%")
                                    ->orWhere('acl.branch_arr', 'NOT LIKE', "%,{$branchID}")
                                    ->orWhere('acl.branch_arr', 'NOT LIKE', "{$branchID}"); // Individual Branch
                            }
                        }
                    })
                    ->where(function ($ledgerHeads) use ($projectID) {
                        if (!empty($projectID)) {
                            $ledgerHeads->where('acl.project_arr', 'LIKE', "%,{$projectID},%")
                                ->orWhere('acl.project_arr', 'LIKE', "{$projectID},%")
                                ->orWhere('acl.project_arr', 'LIKE', "%,{$projectID}")
                                ->orWhere('acl.project_arr', 'LIKE', "{$projectID}");
                        }
                    })
                    ->select('acl.id', 'acl.name', 'acl.code', 'acl.is_group_head', 'acl.parent_id', 'acl.acc_type_id', 'acl.level')
                    ->orderBy('acl.sys_code', 'ASC')
                    ->orderBy('acl.order_by', 'ASC')
                    ->get();

                // // Ledger Data Group BY parent Ledger Wise
                $ledgerHeadsInGR = collect($ledgerHeads)->groupBy('parent_id');
                $ledgerHeadsInGR = $ledgerHeadsInGR->toarray();

                // dd($ledgerHeadsInGR);

                // // // // Array Data make for parent wise transection head load set in Public Array
                foreach ($ledgerHeadsInGR as $key => $ParentLedgerData) {
                    foreach ($ParentLedgerData as $RootLedger) {

                        if ($RootLedger->is_group_head == 0) {
                            // Public Variable
                            $this->PublicLedger[$RootLedger->parent_id][] = $RootLedger->id;
                        }

                        if (isset($ledgerHeadsInGR[$RootLedger->id])) {
                            self::prepareSubLedger($RootLedger->id, $ledgerHeadsInGR, $RootLedger->parent_id);
                        }
                    }
                }
                // // // End Make Data
            }

            if ($voucher_type == 2) {

                // Get Ids of Cash and Bank Type Ledger
                $ledgerCashBankIds = DB::table('acc_account_ledger as acl')
                    ->where([['acl.is_delete', 0], ['acl.is_Active', 1]])
                    ->pluck('acl.id');

                $ledgerCashBankIds = $ledgerCashBankIds->toarray();

                $ledgerCashIds = DB::table('acc_account_ledger as acl')
                    ->where([['acl.is_delete', 0], ['acl.is_Active', 1]])
                    ->where('acl.acc_type_id', 4)
                    ->pluck('acl.id');

                $ledgerCashIds = $ledgerCashIds->toarray();

                $ledgerBankIds = DB::table('acc_account_ledger as acl')
                    ->where([['acl.is_delete', 0], ['acl.is_Active', 1]])
                    ->where('acl.acc_type_id', 5)
                    ->pluck('acl.id');

                $ledgerBankIds = $ledgerBankIds->toarray();

                // Get only debit Transaction Ids
                $debitHead = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($debitHead) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $debitHead->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $debitHead->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($debitHead) use ($projectID) {
                        if (!empty($projectID)) {
                            $debitHead->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($debitHead) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $debitHead->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($debitHead) use ($ledgerCashBankIds) {
                        $debitHead->on('avd.voucher_id', 'av.id')
                            ->where(function ($debitHead) use ($ledgerCashBankIds) {
                                $debitHead->whereIn('avd.debit_acc', $ledgerCashBankIds);
                                // ->whereNotIn('avd.credit_acc', $ledgerCashBankIds);
                            });
                    })
                // ->groupBy('avd.debit_acc')
                    ->distinct('avd.credit_acc')
                    ->pluck('avd.credit_acc');

                // Get only Credit Transaction Ids
                $creditHead = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($creditHead) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $creditHead->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $creditHead->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($creditHead) use ($projectID) {
                        if (!empty($projectID)) {
                            $creditHead->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($creditHead) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $creditHead->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($creditHead) use ($ledgerCashBankIds) {
                        $creditHead->on('avd.voucher_id', 'av.id')
                            ->where(function ($creditHead) use ($ledgerCashBankIds) {
                                $creditHead->whereIn('avd.credit_acc', $ledgerCashBankIds);
                                // ->whereNotIn('avd.debit_acc', $ledgerCashBankIds);
                            });
                    })
                // ->groupBy('avd.credit_acc')
                    ->distinct('avd.debit_acc')
                    ->pluck('avd.debit_acc');

                // dd($creditHead);

                $accountHeads = array();
                $accountHeads = array_merge($debitHead->toarray(), $creditHead->toarray());
                // dd($accountHeads);

                // // Find Parents of each Transactional Head
                foreach ($accountHeads as $key => $value) {
                    $this->findParent($value);
                }

                $accountHeads = array_merge($accountHeads, $this->accountSet);

                // // Ledgger Heads of those which had a transaction with cash and bank account type
                $ledgerHeads = DB::table('acc_account_ledger as acl')
                    ->where([['acl.is_delete', 0], ['acl.is_active', 1]])
                    ->whereIn('acl.id', $accountHeads)
                    ->where(function ($ledgerHeads) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID > 0) {
                                $ledgerHeads->where('acl.branch_arr', 'LIKE', "%,{$branchID},%")
                                    ->orWhere('acl.branch_arr', 'LIKE', "{$branchID},%")
                                    ->orWhere('acl.branch_arr', 'LIKE', "%,{$branchID}")
                                    ->orWhere('acl.branch_arr', 'LIKE', "{$branchID}"); // Individual Branch
                            }
                            if ($branchID == -2) {
                                $ledgerHeads->where('acl.branch_arr', 'NOT LIKE', "%,{$branchID},%")
                                    ->orWhere('acl.branch_arr', 'NOT LIKE', "{$branchID},%")
                                    ->orWhere('acl.branch_arr', 'NOT LIKE', "%,{$branchID}")
                                    ->orWhere('acl.branch_arr', 'NOT LIKE', "{$branchID}"); // Individual Branch
                            }
                        }
                    })
                    ->where(function ($ledgerHeads) use ($projectID) {
                        if (!empty($projectID)) {
                            $ledgerHeads->where('acl.project_arr', 'LIKE', "%,{$projectID},%")
                                ->orWhere('acl.project_arr', 'LIKE', "{$projectID},%")
                                ->orWhere('acl.project_arr', 'LIKE', "%,{$projectID}")
                                ->orWhere('acl.project_arr', 'LIKE', "{$projectID}");
                        }
                    })
                    ->select('acl.id', 'acl.name', 'acl.code', 'acl.is_group_head', 'acl.parent_id', 'acl.acc_type_id', 'acl.level')
                    ->orderBy('acl.sys_code', 'ASC')
                    ->orderBy('acl.order_by', 'ASC')
                    ->get();

                // // Ledger Data Group BY parent Ledger Wise
                $ledgerHeadsInGR = collect($ledgerHeads)->groupBy('parent_id');
                $ledgerHeadsInGR = $ledgerHeadsInGR->toarray();

                // dd($ledgerHeadsInGR);

                // // // // Array Data make for parent wise transection head load set in Public Array
                foreach ($ledgerHeadsInGR as $key => $ParentLedgerData) {
                    foreach ($ParentLedgerData as $RootLedger) {

                        if ($RootLedger->is_group_head == 0) {
                            // Public Variable
                            $this->PublicLedger[$RootLedger->parent_id][] = $RootLedger->id;
                        }

                        if (isset($ledgerHeadsInGR[$RootLedger->id])) {
                            self::prepareSubLedger($RootLedger->id, $ledgerHeadsInGR, $RootLedger->parent_id);
                        }
                    }
                }
                // // // End Make Data
            }

            if ($voucher_type == 3) {

                // Get Ids of Cash and Bank Type Ledger
                $ledgerCashBankIds = DB::table('acc_account_ledger as acl')
                    ->where([['acl.is_delete', 0], ['acl.is_Active', 1]])
                    ->whereIn('acl.acc_type_id', [1, 2, 3, 6, 7, 8, 9, 10, 11, 12, 13])
                    ->pluck('acl.id');

                $ledgerCashBankIds = $ledgerCashBankIds->toarray();

                $ledgerCashIds = DB::table('acc_account_ledger as acl')
                    ->where([['acl.is_delete', 0], ['acl.is_Active', 1]])
                    ->where('acl.acc_type_id', 4)
                    ->pluck('acl.id');

                $ledgerCashIds = $ledgerCashIds->toarray();

                $ledgerBankIds = DB::table('acc_account_ledger as acl')
                    ->where([['acl.is_delete', 0], ['acl.is_Active', 1]])
                    ->where('acl.acc_type_id', 5)
                    ->pluck('acl.id');

                $ledgerBankIds = $ledgerBankIds->toarray();

                // Get only debit Transaction Ids
                $debitHead = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($debitHead) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $debitHead->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $debitHead->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($debitHead) use ($projectID) {
                        if (!empty($projectID)) {
                            $debitHead->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($debitHead) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $debitHead->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($debitHead) use ($ledgerCashBankIds) {
                        $debitHead->on('avd.voucher_id', 'av.id')
                            ->where(function ($debitHead) use ($ledgerCashBankIds) {
                                $debitHead->whereIn('avd.debit_acc', $ledgerCashBankIds);
                                // ->whereNotIn('avd.credit_acc', $ledgerCashBankIds);
                            });
                    })
                // ->groupBy('avd.debit_acc')
                    ->distinct('avd.credit_acc')
                    ->pluck('avd.credit_acc');

                // Get only Credit Transaction Ids
                $creditHead = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($creditHead) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $creditHead->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $creditHead->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($creditHead) use ($projectID) {
                        if (!empty($projectID)) {
                            $creditHead->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($creditHead) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $creditHead->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($creditHead) use ($ledgerCashBankIds) {
                        $creditHead->on('avd.voucher_id', 'av.id')
                            ->where(function ($creditHead) use ($ledgerCashBankIds) {
                                $creditHead->whereIn('avd.credit_acc', $ledgerCashBankIds);
                                // ->whereNotIn('avd.debit_acc', $ledgerCashBankIds);
                            });
                    })
                // ->groupBy('avd.credit_acc')
                    ->distinct('avd.debit_acc')
                    ->pluck('avd.debit_acc');

                // dd($creditHead);

                $accountHeads = array();
                $accountHeads = array_merge($debitHead->toarray(), $creditHead->toarray());
                // dd($accountHeads);

                // // Find Parents of each Transactional Head
                foreach ($accountHeads as $key => $value) {
                    $this->findParent($value);
                }

                $accountHeads = array_merge($accountHeads, $this->accountSet);

                // // Ledgger Heads of those which had a transaction with cash and bank account type
                $ledgerHeads = DB::table('acc_account_ledger as acl')
                    ->where([['acl.is_delete', 0], ['acl.is_active', 1]])
                    ->whereIn('acl.id', $accountHeads)
                    ->where(function ($ledgerHeads) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID > 0) {
                                $ledgerHeads->where('acl.branch_arr', 'LIKE', "%,{$branchID},%")
                                    ->orWhere('acl.branch_arr', 'LIKE', "{$branchID},%")
                                    ->orWhere('acl.branch_arr', 'LIKE', "%,{$branchID}")
                                    ->orWhere('acl.branch_arr', 'LIKE', "{$branchID}"); // Individual Branch
                            }
                            if ($branchID == -2) {
                                $ledgerHeads->where('acl.branch_arr', 'NOT LIKE', "%,{$branchID},%")
                                    ->orWhere('acl.branch_arr', 'NOT LIKE', "{$branchID},%")
                                    ->orWhere('acl.branch_arr', 'NOT LIKE', "%,{$branchID}")
                                    ->orWhere('acl.branch_arr', 'NOT LIKE', "{$branchID}"); // Individual Branch
                            }
                        }
                    })
                    ->where(function ($ledgerHeads) use ($projectID) {
                        if (!empty($projectID)) {
                            $ledgerHeads->where('acl.project_arr', 'LIKE', "%,{$projectID},%")
                                ->orWhere('acl.project_arr', 'LIKE', "{$projectID},%")
                                ->orWhere('acl.project_arr', 'LIKE', "%,{$projectID}")
                                ->orWhere('acl.project_arr', 'LIKE', "{$projectID}");
                        }
                    })
                    ->select('acl.id', 'acl.name', 'acl.code', 'acl.is_group_head', 'acl.parent_id', 'acl.acc_type_id', 'acl.level')
                    ->orderBy('acl.sys_code', 'ASC')
                    ->orderBy('acl.order_by', 'ASC')
                    ->get();


                // // Ledger Data Group BY parent Ledger Wise
                $ledgerHeadsInGR = collect($ledgerHeads)->groupBy('parent_id');
                $ledgerHeadsInGR = $ledgerHeadsInGR->toarray();

                // dd($ledgerHeadsInGR);

                // // // // Array Data make for parent wise transection head load set in Public Array
                foreach ($ledgerHeadsInGR as $key => $ParentLedgerData) {
                    foreach ($ParentLedgerData as $RootLedger) {

                        if ($RootLedger->is_group_head == 0) {
                            // Public Variable
                            $this->PublicLedger[$RootLedger->parent_id][] = $RootLedger->id;
                        }

                        if (isset($ledgerHeadsInGR[$RootLedger->id])) {
                            self::prepareSubLedger($RootLedger->id, $ledgerHeadsInGR, $RootLedger->parent_id);
                        }
                    }
                }
                // // // End Make Data
            }

            // // // End Make Data

            // // // Search By Fiscal Year
            if ($searchBy == 1) {

                $startDateFY = (new DateTime($startDateFY))->format('Y-m-d');
                $endDateFY = (new DateTime($endDateFY))->format('Y-m-d');

                if ($prevFiscYear) {
                    $startDatePY = $prevFiscYear->fy_start_date;
                    $endDatePY = $prevFiscYear->fy_end_date;
                }

                if ($voucher_type == 1 || $voucher_type == 2) {
                    // Balace before start date to calculate Opening Balance
                    if (!empty($startDatePY) && !empty($endDatePY)) {
                        $cash_ob_month = $this->openingBalanceDateRange($brOpeningDate, $startDatePY, $ledgerCashIds, $branchID,
                            $projectID, $projectTypeID);

                        $bank_ob_month = $this->openingBalanceDateRange($brOpeningDate, $startDatePY, $ledgerBankIds, $branchID,
                            $projectID, $projectTypeID);

                        $cash_receipt_month = $this->receiptDateRange($startDatePY, $endDatePY, $ledgerCashIds, $branchID,
                            $projectID, $projectTypeID);
                        $bank_receipt_month = $this->receiptDateRange($startDatePY, $endDatePY, $ledgerBankIds, $branchID,
                            $projectID, $projectTypeID);

                        $cash_payment_month = $this->paymentDateRange($startDatePY, $endDatePY, $ledgerCashIds, $branchID,
                            $projectID, $projectTypeID);
                        $bank_payment_month = $this->paymentDateRange($startDatePY, $endDatePY, $ledgerBankIds, $branchID,
                            $projectID, $projectTypeID);

                        $currentMonthReceiptQuery = DB::table('acc_voucher as av')
                            ->where([['av.is_delete', 0], ['av.is_active', 1]])
                            ->whereBetween('av.voucher_date', [$startDatePY, $endDatePY])
                            ->whereIn('av.voucher_status', [1, 2])
                            ->where(function ($currentMonthReceiptQuery) use ($branchID) {
                                if (!empty($branchID)) {
                                    if ($branchID >= 0) {
                                        $currentMonthReceiptQuery->where('av.branch_id', $branchID); // Individual Branch
                                    } else if ($branchID == -2) {
                                        $currentMonthReceiptQuery->where('av.branch_id', '!=', 1); // Branch without head office
                                    }
                                }
                            })
                            ->where(function ($currentMonthReceiptQuery) use ($projectID) {
                                if (!empty($projectID)) {
                                    $currentMonthReceiptQuery->where('av.project_id', $projectID);
                                }
                            })
                            ->where(function ($currentMonthReceiptQuery) use ($projectTypeID) {
                                if (!empty($projectTypeID)) {
                                    $currentMonthReceiptQuery->where('av.project_type_id', $projectTypeID);
                                }
                            })
                            ->join('acc_voucher_details as avd', function ($currentMonthReceiptQuery) use ($ledgerCashBankIds) {
                                $currentMonthReceiptQuery->on('avd.voucher_id', 'av.id')
                                    ->where(function ($currentMonthReceiptQuery) use ($ledgerCashBankIds) {
                                        $currentMonthReceiptQuery
                                            ->whereIn('avd.debit_acc', $ledgerCashBankIds);
                                    });
                            })
                            ->join('acc_account_ledger as acl', function ($currentMonthReceiptQuery) {
                                $currentMonthReceiptQuery->on('avd.credit_acc', 'acl.id')
                                    ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                            })
                            ->select('avd.credit_acc as ledger_id',
                                DB::raw(
                                    'IFNULL(SUM(avd.amount),0) as sum_debit_month'
                                )
                            )
                            ->groupBy('avd.credit_acc')
                            ->get();

                        $currentMonthReceipt = array();
                        foreach ($currentMonthReceiptQuery as $rowD) {
                            $currentMonthReceipt[$rowD->ledger_id] = (array) $rowD;
                        }

                        $currentMonthPaymentQuery = DB::table('acc_voucher as av')
                            ->where([['av.is_delete', 0], ['av.is_active', 1]])
                            ->whereBetween('av.voucher_date', [$startDatePY, $endDatePY])
                            ->whereIn('av.voucher_status', [1, 2])
                            ->where(function ($currentMonthPaymentQuery) use ($branchID) {
                                if (!empty($branchID)) {
                                    if ($branchID >= 0) {
                                        $currentMonthPaymentQuery->where('av.branch_id', $branchID); // Individual Branch
                                    } else if ($branchID == -2) {
                                        $currentMonthPaymentQuery->where('av.branch_id', '!=', 1); // Branch without head office
                                    }
                                }
                            })
                            ->where(function ($currentMonthPaymentQuery) use ($projectID) {
                                if (!empty($projectID)) {
                                    $currentMonthPaymentQuery->where('av.project_id', $projectID);
                                }
                            })
                            ->where(function ($currentMonthPaymentQuery) use ($projectTypeID) {
                                if (!empty($projectTypeID)) {
                                    $currentMonthPaymentQuery->where('av.project_type_id', $projectTypeID);
                                }
                            })
                            ->join('acc_voucher_details as avd', function ($currentMonthPaymentQuery) use ($ledgerCashBankIds) {
                                $currentMonthPaymentQuery->on('avd.voucher_id', 'av.id')
                                    ->where(function ($currentMonthPaymentQuery) use ($ledgerCashBankIds) {
                                        $currentMonthPaymentQuery
                                            ->whereIn('avd.credit_acc', $ledgerCashBankIds);
                                    });
                            })
                            ->join('acc_account_ledger as acl', function ($currentMonthPaymentQuery) {
                                $currentMonthPaymentQuery->on('avd.debit_acc', 'acl.id')
                                    ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                            })
                            ->select('avd.debit_acc as ledger_id',
                                DB::raw(
                                    'IFNULL(SUM(avd.amount),0) as sum_credit_month'
                                )
                            )
                            ->groupBy('avd.debit_acc')
                            ->get();

                        $currentMonthPayment = array();
                        foreach ($currentMonthPaymentQuery as $rowC) {
                            $currentMonthPayment[$rowC->ledger_id] = (array) $rowC;
                        }
                    }

                    $cash_ob_year = $this->openingBalanceDateRange($brOpeningDate, $startDateFY, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_ob_year = $this->openingBalanceDateRange($brOpeningDate, $startDateFY, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_receipt_year = $this->receiptDateRange($startDateFY, $endDateFY, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_receipt_cum = $this->receiptDateRange($brOpeningDate, $endDateFY, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_receipt_year = $this->receiptDateRange($startDateFY, $endDateFY, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_receipt_cum = $this->receiptDateRange($brOpeningDate, $endDateFY, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_payment_year = $this->paymentDateRange($startDateFY, $endDateFY, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_payment_cum = $this->paymentDateRange($brOpeningDate, $endDateFY, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_payment_year = $this->paymentDateRange($startDateFY, $endDateFY, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_payment_cum = $this->paymentDateRange($brOpeningDate, $endDateFY, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);
                }

                $currentYearReceiptQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$startDateFY, $endDateFY])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($currentYearReceiptQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $currentYearReceiptQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $currentYearReceiptQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($currentYearReceiptQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $currentYearReceiptQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($currentYearReceiptQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $currentYearReceiptQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($currentYearReceiptQuery) use ($ledgerCashBankIds) {
                        $currentYearReceiptQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($currentYearReceiptQuery) use ($ledgerCashBankIds) {
                                $currentYearReceiptQuery
                                    ->whereIn('avd.debit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($currentYearReceiptQuery) {
                        $currentYearReceiptQuery->on('avd.credit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_debit_dur'
                        )
                    )
                    ->groupBy('avd.credit_acc')
                    ->get();

                $currentYearReceipt = array();
                foreach ($currentYearReceiptQuery as $rowD) {
                    $currentYearReceipt[$rowD->ledger_id] = (array) $rowD;
                }

                $cumulativeReceiptQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$brOpeningDate, $endDateFY])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($cumulativeReceiptQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $cumulativeReceiptQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $cumulativeReceiptQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($cumulativeReceiptQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $cumulativeReceiptQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($cumulativeReceiptQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $cumulativeReceiptQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($cumulativeReceiptQuery) use ($ledgerCashBankIds) {
                        $cumulativeReceiptQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($cumulativeReceiptQuery) use ($ledgerCashBankIds) {
                                $cumulativeReceiptQuery
                                    ->whereIn('avd.debit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($cumulativeReceiptQuery) {
                        $cumulativeReceiptQuery->on('avd.credit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_debit_clo'
                        )
                    )
                    ->groupBy('avd.credit_acc')
                    ->get();

                $cumulativeReceipt = array();
                foreach ($cumulativeReceiptQuery as $rowD) {
                    $cumulativeReceipt[$rowD->ledger_id] = (array) $rowD;
                }

                $currentYearPaymentQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$startDateFY, $endDateFY])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($currentYearPaymentQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $currentYearPaymentQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $currentYearPaymentQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($currentYearPaymentQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $currentYearPaymentQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($currentYearPaymentQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $currentYearPaymentQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($currentYearPaymentQuery) use ($ledgerCashBankIds) {
                        $currentYearPaymentQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($currentYearPaymentQuery) use ($ledgerCashBankIds) {
                                $currentYearPaymentQuery
                                    ->whereIn('avd.credit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($currentYearPaymentQuery) {
                        $currentYearPaymentQuery->on('avd.debit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_credit_dur'
                        )
                    )
                    ->groupBy('avd.debit_acc')
                    ->get();

                $currentYearPayment = array();
                foreach ($currentYearPaymentQuery as $rowC) {
                    $currentYearPayment[$rowC->ledger_id] = (array) $rowC;
                }

                $cumulativePaymentQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$startDateFY, $endDateFY])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($cumulativePaymentQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $cumulativePaymentQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $cumulativePaymentQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($cumulativePaymentQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $cumulativePaymentQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($cumulativePaymentQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $cumulativePaymentQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($cumulativePaymentQuery) use ($ledgerCashBankIds) {
                        $cumulativePaymentQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($cumulativePaymentQuery) use ($ledgerCashBankIds) {
                                $cumulativePaymentQuery
                                    ->whereIn('avd.credit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($cumulativePaymentQuery) {
                        $cumulativePaymentQuery->on('avd.debit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_credit_clo'
                        )
                    )
                    ->groupBy('avd.debit_acc')
                    ->get();

                $cumulativePayment = array();
                foreach ($cumulativePaymentQuery as $rowC) {
                    $cumulativePayment[$rowC->ledger_id] = (array) $rowC;
                }
                // dd($currentMonthPayment);

            }

            // // // Search By Current Year
            if ($searchBy == 2) {

                $startDateCY = (new DateTime($startDateCY))->format('Y-m-d');
                $endDateCY = (new DateTime($endDateCY))->format('Y-m-d');

                $startDateCM = (new DateTime($startDateCM))->format('Y-m-d');
                $endDateCM = $endDateCY;

                if ($voucher_type == 1 || $voucher_type == 2) {
                    // Balace before start date to calculate Opening Balance
                    $cash_ob_month = $this->openingBalanceDateRange($brOpeningDate, $startDateCM, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_ob_year = $this->openingBalanceDateRange($brOpeningDate, $startDateCY, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_ob_month = $this->openingBalanceDateRange($brOpeningDate, $startDateCM, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_ob_year = $this->openingBalanceDateRange($brOpeningDate, $startDateCY, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_receipt_month = $this->receiptDateRange($startDateCM, $endDateCM, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_receipt_year = $this->receiptDateRange($startDateCY, $endDateCY, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_receipt_cum = $this->receiptDateRange($brOpeningDate, $endDateCY, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_receipt_month = $this->receiptDateRange($startDateCM, $endDateCM, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_receipt_year = $this->receiptDateRange($startDateCY, $endDateCY, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_receipt_cum = $this->receiptDateRange($brOpeningDate, $endDateCY, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_payment_month = $this->paymentDateRange($startDateCM, $endDateCM, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_payment_year = $this->paymentDateRange($startDateCY, $endDateCY, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_payment_cum = $this->paymentDateRange($brOpeningDate, $endDateCY, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_payment_month = $this->paymentDateRange($startDateCM, $endDateCM, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_payment_year = $this->paymentDateRange($startDateCY, $endDateCY, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_payment_cum = $this->paymentDateRange($brOpeningDate, $endDateCY, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);
                }

                $currentMonthReceiptQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$startDateCM, $endDateCM])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($currentMonthReceiptQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $currentMonthReceiptQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $currentMonthReceiptQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($currentMonthReceiptQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $currentMonthReceiptQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($currentMonthReceiptQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $currentMonthReceiptQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($currentMonthReceiptQuery) use ($ledgerCashBankIds) {
                        $currentMonthReceiptQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($currentMonthReceiptQuery) use ($ledgerCashBankIds) {
                                $currentMonthReceiptQuery
                                    ->whereIn('avd.debit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($currentMonthReceiptQuery) {
                        $currentMonthReceiptQuery->on('avd.credit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_debit_month'
                        )
                    )
                    ->groupBy('avd.credit_acc')
                    ->get();

                $currentMonthReceipt = array();
                foreach ($currentMonthReceiptQuery as $rowD) {
                    $currentMonthReceipt[$rowD->ledger_id] = (array) $rowD;
                }

                $currentYearReceiptQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$startDateCY, $endDateCY])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($currentYearReceiptQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $currentYearReceiptQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $currentYearReceiptQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($currentYearReceiptQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $currentYearReceiptQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($currentYearReceiptQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $currentYearReceiptQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($currentYearReceiptQuery) use ($ledgerCashBankIds) {
                        $currentYearReceiptQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($currentYearReceiptQuery) use ($ledgerCashBankIds) {
                                $currentYearReceiptQuery
                                    ->whereIn('avd.debit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($currentYearReceiptQuery) {
                        $currentYearReceiptQuery->on('avd.credit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_debit_dur'
                        )
                    )
                    ->groupBy('avd.credit_acc')
                    ->get();

                $currentYearReceipt = array();
                foreach ($currentYearReceiptQuery as $rowD) {
                    $currentYearReceipt[$rowD->ledger_id] = (array) $rowD;
                }

                $cumulativeReceiptQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$brOpeningDate, $endDateCY])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($cumulativeReceiptQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $cumulativeReceiptQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $cumulativeReceiptQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($cumulativeReceiptQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $cumulativeReceiptQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($cumulativeReceiptQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $cumulativeReceiptQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($cumulativeReceiptQuery) use ($ledgerCashBankIds) {
                        $cumulativeReceiptQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($cumulativeReceiptQuery) use ($ledgerCashBankIds) {
                                $cumulativeReceiptQuery
                                    ->whereIn('avd.debit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($cumulativeReceiptQuery) {
                        $cumulativeReceiptQuery->on('avd.credit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_debit_clo'
                        )
                    )
                    ->groupBy('avd.credit_acc')
                    ->get();

                $cumulativeReceipt = array();
                foreach ($cumulativeReceiptQuery as $rowD) {
                    $cumulativeReceipt[$rowD->ledger_id] = (array) $rowD;
                }

                $currentMonthPaymentQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$startDateCM, $endDateCM])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($currentMonthPaymentQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $currentMonthPaymentQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $currentMonthPaymentQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($currentMonthPaymentQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $currentMonthPaymentQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($currentMonthPaymentQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $currentMonthPaymentQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($currentMonthPaymentQuery) use ($ledgerCashBankIds) {
                        $currentMonthPaymentQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($currentMonthPaymentQuery) use ($ledgerCashBankIds) {
                                $currentMonthPaymentQuery
                                    ->whereIn('avd.credit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($currentMonthPaymentQuery) {
                        $currentMonthPaymentQuery->on('avd.debit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_credit_month'
                        )
                    )
                    ->groupBy('avd.debit_acc')
                    ->get();

                $currentMonthPayment = array();
                foreach ($currentMonthPaymentQuery as $rowC) {
                    $currentMonthPayment[$rowC->ledger_id] = (array) $rowC;
                }

                $currentYearPaymentQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$startDateCY, $endDateCY])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($currentYearPaymentQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $currentYearPaymentQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $currentYearPaymentQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($currentYearPaymentQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $currentYearPaymentQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($currentYearPaymentQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $currentYearPaymentQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($currentYearPaymentQuery) use ($ledgerCashBankIds) {
                        $currentYearPaymentQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($currentYearPaymentQuery) use ($ledgerCashBankIds) {
                                $currentYearPaymentQuery
                                    ->whereIn('avd.credit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($currentYearPaymentQuery) {
                        $currentYearPaymentQuery->on('avd.debit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_credit_dur'
                        )
                    )
                    ->groupBy('avd.debit_acc')
                    ->get();

                $currentYearPayment = array();
                foreach ($currentYearPaymentQuery as $rowC) {
                    $currentYearPayment[$rowC->ledger_id] = (array) $rowC;
                }

                $cumulativePaymentQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$startDateCY, $endDateCY])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($cumulativePaymentQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $cumulativePaymentQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $cumulativePaymentQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($cumulativePaymentQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $cumulativePaymentQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($cumulativePaymentQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $cumulativePaymentQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($cumulativePaymentQuery) use ($ledgerCashBankIds) {
                        $cumulativePaymentQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($cumulativePaymentQuery) use ($ledgerCashBankIds) {
                                $cumulativePaymentQuery
                                    ->whereIn('avd.credit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($cumulativePaymentQuery) {
                        $cumulativePaymentQuery->on('avd.debit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_credit_clo'
                        )
                    )
                    ->groupBy('avd.debit_acc')
                    ->get();

                $cumulativePayment = array();
                foreach ($cumulativePaymentQuery as $rowC) {
                    $cumulativePayment[$rowC->ledger_id] = (array) $rowC;
                }
                // dd($currentMonthPayment);

            }

            // // // Search By Date Range
            if ($searchBy == 3) {

                $startDateDR = (new DateTime($startDateDR))->format('Y-m-d');
                $endDateDR = (new DateTime($endDateDR))->format('Y-m-d');

                $startDateDM = (new DateTime($startDateDM))->format('Y-m-d');
                $endDateDM = $endDateDR;

                // Balace before start date to calculate Opening Balance
                $cash_ob_month = 0;

                $cash_ob_year = 0;

                $bank_ob_month = 0;

                $bank_ob_year = 0;

                // dd($endDateDROB);

                // Balance during for cash and bank seperately
                if ($voucher_type == 1 || $voucher_type == 2) {

                    $cash_ob_month = $this->openingBalanceDateRange($brOpeningDate, $startDateDM, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_ob_year = $this->openingBalanceDateRange($brOpeningDate, $startDateDR, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_ob_month = $this->openingBalanceDateRange($brOpeningDate, $startDateDM, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_ob_year = $this->openingBalanceDateRange($brOpeningDate, $startDateDR, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_receipt_month = $this->receiptDateRange($startDateDM, $endDateDM, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_receipt_year = $this->receiptDateRange($startDateDR, $endDateDR, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_receipt_cum = $this->receiptDateRange($brOpeningDate, $endDateDR, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_receipt_month = $this->receiptDateRange($startDateDM, $endDateDM, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_receipt_year = $this->receiptDateRange($startDateDR, $endDateDR, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_receipt_cum = $this->receiptDateRange($brOpeningDate, $endDateDR, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_payment_month = $this->paymentDateRange($startDateDM, $endDateDM, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_payment_year = $this->paymentDateRange($startDateDR, $endDateDR, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $cash_payment_cum = $this->paymentDateRange($brOpeningDate, $endDateDR, $ledgerCashIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_payment_month = $this->paymentDateRange($startDateDM, $endDateDM, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_payment_year = $this->paymentDateRange($startDateDR, $endDateDR, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);

                    $bank_payment_cum = $this->paymentDateRange($brOpeningDate, $endDateDR, $ledgerBankIds, $branchID,
                        $projectID, $projectTypeID);
                }

                $currentMonthReceiptQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$startDateDM, $endDateDM])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($currentMonthReceiptQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $currentMonthReceiptQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $currentMonthReceiptQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($currentMonthReceiptQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $currentMonthReceiptQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($currentMonthReceiptQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $currentMonthReceiptQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($currentMonthReceiptQuery) use ($ledgerCashBankIds) {
                        $currentMonthReceiptQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($currentMonthReceiptQuery) use ($ledgerCashBankIds) {
                                $currentMonthReceiptQuery
                                    ->whereIn('avd.debit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($currentMonthReceiptQuery) {
                        $currentMonthReceiptQuery->on('avd.credit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_debit_month'
                        )
                    )
                    ->groupBy('avd.credit_acc')
                    ->get();

                $currentMonthReceipt = array();
                foreach ($currentMonthReceiptQuery as $rowD) {
                    $currentMonthReceipt[$rowD->ledger_id] = (array) $rowD;
                }

                $currentYearReceiptQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$startDateDR, $endDateDR])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($currentYearReceiptQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $currentYearReceiptQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $currentYearReceiptQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($currentYearReceiptQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $currentYearReceiptQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($currentYearReceiptQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $currentYearReceiptQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($currentYearReceiptQuery) use ($ledgerCashBankIds) {
                        $currentYearReceiptQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($currentYearReceiptQuery) use ($ledgerCashBankIds) {
                                $currentYearReceiptQuery
                                    ->whereIn('avd.debit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($currentYearReceiptQuery) {
                        $currentYearReceiptQuery->on('avd.credit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_debit_dur'
                        )
                    )
                    ->groupBy('avd.credit_acc')
                    ->get();

                $currentYearReceipt = array();
                foreach ($currentYearReceiptQuery as $rowD) {
                    $currentYearReceipt[$rowD->ledger_id] = (array) $rowD;
                }

                $cumulativeReceiptQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$brOpeningDate, $endDateDR])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($cumulativeReceiptQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $cumulativeReceiptQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $cumulativeReceiptQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($cumulativeReceiptQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $cumulativeReceiptQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($cumulativeReceiptQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $cumulativeReceiptQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($cumulativeReceiptQuery) use ($ledgerCashBankIds) {
                        $cumulativeReceiptQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($cumulativeReceiptQuery) use ($ledgerCashBankIds) {
                                $cumulativeReceiptQuery
                                    ->whereIn('avd.debit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($cumulativeReceiptQuery) {
                        $cumulativeReceiptQuery->on('avd.credit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.credit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_debit_clo'
                        )
                    )
                    ->groupBy('avd.credit_acc')
                    ->get();

                $cumulativeReceipt = array();
                foreach ($cumulativeReceiptQuery as $rowD) {
                    $cumulativeReceipt[$rowD->ledger_id] = (array) $rowD;
                }

                $currentMonthPaymentQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$startDateDM, $endDateDM])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($currentMonthPaymentQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $currentMonthPaymentQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $currentMonthPaymentQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($currentMonthPaymentQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $currentMonthPaymentQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($currentMonthPaymentQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $currentMonthPaymentQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($currentMonthPaymentQuery) use ($ledgerCashBankIds) {
                        $currentMonthPaymentQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($currentMonthPaymentQuery) use ($ledgerCashBankIds) {
                                $currentMonthPaymentQuery
                                    ->whereIn('avd.credit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($currentMonthPaymentQuery) {
                        $currentMonthPaymentQuery->on('avd.debit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_credit_month'
                        )
                    )
                    ->groupBy('avd.debit_acc')
                    ->get();

                $currentMonthPayment = array();
                foreach ($currentMonthPaymentQuery as $rowC) {
                    $currentMonthPayment[$rowC->ledger_id] = (array) $rowC;
                }

                $currentYearPaymentQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$startDateDR, $endDateDR])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($currentYearPaymentQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $currentYearPaymentQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $currentYearPaymentQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($currentYearPaymentQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $currentYearPaymentQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($currentYearPaymentQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $currentYearPaymentQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($currentYearPaymentQuery) use ($ledgerCashBankIds) {
                        $currentYearPaymentQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($currentYearPaymentQuery) use ($ledgerCashBankIds) {
                                $currentYearPaymentQuery
                                    ->whereIn('avd.credit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($currentYearPaymentQuery) {
                        $currentYearPaymentQuery->on('avd.debit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_credit_dur'
                        )
                    )
                    ->groupBy('avd.debit_acc')
                    ->get();

                $currentYearPayment = array();
                foreach ($currentYearPaymentQuery as $rowC) {
                    $currentYearPayment[$rowC->ledger_id] = (array) $rowC;
                }

                $cumulativePaymentQuery = DB::table('acc_voucher as av')
                    ->where([['av.is_delete', 0], ['av.is_active', 1]])
                    ->whereBetween('av.voucher_date', [$startDateDR, $endDateDR])
                    ->whereIn('av.voucher_status', [1, 2])
                    ->where(function ($cumulativePaymentQuery) use ($branchID) {
                        if (!empty($branchID)) {
                            if ($branchID >= 0) {
                                $cumulativePaymentQuery->where('av.branch_id', $branchID); // Individual Branch
                            } else if ($branchID == -2) {
                                $cumulativePaymentQuery->where('av.branch_id', '!=', 1); // Branch without head office
                            }
                        }
                    })
                    ->where(function ($cumulativePaymentQuery) use ($projectID) {
                        if (!empty($projectID)) {
                            $cumulativePaymentQuery->where('av.project_id', $projectID);
                        }
                    })
                    ->where(function ($cumulativePaymentQuery) use ($projectTypeID) {
                        if (!empty($projectTypeID)) {
                            $cumulativePaymentQuery->where('av.project_type_id', $projectTypeID);
                        }
                    })
                    ->join('acc_voucher_details as avd', function ($cumulativePaymentQuery) use ($ledgerCashBankIds) {
                        $cumulativePaymentQuery->on('avd.voucher_id', 'av.id')
                            ->where(function ($cumulativePaymentQuery) use ($ledgerCashBankIds) {
                                $cumulativePaymentQuery
                                    ->whereIn('avd.credit_acc', $ledgerCashBankIds);
                            });
                    })
                    ->join('acc_account_ledger as acl', function ($cumulativePaymentQuery) {
                        $cumulativePaymentQuery->on('avd.debit_acc', 'acl.id')
                            ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                    })
                    ->select('avd.debit_acc as ledger_id',
                        DB::raw(
                            'IFNULL(SUM(avd.amount),0) as sum_credit_clo'
                        )
                    )
                    ->groupBy('avd.debit_acc')
                    ->get();

                $cumulativePayment = array();
                foreach ($cumulativePaymentQuery as $rowC) {
                    $cumulativePayment[$rowC->ledger_id] = (array) $rowC;
                }
                // dd($currentMonthPayment);

            }

            $ttl_debit_month = 0;
            $ttl_debit_dur = 0;
            $ttl_debit_clo = 0;

            $ttl_credit_month = 0;
            $ttl_credit_dur = 0;
            $ttl_credit_clo = 0;

            $DataSet = array();
            $DataSetLedger = array();
            $TempDataSet = array();

            // // This calculation for transection ledger only
            foreach ($ledgerHeads as $row) {

                $debit_month = 0;
                $debit_dur = 0;
                $debit_clo = 0;

                $credit_month = 0;
                $credit_dur = 0;
                $credit_clo = 0;

                if ($row->acc_type_id == 4 || $row->acc_type_id == 5) {
                    $debit_month = 0;
                    $debit_dur = 0;
                    $debit_clo = 0;

                    $credit_month = 0;
                    $credit_dur = 0;
                    $credit_clo = 0;
                } else {
                    if (isset($currentMonthReceipt[$row->id])) {
                        $debit_month = $currentMonthReceipt[$row->id]['sum_debit_month'];
                    }
                    if (isset($currentMonthPayment[$row->id])) {
                        $credit_month = $currentMonthPayment[$row->id]['sum_credit_month'];
                    }

                    if (isset($currentYearReceipt[$row->id])) {
                        $debit_dur = $currentYearReceipt[$row->id]['sum_debit_dur'];
                    }
                    if (isset($currentYearPayment[$row->id])) {
                        $credit_dur = $currentYearPayment[$row->id]['sum_credit_dur'];
                    }

                    if (isset($cumulativeReceipt[$row->id])) {
                        $debit_clo = $cumulativeReceipt[$row->id]['sum_debit_clo'];
                    }

                    if (isset($cumulativePayment[$row->id])) {
                        $credit_clo = $cumulativePayment[$row->id]['sum_credit_clo'];
                    }
                }

                // // Total Calculation
                $ttl_debit_month += $debit_month;
                $ttl_debit_dur += $debit_dur;
                $ttl_debit_clo += $debit_clo;

                $ttl_credit_month += $credit_month;
                $ttl_credit_dur += $credit_dur;
                $ttl_credit_clo += $credit_clo;

                $DataSetLedger[$row->id] = [
                    'debit_month' => $debit_month,
                    'credit_month' => $credit_month,
                    'debit_dur' => $debit_dur,
                    'credit_dur' => $credit_dur,
                    'debit_clo' => $debit_clo,
                    'credit_clo' => $credit_clo,
                ];
            }
            // dd($DataSetLedger);
            // // End Calculation for Transection Ledger

            // // Set View For
            $opening_balance_month = $cash_ob_month + $bank_ob_month;
            $opening_balance_year = $cash_ob_year + $bank_ob_year;

            $cash_clo_month = $cash_ob_month + ($cash_receipt_month - $cash_payment_month);
            $cash_clo_year = $cash_ob_year + ($cash_receipt_year - $cash_payment_year);
            $cash_clo_cum = ($cash_receipt_cum - $cash_payment_cum);

            $bank_clo_month = $bank_ob_month + ($bank_receipt_month - $bank_payment_month);
            $bank_clo_year = $bank_ob_year + ($bank_receipt_year - $bank_payment_year);
            $bank_clo_cum = ($bank_receipt_cum - $bank_payment_cum);

            $closing_balance_month = $cash_clo_month + $bank_clo_month;
            $closing_balance_year = $cash_clo_year + $bank_clo_year;
            $closing_balance_cum = $cash_clo_cum + $bank_clo_cum;

            $ttl_for_receipt_month = $opening_balance_month + $ttl_debit_month;
            $ttl_for_receipt_year = $opening_balance_year + $ttl_debit_dur;
            $ttl_for_receipt_cum = $ttl_debit_clo;

            $ttl_for_payment_month = $closing_balance_month + $ttl_credit_month;
            $ttl_for_payment_year = $closing_balance_year + $ttl_credit_dur;
            $ttl_for_payment_cum = $closing_balance_cum + $ttl_credit_clo;

            $opening_balance_month_txt = $opening_balance_month != 0 ? number_format($opening_balance_month, 2) : '-';
            $opening_balance_year_txt = $opening_balance_year != 0 ? number_format($opening_balance_year, 2) : '-';

            $closing_balance_month_txt = $closing_balance_month != 0 ? number_format($closing_balance_month, 2) : '-';
            $closing_balance_year_txt = $closing_balance_year != 0 ? number_format($closing_balance_year, 2) : '-';
            $closing_balance_cum_txt = $closing_balance_cum != 0 ? number_format($closing_balance_cum, 2) : '-';

            $cash_ob_month = $cash_ob_month != 0 ? number_format($cash_ob_month, 2) : '-';
            $cash_ob_year = $cash_ob_year != 0 ? number_format($cash_ob_year, 2) : '-';

            $ttl_for_receipt_month = $ttl_for_receipt_month != 0 ? number_format($ttl_for_receipt_month, 2) : '-';
            $ttl_for_receipt_year = $ttl_for_receipt_year != 0 ? number_format($ttl_for_receipt_year, 2) : '-';
            $ttl_for_receipt_cum = $ttl_for_receipt_cum != 0 ? number_format($ttl_for_receipt_cum, 2) : '-';

            $ttl_for_payment_month = $ttl_for_payment_month != 0 ? number_format($ttl_for_payment_month, 2) : '-';
            $ttl_for_payment_year = $ttl_for_payment_year != 0 ? number_format($ttl_for_payment_year, 2) : '-';
            $ttl_for_payment_cum = $ttl_for_payment_cum != 0 ? number_format($ttl_for_payment_cum, 2) : '-';

            $bank_ob_month = $bank_ob_month != 0 ? number_format($bank_ob_month, 2) : '-';
            $bank_ob_year = $bank_ob_year != 0 ? number_format($bank_ob_year, 2) : '-';

            $cash_clo_month = $cash_clo_month != 0 ? number_format($cash_clo_month, 2) : '-';
            $cash_clo_year = $cash_clo_year != 0 ? number_format($cash_clo_year, 2) : '-';
            $cash_clo_cum = $cash_clo_cum != 0 ? number_format($cash_clo_cum, 2) : '-';

            $bank_clo_month = $bank_clo_month != 0 ? number_format($bank_clo_month, 2) : '-';
            $bank_clo_year = $bank_clo_year != 0 ? number_format($bank_clo_year, 2) : '-';
            $bank_clo_cum = $bank_clo_cum != 0 ? number_format($bank_clo_cum, 2) : '-';

            $ttl_debit_month = $ttl_debit_month != 0 ? number_format($ttl_debit_month, 2) : '-';
            $ttl_debit_dur = $ttl_debit_dur != 0 ? number_format($ttl_debit_dur, 2) : '-';
            $ttl_debit_clo = $ttl_debit_clo != 0 ? number_format($ttl_debit_clo, 2) : '-';

            $ttl_credit_month = $ttl_credit_month != 0 ? number_format($ttl_credit_month, 2) : '-';
            $ttl_credit_dur = $ttl_credit_dur != 0 ? number_format($ttl_credit_dur, 2) : '-';
            $ttl_credit_clo = $ttl_credit_clo != 0 ? number_format($ttl_credit_clo, 2) : '-';

            $opening_balance_month_txt = "<strong>" . $opening_balance_month_txt . "</strong>";
            $opening_balance_year_txt = "<strong>" . $opening_balance_year_txt . "</strong>";

            $closing_balance_month_txt = "<strong>" . $closing_balance_month_txt . "</strong>";
            $closing_balance_year_txt = "<strong>" . $closing_balance_year_txt . "</strong>";
            $closing_balance_cum_txt = "<strong>" . $closing_balance_cum_txt . "</strong>";

            $ttl_for_receipt_month = "<strong>" . $ttl_for_receipt_month . "</strong>";
            $ttl_for_receipt_year = "<strong>" . $ttl_for_receipt_year . "</strong>";
            $ttl_for_receipt_cum = "<strong>" . $ttl_for_receipt_cum . "</strong>";

            $ttl_for_payment_month = "<strong>" . $ttl_for_payment_month . "</strong>";
            $ttl_for_payment_year = "<strong>" . $ttl_for_payment_year . "</strong>";
            $ttl_for_payment_cum = "<strong>" . $ttl_for_payment_cum . "</strong>";

            $ttl_debit_month = "<strong>" . $ttl_debit_month . "</strong>";
            $ttl_debit_dur = "<strong>" . $ttl_debit_dur . "</strong>";
            $ttl_debit_clo = "<strong>" . $ttl_debit_clo . "</strong>";

            $ttl_credit_month = "<strong>" . $ttl_credit_month . "</strong>";
            $ttl_credit_dur = "<strong>" . $ttl_credit_dur . "</strong>";
            $ttl_credit_clo = "<strong>" . $ttl_credit_clo . "</strong>";

            $receipt_title = "<strong>" . 'Receipt' . "</strong>";
            $ttl_receipt_title = "<strong>" . 'Total Receipt' . "</strong>";
            $ttl_title = "<strong>" . 'Total' . "</strong>";

            $payment_title = "<strong>" . 'Payment' . "</strong>";
            $ttl_payment_title = "<strong>" . 'Total Payment' . "</strong>";
            $ttl_title_for_payment = "<strong>" . 'Total' . "</strong>";

            $asset_title = "<p class='text-uppercase'>" . 'Asset' . "</p>";
            $ob_title = "<strong class='text-uppercase'>" . 'Opening Balance' . "</strong>";
            $cb_title = "<strong class='text-uppercase'>" . 'Closing Balance' . "</strong>";
            $cash_title = "<p class='text-uppercase'>" . 'Cash In Hand' . "</p>";
            $bank_title = "<p class='text-uppercase'>" . 'Cash At Bank' . "</p>";

            // // // Calculation & Make visible Data for all ledger head

            // ---------------------- Opening Balance -------------------------//
            $tempSet = [
                'particular_name' => $asset_title,
                'notes' => '',
                'balance_dur_txt' => '',
                'balance_month_txt' => '',
                'closing_balance_txt' => '',
            ];
            $DataSet[] = $tempSet;

            $tempSet = [
                'particular_name' => $ob_title,
                'notes' => '',
                'balance_month_txt' => $opening_balance_month_txt,
                'balance_dur_txt' => $opening_balance_year_txt,
                'closing_balance_txt' => '',
            ];
            $DataSet[] = $tempSet;

            $tempSet = [
                'particular_name' => $cash_title,
                'notes' => '',
                'balance_month_txt' => $cash_ob_month,
                'balance_dur_txt' => $cash_ob_year,
                'closing_balance_txt' => '',
            ];
            $DataSet[] = $tempSet;

            $tempSet = [
                'particular_name' => $bank_title,
                'notes' => '',
                'balance_month_txt' => $bank_ob_month,
                'balance_dur_txt' => $bank_ob_year,
                'closing_balance_txt' => '',
            ];
            $DataSet[] = $tempSet;
            // ----------------------- END ------------------------//

            // -------------------------- Receipt Calculation Start---------------------//
            $tempSet = [
                'particular_name' => $receipt_title,
                'notes' => '',
                'balance_month_txt' => '',
                'balance_dur_txt' => '',
                'closing_balance_txt' => '',
            ];
            $DataSet[] = $tempSet;

            foreach ($ledgerHeads as $row) {
                if ($row->acc_type_id == 4 || $row->acc_type_id == 5) {
                    continue;
                }
                $tempSet = array();

                $debit_month = 0;
                $debit_dur = 0;
                $debit_clo = 0;

                if ($row->is_group_head == 0) {

                    if (isset($DataSetLedger[$row->id])) {

                        $debit_month = $DataSetLedger[$row->id]['debit_month'];
                        $debit_dur = $DataSetLedger[$row->id]['debit_dur'];
                        $debit_clo = $DataSetLedger[$row->id]['debit_clo'];
                    }
                } else if ($row->is_group_head == 1) {
                    if (isset($this->PublicLedger[$row->id])) {
                        $ChildTransLegers = $this->PublicLedger[$row->id];

                        foreach ($ChildTransLegers as $CL_ID) {

                            $debit_month += $DataSetLedger[$CL_ID]['debit_month'];
                            $debit_dur += $DataSetLedger[$CL_ID]['debit_dur'];
                            $debit_clo += $DataSetLedger[$CL_ID]['debit_clo'];
                        }

                    }
                }

                /// //// // --------------------------------------- Level Check -------------------------------
                if (($depth_level != '')) {
                
                    if ($row->level != $depth_level) {
                        continue;
                    }

                }

                // // ////------------------- Condition Implement for Zero Balance ---------------------------
                if ($zero_balance == 2) {
                    if (($debit_month == 0) && ($debit_dur == 0) && ($debit_clo == 0)) {
                        continue;
                    }
                }

                // // Data set for view
                $particular_name = $row->name . " [" . $row->code . "]";

                $debit_month_txt = ($debit_month != 0) ? number_format($debit_month, 2) : '-';
                $debit_dur_txt = ($debit_dur != 0) ? number_format($debit_dur, 2) : '-';
                $debit_clo_txt = ($debit_clo != 0) ? number_format($debit_clo, 2) : '-';

                if ($row->is_group_head != 0) {
                    $debit_month_txt = "<strong>" . $debit_month_txt . "</strong>";
                    $debit_dur_txt = "<strong>" . $debit_dur_txt . "</strong>";
                    $debit_clo_txt = "<strong>" . $debit_clo_txt . "</strong>";

                    $particular_name = "<strong>" . $row->name . " [" . $row->code . "]</strong>";
                }

                // This is Regular tempset
                $tempSet = [
                    'particular_name' => $particular_name,
                    'notes' => '',
                    'balance_month_txt' => $debit_month_txt,
                    'balance_dur_txt' => $debit_dur_txt,
                    'closing_balance_txt' => $debit_clo_txt,
                ];
                $DataSet[] = $tempSet;
                // end
            }
            $tempSet = [
                'particular_name' => $ttl_receipt_title,
                'notes' => '',
                'balance_month_txt' => $ttl_debit_month,
                'balance_dur_txt' => $ttl_debit_dur,
                'closing_balance_txt' => $ttl_debit_clo,

            ];
            $DataSet[] = $tempSet;
            //End

            // For adding Total Row
            $tempSet = [
                'particular_name' => $ttl_title,
                'notes' => '',
                'balance_month_txt' => $ttl_for_receipt_month,
                'balance_dur_txt' => $ttl_for_receipt_year,
                'closing_balance_txt' => $ttl_for_receipt_cum,

            ];
            $DataSet[] = $tempSet;
            // ------------------------- Receipt Calculation END ---------------------- //

            // -------------------------- Payment Calculation Start---------------------//
            $tempSet = [
                'particular_name' => $payment_title,
                'notes' => '',
                'balance_dur_txt' => '',
                'balance_month_txt' => '',
                'closing_balance_txt' => '',
            ];
            $DataSet[] = $tempSet;

            foreach ($ledgerHeads as $row) {
                if ($row->acc_type_id == 4 || $row->acc_type_id == 5) {
                    continue;
                }
                $tempSet = array();

                $credit_month = 0;
                $credit_dur = 0;
                $credit_clo = 0;

                if ($row->is_group_head == 0) {

                    if (isset($DataSetLedger[$row->id])) {

                        $credit_month = $DataSetLedger[$row->id]['credit_month'];
                        $credit_dur = $DataSetLedger[$row->id]['credit_dur'];
                        $credit_clo = $DataSetLedger[$row->id]['credit_clo'];
                    }
                } else if ($row->is_group_head == 1) {
                    if (isset($this->PublicLedger[$row->id])) {
                        $ChildTransLegers = $this->PublicLedger[$row->id];

                        foreach ($ChildTransLegers as $CL_ID) {

                            $credit_month += $DataSetLedger[$CL_ID]['credit_month'];
                            $credit_dur += $DataSetLedger[$CL_ID]['credit_dur'];
                            $credit_clo += $DataSetLedger[$CL_ID]['credit_clo'];
                        }

                    }
                }

                /// //// // --------------------------------------- Level Check -------------------------------
                if (($depth_level != 'All')) {
                    if ($row->level != $depth_level) {
                        continue;
                    }
                }

                // // ////------------------- Condition Implement for Zero Balance ---------------------------
                if ($zero_balance == 2) {
                    if (($credit_month == 0) && ($credit_dur == 0) && ($credit_clo == 0)) {
                        continue;
                    }
                }

                // // Data set for view
                $particular_name = $row->name . " [" . $row->code . "]";

                $credit_month_txt = ($credit_month != 0) ? number_format($credit_month, 2) : '-';
                $credit_dur_txt = ($credit_dur != 0) ? number_format($credit_dur, 2) : '-';
                $credit_clo_txt = ($credit_clo != 0) ? number_format($credit_clo, 2) : '-';

                if ($row->is_group_head != 0) {
                    $credit_month_txt = "<strong>" . $credit_month_txt . "</strong>";
                    $credit_dur_txt = "<strong>" . $credit_dur_txt . "</strong>";
                    $credit_clo_txt = "<strong>" . $credit_clo_txt . "</strong>";

                    $particular_name = "<strong>" . $row->name . " [" . $row->code . "]</strong>";
                }

                // This is Regular tempset
                $tempSet = [
                    'particular_name' => $particular_name,
                    'notes' => '',
                    'balance_month_txt' => $credit_month_txt,
                    'balance_dur_txt' => $credit_dur_txt,
                    'closing_balance_txt' => $credit_clo_txt,
                ];
                $DataSet[] = $tempSet;
                // end
            }
            $tempSet = [
                'particular_name' => $ttl_payment_title,
                'notes' => '',
                'balance_month_txt' => $ttl_credit_month,
                'balance_dur_txt' => $ttl_credit_dur,
                'closing_balance_txt' => $ttl_credit_clo,

            ];
            $DataSet[] = $tempSet;
            //End

            // ---------------------- Closing Balance -------------------------//
            $tempSet = [
                'particular_name' => $asset_title,
                'notes' => '',
                'balance_dur_txt' => '',
                'balance_month_txt' => '',
                'closing_balance_txt' => '',
            ];
            $DataSet[] = $tempSet;

            $tempSet = [
                'particular_name' => $cb_title,
                'notes' => '',
                'balance_month_txt' => $closing_balance_month_txt,
                'balance_dur_txt' => $closing_balance_year_txt,
                'closing_balance_txt' => $closing_balance_cum_txt,
            ];
            $DataSet[] = $tempSet;

            $tempSet = [
                'particular_name' => $cash_title,
                'notes' => '',
                'balance_month_txt' => $cash_clo_month,
                'balance_dur_txt' => $cash_clo_year,
                'closing_balance_txt' => $cash_clo_cum,
            ];
            $DataSet[] = $tempSet;

            $tempSet = [
                'particular_name' => $bank_title,
                'notes' => '',
                'balance_month_txt' => $bank_clo_month,
                'balance_dur_txt' => $bank_clo_year,
                'closing_balance_txt' => $bank_clo_cum,
            ];
            $DataSet[] = $tempSet;
            // //----------------------- END ------------------------//

            //For adding Total Row
            $tempSet = [
                'particular_name' => $ttl_title,
                'notes' => '',
                'balance_month_txt' => $ttl_for_payment_month,
                'balance_dur_txt' => $ttl_for_payment_year,
                'closing_balance_txt' => $ttl_for_payment_cum,

            ];
            $DataSet[] = $tempSet;
            // ------------------------- Payment Calculation END ---------------------- //

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,

            );

            echo json_encode($json_data);
            exit;

        } else {
            return view('ACC.Report.receipt_payment');
        }
    }

}
