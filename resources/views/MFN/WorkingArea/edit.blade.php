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
                        placeholder="Enter Name" value="{{ $workingAreaList->name }}"
                        required data-error="Please Enter Name">
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
                                <option value="{{ $branch->id }}" {{ $branch->id == $workingAreaList->branchId ? 'selected' : '' }}>
                                    {{ $branch->branch_name }}</option>
                            @endforeach
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
                            @foreach($villageList as $village)
                                <option value="{{ $village->id }}" {{ $village->id == $workingAreaList->villageId ? 'selected' : '' }}>
                                    {{ $village->village_name }}</option>
                            @endforeach
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
                            <button type="submit" class="btn btn-primary btn-round">Update</button>
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

    });
    
</script>

@endsection
