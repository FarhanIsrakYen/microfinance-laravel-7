@extends('Layouts.erp_master_full_width')
@section('content')
<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>

<!-- Page -->
<?php
use App\Services\CommonService as Common;
$branchId = Auth::user()->branch_id;
$branchInfo = Common::ViewTableFirst('gnl_branchs',
    [['is_delete', 0], ['is_active', 1],
        ['id', Auth::user()->branch_id]],
    ['id', 'branch_name']);
$groupInfo = Common::ViewTableFirst('gnl_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name']);
?>

<div class="panel">
    <div class="panel-body">
        <div class="row align-items-center pb-10 d-print-none">
            @if($branchId == 1)
            <div class="col-lg-2">
                <label class="input-title">Branch</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="branch_id" id="branch_id"
                    onchange="fnAjaxSelectBoxForFO(
                                            'field_officer',
                                            this.value,
                                '',
                                '',
                                '{{base64_encode('id,emp_name')}}',
                                '{{url('/ajaxSelectBoxForFieldOfficer')}}'
                                        );">
                        <option value="">Select</option>
                        @foreach ($branchData as $row)
                        <option value="{{ $row->id }}">
                            {{ sprintf("%04d", $row->branch_code) . "-" . $row->branch_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Field Officer</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="field_officer" id="field_officer">
                        <option value="">All</option>
                    </select>
                </div>
            </div>

            @else
            <div class="col-lg-2">
                <label class="input-title">Field Officer</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="field_officer" id="field_officer"
                    >
                        <option value="">All</option>
                        @foreach ($fieldOfficerData as $row)
                        <option value="{{ $row->id }}">{{  $row->emp_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            @endif

            <div class="col-lg-2">
                <label class="input-title">Funding Organization</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="funding_org" id="funding_org"
                    onchange="fnAjaxSelectBox(
                                            'loan_product',
                                            this.value,
                                '{{base64_encode('mfn_loan_products')}}',
                                '{{base64_encode('fundingOrgId')}}',
                                '{{base64_encode('id,shortName')}}',
                                '{{url('/ajaxSelectBox')}}',
                                '{{null}}',
                                '{{'isActiveOff'}}'
                                        );">
                        <option value="">All</option>
                        @foreach ($fundingOrgData as $row)
                        <option value="{{ $row->id }}">{{  $row->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Category</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="loan_prod_cat" id="loan_prod_cat"
                    onchange="fnAjaxSelectBox(
                                            'loan_product',
                                            this.value,
                                '{{base64_encode('mfn_loan_products')}}',
                                '{{base64_encode('productCategoryId')}}',
                                '{{base64_encode('id,shortName')}}',
                                '{{url('/ajaxSelectBox')}}',
                                '{{null}}',
                                '{{'isActiveOff'}}'
                                        );">
                        <option value="">All</option>
                        @foreach ($loanProdCategoryData as $row)
                        <option value="{{ $row->id }}">{{  $row->shortName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Loan Product</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="loan_product" id="loan_product">
                        <option value="">All</option>
                        @foreach ($loanProductData as $row)
                        <option value="{{ $row->id }}">{{  $row->shortName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Product/Category Wise</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="prod_cat_wise" id="prod_cat_wise">
                        <option value="1">Product Wise</option>
                        <option value="2">Category Wise</option>
                    </select>
                </div>
            </div>

        </div>
        <div class="row d-print-none">
            <div class="col-lg-2">
                <label class="input-title">Service Charge</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="service_charge" id="service_charge">
                        <option value="1">With Service Charge</option>
                        <option value="2">Without Service Charge</option>
                    </select>
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Date To</label>
                <div class="input-group">
                    <input type="text" class="form-control datepicker-custom" id="end_date" name="end_date"
                        placeholder="DD-MM-YYYY" value="" autocomplete="off">
                </div>
            </div>

            <div class="col-lg-2 ml-auto text-center">
                <br>
                <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                    id="searchButton">Search</a>
            </div>
        </div>
       
        <div class="row d-print-none text-right" style="display: none" id="printPDF">
            <div class="col-lg-12">
                <a href="javascript:void(0)" onClick="window.print();" class="btnPrint mr-2">
                    <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                </a>
                <a href="javascript:void(0)" onclick="fnDownloadPDF();">
                    <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
                </a>
                <a href="javascript:void(0)" onclick="fnDownloadExcel();">
                    <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                </a>
            </div>
        </div>
        <div class="row text-center d-none d-print-block">
            <div class="col-lg-12" style="color:#000;">
                <strong>{{ $groupInfo->group_name }}</strong><br>
                <strong>{{ $branchInfo->branch_name }}</strong><br>
                <span>All Collection Report</span><br>
            </div>
        </div>
        <div class="row" id="ttlRow" style="display: none">
            <div class="col-xl-12 col-lg-12 col-sm-12 col-md-12 col-12 text-right">
                <span><b>Printed Date:</b> {{ (new Datetime())->format('d-m-Y') }} </span>
            </div>
        </div>
        <div class="row" id="table1" style="display: none">
            <div class="col-lg-12">
                <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                    <thead class="text-center">
                        <tr>
                            <th width="5%" rowspan="2">SL</th>
                            <th rowspan="2">Samity</th>
                            <th rowspan="2">Member's Code</th>
                            <th rowspan="2">Member's Name</th>
                            <th rowspan="2">Component</th>
                            <th rowspan="2">Loan Code</th>
                            <th rowspan="2">Loan Disburse Date</th>
                            <th rowspan="2">Loan Amount</th>
                            <th rowspan="2">Outstanding</th>
                            <th rowspan="2">Advance Amount</th>
                        </tr>
                        
                    </thead>
                    <tbody id="clsDataTable"></tbody>
                    <tfoot>
                        <tr>
                            <td colspan="7" class="text-right"><b>Total:</b></td>
                            <td class="text-right"><b id="ttl_disburse_amount">0.00</b></td>
                            <td class="text-right"><b id="ttl_loan_amount">0.00</b></td>
                            <td class="text-right"><b id="ttl_advance_amount">0.00</b></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="row mt-4" id="table2" style="display: none">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <h6  id="prodCatWise">Product Wise</h6>
                    <table class="table w-full table-hover table-bordered table-striped pcTable">
                        <thead class="text-center">
                            <tr>
                                <th width="5%" rowspan="2">SL</th>
                                <th rowspan="2">Product</th>
                                <th rowspan="2">Disburse Amount</th>
                                <th rowspan="2">Present Loan Amount</th>
                                <th rowspan="2">Advance Amount</th>
                            </tr>
                            
                        </thead>
                        <tbody  id="pcDataTable">
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="2" class="text-right"><b>Total:</b></td>
                                <td class="text-right"><b id="ttl_disburse">0.00</b></td>
                                <td class="text-right"><b id="ttl_loan">0.00</b></td>
                                <td class="text-right"><b id="ttl_advance">0.00</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->

<script>

function number_format(x) {
    return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
}

function ajaxDataLoad(end_date = null, branch_id = null, field_officer = null, funding_org = null, loan_prod_cat = null, loan_product = null, service_charge = null, prod_cat_wise = null, advance_type = null) {
    $("#clsDataTable").empty();

    $('#ttl_disburse_amount').html('');
    $('#ttl_loan_amount').html('');
    $('#ttl_advance_amount').html('');
    $('#ttl_sav_balance').html('');
    $('#ttl_member_claim').html('');
    $('#ttl_org_claim').html('');
    $.ajax({
        method: "GET",
        dataType: "json",
        url: "{{route('advanceRegisterDataTable')}}/loadReportData",
        data: {
                _token: "{{ csrf_token() }}",
                endDate: end_date,
                branchId: branch_id,
                fieldOfficer: field_officer,
                fundingOrg: funding_org,
                loanProdCategory : loan_prod_cat,
                loanProduct :loan_product,
                serviceCharge :service_charge,
                prodCatWise :prod_cat_wise,
            },
        success: function (data) {
            var html = '';
            var sl = parseInt(0);
            $.each(data.data, function( key, obj) {
                sl = parseInt(sl) + 1;

                html += '<tr>';
                html += '<td class="text-center" rowspan="' + obj.length +' ">' + sl +'</td>';
                html += '<td rowspan="'+ obj.length +' ">'+ key +'</td>';
                html += '<td>'+obj[0].memberCode+'</td>';
                html += '<td>'+obj[0].member+'</td>';
                html += '<td class="text-center">'+obj[0].component+'</td>';
                html += '<td class="text-center">'+obj[0].loanCode+'</td>';
                html += '<td class="text-center">'+obj[0].disbursementDate+'</td>';
                html += '<td class="text-right">'+ parseFloat(obj[0].disburseAmount).toFixed(2)+'</td>';
                html += '<td class="text-right">'+ parseFloat(obj[0].loanAmount).toFixed(2) +'</td>';
                html += '<td class="text-right">'+ parseFloat(obj[0].advanceAmount).toFixed(2) +'</td>';
                html += '</tr>';

                for(var i = 1; i < obj.length; i++) {
                    html += '<tr>';
                    html += '<td>'+obj[i].memberCode+'</td>';
                    html += '<td>'+obj[i].member+'</td>';
                    html += '<td class="text-center">'+obj[i].component+'</td>';
                    html += '<td class="text-center">'+obj[i].loanCode+'</td>';
                    html += '<td class="text-center">'+obj[i].disbursementDate+'</td>';
                    html += '<td class="text-right">'+ parseFloat(obj[i].disburseAmount).toFixed(2) +'</td>';
                    html += '<td class="text-right">'+ parseFloat(obj[i].loanAmount).toFixed(2) +'</td>';
                    html += '<td class="text-right">'+ parseFloat(obj[i].advanceAmount).toFixed(2) +'</td>';
                    html += '</tr>';
                }
                
            });

            $("#clsDataTable").append(html);
            $('#ttl_disburse_amount').html(data.ttl_disburse_amount);
            $('#ttl_loan_amount').html(data.ttl_loan_amount);
            $('#ttl_advance_amount').html(data.ttl_advance_amount);

        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert('No Match Found');
        }
    });  
}

function ajaxProdCatDataLoad(end_date = null, branch_id = null, field_officer = null, funding_org =null, loan_prod_cat = null, loan_product = null, service_charge = null, prod_cat_wise = null, advance_type = null) {
    $("#pcDataTable").empty();

    $('#ttl_disburse').html('');
    $('#ttl_loan').html('');
    $('#ttl_advance').html('');

    $.ajax({
        method: "GET",
        dataType: "json",
        url: "{{route('advanceRegisterDataTable')}}/loadReportData",
        data: {endDate: end_date,
                branchId: branch_id,
                fieldOfficer: field_officer,
                fundingOrg: funding_org,
                loanProdCategory : loan_prod_cat,
                loanProduct : loan_product,
                serviceCharge : service_charge,
                prodCatWise : prod_cat_wise,
            },
        success: function (data) {
            console.log(data);
            var html = '';
            $.each(data.prodCatData, function( key, obj) {
                html += '<tr>';
                html += '<td>'+obj.sL+'</td>';
                html += '<td>'+obj.product+'</td>';
                html += '<td class="text-right">'+obj.disburse+'</td>';
                html += '<td class="text-right">'+obj.loan+'</td>';
                html += '<td class="text-right">'+obj.advance+'</td>';
                html += '</tr>'; 
            });

            $("#pcDataTable").append(html);
            $('#ttl_disburse').html(data.ttl_disburse);
            $('#ttl_loan').html(data.ttl_loan);
            $('#ttl_advance').html(data.ttl_advance);
        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert('No Match Found');
        }
    });  
}

$(document).ready(function() {

    $('.clsSelect2').css("width","100%");

    var branch_id = '';
    $('#branch_id').change(function() {
        branch_id = $(this).val();
    });

    var end_date = '';
    $('#end_date').change(function() {
        end_date = $(this).val();
    });

    $('#searchButton').click(function() {
        if ('<?php echo($branchId); ?>' == 1) {
            if (branch_id == '') {
                swal({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select A branch',
                });
                return false;
            }
        }
        if ('<?php echo($branchId); ?>' != 1) {
           branch_id =  '<?php echo($branchId); ?>';
        }

        if (end_date == '') {
            swal({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select Date',
            });
            return false;
        }
        
        var field_officer = $('#field_officer').val();
        var funding_org = $('#funding_org').val();
        var loan_prod_cat = $('#loan_prod_cat').val();
        var loan_product = $('#loan_product').val();
        var service_charge = $('#service_charge').val();
        var prod_cat_wise = $('#prod_cat_wise').val();
        $('#prod_cat_wise').change(function() {
            prod_cat_wise = $(this).val();
            if (prod_cat_wise == 1) {
                $("#prodCatWise").text('Product Wise');
            }
            else {
                $("#prodCatWise").text('Category Wise');
            }
        });
        $('#end_date_txt').html(end_date);
        ajaxDataLoad(end_date, branch_id, field_officer, funding_org, loan_prod_cat, loan_product, service_charge, prod_cat_wise );

        ajaxProdCatDataLoad(end_date, branch_id, field_officer, funding_org, loan_prod_cat, loan_product, service_charge, prod_cat_wise);

        $('#table1,#table2,#ttlRow,#printPDF').show('slow');
    });
    
});

function fnAjaxSelectBoxForFO(FeildID = null, FeildVal = null, TableName = null, WhereColumn = null, SelectColumn = null, URL = null, SelectedVal = null) {

        if (FeildID != null && FeildVal != null && TableName != null && WhereColumn != null && SelectColumn != null) {
            $.ajax({
                method: "GET",
                url: URL,
                dataType: "text",
                data: {FeildVal: FeildVal, TableName: TableName, WhereColumn: WhereColumn, SelectColumn: SelectColumn, SelectedVal: SelectedVal},
                success: function (data) {
                    if (data) {
                        $('#' + FeildID)
                                .empty()
                                .html(data);
                                // .trigger('change');
                                //                    .selectpicker('refresh');
                    }
                }
            });
        }
    }

function fnDownloadPDF() {
    $('.clsDataTable,.pcTable').tableExport({
        type: 'pdf',
        fileName: 'Advance Register report',
        jspdf: {
            orientation: 'l',
            format: 'a4',
            margins: {
                left: 10,
                right: 10,
                top: 20,
                bottom: 20
            },
            autotable: {
                styles: {
                    overflow: 'linebreak'
                },
                tableWidth: 'auto'
            }
        }
    });
}

function fnDownloadExcel() {
    $('.clsDataTable,.pcTable').tableExport({
        type: 'excel',
        fileName: 'Advance Register report',
    });
}
</script>
@endsection