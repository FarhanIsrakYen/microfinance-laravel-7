<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use App\Model\INV\PBrand;
use App\Model\INV\PGroup;
use App\Model\INV\PModel;
use Illuminate\Http\Request;
use Redirect;

class PModelController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
        $PModelData = PModel::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('INV.Model.index', compact('PModelData'));
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'prod_group_id' => 'required',
                'prod_cat_id' => 'required',
                'prod_sub_cat_id' => 'required',
                // 'prod_brand_id' => 'required',
                'model_name' => 'required',
            ]);

            $RequestData = $request->all();

            $isInsert = PModel::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted Product Model Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('inv/model')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert Product Model data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $PgroupData = Pgroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $BrandData = PBrand::where('is_delete', 0)->orderBy('id', 'DESC')->get();

            return view('INV.Model.add', compact('PgroupData', 'BrandData'));
        }
    }

    public function edit(Request $request, $id = null)
    {

        $PModelData = PModel::where('id', $id)->first();
        // dd($PbrandData);
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'prod_group_id' => 'required',
                'prod_cat_id' => 'required',
                'prod_sub_cat_id' => 'required',
                // 'prod_brand_id' => 'required',
                'model_name' => 'required',
            ]);

            $Data = $request->all();

            $isUpdate = $PModelData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Product Model Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('inv/model')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update Product Model data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);

            }
        }
        $PgroupData = Pgroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        $BrandData = PBrand::where('is_delete', 0)->orderBy('id', 'DESC')->get();

        return view('INV.Model.edit', compact('PgroupData', 'PModelData', 'BrandData'));
    }

    public function view($id = null)
    {
        $PGroupData = PModel::where('id', $id)->first();

        return view('INV.Model.view', compact('PGroupData'));
    }

    public function delete($id = null)
    {
        $PGroupData = PModel::where('id', $id)->first();
        $PGroupData->is_delete = 1;
        $delete = $PGroupData->save();

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
        $PGroupData = PModel::where('id', $id)->first();

        if ($PGroupData->is_active == 1) {
            $PGroupData->is_active = 0;
        } else {
            $PGroupData->is_active = 1;
        }

        $Status = $PGroupData->save();

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
