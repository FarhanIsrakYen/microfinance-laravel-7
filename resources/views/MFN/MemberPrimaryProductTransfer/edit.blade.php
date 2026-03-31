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
            <input type="hidden" name="id" id="t_id" value="{{ encrypt($TransferData->id) }}">
            <input id="oldProductId" type="hidden" class="form-control round" name="oldProductId" value="{{$TransferData->oldProductId}}">
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Member</label>
                <div class="col-lg-7">
                    <div class="input-group">

                     <input id="oldProductId" type="hidden" class="form-control round" name="memberId" value="{{$TransferData->memberId}}">
                        <select class="form-control clsSelect2"
                        id="member_id" required data-error="Please Select Member" disabled>
                        <option value="">Select Member</option>
                        @foreach($members as $row)
                        <option value="{{ $row->id }}" {{($row->id==$TransferData->memberId)? 'selected': ''}}>{{ $row->name.' ('.$row->memberCode.')'}}</option>
                        @endforeach
                            
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">New Primary Product</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="newProductId"
                        id="newProductId" required data-error="Please Select Primary Product">
                            <option value="">Select</option>
                          
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
                            name="transferDate" required data-error="Please Select Date" 
                            value="{{ \Carbon\Carbon::parse($TransferData->transferDate)->format('d-m-Y') }}" readonly>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Note</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <textarea class="form-control round" id="note" name="note" rows="2"
                        placeholder="Enter Address"></textarea>
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
                <tbody>
                    <tr>
                        <td><h6>Member Name</h6></td>
                        <td class="infoMemberName"> </td>
                    </tr>
                    <tr>
                        <td><h6>Samity Name</h6></td>
                        <td class="infoSamityName"></td>
                    </tr>
                    <tr>
                        <td><h6>Current Primary Product</h6></td>
                        <td class="infoCurrentProductName"></td>
                    </tr>
                </tbody>
            </table>


            <!-- Savings Information Summary-->
            <h5>Savings Information</h4>
            <table style="width:100%" class="table table-striped table-bordered loan-saving-summary-table right">
                <thead>
                    <tr>
                        <th width="40%">Savings Code</th>
                        <th>Desposit</th>
                        <th>Withdraw</th>
                        <th>Balance</th>
                    </tr>
                </thead>
                <tbody  id="savingsSummary">
                    {{-- <tr>
                        <td>WS.001.0010.00023.001</td>
                        <td>116855</td>
                        <td>104500</td>
                        <td>12355</td>
                    </tr> --}}
                </tbody>
            </table>

        </div>
    </div>
</form>

<script type="text/javascript">

    $( document ).ready(function() {

        $('form').submit(function (event) {
            event.preventDefault();

            //disable Multiple Click
            $(this).find(':submit').attr('disabled', 'disabled');

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

          /* member selectize */

   


       

        /* get savings accounts */
        $("#member_id").change(function (e) {
            // $('#accountId option:gt(0)').remove();

            var member_id = $(this).val();
            var t_id = $('#t_id').val();
            

            if(member_id == ''){
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./../getData",
                data: {context : 'product_for_edit', member_id : member_id , t_id : t_id},
                dataType: "json",
                success: function (response) {
                    $('#savingsSummary').empty();
                    $('#newProductId').empty();
                    $('#newProductId').append("<option value=''>Select</option>");

                    $.each(response.product, function (index, value) { 
                    // console.log(value);

                        $('#newProductId').append("<option value="+value.id+">"+value.name+"</option>");
                    });
                    // $('#' + TableID + ' tbody').find('tr:first').after(html);
                    $.each(response.savingsACC, function (index, value) { 
                    // console.log(value);
                    $('#savingsSummary').append("<tr> <td>"+value.accountCode+"</td><td>"+value.diposite+"</td><td>"+value.withdraw+"</td><td>"+value.balance+"</td></tr>");
                        // $('#newProductId').append();
                    });

                    $('.infoMemberName').html(response.memberdata.member);
                    $('.infoSamityName').html(response.memberdata.samity);
                    $('.infoCurrentProductName').html(response.primaryProduct.name);
                    // $('#oldProductId').val(response.memberdata.primaryProductId);
                    $('#summaryTable').show();

                },
                error: function(){
                    alert('error!');
                }
            });

            
        });

        // // Make Summary Table Visible On selecting Member
        // $('#member_id').change(function() {
        //     if ($('#member_id').val() != '') {
                
        //     }
        //     else{
        //         $('#summaryTable').hide();
        //     }
        // });
        $("#member_id").trigger('change');
        // $("#").trigger();

    });
</script>


@endsection
