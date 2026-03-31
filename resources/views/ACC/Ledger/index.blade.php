@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;
?>

<?php
$StartDate =  Common::systemCurrentDate();
$EndDate = Common::systemCurrentDate();



$ProjectData = Common::ViewTableOrder('gnl_projects', [['is_delete', 0], ['is_active', 1]], ['id', 'project_name'], ['project_name', 'ASC']);
$ProjectTypeData = Common::ViewTableOrder('gnl_project_types', [['is_delete', 0], ['is_active', 1]], ['id', 'project_type_name'], ['project_type_name', 'ASC']);
$AccType = Common::ViewTableOrder('acc_account_type', [['is_delete', 0], ['is_active', 1]], ['id', 'name', 'parent_id'], ['id', 'ASC']);

$AccType_group = $AccType->groupBy('parent_id');

$AccArray = array();
foreach($AccType_group as $key => $accValue){

    $AccArray[$key][] = $key;

    foreach($accValue as $row){
        $AccArray[$row->parent_id][] = $row->id;
    }
}

// dd($AccArray);

// $Data_query_group = $Data_query->groupBy('parent_id');
$BrandData = Common::ViewTableOrder('pos_p_brands', [['is_delete', 0], ['is_active', 1]], ['id', 'brand_name'], ['brand_name', 'ASC']);

$role = $GlobalRole;

// dd($role);

?>
<?php

use App\Services\AccService as ACC;
 // $LedgerArray = Common::LedgerArray();

 //dd($LedgerArray);
?>
<!-- Page -->
<div class="row">
    <div class="col-lg-12">
        <div class="row align-items-center pb-10 d-print-none">
            <!-- Html View Load For Branch Search -->
            <div class="col-lg-2">
                <label class="input-title">Ledger</label>
                <select class="form-control clsSelect2" id="ledger_id">
                    <option value="">Select Ledger</option>
                    @foreach ($AccType as $Row)
                    <option value="<?= (isset($AccArray[$Row->id])) ? implode(',', $AccArray[$Row->id]): $Row->id ?>">{{ $Row->name}}</option>
                    @endforeach
                </select>
            </div>
            {!! HTML::forBranchFeildSearch('all') !!}
        
            <div class="col-lg-2">
                <label class="input-title">Project</label>
                <select class="form-control clsSelect2"  id="project_d">
                    <option value="">Select Project</option>
                    @foreach ($ProjectData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->project_name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <label class="input-title">Project Type</label>
                <select class="form-control clsSelect2"  id="project_type_d">
                    <option value="">Select Project Type</option>
                    @foreach ($ProjectTypeData as $Row)
                    <option value="{{ $Row->id }}">{{ $Row->project_type_name}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-lg-2">
                <div class="row text-right p-10">
                    <div class="col-lg-12">
                        <a href="javascript:void(0)" id="btnSearch" class="btn btn-primary btn-round">Search</a>
                    </div>
                </div>
            </div>
    
           
        </div>
    
    
        
   </div>
 </div>
 
               <div class="row">
               <div class="col-lg-12" id="divID">
               
              </div>
            </div>



        

<!-- End Page -->
<Script>
    $(document).ready(function() {

        var role = <?= json_encode($GlobalRole) ?>;
      
        $('#btnSearch').click(function() {
    
            var Ledger = $('#ledger_id').val();
            
            var branchID = $('#branch_id').val();
            var projectID = $('#project_d').val();
            // var ProjectTypeID = $('#project_type_d').val();
           

            $('#tableID').empty();
            var table = '<table class="table w-full table-hover table-bordered table-striped dataTable" id="tableID">';
            table += '<thead><tr> <th style="width:3%;">SL</th><th>Ledger</th> <th>Code</th>  <th>Account Type</th>  <th class="text-center">Action</th> </tr> </thead>';
            table +='<tbody>  </tbody></table>'    ;  
            

            $('#divID').html(table);

            fnLoadLedgerTable(role , Ledger , branchID, projectID );
           

        });

            var table ='';

            table += '<table class="table w-full table-hover table-bordered table-striped dataTable" id="tableID">';
            table += '<thead><tr> <th style="width:3%;">SL</th><th>Ledger</th> <th>Code</th>  <th>Account Type</th>  <th class="text-center">Action</th> </tr> </thead>';
            table +='<tbody>  </tbody></table>'    ;  
            

            $('#divID').html(table);
           

        
        
        fnLoadLedgerTable(role);
        //  console.log(role);





    });

function fnLoadLedgerTable(Role = null ,AccType = null ,BranchID= null,ProjectID= null) {


    if (Role != '') {
        $.ajax({
            method: "GET",
            url: "{{ route('ajaxLedgerTable')}}",
            dataType: "text",
            data: {
                Role: Role,
                AccType: AccType,
                BranchID: BranchID,
                ProjectID:ProjectID,
            },
            success: function(data) {
                if (data) {
                    // $('#bill_no').val(data);

                    $('#tableID tbody').after(data);
                    // console.log(data);
                }
            }
        });
    }
}


function fnDelete(RowID) {
    $.ajax({
        method: "GET",
        url: "{{url('/ajaxdDeleteLedger')}}",
        dataType: "text",
        data: {
            RowID: RowID,
        },
        success: function(data) {
            if (data) {
                if(data=='ok'){
                 // location.reload()
                  fnDeleteCheck(
                    "{{url('acc/ledger/delete/')}}",
                    "{{url('/ajaxDeleteCheck')}}",
                    RowID,

                 );
               

                }else{
                     swal({
                    icon: 'error',
                    title: 'Error',
                    text: 'You Must Delete The Child Data First!!',
                });

                }

                
            }
        }
    });

}

</Script>


@endsection
