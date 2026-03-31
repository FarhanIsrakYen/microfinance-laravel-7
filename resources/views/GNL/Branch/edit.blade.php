@extends('Layouts.erp_master')

@section('content')
<!-- Page -->
    <form enctype="multipart/form-data" method="POST" data-toggle="validator" novalidate="true" autocomplete="off">
        @csrf

        <div class="row">
            <div class="col-lg-9 offset-lg-3">

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar" >GROUP</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <select class="form-control clsSelect2 readonlySelect" name="group_id" id="group_id">
                                @foreach ($GroupData as $Row)
                                {{-- for selected id edit  --}}
                                <option value="{{$Row->id}}" {{ ($BranchData->group_id == $Row->id) ? 'selected="selected"' : '' }} >{{$Row->group_name}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar" >COMPANY</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <select class="form-control clsSelect2 readonlySelect" name="company_id" id="company_id">
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Project</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <select  class="form-control clsSelect2 readonlySelect"name="project_id" id="project_id">
                                <option value="{{$BranchData->project_id}}" selected>
                                    {{$BranchData->project['project_name']}}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar" >Project Type</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <select class="form-control clsSelect2 readonlySelect" name="project_type_id" id="project_type_id" >
                                <option value="{{$BranchData->project_type_id}}" selected> 
                                    {{$BranchData->projectType['project_type_name']}}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Branch Name</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <input type="text" class="form-control round" placeholder="Enter Branch Name" name="branch_name" id="textbranch_name"
                                   value="{{$BranchData->branch_name}}" required data-error="Please enter branch name." @if($BranchData->id == 0) readonly @endif>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Branch Code</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <input type="text" class="form-control round" placeholder="Enter Branch Code"
                                   name="branch_code" id="textbranch_code" required
                                   value="{{$BranchData->branch_code}}" data-error="Please enter branch code.">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Contact Person</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <input type="text" class="form-control round readonlySelect" placeholder="Enter Branch contact Person"
                                   name="contact_person" id="textcontact_person" required
                                   value="{{$BranchData->contact_person}}" data-error="Please enter branch contact person">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title">Email</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <input type="text" class="form-control round readonlySelect" placeholder="Enter Branch Email" name="branch_email"
                                   id="textbranch_email"
                                   value="{{$BranchData->branch_email}}" data-error="Please enter branch email.">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title RequiredStar">Mobile</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber"
                            name="branch_phone" id="textbranch_phone" placeholder="Mobile Number (01*********)" 
                            value="{{$BranchData->branch_phone}}"
                            required data-error="Please enter mobile number (01*********)" minlength="11" maxlength="11"
                            onblur="fnCheckDuplicate(
                                '{{base64_encode('gnl_branchs')}}',
                                this.name+'&&is_delete',
                                this.value+'&&0',
                                '{{url('/ajaxCheckDuplicate')}}',
                                this.id,
                                'errMsgPhone',
                                'mobile number');">
                        </div>
                        <div class="help-block with-errors is-invalid" id="errMsgPhone"></div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title">Address</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <textarea type="text" class="form-control round readonlySelect" placeholder="Enter Branch Address" name="branch_addr"
                                    id="textbranch_addr"  data-error="Please enter branch address.">{{$BranchData->branch_addr}}</textarea>
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <div class="form-row align-items-center">
                    <label class="col-lg-3 input-title">Branch Opening Date</label>
                    <div class="col-lg-5 form-group">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text ">
                                    <i class="icon wb-calendar round" aria-hidden="true"></i>
                                </span>
                            </div>
                            <input type="text" class="form-control round readonlySelect datepicker-custom"  
                                    id="textbranch_opening_date" name="branch_opening_date" placeholder="DD-MM-YYYY"
                                    value="{{ (new Datetime($BranchData->branch_opening_date))->format('d-m-Y') }}">
                        </div>
                        <div class="help-block with-errors is-invalid"></div>
                    </div>
                </div>

                <?php
                    if (!empty(Session::get('LoginBy.user_role.role_module'))) {
                       $SysModules = Session::get('LoginBy.user_role.role_module');
                    }
                ?>
                @if(count($SysModules) > 0)
                    @foreach ($SysModules as $module)
                        @if($module['short_name'] == 'pos')
                        <div class="form-row align-items-center">
                            <label class="col-lg-3 input-title">POS Opening Date</label>
                            <div class="col-lg-5 form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="icon wb-calendar round" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control round readonlySelect datepicker-custom"  
                                    id="soft_start_date" name="soft_start_date" placeholder="DD-MM-YYYY"
                                    value="{{ (new Datetime($BranchData->soft_start_date))->format('d-m-Y') }}">
                                </div>
                                <div class="help-block with-errors is-invalid"></div>
                            </div>
                        </div>
                        @endif
                        @if($module['short_name'] == 'acc')
                        <div class="form-row align-items-center">
                            <label class="col-lg-3 input-title">Accounting Opening Date</label>
                            <div class="col-lg-5 form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="icon wb-calendar round" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control round readonlySelect datepicker-custom"  
                                    id="acc_start_date" name="acc_start_date" placeholder="DD-MM-YYYY"
                                    value="{{ $BranchData->acc_start_date ? (new Datetime($BranchData->acc_start_date))->format('d-m-Y') : ''}}">
                                </div>
                                <div class="help-block with-errors is-invalid"></div>
                            </div>
                        </div>
                        @endif
                        @if($module['short_name'] == 'mfn')
                        <div class="form-row align-items-center">
                            <label class="col-lg-3 input-title">Microfinance Opening Date</label>
                            <div class="col-lg-5 form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="icon wb-calendar round" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control round readonlySelect datepicker-custom"  
                                    id="mfn_start_date" name="mfn_start_date" placeholder="DD-MM-YYYY"
                                    value="{{ $BranchData->mfn_start_date ? (new Datetime($BranchData->mfn_start_date))->format('d-m-Y') : '' }}">
                                </div>
                                <div class="help-block with-errors is-invalid"></div>
                            </div>
                        </div>
                        @endif
                        @if($module['short_name'] == 'fam')
                        <div class="form-row align-items-center">
                            <label class="col-lg-3 input-title">Fixed Asset Management Opening Date</label>
                            <div class="col-lg-5 form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="icon wb-calendar round" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control round readonlySelect datepicker-custom"  
                                    id="fam_start_date" name="fam_start_date" placeholder="DD-MM-YYYY"
                                    value="{{ $BranchData->fam_start_date ? (new Datetime($BranchData->fam_start_date))->format('d-m-Y') : '' }}">
                                </div>
                                <div class="help-block with-errors is-invalid"></div>
                            </div>
                        </div>
                        @endif
                        @if($module['short_name'] == 'inv')
                        <div class="form-row align-items-center">
                            <label class="col-lg-3 input-title">Inventory Opening Date</label>
                            <div class="col-lg-5 form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="icon wb-calendar round" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control round readonlySelect datepicker-custom" 
                                    id="inv_start_date" name="inv_start_date" placeholder="DD-MM-YYYY"
                                    value="{{ $BranchData->inv_start_date ? (new Datetime($BranchData->inv_start_date))->format('d-m-Y') : '' }}">
                                </div>
                                <div class="help-block with-errors is-invalid"></div>
                            </div>
                        </div>
                        @endif
                        @if($module['short_name'] == 'proc')
                        <div class="form-row align-items-center">
                            <label class="col-lg-3 input-title">Procurement Opening Date</label>
                            <div class="col-lg-5 form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="icon wb-calendar round" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control round readonlySelect datepicker-custom" 
                                    id="proc_start_date" name="proc_start_date" placeholder="DD-MM-YYYY"
                                    value="{{ $BranchData->proc_start_date ? (new Datetime($BranchData->proc_start_date))->format('d-m-Y') : '' }}">
                                </div>
                                <div class="help-block with-errors is-invalid"></div>
                            </div>
                        </div>
                        @endif
                        @if($module['short_name'] == 'bill')
                        <div class="form-row align-items-center">
                            <label class="col-lg-3 input-title">Billing System Opening Date</label>
                            <div class="col-lg-5 form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="icon wb-calendar round" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control round readonlySelect datepicker-custom" 
                                    id="bill_start_date" name="bill_start_date" placeholder="DD-MM-YYYY"
                                    value="{{ $BranchData->bill_start_date ? (new Datetime($BranchData->bill_start_date))->format('d-m-Y') : '' }}">
                                </div>
                                <div class="help-block with-errors is-invalid"></div>
                            </div>
                        </div>
                        @endif
                        @if($module['short_name'] == 'HR')
                        <div class="form-row align-items-center">
                            <label class="col-lg-3 input-title">Human Resource Opening Date</label>
                            <div class="col-lg-5 form-group">
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text">
                                            <i class="icon wb-calendar round" aria-hidden="true"></i>
                                        </span>
                                    </div>
                                    <input type="text" class="form-control round readonlySelect datepicker-custom"
                                    id="hr_start_date" name="hr_start_date" placeholder="DD-MM-YYYY"
                                    value="{{ $BranchData->hr_start_date ? (new 
                                    Datetime($BranchData->hr_start_date))->format('d-m-Y') : '' }}">
                                </div>
                                <div class="help-block with-errors is-invalid"></div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @endif

                <div class="row">
                    <div class="col-lg-9">
                        <div class="form-group d-flex justify-content-center">
                            <div class="example example-buttons">
                                <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                                <button type="submit" class="btn btn-primary btn-round">Update</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
