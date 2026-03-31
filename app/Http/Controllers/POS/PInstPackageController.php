<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\POS\PInstallmentPackage;
use Illuminate\Http\Request;
use Redirect;

class PInstPackageController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    // List of Installment Package
    public function index(Request $request)
    {
        $ProdInstPackData = PInstallmentPackage::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('POS.PInstallPackage.index', compact('ProdInstPackData'));
    }

    // Add and Store Installment Package
    public function add(Request $request)
    {
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'prod_inst_month' => 'required',
                'prod_inst_profit' => 'required',
            ]);
            $RequestData = $request->all();

            $isInsert = PInstallmentPackage::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted Product installment Package Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/pinstallpackage')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Product installment Package',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            return view('POS.PInstallPackage.add');
        }
    }

    // Edit Installment Package
    public function edit(Request $request, $id = null)
    {
        $ProdInstPackData = PInstallmentPackage::where('id', $id)->first();
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'prod_inst_month' => 'required',
                'prod_inst_profit' => 'required',
            ]);

            $Data = $request->all();

            $isUpdate = $ProdInstPackData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Product installment Package Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/pinstallpackage')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to update data in Product installment Package',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            return view('POS.PInstallPackage.edit', compact('ProdInstPackData'));
        }
    }

    // View Installment Package
    public function view($id = null)
    {
        $ProdInstPackData = PInstallmentPackage::where('id', $id)->first();
        return view('POS.PInstallPackage.view', compact('ProdInstPackData'));
    }

    // Soft delete Installment Package
    public function delete($id = null)
    {

        $ProdInstPackData = PInstallmentPackage::where('id', $id)->first();
        $ProdInstPackData->is_delete = 1;

        $delete = $ProdInstPackData->save();

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

    // Parmanent Delete Installment Package
    public function destroy($id = null)
    {

        $ProdInstPackData = PInstallmentPackage::where('id', $id)->first();
        $delete = $ProdInstPackData->delete();

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

    // Publish/Unpublish Installment Package
    public function isActive($id = null)
    {

        $ProdInstPackData = PInstallmentPackage::where('id', $id)->first();
        if ($ProdInstPackData->is_active == 1) {
            $ProdInstPackData->is_active = 0;
        } else {
            $ProdInstPackData->is_active = 1;
        }

        $Status = $ProdInstPackData->save();

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
