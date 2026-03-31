<?php

namespace App\Http\Controllers\HR;
use Auth;
use App\Http\Controllers\Controller;
use App\Model\GNL\SysUser;
use App\Model\HR\Bank;
use App\Services\HrService as HRS;
use App\Services\CommonService as Common;
use DateTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Redirect;
use App\Services\RoleService as Role;
use DB;
use App\Services\CommonService;

class BankController extends Controller
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
            return view('HR.Bank.Bank.index');
        }
        $columns = ['name'];

        $limit = $req->length;
        $orderColumnIndex = (int)$req->input('order.0.column') <= 1 ? 0 : (int)$req->input('order.0.column') - 1;
        $order = $columns[$orderColumnIndex];
        $dir = $req->input('order.0.dir');

        // Searching variable
        $search = (empty($req->input('search.value'))) ? null : $req->input('search.value');

        $BankData = DB::table('hr_banks')
                    ->where('is_delete', 0)
                     ->orderBy($order, $dir);

        if ($search != null) {
            $BankData->where('name', 'LIKE', "%{$search}%");
        }

        $totalData = (clone $BankData)->count();
        $BankData = $BankData->limit($limit)->offset($req->start)->get();

        $sl = (int)$req->start + 1;
        foreach ($BankData as $key => $row) {
            $BankData[$key]->sl = $sl++;
            $BankData[$key]->id = encrypt($row->id);
        }
        // dd( $BankData);

        $data = array(
            "draw"              => intval($req->input('draw')),
            "recordsTotal"      => $totalData,
            "recordsFiltered"   => $totalData,
            'data'              => $BankData,
        );


        return response()->json($data);
    }

    // Add and Store 
    public function add(Request $req)
    {
        if ($req->isMethod('post')) {
            return $this->store($req);
        }
        return view('HR.Bank.Bank.add');
    }

    // Edit 
    public function edit(Request $request)
    {

        if ($request->isMethod('post')) {
            return $this->update($request);
        } 

        
        $BankData = Bank::find(decrypt($request->id));

        // dd( $BankData);
        
        $data = array(
            "BankData"            => $BankData,
            
        );
        
        return view('HR.Bank.Bank.edit',$data);

    }

    //View 
    public function view($id = null)
    {
        $BankData = Bank::find(decrypt($id));

        // dd( $BankData);

        $data = array(
            "BankData"            => $BankData,
            
        );

        return view('HR.Bank.Bank.view',$data);
       
    }

    // Soft Delete 
    public function delete(Request $request)
    {
        // dd('decrypt($request->id)');
        $BankData = Bank::find(decrypt($request->id));

        

        $passport = $this->getPassport($request, $operationType = 'delete', $BankData);
        if ($passport['isValid'] == false) {
            $notification = array(
                'message'    => $passport['errorMsg'],
                'alert-type' => 'error',
            );
            return response()->json($notification);
        }

        DB::beginTransaction();

        try {
            $BankData->is_delete = 1 ;
        
            $BankData->update();
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
            $isInsertDetails = Bank::create($RequestData);
            // Your Code here
            DB::commit();
            // return
            $notification = array(
                'message' => 'Successfully inserted Bank',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        } catch (Exception $e) {
            DB::rollBack();
            $notification = array(
                'message' => 'Unsuccessful to inserted Bank',
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

        $PreviousData = Bank::find(decrypt($req->id));
        $RequestData = $req->all();
        // dd($RequestData);   

        DB::beginTransaction();

        try {
            $isInsertDetails = $PreviousData->update($RequestData);
            // Your Code here
            DB::commit();
            // return
            $notification = array(
                'message' => 'Successfully updated Bank',
                'alert-type' => 'success',
            );

            return response()->json($notification);
        } catch (Exception $e) {
            DB::rollBack();
            $notification = array(
                'message' => 'Unsuccessful to update Bank',
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
                // 'bankId' => 'required',
                'name' => 'required',
                // 'address' => 'required',
            );
            $validator = Validator::make($req->all(), $rules);

            $attributes = array(
                // 'bankId' => 'Bank Select',
                'name' => 'Bank Name Rquired',
                // 'address' => 'Address Required',

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
