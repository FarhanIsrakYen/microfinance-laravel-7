@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>

<!-- Page -->
<?php $AccBankCash = Common::ViewTableOrderIn('acc_account_ledger',
    ['is_delete' => 0, 'is_group_head' => 0],
    ['acc_type_id', [4, 5]],
    ['id', 'name', 'code'],
    ['id', 'ASC'])?>
<?php $AccNonBankCash = Common::ViewTableOrderNotIn('acc_account_ledger',
    ['is_delete' => 0, 'is_group_head' => 0],
    ['acc_type_id', [4, 5]],
    ['id', 'name', 'code'],
    ['id', 'ASC'])?>
<div class="nav-tabs-horizontal" >

    <ul class="nav nav-tabs nav-tabs-reverse nav-fill d-print-none" role="tablist">
       
        <?php $flag = true;?>
        @foreach ($vtype as $vdata)

        <li class="nav-item mr-3" role="presentation">
            <a class="nav-link {{ ($flag == true) ? 'active' : '' }}" data-toggle="tab"
                href="#tab{{$vdata->id}}" aria-controls="tab{{$vdata->id}}"
                role="tab">{{$vdata->name}}</a></li>
        <?php $flag = false;?>
        @endforeach

    </ul>
    
    <div class="tab-content pt-20">
        <?php $flag = true; ?>
        @foreach ($vtype as $vdata)

                <?php
                    if ($vdata->id == 1) {
                        $LedgerAcc1 = $AccBankCash;
                        $LedgerAcc2 = $AccNonBankCash;
                        $Acc1 = "Credit";
                        $Acc2 = "Debit";
                        $fundflag = false;

                    }else if ($vdata->id == 2) {
                        $fundflag = false;
                        $LedgerAcc1 = $AccBankCash;
                        $LedgerAcc2 = $AccNonBankCash;
                        $Acc1 = "Debit";
                        $Acc2 = "Credit";
                        # code...
                    } else if ($vdata->id == 3) {
                        $fundflag = false;
                        $LedgerAcc1 = $AccNonBankCash;
                        $LedgerAcc2 = $AccNonBankCash;
                        $Acc1 = "Debit";
                        $Acc2 = "Credit";
                        # code...
                    }else if ($vdata->id == 4) {
                        $fundflag = false;
                        $LedgerAcc1 = $AccBankCash;
                        $LedgerAcc2 = $AccBankCash;
                        $Acc1 = "Debit";
                        $Acc2 = "Credit";
                        # code...
                    }else if ($vdata->id == 5) {
                        $fundflag = true;
                        $Acc1 = "Debit";
                        $Acc2 = "Credit";
                        # code...
                    } else {
                        $LedgerAcc1 = $AccBankCash;
                        $LedgerAcc2 = $AccNonBankCash;
                        $Acc1 = "Debit";
                        $Acc2 = "Credit";
                        $fundflag = false;
                        # code...
                    }

                ?>
      
        <div class="tab-pane show {{ ($flag == true) ? 'active' : '' }}" id="tab{{$vdata->id}}" role="tabpanel">
            <?php $flag = false;?>
            <form enctype="multipart/form-data" id="formID_{{$vdata->id}}" method="POST" data-toggle="validator" novalidate="true">
                @csrf
               
                {!! HTML::forCompanyFeild('','',false) !!}
                {!! HTML::forBranchFeild(false,'branch_id','branch_id_'.$vdata->id ,null,'','Branch')
                !!}
                <input type="hidden" id="voucher_type_id_{{$vdata->id}}" name="voucher_type_id"
                    value="{{$vdata->id}}">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="panel-heading align-self-center" style="background:#17b3a3; height:40px; width: 100%;">
                                <div class="row">
                                    <label class="panel-title col-md-4 text-white" style="padding: 10px 100px;">{{$vdata->name}}</label>
                                    <div class="panel-title col-md-5" style="padding: 10px 100px;">
                                        <label class="text-white">Total Amount: <span
                                                id="totalAmount_{{$vdata->id}}">0.00</span> Tk</label>
                                                {{-- <input type="hidden" name="total_amount" id="total_amount_{{$vdata->id}}"> --}}
                                    </div>
                                    <div class="panel-title col-md-3" style="padding: 10px 100px;">
                                        <label class="text-white">Branch:  Head Office</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row align-items-center">
                            <label class="col-lg-1 input-title"
                                for="project_id_{{$vdata->id}}">Project</label>
                            <div class="col-lg-2 input-group">

                                <select class="form-control clsSelect2" required style="width: 100%;"
                                    data-error="Please select project name." name="project_id"
                                    id="project_id_{{$vdata->id}}" onchange="fnAjaxSelectBox(
                                    'project_type_id_{{$vdata->id}}',
                                    this.value,
                                    '{{base64_encode('gnl_project_types')}}',
                                    '{{base64_encode('project_id')}}',
                                    '{{base64_encode('id,project_type_name')}}',
                                    '{{url('/ajaxSelectBox')}}'
                                            );">
                                    <option value="">Select Option</option>
                                    @foreach ($project as $Row)
                                    <option value="{{$Row->id}}">{{$Row->project_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            ​
                            <label class="col-lg-1 input-title"
                                for="project_type_id_{{$vdata->id}}">Project Type</label>
                            ​
                            <div class="col-lg-2 input-group">
                                <select class="form-control clsSelect2" required
                                    data-error="Please select Project Type." name="project_type_id"
                                    id="project_type_id_{{$vdata->id}}"
                                    onChange="fnGenBill({{ $vdata->id }});" style="width: 100%;">

                                    <option value="">Select Option</option>

                                </select>
                            </div>
                            ​
                            <label class="col-lg-1 input-title"
                                for="voucher_date_{{$vdata->id}}">Voucher Date</label>

                            <div class="col-lg-2 input-group">
                                <div class="input-group-prepend ">
                                    <span class="input-group-text ">
                                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                                    </span>
                                </div>

                                <input type="text" name="voucher_date" id="voucher_date_{{$vdata->id}}"
                                    data-plugin="datepicker" class="form-control round"
                                    placeholder="DD-MM-YYYY" value="{{ Common::systemCurrentDate() }}"
                                    readonly="true" required>
                            </div>
                            ​
                            <label class="col-lg-1 input-title"
                                for="voucher_code_{{$vdata->id}}">Voucher Code</label>
                            ​
                            <div class="col-lg-2 input-group">
                                <input type="text" class="form-control round"
                                    id="voucher_code_{{$vdata->id}}" name="voucher_code" readonly>
                            </div>
                        </div>

                        <div id="ftdivID" style="display:{{ ($fundflag == true) ? 'block' : 'none' }};">
                        <div class="row align-items-center">
                            <label class="col-lg-1 input-title" for="TargetBranch">Target Branch</label>
                                ​
                                <div class="col-lg-2 input-group">
                                    <select class="form-control clsSelect2" id="t_Branch_{{$vdata->id}}" name="t_branch" style="width: 100%;"
                                        onchange="fnAjaxSelectBoxForTargetBranch(
                                            't_branchCB_{{$vdata->id}}',
                                            this.value,
                                            '{{base64_encode('acc_account_ledger')}}',
                                            '{{base64_encode('branch_arr')}}',
                                            '{{base64_encode('id,name,code')}}',
                                            '{{url('/ajaxSelectBoxfortargetbranch')}}');"
                                                    >
                                        <option value="">Select Option</option>
                                        @foreach($BranchData as $data)
                                        <option value="{{ $data->id }}">
                                            {{ $data->branch_code.'-'.$data->branch_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <!-- Target Branch  -->
                                <label class="col-lg-1 input-title" for="TargetBranchCash">Target Branch Cash/Bank</label>
                                ​
                                <div class="col-lg-2 input-group"> 
                                    <select class="form-control clsSelect2" id="t_branchCB_{{$vdata->id}}" name="target_bankcash" style="width: 100%;"
                                        onchange="fnsetdcselectbox(this.value,{{ $vdata->id }},{{ $AccBankCash }},{{ $AccNonBankCash }});">
                                        <option value="">Select Option</option>
                                    </select>
                                </div>
                           
                        </div>
                        </div>
                       

                        <div class="row align-items-center">
                            <label class="col-lg-1 input-title" for="Acc_One_{{$vdata->id}}">{{$Acc1}} 
                                Account</label>
                            ​
                            <div class="col-lg-2 input-group">
                                <select class="form-control clsSelect2" id="Acc_One_{{$vdata->id}}"  style="width: 100%;" onchange="fnChecktwoAcc({{ $vdata->id }});">
                                    <option value="">Select Option</option>
                                    @foreach($LedgerAcc1 as $data)
                                    <option value="{{ $data->id }}"
                                        data="{{ $data->code.'-'.$data->name }}">
                                        {{ $data->code.'-'.$data->name }}</option>
                                    @endforeach
                                   
                                </select>
                                <div id="oneAccError_{{$vdata->id}}" class="text-danger" style="display:none"> <p> {{$Acc1}} & {{$Acc2}} are same! </p></div>
                            </div>

                            <label class="col-lg-1 input-title" for="Acc_Two_{{$vdata->id}}">{{$Acc2}}
                                Account</label>
                            ​
                            <div class="col-lg-2 input-group">
                                <select class="form-control clsSelect2" id="Acc_Two_{{$vdata->id}}" style="width: 100%;" onchange="fnChecktwoAcc({{ $vdata->id }});">
                                    <option value="">Select Option</option>
                                    @foreach($LedgerAcc2 as $data)
                                    <option value="{{ $data->id }}"
                                        data="{{ $data->code.'-'.$data->name }}">
                                        {{ $data->code.'-'.$data->name }}</option>
                                    @endforeach
                                </select>
                                <div id="twoAccError_{{$vdata->id}}" class="text-danger" style="display:none"> <p> {{$Acc1}} & {{$Acc2}} are same! </p></div>
                            </div>
                            

                            <label class="col-lg-1 input-title"
                                for="amount_{{$vdata->id}}">Amount</label>

                            <div class="col-lg-2 input-group">
                                <input type="number" class="form-control round" id="amount_{{$vdata->id}}"
                                    placeholder="Enter Amount">
                            </div>

                            <label class="col-lg-1 input-title"
                                for="local_narration_{{$vdata->id}}">Narration/ Cheque Details</label>

                            <div class="col-lg-2 input-group">
                                <input type="text" class="form-control round"
                                    id="local_narration_{{$vdata->id}}" placeholder="Enter Details">
                            </div>
                        </div>

                        <div class="row text-right p-10">
                            <div class="col-lg-12">

                                <?php
                                    $i = 0;
                                    $TableID = "tableID_{$vdata->id}";
                                    $ColumnName = "acc_one_arr[]&acc_two_arr[]&amount_arr[]&narration_arr[]";
                                    $ColumnID = "acc_one_id_&acc_two_id_&amount_id_&narration_id_&deleteRow_";
                                ?>
                            <a href="javascript:void(0);" class="btn btn-primary btn-round d-print-none" id="addbtn_{{$vdata->id}}"
                            onclick="btnAddNewRow('<?=$vdata->id?>','<?=$TableID?>', '<?=$ColumnName?>', '<?=$ColumnID?>', 'TotalRowID');">
                            <i class="icon wb-plus  align-items-center"></i> Add
                            </a>
                               

                            </div>
                        </div>

                    </div>
                </div>
                <div class="row">

                    <div class="col-lg-12">

                        <div class="form-row align-items-center">
                            <div class="table-responsive">
                                <table
                                    class="table table-hover table-striped table-bordered w-full text-center table-responsive my-custom-scrollbar"
                                    id="tableID_{{$vdata->id}}">
                                    <thead>
                                        <tr>
                                            <!-- <th width="40%">Barcode</th> -->
                                            <th width="30%">{{$Acc1}} Amount</th>
                                            <th width="30%">{{$Acc2}} Amount</th>
                                            <!--<th width="10%" >Order Quantity</th>-->
                                            <!--<th width="10%" >Receive Quantity</th>-->
                                            <th width="20%">Amount</th>
                                            <th width="30%">Narration</th>
                                            <th width="10%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>

                                        </tr>
                                    </tbody>
                                </table>

                                <!-- Row_Count is temporary variable for using row add and delete-->
                                <input type="hidden" id="TotalRowID_{{$vdata->id}}" value="0" />




                            </div>

                        </div>
                        <div class="form-row align-items-center">
                            <label class="col-lg-2 input-title"
                                for="global_narration_{{$vdata->id}}">Global Narration Details</label>
                            <div class="col-lg-10 form-group">
                                <div class="input-group ">
                                    <textarea type="text" class="form-control round"
                                        id="global_narration_{{$vdata->id}}" name="global_narration"
                                        placeholder="Please Enter Narration."> </textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-row align-items-center">
                            <div class="col-lg-12">
                                <div class="form-group d-flex justify-content-center">
                                    <div class="example example-buttons">
                                        <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                                        <button type="submit" class="btn btn-primary btn-round d-print-none"
                                            id="fSubmit_{{$vdata->id}}" onclick="fnCheckSubmit({{ $vdata->id }});window.print();">Save & Print</button>
                                        {{-- <button type="button" class="btn btn-warning btn-round">Reset</button> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        @endforeach


    </div>
   
</div>


<div class="panel-body">
    <div class="row">
        <div class="col-lg-12">
           
                

        </div>
    </div>
</div>

<!-- End Page -->
<script>


$(document).ready(function() {

    $('#project_type_id').change(function() {


    });

});

function fnChecktwoAcc(VoucherType){

    var Acc1 = $('#Acc_Two_'+ VoucherType).val();
    var Acc2 = $('#Acc_One_' + VoucherType).val();

    if($('#Acc_Two_'+ VoucherType).val()!='' && $('#Acc_One_' + VoucherType).val()!=''){
        if(Acc1 == Acc2){

        // $("#addbtn_"+ VoucherType).prop("disabled", true);
        // $("#addbnt_"+ VoucherType).attr("disabled", true);
        $("#twoAccError_"+ VoucherType).show();
        $("#oneAccError_"+ VoucherType).show();

        }else{
        // $("#addbnt_"+ VoucherType).attr("disabled", false);
        $("#twoAccError_"+ VoucherType).hide();
        $("#oneAccError_"+ VoucherType).hide();
        }

    }
}

function fnGenBill(VoucherType) {

    // console.log('tetst d');
    var BranchID = $('#branch_id_'+ VoucherType).val();
    var vouchertype = $('#voucher_type_id_' + VoucherType).val();
    var projectID = $('#project_id_' + VoucherType).val();
    var project_typeID = $('#project_type_id_' + VoucherType).val();
    if (project_typeID != '' && BranchID != '') {
    // console.log(BranchID);
        $.ajax({
            method: "GET",
            url: "{{url('/ajaxVoucherBillGen')}}",
            dataType: "text",
            data: {
                BranchID: BranchID,
                vouchertype: vouchertype,
                projectID: projectID,
                project_typeID: project_typeID
            },
            success: function(data) {
                if (data) {
                    console.log(data);
                    $('#voucher_code_' + VoucherType).val(data);
                    //data.details.forEach(SaperateItem);
                }
            }
        });
    }

}

/* Add row Start */
function btnAddNewRow(vDataID, TableID, ColumnNameS, ColumnIDS, TotalRowID) {

    var ColumnName = ColumnNameS.split("&");
    var ColumnID = ColumnIDS.split("&");
    /*
        0: "product_id_"
        1: "sys_barcode_"
        2: "product_name_"
        3: "product_quantity_"
        4: "unit_cost_price_"
        5: "total_cost_price_"
        6: "deleteRow_"

    */

    if ($('#amount_' + vDataID).val() != '' && $('#Acc_One_' + vDataID).val() != '' && $('#Acc_Two_' + vDataID).val() !=
        '' && $('#Acc_Two_'+ vDataID).val() != $('#Acc_One_' + vDataID).val() && $('#amount_' + vDataID).val() > 0) {
        var TotalRowCount = $('#' + TotalRowID +'_'+ vDataID).val();





        TotalRowCount++;
        $('#' + TotalRowID+'_'+ vDataID).val(TotalRowCount);

        var amount = $('#amount_' + vDataID).val();
        var AccTwo = $('#Acc_Two_' + vDataID).find("option:selected").attr('data');
        var AccTwoVal = $('#Acc_Two_' + vDataID).val();
        var AccOne = $('#Acc_One_' + vDataID).find("option:selected").attr('data');
        var AccOneVal = $('#Acc_One_' + vDataID).val();
        var narration = $('#local_narration_' + vDataID).val();
        console.log(AccTwo);
        console.log(narration);


        var html = '<tr>';

        // html += '<td class="input-group barcodeWidth" width="35%">';
        html += '<td>';
        html += '<input type="hidden" name="' + ColumnName[0] + '" id="' + ColumnID[0] + TotalRowCount +
            '"value="' + AccOneVal + '">';
        html += '' + AccOne + '';
        html += '</td>';
        html += '<td>';
        html += '<input type="hidden" name="' + ColumnName[1] + '" id="' + ColumnID[1] + TotalRowCount +
            '"value="' + AccTwoVal + '">';
        html += '' + AccTwo + '';
        '" readonly >';
        html += '</td>';
        html += '<td>';
        html += '<input type="hidden" name="' + ColumnName[2] + '" id="' + ColumnID[2] + TotalRowCount +
            '" class="clsAmount" value="' + amount + '">';
        html += '' + amount + '';
        html += '</td>';
        html += '<td>';
        html += '<input type="hidden" name="' + ColumnName[3] + '" id="' + ColumnID[3] + TotalRowCount +
            '"value="' + narration + '">';
        html += '' + narration + '';
        html += '</td>';

        html += '<td>' +
            '<a href="javascript:void(0)" class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center" onclick="btnRemoveRow(this,' +
            vDataID + ');">' +
            ' <i class="icon fa fa-times align-items-center"></i>' +
            '</a>' +
            '</td>';
        html += '</tr>';

        $('#' + TableID + ' tbody').find('tr:first').after(html);

        $('#amount_' + vDataID).val('');
        $('#Acc_Two_' + vDataID).val('');
        $('#Acc_Two_' + vDataID).trigger('change');
        $('#Acc_One_' + vDataID).val('');
        $('#Acc_One_' + vDataID).trigger('change');
        $('#local_narration_' + vDataID).val('');
        fnTotalAmount(vDataID);

    } else {
        if ($('#amount_' + vDataID).val() == '' || $('#amount_' + vDataID).val() == 0) {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Please Enter Amount!',
            });
        }else if ($('#Acc_One_' + vDataID).val() == '' || $('#Acc_Two_' + vDataID).val() == '') {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Please Select the input Field!',
            });
        }else if($('#Acc_Two_'+ vDataID).val()== $('#Acc_One_' + vDataID).val()){
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Debit and Credit Are Same !!',
            });
        }
    }



}
/* Add row End */
function btnRemoveRow(RemoveID, vDataID) {

    $(RemoveID).closest('tr').remove();
    // fnTotalQuantity();
    fnTotalAmount(vDataID);
}

