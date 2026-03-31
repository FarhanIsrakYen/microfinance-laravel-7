@extends('Layouts.erp_master')
@section('content')

<!-- Search Options -->
<div class="row align-items-center pb-10 mb-4">

    @if (count($branchces) > 1)
    <div class="col-lg-2">
        <label class="input-title">Branch</label>
        <div class="input-group">
            <select class="form-control clsSelect2" name="name" id="filBranch"
            >
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
                <option value="">Select</option>
                @foreach ($samities as $samity)
                <option value="{{ $samity->id }}">{{ $samity->name }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-lg-2">
        <label class="input-title">Product</label>
        <div class="input-group">
            <select class="form-control clsSelect2" name="filProduct" id="filProduct">
                <option value="">All</option>
                @foreach ($products as $product)
                <option value="{{ $product->id }}">{{ $product->name }}</option>
                @endforeach
            </select>
        </div>
    </div>
    <div class="col-lg-2">
        <label class="input-title">Member Status</label>
        <div class="input-group">
            <select class="form-control clsSelect2" name="filMemberStatus" id="filMemberStatus">
                <option value="">All</option>
                <option value="1">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>
    </div>
    <div class="col-lg-2">
        <label class="input-title">Date To</label>
        <div class="input-group">
            <input type="text" class="form-control datepicker-custom" id="startDate" name="startDate"
                placeholder="DD-MM-YYYY" value="" autocomplete="off">
        </div>
    </div>
    <div class="col-lg-2">
        <label class="input-title">Member Code</label>
        <div class="input-group">
            <input type="text" class="form-control" id="memberCode" name="memberCode" placeholder="Member Code"
                value="" autocomplete="off">
        </div>
    </div>
</div>
<div class="row align-items-center text-right p-10">
    <div class="col-lg-12">
        <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round" id="searchButton">Search</a>
    </div>
    
</div>

<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped clsDataTable" id="Table">
        <thead>
            <tr>
                <th style="width: 3%;" class="text-center">SL</th>
                <th class="text-center">Member Code	</th>
                <th class="text-center">Member Name</th>
                <th class="text-center">Savings Code</th>
                <th class="text-center">Deposit</th>
                <th class="text-center">Interest Amount</th>
                <th class="text-center"> Withdraw </th>
                <th class="text-center"> Balance</th>
                <th style="width: 10%;" class="text-center">Action</th>
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

<script>

    // $notification2
$(document).ready(function () {
    
    $('.page-header-actions').hide();
    });
    function fngetData(branch_id = null, samity_id = null, product_id = null, memberCode = null, startDate = null ,memberStatus = null)
     {

        if (branch_id == '') {
            alert('Select Branch');
            return false;
        }
        // if (samity_id == '') {
        //     alert('Select Samity');
        //     return false;
        // }

        $('#Table tbody').html('');
    
        $.ajax({
                method: "GET",
                url: "{{route('SavingsStatusDetails')}}",
                dataType: "text",
                data: {

                    filBranch: branch_id,
                    filSamity: samity_id,
                    filProduct: product_id,
                    memberCode: memberCode,
                    dateTo: startDate,
                    memberStatus : memberStatus,
                },
                success: function(data) {
                    if (data) {
                        $('#Table tbody').html(data);
                // console.log(data)
                        
                    }
                }
            });


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
                    var product_id = $('#filProduct').val();
                    var memberCode = $('#memberCode').val();
                    var startDate = $('#startDate').val();
                    var memberStatus = $('#filMemberStatus').val();
                    

                    fngetData(branch_id, samity_id, product_id, memberCode, startDate , memberStatus);
                });
            
          $("#filBranch").change(function (e) {
            e.preventDefault();

            $('#filSamity option:gt(0)').remove();

            if($(this).val() == ''){
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./../getSamities",
                data: {branchId : $("#filBranch").val()},
                dataType: "json",
                success: function (samities) {
                    $.each(samities, function (index, samity) {
                        $('#filSamity').append("<option value="+samity.id+">"+samity.name+"</option>");
                    });
                },
                error: function(){
                    alert('error!');
                }
            });
        });
            }); /* end ready */


   

</script>

@endsection
