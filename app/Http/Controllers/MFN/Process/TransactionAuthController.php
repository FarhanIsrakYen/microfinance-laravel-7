<?php

namespace App\Http\Controllers\MFN\Process;

use App\Services\HrService as HRS;
use App\Services\CommonService as Common;
use App\Services\MfnService as MFN;
use App\Http\Controllers\Controller;
use App\Model\MFN\SavingsWithdraw;
use App\Model\MFN\Loan;
use App\Model\GNL\Branch;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use DateTime;
use Role;
use App\Services\HrService;
use App\Services\MfnService;
use App\Helpers\RoleHelper;

class TransactionAuthController extends Controller
{

    public function index(Request $req)
    {
            $accessAbleBranchIds = HrService::getUserAccesableBranchIds();
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

        return view('MFN.TransactionAuth.index', compact('samities'));
    }

    public function transactionauthDatatable(Request $request)
    {
        $columns = array(
            0 => 'id',
            1 => 'samity_code',
            2 => 'samity_name',
            3 => 'loan_dis',
            4 => 'loan_col',
            5 => 'savings_col',
            6 => 'savings_wd',
            7 => 'action',
        );
        // Datatable Pagination Variable

        $totalData = 0;

        $totalFiltered = $totalData;
        $limit = $request->input('length');
        $start = $request->input('start');
        $order = $columns[$request->input('order.0.column')];
        $dir = $request->input('order.0.dir');

        // Searching variable
        // $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
        // $SDate = (empty($request->input('SDate'))) ? null : $request->input('SDate');
        // $EDate = (empty($request->input('EDate'))) ? null : $request->input('EDate');samity_id
        $samity_id = (empty($request->input('samity_id'))) ? null : $request->input('samity_id');

        $branchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');
        $curDate = (empty($request->input('curDate'))) ? null : $request->input('curDate');
        $curDate = new DateTime($curDate);
        $curDate = $curDate->format('Y-m-d');
        // Query

        $isBranchOnOpening = MfnService::isOpening($branchID);
        // $isBranchOnOpening = false;

        if ($isBranchOnOpening) {
            $loan = DB::table('mfn_loans')->select('samityId', DB::raw('SUM(loanAmount) as amt'))->where('isAuthorized', 0)->where('is_delete', 0)->where('branchId', $branchID)->where('disbursementDate', '<=', $curDate)->groupBy('samityId')->get();
            $loanCol = DB::table('mfn_loan_collections')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 0)->where('is_delete', 0)->where('branchId', $branchID)->where('collectionDate', '<=',$curDate)->groupBy('samityId')->get();
            $sevingsDep = DB::table('mfn_savings_deposit')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 0)->where('is_delete', 0)->where('branchId', $branchID)->where('date', '<=', $curDate)->groupBy('samityId')->get();
            $sevingsWd = DB::table('mfn_savings_withdraw')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 0)->where('is_delete', 0)->where('branchId', $branchID)->where('date', '<=', $curDate)->groupBy('samityId')->get();
        } else {
            $loan = DB::table('mfn_loans')->select('samityId', DB::raw('SUM(loanAmount) as amt'))->where('isAuthorized', 0)->where('is_delete', 0)->where('branchId', $branchID)->where('disbursementDate', $curDate)->groupBy('samityId')->get();
            $loanCol = DB::table('mfn_loan_collections')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 0)->where('is_delete', 0)->where('branchId', $branchID)->where('collectionDate', $curDate)->groupBy('samityId')->get();
            $sevingsDep = DB::table('mfn_savings_deposit')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 0)->where('is_delete', 0)->where('branchId', $branchID)->where('date', $curDate)->groupBy('samityId')->get();
            $sevingsWd = DB::table('mfn_savings_withdraw')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 0)->where('is_delete', 0)->where('branchId', $branchID)->where('date', $curDate)->groupBy('samityId')->get();
        }

        $samityIds = array_unique(array_merge($loan->pluck('samityId')->toArray(), $loanCol->pluck('samityId')->toArray(), $sevingsDep->pluck('samityId')->toArray(), $sevingsWd->pluck('samityId')->toArray()));

        $shamitys = DB::table('mfn_samity')->whereIn('id', $samityIds)->get();

        if(!empty($samity_id)){

            $shamitys = $shamitys->where('id',$samity_id);

        }
        
        $arrayloan = $loan->toArray();
        $arrayloanCol = $loanCol->toArray();
        $arraysevingsDep = $sevingsDep->toArray();
        $arraysevingsWd = $sevingsWd->toArray();


        $DataSet = array();
        $i = 0;

        // $array [] = 

        foreach ($shamitys as $Row) {


            $TempSet = array();
            $TempSet = [
                'id' => ++$i,
                'samity_code' => $Row->samityCode,
                'samity_name' => $Row->name,
                'loan_dis' => (!empty($loan->where('samityId', $Row->id)->first()->amt) ? $loan->where('samityId', $Row->id)->first()->amt : 0),
                'loan_col' => (!empty($loanCol->where('samityId', $Row->id)->first()->amt) ? $loanCol->where('samityId', $Row->id)->first()->amt : 0),
                'savings_col' => (!empty($sevingsDep->where('samityId', $Row->id)->first()->amt) ? $sevingsDep->where('samityId', $Row->id)->first()->amt : 0),
                'savings_wd' => (!empty($sevingsWd->where('samityId', $Row->id)->first()->amt) ? $sevingsWd->where('samityId', $Row->id)->first()->amt : 0),
                'action' =>  $Row->id,
            ];

            $key = '';
            $key2 = '';
            $key3 = '';

            // 1 => 'samity_code',
            // 2 => 'samity_name',
            // 3 => 'loan_dis',
            // 4 => 'loan_col',
            // 5 => 'savings_col',
            // 6 => 'savings_wd',

            $DataSet[] = $TempSet;
        }
        $json_data = array(
            "draw" => intval($request->input('draw')),
            "recordsTotal" => intval($totalData),
            "recordsFiltered" => intval($totalFiltered),
            "data" => $DataSet,

        );


        echo json_encode($json_data);
    }

    public function ajaxTransactionAuth(Request $request)
    {
        if ($request->ajax()) {

            $myObj = $request->myObj;
            $curDate = $request->curDate;
            $branchID = $request->branchID;

            $curDate = new DateTime($curDate);
            $curDate = $curDate->format('Y-m-d');

            if ($curDate != MFN::systemCurrentDate($branchID)) {
                $notification = array(
                    'message'    => 'Branch Date not matched',
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }


            $flag = true;
            // $loan = DB::table('mfn_loans')->select('samityId', DB::raw('SUM(loanAmount) as amt'))->where('isAuthorized', 0)->where('disbursementDate', $curDate)->groupBy('samityId')->get();
            // $loanCol = DB::table('mfn_loan_collections')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 0)->where('collectionDate', $curDate)->groupBy('samityId')->get();
            // $sevingsDep = DB::table('mfn_savings_deposit')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 0)->where('date', $curDate)->groupBy('samityId')->get();
            // $sevingsWd = DB::table('mfn_savings_withdraw')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 0)->where('date', $curDate)->groupBy('samityId')->get();

            $isBranchOnOpening = MfnService::isOpening($branchID);

            DB::beginTransaction();
            try {

                foreach ($myObj as $Row) {

                    $Id = $Row['ID'];
                    $status = $Row['value'];

                    if ($status == 1) {
                        // date bosbe pore *****************************   

                        if ($isBranchOnOpening) {
                            DB::table('mfn_loans')->where('branchId', $branchID)->where('samityId', $Id)->where('disbursementDate', '<=', $curDate)->update(['isAuthorized' => 1, 'loanStatusId' => 4]);
                            DB::table('mfn_loan_collections')->where('branchId', $branchID)->where('samityId', $Id)->where('collectionDate', '<=', $curDate)->update(['isAuthorized' => 1]);
                            DB::table('mfn_savings_withdraw')->where('branchId', $branchID)->where('samityId', $Id)->where('date', '<=', $curDate)->update(['isAuthorized' => 1]);
                            DB::table('mfn_savings_deposit')->where('branchId', $branchID)->where('samityId', $Id)->where('date', '<=', $curDate)->update(['isAuthorized' => 1]);
                        } else {
                            DB::table('mfn_loans')->where('branchId', $branchID)->where('samityId', $Id)->where('disbursementDate', $curDate)->update(['isAuthorized' => 1, 'loanStatusId' => 4]);
                            DB::table('mfn_loan_collections')->where('branchId', $branchID)->where('samityId', $Id)->where('collectionDate', $curDate)->update(['isAuthorized' => 1]);
                            DB::table('mfn_savings_withdraw')->where('branchId', $branchID)->where('samityId', $Id)->where('date', $curDate)->update(['isAuthorized' => 1]);
                            DB::table('mfn_savings_deposit')->where('branchId', $branchID)->where('samityId', $Id)->where('date', $curDate)->update(['isAuthorized' => 1]);
                        }
                    }
                }

                DB::commit();

                $notification = array(
                    'alert-type'    => 'success',
                    'message'       => 'Successfully Authorized.',
                );
                return response()->json($notification);
            } catch (Exception $e) {

                DB::rollBack();
                $notification = array(
                    'message'    => 'Unsuccessful to Authorized',
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }
        }
    }
}