function fnTotalAmount(vDataID) {

    var varAmount = 0;
    $('.clsAmount').each(function() {
        varAmount = Number(varAmount) + Number($(this).val());
        console.log($(this).val());
    });
    $('#totalAmount_' + vDataID).html(varAmount);
    // $('#total_amount_' + vDataID).val(varAmount);
    console.log(varAmount);
}

function fnCheckSubmit(vDataID){


    event.preventDefault();
    // $('#' + TotalRowID'_'+ vDataID).
   

    if($('#TotalRowID_'+vDataID).val()<= 0 ){
        swal({
                icon: 'error',
                title: 'Error',
                text: 'Add at lest one transaction ..',
            });

    }else{
        $('#formID_'+vDataID).submit();
    }

}

function fnsetdcselectbox(value, vDataID , cashAcc, NonCashAcc){
    

    var accCash='<option value="">Select One</option>';
    var accNonCash='<option value="">Select One</option>';
    
    $.each( cashAcc, function( key, value ) {
        accCash += '<option value="' + value.id + '" data="' +  value.code +'-'+ value.name+ '" >' +  value.code +'-'+ value.name+ '</option>';
        });

    $.each( NonCashAcc, function( key, value ) {
        accNonCash += '<option value="' + value.id + '" data="' +  value.code +'-'+ value.name+ '" >' +  value.code +'-'+ value.name+ '</option>';
       
    });

    
    if (value == "0"){
        $('#Acc_Two_' + vDataID).val('');
        $('#Acc_Two_' + vDataID).trigger('change');
        $('#Acc_One_' + vDataID).val('');
        $('#Acc_One_' + vDataID).trigger('change');
        $('#Acc_One_' + vDataID).html(accNonCash);
        $('#Acc_Two_' + vDataID).html(accNonCash);


    }else{
        $('#Acc_Two_' + vDataID).val('');
        $('#Acc_Two_' + vDataID).trigger('change');
        $('#Acc_One_' + vDataID).val('');
        $('#Acc_One_' + vDataID).trigger('change');

        $('#Acc_One_' + vDataID).html(accNonCash);
        $('#Acc_Two_' + vDataID).html(accCash);
       


    }



}

// $('#submitButton').on('click', function(event) {
//     event.preventDefault();
//     if($('#branch_from').val()==$('#branch_to').val()){
//         swal({
//                 icon: 'error',
//                 title: 'Error',
//                 text: 'Two Branch Name Cant be same',
//             });

//     }else{
//         if(Number($('#branch_from').val()) != 1 ){
//             swal({
//                     icon: 'error',
//                     title: 'Error',
//                     text: 'Access Denied ! You are not authorized in this page',
//                     confirmButtonText: "Ok"
//             }).then((isConfirm) => {
//                 if (isConfirm) {
//                     window.location.href = "{{url('pos/issue')}}";
//                 }
//             });
//         }else{
//              if ($('#total_amount').val() > 0 && $('#product_id_0').val() == '') {

//                  $('#issue_form').submit();
//             } else {
//                 if ($('#total_amount').val() <= 0) {
//                     swal({
//                         icon: 'error',
//                         title: 'Error',
//                         text: 'Total payable amount must be gratter than zero !!',
//                     });
//                 } else{
//                     swal({
//                         icon: 'error',
//                         title: 'Error',
//                         text: 'Add the selected Product Or Remove it !!',
//                     });
//                 }

//             }


//         }

//     }

// });
</script>

@endsection