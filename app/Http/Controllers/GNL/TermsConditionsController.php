<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use App\Model\GNL\Company;
use App\Model\GNL\TermsConditions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Redirect;
use App\Services\RoleService as Role;

class TermsConditionsController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
      $TCData = TermsConditions::where('is_delete', 0)->orderBy('id', 'DESC')->get();
      return view('GNL.TermsConditions.index', compact('TCData'));
    }

    public function add(Request $request)
    {

      if ($request->isMethod('post')) {
          $validateData = $request->validate([
              'tc_name' => 'required',
          ]);
          $RequestData = $request->all();

          $isInsert = TermsConditions::create($RequestData);

          if ($isInsert) {
              $notification = array(
                  'message' => 'Successfully Inserted Data',
                  'alert-type' => 'success',
              );
              return Redirect::to('gnl/terms_conditions')->with($notification);
          } else {
              $notification = array(
                  'message' => 'Unsuccessful to insert data',
                  'alert-type' => 'error',
              );
              return redirect()->back()->with($notification);
          }
      } else {
            return view('GNL.TermsConditions.add');
        }
    }

    public function edit(Request $request, $id = null)
    {

              $TCData = TermsConditions::where('id', $id)->first();
              if ($request->isMethod('post')) {
                  $validateData = $request->validate([
                    'tc_name' => 'required',
                  ]);

                  $Data = $request->all();

                  $isUpdate = $TCData->update($Data);

                  if ($isUpdate) {
                      $notification = array(
                          'message' => 'Successfully Updated  Data',
                          'alert-type' => 'success',
                      );
                      return Redirect::to('gnl/terms_conditions')->with($notification);
                  } else {
                      $notification = array(
                          'message' => 'Unsuccessful to update data',
                          'alert-type' => 'error',
                      );
                      return redirect()->back()->with($notification);
                  }
              } else {
            return view('GNL.TermsConditions.edit', compact('TCData'));
        }
    }

    public function view($id = null)
    {
        $TCData = TermsConditions::where('id', $id)->first();
        return view('GNL.TermsConditions.view', compact('TCData'));
    }
    public function delete($id = null)
    {

        $TCData = TermsConditions::where('id', $id)->first();
        $TCData->is_delete = 1;

        $delete = $TCData->save();

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
        $TCData = TermsConditions::where('id', $id)->first();
        $delete = $TCData->delete();

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
        $TCData = TermsConditions::where('id', $id)->first();
        if ($TCData->is_active == 1) {
            $TCData->is_active = 0;
        } else {
            $TCData->is_active = 1;
        }

        $Status = $TCData->save();

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
