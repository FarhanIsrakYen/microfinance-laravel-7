@extends('Layouts.erp_master_full_width')
@section('content')

<style>

    .selectize-control div.active {
        background-color: lightblue;
    }

    .selectize-control .lebel {
        color: #804739;
        font-weight: bold;
    }
</style>
<link rel="stylesheet" href="{{ asset('assets/css/cube-grid-spinner.css') }}">

<div class="panel">
    <div class="panel-body">
        <form method="get" class="form-horizontal" id="filterFormId">
            <div class="row align-items-center pb-10 d-print-none">
                @if(Auth::user()->branch_id == 1)
                    <div class="col-lg-2">
                        <label class="input-title">Branch</label>
                        <div class="input-group">
                            <select class="form-control" name="branchId" id="branchId" required
                                data-error="Please Select Branch">
                                <option value="">Select</option>
                            </select>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="branchId" id="branchId">
                @endif

                <div class="col-lg-2">
                    <label class="input-title">F. Org</label>
                    <div class="input-group">
                        <select class="form-control" name="fundingOrg" id="fundingOrg">
                            <option value="">All</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">P. Ctg</label>
                    <div class="input-group">
                        <select class="form-control" name="productCategories" id="productCategories">
                            <option value="">All</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Product</label>
                    <div class="input-group">
                        <select class="form-control" name="product" id="product">
                            <option value="">All</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">From</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="fromDate" name="fromDate"
                            value="{{ $sysDate }}">
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">To</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="toDate" name="toDate"
                            value="{{ $sysDate }}">
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Options</label>
                    <div class="input-group">
                        <select class="form-control" name="options" id="options" required
                            data-error="Please Select Branch">
                            <option value="">Select</option>
                            <option value="1">Load Product</option>
                            <option value="2">Load Product category</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2 pt-20 text-center ml-auto">
                    {{-- <a href="javascript:void(0)" class="btn btn-primary btn-round" id="searchButton">Search</a> --}}
                    <button type="submit" class="btn btn-primary btn-round">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="panel">
    <div class="panel-body pdf-export">
        <div class="row text-center d-print-block">
            <div class="col-lg-12" style="color:#000;">
                <strong>{{ $groupName }}</strong><br>
                <strong>{{ $branchName }}</strong><br>
                <span>Preriodic Collection Report Component Wise</span><br>
                (<span id="start_date_txt"></span>
                to
                <span id="end_date_txt"></span>)
            </div>
        </div>
        <div class="row d-print-none text-right" data-html2canvas-ignore="true">
            <div class="col-lg-12">
                <a href="javascript:void(0)" onClick="window.print();" style="background-color:transparent;border:none;"
                    class="btnPrint mr-2">
                    <i class="fa fa-print fa-lg" style="font-size:20px;"></i>
                </a>
                {{-- <a href="javascript:void(0)" onclick="getPDF();">
                    <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
                </a>
                <a href="javascript:void(0)" onclick="fnDownloadXLSX();">
                    <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                </a> --}}
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                {{-- <span class="d-print-none"><b>Total Row:</b> <span id="totalRowDiv">0</span></span> --}}
            </div>
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                <span><b>Printed Date:</b> {{ date("d-m-Y") }} </span>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive" id="reportingDiv">
                    {{-- load table --}}
                </div>
            </div>
        </div>
    </div>
</div>

<div class="sk-cube-grid" id="spinnerId" style="display: none;">
    <div class="sk-cube sk-cube1"></div>
    <div class="sk-cube sk-cube2"></div>
    <div class="sk-cube sk-cube3"></div>
    <div class="sk-cube sk-cube4"></div>
    <div class="sk-cube sk-cube5"></div>
    <div class="sk-cube sk-cube6"></div>
    <div class="sk-cube sk-cube7"></div>
    <div class="sk-cube sk-cube8"></div>
    <div class="sk-cube sk-cube9"></div>
</div>

<link rel="stylesheet" href="{{ asset('assets/css/selectize.bootstrap3.min.css') }}">
<script src="{{ asset('assets/js/selectize.min.js') }}"></script>

