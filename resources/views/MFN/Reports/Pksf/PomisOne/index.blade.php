@extends('Layouts.erp_master_full_width')
@section('content')
<?php 
use App\Services\CommonService;

$branchID = CommonService::getBranchId();
?>
<div class="panel">
    <div class="panel-body" >
        <form enctype="multipart/form-data" method="post" class="form-horizontal" 
            data-toggle="validator" novalidate="true" autocomplete="off" id="filterFormId">
            @csrf
        <!-- Search Options -->
            <div class="row align-items-center pb-10 mb-4 d-print-none">
                
                @if (count($branchList) > 1)
                <div class="col-lg-2">
                    <label class="input-title">Branch</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="branch" id="branch_id">
                            <option value="">Select Option</option>
                            @foreach ($branchList as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                @else
                <input type="hidden" name="branch" id="branch_id" value="{{$branchID}}">
                @endif 
                
                <div class="col-lg-2">
                    <label class="input-title">Year</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="year" id="year_id">
                            <option value="">Select Year</option>
                            <option value="2018">2018</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Month</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="month" id="month_id">
                            <option value="">Select Month</option>
                            <option value="January">January</option>
                            <option value="February">February</option>
                            <option value="March">March</option>
                            <option value="April">April</option>
                            <option value="May">May</option>
                            <option value="June">June</option>
                            <option value="July">July</option>
                            <option value="August">August</option>
                            <option value="September">September</option>
                            <option value="October">October</option>
                            <option value="November">November</option>
                            <option value="December">December</option>
                           
                        </select>
                    </div>
                </div>

              
                {{-- <div class="col-lg-2">
                    <label class="input-title">Product Category</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="product_cat" id="product_cat_id"
                        onchange="fnAjaxSelectBox('product_id',
                                            this.value,
                                '{{ base64_encode('mfn_loan_products')}}',
                                '{{base64_encode('productCategoryId')}}',
                                '{{base64_encode('id,name')}}',
                                '{{url('/ajaxSelectBox')}}',
                                '{{null}}',
                                '{{'isActiveOff'}}'
                                        );">
                            <option value="">All</option>
                            @foreach ($LoanProductCat as $Row)
                                <option value="{{ $Row->id }}">{{ $Row->name }}</option>
                            @endforeach
                            
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Product</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="product" id="product_id">
                            <option value="">Select Product</option>
                            
                        </select>
                    </div>
                </div>
               --}}
            
                {{-- <div class="col-lg-2">
                    <label class="input-title">Collection Sheet Options</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" >
                            <option value="">Select </option>
                            
                        </select>
                    </div>
                </div> --}}

                <div class="col-lg pt-20 text-right">
                    {{-- <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                        id="searchButton">Search</a> --}}
                        <button class="btn btn-primary btn-round" id="search_id">Search</button>
                </div>

            
            </div>

        </form>

        <div class="row">
            <div class="col-md-12"  id="reportingDiv">
                
            </div>
        </div>
    </div>
</div>
<script>



$(document).ready(function() {
    $('.page-header-actions').hide();
   $("#filterFormId").submit(function( event ) {
    event.preventDefault();
    // $("#loadingModal").show();
    if( $("#branch_id").val() !='' && $("#year_id").val() !='' && $("#month_id").val() !='' ){

        $("#reportingDiv").load('{{URL::to("mfn/reports/pksf/pomis1/loadData")}}'+'?'+$("#filterFormId").serialize());
    }else{

        swal({
                            icon: 'warning',
                            title: 'Select Search Field',
                            text: 'You have have to select Branch , Year and Month.',
                        });

    }



   
});

// $("#loadingModal").hide();


}); 
$('#branch_id').on('change', function () {
    $('#samity_id').val('');
    $('#samity_id').trigger('change');
    $('#product_cat_id').val('');
    $('#product_cat_id').trigger('change');
    $('#product_id').val('');
    $('#product_id').trigger('change');

    var BranchID = $('#branch_id').val();
    

         if ($(this).val() != '') {
              $.ajax({
                method: "GET",
                url: "{{route('mfnReportgSamitygetYears')}}",
                dataType: "json",
                data: {
                    BranchID: BranchID,
                   
                },
                success: function(data) {
                    $('#year_id').html(data);
                    
                    // console.log(data);
                }
            });
        }

});
$('#year_id').on('change', function () {
    $('#samity_id').val('');
    $('#samity_id').trigger('change');
    $('#product_cat_id').val('');
    $('#product_cat_id').trigger('change');
    $('#product_id').val('');
    $('#product_id').trigger('change');
    $('#month_id').val('');
    $('#month_id').trigger('change');
    $('#day_id').val('');
    $('#day_id').trigger('change');

});
$('#month_id').on('change', function () {
    $('#samity_id').val('');
    $('#samity_id').trigger('change');
    $('#product_cat_id').val('');
    $('#product_cat_id').trigger('change');
    $('#product_id').val('');
    $('#product_id').trigger('change');
    $('#day_id').val('');
    $('#day_id').trigger('change');
});


$('#day_id').on('change', function () {

    var BranchID = $('#branch_id').val();
    var Day = $('#day_id').val();
    var Year = $('#year_id').val();
    var Month = $('#month_id').val();

         if ($(this).val() != '' && BranchID != '' && Year != '' && Month != '') {
              $.ajax({
                method: "GET",
                url: "{{route('mfnReportgSamity')}}",
                dataType: "json",
                data: {
                    BranchID: BranchID,
                    Year : Year,
                    Month : Month,
                    Day : Day 
                },
                success: function(data) {
                    $('#samity_id').html(data);
                    
                    // console.log(data);
                }
            });
        }
       
});


// *** (year only) ***
// $(function() { 
//    $('#year_id').datepicker( {
//         changeMonth: false,
//         changeYear: true,
//         showButtonPanel: false,
//         dateFormat: 'yy',
//         onClose: function(dateText, inst) { 
//             var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
//             $(this).val($.datepicker.formatDate('yy', new Date(year, 1, 1)));
//             //   $(this).datepicker('setDate', new Date('2017'));
//         }
//     }).focus(function () {
//                 $(".ui-datepicker-month").hide();
//                 $(".ui-datepicker-prev").hide();
//                 $(".ui-datepicker-next").hide();
//                 $(".ui-datepicker-calendar").hide();
//             });
// });

// $(function() { 
//    $('#month_id').datepicker( {
//         changeMonth: true,
//         changeYear: false,
//         showButtonPanel: false,
//         dateFormat: 'mm',
//         onClose: function(dateText, inst) { 
//             var month = $("#ui-datepicker-div .ui-datepicker-month :selected").val();
//             // var year = $("#ui-datepicker-div .ui-datepicker-year :selected").val();
//             $(this).val($.datepicker.formatDate('MM', new Date('2020', month, 1)));
//             //   $(this).datepicker('setDate', new Date('2017'));
//         }
//     }).focus(function () {
//                 // $(".ui-datepicker-month").hide();
//                 $(".ui-datepicker-year").hide();
//                 $(".ui-datepicker-calendar").hide();


//             });
// });


</script>

@endsection
