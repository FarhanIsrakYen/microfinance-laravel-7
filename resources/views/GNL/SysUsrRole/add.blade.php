@extends('Layouts.erp_master')
@section('content')

<!-- Page -->
<form method="post" data-toggle="validator" novalidate="true">
    @csrf
    <div class="row">
        <div class="col-lg-9 offset-lg-3">

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title" for="ParentName">Parent Role</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="parent_id" id="ParentID">
                            <option value="<?=$roleID?>">Select One</option>
                            @foreach($parent_role as $prole)
                                <option @if($prole->id == $roleID) {{ 'selected' }} @endif 
                                    value="{{ $prole->id }}">{{ $prole->role_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="roleName">Role Name</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter Role Name" name="role_name" id="txt_role_name" required data-error="Please enter role name.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                    @error('role_name')
                        <div class="help-block with-errors is-invalid">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title" for="groupName">Order By</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ">
                        <input type="number" class="form-control round" id="num_order_by" name="order_by" placeholder="Enter Order">
                    </div>
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