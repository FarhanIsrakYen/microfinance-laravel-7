@extends('Layouts.erp_master_full_width')
@section('content')

<div class="panel">
    <div class="panel-body">
        <form enctype="multipart/form-data" method="post" class="form-horizontal" 
            data-toggle="validator" novalidate="true" autocomplete="off" id="filterFormId">
            @csrf
        <!-- Search Options -->
            <div class="row align-items-center pb-10 mb-4 d-print-none">
                


                @if (count($branchData) > 1)
                <div class="col-lg-3">
                    <label class="input-title">Branch</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="branch" id="branch_id"
                        onchange="fnAjaxSelectBox('samity_id',
                                            this.value,
                                '{{ base64_encode('mfn_samity')}}',
                                '{{base64_encode('branchId')}}',
                                '{{base64_encode('id,samityCode,name')}}',
                                '{{url('/ajaxSelectBox')}}',
                                '{{null}}',
                                '{{'isActiveOff'}}'
                                        );"
                                        >
                            <option value="">Select Option</option>
                            @foreach ($branchData as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->branch_code.' - '.$branch->branch_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif    
                
                <div class="col-lg-3">
                    <label class="input-title">Samity Name</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="samity" id="samity_id">
                            <option value="">Select All</option>
                            @foreach ($samityData as $samity)
                            <option value="{{ $samity->id }}">{{ $samity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
               
                {{-- <div class="col-lg-2">
                    
                    <label class="input-title">Primary Product Category</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="category"
                        onchange="fnAjaxSelectBox('product_id',
                                                this.value,
                                    '{{ base64_encode('mfn_loan_products')}}',
                                    '{{base64_encode('productCategoryId')}}',
                                    '{{base64_encode('id,name')}}',
                                    '{{url('/ajaxSelectBox')}}',
                                '{{null}}',
                                '{{'isActiveOff'}}'
                                            );">
                            <option value="">Select One</option>
                            @foreach ($loanProdCategoryData as $Row)
                            <option value="{{ $Row->id }}">{{ $Row->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Primary Product</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="productId" id="product_id">
                            <option value="">Select One</option>
                            
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Savings Product</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="savingsId" id="savings_id">
                            <option value="">Select One</option>
                            @foreach ($savingsProductData as $savingsProduct)
                                <option value="{{ $savingsProduct->id }}">{{ $savingsProduct->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div> --}}
                <div class="col-lg-3">
                    <label class="input-title">Date From</label>
                    
                    <div class="input-group ghdatepicker">
                        <div class="input-group-prepend ">
                            <span class="input-group-text ">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control round datepicker-custom" id="date" name="date"
                         value="{{\Carbon\Carbon::parse($sysDate)->format('d-m-Y')}}">
                    </div>
                </div>
                <div class="col-lg-3">
                    <label class="input-title">Date To</label>
                    
                    <div class="input-group ghdatepicker">
                        <div class="input-group-prepend ">
                            <span class="input-group-text ">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control round datepicker-custom" id="date_to" name="date_to"
                         value="{{\Carbon\Carbon::parse($sysDate)->format('d-m-Y')}}">
                    </div>
                </div>
                <div class="col-lg pt-20 text-right">
                    {{-- <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                        id="searchButton">Search</a> --}}
                        <button class="btn btn-primary btn-round" id="search_id" >Search</button>
                </div>

            
            </div>

        </form>

        <div class="row">
            <div class="col-md-12"  id="reportingDiv">
                
            </div>
        </div>
        {{-- <div class="row align-items-center pb-10 d-print-none">
            @if(Auth::user()->branch_id == 1)
                <div class="col-lg-3">
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

            <div class="col-lg-3">
                <label class="input-title">Samity</label>
                <div class="input-group">
                    <select class="form-control" name="samityId" id="samityId" required
                        data-error="Please Select Samity">
                        <option value="">Select</option>
                    </select>
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">Start Date</label>
                <div class="input-group">
                    <input type="text" class="form-control datepicker-custom" id="startDate" name="startDate" placeholder="DD-MM-YYYY" value="{{ $sysDate }}">
                </div>
            </div>

            <div class="col-lg-2">
                <label class="input-title">End Date</label>
                <div class="input-group">
                    <input type="text" class="form-control datepicker-custom" id="endDate" name="endDate" placeholder="DD-MM-YYYY" value="{{ $sysDate }}">
                </div>
            </div>

            <div class="col-lg-2 pt-20 text-center ml-auto">
                <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                    id="searchButton">Search</a>
            </div>
        </div> --}}
    </div>
</div>

{{-- <div class="panel">
    <div class="panel-body pdf-export">
        <div class="row text-center d-print-block">
            <div class="col-lg-12" style="color:#000;">
                <strong>{{ $groupName }}</strong><br>
                <strong>{{ $branchName }}</strong><br>
                <span>All Write-Off Report</span><br>
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
                <a href="javascript:void(0)" onclick="getPDF();">
                    <i class="fa fa-file-pdf-o fa-lg" style="font-size:20px;"></i>
                </a>
                <a href="javascript:void(0)" onclick="fnDownloadXLSX();">
                    <i class="fa fa-file-excel-o fa-lg" style="font-size:20px; margin-left: 5px;"></i>
                </a>
            </div>
        </div>
        <div class="row">
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6">
                <span class="d-print-none"><b>Total Row:</b> <span id="totalRowDiv">0</span></span>
            </div>
            <div class="col-xl-6 col-lg-6 col-sm-6 col-md-6 col-6 text-right">
                <span><b>Printed Date:</b> {{ date("d-m-Y") }} </span>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
                        <thead class="text-center">
                            <tr>
                                <th rowspan="2" style="width: 3%;">SL</th>
                                <th colspan="2" class="text-center">Member</th>
                                <th rowspan="2">Loan Code</th>
                                <th rowspan="2">Loan Product</th>
                                <th rowspan="2">Samity Code</th>
                                <th rowspan="2">Samity Name</th>
                                <th rowspan="2">Waiver Date</th>
                                <th rowspan="2" class="text-center">Loan Amount</th>
                                <th rowspan="2" class="text-center">Write-Off Amount</th>
                            </tr>
                            <tr>
                                <th class="text-center">Name</th>
                                <th class="text-center">Code</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td colspan="8" class="text-right"><b>TOTAL: </b></td>
                                <td><b id="ttlLoanAmount">0</b></td>
                                <td><b id="ttlWriteOffAmount">0</b></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div> --}}

<link rel="stylesheet" href="{{ asset('assets/css/selectize.bootstrap3.min.css') }}">
<script src="{{ asset('assets/js/selectize.min.js') }}"></script>

<style>
    .selectize-control div.active {
        background-color: lightblue;
    }

    .selectize-control .lebel {
        color: #804739;
        font-weight: bold;
    }

</style>

<script>
    $(document).ready(function() {
        $('.page-header-actions').hide();
       $("#filterFormId").submit(function( event ) {
        event.preventDefault();
        
            if( ($("#branch_id").val() !='') ){
    
                $("#reportingDiv").load('{{URL::to("mfn/reports/writeOffReport/loadData")}}'+'?'+$("#filterFormId").serialize());
            }else{
                swal({
                                icon: 'warning',
                                title: 'Select Search Field',
                                text: 'You have to select Branch.',
                            });
            }
        });
    }); 
    
</script>
{{-- <script>
    $(document).ready(function () {

        /* branch selectize */
        @if(Auth::user()->branch_id == 1)
            selectizeBranch(<= json_encode($branchs) ?>);
        @else
            selectizeSamity(<= json_encode($samitys) ?>);
            $('#branchId').val({{ $branchs }});
        @endif

        $('#searchButton').click(function () {
            var branchId = $('#branchId').val();
            var samityId = $('#samityId').val();
            var startDate = $('#startDate').val();
            var endDate = $('#endDate').val();

            $('#start_date_txt').html(startDate);
            $('#end_date_txt').html(endDate);

            ajaxDataLoad(branchId, samityId, startDate, endDate);
        });
    });

    function ajaxDataLoad(branchId = null, samityId = null, startDate = null, endDate = null) {

        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            // lengthMenu: [[10, 20, 30, 50, -1], [10, 20, 30, 50, "All"]],
            paging: false,
            ordering: false,
            info: false,
            searching: false,
            "ajax": {
                "url": "{{ url()->current() }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    branchId: branchId,
                    samityId: samityId,
                    startDate: startDate,
                    endDate: endDate
                }
            },
            "columns": [
                {
                    data     : 'sl',
                    name     : 'sl',
                    orderable: false,
                    targets  : 0,
                    className: 'text-center'
                },
                {
                    "data"   : "memberName"
                },
                {
                    "data"   : "memberCode"
                },
                {
                    "data"   : "loanCode"
                },
                {
                    "data"   : "loanProd"
                },
                {
                    "data"   : "samityCode",
                },
                {
                    "data"   : "samityName",
                    className: 'text-center'
                },
                {
                    "data"   : "writeOffDate",
                    className: 'text-center'
                },
                {
                    "data"   : "loanAmount",
                    className: 'text-right'
                },
                {
                    "data"   : "writeOffAmount",
                    className: 'text-right'
                }
            ],
            drawCallback: function (oResult) {
                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#ttlLoanAmount').html(oResult.json.ttlLoanAmount);
                    $('#ttlWriteOffAmount').html(oResult.json.ttlWriteOffAmount);
                }
            }

        });
    }

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

    $('#branchId').change(function (e) {

        var element = jQuery('#samityId');

        if (element[0].selectize) {
            element[0].selectize.destroy();
        }

        var branchId = $(this).val();

        if (branchId == '') {
            return false;
        }

        $.ajax({
            type: "POST",
            url: "./waiverGetData",
            data: {
                context: 'samity',
                branchId: branchId
            },
            dataType: "json",
            success: function (response) {

                selectizeSamity(response['samitys']);
            },
            error: function () {
                alert('error!');
            }
        });
    });

    function selectizeSamity(options) {

        $('#samityId').selectize({
            valueField: 'id',
            labelField: 'samity',
            searchField: ['samityCode', 'name'],
            sortField: [{
                field: "samityCode",
                direction: "asc"
            }],
            // sortDirection: 'asc',
            placeholder: 'Select Samity',
            highlight: true,
            allowEmptyOption: true,
            maxItems: 1,
            //only using options-value in jsfiddle - real world it's using the load-function
            options: options,
            create: false,
            render: {
                option: function (samity, escape) {

                    return '<div>' +
                        '<span class="lebel">' + samity.samityCode + ' - ' + samity.name + '</span>' +
                        '</div>';

                }
            }
        });
    }

</script> --}}
@endsection
