<?php

namespace App\Http\Controllers\HR;

use App\Http\Controllers\Controller;
use App\Model\HR\GovtHoliday;
use DateTime;
use Illuminate\Http\Request;
use Redirect;

class GovtHolidayController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    // List of Govt Holiday
    public function index()
    {
        $GovtHolidayData = GovtHoliday::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('HR.GovtHoliday.index', compact('GovtHolidayData'));
    }

    // Add and Store Govt Holiday
    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'gh_title' => 'required',
                'gh_date' => 'required ',
                'efft_start_date' => 'required ',
            ]);

            $RequestData = $request->all();

            $sDate = new DateTime($RequestData['efft_start_date']);
            $sDate = $sDate->format('Y-m-d');

            $RequestData['efft_start_date'] = $sDate;

            $eDate = new DateTime($RequestData['efft_end_date']);
            $eDate = $eDate->format('Y-m-d');

            $RequestData['efft_end_date'] = $eDate;

            $isInsert = GovtHoliday::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted',
                    'alert-type' => 'success',
                );
                return Redirect::to('hr/govtholiday')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data',
                    'alert-type' => 'error',
                );
                return Redirect::to('hr/govtholiday')->with($notification);
            }
        } else {

            return view('HR.GovtHoliday.add');
        }
    }

    // Edit GovtHoliday
    public function edit(Request $request, $id = null)
    {

        $GovtHolidayData = GovtHoliday::where('id', $id)->first();

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'gh_title' => 'required',
                'gh_date' => 'required ',
                'efft_start_date' => 'required ',
            ]);

            $Data = $request->all();

            $sDate = new DateTime($Data['efft_start_date']);
            $sDate = $sDate->format('Y-m-d');

            $Data['efft_start_date'] = $sDate;

            $eDate = new DateTime($Data['efft_end_date']);
            $eDate = $eDate->format('Y-m-d');

            $Data['efft_end_date'] = $eDate;

            $isUpdate = $GovtHolidayData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated GovtHoliday Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('hr/govtholiday')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in GovtHoliday',
                    'alert-type' => 'error',
                );
                return Redirect::to('hr/govtholiday')->with($notification);
            }
        } else {

            return view('HR.GovtHoliday.edit', compact('GovtHolidayData'));
        }
    }

    //View GovtHoliday
    public function view($id = null)
    {

        $GovtHolidayData = GovtHoliday::where('id', $id)->first();
        
        return view('HR.GovtHoliday.view', compact('GovtHolidayData'));
    }

    // Soft Delete GovtHoliday
    public function delete($id = null)
    {

        $GovtHolidayData = GovtHoliday::where('id', $id)->first();
        $GovtHolidayData->is_delete = 1;

        $delete = $GovtHolidayData->save();

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

    // Parmanent Delete GovtHoliday
    public function destroy($id = null)
    {
        $GovtHolidayData = GovtHoliday::where('id', $id)->first();
        $delete = $GovtHolidayData->delete();

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

    // Publish/Unpublish GovtHoliday
    public function isactive($id = null)
    {
        $GovtHolidayData = GovtHoliday::where('id', $id)->first();
        if ($GovtHolidayData->is_active == 1) {
            $GovtHolidayData->is_active = 0;
        } else {
            $GovtHolidayData->is_active = 1;
        }
        $Status = $GovtHolidayData->save();

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
