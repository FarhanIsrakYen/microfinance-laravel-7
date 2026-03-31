<?php

namespace App\Http\Controllers;

use App\Model\Acc\Voucher;
use App\Model\GNL\Area;
use App\Model\GNL\Branch;
use App\Model\GNL\MapZoneArea;
use App\Model\POS\Collection;
use App\Model\POS\Customer;
use App\Model\POS\PProcessingFee;
use App\Model\POS\Product;
use App\Model\POS\SalesMaster;
use App\Model\POS\Supplier;
use App\Services\AccService as ACCS;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\InvService as INVS;
use App\Services\PosService as POSS;
use Datetime;
use Facade\Ignition\QueryRecorder\Query;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Picqer;

class AjaxController extends Controller
{

    /**
     * ajaxSelectBoxLoad method used for Select Box loading data, This method is called via ajax
     * @param TableName - @type - String - Database table name
     * @param WhereColumn - Database column name,
     *            this variable is used in where condition of selecting uery
     * @param FeildVal - This variable is used in where condition for column value
     * @param SelectColumn - Database column name, this variable is used in select option for selecting uery
     * @param SelectedVal - This variable is used in edit page section,
     *          when SelectedVal find, its match Query data and if match its return selected text into selecting option
     */

    public function ajaxSelectBoxLoad(Request $request)
    {

        if ($request->ajax()) {

            $FeildVal = $request->FeildVal;
            $TableName = base64_decode($request->TableName);
            $WhereColumn = base64_decode($request->WhereColumn);
            $SelectColumn = base64_decode($request->SelectColumn);
            $SelectArr = explode(',', $SelectColumn);
            $PrimaryKey = $SelectArr[0];
            $DisplayKey1 = $SelectArr[1];

            $isActive = $request->isActive;
            
            $DisplayKey2 = '';

            if (isset($SelectArr[2])) {
                $DisplayKey2 = $SelectArr[2];
            }

            $SelectedVal = $request->SelectedVal;

//             , 'is_active' => 1
// , 'is_active' => 1

            // Query
            if (!empty($DisplayKey2)) {
                $QueryData = DB::table($TableName)
                    ->where([$WhereColumn => $FeildVal, 'is_delete' => 0])
                    ->where(function ($QueryData) use ($isActive) {
                        if ($isActive != 'isActiveOff') {
                            $QueryData->where('is_active', 1);
                        }
                     })
                    ->select([$PrimaryKey, $DisplayKey1, $DisplayKey2])
                // ->orderBy([$SelectArr[1] => 'ASC'])
                    ->get();
            } else {
                $QueryData = DB::table($TableName)
                    ->where([$WhereColumn => $FeildVal, 'is_delete' => 0])
                    ->where(function ($QueryData) use ($isActive) {
                        if ($isActive != 'isActiveOff') {
                            $QueryData->where('is_active', 1);
                        }
                     })
                    ->select([$PrimaryKey, $DisplayKey1])
                // ->orderBy([$SelectArr[1] => 'ASC'])
                    ->get();
            }

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $SelectText = '';

                if ($SelectedVal != null) {
                    if ($SelectedVal == $Row->$PrimaryKey) {
                        $SelectText = 'selected="selected"';
                    }
                }
                if ($DisplayKey2 != null) {
                    $output .= '<option value="' . $Row->$PrimaryKey . '" ' . $SelectText . '>' . $Row->$DisplayKey1 . ' - ' . $Row->$DisplayKey2 . '</option>';
                } else {
                    $output .= '<option value="' . $Row->$PrimaryKey . '" ' . $SelectText . '>' . $Row->$DisplayKey1 . '</option>';
                }

            }

            echo $output;
        }
    }

    public function ajaxSelectBoxLoadObj(Request $request)
    {

        if ($request->ajax()) {

            $FeildVal = $request->FeildVal;
            $TableName = base64_decode($request->TableName);
            $WhereColumn = base64_decode($request->WhereColumn);
            $SelectColumn = base64_decode($request->SelectColumn);
            $SelectArr = explode(',', $SelectColumn);
            // dd($SelectArr);

            $PrimaryKey = $SelectArr[0];
            $DisplayKey = $SelectArr[1];

            // $SelectedVal = $request->SelectedVal;

            // Query
            $QueryData = DB::table($TableName)
                ->where([$WhereColumn => $FeildVal, 'is_delete' => 0])
                ->select([$PrimaryKey, $DisplayKey])
            // ->orderBy([$SelectArr[1] => 'ASC'])
                ->get();

            // dd($QueryData);

            // if(count($QueryData->toarray()) > 0){
            //     return  $QueryData;
            // }
            // else{
            //     return false;
            // }

            return $QueryData;
        }
    }

    public function ajaxSelectBoxLoadForTargetBranch(Request $request)
    {

        if ($request->ajax()) {
            // dd("ok");
            $FeildVal = $request->FeildVal;
            $TableName = base64_decode($request->TableName);
            $WhereColumn = base64_decode($request->WhereColumn);
            $SelectColumn = base64_decode($request->SelectColumn);
            $SelectArr = explode(',', $SelectColumn);
            $PrimaryKey = $SelectArr[0];
            $DisplayKey = $SelectArr[1];
            // Query
            $QueryData = DB::table($TableName)
                ->select([$PrimaryKey, $DisplayKey, 'code'])
                ->where(['is_delete' => 0, 'is_group_head' => 0])
                ->whereIn('acc_type_id', [4, 5])
                ->where(function ($QueryData) use ($WhereColumn, $FeildVal) {
                    if (!empty($FeildVal)) {
                        $QueryData->where($WhereColumn, 'LIKE', "%,{$FeildVal},%")
                            ->orWhere($WhereColumn, 'LIKE', "{$FeildVal},%")
                            ->orWhere($WhereColumn, 'LIKE', "%,{$FeildVal}")
                            ->orWhere($WhereColumn, 'LIKE', "{$FeildVal}");
                    }
                })
            // ->where($WhereColumn, 'LIKE', "%,{$FeildVal},%")
            // ->orWhere($WhereColumn, 'LIKE', "{$FeildVal},%")
            // ->orWhere($WhereColumn, 'LIKE', "%,{$FeildVal}")
            // ->orWhere($WhereColumn, 'LIKE', "{$FeildVal}")

            // ->orderBy([$SelectArr[1] => 'ASC'])
                ->get();

            $SelectedVal = $request->SelectedVal;

            $SelectText = '';
            if ($SelectedVal != null) {
                if ($SelectedVal == 0) {
                    $SelectText = 'selected="selected"';
                }
            }

            $output = '<option value="">Select Option</option>';

            $output .= '<option value="0"' . $SelectText . '>Non Cash</option>';
            foreach ($QueryData as $Row) {
                $SelectText = '';
                if ($SelectedVal != null) {
                    if ($SelectedVal == $Row->$PrimaryKey) {
                        $SelectText = 'selected="selected"';
                    }
                }
                $output .= '<option value="' . $Row->$PrimaryKey . '" data="' . $Row->code . '-' . $Row->$DisplayKey . '" ' . $SelectText . '>' . $Row->code . '-' . $Row->$DisplayKey . '</option>';
            }

            echo $output;
        }
    }

    public function ajaxSelectBoxCodeLoad(Request $request)
    {

        if ($request->ajax()) {

            $FeildVal = $request->FeildVal;
            $TableName = base64_decode($request->TableName);
            $WhereColumn = base64_decode($request->WhereColumn);
            $SelectColumn = base64_decode($request->SelectColumn);
            $SelectArr = explode(',', $SelectColumn);
            $PrimaryKey = $SelectArr[0];
            $CodeKey = $SelectArr[1];
            $DisplayKey = $SelectArr[2];

            $SelectedVal = $request->SelectedVal;

            // Query
            $QueryData = DB::table($TableName)
                ->where([$WhereColumn => $FeildVal, 'is_delete' => 0])
                ->select([$PrimaryKey, $CodeKey, $DisplayKey])
            // ->orderBy([$SelectArr[1] => 'ASC'])
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $SelectText = '';

                if ($SelectedVal != null) {
                    if ($SelectedVal == $Row->$PrimaryKey) {
                        $SelectText = 'selected="selected"';
                    }
                }
                $output .= '<option value="' . $Row->$PrimaryKey . '" ' . $SelectText . '>' . sprintf("%04d", $Row->$CodeKey) . '-' . $Row->$DisplayKey . '</option>';
            }

            echo $output;
        }
    }

    public function ajaxSelectBoxLoadforLedger(Request $request)
    {

        if ($request->ajax()) {

            $FeildVal = $request->FeildVal;
            $TableName = base64_decode($request->TableName);
            $WhereColumn = base64_decode($request->WhereColumn);
            $SelectColumn = base64_decode($request->SelectColumn);
            $SelectArr = explode(',', $SelectColumn);
            $PrimaryKey = $SelectArr[0];
            $DisplayKey = $SelectArr[1];

            $SelectedVal = $request->SelectedVal;

            // Query
            $QueryData = DB::table($TableName)
                ->where([$WhereColumn => $FeildVal, 'is_delete' => 0])
                ->select(['id', $PrimaryKey, $DisplayKey])
                ->orderBy($PrimaryKey, 'ASC')
            // ->orderBy([$SelectArr[1] => 'ASC'])
                ->get();

            if ($SelectedVal != null) {
                $checkselecteddata = DB::table($TableName)
                    ->where([$WhereColumn => $FeildVal, 'is_delete' => 0])
                    ->where('id', $SelectedVal)
                    ->select([$PrimaryKey, $DisplayKey])
                    ->orderBy($PrimaryKey, 'ASC')
                    ->first();
                $last = $checkselecteddata->order_by - 1;
                //  dd($checkselecteddata);

            } else {
                $last = count($QueryData);
            }

            //   dd($QueryData, $SelectedVal,$PrimaryKey);
            $output = '<option value="0">At First</option>';

            if (!empty($QueryData)) {
                foreach ($QueryData as $Row) {

                    $SelectText = '';

                    if ($last != null) {
                        if ($last == $Row->$PrimaryKey) {
                            $SelectText = 'selected="selected"';
                        }
                        // if ($SelectedVal == $Row->id) {
                        //     $SelectText = 'selected="selected"';
                        // }
                    }
                    if ($request->SelectedVal != $Row->id) {
                        $output .= '<option value="' . $Row->$PrimaryKey . '" ' . $SelectText . '> After ' . $Row->$DisplayKey . '</option>';

                    }
                }
            }

            echo $output;
        }
    }

    public function ajaxGetAreabyZone(Request $request)
    {

        if ($request->ajax()) {

            $ZoneID = $request->zone_id;
            // $SelectedVal = $request->SelectedVal;

            $AreainZone = MapZoneArea::select('area_id')->where('zone_id', $ZoneID)->get();
            $AreainZone = $AreainZone->pluck('area_id');

            $Area = Area::whereIn('id', $AreainZone->toArray())->get();

            // dd($Area);
            $output = '<option value="">Select Area</option>';
            foreach ($Area as $Row) {
                $output .= '<option value="' . $Row->id . '">' . $Row->area_name . '</option>';
            }

            echo $output;
        }
    }

    public function ajaxMenuList(Request $request)
    {

        if ($request->ajax()) {

            $module_id = $request->module_id;
            $SelectedVal = $request->SelectedVal;
            // Query
            $QueryData = DB::table('gnl_sys_menus')
                ->where([['is_delete', 0], ['module_id', $module_id]])
                ->orderBy('parent_menu_id', 'ASC')
                ->orderBy('menu_name', 'ASC')
                ->get();

            // dd($QueryData);

            $output = '<option value="0">Select One</option>';
            foreach ($QueryData as $Row) {

                $SelectText = '';

                if ($SelectedVal != null) {
                    if ($SelectedVal == $Row->id) {
                        $SelectText = 'selected="selected"';
                    }
                }
                $output .= '<option value="' . $Row->id . '" ' . $SelectText . '>' . $Row->menu_name . '(' . $Row->route_link . ')</option>';
            }

            echo $output;
        }
    }

    public function ajaxCheckOBStockBranch(Request $request)
    {

        if ($request->ajax()) {

            // Query
            $BranchData = Branch::select(DB::raw('id, branch_name, branch_code, (SELECT COUNT(ob.id) '
                . 'FROM `pos_ob_stock_m` as ob '
                . 'WHERE ob.branch_id = gnl_branchs.id AND ob.is_delete = 0 ) as BranchCount'))
                ->where(['is_delete' => 0, 'is_approve' => 1])
                ->having('BranchCount', '=', 0)
                ->get();

            $output = '<option value="">Select Branch</option>';
            foreach ($BranchData as $Row) {
                $output .= '<option value="' . $Row->id . '">' . sprintf("%04d", $Row->branch_code) . " - " . $Row->branch_name . '</option>';
            }

            echo $output;
        }
    }
    // ajaxCheckOBStockBranch

    public function ajaxDeleteLedger(Request $request)
    {

        if ($request->ajax()) {

            $key = $request->RowID;
            $Model = 'App\\Model\\Acc\\Ledger';
            $any = $Model::where(['is_delete' => 0, 'parent_id' => $key])->count();
            // $branch_id = $DayEndData->branch_id;
            // $branch_date = $DayEndData->branch_date;

            // $checkdata = $Model::where('branch_id', '=', $branch_id)
            //     ->where('is_active', 0)
            //     ->where('branch_date', '>', $DayEndData->branch_date)
            //     ->count();

            if ($any > 0) {
                return 'child';
            } else {

                // $Ledger = $Model::where('id', $key)->first();
                // $Ledger->is_delete = 1;
                // $delete = $Ledger->update();
                // $DayEndData->is_active = 1;
                // $isSuccess = $DayEndData->update();

                // if ($isSuccess) {

                //     $deletedata = $Model::where('branch_id', '=', $branch_id)
                //         ->where('branch_date', '>', $DayEndData->branch_date)
                //         ->delete();

                //     return 'deleted';

                // } else {
                //     return 'db_error';
                // }
                return 'ok';

            }
        }
    }

    /**
     * ajaxdDeleteCheck method used for checking data when delete function occur, This method is called via ajax
     * @param TableName - Database table name
     */
    public function ajaxdDeleteCheck(Request $request)
    {

        if ($request->ajax()) {
            $key = $request->key;
            $columnname = base64_decode($request->columnname);
            $condition2 = base64_decode($request->condition2);
            $where1 = null;
            $where2 = null;

            // dd($columnname,$condition2);

            if ($condition2 != null) {
                $conditionarr = explode(',', $condition2);
                $where1 = $conditionarr[0];
                $where2 = $conditionarr[1];
            }
            $table1 = base64_decode($request->table1);
            $table2 = base64_decode($request->table2);
            $table3 = base64_decode($request->table3);
            $QueryCheck = 0;
            $QueryCheck1 = 0;
            $QueryCheck2 = 0;

            if ($table1 != null) {
                if ($condition2 != null) {
                    $QueryCheck = DB::table($table1)->where([$columnname => $key, $where1 => $where2])->count();
                } else {
                    $QueryCheck = DB::table($table1)->where($columnname, $key)->count();
                }
            }
            if ($table2 != null) {
                if ($condition2 != null) {
                    $QueryCheck1 = DB::table($table2)->where([$columnname => $key, $where1 => $where2])->count();
                } else {
                    $QueryCheck1 = DB::table($table2)->where($columnname, $key)->count();
                }
            }
            if ($table3 != null) {
                if ($condition2 != null) {
                    $QueryCheck2 = DB::table($table3)->where([$columnname => $key, $where1 => $where2])->count();
                } else {
                    $QueryCheck2 = DB::table($table3)->where($columnname, $key)->count();
                }
            }

            return $QueryCheck + $QueryCheck1 + $QueryCheck2;
        }
    }

    /**
     * ajaxCheckDuplicate method used for checking unique data, This method is called via ajax
     * @param TableName - Database table name
     */
    // public function ajaxCheckDuplicate(Request $request)
    // {
    //     if ($request->ajax()) {

    //         $Model = 'App\\Model\\POS\\' . base64_decode($request->model);
    //         // dd($Model);
    //         $query = $request->get('query');
    //         $cond_where = $request->get('forWhich');

    //         $data = $Model::where($cond_where, $query)->first();
    //         // dd($data);

    //         if ($data) {
    //             return response()->json(array("exists" => true,"rowID" => $data->id));
    //         } else {
    //             return response()->json(array("exists" => false));
    //         }
    //     }
    // }

    /**
     * ajaxCheckDuplicate method used for checking unique data, This method is called via ajax
     * @param directory - Directory of Model, model - Model Name, forwhich - Field Name, query- value from input field
     */

    public function ajaxCheckDuplicate(Request $request)
    {
        if ($request->ajax()) {
            $table = base64_decode($request->get('tableName'));
            $columnName = $request->get('columnName');
            $columnValue = $request->get('columnValue');
            $editableID = $request->get('editableID');

            $columnNameArr = (!empty($columnName)) ? explode('&&', $columnName) : array();
            $columnValueArr = (!empty($columnValue)) ? explode('&&', $columnValue) : array();

            $whereCond = [];

            foreach ($columnNameArr as $k => $column) {
                if (isset($columnValueArr[$k])) {
                    array_push($whereCond, [$column, $columnValueArr[$k]]);
                }
            }

            if (count($whereCond) > 0) {
                $queryData = DB::table($table)
                    ->where($whereCond)
                    ->first();

                if ($queryData) {
                    if ($editableID == $queryData->id) {
                        return response()->json(array("exists" => 0));
                    } else {
                        return response()->json(array("exists" => 1));
                    }
                } else {
                    return response()->json(array("exists" => 0));
                }
                // // // 0 = Unique
                // // // 1 = Duplicate
            } else {
                // // // -1 = Condition Mis match
                return response()->json(array("exists" => -1));
            }
        }
    }



    

    public function ajaxSupplierInfo(Request $request)
    {
        if ($request->ajax()) {
            $Data = Supplier::where('id', $request->ID)->select('supplier_type', 'comission_percent')->first();
            echo $Data;
        }
    }

    /**
     * ajaxBarcodeGenerate method used for generating barcode , This method is called via ajax
     * @param test - @Type - String -  test details
     */
    public function ajaxBarcodeGenerate(Request $request)
    {
        if ($request->ajax()) {

            $GroupID = $request->GroupID;
            $CatID = $request->CatID;
            $BrandID = $request->BrandID;

            $GroupID = sprintf("%02d", $GroupID);
            $CatID = sprintf("%03d", $CatID);
            $BrandID = sprintf("%03d", $BrandID);
            $newID = sprintf("%05d", "1");

            $barcode_pre = $GroupID . $CatID . $BrandID;

            $data = Product::query()
                ->select(['id', 'sys_barcode'])
                ->where(['prod_group_id' => $request->GroupID, 'prod_cat_id' => $request->CatID, 'prod_brand_id' => $request->BrandID])
                ->where('sys_barcode', 'LIKE', "{$barcode_pre}%")
                ->orderBy('sys_barcode', 'DESC')->first();

            //dd($barcode_pre);

            if ($data) {
                $barcode = $data->sys_barcode + 1;
                $barcode = sprintf("%013d", $barcode);
            } else {
                $barcode = $barcode_pre . $newID;
                $barcode = sprintf("%013d", $barcode);
            }
            //dd($barcode_pre);

            $barcode_generator = new Picqer\Barcode\BarcodeGeneratorPNG();
            $image = '<img width="80%" src="data:image/png;base64,' . base64_encode($barcode_generator->getBarcode($barcode, $barcode_generator::TYPE_CODE_128)) . '">';

            $Data = [
                'barcode' => $barcode,
                'bar_image' => $image,
            ];
            echo json_encode($Data);
        }
    }

    public function ajaxBranchDate(Request $request)
    {
        $BranchId = $request->BranchId;
        $Module = $request->Module;

        $BranchDate = Common::systemCurrentDate($BranchId, $Module);

        return $BranchDate;
    }

    /*     * ********************************** Bill No Generate Start */

    public function ajaxGenerateBillPurchase(Request $request)
    {
        $BranchId = $request->BranchId;

        $BillNo = POSS::generateBillPurchase($BranchId);

        return $BillNo;
    }

    public function ajaxGenerateBillPurchaseReturn(Request $request)
    {
        $BranchId = $request->BranchId;
        $BillNo = POSS::generateBillPurchaseReturn($BranchId);

        return $BillNo;
    }

    public function ajaxGenerateBillPurchaseReturnInv(Request $request)
    {
        $BranchId = $request->BranchId;
        $BillNo = INVS::generateBillPurchaseReturn($BranchId);

        return $BillNo;
    }

    public function ajaxGenerateBillIssue(Request $request)
    {
        $BranchId = $request->BranchId;
        $BillNo = POSS::generateBillIssue($BranchId);

        return $BillNo;
    }

    public function ajaxGenerateBillIssueInv(Request $request)
    {
        $BranchId = $request->BranchId;
        $BillNo = INVS::generateBillIssue($BranchId);

        return $BillNo;
    }

    public function ajaxGenerateBillIssueReturn(Request $request)
    {
        $BranchId = $request->BranchId;

        $BillNo = POSS::generateBillIssueReturn($BranchId);

        return $BillNo;
    }

    public function ajaxGenerateBillIssueReturnInv(Request $request)
    {
        $BranchId = $request->BranchId;

        $BillNo = INVS::generateBillIssueReturn($BranchId);

        return $BillNo;
    }

    public function ajaxGenerateBillTransfer(Request $request)
    {

        $BranchId = $request->BranchID;

        $BillNo = POSS::generateBillTransfer($BranchId);

        return $BillNo;
    }

    public function ajaxGenerateBillTransferInv(Request $request)
    {

        $BranchId = $request->BranchID;

        $BillNo = INVS::generateBillTransfer($BranchId);

        return $BillNo;
    }

    public function ajaxGenerateBillVoucher(Request $request)
    {
        $voucherType = $request->vouchertype;
        $projectID = $request->projectID;
        $project_typeID = $request->project_typeID;
        $BranchID = $request->BranchID;

        $BillNo = ACCS::generateBillVoucher($BranchID, $voucherType, $projectID, $project_typeID);

        return $BillNo;
    }

    // public function ajaxGenerateBillSales(Request $request)
    // {
    //     // // $companyID = $request->compID;
    //     // $branchID = $request->branchID;
    //     // $branchCode = Branch::where(['is_delete' => 0, 'is_approve' => 1, 'id' => $branchID])
    //     //     ->select('branch_code')
    //     //     ->first();
    //     // $ldate = date('Ym');
    //     // $PreBillNo = "SL" . $branchCode->branch_code . $ldate;
    //     // $record = SalesMaster::where('branch_id', $branchID)
    //     //     ->select(['id', 'sales_bill_no'])
    //     //     ->where('sales_bill_no', 'LIKE', "{$PreBillNo}%")
    //     //     ->orderBy('sales_bill_no', 'DESC')
    //     //     ->first();
    //     // // dd($record);
    //     // if ($record) {
    //     //     $OldBillNoA = explode($PreBillNo, $record->sales_bill_no);
    //     //     $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
    //     // } else {
    //     //     $BillNo = $PreBillNo . sprintf("%05d", 1);
    //     // }
    //     // return $BillNo;
    //     $BranchId = $request->BranchID;
    //     $BillNo = POSS::generateBillSales($BranchId);
    //     return $BillNo;
    // }

    public function ajaxGenerateBillSalesReturn(Request $request)
    {
        $BranchId = $request->BranchId;

        $BillNo = POSS::generateBillSalesReturn($BranchId);

        return $BillNo;
    }

    public function ajaxGenerateBillUsessReturn(Request $request)
    {
        $BranchId = $request->BranchId;

        $BillNo = INVS::generateBillUsesReturn($BranchId);

        return $BillNo;
    }

    /*     * ****************************** Bill No Generate End */

    /*     * *************************** Product Load of Transaction Portion Start */

    // ajaxProductLoad

    public function ajaxProductLoadPurchase(Request $request)
    {

        if ($request->ajax()) {

            $ModelID = (isset($request->ModelID)) ? $request->ModelID : null;
            $GroupID = (isset($request->GroupID)) ? $request->GroupID : null;
            $CategoryID = (isset($request->CategoryID)) ? $request->CategoryID : null;
            $SubCatID = (isset($request->SubCatID)) ? $request->SubCatID : null;
            // $CompanyID = (isset($request->CompanyID)) ? $request->CompanyID : null;
            $SupplierID = (isset($request->SupplierID)) ? $request->SupplierID : null;

            // $CompanyArr = (!empty($CompanyID)) ? ['company_id', '=', $CompanyID] : ['company_id', '<>', ''];
            $SupplierArr = (!empty($SupplierID)) ? ['supplier_id', '=', $SupplierID] : ['supplier_id', '<>', ''];
            $GroupArr = (!empty($GroupID)) ? ['prod_group_id', '=', $GroupID] : ['prod_group_id', '<>', ''];
            $CategoryArr = (!empty($CategoryID)) ? ['prod_cat_id', '=', $CategoryID] : ['prod_cat_id', '<>', ''];
            $SubCatArr = (!empty($SubCatID)) ? ['prod_sub_cat_id', '=', $SubCatID] : ['prod_sub_cat_id', '<>', ''];
            $ModelArr = (!empty($ModelID)) ? ['prod_model_id', '=', $ModelID] : ['prod_model_id', '<>', ''];

            // Query
            $QueryData = DB::table('pos_products')
                ->select(['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'])
                ->where([['is_delete', '=', 0], $SupplierArr, $GroupArr, $CategoryArr, $SubCatArr, $ModelArr])
                ->orderBy('product_name', 'ASC')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $output .= '<option value="' . $Row->id . '"
                                    pname= "' . $Row->product_name . '"
                                    sbarcode="' . $Row->sys_barcode . '"
                                    pbarcode="' . $Row->prod_barcode . '"
                                    pcprice="' . $Row->cost_price . '"
                                    psprice="' . $Row->sale_price . '" >';
                $output .= $Row->product_name . ' (' . $Row->sys_barcode . ')';
                $output .= '</option>';
            }

            echo $output;
        }
    }

    public function ajaxProductLoadPurchaseInv(Request $request)
    {

        if ($request->ajax()) {

            $ModelID = (isset($request->ModelID)) ? $request->ModelID : null;
            $GroupID = (isset($request->GroupID)) ? $request->GroupID : null;
            $CategoryID = (isset($request->CategoryID)) ? $request->CategoryID : null;
            $SubCatID = (isset($request->SubCatID)) ? $request->SubCatID : null;
            // $CompanyID = (isset($request->CompanyID)) ? $request->CompanyID : null;
            $SupplierID = (isset($request->SupplierID)) ? $request->SupplierID : null;

            // $CompanyArr = (!empty($CompanyID)) ? ['company_id', '=', $CompanyID] : ['company_id', '<>', ''];
            $SupplierArr = (!empty($SupplierID)) ? ['supplier_id', '=', $SupplierID] : ['supplier_id', '<>', ''];
            $GroupArr = (!empty($GroupID)) ? ['prod_group_id', '=', $GroupID] : ['prod_group_id', '<>', ''];
            $CategoryArr = (!empty($CategoryID)) ? ['prod_cat_id', '=', $CategoryID] : ['prod_cat_id', '<>', ''];
            $SubCatArr = (!empty($SubCatID)) ? ['prod_sub_cat_id', '=', $SubCatID] : ['prod_sub_cat_id', '<>', ''];
            $ModelArr = (!empty($ModelID)) ? ['prod_model_id', '=', $ModelID] : ['prod_model_id', '<>', ''];

            // Query
            $QueryData = DB::table('inv_products')
                ->select(['id', 'product_name', 'cost_price', 'product_code'])
                ->where([['is_delete', '=', 0], $SupplierArr, $GroupArr, $CategoryArr, $SubCatArr, $ModelArr])
                ->orderBy('product_name', 'ASC')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $output .= '<option value="' . $Row->id . '"
                                    pname= "' . $Row->product_name . '"
                                    sbarcode="' . $Row->product_code . '"
                                    pcprice="' . $Row->cost_price . '">';
                $output .= $Row->product_name . ($Row->product_code ? ' (' . $Row->product_code . ')' : '');
                $output .= '</option>';
            }

            echo $output;
        }
    }

    /////////////////////////////////////

    public function ajaxProductLoadPurchaseReturn(Request $request)
    {

        if ($request->ajax()) {

            $ModelID = (isset($request->ModelID)) ? $request->ModelID : null;
            $GroupID = (isset($request->GroupID)) ? $request->GroupID : null;
            $CategoryID = (isset($request->CategoryID)) ? $request->CategoryID : null;
            $SubCatID = (isset($request->SubCatID)) ? $request->SubCatID : null;
            // $CompanyID = (isset($request->CompanyID)) ? $request->CompanyID : null;
            $SupplierID = (isset($request->SupplierID)) ? $request->SupplierID : null;

            // $CompanyArr = (!empty($CompanyID)) ? ['company_id', '=', $CompanyID] : ['company_id', '<>', ''];
            $SupplierArr = (!empty($SupplierID)) ? ['supplier_id', '=', $SupplierID] : ['supplier_id', '<>', ''];
            $GroupArr = (!empty($GroupID)) ? ['prod_group_id', '=', $GroupID] : ['prod_group_id', '<>', ''];
            $CategoryArr = (!empty($CategoryID)) ? ['prod_cat_id', '=', $CategoryID] : ['prod_cat_id', '<>', ''];
            $SubCatArr = (!empty($SubCatID)) ? ['prod_sub_cat_id', '=', $SubCatID] : ['prod_sub_cat_id', '<>', ''];
            $ModelArr = (!empty($ModelID)) ? ['prod_model_id', '=', $ModelID] : ['prod_model_id', '<>', ''];

            // Query
            $QueryData = DB::table('pos_products')
                ->select(['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'])
                ->where([['is_delete', '=', 0], $SupplierArr, $GroupArr, $CategoryArr, $SubCatArr, $ModelArr])
                ->orderBy('product_name', 'ASC')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $output .= '<option value="' . $Row->id . '"
                                    pname= "' . $Row->product_name . '"
                                    sbarcode="' . $Row->sys_barcode . '"
                                    pbarcode="' . $Row->prod_barcode . '"
                                    pcprice="' . $Row->cost_price . '"
                                    psprice="' . $Row->sale_price . '" >';
                $output .= $Row->product_name . ' (' . $Row->sys_barcode . ')';
                $output .= '</option>';
            }

            echo $output;
        }
    }

    public function ajaxProductLoadIssue(Request $request)
    {

        if ($request->ajax()) {

            $ModelID = (isset($request->ModelID)) ? $request->ModelID : null;
            $GroupID = (isset($request->GroupID)) ? $request->GroupID : null;
            $CategoryID = (isset($request->CategoryID)) ? $request->CategoryID : null;
            $SubCatID = (isset($request->SubCatID)) ? $request->SubCatID : null;
            // $CompanyID = (isset($request->CompanyID)) ? $request->CompanyID : null;
            $SupplierID = (isset($request->SupplierID)) ? $request->SupplierID : null;

            // $CompanyArr = (!empty($CompanyID)) ? ['company_id', '=', $CompanyID] : ['company_id', '<>', ''];
            $SupplierArr = (!empty($SupplierID)) ? ['supplier_id', '=', $SupplierID] : ['supplier_id', '<>', ''];
            $GroupArr = (!empty($GroupID)) ? ['prod_group_id', '=', $GroupID] : ['prod_group_id', '<>', ''];
            $CategoryArr = (!empty($CategoryID)) ? ['prod_cat_id', '=', $CategoryID] : ['prod_cat_id', '<>', ''];
            $SubCatArr = (!empty($SubCatID)) ? ['prod_sub_cat_id', '=', $SubCatID] : ['prod_sub_cat_id', '<>', ''];
            $ModelArr = (!empty($ModelID)) ? ['prod_model_id', '=', $ModelID] : ['prod_model_id', '<>', ''];

            // Query
            $QueryData = DB::table('pos_products')
                ->select(['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'])
                ->where([['is_delete', '=', 0], $SupplierArr, $GroupArr, $CategoryArr, $SubCatArr, $ModelArr])
                ->orderBy('product_name', 'ASC')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $output .= '<option value="' . $Row->id . '"
                                    pname= "' . $Row->product_name . '"
                                    sbarcode="' . $Row->sys_barcode . '"
                                    pbarcode="' . $Row->prod_barcode . '"
                                    pcprice="' . $Row->cost_price . '"
                                    psprice="' . $Row->sale_price . '" >';
                $output .= $Row->product_name . ' (' . $Row->sys_barcode . ')';
                $output .= '</option>';
            }

            echo $output;
        }
    }

    public function ajaxProductLoadIssueReturn(Request $request)
    {

        if ($request->ajax()) {

            $ModelID = (isset($request->ModelID)) ? $request->ModelID : null;
            $GroupID = (isset($request->GroupID)) ? $request->GroupID : null;
            $CategoryID = (isset($request->CategoryID)) ? $request->CategoryID : null;
            $SubCatID = (isset($request->SubCatID)) ? $request->SubCatID : null;
            // $CompanyID = (isset($request->CompanyID)) ? $request->CompanyID : null;
            $SupplierID = (isset($request->SupplierID)) ? $request->SupplierID : null;

            //$CompanyArr = (!empty($CompanyID)) ? ['company_id', '=', $CompanyID] : ['company_id', '<>', ''];
            $SupplierArr = (!empty($SupplierID)) ? ['supplier_id', '=', $SupplierID] : ['supplier_id', '<>', ''];
            $GroupArr = (!empty($GroupID)) ? ['prod_group_id', '=', $GroupID] : ['prod_group_id', '<>', ''];
            $CategoryArr = (!empty($CategoryID)) ? ['prod_cat_id', '=', $CategoryID] : ['prod_cat_id', '<>', ''];
            $SubCatArr = (!empty($SubCatID)) ? ['prod_sub_cat_id', '=', $SubCatID] : ['prod_sub_cat_id', '<>', ''];
            $ModelArr = (!empty($ModelID)) ? ['prod_model_id', '=', $ModelID] : ['prod_model_id', '<>', ''];

            // Query
            $QueryData = DB::table('pos_products')
                ->select(['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'])
                ->where([['is_delete', '=', 0], $SupplierArr, $GroupArr, $CategoryArr, $SubCatArr, $ModelArr])
                ->orderBy('product_name', 'ASC')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $output .= '<option value="' . $Row->id . '"
                                    pname= "' . $Row->product_name . '"
                                    sbarcode="' . $Row->sys_barcode . '"
                                    pbarcode="' . $Row->prod_barcode . '"
                                    pcprice="' . $Row->cost_price . '"
                                    psprice="' . $Row->sale_price . '" >';
                $output .= $Row->product_name . ' (' . $Row->sys_barcode . ')';
                $output .= '</option>';
            }

            echo $output;
        }
    }

    /* Get Product Barcode by selecting product model for Transfer */

    public function ajaxProductLoadTransfer(Request $request)
    {

        if ($request->ajax()) {

            $ModelID = (isset($request->ModelID)) ? $request->ModelID : null;
            $GroupID = (isset($request->GroupID)) ? $request->GroupID : null;
            $CategoryID = (isset($request->CategoryID)) ? $request->CategoryID : null;
            $SubCatID = (isset($request->SubCatID)) ? $request->SubCatID : null;
            // $CompanyID = (isset($request->CompanyID)) ? $request->CompanyID : null;
            $SupplierID = (isset($request->SupplierID)) ? $request->SupplierID : null;

            //$CompanyArr = (!empty($CompanyID)) ? ['company_id', '=', $CompanyID] : ['company_id', '<>', ''];
            $SupplierArr = (!empty($SupplierID)) ? ['supplier_id', '=', $SupplierID] : ['supplier_id', '<>', ''];
            $GroupArr = (!empty($GroupID)) ? ['prod_group_id', '=', $GroupID] : ['prod_group_id', '<>', ''];
            $CategoryArr = (!empty($CategoryID)) ? ['prod_cat_id', '=', $CategoryID] : ['prod_cat_id', '<>', ''];
            $SubCatArr = (!empty($SubCatID)) ? ['prod_sub_cat_id', '=', $SubCatID] : ['prod_sub_cat_id', '<>', ''];
            $ModelArr = (!empty($ModelID)) ? ['prod_model_id', '=', $ModelID] : ['prod_model_id', '<>', ''];

            // Query
            $QueryData = DB::table('pos_products')
                ->select(['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'])
                ->where([['is_delete', '=', 0], $SupplierArr, $GroupArr, $CategoryArr, $SubCatArr, $ModelArr])
                ->orderBy('product_name', 'ASC')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $output .= '<option value="' . $Row->id . '"
                                    pname= "' . $Row->product_name . '"
                                    sbarcode="' . $Row->sys_barcode . '"
                                    pbarcode="' . $Row->prod_barcode . '"
                                    pcprice="' . $Row->cost_price . '"
                                    psprice="' . $Row->sale_price . '" >';
                $output .= $Row->product_name . ' (' . $Row->sys_barcode . ')';
                $output .= '</option>';
            }

            echo $output;
        }
    }

    public function ajaxProductLoadTransferInv(Request $request)
    {

        if ($request->ajax()) {

            $ModelID = (isset($request->ModelID)) ? $request->ModelID : null;
            $GroupID = (isset($request->GroupID)) ? $request->GroupID : null;
            $CategoryID = (isset($request->CategoryID)) ? $request->CategoryID : null;
            $SubCatID = (isset($request->SubCatID)) ? $request->SubCatID : null;
            // $CompanyID = (isset($request->CompanyID)) ? $request->CompanyID : null;
            $SupplierID = (isset($request->SupplierID)) ? $request->SupplierID : null;

            //$CompanyArr = (!empty($CompanyID)) ? ['company_id', '=', $CompanyID] : ['company_id', '<>', ''];
            $SupplierArr = (!empty($SupplierID)) ? ['supplier_id', '=', $SupplierID] : ['supplier_id', '<>', ''];
            $GroupArr = (!empty($GroupID)) ? ['prod_group_id', '=', $GroupID] : ['prod_group_id', '<>', ''];
            $CategoryArr = (!empty($CategoryID)) ? ['prod_cat_id', '=', $CategoryID] : ['prod_cat_id', '<>', ''];
            $SubCatArr = (!empty($SubCatID)) ? ['prod_sub_cat_id', '=', $SubCatID] : ['prod_sub_cat_id', '<>', ''];
            $ModelArr = (!empty($ModelID)) ? ['prod_model_id', '=', $ModelID] : ['prod_model_id', '<>', ''];

            // Query
            $QueryData = DB::table('pos_products')
                ->select(['id', 'product_name', 'product_code'])
                ->where([['is_delete', '=', 0], $SupplierArr, $GroupArr, $CategoryArr, $SubCatArr, $ModelArr])
                ->orderBy('product_name', 'ASC')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $row) {

                $output .= '<option value="' . $row->id . '"
                                    pname= "' . $row->product_name . '"
                                    sbarcode="' . $row->product_code . '"';
                $output .= ($row->product_code != '' ? $row->product_name . ' - ' . $row->product_code : $row->product_name);
                $output .= '</option>';
            }

            echo $output;
        }
    }

    public function ajaxProductLoadSales(Request $request)
    {

        if ($request->ajax()) {

            $ModelID = (isset($request->ModelID)) ? $request->ModelID : null;
            $GroupID = (isset($request->GroupID)) ? $request->GroupID : null;
            $CategoryID = (isset($request->CategoryID)) ? $request->CategoryID : null;
            $SubCatID = (isset($request->SubCatID)) ? $request->SubCatID : null;
            //$CompanyID = (isset($request->CompanyID)) ? $request->CompanyID : null;
            $SupplierID = (isset($request->SupplierID)) ? $request->SupplierID : null;

            // $CompanyArr = (!empty($CompanyID)) ? ['company_id', '=', $CompanyID] : ['company_id', '<>', ''];
            $SupplierArr = (!empty($SupplierID)) ? ['supplier_id', '=', $SupplierID] : ['supplier_id', '<>', ''];
            $GroupArr = (!empty($GroupID)) ? ['prod_group_id', '=', $GroupID] : ['prod_group_id', '<>', ''];
            $CategoryArr = (!empty($CategoryID)) ? ['prod_cat_id', '=', $CategoryID] : ['prod_cat_id', '<>', ''];
            $SubCatArr = (!empty($SubCatID)) ? ['prod_sub_cat_id', '=', $SubCatID] : ['prod_sub_cat_id', '<>', ''];
            $ModelArr = (!empty($ModelID)) ? ['prod_model_id', '=', $ModelID] : ['prod_model_id', '<>', ''];

            // Query
            $QueryData = DB::table('pos_products')
                ->select(['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'])
                ->where([['is_delete', '=', 0], $SupplierArr, $GroupArr, $CategoryArr, $SubCatArr, $ModelArr])
                ->orderBy('product_name', 'ASC')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $output .= '<option value="' . $Row->id . '"
                                    pname= "' . $Row->product_name . '"
                                    sbarcode="' . $Row->sys_barcode . '"
                                    pbarcode="' . $Row->prod_barcode . '"
                                    pcprice="' . $Row->cost_price . '"
                                    psprice="' . $Row->sale_price . '" >';
                $output .= $Row->product_name . ' (' . $Row->sys_barcode . ')';
                $output .= '</option>';
            }

            echo $output;
        }
    }

    public function ajaxProductLoadSalesReturn(Request $request)
    {

        if ($request->ajax()) {

            $ModelID = (isset($request->ModelID)) ? $request->ModelID : null;
            $GroupID = (isset($request->GroupID)) ? $request->GroupID : null;
            $CategoryID = (isset($request->CategoryID)) ? $request->CategoryID : null;
            $SubCatID = (isset($request->SubCatID)) ? $request->SubCatID : null;
            //$CompanyID = (isset($request->CompanyID)) ? $request->CompanyID : null;
            $SupplierID = (isset($request->SupplierID)) ? $request->SupplierID : null;

            // $CompanyArr = (!empty($CompanyID)) ? ['company_id', '=', $CompanyID] : ['company_id', '<>', ''];
            $SupplierArr = (!empty($SupplierID)) ? ['supplier_id', '=', $SupplierID] : ['supplier_id', '<>', ''];
            $GroupArr = (!empty($GroupID)) ? ['prod_group_id', '=', $GroupID] : ['prod_group_id', '<>', ''];
            $CategoryArr = (!empty($CategoryID)) ? ['prod_cat_id', '=', $CategoryID] : ['prod_cat_id', '<>', ''];
            $SubCatArr = (!empty($SubCatID)) ? ['prod_sub_cat_id', '=', $SubCatID] : ['prod_sub_cat_id', '<>', ''];
            $ModelArr = (!empty($ModelID)) ? ['prod_model_id', '=', $ModelID] : ['prod_model_id', '<>', ''];

            // Query
            $QueryData = DB::table('pos_products')
                ->select(['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'])
                ->where([['is_delete', '=', 0], $SupplierArr, $GroupArr, $CategoryArr, $SubCatArr, $ModelArr])
                ->orderBy('product_name', 'ASC')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $output .= '<option value="' . $Row->id . '"
                                    pname= "' . $Row->product_name . '"
                                    sbarcode="' . $Row->sys_barcode . '"
                                    pbarcode="' . $Row->prod_barcode . '"
                                    pcprice="' . $Row->cost_price . '"
                                    psprice="' . $Row->sale_price . '" >';
                $output .= $Row->product_name . ' (' . $Row->sys_barcode . ')';
                $output .= '</option>';
            }

            echo $output;
        }
    }

    /* End */

    public function ajaxSalebillDetails(Request $request)
    {

        if ($request->ajax()) {

            $bill = $request->SalesBillNo;
            $returnBillNo = (isset($request->returnBillNo)) ? $request->returnBillNo : null;

            // Query
            $SaleM = SalesMaster::where('sales_bill_no', $bill)->first();

            $SaleD = DB::table('pos_sales_d as psd')
                ->where('sales_bill_no', $bill)
                ->select('psd.*', 'prod.product_name')
                ->leftjoin('pos_products as prod', function ($SaleD) {
                    $SaleD->on('prod.id', '=', 'psd.product_id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->get();

            $output = '';
            $option = '<option value="">Select Product</option>';
            $totalRetQnt = 0;

            if (count($SaleD) > 0) {
                foreach ($SaleD as $Row) {

                    $productID = $Row->product_id;
                    $countdata = DB::table('pos_sales_return_m as sm')
                        ->where('sm.sales_bill_no', $Row->sales_bill_no)
                        ->where([['sm.is_delete', 0], ['sm.is_active', 1]])
                        ->where(function ($countdata) use ($returnBillNo) {

                            if (!empty($returnBillNo)) {
                                $countdata->where('sm.return_bill_no', '<>', $returnBillNo);
                            }
                        })
                        ->join('pos_sales_return_d as sd', function ($countdata) use ($productID) {
                            $countdata->on('sm.return_bill_no', '=', 'sd.return_bill_no')
                                ->where('sd.product_id', $productID);
                        })
                        ->sum('sd.product_quantity');

                    $output .= '<tr><td>' . $Row->product_name . ' (' . $Row->product_system_barcode . ')' . '</td><td>' . $Row->product_quantity . '</td><td>' . $countdata . '</td><td>' . $Row->product_unit_price . '</td><td>' . $Row->total_sales_price . '</td></tr>';

                    if ($countdata < $Row->product_quantity) {

                        // $stockQnt = $Row->product_quantity - $countdata;
                        $stockQnt = $Row->product_quantity;

                        $option .= '<option
                        value="' . $Row->product_id .
                        '" pcprice="' . $Row->product_cost_price .
                        '" psprice="' . $Row->product_unit_price .
                        '" pname="' . $Row->product_name .
                        '" sbarcode="' . $Row->product_system_barcode .
                        '" pbarcode="' . $Row->product_barcode .
                        '" pquantity="' . $stockQnt .
                        '" retquantity="' . $countdata .
                        '">' . $Row->product_name . ' (' . $Row->product_system_barcode . ')' . '</option>';
                    }

                    $totalRetQnt += $countdata;

                }

                $output .= '<tr><td><strong>Total</strong> </td><td><strong>' . $SaleM->total_quantity . '</strong></td><td><strong>' . $totalRetQnt . '</strong></td><td></td><td><strong>' . $SaleM->total_amount . '</strong></td></tr>';
            }
            $response = [
                'master' => $SaleM,
                'option' => $option,
                'tbody' => $output,
            ];

            echo json_encode($response);
        }
    }

    public function ajaxUsebillDetails(Request $request)
    {
        if ($request->ajax()) {

            $bill = $request->usesBillNo;
            $returnBillNo = (isset($request->returnBillNo)) ? $request->returnBillNo : null;

            // Query
            $useM = DB::table('inv_use_m')->where('uses_bill_no', $bill)->first();

            $useD = DB::table('inv_use_d as psd')
                ->where('uses_bill_no', $bill)
                ->select('psd.*', 'prod.product_name')
                ->leftjoin('inv_products as prod', function ($useD) {
                    $useD->on('prod.id', '=', 'psd.product_id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->get();

            $output = '';
            $option = '<option value="">Select Product</option>';
            $totalRetQnt = 0;

            if (count($useD) > 0) {
                foreach ($useD as $Row) {

                    $productID = $Row->product_id;
                    $countdata = DB::table('inv_use_return_m as sm')
                        ->where('sm.uses_bill_no', $Row->uses_bill_no)
                        ->where([['sm.is_delete', 0], ['sm.is_active', 1]])
                        ->where(function ($countdata) use ($returnBillNo) {

                            if (!empty($returnBillNo)) {
                                $countdata->where('sm.return_bill_no', '<>', $returnBillNo);
                            }
                        })
                        ->join('inv_use_return_d as sd', function ($countdata) use ($productID) {
                            $countdata->on('sm.return_bill_no', '=', 'sd.return_bill_no')
                                ->where('sd.product_id', $productID);
                        })
                        ->sum('sd.product_quantity');

                    $output .= '<tr><td>' . $Row->product_name . '</td><td>' . $Row->product_quantity . '</td><td>' . $countdata . '</td></tr>';

                    if ($countdata < $Row->product_quantity) {

                        // $stockQnt = $Row->product_quantity - $countdata;
                        $stockQnt = $Row->product_quantity;

                        $option .= '<option
                        value="' . $Row->product_id .
                        '" pname="' . $Row->product_name .
                        // '" pbarcode="' . $Row->product_code .
                        '" pquantity="' . $stockQnt .
                        '" retquantity="' . $countdata .
                        '">' . $Row->product_name . '</option>';

                    }
                    $totalRetQnt += $countdata;

                }

                $output .= '<tr><td><strong>Total</strong> </td><td><strong>' . $useM->total_quantity . '</strong></td><td><strong>' . $totalRetQnt . '</strong></td></tr>';
            }
            $response = [
                'master' => $useM,
                'option' => $option,
                'tbody' => $output,
            ];

            echo json_encode($response);
        }
    }

    /*     * *********************************** Product Load of Transaction Portion End */

    //supplier name load function
    public function ajaxSupplierNameLoad(Request $request)
    {

        if ($request->ajax()) {

            $data = Supplier::where(['id' => $request->SupplierID])->select('sup_comp_name')->first();

            $orders = DB::table('pos_orders_m')->where([['is_approve', 1], ['order_to', $request->SupplierID], ['is_delivered', 0], ['is_completed', 0], ['is_active', 1], ['is_delete', 0]])->get();

            $data = array(
                'suplierName' => $data->sup_comp_name,
                'orderList' => $orders,
            );

            return response()->json($data);
        }
    }

    //supplier name load function For Inventory
    public function ajaxSupplierNameLoadInv(Request $request)
    {

        if ($request->ajax()) {

            $data = DB::table('inv_suppliers')->where(['id' => $request->SupplierID])->select('sup_comp_name')->first();

            echo $data->sup_comp_name;
        }
    }

    //Customer Mobile No load function
    public function ajaxCustomerMobileLoad(Request $request)
    {

        if ($request->ajax()) {

            $data = Customer::where(['customer_no' => $request->CustomerID])->select('customer_mobile')->first();

            echo $data->customer_mobile;
        }
    }

    //Customer Mobile No load function
    public function ajaxCustomerNIDLoad(Request $request)
    {

        if ($request->ajax()) {

            $data = Customer::where(['customer_no' => $request->CustomerID])->select('customer_nid')->first();

            echo $data->customer_nid;
        }
    }

    // generate customer no
    public static function ajaxGenerateCustomerNo(Request $request)
    {
        // dd($request->BranchID);
        $branchID = $request->BranchID;
        $BranchT = 'App\\Model\\GNL\\Branch';
        $ModelT = "App\\Model\\POS\\Customer";

        $BranchCodeQuery = $BranchT::where([['is_delete', 0], ['is_approve', 1], ['id', $branchID]])
            ->select('branch_code')
            ->first();

        if ($BranchCodeQuery) {
            $BranchCode = sprintf("%04d", $BranchCodeQuery->branch_code);
        } else {
            $BranchCode = sprintf("%04d", 0);
        }

        // $ldate = date('Ym');

        $PreBillNo = "CUS" . $BranchCode;
        $record = $ModelT::select(['id', 'customer_no'])
            ->where('branch_id', $branchID)
            ->where('customer_no', 'LIKE', "{$PreBillNo}%")
            ->orderBy('customer_no', 'DESC')
            ->first();

        if ($record) {
            $OldBillNoA = explode($PreBillNo, $record->customer_no);
            $BillNo = $PreBillNo . sprintf("%05d", ($OldBillNoA[1] + 1));
        } else {
            $BillNo = $PreBillNo . sprintf("%05d", 1);
        }

        return $BillNo;
    }

    // Employee Designation load function
    public function ajaxEmpDesignationLoad(Request $request)
    {

        if ($request->ajax()) {
            $employeeID = $request->employee_id;

            $data = DB::table('hr_employees as emp')
                ->where([['emp.is_delete', 0], ['emp.is_active', 1], ['emp.employee_no', $employeeID]])
                ->select('des.name as designation')
                ->leftjoin('hr_designations as des', function ($data) {
                    $data->on('emp.designation_id', '=', 'des.id')
                        ->where([['des.is_delete', 0], ['des.is_active', 1]]);
                })
                ->first();

            echo $data->designation;
        }
    }

    //Sales ID load function
    public function ajaxSalesIDLoad(Request $request)
    {

        if ($request->ajax()) {

            $data = SalesMaster::where(['sales_bill_no' => $request->selectedData])->select('id')->first();
            return $data->id;
        }
    }

    //function for generate bill no for installment sales
    public function ajaxGBillSales(Request $request)
    {
        $BranchId = $request->BranchId;

        $BillNo = POSS::generateBillSales($BranchId);

        return $BillNo;
    }

    //function for generate bill no for inventory use
    public function ajaxGBillUses(Request $request)
    {
        $BranchId = $request->BranchId;

        $BillNo = INVS::generateBillSales($BranchId);

        return $BillNo;
    }

    //function for Calculate Stock Quantity for Product
    public function ajaxStockQuantity(Request $request)
    {

        $BranchID = $request->BranchId;
        $ProductID = $request->ProductId;
        $sDate = Common::getBranchSoftwareStartDate($BranchID, 'pos');
        $eDate = Common::systemCurrentDate($BranchID, 'pos');

        // dd($sDate, $eDate , $BranchID);

        $Stock = POSS::stockQuantity($BranchID, $ProductID, false, $sDate, $eDate);
        // dd( $Stock);

        return $Stock;
    }

    public function ajaxStockQuantityInv(Request $request)
    {
        $BranchID = $request->BranchId;
        $ProductID = $request->ProductId;
        $sDate = Common::getBranchSoftwareStartDate($BranchID, 'inv');
        $eDate = Common::systemCurrentDate($BranchID, 'inv');

        // dd($sDate, $eDate , $BranchID);

        $Stock = INVS::stockQuantity($BranchID, $ProductID, false, $sDate, $eDate);

        return $Stock;
    }

    public function ajaxBranchOpendate(Request $request)
    {

        $BranchID = $request->branchID;
        $moduleName = $request->moduleName;

        $branch_date = Common::getBranchSoftwareStartDate($BranchID, $moduleName);

        if ($branch_date != null) {
            $date = (new DateTime($branch_date))->format('d-m-Y');
            return $date;
        } else {
            return null;
        }
    }

    public function ajaxCustSalesDetails(Request $request)
    {
        $customerId = $request->customerId;
        $selectedVal = (isset($request->selectedVal)) ? $request->selectedVal : null;

        $salesData = DB::table('pos_sales_m')
            ->where([['customer_id', $customerId], ['sales_type', 2],
                ['is_delete', 0], ['is_active', 1], ['is_complete', 0]])
            ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
            ->select('id', 'sales_bill_no', 'total_amount', 'installment_rate')
            ->get();

        $output = '<option value="">Select One</option>';

        foreach ($salesData as $Row) {

            $selectText = '';

            if ($selectedVal != null) {
                if ($selectedVal == $Row->sales_bill_no) {
                    $selectText = 'selected="selected"';
                }
            }

            $output .= '<option value="' . $Row->sales_bill_no . '"
                    sales_id ="' . $Row->id . '"
                    sales_payable_amount ="' . $Row->total_amount . '"
                    installment_rate="' . $Row->installment_rate . '"
                    ' . $selectText . '>' . $Row->sales_bill_no . '</option>';
        }

        echo $output;
    }

    public function ajaxBillCollection(Request $request)
    {
        $salesBillNo = $request->salesBill;

        $CollectionData = Collection::where([['sales_bill_no', $salesBillNo], ['is_delete', 0]])->sum('collection_amount');

        echo $CollectionData;
    }

    public function getProcessingFee(Request $request)
    {
        if ($request->ajax()) {

            $proFeeData = PProcessingFee::where([['company_id', $request->companyId], ['is_delete', 0]])->first();

            if ($proFeeData) {
                return $proFeeData->amount;
            } else {
                return 0;
            }

        }
    }

    public function getBranchCustomer(Request $request)
    {

        $branchId = $request->branchId;

        $QueryData = DB::table('pos_customers')
            ->where([['is_delete', 0], ['is_active', 1]])
            ->where(function ($QueryData) use ($branchId) {
                if (!empty($branchId)) {
                    $QueryData->where('branch_id', $branchId);
                }
            })
            ->get(); // no update

        $output = '<option value="">Select One</option>';

        foreach ($QueryData as $row) {

            $output .= '<option value="' . $row->customer_no . '"
                    cname ="' . $row->customer_name . '"
                    ccode ="' . $row->customer_no . '">' . $row->customer_name . '(' . $row->customer_no . ')' . '</option>';
        }

        return $output;
    }
    
    public function ajaxAutoVoucheritem(Request $request)
    {

        $misType = $request->misType;
        $voucherType = $request->VoucherType;
        $Model = 'App\\Model\\Acc\\MisConfig';

        $MisItem = $Model::where([['is_delete', 0], ['sales_type', $misType]])->get();

        // $output = '<option value="">Select One</option>';

        // foreach ($customers as $row) {

        //     $output .= '<option value="' . $row->id . '"
        //             cname ="' . $row->customer_name . '"
        //             ccode ="' . $row->customer_code . '">' . $row->customer_name . '</option>';
        // }

        return $MisItem;
    }

    public function getArea(Request $request)
    {
        if ($request->ajax()) {

            $zoneId = $request->zoneId;

            // Query
            $QueryData = DB::table('gnl_map_zone_area')
                ->where('zone_id', $zoneId)
                ->select('gnl_areas.id', 'gnl_areas.area_name', 'gnl_areas.area_code')
                ->leftjoin('gnl_areas', 'gnl_map_zone_area.area_id', 'gnl_areas.id')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $SelectText = '';

                $output .= '<option value="' . $Row->id . '">' . sprintf("%d04", $Row->area_code) . ' - ' . $Row->area_name . '</option>';
            }

            echo $output;
        }
    }

    public function getSalesBillNo(Request $request)
    {
        if ($request->ajax()) {

            $branchId = $request->branchId;

            // Query
            $QueryData = DB::table('pos_sales_m')
                ->where([['is_delete', 0], ['is_active', 1]])
                ->where(function ($QueryData) use ($branchId) {
                    if (!empty($branchId)) {
                        $QueryData->where('branch_id', $branchId);
                    }
                })
                ->select('pos_sales_m.id', 'pos_sales_m.sales_bill_no')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {
                $SelectText = '';
                $output .= '<option value="' . $Row->sales_bill_no . '">' . $Row->sales_bill_no . '</option>';
            }

            echo $output;
        }
    }

    public function getEmployeeName(Request $request)
    {

        if ($request->ajax()) {

            $branchId = $request->branchId;

            // Query
            $QueryData = DB::table('hr_employees')
                ->where([['is_delete', 0], ['is_active', 1]])
                ->where(function ($QueryData) use ($branchId) {
                    if (!empty($branchId)) {
                        $QueryData->where('branch_id', $branchId);
                    }
                })
                ->select('hr_employees.id', 'hr_employees.employee_no', 'hr_employees.emp_name')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {
                $SelectText = '';
                $output .= '<option value="' . $Row->id . '">' . $Row->emp_name . '(' . $Row->employee_no . ')' . '</option>';
            }

            echo $output;
        }
    }

    public function getBranch(Request $request)
    {
        if ($request->ajax()) {

            $areaId = $request->areaId;

            // Query
            $QueryData = DB::table('gnl_map_area_branch')
                ->where('area_id', $areaId)
                ->select('gnl_branchs.id', 'gnl_branchs.branch_name', 'gnl_branchs.branch_code')
                ->leftjoin('gnl_branchs', 'gnl_map_area_branch.branch_id', 'gnl_branchs.id')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($QueryData as $Row) {

                $SelectText = '';

                $output .= '<option value="' . $Row->id . '">' . sprintf("%d04", $Row->branch_code) . ' - ' . $Row->branch_name . '</option>';
            }

            echo $output;
        }
    }

    public function ajaxVoucherAuth(Request $request)
    {
        if ($request->ajax()) {
            $myObj = $request->myObj;
            $flag = true;
            foreach ($myObj as $Row) {
                //dd($Row['ID']);
                // dd($Row['value']);
                $Id = $Row['ID'];
                $status = $Row['value'];
                $voucherData = Voucher::where('id', $Id)->first();
                // dd($voucherData);

                if ($status == 1) {
                    $voucherData->auth_by = Auth::id();
                    $voucherData->voucher_status = 1;
                    $isSuccess = $voucherData->update();

                    if (!$isSuccess) {
                        $flag = false;
                    }
                }
            }
            echo $flag;
        }
    }

    public function ajaxVoucherUnAuth(Request $request)
    {
        if ($request->ajax()) {

            $myObj = $request->myObj;

            $flag = true;
            foreach ($myObj as $Row) {

                //dd($Row['ID']);
                // dd($Row['value']);
                $Id = $Row['ID'];
                $status = $Row['value'];
                $voucherData = Voucher::where('id', $Id)->first();
                // dd($voucherData);

                if ($status == 1) {

                    $voucherData->auth_by = 0;
                    $voucherData->voucher_status = 0;
                    $isSuccess = $voucherData->update();

                    if (!$isSuccess) {
                        $flag = false;
                    }

                }

            }

            echo $flag;
        }
    }

    public function getOrderPurchaseProdLoad(Request $request)
    {

        if ($request->ajax()) {

            $orderNo = $request->orderNo;

            /*$queryData = DB::table('pos_requisitions_m as prm')
            ->where([['prm.is_approve', 1], ['prm.is_delete', 0], ['prm.order_no', $orderNo]])
            ->select('prm.requisition_no', 'prd.product_id' ,'prod.product_name', 'prod.prod_barcode', 'prd.product_quantity', 'prod.cost_price as prod_cost_price')
            ->leftjoin('pos_requisitions_d as prd', function($queryData){
            $queryData->on('prm.id' , '=', 'prd.requisition_id');
            })
            ->leftjoin('pos_products as prod', function($queryData){
            $queryData->on('prd.product_id', '=', 'prod.id')
            ->where('prod.is_delete', 0);
            })
            ->addSelect(['remaining_qtn' => DB::table('pos_purchases_d as ppd')
            ->select(DB::raw('(prd.product_quantity - IFNULL(SUM(ppd.product_quantity), 0))'))
            ->whereColumn([['prm.requisition_no', 'ppd.requisition_no'], ['ppd.product_id', 'prd.product_id']])
            ->where('ppd.is_delete', 0)
            ->limit(1)
            ])
            ->get();*/
            // dd($orderNo);
            $queryData = DB::table('pos_orders_m as pom')
                ->where([['pom.order_no', $orderNo], ['pom.is_completed', 0]])
                ->select('pod.product_id', 'prod.product_name', 'prod.prod_barcode', 'pod.product_quantity', 'prod.cost_price as prod_cost_price')
                ->leftjoin('pos_orders_d as pod', function ($queryData) {
                    $queryData->on('pod.order_no', '=', 'pom.order_no');
                })
                ->leftjoin('pos_products as prod', function ($queryData) {
                    $queryData->on('prod.id', '=', 'pod.product_id')
                        ->where('prod.is_delete', 0);
                })
                ->addSelect(['remaining_qtn' => DB::table('pos_purchases_m as ppm')
                        ->select(DB::raw('(pod.product_quantity - IFNULL(SUM(ppd.product_quantity), 0))'))
                        ->leftjoin('pos_purchases_d as ppd', function ($queryData) {
                            $queryData->on('ppd.purchase_bill_no', '=', 'ppm.bill_no');
                        })
                        ->whereColumn([['pom.order_no', 'ppm.order_no'], ['ppd.product_id', 'pod.product_id']])
                        ->where([['ppm.is_delete', 0], ['ppm.is_active', 1]])
                        ->limit(1),
                ])
                ->get();

            // dd($queryData);

            $output = '<option value="">Select One</option>';
            foreach ($queryData as $row) {

                if ($row->remaining_qtn > 0) {
                    $output .= '<option value="' . $row->product_id . '" pname="' . $row->product_name . '" sbarcode="' . $row->prod_barcode . '" pcprice="' . $row->prod_cost_price . '" prod_qtn="' . $row->remaining_qtn . '">' . $row->product_name . ' - (' . $row->prod_barcode . ')' . '</option>';
                }

            }

            echo $output;
        }
    }

    public function getOrderPurchaseProdLoadInv(Request $request)
    {

        if ($request->ajax()) {

            $orderNo = $request->orderNo;

            /*$queryData = DB::table('pos_requisitions_m as prm')
            ->where([['prm.is_approve', 1], ['prm.is_delete', 0], ['prm.order_no', $orderNo]])
            ->select('prm.requisition_no', 'prd.product_id' ,'prod.product_name', 'prod.prod_barcode', 'prd.product_quantity', 'prod.cost_price as prod_cost_price')
            ->leftjoin('pos_requisitions_d as prd', function($queryData){
            $queryData->on('prm.id' , '=', 'prd.requisition_id');
            })
            ->leftjoin('pos_products as prod', function($queryData){
            $queryData->on('prd.product_id', '=', 'prod.id')
            ->where('prod.is_delete', 0);
            })
            ->addSelect(['remaining_qtn' => DB::table('pos_purchases_d as ppd')
            ->select(DB::raw('(prd.product_quantity - IFNULL(SUM(ppd.product_quantity), 0))'))
            ->whereColumn([['prm.requisition_no', 'ppd.requisition_no'], ['ppd.product_id', 'prd.product_id']])
            ->where('ppd.is_delete', 0)
            ->limit(1)
            ])
            ->get();*/

            $queryData = DB::table('inv_orders_m as pom')
                ->where([['pom.order_no', $orderNo], ['pom.is_completed', 0]])
                ->select('pod.product_id', 'prod.product_name', 'prod.product_code', 'pod.product_quantity', 'prod.cost_price as prod_cost_price')
                ->leftjoin('inv_orders_d as pod', function ($queryData) {
                    $queryData->on('pod.order_no', '=', 'pom.order_no');
                })
                ->leftjoin('inv_products as prod', function ($queryData) {
                    $queryData->on('prod.id', '=', 'pod.product_id')
                        ->where('prod.is_delete', 0);
                })
                ->addSelect(['remaining_qtn' => DB::table('inv_purchases_m as ppm')
                        ->select(DB::raw('(pod.product_quantity - IFNULL(SUM(ppd.product_quantity), 0))'))
                        ->leftjoin('inv_purchases_d as ppd', function ($queryData) {
                            $queryData->on('ppd.purchase_bill_no', '=', 'ppm.bill_no');
                        })
                        ->whereColumn([['pom.order_no', 'ppm.order_no'], ['ppd.product_id', 'pod.product_id']])
                        ->where([['ppm.is_delete', 0], ['ppm.is_active', 1]])
                        ->limit(1),
                ])
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($queryData as $row) {

                if ($row->remaining_qtn > 0) {
                    $output .= '<option value="' . $row->product_id . '" pname="' . $row->product_name . '" sbarcode="' . $row->product_code . '" pcprice="' . $row->prod_cost_price . '" prod_qtn="' . $row->remaining_qtn . '">'
                        . ($row->product_code != '' ? $row->product_name . ' - ' . $row->product_code : $row->product_name) .
                        '</option>';
                }

            }

            echo $output;
        }
    }

    public function getReqProductInIssue(Request $request)
    {

        if ($request->ajax()) {

            $reqNo = $request->reqNo;

            /* $queryData = DB::table('pos_orders_m as pom')
            ->where([['pom.is_approve', 1], ['pom.is_delete', 0], ['pom.requisition_no', $reqNo]])
            ->select('pom.requisition_no', 'prd.product_id' ,'prod.product_name', 'prod.prod_barcode', 'prd.product_quantity', 'prod.cost_price as prod_cost_price')
            ->leftjoin('pos_requisitions_d as prd', function($queryData){
            $queryData->on('prm.id' , '=', 'prd.requisition_id');
            })
            ->leftjoin('pos_products as prod', function($queryData){
            $queryData->on('prd.product_id', '=', 'prod.id')
            ->where('prod.is_delete', 0);
            })
            ->addSelect(['remaining_qtn' => DB::table('pos_issues_d as pid')
            ->select(DB::raw('(prd.product_quantity - IFNULL(SUM(pid.product_quantity), 0))'))
            ->whereColumn([['prm.requisition_no', 'pid.requisition_no'], ['pid.product_id', 'prd.product_id']])
            ->where('pid.is_delete', 0)
            ->limit(1)
            ])
            ->get();*/

            $queryData = DB::table('pos_requisitions_m as prm')
                ->where([['prm.requisition_no', $reqNo], ['prm.is_complete', 0]])
                ->select('prd.product_id', 'prod.product_name', 'prod.prod_barcode', 'prd.product_quantity', 'prod.cost_price as prod_cost_price')
                ->leftjoin('pos_requisitions_d as prd', function ($queryData) {
                    $queryData->on('prm.requisition_no', '=', 'prd.requisition_no');
                })
                ->leftjoin('pos_products as prod', function ($queryData) {
                    $queryData->on('prd.product_id', '=', 'prod.id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->addSelect(['remaining_qtn' => DB::table('pos_issues_m as pim')
                        ->select(DB::raw('(prd.product_quantity - IFNULL(SUM(pid.product_quantity), 0))'))
                        ->leftjoin('pos_issues_d as pid', function ($queryData) {
                            $queryData->on('pid.issue_bill_no', '=', 'pim.bill_no');
                        })
                        ->whereColumn([['prm.requisition_no', 'pim.requisition_no'], ['pid.product_id', 'prd.product_id']])
                        ->where([['pim.is_delete', 0], ['pim.is_active', 1]])
                        ->limit(1),
                ])
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($queryData as $row) {

                if ($row->remaining_qtn > 0) {
                    $output .= '<option value="' . $row->product_id . '" pname="' . $row->product_name . '" sbarcode="' . $row->prod_barcode . '" pcprice="' . $row->prod_cost_price . '" remainQtn="' . $row->remaining_qtn . '">' . $row->product_name . ' - (' . $row->prod_barcode . ')' . '</option>';
                }
            }

            echo $output;
        }
    }

    public function getReqProductInIssueInv(Request $request)
    {

        if ($request->ajax()) {

            $reqNo = $request->reqNo;

            /* $queryData = DB::table('pos_orders_m as pom')
            ->where([['pom.is_approve', 1], ['pom.is_delete', 0], ['pom.requisition_no', $reqNo]])
            ->select('pom.requisition_no', 'prd.product_id' ,'prod.product_name', 'prod.prod_barcode', 'prd.product_quantity', 'prod.cost_price as prod_cost_price')
            ->leftjoin('pos_requisitions_d as prd', function($queryData){
            $queryData->on('prm.id' , '=', 'prd.requisition_id');
            })
            ->leftjoin('pos_products as prod', function($queryData){
            $queryData->on('prd.product_id', '=', 'prod.id')
            ->where('prod.is_delete', 0);
            })
            ->addSelect(['remaining_qtn' => DB::table('pos_issues_d as pid')
            ->select(DB::raw('(prd.product_quantity - IFNULL(SUM(pid.product_quantity), 0))'))
            ->whereColumn([['prm.requisition_no', 'pid.requisition_no'], ['pid.product_id', 'prd.product_id']])
            ->where('pid.is_delete', 0)
            ->limit(1)
            ])
            ->get();*/

            $queryData = DB::table('inv_requisitions_m as prm')
                ->where([['prm.requisition_no', $reqNo], ['prm.is_complete', 0]])
                ->select('prd.product_id', 'prod.product_name', 'prod.product_code', 'prd.product_quantity')
                ->leftjoin('inv_requisitions_d as prd', function ($queryData) {
                    $queryData->on('prm.requisition_no', '=', 'prd.requisition_no');
                })
                ->leftjoin('inv_products as prod', function ($queryData) {
                    $queryData->on('prd.product_id', '=', 'prod.id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->addSelect(['remaining_qtn' => DB::table('inv_issues_m as pim')
                        ->select(DB::raw('(prd.product_quantity - IFNULL(SUM(pid.product_quantity), 0))'))
                        ->leftjoin('inv_issues_d as pid', function ($queryData) {
                            $queryData->on('pid.issue_bill_no', '=', 'pim.bill_no');
                        })
                        ->whereColumn([['prm.requisition_no', 'pim.requisition_no'], ['pid.product_id', 'prd.product_id']])
                        ->where([['pim.is_delete', 0], ['pim.is_active', 1]])
                        ->limit(1),
                ])
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($queryData as $row) {

                if ($row->remaining_qtn > 0) {
                    $output .= '<option value="' . $row->product_id . '"
                                    pname= "' . $row->product_name . '"
                                    sbarcode="' . $row->product_code . '"
                                    remainQtn="' . $row->remaining_qtn . '">';
                    $output .= $row->product_name . ($row->product_code ? ' (' . $row->product_code . ')' : '');
                    $output .= '</option>';
                }
            }

            echo $output;
        }
    }

    public function getReqLoadIssue(Request $request)
    {

        if ($request->ajax()) {

            $branchId = $request->branchId;
            $selRequisition = $request->selRequisition;

            $queryData = DB::table('pos_requisitions_m')
                ->where([['branch_from', $branchId],
                    ['is_approve', 1],
                    // ['is_complete', 0],
                    ['is_active', 1],
                    ['is_delete', 0]])
                ->where(function ($queryData) use ($selRequisition) {
                    if (!empty($selRequisition)) {
                        $queryData->where('is_complete', 0)
                            ->orWhere('requisition_no', $selRequisition);
                    } else {
                        $queryData->where('is_complete', 0);
                    }
                })
                ->select('requisition_no')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($queryData as $row) {
                $selectText = ($selRequisition == $row->requisition_no) ? "selected" : "";

                $output .= '<option value="' . $row->requisition_no . '" ' . $selectText . ' >';
                $output .= $row->requisition_no;
                $output .= '</option>';
            }

            echo $output;
        }
    }

    public function getReqLoadIssueInv(Request $request)
    {

        if ($request->ajax()) {

            $branchId = $request->branchId;
            $selRequisition = $request->selRequisition;

            $queryData = DB::table('inv_requisitions_m')
                ->where([['branch_from', $branchId],
                    ['is_approve', 1],
                    // ['is_complete', 0],
                    ['is_active', 1],
                    ['is_delete', 0]])
                ->where(function ($queryData) use ($selRequisition) {
                    if (!empty($selRequisition)) {
                        $queryData->where('is_complete', 0)
                            ->orWhere('requisition_no', $selRequisition);
                    } else {
                        $queryData->where('is_complete', 0);
                    }
                })
                ->select('requisition_no')
                ->get();

            $output = '<option value="">Select One</option>';
            foreach ($queryData as $row) {
                $selectText = ($selRequisition == $row->requisition_no) ? "selected" : "";

                $output .= '<option value="' . $row->requisition_no . '" ' . $selectText . ' >';
                $output .= $row->requisition_no;
                $output .= '</option>';
            }

            echo $output;
        }
    }

    public function getLoadSupplierProdByReq(Request $request)
    {
        if ($request->ajax()) {

            $supplier = $request->supplier;

            $queryData = DB::table('pos_requisitions_d as prd')
                ->where([['prd.is_ordered', 0]])
                ->select('prm.*', 'prd.id as prd_id', 'prod.supplier_id', 'prd.product_id', 'prd.product_quantity', 'prod.product_name', 'prod.sys_barcode', 'ps.sup_name', 'brf.branch_name as branch_from_name')
                ->leftjoin('pos_requisitions_m as prm', function ($queryData) {
                    $queryData->on('prd.requisition_id', '=', 'prm.id')
                        ->where([['prm.is_delete', 0], ['prm.is_active', 1]]);
                })
                ->leftjoin('pos_products as prod', function ($queryData) {
                    $queryData->on('prod.id', '=', 'prd.product_id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->leftjoin('pos_suppliers as ps', function ($queryData) {
                    $queryData->on('ps.id', '=', 'prod.supplier_id')
                        ->where([['ps.is_delete', 0], ['ps.is_active', 1]]);
                })
                ->leftjoin('gnl_branchs as brf', function ($queryData) {
                    $queryData->on('prm.branch_from', '=', 'brf.id')
                        ->where('brf.is_approve', 1);
                })
                ->addSelect(['remaining_qtn' => DB::table('pos_orders_d as pod')
                        ->select(DB::raw('(prd.product_quantity - IFNULL(SUM(pod.product_quantity), 0))'))
                        ->whereColumn([['prm.requisition_no', 'pod.requisition_no'], ['pod.product_id', 'prd.product_id']])
                        ->limit(1),
                ])
                ->where(function ($queryData) use ($supplier) {
                    if (!empty($supplier)) {
                        $queryData->where('prod.supplier_id', $supplier);
                    }
                })
                ->orderBy('prm.requisition_no')
                ->get();

            if (count($queryData) == 0) {
                $dataSet = '<td colspan="10">No rows data for order</td>';
                return $dataSet;
            }

            $dataSet = '';
            $i = 0;
            foreach ($queryData as $row) {
                $i++;
                $output = '';

                $output = '<tr>' .
                '<td onclick="fnCheck(' . $i . ');">' .
                '<input type="checkBox" name="order_check_box_arr[]" class="ckeckBoxCls" id="order_check_box_' . $i . '" value="' . $row->prd_id . '" supplier="' . $row->supplier_id . '" onclick="fnCheck(' . $i . ');">' .
                '</td>' .

                '<td>' . $i . '</td>' .

                '<td class="text-left">' . $row->product_name . '(' . $row->sys_barcode . ')' . '</td>' .

                '<td>' . date('d-m-Y', strtotime($row->requisition_date)) . '</td>' .

                '<td>' . $row->requisition_no . '</td>' .

                '<td>' . "Head Office" . '</td>' .

                '<td>' . $row->branch_from_name . '</td>' .

                '<td width="10%">' .
                '<input type="number" name="product_quantity_arr[]"  id="total_quantity_id_' . $i . '" class="form-control round textNumber" value="' . $row->remaining_qtn . '" readonly="true">' .
                '</td>' .

                '<td>' . $row->sup_name . '</td>' .

                '<td>' .
                '<a href="' . url('pos/requisition/view/' . $row->id) . '" title="View" class="btnView">
                                    <i class="icon wb-eye mr-2 blue-grey-600"></i>
                                </a>' .

                '<input type="text" name="requisition_id_arr[]" id="requisition_id_' . $i . '" value="' . $row->prd_id . '" hidden="true">

                                <input type="text" name="order_to_arr[]" id="supplier_id_' . $i . '" value="' . $row->supplier_id . '" hidden="true">

                                <input type="text" name="requisition_no_arr[]" id="requisition_no_id_' . $i . '" value="' . $row->requisition_no . '" hidden="true">

                                <input type="text" name="requisition_date_arr[]" id="requisition_date_id_' . $i . '" value="' . $row->requisition_date . '" hidden="true">

                                <input type="text" name="requisition_branch_from_arr[]" id="requisition_branch_from_id_' . $i . '" value="' . $row->branch_from . '" hidden="true">' .

                '<input type="text" name="product_id_arr[]" id="product_id_' . $i . '" value="' . $row->product_id . '" hidden="true">' .
                    '</td>' .
                    '</tr>';

                // <input type="text" name="total_quantity_arr[]" id="product_quantity_id_'.$i.'" value="'.$row->total_quantity.'" hidden="true">

                $dataSet .= $output;
            }

            echo $dataSet;
        }
    }

    public function ajaxGetRequisitionNo(Request $request)
    {
        $BranchId = $request->BranchId;

        $reqNo = POSS::generateBillRequisiton($BranchId);

        $branch_soft_date = Common::systemCurrentDate($BranchId, 'pos');

        return [
            'reqNo' => $reqNo,
            'branch_soft_date' => $branch_soft_date,
        ];
    }

    public function ajaxGetRequisitionNoInv(Request $request)
    {
        $BranchId = $request->BranchId;

        $reqNo = INVS::generateBillRequisiton($BranchId);

        $branch_soft_date = Common::systemCurrentDate($BranchId, 'pos');

        return [
            'reqNo' => $reqNo,
            'branch_soft_date' => $branch_soft_date,
        ];
    }

    public function ajaxCollectionNo(Request $request)
    {
        $BranchId = $request->BranchId;

        $BillNo = POSS::generateCollectionNo($BranchId);

        return $BillNo;
    }
}
