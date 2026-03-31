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

class TransactionUnauthController extends Controller
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

        return view('MFN.TransactionUnauth.index',compact('samities'));
    }

    public function transactionUnauthDatatable(Request $request)
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
        $samity_id = (empty($request->input('samity_id'))) ? null : $request->input('samity_id');
        $branchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');
        $curDate = (empty($request->input('curDate'))) ? null : $request->input('curDate');
        $curDate = new DateTime($curDate);
        $curDate = $curDate->format('Y-m-d');
        // Query

        $isBranchOnOpening = MfnService::isOpening($branchID);

        if ($isBranchOnOpening) {
            $loan = DB::table('mfn_loans')->select('samityId', DB::raw('SUM(loanAmount) as amt'))->where('isAuthorized', 1)->where('is_delete', 0)->where('branchId', $branchID)->where('disbursementDate', '<=', $curDate)->groupBy('samityId')->get();
            $loanCol = DB::table('mfn_loan_collections')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 1)->where('is_delete', 0)->where('branchId', $branchID)->where('collectionDate', '<=', $curDate)->groupBy('samityId')->get();
            $sevingsDep = DB::table('mfn_savings_deposit')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 1)->where('is_delete', 0)->where('branchId', $branchID)->where('date', '<=', $curDate)->groupBy('samityId')->get();
            $sevingsWd = DB::table('mfn_savings_withdraw')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 1)->where('is_delete', 0)->where('branchId', $branchID)->where('date', '<=', $curDate)->groupBy('samityId')->get();
        } else {
            $loan = DB::table('mfn_loans')->select('samityId', DB::raw('SUM(loanAmount) as amt'))->where('isAuthorized', 1)->where('is_delete', 0)->where('branchId', $branchID)->where('disbursementDate', $curDate)->groupBy('samityId')->get();
            $loanCol = DB::table('mfn_loan_collections')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 1)->where('is_delete', 0)->where('branchId', $branchID)->where('collectionDate', $curDate)->groupBy('samityId')->get();
            $sevingsDep = DB::table('mfn_savings_deposit')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 1)->where('is_delete', 0)->where('branchId', $branchID)->where('date', $curDate)->groupBy('samityId')->get();
            $sevingsWd = DB::table('mfn_savings_withdraw')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 1)->where('is_delete', 0)->where('branchId', $branchID)->where('date', $curDate)->groupBy('samityId')->get();
        }


        //$datatest = Loan:: select('samityId', DB::raw('SUM(loanAmount) as amt'))->groupBy('samityId')->get();


        $samityIds = array_unique(array_merge($loan->pluck('samityId')->toArray(), $loanCol->pluck('samityId')->toArray(), $sevingsDep->pluck('samityId')->toArray(), $sevingsWd->pluck('samityId')->toArray()));

        $shamitys = DB::table('mfn_samity')->whereIn('id', $samityIds)->get();
        $arrayloan = $loan->toArray();
        $arrayloanCol = $loanCol->toArray();
        $arraysevingsDep = $sevingsDep->toArray();
        $arraysevingsWd = $sevingsWd->toArray();
        if(!empty($samity_id)){

        $shamitys = $shamitys->where('id',$samity_id);

        }



        $DataSet = array();
        $i = 0;

        // $array [] = 

        foreach ($shamitys as $Row) {

            // $key = array_search($Row, array_column($arrayloan,'samityId'));
            // $key2 = array_search($Row, array_column($arrayloanCol,'samityId'));
            // $key3 = array_search($Row, array_column($arraysevingsDep,'samityId'));

            // $ApproveText = ($Row->is_active == 0) ?
            // '<span class="text-danger">Close</span>' :
            // '<span class="text-primary">Active</span>';
            // $branch_date = new DateTime($Row->date);
            // $branch_date = $branch_date->format('d-m-Y');

            // $action_text = 0;




            $TempSet = array();
            $TempSet = [
                'id' => ++$i,
                'samity_code' => $Row->samityCode,
                'samity_name' => $Row->name,
                'loan_dis' => (!empty($loan->where('samityId', $Row->id)->first()->amt) ? $loan->where('samityId', $Row->id)->first()->amt : 0),
                'loan_col' => (!empty($loanCol->where('samityId', $Row->id)->first()->amt) ? $loanCol->where('samityId', $Row->id)->first()->amt : 0),
                'savings_col' => (!empty($sevingsDep->where('samityId', $Row->id)->first()->amt) ? $sevingsDep->where('samityId', $Row->id)->first()->amt : 0),
                'savings_wd' => (!empty($sevingsWd->where('samityId', $Row->id)->first()->amt) ? $sevingsWd->where('samityId', $Row->id)->first()->amt : 0),
                'action'     =>  $Row->id,
            ];

            // $key = '';
            // $key2 = '';
            // $key3 = '';

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
    public function view($id = null,  $date = null)
    {
        // $Issuem = Issuem::where('id', $id)->first();
        // $Issued = Issued::where('issue_bill_no', $Issuem->bill_no)->get();
        // $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
        //     ->orderBy('branch_code', 'ASC')
        //     ->get();

        $curDate = $date;
        $curDate = new DateTime($curDate);
        $curDate = $curDate->format('Y-m-d');
        $Id = $id;
        $shamity_data = DB::table('mfn_samity')->where('id', $Id)->first();

        $isBranchOnOpening = MfnService::isOpening($shamity_data->branchId);

        $loan_data = DB::table('mfn_loans')
            ->where([
                ['samityId', $Id],
                ['isAuthorized', 1],
                ['is_delete', 0],
            ])
            ->where(function ($query) use ($isBranchOnOpening, $curDate) {
                $isBranchOnOpening == true ? $query->where('disbursementDate', '<=', $curDate) : $query->where('disbursementDate', '=', $curDate);
            })
            ->get();

        $loan_col_data = DB::table('mfn_loan_collections AS lc')
            ->join('mfn_loans AS loan', 'loan.id', 'lc.loanId')
            ->where([
                ['lc.samityId', $Id],
                ['lc.isAuthorized', 1],
                ['lc.amount', '!=', 0],
                ['lc.is_delete', 0],
            ])
            ->where(function ($query) use ($isBranchOnOpening, $curDate) {
                $isBranchOnOpening == true ? $query->where('collectionDate', '<=', $curDate) : $query->where('collectionDate', '=', $curDate);
            })
            ->select('lc.id', 'lc.amount', 'loan.loanCode')
            ->get();

        $saveings_wd_data = DB::table('mfn_savings_withdraw AS withdraw')
            ->join('mfn_savings_accounts AS sa', 'sa.id', 'withdraw.accountId')
            ->where([
                ['withdraw.samityId', $Id],
                ['withdraw.isAuthorized', 1],
                ['withdraw.is_delete', 0],
                ['withdraw.amount', '!=', 0],
            ])
            ->where(function ($query) use ($isBranchOnOpening, $curDate) {
                $isBranchOnOpening == true ? $query->where('date', '<=', $curDate) : $query->where('date', '=', $curDate);
            })
            ->select('withdraw.id', 'withdraw.amount', 'sa.accountCode')
            ->get();

        $saveings_dp_data = DB::table('mfn_savings_deposit AS deposit')
            ->join('mfn_savings_accounts AS sa', 'sa.id', 'deposit.accountId')
            ->where([
                ['deposit.samityId', $Id],
                ['deposit.isAuthorized', 1],
                ['deposit.amount', '!=', 0],
                ['deposit.is_delete', 0],
            ])
            ->where(function ($query) use ($isBranchOnOpening, $curDate) {
                $isBranchOnOpening == true ? $query->where('date', '<=', $curDate) : $query->where('date', '=', $curDate);
            })
            ->select('deposit.id', 'deposit.amount', 'sa.accountCode')
            ->get();

        return view('MFN.TransactionUnauth.showdetails', compact('shamity_data', 'curDate', 'loan_data', 'loan_col_data', 'saveings_wd_data', 'saveings_dp_data'));
    }

    public function ajaxTransactionUnauth(Request $request)
    {
        if ($request->ajax()) {

            $SamityID = $request->samity_id;
            $shamity_data = DB::table('mfn_samity')->where('id', $SamityID)->first();

            $curDate = $request->curDate;
            $curDate = new DateTime($curDate);
            $curDate = $curDate->format('Y-m-d');


            if ($curDate != MFN::systemCurrentDate($shamity_data->branchId)) {
                $notification = array(
                    'message'    => 'Branch Date not matched',
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }
            // $loan = DB::table('mfn_loans')->select('samityId', DB::raw('SUM(loanAmount) as amt'))->where('isAuthorized', 0)->where('disbursementDate', $curDate)->groupBy('samityId')->get();
            // $loanCol = DB::table('mfn_loan_collections')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 0)->where('collectionDate', $curDate)->groupBy('samityId')->get();
            // $sevingsDep = DB::table('mfn_savings_deposit')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 0)->where('date', $curDate)->groupBy('samityId')->get();
            // $sevingsWd = DB::table('mfn_savings_withdraw')->select('samityId', DB::raw('SUM(amount) as amt'))->where('isAuthorized', 0)->where('date', $curDate)->groupBy('samityId')->get();

            $isBranchOnOpening = MfnService::isOpening($shamity_data->branchId);

            DB::beginTransaction();
            try {
                $myObjL = $request->myObjL;
                if ($myObjL != null) {
                    foreach ($myObjL as $Row) {

                        $Id = $Row['ID'];
                        $status = $Row['value'];

                        if ($status == 1) {
                            if ($isBranchOnOpening) {
                                DB::table('mfn_loans')->where('id', $Id)->where('disbursementDate', '<=', $curDate)->update(['isAuthorized' => 0, 'loanStatusId' => 1]);
                            } else {
                                DB::table('mfn_loans')->where('id', $Id)->where('disbursementDate', $curDate)->update(['isAuthorized' => 0, 'loanStatusId' => 1]);
                            }
                        }
                    }
                }

                $myObjLC = $request->myObjLC;
                if ($myObjLC != null) {
                    foreach ($myObjLC as $Row) {

                        $Id = $Row['ID'];
                        $status = $Row['value'];

                        if ($status == 1) {
                            if ($isBranchOnOpening) {
                                DB::table('mfn_loan_collections')->where('id', $Id)->where('collectionDate', '<=', $curDate)->update(['isAuthorized' => 0]);
                            } else {
                                DB::table('mfn_loan_collections')->where('id', $Id)->where('collectionDate', $curDate)->update(['isAuthorized' => 0]);
                            }
                        }
                    }
                }
                $myObjSW = $request->myObjSW;

                if ($myObjSW != null) {


                    foreach ($myObjSW as $Row) {

                        $Id = $Row['ID'];
                        $status = $Row['value'];

                        if ($status == 1) {
                            if ($isBranchOnOpening) {
                                DB::table('mfn_savings_withdraw')->where('id', $Id)->where('date', '<=', $curDate)->update(['isAuthorized' => 0]);
                            } else {
                                DB::table('mfn_savings_withdraw')->where('id', $Id)->where('date', $curDate)->update(['isAuthorized' => 0]);
                            }
                        }
                    }
                }

                $myObjSD = $request->myObjSD;
                if ($myObjSD != null) {
                    foreach ($myObjSD as $Row) {

                        $Id = $Row['ID'];
                        $status = $Row['value'];

                        if ($status == 1) {
                            if ($isBranchOnOpening) {
                                DB::table('mfn_savings_deposit')->where('id', $Id)->where('date', '<=', $curDate)->update(['isAuthorized' => 0]);
                            } else {
                                DB::table('mfn_savings_deposit')->where('id', $Id)->where('date', $curDate)->update(['isAuthorized' => 0]);
                            }
                        }
                    }
                }


                DB::commit();
                $notification = array(
                    'alert-type'    => 'success',
                    'message'       => 'Successfully Unauthorized.',
                );
                return response()->json($notification);
            } catch (Exception $e) {
                DB::rollBack();

                $notification = array(
                    'message'    => 'Unsuccessful to Unauthorization',
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }
        }
    }
}
