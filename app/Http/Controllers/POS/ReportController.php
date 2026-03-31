<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Model\GNL\MapAreaBranch;
use App\Model\GNL\MapZoneArea;
use App\Model\POS\Collection;
use App\Model\POS\SalesDetails;
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\PosService as POSS;
use DateTime;
use DB;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission']);
        parent::__construct();

        config()->set('database.connections.mysql.strict', false);
        DB::reconnect();
    }

/* ------------------------ Stock Report Start */
    public function stockBranch(Request $request)
    {
        if ($request->ajax()) {

            $sl = 1;

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $branchId = (empty($request->input('branchId'))) ? null : $request->input('branchId');
            $supplierId = (empty($request->input('supplierId'))) ? null : $request->input('supplierId');
            $groupId = (empty($request->input('groupId'))) ? null : $request->input('groupId');
            $catId = (empty($request->input('catId'))) ? null : $request->input('catId');
            $subCatId = (empty($request->input('subCatId'))) ? null : $request->input('subCatId');
            $brandId = (empty($request->input('brandId'))) ? null : $request->input('brandId');
            $modelId = (empty($request->input('modelId'))) ? null : $request->input('modelId');
            $productId = (empty($request->input('productId'))) ? null : $request->input('productId');
            $stockSearch = (empty($request->input('stockSearch'))) ? null : $request->input('stockSearch');

            $reportData = DB::table('pos_products as p')
                ->where([['p.is_active', 1], ['p.is_delete', 0]])
                ->select('p.id', DB::raw('CONCAT(p.product_name, " (", p.prod_barcode, ")") AS product_name'), 'p.prod_barcode')
                ->where(function ($reportData) use ($supplierId, $groupId, $catId, $subCatId, $brandId, $modelId, $productId) {
                    if (!empty($supplierId)) {
                        $reportData->where('p.supplier_id', $supplierId);
                    }
                    if (!empty($groupId)) {
                        $reportData->where('p.prod_group_id', $groupId);
                    }
                    if (!empty($catId)) {
                        $reportData->where('p.prod_cat_id', $catId);
                    }
                    if (!empty($subCatId)) {
                        $reportData->where('p.prod_sub_cat_id', $subCatId);
                    }
                    if (!empty($brandId)) {
                        $reportData->where('p.prod_brand_id', $brandId);
                    }
                    if (!empty($modelId)) {
                        $reportData->where('p.prod_model_id', $modelId);
                    }
                    if (!empty($productId)) {
                        $reportData->where('p.id', $productId);
                    }
                })
                ->orderBy('p.id', 'ASC')
                ->get();

            $DataSet = array();
            $ttlOStock = 0;
            // $ttlPurchase = 0;
            // $ttlPurchaseR = 0;
            $ttlIssue = 0;
            $ttlIssueR = 0;
            $ttlTransferIn = 0;
            $ttlTransferOut = 0;
            $ttlSales = 0;
            $ttlSalesR = 0;
            $ttlAdj = 0;
            $ttlStock = 0;

            $productId = $reportData->pluck('id')->all();
            $stockData = POSS::stockQuantity_Multiple($branchId, $productId, true, $startDate, $endDate);

            foreach ($reportData as $row) {
                $TempSet = array();

                $openingBalance = (isset($stockData[$row->id]['OpeningBalance'])) ? $stockData[$row->id]['OpeningBalance'] : 0;
                // $purchase = (isset($stockData[$row->id]['Purchase'])) ? $stockData[$row->id]['Purchase'] : 0;
                // $purchaseReturn = (isset($stockData[$row->id]['PurchaseReturn'])) ? $stockData[$row->id]['PurchaseReturn'] : 0;
                $issue = (isset($stockData[$row->id]['Issue'])) ? $stockData[$row->id]['Issue'] : 0;
                $issueReturn = (isset($stockData[$row->id]['IssueReturn'])) ? $stockData[$row->id]['IssueReturn'] : 0;
                $transferIn = (isset($stockData[$row->id]['TransferIn'])) ? $stockData[$row->id]['TransferIn'] : 0;
                $transferOut = (isset($stockData[$row->id]['TransferOut'])) ? $stockData[$row->id]['TransferOut'] : 0;
                $sales = (isset($stockData[$row->id]['Sales'])) ? $stockData[$row->id]['Sales'] : 0;
                $salesReturn = (isset($stockData[$row->id]['SalesReturn'])) ? $stockData[$row->id]['SalesReturn'] : 0;
                $adjustment = (isset($stockData[$row->id]['Adjustment'])) ? $stockData[$row->id]['Adjustment'] : 0;
                $stock = (isset($stockData[$row->id]['Stock'])) ? $stockData[$row->id]['Stock'] : 0;

                if ($stock == 0 && $stockSearch == 1) {
                    continue;
                }

                $TempSet = [
                    'id' => $sl++,
                    'product_name' => $row->product_name,
                    'openning_stock' => $openingBalance,
                    'Issue' => $issue,
                    'IssueReturn' => $issueReturn,
                    'transfer_in' => $transferIn,
                    'transfer_out' => $transferOut,
                    'sales' => $sales,
                    'sales_return' => $salesReturn,
                    'adj' => $adjustment,
                    'stock' => $stock,
                ];

                $DataSet[] = $TempSet;

                $ttlOStock += $openingBalance;
                $ttlIssue += $issue;
                $ttlIssueR += $issueReturn;
                $ttlTransferIn += $transferIn;
                $ttlTransferOut += $transferOut;
                $ttlSales += $sales;
                $ttlSalesR += $salesReturn;
                $ttlAdj += $adjustment;
                $ttlStock += $stock;
            }

            $total_row = count($DataSet);

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                "totalRow" => $total_row,
                'ttlOStock' => $ttlOStock,
                // 'ttlPurchase' => $ttlPurchase,
                // 'ttlPurchaseR' => $ttlPurchaseR,
                'ttlIssue' => $ttlIssue,
                'ttlIssueR' => $ttlIssueR,
                'ttlTransferIn' => $ttlTransferIn,
                'ttlTransferOut' => $ttlTransferOut,
                'ttlSales' => $ttlSales,
                'ttlSalesR' => $ttlSalesR,
                'ttlAdj' => $ttlAdj,
                'ttlStock' => $ttlStock,
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.stock_branch');
        }
    }

    public function stockHO(Request $request)
    {
        if ($request->ajax()) {

            $sl = 1;

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $branchId = 1;
            $supplierId = (empty($request->input('supplierId'))) ? null : $request->input('supplierId');
            $groupId = (empty($request->input('groupId'))) ? null : $request->input('groupId');
            $catId = (empty($request->input('catId'))) ? null : $request->input('catId');
            $subCatId = (empty($request->input('subCatId'))) ? null : $request->input('subCatId');
            $brandId = (empty($request->input('brandId'))) ? null : $request->input('brandId');
            $modelId = (empty($request->input('modelId'))) ? null : $request->input('modelId');
            $productId = (empty($request->input('productId'))) ? null : $request->input('productId');
            $stockSearch = (empty($request->input('stockSearch'))) ? null : $request->input('stockSearch');

            $reportData = DB::table('pos_products as p')
                ->where([['p.is_active', 1], ['p.is_delete', 0]])
                ->select('p.id', DB::raw('CONCAT(p.product_name, " (", p.prod_barcode, ")") AS product_name'), 'p.prod_barcode')
                ->where(function ($reportData) use ($supplierId, $groupId, $catId, $subCatId, $brandId, $modelId, $productId) {
                    if (!empty($supplierId)) {
                        $reportData->where('p.supplier_id', $supplierId);
                    }
                    if (!empty($groupId)) {
                        $reportData->where('p.prod_group_id', $groupId);
                    }
                    if (!empty($catId)) {
                        $reportData->where('p.prod_cat_id', $catId);
                    }
                    if (!empty($subCatId)) {
                        $reportData->where('p.prod_sub_cat_id', $subCatId);
                    }
                    if (!empty($brandId)) {
                        $reportData->where('p.prod_brand_id', $brandId);
                    }
                    if (!empty($modelId)) {
                        $reportData->where('p.prod_model_id', $modelId);
                    }
                    if (!empty($productId)) {
                        $reportData->where('p.id', $productId);
                    }
                })
                ->orderBy('p.id', 'ASC')
                ->get();

            $DataSet = array();
            $ttlOStock = 0;
            $ttlPurchase = 0;
            $ttlPurchaseR = 0;
            $ttlIssue = 0;
            $ttlIssueR = 0;
            $ttlAdj = 0;
            $ttlStock = 0;

            // // // ----------- Multi Call
            $productId = $reportData->pluck('id')->all();
            $stockData = POSS::stockQuantity_Multiple($branchId, $productId, true, $startDate, $endDate);
            // dd( $stockData );

            foreach ($reportData as $row) {
                $TempSet = array();

                $OpeningBalance = (isset($stockData[$row->id]['OpeningBalance'])) ? $stockData[$row->id]['OpeningBalance'] : 0;
                $Purchase = (isset($stockData[$row->id]['Purchase'])) ? $stockData[$row->id]['Purchase'] : 0;
                $PurchaseReturn = (isset($stockData[$row->id]['PurchaseReturn'])) ? $stockData[$row->id]['PurchaseReturn'] : 0;
                $Issue = (isset($stockData[$row->id]['Issue'])) ? $stockData[$row->id]['Issue'] : 0;
                $IssueReturn = (isset($stockData[$row->id]['IssueReturn'])) ? $stockData[$row->id]['IssueReturn'] : 0;
                $Adjustment = (isset($stockData[$row->id]['Adjustment'])) ? $stockData[$row->id]['Adjustment'] : 0;
                $Stock = (isset($stockData[$row->id]['Stock'])) ? $stockData[$row->id]['Stock'] : 0;

                if ($Stock == 0 && $stockSearch == 1) {
                    continue;
                }

                $TempSet = [
                    'id' => $sl++,
                    'product_name' => $row->product_name,
                    'openning_stock' => $OpeningBalance,
                    'purchase' => $Purchase,
                    'purchase_return' => $PurchaseReturn,
                    'Issue' => $Issue,
                    'IssueReturn' => $IssueReturn,
                    'adj' => $Adjustment,
                    'stock' => $Stock,
                ];

                $DataSet[] = $TempSet;

                $ttlOStock += $OpeningBalance;
                $ttlPurchase += $Purchase;
                $ttlPurchaseR += $PurchaseReturn;
                $ttlIssue += $Issue;
                $ttlIssueR += $IssueReturn;
                $ttlAdj += $Adjustment;
                $ttlStock += $Stock;
            }

            $total_row = count($DataSet);

            $json_data = array(
                "draw" => intval($request->input('draw')),
                // "recordsTotal" => intval($totalData),
                // "recordsFiltered" => intval($totalFiltered),
                "data" => $DataSet,
                "totalRow" => $total_row,
                'ttlOStock' => $ttlOStock,
                'ttlPurchase' => $ttlPurchase,
                'ttlPurchaseR' => $ttlPurchaseR,
                'ttlIssue' => $ttlIssue,
                'ttlIssueR' => $ttlIssueR,
                'ttlAdj' => $ttlAdj,
                'ttlStock' => $ttlStock,
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.stock_ho');
        }
    }

    public function stockInvBranch(Request $request)
    {
        if ($request->ajax()) {

            $sl = 1;

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $zoneId = (empty($request->input('zoneId'))) ? null : $request->input('zoneId');
            $areaId = (empty($request->input('areaId'))) ? null : $request->input('areaId');
            $branchId = (empty($request->input('branchId'))) ? null : $request->input('branchId');
            $supplierId = (empty($request->input('supplierId'))) ? null : $request->input('supplierId');
            $groupId = (empty($request->input('groupId'))) ? null : $request->input('groupId');
            $catId = (empty($request->input('catId'))) ? null : $request->input('catId');
            $subCatId = (empty($request->input('subCatId'))) ? null : $request->input('subCatId');
            $brandId = (empty($request->input('brandId'))) ? null : $request->input('brandId');
            $modelId = (empty($request->input('modelId'))) ? null : $request->input('modelId');
            $productId = (empty($request->input('productId'))) ? null : $request->input('productId');
            $stock = (empty($request->input('stock'))) ? null : $request->input('stock');

            $reportData = DB::table('pos_products as prod')
                ->where([['prod.is_delete', 0], ['prod.is_active', 1]])
                ->select('prod.id', DB::raw('CONCAT(prod.product_name, " (", prod.prod_barcode, ")") AS product_name'), 'prod.prod_barcode', 'prod.cost_price')
                ->where(function ($reportData) use ($supplierId, $groupId, $catId, $subCatId, $brandId, $modelId, $productId) {
                    if (!empty($supplierId)) {
                        $reportData->where('prod.supplier_id', $supplierId);
                    }
                    if (!empty($groupId)) {
                        $reportData->where('prod.prod_group_id', $groupId);
                    }
                    if (!empty($catId)) {
                        $reportData->where('prod.prod_cat_id', $catId);
                    }
                    if (!empty($subCatId)) {
                        $reportData->where('prod.prod_sub_cat_id', $subCatId);
                    }
                    if (!empty($brandId)) {
                        $reportData->where('prod.prod_brand_id', $brandId);
                    }
                    if (!empty($modelId)) {
                        $reportData->where('prod.prod_model_id', $modelId);
                    }
                    if (!empty($productId)) {
                        $reportData->where('prod.id', $productId);
                    }
                })
                ->orderBy('prod.id', 'ASC')
                ->get();

            $DataSet = array();
            $os_ttl_qtn = 0;
            $is_ttl_qtn = 0;
            $sr_ttl_qtn = 0;
            $tin_ttl_qtn = 0;
            $adj_ttl_qtn = 0;
            $sales_ttl_qtn = 0;
            $isr_ttl_qtn = 0;
            $tout_ttl_qtn = 0;
            $adj2_ttl_qtn = 0;
            $stock_ttl_qtn = 0;
            $os_ttl_amt = 0;
            $is_ttl_amt = 0;
            $sr_ttl_amt = 0;
            $tin_ttl_amt = 0;
            $adj_ttl_amt = 0;
            $sales_ttl_amt = 0;
            $isr_ttl_amt = 0;
            $tout_ttl_amt = 0;
            $adj2_ttl_amt = 0;
            $stock_ttl_amt = 0;

            $productId = $reportData->pluck('id')->all();
            $stockData = POSS::stockQuantity_Multiple($branchId, $productId, true, $startDate, $endDate);

            foreach ($reportData as $row) {

                $TempSet = array();

                $openning_stock = (isset($stockData[$row->id]['OpeningBalance'])) ? $stockData[$row->id]['OpeningBalance'] : 0;
                $issue = (isset($stockData[$row->id]['Issue'])) ? $stockData[$row->id]['Issue'] : 0;
                $salesR = (isset($stockData[$row->id]['SalesReturn'])) ? $stockData[$row->id]['SalesReturn'] : 0;
                $TransferIn = (isset($stockData[$row->id]['TransferIn'])) ? $stockData[$row->id]['TransferIn'] : 0;
                $adj = (isset($stockData[$row->id]['Adjustment'])) ? $stockData[$row->id]['Adjustment'] : 0;

                $sales = (isset($stockData[$row->id]['Sales'])) ? $stockData[$row->id]['Sales'] : 0;
                $issueReturn = (isset($stockData[$row->id]['IssueReturn'])) ? $stockData[$row->id]['IssueReturn'] : 0;
                $transferOut = (isset($stockData[$row->id]['TransferOut'])) ? $stockData[$row->id]['TransferOut'] : 0;

                $stockQtn = (isset($stockData[$row->id]['Stock'])) ? $stockData[$row->id]['Stock'] : 0;

                /*
                 *For without zero stock = 1
                 */
                if ($stock == 1 && $stockQtn == 0) {
                    continue;
                }

                $TempSet = [
                    'id' => $sl++,
                    'product_name' => $row->product_name,

                    'openning_stock_qtn' => $openning_stock,
                    'openning_stock_amt' => round(($openning_stock * $row->cost_price), 2),

                    /*add current priod start*/
                    'issue_qtn' => $issue,
                    'issue_amt' => round(($issue * $row->cost_price), 2),

                    'sales_return_qtn' => $salesR,
                    'sales_return_amt' => round(($salesR * $row->cost_price), 2),

                    'transfer_in_qtn' => $TransferIn,
                    'transfer_in_amt' => round(($TransferIn * $row->cost_price), 2),

                    'adj_qtn' => $adj,
                    'adj_amt' => round(($adj * $row->cost_price), 2),
                    /*add current priod end*/

                    /*less current priod start*/
                    'sales_qtn' => $sales,
                    'sales_amt' => round(($sales * $row->cost_price), 2),

                    'issue_return_qtn' => $issueReturn,
                    'issue_return_amt' => round(($issueReturn * $row->cost_price), 2),

                    'transfer_out_qtn' => $transferOut,
                    'transfer_out_amt' => round(($transferOut * $row->cost_price), 2),

                    'adj_qtn2' => $adj,
                    'adj_amt2' => round(($adj * $row->cost_price), 2),
                    /*less current priod end*/

                    'stock_qtn' => $stockQtn,
                    'stock_amt' => round(($stockQtn * $row->cost_price), 2),
                ];

                $os_ttl_qtn += $openning_stock;
                $is_ttl_qtn += $issue;
                $sr_ttl_qtn += $salesR;
                $tin_ttl_qtn += $TransferIn;
                $adj_ttl_qtn += $adj;
                $sales_ttl_qtn += $sales;
                $isr_ttl_qtn += $issueReturn;
                $tout_ttl_qtn += $transferOut;
                $adj2_ttl_qtn += $adj;
                $stock_ttl_qtn += $stockQtn;

                $os_ttl_amt += $TempSet['openning_stock_amt'];
                $is_ttl_amt += $TempSet['issue_amt'];
                $sr_ttl_amt += $TempSet['sales_return_amt'];
                $tin_ttl_amt += $TempSet['transfer_in_amt'];
                $adj_ttl_amt += $TempSet['adj_amt'];
                $sales_ttl_amt += $TempSet['sales_amt'];
                $isr_ttl_amt += $TempSet['issue_return_amt'];
                $tout_ttl_amt += $TempSet['transfer_out_amt'];
                $adj2_ttl_amt += $TempSet['adj_amt'];
                $stock_ttl_amt += $TempSet['stock_amt'];

                $DataSet[] = $TempSet;
            }

            $total_row = count($DataSet);

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                "totalRow" => $total_row,
                'os_ttl_qtn' => $os_ttl_qtn,
                'is_ttl_qtn' => $is_ttl_qtn,
                'sr_ttl_qtn' => $sr_ttl_qtn,
                'tin_ttl_qtn' => $tin_ttl_qtn,
                'adj_ttl_qtn' => $adj_ttl_qtn,
                'sales_ttl_qtn' => $sales_ttl_qtn,
                'isr_ttl_qtn' => $isr_ttl_qtn,
                'tout_ttl_qtn' => $tout_ttl_qtn,
                'adj2_ttl_qtn' => $adj2_ttl_qtn,
                'stock_ttl_qtn' => $stock_ttl_qtn,

                'os_ttl_amt' => number_format($os_ttl_amt, 2),
                'is_ttl_amt' => number_format($is_ttl_amt, 2),
                'sr_ttl_amt' => number_format($sr_ttl_amt, 2),
                'tin_ttl_amt' => number_format($tin_ttl_amt, 2),
                'adj_ttl_amt' => number_format($adj_ttl_amt, 2),
                'sales_ttl_amt' => number_format($sales_ttl_amt, 2),
                'isr_ttl_amt' => number_format($isr_ttl_amt, 2),
                'tout_ttl_amt' => number_format($tout_ttl_amt, 2),
                'adj2_ttl_amt' => number_format($adj2_ttl_amt, 2),
                'stock_ttl_amt' => number_format($stock_ttl_amt, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.stock_inv_branch');
        }
    }

    public function stockInvHO(Request $request)
    {
        if ($request->ajax()) {

            $sl = 1;

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $zoneId = (empty($request->input('zoneId'))) ? null : $request->input('zoneId');
            $areaId = (empty($request->input('areaId'))) ? null : $request->input('areaId');
            $branchId = 1;
            $supplierId = (empty($request->input('supplierId'))) ? null : $request->input('supplierId');
            $groupId = (empty($request->input('groupId'))) ? null : $request->input('groupId');
            $catId = (empty($request->input('catId'))) ? null : $request->input('catId');
            $subCatId = (empty($request->input('subCatId'))) ? null : $request->input('subCatId');
            $brandId = (empty($request->input('brandId'))) ? null : $request->input('brandId');
            $modelId = (empty($request->input('modelId'))) ? null : $request->input('modelId');
            $productId = (empty($request->input('productId'))) ? null : $request->input('productId');
            $stock = (empty($request->input('stock'))) ? null : $request->input('stock');

            $reportData = DB::table('pos_products as prod')
                ->where([['prod.is_delete', 0], ['prod.is_active', 1]])
                ->select('prod.id', DB::raw('CONCAT(prod.product_name, " (", prod.prod_barcode, ")") AS product_name'), 'prod.prod_barcode', 'prod.cost_price')
                ->where(function ($reportData) use ($supplierId, $groupId, $catId, $subCatId, $brandId, $modelId, $productId) {
                    if (!empty($supplierId)) {
                        $reportData->where('prod.supplier_id', $supplierId);
                    }
                    if (!empty($groupId)) {
                        $reportData->where('prod.prod_group_id', $groupId);
                    }
                    if (!empty($catId)) {
                        $reportData->where('prod.prod_cat_id', $catId);
                    }
                    if (!empty($subCatId)) {
                        $reportData->where('prod.prod_sub_cat_id', $subCatId);
                    }
                    if (!empty($brandId)) {
                        $reportData->where('prod.prod_brand_id', $brandId);
                    }
                    if (!empty($modelId)) {
                        $reportData->where('prod.prod_model_id', $modelId);
                    }
                    if (!empty($productId)) {
                        $reportData->where('prod.id', $productId);
                    }
                })
                ->orderBy('prod.id', 'ASC')
                ->get();

            $DataSet = array();
            $os_ttl_qtn = 0;
            $pur_ttl_qtn = 0;
            $isr_ttl_qtn = 0;
            $adj_ttl_qtn = 0;
            $pr_ttl_qtn = 0;
            $is_ttl_qtn = 0;
            $stock_ttl_qtn = 0;

            $os_ttl_amt = 0;
            $pur_ttl_amt = 0;
            $isr_ttl_amt = 0;
            $adj_ttl_amt = 0;
            $pr_ttl_amt = 0;
            $is_ttl_amt = 0;
            $stock_ttl_amt = 0;

            /*for multiple product*/
            $productId = $reportData->pluck('id')->all();
            $stockData = POSS::stockQuantity_Multiple($branchId, $productId, true, $startDate, $endDate);

            foreach ($reportData as $row) {

                $TempSet = array();

                $openning_stock = (isset($stockData[$row->id]['OpeningBalance'])) ? $stockData[$row->id]['OpeningBalance'] : 0;
                $purchase = (isset($stockData[$row->id]['Purchase'])) ? $stockData[$row->id]['Purchase'] : 0;
                $purchaseR = (isset($stockData[$row->id]['PurchaseReturn'])) ? $stockData[$row->id]['PurchaseReturn'] : 0;
                $Issue = (isset($stockData[$row->id]['Issue'])) ? $stockData[$row->id]['Issue'] : 0;
                $IssueReturn = (isset($stockData[$row->id]['IssueReturn'])) ? $stockData[$row->id]['IssueReturn'] : 0;
                $adj = (isset($stockData[$row->id]['Adjustment'])) ? $stockData[$row->id]['Adjustment'] : 0;

                $stockQtn = (isset($stockData[$row->id]['Stock'])) ? $stockData[$row->id]['Stock'] : 0;

                /*
                 *For without zero stock = 1
                 */
                if ($stock == 1 && $stockQtn == 0) {
                    continue;
                }

                $TempSet = [
                    'id' => $sl++,
                    'product_name' => $row->product_name,
                    // 'model_name' => $row->model_name,
                    // 'sys_barcode' => $row->sys_barcode,

                    'openning_stock_qtn' => $openning_stock,
                    'openning_stock_amt' => round(($openning_stock * $row->cost_price), 2),

                    /*add current priod start*/
                    'purchase_qtn' => $purchase,
                    'purchase_amt' => round(($purchase * $row->cost_price), 2),

                    'issue_return_qtn' => $IssueReturn,
                    'issue_return_amt' => round(($IssueReturn * $row->cost_price), 2),

                    'adj_qtn' => $adj,
                    'adj_amt' => round(($adj * $row->cost_price), 2),
                    /*add current priod end*/

                    /*less current priod start*/
                    'purchase_return_qtn' => $purchaseR,
                    'purchase_return_amt' => round(($purchaseR * $row->cost_price), 2),

                    'issue_qtn' => $Issue,
                    'issue_amt' => round(($Issue * $row->cost_price), 2),
                    /*less current priod end*/

                    'stock_qtn' => $stockQtn,
                    'stock_amt' => round(($stockQtn * $row->cost_price), 2),
                ];

                // $TempSet['stock_amt'] = round((($TempSet['openning_stock_amt'] + $TempSet['purchase_amt'] + $TempSet['issue_return_amt'] + $TempSet['adj_amt']) - ($TempSet['purchase_return_amt'] + $TempSet['issue_amt'])), 2);

                $os_ttl_qtn += $openning_stock;
                $pur_ttl_qtn += $purchase;
                $isr_ttl_qtn += $IssueReturn;
                $adj_ttl_qtn += $adj;
                $pr_ttl_qtn += $purchaseR;
                $is_ttl_qtn += $Issue;
                $stock_ttl_qtn += $stockQtn;

                $os_ttl_amt += $TempSet['openning_stock_amt'];
                $pur_ttl_amt += $TempSet['purchase_amt'];
                $isr_ttl_amt += $TempSet['issue_return_amt'];
                $adj_ttl_amt += $TempSet['adj_amt'];
                $pr_ttl_amt += $TempSet['purchase_return_amt'];
                $is_ttl_amt += $TempSet['issue_amt'];
                $stock_ttl_amt += $TempSet['stock_amt'];

                $DataSet[] = $TempSet;
            }

            $total_row = count($DataSet);

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                "totalRow" => $total_row,
                'os_ttl_qtn' => number_format($os_ttl_qtn, 2),
                'pur_ttl_qtn' => number_format($pur_ttl_qtn, 2),
                'isr_ttl_qtn' => number_format($isr_ttl_qtn, 2),
                'adj_ttl_qtn' => number_format($adj_ttl_qtn, 2),
                'pr_ttl_qtn' => number_format($pr_ttl_qtn, 2),
                'is_ttl_qtn' => number_format($is_ttl_qtn, 2),
                'stock_ttl_qtn' => number_format($stock_ttl_qtn, 2),

                'os_ttl_amt' => number_format($os_ttl_amt, 2),
                'pur_ttl_amt' => number_format($pur_ttl_amt, 2),
                'isr_ttl_amt' => number_format($isr_ttl_amt, 2),
                'adj_ttl_amt' => number_format($adj_ttl_amt, 2),
                'pr_ttl_amt' => number_format($pr_ttl_amt, 2),
                'is_ttl_amt' => number_format($is_ttl_amt, 2),
                'stock_ttl_amt' => number_format($stock_ttl_amt, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.stock_inv_ho');
        }
    }

/* ------------------------ Stock Report End */

/* ------------------------ Collection Report Start */
    public function getCollectionSheet(Request $request)
    {
        if ($request->ajax()) {

            // Datatable Pagination variable
            $DataSet = array();

            $totalData = 0;
            $totalFiltered = $totalData;
            $sl = 1;
            $total_row = 0;

            $ttl_sales_amount = 0;
            $ttl_collection_amount = 0;
            $ttl_due_amount = 0;
            $ttl_quantity = 0;
            $ttl_first_week = 0;
            $ttl_second_week = 0;
            $ttl_third_week = 0;
            $ttl_fourth_week = 0;
            $ttl_fifth_week = 0;

            // Searching variable
            $branchId = (empty($request->input('branchId'))) ? null : $request->input('branchId');
            $employeeId = (empty($request->input('employeeId'))) ? null : $request->input('employeeId');

            $firstWeek = (empty($request->input('firstWeek'))) ? null : new DateTime($request->input('firstWeek'));
            $endfirstWeek = (empty($request->input('endfirstWeek'))) ? null : new DateTime($request->input('endfirstWeek'));
            $secondWeek = (empty($request->input('secondWeek'))) ? null : new DateTime($request->input('secondWeek'));
            $endsecondWeek = (empty($request->input('endsecondWeek'))) ? null : new DateTime($request->input('endsecondWeek'));
            $thirdWeek = (empty($request->input('thirdWeek'))) ? null : new DateTime($request->input('thirdWeek'));
            $endthirdWeek = (empty($request->input('endthirdWeek'))) ? null : new DateTime($request->input('endthirdWeek'));
            $forthWeek = (empty($request->input('forthWeek'))) ? null : new DateTime($request->input('forthWeek'));
            $endforthWeek = (empty($request->input('endforthWeek'))) ? null : new DateTime($request->input('endforthWeek'));
            $fifthWeek = (empty($request->input('fifthWeek'))) ? null : new DateTime($request->input('fifthWeek'));
            $endFifthWeek = (empty($request->input('endFifthWeek'))) ? null : new DateTime($request->input('endFifthWeek'));

            $monthYearsel = (empty($request->input('monthYearsel'))) ? null : (new DateTime("01" . $request->input('monthYearsel')))->format('Y-m');

            // dd($monthYearsel);

            if (!empty($branchId) && !empty($employeeId) && !empty($firstWeek) && !empty($endFifthWeek)) {

                // // Query
                $collection_sheet_data = DB::table('pos_sales_m as psm')
                    ->where([['psm.sales_type', 2], ['psm.is_delete', 0], ['psm.is_complete', 0]])
                    ->whereIn('psm.branch_id', HRS::getUserAccesableBranchIds())

                    ->leftjoin('pos_customers as cust', function ($collection_sheet_data) {
                        $collection_sheet_data->on('cust.customer_no', 'psm.customer_id')
                            ->where([['cust.is_delete', 0], ['cust.is_active', 1]]);
                    })
                    ->leftjoin('hr_employees as emp', function ($collection_sheet_data) {
                        $collection_sheet_data->on('emp.employee_no', 'psm.employee_id')
                            ->where([['emp.is_delete', 0], ['emp.is_active', 1]]);
                    })
                    ->select('psm.id', 'cust.customer_name', 'cust.father_name', 'cust.spouse_name', 'cust.pre_village_id', 'cust.customer_no', 'gv.village_name', 'emp.emp_name', 'emp.employee_no',
                        'psm.sales_bill_no', 'psm.sales_date', 'psm.total_quantity as product_quantity',
                        'psm.inst_package_id', 'psm.installment_month', 'psm.installment_type', 'psm.installment_rate', 'psm.installment_amount',
                        'psm.total_amount as sales_amount', 'psm.service_charge as processing_fee', 'psm.company_id', 'psm.branch_id',
                        DB::raw('CONCAT(cust.customer_name, " (", cust.customer_no,")") as customer_name_txt,
                                CONCAT(emp.emp_name, " (", emp.employee_no,")") as emp_name_txt')
                    )
                    ->leftjoin('gnl_villages as gv', function ($collection_sheet_data) {
                        $collection_sheet_data->on('gv.id', 'cust.pre_village_id')
                            ->where([['gv.is_delete', 0], ['gv.is_active', 1]]);
                    })
                    ->addSelect(['ttl_paid_amount' => DB::table('pos_collections as col')
                            ->select(DB::raw('IFNULL(SUM(collection_amount), 0)'))
                            ->whereColumn('col.sales_bill_no', 'psm.sales_bill_no')
                        // ->where([['col.is_delete', 0], ['col.is_active', 1], ['col.collection_date', '<', $firstWeek->format('Y-m-d')]])
                            ->where([['col.is_delete', 0], ['col.is_active', 1], ['col.collection_date', '<', $endFifthWeek->format('Y-m-d')]])
                            ->limit(1),
                    ])
                // ->addSelect(['product_name' => DB::table('pos_products as prod')
                //         ->select(DB::raw('GROUP_CONCAT(prod.product_name)'))
                //         ->whereColumn('prod.id', 'psd.product_id')
                //         ->where([['prod.is_delete', 0], ['prod.is_active', 1]])
                //         ->limit(1),
                // ])
                    ->where(function ($collection_sheet_data) use ($monthYearsel) {
                        if (!empty($monthYearsel)) {
                            $collection_sheet_data->whereRaw('CASE WHEN psm.installment_type = 1 THEN psm.sales_date NOT LIKE "' . $monthYearsel . '%" ELSE 1=1 END');
                        }
                    })
                    ->where(function ($collection_sheet_data) use ($branchId) {
                        if (!empty($branchId)) {
                            $collection_sheet_data->where('psm.branch_id', '=', $branchId);
                        }
                    })
                    ->where(function ($collection_sheet_data) use ($employeeId) {
                        if (!empty($employeeId)) {
                            $collection_sheet_data->where('psm.employee_id', '=', $employeeId);
                        }
                    })
                    ->having(DB::raw("(sales_amount - ttl_paid_amount)"), '>', 0)
                    ->orderBy('psm.sales_date', 'ASC')
                    ->orderBy('psm.id', 'ASC')
                    ->get();

                // dd($collection_sheet_data);

                $billNoList = $collection_sheet_data->pluck('sales_bill_no');
                $detailsData = DB::table('pos_sales_d as dt')
                    ->whereIn('dt.sales_bill_no', $billNoList->toarray())
                    ->join('pos_products as pro', function ($detailsData) {
                        $detailsData->on('pro.id', '=', 'dt.product_id')
                            ->where('pro.is_delete', 0);
                    })
                    ->select('dt.sales_bill_no', 'pro.product_name')
                    ->get();

                $i = 0;
                foreach ($collection_sheet_data as $row) {

                    $product_names = $detailsData->where('sales_bill_no', $row->sales_bill_no)
                        ->pluck('product_name')
                        ->toArray();

                    $balance_amount = ($row->sales_amount - $row->ttl_paid_amount);

                    $installment_amount = $row->installment_amount;
                    if ($balance_amount < $row->installment_amount) {
                        $installment_amount = $balance_amount;
                    }

                    $InstallmentDates = POSS::installmentSchedule($row->company_id, $row->branch_id, null,
                        $row->sales_date, $row->installment_type, $row->installment_month);

                    $first_week = 0;
                    $second_week = 0;
                    $third_week = 0;
                    $fourth_week = 0;
                    $fifth_week = 0;

                    $shFlag = false;

                    foreach ($InstallmentDates as $insRow) {
                        $insRow = new DateTime($insRow);

                        if (($firstWeek <= $insRow) && ($endfirstWeek >= $insRow)) {

                            if ($row->installment_type == 2 && $row->sales_date == $insRow->format('Y-m-d')) {
                                continue;
                            } else {
                                $shFlag = true;
                                $first_week = $installment_amount;
                                // break;
                            }
                        }

                        if (($secondWeek <= $insRow) && ($endsecondWeek >= $insRow)) {
                            if ($row->installment_type == 2 && $row->sales_date == $insRow->format('Y-m-d')) {
                                continue;
                            } else {
                                $shFlag = true;
                                $second_week = $installment_amount;
                                // break;
                            }
                        }

                        if (($thirdWeek <= $insRow) && ($endthirdWeek >= $insRow)) {
                            if ($row->installment_type == 2 && $row->sales_date == $insRow->format('Y-m-d')) {
                                continue;
                            } else {
                                $shFlag = true;
                                $third_week = $installment_amount;
                                // break;
                            }
                        }

                        if (($forthWeek <= $insRow) && ($endforthWeek >= $insRow)) {
                            if ($row->installment_type == 2 && $row->sales_date == $insRow->format('Y-m-d')) {
                                continue;
                            } else {
                                $shFlag = true;
                                $fourth_week = $installment_amount;
                                // break;
                            }
                        }

                        if (($fifthWeek <= $insRow) && ($endFifthWeek >= $insRow)) {
                            if ($row->installment_type == 2 && $row->sales_date == $insRow->format('Y-m-d')) {
                                continue;
                            } else {
                                $shFlag = true;
                                $fifth_week = $installment_amount;
                                // break;
                            }
                        }
                    }

                    if ($shFlag) {
                        $ttl_sales_amount += $row->sales_amount;
                        $ttl_collection_amount += $row->ttl_paid_amount;
                        $ttl_due_amount += $balance_amount;
                        $ttl_quantity += $row->product_quantity;
                        $ttl_first_week += $first_week;
                        $ttl_second_week += $second_week;
                        $ttl_third_week += $third_week;
                        $ttl_fourth_week += $fourth_week;
                        $ttl_fifth_week += $fifth_week;

                        $TempSet = array();

                        $customer_info = $row->customer_name_txt;
                        $customer_info .= (!empty($row->spouse_name)) ? '<br><strong>Spouse:</strong> ' . $row->spouse_name :
                        (!empty($row->father_name)) ? '<br><strong>Father:</strong> ' . $row->father_name : '';

                        $customer_info .= (!empty($row->village_name)) ? '<br><strong>Village:</strong> ' . $row->village_name : '';

                        $TempSet = [
                            'sl' => $sl++,
                            'customer_name' => $customer_info,
                            // 'customer_no' => $row->customer_no,
                            'sales_bill_no' => $row->sales_bill_no,
                            'sales_by' => $row->emp_name_txt,
                            'product_name' => implode(', ', $product_names),
                            'product_quantity' => $row->product_quantity,
                            'sales_amount' => number_format($row->sales_amount, 2),
                            'collection_amount' => number_format($row->ttl_paid_amount, 2),
                            'balance_amount' => number_format($balance_amount, 2),
                            'first_week' => number_format($first_week, 2),
                            'second_week' => number_format($second_week, 2),
                            'third_week' => number_format($third_week, 2),
                            'fourth_week' => number_format($fourth_week, 2),
                            'fifth_week' => number_format($fifth_week, 2),
                        ];

                        $DataSet[] = $TempSet;
                    }
                }
            }

            $total_row = count($DataSet);
            $totalData = $total_row;
            $totalFiltered = $total_row;

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $DataSet,
                "totalRow" => $total_row,
                "ttl_sales_amount" => number_format($ttl_sales_amount, 2),
                "ttl_collection_amount" => number_format($ttl_collection_amount, 2),
                "ttl_due_amount" => number_format($ttl_due_amount, 2),
                "ttl_quantity" => $ttl_quantity,
                "ttl_first_week" => number_format($ttl_first_week, 2),
                "ttl_second_week" => number_format($ttl_second_week, 2),
                "ttl_third_week" => number_format($ttl_third_week, 2),
                "ttl_fourth_week" => number_format($ttl_fourth_week, 2),
                "ttl_fifth_week" => number_format($ttl_fifth_week, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.collection_sheet');
        }
    }

    public function getCollectionAll(Request $request)
    {
        if ($request->ajax()) {
            $sl = 1;

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $salesBillNo = (empty($request->input('salesBillNo'))) ? null : $request->input('salesBillNo');
            $branchId = (empty($request->input('branchId'))) ? null : $request->input('branchId');
            // $productId = (empty($request->input('productId'))) ? null : $request->input('productId');

            // Query
            $allcollectData = DB::table('pos_collections as pcollect')
                ->where([['pcollect.is_delete', 0]])
                ->whereIn('pcollect.branch_id', HRS::getUserAccesableBranchIds())
                ->select('pcollect.*', 'pc.customer_no', 'pc.customer_name', 'psm.*', 'pcollect.sales_bill_no as bill_no',
                    DB::raw('CONCAT(pc.customer_name, " (", pc.customer_no,")") as customer_name'))

                ->leftjoin('pos_customers as pc', function ($allcollectData) {
                    $allcollectData->on('pcollect.customer_id', '=', 'pc.customer_no')
                        ->where([['pc.is_delete', 0]]);
                })
                ->leftjoin('pos_sales_m as psm', function ($allcollectData) {
                    $allcollectData->on('pcollect.sales_bill_no', '=', 'psm.sales_bill_no')
                        ->where([['psm.is_delete', 0]]);
                })
                ->where(function ($allcollectData) use ($search, $startDate, $endDate) {
                    if (!empty($startDate) && !empty($endDate)) {
                        $startDate = (new DateTime($startDate))->format('Y-m-d');
                        $endDate = (new DateTime($endDate))->format('Y-m-d');

                        $allcollectData->whereBetween('pcollect.collection_date', [$startDate, $endDate]);
                    }
                })
                ->where(function ($allcollectData) use ($search, $branchId) {
                    if (!empty($branchId)) {
                        $allcollectData->where('pcollect.branch_id', '=', $branchId);
                    }
                })
                ->where(function ($allcollectData) use ($search, $salesBillNo) {
                    if (!empty($salesBillNo)) {
                        $allcollectData->where('pcollect.sales_bill_no', '=', $salesBillNo);
                    }
                })
                ->orderBy('pcollect.collection_date', 'ASC')
                ->orderBy('pcollect.id', 'ASC')
                ->get();
            // dd($allcollectData);
            $total_row = count($allcollectData);

            // $ttl_sales_amount = $allcollectData->sum('total_amount');
            // $ttl_service_charge = $allcollectData->sum('service_charge');
            // $total_payable_amount = $ttl_sales_amount + $ttl_service_charge;
            // $ttl_collection_amount = $allcollectData->sum('collection_amount');

            $ttl_sales_amount = 0;
            $ttl_processing_fee = 0;
            $ttl_total_sales = 0;
            $ttl_collection_amount = 0;
            $ttl_total_collection = 0;

            if (!empty($search)) {
                $totalFiltered = count($allcollectData);
            }

            $DataSet = array();
            $i = 0;
            foreach ($allcollectData as $row) {
                $TempSet = array();

                $sales_amount = $row->total_amount;
                $sales_amount_un = 0;
                $processing_fee = 0;
                $total_sales = 0;
                $total_sales_un = 0;
                $collection_amount = $row->collection_amount;
                $total_collection = 0;

                if ($row->collection_date == $row->sales_date) {
                    $processing_fee = $row->service_charge;
                    $sales_amount_un = $row->total_amount;
                    $total_sales_un = $sales_amount_un + $processing_fee;
                }

                $total_sales = $sales_amount + $processing_fee;

                $total_collection = $collection_amount + $processing_fee;

                $ttl_sales_amount += $sales_amount_un;
                $ttl_processing_fee += $processing_fee;
                $ttl_total_sales += $total_sales_un;
                $ttl_collection_amount += $collection_amount;
                $ttl_total_collection += $total_collection;

                $TempSet = [
                    'sl' => $sl++,
                    'collection_date' => (new DateTime($row->collection_date))->format('d-m-Y'),
                    'collection_no' => $row->collection_no,
                    'customer_name' => $row->customer_name,
                    'sales_bill_no' => $row->bill_no,
                    'sales_amount' => number_format($sales_amount, 2),
                    'processing_fee' => number_format($processing_fee, 2),
                    'total_sales' => number_format($total_sales, 2),
                    'collection_amount' => number_format($collection_amount, 2),
                    'total_collection' => number_format($total_collection, 2),
                ];

                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                "totalRow" => $total_row,
                "ttl_sales_amount" => number_format($ttl_sales_amount, 2),
                "ttl_processing_fee" => number_format($ttl_processing_fee, 2),
                "ttl_total_sales" => number_format($ttl_total_sales, 2),
                "ttl_collection_amount" => number_format($ttl_collection_amount, 2),
                "ttl_total_collection" => number_format($ttl_total_collection, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.collection_report_all');
        }
    }

    public function customerDue(Request $request)
    {
        if ($request->ajax()) {

            $totalData = 0;
            $totalFiltered = $totalData;
            $sl = 1;

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $branchID = (empty($request->input('branchID'))) ? null : $request->input('branchID');
            $employeeID = (empty($request->input('employeeID'))) ? null : $request->input('employeeID');
            $customerID = (empty($request->input('CustomerID'))) ? null : $request->input('CustomerID');
            $saleBillNo = (empty($request->input('SaleBillNo'))) ? null : $request->input('SaleBillNo');

            $custDueRData = DB::table('pos_sales_m as sm')
                ->where([['sm.sales_type', 2], ['sm.is_delete', 0]])
                ->select('prod.product_name as products', 'sm.sales_bill_no', 'sm.sales_date', 'sm.total_amount as sales_amt',
                    'sm.service_charge as pro_fee', 'sm.total_quantity as product_quantity',
                    'c.customer_no', 'c.customer_name', 'c.customer_mobile', 'c.customer_nid', 'c.spouse_name',
                    DB::raw('(sm.total_amount + sm.service_charge) as gross_total'),
                    // DB::raw('SUM(pcol.collection_amount) as paid_amount'),
                    DB::raw('CONCAT(c.customer_name, " (", c.customer_no,")") as customer_name'))
                ->leftjoin('pos_customers as c', function ($custDueRData) {
                    $custDueRData->on('c.customer_no', 'sm.customer_id')
                        ->where('c.is_delete', 0);
                })
            // ->addSelect(['products' => SalesDetails::select('product_name')
            //         ->whereColumn('sales_bill_no', 'sm.sales_bill_no')
            //         ->orderBy('sales_bill_no', 'ASC')
            //         ->limit(1),
            // ])
                ->leftjoin('pos_sales_d as psd', function ($collection_sheet_data) {
                    $collection_sheet_data->on('psd.sales_bill_no', 'sm.sales_bill_no');
                })
                ->join('pos_products as prod', function ($collection_sheet_data) {
                    $collection_sheet_data->on('prod.id', 'psd.product_id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->addSelect(['paid_amount' => DB::table('pos_collections')
                        ->selectRaw('SUM(collection_amount) as  paid_amount')
                        ->whereColumn('sales_bill_no', 'sm.sales_bill_no')
                        ->where([['is_delete', 0], ['is_active', 1]])
                        ->whereIn('branch_id', HRS::getUserAccesableBranchIds())
                        ->groupBy('sales_bill_no')
                        ->limit(1),
                ])
            // ->leftjoin('pos_collections as pcol', function ($collection_sheet_data) {
            //     $collection_sheet_data->on('pcol.sales_bill_no', 'sm.sales_bill_no')
            //         ->where([['pcol.is_delete', 0], ['pcol.is_active', 1]])
            //         ->whereIn('pcol.branch_id', HRS::getUserAccesableBranchIds());
            // })
                ->where(function ($custDueRData) use ($branchID) {
                    if (!empty($branchID)) {
                        $custDueRData->where('sm.branch_id', $branchID);
                    }
                })
                ->where(function ($custDueRData) use ($employeeID) {
                    if (!empty($employeeID)) {
                        $custDueRData->where('sm.employee_id', $employeeID);
                    }
                })
                ->where(function ($custDueRData) use ($customerID) {
                    if (!empty($customerID)) {
                        $custDueRData->where('sm.customer_id', $customerID);
                    }
                })
                ->where(function ($custDueRData) use ($saleBillNo) {
                    if (!empty($saleBillNo)) {
                        $custDueRData->where('sm.sales_bill_no', $saleBillNo);
                    }
                })
                ->groupBy('sm.sales_bill_no') // for sum of collection amount
                ->orderBy('sm.sales_date', 'ASC')
                ->orderBy('sm.id', 'ASC')
                ->get();

            $total_row = count($custDueRData);

            $ttl_qnt = $custDueRData->sum('product_quantity');
            $ttl_sales_amount = $custDueRData->sum('sales_amt');
            $ttl_service_charge = $custDueRData->sum('pro_fee');
            $ttl_gross_amount = $custDueRData->sum('gross_total');
            $ttl_paid_amount = $custDueRData->sum('paid_amount');
            $ttl_due_amount = 0;

            if (!empty($search)) {
                $totalFiltered = count($custDueRData);
            }

            $DataSet = array();

            foreach ($custDueRData as $row) {
                $TempSet = array();

                // $customer_info = "<b>Code: </b>".$row->customer_no;
                $customer_info = "<b>Name: </b>" . $row->customer_name;
                $customer_info .= "<br><b>Mobile: </b>" . $row->customer_mobile;
                $customer_info .= "<br><b>NID: </b>" . $row->customer_nid;
                $customer_info .= "<br><b>Spouse: </b>" . $row->spouse_name;

                $TempSet = [
                    'sl' => $sl++,
                    'customer_info' => $customer_info,
                    // 'customer_no' => $row->customer_no,
                    // 'customer_name' => $row->customer_name,
                    // 'mobile' => $row->customer_mobile,
                    // 'cus_nid' => $row->customer_nid,
                    // 'spouse_name' => $row->spouse_name,
                    'bill_no' => $row->sales_bill_no,
                    'sales_date' => (new Datetime($row->sales_date))->format('d-m-Y'),
                    'product' => $row->products,
                    'quantity' => $row->product_quantity,
                    'sales_amount' => $row->sales_amt,
                    'processing_fee' => $row->pro_fee,
                    'gross_total' => $row->gross_total,
                    'paid_amount' => $row->paid_amount,
                    'due_amount' => ($row->gross_total - $row->paid_amount),
                ];

                $ttl_due_amount += $TempSet['due_amount'];
                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $DataSet,
                "totalRow" => $total_row,
                "ttl_qnt" => $ttl_qnt,
                "ttl_sales_amount" => number_format($ttl_sales_amount, 2),
                "ttl_service_charge" => number_format($ttl_service_charge, 2),
                "ttl_gross_amount" => number_format($ttl_gross_amount, 2),
                "ttl_paid_amount" => number_format($ttl_paid_amount, 2),
                "ttl_due_amount" => number_format($ttl_due_amount, 2),
            );

            echo json_encode($json_data);

        } else {
            return view('POS.Report.collection_customer_due');
        }
    }

    public function getCollectionWithProfit(Request $request)
    {
        if ($request->ajax()) {

            // Datatable Pagination Variable
            $totalData = Collection::where([['is_delete', 0]])->count();
            $totalFiltered = $totalData;
            $sl = 1;

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $branchId = (empty($request->input('branchId'))) ? null : $request->input('branchId');

            // Query
            $allcollectData = DB::table('pos_collections as pcollect')
                ->where([['pcollect.is_delete', 0], ['pcollect.is_active', 1]])
                ->whereIn('pcollect.branch_id', HRS::getUserAccesableBranchIds())
                ->select('pcollect.*', 'pc.customer_no', 'pc.customer_name', 'psm.*', 'pcollect.sales_bill_no as bill_no',
                    DB::raw('CONCAT(pc.customer_name, " (", pc.customer_no,")") as customer_name'))

                ->leftjoin('pos_customers as pc', function ($allcollectData) {
                    $allcollectData->on('pcollect.customer_id', '=', 'pc.customer_no')
                        ->where([['pc.is_delete', 0], ['pc.is_active', 01]]);
                })
                ->leftjoin('pos_sales_m as psm', function ($allcollectData) {
                    $allcollectData->on('pcollect.sales_bill_no', '=', 'psm.sales_bill_no')
                        ->where([['psm.is_delete', 0], ['psm.is_active', 1]]);
                })
                ->where(function ($allcollectData) use ($startDate, $endDate) {
                    if (!empty($startDate) && !empty($endDate)) {
                        $startDate = (new DateTime($startDate))->format('Y-m-d');
                        $endDate = (new DateTime($endDate))->format('Y-m-d');
                        $allcollectData->whereBetween('pcollect.collection_date', [$startDate, $endDate]);
                    }
                })
                ->where(function ($allcollectData) use ($branchId) {
                    if (!empty($branchId)) {
                        $allcollectData->where('pcollect.branch_id', '=', $branchId);
                    }
                })
                ->orderBy('pcollect.collection_date', 'ASC')
                ->orderBy('pcollect.id', 'ASC')
                ->get();

            $total_row = count($allcollectData);
            $ttl_sales_amount = $allcollectData->sum('total_amount');
            $ttl_vat_amount = $allcollectData->sum('vat_amount');
            $ttl_discount_amount = $allcollectData->sum('discount_amount');
            $ttl_service_charge = $allcollectData->sum('service_charge');
            $total_pf_col_amount = $ttl_sales_amount + $ttl_vat_amount + $ttl_discount_amount;
            $ttl_collection_amount = $allcollectData->sum('collection_amount');
            $ttl_principal_amount = $allcollectData->sum('principal_amount');
            $ttl_installment_profit = $allcollectData->sum('installment_profit');
            if (!empty($search)) {
                $totalFiltered = count($allcollectData);
            }
            $DataSet = array();
            $i = 0;
            foreach ($allcollectData as $row) {
                $TempSet = array();
                $TempSet = [
                    'sl' => $sl++,
                    'collection_date' => (new DateTime($row->collection_date))->format('d-m-Y'),
                    'customer_name' => $row->customer_name,
                    'sales_bill_no' => $row->bill_no,
                    // 'customer_no' => $row->customer_no,
                    'total_amount' => $row->total_amount,
                    'vat_amount' => $row->vat_amount,
                    'discount_amount' => $row->discount_amount,
                    'service_charge' => $row->service_charge,
                    'pf_collection_amount' => $row->total_amount + $row->vat_amount - $row->discount_amount,
                    'collection_amount' => $row->collection_amount,
                    'principal_amount' => $row->principal_amount,
                    'installment_profit' => $row->installment_profit,
                ];
                $DataSet[] = $TempSet;
            }
            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $DataSet,
                "totalRow" => $total_row,
                "total_pf_col_amount" => number_format($total_pf_col_amount, 2),
                "ttl_service_charge" => number_format($ttl_service_charge, 2),
                "ttl_collection_amount" => number_format($ttl_collection_amount, 2),
                "ttl_principal_amount" => number_format($ttl_principal_amount, 2),
                "ttl_installment_profit" => number_format($ttl_installment_profit, 2),
            );
            echo json_encode($json_data);
        } else {
            return view('POS.Report.collection_report_profit');
        }
    }
/* ------------------------ Collection Report End */

/* ------------------------ Due Report Start */
    public function getCurrentDue(Request $request)
    {
        if ($request->ajax()) {

            $companyID = Common::getCompanyId();
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $branchID = (empty($request->input('branchId'))) ? null : $request->input('branchId');
            $endDate = (new DateTime($endDate))->format('Y-m-d');

            if (empty($branchID)) {
                $selBranchArr = HRS::getUserAccesableBranchIds();
            } else {
                $selBranchArr = [$branchID];
            }

            // ## due_calculation function calling
            $reportDataSet = POSS::due_calculation($companyID, $selBranchArr, $endDate, 'current_due', 'report');
            // dd($reportDataSet);

            // ## Report Data View
            $DataSet = (($reportDataSet) && isset($reportDataSet['report_data'])) ? unserialize($reportDataSet['report_data']) : array();

            $ttl_sales_amount = (($reportDataSet) && isset($reportDataSet['ttl_sales_amount'])) ? $reportDataSet['ttl_sales_amount'] : 0;
            $ttl_payable_amount = (($reportDataSet) && isset($reportDataSet['ttl_payable_amount'])) ? $reportDataSet['ttl_payable_amount'] : 0;
            $ttl_paid_amount = (($reportDataSet) && isset($reportDataSet['ttl_paid_amount'])) ? $reportDataSet['ttl_paid_amount'] : 0;
            $ttl_current_due = (($reportDataSet) && isset($reportDataSet['ttl_current_due'])) ? $reportDataSet['ttl_current_due'] : 0;
            $ttl_total_balance = (($reportDataSet) && isset($reportDataSet['ttl_total_balance'])) ? $reportDataSet['ttl_total_balance'] : 0;

            $total_row = count($DataSet);

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($total_row),
                "data" => $DataSet,
                "totalRow" => $total_row,
                "ttl_sales_amount" => number_format($ttl_sales_amount, 2),
                "ttl_payable_amount" => number_format($ttl_payable_amount, 2),
                // "ttl_inst_amount" => number_format($ttl_inst_amount, 2),
                "ttl_paid_amt" => number_format($ttl_paid_amount, 2),
                "ttl_current_due" => number_format($ttl_current_due, 2),
                "ttl_total_balance" => number_format($ttl_total_balance, 2),
            );

            echo json_encode($json_data);

        } else {
            return view('POS.Report.due_report_current');
        }
    }

    public function getOverDue(Request $request)
    {
        if ($request->ajax()) {

            $companyID = Common::getCompanyId();
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $branchID = (empty($request->input('branchId'))) ? null : $request->input('branchId');
            $endDate = (new DateTime($endDate))->format('Y-m-d');

            if (empty($branchID)) {
                $selBranchArr = HRS::getUserAccesableBranchIds();
            } else {
                $selBranchArr = [$branchID];
            }

            // ## due_calculation function calling
            $reportDataSet = POSS::due_calculation($companyID, $selBranchArr, $endDate, 'over_due', 'report');
            // dd($reportDataSet);

            // ## Report Data View
            $DataSet = (($reportDataSet) && isset($reportDataSet['report_data'])) ? unserialize($reportDataSet['report_data']) : array();

            $ttl_sales_amount = (($reportDataSet) && isset($reportDataSet['ttl_sales_amount'])) ? $reportDataSet['ttl_sales_amount'] : 0;
            $ttl_paid_amount = (($reportDataSet) && isset($reportDataSet['ttl_paid_amount'])) ? $reportDataSet['ttl_paid_amount'] : 0;
            $ttl_over_due = (($reportDataSet) && isset($reportDataSet['ttl_over_due'])) ? $reportDataSet['ttl_over_due'] : 0;

            $total_row = count($DataSet);

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($total_row),
                "data" => $DataSet,
                "totalRow" => $total_row,
                "ttl_sales_amount" => number_format($ttl_sales_amount, 2),
                "ttl_paid_amount" => number_format($ttl_paid_amount, 2),
                "ttl_over_due" => number_format($ttl_over_due, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.due_report_over');
        }
    }

    public function getCurrentnOverDue(Request $request)
    {
        if ($request->ajax()) {

            $companyID = Common::getCompanyId();
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $zoneId = (empty($request->input('zoneId'))) ? null : $request->input('zoneId');
            $areaId = (empty($request->input('areaId'))) ? null : $request->input('areaId');
            $branchID = (empty($request->input('branchId'))) ? null : $request->input('branchId');

            $endDate = (new DateTime($endDate))->format('Y-m-d');

            if (empty($branchID)) {

                if (!empty($zoneId)) {
                    $zoneQuery = DB::table('gnl_zones')
                        ->where([['is_active', 1], ['is_delete', 0], ['id', $zoneId]])
                        ->where(function ($zoneQuery) use ($companyID) {
                            if (!empty($companyID)) {
                                $zoneQuery->where('company_id', $companyID);
                            }
                        })
                        ->select('branch_arr')
                        ->first();

                    if ($zoneQuery) {
                        $selBranchArr = explode(',', $zoneQuery->branch_arr);
                    } else {
                        $selBranchArr = HRS::getUserAccesableBranchIds();
                    }

                } elseif (!empty($areaId)) {
                    $areaQuery = DB::table('gnl_areas')
                        ->where([['is_active', 1], ['is_delete', 0], ['id', $areaId]])
                        ->where(function ($areaQuery) use ($companyID) {
                            if (!empty($companyID)) {
                                $areaQuery->where('company_id', $companyID);
                            }
                        })
                        ->select('branch_arr')
                        ->first();

                    if ($areaQuery) {
                        $selBranchArr = explode(',', $areaQuery->branch_arr);
                    } else {
                        $selBranchArr = HRS::getUserAccesableBranchIds();
                    }

                } else {
                    $selBranchArr = HRS::getUserAccesableBranchIds();
                }
            } else {
                $selBranchArr = [$branchID];
            }

            // ## due_calculation function calling
            $reportDataSet = POSS::due_calculation($companyID, $selBranchArr, $endDate, 'current_and_over_due', 'report');
            // dd($reportDataSet);

            // ## Report Data View
            $DataSet = (($reportDataSet) && isset($reportDataSet['report_data'])) ? unserialize($reportDataSet['report_data']) : array();

            $ttl_sales_amount = (($reportDataSet) && isset($reportDataSet['ttl_sales_amount'])) ? $reportDataSet['ttl_sales_amount'] : 0;
            $ttl_payable_amount = (($reportDataSet) && isset($reportDataSet['ttl_payable_amount'])) ? $reportDataSet['ttl_payable_amount'] : 0;
            $ttl_paid_amount = (($reportDataSet) && isset($reportDataSet['ttl_paid_amount'])) ? $reportDataSet['ttl_paid_amount'] : 0;
            $ttl_current_due = (($reportDataSet) && isset($reportDataSet['ttl_current_due'])) ? $reportDataSet['ttl_current_due'] : 0;
            $ttl_over_due = (($reportDataSet) && isset($reportDataSet['ttl_over_due'])) ? $reportDataSet['ttl_over_due'] : 0;
            $ttl_total_balance = (($reportDataSet) && isset($reportDataSet['ttl_total_balance'])) ? $reportDataSet['ttl_total_balance'] : 0;

            $total_row = count($DataSet);

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($total_row),
                "data" => $DataSet,
                "totalRow" => $total_row,
                "ttl_sales_amount" => number_format($ttl_sales_amount, 2),
                "ttl_payable_amount" => number_format($ttl_payable_amount, 2),
                "ttl_paid_amount" => number_format($ttl_paid_amount, 2),
                "ttl_current_due" => number_format($ttl_current_due, 2),
                "ttl_over_due" => number_format($ttl_over_due, 2),
                "ttl_total_balance" => number_format($ttl_total_balance, 2),
            );

            echo json_encode($json_data);

        } else {
            return view('POS.Report.due_report_current_over');
        }
    }

    public function old_getCurrentDue(Request $request)
    {
        if ($request->ajax()) {

            // Test Commit

            $companyID = Common::getCompanyId();
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $branchID = (empty($request->input('branchId'))) ? null : $request->input('branchId');

            $endDate = new DateTime($endDate);
            $endDate = $endDate->format('Y-m-d');

            $currentDue = DB::table('pos_sales_m as sm')
                ->where([['sm.is_delete', 0], ['sm.sales_type', 2]])
                ->whereIn('sm.branch_id', HRS::getUserAccesableBranchIds())
                ->select('sm.branch_id', 'c.customer_name', 'c.customer_no', 'sm.sales_bill_no',
                    'sm.sales_date', 'sm.total_amount as sales_amount', 'sm.installment_amount',
                    'sm.installment_type', 'sm.installment_month',
                    DB::raw('
                    (CASE
                        WHEN sm.installment_type = 1 THEN sm.installment_month
                        ELSE (FLOOR(DATEDIFF(DATE(DATE_FORMAT(DATE_ADD(sm.sales_date, INTERVAL +sm.installment_month MONTH), "%Y%m%d")), DATE(DATE_FORMAT(sm.sales_date, "%Y%m%d")))/7))
                    END) as ttl_installment,
                    IFNULL(SUM(
                        CASE
                            WHEN col.collection_date < "' . $endDate . '"
                            THEN col.collection_amount
                        END
                    ), 0) as ttl_clln_amt,
                    (sm.paid_amount - sm.vat_amount - sm.service_charge) as first_instalment
                    '),
                    DB::raw('CONCAT(c.customer_name, " (", c.customer_no, ")") AS customer_name')
                )
                ->leftjoin('pos_customers as c', function ($currentDue) {
                    $currentDue->on('c.customer_no', '=', 'sm.customer_id')
                        ->where([['c.is_delete', 0], ['c.is_active', 1]]);
                })
                ->leftjoin('pos_collections as col', function ($currentDue) {
                    $currentDue->on('col.sales_bill_no', '=', 'sm.sales_bill_no')
                        ->where([['col.is_delete', 0], ['col.is_active', 1]]);
                })
            // ->addSelect([
            //     'ttl_clln_amt' => DB::table('pos_collections as clln')
            //         ->select(DB::raw('SUM(clln.collection_amount)'))
            //         ->whereColumn('clln.sales_bill_no', 'sm.sales_bill_no')
            //         ->where('clln.is_delete', 0)
            //         ->limit(1),
            // ])
                ->where(function ($currentDue) use ($endDate) {
                    if (!empty($endDate)) {
                        $currentDue->where('sm.sales_date', '<', $endDate);
                    }
                })
                ->where(function ($currentDue) use ($branchID) {
                    if (!empty($branchID)) {
                        $currentDue->where('sm.branch_id', $branchID);
                    }
                })
                ->groupBy('sm.sales_bill_no')
                ->orderBy('sm.sales_date', 'ASC')
                ->get();

            $sl = 0;
            $ttl_sales_amount = 0;
            $ttl_paid_amt = 0;
            $ttl_current_due = 0;
            $ttl_total_balance = 0;
            $ttl_inst_amount = 0;

            $DataSet = array();
            $scheduleDate = array();

            $paid = '<span class="text-primary">Paid</span>';
            $due = '<span class="text-danger">Due</span>';
            $Status = '';

            foreach ($currentDue as $row) {

                $scheduleDate = POSS::installmentSchedule($companyID, $branchID, null, $row->sales_date, $row->installment_type, $row->installment_month);

                // Check today installment schedule or not
                if ($endDate < end($scheduleDate)) {

                    $i = 0;
                    foreach ($scheduleDate as $value) {
                        if ($value < $endDate) {
                            $i++;
                        } else {
                            break;
                        }
                    }

                    //first_instalment
                    $last_instalment = ($row->sales_amount - $row->first_instalment - $row->installment_amount * ($row->ttl_installment - 2));

                    $i = $i - 1;

                    $noInstPaid = floor(($row->ttl_clln_amt - $row->first_instalment) / $row->installment_amount);
                    $noInstDue = $i - $noInstPaid;

                    $payableAmt = $row->first_instalment + ($row->installment_amount * $i);

                    $current_due = $row->ttl_clln_amt - $payableAmt;

                    if ($current_due >= 0) {

                        $current_due = 0;
                        $Status = $paid;
                    } else {
                        $current_due = abs($current_due);
                        $Status = $due;
                    }

                    // if ($row->ttl_clln_amt >= $payableAmt) {
                    //     $Status = $paid;
                    // } else {
                    //     $Status = $due;
                    // }

                    $TempSet = array();

                    $TempSet = [
                        'sl' => ++$sl,
                        // 'customer_no' => $row->customer_no,
                        'customer_name' => $row->customer_name,
                        'sales_bill_no' => $row->sales_bill_no,
                        'sales_date' => (new DateTime($row->sales_date))->format('d-m-Y'),
                        'sales_amount' => $row->sales_amount,
                        'installment' => $row->ttl_installment,
                        'first_installment' => round($row->first_instalment, 2),
                        'installment_amount' => round($row->installment_amount, 2),
                        'last_installment' => round($last_instalment, 2),
                        'payable_amount' => round($payableAmt, 2),
                        'paid_amount' => round($row->ttl_clln_amt, 2),
                        // 'current_due' => round((($current_due) > 0) ? 0 : ($current_due), 2),
                        'current_due' => round($current_due, 2),
                        'total_balance' => (($row->sales_amount - $row->ttl_clln_amt) < 0 ? 0 : round($row->sales_amount - $row->ttl_clln_amt, 2)),
                        // 'status' => $Status,
                    ];

                    $DataSet[] = $TempSet;

                    $ttl_sales_amount += $TempSet['sales_amount'];
                    $ttl_inst_amount += $TempSet['installment_amount'];
                    $ttl_paid_amt += $TempSet['paid_amount'];
                    $ttl_current_due += $TempSet['current_due'];
                    $ttl_total_balance += $TempSet['total_balance'];
                }
            }

            $total_row = count($DataSet);

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($total_row),
                "data" => $DataSet,
                "totalRow" => $total_row,
                "ttl_sales_amount" => number_format($ttl_sales_amount, 2),
                "ttl_inst_amount" => number_format($ttl_inst_amount, 2),
                "ttl_paid_amt" => number_format($ttl_paid_amt, 2),
                "ttl_current_due" => number_format($ttl_current_due, 2),
                "ttl_total_balance" => number_format($ttl_total_balance, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.due_report_current');
        }
    }

    public function old_getOverDue(Request $request)
    {
        if ($request->ajax()) {

            $companyID = Common::getCompanyId();
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $branchID = (empty($request->input('branchId'))) ? null : $request->input('branchId');

            $endDate = (new DateTime($endDate))->format('Y-m-d');

            $overDue = DB::table('pos_sales_m as sm')
                ->where([['sm.is_delete', 0], ['sm.is_active', 1], ['sm.sales_type', 2]])
                ->whereIn('sm.branch_id', HRS::getUserAccesableBranchIds())
                ->select('sm.branch_id', 'c.customer_name', 'c.customer_no', 'sm.sales_bill_no',
                    'sm.sales_date', 'sm.total_amount as sales_amount', 'sm.installment_amount',
                    'sm.installment_type', 'sm.installment_month',
                    DB::raw('
                    (CASE
                        WHEN sm.installment_type = 1 THEN sm.installment_month
                        ELSE (FLOOR(DATEDIFF(DATE(DATE_FORMAT(DATE_ADD(sm.sales_date, INTERVAL +sm.installment_month MONTH), "%Y%m%d")), DATE(DATE_FORMAT(sm.sales_date, "%Y%m%d")))/7))
                    END) as ttl_installment,

                    SUM(col.collection_amount) as ttl_clln_amt
                    '),
                    DB::raw('CONCAT(c.customer_name, " (", c.customer_no, ")") AS customer_name')
                )
                ->leftjoin('pos_customers as c', function ($overDue) {
                    $overDue->on('c.customer_no', '=', 'sm.customer_id')
                        ->where([['c.is_delete', 0], ['c.is_active', 1]]);
                })
                ->leftjoin('pos_collections as col', function ($currentDue) {
                    $currentDue->on('col.sales_bill_no', '=', 'sm.sales_bill_no')
                        ->where([['col.is_delete', 0], ['col.is_active', 1]]);
                })
            // ->addSelect([
            //     'ttl_clln_amt' => DB::table('pos_collections as clln')
            //         ->select(DB::raw('SUM(clln.collection_amount)'))
            //         ->whereColumn('clln.sales_bill_no', 'sm.sales_bill_no')
            //         ->where([['clln.is_delete', 0], ['clln.is_active', 1]])
            //         ->limit(1)
            // ])
                ->where(function ($overDue) use ($endDate) {
                    if (!empty($endDate)) {
                        $overDue->where('sm.sales_date', '<', $endDate);
                    }
                })
                ->where(function ($overDue) use ($branchID) {
                    if (!empty($branchID)) {
                        $overDue->where('sm.branch_id', $branchID);
                    }

                })
                ->groupBy('sm.sales_bill_no')
                ->orderBy('sm.sales_date', 'ASC')
                ->get();

            $sl = 0;
            $ttl_sales_amount = 0;
            $ttl_inst_amount = 0;
            $ttl_paid_amount = 0;
            $ttl_over_due = 0;
            $ttl_total_balance = 0;

            $DataSet = array();
            $scheduleDate = array();

            $paid = '<span class="text-primary">Paid</span>';
            $due = '<span class="text-danger">Due</span>';
            $Status = '';

            foreach ($overDue as $row) {

                $scheduleDate = POSS::installmentSchedule($companyID, $branchID, null, $row->sales_date, $row->installment_type, $row->installment_month);

                // Check today installment schedule or not
                if ($endDate > end($scheduleDate)) {

                    if ($row->ttl_clln_amt >= $row->sales_amount) {
                        $Status = $paid;
                    } else {
                        $Status = $due;
                    }

                    $TempSet = array();

                    $TempSet = [
                        'sl' => ++$sl,
                        // 'customer_no' => $row->customer_no,
                        'customer_name' => $row->customer_name,
                        'sales_bill_no' => $row->sales_bill_no,
                        'sales_date' => (new DateTime($row->sales_date))->format('d-m-Y'),
                        'sales_amount' => $row->sales_amount,
                        'installment' => $row->ttl_installment,
                        'installment_amount' => round($row->installment_amount, 2),
                        'paid_amount' => round($row->ttl_clln_amt, 2),
                        'over_due' => (($row->sales_amount - $row->ttl_clln_amt) < 0 ? 0 : round($row->sales_amount - $row->ttl_clln_amt, 2)),
                        'total_balance' => (($row->sales_amount - $row->ttl_clln_amt) < 0 ? 0 : round($row->sales_amount - $row->ttl_clln_amt, 2)),
                        'status' => $Status,
                    ];

                    $DataSet[] = $TempSet;

                    $ttl_sales_amount += $TempSet['sales_amount'];
                    $ttl_inst_amount += $TempSet['installment_amount'];
                    $ttl_paid_amount += $TempSet['paid_amount'];
                    $ttl_over_due += $TempSet['over_due'];
                    $ttl_total_balance += $TempSet['total_balance'];
                }
            }

            $total_row = count($DataSet);

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($total_row),
                "data" => $DataSet,
                "totalRow" => $total_row,
                "ttl_sales_amount" => number_format($ttl_sales_amount, 2),
                "ttl_inst_amount" => number_format($ttl_inst_amount, 2),
                "ttl_paid_amount" => number_format($ttl_paid_amount, 2),
                "ttl_over_due" => number_format($ttl_over_due, 2),
                "ttl_total_balance" => number_format($ttl_total_balance, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.due_report_over');
        }
    }

    public function old_getCurrentnOverDue(Request $request)
    {
        if ($request->ajax()) {

            $companyID = Common::getCompanyId();
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $zoneId = (empty($request->input('zoneId'))) ? null : $request->input('zoneId');
            $areaId = (empty($request->input('areaId'))) ? null : $request->input('areaId');
            $branchID = (empty($request->input('branchId'))) ? null : $request->input('branchId');

            $endDate = new DateTime($endDate);
            $endDate = $endDate->format('Y-m-d');

            $currentnOverDue = DB::table('pos_sales_m as sm')
                ->where([['sm.is_delete', 0], ['sm.sales_type', 2]])
                ->whereIn('sm.branch_id', HRS::getUserAccesableBranchIds())
                ->select('sm.branch_id', 'c.customer_name', 'c.customer_no', 'sm.sales_bill_no',
                    'sm.sales_date', 'sm.total_amount as sales_amount', 'sm.installment_amount',
                    'sm.installment_type', 'sm.installment_month',
                    DB::raw('
                    (CASE
                        WHEN sm.installment_type = 1 THEN sm.installment_month
                        ELSE (FLOOR(DATEDIFF(DATE(DATE_FORMAT(DATE_ADD(sm.sales_date, INTERVAL +sm.installment_month MONTH), "%Y%m%d")), DATE(DATE_FORMAT(sm.sales_date, "%Y%m%d")))/7))
                    END) as ttl_installment,

                    SUM(col.collection_amount) as ttl_clln_amt
                    '),
                    DB::raw('CONCAT(c.customer_name, " (", c.customer_no, ")") AS customer_name')
                )
                ->leftjoin('pos_customers as c', function ($currentnOverDue) {
                    $currentnOverDue->on('c.customer_no', '=', 'sm.customer_id')
                        ->where([['c.is_delete', 0], ['c.is_active', 1]]);
                })
                ->leftjoin('gnl_map_area_branch as mab', function ($currentnOverDue) {
                    $currentnOverDue->on('mab.branch_id', '=', 'sm.branch_id');
                })
                ->leftjoin('gnl_areas as area', function ($currentnOverDue) {
                    $currentnOverDue->on('area.id', '=', 'mab.area_id');
                })
                ->leftjoin('gnl_map_zone_area as mza', function ($currentnOverDue) {
                    $currentnOverDue->on('mza.area_id', '=', 'area.id');
                })
                ->leftjoin('gnl_zones as zone', function ($currentnOverDue) {
                    $currentnOverDue->on('zone.id', '=', 'mza.zone_id');
                })
                ->leftjoin('pos_collections as col', function ($currentnOverDue) {
                    $currentnOverDue->on('col.sales_bill_no', '=', 'sm.sales_bill_no')
                        ->where([['col.is_delete', 0], ['col.is_active', 1]]);
                })
            // ->addSelect([
            //     'ttl_clln_amt' => DB::table('pos_collections as clln')
            //         ->select(DB::raw('SUM(clln.collection_amount)'))
            //         ->whereColumn('clln.sales_bill_no', 'sm.sales_bill_no')
            //         ->where('clln.is_delete', 0)
            //         ->limit(1)
            // ])
                ->where(function ($currentnOverDue) use ($endDate) {
                    if (!empty($endDate)) {
                        $currentnOverDue->where('sm.sales_date', '<', $endDate);
                    }
                })
                ->where(function ($currentnOverDue) use ($zoneId) {
                    if (!empty($zoneId)) {
                        $currentnOverDue->where('zone.id', $zoneId);
                    }
                })
                ->where(function ($currentnOverDue) use ($areaId) {
                    if (!empty($areaId)) {
                        $currentnOverDue->where('area.id', $areaId);
                    }
                })
                ->where(function ($currentnOverDue) use ($branchID) {
                    if (!empty($branchID)) {
                        $currentnOverDue->where('sm.branch_id', $branchID);
                    }
                })
                ->groupBy('sm.sales_bill_no')
                ->orderBy('sm.sales_date', 'ASC')
                ->get();

            $sl = 0;
            $ttl_sales_amount = 0;
            $ttl_inst_amount = 0;
            $ttl_paid_amount = 0;
            $ttl_current_due = 0;
            $ttl_over_due = 0;
            $ttl_due = 0;
            $ttl_total_balance = 0;

            $DataSet = array();
            $scheduleDate = array();

            $paid = '<span class="text-primary">Paid</span>';
            $due = '<span class="text-danger">Due</span>';
            $Status = '';

            foreach ($currentnOverDue as $row) {

                $scheduleDate = POSS::installmentSchedule($companyID, $branchID, null, $row->sales_date, $row->installment_type, $row->installment_month);

                //CURRENT DUE
                if ($endDate < end($scheduleDate)) {

                    $i = 0;
                    foreach ($scheduleDate as $value) {
                        if ($value < $endDate) {
                            $i++;
                        } else {
                            break;
                        }
                    }

                    $noInstPaid = floor($row->ttl_clln_amt / $row->installment_amount);
                    $noInstDue = $i - $noInstPaid;
                    $payableAmt = $row->installment_amount * $noInstDue;

                    if ($row->ttl_clln_amt >= $payableAmt) {
                        $Status = $paid;
                    } else {
                        $Status = $due;
                    }

                    $TempSet = array();

                    $TempSet = [
                        'sl' => ++$sl,
                        'customer_no' => $row->customer_no,
                        'customer_name' => $row->customer_name,
                        'sales_bill_no' => $row->sales_bill_no,
                        'sales_date' => (new DateTime($row->sales_date))->format('d-m-Y'),
                        'sales_amount' => $row->sales_amount,
                        'installment' => $row->ttl_installment,
                        'installment_amount' => round($row->installment_amount, 2),
                        'paid_amount' => round($row->ttl_clln_amt, 2),
                        'current_due' => round((($payableAmt) < 0) ? 0 : ($payableAmt), 2),
                        'over_due' => 0,
                        'total_due' => round((($payableAmt) < 0) ? 0 : ($payableAmt), 2),
                        'total_balance' => (($row->sales_amount - $row->ttl_clln_amt) < 0 ? 0 : round($row->sales_amount - $row->ttl_clln_amt, 2)),
                        'status' => $Status,
                    ];

                    $DataSet[] = $TempSet;

                    $ttl_sales_amount += $TempSet['sales_amount'];
                    $ttl_inst_amount += $TempSet['installment_amount'];
                    $ttl_paid_amount += $TempSet['paid_amount'];
                    $ttl_current_due += $TempSet['current_due'];
                    $ttl_over_due += $TempSet['over_due'];
                    $ttl_due += $TempSet['total_due'];
                    $ttl_total_balance += $TempSet['total_balance'];
                }

                // over due
                if ($endDate > end($scheduleDate)) {

                    if ($row->ttl_clln_amt >= $row->sales_amount) {
                        $Status = $paid;
                    } else {
                        $Status = $due;
                    }

                    $TempSet = array();

                    $TempSet = [
                        'sl' => ++$sl,
                        'customer_no' => $row->customer_no,
                        'customer_name' => $row->customer_name,
                        'sales_bill_no' => $row->sales_bill_no,
                        'sales_date' => (new DateTime($row->sales_date))->format('d-m-Y'),
                        'sales_amount' => $row->sales_amount,
                        'installment' => $row->ttl_installment,
                        'installment_amount' => round($row->installment_amount, 2),
                        'paid_amount' => round($row->ttl_clln_amt, 2),
                        'current_due' => 0,
                        'over_due' => (($row->sales_amount - $row->ttl_clln_amt) < 0 ? 0 : round($row->sales_amount - $row->ttl_clln_amt, 2)),
                        'total_due' => (($row->sales_amount - $row->ttl_clln_amt) < 0 ? 0 : round($row->sales_amount - $row->ttl_clln_amt, 2)),
                        'total_balance' => (($row->sales_amount - $row->ttl_clln_amt) < 0 ? 0 : round($row->sales_amount - $row->ttl_clln_amt, 2)),
                        'status' => $Status,
                    ];

                    $DataSet[] = $TempSet;

                    $ttl_sales_amount += $TempSet['sales_amount'];
                    $ttl_inst_amount += $TempSet['installment_amount'];
                    $ttl_paid_amount += $TempSet['paid_amount'];
                    $ttl_current_due += $TempSet['current_due'];
                    $ttl_over_due += $TempSet['over_due'];
                    $ttl_due += $TempSet['total_due'];
                    $ttl_total_balance += $TempSet['total_balance'];
                }
            }

            $total_row = count($DataSet);

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($total_row),
                "data" => $DataSet,
                "totalRow" => $total_row,
                "ttl_sales_amount" => number_format($ttl_sales_amount, 2),
                "ttl_inst_amount" => number_format($ttl_inst_amount, 2),
                "ttl_paid_amount" => number_format($ttl_paid_amount, 2),
                "ttl_current_due" => number_format($ttl_current_due, 2),
                "ttl_over_due" => number_format($ttl_over_due, 2),
                "ttl_due" => number_format($ttl_due, 2),
                "ttl_total_balance" => number_format($ttl_total_balance, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.due_report_current_over');
        }
    }
/* ------------------------ Due Report End */

/* ------------------------- Sales report Start */
    public function getsale(Request $request)
    {
        if ($request->ajax()) {

            $sl = 1;

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $customerId = (empty($request->input('customerId'))) ? null : $request->input('customerId');
            $employeeId = (empty($request->input('employeeId'))) ? null : $request->input('employeeId');
            $branchId = (empty($request->input('branchId'))) ? null : $request->input('branchId');
            $sales_type = (empty($request->input('sales_type'))) ? null : $request->input('sales_type');
            $zoneId = (empty($request->input('zoneId'))) ? null : $request->input('zoneId');
            $areaId = (empty($request->input('area_Id'))) ? null : $request->input('area_Id');

            $branch_list = null;
            if (!empty($zoneId)) {
                $AreainZone = MapZoneArea::select('area_id')->where('zone_id', $zoneId)->get();
                $AreainZone = $AreainZone->pluck('area_id');
                $BranchinArea = MapAreaBranch::select('branch_id')->whereIn('area_id', $AreainZone->toArray())->get();
                $BranchinArea = $BranchinArea->pluck('branch_id');
                $branch_list = $BranchinArea->toArray();

                // dd($branch_list);
            }
            if (!empty($areaId)) {
                $BranchinArea = MapAreaBranch::select('branch_id')->where('area_id', $areaId)->get();
                $BranchinArea = $BranchinArea->pluck('branch_id');
                $branch_list = $BranchinArea->toArray();
                // dd($branch_list);
            }

            // dd($customerId,$employeeId ,$branchId,$sales_type,$zoneId,$areaId);
            $groupId = (empty($request->input('groupId'))) ? null : $request->input('groupId');
            $catId = (empty($request->input('catId'))) ? null : $request->input('catId');
            $subCatId = (empty($request->input('subCatId'))) ? null : $request->input('subCatId');
            $brandId = (empty($request->input('brandId'))) ? null : $request->input('brandId');
            // dd($groupId,$catId ,$branchId,$subCatId,$brandId);
            $instSalesData = DB::table('pos_sales_m as psm')
                ->where([['psm.is_delete', 0]])
                ->whereIn('psm.branch_id', HRS::getUserAccesableBranchIds())
                ->select('psm.id', 'psm.sales_bill_no', 'psm.sales_date', 'psm.total_quantity as product_qty', 'psm.cash_price', 'psm.installment_profit',
                    'psm.service_charge as processing_fee',
                    DB::raw('CONCAT(cust.customer_name, " (", cust.customer_no, ")") as customer_name_txt,
                    CONCAT(emp.emp_name, " (", emp.emp_code, ")") as sales_by,
                    (CASE
                        WHEN psm.sales_type = 1 THEN "Cash"
                        WHEN psm.sales_type = 2 THEN "Installment"
                        ELSE "Cash"
                    END) as sales_type_txt,
                    (psm.cash_price + psm.installment_profit + psm.service_charge) as total_sales_amt,
                    (psm.paid_amount - psm.service_charge - psm.vat_amount) as first_installment'))

                ->leftjoin('pos_sales_d as psd', function ($instSalesData) {
                    $instSalesData->on('psd.sales_bill_no', '=', 'psm.sales_bill_no');
                })

                ->leftjoin('pos_products as prod', function ($instSalesData) {
                    $instSalesData->on('psd.product_id', '=', 'prod.id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->leftjoin('pos_customers as cust', function ($instSalesData) {
                    $instSalesData->on('psm.customer_id', '=', 'cust.customer_no')
                        ->where([['cust.is_delete', 0], ['cust.is_active', 1]]);
                })
                ->leftjoin('hr_employees as emp', function ($instSalesData) {
                    $instSalesData->on('psm.employee_id', '=', 'emp.employee_no')
                        ->where([['emp.is_delete', 0]]);
                })
                ->where(function ($instSalesData) use ($startDate, $endDate) {
                    if (!empty($startDate) && !empty($endDate)) {

                        $startDate = (new DateTime($startDate))->format('Y-m-d');
                        $endDate = (new DateTime($endDate))->format('Y-m-d');

                        $instSalesData->whereBetween('psm.sales_date', [$startDate, $endDate]);
                    }

                })
                ->where(function ($instSalesData) use ($customerId) {
                    if (!empty($customerId)) {
                        $instSalesData->where('psm.customer_id', $customerId);
                    }
                })
                ->where(function ($instSalesData) use ($employeeId) {
                    if (!empty($employeeId)) {
                        $instSalesData->where('psm.employee_id', $employeeId);
                    }
                })
                ->where(function ($instSalesData) use ($branchId) {
                    if (!empty($branchId)) {
                        $instSalesData->where('psm.branch_id', $branchId);
                    }
                })
                ->where(function ($instSalesData) use ($branch_list) {
                    if (!empty($branch_list)) {
                        $instSalesData->whereIn('psm.branch_id', $branch_list);
                    }
                })
                ->where(function ($instSalesData) use ($sales_type) {
                    if (!empty($sales_type)) {
                        $instSalesData->where('psm.sales_type', $sales_type);
                    }
                })
                ->where(function ($instSalesData) use ($groupId) {
                    if (!empty($groupId)) {
                        $instSalesData->where('prod.prod_group_id', $groupId);
                    }
                })
                ->where(function ($instSalesData) use ($catId) {
                    if (!empty($catId)) {
                        $instSalesData->where('prod.prod_cat_id', $catId);
                    }
                })
                ->where(function ($instSalesData) use ($subCatId) {
                    if (!empty($subCatId)) {
                        $instSalesData->where('prod.prod_sub_cat_id', $subCatId);
                    }
                })
                ->where(function ($instSalesData) use ($brandId) {
                    if (!empty($brandId)) {
                        $instSalesData->where('prod.prod_brand_id', $brandId);
                    }
                })

            // ->where(function ($instSalesData) use ($zoneId) {
            //     if (!empty($zoneId)) {
            //         $instSalesData->where('zone.id', $zoneId);
            //     }
            // })
            // ->where(function ($instSalesData) use ($areaId) {
            //     if (!empty($areaId)) {
            //         $instSalesData->where('area.id', $areaId);
            //     }
            // })psm.sales_bill_no
                ->groupBy('psm.sales_bill_no')
                ->orderBy('psm.sales_date', 'ASC')
                ->orderBy('psm.id', 'ASC')
                ->get();

            // dd($instSalesData);

            $total_row = count($instSalesData);
            $ttl_product_qty = $instSalesData->sum('product_qty');
            $ttl_cash_price = $instSalesData->sum('cash_price');
            $ttl_profit = $instSalesData->sum('installment_profit');
            $ttl_processing_fee = $instSalesData->sum('processing_fee');
            $ttl_total_sales_amount = $instSalesData->sum('total_sales_amt');
            $ttl_first_installment = $instSalesData->sum('first_installment');

            if (!empty($startDate) || !empty($endDate) || !empty($customerId) || !empty($employeeId) || !empty($branchId) || !empty($sales_type) || !empty($zoneId) || !empty($area_Id)) {
                $totalFiltered = count($instSalesData);
            }

            $DataSet = array();
            $i = 0;
            foreach ($instSalesData as $row) {
                $TempSet = array();

                $TempSet = [
                    'sl' => $sl++,
                    'customer_name' => $row->customer_name_txt,
                    'sales_type' => $row->sales_type_txt,
                    'sales_bill_no' => $row->sales_bill_no,
                    'sales_date' => (new Datetime($row->sales_date))->format('d-m-Y'),
                    'emp_name' => $row->sales_by,
                    'total_quantity' => $row->product_qty,
                    'cash_price' => $row->cash_price,
                    'profit' => $row->installment_profit,
                    'processing_fee' => $row->processing_fee,
                    'total_sales_amount' => $row->total_sales_amt,
                    'first_installment' => $row->first_installment,
                ];

                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                'ttl_product_qty' => $ttl_product_qty,
                'ttl_cash_price' => number_format($ttl_cash_price, 2),
                'ttl_profit' => number_format($ttl_profit, 2),
                'ttl_processing_fee' => number_format($ttl_processing_fee, 2),
                'ttl_total_sales_amount' => number_format($ttl_total_sales_amount, 2),
                'ttl_first_installment' => number_format($ttl_first_installment, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.sales_report');
        }
    }

    public function getsaleDetails(Request $request)
    {

        $salesDReportData = array();

        if ($request->isMethod('post')) {
            $requestData = $request->all();

            $startDate = (empty($requestData['StartDate'])) ? null : $requestData['StartDate'];
            $endDate = (empty($requestData['EndDate'])) ? null : $requestData['EndDate'];
            $zoneId = (empty($requestData['zone_id'])) ? null : $requestData['zone_id'];
            $areaId = (empty($requestData['area_id'])) ? null : $requestData['area_id'];
            $branchId = (empty($requestData['branch_id'])) ? null : $requestData['branch_id'];
            $salesType = (empty($requestData['sales_type'])) ? null : $requestData['sales_type'];
            $customerId = (empty($requestData['customer_id'])) ? null : $requestData['customer_id'];
            $employeeId = (empty($requestData['employee_id'])) ? null : $requestData['employee_id'];
            $groupId = (empty($requestData['group_id'])) ? null : $requestData['group_id'];
            $catId = (empty($requestData['cat_id'])) ? null : $requestData['cat_id'];
            $subCatId = (empty($requestData['sub_cat_id'])) ? null : $requestData['sub_cat_id'];
            $brandId = (empty($requestData['brand_id'])) ? null : $requestData['brand_id'];

            $salesDReportData = DB::table('pos_sales_m as psm')
                ->where([['psm.is_delete', 0], ['psm.is_active', 1]])
                ->whereIn('psm.branch_id', HRS::getUserAccesableBranchIds())
                ->select('psm.id', 'psm.sales_date', 'psm.sales_bill_no',
                    'psd.product_quantity as product_qtn', 'psd.total_sales_price as total_amount',
                    DB::raw('CONCAT(cust.customer_name,"(", cust.customer_no, ")") as customer_name_txt,
                 CONCAT(emp.emp_name, "(", emp.emp_code, ")" ) as sales_by,
                 CONCAT(prod.product_name, "(", prod.prod_barcode, ")" ) as product_name_txt,
                 (CASE
                     WHEN psm.sales_type = 1 THEN "Cash"
                     WHEN psm.sales_type = 2 THEN "Installment"
                     ELSE "Cash"
                 END) as sales_type_txt'))
                ->leftjoin('pos_sales_d as psd', function ($salesDReportData) {
                    $salesDReportData->on('psd.sales_bill_no', '=', 'psm.sales_bill_no');
                })
                ->leftjoin('pos_customers as cust', function ($salesDReportData) {
                    $salesDReportData->on('psm.customer_id', '=', 'cust.customer_no')
                        ->where([['cust.is_delete', 0], ['cust.is_active', 1]]);
                })
                ->leftjoin('hr_employees as emp', function ($salesDReportData) {
                    $salesDReportData->on('psm.employee_id', '=', 'emp.employee_no')
                        ->where([['emp.is_delete', 0], ['emp.is_active', 1]]);
                })
                ->leftjoin('pos_products as prod', function ($salesDReportData) {
                    $salesDReportData->on('psd.product_id', '=', 'prod.id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->where(function ($salesDReportData) use ($startDate, $endDate) {

                    if (!empty($startDate) && !empty($endDate)) {

                        $startDate = (new DateTime($startDate))->format('Y-m-d');
                        $endDate = (new DateTime($endDate))->format('Y-m-d');

                        $salesDReportData->whereBetween('psm.sales_date', [$startDate, $endDate]);
                    }
                })
                ->where(function ($salesDReportData) use ($branchId) {

                    if (!empty($branchId)) {
                        $salesDReportData->where('psm.branch_id', $branchId);
                    }
                })
                ->where(function ($salesDReportData) use ($salesType) {

                    if (!empty($salesType)) {
                        $salesDReportData->where('psm.sales_type', $salesType);
                    }
                })
                ->where(function ($salesDReportData) use ($customerId) {
                    if (!empty($customerId)) {
                        $salesDReportData->where('psm.customer_id', $customerId);
                    }
                })
                ->where(function ($salesDReportData) use ($employeeId) {
                    if (!empty($employeeId)) {
                        $salesDReportData->where('psm.employee_id', $employeeId);
                    }
                })
                ->where(function ($salesDReportData) use ($groupId) {
                    if (!empty($groupId)) {
                        $salesDReportData->where('prod.prod_group_id', $groupId);
                    }
                })
                ->where(function ($salesDReportData) use ($catId) {
                    if (!empty($catId)) {
                        $salesDReportData->where('prod.prod_cat_id', $catId);
                    }
                })
                ->where(function ($salesDReportData) use ($subCatId) {

                    if (!empty($subCatId)) {
                        $salesDReportData->where('prod.prod_sub_cat_id', $subCatId);
                    }
                })
                ->where(function ($salesDReportData) use ($brandId) {
                    if (!empty($brandId)) {
                        $salesDReportData->where('prod.prod_brand_id', $brandId);
                    }
                })
                ->orderBy('psm.sales_date', 'ASC')
                ->orderBy('psd.id', 'ASC')
                ->get();

            $data = array(
                'QueryData' => (!empty($salesDReportData)) ? $salesDReportData : array(),
                'startDate' => $startDate,
                'endDate' => $endDate,
                'zoneId' => $zoneId,
                'areaId' => $areaId,
                'branchId' => $branchId,
                'salesType' => $salesType,
                'customerId' => $customerId,
                'employeeId' => $employeeId,
                'groupId' => $groupId,
                'catId' => $catId,
                'subCatId' => $subCatId,
                'brandId' => $brandId,
            );

            return view('POS.Report.sales_details', $data);

        } else {

            $data = array(
                'QueryData' => $salesDReportData,
            );
            return view('POS.Report.sales_details', $data);
        }
    }

    public function getTSalesSummary(Request $request)
    {
        if ($request->ajax()) {

            $sl = 1;

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $branchId = (empty($request->input('branchId'))) ? null : $request->input('branchId');
            // $groupId = (empty($request->input('groupId'))) ? null : $request->input('groupId');
            // $catId = (empty($request->input('catId'))) ? null : $request->input('catId');
            // $subCatId = (empty($request->input('subCatId'))) ? null : $request->input('subCatId');
            // $brandId = (empty($request->input('brandId'))) ? null : $request->input('brandId');
            // $modelId = (empty($request->input('modelId'))) ? null : $request->input('modelId');

            // Query
            $tSSummaryRData = DB::table('pos_sales_m as psm')
                ->where([['psm.is_active', 1], ['psm.is_delete', 0]])
                ->whereIn('psm.branch_id', HRS::getUserAccesableBranchIds())

                ->select('psm.branch_id', 'br.branch_code', DB::raw('br.branch_name as branch_name_txt, SUM(psm.total_quantity) as product_qtn,
              SUM(psm.cash_price + psm.installment_profit + psm.service_charge) as total_sales_amt,
              SUM(psm.paid_amount - psm.service_charge - psm.vat_amount) as first_installment'))

            // ->leftjoin('pos_sales_d as psd', function ($tSSummaryRData) {
            //     $tSSummaryRData->on('psd.sales_bill_no', '=', 'psd.sales_bill_no');
            // })
                ->leftjoin('gnl_branchs as br', function ($tSSummaryRData) {
                    $tSSummaryRData->on('psm.branch_id', '=', 'br.id')
                        ->where([['br.is_delete', 0], ['br.is_active', 1], ['br.is_approve', 1]]);
                })
            // ->leftjoin('pos_products as prod', function ($tSSummaryRData) {
            //     $tSSummaryRData->on('psd.product_id', '=', 'prod.id')
            //         ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
            // })
                ->groupBy('psm.branch_id')
                ->where(function ($tSSummaryRData) use ($startDate, $endDate) {

                    if (!empty($startDate) && !empty($endDate)) {

                        $startDate = (new DateTime($startDate))->format('Y-m-d');
                        $endDate = (new DateTime($endDate))->format('Y-m-d');

                        $tSSummaryRData->whereBetween('psm.sales_date', [$startDate, $endDate]);
                    }
                })
                ->where(function ($tSSummaryRData) use ($branchId) {
                    if (!empty($branchId)) {
                        $tSSummaryRData->where('psm.branch_id', $branchId);
                    }
                })
            // ->where(function ($tSSummaryRData) use ($groupId) {
            //     if (!empty($groupId)) {
            //         $tSSummaryRData->where('prod.prod_group_id', $groupId);
            //     }
            // })
            // ->where(function ($tSSummaryRData) use ($catId) {

            //     if (!empty($catId)) {
            //         $tSSummaryRData->where('prod.prod_cat_id', $catId);
            //     }
            // })
            // ->where(function ($tSSummaryRData) use ($subCatId) {
            //     if (!empty($subCatId)) {
            //         $tSSummaryRData->where('prod.prod_sub_cat_id', $subCatId);
            //     }
            // })
            // ->where(function ($tSSummaryRData) use ($brandId) {
            //     if (!empty($brandId)) {
            //         $tSSummaryRData->where('prod.prod_brand_id', $brandId);
            //     }
            // })
            // ->where(function ($tSSummaryRData) use ($modelId) {
            //     if (!empty($modelId)) {
            //         $tSSummaryRData->where('prod.prod_model_id', $modelId);
            //     }
            // })
                ->get();

            $total_quantity = $tSSummaryRData->sum('product_qtn');
            $total_sales_amount = $tSSummaryRData->sum('total_sales_amt');
            $total_first_installment = $tSSummaryRData->sum('first_installment');

            // dd($tSSummaryRData);

            $DataSet = array();

            foreach ($tSSummaryRData as $row) {

                $tempSet = array();

                $tempSet = [
                    'sl' => $sl++,
                    'branch_name' => (!empty($row->branch_name_txt)) ? $row->branch_name_txt . "(" . $row->branch_code . ")" : "",
                    'product_qtn' => $row->product_qtn,
                    'total_sales_amt' => $row->total_sales_amt,
                    'first_installment' => $row->first_installment,
                ];

                $DataSet[] = $tempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                "total_quantity" => $total_quantity,
                "total_sales_amount" => number_format($total_sales_amount, 2),
                "total_first_installment" => number_format($total_first_installment, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.sales_summary_total');
        }
    }

    public function getAreaWiseSales(Request $request)
    {
        if ($request->isMethod('post')) {

            $reqData = $request->all();

            $startDate = (empty($request->input('StartDate'))) ? null : $request->input('StartDate');
            $endDate = (empty($request->input('EndDate'))) ? null : $request->input('EndDate');
            $areaId = (empty($request->input('area_id'))) ? null : $request->input('area_id');
            $groupId = (empty($request->input('group_id'))) ? null : $request->input('group_id');
            $catId = (empty($request->input('cat_id'))) ? null : $request->input('cat_id');
            $subCatId = (empty($request->input('sub_cat_id'))) ? null : $request->input('sub_cat_id');
            $brandId = (empty($request->input('brand_id'))) ? null : $request->input('brand_id');
            $modelId = (empty($request->input('model_id'))) ? null : $request->input('model_id');

            if (empty($areaId)) {
                $reqData['total'] = "SUB TOTAL";
            } else {
                $reqData['total'] = "TOTAL";
            }

            // IFNULL(COUNT(CASE WHEN psm.sales_type = 1 THEN psm.customer_id END), 0) as ttl_cash_cust,
            // IFNULL(COUNT(CASE WHEN psm.sales_type = 2 THEN psm.customer_id END), 0) as ttl_credit_cust,
            // COUNT(psm.customer_id) as ttl_cust,

            $areaWiseSalesReport = DB::table('pos_sales_m as psm')
                ->where([['psm.is_delete', 0], ['psm.is_active', 1]])
                ->whereIn('psm.branch_id', HRS::getUserAccesableBranchIds())
                ->select('psm.branch_id', 'b.branch_name', 'b.branch_code', 'area.area_code', 'area.area_name',
                    DB::raw('
                        IFNULL(SUM(CASE WHEN psm.sales_type = 1 THEN psm.total_quantity END), 0) as ttl_cash_qtn,
                        IFNULL(SUM(CASE WHEN psm.sales_type = 1 THEN psm.total_amount END), 0) as ttl_cash_amt,

                        IFNULL(SUM(CASE WHEN psm.sales_type = 2 THEN psm.total_quantity END), 0) as ttl_credit_qtn,
                        IFNULL(SUM(CASE WHEN psm.sales_type = 2 THEN psm.total_amount END), 0) as ttl_credit_amt,

                        SUM(psm.total_quantity) as ttl_qtn,
                        SUM(psm.total_amount) as ttl_amt,

                        IFNULL(CASE WHEN cust.customer_type = 1 THEN COUNT(cust.id)  END , 0) as ttl_cash_cust,
                        IFNULL(CASE WHEN cust.customer_type = 2 THEN COUNT(cust.id)  END , 0) as ttl_credit_cust
                        '))
                ->leftjoin('pos_customers as cust', function ($areaWiseSalesReport) {
                    $areaWiseSalesReport->on('cust.customer_no', 'psm.customer_id')
                        ->where([['cust.is_delete', 0], ['cust.is_active', 1]]);
                })

            // ->leftjoin('pos_sales_d as psd', function ($areaWiseSalesReport) {
            //     $areaWiseSalesReport->on('psd.sales_bill_no', 'psm.sales_bill_no');
            // })
            // ->leftjoin('pos_products as prod', function ($areaWiseSalesReport) {
            //     $areaWiseSalesReport->on('prod.id', 'psd.product_id')
            //         ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
            // })

                ->leftjoin('gnl_branchs as b', function ($areaWiseSalesReport) {
                    $areaWiseSalesReport->on('b.id', 'psm.branch_id')
                        ->where([['b.is_delete', 0], ['b.is_approve', 1]]);
                })
                ->leftjoin('gnl_map_area_branch as mab', function ($areaWiseSalesReport) {
                    $areaWiseSalesReport->on('mab.branch_id', 'psm.branch_id');
                })
                ->leftjoin('gnl_areas as area', function ($areaWiseSalesReport) {
                    $areaWiseSalesReport->on('area.id', 'mab.area_id')
                        ->where('area.is_delete', 0);
                })
                ->where(function ($areaWiseSalesReport) use ($startDate, $endDate) {
                    if (!empty($startDate) && !empty($endDate)) {

                        $startDate = (new DateTime($startDate))->format('Y-m-d');
                        $endDate = (new DateTime($endDate))->format('Y-m-d');

                        $areaWiseSalesReport->whereBetween('psm.sales_date', [$startDate, $endDate]);
                    }
                })
                ->where(function ($areaWiseSalesReport) use ($areaId) {
                    if (!empty($areaId)) {
                        $areaWiseSalesReport->where('area.id', $areaId);
                    }
                })
            // ->where(function ($areaWiseSalesReport) use ($groupId) {
            //     if (!empty($groupId)) {
            //         $areaWiseSalesReport->where('prod.prod_group_id', $groupId);
            //     }
            // })
            // ->where(function ($areaWiseSalesReport) use ($catId) {
            //     if (!empty($catId)) {
            //         $areaWiseSalesReport->where('prod.prod_cat_id', $catId);
            //     }
            // })
            // ->where(function ($areaWiseSalesReport) use ($subCatId) {
            //     if (!empty($subCatId)) {
            //         $areaWiseSalesReport->where('prod.prod_sub_cat_id', $subCatId);
            //     }
            // })
            // ->where(function ($areaWiseSalesReport) use ($brandId) {
            //     if (!empty($brandId)) {
            //         $areaWiseSalesReport->where('prod.prod_brand_id', $brandId);
            //     }
            // })
            // ->where(function ($areaWiseSalesReport) use ($modelId) {
            //     if (!empty($modelId)) {
            //         $areaWiseSalesReport->where('prod.prod_model_id', $modelId);
            //     }
            // })
                ->groupBy('psm.branch_id')
                ->orderBy('psm.sales_date', 'ASC')
                ->orderBy('psm.id', 'ASC')
                ->get();

            $sl = 1;
            $total_row = count($areaWiseSalesReport);
            $DataSet = array();

            foreach ($areaWiseSalesReport as $row) {
                $tempSet = array();

                $tempSet = [
                    'sl' => $sl++,
                    'branch_name' => $row->branch_code . '-' . $row->branch_name,
                    'ttl_cash_cust' => $row->ttl_cash_cust,
                    'ttl_credit_cust' => $row->ttl_credit_cust,
                    'ttl_cash_qtn' => $row->ttl_cash_qtn,
                    'ttl_credit_qtn' => $row->ttl_credit_qtn,
                    'ttl_cash_amt' => $row->ttl_cash_amt,
                    'ttl_credit_amt' => $row->ttl_credit_amt,
                    'ttl_cust' => $row->ttl_cash_cust + $row->ttl_credit_cust,
                    'ttl_qtn' => $row->ttl_qtn,
                    'ttl_amt' => $row->ttl_amt,
                ];

                $DataSet[sprintf("%04d", $row->area_code) . '-' . $row->area_name][] = $tempSet;
            }

            return view('POS.Report.sales_report_area_wise', compact('DataSet', 'reqData', 'startDate', 'endDate', 'areaId'));

        } else {
            return view('POS.Report.sales_report_area_wise');
        }
    }

    public function getZoneSales(Request $request)
    {
        if ($request->isMethod('post')) {

            $reqData = $request->all();

            $startDate = (empty($request->input('StartDate'))) ? null : $request->input('StartDate');
            $endDate = (empty($request->input('EndDate'))) ? null : $request->input('EndDate');
            $zoneId = (empty($request->input('zone_id'))) ? null : $request->input('zone_id');
            $groupId = (empty($request->input('group_id'))) ? null : $request->input('group_id');
            $catId = (empty($request->input('cat_id'))) ? null : $request->input('cat_id');
            $subCatId = (empty($request->input('sub_cat_id'))) ? null : $request->input('sub_cat_id');
            $brandId = (empty($request->input('brand_id'))) ? null : $request->input('brand_id');
            $modelId = (empty($request->input('model_id'))) ? null : $request->input('model_id');

            if (empty($areaId)) {
                $reqData['total'] = "SUB TOTAL";
            } else {
                $reqData['total'] = "TOTAL";
            }

            // IFNULL(COUNT(CASE WHEN psm.sales_type = 1 THEN psm.customer_id END), 0) as ttl_cash_cust,
            // IFNULL(COUNT(CASE WHEN psm.sales_type = 2 THEN psm.customer_id END), 0) as ttl_credit_cust,
            // COUNT(psm.customer_id) as ttl_cust,

            $zoneWiseSalesReport = DB::table('pos_sales_m as psm')
                ->where([['psm.is_delete', 0], ['psm.is_active', 1], ['psm.branch_id', '<>', 1]])
                ->whereIn('psm.branch_id', HRS::getUserAccesableBranchIds())
                ->select('psm.branch_id', 'b.branch_name', 'b.branch_code', 'zone.zone_name', 'zone.zone_code',
                    DB::raw('
                        IFNULL(SUM(CASE WHEN psm.sales_type = 1 THEN psm.total_quantity END), 0) as ttl_cash_qtn,
                        IFNULL(SUM(CASE WHEN psm.sales_type = 1 THEN psm.total_amount END), 0) as ttl_cash_amt,

                        IFNULL(SUM(CASE WHEN psm.sales_type = 2 THEN psm.total_quantity END), 0) as ttl_credit_qtn,
                        IFNULL(SUM(CASE WHEN psm.sales_type = 2 THEN psm.total_amount END), 0) as ttl_credit_amt,

                        SUM(psm.total_quantity) as ttl_qtn,
                        SUM(psm.total_amount) as ttl_amt,

                        IFNULL(CASE WHEN psm.sales_type = 1 THEN COUNT(cust.id)  END , 0) as ttl_cash_cust,
                        IFNULL(CASE WHEN psm.sales_type = 2 THEN COUNT(cust.id)  END , 0) as ttl_credit_cust
                        '))
                ->leftjoin('pos_customers as cust', function ($areaWiseSalesReport) {
                    $areaWiseSalesReport->on('cust.customer_no', 'psm.customer_id')
                        ->where([['cust.is_delete', 0], ['cust.is_active', 1]]);
                })
            // ->leftjoin('pos_sales_d as psd', function ($zoneWiseSalesReport) {
            //     $zoneWiseSalesReport->on('psd.sales_bill_no', 'psm.sales_bill_no');
            // })
            // ->leftjoin('pos_products as prod', function ($zoneWiseSalesReport) {
            //     $zoneWiseSalesReport->on('prod.id', 'psd.product_id')
            //         ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
            // })
                ->leftjoin('gnl_branchs as b', function ($zoneWiseSalesReport) {
                    $zoneWiseSalesReport->on('b.id', 'psm.branch_id')
                        ->where([['b.is_delete', 0], ['b.is_active', 1], ['b.is_approve', 1]]);
                })
                ->leftjoin('gnl_map_area_branch as mab', function ($zoneWiseSalesReport) {
                    $zoneWiseSalesReport->on('mab.branch_id', 'psm.branch_id');
                })
                ->leftjoin('gnl_areas as area', function ($zoneWiseSalesReport) {
                    $zoneWiseSalesReport->on('area.id', 'mab.area_id')
                        ->where('area.is_delete', 0);
                })
                ->leftjoin('gnl_map_zone_area as mza', function ($zoneWiseSalesReport) {
                    $zoneWiseSalesReport->on('area.id', 'mza.area_id')
                        ->where('area.is_delete', 0);
                })
                ->leftjoin('gnl_zones as zone', function ($zoneWiseSalesReport) {
                    $zoneWiseSalesReport->on('mza.zone_id', 'zone.id')
                        ->where('area.is_delete', 0);
                })
                ->where(function ($zoneWiseSalesReport) use ($startDate, $endDate) {
                    if (!empty($startDate) && !empty($endDate)) {

                        $startDate = (new DateTime($startDate))->format('Y-m-d');
                        $endDate = (new DateTime($endDate))->format('Y-m-d');

                        $zoneWiseSalesReport->whereBetween('psm.sales_date', [$startDate, $endDate]);
                    }
                })
                ->where(function ($zoneWiseSalesReport) use ($zoneId) {
                    if (!empty($zoneId)) {
                        $zoneWiseSalesReport->where('zone.id', $zoneId);
                    }
                })
            // ->where(function ($zoneWiseSalesReport) use ($groupId) {
            //     if (!empty($groupId)) {
            //         $zoneWiseSalesReport->where('prod.prod_group_id', $groupId);
            //     }
            // })
            // ->where(function ($zoneWiseSalesReport) use ($catId) {
            //     if (!empty($catId)) {
            //         $zoneWiseSalesReport->where('prod.prod_cat_id', $catId);
            //     }
            // })
            // ->where(function ($zoneWiseSalesReport) use ($subCatId) {
            //     if (!empty($subCatId)) {
            //         $zoneWiseSalesReport->where('prod.prod_sub_cat_id', $subCatId);
            //     }
            // })
            // ->where(function ($zoneWiseSalesReport) use ($brandId) {
            //     if (!empty($brandId)) {
            //         $zoneWiseSalesReport->where('prod.prod_brand_id', $brandId);
            //     }
            // })
                ->groupBy('psm.branch_id')
                ->orderBy('psm.sales_date', 'ASC')
                ->orderBy('psm.id', 'ASC')
                ->get();

            $sl = 1;
            $total_row = count($zoneWiseSalesReport);
            $DataSet = array();

            foreach ($zoneWiseSalesReport as $row) {
                $tempSet = array();

                $tempSet = [
                    'sl' => $sl++,
                    // 'zone' => $row->zone_name,
                    'branch_name' => $row->branch_code . '-' . $row->branch_name,
                    'ttl_cash_cust' => $row->ttl_cash_cust,
                    'ttl_credit_cust' => $row->ttl_credit_cust,
                    'ttl_cash_qtn' => $row->ttl_cash_qtn,
                    'ttl_credit_qtn' => $row->ttl_credit_qtn,
                    'ttl_cash_amt' => $row->ttl_cash_amt,
                    'ttl_credit_amt' => $row->ttl_credit_amt,
                    'ttl_cust' => $row->ttl_cash_cust + $row->ttl_credit_cust,
                    'ttl_qtn' => $row->ttl_qtn,
                    'ttl_amt' => $row->ttl_amt,
                ];

                $DataSet[sprintf("%04d", $row->zone_code) . '-' . $row->zone_name][] = $tempSet;
            }

            return view('POS.Report.sales_report_zone', compact('DataSet', 'reqData', 'startDate', 'endDate', 'zoneId'));

        } else {
            return view('POS.Report.sales_report_zone');
        }
    }

    public function getSalesReturn(Request $request)
    {

        if ($request->ajax()) {

            $sl = 1;

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $productId = (empty($request->input('productId'))) ? null : $request->input('productId');
            $zoneId = (empty($request->input('zoneId'))) ? null : $request->input('zoneId');
            $areaId = (empty($request->input('areaId'))) ? null : $request->input('areaId');
            $branchId = (empty($request->input('branchId'))) ? null : $request->input('branchId');
            // $customerId = (empty($request->input('customerId'))) ? null : $request->input('customerId');
            // $empId = (empty($request->input('empId'))) ? null : $request->input('empId');
            // $saleTypeId = (empty($request->input('saleTypeId'))) ? null : $request->input('saleTypeId');
            // $modelId = (empty($request->input('model_id'))) ? null : $request->input('model_id');
            // Query
            $salesReturnData = DB::table('pos_sales_return_m as prm')
                ->where([['prm.is_delete', 0], ['prm.is_active', 1]])
                ->whereIn('prm.branch_id', HRS::getUserAccesableBranchIds())
                ->select('prm.return_date', 'prm.return_bill_no', 'prm.total_return_quantity',
                    'prm.total_return_amount', 'prm.sales_bill_no', 'prm.sales_date',
                    'prod.product_name', 'prod.prod_barcode',
                    DB::raw('CONCAT(gb.branch_name, " (", gb.branch_code, ")") AS branch_name'),
                    DB::raw('CONCAT(prod.product_name, " (", prod.prod_barcode, ")") AS product_name')
                )
                ->leftjoin('pos_sales_return_d as prd', function ($salesReturnData) {
                    $salesReturnData->on('prm.return_bill_no', '=', 'prd.return_bill_no');
                })
                ->leftjoin('pos_products as prod', function ($salesReturnData) {
                    $salesReturnData->on('prod.id', '=', 'prd.product_id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->leftjoin('gnl_branchs as gb', function ($salesReturnData) {
                    $salesReturnData->on('gb.id', '=', 'prm.branch_id')
                        ->where([['gb.is_delete', 0], ['gb.is_active', 1]]);
                })

                ->where(function ($salesReturnData) use ($zoneId) {
                    if (!empty($zoneId)) {
                        $salesReturnData->where('prm.zone_id', $zoneId);
                    }
                })
                ->where(function ($salesReturnData) use ($areaId) {
                    if (!empty($areaId)) {
                        $salesReturnData->where('prm.area_id', $areaId);
                    }
                })
                ->where(function ($salesReturnData) use ($branchId) {
                    if (!empty($branchId)) {
                        $salesReturnData->where('prm.branch_id', $branchId);
                    }
                })
            // ->where(function ($salesReturnData) use ($customerId) {
            //     if (!empty($customerId)) {
            //         $salesReturnData->where('prm.customer_id', $customerId);
            //     }
            // })
            // ->where(function ($salesReturnData) use ($empId) {
            //     if (!empty($empId)) {
            //         $salesReturnData->where('prm.employee_id', $empId);
            //     }
            // })
            // ->where(function ($salesReturnData) use ($saleTypeId) {
            //     if (!empty($saleTypeId)) {
            //         $salesReturnData->where('prm.sales_type', $saleTypeId);
            //     }
            // })
                ->where(function ($salesReturnData) use ($productId) {
                    if (!empty($productId)) {
                        $salesReturnData->where('prd.product_id', $productId);
                    }
                })
                ->where(function ($salesReturnData) use ($startDate, $endDate) {

                    if (!empty($startDate) && !empty($endDate)) {

                        $startDate = (new DateTime($startDate))->format('Y-m-d');
                        $endDate = (new DateTime($endDate))->format('Y-m-d');

                        $salesReturnData->whereBetween('prm.return_date', [$startDate, $endDate]);
                    }
                })
                ->orderBy('prm.return_date', 'ASC')
                ->get();

            $product_quantity = $salesReturnData->sum('total_return_quantity');
            $total_cost_price = $salesReturnData->sum('total_return_amount');

            $DataSet = array();
            $i = 0;

            foreach ($salesReturnData as $row) {
                $TempSet = array();
                $TempSet = [
                    'id' => $sl++,
                    'return_date' => (new DateTime($row->return_date))->format('d-m-Y'),
                    'return_bill_no' => $row->return_bill_no,
                    'sales_bill_no' => $row->sales_bill_no,
                    'branch_name' => $row->branch_name,
                    'product_name' => $row->product_name,
                    'product_quantity' => $row->total_return_quantity,
                    'total_cost_price' => $row->total_return_amount,
                ];

                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                'product_quantity' => $product_quantity,
                'total_cost_price' => number_format($total_cost_price, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.sales_return_report');
        }
    }

    public function getCustomerDetails(Request $request)
    {
        if ($request->ajax()) {

            $sl = 1;

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $customerId = (empty($request->input('customerId'))) ? null : $request->input('customerId');
            $branchId = (empty($request->input('branchId'))) ? null : $request->input('branchId');
            $sales_type = (empty($request->input('sales_type'))) ? null : $request->input('sales_type');
            $productId = (empty($request->input('productId'))) ? null : $request->input('productId');

            $salesDReportData = DB::table('pos_sales_m as psm')
                ->where([['psm.is_delete', 0], ['psm.is_active', 1]])
                ->whereIn('psm.branch_id', HRS::getUserAccesableBranchIds())
                ->select('psm.id', 'psm.sales_date', 'psm.sales_bill_no', 'psm.total_quantity as product_qty',
                    'cust.customer_mobile',
                    DB::raw('CONCAT(cust.customer_name,"(", cust.customer_no, ")") as customer_name_txt,
                        SUM(col.collection_amount) as paid_amount,
                        CONCAT(pro.product_name , " - " , prod_barcode) as product_name,
                 (CASE
                     WHEN psm.sales_type = 1 THEN "Cash"
                     WHEN psm.sales_type = 2 THEN "Installment"
                     ELSE "Cash"
                 END) as sales_type_txt,
                 (psm.cash_price + psm.installment_profit + psm.service_charge) as total_sales_amt'))
                ->leftjoin('pos_customers as cust', function ($salesDReportData) {
                    $salesDReportData->on('psm.customer_id', '=', 'cust.customer_no')
                        ->where([['cust.is_delete', 0], ['cust.is_active', 1]]);
                })
                ->join('pos_collections as col', function ($salesDReportData) {
                    $salesDReportData->on('psm.sales_bill_no', '=', 'col.sales_bill_no')
                        ->where([['col.is_delete', 0], ['col.is_active', 1]]);
                })
                ->join('pos_sales_d as psd', function ($salesDReportData) {
                    $salesDReportData->on('psm.sales_bill_no', '=', 'psd.sales_bill_no');
                })
                ->join('pos_products as pro', function ($detailsData) {
                    $detailsData->on('pro.id', '=', 'psd.product_id')
                        ->where([['pro.is_delete', 0], ['pro.is_active', 1]]);
                })
                ->where(function ($salesDReportData) use ($startDate, $endDate) {

                    if (!empty($startDate) && !empty($endDate)) {

                        $startDate = (new DateTime($startDate))->format('Y-m-d');
                        $endDate = (new DateTime($endDate))->format('Y-m-d');

                        $salesDReportData->whereBetween('psm.sales_date', [$startDate, $endDate]);
                    }
                })
                ->where(function ($salesDReportData) use ($branchId) {

                    if (!empty($branchId)) {
                        $salesDReportData->where('psm.branch_id', $branchId);
                    }
                })
                ->where(function ($salesDReportData) use ($sales_type) {

                    if (!empty($sales_type)) {
                        $salesDReportData->where('psm.sales_type', $sales_type);
                    }
                })
                ->where(function ($salesDReportData) use ($customerId) {
                    if (!empty($customerId)) {
                        $salesDReportData->where('psm.customer_id', $customerId);
                    }
                })
                ->where(function ($salesDReportData) use ($productId) {
                    if (!empty($productId)) {
                        $salesDReportData->where('psd.product_id', $productId);
                    }
                })
                ->orderBy('psm.customer_id', 'ASC')
                ->groupBy('cust.customer_no')
                ->get();

            // dd($salesDReportData);

            // $billNoList = $salesDReportData->pluck('sales_bill_no');
            //     $detailsData = DB::table('pos_sales_d as dt')
            //         ->whereIn('dt.sales_bill_no', $billNoList->toarray())
            //         ->join('pos_products as pro', function ($detailsData) {
            //             $detailsData->on('pro.id', '=', 'dt.product_id')
            //                 ->where([['pro.is_delete', 0],['pro.is_active', 1]]);
            //         })
            //         ->select('dt.sales_bill_no', 'pro.product_name')
            //         ->get();

            $total_row = count($salesDReportData);
            $ttl_product_qty = $salesDReportData->sum('product_qty');
            $ttl_sales_amount = $salesDReportData->sum('total_sales_amt');
            $ttl_paid_amount = $salesDReportData->sum('paid_amount');

            if (!empty($startDate) || !empty($endDate) || !empty($customerId) || !empty($branchId)) {
                $totalFiltered = count($salesDReportData);
            }

            $DataSet = array();
            $i = 0;
            foreach ($salesDReportData as $row) {
                // $product_names = $detailsData->where('sales_bill_no', $row->sales_bill_no)
                //         ->pluck('product_name')
                //         ->toArray();
                $TempSet = array();

                $TempSet = [
                    'sl' => $sl++,
                    'customer_name' => $row->customer_name_txt,
                    'customer_mobile' => $row->customer_mobile,
                    'sales_type' => $row->sales_type_txt,
                    'sales_bill_no' => $row->sales_bill_no,
                    'sales_date' => (new Datetime($row->sales_date))->format('d-m-Y'),
                    // 'product_name'       =>implode(', ', $product_names),
                    'product_name' => $row->product_name,
                    'total_quantity' => $row->product_qty,
                    'total_sales_amount' => $row->total_sales_amt,
                    'paid_amount' => $row->paid_amount,
                ];

                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                'ttl_product_qty' => $ttl_product_qty,
                'ttl_sales_amount' => number_format($ttl_sales_amount, 2),
                'ttl_paid_amount' => number_format($ttl_paid_amount, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.customer_details_report');
        }
    }

/* ------------------------- Sales report end */

/* ------------------------- Purchase report Start */
    //Purchase report index
    public function getPurchaseAll(Request $request)
    {
        if ($request->ajax()) {

            $sl = 1;

            // Searching variable
            $SDate = (empty($request->input('SDate'))) ? null : $request->input('SDate');
            $EDate = (empty($request->input('EDate'))) ? null : $request->input('EDate');
            $txt_product_name = (empty($request->input('txt_product_name'))) ? null : $request->input('txt_product_name');
            $txt_supplier_name = (empty($request->input('txt_supplier_name'))) ? null : $request->input('txt_supplier_name');
            $PurchaseNo = (empty($request->input('PurchaseNo'))) ? null : $request->input('PurchaseNo');
            $InvoiceNo = (empty($request->input('InvoiceNo'))) ? null : $request->input('InvoiceNo');
            $OrderNo = (empty($request->input('OrderNo'))) ? null : $request->input('OrderNo');
            $PGroupID = (empty($request->input('PGroupID'))) ? null : $request->input('PGroupID');
            $CategoryId = (empty($request->input('CategoryId'))) ? null : $request->input('CategoryId');
            $SubCatID = (empty($request->input('SubCatID'))) ? null : $request->input('SubCatID');
            $BrandID = (empty($request->input('BrandID'))) ? null : $request->input('BrandID');

            // Query
            $PurReportData = DB::table('pos_purchases_m as purm')
                ->where([['purm.is_delete', 0], ['purm.is_active', 1]])
                ->whereIn('purm.branch_id', HRS::getUserAccesableBranchIds())

                ->select('purm.purchase_date', 'purm.bill_no', 'purm.invoice_no', 'purm.delivery_to',
                    'purd.product_quantity', 'purd.unit_cost_price', 'purd.total_cost_price',
                    'pro.product_name', 'pro.prod_barcode', 'sup.sup_name', 'purm.order_no',
                    DB::raw('CONCAT(pro.product_name, " (", pro.prod_barcode, ")") AS product_name')
                )

                ->leftjoin('pos_purchases_d as purd', function ($PurReportData) {
                    $PurReportData->on('purd.purchase_bill_no', '=', 'purm.bill_no');
                })
                ->leftjoin('pos_products as pro', function ($PurReportData) {
                    $PurReportData->on('pro.id', '=', 'purd.product_id')
                        ->where([['pro.is_delete', 0], ['pro.is_active', 1]]);
                })

                ->leftjoin('pos_suppliers as sup', function ($PurReportData) {
                    $PurReportData->on('sup.id', '=', 'purm.supplier_id');
                })

                ->where(function ($PurReportData) use ($SDate, $EDate) {

                    if (!empty($SDate) && !empty($EDate)) {

                        $SDate = (new DateTime($SDate))->format('Y-m-d');
                        $EDate = (new DateTime($EDate))->format('Y-m-d');

                        $PurReportData->whereBetween('purm.purchase_date', [$SDate, $EDate]);
                    }

                })
                ->where(function ($PurReportData) use ($txt_product_name) {
                    if (!empty($txt_product_name)) {
                        $PurReportData->where('purd.product_id', $txt_product_name);
                    }
                })
                ->where(function ($PurReportData) use ($txt_supplier_name) {
                    if (!empty($txt_supplier_name)) {
                        $PurReportData->where('purm.supplier_id', $txt_supplier_name);

                    }
                })
                ->where(function ($PurReportData) use ($PurchaseNo) {
                    if (!empty($PurchaseNo)) {
                        $PurReportData->where('purm.bill_no', 'LIKE', $PurchaseNo);
                    }
                })
                ->where(function ($PurReportData) use ($InvoiceNo) {
                    if (!empty($InvoiceNo)) {
                        $PurReportData->where('purm.invoice_no', 'LIKE', $InvoiceNo);
                    }
                })
                ->where(function ($PurReportData) use ($OrderNo) {
                    if (!empty($OrderNo)) {
                        $PurReportData->where('purm.order_no', 'LIKE', $OrderNo);
                    }
                })
                ->where(function ($PurReportData) use ($PGroupID) {
                    if (!empty($PGroupID)) {
                        $PurReportData->where('pro.prod_group_id', $PGroupID);
                    }
                })
                ->where(function ($PurReportData) use ($CategoryId) {
                    if (!empty($CategoryId)) {
                        $PurReportData->where('pro.prod_cat_id', $CategoryId);
                    }
                })
                ->where(function ($PurReportData) use ($SubCatID) {
                    if (!empty($SubCatID)) {
                        $PurReportData->where('pro.prod_sub_cat_id', $SubCatID);
                    }
                })
                ->where(function ($PurReportData) use ($BrandID) {
                    if (!empty($BrandID)) {
                        $PurReportData->where('pro.prod_brand_id', $BrandID);
                    }
                })
                ->orderBy('purm.purchase_date', 'ASC')
                ->orderBy('purm.bill_no', 'ASC')
                ->get();

            $total_quantity = $PurReportData->sum('product_quantity');
            $total_amount = $PurReportData->sum('total_cost_price');

            $DataSet = array();
            $i = 0;
            foreach ($PurReportData as $Row) {
                $TempSet = array();

                $TempSet = [
                    'id' => $sl++,
                    'purchase_date' => (new DateTime($Row->purchase_date))->format('d-m-Y'),
                    'purchase_bill_no' => $Row->bill_no,
                    'order_no' => $Row->order_no,
                    'invoice_no' => $Row->invoice_no,
                    'supplier_name' => $Row->sup_name,
                    'product_name' => $Row->product_name,
                    'product_quantity' => $Row->product_quantity,
                    'unit_cost_price' => $Row->unit_cost_price,
                    'total_cost_price' => $Row->total_cost_price,
                ];

                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                'total_product_qnt' => $total_quantity,
                'total_amount' => number_format($total_amount, 2),
            );

            echo json_encode($json_data);

        } else {
            return view('POS.Report.purchase_report');
        }
    }

    ## Purchase Return Report
    public function getPurchaseReturnAll(Request $request)
    {
        if ($request->ajax()) {

            $sl = 1;

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $productId = (empty($request->input('productId'))) ? null : $request->input('productId');

            // Query
            $purReturnData = DB::table('pos_purchases_r_m as prm')
                ->where([['prm.is_delete', 0], ['prm.is_active', 1]])
                ->whereIn('prm.branch_id', HRS::getUserAccesableBranchIds())
                ->select('prm.return_date', 'prm.bill_no', 'prm.total_amount', 'prd.product_quantity',
                    'prd.unit_cost_price', 'prd.total_cost_price',
                    'prod.product_name', 'prod.prod_barcode',
                    DB::raw('CONCAT(prod.product_name, " (", prod.prod_barcode, ")") AS product_name')
                )
                ->leftjoin('pos_purchases_r_d as prd', function ($purReturnData) {
                    $purReturnData->on('prm.bill_no', '=', 'prd.pr_bill_no');
                })
                ->leftjoin('pos_products as prod', function ($purReturnData) {
                    $purReturnData->on('prod.id', '=', 'prd.product_id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->where(function ($purReturnData) use ($startDate, $endDate) {

                    if (!empty($startDate) && !empty($endDate)) {

                        $startDate = (new DateTime($startDate))->format('Y-m-d');
                        $endDate = (new DateTime($endDate))->format('Y-m-d');

                        $purReturnData->whereBetween('prm.return_date', [$startDate, $endDate]);
                    }
                })
                ->where(function ($purReturnData) use ($productId) {
                    if (!empty($productId)) {
                        $purReturnData->where('prd.product_id', $productId);
                    }
                })
                ->orderBy('prm.return_date', 'ASC')
                ->get();

            $product_quantity = $purReturnData->sum('product_quantity');
            $unit_cost_price = $purReturnData->sum('unit_cost_price');
            $total_cost_price = $purReturnData->sum('total_cost_price');

            $DataSet = array();
            $i = 0;

            foreach ($purReturnData as $row) {
                $TempSet = array();
                $TempSet = [
                    'id' => $sl++,
                    'return_date' => (new DateTime($row->return_date))->format('d-m-Y'),
                    'return_bill_no' => $row->bill_no,
                    'product_name' => $row->product_name,
                    'product_quantity' => $row->product_quantity,
                    'unit_cost_price' => $row->unit_cost_price,
                    'total_cost_price' => $row->total_cost_price,
                ];

                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                'product_quantity' => $product_quantity,
                'unit_cost_price' => number_format($unit_cost_price, 2),
                'total_cost_price' => number_format($total_cost_price, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.purchase_return_report');
        }
    }
/* ------------------------- Purchase report End */

/* ------------------------- Issue report Start */
    public function getIssueAll(Request $request)
    {
        if ($request->ajax()) {

            $sl = 1;

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $branchId = (empty($request->input('branchId'))) ? null : $request->input('branchId');
            $productId = (empty($request->input('productId'))) ? null : $request->input('productId');
            $issue_bill_no = (empty($request->input('issue_bill_no'))) ? null : $request->input('issue_bill_no');
            $groupId = (empty($request->input('groupId'))) ? null : $request->input('groupId');
            $catId = (empty($request->input('catId'))) ? null : $request->input('catId');
            $subCatId = (empty($request->input('subCatId'))) ? null : $request->input('subCatId');
            $brandId = (empty($request->input('brandId'))) ? null : $request->input('brandId');

            // Query
            $issueData = DB::table('pos_issues_m as pim')
                ->where([['pim.is_delete', 0], ['pim.is_active', 1]])
                ->whereIn('pim.branch_to', HRS::getUserAccesableBranchIds())
                ->select('pim.issue_date', 'pim.bill_no', 'pim.branch_to', 'pid.product_quantity', 'pid.total_cost_amount',
                    'br.branch_name', 'br.branch_code', 'prod.product_name', 'prod.prod_barcode', 'prod.sale_price', 'pim.requisition_no',
                    DB::raw('CONCAT(prod.product_name, " (", prod.prod_barcode, ")") AS product_name'))
                ->leftjoin('pos_issues_d as pid', function ($issueData) {
                    $issueData->on('pid.issue_bill_no', '=', 'pim.bill_no');
                })
                ->leftjoin('gnl_branchs as br', function ($issueData) {
                    $issueData->on('pim.branch_to', '=', 'br.id')
                        ->where([['br.is_delete', 0], ['br.is_active', 1]]);
                })
                ->leftjoin('pos_products as prod', function ($issueData) {
                    $issueData->on('pid.product_id', '=', 'prod.id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->where(function ($issueData) use ($startDate, $endDate) {
                    if (!empty($startDate) && !empty($endDate)) {
                        $startDate = (new DateTime($startDate))->format('Y-m-d');
                        $endDate = (new DateTime($endDate))->format('Y-m-d');

                        $issueData->whereBetween('pim.issue_date', [$startDate, $endDate]);
                    }
                })
                ->where(function ($issueData) use ($branchId) {
                    if (!empty($branchId)) {
                        $issueData->where('pim.branch_to', '=', $branchId);
                    }
                })
                ->where(function ($issueData) use ($productId) {
                    if (!empty($productId)) {
                        $issueData->where('pid.product_id', '=', $productId);
                    }
                })
                ->where(function ($issueData) use ($issue_bill_no) {
                    if (!empty($issue_bill_no)) {
                        $issueData->where('pim.bill_no', '=', $issue_bill_no);
                    }
                })
                ->where(function ($issueData) use ($groupId) {
                    if (!empty($groupId)) {
                        $issueData->where('prod.prod_group_id', $groupId);
                    }
                })
                ->where(function ($issueData) use ($catId) {
                    if (!empty($catId)) {
                        $issueData->where('prod.prod_cat_id', $catId);
                    }
                })
                ->where(function ($issueData) use ($subCatId) {
                    if (!empty($subCatId)) {
                        $issueData->where('prod.prod_sub_cat_id', $subCatId);
                    }
                })
                ->where(function ($issueData) use ($brandId) {
                    if (!empty($brandId)) {
                        $issueData->where('prod.prod_brand_id', $brandId);
                    }
                })
                ->orderBy('pim.issue_date', 'ASC')
                ->get();

            $sale_price = $issueData->sum('sale_price');
            $product_quantity = $issueData->sum('product_quantity');
            $total_cost_amount = $issueData->sum('total_cost_amount');

            $DataSet = array();
            $i = 0;
            foreach ($issueData as $row) {
                $TempSet = array();
                $TempSet = [
                    'id' => $sl++,
                    'issue_date' => (new DateTime($row->issue_date))->format('d-m-Y'),
                    'issue_bill_no' => $row->bill_no,
                    'requisition_no' => $row->requisition_no,
                    'branch_name' => (!empty($row->branch_name)) ? $row->branch_name . "(" . $row->branch_code . ")" : "",
                    'product_name' => $row->product_name,
                    'sale_price' => $row->sale_price,
                    'product_quantity' => $row->product_quantity,
                    'total_cost_amount' => $row->total_cost_amount,
                ];

                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                'sale_price' => $sale_price,
                'product_quantity' => number_format($product_quantity, 2),
                'total_cost_amount' => number_format($total_cost_amount, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.issue_report');
        }
    }

    public function getIssueReturnAll(Request $request)
    {
        if ($request->ajax()) {

            $sl = 1;

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $branchId = (empty($request->input('branchId'))) ? null : $request->input('branchId');
            $productId = (empty($request->input('productId'))) ? null : $request->input('productId');
            $ir_bill_no = (empty($request->input('bill_no'))) ? null : $request->input('bill_no');
            $groupId = (empty($request->input('groupId'))) ? null : $request->input('groupId');
            $catId = (empty($request->input('catId'))) ? null : $request->input('catId');
            $subCatId = (empty($request->input('subCatId'))) ? null : $request->input('subCatId');
            $brandId = (empty($request->input('brandId'))) ? null : $request->input('brandId');

            // Query
            $issueData = DB::table('pos_issues_r_m as pirm')
                ->where([['pirm.is_delete', 0], ['pirm.is_active', 1]])
                ->whereIn('pirm.branch_from', HRS::getUserAccesableBranchIds())
                ->select('pirm.return_date', 'pirm.bill_no', 'pirm.branch_from', 'br.branch_name', 'br.branch_code',
                    'pird.product_quantity', 'pird.unit_cost_price', 'pird.total_cost_amount',
                    'prod.product_name', 'prod.prod_barcode', 'prod.sale_price',
                    DB::raw('CONCAT(prod.product_name, " (", prod.prod_barcode, ")") AS product_name')
                )
                ->leftjoin('pos_issues_r_d as pird', function ($issueData) {
                    $issueData->on('pird.ir_bill_no', '=', 'pirm.bill_no');
                })
                ->leftjoin('gnl_branchs as br', function ($issueData) {
                    $issueData->on('pirm.branch_from', '=', 'br.id')
                        ->where([['br.is_delete', 0], ['br.is_active', 1]]);
                })
                ->leftjoin('pos_products as prod', function ($issueData) {
                    $issueData->on('pird.product_id', '=', 'prod.id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->where(function ($issueData) use ($startDate, $endDate) {
                    if (!empty($startDate) && !empty($endDate)) {
                        $startDate = (new DateTime($startDate))->format('Y-m-d');
                        $endDate = (new DateTime($endDate))->format('Y-m-d');

                        $issueData->whereBetween('pirm.return_date', [$startDate, $endDate]);
                    }

                })
                ->where(function ($issueData) use ($branchId) {
                    if (!empty($branchId)) {
                        $issueData->where('pirm.branch_from', '=', $branchId);
                    }
                })
                ->where(function ($issueData) use ($productId) {
                    if (!empty($productId)) {
                        $issueData->where('pird.product_id', '=', $productId);
                    }
                })
                ->where(function ($issueData) use ($ir_bill_no) {
                    if (!empty($ir_bill_no)) {
                        $issueData->where('pirm.bill_no', '=', $ir_bill_no);
                    }
                })
                ->where(function ($issueData) use ($groupId) {
                    if (!empty($groupId)) {
                        $issueData->where('prod.prod_group_id', $groupId);
                    }
                })
                ->where(function ($issueData) use ($catId) {
                    if (!empty($catId)) {
                        $issueData->where('prod.prod_cat_id', $catId);
                    }
                })
                ->where(function ($issueData) use ($subCatId) {
                    if (!empty($subCatId)) {
                        $issueData->where('prod.prod_sub_cat_id', $subCatId);
                    }
                })
                ->where(function ($issueData) use ($brandId) {
                    if (!empty($brandId)) {
                        $issueData->where('prod.prod_brand_id', $brandId);
                    }
                })
                ->orderBy('pirm.return_date', 'ASC')
                ->get();

            $sale_price = $issueData->sum('sale_price');
            $product_quantity = $issueData->sum('product_quantity');
            $total_cost_amount = $issueData->sum('total_cost_amount');

            $DataSet = array();
            $i = 0;
            foreach ($issueData as $row) {
                $TempSet = array();
                $TempSet = [
                    'id' => $sl++,
                    'return_date' => (new DateTime($row->return_date))->format('d-m-Y'),
                    'Issue Return Bill No' => $row->bill_no,
                    'branch_name' => (!empty($row->branch_name)) ? $row->branch_name . "(" . $row->branch_code . ")" : "",
                    'product_name' => $row->product_name,
                    'sale_price' => $row->sale_price,
                    'product_quantity' => $row->product_quantity,
                    'total_cost_amount' => $row->total_cost_amount,
                ];

                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                'sale_price' => $sale_price,
                'product_quantity' => number_format($product_quantity, 2),
                'total_cost_amount' => number_format($total_cost_amount, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.issue_return_report');
        }
    }

/* ------------------------- Issue report End */

/* ------------------------- Transfer report Start */
    public function getTransferIn(Request $request)
    {
        if ($request->ajax()) {

            $sl = 1;

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $branchId = (empty($request->input('branchId'))) ? null : $request->input('branchId');

            $productId = (empty($request->input('productId'))) ? null : $request->input('productId');
            $groupId = (empty($request->input('groupId'))) ? null : $request->input('groupId');
            $catId = (empty($request->input('catId'))) ? null : $request->input('catId');
            $subCatId = (empty($request->input('subCatId'))) ? null : $request->input('subCatId');
            $brandID = (empty($request->input('brandID'))) ? null : $request->input('brandID');

            $TReportData = DB::table('pos_transfers_m as ptm')
                ->where([['ptm.is_delete', 0], ['ptm.is_active', 1]])
                ->whereIn('ptm.branch_to', HRS::getUserAccesableBranchIds())
                ->select('ptm.transfer_date', 'ptm.bill_no', 'br.branch_name', 'br.branch_code',
                    'brT.branch_name as branch_name_to', 'brT.branch_code as branch_code_to',
                    'ptd.product_quantity', 'ptd.unit_cost_price', 'ptd.total_cost_price',
                    'prod.product_name', 'prod.prod_barcode',
                    DB::raw('CONCAT(prod.product_name, " (", prod.prod_barcode, ")") AS product_name'))
                ->leftjoin('pos_transfers_d as ptd', function ($TReportData) {
                    $TReportData->on('ptd.transfer_bill_no', '=', 'ptm.bill_no');
                })
                ->leftjoin('pos_products as prod', function ($TReportData) {
                    $TReportData->on('prod.id', '=', 'ptd.product_id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->leftjoin('gnl_branchs as br', function ($TReportData) {
                    $TReportData->on('br.id', '=', 'ptm.branch_from')
                        ->where([['br.is_delete', 0], ['br.is_active', 1], ['br.is_approve', 1]]);
                })

                ->leftjoin('gnl_branchs as brT', function ($TReportData) {
                    $TReportData->on('brT.id', '=', 'ptm.branch_to')
                        ->where([['brT.is_delete', 0], ['brT.is_active', 1], ['brT.is_approve', 1]]);
                })

                ->where(function ($TReportData) use ($startDate, $endDate) {
                    if (!empty($startDate) && !empty($endDate)) {

                        $startDate = (new DateTime($startDate))->format('Y-m-d');
                        $endDate = (new DateTime($endDate))->format('Y-m-d');

                        $TReportData->whereBetween('ptm.transfer_date', [$startDate, $endDate]);
                    }
                })
                ->where(function ($TReportData) use ($branchId) {
                    if (!empty($branchId)) {
                        $TReportData->where('ptm.branch_to', '=', $branchId)
                            ->orWhere('ptm.branch_from', '=', $branchId);
                    }
                })
                ->where(function ($TReportData) use ($productId) {
                    if (!empty($productId)) {
                        $TReportData->where('ptd.product_id', '=', $productId);
                    }
                })
                ->where(function ($TReportData) use ($groupId) {
                    if (!empty($groupId)) {
                        $TReportData->where('prod.prod_group_id', '=', $groupId);
                    }
                })
                ->where(function ($TReportData) use ($catId) {
                    if (!empty($catId)) {
                        $TReportData->where('prod.prod_cat_id', '=', $catId);
                    }
                })
                ->where(function ($TReportData) use ($subCatId) {
                    if (!empty($subCatId)) {
                        $TReportData->where('prod.prod_sub_cat_id', '=', $subCatId);
                    }
                })
                ->where(function ($TReportData) use ($brandID) {
                    if (!empty($brandID)) {
                        $TReportData->where('prod.prod_brand_id', '=', $brandID);
                    }
                })
                ->orderBy('ptm.transfer_date', 'ASC')
                ->get();

            $totalQuantity = $TReportData->sum('product_quantity');
            $totalUnitPrice = $TReportData->sum('unit_cost_price');
            $totalAmount = $TReportData->sum('total_cost_price');

            if (!empty($search)) {
                $totalFiltered = count($TReportData);
            }
            $DataSet = array();
            $i = 0;
            foreach ($TReportData as $row) {
                $TempSet = array();

                $TempSet = [
                    'id' => $sl++,
                    'transfer_date' => (new Datetime($row->transfer_date))->format('d-m-Y'),
                    'transfer_bill_no' => $row->bill_no,
                    'branch_from' => (!empty($row->branch_name)) ? $row->branch_name . "(" . $row->branch_code . ")" : "",
                    'branch_to' => (!empty($row->branch_name_to)) ? $row->branch_name_to . "(" . $row->branch_code_to . ")" : "",
                    // 'prod_barcode' => $row->prod_barcode,
                    'product_name' => $row->product_name,
                    'product_quantity' => $row->product_quantity,
                    'unit_cost_price' => $row->unit_cost_price,
                    'total_cost_price' => $row->total_cost_price,
                ];

                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                "totalQuantity" => number_format($totalQuantity, 0),
                "totalUnitPrice" => number_format($totalUnitPrice, 2),
                "totalAmount" => number_format($totalAmount, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.transfer_in');
        }
    }

    public function getTransferOut(Request $request)
    {
        if ($request->ajax()) {

            $sl = 1;

            // Searching variable
            $startDate = (empty($request->input('startDate'))) ? null : $request->input('startDate');
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $branchId = (empty($request->input('branchId'))) ? null : $request->input('branchId');
            // $SupplierID = (empty($request->input('SupplierID'))) ? null : $request->input('SupplierID');
            $productId = (empty($request->input('productId'))) ? null : $request->input('productId');
            $groupId = (empty($request->input('groupId'))) ? null : $request->input('groupId');
            $catId = (empty($request->input('catId'))) ? null : $request->input('catId');
            $subCatId = (empty($request->input('subCatId'))) ? null : $request->input('subCatId');
            $brandID = (empty($request->input('brandID'))) ? null : $request->input('brandID');

            $TReportData = DB::table('pos_transfers_m as ptm')
                ->where([['ptm.is_delete', 0], ['ptm.is_active', 1]])
                ->whereIn('ptm.branch_from', HRS::getUserAccesableBranchIds())
                ->select('ptm.transfer_date', 'ptm.bill_no', 'br.branch_name', 'br.branch_code',
                    'brF.branch_name as branch_name_from', 'brF.branch_code as branch_code_from',
                    'ptd.product_quantity', 'ptd.unit_cost_price', 'ptd.total_cost_price',
                    'prod.product_name', 'prod.prod_barcode',
                    DB::raw('CONCAT(prod.product_name, " (", prod.prod_barcode, ")") AS product_name'))
                ->leftjoin('pos_transfers_d as ptd', function ($TReportData) {
                    $TReportData->on('ptd.transfer_bill_no', '=', 'ptm.bill_no');
                })
                ->leftjoin('pos_products as prod', function ($TReportData) {
                    $TReportData->on('prod.id', '=', 'ptd.product_id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->leftjoin('gnl_branchs as br', function ($TReportData) {
                    $TReportData->on('br.id', '=', 'ptm.branch_to')
                        ->where([['br.is_delete', 0], ['br.is_active', 1], ['br.is_approve', 1]]);
                })

                ->leftjoin('gnl_branchs as brF', function ($TReportData) {
                    $TReportData->on('brF.id', '=', 'ptm.branch_from')
                        ->where([['brF.is_delete', 0], ['brF.is_active', 1], ['brF.is_approve', 1]]);
                })
                ->where(function ($TReportData) use ($startDate, $endDate) {
                    if (!empty($startDate) && !empty($endDate)) {

                        $startDate = (new DateTime($startDate))->format('Y-m-d');
                        $endDate = (new DateTime($endDate))->format('Y-m-d');

                        $TReportData->whereBetween('ptm.transfer_date', [$startDate, $endDate]);
                    }
                })
                ->where(function ($TReportData) use ($branchId) {
                    if (!empty($branchId)) {
                        $TReportData->where('ptm.branch_from', '=', $branchId)
                            ->orWhere('ptm.branch_to', '=', $branchId);
                    }
                })
                ->where(function ($TReportData) use ($productId) {
                    if (!empty($productId)) {
                        $TReportData->where('ptd.product_id', '=', $productId);
                    }
                })
                ->where(function ($TReportData) use ($groupId) {
                    if (!empty($groupId)) {
                        $TReportData->where('prod.prod_group_id', '=', $groupId);
                    }
                })
                ->where(function ($TReportData) use ($catId) {
                    if (!empty($catId)) {
                        $TReportData->where('prod.prod_cat_id', '=', $catId);
                    }
                })
                ->where(function ($TReportData) use ($subCatId) {
                    if (!empty($subCatId)) {
                        $TReportData->where('prod.prod_sub_cat_id', '=', $subCatId);
                    }
                })
                ->where(function ($TReportData) use ($brandID) {
                    if (!empty($brandID)) {
                        $TReportData->where('prod.prod_brand_id', '=', $brandID);
                    }
                })
                ->orderBy('ptm.transfer_date', 'ASC')
                ->get();

            $totalQuantity = $TReportData->sum('product_quantity');
            $totalUnitPrice = $TReportData->sum('unit_cost_price');
            $totalAmount = $TReportData->sum('total_cost_price');

            $DataSet = array();
            $i = 0;
            foreach ($TReportData as $row) {
                $TempSet = array();

                $TempSet = [
                    'id' => $sl++,
                    'transfer_date' => (new Datetime($row->transfer_date))->format('d-m-Y'),
                    'transfer_bill_no' => $row->bill_no,
                    // 'prod_barcode' => $row->prod_barcode,
                    'branch_from' => (!empty($row->branch_name_from)) ? $row->branch_name_from . "(" . $row->branch_code_from . ")" : "",
                    'branch_to' => (!empty($row->branch_name)) ? $row->branch_name . "(" . $row->branch_code . ")" : "",
                    'product_name' => $row->product_name,
                    'product_quantity' => $row->product_quantity,
                    'unit_cost_price' => $row->unit_cost_price,
                    'total_cost_price' => $row->total_cost_price,
                ];

                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                "totalQuantity" => number_format($totalQuantity, 0),
                "totalUnitPrice" => number_format($totalUnitPrice, 2),
                "totalAmount" => number_format($totalAmount, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.transfer_out');
        }
    }

/* ------------------------- Transfer report End */

/* ------------------------- Requisition report Start */
    public function getRequisition(Request $request)
    {
        $requisitionData = array();

        if ($request->isMethod('post')) {

            $requestData = $request->all();

            // Searching variable
            $startDate = (empty($requestData['StartDate'])) ? null : $requestData['StartDate'];
            $endDate = (empty($requestData['EndDate'])) ? null : $requestData['EndDate'];
            $branchId = (empty($requestData['branch_id'])) ? null : $requestData['branch_id'];
            $supplierId = (empty($requestData['supplier_id'])) ? null : $requestData['supplier_id'];
            $productId = (empty($requestData['product_id'])) ? null : $requestData['product_id'];

            // Query
            $requisitionData = DB::table('pos_requisitions_m as prm')
                ->where([['prm.is_active', 1], ['prm.is_delete', 0], ['prm.is_approve', 1]])
                ->select('prm.requisition_date', 'prm.requisition_no', 'prm.branch_from', 'gb.branch_name as branch_from', 'gb.branch_code', 'prd.product_id',
                    'prd.product_quantity', 'prod.product_name', 'prod.prod_barcode',
                    'prod.supplier_id', 'ps.sup_name')
                ->leftjoin('pos_requisitions_d as prd', function ($requisitionData) {
                    $requisitionData->on('prd.requisition_no', '=', 'prm.requisition_no');
                })
                ->leftjoin('pos_products as prod', function ($requisitionData) {
                    $requisitionData->on('prd.product_id', '=', 'prod.id')
                        ->where([['prod.is_delete', 0]]);
                })
                ->leftjoin('pos_suppliers as ps', function ($requisitionData) {
                    $requisitionData->on('prod.supplier_id', '=', 'ps.id')
                        ->where([['ps.is_delete', 0]]);
                })
                ->leftjoin('gnl_branchs as gb', function ($requisitionData) {
                    $requisitionData->on('prm.branch_from', '=', 'gb.id')
                        ->where([['gb.is_delete', 0]]);
                })
                ->where(function ($requisitionData) use ($startDate, $endDate) {
                    if (!empty($startDate) && !empty($endDate)) {
                        $startD = (new DateTime($startDate))->format('Y-m-d');
                        $endD = (new DateTime($endDate))->format('Y-m-d');

                        $requisitionData->whereBetween('prm.requisition_date', [$startD, $endD]);
                    }
                })
                ->where(function ($requisitionData) use ($branchId) {
                    if (!empty($branchId)) {
                        $requisitionData->where('prm.branch_from', $branchId);
                    }
                })
                ->where(function ($requisitionData) use ($supplierId) {
                    if (!empty($supplierId)) {
                        $requisitionData->where('prod.supplier_id', $supplierId);
                    }
                })
                ->where(function ($requisitionData) use ($productId) {
                    if (!empty($productId)) {
                        $requisitionData->where('prd.product_id', $productId);
                    }
                })
                ->get();

            // dd($requisitionData);

            $DataSetNew = array();
            foreach ($requisitionData as $rowN) {
                $DataSetNew[$rowN->requisition_no][] = $rowN->product_id;
            }

            return view('POS.Report.requisition_report', compact('requisitionData', 'startDate', 'endDate', 'branchId', 'supplierId', 'productId', 'DataSetNew'));
        } else {
            return view('POS.Report.requisition_report', compact('requisitionData'));
        }
    }
/* ------------------------- Requisition report End */

/* ------------------------- Order report Start */
    public function getProdOrder(Request $request)
    {
        $ProdOrderData = array();

        if ($request->isMethod('post')) {

            $requestData = $request->all();

            // Searching variable
            $startDate = (empty($requestData['StartDate'])) ? null : $requestData['StartDate'];
            $endDate = (empty($requestData['EndDate'])) ? null : $requestData['EndDate'];
            $branchId = (empty($requestData['branch_id'])) ? null : $requestData['branch_id'];
            $supplierId = (empty($requestData['supplier_id'])) ? null : $requestData['supplier_id'];
            $productId = (empty($requestData['product_id'])) ? null : $requestData['product_id'];

            // Query
            $ProdOrderData = DB::table('pos_orders_m as pom')
                ->where([['pom.is_active', 1], ['pom.is_delete', 0], ['pom.is_approve', 1]])
                ->select('pom.order_no', 'pom.order_date', 'pom.delivery_date', 'pom.order_to',
                    'pom.delivery_place', 'pod.product_id', 'pod.product_quantity', 'prod.product_name',
                    'prod.prod_barcode', 'ps.sup_name', 'gb.branch_name as delivery_place', 'gb.branch_code')
                ->leftjoin('pos_orders_d as pod', function ($ProdOrderData) {
                    $ProdOrderData->on('pod.order_no', '=', 'pom.order_no');
                })
                ->leftjoin('pos_products as prod', function ($ProdOrderData) {
                    $ProdOrderData->on('pod.product_id', '=', 'prod.id')
                        ->where([['prod.is_delete', 0], ['prod.is_active', 1]]);
                })
                ->leftjoin('pos_suppliers as ps', function ($ProdOrderData) {
                    $ProdOrderData->on('prod.supplier_id', '=', 'ps.id')
                        ->where([['ps.is_delete', 0], ['ps.is_active', 1]]);
                })
                ->leftjoin('gnl_branchs as gb', function ($ProdOrderData) {
                    $ProdOrderData->on('pom.delivery_place', '=', 'gb.id')
                        ->where([['gb.is_delete', 0], ['gb.is_active', 1]]);
                })
                ->where(function ($ProdOrderData) use ($startDate, $endDate) {
                    if (!empty($startDate) && !empty($endDate)) {
                        $startD = (new DateTime($startDate))->format('Y-m-d');
                        $endD = (new DateTime($endDate))->format('Y-m-d');

                        $ProdOrderData->whereBetween('pom.order_date', [$startD, $endD]);
                    }
                })
                ->where(function ($ProdOrderData) use ($branchId) {
                    if (!empty($branchId)) {
                        $ProdOrderData->where('pom.delivery_place', $branchId);
                    }
                })
                ->where(function ($ProdOrderData) use ($supplierId) {
                    if (!empty($supplierId)) {
                        $ProdOrderData->where('prod.supplier_id', $supplierId);
                    }
                })
                ->where(function ($ProdOrderData) use ($productId) {
                    if (!empty($productId)) {
                        $ProdOrderData->where('pod.product_id', $productId);
                    }
                })
                ->get();

            $DataSetNew = array();
            foreach ($ProdOrderData as $rowN) {
                $DataSetNew[$rowN->order_no][] = $rowN->product_id;
            }

            // for download pdf file
            if (isset($requestData['pdfButton']) && $requestData['pdfButton'] == 'pdfButton') {
                // https://www.webslesson.info/2018/06/laravel-how-to-generate-dynamic-pdf-from-html-using-dompdf.html

                $requestData['pdfButton'] = null;

                $outputHtml = self::htmlProdOrder($ProdOrderData, $DataSetNew);

                $pdf = \App::make('dompdf.wrapper');
                $pdf->loadHTML($outputHtml);
                return $pdf->download('ProducctOrder.pdf');
            }

            return view('POS.Report.order_report', compact('ProdOrderData', 'startDate', 'endDate', 'branchId', 'supplierId', 'productId', 'DataSetNew'));
        } else {
            return view('POS.Report.order_report', compact('ProdOrderData'));
        }
    }

    public function htmlProdOrder($ProdOrderData, $DataSetNew)
    {
        $output = '<h3 align="center" style="margin:0px;">USHA Foundation</h3>';
        $output .= '<h5 align="center" style="margin:0px;">Head Office</h5>';
        $output .= '<p align="center" style="margin:0px;">Product Order Report</p>';
        $output .= '<p align="center" style="margin:0px;">(31-May-2020 to 31-May-2020)</p><br>';

        $output .= '<table width="100%" style="border-collapse: collapse; border: 0; font-size: 12px; ">
            <thead style="background-color: #17b3a3; text-align: center; color: #fff;"><tr>
                <th style="border: 1px solid; padding:2px; " >SL</th>
                <th style="border: 1px solid; padding:2px; " >Order No</th>
                <th style="border: 1px solid; padding:2px; "> Order Date</th>
                <th style="border: 1px solid; padding:2px; ">Delivery Date</th>
                <th style="border: 1px solid; padding:2px; ">Delivery Place</th>
                <th style="border: 1px solid; padding:2px; ">Supplier</th>
                <th style="border: 1px solid; padding:2px; ">Product Name</th>
                <th style="border: 1px solid; padding:2px; ">Quantity</th>
            </tr></thead><tbody>';

        $i = 0;
        $TotalQnt = 0;
        $ProdOrderArr = array();

        foreach ($ProdOrderData as $row) {
            $TotalQnt += $row->product_quantity;
            $rSpam = count($DataSetNew[$row->order_no]);
            $output .= '<tr>';
            if (!in_array($row->order_no, $ProdOrderArr)) {
                $i++;
                array_push($ProdOrderArr, $row->order_no);

                $output .= '<td style="border: 1px solid; padding:2px; text-align:center; " rowspan="' . $rSpam . '" class="text-center">' . $i . '</td>';
                $output .= '<td style="border: 1px solid; padding:2px; " rowspan="' . $rSpam . '" class="text-center">' . $row->order_no . '</td>';
                $output .= '<td style="border: 1px solid; padding:2px; " rowspan="' . $rSpam . '" class="text-center">' . $row->order_date . '</td>';
                $output .= '<td style="border: 1px solid; padding:2px; " rowspan="' . $rSpam . '" class="text-center">' . $row->delivery_date . '</td>';
                $output .= '<td style="border: 1px solid; padding:2px; " rowspan="' . $rSpam . '" class="text-center">' . $row->delivery_place . '</td>';
            }

            $output .= '<td style="border: 1px solid; padding:2px; " >' . $row->sup_name . '</td>';
            $output .= '<td style="border: 1px solid; padding:2px; " >' . $row->product_name . " (" . $row->prod_barcode . ")" . '</td>';
            $output .= '<td style="border: 1px solid; padding:2px;  text-align:center;" >' . $row->product_quantity . '</td>';

            $output .= '</tr>';
        }

        $output .= '</tbody></table>';

        return $output;
    }
/* ------------------------- Order report End */

    public function getbranchcustomer(Request $request)
    {
        if ($request->ajax()) {

            $sl = 1;

            // Searching variable
            $branchId = (empty($request->input('branchId'))) ? null : $request->input('branchId');

            $branchRData = DB::table('pos_customers as cus')
                ->where([['cus.is_active', 1], ['cus.is_delete', 0], ['cus.branch_id', '<>', 1]])
                ->whereIn('br.id', HRS::getUserAccesableBranchIds())
                ->select(DB::raw("br.branch_name, COUNT(cus.id) as nCustomer"), 'br.branch_code')
                ->leftjoin('gnl_branchs as br', function ($branchRData) {
                    $branchRData->on('br.id', 'cus.branch_id')
                        ->where([['br.is_delete', 0], ['br.is_active', 1], ['br.is_approve', 1]]);
                })
                ->where(function ($branchRData) use ($branchId) {
                    if (!empty($branchId)) {
                        $branchRData->where('cus.branch_id', $branchId);
                    }
                })
                ->groupBy('br.branch_name')
                ->get();

            $tcustomer = $branchRData->sum('nCustomer');

            if (!empty($branchId)) {
                $totalFiltered = count($branchRData);
            }

            $DataSet = array();
            foreach ($branchRData as $row) {
                $TempSet = array();

                $TempSet = [
                    'sl' => $sl++,
                    'branch_name' => (!empty($row->branch_name)) ? $row->branch_name . "(" . $row->branch_code . ")" : "",
                    'customer_count' => $row->nCustomer,
                ];

                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "data" => $DataSet,
                "tcustomer" => $tcustomer,
            );

            echo json_encode($json_data);

        } else {
            return view('POS.Report.branch_customer');
        }
    }

/*    // ---------------------- Under Construction Report ------------------------------------- */

    public function getRegionSales(Request $request)
    {
        return view('errors.under_construction');
    }

    public function getMISReportFirst(Request $request)
    {
        return view('errors.under_construction');

        /* if ($request->isMethod('post'))
    {

    // $misQuery = DB::table('pos_suppliers as ps')
    //         ->where([['ps.is_active', 1], ['ps.is_delete', 0]])
    //         ->select('ps.id', 'pp.supplier_id', 'ps.sup_name', 'pp.product_name', )
    //         ->leftjoin('pos_products as pp', function($misQuery){
    //             $misQuery->on('pp.supplier_id', '=', 'ps.id')
    //                 ->where([['pp.is_active', 1], ['pp.is_delete', 0]]);
    //         })
    //         ->addSelect(['ttl_outlet' => DB::table('pos_issues_d as pid')
    //                 ->selectRaw('COUNT(pid.branch_to)')
    //                 ->whereColumn('pid.product_id', 'pp.id')
    //                 ->where('is_delete', 0)
    //                 ->limit(1)
    //         ])
    //         ->get();

    $suplierProd = DB::table('pos_suppliers as ps')
    ->where([['ps.is_active', 1], ['ps.is_delete', 0]])
    ->select('ps.id', 'ppm.supplier_id', 'ps.sup_name', 'pp.product_name')
    ->join('pos_purchases_m as ppm', function($suplierProd){
    $suplierProd->on('ppm.supplier_id', '=', 'ps.id')
    ->where([['ppm.is_active', 1], ['ppm.is_delete', 0]]);
    })
    ->join('pos_purchases_d as ppd', function($suplierProd){
    $suplierProd->on('ppd.purchase_id', '=', 'ppm.id')
    ->where([['ppd.is_active', 1], ['ppd.is_delete', 0]]);
    })
    ->join('pos_products as pp', function($suplierProd){
    $suplierProd->on('pp.id', '=', 'ppd.product_id')
    ->where([['pp.is_active', 1], ['pp.is_delete', 0]]);
    })
    ->distinct('pp.id')
    ->get();

    // $outlet = DB::table('pos_issues_d as pid')
    //         ->select('pid.product_id', DB::raw('COUNT(pid.branch_to)'))
    //         ->where('is_delete', 0)
    //         ->groupBy('pid.branch_to')
    //         ->distinct('pid.product_id')
    //         ->get();

    // dd($outlet);

    $misData = array();
    foreach ($suplierProd as $row)
    {
    $misData[$row->sup_name][] = $row->product_name;
    }

    return view('POS.Report.MIS_report-1', compact('misData'));
    }
    else
    {
    return view('POS.Report.MIS_report-1');
    }*/
    }

    public function getMISReportSecond(Request $request)
    {
        return view('errors.under_construction');
        // if ($request->isMethod('post'))
        // {
        //     dd(1);
        // }
        // else
        // {
        //     return view('POS.Report.MIS_report-2');
        // }
    }

    public function getincentive(Request $request)
    {
        // if ($request->ajax()) {
        // } else {
        //     return view('POS.ReshmaReport.incentive');
        // }

        return view('errors.under_construction');
    }

    public function getcollregister(Request $request)
    {
        // if ($request->ajax()) {
        // } else {
        //     return view('POS.ReshmaReport.collregister');
        // }

        return view('errors.under_construction');
    }

    public function getbranch(Request $request)
    {
        return view('errors.under_construction');

        // if ($request->ajax()) {
        // } else {
        //     return view('POS.ReshmaReport.branch_report');
        // }
    }

    public function getzone(Request $request)
    {
        // if ($request->ajax()) {
        // } else {
        //     return view('POS.ReshmaReport.zone_report');
        // }

        return view('errors.under_construction');
    }

    public function getarea(Request $request)
    {
        // if ($request->ajax()) {
        // } else {
        //     return view('POS.ReshmaReport.area_report');
        // }

        return view('errors.under_construction');
    }

    public function getInstallmentReceivable(Request $request)
    {
        return view('errors.under_construction');
    }

/*    // ---------------------- Under Construction Report End ------------------------------------- */

    public function bac_getCurrentDue(Request $request)
    {
        if ($request->ajax()) {

            $companyID = Common::getCompanyId();
            $endDate = (empty($request->input('endDate'))) ? null : $request->input('endDate');
            $branchID = (empty($request->input('branchID'))) ? null : $request->input('branchId');

            $endDate = new DateTime($endDate);
            $endDate = $endDate->format('Y-m-d');

            $currentDue = DB::table('pos_sales_m as sm')
            // ['sm.is_complete', 0]
            /**
             * is_complete 0 check hobe na karon amak jei date select kora hoyeche oi date er data dekhabe
             * jodi payment korei dey tahole is_complete 1 hole calculation a asbe na tai ai condition dewa jacche na
             */
                ->where([['sm.is_delete', 0], ['sm.sales_type', 2]])
                ->whereIn('sm.branch_id', HRS::getUserAccesableBranchIds())
                ->select('sm.branch_id', 'c.customer_name', 'c.customer_no', 'sm.sales_bill_no',
                    'sm.sales_date', 'sm.total_amount as sales_amount', 'sm.installment_amount',
                    'sm.installment_type', 'sm.installment_month',
                    DB::raw('(sm.paid_amount - sm.vat_amount - sm.service_charge) as first_installment,
                        (CASE
                            WHEN sm.installment_type = 1 THEN sm.installment_month
                            ELSE (FLOOR(DATEDIFF(DATE(DATE_FORMAT(DATE_ADD(sm.sales_date, INTERVAL +sm.installment_month MONTH), "%Y%m%d")), DATE(DATE_FORMAT(sm.sales_date, "%Y%m%d")))/7))
                        END) as ttl_installment
                        '))
                ->leftjoin('pos_customers as c', function ($currentDue) {
                    $currentDue->on('c.customer_no', '=', 'sm.customer_id')
                        ->where('c.is_delete', 0);
                })
                ->addSelect(['col_amt_till_sel' => DB::table('pos_collections as col')
                        ->select(DB::raw('SUM(col.collection_amount)'))
                        ->whereColumn('col.sales_bill_no', 'sm.sales_bill_no')
                        ->where([['col.is_delete', 0], ['col.collection_date', '<=', $endDate]])
                        ->limit(1),
                    'col_amt_till_today' => DB::table('pos_collections as col2')
                        ->select(DB::raw('SUM(col2.collection_amount)'))
                        ->whereColumn('col2.sales_bill_no', 'sm.sales_bill_no')
                        ->where('col2.is_delete', 0)
                        ->limit(1),
                ])
                ->where(function ($currentDue) use ($endDate, $branchID) {
                    if (!empty($endDate)) {
                        $currentDue->where('sm.sales_date', '<=', $endDate);
                    }

                    if (!empty($branchID)) {
                        $currentDue->where('sm.branch_id', $branchID);
                    }

                })
                ->orderBy('sm.sales_date', 'ASC')
                ->orderBy('sm.id', 'ASC')
                ->get();

            // dd($currentDue);

            $sl = 0;
            $ttl_sales_amount = 0;
            $ttl_current_due = 0;
            $ttl_total_balance = 0;
            $ttl_inst_amount = 0;

            $DataSet = array();
            $scheduleDate = array();

            $paid = '<span class="text-primary">Paid</span>';
            $due = '<span class="text-danger">Due</span>';

            foreach ($currentDue as $row) {

                $scheduleDate = POSS::installmentSchedule($companyID, $branchID, null, $row->sales_date, $row->installment_type, $row->installment_month);

                // Check today installment schedule or not
                if (in_array($endDate, $scheduleDate)) {

                    $endDateIndex = array_search($endDate, $scheduleDate) + 1;

                    $row_salesAmount = $row->sales_amount;
                    $row_paidAmountSel = $row->col_amt_till_sel;

                    // $row_ttl_installment = $row->ttl_installment;
                    $row_installment_amount = $row->installment_amount;
                    $row_first_installment = $row->first_installment;

                    $payable_amnt_till_sel = ($row_first_installment + ($row_installment_amount * $endDateIndex));

                    // Today current due or not
                    if ($row_paidAmountSel < $payable_amnt_till_sel) {

                        $due_amount_till_sel = ($payable_amnt_till_sel - $row_paidAmountSel);

                        if ($due_amount_till_sel < $row_installment_amount) {
                            $current_due = $due_amount_till_sel;
                        } else {
                            $current_due = $row_installment_amount;
                        }

                        $total_balance = ($row_salesAmount - $row_paidAmountSel);

                        // Status
                        $col_amt_till_today = $row->col_amt_till_today;

                        if ($payable_amnt_till_sel < $col_amt_till_today) {
                            $Status = $paid;
                        } else {
                            $Status = $due;
                        }

                        $TempSet = array();

                        $TempSet = [
                            'sl' => ++$sl,
                            'customer_no' => $row->customer_no,
                            'customer_name' => $row->customer_name,
                            'sales_bill_no' => $row->sales_bill_no,
                            'installment' => $row->ttl_installment,
                            'installment_amount' => number_format($row_installment_amount, 2),
                            'sales_date' => (new Datetime($row->sales_date))->format('d-m-Y'),
                            'sales_amount' => $row_salesAmount,
                            'current_due' => number_format($current_due, 2),
                            'total_balance' => number_format($total_balance, 2),
                            'status' => $Status,
                        ];

                        $DataSet[] = $TempSet;

                        $ttl_sales_amount += $row_salesAmount;
                        $ttl_current_due += $current_due;
                        $ttl_total_balance += $total_balance;
                        $ttl_inst_amount += $row_installment_amount;
                    }
                }
            }

            $total_row = count($DataSet);

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($total_row),
                "data" => $DataSet,
                "totalRow" => $total_row,
                "ttl_sales_amount" => number_format($ttl_sales_amount, 2),
                "ttl_current_due" => number_format($ttl_current_due, 2),
                "ttl_total_balance" => number_format($ttl_total_balance, 2),
                "ttl_inst_amount" => number_format($ttl_inst_amount, 2),
            );

            echo json_encode($json_data);
        } else {
            return view('POS.Report.current_due');
        }
    }

    public function bak_getCustomerDue(Request $request)
    {
        if ($request->ajax()) {

            $totalData = 0;
            $totalFiltered = $totalData;
            $sl = 1;

            // Searching variable
            $search = (empty($request->input('search.value'))) ? null : $request->input('search.value');
            $branchID = (empty($request->input('branchId'))) ? null : $request->input('branchId');
            $employeeID = (empty($request->input('employeeID'))) ? null : $request->input('employeeID');
            $customerID = (empty($request->input('CustomerID'))) ? null : $request->input('CustomerID');
            $saleBillNo = (empty($request->input('SaleBillNo'))) ? null : $request->input('SaleBillNo');

            $custDueRData = DB::table('pos_sales_m as sm')
                ->where([['sm.sales_type', 2], ['sm.is_delete', 0]])
                ->select('sm.sales_bill_no', 'sm.sales_date', 'sm.total_amount as sales_amt',
                    'sm.service_charge as pro_fee', 'sm.total_quantity as product_quantity',
                    'c.customer_no', 'c.customer_name', 'c.customer_mobile', 'c.customer_nid',
                    'c.spouse_name', DB::raw('(sm.total_amount + sm.service_charge) as gross_total'))
                ->leftjoin('pos_customers as c', function ($custDueRData) {
                    $custDueRData->on('c.customer_no', 'sm.customer_id')
                        ->where('c.is_delete', 0);
                })
                ->addSelect(['products' => SalesDetails::select('product_name')
                        ->whereColumn('sales_bill_no', 'sm.sales_bill_no')
                        ->orderBy('sales_bill_no', 'ASC')
                        ->limit(1),
                ])
                ->addSelect(['paid_amount' => Collection::selectRaw('SUM(collection_amount) as  paid_amount')
                        ->whereColumn('sales_bill_no', 'sm.sales_bill_no')
                        ->groupBy('sales_bill_no')
                        ->limit(1),
                ])
                ->where(function ($custDueRData) use ($branchID, $employeeID, $customerID, $saleBillNo) {

                    if (!empty($branchId)) {
                        $custDueRData->where('sm.branch_id', $branchId);
                    }
                    if (!empty($employeeID)) {
                        $custDueRData->where('sm.employee_id', $employeeID);
                    }
                    if (!empty($customerID)) {
                        $custDueRData->where('sm.customer_id', $customerID);
                    }
                    if (!empty($saleBillNo)) {
                        $custDueRData->where('sm.sales_bill_no', $saleBillNo);
                    }
                })->get();

            // dd($custDueRData);

            $total_row = count($custDueRData);

            $ttl_qnt = $custDueRData->sum('product_quantity');
            $ttl_sales_amount = $custDueRData->sum('sales_amt');
            $ttl_service_charge = $custDueRData->sum('pro_fee');
            $ttl_gross_amount = $custDueRData->sum('gross_total');
            $ttl_paid_amount = $custDueRData->sum('paid_amount');
            $ttl_due_amount = 0;

            if (!empty($search)) {
                $totalFiltered = count($custDueRData);
            }

            $DataSet = array();

            foreach ($custDueRData as $row) {
                $TempSet = array();

                $TempSet = [
                    'sl' => $sl++,
                    'customer_no' => $row->customer_no,
                    'customer_name' => $row->customer_name,
                    'mobile' => $row->customer_mobile,
                    'cus_nid' => $row->customer_nid,
                    'spouse_name' => $row->spouse_name,
                    'bill_no' => $row->sales_bill_no,
                    'sales_date' => (new Datetime($row->sales_date))->format('d-m-Y'),
                    'product' => $row->products,
                    'quantity' => $row->product_quantity,
                    'sales_amount' => $row->sales_amt,
                    'processing_fee' => $row->pro_fee,
                    'gross_total' => $row->gross_total,
                    'paid_amount' => $row->paid_amount,
                    'due_amount' => abs($row->gross_total - $row->paid_amount),
                ];

                $ttl_due_amount += $TempSet['due_amount'];

                $DataSet[] = $TempSet;
            }

            $json_data = array(
                "draw" => intval($request->input('draw')),
                "recordsTotal" => intval($totalData),
                "recordsFiltered" => intval($totalFiltered),
                "data" => $DataSet,
                "totalRow" => $total_row,
                "ttl_qnt" => $ttl_qnt,
                "ttl_sales_amount" => number_format($ttl_sales_amount, 2),
                "ttl_service_charge" => number_format($ttl_service_charge, 2),
                "ttl_gross_amount" => number_format($ttl_gross_amount, 2),
                "ttl_paid_amount" => number_format($ttl_paid_amount, 2),
                "ttl_due_amount" => number_format($ttl_due_amount, 2),
            );

            echo json_encode($json_data);

        } else {
            return view('POS.Report.customerdue');
        }
    }

}
