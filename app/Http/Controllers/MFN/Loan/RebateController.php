<?php

namespace App\Http\Controllers\MFN\Loan;

use App\Http\Controllers\Controller;
use App\Model\MFN\Loan;
use App\Model\MFN\LoanCollection;
use App\Model\MFN\Rebates;
use App\Services\RoleService;
use App\Services\HrService;
use App\Services\MfnService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class RebateController extends Controller
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

            return view('MFN.Loan.Rebate.index');
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
            'rebate',
            'paymentType',
            'action',
        ];

        $limit            = $req->length;
        $orderColumnIndex = (int) $req->input('order.0.column') <= 1 ? 0 : (int) $req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $rebateData = DB::table('mfn_loan_rebates as mlr')
            ->where('mlr.is_delete', '=', 0)
            ->select('mlr.id', 'mlr.rebateAmount as rebate', 'mm.name as memberName', 'mm.memberCode as memberCode', 'ml.loanCode as loanCode', 'mlr.rebateDate as date', DB::raw('SUM(mlc.principalAmount) as principalAmount, SUM(mlc.interestAmount) as interestAmount, (SUM(mlc.principalAmount) + SUM(mlc.interestAmount)) as principalWithInterest'))
            ->leftJoin('mfn_loans as ml', 'mlr.loanId', 'ml.id')
            ->leftJoin('mfn_loan_collections as mlc', 'ml.id', 'mlc.loanId')
            ->leftJoin('mfn_members as mm', 'ml.memberId', 'mm.id')
            ->whereIn('mlr.branchId', $accessAbleBranchIds)
            ->where(function ($rebateData) use ($search) {

                if ($search != null || !empty($search)) {
                    $rebateData->where('mm.name', 'LIKE', "%{$search}%")
                        ->orWhere('mm.memberCode', 'LIKE', "%{$search}%");
                }
            })
            ->groupBy('mlr.loanId')
            ->orderBy($order, $dir)
            ->limit($limit)
            ->offset($req->start)
            ->get();

        $totalData = $rebateData->count();
        $sl        = (int) $req->start + 1;

        foreach ($rebateData as $key => $row) {
            $rebateData[$key]->sl                    = $sl++;
            $rebateData[$key]->date                  = Carbon::parse($row->date)->format('d-m-Y');
            $rebateData[$key]->principalAmount       = round($row->principalAmount, 2);
            $rebateData[$key]->interestAmount        = round($row->interestAmount, 2);
            $rebateData[$key]->principalWithInterest = round($row->principalWithInterest, 2);
            $rebateData[$key]->rebate                = round($row->rebate, 2);
            $rebateData[$key]->id                    = encrypt($row->id);
            $rebateData[$key]->action                = RoleService::roleWiseArray($this->GlobalRole, $rebateData[$key]->id);
        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $rebateData,
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

        return view('MFN.Loan.Rebate.add', $data);
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

            $reqData['rebateDate'] = Carbon::parse($req->rebateDate)->format('Y-m-d');
            $isRebateCreate        = Rebates::create($reqData);
            $sysDate               = MfnService::systemCurrentDate(Auth::user()->branch_id);

            //insert collection as a cash
            $cashCollectionData                   = $reqData;
            $cashCollectionData['collectionDate'] = $reqData['rebateDate'];
            $cashCollectionData['amount']         = $reqData['principalAmount'];
            $cashCollectionData['interestAmount'] = floatval($reqData['interestAmount']) - floatval($reqData['rebateAmount']);
            $cashCollectionData['paymentType']    = 'Cash';
            // $isCashColln = LoanCollection::create($cashCollectionData);
            $collectionReq = new Request;
            $collectionReq->merge($cashCollectionData);
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
            $rebateCollectionData                    = $cashCollectionData;
            $rebateCollectionData['principalAmount'] = 0;
            $rebateCollectionData['interestAmount']  = $reqData['rebateAmount'];
            $rebateCollectionData['amount']          = $reqData['rebateAmount'];
            $rebateCollectionData['paymentType']     = 'Rebate';
            // $isLoanColln = LoanCollection::create($rebateCollectionData);

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
            $isRebateCreate->update(['collection_ids' => $collnIds]);

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

    public function edit(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->update($req);
        }

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

        $rebates = DB::table('mfn_loan_rebates as mlr')
            ->where('mlr.id', decrypt($req->id))
            ->select('mlr.*', 'mm.id as memId')
            ->leftJoin('mfn_loans as ml', 'mlr.loanId', 'ml.id')
            ->leftJoin('mfn_members as mm', 'ml.memberId', 'mm.id')
            ->first();

        $data = array(
            'rebateId'     => $rebates->id,
            'loanId'       => $rebates->loanId,
            'samityId'     => $rebates->samityId,
            'branchId'     => $rebates->branchId,
            'rebateDate'   => Carbon::parse($rebates->rebateDate)->format('d-m-Y'),
            'rebateAmount' => $rebates->rebateAmount,
            'note'         => $rebates->note,
            'memberId'     => $rebates->memId,

            'branchs'      => (Auth::user()->branch_id == 1) ? $branchs : Auth::user()->branch_id,
            'samitys'      => $samitys,
        );

        return view('MFN.Loan.Rebate.edit', $data);
    }

    public function update(Request $req)
    {
        DB::beginTransaction();

        try {

            $rebateId = decrypt($req->id);
            $reqData  = $req->all();

            $rebateData            = Rebates::where('id', $rebateId)->first();
            $collnIds              = explode(',', $rebateData->collection_ids);
            $reqData['rebateDate'] = Carbon::parse($req->rebateDate)->format('Y-m-d');
            $rebateData->update($reqData);
            $collnIds = explode(',', $rebateData->collection_ids);

            //update collection as a cash
            $cashCollectionData                   = $reqData;
            $cashCollectionData['id']             = encrypt($collnIds[0]);
            $cashCollectionData['collectionDate'] = $reqData['rebateDate'];
            $cashCollectionData['amount']         = $reqData['principalAmount'];
            $cashCollectionData['interestAmount'] = floatval($reqData['interestAmount']) - floatval($reqData['rebateAmount']);
            $cashCollectionData['paymentType']    = 'Cash';
            // LoanCollection::where('id', $collnIds[0])->first()->update($cashCollectionData);
            $collectionReq = new Request;
            $collectionReq->merge($cashCollectionData);
            $response = app('App\Http\Controllers\MFN\Loan\LoanTransactionController')->update($collectionReq)->getData();

            if ($response->{'alert-type'} == 'error') {
                $notification = array(
                    'message'    => $response->message,
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            //update collection as a rebate
            $rebateCollectionData                    = $cashCollectionData;
            $rebateCollectionData['id']              = encrypt($collnIds[1]);
            $rebateCollectionData['principalAmount'] = 0;
            $rebateCollectionData['interestAmount']  = $reqData['rebateAmount'];
            $rebateCollectionData['amount']          = $reqData['rebateAmount'];
            $rebateCollectionData['paymentType']     = 'Rebate';
            // LoanCollection::where('id', $collnIds[1])->first()->update($rebateCollectionData);
            $collectionReq = new Request;
            $collectionReq->merge($rebateCollectionData);
            $response = app('App\Http\Controllers\MFN\Loan\LoanTransactionController')->update($collectionReq)->getData();

            if ($response->{'alert-type'} == 'error') {
                $notification = array(
                    'message'    => $response->message,
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Updated',
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
        // $rebate = Rebates::where('id', decrypt($req->id))->first();
        $rebate = Rebates::find(decrypt($req->id));
        $pass   = $this->validationPass($req, $operationType = 'delete', $rebate);
        if ($pass['isValid'] == false) {
            $notification = array(
                'message'    => $pass['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {

            LoanCollection::where([['loanId', $rebate->loanId], ['branchId', $rebate->branchId], ['samityId', $rebate->samityId]])->update(['is_delete' => 1]);

            Loan::where([['id', $rebate->loanId], ['branchId', $rebate->branchId], ['samityId', $rebate->samityId], ['loanCompleteDate', '!=', null]])->update(['loanCompleteDate' => null]);

            Rebates::where('id', decrypt($req->id))->update(['is_delete' => 1]);

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
        $rebates = DB::table('mfn_loan_rebates as mlr')
            ->where('mlr.id', decrypt($req->id))
            ->select('mlr.*', 'mm.name as memberName', 'mm.memberCode', 'ml.loanCode', 'ms.samityCode', 'gsu.full_name as entryBy')
            ->leftJoin('mfn_loans as ml', 'mlr.loanId', 'ml.id')
            ->leftJoin('mfn_members as mm', 'ml.memberId', 'mm.id')
            ->leftJoin('mfn_samity as ms', 'mlr.samityId', 'ms.id')
            ->leftJoin('gnl_sys_users as gsu', 'mlr.created_by', 'gsu.id')
            ->first();

        $data = array(
            'memberName'   => $rebates->memberName,
            'memberCode'   => $rebates->memberCode,
            'loanCode'     => $rebates->loanCode,
            'samityCode'   => $rebates->samityCode,
            'rebateDate'   => Carbon::parse($rebates->rebateDate)->format('d-m-Y'),
            'rebateAmount' => $rebates->rebateAmount,
            'entryBy'      => $rebates->entryBy,
            'notes'        => $rebates->note,
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
                ->where(function ($query) use ($req) {

                    if ($req->isEdit == 'yes') {
                        $query->where('id', '<>', $req->rebateId);
                    }
                })
                ->pluck('loanId')
                ->all();

            $writeOffLoanIds = DB::table('mfn_loan_writeoffs')
                ->where('is_delete', 0)
                ->where(function ($query) use ($req) {

                    if ($req->isEdit == 'yes') {
                        $query->where('id', '<>', $req->rebateId);
                    }
                })
                ->pluck('loanId')
                ->all();

            $waiverLoanIds = DB::table('mfn_loan_waivers')
                ->where('is_delete', 0)
                ->where(function ($query) use ($req) {

                    if ($req->isEdit == 'yes') {
                        $query->where('id', '<>', $req->rebateId);
                    }
                })
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

        if ($req->context == 'loanInfoEdit') {

            $rebateId = $req->rebateId;

            $loanInfo = DB::table('mfn_loans as ml')
                ->where('ml.id', $req->loanId)
                ->select('ml.loanAmount as pricipal_payable', 'ml.ineterestAmount as interest_payable', 'ml.actualInstallmentAmount as pricipal_installment', 'ml.extraInstallmentAmount as interest_installment', 'ml.installmentAmount as total_installment', DB::raw("IFNULL(SUM(mlc.principalAmount), 0) as pricipal_trans, IFNULL(SUM(mlc.interestAmount), 0) as interest_trans, (IFNULL(SUM(mlc.principalAmount), 0) + IFNULL(SUM(mlc.interestAmount), 0)) as total_trans, (ml.loanAmount - IFNULL(SUM(mlc.principalAmount), 0)) as pricipal_outstanding, (ml.ineterestAmount - IFNULL(SUM(mlc.interestAmount), 0)) as interest_outstanding, ((ml.loanAmount - IFNULL(SUM(mlc.principalAmount), 0)) + (ml.ineterestAmount - IFNULL(SUM(mlc.interestAmount), 0))) as total_outstanding, IFNULL(IF(ml.loanType = 'Regular', ml.repayAmount, ml.loanAmount), 0) as total_payable"))
                ->leftjoin('mfn_loan_collections as mlc', function ($query) use ($rebateId) {

                    $collnIds = DB::table('mfn_loan_rebates')->where('id', $rebateId)->first();

                    $query->on('ml.id', 'mlc.loanId')
                        ->where('mlc.is_delete', 0)
                        ->whereNotIn('mlc.id', explode(',', $collnIds->collection_ids));
                })
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
            );
        }

        return response()->json($data);
    }

    public function validationPass($req, $operationType, $rebate = null)
    {
        $errorMsg = null;

        if ($operationType == 'store') {

            $rebateDate = Carbon::parse($req->rebateDate)->format('Y-m-d');

            $clln = DB::table('mfn_loan_collections as mlc')
                ->where([['mlc.loanId', $req->loanId], ['mlc.samityId', $req->samityId], ['mlc.branchId', $req->branchId], ['is_delete', 0]])
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

                if (DB::table('mfn_loan_waivers as mlw')->where([['mlw.is_delete', 0], ['mlw.loanId', $req->loanId], ['mlw.samityId', $req->samityId], ['mlw.branchId', $req->branchId]])->exists()) {

                    $errorMsg = 'Unable to rebate. Already exsist in waiver!!';
                }
            }
        }

        if ($operationType == 'delete') {

            if (!in_array(Auth::user()->branch_id, [1, $rebate->branchId])) {
                $errorMsg = "Branch doesn't match!!";
            }

            if (is_null($errorMsg)) {

                $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

                if ($sysDate != $rebate->rebateDate) {
                    $errorMsg = "Branch Date doesn't match!!";
                }
            }

            if (is_null($errorMsg)) {

                $rebateCollnId = explode(',', $rebate->collection_ids);

                $member = DB::table('mfn_loan_collections as mlc')
                    ->where('mlc.id', $rebateCollnId[0])
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
