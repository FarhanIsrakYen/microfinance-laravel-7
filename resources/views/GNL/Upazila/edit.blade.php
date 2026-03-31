@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true">
    @csrf
    <div class="row">
        <div class="col-lg-9 offset-lg-3">
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="division_id">Devision</label>
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
                            @foreach ($divData as $Row)
                            <option value="{{$Row->id}}" {{ ($upazilaData->division_id == $Row->id) ? 
                                'selected="selected"' : ''}}>{{$Row->division_name}}
                            </option>
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
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="upazila_id">Upazila</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" value="{{$upazilaData->upazila_name}}" name="upazila_name" id="upazila_name" required data-error="Please enter Upszila name.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <div class="col-lg-2"></div>
                <div class="col-lg-5">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                        <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round" id="upsdateButtonforUpazila">Update</button>
                            <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    //   all company load
    $(document).ready(function(){
        fnAjaxSelectBox(
            'district_id',
            {{ $upazilaData-> division_id }},
            '{{base64_encode('gnl_districts')}}',
            '{{base64_encode('division_id')}}',
            '{{base64_encode('id,district_name')}}',
            '{{url('/ajaxSelectBox')}}',
            {{ $upazilaData->district_id}}
        );
        $('form').submit(function (event) {
            // event.preventDefault();
            $(this).find(':submit').attr('disabled', 'disabled');
            // $(this).submit();
        });
    });

</script>

@endsection
