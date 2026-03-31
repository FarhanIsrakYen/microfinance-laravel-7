<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use App\Model\INV\PColor;
use App\Model\INV\PGroup;
use Illuminate\Http\Request;
use Redirect;
use DB;

class ProductColorController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    // List of Product Color
    public function index()
    {
        $ProdColorData = PColor::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('INV.ProductColor.index', compact('ProdColorData'));
    }

    //Add and store Product Color
    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'color_name' => 'required',
            ]);
            $RequestData = $request->all();

            $isInsert = PColor::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted Product Color Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('inv/color')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Product Color',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            $ProdGroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('INV.ProductColor.add',compact('ProdGroupData'));
        }
    }

    //Edit Product Color
    public function edit(Request $request, $id = null)
    {

        $ProdColorData = PColor::where('id', $id)->first();
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'color_name' => 'required',
            ]);

            $Data = $request->all();

            $isUpdate = $ProdColorData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Product Color Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('inv/color')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to update data in Product Color',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            $ProdGroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('INV.ProductColor.edit', compact('ProdColorData','ProdGroupData'));
        }
    }

    //View Product Color
    public function view($id = null)
    {
        $ProdColorData = PColor::where('id', $id)->first();
        return view('INV.ProductColor.view', compact('ProdColorData'));
    }

    //Soft Delete Product Color
    public function delete($id = null)
    {

        $ProdColorData = PColor::where('id', $id)->first();
        $ProdColorData->is_delete = 1;

        $delete = $ProdColorData->save();

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

    //Parmanent Delete Product Color
    public function destroy($id = null)
    {
        $ProdColorData = PColor::where('id', $id)->first();
        $delete = $ProdColorData->delete();

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

    //Publish/Unpublish Product Color
    public function isActive($id = null)
    {
        $ProdColorData = PColor::where('id', $id)->first();
        if ($ProdColorData->is_active == 1) {
            $ProdColorData->is_active = 0;
        } else {
            $ProdColorData->is_active = 1;
        }
        $Status = $ProdColorData->save();

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
