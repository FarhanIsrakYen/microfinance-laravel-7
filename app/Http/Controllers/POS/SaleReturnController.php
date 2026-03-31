<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\POS\SaleReturnd;
use App\Model\POS\SaleReturnm;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\PosService as POSS;
use App\Services\RoleService as Role;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;

class SaleReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {

            // Ordering Variable
            $columns = array(
                0 => 'pos_sales_return_m.id',
                1 => 'pos_sales_return_m.return_date',
                2 => 'pos_sales_return_m.return_bill_no',
                3 => 'pos_sales_return_m.sales_bill_no',
                4 => 'pos_sales_return_m.total_return_quantity',
                5 => 'pos_sales_return_m.total_return_amount',
            );

            // Datatable Pagination Variable
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $branchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');
            $salesBillNo = (empty($request->input('salesBillNo'))) ? null : $request->input('salesBillNo');
            $sDate = (empty($request->input('sDate'))) ? null : $request->input('sDate');
            $eDate = (empty($request->input('eDate'))) ? null : $request->input('eDate');
            // $PGroupID = (empty($request->input('PGroupID'))) ? null : $request->input('PGroupID');
            // $CategoryId = (empty($request->input('CategoryId'))) ? null : $request->input('CategoryId');
            // $SubCatID = (empty($request->input('SubCatID'))) ? null : $request->input('SubCatID');
            // $BrandID = (empty($request->input('BrandID'))) ? null : $request->input('BrandID');

            // Query
            $salesRData = SaleReturnm::where([['pos_sales_return_m.is_delete', 0], ['gnl_branchs.is_approve', 1]])
                ->select('pos_sales_return_m.*',
                    'gnl_branchs.branch_name as branch_name', 'gnl_branchs.branch_code as branch_code')
                ->whereIn('pos_sales_return_m.branch_id', HRS::getUserAccesableBranchIds())
                ->leftJoin('gnl_branchs', 'pos_sales_return_m.branch_id', '=', 'gnl_branchs.id')
                ->where(function ($salesRData) use ($search, $branchID, $salesBillNo, $sDate, $eDate) {

                    if (!empty($search)) {
                        $salesRData->where('pos_sales_return_m.sales_bill_no', 'LIKE', "%{$search}%")
                        ->orWhere('pos_sales_return_m.return_bill_no', 'LIKE', "%{$search}%")
                        ->orWhere('pos_sales_return_m.total_return_quantity', 'LIKE', "%{$search}%")
                        ->orWhere('pos_sales_return_m.total_return_amount', 'LIKE', "%{$search}%")
                        ->orWhere('gnl_branchs.branch_name', 'LIKE', "%{$search}%");
                    }

                    if (!empty($branchID)) {
                        $salesRData->where('pos_sales_return_m.branch_id', '=', $branchID);
                    }

                    if (!empty($salesBillNo)) {
                        $salesRData->where('pos_sales_return_m.sales_bill_no', 'LIKE', $salesBillNo);
                    }

                    if (!empty($sDate) && !empty($eDate)) {

                        $sDate = new DateTime($sDate);
                        $sDate = $sDate->format('Y-m-d');

                        $eDate = new DateTime($eDate);
                        $eDate = $eDate->format('Y-m-d');

                        $salesRData->whereBetween('pos_sales_return_m.return_date', [$sDate, $eDate]);
                    }
                })
            // ->offset($start)
            // ->limit($limit)
                ->orderBy($order, $dir)
                ->orderBy('pos_sales_return_m.return_date', 'DESC');
            // ->get();

            $tempQueryData = clone $salesRData;
            $salesRData = $salesRData->offset($start)->limit($limit)->get();

            $billNoList = $salesRData->pluck('return_bill_no');
            $detailsRData = DB::table('pos_sales_return_d as srt')
                ->whereIn('srt.return_bill_no', $billNoList->toarray())
                ->join('pos_products as pro', function ($detailsRData) {
                    $detailsRData->on('pro.id', '=', 'srt.product_id')
                        ->where('pro.is_delete', 0);
                })
                ->select('srt.return_bill_no', 'pro.product_name', 'pro.prod_barcode')
                ->get();
            // dd($detailsRData);
            $totalData = SaleReturnm::where([['is_delete', 0], ['is_active', 1]])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;

