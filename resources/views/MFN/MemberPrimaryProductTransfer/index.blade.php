@extends('Layouts.erp_master')

@section('content')
<style>
    tr td:last-child {
        text-align: center
    }
</style>
    <!-- Page -->
    <div class="panel">
        <div class="panel-body">
            <form method="get" class="form-horizontal" id="filterFormId">
                <div class="row align-items-center pb-10 d-print-none">
                    @if(Auth::user()->branch_id == 1)
                        <div class="col-lg-2 mt-1">
                            <label class="input-title">Branch</label>
                            <div class="input-group">
                                <select class="form-control clsSelect2" name="branchId" id="branchId" required
                                    data-error="Please Select Branch">
                                    <option value="">Select</option>
                                    @foreach ($branchList as $branch)
                                    <option value="{{ $branch->id }}">{{ $branch->branch_code }} - {{ $branch->branch_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @else
                        <input type="hidden" name="branchId" id="branchId" value="{{ Auth::user()->branch_id }}">
                    @endif
    
                    <div class="col-lg-2 mt-1">
                        <label class="input-title">Samity</label>
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="samityId" id="samityId">
                                <option value="">All</option>
                            </select>
                        </div>
                    </div>
    
                    <div class="col-lg-2 mt-1">
                        <label class="input-title">Member Code</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id='memberCode' name='memberCode'>
                        </div>
                    </div>
    
                    <div class="col-lg-2 mt-1">
                        <label class="input-title">Product</label>
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="product" id="product">
                                <option value="">All</option>
                                
                            </select>
                        </div>
                    </div>
    
                    <div class="col-lg-2 mt-1">
                        <label class="input-title">Date</label>
                        <div class="input-group">
                            <input type="text" class="form-control datepicker-custom" id="date" name="date"
                placeholder="DD-MM-YYYY" value="" autocomplete="off">
                        </div>
                    </div>
    
                    <div class="col-lg-2 pt-20 text-center ml-auto">
                        {{-- <a href="javascript:void(0)" class="btn btn-primary btn-round" id="searchButton">Search</a> --}}
                        <button type="submit" class="btn btn-primary btn-round">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th style="width: 3%;">SL</th>
                    <th>Member Name</th>
                    <th>Member Code</th>
                    <th>Branch Name</th>
                    <th>Old Product Name</th>
                    <th>New Product Name</th>
                    <th>Transfer Date</th>
                    <th>Entry By</th>
                    <th style="width: 15%;" class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
    </div>

    <!-- End Page -->
    <script>

    $("#filterFormId").submit(function (event) {
        event.preventDefault();

        if($('#branchId').val() == ''){
            swal({
                icon: 'error',
                title: 'Oops...',
                text: 'You Must select a Branch'
            });
            return;
        }

        var branchId = $('#branchId').val();
        var samityId = $('#samityId').val();
        var memberCode = $('#memberCode').val();
        var product = $('#product').val();
        var date = $('#date').val();

        ajaxDataLoad(branchId, samityId, memberCode, product, date);
        
        
    });
   

        function ajaxDataLoad(branchID=null, samityId= null, memeberCode=null, product=null, date=null) {
            $('.clsDataTable').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,
                "ajax": {
                    "url": "{{ url()->current() }}",
                    "dataType": "json",
                    "type": "post",
                    "data": {
                        branchID: branchID,
                        samityId: samityId,
                        memeberCode: memeberCode,
                        date: date,
                        product: product,
                    }
                },
                "columns": [{
                        data: 'sl',
                        name: 'sl',
                        orderable: false,
                        targets: 1,
                        className: 'text-center'
                    },
                    {
                        "data": "memberName"
                    },
                    {
                        "data": "memberCode"
                    },
                    {
                        "data": "branchName"
                    },
                    {
                        "data": "oldProduct"
                    },
                    {
                        "data": "newProduct"
                    },
                    {
                        "data": "transferDate"
                    },
                    {
                        "data": "entryBy"
                    },
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
                // "columnDefs": [{
                //     "targets": -1,
                //     "createdCell": function(td, cellData, rowData, row, col) {
                //         $(td).addClass("text-center d-print-none");
                //         $(td).closest('tr').attr("cellData", cellData);
                //         $(td).html('<a href=' + "{{ url()->current() }}" + '/view/' + cellData +
                //             ' title="View" class="btnView"><i class="icon wb-eye mr-2 blue-grey-600"></i></a> <a href=' +
                //             "{{ url()->current() }}" + '/edit/' + cellData +
                //             ' title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a> <a href="javascript:void(0)" onclick="fnDelete(\'' +
                //             cellData + '\'' +
                //             ');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>'
                //         );
                //     }
                // }]
            });
        }

        $(document).ready(function() {
            ajaxDataLoad();
        });

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
                    // var row = $('table tbody tr[cellData=' + rowID + ']');
                    var row = $('table tbody tr[cellData=' + '\'' + rowID + '\'' + ']');

                    console.log(row);
                    $.ajax({
                            url: "{{ url()->current() }}" + '/delete',
                            type: 'POST',
                            dataType: 'json',
                            data: {
                                id: rowID
                            },
                        })
                        .done(function(response) {

                            // var row = $('table tbody tr[cellData=' + rowID + ']');
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
                        .fail(function() {
                            alert("error");
                        });
                });
        }


    $('#branchId').change(function(e){
        populateDropDown();
    });
    
    @if(Auth::user()->branch_id != 1)
        populateDropDown();
    @endif

    function populateDropDown(){
        var branchId = $('#branchId').val();
        
        if (branchId == '') {
            alert('branch must be selected');
            return false;
        }

        $.ajax({
            type: "POST",
            url: "{{ url()->current() }}/getData",
            data: {
                branchId: branchId,
                context: 'DropDownPopulateForFiltering',
            },
            dataType: "json",
            success: function (response) {
                
                $(`#product option:gt(0)`).remove();
                $.each(response.products, function (index, value) { 
                    $('#product').append(`<option value='${value.id}'>${value.productCode} - ${value.name}</option>`);
                });

                $(`#samityId option:gt(0)`).remove();
                $.each(response.samities, function (index, value) { 
                    $('#samityId').append(`<option value='${value.id}'>${value.samityCode} - ${value.name}</option>`);
                });
            },
            error: function () {
                alert('error!');
            }
        });
    }
    </script>

@endsection
