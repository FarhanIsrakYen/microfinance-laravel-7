@extends('Layouts.erp_master')
@section('content')

<form enctype="multipart/form-data" method="post" class="form-horizontal" 
    data-toggle="validator" novalidate="true" autocomplete="off"> 
    @csrf                          
    <div class="row">
        <div class="col-lg-8 offset-lg-3">

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Name</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="text" name="name" id="name" class="form-control round"
                        placeholder="Enter Name" required data-error="Please Enter Name">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Branch</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="branchId" 
                        id="branchId" required data-error="Please Select Branch">
                            <option value="">Select</option>
                            @foreach($branchList as $branch)
                                <option value="{{ $branch->id }}">{{ $branch->branch_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Division</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="division" 
                        id="division" required data-error="Please Select Division">
                            <option value="">Select</option>
                            @foreach($divisions as $divisions)
                                <option value="{{ $divisions->id }}">{{ $divisions->division_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">District</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="district" 
                        id="district" required data-error="Please Select District">
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Thana/Upazila</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="upazila" 
                        id="upazila" required data-error="Please Select Thana/Upazila">
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Union</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="union" 
                        id="union" required data-error="Please Select Union">
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row form-group align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Village</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="villageId" 
                        id="villageId" required data-error="Please Select Village">
                            <option value="">Select</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
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
    
<script type="text/javascript">
    jQuery(document).ready(function($) {

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
                            window.location.href = "{{url('mfn/workingarea')}}"; 
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

        /* get districts */
        $('#division').change(function (e) { 
            e.preventDefault();
            $("#district option:gt(0),#upazila option:gt(0),#union option:gt(0),#villageId option:gt(0)").remove();

            var divisionId = $(this).val();

            if (divisionId == '') {
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./../getDistricts",
                data: {divisionId : divisionId},
                dataType: "json",
                success: function (districts) {
                    $.each(districts, function (index, value) { 
                        $("#district").append("<option value='"+index+"'>"+value+"</option>");
                    });
                    
                },
                error: function(){
                    alert('error!');
                }
            });
            
        });
        /* end getting districts */

        /* get upazilas */
        $('#district').change(function (e) { 
            e.preventDefault();
            $("#upazila option:gt(0),#union option:gt(0),#villageId option:gt(0)").remove();

            var districtId = $(this).val();

            if (districtId == '') {
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./../getUpazilas",
                data: {districtId : districtId},
                dataType: "json",
                success: function (districts) {
                    $.each(districts, function (index, value) { 
                        $("#upazila").append("<option value='"+index+"'>"+value+"</option>");
                    });
                    
                },
                error: function(){
                    alert('error!');
                }
            });
            
        });
        /* end getting upazilas */

        /* get unions */
        $('#upazila').change(function (e) { 
            e.preventDefault();
            $("#union option:gt(0),#villageId option:gt(0)").remove();

            var upazilaId = $(this).val();

            if (upazilaId == '') {
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./../getUnions",
                data: {upazilaId : upazilaId},
                dataType: "json",
                success: function (districts) {
                    $.each(districts, function (index, value) { 
                        $("#union").append("<option value='"+index+"'>"+value+"</option>");
                    });
                    
                },
                error: function(){
                    alert('error!');
                }
            });
            
        });
        /* end getting unions */

        /* get villages */
        $('#union').change(function (e) { 
            e.preventDefault();
            $("#villageId option:gt(0)").remove();

            var unionId = $(this).val();

            if (unionId == '') {
                return false;
            }

            $.ajax({
                type: "POST",
                url: "./../getVillages",
                data: {unionId : unionId},
                dataType: "json",
                success: function (districts) {
                    $.each(districts, function (index, value) { 
                        $("#villageId").append("<option value='"+index+"'>"+value+"</option>");
                    });
                    
                },
                error: function(){
                    alert('error!');
                }
            });
            
        });
        /* end getting unions */

    });
    
</script>

@endsection
