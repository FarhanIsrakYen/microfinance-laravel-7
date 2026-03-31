<?php

namespace App\Http\Controllers\GNL;

use App\Services\RoleService as Role;
use App\Http\Controllers\Controller;
use App\Model\GNL\District;
use App\Model\GNL\Division;
use App\Model\GNL\Union;
use App\Model\GNL\Upazila;
use App\Model\GNL\Village;
use Illuminate\Http\Request;
use Redirect;

class AddressController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();
    }

    // Division
    public function divIndex()
    {
        $DivData = Division::where('is_delete', 0)->orderBy('id', 'DESC')->get();

        return view('GNL.Division.index', compact('DivData'));
    }

    public function divAdd(Request $request)
    {
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'division_name' => 'required',
            ]);

            $RequestData = $request->all();

            $isInsert = Division::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted New Division List',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/division')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Division List',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            return view('GNL.Division.add');
        }
    }

    public function divEdit(Request $request, $id = null)
    {
        $DivData = Division::where('id', $id)->first();

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'division_name' => 'required',
            ]);

            $Data = $request->all();

            $isUpdate = $DivData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Division List',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/division')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Division List',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            return view('GNL.Division.edit', compact('DivData'));
        }

    }

    public function divView($id = null)
    {
        $DivData = Division::where('id', $id)->first();
        return view('GNL.Division.view', compact('DivData'));
    }

    public function divDelete($id = null)
    {
        $DivData = Division::where('id', $id)->first();
        $DivData->is_delete = 1;
        $delete = $DivData->save();

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

    public function divIsactive($id = null)
    {
        $DivData = Division::where('id', $id)->first();

        if ($DivData->is_active == 1) {
            $DivData->is_active = 0;
        } else {
            $DivData->is_active = 1;
        }

        $Status = $DivData->save();

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

    //-------------------end Division----------------

    // District

    public function disIndex(Request $request)
    {

        if ($request->ajax()) {

            $columns = array(
                0 => 'gnl_districts.id',
                1 => 'gnl_districts.district_name',
                2 => 'gnl_divisions.division_name',
                3 => 'action',
            );
            // Datatable Pagination Variable
            // $totalData = District::where('gnl_districts.is_delete', '=', 0)->count();
            // $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $divisionID = (empty($request->input('divisionID'))) ? null : $request->input('divisionID');
            // Query
            $districtData = District::where('gnl_districts.is_delete', '=', 0)
                ->select('gnl_districts.*', 'gnl_divisions.division_name as division_name')
                ->leftJoin('gnl_divisions', 'gnl_districts.division_id', '=', 'gnl_divisions.id')
                ->where(function ($districtData) use ($search) {
                    if (!empty($search)) {
                        $districtData->where('gnl_districts.district_name', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_divisions.division_name', 'LIKE', "%{$search}%");
                    }
                })
                ->where(function ($districtData) use ( $divisionID) {
                    if (!empty($divisionID)) {
                        $districtData->where('gnl_districts.division_id', '=', $divisionID);
                    }
                })
                // ->offset($start)
                // ->limit($limit)
                ->orderBy($order, $dir)
                ->orderBy('gnl_districts.id', 'DESC');
                // ->get();
                $tempQueryData = clone $districtData;
                $districtData = $districtData->offset($start)->limit($limit)->get();

                $totalData = District::where([ ['gnl_districts.is_delete', 0], ['gnl_districts.is_active', 1]])->count();

                $totalFiltered = $totalData;

            if (!empty($search)) {
              $totalFiltered = $tempQueryData->count();
            }
            $DataSet = array();
            $i = $start;
            foreach ($districtData as $Row) {

                $TempSet = array();
                $TempSet = [
                    'id' => ++$i,
                    'district_name' => $Row->district_name,
                    'division_name' => $Row->division_name,
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->id, [])
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
            $DisData = District::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('GNL.District.index', compact('DisData'));
        }
    }

    public function disAdd(Request $request)
    {
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'division_id' => 'required',
                'district_name' => 'required',
            ]);

            $RequestData = $request->all();

            $isInsert = District::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted New District List',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/district')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in District List',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $DivData = Division::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('GNL.District.add', compact('DivData'));
        }
        return view('GNL.District.add');
    }

    public function disEdit(Request $request, $id = null)
    {
        $DisData = District::where('id', $id)->first();
        $DivData = Division::where('is_delete', 0)->orderBy('id', 'DESC')->get();

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'division_id' => 'required',
                'district_name' => 'required',
            ]);

            $Data = $request->all();

            $isUpdate = $DisData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated District',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/district')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in District',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            return view('GNL.District.edit', compact('DivData', 'DisData'));
        }
    }

    public function disView($id = null)
    {
        $DisData = District::where('id', $id)->first();
        return view('GNL.District.view', compact('DisData'));
    }

    public function disDelete($id = null)
    {
        $DisData = District::where('id', $id)->first();

        $DisData->is_delete = 1;

        $delete = $DisData->save();

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

    public function disIsactive($id = null)
    {
        $DisData = District::where('id', $id)->first();

        if ($DisData->is_active == 1) {
            $DisData->is_active = 0;
            # code...
        } else {
            $DisData->is_active = 1;
        }

        $Status = $DisData->save();
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

    //-----------End District---------->
    //--------------Upozilla-------------->

    public function upIndex(Request $request)
    {
        if ($request->ajax()) {
            $columns = array(
                0 => 'gnl_upazilas.id',
                1 => 'gnl_upazilas.upazila_name',
                2 => 'gnl_districts.district_name',
                3 => 'gnl_divisions.division_name'
            );
            // Datatable Pagination Variable
            // $totalData = Upazila::where('gnl_upazilas.is_delete', '=', 0)->count();
            // $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $divisionID = (empty($request->input('divisionID'))) ? null : $request->input('divisionID');
            $districtID = (empty($request->input('districtID'))) ? null : $request->input('districtID');

            

            // Query
            $upazilaData = Upazila::where('gnl_upazilas.is_delete', '=', 0)
                ->select('gnl_upazilas.*', 'gnl_districts.district_name','gnl_divisions.division_name')
                ->leftJoin('gnl_districts', 'gnl_upazilas.district_id', '=', 'gnl_districts.id')
                ->leftJoin('gnl_divisions', 'gnl_upazilas.division_id', '=', 'gnl_divisions.id')
                ->where(function ($upazilaData) use ($search) {
                    if (!empty($search)) {
                        $upazilaData->where('gnl_upazilas.upazila_name', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_districts.district_name', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_divisions.division_name', 'LIKE', "%{$search}%");
                    }
                })
                ->where(function ($upazilaData) use ($divisionID) {
                    if (!empty($divisionID)) {
                        $upazilaData->where('gnl_upazilas.division_id', '=', $divisionID);
                    }
                })
                ->where(function ($upazilaData) use ($districtID) {
                    if (!empty($districtID)) {
                        $upazilaData->where('gnl_upazilas.district_id', '=', $districtID);
                    }
                })
                // ->offset($start)
                // ->limit($limit)
                ->orderBy($order, $dir)
                ->orderBy('gnl_upazilas.id', 'DESC');


                $tempQueryData = clone $upazilaData;
                $upazilaData = $upazilaData->offset($start)->limit($limit)->get();

                $totalData = Upazila::where([ ['gnl_upazilas.is_delete', 0], ['gnl_upazilas.is_active', 1]])->count();

                $totalFiltered = $totalData;
            if (!empty($search) || !empty($divisionID) || !empty($districtID)) {
                $totalFiltered = $tempQueryData->count();
            }

            $DataSet = array();
            $i = $start;

            foreach ($upazilaData as $Row) {
                $TempSet = array();
                $TempSet = [
                    'id' => ++$i,
                    'upazila_name' => $Row->upazila_name,
                    'district_name' => $Row->district_name,
                    'division_name' => $Row->division_name,
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->id, [])
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
            $upazilaData = Upazila::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('GNL.Upazila.index', compact('upazilaData'));
        }

    }

    public function upAdd(Request $request)
    {
        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'district_id' => 'required',
                'upazila_name' => 'required',
            ]);

            $RequestData = $request->all();

            $isInsert = Upazila::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted New Upazila List',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/upazila')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Upazila List',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $divData = Division::where([['is_delete', 0],['is_active',1]])->orderBy('id', 'DESC')->get();
            return view('GNL.Upazila.add', compact('divData'));
        }
    }

    public function upEdit(Request $request, $id = null)
    {
        $upazilaData = Upazila::where('id', $id)->first();
        $divData = Division::where([['is_delete', 0],['is_active',1]])->orderBy('id', 'DESC')->get();

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'district_id' => 'required',
                'upazila_name' => 'required',
            ]);

            $Data = $request->all();

            $isUpdate = $upazilaData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Upazila',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/upazila')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Upazila',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            return view('GNL.Upazila.edit', compact('divData', 'upazilaData'));
        }
    }

    public function upView($id = null)
    {
        $upazilaData = Upazila::where('id', $id)->first();
        return view('GNL.Upazila.view', compact('upazilaData'));
    }

    public function upDelete($id = null)
    {
        $upazilaData = Upazila::where('id', $id)->first();
        $upazilaData->is_delete = 1;
        $delete = $upazilaData->save();

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

    public function upIsactive($id = null)
    {
        $upazilaData = Upazila::where('id', $id)->first();
        if ($upazilaData->is_active == 1) {
            $upazilaData->is_active = 0;
        } else {
            $upazilaData->is_active = 1;
        }
        $Status = $upazilaData->save();
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

    //------------------End Upazila------------->

    //-------------------Union----------------->

    public function unionIndex(Request $request)
    {
        if ($request->ajax()) {

            $columns = array(
                0 => 'gnl_unions.id',
                1 => 'gnl_unions.union_name',
                2 => 'gnl_upazilas.upazila_name'
            );
            // Datatable Pagination Variable
            // $totalData = Union::where('gnl_unions.is_delete', '=', 0)->count();
            // $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');
            $sl = $start + 1;

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $divisionID = (empty($request->input('divisionID'))) ? null : $request->input('divisionID');
            $districtID = (empty($request->input('districtID'))) ? null : $request->input('districtID');
            $upazilaID = (empty($request->input('upazilaID'))) ? null : $request->input('upazilaID');
            // Query
            $unionData = Union::where('gnl_unions.is_delete', '=', 0)
                ->select('gnl_unions.*', 'gnl_upazilas.upazila_name as upazila_name','gnl_districts.district_name','gnl_divisions.division_name')
                ->leftJoin('gnl_upazilas', 'gnl_unions.upazila_id', '=', 'gnl_upazilas.id')
                ->leftJoin('gnl_districts', 'gnl_unions.district_id', '=', 'gnl_districts.id')
                ->leftJoin('gnl_divisions', 'gnl_unions.division_id', '=', 'gnl_divisions.id')
                ->where(function ($unionData) use ($search) {
                    if (!empty($search)) {
                        $unionData->where('gnl_unions.union_name', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_upazilas.upazila_name', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_districts.district_name', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_divisions.division_name', 'LIKE', "%{$search}%");
                    }
                })
                ->where(function ($unionData) use ($divisionID) {
                    if (!empty($divisionID)) {
                        $unionData->where('gnl_unions.division_id', '=', $divisionID);
                    }
                })
                ->where(function ($unionData) use ($districtID) {
                    if (!empty($districtID)) {
                        $unionData->where('gnl_unions.district_id', '=', $districtID);
                    }
                })
                ->where(function ($unionData) use ($upazilaID) {
                    if (!empty($upazilaID)) {
                        $unionData->where('gnl_unions.upazila_id', '=', $upazilaID);
                    }
                })
                // ->offset($start)
                // ->limit($limit)
                ->orderBy($order, $dir)
                ->orderBy('gnl_unions.id', 'DESC');

                $tempQueryData = clone $unionData;
                $unionData = $unionData->offset($start)->limit($limit)->get();

                $totalData = Union::where([ ['gnl_unions.is_delete', 0], ['gnl_unions.is_active', 1]])->count();

                $totalFiltered = $totalData;

            if (!empty($search) || !empty($divisionID) || !empty($districtID) || !empty($upazilaID)) {
              $totalFiltered = $tempQueryData->count();
            }

            $DataSet = array();

            foreach ($unionData as $Row) {
                $TempSet = array();
                $TempSet = [
                    'id' => $sl++,
                    'union_name' => $Row->union_name,
                    'upazila_name' => $Row->upazila_name,
                    'district_name' => $Row->district_name,
                    'division_name' => $Row->division_name,
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->id, [])
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
            return view('GNL.Union.index');
        }

    }

    public function unionAdd(Request $request)
    {
        if ($request->isMethod('POST')) {

            $validateData = $request->validate([
                'division_id' => 'required',
                'district_id' => 'required',
                'upazila_id' => 'required',
                'union_name' => 'required'
            ]);
            $RequestData = $request->all();
            // dd($RequestData );
            $isInsert = Union::create($RequestData);

            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted New Union Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/union')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Union',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {
            $divisionData = Division::where([['is_delete', 0],['is_active',1]])->orderBy('id', 'DESC')->get();
            return view('GNL.Union.add', compact('divisionData'));
        }
    }

    public function unionEdit(Request $request, $id = null)
    {
        $unionData = Union::where('id', $id)->first();
        $divisionData = Division::where([['is_delete', 0],['is_active',1]])->orderBy('id', 'DESC')->get();
        $districtData = District::where([['is_delete', 0],['is_active',1]])->orderBy('id', 'DESC')->get();
        $upazilaData = Upazila::where([['is_delete', 0],['is_active',1]])->orderBy('id', 'DESC')->get();

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'division_id' => 'required',
                'district_id' => 'required',
                'upazila_id' => 'required',
                'union_name' => 'required'
            ]);

            $Data = $request->all();
            $isUpdate = $unionData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Union Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/union')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in union',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            return view('GNL.Union.edit', compact('divisionData', 'districtData','upazilaData', 'unionData'));
        }
    }

    public function unionView($id = null)
    {
        $unionData = Union::where('id', $id)->first();
        return view('GNL.Union.view', compact('unionData'));
    }

    public function unionDelete($id = null)
    {
        //        return view('GNL.Dashboard.dashboard');
        $unionData = Union::where('id', $id)->first();
        $unionData->is_delete = 1;
        $delete = $unionData->save();

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

    public function unionIsactive($id = null)
    {
        $unionData = Union::where('id', $id)->first();
        if ($unionData->is_active == 1) {
            $unionData->is_active = 0;
            # code...
        } else {
            $unionData->is_active = 1;
        }

        $Status = $unionData->save();

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

    //------------------Village---------------->

    public function villageIndex(Request $request)
    {
        if ($request->ajax()) {
            $columns = array(
                0 => 'gnl_villages.id',
                1 => 'gnl_villages.village_name',
                2 => 'gnl_unions.union_name',
                3 => 'gnl_upazilas.upazila_name',
                4 => 'gnl_districts.district_name',
                5 => 'gnl_divisions.division_name',

            );
            // Datatable Pagination Variable
            // $totalData = Village::where('gnl_villages.is_delete', '=', 0)->count();
            // $totalFiltered = $totalData;
            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $divisionID = (empty($request->input('divisionID'))) ? null : $request->input('divisionID');
            $districtID = (empty($request->input('districtID'))) ? null : $request->input('districtID');
            $upazilaID = (empty($request->input('upazilaID'))) ? null : $request->input('upazilaID');
            $unionID = (empty($request->input('unionID'))) ? null : $request->input('unionID');

            // Query
            $villageData = Village::where('gnl_villages.is_delete', '=', 0)
                ->select('gnl_villages.*', 'gnl_unions.union_name','gnl_upazilas.upazila_name as upazila_name',
                'gnl_districts.district_name','gnl_divisions.division_name')
                ->leftJoin('gnl_unions', 'gnl_villages.union_id', '=', 'gnl_unions.id')
                ->leftJoin('gnl_upazilas', 'gnl_villages.upazila_id', '=', 'gnl_upazilas.id')
                ->leftJoin('gnl_districts', 'gnl_villages.district_id', '=', 'gnl_districts.id')
                ->leftJoin('gnl_divisions', 'gnl_villages.division_id', '=', 'gnl_divisions.id')
                ->where(function ($villageData) use ($search) {
                    if (!empty($search)) {
                        $villageData->where('gnl_unions.union_name', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_villages.village_name', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_upazilas.upazila_name', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_districts.district_name', 'LIKE', "%{$search}%")
                            ->orWhere('gnl_divisions.division_name', 'LIKE', "%{$search}%");
                    }
                })
                ->where(function ($villageData) use ($divisionID) {
                    if (!empty($divisionID)) {
                        $villageData->where('gnl_villages.division_id', '=', $divisionID);
                    }
                })
                ->where(function ($villageData) use ($districtID) {
                    if (!empty($districtID)) {
                        $villageData->where('gnl_villages.district_id', '=', $districtID);
                    }
                })
                ->where(function ($villageData) use ($upazilaID) {
                    if (!empty($upazilaID)) {
                        $villageData->where('gnl_villages.upazila_id', '=', $upazilaID);
                    }
                })
                ->where(function ($villageData) use ($unionID) {
                    if (!empty($unionID)) {
                        $villageData->where('gnl_villages.union_id', '=', $unionID);
                    }
                })
                // ->offset($start)
                // ->limit($limit)
                ->orderBy($order, $dir)
                ->orderBy('gnl_villages.id', 'DESC');
                // ->get();
                $tempQueryData = clone $villageData;
                $villageData = $villageData->offset($start)->limit($limit)->get();

                $totalData = Village::where([ ['gnl_villages.is_delete', 0], ['gnl_villages.is_active', 1]])->count();

                $totalFiltered = $totalData;
            if (!empty($search) || !empty($divisionID) || !empty($districtID) || !empty($upazilaID) || !empty($unionID)) {
                  $totalFiltered = $tempQueryData->count();
            }

            $DataSet = array();
            $i = $start;

            foreach ($villageData as $Row) {
                $TempSet = array();
                $TempSet = [
                    'id' => ++$i,
                    'village_name' => $Row->village_name,
                    'union_name' => $Row->union_name,
                    'upazila_name' => $Row->upazila_name,
                    'district_name' => $Row->district_name,
                    'division_name' => $Row->division_name,
                    'action' => Role::roleWiseArray($this->GlobalRole, $Row->id, [])
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
            $villageData = Village::where('is_delete', 0)->orderBy('id', 'DESC')->get();
            return view('GNL.Village.index', compact('villageData'));
        }

    }

    public function villageAdd(Request $request)
    {

        if ($request->isMethod('POST')) {
            $validateData = $request->validate([
                'division_id' => 'required',
                'district_id' => 'required',
                'upazila_id' => 'required',
                'union_id' => 'required',
                'village_name' => 'required',
            ]);
            $RequestData = $request->all();
            $isInsert = Village::create($RequestData);
            //dd($isInsert);
            if ($isInsert) {
                $notification = array(
                    'message' => 'Successfully Inserted New Village Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/village')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Village',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }

        } else {
            $divisionData = Division::where([['is_delete', 0],['is_active',1]])->orderBy('id', 'DESC')->get();
            return view('GNL.Village.add', compact('divisionData'));
        }
    }

    public function villageEdit(Request $request, $id = null)
    {
        $villageData = Village::where('id', $id)->first();
        $divisionData = Division::where([['is_delete', 0],['is_active',1]])->orderBy('id', 'DESC')->get();


        if ($request->isMethod('POST')) {
            $validateData = $request->validate([
                'division_id' => 'required',
                'district_id' => 'required',
                'upazila_id' => 'required',
                'union_id' => 'required',
                'village_name' => 'required',
            ]);

            $Data = $request->all();

            $isUpdate = $villageData->update($Data);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated Village Data',
                    'alert-type' => 'success',
                );
                return Redirect::to('gnl/village')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Village',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            return view('GNL.Village.edit', compact('divisionData','villageData'));
        }
    }

    public function villageView($id = null)
    {
        $villageData = Village::where('id', $id)->first();
        return view('GNL.Village.view', compact('villageData'));
    }

    public function villageDelete($id = null)
    {
        // return view('GNL.Dashboard.dashboard');
        $villageData = Village::where('id', $id)->first();
        $villageData->is_delete = 1;
        $delete = $villageData->save();

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

    public function villageIsactive($id = null)
    {
        $villageData = Village::where('id', $id)->first();
        if ($villageData->is_active == 1) {
            $villageData->is_active = 0;
        } else {
            $villageData->is_active = 1;
        }
        $Status = $villageData->save();

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

    //------------------End Village------------->

}
