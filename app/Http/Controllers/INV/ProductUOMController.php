<?php
namespace App\Http\Controllers\INV;

use App\Http\Controllers\Controller;
use App\Model\INV\PUOM;
use Illuminate\Http\Request;
use Redirect;

class ProductUOMController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    // List Of Product UOM
    public function index(Request $request)
    {
        $ProdUOMData = PUOM::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('INV.ProductUOM.index', compact('ProdUOMData'));
    }

    //Add and Store Product UOM
    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'uom_name' => 'required',
            ]);
            $RequestData = $request->all();

            $isInsert = PUOM::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted Product uom Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('inv/uom')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Product uom',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            // $ProdGroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();

            return view('INV.ProductUOM.add');
        }
    }

    // Edit Product UOM
    public function edit(Request $request, $id = null)
    {

        $ProdUOMData = PUOM::where('id', $id)->first();
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'uom_name' => 'required',
            ]);

            $Data = $request->all();

            $isUpdate = $ProdUOMData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Product uom Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('inv/uom')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to update data in Product uom',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            return view('INV.ProductUOM.edit', compact('ProdUOMData'));
        }
    }

    // View Product UOM
    public function view($id = null)
    {
        $ProdUOMData = PUOM::where('id', $id)->first();
        return view('INV.ProductUOM.view', compact('ProdUOMData'));
    }

    // Delete Product UOM
    public function delete($id = null)
    {

        $ProdUOMData = PUOM::where('id', $id)->first();
        $ProdUOMData->is_delete = 1;

        $delete = $ProdUOMData->save();

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

    //Parmanent Delete Product UOM
    public function destroy($id = null)
    {
        $ProdUOMData = PUOM::where('id', $id)->first();
        $delete = $ProdUOMData->delete();

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

    // Publish/ Unpublish Product UOM
    public function isActive($id = null)
    {
        $ProdUOMData = PUOM::where('id', $id)->first();
        if ($ProdUOMData->is_active == 1) {
            $ProdUOMData->is_active = 0;
        } else {
            $ProdUOMData->is_active = 1;
        }

        $Status = $ProdUOMData->save();

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
