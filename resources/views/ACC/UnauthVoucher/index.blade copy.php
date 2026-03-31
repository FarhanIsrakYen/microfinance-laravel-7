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
$VoucherTypeData = Common::ViewTableOrder('acc_voucher_type', [['is_delete', 0], ['is_active', 1]], ['id', 'name'], ['name', 'ASC']);
$BrandData = Common::ViewTableOrder('pos_p_brands', [['is_delete', 0], ['is_active', 1]], ['id', 'brand_name'], ['brand_name', 'ASC']);

?>
<!-- Page -->
<div class="panel-body">


    <div class="row align-items-center pb-10 d-print-none">
        <!-- Html View Load For Branch Search -->
        {!! HTML::forBranchFeildSearch('all') !!}
    
    
        <div class="col-lg-2">
          <label class="input-title">Start Date</label>
            <div class="input-group ghdatepicker">
                <div class="input-group-prepend ">
                    <span class="input-group-text ">
                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                    </span>
                </div>
                <input type="text" class="form-control round datepicker-custom" id="start_date" name="start_date"
                    placeholder="DD-MM-YYYY" value="{{$StartDate}}">
            </div>
        </div>
    
    
        <div class="col-lg-2">
          <label class="input-title">End Date</label>
            <div class="input-group ghdatepicker">
                <div class="input-group-prepend ">
                    <span class="input-group-text ">
                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                    </span>
                </div>
                <input type="text" class="form-control round datepicker-custom" id="end_date" name="end_date"
                    placeholder="DD-MM-YYYY" value="{{$EndDate}}">
            </div>
            <div class="help-block with-errors is-invalid"></div>
        </div>
    
    
        <div class="col-lg-2">
            <label class="input-title">Project</label>
            <select class="form-control clsSelect2" name="project_d" id="project_d">
                <option value="">Select Project</option>
                @foreach ($ProjectData as $Row)
                <option value="{{ $Row->id }}">{{ $Row->project_name}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-lg-2">
            <label class="input-title">Project Type</label>
            <select class="form-control clsSelect2" name="project_type_d" id="project_type_d">
                <option value="">Select Project Type</option>
                @foreach ($ProjectTypeData as $Row)
                <option value="{{ $Row->id }}">{{ $Row->project_type_name}}</option>
                @endforeach
            </select>
        </div>

        <div class="col-lg-2">
            <label class="input-title">Voucher Type</label>
            <select class="form-control clsSelect2" name="voucher_type_d" id="voucher_type_d">
                <option value="">Select Voucher Type</option>
                @foreach ($VoucherTypeData as $Row)
                <option value="{{ $Row->id }}">{{ $Row->name}}</option>
                @endforeach
            </select>
        </div>
       
    </div>


    <div class="row text-right p-10">
        <div class="col-lg-11">
            <a href="javascript:void(0)" id="btnSearch" class="btn btn-primary btn-round">Search</a>
        </div>
        <div class="col-lg-1">
            
            <a href="javascript:void(0)" id="btnSearch" onclick="fnAuthorizeAll();"  class="btn btn-primary btn-round">Authorize</a>
        </div>
    </div>



                <div class="row">
                    <div class="col-lg-12">
                        <table class="table w-full table-hover table-bordered table-striped" id="tableID">
                            <thead>
                                <tr>
                                    <th style="width:3%;">SL</th>
                                    <th class="text-center">Voucher Date</th>
                                    <th>Voucher Code</th>
                                    <th>Voucher Type</th>
                                    <th>Project Type</th>
                                    <th>Global Narration</th>
                                    <th>Entry By</th>
                                    {{-- <th class="text-center">Status</th> --}}
                                    <th class="text-center">  <input type="checkbox" id="all_check_id" onclick="fnallCheck();"  title="Select All" /></th>  
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $i = 0;
                                ?>
                                @foreach ($vdata as $Row)
        
                                <tr>
                                    <td scope="row"> {{++$i}}</td>
                                    <td> {{$Row->voucher_date}}</td>
                                    <td> {{$Row->voucher_code}}</td>
                                    <td> {{ $Row->voucherType['name']}}</td>
                                    <td> {{$Row->projectType['project_type_name']}}</td>
                                    <td> {{$Row->global_narration}}</td>
                                    <td> {{ $Row->prep_by}}</td>
                                    {{-- <td  class="text-center text-danger"> {{ ($Row->voucher_status ==1)? "Authorized" : "Unauthoried"}}</td> --}}
                                    <td class="text-center">
                                        <input type="checkbox" class="clsvoucher" data="{{$Row->id}}" id="item_{{$Row->id}}" />
                                        {{-- <a type="button" class="btn btn-primary btn-sm" href="url('acc/auto_unauth_vouchers/auth/'.$Row->id)"><i class="fad fa-info-circle"></i> Authorize </a> --}}
                                    
                                    </td>
        
                                </tr>
        
                                @endforeach
        
                            </tbody>
                            
                        </table>

                    </div>
                </div>
            </div>

<!-- End Page -->
<script>
    $(document).ready(function() {
        $('.page-header-actions').hide();
       // ajaxDataLoad();
    
        $('#btnSearch').click(function() {
    
            var sDate = $('#start_date').val();
            var eDate = $('#end_date').val();
            var branchID = $('#branch_id').val();
            var projectID = $('#project_d').val();
            var ProjectTypeID = $('#project_type_d').val();
            var VoucherTypeID = $('#voucher_type_d').val();

        
           
    
         //   ajaxDataLoad(sDate, eDate, branchID, projectID, ProjectTypeID, VoucherTypeID);
        });
    });
    
    function ajaxDataLoad(sDate = null, eDate = null, branchID = null, projectID = null,
    ProjectTypeID = null, VoucherTypeID = null ) {

            $('#tableID').DataTable({
            destroy: true,
            // retrieve: true,
            processing: true,
            serverSide: true,
            order: [
                [0, "desc"]
            ],
            stateSave: true,
            stateDuration: 1800,
            "ajax": {
                "url": "{{route('unauthvoucherDatatable')}}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{csrf_token()}}",
                    sDate: sDate,
                    eDate: eDate,
                    branchID: branchID,
                    projectID: projectID,
                    ProjectTypeID: ProjectTypeID,
                    VoucherTypeID: VoucherTypeID,
                }
            },


            columns: [
    
                {
                    data: 'id',
                    className: 'text-center'
                },
                {
                    data: 'voucher_date',
                    className: 'text-center'
                },
                {
                    data: 'voucher_code'
                   
                },
                {
                    data: 'voucher_type_id'
                },
                {
                    data: 'project_id'
                },
                {
                    data: 'global_narration'
                },
                {
                    data: 'prep_by'
                },
                {
                    data: 'status',
                    className: 'text-center'
                },
                // {
                //     data: 'action',
                //     name: 'action',
                //     orderable: false,
                //     className: 'text-center'
                // },
    
            ],
            'fnRowCallback': function(nRow, aData, Index) {
                // var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                // $('td:last', nRow).html(actionHTML);
            }
    
        });
    
    
     
    
    }
    
   
    function fnallCheck(){

        // console.log('tetettetete');
        

        // var allcheck = $('#all_check_id').val();

        if($('#all_check_id').is(':checked')){
            $('.clsvoucher').each(function() {

            $(this).prop("checked", true);
            });
            
        }else{
            $('.clsvoucher').each(function() {

            $(this).prop("checked", false);

            });

        }



        // console.log(allcheck);

       

    }
    
    function fnAuthorizeAll() {

        var myObj = [];

        var count = 0;

        $('.clsvoucher').each(function() {

            var RowID = $(this).attr('data');
            // $(this).hide();
            // $(this).prop("checked", false);
            if($("#item_"+RowID).is(':checked')){
                count = count+1; 
            }

            var value = $("#item_"+RowID).is(':checked')? 1 : 0;

                myObj.push({ID:RowID, value:value});
           

        });
        console.log(myObj);


        if(count==0){
            swal({
                        icon: 'info',
                        title: "Please checked voucher.",
                        text: "You must checked at least one vouchers.",

                    });

        }else{

            $.ajax({
                method: "GET",
                url: "{{url('/ajaxVoucherAuth')}}",
                dataType: "text",
                data: {
                    myObj: myObj
                },
                success: function(data) {
                    
                    if (data) {

                        toastr.success("Successfully Authorized");




                            $('.clsvoucher').each(function() {

                            var RowID = $(this).attr('data');
                            if($("#item_"+RowID).is(':checked')){


                                $("#item_"+RowID).closest('tr').remove();



                            }


                            });
                       
                   
                    }else{
                        toastr.error("Authorization Unsuccessfull");
                    }
                    



                }
                

              });

        }

       
    }

    </script>
    @endsection