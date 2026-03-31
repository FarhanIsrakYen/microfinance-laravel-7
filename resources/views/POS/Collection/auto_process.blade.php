@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\PosService as POSS;
?>

<form method="post" class="form-horizontal" action=""
    data-toggle="validator" novalidate="true">
    @csrf

    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild() !!}
        </div>
    </div>



    <div class="row offset-lg-1">
        <div class="col-lg-6">
            {!! HTML::forBranchFeild(true,'branch_id','branch_id',null,'','Branch') !!}
        </div>

        <div class="col-lg-6">
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Collection Date</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ghdatepicker">
                        <div class="input-group-prepend ">
                            <span class="input-group-text ">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control round" id="collection_date"
                            name="collection_date" required readonly value="{{ Common::systemCurrentDate() }}">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="table-responsive">
            <table class="table w-full table-hover table-bordered table-striped">
                <thead>
                    <tr>
                        <th style="width:5%;">SL</th>
                        <th>Customer Name</th>
                        <!-- <th>Customer Code</th> -->
                        <th>Sales Bill No</th>
                        <th>Installment Amount</th>
                        <th width="5%">Full</th>
                        <th width="5%">Partial</th>
                        <th width="5%">Zero</th>
                        <th>Amount</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 0; ?>
                    @foreach($instSalesData as $salRow)
                    <?php
                    $scheduleList = POSS::installmentSchedule($salRow->company_id, $salRow->branch_id,null,
                        $salRow->sales_date, $salRow->installment_type, $salRow->installment_month);

                    $Today = new DateTime(Common::systemCurrentDate());
                    $Today = $Today->format('Y-m-d');

                    $ColList = FALSE;

                    if(in_array($Today, $scheduleList)){
                        $ColList = TRUE;
                    }
                    ?>
                    @if($ColList)
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td>
                                {{ $salRow->customer_name. " (". $salRow->customer_no.")" }}
                                <input type="hidden" name="sales_id_arr[]" value="{{ $salRow->id }}">
                                <input type="hidden" name="sales_bill_no_arr[]" value="{{ $salRow->sales_bill_no }}">
                                <input type="hidden" name="customer_id_arr[]" value="{{ $salRow->customer_id }}">
                                <input type="hidden" name="installment_rate_arr[]" value="{{ $salRow->installment_rate }}">

                            </td>
                            
                            <td>
                                {{ $salRow->sales_bill_no }}
                            </td>
                            <td class="text-right">
                                {{ $salRow->installment_amount }}
                                <input type="hidden" name="installment_amount_arr[]" id="installment_amount_arr_{{ $i }}" value="{{ $salRow->installment_amount }}">
                            </td>
                            <td>
                                <input type="radio" onclick="fnFullPayment({{ $i }});" id="radio_full_{{ $i }}" name="payment_type_{{ $i }}" checked value="0">
                            </td>
                            <td>
                                <input type="radio" onclick="fnPartialPayment({{ $i }});" id="radio_partial_{{ $i }}" name="payment_type_{{ $i }}" value="0">
                            </td>
                            <td>
                                <input type="radio" onclick="fnZeroPayment({{ $i }});" id="radio_zero_{{ $i }}" name="payment_type_{{ $i }}" value="0">
                            </td>
                            <td>
                                <input type="text" name="paid_amount_arr[]" id="paid_amount_arr_{{ $i }}" value="{{ $salRow->installment_amount }}" class="form-control round text-right">
                            </td>

                        </tr>
                    @endif
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-9 offset-lg-2">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                  <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                    <button type="submit" class="btn btn-primary btn-round"
                        id="validateButton2">Save</button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>

$(document).ready(function() {
    $('.page-header-actions').hide();

});

function fnFullPayment(rowNo){

    $('#paid_amount_arr_'+rowNo).val($('#installment_amount_arr_'+rowNo).val());
    $('#paid_amount_arr_'+rowNo).prop('readonly', true);
}

function fnPartialPayment(rowNo){
    $('#paid_amount_arr_'+rowNo).val('');
    $('#paid_amount_arr_'+rowNo).prop('readonly', false);
}

function fnZeroPayment(rowNo){
    $('#paid_amount_arr_'+rowNo).val(0);
    $('#paid_amount_arr_'+rowNo).prop('readonly', true);

}

</script>
@endsection
