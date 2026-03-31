@extends('Layouts.erp_master')
@section('content')

<?php          use App\Services\MfnService as MFN;
                use App\Services\CommonService as Common;
                use App\Services\HtmlService as HTML;
                        $BranchID = (isset($BranchID) && !empty($BranchID)) ? $BranchID : Common::getBranchId();
                        $StartDate = (isset($StartDate) && !empty($StartDate)) ? $StartDate : MFN ::systemCurrentDate($BranchID);
                       
                        
                        $StartDate = new DateTime($StartDate);
                        $StartDate = $StartDate->format('d-m-Y');
                        // dd($StartDate);
                    ?>
<form  method="POST" data-toggle="validator" novalidate="true">
    @csrf

 
    <!-- Search Option Start -->
    <div class="row align-items-center d-flex justify-content-center">

        <div class="col-lg-2">
            <label class="input-title">Date</label>
              <div class="input-group ghdatepicker">
                  <div class="input-group-prepend ">
                      <span class="input-group-text ">
                          <i class="icon wb-calendar round" aria-hidden="true"></i>
                      </span>
                  </div>
                  <input type="text" class="form-control round datepicker-custom" id="cur_date" name="cur_date"
                      placeholder="DD-MM-YYYY" value="{{ $StartDate }}">
              </div>
          </div>


        <!-- Html View Load For Branch Search -->
        {!! HTML::forBranchFeildSearch($BranchID) !!}

        <div class="col-lg-2">
            <label class="input-title">Samity</label>
            <div class="input-group">
                <select class="form-control clsSelect2" name="filSamity" id="filSamity">
                    <option value="">All</option>
                    @foreach ($samities as $samity)
                        <option value="{{ $samity->id }}">{{ $samity->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-lg-2">
            <label class="input-title"></label>
            <div class="input-group">
                <a href="javascript:void(0);" id="btnSearch" class="btn btn-primary btn-round">Search</a>
            </div>
           
        </div>

       

        <div class="col-lg-1 text-right">
            
            

        </div>
    </div>

    <div class="row text-right p-10">
    
        <div class="col-lg-12">
            <a href="javascript:void(0)"  style="float: right;" onclick="fnAuthorizeAll();"  class="btn btn-primary btn-round">Authorize</a>
            {{-- <a href="javascript:void(0)" id="btnSearch" onclick="fnAuthorizeAll();"  class="btn btn-primary btn-round">Authorize</a> --}}
        </div>
    </div>
    <!-- Search Option End -->
</form>




<div class="row" style="margin-top:2%;">
    <div class="col-lg-12">
        

        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th style="width: 5%;">SL</th>
                    <th>Samity Code</th>
                    <th>Samity Name</th>
                    <th>Loan Disbursment Amount</th>
                    <th>Loan Collection Amount</th>
                    <th>Savings Collection Amount</th>
                    <th>Savings Withdraw Amount</th>
                    <th class="text-center">  <input type="checkbox" id="all_check_id" onclick="fnallCheck();" title="Select All"/></th>  
                    {{-- <th style="width: 10%;" class="text-center"></th> --}}
                </tr>
            </thead>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.page-header-actions').hide();
    var branchID = $('#branch_id').val();
    ajaxDataLoad(branchID);
    $('#btnSearch').click(function() {

        var branchID = $('#branch_id').val();
        var samity_id = $('#filSamity').val();

        ajaxDataLoad(branchID, samity_id);
    });

    $("#branch_id").change(function (e) {
            e.preventDefault();

            $('#filSamity option:gt(0)').remove();

            if($(this).val() == ''){
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./getSamities",
                data: {branchId : $("#branch_id").val()},
                dataType: "json",
                success: function (samities) {
                    $.each(samities, function (index, samity) {
                        $('#filSamity').append("<option value="+samity.id+">"+samity.name+"</option>");
                    });
                },
                error: function(){
                    alert('error!');
                }
            });
        });

    
});

function ajaxDataLoad(branchID = null, samity_id = null ) {
    // console.log(branchID);
    var curDate = $('#cur_date').val();
    $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            searching: false,
            
            "ajax":{
                "url": "{{route('transactionauthDatatable')}}",
                     "dataType": "json",
                     "type": "post",
                     "data": {
                        // SDate: SDate,
                        samity_id: samity_id,
                        branchID: branchID,
                        curDate: curDate,
                    }
                   },
            "columns": [
                { data: 'id',
                name: 'id', orderable: false, targets: 1, className: 'text-center'},
                { "data": "samity_code" },
                { "data": "samity_name" },
                { "data": "loan_dis" },
                { "data": "loan_col" },
                { "data": "savings_col" },
                { "data": "savings_wd" },
                { "data": "action", name: 'action', className: 'text-center d-print-none', orderable: false, "width": "5%" },
            ],
            'fnRowCallback': function(nRow, aData, Index) {
            $('.btnDelete', nRow).removeClass('btnDelete');

            var actionHTML = '<input type="checkbox" class="clsTrans" data="'+aData.action+'" id="item_'+aData.action+'" />';


            $('td:last', nRow).html(actionHTML);
            
           
        }
                 
        });
}



$('form').submit(function(event) {
    $(this).find(':submit').attr('disabled', 'disabled');
});

  function fnallCheck(){

        // console.log('tetettetete');
        

        // var allcheck = $('#all_check_id').val();

        if($('#all_check_id').is(':checked')){
            $('.clsTrans').each(function() {

            $(this).prop("checked", true);
            });
            
        }else{
            $('.clsTrans').each(function() {

            $(this).prop("checked", false);

            });

        }



        // console.log(allcheck);

       

    }
    
    function fnAuthorizeAll() {

        var myObj = [];

        var count = 0;

        $('.clsTrans').each(function() {

            var RowID = $(this).attr('data');
            // $(this).hide();
            // $(this).prop("checked", false);
            if($("#item_"+RowID).is(':checked')){
                count = count+1; 
            }

            var value = $("#item_"+RowID).is(':checked')? 1 : 0;

                myObj.push({ID:RowID, value:value});
           

        });
        // console.log(myObj);
        var curDate = $('#cur_date').val();
        var branchID = $('#branch_id').val();

        if(count==0){
            swal({
                        icon: 'info',
                        title: "Please check Transaction.",
                        text: "You must check at least one Transactions.",

                    });

        }else{

            $.ajax({
                method: "GET",
                url: "{{route('ajaxTransactionAuth')}}",
                dataType: "json",
                data: {
                    myObj: myObj,
                    curDate: curDate,
                    branchID:branchID
                },
                success: function(data) {
                    if (data) {
                        if (data['alert-type'] == 'error') {
                        toastr.error(data['message']);
                        }
                        else{
                            toastr.success("Successfully Authorized");
                            var branchID = $('#branch_id').val();
                            ajaxDataLoad(branchID);
                        } 
                      
                    }else{
                        toastr.error("Unsuccessfully Authorized");
                    }
                }
              });

        }

       
    }
</script>

@endsection