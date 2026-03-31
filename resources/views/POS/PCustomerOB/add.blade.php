@extends('Layouts.erp_master_full_width')
@section('content')
@include('elements.pop.cust_due_sales_modal')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>

<div class="panel">
    <div class="panel-body">
        <form method="post" data-toggle="validator" novalidate="true" id="custDueSalesFormId">
        @csrf
        <div class="row">
            <div class="col-lg-8 offset-lg-3">
                <!-- Html View Load  -->
                {!! HTML::forCompanyFeild() !!}
            </div>
        </div>

        <div class="row offset-lg-1">
            <div class="col-lg-5">
                {{-- {!! HTML::forBranchFeild(true) !!} --}}
                @if(Common::getBranchId() == 1)
                <?php
                    $brachData = DB::table('gnl_branchs')
                                ->where([['is_approve', 1], ['is_delete', 0], ['is_active', 1]])
                                ->whereNotExists(function ($brachData){
                                    $brachData->select('branch_id')
                                            ->from('pos_ob_duesales_m')
                                            ->whereRaw('gnl_branchs.id = pos_ob_duesales_m.branch_id')
                                            ->where([['pos_ob_duesales_m.is_delete', 0], ['pos_ob_duesales_m.is_active', 1]]);
                                })->get();
                ?>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Branch</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <select class="form-control clsSelect2" required data-error="Please Select Branch" name="branch_id"  id="branch_id">
                                <option value="">Select Branch</option>
                                @foreach($brachData as $data)
                                <option value="{{ $data->id }}">{{ sprintf("%04d", $data->branch_code) . " - " . $data->branch_name  }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            @else
                <input type="hidden" name="branch_id" id="branch_id" value="{{ Common::getBranchId() }}">
            @endif
            </div>

            <div class="col-lg-5">
                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Opening Date</label>
                    <div class="col-lg-5 input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <input type="text" name="opening_date" id="opening_date"
                            class="form-control round" autocomplete="off" placeholder="DD-MM-YYYY" readonly>
                    </div>
                </div>
            </div>

            <div class="">
                <a class="btn btn-sm btn-primary btn-outline btn-round" href="javascript:void(0);"
                id="btn-add-cust-due-sales" data-toggle="modal" data-target="#modal-cust-due-sales">
                    <i class="icon wb-link" aria-hidden="true"></i>
                    <span class="hidden-sm-down">Add</span>
                </a>
            </div>

        </div>

        <div class="row">
            <table class="table table-hover table-striped table-bordered w-full text-center table-responsive" id="TblCustDueSales">
                <thead>
                    <th width="1%">Sl#</th>
                    <th width="10%">Customer Name</th>
                    <th width="5%">Customer Code</th>
                    <th width="18%">Product</th>
                    <th width="7%">Bill No</th>
                    <th width="7%">Sales Amount</th>
                    <th width="7%">Colletion Amount</th>
                    <th width="7%">Due Amount</th>
                    <th width="7%">Inst. Amount</th>
                    <th width="7%">Inst. Month</th>
                    <th width="7%">Inst. Type</th>
                    <th width="7%">Sale Date</th>
                    <th width="7%">Last Collection Date</th>
                    <th width="3%">Remove</th>
                </thead>
                <tbody>

                </tbody>
                <tfoot>
                    <td>
                        <input type="hidden" name="total_customer" id="total_customer" value="">
                    </td>
                    <td colspan="4"><strong>Total</strong></td>
                    <td>
                        <strong id="total_sales_amt">0</strong>
                        <input type="hidden" name="total_sales_amount" id="total_sales_amount" value="0">
                    </td>
                    <td>
                        <strong id="total_clln_amt">0</strong>
                        <input type="hidden" name="total_collection" id="total_collection" value="0">
                    </td>
                    <td>
                        <strong id="total_due_amt">0</strong>
                        <input type="hidden" name="total_due_amount" id="total_due_amount" value="0">
                    </td>
                </tfoot>
            </table>
            <!-- Row_Count is temporary variable for using row add and delete-->
            <input type="hidden" id="TotalRowID" value="0" />
        </div>
        <div class="row align-items-center">
            <div class="col-lg-12">
                <div class="form-group d-flex justify-content-center">
                    <div class="example example-buttons">
                        <a href="javascript:void(0)" onclick="goBack();"
                            class="btn btn-default btn-round d-print-none">Back</a>
                        <button type="submit" class="btn btn-primary btn-round"
                            id="submitBtnCustDueSale">Save</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    </div>
</div>

<script type="text/javascript">

$(document).ready(function() {

    $('.cls-select-2').select2({
        dropdownParent: $('#modal-cust-due-sales'),
        placeholder: "Select Please",
    });

    var sl = 0;

    $('#btn-add-cust-due-sales').click(function() {
        var branchID = $('#branch_id option:selected').val();
        if (branchID == '' ) {
            swal({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select branch',
            });
            return false;
        }
    });

    $('#subBtnCustDueSalesPOP').click(function(e) {

        var numberOfEmptyFields = $('#modal-cust-due-sales').find('input[required], select[required]').filter(function () {
            return $(this).val() === "";
        }).length;

        if (numberOfEmptyFields > 0) {
            e.preventDefault();
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Please fillup all Required fields!',
            });
            $('#modal-cust-due-sales').find(".form-control:invalid").first().focus(); // show all data error
            $('#modal-cust-due-sales').find(".form-control:invalid").focusout();  // Focus on first error

            return false;
        }


    	var TotalRowCount = Number($('#TotalRowID').val());
        TotalRowCount++;
        $('#TotalRowID').val(TotalRowCount);

    	var custId = $('#customer_name_0').val();
    	var custName = $('#customer_name_0').find("option:selected").attr('cname');
    	var custCode = $('#customer_name_0').find("option:selected").attr('ccode');
        var productId = $('#product_0').val();
        var productName = $('#product_0').find("option:selected").attr('pname');
        var salesBillNo = $('#sale_bill_no_0').val();
        var salesAmt = $('#sale_amt_0').val();
        var cllnAmt = $('#clln_amt_0').val();
        var dueAmt = $('#due_amt_0').val();
        var instAmt = $('#inst_amt_0').val();
        var instMonthId = $('#inst_month_0').val();
        var instMonth = $('#inst_month_0').find("option:selected").attr('instMonth');
        var instType = $('#inst_type_0').val();
        var salesDate = $('#sales_date_0').val();
        var lstCllnDate = $('#lst_clln_date_0').val();

        //find installment type from id
        var instTypeText;
        if (instType == 1) {
        	instTypeText = 'Month';
        }
        else{
        	instTypeText = 'Week';
        }

        {{-- Query for get all product --}}
        <?php $ProductList = Common::ViewTableOrder('pos_products',
                        ['is_delete' => 0],
                        ['id', 'product_name'],
                        ['product_name', 'ASC']);
        $BranchCode = Common::ViewTableOrder('gnl_branchs',
                        ['is_delete' => 0],
                        ['id', 'branch_code'],
                        ['branch_code', 'ASC']);
        ?>

        //find product name from select id
        let prodNameFromArr = [];
        for (var i = 0; i <= productId.length - 1; i++) {
        	@foreach($ProductList as $PData )
	        	if (productId[i] == {{ $PData->id }}) {
	        		prodNameFromArr.push("{{ $PData->product_name }}");
	        	}
	        @endforeach
        }

        var html = '<tr>';

        html += '<td>'+ ++sl +'</td>';

        html += '<td>'+ custName +
        			'<input type="hidden" name="customer_id_arr[]" value="'+ custId +'" >'+
        			'<input type="hidden" name="customer_name_arr[]" value="'+ custName +'" >'+
        		'</td>';

        html += '<td>'+ custCode +
        			'<input type="hidden" name="customer_no_arr[]" value="'+ custCode +'" >'+
        		'</td>';

        html += '<td>'+ prodNameFromArr +
        			'<input type="hidden" name="product_arr['+ custId +'][]" value="'+ productId +'" >'+
        		'</td>';


          html += '<td>' + salesBillNo +
                      '<input type="hidden" name="sale_bill_no_arr[]" value="'+ salesBillNo +'">'+
                  '</td>';


        html += '<td>'+ salesAmt +
        			'<input type="hidden" name="sale_amt_arr[]" value="'+ salesAmt +'" class="clsSalesAmt" >'+
        		'</td>';

        html += '<td>'+ cllnAmt +
        			'<input type="hidden" name="collection_amt_arr[]" value="'+ cllnAmt +'" class="clsCllnAmt" >'+
        		'</td>';

        html += '<td>'+ dueAmt +
        			'<input type="hidden" name="due_amt_arr[]" value="'+ dueAmt +'" class="clsDueAmt" >'+
        		'</td>';

        html += '<td>'+ instAmt +
        			'<input type="hidden" name="inst_amt_arr[]" value="'+ instAmt +'" >'+
        		'</td>';

        html += '<td>'+ instMonth +
        			'<input type="hidden" name="inst_month_arr[]" value="'+ instMonthId +'" >'+
        		'</td>';

        html += '<td>'+ instTypeText +
        			'<input type="hidden" name="inst_type_arr[]" value="'+ instType +'" >'+
        		'</td>';

        html += '<td>'+ salesDate +
        			'<input type="hidden" name="sale_date_arr[]" value="'+ salesDate +'" >'+
        		'</td>'

        html += '<td>'+ lstCllnDate +
        			'<input type="hidden" name="last_clln_date_arr[]" value="'+ lstCllnDate +'" >'+
        		'</td>';

        html += '<td width="5%">' +
            '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRow(this);">' +
            ' <i class="icon fa fa-times align-items-center"></i>' +
            '</a>' +
            '</td>';

        html += '</tr>';

        $('#TblCustDueSales').append(html);
        // $('#TblCustDueSales').find('tr:first').after(html);

        $('#modal-cust-due-sales').modal('toggle');
        $("#CDSModalFormId").trigger("reset");

        $('#customer_name_0').val('');
        $('#product_0').val('');
        $('#inst_month_0').val('');
        $('#inst_type_0').val('');

        $('#customer_name_0').trigger('change');
        $('#product_0').trigger('change');
        $('#inst_month_0').trigger('change');
        $('#inst_type_0').trigger('change');

        fnTotalSalesAmount();
	    fnTotalCllnAmount();
	    fnTotalDueAmount();
    });

    fnGetOpenningDate($('#branch_id').val());
    getBranchCustomer($('#branch_id').val());
});

