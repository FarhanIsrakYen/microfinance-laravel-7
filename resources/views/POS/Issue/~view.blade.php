@extends('Layouts.erp_master')
@section('content')
<!-- Page -->
<div class="page">
    <div class="page-header">
        <label class="">New Issue Entry</label>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Transaction</a></li>
            <li class="breadcrumb-item"><a href="index.php?PageId=IssueList">Issue</a></li>
            <li class="breadcrumb-item active">Entry</li>
        </ol>
    </div>

    <div class="page-content">
        <form action="{{url('/pos/issue/update/'.$Issuem->id)}}" method="POST" data-toggle="validator" novalidate="true"
            id="form_id">
            @csrf
            <div class="panel">
                <div class="panel-body">


                    <div class="row ">
                        <div class="col-lg-12 offset-lg-1">
                            <!-- Html View Load  -->
                            <input type="hidden" id="issue_date" name="issue_date" value="{{ date('d-m-Y') }}" readonly>

                            <div class="form-row align-items-center ">

                                <label class="col-lg-3 input-title RequiredStar">Company</label>
                                <div class="col-lg-5">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <input type="text" class="form-control round" readonly
                                                value="{{$Issuem->company['comp_name']}}">
                                        </div>
                                        <div class="help-block with-errors is-invalid"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!--Form Left-->
                                <div class="col-lg-6">
                                    <div class="form-row align-items-center ">

                                        <label class="col-lg-3 input-title RequiredStar">Branch From</label>

                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round" readonly
                                                        value="{{ sprintf("%04d", 0).'-Head Office'}}">

                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-row align-items-center ">
                                        <label class="col-lg-3 input-title RequiredStar">Branch To</label>

                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round" readonly
                                                        value="{{$Issuem->branch['branch_name']}}">

                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center ">

                                        <label class="col-lg-3 input-title">Branch Order No</label>
                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round"
                                                        placeholder="Enter Issue Order No" name="branch_order_no"
                                                        readonly id="branch_order_no"
                                                        value="{{$Issuem->branch_order_no}}">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                            </div>
                                        </div>
                                    </div>



                                    <!--    end left -->
                                </div>

                                <!--Form Right-->
                                <div class="col-lg-6">

                                    <div class="form-row align-items-center">

                                        <label class="col-lg-3 input-title">Bill No</label>

                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round"
                                                        placeholder="Enter Issue No." name="bill_no" id="bill_no"
                                                        readonly value="{{$Issuem->bill_no}}" required readonly>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-row align-items-center ">

                                        <label class="col-lg-3 input-title">Order No</label>
                                        <div class="col-lg-5">
                                            <div class="form-group">
                                                <div class="input-group ">
                                                    <input type="text" class="form-control round" name="order_no"
                                                        id="order_no" placeholder="Enter Order No." readonly
                                                        value="{{$Issuem->order_no}}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!--  end Right    -->
                                </div>


                            </div>



                        </div>
                    </div>

                </div>
            </div>

            <div class="panel">
                <div class="panel-body">



                    <table class="table table-hover table-striped table-bordered w-full text-center my-custom-scrollbar"
                        id="purchaseTable">
                        <thead>
                            <tr>
                                <!-- <th width="40%">Barcode</th> -->
                                <th width="35%" class="RequiredStar">Product Name</th>
                                <th width="10%" class="RequiredStar">Quantity</th>
                                <!--<th width="10%" class="RequiredStar">Order Quantity</th>-->
                                <!--<th width="10%" class="RequiredStar">Receive Quantity</th>-->
                                <th width="15%">Cost Price</th>
                                <th width="15%">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $i = 0;
                            $TableID = "purchaseTable";

                            $ColumnName = "product_id_arr[]&sys_barcode_arr[]&product_name_arr[]&product_quantity_arr[]&ordered_quantity_arr[]&received_quantity_arr[]&unit_cost_price_arr[]&total_cost_price_arr[]";

                            $ColumnID = "product_id_&sys_barcode_&product_name_&product_quantity_&ordered_quantity_&received_quantity_&unit_cost_price_&total_cost_price_&deleteRow_";
                            // 'company_id'=> $CompanyID,
                            $ProductList = Common::ViewTableOrder('pos_products',
                                            ['is_delete' => 0],
                                            ['id', 'product_name', 'cost_price', 'sale_price', 'prod_vat', 'sys_barcode', 'prod_barcode'],
                                            ['product_name', 'ASC']);
                        ?>


                            @if(count($Issued) > 0)
                            @foreach($Issued as $Data)
                            <?php $i++; ?>
                            <tr>
                                <td class="input-group barcodeWidth">
                                    <input type="hidden" name="product_id_arr[]" value="{{ $Data->product_id }}">

                                    @foreach($ProductList as $ProductInfo)
                                    @if($ProductInfo->id == $Data->product_id)
                                    <input type="text" class="form-control round"
                                        value="{{ $ProductInfo->product_name . ' (' . $ProductInfo->sys_barcode . ')' }} "
                                        readonly>
                                    @endif
                                    @endforeach
                                </td>

                                <td>
                                    <!-- Input for System barcode  -->
                                    <!-- <input type="hidden" name="sys_barcode_arr[]" id="sys_barcode_0"> -->

                                    <!-- Input for Product Name  -->
                                    <!--  <input type="hidden"  name="product_name_arr[]" id="product_name_0">  -->

                                    <input type="number" name="product_quantity_arr[]" id="product_quantity_{{ $i }}"
                                        class="form-control round clsQuantity" placeholder="Enter Quantity"
                                        value="{{ $Data->product_quantity }}"
                                        onkeyup="fnTotalQuantity(); fnTtlProductPrice({{$i}});" required min="1"
                                        readonly>
                                </td>

                                <!-- <td>
                                            <input type="hidden" name="ordered_quantity_arr[]" id="ordered_quantity_0" class="form-control round clsOrderQuantity"
                                            value="0" onkeyup="fnTotalOrderQuantity();">
                                        </td> -->
                                <!-- <td>
                                            <input type="hidden" name="received_quantity_arr[]" id="received_quantity_0" class="form-control round clsRcvQuantity"
                                            value="0" onkeyup="fnTotalReceiveQuantity();">
                                        </td> -->
                                <td>
                                    <input type="number" name="unit_cost_price_arr[]" id="unit_cost_price_{{ $i }}"
                                        class="form-control round" value="{{ $Data->unit_cost_price }}" required min="1"
                                        readonly>
                                </td>

                                <td>
                                    <input type="number" name="total_cost_price_arr[]" id="total_cost_price_{{ $i }}"
                                        class="form-control round ttlAmountCls" value="{{ $Data->total_cost_amount }}"
                                        min="1" readonly>
                                </td>


                            </tr>
                            @endforeach
                            @endif
                        </tbody>
                        <tfoot>
                            <tr>
                                <td width="45%">
                                    <h5>Total Quantity</h5>
                                    <input type="hidden" name="total_quantity" id="total_quantity" value="0" min="1">
                                    <!-- <input type="hidden" name="total_ordered_quantity" id="total_ordered_quantity" value="0"> -->
                                    <!-- <input type="hidden" name="total_received_quantity" id="total_received_quantity" value="0"> -->
                                </td>
                                <td width="10%" id="tdTotalQuantity" style="font-weight: bold;">0</td>

                                <td width="20%">
                                    <h5>Total Amount</h5>
                                    <input type="hidden" name="total_amount" id="total_amount" value="0" min="1">
                                </td>
                                <td width="25%" id="tdTotalAmount" style="font-weight: bold;">0</td>
                            </tr>
                        </tfoot>
                    </table>



                    <div class="row ">
                        <div class="col-lg-12 offset-lg-3">

                            <div class="form-row align-items-center ">

                                <div class="col-lg-6">
                                    <div class="form-group d-flex justify-content-center">
                                        <div class="example example-buttons">
                                            <a href="{{url('pos/issue')}}" class="btn btn-default btn-round">Close</a>
                                            {{-- <button type="submit" class="btn btn-primary btn-round" id="submitButton">Save</button> --}}
                                            <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

