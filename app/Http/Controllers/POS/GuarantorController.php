<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\GNL\Division;
use App\Model\POS\Customer;
use App\Model\POS\Guarantor;
use App\Services\CommonService as Common;
use DateTime;
use Illuminate\Http\Request;
use Redirect;
use App\Services\HrService as HRS;
use App\Services\RoleService as Role;

class GuarantorController extends Controller
{

    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    // List of Guarantor
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $columns = array(
                0 => 'pos_guarantors.id',
                1 => 'pos_guarantors.gr_name',
                2 => 'pos_guarantors.gr_mobile',
                3 => 'pos_guarantors.gr_email',
                6 => 'action',
            );

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
           
            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $CustomerID = (empty($request->input('CustomerID'))) ? null : $request->input('CustomerID');
            $CompID = (empty($request->input('CompID'))) ? null : $request->input('CompID');

            // Query
            $GuarantorData = Guarantor::where([['pos_guarantors.is_delete', '=', 0], ['pos_guarantors.is_active', '=', 1]])
                ->select('pos_guarantors.*',
                    'pos_customers.customer_name','pos_customers.customer_no',
                    'gnl_companies.comp_name')
                ->leftJoin('pos_customers', 'pos_guarantors.customer_id', '=', 'pos_customers.customer_no')
                ->leftJoin('gnl_companies', 'pos_guarantors.company_id', '=', 'gnl_companies.id')

                ->where(function ($GuarantorData) use ($search) {
                    if (!empty($search)) {
                        $GuarantorData->where('pos_guarantors.gr_name', 'LIKE', "%{$search}%")
                            ->orWhere('pos_customers.customer_name', 'LIKE', "%{$search}%")
                            ->orWhere('pos_guarantors.gr_mobile', 'LIKE', "%{$search}%")
                            ->orWhere('pos_guarantors.gr_email', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_companies.comp_name', 'LIKE', "%{$search}%");
                    }
                })
                ->where(function ($GuarantorData) use ($CustomerID) {
                    if (!empty($CustomerID)) {
                        $GuarantorData->where('pos_guarantors.customer_id', '=', $CustomerID);
                    }
                  })
                    ->where(function ($GuarantorData) use ($CompID) {
                        if (!empty($CompID)) {
                            $GuarantorData->where('pos_guarantors.company_id', '=', $CompID);
                        }
                })
                // ->offset($start)
                // ->limit($limit)
                ->orderBy($order, $dir)
                ->orderBy('pos_guarantors.id', 'DESC');
                // ->get();

                $tempQueryData = clone $GuarantorData;
                $GuarantorData = $GuarantorData->offset($start)->limit($limit)->get();

                $totalData = Guarantor::where([['is_delete', 0], ['is_active', 1]])->count();

               $totalFiltered = $totalData;

            if (!empty($search) || !empty($CustomerID) || !empty($CompID)) {
                  $totalFiltered = $tempQueryData->count();

            }

            $DataSet = array();
            $i = $start;

            foreach ($GuarantorData as $Row) {
                $IgnoreArray = array();
                $TempSet = array();
                $GuarantorData = Guarantor::where([['is_active', 1], ['is_delete', 0]])
                // ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                    ->where('customer_id', '=', $Row->id)
                    ->count();
                // dd($GuarantorData);
                if ($GuarantorData > 0) {

                    $IgnoreArray = ['delete', 'edit'];
                }
                $TempSet = [
                    'id' => ++$i,
                    'gr_name' => $Row->gr_name,
                    'gr_mobile' => $Row->gr_mobile,
                    'gr_email' => $Row->gr_email,
                    'customer_name' => (!empty($Row->customer_name)) ? $Row->customer_name."(".$Row->customer_no.")" : "",
                    'comp_name' => $Row->comp_name,
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->guarantor_no, $IgnoreArray),
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
            // $GuarantorData = Guarantor::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('POS.Guarantor.index');
        }
    }