            if (!empty($search) || !empty($branchID) || !empty($salesBillNo || !empty($sDate) || !empty($eDate))) {
                $totalFiltered = $tempQueryData->count();
            }

            $dataSet = array();
            $i = $start;

            foreach ($salesRData as $row) {

                $TempSet = array();
                $IgnoreArray = array();

                if (date('d-m-Y', strtotime($row->return_date)) != Common::systemCurrentDate($row->branch_id, 'pos')) {
                    $IgnoreArray = ['delete', 'edit'];

                }

                $product_names = $detailsRData->where('return_bill_no', $row->return_bill_no)
                // ->select(DB::raw("CONCAT(product_name,' ',prod_barcode) AS product_name"))
                // ->pluck('product_name','id')
                    ->pluck('product_name')
                    ->toArray();
                // dd($product_names);
                // if(count($product_names) > 0){
                $TempSet = [
                    'id' => ++$i,
                    'return_date' => date('d-m-Y', strtotime($row->return_date)),
                    'return_bill_no' => $row->return_bill_no,
                    'sales_bill_no' => $row->sales_bill_no,
                    'total_return_quantity' => $row->total_return_quantity,
                    'total_return_amount' => $row->total_return_amount,
                    'product_name' => implode(', ', $product_names),
                    'branch_name' => (!empty($row->branch_name)) ? $row->branch_name . " (" . $row->branch_code . ")" : "",

                    'action' => Role::roleWiseArray($this->GlobalRole, $row->return_bill_no, $IgnoreArray),
                ];

                $dataSet[] = $TempSet;
                // }

                // else{
                //     --$totalData;
                //     --$totalFiltered;
                // }

            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $dataSet,
            );

