<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\GNL\Company;
use App\Model\GNL\SpecialHoliday;
use App\Services\RoleService as Role;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;

class SpecialHolidayController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {

        if ($request->ajax()) {
            $columns = array(
                0 => 'hr_holidays_special.id',
                1 => 'hr_holidays_special.sh_title',
                2 => 'hr_holidays_special.sh_app_for',
                3 => 'hr_holidays_special.sh_date_from',
                4 => 'hr_holidays_special.sh_date_to',
                5 => 'hr_holidays_special.sh_description',
            );
            // Datatable Pagination Variable
            // $totalData = SpecialHoliday::where('hr_holidays_special.is_delete', '=', 0)->count();
            // $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');

            // Query
            $SpecialHolidayData = SpecialHoliday::where('hr_holidays_special.is_delete', ' =', 0)
                ->select('hr_holidays_special.*',
                    'gnl_companies.comp_name')
            // ->whereIn('hr_holidays_special.branch_id', HRS::getUserAccesableBranchIds())
                ->leftJoin('gnl_companies', 'hr_holidays_special.company_id', '=', 'gnl_companies.id')
                ->where(function ($SpecialHolidayData) use ($search) {
                    if (!empty($search)) {
                        $SpecialHolidayData->where('hr_holidays_special.sh_title', 'LIKE', "%{$search}%")
                            ->orWhere('hr_holidays_special.sh_app_for', 'LIKE', "%{$search}%")
                            ->orWhere('hr_holidays_special.sh_date_from', 'LIKE', "%{$search}%")
                            ->orWhere('hr_holidays_special.sh_date_to', 'LIKE', "%{$search}%")
                            ->orWhere('hr_holidays_special.sh_description', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_companies.comp_name', 'LIKE', "%{$search}%");
                    }
                })
            // ->offset($start)
            // ->limit($limit)
                ->orderBy($order, $dir)
                ->orderBy('hr_holidays_special.id', 'DESC');
            // ->get();
            $tempQueryData = clone $SpecialHolidayData;
            $SpecialHolidayData = $SpecialHolidayData->offset($start)->limit($limit)->get();

            $totalData = SpecialHoliday::where([['hr_holidays_special.is_delete', 0],
                ['hr_holidays_special.is_active', 1]])
                ->count();
            $totalFiltered = $totalData;

            if (!empty($search)) {
                $totalFiltered = $tempQueryData->count();
            }

            $DataSet = array();
            $i = $start;

            foreach ($SpecialHolidayData as $Row) {
                $TempSet = array();
                $TempSet = [
                    // 'id' => $Row->id,
                    'sid' => ++$i,
                    'sh_title' => $Row->sh_title,
                    'sh_app_for' => $Row->sh_app_for,
                    'sh_date_from' => date('d-m-Y', strtotime($Row->sh_date_from)),
                    'sh_date_to' => date('d-m-Y', strtotime($Row->sh_date_to)),
                    'sh_description' => $Row->sh_description,
                    'comp_name' => $Row->comp_name,
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->id),
                ];

                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $DataSet,
            );

            return response()->json($json_data);
        }
        return view('GNL.SpecialHoliday.index');
    }

    // Add and Store Special Holiday
    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                // 'company_id' => 'required',
                // 'branch_id' => 'required',
                'sh_app_for' => 'required',
                'sh_date_from' => 'required',
                'sh_date_to' => 'required ',
                'sh_title' => 'required',
            ]);

            $RequestData = $request->all();
            // dd($RequestData);

            $RequestData['sh_date_from'] = new DateTime($RequestData['sh_date_from']);
            $RequestData['sh_date_from'] = $RequestData['sh_date_from']->format('Y-m-d');

            $RequestData['sh_date_to'] = new DateTime($RequestData['sh_date_to']);
            $RequestData['sh_date_to'] = $RequestData['sh_date_to']->format('Y-m-d');

            $isInsert = SpecialHoliday::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted New Special Holiday Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/specialholiday')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Special Holiday',
                    'alert-type' => 'error',
                );
                return Redirect::to('gnl/specialholiday')->with($notification);
            }
        } else {

            $CompanyData = Company::where('is_delete', 0)->orderBy('id', 'DESC')->get();

            $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
                ->orderBy('branch_code', 'ASC')
                ->get();

            return view('GNL.SpecialHoliday.add', compact('CompanyData', 'BranchData'));
        }
    }

    // Edit Special Holiday
    public function edit(Request $request, $id = null)
    {

        $SpecialHolidayData = SpecialHoliday::where('id', $id)->first();

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'sh_app_for' => 'required',
                'sh_date_from' => 'required',
                'sh_date_to' => 'required ',
            ]);

            $Data = $request->all();

            $Data['sh_date_from'] = new DateTime($Data['sh_date_from']);
            $Data['sh_date_from'] = $Data['sh_date_from']->format('Y-m-d');

            $Data['sh_date_to'] = new DateTime($Data['sh_date_to']);
            $Data['sh_date_to'] = $Data['sh_date_to']->format('Y-m-d');

            $isUpdate = $SpecialHolidayData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Special Holiday Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/specialholiday')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Special Holiday',
                    'alert-type' => 'error',
                );
                return Redirect::to('gnl/specialholiday')->with($notification);
            }
        } else {

            $CompanyData = Company::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
                ->orderBy('branch_code', 'ASC')
                ->get();
            return view('GNL.SpecialHoliday.edit', compact('SpecialHolidayData', 'CompanyData', 'BranchData'));
        }
    }

    //View Special Holiday
    public function view($id = null)
    {

        $SpecialHolidayData = SpecialHoliday::where('id', $id)->first();
        $CompanyData = Company::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        $BranchData = Branch::where(['is_delete' => 0, 'is_approve' => 1])
            ->orderBy('branch_code', 'ASC')
            ->get();
        return view('GNL.SpecialHoliday.view', compact('SpecialHolidayData', 'CompanyData', 'BranchData'));
    }

    // Soft Delete Special Holiday
    public function delete($id = null)
    {

        $SpecialHolidayData = SpecialHoliday::where('id', $id)->first();
        $SpecialHolidayData->is_delete = 1;

        $delete = $SpecialHolidayData->save();

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

    // Parmanent Delete Special Holiday
    public function destroy($id = null)
    {
        $SpecialHolidayData = SpecialHoliday::where('id', $id)->first();
        $delete = $SpecialHolidayData->delete();

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

    // Publish/Unpublish Special Holiday
    public function isactive($id = null)
    {
        $SpecialHolidayData = SpecialHoliday::where('id', $id)->first();
        if ($SpecialHolidayData->is_active == 1) {
            $SpecialHolidayData->is_active = 0;
        } else {
            $SpecialHolidayData->is_active = 1;
        }
        $Status = $SpecialHolidayData->save();

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

            $startDateFrom = new DateTime($request->get('startDateFrom'));
            $startDateTo = new DateTime($request->get('startDateTo'));

            if (DB::getSchemaBuilder()->hasTable('pos_day_end')) {
                $queryData1 = DB::table('pos_day_end')
                    ->where([['is_active', 0], ['is_delete', 0]])
                    ->whereBetween('branch_date', [$startDateFrom->format('Y-m-d'), $startDateTo->format('Y-m-d')])
                    ->count();

                if ($queryData1 > 0) {
                    return response()->json(array("exists" => 1, "Table" => 'DayEnd'));
                }
            }

            if (DB::getSchemaBuilder()->hasTable('pos_day_end')) {
                $queryData2 = DB::table('acc_day_end')
                    ->where([['is_active', 0], ['is_delete', 0]])
                    ->whereBetween('branch_date', [$startDateFrom->format('Y-m-d'), $startDateTo->format('Y-m-d')])
                    ->count();

                if ($queryData2 > 0) {
                    return response()->json(array("exists" => 1, "Table" => 'DayEnd'));
                }
            }

            $queryData3 = null;
            $queryData5 = null;
            // // // MFN Day End, HR Day End, INV er day end
            // ->whereBetween('gh_date', [$startDateFrom->format('d-m'), $startDateTo->format('d-m')])
            if (DB::getSchemaBuilder()->hasTable('hr_holidays_govt')) {
                $queryData3 = DB::table('hr_holidays_govt')
                    ->where([['is_active', 1], ['is_delete', 0]])
                    ->get();

            }

            if (DB::getSchemaBuilder()->hasTable('hr_holidays_comp')) {
                $queryData5 = DB::table('hr_holidays_comp')
                    ->where([['is_active', 1], ['is_delete', 0]])
                    ->where('ch_eff_date', '<=', $startDateFrom->format('Y-m-d'))
                //->whereBetween('ch_eff_date', [$startDateFrom->format('Y-m-d'), $startDateTo->format('Y-m-d')])
                    ->first();
            }

            $holidaybar = array();
            if (!empty($queryData5)) {
                $holidaybar = explode(',', $queryData5->ch_day);

            }

            $tempFromDate = clone $startDateFrom;
            $tempToDate = clone $startDateTo;

            while ($tempFromDate <= $tempToDate) {

                $tempdate = date_format($tempFromDate, 'l');
                $tempDayMonth = date_format($tempFromDate, 'd-m');

                if (!empty($holidaybar) && in_array($tempdate, $holidaybar)) {
                    // company holiday day check if exist return
                    return response()->json(array("exists" => 1, "Table" => 'Holiday'));
                    break;
                }

                if (!empty($queryData3) && $queryData3->where('gh_date', $tempDayMonth)->count() > 0) {
                    // govt holiday check if exist return
                    return response()->json(array("exists" => 1, "Table" => 'Holiday'));
                    break;
                }

                if (DB::getSchemaBuilder()->hasTable('hr_holidays_special')) {
                    $queryData4 = DB::table('hr_holidays_special')
                        ->where([['is_active', 1], ['is_delete', 0]])
                        ->where([['sh_date_from', '<=', $tempFromDate->format('Y-m-d')], ['sh_date_to', '>=', $tempFromDate->format('Y-m-d')]])
                        ->count();

                    if ($queryData4 > 0) {
                        return response()->json(array("exists" => 1, "Table" => 'Holiday'));
                        break;
                    }
                }

                $tempFromDate->modify('+1 day');

            }
        }
    }

}
