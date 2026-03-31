<?php

namespace App\Http\Controllers\MFN\Others;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Services\AccService;
use App\Services\MfnService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Model\MFN\MemberPrimaryProductTransfer;
use App\Model\MFN\SavingsDeposit;
use App\Model\MFN\SavingsWithdraw;

class CheckInappropriateDataController extends Controller
{
    private $updateData = false;

    public function index(Request $req)
    {

        $branches = DB::table('gnl_branchs')
            ->where([
                ['is_delete', 0],
                ['id', '>', 1],
                // ['id', '=', 12],
            ])
            // ->limit(5)
            ->get();

        foreach ($branches as $key => $branch) {
            $this->memberPrimaryProductTransfer($branch->id);
            // $this->updatePrimaryProductTransfers($branch->id);
        }
    }

    public function memberPrimaryProductTransfer($branchId = null, $samityId = null, $memberId = null, $dateFrom = null, $dateTo = null)
    {
        $transfers = DB::table('mfn_member_primary_product_transfers')
            ->where('is_delete', 0);

        $branchId != null ? $transfers->where('branchId', $branchId) : false;
        $samityId != null ? $transfers->where('samityId', $samityId) : false;
        $memberId != null ? $transfers->where('memberId', $memberId) : false;

        if ($dateFrom != null) {
            $dateFrom = date('Y-m-d', strtotime($dateFrom));
            $transfers->where('transferDate', '>=', $dateFrom);
        }
        if ($dateTo != null) {
            $dateTo = date('Y-m-d', strtotime($dateTo));
            $transfers->where('transferDate', '<=', $dateTo);
        }

        $transfers = $transfers->get();

        // check transfered members product wise balance
        // current primary product savings balance should be real savings balance
        // others should be zero

        $memberIds = $transfers->unique('memberId')->pluck('memberId')->toArray();

        $members = DB::table('mfn_members')
            ->whereIn('id', $memberIds)
            ->select('id', 'primaryProductId', 'memberCode')
            ->get();

        $savAccs = DB::table('mfn_savings_accounts')
            ->where('is_delete', 0)
            ->whereIn('memberId', $members->pluck('id'))
            ->select('id', 'accountCode', 'memberId')
            ->get();

        $deposits = DB::table('mfn_savings_deposit')
            ->where([
                ['is_delete', 0]
            ])
            ->whereIn('memberId', $memberIds)
            ->groupBy('memberId')
            ->groupBy('primaryProductId')
            ->groupBy('accountId')
            ->select(DB::raw("memberId, primaryProductId, accountId, SUM(amount) AS amount, COUNT(id) AS numberOfDeposit"))
            ->get();

        $withdraws = DB::table('mfn_savings_withdraw')
            ->where([
                ['is_delete', 0]
            ])
            ->whereIn('memberId', $memberIds)
            ->groupBy('memberId')
            ->groupBy('primaryProductId')
            ->groupBy('accountId')
            ->select(DB::raw("memberId, primaryProductId, accountId, SUM(amount) AS amount, COUNT(id) AS numberOfWithdraw"))
            ->get();

        foreach ($members as $member) {
            $memSavAccs = $savAccs->where('memberId', $member->id);

            $oldPeoductIds = $transfers->where('memberId')->pluck('oldProductId')->toArray();
            $newPeoductIds = $transfers->where('memberId')->pluck('newProductId')->toArray();

            $allProductIds = array_unique(array_merge($oldPeoductIds, $newPeoductIds));

            foreach ($memSavAccs as $memSavAcc) {

                $actualBalance = $deposits->where('accountId', $memSavAcc->id)->sum('amount') - $withdraws->where('accountId', $memSavAcc->id)->sum('amount');

                $hasWrongData = false;

                foreach ($allProductIds as $primaryProductId) {
                    $balance = $deposits->where('accountId', $memSavAcc->id)->where('primaryProductId', $primaryProductId)->sum('amount') - $withdraws->where('accountId', $memSavAcc->id)->where('primaryProductId', $primaryProductId)->sum('amount');

                    if ($primaryProductId != $member->primaryProductId && $balance != 0) {
                        echo "Member Id: $memSavAcc->memberId <br>";
                        echo "Account: $memSavAcc->accountCode , Id: $memSavAcc->id <br>";
                        echo "Old product id: $primaryProductId , balance is $balance <br><br>";
                        $hasWrongData = true;
                    }
                    if ($primaryProductId == $member->primaryProductId && $balance != $actualBalance) {
                        echo "Member Id: $memSavAcc->memberId <br>";
                        echo "Account: $memSavAcc->accountCode , Id: $memSavAcc->id <br>";
                        echo "Current product id: $primaryProductId , Actual Balance: $actualBalance , Product Balance: $balance <br><br>";
                        $hasWrongData = true;
                    }
                }

                if ($hasWrongData) {
                    $numberOfDeposit = $deposits->where('accountId', $memSavAcc->id)->sum('numberOfDeposit');
                    $numberOfWithdraw = $deposits->where('accountId', $memSavAcc->id)->sum('numberOfWithdraw');

                    if ($numberOfDeposit == 1 && $numberOfWithdraw == 0) {
                        // echo "Account: $memSavAcc->accountCode , Id: $memSavAcc->id <br>";
                    }
                }
            }
        }
    }

