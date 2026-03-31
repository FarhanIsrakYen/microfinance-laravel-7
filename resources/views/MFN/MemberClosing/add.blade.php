@extends('Layouts.erp_master')

@section('content')

<?php 
    if (Auth::user()->branch_id == 1){
        $branchName = '';
    }

    else {
        $branchName = $branch->branch_name;
        $sysDate = (new Datetime(\App\Services\MfnService::systemCurrentDate(Auth::user()->branch_id)))->format('d-m-Y');
    }
?>

<form enctype="multipart/form-data" method="post" class="form-horizontal" 
    data-toggle="validator" novalidate="true" autocomplete="off"> 
    @csrf    

    <div class="row">
        <div class="col-lg-6">

            @if(Auth::user()->branch_id == 1)
                <div class="form-row align-items-center">
                    <label class="col-lg-4 input-title">Branch</label>
                    <div class="col-lg-7">
                        <div class="form-group">
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="branch_id" id="branch_id" 
                                onchange="fnAjaxSelectBox(
                                                'samity_id',
                                                this.value,
                                    '{{base64_encode('mfn_samity')}}',
                                    '{{base64_encode('branchId')}}',
                                    '{{base64_encode('id,name')}}',
                                    '{{url('/ajaxSelectBox')}}',
                                    '{{null}}',
                                    '{{'isActiveOff'}}'
                                            );">
                                    <option value="">Select Option</option>
                                    @foreach ($branchList as $branch)
                                        <option value="{{ $branch->id }}">
                                            {{ sprintf("%04d", $branch->branch_code) . "-" . $branch->branch_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row align-items-center">
                    <label class="col-lg-4 input-title">Samity</label>
                    <div class="col-lg-7">
                        <div class="form-group">
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="samity_id" id="samity_id"
                                onchange="fnAjaxSelectBoxForMember(
                                                    'member_id',
                                                    this.value,
                                        '{{base64_encode('mfn_members')}}',
                                        '{{base64_encode('samityId')}}',
                                        '{{base64_encode('id,name')}}',
                                        '{{url('/ajaxSelectBoxForMember')}}'
                                                );">
                                    <option value="">Select Option</option>
                                    <!-- @foreach ($samityList as $samity)
                                        <option value="{{ $samity->id }}">{{ $samity->samityCode. ' - ' . $samity->name }}</option>
                                    @endforeach -->
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <div class="form-row align-items-center">
                    <label class="col-lg-4 input-title">Samity</label>
                    <div class="col-lg-7">
                        <div class="form-group">
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="samity_id" id="samity_id"
                                onchange="fnAjaxSelectBoxForMember(
                                                    'member_id',
                                                    this.value,
                                        '{{base64_encode('mfn_members')}}',
                                        '{{base64_encode('samityId')}}',
                                        '{{base64_encode('id,name,memberCode')}}',
                                        '{{url('/ajaxSelectBoxForMember')}}'
                                                );">
                                    <option value="">Select Option</option>
                                    @foreach ($samityList as $samity)
                                        <option value="{{ $samity->id }}">{{ $samity->samityCode. ' - ' . $samity->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">member</label>
                <div class="col-lg-7">
                    <div class="form-group">
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="member_id" id="member_id"
                            onchange="fnAjaxSelectBoxForMemberDetails(
                                    this.value,
                                    '{{url('/ajaxSelectBoxForMemberDetails')}}'
                                            );">
                                <option value="">Select Option</option>
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Member Closing Date</label>
                <div class="col-lg-7">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                </span>
                            </div>
                            @if(Auth::user()->branch_id == 1)
                            <input type="text" class="form-control round" id="closingDate" name="closingDate" readonly>
                            @else
                            <input type="text" class="form-control round" id="closingDate" name="closingDate" value="{{ $sysDate }}" readonly>
                            @endif
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
                        placeholder="Write Note"></textarea>
                    </div>
                </div>
            </div>
            
        </div>
        <div class="col-lg-6 memberInfo" style="display: none">
            <h5>Member's Information</h5>
            <table class="table table-striped table-bordered">
                <tbody>
                    <tr>
                        <td width="50%">Member Name</td>
                        <td width="50%" id="member_name"></td>
                    </tr>
                    <tr>
                        <td width="50%">Branch Name</td>
                        <td width="50%" id="branch_name"></td>
                    </tr>
                    <tr>
                        <td width="50%">Samity Name</td>
                        <td width="50%" id="samity_name"></td>
                    </tr>
                    <tr>
                        <td width="50%">Current Primary Product</td>
                        <td width="50%" id="product_name"></td>
                    </tr>
                    <tr>
                        <td width="50%">Working Area</td>
                        <td width="50%" id="working_area"></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row savingsInfo" style="display: none">
        <div class="col-lg-12">
            <p id="loanSummaryTitle"></p>
            <table id="loanSummary" class="table table-striped table-bordered loan-saving-summary-table right">
                <thead></thead>
                <tbody></tbody>
            </table>
            <p id="savingsSummaryTitle" style="color: rgb(0, 0, 0);">Savings Information:</p>
            <table id="savingsSummary"  class="table table-striped table-bordered loan-saving-summary-table right">
                <thead>
                    <tr>
                        <th width="15%">Savings Code</th>
                        <th width="15%">Product</th>
                        <th width="12%">Opening Date</th>
                        <th>Total Desposit</th>
                        <th>Total Withdraw</th>
                        <th>Savings Balance</th>
                        {{-- <th>Interest Amount</th>
                        <th width="10%">Payment Mode</th>
                        <th width="10%">Bank List</th>
                        <th>Cheque No</th> --}}
                    </tr>
                </thead>
                <tbody>
                </tbody>
            </table>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                        class="btn btn-default btn-round d-print-none">Back</a>
                    <button type="submit" class="btn btn-primary btn-round disabled">Save</button>
                    <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    var closingBL = 0;
    function ajaxDataLoad(data){
        $("#savingsSummary tbody").html('');
        var html = '';
        $.each(data.savings, function( key, obj  ) {
            html += '<tr>';
            html += '<td>'+obj.accountCode+'</td>';
            html += '<td>'+obj.savingsProduct+'</td>';
            html += '<td>'+obj.openingDate+'</td>';
            html += '<td class="text-right">'+obj.totalDeposit+'</td>';
            html += '<td class="text-right">'+obj.totalWithdraw+'</td>';
            html += '<td class="text-right">'+obj.savingsBalance+'</td>';
            // html += '<td><input class="form-control" type="text" name="interestAmount[]" value="0"></td>';
            // html += '<td><select class="form-control paymentTypeId clsSelect2" name="paymentTypeId[]">';
            // html += '<option value="cash">Cash</option>';
            // html += '<option value="bank">Bank</option></select></td>';
            // html += '<td><select class="form-control ledgerId clsSelect2" name="ledgerId[]">';
            // html += '<option value="">Select</option></select></td>';
            // html += '<td><input class="form-control chequeNo" type="text" name="chequeNo[]" readonly=""></td>';
            html += '</tr>'; 
            closingBL += parseFloat(obj.savingsBalance);
        });
        $("#savingsSummary tbody").append(html);
    }

    var samityID = '';
    var branchID = '';
    var branchName = '<?php echo $branchName; ?>';
    var samityName = '';
    var memberName = '';
    $( document ).ready(function() {

        $('#branch_id').change(function() {
            branchName = $('#branch_id option:selected').text();
            branchID = $(this).val();
            $.ajax({
                method: "GET",
                url: "{{url('/ajaxGetSysDate')}}",
                dataType: "text",
                data: {branchID: branchID},
                success: function (data) {
                    if (data) {
                        sysDate = new Date (data);
                        sysDate = sysDate.getDate() + "-" + ( '0' + (sysDate.getMonth()+1) ).slice( -2 ) + "-" + sysDate.getFullYear();
                        $('#closingDate').val(sysDate);
                    }
                }
            });
        });

        $('#samity_id').change(function() {
            samityName = $('#samity_id option:selected').text();
            samityID = $(this).val();
        });

        $('#member_id').change(function() {
            memberName = $('#member_id option:selected').text();
            $('.savingsInfo').show('slow');
        });

        // Disable Multiple Click
        $('form').submit(function (event) {
            event.preventDefault();
            $(this).find(':submit').attr('disabled', 'disabled');

            $.ajax({
                    url: "{{ url()->current() }}",
                    type: 'POST',
                    dataType: 'json',
                    data: $('form').serialize() +"&closingBL="+closingBL,
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
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function () {
                            window.location.href = "{{ url('mfn/memberclosing/') }}";
                        });
                    }

                })
                .fail(function () {
                    console.log("error");
                })
                .always(function () {
                    console.log("complete");
                });

        });

       
    });

    function fnAjaxSelectBoxForMember(FeildID = null, FeildVal = null, TableName = null, WhereColumn = null, SelectColumn = null, URL = null, SelectedVal = null) {

        if (FeildID != null && FeildVal != null && TableName != null && WhereColumn != null && SelectColumn != null) {

            $.ajax({
                method: "GET",
                url: URL,
                dataType: "text",
                data: {FeildVal: FeildVal, TableName: TableName, WhereColumn: WhereColumn, SelectColumn: SelectColumn, SelectedVal: SelectedVal},
                success: function (data) {
                    if (data) {
                        $('#' + FeildID)
                                .empty()
                                .html(data);
                                // .trigger('change');
                                //                    .selectpicker('refresh');
                    }
                }
            });
        }
    }
    function fnAjaxSelectBoxForMemberDetails(FeildID = null, URL = null) {
        $.ajax({
            method: "GET",
            url: URL,
            dataType: "json",
            data: {memberID: FeildID, samityID :samityID},
            success: function (data) {
                if (data) {
                    $('#member_name').html(memberName);
                    $('#branch_name').html(branchName);
                    $('#samity_name').html(samityName);
                    $('#product_name').html(data.productName);
                    $('#working_area').html(data.workingArea);
                    $('.memberInfo').show('slow');
                    ajaxDataLoad(data);
                }
            }
        });
    }
    
</script>


@endsection
