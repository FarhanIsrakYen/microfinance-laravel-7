<?php

namespace App\Http\Controllers\ACC;

use App\Http\Controllers\Controller;
use App\Model\Acc\Voucher;
use App\Model\Acc\VoucherDetails;
use App\Model\Acc\VoucherType;
use App\Model\GNL\Branch;
use App\Model\GNL\Project;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\RoleService as Role;
use DateTime;
use DB;
use Illuminate\Http\Request;

class AutoVoucherController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {

        if ($request->ajax()) {
            // dd('tscscscsts');
            $columns = array(
                0 => 'av.id',
                1 => 'av.voucher_date',
                2 => 'av.voucher_code',
                6 => 'sum',
            );
            // Datatable Pagination Variable

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

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
                ->where([['av.is_delete', 0], ['av.is_active', 1], ['av.v_generate_type', 1]])
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
                            ->orWhere('avd.amount', 'LIKE', "%{$search}%");
                    }
                    if (!empty($VoucherTypeID)) {

                        $QueryData->where('av.voucher_type_id', $VoucherTypeID);
                    }
                    if (!empty($projectID)) {

                        $QueryData->where('av.project_id', $projectID);
                    }
                    if (!empty($ProjectTypeID)) {

                        $QueryData->where('av.project_type_id', $ProjectTypeID);
                    }
                    if (!empty($branchID)) {

                        $QueryData->where('av.branch_id', $branchID);
                    }
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
                ->where([['av.is_delete', 0], ['av.is_active', 1], ['av.v_generate_type', 1]])
                ->whereIn('av.branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;

            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($branchID) || !empty($projectID)
                || !empty($ProjectTypeID) || !empty($VoucherTypeID)) {

                $totalFiltered = count($tempQueryData->get()->toarray());
            }

            $DataSet = array();
            $i = $start;
            $authorized = '<span style="font-size: 16px; color: Dodgerblue;"><i class="fas fa-check"></i></span>';
            $unauth = '<span style="font-size: 16px; color: red;"><i class="far fa-times-circle"></i></span>';

            foreach ($QueryData as $Row) {
                $TempSet = array();

                $IgnoreArray = array();

                $IgnoreArray = ['edit', 'delete'];

                $TempSet = [
                    'id' => ++$i,
                    'voucher_date' => $Row->voucher_date,
                    'voucher_code' => $Row->voucher_code,
                    'voucher_type_id' => $Row->voucher_name,
                    'project_id' => $Row->project_name,
                    'branch_id' => (!empty($Row->branch['branch_name'])) ? $Row->branch['branch_name'] . "(" . $Row->branch['branch_code'] . ")" : "",
                    'sum' => $Row->sum,
                    'global_narration' => $Row->global_narration,
                    'prep_by' => $Row->UserName {'full_name'},

                    'status' => (($Row->voucher_status == 0) ? $unauth : $authorized),

                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->id, $IgnoreArray),
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
            //
            $vdata = Voucher::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('ACC.AutoVoucher.index', compact('vdata'));
        }
    }

    public function view($id = null)
    {
        $vtype = VoucherType::where('is_delete', 0)->orderBy('id', 'ASC')->get();
        $project = Project::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        $BranchData = Branch::where(['is_delete' => 0])
            ->orderBy('branch_code', 'ASC')->get();
        $voucherdata = Voucher::where('id', $id)->first();
        $vdatad = VoucherDetails::where('voucher_id', $voucherdata->id)->get();
        //$vdata = Voucher::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('ACC.AutoVoucher.view', compact('vtype', 'project', 'BranchData', 'voucherdata', 'vdatad'));
    }

}
