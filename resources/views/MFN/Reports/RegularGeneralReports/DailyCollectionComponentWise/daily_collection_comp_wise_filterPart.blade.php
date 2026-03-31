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
                    <label class="input-title">Product Category</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="productCategories" id="productCategories">
                            <option value="">All</option>
                            @foreach($prodCat as $row)
                                <option value="{{ $row->id }}">{{ $row->prodCat }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Product</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="product" id="product">
                            <option value="">All</option>
                            @foreach ($products as $prod)
                                <option value="{{ $prod->id }}">{{ $prod->product }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Date</label>
                    <div class="input-group">
                        <input type="text" class="form-control datepicker-custom" id="date" name="date"
                            value="{{ $sysDate }}" required>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Saving Recoverable</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="savingRecoerable" id="savingRecoerable">
                            <option value="No">No</option>
                            <option value="Yes">Yes</option>
                        </select>
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

        $("#filterFormId").submit(function (event) {

            if ($('#reportTableDiv').length > 0) {
                $('#reportTableDiv').remove();
            }

            event.preventDefault();
            $('#spinnerId').show('slow');
            $("#reportingDiv").load('./dailyCollectionComponentWiseTablePart' +
                '?' + $("#filterFormId").serialize(),
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

    $('#productCategories').change(function (e) {

        var prodCatId = $(this).val();

        if (prodCatId == '') {
            return false;
        }

        $.ajax({
            type: "POST",
            url: "./dcGetData",
            data: {
                context: 'product',
                prodCatId: prodCatId
            },
            dataType: "json",
            success: function (response) {

                $('#product')
                        .find('option')
                        .remove()
                        .end()
                        .append(response['products']);
            },
            error: function () {
                alert('error!');
            }
        });
    });

</script>
@endsection
