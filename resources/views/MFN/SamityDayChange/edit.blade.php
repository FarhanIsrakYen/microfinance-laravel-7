@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal"
    data-toggle="validator" novalidate="true" autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-8 offset-lg-3">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Samity</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="samity" id="samity" disabled>
                            <option value="">Select</option>
                            @foreach ($samities as $samity)
                            <option value="{{ $samity->id }}"
                                {{ $samityDayChange->samityId == $samity->id ? 'selected' : '' }}>{{ $samity->name }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Samity Day</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control round" name="samityDay" placeholder="Current Samity Day"
                            readonly value="{{ $samityDayChange->oldSamityDay }}">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">New Samity Day</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="newSamityDay" id="newSamityDay">
                            <option value="">Select</option>
                            @foreach ($workingDays as $workingDay)
                            <option value="{{ $workingDay }}"
                                {{ $samityDayChange->newSamityDay == $workingDay ? 'selected' : '' }}>{{ $workingDay }}
                            </option>
                            @endforeach
                        </select>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Effective Date</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text">
                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control round" id="effectiveDate" name="effectiveDate"
                                placeholder="DD-MM-YYYY" readonly value="{{ $effectiveDate }}">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
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
                            <button type="submit" class="btn btn-primary btn-round">Save</button>
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
            .done(function(response) {

                if (response['alert-type']=='error') {
                    swal({
                        icon: 'error',
                        title: 'Oops...',
                        text: response['message'],
                    });
                    $('form').find(':submit').prop('disabled', false);
                    console.log(response['consoleMsg']);
                }
                else{
                    $('form').trigger("reset");
                    swal({
                        icon: 'success',
                        title: 'Success...',
                        text: response['message'],
                    });

                    setTimeout(function(){ window.location =  './../'}, 2000);
                }


            })
            .fail(function() {
                console.log("error");
            })
            .always(function() {
                console.log("complete");
            });

        });
    });

</script>


@endsection
