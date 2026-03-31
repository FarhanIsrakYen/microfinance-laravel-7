<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use App\Model\INV\Barcode;
use App\Model\INV\PBrand;
use App\Model\INV\PColor;
use App\Model\INV\PGroup;
use App\Model\INV\Product;
use App\Model\INV\PSize;
use App\Model\INV\PUOM;
use App\Model\INV\Supplier;
use App\Services\CommonService as Common;
use DB;
use Illuminate\Http\Request;
use Picqer;
use Redirect;
use App\Services\RoleService as Role;
use Session;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission');
        
        // $this->middleware('permission', ['except' => ['ajaxLoadModelFP', 'ajaxLoadSizeFP', 'ajaxLoadColorFP']]);
        parent::__construct();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $columns = array(
                0 => 'inv_products.id',
                1 => 'inv_products.product_name',
                2 => 'inv_products.product_image',
                5 => 'inv_products.cost_price',
                7 => 'inv_p_groups.group_name',
                8 => 'inv_p_categories.cat_name',
                9 => 'inv_p_models.model_name',
                10 => 'inv_p_brands.brand_name',
            );
            // Datatable Pagination Variable
            $totalData = Product::where('inv_products.is_delete', '=', 0)->count();
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $PGroupID = (empty($request->input('PGroupID'))) ? null : $request->input('PGroupID');
            $CategoryId = (empty($request->input('CategoryId'))) ? null : $request->input('CategoryId');
            $SubCatID = (empty($request->input('SubCatID'))) ? null : $request->input('SubCatID');
            $BrandID = (empty($request->input('BrandID'))) ? null : $request->input('BrandID');
            $ModelID = (empty($request->input('ModelID'))) ? null : $request->input('ModelID');

            // Query
            $ProductData = Product::where('inv_products.is_delete', '=', 0)
                ->select('inv_products.*',
                    'inv_p_groups.group_name as group_name',
                    'inv_p_categories.cat_name as cat_name',
                    'inv_p_models.model_name as model_name',
                    'inv_p_brands.brand_name as brand_name',
                    'inv_suppliers.sup_comp_name as sup_comp_name')
                ->leftJoin('inv_p_groups', 'inv_products.prod_group_id', '=', 'inv_p_groups.id')
                ->leftJoin('inv_p_categories', 'inv_products.prod_cat_id', '=', 'inv_p_categories.id')
                ->leftJoin('inv_p_models', 'inv_products.prod_model_id', '=', 'inv_p_models.id')
                ->leftJoin('inv_p_brands', 'inv_products.prod_brand_id', '=', 'inv_p_brands.id')
                ->leftJoin('inv_suppliers', 'inv_products.supplier_id', '=', 'inv_suppliers.id')
                ->where(function ($ProductData) use ($search, $PGroupID, $CategoryId, $SubCatID, $ModelID, $BrandID) {
                    if (!empty($search)) {
                        $ProductData->where('inv_products.product_name', 'LIKE', "%{$search}%")
                            ->orWhere('inv_products.prod_barcode', 'LIKE', "%{$search}%")
                            ->orWhere('inv_products.sys_barcode', 'LIKE', "%{$search}%")
                            ->orWhere('inv_p_groups.group_name', 'LIKE', "%{$search}%")
                            ->orWhere('inv_p_categories.cat_name', 'LIKE', "%{$search}%")
                            ->orWhere('inv_p_models.model_name', 'LIKE', "%{$search}%")
                            ->orWhere('inv_p_brands.brand_name', 'LIKE', "%{$search}%");
                    }

                    if (!empty($PGroupID)) {
                        $ProductData->where('inv_products.prod_group_id', '=', $PGroupID);
                    }

                    if (!empty($CategoryId)) {
                        $ProductData->where('inv_products.prod_cat_id', '=', $CategoryId);
                    }

                    if (!empty($SubCatID)) {
                        $ProductData->where('inv_products.prod_sub_cat_id', '=', $SubCatID);
                    }
                    if (!empty($BrandID)) {
                        $ProductData->where('inv_products.prod_brand_id', '=', $BrandID);
                    }
                    if (!empty($ModelID)) {
                        $ProductData->where('inv_products.prod_model_id', '=', $ModelID);
                    }
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('inv_products.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            if (!empty($search) || !empty($PGroupID) || !empty($CategoryId) || !empty($SubCatID) || !empty($ModelID) || !empty($BrandID)) {
                $totalFiltered = count($ProductData);
            }

            $DataSet = array();
            $i = 0;

            foreach ($ProductData as $Row) {
                $PriceText = $Row->cost_price;
                $DetailsText = "<p><b>Group:</b> " . $Row->group_name . "</p>";
                $DetailsText .= "<p><b>Category:</b> " . $Row->cat_name . "</p>";
                $DetailsText .= "<p><b>Model:</b> " . $Row->model_name . "</p>";
                $DetailsText .= "<p><b>Brand:</b> " . $Row->brand_name . "</p>";

                $pImage = "";

                if (!empty($Row->prod_image)) {
                    if (file_exists($Row->prod_image)) {
                        $pImage = '<img src="' . asset($Row->prod_image) . '" class="productImage" >';
                    }
                } else {
                    $pImage = '<img src="' . asset("/assets/images/dummy.png") . '" class="productImage" >';
                }

                $TempSet = array();

                $TempSet = [
                    'id' => ++$i,
                    'product_name' => $Row->product_name,
                    'product_image' => $pImage,
                    'Supplier' => $Row->sup_comp_name,
                    'Price' => $PriceText,
                    'Details' => $DetailsText,
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
            return view('INV.Product.index');

        }
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'product_name' => 'required|string',
                'supplier_id' => 'required',
                'prod_group_id' => 'required',
                'prod_cat_id' => 'required',
                'prod_sub_cat_id' => 'required',
                'prod_brand_id' => 'required',
                'prod_model_id' => 'required',
                'prod_size_id' => 'required',
                'prod_color_id' => 'required',
                'prod_uom_id' => 'required',
                'cost_price' => 'required',
            ]);

            $RequestData = $request->all();

            // // company Id
            $RequestData['company_id'] = Common::getCompanyId();

            DB::beginTransaction();
            try
            {
                // Your Code here
                Product::create($RequestData);
                $lastInsertQuery = Product::latest()->first();

                $tableName = $lastInsertQuery->getTable();
                $pid = $lastInsertQuery->id;

                $pro_image = $request->file('prod_image');

                if ($pro_image != null) {

                    $FileType = $pro_image->getMimeType();

                    if (($FileType != "image/jpeg") 
                    && ($FileType != "image/pjpeg") 
                    && ($FileType != "image/jpg") 
                    && ($FileType != "image/png")) {
                        $prod_image_url = null;
                    } else {
                        $upload = Common::fileUpload($pro_image, $tableName, $pid);
                        $prod_image_url = $upload;
                    }
                } else {
                    $prod_image_url = null;
                }

                $lastInsertQuery->prod_image = $prod_image_url;

                $isSuccess = $lastInsertQuery->update();

                DB::commit();
                $notification = array(
                    'message' => 'Successfully Inserted Product.',
                    'alert-type' => 'success',
                );
                return Redirect::to('inv/product')->with($notification);

            } catch (\Exception $e) {
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to insert Product.',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
                //return $e;
            }

        } else {
            // $CompanyData = Company ::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $PGroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $SupplierData = Supplier::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $UOMData = PUOM::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $BrandData = PBrand::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $ColorData = PColor::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $SizeData = PSize::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('INV.Product.add', compact('PGroupData', 'SupplierData', 'UOMData', 'BrandData', 'ColorData', 'SizeData'));
            // , 'CompanyData'
        }
    }

    public function edit(Request $request, $id = null)
    {

        $ProductData = Product::where('id', $id)->first();

        $tableName = $ProductData->getTable();
        $pid = $id;

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'product_name' => 'required',
                'supplier_id' => 'required',
                'prod_group_id' => 'required',
                'prod_cat_id' => 'required',
                'prod_sub_cat_id' => 'required',
                'prod_brand_id' => 'required',
                'prod_model_id' => 'required',
                'prod_size_id' => 'required',
                'prod_color_id' => 'required',
                'prod_uom_id' => 'required',
                'cost_price' => 'required',
            ]);
            // prod_image

            $Data = $request->all();
            $pro_image = $request->file('prod_image');

            if ($pro_image != null) {
                $FileType = $pro_image->getMimeType();

                if (($FileType != "image/jpeg") 
                && ($FileType != "image/pjpeg") 
                && ($FileType != "image/jpg") 
                && ($FileType != "image/png")) {
                    $pro_image = null;
                } else {
                    $upload = Common::fileUpload($pro_image, $tableName, $pid);
                    $Data['prod_image'] = $upload;
                }
            }

            // // company Id
            $Data['company_id'] = Common::getCompanyId();

            DB::beginTransaction();
            try
            {
                // Your Code here.
                $isUpdate = $ProductData->update($Data);

                DB::commit();
                $notification = array(
                    'message' => 'Successfully Updated Product .',
                    'alert-type' => 'success',
                );
                return Redirect::to('inv/product')->with($notification);
                // return
            } catch (\Exception $e) {
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to Update Product .',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

        } else {
            # code...
            $PGroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $SupplierData = Supplier::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $UOMData = PUOM::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $BrandData = PBrand::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $ColorData = PColor::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $SizeData = PSize::where('is_delete', 0)->orderBy('id', 'DESC')->get();

            return view('INV.Product.edit', compact('ProductData', 'PGroupData', 'UOMData', 'SupplierData', 'BrandData', 'ColorData', 'SizeData'));
            // 'CompanyData',
        }

    }

    public function view($id = null)
    {
        $ProductData = Product::where('id', $id)->first();
        return view('INV.Product.view', compact('ProductData'));
    }

    public function delete($id = null)
    {
        $ProductData = Product::where('id', $id)->first();
        $ProductData->is_delete = 1;
        $delete = $ProductData->save();

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
        $ProductData = Product::where('id', $id)->first();
        if ($ProductData->is_active == 1) {
            $ProductData->is_active = 0;
        } else {
            $ProductData->is_active = 1;
        }
        $Status = $ProductData->save();

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

    public function ajaxLoadModelFP(Request $request)
    {
        if ($request->ajax()) {

            $groupId = $request->group_id;
            $catId = $request->cat_id;
            $subCatId = $request->sub_cat_id;
            $sel_model_id = $request->sel_model_id;

            $QueryData = DB::table('inv_p_models')
                ->where([['is_delete', 0], ['is_active', 1], ['prod_group_id', $groupId], ['prod_cat_id', $catId],
                    ['prod_sub_cat_id', $subCatId]])
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $SelectText = '';

                if ($sel_model_id != null) {
                    if ($sel_model_id == $Row->id) {
                        $SelectText = 'selected="selected"';
                    }
                }
                $output .= '<option value="' . $Row->id . '" ' . $SelectText . '>' . $Row->model_name . '</option>';
            }

            echo $output;
        }
    }

    public function ajaxLoadSizeFP(Request $request)
    {

        if ($request->ajax()) {

            $groupId = $request->group_id;
            $catId = $request->cat_id;
            $subCatId = $request->sub_cat_id;
            $sel_size_id = $request->sel_size_id;

            $QueryData = DB::table('inv_p_sizes')
                ->where([['is_delete', 0], ['is_active', 1],
                    ['prod_group_id', $groupId],
                    ['prod_cat_id', $catId],
                    ['prod_sub_cat_id', $subCatId],
                ])
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $SelectText = '';

                if ($sel_size_id != null) {
                    if ($sel_size_id == $Row->id) {
                        $SelectText = 'selected="selected"';
                    }
                }
                $output .= '<option value="' . $Row->id . '" ' . $SelectText . '>' . $Row->size_name . '</option>';
            }

            echo $output;
        }
    }

    public function ajaxLoadColorFP(Request $request)
    {

        if ($request->ajax()) {

            $groupId = $request->group_id;
            $catId = $request->cat_id;
            $subCatId = $request->sub_cat_id;
            $sel_color_id = $request->sel_color_id;

            $QueryData = DB::table('inv_p_colors')
                ->where([['is_delete', 0],
                    ['is_active', 1],
                    ['prod_group_id', $groupId],
                    ['prod_cat_id', $catId],
                    ['prod_sub_cat_id', $subCatId],
                ])
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $SelectText = '';

                if ($sel_color_id != null) {
                    if ($sel_color_id == $Row->id) {
                        $SelectText = 'selected="selected"';
                    }
                }
                $output .= '<option value="' . $Row->id . '" ' . $SelectText . '>' . $Row->color_name . '</option>';
            }

            echo $output;
        }
    }

}
