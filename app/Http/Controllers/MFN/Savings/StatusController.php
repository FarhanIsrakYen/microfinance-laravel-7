<?php

namespace App\Http\Controllers\MFN\Savings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\MfnService;
use DB;
use App\Model\MFN\Member;
use App\Model\MFN\SavingsAccount;
use App\Model\MFN\SavingsDeposit;
use App\Model\MFN\SavingsWithdraw;
use Carbon\Carbon;
use DateTime;
use Validator;
use App\Services\HrService;

class StatusController extends Controller
{
    public function index(Request $req)
    {
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

        if (count($branchces) == 1) {
            $samities = DB::table('mfn_samity')
                ->where([
                    ['is_delete', 0]
                ])
                ->whereIn('branchId', $accessAbleBranchIds)
                ->orderBy('samityCode')
                ->select(DB::raw("id, CONCAT(samityCode, ' - ', name) AS name"))
                ->get();
        }
        else{
            $samities = [];
        }
        
        $products = DB::table('mfn_savings_product')
            ->where([
                ['is_delete', 0]
            ])
            ->orderBy('productCode')
            ->select(DB::raw("id, CONCAT(productCode, ' - ', name) AS name"))
            ->get();

        $data = array(
            'branchces' => $branchces,
            'samities' => $samities,
            'products' => $products,
        );

        return view('MFN.Savings.Status.index', $data);
    }

    public function SavingsStatusDetails(Request $req)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();
        //    
        $search_branch =  $req->filBranch;
        $search_samity =  $req->filSamity;
        $search_product =  $req->filProduct;
        $search_memberCode =  $req->memberCode;
        $search_memberStatus =  $req->memberStatus;
        $search_dateTo =  null;
        if ($req->dateTo != null) {
            $search_dateTo =  new DateTime($req->dateTo);
            $search_dateTo = $search_dateTo->format('Y-m-d');
        }

        $savingsStatus = SavingsAccount::where('mfn_savings_accounts.is_delete', 0)
            ->whereIn('mfn_savings_accounts.branchId', $accessAbleBranchIds)
            ->select('mfn_savings_accounts.*', 'mfn_members.name As M_name', 'mfn_members.memberCode As M_Code', 'mfn_members.closingDate As M_status')
            ->leftJoin('mfn_members', 'mfn_members.id', 'mfn_savings_accounts.memberId')
            ->where(function ($savingsStatus) use ($search_branch, $search_samity, $search_product, $search_memberCode, $search_dateTo, $search_memberStatus) {
                if (!empty($search_memberCode)) {
                    $savingsStatus->where('mfn_members.memberCode', 'LIKE', "%{$search_memberCode}%");
                }
                if (!empty($search_memberStatus)) {
                    if ($search_memberStatus == 1) {
                        $savingsStatus->where('mfn_members.closingDate', '0000-00-00');
                    } else {
                        $savingsStatus->where('mfn_members.closingDate', '!=', '0000-00-00');
                    }
                }
                if (!empty($search_branch)) {
                    $savingsStatus->where('mfn_savings_accounts.branchId', '=', $search_branch);
                }
                if (!empty($search_samity)) {
                    $savingsStatus->where('mfn_savings_accounts.samityId', '=', $search_samity);
                }
                if (!empty($search_product)) {
                    $savingsStatus->where('mfn_savings_accounts.savingsProductId', '=', $search_product);
                }


                // if (!empty($Customercode)) {
                //     $savingsStatus->where('pos_customers.customer_code', '=', $Customercode);
                // }

                // if (!empty($CustomerName)) {
                //     $savingsStatus->where('pos_customers.customer_name', '=', $CustomerName);
                // }

            })
            ->orderBy('mfn_savings_accounts.memberId')
            ->get();




        $html = '';
        $i = 0;

        // $tempMemberID =  $savingsStatus[0]['memberId'];
        //    $tempMember =

        foreach ($savingsStatus as $key => $value) {

            $filters['accountId'] = $value->id;
            if (!empty($search_dateTo)) {
                $filters['dateTo'] = $search_dateTo;
            }
            // $savingsStatus->where('memberId',$value->memberId)->count()




            $html .= '<tr>';
            $html .= '<th style="width: 3%;">' . ($key + 1) . '</th>';


            if (!empty($savingsStatus[($key - 1)]['memberId']) && $savingsStatus[($key - 1)]['memberId'] == $value->memberId) {
                // $html.=  '<th>00</th>';
            } else {
                $html .=  '<th class="text-center align-middle" rowspan="' . ($savingsStatus->where('memberId', $value->memberId)->count()) . '">' . $value->M_Code . '</th>';
                $html .=  '<th  class="text-center align-middle" rowspan="' . ($savingsStatus->where('memberId', $value->memberId)->count()) . '">' . $value->M_name . '</th>';
            }

            $html .=  '<th>' . $value->accountCode . '</th>';
            $html .=  '<th class="text-right">' . number_format((MfnService::getSavingsDeposit($filters)), 2) . '</th>';
            $html .=  '<th class="text-right">' . number_format((MfnService::getSavingsInterest($filters)), 2) . '</th>';
            $html .=  '<th class="text-right"> ' . number_format((MfnService::getSavingsWithdraw($filters)), 2) . '</th>';
            $html .=  '<th class="text-right"> ' . number_format((MfnService::getSavingsBalance($filters)), 2) . '</th>';
            $html .=  '<th style="width: 10%;" class="text-center"><a href= "./status/view/' . $value->id . '" title="View" class="btn btn-primary">View Details</a></th>';
            $html .= '</tr>';
        }

        echo $html;
    }
    public function view($id = null)
    {
        $RowID = $id;
        $deposits = SavingsDeposit::where('accountId', $RowID)
            ->where('is_delete', 0)
            ->where('amount', '>', 0)
            ->whereIn('transactionTypeId', [1, 2, 4, 6, 7])
            ->get();
        $withdraws = SavingsWithdraw::where('accountId', $RowID)->where('is_delete', 0)->where('amount', '>', 0)->get();

        $acc =  SavingsAccount::where('mfn_savings_accounts.is_delete', 0)->where('id', $RowID)->first();
        $memberDetails = Member::where('id', $acc->memberId)->select('*')->first();
        $transactionDates = array_unique(array_merge($deposits->where('transactionTypeId', '!=', 4)->pluck('date')->toArray(), $withdraws->where('transactionTypeId', '!=', 4)->pluck('date')->toArray()));
        sort($transactionDates);

        // $html = '';

        $balance = 0;


        return view('MFN.Savings.Status.view', compact('deposits', 'withdraws', 'transactionDates', 'acc', 'memberDetails'));
    }
}
