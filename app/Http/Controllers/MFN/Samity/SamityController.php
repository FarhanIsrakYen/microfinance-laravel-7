<?php

namespace App\Http\Controllers\MFN\Samity;

use App\Http\Controllers\Controller;
use App\Model\MFN\Samity;
use App\Model\GNL\Branch;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use App\Services\HrService;
use App\Services\MfnService;
use App\Helpers\RoleHelper;
use App\Services\RoleService;

class SamityController extends Controller
{

    public function index(Request $req)
    {
        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        if (!$req->ajax()) {

            $branchList = Branch::where('is_delete', 0)
                ->where('id', '>', 1)
                ->whereIn('id', $accessAbleBranchIds)
                ->orderBy('branch_name')
                ->select('id', 'branch_name', 'branch_code')
                ->get();

            return view('MFN.Samity.index', compact('branchList'));
        }

        $columns = ['mfn_samity.name', 'samityCode', 'gnl_branchs.branch_code', 'mfn_working_areas.name', 'hr_employees.emp_code', 'samityType', 'samityDay', 'mfn_samity.openingDate', 'maxActiveMember'];

        $limit = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 1 : (int)$req->input('order.0.column') - 1; // default sorting would be samity code
        $order = $columns[$orderColumnIndex];
        $dir = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');
        $startDate = (empty($req->input('startDate'))) ? null : $req->input('startDate');
        $endDate = (empty($req->input('endDate'))) ? null : $req->input('endDate');
        $branchId = (empty($req->input('branchId'))) ? null : $req->input('branchId');
        $samityNameOrCode = (empty($req->input('samityNameOrCode'))) ? null : $req->input('samityNameOrCode');

        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        $samities = DB::table('mfn_samity')
            ->leftJoin('gnl_branchs', 'gnl_branchs.id', 'mfn_samity.branchId')
            ->leftJoin('mfn_working_areas', 'mfn_working_areas.id', 'mfn_samity.workingAreaId')
            ->leftJoin('hr_employees', 'hr_employees.id', 'mfn_samity.fieldOfficerEmpId')
            ->whereIn('mfn_samity.branchId', $accessAbleBranchIds)
            ->where('mfn_samity.is_delete', 0)
            ->whereIn('mfn_samity.branchId', $accessAbleBranchIds)
            ->select(DB::raw('mfn_samity.*, CONCAT(gnl_branchs.branch_code, " - ", gnl_branchs.branch_name) AS branch, mfn_working_areas.name AS workingArea, hr_employees.emp_name AS fieldOfficer'))
            ->orderBy($order, $dir);

        if ($search != null) {
            $samities->where(function ($query) use ($search) {
                $query->where('mfn_samity.name', 'LIKE', "%{$search}%")
                    ->orWhere('mfn_samity.samityCode', 'LIKE', "%{$search}%")
                    ->orWhere('mfn_samity.samityType', 'LIKE', "%{$search}%");
            });
        }
        if ($branchId != null) {
            $samities->where(function ($query) use ($branchId) {
                $query->where('mfn_samity.branchId', '=', $branchId);
            });
        }

        if ($samityNameOrCode != null) {
            $samities->where(function ($query) use ($samityNameOrCode) {
                $query->where('mfn_samity.name', 'LIKE', "%{$samityNameOrCode}%")
                    ->orWhere('mfn_samity.samityCode', 'LIKE', "%{$samityNameOrCode}%");
            });
        }

        if ($startDate != null || $endDate != null) {

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate   = Carbon::parse($endDate)->format('Y-m-d');

            $samities->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('mfn_samity.openingDate', [$startDate, $endDate]);
            });
        }

        $totalData = (clone $samities)->count();
        $samities = $samities->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;
        foreach ($samities as $key => $samity) {
            $samities[$key]->openingDate = Carbon::parse($samity->openingDate)->format('d-m-Y');
            $samities[$key]->sl          = $sl++;
            $samities[$key]->action      = RoleService::roleWiseArray($this->GlobalRole, $samities[$key]->id);
        }
        
        $data = array(
            "draw"              => intval($req->input('draw')),
            "recordsTotal"      => $totalData,
            "recordsFiltered"   => $totalData,
            'data'              => $samities,
        );

        return response()->json($data);
    }

    public function viewSamity($samityId)
    {
        $samity = DB::table('mfn_samity')->where('id', $samityId)->first();
        if (Auth::user()->branch_id != 1 &&  Auth::user()->branch_id != $samity->branchId) {
            return '';
        }

        $branch = DB::table('gnl_branchs')
            ->where('id', $samity->branchId)
            ->select('branch_name', 'branch_code')
            ->first();

        $fieldOfficerName = DB::table('hr_employees')->where('id', $samity->fieldOfficerEmpId)->first()->emp_name;

        $workingArea = DB::table('mfn_working_areas')->where('id', $samity->workingAreaId)->select('name', 'villageId')->first();
        $village = DB::table('gnl_villages')->where('id', $workingArea->villageId)->select('village_name', 'union_id')->first();
        $union = DB::table('gnl_unions')->where('id', $village->union_id)->select('union_name', 'upazila_id')->first();
        $upazila = DB::table('gnl_upazilas')->where('id', $union->upazila_id)->select('upazila_name', 'district_id')->first();
        $district = DB::table('gnl_districts')->where('id', $upazila->district_id)->select('district_name', 'division_id')->first();
        $division = DB::table('gnl_divisions')->where('id', $district->division_id)->select('division_name')->first();

        $data = array(
            'samity'            => $samity,
            'branch'            => $branch,
            'fieldOfficerName'  => $fieldOfficerName,
            'workingArea'       => $workingArea,
            'village'           => $village,
            'union'             => $union,
            'upazila'           => $upazila,
            'district'          => $district,
            'division'          => $division,
        );

        return view('MFN.Samity.view', $data);
    }

    public function addSamity(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->storeSamity($req);
        }

        $userBranchId = Auth::user()->branch_id;
        $samityCode = $this->generateSamityCodeForNewSamity($userBranchId);
        $workingAreas = DB::table('mfn_working_areas')
            ->where([
                ['is_delete', 0],
                ['branchId', $userBranchId],
            ])
            ->select('name', 'id')
            ->get();

        $filedOfficers = MfnService::getFieldOfficers($userBranchId);

        $sysDate = MfnService::systemCurrentDate($userBranchId);

        $isOpening = MfnService::isOpening(Auth::user()->branch_id);

        $weekDays = $this->getActiveWeekDays();

        $data = array(
            'samityCode'        => $samityCode,
            'workingAreas'      => $workingAreas,
            'filedOfficers'     => $filedOfficers,
            'sysDate'           => $sysDate,
            'isOpening'         => $isOpening,
            'weekDays'          => $weekDays,
        );

        return view('MFN.Samity.add', $data);
    }

    public function storeSamity(Request $req)
    {
        $passport = $this->getPassport($req, $operationType = 'store');

        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $isOpening = MfnService::isOpening(Auth::user()->branch_id);

        $samity = new Samity();
        $samity->name               = $req->samity_name;
        $samity->samityCode         = $req->samity_code;
        $samity->branchId           = Auth::user()->branch_id;
        $samity->openingDate        = Carbon::parse($req->opening_date);
        $samity->samityType         = $req->samity_type;
        $samity->samityDay          = $req->samity_day;
        $samity->maxActiveMember    = $req->max_no;
        $samity->workingAreaId      = $req->working_area;
        $samity->fieldOfficerEmpId  = $req->field_officer;
        $samity->registrationNo     = $req->reg_no;
        $samity->samityTime         = Carbon::parse($req->samity_time);
        $samity->isTransferable     = $req->transferable == null ? 0 : 1;
        $samity->latitude           = $req->latitude;
        $samity->longtitude         = $req->longitude;
        $samity->isOpening          = $isOpening ? 1 : 0;
        $samity->created_by         = Auth::user()->id;
        $samity->created_at         = Carbon::now();
        $samity->save();

        $notification = array(
            'message'       => 'Successfully Inserted',
            'alert-type'    => 'success',
        );

        return response()->json($notification);
    }

    public function editSamity(Request $req)
    {
        if ($req->isMethod('post')) {
            if($req->basicInfoEdit == 'on'){
                return $this->updateSamityBasicInfo($req);
            }
            else{
                return $this->updateSamity($req);
            }
        }

        $samity = DB::table('mfn_samity')->where('id', $req->samityId)->first();

        $workingAreas = DB::table('mfn_working_areas')
            ->where([
                ['is_delete', 0],
                ['branchId', $samity->branchId],
            ])
            ->select('name', 'id')
            ->get();

        $fieldOfficersDesignationIds = json_decode(DB::table('mfn_config')->where('title', 'fieldOfficerHrDesignationIds')->first()->content);

        $filedOfficers = DB::table('hr_employees')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
                ['branch_id', $samity->branchId]
            ])
            ->whereIn('designation_Id', $fieldOfficersDesignationIds)
            ->select(DB::raw("CONCAT(emp_code, ' - ', emp_name) AS name, id"))
            ->get();

        $samityOpeningDate = Carbon::parse($samity->openingDate)->format('d-m-Y');
        $samityTime = Carbon::parse($samity->samityTime)->format('g:iA');

        $sysDate = MfnService::systemCurrentDate($samity->branchId);
        $isOpening = MfnService::isOpening($samity->branchId) && $samity->isOpening;

        $weekDays = $this->getActiveWeekDays();

        $data = array(
            'samity'            => $samity,
            'samityTime'        => $samityTime,
            'workingAreas'      => $workingAreas,
            'filedOfficers'     => $filedOfficers,
            'samityOpeningDate' => $samityOpeningDate,
            'sysDate'           => $sysDate,
            'isOpening'         => $isOpening,
            'weekDays'          => $weekDays,
        );

        return view('MFN.Samity.edit', $data);
    }

    public function updateSamity(Request $req)
    {
        $samity = DB::table('mfn_samity')->where('id', decrypt($req->samity_id))->first();
        $passport = $this->getPassport($req, $operationType = 'update', $samity);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $samity = Samity::find($samity->id);
        $samity->name               = $req->samity_name;
        if ($samity->isOpening == 1) {
            $samity->openingDate = Carbon::parse($req->opening_date);
        }
        $samity->samityType         = $req->samity_type;
        $samity->samityDay          = $req->samity_day;
        $samity->maxActiveMember    = $req->max_no;
        $samity->workingAreaId      = $req->working_area;
        $samity->fieldOfficerEmpId  = $req->field_officer;
        $samity->registrationNo     = $req->reg_no;
        $samity->samityTime         = Carbon::parse($req->samity_time);
        $samity->isTransferable     = $req->transferable == null ? 0 : 1;
        $samity->latitude           = $req->latitude;
        $samity->longtitude         = $req->longitude;
        $samity->updated_by         = Auth::user()->id;
        $samity->updated_at         = Carbon::now();
        $samity->save();

        $notification = array(
            'message'       => 'Successfully Updated',
            'alert-type'    => 'success',
        );

        return response()->json($notification);
    }

    public function basicInfoValidation(Request $req){
        $notification = array(
            'alert-type' => 'error',
        );

        if($req->samity_name == null || $req->samity_name == ""){
            $notification['message'] = "Samity Name Can't be empty";
            return $notification;
        }

        $members = DB::table('mfn_members')->where([['samityId',decrypt($req->samity_id)],['is_delete',0]])->get();
        if(count($members) > $req->max_no){
            $notification['message'] = "This Samity already has ".count($members)." active members";
            return $notification;
        }

        return array('alert-type' => 'success');
    }

    public function updateSamityBasicInfo(Request $req)
    {
        $validation = $this->basicInfoValidation($req);
        if($validation['alert-type']=='error'){
            return response()->json($validation);
        }

        $samity = Samity::find(decrypt($req->samity_id));
        $samity->name               = $req->samity_name;
        
        $samity->maxActiveMember    = $req->max_no;
        $samity->registrationNo     = $req->reg_no;
        $samity->latitude           = $req->latitude;
        $samity->longtitude         = $req->longitude;
        $samity->updated_at         = Carbon::now();
        $samity->save();

        $notification = array(
            'message'       => 'Successfully Updated',
            'alert-type'    => 'success',
        );

        return response()->json($notification);


    }

    public function deleteSamity(Request $req)
    {
        $samity = DB::table('mfn_samity')->where('id', $req->samity_id)->first();
        $passport = $this->getPassport($req, $operationType = 'delete', $samity);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $samity = Samity::find($samity->id);
        $samity->is_delete  = 1;
        $samity->save();

        $notification = array(
            'message'       => 'Successfully Deleted',
            'alert-type'    => 'success',
        );

        return response()->json($notification);
    }

    public static function generateSamityCodeForNewSamity($branchId)
    {
        $mfnGnlConfig = json_decode(DB::table('mfn_config')->where('title', 'general')->first()->content);
        $codeSeperator = $mfnGnlConfig->codeSeperator;

        $mfnBranchConfig = json_decode(DB::table('mfn_config')->where('title', 'branch')->first()->content);
        $branchCode = DB::table('gnl_branchs')->where('id', $branchId)->first()->branch_code;
        $branchCode = str_pad($branchCode, $mfnBranchConfig->branchCodeLengthItSelf, "0", STR_PAD_LEFT);

        $mfnSamityConfig = json_decode(DB::table('mfn_config')->where('title', 'samity')->first()->content);
        $samityCodeLengthItSelf = $mfnSamityConfig->samityCodeLengthItSelf;

        $maxSamityCodeOFThisBranch = DB::table('mfn_samity')
            ->where([
                ['is_delete', 0],
                ['branchId', $branchId],
            ])
            ->max('samityCode');

        if ($maxSamityCodeOFThisBranch == null) {
            $samitynumber = 1;
        } else {
            $maxSamityCodeOFThisBranch = substr($maxSamityCodeOFThisBranch, -$samityCodeLengthItSelf);
            $samitynumber = (int)$maxSamityCodeOFThisBranch + 1;
        }

        $samityCode = $branchCode . $codeSeperator . str_pad($samitynumber, $samityCodeLengthItSelf, "0", STR_PAD_LEFT);

        // IF ORGANIZATION TYPE IS NGO THEN MFI CODE OF THIS NGO WILL BE ADDED FIRST
        if ($mfnGnlConfig->companyType == 'ngo') {
            $samityCode = $mfnGnlConfig->mfiCode . $codeSeperator . $samityCode;
        }

        return $samityCode;
    }

    public function getPassport($req, $operationType, $samity = null)
    {
        $errorMsg = null;

        if ($operationType == 'store') {
            $branchId = Auth::user()->branch_id;
            $isOpening = MfnService::isOpening(Auth::user()->branch_id);
            $samityOpeningDate = Carbon::parse($req->opening_date)->format('Y-m-d');
        } else {
            $branchId = $samity->branchId;
            $isOpening = $samity->isOpening;
            $samityOpeningDate = $samity->openingDate;
        }
        $sysDate = MfnService::systemCurrentDate($branchId);

        if ($operationType != 'delete') {
            $validator = Validator::make($req->all(), [
                'samity_name'   => 'required',
                'opening_date'  => 'required|date',
                'working_area'  => 'required',
                'field_officer' => 'required',
                'max_no'        => 'required|numeric|min:1',
            ]);

            $attributes = array(
                'samity_name' => 'Samity Name',
                'working_area' => 'Working Area',
                'field_officer' => 'Field Officer',
                'max_no' => 'Maximum Active Member',
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        // SAMITY COULD NOT BE CREATED FROM THE HEAD OFFICE
        if (Auth::user()->branch_id == 1 && $operationType == 'store') {
            $errorMsg = 'Samity could not be created from Head Office.';
        }

        // check on update/delete other branch user can not delete this.
        if ($operationType == 'update' || $operationType == 'delete') {
            if (Auth::user()->branch_id != 1 &&  Auth::user()->branch_id != $samity->branchId) {
                $errorMsg = 'Samity could not be created/modified from another branch.';
            }
        }

        // CHECK THE SAMITY OPENING DATE IS EQUAL TO THE BRANCH DATE OR NOT
        if (!$isOpening && $sysDate != $samityOpeningDate) {
            $errorMsg = 'Branch date is not same to the samity opening date.';
        }
        if ($isOpening) {
            $branchSoftwareStartDate = DB::table('gnl_branchs')->where('id', $branchId)->value('mfn_start_date');

            if ($sysDate != $branchSoftwareStartDate) {
                $errorMsg = 'This is from opening, Branch should be on Software start date, i.e. ' . Carbon::parse($branchSoftwareStartDate)->format('d-m-Y');
            }

            // if it is from opening, than opening date could not be greater than software start date.
            if ($samityOpeningDate > $branchSoftwareStartDate) {
                $errorMsg = 'This is from opening, Samity Opening date should not be greater than ' . Carbon::parse($branchSoftwareStartDate)->format('d-m-Y');
            }
        }

        // check samity opening date is less than the branch start date or not
        if ($operationType != 'delete') {
            $barnchOpenigDate = DB::table('gnl_branchs')->where('id', $branchId)->value('branch_opening_date');

            if (Carbon::parse($req->opening_date)->format('Y-m-d') < $barnchOpenigDate) {
                $errorMsg = "Samity Opening Date could not be less than Branch Opening Date.";
            }
        }


        // IF THE OPERATION IS DELETE THEN CHECK ANY TRANSACTION IS PRESENT OR NOT
        // IF ANY TRANSACTION PRESENT THEN NO DATA CAN BE DELETED.
        if ($operationType == 'delete') {
            $anyMemberExists = DB::table('mfn_members')
                ->where([
                    ['is_delete', 0],
                    ['samityId', $samity->id],
                ])
                ->exists();

            $samityClosingExists = DB::table('mfn_samity_closing')
                ->where([
                    ['is_delete', 0],
                    ['samityId', $samity->id],
                ])
                ->exists();

            if ($anyMemberExists || $samityClosingExists) {
                $errorMsg = 'Data can not be deleted, transaction exists.';
            }
        }

        // IF THE OPERATION IS UPDATE THEN CHECK THAT REQUESTED CAN BE PROCEED OR NOT
        if ($operationType == 'update') {
            $anyMemberExists = DB::table('mfn_members')
                ->where([
                    ['is_delete', 0],
                    ['samityId', $samity->id],
                ])
                ->exists();

            // IF ANY MEMBER EXISTS THEN SAMITY TYPE COULD NOT BE CHNAGED
            if ($anyMemberExists && $req->samity_type != $samity->samityType) {
                $errorMsg = 'Samity Type could not be changed, member exists.';
            }

            // IF ANY MEMBER EXISTS THEN SAMITY DAY COULD NOT BE CHNAGED
            if ($anyMemberExists && $req->samity_day != $samity->samityDay) {
                $errorMsg = 'Samity Day could not be changed, member exists.';
            }

            // IF ANY MEMBER EXISTS THEN SAMITY OPENING DATE COULD NOT BE CHNAGED
            if ($anyMemberExists && Carbon::parse($req->opening_date)->format('Y-m-d') != $samity->openingDate) {
                $errorMsg = 'Samity Opening date could not be changed, member exists.';
            }

            // IF MAXIMUM ACTIVE MEMBER IS CHANGED THEN NEED TO VERIFY THE CURRENT ACTIVE MEMBER.
            $currentActiveMemberNumber = DB::table('mfn_members')
                ->where([
                    ['is_delete', 0],
                    ['samityId', $samity->id],
                    ['closingDate', '0000-00-00'],
                ])->count('id');

            if ($currentActiveMemberNumber > $req->max_no) {
                $errorMsg = "Current Active member Number is $currentActiveMemberNumber. So, maximum active member no could not be $req->max_no";
            }

            // IF ANY MEMBER EXIXTS THEN FIELD WORKING AREA COULD NOT BE CHANGED.
            if ($req->working_area != $samity->workingAreaId && $anyMemberExists) {
                $errorMsg = 'Member exists, working area could not be changed.';
            }

            // IF ANY MEMBER EXIXTS THEN FIELD OFFICER COULD NOT BE CHANGED.
            if ($req->field_officer != $samity->fieldOfficerEmpId && $anyMemberExists) {
                $errorMsg = 'Member exists, working area could not be changed.';
            }
        }


        // CHECK THE INCOMING DATA IS CORRECT OR NOT
        if ($operationType == 'store') {
            $samityCode = $this->generateSamityCodeForNewSamity($branchId);
            if ($samityCode != $req->samity_code) {
                $errorMsg = 'Something went wrong!';
            }
        }

        // if any samity day change exists today/after this date. then operation could not be performed
        if ($operationType != 'store') {
            $samityDayChnagesExists = DB::table('mfn_samity_day_changes')
                ->where([
                    ['is_delete', 0],
                    ['samityId', $samity->id],
                    ['effectiveDate', '>=', $sysDate],
                ])
                ->exists();

                if ($samityDayChnagesExists) {
                    $errorMsg = 'Samity Day change exists.';
                }
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $passport;
    }

    public function getActiveWeekDays()
    {
        $branchDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
        $activeWeekDays = array(
            'Saturday' => 'Saturday',
            'Sunday' => 'Sunday',
            'Monday' => 'Monday',
            'Tuesday' => 'Tuesday',
            'Wednesday' => 'Wednesday',
            'Thursday' => 'Thursday',
            'Friday' => 'Friday'
        );

        $weeklyHolidays = DB::table('hr_holidays_comp')
            ->where('ch_eff_date', '<=', $branchDate)
            ->orderBy('ch_eff_date', 'desc')
            ->limit(1)
            ->value('ch_day');

        if ($weeklyHolidays != null) {
            $weeklyHolidays = explode(',', $weeklyHolidays);
            $activeWeekDays = array_diff($activeWeekDays, $weeklyHolidays);
        }

        return $activeWeekDays;
    }
}
