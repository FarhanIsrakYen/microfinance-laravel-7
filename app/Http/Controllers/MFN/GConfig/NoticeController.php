<?php

namespace App\Http\Controllers\MFN\GConfig;

use App\Http\Controllers\Controller;
use App\Model\MFN\Notice;
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

class NoticeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }
    
    public function index(Request $req) {

        if ($req->ajax()) {
            $systemTime = (Carbon::now())->format('Y-m-d H:i');
            $columns = ['gn.name', 'gn.branchId', 'gn.noticePeriod'];

            $limit = $req->length;
            $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
            $order = $columns[$orderColumnIndex];
            $dir = $req->input('order.0.dir');
            

            $branchID = Auth::user()->branch_id;

            $branchList = Branch::where('is_delete',0)
                        ->select('id','branch_name','branch_code')->get();

            $notices = DB::table('gnl_notice as gn')
                ->where('gn.is_delete', 0)
                ->join('gnl_branchs as gb', function ($notices) {
                    $notices
                        ->on('gb.id', '=', 'gn.branchId')
                        ->where('gb.is_delete', 0);                                     
                })
                ->select('gn.id','gn.name','gn.branchId',
                    DB::raw('CASE
                                WHEN 
                                    ((gn.noticePeriod >= "'.$systemTime . '" or gn.noticePeriod = "' . '0000-00-00 00:00:00'. '") and 
                                    (gn.branchId LIKE "' . "{$branchID},%" .'" or gn.branchId LIKE "' . "%,{$branchID}".'" or gn.branchId LIKE "'."{$branchID}" .'" or gn.branchId LIKE "' . "%,{$branchID},%". '"))
                                THEN "enabled"
                                ELSE  "disabled"
                            END as status'))
                ->orderBy($order, $dir)
                ->limit($limit)
                ->get();

            $totalData = DB::table('gnl_notice')->where('is_delete', 0)->count('id');

            $dataSet = array();
            $sl = 1;
            foreach ($notices as $key => $row) {
                $tempSet = array();
                $branch_arr = array();

                foreach ($branchList as $branch) {
                    if (in_array($branch->id, explode(',',$row->branchId))) {
                        $branch_txt[] = $branch->branch_name;
                        $branch_arr = array_unique(array_merge($branch_arr, $branch_txt));
                    }
                }

                $tempSet = [
                    'id' => $row->id,
                    'sl' => $sl++,
                    'name' => $row->name,
                    'branch' => implode(", ", $branch_arr),
                    'status' => $row->status == 'enabled' ? '<button class="btn btn-success">'.$row->status.'</button>' : 
                                '<button class="btn btn-warning">'.$row->status.'</button>',
                ];
                unset($branch_arr,$branch_txt);
                $dataSet[] = $tempSet;
            }

            $data = array(
                "draw" => intval($req->input('draw')),
                "recordsTotal" => $totalData,
                "recordsFiltered" => $totalData,
                'data' => $dataSet,
            );

            return response()->json($data);
        }
        return view('MFN.Notice.index');
    }


    public function add(Request $req) {

        $branchList = Branch::where('is_delete',0)->orderBy('branch_name')->select('id','branch_name','branch_code')->get();
        if ($req->isMethod('post')) {
            $passport = $this->getPassport($req, $operationType = 'store');

            if ($passport['isValid'] == false) {
                $notification = array(
                    'message' => $passport['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $notice = new Notice();
            $notice->name         = $req->name;
            $notice->branchId     = implode(",", $req->branchId);
            $notice->noticePeriod = $req->noticePeriod ? $req->noticePeriod : '0000-00-00 00:00:00';
            $notice->created_by   = Auth::user()->id;
            $notice->created_at   = Carbon::now();
            $notice->save();

            $notification = array(
                'message' => 'Successfully Inserted',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        } 
        return view('MFN.Notice.add',compact('branchList'));
    }

    public function edit(Request $req) {

        $branchList = Branch::where('is_delete',0)->orderBy('branch_name')->select('id','branch_name','branch_code')->get();
        $noticeData = Notice::where('id', $req->noticeId)->first();;

        if ($req->isMethod('post')) {
            $passport = $this->getPassport($req, $operationType = 'store');

            if ($passport['isValid'] == false) {
                $notification = array(
                    'message' => $passport['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $notice = Notice::find($noticeData->id);

            $notice->name         = $req->name;
            $notice->branchId     = implode(",", $req->branchId);
            $notice->noticePeriod = $req->noticePeriod ? $req->noticePeriod : '0000-00-00 00:00:00';
            $notice->updated_by   = Auth::user()->id;
            $notice->updated_at   = Carbon::now();
            $notice->save();

            $notification = array(
                'message' => 'Successfully Updated',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        } 
        return view('MFN.Notice.edit',compact('branchList','noticeData'));
    }

    public function delete(Request $req) {

        $noticeData = Notice::where('id', $req->noticeId)->first();
        $noticeData = Notice::find($noticeData->id);

        $noticeData->is_delete = 1;
        $delete = $noticeData->save();

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

        if ($operationType != 'delete') {
            $validator = Validator::make($req->all(), [
                'name'       => 'required',
                'branchId'   => 'required',
                'active_till'  => 'required',
            ]);

            $attributes = array(
                'name' => 'Notice Name',
            );
            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $wareaValid = array(
            'isValid' => $isValid,
            'errorMsg' => $errorMsg
        );

        return $wareaValid;
    }
}
