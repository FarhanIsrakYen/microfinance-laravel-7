<?php

namespace App\Http\Controllers\MFN\Savings;

use Redirect;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MfnService;
use DB;
use App\Model\MFN\Member;
use App\Model\MFN\SavingsAccount;
use App\Model\MFN\SavingsDeposit;
use App\Model\MFN\SavingsWithdraw;
use App\Model\MFN\SavingsOBAccount;
use Carbon\Carbon;
use DateTime;
use Validator;
use App\Services\HrService;
use App\Model\GNL\Branch;
use Facade\Ignition\Tabs\Tab;

class SavingsOBController extends Controller
{
    public function index(Request $req)
    {

        if ($req->isMethod('post')) {      
            
            $RequestData1 = $req->all();

            $branchId = Auth::user()->branch_id;
            $firstSavAcc = DB::table('mfn_savings_accounts')->where('id', $RequestData1['acc_id'][0])->first();

            if ($branchId != $firstSavAcc->branchId) {
                $notification = array(
                    'message' => 'This should be done from branch.',
                    'alert-type' => 'error'
                );
                return redirect()->back()->with($notification);  
            }

            // if branch is not on opening date, then it could not store/update

            $isBranchOnOpeningDate = Mfnservice::isOpening($branchId);

            if (!$isBranchOnOpeningDate) {
                $notification = array(
                    'message' => 'Branch is not on opening date',
                    'alert-type' => 'error'
                );
                return redirect()->back()->with($notification); 
            }                       

            $acc_id_arr = (isset($RequestData1['acc_id']) ? $RequestData1['acc_id'] : array());
            $member_id_arr = (isset($RequestData1['member_id']) ? $RequestData1['member_id'] : array());
            $samity_id_arr = (isset($RequestData1['samity_id']) ? $RequestData1['samity_id'] : array());
            $branch_id_arr = (isset($RequestData1['branch_id']) ? $RequestData1['branch_id'] : array());
            $diposite_amt_arr = (isset($RequestData1['diposite_amt']) ? $RequestData1['diposite_amt'] : array());
            $inerest_amt_arr = (isset($RequestData1['inerest_amt']) ? $RequestData1['inerest_amt'] : array());

            $withdraw_amt_arr = (isset($RequestData1['withdraw_amt']) ? $RequestData1['withdraw_amt'] : array());

            $balance_amt_arr = (isset($RequestData1['balance_amt']) ? $RequestData1['balance_amt'] : array());

            $validation = $this->validation($member_id_arr, $acc_id_arr, $diposite_amt_arr);
            if($validation['alert-type'] == 'error'){
                return redirect()->back()->with($validation); 
            }

            $RequestData = array();

            $warningA = array();

            try {
                foreach ($acc_id_arr as $key => $value) {
                    
                    if (!empty($value)) {
                        $RequestData['accountId'] = $value;
                        $RequestData['memberId'] = $member_id_arr[$key];
                        $RequestData['samityId'] = $samity_id_arr[$key];
                        $RequestData['branchId'] = $branch_id_arr[$key];
                        $RequestData['depositAmount'] = $diposite_amt_arr[$key];
                        $RequestData['interestAmount'] = $inerest_amt_arr[$key];
                        $RequestData['withdrawAmount'] = $withdraw_amt_arr[$key];
                        $RequestData['openingBalance'] = $balance_amt_arr[$key];

                        // if ($diposite_amt_arr[$key] > 0 || $inerest_amt_arr[$key] > 0 || $withdraw_amt_arr[$key] > 0 || $balance_amt_arr[$key] > 0) {

                            

                            $withdraw = SavingsWithdraw::where('accountId', $value)->where('is_delete', 0)
                                        ->where('date', '>', MfnService::systemCurrentDate($RequestData['branchId']))
                                        ->where('transactionTypeId', '<>', 4)->count();

                            
                            $amountOB = SavingsOBAccount::where('accountId', $value)->first();
                            $oldDeposit = ($amountOB) ? $amountOB->depositAmount : 0;
                            $oldInterest = ($amountOB) ? $amountOB->interestAmount : 0;
                            $oldWithdraw = ($amountOB) ? $amountOB->withdrawAmount : 0;
                            $oldOB = ($amountOB) ? $amountOB->openingBalance : 0;

                            if ($withdraw == 0 && ($RequestData['openingBalance'] != $oldOB ||
                                $RequestData['depositAmount'] != $oldDeposit ||
                                $RequestData['interestAmount'] != $oldInterest ||
                                $RequestData['withdrawAmount'] != $oldWithdraw)) {

                                $isInsertDetails = SavingsOBAccount::updateOrCreate(
                                    ['accountId' => $value],
                                    $RequestData
                                );
                                // create($RequestData);

                                if ($isInsertDetails) {

                                    $savAcc = DB::table('mfn_savings_accounts')->where('id', $value)->first();
                                    $primaryProductId = DB::table('mfn_members')->where('id', $member_id_arr[$key])->first()->primaryProductId;
                                    // inseert in diposite opening  amount type 4 table 
                                    $RequestData['transactionTypeId'] = 4;
                                    $RequestData['date'] = MfnService::systemCurrentDate($branch_id_arr[$key]);
                                    $RequestData['primaryProductId'] = $primaryProductId;
                                    $RequestData['savingsProductId'] = $savAcc->savingsProductId;
                                    $RequestData['amount'] = $diposite_amt_arr[$key];

                                    //if($RequestData['amount']>0){
                                    SavingsDeposit::updateOrCreate(
                                        ['accountId' => $value, 'transactionTypeId' => 4],
                                        $RequestData
                                    );
                                    // }

                                    // inseert in diposite interest amount type 5 table 
                                    $RequestData['transactionTypeId'] = 5;
                                    $RequestData['amount'] = $inerest_amt_arr[$key];
                                    //if($RequestData['amount']>0){
                                    SavingsDeposit::updateOrCreate(
                                        ['accountId' => $value, 'transactionTypeId' => 5],
                                        $RequestData
                                    );
                                    //}

                                    // inseert in withdraw table  type 4  table 

                                    $RequestData['transactionTypeId'] = 4;
                                    $RequestData['amount'] = $withdraw_amt_arr[$key];
                                    //  if($RequestData['amount']>0){
                                    // ::create($RequestData);
                                    SavingsWithdraw::updateOrCreate(
                                        ['accountId' => $value, 'transactionTypeId' => 4],
                                        $RequestData
                                    );
                                    // }


                                }
                            } else {
                                if ($withdraw != 0) {

                                    $data = SavingsAccount::where('id', $value)->first();

                                    $k = $data->member->name . '(' . $data->accountCode . ')';

                                    array_push($warningA, $k);
                                    // $notification = array(
                                    //     'message' => 'Has Transactions can not update',
                                    //     'alert-type' => 'warning',
                                    // );
                                }
                            }
                        // }
                    }
                }


                DB::commit();
                $notification2 = '';
                if (empty($warningA)) {
                    $notification = array(
                        'message' => 'Successfully inserted Savings Opening Balance',
                        'alert-type' => 'success',
                    );
                } else {
                    $notification = array(
                        'message' => 'Update Unavailable  ' . implode(" ", $warningA) . '   account has withdraws ',
                        'alert-type' => 'warning',
                    );
                    // return response()->json(array(
                    //     'messages' => array(
                    //         array(
                    //             'status'=>'warning',
                    //             'message'=> 'Invoiced could not sent!'
                    //         ),
                    //         array(
                    //             'status'=>'success',
                    //             'message'=> 'item successfully modified!'
                    //         )
                    //     )
                    // ))';
                    $notification2 = array(
                        'message' => 'Others insert/ Update Successfully ',
                        'alert-type' => 'success',
                    );

                    // \Session::put('notification2', $notification2);
                    // session()->set('notification2',$notification2);
                    // $notification2 = 'Others insert/ Update Successfully ';

                }


                return Redirect::to('mfn/savings_ob')->with($notification, $notification2);
            } catch (Exception $e) {
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to inserted Savings Opening Balance',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
                //return $e;
            }




        }
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        $branchces = DB::table('gnl_branchs')
            ->where([
                ['is_delete', 0],
                ['id', '>', 1],
            ])
            ->whereIn('id', $accessAbleBranchIds)
            ->orderBy('branch_code')
            ->select(DB::raw("id, CONCAT(branch_code, ' - ', branch_name) AS name"))
            ->get();

        if (count($branchces) > 1) {
            $samities = [];
        } else {
            $samities = DB::table('mfn_samity')
                ->where([
                    ['is_delete', 0]
                ])
                ->whereIn('branchId', $accessAbleBranchIds)
                ->orderBy('samityCode')
                ->select(DB::raw("id, CONCAT(samityCode, ' - ', name) AS name"))
                ->get();
        }


        $data = array(
            'branchces' => $branchces,
            'samities' => $samities,
        );

        return view('MFN.Savings.SavingsOB.index', $data);
    }