            echo json_encode($json_data);

        } else {

            // $SaleRM = SaleReturnm::where('is_delete', 0)
            //     ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
            //     ->orderBy('return_date','DESC')
            //     ->orderBy('id', 'DESC')
            //     ->get();
            return view('POS.SaleReturn.index');
        }
    }
    public function add(Request $request)
    {
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'return_bill_no' => 'required',
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();
            $return_date = new DateTime($RequestData['return_date']);
            $RequestData['return_date'] = $return_date->format('Y-m-d');

            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $pro_barcode_arr = (isset($RequestData['product_barcode_arr']) ? $RequestData['product_barcode_arr'] : array());
            $sys_barcode_arr = (isset($RequestData['product_system_barcode_arr']) ? $RequestData['product_system_barcode_arr'] : array());

            $prod_cost_arr = (isset($RequestData['product_cost_price_arr']) ? $RequestData['product_cost_price_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());

            $product_sales_price_arr = (isset($RequestData['product_sales_price_arr']) ? $RequestData['product_sales_price_arr'] : array());
            $total_sales_price_arr = (isset($RequestData['total_sales_price_arr']) ? $RequestData['total_sales_price_arr'] : array());

            if (count(array_filter($product_id_arr)) <= 0) {
                $notification = array(
                    'message' => 'Something went wrong! Please try again.',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

            DB::beginTransaction();

            try {

                if (SaleReturnm::where('return_bill_no', '=', $RequestData['return_bill_no'])->exists()) {
                    $RequestData['return_bill_no'] = POSS::generateBillSalesReturn($RequestData['branch_id']);
                }

                $isInsert = SaleReturnm::create($RequestData);

                /* Child Table Insertion */
                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {

                        $RequestData['product_id'] = $product_id_sin;
                        $RequestData['product_quantity'] = $product_quantity_arr[$key];
                        $RequestData['product_cost_price'] = $prod_cost_arr[$key];
                        $RequestData['product_sales_price'] = $product_sales_price_arr[$key];
                        $RequestData['total_amount'] = $total_sales_price_arr[$key];
                        $RequestData['product_barcode'] = $pro_barcode_arr[$key];
                        $RequestData['product_system_barcode'] = $sys_barcode_arr[$key];

                        SaleReturnd::create($RequestData);
                    }
                }

                DB::commit();

                $notification = array(
                    'message' => 'Successfully Inserted Data',
                    'alert-type' => 'success',
                );
                return redirect('pos/sales_return')->with($notification);
            } catch (\Exception $e) {
                DB::rollBack();

                $notification = array(
                    'message' => 'Unsuccessful to insert data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            $SaleM = DB::table('pos_sales_m as psm')
                ->where([['psm.is_delete', 0], ['psm.sales_type', 1]])
                ->whereIn('psm.branch_id', HRS::getUserAccesableBranchIds())
            // ->leftjoin('pos_sales_return_m as psrm', function ($SaleM) {
            //     $SaleM->on('psrm.sales_bill_no', '=', 'psm.sales_bill_no')
            //         ->where('psrm.is_delete', 0);
            // })
            // ->leftjoin('pos_sales_return_d as psrd', function ($SaleM) {
            //     $SaleM->on('psrd.return_bill_no', '=', 'psrm.return_bill_no');
            // })
            // ->select('psm.sales_bill_no', 'psm.branch_id', 'psrm.return_bill_no', 'psrd.product_id', 'psrd.product_quantity')
                ->orderBy('psm.sales_bill_no', 'DESC')
                ->get();

            // dd($SaleM->toarray());

            return view('POS.SaleReturn.add', compact('SaleM'));
        }
    }

    public function edit(Request $request, $id = null)
    {

        $SaleRM = SaleReturnm::where('return_bill_no', $id)->first();
        // dd($SaleRM);
        $SaleRD = SaleReturnd::where('return_bill_no', $id)->get();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'return_bill_no' => 'required',
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();
            //dd($request);
            $return_date = new DateTime($RequestData['return_date']);
            $RequestData['return_date'] = $return_date->format('Y-m-d');

            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $pro_barcode_arr = (isset($RequestData['product_barcode_arr']) ? $RequestData['product_barcode_arr'] : array());
            $sys_barcode_arr = (isset($RequestData['product_system_barcode_arr']) ? $RequestData['product_system_barcode_arr'] : array());

            $prod_cost_arr = (isset($RequestData['product_cost_price_arr']) ? $RequestData['product_cost_price_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());

            $product_sales_price_arr = (isset($RequestData['product_sales_price_arr']) ? $RequestData['product_sales_price_arr'] : array());
            $total_sales_price_arr = (isset($RequestData['total_sales_price_arr']) ? $RequestData['total_sales_price_arr'] : array());

            if (count(array_filter($product_id_arr)) <= 0) {
                $notification = array(
                    'message' => 'Something went wrong! Please try again.',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

            DB::beginTransaction();

            try {
                $isUpdateMaster = $SaleRM->update($RequestData);

                SaleReturnd::where('return_bill_no', $id)->get()->each->delete();

                /* Child Table Insertion */
                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {

                        $RequestData['product_id'] = $product_id_sin;
                        $RequestData['product_quantity'] = $product_quantity_arr[$key];
                        $RequestData['product_cost_price'] = $prod_cost_arr[$key];
                        $RequestData['product_sales_price'] = $product_sales_price_arr[$key];
                        $RequestData['total_amount'] = $total_sales_price_arr[$key];
                        $RequestData['product_barcode'] = $pro_barcode_arr[$key];
                        $RequestData['product_system_barcode'] = $sys_barcode_arr[$key];

                        SaleReturnd::create($RequestData);
                    }
                }

                DB::commit();
                $notification = array(
                    'message' => 'Successfully Updated',
                    'alert-type' => 'success',
                );

                return redirect('pos/sales_return')->with($notification);

            } catch (\Exception $e) {

                DB::rollBack();

                $notification = array(
                    'message' => 'Unsuccessful to Update data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

        } else {
            return view('POS.SaleReturn.edit', compact('SaleRM', 'SaleRD'));
        }
    }
    public function view($id = null)
    {
        $SaleRM = SaleReturnm::where('return_bill_no', $id)->first();
        $SaleRD = SaleReturnd::where('return_bill_no', $id)->get();
        return view('POS.SaleReturn.view', compact('SaleRM', 'SaleRD'));
    }

    public function delete($id = null)
    {

        $SaleRM = SaleReturnm::where('sales_bill_no', $id)->first();

        $SaleRM->is_delete = 1;

        $delete = $SaleRM->save();

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
        $SaleRM = SaleReturnm::where('return_bill_no', $id)->first();

        if ($SaleRM->is_active == 1) {

            $SaleRM->is_active = 0;
        } else {

            $SaleRM->is_active = 1;
        }

        $Status = $SaleRM->save();

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
