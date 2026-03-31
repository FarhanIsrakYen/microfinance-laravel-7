<?php

namespace App\Http\Controllers\MFN\Loan;

use App\Http\Controllers\Controller;
use App\Model\MFN\LoanCollection;
use App\Model\MFN\WriteOffCollections;
use App\Services\HrService;
use App\Services\MfnService;
use App\Services\RoleService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class WriteOffCollectionController extends Controller
{
    public function list(Request $req) {
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

            return view('MFN.Loan.WriteOffCollection.list');
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

        $limit = $req->length;
        $orderColumnIndex = (int) $req->input('order.0.column') <= 1 ? 0 : (int) $req->input('order.0.column') - 1;
        $order = $columns[$orderColumnIndex];
        $dir = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $wirteOffData = DB::table('mfn_loan_writeoffs as mlw')
            ->where('mlw.is_delete', '=', 0)
            ->select('ml.id', 'mlw.amount as writeOffAmount', 'mm.name as memberName', 'mm.memberCode as memberCode', 'ml.loanCode as loanCode', 'mlw.writeOffDate as date', 'mlw.principalAmount', 'mlw.interestAmount', 'mlw.amount', 'mlw.amount as pi')
            ->leftJoin('mfn_loans as ml', 'mlw.loanId', 'ml.id')
            ->leftJoin('mfn_members as mm', 'ml.memberId', 'mm.id')
            ->whereIn('mlw.branchId', $accessAbleBranchIds)
            ->where(function ($wirteOffData) use ($search) {

                if ($search != null || !empty($search)) {
                    $loanTransactions->Where('mm.name', 'LIKE', "%{$search}%")
                        ->orWhere('mm.memberCode', 'LIKE', "%{$search}%");
                }
            })
            ->orderBy($order, $dir)
            ->limit($limit)
            ->offset($req->start)
            ->get();

        $totalData = $wirteOffData->count();
        $sl = (int) $req->start + 1;

        foreach ($wirteOffData as $key => $row) {
            $wirteOffData[$key]->sl = $sl++;
            $wirteOffData[$key]->date = Carbon::parse($row->date)->format('d-m-Y');
            $wirteOffData[$key]->principalAmount = round($row->principalAmount, 2);
            $wirteOffData[$key]->interestAmount = round($row->interestAmount, 2);
            $wirteOffData[$key]->amount = round($row->amount, 2);
            $wirteOffData[$key]->id = encrypt($row->id);
        }

        $data = array(
            "draw" => intval($req->input('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalData,
            'data' => $wirteOffData,
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

            return view('MFN.Loan.WriteOffCollection.index');
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
            'collectionAmount',
            'entryBy',
            'action',
        ];

        $limit = $req->length;
        $orderColumnIndex = (int) $req->input('order.0.column') <= 1 ? 0 : (int) $req->input('order.0.column') - 1;
        $order = $columns[$orderColumnIndex];
        $dir = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $wirteOffData = DB::table('mfn_loan_writeoff_collections as mlwc')
            ->where('mlwc.is_delete', 0)
            ->select('mlwc.id', 'mlwc.amount as collectionAmount', 'mm.name as memberName', 'mm.memberCode as memberCode', 'ml.loanCode as loanCode', 'mlwc.date', 'ml.loanAmount', 'ml.ineterestAmount', 'gsu.full_name as entryBy', DB::raw('(IFNULL(ml.loanAmount, 0) + IFNULL(ml.ineterestAmount, 0)) as pi'))
            ->leftJoin('mfn_loans as ml', 'mlwc.loanId', 'ml.id')
            ->leftJoin('mfn_members as mm', 'mlwc.memberId', 'mm.id')
            ->leftJoin('gnl_sys_users as gsu', 'mlwc.created_by', 'gsu.id')
            ->whereIn('mlwc.branchId', $accessAbleBranchIds)
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
        $sl = (int) $req->start + 1;

        foreach ($wirteOffData as $key => $row) {
            $wirteOffData[$key]->sl = $sl++;
            $wirteOffData[$key]->date = Carbon::parse($row->date)->format('d-m-Y');
            $wirteOffData[$key]->loanAmount = round($row->loanAmount, 2);
            $wirteOffData[$key]->ineterestAmount = round($row->ineterestAmount, 2);
            $wirteOffData[$key]->collectionAmount = round($row->collectionAmount, 2);
            $wirteOffData[$key]->id = encrypt($row->id);
            $wirteOffData[$key]->action          = RoleService::roleWiseArray($this->GlobalRole, $wirteOffData[$key]->id);
        }

        $data = array(
            "draw" => intval($req->input('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalData,
            'data' => $wirteOffData,
        );

        return response()->json($data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $loanId = decrypt($req->id);
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();
        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $writeOffClln = DB::table('mfn_loans as ml')
            ->where([['ml.is_delete', '=', 0], ['ml.id', $loanId]])
            ->select('ml.id', 'mm.id as memberId', 'mm.name as memberName', 'ml.samityId as samityId', 'ml.branchId as branchId', 'ml.loanCode', 'ml.loanAmount as pricipal_payable', 'ml.ineterestAmount as interest_payable', 'ml.repayAmount as total_payable', 'ml.actualInstallmentAmount as pricipal_installment', 'ml.extraInstallmentAmount as interest_installment', 'ml.installmentAmount as total_installment', DB::raw("SUM(mlc.principalAmount) as pricipal_trans, SUM(mlc.interestAmount) as interest_trans, (IFNULL(SUM(mlc.principalAmount), 0) + IFNULL(SUM(mlc.interestAmount), 0)) as total_trans, (ml.loanAmount - IFNULL(SUM(mlc.principalAmount), 0)) as pricipal_outstanding, (ml.ineterestAmount - IFNULL(SUM(mlc.interestAmount), 0)) as interest_outstanding, ((ml.loanAmount - IFNULL(SUM(mlc.principalAmount), 0)) + (ml.ineterestAmount - IFNULL(SUM(mlc.interestAmount), 0))) as total_outstanding"))
            ->leftjoin('mfn_loan_collections as mlc', 'ml.id', 'mlc.loanId')
            ->leftjoin('mfn_members as mm', 'ml.memberId', 'mm.id')
            ->groupBy('ml.id')
            ->first();

        $data = array(
            'sysDate' => $sysDate,
            'memberId' => $writeOffClln->memberId,
            'memberName' => $writeOffClln->memberName,
            'loanId' => $loanId,
            'loanCode' => $writeOffClln->loanCode,
            'samityId' => $writeOffClln->samityId,
            'branchId' => $writeOffClln->branchId,
            'principalAmount' => round($writeOffClln->pricipal_outstanding, 2),
            'interestAmount' => round($writeOffClln->interest_outstanding, 2),
            'amount' => round(floatval($writeOffClln->total_outstanding), 2),

            'total_payable' => round($writeOffClln->total_payable, 2),
            'pricipal_payable' => round($writeOffClln->pricipal_payable, 2),
            'interest_payable' => round($writeOffClln->interest_payable, 2),

            'total_trans' => round($writeOffClln->total_trans, 2),
            'pricipal_trans' => round($writeOffClln->pricipal_trans, 2),
            'interest_trans' => round($writeOffClln->interest_trans, 2),

            'total_outstanding' => round($writeOffClln->total_outstanding, 2),
            'pricipal_outstanding' => round($writeOffClln->pricipal_outstanding, 2),
            'interest_outstanding' => round($writeOffClln->interest_outstanding, 2),

            'total_installment' => round($writeOffClln->total_installment, 2),
            'pricipal_installment' => round($writeOffClln->pricipal_installment, 2),
            'interest_installment' => round($writeOffClln->interest_installment, 2),
            // 'total_advance' => round($loanInfo->total_advance, 2),
            // 'pricipal_advance' => round($loanInfo->pricipal_advance, 2),
            // 'interest_advance' => round($loanInfo->interest_advance, 2),
            // 'total_due' => round($loanInfo->total_due, 2),
            // 'pricipal_due' => round($loanInfo->pricipal_due, 2),
            // 'interest_due' => round($loanInfo->interest_due, 2),
        );

        return view('MFN.Loan.WriteOffCollection.add', $data);
    }

    public function store(Request $req)
    {
        DB::beginTransaction();
        try {
            $reqData = $req->all();
            $reqData['created_by'] = Auth::user()->id;
            $reqData['date'] = Carbon::parse($req->date)->format('Y-m-d');

            $isWriteOffClln = WriteOffCollections::create($reqData);

            //insert collection as a writeoff collection
            $writeOffCollectionData = $reqData;
            $writeOffCollectionData['collectionDate'] = $reqData['date'];
            $writeOffCollectionData['paymentType'] = 'WriteOffCollection';
            $writeOffCollectionData['transactionType'] = 'Regular';

            $collectionReq = new Request;
            $collectionReq->merge($writeOffCollectionData);
            $response = app('App\Http\Controllers\MFN\Loan\LoanTransactionController')->store($collectionReq)->getData();

            if ($response->{'alert-type'} == 'error') {
                $notification = array(
                    'message' => $response->message,
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $isWriteOffClln->update(['collectionId' => $response->createId]);

            DB::commit();
            $notification = array(
                'message' => 'Successfully Inserted',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message' => 'Something went wrong',
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

        $writeOffClln = DB::table('mfn_loan_writeoff_collections as mlwc')
            ->where([['ml.is_delete', '=', 0], ['mlwc.id', decrypt($req->id)], ['mlc.paymentType', '!=', 'WriteOff']])
            ->select('ml.id', 'mlwc.date', 'mlwc.loanId', 'mlwc.amount as collnAmount', 'mm.id as memberId', 'mm.name as memberName', 'ml.samityId as samityId', 'ml.branchId as branchId', 'ml.loanCode', 'ml.loanAmount as pricipal_payable', 'ml.ineterestAmount as interest_payable', 'ml.repayAmount as total_payable', 'ml.actualInstallmentAmount as pricipal_installment', 'ml.extraInstallmentAmount as interest_installment', 'ml.installmentAmount as total_installment', DB::raw("SUM(mlc.principalAmount) as pricipal_trans, SUM(mlc.interestAmount) as interest_trans, (IFNULL(SUM(mlc.principalAmount), 0) + IFNULL(SUM(mlc.interestAmount), 0)) as total_trans, (ml.loanAmount - IFNULL(SUM(mlc.principalAmount), 0)) as pricipal_outstanding, (ml.ineterestAmount - IFNULL(SUM(mlc.interestAmount), 0)) as interest_outstanding, ((ml.loanAmount - IFNULL(SUM(mlc.principalAmount), 0)) + (ml.ineterestAmount - IFNULL(SUM(mlc.interestAmount), 0))) as total_outstanding"))
            ->leftjoin('mfn_loans as ml', 'mlwc.loanId', 'ml.id')
            ->leftjoin('mfn_loan_collections as mlc', 'mlwc.loanId', 'mlc.loanId')
            ->leftjoin('mfn_members as mm', 'ml.memberId', 'mm.id')
            ->groupBy('mlwc.loanId')
            ->first();

        $data = array(
            'date' => $writeOffClln->date,
            'memberId' => $writeOffClln->memberId,
            'memberName' => $writeOffClln->memberName,
            'loanId' => $writeOffClln->loanId,
            'loanCode' => $writeOffClln->loanCode,
            'samityId' => $writeOffClln->samityId,
            'branchId' => $writeOffClln->branchId,
            'collnAmount' => $writeOffClln->collnAmount,
            'principalAmount' => round($writeOffClln->pricipal_outstanding, 2),
            'interestAmount' => round($writeOffClln->interest_outstanding, 2),
            'amount' => round(floatval($writeOffClln->total_outstanding), 2),

            'total_payable' => round($writeOffClln->total_payable, 2),
            'pricipal_payable' => round($writeOffClln->pricipal_payable, 2),
            'interest_payable' => round($writeOffClln->interest_payable, 2),

            'total_trans' => round($writeOffClln->total_trans, 2),
            'pricipal_trans' => round($writeOffClln->pricipal_trans, 2),
            'interest_trans' => round($writeOffClln->interest_trans, 2),

            'total_outstanding' => round($writeOffClln->total_outstanding, 2),
            'pricipal_outstanding' => round($writeOffClln->pricipal_outstanding, 2),
            'interest_outstanding' => round($writeOffClln->interest_outstanding, 2),

            'total_installment' => round($writeOffClln->total_installment, 2),
            'pricipal_installment' => round($writeOffClln->pricipal_installment, 2),
            'interest_installment' => round($writeOffClln->interest_installment, 2),
            // 'total_advance' => round($loanInfo->total_advance, 2),
            // 'pricipal_advance' => round($loanInfo->pricipal_advance, 2),
            // 'interest_advance' => round($loanInfo->interest_advance, 2),
            // 'total_due' => round($loanInfo->total_due, 2),
            // 'pricipal_due' => round($loanInfo->pricipal_due, 2),
            // 'interest_due' => round($loanInfo->interest_due, 2),
        );

        return view('MFN.Loan.WriteOffCollection.edit', $data);
    }

    public function update(Request $req)
    {
        $writeOffClln = WriteOffCollections::find(decrypt($req->id));

        DB::beginTransaction();
        try {
            $reqData = $req->all();
            $reqData['updated_by'] = Auth::user()->id;
            $reqData['date'] = Carbon::parse($req->date)->format('Y-m-d');

            $writeOffClln->update($reqData);

            //insert collection as a writeoff collection
            $writeOffCollectionData = $reqData;
            $writeOffCollectionData['id'] = encrypt($writeOffClln->collectionId);
            $writeOffCollectionData['collectionDate'] = $reqData['date'];
            $writeOffCollectionData['paymentType'] = 'WriteOffCollection';
            $writeOffCollectionData['transactionType'] = 'Regular';

            $collectionReq = new Request;
            $collectionReq->merge($writeOffCollectionData);
            $response = app('App\Http\Controllers\MFN\Loan\LoanTransactionController')->update($collectionReq)->getData();

            if ($response->{'alert-type'} == 'error') {
                $notification = array(
                    'message' => $response->message,
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            DB::commit();
            $notification = array(
                'message' => 'Successfully Uupdated',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message' => 'Something went wrong',
                'consoleMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );

            return response()->json($notification);
        }
    }

    public function delete(Request $req)
    {
        $writeOffClln = WriteOffCollections::find(decrypt($req->id));

        DB::beginTransaction();
        try {

            LoanCollection::where('id', $writeOffClln->collectionId)->update(['is_delete' => 1]);

            WriteOffCollections::where('id', decrypt($req->id))->update(['is_delete' => 1]);

            DB::commit();
            $notification = array(
                'message' => 'Successfully Deleted',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        } catch (\Exception $e) {

            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message' => 'Something went wrong',
                'consoleMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );

            return response()->json($notification);
        }

    }

    public function view(Request $req)
    {
        $writeOffClln = DB::table('mfn_loan_writeoff_collections as mlwc')
            ->where([['mlwc.is_delete', '=', 0], ['mlwc.id', decrypt($req->id)], ['mlc.paymentType', '!=', 'WriteOff']])
            ->select('mm.name as memberName', 'mm.memberCode', 'ml.loanCode', 'mlwc.date', 'ml.ineterestAmount', 'mlwc.amount', 'gsu.full_name as entryBy', 'mlc.paymentType', DB::raw('IFNULL(ml.loanAmount, 0) as principalAmount, IFNULL(ml.ineterestAmount, 0) as interestAmount, (IFNULL(ml.loanAmount, 0) + IFNULL(ml.ineterestAmount, 0)) as pi'))
            ->leftjoin('mfn_loans as ml', 'mlwc.loanId', 'ml.id')
            ->leftjoin('mfn_loan_collections as mlc', 'mlwc.loanId', 'mlc.loanId')
            ->leftjoin('mfn_members as mm', 'ml.memberId', 'mm.id')
            ->leftJoin('gnl_sys_users as gsu', 'mlwc.created_by', 'gsu.id')
            ->groupBy('mlwc.loanId')
            ->first();

        $data = array(
            'memberName' => $writeOffClln->memberName,
            'memberCode' => $writeOffClln->memberCode,
            'loanCode' => $writeOffClln->loanCode,
            'date' => $writeOffClln->date,
            'principalAmount' => round($writeOffClln->principalAmount, 2),
            'interestAmount' => round($writeOffClln->interestAmount, 2),
            'principalWithInterest' => round($writeOffClln->pi, 2),
            'amount' => round($writeOffClln->amount, 2),
            'paymentType' => $writeOffClln->paymentType,
            'entryBy' => $writeOffClln->entryBy,
        );

        return response()->json($data);
    }
}
