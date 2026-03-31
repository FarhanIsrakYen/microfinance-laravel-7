@extends('Layouts.erp_master')
@section('content')

<!-- Page -->
<?php
$selectProjectArr = explode(',', $Ledgerdata->project_arr);
$selectBranchArr = explode(',', $Ledgerdata->branch_arr);
?>

<form enctype="multipart/form-data" method="POST" data-toggle="validator" novalidate="true">
    @csrf
    <div class="row">
        <div class="col-lg-12 offset-lg-3">
            <div class="form-row align-items-center">
                    <label class="col-lg-2 input-title" for="LedgerName">Name</label>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="input-group">
                            <input type="text" class="form-control round" name="name" id="name" 
                            value="{{$Ledgerdata->name}}" placeholder="Enter Ledger Name" disabled>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                    <label class="col-lg-2 input-title" for="LedgerCode">Code</label>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="input-group ">
                            <input type="text" class="form-control round" name="code" id="code" 
                            value="{{$Ledgerdata->code}}" placeholder="Enter Code" disabled>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-row align-items-center">
                <label class="col-lg-2 input-title" for="LedgerParent">Parent</label>

                <div class="col-lg-4 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="parent_id" id="parent_id"  disabled
                        onchange="fnAjaxSelectBox(
                                                'order_by',
                                                this.value,
                                    '{{base64_encode('acc_account_ledger')}}',
                                    '{{base64_encode('parent_id')}}',
                                    '{{base64_encode('order_by,name')}}',
                                    '{{url('/ajaxSelectBoxforLedger')}}'
                                            );">
                            <option value='0'>Grand Parent</option>
                            @foreach ($Ledger as $Row)
                            <option value="{{$Row->id}}"  {{ ($Ledgerdata->parent_id == $Row->id) ? 'selected="selected"' : '' }}  >{{$Row->name}}</option>
                            @endforeach
                            
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-2 input-title" for="LedgerAccountType">Account Type</label>

                <div class="col-lg-4 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="acc_type_id" id="acc_type_id" disabled>
                            <option value="0">Select Account Type</option>
                            @foreach ($acc_data as $Row)
                            <option value="{{$Row->id}}" {{ ($Ledgerdata->acc_type_id == $Row->id) ? 'selected="selected"' : '' }}  >{{$Row->name}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-2 input-title" for="LedgerOrdering">Ordering After</label>

                <div class="col-lg-4 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="order_by" id="order_by" disabled>
                            <option value="0">At First</option>
                            
                            
                            
                            
                        </select>
                    </div>
                </div>
            </div>

            
               {{-- ///////////////////////////////////////////////////////////////  --}}

             

            <div class="form-row align-items-center">
                <label class="col-lg-2 input-title" for="LedgerBranch">Project/Branch</label>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="checkbox-custom checkbox-primary">

                            <input type="checkbox" id="project_array_0" name="project_array[]"
                            <?= (in_array(0, $selectProjectArr)) ? "checked" : "" ?>
                            value='0' />
                            <label>All Project</label>

                            @foreach ($project as $Row)
                            <div class="checkbox-custom checkbox-primary">
                                <div class="row">
                                    <div class="col-lg-4">
                                        <input type="checkbox" class="project_cls" 
                                        id="project_array_{{$Row->id}}" name="project_array[]" 
                                        <?= (in_array($Row->id, $selectProjectArr)) ? "checked" : "" ?>
                                        value="{{$Row->id}}" />

                                        <label for="project_array_{{$Row->id}}">
                                            <small>{{$Row->project_name}}</small>
                                        </label>
                                    </div>

                                    <div class="col-lg-1 project_icon" id="project_icon_{{$Row->id}}">
                                        <a href="javascript:void(0)" data-toggle="modal" data-target="#branch_modal_{{$Row->id}}">
                                            <i class="icon wb-home mr-2"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>

                            @include('elements.pop.LedgerBranchSelect')
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            {{-- /////////////////////////////////////////////////////////  --}}
            <div class="form-row align-items-center">
                <label class="col-lg-2 input-title" for="description">Description</label>

                <div class="col-lg-4 form-group">
                    <div class="input-group">
                        <textarea class="form-control round fix-size" disabled id="description" name="description" rows="2" placeholder="Enter Address" data-error="Please enter Description."></textarea>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                    <label class="col-lg-2 input-title" for="LedgerIsGroupHead">Is Group Head</label>
                <div class="col-lg-4">
                    <div class="form-group">
                        <div class="checkbox-custom checkbox-primary">

                            <input type="checkbox" name="is_group_head" disabled
                            {{ ($Ledgerdata->is_group_head== 1) ? 'checked="checked"' : '' }}/>
                            <label></label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
            <label class="col-lg-2 input-title"></label>
                <div class="col-lg-4">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                             <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Close</a>
                        
                          
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>


<!-- End Page -->
<script>

$(document).ready(function() {

    fnAjaxSelectBox('order_by',
        $('#parent_id').val(),
        '{{ base64_encode("acc_account_ledger") }}',
        '{{base64_encode("parent_id")}}',
        '{{base64_encode("order_by,name")}}',
        '{{url("/ajaxSelectBoxforLedger")}}',
        '{{ $Ledgerdata->id}}'
    );
   
});


</script>

@endsection
