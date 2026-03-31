@extends('Layouts.erp_master')
@section('content')


<!-- Page -->
<form enctype="multipart/form-data" method="POST" data-toggle="validator" novalidate="true" autocomplete="off">
    @csrf

    <div class="row">
       <div class="col-lg-9 offset-lg-3">

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">GROUP</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group ">
                        <select class="form-control dynamic clsSelect2"
                                name="group_id" id="group_id" required data-error="Please select group name."
                                onchange="fnAjaxSelectBox(
                                            'company_id',
                                            this.value,
                                '{{base64_encode('gnl_companies')}}',
                                '{{base64_encode('group_id')}}',
                                '{{base64_encode('id,comp_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                        );">

                            <option value="">Select Group</option>

                            @foreach ($GroupData as $Row)
                            <option value="{{$Row->id}}" >{{$Row->group_name}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">COMPANY</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control dynamic clsSelect2"
                                name="company_id" id="company_id"
                                required data-error="Please select Company name."
                                onchange="fnAjaxSelectBox(
                                            'project_id',
                                            this.value,
                                '{{base64_encode('gnl_projects')}}',
                                '{{base64_encode('company_id')}}',
                                '{{base64_encode('id,project_name')}}',
                                '{{url('/ajaxSelectBox')}}'
                                        );"
                                >
                            <option value="">Select One</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar dynamic" >Project</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2"
                                 required data-error="Please select Project name."
                                 name="project_id" id="project_id"  data-dependent="project_type"
                                 onchange="fnAjaxSelectBox(
                                             'project_type_id',
                                             this.value,
                                 '{{base64_encode('gnl_project_types')}}',
                                 '{{base64_encode('project_id')}}',
                                 '{{base64_encode('id,project_type_name')}}',
                                 '{{url('/ajaxSelectBox')}}'
                                         );"
                                 >
                            <option value="" >Select Option</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar dynamic" >Project Type</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" name="project_type_id" id="project_type_id" required data-error="Please select project type.">
                            <option value="">Select Option</option>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" >Branch Name</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter Branch Name" name="branch_name" id="textbranch_name" required data-error="Please enter branch name.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" >Branch Code</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter Branch Code" name="branch_code" id="textbranch_code" required data-error="Please enter branch code.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" >Contact Person</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter Branch contact Person" name="contact_person" id="textcontact_person" required data-error="Please enter branch contact person">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Email</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="email" class="form-control round" placeholder="Enter Branch Email" name="branch_email" id="textbranch_email"  >
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Mobile</label>
                <div class="col-lg-5 form-group">
                    <div class="input-group">
                        <input type="text" pattern="[01][0-9]{10}" class="form-control round textNumber"
                            name="branch_phone" id="textbranch_phone" placeholder="Mobile Number (01*********)" required
                            data-error="Please enter mobile number (01*********)" minlength="11" maxlength="11"
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
                        <textarea type="text" class="form-control round" placeholder="Enter Branch Address" name="branch_addr" id="textbranch_addr"  data-error="Please enter branch address."></textarea>
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
                        <input type="text" class="form-control round datepicker-custom"  
                        id="textbranch_opening_date" name="branch_opening_date" placeholder="DD-MM-YYYY">
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
                                        <i class="icon wb-calendar round"  aria-hidden="true"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control round datepicker-custom"  id="soft_start_date" 
                                name="soft_start_date" placeholder="DD-MM-YYYY">
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
                                        <i class="icon wb-calendar round"  aria-hidden="true"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control round datepicker-custom"  id="acc_start_date" 
                                name="acc_start_date" placeholder="DD-MM-YYYY">
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
                                        <i class="icon wb-calendar round"  aria-hidden="true"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control round datepicker-custom"  id="mfn_start_date" 
                                name="mfn_start_date" placeholder="DD-MM-YYYY">
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
                                        <i class="icon wb-calendar round"  aria-hidden="true"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control round datepicker-custom"  id="fam_start_date" 
                                name="fam_start_date" placeholder="DD-MM-YYYY">
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
                                        <i class="icon wb-calendar round"  aria-hidden="true"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control round datepicker-custom"  id="inv_start_date" 
                                name="inv_start_date" placeholder="DD-MM-YYYY">
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
                                        <i class="icon wb-calendar round"  aria-hidden="true"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control round datepicker-custom"  id="proc_start_date" 
                                name="proc_start_date" placeholder="DD-MM-YYYY">
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
                                        <i class="icon wb-calendar round"  aria-hidden="true"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control round datepicker-custom"  id="bill_start_date" 
                                name="bill_start_date" placeholder="DD-MM-YYYY">
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
                                        <i class="icon wb-calendar round"  aria-hidden="true"></i>
                                    </span>
                                </div>
                                <input type="text" class="form-control round datepicker-custom"  id="hr_start_date" 
                                name="hr_start_date" placeholder="DD-MM-YYYY">
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
                            <button type="submit" class="btn btn-primary btn-round">Save</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<!-- End Page -->
<script type="text/javascript">
    $('form').submit(function (event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });
</script>
@endsection
