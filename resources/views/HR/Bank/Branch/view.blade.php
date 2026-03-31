@extends('Layouts.erp_master')
@section('content')

<?php 
use App\Services\CommonService as Common;
?>
<!-- Page -->
    <form enctype="multipart/form-data" method="post" data-toggle="validator" novalidate="true">
        @csrf
        <div class="row">
            <div class="col-lg-9 offset-lg-3">
                <div class="form-row align-items-center">
                   
                    <div class="col-lg-5 form-group">
                       
                    </div>
                </div>
               

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Bank Name</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                        <input type="text" class="form-control round" name="name" placeholder="Enter Bank Name"  disabled
                        value="{{$BankData->name}}" required data-error="Please enter Bank name."> 
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>


                <div class="row">
                    <div class="col-lg-9">
                        <div class="form-group d-flex justify-content-center">
                            <div class="example example-buttons">
                                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                                {{-- <button type="submit" class="btn btn-primary btn-round" >Save</button> --}}
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </form>
<!-- End Page -->

<script type="text/javascript">
$(document).ready(function () {        

    $('form').submit(function (event) {
        event.preventDefault();
        // $(this).find(':submit').attr('disabled', 'disabled');

        $.ajax({
                    url: "{{ url()->current() }}",
                    type: 'POST',
                    dataType: 'json',
                    contentType: false,
                    data: new FormData(this),
                    processData: false,
                })
                .done(function (response) {
                    if (response['alert-type'] == 'error') {
                        swal({
                            icon: 'error',
                            title: 'Oops...',
                            text: response['message'],
                        });
                        $('form').find(':submit').prop('disabled', false);
                    } else {
                        $('form').trigger("reset");
                        swal({
                            icon: 'success',
                            title: 'Success...',
                            text: response['message'],
                            timer: 2000,
                            showConfirmButton: false
                        }).then(function () {
                            window.location.href = "./../";
                        });
                    }

                })
                .fail(function () {
                    console.log("error");
                });
    });
});

</script>


@endsection
