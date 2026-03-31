<?php

namespace App\Http\Controllers\MFN\Process;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MfnService;
use DB;
use App\Model\MFN\Loan;
use App\Model\MFN\AutoProcess;

use App\Model\MFN\Samity;
use App\Model\MFN\Member;
use App\Model\MFN\SavingsAccount;
use App\Model\MFN\LoanCollection;

use App\Model\MFN\SavingsDeposit;
use App\Model\MFN\SavingsWithdraw;
use Carbon\Carbon;
use DateTime;
use Redirect;
use Validator;
use App\Services\HrService;

class AutoProcessController extends Controller
{
    public function index(Request $req)
    {
        if (!$req->ajax()) {
            return view('MFN.AutoProcess.index');
        }

        $columns = ['name', 'samityCode'];

        $limit            = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching parameter
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $branchDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        // get those samities whose have samity day at this $branchDate
        $samityIds = $this->getSamityIdsForAutoProcess(Auth::user()->branch_id, $branchDate);

        $samities = DB::table('mfn_samity')
            ->where('is_delete', 0)
            ->whereIn('id', $samityIds)
            ->orderBy($order, $dir);;

        if ($search != null) {
            $samities->where(function ($query) use ($search) {
                $query->where('samityCode', 'LIKE', "%$search%")
                    ->orWhere('name', 'LIKE', "%$search%");
            });
        }

        $totalData = (clone $samities)->count();
        $samities  = $samities->limit($limit)->offset($req->start)->get();

        $branchDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $samityFileOfficerEmpIds = MfnService::getSamityFieldOfficerEmpId($samities->pluck('id')->toArray(), $branchDate);

        $employees = DB::table('hr_employees')
            ->whereIn('id', $samityFileOfficerEmpIds)
            ->select(DB::raw("CONCAT(emp_code, ' - ', emp_name) AS name, id"))
            ->get();

        $slNo = 1;
        $autoprocesses = DB::table('mfn_auto_processes')
            ->whereIn('samityId', $samities->pluck('id')->toArray())
            ->where([
                ['date', $branchDate],
                ['isCompleted', 1],
            ])
            ->get();

        foreach ($samities as $key => $samity) {
            $samities[$key]->slNo = $slNo++;
            $samities[$key]->fieldOfficerEmployee = $employees->where('id', $samityFileOfficerEmpIds[$samity->id])->max('name');
            $samities[$key]->isAutoprocessGiven = $autoprocesses->where('samityId', $samity->id)->count() === 0 ? false : true;
            $samities[$key]->autoProcessDay = date('l', strtotime($branchDate));
            $samities[$key]->autoProcessDate = $branchDate;
            $samities[$key]->id = encrypt($samity->id);
        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $samities,
        );

        return response()->json($data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $samityId = decrypt($req->samityId);

        $autoProcessDate = date('Y-m-d', strtotime($req->autoProcessDate));
        // $autoProcessDate = "2021-08-16";

        $members = $this->getMembersOnBranchDateForAutoProcess($samityId, $autoProcessDate);

        $loans = $this->getLoansForAutoProcess($members->pluck('id')->toArray(), $autoProcessDate);

        $savAccounts = $this->getSavingsForAutoProcess($members->pluck('id')->toArray(), $autoProcessDate);

        $data = array(
            'samityId'          => $req->samityId,
            'autoProcessDate'   => $autoProcessDate,
            'members'           => $members,
            'loans'             => $loans,
            'savAccounts'       => $savAccounts,
        );

        return view('MFN.AutoProcess.add', $data);
    }

    public function store($req)
    {
        $passport = $this->getPassport($req, $operationType = 'store');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $samityId = decrypt($req->samityId);
        $autoProcessDate = date('Y-m-d', strtotime($req->autoProcessDate));

        // convert loan array and savings array to empty array if these are null
        $req->loanAccIds = $req->loanAccIds == null ? [] : $req->loanAccIds;
        $req->savAccIds = $req->savAccIds == null ? [] : $req->savAccIds;

        DB::beginTransaction();

        try {
            // make member attendence data
            $memberPresent = array();
            foreach ($req->memIds as $key => $memberId) {
                array_push(
                    $memberPresent,
                    [
                        "ID" => $memberId,
                        "P" => $req->memPresents[$key],
                    ]
                );
            }

            // store loan collectios
            $loans = DB::table('mfn_loans')
                ->whereIn('id', $req->loanAccIds)
                ->select('id', 'memberId', 'samityId', 'branchId')
                ->get();

            $LoanCollectionReq = new Request;
            foreach ($req->loanAccIds as $key => $loanAccId) {
                $loan = $loans->where('id', $loanAccId)->first();

                $LoanCollectionReq->merge([
                    "loanId"            => $loan->id,
                    "memberId"          => $loan->memberId,
                    "samityId"          => $loan->samityId,
                    "branchId"          => $loan->branchId,
                    "collectionDate"    => $autoProcessDate,
                    "amount"            => $req->loanCollectionAmounts[$key],
                    "paymentType"       => 'Cash',
                    "isFromAutoProcess" => 1,
                ]);

                $response = app('App\Http\Controllers\MFN\Loan\LoanTransactionController')->store($LoanCollectionReq)->getData();

                if ($response->{'alert-type'} == 'error') {
                    $notification = array(
                        'message'    => $response->message,
                        'alert-type' => 'error',
                    );
                    return response()->json($notification);
                }
            }

            // store savings deposit
            $savAccounts = DB::table('mfn_savings_accounts')
                ->whereIn('id', $req->savAccIds)
                ->select('id', 'memberId')
                ->get();

            $depositReq = new Request;
            foreach ($req->savAccIds as $key => $savAccId) {
                $savAcc = $savAccounts->where('id', $savAccId)->first();
                $depositReq->merge([
                    "memberId"          => $savAcc->memberId,
                    "accountId"         => $savAcc->id,
                    "date"              => $autoProcessDate,
                    "amount"            => $req->savDepositAmounts[$key],
                    "transactionTypeId" => 1, // 1 for Cash
                    "isFromAutoProcess" => 1,
                ]);

                $response = app('App\Http\Controllers\MFN\Savings\DepositController')->store($depositReq)->getData();

                if ($response->{'alert-type'} == 'error') {
                    $notification = array(
                        'message'    => $response->message,
                        'alert-type' => 'error',
                    );
                    return response()->json($notification);
                }
            }

            // store autoprocess data
            // $autoProcess = new AutoProcess;
            // $autoProcess->samityId          = $samityId;
            // $autoProcess->date              = $autoProcessDate;
            // $autoProcess->presentMembers    = json_encode($memberPresent);
            // $autoProcess->isCompleted       = 1;
            // $autoProcess->created_at        = Carbon::now();
            // $autoProcess->updated_at        = Carbon::now();
            // $autoProcess->created_by        = Auth::user()->id;
            // $autoProcess->save();

            AutoProcess::updateOrCreate(
                ['samityId' => $samityId, 'date' => $autoProcessDate],
                [
                    'presentMembers' => json_encode($memberPresent),
                    'isCompleted' => 1,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                    'created_by' => Auth::user()->id,
                ]
            );

            DB::commit();
            $notification = array(
                'message'       => 'Operation Completed Successfully ',
                'alert-type'    => 'success',
            );

            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong',
                'consoleMsg'    => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage()
            );

            return response()->json($notification);
        }
    }

