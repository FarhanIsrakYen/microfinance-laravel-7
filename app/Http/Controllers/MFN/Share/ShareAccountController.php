<?php

namespace App\Http\Controllers\MFN\Share;

use App\Http\Controllers\Controller;
use App\Model\MFN\ShareAccount;
use App\Model\MFN\Share;
use App\Rules\Unique;
use App\Services\HrService;
use App\Services\MfnService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class ShareAccountController extends Controller
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
                ->select('id', 'branch_name','branch_code')
                ->get();

            $samities = MfnService::getSamities($branchList->pluck('id')->toArray());

            $data = array(
                'branchList' => $branchList,
                'samities' => $samities,
            );

            return view('MFN.Share.Account.index', $data);
        }

        $columns = [
                    'sa.id', 
                    'm.name',  
                    'branch.branch_name', 
                    'samity.name',
                    'sa.purchaseDate', 
                    'sa.numberOfShare', 
                    'sa.totalPrice', 
                    'sa.closingDate', 
                    'emp.emp_name'
                ];

        $limit = $req->length;
        $orderColumnIndex = (int) $req->input('order.0.column') <= 1 ? 0 : (int) $req->input('order.0.column') - 1;
        $order = $columns[$orderColumnIndex];
        $dir = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $shareAccs = DB::table('mfn_share_accounts AS sa')->where('sa.is_delete',0)
            ->leftJoin('mfn_members AS m', 'm.id', 'sa.memberId')
            ->leftJoin('gnl_branchs AS branch', 'branch.id', 'sa.branchId')
            ->leftJoin('mfn_samity AS samity', 'samity.id', 'sa.samityId')
            ->leftJoin('hr_employees AS emp', 'emp.user_id', 'sa.created_by')
            ->whereIn('m.branchId', $accessAbleBranchIds)
            ->where('sa.is_delete', 0)
            ->select('m.name AS member', 'branch.branch_name AS branchName', 'samity.name AS samityName', 'emp.emp_name AS empName', 'sa.*')
            ->orderBy($order, $dir);

        if ($search != null) {
            $shareAccs->where(function ($query) use ($search) {
                $query->Where('m.name', 'LIKE', "%$search%")
                    ->orWhere('branch.branch_name', 'LIKE', "%$search%")
                    ->orWhere('samity.name', 'LIKE', "%$search%");
            });
        }

        if ($req->filBranch != '') {
            $shareAccs->where('sa.branchId', $req->filBranch);
        }
        if ($req->filSamity != '') {
            $shareAccs->where('sa.samityId', $req->filSamity);
        }
        if ($req->savingsCode != '') {
            // $shareAccs->where('sa.accountCode', 'LIKE', "%$req->savingsCode%");
        }
        if ($req->startDate != '') {
            $startDate = Carbon::parse($req->startDate)->format('Y-m-d');
            $shareAccs->where('sa.purchaseDate', '>=', $startDate);
        }
        if ($req->endDate != '') {
            $endDate = Carbon::parse($req->endDate)->format('Y-m-d');
            $shareAccs->where('sa.purchaseDate', '<=', $endDate);
        }

        $totalData = (clone $shareAccs)->count();
        $shareAccs = $shareAccs->limit($limit)->offset($req->start)->get();

        $sl = (int) $req->start + 1;
        foreach ($shareAccs as $key => $ShareAcc) {
            $shareAccs[$key]->purchaseDate = Carbon::parse($ShareAcc->purchaseDate)->format('d-m-Y');
            $shareAccs[$key]->status = $ShareAcc->closingDate == '0000-00-00' ? 'Active' : 'Inactive';
            $shareAccs[$key]->sl = $sl++;
            $shareAccs[$key]->id = encrypt($ShareAcc->id);
        }

        $data = array(
            "draw" => intval($req->input('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalData,
            'data' => $shareAccs,
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

        // $savProducts = DB::table('mfn_savings_product')
        //     ->where([
        //         ['is_delete', 0],
        //         ['status', 1],
        //         ['effectiveDate', '<=', $sysDate],
        //     ])
        //     ->get();

        $isOpening = MfnService::isOpening(Auth::user()->branch_id);

        $data = array(
            'sysDate' => $sysDate,
            'isOpening' => $isOpening,
            'members' => $members,
            // 'savProducts' => $savProducts,
        );

        return view('MFN.Share.Account.add', $data);
    }

    public function store($req)
    {
        // $isOpening = MfnService::isOpening(Auth::user()->branch_id);

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
            $member = DB::table('mfn_members')->where('id', $req->memberId)->first();
            

            $shareAcc = new ShareAccount;
            $shareAcc->memberId = $req->memberId;
            $shareAcc->branchId = $member->branchId;
            $shareAcc->samityId = $member->samityId;
            $shareAcc->unitPrice = $req->unitPrice;
            $shareAcc->numberOfShare = $req->numberOfShare;
            $shareAcc->totalPrice = $req->totalPrice;
            $shareAcc->purchaseDate = Carbon::parse($req->purchaseDate)->format('Y-m-d');
            $shareAcc->status = 'Active';
            $shareAcc->created_by = Auth::user()->id;
            $shareAcc->created_at = Carbon::now();
            $shareAcc->save();

            

            DB::commit();
            $notification = array(
                'message' => 'Successfully Inserted',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message' => 'Something went wrong',
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

        $ShareAcc = ShareAccount::find(decrypt($req->id));

   

        $member = DB::table('mfn_members')
            ->where('id', $ShareAcc->memberId)
            ->select(DB::raw("conCAT(name, ' - ', memberCode) AS member"))
            ->value('member');


        $sysDate = MfnService::systemCurrentDate($ShareAcc->branchId);

        $data = array(
            'ShareAcc' => $ShareAcc,
            'sysDate' => $sysDate,
            'member' => $member,
        );

        return view('MFN.Share.Account.edit', $data);
    }

    public function view($id)
    {
        $ShareAcc = ShareAccount::find(decrypt($id));
        if (Auth::user()->branch_id != 1 && Auth::user()->branch_id != $ShareAcc->branchId) {
            return '';
        }
        $member = DB::table('mfn_members')
            ->where('id', $ShareAcc->memberId)
            ->select(DB::raw("conCAT(name, ' - ', memberCode) AS member"))
            ->first();

       

        $data = array(
            'member' => $member,
            'ShareAcc' => $ShareAcc,
            
        );

        return view('MFN.Share.Account.view', $data);
    }

    public function update($req)
    {
        $ShareAcc = ShareAccount::find(decrypt($req->id));

        // $passport = $this->getPassport($req, $operationType = 'update', $ShareAcc);
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
           
            $ShareAcc->unitPrice = $req->unitPrice;
            $ShareAcc->numberOfShare =$req->numberOfShare;
            $ShareAcc->totalPrice =$req->totalPrice;

            $ShareAcc->updated_by = Auth::user()->id;
            $ShareAcc->updated_at = Carbon::now();
            $ShareAcc->save();


            DB::commit();
            $notification = array(
                'message' => 'Successfully Updated',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message' => 'Something went wrong',
                'consoleMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );

            return response()->json($notification);
        }
    }

    public function delete(Request $req)
    {

        $ShareAcc = ShareAccount::find(decrypt($req->id));

        // $passport = $this->getPassport($req, $operationType = 'delete', $ShareAcc);
        // if ($passport['isValid'] == false) {
        //     $notification = array(
        //         'message' => $passport['errorMsg'],
        //         'alert-type' => 'error',
        //     );
        //     return response()->json($notification);
        // }

        DB::beginTransaction();

        try {

            $ShareAcc->is_delete = 1;
            $ShareAcc->save();

            // if it is one time account then delete corresponding deposit

            
            DB::commit();
            $notification = array(
                'message' => 'Successfully Deleted',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message' => 'Something went wrong',
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
            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
            $accountOpeningDate = Carbon::parse($req->openingDate)->format('Y-m-d');
            $member = DB::table('mfn_members')->where('id', $req->memberId)->first();
            $isOpening = MfnService::isOpening(Auth::user()->branch_id);
            $branchId = Auth::user()->branch_id;
        } else {
            $sysDate = MfnService::systemCurrentDate($savAcc->branchId);
            $accountOpeningDate = $savAcc->openingDate;
            $member = DB::table('mfn_members')->where('id', $savAcc->memberId)->first();
            $isOpening = $savAcc->isOpening;
            $branchId = $savAcc->branchId;
        }

        if ($isOpening && $operationType != 'delete') {
            $accountOpeningDate = Carbon::parse($req->openingDate)->format('Y-m-d');
        }

        if ($operationType != 'delete') {

            $rules = array();

            if ($operationType == 'store') {
                $rules = array(
                    'memberId' => 'required',
                    'savingsProductId' => 'required',
                    'accountCode' => ['required', new Unique('mfn_savings_accounts')],
                );
            }

            if ($operationType == 'update') {
                $rules = array(
                    'accountCode' => ['required', new Unique('mfn_savings_accounts', $savAcc->id)],
                );
            }

            if ($req->savingsProductId != '') {
                $savProduct = DB::table('mfn_savings_product')->where('id', $req->savingsProductId)->first();

                if ($savProduct->productTypeId == 1) { // if regular product
                    $rules = array_merge($rules, array(
                        'autoProcessAmount' => 'required',
                        'regularInterestRate' => 'required|numeric',
                    ));
                } elseif ($savProduct->productTypeId == 2) { // if one time product
                    $rules = array_merge($rules, array(
                        'period' => 'required|numeric',
                        'onetimeInterestRate' => 'required|numeric',
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
                'memberId' => 'Member ',
                'savingsProductId' => 'product',
                'autoProcessAmount' => 'Auto Process Amount',
                'period' => 'Period',
                'onetimeDepositAmount' => 'Deposit Amount',
                'transactionTypeId' => 'Deposit By',
                'ledgerId' => 'Bank Account',
                'chequeNo' => 'Cheque No',
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }

            /// re-validate the incoming data
            if ($errorMsg != null) {
                if ($operationType == 'update') {
                    $req->merge([
                        'memberId' => $savAcc->memberId,
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
            if (isset($req->savingsCycle)) {
                $savCycleExists = DB::table('mfn_savings_accounts')
                    ->where([
                        ['is_delete', 0],
                        ['memberId', $member->id],
                        ['savingsCycle', $req->savingsCycle],
                    ]);

                $operationType == 'update' ? $savCycleExists->where('id', '!=', $savAcc->id) : false;

                if ($savCycleExists->exists()) {
                    $errorMsg = 'This Savings Cycle alreay exists.';
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
            $savProduct = DB::table('mfn_savings_product')->where('id', $savAcc->savingsProductId)->first();

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
                    ['accountId', $savAcc->id],
                ])
                ->exists();

            if ($depositExists || $withdrawExists) {
                $errorMsg = "Transaction exists";
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg,
        );

        return $passport;
    }

    public function reValidateData($req, $isOpening = false)
    {
        $errorMsg = null;
        $product = DB::table('mfn_savings_product')->where('id', $req->savingsProductId)->first();
        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        // check savings account is correct or not
        $savData = [];
        $savData['memberId'] = $req->memberId;
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

   

    public function getData(Request $req)
    {
        $isOpening = MfnService::isOpening(Auth::user()->branch_id);

        if ($isOpening) {
            $sysDate = Carbon::parse($req->openingDate)->format('Y-m-d');
        } else {
            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
        }

        if ($req->context == 'sharePrice') {
            
            $Shareprice = Share::orderBy('id', 'desc')->first()->shareValue;

            $data = array(
                'Shareprice' => $Shareprice,
            );
        }

       
        return response()->json($data);
    }

    
}
