<?php

namespace App\Http\Controllers\acc;

use App\Http\Controllers\Controller;
use App\Model\Acc\MisConfig;
use App\Model\Acc\MisType;
use App\Model\POS\Supplier;
use Illuminate\Http\Request;
use Redirect;

class MisConfigController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {

        } else {
            $data = MisConfig::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('ACC.MisConfig.index', compact('data'));
        }
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'mis_name' => 'required',
            ]);

            $RequestData = $request->all();
            //dd($RequestData);

            $isInsert = MisConfig::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted MIS Configaration Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('acc/mis_config')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in MIS Configaration',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $misType = MisType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $SupplierData = Supplier::where(['is_delete' => 0, 'is_active' => 1])->get();
            return view('ACC.MisConfig.add', compact('misType', 'SupplierData'));
        }
    }

    public function edit(Request $request, $id = null)
    {

        $data = MisConfig::where('id', $id)->first();

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'mis_name' => 'required',
            ]);

            $RequestData = $request->all();

            $isUpdate = $data->update($RequestData);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated MIS Configaration Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('acc/mis_config')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in MIS Configaration',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            $vdata = MisConfig::where('id', $id)->first();
            $misType = MisType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $SupplierData = Supplier::where(['is_delete' => 0, 'is_active' => 1])->get();
            //$vdata = MisConfig::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('ACC.MisConfig.edit', compact('vdata', 'misType', 'SupplierData'));
        }
    }

    public function view($id = null)
    {
        $vdata = MisConfig::where('id', $id)->first();
        return view('ACC.MisConfig.view', compact('vdata'));
    }

    public function delete($id = null)
    {

        $MisConfig = MisConfig::where('id', $id)->first();
        $MisConfig->is_delete = 1;
        $delete = $MisConfig->save();

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
        $MisConfig = MisConfig::where('id', $id)->first();
        if ($MisConfig->is_active == 1) {
            $MisConfig->is_active = 0;
            # code...
        } else {
            $MisConfig->is_active = 1;
        }

        $Status = $MisConfig->save();

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
