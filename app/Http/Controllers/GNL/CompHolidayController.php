<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use App\Model\GNL\CompanyHoliday;
use App\Services\CommonService as Common;
use DateTime;
use Illuminate\Http\Request;
use Redirect;
use DB;
class CompHolidayController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    // List of Company Holiday
    public function index()
    {
        $CompHolidayData = CompanyHoliday::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('GNL.CompanyHoliday.index', compact('CompHolidayData'));
    }

    // Add and Store Company Holiday
    public function add(Request $request)
    {

        $days = Common::getWeekdayName();
        // $days = implode(',', $days);

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'company_id' => 'required',
                'ch_title' => 'required',
                'ch_eff_date' => 'required',
            ]);

            $RequestData = $request->all();
            $RequestData['ch_eff_date'] = new DateTime($RequestData['ch_eff_date']);
            $RequestData['ch_eff_date'] = $RequestData['ch_eff_date']->format('Y-m-d');

            $RequestData['ch_day'] = implode(",", $RequestData['ch_day']);

            $isInsert = CompanyHoliday::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted Company Holiday Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/compholiday')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Company Holiday',
                    'alert-type' => 'error',
                );
                return Redirect::to('gnl/compholiday')->with($notification);
            }
        } else {

            return view('GNL.CompanyHoliday.add', compact('days'));
        }
    }

    // Edit Company Holiday
    public function edit(Request $request, $id = null)
    {

        $days = Common::getWeekdayName();
        $CompHolidayData = CompanyHoliday::where('id', $id)->first();

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'company_id' => 'required',
                'ch_title' => 'required',
                'ch_day' => 'required',
                'ch_eff_date' => 'required',
            ]);

            $Data = $request->all();
            $Data['ch_eff_date'] = new DateTime($Data['ch_eff_date']);
            $Data['ch_eff_date'] = $Data['ch_eff_date']->format('Y-m-d');

            $Data['ch_day'] = implode(",", $Data['ch_day']);

            $isUpdate = $CompHolidayData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Company holiday Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/compholiday')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Company holiday',
                    'alert-type' => 'error',
                );
                return Redirect::to('gnl/compholiday')->with($notification);
            }
        } else {

            return view('GNL.CompanyHoliday.edit', compact('CompHolidayData', 'days'));
        }
    }

    //View Company Holiday
    public function view($id = null)
    {

        $days = Common::getWeekdayName();
        $CompHolidayData = CompanyHoliday::where('id', $id)->first();

        return view('GNL.CompanyHoliday.view', compact('CompHolidayData', 'days'));
    }

    // Soft Delete Company Holiday
    public function delete($id = null)
    {

        $CompHolidayData = CompanyHoliday::where('id', $id)->first();
        $CompHolidayData->is_delete = 1;

        $delete = $CompHolidayData->save();

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

    // Parmanent Delete Company Holiday
    public function destroy($id = null)
    {
        $CompHolidayData = CompanyHoliday::where('id', $id)->first();
        $delete = $CompHolidayData->delete();

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

    // Publish/Unpublish Company Holiday
    public function isactive($id = null)
    {
        $CompHolidayData = CompanyHoliday::where('id', $id)->first();

        if ($CompHolidayData->is_active == 1) {

            $CompHolidayData->is_active = 0;
            # code...
        } else {

            $CompHolidayData->is_active = 1;
        }

        $Status = $CompHolidayData->save();

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
    public function CheckDayEnd(Request $request)
    {

        if ($request->ajax()) {

            $selectedDate = (!empty($request->get('startDateFrom'))) ? $request->get('startDateFrom') : null;

            if(empty($selectedDate)){
                return response()->json(array("exists" => 1, "Table" => 'emptydata'));
            }

            $selectedDate = (new DateTime($request->get('startDateFrom')))->format('Y-m-d');
           
           
            if(DB::getSchemaBuilder()->hasTable('pos_day_end')){
                $queryData1 = DB::table('pos_day_end')
                ->where([['is_active', 0], ['is_delete', 0]])
                ->where([['branch_date', '>=', $selectedDate]])
                ->count();

                if ($queryData1 > 0) {
                    return response()->json(array("exists" => 1, "Table" => 'DayEnd'));
                }

            }

            if(DB::getSchemaBuilder()->hasTable('acc_day_end')){

                $queryData2 = DB::table('acc_day_end')
                ->where([['is_active', 0], ['is_delete', 0]])
                ->where('branch_date','>=',$selectedDate)
                ->count();

                if ($queryData2 > 0) {
                    return response()->json(array("exists" => 1, "Table" => 'DayEnd'));
                }
            }
           
           
        }
    }


}
