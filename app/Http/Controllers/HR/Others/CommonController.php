<?php

namespace App\Http\Controllers\HR\Others;

use App\Http\Controllers\Controller;
use DB;
use Illuminate\Http\Request;

class CommonController extends Controller
{
    public function getDistricts(Request $req)
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

    public function getUpazilas(Request $req)
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

    public function getUnions(Request $req)
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

    public function getVillages(Request $req)
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
}
