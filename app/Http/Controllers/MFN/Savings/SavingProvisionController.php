<?php

namespace App\Http\Controllers\MFN\Savings;

use App\Http\Controllers\Controller;
use App\Model\MFN\SavingProvisions;
use App\Model\MFN\SavingProvisionsDetails;
use App\Services\HrService;
use App\Services\MfnService;
use Auth;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class SavingProvisionController extends Controller
{
    public static $fromProvisionDate;
    public static $toProvisionDate;
    public static $monthEndDate;
    public static $provisionFrequency;
    public static $generateMethod;
    public static $generateProvisionHavingWithdraw;

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
                ->select('id', 'branch_name', 'branch_code', DB::raw('CONCAT(branch_code, " - ", branch_name) as branch'))
                ->get();

            $data = array(
                'branchList' => $branchList,
            );

            return view('MFN.Savings.InterestProvision.index', $data);
        }

        $columns = [
            'date',
            'otsId',
            'amount',
            'action',
        ];

        $limit            = $req->length;
        $orderColumnIndex = (int) $req->input('order.0.column') <= 1 ? 0 : (int) $req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $savingProvisionData = DB::table('mfn_savings_provision as msp')
            ->where('msp.is_delete', 0)
            ->whereIn('mspd.branchId', $accessAbleBranchIds)
            ->select('msp.id', 'msp.provisionDate', 'msp.provisionCode', 'msp.amount')
            ->leftjoin('mfn_savings_provision_details as mspd', 'msp.id', 'mspd.provisionId')
            ->groupBy('msp.provisionCode')
            ->get();

        $totalData = $savingProvisionData->count();
        $sl        = (int) $req->start + 1;

        foreach ($savingProvisionData as $key => $row) {
            $savingProvisionData[$key]->sl            = $sl++;
            $savingProvisionData[$key]->provisionDate = Carbon::parse($row->provisionDate)->format('d-m-Y');
            $savingProvisionData[$key]->amount        = round($row->amount, 2);
            $savingProvisionData[$key]->id            = encrypt($row->id);

        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $savingProvisionData,
        );

        return response()->json($data);
    }

    public function add(Request $req)
    {
        $savingProvision = null;
        $reqData         = $req->all();

        if ($req->generateFor === 'All') {

            $savingAccountIds = DB::table('mfn_savings_accounts')
                ->where('is_delete', 0)
                ->pluck('id')
                ->toArray();

            $savingProvision = self::generateSavingProvision($savingAccountIds, $req->dateTo);

        } else {

            if ($req->samityId === 'all') {

                $savingAccountIdsForBranchAllSamity = DB::table('mfn_savings_accounts')
                    ->where([
                        ['is_delete', 0],
                        ['branchId', $req->branchId],
                    ])
                    ->pluck('id')
                    ->toArray();

                $savingProvision = self::generateSavingProvision($savingAccountIdsForBranchAllSamity, $req->dateTo);

            } elseif ($req->accountId === 'all') {

                $savingAccountIdsForSamity = DB::table('mfn_savings_accounts')
                    ->where([
                        ['is_delete', 0],
                        ['branchId', $req->branchId],
                        ['samityId', $req->samityId],
                    ])
                    ->pluck('id')
                    ->toArray();

                $savingProvision = self::generateSavingProvision($savingAccountIdsForSamity, $req->dateTo);
            } else {

                $savingProvision = self::generateSavingProvision($req->accountId, $req->dateTo);
            }
        }

        if (!is_array($savingProvision)) {

            $notification = array(
                'alert-type' => 'error',
                'message'    => $savingProvision,
            );

            return response()->json($notification);
        }

        DB::beginTransaction();
        try {

            $reqData['dateTo']        = Carbon::parse($req->dateTo)->format('Y-m-d');
            $reqData['provisionCode'] = self::gernerateProvisionCode();
            $reqData['provisionDate'] = MfnService::systemCurrentDate(Auth::user()->branch_id);
            $reqData['amount']        = array_sum(array_column($savingProvision, 'provisionAmount'));
            $reqData['created_by']    = Auth::user()->id;

            //insert into saving provision & get inserted Id
            $reqData['provisionId'] = SavingProvisions::create($reqData)->id;

            foreach ($savingProvision as $row) {

                $reqData['branchId']        = $row['branchId'];
                $reqData['samityId']        = $row['samityId'];
                $reqData['accountId']       = $row['accountId'];
                $reqData['principalAmount'] = $row['principalAmount'];
                $reqData['provisionAmount'] = $row['provisionAmount'];
                $reqData['dateFrom']        = $row['dateFrom'];

                SavingProvisionsDetails::create($reqData);
            }

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

    public function view(Request $req)
    {
        $savingProvisionData = DB::table('mfn_savings_provision_details as mspd')
            ->where('mspd.provisionId', decrypt($req->id))
            ->select('msa.accountCode', 'mm.name as accountHolder', 'msprodt.name as accountNature', 'mspd.dateFrom', 'mspd.dateTo', 'mspd.principalAmount', 'mspd.provisionAmount')
            ->leftjoin('mfn_savings_accounts as msa', 'mspd.accountId', 'msa.id')
            ->leftjoin('mfn_members as mm', 'msa.memberId', 'mm.id')
            ->leftjoin('mfn_savings_product as msprod', 'msa.savingsProductId', 'msprod.id')
            ->leftjoin('mfn_savings_product_type as msprodt', 'msprod.productTypeId', 'msprodt.id')
            ->get();

        $sl                 = 1;
        $provisionTableHtml = '<tbody>';

        foreach ($savingProvisionData as $row) {

            $provisionTableHtml .= '<tr>' .
            '<td>' . $sl++ . '</td>' .
            '<td>' . $row->accountCode . '</td>' .
            '<td>' . $row->accountHolder . '</td>' .
            '<td>' . $row->accountNature . '</td>' .
            '<td>' . Carbon::parse($row->dateFrom)->format('d-m-Y') . '</td>' .
            '<td>' . Carbon::parse($row->dateTo)->format('d-m-Y') . '</td>' .
            '<td class="text-right">' . $row->principalAmount . '</td>' .
            '<td class="text-right">' . $row->provisionAmount . '</td>' .
                '</tr>';
        }

        $provisionTableHtml .= '</tbody>';

        return response()->json(array('provisionTableHtml' => $provisionTableHtml));
    }

    public function delete(Request $req)
    {
        DB::beginTransaction();

        try {

            SavingProvisions::where('id', decrypt($req->id))->update(['is_delete' => 1]);

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

    public function getData(Request $req)
    {
        if ($req->context === 'samity') {
            $samities = DB::table('mfn_samity')
                ->where([
                    ['is_delete', 0],
                    ['branchId', $req->branchId],
                ])
                ->select('id', DB::raw("CONCAT(samityCode, ' - ', name) as samityName"))
                ->get();

            $samityOptionHtml = "<option value='all'>All</option>";

            foreach ($samities as $samity) {
                $samityOptionHtml .= "<option value=" . $samity->id . ">" . $samity->samityName . "</option>";
            }

            $data = array(
                'samities' => $samityOptionHtml,
            );
        }

        if ($req->context === 'account') {
            $accounts = DB::table('mfn_savings_accounts')
                ->where([
                    ['is_delete', 0],
                    ['samityId', $req->samityId],
                ])
                ->select('id', 'accountCode')
                ->get();

            $accountsOptionHtml = "<option value='all'>All</option>";

            foreach ($accounts as $account) {
                $accountsOptionHtml .= "<option value=" . $account->id . ">" . $account->accountCode . "</option>";
            }

            $data = array(
                'accounts' => $accountsOptionHtml,
            );
        }

        return response()->json($data);
    }

    public static function generateSavingProvision($accIdOrIds, ...$dates)
    {
        if (isset($dates[1])) {

            self::$toProvisionDate   = date('Y-m-d', strtotime($dates[1]));
            self::$fromProvisionDate = date('Y-m-d', strtotime($dates[0]));

            if (self::$fromProvisionDate > self::$toProvisionDate) {
                return 'From date should be appear first!!';
            }

        } elseif (isset($dates[0])) {
            self::$toProvisionDate = date('Y-m-d', strtotime($dates[0]));
        }

        if (is_numeric($accIdOrIds)) {
            $accIdOrIds = [$accIdOrIds];
        }

        $provisionConfig = json_decode(
            DB::table('mfn_config')
                ->where('title', 'provision')
                ->select('content')
                ->first()
                ->content
        );

        self::$provisionFrequency              = $provisionConfig->provisionFrequency;
        self::$generateMethod                  = $provisionConfig->generateMethod;
        self::$generateProvisionHavingWithdraw = $provisionConfig->generateProvisionHavingWithdraw;

        $savingAccInfo = DB::table('mfn_savings_accounts')
            ->where('is_delete', 0)
            ->whereIn('id', $accIdOrIds)
            ->select('id', 'branchId', 'samityId', 'memberId', 'interestRate', 'periodMonth', 'openingDate')
            ->get();

        if (self::$provisionFrequency == 'daily') {

            $interestAmount = 0;
            $provisionArr   = array();

            foreach ($savingAccInfo as $savingKey => $accinfo) {

                $sysDate = MfnService::systemCurrentDate($accinfo->branchId);

                self::$fromProvisionDate = DB::table('mfn_savings_provision_details as mspd')
                    ->where([
                        ['mspd.accountId', $accinfo->id],
                        ['msp.is_delete', 0],
                    ])
                    ->leftjoin('mfn_savings_provision as msp', 'mspd.provisionId', 'msp.id')
                    ->max('mspd.dateTo');

                if (is_null(self::$fromProvisionDate)) {
                    self::$fromProvisionDate = date('Y-m-d', strtotime($accinfo->openingDate));
                }

                self::$fromProvisionDate = date('Y-m-d', strtotime('+1 day', strtotime(self::$fromProvisionDate)));

                if (self::$fromProvisionDate > self::$toProvisionDate) {
                    return 'Sorry, Already generated provision for this day!!';
                }

                if (self::$fromProvisionDate != self::$toProvisionDate) {
                    return 'Please generate provision previous date ' . date('d-m-Y', strtotime(self::$fromProvisionDate)) . '!!';
                }

                $accDeposit = DB::table('mfn_savings_deposit')
                    ->where([
                        ['is_delete', 0],
                        ['accountId', $accinfo->id],
                        ['date', self::$fromProvisionDate],
                    ])
                    ->sum('amount');

                $accWithDraw = DB::table('mfn_savings_withdraw')
                    ->where([
                        ['is_delete', 0],
                        ['accountId', $accinfo->id],
                        ['date', self::$fromProvisionDate],
                    ])
                    ->sum('amount');

                $currentBalance = floatval($accDeposit) - floatval($accWithDraw);

                $isLeapYear     = (date('L', mktime(0, 0, 0, 1, 1, date('Y', strtotime($sysDate)))) == 1) ? 366 : 365;
                $interestRate   = (floatval($accinfo->interestRate) / $isLeapYear) / 100;
                $interestAmount = $currentBalance * $interestRate;

                $provisionArr[$savingKey]['accountId']       = $accinfo->id;
                $provisionArr[$savingKey]['branchId']        = $accinfo->branchId;
                $provisionArr[$savingKey]['samityId']        = $accinfo->samityId;
                $provisionArr[$savingKey]['memberId']        = $accinfo->memberId;
                $provisionArr[$savingKey]['dateFrom']        = self::$fromProvisionDate;
                $provisionArr[$savingKey]['principalAmount'] = $currentBalance;
                $provisionArr[$savingKey]['provisionAmount'] = $interestAmount;
            }

            return $provisionArr;
        }
        // provisionFrequency = monthly, half-yearly, quaterly, yearly
        else {
            //for daily method
            if (self::$generateMethod == 'daily') {

                $provisionArr = array();
                foreach ($savingAccInfo as $savingKey => $accinfo) {

                    $sysDate = MfnService::systemCurrentDate($accinfo->branchId);

                    self::$fromProvisionDate = DB::table('mfn_savings_provision_details as mspd')
                        ->where([
                            ['mspd.accountId', $accinfo->id],
                            ['msp.is_delete', 0],
                        ])
                        ->leftjoin('mfn_savings_provision as msp', 'mspd.provisionId', 'msp.id')
                        ->max('mspd.dateTo');

                    if (is_null(self::$fromProvisionDate)) {
                        self::$fromProvisionDate = date('Y-m-d', strtotime($accinfo->openingDate));
                    }

                    self::$fromProvisionDate = date('Y-m-d', strtotime('+1 day', strtotime(self::$fromProvisionDate)));

                    if (self::$fromProvisionDate > self::$toProvisionDate) {
                        return 'Sorry, Already generated provision for this day!!';
                    }

                    $accDeposit = DB::table('mfn_savings_deposit')
                        ->where([['is_delete', 0], ['accountId', $accinfo->id]])
                        ->whereBetween('date', [self::$fromProvisionDate, self::$toProvisionDate])
                        ->pluck('amount', 'date')
                        ->toArray();

                    $accWithDraw = DB::table('mfn_savings_withdraw')
                        ->where([['is_delete', 0], ['accountId', $accinfo->id]])
                        ->whereBetween('date', [self::$fromProvisionDate, self::$toProvisionDate])
                        ->select('date', DB::raw('(0 - amount) as amount'))
                        ->get();

                    $accWithDraw    = $accWithDraw->pluck('amount', 'date')->toArray();
                    $accTransaction = array_merge($accDeposit, $accWithDraw);
                    ksort($accTransaction);

                    $transactionDate = array_keys($accTransaction);

                    $isLeapYear = (date('L', mktime(0, 0, 0, 1, 1, date('Y', strtotime($sysDate)))) == 1) ? 366 : 365;

                    $interestRate = (floatval($accinfo->interestRate) / $isLeapYear) / 100;

                    $currentBalance = 0;
                    $interestAmount = 0;
                    foreach ($transactionDate as $key => $date) {

                        $dateDiff = date_diff(date_create($date), date_create((!empty($transactionDate[$key + 1]) ? $transactionDate[$key + 1] : self::$toProvisionDate)))->days;

                        $currentBalance += floatval($accTransaction[$date]);
                        $interestAmount += floatval($currentBalance * $dateDiff * $interestRate);

                    }

                    $provisionArr[$savingKey]['accountId']       = $accinfo->id;
                    $provisionArr[$savingKey]['branchId']        = $accinfo->branchId;
                    $provisionArr[$savingKey]['samityId']        = $accinfo->samityId;
                    $provisionArr[$savingKey]['memberId']        = $accinfo->memberId;
                    $provisionArr[$savingKey]['dateFrom']        = self::$fromProvisionDate;
                    $provisionArr[$savingKey]['principalAmount'] = $currentBalance;
                    $provisionArr[$savingKey]['provisionAmount'] = $interestAmount;
                }

                return $provisionArr;
            }
            //for average method
            elseif (self::$generateMethod == 'average') {

                $interestAmount = 0;
                $provisionArr   = array();

                foreach ($savingAccInfo as $savingKey => $accinfo) {

                    $sysDate = MfnService::systemCurrentDate($accinfo->branchId);

                    self::$fromProvisionDate = DB::table('mfn_savings_provision_details as mspd')
                        ->where([
                            ['mspd.accountId', $accinfo->id],
                            ['msp.is_delete', 0],
                        ])
                        ->leftjoin('mfn_savings_provision as msp', 'mspd.provisionId', 'msp.id')
                        ->max('mspd.dateTo');

                    if (is_null(self::$fromProvisionDate)) {
                        self::$fromProvisionDate = date('Y-m-d', strtotime($accinfo->openingDate));
                    }

                    self::$fromProvisionDate = date('Y-m-d', strtotime('+1 day', strtotime(self::$fromProvisionDate)));

                    if (self::$fromProvisionDate > self::$toProvisionDate) {
                        return 'Sorry, Already generated provision for this day!!';
                    }

                    $accDepositFrom = DB::table('mfn_savings_deposit')
                        ->where([['is_delete', 0], ['accountId', $accinfo->id]])
                        ->where('date', '<=', self::$fromProvisionDate)
                        ->sum('amount');

                    $accWithDrawFrom = DB::table('mfn_savings_withdraw')
                        ->where([['is_delete', 0], ['accountId', $accinfo->id]])
                        ->where('date', '<=', self::$fromProvisionDate)
                        ->sum('amount');

                    $accDepositTo = DB::table('mfn_savings_deposit')
                        ->where([['is_delete', 0], ['accountId', $accinfo->id]])
                        ->where('date', '<=', self::$toProvisionDate)
                        ->sum('amount');

                    $accWithDrawTo = DB::table('mfn_savings_withdraw')
                        ->where([['is_delete', 0], ['accountId', $accinfo->id]])
                        ->where('date', '<=', self::$toProvisionDate)
                        ->sum('amount');

                    $accOpeningBalance = floatval($accDepositFrom) - floatval($accWithDrawFrom);
                    $accClosingBalance = floatval($accDepositTo) - floatval($accWithDrawTo);
                    $averageBalance    = ($accOpeningBalance + $accClosingBalance) / 2;

                    $interestRate   = (floatval($accinfo->interestRate) / 12) / 100;
                    $interestAmount = $averageBalance * $interestRate;

                    $provisionArr[$savingKey]['accountId']       = $accinfo->id;
                    $provisionArr[$savingKey]['branchId']        = $accinfo->branchId;
                    $provisionArr[$savingKey]['samityId']        = $accinfo->samityId;
                    $provisionArr[$savingKey]['memberId']        = $accinfo->memberId;
                    $provisionArr[$savingKey]['dateFrom']        = self::$fromProvisionDate;
                    $provisionArr[$savingKey]['principalAmount'] = $averageBalance;
                    $provisionArr[$savingKey]['provisionAmount'] = $interestAmount;
                }

                return $provisionArr;
            }
        }
    }

    public static function gernerateProvisionCode()
    {
        $preProvisionCode = "PV";

        $lastProvisionCode = DB::table('mfn_savings_provision')
            ->select(['id', 'provisionCode'])
            ->where('provisionCode', 'LIKE', "{$preProvisionCode}%")
            ->orderBy('provisionCode', 'DESC')
            ->first();

        if ($lastProvisionCode) {

            $oldProvisionCode = explode($preProvisionCode, $lastProvisionCode->provisionCode);
            $preProvisionCode = $preProvisionCode . sprintf("%05d", ($oldProvisionCode[1] + 1));

        } else {
            $preProvisionCode = $preProvisionCode . sprintf("%05d", 1);
        }

        return $preProvisionCode;
    }
}
