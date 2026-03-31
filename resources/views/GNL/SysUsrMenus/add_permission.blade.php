@extends('Layouts.erp_master')
@section('content')

<form method="post" data-toggle="validator" novalidate="true">
    @csrf
    <div class="row">
        <div class="col-lg-9 offset-lg-3">
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="ParentName">Permission Type</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="set_status" id="select_name">
                            <option value="">Select One</option>
                            <option value="1">Add</option>
                            <option value="2">Edit</option>
                            <option value="3">View</option>
                            <option value="4">Publish</option>
                            <option value="5">Unpublish</option>
                            <option value="6">Delete</option>
                            <option value="7">Approve</option>
                            <option value="8">All Data</option>
                            <option value="9">Change Password</option>
                            <option value="10">Permission</option>
                            <option value="11">Print</option>
                            <option value="12">print pdf</option>
                            <option value="13">Force Delete</option>
                            <option value="14">Permission Folder</option>
                            <option value="15">Execute</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="roleName">Permission Name</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter Menu Name" name="name" id="RoleName" required data-error="Please enter menu name.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Route Link</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter Route Link" name="route_link" id="route_link" required data-error="Please enter Route Link." >
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Page Title</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ">
                        <input type="text" class="form-control round" id="page_title" name="page_title">
                    </div>
                </div>
            </div>

            <!-- <div class="form-row align-items-center">
                <label class="col-lg-3 input-title" for="roleName">Method Name</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter Method Name" name="method_name" id="RoleName" data-error="Please enter Method name.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                    @error('method_name')
                    <div class="help-block with-errors is-invalid">{{ $message }}</div>
                    @enderror
                </div>
            </div> -->

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title" for="groupName">Order By</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ">
                        <input type="number" class="form-control round" id="OrderBy" name="order_by" placeholder="Enter Order">
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <div class="col-lg-6">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round" id="validateButton2">Save</button>
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