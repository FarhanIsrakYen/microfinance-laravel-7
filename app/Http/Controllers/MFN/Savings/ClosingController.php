<?php

namespace App\Http\Controllers\MFN\Savings;

use App\Http\Controllers\Controller;
use App\Model\MFN\Member;
use App\Model\MFN\SavingsAccount;
use App\Model\MFN\SavingsClosing;
use App\Model\MFN\SavingsWithdraw;
use App\Services\HrService;
use App\Services\MfnService;
use App\Services\RoleService;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;
use Auth;

class ClosingController extends Controller
{
    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     parent::__construct();
    // }

    public function index(Request $request)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        if ($request->ajax()) {

            $columns = array(
                0 => 'id',
                1 => 'member_name',
                2 => 'member_code',
                3 => 'savings_code',
                4 => 'date',
                5 => 'method',
                6 => 'balance',
                7 => 'action',
            );

            // Datatable Pagination Variable
            $totalData     = SavingsClosing::where('is_delete', '=', 0)->whereIn('branchId', $accessAbleBranchIds)->count();
            $totalFiltered = $totalData;
            $limit         = $request->input('length');
            $start         = $request->input('start');
            $order         = $columns[$request->input('order.0.column')];
            $dir           = $request->input('order.0.dir');

            // Searching variable
            $search        = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $search_branch = $request->filBranch;
            $search_samity = $request->filSamity;
            $search_memberCode = $request->fillMemberCode;
            $search_dateFrom = $search_dateTo = null;
            if ($request->fillDateFrom != null) {
                $search_dateFrom = date('Y-m-d', strtotime($request->fillDateFrom));
            }
            if ($request->fillDateTo != null) {
                $search_dateTo = date('Y-m-d', strtotime($request->fillDateTo));
            }

            $savingsClosings = SavingsClosing::where('mfn_savings_closings.is_delete', 0)
                ->whereIn('mfn_savings_closings.branchId', $accessAbleBranchIds)
                ->select('mfn_savings_closings.*', 'mfn_members.name As M_name', 'mfn_members.memberCode As M_Code', 'mfn_savings_accounts.accountCode As A_Code')
                ->leftJoin('mfn_members', 'mfn_members.id', 'mfn_savings_closings.memberId')
                ->leftJoin('mfn_savings_accounts', 'mfn_savings_accounts.id', 'mfn_savings_closings.accountId')
                ->where(function ($savingsClosings) use ($search, $search_branch, $search_samity, $search_memberCode, $search_dateFrom, $search_dateTo) {
                    if (!empty($search)) {
                        $savingsClosings->where('mfn_members.memberCode', 'LIKE', "%{$search}%");
                    }
                    if (!empty($search_branch)) {
                        $savingsClosings->where('mfn_savings_closings.branchId', '=', $search_branch);
                    }
                    if (!empty($search_samity)) {
                        $savingsClosings->where('mfn_savings_closings.samityId', '=', $search_samity);
                    }
                    if (!empty($search_memberCode)) {
                        $savingsClosings->where('mfn_members.memberCode', 'LIKE', "%{$search_memberCode}%");
                    }
                    if (!empty($search_dateFrom)) {
                        $savingsClosings->where('mfn_savings_closings.closingDate', '>=', $search_dateFrom);
                    }
                    if (!empty($search_dateTo)) {
                        $savingsClosings->where('mfn_savings_closings.closingDate', '<=', $search_dateTo);
                    }
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy($order, $dir)
                ->get();

            if (!empty($search)) {
                $totalFiltered = count($savingsClosings);
            }

            $DataSet = array();
            $slNo       = 1;

            // 0 => 'id',
            // 1 => 'member_name',
            // 2 => 'member_code',
            // 3 => 'savings_code',
            // 4 => 'date',
            // 5 => 'method',
            // 6 => 'balance',
            // 7 => 'action',

            foreach ($savingsClosings as $Row) {

                $TempSet = array();
                $TempSet = [
                    'slNo'         => $slNo++,
                    'id'           => $Row->id,
                    'member_name'  => $Row->M_name,
                    'member_code'  => $Row->M_Code,
                    'savings_code' => $Row->A_Code,
                    'date'         => $Row->closingDate,
                    'balance'      => $Row->closingAmount,
                    // 'action'       => $Row->isFromMemberClosing == 0 ? 0 : $Row->id,
                    'action'      => RoleService::roleWiseArray($this->GlobalRole, $Row->id),
                ];

                $DataSet[] = $TempSet;
            }
            $json_data = array(
                "draw"            => intval($request->input('draw')),
                "recordsTotal"    => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data"            => $DataSet,
            );

            echo json_encode($json_data);
        } else {

            $branchces = DB::table('gnl_branchs')
                ->where([
                    ['is_delete', 0],
                    ['id', '>', 1],
                ])
                ->whereIn('id', $accessAbleBranchIds)
                ->orderBy('branch_code')
                ->select(DB::raw("id, CONCAT(branch_code, ' - ', branch_name) AS name"))
                ->get();
            $samities = DB::table('mfn_samity')
                ->where([
                    ['is_delete', 0],
                ])
                ->orderBy('samityCode')
                ->select(DB::raw("id, CONCAT(samityCode, ' - ', name) AS name"))
                ->get();

            $data = array(
                'branchces' => $branchces,
                'samities'  => $samities,

            );

            return view('MFN.Savings.Closing.index', $data);
        }
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            return $this->store($request);
        } else {
            $memberDetails = Member::where('is_delete', 0)->where('branchId', Auth::user()->branch_id)->get();
            $branchDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
            return view('MFN.Savings.Closing.add', compact('memberDetails', 'branchDate'));
        }
    }

