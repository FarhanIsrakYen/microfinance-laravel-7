<?php

namespace App\Http\Controllers\MFN\GConfig;

use App\Http\Controllers\Controller;
use App\Services\RoleService;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;

class FieldOfficerController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }
    
    public function index(Request $req) {

        $content = DB::table('mfn_config')
        ->where('title', 'fieldOfficerHrDesignationIds')
        ->first()->content;

        $isContentSet = $content == '' || null ? false : true; 

        $fieldOfficersDesignationIds = json_decode($content);

        $fieldOfficers = DB::table('hr_designations')
            ->where([['is_delete', 0],])
            ->whereIn('id', $fieldOfficersDesignationIds)
            ->select('name')
            ->get();

        $data = array(
            'isContentSet'  => $isContentSet,
            'fieldOfficers' => $fieldOfficers,
        );

        return view('MFN.FieldOfficer.index', $data);
    }

    public function add(Request $req) {

        if ($req->isMethod('post')) {

            $desig_arrs = isset($req['desig_arr']) ? $req['desig_arr'] : array();
            
            $isUpdate = DB::table('mfn_config')
            ->where('id', 5)
            ->update(['content' => json_encode($desig_arrs)]);

            if ($isUpdate) {
                $notification = array(
                'message' => 'Successfully Inserted',
                'alert-type' => 'success',
                );
                return response()->json($notification);
            }   

        } 
        return view('MFN.FieldOfficer.add');
    }


    public function edit(Request $req) {
        
        if ($req->isMethod('post')) {

            $desig_arrs = isset($req['desig_arr']) ? $req['desig_arr'] : array();
            
            $isUpdate = DB::table('mfn_config')
            ->where('id', 5)
            ->update(['content' => json_encode($desig_arrs)]);

            if ($isUpdate) {
                $notification = array(
                'message' => 'Successfully Updated',
                'alert-type' => 'success',
                );
                return response()->json($notification);
            }

        } 
        $fieldOfficers = json_decode(DB::table('mfn_config')
                    ->where('title', 'fieldOfficerHrDesignationIds')
                    ->first()->content);

        return view('MFN.FieldOfficer.edit',compact('fieldOfficers'));
    }

}
