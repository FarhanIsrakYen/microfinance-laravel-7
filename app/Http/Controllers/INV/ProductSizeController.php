<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use App\Model\INV\PSize;
use App\Model\INV\PGroup;
use DB;
use Illuminate\Http\Request;
use Redirect;

class ProductSizeController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    // List Of Product Size
    public function index(Request $request)
    {
        $ProdSizeData = PSize::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('INV.ProductSize.index', compact('ProdSizeData'));
    }

    // Add and Store Product Size
    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'size_name' => 'required',
            ]);
            $RequestData = $request->all();

            $isInsert = PSize::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted Product Size Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('inv/size')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Product Size',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            $ProdGroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('INV.ProductSize.add', compact('ProdGroupData'));
        }
    }

    // Edit Product Size
    public function edit(Request $request, $id = null)
    {

        $ProdSizeData = PSize::where('id', $id)->first();
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'size_name' => 'required',
            ]);

            $Data = $request->all();

            $isUpdate = $ProdSizeData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Product Size Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('inv/size')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to update data in Product Size',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            $ProdGroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();

            return view('INV.ProductSize.edit', compact('ProdSizeData','ProdGroupData'));
        }
    }

    // View Product Size
    public function view($id = null)
    {
        $ProdSizeData = PSize::where('id', $id)->first();
        return view('INV.ProductSize.view', compact('ProdSizeData'));
    }

    // Soft Delete Product Size
    public function delete($id = null)
    {

        $ProdSizeData = PSize::where('id', $id)->first();
        $ProdSizeData->is_delete = 1;

        $delete = $ProdSizeData->save();

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

    // Parmanet Delete Product Size
    public function destroy($id = null)
    {
        $ProdSizeData = PSize::where('id', $id)->first();
        $delete = $ProdSizeData->delete();

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

    // Publish/ Unpublish Product Size
    public function isActive($id = null)
    {
        $ProdSizeData = PSize::where('id', $id)->first();
        if ($ProdSizeData->is_active == 1) {
            $ProdSizeData->is_active = 0;
        } else {
            $ProdSizeData->is_active = 1;
        }

        $Status = $ProdSizeData->save();

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


    public function ajaxSelectModelLoad(Request $request)
    {

        if ($request->ajax()) {

            $groupId = $request->group_id;
            $catId = $request->cat_id;
            $subCatId = $request->sub_cat_id;
            $brandId = $request->brand_id;
            $sel_model_id = $request->sel_model_id;


            $QueryData = DB::table('inv_p_models')
                        ->where([['is_delete', 0], ['is_active', 1], ['prod_group_id', $groupId], ['prod_cat_id', $catId], 
                        ['prod_sub_cat_id', $subCatId], ['prod_brand_id', $brandId]])
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

}
