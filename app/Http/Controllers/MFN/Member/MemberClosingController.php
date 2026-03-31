<?php

namespace App\Http\Controllers\MFN\Member;

use App\Http\Controllers\Controller;
use App\Model\MFN\Member;
use App\Model\MFN\MemberClosing;
use App\Model\MFN\SavingsAccount;
use App\Model\MFN\SavingsClosing;
use App\Model\MFN\SavingsWithdraw;
use App\Services\HrService;
use App\Services\MfnService;
use App\Services\RoleService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MemberClosingController extends Controller
{
    public function index(Request $req)
    {

        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        if (!$req->ajax()) {

            $branchList = DB::table('gnl_branchs')
                ->where([
                    ['is_delete', 0], ['is_active', 1], ['is_approve', 1],
                    ['id', '>', 1],
                ])
                ->whereIn('id', $accessAbleBranchIds)
                ->orderBy('branch_code')
                ->select('id', 'branch_name')
                ->get();
            $data = array(
                'branchList' => $branchList,
            );

            return view('MFN.MemberClosing.index', $data);
        }

        $columns          = ['mfn_members.name', 'mfn_members.memberCode', 'gnl_branchs.branch_name', 'mfn_samity.name', 'mclose.closingDate'];
        $limit            = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable

        $search              = (empty($req->input('search.value'))) ? null : $req->input('search.value');
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        $memberClosings = DB::table('mfn_member_closings AS mclose')
            ->leftJoin('mfn_members', 'mfn_members.id', 'mclose.memberId')
            ->leftJoin('gnl_branchs', 'gnl_branchs.id', 'mclose.branchId')
            ->leftJoin('mfn_samity', 'mfn_samity.id', 'mclose.samityId')
            ->whereIn('mclose.branchId', $accessAbleBranchIds)
            ->where('mclose.is_delete', 0)
            ->select(
                'mfn_members.name AS memberName',
                'mfn_members.memberCode AS memberCode',
                'mfn_samity.name AS samity',
                'mclose.*',
                DB::raw('CONCAT(gnl_branchs.branch_code, " - ", gnl_branchs.branch_name) AS branchName')
            )
            ->orderBy($order, $dir);

        if ($search != null) {
            $memberClosings->where(function ($query) use ($search) {
                $query->Where('mfn_members.name', 'LIKE', "%{$search}%")
                    ->orWhere('mfn_members.memberCode', 'LIKE', "%{$search}%")
                    ->orWhere('mfn_samity.name', 'LIKE', "%{$search}%")
                    ->orWhere('mfn_samity.samityCode', 'LIKE', "%{$search}%")
                    ->orWhere('gnl_branchs.branch_name', 'LIKE', "%{$search}%");
            });
        }

        $totalData = (clone $memberClosings)->count();
        $memberClosings = $memberClosings->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;

        foreach ($memberClosings as $key => $memberClosing) {
            $memberClosings[$key]->closingDate = Carbon::parse($memberClosing->closingDate)->format('d-m-Y');
            $memberClosings[$key]->sl          = $sl++;
            $memberClosings[$key]->id          = encrypt($memberClosing->id);
            $memberClosings[$key]->action      = RoleService::roleWiseArray($this->GlobalRole, $memberClosings[$key]->id);
        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $memberClosings,
        );

        return response()->json($data);
    }

    public function view($id)
    {
        $memberClosing = DB::table('mfn_member_closings')->where('id', decrypt($id))
            ->select('id', 'branchId', 'memberId', 'samityId', 'closingDate', 'closingBalance')->first();

        if (Auth::user()->branch_id != 1 && Auth::user()->branch_id != $memberClosing->branchId) {
            return '';
        }

        $member = DB::table('mfn_members')->where('id', $memberClosing->memberId)->select('name', 'memberCode')->first();

        $branch = DB::table('gnl_branchs')->where('id', $memberClosing->branchId)->first()->branch_name;
        $samity = DB::table('mfn_samity')->where('id', $memberClosing->samityId)->first()->name;

        $data = array(
            'memberClosing' => $memberClosing,
            'member'        => $member,
            'branch'        => $branch,
            'samity'        => $samity,

        );
        return view('MFN.MemberClosing.view', $data);
    }

    public function add(Request $req)
    {
        $branchId            = Auth::user()->branch_id;
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();
        $branchList          = DB::Table('gnl_branchs')
            ->where([['is_delete', 0], ['is_active', 1], ['is_approve', 1]])
            ->where('id', '>', 1)
            ->whereIn('id', $accessAbleBranchIds)
            ->orderBy('branch_name')
            ->select('id', 'branch_name', 'branch_code')
            ->get();

        $samityList = DB::Table('mfn_samity')
            ->where([['is_delete', 0], ['branchId', $branchId]])
            ->select('id', 'name', 'samityCode')
            ->get();

        $branch = DB::Table('gnl_branchs')->where('is_delete', 0)
            ->where('id', $branchId)
            ->select('id', 'branch_name')
            ->first();

        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        return view('MFN.MemberClosing.add', compact('branchList', 'samityList', 'branch'));
    }

    public function store(Request $req)
    {
        $passport = $this->getPassport($req, $operationType = 'store');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }
        $memberId = $req->member_id;
        $samityId = $req->samity_id;
        $branchId = $req->branch_id;

        DB::beginTransaction();
        try {
            $member              = Member::where('id', $memberId)->first();
            $member->closingDate = Carbon::parse($req->closingDate);
            $member->save();

            $savingsAccounts = DB::table('mfn_savings_accounts')
                ->where([
                    ['is_delete', 0],
                    ['memberId', $memberId],
                    ['closingDate', '0000-00-00'],
                ])
                ->get();

            foreach ($savingsAccounts as $key => $savingsaccinfo) {
                $filters['accountId']                    = $savingsaccinfo->id;
                $filters['memberId']                     = $savingsaccinfo->memberId;
                $filters['branchId']                     = $savingsaccinfo->branchId;
                $filters['samityId']                     = $savingsaccinfo->samityId;
                // $savingsAccounts[$key]['savingsBalance'] = MfnService::getSavingsBalance($filters);
                $savingsAccounts[$key]->savingsBalance = MfnService::getSavingsBalance($filters);
            }

            $accClosingReq = new Request;
            foreach ($savingsAccounts as $savAcc) {
                /// make a account closing request
                $accClosingReq->merge([
                    "memberId"              => $memberId,
                    "accountId"             => $savAcc->id,
                    "closingDate"           => date('Y-m-d', strtotime($req->closingDate)),
                    "transactionTypeId"     => 6, // 6 for member closing
                    "ledgerId"              => null,
                    "chequeNo"              => null,
                    "closingAmount"         => $savAcc->savingsBalance,
                    "isFromMemberClosing"   => 1,
                ]);

                $response = app('App\Http\Controllers\MFN\Savings\ClosingController')->store($accClosingReq)->getData();

                if ($response->{'alert-type'} == 'error') {
                    $notification = array(
                        'message'    => $response->message,
                        'alert-type' => 'error',
                    );
                    return response()->json($notification);
                }
            }

            $memberClosing                          = new MemberClosing();
            $memberClosing->memberId                = $req->member_id;
            $memberClosing->samityId                = $req->samity_id;
            $memberClosing->branchId                = Auth::user()->branch_id;
            $memberClosing->closingDate             = Carbon::parse($req->closingDate);
            $memberClosing->closingBalance          = $req->closingBL;
            $memberClosing->closedSavingsAccountIds = json_encode($savingsAccounts->pluck('id')->toArray());
            $memberClosing->created_by              = Auth::user()->id;
            $memberClosing->created_at              = Carbon::now();
            $memberClosing->save();

            MfnService::sendMail('mfn_member_closings', $memberClosing->memberId, $memberClosing->created_at);

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Inserted',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        } catch (Exception $e) {
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
        $memberClosing = MemberClosing::find(decrypt($req->id));

        DB::beginTransaction();

        try {

            $memberClosing->is_delete = 1;
            $memberClosing->save();

            $member              = Member::where('id', $memberClosing->memberId)->first();
            $member->closingDate = '0000-00-00';
            $member->save();

            $savAccs = json_decode($memberClosing->closedSavingsAccountIds);

            foreach ($savAccs as $savAcc) {
                $savings              = SavingsAccount::where([['is_delete', 0], ['id', $savAcc]])->first();
                if ($savings->closingDate != $memberClosing->closingDate) {
                    throw new \Exception('Savings Account Closing date is not matched with Member Closing date.');
                }

                $savings->closingDate = '0000-00-00';
                $savings->save();
                
                $savingsClosing              = SavingsClosing::where([['is_delete', 0], ['accountId', $savAcc]])->first();
                if ($savingsClosing->closingDate != $memberClosing->closingDate) {
                    throw new \Exception('Savings Closing date is not matched with Member Closing date.');
                }

                $savingsClosing->is_delete = 1;
                $savingsClosing->save();

                $withdraw            = SavingsWithdraw::where([['is_delete', 0], ['accountId', $savAcc], ['transactionTypeId', 6]])->first();

                if ($withdraw->date != $memberClosing->closingDate) {
                    throw new \Exception('Withdraw date is not matched with Member Closing date.');
                }

                $withdraw->is_delete = 1;
                $withdraw->save();
            }

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

    public function getPassport($req, $operationType)
    {
        $memberId = $req->member_id;
        $samityId = $req->samity_id;
        $branchId = ($req->branch_id) ? $req->branch_id : Auth::user()->branch_id;

        $errorMsg = null;

        if ($operationType == 'store') {

            $sysDate = MfnService::systemCurrentDate($branchId);

            $memberClosingDate = Carbon::parse($req->closingDate)->format('Y-m-d');

            $savingsDeposit = DB::table('mfn_savings_deposit')
                ->where([['memberId', $memberId], ['is_delete', 0], ['amount', '!=', 0]])
                ->whereDate('date', '>', $memberClosingDate)
                ->exists();

            $savingsWithdraw = DB::table('mfn_savings_withdraw')
                ->where([['memberId', $memberId], ['is_delete', 0], ['amount', '!=', 0]])
                ->whereDate('date', '>', $memberClosingDate)
                ->exists();

            $savingsAccount = DB::table('mfn_savings_accounts')
                ->where([['memberId', $memberId], ['is_delete', 0]])
                ->where(function ($query) use ($memberClosingDate) {
                    $query->where('closingDate', '>', $memberClosingDate)
                        ->orWhere('openingDate', '>', $memberClosingDate);
                })
                ->exists();

            // $savingsClosing

            $loan = DB::table('mfn_loans')
                ->where([['memberId', $memberId], ['is_delete', 0]])
                ->where(function ($loan) use ($memberClosingDate) {
                    $loan->where('disbursementDate', '>', $memberClosingDate)
                        ->orWhere('loanCompleteDate', null)
                        ->orWhere('loanCompleteDate', '0000-00-00');
                })
                ->exists();

            $primaryProdTran = DB::table('mfn_member_primary_product_transfers')
                ->where([['memberId', $memberId], ['is_delete', 0]])
                ->whereDate('transferDate', '>', $memberClosingDate)
                ->exists();

            $loanCollection = DB::table('mfn_loan_collections')
                ->where([['memberId', $memberId], ['is_delete', 0], ['amount', '!=', 0]])
                ->whereDate('collectionDate', '>', $memberClosingDate)
                ->exists();

            // Check if any future transaction exists or not
            if ($savingsDeposit > 0 || $savingsWithdraw > 0 || $savingsAccount > 0 ||
                $loan > 0 || $primaryProdTran > 0 || $loanCollection > 0
            ) {
                $errorMsg = 'Member Cant be closed, subsequent transaction exists.';
            }
        }

        // check branch date is equal to Member Closing date or not
        if ($sysDate != $memberClosingDate) {
            $errorMsg = 'Branch date is not equal to Member Closing date.';
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid'  => $isValid,
            'errorMsg' => $errorMsg,
        );

        return $passport;
    }

    public function getData(Request $req)
    {
        $data = [];

        if ($req->context == 'member') {
            $savAccs = DB::table('mfn_savings_accounts AS savAcc')
            ->leftJoin('mfn_savings_product AS savP', 'savP.id', 'savAcc.savingsProductId')
                ->where([
                    ['savAcc.is_delete', 0],
                    ['savAcc.memberId', $req->memberId],
                    ['savAcc.closingDate', '0000-00-00'],
                ])
                ->select('savP.name AS productName', 'savAcc.*')
                ->get();

            $deposits = DB::table('mfn_savings_deposit')
                ->where([
                    ['is_delete', 0],
                ])
                ->whereIn('accountId', $savAccs->pluck('id')->toArray())
                ->groupBy('accountId')
                ->select(DB::raw("accountId, SUM(amount) AS amount"))
                ->get();

            $withdraws = DB::table('mfn_savings_withdraw')
                ->where([
                    ['is_delete', 0],
                ])
                ->whereIn('accountId', $savAccs->pluck('id')->toArray())
                ->groupBy('accountId')
                ->select(DB::raw("accountId, SUM(amount) AS amount"))
                ->get();

            foreach ($savAccs as $key => $savAcc) {
                $savAccs[$key]->deposit = $deposits->where('accountId', $savAcc->id)->sum('amount');
                $savAccs[$key]->withdraw = $withdraws->where('accountId', $savAcc->id)->sum('amount');
                $savAccs[$key]->balance = $savAccs[$key]->deposit - $savAccs[$key]->withdraw;
            }

            $data = array(
                'savAccs' => $savAccs
            );

            return response()->json($data);
        }
    }
}
