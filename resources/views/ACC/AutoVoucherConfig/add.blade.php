@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\HtmlService as HTML;
?>
 <!-- Page -->
 {{-- {!! HTML::forLedgerSelectFeild() !!} --}}
<div class="panel-body">
    <form enctype="multipart/form-data" method="POST" data-toggle="validator" novalidate="true">
        @csrf
        {!! HTML::forBranchFeild(false) !!}
        <div class="row">
            <div class="col-lg-12">

                <div class="row">
                    <div class="panel-heading align-self-center" style="background:#17b3a3; height:40px; width: 100%;">
                        <div class="row">
                            {{-- <label class="panel-title col-md-4 text-white" style="padding: 10px 100px;">HHHHHH</label> --}}
                            <div class="panel-title col-md-5" style="padding: 10px 100px;">
                                <label class="text-white">Auto Vouchers Configuration</label>
                                        {{-- <input type="hidden" name="total_amount" id="total_amount_{{$vdata->id}}"> --}}
                            </div>

                        </div>
                    </div>
                </div>
                <br>
                <div class="row mb-2 align-items-center">
                    <label class="col-lg-2 input-title RequiredStar" for="sales_type">Business Type</label>
                    <div class="col-lg-2 input-group">
                        <select class="form-control clsSelect2" name="sales_type" id="sales_type" >
                                    <option value="">Select Business Type</option>
                                    @foreach ($misType as $Row)
                                    <option value="{{$Row->id}}" >{{$Row->name}}</option>
                                    @endforeach
                        </select>
                    </div>




                    ​
                    <label class="col-lg-2 input-title RequiredStar" for="voucher_type">Voucher Type</label>
                    ​
                    <div class="col-lg-2 input-group">
                        <select class="form-control clsSelect2"  name="voucher_type" id="voucher_type">
                            <option value="">Select Voucher Type</option>
                                    @foreach ($vtype as $Row)
                                    <option value="{{$Row->id}}" >{{$Row->name}}</option>
                                    @endforeach
                        </select>
                    </div>

                    <div class="col-lg-4 text-right p-10">
                        <a href="javascript:void(0);" type="submit" class="btn btn-primary btn-round" id="addbtn"
                            onclick="fncallajaxtoconfig();">
                            <i class="icon wb-plus  align-items-center"></i> Add
                            </a>
                        {{-- <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Add</button> --}}
                    </div>
                </div>



            </div>
        </div>



    <div class="row">
    <div class="col-lg-12">
    <table class="table w-full table-hover table-bordered table-striped dataTable"  id="tableID">
        <thead>
            <tr>
                <th>MIS Name</th>
                <th>Ledger Code</th>
                <th>Amount Type</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td></td>
                <td>
                {{-- <input type="text" class="form-control" id="LedgerCode" name="LedgerCode"> --}}
                </td>
                <td>
                {{-- <input type="text" class="form-control" id="AmountType" name="AmountType"> --}}
                </td>
            </tr>

        </tbody>
    </table>
    <br>

    <div class="form-row align-items-center">
        <label class="col-lg-2 input-title" for="LocalNarration">Local Narration</label>
        <div class="col-lg-10 form-group">
            <div class="input-group ">
                <input type="text" class="form-control round" id="local_narration" name="local_narration" placeholder="Please enter Narration.">
            </div>
        </div>
    </div>

    <div class="form-row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                    <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Submit</button>
                    {{-- <button type="button" class="btn btn-warning btn-round">Reset</button> --}}
                </div>
            </div>
        </div>
    </div>

    </div>
    </div>
    </form>
</div>
<script>
$(document).ready(function() {


    // clsSelect2-test



    $('#sales_type').change(function() {


    });


});

function fncallajaxtoconfig (){

   var misType = $('#sales_type').val();
   var VoucherType = $('#voucher_type').val();


    if ( $('#sales_type').val() != '' && $('#voucher_type').val() != '') {
              $.ajax({
                method: "GET",
                url: "{{url('/ajaxAutoVoucheritem')}}",
                dataType: "json",
                data: {
                    misType: misType, VoucherType:VoucherType
                },
                success: function(data) {

                    $.each( data, function( key, value ) {
                        console.log(value.mis_name);
                        var html = '<tr>';
                        html += '<td>'+value.mis_name+'</td>';
                        html += '<td>  {!! HTML::forLedgerSelectFeild() !!} </td>';

                        html += '<td>';
                            html += '<input type="hidden" name="mis_config_id[]" id="mis_config_id'+key+'" value="'+value.id+'">';
                        html += '<input type="hidden" name="mis_config_name[]" id="mis_config_name'+key+'" value="'+value.mis_name+'">';
                        html += '<input type="hidden" name="table_field_name[]" id="table_field_name'+key+'" value="'+value.table_field_name+'">';
                        html += '<input type="hidden" name="supplier_id_arr[]" id="supplier_id'+key+'" value="'+value.supplier_id+'">';
                        html += '<select class="form-control clsSelect2" id="amount_type'+key+'" name="AmountType_arr[]"> ';
                        html += ' <option value="">Select Option</option><option value="0">Debit</option> <option value="1">Credit</option></select>';
                        // html += '<td><input type="text" class="form-control" ></td>';
                        html += '</td>';
                        html += '</tr>';

                        $('#tableID tbody').find('tr:first').after(html);
                        $('.clsSelect2').select2();
                        // accCash += '<option value="' + value.id + '" data="' +  value.code +'-'+ value.name+ '" >' +  value.code +'-'+ value.name+ '</option>';
                        });
                    // console.log(data);

                }
            });
        }


}

$('#addbtn').click(function(event) {
    $(this).attr('disabled', 'disabled');
    $(this).addClass('disabled');
});
</script>


<!-- End Page -->
@endsection
