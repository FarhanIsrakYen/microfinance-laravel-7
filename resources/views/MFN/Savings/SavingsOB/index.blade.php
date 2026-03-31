@extends('Layouts.erp_master')
@section('content')

<!-- Search Options -->
<form method="POST" data-toggle="validator" novalidate="true" id="issue_form">
    @csrf




    <div class="row align-items-center pb-10 mb-4">

        @if (count($branchces) > 1)
        <div class="col-lg-2">
            <label class="input-title">Branch</label>
            <div class="input-group">
                <select class="form-control clsSelect2" name="name" id="filBranch">
                    <option value="">Select</option>
                    @foreach ($branchces as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        @endif

        <div class="col-lg-2">
            <label class="input-title">Samity</label>
            <div class="input-group">
                <select class="form-control clsSelect2" name="name" id="filSamity">
                    <option value="">All</option>
                    @foreach ($samities as $samity)
                    <option value="{{ $samity->id }}">{{ $samity->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-lg-8 text-right">
            <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round"
                id="searchButton">Search</a>
        </div>


    </div>


    <div class="table-responsive">
        <table class="table w-full table-hover table-bordered table-striped clsDataTable" id="Table">
            <thead>
                <tr>
                    <th style="width: 3%;" class="text-center">SL</th>
                    <th class="text-center">Member Code </th>
                    <th class="text-center">Member Name</th>
                    <th class="text-center">Savings Code</th>
                    <th class="text-center">Deposit</th>
                    <th class="text-center">Interest Amount</th>
                    <th class="text-center"> Withdraw </th>
                    <th class="text-center"> Balance</th>
                    {{-- <th style="width: 10%;" class="text-center">Action</th> --}}
                </tr>
            </thead>
            <tbody>
                {{-- <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr> --}}
            </tbody>
        </table>
    </div>
    <div class="form-group d-flex justify-content-center">
        <div class="example example-buttons">
            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round d-print-none">Back</a>
            <button type="submit" class="btn btn-primary btn-round" id="submitButton">Save</button>
            <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
        </div>
    </div>

</form>
<script>
    // $notification2
    $(document).ready(function () {

        $('.page-header-actions').hide();

        /* get samity list on selecting branch */
        $("#filBranch").change(function (e) { 
            e.preventDefault();
            $("#filSamity option:gt(0)").remove();

            if($(this).val() == ''){
                return false;
            }

            $.ajax({
                type: "post",
                url: "./getSamities",
                data: {branchId : $("#filBranch").val()},
                dataType: "json",
                success: function (samities) {
                    $.each(samities, function (index, samity) {
                         $("#filSamity").append("<option value="+ samity.id +" >" + samity.name  +"</option>");
                    });
                },
                error: function (response) {
                   alert('Error');
                }
            });
            
        });
        /* end gettting samity list on selecting branch */
    });


    function fngetData(branch_id = null, samity_id = null) {

        $.ajax({
            method: "GET",
            url: "{{route('SavingsOBDetails')}}",
            dataType: "text",
            data: {

                filBranch: branch_id,
                filSamity: samity_id,

            },
            success: function (data) {
                if (data) {
                    $('#Table tbody').html(data);
                    // console.log(data)

                }
            }
        });


    }

    function fnCalculate(Row) {
        var totalAmt = 0;

        var amt = Number($('#diposite_amt_' + Row).val()) + Number($('#inerest_amt_' + Row).val());
        amt = amt - Number($('#withdraw_amt_' + Row).val());
        $('#balance_amt_' + Row).val(amt)
    }

    $(document).ready(function () {

        $('#startDate').datepicker({
            dateFormat: 'dd-mm-yy',
            orientation: 'bottom',
            autoclose: true,
            todayHighlight: true,
            changeMonth: true,
            changeYear: true,
            yearRange: '1900:+10',

        });


        $('#searchButton').click(function () {

            var branch_id = $('#filBranch').val();
            var samity_id = $('#filSamity').val();

            fngetData(branch_id, samity_id);
        });

    }); /* end ready */
</script>

@endsection