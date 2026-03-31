@extends('Layouts.erp_master')
@section('content')

<!-- Page -->
<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true"
    autocomplete="off">
    @csrf

    <div class="row">
        <div class="col-lg-6">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Name</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="samity_name" name="samity_name"
                            placeholder="Enter Samity Name" required data-error="Please enter Samity Name.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Samity Code</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="samity_code" name="samity_code"
                            placeholder="Samity Code" value="{{ $samityCode }}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Working Area</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="working_area" required
                            data-error="Please Selct Working Area">
                            <option value="">Select</option>
                            @foreach ($workingAreas as $workingArea)
                            <option value="{{ $workingArea->id }}">{{ $workingArea->name }}</option>
                            @endforeach
                        </select>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Registration No</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="reg_no" name="reg_no"
                            placeholder="Enter Registration No.">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Field Officer</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="field_officer" required
                            data-error="Please Select Field Officer">
                            <option value="">Select</option>
                            @foreach ($filedOfficers as $filedOfficer)
                            <option value="{{$filedOfficer->id}}">{{$filedOfficer->name}}</option>

                            @endforeach
                        </select>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Samity Day</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="samity_day" required
                            data-error="Please Select A Day">
                            @foreach ($weekDays as $weekDay)
                            <option value="{{ $weekDay }}">{{ $weekDay }}</option>
                            @endforeach
                        </select>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Samity Time</label>
                <div class="col-lg-7">
                    <div class="input-group clockpicker">
                        <input type="text" name="samity_time" class="form-control" value="00:00">
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-time"></span>
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-6">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Samity Type</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="samity_type" required
                            data-error="Please Select Samity Type">
                            <option value="Female">Female</option>
                            <option value="Male">Male</option>
                            <option value="Both">Both</option>
                        </select>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Opening Date</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        @if ($isOpening)
                        <input type="text" class="form-control round" id="opening_date" name="opening_date"
                        autocomplete="off" placeholder="DD-MM-YYYY" required
                        data-error="Please Select A Date">
                        @else
                        <input type="text" class="form-control round" id="opening_date" name="opening_date"
                            value="{{ \Carbon\Carbon::parse($sysDate)->format('d-m-Y') }}" readonly autocomplete="off" placeholder="DD-MM-YYYY" required>
                        @endif
                        
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title" for="transferable">Is Tranferable : </label>
                <div class="col-lg-7">
                    <div class="input-group checkbox-custom checkbox-primary">
                        <input type="checkbox" name="transferable" id="transferable" value="1">
                        <label></label>
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Maximum Active Member</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber" id="max_no" name="max_no"
                            placeholder="Enter Max Number" required data-error="Please Enter Max Member Number">
                    </div>
                    <div id="errmsg" class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Latitude</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="latitude" name="latitude"
                            placeholder="Enter Latitude">
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Longitude</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="longitude" name="longitude"
                            placeholder="Enter Longitude">
                    </div>
                </div>
            </div>

        </div>
    </div>
    <div class="row align-items-center">
        <div class="col-lg-12">
            <div class="form-group d-flex justify-content-center">
                <div class="example example-buttons">
                    <a href="javascript:void(0)" onclick="goBack();"
                        class="btn btn-default btn-round d-print-none">Back</a>
                    <button type="submit" class="btn btn-primary btn-round">Save</button>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Page -->

<script type="text/javascript">
    jQuery(document).ready(function ($) {

        $('form').submit(function (event) {
            event.preventDefault();

            //disable Multiple Click
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
                    } else {
                        $('form').trigger("reset");
                        swal({
                            icon: 'success',
                            title: 'Success...',
                            text: response['message'],
                        });

                        setTimeout(function () {
                            window.location = './'
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

        var isOpening = "{{ $isOpening }}";

        if(isOpening){
            var systemDate = new Date("{{ $sysDate }}");
            $('#opening_date').datepicker({
                dateFormat: 'dd-mm-yy',
                orientation: 'bottom',
                autoclose: true,
                todayHighlight: true,
                changeMonth: true,
                changeYear: true,
                yearRange: '1900:+10',
                maxDate : systemDate
            }).keydown(false);
        }  

    });

</script>
@endsection
