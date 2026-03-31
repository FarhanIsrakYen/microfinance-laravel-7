<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use App\Model\GNL\FiscalYear;
use DateTime;
use Illuminate\Http\Request;
use Redirect;

class FiscalYearController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index()
    {
        $FiscalYear = FiscalYear::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('GNL.FiscalYear.index', compact('FiscalYear'));
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
//                'company_id' => 'required',
                'fy_name' => 'required',
                'fy_start_date' => 'required',
                //'fy_end_date' => 'required'
            ]);

            $RequestData = $request->all();
            $StartDate = new DateTime($RequestData['fy_start_date']);
            $RequestData['fy_start_date'] = $StartDate->format('Y-m-d');
            $EndDate = $StartDate;
            $EndDate = $EndDate->modify('+1 year, -1 Day');
            $RequestData['fy_end_date'] = $EndDate->format('Y-m-d');

            $isInsert = FiscalYear::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted New Fiscal Year',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/fiscal_year')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Fiscal Year',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            return view('GNL.FiscalYear.add');
        }
    }

    public function edit(Request $request, $id = null)
    {

        $FiscalYear = FiscalYear::where('id', $id)->first();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
//                'company_id' => 'required',
                'fy_name' => 'required',
                'fy_start_date' => 'required',
            ]);

            $RequestData = $request->all();
            $StartDate = new DateTime($RequestData['fy_start_date']);
            $RequestData['fy_start_date'] = $StartDate->format('Y-m-d');
            $EndDate = $StartDate;
            $EndDate = $EndDate->modify('+1 year, -1 Day');
            $RequestData['fy_end_date'] = $EndDate->format('Y-m-d');

            $isUpdate = $FiscalYear->update($RequestData);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Fiscal Year',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/fiscal_year')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Fiscal Year',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $FiscalYear = FiscalYear::where('id', $id)->first();

            return view('GNL.FiscalYear.edit', compact('FiscalYear'));
        }
    }

    public function view($id = null)
    {
        $FiscalYear = FiscalYear::where('id', $id)->first();
        return view('GNL.FiscalYear.view', compact('FiscalYear'));
    }

    public function delete($id = null)
    {
        $FiscalYear = FiscalYear::where('id', $id)->first();
        $FiscalYear->is_delete = 1;
        $delete = $FiscalYear->save();

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

    public function isactive($id = null)
    {
        $FiscalYear = FiscalYear::where('id', $id)->first();

        if ($FiscalYear->is_active == 1) {
            $FiscalYear->is_active = 0;
        } else {
            $FiscalYear->is_active = 1;
        }

        $Status = $FiscalYear->save();

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
