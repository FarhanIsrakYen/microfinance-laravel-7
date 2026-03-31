<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\POS\Barcode;
use App\Model\POS\PBrand;
use App\Model\POS\PCategory;
use App\Model\POS\PColor;
use App\Model\POS\PGroup;
use App\Model\POS\PModel;
use App\Model\POS\Product;
use App\Model\POS\PSize;
use App\Model\POS\PSubCategory;
use App\Model\POS\PUOM;
use App\Model\POS\Supplier;
use DB;
use Illuminate\Http\Request;
use Picqer;
use Redirect;
use App\Services\RoleService as Role;

class ProductControllerbackup extends Controller
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
                0 => 'pos_products.id',
                1 => 'pos_products.product_name',
                2 => 'pos_products.product_image',
                3 => 'pos_products.prod_barcode',
                4 => 'pos_products.sys_barcode',
                5 => 'pos_products.cost_price',
                6 => 'pos_products.sale_price',
                7 => 'pos_p_groups.group_name',
                8 => 'pos_p_categories.cat_name',
                9 => 'pos_p_models.model_name',
                10 => 'pos_p_brands.brand_name',
            );
            // Datatable Pagination Variable
            $totalData = Product::where('pos_products.is_delete', '=', 0)->count();
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
            $ProductData = Product::where('pos_products.is_delete', '=', 0)
                ->select('pos_products.*',
                    'pos_p_groups.group_name as group_name',
                    'pos_p_categories.cat_name as cat_name',
                    'pos_p_models.model_name as model_name',
                    'pos_p_brands.brand_name as brand_name',
                    'pos_suppliers.sup_comp_name as sup_comp_name')
                ->leftJoin('pos_p_groups', 'pos_products.prod_group_id', '=', 'pos_p_groups.id')
                ->leftJoin('pos_p_categories', 'pos_products.prod_cat_id', '=', 'pos_p_categories.id')
                ->leftJoin('pos_p_models', 'pos_products.prod_model_id', '=', 'pos_p_models.id')
                ->leftJoin('pos_p_brands', 'pos_products.prod_brand_id', '=', 'pos_p_brands.id')
                ->leftJoin('pos_suppliers', 'pos_products.supplier_id', '=', 'pos_suppliers.id')
                ->where(function ($ProductData) use ($search, $PGroupID, $CategoryId, $SubCatID, $ModelID, $BrandID) {
                    if (!empty($search)) {
                        $ProductData->where('pos_products.product_name', 'LIKE', "%{$search}%")
                            ->orWhere('pos_products.prod_barcode', 'LIKE', "%{$search}%")
                            ->orWhere('pos_products.sys_barcode', 'LIKE', "%{$search}%")
                            ->orWhere('pos_p_groups.group_name', 'LIKE', "%{$search}%")
                            ->orWhere('pos_p_categories.cat_name', 'LIKE', "%{$search}%")
                            ->orWhere('pos_p_models.model_name', 'LIKE', "%{$search}%")
                            ->orWhere('pos_p_brands.brand_name', 'LIKE', "%{$search}%");
                    }

                    if (!empty($PGroupID)) {
                        $ProductData->where('pos_products.prod_group_id', '=', $PGroupID);
                    }

                    if (!empty($CategoryId)) {
                        $ProductData->where('pos_products.prod_cat_id', '=', $CategoryId);
                    }

                    if (!empty($SubCatID)) {
                        $ProductData->where('pos_products.prod_sub_cat_id', '=', $SubCatID);
                    }
                    if (!empty($BrandID)) {
                        $ProductData->where('pos_products.prod_brand_id', '=', $BrandID);
                    }
                    if (!empty($ModelID)) {
                        $ProductData->where('pos_products.prod_model_id', '=', $ModelID);
                    }
                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('pos_products.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            if (!empty($search) || !empty($PGroupID) || !empty($CategoryId) || !empty($SubCatID) || !empty($ModelID) || !empty($BrandID)) {
                $totalFiltered = count($ProductData);
            }

            $DataSet = array();
            $i = 0;

            foreach ($ProductData as $Row) {
                $BarcodeText = "<p><b>Given:</b> " . $Row->prod_barcode . "</p>";
                $BarcodeText .= "<p><b>System:</b> " . $Row->sys_barcode . "</p>";
                $PriceText = "<p><b>Cost:</b> " . $Row->cost_price . "</p>";
                $PriceText .= "<p><b>Sale:</b> " . $Row->sale_price . "</p>";
                $DetailsText = "<p><b>Group:</b> " . $Row->group_name . "</p>";
                $DetailsText .= "<p><b>Category:</b> " . $Row->cat_name . "</p>";
                $DetailsText .= "<p><b>Model:</b> " . $Row->model_name . "</p>";
                $DetailsText .= "<p><b>Brand:</b> " . $Row->brand_name . "</p>";

                $pImage = "";

                if (!empty($Row->product_image)) {
                    if (file_exists($Row->product_image)) {
                        $pImage = '<img src="' . asset($Row->product_image) . '" class="productImage" >';
                    }
                } else {
                    $pImage = '<img src="' . asset("/assets/images/dummy.png") . '" class="productImage" >';
                }

                // $PImage = (!empty($Row->product_image)).
                // '(file_exists($Row->product_image))'?'<img src="{{ asset($Row->product_image) }}">':
                // '<img src=" asset('/assets/images/dummy.png') >';

                $TempSet = array();

                $TempSet = [
                    'id' => ++$i,
                    'product_name' => $Row->product_name,
                    'product_image' => $pImage,
                    'prod_barcode' => $BarcodeText,
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
            // $PGroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            // $PCategoryData = PCategory::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            // $PSubCatData = PSubCategory::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            // $PBrandData = PBrand::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            // $PModelData = PModel::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            // $ProductData = Product::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            // compact('PGroupData', 'PCategoryData', 'PSubCatData', 'PBrandData', 'PModelData')
            return view('POS.Product.index');

        }
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'product_name' => 'required|string',
                'sys_barcode' => 'required|string',
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
                'sale_price' => 'required',
            ]);

            $request['barcode_image'] = null;

            $RequestData = $request->all();
            $flagProdBar = false;

            // dd(empty($RequestData['prod_barcode']) );

            if(isset($RequestData['prod_barcode']) == false && empty($RequestData['prod_barcode'])){
                
                // $flagProdBar = true;
                // dd('hshs');
                $RequestData['prod_barcode'] = $RequestData['sys_barcode'];
            }
            // dd('hello');
            DB::beginTransaction();

            try
            {
                // Your Code here

                $isInsert = Product::create($RequestData);
                if ($isInsert) {
                    $pid = $isInsert->id;
//    dd($pid);
                 
                    $barcode_num = $request->sys_barcode;
                    $barcode_generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                    $image = $barcode_generator->getBarcode($barcode_num, $barcode_generator::TYPE_CODE_128);
                    if ($image != null) {
                        $image_name = "bar_" . $barcode_num;
                        $img_path = 'storage/uploads/product/barcode/' . $pid;

                        if (!is_dir($img_path)) {
                            mkdir($img_path, 0777, true);
                        }
                        $img_path = $img_path . '/' . $image_name . '.png';
                        file_put_contents($img_path, $image);
                        // dd(file_put_contents($img_path, $image));

                        $isInsert->barcode_image = $img_path;
                        $isInsert->update();
                        dd($isInsert);
                        // for barcode table
                        $barcode = new Barcode;
                        $barcode->product_id = $pid;
                        $barcode->product_barcode = $isInsert->sys_barcode;
                        $barcode->save();
                    }

                }
                DB::commit();
                $notification = array(
                    'message' => 'Successfully Inserted Product.',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/product')->with($notification);

            } catch (\Exception $e) {
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to insert Product.',
                    'alert-type' => 'error',
                );
                dd($e);
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
            return view('POS.Product.add', compact('PGroupData', 'SupplierData', 'UOMData', 'BrandData', 'ColorData', 'SizeData'));
            // , 'CompanyData'
        }
    }

    public function edit(Request $request, $id = null)
    {

        $ProductData = Product::where('id', $id)->first();
        $barcode = Barcode::where('product_id', $id)->first();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'product_name' => 'required',
                'sys_barcode' => 'required',
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
                'sale_price' => 'required',
            ]);


            if ($ProductData->sys_barcode != $request->sys_barcode) {

                if ($request->barcode_image_old != null) {

                    if (file_exists($request->barcode_image_old)) {
                        unlink($request->barcode_image_old);
                    }

                }
                $barcode_num = $request->sys_barcode;
                $barcode_generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                $image = $barcode_generator->getBarcode($barcode_num, $barcode_generator::TYPE_CODE_128);
                if ($image != null) {
                    $image_name = "bar_" . $barcode_num;
                    $img_path = 'storage/uploads/product/barcode/' . $id;
                    if (!is_dir($img_path)) {
                        mkdir($img_path, 0777, true);
                    }
                    $img_path = $img_path . '/' . $image_name . '.png';
                    file_put_contents($img_path, $image);

                    $request['barcode_image'] = $img_path;
                }

            }else{
                $request['barcode_image'] =  $request['barcode_image_old'];
            }

            $Data = $request->all();

            if(isset($Data['prod_barcode']) == false && empty($Data['prod_barcode'])){
                $Data['prod_barcode'] = $Data['sys_barcode'];
            }
          
            DB::beginTransaction();
            try
            {
                // Your Code here.
                $isUpdate = $ProductData->update($Data);
               

                if ($isUpdate) {
                    if(!empty($barcode)){
                        $barcode->product_id = $id;
                        $barcode->product_barcode = $ProductData->sys_barcode;
                        $barcode->save();
                    }else{
                        $barcode = new Barcode;
                        $barcode->product_id = $id;
                        $barcode->product_barcode = $isInsert->sys_barcode;
                        $barcode->save();

                    }
                   // dd($isUpdate);
                }
                DB::commit();
                $notification = array(
                    'message' => 'Successfully Updated Product .',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/product')->with($notification);
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

            return view('POS.Product.edit', compact('ProductData', 'PGroupData', 'UOMData', 'SupplierData', 'BrandData', 'ColorData', 'SizeData'));
            // 'CompanyData',
        }

    }

    public function view($id = null)
    {
        $ProductData = Product::where('id', $id)->first();
        return view('POS.Product.view', compact('ProductData'));
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

    // public function ajaxSelectModelLoad(Request $request)
    // {

    //     if ($request->ajax()) {

    //         $groupId = $request->group_id;
    //         $catId = $request->cat_id;
    //         $subCatId = $request->sub_cat_id;
    //         $brandId = $request->brand_id;
    //         $sel_model_id = $request->sel_model_id;


    //         $QueryData = DB::table('pos_p_models')
    //                     ->where([['is_delete', 0], ['is_active', 1], ['prod_group_id', $groupId], ['prod_cat_id', $catId], 
    //                     ['prod_sub_cat_id', $subCatId], ['prod_brand_id', $brandId]])
    //                     ->get();

    //         $output = '<option value="">Select One</option>';
    //         foreach ($QueryData as $Row) {

    //             $SelectText = '';

    //             if ($sel_model_id != null) {
    //                 if ($sel_model_id == $Row->id) {
    //                     $SelectText = 'selected="selected"';
    //                 }
    //             }
    //             $output .= '<option value="' . $Row->id . '" ' . $SelectText . '>' . $Row->model_name . '</option>';
    //         }

    //         echo $output;
    //     }
    // }

    public function ajaxLoadModelFP(Request $request)
    {
        if ($request->ajax()) {

            $groupId = $request->group_id;
            $catId = $request->cat_id;
            $subCatId = $request->sub_cat_id;
            $sel_model_id = $request->sel_model_id;

            $QueryData = DB::table('pos_p_models')
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

            $QueryData = DB::table('pos_p_sizes')
                        ->where([['is_delete', 0], ['is_active', 1], 
                        ['prod_group_id', $groupId], 
                        ['prod_cat_id', $catId], 
                        ['prod_sub_cat_id', $subCatId]
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

            $QueryData = DB::table('pos_p_colors')
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