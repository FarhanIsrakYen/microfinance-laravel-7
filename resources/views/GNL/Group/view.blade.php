@extends('Layouts.erp_master')
@section('content')

    <div class="row">
        <div class="col-lg-9 offset-lg-3">
            <div class="form-row form-group align-items-center">
                <label for="textGroupName" class="col-lg-3 input-title RequiredStar">Group Name</label>
                <div class="col-lg-5">
                    <div class="input-group ">
                        <input type="text" class="form-control round" placeholder="Enter Group Name"
                            value="{{$GroupData->group_name}}" name="group_name" id="textGroupName" required
                            data-error="Please enter Group name." readonly>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label for="textGroupName" class="col-lg-3 input-title">Group Email</label>
                <div class="col-lg-5">
                    <div class="input-group">
                        <input type="email" class="form-control round" name="group_email"
                            value="{{$GroupData->group_email}}" id="GroupEmail"
                            placeholder="Enter Group Email" readonly>

                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label for="textGroupName" class="col-lg-3 input-title RequiredStar">Mobile</label>
                <div class="col-lg-5">
                    <div class="input-group ">
                        <input type="text" class="form-control round" name="group_phone"
                            value="{{$GroupData->group_phone}}" id="textGroupPhone"
                            placeholder="Enter Group Phone" required data-error="Please enter Phone Number"
                            readonly>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label for="textGroupName" class="col-lg-3 input-title">Address</label>
                <div class="col-lg-5">
                    <textarea class="form-control round" name="group_addr" id="GroupAddress"
                        value="{{$GroupData->group_addr}}" rows="2" placeholder="Enter Address"
                        readonly>{{$GroupData->group_addr}}</textarea>
                </div>
            </div>
            <div class="form-row form-group align-items-center">
                <label for="textGroupName" class="col-lg-3 input-title">Website</label>
                <div class="col-lg-5">
                    <input type="text" class="form-control round" id="group_web_add"
                        value="{{$GroupData->group_web_add}}" name="group_web_add"
                        placeholder="Enter Website" readonly>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Group logo</label>
                <div class="col-lg-2">
                    @if(!empty($GroupData->group_logo))

                    @if(file_exists($GroupData->group_logo))
                    <img src="{{ asset($GroupData->group_logo) }}" style="width: 70px;">
                    @endif
                    @endif
                </div>

            </div>
            <div class="row">
                <div class="col-lg-9">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();"
                                class="btn btn-default btn-round">Back</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