</div>

<!-- End Page -->
<script>
$(document).ready(function() {
    $('.clsProductSelect').select2();
    $('#company_id').change(function() {
        fnAjaxSelectBox(
            'prod_group_id',
            this.value,
            '{{base64_encode("pos_p_groups")}}',
            '{{base64_encode("company_id")}}',
            '{{base64_encode("id,group_name")}}',
            '{{url("/ajaxSelectBox")}}',
        );

    });

    fnTotalQuantity();
    fnTotalAmount();
});

// product load function
function fnProductLoad() {
    var CompanyID = $('#company_id').val();
    var SupplierID = $('#supplier_id').val();
    var GroupID = $('#prod_group_id').val();
    var CategoryID = $('#prod_cat_id').val();
    var SubCatID = $('#prod_sub_cat_id').val();
    var ModelID = $('#prod_model_id').val();
    var firstRowFirstColId = $('#purchaseTable tbody tr td:first-child select').attr('id');

    console.log(firstRowFirstColId);

    $.ajax({
        method: "GET",
        url: "{{url('/ajaxProductLPurchase')}}",
        dataType: "text",
        data: {
            ModelID: ModelID,
            GroupID: GroupID,
            CategoryID: CategoryID,
            SubCatID: SubCatID,
            CompanyID: CompanyID,
            SupplierID: SupplierID
        },
        success: function(data) {
            if (data) {
                // $('#product_id_0').html('');
                // $('#'+firstRowFirstColId).append(data);
                // $('#'+firstRowFirstColId).trigger('change');

                $('#product_id_0')
                    .find('option')
                    .remove()
                    .end()
                    .append(data);

            }
        }
    });
}
// END PORODUCT LOAD 

