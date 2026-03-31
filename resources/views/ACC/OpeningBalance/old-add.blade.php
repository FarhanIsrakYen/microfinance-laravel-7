@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>
<?php

$startDate = Common::systemCurrentDate();
$endDate = Common::systemCurrentDate();
$branchId = Common::getBranchId();

$projectData = Common::ViewTableOrder(
    'gnl_projects',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'project_name'],
    ['project_name', 'ASC']
);
$projectTypeData = Common::ViewTableOrder(
    'gnl_project_types',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'project_type_name'],
    ['project_type_name', 'ASC']
);
$branchData = Common::ViewTableOrder(
    'gnl_branchs',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'branch_name', 'branch_code'],
    ['branch_name', 'ASC']
);
$brachData = DB::table('gnl_branchs')
    ->where([['is_approve', 1], ['is_delete', 0]])
    ->whereNotExists(function ($brachData) {
        $brachData->select('branch_id')
            ->from('acc_ob_m')
            ->whereRaw('gnl_branchs.id = acc_ob_m.branch_id');
    })
->get();

?>

<form method="post" class="form-horizontal" data-toggle="validator" novalidate="true">
    @csrf
    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild() !!}
        </div>
    </div>

    <div class="w-full d-print-none">
        <div class="panel">
            <div class="panel-body">
                <div class="row align-items-center pb-10 d-print-none">

                    <div class="col-lg-2">
                        <label class="input-title RequiredStar">Project</label>
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="project_id" id="project_id" onchange="fnAjaxSelectBox('project_type_id',
                                                    this.value,
                                                    '{{base64_encode("gnl_project_types")}}',
                                                    '{{base64_encode("project_id")}}',
                                                    '{{base64_encode("id,project_type_name")}}',
                                                    '{{url("/ajaxSelectBox")}}');">
                                <!-- <option value="">Select Option</option> -->
                                @foreach ($projectData as $project)
                                <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <!-- {!! HTML::forBranchFeildSearch_new('one') !!} -->
                    <div class="col-lg-2">
                        <label class="input-title RequiredStar">Project Type</label>
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="project_type_id" id="project_type_id"
                                onchange="fnAjaxSelectBox('branch_id',
                                                  this.value,
                                                  '{{base64_encode("gnl_branchs")}}',
                                                  '{{base64_encode("project_type_id")}}',
                                                  '{{base64_encode("id,branch_name")}}',
                                                  '{{url("/ajaxSelectBox")}}');">
                                <option value="">Select Option</option>
                                @foreach ($projectTypeData as $projectType)
                                <option value="{{ $projectType->id }}">{{ $projectType->project_type_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    @if(Common::getBranchId() == 1)

                    <div class="col-lg-2">
                        <label class="input-title RequiredStar">Branch</label>

                        <div class="input-group">
                            <select class="form-control clsSelect2" required name="branch_id" id="branch_id">
                                <option>Select Branch</option>
                                @foreach($brachData as $data)
                                <option value="{{ $data->id }}">
                                    {{ sprintf("%04d", $data->branch_code) . " - " . $data->branch_name  }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="branch_id" id="branch_id" value="{{ Common::getBranchId() }}">
                    @endif
                    <div class="col-lg-2">
                        <label class="input-title RequiredStar">Opening Date</label>
                        <div class="input-group ghdatepicker">
                            <div class="input-group-prepend ">
                                <span class="input-group-text ">
                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control round" id="opening_date" name="opening_date"
                                value="{{ Common::systemCurrentDate() }}" placeholder="DD-MM-YYYY" required
                                data-error="Select Date" autocomplete="off" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <table class="table w-full table-hover table-bordered table-striped ">
                <thead>
                    <tr>
                        <th>SL</th>
                        <th>Account Type</th>
                        <th>Account Head</th>
                        <th>Code</th>
                        <th>Debit</th>
                        <th>Credit</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = 0;?>
                    @foreach ($ladgerData as $Row)
                    <?php $i++;?>
                    <tr>
                        <td scope="row">{{$i}}</td>
                        <td>
                            <input type="hidden" name="acc_type_arr[]" id="acc_type_arr_{{$i}}"
                                value="{{$Row->acc_type_id}}">
                            {{$Row->account_type['name']}}
                        </td>
                        <td>
                            <input type="hidden" name="ledger_arr[]" id="ledger_arr_{{$i}}" value="{{$Row->id}}">
                            {{$Row->name}}
                        </td>
                        <td>
                            <input type="hidden" name="ledger_code_arr[]" id="ledger_code_arr_{{$i}}"
                                value="{{$Row->code}}">
                            {{$Row->code}}
                        </td>

                        <td>
                            <input type="number" class="form-control clsCashD" step="any" pattern="[0-9]"
                                name="debit_amount_arr[]" id="debit_amount_{{$i}}" value='0'
                                onkeyup="fnCalculateTotal({{$i}});fnTotalDebit();"></td>

                        <td>
                            <input type="number" class="form-control clsCashC" name="credit_amount_arr[]"
                                id="credit_amount_{{$i}}" value='0' onkeyup="fnCalculateTotal({{$i}});fnTotalCredit();">
                        </td>

                        <td>
                            <input type="number" class="form-control  clsTotal" name="balance_amount_arr[]"
                                id="balance_amount_{{$i}}" value='0' readonly>
                        </td>

                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="4" class="text-right">
                            <input type="hidden">
                            <h5>Total:</h5>
                        </td>

                        <td style="font-weight: bold;">
                            <input type="hidden" name="total_debit_amount" id="total_debit_amount" value="0" min="1">
                            <h5 id="tdTotalDebit">0.00</h5>
                        </td>

                        <td style="font-weight: bold;">
                            <input type="hidden" name="total_credit_amount" id="total_credit_amount" value="0" min="1">
                            <h5 id="tdTotalCredit">0.00</h5>
                        </td>
                        <td style="font-weight: bold;">
                            <input type="hidden" name="total_balance" id="total_balance" value="0" min="1">
                            <h5 id="tdTotalBalance">0.00</h5>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="row">
            <div class="col-lg-8 offset-lg-2">
                <div class="form-group d-flex justify-content-center">
                    <div class="example example-buttons">
                        <a href="javascript:void(0)" onclick="goBack();"
                            class="btn btn-default btn-round d-print-none">Back</a>
                        <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Save</button>
                        <!--<button type="button" class="btn btn-warning btn-round">Next</button>-->
                    </div>
                </div>
            </div>
        </div>
</form>

<script>
$(document).ready(function() {

    var branchID = $('#branch_id').val();
    $.ajax({
        method: "GET",
        url: "{{url('/ajaxBranchOpendate')}}",
        dataType: "text",
        data: {
            branchID: branchID,
            moduleName: 'acc'
        },
        success: function(data) {
            if (data) {
                // $('#product_id_0').html('');
                // $('#'+firstRowFirstColId).append(data);
                // $('#'+firstRowFirstColId).trigger('change');
                // console.log (data);
                $('#opening_date').val(data);


            }
        }
    });




});

$('#branch_id').change(function() {

    var branchID = $('#branch_id').val();
    $.ajax({
        method: "GET",
        url: "{{url('/ajaxBranchOpendate')}}",
        dataType: "text",
        data: {
            branchID: branchID,
            moduleName: 'acc'
        },
        success: function(data) {
            if (data) {
                // $('#product_id_0').html('');
                // $('#'+firstRowFirstColId).append(data);
                // $('#'+firstRowFirstColId).trigger('change');
                // console.log (data);
                // $('#opening_date').val(data);
            }
        }
    });

});

function fnCalculateTotal(Row) {
    var CDebitAmt = $('#debit_amount_' + Row).val();
    var CCreditAmt = $('#credit_amount_' + Row).val();

    if (Number(CDebitAmt) >= 0 && Number(CCreditAmt) >= 0) {
        var TotalAmount = (Number(CDebitAmt) - Number(CCreditAmt));
        TotalAmount = Math.abs(TotalAmount);
        $('#balance_amount_' + Row).val(TotalAmount);
    } else {
        $('#balance_amount_' + Row).val(0);
    }
}

function fnTotalDebit() {

    var totalDAmt = 0;
    $('.clsCashD').each(function() {
        totalDAmt = Number(totalDAmt) + Number($(this).val());

    });
    $('#debit_amount').val(totalDAmt);
    $('#tdTotalDebit').html(totalDAmt);
    $('#total_debit_amount').val(totalDAmt);
    fnTotalAmount();
}

function fnTotalCredit() {

    var totalCAmt = 0;
    $('.clsCashC').each(function() {
        totalCAmt = Number(totalCAmt) + Number($(this).val());

    });
    $('#credit_amount').val(totalCAmt);
    // $('#tdTotalDebit').html(totalCAmt);
    $('#tdTotalCredit').html(totalCAmt);
    $('#total_credit_amount').val(totalCAmt);
    fnTotalAmount();
}

function fnTotalAmount() {

    var totalAmt = 0;
    $('.clsTotal').each(function() {
        totalAmt = Number(totalAmt) + Number($(this).val());
    });
    $('#balance_amount').val(Math.abs(totalAmt));
    $('#tdTotalBalance').html(totalAmt);
    $('#total_balance').val(Math.abs(totalAmt));

}

$('form').submit(function(event) {
    $(this).find(':submit').attr('disabled', 'disabled');
});
</script>
@endsection