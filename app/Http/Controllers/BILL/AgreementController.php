<?php

namespace App\Http\Controllers\BILL;

use App\Http\Controllers\Controller;
use App\Model\POS\Barcode;
use App\Model\BILL\AgreementMaster;
use App\Model\BILL\AgreementDetails;
use App\Model\BILL\Customer;

use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\RoleService as Role;
use App\Services\BillService as BILLS;

use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;

class AgreementController extends Controller
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
                'pm.service_start_date',
                'sup.customer_name',
                'pm.total_amount',
            ];

            // Datatable Pagination Variable
            $totalData = AgreementMaster::where('is_delete', '=', 0)
                ->whereIn('bill_agreement_m.branch_id', HRS::getUserAccesableBranchIds())
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
            $agreementData = DB::table('bill_agreement_m as pm')
                ->where(['pm.is_delete' => 0])
                ->whereIn('pm.branch_id', HRS::getUserAccesableBranchIds())
                ->select('pm.*', 'br.branch_name', 'sup.customer_name')
                ->leftjoin('gnl_branchs as br', function ($agreementData) {
                    $agreementData->on('pm.branch_id', '=', 'br.id')
                        ->where('br.is_approve', 1);
                })
                ->leftjoin('bill_customers as sup', function ($agreementData) {
                    $agreementData->on('pm.customer_id', '=', 'sup.id');
                })
                ->where(function ($agreementData) use ($search, $SDate, $EDate, $CustomerID, $PGroupID, $CategoryId, $SubCatID, $BrandID) {

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
                    if (!empty($CategoryId)) {
                        $agreementData->where('prod.prod_cat_id', '=', $CategoryId);
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
                    'service_start_date' => (new DateTime($Row->service_start_date))->format('d-m-Y'),
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
            return view('BILL.Agreement.index');
        }
    }

    public function add(Request $request)
    {
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'agreement_no' => 'required',
                'agreement_date' => 'required',
                'service_start_date' => 'required',
                'customer_id' => 'required',
                'total_amount' => 'required',
            ]);

            $RequestData = $request->all();

            /* Master Table Insertion */

            /* Format date*/
            $agreement_date = new DateTime($RequestData['agreement_date']);
            $RequestData['agreement_date'] = $agreement_date->format('Y-m-d');

            $service_start_date = new DateTime($RequestData['service_start_date']);
            $RequestData['service_start_date'] = $service_start_date->format('Y-m-d');

            /* Product Data */
            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_type_arr = (isset($RequestData['product_type_arr']) ? $RequestData['product_type_arr'] : array());
            $license_fee_arr = (isset($RequestData['license_fee_arr']) ? $RequestData['license_fee_arr'] : array());
            $service_fee_arr = (isset($RequestData['service_fee_arr']) ? $RequestData['service_fee_arr'] : array());

            /*DB begain transaction*/
            DB::beginTransaction();

            try {

                if (DB::table('bill_agreement_m')->where('agreement_no', '=', $RequestData['agreement_no'])->exists()) {
                    $RequestData['agreement_no'] = BILLS::generateAgreementNo($RequestData['branch_id']);
                }

                $isInsert = AgreementMaster::create($RequestData);

                if ($isInsert) {

                    /* Child Table Insertion */
                    // $RequestData['Agreement_id'] = $isInsert->id;
                    $Data['agreement_no'] = $RequestData['agreement_no'];
                    $Data['company_id'] = $RequestData['company_id'];


                    // start insert 2nd table
                    foreach ($product_id_arr as $key => $product_id_sin) {
                        if (!empty($product_id_sin)) {

                            $Data['product_id'] = $product_id_sin;
                            $Data['license_fee'] = $license_fee_arr[$key];
                            $Data['service_fee'] = $service_fee_arr[$key];
                            $Data['total_amount'] = $RequestData['total_amount'];
                            $Data['product_type'] = $product_type_arr[$key];
                            $isInsertM = AgreementDetails::create($Data);

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

                return Redirect::to('bill/agreement')->with($notification);
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
            return view('BILL.Agreement.add', compact('customerData'));
        }
    }

    public function edit(Request $request, $id = null)
    {
        $agreementData = AgreementMaster::where('id', $id)->first();
        $agreementDataD = AgreementDetails::where('agreement_no', $agreementData->agreement_no)->get();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'agreement_no' => 'required',
                'agreement_date' => 'required',
                'service_start_date' => 'required',
                'customer_id' => 'required',
                'total_amount' => 'required',
            ]);

            /* ---------------------------------- Master Table update start ------------------------- */
            $RequestData = $request->all();

            // Date Format
            $agreement_date = new DateTime($RequestData['agreement_date']);
            $RequestData['agreement_date'] = $agreement_date->format('Y-m-d');

            $service_start_date = new DateTime($RequestData['service_start_date']);
            $RequestData['service_start_date'] = $service_start_date->format('Y-m-d');

            /* Product Data */
            $product_id_arr = (isset($RequestData['product_id_arr']) ? $RequestData['product_id_arr'] : array());
            $product_type_arr = (isset($RequestData['product_type_arr']) ? $RequestData['product_type_arr'] : array());
            $license_fee_arr = (isset($RequestData['license_fee_arr']) ? $RequestData['license_fee_arr'] : array());
            $service_fee_arr = (isset($RequestData['service_fee_arr']) ? $RequestData['service_fee_arr'] : array());

            DB::beginTransaction();

            try {
                // Database update start  1st table

                $isUpdate = $agreementData->update($RequestData);

                if ($isUpdate) {


                    /* Delete Agreement details data for this Agreement no */
                    AgreementDetails::where('agreement_no', $agreementData->agreement_no)->get()->each->delete();

                    /* Child Table Insertion Start */

                    $Data['agreement_no'] = $RequestData['agreement_no'];
                    $Data['company_id'] = $RequestData['company_id'];


                    // start insert 2nd table
                    foreach ($product_id_arr as $key => $product_id_sin) {
                        if (!empty($product_id_sin)) {

                            $Data['product_id'] = $product_id_sin;
                            $Data['license_fee'] = $license_fee_arr[$key];
                            $Data['service_fee'] = $service_fee_arr[$key];
                            $Data['total_amount'] = $RequestData['total_amount'];
                            $Data['product_type'] = $product_type_arr[$key];

                            // dd($Data['product_type']);
                            $isUpdateM = AgreementDetails::create($Data);

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

                return Redirect::to('bill/agreement')->with($notification);
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
            return view('BILL.Agreement.edit', compact('agreementData', 'agreementDataD', 'customerData'));
        }
    }

    public function view($id = null)
    {
        $agreementData = AgreementMaster::where('id', $id)->first();
        $agreementDataD = AgreementDetails::where('agreement_no', $agreementData->agreement_no)->get();

        $customer = Customer::where('is_delete', 0)->orderBy('id', 'DESC')->get();
        return view('BILL.Agreement.view', compact('agreementData', 'agreementDataD', 'customer'));
    }

    public function delete($id = null)
    {
        $agreementData = AgreementMaster::where('id', $id)->first();

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

    public function ajaxLoadProductForAgreement(Request $request)
    {

        if ($request->ajax()) {

            $queryData = DB::table('bill_products')
                ->where([['is_delete', 0], ['is_active', 1]])
                ->select('id','product_name','sale_price', 'prod_vat')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($queryData as $Row) {

                $SelectText = '';
                $output .= '<option value="' . $Row->id . '"  pname="' . $Row->product_name . '"    pcprice="' . $Row->sale_price . '"  type="' . 1 . '" ' . $SelectText . '>' . $Row->product_name . '</option>';
            }

            echo $output;
        }
    }

    public function ajaxLoadPackageForAgreement(Request $request)
    {

        if ($request->ajax()) {

            $queryData = DB::table('bill_packages')
                ->where([['is_delete', 0], ['is_active', 1]])
                ->select('id','package_name','package_price')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($queryData as $Row) {

                $SelectText = '';
                $output .= '<option value="' . $Row->id . '" pname="' . $Row->package_name . '"  pcprice="' . $Row->package_price . '"  type="' . 2 .  '" ' . $SelectText . '>' . $Row->package_name . '</option>';
            }

            echo $output;
        }
    }

}
