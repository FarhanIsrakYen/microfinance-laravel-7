<?php

namespace App\Http\Controllers\HR;
use Auth;
use App\Http\Controllers\Controller;
use App\Model\GNL\SysUser;
use App\Model\HR\Bank;
use App\Model\HR\BankBranch;
use Validator;
use App\Services\HrService as HRS;
use App\Services\CommonService as Common;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Redirect;
use App\Services\RoleService as Role;
use DB;
use App\Services\CommonService;

class BankBranchController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    // List of 
    public function index(Request $req)
    {

        if (!$req->ajax()) {
            return view('HR.Bank.Branch.index');
        }
        $columns = [
            'b_name',
            'name',
            'address'
        ];


        $totalData = DB::table('hr_bank_branches')->where('is_delete', '=', 0)->count();
        $totalFiltered = $totalData;
        $start = $req->start;
        $limit = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order = $columns[$orderColumnIndex];
        $dir = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $QueryData = DB::table('hr_bank_branches')
                    ->join('hr_banks', 'hr_banks.id', 'hr_bank_branches.bankId')
                    ->select('hr_banks.name AS b_name','hr_bank_branches.*')
                    ->where('hr_bank_branches.is_delete', 0)
                    ->where(function ($QueryData) use ($search) {
                        
                        if (!empty($search)) {
                            $QueryData->where('hr_bank_branches.name', 'LIKE', "%{$search}%")
                             ->orWhere('hr_banks.name', 'LIKE', "%{$search}%");
                        }
                    })
                    ->offset($start)
                    ->limit($limit)
                    ->orderBy($order, $dir)
                    ->get();
      
        if (!empty($search)) {
            $totalFiltered = count($QueryData);
        }



        $sl = (int)$req->start + 1;
        foreach ($QueryData as $key => $row) {
            $QueryData[$key]->sl = $sl++;
            $QueryData[$key]->id = encrypt($row->id);
        }
        // dd( $QueryData);

        $data = array(
            "draw"              => intval($req->input('draw')),
            "recordsTotal"      => $totalData,
            "recordsFiltered"   => $totalData,
            'data'              => $QueryData,
        );


        return response()->json($data);
    }

    // Add and Store 
    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }

        
        $BankData = Bank::where('is_delete',0)->get();

        
        $data = array(
            "BankData"            => $BankData,
            
        );

        return view('HR.Bank.Branch.add', $data);
    }

    // Edit 
    public function edit(Request $request)
    {

        if ($request->isMethod('post')) {
            return $this->update($request);
        } 

        $BankData = Bank::where('is_delete',0)->get();

     
        $BranchData = BankBranch::find(decrypt($request->id));

        // dd( $BankData);
        
        $data = array(
            "BankData"            => $BankData,
            "BranchData"          => $BranchData,
            
        );
        
        return view('HR.Bank.Branch.edit',$data);

    }

    //View 
    public function view($id = null)
    {
        $BranchData = BankBranch::find(decrypt($id));

        // dd( $BranchData);

        $data = array(
            "BranchData"            => $BranchData,
            
        );

        return view('HR.Bank.Branch.view',$data);
       
    }

    // Soft Delete 
    public function delete(Request $request)
    {
        // dd('decrypt($request->id)');
        $BranchData = BankBranch::find(decrypt($request->id));

        

        $passport = $this->getPassport($request, $operationType = 'delete', $BranchData);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {
            $BranchData->is_delete = 1 ;
        
            $BranchData->update();
            // delete guarantors

            DB::commit();
            $notification = array(
                'message'    => 'Successfully Deleted',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        } catch (\Exception $e) {
            DB::rollback();
            $notification = array(
                'alert-type' => 'error',
                'message'    => 'Something went wrong',
                'consoleMsg' => $e->getFile() . ' ' . $e->getLine() . ' ' . $e->getMessage(),
            );

            return response()->json($notification);
        }
        
       
    }

    public function store(Request $req)
    {
        $passport = $this->getPassport($req, $operationType = 'store');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }
        $RequestData = $req->all();
        // dd($RequestData);   

        DB::beginTransaction();

        try {
            $isInsertDetails = BankBranch::create($RequestData);
            // Your Code here
            DB::commit();
            // return
            $notification = array(
                'message' => 'Successfully inserted Branch',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        } catch (Exception $e) {
            DB::rollBack();
            $notification = array(
                'message' => 'Unsuccessful to inserted Branch',
                'alert-type' => 'error',
                'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
            );
            return response()->json($notification);
            //return $e;
        }

       
    }

    public function update(Request $req)
    {
        $passport = $this->getPassport($req, $operationType = 'update');
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        $PreviousData = BankBranch::find(decrypt($req->id));
        $RequestData = $req->all();
        // dd($RequestData);   

        DB::beginTransaction();

        try {
            $isInsertDetails = $PreviousData->update($RequestData);
            // Your Code here
            DB::commit();
            // return
            $notification = array(
                'message' => 'Successfully updated Branch',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        } catch (Exception $e) {
            DB::rollBack();
            $notification = array(
                'message' => 'Unsuccessful to updated Issue List',
                'alert-type' => 'error',
                'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
            );
            return response()->json($notification);
            //return $e;
        }

       
    }

    // Publish/Unpublish 
    public function isActive($id = null)
    {
        
    }
    public function getPassport($req, $operationType, $ID = null)
    {
        $errorMsg      = null;
        if ($operationType != 'delete') {

            $rules = array(
                'bankId' => 'required',
                'name' => 'required',
                'address' => 'required',
            );
            $validator = Validator::make($req->all(), $rules);

            $attributes = array(
                'bankId' => 'Bank Select',
                'name' => 'Branch Name Rquired',
                'address' => 'Address Required',

            );

            $validator->setAttributeNames($attributes);

            if ($validator->fails()) {
                $errorMsg = implode(' || ', $validator->messages()->all());
            }
        }

        $isValid = $errorMsg == null ? true : false;

        $passport = array(
            'isValid'  => $isValid,
            'errorMsg' => $errorMsg,
        );

        return $passport;
    }

    public function getData(Request $req)
    {
        
    }


}
