<?php

namespace App\Http\Controllers\MFN\Savings;

use App\Http\Controllers\Controller;
use App\Model\MFN\SavingsDeposit;
use App\Services\HrService;
use App\Services\MfnService;
use App\Services\RoleService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class DepositController extends Controller
{
    public function index(Request $req)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        if (!$req->ajax()) {
            $branchces = DB::table('gnl_branchs')
                ->where([
                    ['is_delete', 0],
                    ['id', '>', 1],
                ])
                ->whereIn('id', $accessAbleBranchIds)
                ->orderBy('branch_code')
                ->select(DB::raw("id, CONCAT(branch_code, ' - ', branch_name) AS name"))
                ->get();

            if (count($branchces) > 1) {
                $samities = [];
            } else {
                $samities = MfnService::getSamities($branchces->pluck('id')->toArray());
            }

            $loanProductIds = MfnService::getBranchAssignedLoanProductIds($branchces->pluck('id')->toArray());
            $savProductIds = MfnService::getBranchAssignedSavProductIds($branchces->pluck('id')->toArray());

            $loanProducts = DB::table('mfn_loan_products')
                ->whereIn('id', $loanProductIds)
                ->where('is_delete', 0)
                ->select('id', 'name', 'productCode')
                ->get();

            $savProducts = DB::table('mfn_savings_product')
                ->whereIn('id', $savProductIds)
                ->where('is_delete', 0)
                ->select('id', 'name', 'productCode')
                ->get();

            $data = array(
                'branchces'     => $branchces,
                'samities'      => $samities,
                'loanProducts'  => $loanProducts,
                'savProducts'   => $savProducts,
            );
            
            return view('MFN.Savings.Deposit.index', $data);
        }

        $columns = ['sa.accountCode', 'm.name', 'sp.name', 'branch.branch_name', 'samity.name', 'sa.openingDate', 'sa.closingDate', 'emp.emp_name'];

        $limit            = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ?0 : (int)$req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ?null : $req->input('search.value');

        $deposits = DB::table('mfn_savings_deposit AS deposit')
            ->leftJoin('mfn_savings_accounts AS sa', 'sa.id', 'deposit.accountId')
            ->leftJoin('mfn_savings_product AS sp', 'sp.id', 'deposit.savingsProductId')
            ->leftJoin('mfn_loan_products AS lp', 'lp.id', 'deposit.primaryProductId')
            ->leftJoin('mfn_members AS m', 'm.id', 'deposit.memberId')
            ->leftJoin('gnl_branchs AS branch', 'branch.id', 'deposit.branchId')
            ->leftJoin('mfn_samity AS samity', 'samity.id', 'deposit.samityId')
            ->leftJoin('mfn_savings_transaction_types AS tt', 'tt.id', 'deposit.transactionTypeId')
            ->leftJoin('hr_employees AS emp', 'emp.user_id', 'deposit.created_by')
            ->whereIn('deposit.branchId', $accessAbleBranchIds)
            ->where([
                ['deposit.is_delete', 0],
                ['deposit.amount', '>', 0],
                ['deposit.transactionTypeId', '<=', 4], // only Cash, Bank, Interest, Opening Balance
            ])
            ->select('sa.accountCode', 'sp.name AS savingsProduct', 'lp.name AS loanProduct', 'm.name AS member', 'branch.branch_name AS branchName', 'samity.name AS samityName', 'tt.name AS transactionType', 'emp.emp_name AS empName', 'deposit.*')
            ->orderBy($order, $dir);

        if ($search != null) {
            $deposits->where(function ($query) use ($search) {
                $query->where('sa.accountCode', 'LIKE', "%$search%")
                    ->orWhere('m.name', 'LIKE', "%$search%")
                    ->orWhere('branch.branch_name', 'LIKE', "%$search%")
                    ->orWhere('samity.name', 'LIKE', "%$search%");
            });
        }

        if ($req->filBranch != '') {
            $deposits->where('deposit.branchId', $req->filBranch);
        }
        if ($req->filSamity != '') {
            $deposits->where('deposit.samityId', $req->filSamity);
        }
        if ($req->filPrimaryProduct != '') {
            $deposits->where('deposit.primaryProductId', $req->filPrimaryProduct);
        }
        if ($req->filSavProduct != '') {
            $deposits->where('deposit.savingsProductId', $req->filSavProduct);
        }
        if ($req->savingsCode != '') {
            $deposits->where('sa.accountCode', 'LIKE', "%$req->savingsCode%");
        }
        if ($req->startDate != '') {
            $startDate = Carbon::parse($req->startDate)->format('Y-m-d');
            $deposits->where('deposit.date', '>=', $startDate);
        }
        if ($req->endDate != '') {
            $endDate = Carbon::parse($req->endDate)->format('Y-m-d');
            $deposits->where('deposit.date', '<=', $endDate);
        }

        $totalData = (clone $deposits)->count();
        $deposits  = $deposits->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;
        foreach ($deposits as $key => $deposit) {
            $deposits[$key]->date               = Carbon::parse($deposit->date)->format('d-m-Y');
            $deposits[$key]->status             = $deposit->isAuthorized == 1 ?'Authorized' : 'Unauthorized';
            $deposits[$key]->sl                 = $sl++;
            $deposits[$key]->id                 = encrypt($deposit->id);
            $deposits[$key]->action             = RoleService::roleWiseArray($this->GlobalRole, $deposits[$key]->id);
        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $deposits,
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
            'sysDate' => $sysDate,
            'members' => $members,
        );

        return view('MFN.Savings.Deposit.add', $data);
    }

    public function store($req)
    {
        $passport = $this->getPassport($req, $operationType = 'store');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        // store data
        DB::beginTransaction();

        try {

            $savAcc           = DB::table('mfn_savings_accounts')->where('id', $req->accountId)->first();
            $primaryProductId = DB::table('mfn_members')->where('id', $savAcc->memberId)->first()->primaryProductId;

            if ($req->transactionTypeId == 1) {

                $ledgerId = MfnService::getCashLedgerId(); // Cash In hand Ledger Id will be here

                $chequeNo = '';
            } else {
                $ledgerId = $req->ledgerId;
                $chequeNo = $req->chequeNo;
            }
            $autoVal = 0;
            if ($req->isFromAutoProcess == 1) {
                $autoVal = 1; // Cash In hand Ledger Id will be here

            } else {
                $autoVal = 0;
            }

            $deposit                    = new SavingsDeposit;
            $deposit->accountId         = $savAcc->id;
            $deposit->memberId          = $savAcc->memberId;
            $deposit->samityId          = $savAcc->samityId;
            $deposit->branchId          = $savAcc->branchId;
            $deposit->primaryProductId  = $primaryProductId;
            $deposit->savingsProductId  = $savAcc->savingsProductId;
            $deposit->amount            = $req->amount;
            $deposit->date              = Carbon::parse($req->date);
            $deposit->transactionTypeId = $req->transactionTypeId;
            $deposit->isFromAutoProcess = $autoVal;
            $deposit->ledgerId          = $ledgerId;
            $deposit->chequeNo          = $chequeNo;
            $deposit->created_at        = Carbon::now();
            $deposit->created_by        = Auth::user()->id;

            $deposit->save();

            MfnService::sendMail('mfn_savings_deposit', $deposit->memberId, $deposit->created_at, $deposit->amount);

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Inserted',
                'alert-type' => 'success',
                'depositId'  => $deposit->id
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

        $deposit = SavingsDeposit::find(decrypt($req->id));

        $member = DB::table('mfn_members')
            ->where('id', $deposit->memberId)
            ->select(DB::raw("conCAT(name, ' - ', memberCode) AS member"))
            ->value('member');

        $savAccount = DB::table('mfn_savings_accounts')
            ->where('id', $deposit->accountId)
            ->value('accountCode');

        $data = array(
            'deposit'    => $deposit,
            'member'     => $member,
            'savAccount' => $savAccount,
        );

        return view('MFN.Savings.Deposit.edit', $data);
    }

    public function view($id)
    {
        $savingsDeposit = SavingsDeposit::find(decrypt($id));
        if (Auth::user()->branch_id != 1 && Auth::user()->branch_id != $savingsDeposit->branchId) {
            return '';
        }
        $member = DB::table('mfn_members')
            ->where('id', $savingsDeposit->memberId)
            ->select(DB::raw("conCAT(name, ' - ', memberCode) AS member"))
            ->first();
        $savAccount = DB::table('mfn_savings_accounts')
            ->where('id', $savingsDeposit->accountId)
            ->select('accountCode')
            ->first();
        $depositType = DB::table('mfn_savings_transaction_types')
            ->where('id', $savingsDeposit->transactionTypeId)
            ->first()->name;
        $Ledger = DB::table('acc_account_ledger')
            ->where('id', $savingsDeposit->ledgerId)
            ->select('name')
            ->first();

        $entryBy = DB::table('hr_employees')
            ->where('user_id', $savingsDeposit->created_by)
            ->value('emp_name');

        $data = array(
            'member'         => $member,
            'savAccount'     => $savAccount,
            'savingsDeposit' => $savingsDeposit,
            'Ledger'         => $Ledger,
            'depositType'    => $depositType,
            'entryBy'        => $entryBy,
        );

        return view('MFN.Savings.Deposit.view', $data);
    }

    public function update($req)
    {
        $deposit = SavingsDeposit::find(decrypt($req->id));

        $passport = $this->getPassport($req, $operationType = 'update', $deposit);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        // update data
        DB::beginTransaction();

        try {

            if ($req->transactionTypeId == 1) {
                $ledgerId = MfnService::getCashLedgerId(); // Cash In hand Ledger Id will be here
                $chequeNo = '';
            } else {
                $ledgerId = $req->ledgerId;
                $chequeNo = $req->chequeNo;
            }

            $isOpening = MfnService::isOpening($deposit->branchId);

            if (isset($req->reqFromAccount) && $isOpening) {
                $deposit->date = date('Y-m-d', strtotime($req->date));
            }

            $deposit->amount            = $req->amount;
            $deposit->transactionTypeId = $req->transactionTypeId;
            $deposit->ledgerId          = $ledgerId;
            $deposit->chequeNo          = $chequeNo;
            $deposit->updated_at        = Carbon::now();
            $deposit->updated_by        = Auth::user()->id;

            if (isset($req->is_delete)) {
                $deposit->is_delete = $req->is_delete;
            }

            $deposit->save();

            MfnService::sendMail('mfn_savings_deposit', $deposit->memberId, $deposit->updated_at, $deposit->amount, true);

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Updated',
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
        $deposit = SavingsDeposit::find(decrypt($req->id));

        $passport = $this->getPassport($req, $operationType = 'delete', $deposit);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        // delete data
        DB::beginTransaction();

        try {

            $deposit->is_delete = 1;
            $deposit->save();

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Deleted',
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

    public function getPassport($req, $operationType, $deposit = null)
    {
        $errorMsg = null;

        // set required valiables
        if ($operationType == 'store') {
            $sysDate     = MfnService::systemCurrentDate(Auth::user()->branch_id);
            $depositDate = Carbon::parse($req->date)->format('Y-m-d');
            $account     = DB::table('mfn_savings_accounts AS sa')
                ->join('mfn_savings_product AS sp', 'sp.id', 'sa.savingsProductId')
                ->where('sa.id', $req->accountId)
                ->select('sa.*', 'sp.productTypeId')
                ->first();

            $isOpening = MfnService::isOpening(Auth::user()->branch_id);
            $branchId  = Auth::user()->branch_id;
        } else {
            $sysDate     = MfnService::systemCurrentDate($deposit->branchId);
            $depositDate = $deposit->date;
            $account = DB::table('mfn_savings_accounts AS sa')
                ->join('mfn_savings_product AS sp', 'sp.id', 'sa.savingsProductId')
                ->where('sa.id', $deposit->accountId)
                ->select('sa.*', 'sp.productTypeId')
                ->first();

            $isOpening = MfnService::isOpening($deposit->branchId);
            if (isset($req->reqFromAccount) && $isOpening) {
                $depositDate = Carbon::parse($req->date)->format('Y-m-d');
            }
            $branchId  = $deposit->branchId;
        }

        if ($operationType != 'delete') {

            if ($req->isFromAutoProcess == 1) {
                $rules = array(
                    'amount'            => 'required|numeric',
                    'transactionTypeId' => 'required',
                );
            } else {
                $rules = array(
                    'amount'            => 'required|numeric|min:1',
                    'transactionTypeId' => 'required',
                );
            }

            if ($operationType == 'store') {
                $rules['accountId'] = 'required';
            }

            if ($req->transactionTypeId == 1) {
                $ledgerId = MfnService::getCashLedgerId();
                if ($ledgerId == null) {
                    $errorMsg = "Branch Ledger is not found or set.";
                }
            }

            if ($req->transactionTypeId == 2) {
                $rules = array_merge($rules, array(
                    'ledgerId' => 'required',
                    'chequeNo' => 'required',
                ));
            }

            $validator = Validator::make($req->all(), $rules);

            $attributes = array(
                'accountId' => 'Account',
                'amount'    => 'Amount',
                'ledgerId'  => 'Bank Account',
                'chequeNo'  => 'Cheque No',
            );

            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        if (!isset($req->reqFromAccount) && ($operationType == 'update' || $operationType == 'delete')) {
            // if it is not Cash or Bank
            if ($deposit->transactionTypeId > 2) {
                $errorMsg = 'This deposit can not ' . $operationType . ' manually';
            }            
        }

        if (!$isOpening && $sysDate != $depositDate) {
            $errorMsg = "Branch date is not equal to Deposit date.";
        }

        // if account is closed,it cloud not be updated/deleted
        if ($account->closingDate != '0000-00-00') {
            $errorMsg = "You can not updated/delete, Account is closed.";
        }

        $branchSoftwareStartDate = DB::table('gnl_branchs')->where('id', $branchId)->value('mfn_start_date');

        // if deposit is on/before software start date, than it could not be given
        // except opening balane
        if ($operationType == 'store' && !isset($req->isOpeningBalance) && $depositDate < $branchSoftwareStartDate) {
            $errorMsg = "Deposit date could not be before software start date.";
        }

        // if it is from opening
        if ($isOpening) {
            if ($sysDate != $branchSoftwareStartDate) {
                $errorMsg = 'Branch should be on Software start date ' . Carbon::parse($branchSoftwareStartDate)->format('d-m-Y');
            }
        }

        if ($operationType == 'update' || $operationType == 'delete') {
            // this this transaction is auhorized then it could not be updated/deleted
            // transactionTypeId = 4 means opening balance
            if ($deposit->isAuthorized && (!isset($req->reqFromAccount) && !$deposit->transactionTypeId=4)) {
                $errorMsg = "Deposit is authorized, you can not update/delete it.";
            }

            // this can be updated/deleted from head office and corresponding branch
            if (Auth::user()->branch_id != 1 && Auth::user()->branch_id != $deposit->branchId) {
                $errorMsg = "This can be updated/deleted from head office and corresponding branch.";
            }

            // if product is one time, then it could not be directly deleted
            if ($account->productTypeId == 2 && !isset($req->reqFromAccount)) {
                $errorMsg = "You can not updated/delete one time product deposit directly.";
            }
        }

        // check any withdraw exists after/today of updating/deleting amount
        if ($errorMsg == null && (($operationType == 'update' && $req->amount < $deposit->amount) || $operationType == 'delete')) {
            $withdrawExists = DB::table('mfn_savings_withdraw')
                ->where([
                    ['is_delete', 0],
                    ['amount', '!=', 0],
                    ['accountId', $deposit->accountId],
                    ['date', '>=', $deposit->date],
                ])
                ->exists();

            if ($withdrawExists) {
                $errorMsg = "You have to give more amount than previos or you have to delete all withdraws today/after this date.";
            }
        }

        if ($errorMsg == null && $operationType == 'store') {

            // if account is one time, then more than one deposit is not allowed.
            if ($operationType == 'store' && $account->productTypeId == 2) {
                $isAnyDepositExists = DB::table('mfn_savings_deposit')
                    ->where([
                        ['is_delete', 0],
                        ['accountId', $req->accountId],
                    ])
                    ->exists();

                if ($isAnyDepositExists) {
                    $errorMsg = "Deposit is allowed only once for One Time Product.";
                }
            }

            // if product is regular
            if ($account->productTypeId == 1) {
                // in case of storing data if multiple transaction is not allowed, then stop
                $savConfig = json_decode(DB::table('mfn_config')->where('title', 'savings')->first()->content);

                if ($savConfig->allowMultipleTransaction == 'no') {
                    $isAnyTransactionExistsToday = DB::table('mfn_savings_deposit')
                        ->where([
                            ['is_delete', 0],
                            ['amount', '!=', 0],
                            ['transactionTypeId', '!=', 8], // Primary product transfer
                            ['accountId', $req->accountId],
                            ['date', $depositDate],
                        ])
                        ->exists();
                    if ($isAnyTransactionExistsToday) {
                        $errorMsg = "Multiple Transaction not permitted, Transaction Exists Today.";
                    }
                }

                // check auto process validation
                if ($savConfig->allowAutoProcess == 'yes') {
                    $isTodayAutoProcessDay = MfnService::isSamityDay($account->samityId, $depositDate);
                    if ($isTodayAutoProcessDay) {
                        if (!isset($req->isFromAutoProcess)) {
                            $errorMsg = "You need to give deposit from auto process.";
                        }
                    }
                }
            }
        }

        $isValid = $errorMsg == null ?true : false;

        $passport = array(
            'isValid'  => $isValid,
            'errorMsg' => $errorMsg,
        );

        return $passport;
    }

    public function getData(Request $req)
    {
        if ($req->context == 'member') {
            $filters['accountType'] = 'regular';
            $filters['onlyActiveAccounts'] = 'yes';
            $filters['memberId']    = $req->memberId;
            $savAccounts            = MfnService::getSavingsAccounts($filters);
            $savAccounts            = $savAccounts->pluck('accountCode', 'id')->all();

            $data = array(
                'savAccounts' => $savAccounts,
            );
        }

        if ($req->context == 'account') {
            $account = DB::table('mfn_savings_accounts')
                ->where('id', $req->accountId)
                ->select('id', 'memberId', 'autoProcessAmount')
                ->first();

            $member = DB::table('mfn_members')
                ->where('id', $account->memberId)
                ->select('id', 'primaryProductId')
                ->first();

            $deposits = DB::table('mfn_savings_deposit')
                ->where([
                    ['is_delete', 0],
                    ['accountId', $account->id],
                    ['primaryProductId', $member->primaryProductId],
                ])
                ->groupBy('isAuthorized')
                ->select(DB::raw('isAuthorized, SUM(amount) as amount'))
                ->get();

            $authorizedDeposit   = $deposits->where('isAuthorized', 1)->sum('amount');
            $unauthorizedDeposit = $deposits->where('isAuthorized', 0)->sum('amount');

            $withdraws = DB::table('mfn_savings_withdraw')
                ->where([
                    ['is_delete', 0],
                    ['accountId', $account->id],
                    ['primaryProductId', $member->primaryProductId],
                ])
                ->groupBy('isAuthorized')
                ->select(DB::raw('isAuthorized, SUM(amount) as amount'))
                ->get();

            $authorizedWithdraw   = $withdraws->where('isAuthorized', 1)->sum('amount');
            $unauthorizedWithdraw = $withdraws->where('isAuthorized', 0)->sum('amount');

            $balance = $deposits->sum('amount') - $withdraws->sum('amount');

            $data = array(
                'authorizedDeposit'    => $authorizedDeposit,
                'unauthorizedDeposit'  => $unauthorizedDeposit,
                'totalDeposit'         => $deposits->sum('amount'),
                'authorizedWithdraw'   => $authorizedWithdraw,
                'unauthorizedWithdraw' => $unauthorizedWithdraw,
                'totalWithdraw'        => $withdraws->sum('amount'),
                'balance'              => $balance,
                'autoProcessAmount'    => (int)$account->autoProcessAmount,
            );
        }

        return response()->json($data);
    }
}
