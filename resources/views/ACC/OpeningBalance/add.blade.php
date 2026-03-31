@extends('Layouts.erp_master')
@section('content')
<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>
<?php
$projectData = Common::ViewTableOrder(
    'gnl_projects',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'project_name'],
    ['project_name', 'ASC']
);

?>

<div class="panel-body">

    <form enctype="multipart/form-data" method="POST" data-toggle="validator" novalidate="true">
        @csrf
        <div class="row">
            <div class="col-lg-8 offset-lg-3">
                <!-- Html View Load  -->
                {!! HTML::forCompanyFeild() !!}
            </div>
        </div>


        <div class="row">
            <div class="col-lg-2">
                <label class="input-title RequiredStar">Project</label>
                <div class="input-group">
                    <?php
                        $PTypeTable = base64_encode("gnl_project_types");
                        $PTypeFeild = base64_encode("project_id");
                        $PTypeView = base64_encode("id,project_type_name");
                        $PTypeUrl = url("/ajaxSelectBox");
                    ?>
                    <select class="form-control clsSelect2" name="project_id" id="project_id" require onchange="fnAjaxSelectBox('project_type_id', this.value, '{{$PTypeTable}}',
                            '{{$PTypeFeild}}','{{$PTypeView}}','{{$PTypeUrl}}');">
                        <option value="">Select Project</option>
                        @foreach ($projectData as $project)
                        <option value="{{ $project->id }}">{{ $project->project_name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title RequiredStar">Project Type</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="project_type_id" id="project_type_id" require>
                        <option value="">Select Project Type</option>
                    </select>
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title RequiredStar">Branch</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="branch_id" id="branch_id" require>
                        <option value="">Select Branch</option>
                    </select>
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
                    <input type="text" class="form-control round" id="opening_date" name="opening_date"
                        value="{{ Common::systemCurrentDate() }}" autocomplete="off" readonly>
                </div>
            </div>
        </div>

        <div class="row pt-10">
            <table class="table w-full table-hover table-bordered table-striped" id="OBTable1">
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
</div>


<script>

 /* ------------------ Branch Load */
$('#project_type_id').change(function() {

    var projectID = $('#project_id').val();
    var projectTypeID = $('#project_type_id').val();
    var SelectedVal = null;

    $.ajax({
        method: "GET",
        url: "{{route('ajaxBLoadForACOB')}}",
        dataType: "text",
        data: {
            projectID: projectID,
            projectTypeID: projectTypeID,
            SelectedVal:SelectedVal
        },
        success: function(data) {
            if (data) {
                $('#branch_id').empty().html(data);
            }
        }
    });
});

    /* ------------------ Software Opening Date & Ledger Load */
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

                $('#opening_date').val(data);

            }
        }
    });

    var projectID = $('#project_id').val();

    $.ajax({
        method: "GET",
        url: "{{route('ajaxLedgerACOB')}}",
        dataType: "text",
        data: {
            projectID: projectID,
            branchID:branchID
        },
        success: function(data) {
            if (data) {
                $('#OBTable1').empty().html(data);

            }
        }
    });
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
    // $('#tdTotalBalance').html(totalAmt);
    // $('#total_balance').val(Math.abs(totalAmt));

    $('#balance_amount').val(totalAmt);
    $('#tdTotalBalance').html(totalAmt);
    $('#total_balance').val(totalAmt);

}

$('form').submit(function(event) {
    $(this).find(':submit').attr('disabled', 'disabled');
});
</script>
@endsection
