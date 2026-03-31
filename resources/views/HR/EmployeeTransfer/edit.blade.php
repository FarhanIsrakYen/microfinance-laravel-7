@extends('Layouts.erp_master')
@section('content')

    <form method="post" enctype="multipart/form-data" data-toggle="validator" novalidate="true">
        @csrf

        <input type="hidden" name="company_id" value="{{ $companyId }}">
        <div class="row">
            <div class="col-lg-8 offset-lg-3">
                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Employee</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="employee_no" id="employee_no">
                                <option value="">Select One</option>
                                @foreach ($employees as $row)
                                    <option value="{{ $row->employee_no }}" {{ ($employee_no == $row->employee_no ) ? 'selected' : '' }}>{{ $row->employee }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        {!! App\Services\HtmlService::forBranchFeild(
                        true,
                        'branch_from',
                        'branch_from',
                        $branch_from,
                        '',
                        'Branch
                        From'
                        ) !!}
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        {!! App\Services\HtmlService::forBranchFeild(true, 'branch_to', 'branch_to', $branch_to, '', 'Branch to')
                        !!}
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Transfer Date</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <input type="text" class="form-control datepicker-custom" name="transfer_date"
                                id="transfer_date" value="{{ \Carbon\Carbon::parse($transfer_date)->format('d-m-Y') }}"
                                placeholder="DD-MM-YYYY">
                        </div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <div class="col-lg-8">
                        <div class="form-group d-flex justify-content-center">
                            <div class="example example-buttons">
                                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                                <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Update</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>

    <script type="text/javascript">
        $("#employee_no").change(function(e) {

            var employeeId = $(this).val();

            if (employeeId == '') {
                return false;
            }

            $.ajax({
                type: "GET",
                url: "./../getData",
                data: {
                    context: 'branchFrom',
                    employeeId: employeeId
                },
                dataType: "json",
                success: function(response) {
                    $('#branch_from option[value="' + response.employeeBranch + '"]').prop('selected',
                        true);
                    $('#branch_from').trigger('change');
                },
                error: function() {
                    alert('error!');
                }
            });
        });

        $('#branch_to').change(function() {

            var branchFrom = $('#branch_from').val();
            var branchTo = $(this).val();

            if (branchFrom == branchTo) {
                swal({
                    icon: 'error',
                    title: 'Error...',
                    text: "Branch From & Branch To can't be same!!",
                });

                $('#branch_to option[value="' + 1 + '"]').prop('selected',
                    true)
                $('#branch_to').trigger('change');
            }
        });

        $('form').submit(function (event) {
            event.preventDefault();
            // $(this).find(':submit').attr('disabled', 'disabled');

            $.ajax({
                    url: "{{ url()->current() }}",
                    type: 'POST',
                    dataType: 'json',
                    data: $('form').serialize(),
                })
                .done(function (response) {

                    if (response['alert-type'] == 'error') {
                        swal({
                            icon: 'error',
                            title: 'Oops...',
                            text: response['message'],
                        });
                        $('form').find(':submit').prop('disabled', false);
                    } else {
                        $('form').trigger("reset");
                        swal({
                            icon: 'success',
                            title: 'Success...',
                            text: response['message'],
                        });

                        setTimeout(function () {
                            window.location = './../'
                        }, 3000);
                    }

                })
                .fail(function () {
                    console.log("error");
                })
                .always(function () {
                    console.log("complete");
                });

        });

    </script>
@endsection
