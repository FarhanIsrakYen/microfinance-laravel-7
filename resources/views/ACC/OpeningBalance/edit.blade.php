@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>
<?php
$projectData = Common::ViewTableLast(
    'gnl_projects',
    [['is_delete', 0], ['is_active', 1], ['id', $OBDataM->project_id]],
    ['id', 'project_name']
);

$projectTypeData = Common::ViewTableLast(
    'gnl_project_types',
    [['is_delete', 0], ['is_active', 1], ['id', $OBDataM->project_type_id]],
    ['id', 'project_type_name']
);

$branchData = Common::ViewTableLast(
    'gnl_branchs',
    [['is_delete', 0], ['is_active', 1], ['id', $OBDataM->branch_id]],
    ['id', 'branch_name', 'branch_code']
);

?>
<div class="panel-body">
    <form enctype="multipart/form-data" method="POST" data-toggle="validator" novalidate="true">
        @csrf
        <div class="row">
            <div class="col-lg-8 offset-lg-3">
                <!-- Html View Load  -->
                {!! HTML::forCompanyFeild($OBDataM->company_id) !!}
            </div>
        </div>


        <div class="row">
            <div class="col-lg-2">
                <label class="input-title RequiredStar">Project</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" disabled>
                        @if($projectData)
                        <option>{{ $projectData->project_name }}</option>
                        @endif
                    </select>
                    <input type="hidden" name="project_id" value="{{$OBDataM->project_id}}">
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title RequiredStar">Project Type</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" disabled>
                        @if($projectTypeData)
                        <option>{{ $projectTypeData->project_type_name }}</option>
                        @endif
                    </select>
                    <input type="hidden" name="project_type_id" value="{{$OBDataM->project_type_id}}">
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title RequiredStar">Branch</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" disabled>
                        @if($branchData)
                        <option>{{ sprintf("%04d", $branchData->branch_code)."-".$branchData->branch_name }}</option>
                        @endif
                    </select>
                    <input type="hidden" name="branch_id" value="{{$OBDataM->branch_id}}">
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Opening Date</label>
                <div class="input-group ghdatepicker">
                    <div class="input-group-prepend ">
                        <span class="input-group-text ">
                            <i class="icon wb-calendar round" aria-hidden="true"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control round" name="opening_date"
                        value="{{date('d-m-Y', strtotime($OBDataM->opening_date))}}"  readonly>
                </div>
            </div>
        </div>

        <div class="row pt-10">
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
                    <?php
                    $Debit = 0;
                    $Credit = 0;
                    $Balance = 0;

                    if(in_array($Row->id, $EditLedgerArr)){
                        $Debit = $EditChildArr[$Row->id]['debit_amount'];
                        $Credit = $EditChildArr[$Row->id]['credit_amount'];
                        $Balance = $EditChildArr[$Row->id]['balance_amount'];
                    }
                    ?>
                    <tr>
                        <td scope="row">{{++$i}}</td>
                        <td>
                            <input type="hidden" name="acc_type_arr[]" id="acc_type_arr_{{$i}}"
                                value="{{$Row->acc_type_id}}">
                            {{$Row->account_type['name']}}
                        </td>
                        <td>
                            <input type="hidden" name="ledger_arr[]" id="ledger_arr_{{$i}}"
                                value="{{$Row->id}}">{{$Row->name}}
                        </td>
                        <td>
                            <input type="hidden" name="ledger_code_arr[]" id="ledger_code_arr_{{$i}}"
                                value="{{$Row->code}}">
                            {{$Row->code}}
                        </td>

                        <td>
                            <input type="number" class="form-control clsCashD" step="any" pattern="[0-9]"
                                name="debit_amount_arr[]" id="debit_amount_{{$i}}" value="{{$Debit}}"
                                onkeyup="fnCalculateTotal({{$i}});fnTotalDebit();" @if(in_array($Row->id, $ledger_in_voucher)){{ 'readonly' }}@endif></td>

                        <td>
                            <input type="number" class="form-control clsCashC" name="credit_amount_arr[]"
                                id="credit_amount_{{$i}}" value="{{$Credit}}" onkeyup="fnCalculateTotal({{$i}});fnTotalCredit();" @if(in_array($Row->id, $ledger_in_voucher)){{ 'readonly' }}@endif>
                        </td>

                        <td>
                            <input type="number" class="form-control  clsTotal" name="balance_amount_arr[]"
                                id="balance_amount_{{$i}}" value="{{$Balance}}" readonly>
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
                        <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Update</button>
                        <!--<button type="button" class="btn btn-warning btn-round">Next</button>-->
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
$(document).ready(function() {
    fnTotalDebit();
    fnTotalCredit();

});


function fnCalculateTotal(Row) {
    var CDebitAmt = $('#debit_amount_' + Row).val();
    var CCreditAmt = $('#credit_amount_' + Row).val();

    if (Number(CDebitAmt) >= 0 && Number(CCreditAmt) >= 0) {
        var TotalAmount = (Number(CDebitAmt) - Number(CCreditAmt));
        // TotalAmount = Math.abs(TotalAmount);
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
    // $('#balance_amount').val(Math.abs(totalAmt));
    $('#balance_amount').val(totalAmt);
    $('#tdTotalBalance').html(totalAmt);
    // $('#total_balance').val(Math.abs(totalAmt));
    $('#total_balance').val(totalAmt);

}

$('form').submit(function(event) {
    $(this).find(':submit').attr('disabled', 'disabled');
});
</script>
@endsection
