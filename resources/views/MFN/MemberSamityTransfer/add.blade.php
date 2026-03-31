@extends('Layouts.erp_master')

@section('content')

<?php
    $sysDate = \App\Services\MfnService::systemCurrentDate(Auth::user()->branch_id);
    $sysDate = \Carbon\Carbon::parse($sysDate)->format('d-m-Y');
?>

<form enctype="multipart/form-data" method="post" class="form-horizontal" 
    data-toggle="validator" novalidate="true" autocomplete="off"> 
    @csrf    

    <div class="row">

        <!-- Left Input Field -->
        <div class="col-lg-6">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Current Samity</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="currentSamity" 
                        id="currentSamity" required data-error="Please Select Samity">
                            <option value="">Select</option>
                            @foreach ($samities as $samity)
                            <option value="{{ $samity->id }}">{{ $samity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Member</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="memberName" 
                        id="memberName" required data-error="Please Select Member">
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">New Samity</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="newSamity" 
                        id="newSamity" required data-error="Please Select Samity">
                            <option value="">Select</option>
                            @foreach ($samities as $samity)
                            <option value="{{ $samity->id }}">{{ $samity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Transfer Date</label>
                <div class="col-lg-7">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control round" id="transferDate"
                            name="transferDate" value="{{ $sysDate }}" readonly>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>
            
            <div class="form-row align-items-center">
                <div class="col-lg-4"></div>
                <div class="col-lg-7">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round d-print-none">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Save</button>
                            <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side Summary Table -->
        <div class="col-lg-6" id="summaryTable" style="display: none;">
            <!-- Member's Information Summary-->
            <h5>Member's Information</h4>
            <table id="memberSummary" class="table table-striped table-bordered">
                
            </table>

            <!-- Loan Information Summary-->
            <h5>Loan Information</h4>
            <table id="loanSummary" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th width="20%">Loan No</th>
                        <th class="text-right">Loan Amount</th>
                        <th class="text-right">Total Payable Amount</th>
                        <th>Last Repayment Date</th>
                        <th class="text-right">Outstanding Amount</th>
                    </tr>
                </thead>
                <tbody id="loanSummaryBody">
                    
                </tbody>
            </table>

            <!-- Savings Information Summary-->
            <h5>Savings Information</h4>
            <table id="savingsSummary" style="width:100%" class="table table-striped table-bordered loan-saving-summary-table right">
                <thead>
                    <tr>
                        <th width="40%">Savings Code</th>
                        {{-- <th>Opening Date</th> --}}
                        <th class="text-right">Desposit</th>
                        <th class="text-right">Withdraw</th>
                        <th class="text-right">Balance</th>
                    </tr>
                </thead>
                <tbody id="savingsSummaryBody">
                    
                </tbody>
            </table>

        </div>
    </div>

</form>
    
<script type="text/javascript">

    $( document ).ready(function() {

        // Disable Multiple Click
        $('form').submit(function (event) {
            $(this).find(':submit').attr('disabled', 'disabled');
        });

    });

    /* get members for this somnity */
    $("#currentSamity").change(function (e) {
            // $('#accountId option:gt(0)').remove();
            $('#memberName option:gt(0)').remove();

            var samity_id = $(this).val();

            if(samity_id == ''){
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./getData",
                data: {samity_id : samity_id},
                dataType: "json",
                success: function (response) {
                    $('#summaryTable').hide();
                    $.each(response.members, function (index, value) { 
                        $('#memberName').append(`<option value='${value.id}''>${value.name} - ${value.memberCode}</option>`);
                    });
                },
                error: function(){
                    alert('error!');
                }
            });
        });


    /* get members for this somnity */
    $("#memberName").change(function (e) {
            var member_id = $(this).val();

            if(member_id == ''){
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./getData",
                data: {
                    member_id : member_id,
                    transferDate: $('#transferDate').val()
                },
                dataType: "json",
                success: function (response) {
                    console.log(response);
                    if ($('#memberName').val() != '') {
                        $('#summaryTable').show();
                    }
                    else{
                        $('#summaryTable').hide();
                    }
                    populateMemberInfoTable(response.memberdata, response.savingsACC, response.loanproduct)
                 
                },
                error: function(){
                    alert('error!');
                }
            });
        });

    $('#newSamity').change(function (e){
        var newSamity = $(this).val();
        var currentSamity = $('#currentSamity').val();
        if(newSamity == currentSamity){
            swal({
                icon: 'error',
                title: 'Oops...',
                text: 'New Samity can not be Current Samity'
            });

            $("#newSamity").val($("#newSamity option:first").val());
        }
    });
    
    function populateMemberInfoTable(member, savingsACC, loanproduct){
        context = `<tbody>
                <tr>
                    <td><h6>Member Name</h6></td>
                    <td class="memberName"> ${member.member} </td>
                </tr>
                <tr>
                    <td><h6>Branch Name</h6></td>
                    <td class="branchName"> ${member.branch} </td>
                </tr>
                <tr>
                    <td><h6>Samity Name</h6></td>
                    <td class="samityName"> ${member.samity} </td>
                </tr>
                <tr>
                    <td><h6>Current Primary Product</h6></td>
                    <td class="currentProductName"> ${member.LoanProduct} </td> 
                </tr>
            </tbody>`;
        $('#memberSummary').html(context);

        context='';
        if(savingsACC.length == 0){
            context=`<tr><td colspan="4" class="text-center">Savings Data found.</td></tr>`;

        }
        else{
            for(let i=0; i<savingsACC.length; i++){
                context += `<tr>
                            <td class="text-center">${savingsACC[i].accountCode}</td>
                            <td class="text-right">${savingsACC[i].diposite}</td>
                            <td class="text-right">${savingsACC[i].withdraw}</td>
                            <td class="text-right">${savingsACC[i].balance}</td>
                        </tr>`;
            }
        }
        $('#savingsSummaryBody').html(context);


        context='';
        if(loanproduct.length == 0){
            context=`<tr><td colspan="5" class="text-center">Loan Data found.</td></tr>`;

        }
        else{
            //need to change the fields
            for(let i=0; i<loanproduct.length; i++){
                context += `<tr>
                            <td class="text-center">${loanproduct[i].loanCode}</td>
                            <td class="text-right">${loanproduct[i].loanAmount}</td>
                            <td class="text-right">${loanproduct[i].repayAmount}</td>
                            <td class="text-center">${loanproduct[i].lastInastallmentDate}</td>
                            <td class="text-right">${loanproduct[i].outstanding}</td>
                        </tr>`;
            }
        }
        $('#loanSummaryBody').html(context);
    }

    $('form').submit(function (event) {
            event.preventDefault();

            //disable Multiple Click
            $(this).find(':submit').attr('disabled', 'disabled');

            $.ajax({
                    url: "./add",
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
