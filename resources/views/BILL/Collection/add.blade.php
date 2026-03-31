@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HrService as HRS;
use App\Services\HtmlService as HTML;

?>
<?php
$CustList = Common::ViewTableOrderIn('bill_customers',
    [['is_delete', 0], ['is_active', 1]],
    ['branch_id', HRS::getUserAccesableBranchIds()],
    ['id','customer_no', 'customer_name'],
    ['customer_name', 'ASC']);

$billList = Common::ViewTableOrderIn('bill_cash_m',
    [['is_delete', 0], ['is_active', 1]],
    ['branch_id', HRS::getUserAccesableBranchIds()],
    ['id', 'bill_no', 'total_amount'],
    ['bill_no', 'ASC']);

$PaySystemList = Common::ViewTableOrder('gnl_payment_system',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'payment_system_name'],
    ['id', 'ASC']);

$EmpList = Common::ViewTableOrderIn('hr_employees',
    [['is_delete', 0], ['is_active', 1]],
    ['branch_id', HRS::getUserAccesableBranchIds()],
    ['id', 'employee_no','emp_name'],
    ['emp_name', 'ASC']);

?>

<!-- Page -->
<form method="post" data-toggle="validator" novalidate="true" autocomplete="off">
    @csrf
    <div class="panel">
        <div class="panel-body">

            <input type="hidden" name="collection_no" id="collection_no">

            <div class="row">
                <div class="col-lg-9 offset-lg-3">
                    <!-- Html View Load  -->
                    {!! HTML::forCompanyFeild() !!}
                </div>
            </div>
            <div class="row">
                <div class="col-lg-9 offset-lg-3">
                    {!! HTML::forBranchFeild(false) !!}
                </div>
            </div>

            <div class="row">
                <!--Form Left-->
                <div class="col-lg-9 offset-lg-3">

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-3 input-title RequiredStar">Customer</label>
                        <div class="col-lg-5 input-group">
                            <select class="form-control clsSelect2" name="customer_id" id="customer_id" required
                                data-error="Please Select Customer" onchange="fnGetCustBillsInfo(this.value);">
                                <option value="">Select One</option>
                                @foreach($CustList as $CData)
                                <option value="{{ $CData->customer_no }}">{{ $CData->customer_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-3 input-title RequiredStar">Bill No</label>
                        <div class="col-lg-5 input-group">
                            <select name="bill_no" id="bill_no" class="form-control clsSelect2"
                                required onchange="fnBillDetailsLoad();">
                                <!-- fnSalesIDLoad(); -->
                                <option value="">Select One</option>
                                @foreach($billList as $bData)
                                <option value="{{ $bData->bill_no }}" bill_id="{{ $bData->id }}"
                                    bill_amount="{{ $bData->total_amount }}">
                                    {{ $bData->bill_no }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-3 input-title">Collection Date</label>
                        <div class="col-lg-5 input-group">
                            <input type="text" name="collection_date" id="collection_date" readonly
                                class="form-control round" value="{{ Common::systemCurrentDate() }}">
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-3 input-title RequiredStar">Amount</label>
                        <div class="col-lg-5">
                            <input type="text" class="form-control round onlyNumber textNumber text-right" placeholder="Enter Amount"
                                name="collection_amount" id="collection_amount" required
                                data-error="Please Enter amount" onkeyup="fnCalculate();">
                            <div class="help-block with-errors is-invalid"></div>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-3 input-title">Payment Type</label>
                        <div class="col-lg-5 input-group">
                            <select name="payment_system_id" id="payment_system_id"
                                class="form-control clsSelect2">
                                @foreach($PaySystemList as $PData)
                                <option value="{{ $PData->id }}">{{ $PData->payment_system_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-3 input-title">Collection By</label>
                        <div class="col-lg-5 input-group">
                            <select class="form-control clsSelect2" name="employee_id" id="employee_id">
                                <option value="">Select One</option>
                                @foreach($EmpList as $EData)
                                <option value="{{ $EData->employee_no }}">
                                    {{ $EData->emp_name. '('. $EData->employee_no  .')'}}
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
                            <button type="submit" class="btn btn-primary btn-round">Save</button>
                        </div>
                    </div>
                </div>
            </div>

            <!--End Panel Body-->
        </div>
        <!--End Panel 1-->
    </div>

</form>

<script type="text/javascript">

$(document).ready(function(){
    fnColletionNo();
});


$('#branch_id').change(function() {
    fnAjaxSelectBox('customer_id',
        $('#branch_id').val(),
        '{{ base64_encode("bill_customers") }}',
        '{{base64_encode("branch_id")}}',
        '{{base64_encode("id,customer_name")}}',
        '{{url("/ajaxSelectBox")}}'
    );

    fnAjaxSelectBox('employee_id',
        $('#branch_id').val(),
        '{{ base64_encode("hr_employees") }}',
        '{{base64_encode("branch_id")}}',
        '{{base64_encode("employee_no,emp_name")}}',
        '{{url("/ajaxSelectBox")}}'
    );

    fnColletionNo();

});

function fnGetCustBillsInfo(customerId) {
    $.ajax({
        method: 'get',
        url: '{{ url("/ajaxCustBillDetails") }}',
        dataType: 'text',
        data: {
            customerId: customerId
        },
        success: function(data) {

            $('#bill_no')
                .find('option')
                .remove()
                .end()
                .append(data);
        }
    });
}

function fnBillDetailsLoad() {

    var cashBill = $('#bill_no').val();

    var bill_id = $('#bill_no').find('option:selected').attr('bill_id');
    var bill_amount = $('#bill_no').find('option:selected').attr('bill_amount');
    // var installment_rate = $('#bill_no').find('option:selected').attr('installment_rate');

    $('#bill_id').val(bill_id);
    $('#bill_amount').val(bill_amount);
    // $('#installment_rate').val(installment_rate);

    $.ajax({
        method: 'get',
        url: '{{ url("/ajaxBillCollectionForBills") }}',
        dataType: 'text',
        data: {
            cashBill: cashBill
        },
        success: function(data) {
            if (data) {
                var due_amount = (Number(bill_amount) - Number(data));
                $('#due_amount').val(due_amount.toFixed(2));
            }
        }
    });
}



function fnCalculate() {

    var collection_amount = $('#collection_amount').val();

    if (collection_amount == '' || collection_amount == null) {
        collection_amount = 0;
    }

    var due_amount = $('#due_amount').val();

}

function fnColletionNo() {

    var BranchId = $("#branch_id").val();

    if (BranchId != '') {
        $.ajax({
            method: "GET",
            url: "{{ url('/ajaxBillCollectionNo') }}",
            dataType: "text",
            data: {
                BranchId: BranchId
            },
            success: function(data) {
                if (data) {
                    $('#collection_no').val(data);
                }
            }
        });
    }
}



$('form').submit(function(event) {
    $(this).find(':submit').attr('disabled', 'disabled');
});

</script>


@endsection