// start first column on change 
$('#product_id_0').change(function() {

    if ($(this).val() != '') {
        $('#product_quantity_0').prop('readonly', false);
        var selProdCostPrice = $(this).find("option:selected").attr('pcprice');
        $('#unit_cost_price_0').val(selProdCostPrice);
        fnTtlProductPrice(0);
    } else {
        $('#product_quantity_0').prop('readonly', true);
        $('#unit_cost_price_0').val(0);
        //$('#product_id_0').val('');
    }
});
// end first column on change 



/* Add row Start */
function btnAddNewRow(TableID, ColumnNameS, ColumnIDS, TotalRowID) {
    var ColumnName = ColumnNameS.split("&");
    var ColumnID = ColumnIDS.split("&");
    /*
     * ColumnID[0] = this is ID for input feild Product_id_0
     * ColumnID[3] = this is ID for input feild product_quantity_0
     * ColumnID[6] = this is ID for input feild unit_cost_price_0
     * ColumnID[7] = this is ID for input feild total_cost_price_0
     */
    if ($('#' + ColumnID[0] + 0).val() != '' && $('#product_quantity_0').val() > 0) {

        // var RowCount = $('#' + RowCountID).val();
        // RowCount++;
        // $('#' + RowCountID).val(RowCount);

        // console.log(ProductIDArr);

        // $('#' + ColumnID[0] + 0).val()
        //             var fruits = ["Banana", "Orange", "Apple", "Mango"];
        // var n = fruits.includes("Orangess");
        // if(){

        // }

        var TotalRowCount = $('#' + TotalRowID).val();
        TotalRowCount++;
        $('#' + TotalRowID).val(TotalRowCount);
        var ProductID = $('#' + ColumnID[0] + 0).val();
        var ProductName = $('#' + ColumnID[0] + 0).find("option:selected").attr('pname');
        var ProductBarcode = $('#' + ColumnID[0] + 0).find("option:selected").attr('pbarcode');
        var ProductSysBarcode = $('#' + ColumnID[0] + 0).find("option:selected").attr('sbarcode');
        var ProductCostPrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('pcprice');
        var ProductSalePrice = $('#' + ColumnID[0] + 0).find("option:selected").attr('psprice');
        var ProductQuantity = $('#' + ColumnID[3] + 0).val();
        var ProductAmount = $('#' + ColumnID[7] + 0).val();


        var html = '<tr>';

        html += '<td class="input-group barcodeWidth" width="35%">';
        // html +='<select class="form-control clsSelect2"  name="' + ColumnName[0] + '" id="' + ColumnID[0] + TotalRowCount + '" onchange="fnGetSelectedValue(' + TotalRowCount + ');" required >'+
        //         '<option value="">Select Product</option>'+
        //         '</select>';
        html += '<input type="hidden" name="' + ColumnName[0] + '" value="' + ProductID + '">';
        html += '<input type="text" class="form-control round" value="' + ProductName + '(' + ProductSysBarcode + ')' +
            '" readonly >';
        html += '</td>';
        html += '<td width="10%">';
        // // Input Feild For sys_barcode
        // html += '<input type="hidden" name="' + ColumnName[1] + '" id="' + ColumnID[1] + TotalRowCount + '">';
        // // Input Feild For sys_barcode
        // html += '<input type="hidden" name="' + ColumnName[2] + '" id="' + ColumnID[2] + TotalRowCount + '">';

        html += '<input type="number" name="' + ColumnName[3] + '" id="' + ColumnID[3] + TotalRowCount +
            '" class="form-control round clsQuantity" value="' + ProductQuantity +
            '" onkeyup="fnTotalQuantity();fnTtlProductPrice(' + TotalRowCount + ');" required min="1">';
        html += '</td>';
        // // input feild for ordered_qtn
        // html += '<td width="10%">'+
        //             '<input type="number" name="' + ColumnName[4] + '" id="' + ColumnID[4] + TotalRowCount + '" class="form-control round clsOrderQuantity" value="0" onkeyup="fnTotalOrderQuantity();" required min="1">'+
        //         '</td>';

        // // input feild for received_qtn
        // html += '<td width="10%">'+
        //             '<input type="number" name="' + ColumnName[5] + '" id="' + ColumnID[5] + TotalRowCount + '" class="form-control round clsRcvQuantity" value="0" onkeyup="fnTotalReceiveQuantity();" required min="1">'+
        //         '</td>';

        html += '<td width="15%">' +
            '<input type="number" name="' + ColumnName[6] + '" id="' + ColumnID[6] + TotalRowCount +
            '" class="form-control round" value="' + ProductCostPrice + '" readonly required min="1">' +
            // onkeyup="fnTtlProductPrice('+TotalRowCount+');"
            '</td>';
        html += '<td width="15%">' +
            '<input type="number" name="' + ColumnName[7] + '" id="' + ColumnID[7] + TotalRowCount +
            '" class="form-control round ttlAmountCls" value="' + ProductAmount + '" readonly>' +
            '</td>';
        html += '<td width="5%">' +
            '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRow(this);">' +
            ' <i class="icon fa fa-times align-items-center"></i>' +
            '</a>' +
            '</td>';
        html += '</tr>';
        $('#' + TableID).append(html);

        $('#' + ColumnID[0] + 0).val('');
        $('#' + ColumnID[0] + 0).trigger('change');
        $('#' + ColumnID[3] + 0).val(0);
        $('#' + ColumnID[6] + 0).val(0);
        $('#' + ColumnID[7] + 0).val(0);
        $('#' + ColumnID[3] + 0).prop('readonly', true);
    } else {

        if ($('#' + ColumnID[0] + 0).val() == '') {
            swal({
                icon: 'error',
                title: 'Oops...',
                text: 'Please select product!',
            });
        } else {
            swal({
                icon: 'error',
                title: 'Oops...',
                text: 'Quantity must be greater than 0!',
            });
        }

    }
}
/* Add row End */



