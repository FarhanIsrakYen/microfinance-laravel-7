<?php

namespace App\Http\Controllers\GNL;

use App\Services\CommonService as Common;
use App\Http\Controllers\Controller;
use App\Model\GNL\Branch;
use App\Model\GNL\Group;
use DateTime;
use Illuminate\Http\Request;
use Redirect;
use App\Services\RoleService as Role;

class BranchController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {

            $columns = array(
                0 => 'gnl_branchs.id',
                1 => 'gnl_branchs.branch_name',
                2 => 'gnl_branchs.branch_code',
                3 => 'gnl_branchs.contact_person',
                4 => 'gnl_branchs.branch_phone',
                5 => 'gnl_branchs.branch_opening_date',
                6 => 'gnl_branchs.soft_start_date',
                7 => 'gnl_companies.comp_name',
                8 => 'action',
            );
            // Datatable Pagination Variable
            // $totalData = Branch::where('gnl_branchs.is_delete', '=', 0)->count();
            // $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');

            // Query
            $BranchData = Branch::where('gnl_branchs.is_delete', '=', 0)
            // ->whereIn('gnl_branchs.id', HRS::getUserAccesableBranchIds())
                ->select('gnl_branchs.*', 'gnl_companies.comp_name')
                ->leftJoin('gnl_companies', 'gnl_branchs.company_id', '=', 'gnl_companies.id')
                ->where(function ($BranchData) use ($search) {
                    if (Common::getBranchId() != 1) {
                        $BranchData->where('gnl_branchs.id', Common::getBranchId());
                    }
                    if (!empty($search)) {
                        $BranchData->where('gnl_branchs.branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_branchs.branch_code', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_branchs.contact_person', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_branchs.branch_opening_date', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_branchs.soft_start_date', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_companies.comp_name', 'LIKE', "%{$search}%");
                    }
                })
                // ->offset($start)
                // ->limit($limit)
                ->orderBy($order, $dir)
                ->orderBy('gnl_branchs.branch_code', 'ASC');
                // ->get();
                $tempQueryData = clone $BranchData;
                $BranchData = $BranchData->offset($start)->limit($limit)->get();

                $totalData = Branch::where([ ['gnl_branchs.is_delete', 0], ['gnl_branchs.is_active', 1]])->count();

                $totalFiltered = $totalData;

            if (!empty($search)) {
                $totalFiltered = $tempQueryData->count();
            }

            $DataSet = array();
            $i = $start;

