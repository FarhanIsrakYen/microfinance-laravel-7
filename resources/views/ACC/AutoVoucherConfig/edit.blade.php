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
        {!! HTML::forBranchFeild(false,'branch_id','branch_id',$dataset[0]->branch_id) !!}
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
                        <input type="hidden" class="form-control"  name="sales_type" id="sales_type" readonly value="{{$dataset[0]->sales_type}}">
                        <input type="text" class="form-control" readonly value="{{$dataset[0]->salestype['name']}}">
                        {{-- <select class="form-control clsSelect2">
                                    <option value="">Select Business Type</option>
                                    @foreach ($misType as $Row)
                                    <option value="{{$Row->id}}"  {{ ($dataset[0]->sales_type == $Row->id) ? 'selected="selected"' : '' }}  >{{$Row->name}}</option>
                                    @endforeach
                        </select> --}}
                    </div>

                  


                    ​
                    <label class="col-lg-2 input-title RequiredStar" for="voucher_type">Voucher Type</label>
                    ​
                    <div class="col-lg-2 input-group">
                        <input type="hidden" class="form-control"  name="voucher_type" id="voucher_type" readonly value="{{$dataset[0]->voucher_type}}">
                        <input type="text" class="form-control" readonly value="{{$dataset[0]->vouchertype['name']}}">

                        {{-- <select class="form-control clsSelect2"  name="voucher_type" id="voucher_type" readonly>
                            <option value="">Select Voucher Type</option>
                                    @foreach ($vtype as $Row)
                                    <option value="{{$Row->id}}" {{ ($dataset[0]->voucher_type == $Row->id) ? 'selected="selected"' : '' }}>{{$Row->name}}</option>
                                    @endforeach
                        </select> --}}
                    </div>
                    
                    <div class="col-lg-4 text-right p-10">
                        {{-- <a href="javascript:void(0);" class="btn btn-primary btn-round" id="addbtn"
                        onclick="return false;">
                            <i class="icon wb-plus  align-items-center"></i> Add
                            </a> --}}
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

            <?php
            $i = 0;
            ?>
            @foreach ($dataset as $Row)

            <tr>
                <td> {{$Row->mis_config_name}}</td>
                <td> {!! HTML::forLedgerSelectFeild($Row->ledger_code) !!}</td>
                <td> 

                <input type="hidden" name="mis_config_id[]" id="mis_config_id{{$i}}" value="{{$Row->mis_config_id}}">
                    <input type="hidden" name="mis_config_name[]" id="mis_config_name{{$i}}" value="{{$Row->mis_config_name}}">
                    <input type="hidden" name="table_field_name[]" id="table_field_name{{$i}}" value="{{$Row->table_field_name}}">
                    <input type="hidden" name="supplier_id_arr[]" id="supplier_id{{$i}}" value="{{$Row->supplier_id}}">
                    <select class="form-control clsSelect2" id="amount_type{{$i}}" name="AmountType_arr[]">
                        <option value="">Select Option</option>
                        <option value="0" {{ ($Row->amount_type == 0) ? 'selected="selected"' : '' }}>Debit</option> 
                        <option value="1" {{ ($Row->amount_type == 1) ? 'selected="selected"' : '' }}>Credit</option>
                    </select>
                </td>
            </tr>

            @endforeach
           
        </tbody>
    </table>
    <br>

    <div class="form-row align-items-center">
        <label class="col-lg-2 input-title" for="LocalNarration">Local Narration</label>
        <div class="col-lg-10 form-group">
            <div class="input-group ">
            <input type="text" class="form-control round" id="local_narration" name="local_narration" placeholder="Please enter Narration." value="{{$dataset[0]->local_narration}}">
            </div>
        </div>
    </div>

    <div class="form-row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                    <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Update</button>
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
                    
                    // data
                    $.each( data, function( key, value ) {
                        // console.log(value.mis_name);
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
                        // console.log( $('#tableID tbody').find('tr:first'));
                        // $('#tableID tbody').find('tr:first')
                        // accCash += '<option value="' + value.id + '" data="' +  value.code +'-'+ value.name+ '" >' +  value.code +'-'+ value.name+ '</option>';
                        });
                    // console.log(data);
                   
                }
            });
        }
    

}
</script>


<!-- End Page -->
@endsection