function fnGetSelectedValue(RowId) {

    var price = $("#product_id_" + RowId).children("option:selected").attr('pcprice');
    $("#product_price_" + RowId).val(price);

}

function fnTotalQuantity() {

    var totalQtn = 0;
    $('.clsQuantity').each(function() {
        totalQtn = Number(totalQtn) + Number($(this).val());
    });
    $('#total_quantity').val(totalQtn);
    $('#tdTotalQuantity').html(totalQtn);
}

function fnTtlProductPrice(Row) {

    console.log(Row);

    var ProductQtn = $('#product_quantity_' + Row).val();
    var ProductPrice = $('#unit_cost_price_' + Row).val();
    var TotalProductPrice = (Number(ProductQtn) * Number(ProductPrice));
    $('#total_cost_price_' + Row).val(TotalProductPrice);
    fnTotalAmount();
}

function fnTotalAmount() {

    var totalAmt = 0;
    $('.ttlAmountCls').each(function() {
        totalAmt = Number(totalAmt) + Number($(this).val());
    });
    $('#tdTotalAmount').html(totalAmt);
    //-------------------------- Total Amount
    $('#total_amount').val(totalAmt);

    // //--------------------------- T/A After Discount
    // fnCalDiscount($('#discount_rate').val());

    // //-----------------------------calculate vat amount
    // fnCalVat($('#vat_rate').val());

    // //-----------------------------calculate Due amount
    // fnCalDue($('#paid_amount').val());
}

$('#submitButton').on('click', function(event) {
    event.preventDefault();
    if ($('#total_amount').val() > 0 && $('#product_id_0').val() == '') {

        $('#form_id').submit();
    } else {

        if ($('#total_amount').val() <= 0) {
            swal({
                icon: 'error',
                title: 'Oops...',
                text: 'Total payable amount must be gratter than zero !!',
            });
        } else {
            swal({
                icon: 'error',
                title: 'Oops...',
                text: 'Product Entry must be empty!!',
            });
        }

    }
});

function fnGenBillNo(BranchId) {
    if (BranchId != '') {
        $.ajax({
            method: "GET",
            url: "{{ url('/ajaxGBillIssue') }}",
            dataType: "text",
            data: {
                BranchId: BranchId
            },
            success: function(data) {
                if (data) {
                    $('#bill_no').val(data);
                    // console.log(data);
                }
            }
        });
    }
}
</script>
@endsection