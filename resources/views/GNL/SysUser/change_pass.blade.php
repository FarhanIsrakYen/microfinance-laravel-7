@extends('Layouts.erp_master')
@section('content')

<form method="post" enctype="multipart/form-data" data-toggle="validator" novalidate="true">
	@csrf
    <div class="row">
        <div class="col-lg-9 offset-lg-3">
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="roleName">Password</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="password" class="form-control round" placeholder="Enter Password" name="password" id="password" required data-error="Please Password">
                        
                    </div>
                    @error('password')
                        <div class="help-block with-errors is-invalid">{{ $message }}</div>
                    @enderror
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="roleName">Confirm Password</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="password" class="form-control round" placeholder="Enter Password" name="conf_password" id="password" required data-error="Please Password">
                        
                    </div>
                    @error('password')
                        <div class="help-block with-errors is-invalid">{{ $message }}</div>
                    @enderror
                    <div class="help-block with-errors is-invalid"></div>
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