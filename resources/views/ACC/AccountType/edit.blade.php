@extends('Layouts.erp_master')
@section('content')

<!-- Page -->

<div class="panel-body">
    <form enctype="multipart/form-data" method="POST" data-toggle="validator" novalidate="true">
        @csrf
        <div class="row">
            <div class="col-lg-12 offset-lg-3">
                <div class="form-row align-items-center">
                        <label class="col-lg-2 input-title RequiredStar" for="name">Name</label>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="input-group">
                            <input name="name" id="name" type="text" class="form-control round" value="{{$data->name}}" placeholder="Enter Account Name">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row align-items-center"  id="divCode"> 
                    <label class="col-lg-2 input-title RequiredStar" for="LedgerCode">Code</label>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="input-group ">
                                <input type="text" class="form-control round" name="code" id="code" value="{{$data->code}}" placeholder="Enter Code">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row align-items-center">
                    <label class="col-lg-2 input-title" for="AccountTypesParent">Parent</label>
                    
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="input-group ">
                                <select name="parent_id" id="parent_id" class="form-control clsSelect2">
                                    <option value="0">Select Parent</option>

                                    @foreach ($acc_data as $Row)
                                    <option value="{{$Row->id}}" {{ ($data->parent_id == $Row->id) ? 'selected="selected"' : '' }} >{{$Row->name}}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                        <label class="col-lg-2 input-title" for="AccountTypesDestription">Description</label>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="input-group ">
                                <textarea name="description" id="description" class="form-control round"  rows="2" placeholder="Enter Description">{{$data->description}}</textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-row align-items-center">
                        <label class="col-lg-2 input-title" for="is_parent">Is Parent</label>
                    <div class="col-lg-4">
                        <div class="form-group">
                            <div class="checkbox-custom checkbox-primary">
                                <input type="checkbox" name="is_parent" id="is_parent" {{ ($data->is_parent == 1) ? 'checked="checked"' : '' }}  />
                                <label>Parent</label>
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

<Script>
    $(document).ready(function() {

        if($('#parent_id').val() == 0 && $('#is_parent').prop("checked")){

            $('#divCode').show();
        }else{
            $('#divCode').hide();
        }
      
        // $('#is_parent').prop("checked")

    });
</Script>
@endsection
