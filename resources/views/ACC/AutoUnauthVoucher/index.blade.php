@extends('Layouts.erp_master')
@section('content')

<?php
use App\Services\CommonService as Common;
use App\Services\HtmlService as HTML;

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

        <div class="col-lg-2">
            <label class="input-title">Start Date</label>
            <div class="input-group ghdatepicker">
                <div class="input-group-prepend ">
                    <span class="input-group-text ">
                        <i class="icon wb-calendar round" aria-hidden="true"></i>
                    </span>
                </div>
                <input type="text" class="form-control round datepicker-custom" id="start_date" name="start_date"
                    placeholder="DD-MM-YYYY">
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
                    placeholder="DD-MM-YYYY">
            </div>
            <div class="help-block with-errors is-invalid"></div>
        </div>

    </div>


    <div class="row text-right p-10">
        <div class="col-lg-11">
            <a href="javascript:void(0)" id="btnSearch" class="btn btn-primary btn-round">Search</a>
        </div>
        <div class="col-lg-1">
            <a href="javascript:void(0)" id="btnSearch" onclick="fnAuthorizeAll();"
                class="btn btn-primary btn-round">Authorize</a>
        </div>
        <div class="help-block with-errors is-invalid"></div>
    </div>


    <div class="row">
        <div class="col-lg-12 table-responsive">
            <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                <thead>
                    <tr>
                        <th style="width:3%;">SL</th>
                        <th class="text-center">Voucher Date</th>
                        <th>Voucher Code</th>
                        <th>Voucher Type</th>
                        <th>Project Type</th>
                        <th>Branch Name</th>
                        <th>Total Amount</th>
                        <th>Global Narration</th>
                        <th>Entry By</th>
                        <th class="text-center"> <input type="checkbox" id="all_check_id" onclick="fnallCheck();"
                                title="Select All" /></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('.page-header-actions').hide();

        console.log('hshhsh');
        ajaxDataLoad();

        $('#btnSearch').click(function () {

            var sDate = $('#start_date').val();
            var eDate = $('#end_date').val();
            var branchID = $('#branch_id').val();
            var projectID = $('#project_d').val();
            var ProjectTypeID = $('#project_type_d').val();
            var VoucherTypeID = $('#voucher_type_d').val();

            ajaxDataLoad(sDate, eDate, branchID, projectID, ProjectTypeID, VoucherTypeID);
        });
    });

    function ajaxDataLoad(sDate = null, eDate = null, branchID = null, projectID = null,
        ProjectTypeID = null, VoucherTypeID = null) {

        $('.clsDataTable').DataTable({
            destroy: true,
            // retrieve: true,
            processing: true,
            serverSide: true,
            order: [
                [1, "DESC"]
            ],
            // stateSave: true,
            // stateDuration: 1800,
            "ajax": {
                "url": "{{route('autounauthvoucherDatatable')}}",
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
            columns: [{
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
                    data: 'voucher_type_id',
                    orderable: false
                },
                {
                    data: 'project_id',
                    orderable: false
                },
                {
                    data: 'branch_id',
                    orderable: false
                },
                {
                    data: 'sum',
                    className: 'text-right'
                },
                {
                    data: 'global_narration',
                    orderable: false
                },
                {
                    data: 'prep_by',
                    orderable: false
                },
                {
                    data: 'status',
                    className: 'text-center',
                    orderable: false
                },

            ],
            'fnRowCallback': function (nRow, aData, Index) {
                // var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                // $('td:last', nRow).html(actionHTML);
            }
        });
    }


    function fnallCheck() {
        if ($('#all_check_id').is(':checked')) {
            $('.clsvoucher').each(function () {
                $(this).prop("checked", true);
            });
        } else {
            $('.clsvoucher').each(function () {
                $(this).prop("checked", false);
            });
        }
    }

    function fnAuthorizeAll() {

        swal({
                title: "Are you sure to authorized voucher ?",
                // text: "Once , your record will be activated!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((willDelete) => {
                if (willDelete) {
                    var myObj = [];
                    var count = 0;

                    $('.clsvoucher').each(function () {
                        var RowID = $(this).attr('data');

                        if ($("#item_" + RowID).is(':checked')) {
                            count = count + 1;
                        }

                        var value = $("#item_" + RowID).is(':checked') ? 1 : 0;
                        myObj.push({
                            ID: RowID,
                            value: value
                        });
                    });

                    if (count == 0) {
                        swal({
                            icon: 'info',
                            title: "Please checked voucher.",
                            text: "You must checked at least one vouchers.",
                        });
                    } else {

                        $.ajax({
                            method: "POST",
                            url: "{{url('/ajaxVoucherAuth')}}",
                            dataType: "text",
                            data: {
                                myObj: myObj
                            },
                            success: function (data) {
                                if (data) {
                                    toastr.success("Successfully Authorized");
                                    $('.clsvoucher').each(function () {
                                        var RowID = $(this).attr('data');
                                        if ($("#item_" + RowID).is(':checked')) {
                                            $("#item_" + RowID).closest('tr').remove();
                                        }
                                    });
                                } else {
                                    toastr.error("Authorization Unsuccessfull");
                                }
                            }
                        });
                    }
                }
            });
    }

</script>
@endsection