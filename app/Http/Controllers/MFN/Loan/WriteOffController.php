<?php

namespace App\Http\Controllers\MFN\Loan;

use App\Http\Controllers\Controller;
use App\Model\MFN\Loan;
use App\Model\MFN\LoanCollection;
use App\Model\MFN\WriteOff;
use App\Services\HrService;
use App\Services\MfnService;
use App\Services\RoleService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class WriteOffController extends Controller
{
    public function eligibleList(Request $req)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        if (!$req->ajax()) {

            $branchList = DB::table('gnl_branchs as b')
                ->where([
                    ['is_delete', 0],
                    ['id', '>', 1],
                ])
                ->whereIn('id', $accessAbleBranchIds)
                ->select('b.id', 'b.branch_name', 'b.branch_code', DB::raw("CONCAT(b.branch_code,' - ',b.branch_name) as branch"))
                ->orderBy('branch_code')
                ->get();

            $data = array(
                'branchList' => $branchList,
            );

            return view('MFN.Loan.WriteOff.eligible_list', $data);
        }

        $columns = [
            'loanCode',
            'memberName',
            'memberCode',
            'fOrSName',
            'disburseDate',
            'lastRepayDate',
            'ttlPayableAmt',
            'paidAmt',
            'ttlDueAmt',
            'ttlPrincipalDueAmt',
            'ttlServiceChargeDueAmt',
            'lastTransDate',
            'action',
        ];

        $limit            = $req->length;
        $orderColumnIndex = (int) $req->input('order.0.column') <= 1 ? 0 : (int) $req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable
        $search   = (empty($req->input('search.value'))) ? null : $req->input('search.value');
        $branchId = (empty($req->branchId)) ? '' : $req->branchId;
        $samityId = (empty($req->samityId)) ? '' : $req->samityId;
        $sysDate  = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $rebateLoanIds = DB::table('mfn_loan_rebates')
            ->where('is_delete', 0)
            ->pluck('loanId')
            ->all();

        $writeOffLoanIds = DB::table('mfn_loan_writeoffs')
            ->where('is_delete', 0)
            ->pluck('loanId')
            ->all();

        $waiverLoanIds = DB::table('mfn_loan_waivers')
            ->where('is_delete', 0)
            ->pluck('loanId')
            ->all();

        $alreadyWaiveredLoanIds = array_merge($rebateLoanIds, $writeOffLoanIds, $waiverLoanIds);

        $eligibleList = DB::table('mfn_loans as ml')
            ->where('ml.is_delete', '=', 0)
            ->whereNotIn('ml.id', $alreadyWaiveredLoanIds)
            ->select('ml.id', 'mm.id as memberId', 'ml.samityId as samityId', 'ml.branchId as branchId', 'ml.disbursementDate as disburseDate', 'ml.loanCode', 'mm.name as memberName', 'mm.memberCode', 'ml.repayAmount as ttlPayableAmt', 'ml.lastInastallmentDate as lastRepayDate', 'ml.loanAmount as pricipal_payable', 'ml.ineterestAmount as interest_payable', 'ml.actualInstallmentAmount as pricipal_installment', 'ml.extraInstallmentAmount as interest_installment', 'ml.installmentAmount as total_installment', DB::raw("IFNULL(SUM(mlc.amount), 0) as paidAmt, (ml.repayAmount - IFNULL(SUM(mlc.amount), 0)) as ttlDueAmt, (ml.loanAmount - IFNULL(SUM(mlc.principalAmount), 0)) as ttlPrincipalDueAmt, (ml.ineterestAmount - IFNULL(SUM(mlc.interestAmount), 0)) as ttlServiceChargeDueAmt, MAX(mlc.collectionDate) as lastTransDate, IF(mmd.fatherName IS NULL OR mmd.fatherName = '', mmd.spouseName, mmd.fatherName) as fOrSName, DATE_ADD(ml.lastInastallmentDate, INTERVAL mlp.yearsEligibleWriteOff YEAR) as writeOffDate"))
            ->leftjoin('mfn_members as mm', 'ml.memberId', 'mm.id')
            ->leftjoin('mfn_member_details as mmd', 'ml.memberId', 'mmd.memberId')
            ->leftjoin('mfn_loan_collections as mlc', 'ml.id', 'mlc.id')
            ->leftjoin('mfn_loan_products as mlp', 'ml.productId', 'mlp.id')
            ->where(function ($eligibleList) use ($branchId, $samityId) {

                if (!empty($branchId)) {
                    $eligibleList->where('ml.branchId', $branchId);
                }

                if (!empty($samityId)) {
                    $eligibleList->where('ml.samityId', $samityId);
                }
            })
            ->groupBy('ml.id')
            ->orderBy($order, $dir)
            ->limit($limit)
            ->offset($req->start)
            ->get();

        $totalData = $eligibleList->count();
        $sl        = (int) $req->start + 1;
        $DataSet   = array();

        foreach ($eligibleList as $row) {

            if ($sysDate < $row->writeOffDate) {
                continue;
            }

            $TempSet = array();

            $TempSet = [
                'sl'                     => $sl++,
                'loanCode'               => $row->loanCode,
                'memberName'             => $row->memberName,
                'memberCode'             => $row->memberCode,
                'fOrSName'               => $row->fOrSName,
                'ttlPayableAmt'          => round($row->ttlPayableAmt, 2),
                'paidAmt'                => round($row->paidAmt, 2),
                'ttlDueAmt'              => round($row->ttlDueAmt, 2),
                'ttlPrincipalDueAmt'     => round($row->ttlPrincipalDueAmt, 2),
                'ttlServiceChargeDueAmt' => round($row->ttlServiceChargeDueAmt, 2),
                'disburseDate'           => Carbon::parse($row->disburseDate)->format('d-m-Y'),
                'lastRepayDate'          => Carbon::parse($row->lastRepayDate)->format('d-m-Y'),
                'lastTransDate'          => Carbon::parse($row->lastTransDate)->format('d-m-Y'),
                'id'                     => encrypt($row->id),
                'action'                 => RoleService::roleWiseArray($this->GlobalRole, $row->id),
            ];

            $DataSet[] = $TempSet;
        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $DataSet,
        );

        return response()->json($data);
    }

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

            return view('MFN.Loan.WriteOff.index');
        }

        $columns = [
            'memberName',
            'memberCode',
            'loanCode',
            'date',
            'principalAmount',
            'interestAmount',
            'principalWithInterest',
            'paid',
            'writeOffAmount',
            'action',
        ];

        $limit            = $req->length;
        $orderColumnIndex = (int) $req->input('order.0.column') <= 1 ? 0 : (int) $req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $wirteOffData = DB::table('mfn_loan_writeoffs as mlw')
            ->where('mlw.is_delete', '=', 0)
            ->select('mlw.id', 'mlw.amount as writeOffAmount', 'mm.name as memberName', 'mm.memberCode as memberCode', 'ml.loanCode as loanCode', 'mlw.writeOffDate as date', 'mlw.principalAmount', 'mlw.interestAmount', 'mlw.amount', 'mlw.amount as pi')
            ->leftJoin('mfn_loans as ml', 'mlw.loanId', 'ml.id')
            ->leftJoin('mfn_members as mm', 'ml.memberId', 'mm.id')
            ->whereIn('mlw.branchId', $accessAbleBranchIds)
            ->where(function ($wirteOffData) use ($search) {

                if ($search != null || !empty($search)) {
                    $wirteOffData->Where('mm.name', 'LIKE', "%{$search}%")
                        ->orWhere('mm.memberCode', 'LIKE', "%{$search}%");
                }
            })
            ->orderBy($order, $dir)
            ->limit($limit)
            ->offset($req->start)
            ->get();

        $totalData = $wirteOffData->count();
        $sl        = (int) $req->start + 1;

        foreach ($wirteOffData as $key => $row) {
            $wirteOffData[$key]->sl              = $sl++;
            $wirteOffData[$key]->date            = Carbon::parse($row->date)->format('d-m-Y');
            $wirteOffData[$key]->principalAmount = round($row->principalAmount, 2);
            $wirteOffData[$key]->interestAmount  = round($row->interestAmount, 2);
            $wirteOffData[$key]->amount          = round($row->amount, 2);
            $wirteOffData[$key]->id              = encrypt($row->id);
            $wirteOffData[$key]->action          = RoleService::roleWiseArray($this->GlobalRole, $wirteOffData[$key]->id);
        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $wirteOffData,
        );

        return response()->json($data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $loanId              = decrypt($req->id);
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();
        $sysDate             = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $eligibleData = DB::table('mfn_loans as ml')
            ->where([['ml.is_delete', '=', 0], ['ml.id', $loanId]])
            ->select('ml.id', 'mm.id as memberId', 'mm.name as memberName', 'ml.samityId as samityId', 'ml.branchId as branchId', 'ml.loanCode', 'ml.loanAmount as pricipal_payable', 'ml.ineterestAmount as interest_payable', 'ml.repayAmount as total_payable', 'ml.actualInstallmentAmount as pricipal_installment', 'ml.extraInstallmentAmount as interest_installment', 'ml.installmentAmount as total_installment', DB::raw("IFNULL(SUM(mlc.principalAmount), 0) as pricipal_trans, IFNULL(SUM(mlc.interestAmount), 0) as interest_trans, (IFNULL(SUM(mlc.principalAmount), 0) + IFNULL(SUM(mlc.interestAmount), 0)) as total_trans, (ml.loanAmount - IFNULL(SUM(mlc.principalAmount), 0)) as pricipal_outstanding, (ml.ineterestAmount - IFNULL(SUM(mlc.interestAmount), 0)) as interest_outstanding, ((ml.loanAmount - IFNULL(SUM(mlc.principalAmount), 0)) + (ml.ineterestAmount - IFNULL(SUM(mlc.interestAmount), 0))) as total_outstanding"))
            ->leftjoin('mfn_loan_collections as mlc', function ($query) {
                $query->on('ml.id', 'mlc.loanId')->where('mlc.is_delete', 0);
            })
            ->leftjoin('mfn_members as mm', 'ml.memberId', 'mm.id')
            ->groupBy('ml.id')
            ->first();

        $loanStatus = MfnService::getLoanStatus($loanId)[0];

        $data = array(
            'sysDate'              => $sysDate,
            'memberId'             => $eligibleData->memberId,
            'memberName'           => $eligibleData->memberName,
            'loanId'               => $loanId,
            'loanCode'             => $eligibleData->loanCode,
            'samityId'             => $eligibleData->samityId,
            'branchId'             => $eligibleData->branchId,
            'principalAmount'      => round($eligibleData->pricipal_outstanding, 2),
            'interestAmount'       => round($eligibleData->interest_outstanding, 2),
            'amount'               => round(floatval($eligibleData->total_outstanding), 2),

            'total_payable'        => round($eligibleData->total_payable, 2),
            'pricipal_payable'     => round($eligibleData->pricipal_payable, 2),
            'interest_payable'     => round($eligibleData->interest_payable, 2),

            'total_trans'          => round($eligibleData->total_trans, 2),
            'pricipal_trans'       => round($eligibleData->pricipal_trans, 2),
            'interest_trans'       => round($eligibleData->interest_trans, 2),

            'total_outstanding'    => round($eligibleData->total_outstanding, 2),
            'pricipal_outstanding' => round($eligibleData->pricipal_outstanding, 2),
            'interest_outstanding' => round($eligibleData->interest_outstanding, 2),

            'total_installment'    => round($eligibleData->total_installment, 2),
            'pricipal_installment' => round($eligibleData->pricipal_installment, 2),
            'interest_installment' => round($eligibleData->interest_installment, 2),
            'total_advance'        => round($loanStatus['advanceAmount'], 2),
            'pricipal_advance'     => round($loanStatus['advanceAmountPrincipal'], 2),
            'interest_advance'     => round(($loanStatus['advanceAmount'] - $loanStatus['advanceAmountPrincipal']), 2),
            'total_due'            => round($loanStatus['dueAmount'], 2),
            'pricipal_due'         => round($loanStatus['dueAmountPrincipal'], 2),
            'interest_due'         => round(($loanStatus['dueAmount'] - $loanStatus['dueAmountPrincipal']), 2),
        );

        return view('MFN.Loan.WriteOff.add', $data);
    }

    public function store(Request $req)
    {
        $pass = $this->validationPass($req, $operationType = 'store');
        if ($pass['isValid'] == false) {
            $notification = array(
                'message'    => $pass['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {

            $reqData = $req->all();

            $reqData['writeOffDate'] = Carbon::parse($req->writeOffDate)->format('Y-m-d');
            $isWriteOff              = WriteOff::create($reqData);
            $sysDate                 = MfnService::systemCurrentDate(Auth::user()->branch_id);

            //insert collection as a writeoff
            $writeOffCollectionData                   = $reqData;
            $writeOffCollectionData['collectionDate'] = $reqData['writeOffDate'];
            $writeOffCollectionData['paymentType']    = 'WriteOff';
            // $isCollection = LoanCollection::create($writeOffCollectionData);
            $collectionReq = new Request;
            $collectionReq->merge($writeOffCollectionData);
            $response = app('App\Http\Controllers\MFN\Loan\LoanTransactionController')->store($collectionReq)->getData();

            if ($response->{'alert-type'} == 'error') {
                $notification = array(
                    'message'    => $response->message,
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $isWriteOff->update(['collectionIds' => $response->createId]);

            Loan::where('id', $req->loanId)->update(['loanCompleteDate' => $sysDate]);

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

    public function delete(Request $req)
    {
        $writeOff = WriteOff::find(decrypt($req->id));
        $pass     = $this->validationPass($req, $operationType = 'delete', $writeOff);
        if ($pass['isValid'] == false) {
            $notification = array(
                'message'    => $pass['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {
            // $writeOff = WriteOff::where('id', decrypt($req->id))->first();

            LoanCollection::where([['loanId', $writeOff->loanId], ['branchId', $writeOff->branchId], ['samityId', $writeOff->samityId]])->update(['is_delete' => 1]);

            Loan::where([['id', $writeOff->loanId], ['branchId', $writeOff->branchId], ['samityId', $writeOff->samityId], ['loanCompleteDate', '!=', null]])->update(['loanCompleteDate' => null]);

            WriteOff::where('id', decrypt($req->id))->update(['is_delete' => 1]);

            DB::commit();

        } catch (\Throwable $th) {
            DB::rollback();
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

    public function getData(Request $req)
    {
        if ($req->context == 'samity') {

            $samitys = DB::table('mfn_samity as ms')
                ->where([['is_delete', 0], ['branchId', $req->branchId]])
                ->select('ms.id', 'ms.name', 'ms.samityCode', DB::raw("CONCAT(ms.samityCode, ' - ', ms.name) as samity"))
                ->get();

            $data = array(
                'samitys' => $samitys,
            );

        }

        return response()->json($data);
    }

    public function validationPass($req, $operationType, $writeOff = null)
    {
        $errorMsg = null;

        if ($operationType == 'store') {

            $rebateDate = Carbon::parse($req->rebateDate)->format('Y-m-d');

            $clln = DB::table('mfn_loan_collections as mlc')
                ->where([['mlc.loanId', $req->loanId], ['mlc.samityId', $req->samityId], ['mlc.branchId', $req->branchId]])
                ->select(DB::raw('MAX(collectionDate) as maxCllnDate'))
                ->groupBy('mlc.loanId')
                ->first();

            if (!is_null($clln)) {
                if ($rebateDate < $clln->maxCllnDate) {
                    $errorMsg = 'Unable to rebate!!';
                }
            }

            if (is_null($errorMsg)) {

                if (DB::table('mfn_loan_waivers as mlw')->where([['mlw.is_delete', 0], ['mlw.loanId', $req->loanId], ['mlw.samityId', $req->samityId], ['mlw.branchId', $req->branchId]])->exists()) {

                    $errorMsg = 'Unable to rebate. Already exsist in waiver!!';
                }
            }

            if (is_null($errorMsg)) {

                if (DB::table('mfn_loan_rebates as mlr')->where([['mlr.is_delete', 0], ['mlr.loanId', $req->loanId], ['mlr.samityId', $req->samityId], ['mlr.branchId', $req->branchId]])->exists()) {

                    $errorMsg = 'Unable to rebate. Already exsist in rebate!!';
                }
            }
        }

        if ($operationType == 'delete') {

            if (!in_array(Auth::user()->branch_id, [1, $writeOff->branchId])) {
                $errorMsg = "Branch doesn't match!!";
            }

            if (is_null($errorMsg)) {

                $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

                if ($sysDate != $writeOff->writeOffDate) {
                    $errorMsg = "Branch Date doesn't match!!";
                }
            }

            if (is_null($errorMsg)) {

                $writeOffCollnId = explode(',', $writeOff->collectionIds);

                $member = DB::table('mfn_loan_collections as mlc')
                    ->where('mlc.id', $writeOffCollnId[0])
                    ->select('mm.closingDate')
                    ->leftjoin('mfn_members as mm', 'mlc.memberId', 'mm.id')
                    ->first();

                if ($member->closingDate != '0000-00-00') {
                    $errorMsg = "Can't delete. Member already closed!!";
                }
            }
        }

        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $lastCollectionDate = DB::table('mfn_loan_collections')
            ->where([
                ['loanId', $req->loanId],
                ['samityId', $req->samityId],
                ['branchId', $req->branchId],
                ['is_delete', 0]
            ])
            ->max('collectionDate');

        if ($sysDate < $lastCollectionDate) {
            $errorMsg = 'Sorry, You have transaction!! Try again, after day end.';
        }

        $isValid = $errorMsg == null ? true : false;

        $pass = array(
            'isValid'  => $isValid,
            'errorMsg' => $errorMsg,
        );

        return $pass;
    }
}
