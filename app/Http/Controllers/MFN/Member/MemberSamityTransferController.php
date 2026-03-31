<?php

namespace App\Http\Controllers\MFN\Member;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MfnService;
use App\Services\HrService;
use App\Model\MFN\SavingsAccount;
use App\Model\MFN\SavingsDeposit;
use App\Model\MFN\SavingsWithdraw;
use App\Model\MFN\Loan;
use App\Model\MFN\LoanCollection;
use App\Model\MFN\MemberSamityTransfer;
use App\Model\MFN\MemberPrimaryProductTransfer;
use App\Model\MFN\MemberClosing;
use App\Model\MFN\Member;
use App\Http\Controllers\MFN\Member\MemberController;
use Carbon\Carbon;

use DB;

class MemberSamityTransferController extends Controller
{
    public function index(Request $req)
    {   
        if(!$req->ajax()){
            return view('MFN.MemberSamityTransfer.index');
        }

        $columns          = ['mm.name', 'mm.memberCode', 'mppt.transferDate', 'gnl_branchs.branch_name', 'mfn_samity.name', 'mfn_samity.name', 'mmst.created_by'];
        $limit            = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order            = $columns[$orderColumnIndex];
        $dir              = $req->input('order.0.dir');

        $search              = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $samityTransferInfo =  DB::table('mfn_member_samity_transfers as mmst')
                                ->where('mmst.is_delete',0)
                                ->where('mmst.branchId',Auth::user()->branch_id)
                                ->leftjoin('mfn_members as mm', 'mm.id', 'mmst.memberId')
                                ->leftjoin('gnl_branchs as br', 'mmst.branchId','br.id')
                                ->leftjoin('gnl_sys_users as gsu', 'gsu.id', 'mmst.created_by')
                                ->leftjoin('mfn_samity as msold', 'msold.id','mmst.oldSamityId')
                                ->leftjoin('mfn_samity as msnew', 'msnew.id','mmst.newSamityId')
                                ->select('mmst.id as id','mm.memberCode as memberCode', 'mm.name as memberName', 'mm.id as memberId', 'mmst.date as transferDate',
                                'br.branch_name as branchName', 'gsu.full_name as entryBy','msold.name as oldSamity', 'msnew.name as newSamity')
                                ->orderBy($order, $dir)
                                ->get();

        
        if ($search != null) {
            $samityTransferInfo->where(function ($query) use ($search) {
                $query->where('gnl_branchs.branch_name', 'LIKE', "%{$search}%")
                    ->orWhere('mm.name', 'LIKE', "%{$search}%")
                    ->orWhere('msold.name', 'LIKE', "%{$search}%")
                    ->orWhere('msnew.name', 'LIKE', "%{$search}%");
            });
        }

        $totalData = count($samityTransferInfo);

        $sl=1;
        foreach($samityTransferInfo as $key => $info )
        {
            $info->sl           =$sl++;
            $info->id           = encrypt($info->id);
        }
        
        $data = array(
            "draw"            => intval($req->input('draw')),
            'data' => $samityTransferInfo,
            "recordsTotal"    => $totalData,
            "recordsFiltered" => $totalData,
        );

        return response()->json($data);
    }

    public function add()
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $samities = DB::table('mfn_samity')
            ->where([
                ['is_delete', 0],
                ['branchId', Auth::user()->branch_id],
            ])
            ->select(DB::raw("CONCAT(samityCode, ' - ', name) AS name, id"))
            ->get();

        $data = array(
            'samities' => $samities
        );

