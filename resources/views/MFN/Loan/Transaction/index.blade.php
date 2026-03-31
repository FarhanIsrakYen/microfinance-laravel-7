@extends('Layouts.erp_master_full_width')
@section('content')
<style>
    tr td:last-child {
        text-align: center
    }
</style>

    {{-- view data --}}
    @include('MFN.Loan.Transaction.view_loan_transaction')

    <?php $collectionType = strstr(url()->current(), '/mfn/oneTimeLoanTransaction'); ?>

    <div class="row align-items-center pb-10 mb-4">

        @if (count($branchList) > 1)
            <div class="col-lg-2">
                <label class="input-title">Branch</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="filBranch" id="filBranch">
                        <option value="">All</option>
                        @foreach ($branchList as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->branch_code . ' - ' . $branch->branch_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @else
            <input type="hidden" name="filBranch" id="filBranch" value="{{ $branchList[0]->id }}">
        @endif

        @if ($samities)
            <div class="col-lg-2">
                <label class="input-title">Samity</label>
                <div class="input-group">
                    <select class="form-control clsSelect2" name="filSamity" id="filSamity">
                        <option value="">All</option>
                        @foreach ($samities as $samity)
                            <option value="{{ $samity->id }}">{{ $samity->name }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif

        <div class="col-lg-2">
            <label class="input-title">Product</label>
            <div class="input-group">
                <select class="form-control clsSelect2" name="fillProduct" id="fillProduct">
                    <option value="">All</option>
                    @foreach ($loanProducts as $loanProduct)
                        <option value="{{ $loanProduct->id }}">{{ $loanProduct->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>


        <div class="col-lg-2">
            <label class="input-title">Loan Code</label>
            <div class="input-group">
                <input type="text" class="form-control" id="fillLoanCode" name="fillLoanCode" placeholder="loan Code"
                    value="" autocomplete="off">
            </div>
        </div>

        <div class="col-lg-2">
            <label class="input-title">Start Date</label>
            <div class="input-group">
                <input type="text" class="form-control" id="start_date" name="start_date" placeholder="DD-MM-YYYY" value=""
                    autocomplete="off">
            </div>
        </div>

        <div class="col-lg-2">
            <label class="input-title">End Date</label>
            <div class="input-group">
                <input type="text" class="form-control" id="end_date" name="end_date" placeholder="DD-MM-YYYY" value=""
                    autocomplete="off">
            </div>
        </div>

        <div class="col-lg-2">
            <br>
            <a href="javascript:void(0)" name="searchButton" class="btn btn-primary btn-round float-right"
                id="searchButton">Search</a>
        </div>

    </div>

    <!-- Page -->
    <div class="table-responsive">
        <table class="table w-full table-hover table-bordered table-striped clsDataTable">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 3%;">SL</th>
                    <th rowspan="2">Member Name</th>
                    <th rowspan="2">Member Code</th>
                    <th rowspan="2">Loan Code</th>
                    <th rowspan="2">Date</th>
                    <th colspan="4">Transaction Amount</th>
                    <th rowspan="2">Mode Of <br> Payment</th>
                    <th rowspan="2">Status</th>
                    <th rowspan="2" style="width: 80px;">Action</th>
                </tr>
                <tr>
                    <th>P</th>
                    <th>I</th>
                    <th>Paid</th>
                    <th>Outstanding</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- End Page -->
    <script>
        function ajaxDataLoad(filBranch = null, filSamity = null, fillProduct = null, loanCode = null, start_date = null,
            end_date = null) {
            $('.clsDataTable').DataTable({
                destroy: true,
                processing: true,
                serverSide: true,
                "ajax": {
                    "url": "{{ url()->current() }}",
                    "dataType": "json",
                    "type": "post",
                    "data": {
                        filBranch: filBranch,
                        filSamity: filSamity,
                        fillProduct: fillProduct,
                        loanCode: loanCode,
                        start_date: start_date,
                        end_date: end_date
                    }
                },
                "columns": [{
                        data: 'sl',
                        name: 'sl',
                        orderable: false,
                        targets: 0,
                        className: 'text-center'
                    },
                    {
                        "data": "memberName"
                    },
                    {
                        "data": "memberCode"
                    },
                    {
                        "data": "loanCode"
                    },
                    {
                        "data": "date"
                    },
                    {
                        "data": "principalAmount",
                        className: 'text-right'
                    },
                    {
                        "data": "interestAmount",
                        className: 'text-right'
                    },
                    {
                        "data": "principalWithInterest",
                        className: 'text-right'
                    },
                    {
                        "data": "outStanding",
                        className: 'text-right'
                    },
                    {
                        "data": "paymentType",
                        className: 'text-center'
                    },
                    {
                        "data": "status",
                        className: 'text-center'
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
                "columnDefs": [

                    {
                        "targets": -2,
                        "createdCell": function(td, cellData, rowData, row, col) {
                            $(td).addClass("text-center");
                            if (rowData['isAuthorized'] == 1) {
                                $(td).html(
                                    '<span style="font-size: 16px; color: Dodgerblue;"><i class="fas fa-check"></i></span>'
                                );
                            } else {
                                $(td).html(
                                    '<span style="font-size: 16px; color: red;"><i class="far fa-times-circle"></i></span>'
                                );
                            }
                        }

                    },
                    // {
                    //     "targets": -1,
                    //     "createdCell": function(td, cellData, rowData, row, col) {
                    //         $(td).addClass("text-center d-print-none");
                    //         $(td).closest('tr').attr("cellData", cellData);
                    //         if (rowData['isAuthorized'] == 1 || (rowData['paymentType'] != 'Cash' &&
                    //                 rowData['paymentType'] != 'Bank')) {
                    //             $(td).html(
                    //                 '<a href="javascript:void(0)" title="View" class="btnView" data-target="#loanViewModal" onclick="fnView(\'' +
                    //                 cellData + '\'' +
                    //                 ')"><i class="icon wb-eye mr-2 blue-grey-600"></i></a> <i class="icon wb-edit mr-2 blue-grey-400"></i> <i class="icon wb-trash mr-2 blue-grey-400"></i>'
                    //             );
                    //         } else {
                    //             $(td).html(
                    //                 '<a href="javascript:void(0)" title="View" class="btnView" data-target="#loanViewModal" onclick="fnView(\'' +
                    //                 cellData + '\'' +
                    //                 ')"><i class="icon wb-eye mr-2 blue-grey-600"></i></a> <a href=' +
                    //                 "{{ url()->current() }}" + '/edit/' + cellData +
                    //                 ' title="Edit" class="btnEdit"><i class="icon wb-edit mr-2 blue-grey-600"></i></a> <a href="javascript:void(0)" onclick="fnDelete(\'' +
                    //                 cellData + '\'' +
                    //                 ');" title="Delete" class=""><i class="icon wb-trash mr-2 blue-grey-600"></i></a>'
                    //             );
                    //         }

                    //     }
                    // }
                ]

            });
        }



        $(document).ready(function() {
            ajaxDataLoad();

            $('#searchButton').click(function() {

                var filBranch = $('#filBranch').val();
                var filSamity = $('#filSamity').val();
                var fillProduct = $('#fillProduct').val();
                var loanCode = $('#fillLoanCode').val();
                var start_date = $('#start_date').val();
                var end_date = $('#end_date').val();

                ajaxDataLoad(filBranch, filSamity, fillProduct, loanCode, start_date, end_date);
            });

            $('#start_date, #end_date').click(function() {
                $(this).val('');
            });
            $('#start_date, #end_date').on('input', function() {
                $(this).val('');
            });

            //prevent redirect on view and show modal
            $(document).on('click', '.btnView', function(e) {
                e.preventDefault();
                let ref= $(this).attr('href');
                ref= ref.split('/');
                let id= ref[ref.length -1];
                fnView(id);
            });
        });

        function fnView(id) {

            $('#loanViewModal').modal('toggle');

            $.ajax({
                type: "POST",
                url: "{{ url()->current() }}" + '/view',
                data: {
                    context: 'viewData',
                    id: id
                },
                dataType: "json",
                /* beforeSend: function() {
                    $("#loaderDiv").show();
                }, */
                success: function(response) {

                    $('#memberName').html(response.memberName);
                    $('#memberCode').html(response.memberCode);
                    $('#loanCode').html(response.loanCode);
                    $('#date').html(response.date);
                    $('#principalAmount').html(response.principalAmount);
                    $('#interestAmount').html(response.interestAmount);
                    $('#outStanding').html(response.outStanding);
                    $('#paid').html(response.amount);
                    $('#paymentType').html(response.paymentType);
                    $('#status').html(response.status);
                    $('#entryBy').html(response.entryBy);

                    // $('#loanViewModal').modal('toggle');

                },
                error: function() {
                    alert('error!');
                }
            });
        }

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
    </script>
    <!-- End Page -->
@endsection
