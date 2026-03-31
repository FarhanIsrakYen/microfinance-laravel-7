@extends('Layouts.erp_master')
@section('content')


<!-- Page -->
<!-- <div class="page">
    <div class="page-header">
        <h4 class="">New Union Entry</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">Address</a></li>
            <li class="breadcrumb-item"><a href="index.php?PageId=UnionList">Union/Zone List</a></li>
            <li class="breadcrumb-item active">Entry</li>
        </ol>
    </div>

    <div class="page-content">
        <div class="panel">
            <div class="panel-body"> -->
                <form action="{{route('storeunion')}}" method="POST"  data-toggle="validator" novalidate="true">
                @csrf
                    <div class="row">
                        <div class="col-lg-9 offset-lg-3">
                            <div class="form-row align-items-center">
                              <label class="col-lg-3 input-title RequiredStar" for="divisionName">Division</label>
                                <div class="col-lg-5 form-group">
                                    <div class="input-group">
                                        <select class="form-control clsSelect2" name="devision_id " id="divisionName" required data-error="Please select Division name.">
                                            <option>Select One</option>
                                            <option value="1">Dhaka</option>
                                            <option value="2"> Rajshahi</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>
                            <div class="form-row align-items-center">
                              <label class="col-lg-3 input-title RequiredStar" for="groupName">DISTRICT</label>
                                <div class="col-lg-5 form-group">
                                    <div class="input-group">
                                        <select class="form-control clsSelect2" name="district_id " id="districtName" required data-error="Please select District name.">
                                            <option>Select One</option>
                                            <option value="1">Gazipur</option>
                                            <option value="2">Bogura</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>
                            <div class="form-row align-items-center">
                              <label class="col-lg-3 input-title RequiredStar" for="upazilaName">UPAZILA</label>
                                <div class="col-lg-5 form-group">
                                    <div class="input-group">
                                        <select class="form-control clsSelect2" name="upazila_id " id="upazilaName" required data-error="Please select Upazila name.">
                                            <option>Select One</option>
                                            <option value="1">North Dhaka</option>
                                            <option value="2">South Dhaka</option>
                                        </select>
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>
                            <div class="form-row align-items-center">
                              <label class="col-lg-3 input-title RequiredStar" for="unionName">UNION/ZONE</label>
                                <div class="col-lg-5 form-group">
                                    <div class="input-group">
                                        <input type="text" class="form-control round" placeholder="Enter village Name" name="union_name" id="unionName" required data-error="Please enter Union name.">
                                    </div>
                                    <div class="help-block with-errors is-invalid"></div>
                                </div>
                            </div>
                            <div class="form-row align-items-center">
                                <div class="col-lg-6">
                                    <div class="form-group d-flex justify-content-center">
                                        <div class="example example-buttons">
                                            <a href="{{ url('gnl/union') }}" class="btn btn-default btn-round">Close</a>
                                            <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Save</button>
                                            <!--<button type="button" class="btn btn-warning btn-round">Next</button>-->
                                        </div>
                                    </div>
                                </div>
                            </div>
                          </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
      </div>

<!-- End Page -->
@endsection
