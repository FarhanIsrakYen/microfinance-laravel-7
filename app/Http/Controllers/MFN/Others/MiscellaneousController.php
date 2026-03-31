<?php

namespace App\Http\Controllers\MFN\Others;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MfnService;
use App\Services\HrService;
use App\Services\AccService;
use Carbon\Carbon;
use DB;
use App\Http\Controllers\MFN\Process\MonthEndSummary;
use App\Http\Controllers\MFN\Process\AutoVoucher;
use App\Http\Controllers\MFN\Savings\SavingsAccountController;

class MiscellaneousController extends Controller
{

    public function index()
    {

        $withdraw = SavingsWithdraw::where('accountId', 14101)->where('is_delete', 0)
                                        ->where('date', '>', MfnService::systemCurrentDate(22))
                                        ->where('transactionTypeId', '!=', 4)->count();
        dd($withdraw);
        // dd(date('Y-m-d H:m:s', 1128297600));
        // dd(strtotime(''));
        dd(date('Y-m-d H:m:s', 1612787336), date('Y-m-d H:m:s', 1612787643));
        dd(date('Y-m-d', 1612787643));
        $dayEnds = DB::table('mfn_day_end')->where('isActive', 0)->where('numberOfmember', 0)->orderBy('branchId')->limit(15)->get();
        foreach($dayEnds as $dayEnd){
            app('App\Http\Controllers\MFN\Process\MfnDayEndController')->calculateDayEndSummery($dayEnd->branchId, $dayEnd->date);
        }
        $dayEnds = DB::table('mfn_day_end')->where('isActive', 0)->where('numberOfmember', 0)->orderBy('branchId')->count();
        dd($dayEnds);
        

        $branchDate = '2021-01-04';
        $schedules = MfnService::generateLoanSchedule([9030], $branchDate, '2021-01-04');
        // $schedules = MfnService::getLoanStatus([16623], '19-01-2021');
        echo "<pre>";
        print_r($schedules);
        echo "</pre>";
        dd();
        $schedules = collect($schedules);

        dd($schedules);
        $holidayFrom = "2015-05-07";
        $holidayTo = "2030-12-24";
        $samityIdsHavingSamityHoliday = DB::table('hr_holidays_special')
            ->where([
                ['is_delete', 0],
                ['sh_date_from', '>=', $holidayFrom],
                ['sh_date_to', '<=', $holidayTo],
                ['samity_id', '>', 0],
            ])
            ->groupBy('samity_id')
            ->toSql();

        dd($samityIdsHavingSamityHoliday, $holidayFrom, $holidayTo);


        $loans = DB::table('mfn_loans')
            ->where('is_delete', 0)
            ->limit(1000)
            ->get();
        $startTime = microtime(true);
        // $schedules = MfnService::generateLoanSchedule($loans->pluck('id')->toArray());
        $loanStatus = MfnService::getloanStatus($loans->pluck('id')->toArray(), '2021-01-31');

        dd($loanStatus);

        dd(microtime(true) - $startTime);
        $loanIds = [
            0 => 5736,
            1 => 5748,
            2 => 5754,
            3 => 5757,
            4 => 5759,
            5 => 5761,
            6 => 5762,
            7 => 5765,
            8 => 5770,
            9 => 5772,
            10 => 5775
        ];
        $autoProcessDate = "2021-01-03";
        $schedules = MfnService::generateLoanSchedule([5772, 5736], $autoProcessDate, $autoProcessDate);
        dd($schedules);
        // dd(date('Y-m-d H:m:s'));
        $start = microtime(true);
        $branches = DB::table('gnl_branchs')->where('id', '>', 1)->get();
        foreach ($branches as $key => $branch) {
            SavingsAccountController::openMendatoryRegularSavingsAccount(4, $branch->id);
        }
        $time_elapsed_secs = microtime(true) - $start;
        dd($time_elapsed_secs);

        $data = array(
            'memberId' => 6692,
            'productId' => 1,
        );
        $savingsCode = SavingsAccountController::generateSavingsCode($data);
        dd($savingsCode);
        MfnService::setOpeningBalanceForOneTimeSavings(20);
        dd();
        $filters['accountId'] = 104;
        $balance = MfnService::getSavingsBalance($filters);
        dd($balance);
        $autoVoucher = new AutoVoucher;
        $autoVoucher->test(10, '2020-07-01');
        dd(1);

        $loanStatuses = Mfnservice::getLoanStatus(18, '2020-07-01');
        dd($loanStatuses);
        dd($_SERVER['REMOTE_ADDR']);
        $loaSchedules = Mfnservice::generateLoanSchedule(1);
        dd($loaSchedules);
        $branchIds = DB::table('gnl_branchs')
            ->where([
                ['id', '!=', 1],
                ['is_delete', 0],
                ['is_active', 1],
                ['is_approve', 1],
            ])
            ->pluck('id')
            ->toArray();

        $members = collect();
        foreach ($branchIds as $branchId) {
            $branchMembers = DB::table('mfn_members AS member')
                ->leftjoin('mfn_loans AS loan', 'loan.memberId', 'member.id')
                ->where([
                    ['member.is_delete', 0],
                    ['member.branchId', $branchId],
                    ['member.primaryProductId', '!=', 'loan.productId'],
                    ['loan.id', null],
                ])
                ->where(function ($query) {
                    $query->where('loan.is_delete', 1)
                        ->orWhere('loan.is_delete', null);
                })
                ->select('member.*')
                ->limit(5)
                ->get();

            $members = $members->merge($branchMembers->all());
        }

        dd($members);


        $date = '2020-01';
        $isValid = date('Y-m', strtotime($date . '-01')) == $date ? true : false;
        dd($isValid);

        // $autoVoucher = app('App\Http\Controllers\MFN\Process\AutoVoucher')->store(5, '2020-02-12');
        // dd($autoVoucher);
        $loanStatuses = Mfnservice::getLoanStatus([1, 2], '2020-07-01');
        dd($loanStatuses);
        $type = DB::select(DB::raw('SHOW COLUMNS FROM mfn_auto_voucher_config WHERE Field = "context"'))[0]->Type;
        preg_match('/^enum\((.*)\)$/', $type, $matches);
        $values = array();
        foreach (explode(',', $matches[1]) as $value) {
            $values[] = trim($value, "'");
        }

        dd($values);
        $monthStartDate = '2020-01-31';
        $monthStartDate = date('Y-m-d', strtotime("+1 months", strtotime($monthStartDate)));
        dd($monthStartDate);

        $loanCollection = DB::table('mfn_loan_collections')
            ->where([
                ['is_delete', 0],
            ])
            ->select(DB::raw("SUM(amount) AS amount, SUM(principalAmount) AS principalAmount"))
            ->first();

        dd($loanCollection);
        $loanStatuses = Mfnservice::getLoanStatus([1, 2], '2020-07-01', '2020-08-30');
        // $loanStatus = collect($loanStatus);
        // $loanStatus = $loanStatus->where('loanId', 2)->sum('payableAmount');
        // foreach ($loanStatuses as $key => $loanStatus) {
        //     echo $loanStatus['loanId'];
        // }
        // dd();
        echo '<pre>';
        print_r($loanStatuses);
        echo '</pre>';
        dd();
        $loaSchedules = Mfnservice::generateLoanSchedule(1053);
        $lastInstallment = end($loaSchedules);
        dd($lastInstallment['installmentDate']);
        $holidays = HrService::systemHolidays($companyId = null, $branchID = 5, $samityID = null, $startDate = '2020-02-11', $endDate = '2020-02-20');
        dd($holidays);

        $profileImageName = 'mp202004271.png';
        $profileImage = asset('images/members/profile') . '/' . $profileImageName;
        dd($profileImage);
        for ($i = 0; $i < 100; $i++) {
            $loanAmount = rand(5000, 100000);
            $numberOfInstallment = rand(1, 46);
            $interestRateIndex = rand(1.1 * 10, 1.5 * 10) / 10;
            $installmentDetails = MfnService::generateInstallmentDetails($loanAmount, $numberOfInstallment, $interestRateIndex, $loanType = 'Regular');

            // echo '$loanAmount: '.$loanAmount.'<br>';
            // echo '$numberOfInstallment: '.$numberOfInstallment.'<br>';
            // echo '$interestRateIndex: '.$interestRateIndex.'<br>';
            // echo '<pre>';
            // print_r($installmentDetails);
            // echo '</pre>';

            if ($installmentDetails['extraInstallmentAmount'] < 0 || $installmentDetails['extraInstallmentAmount'] > 100) {
                dd($loanAmount, $numberOfInstallment, $interestRateIndex, $installmentDetails);
            }
        }

        dd('Test Passed');
        dd(date("Y-m-d h:i:s"));
        $str = 'repaymentFrequencyId';
        $arr = preg_split('/(?=[A-Z])/', $str);
        dd($arr);

        // $weeklyLoanIds = DB::table('mfn_loans')->where('repaymentFrequencyId', 2)->pluck('id')->toArray();
        // $time_start = microtime(true);
        // $schedules = MfnService::generateLoanSchedule($weeklyLoanIds);
        // echo '$execution_time ' . ((microtime(true) - $time_start)) . ' s<br>';

        $monthlyLoanIds = DB::table('mfn_loans')->pluck('id')->toArray();
        $time_start = microtime(true);
        $schedules = MfnService::generateLoanSchedule($monthlyLoanIds);
        // $schedules = MfnService::getLoanStatus($monthlyLoanIds, '2020-03-04', '2020-10-10');
        echo '$execution_time ' . ((microtime(true) - $time_start)) . ' s<br>';
        echo 'Number of total installments: ' . count($schedules) . '<br>';

        // echo '<pre>';
        // print_r($schedules);
        // echo '</pre>';        

        return 'true';

        return response()->json($schedules);


        $loanamount = 200000.00;
        $numberOfInstallment = 46;
        $interestRateIndex = 1.141;

        dd(MfnService::generateInstallmentDetails($loanamount, $numberOfInstallment, $interestRateIndex, $loanType = 'Regular'));

        $holidays = HrService::systemHolidays($companyId = null, $branchID = 5, $samityID = null, $startDate = '2020-01-01', $endDate = '2020-04-30');
        dd($holidays);
        $branchAssingedProductIds = json_decode(DB::table('mfn_branch_products')
            ->where('branchId', 5)
            ->value('loanProductIds'));

        dd($branchAssingedProductIds);

        $profileImageFilename = 'mp2020041151.jpg';
        if (file_exists(public_path('images/members/profile/' . $profileImageFilename))) {
            dd('sfdfg');
            $profileImageErrorMsg = 'File already exists, file name: ' . $profileImageFilename;
        }

        dd(asset('/uploads/members/profile/' . $profileImageFilename));
        $branchId = 15;
        $projectId = 3;
        $projectTypeId = 1;
        $accTypeId = 5;

        $ledgers = AccService::getLedgerAccount($branchId, $projectId, $projectTypeId, $accTypeId)->pluck('id')->all();

        dd($ledgers);

        $test = app('App\Http\Controllers\MFN\Member\MemberController')->getMandatorySavingProducts()->pluck('id')->all();

        dd($test);

        $depositReq = new Request;

        $depositReq->merge([
            "memberId"          => 3,
            "accountId"     => 2,
            "date"     => '2020-02-11',
            "amount"      => 451,
            "transactionTypeId"        => 1,
        ]);
        $response = app('App\Http\Controllers\MFN\Savings\DepositController')->store($depositReq)->getData();

        $req = (object)[];
        $req->mobileNo = '01616166666';
        $req->firstEvidenceTypeId = 1;
        $req->firstEvidence = 515545;
        $req->firstEvidenceIssuerCountryId = 7;

        $req->secondEvidenceTypeId = 4;
        $req->secondEvidence = 445424;
        $req->secondEvidenceIssuerCountryId = 10;

        $sysDate = '2020-03-24';
        $operationType = 'create';
        $memberId = '';

        $data = app('App\Http\Controllers\MFN\Member\MemberController')->getDuplicateMemberData($req, $sysDate, $operationType, $memberId);

        dd($data);

        $operationType = 'update';
        $memberCodeExists = DB::table('mfn_members')
            ->where([
                ['is_delete', 0],
                ['memberCode', '0001.0003.0002'],
            ]);

        $operationType == 'update' ? $memberCodeExists->where('id', '!=', 11) : false;

        if ($memberCodeExists->exists()) {
            dd('true');
        }

        dd('false');

        $filename = Carbon::now()->format('Ymd');
        dd($filename);

        $mobileNumberLength = json_decode(DB::table('mfn_config')->where('title', 'general')->first()->content)->mobileNoLength;
        $mobileNumberLength = (int)$mobileNumberLength - 3;
        $mobileNo = '01616166666';
        $isValid = preg_match("/^01[3456789][0-9]{" . $mobileNumberLength . "}\b/", $mobileNo);
        // $isValid = preg_match("/^01[3456789][0-9]{8}\b/", $mobileNo);
        dd($isValid);

        $data = [];
        $data['memberCode'] = '00001.0002.003';
        $data['productId'] = 1;
        $savingsCode = app('App\Http\Controllers\MFN\Savings\SavingsAccountController')->generateSavingsCode($req)->getData();

        dd($savingsCode);
    }

    public function redoAutoVouchers($branchId, $date)
    {
        DB::beginTransaction();

        try {
            $autoVoucher = new AutoVoucher();
            $autoVoucher->mfnCreateAutoVoucher($branchId, $date);

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
