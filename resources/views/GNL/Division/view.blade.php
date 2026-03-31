@extends('Layouts.erp_master')
@section('content')
    <div class="row ">
        <div class="col-lg-9 offset-lg-3">
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="division_name">Division Name</label>

                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="">
                            <input type="text" class="form-control round" id="division_name" name="division_name" value="{{$DivData->division_name}}" required="true" required data-error="Please enter division Name." readonly>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title" for="short_name">Short name</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            <input type="text" class="form-control round" id="short_name" name="short_name" value="{{$DivData->short_name}}" readonly >
                        </div>

                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
