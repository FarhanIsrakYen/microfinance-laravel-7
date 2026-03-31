<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\POS\PCategory;
use App\Model\POS\PGroup;
use Illuminate\Http\Request;
use Redirect;

class PCategoryController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index()
    {
        $PCategoryData = PCategory::where('pos_p_categories.is_delete', 0)->orderBy('pos_p_categories.id', 'DESC')->get();
        return view('POS.Category.index', compact('PCategoryData'));
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'cat_name' => 'required',
            ]);

            $RequestData = $request->all();

            $isInsert = PCategory::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted Product Category Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/category')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert Product category data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        }

        $PGroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('POS.Category.add', compact('PGroupData'));
    }

    public function edit(Request $request, $id = null)
    {

        $PCategoryData = PCategory::where('id', $id)->first();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'cat_name' => 'required',
            ]);

            $Data = $request->all();

            $isUpdate = $PCategoryData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Product category Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/category')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update Product category data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        }

        $PGroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('POS.Category.edit', compact('PCategoryData', 'PGroupData'));
    }

    public function view($id = null)
    {
        $PCategoryData = PCategory::where('id', $id)->first();
        return view('POS.Category.view', compact('PCategoryData'));
    }

    public function delete($id = null)
    {

        $PCategoryData = PCategory::where('id', $id)->first();
        $PCategoryData->is_delete = 1;
        $delete = $PCategoryData->save();

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
        $PCategoryData = PCategory::where('id', $id)->first();

        if ($PCategoryData->is_active == 1) {
            $PCategoryData->is_active = 0;
        } else {
            $PCategoryData->is_active = 1;
        }

        $Status = $PCategoryData->save();

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
