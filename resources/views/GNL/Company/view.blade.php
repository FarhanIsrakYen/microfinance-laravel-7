@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;

$userInfo = Auth::user();
$userID = $userInfo->id;
$roleID = $userInfo->sys_user_role_id;
?>

<!-- Page -->
<div class="row">
    <div class="col-lg-9 offset-lg-3">

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Group</label>

            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <select class="form-control clsSelect2" disabled>
                        <option value="">Select One</option>
                        @foreach ($GroupData as $Row)
                        <option value="{{$Row->id}}"
                            {{ ($CompanyData->group_id == $Row->id) ? 'selected="selected"' : '' }}>
                            {{$Row->group_name}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="help-block with-errors is-invalid"></div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Company Name</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <input type="text" class="form-control round" readonly name="comp_name" id="txtCompanyName"
                        value="{{$CompanyData->comp_name}}" placeholder="Enter Company Name" required
                        data-error="Please enter Company name.">
                </div>
                <div class="help-block with-errors is-invalid"></div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Company Code</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <input type="text" name="comp_code" id="checkDuplicateCode" readonly class="form-control round"
                        placeholder="Enter Company Code" required data-error="Please enter company code."
                        value="{{$CompanyData->comp_code}}">
                </div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title">Company Phone</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <input type="number" class="form-control round" readonly name="comp_phone"
                        placeholder="Enter Company Phone" value="{{$CompanyData->comp_phone}}">
                </div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title">Email</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <input type="email" class="form-control round" readonly name="comp_email" id="txtCompanyEmail"
                        value="{{$CompanyData->comp_email}}" placeholder="Enter Company Email">
                </div>
                <div class="help-block with-errors is-invalid"></div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title">Address</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <textarea class="form-control round" name="comp_addr" disabled id="txtCompanyAddress" rows="2"
                        placeholder="Enter Address">{{$CompanyData->comp_addr}}</textarea>
                </div>
                <div class="help-block with-errors is-invalid"></div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title">Website</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <input type="url" class="form-control round" readonly name="comp_web_add" id="txtCompanyWeb"
                        placeholder="Example https://example.com" value="{{$CompanyData->comp_web_add}}">
                </div>
                <div class="help-block with-errors is-invalid"></div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Fiscal Year Start</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <select class="form-control clsSelect2" disabled>
                        <option value="">Select Start Fiscal Year</option>
                        <option value="01-01" @if($CompanyData->fy_start_date == '01-01')
                            {{ 'selected' }} @endif
                            >01-Jan
                        </option>
                        <option value="01-07" @if($CompanyData->fy_start_date == '01-07')
                            {{ 'selected' }} @endif>01-July
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Fiscal Year End</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <select class="form-control clsSelect2" disabled>
                        <option value="">Select End Fiscal Year</option>
                        <option value="31-12" @if($CompanyData->fy_end_date ==
                            '31-12'){{ 'selected' }} @endif>31-Dec
                        </option>
                        <option value="30-06" @if($CompanyData->fy_end_date ==
                            '30-06'){{ 'selected' }}@endif>30-June
                        </option>

                    </select>
                </div>
                <div class="help-block with-errors is-invalid"></div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title">Company logo</label>
            <div class="col-lg-2">
                @if(!empty($CompanyData->comp_logo))

                @if(file_exists($CompanyData->comp_logo))
                <img src="{{ asset($CompanyData->comp_logo) }}" style="width: 70px;">
                @endif
                @endif
            </div>
        </div>

        @if(Common::isSuperUser() == true)
                <div class="form-row align-items-center">
                    <label class="col-lg-12 input-title">
                        Module Selection
                    </label>
                    <div class="col-lg-12">
                        <div class="row">
                        <?php
                            $sysModules = Common::ViewTableOrder('gnl_sys_modules', 
                            [['is_active', 1], ['is_delete', 0]],
                            ['id', 'module_name', 'module_short_name'],
                            ['id', 'ASC']
                            );

                            $selecetedModule = explode(',', $CompanyData->module_arr);
                            $i = 0;
                            foreach($sysModules as $module){
                                
                                if (in_array($module->id, $selecetedModule)) {
                                    $CheckText = 'checked';
                                } else {
                                    $CheckText = '';
                                }

                                $i++;
                                ?>
                                <div class="col-lg-4">
                                    <div class="checkbox-custom checkbox-primary">
                                        <input type="checkbox" class="checkboxs" {{$CheckText}} name="module_arr[]" id="module_arr_{{$i}}" value="{{$module->id}}" />
                                        <label for="module_arr_{{$i}}" style="color:#000;">{{$module->module_name}}</label>
                                    </div>
                                </div>
                                <?php
                            }
                        ?>
                        </div>
                    </div>
                </div>
                @endif

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
<!-- End Page -->

@endsection