<?php

namespace App\Http\Controllers\MFN\Member;

use App\Http\Controllers\Controller;
use App\Model\MFN\Loan;
use App\Model\MFN\LoanCollection;
use App\Model\MFN\Member;
use App\Model\MFN\MemberPrimaryProductTransfer;
use App\Model\MFN\SavingsDeposit;
use App\Model\MFN\SavingsWithdraw;
use App\Services\HrService;
use App\Services\MfnService;
use App\Services\RoleService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;

class MemberPrimaryProductTransferController extends Controller
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
                ->get();
            $data = array(
                'branchList' => $branchList,
            );

            return view('MFN.MemberPrimaryProductTransfer.index', $data);
        }
        $columns          = ['mfn_members.name', 'mfn_members.memberCode', 'gnl_branchs.branch_name', 'lp1.name', 'lp2.name', 'hr_employees.emp_name', 'mppt.transferDate', 'mppt.created_by'];
        $limit            = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        // Searching variable

        $search              = (empty($req->input('search.value'))) ? null : $req->input('search.value');
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        $memberPrimaryProdTransfers = DB::table('mfn_member_primary_product_transfers AS mppt')
            ->leftJoin('mfn_members', 'mfn_members.id', 'mppt.memberId')
            ->leftJoin('gnl_branchs', 'gnl_branchs.id', 'mppt.branchId')
            ->leftJoin('mfn_loan_products as lp1', 'lp1.id', 'mppt.oldProductId')
            ->leftJoin('mfn_loan_products as lp2', 'lp2.id', 'mppt.newProductId')
            ->leftJoin('hr_employees', 'hr_employees.user_id', 'mppt.created_by')
            ->whereIn('mppt.branchId', $accessAbleBranchIds)
            ->where('mppt.is_delete', 0)
            ->select(
                'mfn_members.name AS memberName',
                'mfn_members.memberCode',
                'mfn_members.samityId',
                'lp1.name AS oldProduct',
                'lp2.name AS newProduct',
                'hr_employees.emp_name AS entryBy',
                'mppt.*',
                DB::raw('CONCAT(gnl_branchs.branch_code, " - ", gnl_branchs.branch_name) AS branchName')
            )
            ->orderBy($order, $dir);

        if ($search != null) {
            $memberPrimaryProdTransfers->where(function ($query) use ($search) {
                $query->where('gnl_branchs.branch_name', 'LIKE', "%{$search}%")
                    ->orWhere('mfn_members.name', 'LIKE', "%{$search}%")
                    ->orWhere('mfn_members.memberCode', 'LIKE', "%{$search}%")
                    ->orWhere('hr_employees.emp_name', 'LIKE', "%{$search}%");
            });
        }

        if ($req->branchID != '') {
            $memberPrimaryProdTransfers->where('mppt.branchId', $req->branchID);
        }
        if ($req->samityId != '') {
            $memberPrimaryProdTransfers->where('mfn_members.samityId', $req->samityId);
        }
        if ($req->product != '') {
            $product = $req->product;
            $memberPrimaryProdTransfers->where(function ($query) use ($product) {
                $query->where('mppt.newProductId', $product)
                    ->orWhere('mppt.oldProductId', $product);
            });
        }
        if ($req->memeberCode != '') {
            $nameOrCode = $req->memeberCode;
            $memberPrimaryProdTransfers->where(function ($query) use ($nameOrCode) {
                $query->where('mfn_members.name', 'LIKE', "%$nameOrCode%")
                    ->orWhere('mfn_members.memberCode', 'LIKE', "%$nameOrCode%");
            });
        }
        if ($req->date != '') {
            $date = Carbon::parse($req->date)->format('Y-m-d');
            $memberPrimaryProdTransfers->where('mppt.transferDate', $date);
        }

        $totalData = (clone $memberPrimaryProdTransfers)->count();
        $memberPrimaryProdTransfers = $memberPrimaryProdTransfers->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;

        foreach ($memberPrimaryProdTransfers as $key => $memberPrimaryProdTransfer) {
            $memberPrimaryProdTransfers[$key]->transferDate = Carbon::parse($memberPrimaryProdTransfer->transferDate)->format('d-m-Y');
            $memberPrimaryProdTransfers[$key]->sl           = $sl++;
            $memberPrimaryProdTransfers[$key]->id           = encrypt($memberPrimaryProdTransfer->id);
            $memberPrimaryProdTransfers[$key]->action        = RoleService::roleWiseArray($this->GlobalRole, $memberPrimaryProdTransfers[$key]->id);
        }

        $data = array(
            "draw"            => intval($req->input('draw')),
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
            'data'            => $memberPrimaryProdTransfers,
        );

        return response()->json($data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();
        $sysDate             = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $members = DB::table('mfn_members')->whereIn('branchId', $accessAbleBranchIds)->where([
            ['is_delete', 0],
            ['closingDate', '0000-00-00'],
        ])->get();

        $data = array(
            'sysDate' => $sysDate,
            'members' => $members,
        );

        return view('MFN.MemberPrimaryProductTransfer.add', $data);
    }

    public function edit(Request $req)
    {

        if ($req->isMethod('post')) {
            return $this->update($req);
        }

        $TransferData = MemberPrimaryProductTransfer::find(decrypt($req->id));

        $sysDate             = MfnService::systemCurrentDate(Auth::user()->branch_id);
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();
        $members             = DB::table('mfn_members')->whereIn('branchId', $accessAbleBranchIds)->where([
            ['is_delete', 0],
            ['closingDate', '0000-00-00'],
        ])->get();

        $data = array(
            'sysDate'      => $sysDate,
            'members'      => $members,
            'TransferData' => $TransferData,
        );
        return view('MFN.MemberPrimaryProductTransfer.edit', $data);
    }

    public function view($id)
    {
        $memberPrimaryProductTransfer = DB::table('mfn_member_primary_product_transfers')->where('id', decrypt($id))
            ->select('id', 'memberId', 'branchId', 'newProductId', 'oldProductId', 'transferDate', 'transferData', 'note')->first();

        if (Auth::user()->branch_id != 1 && Auth::user()->branch_id != $memberPrimaryProductTransfer->branchId) {
            return '';
        }

        $member = DB::table('mfn_members')->where('id', $memberPrimaryProductTransfer->memberId)->select('name', 'memberCode')->first();

        $loanProductNew = DB::table('mfn_loan_products')->where('id', $memberPrimaryProductTransfer->newProductId)->first()->name;
        $loanProductOld = DB::table('mfn_loan_products')->where('id', $memberPrimaryProductTransfer->oldProductId)->first()->name;
        $data           = array(
            'memberPrimaryProductTransfer' => $memberPrimaryProductTransfer,
            'member'                       => $member,
            'loanProductNew'               => $loanProductNew,
            'loanProductOld'               => $loanProductOld,

        );
        return view('MFN.MemberPrimaryProductTransfer.view', $data);
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

        /**
         *
         * update product in member
         * insert balance of all savings account in diposite and withdraw
         * transer recored entry
         */

        // store data
        DB::beginTransaction();

        try {
            $NewprimaryProductId = $req->newProductId;
            $OldprimaryProductId = $req->oldProductId;

            $memberData = Member::where('id', $req->memberId)
                ->where([
                    ['is_delete', 0],
                    ['closingDate', '0000-00-00'],
                ])->first();
            // member update
            $memberData->primaryProductId = $NewprimaryProductId;
            $memberData->update();
            // member updated

            $savingsACCs = DB::table('mfn_savings_accounts')
                ->where('is_delete', 0)
                ->where('memberId', $req->memberId)
                ->where('closingDate', '0000-00-00')
                ->get();

                $transferDate = Carbon::parse($req->transferDate);

                $savingsACCs = DB::table('mfn_savings_accounts')
                ->where('is_delete', 0)
                ->where('openingDate', '<=', $transferDate)
                ->where('memberId', $req->memberId)
                ->where(function ($query) use ($transferDate) {
                    $query->where('closingDate', '0000-00-00')
                        ->orWhere('closingDate', '>', $transferDate);
                })
                ->get();

            $savingsArray = array();

            foreach ($savingsACCs as $index => $savAcc) {
                $filters['accountId']        = $savAcc->id;
                $balance                     = MfnService::getSavingsBalance($filters);
                $savingsArray[$index]['id']  = $savAcc->id;
                $savingsArray[$index]['amt'] = $balance;

                // withdraw of old product

                $withdraw                    = new SavingsWithdraw;
                $withdraw->accountId         = $savAcc->id;
                $withdraw->memberId          = $savAcc->memberId;
                $withdraw->samityId          = $savAcc->samityId;
                $withdraw->branchId          = $savAcc->branchId;
                $withdraw->primaryProductId  = $OldprimaryProductId;
                $withdraw->savingsProductId  = $savAcc->savingsProductId;
                $withdraw->amount            = $balance;
                $withdraw->date              = $transferDate;
                $withdraw->transactionTypeId = 8;
                $withdraw->ledgerId          = 0;
                $withdraw->chequeNo          = 0;
                $withdraw->created_at        = Carbon::now();
                $withdraw->created_by        = Auth::user()->id;
                $withdraw->save();
                // diposite of new product
                $deposit                    = new SavingsDeposit;
                $deposit->accountId         = $savAcc->id;
                $deposit->memberId          = $savAcc->memberId;
                $deposit->samityId          = $savAcc->samityId;
                $deposit->branchId          = $savAcc->branchId;
                $deposit->primaryProductId  = $NewprimaryProductId;
                $deposit->savingsProductId  = $savAcc->savingsProductId;
                $deposit->amount            = $balance;
                $deposit->date              = $transferDate;
                $deposit->transactionTypeId = 8; // type id 8 for product transfer
                $deposit->isFromAutoProcess = 0;
                $deposit->ledgerId          = 0;
                $deposit->chequeNo          = 0;
                $deposit->created_at        = Carbon::now();
                $deposit->created_by        = Auth::user()->id;
                $deposit->save();
            }

            // transfer entry

            $Transfer               = new MemberPrimaryProductTransfer;
            $Transfer->memberId     = $req->memberId;
            $Transfer->branchId     = $memberData->branchId;
            $Transfer->oldProductId = $req->oldProductId;
            $Transfer->newProductId = $req->newProductId;
            $Transfer->transferDate = $transferDate;
            $Transfer->transferData = json_encode($savingsArray);
            $Transfer->note         = $req->note;
            $Transfer->created_at   = Carbon::now();
            $Transfer->created_by   = Auth::user()->id;
            $Transfer->save();

            MfnService::sendMail('mfn_member_primary_product_transfers', $Transfer->memberId, $Transfer->created_at);

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Inserted',
                'alert-type' => 'success',
            );

            return response()->json($notification);

            // return Redirect::to('mfn/memberPrimaryProductTransfer')->with($notification);
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

    public function update(Request $req)
    {
        $passport = $this->getPassport($req, $operationType = 'update');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $transfer = MemberPrimaryProductTransfer::find(decrypt($req->id));

        if ($req->newProductId == $transfer->newProductId) {

            $notification = array(
                'message'    => 'You are allready Updated',
                'alert-type' => 'warning',
            );

            return response()->json($notification);
        }

        // store data
        DB::beginTransaction();

        try {
            $NewprimaryProductId = $req->newProductId;
            $OldprimaryProductId = $req->oldProductId;

            $memberData = Member::where('id', $req->memberId)
                ->where([
                    ['is_delete', 0],
                    ['closingDate', '0000-00-00'],
                ])->first();
            // member update
            $memberData->primaryProductId = $NewprimaryProductId;
            $memberData->update();
            // member updated

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
                $updateDeposit = SavingsDeposit::where('accountId', $savAcc->id)->where('is_delete', 0)->where('memberId', $transfer->memberId)->where('date', $transfer->transferDate)->where('transactionTypeId', 8)->update(['primaryProductId' => $NewprimaryProductId]);
            }

            $transfer->newProductId = $req->newProductId;
            $transfer->note         = $req->note;
            $transfer->update();
            // transfer entry

            MfnService::sendMail('mfn_member_primary_product_transfers', $transfer->memberId, $transfer->created_at, 0, true);

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
        $passport = $this->getPassport($req, $operationType = 'store');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        ////////////
        ////////////


        $TransferData = MemberPrimaryProductTransfer::find(decrypt($req->id));

        $TransferData->is_delete = 1;
        $TransferData->update();

        $memberData = Member::where('id', $TransferData->memberId)
            ->where([
                ['is_delete', 0],
                ['closingDate', '0000-00-00'],
            ])->first();
        // member update
        $memberData->primaryProductId = $TransferData->oldProductId;
        $memberData->update();

        $savingsAcc = json_decode($TransferData->transferData);

        $flag = true;

        foreach ($savingsAcc as $acc) {

            $DeleteWithdraw = SavingsWithdraw::where('accountId', $acc->id)->where('memberId', $TransferData->memberId)->where('date', $TransferData->transferDate)->where('transactionTypeId', 8)->update(['is_delete' => 1]);

            if ($DeleteWithdraw == false) {
                $flag = false;
            }

            $DeleteDeposit = SavingsDeposit::where('accountId', $acc->id)->where('memberId', $TransferData->memberId)->where('date', $TransferData->transferDate)->where('transactionTypeId', 8)->update(['is_delete' => 1]);
            if ($DeleteDeposit == false) {
                $flag = false;
            }
        }

        if ($flag == true) {
            $notification = array(
                'message'    => 'Successfully Deleted',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        } else {

            $notification = array(
                'message'    => 'Unsuccessfully to delete',
                'alert-type' => 'error',
            );

            return response()->json($notification);
        }
    }

    public function getData(Request $req)
    {
        if ($req->context == 'product') {
            $members = DB::table('mfn_members AS m')->where('m.id', $req->member_id)
                ->leftJoin('gnl_branchs as b', 'b.id', 'm.branchId')
                ->leftJoin('mfn_samity as s', 's.id', 'm.samityId')
                ->leftJoin('mfn_loan_products as lp', 'lp.id', 'm.primaryProductId')
                ->where([
                    ['m.is_delete', 0],
                    ['m.closingDate', '0000-00-00'],
                ])
                ->select(DB::raw("m.id, m.name, m.primaryProductId, m.memberCode, lp.name as LoanProduct, CONCAT(m.name,' - ',m.memberCode) as member, CONCAT(b.branch_name,' - ',b.branch_code) as branch, CONCAT(s.name,' - ',s.samityCode) as samity"))

                ->first();


            $primaryProductIds = json_decode(DB::table('mfn_branch_products')
                ->where('branchId', Auth::user()->branch_id)
                ->first()
                ->loanProductIds);

            // $primaryProducts = DB::table('mfn_loan_products')
            // ->whereIn('id', $primaryProductIds)
            // ->select(DB::raw("CONCAT(productCode, '-', name) AS name, id"))
            // ->get();




            $product = DB::table('mfn_loan_products')->whereIn('id', $primaryProductIds)->where('is_delete', 0)->where('id', '!=', $members->primaryProductId)->get();

            $savingsACC = DB::table('mfn_savings_accounts')
                ->where('is_delete', 0)
                ->where('memberId', $req->member_id)
                ->select('id', 'accountCode')
                ->get();

            foreach ($savingsACC as $index => $value) {
                $filters['accountId'] = $value->id;

                $savingsACC[$index]->diposite = MfnService::getSavingsDeposit($filters);
                $savingsACC[$index]->withdraw = MfnService::getSavingsWithdraw($filters);
                $savingsACC[$index]->balance  = MfnService::getSavingsBalance($filters);
            }

            $data = array(
                'product'    => $product,
                'memberdata' => $members,
                'savingsACC' => $savingsACC,
            );
        }

        if ($req->context == 'product_for_edit') {

            $TransferData = MemberPrimaryProductTransfer::find(decrypt($req->t_id));


            $members = DB::table('mfn_members AS m')->where('m.id', $TransferData->memberId)
                ->leftJoin('gnl_branchs as b', 'b.id', 'm.branchId')
                ->leftJoin('mfn_samity as s', 's.id', 'm.samityId')
                // ->leftJoin('mfn_loan_products as lp', 'lp.id', 'm.primaryProductId')
                ->where([
                    ['m.is_delete', 0],
                    ['m.closingDate', '0000-00-00'],
                ])
                ->select(DB::raw("m.id, m.name, m.memberCode, CONCAT(m.name,' - ',m.memberCode) as member, CONCAT(b.branch_name,' - ',b.branch_code) as branch, CONCAT(s.name,' - ',s.samityCode) as samity"))

                ->first();

            $primaryProductIds = json_decode(DB::table('mfn_branch_products')
                ->where('branchId', Auth::user()->branch_id)
                ->first()
                ->loanProductIds);


            $product        = DB::table('mfn_loan_products')->whereIn('id', $primaryProductIds)
                ->where('is_delete', 0)->where('id', '!=', $TransferData->oldProductId)->get();

            $primaryProduct = DB::table('mfn_loan_products')->where('is_delete', 0)->where('id', $TransferData->oldProductId)->first();

            $savingsACC = DB::table('mfn_savings_accounts')
                ->where('is_delete', 0)
                ->where('memberId', $req->member_id)
                ->select('id', 'accountCode')
                ->get();

            foreach ($savingsACC as $index => $value) {
                $filters['accountId'] = $value->id;

                $savingsACC[$index]->diposite = MfnService::getSavingsDeposit($filters);
                $savingsACC[$index]->withdraw = MfnService::getSavingsWithdraw($filters);
                $savingsACC[$index]->balance  = MfnService::getSavingsBalance($filters);
            }

            $data = array(
                'primaryProduct' => $primaryProduct,
                'product'        => $product,
                'memberdata'     => $members,
                'savingsACC'     => $savingsACC,
            );
        }
        if ($req->context == 'DropDownPopulateForFiltering') {
            $productIds = MfnService::getBranchAssignedLoanProductIds($req->branchId);
            $products = DB::table('mfn_loan_products')->whereIn('id', $productIds)->get();

            $samities = DB::table('mfn_samity')->where([['branchId', $req->branchId], ['is_delete', 0]])->get();

            $data = array(
                'products' => $products,
                'samities' => $samities
            );
        }

        return response()->json($data);
    }


    public function getPassport($req, $operationType, $transfer = null)
    {
        $errorMsg = null;
        $transferDate = date('Y-m-d', strtotime($req->transferDate));

        // consider running loan only 
        $loan = Loan::where('memberId', $req->memberId)
            ->where('is_delete', 0)
            ->where(function ($query) use ($transferDate) {
                $query->where('loanCompleteDate', '0000-00-00')
                    ->orWhere('loanCompleteDate', '>', $transferDate)
                    ->orWhere('disbursementDate', '>', $transferDate);
            })
            ->count();

        $loanCollection = LoanCollection::where('memberId', $req->memberId)->where('is_delete', 0)->where('amount', '!=', 0)->where('collectionDate', '>', $transferDate)->count();
        $savingsD = SavingsDeposit::where('memberId', $req->memberId)->where('is_delete', 0)->where('amount', '!=', 0)->where('date', '>=', $transferDate)->where('transactionTypeId', '=', 8)->count();
        $savingsW = SavingsWithdraw::where('memberId', $req->memberId)->where('is_delete', 0)->where('amount', '!=', 0)->where('date', '>=', $transferDate)->where('transactionTypeId', '=', 8)->count();
        if ($operationType != 'update') {
            $transfers = MemberPrimaryProductTransfer::where('transferDate', '>=', $transferDate)->where('is_delete', 0)->where('memberId', $req->memberId)->count();
        } else {
            $transfers = MemberPrimaryProductTransfer::where('transferDate', '>=', $transferDate)->where('id', '!=', decrypt($req->id))->where('is_delete', 0)->where('memberId', $req->memberId)->count();
        }

        if ($operationType != 'delete') {

            $rules = array(
                'oldProductId' => 'required',
                'memberId'     => 'required',
                'newProductId' => 'required',
                'transferDate' => 'required',

            );

            $validator = Validator::make($req->all(), $rules);

            $attributes = array(
                'oldProductId' => 'Old Product required',
                'memberId'     => 'Member is required',
                'newProductId' => 'New Product required',
                'transferDate' => 'Date is required',

            );

            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        if ($loan > 0 || $loanCollection > 0 || $savingsD > 0 || $savingsW > 0 || $transfers > 0) {
            $errorMsg = 'Product Transfer not possible you have transactions on other days.';
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid'  => $isValid,
            'errorMsg' => $errorMsg,
        );
        return $passport;
    }
}