    public function closedAccount($branchId, $samityId = null)
    {
        // check closed accounts having any balance or not
        $accounts = DB::table('mfn_savings_accounts')
            ->where([
                ['is_delete', 0],
                ['branchId', $branchId],
                ['closingDate', '!=', '0000-00-00'],
            ]);

        if ($samityId != null) {
            $accounts->where('samityId', $samityId);
        }

        $accounts = $accounts->get();

        $deposits = DB::table('mfn_savings_deposit')
            ->where('is_delete', 0)
            ->whereIn('accountId', $accounts->pluck('id')->toArray())
            ->groupBy('accountId')
            ->select(DB::raw("accountId, SUM(amount) AS amount"))
            ->get();

        $withdraws = DB::table('mfn_savings_withdraw')
            ->where('is_delete', 0)
            ->whereIn('accountId', $accounts->pluck('id')->toArray())
            ->groupBy('accountId')
            ->select(DB::raw("accountId, SUM(amount) AS amount"))
            ->get();

        foreach ($accounts as $account) {
            $balance = $deposits->where('id', $account->id)->sum('amount') - $withdraws->where('id', $account->id)->sum('amount');

            if ($balance != 0) {
                echo $account->accountCode . ' -- ' . $balance . '<br>';
            }
        }
    }

    public function updatePrimaryProductTransfers($branchId)
    {

        DB::beginTransaction();

        try {

            $transfers = DB::table('mfn_member_primary_product_transfers')
                ->where([
                    ['is_delete', 0],
                    ['branchId', $branchId],
                ])
                ->orderBy('transferDate')
                ->get();

            foreach ($transfers as $transfer) {

                DB::table('mfn_savings_deposit')
                    ->where([
                        ['is_delete', 0],
                        ['transactionTypeId', '!=', 8],
                        ['date', '>=', $transfer->transferDate],
                        ['memberId', $transfer->memberId],
                    ])
                    ->update(['primaryProductId' => $transfer->newProductId]);

                DB::table('mfn_savings_deposit')
                    ->where([
                        ['is_delete', 0],
                        ['transactionTypeId', '!=', 8],
                        ['date', '<', $transfer->transferDate],
                        ['memberId', $transfer->memberId],
                    ])
                    ->update(['primaryProductId' => $transfer->oldProductId]);

                DB::table('mfn_savings_withdraw')
                    ->where([
                        ['is_delete', 0],
                        ['transactionTypeId', '!=', 8],
                        ['date', '>=', $transfer->transferDate],
                        ['memberId', $transfer->memberId],
                    ])
                    ->update(['primaryProductId' => $transfer->newProductId]);

                DB::table('mfn_savings_withdraw')
                    ->where([
                        ['is_delete', 0],
                        ['transactionTypeId', '!=', 8],
                        ['date', '<', $transfer->transferDate],
                        ['memberId', $transfer->memberId],
                    ])
                    ->update(['primaryProductId' => $transfer->oldProductId]);

                $transferDate = $transfer->transferDate;

                $savingsACCs = DB::table('mfn_savings_accounts')
                    ->where('is_delete', 0)
                    ->where('openingDate', '<=', $transfer->transferDate)
                    ->where('memberId', $transfer->memberId)
                    ->where(function ($query) use ($transferDate) {
                        $query->where('closingDate', '0000-00-00')
                            ->orWhere('closingDate', '>', $transferDate);
                    })
                    ->get();

                foreach ($savingsACCs as $index => $savAcc) {
                    $filters['accountId']        = $savAcc->id;
                    $filters['dateTo']           = date('Y-m-d', strtotime('-1 days', strtotime($transfer->transferDate)));
                    $balance                     = MfnService::getSavingsBalance($filters);
                    $savingsArray[$index]['id']  = $savAcc->id;
                    $savingsArray[$index]['amt'] = $balance;

                    // withdraw of old product
                    $withdraw = SavingsWithdraw::updateOrCreate(
                        ['accountId' => $savAcc->id, 'date' => $transfer->transferDate, 'transactionTypeId' => 8, 'is_delete' => 0],
                        [
                            'memberId' => $savAcc->memberId,
                            'samityId' => $savAcc->samityId,
                            'branchId' => $savAcc->branchId,
                            'primaryProductId' => $transfer->oldProductId,
                            'savingsProductId' => $savAcc->savingsProductId,
                            'amount' => $balance,
                            'ledgerId' => 0,
                            'chequeNo' => 0,
                            'created_at' => Carbon::now(),
                            'created_by' => 0,
                        ]
                    );

                    // diposite of new product

                    $deposit = SavingsDeposit::updateOrCreate(
                        ['accountId' => $savAcc->id, 'date' => $transfer->transferDate, 'transactionTypeId' => 8, 'is_delete' => 0],
                        [
                            'memberId' => $savAcc->memberId,
                            'samityId' => $savAcc->samityId,
                            'branchId' => $savAcc->branchId,
                            'primaryProductId' => $transfer->newProductId,
                            'savingsProductId' => $savAcc->savingsProductId,
                            'amount' => $balance,
                            'ledgerId' => 0,
                            'chequeNo' => 0,
                            'created_at' => Carbon::now(),
                            'created_by' => 0,
                        ]
                    );

                    echo "deposit id: $deposit->id, withdraw id: $withdraw->id <br>";
                }

                $transferData = json_encode($savingsArray);

                $ppTransfer               = MemberPrimaryProductTransfer::find($transfer->id);
                $ppTransfer->transferData = json_encode($savingsArray);
                $ppTransfer->save();
            }

            DB::commit();
            $notification = array(
                'message'    => 'Operation Completed Successfully',
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
}
