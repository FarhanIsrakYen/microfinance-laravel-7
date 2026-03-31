<?php

namespace App\Http\Controllers\MFN\Savings;

use App\Http\Controllers\Controller;
use App\Model\MFN\SavingsAccount;
use App\Rules\Unique;
use App\Services\HrService;
use App\Services\MfnService;
use App\Services\RoleService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class SavingsAccountController extends Controller
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
                ->select('id', 'branch_name', 'branch_code')
                ->get();

            if (count($branchList) > 1) {
                $samities = [];
            } else {
                $samities = MfnService::getSamities($branchList->pluck('id')->toArray());
            }

            $loanProductIds = MfnService::getBranchAssignedLoanProductIds($branchList->pluck('id')->toArray());
            $savProductIds = MfnService::getBranchAssignedSavProductIds($branchList->pluck('id')->toArray());

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
                'branchList'    => $branchList,
                'samities'      => $samities,
                'loanProducts'  => $loanProducts,
                'savProducts'   => $savProducts,
            );

            return view('MFN.Savings.Account.index', $data);
        }

        $columns = ['sa.accountCode', 'sp.name', 'm.memberCode', 'm.name', 'samity.samityCode', 'samity.name', 'branch.branch_name', 'sa.autoProcessAmount', 'sa.openingDate', 'sa.closingDate', 'sa.closingDate', 'emp.emp_name'];

        $limit            = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $savAccs = DB::table('mfn_savings_accounts AS sa')
            ->leftJoin('mfn_savings_product AS sp', 'sp.id', 'sa.savingsProductId')
            ->leftJoin('mfn_members AS m', 'm.id', 'sa.memberId')
            ->leftJoin('gnl_branchs AS branch', 'branch.id', 'sa.branchId')
            ->leftJoin('mfn_samity AS samity', 'samity.id', 'sa.samityId')
            ->leftJoin('hr_employees AS emp', 'emp.user_id', 'sa.created_by')
            ->whereIn('m.branchId', $accessAbleBranchIds)
            ->where('sa.is_delete', 0)
            ->select('sp.name AS product', 'm.memberCode AS memberCode', 'm.name AS member', 'branch.branch_name AS branchName', 'samity.samityCode', 'samity.name AS samityName', 'emp.emp_name AS empName', 'sa.*')
            ->orderBy($order, $dir);

        if ($search != null) {
            $savAccs->where(function ($query) use ($search) {
                $query->where('sa.accountCode', 'LIKE', "%$search%")
                    ->orWhere('m.name', 'LIKE', "%$search%")
                    ->orWhere('branch.branch_name', 'LIKE', "%$search%")
                    ->orWhere('samity.name', 'LIKE', "%$search%");
            });
        }

        if ($req->filBranch != '') {
            $savAccs->where('sa.branchId', $req->filBranch);
        }
        if ($req->filSamity != '') {
            $savAccs->where('sa.samityId', $req->filSamity);
        }
        if ($req->filPrimaryProduct != '') {
            $savAccs->where('m.primaryProductId', $req->filPrimaryProduct);
        }
        if ($req->filSavProduct != '') {
            $savAccs->where('sa.savingsProductId', $req->filSavProduct);
        }
        if ($req->savingsCode != '') {
            $savAccs->where('sa.accountCode', 'LIKE', "%$req->savingsCode%");
        }
        if ($req->startDate != '') {
            $startDate = Carbon::parse($req->startDate)->format('Y-m-d');
            $savAccs->where('sa.openingDate', '>=', $startDate);
        }
        if ($req->endDate != '') {
            $endDate = Carbon::parse($req->endDate)->format('Y-m-d');
            $savAccs->where('sa.openingDate', '<=', $endDate);
        }

        $totalData = (clone $savAccs)->count();
        $savAccs   = $savAccs->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;
        foreach ($savAccs as $key => $savAcc) {
            $savAccs[$key]->openingDate = Carbon::parse($savAcc->openingDate)->format('d-m-Y');
            $savAccs[$key]->status      = $savAcc->closingDate == '0000-00-00' ? 'Active' : 'Inactive';
            $savAccs[$key]->closingDate = ($savAcc->closingDate == '0000-00-00' ||  $savAcc->closingDate == null) ? '' : Carbon::parse($savAcc->closingDate)->format('d-m-Y');
            $savAccs[$key]->sl          = $sl++;
            $savAccs[$key]->id          = encrypt($savAcc->id);
            $savAccs[$key]->action      = RoleService::roleWiseArray($this->GlobalRole, $savAccs[$key]->id);
        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $savAccs,
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

        $savProductIds = MfnService::getBranchAssignedSavProductIds(Auth::user()->branch_id);

        $savProducts = DB::table('mfn_savings_product')
            ->where([
                ['is_delete', 0],
                ['status', 1],
                ['effectiveDate', '<=', $sysDate],
            ])
            ->whereIn('id', $savProductIds)
            ->get();

        $isOpening = MfnService::isOpening(Auth::user()->branch_id);

        $data = array(
            'sysDate'     => $sysDate,
            'isOpening'   => $isOpening,
            'members'     => $members,
            'savProducts' => $savProducts,
        );

        return view('MFN.Savings.Account.add', $data);
    }

    public function store($req)
    {
        $isOpening = MfnService::isOpening(Auth::user()->branch_id);

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
            $member         = DB::table('mfn_members')->where('id', $req->memberId)->first();
            $savingsProduct = DB::table('mfn_savings_product')->where('id', $req->savingsProductId)->first();

            if ($savingsProduct->productTypeId == 1) { // regular deposit
                $autoProcessAmount = $req->autoProcessAmount;
                $interestRate      = $req->regularInterestRate;
                $periodMonth       = 0;
                $matureDate        = '0000-00-00';
            } elseif ($savingsProduct->productTypeId == 2) { // one time deposit
                $autoProcessAmount = $req->onetimeDepositAmount;
                $interestRate      = $req->onetimeInterestRate;
                $periodMonth       = $req->period;
                $matureDate        = Carbon::parse($req->openingDate)->addMonthsNoOverflow($req->period)->format('Y-m-d');
            }

            if (isset($req->savingsCycle)) {
                $savingsCycle = $req->savingsCycle;
            } else {
                $mfnGnlConfig = json_decode(DB::table('mfn_config')->where('title', 'general')->first()->content);
                $savingsCycle = explode($mfnGnlConfig->codeSeperator, $req->accountCode);
                $savingsCycle = (int)end($savingsCycle);
            }

            $savAcc                    = new SavingsAccount;
            $savAcc->accountCode       = $req->accountCode;
            $savAcc->mraCode           = $this->generateMraCode($req->accountCode);
            $savAcc->memberId          = $member->id;
            $savAcc->branchId          = $member->branchId;
            $savAcc->samityId          = $member->samityId;
            $savAcc->savingsProductId  = $req->savingsProductId;
            $savAcc->autoProcessAmount = $autoProcessAmount;
            $savAcc->interestRate      = $interestRate;
            $savAcc->openingDate       = Carbon::parse($req->openingDate)->format('Y-m-d');
            $savAcc->periodMonth       = $periodMonth;
            $savAcc->matureDate        = $matureDate;
            $savAcc->isMandatory       = isset($req->isMandatory) ? $req->isMandatory : 0;
            $savAcc->isOpening         = $isOpening ? 1 : 0;
            $savAcc->savingsCycle      = $savingsCycle;
            $savAcc->created_by        = Auth::user()->id;
            $savAcc->created_at        = Carbon::now();
            $savAcc->save();

            MfnService::sendMail('mfn_savings_accounts', $savAcc->memberId, $savAcc->created_at);

            if ($isOpening) {
                MfnService::setOpeningBalanceForOneTimeSavings($savAcc->branchId, $savAcc->id);
            }

            if (!$isOpening) {
                // if it is one time account then store deposit
                if ($savingsProduct->productTypeId == 2) {
                    $depositReq = new Request;
                    $depositReq->merge([
                        "memberId"          => $savAcc->memberId,
                        "accountId"         => $savAcc->id,
                        "date"              => $savAcc->openingDate,
                        "amount"            => $savAcc->autoProcessAmount,
                        "transactionTypeId" => $req->transactionTypeId,
                        "ledgerId"          => $req->ledgerId,
                        "chequeNo"          => $req->chequeNo,
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

    public function edit(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->update($req);
        }

        $savAcc = SavingsAccount::find(decrypt($req->id));

        $member = DB::table('mfn_members')
            ->where('id', $savAcc->memberId)
            ->select(DB::raw("conCAT(name, ' - ', memberCode) AS member"))
            ->value('member');

        $DipositeInfo = DB::table('mfn_savings_deposit')
            ->where([
                ['is_delete', 0],
                ['accountId', $savAcc->id],
                ['date', $savAcc->openingDate],
            ])
            ->first();

        $savProduct = DB::table('mfn_savings_product')
            ->where('id', $savAcc->savingsProductId)
            ->select(DB::raw("conCAT(productCode, ' - ', name) AS savProduct"))
            ->value('savProduct');

        $isOpening = MfnService::isOpening($savAcc->branchId) && $savAcc->isOpening;

        $mfnGnlConfig = json_decode(DB::table('mfn_config')->where('title', 'general')->first()->content);

        $sysDate = MfnService::systemCurrentDate($savAcc->branchId);

        $data = array(
            'savAcc'     => $savAcc,
            'isOpening'  => $isOpening,
            'sysDate'    => $sysDate,
            'deposit'    => $DipositeInfo,
            'member'     => $member,
            'savProduct' => $savProduct,
        );

        return view('MFN.Savings.Account.edit', $data);
    }

    public function view($id)
    {
        $savingsAcc = SavingsAccount::find(decrypt($id));
        if (Auth::user()->branch_id != 1 && Auth::user()->branch_id != $savingsAcc->branchId) {
            return '';
        }
        $member = DB::table('mfn_members')
            ->where('id', $savingsAcc->memberId)
            ->select(DB::raw("conCAT(name, ' - ', memberCode) AS member"))
            ->first();

        $savProduct = DB::table('mfn_savings_product')
            ->where('id', $savingsAcc->savingsProductId)
            ->select(DB::raw("conCAT(productCode, ' - ', name) AS savProduct"), 'productTypeId', 'collectionFrequencyId')
            ->first();

        if ($savProduct->productTypeId == 2) {
            $collFreq = 'Onetime';
        } else {
            $collFreq = DB::table('mfn_savings_collection_frequency')->where('id', $savProduct->collectionFrequencyId)->first()->name;
        }

        $prodType = DB::table('mfn_loan_product_types')->where('id', $savProduct->productTypeId)->first()->name;

        $filters['accountId'] = $savingsAcc->id;
        $Balance              = MfnService::getSavingsBalance($filters);

        if ($prodType == 'One Time') {
            $payableAmt = ($savingsAcc->periodMonth / 12) * ($savingsAcc->autoProcessAmount * ($savingsAcc->interestRate / 100)) + $savingsAcc->autoProcessAmount;
        } else {
            $payableAmt = "0";
        }

        $data = array(
            'member'     => $member,
            'savProduct' => $savProduct,
            'savingsAcc' => $savingsAcc,
            'prodType'   => $prodType,
            'Balance'    => $Balance,
            'collFreq'   => $collFreq,
            'payableAmt' => $payableAmt,

        );

        return view('MFN.Savings.Account.view', $data);
    }

    public function update($req)
    {
        $savAcc = SavingsAccount::find(decrypt($req->id));

        $passport = $this->getPassport($req, $operationType = 'update', $savAcc);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $isOpening = MfnService::isOpening($savAcc->branchId) && $savAcc->isOpening;

        // update data
        DB::beginTransaction();

        try {
            $savingsProduct = DB::table('mfn_savings_product')->where('id', $savAcc->savingsProductId)->first();

            if ($savingsProduct->productTypeId == 1) { // regular deposit
                $autoProcessAmount = $req->autoProcessAmount;
                $interestRate      = $req->regularInterestRate;
                $periodMonth       = 0;
                $matureDate        = '0000-00-00';
            } elseif ($savingsProduct->productTypeId == 2) { // one time deposit
                $autoProcessAmount = $req->onetimeDepositAmount;
                $interestRate      = $req->onetimeInterestRate;
                $periodMonth       = $req->period;
                $matureDate        = Carbon::parse($req->openingDate)->addMonthsNoOverflow($req->period)->format('Y-m-d');
            }

            if (isset($req->savingsCycle)) {
                $savingsCycle = $req->savingsCycle;
            } else {
                $mfnGnlConfig = json_decode(DB::table('mfn_config')->where('title', 'general')->first()->content);
                $savingsCycle = explode($mfnGnlConfig->codeSeperator, $req->accountCode);
                $savingsCycle = (int)end($savingsCycle);
            }

            if ($isOpening) {
                $savAcc->accountCode  = $req->accountCode;
                $savAcc->mraCode      = $this->generateMraCode($req->accountCode);
                $savAcc->openingDate  = Carbon::parse($req->openingDate)->format('Y-m-d');
                $savAcc->savingsCycle = $savingsCycle;
            }

            $savAcc->autoProcessAmount = $autoProcessAmount;
            $savAcc->interestRate      = $interestRate;
            $savAcc->periodMonth       = $periodMonth;
            $savAcc->matureDate        = $matureDate;

            $savAcc->updated_by = Auth::user()->id;
            $savAcc->updated_at = Carbon::now();
            $savAcc->save();

            MfnService::sendMail('mfn_savings_accounts', $savAcc->memberId, $savAcc->created_at, true);

            // if it is one time account then update deposit
            if ($savingsProduct->productTypeId == 2) {
                if ($isOpening) {
                    MfnService::setOpeningBalanceForOneTimeSavings($savAcc->branchId, $savAcc->id);
                } else {
                    // get the deposit
                    $deposit = DB::table('mfn_savings_deposit')
                        ->where([
                            ['is_delete', 0],
                            ['accountId', $savAcc->id],
                            ['date', $savAcc->openingDate],
                        ])
                        ->first();

                    $depositReq = new Request;
                    $depositReq->merge([
                        "id"                => encrypt($deposit->id),
                        "reqFromAccount"    => true,
                        "amount"            => $savAcc->autoProcessAmount,
                        "date"              => $savAcc->openingDate,
                        "transactionTypeId" => $req->transactionTypeId,
                        "ledgerId"          => $req->ledgerId,
                        "chequeNo"          => $req->chequeNo,
                    ]);

                    $response = app('App\Http\Controllers\MFN\Savings\DepositController')->update($depositReq)->getData();

                    if ($response->{'alert-type'} == 'error') {
                        $notification = array(
                            'message'    => $response->message,
                            'alert-type' => 'error',
                        );
                        return response()->json($notification);
                    }
                }
            }

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
        $savAcc = SavingsAccount::find(decrypt($req->id));

        $passport = $this->getPassport($req, $operationType = 'delete', $savAcc);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {

            // if it is one time account then delete corresponding deposit

            // delete the deposits
            $deposits = DB::table('mfn_savings_deposit')
                ->where([
                    ['is_delete', 0],
                    ['accountId', $savAcc->id],
                    // ['date', $savAcc->openingDate],
                ])
                ->get();

            foreach ($deposits as  $deposit) {
                $depositReq = new Request;
                $depositReq->merge([
                    "id"             => encrypt($deposit->id),
                    "reqFromAccount" => true,
                ]);

                $response = app('App\Http\Controllers\MFN\Savings\DepositController')->delete($depositReq)->getData();

                if ($response->{'alert-type'} == 'error') {
                    $notification = array(
                        'message'    => $response->message,
                        'alert-type' => 'error',
                    );
                    return response()->json($notification);
                }
            }

            # code for delete withdraws

            $savAcc->is_delete = 1;
            $savAcc->save();

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

    public function getPassport($req, $operationType, $savAcc = null)
    {
        $errorMsg = null;

        // set required valiables
        if ($operationType == 'store') {
            $sysDate            = MfnService::systemCurrentDate(Auth::user()->branch_id);
            $accountOpeningDate = Carbon::parse($req->openingDate)->format('Y-m-d');
            $member             = DB::table('mfn_members')->where('id', $req->memberId)->first();
            $isOpening          = MfnService::isOpening(Auth::user()->branch_id);
            $branchId           = Auth::user()->branch_id;
            $savProduct = DB::table('mfn_savings_product')->where('id', $req->savingsProductId)->first();
        } else {
            $sysDate            = MfnService::systemCurrentDate($savAcc->branchId);
            $accountOpeningDate = $savAcc->openingDate;
            $member             = DB::table('mfn_members')->where('id', $savAcc->memberId)->first();
            $isOpening          = $savAcc->isOpening;
            $branchId           = $savAcc->branchId;
            $savProduct = DB::table('mfn_savings_product')->where('id', $savAcc->savingsProductId)->first();
        }

        if ($isOpening && $operationType != 'delete') {
            $accountOpeningDate = Carbon::parse($req->openingDate)->format('Y-m-d');
        }

        if ($operationType != 'delete') {

            $rules = array();

            if ($operationType == 'store') {
                $rules = array(
                    'memberId'         => 'required',
                    'savingsProductId' => 'required',
                    'openingDate'       => 'required|date',
                    'accountCode'      => ['required', new Unique('mfn_savings_accounts')],
                );
            }

            if ($operationType == 'update') {
                $rules = array(
                    'accountCode' => ['required', new Unique('mfn_savings_accounts', $savAcc->id)],
                );
            }

            if ($req->savingsProductId != '') {

                if ($savProduct->productTypeId == 1) { // if regular product
                    $rules = array_merge($rules, array(
                        'autoProcessAmount'   => 'required',
                        'regularInterestRate' => 'required|numeric',
                    ));
                } elseif ($savProduct->productTypeId == 2) { // if one time product
                    $rules = array_merge($rules, array(
                        'period'               => 'required|numeric',
                        'onetimeInterestRate'  => 'required|numeric',
                        'onetimeDepositAmount' => 'required|numeric',
                    ));

                    if (!$isOpening) {
                        $rules['transactionTypeId'] = 'required';

                        if ($req->transactionTypeId == 2) { // if it is Bank
                            $rules['ledgerId'] = 'required';
                            $rules['chequeNo'] = 'required';
                        }
                    }
                }
            }

            $validator = Validator::make($req->all(), $rules);

            $attributes = array(
                'memberId'             => 'Member ',
                'savingsProductId'     => 'product',
                'autoProcessAmount'    => 'Auto Process Amount',
                'period'               => 'Period',
                'onetimeDepositAmount' => 'Deposit Amount',
                'transactionTypeId'    => 'Deposit By',
                'ledgerId'             => 'Bank Account',
                'chequeNo'             => 'Cheque No',
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }

            // if it is one time product then check the account opening limitation            
            if ($savProduct->productTypeId == 2) { // if one time product
                if ($req->onetimeDepositAmount < $savProduct->minimumSavingsBalance) {
                    $errorMsg = 'Minimum Savings balance should be ' . $savProduct->minimumSavingsBalance;
                }
            }

            /// re-validate the incoming data
            if ($errorMsg != null) {
                if ($operationType == 'update') {
                    $req->merge([
                        'memberId'         => $savAcc->memberId,
                        'savingsProductId' => $savAcc->savingsProductId,
                    ]);
                }
                $reValidate = $this->reValidateData($req, $isOpening);

                if ($reValidate !== true) {
                    $errorMsg = $reValidate;
                }
            }

            if ($accountOpeningDate < $member->admissionDate) {
                $errorMsg = 'Account Opening date could not be less than Member Admission date.';
            }

            // if savings cycle exists into request than, check savings cycle already exists or not
            // savings cycle should be product wise
            if (isset($req->savingsCycle)) {
                $savCycleExists = DB::table('mfn_savings_accounts')
                    ->where([
                        ['is_delete', 0],
                        ['memberId', $member->id],
                        ['savingsProductId', $savProduct->id],
                        ['savingsCycle', $req->savingsCycle],
                    ]);

                $operationType == 'update' ? $savCycleExists->where('id', '!=', $savAcc->id) : false;

                if ($savCycleExists->exists()) {
                    $errorMsg = 'This Savings Cycle alreay exists.';
                }
            }

            // Check multiple account is allowed or not, base on product configuration
            if ($savProduct->isMultipleSavingsAllowed == 'No' && $operationType == 'store') {
                $numberOfAccount = DB::table('mfn_savings_accounts')
                    ->where([
                        ['is_delete', 0],
                        ['memberId', $member->id],
                        ['savingsProductId', $savProduct->id],
                    ])
                    ->count();

                if ($numberOfAccount > 0) {
                    $errorMsg = 'Sorry, multiple account is not allowed for this savings product.';
                }
            }
        }

        // check branch date is equal to account opening date or not if it it not from opening
        if (!$isOpening && $sysDate != $accountOpeningDate) {
            $errorMsg = 'Branch date is not equal to Account opening date.';
        }

        // if it is from opening
        if ($isOpening) {
            $branchSoftwareStartDate = DB::table('gnl_branchs')->where('id', $branchId)->value('mfn_start_date');

            if ($sysDate != $branchSoftwareStartDate) {
                $errorMsg = 'Branch should be on Software start date ' . Carbon::parse($branchSoftwareStartDate)->format('d-m-Y');
            }
        }

        if ($operationType == 'update' || $operationType == 'delete') {
            // this can be updated/deleted from head office and corresponding branch
            if (Auth::user()->branch_id != 1 && Auth::user()->branch_id != $savAcc->branchId) {
                $errorMsg = "This can be updated/deleted from head office and corresponding branch.";
            }

            // if any transaction exists then could not be updated/deleted
            $depositExists = DB::table('mfn_savings_deposit')
                ->where([
                    ['is_delete', 0],
                    ['amount', '!=', 0],
                    ['accountId', $savAcc->id],
                ]);

            // if it is one time account then don't consider the auto generated deposit
            if ($savProduct->productTypeId == 2) { // if one time product
                $depositId = DB::table('mfn_savings_deposit')
                    ->where([
                        ['is_delete', 0],
                        ['amount', '!=', 0],
                        ['accountId', $savAcc->id],
                    ])
                    ->value('id');

                $depositExists->where('id', '!=', $depositId);
            }

            $depositExists = $depositExists->exists();

            $withdrawExists = DB::table('mfn_savings_withdraw')
                ->where([
                    ['is_delete', 0],
                    ['amount', '!=', 0],
                    ['accountId', $savAcc->id],
                ])
                ->exists();

            if ($depositExists || $withdrawExists) {
                $errorMsg = "Transaction exists";
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid'  => $isValid,
            'errorMsg' => $errorMsg,
        );

        return $passport;
    }

    public function reValidateData($req, $isOpening = false)
    {
        $errorMsg = null;
        $product  = DB::table('mfn_savings_product')->where('id', $req->savingsProductId)->first();
        $sysDate  = MfnService::systemCurrentDate(Auth::user()->branch_id);

        // check savings account is correct or not
        $savData              = [];
        $savData['memberId']  = $req->memberId;
        $savData['productId'] = $req->savingsProductId;
        if ($isOpening == true) {
            $savData['savingsCycle'] = $req->savingsCycle;
        }
        $accountCode = self::generateSavingsCode($savData);

        if ($accountCode != $req->accountCode) {
            $errorMsg = 'Account Code not matched.';
        }

        // check interest rate is correct or not
        if ($product->productTypeId == 1) { // if it is regular
            $interestRate = MfnService::getSavingsRegularProductInterestRate($product->id, $sysDate);

            if ($interestRate != $req->regularInterestRate) {
                $errorMsg = 'Interest Rate is invalid.';
            }
        } elseif ($product->productTypeId == 2) { // if it is one time
            $interestRates = MfnService::getSavingsOnetimeProductInterestRates($product->id, $sysDate);

            if (!isset($interestRates[$req->period])) {
                $errorMsg = 'Period is invalid.';
            } elseif ($interestRates[$req->period] != $req->onetimeInterestRate) {
                $errorMsg = 'Interest Rate is invalid.';
            }
        }

        return $errorMsg == null ? true : $errorMsg;
    }

    public static function generateSavingsCode($data)
    {
        $mfnGnlConfig  = json_decode(DB::table('mfn_config')->where('title', 'general')->first()->content);
        $codeSeperator = $mfnGnlConfig->codeSeperator;

        $mfnSavingsConfig        = json_decode(DB::table('mfn_config')->where('title', 'savings')->first()->content);
        $savingsCodeLengthItSelf = $mfnSavingsConfig->savingsCodeLengthItSelf;

        if (isset($data['memberCode'])) {
            $memberCode = $data['memberCode'];

            if (isset($data['savingsCycle'])) {
                $savingsCycle = $data['savingsCycle'];
            } else {
                $savingsCycle = 1;
            }
        } else {
            $memberCode = DB::table('mfn_members')->where('id', $data['memberId'])->first()->memberCode;

            if (isset($data['savingsCycle'])) {
                $savingsCycle = $data['savingsCycle'];
            } else {
                $savingsCodes = DB::table('mfn_savings_accounts')->where([
                    ['is_delete', 0],
                    ['memberId', $data['memberId']],
                ]);

                // if savings code generation depends on savings product
                $mfnSavingsConfig = json_decode(DB::table('mfn_config')->where('title', 'savings')->first()->content);
                if ($mfnSavingsConfig->isSavingsCycleDependsOnProduct == 'yes') {
                    $savingsCodes->where('savingsProductId', $data['productId']);
                }

                $savingsCodes = $savingsCodes->pluck('accountCode')
                    ->all();
                $maxCycle = 0;
                foreach ($savingsCodes as $savingsCode) {
                    $spilts = explode($codeSeperator, $savingsCode);
                    if ((int)end($spilts) > $maxCycle) {
                        $maxCycle = (int)end($spilts);
                    }
                }
                $savingsCycle = $maxCycle + 1;
            }
        }

        $savingsCode = $memberCode . $codeSeperator . str_pad($savingsCycle, $savingsCodeLengthItSelf, "0", STR_PAD_LEFT);

        if ($mfnSavingsConfig->isProductPrefixRequiredInSavingsCode) {
            $productShortName = DB::table('mfn_savings_product')->where('id', $data['productId'])->first()->shortName;

            $savingsCode = $productShortName . $codeSeperator . $savingsCode;
        }

        return $savingsCode;
    }

    public function getData(Request $req)
    {
        $isOpening = MfnService::isOpening(Auth::user()->branch_id);

        if ($isOpening) {
            $sysDate = Carbon::parse($req->openingDate)->format('Y-m-d');
        } else {
            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
        }

        if ($req->context == 'savingsCode') {
            $savData              = [];
            $savData['productId'] = $req->savProductId;
            $savData['memberId']  = $req->memberId;

            if ($isOpening) {
                $savData['savingsCycle'] = $req->savingsCycle;
            }

            $savCode = self::generateSavingsCode($savData);

            $data = array(
                'savCode' => $savCode,
            );
        }

        if ($req->context == 'product') {
            $product     = DB::table('mfn_savings_product')->where('id', $req->savProductId)->first();
            $productType = DB::table('mfn_savings_product_type')->where('id', $product->productTypeId)->value('name');

            if ($product->productTypeId == 2) {
                $collectionFrequency = 'One Time';
            } else {
                $collectionFrequency = DB::table('mfn_savings_collection_frequency')->where('id', $product->collectionFrequencyId)->value('name');
            }

            // get product interest rate if product is regular
            $regularInterestRate = null;
            if ($product->productTypeId == 1) {
                $regularInterestRate = MfnService::getsavingsRegularProductInterestRate($product->id, $sysDate);

                if ($regularInterestRate == null) {
                    $regularInterestRate = 'shouldDefine';
                }
            }

            // if product is one time then get interest rates based on duration
            $durationInterests = [];
            if ($product->productTypeId == 2) {
                $durationInterests = MfnService::getSavingsOnetimeProductInterestRates($product->id, $sysDate);

                if (count($durationInterests) == 0) {
                    $durationInterests = 'shouldDefine';
                }
            }

            $data = array(
                'productTypeId'       => $product->productTypeId,
                'productType'         => $productType,
                'collectionFrequency' => $collectionFrequency,
                'regularInterestRate' => $regularInterestRate,
                'durationInterests'   => $durationInterests,
            );
        }

        return response()->json($data);
    }

    public static function generateMraCode($savingsCode)
    {
        $mfnGnlConfig = json_decode(DB::table('mfn_config')->where('title', 'general')->first()->content);
        if ($mfnGnlConfig->companyType != 'ngo') {
            return '';
        }

        $codeSeperator  = $mfnGnlConfig->codeSeperator;
        // $savingsMraCode = $mfnGnlConfig->mfiCode . str_replace($codeSeperator, '', $savingsCode);
        $savingsMraCode = str_replace($codeSeperator, '', $savingsCode);

        return $savingsMraCode;
    }

    /**
     * This function is used to open mendatory savings account for those members which have no mendatory savings account ever. This is only applicable for opening members opening account.
     * 
     * *** This Function is only did for USHA Foundation on thier demand. ***
     *
     * @param   int $productId
     * @param   int $branchId
     * @param   int (default null) $samityId
     * @param   int (default null) $memberId
     *
     * @return  [void]
     */
    public static function openMendatoryRegularSavingsAccount($productId, $branchId, $samityId = null, $memberId = null)
    {
        DB::beginTransaction();

        try {
            //check $productId is in mendatory savings account or not
            $mendatorySavingsProductIds = DB::table('mfn_savings_product')
                ->where([
                    ['is_delete', 0],
                    ['productTypeId', 1], // 1 for Regular product
                    ['isMandatoryOnMemberAdmission', 1],
                ])
                ->pluck('id')
                ->toArray();

            if (!in_array($productId, $mendatorySavingsProductIds)) {
                return false;
            }
            if ($branchId == null) {
                return 'Please give branch id';
            }

            $memberIdsHavingsSavAccounts = DB::table('mfn_savings_accounts')
                ->where([
                    ['is_delete', 0],
                    ['savingsProductId', $productId],
                ])
                ->groupBy('memberId');

            if ($branchId != null) {
                $memberIdsHavingsSavAccounts->where('branchId', $branchId);
            }
            if ($samityId != null) {
                $memberIdsHavingsSavAccounts->where('samityId', $samityId);
            }
            if ($memberId != null) {
                $memberIdsHavingsSavAccounts->where('memberId', $memberId);
            }

            $memberIdsHavingsSavAccounts = $memberIdsHavingsSavAccounts->pluck('memberId')->toArray();

            $branch  = DB::table('gnl_branchs')->where('id', $branchId)->first();

            // select those members which have no savings accounts of target product
            $members = DB::table('mfn_members')
                ->where([
                    ['is_delete', 0],
                    ['admissionDate', '<=', $branch->mfn_start_date],
                ])
                ->whereNotIn('id', $memberIdsHavingsSavAccounts);

            if ($branchId != null) {
                $members->where('branchId', $branchId);
            }
            if ($samityId != null) {
                $members->where('samityId', $samityId);
            }
            if ($memberId != null) {
                $members->where('id', $memberId);
            }

            $members = $members->get();

            $accounts = array();

            foreach ($members as $key => $member) {
                // insert a savings account of that particular product
                $interestRate = MfnService::getsavingsRegularProductInterestRate($productId, $member->admissionDate);

                $savData['productId'] = $productId;
                $savData['memberId']  = $member->id;
                $savingsCode = self::generateSavingsCode($savData);

                $mraCode = self::generateMraCode($savingsCode);

                $account = [
                    'accountCode'       => $savingsCode,
                    'mraCode'           => $mraCode,
                    'memberId'          => $member->id,
                    'branchId'          => $member->branchId,
                    'samityId'          => $member->samityId,
                    'savingsProductId'  => $productId,
                    'autoProcessAmount' => 100,
                    'interestRate'      => $interestRate,
                    'openingDate'       => $member->admissionDate,
                    'isAuthorized'      => 1,
                    'isMandatory'       => 1,
                    'isOpening'         => 1,
                    'savingsCycle'      => 1,
                    'created_by'        => 0,
                    'created_at'        => date('Y-m-d H:m:s'),
                    'updated_at'        => date('Y-m-d H:m:s'),
                ];

                array_push($accounts, $account);
            }

            DB::table('mfn_savings_accounts')
                ->insert($accounts);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollback();
        }
    }
}
