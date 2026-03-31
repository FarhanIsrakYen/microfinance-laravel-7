@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator"
    novalidate="true" autocomplete="off">
    @csrf
    <div class="row">
        <div class="col-lg-9 offset-lg-3">
            <div class="form-row form-group align-items-center">
                <label for="textGroupName" class="col-lg-3 input-title RequiredStar">Group Name</label>
                <div class="col-lg-5">
                    <div class="input-group ">
                        <input type="text" class="form-control round" placeholder="Enter Group Name"
                            name="group_name" id="textGroupName" required
                            data-error="Please enter Group name."
                            onblur="fnCheckDuplicate(
                                '{{base64_encode('gnl_groups')}}', 
                                this.name+'&&is_delete', 
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'txtCodeError', 
                                'group name');"
                            >
                    </div>
                    <div class="help-block with-errors is-invalid" id="txtCodeError"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label for="textGroupName" class="col-lg-3 input-title">Group Email</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="email" class="form-control round" name="group_email"
                            id="GroupEmail" placeholder="Enter Group Email">

                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label for="textGroupName" class="col-lg-3 input-title RequiredStar">Mobile</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber"
                            name="group_phone" id="textGroupPhone" placeholder="Mobile Number (01*********)"
                            required data-error="Please enter mobile number (01*********)" minlength="11" maxlength="11"
                            onblur="fnCheckDuplicate(
                                '{{base64_encode('gnl_groups')}}',
                                this.name+'&&is_delete',
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'errMsgPhone',
                                'mobile number');">
                    </div>
                    <div class="help-block with-errors is-invalid" id="errMsgPhone"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label for="textGroupName" class="col-lg-3 input-title">Address</label>
                <div class="col-lg-5">
                    <textarea class="form-control round" name="group_addr" id="GroupAddress" rows="2"
                        placeholder="Enter Address"></textarea>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label for="textGroupName" class="col-lg-3 input-title">Website</label>
                <div class="col-lg-5">
                    <input type="text" class="form-control round" id="group_web_add"
                        name="group_web_add" placeholder="Enter Website">
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Group logo</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                        <input type="text" class="form-control round" readonly="">
                        <div class="input-group-append">
                            <span class="btn btn-success btn-file" style="height: 30px">
                                <i class="icon wb-upload" aria-hidden="true"></i>
                                <input type="file" id="groupimage" name="group_logo" onchange="validate_fileupload(this.id);">
                            </span>
                        </div>
                    </div>
                    <span style="font-size: 14px; color: green;">(Maximum file size 1 Mb)</span>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round"
                                id="submitButtonforGroup">Save</button>
                            <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
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