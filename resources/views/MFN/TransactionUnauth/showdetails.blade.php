@extends('Layouts.erp_master')
@section('content')

<?php          use App\Services\MfnService as MFN;
               use App\Services\CommonService as Common;
                        $BranchID = (isset($BranchID) && !empty($BranchID)) ? $BranchID : Common::getBranchId();
                        // $StartDate = (isset($StartDate) && !empty($StartDate)) ? $StartDate : MFN ::systemCurrentDate($BranchID);
                       
                        

                        // dd($StartDate);
                    ?>
<div class="row text-right p-10">
    <div class="col-lg-11 text-right">
        <input type="hidden" class="form-control round" id="cur_date" name="cur_date"
                     placeholder="DD-MM-YYYY" value="{{$curDate }}">
        <input type="hidden"  id="samity_id" name="samity_id"
                     value="{{$shamity_data->id }}">
        {{-- <a href="javascript:void(0)" id="btnSearch" class="btn btn-primary btn-round">Search</a> --}}
        {{-- <label class="input-title" for="all_check_id">All</label> --}}
        {{-- <input type="checkbox" id="all_check_id" onclick="fnallCheck();" title="Select All"/> --}}
    </div>
    <div class="col-lg-1 p-10">
        @if(Common::getBranchId() == 1)
        <a href="javascript:void(0)" id="btnSearch" onclick="fnUnAuthorizeAll();"  class="btn btn-danger btn-round">Unauthorize</a>                      
        @endif
        
    </div>
</div> 
<div class="row text-right p-10">
    <div class="col-lg-9 text-right">
    </div>
    <div class="col-lg-3">
        <input type="checkbox" id="all_check_id" onclick="fnallCheck();" title="Select All"/>
        <label class="input-title" for="all_check_id"> All</label>
        
    </div>
</div> 

<div class="row" style="margin-top:2%;">
    <div class="col-lg-12">

        

        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th colspan="5"  class="text-center" > Loan Disbursment </th>  
                    
                </tr>
               
                <tr>
                    <th style="width: 5%;">SL</th>
                    <th>Loan Code</th>
                    <th>Loan Disbursment Amount</th> 
                    {{-- <th class="text-center" >  Action </th>   --}}
                    <th  class="text-center" style="width: 5%;"><input type="checkbox" id="all_check_id_loan" onclick="fnallCheckLoan();" title="Select All"/></th>  
                    {{-- <th style="width: 10%;" class="text-center"></th> --}}
                </tr>
            </thead>
            <tbody>
                <?php
                        $i = 0;
                        ?>
                        @foreach ($loan_data as $Row)

                        <tr>
                            <td scope="row"> {{++$i}}</td>
                            <td> {{$Row->loanCode}}</td>
                            <td> {{$Row->loanAmount}}</td>
                           

                            <td class="text-center">
                                <!-- Action Calling Role Wise -->
                                <input type="checkbox" class="clsTransL" data="{{$Row->id}}" id="itemL_{{$Row->id}}" />
                            </td>

                        </tr>

                        @endforeach


            </tbody>
        </table>
        
        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th colspan="5"  class="text-center" > Loan Collection  </th>
                    
                    
                </tr>
                <tr>
                    <th style="width: 5%;">SL</th>
                    <th>Loan Code</th>
                    <th>Collection Amount</th> 
                    <th  class="text-center" style="width: 5%;"><input type="checkbox" id="all_check_id_loan_col" onclick="fnallCheckLoanCol();" title="Select All"/></th> 
                    {{-- <th style="width: 10%;" class="text-center"></th> --}}
                </tr>
            </thead>
            <tbody>
                <?php
                        $i = 0;
                        ?>
                        @foreach ($loan_col_data as $Row)

                        <tr>
                            <td scope="row"> {{++$i}}</td>
                            <td> {{$Row->loanCode}}</td>
                            <td> {{$Row->amount}}</td>                           

                            <td class="text-center">
                                <!-- Action Calling Role Wise -->
                                <input type="checkbox" class="clsTransLC" data="{{$Row->id}}" id="itemLC_{{$Row->id}}" />
                            </td>

                        </tr>

                        @endforeach


            </tbody>
        </table>
        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th colspan="5"  class="text-center" > Savings Collection  </th>
                    
                    
                </tr>
                <tr>
                    <th style="width: 5%;">SL</th>
                    <th>Account Code</th>
                    <th>Deposit Amount</th> 
                    <th  class="text-center" style="width: 5%;"><input type="checkbox" id="all_check_id_SD" onclick="fnallCheckSD();" title="Select All"/></th> 
                    {{-- <th style="width: 10%;" class="text-center"></th> --}}
                </tr>
            </thead>
            <tbody>
                <?php
                        $i = 0;
                        ?>
                        @foreach ($saveings_dp_data as $Row)

                        <tr>
                            <td scope="row"> {{++$i}}</td>
                            <td> {{$Row->accountCode}}</td>
                            <td> {{$Row->amount}}</td>
                           

                            <td class="text-center">
                                <!-- Action Calling Role Wise -->
                                <input type="checkbox" class="clsTransSD" data="{{$Row->id}}" id="itemSD_{{$Row->id}}" />

                            </td>

                        </tr>

                        @endforeach


            </tbody>
        </table>
        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th colspan="5"  class="text-center" > Savings Withdraw  </th>
                    
                    
                </tr>
                <tr>
                    <th style="width: 5%;">SL</th>
                    <th>Account Code</th>
                    <th>Withdraw Amount</th> 
                    <th  class="text-center" style="width: 5%;"><input type="checkbox" id="all_check_id_SW" onclick="fnallCheckSW();" title="Select All"/></th> 
                    {{-- <th style="width: 10%;" class="text-center"></th> --}}
                </tr>
            </thead>
            <tbody>
                <?php
                        $i = 0;
                        ?>
                        @foreach ($saveings_wd_data as $Row)

                        <tr>
                            <td scope="row"> {{++$i}}</td>
                            <td> {{$Row->accountCode}}</td>
                            <td> {{$Row->amount}}</td>                           

                            <td class="text-center">
                                <input type="checkbox" class="clsTransSW" data="{{$Row->id}}" id="itemSW_{{$Row->id}}" />
                            </td>

                        </tr>

                        @endforeach


            </tbody>
        </table>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.page-header-actions').hide();
   // ajaxDataLoad();
   
});





