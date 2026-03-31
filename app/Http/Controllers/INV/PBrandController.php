<?php

namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use App\Model\INV\PBrand;
use App\Model\INV\PGroup;
use Illuminate\Http\Request;
use Redirect;

class PBrandController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
        $PBrandData = PBrand::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('INV.Brand.index', compact('PBrandData'));
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'brand_name' => 'required',
            ]);

            $RequestData = $request->all();
            // dd($RequestData);

            $isInsert = PBrand::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted Product Brand Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('inv/brand')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert Product Brand data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $PgroupData = Pgroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('INV.Brand.add');
        }
    }

    public function edit(Request $request, $id = null)
    {

        $PbrandData = PBrand::where('id', $id)->first();
        // dd($PbrandData);
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'brand_name' => 'required',
            ]);

            $Data = $request->all();

            $isUpdate = $PbrandData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Product Brand Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('inv/brand')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update Product Brand data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            # code...
            return view('INV.Brand.edit', compact('PbrandData'));
        }
        // $PgroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();

    }

    public function view($id = null)
    {
        $PGroupData = PBrand::where('id', $id)->first();
        return view('INV.Brand.view', compact('PGroupData'));
    }

    public function delete($id = null)
    {
        $PGroupData = PBrand::where('id', $id)->first();
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
        $PGroupData = PBrand::where('id', $id)->first();
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
