@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true" autocomplete="off">
    @csrf

    <div class="row">
        <div class="col-lg-1"></div>
        <div class="col-lg-7">

            @if(Auth::user()->branch_id == 1)
                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Branch</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <select class="form-control" name="branchId" id="branchId" required
                                data-error="Please Select Branch">
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            @else
                <input type="hidden" name="branchId" id="branchId" value="">
            @endif

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Samity</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control" name="samityId" id="samityId" required
                            data-error="Please Select Samity">
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Member</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control" name="memberId" id="memberId" required
                            data-error="Please Select Member">
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Loan Id</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control" name="loanId" id="loanId" required
                            data-error="Please Select Loan Account">
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Waiver Date</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control" name="waiverDate" value="{{ Carbon\Carbon::parse($sysDate)->format('d-m-Y') }}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Waiver Type</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control" name="isWithServiceCharge" id="isWithServiceCharge" required
                            data-error="Please Select">
                            <option value="1">With Service Charge</option>
                            <option value="0">Without Service Charge</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div id="waiverAmountDiv">
                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Principal Amount</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <input type="text" class="form-control textNumber" name="principalAmount" id="principalAmount" required data-error="Please give Amount" readonly>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Interest Amount</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <input type="text" class="form-control textNumber" name="interestAmount" id="interestAmount" required data-error="Please give Amount" readonly>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Waiver Amount</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <input type="text" class="form-control textNumber" name="amount" id="amount" required data-error="Please give Amount" readonly>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Notes</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <textarea class="form-control" name="note" id="note" rows="2" required></textarea>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
        </div>

        <div class="col-lg-4 align-items-left">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>LOAN</th>
                        <th>TOTAL</th>
                        <th>PRICIPAL</th>
                        <th>INTEREST</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Payable</td>
                        <td class="savInfo text-right" id="total_payable"></td>
                        <td class="savInfo text-right" id="pricipal_payable"></td>
                        <td class="savInfo text-right" id="interest_payable"></td>
                    </tr>
                    <tr>
                        <td>Teansaction</td>
                        <td class="savInfo text-right" id="total_trans"></td>
                        <td class="savInfo text-right" id="pricipal_trans"></td>
                        <td class="savInfo text-right" id="interest_trans"></td>
                    </tr>
                    <tr>
                        <td>Outstanding</td>
                        <td class="savInfo text-right" id="total_outstanding"></td>
                        <td class="savInfo text-right" id="pricipal_outstanding"></td>
                        <td class="savInfo text-right" id="interest_outstanding"></td>
                    </tr>
                    <tr>
                        <td>Installment</td>
                        <td class="savInfo text-right" id="total_installment"></td>
                        <td class="savInfo text-right" id="pricipal_installment"></td>
                        <td class="savInfo text-right" id="interest_installment"></td>
                    </tr>
                    <tr>
                        <td>Advance</td>
                        <td class="savInfo text-right" id="total_advance"></td>
                        <td class="savInfo text-right" id="pricipal_advance"></td>
                        <td class="savInfo text-right" id="interest_advance"></td>
                    </tr>
                    <tr>
                        <td>Due</td>
                        <td class="savInfo text-right" id="total_due"></td>
                        <td class="savInfo text-right" id="pricipal_due"></td>
                        <td class="savInfo text-right" id="interest_due"></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-lg-1"></div>
    </div>

    <div class="row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                            class="btn btn-default btn-round d-print-none">Back</a>
                    <button type="submit" class="btn btn-primary btn-round">Save</button>
                </div>
            </div>
        </div>
    </div>
</form>

<link rel="stylesheet" href="{{asset('assets/css/selectize.bootstrap3.min.css')}}">
<script src="{{asset('assets/js/selectize.min.js')}}"></script>

<style>
    .selectize-control div.active {
        background-color: lightblue;
    }
    .selectize-control .lebel {
        color: #804739;
        font-weight: bold;
    }
</style>

