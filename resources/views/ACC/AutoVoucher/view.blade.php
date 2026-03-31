@extends('Layouts.erp_master')
@section('content')
<?php
                  

if ($voucherdata->voucher_type_id == 1) {
    $Acc1 = "Credit";
    $Acc2 = "Debit";

}else {
    $Acc1 = "Debit";
    $Acc2 = "Credit";
    # code...
}

?>

<div class="row">
    <div class="col-lg-12">
        <table class="table table-striped table-bordered">

            <thead>
                <tr>
                    <th colspan="2"  style="border:none;">
                        <h4 class="text-white">{{$voucherdata->voucherType['name']}}</h4>
                    </th>
                    <th colspan="2"  style="border:none;">
                        <h4 class="text-white">Branch:{{$voucherdata->branch['branch_name']}}</h4>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="width:25%;">Project</td>
                    <td style="width:25%;">{{$voucherdata->project['project_name']}}</td>
                    <td style="width:25%;">Voucher Date </td>
                    <td style="width:25%;">{{$voucherdata->voucher_date}}</td>
                </tr>
                <tr>
                    <td style="width:25%;">Project Type</td>
                    <td style="width:25%;" >{{$voucherdata->projectType['project_type_name']}}</td>
                    
                    <td style="width:25%;">Voucher Code </td>
                    <td style="width:25%;" >{{$voucherdata->voucher_code}}</td>
                </tr>
            </tbody>
            
            </tbody>
           
        </table>

    </div>
</div>
<div class="row">
    <div class="col-lg-12">
        <table class="table table-striped table-bordered">
            <thead>
               
                <tr>
                    <th >
                        {{ $Acc1}}
                    </th>
                    <th >
                        {{$Acc2}}
                    </th>
                    <th >
                        Narration
                    </th>
                    <th class="text-center">
                       Amount 
                    </th>
                    

                </tr>
            </thead>
            <tbody>
                @if(count($vdatad) > 0)
                                        @foreach($vdatad as $index => $Data)
                                        <?php 
                                        $accOnename='';
                                        $accTwoname='';
                                       //dd($Data);
                                        // $ColumnName = "acc_one_arr[]&acc_two_arr[]&amount_arr[]&narration_arr[]";
                                        // $ColumnID = "acc_one_id_&acc_two_id_&amount_id_&narration_id_&deleteRow_";

                                        if($voucherdata->voucher_type_id==1){
                                           $accTwoname =  $Data->LedgerDebit['name'];
                                           $accOnename =  $Data->LedgerCredit['name'] ;
                                        //    dd($accTow);

                                        }else if($voucherdata->voucher_type_id==5) {
                                           $accOnename =  $Data->LedgerDebit['name'];
                                           $accTwoname =  $Data->LedgerCredit['name'] ;

                                            # code...
                                        }else {
                                            # code...
                                           $accOnename =  $Data->LedgerDebit['name'];
                                           $accTwoname =  $Data->LedgerCredit['name'] ;
                                        }
                                        ?>

                                        <tr>
                                            <td  style="width:25%;">
                                                {{ $accOnename }}
                                            </td>
                                            <td style="width:25%;">
                                                 {{$accTwoname}}
                                            </td>
                                            <td style="width:35%;">
                                                {{ $Data->local_narration }}
                                            </td>
                                            <td style="width:15%;" class="clsAmount text-right">
                                                {{ $Data->amount }}
                                            </td>

                                            

                                            
                                        </tr>
                                        @endforeach
                                        @endif

                                      

         

            </tbody>
            <tfoot>
                <td colspan="2" class="text-right">
                    
                    </td>
                <td colspan="1" class="text-right">
                <strong>Total Amount</strong>
                </td>
                <td colspan="1" id="totalAmount" class="text-right">
                   
                </td>
               

            </tfoot>
           
        </table>
        <table  class="table table-striped table-bordered">
            <thead>

                <th colspan="4">
                    Global Narration
                </th>
            </thead>
            <tbody>

                <td colspan="4">
                    {{ $voucherdata->global_narration }}
                </td>

            </tbody>
        </table>
    </div>
</div>

<div class="form-group d-flex justify-content-center">
    <div class="example example-buttons">
        <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>

    </div>
</div>


<script>
$(document).ready(function() {

fnTotalAmount();


});
function fnTotalAmount() {
    // console.log('ee');
    var varAmount = 0;
    $('.clsAmount').each(function() {
        varAmount = Number(varAmount) + Number($(this).html());
        //  console.log($(this).html())
    });
    $('#totalAmount').html(varAmount);
}



</script>

@endsection
