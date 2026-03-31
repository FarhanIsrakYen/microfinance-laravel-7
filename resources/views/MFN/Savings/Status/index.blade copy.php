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
                <option value="">All</option>
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

    <div class="col-lg-2 pt-20 text-center">
        <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round" id="searchButton">Search</a>
    </div>
</div>

<div class="table-responsive">
    <table class="table w-full table-hover table-bordered table-striped clsDataTable">
        <thead>
            <tr>
                <th style="width: 3%;">SL</th>
                <th>Member Code	</th>
                <th>Member Name</th>
                <th>Savings Code</th>
                <th>Deposit</th>
                <th>Interest Amount</th>
                <th> Refund</th>
                <th> Balance</th>
                <th style="width: 10%;" class="text-center">Action</th>
            </tr>
        </thead>
        <tbody>
          <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
          </tr>
        </tbody>
    </table>
</div>

<script>
    // function ajaxDataLoad(branch_id = null, samity_id = null, product_id = null, memberCode = null, startDate = null) {
    //     $('.clsDataTable').DataTable({
    //         destroy: true,
    //         processing: true,
    //         serverSide: true,
    //         "ajax": {
    //             "url": "{{ url()->current() }}",
    //             "dataType": "json",
    //             "type": "post",
    //             "data": {
    //                 filBranch: branch_id,
    //                 filSamity: samity_id,
    //                 filProduct: product_id,
    //                 memberCode: memberCode,
    //                 dateTo: startDate,
    //
    //             }
    //         },
    //         "columns": [
    //             {data: 'sl', name: 'sl', orderable: false, targets: 1, className: 'text-center'},
    //             { "data": "memberCode" },
    //             { "data": "memberName" },
    //             { "data": "savingsCode" },
    //             { "data": "amount" },
    //             { "data": "interestAmount" },
    //             { "savingsWithdrawStatus":"refund" },
    //             { "data": "balance" },
    //             { "data": "id", name: 'action', orderable: false, "width": "10%" },
    //         ],
    //         "columnDefs": [{
    //             "targets": 8,
    //             "createdCell": function (td, cellData, rowData, row, col) {
    //                 $(td).addClass("text-center d-print-none");
    //                 $(td).closest('tr').attr("cellData", cellData);
    //                 $(td).html('<a href=' + "{{ url()->current() }}" + '/view/' + cellData +
    //                     ' title="View" class="btnView"><i class="icon wb-eye mr-2 blue-grey-600"></i></a> <a href=' +
    //                     "{{ url()->current() }}" + '/edit/' + cellData +
    //                     ' title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a> <a href="javascript:void(0)" onclick="fnDelete(' +
    //                     '\'' + cellData + '\'' +
    //                     ');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>'
    //                     );
    //             }
    //         }]
    //     });
    // }

    $(document).ready(function () {

        $('#startDate').datepicker({
            dateFormat: 'dd-mm-yy',
            orientation: 'bottom',
            autoclose: true,
            todayHighlight: true,
            changeMonth: true,
            changeYear: true,
            yearRange: '1900:+10',
            // onClose: function (selectedDate) {
            //     $("#end_date").datepicker("option", "minDate", selectedDate);
            // }
        });


        ajaxDataLoad();

        $('#searchButton').click(function () {

            var branch_id = $('#filBranch').val();
            var samity_id = $('#filSamity').val();
            var product_id = $('#filProduct').val();
            var memberCode = $('#memberCode').val();
            var startDate = $('#startDate').val();
            // var end_date = $('#end_date').val();

            ajaxDataLoad(branch_id, samity_id, product_id, memberCode, startDate);
        });

        $("#filBranch").change(function (e) {
            e.preventDefault();

            $('#filSamity option:gt(0)').remove();

            if($(this).val() == ''){
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./getSamities",
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

        $("#startDate").on('click', function () {
            this.value = '';
        });
    }); /* end ready */


    function fnDelete(rowID) {

        swal({
                title: "Are you sure to delete data?",
                text: "Once Delete, this will be permanently delete!",
                icon: "warning",
                buttons: true,
                dangerMode: true,
            })
            .then((isConfirm) => {
                if (!isConfirm) {
                    return false;
                }
                var row = $('table tbody tr[cellData=' + rowID + ']');
                console.log(row);
                $.ajax({
                        url: "{{ url()->current() }}" + '/delete',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id: rowID
                        },
                    })
                    .done(function (response) {

                        var row = $('table tbody tr[cellData=' + rowID + ']');

                        if (response['alert-type'] == 'error') {
                            swal({
                                icon: 'error',
                                title: 'Oops...',
                                text: response['message'],
                            });
                        } else {
                            swal({
                                icon: 'success',
                                title: 'Success...',
                                text: response['message'],
                            });
                            row.remove();
                            $('.clsDataTable').DataTable().draw();
                        }

                    })
                    .fail(function () {
                        alert("error");
                    });
            });
    }

</script>

@endsection
