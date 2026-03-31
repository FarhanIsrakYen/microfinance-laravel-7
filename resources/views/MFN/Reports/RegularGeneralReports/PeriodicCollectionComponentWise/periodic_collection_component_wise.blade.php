@extends('Layouts.erp_master_full_width')
@section('content')
<style type="text/css" media="print">
    @page { size: landscape; }
  </style>
<style>

    .selectize-control div.active {
        background-color: lightblue;
    }

    .selectize-control .lebel {
        color: #804739;
        font-weight: bold;
    }
    select{
        padding: 0 5px;
    }
    .page-header-actions{
        display: none;
    }
</style>
<link rel="stylesheet" href="{{ asset('assets/css/cube-grid-spinner.css') }}">

<div class="panel">
    <div class="panel-body">
        <form method="get" class="form-horizontal" id="filterFormId">
            <div class="row align-items-center pb-10 d-print-none">

                {{-- <div class="col-lg-2 mt-1">
                    <label class="input-title">Filter By</label>
                    <div class="input-group">
                        <select class="form-control" name="filterBy" id="filterBy">
                            <option value="">Zone</option>
                            <option value="">Area</option>
                            <option value="">Branch</option>
                        </select>
                    </div>
                </div> --}}


                @if(count($branchs) > 1)
                    <div class="col-lg-2 mt-1 filter-div">
                        <label class="input-title">Branch</label>
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="branchId" id="branchId" required
                                data-error="Please Select Branch">
                                <option value="">Select</option>
                                @foreach ($branchs as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->branch_code }} - {{ $branch->branch_name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="branchId" id="branchId" value="{{ Auth::user()->branch_id }}">
                @endif

                <div class="col-lg-2 mt-1">
                    <label class="input-title">F. Org</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="fundingOrg" id="fundingOrg">
                            <option value="">All</option>
                            @foreach ($fundingOrg as $org)
                            <option value="{{ $org->id }}">{{ $org->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2 mt-1">
                    <label class="input-title">P. Ctg</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="productCategories" id="productCategories">
                            <option value="">All</option>
                            @foreach ($prodCat as $item)
                            <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2 mt-1">
                    <label class="input-title">Product</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="product" id="product">
                            <option value="">All</option>
                            
                        </select>
                    </div>
                </div>

                <div class="col-lg-2 mt-1">
                    <label class="input-title">From</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="fromDate" name="fromDate"
                            value="{{ $sysDate }}">
                    </div>
                </div>

                <div class="col-lg-2 mt-1">
                    <label class="input-title">To</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="toDate" name="toDate"
                            value="{{ $sysDate }}">
                    </div>
                </div>

                <div class="col-lg-2 mt-1">
                    <label class="input-title">Options</label>
                    <div class="input-group">
                        <select class="form-control" name="options" id="options" required
                            data-error="Please Select Branch">
                            <option value="">Select</option>
                            <option value="1">Loan Product</option>
                            <option value="2">Loan Product category</option>
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

        $("#filterFormId").submit(function (event) {
            event.preventDefault();

            if ($('#reportTableDiv').length > 0) {
                $('#reportTableDiv').remove();
            }

            if($('#branchId').val() == ''){
                swal({
                    icon: 'error',
                    title: 'Oops...',
                    text: 'You Must select a Branch'
                });
                return;
            }
            
            // $('#spinnerId').show('slow');
            $("#reportingDiv").load('{{ url()->current() }}/loadReportData' +
                '?' + $("#filterFormId").serialize(), function(response, status, xhr){

                    if (status == 'success') {
                        // $('#spinnerId').hide('slow');
                        $('#start_date_txt').html($('#fromDate').val());
                        $('#end_date_txt').html($('#toDate').val());
                    }
                    else{
                        alert('error');
                        $('#spinnerId').hide();
                    }
                });
        });
    });

    $('#branchId, #fundingOrg, #productCategories').change(function(e){
        populateProductDropDown();
    });
    
    @if(Auth::user()->branch_id != 1)
        populateProductDropDown();
    @endif

    function populateProductDropDown(){
        var prodCatId = $("#productCategories").val();
        var fundingOrgId = $('#fundingOrg').val();
        var branchId = $('#branchId').val();
        
        if (branchId == '') {
            alert('branch must be selected');
            return false;
        }

        $.ajax({
            type: "POST",
            url: "{{ url()->current() }}/getData",
            data: {
                prodCatId: prodCatId,
                fundingOrgId: fundingOrgId,
                branchId: branchId
            },
            dataType: "json",
            success: function (response) {
                $(`#product option:gt(0)`).remove();
                $.each(response.data, function (index, value) { 
                    $('#product').append(`<option value='${value.id}'>${value.productCode} - ${value.name}</option>`);
                });
            },
            error: function () {
                alert('error!');
            }
        });
    }

    function showFilterDiv(id){
        
    }
</script>
@endsection
