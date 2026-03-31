@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true">
    @csrf
    <div class="row ">
        <div class="col-lg-9 offset-lg-3">
          {!! HTML::forCompanySelect() !!}
          <div class="form-row align-items-center">
              <label class="col-lg-3 input-title RequiredStar" for="tc_name">Terms & Conditions</label>
              <div class="col-lg-5">
                  <div class="form-group">
                      <div class="">
                          <input type="text" class="form-control round" id="tc_name" name="tc_name" value="{{$TCData->tc_name}}" required="true" required data-error="Please enter Terms & Conditions Name.">
                      </div>
                      <div class="help-block with-errors is-invalid"></div>
                  </div>
              </div>
          </div>

            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round" id="updateButton">Update</button>
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
        $(this).find(':submit').attr('disabled', 'disabled');
    });
</script>
@endsection
