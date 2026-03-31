<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\POS\Supplier;
use App\Services\HrService as HRS;
use App\Services\RoleService as Role;
use Illuminate\Http\Request;
use Redirect;

class SupplierController extends Controller
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
                0 => 'pos_suppliers.id',
                1 => 'pos_suppliers.sup_name',
                2 => 'pos_suppliers.supplier_type',
                3 => 'pos_suppliers.sup_comp_name',
                4 => 'pos_suppliers.sup_email',
                5 => 'pos_suppliers.sup_phone',
            );
            // Datatable Pagination Variable

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $supplier_type = (empty($request->input('supplier_type'))) ? null : $request->input('supplier_type');

            // Query
            $SupplierData = Supplier::where('pos_suppliers.is_delete', '=', 0)
                ->whereIn('pos_suppliers.branch_id', HRS::getUserAccesableBranchIds())
                ->where(function ($SupplierData) use ($search) {
                    if (!empty($search)) {
                        $SupplierData->where('pos_suppliers.sup_name', 'LIKE', "%{$search}%")
                            ->orWhere('pos_suppliers.sup_comp_name', 'LIKE', "%{$search}%")
                            ->orWhere('pos_suppliers.sup_email', 'LIKE', "%{$search}%")
                            ->orWhere('pos_suppliers.sup_phone', 'LIKE', "%{$search}%");
                    }
                })
                ->where(function ($SupplierData) use ($supplier_type) {
                    if (!empty($supplier_type)) {
                        $SupplierData->where('pos_suppliers.supplier_type', $supplier_type);
                    }
                })
            // ->offset($start)
            // ->limit($limit)
                ->orderBy($order, $dir)
                ->orderBy('id', 'DESC');
            // ->get();

            $tempQueryData = clone $SupplierData;
            $SupplierData = $SupplierData->offset($start)->limit($limit)->get();

            $totalData = Supplier::where([['is_delete', 0], ['is_active', 1]])->count();

            $totalFiltered = $totalData;

            if (!empty($search) || !empty($supplier_type)) {
                $totalFiltered = $tempQueryData->count();
            }

            $DataSet = array();
            $i = $start;

            foreach ($SupplierData as $Row) {
                $TempSet = array();
                $SupplierText = ($Row->supplier_type == 1) ? 'Purchase' : 'Commission';

                $TempSet = [
                    'id' => ++$i,
                    'sup_name' => $Row->sup_name,
                    'supplier_type' => $SupplierText,
                    'sup_comp_name' => $Row->sup_comp_name,
                    'sup_email' => $Row->sup_email,
                    'sup_phone' => $Row->sup_phone,
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
            $SupplierData = Supplier::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('POS.Supplier.index', compact('SupplierData'));
        }
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'sup_name' => 'required',
                'sup_comp_name' => 'required',
                'sup_email' => 'required',
                'sup_phone' => 'required',
                // 'sup_ref_no' => 'required',
                // 'sup_attentionA' => 'required',
            ]);

            $RequestData = $request->all();

            $isInsert = Supplier::create($RequestData);
            // dd($isInsert);
            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted New supplier Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/supplier')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in supplier',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            return view('POS.Supplier.add');
        }
    }

    public function edit(Request $request, $id = null)
    {
        $SupplierData = Supplier::where('id', $id)->first();

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'sup_name' => 'required',
                'sup_comp_name' => 'required',
                'sup_email' => 'required',
                'sup_phone' => 'required',
                // 'sup_ref_no' => 'required',
                // 'sup_attentionA' => 'required',
            ]);

            $Data = $request->all();

            $isUpdate = $SupplierData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Supplier Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/supplier')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Supplier',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            $SupplierData = Supplier::where('id', $id)->first();
            return view('POS.Supplier.edit', compact('SupplierData'));
        }
    }

    public function view($id = null)
    {
        $SupplierData = Supplier::where('id', $id)->first();
        return view('POS.Supplier.view', compact('SupplierData'));
    }

    public function delete($id = null)
    {
        $SupplierData = Supplier::where('id', $id)->first();
        $SupplierData->is_delete = 1;
        $delete = $SupplierData->save();

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
        $SupplierData = Supplier::where('id', $id)->first();
        if ($SupplierData->is_active == 1) {
            $SupplierData->is_active = 0;
        } else {
            $SupplierData->is_active = 1;
        }

        $Status = $SupplierData->save();

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
