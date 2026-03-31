<?php

namespace App\Http\Controllers\ACC;

use App\Http\Controllers\Controller;
use App\Model\Acc\Voucher;
use App\Model\GNL\Branch;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;

class AutoAuthVoucherController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
        //
        if ($request->ajax()) {
            $columns = array(
                0 => 'id',
                1 => 'voucher_date',
                2 => 'voucher_code',
                6 => 'sum',
            );
            // Datatable Pagination Variable

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
            // dd($limit ,$start,$order,$dir);
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $SDate = (empty($request->input('sDate'))) ? null : $request->input('sDate');
            $EDate = (empty($request->input('eDate'))) ? null : $request->input('eDate');
            $branchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');
            $projectID = (empty($request->input('projectID'))) ? null : $request->input('projectID');
            $ProjectTypeID = (empty($request->input('ProjectTypeID'))) ? null : $request->input('ProjectTypeID');
            $VoucherTypeID = (empty($request->input('VoucherTypeID'))) ? null : $request->input('VoucherTypeID');

            config()->set('database.connections.mysql.strict', false);
            DB::reconnect();

            $QueryData = Voucher::from('acc_voucher as av')
                ->where('av.is_delete', '=', 0)
                ->where('av.is_active', '=', 1)
                ->where('av.voucher_status', '=', 1)
                ->where('av.v_generate_type', '=', 1)
                ->whereIn('av.branch_id', HRS::getUserAccesableBranchIds())
                ->select('av.*', 'avt.name as voucher_name', 'gp.project_name', DB::raw('SUM(avd.amount) as sum'))
                ->leftjoin('acc_voucher_type as avt', function ($QueryData) {
                    $QueryData->on('av.voucher_type_id', 'avt.id')
                        ->where([['avt.is_delete', 0], ['avt.is_active', 1]]);
                })
                ->leftjoin('gnl_projects as gp', function ($QueryData) {
                    $QueryData->on('av.project_id', 'gp.id')
                        ->where([['gp.is_delete', 0], ['gp.is_active', 1]]);
                })

                ->where(function ($QueryData) use ($search, $SDate, $EDate, $branchID, $projectID, $ProjectTypeID, $VoucherTypeID) {
                    if (Common::getBranchId() != 1) {
                        $QueryData->where('av.branch_id', Common::getBranchId());
                    }
                    if (!empty($search)) {
                        $QueryData->where('av.voucher_code', 'LIKE', "%{$search}%")
                            ->orWhere('av.global_narration', 'LIKE', "%{$search}%")
                            ->orWhere('gp.project_name', 'LIKE', "%{$search}%")
                            ->orWhere('avd.amount', 'LIKE', "%{$search}%");
                    }

                })
                ->where(function ($QueryData) use ($VoucherTypeID) {

                    if (!empty($VoucherTypeID)) {

                        $QueryData->where('av.voucher_type_id', $VoucherTypeID);
                    }

                })
                ->where(function ($QueryData) use ($branchID) {

                    if (!empty($branchID)) {

                        $QueryData->where('av.branch_id', $branchID);
                    }

                })
                ->where(function ($QueryData) use ($projectID, $ProjectTypeID) {

                    if (!empty($projectID)) {

                        $QueryData->where('av.project_id', $projectID);
                    }
                    if (!empty($ProjectTypeID)) {

                        $QueryData->where('av.project_type_id', $ProjectTypeID);
                    }

                })
                ->where(function ($QueryData) use ($SDate, $EDate) {
                    if (!empty($SDate) && !empty($EDate)) {

                        $SDate = new DateTime($SDate);
                        $SDate = $SDate->format('Y-m-d');

                        $EDate = new DateTime($EDate);
                        $EDate = $EDate->format('Y-m-d');

                        $QueryData->whereBetween('av.voucher_date', [$SDate, $EDate]);
                    }
                })

                ->leftjoin('acc_voucher_details as avd', function ($QueryData) {
                    $QueryData->on('avd.voucher_id', 'av.id');
                })
                ->groupBy('avd.voucher_id')
                ->orderBy($order, $dir);

            $tempQueryData = clone $QueryData;
            $QueryData = $QueryData->offset($start)->limit($limit)->get();

            $totalData = DB::table('acc_voucher as av')
                ->where('av.is_delete', '=', 0)
                ->where('av.is_active', '=', 1)
                ->where('av.voucher_status', '=', 1)
                ->where('av.v_generate_type', '=', 1)
                ->whereIn('av.branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;

            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($branchID) || !empty($projectID)
                || !empty($ProjectTypeID) || !empty($VoucherTypeID)) {

                $totalFiltered = count($tempQueryData->get()->toarray());
            }

            $DataSet = array();
            $i = $start;
            // $pending = '<a type="button" class="btn btn-danger btn-sm" ><i class="fad fa-info-circle"></i> Authorize</a>';
            foreach ($QueryData as $Row) {
                $field = ' <input type="checkbox" class="clsvoucher" data="' . $Row->id . '" id="item_' . $Row->id . '" />';

                $TempSet = array();
                $TempSet = [
                    'id' => ++$i,
                    'voucher_date' => $Row->voucher_date,
                    'voucher_code' => $Row->voucher_code,
                    'voucher_type_id' => $Row->voucher_name,
                    'project_id' => $Row->project_name,
                    'branch_id' => (!empty($Row->branch['branch_name'])) ? $Row->branch['branch_name'] . "(" . $Row->branch['branch_code'] . ")" : "",
                    'sum' => $Row->sum,
                    'global_narration' => $Row->global_narration,
                    'prep_by' => $Row->UserName['full_name'],
                    'status' => $field,
                    // 'action' => Role::roleWiseArray($this->GlobalRole, $Row->id, [])
                ];

                $DataSet[] = $TempSet;
            }
            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $DataSet,
            );

            echo json_encode($json_data);

        } else {
            // $vdata = Voucher::where('is_delete', '=', 0)->where('voucher_status', '=', 1)->where('v_generate_type', '=',1)
            // ->whereIn('branch_id', HRS::getUserAccesableBranchIds())->get();
            return view('ACC.AutoAuthVoucher.index');
        }
    }

    public function isAuth($id = null)
    {
        $voucherData = Voucher::where('id', $id)->first();

        dd($voucherData);

        if ($voucherData->voucher_status == 1) {

            $voucherData->auth_by = 0;
            $voucherData->voucher_status = 0;
            $isSuccess = $voucherData->update();

            if ($isSuccess) {

                $notification = array(

                    'message' => 'Successfully Unauthorized',
                    'alert-type' => 'success',
                );
                return redirect()->back()->with($notification);
            }
        }
    }

}
