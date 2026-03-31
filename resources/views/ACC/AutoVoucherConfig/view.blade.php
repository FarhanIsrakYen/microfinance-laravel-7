@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>

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
                    <label class="col-lg-2 input-title" for="sales_type">Business Type</label>
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
                    <label class="col-lg-2 input-title" for="voucher_type">Voucher Type</label>
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
                <th>Ledger Name-Code</th>
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
                <td> {{$Row->ledger['name'].'-'.$Row->ledger_code}}</td>
                <td> 

                    {{ ($Row->amount_type == 0) ? 'Debit' : 'Credit' }}
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
            <input type="text" class="form-control round" id="local_narration" name="local_narration" placeholder="Please enter Narration." value="{{$dataset[0]->local_narration}}" readonly>
            </div>
        </div>
    </div>

    <div class="form-row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                   <a href="javascript:void(0)" onClick="window.print();"
                    class="btn btn-default btn-round clsPrint d-print-none">Print</a>
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


 
});

function fncallajaxtoconfig (){
  
}
</script>


<!-- End Page -->
@endsection
