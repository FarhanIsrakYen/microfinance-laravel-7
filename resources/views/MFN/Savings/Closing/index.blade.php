@extends('Layouts.erp_master')
@section('content')
<style>
    tr td:last-child {
        text-align: center
    }
</style>

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
        <label class="input-title">Member Code</label>
        <div class="input-group">
            <input type="text" class="form-control" id="fillMemberCode" name="fillMemberCode"
                placeholder="Member Code">
        </div>
    </div>

    <div class="col-lg-2">
        <label class="input-title">Date From</label>
        <div class="input-group">
            <input type="text" class="form-control datepicker-custom" id="fillDateFrom" name="fillDateFrom"
                placeholder="DD-MM-YYYY" value="" autocomplete="off">
        </div>
    </div>

    <div class="col-lg-2">
        <label class="input-title">Date To</label>
        <div class="input-group">
            <input type="text" class="form-control datepicker-custom" id="fillDateTo" name="fillDateTo"
                placeholder="DD-MM-YYYY" value="" autocomplete="off">
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
                <th class="text-center">Member Name</th>
                <th class="text-center">Member Code	</th>
                <th class="text-center">Savings Code</th>
                <th class="text-center">Closing Date</th>
                <th class="text-center">Closing Balance</th>
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
    function ajaxDataLoad(branch_id = null, samity_id = null, fillMemberCode = null, fillDateFrom = null, fillDateTo = null){
       $('.clsDataTable').DataTable({
        destroy: true,
        // retrieve: true,
        processing: true,
        serverSide: true,
        order: [
            [0, "desc"]
        ],
        "ajax": {
            "url": "{{route('closingsDatatable')}}",
            "dataType": "json",
            "type": "post",
            "data": {
                _token: "{{csrf_token()}}",
                filBranch: branch_id,
                filSamity: samity_id,
                fillMemberCode: fillMemberCode,
                fillDateFrom: fillDateFrom,
                fillDateTo: fillDateTo,
            }
        },
        columns: [

            {
                data: 'slNo',
                className: 'text-center'
            },
            {
                data: 'member_name'
            },
            {
                data: 'member_code',
                className: 'text-center'
            },
            {
                data: 'savings_code',
                className: 'text-center'
            },
            {
                data: 'date',
                className: 'text-right'
            },
            {
                data: 'balance'
            },
            // {
            //     data: 'action',
            //     name: 'action',
            //     orderable: false,
            //     className: 'text-center'
            // },
            {
                    "data": "id",
                    name: 'action',
                    orderable: false,
                    "width": "10%"
                },

        ],
        'fnRowCallback': function(nRow, aData, Index) {
                var actionHTML = jsRoleWisePermission(aData.action.set_status, aData.action.action_name, aData.action.action_link);
                $('td:last', nRow).html(actionHTML);
            },
        // 'fnRowCallback': function(nRow, aData, Index) {
        //     $('.btnDelete', nRow).removeClass('btnDelete');

        //             var actionHTML = '';

        //             if(aData.action !== 0){
        //                 actionHTML = '<a href=' + "{{ url()->current() }}" + '/view/' +aData.action+
        //                 ' title="View" class="btnView"><i class="icon wb-eye mr-2 blue-grey-600"></i></a><i class="icon wb-trash mr-2 blue-grey-400"></i>';
        //             }
        //             else{
        //                 actionHTML = '<a href=' + "{{ url()->current() }}" + '/view/' +aData.action+
        //                 ' title="View" class="btnView"><i class="icon wb-eye mr-2 blue-grey-600"></i></a><a href="javascript:void(0)" onclick="fnDelete(\'' +aData.action+ '\'' +
        //                 ');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>';
        //             }

        //             $('td:last', nRow).html(actionHTML);
        // },
        "columnDefs": [
            // {
            //     "targets": -1,
            //     "createdCell": function (td, cellData, rowData, row, col) {
            //         $(td).addClass("text-center d-print-none");
            //         $(td).closest('tr').attr("cellData", cellData);
                    
            //         if (rowData['isAuthorized'] == 1 || rowData['transactionTypeId'] > 2) {
            //             $(td).html('<a href=' + "{{ url()->current() }}" + '/view/' + cellData +
            //             ' title="View" class="btnView"><i class="icon wb-eye mr-2 blue-grey-600"></i></a> <i class="icon wb-edit mr-2 blue-grey-600"></i> <i class="icon wb-trash mr-2 blue-grey-600"></i>'
            //             );
            //         }else{
            //             $(td).html('<a href=' + "{{ url()->current() }}" + '/view/' + cellData +
            //             ' title="View" class="btnView"><i class="icon wb-eye mr-2 blue-grey-600"></i></a>' +
            //             '<a href="javascript:void(0)" onclick="fnDelete(' +
            //             '\'' + cellData + '\'' +
            //             ');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>'
            //             );
            //         }
                    
            //     }
               
            // }
        ]

    });
    }

    $(document).ready( function () {

       

        ajaxDataLoad();

       $('#searchButton').click(function () {

            var branch_id = $('#filBranch').val();
            var samity_id = $('#filSamity').val();
            var fillMemberCode = $('#fillMemberCode').val();
            var fillDateFrom = $('#fillDateFrom').val();
            var fillDateTo = $('#fillDateTo').val();

            ajaxDataLoad(branch_id, samity_id, fillMemberCode, fillDateFrom, fillDateTo);
        });
    });
    function fnDelete(rowID) {
    /**
     * para1 = link to delete without id
     * para 2 = ajax check link same for all
     * para 3 = id of deleting item
     * para 4 = matching column
     * para 5 = table 1
     * para 6 = table 2
     * para 7 = table 3
     */
    // fnDeleteCheck(
    //     "{{url('mfn/savings/closing/delete/')}}",
    //     "{{url('/ajaxDeleteCheck')}}",
    //     RowID,

    // );
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
                var row = $('table tbody tr[cellData=' + '\'' + rowID + '\'' + ']');
                
                $.ajax({
                        url: "{{ url()->current() }}" + '/delete',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            id: rowID
                        },
                    })
                    .done(function (response) {

                        var row = $('table tbody tr[cellData=' + '\'' + rowID + '\'' + ']');

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
