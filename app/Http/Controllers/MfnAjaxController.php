<?php

namespace App\Http\Controllers;

use App\Model\Acc\Voucher;
use App\Model\GNL\Area;
use App\Model\GNL\Branch;
use App\Model\GNL\MapZoneArea;
use App\Model\POS\Collection;
use App\Model\POS\Customer;
use App\Model\POS\PProcessingFee;
use App\Model\POS\Product;
use App\Model\POS\SaleReturnd;
use App\Model\POS\SalesMaster;
use App\Model\POS\Supplier;

use Datetime;
use Facade\Ignition\QueryRecorder\Query;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Picqer;

use App\Services\HrService as HRS;
use App\Services\CommonService as Common;
use App\Services\PosService as POSS;
use App\Services\AccService as ACCS;

class MfnAjaxController extends Controller
{

    public function ajaxSelectBoxLoadObj(Request $request)
    {

        if ($request->ajax()) {
            
            $FeildVal = $request->FeildVal;
            $TableName = base64_decode($request->TableName);
            $WhereColumn = base64_decode($request->WhereColumn);
            $SelectColumn = base64_decode($request->SelectColumn);
            $SelectArr = explode(',', $SelectColumn);
            // dd($SelectArr);

            $PrimaryKey = $SelectArr[0];
            $DisplayKey = $SelectArr[1];

            // $SelectedVal = $request->SelectedVal;

            // Query
            $QueryData = DB::table($TableName)
                ->where([$WhereColumn => $FeildVal, 'is_delete' => 0])
                ->select([$PrimaryKey, $DisplayKey])
                // ->orderBy([$SelectArr[1] => 'ASC'])
                ->get();

                // dd($QueryData);

            // if(count($QueryData->toarray()) > 0){
            //     return  $QueryData;
            // }
            // else{
            //     return false;
            // }

        

            return  $QueryData;
        }
    }

