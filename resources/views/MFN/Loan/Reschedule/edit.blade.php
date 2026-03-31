@extends('Layouts.erp_master')

@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
    autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-10 offset-lg-3">

            <input type="hidden" name="id" value="{{ encrypt($reschedule->id) }}">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Loan</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control round" value="{{ $loanCode }}" readonly>
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Installment Number</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" name="installmentNo" class="form-control round" value="{{ $reschedule->installmentNo }}" readonly>
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Current Installment Date</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control round" name="previosDate"
                            value="{{ date('d-m-Y', strtotime($reschedule->previosDate)) }}" readonly>
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Number Of Term to Reschedule</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" name="numberOfTerm" id="numberOfTerm"
                        value="{{ $reschedule->numberOfTerm }}" required>
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">New Installment Date</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control round" name="rescheduleDate" id="rescheduleDate" 
                        value="{{ date('d-m-Y', strtotime($reschedule->rescheduleDate)) }}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <div class="col-lg-2"></div>
                <div class="col-lg-5">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round d-print-none">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Page -->
<script type="text/javascript">
    $(document).ready(function () {

        $('form').submit(function (event) {
            event.preventDefault();
            $(this).find(':submit').attr('disabled', 'disabled');

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
                        console.log(response['consoleMsg']);
                    } else {
                        $('form').trigger("reset");
                        swal({
                            icon: 'success',
                            title: 'Success...',
                            text: response['message'],
                        });

                        setTimeout(function () {
                            goBack();
                            // window.location = './'
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

        /* get rescheduleDate when numberOfTerm is given */
        $("#numberOfTerm").on('input', function () {
            $("#rescheduleDate").val('');

            var numberOfTerm = $(this).val();

            if(numberOfTerm == ''){
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./../getData",
                data: {
                    context: 'numberOfTerm',
                    loaId: "{{ encrypt($reschedule->loanId) }}",
                    installmentNumber: "{{ $reschedule->installmentNo }}",
                    numberOfTerm: numberOfTerm,
                    exceptRescheduleId: "{{ $reschedule->id }}",
                },
                dataType: "json",
                success: function (response) {
                    $("#rescheduleDate").val(response['reschedulableDate']);
                },
                error: function () {
                    alert('error!');
                }
            });
        });

        /* end get rescheduleDate when numberOfTerm is given */

    });

</script>


@endsection
