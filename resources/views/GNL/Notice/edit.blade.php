@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" 
    data-toggle="validator" novalidate="true" autocomplete="off"> 
    @csrf                          
    <div class="row">
        <div class="col-lg-8 offset-lg-3">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Title</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" name="notice_title" id="notice_title" class="form-control round"
                        placeholder="Enter Title" required data-error="Please Enter Title" value="{{$noticeData->notice_title}}" />
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Notice Period</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="notice_period" 
                            id="notice_period" required data-error="Please Select Status">
                            <option value="1" {{ ($noticeData->notice_period == 1) ? 'selected' : '' }} >Infinity</option>
                            <option value="2" {{ ($noticeData->notice_period == 2) ? 'selected' : '' }} >Set Time</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div style="display:none;" id="periodDiv">

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Start Time</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <input type="text" class="form-control round" name="start_time" id="start_time" value="{{$noticeData->start_time}}">
                        </div>
                    </div>
                </div>

                <div class="form-row form-group align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">End Time</label>
                    <div class="col-lg-5">
                        <div class="input-group">
                            <input  type="text" class="form-control round" id="end_time" name="end_time" value="{{$noticeData->end_time}}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Notice</label>
                <div class="col-lg-8">
                    <div class="input-group">
                        <textarea type="text" name="notice_body" id="notice_body" class="form-control round"
                        placeholder="Enter Notice" required data-error="Please Enter Notice">{{$noticeData->notice_body}}</textarea> 
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">

                <label class="col-lg-3 input-title RequiredStar">Branch</label>

                <div class="col-lg-9 form-group">

                        <?php
                            $selBranch = explode(',',$noticeData->branch_id);
                        ?>
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="input-group checkbox-custom checkbox-primary">
                                    <input type="checkbox" name="branchId[]" id="branch_0" 
                                    value="0" {{ (in_array(0, $selBranch) ) ? 'checked' : '' }} >
                                    <label for="branch_0"><strong>All Branch</strong></label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        @foreach($branchList as $branch)
                        <?php
                            $checkText = ( in_array($branch->id, $selBranch) ) ? 'checked' : '';
                        ?>
                        <div class="col-lg-4">
                            <div class="input-group checkbox-custom checkbox-primary">
                                    <input type="checkbox" class="branch_cls" name="branchId[]" id="branch_{{ $branch->id }}" value="{{ $branch->id }}" {{ $checkText }}>
                                    <label for="branch_{{ $branch->id }}">
                                        {{ $branch->branch_name. " (".$branch->branch_code.")"}}
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
                            <button type="submit" class="btn btn-primary btn-round" id="submitBtn">Update</button>
                            <!-- <a href="#"><button type="button" class="btn btn-warning btn-round">Next</button></a> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<link rel="stylesheet" type="text/css" href="{{asset('assets/css-js/datetimepicker-master/jquery.datetimepicker.css')}}">
<script src="{{asset('assets/css-js/datetimepicker-master/build/jquery.datetimepicker.full.min.js')}}"></script>

<script type="text/javascript">

    $(document).ready(function($) {

        $('#start_time').datetimepicker({
            format:'Y-m-d H:i',
            autoclose: true,
        }).keydown(false);

        $('#end_time').datetimepicker({
            format:'Y-m-d H:i',
            autoclose: true,
        }).keydown(false);

        var notice_period = $('#notice_period').val();
        if (notice_period == 2) {
            $('#periodDiv').show('slow');
        }
        else
        {
            $('#periodDiv').hide('slow'); 
        }

        $('#notice_period').change(function(){
            notice_period = $(this).val();
            if (notice_period == 2) {
                $('#periodDiv').show('slow');
            }
            else
            {
                $('#periodDiv').hide('slow'); 
            }
        });

        /////////////////////////
        $('#branch_0').click(function() {
            if ($('#branch_0').is(':checked')) {
                $('.branch_cls').each(function() {
                    $(this).prop("checked", true);
                });
            } else {
                $('.branch_cls').each(function() {
                    $(this).prop("checked", false);
                });
            }
        });

        $('.branch_cls').click(function() {
            var flag = false;

            $('.branch_cls').each(function() {
                if($(this).is(':checked') === false){
                    flag = true;
                }
            });

            if(flag){
                $('#branch_0').prop("checked", false);
            }
            else{
                $('#branch_0').prop("checked", true);
            }
        });


        $('form').submit(function (event) {
            //disable Multiple Click
            event.preventDefault();
            // $(this).find(':submit').attr('disabled', 'disabled');
            $('#submitBtn').prop('disabled', true);

            $.ajax({
                url: "{{ url()->current() }}",
                type: 'POST',
                dataType: 'json',
                data: $('form').serialize(),
            })
            .done(function(response) {
                $('#submitBtn').prop('disabled', false);
                if (response['alert-type']=='error') {
                    const wrapper = document.createElement('div');
                    wrapper.innerHTML = response['message'];
                    
                    swal({
                        icon: 'error',
                        title: 'Oops...',
                        content: wrapper,
                    });

                    // $('form').find(':submit').prop('disabled', false);
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
                            window.location.href = "{{url('gnl/notice')}}"; 
                        });
                    }
                
                })
            .fail(function() {
                $('#submitBtn').prop('disabled', false);
                console.log("error");
            })
            .always(function() {
                $('#submitBtn').prop('disabled', false);
                console.log("complete");
            });
            
        });

    });
    
</script>

@endsection