    public function validation($member_id_arr, $acc_id_arr, $deposite_amt_arr){

        $openingBalanceList = DB::table('mfn_savings_deposit')->whereIn('accountId', $acc_id_arr)->where('transactionTypeId',4)->get();
        $branchId= DB::table('mfn_savings_accounts')->whereIn('id', $acc_id_arr)->first()->branchId; //opening balance na e thakte pare tai accont theke dorsi
        $branchDate= MfnService::systemCurrentDate($branchId);

        $withdrawIssue= array();
        $savingsIssue= array();
        $samityTransferIssue= array();
        $primaryProductIssue= array();
        $memberClosingIssue= array();
        foreach($acc_id_arr as $index => $acc){

            $openingBalance = $openingBalanceList->where('accountId', $acc)->sum('amount');

            if($openingBalance == $deposite_amt_arr[$index]){
                continue;
            }
                
            //check if there is any withdraw in future
            $withdrawCount =  DB::table('mfn_savings_withdraw')->where([['is_delete',0],['accountId', $acc]])->where('date','>',$branchDate)->count();
            if($withdrawCount > 0){
                array_push($withdrawIssue, $acc);
            }

            //check if there is any savings closing
            $savingsClosingCount =  DB::table('mfn_savings_closings')->where([['is_delete',0],['accountId', $acc]])->where('closingDate','>',$branchDate)->count();
            if($savingsClosingCount > 0){
                array_push($savingsIssue, $acc);
            }

            $memberId= $member_id_arr[$index];
            //check if there is any smaity transfer
            $samityTransferCount = DB::table('mfn_member_samity_transfers')->where([['memberId', $memberId],['is_delete',0]])->where('date','>',$branchDate)->count();
            if($samityTransferCount > 0){
                array_push($samityTransferIssue, $memberId);
            }

            //check if there is any primary porduct transfer
            $primaryProducttransferCount = DB::table('mfn_member_primary_product_transfers')->where([['memberId', $memberId],['is_delete',0]])->where('transferDate','>',$branchDate)->count();
            if($primaryProducttransferCount > 0){
                array_push($primaryProductIssue, $memberId);
            }

            //check if there is any Member Closing
            $memberClosingCount = DB::table('mfn_member_closings')->where([['memberId', $memberId],['is_delete',0]])->where('closingDate','>',$branchDate)->count();
            if($memberClosingCount > 0){
                array_push($memberClosingIssue, $memberId);
            }
            
        }

        //error message making
        $notification = array('alert-type' => 'success', 'message' => '');
        if(count($withdrawIssue) > 0){
            $notification['alert-type'] = 'error';
            $accountNumbers = DB::table('mfn_savings_accounts')->whereIn('id', $withdrawIssue)->pluck('accountCode')->toArray();
            $accountNumbers = join(", ", $accountNumbers);
            $notification['message'] = $notification['message']." ".$accountNumbers." acount(s) have withdraw | ";
        }
        if(count($savingsIssue) > 0){
            $notification['alert-type'] = 'error';
            $accountNumbers = DB::table('mfn_savings_accounts')->whereIn('id', $savingsIssue)->pluck('accountCode')->toArray();
            $accountNumbers = join(", ", $accountNumbers);
            $notification['message'] = $notification['message']." ".$accountNumbers." acount(s) have Savings Closing | ";
        }
        if(count($samityTransferIssue) > 0){
            $notification['alert-type'] = 'error';
            $MemberCodes = DB::table('mfn_members')->whereIn('id', $samityTransferIssue)->pluck('memberCode')->toArray();
            $MemberCodes = join(", ", $MemberCodes);
            $notification['message'] = $notification['message']." ".$MemberCodes." member(s) have Samity tranfer | ";
        }
        if(count($primaryProductIssue) > 0){
            $notification['alert-type'] = 'error';
            $MemberCodes = DB::table('mfn_members')->whereIn('id', $primaryProductIssue)->pluck('memberCode')->toArray();
            $MemberCodes = join(", ", $MemberCodes);
            $notification['message'] = $notification['message']." ".$MemberCodes." member(s) have Primary Product tranfer | ";
        }
        if(count($memberClosingIssue) > 0){
            $notification['alert-type'] = 'error';
            $MemberCodes = DB::table('mfn_members')->whereIn('id', $memberClosingIssue)->pluck('memberCode')->toArray();
            $MemberCodes = join(", ", $MemberCodes);
            $notification['message'] = $notification['message']." ".$MemberCodes." member(s) have Member Closing";
        }
        return $notification;
    }

