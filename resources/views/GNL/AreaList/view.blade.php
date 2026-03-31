@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>

<div class="row">
        <div class="col-lg-9 offset-lg-3">
            {!! HTML::forCompanyFeild($AreaData->company_id,'disabled') !!}
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="area_name">Area Name</label>

                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="">
                            <input type="hidden" id="area_id" value="{{$AreaData->id}}">
                            <input type="text" class="form-control round" id="area_name" name="area_name" value="{{$AreaData->area_name}}" required="true" required data-error="Please select Area Name." readonly>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="area_code">Area Code</label>
                <div class="col-lg-5">
                    <div class="form-group">
                        <div class="input-group ">
                            <input type="text" class="form-control round" id="area_code" name="area_code" value="{{$AreaData->area_code}}" required="true" required data-error="Please select Area Mobile." readonly>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

              <!-- {!! HTML::forCompanyFeild($AreaData->company_id,'disabled') !!} -->
              <div class="form-row align-items-center">
                  <label class="col-lg-3 input-title RequiredStar" for="company_id">COMPANY</label>
                  <div class="col-lg-5 form-group">
                      <div class="input-group">
                          <select class="form-control clsCompany clsSelect2" disabled>
                              <option value="">Select Company</option>
                              @foreach ($Companies as $Row)
                              <option value="{{$Row->id}}"
                                      {{ ($AreaData->company_id == $Row->id) ? 'selected="selected"' : ''}} >
                                  {{$Row->comp_name}}
                              </option>
                              @endforeach
                          </select>
                      </div>
                      <div class="help-block with-errors is-invalid"></div>
                  </div>
              </div>
            <div class="form-row align-items-center">
                <label class="col-lg-12 input-title">
                    Branchs
                </label>

                <div class="col-lg-12" id="BranchDiv">&nbsp;</div>
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

<script>
    $(document).ready(function () {
        var CompanyID = $('#company_id').val();
        var AreaID = $('#area_id').val();

        $.ajax({
            method: "GET",
            url: "{{url('gnl/area/ajaxAreaList')}}",
            dataType: "text",
            data: {
                CompanyID: CompanyID,
                AreaID: AreaID
            },
            success: function (data) {
                if (data) {
                    $('#BranchDiv').html(data);
                }
            }
        });
    });
</script>

@endsection
