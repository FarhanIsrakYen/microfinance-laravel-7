@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>

<!-- Page -->
    <form enctype="multipart/form-data" method="post" data-toggle="validator" novalidate="true">
        @csrf
        <div class="row">
            <div class="col-lg-9 offset-lg-3">

                <!-- Html View Load  -->
               {!! HTML::forCompanyFeild() !!}

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Department</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="dept_id" id="dept_id"
                                required data-error="Please select Department">
                                <option value="">Select Department</option>
                                @foreach($departmentData as $dep)
                                    <option value="{{ $dep->id }}">{{ $dep->dept_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Name</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <input type="text" class="form-control round" name="room_name" id="room_name" 
                            placeholder="Enter Name" required data-error="Please enter name.">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title">Code</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <input type="text" class="form-control round" name="room_code" id="room_code" placeholder="Enter Code">
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-lg-9">
                        <div class="form-group d-flex justify-content-center">
                            <div class="example example-buttons">
                                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                                <button type="submit" class="btn btn-primary btn-round" id="btnSubmitCompany">Save</button>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
<!-- End Page -->

<script type="text/javascript">
    $('form').submit(function (event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });
</script>
@endsection
