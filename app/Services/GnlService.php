<?php

namespace App\Services;

use DB;
use App\Services\HrService;
use Carbon\Carbon;

class GnlService
{
    public static function getDivisions()
    {
        return DB::table('gnl_divisions')
        ->where([
            ['is_active', 1],
            ['is_delete', 0],
        ])
        ->get();
    }

    public static function getDistricts($filters = [])
    {
        $districts = DB::table('gnl_districts')
        ->where([
            ['is_active', 1],
            ['is_delete', 0],
        ]);

        if (isset($filters['division_id'])) {
            $districts->where('division_id', $filters['division_id']);
        }

        return $districts->get();
    }

    public static function getUpazilas($filters = [])
    {
        $upazilas = DB::table('gnl_upazilas')
        ->where([
            ['is_active', 1],
            ['is_delete', 0],
        ]);

        if (isset($filters['district_id'])) {
            $upazilas->where('district_id', $filters['district_id']);
        }

        return $upazilas->get();
    }

    public static function getUnions($filters = [])
    {
        $unions = DB::table('gnl_unions')
        ->where([
            ['is_active', 1],
            ['is_delete', 0],
        ]);

        if (isset($filters['upazila_id'])) {
            $unions->where('upazila_id', $filters['upazila_id']);
        }

        return $unions->get();
    }

    public static function getVillages($filters = [])
    {
        $villages = DB::table('gnl_villages')
        ->where([
            ['is_active', 1],
            ['is_delete', 0],
        ]);

        if (isset($filters['union_id'])) {
            $villages->where('union_id', $filters['union_id']);
        }

        return $villages->get();
    }
}
