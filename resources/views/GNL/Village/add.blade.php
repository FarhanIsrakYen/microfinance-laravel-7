@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="POST" class="form-horizontal" data-toggle="validator" novalidate="true">
    @csrf
    <div class="row">
        <div class="col-lg-9 offset-lg-3">

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="division_id">Division</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                        name="division_id" id="division_id"
                        required data-error="Please select Division name."
                        onchange="fnAjaxSelectBox(
                                            'district_id',
                                            this.value,
                                '{{base64_encode('gnl_districts')}}',
                                '{{base64_encode('division_id')}}',
                                '{{base64_encode('id,district_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                        );">
                            <option value="">Select Division</option>
                            @foreach ($divisionData as $row)
                            <option value="{{$row->id}}">{{$row->division_name}}</option>
                            @endforeach

                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="district_id">District</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                        name="district_id" id="district_id"
                        required data-error="Please select District name."
                        onchange="fnAjaxSelectBox(
                                            'upazila_id',
                                            this.value,
                                '{{base64_encode('gnl_upazilas')}}',
                                '{{base64_encode('district_id')}}',
                                '{{base64_encode('id,upazila_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                        );">
                            <option value="">Select District</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Upazila</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                        name="upazila_id" id="upazila_id"
                        required data-error="Please select Upazila name."
                        onchange="fnAjaxSelectBox(
                                            'union_id',
                                            this.value,
                                '{{base64_encode('gnl_unions')}}',
                                '{{base64_encode('upazila_id')}}',
                                '{{base64_encode('id,union_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                        );">
                            <option value="">Select Upazila</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="union_id">Union</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="union_id" id="union_id" required data-error="Please select Upazila name.">
                            <option value="">Selact One</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="village_name">village</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter village Name" name="village_name" id="village_name" required data-error="Please enter village name.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Save</button>
                            <!--<button type="button" class="btn btn-warning btn-round">Next</button>-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
      </div>
</form>

<script type="text/javascript">
$('form').submit(function (event) {
    // event.preventDefault();
    $(this).find(':submit').attr('disabled', 'disabled');
    // $(this).submit();
});
</script>
<!-- End Page -->
@endsection