    public function edit(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->update($req);
        }

        $samityId = decrypt($req->samityId);

        $autoProcessDate = date('Y-m-d', strtotime($req->autoProcessDate));
        // $autoProcessDate = "2021-08-16";

        $members = $this->getMembersOnBranchDateForAutoProcess($samityId, $autoProcessDate);

        $loans = $this->getLoansForAutoProcess($members->pluck('id')->toArray(), $autoProcessDate);

        $savAccounts = $this->getSavingsForAutoProcess($members->pluck('id')->toArray(), $autoProcessDate);

        // get the collection amounts
        $collectionAmounts = DB::table('mfn_loan_collections')
            ->where([
                ['is_delete', 0],
                ['samityId', $samityId],
                ['collectionDate', $autoProcessDate],
                ['isFromAutoProcess', 1],
            ])
            ->whereIn('memberId', $members->pluck('id'))
            ->select('loanId', 'amount')
            ->get();

        // get savings deposit amounts
        $savDepositAmounts = DB::table('mfn_savings_deposit')
            ->where([
                ['is_delete', 0],
                ['samityId', $samityId],
                ['date', $autoProcessDate],
                ['isFromAutoProcess', 1],
            ])
            ->whereIn('memberId', $members->pluck('id'))
            ->select('accountId', 'amount')
            ->get();

        $data = array(
            'samityId'          => $req->samityId,
            'autoProcessDate'   => $autoProcessDate,
            'members'           => $members,
            'loans'             => $loans,
            'savAccounts'       => $savAccounts,
            'collectionAmounts' => $collectionAmounts,
            'savDepositAmounts' => $savDepositAmounts,
        );

