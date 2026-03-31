<?php

namespace App\Http\Controllers\BILL;

use App\Http\Controllers\Controller;
use App\Model\POS\Barcode;
use App\Model\BILL\SoftwareAgreementMaster;
use App\Model\BILL\SoftwareAgreementDetails;
use App\Model\BILL\Customer;

use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\RoleService as Role;
use App\Services\BillService as BILLS;

use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;

class SoftwareAgreementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    public function index(Request $request)
    {
        if ($request->ajax()) {
            $columns = [
                'pm.agreement_no',
                'pm.agreement_date',
                'pm.agreement_end_date',
                'sup.customer_name',
                'pm.total_amount',
            ];

            // Datatable Pagination Variable
            $totalData = SoftwareAgreementMaster::where([['is_delete', '=', 0],['is_active',1]])
                ->whereIn('bill_software_agreement_m.branch_id', HRS::getUserAccesableBranchIds())
                ->count();

            $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $SDate = (empty($request->input('SDate'))) ? null : $request->input('SDate');
            $EDate = (empty($request->input('EDate'))) ? null : $request->input('EDate');
            // $BranchID = (empty($request->input('BranchID'))) ? null : $request->input('BranchID');
            $CustomerID = (empty($request->input('CustomerID'))) ? null : $request->input('CustomerID');
            $PGroupID = (empty($request->input('PGroupID'))) ? null : $request->input('PGroupID');
            $CategoryId = (empty($request->input('CategoryId'))) ? null : $request->input('CategoryId');
            $SubCatID = (empty($request->input('SubCatID'))) ? null : $request->input('SubCatID');
            $BrandID = (empty($request->input('BrandID'))) ? null : $request->input('BrandID');

            // Query
            $agreementData = DB::table('bill_software_agreement_m as pm')
                ->where([['pm.is_delete', 0],['pm.is_active',1]])
                ->whereIn('pm.branch_id', HRS::getUserAccesableBranchIds())
                ->select('pm.*', 'br.branch_name', 'sup.customer_name')
                ->leftjoin('gnl_branchs as br', function ($agreementData) {
                    $agreementData->on('pm.branch_id', '=', 'br.id')
                        ->where('br.is_approve', 1);
                })
                ->leftjoin('bill_customers as sup', function ($agreementData) {
                    $agreementData->on('pm.customer_id', '=', 'sup.id');
                })
                ->where(function ($agreementData) use ($search,$SDate, $EDate, $CustomerID, $PGroupID, $CategoryId, $SubCatID, $BrandID) {

                    if (!empty($search)) {
                        $agreementData->where('sup.customer_name', 'LIKE', "%{$search}%")
                            ->orWhere('pm.agreement_no', 'LIKE', "%{$search}%")
                            ->orWhere('br.branch_name', 'LIKE', "%{$search}%")
                            ->orWhere('pm.agreement_date', 'LIKE', "%{$search}%");
                    }

                    if (!empty($SDate) && !empty($EDate)) {

                        $SDate = new DateTime($SDate);
                        $SDate = $SDate->format('Y-m-d');

                        $EDate = new DateTime($EDate);
                        $EDate = $EDate->format('Y-m-d');

                        $agreementData->whereBetween('pm.agreement_date', [$SDate, $EDate]);
                    }

                    if (!empty($CustomerID)) {
                        $agreementData->where('pm.customer_id', '=', $CustomerID);
                    }
                    if (!empty($PGroupID)) {
                        $agreementData->where('prod.prod_group_id', '=', $PGroupID);
                    }
                    if (!empty($CategoryId)) {
                        $agreementData->where('prod.prod_cat_id', '=', $CategoryId);
                    }
                    if (!empty($SubCatID)) {
                        $agreementData->where('prod.prod_sub_cat_id', '=', $SubCatID);
                    }
                    if (!empty($BrandID)) {
                        $agreementData->where('prod.prod_brand_id', '=', $BrandID);
                    }

                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('pm.agreement_date', 'DESC')
                ->orderBy('pm.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($CustomerID) || !empty($PGroupID)
                || !empty($CategoryId) || !empty($SubCatID) || !empty($BrandID)) {
                $totalFiltered = count($agreementData);
            }

            $DataSet = array();
            $i = 0;

            // $RequisitionArray = array();

            foreach ($agreementData as $Row) {

                $TempSet = array();
                $IgnoreArray = array();

                if (date('d-m-Y', strtotime($Row->agreement_date)) != Common::systemCurrentDate($Row->branch_id, 'bill')) {
                    $IgnoreArray = ['delete', 'edit'];
                }

                // if(in_array($Row->requisition_no, $RequisitionArray)){

                //     if(!in_array('edit', $IgnoreArray)){
                //         array_push($IgnoreArray, 'edit');
                //     }
                // }
                // else{
                //     array_push($RequisitionArray, $Row->requisition_no);
                // }

                $TempSet = [
                    'id' => ++$i,
                    'agreement_date' => (new DateTime($Row->agreement_date))->format('d-m-Y'),
                    'agreement_no' => $Row->agreement_no,
                    'customer_name' => $Row->customer_name,
                    'agreement_end_date' => (new DateTime($Row->agreement_end_date))->format('d-m-Y'),
                    'total_amount' => $Row->total_amount,

                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->id, $IgnoreArray),
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
            return view('BILL.SoftwareAgreement.index');
        }
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'agreement_no' => 'required',
                // 'agreement_date' => 'required',
                // 'service_start_date' => 'required',
                'customer_id' => 'required',
                'total_amount' => 'required',
                // 'no_of_branch' => 'required'
            ]);

            $RequestData = $request->all();

            /* Master Table Insertion */

            /* Format date*/
            $agreement_date = new DateTime($RequestData['agreement_date']);
            $RequestData['agreement_date'] = $agreement_date->format('Y-m-d');

            $agreement_end_date = new DateTime($RequestData['agreement_end_date']);
            $RequestData['agreement_end_date'] = $agreement_end_date->format('Y-m-d');

            // fo Software Agreement
            // $RequestData['is_software'] = 1;

            /* Product Data */
            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_type_arr = (isset($RequestData['product_type_arr']) ? $RequestData['product_type_arr'] : array());
            $license_fee_ho_arr = (isset($RequestData['license_fee_ho_arr']) ? $RequestData['license_fee_ho_arr'] : array());
            $license_fee_br_arr = (isset($RequestData['license_fee_br_arr']) ? $RequestData['license_fee_br_arr'] : array());
            $license_fee_arr = (isset($RequestData['license_fee_arr']) ? $RequestData['license_fee_arr'] : array());
            $service_fee_ho_arr = (isset($RequestData['service_fee_ho_arr']) ? $RequestData['service_fee_ho_arr'] : array());
            $service_fee_br_arr = (isset($RequestData['service_fee_br_arr']) ? $RequestData['service_fee_br_arr'] : array());
            $service_fee_arr = (isset($RequestData['service_fee_arr']) ? $RequestData['service_fee_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            $branch_no_arr = (isset($RequestData['branch_no_arr']) ? $RequestData['branch_no_arr'] : array());
            $service_start_date_arr = (isset($RequestData['service_start_date_arr']) ? $RequestData['service_start_date_arr'] : array());


            /*DB begain transaction*/
            DB::beginTransaction();

            try {

                if (DB::table('bill_software_agreement_m')->where('agreement_no', '=', $RequestData['agreement_no'])->exists()) {
                    $RequestData['agreement_no'] = BILLS::generateAgreementNo($RequestData['branch_id']);
                }

                $isInsert = SoftwareAgreementMaster::create($RequestData);

                if ($isInsert) {

                    /* Child Table Insertion */
                    // $RequestData['Agreement_id'] = $isInsert->id;
                    $Data['agreement_no'] = $RequestData['agreement_no'];
                    $Data['company_id'] = $RequestData['company_id'];


                    // start insert 2nd table
                    foreach ($product_id_arr as $key => $product_id_sin) {
                        if (!empty($product_id_sin)) {

                            $Data['product_id'] = $product_id_sin;
                            $Data['product_quantity'] = $product_quantity_arr[$key];
                            $Data['branch_no'] = $branch_no_arr[$key];
                            $Data['service_start_date'] = (new DateTime($service_start_date_arr[$key]))->format('Y-m-d');
                            $Data['license_fee_ho'] = $license_fee_ho_arr[$key];
                            $Data['license_fee_br'] = $license_fee_br_arr[$key];
                            $Data['license_fee'] =  $license_fee_arr[$key];
                            $Data['service_fee_ho'] = $service_fee_ho_arr[$key];
                            $Data['service_fee_br'] = $service_fee_br_arr[$key];
                            $Data['service_fee'] = $service_fee_arr[$key];
                            $Data['total_license_fee'] = $RequestData['total_license_fee'];
                            $Data['total_service_fee'] = $RequestData['total_service_fee'];
                            $Data['total_amount'] = $RequestData['total_amount'];
                            $Data['product_type'] = $product_type_arr[$key];
                            $isInsertM = SoftwareAgreementDetails::create($Data);

                        }
                    }
                    // end insert 2nd table
                }


                // commit DB and return with success masssage
                DB::commit();
                $notification = array(
                    'message' => 'Successfully Inserted Data in Agreement',
                    'alert-type' => 'success',
                );

                return Redirect::to('bill/agreement_us')->with($notification);
            } catch (\Exception $e) {
                dd($e);
                DB::rollBack();
                // role back undo all DB operation
                // return $e file line and error masssage in console log ;
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Agreement',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $customerData = Customer::where(['is_delete' => 0, 'is_active' => 1])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->get();
            return view('BILL.SoftwareAgreement.add', compact('customerData'));
        }
    }

    public function edit(Request $request, $id = null)
    {
        $agreementData = SoftwareAgreementMaster::where('id', $id)->first();
        $agreementDataD = SoftwareAgreementDetails::where('agreement_no', $agreementData->agreement_no)->get();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'agreement_no' => 'required',
                // 'agreement_date' => 'required',
                // 'service_start_date' => 'required',
                'customer_id' => 'required',
                'total_amount' => 'required',
                // 'no_of_branch' => 'required'
            ]);

            /* ---------------------------------- Master Table update start ------------------------- */
            $RequestData = $request->all();

            // Date Format
            $agreement_date = new DateTime($RequestData['agreement_date']);
            $RequestData['agreement_date'] = $agreement_date->format('Y-m-d');

            $agreement_end_date = new DateTime($RequestData['agreement_end_date']);
            $RequestData['agreement_end_date'] = $agreement_end_date->format('Y-m-d');

            //Flag for Software Agreement
            // $RequestData['is_software'] = 1;


            /* Product Data */
            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_type_arr = (isset($RequestData['product_type_arr']) ? $RequestData['product_type_arr'] : array());
            $license_fee_ho_arr = (isset($RequestData['license_fee_ho_arr']) ? $RequestData['license_fee_ho_arr'] : array());
            $license_fee_br_arr = (isset($RequestData['license_fee_br_arr']) ? $RequestData['license_fee_br_arr'] : array());
            $license_fee_arr = (isset($RequestData['license_fee_arr']) ? $RequestData['license_fee_arr'] : array());
            $service_fee_ho_arr = (isset($RequestData['service_fee_ho_arr']) ? $RequestData['service_fee_ho_arr'] : array());
            $service_fee_br_arr = (isset($RequestData['service_fee_br_arr']) ? $RequestData['service_fee_br_arr'] : array());
            $service_fee_arr = (isset($RequestData['service_fee_arr']) ? $RequestData['service_fee_arr'] : array());
            $product_quantity_arr = (isset($RequestData['product_quantity_arr']) ? $RequestData['product_quantity_arr'] : array());
            $branch_no_arr = (isset($RequestData['branch_no_arr']) ? $RequestData['branch_no_arr'] : array());
            $service_start_date_arr = (isset($RequestData['service_start_date_arr']) ? $RequestData['service_start_date_arr'] : array());

            DB::beginTransaction();

            try {
                // Database update start  1st table

                $isUpdate = $agreementData->update($RequestData);

                if ($isUpdate) {


                    /* Delete Agreement details data for this Agreement no */
                    SoftwareAgreementDetails::where('agreement_no', $agreementData->agreement_no)->get()->each->delete();

                    /* Child Table Insertion Start */

                    $Data['agreement_no'] = $RequestData['agreement_no'];
                    $Data['company_id'] = $RequestData['company_id'];


                    // start insert 2nd table
                    foreach ($product_id_arr as $key => $product_id_sin) {
                        if (!empty($product_id_sin)) {

                            $Data['product_id'] = $product_id_sin;
                            $Data['product_quantity'] = $product_quantity_arr[$key];
                            $Data['branch_no'] = $branch_no_arr[$key];
                            $Data['service_start_date'] = (new DateTime($service_start_date_arr[$key]))->format('Y-m-d');
                            $Data['license_fee_ho'] = $license_fee_ho_arr[$key];
                            $Data['license_fee_br'] = $license_fee_br_arr[$key];
                            $Data['license_fee'] =  $license_fee_arr[$key];
                            $Data['service_fee_ho'] = $service_fee_ho_arr[$key];
                            $Data['service_fee_br'] = $service_fee_br_arr[$key];
                            $Data['service_fee'] = $service_fee_arr[$key];
                            $Data['total_license_fee'] = $RequestData['total_license_fee'];
                            $Data['total_service_fee'] = $RequestData['total_service_fee'];
                            $Data['total_amount'] = $RequestData['total_amount'];
                            $Data['product_type'] = $product_type_arr[$key];

                            $isUpdateM = SoftwareAgreementDetails::create($Data);

                        }
                    }

                    /* ------------------- Child Data insertion End ---------------- */
                }

                DB::commit();
                //commit and  return with success massage
                $notification = array(
                    'message' => 'Successfully Update Agreement Data',
                    'alert-type' => 'success',
                );

                return Redirect::to('bill/agreement_us')->with($notification);
            } catch (\Exception $e) {
                dd($e);
                DB::rollBack();
                // return $e file line and error masssage in console log ;
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Agreement',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
            }

            /* ----------------------- Master table Update end ------------------------ */
        } else {
            $customerData = Customer::where(['is_delete' => 0, 'is_active' => 1])
                ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                ->get();
            return view('BILL.SoftwareAgreement.edit', compact('agreementData', 'agreementDataD', 'customerData'));
        }
    }

    public function view($id = null)
    {
        $agreementData = SoftwareAgreementMaster::where('id', $id)->first();
        $agreementDataD = SoftwareAgreementDetails::where('agreement_no', $agreementData->agreement_no)->get();

        $customer = Customer::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('BILL.SoftwareAgreement.view', compact('agreementData', 'agreementDataD', 'customer'));
    }

    public function delete($id = null)
    {
        $agreementData = SoftwareAgreementMaster::where('id', $id)->first();

        if ($agreementData->is_delete == 0) {

            $agreementData->is_delete = 1;
            $isSuccess = $agreementData->update();

            if ($isSuccess) {
                $notification = array(
                    'message' => 'Successfully Deleted',
                    'alert-type' => 'success',
                );
                return redirect()->back()->with($notification);
            }
        }
    }

}
