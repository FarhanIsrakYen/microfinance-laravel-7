<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\POS\PGroup;
use App\Model\POS\PSubCategory;
use Illuminate\Http\Request;
use Redirect;

class PSubCategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index()
    {
        $PSubCategoryData = PSubCategory::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('POS.SubCategory.index', compact('PSubCategoryData'));
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'prod_group_id' => 'required',
                'prod_cat_id' => 'required',
                'sub_cat_name' => 'required',
            ]);

            $RequestData = $request->all();

            $isInsert = PSubCategory::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted Product Sub Category Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/subcategory')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert Sub Category data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $PgroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();

            return view('POS.SubCategory.add', compact('PgroupData'));
        }
    }

    public function edit(Request $request, $id = null)
    {

        $PSubCategoryData = PSubCategory::where('id', $id)->first();
        // dd($PSubCategoryData);
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                //'company_id' => 'required',
                'prod_group_id' => 'required',
                'prod_cat_id' => 'required',
                'sub_cat_name' => 'required',
            ]);

            $Data = $request->all();

            $isUpdate = $PSubCategoryData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Product Sub Category Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/subcategory')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update Product Sub Category data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            //$PSubCategoryData = PSubCategory::where('id', $id)->first();
            $PgroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            //dd( $PSubCategoryData);

            return view('POS.SubCategory.edit', compact('PSubCategoryData', 'PgroupData'));

        }

    }

    public function view($id = null)
    {
        $PGroupData = PSubCategory::where('id', $id)->first();
        return view('POS.SubCategory.view', compact('PGroupData'));

    }

    public function delete($id = null)
    {

        $SubData = PSubCategory::where('id', $id)->first();
        $SubData->is_delete = 1;
        $delete = $SubData->save();

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
        $SubData = PSubCategory::where('id', $id)->first();
        if ($SubData->is_active == 1) {
            $SubData->is_active = 0;
        } else {
            $SubData->is_active = 1;
        }
        $Status = $SubData->save();

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
