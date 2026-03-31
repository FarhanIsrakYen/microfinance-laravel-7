<?php
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\HtmlService as HTML;

$StartDate = Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();

?>

<div class="w-full d-print-none">
    <div class="panel filterDiv">

        <div class="panel-heading">
            <h3 class="panel-title"></h3>
            <div class="panel-actions">
                <a class="panel-action icon wb-minus" data-toggle="panel-collapse" aria-expanded="true"
                    aria-hidden="true"></a>
            </div>
        </div>

        <div class="panel-body panel-search">

            <div class="row align-items-center pb-10">

                @if(isset($zone) && $zone)
                <div class="col-lg-2">
                    <label class="input-title">Zone</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="zone_id" id="zone_id" onchange="fnAjaxGetArea();">
                            <option value="">Select Option</option>
                            {{
                                $zoneData = Common::ViewTableOrder('gnl_zones',
                                [['is_delete', 0], ['is_active', 1]],
                                ['id', 'zone_name'],
                                ['id', 'ASC'])
                            }}
                            @foreach($zoneData as $row)
                            <option value="{{ $row->id}}">{{ $row->zone_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($area) && $area)
                <div class="col-lg-2">
                    <label class="input-title">Area</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="area_id" id="area_id"
                            onchange="fnAjaxGetBranch();">

                            <option value="">Select Option</option>
                            {{
                                $areaData = Common::ViewTableOrder('gnl_areas',
                                [['is_delete', 0], ['is_active', 1]],
                                ['id', 'area_name'],
                                ['id', 'ASC'])
                            }}
                            @foreach($areaData as $row)
                            <option value="{{ $row->id}}">{{ $row->area_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($project) && $project)
                <div class="col-lg-2">
                    <label class="input-title">Project</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="project_id" id="project_id">
                            {{
                                $projectData = Common::ViewTableOrder('gnl_projects',
                                [['is_delete', 0], ['is_active', 1],
                                ['project_name', '!=', '']],
                                ['id', 'project_name','project_code'],
                                ['project_name', 'ASC'])
                            }}
                            @foreach ($projectData as $Row)
                            <option value="{{ $Row->id }}">{{ sprintf("%03d",$Row->project_code) }} -
                                {{ $Row->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($projectType) && $projectType)
                <div class="col-lg-2">
                    <label class="input-title">Project Type</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="project_type_id" id="project_type_id">
                            {{
                                $projectTypeData = Common::ViewTableOrder('gnl_project_types',
                                [['is_delete', 0], ['is_active', 1],
                                ['project_type_name', '!=', '']],
                                ['id', 'project_type_name'],
                                ['project_type_name', 'ASC'])
                            }}
                            @foreach ($projectTypeData as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->project_type_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($branch) && $branch)
                {!! HTML::forBranchFeildSearch_new('all','branch_id','branch_id', 'Branch', null)!!}
                @endif

                @if(isset($branchFrom) && $branchFrom)
                {!! HTML::forBranchFeildSearch_new('all','branch_id','branch_id', 'Branch From', null)!!}
                @endif

                @if(isset($branchTo) && $branchTo)
                {!! HTML::forBranchFeildSearch_new('all','branch_id','branch_id', 'Branch To', null)!!}
                @endif

                @if(isset($reqFrom) && $reqFrom)
                {!! HTML::forBranchFeildSearch_new('all','branch_id','branch_id', 'Requisition From', null)!!}
                @endif

                @if(isset($deliveryPlace) && $deliveryPlace)
                {!! HTML::forBranchFeildSearch_new('all','branch_id','branch_id', 'Delivery Place', null)!!}
                @endif

                <!-- For Accounting Report -->
                @if(isset($branchAcc) && $branchAcc)
                {!! HTML::forBranchFeildSearch() !!}
                @endif

                @if(isset($vTypeReceipt) && $vTypeReceipt)
                <div class="col-lg-2">
                    <label class="input-title">Voucher Type</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="voucher_type" id="voucher_type">
                            <option value="1" selected>Cash</option>
                            <option value="2">All</option>
                            <option value="3">Non-Cash</option>
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($depthLevel) && $depthLevel)
                <div class="col-lg-2">
                    <label class="input-title">Depth Level</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="depth_level" id="depth_level">
                            <option value="">All</option>
                            {{
                                $levelData = DB::table('acc_account_ledger')
                                ->select('id','level')
                                ->where([['is_delete', 0],['is_active', 1]])
                                ->groupBy('level')
                                ->get()
                            }}
                            @foreach ($levelData as $Row)
                            <option value="{{ $Row->level }}">Level-{{ $Row->level }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($roundUp) && $roundUp)
                <div class="col-lg-2">
                    <label class="input-title">Round Up</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="round_up" id="round_up">
                            <option value="0">No</option>
                            <option value="1">Yes</option>
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($zeroBalance) && $zeroBalance)
                <div class="col-lg-2">
                    <label class="input-title">'0' Balance</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="zero_balance" id="zero_balance">
                            <option value="1">Yes</option>
                            <option value="2">No</option>
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($searchBy) && $searchBy)

                <div class="col-lg-2">
                    <label class="input-title">Search By</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" id="search_by" name="search_by">
                            <option value="">Select</option>
                            <option value="1">Fiscal Year</option>

                            @if(isset($currentYear) && $currentYear)
                            <option value="2">Current Year</option>
                            @endif
                            @if(isset($dateRange) && $dateRange)
                            <option value="3">Date Range</option>
                            @endif
                        </select>
                    </div>
                </div>


                <!-- Select box for fiscal Year [Option 1]-->
                <div class="col-lg-2" style="display: none" id="fyDiv">
                    <label class="input-title">Fiscal Year</label>
                    <div class="input-group">
                        <select class="form-control" name="fiscal_year" id="fiscal_year">
                            <option value="">Select</option>
                            <?php
                                $companyID = Common::getCompanyId();
                                $today     = (new DateTime())->format('Y-m-d');
                                $fiscalYearData = Common::ViewTableOrder('gnl_fiscal_year',
                                [['is_delete', 0],['is_active', 1],['company_id',$companyID]],
                                ['id', 'fy_name','fy_start_date','fy_end_date'],
                                ['fy_name', 'ASC']);

                                $current_fiscal_year = DB::table('gnl_fiscal_year')
                                                    ->select('id','fy_start_date','fy_name')
                                                    ->where('company_id',$companyID)
                                                    ->where('fy_start_date', '<=', $today)
                                                    ->where('fy_end_date', '>=', $today)
                                                    ->orderBy('id', 'DESC')
                                                    ->first();
                                $fy_start_date = "";
                                $fy_name = "";
                                if($current_fiscal_year){
                                    $fy_start_date = (new DateTime($current_fiscal_year->fy_start_date))->format('d-m-Y');
                                    $fy_name = $current_fiscal_year->fy_name;
                                }
                            ?>
                            @foreach ($fiscalYearData as $Row)
                            <option value="{{ $Row->id }}"
                                data-startdate="{{ (new DateTime($Row->fy_start_date))->format('d-m-Y') }}"
                                data-enddate="{{ (new DateTime($Row->fy_end_date))->format('d-m-Y') }}">
                                {{ $Row->fy_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <input type="hidden" name="start_date_cy" id="start_date_cy" value="{{ $fy_start_date }}"
                    data-fiscal="{{ $fy_name }}">

                <!-- End Date Datepicker for current year [Option 2]--->
                <div class="col-lg-2" style="display: none" id="endDateDivCY">
                    <label class="input-title">Date To</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="end_date_cy" name="end_date_cy"
                            placeholder="DD-MM-YYYY" autocomplete="off">
                    </div>
                </div>

                <!-- Start Date Datepicker for Date Range [Option 3]--->
                <div class="col-lg-2" style="display: none" id="startDateDivDR">
                    <label class="input-title">Start Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="start_date_dr"
                            name="start_date_dr" placeholder="DD-MM-YYYY" autocomplete="off" value="{{$StartDate}}">
                    </div>
                </div>

                <!-- End Date Datepicker for Date Range [Option 3]--->
                <div class="col-lg-2" style="display: none" id="endDateDivDR">
                    <label class="input-title">End Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="end_date_dr" name="end_date_dr"
                            placeholder="DD-MM-YYYY" autocomplete="off" value="{{$EndDate}}">
                    </div>
                </div>

                @endif

                @if(isset($group) && $group)
                <div class="col-lg-2">
                    <label class="input-title">Group</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="group_id" id="group_id" onchange="fnAjaxSelectBox('cat_id',this.value,
                                                '{{ base64_encode('pos_p_categories') }}',
                                                '{{base64_encode('prod_group_id')}}',
                                                '{{base64_encode('id,cat_name')}}',
                                                '{{url('/ajaxSelectBox')}}'
                                                );
                                        fnAjaxSelectBox('sub_cat_id', this.value,
                                                '{{base64_encode('pos_p_subcategories')}}',
                                                '{{base64_encode('prod_group_id')}}',
                                                '{{base64_encode('id,sub_cat_name')}}',
                                                '{{url('/ajaxSelectBox')}}'
                                                );">

                            <option value="">Select All</option>
                            {{
                                $PGroupList = Common::ViewTableOrder('pos_p_groups', 
                                [['is_delete', 0], ['is_active', 1]], 
                                ['id', 'group_name'], 
                                ['group_name', 'ASC'])
                            }}
                            @foreach($PGroupList as $Row)
                            <option value="{{ $Row->id}}">{{ $Row->group_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Category</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="cat_id" id="cat_id" onchange="fnAjaxSelectBox('sub_cat_id', this.value,
                                                '{{base64_encode('pos_p_subcategories')}}',
                                                '{{base64_encode('prod_cat_id')}}',
                                                '{{base64_encode('id,sub_cat_name')}}',
                                                '{{url('/ajaxSelectBox')}}');">
                            <option value="">Select All</option>
                            {{
                                $CategoryList = Common::ViewTableOrder('pos_p_categories', 
                                [['is_delete', 0], ['is_active', 1]], 
                                ['id', 'cat_name'], 
                                ['cat_name', 'ASC'])
                            }}
                            @foreach ($CategoryList as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->cat_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Sub Category</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="sub_cat_id" id="sub_cat_id">
                            <option value="">Select All</option>
                            {{
                                $SubCatList = Common::ViewTableOrder('pos_p_subcategories', 
                                [['is_delete', 0], ['is_active', 1]], 
                                ['id', 'sub_cat_name'], 
                                ['sub_cat_name', 'ASC'])
                            }}
                            @foreach ($SubCatList as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->sub_cat_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Brand</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="brand_id" id="brand_id">
                            <option value="">Select All</option>
                            {{
                                $BrandList = Common::ViewTableOrder('pos_p_brands', 
                                [['is_delete', 0], ['is_active', 1]], 
                                ['id', 'brand_name'], 
                                ['brand_name', 'ASC'])
                            }}
                            @foreach ($BrandList as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->brand_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($model) && $model)
                <div class="col-lg-2">
                    <label class="input-title">Model</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="model_id" id="model_id" onchange="fnAjaxSelectBox('product_id',
                                                        this.value,
                                                        '{{base64_encode('pos_products')}}',
                                                        '{{base64_encode('prod_model_id')}}',
                                                        '{{base64_encode('id,product_name')}}',
                                                        '{{url('/ajaxSelectBox')}}');">
                            <option value="">Select Option</option>
                            {{
                                $modelData = Common::ViewTableOrder('pos_p_models',
                                [['is_delete', 0], ['is_active', 1]],
                                ['id', 'model_name'],
                                ['id', 'ASC'])
                            }}
                            @foreach ($modelData as $model)
                            <option value="{{ $model->id }}">{{ $model->model_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($product) && $product)
                <div class="col-lg-2">
                    <label class="input-title">Product</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="product_id" id="product_id">
                            <option value="">Select All</option>
                            {{ //## Product Query
                                $productData = Common::ViewTableOrder('pos_products',
                                [['is_delete', 0], ['is_active', 1]],
                                ['id', 'product_name', 'prod_barcode'],
                                ['product_name', 'ASC'])
                            }}

                            @foreach ($productData as $row)
                            <option value="{{ $row->id }}">
                                {{ $row->product_name." (".$row->prod_barcode.")" }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($supplier) && $supplier)
                <div class="col-lg-2">
                    <label class="input-title">Supplier</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="supplier_id" id="supplier_id">
                            <option value="">Select All</option>
                            {{
                                $supplierList = Common::ViewTableOrder('pos_suppliers', 
                                [['is_delete', 0], ['is_active', 1], ['sup_name', '!=', '']], 
                                ['id', 'sup_name'], 
                                ['sup_name', 'ASC'])
                            }}
                            @foreach ($supplierList as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->sup_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($purchaseNo) && $purchaseNo)
                <div class="col-lg-2">
                    <label class="input-title">Purchase No</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="purchase_id" id="purchase_id">
                            <option value="">Select All</option>
                            {{ 
                                $PurchaseNoList = Common::ViewTableOrder('pos_purchases_m', 
                                [['is_delete', 0], ['is_active', 1], ['bill_no', '!=', '']], 
                                ['id', 'bill_no'], 
                                ['bill_no', 'ASC']) 
                            }}
                            @foreach ($PurchaseNoList as $Row)
                            <option value="{{ $Row->bill_no }}">{{ $Row->bill_no }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($orderNo) && $orderNo)
                <div class="col-lg-2">
                    <label class="input-title">Order No</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="order_id" id="order_id">
                            <option value="">Select All</option>
                            {{
                                $OrderList = Common::ViewTableOrder('pos_orders_m', 
                                [['is_delete', 0], ['is_active', 1], ['is_approve', 1]], 
                                ['order_no'], 
                                ['order_no', 'ASC'])
                            }}
                            @foreach ($OrderList as $Row)
                            <option value="{{ $Row->order_no }}">{{ $Row->order_no }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($invoiceNo) && $invoiceNo)
                <div class="col-lg-2">
                    <label class="input-title">Invoice No</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="invoice_id" id="invoice_id">
                            <option value="">Select All</option>
                            {{
                                $InvoiceNoList = Common::ViewTableOrder('pos_purchases_m', 
                                [['is_delete', 0], ['is_active', 1], ['invoice_no', '!=', '']], 
                                ['id', 'invoice_no'], 
                                ['invoice_no', 'ASC'])
                            }}
                            @foreach ($InvoiceNoList as $Row)
                            <option value="{{ $Row->invoice_no }}">{{ $Row->invoice_no }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($customer) && $customer)
                <div class="col-lg-2">
                    <label class="input-title">Customer</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="customer_id" id="customer_id">
                            <option value="">Select All</option>
                            {{
                                $customerData = Common::ViewTableOrderIn('pos_customers',
                                [['is_delete', 0], ['is_active', 1]],
                                ['branch_id', HRS::getUserAccesableBranchIds()],
                                ['id', 'customer_no', 'customer_name'],
                                ['customer_name', 'ASC'])
                            }}
                            @foreach ($customerData as $row)
                            <option value="{{ $row->customer_no }}">{{ $row->customer_name . ' - ' .$row->customer_no }}
                            </option>
                            @endforeach

                        </select>
                    </div>
                </div>
                @endif

                @if(isset($employee) && $employee)
                <div class="col-lg-2">
                    <label class="input-title">Sales By</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="employee_id" id="employee_id">
                            <option value="">Select All</option>
                            {{  $employeeData = Common::ViewTableOrderIn('hr_employees',
                                [['is_delete', 0], ['is_active', 1]],
                                ['branch_id', HRS::getUserAccesableBranchIds()],
                                ['id', 'employee_no', 'emp_name', 'emp_code'],
                                ['emp_code', 'ASC'])
                            }}
                            @foreach ($employeeData as $row)
                            <option value="{{ $row->employee_no }}">{{ $row->emp_name .' - ' . $row->employee_no }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($salesType) && $salesType)
                <div class="col-lg-2">
                    <label class="input-title">Sales Type</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="sales_type" id="sales_type">
                            <option value="">All</option>
                            <option value="1">Cash Sales</option>
                            <option value="2">Installment Sales</option>
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($salesBillNo) && $salesBillNo)
                <div class="col-lg-2">
                    <label class="input-title">Sales Bill No.</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="sales_bill_no" id="sales_bill_no">
                            <option value="">Select All</option>
                            {{
                                $saleMasterData = Common::ViewTableOrder('pos_sales_m',
                                [['is_delete', 0], ['is_active', 1]],
                                ['id', 'sales_bill_no'],
                                ['sales_bill_no', 'ASC'])
                            }}
                            @foreach ($saleMasterData as $Row)
                            <option value="{{ $Row->sales_bill_no }}">{{ $Row->sales_bill_no }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($issueRetBillNo) && $issueRetBillNo)
                <div class="col-lg-2">
                    <label class="input-title">Issue Return Bill No</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="issue_r_bill_no" id="issue_r_bill_no">
                            <option value="">Select All</option>
                            {{
                                $issueData = Common::ViewTableOrder('pos_issues_r_m',
                                [['is_delete', 0], ['is_active', 1]],
                                ['id', 'bill_no'],
                                ['bill_no', 'ASC'])
                            }}
                            @foreach ($issueData as $row)
                            <option value="{{ $row->bill_no }}">{{ $row->bill_no}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($stock) && $stock)
                <div class="col-lg-2">
                    <label class="input-title">Stock</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="stockSearch" id="stockSearch">
                            <option value="0">With Zero</option>
                            <option value="1">Without Zero</option>
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($ledger) && $ledger)
                <div class="col-lg-2">
                    <label class="input-title">Ledger</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="ledger_id" id="ledger_id">
                            <option value="">Select</option>
                            {{
                                $ledgerData = Common::ViewTableOrder('acc_account_ledger',
                                [['is_delete', 0], ['is_active', 1],['is_group_head', 0]],
                                ['id', 'name','code'],
                                ['name', 'ASC'])
                            }}
                            @foreach ($ledgerData as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->code .'-'. $Row->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($ledgerCash) && $ledgerCash)
                <div class="col-lg-2">
                    <label class="input-title">Ledger</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="ledger_cash" id="ledger_cash">
                            <option value="">All Cash</option>
                            {{
                                $ledgerCashBook = Common::ViewTableOrder('acc_account_ledger',
                                [['is_delete', 0], ['is_active', 1],['is_group_head', 0], ['acc_type_id',4]],
                                ['id', 'name','code'],
                                ['name', 'ASC'])
                            }}
                            @foreach ($ledgerCashBook as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->code .'-'. $Row->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($ledgerBank) && $ledgerBank)
                <div class="col-lg-2">
                    <label class="input-title">Ledger</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="ledger_bank" id="ledger_bank">
                            <option value="">All Bank</option>
                            {{
                                $ledgerBankBook = Common::ViewTableOrder('acc_account_ledger',
                                [['is_delete', 0], ['is_active', 1],['is_group_head', 0], ['acc_type_id',5]],
                                ['id', 'name','code'],
                                ['name', 'ASC'])
                            }}
                            @foreach ($ledgerBankBook as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->code .'-'. $Row->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($voucherType) && $voucherType)
                <div class="col-lg-2">
                    <label class="input-title">Voucher Type</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="voucher_type_id" id="voucher_type_id">
                            <option value="">All</option>
                            {{
                                $voucherTypeData = Common::ViewTableOrder('acc_voucher_type',
                                [['is_delete', 0],['is_active', 1]],
                                ['id', 'short_name'],
                                ['short_name', 'ASC'])
                            }}
                            @foreach ($voucherTypeData as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->short_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                @if(isset($space) && $space)
                <div class="col-lg-2">
                </div>
                @endif

                @if(isset($startDate) && $startDate)
                <div class="col-lg-2">
                    <label class="input-title">Start Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="start_date" name="StartDate"
                            placeholder="DD-MM-YYYY" value="{{$StartDate}}">
                    </div>
                </div>
                @endif

                @if(isset($endDate) && $endDate)
                <div class="col-lg-2">
                    <label class="input-title">End Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="end_date" name="EndDate"
                            placeholder="DD-MM-YYYY" value="{{$EndDate}}">
                    </div>
                </div>
                @endif

                @if(isset($monthYear) && $monthYear)
                <div class="col-lg-2">
                    <label class="input-title">Month</label>
                    <div class="input-group">
                        <!-- monthYearPicker -->
                        <input type="text" class="form-control" id="month_year" name="month_year" placeholder="MM-YYYY"
                            autocomplete="off">
                    </div>
                </div>
                @endif

            </div>

            <div class="row text-center pt-10 pb-10">
                <div class="col-lg-12">
                    <a href="javascript:void(0);" id="searchButton" class="btn btn-primary btn-round text-uppercase"
                        style="width: 10%; font-size:16px;">
                        <i class="fa fa-search" aria-hidden="true"></i>&nbsp;Filter
                    </a>

                    {{-- @if(isset($refresh) && $refresh) --}}
                    <a href="javascript:void(0);" id="refreshButton" class="btn btn-danger btn-round text-uppercase"
                        style="width: 10%; font-size:16px;">
                        <i class="fa fa-refresh" aria-hidden="true"></i>&nbsp;Refresh
                    </a>
                    {{-- @endif --}}
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    // $(document).ready(function () {
    //     $('#searchButton').on('click', function (e) {
    //         $(".wb-minus").trigger('click');
    //     });

    // });

    $(document).ready(function () {
        $('#refreshButton').on('click', function (e) {
            window.location.href = window.location.href.split('#')[0];
        });

        $('#searchButton').click(function () {
            $(".wb-minus").trigger('click');

            // // // Loader Active
            fnLoading(true);

            $("#filterFormId").submit();
        });
    });

    function fnAjaxGetArea() {

        zoneId = $('#zone_id').val();

        if (zoneId != null) {

            $.ajax({
                method: "GET",
                url: "{{ url('ajaxGetArea') }}",
                dataType: "text",
                data: {
                    zoneId: zoneId,
                },
                success: function (data) {

                    if (data) {

                        $('#area_id')
                            .empty()
                            .html(data);
                        // .trigger('change');
                    }
                }
            });
        }
    }

    function fnAjaxGetBranch() {

        areaId = $('#area_id').val();

        if (areaId != null) {

            $.ajax({
                method: "GET",
                url: "{{ url('ajaxGetBranch') }}",
                dataType: "text",
                data: {
                    areaId: areaId,
                },
                success: function (data) {

                    if (data) {

                        $('#branch_id')
                            .empty()
                            .html(data);
                        // .trigger('change');
                    }
                }
            });
        }
    }

</script>