    public function SavingsStatusDetails(Request $req)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        $search_branch =  $req->filBranch;
        $search_samity =  $req->filSamity;

        if (Auth::user()->branch_id != 1) {
            $search_branch = Auth::user()->branch_id;
        }

        if ($search_branch == null) {
            return '';
        }

        // we will display only regular savings accounts
        $regularSavProductIds = DB::table('mfn_savings_product')
            ->where('productTypeId', 1)
            ->pluck('id')
            ->toArray();

        $savingsinfo = SavingsAccount::where('mfn_savings_accounts.is_delete', 0)
            ->where('mfn_savings_accounts.isOpening', 1)
            ->whereIn('savingsProductId', $regularSavProductIds)
            ->whereIn('mfn_savings_accounts.branchId', $accessAbleBranchIds)
            ->select('mfn_savings_accounts.*', 'mfn_members.name As M_name', 'mfn_members.memberCode As M_Code', 'mfn_members.closingDate As M_status')
            ->leftJoin('mfn_members', 'mfn_members.id', 'mfn_savings_accounts.memberId')
            ->where(function ($savingsinfo) use ($search_branch, $search_samity) {

                if (!empty($search_branch)) {
                    $savingsinfo->where('mfn_savings_accounts.branchId', '=', $search_branch);
                }
                if (!empty($search_samity)) {
                    $savingsinfo->where('mfn_savings_accounts.samityId', '=', $search_samity);
                }
            })
            ->orderBy('mfn_savings_accounts.memberId')
            ->get();



