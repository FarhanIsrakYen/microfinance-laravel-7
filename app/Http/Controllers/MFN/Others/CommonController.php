<?php

namespace App\Http\Controllers\MFN\Others;

use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Services\AccService;
use App\Services\MfnService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommonController extends Controller
{
    public function getSamities(Request $req)
    {
        $samities = MfnService::getSamities($req->branchId);
        return response()->json($samities);
    }

    public function getBranchDate(Request $req)
    {
        $branchDate = Carbon::parse(MfnService::systemCurrentDate($req->branchId))->format('d-m-Y');
        return response()->json($branchDate);
    }

    public function getObject(Request $req)
    {
        $data = DB::table($req->table)->where('id', $req->id)->first();

        return response()->json($data);
    }

    public function getMember(Request $req)
    {
        $data = DB::table('mfn_members')
            ->where('samityId', $req->samityId)
            ->select('id', 'name', 'memberCode', 'closingDate')
            ->get();
        return response()->json($data);
    }

    public function getMemberDetails(Request $req)
    {
        $data = DB::table('mfn_member_details')
            ->where('memberId', $req->memberId)
            ->select('spouseName', 'mobileNo')
            ->first();
        return response()->json($data);
    }

    public function getLoanAccounts(Request $req)
    {
        $data = DB::table('mfn_loans')
            ->where('productId', $req->productId)
            ->select('id', 'loanCode')
            ->get();
        return response()->json($data);
    }

    public function getSavingsAccounts(Request $req)
    {
        $data = DB::table('mfn_savings_accounts')
            ->where('savingsProductId', $req->productId)
            ->select('id', 'accountCode')
            ->get();
        return response()->json($data);
    }

    public static function getDistricts(Request $req)
    {
        $districts = DB::table('gnl_districts')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
            ]);

        $req->divisionId != '' ? $districts->where('division_id', $req->divisionId) : false;

        $districts = $districts->pluck('district_name', 'id')->all();

        return response()->json($districts);
    }

    public static function getUpazilas(Request $req)
    {
        $upazilas = DB::table('gnl_upazilas')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
            ]);

        $req->districtId != '' ? $upazilas->where('district_id', $req->districtId) : false;

        $upazilas = $upazilas->pluck('upazila_name', 'id')->all();

        return response()->json($upazilas);
    }

    public static function getUnions(Request $req)
    {
        $unions = DB::table('gnl_unions')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
            ]);

        $req->upazilaId != '' ? $unions->where('upazila_id', $req->upazilaId) : false;

        $unions = $unions->pluck('union_name', 'id')->all();

        return response()->json($unions);
    }

    public static function getVillages(Request $req)
    {
        $villages = DB::table('gnl_villages')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
            ]);

        $req->unionId != '' ? $villages->where('union_id', $req->unionId) : false;

        $villages = $villages->pluck('village_name', 'id')->all();

        return response()->json($villages);
    }

    public static function getBankLedgerId(Request $req)
    {
        $branchId = Auth::user()->branch_id;

        if (isset($req->branchId)) {
            if ($req->branchId != null && $req->branchId != '') {
                $branchId = $req->branchId;
            }
        }

        $accTypeID = $req->accTypeID;
        $value     = $req->selected;

        $ledgersIds = AccService::getLedgerAccount($branchId, null, null, $accTypeID, null, null);

        // return response()->json($ledgersIds);

        $html = '<option value="">Select Bank</option>';

        foreach ($ledgersIds as $Row) {
            $selectTxt = '';
            if ($value != null) {
                if ($Row->id == $value) {
                    $selectTxt = "selected";
                }
            }
            $html .= '<option value="' . $Row->id . '" ' . $selectTxt . ' >' . $Row->name . '</option>';
        }

        return $html;
    }

    ///////api call function
    public static function getBranchs()
    {
        if (Auth::user()->branch_id == 1) {
            $branchList = Branch::where('is_delete', 0)
                ->select(DB::raw("CONCAT(branch_code, ' - ', branch_name) AS label, id"))
                ->orderBy('branch_name')
                ->get();

        } else {
            $branchList = Branch::where('is_delete', 0)->where('id', Auth::user()->branch_id)->orderBy('branch_name')->select('id', 'branch_name as label')->get();
        }

        return response()->json($branchList);
    }

    public static function getDivisions()
    {
        $divisions = DB::table('gnl_divisions')
            ->where([
                ['is_delete', 0],
                ['is_active', 1],
            ])
            ->select('id', 'division_name as label')
            ->orderBy('division_name')
            ->get();

        return response()->json($divisions);
    }

    public static function getAccountStatement(Request $req)
    {
        $accountStatement = DB::table('mfn_savings_deposit as msd')
            ->where([
                ['msd.is_delete', 0],
                ['msd.branchId', $req->branchId],
                ['msd.samityId', $req->samityId],
                ['msd.memberId', $req->memberId],
                ['msd.accountId', $req->accountId],
            ])
            ->where(function($query) use ($req){
                if (isset($req->depositDate)) {
                    if (!is_null($req->depositDate) || !empty($req->depositDate)) {
                        $query->where('msd.date', date('Y-m-d', strtotime($req->depositDate)));
                    }
                }
            })
            ->select('msd.id', 'mm.name as memberName', 'msd.amount', 'msd.date')
            ->leftjoin('mfn_members as mm', 'msd.memberId', 'mm.id')
            ->get();

        return response()->json($accountStatement);
    }

}
