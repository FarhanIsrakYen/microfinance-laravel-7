@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>

<form method="post" enctype="multipart/form-data" data-toggle="validator" novalidate="true">
    @csrf

    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            <!-- Html View Load  -->
            {!! HTML::forCompanyFeild($suser->company_id, '', true) !!}
        </div>
    </div>
    <div class="row">
        <div class="col-lg-8 offset-lg-3">
            {!! HTML::forBranchFeild(true,'branch_id','branch_id',$suser->branch_id) !!}
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8 offset-lg-3">

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="ParentName">User Role</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="sys_user_role_id" id="userRole" required
                            data-error="Select Role">
                            <option value="<?=$roleID?>">Select One</option>
                            @foreach($user_roles as $role)
                            <option value="{{ $role->id }}" <?php if ($role->id == $suser->sys_user_role_id) {
    echo "selected";
}?>>{{ $role->role_name }}</option>
                            @endforeach
                        </select>

                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title">Employee</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="employee_id" id="employee_id">
                            <option value="" selected="selected">Select One</option>
                            @foreach ($EmployeeData as $Row)
                            <option value="{{ $Row->employee_no }}"
                                {{ $Row->employee_no == $suser->employee_id ? 'selected' : '' }}>
                                {{sprintf("%04d", $Row->emp_code)." - ".$Row->emp_name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="roleName">Full Name</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter Name" name="full_name"
                            id="fullName" required data-error="Please enter name." value="{{ $suser->full_name }}">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="roleName">Username</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter username" name="username"
                            id="username" value="{{ $suser->username }}" readonly>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>


            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Email</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="email" class="form-control round" id="email" name="email" placeholder="Enter Email"
                            data-error="Please enter email." value="{{ $suser->email }}">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Contact No</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ">
                        <input type="number" class="form-control round" id="contactNo" name="contact_no"
                            placeholder="Enter Phone Number" value="{{ $suser->contact_no }}">

                    </div>
                </div>
            </div>

            <!--                            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Designation</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="designation" name="designation" placeholder="Enter Designation" value="{{ $suser->designation }}">
                    </div>
                </div>
            </div>-->

            <!--                            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Department</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="department" name="department" placeholder="Enter Department" value="{{ $suser->department }}">
                    </div>
                </div>
            </div>-->

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">User Image</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                        <input type="text" class="form-control round" readonly="">
                        <div class="input-group-append">
                            <span class="btn btn-success btn-file">
                                <i class="icon wb-upload" aria-hidden="true"></i>
                                <input type="file" id="userImage" name="user_image" onchange="validate_fileupload(this.id);">
                            </span>
                        </div>
                    </div>
                    <span style="font-size: 14px; color: green;">(Maximum file size 1 Mb)</span>
                </div>

                <div class="col-lg-2">
                    @if(!empty($suser->user_image))

                    @if(file_exists($suser->user_image))
                    <img src="{{ asset($suser->user_image) }}" style="width: 70px;">
                    @endif
                    @endif
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">User Signature</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                        <input type="text" class="form-control round" readonly="">
                        <div class="input-group-append">
                            <span class="btn btn-success btn-file">
                                <i class="icon wb-upload" aria-hidden="true"></i>
                                <input type="file" id="userSignature" name="signature_image" onchange="validate_fileupload(this.id);">
                            </span>
                        </div>
                    </div>
                    <span style="font-size: 14px; color: green;">(Maximum file size 1 Mb)</span>
                </div>
                <div class="col-lg-2">
                    @if(!empty($suser->signature_image))

                    @if(file_exists($suser->signature_image))
                    <img src="{{ asset($suser->signature_image) }}" style="width: 70px;">
                    @endif
                    @endif
                </div>
            </div>

            <div class="form-row align-items-center">
                <div class="col-lg-8">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Update</button>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</form>

<script type="text/javascript">
$('form').submit(function(event) {
    $(this).find(':submit').attr('disabled', 'disabled');
});

// $(document).ready(function() {


// });

$('#branch_id').change(function(){

    fnAjaxSelectBox(
        'employee_id',
        $('#branch_id').val(),
        '{{base64_encode("hr_employees")}}',
        '{{base64_encode("branch_id")}}',
        '{{base64_encode("employee_no,emp_name")}}',
        '{{url("/ajaxSelectBox")}}',
        '{{$suser->employee_id}}'
    );

});

function validate_fileupload(id) {
	var myFile = $('#' + id).prop('files');
	var filetype = myFile[0].type;
	var filesize = myFile[0].size / (1024 * 1024);  // in mb

	var errorFlag = false;

	if(filesize > 1){
		errorFlag = true;
	}

	if(filetype == 'image/jpeg' 
		|| filetype == 'image/jpg' 
		|| filetype == 'image/png' 
		|| filetype == 'image/bmp' 
		|| filetype == 'image/gif')
	{
		errorFlag = false;
	}
	else{
		errorFlag = true;
	}
	
	if(errorFlag === true){
		$('#' + id).val('');
		swal({
			icon: 'error',
			title: 'Error',
			text: 'File size must be equal or less than 1 mb & file type is image. !!',
		});
	}
}
</script>

@endsection