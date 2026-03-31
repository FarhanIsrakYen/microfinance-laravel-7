@extends('Layouts.erp_master')
@section('content')

<form method="post" class="form-horizontal" data-toggle="validator" novalidate="true" autocomplete="off">
    @foreach ($components as $component)
    <div class="row" componenet_id="{{ $component->id }}">
        <div class="col-lg-12">
            <div class="panel panel-default">
                <div class="panel-heading p-2 mb-4">
                    <div class="form-row form-group align-items-center">
                        <label class="col-lg-2 input-title">{{ $component->title }}</label>
                        <label class="col-lg-1 input-title">Voucher Type</label>
                        <div class="col-lg-3">
                            <div class="input-group">
                                <input type="hidden" name="voucherTypeComponentIds[]" value="{{ $component->id }}">
                                <select name="voucherTypes[]" class="form-control clsSelect2 voucherTypes"
                                    voucherType="{{ $component->voucherType }}">
                                    <option value="">Select</option>
                                    <option value="Debit">Debit</option>
                                    <option value="Credit">Credit</option>
                                    <option value="Journal">Journal</option>
                                </select>
                            </div>
                        </div>
                        <label class="col-lg-1 input-title">Status</label>
                        <div class="col-lg-3">
                            <div class="input-group">
                                <select name="status[]" class="form-control clsSelect2 status" status="{{ $component->status }}">
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel-body">
                    @php
                    $currentFundingOrgId = 0;
                    $currentLoanProductId = 0;
                    @endphp
                    <div class="row tableDiv">
                        
                    </div>
                </div>
            </div>

        </div>
    </div>
    @endforeach {{-- Component --}}

    <div class="form-row form-group d-flex justify-content-center">
        <div class="example example-buttons">
            <a href="javascript:void(0)" id="previousButton" class="btn btn-default btn-round">Previous</a>
            <button type="submit" class="btn btn-primary btn-round">Save</button>
        </div>
    </div>
</form>

<script>
    // set voycher types
    $('.voucherTypes').each(function (index, element) {
            $(this).val($(this).attr('voucherType'));
        });
        // set components status
        $('.status').each(function (index, element) {
            var value = $(this).attr('status') == 1 ? 'active' : 'inactive';
            $(this).val(value);
        });

    $(document).ready(function () {        

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
                        // $('form').trigger("reset");
                        swal({
                            icon: 'success',
                            title: 'Success...',
                            text: response['message'],
                        }).then(function () {
                            window.location.reload();
                        });
                    }

                })
                .fail(function () {
                    console.log("error");
                });
        });

        /* load page */
        $('.voucherTypes, .status').on('change', function () {
            $(this).parents('.row').find('.tableDiv').empty();

            var componentId = $(this).parents('.row').attr('componenet_id');
            var voucherType = $(this).val();
            var status = $(this).parents('.row').find('.status').val();

            console.log(componentId);
            console.log(voucherType);
            console.log(status);

            if (componentId == '' || voucherType == '' || status == 'inactive') {
                return false;
            }

            // return false;
            $(this).parents('.row').find('.tableDiv').load("{{ url()->current() }}"+"/loadDiv?componentId="+componentId+"&voucherType="+voucherType);
        });
        /* end loading page */
        $('.voucherTypes').trigger('change');
    });

</script>

@endsection
