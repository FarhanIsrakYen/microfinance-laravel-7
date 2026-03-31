<?php

namespace App\Http\Controllers\acc;

use App\Http\Controllers\Controller;
use App\Model\Acc\Ledger;
use App\Model\Acc\OpeningBalanceDetails;
use App\Model\Acc\OpeningBalanceMaster;
use App\Model\Acc\AccDayEnd;
use App\Model\Acc\VoucherDetails;

use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;

use App\Services\RoleService as Role;
use App\Services\HrService as HRS;
use App\Services\CommonService as Common;
use App\Services\AccService as ACCS;

class OpeningBalanceController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {

            $columns = array(
                0 => 'id',
                1 => 'acc_ob_m.opening_date',
                4 => 'acc_ob_m.total_debit_amount',
                5 => 'acc_ob_m.total_credit_amount',
                6 => 'acc_ob_m.total_balance'
            );
            // Datatable Pagination Variable
            $totalData = OpeningBalanceMaster::where('is_delete', '=', 0)->count();
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
            $sl = $start + 1;
            // // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $BranchID = (empty($request->input('BranchID'))) ? null : $request->input('BranchID');
            $projectID = (empty($request->input('projectID'))) ? null : $request->input('projectID');
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $OBData = OpeningBalanceMaster::where(['acc_ob_m.is_delete' => 0, 'gnl_branchs.is_approve' => 1])
                ->select('acc_ob_m.*',
                    'gnl_branchs.branch_name as branch_name','gnl_branchs.branch_code',
                    'gnl_projects.project_name')

                ->whereIn('acc_ob_m.branch_id', HRS::getUserAccesableBranchIds())
                ->leftJoin('gnl_branchs', 'acc_ob_m.branch_id', '=', 'gnl_branchs.id')
                ->leftJoin('gnl_projects', 'acc_ob_m.project_id', '=', 'gnl_projects.id')
                ->where(function ($OBData) use ($search, $BranchID,$projectID,$startDate,$endDate) {
                    if (!empty($search)) {
                        $OBData->where('gnl_branchs.branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_projects.project_name', 'LIKE', "%{$search}%")
                            ->orWhere('acc_ob_m.total_debit_amount', 'LIKE', "%{$search}%")
                            ->orWhere('acc_ob_m.total_credit_amount', 'LIKE', "%{$search}%")
                            ->orWhere('acc_ob_m.total_balance', 'LIKE', "%{$search}%");
                    }
                    if (!empty($BranchID)) {
                        $OBData->where('acc_ob_m.branch_id', $BranchID);
                    }
                    if (!empty($projectID)) {
                        $OBData->where('acc_ob_m.project_id', $projectID);
                    }
                    if (!empty($startDate) && !empty($endDate)) {

                        $startDate = new DateTime($startDate);
                        $startDate = $startDate->format('Y-m-d');

                        $endDate = new DateTime($endDate);
                        $endDate = $endDate->format('Y-m-d');

                        $OBData->whereBetween('acc_ob_m.opening_date', [$startDate, $endDate]);
                    }
                })
                // ->offset($start)
                // ->limit($limit)
                ->orderBy($order, $dir)
                ->orderBy('acc_ob_m.opening_date', 'DESC')
                ->orderBy('acc_ob_m.id', 'DESC');
                // ->get();

            $tempQueryData = clone $OBData;
            $OBData = $OBData->offset($start)->limit($limit)->get();

            $totalData = count($OBData);
            $totalFiltered = $totalData;
            if (!empty($search) || !empty($BranchID) || !empty($projectID) || !empty($startDate) || !empty($endDate)) {
                $totalFiltered = $tempQueryData->count();
            }

            config()->set('database.connections.mysql.strict', false);
            DB::reconnect();
            $checkForEnD = AccDayEnd::where([['is_active', 0], ['is_delete', 0]])
                ->select('branch_id')
                ->orderBy('id', 'DESC')
                ->pluck('branch_id')
                ->toArray();

