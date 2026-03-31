<?php

namespace App\Http\Controllers\MFN\GConfig;

use App\Services\RoleService;
use App\Http\Controllers\Controller;
use App\Model\MFN\PksfFunds;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Response;
use Redirect;

class PksfFundsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        parent::__construct();
    }

    public function index(Request $req)
    {

        if (!$req->ajax()) {
                
            return view('MFN.PksfFunds.index');
        }

        $columns = ['mfn_pksf_funds.name'];

        $limit = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order = $columns[$orderColumnIndex];
        $dir = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $pksfFundsList = PksfFunds::where('is_delete',0)->select('mfn_pksf_funds.id','mfn_pksf_funds.name')->orderBy($order, $dir);

        if ($search != null) {
            $pksfFundsList->where(function ($query) use ($search) {
                $query->where('mfn_pksf_funds.name', 'LIKE', "%{$search}%");
            });
        }

        $totalData = (clone $pksfFundsList)->count();
        $pksfFundsList = $pksfFundsList->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;
        foreach ($pksfFundsList as $key => $pksfFund) {
            $pksfFundsList[$key]->sl = $sl++;
            $pksfFundsList[$key]->action        = RoleService::roleWiseArray($this->GlobalRole, $pksfFundsList[$key]->id);
        }

        $data = array(
            "draw"              => intval($req->input('draw')),
            "recordsTotal"      => $totalData,
            "recordsFiltered"   => $totalData,
            'data'              => $pksfFundsList,
        );

        return response()->json($data);
    }


    public function add(Request $req) {

        if ($req->isMethod('post')) {
            $pksfFundValid = $this->getPassport($req, $operationType = 'store');

            if ($pksfFundValid['isValid'] == false) {
                $notification = array(
                    'message' => $pksfFundValid['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $pksfFund = new PksfFunds();
            $pksfFund->name        = $req->name;
            $pksfFund->created_by  = Auth::user()->id;
            $pksfFund->created_at  = Carbon::now();
            $pksfFund->save();

            $notification = array(
                'message' => 'Successfully Inserted',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        }
        return view('MFN.PksfFunds.add');
    }

    public function edit(Request $req) {

        $pksfFundData = PksfFunds::where('id', $req->pksfFundId)->first();;

        if ($req->isMethod('post')) {
            $pksfFundValid = $this->getPassport($req, $operationType = 'store');

            if ($pksfFundValid['isValid'] == false) {
                $notification = array(
                    'message' => $pksfFundValid['errorMsg'],
                    'alert-type' => 'error',
                );
                return response()->json($notification);
            }

            $pksfFund = PksfFunds::find($pksfFundData->id);
            $pksfFund->name        = $req->name;
            $pksfFund->updated_by  = Auth::user()->id;
            $pksfFund->updated_at  = Carbon::now();
            $pksfFund->save();

            $notification = array(
                'message' => 'Successfully Updated',
                'alert-type' => 'success',
            );

            return response()->json($notification);

        } 
        return view('MFN.PksfFunds.edit',compact('pksfFundData'));
    }


    public function delete(Request $req) {

        $pksfFundData = PksfFunds::where('id', $req->pksfFundId)->first();
        $passport = $this->getPassport($req, $operationType = 'delete', $pksfFundData);
    
        if ($passport['isValid'] == false) {
            $notification = array(
                'message' => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $pksfFundData = PksfFunds::find($pksfFundData->id);
        $pksfFundData->is_delete  = 1;
        $delete = $pksfFundData->save();

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

    public function getPassport($req, $operationType, $pksfFundData = null)
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


        // IF THE OPERATION IS DELETE THEN CHECK ANY PKSF Fund ID IS PRESENT IN LOAN PRODUCT OR NOT
        // IF YES THEN NO DATA CAN BE DELETED.
        if ($operationType == 'delete') {
            $loanProductExists = DB::table('mfn_loan_products')
                ->where([
                    ['is_delete', 0],
                    ['pksfFundId', $pksfFundData->id],
                ])
                ->exists();

            if ($loanProductExists) {
                $errorMsg = 'Data can not be deleted, Child Table Exist.';
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
