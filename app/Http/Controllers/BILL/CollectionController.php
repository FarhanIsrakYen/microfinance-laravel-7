<?php

namespace App\Http\Controllers\BILL;

use App\Http\Controllers\Controller;
use App\Model\GNL\FiscalYear;
use App\Model\BILL\Collection;

use Auth;
use DateTime;
use DB;
use Illuminate\Http\Request;
use Redirect;

use App\Services\HrService as HRS;
use App\Services\RoleService as Role;
use App\Services\CommonService as Common;
use App\Services\PosService as POSS;

class CollectionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    // Collection List
    public function index(Request $request)
    {

        if ($request->ajax()) {
            // Ordering Variable
            $columns = array(
                'col.id',
                'col.bill_no',
                'cus.customer_name',
                'col.collection_date',
                'col.collection_amount',
                'col.payment_system',
                'emp.emp_name',
            );
            // Datatable Pagination Variable
            $totalData = Collection::where('is_delete', '=', 0)
                ->whereIn('bill_collections.branch_id', HRS::getUserAccesableBranchIds())
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
            $EmployeeID = (empty($request->input('EmployeeID'))) ? null : $request->input('EmployeeID');

            // Query
            $collectionData = DB::table('bill_collections as col')
                ->where([['col.is_delete', 0],['col.is_active', 1]])
                // ->whereIn('col.branch_id', HRS::getUserAccesableBranchIds())
                ->select('col.*', 'cus.customer_name','cus.customer_no', 'emp.emp_name','emp.employee_no')
                ->leftjoin('bill_customers as cus', function ($collectionData) {
                    $collectionData->on('col.customer_id', '=', 'cus.customer_no')
                        ->where([['cus.is_delete', 0], ['cus.is_active', 1]]);

                })
                ->leftjoin('hr_employees as emp', function ($collectionData) {
                    $collectionData->on('col.employee_id', '=', 'emp.employee_no')
                        ->where([['emp.is_delete', 0], ['emp.is_active', 1]]);

                })
                ->where(function ($collectionData) use ($search) {
                    if (!empty($search)) {
                        $collectionData
                            ->where('col.bill_no', 'LIKE', "%{$search}%")
                            ->orWhere('col.collection_date', 'LIKE', "%{$search}%")
                            ->orWhere('cus.customer_name', 'LIKE', "%{$search}%")
                            ->orWhere('emp.emp_name', 'LIKE', "%{$search}%");
                    }
                })
                ->where(function ($collectionData) use ($SDate,$EDate ) {
                    if (!empty($SDate) && !empty($EDate)) {

                        $SDate = new DateTime($SDate);
                        $SDate = $SDate->format('Y-m-d');

                        $EDate = new DateTime($EDate);
                        $EDate = $EDate->format('Y-m-d');

                        $collectionData->whereBetween('col.collection_date', [$SDate, $EDate]);
                    }

                })
                ->where(function ($collectionData) use ($EmployeeID) {
                    if (!empty($EmployeeID)) {
                        $collectionData->where('col.employee_id', '=', $EmployeeID);
                    }

                })
                ->offset($start)
                ->limit($limit)
                ->orderBy('col.collection_date', 'DESC')
                ->orderBy('col.id', 'DESC')
                ->orderBy($order, $dir)
                ->get();

            // dd($collectionData);


            if (!empty($search) || !empty($SDate) || !empty($EDate) || !empty($BranchID) || !empty($EmployeeID)) {
                $totalFiltered = count($collectionData);
            }

            $DataSet = array();
            $i = 0;

            foreach ($collectionData as $Row) {
                $TempSet = array();

                $IgnoreArray = array();

                if(date('d-m-Y', strtotime($Row->collection_date)) != Common::systemCurrentDate($Row->branch_id, 'bill')){
                    $IgnoreArray = ['delete', 'edit'];
                }

                $TempSet = [
                    'id' => ++$i,
                    'bill_no' => $Row->bill_no,
                    'customer_name' => $Row->customer_no ? $Row->customer_name . '('. $Row->customer_no .')' : '',
                    'collection_date' => (new DateTime($Row->collection_date))->format('d-m-Y'),
                    'collection_amount' => $Row->collection_amount,
                    'payment_system' => ($Row->payment_system_id == 1) ? 'Cash' : 'Others',
                    'collection_by' => $Row->employee_no ? $Row->emp_name . '('. $Row->employee_no .')' : '',
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->collection_no),
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

            return view('BILL.Collection.index');
        }
    }

    // Add And store collection Data
    public function add(Request $request)
    {
        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'bill_no' => 'required',
                'collection_amount' => 'required',
            ]);

            $RequestData = $request->all();

            $collection_date = new DateTime($RequestData['collection_date']);
            $RequestData['collection_date'] = $collection_date->format('Y-m-d');

            $fiscal_year = FiscalYear::where([['company_id', $RequestData['company_id']], ['is_delete', 0]])
                ->select('id')
                ->where('fy_start_date', '<=', $RequestData['collection_date'])
                ->where('fy_end_date', '>=', $RequestData['collection_date'])
                ->orderBy('id', 'DESC')
                ->first();

            if ($fiscal_year) {
                $RequestData['fiscal_year_id'] = $fiscal_year->id;
            }

            // Cash Price, Principle Amount, Installment profit Calculation
            // $RequestData['cash_price'] = $RequestData['collection_amount'];
            // $principal_amount = ($RequestData['collection_amount'] / (100 + $RequestData['installment_rate'])) * 100;
            // $installment_profit = ($RequestData['collection_amount'] - $principal_amount);

            // $RequestData['principal_amount'] = $principal_amount;
            // $RequestData['installment_profit'] = $installment_profit;

            if (Collection::where('collection_no', '=', $RequestData['collection_no'])->exists()) {
                $RequestData['collection_no'] = POSS::generateCollectionNo(Common::getBranchId());
            }

            $isInsert = Collection::create($RequestData);

            if ($isInsert) {

                $notification = array(
                    'message' => 'Successfully Inserted in Collection',
                    'alert-type' => 'success',
                );

                return Redirect::to('bill/collection')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Collection',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            return view('BILL.Collection.add');
        }
    }

    // Edit Collection Data
    public function edit(Request $request, $id = null)
    {

        $collectionData = Collection::where('collection_no', $id)->first();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'bill_no' => 'required',
                'collection_amount' => 'required',
            ]);

            $RequestData = $request->all();

            $collection_date = new DateTime($RequestData['collection_date']);
            $RequestData['collection_date'] = $collection_date->format('Y-m-d');

            $fiscal_year = FiscalYear::where([['company_id', $RequestData['company_id']], ['is_delete', 0]])
                ->select('id')
                ->where('fy_start_date', '<=', $RequestData['collection_date'])
                ->where('fy_end_date', '>=', $RequestData['collection_date'])
                ->orderBy('id', 'DESC')
                ->first();

            if ($fiscal_year) {
                $RequestData['fiscal_year_id'] = $fiscal_year->id;
            }

            $isUpdate = $collectionData->update($RequestData);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Collection Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('bill/collection')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update Collection data',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        }
        // $PgroupData = PGroup::where('is_delete', 0)->orderBy('id', 'DESC')->get();

        return view('BILL.Collection.edit', compact('collectionData'));
    }

    // View Collection Data
    public function view($id = null)
    {
        $collectionData = Collection::where('collection_no', $id)->first();
        return view('BILL.Collection.view', compact('collectionData'));
    }

    // Delete Collection Data
    public function delete($id = null)
    {

        $collectionData = Collection::where('collection_no', $id)->first();
        $collectionData->is_delete = 1;
        $delete = $collectionData->save();

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

    // public function destroy($id = null)
    // {
    //     $collectionData = Collection::where(['id' => $id])->get()->each->delete();

    //     if ($collectionData) {
    //         $notification = array(
    //             'message' => 'Successfully Deleted',
    //             'alert-type' => 'success',
    //         );
    //     } else {
    //         $notification = array(
    //             'message' => 'Unsuccessful to Delete',
    //             'alert-type' => 'error',
    //         );
    //     }

    //     return redirect()->back()->with($notification);
    // }

}