/* Remove row  */
function btnRemoveRow(RemoveID) {

    $(RemoveID).closest('tr').remove();
    fnTotalSalesAmount();
    fnTotalCllnAmount();
    fnTotalDueAmount();
}

function fnTotalSalesAmount() {

    var totalSalesAmt = 0;
    $('.clsSalesAmt').each(function() {
        totalSalesAmt = Number(totalSalesAmt) + Number($(this).val());
    });
    $('#total_sales_amt').html(totalSalesAmt);
    //-------------------------- Total sales Amount
    $('#total_sales_amount').val(totalSalesAmt);
}

function fnTotalCllnAmount() {

    var totalCllnAmt = 0;
    $('.clsCllnAmt').each(function() {
        totalCllnAmt = Number(totalCllnAmt) + Number($(this).val());
    });
    $('#total_clln_amt').html(totalCllnAmt);
    //-------------------------- Total Collection Amount
    $('#total_collection').val(totalCllnAmt);
}

function fnTotalDueAmount() {

    var totalDueAmt = 0;
    $('.clsDueAmt').each(function() {
        totalDueAmt = Number(totalDueAmt) + Number($(this).val());
    });
    $('#total_due_amt').html(totalDueAmt);
    //-------------------------- Total Due Amount
    $('#total_due_amount').val(totalDueAmt);
}

