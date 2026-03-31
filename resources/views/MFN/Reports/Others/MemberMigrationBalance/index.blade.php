@extends('Layouts.erp_master_full_width')
@section('content')
<div class="panel">
    <div class="panel-body">
        <form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator"
            novalidate="true" autocomplete="off">
            @csrf
            <!-- Search Options -->
            <div class="row align-items-center pb-10 mb-4 d-print-none">

                @if (count($branchces) > 1)
                <div class="col-lg-2">
                    <label class="input-title">Branch</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="branch" id="branch">
                            <option value="">Select</option>
                            @foreach ($branchces as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->branch_code . ' - ' . $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <div class="col-lg-2">
                    <label class="input-title">Samity Name</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="samity" id="samity">
                            <option value="">All</option>
                            @foreach ($samities as $samity)
                            <option value="{{ $samity->id }}">{{ $samity->samityCode . ' - '. $samity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Date</label>
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
                <div class="col-lg-2">
                    <label class="input-title">Service Charge</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="service_charge">
                            <option value="1">With</option>
                            <option value="0">Without</option>

                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Funding Organization</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="funding_org">
                            <option value="">All</option>
                            @foreach ($fundindOrgs as $fundindOrg)
                            <option value="{{ $fundindOrg->id }}">{{ $fundindOrg->name }}</option>
                            @endforeach

                        </select>
                    </div>
                </div>
                <div class="col-lg pt-20 text-right">
                    {{-- <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                        id="searchButton">Search</a> --}}
                    <button class="btn btn-primary btn-round" id="search_id">Show</button>
                </div>


            </div>

        </form>

        <div class="row">
            <div class="col-md-12" id="reportingDiv">

            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        $('.page-header-actions').hide();
        $("form").submit(function (event) {
            event.preventDefault();

            if ($("#branch").val() != '') {

                $("#reportingDiv").load(
                    "{{ url()->current() }}" + '/print_report' +
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

        /* on selecting branch get samity */
        $("#branch").on('change', function () {
            $("#samity option:gt(0)").remove();
            if ($(this).val() == '') {
                return false;
            }
            var branchId = $(this).val();

            $.ajax({
                type: "post",
                url: "{{ url()->current() }}" + '/getData',
                data: {
                    context : 'branch',
                    branchId : branchId
                },
                dataType: "json",
                success: function (response) {
                    $.each(response.samities, function (index, obj) { 
                         $("#samity").append("<option value=" + obj.id + ">"+ obj.name +"</option>");
                    });
                },
                error: function (response) {
                   alert('Error');
                }
            });
        });
        /* end on selecting branch get samity */


    });
</script>

@endsection