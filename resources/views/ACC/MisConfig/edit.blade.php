@extends('Layouts.erp_master')
@section('content')
<!-- Page -->

<div class="panel-body">
    <form enctype="multipart/form-data" method="POST" data-toggle="validator" novalidate="true">
        @csrf
        <div class="row">
            <div class="col-lg-12 offset-lg-3">

            <div class="form-row align-items-center">
                    <label class="col-lg-2 input-title" for="sales_type">Business Type</label>

                    <div class="col-lg-4 form-group">
                        <div class="input-group">
                            <select class="form-control clsSelect2"
                            name="sales_type" id="sales_type" >
                                <option value="0">Select Business Type</option>
                                @foreach ($misType as $Row)
                                <option value="{{$Row->id}}" {{ ($vdata->sales_type == $Row->id) ? 'selected="selected"' : '' }} >{{$Row->name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-2 input-title" for="supplier_id">Supplier</label>

                    <div class="col-lg-4 form-group">
                        <div class="input-group">
                            <select name="supplier_id" id="supplier_id" class="form-control clsSelect2" >
                                <option value="0">Select Supplier</option>
                                @foreach ($SupplierData as $Row)
                                <option value="{{$Row->id}}" {{ ($vdata->supplier_id == $Row->id) ? 'selected="selected"' : '' }}>{{$Row->sup_name}}</option>      
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                        <label class="col-lg-2 input-title RequiredStar" for="mis_name">MIS Name</label>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" class="form-control round" name="mis_name" id="mis_name" value="{{$vdata->mis_name}}" placeholder="Enter MIS Name">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                        <label class="col-lg-2 input-title" for="table_field_name">Table Field Name</label>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="input-group ">
                                <input type="text" class="form-control round" name="table_field_name" id="table_field_name" value="{{$vdata->table_field_name}}"  placeholder="Enter Table Field Name">
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

<!-- End Page -->

@endsection
