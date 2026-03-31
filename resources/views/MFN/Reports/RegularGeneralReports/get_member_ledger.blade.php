@extends('Layouts.erp_master_full_width')
@section('content')
<script type="text/javascript" src="{{ asset('assets/js/pdf/tableExport.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/jspdf.plugin.autotable.js') }}"></script>
<script type="text/javascript" src="{{ asset('assets/js/pdf/xlsx.core.min.js') }}"></script>

<!-- Page -->
<?php
use App\Services\CommonService as Common;
$branchId = Auth::user()->branch_id;
$branchInfo = Common::ViewTableFirst('gnl_branchs',
    [['is_delete', 0], ['is_active', 1],
        ['id', Auth::user()->branch_id]],
    ['id', 'branch_name']);
$groupInfo = Common::ViewTableFirst('gnl_groups',
    [['is_delete', 0], ['is_active', 1]],
    ['id', 'group_name']);
?>

<div class="panel">
    <div class="panel-body">
        <div class="row align-items-center pb-10 d-print-none">
            @if($branchId == 1)
            <div class="col-lg-2">
                <label class="input-title">Branch</label>
                <div class="input-group">
                        <select class="form-control clsSelect2" name="branch_id" id="branch_id">
                        <option value="">Select All</option>
                        @foreach ($branchData as $row)
                        <option value="{{ $row->id }}">
                            {{ sprintf("%04d", $row->branch_code) . "-" . $row->branch_name }}
                        </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Samity</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="samity_id" id="samity_id">
                        <option value="">All</option>
                    </select>
                </div>
            </div>

            @else
            <div class="col-lg-2">
                <label class="input-title">Samity</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="samity_id" id="samity_id">
                        <option value="">All</option>
                        {{-- @foreach ($samityData as $row)
                        <option value="{{ $row->id }}">{{  $row->name }}</option>
                        @endforeach --}}
                    </select>
                </div>
            </div>
            @endif

            <div class="col-lg-2">
                <label class="input-title">Member</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="member_id" id="member_id">
                        <option value="">All</option>
                    </select>
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Loan Product</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="loan_product" id="loan_product">
                        <option value="">All</option>
                        @foreach ($loanProductData as $row)
                        <option value="{{ $row->id }}">{{  $row->shortName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Loan Account</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="loan_account" id="loan_account">
                        <option value="">All</option>
                        @foreach ($loanAccountData as $row)
                        <option value="{{ $row->id }}">{{  $row->loanCode }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Savings Product</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="savings_product" id="savings_product">
                        <option value="">All</option>
                        @foreach ($savingsProductData as $row)
                        <option value="{{ $row->id }}">{{  $row->shortName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

        </div>
        <div class="row d-print-none">

            <div class="col-lg-2">
                <label class="input-title">Savings Account</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="savings_account" id="savings_account">
                        <option value="">All</option>
                        @foreach ($savingsAccountData as $row)
                        <option value="{{ $row->id }}">{{  $row->accountCode }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Date From</label>
                <div class="input-group">
                    <input type="text" class="form-control datepicker-custom" id="start_date" name="start_date"
                        placeholder="DD-MM-YYYY" value="" autocomplete="off">
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Date To</label>
                <div class="input-group">
                    <input type="text" class="form-control datepicker-custom" id="end_date" name="end_date"
                        placeholder="DD-MM-YYYY" value="" autocomplete="off">
                </div>
            </div>

        </div>
        <div class="row d-print-none">
            <div class="col-lg-12 pt-20 text-center">
                <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                    id="searchButton">Search</a>
            </div>
        </div>
        <div class="row text-center d-print-block mt-4" id="reportTitle" style="display: none">
            <div class="col-lg-12" style="color:#000;">
                <strong>{{ $groupInfo->group_name }}</strong><br>
                <strong>{{ $branchInfo->branch_name }}</strong><br>
                <span>Member Ledger Report</span><br>
            </div>
        </div>
        <div class="row d-print-none text-right" style="display: none" id="printPDF">
            <div class="col-lg-12">
                <a href="javascript:void(0)" onClick="window.print();" class="btnPrint mr-2">
                    <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                </a>
                <a href="javascript:void(0)" onclick="fnDownloadPDF();">
                    <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
                </a>
                <a href="javascript:void(0)" onclick="fnDownloadExcel();">
                    <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                </a>
            </div>
        </div>
        <div class="row" id="ttlRow" style="display: none">
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                <b>Member Name:</b> <span id="memberName"></span> <br>
                <b>Spouse Name:</b> <span id="spouseName"></span> <br>
                <b>Mobile Number:</b> <span id="mobileNo"></span> <br>
                <b>Branch Name:</b> <span id="branchName"></span> <br>
                <b>Samity Name:</b> <span id="samityName"></span> <br>
            </div>
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                <span><b>Printed Date:</b> {{ (new Datetime())->format('d-m-Y') }} </span>
            </div>
        </div>
        <div class="row" id="table1" style="display: none">
            <div class="col-lg-12">
                <h5 class="text-center">Savings Details</h5>
                <table class="table w-full table-hover table-bordered table-striped savingsDataTable">
                    <thead class="text-center">
                        <tr>
                            <th width="5%">SL</th>
                            <th>Date</th>
                            <th>Account Code</th>
                            <th>Product</th>
                            <th>Deposit Amount</th>
                            <th>Withdraw Amount</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody id="savingsDataTable">
                    </tbody>
                    <tfoot>
                        <tr>
                            <td colspan="4" class="text-center"><b>Total:</b></td>
                            <td class="text-right"><b id="ttl_deposit_amount">0.00</b></td>
                            <td class="text-right"><b id="ttl_withdraw_amount">0.00</b></td>
                            <td class="text-right"><b id="ttl_balance_sav">0.00</b></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        <div class="row mt-4" id="table2" style="display: none">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <h5 class="text-center">Loan Details</h5>
                    <table class="table w-full table-hover table-bordered table-striped loanDataTble">
                        <thead class="text-center">
                            <tr>
                                <th width="5%" rowspan="2">SL</th>
                                <th rowspan="2">Date</th>
                                <th rowspan="2">Account</th>
                                <th rowspan="2">Product</th>
                                <th rowspan="2">Disburse Amount</th>
                                <th colspan="3">Collection Amount</th>
                                <th rowspan="2">Rebate</th>
                                <th rowspan="2">Outstanding</th>
                            </tr>
                            <tr>
                                <th>Principal Amount</th>
                                <th>Interest Amount</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody  id="loanDataTable">
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-center"><b>Total:</b></td>
                                <td class="text-right"><b id="ttl_disburse_amount">0.00</b></td>
                                <td class="text-right"><b id="ttl_principal_amount">0.00</b></td>
                                <td class="text-right"><b id="ttl_interest_amount">0.00</b></td>
                                <td class="text-right"><b id="ttl_collection_amount">0.00</b></td>
                                <td class="text-right"><b id="ttl_rebate_amount">0.00</b></td>
                                <td class="text-right"><b id="ttl_outstanding">0.00</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- End Page -->

<script>
function ajaxSavingsDataLoad(branch_id = null, samity_id = null, member_id = null, start_date = null, 
    end_date = null, loan_product = null, loan_account = null, savings_product = null, savings_account = null) {
    $("#savingsDataTable").empty();
    $.ajax({
        method: "GET",
        dataType: "json",
        url: "{{route('memberLedgerDataTable')}}",
        data: {
                _token: "{{ csrf_token() }}",
                branchId: branch_id,
                memberId: member_id,
                samityId: samity_id,
                startDate: start_date,
                endDate: end_date,
                loanProduct: loan_product,
                loanAccount: loan_account,
                savingsProduct: savings_product,
                savingsAccount: savings_account
            },
        success: function (data) {
            var html = '';
            var sl = parseInt(0);
            $.each(data.savingsData, function( key, obj) {

                sl = parseInt(sl) + 1;

                html += '<tr>';
                html += '<td class="text-center" rowspan="' + obj.length +' ">' + sl +'</td>';
                html += '<td>'+obj.date+'</td>';
                html += '<td>'+obj.accountCode+'</td>';
                html += '<td>'+obj.product+'</td>';
                html += '<td class="text-right">'+ parseFloat(obj.depositAmount).toFixed(2)+'</td>';
                html += '<td class="text-right">'+ parseFloat(obj.withdrawAmount).toFixed(2) +'</td>';
                html += '<td class="text-right">'+ parseFloat(obj.currentBL).toFixed(2) +'</td>';
                html += '</tr>';
                
            });

            $("#savingsDataTable").append(html);
            $('#ttl_deposit_amount').html(data.ttl_deposit_amount);
            $('#ttl_withdraw_amount').html(data.ttl_withdraw_amount);
            $('#ttl_balance_sav').html(data.ttl_balance_sav);

        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert('No Match Found');
        }
    });  
}

function ajaxLoanDataLoad(branch_id = null, samity_id = null, member_id = null, start_date = null, 
    end_date = null, loan_product = null, loan_account = null, savings_product = null, savings_account = null) {
    $("#loanDataTable").empty();
    $.ajax({
        method: "GET",
        dataType: "json",
        url: "{{route('memberLedgerDataTable')}}",
        data: {
                _token: "{{ csrf_token() }}",
                branchId: branch_id,
                samityId: samity_id,
                memberId: member_id,
                startDate: start_date,
                endDate: end_date,
                loanProduct: loan_product,
                loanAccount: loan_account,
                savingsProduct: savings_product,
                savingsAccount: savings_account
            },
        success: function (data) {
            var html = '';
            var sl = parseInt(0);
            $.each(data.loanData, function( key, obj) {
                sl = parseInt(sl) + 1;

                html += '<tr>';
                html += '<td class="text-center" rowspan="' + obj.length +' ">' + sl +'</td>';
                html += '<td>'+obj.date+'</td>';
                html += '<td>'+obj.loanCode+'</td>';
                html += '<td>'+obj.product+'</td>';
                html += '<td class="text-right">'+ parseFloat(obj.disburseAmount).toFixed(2) +'</td>';
                html += '<td class="text-right">'+ parseFloat(obj.principalAmount).toFixed(2) +'</td>';
                html += '<td class="text-right">'+ parseFloat(obj.interestAmount).toFixed(2) +'</td>';
                html += '<td class="text-right">'+ parseFloat(obj.totalAmount).toFixed(2) +'</td>';
                html += '<td class="text-right">'+ parseFloat(obj.rebate).toFixed(2) +'</td>';
                html += '<td class="text-right">'+ parseFloat(obj.outstanding).toFixed(2) +'</td>';
                html += '</tr>'; 
            });

            $("#loanDataTable").append(html);
            $('#ttl_disburse_amount').html(data.ttl_disburse_amount);
            $('#ttl_principal_amount').html(data.ttl_principal_amount);
            $('#ttl_interest_amount').html(data.ttl_interest_amount);
            $('#ttl_collection_amount').html(data.ttl_collection_amount);
            $('#ttl_rebate_amount').html(data.ttl_rebate_amount);
            $('#ttl_outstanding').html(data.ttl_outstanding);

        },
        error: function (XMLHttpRequest, textStatus, errorThrown) {
            alert('No Match Found');
        }
    });  
}

$(document).ready(function() {

    $('.clsSelect2').css("width","100%");

    $('#start_date').datepicker({
        dateFormat: 'dd-mm-yy',
        orientation: 'bottom',
        autoclose: true,
        todayHighlight: true,
        changeMonth: true,
        changeYear: true,
        yearRange: '1900:+10',
        onClose: function (selectedDate) {
            $("#end_date").datepicker("option", "minDate", selectedDate);
        }
    });

    $("#end_date").datepicker({
        dateFormat: 'dd-mm-yy',
        orientation: 'bottom',
        autoclose: true,
        todayHighlight: true,
        changeMonth: true,
        changeYear: true,
        yearRange: '1900:+10',
        onClose: function (selectedDate) {
            $("#start_date").datepicker("option", "maxDate", selectedDate);
        }
    });

    var branch_id = '';
    $('#branch_id').change(function() {
        branch_id = $(this).val();
        var branchName = $('#branch_id option:selected').text().split('-');
        $('#branchName').html(branchName[1]);
        $.ajax({
            type: "POST",
            url: "../getSamities",
            data: {branchId : branch_id},
            dataType: "json",
            success: function (samities) {
                $('#samity_id').empty();
                $('#samity_id').append("<option value=''>"+ 'All'+"</option>");
                if (samities != '') {
                    $.each(samities, function (index, samity) {
                        $('#samity_id').append("<option value="+samity.id+">"+samity.name+"</option>");
                    });
                }
                
            },
            error: function(){
                alert('error!');
            }
        });
    });

    var samity_id = '';
    $('#samity_id').change(function() {
        samity_id = $(this).val();
        var samityName = $('#samity_id option:selected').text().split('-');
        $('#samityName').html(samityName[1]);

        $.ajax({
            type: "POST",
            url: "../getMember",
            data: {samityId : samity_id},
            dataType: "json",
            success: function (members) {
                $('#member_id').empty();
                $('#member_id').append("<option value=''>"+ 'All'+"</option>");
                if (members != '') {
                    $.each(members, function (index, member) {
                        $('#member_id').append("<option value="+member.id+">" + member.memberCode + ' - ' + member.name +"</option>");
                    });
                }
                
            },
            error: function(){
                alert('error!');
            }
        });
    });

    var member_id = '';
    $('#member_id').change(function() {
        member_id = $(this).val();
        memberName = $('#member_id option:selected').text().split('-');
        $('#memberName').html(memberName[1]);
        
        $.ajax({
            type: "POST",
            url: "../getMemberDetails",
            data: {memberId : member_id},
            dataType: "json",
            success: function (memberDetails) {
                $('#spouseName').html(memberDetails.spouseName);
                $('#mobileNo').html(memberDetails.mobileNo);
            },
            error: function(){
                alert('error!');
            }
        });
    });
    
    var loan_product = '';
    $('#loan_product').change(function() {
        loan_product = $(this).val();

        $.ajax({
            type: "POST",
            url: "../getLoanAccounts",
            data: {productId : loan_product},
            dataType: "json",
            success: function (loanAccounts) {
                $('#loan_account').empty();
                $('#loan_account').append("<option value=''>"+ 'All'+"</option>");
                if (loanAccounts != '') {
                    $.each(loanAccounts, function (index, loanAccount) {
                        $('#loan_account').append("<option value="+loanAccount.id+">" + loanAccount.loanCode  +"</option>");
                    });
                }
                
            },
            error: function(){
                alert('error!');
            }
        });
    });

    var loan_account = '';
    $('#loan_account').change(function() {
        loan_account = $(this).val();
    });

    var savings_product = '';
    $('#savings_product').change(function() {
        savings_product = $(this).val();

        $.ajax({
            type: "POST",
            url: "../getSavingsAccounts",
            data: {productId : savings_product},
            dataType: "json",
            success: function (savingsAccounts) {
                $('#savings_account').empty();
                $('#savings_account').append("<option value=''>"+ 'All'+"</option>");
                if (savingsAccounts != '') {
                    $.each(savingsAccounts, function (index, savingsAccount) {
                        $('#savings_account').append("<option value="+savingsAccount.id+">" + savingsAccount.accountCode  +"</option>");
                    });
                }
                
            },
            error: function(){
                alert('error!');
            }
        });
    });

    var savings_account = '';
    $('#savings_account').change(function() {
        savings_account = $(this).val();
    });

    var start_date = '';
    $('#start_date').change(function() {
        start_date = $(this).val();
    });

    var end_date = '';
    $('#end_date').change(function() {
        end_date = $(this).val();
    });

    $('#searchButton').click(function() {

        if ('<?php echo($branchId); ?>' == 1) {
            if (branch_id == '') {
                swal({
                    icon: 'warning',
                    title: 'Warning',
                    text: 'Please select A branch',
                });
                return false;
            }
        }
        if ('<?php echo($branchId); ?>' != 1) {
           branch_id =  '<?php echo($branchId); ?>';
        }

        if (samity_id == '') {
            swal({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select Samity',
            });
            return false;
        }

        if (member_id == '') {
            swal({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select Member',
            });
            return false;
        }

        if (start_date == '') {
            swal({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select Date',
            });
            return false;
        }

        if (end_date == '') {
            swal({
                icon: 'warning',
                title: 'Warning',
                text: 'Please select Date',
            });
            return false;
        }
        
        
        $('#end_date_txt').html(end_date);
        ajaxSavingsDataLoad(branch_id, samity_id, member_id, start_date, end_date, loan_product, loan_account, savings_product, savings_account);

        ajaxLoanDataLoad(branch_id, samity_id, member_id, start_date, end_date, loan_product, loan_account, savings_product, savings_account);

        $('#table1,#table2,#ttlRow,#printPDF,#reportTitle').show('slow');
    });
    
});


function fnDownloadPDF() {
    $('.savingsDataTable,.loanDataTble').tableExport({
        type: 'pdf',
        fileName: 'Member Legder report',
        jspdf: {
            orientation: 'l',
            format: 'a4',
            margins: {
                left: 10,
                right: 10,
                top: 20,
                bottom: 20
            },
            autotable: {
                styles: {
                    overflow: 'linebreak'
                },
                tableWidth: 'auto'
            }
        }
    });
}

function fnDownloadExcel() {
    $('.savingsDataTable,.loanDataTble').tableExport({
        type: 'excel',
        fileName: 'Member Legder report',
    });
}
</script>
@endsection