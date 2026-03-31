<?php

namespace App\Http\Controllers\BILL;

use App\Http\Controllers\Controller;
use App\Model\BILL\PCategory;
use App\Model\BILL\Product;
use App\Model\BILL\Supplier;
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
                0 => 'bill_products.id',
                1 => 'bill_products.product_name',
                2 => 'bill_products.product_image',
                3 => 'bill_products.sale_price',
                4 => 'bill_p_categories.cat_name',
            );
            // Datatable Pagination Variable
            $totalData = Product::where('bill_products.is_delete', '=', 0)->count();
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $CategoryId = (empty($request->input('CategoryId'))) ? null : $request->input('CategoryId');

            // Query
            $ProductData = Product::where('bill_products.is_delete', '=', 0)
                ->select('bill_products.*',
                    'bill_p_categories.cat_name as cat_name',
                    'bill_suppliers.sup_comp_name as sup_comp_name')
                ->leftJoin('bill_p_categories', 'bill_products.prod_cat_id', '=', 'bill_p_categories.id')
                ->leftJoin('bill_suppliers', 'bill_products.supplier_id', '=', 'bill_suppliers.id')
                ->where(function ($ProductData) use ($search, $CategoryId) {

                    if (!empty($search)) {
                        $ProductData->where('bill_products.product_name', 'LIKE', "%{$search}%")
                            ->orWhere('bill_p_categories.cat_name', 'LIKE', "%{$search}%");
                    }


                    if (!empty($CategoryId)) {
                        $ProductData->where('bill_products.prod_cat_id', '=', $CategoryId);
                    }

                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('bill_products.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            if (!empty($search) || !empty($CategoryId)) {
                $totalFiltered = count($ProductData);
            }
             // || !empty($ModelID) || !empty($BrandID
            $DataSet = array();
            $i = 0;

            foreach ($ProductData as $Row) {
                $PriceText = "<p><b>Sale:</b> " . $Row->sale_price . "</p>";
                $DetailsText = "<p><b>Category:</b> " . $Row->cat_name . "</p>";
                $pImage = "";

                if (!empty($Row->prod_image)) {
                    if (file_exists($Row->prod_image)) {
                        $pImage = '<img src="' . asset($Row->prod_image) . '" class="productImage" >';
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
            return view('BILL.Product.index');

        }
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'product_name' => 'required|string',
                'prod_cat_id' => 'required',
                'sale_price' => 'required',
            ]);

            // $request['barcode_image'] = null;

            $RequestData = $request->all();
            $flagProdBar = false;
            $RequestData['company_id'] = Session::get('LoginBy.user_config.company_id');

            DB::beginTransaction();
            try
            {
                // Your Code here
                Product::create($RequestData);
                $lastInsertQuery = Product::latest()->first();

                // if ($RequestData['sys_barcode'] == $lastInsertQuery->sys_barcode) {
                    // $pid = $isInsert->id;
                    $lastInsertQuery = Product::latest()->first('id');
                    $tableName = $lastInsertQuery->getTable();
                    $pid = $lastInsertQuery->id;

                    $pro_image = $request->file('prod_image');

                    if ($pro_image != null) {

                        $FileType = $pro_image->getMimeType();

                        if (($FileType != "image/jpeg") && ($FileType != "image/pjpeg") && ($FileType != "image/jpg") && ($FileType != "image/png")) {
                            $prod_image_url = null;
                        } else {
                            $upload = Common::fileUpload($pro_image, $tableName, $pid);
                            $prod_image_url = $upload;
                        }
                    } else {
                        $prod_image_url = null;
                    }

                    // $barcode_num = $request->sys_barcode;
                    // $barcode_generator = new Picqer\Barcode\BarcodeGeneratorPNG();
                    // $image = $barcode_generator->getBarcode($barcode_num, $barcode_generator::TYPE_CODE_128);

                    // if ($image != null) {
                    //     $image_name = "bar_";
                    //     $uploadBar = Common::fileUpload($image, $tableName, $pid, true, $image_name);

                        // $barcode_image_url = $uploadBar;

                        // for barcode table
                        // $barcode = new Barcode;
                        // $barcode->product_id = $pid;
                        // $barcode->product_barcode = $barcode_num;
                        //
                        // $barcode->save();
                    // }
                    // else {
                    //     // $barcode_image_url = null;
                    // }

                    $lastInsertQuery->prod_image = $prod_image_url;
                    // $lastInsertQuery->barcode_image = $barcode_image_url;

                    $isSuccess = $lastInsertQuery->update();

                    // Product::where('id', $pid)->update(['prod_image' => $prod_image_url, 'barcode_image' => $barcode_image_url]);
                // }
                DB::commit();
                $notification = array(
                    'message' => 'Successfully Inserted Product.',
                    'alert-type' => 'success',
                );
                return Redirect::to('bill/product')->with($notification);

            }
            catch (\Exception $e) {
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
            $PCategoryData = PCategory::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $SupplierData = Supplier::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            // $UOMData = PUOM::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            // $BrandData = PBrand::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            // $ColorData = PColor::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            // $SizeData = PSize::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('BILL.Product.add', compact('SupplierData','PCategoryData'));
            // , 'CompanyData', 'UOMData', 'BrandData', 'ColorData', 'SizeData'
        }
    }

    public function edit(Request $request, $id = null)
    {

        $ProductData = Product::where('id', $id)->first();
        // $barcode = Barcode::where('product_id', $id)->first();

        $tableName = $ProductData->getTable();
        $pid = $id;

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'product_name' => 'required',
                'prod_cat_id' => 'required',
                'sale_price' => 'required',
            ]);
            // prod_image

            // if ($ProductData->sys_barcode != $request->sys_barcode) {
            //
            //     if ($request->barcode_image_old != null) {
            //
            //         if (file_exists($request->barcode_image_old)) {
            //             unlink($request->barcode_image_old);
            //         }
            //
            //     }
            //     $barcode_num = $request->sys_barcode;
            //     $barcode_generator = new Picqer\Barcode\BarcodeGeneratorPNG();
            //     $image = $barcode_generator->getBarcode($barcode_num, $barcode_generator::TYPE_CODE_128);
            //
            //     if ($image != null) {
            //
            //         $image_name = "bar_" . $barcode_num;
            //         $uploadBar = Common::fileUpload($image, $tableName, $pid, true, $image_name);
            //         $request['barcode_image'] = $uploadBar;
            //     }
            //
            // } else {
            //     $request['barcode_image'] = $request['barcode_image_old'];
            // }

            $Data = $request->all();
            $pro_image = $request->file('prod_image');

            if ($pro_image != null) {
                $FileType = $pro_image->getMimeType();

                if (($FileType != "image/jpeg") &&
                ($FileType != "image/pjpeg") &&
                ($FileType != "image/jpg") &&
                 ($FileType != "image/png")) {
                    $pro_image = null;
                } else {
                    $upload = Common::fileUpload($pro_image, $tableName, $pid);
                    $Data['prod_image'] = $upload;
                }
            }

            // // company Id
            $Data['company_id'] = Session::get('LoginBy.user_config.company_id');

            // if (isset($Data['prod_barcode']) == false && empty($Data['prod_barcode'])) {
            //     $Data['prod_barcode'] = $Data['sys_barcode'];
            // }

            DB::beginTransaction();
            try
            {
                // Your Code here.
                $isUpdate = $ProductData->update($Data);

                // if ($isUpdate) {
                //     if (!empty($barcode)) {
                //         $barcode->product_id = $id;
                //         $barcode->product_barcode = $ProductData->sys_barcode;
                //         $barcode->save();
                //     } else {
                //         $barcode = new Barcode;
                //         $barcode->product_id = $id;
                //         $barcode->product_barcode = $isInsert->sys_barcode;
                //         $barcode->save();
                //
                //     }
                //     // dd($isUpdate);
                // }
                DB::commit();
                $notification = array(
                    'message' => 'Successfully Updated Product .',
                    'alert-type' => 'success',
                );
                return Redirect::to('bill/product')->with($notification);
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
            $PCategoryData = PCategory::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $SupplierData = Supplier::where('is_delete', 0)->orderBy('id', 'DESC')->get();

            return view('BILL.Product.edit', compact('ProductData', 'PCategoryData', 'SupplierData'));
            // 'CompanyData',, 'BrandData', 'ColorData', 'SizeData', 'UOMData'
        }

    }

    public function view($id = null)
    {
        $ProductData = Product::where('id', $id)->first();
        return view('BILL.Product.view', compact('ProductData'));
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

    // public function ajaxLoadModelFP(Request $request)
    // {
    //     if ($request->ajax()) {
    //
    //         $groupId = $request->group_id;
    //         $catId = $request->cat_id;
    //         $subCatId = $request->sub_cat_id;
    //         $sel_model_id = $request->sel_model_id;
    //
    //         $QueryData = DB::table('bill_p_models')
    //             ->where([['is_delete', 0], ['is_active', 1], ['prod_group_id', $groupId], ['prod_cat_id', $catId],
    //                 ['prod_sub_cat_id', $subCatId]])
    //             ->get();
    //
    //         $output = '<option value="">Select One</option>';
    //         foreach ($QueryData as $Row) {
    //
    //             $SelectText = '';
    //
    //             if ($sel_model_id != null) {
    //                 if ($sel_model_id == $Row->id) {
    //                     $SelectText = 'selected="selected"';
    //                 }
    //             }
    //             $output .= '<option value="' . $Row->id . '" ' . $SelectText . '>' . $Row->model_name . '</option>';
    //         }
    //
    //         echo $output;
    //     }
    // }
    //
    // public function ajaxLoadSizeFP(Request $request)
    // {
    //
    //     if ($request->ajax()) {
    //
    //         $groupId = $request->group_id;
    //         $catId = $request->cat_id;
    //         $subCatId = $request->sub_cat_id;
    //         $sel_size_id = $request->sel_size_id;
    //
    //         $QueryData = DB::table('bill_p_sizes')
    //             ->where([['is_delete', 0], ['is_active', 1],
    //                 ['prod_group_id', $groupId],
    //                 ['prod_cat_id', $catId],
    //                 ['prod_sub_cat_id', $subCatId],
    //             ])
    //             ->get();
    //
    //         $output = '<option value="">Select One</option>';
    //         foreach ($QueryData as $Row) {
    //
    //             $SelectText = '';
    //
    //             if ($sel_size_id != null) {
    //                 if ($sel_size_id == $Row->id) {
    //                     $SelectText = 'selected="selected"';
    //                 }
    //             }
    //             $output .= '<option value="' . $Row->id . '" ' . $SelectText . '>' . $Row->size_name . '</option>';
    //         }
    //
    //         echo $output;
    //     }
    // }
    //
    // public function ajaxLoadColorFP(Request $request)
    // {
    //
    //     if ($request->ajax()) {
    //
    //         $groupId = $request->group_id;
    //         $catId = $request->cat_id;
    //         $subCatId = $request->sub_cat_id;
    //         $sel_color_id = $request->sel_color_id;
    //
    //         $QueryData = DB::table('bill_p_colors')
    //             ->where([['is_delete', 0],
    //                 ['is_active', 1],
    //                 ['prod_group_id', $groupId],
    //                 ['prod_cat_id', $catId],
    //                 ['prod_sub_cat_id', $subCatId],
    //             ])
    //             ->get();
    //
    //         $output = '<option value="">Select One</option>';
    //         foreach ($QueryData as $Row) {
    //
    //             $SelectText = '';
    //
    //             if ($sel_color_id != null) {
    //                 if ($sel_color_id == $Row->id) {
    //                     $SelectText = 'selected="selected"';
    //                 }
    //             }
    //             $output .= '<option value="' . $Row->id . '" ' . $SelectText . '>' . $Row->color_name . '</option>';
    //         }
    //
    //         echo $output;
    //     }
    // }

}
