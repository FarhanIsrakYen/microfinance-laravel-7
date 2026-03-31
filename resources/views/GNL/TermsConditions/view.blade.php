@extends('Layouts.erp_master')
@section('content')

<!-- Page -->
<form enctype="multipart/form-data" action="" method="POST" data-toggle="validator" novalidate="true">
    @csrf


      <div class="row ">
          <div class="col-lg-9 offset-lg-3">
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="tc_name">Terms & Conditions</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="">
                            <input type="text" class="form-control round" id="tc_name" name="tc_name" value="{{$TCData->tc_name}}" required="true" required data-error="Please enter Terms & Conditions Name." readonly>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-8">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round d-print-none">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Page -->

@endsection
