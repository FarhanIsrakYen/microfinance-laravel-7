@extends('Layouts.erp_master_full_width')
@section('content')

@include('elements.report.report_filter_options', ['branch' => true
])


<div class="w-full">
    <div class="panel">
        <div class="panel-body panel-search pt-2">

            @include('elements.report.report_heading', ['title' => 'Branch Customer Report', 'title_excel' =>
            'Branch_Customer_Report'])

            <div class="row ExportDiv">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered table-striped clsDataTable">
                        <thead>
                            <tr>
                                <th width="3%">SL</th>
                                <th>Branch Name</th>
                                <th>Customer</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td colspan="2" style="text-align: right!important;"><b>Total</b></td>
                                <td class="text-center" id="nCustomer"><b>0</b></td>
                            </tr>
                        </tfoot>
                    </table>
                    @include('../elements.signature.signatureSet')
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {

        $('#searchButton').click(function () {
            var branch_id = $('#branch_id').val();

            $('#reportBranch').html($('#branch_id').find("option:selected").text());
            $('#start_date_txt').html($('#start_date').val());
            $('#end_date_txt').html($('#end_date').val());
            $(".wb-minus").trigger('click');

            ajaxDataLoad(branch_id);
        });

        $('#branch_id').width('100%');
    });

    function ajaxDataLoad(branch_id = null) {

        $('.clsDataTable').DataTable({
            destroy: true,
            processing: true,
            serverSide: true,
            // lengthMenu: [[10, 20, 30, 50, -1], [10, 20, 30, 50, "All"]],
            paging: false,
            ordering: false,
            info: false,
            searching: false,
            "ajax": {
                "url": "{{ route('branchcustomerDatatable') }}",
                "dataType": "json",
                "type": "post",
                "data": {
                    _token: "{{ csrf_token() }}",
                    branchId: branch_id,
                }
            },
            columns: [{
                    data: 'sl',
                    className: 'text-center'
                },
                {
                    data: 'branch_name'
                },
                {
                    data: 'customer_count',
                    className: 'text-center'
                },

            ],
            drawCallback: function (oResult) {

                if (oResult.json) {
                    $('#totalRowDiv').html(oResult.json.totalRow);
                    $('#nCustomer').html(oResult.json.tcustomer);
                }
            },
        });
    }

</script>
@endsection
