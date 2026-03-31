@extends('Layouts.erp_master')
@section('content')
<!-- Page -->
<div class="row">
    <div class="col-lg-9 offset-lg-3">


        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Applicable For</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <div class="radio-custom radio-primary">
                        <input type="radio" id="checkGroup" name="sh_app_for" value="org"
                            {{ $SpecialHolidayData->sh_app_for == 'org' ? 'checked' : ''}}>
                        <label for="checkGroup">Organization &nbsp &nbsp </label>
                    </div>
                    <div class="radio-custom radio-primary">
                        <input type="radio" id="checkBranch" name="sh_app_for" value="branch"
                            {{ $SpecialHolidayData->sh_app_for == 'branch' ? 'checked' : ''}}>
                        <label for="checkBranch">Branch &nbsp &nbsp </label>
                    </div>
                    <div class="radio-custom radio-primary" style="display: none">
                        <input type="radio" id="checkSomity" name="sh_app_for" value="somity"
                            {{ $SpecialHolidayData->sh_app_for == 'somity' ? 'checked' : ''}}>
                        <label for="checkSomity">Somity &nbsp &nbsp </label>
                    </div>
                </div>
                <div class="help-block with-errors is-invalid"></div>
            </div>
        </div>
        @if ($SpecialHolidayData->sh_app_for == 'org')
        <div class="form-row align-items-center desc" id="org">
            <label class="col-lg-3 input-title RequiredStar">Organization</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <select class="form-control clsSelect2" name="company_id" id="selOrgId" required
                        data-error="Select Organization" disabled>
                        <option value="0" selected="selected">Select Organization</option>
                        @foreach($CompanyData as $Row)
                        <option value="{{ $Row->id }}" @if($Row->id==$SpecialHolidayData->company_id)
                            selected='selected' @endif >{{ $Row->comp_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="help-block with-errors is-invalid"></div>
            </div>
        </div>
        @endif

        @if ($SpecialHolidayData->sh_app_for == 'branch')
        <div class="form-row align-items-center desc" id="branch">
            <label class="col-lg-3 input-title RequiredStar">Branch</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <select class="form-control clsSelect2" name="branch_id" id="selBranchId" data-error="Select Branch"
                        disabled>
                        <option value="" selected="selected">Select Branch</option>
                        @foreach($BranchData as $Row)
                        <option value="{{ $Row->id }}" @if($Row->id==$SpecialHolidayData->branch_id) selected='selected'
                            @endif >{{ $Row->branch_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="help-block with-errors is-invalid"></div>
            </div>
        </div>
        @endif

        @if ($SpecialHolidayData->sh_app_for == 'somity')
        <div class="form-row align-items-center desc" id="somity">
            <label class="col-lg-3 input-title RequiredStar">Somity</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <select class="form-control clsSelect2" name="somity_id" id="selSomityId" data-error="Select Somity"
                        disabled>
                        <option value="" selected="selected">Select Somity</option>

                    </select>
                </div>
                <div class="help-block with-errors is-invalid"></div>
            </div>
        </div>
        @endif

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Holiday Title</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <input type="text" class="form-control round" placeholder="Enter Holiday Title" name="sh_title"
                        id="sh_title" value="{{$SpecialHolidayData->sh_title}}" readonly>
                </div>
                <div class="help-block with-errors is-invalid"></div>
            </div>
        </div>

        <div class="form-row align-items-center ">
            <label class="col-lg-3 input-title RequiredStar">Holiday Date From</label>
            <div class="col-lg-5 form-group">
                <div class="input-group ghdatepicker">
                    <div class="input-group-prepend ">
                        <span class="input-group-text ">
                            <i class="icon wb-calendar round" aria-hidden="true"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control round" id="dateSPHFrom" name="sh_date_from"
                        data-plugin="datepicker" placeholder="DD-MM-YYYY" value="{{$SpecialHolidayData->sh_date_from}}"
                        required data-error="Select Date" disabled>
                </div>
                <div class="help-block with-errors is-invalid"></div>
            </div>
        </div>


        <div class="form-row align-items-center ">
            <label class="col-lg-3 input-title RequiredStar">Holiday Date To</label>
            <div class="col-lg-5 form-group">
                <div class="input-group ghdatepicker">
                    <div class="input-group-prepend ">
                        <span class="input-group-text ">
                            <i class="icon wb-calendar round" aria-hidden="true"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control round" id="dateSPHTo" name="sh_date_to"
                        data-plugin="datepicker" placeholder="DD-MM-YYYY" value="{{$SpecialHolidayData->sh_date_to}}"
                        required data-error="Select Date" disabled>
                </div>
                <div class="help-block with-errors is-invalid"></div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title">Description</label>
            <div class="col-lg-5 form-group">
                <div class="input-group ">
                    <textarea class="form-control round" id="textSPHDesc" name="sh_description" rows="2"
                        placeholder="Enter Description" required data-error="Enter Description"
                        readonly>{{$SpecialHolidayData->sh_description}}</textarea>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-9">
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

<!-- End Page -->

<script type="text/javascript">
// Disable radio button
$(':radio:not(:checked)').attr('disabled', true);
</script>

@endsection