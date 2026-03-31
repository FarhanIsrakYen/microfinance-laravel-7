@extends('Layouts.erp_master_full_width')
@section('content')

<link rel="stylesheet" href="{{ asset('assets/css/cube-grid-spinner.css') }}">

<div class="panel">
    <div class="panel-body">
        <form method="get" class="form-horizontal" id="filterFormId">
            <div class="row align-items-center pb-10 d-print-none">
                <div class="col-lg-2">
                    <label class="input-title">Branch</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="branchId" id="branchId" required
                            data-error="Please Select Branch">
                            <option value="">Select</option>
                            @foreach ($branchs as $row)
                                <option value="{{ $row->id }}">{{ $row->branch }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Samity</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="samityId" id="samityId">
                            <option value="">All Samity</option>
                            @foreach ($samitys as $samity)
                                <option value="{{ $samity->id }}">{{ $samity->samity }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">From</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="dateFrom" name="dateFrom" value="{{ $sysDate }}" required>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">To</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="dateTo" name="dateTo" value="{{ $sysDate }}" required>
                    </div>
                </div>

                <div class="col-lg-2 pt-20 text-center">
                    <button type="submit" class="btn btn-primary btn-round">Search</button>
                </div>
            </div>
        </form>
    </div>
</div>

<div id="reportingDiv">

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

        $("form").submit(function (event) {
            event.preventDefault();

            if ($('#reportTableDiv').length > 0) {
                $('#reportTableDiv').remove();
            }

            $('#spinnerId').show('slow');
            $("#reportingDiv").load('{{ url()->current() }}/singleTableView' +
                '?' + $("form").serialize(),
                function (response, status, xhr) {

                    if (status == 'success') {
                        $('#spinnerId').hide('slow');
                    } else {
                        alert('error');
                        $('#spinnerId').hide();
                    }
                });
        });

    });

    $('#branchId').change(function (e) {

        var branchId = $(this).val();

        if (branchId == '') {
            return false;
        }

        $.ajax({
            type: "POST",
            url: "{{ url()->current() }}/getData",
            data: {
                context: 'samity',
                branchId: branchId
            },
            dataType: "json",
            success: function (response) {

                $('#samityId')
                        .find('option')
                        .remove()
                        .end()
                        .append(response['samityHtml']);
            },
            error: function () {
                alert('error!');
            }
        });
    });

</script>
@endsection
