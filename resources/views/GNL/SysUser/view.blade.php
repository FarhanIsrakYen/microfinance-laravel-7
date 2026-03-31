@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\HtmlService as HTML;
?>

<div class="row">
    <div class="col-lg-8 offset-lg-3">
        <!-- Html View Load  -->
        {!! HTML::forCompanyFeild($suser->company_id, 'disabled', true) !!}
    </div>
</div>
<div class="row">
    <div class="col-lg-8 offset-lg-3">
        {!! HTML::forBranchFeild(true,'','',$suser->branch_id, 'disabled') !!}
    </div>
</div>

<div class="row">

    <div class="col-lg-8 offset-lg-3">
        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">User Role</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <select class="form-control clsSelect2" disabled>
                        <option value="">Select One</option>
                        @foreach($userRole as $role)
                        <option value="{{ $role->id }}" <?php if ($role->id == $suser->sys_user_role_id) {
                                    echo "selected";
                                }?>>{{ $role->role_name }}</option>
                        @endforeach
                    </select>

                </div>
            </div>
        </div>

        <div class="form-row form-group align-items-center">
            <label class="col-lg-3 input-title">Employee</label>
            <div class="col-lg-5">
                <div class="input-group">
                    <select class="form-control clsSelect2" disabled>
                        <option value="">Select One</option>
                        @foreach ($EmployeeData as $Row)
                        <option value="{{$Row->employee_no}}" <?php if ($Row->employee_no == $suser->employee_id) {
                                    echo "selected";
                                }?>> >{{sprintf("%04d", $Row->emp_code)." - ".$Row->emp_name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Full Name</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <input type="text" class="form-control round" value="{{ $suser->full_name }}"
                        readonly>
                </div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title RequiredStar">Username</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <input type="text" class="form-control round" value="{{ $suser->username }}"
                        readonly>
                </div>
                <div class="help-block with-errors is-invalid"></div>
            </div>
        </div>


        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title" for="groupName">Email</label>
            <div class="col-lg-5 form-group">
                <div class="input-group">
                    <input type="email" class="form-control round" value="{{ $suser->email }}" readonly>
                </div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title" for="groupName">Contact No</label>
            <div class="col-lg-5 form-group">
                <div class="input-group ">
                    <input type="number" class="form-control round" value="{{ $suser->contact_no }}"
                        readonly>
                </div>
            </div>
        </div>

        <!-- <div class="form-row align-items-center">
                <label class="col-lg-3 input-title" for="groupName">Designation</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="designation" name="designation" placeholder="Enter Designation" value="{{ $suser->designation }}" readonly>
                        
                    </div>
                </div>
            </div> -->

        <!-- <div class="form-row align-items-center">
                <label class="col-lg-3 input-title" for="groupName">Department</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" id="department" name="department" placeholder="Enter Department" value="{{ $suser->department }}" readonly>
                        
                    </div>
                </div>
            </div> -->

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title" for="groupName">User Image</label>
            <div class="col-lg-5 form-group">
                <div class="input-group input-group-file" data-plugin="inputGroupFile">
                    <input type="text" class="form-control round" readonly="">
                    <div class="input-group-append">
                        <span class="btn btn-success btn-file">
                            <i class="icon wb-upload" aria-hidden="true"></i>
                            <input type="file" id="userImage" name="user_image_url" readonly>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <label class="col-lg-3 input-title" for="groupName">User Signature</label>
            <div class="col-lg-5 form-group">
                <div class="input-group input-group-file" data-plugin="inputGroupFile">
                    <input type="text" class="form-control round" readonly="">
                    <div class="input-group-append">
                        <span class="btn btn-success btn-file">
                            <i class="icon wb-upload" aria-hidden="true"></i>
                            <input type="file" id="userSignature" name="signature_image_url"
                                readonly="">

                        </span>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-row align-items-center">
            <div class="col-lg-8">
                <div class="form-group d-flex justify-content-center">
                    <div class="example example-buttons">
                        <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>

@endsection