    public function ajaxSelectBoxLoadForMember(Request $request)
    {

        if ($request->ajax()) {

            $FeildVal = $request->FeildVal;
            $TableName = base64_decode($request->TableName);
            $WhereColumn = base64_decode($request->WhereColumn);
            $SelectColumn = base64_decode($request->SelectColumn);
            $SelectArr = explode(',', $SelectColumn);
            $PrimaryKey = $SelectArr[0];
            $DisplayKey = $SelectArr[1];
            $DisplayCode = $SelectArr[2];
            // dd( $DisplayCode);
            $SelectedVal = $request->SelectedVal;

            // Query
            $QueryData = DB::table($TableName)
                ->where([$WhereColumn => $FeildVal, 'is_delete' => 0])
                ->whereDate('closingDate', '=', '0000-00-00')
                ->select([$PrimaryKey, $DisplayKey, $DisplayCode])
            // ->orderBy([$SelectArr[1] => 'ASC'])
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $SelectText = '';

                if ($SelectedVal != null) {
                    if ($SelectedVal == $Row->$PrimaryKey) {
                        $SelectText = 'selected="selected"';
                    }
                }
                $output .= '<option value="' . $Row->$PrimaryKey . '" ' . $SelectText . '>' . $Row->$DisplayCode . " - " . $Row->$DisplayKey . '</option>';
            }

            echo $output;
        }
    }
    public function ajaxSelectBoxLoadForMemberDetails(Request $request)
    {

        if ($request->ajax()) {

            $memberID = $request->memberID;
            $samityID = $request->samityID;
            // Query
            $queryData = DB::table('mfn_members as mb')
                ->where(['mb.id' => $memberID, 'mb.is_delete' => 0])
                ->whereDate('mb.closingDate', '=', '0000-00-00')
                ->leftjoin('mfn_loan_products as lp', function ($queryData) {
                    $queryData->on('mb.primaryProductId', '=', 'lp.id')
                        ->where([['lp.is_delete', 0]]);
                })
                ->select('mb.name','mb.memberCode','lp.name as productName')
            // ->orderBy([$SelectArr[1] => 'ASC'])
                ->get();
            

            $queryData2 = DB::table('mfn_samity as samity')
                ->where(['samity.id' => $samityID, 'samity.is_delete' => 0])
                ->leftjoin('mfn_working_areas as warea', function ($queryData2) {
                    $queryData2->on('samity.workingAreaId', '=', 'warea.id')
                        ->where([['samity.is_delete', 0]]);
                })
                ->select('warea.name as workingArea')
            // ->orderBy([$SelectArr[1] => 'ASC'])
                ->get();


            $savAccs = DB::table('mfn_savings_accounts AS savAcc')
            ->leftJoin('mfn_savings_product AS savP', 'savP.id', 'savAcc.savingsProductId')
                ->where([
                    ['savAcc.is_delete', 0],
                    ['savAcc.memberId', $memberID],
                    ['savAcc.closingDate', '0000-00-00'],
                ])
                ->select('savP.name AS savingsProduct', 'savAcc.*')
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
                $savAccs[$key]->totalDeposit = $deposits->where('accountId', $savAcc->id)->sum('amount');
                $savAccs[$key]->totalWithdraw = $withdraws->where('accountId', $savAcc->id)->sum('amount');
                $savAccs[$key]->savingsBalance = $savAccs[$key]->totalDeposit - $savAccs[$key]->totalWithdraw;
            }
        
            $response = array(
                "productName" => $queryData[0]->productName,
                "workingArea" => $queryData2[0]->workingArea,
                "savings" => $savAccs->toarray(),
            );
            echo json_encode($response);
        }
    }

    public function ajaxSystemCurrentDate(Request $request)
    {

        if ($request->ajax()) {

            $sysDate = DB::table('mfn_day_end')
                ->where([
                    ['branchId', $request->branchID],
                    ['isActive', 1],
                ])
                ->max('date');

            if ($sysDate == null) {
                $sysDate = DB::table('gnl_branchs')
                    ->where('id', $request->branchID)
                    ->first()
                    ->mis_soft_start_date;
            }
            echo json_encode($sysDate);
        }
    }

    public function ajaxCustDataLoad(Request $request)
    {

        if ($request->ajax()) {

            $data = Customer::where(['id' => $request->selectedData])->select('customer_name', 'customer_mobile', 'customer_nid')->first();

            return response()->json(array('id' => $request->selectedData, 'customer_name' => $data->customer_name, 'customer_mobile' => $data->customer_mobile, 'customer_nid' => $data->customer_nid));
        }
    }
    
    public function ajaxSelectBoxLoadForFieldOfficer(Request $request)
    {

        if ($request->ajax()) {
            $SelectColumn = base64_decode($request->SelectColumn);
            $branchId = $request->FeildVal;
            $SelectArr = explode(',', $SelectColumn);
            $PrimaryKey = $SelectArr[0];
            $DisplayKey = $SelectArr[1];

            $SelectedVal = $request->SelectedVal;

            // Query
            $queryData = DB::table('mfn_samity as ms')
                        ->where([['ms.is_delete', 0],['ms.branchId',$branchId]])
                        ->leftjoin('hr_employees as he', function ($queryData) {
                            $queryData->on('ms.fieldOfficerEmpId', '=', 'he.id')
                                ->where([['he.is_delete', 0], ['he.is_active', 1]]);
                        })
                      ->select('he.id', 'he.emp_name')
                      ->groupBy('he.id')
                      ->get();

            $output = '<option value="">All</option>';
            foreach ($queryData as $row) {

                $SelectText = '';

                if ($SelectedVal != null) {
                    if ($SelectedVal == $row->$PrimaryKey) {
                        $SelectText = 'selected="selected"';
                    }
                }
                $output .= '<option value="' . $row->$PrimaryKey . '" ' . $SelectText . '>' . $row->$DisplayKey . '</option>';
            }

            echo $output;
        }
    }
}
