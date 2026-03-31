@extends('Layouts.erp_master')
@section('content')
<!-- Page -->

<div class="panel-body">
    <form enctype="multipart/form-data" method="POST" data-toggle="validator" novalidate="true">
        @csrf
        <div class="row">
            <div class="col-lg-12 offset-lg-3">
                <div class="form-row align-items-center">
                    <label class="col-lg-2 input-title" for="table_field_name">Business Type</label>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="input-group ">
                            <input type="text" class="form-control round" name="table_field_name" id="table_field_name" value="{{$vdata->salestype['name'] }}" readonly  placeholder="Enter Table Field Name">
                        </div>
                    </div>
                </div>
            </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-2 input-title" for="table_field_name">Supplier</label>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="input-group ">
                            <input type="text" class="form-control round" name="table_field_name" id="table_field_name" value="{{$vdata->supplier['sup_name'] }}" readonly  placeholder="Enter Table Field Name">
                        </div>
                    </div>
                </div>
            </div>

                

                <div class="form-row align-items-center">
                        <label class="col-lg-2 input-title" for="mis_name">MIS Name</label>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="input-group">
                                <input type="text" class="form-control round" name="mis_name" id="mis_name" value="{{$vdata->mis_name}}" readonly placeholder="Enter MIS Name">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                        <label class="col-lg-2 input-title" for="table_field_name">Table Field Name</label>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="input-group ">
                                <input type="text" class="form-control round" name="table_field_name" id="table_field_name" value="{{$vdata->table_field_name}}" readonly  placeholder="Enter Table Field Name">
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
