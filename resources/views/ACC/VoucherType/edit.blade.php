@extends('Layouts.erp_master')
@section('content')

<!-- Page -->

<div class="page-content">
    <div class="panel">
        <div class="panel-body">
            <form enctype="multipart/form-data" method="POST" data-toggle="validator" novalidate="true">
                @csrf
                <div class="row">
                    <div class="col-lg-12 offset-lg-3">
                        <div class="form-row align-items-center">
                                <label class="col-lg-2 input-title RequiredStar" for="VoucherTypeName">Name</label>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <div class="input-group">
                                    <input type="text" class="form-control round" name="name" id="name" value="{{$vdata->name}}" placeholder="Enter Voucher Name">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-row align-items-center">
                                <label class="col-lg-2 input-title" for="VoucherTypeTitle">Title Name</label>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <div class="input-group ">
                                        <input type="text" class="form-control round" name="title_name" id="title_name"  value="{{$vdata->title_name}}" placeholder="Enter Title">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-row align-items-center">
                                <label class="col-lg-2 input-title" for="VoucherTypeShortName">Short Name</label>
                            <div class="col-lg-4">
                                <div class="form-group">
                                    <div class="input-group ">
                                        <input type="text" class="form-control round" name="short_name" id="short_name" value="{{$vdata->short_name}}"  placeholder="Enter Short Name">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-row align-items-center">
                        <label class="col-lg-2 input-title"></label>
                            <div class="col-lg-4">
                                <div class="form-group d-flex justify-content-center">
                                    <div class="example example-buttons">
                                        <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                                        <button type="submit" class="btn btn-primary btn-round">Update</button>
                                        {{-- <button type="button" class="btn btn-warning btn-round">Reset</button> --}}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- End Page -->

@endsection