        return view('MFN.MemberSamityTransfer.add', $data);
    }

    public function store(Request $req)
    {
        
        $memberId= $req->memberName;
        $newSamity= $req->newSamity;
        $currentSamity= $req->currentSamity;
        $transferDate= $req->transferDate;

        $result = $this->validateMemberTransfer($memberId, $transferDate);

        
        if($result['alert-type'] == "error"){
            return response()->json($result);
        }
        
        $history = array('loanInfo'=> $result['loanInfo'], 'savingsInfo'=> $result['savingsInfo']);
        
        DB::beginTransaction();
        try{

            $newMembeCode = MemberController::generateMemberCode($newSamity);
            $newMraCode = MemberController::generateMraCode($newMembeCode);

            //getting memeber info
            $member =  Member::where('id',$memberId)->get()->first();

            //saving transfer data
            $samityTransfer                    = new MemberSamityTransfer;
            $samityTransfer->memberId          = $memberId;
            $samityTransfer->branchId          = Auth::user()->branch_id;
            $samityTransfer->oldSamityId       = $currentSamity;
            $samityTransfer->newSamityId       = $newSamity;
            $samityTransfer->oldMemberCode     = $member->memberCode;
            $samityTransfer->newMemberCode     = $newMembeCode;
            $samityTransfer->oldMraCode        = $member->mraCode;
            $samityTransfer->newMraCode        = $newMraCode;
            $samityTransfer->date              = Carbon::parse($transferDate);
            $samityTransfer->transferData      = json_encode($history);
            $samityTransfer->created_at        = Carbon::now();
            $samityTransfer->created_by        = Auth::user()->id;
            $samityTransfer->save();

            //changing data in member table
            $member->samityId = $newSamity;
            $member->memberCode = $newMembeCode;
            $member->mraCode = $newMraCode;
            $member->save();
            DB::commit();

            $notification = array(
                'message'    => 'Samity Transfer successfull',
                'alert-type' => 'success',
            );
            return response()->json($notification);
        }
        catch (\Exception $e) {

            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
                'consoleMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );

            return response()->json($notification);
        }
    }

    public function view($id)
    {
        $memberSamityTransfer = DB::table('mfn_member_samity_transfers')->where('id', decrypt($id))->get()->first();

        if (Auth::user()->branch_id != 1 && Auth::user()->branch_id != $memberSamityTransfer->branchId) {
            return '';
        }

        $member = DB::table('mfn_members')->where('id', $memberSamityTransfer->memberId)->select('name', 'memberCode')->first();

        $newSamityName = DB::table('mfn_samity')->where('id', $memberSamityTransfer->newSamityId)->first()->name;
        $oldSamityName = DB::table('mfn_samity')->where('id', $memberSamityTransfer->oldSamityId)->first()->name;
        $data = array(
            'memberSamityTransfer'        => $memberSamityTransfer,
            'member'                      => $member,
            'newSamityName'               => $newSamityName,
            'oldSamityName'               => $oldSamityName,

        );
        return view('MFN.MemberSamityTransfer.view', $data);
    }

    public function edit(Request $req, $id)
    { 
        if ($req->isMethod('post')) {
            return $this->update($req);
        }

        $TransferData = MemberSamityTransfer::find(decrypt($req->id));

        $sysDate             = MfnService::systemCurrentDate(Auth::user()->branch_id);
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();
        $members             = DB::table('mfn_members')->whereIn('branchId', $accessAbleBranchIds)->where([
            ['is_delete', 0],
            ['closingDate', '0000-00-00'],
        ])->get();

        
        $samityList = DB::table('mfn_samity')->where('is_delete', 0)
                        ->where('branchId',$TransferData->branchId)
                        ->where('id','!=', $TransferData->newSamityId)
                        ->where('id','!=',$TransferData->oldSamityId)->get();

        $data = array(
            'sysDate'      => $sysDate,
            'members'      => $members,
            'TransferData' => $TransferData,
            'samityList' => $samityList
        );
        return view('MFN.MemberSamityTransfer.edit', $data);
    }

    public function update($req)
    { 
        $memberId =  $req->memberId;
        $transferDate =  $req->transferDate;
        $transferId =  decrypt($req->id);
        $newSamity = $req->newSamityId;
        $result = $this->validateMemberTransfer($memberId, $transferDate, $transferId);

        
        if($result['alert-type'] == "error"){
            return response()->json($result);
        }

        $history = array('loanInfo'=> $result['loanInfo'], 'savingsInfo'=> $result['savingsInfo']);
        
        DB::beginTransaction();
        try{

            $newMembeCode = MemberController::generateMemberCode($newSamity);
            $newMraCode = MemberController::generateMraCode($newMembeCode);

            //saving transfer data
            $samityTransfer = MemberSamityTransfer::where('id',$transferId)->get()->first();
            $samityTransfer->newSamityId       = $newSamity;
            $samityTransfer->newMemberCode     = $newMembeCode;
            $samityTransfer->newMraCode        = $newMraCode;
            $samityTransfer->date              = Carbon::parse($transferDate);
            $samityTransfer->transferData      = json_encode($history);
            $samityTransfer->updated_at        = Carbon::now();
            $samityTransfer->updated_by        = Auth::user()->id;
            $samityTransfer->save();

            //changing data in member table
            $member =  Member::where('id',$memberId)->get()->first();
            $member->samityId = $newSamity;
            $member->memberCode = $newMembeCode;
            $member->mraCode = $newMraCode;
            $member->save();
            DB::commit();

            $notification = array(
                'message'    => 'Samity Transfer successfull',
                'alert-type' => 'success',
            );
            return response()->json($notification);
        }
        catch (\Exception $e) {

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
        DB::beginTransaction();
        try{
            $TransferData = MemberSamityTransfer::find(decrypt($req->id));
            
            //checkging if the old member code is assigned to someone else
            $member = Member::where('memberCode', $TransferData->oldMemberCode)->get()->first();
            if($member){
                $notification = array(
                    'message'    => 'The old member code is in use',
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            //updating member info
            $member = Member::where('id', $TransferData->memberId)->get()->first();
            $member->samityId = $TransferData->oldSamityId;
            $member->memberCode = $TransferData->oldMemberCode;
            $member->mraCode = $TransferData->oldMraCode;
            $member->save();

            //delete samity transfer info
            $TransferData->is_delete = 1;
            $TransferData->save();


            $notification = array(
                'message'    => 'Successfully Deleted',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        }
        catch (\Exception $e) {

            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
                'consoleMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );

            return response()->json($notification);
        }
    }

    public function getData(Request $req)
    {
        $data= array();
        if($req->samity_id)
        {
            $memebers = MfnService::getSelectizeMembers(['samityId' => $req->samity_id]);
            $data['members'] = $memebers;
        }

        if($req->member_id)
        {
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

            //$product = DB::table('mfn_loan_products')->whereIn('id', $primaryProductIds)->where('is_delete', 0)->where('id', '!=', $members->primaryProductId)->get();
            $loanproduct = DB::table('mfn_loans')->where('memberId', $req->member_id)->where('is_delete', 0)->get();
            
            foreach ($loanproduct as $index => $loan ){
                //$loanproduct[$index]->outstanding = MfnService::getLoanStatus($loan->id , $req->transferDate);
                $outstanding = MfnService::getLoanStatus($loan->id , $req->transferDate);
                $loanproduct[$index]->outstanding = $outstanding[0]['outstanding'];
                 
            }


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

            $data['loanproduct'] = $loanproduct;
            $data['memberdata'] = $members;
            $data['savingsACC'] = $savingsACC;
            
        }

        return response()->json($data);
    }

    public function validateMemberTransfer($memberId, $transferDate, $transferId=null){

        //check if members savings are 0
        $savingsACC = DB::table('mfn_savings_accounts')
                ->where('is_delete', 0)
                ->where('memberId', $memberId)
                ->select('id', 'accountCode', 'closingDate')
                ->get();
        
        $savingsInfo = array();
        $allowTransfer = true;
        foreach ($savingsACC as $index => $value) {
            $filters['accountId'] = $value->id;
            $balance = MfnService::getSavingsBalance($filters);
            if($balance >0 || $value->closingDate == "0000-00-00" || $value->closingDate > $transferDate){
                $allowTransfer = false;
                break;
            }
            else{
                $obj = array('id' => $value->id, 'balance' => $balance);
                $savingsInfo[$index] = $obj;
            }
        }

        if(!$allowTransfer){
            $notification = array(
                'message'    => 'Can not transfer. This member has savings',
                'alert-type' => 'error',
            );

            return $notification;
        }

        //check if members loans are 0 and loan complete date is less than transferDate
        $loanInfo = array();
        $allowTransfer = true;
        $loans = DB::table('mfn_loans')->where('memberId', $memberId)->where('is_delete', 0)->get();
        foreach($loans as $index=> $loan){
            $data = MfnService::getLoanStatus($loan->id , $transferDate);
            if($data[0]['outstanding'] > 0 || $loan->loanCompleteDate == "0000-00-00" || $loan->loanCompleteDate > $transferDate){
                $allowTransfer = false;
                break;
            }
            else{
                $obj = array('id' => $value->id, 'outstanding' => $data[0]['outstanding']);
                $loanInfo[$index] = $obj;
            }
        }

        if(!$allowTransfer){
            $notification = array(
                'message'    => 'Can not transfer. This member has unpaid loan',
                'alert-type' => 'error',
            );

            return $notification;
        }

        //check if this member has any kind of transacation today or in future
        if($transferId == null){
            $samitytransferHistory =  MemberSamityTransfer::where('memberId', $memberId)->where('is_delete', 0)->where('date','>=',Carbon::parse($transferDate))->get();
        }
        else{
            $samitytransferHistory =  MemberSamityTransfer::where('memberId', $memberId)
                                        ->where('is_delete', 0)
                                        ->where('date','>=',Carbon::parse($transferDate))
                                        ->where('id','!=', $transferId)->get();
        }
        $primaryProductTransferHistory =  MemberPrimaryProductTransfer::where('memberId', $memberId)->where('is_delete', 0)->where('transferDate','>=',Carbon::parse($transferDate))->get();
        $memberClosingHistory =  MemberClosing::where('memberId', $memberId)->where('is_delete', 0)->where('closingDate','>=',Carbon::parse($transferDate))->get();
        $savingsAccountHistory =  SavingsAccount::where('memberId', $memberId)->where('is_delete', 0)->where('openingDate','>=',Carbon::parse($transferDate))->get();
        $loanHistory =  Loan::where('memberId', $memberId)->where('is_delete', 0)->where('disbursementDate','>=',Carbon::parse($transferDate))->get();
        $savingsDepositHistory =  SavingsDeposit::where('memberId', $memberId)->where('is_delete', 0)->where('date','>',Carbon::parse($transferDate))->get();
        $savingsWithdrawHistory =  SavingsWithdraw::where('memberId', $memberId)->where('is_delete', 0)->where('date','>',Carbon::parse($transferDate))->get();
        $loanCollectionHistory =  LoanCollection::where('memberId', $memberId)->where('is_delete', 0)->where('created_at','>',Carbon::parse($transferDate))->get();
        
        $allowTransfer=true;
        $message="";
        if(count($samitytransferHistory)>0){
            $message="Can not transfer member because samity transfer data exist.";
            $allowTransfer= false;
        }
        elseif(count($primaryProductTransferHistory)>0){
            $message="Can not transfer member because primary product transfer data exist.";
            $allowTransfer= false;
        }
        elseif(count($memberClosingHistory)>0){
            $message="Can not transfer member because member Closing data exist.";
            $allowTransfer= false;
        }
        elseif(count($savingsAccountHistory)>0){
            $message="Can not transfer member because savings account data exist.";
            $allowTransfer= false;
        }
        elseif(count($loanHistory)>0){
            $message="Can not transfer member because Loan data exist.";
            $allowTransfer= false;
        }
        elseif(count($savingsDepositHistory)>0){
            $message="Can not transfer member because deposit data exist.";
            $allowTransfer= false;
        }
        elseif(count($savingsWithdrawHistory)>0){
            $message="Can not transfer member because withdraw data exist.";
            $allowTransfer= false;
        }
        elseif(count($loanCollectionHistory)>0){
            $message="Can not transfer member because loan collection data exist.";
            $allowTransfer= false;
        }
        
        if(!$allowTransfer){
            $notification = array(
                'message'    => $message,
                'alert-type' => 'error',
            );

            return $notification;
        }

        //if passes all the criteria
        $notification = array(
            'alert-type' => 'success',
            'loanInfo' => $loanInfo,
            'savingsInfo' => $savingsInfo
        );

        return $notification;


    }
}
