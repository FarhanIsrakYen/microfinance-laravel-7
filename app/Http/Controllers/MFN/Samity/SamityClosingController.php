<?php

namespace App\Http\Controllers\MFN\Samity;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MfnService;
use App\Services\HrService;
use App\Services\RoleService;
use Carbon\Carbon;
use Auth;
use DB;
use Validator;
use App\Model\MFN\Samity;
use App\Model\MFN\SamityClosing;
use App\Model\GNL\Branch;

class SamityClosingController extends Controller
{
    public function index(Request $req)
    {
        if (!$req->ajax()) {

            $branchList = Branch::where('is_delete', 0)
                ->where('id', '>', 1)
                ->orderBy('branch_code')
                ->select(DB::raw("id, CONCAT(branch_code, ' - ', branch_name) AS branch_name"))
                ->get();
                
            return view('MFN.SamityClosing.index', compact('branchList'));
        }

        $columns = ['mfn_samity.name', 'samityCode', 'openingDate','gnl_branchs.branch_code', 'mfn_samity_closing.closingDate'];

        $limit = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order = $columns[$orderColumnIndex];
        $dir = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');
        $branchId = (empty($req->input('branchId'))) ? null : $req->input('branchId');
        $samityNameOrCode = (empty($req->input('samityNameOrCode'))) ? null : $req->input('samityNameOrCode');

        $accessAbleBranchIds = HrService::getUserAccesableBranchIds();

        $samities = DB::table('mfn_samity_closing')
            ->leftJoin('mfn_samity', 'mfn_samity.id', 'mfn_samity_closing.samityId')
            ->leftJoin('gnl_branchs', 'gnl_branchs.id', 'mfn_samity_closing.branchId')
            ->whereIn('mfn_samity_closing.branchId', $accessAbleBranchIds)
            ->where('mfn_samity_closing.is_delete', 0)
            ->select(DB::raw('mfn_samity.name, mfn_samity.samityCode, mfn_samity.openingDate, 
                    CONCAT(gnl_branchs.branch_code, " - ", gnl_branchs.branch_name) AS branch, 
                    mfn_samity_closing.id, mfn_samity_closing.closingDate'))
            ->orderBy($order, $dir);

        if ($search != null) {
            $samities->where(function ($query) use ($search) {
                $query->where('mfn_samity.name', 'LIKE', "%{$search}%")
                    ->orWhere('mfn_samity.samityCode', 'LIKE', "%{$search}%")
                    ->orWhere('gnl_branchs.branch_code', 'LIKE', "%{$search}%")
                    ->orWhere('gnl_branchs.branch_name', 'LIKE', "%{$search}%");
            });
        }
        if ($branchId != null) {
            $samities->where(function ($query) use ($branchId) {
                $query->where('mfn_samity_closing.branchId', '=', $branchId);
            });
        }

        if ($samityNameOrCode != null) {
            $samities->where(function ($query) use ($samityNameOrCode) {
                $query->where('mfn_samity.name', 'LIKE', "%{$samityNameOrCode}%")
                    ->orWhere('mfn_samity.samityCode', 'LIKE', "%{$samityNameOrCode}%");
            });
        }


        $totalData = (clone $samities)->count();
        $samities = $samities->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;
        foreach ($samities as $key => $samity) {
            $samities[$key]->openingDate = Carbon::parse($samity->openingDate)->format('d-m-Y');
            $samities[$key]->closingDate = Carbon::parse($samity->closingDate)->format('d-m-Y');
            $samities[$key]->sl = $sl++;
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
                ['id', '!=',1],
            ])
            ->whereIn('id', $accessAbleBranchIds)
            ->select(DB::raw("CONCAT(branch_code, '-', branch_name) AS name, id"))
            ->get();

        if (count($accessAbleBranchIds) > 1) {
            $samities = collect([]);
            $closingDate = '';
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
            $closingDate = Carbon::parse($sysDate)->format('d-m-Y');
        }

        $data = array(
            'branches'      => $branches,
            'samities'      => $samities,
            'closingDate'   => $closingDate,
        );
        return view('MFN.SamityClosing.add', $data);
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

        $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);

        $isActiveMember = DB::table('mfn_members')
                    ->where('is_delete',0)
                    ->where('samityId',$req->samity)
                    ->where('closingDate','0000-00-00')
                    ->exists();
                    
        // if active member exists, then no entry
        if ($isActiveMember == 'true') {
            $notification = array(
                'message'       => 'Active Member Exists, Cant Close Samity',
                'alert-type'    => 'error',
            );
            return response()->json($notification);
        }

        // if closing date and sysDate doesnt match, then no entry
        else if($req->closingDate != Carbon::parse($sysDate)->format('d-m-Y')){
            $notification = array(
                'message'       => 'Closing Date doesnt match with System Date',
                'alert-type'    => 'error',
            );
            return response()->json($notification);
        }
        else{

            $samity = DB::table('mfn_samity')->where('id', $req->samity)->first();

            $samityClosing = new SamityClosing();
            $samityClosing->samityId           = $samity->id;
            $samityClosing->branchId           = $samity->branchId;
            $samityClosing->closingDate        = Carbon::parse($req->closingDate);
            $samityClosing->created_by         = Auth::user()->id;
            $samityClosing->created_at         = Carbon::now();
            $samityClosing->save();

            $notification = array(
                'message'       => 'Successfully Inserted',
                'alert-type'    => 'success',
            );

            return response()->json($notification);
        }
        
    }

    public function delete(Request $req)
    {
        $samityClosing = DB::table('mfn_samity_closing')->where('id', $req->closing_id)->first();

        $passport = $this->getPassport($req, $operationType = 'delete', $samityClosing);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $samityClosing = SamityClosing::find($samityClosing->id);
        $samityClosing->is_delete  = 1;
        $samityClosing->save();

        $notification = array(
            'message'       => 'Successfully Deleted',
            'alert-type'    => 'success',
        );

        return response()->json($notification);
    }

    public function getPassport($req, $operationType, $samityClosing = null)
    {
        $errorMsg = null;

        if ($operationType != 'delete') {
            $validator = Validator::make($req->all(), [
                'samity'        => 'required',
                'closingDate'   => 'required',
            ]);

            $attributes = array(
                'samity' => 'Samity',
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        // Set requied variables
        if ($operationType == 'store') { 
            // $samity = Samity::find($req->samity);
        

        } else {
            $samity = Samity::find($samityClosing->samityId);
        }

        if ($operationType == 'delete') {
            // if closing date and sysDate doesnt match, then it cant not be deleted
            $sysDate = MfnService::systemCurrentDate(Auth::user()->branch_id);
            if($req->closingDate != Carbon::parse($sysDate)->format('d-m-Y')){
                $errorMsg = 'Closing Date doesnt match with System Date';
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $passport;
    }
}
