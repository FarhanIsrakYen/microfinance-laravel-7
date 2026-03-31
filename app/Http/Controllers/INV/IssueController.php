<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\INV\IssueDetails;
use App\Model\INV\IssueMaster;
use App\Model\INV\RequisitionMaster;

use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\RoleService as Role;
use App\Services\InvService as INVS;

use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;

class IssueController extends Controller
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
                0 => 'inv_issues_m.id',
                1 => 'inv_issues_m.bill_no',
                2 => 'inv_issues_m.issue_date',
                3 => 'inv_issues_m.total_quantity',
                4 => 'inv_issues_m.branch_from',
                5 => 'inv_issues_m.branch_to',
                6 => 'action',
            );

            // Datatable Pagination Variable
            $totalData = IssueMaster::where('is_delete', '=', 0)
                ->whereIn('inv_issues_m.branch_from', HRS::getUserAccesableBranchIds())
                ->count();
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable

            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $SDate = (empty($request->input('sDate'))) ? null : $request->input('sDate');
            $EDate = (empty($request->input('eDate'))) ? null : $request->input('eDate');
            $branchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');
            $Type = (empty($request->input('Type'))) ? null : $request->input('Type');

            //dd($EDate);

            // Query
            $QuerryData = IssueMaster::where('inv_issues_m.is_delete', '=', 0)
                ->whereIn('inv_issues_m.branch_from', HRS::getUserAccesableBranchIds())
                ->select('inv_issues_m.*', 'B1.branch_name as branch_name_from','B1.branch_code as branch_code_from', 'B2.branch_name as branch_name_to', 'B2.branch_code as branch_code_to')
                ->leftJoin('gnl_branchs as B1', 'B1.id', '=', 'inv_issues_m.branch_from')
                ->leftJoin('gnl_branchs as B2', 'B2.id', '=', 'inv_issues_m.branch_to')
                ->where(function ($QuerryData) use ($search, $SDate, $EDate, $branchID, $Type) {

                    if (!empty($search)) {
                        $QuerryData->where('B1.branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('B2.branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('inv_issues_m.bill_no', 'LIKE', "%{$search}%")
                            ->orWhere('inv_issues_m.issue_date', 'LIKE', "%{$search}%");
                    }
                    if (!empty($Type) && !empty($branchID)) {
                        if ($Type == 1) {
                            $QuerryData->where('inv_issues_m.branch_from', '=', $branchID);
                        }
                        if ($Type == 2) {
                            $QuerryData->where('inv_issues_m.branch_to', '=', $branchID);
                        }

                    } else if (!empty($branchID)) {
                        $QuerryData->where('inv_issues_m.branch_from', '=', $branchID)
                            ->orWhere('inv_issues_m.branch_to', '=', $branchID);

                    }

                    if (!empty($SDate) && !empty($EDate)) {

                        $SDate = new DateTime($SDate);
                        $SDate = $SDate->format('Y-m-d');

                        $EDate = new DateTime($EDate);
                        $EDate = $EDate->format('Y-m-d');

                        $QuerryData->whereBetween('inv_issues_m.issue_date', [$SDate, $EDate]);
                    }
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('inv_issues_m.issue_date', 'DESC')
                ->orderBy('inv_issues_m.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();
            $totalFiltered = count($QuerryData);

            $billNoList = $QuerryData->pluck('bill_no');
            $detailsData = DB::table('inv_issues_d as dt')
                ->whereIn('dt.issue_bill_no', $billNoList->toarray())
                ->join('inv_products as pro', function ($detailsData) {
                    $detailsData->on('pro.id', '=', 'dt.product_id')
                                ->where('pro.is_delete',0);
                })
                ->select('dt.issue_bill_no', 'pro.product_name')
                ->get();

            // $search, $SDate, $EDate,$branchID, $Type
            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($branchID) || !empty($Type)) {
                $totalFiltered = count($QuerryData);
            }

            $DataSet = array();
            $i = 0;
            foreach ($QuerryData as $Row) {

                $date = new DateTime($Row->issue_date);
                $date = $date->format('d-m-Y');
                $TempSet = array();

                $IgnoreArray = array();

                if ($date != Common::systemCurrentDate($Row->branch_from, 'inv')) {
                    $IgnoreArray = ['delete', 'edit'];
                }

                $product_names = $detailsData->where('issue_bill_no', $Row->bill_no)
                    ->pluck('product_name')
                    ->toArray();

                if(count($product_names) > 0){

                    $TempSet = [
                        'id' => ++$i,
                        'bill_no' => $Row->bill_no,
                        'issue_date' => $date,
                        'product_name'=> implode(', ', $product_names),
                        'total_quantity' => $Row->total_quantity,
                        'total_amount' => $Row->total_amount,
                        'branch_from' => (!empty($Row->branch_name_from)) ? $Row->branch_name_from."(".$Row->branch_code_from.")" : "",
                        'branch_to' => (!empty($Row->branch_name_to)) ? $Row->branch_name_to."(".$Row->branch_code_to.")" : "",

                        'action' => Role::roleWiseArray($this->GlobalRole, $Row->id, $IgnoreArray),
                    ];

                    $DataSet[] = $TempSet;
                }
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $DataSet,
            );

            echo json_encode($json_data);

        } else {
            return view('INV.Issue.index');
        }
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            // dd( $request);

            $validateData = $request->validate([
                'bill_no' => 'required',
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();

            $issue_date = new DateTime($RequestData['issue_date']);
            $RequestData['issue_date'] = $issue_date->format('Y-m-d');
            //dd($RequestData);

            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            // $unit_cost_price_arr = (isset($RequestData['unit_cost_price_arr']) ? $RequestData['unit_cost_price_arr'] : array());
            // $total_cost_price_arr = (isset($RequestData['total_cost_price_arr']) ? $RequestData['total_cost_price_arr'] : array());

            DB::beginTransaction();

            try {

                if (IssueMaster::where('bill_no', '=', $RequestData['bill_no'])->exists()) {
                    $RequestData['bill_no'] = INVS::generateBillIssue($RequestData['branch_from']);
                }

                $isInsert = IssueMaster::create($RequestData);

                if ($isInsert) {
                    /* Child Table Insertion */
                    // $RequestData['issue_id'] = $isInsert->id;
                    $RequestData['issue_bill_no'] = $RequestData['bill_no'];
                    $RequestData['company_id'] = $RequestData['company_id'];
                    $RequestData['branch_from'] = $RequestData['branch_from'];
                    $RequestData['branch_to'] = $RequestData['branch_to'];

                    $issue_date = new DateTime($RequestData['issue_date']);
                    $RequestData['issue_date'] = $issue_date->format('Y-m-d');

                    foreach ($product_id_arr as $key => $product_id_sin) {
                        if (!empty($product_id_sin)) {
                            $RequestData['product_id'] = $product_id_sin;
                            $RequestData['product_quantity'] = $product_quantity_arr[$key];
                            // $RequestData['unit_cost_price'] = $unit_cost_price_arr[$key];
                            // $RequestData['total_cost_amount'] = $total_cost_price_arr[$key];

                            $isInsertDetails = IssueDetails::create($RequestData);
                        }
                    }
                }

                $queryData = DB::table('inv_requisitions_m as prm')
                    ->where('prm.requisition_no', $RequestData['requisition_no'])
                    ->select('prd.product_id', 'prd.product_quantity')
                    ->leftjoin('inv_requisitions_d as prd', function ($queryData) {
                        $queryData->on('prm.requisition_no', '=', 'prd.requisition_no');
                    })
                    ->addSelect(['remaining_qtn' => DB::table('inv_issues_m as pim')
                            ->select(DB::raw('(prd.product_quantity - IFNULL(SUM(pid.product_quantity), 0))'))
                            ->leftjoin('inv_issues_d as pid', function ($queryData) {
                                $queryData->on('pid.issue_bill_no', '=', 'pim.bill_no');
                            })
                            ->whereColumn([['prm.requisition_no', 'pim.requisition_no'], ['pid.product_id', 'prd.product_id']])
                            ->where([['pim.is_delete', 0], ['pim.is_active', 1]])
                            ->limit(1),
                    ])
                    ->get();

                $flug = false;
                foreach ($queryData as $row) {
                    if ($row->remaining_qtn == 0) {
                        $flug = true;
                    }
                }

                if ($flug == true) {
                    RequisitionMaster::where('requisition_no', $RequestData['requisition_no'])->update(['is_complete' => 1]);
                }

                // Your Code here
                DB::commit();
                // return
                $notification = array(
                    'message' => 'Successfully inserted Issue List',
                    'alert-type' => 'success',
                );

                return Redirect::to('inv/issue')->with($notification);
            } catch (Exception $e) {
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to inserted Issue List',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
                //return $e;
            }

        } else {
            # code...
            $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
                ->orderBy('branch_code', 'ASC')
                ->get();
            return view('INV.Issue.add', compact('BranchData'));
        }
    }

    public function edit(Request $request, $id = null)
    {
        $Issuem = IssueMaster::where('id', $id)->first();
        $Issued = IssueDetails::where('issue_bill_no', $Issuem->bill_no)->get();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'bill_no' => 'required',
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();
            $issue_date = new DateTime($RequestData['issue_date']);
            $RequestData['issue_date'] = $issue_date->format('Y-m-d');
            //dd($request);

            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            // $unit_cost_price_arr = (isset($RequestData['unit_cost_price_arr']) ? $RequestData['unit_cost_price_arr'] : array());
            // $total_cost_price_arr = (isset($RequestData['total_cost_price_arr']) ? $RequestData['total_cost_price_arr'] : array());

            DB::beginTransaction();

            try {
                $isUpdateMaster = $Issuem->update($RequestData);

                if ($isUpdateMaster) {

                    IssueDetails::where('issue_bill_no', $Issuem->bill_no)->get()->each->delete();

                    /* Child Table Insertion */
                    // $RequestData['issue_id'] = $id;
                    $RequestData['issue_bill_no'] = $RequestData['bill_no'];
                    // $RequestData['company_id'] = $RequestData['company_id'];
                    // $RequestData['branch_from'] = $RequestData['branch_from'];
                    $RequestData['branch_to'] = $Issuem->branch_to;

                    $issue_date = new DateTime($RequestData['issue_date']);
                    $RequestData['issue_date'] = $issue_date->format('Y-m-d');

                    foreach ($product_id_arr as $key => $product_id_sin) {
                        if (!empty($product_id_sin)) {
                            $RequestData['product_id'] = $product_id_sin;
                            $RequestData['product_quantity'] = $product_quantity_arr[$key];
                            // $RequestData['unit_cost_price'] = $unit_cost_price_arr[$key];
                            // $RequestData['total_cost_amount'] = $total_cost_price_arr[$key];

                            $isInsertDetails = IssueDetails::create($RequestData);

                        }

                    }

                }
                // Your Code here
                DB::commit();
                $notification = array(
                    'message' => 'Successfully Updated Issue List',
                    'alert-type' => 'success',
                );

                return Redirect::to('inv/issue')->with($notification);
                // return
            } catch (Exception $e) {
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to Update Issue List',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
            }

        } else {

            $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
                ->orderBy('branch_code', 'ASC')
                ->get();
            return view('INV.Issue.edit', compact('Issuem', 'Issued', 'BranchData'));
        }
    }

    public function view($id = null)
    {
        $Issuem = IssueMaster::where('id', $id)->first();
        $Issued = IssueDetails::where('issue_bill_no', $Issuem->bill_no)->get();
        $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
            ->orderBy('branch_code', 'ASC')
            ->get();
        return view('INV.Issue.view', compact('Issuem', 'Issued', 'BranchData'));
    }

    public function delete($id = null)
    {

        $IssueDataM = IssueMaster::where('id', $id)->first();

        $IssueDataM->is_delete = 1;

        $delete = $IssueDataM->save();

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

    public function isActive($id = null)
    {
        $IssueDataM = IssueMaster::where('id', $id)->first();

        if ($IssueDataM->is_active == 1) {

            $IssueDataM->is_active = 0;
            # code...
        } else {

            $IssueDataM->is_active = 1;
        }

        $Status = $IssueDataM->save();

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

}