function fnallCheck(){

    if($('#all_check_id').is(':checked')){
        $('.clsTransL').each(function() {

        $(this).prop("checked", true);
        });
        $('.clsTransLC').each(function() {

        $(this).prop("checked", true);
        });
        $('.clsTransSD').each(function() {

        $(this).prop("checked", true);
        });
        $('.clsTransSW').each(function() {

        $(this).prop("checked", true);
        });

        $('#all_check_id_loan').prop("checked", true);
        $('#all_check_id_loan_col').prop("checked", true);
        $('#all_check_id_SD').prop("checked", true);
        $('#all_check_id_SW').prop("checked", true);
        

        
    }else{
        $('.clsTransL').each(function() {

        $(this).prop("checked", false);

        });
        $('.clsTransLC').each(function() {

        $(this).prop("checked", false);

        });
        $('.clsTransSD').each(function() {

        $(this).prop("checked", false);

        });
        $('.clsTransSW').each(function() {

        $(this).prop("checked", false);

        });
        $('#all_check_id_loan').prop("checked", false);
        $('#all_check_id_loan_col').prop("checked", false);
        $('#all_check_id_SD').prop("checked", false);
        $('#all_check_id_SW').prop("checked", false);


    }

}

function fnallCheckSD(){

    if($('#all_check_id_SD').is(':checked')){
        
        $('.clsTransSD').each(function() {

        $(this).prop("checked", true);
        });
        
        
    }else{
        
        $('.clsTransSD').each(function() {

        $(this).prop("checked", false);

        });
        


    }

}
function fnallCheckSW(){

    if($('#all_check_id_SW').is(':checked')){
        
        $('.clsTransSW').each(function() {

        $(this).prop("checked", true);
        });
        
        
    }else{
        
        $('.clsTransSW').each(function() {

        $(this).prop("checked", false);

        });
        


    }

}
function fnallCheckLoanCol(){

    if($('#all_check_id_loan_col').is(':checked')){
        
        $('.clsTransLC').each(function() {

        $(this).prop("checked", true);
        });
    
        
    }else{
        
        $('.clsTransLC').each(function() {

        $(this).prop("checked", false);

        });

    }

}
  function fnallCheckLoan(){

    if($('#all_check_id_loan').is(':checked')){
        $('.clsTransL').each(function() {

        $(this).prop("checked", true);
        });
        
        
    }else{
        $('.clsTransL').each(function() {

        $(this).prop("checked", false);

        });
       
    }

}
    