function fnCalculateDue()
{
    var saleAmount = $('#sale_amt_0').val();
    var cllnAmount = $('#clln_amt_0').val();

    if (saleAmount != null && cllnAmount != null) {
        var dueAmount = Number(saleAmount) - Number(cllnAmount);
    }

    $('#due_amt_0').val(dueAmount);
}

$('#branch_id').change(function(){

    var branchId = $(this).val();
    fnGetOpenningDate(branchId);
    getBranchCustomer(branchId);

});

function fnGetOpenningDate(branchId)
{
    if (branchId != '') {

        $.ajax({
            method: 'get',
            url: '{{ url('ajaxGetBOpDate') }}',
            dataType: 'text',
            data: { branchID: branchId, moduleName: 'pos' },
            success: function(data){
                if (data) {
                    $('#opening_date').val(data);
                }
            }

        });
    }
}

function getBranchCustomer(branchId){

    $.ajax({
        method: 'get',
        url: '{{ url('/ajaxGetBranchCustomer') }}',
        dataType: 'text',
        data: { branchId: branchId },
        success: function(data){

            $('#customer_name_0').find('option')
                                .remove()
                                .end()
                                .append(data);

        }
    });
};


$('#submitBtnCustDueSale').on('click', function(event) {
    event.preventDefault();

    var rowCount = $('#TblCustDueSales tbody tr').length;

    if (rowCount > 0) {

        $('#total_customer').val(rowCount);
        $('#custDueSalesFormId').submit();

    }
    else{
        swal({
            icon: 'warning',
            title: 'Error',
            text: 'At least one customer must be add!',
        });
    }
});

$('form').submit(function (event) {
    $(this).find(':submit').attr('disabled', 'disabled');
});

</script>

@endsection
