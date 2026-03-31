<?php

namespace App\Http\Controllers\MFN\Loan;

use App\Http\Controllers\Controller;
use App\Model\MFN\LoanAdjustments;
use App\Services\HrService;
use App\Services\MfnService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class AdjustmentController extends Controller
{     
    public function index(Request $req)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        if (!$req->ajax()) {

            $branchList = DB::table('gnl_branchs')
                ->where([
                    ['is_delete', 0],
                    ['id', '>', 1],
                ])
                ->whereIn('id', $accessAbleBranchIds)
                ->orderBy('branch_code')
                ->select('id', 'branch_name')
                ->get();

            $data = array(
                'branchList' => $branchList,
            );

            return view('MFN.Loan.Adjustment.index');
        }

        $columns = [
            'memberName',
            'memberCode',
            'loanCode',
            'date',
            'adjustmentAmount',
            'adjustmentDetails',
            'status',
            'entryBy',
            'action',
        ];

        $limit            = $req->length;
        $orderColumnIndex = (int) $req->input('order.0.column') <= 1 ? 0 : (int) $req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $adjData = DB::table('mfn_loan_adjustments as mla')
            ->where('mla.is_delete', 0)
            ->select('mla.id', 'mm.name as memberName', 'mm.memberCode as memberCode', 'ml.loanCode as loanCode', 'mla.date', 'mla.adjustmentAmount', 'mla.adjustmentDetails', 'mla.status', 'gsu.full_name as entryBy')
            ->leftJoin('mfn_loans as ml', 'mla.loanId', 'ml.id')
            ->leftJoin('mfn_members as mm', 'ml.memberId', 'mm.id')
            ->leftJoin('gnl_sys_users as gsu', 'mla.created_by', 'gsu.id')
            ->whereIn('mla.branchId', $accessAbleBranchIds)
            ->where(function ($adjData) use ($search) {

                if ($search != null || !empty($search)) {
                    $adjData->Where('mm.name', 'LIKE', "%{$search}%")
                        ->orWhere('mm.memberCode', 'LIKE', "%{$search}%");
                }
            })
            ->orderBy($order, $dir)
            ->limit($limit)
            ->offset($req->start)
            ->get();

        $totalData         = $adjData->count();
        $sl                = (int) $req->start + 1;
        $adjDetailsRowData = '';

        foreach ($adjData as $key => $row) {

            $adjDetails = json_decode($row->adjustmentDetails);

            if (count($adjDetails) > 1) {

                foreach ($adjDetails as $value) {

                    $adjDetailsRowData .= '<span><b>Account Id: ' . $value->accountId . ' </b></span><br>';
                    $adjDetailsRowData .= '<span>Amount: ' . $value->amount . ' </span><br>';
                }
            } else {
                $adjDetailsRowData .= '<span><b>Account Code: ' . $adjDetails[0]->accountId . '</b></span><br>';
                $adjDetailsRowData .= '<span>Amount: ' . $adjDetails[0]->amount . ' </span>';
            }

            if ($row->status == 'Pending') {
                $adjData[$key]->status = '<span style="color: red;"><b>' . $row->status . '</b></span>';
            } elseif ($row->status == 'Approved') {
                $adjData[$key]->status = '<span style="color: limegreen;"><b>' . $row->status . '</b></span>';
            }

            $adjData[$key]->sl                = $sl++;
            $adjData[$key]->date              = Carbon::parse($row->date)->format('d-m-Y');
            $adjData[$key]->adjustmentAmount  = round($row->adjustmentAmount, 2);
            $adjData[$key]->adjustmentDetails = $adjDetailsRowData;
            $adjData[$key]->id                = encrypt($row->id);
        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $adjData,
        );

        return response()->json($data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }
        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $members = MfnService::getSelectizeMembers(['branchId' => Auth::user()->branch_id, 'dateTo' => $sysDate]);

        $data = array(
            'members' => $members,
        );

        return view('MFN.Loan.Adjustment.add', $data);
    }

    public function store(Request $req)
    {
        DB::beginTransaction();

        try {
            $reqData         = $req->all();
            $sysDate         = MfnService::systemCurrentDate(Auth::user()->branch_id);
            $reqData['date'] = Carbon::parse($sysDate)->format('Y-m-d');

            $accIdArr   = (isset($reqData['accIdArr']) ? $reqData['accIdArr'] : array());
            $accountArr = (isset($reqData['accountArr']) ? $reqData['accountArr'] : array());
            $accCodeArr = (isset($reqData['accCodeArr']) ? $reqData['accCodeArr'] : array());
            $amountArr  = (isset($reqData['amountArr']) ? $reqData['amountArr'] : array());

            $adjustmentAmount  = 0;
            $adjustmentDetails = [];

            foreach ($accountArr as $key => $acc) {

                $adjustmentDetails[] = [
                    'accountId' => $accIdArr[$acc],
                    'amount'    => $amountArr[$acc],
                ];

                $adjustmentAmount += $amountArr[$acc];
            }

            $reqData['adjustmentAmount']  = $adjustmentAmount;
            $reqData['adjustmentDetails'] = json_encode($adjustmentDetails);
            $reqData['created_by']        = Auth::user()->id;

            LoanAdjustments::create($reqData);

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Inserted',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
                'consoleMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );

            return response()->json($notification);
        }
    }

    public function edit(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->update($req);
        }

        $adjData = LoanAdjustments::find(decrypt($req->id));

        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
        $members = MfnService::getSelectizeMembers(['branchId' => Auth::user()->branch_id, 'dateTo' => $sysDate]);

        $accId     = array();
        $accAmount = array();

        $adjDetails = json_decode($adjData->adjustmentDetails);
        if (count($adjDetails) > 1) {
            foreach ($adjDetails as $value) {
                array_push($accId, $value->accountId);
                array_push($accAmount, $value->amount);
            }
        } else {

            array_push($accId, $adjDetails[0]->accountId);
            array_push($accAmount, $adjDetails[0]->amount);
        }

        $data = array(
            'loanId'    => $adjData->loanId,
            'memberId'  => $adjData->memberId,
            'samityId'  => $adjData->samityId,
            'branchId'  => $adjData->branchId,
            'accId'     => $accId,
            'accAmount' => $accAmount,

            'members'   => $members,
        );

        return view('MFN.Loan.Adjustment.edit', $data);

    }

    public function update(Request $req)
    {
        $adjData = LoanAdjustments::find(decrypt($req->id));

        try {
            $reqData         = $req->all();
            $sysDate         = MfnService::systemCurrentDate(Auth::user()->branch_id);
            $reqData['date'] = Carbon::parse($sysDate)->format('Y-m-d');

            $accIdArr   = (isset($reqData['accIdArr']) ? $reqData['accIdArr'] : array());
            $accountArr = (isset($reqData['accountArr']) ? $reqData['accountArr'] : array());
            $accCodeArr = (isset($reqData['accCodeArr']) ? $reqData['accCodeArr'] : array());
            $amountArr  = (isset($reqData['amountArr']) ? $reqData['amountArr'] : array());

            $adjustmentAmount  = 0;
            $adjustmentDetails = [];

            foreach ($accountArr as $key => $acc) {

                $adjustmentDetails[] = [
                    'accountId' => $accIdArr[$acc],
                    'amount'    => $amountArr[$acc],
                ];

                $adjustmentAmount += $amountArr[$acc];
            }

            $reqData['adjustmentAmount']  = $adjustmentAmount;
            $reqData['adjustmentDetails'] = json_encode($adjustmentDetails);
            $reqData['updated_by']        = Auth::user()->id;

            $adjData->update($reqData);

        } catch (\Exception $e) {

            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
                'consoleMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );

            return response()->json($notification);
        }

        $notification = array(
            'message'    => 'Successfully Updated',
            'alert-type' => 'success',
        );

        return response()->json($notification);
    }

    public function delete(Request $req)
    {
        $adjustment = LoanAdjustments::find(decrypt($req->id));

        try {
            LoanAdjustments::where('id', $adjustment->id)->update(['is_delete' => 1]);

        } catch (\Throwable $th) {
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
                'consoleMsg' => $th->getFile() . ' ' . $th->getLine() . ' ' . $th->getMessage(),
            );

            return response()->json($notification);
        }

        $notification = array(
            'message'    => 'Successfully Deleted',
            'alert-type' => 'success',
        );

        return response()->json($notification);
    }

    public function view(Request $req)
    {
        $adjData = DB::table('mfn_loan_adjustments as mla')
            ->where('mla.id', decrypt($req->id))
            ->select('mla.*', 'mm.name as memberName', 'mm.memberCode', 'ml.loanCode', 'ms.samityCode', 'gsu.full_name as entryBy')
            ->leftJoin('mfn_loans as ml', 'mla.loanId', 'ml.id')
            ->leftJoin('mfn_members as mm', 'mla.memberId', 'mm.id')
            ->leftJoin('mfn_samity as ms', 'mla.samityId', 'ms.id')
            ->leftJoin('gnl_sys_users as gsu', 'mla.created_by', 'gsu.id')
            ->first();

        $adjDetailsRowData = '';
        $adjDetails        = json_decode($adjData->adjustmentDetails);

        if (count($adjDetails) > 1) {

            foreach ($adjDetails as $value) {

                $adjDetailsRowData .= '<span><b>Account Id: ' . $value->accountCode . ' </b></span><br>';
                $adjDetailsRowData .= '<span>Amount: ' . $value->amount . ' </span><br>';
            }
        } else {
            $adjDetailsRowData .= '<span><b>Account Code: ' . $value->accountCode . '</b></span><br>';
            $adjDetailsRowData .= '<span>Amount: ' . $value->amount . ' </span>';
        }

        $data = array(
            'memberName' => $adjData->memberName,
            'memberCode' => $adjData->memberCode,
            'loanCode'   => $adjData->loanCode,
            'samityCode' => $adjData->samityCode,
            'date'       => Carbon::parse($adjData->date)->format('d-m-Y'),
            'adjAmount'  => $adjData->adjustmentAmount,
            'adjDetails' => $adjDetailsRowData,
            'entryBy'    => $adjData->entryBy,
        );

        return response()->json($data);
    }

    public function approve(Request $req)
    {
        $adjustment = LoanAdjustments::find(decrypt($req->id));

        if ($adjustment->status == 'Pending') {
            $pass = $this->validationPass($req, $operationType = 'approve', $adjustment);
            if ($pass['isValid'] == false) {
                $notification = array(
                    'message'    => $pass['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            DB::beginTransaction();
            try {

                //insert collection
                $collectionData['loanId']          = $adjustment->loanId;
                $collectionData['memberId']        = $adjustment->memberId;
                $collectionData['samityId']        = $adjustment->samityId;
                $collectionData['branchId']        = $adjustment->branchId;
                $collectionData['collectionDate']  = $adjustment->date;
                $collectionData['amount']          = $adjustment->adjustmentAmount;
                $collectionData['paymentType']     = 'Adjustment';
                $collectionData['transactionType'] = 'Regular';

                $collectionReq = new Request;
                $collectionReq->merge($collectionData);
                $response = app('App\Http\Controllers\MFN\Loan\LoanTransactionController')->store($collectionReq)->getData();

                if ($response->{'alert-type'} == 'error') {
                    $notification = array(
                        'message'    => $response->message,
                        'alert-type' => 'error',
                    );
                    return response()->json($notification);
                }
                $collnId = $response->createId;

                //insert widthdraw
                $isMultiCreate    = array();
                $isSingcreate     = null;
                $isWithDrawCreate = '';
                $withdrawData     = array();

                $withdrawData['date']              = $adjustment->date;
                $withdrawData['ledgerId']          = 0;
                $withdrawData['chequeNo']          = '';
                $withdrawData['transactionTypeId'] = 10;

                $adjDetails = json_decode($adjustment->adjustmentDetails);

                if (count($adjDetails) > 1) {

                    foreach ($adjDetails as $row) {

                        $withdrawData['accountId'] = $row->accountId;
                        $withdrawData['amount']    = $row->amount;

                        $collectionReq = new Request;
                        $collectionReq->merge($withdrawData);
                        $response = app('App\Http\Controllers\MFN\Savings\WithdrawController')->store($collectionReq)->getData();

                        if ($response->{'alert-type'} == 'error') {
                            $notification = array(
                                'message'    => $response->message,
                                'alert-type' => 'error',
                            );
                            return response()->json($notification);
                        }
                        array_push($isMultiCreate, $response->createId);
                    }

                } else {

                    $withdrawData['accountId'] = $adjDetails[0]->accountId;
                    $withdrawData['amount']    = $adjDetails[0]->amount;

                    $collectionReq = new Request;
                    $collectionReq->merge($withdrawData);
                    $response = app('App\Http\Controllers\MFN\Savings\WithdrawController')->store($collectionReq)->getData();

                    if ($response->{'alert-type'} == 'error') {
                        $notification = array(
                            'message'    => $response->message,
                            'alert-type' => 'error',
                        );
                        return response()->json($notification);
                    }
                    $isSingcreate = $response->createId;
                }

                if (is_null($isSingcreate)) {
                    $isWithDrawCreate = implode(',', $isMultiCreate);
                } else {
                    $isWithDrawCreate = $isSingcreate;
                }

                $adjustment->update(['status' => 'Approved', 'collectionId' => $collnId, 'withdrawIds' => $isWithDrawCreate]);

                DB::commit();
                $notification = array(
                    'message'    => 'Successfully Approved',
                    'alert-type' => 'success',
                );

                return response()->json($notification);

            } catch (\Throwable $th) {
                DB::rollback();
                $notification = array(
                    'alert-type' => 'error',
                    'message'    => 'Something went wrong',
                    'consoleMsg' => $th->getFile() . ' ' . $th->getLine() . ' ' . $th->getMessage(),
                );

                return response()->json($notification);
            }
        } elseif ($adjustment->status == 'Approve') {
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Already approved',
            );

            return response()->json($notification);
        }
    }

    public function getData(Request $req)
    {
        if ($req->context == 'member') {

            $filters['memberId'] = $req->memberId;
            $filters['status']   = 'Living';
            $loans               = MfnService::getLoanAccounts($filters);
            $loans               = $loans->where('loanType', 'Regular')->pluck('loanCode', 'id')->all();

            $adjConfig = DB::table('mfn_config')->where('title', 'loanAdjustment')->select('content')->first();

            $savingAcc = DB::table('mfn_savings_accounts as msa')
                ->where([
                    ['msa.memberId', $req->memberId],
                    ['msa.is_delete', 0],
                ])
                ->whereIn('msa.savingsProductId', json_decode($adjConfig->content))
                ->get();

            $i        = 1;
            $check    = '';
            $readOnly = 'readOnly';
            if (count($savingAcc) == 1) {
                $check    = 'Checked';
                $readOnly = '';
            }

            $savingTable = '<tbody>';

            foreach ($savingAcc as $item) {

                $accFilters['branchId']  = $item->branchId;
                $accFilters['samityId']  = $item->samityId;
                $accFilters['memberId']  = $item->memberId;
                $accFilters['accountId'] = $item->id;
                $savingAmount            = MfnService::getSavingsBalance($accFilters);

                $savingTable .= '<tr>' .
                '<td>
                            <input type="checkbox" name="accountArr[]" id="accId_' . $i . '" onclick="fnEnterAmount(' . $i . ')" value="' . $item->id . '" ' . $check . '>
                        </td>
                        <td>
                            ' . $item->accountCode . '
                            <input type="hidden" name="accCodeArr[' . $item->id . ']" value="' . $item->accountCode . '">
                        </td>
                        <td>
                            ' . $savingAmount . '
                            <input type="hidden" id="savingAmt_' . $i . '" value="' . $savingAmount . '">
                            <input type="hidden" name="accIdArr[' . $item->id . ']" value="' . $item->id . '">
                        </td>
                        <td>
                            <input type="text" name="amountArr[' . $item->id . ']" id="amount_' . $i . '" value="' . 0 . '" class="form-control textNumber" ' . $readOnly . ' onkeyup="fnCheckAmount(' . $i . ');">
                        </td>' .
                    '</tr>';

                $i++;
            }

            $savingTable .= '</tbody>';

            $data = array(
                'loans'       => $loans,
                'savingAcc'   => $savingAcc,
                'savingTable' => $savingTable,
            );
        }

        if ($req->context == 'loanInfo') {

            $loanInfo = DB::table('mfn_loans as ml')
                ->where('ml.id', $req->loanId)
                ->select('ml.loanAmount as pricipal_payable', 'ml.ineterestAmount as interest_payable', 'ml.actualInstallmentAmount as pricipal_installment', 'ml.extraInstallmentAmount as interest_installment', 'ml.installmentAmount as total_installment', 'ml.samityId', 'ml.branchId', DB::raw("SUM(mlc.principalAmount) as pricipal_trans, SUM(mlc.interestAmount) as interest_trans, (IFNULL(SUM(mlc.principalAmount), 0) + IFNULL(SUM(mlc.interestAmount), 0)) as total_trans, (ml.loanAmount - IFNULL(SUM(mlc.principalAmount), 0)) as pricipal_outstanding, (ml.ineterestAmount - IFNULL(SUM(mlc.interestAmount), 0)) as interest_outstanding, ((ml.loanAmount - IFNULL(SUM(mlc.principalAmount), 0)) + (ml.ineterestAmount - IFNULL(SUM(mlc.interestAmount), 0))) as total_outstanding, IFNULL(IF(ml.loanType = 'Regular', ml.repayAmount, ml.loanAmount), 0) as total_payable"))
                ->leftjoin('mfn_loan_collections as mlc', 'ml.id', 'mlc.loanId')
                ->groupBy('ml.id')
                ->first();

            $loanStatus = MfnService::getLoanStatus($req->loanId)[0];

            $data = array(
                'total_payable'        => round($loanInfo->total_payable, 2),
                'pricipal_payable'     => round($loanInfo->pricipal_payable, 2),
                'interest_payable'     => round($loanInfo->interest_payable, 2),
                'total_trans'          => round($loanInfo->total_trans, 2),
                'pricipal_trans'       => round($loanInfo->pricipal_trans, 2),
                'interest_trans'       => round($loanInfo->interest_trans, 2),
                'total_outstanding'    => round($loanInfo->total_outstanding, 2),
                'pricipal_outstanding' => round($loanInfo->pricipal_outstanding, 2),
                'interest_outstanding' => round($loanInfo->interest_outstanding, 2),
                'total_installment'    => round($loanInfo->total_installment, 2),
                'pricipal_installment' => round($loanInfo->pricipal_installment, 2),
                'interest_installment' => round($loanInfo->interest_installment, 2),
                'total_advance'        => round($loanStatus['advanceAmount'], 2),
                'pricipal_advance'     => round($loanStatus['advanceAmountPrincipal'], 2),
                'interest_advance'     => round(($loanStatus['advanceAmount'] - $loanStatus['advanceAmountPrincipal']), 2),
                'total_due'            => round($loanStatus['dueAmount'], 2),
                'pricipal_due'         => round($loanStatus['dueAmountPrincipal'], 2),
                'interest_due'         => round(($loanStatus['dueAmount'] - $loanStatus['dueAmountPrincipal']), 2),
                'total_rebateable'     => round($loanInfo->interest_outstanding, 2),
                'pricipal_rebateable'  => round(0),
                'interest_rebateable'  => round($loanInfo->interest_outstanding, 2),

                'samityId'             => $loanInfo->samityId,
                'branchId'             => $loanInfo->branchId,
            );
        }

        return response()->json($data);
    }

    public function validationPass($req, $operationType, $adjustment = null)
    {
        $errorMsg = null;

        if ($operationType == 'approve') {

            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

            if ($adjustment->date != $sysDate) {
                $errorMsg = "Unable to Approve. Branch date doesn't mathch!!";
            }

            if (is_null($errorMsg)) {

                $savingDepo = DB::table('mfn_savings_deposit')
                    ->where([
                        ['accountId', $adjustment->accountId],
                        ['memberId', $adjustment->memberId],
                        ['samityId', $adjustment->samityId],
                        ['branchId', $adjustment->branchId],
                    ])
                    ->select(DB::raw('MAX(date) as maxDate'))
                    ->first();

                if ($savingDepo->maxDate > $adjustment->date) {
                    $errorMsg = "Unable to Approve. Already have saving deposit!!";
                }
            }

            if (is_null($errorMsg)) {

                $savingWith = DB::table('mfn_savings_withdraw')
                    ->where([
                        ['accountId', $adjustment->accountId],
                        ['memberId', $adjustment->memberId],
                        ['samityId', $adjustment->samityId],
                        ['branchId', $adjustment->branchId],
                    ])
                    ->select(DB::raw('MAX(date) as maxDate'))
                    ->first();

                if ($savingWith->maxDate > $adjustment->date) {
                    $errorMsg = "Unable to Approve. Already have saving withdraw!!";
                }
            }

            if (is_null($errorMsg)) {

                $loanColln = DB::table('mfn_loan_collections')
                    ->where([
                        ['memberId', $adjustment->memberId],
                        ['samityId', $adjustment->samityId],
                        ['branchId', $adjustment->branchId],
                    ])
                    ->select(DB::raw('MAX(collectionDate) as maxDate'))
                    ->first();

                if ($loanColln->maxDate > $adjustment->date) {
                    $errorMsg = "Unable to Approve. Already have loan collection!!";
                }
            }

            if (is_null($errorMsg)) {

                $trans = DB::table('mfn_member_primary_product_transfers')
                    ->where([
                        ['memberId', $adjustment->memberId],
                        ['branchId', $adjustment->branchId],
                    ])
                    ->select(DB::raw('MAX(transferDate) as maxDate'))
                    ->first();

                if ($trans->maxDate > $adjustment->date) {
                    $errorMsg = "Unable to Approve. Already have primary product transfer!!";
                }
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $pass = array(
            'isValid'  => $isValid,
            'errorMsg' => $errorMsg,
        );

        return $pass;
    }
}