<!-- End Page -->
<script>
//   all company load
    $(document).ready(function () {
        
        fnAjaxSelectBox(
                "company_id",
                "{{$BranchData->group_id }}",
                '{{base64_encode("gnl_companies")}}',
                '{{base64_encode("group_id")}}',
                '{{base64_encode("id,comp_name")}}',
                '{{url("/ajaxSelectBox")}}',
                '{{$BranchData->company_id}}'
                );
        fnAjaxSelectBox(
                "project_id",
                "{{ $BranchData->company_id }}",
                '{{base64_encode("gnl_projects")}}',
                '{{base64_encode("company_id")}}',
                '{{base64_encode("id,project_name")}}',
                '{{url("/ajaxSelectBox")}}',
                '{{$BranchData->project_id}}'
                );
        fnAjaxSelectBox(
                "project_type_id",
                "{{ $BranchData->project_id }}",
                '{{base64_encode("gnl_project_types")}}',
                '{{base64_encode("project_id")}}',
                '{{base64_encode("id,project_type_name")}}',
                '{{url("/ajaxSelectBox")}}',
                '{{ $BranchData->project_type_id}}'
                );

                $('form').submit(function (event) {
                    // event.preventDefault();
                    $(this).find(':submit').attr('disabled', 'disabled');
                    // $(this).submit();
                });

        @if($BranchData->is_approve == 1)
            $('.readonlySelect').prop('disabled', true);
        @endif
    });

</script>
@endsection
