<?php

namespace App\Http\Controllers\ACC;

use App\Services\CommonService as Common;
use App\Services\RoleService as Role;
use App\Http\Controllers\Controller;
use App\Model\Acc\AccountType;
use Illuminate\Http\Request;
use Redirect;

class AccountTypeController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {

            // dd('tets');
            $columns = array(
                0 => 'id',
                1 => 'name',
                2 => 'parent_id',
                3 => 'description',
                4 => 'action',
            );
            // Datatable Pagination Variable
            $totalData = AccountType::where('is_delete', '=', 0)->count();
            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');

            $QueryData = AccountType::where('is_delete', '=', 0)
            // ->whereIn('gnl_branchs.id', HRS::getUserAccesableBranchIds())
                ->select('acc_account_type.*')
            //->leftJoin('acc_account_type', 'gnl_branchs.company_id', '=', 'gnl_companies.id')
                ->where(function ($QueryData) use ($search) {
                    if (Common::getBranchId() != 1) {
                        $QueryData->where('gnl_branchs.id', Common::getBranchId());
                    }
                    if (!empty($search)) {
                        $QueryData->where('name', 'LIKE', "%{$search}%")
                            ->orWhere('description', 'LIKE', "%{$search}%");
                    }
                })
                // ->offset($start)
                // ->limit($limit)
                ->orderBy($order, $dir);
                // ->get();

            $tempQueryData = clone $QueryData;
            $QueryData = $QueryData->offset($start)->limit($limit)->get();

            if (!empty($search)) {
                $totalFiltered = $tempQueryData->count();
            }

            $DataSet = array();
            $i = $start;

            foreach ($QueryData as $Row) {
                $TempSet = array();

                if($Row->parent_id == 0){
                    $grandparent = "Grand Parent";
                }else{
                    $grandparent =  $Row->GrandParent['name'];
                }
                
                $TempSet = [
                    'id' => ++$i,
                    'name' => $Row->name,
                    'parent_id' => $grandparent,
                    'description' => $Row->description,
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->id, [])
                ];

                // dd($Row->GrandParent['name']);

                $DataSet[] = $TempSet;
            }
            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $DataSet,
            );

            echo json_encode($json_data);
        } else {
            // $acc_data = AccountType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('ACC.AccountType.index');
        }
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'name' => 'required',
            ]);

            $RequestData = $request->all();

            if ($RequestData['is_parent'] == "on") {
                $RequestData['is_parent'] = 1;
            } else {
                $RequestData['is_parent'] = 0;
            }
            //dd($RequestData);

            $isInsert = AccountType::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted Account Type Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('acc/acc_type')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Account Type',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $acc_data = AccountType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('ACC.AccountType.add', compact('acc_data'));
        }
    }

    public function edit(Request $request, $id = null)
    {

        $data = AccountType::where('id', $id)->first();

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'name' => 'required',
            ]);

            $RequestData = $request->all();

            if (!empty($RequestData['is_parent']) && $RequestData['is_parent'] == "on") {
                $RequestData['is_parent'] = 1;
            } else {
                $RequestData['is_parent'] = 0;
            }

            $isUpdate = $data->update($RequestData);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Account Type Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('acc/acc_type')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Account Type',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            // $data = AccountType::where('id', $id)->first();
            $acc_data = AccountType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('ACC.AccountType.edit', compact('acc_data', 'data'));
        }
    }

    public function view($id = null)
    {
        $data = AccountType::where('id', $id)->first();
        $acc_data = AccountType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('ACC.AccountType.view', compact('acc_data', 'data'));
    }

    public function delete($id = null)
    {

        $AccountType = AccountType::where('id', $id)->first();
        $AccountType->is_delete = 1;
        $delete = $AccountType->save();

        if ($delete) {
            $notification = array(
                'message' => 'Successfully Deleted',
                'alert-type' => 'success',
            );
            return redirect()->back()->with($notification);
        } else {
            $notification = array(
                'message' => 'Unsuccessful to Delete',
                'alert-type' => 'error',
            );
            return redirect()->back()->with($notification);
        }
    }

    public function isActive($id = null)
    {
        $AccountType = AccountType::where('id', $id)->first();
        if ($AccountType->is_active == 1) {
            $AccountType->is_active = 0;
            # code...
        } else {
            $AccountType->is_active = 1;
        }

        $Status = $AccountType->save();

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
