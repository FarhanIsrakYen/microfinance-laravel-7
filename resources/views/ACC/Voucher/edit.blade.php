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
    ['id', 'ASC']);
    $BranchID = Common::getBranchId();
    $Branchinfo = Common::ViewTableFirst('gnl_branchs',
    ['is_delete' => 0 , 'id' =>  $BranchID],
    ['id', 'branch_name'],
    ['id', 'ASC']);
   //
    
    ?>
<div class="nav-tabs-horizontal">

    <ul class="nav nav-tabs nav-tabs-reverse nav-fill d-print-none" role="tablist">
       
        {{-- @foreach ($vtype as $row) --}}

        <li class="nav-item mr-3" role="presentation">
            <a class="nav-link  active" data-toggle="tab"
                href="#tab{{$voucherdata->voucher_type_id }}" aria-controls="tab{{$voucherdata->voucher_type_id }}"
                role="tab">{{$voucherdata->voucherType['name'] }}</a></li>
       
        {{-- @endforeach --}}


    </ul>
    <div class="tab-content pt-20">
        <?php $flag = true; ?>
      

                <?php
                  

                    if ($voucherdata->voucher_type_id == 1) {
                        $LedgerAcc1 = $AccBankCash;
                        $LedgerAcc2 = $AccNonBankCash;
                        $Acc1 = "Credit";
                        $Acc2 = "Debit";
                        $fundflag = false;

                    }else if ($voucherdata->voucher_type_id == 2) {
                        $fundflag = false;
                        $LedgerAcc1 = $AccBankCash;
                        $LedgerAcc2 = $AccNonBankCash;
                        $Acc1 = "Debit";
                        $Acc2 = "Credit";
                        # code...
                    } else if ($voucherdata->voucher_type_id == 3) {
                        $fundflag = false;
                        $LedgerAcc1 = $AccNonBankCash;
                        $LedgerAcc2 = $AccNonBankCash;
                        $Acc1 = "Debit";
                        $Acc2 = "Credit";
                        # code...
                    }else if ($voucherdata->voucher_type_id == 4) {
                        $fundflag = false;
                        $LedgerAcc1 = $AccBankCash;
                        $LedgerAcc2 = $AccBankCash;
                        $Acc1 = "Debit";
                        $Acc2 = "Credit";
                        # code...
                    }else if ($voucherdata->voucher_type_id == 5) {
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
      
        <div class="tab-pane show active" id="tab{{$voucherdata->voucher_type_id}}" role="tabpanel">
            <?php $flag = false;?>
            <form enctype="multipart/form-data" id="formID_{{$voucherdata->voucher_type_id}}" method="POST" data-toggle="validator" novalidate="true">
                @csrf
                {!! HTML::forCompanyFeild($voucherdata->company_id,'',false) !!}
                {!! HTML::forBranchFeild(false,'branch_id','branch_id_'.$voucherdata->voucher_type_id ,$voucherdata->branch_id,'','Branch')
                !!}
                <input type="hidden" id="voucher_type_id_{{$voucherdata->voucher_type_id}}" name="voucher_type_id"
                    value="{{$voucherdata->voucher_type_id}}">
                    <input type="hidden" id="ft_id_{{$voucherdata->voucher_type_id}}" name="ft_id"
                    value="{{$voucherdata->ft_id}}">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="row">
                            <div class="panel-heading align-self-center" style="background:#17b3a3; height:40px; width: 100%;">
                                <div class="row">
                                    <label class="panel-title col-md-4 text-white" style="padding: 10px 100px;">{{$voucherdata->voucherType['name']}}</label>
                                    <div class="panel-title col-md-5" style="padding: 10px 100px;">
                                        <label class="text-white">Total Amount: <span
                                                id="totalAmount_{{$voucherdata->voucher_type_id}}">0.00</span> Tk</label>
                                                {{-- <input type="hidden" name="total_amount" id="total_amount_{{$voucherdata->voucher_type_id}}"> --}}
                                    </div>


                                    <div class="panel-title col-md-3" style="padding: 10px 100px;">
                                        <label class="text-white">Branch:{{$voucherdata->branch['branch_name']}}</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row align-items-center">
                            <label class="col-lg-1 input-title"
                                for="project_id_{{$voucherdata->voucher_type_id}}">Project</label>
                            <div class="col-lg-2 input-group">

                                <select class="form-control clsSelect2" required style="width: 100%;"
                                    data-error="Please select project name." name="project_id"
                                    id="project_id_{{$voucherdata->voucher_type_id}}" onchange="fnAjaxSelectBox(
                                    'project_type_id_{{$voucherdata->voucher_type_id}}',
                                    this.value,
                                    '{{base64_encode('gnl_project_types')}}',
                                    '{{base64_encode('project_id')}}',
                                    '{{base64_encode('id,project_type_name')}}',
                                    '{{url('/ajaxSelectBox')}}'
                                            );">
                                    <option value="">Select Option</option>
                                    @foreach ($project as $Row)
                                    <option value="{{$Row->id}}" {{ ($voucherdata->project_id == $Row->id) ? 'selected="selected"' : '' }}>{{$Row->project_name}}</option>
                                    @endforeach
                                </select>
                            </div>
                            ​
                            <label class="col-lg-1 input-title"
                                for="project_type_id_{{$voucherdata->voucher_type_id}}">Project Type</label>
                            ​
                            <div class="col-lg-2 input-group">
                                <select class="form-control clsSelect2" required
                                    data-error="Please select Project Type." name="project_type_id"
                                    id="project_type_id_{{$voucherdata->voucher_type_id}}"
                                    onChange="fnGenBill({{$voucherdata->voucher_type_id}});" style="width: 100%;">

                            <option value="{{$voucherdata->project_type_id}}" >{{$voucherdata->projectType['project_type_name']}}</option>

                                </select>
                            </div>
                            ​
                            <label class="col-lg-1 input-title"
                                for="voucher_date_{{$voucherdata->voucher_type_id}}">Voucher Date</label>

                            <div class="col-lg-2 input-group">
                                <div class="input-group-prepend ">
                                    <span class="input-group-text ">
                                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                                    </span>
                                </div>

                                <input type="text" name="voucher_date" id="voucher_date_{{$voucherdata->voucher_type_id}}"
                                    data-plugin="datepicker" class="form-control round"
                                    placeholder="DD-MM-YYYY" value="{{ Common::systemCurrentDate() }}"
                                    readonly="true" required value="{{$voucherdata->voucher_date}}">
                            </div>
                            ​
                            <label class="col-lg-1 input-title"
                                for="voucher_code_{{$voucherdata->voucher_type_id}}">Voucher Code</label>
                            ​
                            <div class="col-lg-2 input-group">
                                <input type="text" class="form-control round"
                                    id="voucher_code_{{$voucherdata->voucher_type_id}}" name="voucher_code" value="{{$voucherdata->voucher_code}}"  readonly>
                            </div>
                        </div>

                        <div id="ftdivID" style="display:{{ ($fundflag == true) ? 'block' : 'none' }};">
                        <div class="row align-items-center">
                            <label class="col-lg-1 input-title" for="TargetBranch">Target Branch</label>
                                ​
                                <div class="col-lg-2 input-group">
                                    @if ($voucherdata->voucher_type_id == 5)

                                    <?php 

                                    if($vdatad[0]->ft_target_acc == 0 ){
                                        $LedgerAcc1 = $AccNonBankCash;
                                        $LedgerAcc2 = $AccNonBankCash;
                                    }else {
                                        $LedgerAcc1 = $AccNonBankCash;
                                        $LedgerAcc2 = $AccBankCash;
                                        # code...
                                    }
                                    
                                   
                                    ?>
                                    @if(count($vdatad) > 0)
                                    <input type="hidden" name="ft_from" value="{{$vdatad[0]->ft_from}}">
                                    <input type="hidden" name="ft_to" value="{{$vdatad[0]->ft_to}}">
                                    {{-- <input type="hidden" name="ft_target_acc" value="{{$vdatad[0]->ft_target_acc}}"> --}}
                                    @endif
                                    @endif

                                    <select readonly class="form-control clsSelect2" id="t_Branch_{{$voucherdata->voucher_type_id}}" name="t_branch" style="width: 100%;" readonly
                                        onchange="fnAjaxSelectBoxForTargetBranch(
                                            't_branchCB_{{$voucherdata->voucher_type_id}}',
                                            this.value,
                                            '{{base64_encode('acc_account_ledger')}}',
                                            '{{base64_encode('branch_arr')}}',
                                            '{{base64_encode('id,name,code')}}',
                                            '{{url('/ajaxSelectBoxfortargetbranch')}}');"
                                                     >
                                        <option value="">Select Option</option>
                                        @foreach($BranchData as $data)
                                        <option value="{{ $data->id }}" {{ ($vdatad[0]->ft_to == $data->id) ? 'selected="selected"' : '' }}>
                                            {{ $data->branch_code.'-'.$data->branch_name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                                <label class="col-lg-1 input-title" for="TargetBranchCash">Target Branch Cash/Bank</label>
                                ​
                                <div class="col-lg-2 input-group"> 
                                    <select readonly class="form-control clsSelect2" id="t_branchCB_{{$voucherdata->voucher_type_id}}" name="target_bankcash" style="width: 100%;"
                                        onchange="fnsetdcselectbox(this.value,{{ $voucherdata->voucher_type_id }},{{ $AccBankCash }},{{ $AccNonBankCash }});">
                                        <option value="">Select Option</option>
                                    </select>
                                </div>
                           
                        </div>
                        </div>
                       

                        <div class="row align-items-center">
                            <label class="col-lg-1 input-title" for="Acc_One_{{$voucherdata->voucher_type_id}}">{{$Acc1}}
                                Account</label>
                            ​
                            <div class="col-lg-2 input-group">
                                <select class="form-control clsSelect2" id="Acc_One_{{$voucherdata->voucher_type_id}}"  style="width: 100%;">
                                    <option value="">Select Option</option>
                                    @foreach($LedgerAcc1 as $data)
                                    <option value="{{ $data->id }}"
                                        data="{{ $data->code.'-'.$data->name }}">
                                        {{ $data->code.'-'.$data->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <label class="col-lg-1 input-title" for="Acc_Two_{{$voucherdata->voucher_type_id}}">{{$Acc2}}
                                Account</label>
                            ​
                            <div class="col-lg-2 input-group">
                                <select class="form-control clsSelect2" id="Acc_Two_{{$voucherdata->voucher_type_id}}" style="width: 100%;">
                                    <option value="">Select Option</option>
                                    @foreach($LedgerAcc2 as $data)
                                    <option value="{{ $data->id }}"
                                        data="{{ $data->code.'-'.$data->name }}">
                                        {{ $data->code.'-'.$data->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <label class="col-lg-1 input-title"
                                for="amount_{{$voucherdata->voucher_type_id}}">Amount</label>

                            <div class="col-lg-2 input-group">
                                <input type="text" class="form-control round" id="amount_{{$voucherdata->voucher_type_id}}"
                                    placeholder="Enter Amount">
                            </div>

                            <label class="col-lg-1 input-title"
                                for="local_narration_{{$voucherdata->voucher_type_id}}">Narration/ Cheque Details</label>

                            <div class="col-lg-2 input-group">
                                <input type="text" class="form-control round"
                                    id="local_narration_{{$voucherdata->voucher_type_id}}" placeholder="Enter Details">
                            </div>
                        </div>

                        <div class="row text-right p-10">
                            <div class="col-lg-12">

                                <?php
                                    $i = 0;
                                    $TableID = "tableID_{$voucherdata->voucher_type_id}";
                                    $ColumnName = "acc_one_arr[]&acc_two_arr[]&amount_arr[]&narration_arr[]";
                                    $ColumnID = "acc_one_id_&acc_two_id_&amount_id_&narration_id_&deleteRow_";
                                ?>
                                <a href="javascript:void(0);" class="btn btn-primary btn-round d-print-none"
                                    onclick="btnAddNewRow('<?=$voucherdata->voucher_type_id?>','<?=$TableID?>', '<?=$ColumnName?>', '<?=$ColumnID?>', 'TotalRowID');">
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
                                    id="tableID_{{$voucherdata->voucher_type_id}}">
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
                                       
                                        
                                        @if(count($vdatad) > 0)
                                        @foreach($vdatad as $index => $Data)
                                        <?php $i++; 
                                        $accOne='';
                                        $accOnename='';
                                        $accTwo ='';
                                        $accTwoname='';
                                       //dd($Data);
                                        // $ColumnName = "acc_one_arr[]&acc_two_arr[]&amount_arr[]&narration_arr[]";
                                        // $ColumnID = "acc_one_id_&acc_two_id_&amount_id_&narration_id_&deleteRow_";

                                        if($voucherdata->voucher_type_id==1){
                                           $accTwo =  $Data->debit_acc ;
                                           $accTwoname =  $Data->LedgerDebit['name'];
                                           $accOne =  $Data->credit_acc ;
                                           $accOnename =  $Data->LedgerCredit['name'] ;
                                        //    dd($accTow);

                                        }else if($voucherdata->voucher_type_id==5) {
                                            $accOne =  $Data->debit_acc ;
                                           $accOnename =  $Data->LedgerDebit['name'];
                                           $accTwo =  $Data->credit_acc ;
                                           $accTwoname =  $Data->LedgerCredit['name'] ;

                                            # code...
                                        }else {
                                            # code...
                                           $accOne =  $Data->debit_acc ;
                                           $accOnename =  $Data->LedgerDebit['name'];
                                           $accTwo =  $Data->credit_acc ;
                                           $accTwoname =  $Data->LedgerCredit['name'] ;
                                        }
                                        ?>

                                        <tr>
                                            <td>
                                                <input type="hidden" id="acc_one_id_{{ $i }}" name="acc_one_arr[]" value="{{$accOne}}"> 
                                                {{ $accOnename }}
                                            </td>
                                            <td>
                                                <input type="hidden" id="acc_two_id_{{ $i }}" name="acc_two_arr[]" value="{{ $accTwo }}">
                                                 {{$accTwoname}}
                                            </td>

                                            <td>

                                                <input type="number" name="amount_arr[]"
                                                    id="amount_id_{{ $i }}" class="form-control round clsAmount"
                                                    placeholder="Enter Quantity" value="{{ $Data->amount }}"
                                                    onkeyup="fnTotalAmount({{$voucherdata->voucher_type_id}});" required min="1">
                                            </td>

                                        
                                            <td>
                                                <input type="text" name="narration_arr[]" id="narration_id_{{ $i }}"
                                                    class="form-control round" value="{{ $Data->local_narration }}">
                                            </td>

                                            

                                            <td>

                                                <a href="javascript:void(0)"
                                                    class="btn btn-dark btn-floating btn-sm d-flex justify-content-center align-items-center clsAddNewRow"
                                                    id="" onclick="btnRemoveRow(this,{{$voucherdata->voucher_type_id}});">
                                                    <i class="icon fa fa-times align-items-center"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                        @endif
                                    </tbody>
                                </table>

                                <!-- Row_Count is temporary variable for using row add and delete-->
                               
                                <input type="hidden" id="TotalRowID_{{$voucherdata->voucher_type_id}}" value="{{ $i }}" />



                            </div>

                        </div>
                        <div class="form-row align-items-center">
                            <label class="col-lg-2 input-title"
                                for="global_narration_{{$voucherdata->voucher_type_id}}">Global Narration Details</label>
                            <div class="col-lg-10 form-group">
                                <div class="input-group ">
                                    <textarea type="text" class="form-control round"
                                        id="global_narration_{{$voucherdata->voucher_type_id}}" name="global_narration"
                                        placeholder="Please Enter Narration."> {{$voucherdata->global_narration}}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="form-row align-items-center">
                            <div class="col-lg-12">
                                <div class="form-group d-flex justify-content-center">
                                    <div class="example example-buttons">
                                        <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
                                        <button type="submit" class="btn btn-primary btn-round d-print-none"
                                            id="fSubmit_{{$voucherdata->voucher_type_id}}" onclick="fnCheckSubmit({{ $voucherdata->voucher_type_id }});window.print();">Update & Print</button>
                                        {{-- <button type="button" class="btn btn-warning btn-round">Reset</button> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>


    </div>
   
</div>


<div class="panel-body">
    <div class="row">
        <div class="col-lg-12">
           
                

        </div>
    </div>
</div>



<script>



function fnsetdcselectbox(value, vDataID , cashAcc, NonCashAcc){

    // console.log(cashAccArr);
    // console.log(NonCashAccArr);
    return false;

    var accCash='<option value="">Select Option</option>';
    var accNonCash='<option value="">Select Option</option>';
    
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


$(document).ready(function() {

    fnAjaxSelectBoxForTargetBranch(
            't_branchCB_{{$voucherdata->voucher_type_id}}',
            '{{$vdatad[0]->ft_to}}',
            '{{base64_encode('acc_account_ledger')}}',
            '{{base64_encode('branch_arr')}}',
            '{{base64_encode('id,name,code')}}',
            '{{url('/ajaxSelectBoxfortargetbranch')}}',
            '{{$vdatad[0]->ft_target_acc}}'
            );

            

    var VoucherType = {{$voucherdata->voucher_type_id}};
    fnTotalAmount(VoucherType);
    

});


function fnGenBill(VoucherType) {

    // console.log('tetst d');
    var BranchID = $('#branch_id').val();
    var vouchertype = $('#voucher_type_id_' + VoucherType).val();
    var projectID = $('#project_id_' + VoucherType).val();
    var project_typeID = $('#project_type_id_' + VoucherType).val();
    if (project_typeID != '' && BranchID != '') {

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
                    // console.log(data);
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
        '') {
        var TotalRowCount = $('#' + TotalRowID +'_'+ vDataID).val();





        TotalRowCount++;
        $('#' + TotalRowID+'_'+ vDataID).val(TotalRowCount);

        var amount = $('#amount_' + vDataID).val();
        var AccTwo = $('#Acc_Two_' + vDataID).find("option:selected").attr('data');
        var AccTwoVal = $('#Acc_Two_' + vDataID).val();
        var AccOne = $('#Acc_One_' + vDataID).find("option:selected").attr('data');
        var AccOneVal = $('#Acc_One_' + vDataID).val();
        var narration = $('#local_narration_' + vDataID).val();
        // console.log(AccTwo);
        // console.log(narration);


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
        html += '<input type="number" name="' + ColumnName[2] + '" id="' + ColumnID[2] + TotalRowCount +
            '" class="form-control round clsAmount"" value="' + amount + '">';
        // html += '' + amount + '';
        html += '</td>';
        html += '<td>';
        html += '<input type="text" class="form-control round" name="' + ColumnName[3] + '" id="' + ColumnID[3] + TotalRowCount +
            '"value="' + narration + '">';
        // html += '' + narration + '';
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
        if ($('#amount_' + vDataID).val() == '') {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Please Enter Amount!',
            });
        } else if ($('#Acc_One_' + vDataID).val() == '' || $('#Acc_Two_' + vDataID).val() == '') {
            swal({
                icon: 'error',
                title: 'Error',
                text: 'Please Select the input Field!',
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
    console.log('ee');
    var varAmount = 0;
    $('.clsAmount').each(function() {
        varAmount = Number(varAmount) + Number($(this).val());
        // console.log($(this).val());
    });
    $('#totalAmount_' + vDataID).html(varAmount);
    // $('#total_amount_' + vDataID).val(varAmount);
    // console.log(varAmount);
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

    // voucher_code_

    //    console.log(vDataID);
    //    console.log('ddddd');







   // $('#issue_form').submit();


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