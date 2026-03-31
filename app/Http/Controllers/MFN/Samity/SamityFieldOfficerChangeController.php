<?php

namespace App\Http\Controllers\MFN\Samity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\HrService;
use DB;
use App\Services\MfnService;
use App\Services\RoleService;
use App\Model\GNL\Branch;
use Auth;
use App\Model\MFN\SamityFieldOfficerChange;
use Carbon\Carbon;
use App\Model\MFN\Samity;
use Illuminate\Support\Facades\Validator;

class SamityFieldOfficerChangeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $req)
    {

        if (!$req->ajax()) {
            $branchList = Branch::where('is_delete', 0)
                ->where('id', '>', 1)
                ->orderBy('branch_code')
                ->select('id', 'branch_name', 'branch_code')
                ->get();
            return view('MFN.SamityFieldOfficerChange.index', compact('branchList'));
        }

        $columns = ['mfn_samity.samityCode', 'gnl_branchs.branch_code', 'empOne.emp_name', 'empTwo.emp_name', 'sfoc.effectiveDate'];

        $limit = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order = $columns[$orderColumnIndex];
        $dir = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');
        $startDate = (empty($req->input('startDate'))) ? null : $req->input('startDate');
        $endDate = (empty($req->input('endDate'))) ? null : $req->input('endDate');
        $branchId = (empty($req->input('branchId'))) ? null : $req->input('branchId');
        $samityNameOrCode = (empty($req->input('samityNameOrCode'))) ? null : $req->input('samityNameOrCode');
        $fieldOfficer = (empty($req->input('fieldOfficer'))) ? null : $req->input('fieldOfficer');

        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        $records = DB::table('mfn_samity_field_officer_change AS sfoc')
            ->join('mfn_samity', 'mfn_samity.id', 'sfoc.samityId')
            ->join('gnl_branchs', 'gnl_branchs.id', 'sfoc.branchId')
            ->join('hr_employees AS empOne', 'empOne.id', 'sfoc.oldFieldOfficerEmpId')
            ->join('hr_employees AS empTwo', 'empTwo.id', 'sfoc.newFieldOfficerEmpId')
            ->whereIn('sfoc.branchId', $accessAbleBranchIds)
            ->where('sfoc.is_delete', 0)
            ->select(DB::raw('CONCAT(mfn_samity.samityCode, " - ", mfn_samity.name) AS samity, CONCAT(gnl_branchs.branch_code, " - ", gnl_branchs.branch_name) AS branch, empOne.emp_name AS preFieldOfficer, empTwo.emp_name AS newFieldOfficer, sfoc.effectiveDate, sfoc.id'))
            ->orderBy($order, $dir);

        if ($search != null) {
            $records->where(function ($query) use ($search) {
                $query->where('mfn_samity.name', 'LIKE', "%{$search}%")
                    ->orWhere('mfn_samity.samityCode', 'LIKE', "%{$search}%")
                    ->orWhere('gnl_branchs.branch_name', 'LIKE', "%{$search}%")
                    ->orWhere('gnl_branchs.branch_name', 'LIKE', "%{$search}%")
                    ->orWhere('empOne.emp_name', 'LIKE', "%{$search}%")
                    ->orWhere('empTwo.emp_name', 'LIKE', "%{$search}%");
            });
        }

        if ($branchId != null) {
            $records->where(function ($query) use ($branchId) {
                $query->where('sfoc.branchId', '=', $branchId);
            });
        }

        if ($samityNameOrCode != null) {
            $records->where(function ($query) use ($samityNameOrCode) {
                $query->where('mfn_samity.name', 'LIKE', "%{$samityNameOrCode}%")
                    ->orWhere('mfn_samity.samityCode', 'LIKE', "%{$samityNameOrCode}%");
            });
        }

        if ($fieldOfficer != null) {
            $records->where(function ($query) use ($fieldOfficer) {
                $query->where('empOne.emp_name', 'LIKE', "%{$fieldOfficer}%")
                    ->orWhere('empTwo.emp_name', 'LIKE', "%{$fieldOfficer}%");
            });
        }

        if ($startDate != null || $endDate != null) {

            $startDate = Carbon::parse($startDate)->format('Y-m-d');
            $endDate   = Carbon::parse($endDate)->format('Y-m-d');

            $records->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('sfoc.effectiveDate', [$startDate, $endDate]);
            });
        }

        $totalData = (clone $records)->count();
        $records = $records->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;
        foreach ($records as $key => $record) {
            $records[$key]->effectiveDate = Carbon::parse($record->effectiveDate)->format('d-m-Y');
            $records[$key]->sl            = $sl++;
            $records[$key]->action        = RoleService::roleWiseArray($this->GlobalRole, $records[$key]->id);
        }

        $data = array(
            "draw"              => intval($req->input('draw')),
            "recordsTotal"      => $totalData,
            "recordsFiltered"   => $totalData,
            'data'              => $records,
        );

        return response()->json($data);
    }

    public function view($samityFieldOfficerChnageId)
    {
        $samityFOC = DB::table('mfn_samity_field_officer_change')->where('id', $samityFieldOfficerChnageId)->first();
        if (Auth::user()->branch_id != 1 &&  Auth::user()->branch_id != $samityFOC->branchId) {
            return '';
        }

        $samity = DB::table('mfn_samity')->where('id', $samityFOC->samityId)->first();

        $branch = DB::table('gnl_branchs')
            ->where('id', $samity->branchId)
            ->select('branch_name', 'branch_code')
            ->first();

        $newFieldOfficerName = DB::table('hr_employees')->where('id', $samityFOC->newFieldOfficerEmpId)->first()->emp_name;
        $previousFieldOfficerName = DB::table('hr_employees')->where('id', $samityFOC->oldFieldOfficerEmpId)->first()->emp_name;

        $workingArea = DB::table('mfn_working_areas')->where('id', $samity->workingAreaId)->select('name', 'villageId')->first();
        $village = DB::table('gnl_villages')->where('id', $workingArea->villageId)->select('village_name', 'union_id')->first();
        $union = DB::table('gnl_unions')->where('id', $village->union_id)->select('union_name', 'upazila_id')->first();
        $upazila = DB::table('gnl_upazilas')->where('id', $union->upazila_id)->select('upazila_name', 'district_id')->first();
        $district = DB::table('gnl_districts')->where('id', $upazila->district_id)->select('district_name', 'division_id')->first();
        $division = DB::table('gnl_divisions')->where('id', $district->division_id)->select('division_name')->first();

        $data = array(
            'samityFOC'                 => $samityFOC,
            'samity'                    => $samity,
            'branch'                    => $branch,
            'newFieldOfficerName'       => $newFieldOfficerName,
            'previousFieldOfficerName'  => $previousFieldOfficerName,
            'workingArea'               => $workingArea,
            'village'                   => $village,
            'union'                     => $union,
            'upazila'                   => $upazila,
            'district'                  => $district,
            'division'                  => $division,
        );

        return view('MFN.SamityFieldOfficerChange.view', $data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        $branches = DB::table('gnl_branchs')
            ->where([
                ['is_delete', 0],
                ['is_approve', 1],
                ['id', '!=', 1],
            ])
            ->whereIn('id', $accessAbleBranchIds)
            ->select(DB::raw("CONCAT(branch_code, '-', branch_name) AS name, id"))
            ->get();

        if (count($accessAbleBranchIds) > 1) {
            $samities = collect([]);
            $effectiveDate = '';
        } else {
            $samities = DB::table('mfn_samity')
                ->where([
                    ['is_delete', 0],
                    ['closingDate', '0000-00-00'],
                    ['openingDate', '<=', $sysDate],
                ])
                ->whereIn('branchId', $accessAbleBranchIds)
                ->select(DB::raw("CONCAT(samityCode, '-', name) AS name, id"))
                ->get();
            $effectiveDate = Carbon::parse($sysDate)->format('d-m-Y');
        }

        $data = array(
            'branches'      => $branches,
            'samities'      => $samities,
            'effectiveDate' => $effectiveDate,
        );

        return view('MFN.SamityFieldOfficerChange.add', $data);
    }

    public function store(Request $req)
    {

        $passport = $this->getPassport($req, $operationType = 'store');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {
            $samity = Samity::find($req->samity);

            $samityFoC = new SamityFieldOfficerChange;
            $samityFoC->samityId                = $samity->id;
            $samityFoC->branchId                = $samity->branchId;
            $samityFoC->oldFieldOfficerEmpId    = $samity->fieldOfficerEmpId;
            $samityFoC->newFieldOfficerEmpId    = $req->newFieldOfficer;
            $samityFoC->effectiveDate           = Carbon::parse($req->effectiveDate);
            $samityFoC->created_at              = Carbon::now();
            $samityFoC->created_by              = Auth::user()->id;
            $samityFoC->save();

            $samity->fieldOfficerEmpId = $req->newFieldOfficer;
            $samity->save();

            DB::commit();

            $notification = array(
                'message'       => 'Successfully Inserted',
                'alert-type'    => 'success',
            );
            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong.' //.$e->getFile().' '.$e->getLine(). ' '.$e->getMessage() 
            );
            return response()->json($notification);
        }
    }

    public function edit(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->update($req);
        }

        $samityFOC = DB::table('mfn_samity_field_officer_change')->where('id', $req->samityFOCId)->first();

        $samityName = DB::table('mfn_samity')
            ->where('id', $samityFOC->samityId)
            ->select(DB::raw("CONCAT(samityCode, '-', name) AS name"))
            ->value('name');

        $filedOfficers = MfnService::getFieldOfficers($samityFOC->branchId);

        $currentFieldOfficer = $filedOfficers->where('id', $samityFOC->oldFieldOfficerEmpId)->first()->name;

        $filedOfficers = $filedOfficers->where('id', '!=', $samityFOC->oldFieldOfficerEmpId);

        $data = array(
            'samityFOC'             => $samityFOC,
            'samityName'            => $samityName,
            'effectiveDate'         => Carbon::parse($samityFOC->effectiveDate)->format('d-m-Y'),
            'currentFieldOfficer'   => $currentFieldOfficer,
            'filedOfficers'         => $filedOfficers
        );

        return view('MFN.SamityFieldOfficerChange.edit', $data);
    }

    public function update(Request $req)
    {
        $samityFOC = SamityFieldOfficerChange::find(decrypt($req->samityFOC_id));

        $passport = $this->getPassport($req, $operationType = 'update', $samityFOC);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {
            $samity = Samity::find($samityFOC->samityId);

            $samityFOC->newFieldOfficerEmpId    = $req->newFieldOfficer;
            $samityFOC->updated_at              = Carbon::now();
            $samityFOC->updated_by              = Auth::user()->id;
            $samityFOC->save();

            $samity->fieldOfficerEmpId = $req->newFieldOfficer;
            $samity->save();

            DB::commit();

            $notification = array(
                'message'       => 'Successfully Updated',
                'alert-type'    => 'success',
            );
            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong.' //.$e->getFile().' '.$e->getLine(). ' '.$e->getMessage() 
            );
            return response()->json($notification);
        }
    }

    public function delete(Request $req)
    {
        $samityFOC = SamityFieldOfficerChange::find($req->samityFOC_id);
        $passport = $this->getPassport($req, $operationType = 'delete', $samityFOC);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {
            $samityFOC->is_delete  = 1;
            $samityFOC->save();

            // update samity field officer
            DB::table('mfn_samity')
                ->where('id', $samityFOC->samityId)
                ->update(['fieldOfficerEmpId' => $samityFOC->oldFieldOfficerEmpId]);

            DB::commit();

            $notification = array(
                'message'       => 'Successfully Deleted',
                'alert-type'    => 'success',
            );
            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();

            $notification = array(
                'alert-type'    => 'error',
                'message'       => 'Something went wrong',
                'consoleMsg'    => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage()
            );
            return response()->json($notification);
        }
    }

    public function getSamityInfo(Request $req)
    {
        $samity = DB::table('mfn_samity')->where('id', $req->samityId)->first();

        $filedOfficers = MfnService::getFieldOfficers($samity->branchId);

        $currentFieldOfficer = $filedOfficers->where('id', $samity->fieldOfficerEmpId)->first()->name;

        $filedOfficers = $filedOfficers->where('id', '!=', $samity->fieldOfficerEmpId);
        $filedOfficers = $filedOfficers->pluck('name', 'id')->toArray();

        $data = array(
            'currentFieldOfficer'   => $currentFieldOfficer,
            'filedOfficers'         => $filedOfficers,
        );

        return response()->json($data);
    }

    public function getPassport($req, $operationType, $samityFOC = null)
    {
        $errorMsg = null;

        if ($operationType == 'store') {
            $branchId = Auth::user()->branch_id;
            $samityId = $req->samity;
            $effectiveDate = Carbon::parse($req->effectiveDate)->format('Y-m-d');
        } else {
            $branchId = $samityFOC->branchId;
            $samityId = $samityFOC->samityId;
            $effectiveDate = $samityFOC->effectiveDate;
        }
        $sysDate = MfnService::systemCurrentDate($branchId);

        if ($operationType != 'delete') {
            $validator = Validator::make($req->all(), [
                'samity'   => 'required',
                'newFieldOfficer'  => 'required',
            ]);

            $attributes = array(
                'samity_name' => 'Samity',
                'newFieldOfficer' => 'newFieldOfficer',
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        // CHECK THE TRANSACTION DATE IS EQUAL TO THE BRANCH DATE OR NOT
        if ($effectiveDate != $sysDate) {
            $errorMsg = 'Branch date is not on ' . Carbon::parse($effectiveDate)->format('d-m-Y');
        }

        // IF ANY FURTHER FIELD OFFICER CHNAGE EXISTS TODAY OR AFTER SYSTEM DATE
        // THEN THIS OPERATION COULD NOT BE PROCEED
        $isAnyFOCExists = DB::table('mfn_samity_field_officer_change')
            ->where([
                ['is_delete', 0],
                ['samityId', $samityId],
                ['effectiveDate', '>=', $sysDate],
            ]);
        if ($operationType == 'update' || $operationType == 'delete') {
            $isAnyFOCExists->where('id', '!=', $samityFOC->id);
        }
        $isAnyFOCExists = $isAnyFOCExists->exists();

        if ($isAnyFOCExists) {
            $errorMsg = 'Field Officer change of this Samity already exists today/after this date.';
        }

        // If any transaction exists then operation could not be performed
        $depositExists = DB::table('mfn_savings_deposit')
            ->where([
                ['is_delete', 0],
                ['samityId', $samityId],
                ['amount', '!=', 0],
                ['date', '>=', $effectiveDate],
            ])
            ->exists();

        $withdrawExists = DB::table('mfn_savings_withdraw')
            ->where([
                ['is_delete', 0],
                ['samityId', $samityId],
                ['amount', '!=', 0],
                ['date', '>=', $effectiveDate],
            ])
            ->exists();

        $loanDisExists = DB::table('mfn_loans')
            ->where([
                ['is_delete', 0],
                ['samityId', $samityId],
                ['disbursementDate', '>=', $effectiveDate],
            ])
            ->exists();

        $loanCollectionExists = DB::table('mfn_loan_collections')
            ->where([
                ['is_delete', 0],
                ['amount', '!=', 0],
                ['samityId', $samityId],
                ['collectionDate', '>=', $effectiveDate],
            ])
            ->exists();

        if ($depositExists || $withdrawExists || $loanDisExists || $loanCollectionExists) {
            $errorMsg = 'Transaction exists today/after this date.';
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $passport;
    }
}
