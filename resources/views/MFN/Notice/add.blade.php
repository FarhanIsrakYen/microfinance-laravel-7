@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" 
    data-toggle="validator" novalidate="true" autocomplete="off"> 
    @csrf                          
    <div class="row">
        <div class="col-lg-8 offset-lg-3">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Notice Title</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <textarea type="text" name="name" id="name" class="form-control round"
                        placeholder="Enter Notice Title" required data-error="Please Enter Notice Title"></textarea> 
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Active Till</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="active_till" 
                        id="active_till" required data-error="Please Select Status">
                            <option value="1">Infinity</option>
                            <option value="2">Set Time</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center" style="display: none" id="periodDiv">
                <label class="col-lg-3 input-title RequiredStar">Notice Period</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input id="noticePeriod" type="text" class="form-control round" name="noticePeriod">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Branch</label>
                <div class="col-lg-5 form-group">
                    <div class="row">
                        @foreach($branchList as $branch)
                        <div class="col-lg-3">
                            <div class="input-group checkbox-custom checkbox-primary">
                                    <input type="checkbox" name="branchId[]" id="branch_{{ $branch->id }}" 
                                        value="{{ $branch->id }}">
                                    <label for="branch_{{ $branch->id }}">
                                        {{ sprintf("%4d", $branch->branch_code).' - '.$branch->branch_name}}
                                    </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            
            <div class="form-row align-items-center">
                <div class="col-lg-2"></div>
                <div class="col-lg-5">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Save</button>
                            <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<link rel="stylesheet" type="text/css" href="./../../../ext-plugin/datetimepicker-master/jquery.datetimepicker.css"/ >
<script src="./../../../ext-plugin/datetimepicker-master/build/jquery.datetimepicker.full.min.js"></script>

<script type="text/javascript">
    jQuery(document).ready(function($) {

        $('#noticePeriod').datetimepicker({
            format:'Y-m-d H:i',
            autoclose: true,
        }).keydown(false);

        var active_till = $('#active_till').val();
        $('#active_till').change(function(){
            active_till = $(this).val();
            if (active_till == 2) {
                $('#periodDiv').show('slow');
            }
            else 
               $('#periodDiv').hide('slow'); 
        });

        $('form').submit(function (event) {
            //disable Multiple Click
            event.preventDefault();
            $(this).find(':submit').attr('disabled', 'disabled');

            $.ajax({
                url: "{{ url()->current() }}",
                type: 'POST',
                dataType: 'json',
                data: $('form').serialize(),
            })
            .done(function(response) {
                if (response['alert-type']=='error') {
                    swal({
                        icon: 'error',
                        title: 'Oops...',
                        text: response['message'],
                    });
                    $('form').find(':submit').prop('disabled', false);
                }
                else{
                    $('form').trigger("reset");
                    swal({
                        icon: 'success',
                        title: 'Success...',
                        text: response['message'],
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function () {
                            window.location.href = "{{url('mfn/notice')}}"; 
                        });
                    }
                
                })
            .fail(function() {
                console.log("error");
            })
            .always(function() {
                console.log("complete");
            });
            
        });

    });
    
</script>

@endsection
