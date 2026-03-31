<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use App\Model\GNL\Notice;
use App\Model\GNL\Branch;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use DateTime;
use Redirect;

use App\Services\CommonService as Common;
use App\Services\RoleService as Role;

class NoticeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $request) {

        if ($request->ajax()) {

            $columns = array(
                0 => 'id',
                1 => 'notice_title',
                3 => 'start_time',
                4 => 'end_time',
                6 => 'is_active',
            );

            // $totalData = Notice::where('is_delete', 0)->count();
            //
            // $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');


            $branchID = Common::getBranchId();

            $branchList = Branch::where([['is_delete',0], ['is_active', 1], ['is_approve', 1]])
                        ->select('id','branch_name','branch_code')->get();

            $notices = DB::table('gnl_notice as gn')
                ->where('gn.is_delete', 0)
                // ->offset($start)
                // ->limit($limit)
                ->orderBy($order, $dir);
                // ->get();
                $tempQueryData = clone $notices;
                $notices = $notices->offset($start)->limit($limit)->get();

                $totalData = Notice::where([ ['gnl_notice.is_delete', 0], ['gnl_notice.is_active', 1]])->count();
                            
                $totalFiltered = $totalData;
            // $totalFiltered = count($notices);
                $totalFiltered = $tempQueryData->count();
            $DataSet = array();
            $i =$start;

            foreach ($notices as $key => $row) {
                $IgnoreArray = array();

                if (in_array(0, explode(',',$row->branch_id))){
                    $branch_name = "All";
                }
                else{
                    $branch_arr = array();

                    foreach ($branchList as $branch) {
                        if (in_array($branch->id, explode(',',$row->branch_id)) ) {
                            array_push($branch_arr, $branch->branch_name."(".$branch->branch_code.")");
                        }
                    }
                    $branch_name = implode(", ", $branch_arr);
                }

                $status = '<p class="btn btn-warning">Disable</p>';

                if($row->is_active == 1){

                    if($row->notice_period == 1){
                        $status = '<p class="btn btn-success">Enable</p>';
                    }
                    else if($row->notice_period == 2){

                        $systemTime = (new DateTime())->format('Y-m-d H:i');
                        $startTime = (new DateTime($row->start_time))->format('Y-m-d H:i');
                        $endTime = (new DateTime($row->end_time))->format('Y-m-d H:i');

                        if($startTime <= $systemTime && $endTime >= $systemTime){
                            $status = '<p class="btn btn-success">Enable</p>';
                        }
                    }
                }

                $TempSet = array();
                $TempSet = [
                    'id' => ++$i,
                    'name' => $row->notice_title,
                    'branch' => $branch_name,
                    'start_time' => $row->start_time,
                    'end_time' => $row->end_time,
                    'notice' => $row->notice_body,
                    'status' => $status,

                    'action' => Role::roleWiseArray($this->GlobalRole, $row->id, $IgnoreArray, $row->is_active)
                ];

                $DataSet[] = $TempSet;
            }

            $data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => $totalData,
                "recordsFiltered" => intval($totalFiltered),
                'data' => $DataSet,
            );

            return response()->json($data);
        }
        return view('GNL.Notice.index');
    }


    public function add(Request $req) {

        $branchList = Branch::where([['is_delete',0], ['is_active',1], ['is_approve',1]])
            ->orderBy('branch_code', 'ASC')
            ->select('id','branch_name','branch_code')
            ->get();

        if ($req->isMethod('post')) {
            $passport = $this->getPassport($req, $operationType = 'store');

            if ($passport['isValid'] == false) {
                $notification = array(
                    'message' => $passport['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $RequestData = $req->all();

            if(!empty($RequestData['branchId'])){
                $RequestData['branch_id'] = implode(",", $RequestData['branchId']);
            }

            $isInsert = Notice::create($RequestData);

            if($isInsert){
                $notification = array(
                    'message' => 'Successfully Inserted',
                    'alert-type' => 'success',
                );
            }
            else{
                $notification = array(
                    'message' => 'Unsuccessful to record insert.',
                    'alert-type' => 'error',
                );
            }

            return response()->json($notification);

        }
        return view('GNL.Notice.add',compact('branchList'));
    }

    public function edit($id = null, Request $req) {

        $branchList = Branch::where([['is_delete',0], ['is_active',1], ['is_approve',1]])
            ->orderBy('branch_code', 'ASC')
            ->select('id','branch_name','branch_code')
            ->get();

        $noticeData = Notice::where('id', $id)->first();;

        if ($req->isMethod('post')) {
            $passport = $this->getPassport($req, $operationType = 'store');

            if ($passport['isValid'] == false) {
                $notification = array(
                    'message' => $passport['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $RequestData = $req->all();

            if(!empty($RequestData['branchId'])){
                $RequestData['branch_id'] = implode(",", $RequestData['branchId']);
            }

            $isUpdate = $noticeData->update($RequestData);

            if($isUpdate){
                $notification = array(
                    'message' => 'Successfully Updated',
                    'alert-type' => 'success',
                );
            }
            else{
                $notification = array(
                    'message' => 'Unsuccessful to record update.',
                    'alert-type' => 'error',
                );
            }

            return response()->json($notification);

        }
        return view('GNL.Notice.edit',compact('branchList','noticeData'));
    }

    public function view($id = null) {

        $branchList = Branch::where([['is_delete',0], ['is_active',1], ['is_approve',1]])
            ->orderBy('branch_code', 'ASC')
            ->select('id','branch_name','branch_code')
            ->get();

        $noticeData = Notice::where('id', $id)->first();;

        return view('GNL.Notice.view',compact('branchList','noticeData'));
    }

    public function delete($id = null) {

        $noticeData = Notice::where('id', $id)->first();
        $noticeData = Notice::find($noticeData->id);

        $noticeData->is_delete = 1;
        $delete = $noticeData->save();

        if ($delete) {
            $notification = array(
                'message' => 'Successfully Deleted',
                'alert-type' => 'success',
            );
            // return response()->json($notification);
        } else {
            $notification = array(
                'message' => 'Unsuccessful to Delete',
                'alert-type' => 'error',
            );
            // return response()->json($notification);
        }

        return redirect()->back()->with($notification);

    }

    public function getPassport($req, $operationType, $wareaData = null)
    {
        $errorMsg = null;

        if ($operationType != 'delete') {
            $validator = Validator::make($req->all(), [
                'notice_title' => 'required',
                'notice_body' => 'required',
                'branchId'   => 'required',
                'notice_period'  => 'required',
            ]);

            $attributes = array(
                'notice_title' => 'Notice Name',
                'notice_period' => 'Notice Period',
                'notice_body' => 'Notice',
                'branchId' => 'Branch'
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                // $errorMsg = implode(' || ', $validator->messages()->all());
                $errorMsg = implode(' <br /> ', $validator->messages()->all());
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $wareaValid = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $wareaValid;
    }

    public function isActive($id = null)
    {
        $noticeData = Notice::where('id', $id)->first();

        if ($noticeData->is_active == 1) {
            $noticeData->is_active = 0;
        } else {
            $noticeData->is_active = 1;
        }

        $Status = $noticeData->save();

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
}
