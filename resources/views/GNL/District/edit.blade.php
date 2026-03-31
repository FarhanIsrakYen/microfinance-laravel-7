@extends('Layouts.erp_master')
@section('content')

<!-- Page -->
    <form enctype="multipart/form-data" method="post" data-toggle="validator" novalidate="true">
        @csrf
        <div class="row">
            <div class="col-lg-9 offset-lg-3">
                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar" for="division_id">Division</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="division_id" id="division_id" required data-error="Select Division">
                                <option value="">Select Division</option>
                                @foreach ($DivData as $Row)
                                <option value="{{$Row->id}}" {{ ($DisData->division_id == $Row->id) ? 'selected="selected"' : '' }} >
                                    {{$Row->division_name}}
                                </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">District</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <input type="text" class="form-control round" name="district_name" id="district_name" value="{{$DisData->district_name}}" required data-error="Please enter District name.">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-9">
                        <div class="form-group d-flex justify-content-center">
                            <div class="example example-buttons">
                                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                                <button type="submit" class="btn btn-primary btn-round" id="updateButtonDistrict">Update</button>
                                <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
<!--End Page -->
<script type="text/javascript">
    $('form').submit(function (event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });
</script>
@endsection
