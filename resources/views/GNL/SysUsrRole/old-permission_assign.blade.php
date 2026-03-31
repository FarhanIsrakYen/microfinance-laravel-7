@extends('Layouts.erp_master')
@section('content')


<div class="page min-height">
    <div class="page-header">
        <h4 class="">System Permission Assign</h4>
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">User Management</a></li>
            <li class="breadcrumb-item"><a href="{{ url('gnl/surole') }}">System User Roles</a></li>
            <li class="breadcrumb-item"><a href="javascript:void(0)">System Permission Assign</a></li>
            <li class="breadcrumb-item active">List</li>
        </ol>
        <!-- {{-- <div class="page-header-actions">
            <a class="btn btn-sm btn-primary btn-outline btn-round" href="{{ url('gnl/surole/add') }}">
                <i class="icon wb-link" aria-hidden="true"></i>
                <span class="hidden-sm-down">New Entry</span>
            </a>
        </div> --}} -->
    </div>

    <div class="page-content">
        <div class="panel">
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-12">
                        <form action="{{ url('gnl/passign/'.$rid.'/assign') }}" method="post">
                            @csrf
                            <div class="example-wrap panel with-nav-tabs panel-primary">
                                <div class="nav-tabs-horizontal" data-plugin="tabs" id="tabs">
                                    <li class="list-unstyled">
                                        <div class="checkbox-custom checkbox-primary">
                                            <input type="checkbox" name="" id="all-menus-per"
                                                onclick="showAllPermission(this.id)" />
                                            <label for="all-menus-per">
                                                <b>Select All Menus Permission</b>
                                            </label>
                                        </div>
                                    </li>
                                    <br>
                                    <ul class="nav nav-tabs nav-tabs-reverse" role="tablist">
                                        <!-- {{---All Module ####################################### --}} -->

                                        <?php $i=0; ?>
                                        @foreach($AllData as $row)
                                        <li class="nav-item mr-2" role="presentation">
                                            <a class="nav-link nav-tabs btn btn-bg-color moduleCheckbox"
                                                data-toggle="tab" href="#module_arr_{{ $i }}_check_tab" role="tab"
                                                id="module_arr_{{ $i }}" ondblclick="fnModuleCheck(this.id);"
                                                title="Double click for Check this module">

                                                <input class="moduleclass" type="checkbox" name="module_arr[]"
                                                    id="module_arr_{{ $i }}_check" value="{{ $row['module_id'] }}"
                                                    <?= (in_array($row['module_id'], $modules)) ? 'checked' : ''?>>

                                                <label>{{ $row['module_name'] }} </label>
                                            </a>
                                        </li>
                                        <?php $i++; ?>
                                        @endforeach
                                    </ul>

                                    <div class="tab-content pt-20">
                                        <?php $j=0; ?>
                                        @foreach($AllData as $row)
                                        <!-- {{-- tab pane --}} -->
                                        <div class="tab-pane" id="module_arr_{{ $j }}_check_tab" role="tabpanel">
                                            <div class="row">
                                                <div class="col-lg-12">
                                                    <!-- {{-- menu name ####################################### --}} -->
                                                    <?php $k=0;  //dd($row['Menus']);?>
                                                    
                                                    @foreach($row['Menus'] as $MenuData)
                                                    @if(!empty($MenuData['menu_id']))
                                                    <li class="list-unstyled menus">
                                                        <div class="checkbox-custom checkbox-primary menuscheck">
                                                            <input type="checkbox" class="menusCheckbox"
                                                                name="menu_arr[]" id="menu_arr_{{ $j }}_{{ $k }}"
                                                                onclick="fnPermissionLoad(this.id, 'module_arr_{{ $j }}_check')"
                                                                value="{{ $MenuData['menu_id'] }}"
                                                                <?= (in_array($MenuData['menu_id'], $menus)) ? 'checked' : ''?> />

                                                            <label for="menu_arr_{{ $j }}_{{ $k }}">
                                                                <b>{{ $MenuData['menu_name'] }}</b>
                                                            </label>
                                                        </div>
                                                    </li>
                                                    <hr>
                                                    @if(!empty($MenuData['Menu_Permissions'][0]['per_id']))
                                                    <!-- {{-- permission  #######################################--}} -->
                                                    <label class="permissions" id="menu_arr_{{ $j }}_{{ $k }}_per_lvl">
                                                        <?php $l=0; //dd($MenuData['Menu_Permissions']);?>
                                                        @foreach($MenuData['Menu_Permissions'] as $PerData)
                                                        <!-- @if(!empty($PerData['per_id'])) -->
                                                        <li class="list-inline-item mr-4">
                                                            <div class="checkbox-custom checkbox-primary"
                                                                id="permissionsID">
                                                                <input type="checkbox" class="PerCheckClass "
                                                                    name="per_arr[]"
                                                                    id="per_arr_{{ $j }}_{{ $k }}_{{ $l }}"
                                                                    value="{{ $PerData['per_id'] }}"
                                                                    <?= (in_array($PerData['per_id'], $permissions)) ? 'checked' : ''?> />

                                                                <label for="per_arr_{{ $j }}_{{ $k }}_{{ $l }}">
                                                                    {{ $PerData['per_name'] }}
                                                                </label>

                                                            </div>
                                                        </li>
                                                        <?php $l++ ?>
                                                        <!-- @endif -->
                                                        @endforeach<br>
                                                    </label>

                                                    @endif

                                                    <?php $k++; ?>
                                                    @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                        <?php $j++; ?>
                                        @endforeach
                                    </div>

                                    <div class="form-row align-items-right float-right">
                                        <div class="form-group d-flex justify-content-center">
                                            <div class="example example-buttons">
                                                <a href="{{ url('gnl/surole') }}"
                                                    class="btn btn-default btn-round">Close</a>
                                                <button type="submit" class="btn btn-primary btn-round"
                                                    id="validateButton2">Save</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.nav-tabs a:first').tab('show');

    $('.menus input:checkbox').each(function() {

        var menu_id = $(this).attr('id');

        if ($(this).is(':checked')) {

            $('#' + menu_id + '_per_lvl').show();
        }
    });
});

function showAllPermission(menusPer) {

    if ($('#' + menusPer).is(':checked')) {

        $("label.permissions").each(function() {

            $(this).show();
            $('input:checkbox').each(function() {

                $(this).prop('checked', true);
            });
        });
    } else {
        $("label.permissions").each(function() {

            $(this).hide();
            $('input:checkbox').each(function() {

                $(this).prop('checked', false);
            });
        });
    }
}

function fnModuleCheck(anchor_id) {

    $('#' + anchor_id + ' input:checkbox').each(function() {
        if ($(this).is(':checked')) {
            $(this).prop('checked', false);

            $($('#' + anchor_id).attr('href') + ' input:checkbox').each(function() {
                $(this).prop('checked', false);
            });

        } else {
            $(this).prop('checked', true);

            $($('#' + anchor_id).attr('href') + ' input:checkbox').each(function() {
                $(this).prop('checked', true);
            });
        }
    });
}


function fnPermissionLoad(menu_id, module_id) {

    if ($("#" + menu_id).is(':checked')) {

        $('#' + module_id).prop('checked', true);

        $('#' + menu_id + '_per_lvl').show();
        $('#' + menu_id + '_per_lvl input:checkbox').each(function() {
            $(this).prop('checked', true);
        });

    } else {

        $('#' + menu_id + '_per_lvl input:checkbox').each(function() {

            $(this).prop('checked', false);
        });


        ////////////////////////////////
        var Uflag = true;
        $('#' + module_id + '_tab input:checkbox').each(function() {

            if ($(this).is(':checked')) {
                Uflag = false;
            }
        });

        if (Uflag === true) {
            $('#' + module_id).prop('checked', false);
        }
        ///////////////////////////////

        $('#' + menu_id + '_per_lvl input:checkbox').click(function() {

            var checked = document.querySelectorAll('label#' + menu_id + '_per_lvl input:checked');

            if (checked.length > 0) {

                $('#' + menu_id).prop('checked', true);
                $('#' + module_id).prop('checked', true);
            } else {

                $('#' + menu_id).prop('checked', false);
                $('#' + module_id).prop('checked', false);
            }

        });
    }
}
</script>

@endsection