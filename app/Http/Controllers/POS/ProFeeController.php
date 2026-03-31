<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\POS\PProcessingFee;
use Illuminate\Http\Request;

class ProFeeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
        $proFeeData = PProcessingFee::where([['is_active', 1], ['is_delete', 0]])->get();
        return view('POS.ProcessingFee.index', compact('proFeeData'));
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {

            $request->validate([
                'company_id' => 'required',
                'amount' => 'required|numeric',
            ]);

            $isInsert = PProcessingFee::create($request->all());

            if ($isInsert) {

                $notification = array(
                    'message' => 'Successfully Inserted',
                    'alert-type' => 'success',
                );

                return redirect('pos/proFee')->with($notification);
            } else {

                $notification = array(
                    'message' => 'Unsuccessful Insert',
                    'alert-type' => 'error',
                );

                return Redirect()->back()->with($notification);
            }
        } else {
            return view('POS.ProcessingFee.add');
        }
    }

    public function edit(Request $request, $id = null)
    {
        $proFeeData = PProcessingFee::where('id', $id)->first();

        if ($request->isMethod('post')) {

            $request->validate([
                'company_id' => 'required',
                'amount' => 'required',
            ]);

            $isUpdate = $proFeeData->update($request->all());

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated',
                    'alert-type' => 'success',
                );

                return redirect('pos/proFee')->with($notification);

            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            return view('POS.ProcessingFee.edit', compact('proFeeData'));
        }
    }

    public function delete($id = null)
    {
        $proFeeData = PProcessingFee::where('id', $id)->first();
        if ($proFeeData->is_delete == 0) {

            $proFeeData->is_delete = 1;
            $isSuccess = $proFeeData->update();

            if ($isSuccess) {
                $notification = array(
                    'message' => 'Successfully Deleted',
                    'alert-type' => 'success',
                );
                return redirect()->back()->with($notification);
            }
        }
    }

    public function isActive($id = null)
    {
        $proFeeData = PProcessingFee::where('id', $id)->first();

        if ($proFeeData->is_active == 1) {
            $proFeeData->is_active = 0;
        } else {
            $proFeeData->is_active = 1;
        }

        $proFeeData->update();
        $notification = array(
            'message' => 'Activation is changed',
            'alert-type' => 'success',
        );
        return redirect()->back()->with($notification);
    }
}
