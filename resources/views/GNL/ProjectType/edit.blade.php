@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="POST" data-toggle="validator" novalidate="true">
    @csrf

    <div class="row">
        <div class="col-lg-9 offset-lg-3">

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">GROUP</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ">
                        <select class="form-control clsSelect2"

                                name="group_id" id="group_id"
                                required data-error="Please select group name."
                                onchange="fnAjaxSelectBox(
                                            'company_id',
                                            this.value,
                                '{{base64_encode('gnl_companies')}}',
                                '{{base64_encode('group_id')}}',
                                '{{base64_encode('id,comp_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                    );"
                                >

                            <option value="">Select One</option>
                            @foreach ($GroupData as $Row)
                            <option value="{{$Row->id}}" {{ ($ProjectTypeData->group_id == $Row->id) ? 'selected="selected"' : '' }} >{{$Row->group_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">COMPANY</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                                 name="company_id"
                                id="company_id"
                                required data-error="Please select Company name."
                                onchange="fnAjaxSelectBox(
                                            'project_id',
                                            this.value,
                                '{{base64_encode('gnl_projects')}}',
                                '{{base64_encode('company_id')}}',
                                '{{base64_encode('id,project_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                    );"
                                >
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Project</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select  class="form-control clsSelect2"
                                 required data-error="Please select Project name." name="project_id" id="project_id"
                                 onchange="fnAjaxSelectBox(
                                             'project_type_id',
                                             this.value,
                                 '{{base64_encode('gnl_project_types')}}',
                                 '{{base64_encode('project_id')}}',
                                 '{{base64_encode('id,project_type_name')}}',
                                 '{{url('/ajaxSelectBox')}}'
                                     );"
                                 >
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Project Type</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="textProjectType" name="project_type_name" value="{{$ProjectTypeData->project_type_name}}" placeholder="Enter Project Type" required data-error="Please enter project type .">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Project Type Code</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" name="project_type_code" id="checkDuplicateCode" value="{{$ProjectTypeData->project_type_code}}"
                        class="form-control round" placeholder="Enter Project Type Code" required data-error="Please enter project type code." 
                        onblur="fnCheckDuplicate(
                                '{{base64_encode('gnl_project_types')}}', 
                                this.name+'&&is_delete', 
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'txtCodeError', 
                                'project type code',
                                '{{$ProjectTypeData->id}}');">
                    </div>
                    <div class="help-block is-invalid" id="txtCodeError"></div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    $(document).ready(function(){
    fnAjaxSelectBox(
            'company_id',
            {{ $ProjectTypeData-> group_id }},
            '{{base64_encode('gnl_companies')}}',
            '{{base64_encode('group_id')}}',
            '{{base64_encode('id,comp_name')}}',
            '{{url('/ajaxSelectBox')}}',
            {{ $ProjectTypeData->company_id}}
            );
    fnAjaxSelectBox(
            'project_id',
            {{ $ProjectTypeData-> company_id }},
            '{{base64_encode('gnl_projects')}}',
            '{{base64_encode('company_id')}}',
            '{{base64_encode('id,project_name')}}',
            '{{url('/ajaxSelectBox')}}',
            {{ $ProjectTypeData->project_id}}
            );
            $('form').submit(function (event) {
                // event.preventDefault();
                $(this).find(':submit').attr('disabled', 'disabled');
                // $(this).submit();
            });
    });
</script>
@endsection