            foreach ($BranchData as $Row) {

                $IgnoreArray = array();

                if($Row->id == 1){
                    $IgnoreArray = ['delete'];
                }

                $ApproveText = ($Row->is_approve == 1) ?
                '<span class="text-primary">Approved</span>' :
                '<span class="text-danger">Pending</span>';

                $OpeningDateText = "<p><b>Branch:</b> " . date('d-m-Y', strtotime($Row->branch_opening_date)) . "</p>";
                $OpeningDateText .= "<p><b>Software:</b> " . date('d-m-Y', strtotime($Row->soft_start_date)) . "</p>";
                $contInfo = "<p><b>Person:</b>" . $Row->contact_person . "</p>";
                $contInfo .= " <p><b>Mobile:</b>" . $Row->branch_phone . "</p>";
                $TempSet = array();
                $TempSet = [
                    'id' => ++$i,
                    'branch_name' => $Row->branch_name,
                    'branch_code' => $Row->branch_code,
                    'Contact Info' => $contInfo,
                    'opening Date' => $OpeningDateText,
                    'comp_name' => $Row->comp_name,
                    'approved' => $ApproveText,
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->id, $IgnoreArray, null, $Row->is_approve)
                ];

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
            return view('GNL.Branch.index');
        }
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'branch_name' => 'required',
            ]);

            $RequestData = $request->all();
            $RequestData['branch_opening_date'] = (new DateTime($RequestData['branch_opening_date']))->format('Y-m-d');
            $RequestData['soft_start_date'] = (new DateTime($RequestData['soft_start_date']))->format('Y-m-d');
            $RequestData['acc_start_date'] = (new DateTime($RequestData['acc_start_date']))->format('Y-m-d');
            $RequestData['mfn_start_date'] = (new DateTime($RequestData['mfn_start_date']))->format('Y-m-d');
            $RequestData['fam_start_date'] = (new DateTime($RequestData['fam_start_date']))->format('Y-m-d');
            $RequestData['inv_start_date'] = (new DateTime($RequestData['inv_start_date']))->format('Y-m-d');
            $RequestData['proc_start_date'] = (new DateTime($RequestData['proc_start_date']))->format('Y-m-d');
            $RequestData['bill_start_date'] = (new DateTime($RequestData['bill_start_date']))->format('Y-m-d');
            $RequestData['hr_start_date'] = (new DateTime($RequestData['hr_start_date']))->format('Y-m-d');

            $isInsert = Branch::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted Branch Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/branch')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Branch',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $GroupData = Group::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('GNL.Branch.add', compact('GroupData'));
        }
    }

    public function edit(Request $request, $id = null)
    {

        $BranchData = Branch::where('id', $id)->first();

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'branch_name' => 'required',
            ]);

            $Data = $request->all();

            if(isset($Data['branch_opening_date'])){
                $Data['branch_opening_date'] = (new DateTime($Data['branch_opening_date']))->format('Y-m-d');
            }

            if(isset($Data['soft_start_date'])){
                $Data['soft_start_date'] = (new DateTime($Data['soft_start_date']))->format('Y-m-d');
            }

            if(isset($Data['acc_start_date'])){
                $Data['acc_start_date'] = (new DateTime($Data['acc_start_date']))->format('Y-m-d');
            }

            if(isset($Data['mfn_start_date'])){
                $Data['mfn_start_date'] = (new DateTime($Data['mfn_start_date']))->format('Y-m-d');
            }

            if(isset($Data['fam_start_date'])){
                $Data['fam_start_date'] = (new DateTime($Data['fam_start_date']))->format('Y-m-d');
            }

            if(isset($Data['inv_start_date'])){
                $Data['inv_start_date'] = (new DateTime($Data['inv_start_date']))->format('Y-m-d');
            }

            if(isset($Data['proc_start_date'])){
                $Data['proc_start_date'] = (new DateTime($Data['proc_start_date']))->format('Y-m-d');
            }

            if(isset($Data['bill_start_date'])){
                $Data['bill_start_date'] = (new DateTime($Data['bill_start_date']))->format('Y-m-d');
            }

            if(isset($Data['hr_start_date'])){
                $Data['hr_start_date'] = (new DateTime($Data['hr_start_date']))->format('Y-m-d');
            }

            $isUpdate = $BranchData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Branch Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/branch')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Branch',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            // $BranchData = Branch::where('id', $id)->first();
            $GroupData = Group::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('GNL.Branch.edit', compact('BranchData', 'GroupData'));
        }
    }

    public function view($id = null)
    {
        $BranchData = Branch::where('id', $id)->first();
        return view('GNL.Branch.view', compact('BranchData'));
    }

    public function delete($id = null)
    {

        $BranchData = Branch::where('id', $id)->first();
        $BranchData->is_delete = 1;
        $delete = $BranchData->save();

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

    public function isApprove($id = null)
    {

        $BranchData = Branch::where('id', $id)->first();

        $BranchData->is_approve = 1;
        //        if ($BranchData->is_approve == 0) {
        //            $BranchData->is_approve = 1;
        //        } else {
        //            $BranchData->is_approve = 0;
        //        }
        $Status = $BranchData->save();

        if ($Status) {
            $notification = array(
                'message' => 'Successfully approved',
                'alert-type' => 'success',
            );
            return redirect()->back()->with($notification);
        } else {
            $notification = array(
                'message' => 'Unsuccessful to approve',
                'alert-type' => 'error',
            );
            return redirect()->back()->with($notification);
        }
    }

    public function isActive($id = null)
    {
        $BranchData = Branch::where('id', $id)->first();
        if ($BranchData->is_active == 1) {
            $BranchData->is_active = 0;
            # code...
        } else {
            $BranchData->is_active = 1;
        }

        $Status = $BranchData->save();

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
