@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator"
    novalidate="true">
    @csrf
    <div class="row">
        <div class="col-lg-9 offset-lg-3">
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">File Name</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter File Name"
                            name="file_name" required
                            data-error="Please enter file name."
                            onblur="fnCheckDuplicate(
                                '{{base64_encode('gnl_files')}}', 
                                this.name+'&&is_delete', 
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'txtCodeError', 
                                'file name');">
                    </div>
                    <div class="help-block with-errors is-invalid" id="txtCodeError"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">File</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group input-group-file" data-plugin="inputGroupFile">
                        <input type="text" class="form-control round" readonly="">
                        <div class="input-group-append">
                            <span class="btn btn-success btn-file" style="height: 30px">
                                <i class="icon wb-upload" aria-hidden="true"></i>
                                <input type="file" id="fileUpload" name="file_url" require 
                                onchange="validate_fileupload(this.id);">
                            </span>
                        </div>
                    </div>
                    <span style="font-size: 14px; color: green;">(Maximum file size 500 Mb)</span>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round"
                                id="submitButton">Save</button>
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

        if(filesize > 500){
            errorFlag = true;
        }

        // if(filetype == 'image/jpeg' 
        //     || filetype == 'image/png' 
        //     || filetype == 'image/bmp' 
        //     || filetype == 'image/gif')
        // {
        //     errorFlag = false;
        // }
        // else{
        //     errorFlag = true;
        // }


        if(errorFlag === true){
            $('#' + id).val('');
            swal({
                icon: 'error',
                title: 'Error',
                text: 'File size must be equal or less than 500 mb !!',
            });
        }

            // (filesize <= 500) 
            //     // && (filetype == 'image/jpeg' 
            //     // || filetype == 'image/png' 
            //     // || filetype == 'image/bmp' 
            //     // || filetype == 'image/gif' 
            //     // || filetype == 'application/pdf' 
            //     // || filetype == 'application/msword' 
            //     // || filetype == 'application/x-excel' 
            //     // || filetype == 'application/x-msexcel' 
            //     // || filetype == 'application/excel')
            // )
        
    }
</script>
@endsection