<?php

namespace App\Http\Controllers\acc;

use App\Http\Controllers\Controller;
use App\Model\Acc\VoucherType;
use Illuminate\Http\Request;
use Redirect;

class VoucherTypeController extends Controller {
	public function __construct() {
		$this->middleware(['auth', 'permission']);
		parent::__construct();
	}

	public function index(Request $request) {
		if ($request->ajax()) {
			// $columns = array(
			//     0 => 'id',
			//     1 => 'name',
			//     2 => 'parent_id',
			//     3 => 'description',
			//     4 => 'action',
			// );
			// // Datatable Pagination Variable
			// $totalData = AccountType::where('is_delete', '=', 0)->count();
			// $totalFiltered = $totalData;
			// $limit = $request->input('length');
			// $start = $request->input('start');
			// $order = $columns[$request->input('order.0.column')];
			// $dir = $request->input('order.0.dir');

			// // Searching variable
			// $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');

			// $QueryData = AccountType::where('is_delete', '=', 0)
			// // ->whereIn('gnl_branchs.id', HRS::getUserAccesableBranchIds())
			//     ->select('acc_account_type.*')
			//     //->leftJoin('acc_account_type', 'gnl_branchs.company_id', '=', 'gnl_companies.id')
			//     ->where(function ($QueryData) use ($search) {
			//         if (Common::getBranchId() != 1) {
			//             $QueryData->where('gnl_branchs.id', Common::getBranchId());
			//         }
			//         if (!empty($search)) {
			//             $QueryData->where('name', 'LIKE', "%{$search}%")
			//                 ->orWhere('description', 'LIKE', "%{$search}%");
			//         }
			//     })
			//     ->offset($start)
			//     ->limit($limit)
			//     ->orderBy($order, $dir)
			//     ->get();

			// if (!empty($search)) {
			//     $totalFiltered = count($QueryData);
			// }

			// $DataSet = array();
			// $i = 0;

			// foreach ($QueryData as $Row) {
			// $TempSet = array();
			// $TempSet = [
			//     'id' => ++$i,
			//     'name' => $Row->name,
			//     'parent_id' => $Row->parent_id,
			//     'description' => $Row->description,
			//     'action' => ''
			// ];

			// $DataSet[] = $TempSet;
			// }
			// $json_data = array(
			//     "draw" => intval($request->input('draw')),
			//     "recordsTotal" => intval($totalData),
			//     "recordsFiltered" => intval($totalFiltered),
			//     "data" => $DataSet,
			// );

			// echo json_encode($json_data);
		} else {
			$vdata = VoucherType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
			return view('ACC.VoucherType.index', compact('vdata'));
		}
	}

	public function add(Request $request) {

		if ($request->isMethod('post')) {
			$validateData = $request->validate([
				'name' => 'required',
			]);

			$RequestData = $request->all();

			$isInsert = VoucherType::create($RequestData);

			if ($isInsert) {
				$notification = array(
					'message' => 'Successfully Inserted Voucher Type Data',
					'alert-type' => 'success',
				);
				return Redirect::to('acc/voucher_type')->with($notification);
			} else {
				$notification = array(
					'message' => 'Unsuccessful to insert data in Voucher Type',
					'alert-type' => 'error',
				);
				return redirect()->back()->with($notification);
			}
		} else {
			$vdata = VoucherType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
			return view('ACC.VoucherType.add', compact('vdata'));
		}
	}

	public function edit(Request $request, $id = null) {

		$data = VoucherType::where('id', $id)->first();

		if ($request->isMethod('post')) {
			$validateData = $request->validate([
				'name' => 'required',
			]);

			$RequestData = $request->all();

			// if ( !empty($RequestData['is_parent']) && $RequestData['is_parent']== "on"){
			//     $RequestData['is_parent'] = 1;
			// }else{
			//     $RequestData['is_parent'] = 0;
			// }

			$isUpdate = $data->update($RequestData);

			if ($isUpdate) {
				$notification = array(
					'message' => 'Successfully Updated Voucher Type Data',
					'alert-type' => 'success',
				);
				return Redirect::to('acc/voucher_type')->with($notification);
			} else {
				$notification = array(
					'message' => 'Unsuccessful to Update data in Voucher Type',
					'alert-type' => 'error',
				);
				return redirect()->back()->with($notification);
			}
		} else {

			$vdata = VoucherType::where('id', $id)->first();
			//$vdata = VoucherType::where('is_delete', 0)->orderBy('id', 'DESC')->get();
			return view('ACC.VoucherType.edit', compact('vdata'));
		}
	}

	public function view($id = null) {
		$vdata = VoucherType::where('id', $id)->first();
		return view('ACC.VoucherType.view', compact('vdata'));
	}

	public function delete($id = null) {

		$VoucherType = VoucherType::where('id', $id)->first();
		$VoucherType->is_delete = 1;
		$delete = $VoucherType->save();

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

	public function isActive($id = null) {
		$VoucherType = VoucherType::where('id', $id)->first();
		if ($VoucherType->is_active == 1) {
			$VoucherType->is_active = 0;
			# code...
		} else {
			$VoucherType->is_active = 1;
		}

		$Status = $VoucherType->save();

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
