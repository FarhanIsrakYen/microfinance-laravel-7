<?php

namespace App\Http\Controllers\MFN\Share;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MfnService;
use DB;
use App\Model\MFN\ShareWithdraw;
use Carbon\Carbon;
use Validator;
use App\Services\HrService;

class ShareWithdrawController extends Controller
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

            $data = array(
                'branchces' => $branchces
            );

            return view('MFN.Share.Withdraw.index', $data);
        }

        $columns = [ 'm.name', 'branch.branch_name', 'samity.name', 'withdraw.withdrawDate', 'withdraw.numberOfShare','withdraw.totalPrice', 'emp.emp_name'];

        $limit = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ?0 : (int)$req->input('order.0.column') - 1;
        $order = $columns[$orderColumnIndex];
        $dir = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ?null : $req->input('search.value');

        $withdraws = DB::table('mfn_share_withdraws AS withdraw')
            ->leftJoin('mfn_share_accounts AS sa', 'sa.id', 'withdraw.accountId')
            ->leftJoin('mfn_members AS m', 'm.id', 'withdraw.memberId')
            ->leftJoin('gnl_branchs AS branch', 'branch.id', 'withdraw.branchId')
            ->leftJoin('mfn_samity AS samity', 'samity.id', 'withdraw.samityId')
            ->leftJoin('hr_employees AS emp', 'emp.user_id', 'withdraw.created_by')
            ->whereIn('withdraw.branchId', $accessAbleBranchIds)
            ->where('withdraw.is_delete', 0)

            ->select('m.name AS member', 'branch.branch_name AS branchName', 'samity.name AS samityName','emp.emp_name AS empName', 'withdraw.*')
            ->orderBy($order, $dir);

        if ($search != null) {
            $withdraws->where(function ($query) use ($search) {
                $query->where('m.name', 'LIKE', "%$search%")
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
        if ($req->startDate != '') {
            $startDate = Carbon::parse($req->startDate)->format('Y-m-d');
            $withdraws->where('withdraw.withdrawDate', '>=', $startDate);
        }
        if ($req->endDate != '') {
            $endDate = Carbon::parse($req->endDate)->format('Y-m-d');
            $withdraws->where('withdraw.withdrawDate', '<=', $endDate);
        }

        $totalData = (clone $withdraws)->count();
        $withdraws = $withdraws->limit($limit)->offset($req->start)->get();


        $sl = (int)$req->start + 1;
        foreach ($withdraws as $key => $withdraw) {
            $withdraws[$key]->withdrawDate = Carbon::parse($withdraw->withdrawDate)->format('d-m-Y');
            // $withdraws[$key]->status = $withdraw->isAuthorized == 1 ?'Authorized' : 'Unauthorized';
            $withdraws[$key]->sl = $sl++;
            $withdraws[$key]->id = encrypt($withdraw->id);
        }

        $data = array(
            "draw"              => intval($req->input('draw')),
            "recordsTotal"      => $totalData,
            "recordsFiltered"   => $totalData,
            'data'              => $withdraws,
        );

        return response()->json($data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {

            return $this->store($req);
        }

        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $members =  MfnService::getSelectizeMembers(['branchId' => Auth::user()->branch_id, 'dateTo' => $sysDate]);

        $data = array(
            'sysDate' => $sysDate,
            'members' => $members,
        );

        return view('MFN.Share.Withdraw.add', $data);
    }

    public function store($req)
    {
        // $passport = $this->getPassport($req, $operationType = 'store');
        // if ($passport['isValid'] == false) {
        //     $notification = array(
        //         'message' => $passport['errorMsg'],
        //         'alert-type' => 'error',
        //     );
        //     return response()->json($notification);
        // }

        // store data
        DB::beginTransaction();

        try {

            $ShareAcc = DB::table('mfn_share_accounts')->where('id', $req->accountId)->first();

            if ($req->transactionTypeId == 1) {
                $ledgerId = MfnService::getCashLedgerId();// Cash In hand Ledger Id will be here
                $chequeNo = '';
            } else {
                $ledgerId = $req->ledgerId;
                $chequeNo = $req->chequeNo;
            }

            $withdraw = new ShareWithdraw;
            $withdraw->accountId         = $ShareAcc->id;
            $withdraw->memberId          = $ShareAcc->memberId;
            $withdraw->samityId          = $ShareAcc->samityId;
            $withdraw->branchId          = $ShareAcc->branchId;
            $withdraw->numberOfShare     = $req->numberOfShare;
            $withdraw->unitPrice         = $req->unitPrice;
            $withdraw->totalPrice        = $req->totalPrice;
            $withdraw->withdrawDate      = Carbon::parse($req->date);
            $withdraw->transactionTypeId = $req->transactionTypeId;
            $withdraw->ledgerId          = $ledgerId;
            $withdraw->chequeNo          = $chequeNo;
            $withdraw->created_at        = Carbon::now();
            $withdraw->created_by        = Auth::user()->id;
            $withdraw->save();

            DB::commit();
            $notification = array(
                'message'       => 'Successfully Inserted',
                'alert-type'    => 'success',
                'createId'    => $withdraw->id,
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

        $withdraw = ShareWithdraw::find(decrypt($req->id));

        $member = DB::table('mfn_members')
            ->where('id', $withdraw->memberId)
            ->select(DB::raw("conCAT(name, ' - ', memberCode) AS member"))
            ->value('member');

            $account = DB::table('mfn_share_accounts')
            ->where('memberId', $withdraw->memberId)
            // ->select('id', 'memberId')
            ->first();


        $data = array(
            'withdraw' => $withdraw,
            'member' => $member,
            'account' => $account,
        );

        return view('MFN.Share.Withdraw.edit', $data);
    }

    public function view($id)
    {
      $shareWithdraw = ShareWithdraw::find(decrypt($id));
        if (Auth::user()->branch_id != 1 &&  Auth::user()->branch_id != $shareWithdraw->branchId) {
            return '';
        }
        $member = DB::table('mfn_members')
            ->where('id', $shareWithdraw->memberId)
            ->select(DB::raw("conCAT(name, ' - ', memberCode) AS member"))
            ->first();
        //   $Account = DB::table('mfn_share_accounts')
        //             ->where('id', $shareWithdraw->accountId)
        //             // ->select('accountCode')
        //             ->first();


      $withdrawType = DB::table('mfn_savings_transaction_types')
                  ->where('id', $shareWithdraw->transactionTypeId)
                  ->first()->name;
        $Ledger = DB::table('acc_account_ledger')
                  ->where('id', $shareWithdraw->ledgerId)
                  ->select('name')
                  ->first();
        $entryBy = DB::table('hr_employees')
                  ->where('user_id', $shareWithdraw->created_by)
                  ->select('emp_name')
                  ->first();
      //   if($shareWithdraw->transactionTypeId == 2 ){
      //   $withdrawBy ='Cheque No:'. $shareWithdraw->chequeNo.'-'.;
      // }
      //   else{
      //     $withdrawBy = $withdrawType;
      //   }
        $data = array(
            'member'            => $member,
            // 'Account'         => $Account,
            'shareWithdraw'  => $shareWithdraw,
             'entryBy'           => $entryBy,
             'withdrawType'=> $withdrawType,
             'Ledger'=> $Ledger,

        );

        return view('MFN.Share.Withdraw.view',$data);
    }
    public function update($req)
    {
        $withdraw = ShareWithdraw::find(decrypt($req->id));

        // $passport = $this->getPassport($req, $operationType = 'update', $withdraw);
        // if ($passport['isValid'] == false) {
        //     $notification = array(
        //         'message' => $passport['errorMsg'],
        //         'alert-type' => 'error',
        //     );
        //     return response()->json($notification);
        // }

        // update data
        DB::beginTransaction();

        try {

            if ($req->transactionTypeId == 1) {
                $ledgerId = MfnService::getCashLedgerId();// Cash In hand Ledger Id will be here
                $chequeNo = '';
            } else {
                $ledgerId = $req->ledgerId;
                $chequeNo = $req->chequeNo;
            }


            $withdraw->numberOfShare     = $req->numberOfShare;
            $withdraw->unitPrice         = $req->unitPrice;
            $withdraw->totalPrice        = $req->totalPrice;
            $withdraw->transactionTypeId = $req->transactionTypeId;
            $withdraw->ledgerId          = $ledgerId;
            $withdraw->chequeNo          = $chequeNo;
            $withdraw->updated_at        = Carbon::now();
            $withdraw->updated_by        = Auth::user()->id;
            $withdraw->save();

            DB::commit();
            $notification = array(
                'message'       => 'Successfully Updated',
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

    public function delete(Request $req)
    {
        $withdraw = ShareWithdraw::find(decrypt($req->id));

        // $passport = $this->getPassport($req, $operationType = 'delete', $withdraw);
        // if ($passport['isValid'] == false) {
        //     $notification = array(
        //         'message' => $passport['errorMsg'],
        //         'alert-type' => 'error',
        //     );
        //     return response()->json($notification);
        // }

        // delete data
        DB::beginTransaction();

        try {

            $withdraw->is_delete = 1;
            $withdraw->save();

            DB::commit();
            $notification = array(
                'message'       => 'Successfully Deleted',
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

    public function getPassport($req, $operationType, $withdraw = null)
    {
        $errorMsg = null;

        // set required valiables
        if ($operationType == 'store') {
            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
            $withdrawDate = Carbon::parse($req->date)->format('Y-m-d');
            $account = DB::table('mfn_savings_accounts AS sa')
                ->join('mfn_savings_product AS sp', 'sp.id', 'sa.savingsProductId')
                ->where('sa.id', $req->accountId)
                ->select('sa.*', 'sp.productTypeId')
                ->first();

            $isOpening = MfnService::isOpening(Auth::user()->branch_id);
            $branchId = Auth::user()->branch_id;
        } else {
            $sysDate = MfnService::systemCurrentDate($withdraw->branchId);
            $withdrawDate = $withdraw->date;
            $account = DB::table('mfn_savings_accounts AS sa')
                ->join('mfn_savings_product AS sp', 'sp.id', 'sa.savingsProductId')
                ->where('sa.id', $withdraw->accountId)
                ->select('sa.*', 'sp.productTypeId')
                ->first();

            $isOpening = MfnService::isOpening($withdraw->branchId);
            $branchId = $withdraw->branchId;
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
                if($ledgerId == null){
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
                $errorMsg = 'Branch should be on Software start date '. Carbon::parse($branchSoftwareStartDate)->format('d-m-Y');
            }
        }

        if ($operationType == 'update' || $operationType == 'delete') {
            // this can be updated/deleted from head office and corresponding branch
            if (Auth::user()->branch_id != 1 && Auth::user()->branch_id != $withdraw->branchId) {
                $errorMsg = "This can be updated/deleted from head office and corresponding branch.";
            }

            // if product is one time, then it could not be directly deleted
            if ($account->productTypeId == 2 && !isset($req->reqFromAccount)) {
                $errorMsg = "You can not updated/delete one time product withdraw directly.";
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
            $filters['accountId'] = $account->id;
            $filters['neglectAmount'] = $operationType == 'store' ? $req->amount : $req->amount - $withdraw->amount;
            $balance = mfnService::getSavingsBalance($filters);
            if ($balance < 0) {
                $errorMsg = 'This makes negetive balance';
            }
            $filters['dateTo'] = $withdrawDate;
            $balance = mfnService::getSavingsBalance($filters);
            if ($balance < 0) {
                $errorMsg = 'This makes negetive balance on this date.';
            }
        }

        $isValid = $errorMsg == null ?true : false;

        $passport = array(
            'isValid'   => $isValid,
            'errorMsg'  => $errorMsg
        );

        return $passport;
    }

    public function getData(Request $req)
    {
        if ($req->context == 'member') {
            // $filters['accountType'] = 'regular';
            // $filters['memberId'] = $req->memberId;
           // $savAccounts = MfnService::getSavingsAccounts($filters);
            // $savAccounts = $savAccounts->pluck('accountCode', 'id')->all();
            $account = DB::table('mfn_share_accounts')
            ->where('memberId', $req->memberId)
            // ->select('id', 'memberId')
            ->first();


            $data = array(
                'account' => $account
            );
        }

       

        return response()->json($data);
    }
}
