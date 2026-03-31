@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true" autocomplete="off">
    @csrf

    <input type="hidden" name="branchId" id="branchId">
    <input type="hidden" name="samityId" id="samityId">

    <div class="row">
        <div class="col-lg-7 offset-md-1">

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

            <div id="accTableDivId" style="display: none;">
                <table class="table table-bordered table-striped" id="accTableId">
                    <thead>
                        <tr>
                            <th>SL#</th>
                            <th>Saving Acc.</th>
                            <th>Saving Balance</th>
                            <th></th>
                        </tr>
                    </thead>
                </table>
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
    var flag = true;

    $(document).ready(function () {


        /* member selectize */
        selectizeMember(<?= json_encode($members) ?>);

        $('#memberId').data('selectize').setValue(<?= $memberId ?>);
        /* end member selectize */
    });

    function selectizeMember(options){

        $('#memberId').selectize({
            valueField: 'id',
            labelField: 'member',
            searchField: ['name', 'memberCode'],
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
            options: options ,
            create: false,
            render: {
                option: function (member, escape) {

                    return '<div>' +
                        '<span class="lebel">' + member.name + ' - ' + member.memberCode + '</span> <br>' +
                        '<span>Branch: ' + member.branch + '</span> <br>' +
                        '<span>Samity: ' + member.samity + '</span> <br>' +
                        '<span>Working Area: ' + member.workingArea + '</span> ' +
                        '</div>';

                }
            }
        });
    }

    /* get loan accounts */
    $("#memberId").change(function (e) {
        
        $('#loanId option:gt(0)').remove();
        $('#accTableDivId').hide('fast');
        $('#accTableDivId tbody').remove();
        $(".savInfo").html('');


        var memberId = $(this).val();

        if(memberId == ''){
            return false;
        }

        $.ajax({
            type: "POST",
            url: "./../getData",
            data: {context : 'member', isEdit: 'edit', memberId : memberId},
            dataType: "json",
            success: function (response) {
                $.each(response['loans'], function (index, value) { 
                    $('#loanId').append("<option value="+index+">"+value+"</option>");
                });

                $('#accTableId').append(response['savingTable']);
                
                if (flag == true) {
                    fnCeckAccBox();

                    $('#loanId option[value="' + {{ $loanId }} + '"]').prop('selected', true);
                    fnLoanChange();

                    flag = false;
                }
            },
            error: function(){
                alert('error!');
            }
        });
    });
    /* end get loan accounts */

    /* get loan information */
    $("#loanId").change(function (e) { 
        fnLoanChange();
    });
    /* end get loan information */

    function fnLoanChange() {
        var loanId = $('#loanId').val();

        $(".savInfo").html('');

        if(loanId == ''){
            return false;
        }

        $.ajax({
            type: "POST",
            url: "./../getData",
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
                // $('#total_advance').html(response.total_advance);
                // $('#pricipal_advance').html(response.pricipal_advance);
                // $('#interest_advance').html(response.interest_advance);
                // $('#total_due').html(response.total_due);
                // $('#pricipal_due').html(response.pricipal_due);
                // $('#interest_due').html(response.interest_due);
                $('#total_rebateable').html(response.total_rebateable);
                $('#pricipal_rebateable').html(response.pricipal_rebateable);
                $('#interest_rebateable').html(response.interest_rebateable);

                $('#samityId').val(response.samityId);
                $('#branchId').val(response.branchId);

                if ( $('#loanId').val() == {{ $loanId }}) {
                    fnCeckAccBox();
                }

                $('#accTableDivId').show('fast');
            },
            error: function(){
                alert('error!');
            }
        });
    }

    function fnCeckAccBox() {

        @if(count($accId) > 1)

            @foreach($accId as $key => $value)

                $('#accTableId input:checkbox[value='+ <?= $value ?> +']').prop('checked', true);

                $('#accTableId > tbody > tr').each(function(){

                    var accId = $(this).find('td:eq(2) input:hidden').val();

                    if(accId == {{ $value }}) {
                        $(this).find('td:last-child input:text').prop('readOnly', false);
                        $(this).find('td:last-child input:text').val(<?= $accAmount[$key] ?>);
                    }
                });
            @endforeach
        @else
            $('#accTableId input:checkbox[value='+ <?= $accId[0] ?> +']').prop('checked', true);
            
            $('#accTableId > tbody > tr').each(function(){

                var accId = $(this).find('td:eq(2) input:hidden').val();

                if(accId == {{ $accId[0] }}) {
                    $(this).find('td:last-child input:text').prop('readOnly', false);
                    $(this).find('td:last-child input:text').val(<?= $accAmount[0] ?>);
                }
            });
        @endif
    }

    function fnEnterAmount(row) {

        if ($('#accId_' + row).is(':checked')) {
            $('#amount_' + row).prop('readOnly', false);
        }
        else{
            $('#amount_' + row).prop('readOnly', true);
        }

    }

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
                        window.location = './../'
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
