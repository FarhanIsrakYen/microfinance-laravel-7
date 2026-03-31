<?php

namespace App\Http\Controllers\ACC;

use App\Http\Controllers\Controller;
use App\Model\Acc\Voucher;
use App\Model\Acc\VoucherDetails;
use App\Model\Acc\VoucherType;
use App\Model\GNL\Branch;
use App\Model\GNL\Project;
use App\Services\AccService as ACCS;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\RoleService as Role;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Redirect;
use Validator;

class VoucherController extends Controller
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
                ->where([['av.is_delete', 0], ['av.is_active', 1], ['av.v_generate_type', 0]])
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
                            ->orWhere('avt.name', 'LIKE', "%{$search}%")
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
                ->where([['av.is_delete', 0], ['av.is_active', 1], ['av.v_generate_type', 0]])
                ->whereIn('av.branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;

            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($branchID) || !empty($projectID)
                || !empty($ProjectTypeID) || !empty($VoucherTypeID)) {

                $totalFiltered = count($tempQueryData->get()->toarray());
            }

            $DataSet = array();
            $i = $start;
            //     <span style="font-size: 48px; color: Dodgerblue;">
            //     <i class="fas fa-camera"></i>
            //   </span>
            $authorized = '<span style="font-size: 16px; color: Dodgerblue;"><i class="fas fa-check"></i></span>';
            $unauth = '<span style="font-size: 16px; color: red;"><i class="far fa-times-circle"></i></span>';

            foreach ($QueryData as $Row) {
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
                    'prep_by' => $Row->UserName {'full_name'},

                    'voucher_status' => (($Row->voucher_status == 0) ? $unauth : $authorized),

                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->id, [])
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
            $vdata = Voucher::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('ACC.Voucher.index', compact('vdata'));
        }
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {

            if ($request['voucher_type_id'] == 1) {
                $request['credit_arr'] = (isset($request['acc_one_arr']) ? $request['acc_one_arr'] : array());
                $request['debit_arr'] = (isset($request['acc_two_arr']) ? $request['acc_two_arr'] : array());
            } else if ($request['voucher_type_id'] == 5) {
                $request['debit_arr'] = (isset($request['acc_one_arr']) ? $request['acc_one_arr'] : array());
                $request['credit_arr'] = (isset($request['acc_two_arr']) ? $request['acc_two_arr'] : array());
            } else {
                $request['debit_arr'] = (isset($request['acc_one_arr']) ? $request['acc_one_arr'] : array());
                $request['credit_arr'] = (isset($request['acc_two_arr']) ? $request['acc_two_arr'] : array());
            }

            $notification = $this->store($request)->getOriginalContent();
            return Redirect::to('acc/vouchers')->with($notification);

        } else {
            // /$acc_data = AccountType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $vtype = VoucherType::where([['is_delete', 0], ['is_active', 1]])->orderBy('id', 'ASC')->get();
            $project = Project::where([['is_delete', 0], ['is_active', 1]])->orderBy('id', 'DESC')->get();

            $ignoreOwnBranch = Common::getBranchId();
            $BranchData = Branch::where([['is_delete', 0], ['is_active', 1], ['is_approve', 1], ['id', '<>', $ignoreOwnBranch]])
                ->orderBy('branch_code', 'ASC')->get();

            return view('ACC.Voucher.add', compact('vtype', 'project', 'BranchData'));
        }
    }

    public function edit(Request $request, $id = null)
    {

        if ($request->isMethod('post')) {

            $RequestData = $request->all();
            $voucher_date = new DateTime($RequestData['voucher_date']);
            $RequestData['voucher_date'] = $voucher_date->format('Y-m-d');

            $voucherdata = Voucher::where('id', $id)->first();

            $ftid = $voucherdata->ft_id;

            if ($RequestData['voucher_type_id'] == 1) {
                $CreditACC = (isset($RequestData['acc_one_arr']) ? $RequestData['acc_one_arr'] : array());
                $DebitACC = (isset($RequestData['acc_two_arr']) ? $RequestData['acc_two_arr'] : array());
            } else if ($RequestData['voucher_type_id'] == 5) {

                $BranchID = $RequestData['branch_id'];

                $DebitACC = (isset($RequestData['acc_one_arr']) ? $RequestData['acc_one_arr'] : array());
                $CreditACC = (isset($RequestData['acc_two_arr']) ? $RequestData['acc_two_arr'] : array());

                $amount_arr = (isset($RequestData['amount_arr']) ? $RequestData['amount_arr'] : array());
                $narration_arr = (isset($RequestData['narration_arr']) ? $RequestData['narration_arr'] : array());

                $RequestData['ft_from'] = $BranchID;
                $TargetBranch = $RequestData['t_branch'];

                // dd($RequestData);
                DB::beginTransaction();
                try {
                    $isUpdate = $voucherdata->update($RequestData);
                    if ($isUpdate) {
                        VoucherDetails::where('voucher_id', $voucherdata->id)->get()->each->delete();

                        /* Child Table Insertion */
                        // $RequestData2['voucher_id'] = $id;
                        $RequestData2['branch_id'] = $RequestData['branch_id'];
                        $RequestData2['ft_from'] = $RequestData['branch_id'];
                        $RequestData2['ft_to'] = $RequestData['t_branch'];
                        $RequestData2['ft_target_acc'] = $RequestData['target_bankcash'];
                        // $RequestData2['voucher_date'] = $RequestData['voucher_date'];
                        $RequestData2['voucher_id'] = $voucherdata->id;

                        foreach ($amount_arr as $key => $Row) {
                            if (!empty($Row)) {
                                $RequestData2['amount'] = $Row;
                                $RequestData2['debit_acc'] = $DebitACC[$key];
                                $RequestData2['credit_acc'] = $CreditACC[$key];
                                $RequestData2['local_narration'] = $narration_arr[$key];

                                $isInsertDetails = VoucherDetails::create($RequestData2);
                            }
                        }
                    }

                    //$BillNo = ACCS::generateBillVoucher($RequestData['t_branch'],$RequestData['voucher_type_id'],$RequestData['project_id'],$RequestData['project_type_id']);
                    $voucherdata2 = Voucher::where(['branch_id' => $RequestData['t_branch'], 'ft_id' => $ftid])->first();

                    // dd($BranchID);

                    $RequestData['voucher_id'] = $voucherdata2->id;
                    $RequestData['branch_id'] = $RequestData['t_branch'];
                    $vid = $voucherdata2->id;

                    try {
                        $isUpdate = $voucherdata2->update($RequestData);
                        if ($isUpdate) {

                            VoucherDetails::where('voucher_id', $RequestData['voucher_id'])->get()->each->delete();

                            /* Child Table Insertion */
                            $RequestData2['voucher_id'] = $RequestData['voucher_id'];
                            $RequestData2['branch_id'] = $RequestData['branch_id'];
                            $RequestData2['ft_target_acc'] = $RequestData['target_bankcash'];
                            // $RequestData2['voucher_date'] = $RequestData['voucher_date'];
                            // $RequestData2['voucher_code'] = $RequestData['voucher_code'];

                            foreach ($amount_arr as $key => $Row) {
                                if (!empty($Row)) {
                                    $RequestData2['amount'] = $Row;
                                    $RequestData2['credit_acc'] = $DebitACC[$key];

                                    if ($RequestData['target_bankcash'] != 0) {
                                        $RequestData2['debit_acc'] = $RequestData['target_bankcash'];
                                    } else {
                                        $RequestData2['debit_acc'] = $CreditACC[$key];
                                    }

                                    $RequestData2['local_narration'] = $narration_arr[$key];

                                    $isInsertDetails = VoucherDetails::create($RequestData2);
                                }
                            }
                        }

                    } catch (Exception $e) {
                        DB::rollBack();
                        $notification = array(
                            'message' => 'Unsuccessful to inserted Voucher Details',
                            'alert-type' => 'error',
                            'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                        );
                        return redirect()->back()->with($notification);
                        //return $e;
                    }

                    if ($TargetBranch != 1) {

                        $voucherdata3 = Voucher::where(['branch_id' => 1, 'ft_id' => $ftid])->first();

                        // $BillNo = ACCS::generateBillVoucher(1,$RequestData['voucher_type_id'],$RequestData['project_id'],$RequestData['project_type_id']);
                        // $vid

                        $RequestData['voucher_id'] = $voucherdata3->id;
                        $RequestData['branch_id'] = 1;
                        $vid = $voucherdata3->id;
                        try {
                            $isUpdate = $voucherdata2->update($RequestData);
                            if ($isUpdate) {
                                VoucherDetails::where('voucher_id', $RequestData['voucher_id'])->get()->each->delete();

                                /* Child Table Insertion */
                                $RequestData2['voucher_id'] = $RequestData['voucher_id'];
                                $RequestData2['branch_id'] = $RequestData['branch_id'];
                                $RequestData2['ft_target_acc'] = $RequestData['target_bankcash'];
                                // $RequestData2['voucher_date'] = $RequestData['voucher_date'];
                                // $RequestData2['voucher_code'] = $RequestData['voucher_code'];

                                foreach ($amount_arr as $key => $Row) {
                                    if (!empty($Row)) {
                                        $RequestData2['amount'] = $Row;
                                        $RequestData2['debit_acc'] = $DebitACC[$key];
                                        $RequestData2['credit_acc'] = $DebitACC[$key];
                                        $RequestData2['local_narration'] = $narration_arr[$key];

                                        $isInsertDetails = VoucherDetails::create($RequestData2);
                                    }
                                }
                            }

                        } catch (Exception $e) {
                            DB::rollBack();
                            $notification = array(
                                'message' => 'Unsuccessful to inserted Voucher Details',
                                'alert-type' => 'error',
                                'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                            );
                            return redirect()->back()->with($notification);
                            //return $e;
                        }

                    }

                    // Your Code here
                    DB::commit();
                    // return
                    $notification = array(
                        'message' => 'Successfully Updated Voucher Details',
                        'alert-type' => 'success',
                    );

                    return Redirect::to('acc/vouchers')->with($notification);
                } catch (Exception $e) {
                    DB::rollBack();
                    $notification = array(
                        'message' => 'Unsuccessful to Update Voucher Details',
                        'alert-type' => 'error',
                        'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                    );
                    return redirect()->back()->with($notification);
                    //return $e;
                }

            } else {
                $DebitACC = (isset($RequestData['acc_one_arr']) ? $RequestData['acc_one_arr'] : array());
                $CreditACC = (isset($RequestData['acc_two_arr']) ? $RequestData['acc_two_arr'] : array());
                # code...
            }

            $amount_arr = (isset($RequestData['amount_arr']) ? $RequestData['amount_arr'] : array());
            $narration_arr = (isset($RequestData['narration_arr']) ? $RequestData['narration_arr'] : array());

            DB::beginTransaction();
            try {
                $isUpdate = $voucherdata->update($RequestData);
                if ($isUpdate) {
                    VoucherDetails::where('voucher_id', $voucherdata->id)->get()->each->delete();
                    /* Child Table Insertion */
                    $RequestData['voucher_id'] = $voucherdata->id;
                    $RequestData['branch_id'] = $RequestData['branch_id'];

                    foreach ($amount_arr as $key => $Row) {
                        if (!empty($Row)) {
                            $RequestData['amount'] = $Row;
                            $RequestData['debit_acc'] = $DebitACC[$key];
                            $RequestData['credit_acc'] = $CreditACC[$key];
                            $RequestData['local_narration'] = $narration_arr[$key];

                            $isInsertDetails = VoucherDetails::create($RequestData);
                        }
                    }
                }

                // Your Code here
                DB::commit();
                // return
                $notification = array(
                    'message' => 'Successfully Updated Voucher Details',
                    'alert-type' => 'success',
                );

                return Redirect::to('acc/vouchers')->with($notification);
            } catch (Exception $e) {
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to update Voucher Details',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
                //return $e;
            }

        } else {

            // /$acc_data = AccountType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $vtype = VoucherType::where([['is_delete', 0], ['is_active', 1]])->orderBy('id', 'ASC')->get();
            $project = Project::where([['is_delete', 0], ['is_active', 1]])->orderBy('id', 'DESC')->get();

            $ignoreOwnBranch = Common::getBranchId();
            $BranchData = Branch::where([['is_delete', 0], ['is_active', 1], ['is_approve', 1], ['id', '<>', $ignoreOwnBranch]])
                ->orderBy('branch_code', 'ASC')->get();

            $voucherdata = Voucher::where(['id' => $id, 'is_delete' => 0])->first();

            $vdatad = VoucherDetails::where('voucher_id', $voucherdata->id)->get();
            //$vdata = Voucher::where('is_delete', 0)->orderBy('id', 'DESC')->get();

            //dd($voucherdata->branch_id);
            if (!empty($voucherdata->branch_id)) {
                if ($voucherdata->branch_id == Common::getBranchId()) {
                    return view('ACC.Voucher.edit', compact('vtype', 'project', 'BranchData', 'voucherdata', 'vdatad'));
                } else {

                    $notification = array(
                        'message' => 'You Are not Allowed to Update/Edit this Voucher',
                        'alert-type' => 'warning',
                        // 'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                    );
                    return redirect()->back()->with($notification);

                }

            } else {
                return view('ACC.Voucher.edit', compact('vtype', 'project', 'BranchData', 'voucherdata', 'vdatad'));

            }

        }
    }

    public function store(Request $request)
    {

        $passport = $this->getPassport($request, $operationType = 'store');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        /* Master Table Data make  */
        $RequestData = new Request;
        $RequestData = $request;

        $voucher_date = new DateTime($RequestData['voucher_date']);
        $RequestData['voucher_date'] = $voucher_date->format('Y-m-d');
        $RequestData['prep_by'] = Auth::id();

        //geting module id
        $RequestData['module_id'] = Common::getModuleId();

        if ($RequestData['voucher_type_id'] == 5) {

            $ftid = Voucher::select('ft_id')->max('ft_id');

            $ftid += 1;

            $BranchID = $RequestData['branch_id'];
            // $RequestData['ft_from'] = $BranchID;
            $TargetBranch = $RequestData['t_branch'];

            $RequestData['ft_id'] = $ftid;
            $RequestData['ft_from'] = $RequestData['branch_id'];
            $RequestData['ft_to'] = $RequestData['t_branch'];
            $RequestData['ft_target_acc'] = $RequestData['target_bankcash'];
            // dd($RequestData);

            /// ///// /// From Branch Voucher Create
            $Status = ACCS::insertVoucher($RequestData);

            $BillNo = ACCS::generateBillVoucher($RequestData['t_branch'], $RequestData['voucher_type_id'], $RequestData['project_id'], $RequestData['project_type_id']);
            $RequestData['voucher_code'] = $BillNo;
            $RequestData['branch_id'] = $RequestData['t_branch'];

            $RequestData['credit_arr'] = (isset($RequestData['acc_one_arr']) ? $RequestData['acc_one_arr'] : array());

            if ($RequestData['target_bankcash'] != 0) {
                $temp = array();
                $tempTarget = $RequestData['target_bankcash'];

                for ($i = 0; $i < count($RequestData['credit_arr']); $i++) {
                    // $RequestData['temp_target_bank_cash']
                    array_push($temp, $tempTarget);
                }

                $RequestData['debit_arr'] = (isset($temp) ? $temp : array());

            } else {
                $RequestData['debit_arr'] = (isset($RequestData['acc_two_arr']) ? $RequestData['acc_two_arr'] : array());

            }

            /// ///// /// To Branch Voucher Create
            $Status = ACCS::insertVoucher($RequestData);

            /// ///// /// H/O Branch Voucher Create
            if ($TargetBranch != 1) {

                $BillNo = ACCS::generateBillVoucher(1, $RequestData['voucher_type_id'], $RequestData['project_id'], $RequestData['project_type_id']);

                $RequestData['voucher_code'] = $BillNo;
                $RequestData['branch_id'] = 1;
                $RequestData['credit_arr'] = (isset($RequestData['acc_one_arr']) ? $RequestData['acc_one_arr'] : array());
                $RequestData['debit_arr'] = (isset($RequestData['acc_one_arr']) ? $RequestData['acc_one_arr'] : array());
                $Status = ACCS::insertVoucher($RequestData);
            }

            return response()->json($Status);

        } else {

            // dd('ss');
            // dd($RequestData);

            $Status = ACCS::insertVoucher($RequestData);

            return response()->json($Status);

        }

        // dd('tetst');

    }

    public function view($id = null)
    {
        $vtype = VoucherType::where([['is_delete', 0], ['is_active', 1]])->orderBy('id', 'ASC')->get();
        $project = Project::where([['is_delete', 0], ['is_active', 1]])->orderBy('id', 'DESC')->get();

        $ignoreOwnBranch = Common::getBranchId();
        $BranchData = Branch::where([['is_delete', 0], ['is_active', 1], ['is_approve', 1], ['id', '<>', $ignoreOwnBranch]])
            ->orderBy('branch_code', 'ASC')->get();

        $voucherdata = Voucher::where(['id' => $id, 'is_delete' => 0])->first();

        $vdatad = VoucherDetails::where('voucher_id', $voucherdata->id)->get();

        return view('ACC.Voucher.view', compact('vtype', 'project', 'BranchData', 'voucherdata', 'vdatad'));
    }

    public function delete($id = null)
    {
        $Voucher = Voucher::where('id', $id)->first();

        $Voucher->is_delete = 1;
        DB::beginTransaction();
        try {
            // Your Code here
            $delete = $Voucher->save();
            if ($delete) {
                // VoucherDetails::where('voucher_id', $id)->get()->each->delete();
            }
            DB::commit();
            // return
            $notification = array(
                'message' => 'Successfully Deleted',
                'alert-type' => 'success',
            );
            return redirect()->back()->with($notification);
        } catch (Exception $e) {
            DB::rollBack();
            $notification = array(
                'message' => 'Unsuccessful to Delete',
                'alert-type' => 'error',
                'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
            );
            return redirect()->back()->with($notification);
        }

    }

    public function getPassport($req, $operationType, $withdraw = null)
    {
        $errorMsg = null;

        // dd( $req->all());
        if ($operationType != 'delete') {

            $rules = array(
                'voucher_code' => 'required',
                'voucher_date' => 'required',
                'project_type_id' => 'required',
                'project_id' => 'required',
                'branch_id' => 'required',
                'company_id' => 'required',
                'acc_one_arr.*' => 'required',
                'acc_two_arr.*' => 'required',
                'amount_arr.*' => 'required',

                // 'narration_arr.*' => 'required'
            );

            if ($operationType == 'store') {
                // $rules['target_bankcash'] = 'required';
            }

            if ($req->voucher_type_id == 5) {
                $rules = array_merge($rules, array(
                    't_branch' => 'required',
                    'target_bankcash' => 'required',
                ));
            }

            $validator = Validator::make($req->all(), $rules);

            $attributes = array(
                'voucher_code' => 'Voucher Code',
                'voucher_date' => 'Voucher Date',
                'project_type_id' => 'Project Type',
                'project_id' => 'Project',
                'branch_id' => 'Branch',
                'company_id' => 'Company',
                'acc_one_arr.*' => 'Ledger Account 1st',
                'acc_two_arr.*' => 'Ledger Account 1st',
                'amount_arr.*' => 'Amount',
                't_branch' => 'Target Branch',
                'target_bankcash' => 'Target Branch Ledger',

            );

            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        // if ($errorMsg == null && $operationType == 'store') {

        //     // if product is regular
        //     if ($account->productTypeId == 1) {
        //         // in case of storing data if multiple transaction is not allowed, then stop
        //         $savConfig = json_decode(DB::table('mfn_config')->where('title', 'savings')->first()->content);

        //         if ($savConfig->allowMultipleTransaction == 'no') {
        //             $isAnyTransactionExistsToday = DB::table('mfn_savings_withdraw')
        //                 ->where([
        //                     ['is_delete', 0],
        //                     ['accountId', $req->accountId],
        //                     ['date', $withdrawDate],
        //                 ])
        //                 ->exists();
        //             if ($isAnyTransactionExistsToday) {
        //                 $errorMsg = "Transaction Exists Today.";
        //             }
        //         }
        //     }
        // }

        // if ($errorMsg == null && $operationType != 'delete') {
        //     // check does it make negetive balance
        //     $filters['accountId'] = $account->id;
        //     $filters['neglectAmount'] = $operationType == 'store' ? $req->amount : $req->amount - $withdraw->amount;
        //     $balance = mfnService::getSavingsBalance($filters);
        //     if ($balance < 0) {
        //         $errorMsg = 'This makes negetive balance';
        //     }
        //     $filters['dateTo'] = $withdrawDate;
        //     $balance = mfnService::getSavingsBalance($filters);
        //     if ($balance < 0) {
        //         $errorMsg = 'This makes negetive balance on this date.';
        //     }
        // }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg,
        );
        //dd(  $passport );
        return $passport;
    }

}