function fnUnAuthorizeAll() {

    var myObjL = [];
    var myObjLC = [];
    var myObjSW= [];
    var myObjSD = [];

    var count = 0;

    $('.clsTransL').each(function() {

        var RowID = $(this).attr('data');
        // $(this).hide();
        // $(this).prop("checked", false);
        if($("#itemL_"+RowID).is(':checked')){
            count = count+1; 
             var value = $("#itemL_"+RowID).is(':checked')? 1 : 0;

            myObjL.push({ID:RowID, value:value});
        }

       
        

    });

    $('.clsTransLC').each(function() {

        var RowID = $(this).attr('data');
        // $(this).hide();
        // $(this).prop("checked", false);
        if($("#itemLC_"+RowID).is(':checked')){
            count = count+1; 
             var value = $("#itemLC_"+RowID).is(':checked')? 1 : 0;

            myObjLC.push({ID:RowID, value:value});
        }

       
        

    });
    $('.clsTransSW').each(function() {

        var RowID = $(this).attr('data');
        // $(this).hide();
        console.log($("#itemSW_"+RowID).is(':checked'));
        // $(this).prop("checked", false);
        if($("#itemSW_"+RowID).is(':checked')){
            count = count+1; 
            // console.log('count');
             var value = $("#itemSW_"+RowID).is(':checked')? 1 : 0;

            myObjSW.push({ID:RowID, value:value});
        }

       
        

    });
    $('.clsTransSD').each(function() {

        var RowID = $(this).attr('data');
        // $(this).hide();
        // $(this).prop("checked", false);
        if($("#itemSD_"+RowID).is(':checked')){
            count = count+1; 
            var value = $("#itemSD_"+RowID).is(':checked')? 1 : 0;
            myObjSD.push({ID:RowID, value:value});
        }

       
        

    });



    var curDate = $('#cur_date').val();
    var samity_id = $('#samity_id').val();
    
    if(count==0){
        swal({
                    icon: 'info',
                    title: "Please check Transaction.",
                    text: "You must check at least one Transactions.",

                });

    }else{

        $.ajax({
            method: "GET",
            url: "{{route('ajaxTransactionUnauth')}}",
            dataType: "json",
            data: {
                myObjL: myObjL,
                myObjLC: myObjLC,
                myObjSW: myObjSW,
                myObjSD: myObjSD,
                samity_id : samity_id,
                curDate: curDate
            },
            success: function(data) {
                if (data) {
                    console.log(data);
                        if (data['alert-type'] == 'error') {
                        toastr.error(data['message']);
                        }
                        else{

                            toastr.success("Successfully Unauthorized");
                            
                            $('.clsTransL').each(function() {
                                var RowID = $(this).attr('data');
                            
                                if($("#itemL_"+RowID).is(':checked')){
                                    $("#itemL_"+RowID).closest('tr').remove();
                                }
                            });
                            $('.clsTransLC').each(function() {
                                var RowID = $(this).attr('data');
                            
                                if($("#itemLC_"+RowID).is(':checked')){
                                    $("#itemLC_"+RowID).closest('tr').remove();
                                }
                            });
                            $('.clsTransSW').each(function() {
                                var RowID = $(this).attr('data');
                            
                                if($("#itemSW_"+RowID).is(':checked')){
                                    $("#itemSW_"+RowID).closest('tr').remove();
                                }
                            });
                            $('.clsTransSD').each(function() {
                                var RowID = $(this).attr('data');
                            
                                if($("#itemSD_"+RowID).is(':checked')){
                                    $("#itemSD_"+RowID).closest('tr').remove();
                                }
                            });
                            } 
                      
                    }else{
                        toastr.error("Unsuccessfully Unauthorized");
                    }

                }
            });

    }

    
}
</script>

@endsection