        return view('MFN.AutoProcess.edit', $data);
    }

    public function update($req)
    {
        $passport = $this->getPassport($req, $operationType = 'update');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }
        $samityId = decrypt($req->samityId);
        $autoProcessDate = date('Y-m-d', strtotime($req->autoProcessDate));

        // convert loan array and savings array to empty array if these are null
        $req->loanAccIds = $req->loanAccIds == null ? [] : $req->loanAccIds;
        $req->savAccIds = $req->savAccIds == null ? [] : $req->savAccIds;

        DB::beginTransaction();
        try {
            // make member attendence data
            $memberPresent = array();
            foreach ($req->memIds as $key => $memberId) {
                array_push(
                    $memberPresent,
                    [
                        "ID" => $memberId,
                        "P" => $req->memPresents[$key],
                    ]
                );
            }

            // update/store loan collectios
            $loans = DB::table('mfn_loans')
                ->whereIn('id', $req->loanAccIds)
                ->select('id', 'memberId', 'samityId', 'branchId')
                ->get();

            // get the collections
            $loanCollections = DB::table('mfn_loan_collections')
                ->where([
                    ['is_delete', 0],
                    ['samityId', $samityId],
                    ['collectionDate', $autoProcessDate],
                    ['isFromAutoProcess', 1],
                ])
                ->whereIn('loanId', $loans->pluck('id'))
                ->select('id', 'loanId', 'memberId', 'samityId', 'branchId', 'collectionDate', 'amount')
                ->get();

            $LoanCollectionReq = new Request;
            foreach ($req->loanAccIds as $key => $loanAccId) {

                $loanCollection = $loanCollections->where('loanId', $loanAccId)->first();

                // if collection exists
                if ($loanCollection != null) {
                    
                    $LoanCollectionReq->merge([
                        "id"                => encrypt($loanCollection->id),
                        "loanId"            => $loanCollection->loanId,
                        "memberId"          => $loanCollection->memberId,
                        "samityId"          => $loanCollection->samityId,
                        "branchId"          => $loanCollection->branchId,
                        "collectionDate"    => $loanCollection->collectionDate,
                        "amount"            => $req->loanCollectionAmounts[$key],
                        "paymentType"       => 'Cash',
                        "isFromAutoProcess" => 1,
                    ]);
                    $response = app('App\Http\Controllers\MFN\Loan\LoanTransactionController')->update($LoanCollectionReq)->getData();
                } else {
                    $loan = $loans->where('id', $loanAccId)->first();

                    $LoanCollectionReq->merge([
                        "loanId"            => $loan->id,
                        "memberId"          => $loan->memberId,
                        "samityId"          => $loan->samityId,
                        "branchId"          => $loan->branchId,
                        "collectionDate"    => $autoProcessDate,
                        "amount"            => $req->loanCollectionAmounts[$key],
                        "paymentType"       => 'Cash',
                        "isFromAutoProcess" => 1,
                    ]);

                    $response = app('App\Http\Controllers\MFN\Loan\LoanTransactionController')->store($LoanCollectionReq)->getData();
                }

                
                if ($response->{'alert-type'} == 'error') {
                    $notification = array(
                        'message'    => $response->message,
                        'alert-type' => 'error',
                    );
                    return response()->json($notification);
                }
            }

            // store savings deposit
            $savAccounts = DB::table('mfn_savings_accounts')
                ->whereIn('id', $req->savAccIds)
                ->select('id', 'memberId')
                ->get();

            // get savings deposits
            $deposits = DB::table('mfn_savings_deposit')
                ->where([
                    ['is_delete', 0],
                    ['samityId', $samityId],
                    ['date', $autoProcessDate],
                    ['isFromAutoProcess', 1],
                ])
                ->whereIn('accountId', $savAccounts->pluck('id'))
                ->select('id', 'accountId', 'memberId', 'date', 'amount')
                ->get();

            $depositReq = new Request;
            foreach ($req->savAccIds as $key => $savAccId) {
                $accDeposits = $deposits->where('accountId', $savAccId);
                if (count($accDeposits) > 1) {
                    $deposit = $accDeposits->where('amount', '>', 0)->first();
                }
                else{
                    $deposit = $deposits->where('accountId', $savAccId)->first();
                }                

                if ($deposit != null) {
                    $depositReq->merge([
                        "id"                => encrypt($deposit->id),
                        "memberId"          => $deposit->memberId,
                        "accountId"         => $deposit->accountId,
                        "date"              => $deposit->date,
                        "amount"            => $req->savDepositAmounts[$key],
                        "transactionTypeId" => 1,
                        "isFromAutoProcess" => 1,
                    ]);

                    $response = app('App\Http\Controllers\MFN\Savings\DepositController')->update($depositReq)->getData();
                } else {
                    $savAcc = $savAccounts->where('id', $savAccId)->first();
                    $depositReq->merge([
                        "memberId"          => $savAcc->memberId,
                        "accountId"         => $savAcc->id,
                        "date"              => $autoProcessDate,
                        "amount"            => $req->savDepositAmounts[$key],
                        "transactionTypeId" => 1, // 1 for Cash
                        "isFromAutoProcess" => 1,
                    ]);

                    $response = app('App\Http\Controllers\MFN\Savings\DepositController')->store($depositReq)->getData();
                }

                if ($response->{'alert-type'} == 'error') {
                    $notification = array(
                        'message'    => $response->message,
                        'alert-type' => 'error',
                    );
                    return response()->json($notification);
                }
            }

            // store autoprocess data
            $autoProcess = AutoProcess::where('samityId', $samityId)->where('date', $autoProcessDate)->first();
            $autoProcess->presentMembers    = json_encode($memberPresent);
            $autoProcess->isCompleted       = 1;
            $autoProcess->updated_at        = Carbon::now();
            $autoProcess->updated_by        = Auth::user()->id;
            $autoProcess->save();

            DB::commit();
            $notification = array(
                'message'       => 'Operation Completed Successfully ',
                'alert-type'    => 'success',
            );

            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong',
                'consoleMsg'    => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage()
            );

            return response()->json($notification);
        }
    }

    public function getMembersOnBranchDateForAutoProcess($samityId, $autoProcessDate)
    {
        $transferdMemberIds = DB::table('mfn_member_samity_transfers')
            ->where([
                ['is_delete', 0],
            ])
            ->where(function ($query) use ($samityId) {
                $query->where('oldSamityId', $samityId)
                    ->orWhere('newSamityId', $samityId);
            })
            ->pluck('memberId')
            ->toArray();

        $members = DB::table('mfn_members AS member')
            ->join('mfn_member_details AS md', 'md.memberId', 'member.id')
            ->where([
                ['member.is_delete', 0],
                ['member.admissionDate', '<=', $autoProcessDate],
            ])
            ->where(function ($query) use ($samityId, $transferdMemberIds) {
                $query->where('member.samityId', $samityId)
                    ->orWhereIn('member.id', $transferdMemberIds);
            })
            ->where(function ($query) use ($autoProcessDate) {
                $query->where('member.closingDate', '0000-00-00')
                    ->orWhere('member.closingDate', '>', $autoProcessDate);
            })
            ->get();

        $memberSamityTransfers = DB::table('mfn_member_samity_transfers')
            ->where([
                ['is_delete', 0],
                ['date', '>', $autoProcessDate],
            ])
            ->whereIn('memberId', $transferdMemberIds)
            ->orderBy('date')
            ->get()
            ->unique('memberId');

        // modify samity id according to history
        foreach ($memberSamityTransfers as $key => $memberSamityTransfer) {
            $members->where('id', $memberSamityTransfer->memberId)->first()->samityId = $memberSamityTransfer->oldSamityId;
        }

        return $members->where('samityId', $samityId);
    }

    public function getPassport($req, $operationType, $object = null)
    {
        $errorMsg = null;

        if ($req->samityId == null) {
            $errorMsg = 'Samity is null';
        }
        if ($req->autoProcessDate == null) {
            $errorMsg = 'Date is null';
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $passport;

        // set required valiables
        $samityId = decrypt($req->samityId);
        $samity = DB::table('mfn_samity')->where('id', $samityId)->first();
        $branchDate = MfnService::systemCurrentDate($samity->branchId);
        $autoProcessDate = date('Y-m-d', strtotime($req->autoProcessDate));

        if ($operationType != 'delete') {

            $rules = array(
                'savDepositAmounts'  => 'array',
                'savDepositAmounts.*'  => 'required|numeric',
                'loanCollectionAmounts'  => 'array',
                'loanCollectionAmounts.*'  => 'required|numeric',
            );

            $validator = Validator::make($req->all(), $rules);

            $attributes = array(
                'savDepositAmounts'     => 'Savings Deposit Amounts',
                'loanCollectionAmounts' => 'Loan Collection Amounts',
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        // check software date is mached or not
        if ($branchDate != $autoProcessDate) {
            $errorMsg = 'Branch date is not matched!';
        }

        // if operation type is store, then check already any other body has given autoprocess or not
        if ($operationType == 'store') {
            $autoprocessGiven = DB::table('mfn_auto_processes')
                ->where([
                    ['samityId', $samity->id],
                    ['date', $autoProcessDate],
                    ['isCompleted', 1],
                ])
                ->exists();

            if ($autoprocessGiven) {
                $errorMsg = 'Autoprocess is already given!';
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $passport;
    }

    public function getSamityIdsForAutoProcess($branchId, $date)
    {
        $date = date('Y-m-d', strtotime($date));

        $samities = DB::table('mfn_samity')
            ->where([
                ['is_delete', 0],
                ['branchId', $branchId],
                ['openingDate', '<=', $date],
            ])
            ->where(function ($query) use ($date) {
                $query->where('closingDate', '0000-00-00');
                // ->orWhere('closingDate', '>=', $date);
            })
            ->get();

        foreach ($samities as $key => $samity) {
            $samities[$key]->samityDate = MfnService::getSamityDateOfWeek($samity, $date);
        }

        $samities = $samities->where('samityDate', $date);

        // remove empty samities
        $emptySamityIds = [];
        foreach ($samities as $key => $samity) {
            # code...
            $members = $this->getMembersOnBranchDateForAutoProcess($samity->id, $date);
            $loans = $this->getLoansForAutoProcess($members->pluck('id')->toArray(), $date);
            $savings = $this->getSavingsForAutoProcess($members->pluck('id')->toArray(), $date);
            if (count($loans) + count($savings) == 0) {
                array_push($emptySamityIds, $samity->id);
            }
        }

        return $samities->whereNotIn('id', $emptySamityIds)->pluck('id')->toArray();
    }

    public function getLoansForAutoProcess($memberIds, $autoProcessDate)
    {
        // get loan accounts
        $loans = DB::table('mfn_loans')
            ->where([
                ['is_delete', 0],
                ['disbursementDate', '<=', $autoProcessDate],
            ])
            ->whereIn('memberId', $memberIds)
            ->where(function ($query) use ($autoProcessDate) {
                $query->where('loanCompleteDate', '0000-00-00')
                    ->orWhere('loanCompleteDate', null)
                    ->orWhere('loanCompleteDate', '>=', $autoProcessDate);
            })
            ->get();

        // get loan schedules on autoprocess date
        MfnService::resetProperties();
        $schedules = MfnService::generateLoanSchedule($loans->pluck('id')->toArray(), $autoProcessDate, $autoProcessDate);
        $schedules = collect($schedules);

        // today's schedule loans
        $loans = $loans->whereIn('id', $schedules->pluck('loanId')->toArray());

        // get loan status on previos day
        // due advance is calculated base on previos day
        $previosDate = date('Y-m-d', strtotime("-1 day", strtotime($autoProcessDate)));
        MfnService::resetProperties();
        $loanStatus = MfnService::getLoanStatus($loans->pluck('id')->toArray(), $previosDate, $previosDate);
        $loanStatus = collect($loanStatus);

        foreach ($loans as $key => $loan) {
            $thisLoanStatus = $loanStatus->where('loanId', $loan->id)->first();
            $loans[$key]->installmentAmount = $schedules->where('loanId', $loan->id)->first()['installmentAmount'];
            $loans[$key]->dueAmount = $thisLoanStatus['dueAmount'];
            $loans[$key]->advanceAmount = $thisLoanStatus['advanceAmount'];
        }

        return $loans;
    }

    public function getSavingsForAutoProcess($memberIds, $autoProcessDate)
    {
        // get savings accounts
        $savAccounts = DB::table('mfn_savings_accounts AS sa')
            ->join('mfn_savings_product AS sp', 'sp.id', 'sa.savingsProductId')
            ->where([
                ['sa.is_delete', 0],
                ['sa.openingDate', '<=', $autoProcessDate],
                ['sa.closingDate', '0000-00-00'], // Only Active acounts
                ['sp.productTypeId', 1], // Regular Savings Accounts
            ])
            ->whereIn('sa.memberId', $memberIds)
            // ->where(function ($query) use ($autoProcessDate) {
            //     $query->where('sa.closingDate', '0000-00-00')
            //         ->orWhere('sa.closingDate', '>=', $autoProcessDate);
            // })
            ->select('sa.*')
            ->get();

        return $savAccounts;
    }
}