    // Add and Store Guarantor
    public function add(Request $request)
    {
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'company_id' => 'required',
                'customer_id' => 'required',
                'gr_name' => 'required ',
                // 'gr_email' => 'required',
                'gr_mobile' => 'required ',
                'gr_pre_division_id' => 'required',
                'gr_pre_district_id' => 'required ',
                'gr_pre_upazila_id' => 'required',
                'gr_pre_union_id' => 'required ',
                // 'gr_pre_village_id' => 'required',
                'gr_par_division_id' => 'required ',
                'gr_par_district_id' => 'required',
                'gr_par_upazila_id' => 'required ',
                'gr_par_union_id' => 'required ',
                // 'gr_par_village_id' => 'required ',
                'gr_nid' => 'required',
            ]);

            $RequestData = $request->all();

            $RequestData['guarantor_no'] = Common::generateGuarantorNo($RequestData['branch_id']);

            if (!empty($RequestData['gr_dob'])) {
                $RequestData['gr_dob'] = new DateTime($RequestData['gr_dob']);
                $RequestData['gr_dob'] = $RequestData['gr_dob']->format('Y-m-d');
            }

            // dd( $RequestData);
            $isInsert = Guarantor::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted New Guarantor Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/guarantor')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Guarantor',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            $DivisionData = Division::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('POS.Guarantor.add', compact('DivisionData'));

        }
    }

    // Edit Guarantor
    public function edit(Request $request, $id = null)
    {
        $GuarantorData = Guarantor::where('guarantor_no', $id)->first();

        // dd($GuarantorData);

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'customer_id' => 'required',
                'gr_name' => 'required ',
                // 'gr_email' => 'required',
                'gr_mobile' => 'required ',
                'gr_pre_division_id' => 'required',
                'gr_pre_district_id' => 'required ',
                'gr_pre_upazila_id' => 'required',
                'gr_pre_union_id' => 'required ',
                // 'gr_pre_village_id' => 'required',
                'gr_par_division_id' => 'required ',
                'gr_par_district_id' => 'required',
                'gr_par_upazila_id' => 'required ',
                'gr_par_union_id' => 'required ',
                // 'gr_par_village_id' => 'required ',
                'gr_nid' => 'required',
            ]);

            $Data = $request->all();

            if (!empty($Data['gr_dob'])) {
                $Data['gr_dob'] = new DateTime($Data['gr_dob']);
                $Data['gr_dob'] = $Data['gr_dob']->format('Y-m-d');
            }

            $isUpdate = $GuarantorData->update($Data);

            // dd($GuarantorData);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Guarantor Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('pos/guarantor')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Guarantor',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            $CustomerData = Customer::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            $DivisionData = Division::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('POS.Guarantor.edit',
                compact('GuarantorData', 'CustomerData', 'DivisionData'));

        }
    }

    //View Guarantor
    public function view($id = null)
    {
        $GuarantorData = Guarantor::where('guarantor_no', $id)->first();
        $DivisionData = Division::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('POS.Guarantor.view',
            compact('GuarantorData', 'DivisionData'));

    }

    // Soft Delete Guarantor
    public function delete($id = null)
    {
        $GuarantorData = Guarantor::where('guarantor_no', $id)->first();
        $GuarantorData->is_delete = 1;

        $delete = $GuarantorData->save();

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

    // Parmanent Delete Guarantor
    public function destroy($id = null)
    {
        $GuarantorData = Guarantor::where('guarantor_no', $id)->first();
        $delete = $GuarantorData->delete();

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

    // Publish/Unpublish Guarantor
    public function isActive($id = null)
    {
        $GuarantorData = Guarantor::where('guarantor_no', $id)->first();
        if ($GuarantorData->is_active == 1) {
            $GuarantorData->is_active = 0;
        } else {
            $GuarantorData->is_active = 1;
        }

        $Status = $GuarantorData->save();

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
