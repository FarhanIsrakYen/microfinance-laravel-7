@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
use App\Services\HrService as HRS;

$CustList = Common::ViewTableOrderIn('pos_customers',
    [['is_delete', 0], ['is_active', 1]],
    ['branch_id', HRS::getUserAccesableBranchIds()],
    ['id','customer_no', 'customer_name', 'customer_no'],
    ['customer_name', 'ASC']);

$SalesList = Common::ViewTableOrderIn('pos_sales_m',
    [['is_delete', 0], ['is_active', 1], ['sales_type', 2], ['is_complete', 0]],
    ['branch_id', HRS::getUserAccesableBranchIds()],
    ['id', 'sales_bill_no', 'total_amount', 'installment_rate'],
    ['sales_bill_no', 'ASC']);

$PaySystemList = Common::ViewTableOrder('gnl_payment_system',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'payment_system_name'],
    ['id', 'ASC']);

$EmpList = Common::ViewTableOrderIn('hr_employees',
    [['is_delete', 0], ['is_active', 1]],
    ['branch_id', HRS::getUserAccesableBranchIds()],
    ['employee_no', 'emp_name', 'emp_code'],
    ['emp_name', 'ASC']);

?>

<!-- Page -->
<div class="row">
    <div class="col-lg-9 offset-lg-3">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($collectionData->company_id, 'disabled') !!}
    </div>
</div>
<div class="row">
    <div class="col-lg-9 offset-lg-3">
        {!! HTML::forBranchFeild(true,'branch_id','branch_id',$collectionData->branch_id,'disabled') !!}
    </div>
</div>

<div class="row">
    <!--Form Left-->
    <div class="col-lg-9 offset-lg-3">

        <div class="form-row form-group align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Customer</label>
            <div class="col-lg-5 input-group">
                <select class="form-control clsSelect2" id="customer_id" disabled>
                    <option value="">Select One</option>
                    @foreach($CustList as $CData)
                    <option value="{{ $CData->customer_no }}"
                        {{ ($collectionData->customer_id == $CData->customer_no) ? 'selected' : '' }}>
                        {{ $CData->customer_name. " (". $CData->customer_no . ")" }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row form-group align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Bill No</label>
            <div class="col-lg-5 input-group">
                <select id="sales_bill_no" class="form-control clsSelect2" disabled>
                    <option value="">Select One</option>
                    @foreach($SalesList as $SData)
                    <option value="{{ $SData->sales_bill_no }}" sales_id="{{ $SData->id }}"
                        sales_payable_amount="{{ $SData->total_amount }}"
                        installment_rate="{{ $SData->installment_rate }}"
                        {{ ($collectionData->sales_bill_no == $SData->sales_bill_no) ? 'selected' : '' }}>
                        {{ $SData->sales_bill_no }}
                    </option>
                    @endforeach
                </select>

                <input type="hidden" name="sales_id" id="sales_id"
                    value="{{ $collectionData->sales_id}}">
                <input type="hidden" name="sales_payable_amount" id="sales_payable_amount">
                <input type="hidden" name="installment_rate" id="installment_rate">
                <input type="hidden" name="due_amount" id="due_amount">

            </div>
        </div>

        <div class="form-row form-group align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Collection Date</label>
            <div class="col-lg-5 input-group">
                <?php
                    if(!empty($collectionData->collection_date)){
                        $collection_date = new DateTime($collectionData->collection_date);
                        $collection_date = $collection_date->format('d-m-Y');
                    }
                    else{
                        $collection_date = Common::systemCurrentDate();
                    }
                    ?>
                <input type="text" name="collection_date" id="collection_date" readonly required
                    class="form-control round" value="{{ $collection_date }}">
            </div>
        </div>

        <div class="form-row form-group align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Amount</label>
            <div class="col-lg-5">
                <input type="text" class="form-control round onlyNumber text-right" id="collection_amount"
                    value="{{ $collectionData->collection_amount }}" readonly>
            </div>
        </div>

        <div class="form-row form-group align-items-center">
            <label class="col-lg-3 input-title">Out Standing</label>
            <div class="col-lg-5">
                <input type="text" class="form-control round text-right" id="out_standing" readonly>
            </div>
        </div>

        <div class="form-row form-group align-items-center">
            <label class="col-lg-3 input-title">Payment Type</label>
            <div class="col-lg-5 input-group">
                <select id="payment_system_id" class="form-control clsSelect2" disabled>
                    @foreach($PaySystemList as $PData)
                    <option value="{{ $PData->id }}"
                        {{ ($collectionData->payment_system_id == $PData->id) ? 'selected' : '' }}>
                        {{ $PData->payment_system_name }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row form-group align-items-center">
            <label class="col-lg-3 input-title">Collection By</label>
            <div class="col-lg-5 input-group">
                <select class="form-control clsSelect2" id="employee_id" disabled>
                    <option value="">Select One</option>
                    @foreach($EmpList as $EData)
                    <option value="{{ $EData->employee_no }}"
                        {{ ($collectionData->employee_id == $EData->employee_no) ? 'selected' : '' }}>
                        {{ $EData->emp_name. " (". $EData->emp_code . ")" }}
                    </option>
                    @endforeach
                </select>
            </div>
        </div>

    </div>
</div>

<div class="row align-items-center">
    <div class="col-lg-12">
        <div class="form-group d-flex justify-content-center">
            <div class="example example-buttons">
                <a href="javascript:void(0)" onclick="goBack();"
                    class="btn btn-default btn-round">Back</a>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">
$(document).ready(function() {

    // fnAjaxSelectBox('customer_id',
    //     $('#branch_id').val(),
    //     '{{ base64_encode("pos_customers") }}',
    //     '{{base64_encode("branch_id")}}',
    //     '{{base64_encode("customer_no,customer_name")}}',
    //     '{{url("/ajaxSelectBox")}}',
    //     '{{ $collectionData->customer_id }}'
    // );

    // fnAjaxSelectBox('employee_id',
    //     $('#branch_id').val(),
    //     '{{ base64_encode("hr_employees") }}',
    //     '{{base64_encode("branch_id")}}',
    //     '{{base64_encode("employee_no,emp_name")}}',
    //     '{{url("/ajaxSelectBox")}}',
    //     '{{ $collectionData->employee_id }}'
    // );

    $.ajax({
        method: 'get',
        url: '{{ url("/ajaxCustSalesDetails") }}',
        dataType: 'text',
        data: {
            customerId: $('#customer_id').val(),
            selectedVal: $('#sales_bill_no').val()
        },
        success: function(data) {

            $('#sales_bill_no')
                .find('option')
                .remove()
                .end()
                .append(data);

            $('#sales_payable_amount').val($('#sales_bill_no').find('option:selected').attr(
                'sales_payable_amount'));
            $('#installment_rate').val($('#sales_bill_no').find('option:selected').attr(
                'installment_rate'));

            $.ajax({
                method: 'get',
                url: '{{ url("/ajaxBillCollection") }}',
                dataType: 'text',
                data: {
                    salesBill: $('#sales_bill_no').val()
                },
                success: function(data) {
                    if (data) {
                        var due_amount_a = (Number($('#sales_payable_amount').val()) -
                            Number(data) + Number($('#collection_amount').val()));
                        $('#due_amount').val(due_amount_a.toFixed(2));

                        var out_standing_a = (due_amount_a - Number($('#collection_amount').val()));
                        $('#out_standing').val(out_standing_a.toFixed(2));
                    }
                }
            });
        }
    });
});

</script>
@endsection
