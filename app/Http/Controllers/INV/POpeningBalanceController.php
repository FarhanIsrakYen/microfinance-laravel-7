<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\INV\DayEnd;
use App\Model\INV\POBStockDetails;
use App\Model\INV\POBStockMaster;
use App\Model\INV\Product;
use App\Model\INV\UsesDetails;

use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;

use App\Services\RoleService as Role;
use App\Services\HrService as HRS;
use App\Services\CommonService as Common;
use App\Services\PosService as POSS;

class POpeningBalanceController extends Controller
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
                1 => 'branch_name',
                2 => 'opening_date',
                3 => 'total_product',
                4 => 'total_quantity',
                5 => 'total_amount',
            );

            // $totalData = POBStockMaster::where('is_delete', 0)
            //     ->whereIn('inv_ob_stock_m.branch_id', HRS::getUserAccesableBranchIds())->count();

            // $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
            $sl = $start + 1;

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $BranchID = (empty($request->input('BranchID'))) ? null : $request->input('BranchID');

            // Query
            $CDSaleData = POBStockMaster::where('inv_ob_stock_m.is_delete', '=', 0)
                ->whereIn('inv_ob_stock_m.branch_id', HRS::getUserAccesableBranchIds())
                ->select('inv_ob_stock_m.*', 'gnl_branchs.branch_name', 'gnl_branchs.branch_code')
                ->leftJoin('gnl_branchs', 'inv_ob_stock_m.branch_id', '=', 'gnl_branchs.id')
                ->where(function ($CDSaleData) use ($search, $BranchID) {
                    if (!empty($search)) {
                        $CDSaleData->where('gnl_branchs.branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('inv_ob_stock_m.opening_date', 'LIKE', "%{$search}%")
                            ->orWhere('inv_ob_stock_m.total_product', 'LIKE', "%{$search}%")
                            ->orWhere('inv_ob_stock_m.total_quantity', 'LIKE', "%{$search}%")
                            ->orWhere('inv_ob_stock_m.total_amount', 'LIKE', "%{$search}%");
                    }

                    if (!empty($BranchID)) {
                        $CDSaleData->where('inv_ob_stock_m.branch_id', '=', $BranchID);
                    }

                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('inv_ob_stock_m.opening_date', 'DESC')
                ->orderBy('inv_ob_stock_m.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            $totalData = count($CDSaleData);
            $totalFiltered = $totalData;

            if (!empty($search) || !empty($BranchID)) {
                $totalFiltered = count($CDSaleData);
            }

            config()->set('database.connections.mysql.strict', false);
            DB::reconnect();

            // Check Day End table
            $checkForEnD = DayEnd::where([['is_active', 0], ['is_delete', 0]])
                ->select('branch_id')
                ->orderBy('id', 'DESC')
                ->groupBy('branch_id')
                ->pluck('branch_id')
                ->toArray();

            $data = array();
            if (!empty($CDSaleData)) {
                // $i = 0;
                foreach ($CDSaleData as $Data) {

                    $IgnoreArray = array();
                    if (in_array($Data->branch_id, $checkForEnD)) {
                        $IgnoreArray = ['delete', 'edit'];
                    }

                    $nestedData['id'] = $sl++;
                    $nestedData['branch_name'] = (!empty($Data->branch_name)) ? $Data->branch_name."(".$Data->branch_code.")" : "";
                    $nestedData['opening_date'] = date('d-m-Y', strtotime($Data->opening_date));

                    $nestedData['total_product'] = $Data->total_product;
                    $nestedData['total_quantity'] = $Data->total_quantity;
                    $nestedData['total_amount'] = $Data->total_amount;
                    $nestedData['action'] = Role::roleWiseArray($this->GlobalRole, $Data->ob_no, $IgnoreArray);

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
            return view('INV.POBStock.index');
        }
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'branch_id' => 'required',
                'opening_date' => 'required',
            ]);

            $RequestData = $request->all();

            $date = new DateTime($RequestData['opening_date']); // used date time for d/m/y format
            $RequestData['opening_date'] = $date->format('Y-m-d');

            $RequestData['ob_no'] = POSS::generateBillPOBS($RequestData['branch_id']);

            $product_arr = (isset($RequestData['product_arr']) ? $RequestData['product_arr'] : array());
            $product_qnt = (isset($RequestData['product_qnt']) ? $RequestData['product_qnt'] : array());
            $unit_cost_price = (isset($RequestData['unit_cost_price']) ? $RequestData['unit_cost_price'] : array());
            $product_ttl = (isset($RequestData['product_ttl']) ? $RequestData['product_ttl'] : array());

            DB::beginTransaction();

            try {
                $isInsert = POBStockMaster::create($RequestData);
                // $idMaster = $isInsert->id;

                $lastInsertQuery = POBStockMaster::latest()->first();
                $idMaster = $lastInsertQuery->id;

                /* Child Table Insertion */
                $RequestData['ob_no'] = $RequestData['ob_no'];
                // $RequestData['company_id'] = $RequestData['company_id'];
                // $RequestData['branch_id'] = $RequestData['branch_id'];

                // $opening_date = new DateTime($RequestData['opening_date']);
                // $RequestData['opening_date'] = $opening_date->format('Y-m-d');

                $countTotalproduct = 0;

                foreach ($product_arr as $key => $product_id_sin) {
                    if ($product_qnt[$key] > 0) {
                        $countTotalproduct += 1;
                        $RequestData['product_id'] = $product_id_sin;
                        $RequestData['product_quantity'] = $product_qnt[$key];
                        $RequestData['unit_cost_price'] = $unit_cost_price[$key];
                        $RequestData['total_cost_amount'] = $product_ttl[$key];

                        $isInsertDetails = POBStockDetails::create($RequestData);
                    }
                }
                $updatetotal = POBStockMaster::where('id', $idMaster)->first();
                $updatetotal->total_product = $countTotalproduct;
                $Status = $updatetotal->save();

                DB::commit();
                $notification = array(
                    'message' => 'Successfully inserted data',
                    'alert-type' => 'success',
                );
                return redirect('inv/product_ob')->with($notification);
            } catch (\Exception $e) {

                dd($e);

                DB::rollBack();

                $notification = array(
                    'message' => 'Unsuccessful to insert datas',
                    'alert-type' => 'error',
                );

                return redirect()->back()->with($notification);
            }

        } else {
            $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
                ->orderBy('branch_code', 'ASC')
                ->get();
            $ProductData = Product::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('INV.POBStock.add', compact('BranchData', 'ProductData'));
        }
    }

    public function edit(Request $request, $id = null)
    {
        $POBDataM = POBStockMaster::where('ob_no', $id)->first();
        $POBDataD = POBStockDetails::where('ob_no', $id)->get();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'branch_id' => 'required',
                'opening_date' => 'required',
            ]);

            $RequestData = $request->all();

            $RequestData['opening_date'] = new DateTime($RequestData['opening_date']); // used date time for d/m/y format
            $RequestData['opening_date'] = $RequestData['opening_date']->format('Y-m-d')
            ;
            $RequestData['ob_no'] = $id;

            $product_arr = (isset($RequestData['product_arr']) ? $RequestData['product_arr'] : array());
            $product_qnt = (isset($RequestData['product_qnt']) ? $RequestData['product_qnt'] : array());
            $unit_cost_price = (isset($RequestData['unit_cost_price']) ? $RequestData['unit_cost_price'] : array());
            $product_ttl = (isset($RequestData['product_ttl']) ? $RequestData['product_ttl'] : array());

            DB::beginTransaction();

            try {
                $isUpdateMaster = $POBDataM->update($RequestData);



                POBStockDetails::where('ob_no', $id)->get()->each->delete();

                /* Child Table Insertion */
                // $RequestData['ob_no'] = $RequestData['ob_no'];
                // $RequestData['company_id'] = $RequestData['company_id'];
                // $RequestData['branch_id'] = $RequestData['branch_id'];

                // $opening_date = new DateTime($RequestData['opening_date']);
                // $RequestData['opening_date'] = $opening_date->format('Y-m-d');

                $countTotalproduct = 0;

                foreach ($product_arr as $key => $product_id_sin) {
                    if ($product_qnt[$key] > 0) {
                        $countTotalproduct += 1;
                        $RequestData['product_id'] = $product_id_sin;
                        $RequestData['product_quantity'] = $product_qnt[$key];
                        $RequestData['unit_cost_price'] = $unit_cost_price[$key];
                        $RequestData['total_cost_amount'] = $product_ttl[$key];
                        $isInsertDetails = POBStockDetails::create($RequestData);
                    }
                }

                $updatetotal = POBStockMaster::where('ob_no', $id)->first();
                $updatetotal->total_product = $countTotalproduct;
                $Status = $updatetotal->save();

                DB::commit();
                $notification = array(
                    'message' => 'Successfully Updated Data',
                    'alert-type' => 'success',
                );

                return redirect('inv/product_ob')->with($notification);
            } catch (\Exception $e) {
                DB::rollBack();

                $notification = array(
                    'message' => 'Unsuccessful to Update Data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        }
        else {
            $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
                ->orderBy('branch_code', 'ASC')
                ->get();
            $ProductData = Product::where('is_delete', 0)->orderBy('id', 'DESC')->get();


            $prod_in_sale_data = DB::table('inv_use_m as psm')
                            ->where([['psm.is_delete', 0], ['psm.is_active', 1],
                                ['psm.branch_id', $POBDataM->branch_id]])
                            ->leftJoin('inv_use_d as psd', 'psd.uses_bill_no', '=', 'psm.uses_bill_no')
                            ->pluck('psd.product_id');

            $prod_in_sale = $prod_in_sale_data->toarray();
            $prod_in_sale = array_unique($prod_in_sale);

            // $prod_in_sale = UsesDetails::select('product_id')->distinct()->get()->pluck('product_id')->all();

            return view('INV.POBStock.edit', compact('POBDataM', 'POBDataD', 'ProductData', 'BranchData', 'prod_in_sale'));

        }

    }

    public function view($id = null)
    {
        $POBDataM = POBStockMaster::where('ob_no', $id)->first();
        $POBDataD = POBStockDetails::where('ob_no', $id)->get();
        return view('INV.POBStock.view', compact('POBDataM', 'POBDataD'));
    }

    public function delete($id = null)
    {
        $POBStockMaster = POBStockMaster::where('ob_no', $id)->first();

        $dayendData = DayEnd::where(['branch_id' => $POBStockMaster->branch_id, 'is_active' => 0, 'is_delete' => 0])->orderBy('id', 'DESC')->first();

        if (!empty($dayendData)) {
            $notification = array(
                'message' => 'Unable to  Delete this Data',
                'alert-type' => 'error',
            );
            return redirect()->back()->with($notification);
        } else {
            $POBStockMaster->is_delete = 1;
            $delete = $POBStockMaster->save();

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
        $POpeningBalanceData = POpeningBalance::where('ob_no', $id)->first();
        if ($POpeningBalanceData->is_active == 1) {
            $POpeningBalanceData->is_active = 0;
        } else {
            $POpeningBalanceData->is_active = 1;
        }
        $Status = $POpeningBalanceData->save();
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
