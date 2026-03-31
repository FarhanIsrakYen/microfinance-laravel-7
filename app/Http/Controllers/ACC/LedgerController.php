<?php

namespace App\Http\Controllers\acc;

use App\Http\Controllers\Controller;
use App\Model\Acc\AccountType;
use App\Model\Acc\Ledger;
use App\Model\GNL\Project;

use App\Services\CommonService as Common;
use App\Services\AccService as ACCS;

use Illuminate\Http\Request;
use Redirect;
use Session;

class LedgerController extends Controller
{
    public function index(Request $request)
    {
        return view('ACC.Ledger.index');
    }

    public function ajaxLedgerTable(Request $request)
    {

        // Role = null ,Ledger_id = null ,BranchID= null,ProjectID= null
        $Role = $request->Role;
        $AccType = $request->AccType;
        $BranchID = $request->BranchID;
        $ProjectID = $request->ProjectID;
        // $BranchID = $request->BranchID;

        $result = ACCS::LedgerHTML($Role, $AccType, $BranchID, $ProjectID);

        return $result;
    }

    public function addin($id = null)
    {
        $LedgerData = Ledger::where(['is_group_head' => 1, 'is_delete' => 0, 'id' => $id])->first();
        $acc_data = AccountType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        $project = Project::where('is_delete', 0)->orderBy('id', 'DESC')->get();

        return view('ACC.Ledger.add', compact('acc_data', 'LedgerData', 'project'));
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'name' => 'required',
            ]);

            $RequestData = $request->all();
            $parent_id = $RequestData['parent_id'];

            // // company Id
            $RequestData['company_id'] = Common::getCompanyId();

            if ($parent_id == 0) {
                $RequestData['level'] = 1;
            } else {

                $Ledgerlevel = Ledger::where('id', $parent_id)->first();
                $RequestData['level'] = $Ledgerlevel->level + 1;

            }
            // generateLedgerSysCode
            // Common::generateBillVoucher

            $RequestData['project_arr'] = implode(',', $RequestData['project_array']);
            $RequestData['branch_arr'] = implode(',', $RequestData['branch_array']);

            if (!empty($RequestData['is_group_head']) && $RequestData['is_group_head'] == "on") {
                $RequestData['is_group_head'] = 1;
            } else {
                $RequestData['is_group_head'] = 0;
            }

            $RequestData['order_by'] = $RequestData['order_by'] + 1;
            $ordered = $RequestData['order_by'];

            $RequestData['sys_code'] = ACCS::generateLedgerSysCode($parent_id, $RequestData['acc_type_id'], $RequestData['is_group_head'], $RequestData['company_id']);

            if (isset($RequestData['code']) == false && empty($RequestData['code'])) {
                $RequestData['code'] = $RequestData['sys_code'];
            }
            $Leger_parent_data = Ledger::where('parent_id', $parent_id)
                ->where('order_by', '>', $ordered - 1)
                ->orderBy('order_by', 'ASC')
                ->get();

            foreach ($Leger_parent_data as $Row) {

                $Row->order_by = $Row->order_by + 1;
                $updateOrder = $Row->save();
            }

            $isInsert = Ledger::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted Ledger Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('acc/ledger')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Ledger',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $Ledger = Ledger::where(['is_group_head' => 1, 'is_delete' => 0])->orderBy('id', 'DESC')->get();
            $acc_data = AccountType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $project = Project::where('is_delete', 0)->orderBy('id', 'DESC')->get();

            return view('ACC.Ledger.add', compact('acc_data', 'Ledger', 'project'));

        }
    }

    public function edit(Request $request, $id = null)
    {

        $data = Ledger::where('id', $id)->first();

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'name' => 'required',
            ]);

            $RequestData = $request->all();
            $parent_id = $RequestData['parent_id'];
            $RequestData['sys_code'] = $data->sys_code;

            // // company Id
            $RequestData['company_id'] = $data->company_id;

            if ($parent_id == 0) {
                $RequestData['level'] = 1;
            } else {

                $Ledgerlevel = Ledger::where('id', $parent_id)->first();
                $RequestData['level'] = $Ledgerlevel->level + 1;

            }

            $RequestData['project_arr'] = implode(',', $RequestData['project_array']);
            $RequestData['branch_arr'] = implode(',', $RequestData['branch_array']);

            if (!empty($RequestData['is_group_head']) && $RequestData['is_group_head'] == "on") {
                $RequestData['is_group_head'] = 1;
            } else {
                $RequestData['is_group_head'] = 0;
            }

            $RequestData['order_by'] = $RequestData['order_by'] + 1;
            $ordered = $RequestData['order_by'];

            

            if ($RequestData['sys_code'] == null || $data->parent_id != $RequestData['parent_id'] || $data->is_group_head != $RequestData['is_group_head'] || $data->company_id != $RequestData['company_id']) {
                
                $RequestData['sys_code'] = ACCS::generateLedgerSysCode($parent_id, $RequestData['acc_type_id'], $RequestData['is_group_head'], $RequestData['company_id']);
                
            }

            if (isset($RequestData['code']) == false && empty($RequestData['code'])) {
                $RequestData['code'] = $RequestData['sys_code'];
            }

            if ($data->order_by != $RequestData['order_by']) {

                if ($data->order_by > $RequestData['order_by']) {
                    $Leger_parent_data = Ledger::where('parent_id', $parent_id)
                        ->where('order_by', '>=', $RequestData['order_by'])
                        ->where('order_by', '<', $data->order_by)
                    // ->whereBetween('order_by', [$RequestData['order_by'], $data->order_by])
                    // ->where('order_by','>', $ordered)
                        ->orderBy('order_by', 'ASC')
                        ->get();

                    foreach ($Leger_parent_data as $Row) {
                        $Row->order_by = $Row->order_by + 1;
                        $updateOrder = $Row->save();
                    }
                    // dd($Leger_parent_data);
                } else {

                    // $RequestData['order_by'] = $RequestData['order_by']-1;
                    $Leger_parent_data = Ledger::where('parent_id', $parent_id)
                        ->where('order_by', '<', $RequestData['order_by'])
                        ->where('order_by', '>', $data->order_by)
                    // ->whereBetween('order_by', [$RequestData['order_by'], $data->order_by])
                    // ->where('order_by','>', $ordered)
                        ->orderBy('order_by', 'ASC')
                        ->get();
                    // dd($Leger_parent_data);

                    foreach ($Leger_parent_data as $Row) {
                        $Row->order_by = $Row->order_by - 1;
                        $updateOrder = $Row->save();
                    }
                    $RequestData['order_by'] = $RequestData['order_by'] - 1;

                }

                //     $Leger_parent_data = Ledger::where('parent_id', $parent_id)
                //     ->where('order_by','>', $ordered)
                //     ->orderBy('order_by', 'ASC')
                //     ->get();
                //          $Temp = 1;
                //     foreach ($Leger_parent_data as $Row ) {

                //             $Row->order_by =$Row->order_by +1;
                //             $updateOrder = $Row->save();

                //     }
                // $Leger_parent_data->save();

            }
            // dd($RequestData);

            // $orderdata = Ledger::where('parent_id', $parent_id)->get();

            $isUpdate = $data->update($RequestData);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Ledger Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('acc/ledger')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to update data in Ledger',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $Ledger = Ledger::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $acc_data = AccountType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $Ledgerdata = Ledger::where('id', $id)->first();

            $project = Project::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('ACC.Ledger.edit', compact('acc_data', 'Ledger', 'Ledgerdata', 'project'));
        }
    }

    public function view($id = null)
    {
        // /$Leger_parent_data = Ledger::where('parent_id', $id)->get();
        $Ledger = Ledger::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        $acc_data = AccountType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        $Ledgerdata = Ledger::where('id', $id)->first();
        $project = Project::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        // return view('ACC.Ledger.edit',compact('acc_data','Ledger','Ledgerdata'));
        return view('ACC.Ledger.view', compact('acc_data', 'Ledger', 'Ledgerdata', 'project'));
    }

    public function delete($id = null)
    {

        $Ledger = Ledger::where('id', $id)->first();
        $Ledger->is_delete = 1;
        $delete = $Ledger->save();

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
        $Ledger = Ledger::where('id', $id)->first();
        if ($Ledger->is_active == 1) {
            $Ledger->is_active = 0;
            # code...
        } else {
            $Ledger->is_active = 1;
        }

        $Status = $Ledger->save();

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
