<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\POS\IssueReturnd;
use App\Model\POS\IssueReturnm;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\PosService as POSS;
use App\Services\RoleService as Role;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;

class IssueReturnController extends Controller
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
                0 => 'pos_issues_r_m.id',
                1 => 'pos_issues_r_m.return_date',
                2 => 'pos_issues_r_m.bill_no',
                4 => 'pos_issues_r_m.total_quantity',
                5 => 'pos_issues_r_m.total_amount',
            );

            // Datatable Pagination Variable

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable

            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $branchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');
            // $ProductID = (empty($request->input('ProductID'))) ? null : $request->input('ProductID');
            $Type = (empty($request->input('Type'))) ? null : $request->input('Type');
            $sDate = (empty($request->input('sDate'))) ? null : $request->input('sDate');
            $eDate = (empty($request->input('eDate'))) ? null : $request->input('eDate');

            // Query
            $QuerryData = IssueReturnm::where('pos_issues_r_m.is_delete', '=', 0)
                ->whereIn('pos_issues_r_m.branch_from', HRS::getUserAccesableBranchIds())
                ->select('pos_issues_r_m.*', 'B1.branch_name as branch_name_from', 'B1.branch_code')
            // 'B2.branch_name as branch_name_to')
                ->leftJoin('gnl_branchs as B1', 'B1.id', '=', 'pos_issues_r_m.branch_from')
            // ->leftJoin('gnl_branchs as B2', 'B2.id', '=', 'pos_issues_r_m.branch_to')
            // ->leftjoin('pos_issues_r_d as pird', function ($QuerryData) {
            //     $QuerryData->on('pird.ir_bill_no', '=', 'pos_issues_r_m.bill_no');
            // })
                ->where(function ($QuerryData) use ($search, $branchID,  $Type, $sDate, $eDate) {

                    if (!empty($search)) {
                        $QuerryData->where('B1.branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('pos_issues_r_m.total_quantity', 'LIKE', "%{$search}%")
                            ->orWhere('pos_issues_r_m.total_amount', 'LIKE', "%{$search}%")
                            ->orWhere('pos_issues_r_m.bill_no', 'LIKE', "%{$search}%")
                            ->orWhere('pos_issues_r_m.return_date', 'LIKE', "%{$search}%");
                    }
                    if (!empty($sDate) && !empty($eDate)) {

                        $sDate = new DateTime($sDate);
                        $sDate = $sDate->format('Y-m-d');

                        $eDate = new DateTime($eDate);
                        $eDate = $eDate->format('Y-m-d');

                        $QuerryData->whereBetween('pos_issues_r_m.return_date', [$sDate, $eDate]);
                    }
                    
                    if (!empty($Type) && !empty($branchID)) {
                        if ($Type == 1) {
                            $QuerryData->where('pos_issues_r_m.branch_from', '=', $branchID);
                        }
                        if ($Type == 2) {
                            $QuerryData->where('pos_issues_r_m.branch_to', '=', $branchID);
                        }

                    } else if (!empty($branchID)) {
                        $QuerryData->where('pos_issues_r_m.branch_from', '=', $branchID)
                            ->orWhere('pos_issues_r_m.branch_to', '=', $branchID);

                    }

                    // if (!empty($ProductID)) {
                    //     $QuerryData->where('pird.product_id', $ProductID);
                    // }

                })
            // ->offset($start)
            // ->limit($limit)
                ->orderBy($order, $dir)
                ->orderBy('pos_issues_r_m.return_date', 'DESC');
            // ->get();

            $tempQueryData = clone $QuerryData;
            $QuerryData = $QuerryData->offset($start)->limit($limit)->get();

            $totalData = IssueReturnm::where('is_delete', '=', 0)
                ->whereIn('branch_from', HRS::getUserAccesableBranchIds())
                ->count();
            $totalFiltered = $totalData;

            // $search, $sDate, $eDate,$branchID, $Type
            if (!empty($search) || !empty($branchID) ||  !empty($Type || !empty($sDate) || !empty($eDate) )) {
                $totalFiltered = $tempQueryData->count();
            }

            $billNoList = $QuerryData->pluck('bill_no');
            $detailsData = DB::table('pos_issues_r_d as dt')
                ->whereIn('dt.ir_bill_no', $billNoList->toarray())
                ->join('pos_products as pro', function ($detailsData) {
                    $detailsData->on('pro.id', '=', 'dt.product_id')
                        ->where('pro.is_delete', 0);
                })
                ->select('dt.ir_bill_no', 'pro.product_name')
                ->get();

            $DataSet = array();
            $i = $start;
            // dd($QuerryData->toArray());
            foreach ($QuerryData as $Row) {

                $date = new DateTime($Row->return_date);
                $date = $date->format('d-m-Y');
                $TempSet = array();

                $IgnoreArray = array();

                if ($date != Common::systemCurrentDate($Row->branch_from, 'pos')) {
                    $IgnoreArray = ['delete', 'edit'];
                }

                $product_names = $detailsData->where('ir_bill_no', $Row->bill_no)
                    ->pluck('product_name')
                    ->toArray();

                // if(count($product_names) > 0){
                $TempSet = [
                    'id' => ++$i,
                    'bill_no' => $Row->bill_no,
                    'issue_date' => $date,
                    'product_name' => implode(', ', $product_names),
                    'total_quantity' => $Row->total_quantity,
                    'total_amount' => $Row->total_amount,
                    'branch_from' => (!empty($Row->branch_name_from)) ? $Row->branch_name_from . "(" . $Row->branch_code . ")" : "",
                    // 'branch_to' => $Row->branch_name_to,

                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->bill_no, $IgnoreArray),
                ];

                $DataSet[] = $TempSet;
                // }
            }
            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $DataSet,
            );

            echo json_encode($json_data);
        } else {
            return view('POS.IssueReturn.index');
        }

    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'bill_no' => 'required',
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();

            $return_date = new DateTime($RequestData['return_date']);
            $RequestData['return_date'] = $return_date->format('Y-m-d');

            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $sys_barcode_arr = (isset($RequestData['sys_barcode_arr']) ? $RequestData['sys_barcode_arr'] : array());
            $product_bar_arr = (isset($RequestData['product_bar_arr']) ? $RequestData['product_bar_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            $unit_cost_price_arr = (isset($RequestData['unit_cost_price_arr']) ? $RequestData['unit_cost_price_arr'] : array());
            $total_cost_price_arr = (isset($RequestData['total_cost_price_arr']) ? $RequestData['total_cost_price_arr'] : array());

            if (count(array_filter($product_id_arr)) <= 0) {
                $notification = array(
                    'message' => 'Something went wrong! Please try again.',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

            DB::beginTransaction();

            try {

                if (IssueReturnm::where('bill_no', '=', $RequestData['bill_no'])->exists()) {
                    $RequestData['bill_no'] = POSS::generateBillIssueReturn($RequestData['branch_from']);
                }

                $isInsert = IssueReturnm::create($RequestData);

                if ($isInsert) {

                    /* Child Table Insertion */
                    // $RequestData['ir_id'] = $isInsert->id;
                    $RequestData['ir_bill_no'] = $RequestData['bill_no'];
                    // $RequestData['company_id'] = $RequestData['company_id'];
                    // $RequestData['branch_from'] = $RequestData['branch_from'];
                    // $RequestData['branch_to'] = $RequestData['branch_to'];

                    // $return_date = new DateTime($RequestData['return_date']);
                    // $RequestData['return_date'] = $return_date->format('Y-m-d');

                    foreach ($product_id_arr as $key => $product_id_sin) {

                        if (!empty($product_id_sin)) {

                            $RequestData['product_id'] = $product_id_sin;
                            $RequestData['product_quantity'] = $product_quantity_arr[$key];
                            $RequestData['unit_cost_price'] = $unit_cost_price_arr[$key];
                            $RequestData['total_cost_amount'] = $total_cost_price_arr[$key];
                            $RequestData['product_barcode'] = $product_bar_arr[$key];
                            $RequestData['product_system_barcode'] = $sys_barcode_arr[$key];

                            $details = IssueReturnd::create($RequestData);
                        }
                    }
                }
                // Your Code here
                DB::commit();
                $notification = array(
                    'message' => 'Successfully Insertd Issue return Data',
                    'alert-type' => 'success',
                );

                return Redirect::to('pos/issue_return')->with($notification);
                // return
            } catch (Exception $e) {

                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to insert Issue return',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
                //return $e;
            }

        } else {
            $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
                ->orderBy('branch_code', 'ASC')
                ->get();
            return view('POS.IssueReturn.add', compact('BranchData'));
        }
    }

    public function edit(Request $request, $id = null)
    {
        $IssueReturnm = IssueReturnm::where('bill_no', $id)->first();
        $IssueReturnd = IssueReturnd::where('ir_bill_no', $id)->get();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'bill_no' => 'required',
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();

            // dd($RequestData);

            $return_date = new DateTime($RequestData['return_date']);
            $RequestData['return_date'] = $return_date->format('Y-m-d');
            //dd($request);

            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $sys_barcode_arr = (isset($RequestData['sys_barcode_arr']) ? $RequestData['sys_barcode_arr'] : array());
            $product_bar_arr = (isset($RequestData['product_bar_arr']) ? $RequestData['product_bar_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            $unit_cost_price_arr = (isset($RequestData['unit_cost_price_arr']) ? $RequestData['unit_cost_price_arr'] : array());
            $total_cost_price_arr = (isset($RequestData['total_cost_price_arr']) ? $RequestData['total_cost_price_arr'] : array());

            if (count(array_filter($product_id_arr)) <= 0) {
                $notification = array(
                    'message' => 'Something went wrong! Please try again.',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

            DB::beginTransaction();

            try {
                // Your Code here
                $isUpdateMaster = $IssueReturnm->update($RequestData);

                if ($isUpdateMaster) {

                    IssueReturnd::where('ir_bill_no', $id)->get()->each->delete();

                    /* Child Table Insertion */
                    // $RequestData['ir_id'] = $id;
                    $RequestData['ir_bill_no'] = $RequestData['bill_no'];
                    // $RequestData['company_id'] = $RequestData['company_id'];
                    // $RequestData['branch_from'] = $RequestData['branch_from'];
                    // $RequestData['branch_to'] = $RequestData['branch_to'];

                    // $return_date = new DateTime($RequestData['return_date']);
                    // $RequestData['return_date'] = $return_date->format('Y-m-d');

                    foreach ($product_id_arr as $key => $product_id_sin) {

                        if (!empty($product_id_sin)) {

                            $RequestData['product_id'] = $product_id_sin;
                            $RequestData['product_quantity'] = $product_quantity_arr[$key];
                            $RequestData['unit_cost_price'] = $unit_cost_price_arr[$key];
                            $RequestData['total_cost_amount'] = $total_cost_price_arr[$key];
                            $RequestData['product_barcode'] = $product_bar_arr[$key];
                            $RequestData['product_system_barcode'] = $sys_barcode_arr[$key];

                            $details = IssueReturnd::create($RequestData);

                        }
                    }
                }

                DB::commit();

                $notification = array(
                    'message' => 'Successfully Updated Issue Return.',
                    'alert-type' => 'success',
                );

                return Redirect::to('pos/issue_return')->with($notification);
                // return
            } catch (Exception $e) {
                DB::rollBack();
                //return $e;
                $notification = array(
                    'message' => 'Unsuccessful to Updated Issue Return.',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
            }

        } else {
            $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
                ->orderBy('branch_code', 'ASC')
                ->get();

            return view('POS.IssueReturn.edit', compact('IssueReturnm', 'IssueReturnd', 'BranchData'));
        }

    }
    public function view($id = null)
    {
        $IssueReturnm = IssueReturnm::where('bill_no', $id)->first();
        $IssueReturnd = IssueReturnd::where('ir_bill_no', $id)->get();

        //$GData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('POS.IssueReturn.view', compact('IssueReturnm', 'IssueReturnd'));
    }

    public function delete($id = null)
    {

        $IssueDataM = IssueReturnm::where('bill_no', $id)->first();

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
        $IssueDataM = IssueReturnm::where('bill_no', $id)->first();

        if ($IssueDataM->is_active == 1) {

            $IssueDataM->is_active = 0;
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
