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
                    <label class="input-title">Year</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="year" id="year" required>
                            <option value="">Select Year</option>
                            @for($i = $currentYear; $i >= $year; $i--)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Month</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="month" id="month" required>
                            <option value="">Select Month</option>
                            @foreach ($months as $key => $month)
                            <option value="{{ $key + 1 }}">{{ $month }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Service Charge</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="isWithServiceChanrge" id="isWithServiceChanrge" required>
                            <option value="Yes">With Service Charge</option>
                            <option value="No">Without Service Charge</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Is Round Up?</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="isRoundUp" id="isRoundUp">
                            <option value="Yes">Yes</option>
                            <option value="No">No</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Loan Option</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="loanOption" id="loanOption">
                            <option value="LoanProd">Loan Product</option>
                            <option value="LoanProdCat">Loan Product Category</option>
                        </select>
                    </div>
                </div>

                <div class="col-lg-2">
                    <label class="input-title">Funding Org.</label>
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="fundingOrg" id="fundingOrg">
                            <option value="">All</option>
                            @foreach ($fundingOrg as $row)
                                <option value="{{ $row->id }}">{{ $row->fundingOrg }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="col-lg-12 pt-20 text-center">
                <button type="submit" class="btn btn-primary btn-round">Search</button>
            </div>
        </form>
    </div>
</div>

<div  id="reportingDiv">

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

            if ($('#loadReport').length > 0) {
                $('#loadReport').remove();
            }
            
            $('#spinnerId').show('slow');
            $("#reportingDiv").load('{{ url()->current() }}' + '/viewPart' +
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

</script>
@endsection
