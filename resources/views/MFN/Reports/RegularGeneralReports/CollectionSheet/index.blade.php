@extends('Layouts.erp_master_full_width')
@section('content')

<div class="panel">
    <div class="panel-body">
        <form enctype="multipart/form-data" method="post" action="#" class="form-horizontal" data-toggle="validator"
            novalidate="true" autocomplete="off">
            @csrf
            <!-- Search Options -->
            <div class="row align-items-center pb-10 mb-4 d-print-none">

                @if (count($branches) > 1)
                <div class="col-lg-2">
                    <label class="input-title">Branch</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="branch" id="branch">
                            <option value="">Select Option</option>
                            @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->branchName }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @endif

                <div class="col-lg-2">
                    <label class="input-title">Year</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="year" id="year">
                            <option value="">Select Year</option>
                            @foreach ($years as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                            @endforeach

                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Month</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="month" id="month">
                            <option value="">Select Month</option>
                            @foreach ($months as $key => $month)
                            <option value="{{ $key }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Day</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="day" id="day">
                            <option value="">Select Day</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>

                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Samity</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="samity" id="samity">
                            <option value="">Select</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Product Category</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="product_cat" id="product_cat">
                            <option value="">All</option>
                            @foreach ($productCategories as $productCategory)
                            <option value="{{ $productCategory->id }}">{{ $productCategory->name }}</option>
                            @endforeach

                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Product</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="product" id="product">
                            <option value="">All</option>
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Report Option</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="report_option">
                            <option value="singlePart">Single Part</option>
                            <option value="twoPart">Two Part</option>

                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Member Code</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="memberCodeVisibility">
                            <option value="show">Show</option>
                            <option value="hide">Hide</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg pt-20 text-right">
                    <button type="submit" class="btn btn-primary btn-round">Show</button>
                </div>

            </div>

        </form>

        <div class="row">
            <div class="col-md-12" id="reportingDiv" >

            </div>
        </div>
    </div>
</div>
<style  type="text/css" media="print">
/* @media print{
     @page{
         height : 350mm;
        margin: 0px; 
        padding:0px;
        border: 1px solid black !important;
    }

    table {
        border-spacing: 0px !important; */
      /* font-size: 10px; 
      color: #000;
      vertical-align: left;
      border: 1px solid #000!important; */
      /* } */
   
    /* @media screen {
  input {
    width: 100%;
    margin: 1em 0;
  }
} */
 /*@media print {
  input {
    display: none;
  }
} */
/* @page { 
    size: landscape ;
    }
    body { 
    page-break-before: avoid;
    margin: 0;
    zoom : 100% ;
    } */
/* body {
    page-break-before: avoid;
    -webkit-transform: rotate(-90deg) scale(.75,.35); 
    -moz-transform:rotate(-90deg) scale(.68,.27);
    zoom : 100% ;
      }
 
    
} */

</style>

<script>
    $(document).ready(function () {
        $('form').submit(function (event) {
            event.preventDefault();

            $("#reportingDiv").empty();

            var errorMsg = '';

            if ($('#year').val() == '') {
                errorMsg = 'Year';
            }

            if ($('#month').val() == '') {
                if (errorMsg != '') {
                    errorMsg = errorMsg + ', Month';
                } else {
                    errorMsg = 'Month';
                }
            }

            if ($('#samity').val() == '') {
                if (errorMsg != '') {
                    errorMsg = errorMsg + ', Samity';
                } else {
                    errorMsg = 'Samity';
                }
            }

            if (errorMsg != '') {
                swal({
                    icon: 'error',
                    title: 'Oops...',
                    text: errorMsg + ' could not be empty.',
                });

                return false;
            }

            $("#reportingDiv").load('{{URL::to("mfn/reports/collection_sheet/loadRreport")}}' + '?' + $(
                'form').serialize());
        });

        /* get samity*/
        $('#branch,#year,#month,#day').change(function (e) {
            e.preventDefault();
            $('#samity option:gt(0)').remove();

            var branchId = '';
            if ("{{ $preSelectedBranchId }}" != '') {
                branchId = "{{ $preSelectedBranchId }}";
            } else {
                branchId = $('#branch').val();
            }

            var year = $('#year').val();
            var month = $('#month').val();
            var day = $('#day').val();

            if (branchId == '' || year == '' || month == '' || day == '') {
                return false;
            }

            $.ajax({
                type: "POST",
                url: "{{ Request::url() }}" + "/getData",
                data: {
                    context: 'samity',
                    branchId: branchId,
                    year: year,
                    month: month,
                    day: day,
                },
                dataType: "json",
                success: function (samities) {
                    $.each(samities, function (index, obj) {
                        $('#samity').append("<option value=" + obj.id + ">" + obj
                            .samityName + "</option>");
                    });
                },
                error: function () {
                    alert('error!');
                }
            });
        });
        /* end getting samity*/

        /* get product */
        $('#product_cat').change(function (e) {
            e.preventDefault();
            $('#product option:gt(0)').remove();

            if ($(this).val() == '') {
                return false;
            }

            $.ajax({
                type: "POST",
                url: "{{ Request::url() }}" + "/getData",
                data: {
                    context: 'product_cat',
                    productCategoryId: $('#product_cat').val(),
                },
                dataType: "json",
                success: function (products) {
                    $.each(products, function (index, obj) {
                        $('#product').append("<option value=" + obj.id + ">" + obj
                            .name + "</option>");
                    });
                },
                error: function () {
                    alert('error!');
                }
            });
        });
        /* end getting product */

    });

</script>


@endsection