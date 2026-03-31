@extends('Layouts.erp_master_full_width')
@section('content')
<div class="panel">
    <div class="panel-body" >
        <form enctype="multipart/form-data" method="post" class="form-horizontal" 
            data-toggle="validator" novalidate="true" autocomplete="off" id="filterFormId">
            @csrf
        <!-- Search Options -->
            <div class="row align-items-center pb-10 mb-4 d-print-none">
                


                @if (count($branchData) > 1)
                <div class="col-lg-2">
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
                
                <div class="col-lg-2">
                    <label class="input-title">Samity</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="samity" id="samity_id">
                            <option value="">All</option>
                            @foreach ($samityData as $samity)
                            <option value="{{ $samity->id }}">{{ $samity->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
               
                <div class="col-lg-2">
                    
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
                            <option value="">All</option>
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
                            <option value="">All</option>
                            
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Savings Product</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="savingsId" id="savings_id">
                            <option value="">All</option>
                            @foreach ($savingsProductData as $savingsProduct)
                                <option value="{{ $savingsProduct->id }}">{{ $savingsProduct->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
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
                <div class="col-lg-2">
                    <label class="input-title">Date To</label>
                    
                    <div class="input-group ghdatepicker">
                        <div class="input-group-prepend ">
                            <span class="input-group-text ">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        <input type="text" class="form-control round datepicker-custom" id="date_to" name="date_to"
                         value="{{\Carbon\Carbon::parse($sysDate)->format('d-m-Y')}}" >
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
    </div>
</div>
<script>



$(document).ready(function() {
    $('.page-header-actions').hide();
   $("#filterFormId").submit(function( event ) {
    event.preventDefault();
    if (compareDates(date,date_to) <= 30) {
        if( ($("#branch_id").val() !='') ){

            $("#reportingDiv").load('{{URL::to("mfn/reports/savings_refund_register/loadData")}}'+'?'+$("#filterFormId").serialize());
        }else{

            swal({
                            icon: 'warning',
                            title: 'Select Search Field',
                            text: 'You have to select Branch.',
                        });
        }
    } else {
        swal({
                            icon: 'warning',
                            title: 'Days crosses limit',
                            text: 'Days difference must be less then 31',
                        });
    }
   
    });


}); 

function compareDates(date,date_to) {
    var date = document.getElementById('date').value;
    var date_to = document.getElementById('date_to').value;
    date = date.split("-");
    date_to = date_to.split("-");
    date_from = new Date(date[1]+"-"+date[0]+"-"+date[2]);
    date_last = new Date(date_to[1]+"-"+date_to[0]+"-"+date_to[2]);
    var difference = date_last.getTime() - date_from.getTime();
    var result = Math.abs(difference) / (1000*3600*24);
    return result;
}
    
</script>

@endsection
