@extends('Layouts.erp_master')
@section('content')

<!-- Page -->
    <form enctype="multipart/form-data" method="post" data-toggle="validator" novalidate="true">
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
                                <option value="{{$Row->id}}" {{ ($ProjectData->group_id == $Row->id) ? 'selected="selected"' : '' }} >{{$Row->group_name}}</option>
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
                             name="company_id" id="company_id"
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
                    <label class="col-lg-3 input-title RequiredStar">PROJECT NAME</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter village Name" name="project_name" value="{{$ProjectData->project_name}}" id="project_name" required data-error="Please enter Project name.">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Project Code</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <input type="text" name="project_code" id="checkDuplicateCode" value="{{$ProjectData->project_code}}"
                            class="form-control round" placeholder="Enter Project Code" required data-error="Please enter project code." 
                            onblur="fnCheckDuplicate(
                                '{{base64_encode('gnl_projects')}}', 
                                this.name+'&&is_delete', 
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'txtCodeError', 
                                'project code',
                                '{{$ProjectData->id}}');">
                            </div>
                        <div class="help-block with-errors is-invalid" id="txtCodeError"></div> 
                        @error('comp_code')
                            <div class="help-block with-errors is-invalid">{{ $message }}</div>
                        @enderror
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
<!-- End Page -->
<script>
  $(document).ready(function(){
    fnAjaxSelectBox(
        'company_id',
        {{ $ProjectData->group_id }},
        '{{base64_encode("gnl_companies")}}',
        '{{base64_encode("group_id")}}',
        '{{base64_encode("id,comp_name")}}',
        '{{url("/ajaxSelectBox")}}',
        '{{ $ProjectData->company_id}}'
        );

        $('form').submit(function (event) {
            // event.preventDefault();
            $(this).find(':submit').attr('disabled', 'disabled');
            // $(this).submit();
        });
});
</script>


@endsection
