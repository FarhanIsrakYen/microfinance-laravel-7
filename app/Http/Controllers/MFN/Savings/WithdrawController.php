<?php

namespace App\Http\Controllers\MFN\Savings;

use App\Http\Controllers\Controller;
use App\Model\MFN\SavingsWithdraw;
use App\Services\HrService;
use App\Services\MfnService;
use App\Services\RoleService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class WithdrawController extends Controller
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
                'branchces' => $branchces,
                'samities'      => $samities,
                'loanProducts'  => $loanProducts,
                'savProducts'   => $savProducts,
            );

            return view('MFN.Savings.Withdraw.index', $data);
        }

        $columns = ['sa.accountCode', 'm.name', 'sp.name', 'branch.branch_name', 'samity.name', 'sa.openingDate', 'sa.closingDate', 'emp.emp_name'];

        $limit            = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ?0 : (int)$req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ?null : $req->input('search.value');

        $withdraws = DB::table('mfn_savings_withdraw AS withdraw')
            ->leftJoin('mfn_savings_accounts AS sa', 'sa.id', 'withdraw.accountId')
            ->leftJoin('mfn_savings_product AS sp', 'sp.id', 'withdraw.savingsProductId')
            ->leftJoin('mfn_loan_products AS lp', 'lp.id', 'withdraw.primaryProductId')
            ->leftJoin('mfn_members AS m', 'm.id', 'withdraw.memberId')
            ->leftJoin('gnl_branchs AS branch', 'branch.id', 'withdraw.branchId')
            ->leftJoin('mfn_samity AS samity', 'samity.id', 'withdraw.samityId')
            ->leftJoin('mfn_savings_transaction_types AS tt', 'tt.id', 'withdraw.transactionTypeId')
            ->leftJoin('hr_employees AS emp', 'emp.user_id', 'withdraw.created_by')
            ->whereIn('withdraw.branchId', $accessAbleBranchIds)
            ->where([
                ['withdraw.is_delete', 0],
                ['withdraw.amount', '>', 0],
                ['withdraw.transactionTypeId', '<=', 7],
            ])
            ->select('sa.accountCode', 'sp.name AS savingsProduct', 'lp.name AS loanProduct', 'm.name AS member', 'branch.branch_name AS branchName', 'samity.name AS samityName', 'tt.name AS transactionType', 'emp.emp_name AS empName', 'withdraw.*')
            ->orderBy($order, $dir);

        if ($search != null) {
            $withdraws->where(function ($query) use ($search) {
                $query->where('sa.accountCode', 'LIKE', "%$search%")
                    ->orWhere('m.name', 'LIKE', "%$search%")
                    ->orWhere('branch.branch_name', 'LIKE', "%$search%")
                    ->orWhere('samity.name', 'LIKE', "%$search%");
            });
        }

        if ($req->filBranch != '') {
            $withdraws->where('withdraw.branchId', $req->filBranch);
        }
        if ($req->filSamity != '') {
            $withdraws->where('withdraw.samityId', $req->filSamity);
        }
        if ($req->filPrimaryProduct != '') {
            $withdraws->where('withdraw.primaryProductId', $req->filPrimaryProduct);
        }
        if ($req->filSavProduct != '') {
            $withdraws->where('withdraw.savingsProductId', $req->filSavProduct);
        }
        if ($req->savingsCode != '') {
            $withdraws->where('sa.accountCode', 'LIKE', "%$req->savingsCode%");
        }
        if ($req->startDate != '') {
            $startDate = Carbon::parse($req->startDate)->format('Y-m-d');
            $withdraws->where('withdraw.date', '>=', $startDate);
        }
        if ($req->endDate != '') {
            $endDate = Carbon::parse($req->endDate)->format('Y-m-d');
            $withdraws->where('withdraw.date', '<=', $endDate);
        }

        $totalData = (clone $withdraws)->count();
        $withdraws = $withdraws->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;
        foreach ($withdraws as $key => $withdraw) {
            $withdraws[$key]->date   = Carbon::parse($withdraw->date)->format('d-m-Y');
            $withdraws[$key]->status = $withdraw->isAuthorized == 1 ?'Authorized' : 'Unauthorized';
            $withdraws[$key]->sl     = $sl++;
            $withdraws[$key]->id     = encrypt($withdraw->id);
            $withdraws[$key]->action        = RoleService::roleWiseArray($this->GlobalRole, $withdraws[$key]->id);
        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $withdraws,
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

        return view('MFN.Savings.Withdraw.add', $data);
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

            $withdraw                    = new SavingsWithdraw;
            $withdraw->accountId         = $savAcc->id;
            $withdraw->memberId          = $savAcc->memberId;
            $withdraw->samityId          = $savAcc->samityId;
            $withdraw->branchId          = $savAcc->branchId;
            $withdraw->primaryProductId  = $primaryProductId;
            $withdraw->savingsProductId  = $savAcc->savingsProductId;
            $withdraw->amount            = $req->amount;
            $withdraw->date              = Carbon::parse($req->date);
            $withdraw->transactionTypeId = $req->transactionTypeId;
            $withdraw->ledgerId          = $ledgerId;
            $withdraw->chequeNo          = $chequeNo;
            $withdraw->created_at        = Carbon::now();
            $withdraw->created_by        = Auth::user()->id;
            $withdraw->save();

            MfnService::sendMail('mfn_savings_withdraw', $withdraw->memberId, $withdraw->created_at, $withdraw->amount);

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Inserted',
                'alert-type' => 'success',
                'createId'   => $withdraw->id,
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

        $withdraw = SavingsWithdraw::find(decrypt($req->id));

        $member = DB::table('mfn_members')
            ->where('id', $withdraw->memberId)
            ->select(DB::raw("conCAT(name, ' - ', memberCode) AS member"))
            ->value('member');

        $savAccount = DB::table('mfn_savings_accounts')
            ->where('id', $withdraw->accountId)
            ->value('accountCode');

        $data = array(
            'withdraw'   => $withdraw,
            'member'     => $member,
            'savAccount' => $savAccount,
        );

        return view('MFN.Savings.Withdraw.edit', $data);
    }

    public function view($id)
    {
        $savingsWithdraw = SavingsWithdraw::find(decrypt($id));
        if (Auth::user()->branch_id != 1 && Auth::user()->branch_id != $savingsWithdraw->branchId) {
            return '';
        }
        $member = DB::table('mfn_members')
            ->where('id', $savingsWithdraw->memberId)
            ->select(DB::raw("conCAT(name, ' - ', memberCode) AS member"))
            ->first();
        $savAccount = DB::table('mfn_savings_accounts')
            ->where('id', $savingsWithdraw->accountId)
            ->select('accountCode')
            ->first();

        $withdrawType = DB::table('mfn_savings_transaction_types')
            ->where('id', $savingsWithdraw->transactionTypeId)
            ->first()->name;
        $Ledger = DB::table('acc_account_ledger')
            ->where('id', $savingsWithdraw->ledgerId)
            ->select('name')
            ->first();
        $entryBy = DB::table('hr_employees')
            ->where('user_id', $savingsWithdraw->created_by)
            ->value('emp_name');
        //   if($savingsWithdraw->transactionTypeId == 2 ){
        //   $withdrawBy ='Cheque No:'. $savingsWithdraw->chequeNo.'-'.;
        // }
        //   else{
        //     $withdrawBy = $withdrawType;
        //   }
        $data = array(
            'member'          => $member,
            'savAccount'      => $savAccount,
            'savingsWithdraw' => $savingsWithdraw,
            'entryBy'         => $entryBy,
            'withdrawType'    => $withdrawType,
            'Ledger'          => $Ledger,

        );

        return view('MFN.Savings.Withdraw.view', $data);
    }
    public function update($req)
    {
        $withdraw = SavingsWithdraw::find(decrypt($req->id));

        $passport = $this->getPassport($req, $operationType = 'update', $withdraw);
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

            $withdraw->amount            = $req->amount;
            $withdraw->transactionTypeId = $req->transactionTypeId;
            $withdraw->ledgerId          = $ledgerId;
            $withdraw->chequeNo          = $chequeNo;
            $withdraw->updated_at        = Carbon::now();
            $withdraw->updated_by        = Auth::user()->id;
            $withdraw->save();

            MfnService::sendMail('mfn_savings_withdraw', $withdraw->memberId, $withdraw->updated_at, $withdraw->amount, true);

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
        $withdraw = SavingsWithdraw::find(decrypt($req->id));

        $passport = $this->getPassport($req, $operationType = 'delete', $withdraw);
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

            $withdraw->is_delete = 1;
            $withdraw->save();

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

    public function getPassport($req, $operationType, $withdraw = null)
    {
        $errorMsg = null;

        // set required valiables
        if ($operationType == 'store') {
            $sysDate      = MfnService::systemCurrentDate(Auth::user()->branch_id);
            $withdrawDate = Carbon::parse($req->date)->format('Y-m-d');
            $account      = DB::table('mfn_savings_accounts AS sa')
                ->join('mfn_savings_product AS sp', 'sp.id', 'sa.savingsProductId')
                ->where('sa.id', $req->accountId)
                ->select('sa.*', 'sp.productTypeId')
                ->first();

            $isOpening = MfnService::isOpening(Auth::user()->branch_id);
            $branchId  = Auth::user()->branch_id;
        } else {
            $sysDate      = MfnService::systemCurrentDate($withdraw->branchId);
            $withdrawDate = $withdraw->date;
            $account      = DB::table('mfn_savings_accounts AS sa')
                ->join('mfn_savings_product AS sp', 'sp.id', 'sa.savingsProductId')
                ->where('sa.id', $withdraw->accountId)
                ->select('sa.*', 'sp.productTypeId')
                ->first();

            $isOpening = MfnService::isOpening($withdraw->branchId);
            $branchId  = $withdraw->branchId;
        }

        if ($operationType != 'delete') {

            $rules = array(
                'amount'            => 'required|numeric|min:1',
                'transactionTypeId' => 'required',
            );

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

        if (!$isOpening && $sysDate != $withdrawDate) {
            $errorMsg = "Branch date is not equal to Withdraw date.";
        }

        $branchSoftwareStartDate = DB::table('gnl_branchs')->where('id', $branchId)->value('mfn_start_date');

        // if withdraw is on/before software start date, than it could not be given
        // except opening balane
        if (!isset($req->isOpeningBalance) && $withdrawDate <= $branchSoftwareStartDate) {
            $errorMsg = "Withdraw date could not be on/before software start date.";
        }

        // if it is from opening
        if ($isOpening) {

            if ($sysDate != $branchSoftwareStartDate) {
                $errorMsg = 'Branch should be on Software start date ' . Carbon::parse($branchSoftwareStartDate)->format('d-m-Y');
            }
        }

        if ($operationType == 'update' || $operationType == 'delete') {
            // this this transaction is auhorized then it could not be updated/deleted
            if ($withdraw->isAuthorized) {
                $errorMsg = "This is authorized, you can not update/delete it.";
            }

            // this can be updated/deleted from head office and corresponding branch
            if (Auth::user()->branch_id != 1 && Auth::user()->branch_id != $withdraw->branchId) {
                $errorMsg = "This can be updated/deleted from head office and corresponding branch.";
            }

            // if product is one time, then it could not be directly deleted
            if ($account->productTypeId == 2 && !isset($req->reqFromAccount)) {
                $errorMsg = "You can not updated/delete one time product withdraw directly.";
            }

            // if account is closed,it cloud not be updated/deleted
            if ($account->closingDate != '0000-00-00') {
                $errorMsg = "You can not updated/delete, Account is closed.";
            }
            // if it is from member transfer, then cloud not be deleted
            if ($withdraw->transactionTypeId == 8) {
                $errorMsg = "You can not update/delete it, it is from primary product transfer.";
            }
        }

        if ($errorMsg == null && $operationType == 'store') {

            // if product is regular
            if ($account->productTypeId == 1) {
                // in case of storing data if multiple transaction is not allowed, then stop
                $savConfig = json_decode(DB::table('mfn_config')->where('title', 'savings')->first()->content);

                if ($savConfig->allowMultipleTransaction == 'no') {
                    $isAnyTransactionExistsToday = DB::table('mfn_savings_withdraw')
                        ->where([
                            ['is_delete', 0],
                            ['accountId', $req->accountId],
                            ['date', $withdrawDate],
                        ])
                        ->exists();
                    if ($isAnyTransactionExistsToday) {
                        $errorMsg = "Transaction Exists Today.";
                    }
                }
            }
        }

        if ($errorMsg == null && $operationType != 'delete') {
            // check does it make negetive balance
            $filters['accountId']     = $account->id;
            $filters['neglectAmount'] = $operationType == 'store' ?$req->amount : $req->amount - $withdraw->amount;
            $balance                  = mfnService::getSavingsBalance($filters);
            if ($balance < 0) {
                $errorMsg = 'This makes negetive balance';
            }
            $filters['dateTo'] = $withdrawDate;
            $balance           = mfnService::getSavingsBalance($filters);
            if ($balance < 0) {
                $errorMsg = 'This makes negetive balance on this date.';
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
                ->select('id', 'memberId')
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
            );
        }

        return response()->json($data);
    }
}
