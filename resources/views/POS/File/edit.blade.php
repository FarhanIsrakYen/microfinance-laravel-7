@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" data-toggle="validator" novalidate="true">
    @csrf
    <div class="row">
        <div class="col-lg-9 offset-lg-3">
            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">File Name</label>
                <div class="col-lg-5">
                    <div class="input-group ">
                        <input type="text" class="form-control round" placeholder="Enter File Name"
                            value="{{$FileData->file_name}}" name="file_name" required
                            data-error="Please enter file name."
                            onblur="fnCheckDuplicate(
                                '{{base64_encode('gnl_files')}}', 
                                this.name+'&&is_delete', 
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'txtCodeError', 
                                'file name',
                                '{{$FileData->id}}');">
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
                                <?php
                                if(!empty($FileData->file_url)){
                                    ?>
                                    <input type="file" id="fileUpload" name="file_url" onchange="validate_fileupload(this.id);">
                                    <?php
                                }
                                else{
                                    ?>
                                    <input type="file" id="fileUpload" name="file_url" require onchange="validate_fileupload(this.id);"> 
                                    <?php
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                    <span style="font-size: 14px; color: green;">(Maximum file size 500 Mb)</span>
                </div>
                <div class="col-lg-4">
                    @if(!empty($FileData->file_url))

                    @if(file_exists($FileData->file_url))
                        <a href="{{ asset($FileData->file_url) }}" target="_blank">
                            <i class="fa fa-eye" aria-hidden="true" style="font-size:18px;"></i>
                        </a>
                        &ensp;
                        <!-- &emsp; -->
                        <!-- &nbsp; -->
                        <a href="{{ asset($FileData->file_url) }}" download>
                            <i class="fa fa-download" aria-hidden="true" style="font-size:18px;"></i>
                        </a>
                    @endif
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round"
                                id="submitButtonforGroup">Update</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Page -->
<script type="text/javascript">
$('form').submit(function(event) {
    // event.preventDefault();
    $(this).find(':submit').attr('disabled', 'disabled');
    // $(this).submit();
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
}
</script>
@endsection