@extends('Layouts.erp_master')
@section('content')

<form method="post" data-toggle="validator" novalidate="true">
    @csrf
    <div class="row">
        <div class="col-lg-9 offset-lg-3">

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Module</label>
                <div class="col-lg-6 form-group">
                    <div class="input-group">
                        <select class="form-control clsSelect2" 
                        name="module_id" id="selModuleId" required data-error="Please select module">
                            <option value="">Select One</option>
                            <?php
                            foreach ($module as $row) {
                                ?>
                                <option value="<?= $row->id ?>">
                                    <?= $row->module_name ?>
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Parent Menu</label>
                <div class="col-lg-6 form-group">
                    <div class="input-group">
                        <select class="form-control round browser-default" 
                        name="parent_menu_id" id="select_parent_menu_id">
                            <option value="0">Select One</option>
                            <?php
                            foreach ($parent_menu as $row) {
                                ?>
                                <option value="<?= $row->id ?>">
                                    <?= $row->menu_name ?> (<small> <?= $row->route_link ?> </small>)
                                </option>
                                <?php
                            }
                            ?>
                        </select>
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="roleName">Menu Name</label>
                <div class="col-lg-6 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter Menu Name" name="menu_name" id="menu_name" required data-error="Please enter menu name.">
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar">Route Link</label>
                <div class="col-lg-6 form-group">
                    <div class="input-group">
                        <input type="text" name="route_link" id="checkDuplicateCode"
                               class="form-control round"
                               placeholder="Enter Route Link" required
                               data-error="Please enter route link."
                               onblur="fnCheckDuplicate(
                                    '{{base64_encode('gnl_sys_menus')}}', 
                                    this.name+'&&is_delete', 
                                    this.value+'&&0',
                                    '{{url('/ajaxCheckDuplicate')}}',
                                    this.id,
                                    'txtCodeError', 
                                    'route link');" >
                    </div>
                    <div class="help-block with-errors is-invalid"></div>
                    <div class="help-block is-invalid" id="txtCodeError"></div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title">Page Title</label>
                <div class="col-lg-6 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter Page Title" name="page_title" id="page_title">
                    </div>
                </div>
            </div>

            <!--                            <div class="form-row align-items-center">
                                            <label class="col-lg-3 input-title RequiredStar" for="roleName">Controller Name</label>
                                            <div class="col-lg-6 form-group">
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" placeholder="Enter Controller Name" name="controller" id="txtController" required data-error="Please controller name.">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                                @error('controller')
                                                    <div class="help-block with-errors is-invalid">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>-->

            <!--                            <div class="form-row align-items-center">
                                            <label class="col-lg-3 input-title RequiredStar" for="roleName">Method Name</label>
                                            <div class="col-lg-6 form-group">
                                                <div class="input-group">
                                                    <input type="text" class="form-control round" placeholder="Enter Method Name" name="action" id="action" required data-error="Please enter method name.">
                                                </div>
                                                <div class="help-block with-errors is-invalid"></div>
                                                @error('controller')
                                                    <div class="help-block with-errors is-invalid">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>-->

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title" for="roleName">Icon</label>
                <div class="col-lg-6 form-group">
                    <div class="input-group">
                        <input type="text" class="form-control round" placeholder="Enter Icon Name" name="menu_icon" id="menu_icon">
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <label class="col-lg-3 input-title RequiredStar" for="groupName">Order By</label>
                <div class="col-lg-6 form-group">
                    <div class="input-group ">
                        <input type="text" class="form-control round" id="order_by" name="order_by" placeholder="Enter Order" required data-error="Please enter order by.">
                    </div>
                </div>
            </div>

            <div class="form-row align-items-center">
                <div class="col-lg-7">
                    <div class="form-group d-flex justify-content-center">
                        <div class="example example-buttons">
                            <a href="javascript:void(0)" onclick="goBack();" class="btn btn-default btn-round">Back</a>
                            <button type="submit" class="btn btn-primary btn-round">Save</button>
                            <!--<button type="button" class="btn btn-warning btn-round">Next</button>-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript">
    $('#select_parent_menu_id').select2();

    $('#selModuleId').change(function(){
        var module_id = $(this).val();

        $.ajax({
            method: "GET",
            url: "{{url('/ajaxMenuList')}}",
            dataType: "text",
            data: {
                module_id: module_id, SelectedVal: null
            },
            success: function (data) {
                if (data) {
                    $('#select_parent_menu_id').empty().html(data);
                }
            }
        });

    });

    $('form').submit(function (event) {
        $(this).find(':submit').attr('disabled', 'disabled');
    });

</script>
@endsection