        if ($savingsinfo->count() > 0) {
            $html = '';
            $i = 0;



            $tempMemberID =  $savingsinfo[0]['memberId'];

            foreach ($savingsinfo as $key => $value) {

                $filters['accountId'] = $value->id;
                if (!empty($search_dateTo)) {
                    $filters['dateTo'] = $search_dateTo;
                }
                // $savingsinfo->where('memberId',$value->memberId)->count()
                $obACC = SavingsOBAccount::where('accountId', $value->id)->first();


                $html .= '<tr>';
                $html .= '<th style="width: 3%;">' . ($key + 1) . '</th>';


                if (!empty($savingsinfo[($key - 1)]['memberId']) && $savingsinfo[($key - 1)]['memberId'] == $value->memberId) {
                    // $html.=  '<th>00</th>';
                } else {
                    $html .=  '<th class="text-center align-middle" rowspan="' . ($savingsinfo->where('memberId', $value->memberId)->count()) . '">' . $value->M_Code . '</th>';
                    $html .=  '<th  class="text-center align-middle" rowspan="' . ($savingsinfo->where('memberId', $value->memberId)->count()) . '">' . $value->M_name . '</th>';
                }

                // .( empty($value->depositAmount)? '0.00': $value->depositAmount).

                $html .=  '<th>' . $value->accountCode . '</th>';
                $html .=  '<th class="text-right">';
                $html .= '<input type="hidden" id="" name="acc_id[]" value="' . $value->id . '">';
                $html .= '<input type="hidden" id="" name="member_id[]" value="' . $value->memberId . '">';
                $html .= '<input type="hidden" id="" name="samity_id[]" value="' . $value->samityId . '">';
                $html .= '<input type="hidden" id="" name="branch_id[]" value="' . $value->branchId . '">';
                $html .= '<input type="number" name="diposite_amt[]" id="diposite_amt_' . $value->id . '" class="form-control round clsDiposite" value="' . (empty($obACC->depositAmount) ? '0.00' : $obACC->depositAmount) . '" onkeyup="fnCalculate(' . $value->id . ');" min="1">';
                $html .= '</th>';
                $html .=  '<th class="text-right">';
                $html .= '<input type="number" name="inerest_amt[]" id="inerest_amt_' . $value->id . '" class="form-control round clsInterest" value="' . (empty($obACC->interestAmount) ? '0.00' : $obACC->interestAmount) . '" onkeyup="fnCalculate(' . $value->id . ');" min="1">';
                $html .= '</th>';
                $html .=  '<th class="text-right">';
                $html .= '<input type="number" name="withdraw_amt[]" id="withdraw_amt_' . $value->id . '" class="form-control round clsWithdraw" value="' . (empty($obACC->withdrawAmount) ? '0.00' : $obACC->withdrawAmount) . '" onkeyup="fnCalculate(' . $value->id . ');" min="1">';
                $html .= '</th>';
                $html .=  '<th class="text-right">';
                $html .= '<input type="number" name="balance_amt[]" id="balance_amt_' . $value->id . '" class="form-control round clsBalance" readonly value="' . (empty($obACC->openingBalance) ? '0.00' : $obACC->openingBalance) . '" onchange="" min="1">';
                $html .= '</th>';
                // $html.=  '<th class="text-right">'. number_format((MfnService::getSavingsDeposit($filters)), 2).'</th>';
                // $html.=  '<th class="text-right">'. number_format((MfnService::getSavingsInterest($filters)), 2).'</th>';
                // $html.=  '<th class="text-right"> '. number_format((MfnService::getSavingsWithdraw($filters)), 2).'</th>';
                // $html.=  '<th class="text-right"> '. number_format((MfnService::getSavingsBalance($filters)), 2).'</th>';
                // $html.=  '<th style="width: 10%;" class="text-center"><a href= "./status/view/'.$value->id.'" title="View" class="btn btn-primary">View Details</a></th>';
                $html .= '</tr>';
            }

            echo $html;
        } else {
            $html = '';
            $html .= '<tr>';
            $html .= '<th > </th>';
            $html .= '<th > </th>';
            $html .= '<th > </th>';
            $html .= '<th > </th>';
            $html .= '<th > </th>';
            $html .= '<th > </th>';
            $html .= '<th > </th>';
            $html .= '<th > </th>';
            $html .= '<tr>';

            echo $html;
        }
    }
}
