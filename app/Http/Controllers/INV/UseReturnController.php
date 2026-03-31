<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;

use App\Model\INV\UseReturnDetails;
use App\Model\INV\UseReturnMaster;
use App\Model\INV\UsesDetails;
use App\Model\INV\UsesMaster;
use App\Services\CommonService as Common;
use App\Services\RoleService as Role;
use App\Services\HrService as HRS;
use App\Services\InvService as INVS;

use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;


class UseReturnController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    // public function index(Request $request)
    // {
    //     if ($request->ajax()) {

    //     } else {
    //         $UseRData = UseReturnMaster::where('is_delete', 0)
    //             ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
    //             ->orderBy('return_date','DESC')
    //             ->orderBy('id', 'DESC')
    //             ->get();
    //         return view('INV.UseReturn.index', compact('UseRData'));
    //     }
    // }

    public function index(Request $request)
    {
      if ($request->ajax()) {

          // Ordering Variable
          $columns = array(
              0 => 'inv_use_return_m.id',
              1 => 'inv_use_return_m.return_date',
              2 => 'inv_use_return_m.return_bill_no',
              3 => 'inv_use_return_m.uses_bill_no',
              4 => 'inv_use_return_m.total_return_quantity',
              5 => 'inv_products.product_name',
          );

          // Datatable Pagination Variable
          $limit = $request->input('length');
          $start = $request->input('start');
          $order = $columns[$request->input('order.0.column')];
          $dir = $request->input('order.0.dir');

          // Searching variable
          $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
          $useBillNo = (empty($request->input('useBillNo'))) ? null : $request->input('useBillNo');
          $sDate = (empty($request->input('sDate'))) ? null : $request->input('sDate');
          $eDate = (empty($request->input('eDate'))) ? null : $request->input('eDate');
          // $PGroupID = (empty($request->input('PGroupID'))) ? null : $request->input('PGroupID');
          // $CategoryId = (empty($request->input('CategoryId'))) ? null : $request->input('CategoryId');
          // $SubCatID = (empty($request->input('SubCatID'))) ? null : $request->input('SubCatID');
          // $BrandID = (empty($request->input('BrandID'))) ? null : $request->input('BrandID');

          // Query
          $useRData = UseReturnMaster::where([['inv_use_return_m.is_delete', 0], ['gnl_branchs.is_approve', 1]])
              ->select('inv_use_return_m.*',
                  'gnl_branchs.branch_name as branch_name', 'gnl_branchs.branch_code as branch_code')
              ->whereIn('inv_use_return_m.branch_id', HRS::getUserAccesableBranchIds())
              ->leftJoin('gnl_branchs', 'inv_use_return_m.branch_id', '=', 'gnl_branchs.id')
              ->where(function ($useRData) use ($search, $useBillNo, $sDate, $eDate) {

                  if (!empty($search)) {
                      $useRData->where('inv_use_return_m.uses_bill_no', 'LIKE', "%{$search}%")
                          ->orWhere('gnl_branchs.branch_name', 'LIKE', "%{$search}%");
                  }
                  if (!empty($useBillNo)) {
                      $useRData->where('inv_use_return_m.uses_bill_no', 'LIKE', $useBillNo);
                  }

                  if (!empty($sDate) && !empty($eDate)) {

                      $sDate = new DateTime($sDate);
                      $sDate = $sDate->format('Y-m-d');

                      $eDate = new DateTime($eDate);
                      $eDate = $eDate->format('Y-m-d');

                      $useRData->whereBetween('inv_use_return_m.return_date', [$sDate, $eDate]);
                  }
              })
              ->offset($start)
              ->limit($limit)
              ->orderBy($order, $dir)
              ->orderBy('inv_use_return_m.return_date', 'DESC')
              ->get();


          $billNoList = $useRData->pluck('return_bill_no');
          $detailsRData = DB::table('inv_use_return_d as srt')
              ->whereIn('srt.return_bill_no', $billNoList->toarray())
              ->join('inv_products as pro', function ($detailsRData) {
                  $detailsRData->on('pro.id', '=', 'srt.product_id')
                              ->where('pro.is_delete', 0);
              })
              ->select('srt.return_bill_no', 'pro.product_name','pro.product_code')
              ->get();
              // dd($detailsRData);
          $totalData = UseReturnMaster::where([['is_delete', 0],['is_active', 1]])
              ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
              ->count();

          $totalFiltered = $totalData;

          if (!empty($search) || !empty($useBillNo || !empty($sDate) || !empty($eDate))) {
              $totalFiltered = count($useRData);
          }
          //
          // //get uses_bill_no for chack sales
          // $useRData = UseReturnMaster::where([['is_active', 1], ['is_delete', 0]])
          //     ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
          //     ->pluck('uses_bill_no')
          //     ->toArray();

          $dataSet = array();
          $i = 0;

          foreach ($useRData as $row) {

              $TempSet = array();
              $IgnoreArray = array();

              if ((new dateTime($row->return_date))->format('d-m-Y') != Common::systemCurrentDate()) {
                  $IgnoreArray = ['delete', 'edit'];

              }

              $product_names = $detailsRData->where('return_bill_no', $row->return_bill_no)
                  // ->select(DB::raw("CONCAT(product_name,' ',prod_barcode) AS product_name"))
                  // ->pluck('product_name','id')
                  ->pluck('product_name')
                  ->toArray();
                  // dd($product_names);
              if(count($product_names) > 0){
                  $TempSet = [
                      'id' => ++$i,
                      'return_date' => (new dateTime($row->return_date))->format('d-m-Y'),
                      'return_bill_no' => $row->return_bill_no,
                      'uses_bill_no' => $row->uses_bill_no,
                      'total_return_quantity' => $row->total_return_quantity,
                      'product_name'=> implode(', ', $product_names),
                      'branch_name' => (!empty($row->branch_name)) ? $row->branch_name. " (".$row->branch_code.")" : "",

                      'action' => Role::roleWiseArray($this->GlobalRole, $row->return_bill_no, $IgnoreArray),
                  ];

                  $dataSet[] = $TempSet;
              }

              else{
                  --$totalData;
                  --$totalFiltered;
              }

          }

          $json_data = array(
              "draw" => intval($request->input('draw')),
              "recordsTotal" => intval($totalData),
              "recordsFiltered" => intval($totalFiltered),
              "data" => $dataSet,
          );

          echo json_encode($json_data);

      } else {

            // $SaleRM = UseReturnMaster::where('is_delete', 0)
            //     ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
            //     ->orderBy('return_date','DESC')
            //     ->orderBy('id', 'DESC')
            //     ->get();
            return view('INV.UseReturn.index');
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
            $pro_barcode_arr = (isset($RequestData['pro_barcode_arr']) ? $RequestData['pro_barcode_arr'] : array());
            $sys_barcode_arr = (isset($RequestData['sys_barcode_arr']) ? $RequestData['sys_barcode_arr'] : array());
            $prod_cost_arr = (isset($RequestData['prod_cost_arr']) ? $RequestData['prod_cost_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());


            DB::beginTransaction();

            try {

                if (UseReturnMaster::where('return_bill_no', '=', $RequestData['return_bill_no'])->exists()) 
                {
                   $RequestData['return_bill_no'] = INVS::generateBillSalesReturn($RequestData['branch_id']);
                }

                $isInsert = UseReturnMaster::create($RequestData);

                /* Child Table Insertion */
                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {

                        $RequestData['product_id'] = $product_id_sin;
                        $RequestData['product_quantity'] = $product_quantity_arr[$key];
                        $RequestData['product_barcode'] = $pro_barcode_arr[$key];

                        UseReturnDetails::create($RequestData);
                    }
                }

                DB::commit();

                $notification = array(
                    'message' => 'Successfully Inserted Data',
                    'alert-type' => 'success',
                );
                return redirect('inv/use_return')->with($notification);
            } catch (\Exception $e) {
                DB::rollBack();

                $notification = array(
                    'message' => 'Unsuccessful to insert data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } 
        else {
            $UseMData = UsesMaster::where('is_delete', 0)
                                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                                ->orderBy('id', 'DESC')
                                ->get();
            return view('INV.UseReturn.add', compact('UseMData'));
        }
    }

    public function edit(Request $request, $id = null)
    {
        $SaleRM = UseReturnMaster::where('return_bill_no', $id)->first();
        $SaleRD = UseReturnDetails::where('return_bill_no', $id)->get();

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
            $pro_barcode_arr = (isset($RequestData['pro_barcode_arr']) ? $RequestData['pro_barcode_arr'] : array());
            $sys_barcode_arr = (isset($RequestData['sys_barcode_arr']) ? $RequestData['sys_barcode_arr'] : array());
            $prod_cost_arr = (isset($RequestData['prod_cost_arr']) ? $RequestData['prod_cost_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());

            $unit_cost_price_arr = (isset($RequestData['unit_cost_price_arr']) ? $RequestData['unit_cost_price_arr'] : array());
            $total_cost_price_arr = (isset($RequestData['total_cost_price_arr']) ? $RequestData['total_cost_price_arr'] : array());

            DB::beginTransaction();

            try {
                $isUpdateMaster = $SaleRM->update($RequestData);

                UseReturnDetails::where('return_bill_no', $id)->get()->each->delete();

                /* Child Table Insertion */
                foreach ($product_id_arr as $key => $product_id_sin) {

                    if (!empty($product_id_sin)) {

                        $RequestData['product_id'] = $product_id_sin;
                        $RequestData['product_quantity'] = $product_quantity_arr[$key];
                        // $RequestData['product_cost_price'] = $prod_cost_arr[$key];
                        // $RequestData['product_sales_price'] = $unit_cost_price_arr[$key];
                        // $RequestData['total_amount'] = $total_cost_price_arr[$key];
                        $RequestData['product_barcode'] = $pro_barcode_arr[$key];
                        // $RequestData['product_system_barcode'] = $sys_barcode_arr[$key];

                        UseReturnDetails::create($RequestData);
                    }
                }

                DB::commit();
                $notification = array(
                    'message' => 'Successfully Updated',
                    'alert-type' => 'success',
                );

                return redirect('inv/use_return')->with($notification);

            } catch (\Exception $e) {

                DB::rollBack();

                $notification = array(
                    'message' => 'Unsuccessful to Update data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

        } else {
            return view('INV.UseReturn.edit', compact('SaleRM', 'SaleRD'));
        }
    }
    public function view($id = null)
    {
        $useRM = UseReturnMaster::where('return_bill_no', $id)->first();
        $useRD = UseReturnDetails::where('return_bill_no', $id)->get();
        return view('INV.UseReturn.view', compact('useRM', 'useRD'));
    }

    public function delete($id = null)
    {

        $SaleRM = UseReturnMaster::where('return_bill_no', $id)->first();

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
        $SaleRM = UseReturnMaster::where('return_bill_no', $id)->first();

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
