@extends('Layouts.erp_master')
@section('content')
<style>
    .tooltip-icon{
        padding: 5px 10px;
        border-radius: 50%;
        background: #ccc;
        color: black;
        cursor: pointer;
    }
    .tooltip-text{
        background: rgba(0,0,0,0.3);
        width: 200px;
        color: white;
        padding: 10px;
        border-radius: 10px;
        position: absolute;
        z-index: 1;
        display: none;
    }
</style>
<!-- Page -->
<form enctype="multipart/form-data" method="post" class="form-horizontal" 
    data-toggle="validator" novalidate="true" autocomplete="off">
    @csrf
    <div class="row">
        <div class='mb-2 ml-auto'>
            <label> Edit Basic Info Only &nbsp; &nbsp;</label>
            <input type="checkbox" class='edit-basic' data-plugin="switchery" id="basic_info_button"  name="basicInfoEdit" onchange="basicInfoButtonToggle(this)">
            <span class="tooltip-icon"> ? </span>
            <div class="tooltip-text"> Only Name, Registration No, Max Member, Latitude, Longitude can be edited</div>
            
            {{-- <input type="checkbox"  data-plugin="switchery" checked /> --}}
        </div>
        
    </div>
    <div class="row">
        <div class="col-lg-6">
            <input type="hidden" name="samity_id" value="{{ encrypt($samity->id) }}">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Name</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round edit-basic" id="samity_name" name="samity_name"
                            placeholder="Enter Samity Name" required value="{{ $samity->name }}"
                            data-error="Please enter Samity Name.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Samity Code</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round edit-basic" id="samity_code" name="samity_code"
                            placeholder="Samity Code" value="{{ $samity->samityCode }}" readonly>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Working Area</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="working_area" required
                            data-error="Please Selct Working Area">
                            <option value="">Select</option>
                            @foreach ($workingAreas as $workingArea)
                            <option value="{{ $workingArea->id }}">{{ $workingArea->name }}</option>
                            @endforeach
                        </select>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Registration No</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round edit-basic" id="reg_no" name="reg_no"
                            value="{{ $samity->registrationNo }}" placeholder="Enter Registration No.">
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Field Officer</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="field_officer" required
                            data-error="Please Select Field Officer">
                            <option value="">Select</option>
                            @foreach ($filedOfficers as $filedOfficer)
                            <option value="{{$filedOfficer->id}}">{{$filedOfficer->name}}</option>
                            @endforeach
                        </select>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Samity Day</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="samity_day" required
                            data-error="Please Select A Day">
                            @foreach ($weekDays as $weekDay)
                            <option value="{{ $weekDay }}">{{ $weekDay }}</option>
                            @endforeach
                        </select>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Samity Time</label>
                <div class="col-lg-7">
                    <div class="input-group clockpicker">
                        <input type="text" id="samity_time" name="samity_time" class="form-control" value="{{ $samityTime }}">
                        <span class="input-group-addon">
                            <span class="glyphicon glyphicon-time"></span>
                        </span>
                    </div>
                </div>
            </div>

        </div>

        <div class="col-lg-6">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Samity Type</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="samity_type" required
                            data-error="Please Select Samity Type">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                            <option value="Both">Both</option>
                        </select>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Opening Date</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="icon wb-calendar round" aria-hidden="true"></i>
                            </span>
                        </div>
                        @if ($isOpening)
                        <input type="text" class="form-control round edit-basic" id="opening_date" name="opening_date"
                        autocomplete="off" placeholder="DD-MM-YYYY" value="{{$samityOpeningDate}}" required
                        data-error="Please Select A Date">
                        @else
                        <input type="text" class="form-control round edit-basic" id="opening_date" name="opening_date"
                            value="{{$samityOpeningDate}}" readonly autocomplete="off" placeholder="DD-MM-YYYY" required
                            data-error="Please Select A Date">
                        @endif
                        
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title" for="transferable">Is Tranferable : </label>
                <div class="col-lg-7">
                    <div class="input-group checkbox-custom checkbox-primary">
                        <input type="checkbox" name="transferable" id="transferable" value="1" @if($samity->isTransferable)
                        checked
                        @endif>
                        <label></label>
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title RequiredStar">Maximum Active Member</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round textNumber edit-basic" id="max_no" name="max_no"
                            placeholder="Enter Max Number" value="{{ $samity->maxActiveMember }}" required
                            data-error="Please Enter Max Member Number">
                    </div>
                    <div id="errmsg" class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Latitude</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round edit-basic" id="latitude" name="latitude"
                            value="{{ $samity->latitude }}" placeholder="Enter Latitude">
                    </div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label class="col-lg-4 input-title">Longitude</label>
                <div class="col-lg-7">
                    <div class="input-group">
                        <input type="text" class="form-control round edit-basic" id="longitude" name="longitude"
                            value="{{ $samity->longtitude }}" placeholder="Enter Longitude">
                    </div>
                </div>
            </div>

        </div>
        </div>
        <div class="row align-items-center">
            <div class="col-lg-12">
                <div class="form-group d-flex justify-content-center">
                    <div class="example example-buttons">
                        <a href="javascript:void(0)" onclick="goBack();"
                            class="btn btn-default btn-round d-print-none">Back</a>
                        <button type="submit" class="btn btn-primary btn-round">Update</button>
                    </div>
                </div>
            </div>
        </div>
</form>
<!-- End Page -->

<script type="text/javascript">
    // set the default values
$("[name=working_area]").val("{{ $samity->workingAreaId }}");
$("[name=field_officer]").val("{{ $samity->fieldOfficerEmpId }}");
$("[name=samity_day]").val("{{ $samity->samityDay }}");
$("[name=samity_type]").val("{{ $samity->samityType }}");

    jQuery(document).ready(function($) {

        $('form').submit(function (event) {
            event.preventDefault();

            //disable Multiple Click
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
                    });
                    setTimeout(function(){ window.location =  './../'}, 2000);
                }
                
            })
            .fail(function() {
                console.log("error");
            });
            
        });

        var isOpening = "{{ $isOpening }}";

        if(isOpening){
            var systemDate = new Date("{{ $sysDate }}");
            $('#opening_date').datepicker({
                dateFormat: 'dd-mm-yy',
                orientation: 'bottom',
                autoclose: true,
                todayHighlight: true,
                changeMonth: true,
                changeYear: true,
                yearRange: '1900:+10',
                maxDate : systemDate
            }).keydown(false);
        }

    });

    $('.tooltip-icon').on('mouseover', function(e){
        $('.tooltip-text').css('display','block');
    });
    $('.tooltip-icon').on('mouseout', function(e){
        $('.tooltip-text').css('display','none');
    });

    function basicInfoButtonToggle(ele){
        
        if($(ele).is(':checked')){
            $('input:not(.edit-basic)').attr('readonly', true);
            $('input:checkbox:not(.edit-basic)').prop('disabled', true);
            $('select').attr('disabled',true);
            $( "#samity_time" ).unbind();
        }
        else{
            $('input:not(.edit-basic)').attr('readonly', false);
            $('input:checkbox:not(.edit-basic)').prop('disabled', false);
            $('select').attr('disabled', false);
            //dont know how to rebind the click to #samity_time
        }
    }
    
</script>
@endsection