<?php

namespace App\Http\Controllers\GNL;

use App\Http\Controllers\Controller;
use App\Model\GNL\Area;
use App\Model\GNL\Company;
use App\Services\RoleService as Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Redirect;

class AreaController extends Controller
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
                0 => 'ar.id',
                1 => 'ar.area_name',
                2 => 'ar.area_code',
                3 => 'br.branch_name',
                // 4 => 'gnl_companies.comp_name',
                // 4 => 'action',
            );

            $limit = $request->input('length');
            $start = $request->input('start');
            $order = $columns[$request->input('order.0.column')];
            $dir = $request->input('order.0.dir');

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');

            // Query
            $AreaData = DB::table('gnl_areas as ar')
                ->where([['ar.is_delete', 0], ['ar.is_active', 1]])
                ->select('ar.id as id', 'ar.area_name', 'ar.area_code', 'ar.branch_arr')
                ->where(function ($AreaData) use ($search) {
                    if (!empty($search)) {
                        $AreaData->where('ar.area_name', 'LIKE', "%{$search}%")
                            ->orWhere('ar.area_code', 'LIKE', "%{$search}%");
                    }
                })
                ->orderBy($order, $dir)
                ->orderBy('ar.area_code', 'DESC');

            $tempQueryData = clone $AreaData;
            $AreaData = $AreaData->offset($start)->limit($limit)->get();

            $totalData = DB::table('gnl_areas')->where([['is_delete', 0], ['is_active', 1]])->count();
            $totalFiltered = $totalData;

            if (!empty($search)) {
                $totalFiltered = $tempQueryData->count();
            }

            $DataSet = array();
            $sl = $start + 1;

            foreach ($AreaData as $Row) {

                $areaWiseBranchName = DB::table('gnl_branchs')
                    ->where([['is_delete', 0], ['is_active', 1], ['is_approve', 1]])
                    ->whereIn('id', explode(',', $Row->branch_arr))
                    ->select(DB::raw('GROUP_CONCAT(branch_name, "(", branch_code, ")") as branch_name'))
                    ->first();

                $TempSet = array();
                $TempSet = [
                    'id' => $sl++,
                    'area_name' => $Row->area_name,
                    'area_code' => $Row->area_code,
                    'branch_name' => ($areaWiseBranchName) ? $areaWiseBranchName->branch_name : $Row->branch_arr,
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
            return view('GNL.AreaList.index');
        }
    }

    public function add(Request $request)
    {

        if ($request->isMethod('post')) {
            $validateData = $request->validate([
                'area_name' => 'required',
                'area_code' => 'required',
            ]);

            /* Master Table Insertion */
            $RequestData = $request->all();
            $BranchArr = (isset($RequestData['branch_arr']) ? $RequestData['branch_arr'] : array());

            if (count($BranchArr) > 0) {
                $RequestData['branch_arr'] = implode(',', $BranchArr);
            }

            DB::beginTransaction();

            try {

                $isInsert = Area::create($RequestData);

                if ($isInsert) {
                    DB::commit();
                    // return
                    $notification = array(
                        'message' => 'Successfully inserted data in Area List',
                        'alert-type' => 'success',
                    );

                    // return Redirect::to('pos/issue')->with($notification);
                    return Redirect::to('gnl/area')->with($notification);
                }

            } catch (Exception $e) {
                DB::rollBack();
                $notification = array(
                    'message' => 'Unsuccessful to insert data in Area List',
                    'alert-type' => 'error',
                    'console_error' => str_replace("\\", "(DS)", $e->getFile()) . "\\n" . $e->getLine() . "\\n" . $e->getMessage(),
                );
                return redirect()->back()->with($notification);
                //return $e;
            }

        } else {

            $Companies = Company::where('is_delete', 0)
                ->select(['id', 'comp_name'])
                ->orderBy('id', 'DESC')
                ->get();

            return view('GNL.AreaList.add', compact('Companies'));
        }
    }

    public function edit(Request $request, $id = null)
    {
        $AreaData = Area::where('id', $id)->first();

        if ($request->isMethod('post')) {

            $validateData = $request->validate([
                'area_name' => 'required',
                'area_code' => 'required',
            ]);

            $RequestData = $request->all();
            $BranchArr = (isset($RequestData['branch_arr']) ? $RequestData['branch_arr'] : array());

            if (count($BranchArr) > 0) {
                $RequestData['branch_arr'] = implode(',', $BranchArr);
            }

            $isUpdate = $AreaData->update($RequestData);

            if ($isUpdate) {
                $notification = array(
                    'message' => 'Successfully Updated New Area List',
                    'alert-type' => 'success',
                );

                return Redirect::to('gnl/area')->with($notification);
            } else {
                $notification = array(
                    'message' => 'Unsuccessful to Update data in Area List',
                    'alert-type' => 'error',
                );
                return redirect()->back()->with($notification);
            }
        } else {

            $Companies = Company::where('is_delete', 0)
                ->select(['id', 'comp_name'])
                ->orderBy('id', 'DESC')
                ->get();
            return view('GNL.AreaList.edit', compact('Companies', 'AreaData'));
        }

    }

    public function view($id = null)
    {
        $Companies = Company::where('is_delete', 0)
            ->select(['id', 'comp_name'])
            ->orderBy('id', 'DESC')
            ->get();

        $AreaData = Area::where('id', $id)->first();

        return view('GNL.AreaList.view', compact('AreaData', 'Companies'));
    }

    public function delete($id = null)
    {
        $AreaData = Area::where('id', $id)->first();

        $AreaData->is_delete = 1;

        $delete = $AreaData->save();

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
        $AreaData = Area::where('id', $id)->first();

        if ($AreaData->is_active == 1) {
            $AreaData->is_active = 0;
            # code...
        } else {
            $AreaData->is_active = 1;
        }

        $Status = $AreaData->save();

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

    public function ajaxAreaListLoad(Request $request)
    {

        if ($request->ajax()) {

            $Edit = false;
            $BranchArray = array(0);
            $ExceptBranchArray = array(0);

            $AreaID = (isset($request->AreaID)) ? $request->AreaID : false;

            $areaWiseBranch = DB::table('gnl_areas')
                ->where([['is_active', 1], ['is_delete', 0]])
                ->select(DB::raw('GROUP_CONCAT(branch_arr) as branch_arr'))
                ->where(function ($areaWiseBranch) use ($AreaID) {
                    if (!empty($AreaID)) {
                        $areaWiseBranch->where('id', '<>', $AreaID);
                    }
                })
                ->first();

            if ($areaWiseBranch) {
                $ExceptBranchArray = explode(',', $areaWiseBranch->branch_arr);
            }

            if ($AreaID) {
                $Edit = true;

                $selectBranch = DB::table('gnl_areas')
                    ->where([['is_active', 1], ['is_delete', 0], ['id', $AreaID]])
                    ->select(DB::raw('GROUP_CONCAT(branch_arr) as branch_arr'))
                    ->first();

                if ($selectBranch) {
                    $BranchArray = explode(',', $selectBranch->branch_arr);
                }
            }

            $BranchData = DB::table('gnl_branchs')
                ->select(DB::raw('id, branch_name, branch_code'))
                ->where([['is_delete', 0], ['is_active', 1], ['is_approve', 1], ['id', '<>', 1]])
                ->whereNotIn('id', $ExceptBranchArray)
                ->get();

            $output = '<div class="row">';

            if (count($BranchData) > 0) {
                $i = 0;
                foreach ($BranchData as $Branch) {

                    if ($Edit && in_array($Branch->id, $BranchArray)) {
                        $CheckText = 'checked';
                    } else {
                        $CheckText = '';
                    }

                    //$CheckText = '';
                    $output .= '<div class="col-lg-4">';
                    $output .= '<div class="checkbox-custom checkbox-primary">';
                    $output .= '<input type="checkbox" ' . $CheckText . ' class="checkboxs" name="branch_arr[]" id="branch_arr_' . $i . '" value="' . $Branch->id . '" />';
                    $output .= '<label for="branch_arr_' . $i . '" style="color:#000;">' . sprintf('%04d', $Branch->branch_code) . ' - ' . $Branch->branch_name . '</label>';
                    $output .= '</div>';
                    $output .= '</div>';

                    $i++;

                    if (($i % 3) == 0) {
                        $output .= '</div>';
                        $output .= '<div class="row">';
                    }
                }
            } else {

                $output .= '<div class="col-lg-12 text-center">';
                $output .= '<p style="color:red;">No new branch for selecting.</p>';
                $output .= '</div>';
            }

            $output .= '</div>';

            echo $output;
        }
    }

}
