<?php

namespace App\Http\Controllers\MFN\Loan;

use App\Http\Controllers\Controller;
use App\Model\MFN\Loan;
use App\Model\MFN\LoanCollection;
use App\Model\MFN\LoanWaiver;
use App\Services\RoleService;
use App\Services\HrService;
use App\Services\MfnService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class WaiverController extends Controller
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

            return view('MFN.Loan.Waiver.index');
        }

        $columns = [
            'memberName',
            'memberCode',
            'loanCode',
            'date',
            'principalAmount',
            'interestAmount',
            'amount',
            'waiver',
            'action',
        ];

        $limit            = $req->length;
        $orderColumnIndex = (int) $req->input('order.0.column') <= 1 ? 0 : (int) $req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $waiverData = DB::table('mfn_loan_waivers as mlw')
            ->where('mlw.is_delete', 0)
            ->select('mlw.id', 'mlw.waiverDate as date', 'mlw.principalAmount', 'mlw.interestAmount', 'mlw.amount', 'mlw.amount as waiver', 'mm.name as memberName', 'mm.memberCode as memberCode', 'ml.loanCode as loanCode')
            ->leftJoin('mfn_loans as ml', 'mlw.loanId', 'ml.id')
            ->leftJoin('mfn_members as mm', 'ml.memberId', 'mm.id')
            ->whereIn('mlw.branchId', $accessAbleBranchIds)
            ->where(function ($waiverData) use ($search) {

                if ($search != null || !empty($search)) {
                    $waiverData->where('mm.name', 'LIKE', "%{$search}%")
                        ->orWhere('mm.memberCode', 'LIKE', "%{$search}%");
                }
            })
            ->orderBy($order, $dir)
            ->limit($limit)
            ->offset($req->start)
            ->get();

        $totalData = $waiverData->count();
        $sl        = (int) $req->start + 1;

        foreach ($waiverData as $key => $row) {
            $waiverData[$key]->sl              = $sl++;
            $waiverData[$key]->date            = Carbon::parse($row->date)->format('d-m-Y');
            $waiverData[$key]->principalAmount = round($row->principalAmount, 2);
            $waiverData[$key]->interestAmount  = round($row->interestAmount, 2);
            $waiverData[$key]->amount          = round(floatval($row->principalAmount) + floatval($row->interestAmount), 2);
            $waiverData[$key]->waiver          = round($row->waiver, 2);
            $waiverData[$key]->id              = encrypt($row->id);
            $waiverData[$key]->action          = RoleService::roleWiseArray($this->GlobalRole, $waiverData[$key]->id);
        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $waiverData,
        );

        return response()->json($data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        if (Auth::user()->branch_id == 1) {
            $branchs = DB::table('gnl_branchs as b')
                ->where([['is_delete', 0], ['is_approve', 1], ['is_active', 1], ['id', '>', 1]])
                ->whereIn('id', HrService::getUserAccesableBranchIds())
                ->select('b.id', 'b.branch_name', 'b.branch_code', DB::raw("CONCAT(b.branch_code,' - ',b.branch_name) as branch"))
                ->get();
        }

        $samitys = DB::table('mfn_samity as ms')
            ->where([['is_delete', 0], ['branchId', Auth::user()->branch_id]])
            ->select('ms.id', 'ms.name', 'ms.samityCode', DB::raw("CONCAT(ms.samityCode, ' - ', ms.name) as samity"))
            ->get();

        $data = array(
            'sysDate' => $sysDate,
            'branchs' => (Auth::user()->branch_id == 1) ? $branchs : Auth::user()->branch_id,
            'samitys' => $samitys,
        );

        return view('MFN.Loan.Waiver.add', $data);
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
            $reqData               = $req->all();
            $reqData['created_by'] = Auth::user()->id;
            $collnIds              = array();

            $reqData['waiverDate'] = Carbon::parse($req->waiverDate)->format('Y-m-d');
            $isWaiverCreate        = LoanWaiver::create($reqData);
            $sysDate               = MfnService::systemCurrentDate(Auth::user()->branch_id);

            if ($req->isWithServiceCharge == 1) {

                //insert collection
                $collectionData                   = $reqData;
                $collectionData['collectionDate'] = $reqData['waiverDate'];
                $collectionData['paymentType']    = 'Waiver';
                // $isLoanCreate = LoanCollection::create($collectionData);
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

                $isWaiverCreate->update(['collectionIds' => $response->createId]);
            } else {

                //insert collection as a waiver
                $collectionData                   = $reqData;
                $collectionData['collectionDate'] = $reqData['waiverDate'];
                $collectionData['amount']         = $reqData['principalAmount'];
                $collectionData['interestAmount'] = 0;
                $collectionData['paymentType']    = 'Waiver';
                // $isLoanCreate = LoanCollection::create($collectionData);
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
                array_push($collnIds, $response->createId);

                //insert collection as a rebate
                $rebateCollectionData                    = $reqData;
                $rebateCollectionData['collectionDate']  = $reqData['waiverDate'];
                $rebateCollectionData['principalAmount'] = 0;
                $rebateCollectionData['amount']          = $reqData['interestAmount'];
                $rebateCollectionData['paymentType']     = 'Rebate';
                // $isLoanRColln = LoanCollection::create($rebateCollectionData);
                $collectionReq = new Request;
                $collectionReq->merge($rebateCollectionData);
                $response = app('App\Http\Controllers\MFN\Loan\LoanTransactionController')->store($collectionReq)->getData();

                if ($response->{'alert-type'} == 'error') {
                    $notification = array(
                        'message'    => $response->message,
                        'alert-type' => 'error',
                    );
                    return response()->json($notification);
                }
                array_push($collnIds, $response->createId);

                $collnIds = implode(',', $collnIds);
                $isWaiverCreate->update(['collectionIds' => $collnIds]);
            }

            Loan::where('id', $req->loanId)->update(['loanCompleteDate' => $sysDate]);

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Inserted',
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
    }

    public function delete(Request $req)
    {
        $waiver = LoanWaiver::find(decrypt($req->id));
        $pass   = $this->validationPass($req, $operationType = 'delete', $waiver);
        if ($pass['isValid'] == false) {
            $notification = array(
                'message'    => $pass['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {

            LoanCollection::where([['loanId', $waiver->loanId], ['branchId', $waiver->branchId], ['samityId', $waiver->samityId]])->update(['is_delete' => 1]);

            Loan::where([['id', $waiver->loanId], ['branchId', $waiver->branchId], ['samityId', $waiver->samityId], ['loanCompleteDate', '!=', null]])->update(['loanCompleteDate' => null]);

            LoanWaiver::where('id', decrypt($req->id))->update(['is_delete' => 1]);

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Deleted',
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
    }

    public function view(Request $req)
    {
        $waiver = DB::table('mfn_loan_waivers as mlw')
            ->where('mlw.id', decrypt($req->id))
            ->select('mlw.*', 'mm.name as memberName', 'mm.memberCode', 'ml.loanCode', 'ms.samityCode', 'gsu.full_name as entryBy')
            ->leftJoin('mfn_loans as ml', 'mlw.loanId', 'ml.id')
            ->leftJoin('mfn_members as mm', 'ml.memberId', 'mm.id')
            ->leftJoin('mfn_samity as ms', 'mlw.samityId', 'ms.id')
            ->leftJoin('gnl_sys_users as gsu', 'mlw.created_by', 'gsu.id')
            ->first();

        $data = array(
            'memberName'   => $waiver->memberName,
            'memberCode'   => $waiver->memberCode,
            'loanCode'     => $waiver->loanCode,
            'samityCode'   => $waiver->samityCode,
            'waiverDate'   => Carbon::parse($waiver->waiverDate)->format('d-m-Y'),
            'waiverAmount' => round($waiver->amount, 2),
            'entryBy'      => $waiver->entryBy,
            'notes'        => $waiver->note,
        );

        return response()->json($data);
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

        if ($req->context == 'member') {

            if (Auth::user()->branch_id == 1) {
                $members = DB::table('mfn_members as m')
                    ->where([['m.is_delete', 0], ['m.samityId', $req->samityId]])
                    ->select('m.id', 'm.name', 'm.memberCode', DB::raw("CONCAT(m.name, ' - ', m.memberCode) as member"))
                    ->get();
            } else {

                $members = DB::table('mfn_members as m')
                    ->where([['m.is_delete', 0], ['m.samityId', $req->samityId],
                        ['m.branchId', Auth::user()->branch_id]])
                    ->select('m.id', 'm.name', 'm.memberCode', DB::raw("CONCAT(m.name, ' - ', m.memberCode) as member"))
                    ->get();
            }

            $data = array(
                'members' => $members,
            );

        }

        if ($req->context == 'loan') {

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

            $filters['memberId'] = $req->memberId;
            $filters['status']   = 'Living';
            $loans               = MfnService::getLoanAccounts($filters);
            $loans               = $loans
                ->where('loanType', '!=', 'Onetime')
                ->whereNotIn('id', $alreadyWaiveredLoanIds)
                ->pluck('loanCode', 'id')
                ->all();

            $data = array(
                'loans' => $loans,
            );
        }

        if ($req->context == 'loanInfo') {

            $loanInfo = DB::table('mfn_loans as ml')
                ->where([['ml.id', $req->loanId]])
                ->select('ml.actualInstallmentAmount as pricipal_installment', 'ml.extraInstallmentAmount as interest_installment', 'ml.installmentAmount as total_installment')
                ->first();

            $loanStatus = MfnService::getLoanStatus($req->loanId)[0];

            $interestOutstanding = $loanStatus['outstanding'] - $loanStatus['outstandingPrincipal'];

            $data = array(
                'total_payable'        => round($loanStatus['payableAmount'], 2),
                'pricipal_payable'     => round($loanStatus['payableAmountPrincipal'], 2),
                'interest_payable'     => round($loanStatus['payableAmount'] - $loanStatus['payableAmountPrincipal'], 2),

                'total_trans'          => round($loanStatus['paidAmount'], 2),
                'pricipal_trans'       => round($loanStatus['paidAmountPrincipal'], 2),
                'interest_trans'       => round($loanStatus['paidAmount'] - $loanStatus['paidAmountPrincipal'], 2),

                'total_outstanding'    => round($loanStatus['outstanding'], 2),
                'pricipal_outstanding' => round($loanStatus['outstandingPrincipal'], 2),
                'interest_outstanding' => round($interestOutstanding, 2),

                'total_installment'    => round($loanInfo->total_installment, 2),
                'pricipal_installment' => round($loanInfo->pricipal_installment, 2),
                'interest_installment' => round($loanInfo->interest_installment, 2),

                'total_advance'        => round($loanStatus['advanceAmount'], 2),
                'pricipal_advance'     => round($loanStatus['advanceAmountPrincipal'], 2),
                'interest_advance'     => round(($loanStatus['advanceAmount'] - $loanStatus['advanceAmountPrincipal']), 2),

                'total_due'            => round($loanStatus['dueAmount'], 2),
                'pricipal_due'         => round($loanStatus['dueAmountPrincipal'], 2),
                'interest_due'         => round(($loanStatus['dueAmount'] - $loanStatus['dueAmountPrincipal']), 2),

                'total_rebateable'     => round($interestOutstanding, 2),
                'pricipal_rebateable'  => round(0),
                'interest_rebateable'  => round($interestOutstanding, 2),
            );
        }

        return response()->json($data);
    }

    public function validationPass($req, $operationType, $waiver = null)
    {
        $errorMsg = null;

        if ($operationType == 'store') {

            $rebateDate = Carbon::parse($req->rebateDate)->format('Y-m-d');

            $clln = DB::table('mfn_loan_collections as mlc')
                ->where([['mlc.loanId', $req->loanId], ['mlc.samityId', $req->samityId], ['mlc.branchId', $req->branchId], ['mlc.is_delete', 0]])
                ->select(DB::raw('MAX(collectionDate) as maxCllnDate'))
                ->groupBy('mlc.loanId')
                ->first();

            if (!is_null($clln)) {
                if ($rebateDate < $clln->maxCllnDate) {
                    $errorMsg = 'Unable to rebate!!';
                }
            }

            if (is_null($errorMsg)) {

                if (DB::table('mfn_loan_writeoffs as mlw')->where([['mlw.is_delete', 0], ['mlw.loanId', $req->loanId], ['mlw.samityId', $req->samityId], ['mlw.branchId', $req->branchId]])->exists()) {

                    $errorMsg = 'Unable to rebate. Already exsist in write off!!';
                }
            }

            if (is_null($errorMsg)) {

                if (DB::table('mfn_loan_rebates as mlr')->where([['mlr.is_delete', 0], ['mlr.loanId', $req->loanId], ['mlr.samityId', $req->samityId], ['mlr.branchId', $req->branchId]])->exists()) {

                    $errorMsg = 'Unable to rebate. Already exsist in rebate!!';
                }
            }
        }

        if ($operationType == 'delete') {

            if (!in_array(Auth::user()->branch_id, [1, $waiver->branchId])) {
                $errorMsg = "Branch doesn't match!!";
            }

            if (is_null($errorMsg)) {

                $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

                if ($sysDate != $waiver->waiverDate) {
                    $errorMsg = "Branch Date doesn't match!!";
                }
            }

            if (is_null($errorMsg)) {

                $waiverCollnId = explode(',', $waiver->collectionIds);

                $member = DB::table('mfn_loan_collections as mlc')
                    ->where('mlc.id', $waiverCollnId[0])
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
                ['is_delete', 0],
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