    public function store(Request $request)
    {
        // $passport = $this->getPassport($request, $operationType = 'store');
        // if ($passport['isValid'] == false) {
        //     $notification = array(
        //         'message'    => $passport['errorMsg'],
        //         'alert-type' => 'error',
        //     );
        //     return response()->json($notification);
        // }

        $RequestData = $request->all();
        $acc = SavingsAccount::where('is_delete', 0)->where('id', $RequestData['accountId'])->first();
        $closingDate                = new DateTime($RequestData['closingDate']);
        $RequestData['closingDate'] = $closingDate->format('Y-m-d');

        // validation
        $errorMsg = null;
        // if branch date is not matched then it could not be continued
        $branchDate = MfnService::systemCurrentDate($acc->branchId);
        if ($branchDate != $RequestData['closingDate']) {
            $errorMsg = 'Branch date is not matched.';
        }

        // if any kind of transaction exists of this account after this date
        // then it could not be continued
        $anyTransactionExists = DB::table('mfn_savings_deposit')
            ->where([
                ['is_delete', 0],
                ['amount', '>', 0],
                ['accountId', $acc->id],
                ['date', '>', $branchDate],
            ])
            ->exists();

        if ($anyTransactionExists == false) {
            $anyTransactionExists = DB::table('mfn_savings_withdraw')
                ->where([
                    ['is_delete', 0],
                    ['amount', '>', 0],
                    ['accountId', $acc->id],
                    ['date', '>', $branchDate],
                ])
                ->exists();
        }

        if ($anyTransactionExists) {
            $errorMsg = 'Transaction exists after this date.';
        }

        if ($errorMsg != null) {
            $notification = array(
                'message'    => $errorMsg,
                'alert-type' => 'error',
            );

            return response()->json($notification);

            // return Redirect::to('mfn/savings/closing')->with($notification);
        }

        if ($RequestData['transactionTypeId'] != 2) {
            if ($RequestData['transactionTypeId'] == 1) {
                $RequestData['ledgerId'] = MfnService::getCashLedgerId(); // Cash In hand Ledger Id will be here
                $RequestData['chequeNo'] = '';
            } else {
                $RequestData['ledgerId'] = 0;
                $RequestData['chequeNo'] = '';
            }
        }

        $RequestData['samityId'] = $acc->samityId;
        $RequestData['branchId'] = $acc->branchId;

        // we will chnage the transactionTypeId to savings closing
        $RequestData['transactionTypeId'] = 7; // 7 is for savings closing

        $primaryProductId = DB::table('mfn_members')->where('id', $acc->memberId)->first()->primaryProductId;

        DB::beginTransaction();
        try {
            $savingsClosing = SavingsClosing::create($RequestData);

            $acc->closingDate = $RequestData['closingDate'];

            $acc->update();
            $RequestData['savingsProductId']  = $acc->savingsProductId;
            $RequestData['date']              = $RequestData['closingDate'];
            $RequestData['amount']            = $RequestData['closingAmount'];
            $RequestData['transactionTypeId'] = $RequestData['transactionTypeId'];
            $RequestData['primaryProductId']  = $primaryProductId;

            $withDraw = SavingsWithdraw::create($RequestData);

            $savingsClosing->withdrawId = $withDraw->id;
            $savingsClosing->update();

            MfnService::sendMail('mfn_savings_closings', $savingsClosing->memberId, $savingsClosing->created_at);

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Account Closed',
                'alert-type' => 'success',
            );

            return response()->json($notification);


            // return
        } catch (Exception $e) {
            DB::rollBack();
            $notification = array(
                'message'       => 'Unsuccessful to close Account',
                'alert-type'    => 'error',
                'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
            );
            return response()->json($notification);
            // return redirect()->back()->with($notification);
        }
    }

    public function view($id = null)
    {
        $accClose = SavingsClosing::where('id', $id)->first();
        $member   = DB::table('mfn_members')
            ->where('id', $accClose->memberId)
            ->first();
        $savAccount = DB::table('mfn_savings_accounts')
            ->where('id', $accClose->accountId)
            ->select('accountCode')
            ->first();

        $withdraw     = SavingsWithdraw::where('id', $accClose->withdrawId)->first();
        $withdrawType = DB::table('mfn_savings_transaction_types')
            ->where('id', $withdraw->transactionTypeId)
            ->first()->name;
        $Ledger = DB::table('acc_account_ledger')
            ->where('id', $withdraw->ledgerId)
            ->select('name')
            ->first();
        $entryBy = DB::table('hr_employees')
            ->where('user_id', $withdraw->created_by)
            ->value('emp_name');

        $data = array(
            'accClose'        => $accClose,
            'member'          => $member,
            'savAccount'      => $savAccount,
            'savingsWithdraw' => $withdraw,
            'entryBy'         => $entryBy,
            'withdrawType'    => $withdrawType,
            'Ledger'          => $Ledger,
        );

        return view('MFN.Savings.Closing.view', $data);
    }

    public function delete(Request $request)
    {

        // $accClose = SavingsClosing::where('id', $request->id)->first();
        $accClose = SavingsClosing::find($request->id);

        // validation
        $errorMsg = null;
        // if branch date is not matched then it could not be continued
        $branchDate = MfnService::systemCurrentDate($accClose->branchId);
        if ($branchDate != $accClose->closingDate) {
            $errorMsg = 'Branch date is not matched.';
        }

        if ($errorMsg != null) {
            $notification = array(
                'message'    => $errorMsg,
                'alert-type' => 'error',
            );

            return response()->json($notification);

            // return Redirect::to('mfn/savings/closing')->with($notification);
        }


        DB::beginTransaction();
        try {
            $accClose->is_delete = 1;
            $accClose->save();
            $acc      = SavingsAccount::where('id', $accClose->accountId)->update(['closingDate' => '0000-00-00']);
            $withDraw = SavingsWithdraw::where('id', $accClose->withdrawId)->update(['is_delete' => 1]);

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Deleted',
                'alert-type' => 'success',
            );

            return response()->json($notification);

            // return Redirect::to('mfn/savings/closing')->with($notification);

            // return
        } catch (Exception $e) {
            DB::rollBack();
            $notification = array(
                'message'       => 'Unsuccessful to Delete',
                'alert-type'    => 'error',
                'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
            );
            return response()->json($notification);
            // return redirect()->back()->with($notification);
        }
    }
    public function closingaccountDetails(Request $request)
    {
        $memberId = $request->memberId;
        $value    = $request->accountId;

        $acc = SavingsAccount::where('mfn_savings_accounts.is_delete', 0)->where('memberId', $memberId)->where('closingDate', '0000-00-00')->get();
        if ($value != null) {
            $acc = SavingsAccount::where('mfn_savings_accounts.is_delete', 0)->where('memberId', $memberId)->get();
        }

        $html = '<option> Select Account Code</option>';
        foreach ($acc as $key => $Row) {

            $selectTxt = '';
            if ($value != null) {
                if ($Row->id == $value) {
                    $selectTxt = "selected";
                }
            }

            $filters['accountId'] = $Row->id;

            $html .= '<option value ="' . $Row->id . '" ' . $selectTxt . ' total_balance = "' . MfnService::getSavingsBalance($filters) . '"
                total_withdraw = "' . MfnService::getSavingsWithdraw($filters) . '"
                total_diposite = "' . MfnService::getSavingsDeposit($filters) . '">' . $Row->accountCode . '</option>';
        }

        echo $html;
    }
    public function getData(Request $req)
    {
        if ($req->context == 'member') {
            $filters['accountType'] = 'regular';
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

    public function getPassport($req, $operationType, $savclosing = null)
    {
        $errorMsg = null;

        // set required valiables
        if ($operationType == 'store') {
            #code ...
        } else {
            #code ...
        }

        if ($operationType != 'delete') {
            $rules = array(
                'fieldName' => 'required',
            );

            $validator = Validator::make($req->all(), $rules);

            $attributes = array(
                'fieldName'  => 'attributeName',
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid'  => $isValid,
            'errorMsg' => $errorMsg,
        );

        return $passport;
    }
}
