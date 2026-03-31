<?php

namespace App\Http\Controllers\MFN\GConfig;

use App\Http\Controllers\Controller;
use App\Model\MFN\WorkingArea;
use App\Model\GNL\Branch;
use App\Model\GNL\Village;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;
use App\Services\MfnService;
use App\Services\RoleService;

class WorkingAreaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $req)
    {
        if ($req->ajax() || $req->is('api/*')) {


            $columns = ['mfn_working_areas.name', 'mfn_working_areas.branchId', 'mfn_working_areas.villageId'];

            $limit = $req->length;
            $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ?0 : (int)$req->input('order.0.column') - 1;
            $order = $columns[$orderColumnIndex];
            $dir = $req->input('order.0.dir');
            // Searching variable
            $search = (empty($req->input('search.value'))) ?null : $req->input('search.value');

            $areas = DB::table('mfn_working_areas')
                ->where('mfn_working_areas.is_delete', 0)
                ->join('gnl_branchs', 'gnl_branchs.id', 'mfn_working_areas.branchId')
                ->join('gnl_villages', 'gnl_villages.id', 'mfn_working_areas.villageId')
                ->select(DB::raw('mfn_working_areas.* , CONCAT(gnl_branchs.branch_code, " - ", gnl_branchs.branch_name) AS branch, gnl_villages.village_name AS village'))
                // ->orderBy($order, $dir)
                ->orderBy($order, "asc")
                ->limit($limit);

            if (Auth::user()->branch_id != 1) {
                $areas->where('branchId', Auth::user()->branch_id);
            }

            if ($search != null) {
                $areas->where(function ($query) use ($search) {
                    $query->where('mfn_working_areas.name', 'LIKE', "%{$search}%")
                        ->orWhere('gnl_branchs.branch_name', 'LIKE', "%{$search}%")
                        ->orWhere('gnl_villages.village_name', 'LIKE', "%{$search}%");
                });
            }
            $areas = $areas->get();

            $totalData = DB::table('mfn_working_areas')->where('is_delete', 0)->count('id');

            $sl = (int)$req->start + 1;
            foreach ($areas as $key => $area) {
                $areas[$key]->sl = $sl++;
                $areas[$key]->action        = RoleService::roleWiseArray($this->GlobalRole, $areas[$key]->id);
            }

            $data = array(
                "draw" => intval($req->input('draw')),
                "recordsTotal" => $totalData,
                "recordsFiltered" => $totalData,
                'data' => $areas,
            );

            return response()->json($data);
        }

        return view('MFN.WorkingArea.index');
    }

    public function view($wareaId)
    {
        $warea = WorkingArea::where('id', $wareaId)->first();

        $branch = DB::table('gnl_branchs')
            ->where('id', $warea->branchId)
            ->select('branch_name', 'branch_code')
            ->first();

        $village = DB::table('gnl_villages')->where('id', $warea->villageId)->select('village_name')->first();

        $data = array(
            'warea'            => $warea,
            'branch'            => $branch,
            'village'           => $village,
        );

        return view('MFN.WorkingArea.view', $data);
    }

    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            $passport = $this->getPassport($req, $operationType = 'store');

            if ($passport['isValid'] == false) {
                $notification = array(
                    'message' => $passport['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $workingArea = new WorkingArea();
            $workingArea->name        = $req->name;
            $workingArea->branchId    = $req->branchId;
            $workingArea->villageId   = $req->villageId;
            $workingArea->created_by  = Auth::user()->id;
            $workingArea->created_at  = Carbon::now();
            $workingArea->save();

            $notification = array(
                'message' => 'Successfully Inserted',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        }

        if (Auth::user()->branch_id == 1) {
            $branchList = Branch::where('is_delete', 0)->orderBy('branch_name')->select('id', 'branch_name')->get();
        }
        else{
            $branchList = Branch::where('is_delete', 0)->where('id', Auth::user()->branch_id)->orderBy('branch_name')->select('id', 'branch_name')->get();
        }

        $divisions = DB::table('gnl_divisions')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
            ])
            ->select('id', 'division_name')
            ->orderBy('division_name')
            ->get();

        return view('MFN.WorkingArea.add', compact('branchList', 'divisions'));
    }


    public function edit(Request $req)
    {
        if (Auth::user()->branch_id == 1) {
            $branchList = Branch::where('is_delete', 0)->orderBy('branch_name')->select('id', 'branch_name')->get();
        }
        else{
            $branchList = Branch::where('is_delete', 0)->where('id', Auth::user()->branch_id)->orderBy('branch_name')->select('id', 'branch_name')->get();
        }
        $villageList = Village::where('is_delete', 0)->orderBy('village_name')->select('id', 'village_name')->get();
        $workingAreaList = WorkingArea::where('id', $req->wareaId)->first();;

        if ($req->isMethod('post')) {
            $passport = $this->getPassport($req, $operationType = 'store');

            if ($passport['isValid'] == false) {
                $notification = array(
                    'message' => $passport['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $workingArea = WorkingArea::find($workingAreaList->id);
            $workingArea->name        = $req->name;
            $workingArea->branchId    = $req->branchId;
            $workingArea->villageId   = $req->villageId;
            $workingArea->updated_by  = Auth::user()->id;
            $workingArea->updated_at  = Carbon::now();
            $workingArea->save();

            $notification = array(
                'message' => 'Successfully Updated',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        }
        return view('MFN.WorkingArea.edit', compact('branchList', 'villageList', 'workingAreaList'));
    }

    public function delete(Request $req)
    {
        $wareaData = WorkingArea::where('id', $req->wareaId)->first();

        $wareaValid = $this->getPassport($req, $operationType = 'delete', $wareaData);
        if ($wareaValid['isValid'] == false) {
            $notification = array(
                'message' => $wareaValid['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $wareaData = WorkingArea::find($wareaData->id);

        $wareaData->is_delete = 1;

        $delete = $wareaData->save();

        if ($delete) {
            $notification = array(
                'message' => 'Successfully Deleted',
                'alert-type' => 'success',
            );
            return response()->json($notification);
        } else {
            $notification = array(
                'message' => 'Unsuccessful to Delete',
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }
    }

    public function getPassport($req, $operationType, $wareaData = null)
    {
        $errorMsg = null;

        if ($operationType == 'store') {
            $branchId = Auth::user()->branch_id;
        } else {
            $branchId = $wareaData->branchId;
        }
        $sysDate = MfnService::systemCurrentDate($branchId);

        if ($operationType != 'delete') {
            $validator = Validator::make($req->all(), [
                'name'       => 'required',
                'branchId'   => 'required',
                'villageId'  => 'required',
            ]);

            $attributes = array(
                'name' => 'Working Area Name',
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        // check on update/delete other branch user can not delete this.
        if ($operationType == 'update' || $operationType == 'delete') {
            if (Auth::user()->branch_id != 1 &&  Auth::user()->branch_id != $wareaData->branchId) {
                $errorMsg = 'Working Area could not be created/modified from another branch.';
            }
        }


        $isValid = $errorMsg == null ?true : false;

        $wareaValid = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $wareaValid;
    }
}