<script>
    $(document).ready(function () {

        /* branch selectize */
        @if(Auth::user()->branch_id == 1)
            selectizeBranch(<?= json_encode($branchs) ?>);
            selectizeFundingOrg(<?= json_encode($fundingOrg) ?>);
            selectizeProductCat(<?= json_encode($prodCat) ?>);
            selectizeProducts(<?= json_encode($products) ?>);
        @else
            selectizeFundingOrg(<?= json_encode($fundingOrg) ?>);
            selectizeProductCat(<?= json_encode($prodCat) ?>);
            selectizeProducts(<?= json_encode($products) ?>);
            $('#branchId').val({{ $branchs }});
        @endif

        $("#filterFormId").submit(function (event) {
            event.preventDefault();

            if ($('#reportTableDiv').length > 0) {
                $('#reportTableDiv').remove();
            }
            
            $('#spinnerId').show('slow');
            $("#reportingDiv").load('./PCRCWReport' +
                '?' + $("#filterFormId").serialize(), function(response, status, xhr){

                    if (status == 'success') {
                        $('#spinnerId').hide('slow');
                    }
                    else{
                        alert('error');
                        $('#spinnerId').hide();
                    }
                });
        });
    });

    function selectizeBranch(options) {

        $('#branchId').selectize({
            valueField: 'id',
            labelField: 'branch',
            searchField: ['branch_code', 'branch_name'],
            sortField: [{
                field: "branch_code",
                direction: "asc"
            }],
            // sortDirection: 'asc',
            placeholder: 'Select Branch',
            highlight: true,
            allowEmptyOption: true,
            maxItems: 1,
            //only using options-value in jsfiddle - real world it's using the load-function
            options: options,
            create: false,
            render: {
                option: function (branch, escape) {

                    return '<div>' +
                        '<span class="lebel">' + branch.branch_code + ' - ' + branch.branch_name +
                        '</span>' +
                        '</div>';

                }
            }
        });
    }

    
    function selectizeFundingOrg(options) {
        
        $('#fundingOrg').selectize({
            valueField: 'id',
            labelField: 'fundingOrg',
            searchField: ['id', 'name'],
            sortField: [{
                field: "id",
                direction: "asc"
            }],
            // sortDirection: 'asc',
            placeholder: 'Select F. Org',
            highlight: true,
            allowEmptyOption: true,
            maxItems: 1,
            //only using options-value in jsfiddle - real world it's using the load-function
            options: options,
            create: false,
            render: {
                option: function (fundingOrg, escape) {

                    return '<div>' +
                        '<span class="lebel">' + fundingOrg.id + ' - ' + fundingOrg.name + '</span>' +
                        '</div>';
                        
                }
            }
        });
    }

    function selectizeProductCat(options) {
        
        $('#productCategories').selectize({
            valueField: 'id',
            labelField: 'prodCat',
            searchField: ['id', 'name'],
            sortField: [{
                field: "id",
                direction: "asc"
            }],
            // sortDirection: 'asc',
            placeholder: 'Select P. Cat',
            highlight: true,
            allowEmptyOption: true,
            maxItems: 1,
            //only using options-value in jsfiddle - real world it's using the load-function
            options: options,
            create: false,
            render: {
                option: function (prodCat, escape) {

                    return '<div>' +
                        '<span class="lebel">' + prodCat.id + ' - ' + prodCat.name + '</span>' +
                        '</div>';
                        
                }
            }
        });
    }

    function selectizeProducts(options) {
        
        $('#product').selectize({
            valueField: 'id',
            labelField: 'product',
            searchField: ['id', 'name'],
            sortField: [{
                field: "id",
                direction: "asc"
            }],
            // sortDirection: 'asc',
            placeholder: 'Select Product',
            highlight: true,
            allowEmptyOption: true,
            maxItems: 1,
            //only using options-value in jsfiddle - real world it's using the load-function
            options: options,
            create: false,
            render: {
                option: function (product, escape) {

                    return '<div>' +
                        '<span class="lebel">' + product.id + ' - ' + product.name + '</span>' +
                        '</div>';
                        
                }
            }
        });
    }
    
    $('#productCategories').change(function (e) {

        var element = jQuery('#product');
  
        if(element[0].selectize){
            element[0].selectize.destroy();
        }

        var prodCatId = $(this).val();

        if (prodCatId == '') {
            return false;
        }

        $.ajax({
            type: "POST",
            url: "./pcrGetData",
            data: {
                context: 'product',
                prodCatId: prodCatId
            },
            dataType: "json",
            success: function (response) {

                /* $.each(response['products'], function (index, value, code) { 
                    $('#product').append("<option value="+index+">"+value+"</option>");
                }); */

                selectizeProducts(response['products']);
            },
            error: function () {
                alert('error!');
            }
        });
    });


    $("form").submit(function (event) {
        event.preventDefault();

        if ($("#branchId").val() != '') {
            console.log('in');

            $("#reportingDiv").load(
                './loadReportData' +
                '?' + $("form").serialize());
        } else {

            swal({
                icon: 'warning',
                title: 'Select Search Field',
                text: 'You have have to select Branch or  Branch and Samity.',
            });

        }
        // $("#loadingModal").show();

    });
</script>
@endsection
