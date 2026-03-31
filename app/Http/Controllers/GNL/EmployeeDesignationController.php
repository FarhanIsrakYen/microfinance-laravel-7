<?php

namespace App\Http\Controllers\GNL;

use App\Helpers\CommonHelper as Common;
use App\Http\Controllers\Controller;
use App\Model\GNL\EmpDesignation;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;


class EmployeeDesignationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $req) {

        if (!$req->ajax()) {
                
            return view('GNL.EmpDesignation.index');
        }

        $columns = ['hr_designations.name'];

        $limit = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order = $columns[$orderColumnIndex];
        $dir = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $empDesignationList = EmpDesignation::where('is_delete',0)
                                ->select('hr_designations.id','hr_designations.name')
                                ->orderBy($order, $dir);

        if ($search != null) {
            $empDesignationList->where(function ($query) use ($search) {
                $query->where('hr_designations.name', 'LIKE', "%{$search}%");
            });
        }

        $totalData = (clone $empDesignationList)->count();
        $empDesignationList = $empDesignationList->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;
        foreach ($empDesignationList as $key => $empDesignation) {
            $empDesignationList[$key]->sl = $sl++;
        }

        $data = array(
            "draw"              => intval($req->input('draw')),
            "recordsTotal"      => $totalData,
            "recordsFiltered"   => $totalData,
            'data'              => $empDesignationList,
        );

        return response()->json($data);
    }


    public function add(Request $req) {

        if ($req->isMethod('post')) {
            $empDesigValid = $this->validateData($req, $operationType = 'store');

            if ($empDesigValid['isValid'] == false) {
                $notification = array(
                    'message' => $empDesigValid['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $empDesignation = new EmpDesignation();
            $empDesignation->name        = $req->name;
            $empDesignation->shortName        = $req->shortName;
            $empDesignation->created_by  = Auth::user()->id;
            $empDesignation->created_at  = Carbon::now();
            $empDesignation->save();

            $notification = array(
                'message' => 'Successfully Inserted',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        } else {
            return view('GNL.EmpDesignation.add');
        }
    }

    public function edit(Request $req) {

        $empDesignationData = EmpDesignation::where('id', $req->desigId)->first();;

        if ($req->isMethod('post')) {
            $empDesigValid = $this->validateData($req, $operationType = 'store');

            if ($empDesigValid['isValid'] == false) {
                $notification = array(
                    'message' => $empDesigValid['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $empDesignation = EmpDesignation::find($empDesignationData->id);
            $empDesignationData->name        = $req->name;
            $empDesignation->shortName        = $req->shortName;
            $empDesignationData->updated_by  = Auth::user()->id;
            $empDesignationData->updated_at  = Carbon::now();
            $empDesignationData->save();

            $notification = array(
                'message' => 'Successfully Updated',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        } else {
            return view('GNL.EmpDesignation.edit',compact('empDesignationData'));
        }
    }


    public function delete(Request $req) {

        $empDesignationData = EmpDesignation::where('id', $req->desigId)->first();
        $empDesigValid = $this->validateData($req, $operationType = 'delete', $empDesignationData);
    
        if ($empDesigValid['isValid'] == false) {
            $notification = array(
                'message' => $empDesigValid['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $empDesignationData = EmpDesignation::find($empDesignationData->id);
        $empDesignationData->is_delete  = 1;
        $delete = $empDesignationData->save();

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

    public function validateData($req, $operationType, $empDesignationData = null)
    {
        $errorMsg = null;

        if ($operationType != 'delete') {
            $validator = Validator::make($req->all(), [
                'name'   => 'required'
            ]);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $empDesigValid = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $empDesigValid;
    }
}