            $data = array();
            if (!empty($OBData)) {
                // $i = 0;
                foreach ($OBData as $Data) {
                    $IgnoreArray = array();
                    // if (in_array($Data->branch_id, $checkForEnD)) {
                    //     $IgnoreArray = ['delete', 'edit'];
                    // }
                    $nestedData['sl'] = $sl++;
                    $nestedData['opening_date'] = date('d-m-Y', strtotime($Data->opening_date));
                    $nestedData['project_name'] = $Data->project_name;
                    $nestedData['branch_name'] = (!empty($Data->branch_name)) ? $Data->branch_name."(".$Data->branch_code.")" : "";
                    $nestedData['ttl_debit_amt'] = $Data->total_debit_amount;
                    $nestedData['ttl_credit_amt'] = $Data->total_credit_amount;
                    $nestedData['ttl_balance_amt'] = $Data->total_balance;
                    $nestedData['action'] = Role::roleWiseArray($this->GlobalRole, $Data->id, $IgnoreArray);

                    $data[] = $nestedData;
                }
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $data,
                'current_branch_id' => Common::getBranchId(),
                'access_branch' => HRS::getUserAccesableBranchIds(),
            );

            echo json_encode($json_data);
        } else {
            return view('ACC.OpeningBalance.index');
        }
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'branch_id' => 'required',
                'opening_date' => 'required',
                'project_id' => 'required',
                'project_type_id' => 'required',
            ]);

            $RequestData = $request->all();

            $RequestData['opening_date'] = new DateTime($RequestData['opening_date']); // used date time for d/m/y format
            $RequestData['opening_date'] = $RequestData['opening_date']->format('Y-m-d');

            $RequestData['ob_no'] = ACCS::generateBillAccOB($RequestData['branch_id']);

            $fiscal_year = DB::table('gnl_fiscal_year')
                ->select('id')
                ->where('company_id', $RequestData['company_id'])
                ->where('fy_start_date', '<=', $RequestData['opening_date'])
                ->where('fy_end_date', '>=', $RequestData['opening_date'])
                ->orderBy('id', 'DESC')
                ->first();

            if ($fiscal_year) {
                $RequestData['fiscal_year_id'] = $fiscal_year->id;
            }

            $ledger_arr = (isset($RequestData['ledger_arr']) ? $RequestData['ledger_arr'] : array());
            $debit_amount_arr = (isset($RequestData['debit_amount_arr']) ? $RequestData['debit_amount_arr'] : array());
            $credit_amount_arr = (isset($RequestData['credit_amount_arr']) ? $RequestData['credit_amount_arr'] : array());
            $balance_amount_arr = (isset($RequestData['balance_amount_arr']) ? $RequestData['balance_amount_arr'] : array());

            if(count(array_filter($ledger_arr)) <= 0){
                $notification = array(
                    'message' => 'Something went wrong! Please try again.',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

            DB::beginTransaction();
            try {
                $isInsert = OpeningBalanceMaster::create($RequestData);

                // $idMaster = $isInsert->id;

                $lastInsertQuery = OpeningBalanceMaster::latest()->first();
                $idMaster = $lastInsertQuery->id;

                /* Child Table Insertion */
                $RequestData['ob_no'] = $RequestData['ob_no'];
                foreach ($ledger_arr as $key => $ledger_id_sin) {
                    if ($debit_amount_arr[$key] > 0 || $credit_amount_arr[$key] > 0) {

                        $RequestData['ledger_id'] = $ledger_id_sin;
                        $RequestData['debit_amount'] = $debit_amount_arr[$key];
                        $RequestData['credit_amount'] = $credit_amount_arr[$key];
                        $RequestData['balance_amount'] = $balance_amount_arr[$key];

                        $isInsertDetails = OpeningBalanceDetails::create($RequestData);
                    }
                }

                DB::commit();
                $notification = array(
                    'message' => 'Successfully inserted data',
                    'alert-type' => 'success',
                );
                return redirect('acc/acc_ob')->with($notification);
            } catch (\Exception $e) {

                DB::rollBack();

                $notification = array(
                    'message' => 'Unsuccessful to insert datas',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            return view('ACC.OpeningBalance.add');
        }
    }

    public function edit(Request $request, $id = null)
    {
        $OBDataM = OpeningBalanceMaster::where('id', $id)->first();

        $OBDetailsData = OpeningBalanceDetails::where('ob_no', $OBDataM->ob_no)
            ->select('ob_no', 'ledger_id', 'debit_amount', 'credit_amount', 'balance_amount')
			->get();
		$OBDataDG = $OBDetailsData->groupBy('ledger_id');
      // dd($OBDataDG);
		$ladgerData = array();
		$EditLedgerArr = array();
		$EditChildArr = array();

		foreach($OBDataDG as $sigRow){
			array_push($EditLedgerArr, $sigRow->toarray()[0]['ledger_id']);
			$EditChildArr[$sigRow->toarray()[0]['ledger_id']] = $sigRow->toarray()[0];
		}

		if ($OBDataM) {
			$projectID = $OBDataM->project_id;
			$branchID = $OBDataM->branch_id;

            $ladgerData = Ledger::where([['is_delete', 0], ['is_active', 1], ['is_group_head', 0]])
                ->where(function ($ladgerData) use ($projectID, $branchID) {
                    $ladgerData->where('branch_arr', 'LIKE', "%,{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "%,{$branchID}")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID}");

                    $ladgerData->where('project_arr', 'LIKE', "%,{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "%,{$projectID}")
                        ->orWhere('project_arr', 'LIKE', "{$projectID}");
                })
                ->select('id', 'name', 'code', 'acc_type_id', 'parent_id')
                ->orderBy('code', 'ASC')
                ->get();

        }

		// post Data
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'branch_id' => 'required',
                'opening_date' => 'required',
                'project_id' => 'required',
                'project_type_id' => 'required',
            ]);

            $RequestData = $request->all();

            $RequestData['opening_date'] = new DateTime($RequestData['opening_date']); // used date time for d/m/y format
            $RequestData['opening_date'] = $RequestData['opening_date']->format('Y-m-d');

            $RequestData['ob_no'] = $OBDataM->ob_no;

            $ledger_arr = (isset($RequestData['ledger_arr']) ? $RequestData['ledger_arr'] : array());
            $debit_amount_arr = (isset($RequestData['debit_amount_arr']) ? $RequestData['debit_amount_arr'] : array());
            $credit_amount_arr = (isset($RequestData['credit_amount_arr']) ? $RequestData['credit_amount_arr'] : array());
            $balance_amount_arr = (isset($RequestData['balance_amount_arr']) ? $RequestData['balance_amount_arr'] : array());

            if(count(array_filter($ledger_arr)) <= 0){
                $notification = array(
                    'message' => 'Something went wrong! Please try again.',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

            DB::beginTransaction();
            try {
                $isUpdateMaster = $OBDataM->update($RequestData);

                OpeningBalanceDetails::where('ob_no', $RequestData['ob_no'])->get()->each->delete();

                /* Child Table Insertion */
                $RequestData['ob_no'] = $RequestData['ob_no'];

                foreach ($ledger_arr as $key => $ledger_id_sin) {
                    if ($debit_amount_arr[$key] > 0 || $credit_amount_arr[$key] > 0) {

                        $RequestData['ledger_id'] = $ledger_id_sin;
                        $RequestData['debit_amount'] = $debit_amount_arr[$key];
                        $RequestData['credit_amount'] = $credit_amount_arr[$key];
                        $RequestData['balance_amount'] = $balance_amount_arr[$key];
                        // dd($RequestData);
                        $isInsertDetails = OpeningBalanceDetails::create($RequestData);

                    }
                }

                DB::commit();

                $notification = array(
                    'message' => 'Successfully Updated Data',
                    'alert-type' => 'success',
                );

                return redirect('acc/acc_ob')->with($notification);
            } catch (\Exception $e) {
                DB::rollBack();

                $notification = array(
                    'message' => 'Unsuccessful to Updated Data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        }

        $ledger_in_voucher_data = VoucherDetails::select('debit_acc', 'credit_acc')->distinct()->get();

        $ledger_in_voucher_debit = $ledger_in_voucher_data->pluck('debit_acc')->all();
        $ledger_in_voucher_acc = $ledger_in_voucher_data->pluck('credit_acc')->all();

        $ledger_in_voucher = array_merge($ledger_in_voucher_debit, $ledger_in_voucher_acc);
        $ledger_in_voucher = array_unique($ledger_in_voucher);

        // dd($ledger_in_voucher_debit, $ledger_in_voucher_acc, $ledger_in_voucher);

        return view('ACC.OpeningBalance.edit', compact('OBDataM', 'EditLedgerArr', 'EditChildArr','ladgerData', 'ledger_in_voucher'));
    }

    public function view($id = null)
    {
        $OBDataM = OpeningBalanceMaster::where('id', $id)->first();
        $OBDataD = OpeningBalanceDetails::where('ob_no', $OBDataM->ob_no)->get();
        return view('ACC.OpeningBalance.view', compact('OBDataM', 'OBDataD'));
    }

    public function delete($id = null)
    {

        $OBMaster = OpeningBalanceMaster::where('id', $id)->first();


        // $dayendData = AccDayEnd::where(['branch_id' => $OBMaster->branch_id,
        // 'is_active' => 0, 'is_delete' => 0])
        // ->orderBy('id', 'DESC')->first();
        // dd($dayendData);
        // if (!empty($dayendData)) {
        //     $notification = array(
        //         'message' => 'Unable to  Delete this Data',
        //         'alert-type' => 'error',
        //     );
        //     return redirect()->back()->with($notification);
        // } else
         {
            $OBMaster->is_delete = 1;
            $delete = $OBMaster->save();

            if ($delete) {
                $notification = array(
                    'message' => 'Successfully Deleted',
                    'alert-type' => 'success',
                );
                return redirect()->back()->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Delete',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

        }

    }
    public function isActive($id = null)
    {
        $AccOBData = OpeningBalanceMaster::where('id', $id)->first();
        if ($AccOBData->is_active == 1) {
            $AccOBData->is_active = 0;
        } else {
            $AccOBData->is_active = 1;
        }
        $Status = $AccOBData->save();
        if ($Status) {
            $notification = array(
                'message' => 'Successfully Updated',
                'alert-type' => 'success',
            );
            return redirect()->back()->with($notification);
        } else {
            $notification = array(
                'message' => 'Unsuccessful to Update',
                'alert-type' => 'error',
            );
            return redirect()->back()->with($notification);
        }
    }

    public function ajaxBranchLoad(Request $request)
    {

        if ($request->ajax()) {

            $projectID = $request->projectID;
            $projectTypeID = $request->projectTypeID;
            $SelectedVal = $request->SelectedVal;
            // Query
            $QueryData = DB::table('gnl_branchs')
                ->where([['is_delete', 0], ['is_active', 1], ['is_approve', 1],
                    ['project_id', $projectID], ['project_type_id', $projectTypeID]])
                ->whereIn('id', HRS::getUserAccesableBranchIds())
                ->whereNotExists(function ($QueryData){
                    $QueryData->select('branch_id')
                            ->from('acc_ob_m')
                            ->whereRaw('gnl_branchs.id = acc_ob_m.branch_id')
                            ->where([['acc_ob_m.is_delete', 0], ['acc_ob_m.is_active', 1]]);
                })
                ->select(['id', 'branch_name', 'branch_code'])
                ->orderBy('branch_code', 'ASC')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {
                $SelectText = '';

                if ($SelectedVal != null) {
                    if ($SelectedVal == $Row->id) {
                        $SelectText = 'selected="selected"';
                    }
                }
                $output .= '<option value="' . $Row->id . '" ' . $SelectText . '>' . sprintf("%04d", $Row->branch_code) . " - " . $Row->branch_name . '</option>';
            }

            echo $output;
        }
    }

    public function ajaxLedgerLoad(Request $request)
    {
        if ($request->ajax()) {

            $projectID = $request->projectID;
            $branchID = $request->branchID;

            // Query
            $ladgerData = Ledger::where([['is_delete', 0], ['is_active', 1], ['is_group_head', 0]])
                ->where(function ($ladgerData) use ($projectID, $branchID) {
                    $ladgerData->where('branch_arr', 'LIKE', "%,{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID},%")
                        ->orWhere('branch_arr', 'LIKE', "%,{$branchID}")
                        ->orWhere('branch_arr', 'LIKE', "{$branchID}");

                    $ladgerData->where('project_arr', 'LIKE', "%,{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "{$projectID},%")
                        ->orWhere('project_arr', 'LIKE', "%,{$projectID}")
                        ->orWhere('project_arr', 'LIKE', "{$projectID}");
                })
                ->select('id', 'name', 'code', 'acc_type_id', 'parent_id')
                ->orderBy('code', 'ASC')
                ->get();

            // dd($ladgerData);

            /* ------------ Table Start */
            $output = '<table class="table w-full table-hover table-bordered table-striped" id="OBTable1">';

            /* ------------ tHead Start */
            $output .= '<thead>';
            $output .= '<tr>';
            $output .= '<th>SL</th>';
            $output .= '<th>Account Type</th>';
            $output .= '<th>Account Head</th>';
            $output .= '<th>Code</th>';
            $output .= '<th>Debit</th>';
            $output .= '<th>Credit</th>';
            $output .= '<th>Balance</th>';
            $output .= '</tr>';
            $output .= '</thead>';
            /* ------------ tHead End */

            /* ------------ tBody Start */
            $output .= '<tbody>';
            $i = 0;
            foreach ($ladgerData as $Row) {
                $i++;
                $output .= '<tr>';

                $output .= '<td scope="row">' . $i . '</td>';

                $output .= '<td>';
                $output .= '<input type="hidden" name="acc_type_arr[]" id="acc_type_arr_' . $i . '" value="' . $Row->acc_type_id . '">';
                $output .= $Row->account_type['name'];
                $output .= '</td>';

                $output .= '<td>';
                $output .= '<input type="hidden" name="ledger_arr[]" id="ledger_arr_' . $i . '" value="' . $Row->id . '">';
                $output .= $Row->name;
                $output .= '</td>';

                $output .= '<td>';
                $output .= '<input type="hidden" name="ledger_code_arr[]" id="ledger_code_arr_' . $i . '" value="' . $Row->code . '">';
                $output .= $Row->code;
                $output .= '</td>';

                $output .= '<td>';
                $output .= '<input type="number" class="form-control clsCashD" step="any" pattern="[0-9]" value="0"
					name="debit_amount_arr[]" id="debit_amount_' . $i . '"
					onkeyup="fnCalculateTotal(' . $i . ');fnTotalDebit();">';
                $output .= '</td>';

                $output .= '<td>';
                $output .= '<input type="number" class="form-control clsCashC" value="0"
						name="credit_amount_arr[]" id="credit_amount_' . $i . '"
						onkeyup="fnCalculateTotal(' . $i . ');fnTotalCredit();">';
                $output .= '</td>';

                $output .= '<td>';
                $output .= '<input type="number" class="form-control  clsTotal" value="0" readonly
						name="balance_amount_arr[]" id="balance_amount_' . $i . '" >';
                $output .= '</td>';

                $output .= '</tr>';
            }
            $output .= '</tbody>';
            /* ------------ tBody End */

            /* ------------ tFoot Start */
            $output .= '<tfoot>';
            $output .= '<tr>';

            $output .= '<td colspan="4" class="text-right">';
            $output .= '<input type="hidden">';
            $output .= '<h5>Total:</h5>';
            $output .= '</td>';

            $output .= '<td style="font-weight: bold;">';
            $output .= '<input type="hidden" name="total_debit_amount" id="total_debit_amount" value="0" min="1">';
            $output .= '<h5 id="tdTotalDebit">0.00</h5>';
            $output .= '</td>';

            $output .= '<td style="font-weight: bold;">';
            $output .= '<input type="hidden" name="total_credit_amount" id="total_credit_amount" value="0" min="1">';
            $output .= '<h5 id="tdTotalCredit">0.00</h5>';
            $output .= '</td>';

            $output .= '<td style="font-weight: bold;">';
            $output .= '<input type="hidden" name="total_balance" id="total_balance" value="0" min="1">';
            $output .= '<h5 id="tdTotalBalance">0.00</h5>';
            $output .= '</td>';

            $output .= '</tr>';
            $output .= '</tfoot>';
            /* ------------ tFoot End */

            $output .= '</table>';
            /* ------------ Table End */
            echo $output;
        }
    }
}
