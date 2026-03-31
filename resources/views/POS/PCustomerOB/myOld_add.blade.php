@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\HtmlService as HTML;
?>

<div class="w-full">
    <div class="page">
        <div class="page-header">
            <h4 class="">Product OB Due Sale Entry</h4>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{url('/pos')}}">Home</a></li>
                <li class="breadcrumb-item"><a href="javascript:void(0)">Product</a></li>
                <li class="breadcrumb-item"><a href="{{url('/pos/product/obduesale')}}">Product OB Due Sale List</a>
                </li>
                <li class="breadcrumb-item active">Entry Product OB Due Sale</li>
            </ol>
        </div>
    </div>
    <div class="panel">
        <div class="panel-body">

            <form action="{{ route('storeprodobduesale') }}" method="post" data-toggle="validator" novalidate="true">
                @csrf

                <div class="row">
                    <div class="col-lg-8 offset-lg-3">
                        <!-- Html View Load  -->
                        {!! HTML::forCompanyFeild() !!}
                    </div>
                </div>

                <div class="row offset-lg-1">
                    <div class="col-lg-6">
                        {!! HTML::forBranchFeild(true) !!}
                    </div>

                    <!--Form Right-->
                    <div class="col-lg-6">
                        <div class="form-row form-group align-items-center">
                            <label class="col-lg-2 input-title RequiredStar">Opening Date</label>
                            <div class="col-lg-5 input-group">
                                <div class="input-group-prepend ">
                                    <span class="input-group-text ">
                                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                                    </span>
                                </div>
                                <input type="text" name="opening_date" id="opening_date" value=""
                                    class="form-control round datepicker" autocomplete="off"
                                    placeholder="DD-MM-YYYY">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <table class="table table-hover table-striped table-bordered w-full text-center table-responsive">
                        <thead>
                            <th width="1%">Sl#</th>
                            <th width="10%">Customer Name</th>
                            <th width="5%">Customer Code</th>
                            <th width="20%">Product</th>
                            <th width="8%">Sales Amount</th>
                            <th width="8%">Colletion Amount</th>
                            <th width="8%">Due Amount</th>
                            <th width="8%">Inst. Amount</th>
                            <th width="8%">Inst. Month</th>
                            <th width="8%">Inst. Type</th>
                            <th width="8%">Sale Date</th>
                            <th width="8%">Last Collection Date</th>
                        </thead>
                        <?php $i = 0; ?>
                        @foreach($CustomerData as $CData)
                        <tbody>
                            <td>
                                <strong>{{ ++$i }}</strong>
                                <input type="hidden" name="customer_id_arr[]" value="{{ $CData->id }}">
                            </td>
                            <td>
                                <input type="hidden" name="customer_name_arr[]" id="customer_name_{{ $i }}"
                                    value="{{ $CData->customer_name }}">
                                {{ $CData->customer_name }}
                            </td>
                            <td>
                                <input type="hidden" name="customer_no_arr[]" id="customer_no_{{ $i }}"
                                    value="{{ $CData->customer_no }}">
                                {{ $CData->customer_no }}
                            </td>
                            <td>
                                <select name="product_arr[{{ $CData->id }}][]" id="product_{{ $i }}"
                                    class="form-control round cls-select2-mul" multiple="multiple">
                                    @foreach($ProductData as $PData)
                                    <option value="{{ $PData->id }}">{{ $PData->product_name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="text" name="sale_amt_arr[]" id="sale_amt_{{ $i }}" value="0"
                                    class="form-control round ttl-sales-amt-cls" onkeyup="fnTotalSalesAmount();">
                            </td>
                            <td>
                                <input type="text" name="collection_amt_arr[]" id="collection_amt_{{ $i }}" value="0"
                                    class="form-control round ttl-clln-amt-cls" onkeyup="fnTotalCllnAmount();">
                            </td>
                            <td>
                                <input type="text" name="due_amt_arr[]" id="due_amt_{{ $i }}" value="0"
                                    class="form-control round ttl-due-amt-cls" onkeyup="fnTotalDueAmount();">
                            </td>
                            <td>
                                <input type="text" name="inst_month_arr[]" id="inst_month_{{ $i }}" value="0"
                                    class="form-control round">
                            </td>
                            <td>
                                <input type="text" name="inst_amt_arr[]" id="inst_amt_{{ $i }}" value="0"
                                    class="form-control round">
                            </td>
                            <td>
                                <select name="inst_type_arr[]" id="inst_type_amt_{{ $i }}"
                                    class="form-control round clsSelect2">
                                    <option value="1">Month</option>
                                    <option value="2">Week</option>
                                </select>
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="text" name="sale_date_arr[]" id="sale_date_{{ $i }}" value=""
                                        class="form-control round datepicker" autocomplete="off" placeholder="DD-MM-YYYY">
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="text" name="last_clln_date_arr[]" id="last_clln_date_{{ $i }}" value=""
                                        class="form-control round datepicker" autocomplete="off" placeholder="DD-MM-YYYY">
                                </div>
                            </td>
                        </tbody>
                        @endforeach
                        <tfoot>
                            <td>
                                <input type="hidden" name="total_customer" value="{{ $i }}">
                            </td>
                            <td colspan="3"><strong>Total</strong></td>
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
                </div>

                <div class="row fixed-bottom">
                    <div class="col-lg-12">
                        <div class="form-group d-flex justify-content-center">
                            <div class="example example-buttons">
                                <a href="{{ url('/pos/product/obduesale') }}" class="btn btn-default btn-round">Back</a>
                                <button type="submit" class="btn btn-primary btn-round"
                                    id="validateButton2">Save</button>
                                <!--<button type="button" class="btn btn-warning btn-round">Next</button>-->
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<script type="text/javascript">
$(document).ready(function() {
    $(".cls-select2-mul").select2({
        placeholder: "Select Product"
    });
});


function fnTotalSalesAmount() {

    var totalSalesAmt = 0;
    $('.ttl-sales-amt-cls').each(function() {
        totalSalesAmt = Number(totalSalesAmt) + Number($(this).val());
    });
    $('#total_sales_amt').html(totalSalesAmt);
    //-------------------------- Total sales Amount
    $('#total_sales_amount').val(totalSalesAmt);
}

function fnTotalCllnAmount() {

    var totalCllnAmt = 0;
    $('.ttl-clln-amt-cls').each(function() {
        totalCllnAmt = Number(totalCllnAmt) + Number($(this).val());
    });
    $('#total_clln_amt').html(totalCllnAmt);
    //-------------------------- Total Collection Amount
    $('#total_collection').val(totalCllnAmt);
}

function fnTotalDueAmount() {

    var totalDueAmt = 0;
    $('.ttl-due-amt-cls').each(function() {
        totalDueAmt = Number(totalDueAmt) + Number($(this).val());
    });
    $('#total_due_amt').html(totalDueAmt);
    //-------------------------- Total Due Amount
    $('#total_due_amount').val(totalDueAmt);
}
</script>

@endsection
