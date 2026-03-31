@extends('Layouts.erp_master_full_width')
@section('content')
<div class="panel">
    <div class="panel-body" >
        <form enctype="multipart/form-data" method="post" class="form-horizontal" 
            data-toggle="validator" novalidate="true" autocomplete="off" id="filterFormId">
            @csrf
        <!-- Search Options -->
            <div class="row align-items-center pb-10 mb-4 d-print-none">
                


                <div class="col-lg-2">
                    <label class="input-title">Branch</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="branch_id" id="branch_id"
                        onchange="fnAjaxSelectBoxForFO(
                            'field_officer',
                            this.value,
                            '',
                            '',
                            '{{base64_encode('id,emp_name')}}',
                            '{{url('/ajaxSelectBoxForFieldOfficer')}}'
                                    );fnAjaxSelectBox('samity_id',
                                        this.value,
                                        '{{ base64_encode('mfn_samity')}}',
                                        '{{base64_encode('branchId')}}',
                                        '{{base64_encode('id,name')}}',
                                        '{{url('/ajaxSelectBox')}}',
                                        '{{null}}',
                                        '{{'isActiveOff'}}'
                                        );">


>
                            <option value="">Select All</option>
                            @foreach ($branchData as $row)
                            <option value="{{ $row->id }}">
                                {{ sprintf("%04d", $row->branch_code) . "-" . $row->branch_name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-lg-2">
                    <label class="input-title">Samity Name</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="samity" id="samity_id">
                            <option value="">Select Day</option>
                            
                        </select>
                    </div>
                </div>
                

                <div class="col-lg-2">
                    <label class="input-title">Savings Product</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="product_id" id="product_id">
                            <option value="">All</option>
                            @foreach ($ProductData as $row)
                            <option value="{{ $row->id }}">{{  $row->shortName }}</option>
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
                         value="{{\Carbon\Carbon::parse($sysDate)->format('d-m-Y')}}">
                    </div>
                </div>
                <div class="col-lg pt-20 text-right">
                    {{-- <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                        id="searchButton">Search</a> --}}
                        <button class="btn btn-primary btn-round" id="search_id">Search</button>
                </div>

            </div>
          

            
            </div>

        </form>

        <div class="row">
            <div class="col-md-12"  id="reportingDiv" style="padding: 3%;">
                
            </div>
        </div>

      
    </div>
</div>


<script>



$(document).ready(function() {
    $('.page-header-actions').hide();
   $("#filterFormId").submit(function( event ) {
    event.preventDefault();
    if( $("#branch_id").val() !=''){

        $("#reportingDiv").load('{{URL::to("mfn/reports/savings_interest_info/loadData")}}'+'?'+$("#filterFormId").serialize());
    }else{

        swal({
                            icon: 'warning',
                            title: 'Select Search Field',
                            text: 'You have have to select Branch.',
                        });

    }
    // $("#loadingModal").show();
   
    });


}); 

function fnAjaxSelectBoxForFO(FeildID = null, FeildVal = null, TableName = null, WhereColumn = null, SelectColumn = null, URL = null, SelectedVal = null) {

if (FeildID != null && FeildVal != null && TableName != null && WhereColumn != null && SelectColumn != null) {
    $.ajax({
        method: "GET",
        url: URL,
        dataType: "text",
        data: {FeildVal: FeildVal, TableName: TableName, WhereColumn: WhereColumn, SelectColumn: SelectColumn, SelectedVal: SelectedVal},
        success: function (data) {
            if (data) {
                $('#' + FeildID)
                        .empty()
                        .html(data);
                        // .trigger('change');
                        //                    .selectpicker('refresh');
            }
        }
    });
}
}

</script>

@endsection