<script>
    $(document).ready(function(){

        /* branch selectize */
        @if(Auth::user()->branch_id == 1)
            selectizeBranch(<?= json_encode($branchs) ?>);
        @else
            selectizeSamity(<?= json_encode($samitys) ?>);
            $('#branchId').val({{ $branchs }});
        @endif
    });

    function selectizeBranch(options) {

        $('#branchId').selectize({
            valueField: 'id',
            labelField: 'branch',
            searchField: ['branch_code', 'branch_name'],
            sortField: [{
                field: "branch_code",
                direction: "asc"
            }],
            // sortDirection: 'asc',
            placeholder: 'Select Branch',
            highlight: true,
            allowEmptyOption: true,
            maxItems: 1,
            //only using options-value in jsfiddle - real world it's using the load-function
            options: options,
            create: false,
            render: {
                option: function (branch, escape) {

                    return '<div>' +
                        '<span class="lebel">' + branch.branch_code + ' - ' + branch.branch_name + '</span>' +
                        '</div>';

                }
            }
        });
    }

    /* get samity information */
    $('#branchId').change(function(e) {

        var element = jQuery('#samityId');

        if(element[0].selectize){
            element[0].selectize.destroy();
        }

        var branchId = $(this).val();

        if(branchId == ''){
            return false;
        }

        $('#waiverAmountDiv :input').each(function(){
            $(this).val('');
        });

        $.ajax({
            type: "POST",
            url: "./getData",
            data: {context : 'samity', branchId : branchId},
            dataType: "json",
            success: function (response) {
                // $.each(response['samitys'], function (index, value, code) {
                //     $('#samityId').append("<option value="+index+">"+value+"</option>");
                // });

                selectizeSamity(response['samitys']);
            },
            error: function(){
                alert('error!');
            }
        });
    });

    function selectizeSamity(options) {

        $('#samityId').selectize({
            valueField: 'id',
            labelField: 'samity',
            searchField: ['samityCode', 'name'],
            sortField: [{
                field: "samityCode",
                direction: "asc"
            }],
            // sortDirection: 'asc',
            placeholder: 'Select Samity',
            highlight: true,
            allowEmptyOption: true,
            maxItems: 1,
            //only using options-value in jsfiddle - real world it's using the load-function
            options: options,
            create: false,
            render: {
                option: function (samity, escape) {

                    return '<div>' +
                        '<span class="lebel">'+ samity.samityCode + ' - ' + samity.name + '</span>' +
                        '</div>';

                }
            }
        });
    }

    /* get member information */
    $('#samityId').change(function(e) {

        var element = jQuery('#memberId');
        if(element[0].selectize){
            element[0].selectize.destroy();
        }

        var samityId = $(this).val();

        if(samityId == ''){
            return false;
        }

        $('#waiverAmountDiv :input').each(function(){
            $(this).val('');
        });

        $.ajax({
            type: "POST",
            url: "./getData",
            data: {context : 'member', samityId : samityId},
            dataType: "json",
            success: function (response) {

                selectizeMembers(response['members']);
            },
            error: function(){
                alert('error!');
            }
        });
    });

    function selectizeMembers(options) {

        $('#memberId').selectize({
            valueField: 'id',
            labelField: 'member',
            searchField: ['memberCode', 'name'],
            sortField: [{
                field: "memberCode",
                direction: "asc"
            }],
            // sortDirection: 'asc',
            placeholder: 'Select Member',
            highlight: true,
            allowEmptyOption: true,
            maxItems: 1,
            //only using options-value in jsfiddle - real world it's using the load-function
            options: options,
            create: false,
            render: {
                option: function (member, escape) {

                    return '<div>' +
                        '<span class="lebel">'+ member.memberCode + ' - ' + member.name + '</span>' +
                        '</div>';

                }
            }
        });
    }

    /* get loan information */
    $('#memberId').change(function(e) {

        $('#loanId option:gt(0)').remove();

        var memberId = $(this).val();

        if(memberId == ''){
            return false;
        }

        $('#waiverAmountDiv :input').each(function(){
            $(this).val('');
        });

        $.ajax({
            type: "POST",
            url: "./getData",
            data: {context : 'loan', memberId : memberId},
            dataType: "json",
            success: function (response) {

                $.each(response['loans'], function (index, value) {
                    $('#loanId').append("<option value="+index+">"+value+"</option>");
                });

                $(".savInfo").html('');

                // selectizeLoans(response['loans']);
            },
            error: function(){
                alert('error!');
            }
        });
    });

    /* get loan information */
    $("#loanId").change(function (e) {

        var loanId = $(this).val();

        $(".savInfo").html('');

        $('#waiverAmountDiv :input').each(function(){
            $(this).val('');
        });

        if(loanId == ''){
            return false;
        }

        $.ajax({
            type: "POST",
            url: "./getData",
            data: {context : 'loanInfo', loanId : loanId},
            dataType: "json",
            success: function (response) {
                $('#total_payable').html(response.total_payable);
                $('#pricipal_payable').html(response.pricipal_payable);
                $('#interest_payable').html(response.interest_payable);
                $('#total_trans').html(response.total_trans);
                $('#pricipal_trans').html(response.pricipal_trans);
                $('#interest_trans').html(response.interest_trans);
                $('#total_outstanding').html(response.total_outstanding);
                $('#pricipal_outstanding').html(response.pricipal_outstanding);
                $('#interest_outstanding').html(response.interest_outstanding);
                $('#total_installment').html(response.total_installment);
                $('#pricipal_installment').html(response.pricipal_installment);
                $('#interest_installment').html(response.interest_installment);
                $('#total_advance').html(response.total_advance);
                $('#pricipal_advance').html(response.pricipal_advance);
                $('#interest_advance').html(response.interest_advance);
                $('#total_due').html(response.total_due);
                $('#pricipal_due').html(response.pricipal_due);
                $('#interest_due').html(response.interest_due);
                $('#total_rebateable').html(response.total_rebateable);
                $('#pricipal_rebateable').html(response.pricipal_rebateable);
                $('#interest_rebateable').html(response.interest_rebateable);

                $('#principalAmount').val(response.pricipal_outstanding);
                $('#interestAmount').val(response.interest_outstanding);
                $('#amount').val(response.total_outstanding)

            },
            error: function(){
                alert('error!');
            }
        });
    });
    /* end get loan information */

    $('#isWithServiceCharge').change(function(){

        if ($('#isWithServiceCharge').val() == 1) {
            $('#amount').val(Number($('#principalAmount').val()) + Number($('#interestAmount').val()));
        }
        else {
            $('#amount').val($('#principalAmount').val());
        }
    })

    // Disable Multiple Click with submit form
    $('form').submit(function (event) {
        event.preventDefault();

        $.ajax({
                url: "{{ url()->current() }}",
                type: 'POST',
                dataType: 'json',
                data: $('form').serialize(),
            })
            .done(function (response) {

                if (response['alert-type'] == 'error') {
                    swal({
                        icon: 'error',
                        title: 'Oops...',
                        text: response['message'],
                    });
                    $('form').find(':submit').prop('disabled', false);
                } else {
                    $('form').trigger("reset");
                    swal({
                        icon: 'success',
                        title: 'Success...',
                        text: response['message'],
                    });

                    setTimeout(function () {
                        window.location = './'
                    }, 3000);
                }

            })
            .fail(function () {
                console.log("error");
            })
            .always(function () {
                console.log("complete");
            });

    });


</script>

@endsection
