@extends('Layouts.erp_master')
@section('content')

<!-- Page -->
    <div class="row">
        <div class="col-lg-9 offset-lg-3">
            <!-- <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar" for="division_id">Division</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="division_id" id="division_id" required data-error="Please select Division name." disabled>
                                <option value="{{$VillageData->division_id}}" selected="selected">{{$VillageData->division['division_name'] }}</option>
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar" for="district_id">DISTRICT</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="district_id" id="district_id" required data-error="Please select District name." disabled>
                                <option value="{{$VillageData->district_id}}" selected="selected">{{$VillageData->division['division_name'] }}</option>
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar" for="upazila_id">UPAZILA</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <select class="form-control clsSelect2" name="upazila_id" id="upazila_id" required data-error="Please select Upazila name." disabled>
                                <option value="{{$VillageData->upazila_id}}" selected="selected">{{$VillageData->upazila['upazila_name'] }}</option>
                            </select>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div> -->
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="union_id">Union</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="union_id" id="union_id" required
                            data-error="Please select Upazila name." disabled>
                            <option value="{{$VillageData->union_id}}" selected="selected">
                                {{$VillageData->union['union_name'] }}</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="village_name">village</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" value="{{$VillageData->village_name}}"
                            name="village_name" id="village_name" required
                            data-error="Please enter village name." readonly>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<!-- End Page -->
@endsection