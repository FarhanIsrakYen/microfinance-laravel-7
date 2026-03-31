<?php

namespace App\Services;

use App\Model\Acc\Voucher;
use App\Model\Acc\VoucherDetails;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\RoleService as Role;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AccService
{
    public function __construct()
    {
        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();
    }
    public static $GlobalCount = 1;


    /**
     * in $req == ()
     * credit_arr credit ledger id arrays
     * debit_arr debit ledger id arrays
     * amount_arr amount array
     * narration_arr  for local narration array
     *
     * others all same as voucher and voucher details staructure
     * /// iff need module perameter would be added later as per need
     * 
     * *******************to generate a voucher dont passs voucher code .... it will generate by it self
     * *******************for update a voucher pass voucher code 
     * 
     * 
     */

    public static function insertVouchermfn(Request $req)
    {

        $ModelVoucher = 'App\\Model\\Acc\\Voucher';
        $ModelVoucherDetails = 'App\\Model\\Acc\\VoucherDetails';

        $RequestData = array();
        $RequestData['branch_id'] = $req->branch_id;
        $RequestData['module_id'] = $req->module_id;
        $RequestData['company_id'] = Common::getCompanyId();
        $RequestData['voucher_type_id'] = $req->voucher_type_id;
        $RequestData['voucher_status'] = $req->voucher_status;
        $RequestData['project_id'] = $req->project_id;
        $RequestData['project_type_id'] = $req->project_type_id;
        $RequestData['v_generate_type'] = $req->v_generate_type;
        $RequestData['voucher_date'] = $req->voucher_date;
        $RequestData['global_narration'] = $req->global_narration;
        $RequestData['voucher_code'] = $req->voucher_code;
        $RequestData['prep_by'] = Auth::id();
        // dd(empty($RequestData['voucher_code']));
        $RequestData['ft_from'] = (!empty($req->ft_from)) ?$req->ft_from : 0;
        $RequestData['ft_to'] = (!empty($req->ft_to)) ?$req->ft_to : 0;
        $RequestData['ft_target_acc'] = (!empty($req->ft_target_acc)) ?$req->ft_target_acc : null;

        $CreditACC = (isset($req->credit_arr) ?$req->credit_arr : array());
        $DebitACC = (isset($req->debit_arr) ?$req->debit_arr : array());
        $amount_arr = (isset($req->amount_arr) ?$req->amount_arr : array());
        $narration_arr = (isset($req->narration_arr) ?$req->narration_arr : array());

        if (count($amount_arr) <= 0) {
            $previousVoucher = $ModelVoucher::where([
                ['voucher_date', $RequestData['voucher_date']],
                ['v_generate_type', $RequestData['v_generate_type']],
                ['branch_id', $RequestData['branch_id']],
                ['voucher_type_id', $RequestData['voucher_type_id']],
                ['module_id', $RequestData['module_id']]
            ])->delete();

            $notification = array(
                'message' => 'Successfully inserted Voucher',
                'alert-type' => 'success',
            );
            return response()->json($notification);
        }

        // dd($CreditACC,  $DebitACC ,$amount_arr);

        if ((empty($RequestData['voucher_code']) || $RequestData['voucher_code'] == null) && $RequestData['v_generate_type'] == 1) {
            // dd('jujdfghfg');
            $previousVoucher = $ModelVoucher::where([
                ['voucher_date', $RequestData['voucher_date']],
                ['v_generate_type', $RequestData['v_generate_type']],
                ['branch_id', $RequestData['branch_id']],
                ['voucher_type_id', $RequestData['voucher_type_id']],
                ['module_id', $RequestData['module_id']]
            ])->first();
            // dd($previousVoucher);
        }
        // dd(1, $previousVoucher, 'sdfhfg');

        if (!empty($previousVoucher)) {
            $RequestData['voucher_code'] = $previousVoucher->voucher_code;
        }
        // dd($CreditACC,$DebitACC);

        if (empty($RequestData['voucher_code']) || $RequestData['voucher_code'] == null) {
            //   dd('if');

            $RequestData['voucher_code'] = self::generateBillVoucher($req->branch_id, $req->voucher_type_id, $req->project_id, $req->project_type_id);

            DB::beginTransaction();
            try {
                // dd('aaa');

                $isInsert = $ModelVoucher::create($RequestData);
                $lastInsertQuery = $ModelVoucher::where('voucher_code', $RequestData['voucher_code'])->first();

                // dd('voucher inserted');
                if ($isInsert) {

                    /* Child Table Insertion */
                    $RequestData2['branch_id'] = $RequestData['branch_id'];
                    $RequestData2['voucher_id'] = $lastInsertQuery->id;

                    $RequestData2['ft_from'] = (!empty($RequestData['ft_from'])) ?$RequestData['ft_from'] : 0;
                    $RequestData2['ft_to'] = (!empty($RequestData['ft_to'])) ?$RequestData['ft_to'] : 0;
                    $RequestData2['ft_target_acc'] = (!empty($RequestData['ft_target_acc'])) ?$RequestData['ft_target_acc'] : null;

                    $total_amount = 0;
                    foreach ($amount_arr as $key => $Row) {
                        if (!empty($Row)) {
                            $RequestData2['amount'] = $Row;
                            $total_amount += $Row;

                            $RequestData2['debit_acc'] = $DebitACC[$key];
                            $RequestData2['credit_acc'] = $CreditACC[$key];
                            $RequestData2['local_narration'] = isset($narration_arr[$key]) ?$narration_arr[$key] : null;

                            $isInsertDetails = $ModelVoucherDetails::create($RequestData2);
                        }
                    }

                    $lastInsertQuery->total_amount = $total_amount;
                    $lastInsertQuery->update();

                    // if (count($amount_arr) == 0) {
                    //     $ModelVoucher::where('voucher_code', $RequestData['voucher_code'])->delete();
                    // }

                    DB::commit();
                    $notification = array(
                        'message' => 'Successfully inserted Voucher',
                        'alert-type' => 'success',
                    );
                    return response()->json($notification);
                }
            } catch (Exception $e) {
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to inserted Voucher',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );

                return response()->json($notification);
            }
        } else {
            // dd('else');
            DB::beginTransaction();
            try {

                // dd($DebitACC, $CreditACC, $RequestData['voucher_code']);

                $isInsert = $ModelVoucher::updateOrCreate(['voucher_code' => $RequestData['voucher_code']], $RequestData);
                $lastInsertQuery = $ModelVoucher::where('voucher_code', $RequestData['voucher_code'])->first();

                // dd($isInsert);
                if ($isInsert) {

                    /* Child Table Insertion */
                    $RequestData2['branch_id'] = $RequestData['branch_id'];
                    $RequestData2['voucher_id'] = $lastInsertQuery->id;
                    $RequestData2['ft_from'] = (!empty($RequestData['ft_from'])) ?$RequestData['ft_from'] : 0;
                    $RequestData2['ft_to'] = (!empty($RequestData['ft_to'])) ?$RequestData['ft_to'] : 0;
                    $RequestData2['ft_target_acc'] = (!empty($RequestData['ft_target_acc'])) ?$RequestData['ft_target_acc'] : null;

                    $deleteDetailsObjs = $ModelVoucherDetails::where('voucher_id', $lastInsertQuery->id)->get();

                    $updateIDArray = array();

                    $total_amount = 0;
                    foreach ($amount_arr as $key => $Row) {
                        if (!empty($Row)) {
                            $RequestData2['amount'] = $Row;
                            $total_amount += $Row;

                            $RequestData2['debit_acc'] = $DebitACC[$key];
                            $RequestData2['credit_acc'] = $CreditACC[$key];
                            $RequestData2['local_narration'] = isset($narration_arr[$key]) ?$narration_arr[$key] : null;

                            $updateid = $deleteDetailsObjs->where('debit_acc', $DebitACC[$key])
                                ->where('credit_acc', $CreditACC[$key])
                                ->whereNotIn('id', $updateIDArray)
                                ->first();

                            // dd($updateid->id);

                            if (!empty($updateid)) {
                                array_push($updateIDArray, $updateid->id);
                                $isInsertDetails = $ModelVoucherDetails::updateOrCreate(['id' => $updateid->id], $RequestData2);
                            } else {
                                $isInsertDetails = $ModelVoucherDetails::create($RequestData2);
                            }
                        }
                    }
                    $lastInsertQuery->total_amount = $total_amount;
                    $lastInsertQuery->update();

                    $tobeDeleteIDs = $deleteDetailsObjs->whereNotIn('id', $updateIDArray);
                    $ModelVoucherDetails::whereIn('id', $tobeDeleteIDs->pluck('id')->toArray())->delete();

                    // if (count($amount_arr) == 0) {
                    //     $ModelVoucher::where('voucher_code', $RequestData['voucher_code'])->delete();
                    // }

                    DB::commit();

                    $notification = array(
                        'message' => 'Successfully inserted Voucher',
                        'alert-type' => 'success',
                    );
                    return response()->json($notification);
                }
            } catch (Exception $e) {
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to inserted Voucher',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );

                return response()->json($notification);
            }
        }

        // dd($RequestData,'ll');
        // dd($DebitACC);

        # code...
    }



    //  --------------------------------------------------------- ACC Bill generate

    public static function generateBillAccOB($branchID = null)
    {
        $BranchT = 'App\\Model\\GNL\\Branch';
        $ModelT = "App\\Model\\Acc\\OpeningBalanceMaster";

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        // $ldate = date('Ym');

        $PreBillNo = "AOB" . $BranchCode;
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

    public static function generateBillVoucher($BranchID = null, $voucherType = null, $projectID = null, $project_typeID = null)
    {
        $BranchM = 'App\\Model\\GNL\\Branch';
        $ProjectM = 'App\\Model\\GNL\\Project';
        $ProjectTypeM = 'App\\Model\\GNL\\ProjectType';
        $VoucherM = 'App\\Model\\Acc\\Voucher';
        $VoucherTypeM = 'App\\Model\\Acc\\VoucherType';

        $BranchCodeQuery = $BranchM::where([['is_delete', 0], ['is_approve', 1], ['id', $BranchID]])
            ->select('branch_code')
            ->first();

        // $ProjectCodeQuery = $ProjectM::where([['is_delete', 0], ['is_active', 1], ['id', $projectID]])
        //     ->select('project_code')
        //     ->first();
        $ProjectTypeCodeQuery = $ProjectTypeM::where([['is_delete', 0], ['is_active', 1], ['id', $project_typeID]])
            ->select('project_type_code')
            ->first();
        $VoucherTypeCodeQuery = $VoucherTypeM::where([['is_delete', 0], ['is_active', 1], ['id', $voucherType]])
            ->select('short_name')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }
        // dd($BranchCode);
        // $ProjectTypeCodeQuery
        $ProjectTypeCode = sprintf("%04d", $ProjectTypeCodeQuery->project_type_code);

        $PreBillNo = $VoucherTypeCodeQuery->short_name . $BranchCode . $ProjectTypeCode;

        $record = $VoucherM::where('branch_id', $BranchID)
            ->select(['id', 'voucher_code'])
            ->where('voucher_code', 'LIKE', "{$PreBillNo}%")
            ->orderBy('voucher_code', 'DESC')
            ->first();

        // dd($record);

        if ($record) {
            $OldBillNoA = explode($PreBillNo, $record->voucher_code);
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
        }

        return $BillNo;
        //return 'v';
    }

    public static function generateLedgerSysCode($parent_id = null, $accType = null, $gHead = null, $company_id = null)
    {
        $ModelAccType = 'App\\Model\\Acc\\AccountType';
        $ModelLedger = 'App\\Model\\Acc\\Ledger';

        if ($company_id == null) {
            $company_id = Common::getCompanyId();
        }

        $genCode = "";

        if ($parent_id == 0) {
            $AccTypeData = $ModelAccType::where([['is_delete', 0], ['is_active', 1], ['id', $accType]])
                ->select('code')
                ->first();

            if ($AccTypeData) {
                $genCode = $AccTypeData->code;
            } else {
                $genCode = 0;
            }
        } else {
            $parentLedger = $ModelLedger::where([['is_delete', 0], ['is_active', 1], ['id', $parent_id], ['company_id', $company_id]])
                ->select('sys_code')
                ->first();

            $PreCode = "";

            if ($parentLedger) {
                $PreCode = $parentLedger->sys_code;

                $LedgerCount = $ModelLedger::where([['is_delete', 0], ['is_active', 1], ['company_id', $company_id]])
                    ->where('parent_id', $parent_id)
                    ->where('sys_code', 'LIKE', "{$PreCode}%")
                    ->count();

                if ($gHead == 0) {
                    $genCode = $PreCode . sprintf("%03d", (($LedgerCount == 0) ?1 : $LedgerCount + 1));
                } else {
                    $genCode = $PreCode . sprintf("%02d", (($LedgerCount == 0) ?1 : $LedgerCount + 1));
                }
            } else {
                $genCode = 0;
            }
        }

        return $genCode;
    }

    /* --------------------------------------------------------------------- generate bill End */

    public static function LedgerHTML($GlobalRole = null, $AccType = null, $branchID = null, $projectID = null)
    {
        $Data_query = DB::table('acc_account_ledger')
            ->select([
                'acc_account_ledger.id', 'acc_account_ledger.parent_id', 'acc_account_ledger.name', 'acc_account_ledger.code',
                'acc_type_id', 'order_by', 'is_group_head', 'acc_account_type.name as acc_name'
            ])
            ->where(function ($Data_query) use ($branchID) {
                if (!empty($branchID)) {
                    $Data_query->where('branch_arr', 'LIKE', "%,{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "%,{$branchID}")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID}");
                }
            })
            ->where(function ($Data_query) use ($projectID) {
                if (!empty($projectID)) {
                    $Data_query->where('project_arr', 'LIKE', "%,{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "%,{$projectID}")
                        ->orWhere('project_arr', 'LIKE', "{$projectID}");
                }
            })

            ->where(function ($Data_query) use ($AccType) {
                if (!empty($AccType)) {
                    $Data_query->whereIn('acc_type_id', explode(',', $AccType));
                }
            })
            ->where(['acc_account_ledger.is_active' => 1, 'acc_account_ledger.is_delete' => 0])
            ->leftJoin('acc_account_type', 'acc_account_ledger.acc_type_id', '=', 'acc_account_type.id')
            // ->orderBy('parent_id', 'ASC')
            // ->orderBy('order_by', 'ASC')
            ->orderBy('sys_code', 'ASC')
            ->get();

        if (count($Data_query) > 0) {
            $Data_query_group = $Data_query->groupBy('parent_id');
            $DataSet = array();

            $spaceCount = 0;
            $html = '';

            if (isset($Data_query_group[0])) {

                $QueryData = $Data_query_group[0];
            } else {
                $ID = $Data_query->toarray()[0]->parent_id;
                $QueryData = $Data_query_group[$ID];
            }

            foreach ($QueryData as $RootData) {

                // dd($RootData);
                $html .= '<tr>';
                // .$RootData->id.'-'
                $html .= "<td> " . self::$GlobalCount . " </td>";
                self::$GlobalCount++;
                $html .= '<td>';

                if ($RootData->is_group_head == 1) {
                    $html .= '<i class="fa fa-folder-open" aria-hidden="true"></i>';
                    $html .= '<span>&nbsp&nbsp' . $RootData->name . '</span>';
                    $html .= "</td>";

                    // /dd($RootData);
                    $html .= '<td class="text-center">' . $RootData->code . '</td>';
                    $html .= '<td class="text-center">' . $RootData->acc_name . '</td>';
                    // need to add two row
                    $action = '<a href="ledger/add/';
                    $action .= $RootData->id;
                    $action .= '"><i class="icon wb-plus mr-2 blue-grey-600"></i>';
                    $action .= Role::roleWisePermission($GlobalRole, $RootData->id);
                    $html .= '<td class="text-center">' . $action . '</td>';
                    $html .= "</tr>";
                } else {
                    $html .= '<i class="fa fa-fighter-jet" aria-hidden="true"></i>';
                    $html .= '<span>&nbsp&nbsp' . $RootData->name . '</span>';
                    $html .= "</td>";
                    $html .= '<td class="text-center">' . $RootData->code . '</td>';
                    $html .= '<td class="text-center">' . $RootData->acc_name . '</td>';
                    // need to add two row
                    $action = Role::roleWisePermission($GlobalRole, $RootData->id);
                    $html .= '<td class="text-center">' . $action . '</td>';
                    $html .= "</tr>";
                }

                $html .= self::SubLedgerHTML($RootData->id, $Data_query_group, $spaceCount, $GlobalRole);
            }
            return $html;
        }
    }
    public static function SubLedgerHTML($ParentID = null, $ParentArr = [], $count = null, $GlobalRole = null)
    {
        $subHtml = "";
        $space = "";

        $count++;

        if (isset($ParentArr[$ParentID])) {
            $SubArrData = $ParentArr[$ParentID];

            for ($i = 0; $i < $count; $i++) {
                $space .= "&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp";
            }

            foreach ($SubArrData as $Subdata) {

                // dd($Subdata);

                $subHtml .= '<tr>';
                $subHtml .= "<td> " . self::$GlobalCount . "</td>";
                self::$GlobalCount++;

                $subHtml .= '<td>';

                $subHtml .= '<span>' . $space;

                if ($Subdata->is_group_head == 1) {
                    $subHtml .= '<i class="fa fa-folder-open" aria-hidden="true"></i>';
                    $subHtml .= '&nbsp&nbsp' . $Subdata->name . '</span>';

                    $subHtml .= "</td>";

                    $subHtml .= '<td class="text-center">' . $Subdata->code . '</td>';
                    $subHtml .= '<td class="text-center">' . $Subdata->acc_name . '</td>';
                    // need to add two row

                    $action = '<a href="ledger/add/';
                    $action .= $Subdata->id;
                    $action .= '"><i class="icon wb-plus mr-2 blue-grey-600"></i>';
                    $action .= Role::roleWisePermission($GlobalRole, $Subdata->id);
                    $subHtml .= '<td class="text-center">' . $action . '</td>';
                    $subHtml .= "</tr>";
                } else {
                    $subHtml .= '<i class="fa fa-fighter-jet" aria-hidden="true"></i>';
                    $subHtml .= '&nbsp&nbsp' . $Subdata->name . '</span>';

                    $subHtml .= "</td>";

                    $subHtml .= '<td class="text-center">' . $Subdata->code . '</td>';
                    $subHtml .= '<td class="text-center">' . $Subdata->acc_name . '</td>';
                    // need to add two row
                    $action = Role::roleWisePermission($GlobalRole, $Subdata->id);

                    $subHtml .= '<td class="text-center">' . $action . '</td>';
                    $subHtml .= "</tr>";
                }

                $subHtml .= self::SubLedgerHTML($Subdata->id, $ParentArr, $count, $GlobalRole);
            }
        } else {
            $count--;
        }

        // print_r($count);

        return $subHtml;
    }

    /**
     * This function returns an object having ledger account information.
     * calling -- dd(ACC::getLedgerAccount(1, 3, null, 3));
     * @return object
     */
    public static function getLedgerAccount(
        $branchID = 1,
        $projectID = null,
        $projectTypeID = null,
        $accType = null,
        $groupHead = null,
        $level = null
    ) {
        /**
         * $groupHead = 0 = Transectional Ledger
         * when $groupHead = null or $groupHead = '' or $groupHead pass kora na hole then fetch data grouphead = 0,
         * when $groupHead = 'all' then all data fetch
         * when $groupHead = :value then fetch data value wise
         */

        $ledgerHeads = DB::table('acc_account_ledger as acl')
            ->where([['is_delete', 0], ['is_active', 1]])
            ->where(function ($ledgerHeads) use ($branchID) {
                if (!empty($branchID)) {
                    $ledgerHeads->where('branch_arr', 'LIKE', "%,{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "%,{$branchID}")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID}");
                }
            })
            ->where(function ($ledgerHeads) use ($projectID) {
                if (!empty($projectID)) {
                    $ledgerHeads->where('project_arr', 'LIKE', "%,{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "%,{$projectID}")
                        ->orWhere('project_arr', 'LIKE', "{$projectID}");
                }
            })
            ->where(function ($ledgerHeads) use ($accType) {
                if (!empty($accType)) {
                    $ledgerHeads->where('acc_type_id', $accType);
                }
            })
            ->where(function ($ledgerHeads) use ($level) {
                if (!empty($level)) {
                    $ledgerHeads->where('level', $level);
                }
            })
            ->where(function ($ledgerHeads) use ($groupHead) {
                if (empty($groupHead)) {
                    $ledgerHeads->where('is_group_head', 0);
                } elseif ($groupHead > 0) {
                    $ledgerHeads->where('is_group_head', $groupHead);
                }
            })
            ->select('id', 'name', 'code', 'is_group_head', 'level', 'acc_type_id')
            ->orderBy('code', 'ASC')
            ->orderBy('order_by', 'ASC')
            ->get();

        return $ledgerHeads;
    }

    public static function getCashLedger($branchID = 1, $projectID = null, $groupHead = null)
    {
        /**
         * $groupHead = 0 = Transectional Ledger
         * when $groupHead = null or $groupHead = '' or $groupHead pass kora na hole then fetch data grouphead = 0,
         * when $groupHead = 'all' then all data fetch
         * when $groupHead = :value then fetch data value wise
         */

        $ledgerHeads = DB::table('acc_account_ledger as acl')
            ->where([['is_delete', 0], ['is_active', 1], ['acc_type_id', 4]])
            ->where(function ($ledgerHeads) use ($branchID) {
                if (!empty($branchID)) {
                    $ledgerHeads->where('branch_arr', 'LIKE', "%,{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "%,{$branchID}")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID}");
                }
            })
            ->where(function ($ledgerHeads) use ($projectID) {
                if (!empty($projectID)) {
                    $ledgerHeads->where('project_arr', 'LIKE', "%,{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "%,{$projectID}")
                        ->orWhere('project_arr', 'LIKE', "{$projectID}");
                }
            })
            ->where(function ($ledgerHeads) use ($groupHead) {
                if (empty($groupHead)) {
                    $ledgerHeads->where('is_group_head', 0);
                } elseif ($groupHead > 0) {
                    $ledgerHeads->where('is_group_head', $groupHead);
                }
            })
            ->select('id', 'name', 'code', 'is_group_head', 'level', 'acc_type_id')
            ->orderBy('code', 'ASC')
            ->orderBy('order_by', 'ASC')
            ->get();

        return $ledgerHeads;
    }

    public static function getBankLedger($branchID = 1, $projectID = null, $groupHead = null)
    {
        /**
         * $groupHead = 0 = Transectional Ledger
         * when $groupHead = null or $groupHead = '' or $groupHead pass kora na hole then fetch data grouphead = 0,
         * when $groupHead = 'all' then all data fetch
         * when $groupHead = :value then fetch data value wise
         */

        $ledgerHeads = DB::table('acc_account_ledger as acl')
            ->where([['is_delete', 0], ['is_active', 1], ['acc_type_id', 5]])
            ->where(function ($ledgerHeads) use ($branchID) {
                if (!empty($branchID)) {
                    $ledgerHeads->where('branch_arr', 'LIKE', "%,{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "%,{$branchID}")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID}");
                }
            })
            ->where(function ($ledgerHeads) use ($projectID) {
                if (!empty($projectID)) {
                    $ledgerHeads->where('project_arr', 'LIKE', "%,{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "%,{$projectID}")
                        ->orWhere('project_arr', 'LIKE', "{$projectID}");
                }
            })
            ->where(function ($ledgerHeads) use ($groupHead) {
                if (empty($groupHead)) {
                    $ledgerHeads->where('is_group_head', 0);
                } elseif ($groupHead > 0) {
                    $ledgerHeads->where('is_group_head', $groupHead);
                }
            })
            ->select('id', 'name', 'code', 'is_group_head', 'level', 'acc_type_id')
            ->orderBy('code', 'ASC')
            ->orderBy('order_by', 'ASC')
            ->get();

        return $ledgerHeads;
    }

    public static function getIncomeLedger($branchID = 1, $projectID = null, $groupHead = null)
    {
        /**
         * $groupHead = 0 = Transectional Ledger
         * when $groupHead = null or $groupHead = '' or $groupHead pass kora na hole then fetch data grouphead = 0,
         * when $groupHead = 'all' then all data fetch
         * when $groupHead = :value then fetch data value wise
         */

        $ledgerHeads = DB::table('acc_account_ledger as acl')
            ->where([['is_delete', 0], ['is_active', 1], ['acc_type_id', 12]])
            ->where(function ($ledgerHeads) use ($branchID) {
                if (!empty($branchID)) {
                    $ledgerHeads->where('branch_arr', 'LIKE', "%,{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "%,{$branchID}")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID}");
                }
            })
            ->where(function ($ledgerHeads) use ($projectID) {
                if (!empty($projectID)) {
                    $ledgerHeads->where('project_arr', 'LIKE', "%,{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "%,{$projectID}")
                        ->orWhere('project_arr', 'LIKE', "{$projectID}");
                }
            })
            ->where(function ($ledgerHeads) use ($groupHead) {
                if (empty($groupHead)) {
                    $ledgerHeads->where('is_group_head', 0);
                } elseif ($groupHead > 0) {
                    $ledgerHeads->where('is_group_head', $groupHead);
                }
            })
            ->select('id', 'name', 'code', 'is_group_head', 'level', 'acc_type_id')
            ->orderBy('code', 'ASC')
            ->orderBy('order_by', 'ASC')
            ->get();

        return $ledgerHeads;
    }

    public static function getExpenseLedger($branchID = 1, $projectID = null, $groupHead = null)
    {
        /**
         * $groupHead = 0 = Transectional Ledger
         * when $groupHead = null or $groupHead = '' or $groupHead pass kora na hole then fetch data grouphead = 0,
         * when $groupHead = 'all' then all data fetch
         * when $groupHead = :value then fetch data value wise
         */

        $ledgerHeads = DB::table('acc_account_ledger as acl')
            ->where([['is_delete', 0], ['is_active', 1], ['acc_type_id', 13]])
            ->where(function ($ledgerHeads) use ($branchID) {
                if (!empty($branchID)) {
                    $ledgerHeads->where('branch_arr', 'LIKE', "%,{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "%,{$branchID}")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID}");
                }
            })
            ->where(function ($ledgerHeads) use ($projectID) {
                if (!empty($projectID)) {
                    $ledgerHeads->where('project_arr', 'LIKE', "%,{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "%,{$projectID}")
                        ->orWhere('project_arr', 'LIKE', "{$projectID}");
                }
            })
            ->where(function ($ledgerHeads) use ($groupHead) {
                if (empty($groupHead)) {
                    $ledgerHeads->where('is_group_head', 0);
                } elseif ($groupHead > 0) {
                    $ledgerHeads->where('is_group_head', $groupHead);
                }
            })
            ->select('id', 'name', 'code', 'is_group_head', 'level', 'acc_type_id')
            ->orderBy('code', 'ASC')
            ->orderBy('order_by', 'ASC')
            ->get();

        return $ledgerHeads;
    }

    /**
     * Balance Calculation is for all calculation
     */
    public static function balanceCalculation($OB, $ledgerArray = [], $startDate, $endDate = null, $branchID = null, $voucherTypeID = null, $companyID = null, $projectID = null, $projectTypeID = null)
    {
        // dd($OB, $ledgerArray, $startDate, $branchID);
        /**
         * $OB = true or false
         */
        $companyID = (empty($companyID)) ?Common::getCompanyId() : $companyID;
        $branchID = (empty($branchID)) ?Common::getBranchId() : $branchID;

        $startDate = new DateTime($startDate);
        $startDate = $startDate->format('Y-m-d');

        $endDate = new DateTime($endDate);
        $endDate = $endDate->format('Y-m-d');

        $resultData = array();

        /** -------------------- Opening Balance Calculation ------------------------- */

        // // // Data Fetch from OB Tables for date range
        $obDateRange = DB::table('acc_ob_m as obm')
            ->where([['obm.is_delete', 0], ['obm.is_active', 1], ['obm.is_year_end', 0]])
            ->where(function ($obDateRange) use ($startDate, $companyID, $branchID, $projectID, $projectTypeID) {

                if (!empty($startDate)) {
                    $obDateRange->where('obm.opening_date', '<=', $startDate);
                }

                // if (!empty($companyID)) {
                //     $obDateRange->where('obm.company_id', $companyID);
                // }

                if (!empty($branchID)) {

                    if ($branchID >= 0) {
                        $obDateRange->where('obm.branch_id', $branchID); // Individual Branch
                    } else if ($branchID == -2) {
                        $obDateRange->where('obm.branch_id', '!=', 1); // Branch without head office
                    }
                }

                if (!empty($projectID)) {
                    $obDateRange->where('obm.project_id', $projectID);
                }

                if (!empty($projectTypeID)) {
                    $obDateRange->where('obm.project_type_id', $projectTypeID);
                }
            })
            ->join('acc_ob_d as obd', function ($obDateRange) use ($ledgerArray) {
                $obDateRange->on('obd.ob_no', 'obm.ob_no')
                    ->whereIn('obd.ledger_id', $ledgerArray);
            })
            ->select(DB::raw('IFNULL(SUM(obd.debit_amount),0) as debit_amount,
                    IFNULL(SUM(obd.credit_amount),0) as credit_amount'))
            ->orderBy('obm.id', 'ASC')
            ->first();

        if ($obDateRange) {
            $resultData['ob_ttl_debit_amt'] = $obDateRange->debit_amount;
            $resultData['ob_ttl_credit_amt'] = $obDateRange->credit_amount;
        }
        // // // End OB Tables for date range

        // // // Data Fetch from Voucher Tables for date range & before start date
        $voucherBegOB = DB::table('acc_voucher as av')
            ->where([['av.is_delete', 0], ['av.is_active', 1]])
            ->whereIn('av.voucher_status', [1, 2])
            ->where(function ($voucherBegOB) use ($OB, $companyID, $startDate, $branchID, $projectID, $projectTypeID, $voucherTypeID) {
                // if (!empty($companyID)) {
                //     $voucherBegOB->where('av.company_id', $companyID);
                // }

                if (!empty($startDate)) {
                    if ($OB) {
                        $voucherBegOB->where('av.voucher_date', '<', $startDate);
                    } else {
                        $voucherBegOB->where('av.voucher_date', '<=', $startDate);
                    }
                }

                if (!empty($branchID)) {
                    if ($branchID > 0) {
                        $voucherBegOB->where('av.branch_id', $branchID); // Individual Branch
                    } else if ($branchID == -2) {
                        $voucherBegOB->where('av.branch_id', '!=', 1); // Branch without head office
                    }
                }

                if (!empty($projectID)) {
                    $voucherBegOB->where('av.project_id', $projectID);
                }

                if (!empty($projectTypeID)) {
                    $voucherBegOB->where('av.project_type_id', $projectTypeID);
                }

                if (!empty($voucherTypeID)) {
                    $voucherBegOB->where('av.voucher_type_id', $voucherTypeID);
                }
            })
            ->join('acc_voucher_details as avd', function ($voucherBegOB) use ($ledgerArray) {
                $voucherBegOB->on('avd.voucher_id', 'av.id')
                    ->where(function ($voucherBegOB) use ($ledgerArray) {
                        $voucherBegOB->whereIn('avd.debit_acc', $ledgerArray)
                            ->orWhereIn('avd.credit_acc', $ledgerArray);
                    });
            })
            ->select(
                DB::raw(
                    'IFNULL(SUM(CASE WHEN avd.debit_acc IN (' . implode(',', $ledgerArray) . ') THEN avd.amount END), 0) as debit_amount,
                    IFNULL(SUM(CASE WHEN avd.credit_acc IN (' . implode(',', $ledgerArray) . ') THEN avd.amount END), 0) as credit_amount'
                )
            )
            ->orderBy('av.voucher_date', 'ASC')
            ->first();

        // dd(1,$voucherBegOB);

        if ($voucherBegOB) {
            $resultData['ob_ttl_debit_amt'] += $voucherBegOB->debit_amount;
            $resultData['ob_ttl_credit_amt'] += $voucherBegOB->credit_amount;
            // $ob_ttl_balance += $voucherBegOB->debit_amount  - $voucherBegOB->credit_amount;
        }
        /** -------------------- END Opening Balance Calculation ------------------------- */

        return $resultData;
    }

    public static function cash_bankBookReport($bankReport, $ledgerArray = [], $startDate, $endDate, $branchID = null, $voucherTypeID = null, $companyID = null, $projectID = null, $projectTypeID = null)
    {
        if ($bankReport && count($ledgerArray) > 1) {
            $bankAll = true;
        } else {
            $bankAll = false;
        }

        $companyID = (empty($companyID)) ?Common::getCompanyId() : $companyID;
        $branchID = (empty($branchID)) ?Common::getBranchId() : $branchID;

        $startDate = new DateTime($startDate);
        $startDate = $startDate->format('Y-m-d');

        $endDate = new DateTime($endDate);
        $endDate = $endDate->format('Y-m-d');

        $resultData = array();

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

        /** -------------------- Opening Balance Calculation ------------------------- */

        $OBData = self::balanceCalculation(true, $ledgerArray, $startDate, null, $branchID, $voucherTypeID, $companyID, $projectID, $projectTypeID);

        if ($OBData) {
            $ob_ttl_debit_amt = $OBData['ob_ttl_debit_amt'];
            $ob_ttl_credit_amt = $OBData['ob_ttl_credit_amt'];
        }

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
        /** -------------------- END Opening Balance Calculation ------------------------- */

        if ($bankAll == false) {
            $ledgerReport = DB::table('acc_voucher as av')
                ->where([['av.is_delete', 0], ['av.is_active', 1]])
                ->whereIn('av.voucher_status', [1, 2])
                ->where(function ($ledgerReport) use ($companyID, $startDate, $endDate, $branchID, $projectID, $projectTypeID, $voucherTypeID) {
                    // if (!empty($companyID)) {
                    //     $ledgerReport->where('av.company_id', $companyID);
                    // }

                    if (!empty($startDate) && !empty($endDate)) {
                        $ledgerReport->whereBetween('av.voucher_date', [$startDate, $endDate]);
                    }

                    if (!empty($branchID)) {
                        if ($branchID >= 0) {
                            $ledgerReport->where('av.branch_id', $branchID); // Individual Branch
                        } else if ($branchID == -2) {
                            $ledgerReport->where('av.branch_id', '!=', 1); // Branch without head office
                        }
                    }

                    if (!empty($projectID)) {
                        $ledgerReport->where('av.project_id', $projectID);
                    }

                    if (!empty($projectTypeID)) {
                        $ledgerReport->where('av.project_type_id', $projectTypeID);
                    }

                    if (!empty($voucherTypeID)) {
                        $ledgerReport->where('av.voucher_type_id', $voucherTypeID);
                    }
                })
                ->join('acc_voucher_details as avd', function ($ledgerReport) use ($ledgerArray) {
                    $ledgerReport->on('avd.voucher_id', 'av.id')
                        ->where(function ($ledgerReport) use ($ledgerArray) {
                            $ledgerReport->whereIn('avd.debit_acc', $ledgerArray)
                                ->orWhereIn('avd.credit_acc', $ledgerArray);
                        });
                })
                ->join('acc_account_ledger as acl', function ($ledgerReport) use ($ledgerArray) {
                    $ledgerReport->on(DB::raw('CASE
                                        WHEN avd.debit_acc IN (' . implode(',', $ledgerArray) . ') THEN avd.credit_acc
                                        WHEN avd.credit_acc IN (' . implode(',', $ledgerArray) . ') THEN avd.debit_acc
                                        END'), 'acl.id')
                        ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                })
                ->select(
                    'acl.name',
                    'avd.local_narration',
                    'av.voucher_date',
                    'av.voucher_code',
                    DB::raw(
                        'IFNULL((CASE WHEN avd.debit_acc IN (' . implode(',', $ledgerArray) . ') THEN avd.amount END), 0) as debit_amount,
                        IFNULL((CASE WHEN avd.credit_acc IN (' . implode(',', $ledgerArray) . ') THEN avd.amount END), 0) as credit_amount'
                    )
                )
                ->orderBy('av.voucher_date', 'ASC')
                ->get();
        } else {

            $ledgerReport = DB::table('acc_voucher as av')
                ->where([['av.is_delete', 0], ['av.is_active', 1]])
                ->whereIn('av.voucher_status', [1, 2])
                ->where(function ($ledgerReport) use ($companyID, $startDate, $endDate, $branchID, $projectID, $projectTypeID, $voucherTypeID) {
                    // if (!empty($companyID)) {
                    //     $ledgerReport->where('av.company_id', $companyID);
                    // }

                    if (!empty($startDate) && !empty($endDate)) {
                        $ledgerReport->whereBetween('av.voucher_date', [$startDate, $endDate]);
                    }

                    if (!empty($branchID)) {
                        if ($branchID >= 0) {
                            $ledgerReport->where('av.branch_id', $branchID); // Individual Branch
                        } else if ($branchID == -2) {
                            $ledgerReport->where('av.branch_id', '!=', 1); // Branch without head office
                        }
                    }

                    if (!empty($projectID)) {
                        $ledgerReport->where('av.project_id', $projectID);
                    }

                    if (!empty($projectTypeID)) {
                        $ledgerReport->where('av.project_type_id', $projectTypeID);
                    }

                    if (!empty($voucherTypeID)) {
                        $ledgerReport->where('av.voucher_type_id', $voucherTypeID);
                    }
                })
                ->join('acc_voucher_details as avd', function ($ledgerReport) use ($ledgerArray) {
                    $ledgerReport->on('avd.voucher_id', 'av.id')
                        ->where(function ($ledgerReport) use ($ledgerArray) {
                            $ledgerReport->whereIn('avd.debit_acc', $ledgerArray)
                                ->orWhereIn('avd.credit_acc', $ledgerArray);
                        });
                })
                ->leftJoin('acc_account_ledger as acl', function ($ledgerReport) use ($ledgerArray) {
                    $ledgerReport->on(DB::raw('CASE
                                        WHEN avd.debit_acc IN (' . implode(',', $ledgerArray) . ') THEN avd.credit_acc
                                        -- WHEN avd.credit_acc IN (' . implode(',', $ledgerArray) . ') THEN avd.debit_acc
                                        END'), 'acl.id')
                        ->where([['acl.is_delete', 0], ['acl.is_active', 1], ['acl.is_group_head', 0]]);
                })
                ->leftJoin('acc_account_ledger as acl2', function ($ledgerReport) use ($ledgerArray) {
                    $ledgerReport->on(DB::raw('CASE
                                        -- WHEN avd.debit_acc IN (' . implode(',', $ledgerArray) . ') THEN avd.credit_acc
                                        WHEN avd.credit_acc IN (' . implode(',', $ledgerArray) . ') THEN avd.debit_acc
                                        END'), 'acl2.id')
                        ->where([['acl2.is_delete', 0], ['acl2.is_active', 1], ['acl2.is_group_head', 0]]);
                })
                ->select(
                    'acl.name as CreditName',
                    'acl2.name as DebitName',
                    'avd.local_narration',
                    'av.voucher_date',
                    'av.voucher_code',
                    DB::raw(
                        'IFNULL((CASE WHEN avd.debit_acc IN (' . implode(',', $ledgerArray) . ') THEN avd.amount END), 0) as debit_amount,
                        IFNULL((CASE WHEN avd.credit_acc IN (' . implode(',', $ledgerArray) . ') THEN avd.amount END), 0) as credit_amount'
                    )
                )
                ->orderBy('av.voucher_date', 'ASC')
                ->get();
        }

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

        if ($bankAll == false) {
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
                    'debit_or_credit' => ($tb >= 0) ?'Dr' : 'Cr',
                ];

                $DataSet[] = $tempSet;
            }
        } else {

            foreach ($ledgerReport as $key => $row) {
                $tempSet = array();

                $tb = $tb + ($row->debit_amount - $row->credit_amount);
                $positive_tb = $tb;

                if (!empty($row->CreditName) && !empty($row->DebitName)) {

                    $tempSet = [
                        'sl' => $sl++,
                        'voucher_date' => $row->voucher_date,
                        'voucher_code' => $row->voucher_code,
                        'account_head' => $row->DebitName,
                        'local_narration' => $row->local_narration,
                        'debit_amount' => $row->debit_amount,
                        'credit_amount' => number_format(0, 2),
                        'balance' => number_format(abs($tb + $row->debit_amount), 2),
                        'debit_or_credit' => (($tb + $row->debit_amount) >= 0) ?'Dr' : 'Cr',
                    ];

                    $DataSet[] = $tempSet;

                    $tempSet = [
                        'sl' => $sl++,
                        'voucher_date' => $row->voucher_date,
                        'voucher_code' => $row->voucher_code,
                        'account_head' => $row->CreditName,
                        'local_narration' => $row->local_narration,
                        'debit_amount' => number_format(0, 2),
                        'credit_amount' => $row->credit_amount,
                        'balance' => number_format(abs($positive_tb), 2),
                        'debit_or_credit' => ($tb >= 0) ?'Dr' : 'Cr',
                    ];

                    $DataSet[] = $tempSet;
                } else {

                    $tempSet = [
                        'sl' => $sl++,
                        'voucher_date' => $row->voucher_date,
                        'voucher_code' => $row->voucher_code,
                        'account_head' => (!empty($row->CreditName)) ?$row->CreditName : $row->DebitName,
                        'local_narration' => $row->local_narration,
                        'debit_amount' => $row->debit_amount,
                        'credit_amount' => $row->credit_amount,
                        'balance' => number_format(abs($positive_tb), 2),
                        'debit_or_credit' => ($tb >= 0) ?'Dr' : 'Cr',
                    ];

                    $DataSet[] = $tempSet;
                }
            }
        }

        $resultData = [
            'ob_ttl_debit_amt' => $ob_ttl_debit_amt,
            'ob_ttl_credit_amt' => $ob_ttl_credit_amt,
            'ob_ttl_balance' => $ob_ttl_balance,

            'sub_ttl_debit_amt' => $sub_ttl_debit_amt,
            'sub_ttl_credit_amt' => $sub_ttl_credit_amt,
            'sub_ttl_balance' => $sub_ttl_balance,

            'ttl_debit_amt' => $ttl_debit_amt,
            'ttl_credit_amt' => $ttl_credit_amt,
            'ttl_balance' => ($ttl_debit_amt - $ttl_credit_amt),

            'DataSet' => $DataSet,
        ];

        return $resultData;
    }

    /**
     * blCalculationIE is stand for Balance Calculation for Income Expense,
     * its return array data,
     */
    public static function blCalculationIE($branchWise, $startDate = null, $endDate = null, $branchID = null, $companyID = null, $projectID = null, $projectTypeID = null, $voucherTypeID = null)
    {
        $fromDate = null;
        $toDate = null;

        if ($startDate == '') {
            $startDate = null;
        }

        if ($endDate == '') {
            $endDate = null;
        }

        if (!empty($startDate)) {
            $fromDate = (new DateTime($startDate))->format('Y-m-d');
        }

        if (!empty($endDate)) {
            $toDate = (new DateTime($endDate))->format('Y-m-d');
        } else {
            $toDate = (new DateTime(Common::systemCurrentDate()))->format('Y-m-d');
        }

        if ($toDate == null) {
            $toDate = (new DateTime(Common::systemCurrentDate()))->format('Y-m-d');
        }

        $resultData = array();

        $queryData = DB::table('acc_voucher as av')
            ->where([['av.is_delete', 0], ['av.is_active', 1]])
            ->whereIn('av.voucher_status', [1, 2])
            ->where(function ($queryData) use ($companyID, $branchID, $projectID, $projectTypeID, $voucherTypeID) {
                // if (!empty($companyID)) {
                //     $queryData->where('av.company_id', $companyID);
                // }

                if (!empty($branchID)) {
                    if ($branchID > 0) {
                        $queryData->where('av.branch_id', $branchID); // Individual Branch
                    } else if ($branchID == -2) {
                        $queryData->where('av.branch_id', '!=', 1); // Branch without head office
                    }
                }

                if (!empty($projectID)) {
                    $queryData->where('av.project_id', $projectID);
                }

                if (!empty($projectTypeID)) {
                    $queryData->where('av.project_type_id', $projectTypeID);
                }

                if (!empty($voucherTypeID)) {
                    $queryData->where('av.voucher_type_id', $voucherTypeID);
                }
            })
            ->where(function ($queryData) use ($fromDate, $toDate) {
                if (!empty($fromDate) && !empty($toDate)) {
                    $queryData->whereBetween('av.voucher_date', [$fromDate, $toDate]);
                }

                if (!empty($fromDate) && empty($toDate)) {
                    $queryData->where('av.voucher_date', '>=', $fromDate);
                }

                if (empty($fromDate) && !empty($toDate)) {
                    $queryData->where('av.voucher_date', '<=', $toDate);
                }
            })
            ->join('acc_voucher_details as avd', function ($queryData) {
                $queryData->on('avd.voucher_id', 'av.id');
            })
            ->join('acc_account_ledger as acl', function ($queryData) {
                $queryData->on(function ($queryData) {
                    $queryData->on('acl.id', 'avd.debit_acc')
                        ->orOn('acl.id', 'avd.credit_acc');
                });
                $queryData->whereIn('acl.acc_type_id', [12, 13]);
            })
            ->join('gnl_branchs as br', function ($queryData) {
                $queryData->on('av.branch_id', 'br.id');
                $queryData->where([['br.is_active', 1], ['br.is_delete', 0], ['br.is_approve', 1]]);
                $queryData->whereIn('br.id', HRS::getUserAccesableBranchIds());
            })
            ->select(
                DB::raw(
                    'IFNULL(SUM(CASE WHEN avd.debit_acc = acl.id and acl.acc_type_id = 12 THEN avd.amount END), 0) as sum_debit_income,
                    IFNULL(SUM(CASE WHEN avd.credit_acc = acl.id and acl.acc_type_id = 12 THEN avd.amount END), 0) as sum_credit_income,
                    IFNULL(SUM(CASE WHEN avd.debit_acc = acl.id and acl.acc_type_id = 13 THEN avd.amount END), 0) as sum_debit_expense,
                    IFNULL(SUM(CASE WHEN avd.credit_acc = acl.id and acl.acc_type_id = 13 THEN avd.amount END), 0) as sum_credit_expense,
                    (
                        IFNULL(SUM(CASE WHEN avd.credit_acc = acl.id and acl.acc_type_id = 12 THEN avd.amount END), 0)
                        -
                        IFNULL(SUM(CASE WHEN avd.debit_acc = acl.id and acl.acc_type_id = 12 THEN avd.amount END), 0)
                    ) as income_amount,
                    (
                        IFNULL(SUM(CASE WHEN avd.debit_acc = acl.id and acl.acc_type_id = 13 THEN avd.amount END), 0)
                        -
                        IFNULL(SUM(CASE WHEN avd.credit_acc = acl.id and acl.acc_type_id = 13 THEN avd.amount END), 0)
                    ) as expense_amount,
                    (
                        (
                            IFNULL(SUM(CASE WHEN avd.credit_acc = acl.id and acl.acc_type_id = 12 THEN avd.amount END), 0)
                            -
                            IFNULL(SUM(CASE WHEN avd.debit_acc = acl.id and acl.acc_type_id = 12 THEN avd.amount END), 0)
                        )
                        -
                        (
                            IFNULL(SUM(CASE WHEN avd.debit_acc = acl.id and acl.acc_type_id = 13 THEN avd.amount END), 0)
                            -
                            IFNULL(SUM(CASE WHEN avd.credit_acc = acl.id and acl.acc_type_id = 13 THEN avd.amount END), 0)
                        )
                    ) as surplus_amount,
                    av.branch_id, CONCAT(br.branch_code, "-", br.branch_name) as branch_name'
                )
            )
            ->orderBy('av.voucher_date', 'ASC')
            ->groupBy('av.branch_id')
            ->get();

        // dd($queryData);

        if ($branchWise == false) {
            // $resultData['sum_debit_income'] = $queryData->sum('sum_debit_income');
            // $resultData['sum_credit_income'] = $queryData->sum('sum_credit_income');
            // $resultData['sum_debit_expense'] = $queryData->sum('sum_debit_expense');
            // $resultData['sum_credit_expense'] = $queryData->sum('sum_credit_expense');
            $resultData['income_amount'] = $queryData->sum('income_amount');
            $resultData['expense_amount'] = $queryData->sum('expense_amount');
            $resultData['surplus_amount'] = $queryData->sum('surplus_amount');
        } else {
            $resultData[0]['income_amount'] = $queryData->sum('income_amount');
            $resultData[0]['expense_amount'] = $queryData->sum('expense_amount');
            $resultData[0]['surplus_amount'] = $queryData->sum('surplus_amount');

            foreach ($queryData as $row) {
                $resultData[$row->branch_name]['income_amount'] = $row->income_amount;
                $resultData[$row->branch_name]['expense_amount'] = $row->expense_amount;
                $resultData[$row->branch_name]['surplus_amount'] = $row->surplus_amount;
            }
        }

        return $resultData;
    }

    /* Cakculate Cash or Bank Amount (Amount = Debit - Credit) depending on Ledger IDs
    Param ($startDate = optional, $endDate = optional,
    $ledgerIds = Either Cash type or Bank Type Acc Ids, $branchID)
     */
    public static function blCalculationCB($branchWise, $startDate = null, $endDate = null, $branchID = null, $companyID = null, $projectID = null, $projectTypeID = null, $voucherTypeID = null)
    {

        // $companyID = (empty($companyID)) ? Common::getCompanyId() : $companyID;
        // $branchID = (empty($branchID)) ? Common::getBranchId() : $branchID;

        $fromDate = null;
        $toDate = null;

        if ($startDate == '') {
            $startDate = null;
        }

        if ($endDate == '') {
            $endDate = null;
        }

        if (!empty($startDate)) {
            $fromDate = (new DateTime($startDate))->format('Y-m-d');
        }

        if (!empty($endDate)) {
            $toDate = (new DateTime($endDate))->format('Y-m-d');
        } else {
            $toDate = (new DateTime(Common::systemCurrentDate()))->format('Y-m-d');
        }

        if ($toDate == null) {
            $toDate = (new DateTime(Common::systemCurrentDate()))->format('Y-m-d');
        }

        $resultData = array();

        ///////////////////////////

        $obData = DB::table('acc_ob_m as obm')
            ->where([['obm.is_delete', 0], ['obm.is_active', 1], ['obm.is_year_end', 0]])
            ->where(function ($obData) use ($companyID, $branchID, $projectID, $projectTypeID) {

                // if (!empty($companyID)) {
                //     $obData->where('obm.company_id', $companyID);
                // }

                if (!empty($branchID)) {

                    if ($branchID >= 0) {
                        $obData->where('obm.branch_id', $branchID); // Individual Branch
                    } else if ($branchID == -2) {
                        $obData->where('obm.branch_id', '!=', 1); // Branch without head office
                    }
                }

                if (!empty($projectID)) {
                    $obData->where('obm.project_id', $projectID);
                }

                if (!empty($projectTypeID)) {
                    $obData->where('obm.project_type_id', $projectTypeID);
                }
            })
            ->where(function ($obData) use ($fromDate, $toDate) {
                if (!empty($fromDate) && !empty($toDate)) {
                    $obData->whereBetween('obm.opening_date', [$fromDate, $toDate]);
                }

                if (!empty($fromDate) && empty($toDate)) {
                    $obData->where('obm.opening_date', '>=', $fromDate);
                }

                if (empty($fromDate) && !empty($toDate)) {
                    $obData->where('obm.opening_date', '<=', $toDate);
                }
            })
            ->join('acc_ob_d as obd', function ($obData) {
                $obData->on('obd.ob_no', 'obm.ob_no');
            })
            ->join('acc_account_ledger as acl', function ($obData) {
                $obData->on('acl.id', 'obd.ledger_id');
                $obData->whereIn('acl.acc_type_id', [4, 5]);
            })
            ->join('gnl_branchs as br', function ($queryData) {
                $queryData->on('obm.branch_id', 'br.id');
                $queryData->where([['br.is_active', 1], ['br.is_delete', 0], ['br.is_approve', 1]]);
                $queryData->whereIn('br.id', HRS::getUserAccesableBranchIds());
            })
            ->select(
                DB::raw(
                    'IFNULL(SUM(CASE WHEN acl.acc_type_id = 4 THEN obd.debit_amount END), 0) as sum_debit_cash,
                    IFNULL(SUM(CASE WHEN acl.acc_type_id = 4 THEN obd.credit_amount END), 0) as sum_credit_cash,
                    IFNULL(SUM(CASE WHEN acl.acc_type_id = 5 THEN obd.debit_amount END), 0) as sum_debit_bank,
                    IFNULL(SUM(CASE WHEN acl.acc_type_id = 5 THEN obd.credit_amount END), 0) as sum_credit_bank,
                    (
                        IFNULL(SUM(CASE WHEN acl.acc_type_id = 4 THEN obd.debit_amount END), 0)
                        -
                        IFNULL(SUM(CASE WHEN acl.acc_type_id = 4 THEN obd.credit_amount END), 0)
                    ) as cash_amount,
                    (
                        IFNULL(SUM(CASE WHEN acl.acc_type_id = 5 THEN obd.debit_amount END), 0)
                        -
                        IFNULL(SUM(CASE WHEN acl.acc_type_id = 5 THEN obd.credit_amount END), 0)
                    ) as bank_amount,
                    obm.branch_id, CONCAT(br.branch_code, "-", br.branch_name) as branch_name'
                )
            )
            ->orderBy('obm.opening_date', 'ASC')
            ->groupBy('obm.branch_id')
            ->get();

        ////////////////////////////////////////////////
        $queryData = DB::table('acc_voucher as av')
            ->where([['av.is_delete', 0], ['av.is_active', 1]])
            ->whereIn('av.voucher_status', [1, 2])
            ->where(function ($queryData) use ($companyID, $branchID, $projectID, $projectTypeID, $voucherTypeID) {
                // if (!empty($companyID)) {
                //     $queryData->where('av.company_id', $companyID);
                // }

                if (!empty($branchID)) {
                    if ($branchID > 0) {
                        $queryData->where('av.branch_id', $branchID); // Individual Branch
                    } else if ($branchID == -2) {
                        $queryData->where('av.branch_id', '!=', 1); // Branch without head office
                    }
                }

                if (!empty($projectID)) {
                    $queryData->where('av.project_id', $projectID);
                }

                if (!empty($projectTypeID)) {
                    $queryData->where('av.project_type_id', $projectTypeID);
                }

                if (!empty($voucherTypeID)) {
                    $queryData->where('av.voucher_type_id', $voucherTypeID);
                }
            })
            ->where(function ($queryData) use ($fromDate, $toDate) {
                if (!empty($fromDate) && !empty($toDate)) {
                    $queryData->whereBetween('av.voucher_date', [$fromDate, $toDate]);
                }

                if (!empty($fromDate) && empty($toDate)) {
                    $queryData->where('av.voucher_date', '>=', $fromDate);
                }

                if (empty($fromDate) && !empty($toDate)) {
                    $queryData->where('av.voucher_date', '<=', $toDate);
                }
            })
            ->join('acc_voucher_details as avd', function ($queryData) {
                $queryData->on('avd.voucher_id', 'av.id');
            })
            ->join('acc_account_ledger as acl', function ($queryData) {
                $queryData->on(function ($queryData) {
                    $queryData->on('acl.id', 'avd.debit_acc')
                        ->orOn('acl.id', 'avd.credit_acc');
                });
                $queryData->whereIn('acl.acc_type_id', [4, 5]);
            })
            ->join('gnl_branchs as br', function ($queryData) {
                $queryData->on('av.branch_id', 'br.id');
                $queryData->where([['br.is_active', 1], ['br.is_delete', 0], ['br.is_approve', 1]]);
                $queryData->whereIn('br.id', HRS::getUserAccesableBranchIds());
            })
            ->select(
                DB::raw(
                    'IFNULL(SUM(CASE WHEN avd.debit_acc = acl.id and acl.acc_type_id = 4 THEN avd.amount END), 0) as sum_debit_cash,
                    IFNULL(SUM(CASE WHEN avd.credit_acc = acl.id and acl.acc_type_id = 4 THEN avd.amount END), 0) as sum_credit_cash,
                    IFNULL(SUM(CASE WHEN avd.debit_acc = acl.id and acl.acc_type_id = 5 THEN avd.amount END), 0) as sum_debit_bank,
                    IFNULL(SUM(CASE WHEN avd.credit_acc = acl.id and acl.acc_type_id = 5 THEN avd.amount END), 0) as sum_credit_bank,
                    (
                        IFNULL(SUM(CASE WHEN avd.debit_acc = acl.id and acl.acc_type_id = 4 THEN avd.amount END), 0)
                        -
                        IFNULL(SUM(CASE WHEN avd.credit_acc = acl.id and acl.acc_type_id = 4 THEN avd.amount END), 0)
                    ) as cash_amount,
                    (
                        IFNULL(SUM(CASE WHEN avd.debit_acc = acl.id and acl.acc_type_id = 5 THEN avd.amount END), 0)
                        -
                        IFNULL(SUM(CASE WHEN avd.credit_acc = acl.id and acl.acc_type_id = 5 THEN avd.amount END), 0)
                    ) as bank_amount,
                    av.branch_id, CONCAT(br.branch_code, "-", br.branch_name) as branch_name'
                )
            )
            ->orderBy('av.voucher_date', 'ASC')
            ->groupBy('av.branch_id')
            ->get();

        if ($branchWise == false) {

            $resultData['Cash'] = $obData->sum('cash_amount') + $queryData->sum('cash_amount');
            $resultData['Bank'] = $obData->sum('bank_amount') + $queryData->sum('bank_amount');
            $resultData['Total_Balance'] = $resultData['Cash'] + $resultData['Bank'];
        } else {
            $resultData[0]['Cash'] = $obData->sum('cash_amount') + $queryData->sum('cash_amount');
            $resultData[0]['Bank'] = $obData->sum('bank_amount') + $queryData->sum('bank_amount');
            $resultData[0]['Total_Balance'] = $resultData[0]['Cash'] + $resultData[0]['Bank'];

            // Opening Balance Table
            foreach ($obData as $row) {
                $resultData[$row->branch_name]['Cash'] = $row->cash_amount;
                $resultData[$row->branch_name]['Bank'] = $row->bank_amount;
                $resultData[$row->branch_name]['Total_Balance'] = $row->cash_amount + $row->bank_amount;
            }

            // Voucher Table
            foreach ($queryData as $rowQ) {
                if (isset($resultData[$rowQ->branch_name])) {
                    $resultData[$rowQ->branch_name]['Cash'] += $rowQ->cash_amount;
                    $resultData[$rowQ->branch_name]['Bank'] += $rowQ->bank_amount;
                    $resultData[$rowQ->branch_name]['Total_Balance'] += $rowQ->cash_amount + $rowQ->bank_amount;
                } else {
                    $resultData[$rowQ->branch_name]['Cash'] = $rowQ->cash_amount;
                    $resultData[$rowQ->branch_name]['Bank'] = $rowQ->bank_amount;
                    $resultData[$rowQ->branch_name]['Total_Balance'] = $rowQ->cash_amount + $rowQ->bank_amount;
                }
            }
        }

        // $resultData['Cash'] = $obData->cash_amount + $queryData->cash_amount;
        // $resultData['Bank'] = $obData->bank_amount + $queryData->bank_amount;
        // $resultData['Total_Balance'] = $resultData['Cash'] + $resultData['Bank'];

        return $resultData;
    }

    ///////////////////////////////////////////////////////
    // Profit/Loss from income statement
    public static function funcIncomeStatememnt(
        $startDate,
        $endDate,
        $ledgerChilds = [],
        $branchID = null,
        $projectID = null,
        $projectTypeID = null
    ) {
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
                        DB::raw(
                            '
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

    /**
     * in $req == ()
     * credit_arr credit ledger id arrays
     * debit_arr debit ledger id arrays
     * amount_arr amount array
     * narration_arr  for local narration array
     *
     * others all same as voucher and voucher details staructure
     * /// iff need module perameter would be added later as per need
     *
     * *******************to generate a voucher dont passs voucher code .... it will generate by it self
     * *******************for update a voucher pass voucher code
     *
     *
     */

    public static function insertVoucher(Request $req)
    {

        $RequestData = new Request;
        $RequestData = $req->all();
        // dd($RequestData);
        // $RequestData = array();
        $RequestData['company_id'] = Common::getCompanyId();
        $prep_by = (isset($RequestData['prep_by']) && !empty($RequestData['prep_by'])) ?$RequestData['prep_by'] : Auth::id();
        $RequestData['prep_by'] = $prep_by;
        $RequestData['created_by'] = $prep_by;
        // dd($RequestData);

        $CreditACC = (isset($req->credit_arr) ?$req->credit_arr : array());
        $DebitACC = (isset($req->debit_arr) ?$req->debit_arr : array());
        $amount_arr = (isset($req->amount_arr) ?$req->amount_arr : array());
        $narration_arr = (isset($req->narration_arr) ?$req->narration_arr : array());

        /*
        //  set module id for mmicro finance auto voucher code
        if((empty($RequestData['voucher_code']) || $RequestData['voucher_code'] == null) && $RequestData['v_generate_type'] == 1){
        // dd('juj');
        $previousVoucher =  Voucher::where(['voucher_date' =>  $RequestData['voucher_date'], 'v_generate_type' =>  $RequestData['v_generate_type'],'branch_id' => $RequestData['branch_id'], 'voucher_type_id' =>$RequestData['voucher_type_id'] ])->first();

        }

        if(!empty($previousVoucher)){
        $RequestData['voucher_code']=$previousVoucher->voucher_code;
        }
        // dd($CreditACC,$DebitACC);

         */

        if (!empty($RequestData['voucher_code'])) {

            $vouchercode = Voucher::where('voucher_code', $RequestData['voucher_code'])->first();

            if (!empty($vouchercode)) {

                $RequestData['voucher_code'] = self::generateBillVoucher($req->branch_id, $req->voucher_type_id, $req->project_id, $req->project_type_id);
            }
        } else {
            # code...
            $RequestData['voucher_code'] = self::generateBillVoucher($req->branch_id, $req->voucher_type_id, $req->project_id, $req->project_type_id);
        }

        //    dd($vouchercode);
        DB::beginTransaction();
        try {

            // $RequestData['branch_id'] = (int) $RequestData['branch_id'];

            // dd($RequestData['branch_id']);

            $isInsert = Voucher::create($RequestData);

            $isInsertID = Voucher::where(['voucher_code' => $RequestData['voucher_code']])->first()->id;

            if ($isInsert) {

                /* Child Table Insertion */
                // $RequestData2['branch_id'] = $RequestData['branch_id'];
                $RequestData2['voucher_id'] = $isInsertID;
                $RequestData2['ft_from'] = (!empty($RequestData['ft_from'])) ?$RequestData['ft_from'] : 0;
                $RequestData2['ft_to'] = (!empty($RequestData['ft_to'])) ?$RequestData['ft_to'] : 0;
                $RequestData2['ft_target_acc'] = (!empty($RequestData['ft_target_acc'])) ?$RequestData['ft_target_acc'] : null;

                foreach ($amount_arr as $key => $Row) {
                    if (!empty($Row)) {
                        $RequestData2['amount'] = $Row;
                        $RequestData2['debit_acc'] = $DebitACC[$key];
                        $RequestData2['credit_acc'] = $CreditACC[$key];
                        $RequestData2['local_narration'] = isset($narration_arr[$key]) ?$narration_arr[$key] : null;

                        $isInsertDetails = VoucherDetails::create($RequestData2);
                    }
                }
                DB::commit();
                $notification = array(
                    'message' => 'Successfully inserted Voucher and Details',
                    'alert-type' => 'success',
                );
                return $notification;
            }
        } catch (Exception $e) {
            DB::rollBack();
            $notification = array(
                'message' => 'Unsuccessful to inserted Voucher Details',
                'alert-type' => 'error',
                'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
            );

            return $notification;
        }
    }

    public static function updateVoucher(Request $req)
    {
        $ModelVoucher = 'App\\Model\\Acc\\Voucher';
        $ModelVoucherDetails = 'App\\Model\\Acc\\VoucherDetails';

        $RequestData = array();
        $RequestData['branch_id'] = $req->branch_id;
        $RequestData['company_id'] = Common::getCompanyId();
        $RequestData['voucher_type_id'] = $req->voucher_type_id;
        $RequestData['project_id'] = $req->project_id;
        $RequestData['project_type_id'] = $req->project_type_id;
        $RequestData['v_generate_type'] = $req->v_generate_type;
        $RequestData['voucher_date'] = $req->voucher_date;
        $RequestData['global_narration'] = $req->global_narration;
        $RequestData['voucher_code'] = $req->voucher_code;
        $prep_by = (isset($RequestData['prep_by']) && !empty($RequestData['prep_by'])) ?$RequestData['prep_by'] : Auth::id();
        $RequestData['prep_by'] = $prep_by;
        $RequestData['created_by'] = $prep_by;

        $RequestData['ft_from'] = (!empty($req->ft_from)) ?$req->ft_from : 0;
        $RequestData['ft_to'] = (!empty($req->ft_to)) ?$req->ft_to : 0;
        $RequestData['ft_target_acc'] = (!empty($req->ft_target_acc)) ?$req->ft_target_acc : null;
        // dd(empty($RequestData['voucher_code']));


        $CreditACC = (isset($req->credit_arr) ?$req->credit_arr : array());
        $DebitACC = (isset($req->debit_arr) ?$req->debit_arr : array());
        $amount_arr = (isset($req->amount_arr) ?$req->amount_arr : array());
        $narration_arr = (isset($req->narration_arr) ?$req->narration_arr : array());

        //dd($CreditACC,  $DebitACC ,$amount_arr);

        if ((empty($RequestData['voucher_code']) || $RequestData['voucher_code'] == null) && $RequestData['v_generate_type'] == 1) {
            // dd('juj');
            $previousVoucher =  $ModelVoucher::where(['voucher_date' =>  $RequestData['voucher_date'], 'v_generate_type' =>  $RequestData['v_generate_type'], 'branch_id' => $RequestData['branch_id'], 'voucher_type_id' => $RequestData['voucher_type_id']])->first();
        }
        // dd($previousVoucher);

        if (!empty($previousVoucher)) {
            $RequestData['voucher_code'] = $previousVoucher->voucher_code;
        }
        // dd($CreditACC,$DebitACC);


        if (empty($RequestData['voucher_code']) || $RequestData['voucher_code'] == null) {
            //   dd($CreditACC,$DebitACC,'empty');

            $RequestData['voucher_code'] = self::generateBillVoucher($req->branch_id, $req->voucher_type_id, $req->project_id, $req->project_type_id);
            DB::beginTransaction();
            try {

                $isInsert = $ModelVoucher::create($RequestData);
                $lastInsertQuery = $ModelVoucher::where('voucher_code', $RequestData['voucher_code'])->first();

                // dd($isInsert);
                if ($isInsert) {

                    /* Child Table Insertion */
                    $RequestData2['branch_id'] = $RequestData['branch_id'];
                    $RequestData2['voucher_id'] = $lastInsertQuery->id;
                    $RequestData2['ft_from'] = (!empty($RequestData['ft_from'])) ?$RequestData['ft_from'] : 0;
                    $RequestData2['ft_to'] = (!empty($RequestData['ft_to'])) ?$RequestData['ft_to'] : 0;
                    $RequestData2['ft_target_acc'] = (!empty($RequestData['ft_target_acc'])) ?$RequestData['ft_target_acc'] : null;

                    foreach ($amount_arr as $key => $Row) {
                        if (!empty($Row)) {
                            $RequestData2['amount'] = $Row;
                            $RequestData2['debit_acc'] = $DebitACC[$key];
                            $RequestData2['credit_acc'] = $CreditACC[$key];
                            $RequestData2['local_narration'] = isset($narration_arr[$key]) ?$narration_arr[$key] : null;

                            $isInsertDetails = $ModelVoucherDetails::create($RequestData2);
                        }
                    }
                    DB::commit();
                    $notification = array(
                        'message' => 'Successfully inserted Voucher Details',
                        'alert-type' => 'success',
                    );
                    return response()->json($notification);
                }
            } catch (Exception $e) {
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to inserted Voucher Details',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );

                return response()->json($notification);
            }
        } else {
            DB::beginTransaction();
            try {

                // dd($DebitACC,$CreditACC,$RequestData['voucher_code']);

                $isInsert = $ModelVoucher::updateOrCreate(['voucher_code' => $RequestData['voucher_code']], $RequestData);
                $lastInsertQuery = $ModelVoucher::where('voucher_code', $RequestData['voucher_code'])->first();

                // dd($isInsert);
                if ($isInsert) {

                    /* Child Table Insertion */
                    $RequestData2['branch_id'] = $RequestData['branch_id'];
                    $RequestData2['voucher_id'] = $lastInsertQuery->id;
                    $RequestData2['ft_from'] = (!empty($RequestData['ft_from'])) ?$RequestData['ft_from'] : 0;
                    $RequestData2['ft_to'] = (!empty($RequestData['ft_to'])) ?$RequestData['ft_to'] : 0;
                    $RequestData2['ft_target_acc'] = (!empty($RequestData['ft_target_acc'])) ?$RequestData['ft_target_acc'] : null;


                    $deleteDetailsObjs = $ModelVoucherDetails::where('voucher_id', $lastInsertQuery->id)->get();


                    $updateIDArray = array();




                    foreach ($amount_arr as $key => $Row) {
                        if (!empty($Row)) {
                            $RequestData2['amount'] = $Row;
                            $RequestData2['debit_acc'] = $DebitACC[$key];
                            $RequestData2['credit_acc'] = $CreditACC[$key];
                            $RequestData2['local_narration'] = isset($narration_arr[$key]) ?$narration_arr[$key] : null;

                            $updateid = $deleteDetailsObjs->where('debit_acc', $DebitACC[$key])
                                ->where('credit_acc', $CreditACC[$key])
                                ->whereNotIn('id', $updateIDArray)
                                ->first();

                            // dd($updateid->id);

                            if (!empty($updateid)) {
                                array_push($updateIDArray, $updateid->id);
                                $isInsertDetails = $ModelVoucherDetails::updateOrCreate(['id' => $updateid->id], $RequestData2);
                            } else {
                                    $isInsertDetails = $ModelVoucherDetails::create($RequestData2);
                                }
                        }
                    }

                    $tobeDeleteIDs = $deleteDetailsObjs->whereNotIn('id', $updateIDArray);
                    $ModelVoucherDetails::whereIn('id', $tobeDeleteIDs->pluck('id')->toArray())->delete();

                    // dd($deleteDetailsObjs->all());
                    DB::commit();

                    $notification = array(
                        'message' => 'Successfully inserted Voucher Details',
                        'alert-type' => 'success',
                    );
                    return response()->json($notification);
                }
            } catch (Exception $e) {
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to inserted Voucher Details',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );

                return response()->json($notification);
            }
        }

        # code...
    }
}
